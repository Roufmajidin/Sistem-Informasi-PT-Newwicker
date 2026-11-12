<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SpkQrLabelController extends Controller

{
    private function getSpkData(int $id): array
    {
        $spk = Spk::findOrFail($id);

        $data = is_array($spk->data)
            ? $spk->data
            : json_decode((string) $spk->data, true);

        abort_unless(
            is_array($data),
            422,
            'Data SPK tidak valid.'
        );

        $items = [];
        $total = 0;

        foreach (($data['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $qty = filter_var(
                $item['qty'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($qty === false || $qty < 1) {
                continue;
            }

            $total += $qty;

            abort_if(
                $total > 2000,
                422,
                'Jumlah label melebihi 2000. Hubungi administrator untuk ekspor bertahap.'
            );

            $items[] = [
                'detail_po_id' => (int) (
                    $item['detail_po_id']
                    ?? $spk->detail_po_id
                    ?? 0
                ),
                'nama' => trim((string) ($item['nama'] ?? '-')),
                'kode' => trim((string) ($item['kode'] ?? '')),
                'material' => trim((string) ($item['material'] ?? '')),
                'qty' => $qty,
                'satuan' => (string) ($item['satuan'] ?? 'pcs'),
            ];
        }

        abort_if(
            empty($items),
            422,
            'SPK ini tidak memiliki item dengan qty valid.'
        );

        return [
            'spk' => $spk,
            'no_spk' => (string) (
                $data['no_spk']
                ?? ('SPK #' . $spk->id)
            ),
            'no_po' => (string) ($data['no_po'] ?? ''),
            'supplier' => (string) ($data['sup'] ?? ''),
            'items' => $items,
            'total' => $total,
        ];
    }

    public function preview(int $id): JsonResponse
    {
        $payload = $this->getSpkData($id);

        return response()->json([
            'no_spk' => $payload['no_spk'],
            'items' => $payload['items'],
            'total_labels' => $payload['total'],
            'download_url' => route(
                'inventor.qr.download',
                ['id' => $id]
            ),
        ]);
    }

    /**
     * Mengambil PNG QR dari QRServer.
     *
     * Data dikirim ke layanan eksternal.
     */
    private function generateQr(string $value): string
    {
        try {
            $response = Http::connectTimeout(10)
                ->timeout(25)
                ->retry(2, 500)
                ->get(
                    'https://api.qrserver.com/v1/create-qr-code/',
                    [
                        'size' => '600x600',
                        'format' => 'png',
                        'ecc' => 'M',
                        'qzone' => 4,
                        'charset-source' => 'UTF-8',
                        'data' => $value,
                    ]
                );

            if (!$response->successful()) {
                throw new \RuntimeException(
                    'HTTP ' . $response->status()
                );
            }

            $image = $response->body();

            // Validasi signature PNG.
            if (
                substr($image, 0, 8)
                !== "\x89PNG\r\n\x1a\n"
            ) {
                throw new \RuntimeException(
                    'Response API bukan gambar PNG.'
                );
            }

            return 'data:image/png;base64,'
                . base64_encode($image);

        } catch (\Throwable $e) {
            report($e);

            abort(
                502,
                'Gagal mengambil QR Code dari QRServer. '
                . 'Periksa koneksi internet server.'
            );
        }
    }

    public function download(int $id)
    {
        // Ekspor besar melalui API dapat memerlukan waktu.
        // Batas ini tidak menjamin bebas timeout.
        @set_time_limit(0);

        $payload = $this->getSpkData($id);
        $spk = $payload['spk'];

        $labels = [];

        foreach ($payload['items'] as $item) {

            $qty = $item['qty'];

            for ($sequence = 1; $sequence <= $qty; $sequence++) {

                $serial = $qty . '/' . $sequence;

                /*
                 * Payload scanner:
                 *
                 * spkId,poId,detailPoId,
                 * itemName,supplierName,no_po,totalQty/serial
                 */
                $value = implode(',', [
                    $spk->id,
                    $spk->po_id,
                    $item['detail_po_id'],
                    $item['nama'],
                    $payload['supplier'],
                    $payload['no_po'],
                    $serial,
                ]);

                // QR PNG 600x600 dari API.
                $qrImage = $this->generateQr($value);

                $labels[] = [
                    'name' => $item['nama'],
                    'kode' => $item['kode'],
                    'material' => $item['material'],

                    'number' => $serial,
                    'qty' => $qty,
                    'sequence' => $sequence,

                    'no_spk' => $payload['no_spk'],
                    'no_po' => $payload['no_po'],
                    'supplier' => $payload['supplier'],

                    'detail_po_id' => $item['detail_po_id'],

                    'qr' => $qrImage,
                ];
            }
        }

        // 3 kolom x 5 baris = 15 label per halaman.
        $pages = array_chunk($labels, 15);

        // F4 portrait 210 x 330 mm.
        $paperWidth = 210 * 72 / 25.4;
        $paperHeight = 330 * 72 / 25.4;

        $pdf = Pdf::loadView(
            'pages.management.pdf.spk-qr-labels',
            [
                'pages' => $pages,
                'spk' => $spk,
                'noSpk' => $payload['no_spk'],
            ]
        )
        ->setPaper(
            [0, 0, $paperWidth, $paperHeight],
            'portrait'
        )
        ->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $filename = 'SPK_QR_'
            . Str::slug($payload['no_spk'], '_')
            . '_'
            . now()->format('Ymd_His')
            . '.pdf';

        return $pdf->download($filename);
    }
}
