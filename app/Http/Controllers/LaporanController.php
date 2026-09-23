<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Spk;
use App\Models\Stok;
use App\Models\Pengajuan;
use App\Models\TransaksiStok;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\WarehouseHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MonitoringInvoice;
use App\Models\InvLama;
use App\Models\SpkLama;
class LaporanController extends Controller
{
     public function index(Request $request)
{
    $jenis = $request->jenis;
    $search = $request->search;

    /*
    |--------------------------------------------------------------------------
    | SUMMARY TRANSAKSI PER BARANG
    |--------------------------------------------------------------------------
    |
    | Semua transaksi dihitung:
    |
    | IN  = menambah stok
    | OUT = mengurangi stok
    |
    | Termasuk transaksi OPNAME.
    |
    | stok_awal TIDAK digunakan dalam perhitungan saldo.
    |
    */

    $transactionSummary = TransaksiStok::query()
        ->select(
            'stok_id',

            DB::raw("
                SUM(
                    CASE
                        WHEN tipe = 'in'
                        THEN qty
                        ELSE 0
                    END
                ) AS total_in
            "),

            DB::raw("
                SUM(
                    CASE
                        WHEN tipe = 'out'
                        THEN qty
                        ELSE 0
                    END
                ) AS total_out
            ")
        )
        ->groupBy('stok_id');


    /*
    |--------------------------------------------------------------------------
    | DATA STOK
    |--------------------------------------------------------------------------
    */

    $stoks = Stok::query()

        ->leftJoinSub(
            $transactionSummary,
            'transaction_summary',
            function ($join) {

                $join->on(
                    'stoks.id',
                    '=',
                    'transaction_summary.stok_id'
                );

            }
        )

        ->select(
            'stoks.*',

            DB::raw("
                COALESCE(
                    transaction_summary.total_in,
                    0
                ) AS total_in
            "),

            DB::raw("
                COALESCE(
                    transaction_summary.total_out,
                    0
                ) AS total_out
            ")
        )


        /*
        |--------------------------------------------------------------------------
        | FILTER JENIS
        |--------------------------------------------------------------------------
        */

        ->when($jenis, function ($q) use ($jenis) {

            $q->where(
                'stoks.jenis',
                $jenis
            );

        })


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        |
        | Cari berdasarkan:
        | - kode barang
        | - nama barang
        |
        */

        ->when($search, function ($q) use ($search) {

            $q->where(function ($query) use ($search) {

                $query

                    ->where(
                        'stoks.kode_barang',
                        'LIKE',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'stoks.nama_barang',
                        'LIKE',
                        '%' . $search . '%'
                    );

            });

        })


        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */

        ->orderBy(
            'stoks.nama_barang'
        )


        /*
        |--------------------------------------------------------------------------
        | EXECUTE QUERY
        |--------------------------------------------------------------------------
        */

        ->get()


        /*
        |--------------------------------------------------------------------------
        | HITUNG SALDO
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | stok_awal TIDAK DIGUNAKAN.
        |
        | Rumus:
        |
        | SALDO = TOTAL IN - TOTAL OUT
        |
        | Contoh:
        |
        | IN  = 45
        | OUT = 35
        |
        | SALDO = 45 - 35
        |       = 10
        |
        | Kalau ada:
        |
        | Opname IN  = 5
        | Opname OUT = 2
        |
        | maka otomatis:
        |
        | TOTAL IN  = IN normal + Opname IN
        | TOTAL OUT = OUT normal + Opname OUT
        |
        */

        ->map(function ($stok) {

            $totalIn = (float) ($stok->total_in ?? 0);

            $totalOut = (float) ($stok->total_out ?? 0);


            /*
            |--------------------------------------------------------------------------
            | SALDO AKHIR
            |--------------------------------------------------------------------------
            */

            $stok->saldo =
                $totalIn
                - $totalOut;


            return $stok;

        });


    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'pages.laporan.index',
        compact(
            'stoks',
            'search',
            'jenis'
        )
    );
}

    public function warehouseHistory(Request $request)
{
    $query = TransaksiStok::with(['stok', 'spk']);

    /*
    |--------------------------------------------------------------------------
    | EXCLUDE OPNAME
    |--------------------------------------------------------------------------
    | Semua transaksi yang keterangan-nya mengandung "Opname"
    | tidak akan ditampilkan dan tidak ikut perhitungan summary.
    |
    | LIKE di MySQL umumnya case-insensitive, sehingga:
    | - Opname
    | - OPNAME
    | - opname
    | - Opname Material
    | - Stock Opname
    | akan ikut dikecualikan.
    */
    $query->where(function ($q) {
        $q->whereNull('keterangan')
            ->orWhere('keterangan', 'not like', '%opname%');
    });


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('po', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%")
                ->orWhere('tipe', 'like', "%{$search}%")
                ->orWhereHas('stok', function ($qq) use ($search) {

                    $qq->where('nama_barang', 'like', "%{$search}%")
                        ->orWhere('kode_barang', 'like', "%{$search}%");

                });

        });
    }


    /*
    |--------------------------------------------------------------------------
    | DATE FROM
    |--------------------------------------------------------------------------
    */
    if ($request->filled('date_from')) {

        $query->whereDate(
            'tanggal',
            '>=',
            $request->date_from
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DATE TO
    |--------------------------------------------------------------------------
    */
    if ($request->filled('date_to')) {

        $query->whereDate(
            'tanggal',
            '<=',
            $request->date_to
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TYPE
    |--------------------------------------------------------------------------
    */
    if ($request->filled('type')) {

        $query->where(
            'tipe',
            $request->type
        );

    }


    /*
    |--------------------------------------------------------------------------
    | JENIS BAHAN
    |--------------------------------------------------------------------------
    */
    if ($request->filled('jenis')) {

        $query->whereHas('stok', function ($q) use ($request) {

            $q->where(
                'jenis',
                $request->jenis
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    |
    | Clone dari $query.
    | Karena $query sudah mengecualikan Opname,
    | summary juga otomatis tidak menghitung Opname.
    |
    */
    $summaryQuery = clone $query;

    $summary = $summaryQuery
        ->join(
            'stoks',
            'stoks.id',
            '=',
            'transaksi_stoks.stok_id'
        )
        ->selectRaw("
            SUM(transaksi_stoks.qty) as total_qty,
            SUM(
                transaksi_stoks.qty * stoks.harga
            ) as total_value,
            COUNT(*) as total_transaksi
        ")
        ->first();


    /*
    |--------------------------------------------------------------------------
    | HISTORY
    |--------------------------------------------------------------------------
    */
    $histories = $query
        ->latest('tanggal')
        ->paginate(25)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */
    if ($request->ajax()) {

        return view(
            'pages.laporan.partials.history_table',
            compact(
                'histories',
                'summary'
            )
        )->render();

    }


    /*
    |--------------------------------------------------------------------------
    | FULL PAGE
    |--------------------------------------------------------------------------
    */
    return view(
        'pages.laporan.history',
        compact(
            'histories',
            'summary'
        )
    );
}

    //     public function update(Request $request)
// {
//     $request->validate([
//         'id'          => 'nullable|integer',
//         'kode_barang' => 'required',
//         'nama_barang' => 'required',
//         'jenis'       => 'required',
//         'satuan'      => 'nullable',
//         'harga'       => 'nullable',
//         'stok_awal'   => 'required|numeric',
//     ]);

    //     DB::beginTransaction();

    //     try {

    //         /*
//         |--------------------------------------------------------------------------
//         | BARANG LAMA
//         |--------------------------------------------------------------------------
//         */

    //         if ($request->id) {

    //             $stok = Stok::findOrFail($request->id);

    //             /*
//             |--------------------------------------------------------------------------
//             | HITUNG SALDO ERP SEBELUM ADJUSTMENT
//             |--------------------------------------------------------------------------
//             */

    //             $totalIn = TransaksiStok::where('stok_id', $stok->id)
//                 ->where('tipe', 'in')
//                 ->sum('qty');

    //             $totalOut = TransaksiStok::where('stok_id', $stok->id)
//                 ->where('tipe', 'out')
//                 ->sum('qty');

    //             $saldoERP =
//                 (float) ($stok->stok_awal ?? 0)
//                 + (float) $totalIn
//                 - (float) $totalOut;


    //             /*
//             |--------------------------------------------------------------------------
//             | SALDO EXCEL YANG DIINPUT USER
//             |--------------------------------------------------------------------------
//             */

    //             $saldoExcel = (float) $request->stok_awal;


    //             /*
//             |--------------------------------------------------------------------------
//             | HITUNG SELISIH
//             |--------------------------------------------------------------------------
//             */

    //             $selisih = $saldoExcel - $saldoERP;


    //             /*
//             |--------------------------------------------------------------------------
//             | UPDATE INFORMASI BARANG
//             |--------------------------------------------------------------------------
//             |
//             | PENTING:
//             | stok_awal TIDAK DIUPDATE.
//             |
//             */

    //             $stok->update([
//                 'kode_barang' => $request->kode_barang,
//                 'nama_barang' => $request->nama_barang,
//                 'jenis'       => $request->jenis,
//                 'satuan'      => $request->satuan,
//                 'harga'       => str_replace('.', '', $request->harga ?? 0),
//             ]);


    //             /*
//             |--------------------------------------------------------------------------
//             | ADJUSTMENT
//             |--------------------------------------------------------------------------
//             */

    //             if (abs($selisih) >= 0.0001) {

    //                 if ($selisih > 0) {

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | SALDO EXCEL LEBIH BESAR
//                     |--------------------------------------------------------------------------
//                     |
//                     | ERP   42.30
//                     | Excel 67.00
//                     |
//                     | Adjustment +24.70
//                     |
//                     */

    //                     // TransaksiStok::create([
//                     //     'stok_id'    => $stok->id,
//                     //     'tanggal'    => now(),
//                     //     'tipe'       => 'in',
//                     //     'qty'        => round($selisih, 3),
//                     //     'keterangan' => 'Adjustment saldo berdasarkan Excel',
//                     // ]);

    //                 } else {

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | SALDO EXCEL LEBIH KECIL
//                     |--------------------------------------------------------------------------
//                     |
//                     | ERP   67
//                     | Excel 55
//                     |
//                     | Adjustment -12
//                     |
//                     */

    //                     // TransaksiStok::create([
//                     //     'stok_id'    => $stok->id,
//                     //     'tanggal'    => now(),
//                     //     'tipe'       => 'out',
//                     //     'qty'        => round(abs($selisih), 3),
//                     //     'keterangan' => 'Adjustment saldo berdasarkan Excel',
//                     // ]);
//                 }
//             }


    //         } else {

    //             /*
//             |--------------------------------------------------------------------------
//             | BARANG BARU
//             |--------------------------------------------------------------------------
//             |
//             | Kalau barang belum ada, angka yang dimasukkan
//             | menjadi stok_awal.
//             |
//             */

    //             $stok = Stok::create([
//                 'kode_barang' => $request->kode_barang,
//                 'nama_barang' => $request->nama_barang,
//                 'jenis'       => $request->jenis,
//                 'satuan'      => $request->satuan,
//                 'harga'       => str_replace('.', '', $request->harga ?? 0),
//                 'stok_awal'   => $request->stok_awal,
//             ]);
//         }


    //         DB::commit();


    //         return response()->json([
//             'success' => true,
//             'message' => 'Data berhasil disimpan.',
//             'id'      => $stok->id,
//         ]);


    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return response()->json([
//             'success' => false,
//             'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
//         ], 500);
//     }
// }

public function update(Request $request)
{
    $request->validate([
        'id'          => 'nullable|integer',
        'kode_barang' => 'nullable|string|max:255',
        'nama_barang' => 'nullable|string|max:255',
        'jenis'       => 'nullable|string|max:255',
        'satuan'      => 'nullable|string|max:50',
        'harga'       => 'nullable',
        'stok_awal'   => 'nullable|numeric',
    ]);

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI HARGA
        |--------------------------------------------------------------------------
        */

        $harga = $request->harga ?? 0;

        if (is_string($harga)) {
            $harga = str_replace('.', '', $harga);
            $harga = str_replace(',', '.', $harga);
        }

        $harga = is_numeric($harga)
            ? (float) $harga
            : 0;


        /*
        |--------------------------------------------------------------------------
        | BARANG LAMA
        |--------------------------------------------------------------------------
        */

        if ($request->filled('id')) {

            $stok = Stok::findOrFail($request->id);

            /*
            |--------------------------------------------------------------------------
            | UPDATE MASTER
            |--------------------------------------------------------------------------
            */

            $stok->kode_barang = $request->kode_barang;
            $stok->nama_barang = $request->nama_barang;
            $stok->jenis       = $request->jenis;
            $stok->satuan      = $request->satuan;
            $stok->harga       = $harga;

            $stok->save();


            /*
            |--------------------------------------------------------------------------
            | PENYESUAIAN SALDO / OPNAME
            |--------------------------------------------------------------------------
            */

            if ($request->has('stok_awal')) {

                // Saldo yang diketik user dianggap sebagai saldo target
                $saldoTarget = (float) $request->stok_awal;


                /*
                |--------------------------------------------------------------------------
                | HITUNG SALDO ERP
                |--------------------------------------------------------------------------
                */

                $totalIn = TransaksiStok::where('stok_id', $stok->id)
                    ->where('tipe', 'in')
                    ->sum('qty');

                $totalOut = TransaksiStok::where('stok_id', $stok->id)
                    ->where('tipe', 'out')
                    ->sum('qty');

                $saldoERP =
                    (float) $totalIn
                    - (float) $totalOut;


                /*
                |--------------------------------------------------------------------------
                | HITUNG SELISIH
                |--------------------------------------------------------------------------
                */

                $selisih = $saldoTarget - $saldoERP;


                /*
                |--------------------------------------------------------------------------
                | BUAT OPNAME JIKA ADA SELISIH
                |--------------------------------------------------------------------------
                */

                if (abs($selisih) >= 0.0001) {

                    if ($selisih > 0) {

                        // Target lebih besar → IN
                        TransaksiStok::create([
                            'stok_id'    => $stok->id,
                            'tanggal'    => now(),
                            'tipe'       => 'in',
                            'qty'        => round($selisih, 3),
                            'keterangan' => 'Opname',
                        ]);

                    } else {

                        // Target lebih kecil → OUT
                        TransaksiStok::create([
                            'stok_id'    => $stok->id,
                            'tanggal'    => now(),
                            'tipe'       => 'out',
                            'qty'        => round(abs($selisih), 3),
                            'keterangan' => 'Opname',
                        ]);
                    }
                }
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data stok berhasil diperbarui.',
                'id'      => $stok->id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | BARANG BARU
        |--------------------------------------------------------------------------
        */

        $stok = Stok::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'jenis'       => $request->jenis,
            'satuan'      => $request->satuan,
            'harga'       => $harga,
            'stok_awal'   => (float) ($request->stok_awal ?? 0),
        ]);


        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Barang baru berhasil ditambahkan.',
            'id'      => $stok->id,
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
        ], 500);
    }
}
    public function destroy($id)
    {
        $stok = Stok::findOrFail($id);

        $stok->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }

    public function detail($id)
    {
        $stok = Stok::findOrFail($id);

        $transaksi = TransaksiStok::where('stok_id', $id)
            ->orderBy('tanggal')
            ->get();

        // dd($transaksi);
        return response()->json([
            'stok' => $stok,
            'transaksi' => $transaksi,
        ]);
    }
public function detailBarang(Request $request, $id)
{
    $stok = Stok::findOrFail($id);

    /*
    |--------------------------------------------------------------------------
    | SEMUA TRANSAKSI
    |--------------------------------------------------------------------------
    | Digunakan khusus untuk menghitung SALDO SAAT INI.
    |
    | Jangan terkena filter tanggal.
    */

    $allTransaksi = TransaksiStok::where(
        'stok_id',
        $id
    )
        ->orderBy('tanggal', 'desc')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | SALDO SAAT INI
    |--------------------------------------------------------------------------
    |
    | Semua IN menambah.
    | Semua OUT mengurangi.
    | Termasuk OPNAME.
    |
    | stok_awal TIDAK digunakan.
    */

    $totalInAll = $allTransaksi
        ->where('tipe', 'in')
        ->sum('qty');

    $totalOutAll = $allTransaksi
        ->where('tipe', 'out')
        ->sum('qty');

    $stokTersedia =
        (float) $totalInAll
        - (float) $totalOutAll;


    /*
    |--------------------------------------------------------------------------
    | TRANSAKSI UNTUK TAMPILAN HISTORY
    |--------------------------------------------------------------------------
    |
    | Yang terkena filter tanggal hanya riwayatnya.
    */

    $transaksi = TransaksiStok::where(
        'stok_id',
        $id
    )

        ->when(
            $request->tanggal_awal,
            fn($q) => $q->whereDate(
                'tanggal',
                '>=',
                $request->tanggal_awal
            )
        )

        ->when(
            $request->tanggal_akhir,
            fn($q) => $q->whereDate(
                'tanggal',
                '<=',
                $request->tanggal_akhir
            )
        )

        ->orderBy('tanggal', 'desc')
        ->get();


    return view(
        'pages.laporan.detail',
        compact(
            'stok',
            'transaksi',
            'stokTersedia'
        )
    );
}
    // public function detailBarang(
    //     Request $request,
    //     $id
    // ) {
    //     $stok = Stok::findOrFail($id);

    //     $transaksi = TransaksiStok::where(
    //         'stok_id',
    //         $id
    //     )

    //         ->when(
    //             $request->tanggal_awal,
    //             fn($q) => $q->whereDate(
    //                 'tanggal',
    //                 '>=',
    //                 $request->tanggal_awal
    //             )
    //         )

    //         ->when(
    //             $request->tanggal_akhir,
    //             fn($q) => $q->whereDate(
    //                 'tanggal',
    //                 '<=',
    //                 $request->tanggal_akhir
    //             )
    //         )

    //         ->orderBy('tanggal', 'desc')

    //         ->get();

    //     return view(
    //         'pages.laporan.detail',
    //         compact(
    //             'stok',
    //             'transaksi'
    //         )
    //     );
    // }

    public function pdf(Request $request, $id)
    {
        $stok = Stok::findOrFail($id);

        $transaksi = TransaksiStok::where(
            'stok_id',
            $id
        )

            ->when(
                $request->tanggal_awal,
                fn($q) => $q->whereDate(
                    'tanggal',
                    '>=',
                    $request->tanggal_awal
                )
            )

            ->when(
                $request->tanggal_akhir,
                fn($q) => $q->whereDate(
                    'tanggal',
                    '<=',
                    $request->tanggal_akhir
                )
            )

            ->orderBy('tanggal')
            ->get();

        $totalIn = $transaksi
            ->where('tipe', 'in')
            ->sum('qty');

        $totalOut = $transaksi
            ->where('tipe', 'out')
            ->sum('qty');

        $pdf = Pdf::loadView(
            'pages.laporan.pdf',
            compact(
                'stok',
                'transaksi',
                'totalIn',
                'totalOut'
            )
        );

        return $pdf->stream(
            'laporan-stok-' . $stok->kode_barang . '.pdf'
        );
    }

    public function storeTransaksi(Request $request)
    {
        $request->validate([
            'stok_id' => 'required',
            'tanggal' => 'required',
        ]);

        if ($request->in > 0) {

            TransaksiStok::create([
                'stok_id' => $request->stok_id,
                'tanggal' => $request->tanggal,
                'tipe' => 'in',
                'qty' => $request->in,
                'po' => $request->po,
                'spk_id' => $request->spk_id,
                'keterangan' => $request->keterangan,
                'no_invoice' => $request->no_invoice,
            ]);
        }

        if ($request->out > 0) {

            TransaksiStok::create([
                'stok_id' => $request->stok_id,
                'tanggal' => $request->tanggal,
                'tipe' => 'out',
                'qty' => $request->out,
                'po' => $request->po,
                'spk_id' => $request->spk_id,
                'keterangan' => $request->keterangan,
                'no_invoice' => $request->no_invoice,
            ]);
        }

        // dd($request->out);
        return response()->json([
            'success' => true,
        ]);
    }

    // public function searchSpk(Request $request)
    // {
    //     $keyword = $request->q;
    //     // dd($request->all());
    //     $spks = Spk::where('data', 'like', '%' . $keyword . '%')
    //         ->latest()
    //         ->take(10)
    //         ->get();

    //     $result = [];

    //     foreach ($spks as $spk) {

    //         $data = $spk->data;

    //         $result[] = [
    //             'id' => $spk->id,
    //             'no_spk' => $data['no_spk'] ?? '',
    //             'supplier' => $data['sup'] ?? '',
    //             'items' => $data['items'] ?? [],
    //         ];
    //     }

    //     return response()->json($result);
    // }
public function searchSpk(Request $request)
{
    $keyword = $request->q;

    $spks = Spk::where('data', 'like', '%' . $keyword . '%')
        ->latest()
        ->take(10)
        ->get();

    $result = [];

    foreach ($spks as $spk) {

        $data = is_array($spk->data)
            ? $spk->data
            : (json_decode($spk->data, true) ?? []);

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ITEMS
        |--------------------------------------------------------------------------
        | Pastikan items selalu array.
        */
        $items = $data['items'] ?? [];

        if (is_string($items)) {
            $decodedItems = json_decode($items, true);

            $items = json_last_error() === JSON_ERROR_NONE
                ? $decodedItems
                : [];
        }

        if (!is_array($items)) {
            $items = [];
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ITEM
        |--------------------------------------------------------------------------
        */
        $items = array_values(array_filter(
            $items,
            function ($item) {
                return is_array($item);
            }
        ));

        $result[] = [
            'id'       => $spk->id,
            'no_spk'   => $data['no_spk'] ?? '',
            'supplier' => $data['sup'] ?? '',
            'items'    => $items,
        ];
    }

    return response()->json($result);
}
    public function searchBarang(Request $request)
    {
        $q = $request->q;

        $barang = Stok::where('nama_barang', 'like', $q . '%')
            ->orderBy('nama_barang')
            ->first();

        return response()->json($barang);
    }

    public function updatePo(Request $request, $id)
    {
        $request->validate([
            'po' => 'nullable|string|max:100',
        ]);

        $history = TransaksiStok::findOrFail($id);

        $history->update([
            'po' => $request->po,
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }
public function overview()
{
    /*
    |--------------------------------------------------------------------------
    | EXCEPTION OPNAME
    |--------------------------------------------------------------------------
    |
    | true  = transaksi opname ikut dihitung
    | false = transaksi opname TIDAK dihitung
    |
    */

    $opname = false;


    /*
    |--------------------------------------------------------------------------
    | ========================================================================
    | WAREHOUSE OVERVIEW
    | ========================================================================
    |--------------------------------------------------------------------------
    */


    $stocks = Stok::all();


    /*
    |--------------------------------------------------------------------------
    | STOCK TRANSACTION SUMMARY
    |--------------------------------------------------------------------------
    */

    $stockSummaryQuery = TransaksiStok::join(
        'stoks',
        'stoks.id',
        '=',
        'transaksi_stoks.stok_id'
    );


    /*
    |--------------------------------------------------------------------------
    | EXCLUDE OPNAME
    |--------------------------------------------------------------------------
    */

    if (!$opname) {

        $stockSummaryQuery->where(function ($query) {

            $query->whereNull('transaksi_stoks.keterangan')
                ->orWhere(
                    'transaksi_stoks.keterangan',
                    'not like',
                    '%opname%'
                );

        });

    }


    $stockSummary = $stockSummaryQuery

        ->select(
            'transaksi_stoks.stok_id',

            DB::raw("
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'in'
                        THEN transaksi_stoks.qty
                        ELSE 0
                    END
                ) AS total_in_qty
            "),

            DB::raw("
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'out'
                        THEN transaksi_stoks.qty
                        ELSE 0
                    END
                ) AS total_out_qty
            ")
        )

        ->groupBy('transaksi_stoks.stok_id')

        ->get()

        ->keyBy('stok_id');


    /*
    |--------------------------------------------------------------------------
    | KPI
    |--------------------------------------------------------------------------
    */

    $totalSku = $stocks->count();

    $totalInventoryValue = 0;

    $lowStockCount = 0;

    $emptyStockCount = 0;


    foreach ($stocks as $stok) {

        $summary = $stockSummary->get($stok->id);

        $stokAkhir =
            (float) ($stok->stok_awal ?? 0)
            + (float) ($summary->total_in_qty ?? 0)
            - (float) ($summary->total_out_qty ?? 0);


        $totalInventoryValue +=
            $stokAkhir * (float) ($stok->harga ?? 0);


        if ($stokAkhir <= 0) {

            $emptyStockCount++;

        } elseif ($stokAkhir <= 10) {

            $lowStockCount++;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INVENTORY ASSET PER CATEGORY
    |--------------------------------------------------------------------------
    */

    $categoriesData = [];


    foreach ($stocks->groupBy('jenis') as $jenis => $items) {

        $totalInventoryAsset = 0;

        $totalStock = 0;

        $totalIn = 0;

        $totalOut = 0;


        foreach ($items as $item) {

            $summary = $stockSummary->get($item->id);


            $totalInQty =
                (float) ($summary->total_in_qty ?? 0);


            $totalOutQty =
                (float) ($summary->total_out_qty ?? 0);


            $stokAkhir =
                (float) ($item->stok_awal ?? 0)
                + $totalInQty
                - $totalOutQty;


            $totalStock += $stokAkhir;


            $inventoryAsset =
                $stokAkhir * (float) ($item->harga ?? 0);


            $totalInventoryAsset += $inventoryAsset;


            $totalIn +=
                $totalInQty * (float) ($item->harga ?? 0);


            $totalOut +=
                $totalOutQty * (float) ($item->harga ?? 0);

        }


        $categoriesData[] = [

            'name' => $jenis,

            'item_count' =>
                $items->count(),

            'total_stock' =>
                $totalStock,

            'total_value' =>
                $totalInventoryAsset,

            'total_in' =>
                $totalIn,

            'total_out' =>
                $totalOut,

            'percentage' =>
                $totalInventoryValue > 0

                    ? round(
                        ($totalInventoryAsset / $totalInventoryValue)
                        * 100
                    )

                    : 0,

            'color' => match (
                strtolower(trim($jenis))
            ) {

                'bahan baku' =>
                    '#0d6efd',

                'bahan penolong' =>
                    '#198754',

                'bahan finishing' =>
                    '#fd7e14',

                default =>
                    '#6c757d'
            },

            'bg_soft' =>
                '#f8f9fa',

            'icon' =>
                'bi-box'

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | FAST MOVING
    |--------------------------------------------------------------------------
    */

    $topOutgoingQuery = TransaksiStok::query()

        ->select(
            'stok_id',

            DB::raw(
                'SUM(qty) as total_out_qty'
            ),

            DB::raw(
                'COUNT(*) as out_frequency'
            )
        )

        ->where('tipe', 'out');


    if (!$opname) {

        $topOutgoingQuery->where(function ($query) {

            $query->whereNull('keterangan')
                ->orWhere(
                    'keterangan',
                    'not like',
                    '%opname%'
                );

        });

    }


    $topOutgoingMaterials = $topOutgoingQuery

        ->groupBy('stok_id')

        ->orderByDesc('total_out_qty')

        ->with('stok')

        ->take(20)

        ->get()

        ->map(function ($row) {

            return (object) [

                'code' =>
                    $row->stok->kode_barang,

                'name' =>
                    $row->stok->nama_barang,

                'category' =>
                    $row->stok->jenis,

                'location' =>
                    '-',

                'unit' =>
                    $row->stok->satuan,

                'total_out_qty' =>
                    $row->total_out_qty,

                'total_out_value' =>
                    $row->total_out_qty
                    * $row->stok->harga,

                'out_frequency' =>
                    $row->out_frequency,

            ];

        });


    /*
    |--------------------------------------------------------------------------
    | STOCK MOVEMENT 6 MONTHS
    |--------------------------------------------------------------------------
    */

    $stockMovement = [];


    for ($i = 5; $i >= 0; $i--) {

        $date =
            Carbon::now()->subMonths($i);


        /*
        |--------------------------------------------------------------------------
        | IN
        |--------------------------------------------------------------------------
        */

        $stockInQuery = TransaksiStok::join(
            'stoks',
            'stoks.id',
            '=',
            'transaksi_stoks.stok_id'
        )

            ->where(
                'transaksi_stoks.tipe',
                'in'
            )

            ->whereYear(
                'transaksi_stoks.tanggal',
                $date->year
            )

            ->whereMonth(
                'transaksi_stoks.tanggal',
                $date->month
            );


        if (!$opname) {

            $stockInQuery->where(function ($query) {

                $query->whereNull(
                    'transaksi_stoks.keterangan'
                )

                ->orWhere(
                    'transaksi_stoks.keterangan',
                    'not like',
                    '%opname%'
                );

            });

        }


        $stockIn =
            $stockInQuery

                ->selectRaw(
                    'SUM(
                        transaksi_stoks.qty
                        * stoks.harga
                    ) as total'
                )

                ->value('total') ?? 0;


        /*
        |--------------------------------------------------------------------------
        | OUT
        |--------------------------------------------------------------------------
        */

        $stockOutQuery = TransaksiStok::join(
            'stoks',
            'stoks.id',
            '=',
            'transaksi_stoks.stok_id'
        )

            ->where(
                'transaksi_stoks.tipe',
                'out'
            )

            ->whereYear(
                'transaksi_stoks.tanggal',
                $date->year
            )

            ->whereMonth(
                'transaksi_stoks.tanggal',
                $date->month
            );


        if (!$opname) {

            $stockOutQuery->where(function ($query) {

                $query->whereNull(
                    'transaksi_stoks.keterangan'
                )

                ->orWhere(
                    'transaksi_stoks.keterangan',
                    'not like',
                    '%opname%'
                );

            });

        }


        $stockOut =
            $stockOutQuery

                ->selectRaw(
                    'SUM(
                        transaksi_stoks.qty
                        * stoks.harga
                    ) as total'
                )

                ->value('total') ?? 0;


        $stockMovement[] = [

            'month' =>
                $date->format('M'),

            'stock_in' =>
                round(
                    $stockIn / 1000000,
                    2
                ),

            'stock_out' =>
                round(
                    $stockOut / 1000000,
                    2
                ),

            'stock_in_text' =>
                'Rp ' .
                number_format(
                    $stockIn / 1000000,
                    2
                ) .
                ' Jt',

            'stock_out_text' =>
                'Rp ' .
                number_format(
                    $stockOut / 1000000,
                    2
                ) .
                ' Jt',

            'difference' =>
                round(
                    ($stockIn - $stockOut)
                    / 1000000,
                    2
                ),

            'status' =>
                $stockIn >= $stockOut
                    ? 'Surplus'
                    : 'Defisit',

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | SUMMARY BY UNIT
    |--------------------------------------------------------------------------
    */

    $unitSummaries = [];


    foreach (
        $stocks->groupBy('satuan')
        as $unit => $items
    ) {

        $qty = 0;

        $value = 0;


        foreach ($items as $item) {

            $summary =
                $stockSummary->get(
                    $item->id
                );


            $stokAkhir =
                (float) (
                    $item->stok_awal ?? 0
                )

                + (float) (
                    $summary->total_in_qty ?? 0
                )

                - (float) (
                    $summary->total_out_qty ?? 0
                );


            $qty += $stokAkhir;


            $value +=
                $stokAkhir
                * (float) (
                    $item->harga ?? 0
                );

        }


        $unitSummaries[] = [

            'unit' =>
                $unit,

            'total_items' =>
                $items->count(),

            'total_stock' =>
                $qty,

            'inventory_value' =>
                $value,

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | ========================================================================
    | FINISHING CONTROL
    | ========================================================================
    |--------------------------------------------------------------------------
    |
    | Sumber data:
    |
    | MonitoringInvoice
    | InvLama
    | SpkLama
    | Spk -> payments
    |
    | Konsep:
    |
    | Invoice        = DEBET
    | Pemotongan SPK = KREDIT
    | Sisa           = Invoice - Pemotongan
    |
    */


    /*
    |--------------------------------------------------------------------------
    | AMBIL INVOICE
    |--------------------------------------------------------------------------
    */

    $monitoringInvoices =
        MonitoringInvoice::query()

            ->orderBy('tanggal_invoice')

            ->orderBy('id')

            ->get();


    $invLamas =
        InvLama::query()

            ->orderBy('tanggal_invoice')

            ->orderBy('id')

            ->get();


    /*
    |--------------------------------------------------------------------------
    | AMBIL SPK LAMA
    |--------------------------------------------------------------------------
    */

    $spkLamas =
        SpkLama::query()

            ->orderBy('tanggal_potong')

            ->orderBy('id')

            ->get();


    /*
    |--------------------------------------------------------------------------
    | AMBIL SPK BARU
    |--------------------------------------------------------------------------
    */

    $spks =
        Spk::query()

            ->get();


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE INVOICE
    |--------------------------------------------------------------------------
    */

    $normalizeInvoice = function ($value) {

        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                (string) $value
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE SUB
    |--------------------------------------------------------------------------
    */

    $normalizeSub = function ($value) {

        $value =
            strtoupper(
                trim(
                    (string) $value
                )
            );


        if (
            str_contains(
                $value,
                'TOMO'
            )
        ) {
            return 'TOMO';
        }


        if (
            str_contains(
                $value,
                'DARTO'
            )
        ) {
            return 'DARTO';
        }


        if (
            str_contains(
                $value,
                'PRODUKSI'
            )
        ) {
            return 'PRODUKSI';
        }


        if (
            str_contains(
                $value,
                'SAMPEL'
            )
        ) {
            return 'SAMPEL';
        }


        return $value;

    };


    /*
    |--------------------------------------------------------------------------
    | MAP INVOICE
    |--------------------------------------------------------------------------
    */

    $allFinishingInvoices = collect();


    /*
    |--------------------------------------------------------------------------
    | MONITORING INVOICE
    |--------------------------------------------------------------------------
    */

    foreach (
        $monitoringInvoices
        as $invoice
    ) {

        $key =
            $normalizeInvoice(
                $invoice->nomor_invoice
            );


        if ($key === '') {
            continue;
        }


        $allFinishingInvoices->push([
            'key' =>
                $key,

            'model' =>
                $invoice,

            'source' =>
                'monitoring_invoice',
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | INVOICE LAMA
    |--------------------------------------------------------------------------
    |
    | Hanya tambahkan jika belum ada
    | pada MonitoringInvoice.
    |
    */

    $monitoringInvoiceKeys =
        $monitoringInvoices

            ->map(function ($invoice) use (
                $normalizeInvoice
            ) {

                return $normalizeInvoice(
                    $invoice->nomor_invoice
                );

            })

            ->filter()

            ->flip();


    foreach (
        $invLamas
        as $invoice
    ) {

        $key =
            $normalizeInvoice(
                $invoice->nomor_invoice
            );


        if ($key === '') {
            continue;
        }


        if (
            isset(
                $monitoringInvoiceKeys[$key]
            )
        ) {
            continue;
        }


        $allFinishingInvoices->push([
            'key' =>
                $key,

            'model' =>
                $invoice,

            'source' =>
                'inv_lama',
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | HELPER HITUNG DETAIL INVOICE
    |--------------------------------------------------------------------------
    */

    $calculateInvoiceTotal =
        function ($details) {

            if (
                !is_array($details)
            ) {
                return 0;
            }


            $total = 0;


            foreach (
                $details
                as $detail
            ) {

                if (
                    !is_array($detail)
                ) {
                    continue;
                }


                /*
                | Cari field total terlebih dahulu
                */

                $lineTotal = 0;


                foreach (
                    [
                        'total',
                        'subtotal',
                        'jumlah',
                        'amount'
                    ]
                    as $field
                ) {

                    if (
                        isset(
                            $detail[$field]
                        )
                        &&
                        is_numeric(
                            $detail[$field]
                        )
                    ) {

                        $lineTotal =
                            (float)
                            $detail[$field];

                        break;

                    }

                }


                /*
                | Kalau total tidak ada,
                | hitung qty x harga.
                */

                if (
                    $lineTotal <= 0
                ) {

                    $qty = 0;

                    $harga = 0;


                    foreach (
                        [
                            'qty',
                            'quantity',
                            'jumlah_qty'
                        ]
                        as $field
                    ) {

                        if (
                            isset(
                                $detail[$field]
                            )
                            &&
                            is_numeric(
                                $detail[$field]
                            )
                        ) {

                            $qty =
                                (float)
                                $detail[$field];

                            break;

                        }

                    }


                    foreach (
                        [
                            'harga',
                            'price',
                            'harga_satuan'
                        ]
                        as $field
                    ) {

                        if (
                            isset(
                                $detail[$field]
                            )
                            &&
                            is_numeric(
                                $detail[$field]
                            )
                        ) {

                            $harga =
                                (float)
                                $detail[$field];

                            break;

                        }

                    }


                    $lineTotal =
                        $qty * $harga;

                }


                $total += $lineTotal;

            }


            return $total;

        };


    /*
    |--------------------------------------------------------------------------
    | SUMMARY STRUCTURE
    |--------------------------------------------------------------------------
    */

    $finishingSummary = [

        'TOMO' => [
            'invoice_count' => 0,
            'invoice_total' => 0,
            'cut_total' => 0,
            'remaining' => 0,
            'completed' => 0,
            'partial' => 0,
            'pending' => 0,
            'invoices' => [],
        ],

        'DARTO' => [
            'invoice_count' => 0,
            'invoice_total' => 0,
            'cut_total' => 0,
            'remaining' => 0,
            'completed' => 0,
            'partial' => 0,
            'pending' => 0,
            'invoices' => [],
        ],

        'PRODUKSI' => [
            'invoice_count' => 0,
            'invoice_total' => 0,
            'cut_total' => 0,
            'remaining' => 0,
            'completed' => 0,
            'partial' => 0,
            'pending' => 0,
            'invoices' => [],
        ],

        'SAMPEL' => [
            'invoice_count' => 0,
            'invoice_total' => 0,
            'cut_total' => 0,
            'remaining' => 0,
            'completed' => 0,
            'partial' => 0,
            'pending' => 0,
            'invoices' => [],
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | PENDING GLOBAL
    |--------------------------------------------------------------------------
    */

    $finishingPending = [];


    /*
    |--------------------------------------------------------------------------
    | RUNNING BALANCE
    |--------------------------------------------------------------------------
    */

    $finishingRunningBalance = [];

    $runningFinishingBalance = 0;


    /*
    |--------------------------------------------------------------------------
    | PROSES SETIAP INVOICE
    |--------------------------------------------------------------------------
    */

    foreach (
        $allFinishingInvoices
        as $invoiceItem
    ) {

        $invoice =
            $invoiceItem['model'];


        $invoiceKey =
            $invoiceItem['key'];


        $invoiceNumber =
            trim(
                $invoice->nomor_invoice
                ?? ''
            );


        if (
            $invoiceNumber === ''
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | DETAIL BAHAN
        |--------------------------------------------------------------------------
        */

        $detailBahan =
            $invoice->detail_bahan
            ?? [];


        if (
            !is_array($detailBahan)
        ) {
            $detailBahan = [];
        }


        /*
        |--------------------------------------------------------------------------
        | NILAI INVOICE
        |--------------------------------------------------------------------------
        */

        $invoiceTotal =
            $calculateInvoiceTotal(
                $detailBahan
            );


        /*
        |--------------------------------------------------------------------------
        | TENTUKAN SUB
        |--------------------------------------------------------------------------
        */

        $invoiceToSub =
            trim(
                (string) (
                    $invoice->to_sub
                    ?? ''
                )
            );


        $sub = '';


        if (
            $invoiceToSub !== ''
        ) {

            $sub =
                $normalizeSub(
                    $invoiceToSub
                );

        }


        /*
        |--------------------------------------------------------------------------
        | FALLBACK KATEGORI
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $sub,
                [
                    'TOMO',
                    'DARTO',
                    'PRODUKSI',
                    'SAMPEL'
                ],
                true
            )
        ) {

            foreach (
                [
                    'kategori',
                    'kategori_invoice',
                    'jenis_invoice',
                    'tipe_invoice',
                    'category',
                    'type_invoice'
                ]
                as $field
            ) {

                if (
                    isset(
                        $invoice->{$field}
                    )
                    &&
                    trim(
                        (string)
                        $invoice->{$field}
                    ) !== ''
                ) {

                    $candidate =
                        $normalizeSub(
                            $invoice->{$field}
                        );


                    if (
                        in_array(
                            $candidate,
                            [
                                'TOMO',
                                'DARTO',
                                'PRODUKSI',
                                'SAMPEL'
                            ],
                            true
                        )
                    ) {

                        $sub =
                            $candidate;

                        break;

                    }

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Kalau belum bisa dikategorikan,
        | jangan dipaksa masuk ke chart.
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $sub,
                [
                    'TOMO',
                    'DARTO',
                    'PRODUKSI',
                    'SAMPEL'
                ],
                true
            )
        ) {

            $sub = 'PRODUKSI';

        }


        /*
        |--------------------------------------------------------------------------
        | HITUNG PEMOTONGAN
        |--------------------------------------------------------------------------
        */

        $cutTotal = 0;

        $cutDetails = [];


        /*
        |--------------------------------------------------------------------------
        | SPK LAMA
        |--------------------------------------------------------------------------
        */

        foreach (
            $spkLamas
            as $spkLama
        ) {

            $spkInvoiceKey =
                $normalizeInvoice(
                    $spkLama->no_inv
                );


            if (
                $spkInvoiceKey === ''
                ||
                $spkInvoiceKey !== $invoiceKey
            ) {
                continue;
            }


            $amount =
                (float) (
                    $spkLama->pemotongan_bahan
                    ?? 0
                );


            if (
                $amount <= 0
            ) {
                continue;
            }


            $cutTotal +=
                $amount;


            $cutDetails[] = [

                'source' =>
                    'spk_lama',

                'spk_id' =>
                    $spkLama->id,

                'no_spk' =>
                    $spkLama->no_spk,

                'po' =>
                    $spkLama->po,

                'tanggal' =>
                    $spkLama->tanggal_potong,

                'amount' =>
                    $amount,

            ];

        }


        /*
        |--------------------------------------------------------------------------
        | SPK BARU
        |--------------------------------------------------------------------------
        */

        foreach (
            $spks
            as $spk
        ) {

            $data =
                $spk->data;


            if (
                !is_array($data)
            ) {
                continue;
            }


            $supplier =
                $data['sup']
                ?? optional(
                    $spk->supplier
                )->name
                ?? null;


            $supplierNormalized =
                $normalizeSub(
                    $supplier
                );


            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            $payments =
                $data['payments']
                ?? [];


            if (
                !is_array($payments)
            ) {
                continue;
            }


            foreach (
                $payments
                as $paymentIndex => $payment
            ) {

                if (
                    !is_array($payment)
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | HANYA PAYMENT BAHAN
                |--------------------------------------------------------------------------
                */

                $note =
                    strtolower(
                        trim(
                            $payment['note']
                            ?? ''
                        )
                    );


                if (
                    $note !== 'bahan'
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | NOTE TAMBAHAN
                |--------------------------------------------------------------------------
                */

                $noteTambahan =
                    trim(
                        $payment['note_tambahan']
                        ?? ''
                    );


                if (
                    $noteTambahan === ''
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | MATCH INVOICE
                |--------------------------------------------------------------------------
                */

                $noteInvoiceNormalized =
                    $normalizeInvoice(
                        $noteTambahan
                    );


                if (
                    $invoiceKey === ''
                    ||
                    $noteInvoiceNormalized === ''
                    ||
                    !str_contains(
                        $noteInvoiceNormalized,
                        $invoiceKey
                    )
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | AMOUNT
                |--------------------------------------------------------------------------
                */

                $amount =
                    (float) (
                        $payment['amount']
                        ?? 0
                    );


                if (
                    $amount <= 0
                ) {
                    continue;
                }


                $paymentDate = null;


                if (
                    !empty(
                        $payment['date']
                    )
                ) {

                    try {

                        $paymentDate =
                            Carbon::parse(
                                $payment['date']
                            );

                    } catch (
                        \Throwable $e
                    ) {

                        $paymentDate =
                            $invoice->tanggal_invoice;

                    }

                }


                $noSpk =
                    $data['no_spk']
                    ?? $spk->no_spk
                    ?? null;


                $cutTotal +=
                    $amount;


                $cutDetails[] = [

                    'source' =>
                        'spk',

                    'spk_id' =>
                        $spk->id,

                    'no_spk' =>
                        $noSpk,

                    'po' =>
                        $data['no_po']
                        ?? null,

                    'tanggal' =>
                        $paymentDate,

                    'amount' =>
                        $amount,

                ];

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SISA
        |--------------------------------------------------------------------------
        */

        $remaining =
            max(
                0,
                $invoiceTotal
                - $cutTotal
            );


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $cutTotal <= 0
        ) {

            $status =
                'pending';

        } elseif (
            $remaining <= 0
        ) {

            $status =
                'completed';

        } else {

            $status =
                'partial';

        }


        /*
        |--------------------------------------------------------------------------
        | PERSENTASE
        |--------------------------------------------------------------------------
        */

        $percentage =
            $invoiceTotal > 0

                ? min(
                    100,
                    round(
                        (
                            $cutTotal
                            / $invoiceTotal
                        )
                        * 100
                    )
                )

                : 0;


        /*
        |--------------------------------------------------------------------------
        | UPDATE SUMMARY
        |--------------------------------------------------------------------------
        */

        $finishingSummary[$sub]
            ['invoice_count']++;


        $finishingSummary[$sub]
            ['invoice_total']
            += $invoiceTotal;


        $finishingSummary[$sub]
            ['cut_total']
            += $cutTotal;


        $finishingSummary[$sub]
            ['remaining']
            += $remaining;


        switch ($status) {

            case 'completed':

                $finishingSummary[$sub]
                    ['completed']++;

                break;


            case 'partial':

                $finishingSummary[$sub]
                    ['partial']++;

                break;


            case 'pending':

                $finishingSummary[$sub]
                    ['pending']++;

                break;

        }


        /*
        |--------------------------------------------------------------------------
        | DETAIL INVOICE
        |--------------------------------------------------------------------------
        */

        $invoiceData = [

            'invoice' =>
                $invoiceNumber,

            'tanggal' =>
                $invoice->tanggal_invoice,

            'sub' =>
                $sub,

            'invoice_total' =>
                $invoiceTotal,

            'cut_total' =>
                $cutTotal,

            'remaining' =>
                $remaining,

            'percentage' =>
                $percentage,

            'status' =>
                $status,

            'cut_details' =>
                $cutDetails,

        ];


        $finishingSummary[$sub]
            ['invoices'][] =
                $invoiceData;


        /*
        |--------------------------------------------------------------------------
        | PENDING / PARTIAL
        |--------------------------------------------------------------------------
        |
        | Yang belum selesai ditaruh ke daftar pekerjaan.
        |
        */

        if (
            $status !== 'completed'
        ) {

            $finishingPending[] = [

                'invoice' =>
                    $invoiceNumber,

                'tanggal' =>
                    $invoice->tanggal_invoice,

                'sub' =>
                    $sub,

                'invoice_total' =>
                    $invoiceTotal,

                'cut_total' =>
                    $cutTotal,

                'remaining' =>
                    $remaining,

                'percentage' =>
                    $percentage,

                'status' =>
                    $status,

            ];

        }

    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL FINISHING GLOBAL
    |--------------------------------------------------------------------------
    */

    $finishingTotalInvoice = 0;

    $finishingTotalCut = 0;

    $finishingTotalRemaining = 0;

    $finishingTotalInvoices = 0;

    $finishingCompleted = 0;

    $finishingPartial = 0;

    $finishingPendingCount = 0;


    foreach (
        $finishingSummary
        as $summary
    ) {

        $finishingTotalInvoice +=
            $summary['invoice_total'];

        $finishingTotalCut +=
            $summary['cut_total'];

        $finishingTotalRemaining +=
            $summary['remaining'];

        $finishingTotalInvoices +=
            $summary['invoice_count'];

        $finishingCompleted +=
            $summary['completed'];

        $finishingPartial +=
            $summary['partial'];

        $finishingPendingCount +=
            $summary['pending'];

    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL COMPLETION
    |--------------------------------------------------------------------------
    */

    $finishingCompletion =
        $finishingTotalInvoice > 0

            ? round(
                (
                    $finishingTotalCut
                    / $finishingTotalInvoice
                ) * 100
            )

            : 0;


    /*
    |--------------------------------------------------------------------------
    | CHART 1
    |--------------------------------------------------------------------------
    |
    | Invoice vs Pemotongan vs Sisa
    |
    */

    $finishingChart = [];


    foreach (
        [
            'TOMO',
            'DARTO',
            'PRODUKSI',
            'SAMPEL'
        ]
        as $sub
    ) {

        $summary =
            $finishingSummary[$sub];


        $finishingChart[] = [

            'sub' =>
                $sub,

            'invoice_total' =>
                round(
                    $summary['invoice_total']
                    / 1000000,
                    2
                ),

            'cut_total' =>
                round(
                    $summary['cut_total']
                    / 1000000,
                    2
                ),

            'remaining' =>
                round(
                    $summary['remaining']
                    / 1000000,
                    2
                ),

            'invoice_total_text' =>
                'Rp ' .
                number_format(
                    $summary['invoice_total'],
                    0,
                    ',',
                    '.'
                ),

            'cut_total_text' =>
                'Rp ' .
                number_format(
                    $summary['cut_total'],
                    0,
                    ',',
                    '.'
                ),

            'remaining_text' =>
                'Rp ' .
                number_format(
                    $summary['remaining'],
                    0,
                    ',',
                    '.'
                ),

            'percentage' =>
                $summary['invoice_total'] > 0

                    ? round(
                        (
                            $summary['cut_total']
                            /
                            $summary['invoice_total']
                        )
                        * 100
                    )

                    : 0,

            'invoice_count' =>
                $summary['invoice_count'],

            'completed' =>
                $summary['completed'],

            'partial' =>
                $summary['partial'],

            'pending' =>
                $summary['pending'],

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | CHART 2
    |--------------------------------------------------------------------------
    |
    | STATUS INVOICE
    |
    */

    $finishingStatusChart = [

        'labels' => [
            'Selesai',
            'Sebagian',
            'Belum Dipotong',
        ],

        'series' => [

            $finishingCompleted,

            $finishingPartial,

            $finishingPendingCount,

        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | CHART 3
    |--------------------------------------------------------------------------
    |
    | PER SUB
    |
    */

    $finishingSubChart = [

        'labels' => [
            'TOMO',
            'DARTO',
            'PRODUKSI',
            'SAMPEL',
        ],

        'invoice' => [],

        'cut' => [],

        'remaining' => [],

    ];


    foreach (
        [
            'TOMO',
            'DARTO',
            'PRODUKSI',
            'SAMPEL'
        ]
        as $sub
    ) {

        $summary =
            $finishingSummary[$sub];


        $finishingSubChart['invoice'][] =
            round(
                $summary['invoice_total']
                / 1000000,
                2
            );


        $finishingSubChart['cut'][] =
            round(
                $summary['cut_total']
                / 1000000,
                2
            );


        $finishingSubChart['remaining'][] =
            round(
                $summary['remaining']
                / 1000000,
                2
            );

    }


    /*
    |--------------------------------------------------------------------------
    | CHART 4
    |--------------------------------------------------------------------------
    |
    | RUNNING BALANCE FINISHING
    |--------------------------------------------------------------------------
    */

    $sortedFinishing =
        $allFinishingInvoices

            ->sortBy(function ($item) {

                $date =
                    $item['model']
                        ->tanggal_invoice;

                if (!$date) {
                    return PHP_INT_MAX;
                }

                try {

                    return Carbon::parse(
                        $date
                    )->timestamp;

                } catch (
                    \Throwable $e
                ) {

                    return PHP_INT_MAX;

                }

            })

            ->values();


    foreach (
        $sortedFinishing
        as $invoiceItem
    ) {

        $invoice =
            $invoiceItem['model'];


        $invoiceNumber =
            trim(
                $invoice->nomor_invoice
                ?? ''
            );


        if (
            $invoiceNumber === ''
        ) {
            continue;
        }


        $detail =
            $invoice->detail_bahan
            ?? [];


        if (
            !is_array($detail)
        ) {
            $detail = [];
        }


        $invoiceTotal =
            $calculateInvoiceTotal(
                $detail
            );


        /*
        |--------------------------------------------------------------------------
        | Cari total potongan berdasarkan invoice
        |--------------------------------------------------------------------------
        */

        $invoiceKey =
            $normalizeInvoice(
                $invoiceNumber
            );


        $cutTotal = 0;


        foreach (
            $spkLamas
            as $spkLama
        ) {

            if (
                $normalizeInvoice(
                    $spkLama->no_inv
                )
                !==
                $invoiceKey
            ) {
                continue;
            }


            $cutTotal +=
                (float) (
                    $spkLama->pemotongan_bahan
                    ?? 0
                );

        }


        foreach (
            $spks
            as $spk
        ) {

            $data =
                $spk->data;


            if (
                !is_array($data)
            ) {
                continue;
            }


            $payments =
                $data['payments']
                ?? [];


            if (
                !is_array($payments)
            ) {
                continue;
            }


            foreach (
                $payments
                as $payment
            ) {

                if (
                    !is_array($payment)
                ) {
                    continue;
                }


                if (
                    strtolower(
                        trim(
                            $payment['note']
                            ?? ''
                        )
                    )
                    !==
                    'bahan'
                ) {
                    continue;
                }


                $note =
                    $normalizeInvoice(
                        $payment['note_tambahan']
                        ?? ''
                    );


                if (
                    $invoiceKey === ''
                    ||
                    !str_contains(
                        $note,
                        $invoiceKey
                    )
                ) {
                    continue;
                }


                $cutTotal +=
                    (float) (
                        $payment['amount']
                        ?? 0
                    );

            }

        }


        $runningFinishingBalance +=
            $invoiceTotal
            - $cutTotal;


        $finishingRunningBalance[] = [

            'invoice' =>
                $invoiceNumber,

            'tanggal' =>
                $invoice->tanggal_invoice,

            'invoice_total' =>
                $invoiceTotal,

            'cut_total' =>
                $cutTotal,

            'remaining' =>
                max(
                    0,
                    $invoiceTotal
                    - $cutTotal
                ),

            'running_balance' =>
                $runningFinishingBalance,

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | SORT PENDING
    |--------------------------------------------------------------------------
    |
    | Prioritas:
    | 1. Belum dipotong
    | 2. Sebagian
    |
    */

    usort(
        $finishingPending,
        function ($a, $b) {

            if (
                $a['status']
                ===
                $b['status']
            ) {
                return 0;
            }


            if (
                $a['status']
                ===
                'pending'
            ) {
                return -1;
            }


            return 1;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'pages.laporan.overview',

        compact(

            /*
            |--------------------------------------------------------------------------
            | WAREHOUSE
            |--------------------------------------------------------------------------
            */

            'totalSku',

            'totalInventoryValue',

            'lowStockCount',

            'emptyStockCount',

            'categoriesData',

            'topOutgoingMaterials',

            'stockMovement',

            'unitSummaries',


            /*
            |--------------------------------------------------------------------------
            | FINISHING
            |--------------------------------------------------------------------------
            */

            'finishingSummary',

            'finishingChart',

            'finishingStatusChart',

            'finishingSubChart',

            'finishingPending',

            'finishingTotalInvoice',

            'finishingTotalCut',

            'finishingTotalRemaining',

            'finishingTotalInvoices',

            'finishingCompleted',

            'finishingPartial',

            'finishingPendingCount',

            'finishingCompletion',

            'finishingRunningBalance'

        )
    );
}
//     public function overview()
//     {
//         /*
//         |--------------------------------------------------------------------------
//         | KPI
//         |--------------------------------------------------------------------------
//         */

//         // $stocks = Stok::with('transaksi')->get();
//         $stocks = Stok::all();
//         $stockSummary = TransaksiStok::join(
//             'stoks',
//             'stoks.id',
//             '=',
//             'transaksi_stoks.stok_id'
//         )
//             ->select(
//                 'stok_id',

//                 DB::raw("
//             SUM(CASE
//                 WHEN tipe='in'
//                 THEN qty
//                 ELSE 0
//             END) AS total_in_qty
//         "),

//                 DB::raw("
//             SUM(CASE
//                 WHEN tipe='out'
//                 THEN qty
//                 ELSE 0
//             END) AS total_out_qty
//         ")
//             )
//             ->groupBy('stok_id')
//             ->get()
//             ->keyBy('stok_id');
//         $totalSku = $stocks->count();

//         $totalInventoryValue = 0;

//         $lowStockCount = 0;

//         $emptyStockCount = 0;

//         foreach ($stocks as $stok) {

//             $summary = $stockSummary->get($stok->id);

//             $stokAkhir =
//                 $stok->stok_awal
//                 + ($summary->total_in_qty ?? 0)
//                 - ($summary->total_out_qty ?? 0);

//             $totalInventoryValue += $stokAkhir * $stok->harga;

//             if ($stokAkhir <= 0) {
//                 $emptyStockCount++;
//             } elseif ($stokAkhir <= 10) {
//                 $lowStockCount++;
//             }
//         }

//         /*
// |--------------------------------------------------------------------------
// | Inventory Asset Per Category
// |--------------------------------------------------------------------------
// */

//         $categoriesData = [];

//         foreach ($stocks->groupBy('jenis') as $jenis => $items) {

//             $totalInventoryAsset = 0;

//             $totalStock = 0;
//             $totalIn = 0;

//             $totalOut = 0;
//             foreach ($items as $item) {

//                 $summary = $stockSummary->get($item->id);

//                 $stokAkhir =
//                     $item->stok_awal
//                     + ($summary->total_in_qty ?? 0)
//                     - ($summary->total_out_qty ?? 0);

//                 /*
//                 |-----------------------------------------
//                 | Current Stock
//                 |-----------------------------------------
//                 */

//                 $totalStock += $stokAkhir;

//                 /*
//                 |-----------------------------------------
//                 | Inventory Asset
//                 |-----------------------------------------
//                 */

//                 $inventoryAsset =
//                     $stokAkhir * $item->harga;

//                 $totalInventoryAsset += $inventoryAsset;
//                 $totalIn += ($summary->total_in_qty ?? 0) * $item->harga;

//                 $totalOut += ($summary->total_out_qty ?? 0) * $item->harga;

//             }

//             $categoriesData[] = [

//                 'name' => $jenis,

//                 'item_count' => $items->count(),

//                 'total_stock' => $totalStock,

//                 'total_value' => $totalInventoryAsset,
//                 'total_in' => $totalIn,

//                 'total_out' => $totalOut,
//                 // 'total' => $totalIn + $totalOut,
//                 'percentage' =>

//                     $totalInventoryValue > 0

//                     ? round(
//                         ($totalInventoryAsset / $totalInventoryValue) * 100
//                     )

//                     : 0,

//                 'color' => match (strtolower($jenis)) {

//                     'bahan baku' => '#0d6efd',

//                     'bahan penolong' => '#198754',

//                     'bahan finishing' => '#fd7e14',

//                     default => '#6c757d'

//                 },

//                 'bg_soft' => '#f8f9fa',

//                 'icon' => 'bi-box'

//             ];

//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Fast Moving Material
//         |--------------------------------------------------------------------------
//         */

//         $topOutgoingMaterials = TransaksiStok::select(

//             'stok_id',

//             DB::raw('SUM(qty) as total_out_qty'),

//             DB::raw('COUNT(*) as out_frequency')

//         )

//             ->where('tipe', 'out')

//             ->groupBy('stok_id')

//             ->orderByDesc('total_out_qty')

//             ->with('stok')

//             ->take(20)

//             ->get()

//             ->map(function ($row) {

//                 return (object) [

//                     'code' => $row->stok->kode_barang,

//                     'name' => $row->stok->nama_barang,

//                     'category' => $row->stok->jenis,

//                     'location' => '-',

//                     'unit' => $row->stok->satuan,

//                     'total_out_qty' => $row->total_out_qty,

//                     'total_out_value' =>

//                         $row->total_out_qty * $row->stok->harga,

//                     'out_frequency' =>

//                         $row->out_frequency,

//                 ];

//             });

//         /*
//         |--------------------------------------------------------------------------
//         | Stock Movement 6 Months
//         |--------------------------------------------------------------------------
//         */

//         $stockMovement = [];

//         for ($i = 5; $i >= 0; $i--) {

//             $date = Carbon::now()->subMonths($i);

//             $stockIn = TransaksiStok::join(
//                 'stoks',
//                 'stoks.id',
//                 '=',
//                 'transaksi_stoks.stok_id'
//             )
//                 ->where('tipe', 'in')
//                 ->whereYear('tanggal', $date->year)
//                 ->whereMonth('tanggal', $date->month)
//                 ->selectRaw('SUM(qty * harga) as total')
//                 ->value('total') ?? 0;

//             $stockOut = TransaksiStok::join(
//                 'stoks',
//                 'stoks.id',
//                 '=',
//                 'transaksi_stoks.stok_id'
//             )
//                 ->where('tipe', 'out')
//                 ->whereYear('tanggal', $date->year)
//                 ->whereMonth('tanggal', $date->month)
//                 ->selectRaw('SUM(qty * harga) as total')
//                 ->value('total') ?? 0;

//             $stockMovement[] = [

//                 'month' => $date->format('M'),

//                 // simpan angka asli
//                 'stock_in' => round($stockIn / 1000000, 2),

//                 'stock_out' => round($stockOut / 1000000, 2),

//                 // tambahan supaya mudah dibaca
//                 'stock_in_text' => 'Rp ' . number_format($stockIn / 1000000, 2) . ' Jt',

//                 'stock_out_text' => 'Rp ' . number_format($stockOut / 1000000, 2) . ' Jt',

//                 'difference' => round(($stockIn - $stockOut) / 1000000, 2),

//                 'status' => $stockIn >= $stockOut
//                     ? 'Surplus'
//                     : 'Defisit',

//             ];

//         }

//         /*
//         |--------------------------------------------------------------------------
//         | Summary By Unit
//         |--------------------------------------------------------------------------
//         */

//         $unitSummaries = [];

//         foreach ($stocks->groupBy('satuan') as $unit => $items) {

//             $qty = 0;

//             $value = 0;

//             foreach ($items as $item) {

//                 $summary = $stockSummary->get($item->id);

//                 $stokAkhir =
//                     $item->stok_awal
//                     + ($summary->total_in_qty ?? 0)
//                     - ($summary->total_out_qty ?? 0);

//                 $qty += $stokAkhir;

//                 $value += $stokAkhir * $item->harga;

//             }

//             $unitSummaries[] = [

//                 'unit' => $unit,

//                 'total_items' => $items->count(),

//                 'total_stock' => $qty,

//                 'inventory_value' => $value,

//             ];

//         }

//         return view(

//             'pages.laporan.overview',

//             compact(

//                 'totalSku',

//                 'totalInventoryValue',

//                 'lowStockCount',

//                 'emptyStockCount',

//                 'categoriesData',

//                 'topOutgoingMaterials',

//                 'stockMovement',

//                 'unitSummaries'

//             )

//         );

//     }
    // export 
    public function exportWarehouseHistory(Request $request)
    {
        $query = TransaksiStok::with([
            'stok',
            'spk',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('po', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%")
                    ->orWhereHas('stok', function ($qq) use ($search) {

                        $qq->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");

                    });

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Range Tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {

            $query->whereDate(
                'tanggal',
                '>=',
                $request->date_from
            );

        }

        if ($request->filled('date_to')) {

            $query->whereDate(
                'tanggal',
                '<=',
                $request->date_to
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {

            $query->where(
                'tipe',
                $request->type
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Jenis Barang
        |--------------------------------------------------------------------------
        */

        if ($request->filled('jenis')) {

            $query->whereHas('stok', function ($q) use ($request) {

                $q->where(
                    'jenis',
                    $request->jenis
                );

            });

        }

        $histories = $query
            ->orderByDesc('tanggal')
            ->get();

        return Excel::download(

            new WarehouseHistoryExport(

                $histories,

                $request->date_from,

                $request->date_to

            ),

            'WAREHOUSE_HISTORY_'
            . now()->format('Ymd_His')
            . '.xlsx'

        );
    }
    // Update SPK (tetap dipakai oleh drawer)

    public function updateHistoryField(Request $request, $id)
    {
        $history = TransaksiStok::with('stok')->findOrFail($id);

        switch ($request->name) {

            case 'tanggal':
                $history->tanggal = $request->value;
                break;

            case 'qty':
                $history->qty = $request->value;
                break;

            case 'po':
                $history->po = $request->value;
                break;

            case 'no_invoice':
                $history->no_invoice = $request->value;
                break;

            case 'keterangan':
                $history->keterangan = $request->value;
                break;

            case 'satuan':

                if ($history->stok) {

                    $history->stok->satuan = $request->value;
                    $history->stok->save();

                }

                return response()->json([
                    'status' => 'success'
                ]);
        }

        $history->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function deleteTransaksi($id)
    {
        try {

            // Hanya user Sumanti yang boleh delete
            if (strtolower(auth()->user()->name ?? '') !== 'sumanti') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus transaksi.'
                ], 403);
            }

            $transaksi = TransaksiStok::find($id);

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan.'
                ], 404);
            }

            $transaksi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function warehousePurchasingPendingCount()
    {
        $count = Pengajuan::query()
            ->where('type_pengajuan', 'purchasing')
            ->where('is_draft', 1)
            ->whereHas('approvalSteps')
            ->whereDoesntHave('approvalSteps', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhere('status', '!=', 'approved');
                });
            })
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

}
