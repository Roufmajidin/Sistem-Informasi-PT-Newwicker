<?php
namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\Po;
use App\Models\Spk;
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

            // skip baris kosong
            if (empty($item['detail_id'])) {
                continue;
            }

            $detailId = $item['detail_id'];

            // =============================
            // 1️⃣ AMBIL DETAIL PO
            // =============================
            $detailPo = DetailPo::find($detailId);

            if (! $detailPo) {
                return response()->json([
                    'success' => false,
                    'message' => "Detail PO ID {$detailId} tidak ditemukan",
                ], 404);
            }

            // qty PO ADA DI JSON detail->qty
            $qtyPo = (int) data_get($detailPo->detail, 'qty', 0);

            if ($qtyPo <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Qty PO kosong untuk Detail PO ID {$detailId}",
                ], 422);
            }

            // =============================
            // 2️⃣ QTY REQUEST
            // =============================
            $qtyRequest = 0;

            if (($item['satuan'] ?? '') === 'pcs') {
                $qtyRequest = (int) $item['pcs'];
            } elseif (($item['satuan'] ?? '') === 'set') {
                $qtyRequest = (int) $item['set'];
            }

            if ($qtyRequest <= 0) {
                continue;
            }

            // =============================
            // 3️⃣ TOTAL QTY SPK YANG SUDAH ADA
            // =============================
            $qtySpkExisting = Spk::where('po_id', $poId)
                ->where('detail_po_id', $detailId)
                ->where('data->kategori', $kategori)
                ->sum(DB::raw("JSON_EXTRACT(data, '$.qty')"));

            // =============================
            // 4️⃣ VALIDASI OVER QTY
            // =============================
            if (($qtySpkExisting + $qtyRequest) > $qtyPo) {
                return response()->json([
                    'success' => false,
                    'message' => "Kategori {$kategori} untuk Detail PO ID {$detailId} melebihi qty PO",
                    'detail' => [
                        'qty_po'      => $qtyPo,
                        'qty_spk'     => $qtySpkExisting,
                        'qty_request' => $qtyRequest,
                        'sisa_qty'    => $qtyPo - $qtySpkExisting,
                    ],
                ], 422);
            }

            // =============================
            // 5️⃣ INSERT BARU (BUKAN UPDATE)
            // =============================
            $itemImages = [];

            foreach ($item['images'] ?? [] as $img) {
                $itemImages[] = $this->saveBase64Image($img, 'spk/items');
            }

            $noteImages = [];

            foreach (($item['catatan']['images'] ?? []) as $img) {
                $noteImages[] = $this->saveBase64Image($img, 'spk/notes');
            }
            Spk::create([
                'po_id'        => $poId,
                'detail_po_id' => $detailId,
                'data'         => [
                    'kategori' => $kategori,
                    'qty'      => $qtyRequest,

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
        }

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil disimpan',
        ]);
    }

}
