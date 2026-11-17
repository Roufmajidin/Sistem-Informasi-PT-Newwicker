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
        // =========================================================
        // VALIDASI EKSTENSI
        // =========================================================

        abort_unless(extension_loaded('gd'), 500, 'GD belum aktif.');
        abort_unless(class_exists(\ZipArchive::class), 500, 'ZIP belum aktif.');
        abort_unless(
            class_exists(\Endroid\QrCode\Writer\PngWriter::class),
            500,
            'Endroid QR Code belum terpasang.'
        );
        abort_unless(
            function_exists('imagettftext') && function_exists('imagettfbbox'),
            500,
            'GD FreeType belum tersedia.'
        );

        // =========================================================
        // FONT REGULAR - HOSTING
        // =========================================================

        $font = storage_path('app/fonts/LiberationSans-Regular.ttf');

        abort_unless(
            is_file($font) && is_readable($font),
            500,
            'Font LiberationSans-Regular.ttf tidak ditemukan atau tidak dapat dibaca.'
        );

        // Catat font yang benar-benar digunakan oleh Laravel.
        \Illuminate\Support\Facades\Log::info('SPK JPEG FONT CHECK', [
            'font' => $font,
            'readable' => is_readable($font),
        ]);

        // =========================================================
        // DATA SPK
        // =========================================================

        $data = $this->getSpkData($id);
        $spk = $data['spk'];

        $noSpk = trim((string) ($data['no_spk'] ?? ''));
        $noPo = trim((string) ($data['no_po'] ?? ''));
        $supplier = trim((string) ($data['supplier'] ?? ''));

        // =========================================================
        // NAMA FOLDER / ZIP
        // =========================================================

        $clean = static function ($value): string {
            $value = trim((string) $value);
            $value = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/u', '-', $value);
            $value = preg_replace('/\s+/u', ' ', $value);

            return trim($value, ' .-_') ?: 'UNKNOWN';
        };

        $folderName = mb_substr(
            $clean($noSpk . ' ' . $noPo . ' (' . $supplier . ')'),
            0,
            150
        );

        $zipFilename = $folderName . '.zip';

        // =========================================================
        // UKURAN STIKER
        // =========================================================

        $width = 283;    // 24 mm @ 300 DPI
        $height = 437;   // 37 mm @ 300 DPI
        $qrSize = 165;   // 14 mm @ 300 DPI

        $maxTextWidth = 263;

        // =========================================================
        // UKURAN FONT REGULAR
        // =========================================================

        $spkFontSize = 11;
        $itemFontSize = 12;
        $supplierFontSize = 11;
        $poFontSize = 10;
        $serialFontSize = 17;

        // =========================================================
        // HELPER MENGUKUR TEKS
        // =========================================================

        $measureText = static function (string $text, int $fontSize) use ($font): int {
            $box = imagettfbbox($fontSize, 0, $font, $text);

            if ($box === false) {
                throw new \RuntimeException('Gagal mengukur teks.');
            }

            return (int) (
                max($box[0], $box[2], $box[4], $box[6])
                - min($box[0], $box[2], $box[4], $box[6])
            );
        };

        // =========================================================
        // HELPER ELLIPSIS
        // =========================================================

        $fitText = static function (string $text, int $fontSize, int $maxWidth) use ($measureText): string {
            $text = mb_strtoupper(trim($text), 'UTF-8');

            if ($text === '') {
                return '-';
            }

            if ($measureText($text, $fontSize) <= $maxWidth) {
                return $text;
            }

            $original = $text;

            while (
                mb_strlen($text, 'UTF-8') > 1
                && $measureText($text . '...', $fontSize) > $maxWidth
            ) {
                $text = mb_substr($text, 0, -1, 'UTF-8');
            }

            return $text !== $original ? rtrim($text) . '...' : $text;
        };

        // =========================================================
        // HELPER WRAP TEKS
        // =========================================================

        $wrapText = static function (string $text, int $fontSize, int $maxWidth, int $maxLines = 2) use ($measureText, $fitText): array {
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

                if (
                    $current === ''
                    || $measureText($candidate, $fontSize) <= $maxWidth
                ) {
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

            foreach ($lines as $index => $line) {
                $lines[$index] = $fitText($line, $fontSize, $maxWidth);
            }

            return $lines;
        };

        // =========================================================
        // HELPER DRAW TEXT CENTER
        // =========================================================

        $drawText = static function (\GdImage $image, string $text, int $baselineY, int $fontSize) use ($font, $width, $maxTextWidth, $fitText): void {
            $text = $fitText($text, $fontSize, $maxTextWidth);

            $box = imagettfbbox($fontSize, 0, $font, $text);

            if ($box === false) {
                throw new \RuntimeException('Gagal menghitung posisi teks.');
            }

            $minX = min($box[0], $box[2], $box[4], $box[6]);
            $maxX = max($box[0], $box[2], $box[4], $box[6]);

            $textWidth = $maxX - $minX;

            $x = (int) round(($width - $textWidth) / 2 - $minX);

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

        // =========================================================
        // SIAPKAN ZIP
        // =========================================================

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

        $writer = new \Endroid\QrCode\Writer\PngWriter();

        $multipleItems = count($data['items']) > 1;
        $generated = 0;
        $zipClosed = false;

        try {
            $zip->addEmptyDir($folderName . '/');

            foreach ($data['items'] as $itemIndex => $item) {
                $itemFolder = $folderName . '/';

                if ($multipleItems) {
                    $itemFolder .= ($itemIndex + 1)
                        . '_'
                        . mb_substr($clean($item['nama']), 0, 80)
                        . '/';

                    $zip->addEmptyDir($itemFolder);
                }

                $qty = (int) $item['qty'];

                for ($sequence = 1; $sequence <= $qty; $sequence++) {
                    $serial = $sequence . '/' . $qty;

                    // Payload QR tetap sama.
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
                        // =========================================
                        // BACKGROUND PUTIH
                        // =========================================

                        $white = imagecolorallocate($image, 255, 255, 255);
                        imagefill($image, 0, 0, $white);

                        // =========================================
                        // WRAP NAMA ITEM / SUPPLIER
                        // =========================================

                        $itemLines = $wrapText(
                            (string) $item['nama'],
                            $itemFontSize,
                            $maxTextWidth,
                            2
                        );

                        $supplierLines = $wrapText(
                            $supplier,
                            $supplierFontSize,
                            $maxTextWidth,
                            2
                        );

                        // =========================================
                        // PENGATURAN JARAK
                        // =========================================

                        $spkHeight = 22;
                        $itemLineHeight = 24;
                        $supplierLineHeight = 22;
                        $poHeight = 21;
                        $serialHeight = 31;

                        $gapSpkQr = 8;
                        $gapQrItem = 11;
                        $gapItemSupplier = 5;
                        $gapSupplierPo = 5;
                        $gapPoSerial = 4;

                        // =========================================
                        // TOTAL TINGGI KONTEN
                        // =========================================

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

                        // Center seluruh blok konten.
                        $y = max(
                            8,
                            (int) round(($height - $totalHeight) / 2)
                        );

                        // =========================================
                        // NO SPK
                        // =========================================

                        $drawText(
                            $image,
                            $noSpk,
                            $y + 18,
                            $spkFontSize
                        );

                        $y += $spkHeight + $gapSpkQr;

                        // =========================================
                        // QR CODE
                        // =========================================

                        imagecopyresized(
                            $image,
                            $qrImage,
                            (int) round(($width - $qrSize) / 2),
                            $y,
                            0,
                            0,
                            $qrSize,
                            $qrSize,
                            imagesx($qrImage),
                            imagesy($qrImage)
                        );

                        $y += $qrSize + $gapQrItem;

                        // =========================================
                        // NAMA ITEM
                        // =========================================

                        foreach ($itemLines as $line) {
                            $drawText(
                                $image,
                                $line,
                                $y + 19,
                                $itemFontSize
                            );

                            $y += $itemLineHeight;
                        }

                        $y += $gapItemSupplier;

                        // =========================================
                        // SUPPLIER
                        // =========================================

                        foreach ($supplierLines as $line) {
                            $drawText(
                                $image,
                                $line,
                                $y + 18,
                                $supplierFontSize
                            );

                            $y += $supplierLineHeight;
                        }

                        $y += $gapSupplierPo;

                        // =========================================
                        // NO PO DALAM KURUNG
                        // =========================================

                        $drawText(
                            $image,
                            '(' . ($noPo ?: '-') . ')',
                            $y + 17,
                            $poFontSize
                        );

                        $y += $poHeight + $gapPoSerial;

                        // =========================================
                        // SERIAL
                        // =========================================

                        $drawText(
                            $image,
                            $serial,
                            $y + 25,
                            $serialFontSize
                        );

                        // =========================================
                        // JPEG 300 DPI
                        // =========================================

                        if (function_exists('imageresolution')) {
                            imageresolution($image, 300, 300);
                        }

                        ob_start();
                        $encoded = imagejpeg($image, null, 95);
                        $jpegData = ob_get_clean();

                        if (!$encoded || !is_string($jpegData) || $jpegData === '') {
                            throw new \RuntimeException('Gagal membuat JPEG.');
                        }

                        // Nama file tanpa angka nol di depan.
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
