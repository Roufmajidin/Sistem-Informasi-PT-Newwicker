<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GoogleDriveAuthController extends Controller
{
    /**
     * Lokasi token pada disk local Laravel.
     *
     * Jika root disk local adalah storage/app/private,
     * file ini berada di storage/app/private/google-drive-token.json.
     */
    private string $tokenPath = 'google-drive-token.json';

    /**
     * Membuat konfigurasi Google OAuth Client.
     */
    private function googleClient(): Client
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');
        $redirectUri = config('services.google_drive.redirect_uri');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            throw new RuntimeException(
                'Konfigurasi Google Drive belum lengkap. Periksa GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET, dan GOOGLE_DRIVE_REDIRECT_URI pada .env.'
            );
        }

        $client = new Client();

        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);

        // Meminta refresh token agar server dapat mengakses Drive
        // tanpa meminta staf R&D login Google berulang kali.
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setScopes([
            Drive::DRIVE,
        ]);

        return $client;
    }

    /**
     * Mengarahkan akun IT ke halaman otorisasi Google.
     */
    public function connect()
    {
        try {
            $client = $this->googleClient();

            return redirect()->away(
                $client->createAuthUrl()
            );
        } catch (Throwable $e) {
            report($e);

            return response(
                'Konfigurasi Google Drive belum siap. Periksa konfigurasi server dan log Laravel.',
                500
            );
        }
    }

    /**
     * Menerima callback Google dan menyimpan token OAuth.
     */
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return response(
                'Otorisasi Google Drive dibatalkan atau ditolak.',
                403
            );
        }

        if (!$request->filled('code')) {
            return response(
                'Authorization code dari Google tidak ditemukan.',
                400
            );
        }

        try {
            $client = $this->googleClient();

            // Tukar authorization code dengan access token.
            $token = $client->fetchAccessTokenWithAuthCode(
                $request->query('code')
            );

            if (!is_array($token) || isset($token['error'])) {
                $error = is_array($token)
                    ? ($token['error_description'] ?? $token['error'] ?? 'Unknown error')
                    : 'Respons token tidak valid';

                return response(
                    'Gagal mendapatkan token Google Drive: ' . $error,
                    400
                );
            }

            // Pastikan yang diterima benar-benar token akses,
            // bukan file konfigurasi OAuth Client.
            if (empty($token['access_token'])) {
                return response(
                    'Google tidak mengembalikan access token. Silakan ulangi proses koneksi.',
                    400
                );
            }

            $storage = Storage::disk('local');

            // Pertahankan refresh token sebelumnya jika Google
            // tidak memberikan refresh token baru.
            if ($storage->exists($this->tokenPath)) {
                $oldToken = json_decode(
                    $storage->get($this->tokenPath),
                    true
                );

                if (
                    is_array($oldToken) &&
                    !empty($oldToken['refresh_token']) &&
                    empty($token['refresh_token'])
                ) {
                    $token['refresh_token'] =
                        $oldToken['refresh_token'];
                }
            }

            // Refresh token dibutuhkan untuk akses berkelanjutan
            // tanpa login Google setiap kali token akses kedaluwarsa.
            if (empty($token['refresh_token'])) {
                return response(
                    'Refresh token tidak diterima. Periksa pengaturan OAuth dan ulangi persetujuan menggunakan akun IT.',
                    400
                );
            }

            // Pastikan direktori tujuan tersedia.
            $storage->makeDirectory(''); // Root disk local.

            $json = json_encode(
                $token,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );

            if ($json === false) {
                throw new RuntimeException(
                    'Token Google gagal dikonversi menjadi JSON.'
                );
            }

            // Simpan hanya token hasil OAuth.
            $saved = $storage->put(
                $this->tokenPath,
                $json
            );

            if (!$saved || !$storage->exists($this->tokenPath)) {
                throw new RuntimeException(
                    'Token Google gagal disimpan ke penyimpanan Laravel.'
                );
            }

            // Verifikasi hasil penyimpanan tanpa menampilkan rahasia.
            $savedToken = json_decode(
                $storage->get($this->tokenPath),
                true
            );

            if (
                !is_array($savedToken) ||
                empty($savedToken['access_token']) ||
                empty($savedToken['refresh_token'])
            ) {
                throw new RuntimeException(
                    'File token tersimpan tetapi format token tidak valid.'
                );
            }

            return response(
                'Google Drive berhasil dihubungkan. Token OAuth tersimpan secara privat di Laravel.'
            );
        } catch (Throwable $e) {
            report($e);

            return response(
                'Koneksi Google Drive gagal. Periksa storage/logs/laravel.log untuk detailnya.',
                500
            );
        }
    }
}