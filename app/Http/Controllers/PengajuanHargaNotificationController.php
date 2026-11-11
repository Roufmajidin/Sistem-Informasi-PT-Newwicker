<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanHargaNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pengajuan_harga_notifications')
            ->where('user_id', $request->user()->id);

        return response()->json([
            'unread' => (clone $query)->whereNull('read_at')->count(),
            'notifications' => $query->orderByDesc('id')->limit(30)->get(),
        ]);
    }

    public function open(Request $request, int $id)
    {
        $row = DB::table('pengajuan_harga_notifications')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($row, 404);

        DB::table('pengajuan_harga_notifications')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        $email = strtolower(trim((string) $request->user()->email));
        $management = collect(['info@', 'office@', 'factory@', 'manager@', 'director@', 'admin@'])
            ->contains(fn($prefix) => str_starts_with($email, $prefix));

        $destination = $management ? '/pengajuan-harga/directur' : '/pengajuan-harga';

        // The notification producer must store the related job ID in a supported field.
        // Do not guess IDs from notification text, or trust a stored redirect URL.
        $payload = json_decode((string) ($row->data ?? '{}'), true);
        $payload = is_array($payload) ? $payload : [];
        $jobId = $row->pekerjaan_id ?? $row->job_id
            ?? $payload['pekerjaan_id'] ?? $payload['job_id'] ?? null;
        $detailId = $row->detail_po_id ?? $row->detail_id
            ?? $payload['detail_po_id'] ?? $payload['detail_id'] ?? null;

        if (!$management && filter_var($jobId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return redirect()->to($destination . '?' . http_build_query(array_filter([
                'highlight_job' => (int) $jobId,
                'highlight_detail' => filter_var($detailId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                    ? (int) $detailId : null,
            ], fn($value) => $value !== null)));
        }

        return redirect()->to($destination);
    }
}
