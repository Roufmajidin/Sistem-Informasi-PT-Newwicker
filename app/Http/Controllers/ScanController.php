<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Spk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function index()
    {
        return view('pages.scan_spk.index');
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr' => ['required', 'string', 'max:4096'],
        ]);

        $raw = trim($validated['qr']);
        $parts = array_map('trim', explode(',', $raw));

        // New format: spk_id,detail_po_id,sequence/qty
        // Legacy: spk_id,po_id,detail_po_id,name,supplier,no_po,sequence/qty
        // Note: legacy comma-separated text cannot be parsed reliably if names contain commas.
        if (count($parts) === 3) {
            [$spkId, $detailPoId, $serial] = $parts;
            $legacy = false;
        } elseif (count($parts) === 7) {
            $spkId = $parts[0];
            $detailPoId = $parts[2];
            $serial = $parts[6];
            $legacy = true;
        } else {
            return $this->invalid('Format QR tidak dikenali.');
        }

        if (
            !ctype_digit($spkId) || !ctype_digit($detailPoId)
            || (int) $spkId < 1 || (int) $detailPoId < 1
            || !preg_match('/^([1-9]\d*)\/([1-9]\d*)$/', $serial, $match)
        ) {
            return $this->invalid('ID SPK, detail PO, atau nomor label tidak valid.');
        }

        $sequence = (int) $match[1];
        $quantity = (int) $match[2];
        if ($sequence > $quantity || $quantity > 2000) {
            return $this->invalid('Nomor urut label di luar batas.');
        }

        $spk = Spk::find((int) $spkId);
        if (!$spk) {
            return $this->notFound('SPK tidak ditemukan.');
        }

        $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
        if (!is_array($data)) {
            return $this->invalid('Data JSON SPK tidak valid.');
        }

        $candidates = [];
        foreach (($data['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = (int) ($item['detail_po_id'] ?? $spk->detail_po_id ?? 0);
            if ($id !== (int) $detailPoId || (int) ($item['qty'] ?? 0) !== $quantity) {
                continue;
            }
            if ($legacy && trim((string) ($item['nama'] ?? '')) !== $parts[3]) {
                continue;
            }
            $candidates[] = $item;
        }

        if (count($candidates) === 0) {
            return $this->notFound('Item atau jumlah label tidak sesuai dengan SPK.');
        }
        if (count($candidates) > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Ada beberapa item SPK dengan detail_po_id dan qty sama. QR ini tidak cukup unik untuk menentukan item.',
            ], 409);
        }

        $item = $candidates[0];
        $detailPo = DetailPo::find((int) $detailPoId);
        $finishing = '';
        if ($detailPo) {
            $detail = $detailPo->detail;
            if (is_string($detail)) {
                $detail = json_decode($detail, true);
            }
            if (is_array($detail)) {
                $records = array_is_list($detail) ? $detail : [$detail];
                $matched = [];
                foreach ($records as $record) {
                    if (!is_array($record)) {
                        continue;
                    }
                    $codes = array_filter([
                        trim((string) ($record['nw_code'] ?? '')),
                        trim((string) ($record['article_nr_'] ?? '')),
                    ]);
                    if (($item['kode'] ?? '') !== '' && in_array(trim((string) $item['kode']), $codes, true)) {
                        $matched = $record;
                        break;
                    }
                    if (Str::lower(trim((string) ($record['description'] ?? ''))) === Str::lower(trim((string) ($item['nama'] ?? '')))) {
                        $matched = $record;
                        break;
                    }
                }
                if (!$matched && count($records) === 1 && is_array($records[0])) {
                    $matched = $records[0];
                }
                $finishing = trim((string) ($matched['finishing'] ?? ''));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'QR SPK berhasil diverifikasi.',
            'data' => [
                'spk_id' => $spk->id,
                'detail_po_id' => (int) $detailPoId,
                'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $spk->id),
                'no_po' => (string) ($data['no_po'] ?? ''),
                'supplier' => (string) ($data['sup'] ?? ''),
                'name' => (string) ($item['nama'] ?? ''),
                'kode' => (string) ($item['kode'] ?? ''),
                'material' => (string) ($item['material'] ?? ''),
                'finishing' => $finishing,
                'number' => $sequence . '/' . $quantity,
                'sequence' => $sequence,
                'qty' => $quantity,
                'format' => $legacy ? 'legacy' : 'short',
            ],
        ])->header('Cache-Control', 'no-store');
    }

    private function invalid(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 422);
    }

    private function notFound(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }
}
