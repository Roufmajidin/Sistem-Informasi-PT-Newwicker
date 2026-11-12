<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SpkQrLabelController extends Controller
{
    private const MAX_LABELS = 2000;

    private const QR_CACHE_DAYS = 7;

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
                $total > self::MAX_LABELS,
                422,
                'Jumlah label melebihi 2000. '
                . 'Hubungi administrator untuk ekspor bertahap.'
            );

            $items[] = [
                'detail_po_id' => (int) (
                    $item['detail_po_id']
                    ?? $spk->detail_po_id
                    ?? 0
                ),
                'nama' => trim(
                    (string) ($item['nama'] ?? '-')
                ),
                'kode' => trim(
                    (string) ($item['kode'] ?? '')
                ),
                'material' => trim(
                    (string) ($item['material'] ?? '')
                ),
                'qty' => $qty,
                'satuan' => (string) (
                    $item['satuan'] ?? 'pcs'
                ),
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
            'no_po' => (string) (
                $data['no_po'] ?? ''
            ),
            'supplier' => (string) (
                $data['sup'] ?? ''
            ),
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
     * Generate QR SVG lokal.
     *
     * Tidak membutuhkan Imagick atau internet.
     */
    private function generateQr(string $value): string
    {
        $cacheKey = 'spk_qr_svg_v1_'
            . hash('sha256', $value);

        return Cache::remember(
            $cacheKey,
            now()->addDays(self::QR_CACHE_DAYS),
            function () use ($value) {

                $svg = QrCode::format('svg')
                    ->size(420)
                    ->margin(4)
                    ->errorCorrection('M')
                    ->generate($value);

                return 'data:image/svg+xml;base64,'
                    . base64_encode((string) $svg);
            }
        );
    }

    public function download(int $id)
    {
        @set_time_limit(0);

        $payload = $this->getSpkData($id);

        $spk = $payload['spk'];
        $labels = [];

        foreach ($payload['items'] as $item) {

            $qty = $item['qty'];

            for (
                $sequence = 1;
                $sequence <= $qty;
                $sequence++
            ) {

                $serial = $qty . '/' . $sequence;

                /*
                 * Format payload:
                 *
                 * spkId,poId,detailPoId,
                 * nama,supplier,no_po,qty/urutan
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

                    'qr' => $this->generateQr($value),
                ];
            }
        }

        // 3 kolom x 5 baris.
        $pages = array_chunk($labels, 15);

        // F4 portrait: 210 x 330 mm.
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
                [
                    0,
                    0,
                    $paperWidth,
                    $paperHeight
                ],
                'portrait'
            )
            ->setOptions([
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $filename = 'SPK_QR_'
            . Str::slug(
                $payload['no_spk'],
                '_'
            )
            . '_'
            . now()->format('Ymd_His')
            . '.pdf';

        return $pdf->download($filename);
    }
}
