<?php
namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Po;
use App\Models\Spk;
use App\Models\SpkTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpkController extends Controller
{
    //
    private function saveBase64Image($base64, $folder = 'spk')
    {
        if (! str_starts_with($base64, 'data:image')) {
            return $base64; // sudah URL
        }

        preg_match('/data:image\/(.*?);base64,/', $base64, $match);
        $extension = $match[1] ?? 'png';

        $image = substr($base64, strpos($base64, ',') + 1);
        $image = base64_decode($image);

        $filename = $folder . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename);
    }

    public function show($id)
    {
        $spk = [
            'no_spk'      => '25-1254/NW 25-81/12/2025',
            'no_po'       => 'NW 25-81',
            'nama'        => 'PAK HERI',
            'tgl_terima'  => '22-Dec-25',
            'tgl_selesai' => '',
            'items'       => [
                [
                    'kode'     => '17744',
                    'gambar'   => '/storage/spk/chair.jpg',
                    'nama'     => 'ELEGANT SLIMIT CHAIR',
                    'ukuran'   => [62, 75, 97],
                    'material' => 'Rattan Frame',
                    'qty_pcs'  => 70,
                    'qty_set'  => '',
                    'harga'    => 'Rp',
                    'total'    => '-',
                    'catatan'  => '',
                ],
            ],
        ];

        return view('spk.show', compact('spk'));
    }

    public function index($id)
    {
        // =========================
        // AMBIL PO + DETAIL
        // =========================
        $po = Po::with('details')->findOrFail($id);

        // =========================
        // GENERATE NO SPK
        // format: 25-0001/NW {NO_PO}/{bulan}/{tahun}
        // =========================
        $now      = Carbon::now();
        $year     = $now->format('y'); // 25
        $month    = $now->format('m'); // 12
        $yearFull = $now->format('Y');

        $noSpkUrut = str_pad($po->id, 4, '0', STR_PAD_LEFT);

        $noSpk = "{$year}-{$noSpkUrut}/NW {$po->order_no}/{$month}/{$yearFull}";

        // =========================
        // MAPPING ITEMS DARI PO DETAILS
        // =========================
        // dd($po->details);
        $items = $po->details->map(function ($d) {

            $detail = $d->detail;
            // FOTO: bisa string / array
            $images = [];
            if (! empty($detail['photo'])) {
                $images[] = $detail['photo'];
            }
            return [
                'kode'      => $detail['article_nr_'] ?? '-',
                'detail_id' => $d['id'] ?? '-',
                'nama'      => $detail['description'] ?? '-',
                'p'         => $detail['item_w'] ?? '-',
                'l'         => $detail['item_d'] ?? '-',
                't'         => $detail['item_h'] ?? '-',
                'material'  => $detail['composition'] ?? '-',
                'pcs'       => $detail['qty'] ?? 0,
                'set'       => $detail['set'] ?? 0,
                'harga'     => $detail['harga'] ?? 0,
                'catatan'   => $d->remark_update ?? '',
                'images'    => $images, // multi image ready
            ];
        })->values();

        // =========================
        // DATA SPK FINAL
        // =========================
        $spk = [
            'id'          => $po->id,
            'no_spk'      => $noSpk,
            'no_po'       => $po->order_no,
            'nama'        => $po->supplier_name ?? '-',
            'tgl_terima'  => optional($po->created_at)->format('d-M-Y'),
            'tgl_selesai' => '-',
            'type'        => 'rangka', // default / bisa dari DB
            'items'       => $items,
        ];
        // dd($spk);

        return view('pages.spk.index', compact('spk'));
    }

    public function save($poId, Request $request)
    {
        $kategori = $request->spk_type;
        $items    = $request->items ?? [];

        if (! $kategori || empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak lengkap',
            ], 422);
        }

        foreach ($items as $item) {

            if (empty($item['detail_id'])) {
                continue;
            }

            $detailId = $item['detail_id'];

            $detailPo = DetailPo::find($detailId);

            if (! $detailPo) {
                return response()->json([
                    'success' => false,
                    'message' => "Detail PO ID {$detailId} tidak ditemukan",
                ], 404);
            }

            $qtyPo = (int) data_get($detailPo->detail, 'qty', 0);

            if ($qtyPo <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Qty PO kosong untuk Detail PO ID {$detailId}",
                ], 422);
            }

            // ================= QTY REQUEST
            $qtyRequest = 0;

            if (($item['satuan'] ?? '') === 'pcs') {
                $qtyRequest = (int) $item['pcs'];
            } elseif (($item['satuan'] ?? '') === 'set') {
                $qtyRequest = (int) $item['set'];
            }

            if ($qtyRequest <= 0) {
                continue;
            }

            // ================= VALIDASI QTY
            $qtySpkExisting = Spk::where('po_id', $poId)
                ->where('detail_po_id', $detailId)
                ->where('data->kategori', $kategori)
                ->sum(DB::raw("JSON_EXTRACT(data, '$.qty')"));

            if (($qtySpkExisting + $qtyRequest) > $qtyPo) {
                return response()->json([
                    'success' => false,
                    'message' => "Kategori {$kategori} untuk Detail PO ID {$detailId} melebihi qty PO",
                ], 422);
            }

            // ================= SAVE IMAGE ITEM
            $itemImages = [];

            foreach ($item['images'] ?? [] as $img) {
                $itemImages[] = $this->saveBase64Image($img, 'spk/items');
            }

            // ================= SAVE IMAGE NOTE
            $noteImages = [];

            foreach (($item['catatan']['images'] ?? []) as $img) {
                $noteImages[] = $this->saveBase64Image($img, 'spk/notes');
            }

            // ================= CREATE SPK
            $spk = Spk::create([
                'po_id'        => $poId,
                'detail_po_id' => $detailId,
                'data'         => [
                    'kategori' => $kategori,
                    'qty'      => $qtyRequest,
                    'sup'      => $request->nama,
                    'no_po'    => $request->no_po,
                    'no_spk'   => $request->no_spk,

                    'item'     => [
                         ...$item,
                        'images'  => $itemImages,
                        'catatan' => [
                            'remark' => $item['catatan']['remark'] ?? '',
                            'images' => $noteImages,
                        ],
                    ],
                ],
                'created_by'   => auth()->id(),
            ]);

            // ==============================
            // ⭐ CREATE TIMELINE
            // ==============================
            $userName = auth()->user()->name ?? 'Unknown';

            SpkTimeline::create([
                'spk_id' => $spk->id,
                'data'   => [
                    'remark' => "{$userName} added SPK ID {$request->no_spk} item {$item['kode']}",
                    'type' => 'create',
                    'user_id' => auth()->id(),
                    'time' => now(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil disimpan',
        ]);
    }

// ItemController.php
    public function search(Request $request)
    {
        $q = trim($request->q);

        if (! $q) {
            return [];
        }

        return DetailPo::where(function ($query) use ($q) {
            $query->where('detail->article_nr_', 'like', "%{$q}%")
                ->orWhere('detail->description', 'like', "%{$q}%");
        })
            ->limit(10)
            ->get()
            ->map(function ($row) {

                $detail = $row->detail ?? [];
                $images = [];
                if (! empty($detail['photo'])) {
                    $images[] = $detail['photo'];
                }
                return [
                    'detail_id' => $row->id,
                    'kode'      => data_get($detail, 'article_nr_'),
                    'nama'      => data_get($detail, 'description'),
                    'p'         => (float) data_get($detail, 'item_w'),
                    'l'         => (float) data_get($detail, 'item_d'),
                    't'         => (float) data_get($detail, 'item_h'),
                    'material'  => data_get($detail, 'composition'),
                    'qty'       => (int) data_get($detail, 'qty'),
                    'photo'     => data_get($detail, 'photo'),
                    'images'    => $images, // multi image ready

                ];
            });
    }

    // timeline spk
        public function tima()
    {
        $timeline = SpkTimeline::latest()
            ->limit(50)
            ->get()
            ->map(function ($row) {

                $data = $row->data ?? [];

                return [
                    'id'         => $row->id,
                    'spk_id'     => $row->spk_id,
                    'remark'     => $data['remark'] ?? '-',
                    'type'       => $data['type'] ?? 'info',
                    'created_at' => $row->created_at,
                ];
            });

        return response()->json($timeline);
    }

}
