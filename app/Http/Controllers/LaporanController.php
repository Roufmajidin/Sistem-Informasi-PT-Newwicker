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
use App\Exports\WarehouseOpnameExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MonitoringInvoice;
use App\Models\InvLama;
use App\Models\SpkLama;
use App\Exports\LaporanStokExport;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{

    public function exportOpname(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'opname' => ['nullable', 'in:has_opname'],
        ]);

        $stoks = Stok::query()
            ->where('is_opname', true)
            ->whereNotNull('opname_at')
            ->when(!empty($validated['jenis']), fn($q) => $q->where('jenis', $validated['jenis']))
            ->when(!empty($validated['search']), function ($q) use ($validated) {
                $search = $validated['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_barang')->orderBy('id')->get();

        $transactions = collect();
        if ($stoks->isNotEmpty()) {
            $transactions = TransaksiStok::query()
                ->with(['spk'])
                ->join('stoks', 'stoks.id', '=', 'transaksi_stoks.stok_id')
                ->whereIn('transaksi_stoks.stok_id', $stoks->pluck('id'))
                ->where(function ($q) {
                    $q->whereRaw('DATE(transaksi_stoks.tanggal) > DATE(stoks.opname_at)')
                        ->orWhere(function ($sameDay) {
                            $sameDay->whereRaw('DATE(transaksi_stoks.tanggal) = DATE(stoks.opname_at)')
                                ->whereRaw('TIME(transaksi_stoks.created_at) > TIME(stoks.opname_at)');
                        });
                })
                ->where(function ($q) {
                    $q->whereNull('transaksi_stoks.keterangan')
                        ->orWhere('transaksi_stoks.keterangan', 'not like', '%opname%');
                })
                ->select('transaksi_stoks.*')
                ->orderBy('transaksi_stoks.stok_id')
                ->orderBy('transaksi_stoks.tanggal')
                ->orderBy('transaksi_stoks.created_at')
                ->orderBy('transaksi_stoks.id')
                ->get();
        }

        return Excel::download(
            new \App\Exports\WarehouseOpnameExport($stoks, $transactions),
            'Warehouse_Report_Opname_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function index(Request $request)
    {
        $jenis = trim((string) $request->input('jenis', ''));
        $search = trim((string) $request->input('search', ''));
        $opname = trim((string) $request->input('opname', ''));

        // Pagination tetap dilakukan terlebih dahulu.
        $stoks = Stok::query()
            ->when($opname === 'has_opname', function ($query) {
                $query->where('is_opname', true)
                    ->whereNotNull('opname_at');
            })
            ->when($jenis !== '', function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('kode_barang', 'like', '%' . $search . '%')
                        ->orWhere('nama_barang', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nama_barang')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $ids = $stoks->getCollection()->pluck('id')->all();
        $totals = collect();

        if (!empty($ids)) {
            $totals = TransaksiStok::query()
                ->join(
                    'stoks',
                    'stoks.id',
                    '=',
                    'transaksi_stoks.stok_id'
                )
                ->whereIn('transaksi_stoks.stok_id', $ids)
                ->where(function ($query) {

                    // ==========================================
                    // 1. BARANG SUDAH OPNAME
                    // ==========================================
                    $query->where(function ($q) {

                        $q->where('stoks.is_opname', true)
                            ->whereNotNull('stoks.opname_at')

                            ->where(function ($cutoffQuery) {

                                // Transaksi setelah tanggal opname.
                                $cutoffQuery->whereRaw(
                                    'DATE(transaksi_stoks.tanggal) > DATE(stoks.opname_at)'
                                )

                                    // Transaksi pada tanggal opname:
                                    // bandingkan jam created_at.
                                    ->orWhere(function ($sameDay) {
                                    $sameDay->whereRaw(
                                        'DATE(transaksi_stoks.tanggal) = DATE(stoks.opname_at)'
                                    )
                                        ->whereRaw(
                                            'TIME(transaksi_stoks.created_at) > TIME(stoks.opname_at)'
                                        );
                                });
                            })

                            // Kecualikan transaksi penanda opname.
                            ->where(function ($sub) {
                                $sub->whereNull('transaksi_stoks.keterangan')
                                    ->orWhere(
                                        'transaksi_stoks.keterangan',
                                        'NOT LIKE',
                                        '%opname%'
                                    );
                            });
                    })

                        // ==========================================
                        // 2. BARANG BELUM OPNAME
                        // RUMUS ORIGINAL TIDAK DIUBAH
                        // ==========================================
                        ->orWhere(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('stoks.is_opname', false)
                                ->orWhereNull('stoks.is_opname')
                                ->orWhereNull('stoks.opname_at');
                        });
                    });
                })
                ->select('transaksi_stoks.stok_id')
                ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN transaksi_stoks.tipe = 'in'
                            THEN transaksi_stoks.qty
                            ELSE 0
                        END
                    ), 0
                ) AS total_in
            ")
                ->selectRaw("
                COALESCE(
                    SUM(
                        CASE
                            WHEN transaksi_stoks.tipe = 'out'
                            THEN transaksi_stoks.qty
                            ELSE 0
                        END
                    ), 0
                ) AS total_out
            ")
                ->groupBy('transaksi_stoks.stok_id')
                ->get()
                ->keyBy('stok_id');
        }

        // ==========================================
        // HITUNG SALDO SETIAP BARANG
        // ==========================================
        $stoks->getCollection()->transform(function ($stok) use ($totals) {

            $total = $totals->get($stok->id);

            $stok->total_in = (float) ($total->total_in ?? 0);
            $stok->total_out = (float) ($total->total_out ?? 0);

            $hasOpnameCutoff = (bool) $stok->is_opname
                && !empty($stok->opname_at);

            if ($hasOpnameCutoff) {

                // Sudah opname:
                // hasil opname + IN setelah cutoff - OUT setelah cutoff.
                $stok->saldo =
                    (float) ($stok->stok_awal ?? 0)
                    + $stok->total_in
                    - $stok->total_out;

            } else {

                // Belum opname:
                // rumus original, seluruh IN - OUT.
                $stok->saldo =
                    $stok->total_in
                    - $stok->total_out;
            }

            return $stok;
        });

        // ==========================================
        // AJAX FILTER & PAGINATION
        // ==========================================
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view(
                    'pages.laporan.partials.rows',
                    compact('stoks')
                )->render(),
                'pagination' => $stoks->links()->render(),
                'total' => $stoks->total(),
                'current_page' => $stoks->currentPage(),
                'last_page' => $stoks->lastPage(),
            ]);
        }

        return view(
            'pages.laporan.index',
            compact('stoks', 'search', 'jenis', 'opname')
        );
    }





    // opname 

    public function storeOpname(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:stoks,id'],
            'stok_awal' => ['required', 'numeric', 'min:0'],
            'opname_at' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'before_or_equal:now',
            ],
        ]);

        $stok = DB::transaction(function () use ($validated) {
            $stok = Stok::query()
                ->lockForUpdate()
                ->findOrFail($validated['id']);

            $stok->stok_awal = $validated['stok_awal'];
            $stok->is_opname = true;
            $stok->opname_at = $validated['opname_at'];
            $stok->save();

            return $stok;
        });

        return response()->json([
            'success' => true,
            'is_opname' => true,
            'id' => $stok->id,
            'stok_awal' => (float) $stok->stok_awal,
            'opname_at' => $stok->opname_at,
            'message' => 'Stock opname berhasil disimpan.',
        ]);
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
            'id' => 'nullable|integer',
            'kode_barang' => 'nullable|string|max:255',
            'nama_barang' => 'nullable|string|max:255',
            'jenis' => 'nullable|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable',
            'stok_awal' => 'nullable|numeric',
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
                $stok->jenis = $request->jenis;
                $stok->satuan = $request->satuan;
                $stok->harga = $harga;

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
                                'stok_id' => $stok->id,
                                'tanggal' => now(),
                                'tipe' => 'in',
                                'qty' => round($selisih, 3),
                                'keterangan' => 'Opname',
                            ]);

                        } else {

                            // Target lebih kecil → OUT
                            TransaksiStok::create([
                                'stok_id' => $stok->id,
                                'tanggal' => now(),
                                'tipe' => 'out',
                                'qty' => round(abs($selisih), 3),
                                'keterangan' => 'Opname',
                            ]);
                        }
                    }
                }


                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data stok berhasil diperbarui.',
                    'id' => $stok->id,
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
                'jenis' => $request->jenis,
                'satuan' => $request->satuan,
                'harga' => $harga,
                'stok_awal' => (float) ($request->stok_awal ?? 0),
            ]);


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang baru berhasil ditambahkan.',
                'id' => $stok->id,
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

        $hasOpnameCutoff = (bool) $stok->is_opname
            && !empty($stok->opname_at);

        $saldoQuery = TransaksiStok::query()
            ->where('stok_id', $stok->id);

        if ($hasOpnameCutoff) {
            $cutoff = Carbon::parse($stok->opname_at);

            $saldoQuery->where(function ($query) use ($cutoff) {
                // Transaksi setelah tanggal opname
                $query->whereDate(
                    'tanggal',
                    '>',
                    $cutoff->toDateString()
                );

                // Transaksi pada tanggal opname:
                // bandingkan jam pencatatan
                $query->orWhere(function ($sub) use ($cutoff) {
                    $sub->whereDate(
                        'tanggal',
                        $cutoff->toDateString()
                    )
                        ->whereRaw(
                            'TIME(created_at) > ?',
                            [$cutoff->format('H:i:s')]
                        );
                });
            });

            $saldoQuery->where(function ($query) {
                $query->whereNull('keterangan')
                    ->orWhere(
                        'keterangan',
                        'NOT LIKE',
                        '%opname%'
                    );
            });
        }

        // Belum opname:
        // tetap menggunakan seluruh transaksi (rumus original).
        $totals = $saldoQuery
            ->selectRaw("
            COALESCE(
                SUM(CASE WHEN tipe = 'in'
                    THEN qty ELSE 0 END), 0
            ) AS total_in
        ")
            ->selectRaw("
            COALESCE(
                SUM(CASE WHEN tipe = 'out'
                    THEN qty ELSE 0 END), 0
            ) AS total_out
        ")
            ->first();

        $stokTersedia =
            ($hasOpnameCutoff
                ? (float) ($stok->stok_awal ?? 0)
                : 0)
            + (float) ($totals->total_in ?? 0)
            - (float) ($totals->total_out ?? 0);

        $transaksi = TransaksiStok::query()
            ->with(['stok', 'spk'])
            ->where('stok_id', $stok->id)
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate(
                    'tanggal',
                    '>=',
                    $request->tanggal_awal
                );
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate(
                    'tanggal',
                    '<=',
                    $request->tanggal_akhir
                );
            })
            ->orderBy('tanggal', 'desc')
            ->orderByRaw('TIME(created_at) DESC')
            ->orderBy('id', 'desc')
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

    // at 9/11/26
    // public function detailBarang(Request $request, $id)
    // {
    //     $stok = Stok::findOrFail($id);
    //     $hasOpnameCutoff = (bool) $stok->is_opname && !empty($stok->opname_at);

    //     $saldoQuery = TransaksiStok::query()->where('stok_id', $stok->id);

    //     if ($hasOpnameCutoff) {
    //         // Samakan dengan index: tanggal efektif transaksi, bukan created_at.
    //         // Transaksi pada hari opname dianggap sebelum cut-off.

    //         $saldoQuery->where(
    //             'tanggal',
    //             '>',
    //             Carbon::parse($stok->opname_at)->format('Y-m-d H:i:s')
    //         )
    //             ->where(function ($q) {
    //                 $q->whereNull('keterangan')
    //                     ->orWhere('keterangan', 'not like', '%opname%');
    //             });
    //     }
    //     // Belum opname: semua transaksi termasuk keterangan Opname.
    //     $totals = $saldoQuery
    //         ->selectRaw("COALESCE(SUM(CASE WHEN tipe = 'in' THEN qty ELSE 0 END), 0) AS total_in")
    //         ->selectRaw("COALESCE(SUM(CASE WHEN tipe = 'out' THEN qty ELSE 0 END), 0) AS total_out")
    //         ->first();

    //     $stokTersedia = ($hasOpnameCutoff ? (float) ($stok->stok_awal ?? 0) : 0)
    //         + (float) ($totals->total_in ?? 0)
    //         - (float) ($totals->total_out ?? 0);

    //     // Riwayat tetap utuh; filter tanggal hanya mengubah daftar riwayat.
    //     $transaksi = TransaksiStok::query()
    //         ->with(['stok', 'spk'])
    //         ->where('stok_id', $stok->id)
    //         ->when($request->tanggal_awal, function ($query) use ($request) {
    //             $query->whereDate('tanggal', '>=', $request->tanggal_awal);
    //         })
    //         ->when($request->tanggal_akhir, function ($query) use ($request) {
    //             $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
    //         })
    //         ->orderBy('tanggal', 'desc')
    //         ->get();

    //     return view('pages.laporan.detail', compact('stok', 'transaksi', 'stokTersedia'));
    // }

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
        )->where(function ($q) {
            $q->whereNull('keterangan')
                ->orWhere('keterangan', 'not like', '%opname%');
        })

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
        // =====================================================
        // 1. VALIDASI
        // =====================================================
        $validated = $request->validate([
            'stok_id' => 'required|integer|exists:stoks,id',
            'tanggal' => 'required|date',
            'in' => 'nullable|numeric|min:0',
            'out' => 'nullable|numeric|min:0',
            'po' => 'nullable|string',
            'spk_id' => 'nullable|integer',
            'keterangan' => 'nullable|string',
            'no_invoice' => 'nullable|string',
            'return' => 'nullable|boolean',
        ]);

        // =====================================================
        // 2. NORMALISASI NILAI
        // =====================================================
        $qtyIn = (float) $request->input('in', 0);
        $qtyOut = (float) $request->input('out', 0);

        $isReturn = $request->boolean('return') ? 1 : 0;

        // =====================================================
        // 3. DEBUG REQUEST
        // =====================================================
        Log::info('STORE TRANSAKSI REQUEST', [
            'stok_id' => $request->stok_id,
            'spk_id' => $request->spk_id,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'return_raw' => $request->input('return'),
            'return_normalized' => $isReturn,
        ]);

        // =====================================================
        // 4. VALIDASI TRANSAKSI
        // =====================================================
        if ($qtyIn <= 0 && $qtyOut <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Isi jumlah IN atau OUT terlebih dahulu.',
            ], 422);
        }

        if ($isReturn === 1 && !$request->filled('spk_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih SPK terlebih dahulu untuk Return.',
            ], 422);
        }

        // =====================================================
        // 5. DATA UMUM
        // =====================================================
        $data = [
            'stok_id' => $request->stok_id,
            'tanggal' => $request->tanggal,
            'po' => $request->po,
            'spk_id' => $request->spk_id ?: null,
            'keterangan' => $request->keterangan,
            'no_invoice' => $request->no_invoice,
            'return' => $isReturn,
        ];

        // =====================================================
        // 6. SIMPAN TRANSAKSI
        // =====================================================
        try {
            $savedIds = DB::transaction(function () use ($qtyIn, $qtyOut, $data) {
                $ids = [];

                // TRANSAKSI IN
                if ($qtyIn > 0) {
                    $transaksiIn = new TransaksiStok();

                    $transaksiIn->forceFill(array_merge(
                        $data,
                        [
                            'tipe' => 'in',
                            'qty' => $qtyIn,
                        ]
                    ));

                    $transaksiIn->save();

                    $ids[] = $transaksiIn->id;
                }

                // TRANSAKSI OUT
                if ($qtyOut > 0) {
                    $transaksiOut = new TransaksiStok();

                    $transaksiOut->forceFill(array_merge(
                        $data,
                        [
                            'tipe' => 'out',
                            'qty' => $qtyOut,
                        ]
                    ));

                    $transaksiOut->save();

                    $ids[] = $transaksiOut->id;
                }

                return $ids;
            });

            // =================================================
            // 7. VERIFIKASI HASIL DATABASE
            // =================================================
            $saved = TransaksiStok::query()
                ->whereIn('id', $savedIds)
                ->get(['id', 'spk_id', 'return']);

            Log::info('STORE TRANSAKSI RESULT', [
                'ids' => $savedIds,
                'return_requested' => $isReturn,
                'saved_rows' => $saved->toArray(),
            ]);

            // =================================================
            // 8. RESPONSE
            // =================================================
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
            ]);

        } catch (\Throwable $e) {
            Log::error('STORE TRANSAKSI ERROR', [
                'message' => $e->getMessage(),
                'stok_id' => $request->stok_id,
                'return' => $isReturn,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi.',
            ], 500);
        }
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
        $keyword = trim((string) $request->input('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        // Pencarian tidak membedakan huruf besar dan kecil.
        // Tetap mencari pada seluruh isi JSON data,
        // termasuk no_spk dan supplier (sup).
        $keywordLower = mb_strtolower($keyword, 'UTF-8');

        $spks = Spk::query()
            ->whereRaw(
                'LOWER(CAST(`data` AS CHAR)) LIKE ?',
                ['%' . $keywordLower . '%']
            )
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

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            $result[] = [
                'id' => $spk->id,
                'no_spk' => $data['no_spk'] ?? '',
                'supplier' => $data['sup'] ?? '',
                'items' => $items,
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
  
  
public function overview(Request $request)
{
    $validated = $request->validate([
        'jenis'  => ['nullable', 'string', 'max:255'],
        'search' => ['nullable', 'string', 'max:255'],
    ]);

    $jenis = trim((string) ($validated['jenis'] ?? ''));
    $search = trim((string) ($validated['search'] ?? ''));

    /*
    |--------------------------------------------------------------------------
    | 1. MASTER STOK HAS OPNAME
    |--------------------------------------------------------------------------
    | Semua jenis stok yang sudah opname.
    | Belum opname tidak masuk dashboard.
    */
    $base = Stok::query()
        ->where('is_opname', true)
        ->whereNotNull('opname_at')
        ->when($jenis !== '', function ($query) use ($jenis) {
            $query->where('jenis', $jenis);
        })
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        });

    /*
    |--------------------------------------------------------------------------
    | 2. FILTER JENIS STOK
    |--------------------------------------------------------------------------
    */
    $jenisOptions = Stok::query()
        ->where('is_opname', true)
        ->whereNotNull('opname_at')
        ->whereNotNull('jenis')
        ->distinct()
        ->orderBy('jenis')
        ->pluck('jenis');

    /*
    |--------------------------------------------------------------------------
    | 3. TRANSAKSI SETELAH CUT-OFF OPNAME
    |--------------------------------------------------------------------------
    | Mengikuti logika exportOpname().
    |
    | - Tanggal transaksi > tanggal opname
    | - Atau tanggal sama dan created_at > opname_at
    | - Transaksi penanda opname tidak dihitung
    */
    $movementBase = TransaksiStok::query()
        ->join(
            'stoks',
            'stoks.id',
            '=',
            'transaksi_stoks.stok_id'
        )
        ->where('stoks.is_opname', true)
        ->whereNotNull('stoks.opname_at')
        ->when($jenis !== '', function ($query) use ($jenis) {
            $query->where('stoks.jenis', $jenis);
        })
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where(
                    'stoks.kode_barang',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'stoks.nama_barang',
                    'like',
                    "%{$search}%"
                );
            });
        })
        ->where(function ($query) {
            $query
                ->whereRaw(
                    'DATE(transaksi_stoks.tanggal) > DATE(stoks.opname_at)'
                )
                ->orWhere(function ($sameDay) {
                    $sameDay
                        ->whereRaw(
                            'DATE(transaksi_stoks.tanggal) = DATE(stoks.opname_at)'
                        )
                        ->whereRaw(
                            'TIME(transaksi_stoks.created_at) > TIME(stoks.opname_at)'
                        );
                });
        })
        ->where(function ($query) {
            $query
                ->whereNull('transaksi_stoks.keterangan')
                ->orWhere(
                    'transaksi_stoks.keterangan',
                    'not like',
                    '%opname%'
                );
        });

    /*
    |--------------------------------------------------------------------------
    | 4. TOTAL MASUK & KELUAR PER BARANG
    |--------------------------------------------------------------------------
    */
    $totals = (clone $movementBase)
        ->select('transaksi_stoks.stok_id')
        ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'in'
                        THEN transaksi_stoks.qty
                        ELSE 0
                    END
                ), 0
            ) AS total_in
        ")
        ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'out'
                        THEN transaksi_stoks.qty
                        ELSE 0
                    END
                ), 0
            ) AS total_out
        ")
        ->groupBy('transaksi_stoks.stok_id')
        ->get()
        ->keyBy('stok_id');

    /*
    |--------------------------------------------------------------------------
    | 5. HITUNG SALDO SELURUH BARANG
    |--------------------------------------------------------------------------
    | Perhitungan ini berlaku untuk SEMUA barang terfilter,
    | bukan hanya 25 barang pada halaman pagination.
    |
    | Qty opname 0 tetap dianggap nilai valid.
    */
    $summaryRows = (clone $base)
        ->get()
        ->map(function ($stok) use ($totals) {

            $transaction = $totals->get($stok->id);

            $stok->total_in = (float) (
                $transaction->total_in ?? 0
            );

            $stok->total_out = (float) (
                $transaction->total_out ?? 0
            );

            $opname = (float) ($stok->stok_awal ?? 0);

            $harga = (float) ($stok->harga ?? 0);

            $stok->saldo = (
                $opname
                + $stok->total_in
                - $stok->total_out
            );

            $stok->nilai_saldo = $stok->saldo * $harga;

            $stok->nilai_masuk = $stok->total_in * $harga;

            $stok->nilai_keluar = $stok->total_out * $harga;

            return $stok;
        });

    /*
    |--------------------------------------------------------------------------
    | 6. KPI DASHBOARD
    |--------------------------------------------------------------------------
    | Saldo nilai dihitung dari:
    |
    | SUM(saldo setiap barang × harga master)
    |
    | Inilah angka yang seharusnya mendekati Rp 270 jutaan,
    | sesuai dashboard sebelumnya, apabila data/filter sama.
    */
    $summary = [
        'items' => $summaryRows->count(),

        'jenis' => $summaryRows
            ->pluck('jenis')
            ->filter()
            ->unique()
            ->count(),

        'saldo_value' => $summaryRows
            ->sum('nilai_saldo'),

        'in_value' => $summaryRows
            ->sum('nilai_masuk'),

        'out_value' => $summaryRows
            ->sum('nilai_keluar'),
    ];

    /*
    |--------------------------------------------------------------------------
    | 7. STOCK MOVEMENT — 6 BULAN TERAKHIR
    |--------------------------------------------------------------------------
    | Grafik menggunakan nilai Rupiah.
    | Hanya transaksi setelah cut-off opname.
    */
    $from = now()
        ->startOfMonth()
        ->subMonths(5)
        ->toDateString();

    $movementRaw = (clone $movementBase)
        ->whereDate(
            'transaksi_stoks.tanggal',
            '>=',
            $from
        )
        ->selectRaw("
            DATE_FORMAT(
                transaksi_stoks.tanggal,
                '%Y-%m'
            ) AS period
        ")
        ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'in'
                        THEN transaksi_stoks.qty
                             * COALESCE(stoks.harga, 0)
                        ELSE 0
                    END
                ), 0
            ) AS stock_in
        ")
        ->selectRaw("
            COALESCE(
                SUM(
                    CASE
                        WHEN transaksi_stoks.tipe = 'out'
                        THEN transaksi_stoks.qty
                             * COALESCE(stoks.harga, 0)
                        ELSE 0
                    END
                ), 0
            ) AS stock_out
        ")
        ->groupBy('period')
        ->orderBy('period')
        ->get()
        ->keyBy('period');

    $movement = collect(range(5, 0))
        ->map(function ($offset) use ($movementRaw) {

            $date = now()
                ->startOfMonth()
                ->subMonths($offset);

            $period = $date->format('Y-m');

            $row = $movementRaw->get($period);

            return [
                'month' => $date->translatedFormat('M Y'),

                'stock_in' => (float) (
                    $row->stock_in ?? 0
                ),

                'stock_out' => (float) (
                    $row->stock_out ?? 0
                ),
            ];
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | 8. GRAFIK SALDO STOK PER JENIS
    |--------------------------------------------------------------------------
    | Semua jenis stok Has Opname.
    | Nilai Rupiah, bukan total qty lintas satuan.
    */
    $byJenis = $summaryRows
        ->groupBy(function ($stok) {
            return $stok->jenis ?: 'Tanpa Jenis';
        })
        ->map(function ($group, $name) {

            return [
                'jenis' => $name,

                'items' => $group->count(),

                'saldo_value' => $group
                    ->sum('nilai_saldo'),
            ];
        })
        ->values()
        ->sortByDesc('saldo_value')
        ->values();

    /*
    |--------------------------------------------------------------------------
    | 9. TABEL DETAIL + PAGINATION
    |--------------------------------------------------------------------------
    */
    $stoks = (clone $base)
        ->orderBy('nama_barang')
        ->orderBy('id')
        ->paginate(25)
        ->withQueryString();

    $stoks->getCollection()
        ->transform(function ($stok) use ($totals) {

            $transaction = $totals->get($stok->id);

            $stok->total_in = (float) (
                $transaction->total_in ?? 0
            );

            $stok->total_out = (float) (
                $transaction->total_out ?? 0
            );

            $opname = (float) ($stok->stok_awal ?? 0);

            $harga = (float) ($stok->harga ?? 0);

            $stok->saldo = (
                $opname
                + $stok->total_in
                - $stok->total_out
            );

            $stok->nilai_saldo = $stok->saldo * $harga;

            return $stok;
        });

    /*
    |--------------------------------------------------------------------------
    | 10. RETURN VIEW
    |--------------------------------------------------------------------------
    */
    return view(
        'pages.laporan.overview',
        compact(
            'stoks',
            'summary',
            'movement',
            'byJenis',
            'jenisOptions',
            'jenis',
            'search'
        )
    );
}

    public function exportWarehouseHistory(Request $request)
    {
        $query = TransaksiStok::with([
            'stok',
            'spk',
        ])->where(function ($q) {
            $q->whereNull('keterangan')
                ->orWhere('keterangan', 'not like', '%opname%');
        });

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
    public function updateHistorySpk(Request $request, $id)
    {
        $history = TransaksiStok::findOrFail($id);

        $history->spk_id = $request->spk_id;

        $history->save();

        return response()->json([
            'status' => 'success',
            'msg' => 'SPK berhasil diupdate.'
        ]);
    }
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
    public function exportExcel(Request $request)
    {
        $jenis = $request->query('jenis');
        $search = $request->query('search');

        $namaFile = 'laporan-stok-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new LaporanStokExport($jenis, $search),
            $namaFile
        );
    }
}
