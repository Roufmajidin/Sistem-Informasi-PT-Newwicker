<?php

namespace App\Services;

use Google\Client;

use Google\Http\MediaFileUpload;

use Google\Service\Drive;

use Google\Service\Drive\DriveFile;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;

use RuntimeException;

use Throwable;

class GoogleDriveService

{

    private Client $client;

    private Drive $drive;

    private string $tokenPath = 'google-drive-token.json';

    public function __construct()

    {

        $this->client = new Client();

        $this->client->setClientId(

            config('services.google_drive.client_id')

        );

        $this->client->setClientSecret(

            config('services.google_drive.client_secret')

        );

        $this->client->setRedirectUri(

            config('services.google_drive.redirect_uri')

        );

        $this->client->setScopes([Drive::DRIVE]);

        $this->loadToken();

        $this->drive = new Drive($this->client);

    }

    /**

     * Membaca token dari storage server dan refresh otomatis jika kedaluwarsa.

     */

    private function loadToken(): void

    {

        $storage = Storage::disk('local');

        if (!$storage->exists($this->tokenPath)) {

            throw new RuntimeException(

                'Token Google Drive tidak ditemukan. Admin IT perlu menghubungkan Google Drive terlebih dahulu.'

            );

        }

        $token = json_decode($storage->get($this->tokenPath), true);

        if (!is_array($token) || empty($token['access_token'])) {

            throw new RuntimeException('File token Google Drive tidak valid.');

        }

        $this->client->setAccessToken($token);

        if (!$this->client->isAccessTokenExpired()) {

            return;

        }

        $refreshToken = $token['refresh_token']

            ?? $this->client->getRefreshToken();

        if (empty($refreshToken)) {

            throw new RuntimeException(

                'Refresh token tidak tersedia. Periksa koneksi Google Drive akun IT.'

            );

        }

        $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (

            !is_array($newToken) ||

            isset($newToken['error']) ||

            empty($newToken['access_token'])

        ) {

            $error = is_array($newToken)

                ? (

                    $newToken['error_description']

                    ?? $newToken['error']

                    ?? 'Respons Google tidak valid.'

                )

                : 'Respons Google tidak valid.';

            throw new RuntimeException('Refresh token gagal: ' . $error);

        }

        // Google tidak selalu mengembalikan refresh token baru.

        if (empty($newToken['refresh_token'])) {

            $newToken['refresh_token'] = $refreshToken;

        }

        $updatedToken = array_merge($token, $newToken);

        $this->client->setAccessToken($updatedToken);

        $updatedToken = $this->client->getAccessToken();

        if (empty($updatedToken['refresh_token'])) {

            $updatedToken['refresh_token'] = $refreshToken;

        }

        $json = json_encode(

            $updatedToken,

            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES

        );

        if ($json === false) {

            throw new RuntimeException(

                'Token Google gagal dikonversi menjadi JSON.'

            );

        }

        if (!$storage->put($this->tokenPath, $json)) {

            throw new RuntimeException(

                'Token baru gagal disimpan di storage server.'

            );

        }

    }

    /**

     * Membuat folder di Google Drive.

     */

    public function createFolder(

        string $name,

        ?string $parentFolderId = null

    ): array {

        $parentFolderId = $parentFolderId

            ?: config('services.google_drive.root_folder_id');

        if (!$parentFolderId) {

            throw new RuntimeException(

                'GOOGLE_DRIVE_ROOT_FOLDER_ID belum dikonfigurasi.'

            );

        }

        $folder = new DriveFile([

            'name' => $name,

            'mimeType' => 'application/vnd.google-apps.folder',

            'parents' => [$parentFolderId],

        ]);

        $created = $this->drive->files->create(

            $folder,

            [

                'fields' => 'id,name,mimeType,webViewLink,parents',

                'supportsAllDrives' => true,

            ]

        );

        $id = $created->getId();

        return [

            'id' => $id,

            'name' => $created->getName(),

            'url' => $created->getWebViewLink()

                ?: 'https://drive.google.com/drive/folders/' . $id,

        ];

    }

    /**

     * Upload file menggunakan resumable upload dan potongan 5 MB.

     *

     * Tidak membaca seluruh file ke memori sekaligus.

     */

    /** Start a resumable session; the OAuth access token never goes to the browser. */

    public function startBrowserUpload(string $fileName, string $folderId, string $mimeType, int $size, string $ticket): string

    {

        if ($size < 1 || $size > 25 * 1024 * 1024 || trim($folderId) === '') {

            throw new RuntimeException('Parameter upload tidak valid.');

        }

        $accessToken = $this->client->getAccessToken()['access_token'] ?? null;

        if (!$accessToken) throw new RuntimeException('Access token Google Drive tidak tersedia.');

        $response = (new \GuzzleHttp\Client(['timeout' => 35, 'http_errors' => true]))->request('POST',

            'https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&fields=id,name,mimeType,size,parents,trashed', [

                'headers' => [

                    'Authorization' => 'Bearer ' . $accessToken,

                    'Content-Type' => 'application/json; charset=UTF-8',

                    'X-Upload-Content-Type' => $mimeType,

                    'X-Upload-Content-Length' => (string) $size,

                ],

                'json' => ['name' => $fileName, 'parents' => [$folderId], 'mimeType' => $mimeType,
                    'appProperties' => ['rnd_upload_ticket' => $ticket]],

                'http_errors' => true,

                'timeout' => 35,

            ]);

        $url = $response->getHeaderLine('Location');

        if (!$url || !preg_match('~^https://[a-z0-9.-]+\.googleapis\.com/upload/~i', $url)) {

            throw new RuntimeException('Google Drive tidak memberikan URL sesi upload yang valid.');

        }

        return $url;

    }

    /** Find a completed upload by its private Drive appProperty, never by filename alone. */
    public function findBrowserUploadByTicket(string $ticket, string $folderId): ?array
    {
        if (!preg_match('/^[a-f0-9-]{36}$/i', $ticket)) {
            throw new RuntimeException('Ticket upload tidak valid.');
        }
        $folderEscaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $folderId);
        $result = $this->drive->files->listFiles([
            'q' => "'{$folderEscaped}' in parents and trashed = false and appProperties has { key='rnd_upload_ticket' and value='{$ticket}' }",
            'fields' => 'nextPageToken,files(id,name,mimeType,size,parents,appProperties,trashed)',
            'pageSize' => 10,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);
        $files = $result->getFiles();
        if (count($files) > 1) {
            throw new RuntimeException('Ditemukan lebih dari satu file untuk ticket upload.');
        }
        if (!$files) return null;
        return ['id' => $files[0]->getId()];
    }

    /** Verify the Drive object before the application records it. */

    public function verifyBrowserUpload(string $id, string $folderId, string $name, int $size, string $ticket): array

    {

        $f = $this->drive->files->get($id, [

            'fields' => 'id,name,mimeType,size,parents,trashed,webViewLink,appProperties',

            'supportsAllDrives' => true,

        ]);

        if ($f->getTrashed() || $f->getName() !== $name ||

            !in_array($folderId, $f->getParents() ?: [], true) ||

            (int) $f->getSize() !== $size ||

            ($f->getAppProperties()['rnd_upload_ticket'] ?? null) !== $ticket) {

            throw new RuntimeException('File Google Drive tidak sesuai sesi upload.');

        }

        return ['id' => $f->getId(), 'name' => $f->getName(),

            'mime_type' => $f->getMimeType(), 'size' => (int) $f->getSize()];

    }

    public function uploadFile(

        string $localPath,

        string $fileName,

        string $folderId,

        string $mimeType

    ): array {

        $uploadStartedAt = microtime(true);

        if (!is_file($localPath) || !is_readable($localPath)) {

            throw new RuntimeException(

                'File lokal tidak ditemukan atau tidak dapat dibaca.'

            );

        }

        if (trim($folderId) === '') {

            throw new RuntimeException('ID folder Google Drive tidak valid.');

        }

        $fileSize = filesize($localPath);

        if ($fileSize === false) {

            throw new RuntimeException('Ukuran file tidak dapat dibaca.');

        }

        $uploadMode = ($fileSize > 0 && $fileSize <= 8 * 1024 * 1024) ? 'multipart' : 'resumable';

        Log::info('RND Drive upload START', [

            'mode' => $uploadMode,

            'size_mb' => round($fileSize / 1048576, 2),

        ]);

        // Small photos: a single multipart request avoids the resumable

        // session setup and per-chunk round trips on shared hosting.

        // Keep the limit bounded to avoid high PHP memory usage.

        if ($fileSize > 0 && $fileSize <= 8 * 1024 * 1024) {

            try {

                $contents = file_get_contents($localPath);

                if ($contents === false) {

                    throw new RuntimeException('File gagal dibaca untuk upload.');

                }

                $file = new DriveFile([

                    'name' => $fileName,

                    'parents' => [$folderId],

                ]);

                $driveRequestAt = microtime(true);

                $result = $this->drive->files->create($file, [

                    'data' => $contents,

                    'mimeType' => $mimeType,

                    'uploadType' => 'multipart',

                    'fields' => 'id,name,mimeType,webViewLink,parents,size',

                    'supportsAllDrives' => true,

                ]);

                Log::info('RND Drive upload COMPLETE', [

                    'mode' => 'multipart',

                    'size_mb' => round($fileSize / 1048576, 2),

                    'drive_request_ms' => round((microtime(true) - $driveRequestAt) * 1000),

                    'total_ms' => round((microtime(true) - $uploadStartedAt) * 1000),

                ]);

                $id = $result->getId();

                if (!$id) {

                    throw new RuntimeException('Google Drive tidak mengembalikan ID file.');

                }

                return [

                    'id' => $id,

                    'name' => $result->getName(),

                    'mime_type' => $result->getMimeType(),

                    'url' => $result->getWebViewLink()

                        ?: 'https://drive.google.com/file/d/' . $id . '/view',

                    'size' => $result->getSize(),

                ];

            } catch (Throwable $e) {

                Log::warning('RND Drive upload FAILED', [

                    'mode' => 'multipart',

                    'size_mb' => round($fileSize / 1048576, 2),

                    'total_ms' => round((microtime(true) - $uploadStartedAt) * 1000),

                    'error_class' => get_class($e),

                ]);

                throw new RuntimeException(

                    'Upload Google Drive gagal: ' . $e->getMessage(),

                    (int) $e->getCode(),

                    $e

                );

            }

        }

        $handle = fopen($localPath, 'rb');

        if ($handle === false) {

            throw new RuntimeException('File gagal dibuka untuk upload.');

        }

        $chunkSize = 16 * 1024 * 1024; // 16 MB: fewer round-trips for large files

        $deferWasEnabled = false;

        $chunkIndex = 0;

        $chunkNetworkMs = 0;

        try {

            $file = new DriveFile([

                'name' => $fileName,

                'parents' => [$folderId],

            ]);

            /*

             * Mode defer diperlukan agar request Drive tidak langsung

             * dieksekusi sebelum MediaFileUpload mengirimkan chunk.

             */

            $this->client->setDefer(true);

            $deferWasEnabled = true;

            $request = $this->drive->files->create(

                $file,

                [

                    'uploadType' => 'resumable',

                    'fields' => 'id,name,mimeType,webViewLink,parents,size',

                    'supportsAllDrives' => true,

                ]

            );

            $media = new MediaFileUpload(

                $this->client,

                $request,

                $mimeType,

                null,

                true,

                $chunkSize

            );

            $media->setFileSize($fileSize);

            $result = false;

            /*

             * File dibaca per chunk, sehingga penggunaan memori lebih kecil.

             */

            while (!$result && !feof($handle)) {

                $chunk = fread($handle, $chunkSize);

                if ($chunk === false) {

                    throw new RuntimeException(

                        'Gagal membaca potongan file untuk upload.'

                    );

                }

                if ($chunk === '' && feof($handle)) {

                    break;

                }

                if ($chunk === '') {

                    throw new RuntimeException(

                        'Pembacaan file kosong sebelum upload selesai.'

                    );

                }

                $chunkIndex++;

                $chunkStartedAt = microtime(true);

                $result = $media->nextChunk($chunk);

                $elapsedMs = round((microtime(true) - $chunkStartedAt) * 1000);

                $chunkNetworkMs += $elapsedMs;

                Log::info('RND Drive upload CHUNK', [

                    'chunk' => $chunkIndex,

                    'chunk_mb' => round(strlen($chunk) / 1048576, 2),

                    'request_ms' => $elapsedMs,

                ]);

            }

            if ($result === false) {

                throw new RuntimeException(

                    'Upload Google Drive belum menghasilkan respons selesai.'

                );

            }

            Log::info('RND Drive upload COMPLETE', [

                'mode' => 'resumable',

                'size_mb' => round($fileSize / 1048576, 2),

                'chunks' => $chunkIndex,

                'chunk_request_ms' => $chunkNetworkMs,

                'total_ms' => round((microtime(true) - $uploadStartedAt) * 1000),

            ]);

            $id = $result->getId();

            if (empty($id)) {

                throw new RuntimeException(

                    'Google Drive tidak mengembalikan ID file setelah upload.'

                );

            }

            return [

                'id' => $id,

                'name' => $result->getName(),

                'mime_type' => $result->getMimeType(),

                'url' => $result->getWebViewLink()

                    ?: 'https://drive.google.com/file/d/' . $id . '/view',

                'size' => $result->getSize(),

            ];

        } catch (Throwable $e) {

            Log::warning('RND Drive upload FAILED', [

                'mode' => 'resumable',

                'size_mb' => round($fileSize / 1048576, 2),

                'chunks' => $chunkIndex,

                'total_ms' => round((microtime(true) - $uploadStartedAt) * 1000),

                'error_class' => get_class($e),

            ]);

            throw new RuntimeException(

                'Upload Google Drive gagal: ' . $e->getMessage(),

                (int) $e->getCode(),

                $e

            );

        } finally {

            if (is_resource($handle)) {

                fclose($handle);

            }

            if ($deferWasEnabled) {

                $this->client->setDefer(false);

            }

        }

    }

    /**

     * Membaca folder Google Drive, hanya anak langsungnya.

     */

    public function listFolderContents(string $folderId): array

    {

        if (trim($folderId) === '') {

            throw new RuntimeException('ID folder Google Drive kosong.');

        }

        $items = [];

        $pageToken = null;

        do {

            $escapedFolderId = str_replace(

                ["\\\\", "'"],

                ["\\\\\\\\", "\\\\'"],

                $folderId

            );

            $params = [

                'q' => "'" . $escapedFolderId . "' in parents and trashed = false",

                'pageSize' => 1000,

                'orderBy' => 'folder,name',

                'fields' => 'nextPageToken,files(id,name,mimeType,size,webViewLink,createdTime,modifiedTime,parents)',

                'supportsAllDrives' => true,

                'includeItemsFromAllDrives' => true,

            ];

            if ($pageToken) {

                $params['pageToken'] = $pageToken;

            }

            $result = $this->drive->files->listFiles($params);

            foreach ($result->getFiles() as $file) {

                $isFolder = $file->getMimeType()

                    === 'application/vnd.google-apps.folder';

                $id = $file->getId();

                $items[] = [

                    'id' => $id,

                    'name' => $file->getName(),

                    'type' => $isFolder ? 'folder' : 'file',

                    'mime_type' => $file->getMimeType(),

                    'size' => $file->getSize(),

                    'url' => $file->getWebViewLink()

                        ?: (

                            $isFolder

                                ? 'https://drive.google.com/drive/folders/' . $id

                                : 'https://drive.google.com/file/d/' . $id . '/view'

                        ),

                    'created_time' => $file->getCreatedTime(),

                    'modified_time' => $file->getModifiedTime(),

                ];

            }

            $pageToken = $result->getNextPageToken();

        } while ($pageToken);

        return $items;

    }

    /**

     * Mengganti nama file atau folder di Google Drive.

     */

    public function renameItem(string $itemId, string $newName): array

    {

        $newName = trim($newName);

        if ($newName === '') {

            throw new \InvalidArgumentException(

                'Nama file atau folder tidak boleh kosong.'

            );

        }

        $updated = $this->drive->files->update(

            $itemId,

            new DriveFile(['name' => $newName]),

            [

                'fields' => 'id,name,mimeType,webViewLink,parents',

                'supportsAllDrives' => true,

            ]

        );

        $id = $updated->getId();

        return [

            'id' => $id,

            'name' => $updated->getName(),

            'mime_type' => $updated->getMimeType(),

            'url' => $updated->getWebViewLink()

                ?: (

                    $updated->getMimeType() === 'application/vnd.google-apps.folder'

                        ? 'https://drive.google.com/drive/folders/' . $id

                        : 'https://drive.google.com/file/d/' . $id . '/view'

                ),

        ];

    }

    /**

     * Memindahkan file/folder ke Trash Google Drive, bukan menghapus permanen.

     */

    public function trashItem(string $itemId): bool

    {

        $this->drive->files->update(

            $itemId,

            new DriveFile(['trashed' => true]),

            [

                'fields' => 'id,trashed',

                'supportsAllDrives' => true,

            ]

        );

        return true;

    }

    /** Metadata asli diperlukan agar parent Drive diverifikasi sebelum perubahan. */

    public function getArchiveItem(string $id): array

    {

        $f = $this->drive->files->get($id, [

            'fields' => 'id,name,mimeType,parents,trashed', 'supportsAllDrives' => true,

        ]);

        return ['id'=>$f->getId(), 'name'=>$f->getName(), 'mime_type'=>$f->getMimeType(),

            'parents'=>$f->getParents() ?: [], 'trashed'=>(bool)$f->getTrashed()];

    }

    public function moveArchiveItem(string $id, string $from, string $to): array

    {

        $metadata = $this->getArchiveItem($id);

        if ($metadata['trashed'] || !in_array($from, $metadata['parents'], true)) {

            throw new \RuntimeException('Item tidak lagi berada di folder asal. Refresh arsip.');

        }

        if ($from === $to) throw new \InvalidArgumentException('Folder asal dan tujuan sama.');

        $f = $this->drive->files->update($id, new DriveFile(), [

            'addParents'=>$to, 'removeParents'=>$from,

            'fields'=>'id,name,mimeType,parents', 'supportsAllDrives'=>true,

        ]);

        return ['id'=>$f->getId(), 'name'=>$f->getName()];

    }

    public function copyArchiveItem(string $id, string $targetId, ?string $name = null): array

    {

        $metadata = $this->getArchiveItem($id);

        if ($metadata['trashed'] || $metadata['mime_type'] === 'application/vnd.google-apps.folder') {

            throw new \InvalidArgumentException('Penyalinan folder belum didukung.');

        }

        $f = $this->drive->files->copy($id, new DriveFile([

            'name'=>$name ?: $metadata['name'], 'parents'=>[$targetId],

        ]), ['fields'=>'id,name,mimeType,size,parents','supportsAllDrives'=>true]);

        return ['id'=>$f->getId(),'name'=>$f->getName(), 'mime_type'=>$f->getMimeType(), 'size'=>$f->getSize()];

    }

    /** Stream a Google Drive binary file to disk without loading it fully into PHP memory. */
    public function downloadFileToPath(string $fileId, string $destination): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{10,200}$/', $fileId)) {
            throw new RuntimeException('Google Drive file ID tidak valid.');
        }
        $token = $this->client->getAccessToken()['access_token'] ?? null;
        if (!$token) throw new RuntimeException('Token Google Drive tidak tersedia.');
        $url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media&supportsAllDrives=true';
        try {
            $this->client->getHttpClient()->request('GET', $url, [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'sink' => $destination,
                'http_errors' => true,
                'connect_timeout' => 20,
                'timeout' => 240,
            ]);
        } catch (Throwable $e) {
            @unlink($destination);
            throw new RuntimeException('Download file Google Drive gagal: ' . $e->getMessage(), 0, $e);
        }
    }

    /** Open Google Drive media as a streamed HTTP response, without local disk buffering. */
    public function openDownloadStream(string $fileId): \Psr\Http\Message\ResponseInterface
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{10,200}$/', $fileId)) {
            throw new \RuntimeException('Google Drive file ID tidak valid.');
        }

        $token = $this->client->getAccessToken()['access_token'] ?? null;
        if (!$token) {
            throw new \RuntimeException('Token Google Drive tidak tersedia.');
        }

        return $this->client->getHttpClient()->request('GET',
            'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fileId) . '?alt=media&supportsAllDrives=true',
            [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'stream' => true,
                'http_errors' => true,
                'connect_timeout' => 20,
                'timeout' => 240,
            ]
        );
    }


}
