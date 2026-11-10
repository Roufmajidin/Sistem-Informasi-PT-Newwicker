<?php

namespace App\Services;

use App\Events\PengajuanHargaNotificationCreated;
use App\Models\PengajuanHargaPekerjaan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanHargaNotifier
{
    public function notifyJob(
        PengajuanHargaPekerjaan $job,
        string $type,
        User $actor
    ): void {
        try {
            // Semua user yang memiliki hak akses Pengajuan Harga
            $users = User::query()
                ->whereHas('accessModules', function ($query) {
                    $query->where('name', 'Pengajuan Harga');
                })
                ->get(['id', 'name', 'email']);

            $titles = [
                'created' => 'Pengajuan Baru',
                'updated' => 'Pengajuan Diperbarui',
                'director_updated' => 'Revisi Director / updated Director',
                'closed' => 'Pengajuan Closed',
            ];

            $title = $titles[$type] ?? 'Aktivitas Pengajuan';

            $message = $actor->name
                . ' · PO ' . ($job->po_code ?: '-')
                . ' · ' . ($job->article_code ?: '-')
                . ' · ' . ($job->sub_name ?: 'Pengajuan Harga');

            foreach ($users as $user) {

                // Aktor tidak menerima notifikasi dari tindakannya sendiri
                if ((int) $user->id === (int) $actor->id) {
                    continue;
                }

                // URL tujuan menggunakan URL yang tersimpan
                // pada record notifikasi.
                $url = '/pengajuan-harga';

                // Simpan notifikasi untuk setiap penerima
                $id = DB::table('pengajuan_harga_notifications')
                    ->insertGetId([
                        'user_id' => $user->id,
                        'actor_id' => $actor->id,
                        'pekerjaan_id' => $job->id,
                        'po_id' => $job->po_id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                // Kirim realtime melalui channel masing-masing user
                try {
                    broadcast(
                        new PengajuanHargaNotificationCreated(
                            (int) $user->id,
                            [
                                'id' => $id,
                                'title' => $title,
                                'message' => $message,
                                'url' => $url,
                                'type' => $type,
                                'pekerjaan_id' => $job->id,
                                'created_at' => now()->toDateTimeString(),
                            ]
                        )
                    );
                } catch (\Throwable $e) {
                    Log::warning(
                        'Pengajuan Harga realtime notification failed',
                        [
                            'recipient_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]
                    );
                }
            }

        } catch (\Throwable $e) {
            Log::error(
                'Pengajuan Harga notification persistence failed',
                [
                    'job_id' => $job->id,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
