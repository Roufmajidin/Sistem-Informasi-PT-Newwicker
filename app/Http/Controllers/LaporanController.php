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
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', 'in:in,out'],
            'jenis' => ['nullable', 'string', 'max:255'],
            'opname' => ['nullable', 'in:all,has_opname,belum_opname'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $opname = $validated['opname'] ?? 'has_opname';

        $query = TransaksiStok::query()
            ->with(['stok', 'spk'])

            // Transaksi penanda opname tidak dihitung.
            ->where(function ($q) {
                $q->whereNull('transaksi_stoks.keterangan')
                    ->orWhere(
                        'transaksi_stoks.keterangan',
                        'not like',
                        '%opname%'
                    );
            });

        // ==============================================
        // FILTER STATUS OPNAME
        // ==============================================

        if ($opname === 'has_opname') {

            $query->whereHas('stok', function ($q) {
                $q->where('is_opname', true)
                    ->whereNotNull('opname_at');
            });

            // Sama dengan cut-off Warehouse Overview.
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('stoks')
                    ->whereColumn(
                        'stoks.id',
                        'transaksi_stoks.stok_id'
                    )
                    ->where('stoks.is_opname', true)
                    ->whereNotNull('stoks.opname_at')
                    ->where(function ($cutoff) {

                        $cutoff->whereRaw(
                            'DATE(transaksi_stoks.tanggal) > DATE(stoks.opname_at)'
                        )
                            ->orWhere(function ($sameDay) {
                                $sameDay->whereRaw(
                                    'DATE(transaksi_stoks.tanggal) = DATE(stoks.opname_at)'
                                )
                                    ->whereRaw(
                                        'TIME(transaksi_stoks.created_at) > TIME(stoks.opname_at)'
                                    );
                            });

                    });
            });

        } elseif ($opname === 'belum_opname') {

            $query->whereHas('stok', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('is_opname', false)
                        ->orWhereNull('is_opname')
                        ->orWhereNull('opname_at');
                });
            });
        }

        // ==============================================
        // SEARCH
        // ==============================================

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaksi_stoks.po', 'like', "%{$search}%")
                    ->orWhere(
                        'transaksi_stoks.keterangan',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'transaksi_stoks.tipe',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas('stok', function ($stok) use ($search) {
                        $stok->where(
                            'nama_barang',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'kode_barang',
                                'like',
                                "%{$search}%"
                            );
                    });
            });
        }

        // ==============================================
        // FILTER TANGGAL
        // ==============================================

        if (!empty($validated['date_from'])) {
            $query->whereDate(
                'transaksi_stoks.tanggal',
                '>=',
                $validated['date_from']
            );
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate(
                'transaksi_stoks.tanggal',
                '<=',
                $validated['date_to']
            );
        }

        // ==============================================
        // FILTER IN / OUT
        // ==============================================

        if (!empty($validated['type'])) {
            $query->where(
                'transaksi_stoks.tipe',
                $validated['type']
            );
        }

        // ==============================================
        // FILTER JENIS STOK
        // ==============================================

        if (!empty($validated['jenis'])) {
            $query->whereHas('stok', function ($q) use ($validated) {
                $q->where('jenis', $validated['jenis']);
            });
        }

        // ==============================================
        // SUMMARY — SEMUA DATA SESUAI FILTER
        // ==============================================

        $summary = (clone $query)
            ->join(
                'stoks as summary_stoks',
                'summary_stoks.id',
                '=',
                'transaksi_stoks.stok_id'
            )
            ->selectRaw("
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(transaksi_stoks.qty), 0) AS total_qty,
            COALESCE(SUM(
                transaksi_stoks.qty *
                COALESCE(summary_stoks.harga, 0)
            ), 0) AS total_value,
            COALESCE(SUM(
                CASE
                    WHEN transaksi_stoks.tipe = 'in'
                    THEN transaksi_stoks.qty *
                         COALESCE(summary_stoks.harga, 0)
                    ELSE 0
                END
            ), 0) AS total_in_value,
            COALESCE(SUM(
                CASE
                    WHEN transaksi_stoks.tipe = 'out'
                    THEN transaksi_stoks.qty *
                         COALESCE(summary_stoks.harga, 0)
                    ELSE 0
                END
            ), 0) AS total_out_value
        ")
            ->first();

        // ==============================================
        // DATA TABEL
        // ==============================================

        $histories = $query
            ->orderByDesc('transaksi_stoks.tanggal')
            ->orderByDesc('transaksi_stoks.created_at')
            ->orderByDesc('transaksi_stoks.id')
            ->paginate(25)
            ->withQueryString();

        // ==============================================
        // AJAX — TETAP MENGGUNAKAN PARTIAL LAMA
        // ==============================================

        if ($request->ajax()) {
            return view(
                'pages.laporan.partials.history_table',
                compact('histories', 'summary')
            )->render();
        }

        return view(
            'pages.laporan.history',
            compact('histories', 'summary', 'opname')
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

        $jenis = trim((string) $request->query('jenis', ''));

        $search = trim((string) $request->query('search', ''));

        $sortStok = (string) $request->query('sort_stok', 'nama');

        if (!in_array($sortStok, ['nama', 'keluar_terbanyak', 'lama_tidak_keluar'], true)) {

            $sortStok = 'nama';

        }

        $idleDays = (int) $request->query('idle_days', 30);

        if (!in_array($idleDays, [30, 60, 90, 180], true))

            $idleDays = 30;



        $base = Stok::query()

            ->where('is_opname', true)

            ->whereNotNull('opname_at')

            ->when($jenis !== '', fn($q) => $q->where('jenis', $jenis))

            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($sub) use ($search) {

                    $sub->where('kode_barang', 'like', "%{$search}%")

                        ->orWhere('nama_barang', 'like', "%{$search}%");

                });

            });



        $jenisOptions = Stok::query()

            ->where('is_opname', true)

            ->whereNotNull('opname_at')

            ->whereNotNull('jenis')

            ->distinct()->orderBy('jenis')->pluck('jenis');



        // Sama persis dengan cut-off di index() Warehouse:

        // tanggal > tanggal opname ATAU tanggal sama & jam created_at > jam opname.

        // Keterangan penanda opname dikecualikan.

        $movementBase = TransaksiStok::query()

            ->join('stoks', 'stoks.id', '=', 'transaksi_stoks.stok_id')

            ->where('stoks.is_opname', true)

            ->whereNotNull('stoks.opname_at')

            ->when($jenis !== '', fn($q) => $q->where('stoks.jenis', $jenis))

            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($sub) use ($search) {

                    $sub->where('stoks.kode_barang', 'like', "%{$search}%")

                        ->orWhere('stoks.nama_barang', 'like', "%{$search}%");

                });

            })

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

            });



        $totals = (clone $movementBase)

            ->select('transaksi_stoks.stok_id')

            ->selectRaw("COALESCE(SUM(CASE WHEN transaksi_stoks.tipe = 'in' THEN transaksi_stoks.qty ELSE 0 END), 0) AS total_in")

            ->selectRaw("COALESCE(SUM(CASE WHEN transaksi_stoks.tipe = 'out' THEN transaksi_stoks.qty ELSE 0 END), 0) AS total_out")

            ->groupBy('transaksi_stoks.stok_id')

            ->get()->keyBy('stok_id');



        // Summary dihitung dari seluruh stok terfilter, bukan hanya halaman pagination.

        $summaryRows = (clone $base)->get([

            'id',

            'kode_barang',

            'nama_barang',

            'jenis',

            'satuan',

            'harga',

            'stok_awal',

            'opname_at'

        ])->map(function ($stok) use ($totals) {

            $t = $totals->get($stok->id);

            $stok->total_in = (float) ($t->total_in ?? 0);

            $stok->total_out = (float) ($t->total_out ?? 0);

            $stok->saldo = (float) ($stok->stok_awal ?? 0) + $stok->total_in - $stok->total_out;

            $stok->nilai_saldo = $stok->saldo * (float) ($stok->harga ?? 0);

            return $stok;

        });



        $summary = [

            'items' => $summaryRows->count(),

            'jenis' => $summaryRows->pluck('jenis')->filter()->unique()->count(),

            'saldo_value' => $summaryRows->sum('nilai_saldo'),

            'in_value' => $summaryRows->sum(fn($s) => $s->total_in * (float) ($s->harga ?? 0)),

            'out_value' => $summaryRows->sum(fn($s) => $s->total_out * (float) ($s->harga ?? 0)),

        ];



        /*

         |-----------------------------------------------------------------------

         | GRAFIK KUMULATIF MINGGUAN (SENIN - MINGGU)

         |-----------------------------------------------------------------------

         | Opname menjadi pemasukan pada minggu tanggal opname terakhir.

         | IN / OUT setelah cut-off menjadi mutasi pada minggu tanggal transaksi.

         | Semua minggu sebelum jendela tampilan tetap ikut akumulasi.

         | 16 minggu terakhir; minggu berjalan mencakup data sampai hari ini.

         */

        $weekEvents = [];

        $addWeekEvent = function ($date, $field, $amount) use (&$weekEvents) {

            if (!$date)

                return;

            $week = \Carbon\Carbon::parse($date)->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();

            if (!isset($weekEvents[$week])) {

                $weekEvents[$week] = ['in' => 0.0, 'out' => 0.0];

            }

            $weekEvents[$week][$field] += (float) $amount;

        };



        foreach ($summaryRows as $stok) {

            $addWeekEvent(

                $stok->opname_at,

                'in',

                (float) ($stok->stok_awal ?? 0) * (float) ($stok->harga ?? 0)

            );

        }



        // Ambil agregat harian dari transaksi yang SUDAH lolos cut-off.

        // Pengelompokan ke minggu dilakukan di PHP supaya awal minggu konsisten.

        $dailyMovements = (clone $movementBase)

            ->selectRaw('DATE(transaksi_stoks.tanggal) AS event_date')

            ->selectRaw("COALESCE(SUM(CASE WHEN transaksi_stoks.tipe = 'in' THEN transaksi_stoks.qty * COALESCE(stoks.harga, 0) ELSE 0 END), 0) AS stock_in")

            ->selectRaw("COALESCE(SUM(CASE WHEN transaksi_stoks.tipe = 'out' THEN transaksi_stoks.qty * COALESCE(stoks.harga, 0) ELSE 0 END), 0) AS stock_out")

            ->groupByRaw('DATE(transaksi_stoks.tanggal)')

            ->orderBy('event_date')

            ->get();



        foreach ($dailyMovements as $day) {

            $addWeekEvent($day->event_date, 'in', $day->stock_in);

            $addWeekEvent($day->event_date, 'out', $day->stock_out);

        }



        ksort($weekEvents);

        $currentWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);

        $displayFrom = $currentWeek->copy()->subWeeks(15);

        $firstEvent = !empty($weekEvents)

            ? \Carbon\Carbon::parse(array_key_first($weekEvents))

            : $currentWeek->copy();

        $cursor = $firstEvent->copy()->lessThan($displayFrom)

            ? $firstEvent->copy()

            : $displayFrom->copy();

        $runningIn = 0.0;

        $runningOut = 0.0;

        $movement = collect();



        // Hitung dari minggu pertama agar saldo awal pada jendela 16 minggu

        // sudah membawa semua transaksi / opname sebelum jendela.

        while ($cursor->lte($currentWeek)) {

            $key = $cursor->toDateString();

            $runningIn += (float) ($weekEvents[$key]['in'] ?? 0);

            $runningOut += (float) ($weekEvents[$key]['out'] ?? 0);



            if ($cursor->gte($displayFrom)) {

                $weekEnd = $cursor->copy()->addDays(6);

                $movement->push([

                    'month' => $cursor->format('d M') . ' - ' . $weekEnd->format('d M Y'),

                    'week_start' => $key,

                    'stock_in' => round($runningIn, 2),
                    'weekly_in' => round((float) ($weekEvents[$key]['in'] ?? 0), 2),

                    'stock_out' => round($runningOut, 2),
                    'weekly_out' => round((float) ($weekEvents[$key]['out'] ?? 0), 2),

                    'balance' => round($runningIn - $runningOut, 2),
                    'weekly_balance_change' => round((float) ($weekEvents[$key]['in'] ?? 0) - (float) ($weekEvents[$key]['out'] ?? 0), 2),

                ]);

            }

            $cursor->addWeek();

        }



        // KPI pemasukan = nilai opname terakhir + seluruh transaksi IN setelahnya.

        $summary['in_value'] = $summaryRows->sum(function ($stok) {

            return ((float) ($stok->stok_awal ?? 0) + (float) $stok->total_in)

                * (float) ($stok->harga ?? 0);

        });

        // KPI saldo tetap mengikuti saldo barang × harga (tidak diubah).



        $byJenis = $summaryRows->groupBy(fn($s) => $s->jenis ?: 'Tanpa Jenis')

            ->map(fn($group, $name) => [

                'jenis' => $name,

                'items' => $group->count(),

                'saldo_value' => $group->sum('nilai_saldo'),

            ])->values()->sortByDesc('saldo_value')->values();



        // Analisis tabel saja; KPI dan grafik tetap menghitung SEMUA stok Has Opname.

        // Tanggal keluar terakhir berdasarkan transaksi OUT setelah cut-off opname.

        $lastOut = (clone $movementBase)

            ->where('transaksi_stoks.tipe', 'out')

            ->select('transaksi_stoks.stok_id')

            ->selectRaw('MAX(transaksi_stoks.tanggal) AS last_out_date')

            ->groupBy('transaksi_stoks.stok_id')

            ->pluck('last_out_date', 'stok_id');



        $today = now()->startOfDay();

        $tableRows = $summaryRows->map(function ($stok) use ($lastOut, $today) {

            $stok->last_out_date = $lastOut->get($stok->id);

            $referenceDate = $stok->last_out_date ?: $stok->opname_at;

            $stok->days_without_out = $referenceDate

                ? max(0, \Carbon\Carbon::parse($referenceDate)->startOfDay()->diffInDays($today, false))

                : 0;

            return $stok;

        });



        if ($sortStok === 'keluar_terbanyak') {

            $tableRows = $tableRows->sort(function ($a, $b) {

                $cmp = $b->total_out <=> $a->total_out;

                return $cmp ?: strcmp((string) $a->nama_barang, (string) $b->nama_barang);

            });

        } elseif ($sortStok === 'lama_tidak_keluar') {

            $tableRows = $tableRows->filter(

                fn($stok) =>

                    $stok->saldo > 0 && $stok->days_without_out >= $idleDays

            )->sort(function ($a, $b) {

                $cmp = $b->days_without_out <=> $a->days_without_out;

                return $cmp ?: strcmp((string) $a->nama_barang, (string) $b->nama_barang);

            });

        } else {

            $tableRows = $tableRows->sortBy(

                fn($stok) =>

                    mb_strtolower((string) $stok->nama_barang) . '|' . str_pad((string) $stok->id, 12, '0', STR_PAD_LEFT)

            );

        }



        $tableRows = $tableRows->values();

        $page = max(1, (int) request()->query('page', 1));

        $perPage = 25;

        $stoks = new \Illuminate\Pagination\LengthAwarePaginator(

            $tableRows->forPage($page, $perPage)->values(),

            $tableRows->count(),

            $perPage,

            $page,

            ['path' => request()->url(), 'query' => request()->query()]

        );





        // FINISHING: gunakan ledger asli monitoringFinishing(), tanpa mengubah rumusnya.

        // Catatan: ini menjalankan query monitoringFinishing() setiap overview dibuka.

        $finishingView = app(\App\Http\Controllers\ProduksiMnController::class)

            ->monitoringFinishing($request);

        $finishingLedger = $finishingView->getData()['ledger'] ?? [];



        $detectFinishingCategory = function ($value) {

            $text = strtoupper(trim((string) ($value ?? '')));

            if (str_contains($text, 'TOMO') || str_contains($text, 'FINISHING'))

                return 'tomo';

            if (str_contains($text, 'DARTO'))

                return 'darto';

            return null;

        };

        $fields = [

            'to_sub',

            'kategori',

            'kategori_invoice',

            'bagian',

            'category',

            'supplier',

            'name_sub',

            'sub',

            'source',

            'note_tambahan'

        ];

        $finishingMonthly = [];

        foreach ($finishingLedger as $group) {

            $rows = $group['rows'] ?? [];

            if ($rows instanceof \Illuminate\Support\Collection)

                $rows = $rows->all();

            if (!is_array($rows))

                continue;



            // Prioritas kategori GROUP sesuai Blade monitoring Finishing.

            $category = null;

            foreach ($fields as $field) {

                $category = $detectFinishingCategory($group[$field] ?? null);

                if ($category)

                    break;

            }

            if (!$category) {

                foreach ($rows as $row) {

                    if (!is_array($row))

                        continue;

                    foreach ($fields as $field) {

                        $category = $detectFinishingCategory($row[$field] ?? null);

                        if ($category)

                            break 2;

                    }

                }

            }

            if (!in_array($category, ['tomo', 'darto'], true))

                continue;



            foreach ($rows as $row) {

                if (!is_array($row))

                    continue;

                $tanggal = $row['tanggal'] ?? $group['tanggal'] ?? null;

                if (empty($tanggal) || $tanggal === '-')

                    continue;

                try {

                    if (preg_match('/^\d{2}\\/\d{2}\\/\d{4}$/', (string) $tanggal)) {

                        $period = \Carbon\Carbon::createFromFormat('!d/m/Y', $tanggal)->format('Y-m');

                    } else {

                        $period = \Carbon\Carbon::parse($tanggal)->format('Y-m');

                    }

                } catch (\Throwable $e) {

                    continue;

                }

                $finishingMonthly[$period] ??= [

                    'tomo_in' => 0,

                    'tomo_out' => 0,

                    'darto_in' => 0,

                    'darto_out' => 0

                ];

                $finishingMonthly[$period][$category . '_in'] += (float) ($row['debet'] ?? 0);

                $finishingMonthly[$period][$category . '_out'] += (float) ($row['kredit'] ?? 0);

            }

        }

        ksort($finishingMonthly);

        $finishingChart = collect();

        $finishingTotals = [

            'tomo_in' => 0.0,

            'tomo_out' => 0.0,

            'darto_in' => 0.0,

            'darto_out' => 0.0

        ];

        if ($finishingMonthly) {

            $first = \Carbon\Carbon::createFromFormat('!Y-m', array_key_first($finishingMonthly));

            $last = now()->startOfMonth();

            // Jangan loop tak terbatas bila data invoice bertanggal masa depan.

            $last = $last->greaterThan($first) ? $last : $first->copy();

            $displayFrom = $last->copy()->subMonths(11);

            for ($cursor = $first->copy(); $cursor->lte($last); $cursor->addMonth()) {

                $period = $cursor->format('Y-m');

                foreach ($finishingTotals as $key => $value) {

                    $finishingTotals[$key] += (float) ($finishingMonthly[$period][$key] ?? 0);

                }

                if ($cursor->gte($displayFrom)) {

                    $finishingChart->push([

                        'month' => $cursor->translatedFormat('M Y'),

                        'tomo_in' => round($finishingTotals['tomo_in'], 2),

                        'tomo_out' => round($finishingTotals['tomo_out'], 2),

                        'tomo_saldo' => round($finishingTotals['tomo_in'] - $finishingTotals['tomo_out'], 2),

                        'darto_in' => round($finishingTotals['darto_in'], 2),

                        'darto_out' => round($finishingTotals['darto_out'], 2),

                        'darto_saldo' => round($finishingTotals['darto_in'] - $finishingTotals['darto_out'], 2),

                    ]);

                }

            }

        }



        return view('pages.warehouse.overview', compact(

            'stoks',

            'summary',

            'movement',

            'byJenis',

            'jenisOptions',

            'jenis',

            'search',

            'sortStok',

            'idleDays',

            'finishingChart',

            'finishingTotals'

        ));

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
