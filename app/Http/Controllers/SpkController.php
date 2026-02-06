<?php
namespace App\Http\Controllers;

use App\Models\Po;
use Carbon\Carbon;

class SpkController extends Controller
{
    //
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
    $now   = Carbon::now();
    $year  = $now->format('y');   // 25
    $month = $now->format('m');   // 12
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
        if (!empty($detail['photo'])) {
            $images[] = $detail['photo'];
        }
        // dd
        return [
            'kode'     => $detail['no_']     ?? '-',
            'nama'     => $detail['description']  ?? '-',
            'p'        => $detail['item_w']  ?? '-',
            'l'        => $detail['item_d']  ?? '-',
            't'        => $detail['item_h']  ?? '-',
            'material' => $detail['composition'] ?? '-',
            'pcs'      => $detail['qty']     ?? 0,
            'set'      => $detail['set']     ?? 0,
            'harga'    => $detail['harga']  ?? 0,
            'catatan'  => $d->remark_update ?? '',
            'images'   => $images, // multi image ready
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
        'items'       => $items
    ];

    return view('pages.spk.index', compact('spk'));
}
}
