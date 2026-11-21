<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\SpkScanLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'label' => ['nullable', 'string', 'max:100'],
            'labels_only' => ['nullable', 'boolean'],
            'grouped' => ['nullable', 'boolean'],
            'group_spk_id' => ['nullable', 'integer'],
            'group_detail_po_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $base = SpkScanLog::query()->where('status', 'success');

        // Ringkasan kelompok berdasarkan SPK dan detail PO (nama item/PO berasal dari SPK).
        if ($request->boolean('labels_only')) {
            $groups = (clone $base)->select('spk_id', 'detail_po_id', DB::raw('COUNT(*) as scan_count'))
                ->groupBy('spk_id', 'detail_po_id')->orderByDesc('scan_count')->get();
            $spks = Spk::whereIn('id', $groups->pluck('spk_id')->filter()->unique())->get()->keyBy('id');
            $labels = $groups->map(function ($g) use ($spks) {
                $data = $spks->get($g->spk_id)?->data;
                $data = is_string($data) ? json_decode($data, true) : $data;
                $data = is_array($data) ? $data : [];
                $itemName = '';
                foreach (($data['items'] ?? []) as $item) {
                    if (is_array($item) && (int) ($item['detail_po_id'] ?? 0) === (int) $g->detail_po_id) {
                        $itemName = (string) ($item['nama'] ?? $item['name'] ?? '');
                        break;
                    }
                }
                return [
                    'spk_id' => $g->spk_id,
                    'detail_po_id' => $g->detail_po_id,
                    'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $g->spk_id),
                    'no_po' => (string) ($data['no_po'] ?? ''),
                    'item_name' => $itemName,
                    'total' => (int) $g->scan_count
                ];
            });
            return response()->json(['success' => true, 'groups' => $labels])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        $query = clone $base;
        $search = trim((string) ($validated['search'] ?? ''));
        $grouped = $request->boolean('grouped');
        $groupSpkId = $validated['group_spk_id'] ?? null;
        $groupDetailId = $validated['group_detail_po_id'] ?? null;

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $pattern = '%' . addcslashes($search, '%_\\') . '%';
                $q->where('qr_raw', 'like', $pattern)
                    ->orWhere('spk_id', 'like', $pattern)
                    ->orWhere('detail_po_id', 'like', $pattern)
                    // Metadata SPK (no_spk, no_po, nama item, supplier) tersimpan di kolom JSON data.
                    ->orWhereIn('spk_id', Spk::query()->select('id')->where('data', 'like', $pattern));
            });
        }

        if ($groupSpkId !== null)
            $query->where('spk_id', $groupSpkId);
        if ($groupDetailId !== null)
            $query->where('detail_po_id', $groupDetailId);

        // Ringkasan mengikuti filter aktif, sesuai isi tabel.
        $todayCount = (clone $query)->whereDate('scanned_at', today())->count();
        $totalCount = (clone $query)->count();
        $uniqueSpk = (clone $query)->whereNotNull('spk_id')->distinct()->count('spk_id');

        if ($grouped) {
            // Grouping sebelum pagination: satu baris per SPK + detail PO.
            $groupQuery = (clone $query)->select(
                'spk_id',
                'detail_po_id',
                DB::raw('COUNT(*) as scan_count'),
                DB::raw('MAX(scanned_at) as last_scan')
            )
                ->groupBy('spk_id', 'detail_po_id')
                ->orderByDesc('last_scan');
            $groupPage = $groupQuery->paginate((int) ($validated['per_page'] ?? 25));
            $keys = $groupPage->getCollection();
            $spks = Spk::whereIn('id', $keys->pluck('spk_id')->filter()->unique())->get()->keyBy('id');
            $rows = $keys->map(function ($g) use ($spks, $base) {
                $data = $spks->get($g->spk_id)?->data;
                $data = is_string($data) ? json_decode($data, true) : $data;
                $data = is_array($data) ? $data : [];
                $name = '';
                foreach (($data['items'] ?? []) as $item) {
                    if (is_array($item) && (int) ($item['detail_po_id'] ?? 0) === (int) $g->detail_po_id) {
                        $name = (string) ($item['nama'] ?? $item['name'] ?? '');
                        break;
                    }
                }
                $logs = (clone $base)->where('spk_id', $g->spk_id)
                    ->where('detail_po_id', $g->detail_po_id)
                    ->with('user')->orderByDesc('scanned_at')->get();
                $latestLog = $logs->first();
                return [
                    'spk_id' => $g->spk_id,
                    'detail_po_id' => $g->detail_po_id,
                    'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $g->spk_id),
                    'no_po' => (string) ($data['no_po'] ?? ''),
                    'item_name' => $name,
                    'subkon' => (string) ($data['sup'] ?? $data['supplier'] ?? ''),
                    'total' => (int) $g->scan_count,
                    'scan_count' => (int) $g->scan_count,
                    'scan_method' => $latestLog?->scan_method ?? '—',
                    'user_name' => (string) ($latestLog?->user?->name ?? $latestLog?->user?->nama ?? '—'),
                    'scanned_at' => $latestLog?->scanned_at?->format('Y-m-d H:i:s'),
                    'labels' => $logs->map(fn($log) => (string) $log->sequence . '/' . (string) $log->qty)->values()->all(),
                ];
            });
            return response()->json([
                'success' => true,
                'grouped' => true,
                'summary' => ['today' => $todayCount, 'total' => $totalCount, 'spk' => $uniqueSpk],
                'rows' => $rows->values(),
                'pagination' => [
                    'current_page' => $groupPage->currentPage(),
                    'last_page' => $groupPage->lastPage(),
                    'total' => $groupPage->total(),
                    'per_page' => $groupPage->perPage()
                ],
                'server_time' => now()->toDateTimeString(),
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        $latest = (clone $query)
            ->with('user')->orderByDesc('scanned_at')->orderByDesc('id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        $spkIds = $latest->getCollection()->pluck('spk_id')->filter()->unique()->values();
        $spks = Spk::query()->whereIn('id', $spkIds)->get()->keyBy('id');

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
                'total' => $log->qty,
                'label' => (string) $log->sequence . '/' . (string) $log->qty,
                'user_id' => $log->user_id,
                'user_name' => (string) ($log->user?->name ?? $log->user?->nama ?? ($log->user_id ? 'User #' . $log->user_id : '—')),
                'scan_method' => $log->scan_method,
                'scanned_at' => $log->scanned_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'summary' => ['today' => $todayCount, 'total' => $totalCount, 'spk' => $uniqueSpk],
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
