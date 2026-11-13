<?php

namespace App\Http\Controllers;

use App\Models\SpkScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomingWarehouseController extends Controller
{
    public function index(): View
    {
        return view('pages.incoming.index');
    }

    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = SpkScanLog::query()->where('status', 'success');
        $search = trim($validated['search'] ?? '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('qr_raw', 'like', '%' . $search . '%')
                  ->orWhere('spk_id', 'like', '%' . $search . '%')
                  ->orWhere('detail_po_id', 'like', '%' . $search . '%');
            });
        }

        $todayCount = (clone $query)->whereDate('scanned_at', today())->count();
        $totalCount = (clone $query)->count();
        $uniqueSpk = (clone $query)->distinct()->count('spk_id');
        $latest = (clone $query)->with('user')->orderByDesc('scanned_at')->orderByDesc('id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        // Resolve SPK metadata in one query instead of one query per scan.
        $spkIds = $latest->getCollection()->pluck('spk_id')->filter()->unique()->values();
        $spks = \App\Models\Spk::query()->whereIn('id', $spkIds)->get()->keyBy('id');

        $rows = $latest->getCollection()->map(function ($log) use ($spks) {
            $spk = $spks->get($log->spk_id);
            $data = $spk?->data;
            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            $data = is_array($data) ? $data : [];
            $itemName = '';
            foreach (($data['items'] ?? []) as $item) {
                if (is_array($item) && (int) ($item['detail_po_id'] ?? 0) === (int) $log->detail_po_id) {
                    $itemName = (string) ($item['nama'] ?? $item['name'] ?? '');
                    break;
                }
            }
            return [
                'id' => $log->id,
                'spk_id' => $log->spk_id,
                'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $log->spk_id),
                'no_po' => (string) ($data['no_po'] ?? ''),
                'subkon' => (string) ($data['sup'] ?? $data['supplier'] ?? ''),
                'detail_po_id' => $log->detail_po_id,
                'item_name' => $itemName,
                'sequence' => $log->sequence,
                'qty' => $log->qty,
                'label' => $log->sequence . '/' . $log->qty,
                'user_id' => $log->user_id,
                'user_name' => (string) ($log->user?->name ?? $log->user?->nama ?? ($log->user_id ? 'User #' . $log->user_id : '—')),
                'scan_method' => $log->scan_method,
                'scanned_at' => $log->scanned_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'summary' => [
                'today' => $todayCount,
                'total' => $totalCount,
                'spk' => $uniqueSpk,
            ],
            'rows' => $rows->values(),
            'pagination' => [
                'current_page' => $latest->currentPage(),
                'last_page' => $latest->lastPage(),
                'total' => $latest->total(),
                'per_page' => $latest->perPage(),
            ],
            'server_time' => now()->toDateTimeString(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
