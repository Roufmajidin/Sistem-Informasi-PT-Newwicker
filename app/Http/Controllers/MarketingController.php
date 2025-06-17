<?php
namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\Barangs;
use App\Models\Buyer;
use App\Models\Buyyer;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
                    'weight_capacity'        => (int) ($temp['weight_capacity_kg_lt'] ?? 0),
                    'materials'              => $temp['materials'] ?? '',
                    'finishes_color'         => $temp['finishes_color'] ?? '',
                    'weaving_composition'    => $temp['weaving_composition'] ?? '',
                    'usd_selling_price'      => (int) ($temp['usd_selling_price'] ?? 0),
                    'packing_dimention'      => $temp['packing_dimention_cm'] ?? '',
                    'nw'                     => (int) ($temp['nw'] ?? 0),
                    'gw'                     => (int) ($temp['gw'] ?? 0),
                    'cbm'                    => (int) ($temp['cbm'] ?? 0),
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
        $file  = $request->file('file'); // File Excel yang diupload
        $range = explode(' ', $request->input('range'));

        $import = new ProductImport($file->getPathname(), $range[0], $range[1]);

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

  public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
        'range' => 'required|string'
    ]);

    // range misalnya diberi "A C"
    $range = explode(' ', $request->input('range'));

    $photoColumn = $range[0] ?? '';
    $codeColumn = $range[1] ?? '';

    Excel::import(new ProductImport($request->file('file')->getPathname(), $photoColumn, $codeColumn));

    return redirect()->back()->with('success', 'Produk berhasil diimport!');
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
        $db = Buyyer::find($id);
        // dd($db);
        return view('pages.detail_buyer', compact('buyer', 'db'));
    }

    public function update(Request $request)
    {
    	if($request->ajax()){

	      $d= 	Barangs::find($request->input('pk'))->update([$request->input('name') => $request->input('value')]);

            dd($d);
            // return response()->json(['success' => true]);
    	}
    }
    public function updateInline(Request $request)
{
    $buyer = Barangs::find($request->input('pk'));

    if (!$buyer) {
        return response()->json(['status' => 'error', 'msg' => 'Data not found.']);
    }

    $field = $request->input('name');
    $value = $request->input('value');

    $allowed = [
        'description', 'article_nr', 'remark', 'cushion', 'glass_orMirror', 'materials',
        'weight_capacity', 'finishes_color', 'usd_selling_price',
        'packing_dimention', 'nw', 'gw', 'cbm', 'accessories',
        'picture_of_accessories', 'finish_steps', 'harga_supplier',
        'loadability', 'electricity', 'comment_visit'
    ];

    if (!in_array($field, $allowed)) {
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
