<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankDataController extends Controller
{
    //
    public function index()
    {
        return view('pages.bank_data.index');
    }
    public function upload(Request $request)
{
    $file = $request->file('excel_file');

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);

    $sheet = $spreadsheet->getActiveSheet();

    $rows = $sheet->toArray();

    $images = [];

    foreach ($sheet->getDrawingCollection() as $drawing) {

        $coordinates = $drawing->getCoordinates();

        preg_match('/([A-Z]+)(\d+)/', $coordinates, $matches);

        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($matches[1]) - 1;

        $row = $matches[2] - 1;

        $key = $row . '-' . $col;

        // memory drawing
        if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {

            ob_start();

            call_user_func(
                $drawing->getRenderingFunction(),
                $drawing->getImageResource()
            );

            $imageContents = ob_get_contents();

            ob_end_clean();

            $base64 = 'data:image/png;base64,' . base64_encode($imageContents);

            $images[$key] = $base64;
        }

        // normal drawing
        else {

            $path = $drawing->getPath();

            $mime = mime_content_type($path);

            $data = file_get_contents($path);

            $base64 = 'data:' . $mime . ';base64,' . base64_encode($data);

            $images[$key] = $base64;
        }
    }

    return response()->json([
        'rows' => $rows,
        'images' => $images
    ]);
}
}
