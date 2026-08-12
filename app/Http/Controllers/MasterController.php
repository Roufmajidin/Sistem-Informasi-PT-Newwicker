<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\MasterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    /**
     * =========================================================
     * MENAMPILKAN DETAIL PO
     * =========================================================
     *
     * Halaman ini digunakan untuk memilih item mana
     * yang akan dimasukkan ke master_items.
     */
    public function detailPo(Request $request)
    {
        $detailPo = DetailPo::with('po')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | AMBIL ARTICLE NR YANG SUDAH ADA DI MASTER
        |--------------------------------------------------------------------------
        */

        $masterArticles = MasterItem::whereNotNull('article_nr')
            ->pluck('article_nr')
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();


        return view(
            'pages.master.detail_po',
            compact(
                'detailPo',
                'masterArticles'
            )
        );
    }
public function showDetailPo(DetailPo $detailPo)
{
    $detailPo->load('po');

    return response()->json([
        'id' => $detailPo->id,
        'detail' => $detailPo->detail,
        'po' => $detailPo->po,
    ]);
}

    /**
     * =========================================================
     * SIMPAN DETAIL PO TERPILIH KE MASTER ITEMS
     * =========================================================
     */
    public function storeFromDetailPo(Request $request)
    {
        $request->validate([
            'detail_po_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'detail_po_ids.*' => [
                'integer',
                'exists:detail_po,id',
            ],
        ]);


        $inserted = 0;
        $skipped = 0;


        DB::transaction(function () use ($request, &$inserted, &$skipped) {

            $details = DetailPo::whereIn(
                'id',
                $request->detail_po_ids
            )->get();


            foreach ($details as $detailPo) {

                /*
                |--------------------------------------------------------------------------
                | AMBIL JSON DETAIL
                |--------------------------------------------------------------------------
                */

                $detail = $detailPo->detail;


                /*
                |--------------------------------------------------------------------------
                | VALIDASI DETAIL
                |--------------------------------------------------------------------------
                */

                if (!is_array($detail)) {

                    $skipped++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | AMBIL ARTICLE NR
                |--------------------------------------------------------------------------
                */

                $articleNr = trim(
                    (string) (
                        $detail['article_nr_']
                        ?? ''
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | ARTICLE NR WAJIB ADA
                |--------------------------------------------------------------------------
                */

                if ($articleNr === '') {

                    $skipped++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | CEK APAKAH SUDAH ADA DI MASTER
                |--------------------------------------------------------------------------
                |
                | Article NR menjadi identitas item sementara.
                |
                */

                $alreadyExists =
                    MasterItem::where(
                        'article_nr',
                        $articleNr
                    )->exists();


                if ($alreadyExists) {

                    $skipped++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | MASUKKAN KE MASTER ITEMS
                |--------------------------------------------------------------------------
                |
                | article_code sengaja NULL.
                |
                | Karena article_code merupakan kode internal
                | yang nanti dapat diisi dari halaman Master Item.
                |
                */

                MasterItem::create([

                    'article_code' => null,

                    'article_nr' =>
                        $articleNr,

                    'description' =>
                        $detail['description']
                        ?? null,

                    'sub_category' =>
                        $detail['sub_category']
                        ?? null,

                    'composition' =>
                        $detail['composition']
                        ?? null,

                    'finishing' =>
                        $detail['finishing']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | ITEM DIMENSION
                    |--------------------------------------------------------------------------
                    */

                    'item_d' =>
                        $detail['item_d']
                        ?? null,

                    'item_h' =>
                        $detail['item_h']
                        ?? null,

                    'item_w' =>
                        $detail['item_w']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | PACKING DIMENSION
                    |--------------------------------------------------------------------------
                    */

                    'pack_d' =>
                        $detail['pack_d']
                        ?? null,

                    'pack_h' =>
                        $detail['pack_h']
                        ?? null,

                    'pack_w' =>
                        $detail['pack_w']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | CBM
                    |--------------------------------------------------------------------------
                    */

                    'cbm' =>
                        $detail['cbm']
                        ?? null,

                    'total_cbm' =>
                        $detail['total_cbm']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | PRICE
                    |--------------------------------------------------------------------------
                    */

                    'value_in_usd' =>
                        $detail['value_in_usd']
                        ?? null,

                    'fob_jakarta_in_usd' =>
                        $detail['fob_jakarta_in_usd']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | LAIN-LAIN
                    |--------------------------------------------------------------------------
                    */

                    'photo' =>
                        $detail['photo']
                        ?? null,

                    'remark' =>
                        $detail['remark']
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | SUMBER DATA
                    |--------------------------------------------------------------------------
                    */

                    'source_detail_po_id' =>
                        $detailPo->id,
                ]);


                $inserted++;
            }

        });


        /*
        |--------------------------------------------------------------------------
        | HASIL
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->back()
            ->with(
                'success',
                "{$inserted} item berhasil dimasukkan ke Master Item. {$skipped} item dilewati."
            );
    }
}