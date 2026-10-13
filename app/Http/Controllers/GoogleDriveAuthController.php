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
    private string $tokenPath = 'google-drive-token.json';

    /**
     * Konfigurasi Google OAuth.
     */
    private function googleClient(): Client
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');
        $redirectUri = config('services.google_drive.redirect_uri');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            throw new RuntimeException(
                'Konfigurasi Google Drive belum lengkap. '
                . 'Periksa konfigurasi pada .env hosting.'
            );
        }

        $client = new Client();

        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);

        // Meminta refresh token untuk akses jangka panjang.
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setScopes([Drive::DRIVE]);

        return $client;
    }

    /**
     * Memulai OAuth.
     * Route ini sebaiknya hanya boleh diakses admin IT.
     */
    public function connect()
    {
        try {
            $client = $this->googleClient();

            // State melindungi proses callback OAuth.
            $state = bin2hex(random_bytes(32));

            session([
                'google_drive_oauth_state' => $state,
            ]);

            $client->setState($state);

            return redirect()->away(
                $client->createAuthUrl()
            );
        } catch (Throwable $e) {
            report($e);

            return response(
                'Google Drive belum siap. Periksa konfigurasi '
                . 'dan log Laravel.',
                500
            );
        }
    }

    /**
     * Callback Google OAuth.
     * Token disimpan pada disk local Laravel di server.
     */
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return response(
                'Otorisasi Google Drive dibatalkan atau ditolak.',
                403
            );
        }

        $expectedState = session('google_drive_oauth_state');
        $receivedState = $request->query('state');

        if (
            !is_string($expectedState) ||
            !is_string($receivedState) ||
            !hash_equals($expectedState, $receivedState)
        ) {
            return response(
                'Sesi OAuth tidak valid atau kedaluwarsa. '
                . 'Silakan mulai koneksi kembali.',
                403
            );
        }

        session()->forget('google_drive_oauth_state');

        if (!$request->filled('code')) {
            return response(
                'Authorization code Google tidak ditemukan.',
                400
            );
        }

        try {
            $client = $this->googleClient();

            $token = $client->fetchAccessTokenWithAuthCode(
                $request->query('code')
            );

            if (
                !is_array($token) ||
                isset($token['error']) ||
                empty($token['access_token'])
            ) {
                $error = is_array($token)
                    ? (
                        $token['error_description']
                        ?? $token['error']
                        ?? 'Respons token tidak valid.'
                    )
                    : 'Respons Google tidak valid.';

                return response(
                    'Gagal memperoleh token Google Drive: ' . $error,
                    400
                );
            }

            $storage = Storage::disk('local');

            /*
             * Pertahankan refresh token lama apabila Google
             * tidak mengirim refresh token pada respons baru.
             */
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

            if (empty($token['refresh_token'])) {
                return response(
                    'Refresh token tidak diterima. Periksa pengaturan '
                    . 'OAuth Google dan ulangi proses koneksi akun IT.',
                    400
                );
            }

            $json = json_encode(
                $token,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );

            if ($json === false) {
                throw new RuntimeException(
                    'Token gagal dikonversi menjadi JSON.'
                );
            }

            // Simpan ke storage privat Laravel, bukan ke browser.
            if (!$storage->put($this->tokenPath, $json)) {
                throw new RuntimeException(
                    'Token gagal disimpan di server.'
                );
            }

            // Verifikasi penyimpanan tanpa menampilkan nilai rahasia.
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
                    'File token tersimpan tetapi formatnya tidak valid.'
                );
            }

            return response(
                'Google Drive berhasil dihubungkan. '
                . 'Token tersimpan secara privat di server Laravel.'
            );
        } catch (Throwable $e) {
            report($e);

            return response(
                'Koneksi Google Drive gagal. Periksa '
                . 'storage/logs/laravel.log.',
                500
            );
        }
    }
}
