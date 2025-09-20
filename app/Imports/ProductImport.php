<?php
namespace App\Imports;

use App\Models\ProductPameran;
use App\Models\Exhibition;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Facades\Log;

class ProductPameranImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithDrawings
{
    private $exhibitionId;
    private $drawings = [];

    public function __construct($exhibitionId)
    {
        $this->exhibitionId = $exhibitionId;
    }

    public function drawings()
    {
        return $this->drawings;
    }

    public function setDrawings(array $drawings)
    {
        $this->drawings = $drawings;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function onRow(Row $row)
    {
        $index = $row->getIndex();
        $data  = $this->normalizeRow($row->toArray());
        $articleCode = $data['article_code'] ?? $data['name'] ?? "item_{$index}";

        // Simpan foto jika ada
        $filename = $this->saveImageIfExist($index, $articleCode);

        // Simpan ke DB
        ProductPameran::create([
            'exhibition_id'  => $this->exhibitionId,
            'article_code'   => $articleCode,
            'name'           => $data['name'] ?? '',
            'categories'     => $data['categories'] ?? '',
            'remark'         => $data['remark'] ?? '',
            'item_w'         => (int) ($data['item_w'] ?? 0),
            'item_d'         => (int) ($data['item_d'] ?? 0),
            'item_h'         => (int) ($data['item_h'] ?? 0),
            'packing_w'      => (int) ($data['packing_w'] ?? 0),
            'packing_d'      => (int) ($data['packing_d'] ?? 0),
            'packing_h'      => (int) ($data['packing_h'] ?? 0),
            'set2'           => $data['set2'] ?? null,
            'set3'           => $data['set3'] ?? null,
            'set4'           => $data['set4'] ?? null,
            'set5'           => $data['set5'] ?? null,
            'composition'    => $data['composition'] ?? '',
            'finishing'      => $data['finishing'] ?? '',
            'qty'            => (int) ($data['qty'] ?? 0),
            'cbm'            => (float) ($data['cbm'] ?? 0),
            'loadability_20' => (float) ($data['loadability_20'] ?? 0),
            'loadability_40' => (float) ($data['loadability_40'] ?? 0),
            'loadability_40hc'=> (float) ($data['loadability_40hc'] ?? 0),
            'rangka'         => $data['rangka'] ?? 0,
            'anyam'          => $data['anyam'] ?? 0,
            'finishing_powder_coating' => $data['finishing_powder_coating'] ?? 0,
            'accessories_final'        => $data['accessories_final'] ?? 0,
            'electricity'              => $data['electricity'] ?? 0,
            'photo'                    => $filename,
        ]);
    }

    private function normalizeRow($row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if (is_string($value) && str_starts_with($value, '=')) {
                try {
                    $value = eval('return ' . substr($value, 1) . ';');
                } catch (\Throwable $e) {
                    $value = null;
                }
            }
            $cleanKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $key));
            $cleanKey = preg_replace('/_+/', '_', $cleanKey);
            $cleanKey = trim($cleanKey, '_');
            $normalized[$cleanKey] = $value;
        }
        return $normalized;
    }

    private function saveImageIfExist($rowIndex, $code): ?string
    {
        foreach ($this->drawings as $drawing) {
            $cell = $drawing->getCoordinates();
            preg_match('/([A-Z]+)([0-9]+)/', $cell, $matches);

            if ($matches && intval($matches[2]) === $rowIndex) {
                $imageContents = $drawing instanceof MemoryDrawing
                    ? (function () use ($drawing) {
                        ob_start();
                        call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                        return ob_get_clean();
                    })()
                    : file_get_contents($drawing->getPath());

                $extension = $drawing instanceof MemoryDrawing
                    ? match ($drawing->getMimeType()) {
                        MemoryDrawing::MIMETYPE_PNG => 'png',
                        MemoryDrawing::MIMETYPE_JPEG => 'jpg',
                        MemoryDrawing::MIMETYPE_GIF => 'gif',
                        default => 'jpg',
                    }
                    : $drawing->getExtension();

                $filename = preg_replace('/[^\w\-]/', '_', $code) . '.' . $extension;
                $path = storage_path('app/public/products');

                if (!is_dir($path)) mkdir($path, 0777, true);

                file_put_contents("$path/$filename", $imageContents);

                return "products/$filename";
            }
        }
        return null;
    }
}
