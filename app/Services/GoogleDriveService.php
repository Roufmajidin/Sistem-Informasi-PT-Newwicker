<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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
     * Membaca token dari storage server.
     * Refresh access token secara otomatis jika kedaluwarsa.
     */
    private function loadToken(): void
    {
        $storage = Storage::disk('local');

        if (!$storage->exists($this->tokenPath)) {
            throw new RuntimeException(
                'Token Google Drive tidak ditemukan. '
                . 'Admin IT perlu menghubungkan Google Drive terlebih dahulu.'
            );
        }

        $token = json_decode(
            $storage->get($this->tokenPath),
            true
        );

        if (
            !is_array($token) ||
            empty($token['access_token'])
        ) {
            throw new RuntimeException(
                'File token Google Drive tidak valid.'
            );
        }

        $this->client->setAccessToken($token);

        // Access token masih berlaku: tidak perlu refresh.
        if (!$this->client->isAccessTokenExpired()) {
            return;
        }

        $refreshToken = $token['refresh_token']
            ?? $this->client->getRefreshToken();

        if (empty($refreshToken)) {
            throw new RuntimeException(
                'Refresh token tidak tersedia. '
                . 'Periksa koneksi Google Drive akun IT.'
            );
        }

        $newToken = $this->client
            ->fetchAccessTokenWithRefreshToken($refreshToken);

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

            throw new RuntimeException(
                'Refresh token gagal: ' . $error
            );
        }

        // Google tidak selalu mengembalikan refresh token baru.
        if (empty($newToken['refresh_token'])) {
            $newToken['refresh_token'] = $refreshToken;
        }

        // Gabungkan token baru dengan token lama.
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
     * Mengunggah file ke folder Google Drive.
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
            throw new RuntimeException(
                'ID folder Google Drive tidak valid.'
            );
        }

        $contents = file_get_contents($localPath);

        if ($contents === false) {
            throw new RuntimeException(
                'File gagal dibaca sebelum diunggah.'
            );
        }

        $file = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $created = $this->drive->files->create(
            $file,
            [
                'data' => $contents,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,webViewLink,parents,size',
                'supportsAllDrives' => true,
            ]
        );

        $id = $created->getId();

        return [
            'id' => $id,
            'name' => $created->getName(),
            'mime_type' => $created->getMimeType(),
            'url' => $created->getWebViewLink()
                ?: 'https://drive.google.com/file/d/' . $id . '/view',
            'size' => $created->getSize(),
        ];
    }
}