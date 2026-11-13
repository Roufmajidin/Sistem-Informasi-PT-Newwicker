<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('pages.scan_spk.index');
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr' => ['required', 'string', 'max:5000'],
        ]);

        $raw = trim($validated['qr']);
        $parts = explode(',', $raw);

        // QR existing: spk_id,po_id,detail_po_id,nama,supplier,no_po,serial
        // Parse from both ends, allowing commas inside product names.
        if (count($parts) < 7) {
            return response()->json(['message' => 'Format QR SPK tidak valid.'], 422);
        }

        $spkId = trim($parts[0]);
        $poId = trim($parts[1]);
        $detailPoId = trim($parts[2]);
        $serial = trim(array_pop($parts));
        $noPo = trim(array_pop($parts));
        $supplier = trim(array_pop($parts));
        $name = trim(implode(',', array_slice($parts, 3)));

        if (!ctype_digit($spkId) || !ctype_digit($poId) || !ctype_digit($detailPoId)
            || !preg_match('/^([1-9]\d*)\/([1-9]\d*)$/', $serial, $match)
            || (int) $match[1] > (int) $match[2]) {
            return response()->json(['message' => 'Format ID atau nomor urut QR tidak valid.'], 422);
        }

        $spk = Spk::find((int) $spkId);
        if (!$spk) {
            return response()->json(['message' => 'SPK tidak ditemukan di database.'], 404);
        }

        if ((int) $spk->po_id !== (int) $poId) {
            return response()->json(['message' => 'PO pada QR tidak sesuai dengan SPK.'], 422);
        }

        $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
        $data = is_array($data) ? $data : [];

        $matchedItem = null;
        foreach (($data['items'] ?? []) as $item) {
            if (!is_array($item)) continue;
            if ((int) ($item['detail_po_id'] ?? $spk->detail_po_id ?? 0) === (int) $detailPoId
                && trim((string) ($item['nama'] ?? '')) === $name) {
                $matchedItem = $item;
                break;
            }
        }

        if (!$matchedItem) {
            return response()->json(['message' => 'Produk pada QR tidak cocok dengan item SPK.'], 422);
        }

        if ((int) ($matchedItem['qty'] ?? 0) !== (int) $match[2]) {
            return response()->json(['message' => 'Jumlah label pada QR tidak cocok dengan SPK.'], 422);
        }

        return response()->json([
            'message' => 'QR SPK berhasil diverifikasi.',
            'data' => [
                'spk_id' => $spk->id,
                'no_spk' => (string) ($data['no_spk'] ?? 'SPK #'.$spk->id),
                'po_id' => (int) $poId,
                'no_po' => $noPo,
                'detail_po_id' => (int) $detailPoId,
                'nama' => $name,
                'kode' => (string) ($matchedItem['kode'] ?? ''),
                'material' => (string) ($matchedItem['material'] ?? ''),
                'supplier' => $supplier,
                'serial' => $serial,
                'qty' => (int) $match[2],
            ],
        ]);
    }
}
