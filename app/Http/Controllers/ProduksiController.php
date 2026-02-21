<?php
namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\ProductionTimeline;
use App\Models\Spk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProduksiController extends Controller
{
    //
    public function index()
    {
        $detailPo = DetailPo::with('po')->get();
        $spks     = Spk::all();

        $result = [];

        foreach ($detailPo as $dp) {
            $photo = Cache::remember(
                'photo_dp_' . $dp->id,
                now()->addHours(6),
                fn() => data_get($dp->detail, 'photo')
            );
            Log::info('PHOTO FROM CACHE', [
                'dp_id' => $dp->id,
                'photo' => $photo,
            ]);

            $mappedSpk = [];

            foreach ($spks as $spk) {

                $kategori = $spk->data['kategori'] ?? '-';
                $sup      = $spk->data['sup'] ?? '-';

                foreach ($spk->data['items'] ?? [] as $item) {

                    if (($item['detail_po_id'] ?? null) == $dp->id) {

                        // INIT KATEGORI
                        if (! isset($mappedSpk[$kategori])) {
                            $mappedSpk[$kategori] = [];
                        }

                        // INIT SPK (BIAR TIDAK DOBEL)
                        if (! isset($mappedSpk[$kategori][$spk->id])) {
                            $mappedSpk[$kategori][$spk->id] = [
                                'spk_id' => $spk->id,
                                'no_spk' => $spk->data['no_spk'] ?? '-',
                                'sup'    => $sup,
                                'qty'    => 0,
                            ];
                        }

                        // AKUMULASI QTY
                        $mappedSpk[$kategori][$spk->id]['qty'] += $item['qty'] ?? 0;
                    }
                }
            }

            $result[] = [
                'detail_po' => $dp,
                'spk'       => $mappedSpk,
                'photo'     => $photo, // 👈 TAMBAHKAN INI

            ];
        }

        // dd($result);
        return view('pages.spk.produksi.index', compact('result'));
    }

    public function getByDetail(Request $request, $kategori = null)
    {
        $request->validate([
            'po_id'        => 'required|integer',
            'detail_po_id' => 'required|integer',
            'spk_id'       => 'nullable|string', // JSON string dari JS
        ]);

        $poId     = $request->po_id;
        $detailId = $request->detail_po_id;

        // Decode SPK JSON dari request jika ada
        $spkData = $request->filled('spk_id') ? json_decode($request->spk_id, true) : [];

        // Ambil timeline dari table
        $query = ProductionTimeline::where('po_id', $poId)
            ->where('detail_po_id', $detailId);

        if (! empty($spkData)) {
            $spkIds = [];
            foreach ($spkData as $cat => $spks) {
                foreach ($spks as $spk) {
                    $spkIds[] = $spk['spk_id'] ?? null;
                }
            }
            $spkIds = array_filter($spkIds);
            if ($spkIds) {
                $query->whereIn('spk_id', $spkIds);
            }

        }

        $data = $query->orderBy('date')->get()->map(function ($row) {
            return [
                'id'         => $row->id,
                'type'       => $row->type,
                'qty'        => $row->qty,
                'spk_id'     => $row->spk_id,
                'sup'        => data_get($row->data, 'sup'),
                'date'       => $row->date,
                'remark'     => $row->remark,
                'is_service' => data_get($row->data, 'is_service', 0),
            ];
        });

        // ===== HITUNG TOTAL SPK DARI TABLE SPK =====
        $totalSpkMap = [];
        $spks        = Spk::where('po_id', $poId)->get();

        foreach ($spks as $spk) {
            $spkJson     = $spk->data;
            $kategoriSpk = strtolower($spkJson['kategori'] ?? 'unknown');

            if (! isset($totalSpkMap[$kategoriSpk])) {
                $totalSpkMap[$kategoriSpk] = 0;
            }

            if (! empty($spkJson['items'])) {
                foreach ($spkJson['items'] as $item) {
                    // FILTER berdasarkan detail_po_id
                    if (($item['detail_po_id'] ?? null) == $detailId) {
                        $totalSpkMap[$kategoriSpk] += $item['qty'] ?? 0;
                    }
                }
            }
        }

        // ===== HITUNG KESIMPULAN =====
        // ===== HITUNG KESIMPULAN =====
        $kesimpulan   = [];
        $kategoriList = $data->pluck('sup')->unique()->toArray();

        foreach ($kategoriList as $cat) {
            $inQty      = $data->where('sup', $cat)->where('type', 'in')->sum('qty');
            $outQty     = $data->where('sup', $cat)->where('type', 'out')->sum('qty');
            $serviceOut = $data->where('sup', $cat)->where('type', 'out')->where('is_service', 1)->sum('qty');

            $netIn = $inQty - $serviceOut; // IN bersih = total IN dikurangi service out

            $totalSpk   = $totalSpkMap[$cat] ?? 0;
            $belumMasuk = max(0, $totalSpk - $netIn);

            $kesimpulan[$cat] = [
                'in'          => $inQty - $serviceOut,
                'out'         => $outQty - $serviceOut,
                'service_out' => $serviceOut,          // <--- tambahkan keterangan service
                'net_in'      => $netIn + $serviceOut, // <--- bisa langsung ditampilkan ke user
                'total_spk'   => $totalSpk,
                'belum_masuk' => $belumMasuk,
            ];
        }

        return response()->json([
            'data'       => $data,
            'kesimpulan' => $kesimpulan,
        ]);
    }

    public function store(Request $request)
    {
        // ================= VALIDASI INPUT =================
        $request->validate([
            'po_id'        => 'required|integer',
            'detail_po_id' => 'required|integer',
            'spk_id'       => 'required|integer',
            'type'         => 'required|in:in,out',
            'qty'          => 'required|numeric|min:0.01',
            'date'         => 'required|date',
            'sup'          => 'required|string', // kategori tujuan
            'origin_sup'   => 'nullable|string', // kategori asal (OUT biasa)
            'is_service'   => 'nullable|boolean',
            'remark'       => 'nullable|string',
        ]);

        $sup       = $request->sup;
        $originSup = $request->origin_sup ?? $sup;
        $isService = $request->is_service ?? 0;

        // ================= AMBIL SPK =================
        $spk = Spk::find($request->spk_id);
        if (! $spk) {
            return response()->json(['success' => false, 'message' => 'SPK tidak ditemukan'], 404);
        }

        $spkData = $spk->data;

        // ================= AMBIL ITEM SESUAI DETAIL_PO_ID =================
        $item = collect($spkData['items'] ?? [])->firstWhere('detail_po_id', $request->detail_po_id);
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Item SPK tidak ditemukan'], 404);
        }

        $maxQtySPK = $item['qty'];

        // ================= HITUNG EXISTING PER KATEGORI =================
        $timelineCat = ProductionTimeline::where('spk_id', $request->spk_id)
            ->where('detail_po_id', $request->detail_po_id)
            ->where('data->sup', $sup)
            ->get();

        $totalMasuk   = $timelineCat->where('type', 'in')->sum('qty');
        $totalOut     = $timelineCat->where('type', 'out')->where('is_service', 0)->sum('qty');
        $totalService = $timelineCat->where('type', 'out')->where('is_service', 1)->sum('qty');
        Log::info("Timeline kategori '{$sup}' | IN: {$totalMasuk}, OUT biasa: {$totalOut}, OUT service: {$totalService}, maxQtySPK: {$maxQtySPK}");
        $sisa         = $maxQtySPK - $request->qty;
        $u            = $totalMasuk + $request->qty - $totalService;
        $totalUsedCat = $totalMasuk - $totalOut - $totalService + $request->qty;
        $newTotalIn   = $totalMasuk + $request->qty - $totalService;
        if ($request->type === 'in') {

            $newTotalIn = $totalMasuk + $request->qty - $totalService;

            if ($newTotalIn > $maxQtySPK) {
                return response()->json([
                    'success' => false,
                    'message' => "Qty IN kategori '{$sup}' melebihi limit SPK ({$maxQtySPK}).",
                ], 422);
            }
        }
        //
        if ($request->type === 'out') {

            if (! $isService) {
                $s = $totalOut + $request->qty;
                if ($s > $maxQtySPK) {
                    return response()->json([
                        'success' => false,
                        'message' => "Qty OUT kategori '{$sup}' melebihi limit SPK ({$maxQtySPK}). Sisa: " . max(0, $maxQtySPK - ($totalMasuk + $totalOut + $totalService)),
                    ], 422);
                }

            } else {
                $s = $totalMasuk - $totalService;
                Log::info("service '{$u}'");
                if ( $totalMasuk< $request->qty) {
                    return response()->json([
                        'success' => false,
                        'message' => "Qty OUT kategori '{$sup}' melebihi limit SPK ({$maxQtySPK}). Sisa: " . max(0, $maxQtySPK - ($totalMasuk + $totalOut + $totalService)),
                    ], 422);
                }
            }

        }

        // ================= SIMPAN =================
        ProductionTimeline::create([
            'po_id'        => $request->po_id,
            'detail_po_id' => $request->detail_po_id,
            'spk_id'       => $request->spk_id,
            'type'         => $request->type,
            'qty'          => $request->qty,
            'date'         => $request->date,
            'remark'       => $request->remark,
            'is_service'   => $request->is_service ? 1 : 0,

            'data'         => [
                'sup'        => $sup,
                'is_service' => $isService,
            ],
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $timeline = ProductionTimeline::findOrFail($id);

        $timeline->update([
            'type'   => $request->type,
            'qty'    => $request->qty,
            'spk_id' => $request->spk_id,
            'date'   => $request->date,
            'remark' => $request->remark,
            'data'   => [
                'sup'        => $request->sup,
                'is_service' => $request->is_service ?? 0,
            ],
        ]);

        return response()->json(['success' => true]);
    }

}
