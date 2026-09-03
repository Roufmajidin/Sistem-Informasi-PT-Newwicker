<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\JenisSupplier;
use App\Models\Karyawan;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestApproval;
use App\Models\PaymentRequestSaved;
use App\Models\PaymentRequestSignature;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\SignatureSpk;
use App\Models\Spk;
use App\Models\SpkTimeline;
use App\Models\Supplier;
use App\Models\TransaksiStok;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\ExportPengajuanSpk;
use App\Exports\ExportAllPaymentRequest;
use App\Models\Kredit;
use App\Helpers\ExportSpks;
class SpkController extends Controller
{
    //
    public function delete($id)
    {
        $spk = Spk::find($id);
        if (!$spk) {
            return response()->json([
                'message' => 'SPK tidak ditemukan',
            ], 404);
        }
        $spk->delete();

        return response()->json([
            'message' => 'SPK berhasil dihapus',
        ]);
    }

    private function saveBase64Image($base64, $folder = 'spk')
    {
        if (!str_starts_with($base64, 'data:image')) {
            return $base64;
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
            'no_spk' => '25-1254/NW 25-81/12/2025',
            'no_po' => 'NW 25-81',
            'nama' => 'PAK HERI',
            'tgl_terima' => '22-Dec-25',
            'tgl_selesai' => '',
            'items' => [
                [
                    'kode' => '17744',
                    'gambar' => '/storage/spk/chair.jpg',
                    'nama' => 'ELEGANT SLIMIT CHAIR',
                    'ukuran' => [62, 75, 97],
                    'material' => 'Rattan Frame',
                    'qty_pcs' => 70,
                    'qty_set' => '',
                    'harga' => 'Rp',
                    'total' => '-',
                    'catatan' => '',
                ],
            ],
        ];

        return view('spk.show', compact('spk'));
    }

   public function index(Request $request, $id)
{
    $viewOnly = $request->is('spk/views/*');

    $bahanBaku = collect();

    $mode = (
        $request->routeIs('spk.edit') ||
        $request->routeIs('spk.view')
    )
        ? 'edit'
        : 'create';

    $jenisSuppliers = JenisSupplier::orderBy('name')->get();

    // =====================================================
    // EDIT MODE
    // =====================================================
    if ($mode === 'edit') {

        $spkModel = Spk::findOrFail($id);

        // =================================================
        // BAHAN BAKU
        // =================================================
        $bahanBaku = TransaksiStok::with('stok')
            ->where('spk_id', $spkModel->id)
            ->where('tipe', 'out')
            ->orderBy('tanggal')
            ->get();

        // =================================================
        // DATA SPK
        // =================================================
        $data = $spkModel->data ?? [];

        // Pastikan data array
        if (!is_array($data)) {
            $data = [];
        }

        // =================================================
        // SIGNATURE APPROVAL SPK
        // =================================================
        $signature = SignatureSpk::with([
            'madeBy.karyawan.divisi',
            'checkedBy.karyawan.divisi',
            'checkedBy2.karyawan.divisi',
            'approvedBy.karyawan.divisi',
            'supplier',
        ])
            ->where('spk_id', $spkModel->id)
            ->first();

        // =================================================
        // PAYMENT REQUEST
        // =================================================
        $paymentRequest = PaymentRequest::where(
            'spk_id',
            $spkModel->id
        )
            ->latest()
            ->first();

        // =================================================
        // ITEMS
        // =================================================
        $items = collect(
            $data['items'] ?? []
        )->map(function ($item) {

            // Pastikan item array
            if (!is_array($item)) {
                $item = [];
            }

            // =================================================
            // SATUAN
            // =================================================
            $satuan = strtolower(
                trim(
                    (string) (
                        $item['satuan'] ?? 'pcs'
                    )
                )
            );

            if ($satuan === '') {
                $satuan = 'pcs';
            }

            // =================================================
            // QTY
            //
            // PENTING:
            // Jangan lagi memaksa KG menjadi 0.
            //
            // Semua satuan selain SET menggunakan qty
            // pada kolom quantity utama (.pcs) di Blade.
            //
            // Contoh:
            // qty = 2.5
            // satuan = kg
            //
            // maka:
            // pcs = 2.5
            // satuan = kg
            // =================================================
            $qty = $item['qty'] ?? 0;

            // Handle string decimal Indonesia
            if (is_string($qty)) {

                $qty = trim($qty);

                if ($qty !== '') {

                    // 2,5 -> 2.5
                    if (
                        str_contains($qty, ',') &&
                        !str_contains($qty, '.')
                    ) {
                        $qty = str_replace(',', '.', $qty);
                    }

                    $qty = (float) $qty;
                } else {
                    $qty = 0;
                }
            } else {
                $qty = (float) $qty;
            }

            // =================================================
            // SET
            // =================================================
            $setQty = 0;

            if ($satuan === 'set') {
                $setQty = $qty;
            }

            // =================================================
            // PCS / KG / CUSTOM UNIT
            //
            // Quantity utama tetap dikirim melalui "pcs"
            // karena Blade existing membaca:
            //
            // {{ $item['pcs'] }}
            //
            // dan JavaScript existing juga menggunakan
            // .pcs sebagai quantity utama untuk selain SET.
            // =================================================
            $mainQty = $satuan === 'set'
                ? 0
                : $qty;

            return [

                // =================================================
                // DETAIL PO
                // =================================================
                'detail_id' =>
                    $item['detail_po_id']
                    ?? $item['detail_id']
                    ?? null,

                // =================================================
                // IDENTITAS ITEM
                // =================================================
                'kode' =>
                    $item['kode']
                    ?? '-',

                'nama' =>
                    $item['nama']
                    ?? '-',

                // =================================================
                // CUSTOM VALUE
                // =================================================
                'custom_columns' =>
                    $item['custom_columns']
                    ?? [],

                // =================================================
                // QUANTITY UTAMA
                // PCS / KG / CUSTOM UNIT
                // =================================================
                'pcs' =>
                    $mainQty,

                // =================================================
                // SET
                // =================================================
                'set' =>
                    $setQty,

                // =================================================
                // SATUAN ASLI
                // =================================================
                'satuan' =>
                    $item['satuan']
                    ?? 'pcs',

                // =================================================
                // HARGA
                // =================================================
                'harga' =>
                    $item['harga']
                    ?? 0,

                // =================================================
                // TOTAL
                // =================================================
                'total' =>
                    $item['total']
                    ?? 0,

                // =================================================
                // IMAGE
                // =================================================
                'images' =>
                    $item['images']
                    ?? [],

                // =================================================
                // CATATAN
                // =================================================
                'catatan' =>
                    $item['catatan']
                    ?? [],

                // =================================================
                // DIMENSI
                // =================================================
                'p' =>
                    $item['p']
                    ?? '-',

                'l' =>
                    $item['l']
                    ?? '-',

                't' =>
                    $item['t']
                    ?? '-',

                // =================================================
                // MATERIAL
                // =================================================
                'material' =>
                    $item['material']
                    ?? '-',
            ];
        })->values();

        // =====================================================
        // FINAL DATA EDIT
        // =====================================================
        $spk = [

            // =================================================
            // SIGNATURE
            // =================================================
            'signature' =>
                $signature,

            // =================================================
            // ID
            // =================================================
            'id' =>
                $spkModel->id,

            // =================================================
            // STATUS
            // =================================================
            'status' =>
                $spkModel->status
                ?? 'draft',

            // =================================================
            // PAYMENT REQUEST STATUS
            // =================================================
            'request_status' =>
                $paymentRequest->status
                ?? null,

            // =================================================
            // NO SPK
            // =================================================
            'no_spk' =>
                $data['no_spk']
                ?? '-',

            // =================================================
            // NO PO
            // =================================================
            'no_po' =>
                $data['no_po']
                ?? '-',

            // =================================================
            // SUPPLIER
            // =================================================
            'nama' =>
                $data['sup']
                ?? '-',

            // =================================================
            // TANGGAL TERIMA
            // =================================================
            'tgl_terima' =>
                $data['tgl_terima']
                ?? null,

            // =================================================
            // TANGGAL SELESAI
            // =================================================
            'tgl_selesai' =>
                $data['tgl_selesai']
                ?? null,

            // =================================================
            // KATEGORI
            // =================================================
            'type' =>
                $data['kategori']
                ?? '-',

            // =================================================
            // ITEMS
            // =================================================
            'items' =>
                $items,

            // =================================================
            // MODE
            // =================================================
            'mode' =>
                'edit',

            // =================================================
            // PAYMENTS
            // =================================================
            'payments' =>
                $data['payments']
                ?? [],

            // =================================================
            // CHECKED TYPES
            // =================================================
            'checked_types' =>
                $data['checked_types']
                ?? [],

            // =================================================
            // CUSTOM HEADERS
            // =================================================
            'custom_headers' =>
                $data['custom_headers']
                ?? [],

            // =================================================
            // PPN
            // =================================================
            'ppn_enabled' =>
                (bool) (
                    $data['ppn_enabled']
                    ?? false
                ),

            'ppn_rate' =>
                (float) (
                    $data['ppn_rate']
                    ?? 11
                ),
        ];
    }

    // =====================================================
    // CREATE MODE
    // =====================================================
    else {

        $po = Po::with('details')
            ->findOrFail($id);

        // =================================================
        // GENERATE NO SPK
        // =================================================
        $noSpk =
            $this->generateNoSpk(
                $po->order_no
            );

        // =================================================
        // ITEMS DARI PO
        // =================================================
    // =================================================
// ITEMS DARI PO
// =================================================
$items = $po->details->map(function ($d) {

    $detail = $d->detail;

    if (!is_array($detail)) {
        $detail = [];
    }

    // =================================================
    // IMAGE
    // =================================================
    $images = [];

    if (!empty($detail['photo'])) {
        $images[] = $detail['photo'];
    }

    // =================================================
    // SATUAN DEFAULT CREATE
    // =================================================
    $satuan = 'pcs';

    // =================================================
    // QTY DARI PO
    // =================================================
    $qty = $detail['qty'] ?? 0;

    if (is_string($qty)) {

        $qty = trim($qty);

        if ($qty === '') {

            $qty = 0;

        } elseif (
            str_contains($qty, ',') &&
            !str_contains($qty, '.')
        ) {

            // 56,5 -> 56.5
            $qty = str_replace(
                ',',
                '.',
                $qty
            );

        } elseif (
            str_contains($qty, ',') &&
            str_contains($qty, '.')
        ) {

            // 1.250,5 -> 1250.5
            if (
                strrpos($qty, ',') >
                strrpos($qty, '.')
            ) {

                $qty = str_replace(
                    '.',
                    '',
                    $qty
                );

                $qty = str_replace(
                    ',',
                    '.',
                    $qty
                );

            } else {

                // 1,250.5 -> 1250.5
                $qty = str_replace(
                    ',',
                    '',
                    $qty
                );
            }

        } elseif (
            substr_count($qty, '.') > 1
        ) {

            // 1.250.000 -> 1250000
            $qty = str_replace(
                '.',
                '',
                $qty
            );
        }

        $qty = is_numeric($qty)
            ? (float) $qty
            : 0;

    } else {

        $qty = is_numeric($qty)
            ? (float) $qty
            : 0;
    }

    // =================================================
    // PCS / SET
    // =================================================
    $mainQty = $qty;

    $setQty = 0;

    // =================================================
    // RETURN ITEM
    // =================================================
    return [

        'detail_id' =>
            $d->id ?? null,

        'kode' =>
            $detail['article_nr_']
            ?? '-',

        'nama' =>
            $detail['description']
            ?? '-',

        'custom_columns' =>
            [],

        // QTY ASLI
        'qty' =>
            $qty,

        // QUANTITY UTAMA
        'pcs' =>
            $mainQty,

        // SET
        'set' =>
            $setQty,

        // SATUAN
        'satuan' =>
            $satuan,

        'harga' =>
            $detail['harga']
            ?? 0,

        'total' =>
            0,

        'images' =>
            $images,

        'catatan' =>
            $d->remark_update
            ?? '',

        'p' =>
            $detail['item_w']
            ?? '-',

        'l' =>
            $detail['item_d']
            ?? '-',

        't' =>
            $detail['item_h']
            ?? '-',

        'material' =>
            $detail['composition']
            ?? '-',
    ];

})->values();

        // =====================================================
        // FINAL DATA CREATE
        // =====================================================
        $spk = [

            'id' =>
                $po->id,

            'status' =>
                'draft',

            'request_status' =>
                null,

            'no_spk' =>
                $noSpk,

            'no_po' =>
                $po->order_no,

            'nama' =>
                $po->supplier_name
                ?? '-',

            'tgl_terima' =>
                now()->format('Y-m-d'),

            'tgl_selesai' =>
                $request->tgl_selesai,

            'type' =>
                'rangka',

            'items' =>
                $items,

            'payments' =>
                [],

            'mode' =>
                'create',

            'checked_types' =>
                [],

            'custom_headers' =>
                [],

            'ppn_enabled' =>
                false,

            'ppn_rate' =>
                11,
        ];
    }

    // =====================================================
    // RETURN VIEW
    // =====================================================
    return view(
        'pages.spk.index',
        compact(
            'spk',
            'jenisSuppliers',
            'viewOnly',
            'bahanBaku'
        )
    );
}

    /**
     * =========================================================
     * ASSIGN SPK
     * =========================================================
     *
     * Menampilkan seluruh SPK yang masih memiliki
     * approval/signature yang belum lengkap.
     *
     * URL:
     *
     * /spk/assign
     *
     * Blade:
     *
     * pages.spk.assign
     */
    public function assign(Request $request)
    {
        // =====================================================
        // MASTER SUPPLIER TYPE
        // =====================================================

        $jenisSuppliers = JenisSupplier::orderBy('name')
            ->get();


        // =====================================================
        // AMBIL SIGNATURE YANG BELUM LENGKAP
        // =====================================================

        $signatures = SignatureSpk::with([
            'madeBy.karyawan.divisi',
            'checkedBy.karyawan.divisi',
            'checkedBy2.karyawan.divisi',
            'approvedBy.karyawan.divisi',
            'supplier',
        ])
            ->where(function ($query) {

                $query
                    ->whereNull('made_at')
                    ->orWhereNull('checked_at')
                    ->orWhereNull('checked_at_2')
                    ->orWhereNull('approved_at');

            })
            ->orderBy('id')
            ->get();


        // =====================================================
        // KALAU TIDAK ADA SPK
        // =====================================================

        if ($signatures->isEmpty()) {

            return view(
                'pages.spk.assign',
                [
                    'spks' => collect(),
                    'jenisSuppliers' => $jenisSuppliers,
                ]
            );
        }


        // =====================================================
        // AMBIL SEMUA SPK SEKALIGUS
        //
        // Hindari:
        //
        // foreach
        //     Spk::find(...)
        //
        // karena menyebabkan N+1 query.
        // =====================================================

        $spkIds = $signatures
            ->pluck('spk_id')
            ->filter()
            ->unique()
            ->values();


        $spkModels = Spk::whereIn(
            'id',
            $spkIds
        )
            ->get()
            ->keyBy('id');


        // =====================================================
        // PAYMENT REQUEST SEKALIGUS
        // =====================================================

        $paymentRequests = PaymentRequest::whereIn(
            'spk_id',
            $spkIds
        )
            ->latest('id')
            ->get()
            ->groupBy('spk_id');


        // =====================================================
        // BAHAN BAKU SEKALIGUS
        //
        // Dipertahankan supaya struktur data assign
        // tetap bisa dikembangkan seperti index SPK.
        // =====================================================

        $bahanBaku = TransaksiStok::with('stok')
            ->whereIn(
                'spk_id',
                $spkIds
            )
            ->where('tipe', 'out')
            ->orderBy('tanggal')
            ->get()
            ->groupBy('spk_id');


        // =====================================================
        // BUILD DATA SPK
        // =====================================================

        $spks = collect();


        foreach ($signatures as $signature) {

            $spkModel =
                $spkModels->get(
                    $signature->spk_id
                );


            // SPK tidak ditemukan
            if (!$spkModel) {
                continue;
            }


            // =================================================
            // DATA JSON SPK
            // =================================================

            $data = $spkModel->data ?? [];

            if (!is_array($data)) {
                $data = [];
            }


            // =================================================
            // PAYMENT TERBARU
            // =================================================

            $paymentRequest =
                $paymentRequests
                    ->get($spkModel->id)
                        ?->first();


            // =================================================
            // ITEMS
            // =================================================

            $items = collect(
                $data['items'] ?? []
            )
                ->map(function ($item) {

                    if (!is_array($item)) {
                        $item = [];
                    }

                    $satuan =
                        $item['satuan']
                        ?? 'pcs';


                    return [

                        'detail_id' =>
                            $item['detail_po_id']
                            ?? null,


                        'kode' =>
                            $item['kode']
                            ?? '-',


                        'nama' =>
                            $item['nama']
                            ?? '-',


                        // CUSTOM COLUMN
                        'custom_columns' =>
                            $item['custom_columns']
                            ?? [],


                        // QTY PCS
                        'pcs' =>
                            $satuan === 'pcs'
                            ? ($item['qty'] ?? 0)
                            : 0,


                        // QTY SET
                        'set' =>
                            $satuan === 'set'
                            ? ($item['qty'] ?? 0)
                            : 0,


                        'qty' =>
                            $item['qty']
                            ?? 0,


                        'harga' =>
                            $item['harga']
                            ?? 0,


                        'total' =>
                            $item['total']
                            ?? 0,


                        'satuan' =>
                            $satuan,


                        'images' =>
                            $item['images']
                            ?? [],


                        'catatan' =>
                            $item['catatan']
                            ?? [],


                        'p' =>
                            $item['p']
                            ?? '-',


                        'l' =>
                            $item['l']
                            ?? '-',


                        't' =>
                            $item['t']
                            ?? '-',


                        'material' =>
                            $item['material']
                            ?? '-',

                    ];

                })
                ->values();


            // =================================================
            // TANGGAL
            //
            // NORMALISASI DI CONTROLLER
            // =================================================

            $tglTerima =
                $this->parseTanggalIndonesia(
                    $data['tgl_terima']
                    ?? null
                );


            $tglSelesai =
                $this->parseTanggalIndonesia(
                    $data['tgl_selesai']
                    ?? null
                );


            // =================================================
            // STATUS SIGNATURE
            // =================================================

            $signatureStatus = [

                'made' => [
                    'done' =>
                        !empty($signature->made_at),

                    'date' =>
                        $signature->made_at,
                ],


                'checked' => [
                    'done' =>
                        !empty($signature->checked_at),

                    'date' =>
                        $signature->checked_at,
                ],


                'checked_2' => [
                    'done' =>
                        !empty($signature->checked_at_2),

                    'date' =>
                        $signature->checked_at_2,
                ],


                'approved' => [
                    'done' =>
                        !empty($signature->approved_at),

                    'date' =>
                        $signature->approved_at,
                ],

            ];


            // =================================================
            // HITUNG JUMLAH SIGNATURE
            // =================================================

            $totalSignature = 4;

            $signedSignature =
                collect($signatureStatus)
                    ->filter(
                        fn($item) =>
                        $item['done'] === true
                    )
                    ->count();


            $pendingSignature =
                $totalSignature -
                $signedSignature;


            // =================================================
            // TENTUKAN NEXT APPROVAL
            // =================================================

            $nextApproval = null;


            if (!$signature->made_at) {

                $nextApproval = 'made';

            } elseif (!$signature->checked_at) {

                $nextApproval = 'checked';

            } elseif (!$signature->checked_at_2) {

                $nextApproval = 'checked_2';

            } elseif (!$signature->approved_at) {

                $nextApproval = 'approved';

            }


            // =================================================
            // DATA FINAL
            // =================================================

            $spks->push([

                // ---------------------------------------------
                // BASIC
                // ---------------------------------------------

                'id' =>
                    $spkModel->id,


                'spk_id' =>
                    $spkModel->id,


                'signature_id' =>
                    $signature->id,


                'signature' =>
                    $signature,


                // ---------------------------------------------
                // STATUS
                // ---------------------------------------------

                'status' =>
                    $spkModel->status
                    ?? 'draft',


                'request_status' =>
                    $paymentRequest->status
                    ?? null,


                // ---------------------------------------------
                // HEADER
                // ---------------------------------------------

                'no_spk' =>
                    $data['no_spk']
                    ?? '-',


                'no_po' =>
                    $data['no_po']
                    ?? '-',


                'nama' =>
                    $data['sup']
                    ?? '-',


                'type' =>
                    $data['kategori']
                    ?? '-',


                // ---------------------------------------------
                // DATE
                // ---------------------------------------------

                'tgl_terima' =>
                    $tglTerima,


                'tgl_selesai' =>
                    $tglSelesai,


                // ---------------------------------------------
                // ITEMS
                // ---------------------------------------------

                'items' =>
                    $items,


                // ---------------------------------------------
                // PAYMENT
                // ---------------------------------------------

                'payments' =>
                    $data['payments']
                    ?? [],


                // ---------------------------------------------
                // CUSTOM
                // ---------------------------------------------

                'checked_types' =>
                    $data['checked_types']
                    ?? [],


                'custom_headers' =>
                    $data['custom_headers']
                    ?? [],


                // ---------------------------------------------
                // PPN
                // ---------------------------------------------

                'ppn_enabled' =>
                    (bool) (
                        $data['ppn_enabled']
                        ?? false
                    ),


                'ppn_rate' =>
                    (float) (
                        $data['ppn_rate']
                        ?? 11
                    ),


                // ---------------------------------------------
                // SIGNATURE STATUS
                // ---------------------------------------------

                'signature_status' =>
                    $signatureStatus,


                'signed_count' =>
                    $signedSignature,


                'pending_count' =>
                    $pendingSignature,


                'next_approval' =>
                    $nextApproval,


                // ---------------------------------------------
                // BAHAN BAKU
                // ---------------------------------------------

                'bahan_baku' =>
                    $bahanBaku
                        ->get($spkModel->id)
                    ?? collect(),


                // ---------------------------------------------
                // MODE
                // ---------------------------------------------

                'mode' =>
                    'assign',

            ]);
        }


        // =====================================================
        // SORT
        //
        // Yang paling membutuhkan tanda tangan
        // diletakkan lebih dahulu.
        // =====================================================

        $spks = $spks
            ->sortBy(function ($spk) {

                return [
                    $spk['pending_count'] * -1,
                    $spk['id'],
                ];

            })
            ->values();


        // =====================================================
        // RETURN ASSIGN VIEW
        // =====================================================

        return view(
            'pages.spk.assign',
            [
                'spks' =>
                    $spks,

                'jenisSuppliers' =>
                    $jenisSuppliers,
            ]
        );
    }
    private function parseTanggalIndonesia($value, $default = null)
    {
        if ($value === null || $value === '') {
            return $default;
        }

        // Kalau sudah Carbon
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        // Kalau DateTime / DateTimeInterface
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        $value = trim((string) $value);

        if ($value === '') {
            return $default;
        }

        // Normalisasi whitespace
        $value = preg_replace('/\s+/', ' ', $value);

        // Hilangkan spasi di sekitar tanda -
        $value = preg_replace('/\s*-\s*/', '-', $value);

        // Hilangkan spasi di sekitar /
        $value = preg_replace('/\s*\/\s*/', '/', $value);

        // Hilangkan spasi di sekitar .
        $value = preg_replace('/\s*\.\s*/', '.', $value);

        // =====================================================
        // BULAN INDONESIA
        // =====================================================

        $bulanIndonesia = [
            'januari' => '01',
            'februari' => '02',
            'maret' => '03',
            'april' => '04',
            'mei' => '05',
            'juni' => '06',
            'juli' => '07',
            'agustus' => '08',
            'september' => '09',
            'oktober' => '10',
            'november' => '11',
            'desember' => '12',
        ];

        // Singkatan Indonesia
        $bulanSingkat = [
            'jan' => '01',
            'feb' => '02',
            'mar' => '03',
            'apr' => '04',
            'mei' => '05',
            'jun' => '06',
            'jul' => '07',
            'agu' => '08',
            'ags' => '08',
            'sep' => '09',
            'okt' => '10',
            'nov' => '11',
            'des' => '12',
        ];

        // =====================================================
        // GANTI BULAN INDONESIA DENGAN ANGKA
        // =====================================================

        $lower = strtolower($value);

        foreach ($bulanIndonesia as $nama => $bulan) {
            $lower = preg_replace(
                '/\b' . preg_quote($nama, '/') . '\b/i',
                $bulan,
                $lower
            );
        }

        foreach ($bulanSingkat as $nama => $bulan) {
            $lower = preg_replace(
                '/\b' . preg_quote($nama, '/') . '\b/i',
                $bulan,
                $lower
            );
        }

        $value = trim($lower);

        // =====================================================
        // FORMAT:
        // 7-07-2026
        // 7/07/2026
        // 7.07.2026
        // =====================================================

        $formats = [
            'd-m-Y',
            'j-m-Y',
            'd/m/Y',
            'j/n/Y',
            'd.m.Y',
            'j.n.Y',

            'd-m-y',
            'j-m-y',
            'd/m/y',
            'j/n/y',
            'd.m.y',
            'j.n.y',

            'Y-m-d',
            'Y/m/d',
            'Y.m.d',

            'd-M-Y',
            'j-M-Y',
            'd-M-y',
            'j-M-y',

            'd-F-Y',
            'j-F-Y',
            'd-F-y',
            'j-F-y',
        ];

        foreach ($formats as $format) {

            try {

                $date = Carbon::createFromFormat(
                    $format,
                    $value
                );

                if ($date !== false) {
                    return $date->format('Y-m-d');
                }

            } catch (\Throwable $e) {
                // lanjut ke format berikutnya
            }
        }

        // =====================================================
        // FORMAT SPECIAL:
        // 7-07- 2026
        // =====================================================

        $normalized = preg_replace(
            '/\s+/',
            '',
            $value
        );

        foreach ($formats as $format) {

            try {

                $date = Carbon::createFromFormat(
                    $format,
                    $normalized
                );

                if ($date !== false) {
                    return $date->format('Y-m-d');
                }

            } catch (\Throwable $e) {
                // lanjut
            }
        }

        // =====================================================
        // FALLBACK CARBON
        // =====================================================

        try {

            return Carbon::parse($value)
                ->format('Y-m-d');

        } catch (\Throwable $e) {

            return $default;
        }
    }
    // helper
    private function generateNoSpk($noPo)
    {
        $now = now();
        $year = $now->format('y'); // 26
        $month = $now->format('m'); // 02
        $yearFull = $now->format('Y'); // 2026
        // 🔥 ambil SPK terakhir di tahun yg sama
        $lastSpk = Spk::where('data->no_spk', 'like', "{$year}-%")
            ->orderByDesc('id')
            ->first();
        $nextNumber = 1;
        if ($lastSpk) {
            // contoh: 26-0029/NW NW 25 - 02/02/2026
            $noSpk = $lastSpk->data['no_spk'] ?? '';
            if (preg_match('/^\d{2}-(\d{4})/', $noSpk, $match)) {
                $nextNumber = (int) $match[1] + 1;
            }
        }
        $urut = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $tanggal = $now->format('m/Y');

        return "{$year}-{$urut}/{$noPo}/{$tanggal}";
    }
    
    // public function save(Request $request, $poId)
    // {
    //     $kategori = $request->input('spk_type');
    //     $items = $request->input('items', []);
    //     $spkId = $request->input('spk_id');

    //     // =====================================================
    //     // VALIDASI DASAR
    //     // =====================================================
    //     if (!$kategori || empty($items)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Data tidak lengkap',
    //         ], 422);
    //     }

    //     // =====================================================
    //     // MODE
    //     // =====================================================
    //     $mode = $spkId ? 'edit' : 'create';

    //     $beforeData = [];
    //     $spkModel = null;

    //     // =====================================================
    //     // EDIT MODE
    //     // =====================================================
    //     if ($mode === 'edit') {

    //         $spkModel = Spk::findOrFail($spkId);

    //         $beforeData = $spkModel->data ?? [];

    //         if (!is_array($beforeData)) {
    //             $beforeData = [];
    //         }
    //     }

    //     // =====================================================
    //     // PPN
    //     // =====================================================
    //     $ppnEnabled = $request->boolean('ppn_enabled');

    //     $ppnRate = $request->input('ppn_rate', 11);

    //     if (!is_numeric($ppnRate)) {
    //         $ppnRate = 11;
    //     }

    //     $ppnRate = (float) $ppnRate;

    //     if ($ppnRate < 0) {
    //         $ppnRate = 0;
    //     }

    //     if (!$ppnEnabled) {
    //         $ppnRate = 0;
    //     }

    //     // =====================================================
    //     // NORMALISASI KATEGORI
    //     // =====================================================
    //     $kategoriCheck = trim(
    //         strtolower($kategori)
    //     );

    //     // =====================================================
    //     // OLAH ITEMS
    //     // =====================================================
    //     $finalItems = [];

    //     foreach ($items as $item) {

    //         // =================================================
    //         // DETAIL PO
    //         // =================================================
    //         if (empty($item['detail_id'])) {
    //             continue;
    //         }

    //         // =================================================
    //         // HITUNG QTY
    //         // =================================================
    //         $qty = 0;

    //         if (($item['satuan'] ?? '') === 'pcs') {

    //             $qty = (int) (
    //                 $item['pcs'] ?? 0
    //             );

    //         } elseif (($item['satuan'] ?? '') === 'set') {

    //             $qty = (int) (
    //                 $item['set'] ?? 0
    //             );
    //         }

    //         if ($qty <= 0) {
    //             continue;
    //         }

    //         // =================================================
    //         // DETAIL PO
    //         // =================================================
    //         $detailPo = DetailPo::find(
    //             $item['detail_id']
    //         );

    //         if (!$detailPo) {

    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Detail PO tidak ditemukan',
    //             ], 422);
    //         }

    //         // =================================================
    //         // QTY PO
    //         // =================================================
    //         $qtyPo = (int) (
    //             $detailPo->detail['qty'] ?? 0
    //         );

    //         // =================================================
    //         // HITUNG QTY SPK LAIN
    //         // =================================================
    //         //
    //         // SPK yang sedang diedit TIDAK dihitung.
    //         //
    //         // Yang dihitung hanya:
    //         // - SPK lain
    //         // - kategori sama
    //         // - detail_po_id sama
    //         //
    //         // =================================================
    //         $qtySpkLain = 0;

    //         $spkLain = Spk::query()
    //             ->where(
    //                 'id',
    //                 '!=',
    //                 $spkId ?: 0
    //             )
    //             ->get();

    //         foreach ($spkLain as $otherSpk) {

    //             $otherData = $otherSpk->data ?? [];

    //             if (!is_array($otherData)) {
    //                 continue;
    //             }

    //             // ---------------------------------------------
    //             // KATEGORI
    //             // ---------------------------------------------
    //             $otherKategori = trim(
    //                 strtolower(
    //                     $otherData['kategori'] ?? ''
    //                 )
    //             );

    //             if (
    //                 $otherKategori !==
    //                 $kategoriCheck
    //             ) {
    //                 continue;
    //             }

    //             // ---------------------------------------------
    //             // ITEMS
    //             // ---------------------------------------------
    //             foreach (
    //                 $otherData['items'] ?? []
    //                 as $otherItem
    //             ) {

    //                 if (
    //                     (string) (
    //                         $otherItem['detail_po_id']
    //                         ?? ''
    //                     )
    //                     !==
    //                     (string) $detailPo->id
    //                 ) {
    //                     continue;
    //                 }

    //                 $otherQty = (int) (
    //                     $otherItem['qty'] ?? 0
    //                 );

    //                 $qtySpkLain += $otherQty;
    //             }
    //         }

    //         // =================================================
    //         // QTY LAMA SPK YANG SEDANG DIEDIT
    //         // =================================================
    //         $qtyLama = 0;

    //         if ($mode === 'edit') {

    //             foreach (
    //                 $beforeData['items'] ?? []
    //                 as $oldItem
    //             ) {

    //                 if (
    //                     (string) (
    //                         $oldItem['detail_po_id']
    //                         ?? ''
    //                     )
    //                     !==
    //                     (string) $detailPo->id
    //                 ) {
    //                     continue;
    //                 }

    //                 $qtyLama += (int) (
    //                     $oldItem['qty'] ?? 0
    //                 );
    //             }
    //         }

    //         // =================================================
    //         // VALIDASI QTY SEMENTARA DIMATIKAN
    //         // =================================================
    //         //
    //         // Untuk sementara qty SPK BOLEH melebihi Qty PO.
    //         //
    //         // Perhitungan berikut tetap dipertahankan:
    //         // - $qtyPo
    //         // - $qtySpkLain
    //         // - $qtyLama
    //         //
    //         // Tidak ada fungsi lain yang diubah.
    //         //
    //         // Validasi Qty dapat diaktifkan kembali nanti
    //         // dengan mengembalikan blok VALIDASI QTY lama.
    //         // =================================================

    //         // =================================================
    //         // IMAGE ITEM
    //         // =================================================
    //         $itemImages = [];

    //         foreach (
    //             $item['images'] ?? []
    //             as $img
    //         ) {

    //             if (
    //                 is_string($img) &&
    //                 str_starts_with(
    //                     $img,
    //                     'data:image'
    //                 )
    //             ) {

    //                 $itemImages[] =
    //                     $this->saveBase64Image(
    //                         $img,
    //                         'spk/items'
    //                     );

    //             } else {

    //                 $itemImages[] = $img;
    //             }
    //         }

    //         // =================================================
    //         // IMAGE CATATAN
    //         // =================================================
    //         $noteImages = [];

    //         foreach (
    //             $item['catatan']['images'] ?? []
    //             as $img
    //         ) {

    //             if (
    //                 is_string($img) &&
    //                 str_starts_with(
    //                     $img,
    //                     'data:image'
    //                 )
    //             ) {

    //                 $noteImages[] =
    //                     $this->saveBase64Image(
    //                         $img,
    //                         'spk/notes'
    //                     );

    //             } else {

    //                 $noteImages[] = $img;
    //             }
    //         }

    //         // =================================================
    //         // ITEM FINAL
    //         // =================================================
    //         $finalItems[] = [

    //             'detail_po_id' =>
    //                 $detailPo->id,

    //             'kode' =>
    //                 (string) (
    //                     $item['kode'] ?? ''
    //                 ),

    //             'nama' =>
    //                 (string) (
    //                     $item['nama'] ?? ''
    //                 ),

    //             'qty' =>
    //                 $qty,

    //             'satuan' =>
    //                 $item['satuan'] ?? '',

    //             'material' =>
    //                 (string) (
    //                     $item['material'] ?? ''
    //                 ),

    //             'p' =>
    //                 (string) (
    //                     $item['p'] ?? ''
    //                 ),

    //             'l' =>
    //                 (string) (
    //                     $item['l'] ?? ''
    //                 ),

    //             't' =>
    //                 (string) (
    //                     $item['t'] ?? ''
    //                 ),

    //             // =================================================
    //             // HARGA DASAR TANPA PPN
    //             // =================================================
    //             'harga' =>
    //                 (float) (
    //                     $item['harga'] ?? 0
    //                 ),

    //             // =================================================
    //             // TOTAL DASAR TANPA PPN
    //             // =================================================
    //             'total' =>
    //                 (float) (
    //                     $item['total'] ?? 0
    //                 ),

    //             // =================================================
    //             // IMAGES
    //             // =================================================
    //             'images' =>
    //                 $itemImages,

    //             // =================================================
    //             // CATATAN
    //             // =================================================
    //             'catatan' => [

    //                 'remark' =>
    //                     (string) (
    //                         $item['catatan']['remark']
    //                         ?? ''
    //                     ),

    //                 'images' =>
    //                     $noteImages,
    //             ],

    //             // =================================================
    //             // CUSTOM COLUMNS
    //             // =================================================
    //             'custom_columns' =>
    //                 $item['custom_columns']
    //                 ?? [],
    //         ];
    //     }

    //     // =====================================================
    //     // VALIDASI FINAL
    //     // =====================================================
    //     if (empty($finalItems)) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tidak ada item valid',
    //         ], 422);
    //     }

    //     // =====================================================
    //     // DATA FINAL
    //     // =====================================================
    //     $afterData = [

    //         'status' =>
    //             $request->input(
    //                 'status',
    //                 'draft'
    //             ),

    //         'kategori' =>
    //             $kategori,

    //         'no_spk' =>
    //             $request->input(
    //                 'no_spk'
    //             ),

    //         'no_po' =>
    //             $request->input(
    //                 'no_po'
    //             ),

    //         'sup' =>
    //             $request->input(
    //                 'nama'
    //             ),

    //         'tgl_terima' =>
    //             $request->input(
    //                 'tgl_terima'
    //             ),

    //         'tgl_selesai' =>
    //             $request->input(
    //                 'tgl_selesai'
    //             ),

    //         // =================================================
    //         // PPN
    //         // =================================================
    //         'ppn_enabled' =>
    //             $ppnEnabled,

    //         'ppn_rate' =>
    //             $ppnRate,

    //         // =================================================
    //         // ITEMS
    //         // =================================================
    //         'items' =>
    //             $finalItems,

    //         // =================================================
    //         // PAYMENTS
    //         // =================================================
    //         'payments' =>
    //             $request->input(
    //                 'payments',
    //                 []
    //             ),

    //         // =================================================
    //         // CHECKED TYPES
    //         // =================================================
    //         'checked_types' =>
    //             $request->input(
    //                 'checked_types',
    //                 []
    //             ),

    //         // =================================================
    //         // CUSTOM HEADERS
    //         // =================================================
    //         'custom_headers' =>
    //             $request->input(
    //                 'custom_headers',
    //                 []
    //             ),
    //     ];

    //     // =====================================================
    //     // CREATE
    //     // =====================================================
    //     if ($mode === 'create') {

    //         $spk = Spk::create([
    //             'po_id' =>
    //                 $poId,

    //             'data' =>
    //                 $afterData,

    //             'created_by' =>
    //                 auth()->id(),
    //         ]);

    //         // =================================================
    //         // TIMELINE CREATE
    //         // =================================================
    //         SpkTimeline::create([
    //             'spk_id' =>
    //                 $spk->id,

    //             'data' => [

    //                 'type' =>
    //                     'create',

    //                 'user' =>
    //                     auth()->user()->name,

    //                 'time' =>
    //                     now(),

    //                 'after' =>
    //                     $afterData,
    //             ],
    //         ]);

    //     } else {

    //         // =================================================
    //         // UPDATE
    //         // =================================================

    //         $changes =
    //             $this->diffRecursive(
    //                 $beforeData,
    //                 $afterData
    //             );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | JANGAN menggunakan updated_by
    //         |
    //         | Karena tabel spk Anda tidak mempunyai kolom updated_by.
    //         |--------------------------------------------------------------------------
    //         */

    //         $spkModel->update([
    //             'data' =>
    //                 $afterData,
    //         ]);

    //         // =================================================
    //         // TIMELINE UPDATE
    //         // =================================================
    //         SpkTimeline::create([
    //             'spk_id' =>
    //                 $spkModel->id,

    //             'data' => [

    //                 'type' =>
    //                     'update',

    //                 'user' =>
    //                     auth()->user()->name,

    //                 'time' =>
    //                     now(),

    //                 'before' =>
    //                     $beforeData,

    //                 'after' =>
    //                     $afterData,

    //                 'changes' =>
    //                     $changes,
    //             ],
    //         ]);

    //         $spk = $spkModel;
    //     }

    //     // =====================================================
    //     // DATA TERSIMPAN
    //     // =====================================================
    //     $savedData =
    //         $spk->data ?? [];

    //     // =====================================================
    //     // RESPONSE
    //     // =====================================================
    //     return response()->json([

    //         'success' =>
    //             true,

    //         'message' =>
    //             $mode === 'edit'
    //             ? 'SPK berhasil diperbarui'
    //             : 'SPK berhasil dibuat',

    //         'spk_id' =>
    //             $spk->id,

    //         'no_spk' =>
    //             $savedData['no_spk']
    //             ?? $request->input(
    //                 'no_spk'
    //             ),

    //         // =================================================
    //         // PPN DEBUG
    //         // =================================================
    //         'ppn_debug' => [

    //             'request_enabled' =>
    //                 $request->input(
    //                     'ppn_enabled'
    //                 ),

    //             'request_rate' =>
    //                 $request->input(
    //                     'ppn_rate'
    //                 ),

    //             'saved_enabled' =>
    //                 $savedData[
    //                     'ppn_enabled'
    //                 ] ?? null,

    //             'saved_rate' =>
    //                 $savedData[
    //                     'ppn_rate'
    //                 ] ?? null,
    //         ],
    //     ]);
    // }

  
  
  public function save(Request $request, $poId)
{
    $kategori = $request->input('spk_type');
    $items = $request->input('items', []);
    $spkId = $request->input('spk_id');

    // =====================================================
    // VALIDASI DASAR
    // =====================================================
    if (!$kategori || empty($items)) {

        return response()->json([
            'success' => false,
            'message' => 'Data tidak lengkap',
        ], 422);
    }

    // =====================================================
    // MODE
    // =====================================================
    $mode = $spkId ? 'edit' : 'create';

    $beforeData = [];
    $spkModel = null;

    // =====================================================
    // EDIT MODE
    // =====================================================
    if ($mode === 'edit') {

        $spkModel = Spk::findOrFail($spkId);

        $beforeData = $spkModel->data ?? [];

        if (!is_array($beforeData)) {
            $beforeData = [];
        }
    }

    // =====================================================
    // PPN
    // =====================================================
    $ppnEnabled =
        $request->boolean('ppn_enabled');

    $ppnRate =
        $request->input(
            'ppn_rate',
            11
        );

    if (!is_numeric($ppnRate)) {

        $ppnRate = 11;
    }

    $ppnRate =
        (float) $ppnRate;

    if ($ppnRate < 0) {

        $ppnRate = 0;
    }

    if (!$ppnEnabled) {

        $ppnRate = 0;
    }

    // =====================================================
    // NORMALISASI KATEGORI
    // =====================================================
    $kategoriCheck = trim(
        strtolower($kategori)
    );

    // =====================================================
    // OLAH ITEMS
    // =====================================================
    $finalItems = [];

    foreach ($items as $item) {

        // =================================================
        // DETAIL PO
        // =================================================
        if (empty($item['detail_id'])) {

            continue;
        }

        // =================================================
        // HITUNG QTY
        // =================================================
        //
        // PCS -> field pcs
        // KG  -> field pcs
        // SET -> field set
        //
        // Contoh:
        // 2     -> 2
        // 2,5   -> 2.5
        // 56,5  -> 56.5
        //
        $satuanItem = strtolower(
            trim(
                (string) (
                    $item['satuan'] ?? 'pcs'
                )
            )
        );

        $qtyRaw = 0;

        if ($satuanItem === 'set') {

            $qtyRaw =
                $item['set'] ?? 0;

        } else {

            /*
             * PCS
             * KG
             * Custom
             *
             * menggunakan quantity utama
             */
            $qtyRaw =
                $item['pcs'] ?? 0;
        }

        // =================================================
        // NORMALISASI ANGKA
        // =================================================
        if (is_string($qtyRaw)) {

            $qtyRaw =
                trim($qtyRaw);

            $qtyRaw =
                str_replace(
                    ' ',
                    '',
                    $qtyRaw
                );

            /*
             * 1.250,5
             * menjadi 1250.5
             */
            if (
                str_contains(
                    $qtyRaw,
                    '.'
                ) &&
                str_contains(
                    $qtyRaw,
                    ','
                )
            ) {

                if (
                    strrpos(
                        $qtyRaw,
                        ','
                    ) >
                    strrpos(
                        $qtyRaw,
                        '.'
                    )
                ) {

                    $qtyRaw =
                        str_replace(
                            '.',
                            '',
                            $qtyRaw
                        );

                    $qtyRaw =
                        str_replace(
                            ',',
                            '.',
                            $qtyRaw
                        );

                } else {

                    /*
                     * 1,250.5
                     * menjadi 1250.5
                     */
                    $qtyRaw =
                        str_replace(
                            ',',
                            '',
                            $qtyRaw
                        );
                }

            /*
             * 56,5
             * menjadi 56.5
             */
            } elseif (
                str_contains(
                    $qtyRaw,
                    ','
                )
            ) {

                $qtyRaw =
                    str_replace(
                        ',',
                        '.',
                        $qtyRaw
                    );

            /*
             * 1.250.000
             * menjadi 1250000
             */
            } elseif (
                substr_count(
                    $qtyRaw,
                    '.'
                ) > 1
            ) {

                $qtyRaw =
                    str_replace(
                        '.',
                        '',
                        $qtyRaw
                    );
            }
        }

        $qty =
            is_numeric($qtyRaw)
                ? (float) $qtyRaw
                : 0;

        if ($qty <= 0) {

            continue;
        }

        // =================================================
        // DETAIL PO
        // =================================================
        $detailPo =
            DetailPo::find(
                $item['detail_id']
            );

        if (!$detailPo) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Detail PO tidak ditemukan',
            ], 422);
        }

        // =================================================
        // QTY PO
        // =================================================
        $qtyPo = (int) (
            $detailPo->detail['qty'] ?? 0
        );

        // =================================================
        // HITUNG QTY SPK LAIN
        // =================================================
        //
        // SPK yang sedang diedit TIDAK dihitung.
        //
        // Yang dihitung hanya:
        // - SPK lain
        // - kategori sama
        // - detail_po_id sama
        //
        // =================================================
        $qtySpkLain = 0;

        $spkLain = Spk::query()
            ->where(
                'id',
                '!=',
                $spkId ?: 0
            )
            ->get();

        foreach ($spkLain as $otherSpk) {

            $otherData =
                $otherSpk->data ?? [];

            if (!is_array($otherData)) {

                continue;
            }

            // ---------------------------------------------
            // KATEGORI
            // ---------------------------------------------
            $otherKategori =
                trim(
                    strtolower(
                        $otherData['kategori']
                        ?? ''
                    )
                );

            if (
                $otherKategori !==
                $kategoriCheck
            ) {

                continue;
            }

            // ---------------------------------------------
            // ITEMS
            // ---------------------------------------------
            foreach (
                $otherData['items'] ?? []
                as $otherItem
            ) {

                if (
                    (string) (
                        $otherItem['detail_po_id']
                        ?? ''
                    )
                    !==
                    (string) $detailPo->id
                ) {

                    continue;
                }

                $otherQty =
                    (int) (
                        $otherItem['qty']
                        ?? 0
                    );

                $qtySpkLain +=
                    $otherQty;
            }
        }

        // =================================================
        // QTY LAMA SPK YANG SEDANG DIEDIT
        // =================================================
        $qtyLama = 0;

        if ($mode === 'edit') {

            foreach (
                $beforeData['items'] ?? []
                as $oldItem
            ) {

                if (
                    (string) (
                        $oldItem['detail_po_id']
                        ?? ''
                    )
                    !==
                    (string) $detailPo->id
                ) {

                    continue;
                }

                $qtyLama +=
                    (int) (
                        $oldItem['qty']
                        ?? 0
                    );
            }
        }

        // =================================================
        // VALIDASI QTY SEMENTARA DIMATIKAN
        // =================================================
        //
        // Untuk sementara qty SPK BOLEH
        // melebihi Qty PO.
        //
        // Perhitungan:
        // - $qtyPo
        // - $qtySpkLain
        // - $qtyLama
        //
        // tetap dipertahankan.
        //
        // =================================================

        /*
        $sisaQty =
            $qtyPo -
            $qtySpkLain;

        if ($mode === 'edit') {

            $sisaQty += $qtyLama;
        }

        if ($qty > $sisaQty) {

            return response()->json([
                'success' => false,

                'message' =>
                    'Qty SPK melebihi Qty PO ' .
                    '(Sisa: ' .
                    max(0, $sisaQty) .
                    ')',

                'debug' => [
                    'mode' =>
                        $mode,

                    'spk_id' =>
                        $spkId,

                    'detail_po_id' =>
                        $detailPo->id,

                    'qty_po' =>
                        $qtyPo,

                    'qty_spk_lain' =>
                        $qtySpkLain,

                    'qty_lama' =>
                        $qtyLama,

                    'qty_request' =>
                        $qty,

                    'sisa_qty' =>
                        $sisaQty,
                ],
            ], 422);
        }
        */

        // =================================================
        // IMAGE ITEM
        // =================================================
        $itemImages = [];

        foreach (
            $item['images'] ?? []
            as $img
        ) {

            if (
                is_string($img) &&
                str_starts_with(
                    $img,
                    'data:image'
                )
            ) {

                $itemImages[] =
                    $this->saveBase64Image(
                        $img,
                        'spk/items'
                    );

            } else {

                $itemImages[] =
                    $img;
            }
        }

        // =================================================
        // IMAGE CATATAN
        // =================================================
        $noteImages = [];

        foreach (
            $item['catatan']['images'] ?? []
            as $img
        ) {

            if (
                is_string($img) &&
                str_starts_with(
                    $img,
                    'data:image'
                )
            ) {

                $noteImages[] =
                    $this->saveBase64Image(
                        $img,
                        'spk/notes'
                    );

            } else {

                $noteImages[] =
                    $img;
            }
        }

        // =================================================
        // ITEM FINAL
        // =================================================
        $finalItems[] = [

            'detail_po_id' =>
                $detailPo->id,

            'kode' =>
                (string) (
                    $item['kode'] ?? ''
                ),

            'nama' =>
                (string) (
                    $item['nama'] ?? ''
                ),

            'qty' =>
                $qty,

            'satuan' =>
                $item['satuan'] ?? '',

            'material' =>
                (string) (
                    $item['material'] ?? ''
                ),

            'p' =>
                (string) (
                    $item['p'] ?? ''
                ),

            'l' =>
                (string) (
                    $item['l'] ?? ''
                ),

            't' =>
                (string) (
                    $item['t'] ?? ''
                ),

            // =================================================
            // HARGA DASAR TANPA PPN
            // =================================================
            'harga' =>
                (float) (
                    $item['harga'] ?? 0
                ),

            // =================================================
            // TOTAL DASAR TANPA PPN
            // =================================================
            'total' =>
                (float) (
                    $item['total'] ?? 0
                ),

            // =================================================
            // IMAGES
            // =================================================
            'images' =>
                $itemImages,

            // =================================================
            // CATATAN
            // =================================================
            'catatan' => [

                'remark' =>
                    (string) (
                        $item['catatan']['remark']
                        ?? ''
                    ),

                'images' =>
                    $noteImages,
            ],

            // =================================================
            // CUSTOM COLUMNS
            // =================================================
            'custom_columns' =>
                $item['custom_columns']
                ?? [],
        ];
    }

    // =====================================================
    // VALIDASI FINAL
    // =====================================================
    if (empty($finalItems)) {

        return response()->json([
            'success' => false,
            'message' =>
                'Tidak ada item valid',
        ], 422);
    }

    // =====================================================
    // DATA FINAL
    // =====================================================
    $afterData = [

        'status' =>
            $request->input(
                'status',
                'draft'
            ),

        'kategori' =>
            $kategori,

        'no_spk' =>
            $request->input(
                'no_spk'
            ),

        'no_po' =>
            $request->input(
                'no_po'
            ),

        'sup' =>
            $request->input(
                'nama'
            ),

        'tgl_terima' =>
            $request->input(
                'tgl_terima'
            ),

        'tgl_selesai' =>
            $request->input(
                'tgl_selesai'
            ),

        // =================================================
        // PPN
        // =================================================
        'ppn_enabled' =>
            $ppnEnabled,

        'ppn_rate' =>
            $ppnRate,

        // =================================================
        // ITEMS
        // =================================================
        'items' =>
            $finalItems,

        // =================================================
        // PAYMENTS
        // =================================================
        'payments' =>
            $request->input(
                'payments',
                []
            ),

        // =================================================
        // CHECKED TYPES
        // =================================================
        'checked_types' =>
            $request->input(
                'checked_types',
                []
            ),

        // =================================================
        // CUSTOM HEADERS
        // =================================================
        'custom_headers' =>
            $request->input(
                'custom_headers',
                []
            ),
    ];

    // =====================================================
    // CREATE
    // =====================================================
    if ($mode === 'create') {

        $spk = Spk::create([

            'po_id' =>
                $poId,

            'data' =>
                $afterData,
  'status' => 'draft',
            'created_by' =>
                auth()->id(),
        ]);

        // =================================================
        // TIMELINE CREATE
        // =================================================
        SpkTimeline::create([

            'spk_id' =>
                $spk->id,

            'data' => [

                'type' =>
                    'create',

                'user' =>
                    auth()->user()->name,

                'time' =>
                    now(),

                'after' =>
                    $afterData,
            ],
        ]);

    } else {

        // =================================================
        // UPDATE
        // =================================================
        $changes =
            $this->diffRecursive(
                $beforeData,
                $afterData
            );

        /*
        |--------------------------------------------------------------------------
        | JANGAN menggunakan updated_by
        |
        | Karena tabel spk tidak memiliki kolom updated_by.
        |--------------------------------------------------------------------------
        */

        $spkModel->update([

            'data' =>
                $afterData,
        ]);

        // =================================================
        // TIMELINE UPDATE
        // =================================================
        SpkTimeline::create([

            'spk_id' =>
                $spkModel->id,

            'data' => [

                'type' =>
                    'update',

                'user' =>
                    auth()->user()->name,

                'time' =>
                    now(),

                'before' =>
                    $beforeData,

                'after' =>
                    $afterData,

                'changes' =>
                    $changes,
            ],
        ]);

        $spk =
            $spkModel;
    }

    // =====================================================
    // DATA TERSIMPAN
    // =====================================================
    $savedData =
        $spk->data ?? [];

    // =====================================================
    // RESPONSE
    // =====================================================
    return response()->json([

        'success' =>
            true,

        'message' =>
            $mode === 'edit'
                ? 'SPK berhasil diperbarui'
                : 'SPK berhasil dibuat',

        'spk_id' =>
            $spk->id,

        'no_spk' =>
            $savedData['no_spk']
            ?? $request->input(
                'no_spk'
            ),

        // =================================================
        // PPN DEBUG
        // =================================================
        'ppn_debug' => [

            'request_enabled' =>
                $request->input(
                    'ppn_enabled'
                ),

            'request_rate' =>
                $request->input(
                    'ppn_rate'
                ),

            'saved_enabled' =>
                $savedData[
                    'ppn_enabled'
                ] ?? null,

            'saved_rate' =>
                $savedData[
                    'ppn_rate'
                ] ?? null,
        ],
    ]);
}
  
    public function timeline($id)
    {
        $timelines = SpkTimeline::where('spk_id', $id)
            ->latest()
            ->get();

        return response()->json($timelines);
    }

    // helper
    private function diffRecursive($before, $after, $path = '')
    {
        $changes = [];
        foreach ($after as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;
            if (!array_key_exists($key, $before)) {
                $changes[$currentPath] = [
                    'before' => null,
                    'after' => $value,
                ];

                continue;
            }
            if (is_array($value) && is_array($before[$key])) {
                $nested = $this->diffRecursive($before[$key], $value, $currentPath);
                $changes = array_merge($changes, $nested);
            } elseif ($before[$key] != $value) {
                $changes[$currentPath] = [
                    'before' => $before[$key],
                    'after' => $value,
                ];
            }
        }

        return $changes;
    }
    public function export($spkId)
    {
        return ExportSpks::export($spkId);
    }

    public function getTotalSpkQtyByDetailPoAndKategori(
        int $detailPoId,
        string $kategori,
        ?int $excludeSpkId = null
    ) {
        return Spk::where('data->kategori', $kategori)
            ->when($excludeSpkId, function ($q) use ($excludeSpkId) {
                $q->where('id', '!=', $excludeSpkId);
            })
            ->get()
            ->sum(function ($spk) use ($detailPoId) {
                return collect($spk->data['items'] ?? [])
                    ->where('detail_po_id', $detailPoId)
                    ->sum(fn($i) => (int) ($i['qty'] ?? 0));
            });
    }

    // ItemController.php
    public function search(Request $request)
    {
        $q = trim((string) $request->q);

        if ($q === '') {
            return [];
        }

        $search = mb_strtolower($q, 'UTF-8');

        return DetailPo::where(function ($query) use ($search) {

            $query->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail, "$.article_nr_"))) LIKE ?',
                ["%{$search}%"]
            )

                ->orWhereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail, "$.description"))) LIKE ?',
                    ["%{$search}%"]
                );

        })
            ->limit(10)
            ->get()
            ->map(function ($row) {

                $detail = $row->detail ?? [];

                $images = [];

                if (!empty($detail['photo'])) {
                    $images[] = $detail['photo'];
                }

                return [
                    'detail_id' => $row->id,

                    'kode' => data_get(
                        $detail,
                        'article_nr_'
                    ),

                    'nama' => data_get(
                        $detail,
                        'description'
                    ),

                    'p' => (float) data_get(
                        $detail,
                        'item_w'
                    ),

                    'l' => (float) data_get(
                        $detail,
                        'item_d'
                    ),

                    't' => (float) data_get(
                        $detail,
                        'item_h'
                    ),

                    'material' => data_get(
                        $detail,
                        'composition'
                    ),

                    'qty' => (int) data_get(
                        $detail,
                        'qty'
                    ),

                    'photo' => data_get(
                        $detail,
                        'photo'
                    ),

                    'images' => $images,
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
                    'id' => $row->id,
                    'spk_id' => $row->spk_id,
                    'type' => $data['type'] ?? 'info',
                    'user' => $data['user'] ?? optional($row->user)->name,
                    'time' => $data['time'] ?? $row->created_at,
                    'before' => $data['before'] ?? null,
                    'after' => $data['after'] ?? null,
                    'changes' => $data['changes'] ?? null,
                ];
            });

        return response()->json($timeline);
    }

    public function spk(Request $request)
    {
        $isRndSpk = $request->spk === 'rnd_spk';

        return view('pages.spk.all', compact('isRndSpk'));
    }

    public function allspk()
    {
        $poList = Po::all();
        $spks = Spk::all();
        $detailPo = DetailPo::all();
        $result = $poList->map(function ($po) use ($spks, $detailPo) {
            // =========================
            // SPK per PO
            // =========================
            $spkList = $spks
                ->where('po_id', $po->id)
                ->map(function ($spk) {
                    return [
                        'id' => $spk->id,
                        'data' => $spk->data,
                    ];
                });
            // =========================
            // DETAIL PO ITEMS
            // =========================
            $items = $detailPo
                ->where('po_id', $po->id)
                ->map(function ($item) use ($spkList) {
                    $detail = is_string($item->detail)
                        ? json_decode($item->detail, true)
                        : $item->detail;
                    /**
                     * STRUKTUR:
                     * kategori → supplier → total_qty
                     */
                    $summary = [];
                    foreach ($spkList as $spk) {
                        $spkId = $spk['id'];
                        $data = $spk['data'] ?? [];
                        $supplier = $data['sup'] ?? '-';
                        $kategori = $data['kategori'] ?? '-';
                        $noSpk = $data['no_spk'] ?? '-';
                        foreach ($data['items'] ?? [] as $spkItem) {
                            if (
                                isset($spkItem['detail_po_id']) &&
                                $spkItem['detail_po_id'] == $item->id
                            ) {
                                $qty = (int) ($spkItem['qty'] ?? 0);

                                if (!isset($summary[$kategori])) {
                                    $summary[$kategori] = [];
                                }

                                if (!isset($summary[$kategori][$supplier])) {
                                    $summary[$kategori][$supplier] = [
                                        'total_qty' => 0,
                                        'spks' => [],
                                    ];
                                }

                                $summary[$kategori][$supplier]['total_qty'] += $qty;
                                // detail per SPK
                                $summary[$kategori][$supplier]['spks'][] = [
                                    'spk_id' => $spkId,
                                    'no_spk' => $noSpk,
                                    'qty' => $qty,
                                    'tgl_selesai' => $data['tgl_selesai'] ?? null, // 🔥 INI
                                ];
                            }
                        }
                    }

                    return [
                        'id' => $item->id,
                        'detail' => $detail,
                        'summary' => $summary,
                    ];
                })
                ->values();

            return [
                'data_po' => [
                    'id' => $po->id,
                    'no_po' => $po->order_no,
                    'company' => $po->company_name,
                    'items' => $items,
                ],
            ];
        });

        return response()->json($result);
    }

    // get spk
    public function spkEdit($id)
    {
        $spk = Spk::findOrFail($id);
        $data = $spk->data; // otomatis array kalau column json cast
        // =========================
        // Mapping ke format blade
        // =========================
        $spkView = [
            'id' => $spk->id,
            'type' => $data['kategori'] ?? '',
            'no_spk' => $data['no_spk'] ?? '',
            'no_po' => $data['no_po'] ?? '',
            'nama' => $data['sup'] ?? '',
            'tgl_terima' => $data['tgl_terima'] ?? '',
            'tgl_selesai' => $data['tgl_selesai'] ?? '',
            'items' => [],
        ];
        // =========================
        // Build item dari JSON
        // =========================
        if (isset($data['item'])) {
            $item = $data['item'];
            $spkView['items'][] = [
                'detail_id' => $item['detail_id'] ?? '',
                'kode' => $item['kode'] ?? '',
                'nama' => $item['nama'] ?? '',
                'p' => $item['p'] ?? '',
                'l' => $item['l'] ?? '',
                't' => $item['t'] ?? '',
                'material' => $item['material'] ?? '',
                'pcs' => $item['pcs'] ?? 0,
                'set' => $item['set'] ?? 0,
                'harga' => $item['harga'] ?? 0,
                'total' => $item['total'] ?? 0,
                'images' => $item['images'] ?? [],
                'catatan' => $item['catatan']['remark'] ?? '',
            ];
        }

        // dd($spkView);
        return view('pages.spk.edit', [
            'spk' => $spkView,
        ]);
    }

    public function getData(Request $request)
    {
        $poId = $request->po_id;
        $detailId = $request->detail_po_id;
        $kategori = strtolower($request->kategori);
        // 🔥 ambil semua SPK dalam PO
        $spks = Spk::where('po_id', $poId)->get();
        $supplierIds = [];
        $spkInfo = [];
        $allSpk = [];
        foreach ($spks as $spk) {
            $data = is_array($spk->data)
                ? $spk->data
                : json_decode($spk->data, true);
            $items = collect($data['items'] ?? []);
            // 🔥 filter detail_po_id
            $item = $items->firstWhere('detail_po_id', $detailId);
            if (!$item) {
                continue; // ❗ skip kalau bukan item ini
            }
            $supplier = Supplier::where('name', $data['sup'])->first();
            if (!$supplier) {
                continue;
            }
            $kategoriSpk = strtolower($data['kategori']);
            // ================= IN (kategori sekarang)
            if ($kategoriSpk === $kategori) {
                $supplierIds[] = $supplier->id;
                $spkInfo[] = [
                    'sup_id' => $supplier->id,
                    'sup' => $supplier->name,
                    'no_spk' => $data['no_spk'],
                    'qty' => $item['qty'] ?? 0,
                    'spk_id' => $spk->id,
                ];
            }
            // ================= OUT (semua kategori tapi tetap detail_po_id sama)
            $allSpk[] = [
                'spk_id' => $spk->id,
                'sup_id' => $supplier->id,
                'sup_name' => $supplier->name,
                'kategori' => $kategoriSpk,
                'no_spk' => $data['no_spk'],
                'qty' => $item['qty'] ?? 0,
            ];
        }
        // 🔥 supplier dropdown (IN)
        $suppliers = Supplier::whereIn('id', $supplierIds)
            ->select('id', 'name')
            ->get();
        // 🔥 timeline
        $timeline = ProductionTimeline::where('po_id', $poId)
            ->where('detail_po_id', $detailId)
            ->where('process', $kategori)
            ->get();

        return response()->json([
            'items' => $timeline,
            'suppliers' => $suppliers,
            'spk_info' => $spkInfo,
            'all_spk' => $allSpk,
        ]);
    }

    public function saveData(Request $request)
    {
        DB::beginTransaction();
        try {
            $poId = $request->po_id;
            $detailId = $request->detail_po_id;
            $process = strtolower($request->process);
            // 🔥 delete dulu biar tidak double
            ProductionTimeline::where('po_id', $poId)
                ->where('detail_po_id', $detailId)
                ->where('process', $process)
                ->delete();
            // ================= IN =================
            foreach ($request->in ?? [] as $row) {
                if (empty($row['qty'])) {
                    continue;
                }
                ProductionTimeline::create([
                    'po_id' => $poId,
                    'detail_po_id' => $detailId,
                    'process' => $process,
                    'type' => 'IN',
                    'sup_id' => $row['supplier'],
                    'qty' => $row['qty'],
                    'date' => $row['tgl'],
                    'remark' => $row['remark'] ?? '-',
                    'spk_id' => $row['spk_id'],
                    'next_process' => null,
                ]);
            }
            // ================= OUT =================
            foreach ($request->out ?? [] as $row) {
                if (empty($row['qty'])) {
                    continue;
                }
                ProductionTimeline::create([
                    'po_id' => $poId,
                    'detail_po_id' => $detailId,
                    'process' => $process,
                    'type' => 'OUT',
                    'sup_id' => $row['supplier'], // 🔥 tujuan supplier
                    'qty' => $row['qty'],
                    'date' => now(),
                    'remark' => $row['remark'] ?? '-',
                    'spk_id' => $row['spk_id'], // 🔥 tujuan spk
                    'next_process' => $row['next_process'],
                ]);
            }
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getDetailBarang(Request $request)
    {
        $po_id = $request->po_id;
        $detail_po_id = $request->detail_po_id;
        $spks = Spk::where('po_id', $po_id)->get();
        $result = [];
        foreach ($spks as $spk) {
            $data = $spk->data;
            foreach ($data['items'] as $item) {
                if ($item['detail_po_id'] == $detail_po_id) {
                    $supplier = Supplier::where('name', $data['sup'])->first();
                    $result[] = [
                        'spk_id' => $spk->id,
                        'supplier' => [
                            'id' => $supplier?->id,
                            'name' => $data['sup'],
                        ],
                        'kategori' => $data['kategori'],
                        'item' => $item,
                    ];
                }
            }
        }
        // =========================
        // 🔥 AMBIL LOG PRODUKSI
        // =========================
        $logs = DB::table('production_timeline as pt')
            ->leftJoin('suppliers as s', 's.id', '=', 'pt.sup_id')
            ->where('pt.po_id', $po_id)
            ->where('pt.detail_po_id', $detail_po_id)
            ->select(
                'pt.date',
                DB::raw("TIME_FORMAT(pt.created_at, '%H:%i') as time"),
                'pt.type',
                'pt.process',
                'pt.next_process',
                'pt.qty',
                's.name as supplier',
                'pt.remark'
            )
            ->orderBy('pt.created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $result,
            'logs' => $logs,
        ]);
    }

    // arsip udah bener

    public function getTimeline(Request $request)
    {
        $po_id = $request->po_id;
        $timeline = ProductionTimeline::select(
            'detail_po_id',
            'process',
            'type',
            'qty',
            'next_process'
        )
            ->where('po_id', $po_id)
            ->get();

        return response()->json($timeline);
    }

    public function getQc(Request $request)
    {
        $po_id = $request->po_id;
        $qc = \App\Models\InspectSchedule::with('kategori')
            ->where('po_id', $po_id)
            ->get()
            ->map(function ($item) {
                return [
                    'detail_po_id' => $item->detail_po_id,
                    'jumlah_inspect' => $item->jumlah_inspect,
                    'passed' => $item->passed,
                    'rejected' => $item->rejected,
                    'tanggal' => $item->tanggal_inspect,
                    // 🔥 langsung ambil dari relasi
                    'kategori' => strtolower(trim($item->kategori?->kategori ?? '')),
                ];
            });

        return response()->json($qc);
    }

    private function getService()
    {
        $client = new Client;
        $client->setAuthConfig(storage_path('app/google-calendar.json'));
        $client->addScope(Calendar::CALENDAR);

        return new Calendar($client);
    }

    /**
     * ===============================
     * TEST DUMMY EVENT
     * ===============================
     */
    public function paymentstore(Request $request)
    {
        DB::beginTransaction();
        try {
            $spk = Spk::findOrFail(
                $request->spk_id
            );
            $pay = $request->payment;
            $data = $spk->data;
            $payments =
                collect($data['payments'] ?? []);
            // =========================
            // FORMAT DATE
            // =========================
            $paymentDate = null;
            if (!empty($pay['date'])) {
                try {
                    $paymentDate =
                        strlen($pay['date']) == 8
                        ? Carbon::createFromFormat(
                            'd/m/y',
                            $pay['date']
                        )
                        : Carbon::createFromFormat(
                            'd/m/Y',
                            $pay['date']
                        );
                } catch (\Exception $e) {
                    $paymentDate = null;
                }
            }
            // =====================================================
            // UNCHECK
            // =====================================================
            if (!$pay['is_request']) {
                // =========================
                // FIND PR BY PAYMENT ID
                // =========================
                $pr = PaymentRequest::where(
                    'payment_id',
                    $pay['payment_id']
                )->first();
                // =========================
                // DELETE PR
                // =========================
                if ($pr) {
                    // sementara bebas delete dulu
                    $pr->delete();
                }
                // =========================
                // UPDATE JSON
                // =========================
                $updatedPayments =
                    $payments->map(function ($item) use ($pay) {
                        if (
                            $item['payment_id']
                            ==
                            $pay['payment_id']
                        ) {
                            $item['is_request'] = false;
                            $item['pr_id'] = null;
                        }

                        return $item;
                    })
                        ->values()
                        ->toArray();
                $data['payments'] =
                    $updatedPayments;
                $spk->update([
                    'data' => $data,
                ]);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Request dibatalkan',
                ]);
            }
            // =====================================================
            // CHECKLIST
            // =====================================================
            $currentPayment =
                $payments->firstWhere(
                    'payment_id',
                    $pay['payment_id']
                );
            // =========================
            // SUDAH ADA PR?
            // =========================
            if (
                !empty($currentPayment['pr_id'])
            ) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'PR sudah ada',
                ]);
            }
            // =========================
            // CREATE PR
            // =========================
            $pr = PaymentRequest::create([
                'spk_id' => $spk->id,
                'payment_id' => $pay['payment_id'],
                'request_no' => $this->generateRequestNo(),
                'no_spk' => $request->no_spk,
                'no_po' => $spk->data['no_po'] ?? null,
                'supplier' => $spk->data['sup'] ?? null,
                'kategori' => $spk->data['kategori'] ?? null,
                // PAYMENT
                'payment_type' => $pay['note'],
                // 'total_amount' =>
                // (int) $pay['amount'],
                'payment_date' => $paymentDate,
                'note' => $pay['note_tambahan'] ?? null,
                // REQUEST
                'request_date' => now(),
                'status' => 'draft',
                'created_by' => auth()->id(),
                'spk_snapshot' => $spk->data,
            ]);
            // =========================
            // UPDATE JSON
            // =========================
            $updatedPayments =
                $payments->map(function ($item) use ($pay, $pr) {
                    if (
                        $item['payment_id']
                        ==
                        $pay['payment_id']
                    ) {
                        $item['is_request'] = true;
                        $item['pr_id'] =
                            $pr->id;
                    }

                    return $item;
                })
                    ->values()
                    ->toArray();
            $data['payments'] =
                $updatedPayments;
            $spk->update([
                'data' => $data,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PR berhasil dibuat',
                'pr_id' => $pr->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveDraftRequest(
        Request $request
    ) {
        DB::beginTransaction();
        try {
            $ids = collect($request->ids)
                ->unique()
                ->values();
            $requests =
                PaymentRequest::with([
                    'items',
                    'spk',
                ])
                    ->whereIn(
                        'id',
                        $ids
                    )
                    ->get();
            foreach ($requests as $row) {
                // =====================
                // UPDATE REQUEST
                // =====================
                $row->update([
                    'request_date' => $request->request_date,
                    'need_date' => $request->need_date,
                    // DRAFT -> PENDING
                    'status' => 'pending',
                ]);
                // =====================
                // UPDATE ITEMS
                // =====================
                $row->items()->update([
                    'status' => 'waiting',
                ]);
                // =====================
                // REMOVE CHECKED TYPES
                // =====================
                $spk = $row->spk;
                if ($spk) {
                    $data = $spk->data;
                    $currentChecked =
                        $data['checked_types'] ?? [];
                    $selectedTypes =
                        $row->checked_types ?? [];
                    // hapus yg sudah diproses
                    $data['checked_types'] = array_values(
                        array_diff(
                            $currentChecked,
                            $selectedTypes
                        )
                    );
                    $spk->update([
                        'data' => $data,
                    ]);
                }
                // =====================
                // SIGNATURE DEFAULT
                // =====================
                $roles = [
                    'made_by',
                    'purchasing',
                    'prod_manager',
                    'ceo',
                    'vp_sales',
                    'finance',
                    'hrd',
                    'coo',
                ];
                foreach ($roles as $role) {
                    PaymentRequestSignature::firstOrCreate([
                        'payment_request_id' => $row->id,
                        'role' => $role,
                    ], [
                        'status' => $role == 'made_by'
                            ? 'approved'
                            : 'pending',
                        'signed_at' => $role == 'made_by'
                            ? now()
                            : null,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request berhasil diajukan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function draftr()
    {
        $authUser = auth()->user();

        $kepalaPurchasing = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'KEPALA PURCHASING');
        })->first();

        $prodManager = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'PROD MANAGER');
        })->first();

        $ceo = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'CEO');
        })->first();

        $vpSales = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'VP SALES & MARKETING');
        })->first();

        $finance = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'FINANCE ACC');
        })->first();

        $hrd = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'HRD GA & SHE');
        })->first();

        $coo = Karyawan::whereHas('divisi', function ($q) {
            $q->where('nama', 'CO');
        })->first();

        /*
        |--------------------------------------------------------------------------
        | REQUEST DRAFT
        |--------------------------------------------------------------------------
        */
        $requests = PaymentRequest::with('spk')
            ->where('status', 'draft')
            ->latest()
            ->get()
            ->map(function ($request) {

                if (!$request->spk) {

                    Log::warning('SPK NOT FOUND', [
                        'payment_request_id' => $request->id,
                        'spk_id' => $request->spk_id,
                    ]);

                    return null;
                }

                $spkData = is_string($request->spk->data)
                    ? json_decode($request->spk->data, true)
                    : ($request->spk->data ?? []);

                $payment = collect(
                    $spkData['payments'] ?? []
                )->firstWhere(
                        'payment_id',
                        $request->payment_id
                    );

                $items = collect(
                    $spkData['items'] ?? []
                )->map(function ($item) {

                    $mainTotal = (float) ($item['total'] ?? 0);

                    $extraTotal = collect(
                        $item['custom_columns'] ?? []
                    )->sum(function ($row) {
                        return (float) ($row['total'] ?? 0);
                    });

                    return [
                        'nama' => $item['nama'] ?? '-',
                        'kode' => $item['kode'] ?? '-',
                        'qty' => $item['qty'] ?? 0,
                        'harga' => $item['harga'] ?? 0,
                        'total' => $mainTotal + $extraTotal,
                    ];
                });

                return [
                    'id' => $request->id,
                    'request_no' => $request->request_no,
                    'payment_id' => $request->payment_id,
                    'status' => $request->status,
                    'request_date' => $request->request_date,
                    'need_date' => $request->need_date,

                    'spk_id' => $request->spk_id,
                    'spk_no' => $spkData['no_spk'] ?? '-',
                    'no_po' => $spkData['no_po'] ?? '-',
                    'supplier' => $spkData['sup'] ?? '-',
                    'kategori' => $spkData['kategori'] ?? '-',
                    'tgl_terima' => $spkData['tgl_terima'] ?? '-',
                    'tgl_selesai' => $spkData['tgl_selesai'] ?? '-',

                    'payment_note' => $payment['note'] ?? '-',
                    'payment_amount' => $payment['amount'] ?? 0,
                    'payment_date' => $payment['date'] ?? null,
                    'payment_is_request' => $payment['is_request'] ?? false,
                    'note_tambahan' => $payment['note_tambahan'] ?? null,

                    'items' => $items,
                    'grand_total_spk' => $items->sum('total'),
                ];
            })
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | SAVED DRAFT
        |--------------------------------------------------------------------------
        */
        $draftRequests = PaymentRequestSaved::latest()
            ->get()
            ->map(function ($draft) {

                $paymentRequests = PaymentRequest::with('spk')
                    ->whereIn(
                        'id',
                        $draft->payment_request_ids ?? []
                    )
                    ->get()
                    ->map(function ($request) {

                        if (!$request->spk) {

                            Log::warning('SPK NOT FOUND IN DRAFT', [
                                'payment_request_id' => $request->id,
                                'spk_id' => $request->spk_id,
                            ]);

                            return null;
                        }

                        $spkData = is_string($request->spk->data)
                            ? json_decode($request->spk->data, true)
                            : ($request->spk->data ?? []);

                        $payment = collect(
                            $spkData['payments'] ?? []
                        )->firstWhere(
                                'payment_id',
                                $request->payment_id
                            );

                        return [
                            'id' => $request->id,
                            'payment_id' => $request->payment_id,
                            'request_no' => $request->request_no,
                            'spk_no' => $spkData['no_spk'] ?? '-',
                            'no_po' => $spkData['no_po'] ?? '-',
                            'supplier' => $spkData['sup'] ?? '-',
                            'kategori' => $spkData['kategori'] ?? '-',
                            'payment_note' => $payment['note'] ?? '-',
                            'payment_amount' => (float) ($payment['amount'] ?? 0),
                        ];
                    })
                    ->filter()
                    ->values();
                $approval = PaymentRequestApproval::where(
                    'payment_request_saved_id',
                    $draft->id
                )
                    ->where('status', 'Pending')
                    ->orderBy('step')
                    ->first();

                return [
                    'id' => $draft->id,
                    'request_no' => $draft->request_no,
                    'request_date' => $draft->request_date,
                    'need_date' => $draft->need_date,
                    'status' => $draft->status,
                    'grand_total' => $paymentRequests->sum('payment_amount'),
                    'total_items' => $paymentRequests->count(),
                    'items' => $paymentRequests,
                    'pending_sign' => $approval->role ?? '-',
                    'ainun_saved_recon' => $draft->ainun_saved_recon,

                ];
            });

        return view(
            'pages.payment_request.draft',
            compact(
                'requests',
                'draftRequests',
                'authUser',
                'kepalaPurchasing',
                'prodManager',
                'ceo',
                'vpSales',
                'finance',
                'hrd',
                'coo'
            )
        );
    }

    public function changeStatus(
        Request $request,
        Spk $spk
    ) {
        try {
            $status = $request->status;
            // =========================
            // VALIDASI
            // =========================
            if (
                !in_array($status, [
                    'draft',
                    'progress',
                    'finished',
                    'closed',
                ])
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status tidak valid',
                ], 422);
            }
            // =========================
            // UPDATE STATUS
            // =========================
            $spk->status = $status;
            // FINISH
            if ($status == 'finished') {
                $spk->finished_at = now();
                $spk->finished_by = auth()->id();
            }
            // CLOSED
            if ($status == 'closed') {
                $spk->finished_at =
                    $spk->finished_at ?? now();
                $spk->finished_by =
                    $spk->finished_by ?? auth()->id();
            }
            $spk->save();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveDraftGroup(
        Request $request
    ) {

        $request->validate([

            'ids' => 'required|array',

            'request_date' => 'required|date',

            'need_date' => 'required|date',
        ]);

        $paymentRequests =
            PaymentRequest::whereIn(
                'id',
                $request->ids
            )->get();

        $grandTotal = 0;

        foreach ($paymentRequests as $paymentRequest) {

            $spkData = is_string(
                $paymentRequest->spk->data
            )
                ? json_decode(
                    $paymentRequest->spk->data,
                    true
                )
                : $paymentRequest->spk->data;

            $payment = collect(
                $spkData['payments'] ?? []
            )->firstWhere(
                    'payment_id',
                    $paymentRequest->payment_id
                );

            $grandTotal += (float) (
                $payment['amount'] ?? 0
            );
        }

        $saved =
            PaymentRequestSaved::create([

                'request_no' => 'DR-' .
                    now()->format('ymdHis'),

                'request_date' => $request->request_date,

                'need_date' => $request->need_date,

                'payment_request_ids' => $request->ids,

                'grand_total' => $grandTotal,

                'status' => 'Diajukan',

                'created_by' => auth()->id(),
            ]);
        // user tap
        $approvers = [

            [
                'user_id' => 171,
                'step' => 1,
                'role' => 'Head Purchasing',
            ],

            [
                'user_id' => 178,
                'step' => 2,
                'role' => 'Production Manager',
            ],
            [
                'user_id' => 191,
                'step' => 3,
                'role' => 'Director',
            ],
            [
                'user_id' => 141,
                'step' => 4,
                'role' => 'General Manager',
            ],
            [
                'user_id' => 134,
                'step' => 5,
                'role' => 'Finance',
            ],

            [
                'user_id' => 190,
                'step' => 6,
                'role' => 'CEO',
            ],

        ];

        foreach ($approvers as $approval) {

            PaymentRequestApproval::create([

                'payment_request_saved_id' => $saved->id,

                'user_id' => $approval['user_id'],

                'step' => $approval['step'],

                'role' => $approval['role'],

                'status' => 'Pending',

            ]);
        }
        PaymentRequest::whereIn(
            'id',
            $request->ids
        )->update([

                    'status' => 'saved',
                ]);

        return response()->json([

            'success' => true,

            'message' => 'Draft berhasil dibuat',

            'id' => $saved->id,
        ]);
    }

    public function detailDraft($id)
    {
        $draft = PaymentRequestSaved::findOrFail($id);

        $approvals = PaymentRequestApproval::with('user')
            ->where('payment_request_saved_id', $draft->id)
            ->orderBy('step')
            ->get();

        $items = PaymentRequest::with('spk')
            ->whereIn('id', $draft->payment_request_ids ?? [])
            ->get()
            ->map(function ($request) {

                // Jika SPK tidak ditemukan
                if (!$request->spk) {
                    return [
                        'supplier' => '-',
                        'spk_id' => $request->spk_id,
                        'no_po' => '-',
                        'spk_no' => '-',
                        'payment_note' => '-',
                        'payment_id' => $request->payment_id,
                        'adjustment' => 0,
                        'payment_amount' => 0,
                        'payment_request_amount' => 0,
                    ];
                }

                $spkData = is_string($request->spk->data)
                    ? json_decode($request->spk->data, true)
                    : $request->spk->data;

                $payment = collect($spkData['payments'] ?? [])
                    ->firstWhere('payment_id', $request->payment_id);

                return [
                    'supplier' => $spkData['sup'] ?? '-',
                    'spk_id' => $request->spk_id,
                    'no_po' => $spkData['no_po'] ?? '-',
                    'spk_no' => $spkData['no_spk'] ?? '-',
                    'payment_note' => $payment['note'] ?? '-',
                    'payment_id' => $request->payment_id,
                    'adjustment' => $payment['adjustment'] ?? 0,
                    'payment_amount' => (float) ($payment['amount'] ?? 0),
                    'payment_request_amount' => !empty($payment['adjustment'])
                        ? (float) $payment['adjustment']
                        : (float) ($payment['amount'] ?? 0),
                ];
            });

        return response()->json([
            'id' => $draft->id,
            'request_no' => $draft->request_no,
            'request_date' => $draft->request_date,
            'need_date' => $draft->need_date,
            'grand_total' => $draft->grand_total,
            'items' => $items,
            'is_finance' => auth()->id() == 134,
            'approvals' => $approvals->map(function ($row) {
                return [
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'name' => optional($row->user)->name,
                    'role' => $row->role,
                    'signature' => optional($row->user)->signature,
                    'status' => $row->status,
                    'approved_at' => $row->approved_at
                        ? $row->approved_at->format('d/m/Y H:i')
                        : null,
                    'can_approve' => auth()->id() == $row->user_id
                        && $row->status == 'Pending',
                ];
            }),
        ]);
    }

    // payment request draft
    private function generateRequestNo()
    {
        $now = now();
        $year = $now->format('y'); // 26
        // =========================
        // AMBIL REQUEST TERAKHIR
        // =========================
        $last = PaymentRequest::where(
            'request_no',
            'like',
            "PR/NW/{$year}/%"
        )
            ->latest('id')
            ->first();
        $nextNumber = 1;
        if ($last) {
            preg_match(
                '/PR\/NW\/\d{2}\/(\d{4})/',
                $last->request_no,
                $match
            );
            if (isset($match[1])) {
                $nextNumber =
                    ((int) $match[1]) + 1;
            }
        }
        // =========================
        // FORMAT 0001
        // =========================
        $urut = str_pad(
            $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );

        // =========================
        // RESULT
        // =========================
        return "PR/NW/{$year}/{$urut}";
    }

    public function calendar()
    {
        $service = $this->getService();
        // ✅ pakai calendar ID kamu yang sudah benar
        $calendarId = '824e23d84ab88f2e4279aba16457256aca6caddd108e8b1118a6756f3dd0920b@group.calendar.google.com';
        // waktu dummy
        $start = now()->addMinutes(2);
        $end = now()->addHour();
        $event = new Event([
            'summary' => '🔥 SPK - Waya',
            'description' => 'Deadline produksi',
            'start' => [
                'dateTime' => $start->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Asia/Jakarta',
            ],
            'end' => [
                'dateTime' => $end->format('Y-m-d\TH:i:s'),
                'timeZone' => 'Asia/Jakarta',
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => 0], // langsung notif
                ],
            ],
        ]);
        $created = $service->events->insert($calendarId, $event);

        return response()->json([
            'status' => 'success',
            'event_id' => $created->getId(),
            'link' => $created->htmlLink,
        ]);
    }

    public function addCalendar()
    {
        $service = $this->getService();
        $calendarId = '824e23d84ab88f2e4279aba16457256aca6caddd108e8b1118a6756f3dd0920b@group.calendar.google.com';
        $entry = new \Google\Service\Calendar\CalendarListEntry;
        $entry->setId($calendarId);
        $service->calendarList->insert($entry);

        return 'Calendar berhasil ditambahkan';
    }

    public function preview($id)
    {
        $spk = Spk::findOrFail($id);

        return view(
            'pages.spk.preview',
            compact('spk')
        );
    }

    public function submitSignature(Request $request, $id)
    {
        $spk = Spk::findOrFail($id);

        $data = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;

        $supplier = Supplier::where(
            'name',
            $data['sup'] ?? ''
        )->first();

        SignatureSpk::updateOrCreate(

            [
                'spk_id' => $spk->id,
            ],

            [
                'supplier_id' => $supplier?->id,

                'made_by' => auth()->id(),
                'made_at' => now(),
                'made_remark' => $request->remark,

                'checked_by' => 171,
                'checked_by_2' => 178,
                'checked_at' => null,
                'checked_at_2' => null,
                'checked_remark' => null,
                'checked_2_remark' => null,
                'approved_by' => 191,
                'approved_at' => null,
                'approved_remark' => null,
            ]
        );

        $spk->update([
            'status' => 'diajukan',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SPK berhasil diajukan',
        ]);
    }

    // approve sign in pengajuan spk
    public function approve($id)
    {
        $urutan = false;

        $approval =
            PaymentRequestApproval::findOrFail($id);

        // hanya user yang berhak
        if (
            $approval->user_id != auth()->id()
        ) {

            return response()->json([

                'success' => false,

                'message' => 'Anda tidak memiliki hak approval',

            ], 403);
        }

        // sudah approve
        if (
            $approval->status == 'Approved'
        ) {

            return response()->json([

                'success' => false,

                'message' => 'Data sudah di approve',

            ], 422);
        }
        if ($urutan) {

            $previous = PaymentRequestApproval::where(
                'payment_request_saved_id',
                $approval->payment_request_saved_id
            )
                ->where('step', $approval->step - 1)
                ->first();

            if ($previous && $previous->status != 'Approved') {

                return response()->json([
                    'success' => false,
                    'message' => 'Menunggu approval sebelumnya',
                ], 422);
            }
        }

        // approve
        $approval->update([

            'status' => 'Approved',

            'approved_at' => now(),

        ]);
        // finn
        // FINANCE APPROVAL
        if ($approval->user_id == 174) {

            $draft = PaymentRequestSaved::find(
                $approval->payment_request_saved_id
            );

            $paymentRequests = PaymentRequest::whereIn(
                'id',
                $draft->payment_request_ids ?? []
            )->get();

            foreach ($paymentRequests as $pr) {

                $spk = Spk::find($pr->spk_id);

                if (!$spk) {
                    continue;
                }

                $data = is_string($spk->data)
                    ? json_decode($spk->data, true)
                    : $spk->data;

                foreach ($data['payments'] as &$payment) {

                    if (
                        ($payment['payment_id'] ?? null)
                        == $pr->payment_id
                    ) {

                        $payment['finance_approved'] = true;
                        $payment['finance_approved_at'] = now()
                            ->format('Y-m-d H:i:s');
                    }
                }

                $spk->update([
                    'data' => $data,
                ]);
            }
        }

        // cek apakah semua sudah approve
        $draft =
            PaymentRequestSaved::find(
                $approval->payment_request_saved_id
            );

        $pendingCount =
            PaymentRequestApproval::where(
                'payment_request_saved_id',
                $draft->id
            )
                ->where(
                    'status',
                    'Pending'
                )
                ->count();

        if ($pendingCount == 0) {

            $draft->update([

                'status' => 'Approved',

            ]);

            PaymentRequest::whereIn(
                'id',
                $draft->payment_request_ids ?? []
            )->update([

                        'status' => 'Approved',

                    ]);
        }

        return response()->json([

            'success' => true,

            'message' => 'Approval berhasil disimpan',

        ]);
    }

    // finance adusment
    public function financeAdjustment(
        Request $request
    ) {
        $spk = Spk::findOrFail(
            $request->spk_id
        );

        $data = is_string(
            $spk->data
        )
            ? json_decode(
                $spk->data,
                true
            )
            : $spk->data;

        foreach (
            $data['payments'] as &$payment
        ) {

            if (
                $payment['payment_id']
                ==
                $request->payment_id
            ) {

                $payment['adjustment'] =
                    (float) 
                    $request->adjustment;

                $payment['adjustment_by'] =
                    auth()->id();

                $payment['adjustment_at'] =
                    now()
                        ->format(
                            'Y-m-d H:i:s'
                        );
            }
        }

        $spk->update([

            'data' => $data,

        ]);

        return response()->json([

            'success' => true,

        ]);
    }

    // approve
    public function signSignature(Request $request, $id)
    {
        $signature = SignatureSpk::findOrFail($id);
        $spk = Spk::findOrFail($signature->spk_id);

        /*
        |--------------------------------------------------------------------------
        | CHECKER 1 (VIVI)
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'checked') {

            if (auth()->id() != $signature->checked_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda bukan Checker 1',
                ], 403);
            }

            $signature->update([
                'checked_at' => now(),
                'checked_remark' => $request->remark,
            ]);

            SpkTimeline::create([
                'spk_id' => $spk->id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => 'checked',
                    'user' => auth()->user()->name,
                    'remark' => $request->remark,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil di-check oleh Checker 1',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECKER 2 (DIDIN)
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'checked_2') {

            if (auth()->id() != $signature->checked_by_2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda bukan Checker 2',
                ], 403);
            }

            $signature->update([
                'checked_at_2' => now(),
                'checked_2_remark' => $request->remark,
            ]);

            SpkTimeline::create([
                'spk_id' => $spk->id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => 'checked_2',
                    'user' => auth()->user()->name,
                    'remark' => $request->remark,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil di-check oleh Checker 2',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | APPROVED (MR STANLEY)
        |--------------------------------------------------------------------------
        */
        if ($request->type === 'approved') {

            if (auth()->id() != 191) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Mr Stanley yang dapat melakukan approval ini',
                ], 403);
            }

            $signature->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approved_remark' => $request->remark,
            ]);

            $spk->update([
                'status' => 'approved',
            ]);

            SpkTimeline::create([
                'spk_id' => $spk->id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => 'approved',
                    'user' => auth()->user()->name,
                    'remark' => $request->remark,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SPK berhasil di-approve',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid request',
        ], 400);
    }

    public function notifications()
    {
        $pfis = Po::query()
            ->whereDate(
                'created_at',
                '>=',
                now()->subDays(7)
            )
            ->latest('created_at')
            ->get()
            ->map(function ($pfi) {

                $shipmentDate =
                    $this->parseShipmentDate(
                        $pfi->shipment_date
                    );

                return [
                    'id' => $pfi->id,
                    'order_no' => $pfi->order_no,

                    'shipment_date' => $shipmentDate
                        ? $shipmentDate->format('d/m/Y')
                        : ($pfi->shipment_date ?: '-'),

                    'created_at' => $pfi->created_at
                        ->format('d/m/Y H:i'),
                ];
            });

        return response()->json($pfis);
    }

    /*
    |--------------------------------------------------------------------------
    | FLEXIBLE DATE PARSER
    |--------------------------------------------------------------------------
    */
    private function parseShipmentDate($value)
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        /*
        |--------------------------------------------------------------------------
        | HAPUS KETERANGAN DALAM KURUNG
        |--------------------------------------------------------------------------
        */

        $value = preg_replace(
            '/\(.*?\)/',
            '',
            $value
        );

        $value = trim($value);

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI BULAN INDONESIA
        |--------------------------------------------------------------------------
        */

        $months = [
            'JANUARI' => 'JANUARY',
            'FEBRUARI' => 'FEBRUARY',
            'MARET' => 'MARCH',
            'APRIL' => 'APRIL',
            'MEI' => 'MAY',
            'JUNI' => 'JUNE',
            'JULI' => 'JULY',
            'AGUSTUS' => 'AUGUST',
            'SEPTEMBER' => 'SEPTEMBER',
            'OKTOBER' => 'OCTOBER',
            'NOVEMBER' => 'NOVEMBER',
            'DESEMBER' => 'DECEMBER',
        ];

        $upper = strtoupper($value);

        foreach ($months as $id => $en) {
            $upper = str_replace(
                $id,
                $en,
                $upper
            );
        }

        $value = $upper;

        /*
        |--------------------------------------------------------------------------
        | COBA PARSE OTOMATIS
        |--------------------------------------------------------------------------
        */

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | FORMAT MANUAL
        |--------------------------------------------------------------------------
        */

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/n/Y',
            'd-n-Y',
            'j/n/Y',
            'j-n-Y',
        ];

        foreach ($formats as $format) {

            try {

                return Carbon::createFromFormat(
                    $format,
                    $value
                );

            } catch (\Exception $e) {
                //
            }
        }

        return null;
    }

    public function indexloading()
    {
        return view('pages.loading.index');
    }

    public function generateLoading(Request $request)
    {
        $containers = [

            '20FT' => [
                'length' => 589,
                'width' => 235,
                'height' => 239,
            ],

            '40HC' => [
                'length' => 1203,
                'width' => 235,
                'height' => 269,
            ],

        ];

        $container =
            $containers[
                $request->container
            ];

        $items3d = [];

        $totalCbm = 0;
        $totalCarton = 0;

        $x = 0;
        $y = 0;
        $z = 0;

        foreach (
            $request->items as $item
        ) {
            $cbm =
                (
                    $item['length'] *
                    $item['width'] *
                    $item['height']
                ) / 1000000;

            $totalCbm +=
                $cbm *
                $item['qty'];

            $totalCarton +=
                $item['qty'];

            for (
                $i = 0;
                $i < $item['qty'];
                $i++
            ) {
                $items3d[] = [

                    'name' => $item['name'],

                    'length' => $item['length'],

                    'width' => $item['width'],

                    'height' => $item['height'],

                    'x' => $x,
                    'y' => $y,
                    'z' => $z,

                    'color' => sprintf(
                        '0x%06X',
                        mt_rand(
                            0,
                            0xFFFFFF
                        )
                    ),
                ];

                $x +=
                    $item['length'];

                if (
                    $x +
                    $item['length']
                    >
                    $container['length']
                ) {
                    $x = 0;
                    $z +=
                        $item['width'];
                }

                if (
                    $z +
                    $item['width']
                    >
                    $container['width']
                ) {
                    $z = 0;
                    $y +=
                        $item['height'];
                }
            }
        }

        $containerVolume =
            (
                $container['length']
                *
                $container['width']
                *
                $container['height']
            ) / 1000000;

        return response()->json([

            'po_name' => $request->po_name,

            'container' => $container,

            'items' => $items3d,

            'total_cbm' => round(
                $totalCbm,
                2
            ),

            'total_carton' => $totalCarton,

            'utilization' => round(
                (
                    $totalCbm /
                    $containerVolume
                ) * 100,
                2
            ),

        ]);
    }
    // finance export 
    public function exportPengajuanSpk($id)
    {
        $saved = PaymentRequestSaved::findOrFail($id);

        return ExportPengajuanSpk::export(
            $saved
        );
    }
    public function exportAllPaymentRequest()
    {
        // dd()
        return ExportAllPaymentRequest::export();
    }

    // rekon
    public function setSavedRecon($id)
    {
        try {

            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | 1. AMBIL PAYMENT REQUEST SAVED
            |--------------------------------------------------------------------------
            */

            $saved = PaymentRequestSaved::findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | 2. AMBIL PAYMENT REQUEST IDS
            |--------------------------------------------------------------------------
            */

            $paymentRequestIds = $saved->payment_request_ids;

            if (is_string($paymentRequestIds)) {

                $paymentRequestIds = json_decode(
                    $paymentRequestIds,
                    true
                );
            }

            $paymentRequestIds = array_values(
                array_filter(
                    (array) $paymentRequestIds
                )
            );


            if (empty($paymentRequestIds)) {

                throw new \Exception(
                    'Payment Request IDs tidak ditemukan pada Saved.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 3. AMBIL PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            $paymentRequests = PaymentRequest::whereIn(
                'id',
                $paymentRequestIds
            )
                ->orderBy('id')
                ->get();


            if ($paymentRequests->isEmpty()) {

                throw new \Exception(
                    'Payment Request tidak ditemukan.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | HASIL
            |--------------------------------------------------------------------------
            */

            $inserted = [];

            $updated = [];

            $skipped = [];

            $totalNominal = 0;


            /*
            |--------------------------------------------------------------------------
            | 4. LOOP SEMUA PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            foreach ($paymentRequests as $paymentRequest) {

                /*
                |--------------------------------------------------------------------------
                | SPK ID
                |--------------------------------------------------------------------------
                */

                $spkId = $paymentRequest->spk_id;


                if (!$spkId) {

                    $skipped[] = [

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'reason' =>
                            'SPK ID kosong.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 5. AMBIL SPK ASLI
                |--------------------------------------------------------------------------
                |
                | BUKAN spk_snapshot.
                |
                | Adjustment terbaru berada di SPK.
                |
                */

                $spk = Spk::find($spkId);


                if (!$spk) {

                    $skipped[] = [

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'spk_id' =>
                            $spkId,

                        'reason' =>
                            'SPK tidak ditemukan.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 6. DATA SPK
                |--------------------------------------------------------------------------
                */

                $spkData = $spk->data;


                if (is_string($spkData)) {

                    $spkData = json_decode(
                        $spkData,
                        true
                    );
                }


                if (!is_array($spkData)) {
                    $spkData = [];
                }


                /*
                |--------------------------------------------------------------------------
                | 7. PAYMENT DARI SPK ASLI
                |--------------------------------------------------------------------------
                */

                $payments =
                    $spkData['payments']
                    ?? [];


                if (
                    !is_array($payments) ||
                    empty($payments)
                ) {

                    $skipped[] = [

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'spk_id' =>
                            $spkId,

                        'reason' =>
                            'Payment pada SPK tidak ditemukan.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 8. PAYMENT ID PAYMENT REQUEST
                |--------------------------------------------------------------------------
                */

                $paymentId =
                    $paymentRequest->payment_id;


                /*
                |--------------------------------------------------------------------------
                | 9. CARI PAYMENT DI SPK
                |--------------------------------------------------------------------------
                */

                $matchedPayment = null;


                foreach ($payments as $payment) {

                    if (
                        !empty($paymentId)
                        &&
                        ($payment['payment_id'] ?? null)
                        === $paymentId
                    ) {

                        $matchedPayment = $payment;

                        break;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | 10. FALLBACK BERDASARKAN PR_ID
                |--------------------------------------------------------------------------
                */

                if (!$matchedPayment) {

                    foreach ($payments as $payment) {

                        if (
                            isset($payment['pr_id'])
                            &&
                            $payment['pr_id'] !== null
                            &&
                            (string) $payment['pr_id']
                            ===
                            (string) $paymentRequest->id
                        ) {

                            $matchedPayment = $payment;

                            break;
                        }
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | 11. PAYMENT TIDAK DITEMUKAN
                |--------------------------------------------------------------------------
                */

                if (!$matchedPayment) {

                    $skipped[] = [

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'spk_id' =>
                            $spkId,

                        'payment_id' =>
                            $paymentId,

                        'reason' =>
                            'Payment Request tidak cocok dengan payment pada SPK.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 12. AMOUNT ASLI SPK
                |--------------------------------------------------------------------------
                */

                $amount = (float) (
                    $matchedPayment['amount']
                    ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | 13. ADJUSTMENT DARI SPK
                |--------------------------------------------------------------------------
                */

                $adjustment = (float) (
                    $matchedPayment['adjustment']
                    ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | 14. TENTUKAN NOMINAL KREDIT
                |--------------------------------------------------------------------------
                */

                if ($adjustment > 0) {

                    /*
                    | Ada recon Ainun
                    */

                    $nominal =
                        $adjustment;

                    $ketExtra =
                        'RECON';

                } else {

                    /*
                    | Tidak ada recon
                    */

                    $nominal =
                        $amount;

                    $ketExtra =
                        null;
                }


                /*
                |--------------------------------------------------------------------------
                | 15. VALIDASI NOMINAL
                |--------------------------------------------------------------------------
                */

                if ($nominal <= 0) {

                    $skipped[] = [

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'spk_id' =>
                            $spkId,

                        'payment_id' =>
                            $paymentId,

                        'amount' =>
                            $amount,

                        'adjustment' =>
                            $adjustment,

                        'reason' =>
                            'Nominal payment 0.',
                    ];

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 16. KETERANGAN PAYMENT
                |--------------------------------------------------------------------------
                */

                $ket = strtoupper(
                    trim(
                        $matchedPayment['note']
                        ?? 'PAYMENT'
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | 17. CEK APAKAH SUDAH ADA DI KREDIT
                |--------------------------------------------------------------------------
                */

                $existing = Kredit::query()
                    ->where(
                        'spk_id',
                        $spkId
                    )
                    ->where(
                        'payment_requests_id',
                        $paymentRequest->id
                    )
                    ->where(
                        'payment_request_saved_id',
                        $saved->id
                    )
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | 18. UPDATE JIKA SUDAH ADA
                |--------------------------------------------------------------------------
                */

                if ($existing) {

                    $existing->nominal =
                        $nominal;

                    $existing->ket =
                        $ket;

                    $existing->ket_extra =
                        $ketExtra;

                    $existing->save();


                    $updated[] = [

                        'kredit_id' =>
                            $existing->id,

                        'spk_id' =>
                            $spkId,

                        'payment_request_id' =>
                            $paymentRequest->id,

                        'payment_id' =>
                            $paymentId,

                        'amount_asli' =>
                            $amount,

                        'adjustment_recon' =>
                            $adjustment,

                        'nominal_kredit' =>
                            $nominal,

                        'ket' =>
                            $ket,

                        'ket_extra' =>
                            $ketExtra,
                    ];


                    $totalNominal +=
                        $nominal;


                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | 19. INSERT KE TABEL KREDIT
                |--------------------------------------------------------------------------
                */

                $kredit = Kredit::create([

                    'spk_id' =>
                        $spkId,

                    'payment_requests_id' =>
                        $paymentRequest->id,

                    'nominal' =>
                        $nominal,

                    'ket' =>
                        $ket,

                    'payment_request_saved_id' =>
                        $saved->id,

                    'ket_extra' =>
                        $ketExtra,

                ]);


                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $totalNominal +=
                    $nominal;


                /*
                |--------------------------------------------------------------------------
                | HASIL INSERT
                |--------------------------------------------------------------------------
                */

                $inserted[] = [

                    'kredit_id' =>
                        $kredit->id,

                    'spk_id' =>
                        $spkId,

                    'payment_request_id' =>
                        $paymentRequest->id,

                    'payment_request_saved_id' =>
                        $saved->id,

                    'payment_id' =>
                        $paymentId,

                    'no_spk' =>
                        $spkData['no_spk']
                        ?? $paymentRequest->no_spk,

                    'supplier' =>
                        $spkData['sup']
                        ?? $paymentRequest->supplier,

                    'tanggal' =>
                        $matchedPayment['date']
                        ?? null,

                    'amount_asli' =>
                        $amount,

                    'adjustment_recon' =>
                        $adjustment,

                    'nominal_kredit' =>
                        $nominal,

                    'ket' =>
                        $ket,

                    'ket_extra' =>
                        $ketExtra,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 20. SET AINUN RECON SELESAI
            |--------------------------------------------------------------------------
            |
            | SETELAH BULK KREDIT BERHASIL
            |
            */

            $saved->ainun_saved_recon = 1;

            $saved->save();


            /*
            |--------------------------------------------------------------------------
            | 21. COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 22. RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Recon Ainun berhasil diselesaikan dan data berhasil masuk ke Kredit.',

                'saved_id' =>
                    $saved->id,

                'request_no' =>
                    $saved->request_no,

                'ainun_saved_recon' =>
                    $saved->ainun_saved_recon,

                'payment_request_count' =>
                    count($paymentRequestIds),

                'inserted_count' =>
                    count($inserted),

                'updated_count' =>
                    count($updated),

                'skipped_count' =>
                    count($skipped),

                'total_nominal' =>
                    $totalNominal,

                'inserted' =>
                    $inserted,

                'updated' =>
                    $updated,

                'skipped' =>
                    $skipped,

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error(
                'GAGAL SET AINUN RECON + BULK KREDIT',
                [
                    'saved_id' =>
                        $id,

                    'error' =>
                        $e->getMessage(),

                    'line' =>
                        $e->getLine(),

                    'file' =>
                        $e->getFile(),
                ]
            );


            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Gagal menyelesaikan recon Ainun.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }
    // hutang
    public function hutang()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. AMBIL SEMUA SPK
        |--------------------------------------------------------------------------
        */

        $spks = Spk::query()
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 2. AMBIL SEMUA KREDIT
        |--------------------------------------------------------------------------
        |
        | Tabel kredit adalah sumber pembayaran Kreditor.
        |
        | Termasuk:
        | - Sebelum ERP
        | - Payment Request
        | - Recon
        |
        */

        $kredits = Kredit::query()
            ->orderBy('id')
            ->get()
            ->groupBy('spk_id');


        /*
        |--------------------------------------------------------------------------
        | 3. PAYMENT REQUEST YANG TERKAIT KREDIT
        |--------------------------------------------------------------------------
        */

        $paymentRequestIds = $kredits
            ->flatten()
            ->pluck('payment_requests_id')
            ->filter()
            ->unique()
            ->values();


        $paymentRequests = collect();


        if ($paymentRequestIds->isNotEmpty()) {

            $paymentRequests = PaymentRequest::query()
                ->whereIn(
                    'id',
                    $paymentRequestIds
                )
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | 4. PAYMENT REQUEST SAVED
        |--------------------------------------------------------------------------
        */

        $savedIds = $kredits
            ->flatten()
            ->pluck('payment_request_saved_id')
            ->filter()
            ->unique()
            ->values();


        $savedRequests = collect();


        if ($savedIds->isNotEmpty()) {

            $savedRequests = PaymentRequestSaved::query()
                ->whereIn(
                    'id',
                    $savedIds
                )
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | 5. ROW KREDITOR
        |--------------------------------------------------------------------------
        */

        $rows = collect();


        foreach ($spks as $spk) {

            /*
            |--------------------------------------------------------------------------
            | DATA SPK
            |--------------------------------------------------------------------------
            */

            $data = $spk->data;


            if (is_string($data)) {

                $data = json_decode(
                    $data,
                    true
                );
            }


            if (!is_array($data)) {

                $data = [];
            }


            /*
            |--------------------------------------------------------------------------
            | IDENTITAS SPK
            |--------------------------------------------------------------------------
            */

            $noSpk = $data['no_spk']
                ?? $spk->no_spk
                ?? '-';


            $noPo = $data['no_po']
                ?? $spk->no_po
                ?? '-';


            $supplier = $data['sup']
                ?? $spk->supplier
                ?? '-';


            $kategori = $data['kategori']
                ?? $spk->kategori
                ?? 'SPK';


            /*
            |--------------------------------------------------------------------------
            | TIMELINE SPK
            |--------------------------------------------------------------------------
            */

            $tglTerima =
                $data['tgl_terima']
                ?? null;


            $tglSelesai =
                $data['tgl_selesai']
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */

            $items =
                $data['items']
                ?? [];


            if (!is_array($items)) {

                $items = [];
            }


            /*
            |--------------------------------------------------------------------------
            | TOTAL PEMBELIAN
            |--------------------------------------------------------------------------
            */

            $pembelian = 0;


            foreach ($items as $item) {

                $qty = (float) (
                    $item['qty']
                    ?? 0
                );


                $harga = (float) (
                    $item['harga']
                    ?? 0
                );


                $totalItem = isset(
                    $item['total']
                )
                    ? (float) $item['total']
                    : (
                        $qty * $harga
                    );


                $pembelian +=
                    $totalItem;
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENTS SPK
            |--------------------------------------------------------------------------
            */

            $payments =
                $data['payments']
                ?? [];


            if (!is_array($payments)) {

                $payments = [];
            }


            /*
            |--------------------------------------------------------------------------
            | POTONGAN BAHAN
            |--------------------------------------------------------------------------
            */

            $potonganBahan = 0;

            $timelineBahan = [];


            foreach ($payments as $payment) {

                $note = strtolower(
                    trim(
                        $payment['note']
                        ?? ''
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | PAYMENT BAHAN
                |--------------------------------------------------------------------------
                */

                if (
                    $note === 'bahan'
                    ||
                    str_contains(
                        $note,
                        'bahan'
                    )
                ) {

                    $amount = (float) (
                        $payment['amount']
                        ?? 0
                    );


                    $adjustment = (float) (
                        $payment['adjustment']
                        ?? 0
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | JIKA ADA ADJUSTMENT
                    |--------------------------------------------------------------------------
                    */

                    $nominalBahan =
                        $adjustment > 0
                        ? $adjustment
                        : $amount;


                    $potonganBahan +=
                        $nominalBahan;


                    $timelineBahan[] = [

                        'date' =>
                            $payment['date']
                            ?? null,

                        'amount' =>
                            $amount,

                        'adjustment' =>
                            $adjustment,

                        'nominal' =>
                            $nominalBahan,

                        'payment_id' =>
                            $payment['payment_id']
                            ?? null,

                        'note' =>
                            $payment['note']
                            ?? 'Bahan',

                        'note_tambahan' =>
                            $payment['note_tambahan']
                            ?? null,
                    ];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | KREDIT SPK
            |--------------------------------------------------------------------------
            */

            $spkKredits = $kredits->get(
                $spk->id,
                collect()
            );


            /*
            |--------------------------------------------------------------------------
            | DETAIL SEBELUM ERP
            |--------------------------------------------------------------------------
            */

            $sebelumErpDetails = $spkKredits
                ->filter(function ($kredit) {

                    return strtolower(
                        trim(
                            $kredit->ket_extra
                            ?? ''
                        )
                    ) === 'sebelum erp';

                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | TOTAL SEBELUM ERP
            |--------------------------------------------------------------------------
            */

            $totalSebelumErp =
                $sebelumErpDetails->sum(
                    function ($kredit) {

                        return (float) (
                            $kredit->nominal
                            ?? 0
                        );
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | DETAIL PEMBAYARAN PENGAJUAN
            |--------------------------------------------------------------------------
            |
            | Semua kredit selain Sebelum ERP.
            |
            */

            $pengajuanDetails = $spkKredits
                ->filter(function ($kredit) {

                    return strtolower(
                        trim(
                            $kredit->ket_extra
                            ?? ''
                        )
                    ) !== 'sebelum erp';

                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | TOTAL PEMBAYARAN
            |--------------------------------------------------------------------------
            |
            | SEMUA yang ada di tabel kredit.
            |
            */

            $pembayaran =
                $spkKredits->sum(
                    function ($kredit) {

                        return (float) (
                            $kredit->nominal
                            ?? 0
                        );
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | DETAIL PEMBAYARAN UNTUK HOVER
            |--------------------------------------------------------------------------
            */

            $paymentDetails = [];


            foreach ($spkKredits as $kredit) {

                $isSebelumErp =
                    strtolower(
                        trim(
                            $kredit->ket_extra
                            ?? ''
                        )
                    ) === 'sebelum erp';


                /*
                |--------------------------------------------------------------------------
                | PAYMENT REQUEST
                |--------------------------------------------------------------------------
                */

                $paymentRequest = null;


                if ($kredit->payment_requests_id) {

                    $paymentRequest =
                        $paymentRequests->get(
                            $kredit->payment_requests_id
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | SAVED
                |--------------------------------------------------------------------------
                */

                $saved = null;


                if ($kredit->payment_request_saved_id) {

                    $saved =
                        $savedRequests->get(
                            $kredit->payment_request_saved_id
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | RECON
                |--------------------------------------------------------------------------
                */

                $isRecon =
                    strtoupper(
                        trim(
                            $kredit->ket_extra
                            ?? ''
                        )
                    ) === 'RECON';


                /*
                |--------------------------------------------------------------------------
                | REQUEST NO
                |--------------------------------------------------------------------------
                */

                $requestNo =
                    $paymentRequest->request_no
                    ?? $saved->request_no
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | REQUEST DATE
                |--------------------------------------------------------------------------
                */

                $requestDate = null;


                if (
                    $paymentRequest
                    &&
                    $paymentRequest->request_date
                ) {

                    $requestDate =
                        $paymentRequest
                            ->request_date
                            ->format('d/m/Y');

                } elseif (
                    $saved
                    &&
                    $saved->request_date
                ) {

                    $requestDate =
                        $saved
                            ->request_date
                            ->format('d/m/Y');
                }


                /*
                |--------------------------------------------------------------------------
                | PAYMENT DETAIL
                |--------------------------------------------------------------------------
                */

                $paymentDetails[] = [

                    'kredit_id' =>
                        $kredit->id,

                    'payment_request_id' =>
                        $kredit->payment_requests_id,

                    'saved_id' =>
                        $kredit->payment_request_saved_id,

                    'request_no' =>
                        $requestNo,

                    'request_date' =>
                        $requestDate,

                    'nominal' =>
                        (float) (
                            $kredit->nominal
                            ?? 0
                        ),

                    'ket' =>
                        $kredit->ket
                        ?? '-',

                    'ket_extra' =>
                        $kredit->ket_extra
                        ?? null,

                    'is_sebelum_erp' =>
                        $isSebelumErp,

                    'is_recon' =>
                        $isRecon,

                    'saved_recon' =>
                        $saved
                        ? (
                            (int) 
                            $saved->ainun_saved_recon === 1
                        )
                        : false,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL RECON
            |--------------------------------------------------------------------------
            */

            $reconDetails =
                collect(
                    $paymentDetails
                )
                    ->filter(function ($payment) {

                        return $payment['is_recon'] === true;
                    })
                    ->values()
                    ->toArray();


            /*
            |--------------------------------------------------------------------------
            | TOTAL RECON
            |--------------------------------------------------------------------------
            */

            $totalRecon =
                collect(
                    $reconDetails
                )->sum(function ($item) {

                    return (float) (
                        $item['nominal']
                        ?? 0
                    );
                });


            /*
            |--------------------------------------------------------------------------
            | SALDO AKHIR
            |--------------------------------------------------------------------------
            |
            | Pembelian
            | - Potongan Bahan
            | - Semua Kredit
            |
            */

            $saldoAkhir =
                $pembelian
                -
                $potonganBahan
                -
                $pembayaran;


            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $term =
                $saldoAkhir <= 0
                ? 'Lunas'
                : 'Belum Lunas';


            /*
            |--------------------------------------------------------------------------
            | SORT TIMELINE BAHAN
            |--------------------------------------------------------------------------
            */

            usort(
                $timelineBahan,
                function ($a, $b) {

                    return strcmp(
                        (string) (
                            $a['date']
                            ?? ''
                        ),
                        (string) (
                            $b['date']
                            ?? ''
                        )
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | PUSH ROW
            |--------------------------------------------------------------------------
            */

            $rows->push([

                'no' =>
                    $rows->count() + 1,

                'spk_id' =>
                    $spk->id,

                'kategori' =>
                    $kategori,

                'tgl_invoice' =>
                    $tglTerima,

                'no_spk' =>
                    $noSpk,

                'no_invoice_spk' =>
                    $noSpk,

                'no_po' =>
                    $noPo,

                'tanggal_jt' =>
                    $tglSelesai,

                'supplier' =>
                    $supplier,


                /*
                |--------------------------------------------------------------------------
                | KEUANGAN
                |--------------------------------------------------------------------------
                */

                'pembelian' =>
                    $pembelian,

                'potongan_bahan' =>
                    $potonganBahan,

                'pembayaran' =>
                    $pembayaran,

                'saldo_akhir' =>
                    $saldoAkhir,

                'term' =>
                    $term,


                /*
                |--------------------------------------------------------------------------
                | SEBELUM ERP
                |--------------------------------------------------------------------------
                */

                'sebelum_erp' =>
                    $totalSebelumErp,

                'sebelum_erp_details' =>
                    $sebelumErpDetails
                        ->map(function ($kredit) {

                            return [

                                'id' =>
                                    $kredit->id,

                                'nominal' =>
                                    (float) $kredit->nominal,

                                'ket' =>
                                    $kredit->ket,

                                'ket_extra' =>
                                    $kredit->ket_extra,

                            ];

                        })
                        ->values()
                        ->toArray(),


                /*
                |--------------------------------------------------------------------------
                | PAYMENT DETAIL
                |--------------------------------------------------------------------------
                */

                'payment_details' =>
                    $paymentDetails,

                'pengajuan_details' =>
                    $pengajuanDetails
                        ->map(function ($kredit) {

                            return [

                                'id' =>
                                    $kredit->id,

                                'payment_request_id' =>
                                    $kredit->payment_requests_id,

                                'saved_id' =>
                                    $kredit->payment_request_saved_id,

                                'nominal' =>
                                    (float) $kredit->nominal,

                                'ket' =>
                                    $kredit->ket,

                                'ket_extra' =>
                                    $kredit->ket_extra,

                            ];

                        })
                        ->values()
                        ->toArray(),


                /*
                |--------------------------------------------------------------------------
                | RECON
                |--------------------------------------------------------------------------
                */

                'recon_details' =>
                    $reconDetails,

                'total_recon' =>
                    $totalRecon,


                /*
                |--------------------------------------------------------------------------
                | BAHAN
                |--------------------------------------------------------------------------
                */

                'timeline_bahan' =>
                    $timelineBahan,

                'total_timeline_bahan' =>
                    count($timelineBahan),

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $totalSpk =
            $rows->sum(function ($row) {

                return (float) (
                    $row['pembelian']
                    ?? 0
                );
            });


        $totalPotonganBahan =
            $rows->sum(function ($row) {

                return (float) (
                    $row['potongan_bahan']
                    ?? 0
                );
            });


        $totalPembayaran =
            $rows->sum(function ($row) {

                return (float) (
                    $row['pembayaran']
                    ?? 0
                );
            });


        $totalSebelumErp =
            $rows->sum(function ($row) {

                return (float) (
                    $row['sebelum_erp']
                    ?? 0
                );
            });


        $totalHutang =
            $rows->sum(function ($row) {

                return (float) (
                    $row['saldo_akhir']
                    ?? 0
                );
            });


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.kreditor.index',
            compact(
                'rows',
                'totalSpk',
                'totalPotonganBahan',
                'totalPembayaran',
                'totalSebelumErp',
                'totalHutang'
            )
        );
    }
    private function findOriginalPaymentAmount(
        $payments,
        $paymentId
    ) {
        if (
            empty($paymentId)
            ||
            !is_array($payments)
        ) {
            return 0;
        }


        foreach ($payments as $payment) {

            if (
                ($payment['payment_id'] ?? null)
                === $paymentId
            ) {

                return (float) (
                    $payment['amount']
                    ?? 0
                );
            }
        }


        return 0;
    }
    // jumping 


    public function jump()
    {
        $spks = Spk::query()
            ->orderBy('id')
            ->get();

        $result = [];

        foreach ($spks as $spk) {

            $data = $spk->data;

            if (is_string($data)) {
                $data = json_decode($data, true) ?? [];
            }

            if (!is_array($data)) {
                continue;
            }

            $payments = $data['payments'] ?? [];

            if (!is_array($payments) || count($payments) < 2) {
                continue;
            }

            $isJanggal = false;

            foreach ($payments as $i => $payment) {

                if (!isset($payments[$i + 1])) {
                    continue;
                }

                $current = filter_var(
                    $payment['is_request'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $next = filter_var(
                    $payments[$i + 1]['is_request'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                if (
                    $current === false &&
                    $next === true
                ) {
                    $isJanggal = true;
                    break;
                }
            }

            if (!$isJanggal) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CARI POSISI FALSE -> TRUE
            |--------------------------------------------------------------------------
            */

            $transitionIndex = null;

            foreach ($payments as $i => $payment) {

                if (!isset($payments[$i + 1])) {
                    continue;
                }

                $current = filter_var(
                    $payment['is_request'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                $next = filter_var(
                    $payments[$i + 1]['is_request'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                if (
                    $current === false &&
                    $next === true
                ) {
                    $transitionIndex = $i;
                    break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PAYMENT SEBELUM ERP
            |--------------------------------------------------------------------------
            */

            $beforeErp = [];

            if ($transitionIndex !== null) {

                for ($i = 0; $i <= $transitionIndex; $i++) {

                    $payment = $payments[$i];

                    $beforeErp[] = [
                        'index' => $i,
                        'date' => $payment['date'] ?? null,
                        'note' => $payment['note'] ?? null,
                        'amount' => (float) (
                            $payment['amount'] ?? 0
                        ),
                        'is_request' => filter_var(
                            $payment['is_request'] ?? false,
                            FILTER_VALIDATE_BOOLEAN
                        ),
                        'payment_id' => $payment['payment_id'] ?? null,
                        'pr_id' => $payment['pr_id'] ?? null,
                    ];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PAYMENT PERTAMA YANG REQUEST
            |--------------------------------------------------------------------------
            */

            $firstRequest = null;

            if (
                $transitionIndex !== null &&
                isset($payments[$transitionIndex + 1])
            ) {

                $payment = $payments[$transitionIndex + 1];

                $firstRequest = [
                    'index' =>
                        $transitionIndex + 1,

                    'date' =>
                        $payment['date'] ?? null,

                    'note' =>
                        $payment['note'] ?? null,

                    'amount' =>
                        (float) (
                            $payment['amount'] ?? 0
                        ),

                    'is_request' =>
                        true,

                    'payment_id' =>
                        $payment['payment_id'] ?? null,

                    'pr_id' =>
                        $payment['pr_id'] ?? null,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL SEBELUM ERP
            |--------------------------------------------------------------------------
            */

            $beforeErpTotal = collect($beforeErp)
                ->sum('amount');

            /*
            |--------------------------------------------------------------------------
            | SELURUH PAYMENT UNTUK TIMELINE
            |--------------------------------------------------------------------------
            */

            $paymentTimeline = collect($payments)
                ->map(function ($payment, $index) {

                    return [
                        'index' =>
                            $index,

                        'date' =>
                            $payment['date'] ?? null,

                        'note' =>
                            $payment['note'] ?? null,

                        'amount' =>
                            (float) (
                                $payment['amount'] ?? 0
                            ),

                        'is_request' =>
                            filter_var(
                                $payment['is_request'] ?? false,
                                FILTER_VALIDATE_BOOLEAN
                            ),

                        'payment_id' =>
                            $payment['payment_id'] ?? null,

                        'pr_id' =>
                            $payment['pr_id'] ?? null,

                        'adjustment' =>
                            (float) (
                                $payment['adjustment'] ?? 0
                            ),
                    ];

                })
                ->values()
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | RESULT
            |--------------------------------------------------------------------------
            */

            $result[] = [

                'SPK_ID' =>
                    $spk->id,

                'NO_SPK' =>
                    $data['no_spk'] ?? null,

                'NO_PO' =>
                    $data['no_po'] ?? null,

                'SUPPLIER' =>
                    $data['sup'] ?? null,

                'KATEGORI' =>
                    $data['kategori'] ?? null,

                'TRANSITION_INDEX' =>
                    $transitionIndex,

                'BEFORE_ERP' =>
                    $beforeErp,

                'BEFORE_ERP_TOTAL' =>
                    $beforeErpTotal,

                'FIRST_REQUEST' =>
                    $firstRequest,

                'PAYMENT_TIMELINE' =>
                    $paymentTimeline,
            ];
        }

        return view(
            'pages.kreditor.janggal',
            compact('result')
        );
    }
    // bulk sementara
    public function bulkGenerate($status = 'dp')
    {
        $status = strtolower(trim($status));

        if (!in_array($status, ['dp', 'kasbon'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status hanya boleh dp atau kasbon.',
            ], 422);
        }

        $spks = Spk::query()
            ->orderBy('id')
            ->get();

        $inserted = [];
        $skipped = [];

        DB::beginTransaction();

        try {

            foreach ($spks as $spk) {

                $data = $spk->data;

                if (is_string($data)) {
                    $data = json_decode($data, true) ?? [];
                }

                if (!is_array($data)) {
                    continue;
                }

                $payments = $data['payments'] ?? [];

                if (!is_array($payments) || empty($payments)) {
                    continue;
                }


                foreach ($payments as $payment) {

                    $note = strtolower(
                        trim($payment['note'] ?? '')
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | FILTER SESUAI STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($status === 'dp') {

                        if ($note !== 'dp') {
                            continue;
                        }

                        $ket = 'DP';

                    } else {

                        if ($note !== 'kasbon') {
                            continue;
                        }

                        $ket = 'KASBON';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HANYA PAYMENT YANG BELUM REQUEST
                    |--------------------------------------------------------------------------
                    */

                    $isRequest = filter_var(
                        $payment['is_request'] ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    );

                    if ($isRequest) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NOMINAL
                    |--------------------------------------------------------------------------
                    */

                    $nominal = (float) (
                        $payment['amount'] ?? 0
                    );

                    if ($nominal <= 0) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT ID
                    |--------------------------------------------------------------------------
                    */

                    $paymentId =
                        $payment['payment_id']
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | CEK DUPLIKASI
                    |--------------------------------------------------------------------------
                    */

                    $query = Kredit::where('spk_id', $spk->id)
                        ->where('ket', $ket)
                        ->where('ket_extra', 'Sebelum ERP')
                        ->where('nominal', $nominal);

                    if ($paymentId) {

                        /*
                        | Karena tabel kredit saat ini belum punya
                        | kolom payment_id, sementara gunakan
                        | kombinasi SPK + ket + nominal.
                        */

                        $alreadyExists = $query->exists();

                    } else {

                        $alreadyExists = $query->exists();
                    }


                    if ($alreadyExists) {

                        $skipped[] = [

                            'spk_id' =>
                                $spk->id,

                            'no_spk' =>
                                $data['no_spk'] ?? null,

                            'supplier' =>
                                $data['sup'] ?? null,

                            'payment_id' =>
                                $paymentId,

                            'nominal' =>
                                $nominal,

                            'ket' =>
                                $ket,

                            'reason' =>
                                'Sudah ada',

                        ];

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | INSERT
                    |--------------------------------------------------------------------------
                    */

                    $kredit = Kredit::create([

                        'spk_id' =>
                            $spk->id,

                        'payment_requests_id' =>
                            null,

                        'nominal' =>
                            $nominal,

                        'ket' =>
                            $ket,

                        'payment_request_saved_id' =>
                            null,

                        'ket_extra' =>
                            'Sebelum ERP',

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | HASIL
                    |--------------------------------------------------------------------------
                    */

                    $inserted[] = [

                        'kredit_id' =>
                            $kredit->id,

                        'spk_id' =>
                            $spk->id,

                        'no_spk' =>
                            $data['no_spk'] ?? null,

                        'supplier' =>
                            $data['sup'] ?? null,

                        'tanggal' =>
                            $payment['date'] ?? null,

                        'payment_id' =>
                            $paymentId,

                        'nominal' =>
                            $nominal,

                        'ket' =>
                            $ket,

                        'ket_extra' =>
                            'Sebelum ERP',

                    ];
                }
            }


            DB::commit();


            return response()->json([

                'success' =>
                    true,

                'status' =>
                    $status,

                'message' =>
                    'Bulk ' . strtoupper($status) .
                    ' berhasil diproses.',

                'inserted_count' =>
                    count($inserted),

                'skipped_count' =>
                    count($skipped),

                'inserted' =>
                    $inserted,

                'skipped' =>
                    $skipped,

            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Bulk generate gagal.',

                'error' =>
                    $e->getMessage(),

                'line' =>
                    $e->getLine(),

            ], 500);
        }
    }
   public function updateBahanBakuKeterangan(Request $request)
{
    $request->validate([
        'id' => 'required|integer',
        'keterangan' => 'nullable|string|max:1000',
    ]);

    $transaksi = TransaksiStok::where('id', $request->id)
        ->where('tipe', 'out')
        ->whereNotNull('spk_id')
        ->firstOrFail();

    $transaksi->keterangan = $request->keterangan;
    $transaksi->save();

    return response()->json([
        'success' => true,
        'id' => $transaksi->id,
        'spk_id' => $transaksi->spk_id,
        'keterangan' => $transaksi->keterangan,
    ]);
}
}
