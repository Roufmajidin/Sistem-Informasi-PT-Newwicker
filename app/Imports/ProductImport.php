<?php
namespace App\Imports;

use App\Models\Barangs;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Facades\Log;

class ProductImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithDrawings
{
    private $buyerId;
    private $photoColumn;
    private $codeColumn;
    private $drawings = [];

    public function __construct($buyerId, $photoColumn = 'A', $codeColumn = 'D')
    {
        $this->buyerId     = $buyerId;
        $this->photoColumn = $photoColumn;
        $this->codeColumn  = $codeColumn;
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
        return 200; // Bisa disesuaikan, makin kecil makin ringan
    }

    public function onRow(Row $row)
    {
        $index = $row->getIndex(); // Baris ke-...
        $data  = $this->normalizeRow($row->toArray());

        $articleNr = $data['article_nr'] ?? $data['description'] ?? null;

        // if (! $articleNr || Barangs::where('article_nr', $articleNr)->exists()) {
        //     return;
        // }

        $filename = $this->saveImageIfExist($index, $articleNr);
        Log::info("Import baris ke-{$row->getIndex()} | article_nr: {$articleNr}");

        Barangs::create([
            'buyer_id'               => $this->buyerId,
            'photo'                  => $filename,
            'buyer_s_code'           => $data['buyers_code'] ?? '',
            'description'            => $data['description'] ?? '',
            'article_nr'             => $articleNr,
            'remark'                 => $data['remark'] ?? '',
            'cushion'                => $data['cushion'] ?? '',
            'glass_orMirror'         => $data['glass_or_mirror'] ?? '',
            'uom'                    => $data['uom'] ?? '',
            'w'                      => (int) ($data['w'] ?? 0),
            'd'                      => (int) ($data['d'] ?? 0),
            'h'                      => (int) ($data['h'] ?? 0),
            'sw'                     => (int) ($data['sw'] ?? 0),
            'sh'                     => (int) ($data['sh'] ?? 0),
            'sd'                     => (int) ($data['sd'] ?? 0),
            'ah'                     => (int) ($data['ah'] ?? 0),
            'weight_capacity'        => $data['weight_capacity_kg_lt'] ?? 0,
            'materials'              => $data['materials'] ?? '',
            'finishes_color'         => $data['finishing_color_of_item'] ?? '',
            'weaving_composition'    => $data['weaving_composition'] ?? '',
            'usd_selling_price'      => (float) ($data['usd_selling_price'] ?? 0),
            'packing_dimention'      => $data['packing_dimention_cm'] ?? '',
            'nw'                     => $data['nw_kg'] ?? 0,
            'gw'                     => $data['gw_kg'] ?? 0,
            'cbm'                    => $data['cbm'] ?? 0,
            'accessories'            => $data['accessories'] ?? '',
            'picture_of_accessories' => $data['picture_of_accessories'] ?? '',
            'leather'                => $data['leather'] ?? '',
            'picture_of_leather'     => $data['picture_of_leather'] ?? '',
            'finish_steps'           => $data['finish_steps'] ?? '',
            'harga_supplier'         => $data['harga_supplier'] ?? '',
            'electricity'            => $data['electricity'] ?? '',
            'comment_visit'          => $data['comment_visit'] ?? '',
            'loadability'            => $data['loadability'] ?? '',
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

            $cleanKey = strtolower(preg_replace('/[^a-z0-9]/i', '_', $key));
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
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    return ob_get_clean();
                })()
                : file_get_contents($drawing->getPath());

                $extension = $drawing instanceof MemoryDrawing
                ? match ($drawing->getMimeType()) {
                    MemoryDrawing::MIMETYPE_PNG => 'png',
                    MemoryDrawing::MIMETYPE_JPEG => 'jpg',
                    MemoryDrawing::MIMETYPE_GIF  => 'gif',
                    default                      => 'jpg',
                }
                : $drawing->getExtension();

                $filename = preg_replace('/[^\w\-]/', '_', $code) . '.' . $extension;
                $path     = storage_path('app/public/products');

                if (! is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                file_put_contents("$path/$filename", $imageContents);

                return "products/$filename";
            }
        }

        return null;
    }
}
