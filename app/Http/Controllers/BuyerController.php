<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carts;
use App\Models\Exhibition;
use App\Models\NewBuyer;
use App\Models\ProductPameran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuyerController extends Controller
{
    // GET all buyers
    public function index()
    {
        return response()->json(NewBuyer::all());
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

                // detail product_pameran
                "product_detail" => $product,

                // foto
                "photo"          => $photoPath,
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
        $buyer = NewBuyer::findOrFail($id);
        $buyer->update($request->all());
        return response()->json($buyer);
    }

    // DELETE
    public function destroy($id)
    {
        NewBuyer::destroy($id);
        return response()->json(['message' => 'Buyer deleted']);
    }

}
