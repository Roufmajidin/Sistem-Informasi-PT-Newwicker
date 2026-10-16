<?php

namespace App\Services;

use Google\Client;

use Google\Http\MediaFileUpload;

use Google\Service\Drive;

use Google\Service\Drive\DriveFile;

use Illuminate\Support\Facades\Storage;

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

    public function uploadFile(

        string $localPath,

        string $fileName,

        string $folderId,

        string $mimeType

    ): array {

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
                $result = $this->drive->files->create($file, [
                    'data' => $contents,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id,name,mimeType,webViewLink,parents,size',
                    'supportsAllDrives' => true,
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

                $result = $media->nextChunk($chunk);

            }

            if ($result === false) {

                throw new RuntimeException(

                    'Upload Google Drive belum menghasilkan respons selesai.'

                );

            }

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

                ["\\", "'"],

                ["\\\\", "\\'"],

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

}
