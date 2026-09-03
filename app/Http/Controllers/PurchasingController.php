<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\TransaksiStok;

class PurchasingController extends Controller
{
    /**
     * Halaman Pengajuan Barang Inventory
     */
    public function index()
    {
        return view('pages.purchasing.index');
    }


    /**
     * Pencarian barang dari inventory/gudang
     */
    public function searchBarang(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if ($keyword === '') {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $stoks = Stok::query()
            ->where(function ($query) use ($keyword) {
                $query->where('kode_barang', 'like', '%' . $keyword . '%')
                    ->orWhere('nama_barang', 'like', '%' . $keyword . '%');
            })
            ->orderBy('nama_barang')
            ->limit(20)
            ->get();

        $data = $stoks->map(function ($stok) {

            $totalIn = $stok->transaksi()
                ->where('tipe', 'in')
                ->sum('qty');

            $totalOut = $stok->transaksi()
                ->where('tipe', 'out')
                ->sum('qty');

            $stokAkhir = (float) $stok->stok_awal
                + (float) $totalIn
                - (float) $totalOut;

            return [
                'id' => $stok->id,
                'kode_barang' => $stok->kode_barang,
                'nama_barang' => $stok->nama_barang,
                'jenis' => $stok->jenis,
                'satuan' => $stok->satuan,
                'harga' => (float) $stok->harga,
                'stok_awal' => (float) $stok->stok_awal,
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'stok_akhir' => $stokAkhir,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    /**
     * Detail satu barang.
     * Dipakai setelah user memilih barang dari hasil pencarian.
     */
    public function detailBarang($id)
    {
        $stok = Stok::findOrFail($id);

        $totalIn = $stok->transaksi()
            ->where('tipe', 'in')
            ->sum('qty');

        $totalOut = $stok->transaksi()
            ->where('tipe', 'out')
            ->sum('qty');

        $stokAkhir = (float) $stok->stok_awal
            + (float) $totalIn
            - (float) $totalOut;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stok->id,
                'kode_barang' => $stok->kode_barang,
                'nama_barang' => $stok->nama_barang,
                'jenis' => $stok->jenis,
                'satuan' => $stok->satuan,
                'harga' => (float) $stok->harga,
                'stok_awal' => (float) $stok->stok_awal,
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'stok_akhir' => $stokAkhir,
            ]
        ]);
    }
}