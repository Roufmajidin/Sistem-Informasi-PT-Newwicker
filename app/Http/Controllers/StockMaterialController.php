<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMaterial;

class StockMaterialController extends Controller
{

    // =====================================
    // LOAD DATA
    // =====================================

    public function index()
    {

        return response()->json(

            StockMaterial::orderBy('id')
                ->get()

        );

    }




    // =====================================
    // SAVE SHEET
    // =====================================

    public function saveSheet(Request $request)
    {

        $items =
            $request->items ?? [];


        $usedIds = [];


        foreach ($items as $item) {

            // =============================
            // SKIP EMPTY
            // =============================

            if (

                empty($item['kode_barang'])
                &&
                empty($item['nama_barang'])

            ) {

                continue;

            }


            // =============================
            // VALUE
            // =============================

            $qty =
                (float) ($item['qty'] ?? 0);

            $harga =
                (float) ($item['harga_qty'] ?? 0);

            $in =
                (float) ($item['in_qty'] ?? 0);

            $out =
                (float) ($item['out_qty'] ?? 0);


            // =============================
            // DATA
            // =============================

            $data = [

                'nama_barang' =>
                    $item['nama_barang'] ?? '',

                'kode_barang' =>
                    $item['kode_barang'] ?? '',

                'satuan' =>
                    $item['satuan'] ?? '',

                'qty' =>
                    $qty,

                'harga_qty' =>
                    $harga,

                'jumlah' =>
                    $qty * $harga,

                'gudang' =>
                    $item['gudang'] ?? '',

                'in_qty' =>
                    $in,

                'out_qty' =>
                    $out,

                'sisa' =>
                    $in - $out,

                'no_po' =>
                    $item['no_po'] ?? '',

                'tanggal' =>
                    !empty($item['tanggal'])
                        ? $item['tanggal']
                        : null

            ];


            // =============================
            // UPDATE / CREATE
            // =============================

            $stock =
                StockMaterial::updateOrCreate(

                    [

                        'kode_barang' =>
                            $item['kode_barang']

                    ],

                    $data

                );


            $usedIds[] =
                $stock->id;

        }



        // =============================
        // DELETE YANG HILANG
        // =============================

        if (count($usedIds) > 0) {

            StockMaterial::whereNotIn(

                'id',

                $usedIds

            )->delete();

        }



        // =============================
        // RESPONSE
        // =============================

        return response()->json([

            'success' => true,

            'message' =>
                'Stock berhasil disimpan',

            'total' =>
                count($usedIds)

        ]);

    }

}
