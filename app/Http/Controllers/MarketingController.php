<?php
namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Jobs\ImportProdukJob;
use App\Models\Barangs;
use App\Models\Buyer;
use App\Models\Buyyer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;

class MarketingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $products = Product::where('buyer_id', 38)->get();
        $buyer    = Buyer::get();
        // dd($products);
        return view('pages.marketing', compact('buyer', 'products'));
    }
    public function buyyerList()
    {
        //
        $lb = Buyyer::get();
        // dd($lb);
        return view('pages.buyer_list', compact('lb'));
    }
    public function search(Request $request)
    {
        $search = $request->input('search');

        $buyers = Buyyer::query()
            ->where('name', 'LIKE', '%' . $search . '%')
            ->get();

        return response()->json($buyers);
    }
    public function importb(Request $request)
    {
        $id_buyer = $request->input('buyer_id'); // <- ambil dari form

        // Simpan CSV yang diupload
        $file     = $request->file('file');
        $filename = time() . '-' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);
        $input = public_path('uploads/' . $filename);

        // Menghandle Mac newline
        $content = file_get_contents($input);
        $content = str_replace("\r", "\n", $content);
        file_put_contents($input, $content);

        // Setelah diberesin newline, baca CSV dan insert sekaligus
        $handle = fopen($input, "r");

        $header = [];

        $row = 0;

        while (($item = fgetcsv($handle, 1000, ",")) !== false) {
            if ($row == 0) {
                foreach ($item as &$heading) {
                    // Menghilangkan spasi dan karakter yang tak diinginkan
                    $heading = strtolower(str_replace([' ', "'"], '_', $heading));
                    $heading = str_replace(['.'], '', $heading);
                }
                $header = $item;
            } else {
                $temp = [];
                foreach ($item as $i => $val) {
                    $temp[$header[$i]] = $val;
                }

                // debug sebelum insert
                // dump($temp);
                // die;
                if (empty(array_filter($temp))) {
                    continue;
                }
                // Cek jika data sudah ada (misalnya berdasarkan article_nr)
                $exists = Barangs::where('article_nr', $temp['article_nr'] ?? '')->exists();

                if ($exists) {
                    continue;
                }

                Barangs::create([
                    'photo'                  => $temp['photo'] ?? '',
                    'buyer_s_code'           => $temp['buyer_s_code'] ?? '',
                    'description'            => $temp['description'] ?? '',
                    'article_nr'             => $temp['article_nr'] ?? '',
                    'remark'                 => $temp['remark'] ?? '',
                    'cushion'                => $temp['cushion'] ?? '',
                    'glass_orMirror'         => $temp['glass_orMirror'] ?? '',
                    'uom'                    => $temp['uom'] ?? '',
                    'w'                      => (int) ($temp['w'] ?? 0),
                    'd'                      => (int) ($temp['d'] ?? 0),
                    'h'                      => (int) ($temp['h'] ?? 0),
                    'sw'                     => (int) ($temp['sw'] ?? 0),
                    'sh'                     => (int) ($temp['sh'] ?? 0),
                    'sd'                     => (int) ($temp['sd'] ?? 0),
                    'ah'                     => (int) ($temp['ah'] ?? 0),
                    'weight_capacity'        => ($temp['weight_capacity_kg_lt'] ?? 0),
                    'materials'              => $temp['materials'] ?? '',
                    'finishes_color'         => $temp['finishes_color'] ?? '',
                    'weaving_composition'    => $temp['weaving_composition'] ?? '',
                    'usd_selling_price'      => (int) ($temp['usd_selling_price'] ?? 0),
                    'packing_dimention'      => $temp['packing_dimention_cm'] ?? '',
                    'nw'                     => ($temp['nw'] ?? 0),
                    'gw'                     => ($temp['gw'] ?? 0),
                    'cbm'                    => ($temp['cbm'] ?? 0),
                    'accessories'            => $temp['accessories'] ?? '',
                    'picture_of_accessories' => $temp['picture_of_accessories'] ?? '',
                    'leather'                => $temp['leather'] ?? '',
                    'picture_of_leather'     => $temp['picture_of_leather'] ?? '',
                    'finish_steps'           => $temp['finish_steps'] ?? '',
                    'harga_supplier'         => $temp['harga_supplier'] ?? '',
                    'electricity'            => $temp['electricity'] ?? '',
                    'comment_visit'          => $temp['comment_visit'] ?? '',
                    'loadability'            => $temp['loadability'] ?? '',
                    'buyer_id'               => $id_buyer,
                ]);
            }

            $row++;
        }

        fclose($handle);

        return redirect()
            ->back()
            ->with('status', 'Import CSV berhasil');
    }

    /**
     * Show the form for creating a new resource.
     */

    //  TODO iMPORT IMAGE

    public function importImage(Request $request)
    {
        $id_buyer = $request->input('buyer_id'); // <- ambil dari form
        $file     = $request->file('file');      // File Excel yang diupload
        $range    = explode(' ', $request->input('range'));
// kirim buyer_id juga
        // $import = new ProductImport($file->getPathname(), $range[0], $range[1]);
        $import      = new ProductImport($id_buyer, $range[0], $range[1]);
        $spreadsheet = IOFactory::load($file->getPathname());
        $drawings    = iterator_to_array($spreadsheet->getActiveSheet()->getDrawingCollection());

        $import->setDrawings($drawings);
        // $import->import($file);
        Excel::import($import, $file);

        return redirect()->back()->with('status', 'Import sukses!');
    }

    private function extractImage($drawing, $fileBaseName)
    {
        if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
            ob_start();
            call_user_func(
                $drawing->getRenderingFunction(),
                $drawing->getImageResource()
            );
            $imageContents = ob_get_contents();
            ob_end_clean();

            $extension = '';
            switch ($drawing->getMimeType()) {
                case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_PNG:
                    $extension = 'png';
                    break;
                case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_GIF:
                    $extension = 'gif';
                    break;
                case \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_JPEG:
                    $extension = 'jpg';
                    break;
                default:
                    $extension = 'png';
            }
        } else {
            $zipReader     = fopen($drawing->getPath(), 'r');
            $imageContents = '';
            while (! feof($zipReader)) {
                $imageContents .= fread($zipReader, 1024);
            }
            fclose($zipReader);
            $extension = $drawing->getExtension();
        }

        // Pastikan directory ada di storage/app/public/products
        $directory = storage_path('app/public/products');
        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = time() . '-' . $fileBaseName . '.' . $extension;
        $path     = $directory . '/' . $filename;

        file_put_contents($path, $imageContents);

        return 'storage/products/' . $filename;
    }
    // TODO:: queue
    public function import(Request $request)
    {
        $request->validate([
            'file'  => 'required|file|mimes:xlsx,xls,csv',
            'range' => 'required|string',
        ]);

        $file     = $request->file('file');
        $range    = explode(' ', $request->input('range'));
        $photoCol = $range[0] ?? 'A';
        $codeCol  = $range[1] ?? 'D';

        // Simpan file ke storage
        $path = $file->storeAs('imports', uniqid() . '_' . $file->getClientOriginalName());
        Log::info("✅ File berhasil disimpan di: " . storage_path("app/$path"));

        // Jalankan Job (queue)
        ImportProdukJob::dispatch(storage_path("app/$path"), Auth::user()->id, $photoCol, $codeCol);

        return back()->with('success', 'Import sedang diproses di background.');
    }
    public function scan($id)
    {
        $barang = \App\Models\Barangs::where('article_nr', $id)->first();

        if ($barang) {
            // dd($barang);
            return response()->json([
                'success' => true,
                'data'    => $barang,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }
    }
    // export

    public function export(Request $request)
    {
        $cartItems = json_decode($request->items, true);

        if (empty($cartItems)) {
            return back()->with('error', 'Data produk kosong.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $sheet->mergeCells('D2:R2')->setCellValue('D2', 'PT. NEWWICKER INDONESIA');
        $sheet->mergeCells('D3:R3')->setCellValue('D3', 'Jalan Kisaba Lanang RT/RW 019/002 Bode Lor, Plumbon Cirebon 45155 – Indonesia');
        $sheet->mergeCells('D4:R4')->setCellValue('D4', 'office@newwicker.com | info@newwicker.com | Website : www.newwicker.com');
        $sheet->getStyle('D2')->getFont()->setBold(true);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $info = [
            ['Order No.', ': NWS 25 – 39'],
            ['Company Name', ': PILLOWTALK'],
            ['Country', ': AUSTRALIA'],
            ['Shipment Date', ': 1 JULY 2025'],
            ['Packing', ': Carton Boxes'],
            ['Contact Person', ':'],
        ];
        $sheet->fromArray($info, null, 'B6');

        $sheet->getStyle('C10')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            'font' => ['bold' => true],
        ]);

        $sheet->setCellValue('A12', 'No.');
        $sheet->setCellValue('B12', 'Photo');
        $sheet->setCellValue('C12', 'Description');
        $sheet->setCellValue('D12', 'Article Nr.');
        $sheet->setCellValue('E12', 'Remark');
        $sheet->setCellValue('F12', 'Cushion');
        $sheet->setCellValue('G12', 'Glass');
        $sheet->setCellValue('H12', 'Item Dimention (CM)');
        $sheet->setCellValue('K12', 'Packing Dimention (CM)');
        $sheet->setCellValue('N12', 'Composition');
        $sheet->setCellValue('O12', 'Finishing');
        $sheet->setCellValue('P12', 'QTY');
        $sheet->setCellValue('Q12', 'CBM');
        $sheet->setCellValue('R12', 'FOB JAKARTA IN USD');
        $sheet->setCellValue('S12', 'Total CBM');
        $sheet->setCellValue('T12', 'Value in USD');

        $sheet->setCellValue('H13', 'W');
        $sheet->setCellValue('I13', 'D');
        $sheet->setCellValue('J13', 'H');
        $sheet->setCellValue('K13', 'W');
        $sheet->setCellValue('L13', 'D');
        $sheet->setCellValue('M13', 'H');

        $merges = [
            'A12:A13', 'B12:B13', 'C12:C13', 'D12:D13', 'E12:E13', 'F12:F13', 'G12:G13',
            'H12:J12', 'K12:M12', 'N12:N13', 'O12:O13', 'P12:P13', 'Q12:Q13', 'R12:R13', 'S12:S13', 'T12:T13',
        ];
        foreach ($merges as $merge) {
            $sheet->mergeCells($merge);
        }

        $sheet->getStyle('A12:T13')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]],
        ]);

        $sheet->getStyle('R12:R13')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
        ]);

        $row = 14;
        foreach ($cartItems as $index => $item) {
            $sheet->fromArray([
                $index + 1,
                '',
                $item['description'] ?? '-',
                $item['article_nr'] ?? '-',
                $item['remark'] ?? '-',
                $item['cushion'] ?? '-',
                $item['glass_orMirror'] ?? '-',
                $item['w'] ?? '-',
                $item['d'] ?? '-',
                $item['h'] ?? '-',
                $item['pw'] ?? '-',
                $item['pd'] ?? '-',
                $item['ph'] ?? '-',
                $item['materials'] ?? '-',
                $item['finishes_color'] ?? '-',
                $item['qty'] ?? 0,
                $item['cbm'] ?? '0.00',
                $this->idr($item['usd_selling_price']),
                $item['total_cbm'] ?? '0.00',
                $this->idr($item['value_in_usd'] ?? 0),
            ], null, 'A' . $row);

            if (! empty($item['photo'])) {
                $drawing = new Drawing();
                $drawing->setPath(public_path('storage/' . $item['photo']));
                $drawing->setHeight(60);
                $drawing->setCoordinates('B' . $row);
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension($row)->setRowHeight(45);
            }

            $sheet->getStyle("A{$row}:T{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR]],
            ]);

            $row++;
        }

        $columnWidths = [
            'A' => 4, 'B'  => 15, 'C' => 16, 'D' => 16,
            'E' => 16, 'F' => 16, 'G' => 10,
            'H' => 4, 'I'  => 4, 'J'  => 4,
            'K' => 4, 'L'  => 4, 'M'  => 4,
            'N' => 16, 'O' => 16,
            'P' => 6, 'Q'  => 10,
            'R' => 10, 'S' => 10, 'T' => 14,
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->getStyle("C14:C{$row}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("E14:E{$row}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("F14:F{$row}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("C14:F{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("C14:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        for ($r = 14; $r < $row; $r++) {
            if (empty($cartItems[$r - 14]['photo'])) {
                $sheet->getRowDimension($r)->setRowHeight(-1);
            }
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'export_cart.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function idr($usd)
    {
        return number_format(floatval($usd) * 16000, 0, ',', '.');
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $buyer = Barangs::where('buyer_id', $id)->get();
        $db    = Buyyer::find($id);
        // dd($db);
        return view('pages.detail_buyer', compact('buyer', 'db'));
    }

    public function update(Request $request)
    {
        if ($request->ajax()) {

            $d = Barangs::find($request->input('pk'))->update([$request->input('name') => $request->input('value')]);

            dd($d);
            // return response()->json(['success' => true]);
        }
    }
    public function updateInline(Request $request)
    {
        $buyer = Barangs::find($request->input('pk'));

        if (! $buyer) {
            return response()->json(['status' => 'error', 'msg' => 'Data not found.']);
        }

        $field = $request->input('name');
        $value = $request->input('value');

        $allowed = [
            'description', 'article_nr', 'remark', 'cushion', 'glass_orMirror', 'materials      ',
            'weight_capacity', 'finishes_color', 'usd_selling_price',
            'packing_dimention', 'nw', 'gw', 'cbm', 'accessories',
            'picture_of_accessories', 'finish_steps', 'harga_supplier',
            'loadability', 'electricity', 'comment_visit', 'w', 'd', 'h',
        ];

        if (! in_array($field, $allowed)) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid field.']);
        }

        $buyer->$field = $value;
        $buyer->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
