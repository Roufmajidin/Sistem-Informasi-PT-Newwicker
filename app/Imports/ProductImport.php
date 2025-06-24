<?php

namespace App\Imports;

use App\Models\Barangs;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ProductImport implements ToCollection, WithHeadingRow, WithDrawings
{
    private $drawings = [];
    private $buyerId;
    private $photoColumn;
    private $codeColumn;
    private $normalizedHeaders = [];

public function __construct($buyerId, $photoColumn = 'A', $codeColumn = 'D')
    {
            $this->buyerId = $buyerId;

        $this->photoColumn = $photoColumn;
        $this->codeColumn = $codeColumn;
    }

    public function drawings()
    {
        return $this->drawings;
    }

    public function setDrawings(array $drawings)
    {
        $this->drawings = $drawings;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {

        foreach ($rows as $index => $row) {
            $cleaned = $this->normalizeRow($row);

            if (empty($cleaned['article_nr'])) continue;

            if (Barangs::where('article_nr', $cleaned['article_nr'])->exists()) continue;

            $filename = $this->saveImageIfExist($index + 2, $cleaned['article_nr']); // +2 karena row Excel dimulai dari 1 dan baris ke-2 adalah data pertama

            Barangs::create([
                  'buyer_id'               => $this->buyerId, // ← YANG BENAR

                'photo'                  => $filename,
                'buyer_s_code'           => $cleaned['buyer_s_code'] ?? '',
                'description'            => $cleaned['description'] ?? '',
                'article_nr'             => $cleaned['article_nr'] ?? '',
                'remark'                 => $cleaned['remark'] ?? '',
                'cushion'                => $cleaned['cushion'] ?? '',
                'glass_orMirror'         => $cleaned['glass_or_mirror'] ?? '',
                'uom'                    => $cleaned['uom'] ?? '',
                'w'                      => (int)($cleaned['w'] ?? 0),
                'd'                      => (int)($cleaned['d'] ?? 0),
                'h'                      => (int)($cleaned['h'] ?? 0),
                'sw'                     => (int)($cleaned['sw'] ?? 0),
                'sh'                     => (int)($cleaned['sh'] ?? 0),
                'sd'                     => (int)($cleaned['sd'] ?? 0),
                'ah'                     => (int)($cleaned['ah'] ?? 0),
                'weight_capacity'        => $cleaned['weight_capacity_kg_lt'] ?? 0,
                'materials'              => $cleaned['materials'] ?? '',
                'finishes_color'         => $cleaned['finishing_color_of_item'] ?? '',
                'weaving_composition'    => $cleaned['weaving_composition'] ?? '',
                'usd_selling_price'      => (float)($cleaned['usd_selling_price'] ?? 0),
                'packing_dimention'      => $cleaned['packing_dimention_cm'] ?? '',
                'nw'                     => $cleaned['nw_kg'] ?? 0,
                'gw'                     => $cleaned['gw_kg'] ?? 0,
                'cbm'                    => $cleaned['cbm'] ?? 0,
                'accessories'            => $cleaned['accessories'] ?? '',
                'picture_of_accessories' => $cleaned['picture_of_accessories'] ?? '',
                'leather'                => $cleaned['leather'] ?? '',
                'picture_of_leather'     => $cleaned['picture_of_leather'] ?? '',
                'finish_steps'           => $cleaned['finish_steps'] ?? '',
                'harga_supplier'         => $cleaned['harga_supplier'] ?? '',
                'electricity'            => $cleaned['electricity'] ?? '',
                'comment_visit'          => $cleaned['comment_visit'] ?? '',
                'loadability'            => $cleaned['loadability'] ?? '',
            ]);
        }
    }
    //
    private $map = [
    // key target => semua varian header yang mungkin muncul setelah “bersih”
    'buyer_s_code' => [
        'buyers_code',      // tanpa apostrof
        'buyer_s_code',     // istilah bersih
        'buyer_s_code_',    // kalau ada underscore tambahan
        'buyer_s_code_cm',  // contoh typo/varian lain
        // tambahkan semua varian yang `dd()` munculkan
    ],
    // … bisa tambahan mapping untuk header lain …
];

    //
   private function normalizeRow($row)
{
    $normalized = [];

    foreach ($row as $key => $value) {
        // 1) jika ini string yang dimulai dengan "=" → hitung ekspresinya
        if (is_string($value) && str_starts_with($value, '=')) {
            $expr = substr($value, 1);
            try {
                // cukup kalau cuma angka dan operator dasar
                $value = eval('return ' . $expr . ';');
            } catch (\Throwable $e) {
                $value = null; // gagal hitung
            }
        }

        // 2) normalisasi header seperti biasa
        $cleanKey = strtolower(preg_replace('/[^a-z0-9]/i', '_', $key));
        $cleanKey = preg_replace('/_+/', '_', $cleanKey);
        $cleanKey = trim($cleanKey, '_');

        $normalized[$cleanKey] = $value;
    }

    return $normalized;
}


    private function saveImageIfExist($excelRow, $code)
    {
        foreach ($this->drawings as $drawing) {
            $cell = $drawing->getCoordinates(); // Misalnya A2, A3, dll
            preg_match('/([A-Z]+)([0-9]+)/', $cell, $matches);

            if ($matches && intval($matches[2]) === $excelRow) {
                if ($drawing instanceof MemoryDrawing) {
                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $imageContents = ob_get_clean();

                    $extension = match ($drawing->getMimeType()) {
                        MemoryDrawing::MIMETYPE_PNG => 'png',
                        MemoryDrawing::MIMETYPE_JPEG => 'jpg',
                        MemoryDrawing::MIMETYPE_GIF => 'gif',
                        default => 'jpg',
                    };
                } else {
                    $imageContents = file_get_contents($drawing->getPath());
                    $extension = $drawing->getExtension();
                }

                $filename = preg_replace('/[^\w\-]/', '_', $code) . '.' . $extension;
                $path = storage_path('app/public/products');

                if (!is_dir($path)) mkdir($path, 0777, true);
                file_put_contents($path . '/' . $filename, $imageContents);

                return 'products/' . $filename;
            }
        }

        return null;
    }
}
