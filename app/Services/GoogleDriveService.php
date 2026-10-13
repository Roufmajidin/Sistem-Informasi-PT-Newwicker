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

        $this->client->setScopes([
            Drive::DRIVE,
        ]);

        $this->loadToken();

        $this->drive = new Drive($this->client);
    }

    /**
     * Membaca token OAuth dan memperbaruinya jika kedaluwarsa.
     */
    private function loadToken(): void
    {
        $storage = Storage::disk('local');

        if (!$storage->exists($this->tokenPath)) {
            throw new RuntimeException(
                'Token Google Drive tidak ditemukan. Hubungkan akun IT terlebih dahulu.'
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
                'Token Google Drive tidak valid.'
            );
        }

        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();

            if (!$refreshToken) {
                throw new RuntimeException(
                    'Refresh token tidak tersedia. Hubungkan ulang akun IT.'
                );
            }

            $newToken = $this->client
                ->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {
                throw new RuntimeException(
                    'Gagal memperbarui token Google Drive: ' .
                    ($newToken['error_description']
                        ?? $newToken['error'])
                );
            }

            // Pertahankan refresh token jika respons baru
            // tidak menyertakannya.
            if (empty($newToken['refresh_token'])) {
                $newToken['refresh_token'] = $refreshToken;
            }

            $storage->put(
                $this->tokenPath,
                json_encode(
                    $this->client->getAccessToken(),
                    JSON_PRETTY_PRINT
                )
            );
        }
    }

    /**
     * Membuat folder di dalam folder induk Google Drive.
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

        return [
            'id' => $created->getId(),
            'name' => $created->getName(),
            'url' => $created->getWebViewLink(),
        ];
    }

    /**
     * Upload file ke folder Google Drive.
     */
    public function uploadFile(
        string $localPath,
        string $fileName,
        string $folderId,
        string $mimeType
    ): array {
        if (!is_file($localPath)) {
            throw new RuntimeException(
                'File lokal tidak ditemukan.'
            );
        }

        $file = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $created = $this->drive->files->create(
            $file,
            [
                'data' => file_get_contents($localPath),
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,webViewLink,parents,size',
                'supportsAllDrives' => true,
            ]
        );

        return [
            'id' => $created->getId(),
            'name' => $created->getName(),
            'mime_type' => $created->getMimeType(),
            'url' => $created->getWebViewLink(),
            'size' => $created->getSize(),
        ];
    }
}
