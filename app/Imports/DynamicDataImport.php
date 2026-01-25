<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class DynamicDataImport implements ToCollection, WithCalculatedFormulas
{
    public array $headers = [];
    public array $items   = [];

    public function collection(Collection $rows)
    {
        // =============================
        // HEADER ROW (A12)
        // =============================
        $this->headers = $this->buildHeaders($rows[12]);

        // =============================
        // DATA ROWS
        // =============================
        for ($i = 12; $i < $rows->count(); $i++) {

            if (! isset($rows[$i][0]) || ! is_numeric($rows[$i][0])) {
                continue;
            }

            $item = [];

            foreach ($this->headers as $col => $key) {
                if ($key === null) {
                    continue;
                }

                $item[$key] = $rows[$i][$col] ?? null;
            }

            $item['photo'] = null;

            $this->items[$i] = $item;
        }
    }

    // ==================================================
    // BUILD HEADER (FIXED: ONLY W & D IF THAT'S ALL)
    // ==================================================
    private function buildHeaders($row): array
    {
        $headers   = [];
        $skipUntil = -1;

        foreach ($row as $col => $cell) {

            // =============================
            // SKIP COL YANG SUDAH DIPAKAI
            // =============================
            if ($col <= $skipUntil) {
                continue;
            }

            $cell = trim((string) $cell);

            // =============================
            // ITEM DIMENSION (MERGED)
            // =============================
            if (
                stripos($cell, 'item') !== false &&
                stripos($cell, 'dimention') !== false
            ) {
                $headers[$col]     = 'item_w_inch';
                $headers[$col + 1] = 'item_d_inch';
                $headers[$col + 2] = 'item_h_inch';

                $skipUntil = $col + 2;
                continue;
            }

            // =============================
            // PACKING DIMENSION (MERGED)
            // =============================
            if (
                stripos($cell, 'packing') !== false &&
                stripos($cell, 'dimention') !== false
            ) {
                $headers[$col]     = 'packing_w_inch';
                $headers[$col + 1] = 'packing_d_inch';
                $headers[$col + 2] = 'packing_h_inch';

                $skipUntil = $col + 2;
                continue;
            }

            // =============================
            // NORMAL HEADER
            // =============================
            if ($cell !== '') {
                $headers[$col] = $this->key($cell);
            } else {
                $headers[$col] = null;
            }
        }

        return $headers;
    }

    // ==================================================
    // IMAGE HANDLER
    // ==================================================
    public function setPhotos(iterable $drawings): void
    {
        foreach ($drawings as $drawing) {

            if (! $drawing instanceof BaseDrawing) {
                continue;
            }

            preg_match('/([A-Z]+)(\d+)/', $drawing->getCoordinates(), $m);
            $rowIndex = ((int) $m[2]) - 1;

            if (! isset($this->items[$rowIndex])) {
                continue;
            }

            if ($drawing instanceof Drawing) {
                $image = file_get_contents($drawing->getPath());
                $mime  = mime_content_type($drawing->getPath());
            } elseif ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $image = ob_get_clean();
                $mime  = $drawing->getMimeType();
            } else {
                continue;
            }

            $this->items[$rowIndex]['photo'] =
            "data:$mime;base64," . base64_encode($image);
        }
    }

    private function key(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_');
    }
}
