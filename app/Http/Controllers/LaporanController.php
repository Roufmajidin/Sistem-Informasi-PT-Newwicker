<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Spk;
use App\Models\Stok;
use App\Models\TransaksiStok;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\WarehouseHistoryExport;
use Maatwebsite\Excel\Facades\Excel;
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->jenis;

        $stoks = Stok::query()
            ->withSum([
                'transaksi as total_in' => function ($q) {
                    $q->where('tipe', 'in');
                },
            ], 'qty')
            ->withSum([
                'transaksi as total_out' => function ($q) {
                    $q->where('tipe', 'out');
                },
            ], 'qty')
            ->when($jenis, function ($q) use ($jenis) {
                $q->where('jenis', $jenis);
            })
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($stok) {
                $stok->saldo =
                    ($stok->stok_awal ?? 0) +
                    ($stok->total_in ?? 0) -
                    ($stok->total_out ?? 0);

                return $stok;
            });

        return view('pages.laporan.index', compact('stoks'));
    }

    public function warehouseHistory(Request $request)
    {
        $query = TransaksiStok::with(['stok', 'spk']);

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
        // range 
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
        // type 
        if ($request->filled('type')) {

            $query->where('tipe', $request->type);

        }
        // Filter jenis bahan
        if ($request->filled('jenis')) {

            $query->whereHas('stok', function ($q) use ($request) {
                $q->where('jenis', $request->jenis);
            });

        }
        $summaryQuery = clone $query;
        $summary = $summaryQuery
            ->join('stoks', 'stoks.id', '=', 'transaksi_stoks.stok_id')
            ->selectRaw("
        SUM(transaksi_stoks.qty) as total_qty,
        SUM(transaksi_stoks.qty * stoks.harga) as total_value,
        COUNT(*) as total_transaksi
    ")
            ->first();
        $histories = $query
            ->latest('tanggal')
            ->paginate(25)
            ->withQueryString();
        if ($request->ajax()) {
            // dd($histories);

            return view('pages.laporan.partials.history_table', compact('histories', 'summary'))->render();
        }

        return view('pages.laporan.history', compact('histories', 'summary'));
    }

    public function update(Request $request)
    {
        $data = [

            'kode_barang' => $request->kode_barang,

            'nama_barang' => $request->nama_barang,

            'jenis' => $request->jenis,

            'satuan' => $request->satuan,

            'harga' => str_replace('.', '', $request->harga),

            'stok_awal' => $request->stok_awal,

        ];

        if ($request->id) {

            $stok = Stok::findOrFail($request->id);

            $stok->update($data);

        } else {

            $stok = Stok::create($data);

        }

        return response()->json([

            'success' => true,

            'message' => 'Data berhasil disimpan.',

            'id' => $stok->id,

        ]);
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

    public function detailBarang(
        Request $request,
        $id
    ) {
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

            ->orderBy('tanggal', 'desc')

            ->get();

        return view(
            'pages.laporan.detail',
            compact(
                'stok',
                'transaksi'
            )
        );
    }

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

    public function searchSpk(Request $request)
    {
        $keyword = $request->q;
        // dd($request->all());
        $spks = Spk::where('data', 'like', '%' . $keyword . '%')
            ->latest()
            ->take(10)
            ->get();

        $result = [];

        foreach ($spks as $spk) {

            $data = $spk->data;

            $result[] = [
                'id' => $spk->id,
                'no_spk' => $data['no_spk'] ?? '',
                'supplier' => $data['sup'] ?? '',
                'items' => $data['items'] ?? [],
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
        | KPI
        |--------------------------------------------------------------------------
        */

        // $stocks = Stok::with('transaksi')->get();
        $stocks = Stok::all();
        $stockSummary = TransaksiStok::join(
            'stoks',
            'stoks.id',
            '=',
            'transaksi_stoks.stok_id'
        )
            ->select(
                'stok_id',

                DB::raw("
            SUM(CASE
                WHEN tipe='in'
                THEN qty
                ELSE 0
            END) AS total_in_qty
        "),

                DB::raw("
            SUM(CASE
                WHEN tipe='out'
                THEN qty
                ELSE 0
            END) AS total_out_qty
        ")
            )
            ->groupBy('stok_id')
            ->get()
            ->keyBy('stok_id');
        $totalSku = $stocks->count();

        $totalInventoryValue = 0;

        $lowStockCount = 0;

        $emptyStockCount = 0;

        foreach ($stocks as $stok) {

            $summary = $stockSummary->get($stok->id);

            $stokAkhir =
                $stok->stok_awal
                + ($summary->total_in_qty ?? 0)
                - ($summary->total_out_qty ?? 0);

            $totalInventoryValue += $stokAkhir * $stok->harga;

            if ($stokAkhir <= 0) {
                $emptyStockCount++;
            } elseif ($stokAkhir <= 10) {
                $lowStockCount++;
            }
        }

        /*
|--------------------------------------------------------------------------
| Inventory Asset Per Category
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

                $stokAkhir =
                    $item->stok_awal
                    + ($summary->total_in_qty ?? 0)
                    - ($summary->total_out_qty ?? 0);

                /*
                |-----------------------------------------
                | Current Stock
                |-----------------------------------------
                */

                $totalStock += $stokAkhir;

                /*
                |-----------------------------------------
                | Inventory Asset
                |-----------------------------------------
                */

                $inventoryAsset =
                    $stokAkhir * $item->harga;

                $totalInventoryAsset += $inventoryAsset;
                $totalIn += ($summary->total_in_qty ?? 0) * $item->harga;

                $totalOut += ($summary->total_out_qty ?? 0) * $item->harga;

            }

            $categoriesData[] = [

                'name' => $jenis,

                'item_count' => $items->count(),

                'total_stock' => $totalStock,

                'total_value' => $totalInventoryAsset,
                'total_in' => $totalIn,

                'total_out' => $totalOut,
                // 'total' => $totalIn + $totalOut,
                'percentage' =>

                    $totalInventoryValue > 0

                    ? round(
                        ($totalInventoryAsset / $totalInventoryValue) * 100
                    )

                    : 0,

                'color' => match (strtolower($jenis)) {

                    'bahan baku' => '#0d6efd',

                    'bahan penolong' => '#198754',

                    'bahan finishing' => '#fd7e14',

                    default => '#6c757d'

                },

                'bg_soft' => '#f8f9fa',

                'icon' => 'bi-box'

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | Fast Moving Material
        |--------------------------------------------------------------------------
        */

        $topOutgoingMaterials = TransaksiStok::select(

            'stok_id',

            DB::raw('SUM(qty) as total_out_qty'),

            DB::raw('COUNT(*) as out_frequency')

        )

            ->where('tipe', 'out')

            ->groupBy('stok_id')

            ->orderByDesc('total_out_qty')

            ->with('stok')

            ->take(20)

            ->get()

            ->map(function ($row) {

                return (object) [

                    'code' => $row->stok->kode_barang,

                    'name' => $row->stok->nama_barang,

                    'category' => $row->stok->jenis,

                    'location' => '-',

                    'unit' => $row->stok->satuan,

                    'total_out_qty' => $row->total_out_qty,

                    'total_out_value' =>

                        $row->total_out_qty * $row->stok->harga,

                    'out_frequency' =>

                        $row->out_frequency,

                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Stock Movement 6 Months
        |--------------------------------------------------------------------------
        */

        $stockMovement = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = Carbon::now()->subMonths($i);

            $stockIn = TransaksiStok::join(
                'stoks',
                'stoks.id',
                '=',
                'transaksi_stoks.stok_id'
            )
                ->where('tipe', 'in')
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->selectRaw('SUM(qty * harga) as total')
                ->value('total') ?? 0;

            $stockOut = TransaksiStok::join(
                'stoks',
                'stoks.id',
                '=',
                'transaksi_stoks.stok_id'
            )
                ->where('tipe', 'out')
                ->whereYear('tanggal', $date->year)
                ->whereMonth('tanggal', $date->month)
                ->selectRaw('SUM(qty * harga) as total')
                ->value('total') ?? 0;

            $stockMovement[] = [

                'month' => $date->format('M'),

                // simpan angka asli
                'stock_in' => round($stockIn / 1000000, 2),

                'stock_out' => round($stockOut / 1000000, 2),

                // tambahan supaya mudah dibaca
                'stock_in_text' => 'Rp ' . number_format($stockIn / 1000000, 2) . ' Jt',

                'stock_out_text' => 'Rp ' . number_format($stockOut / 1000000, 2) . ' Jt',

                'difference' => round(($stockIn - $stockOut) / 1000000, 2),

                'status' => $stockIn >= $stockOut
                    ? 'Surplus'
                    : 'Defisit',

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | Summary By Unit
        |--------------------------------------------------------------------------
        */

        $unitSummaries = [];

        foreach ($stocks->groupBy('satuan') as $unit => $items) {

            $qty = 0;

            $value = 0;

            foreach ($items as $item) {

                $summary = $stockSummary->get($item->id);

                $stokAkhir =
                    $item->stok_awal
                    + ($summary->total_in_qty ?? 0)
                    - ($summary->total_out_qty ?? 0);

                $qty += $stokAkhir;

                $value += $stokAkhir * $item->harga;

            }

            $unitSummaries[] = [

                'unit' => $unit,

                'total_items' => $items->count(),

                'total_stock' => $qty,

                'inventory_value' => $value,

            ];

        }

        return view(

            'pages.laporan.overview',

            compact(

                'totalSku',

                'totalInventoryValue',

                'lowStockCount',

                'emptyStockCount',

                'categoriesData',

                'topOutgoingMaterials',

                'stockMovement',

                'unitSummaries'

            )

        );

    }
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



}
