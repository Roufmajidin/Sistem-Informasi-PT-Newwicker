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
use Imagick;
use ImagickDraw;
use ImagickPixel;
use ZipArchive;
use Throwable;

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
            if (!is_array($item))
                continue;
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);
            if ($qty === false || $qty < 1)
                continue;
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
            'user_id' => $userId,
            'spk_id' => $id,
            'status' => 'queued',
            'current' => 0,
            'total' => $data['total'],
            'message' => 'Menunggu worker...',
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
            'status' => $state['status'],
            'current' => $state['current'],
            'total' => $state['total'],
            'message' => $state['message'] ?? '',
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
            'user_id' => $userId,
            'spk_id' => $id,
            'status' => 'queued',
            'current' => 0,
            'total' => $data['total'],
            'message' => 'Menyiapkan PDF...',
        ], now()->addHours(6));
        (new GenerateSpkQrPdf($id, $token, $userId))->handle();
        return $this->file($token);
    }

    /** Independent browser-print flow; existing PDF queue remains intact. */
    public function browserPrint(int $id)
    {
        $data = $this->getSpkData($id);
        $spk = $data['spk'];
        $detailIds = collect($data['items'])->pluck('detail_po_id')->filter()->unique()->values();
        $detailsById = DetailPo::query()->whereIn('id', $detailIds)->get()->keyBy('id');
        $labels = [];
        foreach ($data['items'] as $item) {
            $finishing = '';
            $detail = $detailsById->get($item['detail_po_id']);
            $raw = $detail ? $detail->detail : null;
            if (is_string($raw))
                $raw = json_decode($raw, true);
            if (is_array($raw)) {
                $candidates = array_is_list($raw) ? $raw : [$raw];
                $matched = null;
                foreach ($candidates as $candidate) {
                    if (!is_array($candidate))
                        continue;
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
                $value = implode(',', [$spk->id, $item['detail_po_id'], $serial]);
                $svg = (string) QrCode::format('svg')->size(210)->margin(2)
                    ->errorCorrection('M')->generate($value);
                $labels[] = [
                    'name' => $item['nama'],
                    'kode' => $item['kode'],
                    'material' => $item['material'],
                    'finishing' => $finishing,
                    'number' => $serial,
                    'qty' => $item['qty'],
                    'sequence' => $sequence,
                    'no_spk' => $data['no_spk'],
                    'no_po' => $data['no_po'],
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

    /** JPEG 24x37mm @ 300 DPI, one JPEG per serial, packed in ZIP. */


    public function downloadJpegZip(int $id)
    {
        abort_unless(extension_loaded('gd'), 500, 'Ekstensi PHP GD belum aktif.');
        abort_unless(class_exists(\ZipArchive::class), 500, 'Ekstensi PHP ZIP belum aktif.');
        abort_unless(
            class_exists(\Endroid\QrCode\Writer\PngWriter::class),
            500,
            'Package endroid/qr-code belum terpasang.'
        );

        $data = $this->getSpkData($id);
        $spk = $data['spk'];

        // =====================================================
        // NAMA ZIP DAN FOLDER
        // =====================================================

        $clean = static function ($value): string {
            $value = trim((string) $value);
            $value = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/u', '-', $value);
            $value = preg_replace('/\s+/u', ' ', $value);

            return trim($value, ' .-_') ?: 'UNKNOWN';
        };

        $folderName = mb_substr(
            $clean(
                trim((string) $data['no_spk']) . ' ' .
                trim((string) $data['no_po']) . ' (' .
                trim((string) $data['supplier']) . ')'
            ),
            0,
            150
        );

        $zipFilename = $folderName . '.zip';

        $directory = storage_path('app/spk-qr-jpeg');

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            abort(500, 'Gagal membuat direktori ZIP.');
        }

        $zipPath = $directory . DIRECTORY_SEPARATOR
            . (string) \Illuminate\Support\Str::uuid() . '.zip';

        $zip = new \ZipArchive();

        abort_unless(
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true,
            500,
            'Gagal membuat ZIP.'
        );

        // =====================================================
        // UKURAN STIKER
        // =====================================================

        $width = 283;   // 24 mm @ 300 DPI
        $height = 437;  // 37 mm @ 300 DPI
        $qrSize = 165;  // 14 mm @ 300 DPI

        // =====================================================
        // FONT ARIAL REGULAR
        // =====================================================

        $regularCandidates = [
            storage_path('app/fonts/Arial.ttf'),
            'C:/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        $boldCandidates = [
            storage_path('app/fonts/Arial-Bold.ttf'),
            'C:/Windows/Fonts/arialbd.ttf',
            '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ];

        $regularFont = null;
        $boldFont = null;

        foreach ($regularCandidates as $candidate) {
            if (is_file($candidate)) {
                $regularFont = $candidate;
                break;
            }
        }

        foreach ($boldCandidates as $candidate) {
            if (is_file($candidate)) {
                $boldFont = $candidate;
                break;
            }
        }

        abort_unless(
            $regularFont !== null && function_exists('imagettftext'),
            500,
            'Font Arial Regular / Liberation Sans tidak ditemukan di hosting.'
        );

        // Jika bold tidak tersedia, serial tetap menggunakan regular.
        $boldFont = $boldFont ?: $regularFont;

        // =====================================================
        // HELPER MENGUKUR TEKS
        // =====================================================

        $measure = static function (string $text, int $fontSize, string $font): int {
            $box = imagettfbbox($fontSize, 0, $font, $text);

            if ($box === false) {
                return 0;
            }

            return (int) abs($box[2] - $box[0]);
        };

        // =====================================================
        // HELPER MEMOTONG TEKS JIKA TERLALU PANJANG
        // =====================================================

        $fitText = static function (string $text, int $fontSize, string $font, int $maxWidth = 263) use ($measure): string {
            $text = mb_strtoupper(trim($text), 'UTF-8');

            if ($text === '') {
                return '-';
            }

            if ($measure($text, $fontSize, $font) <= $maxWidth) {
                return $text;
            }

            while (
                mb_strlen($text, 'UTF-8') > 1
                && $measure($text . '...', $fontSize, $font) > $maxWidth
            ) {
                $text = mb_substr($text, 0, -1, 'UTF-8');
            }

            return rtrim($text) . '...';
        };

        // =====================================================
        // HELPER WRAP NAMA ITEM / SUPPLIER
        // =====================================================

        $wrapText = static function (string $text, int $fontSize, string $font, int $maxWidth = 263, int $maxLines = 2) use ($measure, $fitText): array {
            $text = mb_strtoupper(trim($text), 'UTF-8');

            if ($text === '') {
                return ['-'];
            }

            $words = preg_split('/\s+/u', $text);
            $lines = [];
            $current = '';

            foreach ($words as $word) {
                $candidate = $current === ''
                    ? $word
                    : $current . ' ' . $word;

                if ($current === '' || $measure($candidate, $fontSize, $font) <= $maxWidth) {
                    $current = $candidate;
                } else {
                    $lines[] = $current;
                    $current = $word;
                }
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            if (count($lines) > $maxLines) {
                $remaining = implode(' ', array_slice($lines, $maxLines - 1));
                $lines = array_slice($lines, 0, $maxLines - 1);
                $lines[] = $remaining;
            }

            return array_map(
                fn($line) => $fitText($line, $fontSize, $font, $maxWidth),
                $lines
            );
        };

        // =====================================================
        // HELPER DRAW TEXT CENTER
        // =====================================================

        $drawText = static function (\GdImage $image, string $text, int $baselineY, int $fontSize, string $font, int $maxWidth = 263) use ($fitText, $measure, $width): void {
            $text = $fitText($text, $fontSize, $font, $maxWidth);
            $textWidth = $measure($text, $fontSize, $font);

            $x = (int) round(($width - $textWidth) / 2);
            $black = imagecolorallocate($image, 0, 0, 0);

            imagettftext(
                $image,
                $fontSize,
                0,
                max(4, $x),
                $baselineY,
                $black,
                $font,
                $text
            );
        };

        // =====================================================
        // GENERATE JPEG ZIP
        // =====================================================

        $writer = new \Endroid\QrCode\Writer\PngWriter();

        $multipleItems = count($data['items']) > 1;
        $generated = 0;
        $zipClosed = false;

        try {
            $zip->addEmptyDir($folderName . '/');

            foreach ($data['items'] as $itemIndex => $item) {
                $itemFolder = $folderName . '/';

                if ($multipleItems) {
                    $itemFolder .= str_pad(
                        (string) ($itemIndex + 1),
                        3,
                        '0',
                        STR_PAD_LEFT
                    ) . '_' . mb_substr($clean($item['nama']), 0, 80) . '/';

                    $zip->addEmptyDir($itemFolder);
                }

                for ($sequence = 1; $sequence <= $item['qty']; $sequence++) {
                    $serial = $sequence . '/' . $item['qty'];

                    // Payload QR tetap sama dengan Browser Print lama.
                    $value = implode(',', [
                        $spk->id,
                        $item['detail_po_id'],
                        $serial,
                    ]);

                    $qrCode = \Endroid\QrCode\QrCode::create($value)
                        ->setSize(300)
                        ->setMargin(6)
                        ->setErrorCorrectionLevel(
                            \Endroid\QrCode\ErrorCorrectionLevel::Medium
                        );

                    $pngData = $writer->write($qrCode)->getString();
                    $qrImage = imagecreatefromstring($pngData);

                    if (!$qrImage) {
                        throw new \RuntimeException('Gagal membuat QR.');
                    }

                    $image = imagecreatetruecolor($width, $height);

                    if (!$image) {
                        imagedestroy($qrImage);
                        throw new \RuntimeException('Gagal membuat kanvas JPEG.');
                    }

                    try {
                        $white = imagecolorallocate($image, 255, 255, 255);
                        imagefill($image, 0, 0, $white);

                        // =================================================
                        // FONT SIZE
                        // =================================================

                        $spkFontSize = 9;
                        $itemFontSize = 11;
                        $supplierFontSize = 10;
                        $poFontSize = 9;
                        $serialFontSize = 15;

                        // =================================================
                        // WRAP TEKS
                        // =================================================

                        $itemLines = $wrapText(
                            $item['nama'],
                            $itemFontSize,
                            $regularFont,
                            263,
                            2
                        );

                        $supplierLines = $wrapText(
                            $data['supplier'],
                            $supplierFontSize,
                            $regularFont,
                            263,
                            2
                        );

                        // =================================================
                        // LAYOUT CENTER VERTIKAL
                        // =================================================

                        $spkHeight = 18;
                        $itemLineHeight = 22;
                        $supplierLineHeight = 20;
                        $poHeight = 19;
                        $serialHeight = 27;

                        $gapSpkQr = 9;
                        $gapQrItem = 12;
                        $gapItemSupplier = 7;
                        $gapSupplierPo = 6;
                        $gapPoSerial = 5;

                        $totalHeight =
                            $spkHeight
                            + $gapSpkQr
                            + $qrSize
                            + $gapQrItem
                            + count($itemLines) * $itemLineHeight
                            + $gapItemSupplier
                            + count($supplierLines) * $supplierLineHeight
                            + $gapSupplierPo
                            + $poHeight
                            + $gapPoSerial
                            + $serialHeight;

                        $y = max(
                            8,
                            (int) round(($height - $totalHeight) / 2)
                        );

                        // No SPK
                        $drawText(
                            $image,
                            $data['no_spk'],
                            $y + 15,
                            $spkFontSize,
                            $regularFont
                        );

                        $y += $spkHeight + $gapSpkQr;

                        // QR
                        imagecopyresized(
                            $image,
                            $qrImage,
                            (int) (($width - $qrSize) / 2),
                            $y,
                            0,
                            0,
                            $qrSize,
                            $qrSize,
                            imagesx($qrImage),
                            imagesy($qrImage)
                        );

                        $y += $qrSize + $gapQrItem;

                        // Nama item
                        foreach ($itemLines as $line) {
                            $drawText(
                                $image,
                                $line,
                                $y + 17,
                                $itemFontSize,
                                $regularFont
                            );

                            $y += $itemLineHeight;
                        }

                        $y += $gapItemSupplier;

                        // Supplier
                        foreach ($supplierLines as $line) {
                            $drawText(
                                $image,
                                $line,
                                $y + 16,
                                $supplierFontSize,
                                $regularFont
                            );

                            $y += $supplierLineHeight;
                        }

                        $y += $gapSupplierPo;

                        // No PO
                        $drawText(
                            $image,
                            '(' . ($data['no_po'] ?: '-') . ')',
                            $y + 15,
                            $poFontSize,
                            $regularFont
                        );

                        $y += $poHeight + $gapPoSerial;

                        // Nomor urut (Bold)
                        $drawText(
                            $image,
                            $serial,
                            $y + 23,
                            $serialFontSize,
                            $boldFont
                        );

                        // =================================================
                        // JPEG 300 DPI
                        // =================================================

                        if (function_exists('imageresolution')) {
                            imageresolution($image, 300, 300);
                        }

                        ob_start();
                        $encoded = imagejpeg($image, null, 95);
                        $jpegData = ob_get_clean();

                        if (!$encoded || !is_string($jpegData) || $jpegData === '') {
                            throw new \RuntimeException('Gagal membuat JPEG.');
                        }

                        // Nama file: 1.jpg, 2.jpg, 3.jpg
                        $filename = $sequence . '.jpg';

                        if (!$zip->addFromString($itemFolder . $filename, $jpegData)) {
                            throw new \RuntimeException(
                                'Gagal menambahkan JPEG ke ZIP: ' . $filename
                            );
                        }

                        $generated++;
                    } finally {
                        imagedestroy($qrImage);
                        imagedestroy($image);
                    }
                }
            }

            if (!$zip->close()) {
                throw new \RuntimeException('Gagal menyelesaikan ZIP.');
            }

            $zipClosed = true;
        } catch (\Throwable $e) {
            if (!$zipClosed) {
                $zip->close();
            }

            @unlink($zipPath);
            throw $e;
        }

        if ($generated === 0) {
            @unlink($zipPath);
            abort(422, 'Tidak ada label yang berhasil dibuat.');
        }

        return response()->download(
            $zipPath,
            $zipFilename,
            [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'private, no-store',
            ]
        )->deleteFileAfterSend(true);
    }


}
