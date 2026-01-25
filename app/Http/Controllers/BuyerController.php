<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carts;
use App\Models\Exhibition;
use App\Models\NewBuyer;
use App\Models\ProductPameran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BuyerController extends Controller
{
    // GET all buyers
    public function viewClass()
    {
        return view('pages.cart-buyer.cart-buyer');
    }
    // public function index()
    // {
    //     return response()->json(NewBuyer::all());
    // }
    public function index()
    {
        // Ambil semua buyer
        $buyers = NewBuyer::orderBy('id', 'desc')->get();

        // Ambil semua cart sekaligus dan group per buyer_id
        $allCarts = Carts::all()->groupBy('buyer_id');

        // Ambil semua produk pameran sekaligus
        $allProducts = ProductPameran::all()->keyBy('article_code');

        // Ambil semua exhibition sekaligus
        $allExhibitions = Exhibition::all()->keyBy('id');

        // Map setiap buyer menjadi response
        $result = $buyers->map(function ($buyer) use ($allCarts, $allProducts, $allExhibitions) {

            $buyerCarts = $allCarts->get($buyer->buyer_id, collect());

            $itemsWithProduct = $buyerCarts->map(function ($cart) use ($allProducts, $allExhibitions) {
                $articleCode = trim($cart->article_code);
                $product     = $allProducts->get($articleCode);

                // Default photo
                $photoUrl = asset('images/default.jpg');

                if ($product) {
                    $exhibition = $allExhibitions->get($product->exhibition_id);
                    if ($exhibition) {
                        $photoPath = "pameran/{$exhibition->name}/{$articleCode}.webp";
                        $fullPath  = "public/{$photoPath}";
                        if (Storage::exists($fullPath)) {
                            $photoUrl = asset("storage/{$photoPath}");
                        }
                    }

                    // **Timpa photo di product_detail**
                    $product->photo = $photoUrl;
                }

                return [
                    "id"             => $cart->id,
                    "article_code"   => $cart->article_code,
                    "buyer_id"       => $cart->buyer_id,
                    "status"         => $cart->status,
                    "remark"         => $cart->remark,
                    "created_at"     => $cart->created_at,
                    "updated_at"     => $cart->updated_at,
                    "qty"            => $cart->qty,

                    "product_detail" => $product, // photo sudah terupdate
                    "isDeleted"      => $cart->isDeleted,
                ];
            });

        });

        return response()->json($result);
    }

    // POST create buyer
    public function store(Request $request)
    {
        $buyer = NewBuyer::create($request->all());
        return response()->json($buyer, 201);
    }

    public function show($id)
    {
        $buyer = NewBuyer::where('buyer_id', $id)->firstOrFail();
        $carts = Carts::where('buyer_id', $buyer->buyer_id)->get();

        $itemsWithProduct = $carts->map(function ($cart) {

            // bersihkan spasi
            $articleCode = trim($cart->article_code);

            // cari produk
            $product = ProductPameran::where('article_code', $articleCode)->first();

            // Default nilai photo
            $photoPath = null;
            $photoUrl  = asset('images/default.jpg'); // fallback gambar default

            if ($product) {

                // Cari exhibition terkait
                $exhibition = Exhibition::find($product->exhibition_id);

                if ($exhibition) {
                    // Bentuk path file yang disimpan saat import
                    $photoPath = "pameran/{$exhibition->name}/{$articleCode}.webp";

                    // Path yang dicek di storage
                    $fullPath = "public/{$photoPath}";

                    // Jika file ada → ambil URL storage
                    if (Storage::exists($fullPath)) {
                        $photoUrl = asset("storage/{$photoPath}");
                    }
                }
            }

            return [
                "id"             => $cart->id,
                "article_code"   => $cart->article_code,
                "buyer_id"       => $cart->buyer_id,
                "status"         => $cart->status,
                "remark"         => $cart->remark,
                "created_at"     => $cart->created_at,
                "updated_at"     => $cart->updated_at,
                "qty"            => $cart->qty,

                // detail product_pameran
                "product_detail" => $product,

                // foto
                "photo"          => $photoPath,
                "isDeleted"      => $cart->isDeleted,
            ];
        });

        return response()->json([
            "buyer"         => $buyer,
            "product_items" => $itemsWithProduct,
        ]);
    }

    // PUT/PATCH update
    public function update(Request $request, $id)
    {
        $buyer = NewBuyer::where('buyer_id', $id)->first();
        $buyer->update($request->all());
        return response()->json($buyer);
    }

    // DELETE
    public function destroy($id)
    {
        NewBuyer::destroy($id);
        return response()->json(['message' => 'Buyer deleted']);
    }

    public function product($id)
    {
        $product     = ProductPameran::where('article_code', $id)->first();
        $e           = Exhibition::find($product->exhibition_id);
        $articleCode = trim($product->article_code);
        $photoPath   = "pameran/{$e->name}/{$articleCode}.webp";
        $photoUrl    = asset("storage/{$photoPath}");

        $data = [
            'nr'                 => $product->id,
            'photo'              => $photoUrl,
            'article_code'       => $product->article_code,
            'name'               => $product->name,
            'categories'         => $product->categories,
            'remark'             => $product->remark,
            'item_dimension'     => ['w' => $product->item_w, 'd' => $product->item_d, 'h' => $product->item_h],
            'packing_dimension'  => ['w' => $product->packing_w, 'd' => $product->packing_d, 'h' => $product->packing_h],
            'size_of_set'        => [
                'set_2' => $product->set2,
                'set_3' => $product->set3,
                'set_4' => $product->set4,
                'set_5' => $product->set5,
            ],
            'composition'        => $product->composition,
            'finishing'          => $product->finishing,
            'cbm'                => round((float) $product->cbm, 2),
            'loadability_20'     => round((float) $product->loadability_20, 0),
            'loadability_40'     => round((float) $product->loadability_40, 0),
            'loadability_40_hc'  => round((float) $product->loadability_40hc, 0),
            'price_item'         => (double) $product->fob_jakarta_in_usd,
            'fob_jakarta_in_usd' => (double) $product->fob_jakarta_in_usd,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Produk berhasil ditemukan',
            'product' => $data,
        ]);
    }

    public function cartExport($buyerId)
    {
        // ================== DATA ==================
        $buyer = NewBuyer::where('buyer_id', $buyerId)->firstOrFail();
        $carts = Carts::where('buyer_id', $buyerId)->get();

        if ($carts->isEmpty()) {
            abort(404, 'Cart kosong');
        }

        // ================== LOAD TEMPLATE ==================
        $spreadsheet = IOFactory::load(storage_path('app/public/Book4.xlsx'));
        $sheet       = $spreadsheet->getActiveSheet();

        // ================== HEADER INFO ==================
        $sheet->setCellValue('C5', $buyer->order_no ?? '-');
        $sheet->setCellValue('C6', $buyer->company_name ?? '-');
        $sheet->setCellValue('C7', $buyer->country ?? '-');
        $sheet->setCellValue('C9', $buyer->packing ?? '-');
        $sheet->setCellValue('C10', $buyer->contact_person ?? '-');

        // ================== START ROW DETAIL ==================
        $row = 14;

        foreach ($carts as $index => $cart) {

            $articleCode = trim($cart->article_code);

            $product = ProductPameran::where('article_code', $articleCode)->first();

            // ================== IMAGE PATH (FIX) ==================
            $photoFullPath = null;

            if ($product && $product->exhibition_id) {
                $exhibition = Exhibition::find($product->exhibition_id);

                if ($exhibition) {
                    $relativePath = "pameran/{$exhibition->name}/{$articleCode}.webp";

                    if (Storage::disk('public')->exists($relativePath)) {
                        // ABSOLUTE PATH (WAJIB)
                        $photoFullPath = storage_path("app/public/{$relativePath}");
                    }
                }
            }

            // ================== DATA CELL ==================
            $sheet->fromArray([
                $index + 1,
                '', // kolom foto dikosongkan (gambar via Drawing)
                $product->name ?? '-',
                $articleCode,
                $cart->remark ?? '-',
                $product->cushion ?? '-',
                $product->glass ?? '-',
                $product->item_w ?? '-',
                $product->item_d ?? '-',
                $product->item_h ?? '-',
                $product->packing_w ?? '-',
                $product->packing_d ?? '-',
                $product->packing_h ?? '-',
                $product->composition ?? '-',
                $product->finishing ?? '-',
                $cart->qty ?? 0,
                $product->cbm ?? 0,
                $product->fob_jakarta_in_usd ?? 0,
                ($product->cbm ?? 0) * ($cart->qty ?? 0),
                $product->value_in_usd ?? 0,
            ], null, "A{$row}");

            // ================== STYLE ==================
            $sheet->getStyle("A{$row}:T{$row}")->applyFromArray([
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_HAIR,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ]);

            // ================== IMAGE DRAWING ==================
            if ($photoFullPath && file_exists($photoFullPath)) {

                $drawing = new Drawing();
                $drawing->setName('Product Image');
                $drawing->setDescription($articleCode);
                $drawing->setPath($photoFullPath); // ✅ ABSOLUTE PATH
                $drawing->setHeight(160);
                $drawing->setCoordinates("B{$row}");
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                $sheet->getRowDimension($row)->setRowHeight(145);
            } else {
                // Debug kalau gambar tidak ketemu
                logger()->warning("IMAGE NOT FOUND: {$articleCode}");
                $sheet->getRowDimension($row)->setRowHeight(30);
            }

            $row++;
        }

        // ================== COLUMN WIDTH ==================
        $sheet->getColumnDimension('B')->setWidth(34.5);

        // ================== DOWNLOAD ==================
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            "cart_export_{$buyerId}.xlsx",
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}
