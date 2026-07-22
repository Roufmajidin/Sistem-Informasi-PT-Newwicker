<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Po;

class EdController extends Controller
{
    //
    public function index(){
        return view('pages.exports.index');
    }
   public function searchPo(Request $request)
    {
        $keyword = $request->keyword;

        $rows = Po::with('detailPos')
            ->where('order_no','like',"%{$keyword}%")
            ->orWhere('company_name','like',"%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($rows);
    }
    public function poItems($id)
    {
        $po = Po::with('detailPos')->findOrFail($id);

        $items = [];

        foreach ($po->detailPos as $detailPo) {

            $detail = $detailPo->detail;

            $items[] = [
                'id' => $detailPo->id,
                'article_nr' => $detail['article_nr_'] ?? '',
                'description' => $detail['description'] ?? '',
                'photo' => $detail['photo'] ?? '',
                'qty' => $detail['qty'] ?? 0,
                'cbm' => $detail['cbm'] ?? 0,
                'total_cbm' => $detail['total_cbm'] ?? 0,
                'pack_w' => $detail['pack_w'] ?? '',
                'pack_d' => $detail['pack_d'] ?? '',
                'pack_h' => $detail['pack_h'] ?? '',
                'value' => $detail['fob_jakarta_in_usd']
                    ?? $detail['fob_jakarta_price_in_usd_pc']
                    ?? 0,            ];
        }

        return response()->json($items);
    }
}
