<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ImageImport implements ToCollection
{
    protected $drawings;
    public $items = [];
    protected $imagePerRow = [];
    protected $imageLoaded = false;

    public function __construct($drawings)
    {
        $this->drawings = $drawings;
    }

    public function collection(Collection $rows)
    {
        // ================= LOAD GAMBAR =================
        if (!$this->imageLoaded) {

            foreach ($this->drawings as $drawing) {

                preg_match('/\d+/', $drawing->getCoordinates(), $matches);
                $rowNumber = $matches[0];

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {

                    ob_start();
                    call_user_func(
                        $drawing->getRenderingFunction(),
                        $drawing->getImageResource()
                    );
                    $imageContents = ob_get_clean();
                    $extension = 'png';

                } else {

                    $imageContents = file_get_contents($drawing->getPath());
                    $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
                }

                $fileName = uniqid() . '.' . $extension;

                $savePath = public_path('images/products/');
                if (!file_exists($savePath)) mkdir($savePath, 0777, true);

                file_put_contents($savePath . $fileName, $imageContents);

                // mapping row -> image path
                $this->imagePerRow[$rowNumber] = '/images/products/' . $fileName;
            }

            $this->imageLoaded = true;
        }

        // ================= LOAD DATA =================
        foreach ($rows as $index => $row) {

            if ($index == 0) continue; // skip header

            $excelRow = $index + 1;

            $this->items[] = [
                'photo' => $this->imagePerRow[$excelRow] ?? null,
                'description' => $row[2] ?? null,
                'article_nr' => $row[3] ?? null,
                'remark' => $row[5] ?? null
            ];
        }
    }
}

