<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // <- TAMBAHKAN INI

class ProductImport implements ToCollection
{
    private $spreadsheet;
    private $photoColumn;
    private $codeColumn;

    public function __construct($filePath, $photoColumn = 'A', $codeColumn = 'C')
    {
        $this->spreadsheet = IOFactory::load($filePath);
        $this->photoColumn = $photoColumn;
        $this->codeColumn = $codeColumn;
    }

    public function collection(Collection $rows)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Dapat nomor kolom dari huruf
      $photoIndex = Coordinate::columnIndexFromString($this->photoColumn) - 1;
        $codeIndex = Coordinate::columnIndexFromString($this->codeColumn) - 1;


        foreach ($rows as $rowIndex => $item) {
            $code = $item[$codeIndex] ?? '';
            if ($code == '') {
                continue;
            }

            // Simpan Product
            $product = Product::create([
                'code'    => $code,
                'content' => $item[$codeIndex],
                // additional...
            ]);

            // Loop gambar di sheet
            foreach ($sheet->getDrawingCollection() as $drawing) {
                $coordinates = $drawing->getCoordinates();

                // Cek row yang sesuai
                if((int)filter_var($coordinates,FILTER_SANITIZE_NUMBER_INT) == $rowIndex + 1) { // +2 karena row 1 = heading
                    $filename = $this->extractImage($drawing, $code);
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => 'storage/products/' . $filename,
                        'created_by' => $product->created_by,
                        'file_name'  => $code,
                    ]);
                }
            }
        }
    }

    // extractImage tetap sama...


    private function extractImage($drawing, $code)
    {
        if ($drawing instanceof MemoryDrawing) {
            ob_start();
            call_user_func(
                $drawing->getRenderingFunction(),
                $drawing->getImageResource()
            );
            $imageContents = ob_get_contents();
            ob_end_clean();

            $extension = '';
            switch ($drawing->getMimeType()) {
                case MemoryDrawing::MIMETYPE_PNG:
                    $extension = 'png';
                    break;
                case MemoryDrawing::MIMETYPE_GIF:
                    $extension = 'gif';
                    break;
                case MemoryDrawing::MIMETYPE_JPEG:
                    $extension = 'jpg';
                    break;
                default:
                    $extension = 'png';
            }
        } else {
            $zipReader = fopen($drawing->getPath(), 'r');
            $imageContents = '';
            while (!feof($zipReader)) {
                $imageContents .= fread($zipReader, 1024);
            }
            fclose($zipReader);
            $extension = $drawing->getExtension();
        }

        // Bersihkan nama file
        // $codeSafe = preg_replace('/[\/\s]+/', '_', $code);
        $codeSafe = preg_replace('/[\/\s]+/', ' ', $code);

        $filename = $codeSafe . '.' . $extension;

        $directory = storage_path('app/public/products');
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . '/' . $filename;

        file_put_contents($path, $imageContents);

        return $filename;
    }
}

