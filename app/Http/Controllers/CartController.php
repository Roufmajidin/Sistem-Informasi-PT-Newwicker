<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carts;
use App\Models\NewBuyer;
use App\Models\ProductPameran;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // GET all cart items (optional filter buyer_id)
    public function index()
    {
        $buyers = NewBuyer::all();

        $result = $buyers->map(function ($buyer) {

            // ambil semua cart milik buyer ini
            $carts = Carts::where('buyer_id', $buyer->id)->get();

            // mapping cart + product_pameran
            $itemsWithProduct = $carts->map(function ($cart) {

                // hapus spasi di article_code (sangat penting)
                $articleCode = trim($cart->article_code);

                // cari product pameran berdasarkan article_code
                $product = ProductPameran::where('article_code', $articleCode)->first();

                return [
                    "id"             => $cart->id,
                    "article_code"   => $cart->article_code,
                    "buyer_id"       => $cart->buyer_id,
                    "status"         => $cart->status,
                    "remark"         => $cart->remark,
                    "created_at"     => $cart->created_at,
                    "updated_at"     => $cart->updated_at,

                    // tambahkan product detail
                    "product_detail" => $product,
                ];
            });

            return [
                "buyer"         => $buyer,
                "product_items" => $itemsWithProduct,
            ];
        });

        return response()->json([
            "buyers" => $result,
        ]);
    }

    // POST add to cart
  public function store(Request $request)
{
    $request->validate([
        "article_code" => "required|string",
        "buyer_id"     => "required|integer",
        "qty"          => "nullable|integer|min:1",
        "remark"       => "nullable|string",
    ]);

    $reqQty = $request->qty ?? 1; // jika tidak ada → default = 1

    // cek apakah item sudah ada
    $existing = Carts::where('buyer_id', $request->buyer_id)
        ->where('article_code', $request->article_code)
        ->first();

    if ($existing) {

        // simpan qty lama (opsional kalau ingin dipakai)
        $oldQty = $existing->qty;

        // update qty berdasarkan qty request
        $existing->qty = ($existing->qty ?? 1) + $reqQty;

        // update remark jika dikirim
        if ($request->filled('remark')) {
            $existing->remark = $request->remark;
        }

        $existing->save();

        // === LOGGING ===


        return response()->json([
            "message" => "Quantity updated",
            "data"    => $existing,
        ], 200);
    }

    // ITEM BELUM ADA → buat baru
    $cart = Carts::create([
        "article_code" => $request->article_code,
        "buyer_id"     => $request->buyer_id,
        "status"       => 1,
        "remark"       => $request->remark,
        "qty"          => $reqQty,
    ]);

    // === LOGGING ===


    return response()->json([
        "message" => "Item added to cart successfully",
        "data"    => $cart,
    ], 201);
}


    // GET cart by ID
    public function show($id)
    {
        return response()->json(Carts::findOrFail($id));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $cart = Carts::findOrFail($id);
        $cart->update($request->all());
        return response()->json($cart);
    }

    // DELETE
    public function destroy($id)
    {
        $cart = Carts::find($id);

        if (! $cart) {
            return response()->json([
                'message' => 'Cart item not found',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'message' => 'Cart item deleted successfully',
        ]);
    }
// simpan draft buyer beserta cart nya
    public function checkout(Request $request)
    {
        $request->validate([
            "cart_ids"     => "required|array",
            "company_name" => "required|string",
        ]);

        // 1. Generate buyer_id random
        $buyerId = rand(100000, 999999);

        // 2. Simpan data buyer
        $buyer = NewBuyer::create([
            // "id"       => $buyerId,
            "buyer_id"       => $buyerId,
            "order_no"       => $request->order_no,
            "company_name"   => $request->company_name,
            "country"        => $request->country,
            "shipment_date"  => $request->shipment_date,
            "packing"        => $request->packing,
            "contact_person" => $request->contact_person,
        ]);

        // 3. Update semua cart yang diceklis
        Carts::whereIn('id', $request->cart_ids)
            ->update([
                "buyer_id" => $buyerId,
                "status"   => "final",
            ]);

        return response()->json([
            "message" => "Checkout success",
            "buyer"   => $buyer,
        ]);
    }

}
