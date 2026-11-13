<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Spk;
use App\Models\SpkScanLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ScanController extends Controller
{
    public function index()
    {
        return view('pages.scan_spk.index');
    }

    /** Verify without writing to the database. */
    public function lookup(Request $request): JsonResponse
    {
        return $this->handleScan($request, false);
    }

    /** Verify and persist only first successful scans; never persist duplicate/invalid attempts. */
    public function store(Request $request): JsonResponse
    {
        return $this->handleScan($request, true);
    }

    private function handleScan(Request $request, bool $save): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr' => ['required', 'string', 'max:4096'],
            'scan_method' => ['sometimes', 'in:camera,wireless'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 'invalid',
                'message' => $validator->errors()->first(),
            ], 422)->header('Cache-Control', 'no-store');
        }

        $raw = trim($request->input('qr'));
        $method = $request->input('scan_method', 'camera');
        $parsed = $this->parseQr($raw);

        if (isset($parsed['error'])) {
            return $this->failed($request, $save, $raw, $method, 'invalid', $parsed['error'], 422, $parsed);
        }

        $spk = Spk::find($parsed['spk_id']);
        if (!$spk) {
            return $this->failed($request, $save, $raw, $method, 'invalid', 'SPK tidak ditemukan.', 404, $parsed);
        }

        $data = is_array($spk->data) ? $spk->data : json_decode((string) $spk->data, true);
        if (!is_array($data) || !is_array($data['items'] ?? null)) {
            return $this->failed($request, $save, $raw, $method, 'invalid', 'Data JSON SPK tidak valid.', 422, $parsed);
        }

        $candidates = [];
        foreach ($data['items'] as $item) {
            if (!is_array($item))
                continue;
            $itemId = (int) ($item['detail_po_id'] ?? $spk->detail_po_id ?? 0);
            if ($itemId !== $parsed['detail_po_id'] || (int) ($item['qty'] ?? 0) !== $parsed['qty'])
                continue;
            if ($parsed['legacy'] && trim((string) ($item['nama'] ?? '')) !== $parsed['legacy_name'])
                continue;
            $candidates[] = $item;
        }

        if (count($candidates) !== 1) {
            $ambiguous = count($candidates) > 1;
            return $this->failed(
                $request,
                $save,
                $raw,
                $method,
                'invalid',
                $ambiguous ? 'Beberapa item memiliki detail_po_id dan qty yang sama; QR tidak unik.' : 'Item atau jumlah label tidak sesuai dengan SPK.',
                $ambiguous ? 409 : 404,
                $parsed
            );
        }

        $item = $candidates[0];
        $result = $this->itemDetails($spk, $data, $item, $parsed);
        $identity = [
            'spk_id' => $parsed['spk_id'],
            'detail_po_id' => $parsed['detail_po_id'],
            'sequence' => $parsed['sequence'],
        ];

        // Lookup is read-only; still reports globally scanned labels.
        if (!$save) {
            $exists = SpkScanLog::query()->where($identity)->where('status', 'success')->exists();
            return $this->respond(
                !$exists,
                $exists ? 'duplicate' : 'success',
                $exists ? 'Label sudah pernah discan.' : 'QR SPK berhasil diverifikasi.',
                $result,
                $exists ? 409 : 200
            );
        }

        // Detect duplicates before INSERT, so repeated scans return 409 instead of success.
        // The database unique index remains necessary for concurrent scans.
        $alreadyScanned = SpkScanLog::query()
            ->where($identity)
            ->where('status', 'success')
            ->exists();

        if ($alreadyScanned) {
            return $this->respond(false, 'duplicate', 'Label sudah pernah discan.', $result, 409);
        }

        try {
            // The database unique index on (spk_id, detail_po_id, success_sequence)
            // guarantees atomic uniqueness across simultaneous devices.
            SpkScanLog::create(array_merge($identity, [
                'user_id' => $request->user()?->id,
                'qty' => $parsed['qty'],
                'qr_raw' => $raw,
                'scan_method' => $method,
                'status' => 'success',
                'message' => 'QR SPK berhasil discan.',
                'scanned_at' => now(),
            ]));
        } catch (QueryException $e) {
            // Duplicate key: MySQL 1062 / SQLSTATE 23000. Verify the actual
            // successful label before categorizing any constraint error.
            $duplicateKey = ($e->errorInfo[1] ?? null) == 1062;
            if (!$duplicateKey || !SpkScanLog::query()->where($identity)->where('status', 'success')->exists()) {
                throw $e;
            }
            return $this->respond(false, 'duplicate', 'Label sudah pernah discan.', $result, 409);
        }

        return $this->respond(true, 'success', 'QR SPK berhasil discan dan disimpan.', $result, 201);
    }

    private function parseQr(string $raw): array
    {
        $parts = array_map('trim', explode(',', $raw));
        if (count($parts) === 3) {
            [$spkId, $detailPoId, $serial] = $parts;
            $legacy = false;
            $legacyName = null;
        } elseif (count($parts) === 7) {
            $spkId = $parts[0];
            $detailPoId = $parts[2];
            $serial = $parts[6];
            $legacy = true;
            $legacyName = $parts[3];
        } else {
            return ['error' => 'Format QR tidak dikenali.'];
        }

        if (
            !ctype_digit($spkId) || !ctype_digit($detailPoId)
            || (int) $spkId < 1 || (int) $detailPoId < 1
            || !preg_match('/^([1-9]\d*)\/([1-9]\d*)$/', $serial, $matches)
        ) {
            return ['error' => 'ID SPK, detail PO, atau nomor label tidak valid.'];
        }
        $sequence = (int) $matches[1];
        $qty = (int) $matches[2];
        if ($qty > 2000 || $sequence > $qty) {
            return ['error' => 'Nomor urut label di luar batas.'];
        }

        return [
            'spk_id' => (int) $spkId,
            'detail_po_id' => (int) $detailPoId,
            'sequence' => $sequence,
            'qty' => $qty,
            'legacy' => $legacy,
            'legacy_name' => $legacyName,
        ];
    }

    private function itemDetails(Spk $spk, array $data, array $item, array $parsed): array
    {
        $finishing = '';
        $detailPo = DetailPo::find($parsed['detail_po_id']);
        if ($detailPo) {
            $detail = $detailPo->detail;
            if (is_string($detail))
                $detail = json_decode($detail, true);
            if (is_array($detail)) {
                $records = array_is_list($detail) ? $detail : [$detail];
                $matched = [];
                foreach ($records as $record) {
                    if (!is_array($record))
                        continue;
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
                if (!$matched && count($records) === 1 && is_array($records[0]))
                    $matched = $records[0];
                $finishing = trim((string) ($matched['finishing'] ?? ''));
            }
        }

        return [
            'spk_id' => $spk->id,
            'detail_po_id' => $parsed['detail_po_id'],
            'no_spk' => (string) ($data['no_spk'] ?? 'SPK #' . $spk->id),
            'no_po' => (string) ($data['no_po'] ?? ''),
            'supplier' => (string) ($data['sup'] ?? ''),
            'name' => (string) ($item['nama'] ?? ''),
            'kode' => (string) ($item['kode'] ?? ''),
            'material' => (string) ($item['material'] ?? ''),
            'finishing' => $finishing,
            'number' => $parsed['sequence'] . '/' . $parsed['qty'],
            'sequence' => $parsed['sequence'],
            'qty' => $parsed['qty'],
            'format' => $parsed['legacy'] ? 'legacy' : 'short',
        ];
    }

    private function failed(
        Request $request,
        bool $save,
        string $raw,
        string $method,
        string $status,
        string $message,
        int $code,
        array $parsed = []
    ): JsonResponse {
        return $this->respond(false, $status, $message, null, $code);
    }

    private function respond(
        bool $success,
        string $status,
        string $message,
        ?array $data,
        int $code
    ): JsonResponse {
        return response()->json([
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code)->header('Cache-Control', 'no-store');
    }
}
