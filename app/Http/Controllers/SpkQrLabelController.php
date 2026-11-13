<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSpkQrPdf;
use App\Models\Spk;
use App\Models\DetailPo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SpkQrLabelController extends Controller
{
    private function getSpkData(int $id): array
    {
        $spk = Spk::findOrFail($id);
        $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
        abort_unless(is_array($data), 422, 'Data SPK tidak valid.');
        $items = [];
        $total = 0;
        foreach (($data['items'] ?? []) as $item) {
            if (!is_array($item)) continue;
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
            if ($qty === false || $qty < 1) continue;
            $total += $qty;
            abort_if($total > 2000, 422, 'Jumlah label melebihi 2000.');
            $items[] = [
                'detail_po_id' => (int) ($item['detail_po_id'] ?? $spk->detail_po_id ?? 0),
                'nama' => trim((string) ($item['nama'] ?? '-')),
                'kode' => trim((string) ($item['kode'] ?? '')),
                'material' => trim((string) ($item['material'] ?? '')),
                'qty' => $qty,
                'satuan' => (string) ($item['satuan'] ?? 'pcs'),
            ];
        }
        abort_if(!$items, 422, 'SPK tidak memiliki item dengan qty valid.');
        return [
            'spk' => $spk,
            'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $spk->id),
            'no_po' => (string) ($data['no_po'] ?? ''),
            'supplier' => (string) ($data['sup'] ?? ''),
            'items' => $items,
            'total' => $total,
        ];
    }

    public function preview(int $id): JsonResponse
    {
        $data = $this->getSpkData($id);
        return response()->json([
            'no_spk' => $data['no_spk'],
            'items' => $data['items'],
            'total_labels' => $data['total'],
            'download_url' => route('inventor.qr.download', ['id' => $id]),
        ]);
    }

    public function start(int $id): JsonResponse
    {
        $data = $this->getSpkData($id);
        $token = (string) Str::uuid();
        $userId = (int) auth()->id();
        Cache::put("spk_qr_job:$token", [
            'user_id' => $userId, 'spk_id' => $id, 'status' => 'queued',
            'current' => 0, 'total' => $data['total'], 'message' => 'Menunggu worker...',
        ], now()->addHours(6));
        GenerateSpkQrPdf::dispatch($id, $token, $userId)->onQueue('default');
        return response()->json([
            'token' => $token,
            'progress_url' => route('inventor.qr.progress', ['token' => $token]),
            'file_url' => route('inventor.qr.file', ['token' => $token]),
        ]);
    }

    private function ownedStatus(string $token): array
    {
        abort_unless(Str::isUuid($token), 404);
        $state = Cache::get("spk_qr_job:$token");
        abort_unless(is_array($state), 404, 'Proses tidak ditemukan atau kedaluwarsa.');
        abort_unless((int) ($state['user_id'] ?? 0) === (int) auth()->id(), 403);
        return $state;
    }

    public function progress(string $token): JsonResponse
    {
        $state = $this->ownedStatus($token);
        return response()->json([
            'status' => $state['status'], 'current' => $state['current'],
            'total' => $state['total'], 'message' => $state['message'] ?? '',
        ])->header('Cache-Control', 'no-store');
    }

    public function file(string $token)
    {
        $state = $this->ownedStatus($token);
        abort_unless($state['status'] === 'done', 409, 'PDF belum selesai.');
        $path = 'spk-qr/' . $token . '.pdf';
        abort_unless(Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path, $state['filename'] ?? 'SPK_QR.pdf');
    }

    public function download(int $id)
    {
        $data = $this->getSpkData($id);
        $token = (string) Str::uuid();
        $userId = (int) auth()->id();
        Cache::put("spk_qr_job:$token", [
            'user_id' => $userId, 'spk_id' => $id, 'status' => 'queued',
            'current' => 0, 'total' => $data['total'], 'message' => 'Menyiapkan PDF...',
        ], now()->addHours(6));
        (new GenerateSpkQrPdf($id, $token, $userId))->handle();
        return $this->file($token);
    }

    /** New, independent browser-print flow; existing PDF queue flow remains intact. */
    public function browserPrint(int $id)
    {
        $data = $this->getSpkData($id);
        $spk = $data['spk'];
        // Fetch PO detail records once, not once for every printed label.
        $detailIds = collect($data['items'])->pluck('detail_po_id')->filter()->unique()->values();
        $detailsById = DetailPo::query()->whereIn('id', $detailIds)->get()->keyBy('id');
        $labels = [];
        foreach ($data['items'] as $item) {
            $finishing = '';
            $detail = $detailsById->get($item['detail_po_id']);
            $raw = $detail ? $detail->detail : null; // DetailPo casts JSON detail to array.
            if (is_string($raw)) $raw = json_decode($raw, true);
            if (is_array($raw)) {
                // Supports a single JSON object or an array of PO detail objects.
                $candidates = array_is_list($raw) ? $raw : [$raw];
                $matched = null;
                foreach ($candidates as $candidate) {
                    if (!is_array($candidate)) continue;
                    $candidateCodes = array_filter([
                        trim((string) ($candidate['nw_code'] ?? '')),
                        trim((string) ($candidate['article_nr_'] ?? '')),
                    ]);
                    $itemCode = trim((string) $item['kode']);
                    if ($itemCode !== '' && in_array($itemCode, $candidateCodes, true)) {
                        $matched = $candidate;
                        break;
                    }
                    if (strcasecmp(trim((string) ($candidate['description'] ?? '')), trim((string) $item['nama'])) === 0) {
                        $matched = $candidate;
                        break;
                    }
                }
                if ($matched === null && count($candidates) === 1 && is_array($candidates[0])) {
                    $matched = $candidates[0];
                }
                $finishing = trim((string) ($matched['finishing'] ?? ''));
            }
            for ($sequence = 1; $sequence <= $item['qty']; $sequence++) {
                $serial = $sequence . '/' . $item['qty'];
                // Compact QR payload: SPK ID, Detail PO ID, label sequence.
                // All display metadata remains outside the QR and can be looked up on scan.
                $value = implode(',', [
                    $spk->id,
                    $item['detail_po_id'],
                    $serial,
                ]);
                // No per-label database cache reads/writes in the browser-print path.
                $svg = (string) QrCode::format('svg')->size(210)->margin(2)
                    ->errorCorrection('M')->generate($value);
                $labels[] = [
                    'name' => $item['nama'], 'kode' => $item['kode'],
                    'material' => $item['material'], 'finishing' => $finishing, 'number' => $serial,
                    'qty' => $item['qty'], 'sequence' => $sequence,
                    'no_spk' => $data['no_spk'], 'no_po' => $data['no_po'],
                    'supplier' => $data['supplier'],
                    'detail_po_id' => $item['detail_po_id'],
                    'qr' => 'data:image/svg+xml;base64,' . base64_encode($svg),
                ];
            }
        }
        $pages = array_chunk($labels, 30);
        return response()->view('pages.management.pdf.spk-qr-browser-print', compact('pages', 'spk'))
            ->header('Cache-Control', 'private, no-store');
    }
}
