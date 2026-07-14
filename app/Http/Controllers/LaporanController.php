<?php
namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\Stok;
use App\Models\TransaksiStok;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
class LaporanController extends Controller
{
   public function index(Request $request)
{
    $jenis = $request->jenis;

    $stoks = Stok::query()
        ->withSum([
            'transaksi as total_in' => function ($q) {
                $q->where('tipe', 'in');
            }
        ], 'qty')
        ->withSum([
            'transaksi as total_out' => function ($q) {
                $q->where('tipe', 'out');
            }
        ], 'qty')
        ->when($jenis, function ($q) use ($jenis) {
            $q->where('jenis', $jenis);
        })
        ->orderBy('nama_barang')
        ->get();

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

    $histories = $query
        ->latest('tanggal')
        ->paginate(25)
        ->withQueryString();

    if ($request->ajax()) {
            // dd($histories);

        return view('pages.laporan.partials.history_table', compact('histories'))->render();
    }

    return view('pages.laporan.history', compact('histories'));
}
   public function update(Request $request)
{
    $data = [

        'kode_barang' => $request->kode_barang,

        'nama_barang' => $request->nama_barang,

        'jenis'       => $request->jenis,

        'satuan'      => $request->satuan,

        'harga'       => str_replace('.', '', $request->harga),

        'stok_awal'   => $request->stok_awal,

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
        'message' => 'Data berhasil dihapus'
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
            'stok'      => $stok,
            'transaksi' => $transaksi,
        ]);
    }
  public function detailBarang(
    Request $request,
    $id
)
{
    $stok = Stok::findOrFail($id);

    $transaksi = TransaksiStok::where(
            'stok_id',
            $id
        )

        ->when(
            $request->tanggal_awal,
            fn($q)=>
                $q->whereDate(
                    'tanggal',
                    '>=',
                    $request->tanggal_awal
                )
        )

        ->when(
            $request->tanggal_akhir,
            fn($q)=>
                $q->whereDate(
                    'tanggal',
                    '<=',
                    $request->tanggal_akhir
                )
        )

        ->orderBy('tanggal','desc')

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
            fn($q)=>
                $q->whereDate(
                    'tanggal',
                    '>=',
                    $request->tanggal_awal
                )
        )

        ->when(
            $request->tanggal_akhir,
            fn($q)=>
                $q->whereDate(
                    'tanggal',
                    '<=',
                    $request->tanggal_akhir
                )
        )

        ->orderBy('tanggal')
        ->get();

    $totalIn = $transaksi
        ->where('tipe','in')
        ->sum('qty');

    $totalOut = $transaksi
        ->where('tipe','out')
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
        'laporan-stok-'.$stok->kode_barang.'.pdf'
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
                'stok_id'    => $request->stok_id,
                'tanggal'    => $request->tanggal,
                'tipe'       => 'in',
                'qty'        => $request->in,
                'po'         => $request->po,
                'spk_id'     => $request->spk_id,

                'keterangan' => $request->keterangan,
            ]);
        }

        if ($request->out > 0) {

            TransaksiStok::create([
                'stok_id'    => $request->stok_id,
                'tanggal'    => $request->tanggal,
                'tipe'       => 'out',
                'qty'        => $request->out,
                'po'         => $request->po,
                'spk_id'     => $request->spk_id,
                'keterangan' => $request->keterangan,
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

            $data = $spk->data;;

            $result[] = [
                'id'       => $spk->id,
                'no_spk'   => $data['no_spk'] ?? '',
                'supplier' => $data['sup'] ?? '',
                'items'    => $data['items'] ?? [],
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

}
