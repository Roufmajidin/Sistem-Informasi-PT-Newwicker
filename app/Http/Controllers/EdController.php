<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\ExportIpl;
use App\Models\ExportIplItem;
use App\Models\ExportIplPo;
use App\Models\ExportDocument;
use App\Models\ExportDocumentFile;
use App\Models\ExportAr;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Loberon;
use App\Models\ExportArPayment;
use App\Exports\ExportCipl;
use App\Exports\ExportSi;
use Barryvdh\DomPDF\Facade\Pdf;
class EdController extends Controller
{
    //

    public function index()
    {
        return view('pages.exports.index', [

            'mode' => 'create',

            'ipl' => null,

        ]);
    }

    public function searchPo(Request $request)
    {
        $keyword = $request->keyword;

        $rows = Po::with('detailPos')
            ->where('order_no', 'like', "%{$keyword}%")
            ->orWhere('company_name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($rows);
    }

    public function poItems($id)
    {
        $po = Po::with('detailPos')->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | AMBIL IPL YANG SUDAH RELEASE
        |--------------------------------------------------------------------------
        */

        $releasedItems = ExportIplItem::with('exportIpl')
            ->whereHas('exportIpl', function ($query) {

                $query->whereNotNull('released')
                    ->where('released', '!=', '');

            })
            ->select(
                'id',
                'export_ipl_id',
                'po_id',
                'detail_po_id',
                'po_no',
                'article_nr',
                'qty_pcs'
            )
            ->orderBy('export_ipl_id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GROUP BERDASARKAN PO + ARTICLE
        |--------------------------------------------------------------------------
        |
        | HARUS SAMA DENGAN STOCK MONITORING
        |
        */

        $loadedByPoArticle = $releasedItems
            ->groupBy(function ($item) {

                return trim((string) $item->po_no)
                    . '||'
                    . trim((string) $item->article_nr);

            });


        $items = [];


        /*
        |--------------------------------------------------------------------------
        | LOOP DETAIL PO
        |--------------------------------------------------------------------------
        */

        foreach ($po->detailPos as $detailPo) {

            $detail = is_array($detailPo->detail)
                ? $detailPo->detail
                : json_decode($detailPo->detail, true);


            /*
            |--------------------------------------------------------------------------
            | QTY PO
            |--------------------------------------------------------------------------
            */

            $qtyPo = (float) ($detail['qty'] ?? 0);


            /*
            |--------------------------------------------------------------------------
            | PO NUMBER
            |--------------------------------------------------------------------------
            */

            $poNo = trim(
                (string) $po->order_no
            );


            /*
            |--------------------------------------------------------------------------
            | ARTICLE
            |--------------------------------------------------------------------------
            */

            $article = trim(
                (string) ($detail['article_nr_'] ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | DEFAULT LOADED
            |--------------------------------------------------------------------------
            */

            $loadedQty = 0;


            /*
            |--------------------------------------------------------------------------
            | CARI SEMUA IPL RELEASED
            |--------------------------------------------------------------------------
            */

            if (
                $poNo !== '' &&
                $article !== ''
            ) {

                $key = $poNo . '||' . $article;


                if (isset($loadedByPoArticle[$key])) {

                    $loadedQty = $loadedByPoArticle[$key]
                        ->sum(function ($load) {

                            return (float) (
                                $load->qty_pcs ?? 0
                            );

                        });

                }

            }


            /*
            |--------------------------------------------------------------------------
            | QTY SISA
            |--------------------------------------------------------------------------
            */

            $availableQty = max(
                0,
                $qtyPo - $loadedQty
            );


            /*
            |--------------------------------------------------------------------------
            | RETURN DATA
            |--------------------------------------------------------------------------
            */

            $items[] = [

                'id' => $detailPo->id,

                'article_nr' =>
                    $detail['article_nr_'] ?? '',

                'order_no' =>
                    $po->order_no,

                'po_id' =>
                    $po->id,

                'description' =>
                    $detail['description'] ?? '',

                'photo' =>
                    $detail['photo'] ?? '',


                // Qty PO
                'qty' =>
                    $qtyPo,


                // Qty seluruh IPL RELEASE
                'used_qty' =>
                    $loadedQty,


                // Qty yang masih bisa diambil
                'available_qty' =>
                    $availableQty,


                'cbm' =>
                    $detail['cbm'] ?? 0,

                'total_cbm' =>
                    $detail['total_cbm'] ?? 0,

                'pack_w' =>
                    $detail['pack_w'] ?? '',

                'pack_d' =>
                    $detail['pack_d'] ?? '',

                'pack_h' =>
                    $detail['pack_h'] ?? '',

                'value' =>
                    $this->getPrice($detail),

            ];

        }


        return response()->json($items);
    }

    private function getPrice(array $detail)
    {
        // prioritas 1
        if (!empty($detail['final_fob_price'])) {
            return (float) $detail['final_fob_price'];
        }

        // prioritas 2
        if (!empty($detail['fob_jakarta_in_usd'])) {
            return (float) $detail['fob_jakarta_in_usd'];
        }

        // prioritas 3
        if (!empty($detail['fob_jakarta_price_in_usd_pc'])) {
            return (float) $detail['fob_jakarta_price_in_usd_pc'];
        }

        // prioritas 4
        if (!empty($detail['value_in_usd']) && !empty($detail['qty'])) {
            return round(
                $detail['value_in_usd'] / $detail['qty'],
                2
            );
        }

        return 0;
    }

    public function saveIpl(Request $request)
    {
        DB::beginTransaction();

        try {

            $shipment = $request->shipment ?? [];


            // =====================================================
            // CREATE EXPORT IPL
            // =====================================================

            $ipl = ExportIpl::create([

                'invoice_no' => $request->invoice_no,

                'date' => $request->date ?? null,

                'sales_order' => $request->sales_order,

                'buyer' => $request->buyer,

                'buyer_address' =>
                    $shipment['buyer_address'] ?? null,

                'customer_code' =>
                    $shipment['customer_code'] ?? null,

                'customer_po_no' =>
                    $shipment['customer_po_no'] ?? null,

                'container_type' =>
                    $shipment['container_type'] ?? null,

                'container_no' =>
                    $shipment['container_no'] ?? null,

                'seal_no' =>
                    $shipment['seal_no'] ?? null,

                'vessel_name' =>
                    $shipment['vessel_name'] ?? null,

                'port_loading' =>
                    $shipment['port_loading'] ?? null,

                'port_discharge' =>
                    $shipment['port_discharge'] ?? null,

                'commodity' =>
                    $shipment['commodity'] ?? null,

                'fumigation' =>
                    $shipment['fumigation'] ?? null,

                'etd' =>
                    $shipment['etd'] ?? null,

                'eta' =>
                    $shipment['eta'] ?? null,

                'created_by' =>
                    auth()->id(),

            ]);


            // =====================================================
            // SAVE PO
            // =====================================================

            collect($request->items)
                ->unique('po_id')
                ->each(function ($item) use ($ipl) {

                    ExportIplPo::create([

                        'export_ipl_id' =>
                            $ipl->id,

                        'po_id' =>
                            $item['po_id'],

                        'po_no' =>
                            $item['po_no'],

                    ]);

                });


            // =====================================================
            // SAVE ITEMS
            // =====================================================

            foreach ($request->items as $item) {

                ExportIplItem::create([

                    'export_ipl_id' =>
                        $ipl->id,

                    'po_id' =>
                        $item['po_id'],

                    'detail_po_id' =>
                        $item['detail_po_id'],

                    'po_no' =>
                        $item['po_no'],

                    'hs_code' =>
                        $item['hs_code'],

                    'article_nr' =>
                        $item['article_nr'],

                    'description' =>
                        $item['description'],

                    'photo' =>
                        $item['photo'],

                    'box_dimension' =>
                        $item['box_dimension'],

                    'qty_pcs' =>
                        $item['qty_pcs'],

                    'qty_box' =>
                        $item['qty_box'],

                    'cbm' =>
                        $item['cbm'],

                    'total_cbm' =>
                        $item['total_cbm'],

                    'unit_price' =>
                        $item['unit_price'],

                    'total_price' =>
                        $item['total_price'],

                    'net_weight' =>
                        $item['net_weight'],

                    'gross_weight' =>
                        $item['gross_weight'],

                    'remark' =>
                        $item['remark'],

                ]);

            }


            // =====================================================
            // HITUNG FOB USD
            // =====================================================
            //
            // Sumber utama:
            // export_ipl_items.total_price
            //
            // Contoh:
            //
            // 1050
            // 575
            // 350
            // ...
            // = 56,686
            //

            $fobUsd = ExportIplItem::where(
                'export_ipl_id',
                $ipl->id
            )->sum('total_price');

            $fobUsd = (float) $fobUsd;


            // =====================================================
            // CREATE EXPORT AR
            // =====================================================
            //
            // Setiap IPL yang dibuat langsung mempunyai AR.
            //
            // status:
            // 0 = Draft / belum release
            //

            $exportAr = ExportAr::firstOrCreate(

                [
                    'export_ipl_id' =>
                        $ipl->id,
                ],

                [
                    'status' =>
                        0,

                    'tanggal_invoice' =>
                        $request->date ?? null,

                    'fob_usd' =>
                        $fobUsd,

                    'created_by' =>
                        auth()->id(),
                ]

            );


            // =====================================================
            // PASTIKAN FOB SELALU SAMA DENGAN TOTAL ITEM
            // =====================================================

            $exportAr->fob_usd =
                $fobUsd;


            // Kalau belum ada jumlah container,
            // IPL dianggap 1 container.

            if (
                empty($exportAr->jumlah_container)
            ) {

                $exportAr->jumlah_container =
                    1;
            }


            $exportAr->save();


            // =====================================================
            // COMMIT
            // =====================================================

            DB::commit();


            // =====================================================
            // RESPONSE
            // =====================================================

            return response()->json([

                'success' => true,

                'message' =>
                    'IPL berhasil disimpan.',

                'id' =>
                    $ipl->id,

                'export_ar_id' =>
                    $exportAr->id,

                'fob_usd' =>
                    $exportAr->fob_usd,

                'ar_status' =>
                    $exportAr->status,

            ]);


        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);

        }
    }

    public function ipl(Request $request)
    {
        $activeRef = trim(
            (string) $request->query('ref', '')
        );

        if ($activeRef !== '') {
            return $this->lobIndex($request);
        }
        $datas = ExportIpl::withCount([
            'items',
            'pos',
        ])
            ->latest()
            ->paginate(20);

        /*
        |--------------------------------------------------------------------------
        | INVOICE YANG AKTIF DARI PARAMETER URL
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | /export/ipl?ref=INV002-NW20-07-263
        |
        */

        $activeRef = trim(
            (string) $request->query('ref', '')
        );

        return view(
            'pages.exports.ipl',
            compact(
                'datas',
                'activeRef'
            )
        );
    }

    public function edit($id)
    {
        $ipl = ExportIpl::with([
            'pos',
            'items',
        ])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | EDIT IPL - SOURCE CBM DARI DETAIL PO
        |--------------------------------------------------------------------------
        | Prioritas:
        | 1. detail_po_id yang tersimpan pada ExportIplItem
        | 2. PO + article_nr sebagai fallback
        |
        | CBM dan TOTAL CBM tidak dihitung ulang dari box_dimension.
        |--------------------------------------------------------------------------
        */

        foreach ($ipl->items as $item) {

            $detailPo = null;

            // ---------------------------------------------------------
            // 1. DETAIL PO ID YANG SUDAH TERSIMPAN
            // ---------------------------------------------------------
            if (!empty($item->detail_po_id)) {

                $detailPo = DetailPo::find(
                    $item->detail_po_id
                );
            }

            // ---------------------------------------------------------
            // 2. FALLBACK PO + ARTICLE
            // ---------------------------------------------------------
            if (
                !$detailPo &&
                !empty($item->po_id) &&
                !empty($item->article_nr)
            ) {

                $po = Po::with('detailPos')
                    ->find($item->po_id);

                if ($po) {

                    $articleTarget = trim(
                        (string) $item->article_nr
                    );

                    foreach ($po->detailPos as $candidate) {

                        $detailCandidate = is_array($candidate->detail)
                            ? $candidate->detail
                            : json_decode($candidate->detail, true);

                        $articleCandidate = trim(
                            (string) (
                                $detailCandidate['article_nr_'] ?? ''
                            )
                        );

                        if (
                            $articleCandidate !== '' &&
                            $articleCandidate === $articleTarget
                        ) {

                            $detailPo = $candidate;

                            break;
                        }
                    }
                }
            }

            // ---------------------------------------------------------
            // RECOVERY PO ID JIKA KOSONG
            // ---------------------------------------------------------
            if (
                empty($item->po_id) &&
                !empty($item->po_no)
            ) {

                $po = Po::with('detailPos')
                    ->where(
                        'order_no',
                        trim($item->po_no)
                    )
                    ->first();

                if ($po) {

                    $item->po_id = $po->id;

                    if (
                        !$detailPo &&
                        !empty($item->article_nr)
                    ) {

                        $articleTarget = trim(
                            (string) $item->article_nr
                        );

                        foreach ($po->detailPos as $candidate) {

                            $detailCandidate = is_array($candidate->detail)
                                ? $candidate->detail
                                : json_decode($candidate->detail, true);

                            $articleCandidate = trim(
                                (string) (
                                    $detailCandidate['article_nr_'] ?? ''
                                )
                            );

                            if (
                                $articleCandidate !== '' &&
                                $articleCandidate === $articleTarget
                            ) {

                                $detailPo = $candidate;

                                break;
                            }
                        }
                    }
                }
            }

            // ---------------------------------------------------------
            // DETAIL PO MENJADI SOURCE UTAMA
            // ---------------------------------------------------------
            if ($detailPo) {

                $detail = is_array($detailPo->detail)
                    ? $detailPo->detail
                    : json_decode($detailPo->detail, true);

                $item->detail_po_id = $detailPo->id;

                // Source asli dari Release Order / detail_po.
                $item->cbm = (float) (
                    $detail['cbm'] ?? 0
                );

                $item->total_cbm = (float) (
                    $detail['total_cbm'] ?? 0
                );

                // Dimensi packing juga tetap sinkron dengan detail_po.
                $item->box_dimension =
                    ($detail['pack_w'] ?? '') . ' x ' .
                    ($detail['pack_d'] ?? '') . ' x ' .
                    ($detail['pack_h'] ?? '');
            }
        }

        return view('pages.exports.index', [
            'mode' => 'edit',
            'ipl' => $ipl,
        ]);
    }

    public function updateIpl(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // =====================================================
            // CARI IPL
            // =====================================================

            $ipl = ExportIpl::findOrFail($id);

            $shipment =
                $request->shipment ?? [];


            // =====================================================
            // UPDATE HEADER
            // =====================================================

            $ipl->update([

                'invoice_no' =>
                    $request->invoice_no,

                'sales_order' =>
                    $request->sales_order,

                'date' =>
                    $request->date ?? null,

                'buyer' =>
                    $request->buyer,

                'buyer_address' =>
                    $shipment['buyer_address'] ?? null,

                'customer_code' =>
                    $shipment['customer_code'] ?? null,

                'customer_po_no' =>
                    $shipment['customer_po_no'] ?? null,

                'container_type' =>
                    $shipment['container_type'] ?? null,

                'container_no' =>
                    $shipment['container_no'] ?? null,

                'seal_no' =>
                    $shipment['seal_no'] ?? null,

                'vessel_name' =>
                    $shipment['vessel_name'] ?? null,

                'port_loading' =>
                    $shipment['port_loading'] ?? null,

                'port_discharge' =>
                    $shipment['port_discharge'] ?? null,

                'commodity' =>
                    $shipment['commodity'] ?? null,

                'fumigation' =>
                    $shipment['fumigation'] ?? null,

                'etd' =>
                    $shipment['etd'] ?? null,

                'eta' =>
                    $shipment['eta'] ?? null,

            ]);


            // =====================================================
            // DELETE OLD PO
            // =====================================================

            $ipl->pos()->delete();


            // =====================================================
            // DELETE OLD ITEMS
            // =====================================================

            $ipl->items()->delete();


            // =====================================================
            // SAVE PO
            // =====================================================

            collect($request->items)
                ->unique('po_id')
                ->each(function ($item) use ($ipl) {

                    if (
                        empty($item['po_id'])
                    ) {
                        return;
                    }

                    $ipl->pos()->create([

                        'po_id' =>
                            $item['po_id'] ?? null,

                        'po_no' =>
                            $item['po_no'],

                    ]);

                });


            // =====================================================
            // SAVE ITEMS
            // =====================================================

            foreach ($request->items as $item) {

                if (
                    !isset($item['po_id'])
                ) {
                    continue;
                }


                $ipl->items()->create([

                    'po_id' =>
                        $item['po_id'],

                    'detail_po_id' =>
                        $item['detail_po_id'] ?? null,

                    'po_no' =>
                        $item['po_no'] ?? null,


                    // =================================================
                    // KHUSUS LOBERON
                    // =================================================

                    'blde' =>
                        $item['blde']
                        ?? ($item['po_no'] ?? null),


                    'hs_code' =>
                        $item['hs_code'] ?? null,

                    'article_nr' =>
                        $item['article_nr'] ?? null,

                    'description' =>
                        $item['description'] ?? null,

                    'photo' =>
                        $item['photo'] ?? null,

                    'box_dimension' =>
                        $item['box_dimension'] ?? null,

                    'qty_pcs' =>
                        $item['qty_pcs'] ?? 0,

                    'qty_box' =>
                        $item['qty_box'] ?? 0,

                    'cbm' =>
                        $item['cbm'] ?? 0,

                    'total_cbm' =>
                        $item['total_cbm'] ?? 0,

                    'unit_price' =>
                        $item['unit_price'] ?? 0,

                    'total_price' =>
                        $item['total_price'] ?? 0,

                    'net_weight' =>
                        $item['net_weight'] ?? 0,

                    'gross_weight' =>
                        $item['gross_weight'] ?? 0,

                    'remark' =>
                        $item['remark'] ?? null,

                ]);

            }


            // =====================================================
            // HITUNG ULANG FOB USD
            // =====================================================
            //
            // WAJIB dilakukan setelah item selesai dibuat.
            //
            // Jadi kalau user mengubah:
            //
            // Qty
            // Price
            // Total Price
            //
            // maka FOB AR ikut berubah.
            //

            $fobUsd = ExportIplItem::where(
                'export_ipl_id',
                $ipl->id
            )->sum('total_price');

            $fobUsd = (float) $fobUsd;


            // =====================================================
            // CARI / CREATE EXPORT AR
            // =====================================================
            //
            // Tidak menggunakan invoice_no.
            //
            // Relasi:
            //
            // export_ipls.id
            //        ↓
            // export_ars.export_ipl_id
            //

            $exportAr = ExportAr::firstOrCreate(

                [
                    'export_ipl_id' =>
                        $ipl->id,
                ],

                [
                    // AR baru selalu Draft
                    'status' =>
                        0,

                    'tanggal_invoice' =>
                        $request->date ?? null,

                    'fob_usd' =>
                        $fobUsd,

                    'created_by' =>
                        auth()->id(),
                ]

            );


            // =====================================================
            // UPDATE FOB AR
            // =====================================================
            //
            // Jangan hanya update saat AR baru.
            //
            // Kalau AR sudah ada:
            //
            // fob_usd harus mengikuti TOTAL ITEM TERBARU.
            //

            $exportAr->fob_usd =
                $fobUsd;


            // =====================================================
            // UPDATE TANGGAL INVOICE
            // =====================================================

            if (
                !empty($request->date)
            ) {

                $exportAr->tanggal_invoice =
                    $request->date;
            }


            // =====================================================
            // DEFAULT CONTAINER
            // =====================================================

            if (
                empty($exportAr->jumlah_container)
            ) {

                $exportAr->jumlah_container =
                    1;
            }


            // =====================================================
            // JANGAN RESET STATUS AR LAMA
            // =====================================================
            //
            // Kalau AR sudah:
            //
            // status = 1
            //
            // maka edit IPL tidak mengubahnya kembali ke 0.
            //
            // Kalau AR baru, firstOrCreate di atas sudah
            // memberikan status = 0.
            //


            // =====================================================
            // SAVE AR
            // =====================================================

            $exportAr->save();


            // =====================================================
            // COMMIT
            // =====================================================

            DB::commit();


            // =====================================================
            // RESPONSE
            // =====================================================

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'IPL berhasil diperbarui.',

                'id' =>
                    $ipl->id,

                'export_ar_id' =>
                    $exportAr->id,

                'fob_usd' =>
                    $exportAr->fob_usd,

                'ar_status' =>
                    $exportAr->status,

            ]);


        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 500);

        }
    }

    public function deleteItem($id)
    {
        try {

            $item = ExportIplItem::findOrFail($id);

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function check(Request $request, $detailPoId)
    {
        $detailPo = DetailPo::findOrFail($detailPoId);

        $detail = is_array($detailPo->detail)
            ? $detailPo->detail
            : json_decode($detailPo->detail, true);

        $qtyPo = (float) ($detail['qty'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Hanya IPL yang sudah RELEASE dianggap sudah loaded.
        |--------------------------------------------------------------------------
        | Jika EDIT, item yang sedang diedit dikecualikan.
        |--------------------------------------------------------------------------
        */

        $usedQty = ExportIplItem::where('detail_po_id', $detailPoId)
            ->whereHas('exportIpl', function ($query) {
                $query->whereNotNull('released')
                    ->where('released', '!=', '');
            })
            ->when($request->item_id, function ($q) use ($request) {
                $q->where('id', '!=', $request->item_id);
            })
            ->sum('qty_pcs');

        return response()->json([
            'qty_po' => $qtyPo,
            'used_qty' => $usedQty,
            'available_qty' => max(0, $qtyPo - $usedQty),
            'is_full' => $usedQty >= $qtyPo,
        ]);
    }

    public function stock()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA PO
        |--------------------------------------------------------------------------
        */

        $po = Po::with('detailPos')
            ->orderBy('order_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PREPARE DETAIL PO
        |--------------------------------------------------------------------------
        */

        foreach ($po as $itemPo) {

            foreach ($itemPo->detailPos as $detail) {

                $detail->item = is_array($detail->detail)
                    ? $detail->detail
                    : json_decode($detail->detail, true);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA IPL YANG SUDAH RELEASE
        |--------------------------------------------------------------------------
        |
        | Kita tidak hanya mengambil qty.
        | Kita juga mengambil data IPL supaya nanti bisa mendapatkan:
        |
        | - release_date
        | - invoice_no
        | - container_no
        |
        */

        $releasedItems = ExportIplItem::with('exportIpl')
            ->whereHas('exportIpl', function ($query) {

                $query->whereNotNull('released')
                    ->where('released', '!=', '');

            })
            ->select(
                'id',
                'export_ipl_id',
                'po_id',
                'detail_po_id',
                'po_no',
                'article_nr',
                'qty_pcs'
            )
            ->orderBy('export_ipl_id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GROUP BERDASARKAN
        |
        | PO NO + ARTICLE
        |--------------------------------------------------------------------------
        */

        $loadedByPoArticle = $releasedItems
            ->groupBy(function ($item) {

                return trim((string) $item->po_no)
                    . '||'
                    . trim((string) $item->article_nr);

            });


        /*
        |--------------------------------------------------------------------------
        | HITUNG LOADED QTY + LOADING HISTORY
        |--------------------------------------------------------------------------
        */

        foreach ($po as $itemPo) {

            foreach ($itemPo->detailPos as $detail) {

                $item = $detail->item ?? [];


                /*
                |--------------------------------------------------------------------------
                | PO NUMBER
                |--------------------------------------------------------------------------
                */

                $poNo = trim(
                    (string) $itemPo->order_no
                );


                /*
                |--------------------------------------------------------------------------
                | ARTICLE
                |--------------------------------------------------------------------------
                */

                $article = trim(
                    (string) ($item['article_nr_'] ?? '')
                );


                /*
                |--------------------------------------------------------------------------
                | DEFAULT
                |--------------------------------------------------------------------------
                */

                $loadedQty = 0;

                $loadingHistory = [];


                /*
                |--------------------------------------------------------------------------
                | CARI IPL BERDASARKAN PO + ARTICLE
                |--------------------------------------------------------------------------
                */

                if (
                    $poNo !== '' &&
                    $article !== ''
                ) {

                    $key = $poNo . '||' . $article;


                    if (isset($loadedByPoArticle[$key])) {

                        $loads = $loadedByPoArticle[$key];


                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL LOADED
                        |--------------------------------------------------------------------------
                        */

                        $loadedQty = $loads->sum(function ($load) {

                            return (float) (
                                $load->qty_pcs ?? 0
                            );

                        });


                        /*
                        |--------------------------------------------------------------------------
                        | HISTORY LOADING
                        |--------------------------------------------------------------------------
                        |
                        | Satu IPL = satu history.
                        |
                        */

                        $loadingHistory = $loads
                            ->map(function ($load) {

                                $ipl = $load->exportIpl;

                                return [

                                    'ipl_id' =>
                                        $load->export_ipl_id,

                                    'qty' =>
                                        (float) ($load->qty_pcs ?? 0),

                                    'release_date' =>
                                        optional($ipl)->release_date,

                                    'invoice_no' =>
                                        optional($ipl)->invoice_no,

                                    'container_no' =>
                                        optional($ipl)->container_no,

                                ];

                            })
                            ->values()
                            ->toArray();

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | QTY PO
                |--------------------------------------------------------------------------
                */

                $qtyPo = (float) (
                    $item['qty'] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | QTY SISA
                |--------------------------------------------------------------------------
                */

                $availableQty = max(
                    0,
                    $qtyPo - $loadedQty
                );


                /*
                |--------------------------------------------------------------------------
                | SIMPAN KE DETAIL
                |--------------------------------------------------------------------------
                */

                $detail->loaded_qty = $loadedQty;

                $detail->available_qty = $availableQty;

                $detail->loading_history = $loadingHistory;

                /*
                |--------------------------------------------------------------------------
                | STATUS DETAIL
                |--------------------------------------------------------------------------
                */

                $detail->is_loaded =
                    $loadedQty >= $qtyPo;

            }


            /*
            |--------------------------------------------------------------------------
            | STATUS PO
            |--------------------------------------------------------------------------
            |
            | PO masuk HISTORY jika semua detail sudah loaded.
            |
            */

            $hasDetails =
                $itemPo->detailPos->count() > 0;


            $itemPo->is_fully_loaded =
                $hasDetails &&
                $itemPo->detailPos->every(function ($detail) {

                    return $detail->is_loaded;

                });

        }


        /*
        |--------------------------------------------------------------------------
        | ON PROGRESS
        |--------------------------------------------------------------------------
        */

        $onProgress = $po
            ->filter(function ($itemPo) {

                return !$itemPo->is_fully_loaded;

            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | HISTORY
        |--------------------------------------------------------------------------
        */

        $history = $po
            ->filter(function ($itemPo) {

                return $itemPo->is_fully_loaded;

            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view('pages.exports.so', compact(
            'onProgress',
            'history'
        ));
    }
    // public function stock()
    // {
    //     $po = Po::with('detailPos')
    //         ->orderBy('order_no')
    //         ->get();

    //     foreach ($po as $itemPo) {

    //         foreach ($itemPo->detailPos as $detail) {

    //             $detail->item = is_array($detail->detail)
    //                 ? $detail->detail
    //                 : json_decode($detail->detail, true);

    //         }

    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Qty Loaded per Export
    //     |--------------------------------------------------------------------------
    //     */

    //     $loadedItems = ExportIplItem::with('exportIpl')
    //         ->select(
    //             'id',
    //             'export_ipl_id',
    //             'detail_po_id',
    //             'qty_pcs'
    //         )
    //         ->orderBy('export_ipl_id')
    //         ->get()
    //         ->groupBy('detail_po_id');

    //     return view('pages.exports.so', compact(
    //         'po',
    //         'loadedItems'
    //     ));
    // }
    public function docExports()
    {
        $document = null;
        return view('pages.exports.doc_form', compact('document'));
    }
    public function documentList(Request $request)
    {
        $keyword = trim($request->keyword);

        $query = ExportIpl::withCount('items')
            ->with('creator');

        if ($keyword) {

            $query->where(function ($q) use ($keyword) {

                $q->where('invoice_no', 'like', "%{$keyword}%")
                    ->orWhere('sales_order', 'like', "%{$keyword}%")
                    ->orWhere('buyer', 'like', "%{$keyword}%");

            });

        }

        $datas = $query
            ->latest()
            ->get()
            ->map(function ($row) {

                return [

                    'id' => $row->id,

                    'invoice_no' => $row->invoice_no,

                    'sales_order' => $row->sales_order,

                    'buyer' => $row->buyer,

                    'container' => $row->container_type,

                    'etd' => optional($row->etd)->format('d M Y'),

                    'items' => $row->items_count,

                    'created_by' => optional($row->creator)->name,

                ];

            });

        return response()->json($datas);
    }
    public function documentDetail($id)
    {
        $ipl = ExportIpl::with([
            'items',
            'pos',
            'creator',
        ])->findOrFail($id);

        return response()->json([
            'header' => [

                'id' => $ipl->id,

                'invoice_no' => $ipl->invoice_no,

                'sales_order' => $ipl->sales_order,

                'buyer' => $ipl->buyer,

                'buyer_address' => $ipl->buyer_address,

                'container_type' => $ipl->container_type,

                'container_no' => $ipl->container_no,

                'seal_no' => $ipl->seal_no,

                'vessel_name' => $ipl->vessel_name,

                'etd' => optional($ipl->etd)->format('d M Y'),

                'eta' => optional($ipl->eta)->format('d M Y'),

                'commodity' => $ipl->commodity,

            ],

            'items' => $ipl->items->map(function ($item) {

                return [

                    'article' => $item->article_nr,

                    'description' => $item->description,

                    'qty' => $item->qty_pcs,

                    'box' => $item->qty_box,

                    'price' => $item->unit_price,

                    'total' => $item->total_price,

                    'cbm' => $item->total_cbm,

                    'photo' => $item->photo,

                    'po' => $item->po_no,

                ];

            }),

            'totals' => [

                'qty' => $ipl->items->sum('qty_pcs'),

                'box' => $ipl->items->sum('qty_box'),

                'total_price' => $ipl->items->sum('total_price'),

                'total_cbm' => $ipl->items->sum('total_cbm'),

            ]

        ]);
    }

    // list po
    public function poList(Request $request)
    {
        $query = Po::query();

        if ($request->filled('q')) {

            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {

                $q->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('company_name', 'like', "%{$keyword}%")
                    ->orWhere('country', 'like', "%{$keyword}%");

            });

        }

        return response()->json(
            $query->orderByDesc('id')->get([
                'id',
                'order_no',
                'company_name',
                'country',
                'shipment_date'
            ])
        );
    }
    public function poDetail($id)
    {
        $po = Po::with('details')->findOrFail($id);

        return response()->json($po);
    }
    public function storeDocument(Request $request)
    {
        DB::beginTransaction();

        try {

            $document = ExportDocument::create([

                'po_id' => $request->po_id,

                'buyer_name' => $request->buyer_name,

                'invoice_id' => $request->invoice,

                'packing_list_id' => $request->packing_list,

                'peb_no' => $request->peb_no,

                'created_by' => Auth::id(),

            ]);

            $singleFiles = [

                'shipping_instruction',

                'delivery_order',

                'bill_of_lading',

                'certificate_origin',

                'certificate_fumigation',

                'v_legal',

                'phyto',

                'isf',

                'lacey_plant',

                'lacey_animal',

            ];

            foreach ($singleFiles as $type) {

                if ($request->hasFile($type)) {

                    $file = $request->file($type);

                    $path = $file->store(
                        'export_documents/' . $document->id,
                        'public'
                    );

                    ExportDocumentFile::create([

                        'export_document_id' => $document->id,

                        'document_type' => $type,

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'file_size' => $file->getSize(),

                        'mime_type' => $file->getMimeType(),

                    ]);

                }

            }

            // Declaration Multiple File
            if ($request->hasFile('declarations')) {

                foreach ($request->file('declarations') as $file) {

                    $path = $file->store(
                        'export_documents/' . $document->id . '/declarations',
                        'public'
                    );

                    ExportDocumentFile::create([

                        'export_document_id' => $document->id,

                        'document_type' => 'declaration',

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'file_size' => $file->getSize(),

                        'mime_type' => $file->getMimeType(),

                    ]);

                }

            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' => 'Export Document berhasil disimpan.',

                'id' => $document->id,

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }
    public function history()
    {
        $datas = ExportDocument::with([
            'po',
            'creator',
            'invoice',
            'packingList',
            'files',
        ])
            ->latest()
            ->paginate(20);

        return view(
            'pages.exports.doc_history',
            compact('datas')
        );
    }
    public function editDoc($id)
    {
        $document = ExportDocument::with([
            'files',
            'invoice',
            'packingList',
            'po',
        ])->findOrFail($id);
        // dd($document);
        return view(
            'pages.exports.doc_form',
            compact('document')
        );
    }
    // update 


    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $doc = ExportDocument::findOrFail($id);

            $doc->update([
                'po_id' => $request->po_id,
                'buyer_name' => $request->buyer_name,
                'invoice_id' => $request->invoice,
                'packing_list_id' => $request->packing_list,
                'peb_no' => $request->peb_no,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Hapus file yang dihapus user
            |--------------------------------------------------------------------------
            */

            $deleted = json_decode($request->deleted_files, true);

            if (!empty($deleted)) {

                $files = ExportDocumentFile::whereIn('id', $deleted)->get();

                foreach ($files as $file) {

                    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }

                    $file->delete();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Single File
            |--------------------------------------------------------------------------
            */

            $singleFiles = [

                'shipping_instruction',
                'delivery_order',
                'bl',
                'coo',
                'fumigation',
                'v_legal',
                'phyto',
                'isf',
                'lacey_plant',
                'lacey_animal',

            ];

            foreach ($singleFiles as $type) {

                if ($request->hasFile($type)) {

                    $old = ExportDocumentFile::where([
                        'export_document_id' => $doc->id,
                        'document_type' => $type,
                    ])->first();

                    if ($old) {

                        if ($old->file_path && Storage::disk('public')->exists($old->file_path)) {
                            Storage::disk('public')->delete($old->file_path);
                        }

                        $old->delete();
                    }

                    $file = $request->file($type);

                    $path = $file->store('export_documents', 'public');

                    ExportDocumentFile::create([

                        'export_document_id' => $doc->id,

                        'document_type' => $type,

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'mime_type' => $file->getMimeType(),

                        'file_size' => $file->getSize(),

                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Declaration (Multiple)
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('declarations')) {

                foreach ($request->file('declarations') as $file) {

                    $path = $file->store('export_documents', 'public');

                    ExportDocumentFile::create([

                        'export_document_id' => $doc->id,

                        'document_type' => 'declaration',

                        'original_name' => $file->getClientOriginalName(),

                        'file_path' => $path,

                        'mime_type' => $file->getMimeType(),

                        'file_size' => $file->getSize(),

                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document berhasil diupdate.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function releaseIpl(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. AMBIL IPL
            |--------------------------------------------------------------------------
            */

            $ipl = ExportIpl::findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | 2. CEK SUDAH RELEASE
            |--------------------------------------------------------------------------
            */

            if (!empty($ipl->released)) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'IPL ini sudah pernah di-release.'
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | 3. CEK ETD
            |--------------------------------------------------------------------------
            */

            if (empty($ipl->etd)) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'ETD IPL belum diisi.'
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | 4. AMBIL PO ID DARI export_ipl_pos
            |--------------------------------------------------------------------------
            */

            $poIds = ExportIplPo::where(
                'export_ipl_id',
                $ipl->id
            )
                ->pluck('po_id')
                ->filter()
                ->unique()
                ->values();


            /*
            |--------------------------------------------------------------------------
            | 5. JIKA PO ID TIDAK ADA
            |    CARI BERDASARKAN PO NO
            |--------------------------------------------------------------------------
            */

            if ($poIds->isEmpty()) {

                $poNos = ExportIplItem::where(
                    'export_ipl_id',
                    $ipl->id
                )
                    ->pluck('po_no')
                    ->filter()
                    ->map(function ($value) {

                        return trim($value);

                    })
                    ->unique()
                    ->values();


                if ($poNos->isNotEmpty()) {

                    $poIds = Po::whereIn(
                        'order_no',
                        $poNos
                    )
                        ->pluck('id')
                        ->unique()
                        ->values();

                }
            }


            /*
            |--------------------------------------------------------------------------
            | 6. JIKA PO MASIH TIDAK DITEMUKAN
            |--------------------------------------------------------------------------
            */

            if ($poIds->isEmpty()) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'PO tidak ditemukan berdasarkan PO ID maupun nomor PO.'
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | 7. UPDATE ETD KE PO
            |--------------------------------------------------------------------------
            */

            $updatedPo = Po::whereIn(
                'id',
                $poIds
            )
                ->update([
                    'etd' => $ipl->etd,
                ]);


            /*
            |--------------------------------------------------------------------------
            | 8. RELEASE IPL
            |--------------------------------------------------------------------------
            */

            $releaseDate = now();

            $ipl->released = 1;

            $ipl->release_date = $releaseDate->toDateString();

            $ipl->save();


            /*
            |--------------------------------------------------------------------------
            | 9. HITUNG FOB USD DARI ITEM IPL
            |--------------------------------------------------------------------------
            */

            $fobUsd = ExportIplItem::where(
                'export_ipl_id',
                $ipl->id
            )
                ->sum('total_price');

            $fobUsd = (float) $fobUsd;


            /*
            |--------------------------------------------------------------------------
            | 10. JUMLAH CONTAINER
            |--------------------------------------------------------------------------
            |
            | Untuk 1 IPL = 1 container.
            |
            | Jika nanti 1 IPL bisa memiliki beberapa container,
            | bagian ini bisa kita ubah.
            |
            */

            $jumlahContainer = 1;


            /*
            |--------------------------------------------------------------------------
            | 11. BUAT AR BUYER
            |--------------------------------------------------------------------------
            |
            | Satu IPL hanya boleh mempunyai satu AR.
            |
            | firstOrCreate digunakan supaya apabila request release
            | terkirim dua kali tidak membuat AR duplicate.
            |
            */

            $ar = ExportAr::updateOrCreate(
                [
                    'export_ipl_id' => $ipl->id,
                ],
                [
                    'tanggal_invoice' => $releaseDate->toDateString(),

                    /*
                    |--------------------------------------------------------------
                    | Jatuh tempo
                    |--------------------------------------------------------------
                    */
                    'jatuh_tempo' => null,

                    /*
                    |--------------------------------------------------------------
                    | FOB USD
                    |--------------------------------------------------------------
                    |
                    | Diambil dari total_price seluruh item IPL.
                    |
                    */
                    'fob_usd' => $fobUsd,

                    /*
                    |--------------------------------------------------------------
                    | FOB PEB USD
                    |--------------------------------------------------------------
                    |
                    | Saat Release, FOB PEB mengikuti FOB USD IPL.
                    |
                    */
                    'fob_peb_usd' => $fobUsd,

                    /*
                    |--------------------------------------------------------------
                    | Kurs Kemenkeu
                    |--------------------------------------------------------------
                    |
                    | Belum tersedia pada saat Release.
                    |
                    */
                    'kurs_kemenkeu' => 0,

                    /*
                    |--------------------------------------------------------------
                    | Jumlah Rupiah
                    |--------------------------------------------------------------
                    |
                    | Akan dihitung dari:
                    | FOB PEB USD × Kurs Kemenkeu
                    |
                    */
                    'jumlah_rupiah' => 0,

                    /*
                    |--------------------------------------------------------------
                    | Jumlah Container
                    |--------------------------------------------------------------
                    */
                    'jumlah_container' => $jumlahContainer,

                    /*
                    |--------------------------------------------------------------
                    | PEB
                    |--------------------------------------------------------------
                    */
                    'no_pengajuan_peb' => null,
                    'no_peb' => null,

                    /*
                    |--------------------------------------------------------------
                    | STATUS AR
                    |--------------------------------------------------------------
                    */
                    'status_ar' => 'belum_dibayar',

                    /*
                    |--------------------------------------------------------------
                    | KETERANGAN
                    |--------------------------------------------------------------
                    */
                    'keterangan' => null,
                    'remark' => null,

                    /*
                    |--------------------------------------------------------------
                    | USER
                    |--------------------------------------------------------------
                    */
                    'created_by' => auth()->id(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | 12. COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 13. RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' =>
                    'IPL berhasil di-release, ETD PO berhasil diperbarui, dan AR Buyer berhasil dibuat.',

                'release_date' =>
                    $releaseDate->format('d/m/Y'),

                'etd' =>
                    \Carbon\Carbon::parse($ipl->etd)
                        ->format('d/m/Y'),

                'po_ids' =>
                    $poIds->values(),

                'updated_po' =>
                    $updatedPo,

                'ar_id' =>
                    $ar->id,

                'fob_usd' =>
                    $fobUsd,

            ]);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

            DB::rollBack();


            /*
            |--------------------------------------------------------------------------
            | ERROR RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }
    public function customInvoiceData($id)
    {
        $ipl = ExportIpl::with([
            'pos',
            'items',
        ])->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | RECOVERY DATA IPL LAMA
        |--------------------------------------------------------------------------
        | Jika po_id kosong, cari berdasarkan po_no.
        | Jika detail_po_id kosong, cari berdasarkan PO + article.
        |--------------------------------------------------------------------------
        */

        foreach ($ipl->items as $item) {

            /*
            |--------------------------------------------------------------------------
            | RECOVERY PO ID
            |--------------------------------------------------------------------------
            */

            if (
                empty($item->po_id) &&
                !empty($item->po_no)
            ) {

                $po = Po::where(
                    'order_no',
                    trim($item->po_no)
                )->first();

                if ($po) {
                    $item->po_id = $po->id;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | RECOVERY DETAIL PO ID
            |--------------------------------------------------------------------------
            */

            if (
                empty($item->detail_po_id) &&
                !empty($item->po_id) &&
                !empty($item->article_nr)
            ) {

                $po = Po::with('detailPos')
                    ->find($item->po_id);

                if ($po) {

                    foreach ($po->detailPos as $detailPo) {

                        $detail = is_array(
                            $detailPo->detail
                        )
                            ? $detailPo->detail
                            : json_decode(
                                $detailPo->detail,
                                true
                            );

                        $article = trim(
                            (string) (
                                $detail['article_nr_'] ?? ''
                            )
                        );

                        if (
                            $article !== '' &&
                            $article === trim(
                                (string) $item->article_nr
                            )
                        ) {

                            $item->detail_po_id =
                                $detailPo->id;

                            break;
                        }
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN JSON
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'data' => [

                'id' =>
                    $ipl->id,

                'invoice_no' =>
                    $ipl->invoice_no,

                'sales_order' =>
                    $ipl->sales_order,

                'buyer' =>
                    $ipl->buyer,

                'container_type' =>
                    $ipl->container_type,

                'etd' =>
                    $ipl->etd
                    ? \Carbon\Carbon::parse(
                        $ipl->etd
                    )->format('Y-m-d')
                    : null,

                'items' =>
                    $ipl->items
                        ->map(function ($item) {

                            return [

                                /*
                                |--------------------------------------------------------------------------
                                | SOURCE ITEM ID
                                |--------------------------------------------------------------------------
                                */

                                'id' =>
                                    $item->id,

                                'po_id' =>
                                    $item->po_id,

                                'detail_po_id' =>
                                    $item->detail_po_id,

                                'po_no' =>
                                    $item->po_no,

                                'blde' =>
                                    $item->blde,

                                'hs_code' =>
                                    $item->hs_code,

                                'article_nr' =>
                                    $item->article_nr,

                                'description' =>
                                    $item->description,

                                /*
                                |--------------------------------------------------------------------------
                                | CUSTOM DESCRIPTION
                                |--------------------------------------------------------------------------
                                */

                                'desc_custome' =>
                                    $item->desc_custome,

                                'qty_pcs' =>
                                    (float) (
                                        $item->qty_pcs ?? 0
                                    ),

                                'qty_box' =>
                                    (float) (
                                        $item->qty_box ?? 0
                                    ),

                                'unit_price' =>
                                    (float) (
                                        $item->unit_price ?? 0
                                    ),

                                'total_price' =>
                                    (float) (
                                        $item->total_price ?? 0
                                    ),

                                'cbm' =>
                                    (float) (
                                        $item->cbm ?? 0
                                    ),

                                'total_cbm' =>
                                    (float) (
                                        $item->total_cbm ?? 0
                                    ),

                                'net_weight' =>
                                    (float) (
                                        $item->net_weight ?? 0
                                    ),

                                'gross_weight' =>
                                    (float) (
                                        $item->gross_weight ?? 0
                                    ),

                                'remark' =>
                                    $item->remark,
                                // INI WAJIB
                
                            ];

                        })
                        ->values(),

            ],

        ]);
    }
    public function lobIndex(Request $request)
    {
        $invoiceNo = trim(
            (string) $request->query('ref', '')
        );

        if ($invoiceNo === '') {
            abort(404, 'Invoice tidak ditemukan.');
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL IPL
        |--------------------------------------------------------------------------
        */
        $ipl = ExportIpl::with([
            'creator',
            'pos',
            'items',
            'items.po',
            'items.po.detailPos',
            'items.detailPo',
            'exportDocumentsInvoice',
            'exportDocumentsPacking',
        ])
            ->where('invoice_no', $invoiceNo)
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA ARTICLE
        |--------------------------------------------------------------------------
        */
        $articleNrs = $ipl->items
            ->pluck('article_nr')
            ->filter()
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | DATA LOBERON
        |--------------------------------------------------------------------------
        |
        | article_nr dari ExportIplItem
        |          ↓
        | Loberon.article_code
        |          ↓
        | $item->loberon
        |
        */
        $loberons = Loberon::whereIn(
            'article_code',
            $articleNrs
        )
            ->get()
            ->keyBy(function ($loberon) {
                return trim(
                    (string) $loberon->article_code
                );
            });


        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN DATA IPL + LOBERON
        |--------------------------------------------------------------------------
        */
        foreach ($ipl->items as $item) {

            $articleNr = trim(
                (string) $item->article_nr
            );


            /*
            |--------------------------------------------------------------------------
            | DATA LOBERON
            |--------------------------------------------------------------------------
            */
            $item->loberon =
                $loberons->get($articleNr);


            /*
            |--------------------------------------------------------------------------
            | DETAIL PO
            |--------------------------------------------------------------------------
            */
            $detail = null;

            if ($item->detailPo) {

                $detail = is_array(
                    $item->detailPo->detail
                )
                    ? $item->detailPo->detail
                    : json_decode(
                        $item->detailPo->detail,
                        true
                    );
            }


            $item->detail_data =
                is_array($detail)
                ? $detail
                : [];
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE REF PO / BLDE
        |--------------------------------------------------------------------------
        |
        | Menghilangkan karakter invisible seperti:
        | BLDE­25170
        | menjadi:
        | BLDE25170
        |
        */
        $normalizeRefPo = function ($value) {

            $value = (string) ($value ?? '');

            $value = preg_replace(
                '/[\x{00AD}\x{200B}-\x{200D}\x{FEFF}]/u',
                '',
                $value
            );

            $value = preg_replace(
                '/\s+/u',
                ' ',
                $value
            );

            return trim($value);
        };


        /*
        |--------------------------------------------------------------------------
        | AMBIL EXPORT AR MILIK IPL INI
        |--------------------------------------------------------------------------
        |
        | Ambil SEMUA payment:
        | - deposit
        | - pelunasan
        | - surcharge
        |
        | Jangan difilter deposit saja, karena widget surcharge
        | membutuhkan data surcharge dari database.
        |
        */
        $exportAr = ExportAr::with([
            'payments' => function ($query) {

                $query
                    ->orderBy('payment_date')
                    ->orderBy('id');

            },
        ])
            ->where(
                'export_ipl_id',
                $ipl->id
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | SEMUA PAYMENT
        |--------------------------------------------------------------------------
        */
        $lobPayments = collect();

        if ($exportAr) {

            $lobPayments = $exportAr->payments;

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE REF PO
            |--------------------------------------------------------------------------
            */
            $lobPayments->each(function ($payment) use ($normalizeRefPo) {

                $payment->ref_po_normalized =
                    $normalizeRefPo(
                        $payment->ref_po
                    );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | DEPOSIT BERDASARKAN REF PO / BLDE
        |--------------------------------------------------------------------------
        |
        | Satu BLDE boleh mempunyai beberapa deposit.
        | Semuanya dijumlahkan.
        |
        */
        $depositByPo = collect();


        if ($lobPayments->isNotEmpty()) {

            $depositByPo = $lobPayments
                ->filter(function ($payment) {

                    return
                        strtolower(
                            (string) $payment->payment_type
                        ) === 'deposit'
                        &&
                        trim(
                            (string) $payment->ref_po_normalized
                        ) !== '';

                })
                ->groupBy(function ($payment) {

                    return $payment->ref_po_normalized;

                })
                ->map(function ($payments) {

                    return $payments->sum(
                        function ($payment) {

                            return (float) (
                                $payment->amount ?? 0
                            );

                        }
                    );

                });
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL BLDE YANG MEMANG ADA DI INVOICE
        |--------------------------------------------------------------------------
        */
        $bldeNumbers = $ipl->items
            ->map(function ($item) use ($normalizeRefPo) {

                return $normalizeRefPo(
                    $item->blde ?? ''
                );

            })
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | PASTIKAN SEMUA BLDE ADA DI DEPOSIT MAP
        |--------------------------------------------------------------------------
        |
        | Kalau belum ada deposit:
        |
        | BLDE25170 => 0
        | BLDE25171 => 0
        |
        */
        foreach ($bldeNumbers as $blde) {

            if (!$depositByPo->has($blde)) {

                $depositByPo->put(
                    $blde,
                    0
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ORDER DEPOSIT SESUAI URUTAN ITEM INVOICE
        |--------------------------------------------------------------------------
        */
        $orderedDepositByPo = collect();

        foreach ($bldeNumbers as $blde) {

            $orderedDepositByPo->put(
                $blde,
                (float) $depositByPo->get(
                    $blde,
                    0
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EXPORT AR ID + STATUS
        |--------------------------------------------------------------------------
        */
        $exportArId = $exportAr
            ? $exportAr->id
            : null;

        $arStatus = $exportAr
            ? $exportAr->status
            : 0;


        /*
        |--------------------------------------------------------------------------
        | KIRIM KE BLADE
        |--------------------------------------------------------------------------
        |
        | $lobPayments penting untuk:
        | - existing payment
        | - surcharge 1/2/3
        | - modal payment
        |
        | $ipl tetap membawa:
        | - Loberon data
        | - detail PO
        | - item data
        |
        */
        return view(
            'pages.exports.lobindex',
            compact(
                'ipl',
                'invoiceNo',
                'bldeNumbers',
                'depositByPo',
                'orderedDepositByPo',
                'lobPayments',
                'exportAr',
                'exportArId',
                'arStatus'
            )
        );
    }

    public function updateLoberon(Request $request)
    {
        DB::beginTransaction();

        try {

            // =====================================================
            // 1. INVOICE
            // =====================================================

            $invoiceNo = $request->input('invoice_no');

            if (!$invoiceNo) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invoice number tidak ditemukan.'
                ], 422);
            }


            // =====================================================
            // 2. CARI EXPORT IPL
            // =====================================================

            $ipl = ExportIpl::where(
                'invoice_no',
                $invoiceNo
            )->first();

            if (!$ipl) {

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "Invoice {$invoiceNo} tidak ditemukan."
                ], 404);
            }


            // =====================================================
            // 3. RELEASE STATUS
            // =====================================================

            $releaseInvoice =
                $request->boolean('release_invoice');


            // =====================================================
            // 4. UPDATE HEADER EXPORT IPL
            // =====================================================

            $header =
                $request->input('header', []);

            $iplUpdate = [];


            if (
                array_key_exists(
                    'consignee_name',
                    $header
                )
            ) {

                $iplUpdate['buyer'] =
                    $header['consignee_name'];
            }


            if (
                array_key_exists(
                    'consignee_address',
                    $header
                )
            ) {

                $iplUpdate['buyer_address'] =
                    $header['consignee_address'];
            }


            if (
                array_key_exists(
                    'destination_name',
                    $header
                )
            ) {

                $iplUpdate['final_destination'] =
                    $header['destination_name'];
            }


            if (
                array_key_exists(
                    'destination_address',
                    $header
                )
            ) {

                $iplUpdate['final_destination_address'] =
                    $header['destination_address'];
            }


            if (
                array_key_exists(
                    'eori',
                    $header
                )
            ) {

                $iplUpdate['eori'] =
                    $header['eori'];
            }


            if (
                array_key_exists(
                    'incoterm',
                    $header
                )
            ) {

                $iplUpdate['incoterm'] =
                    $header['incoterm'];
            }


            if (
                array_key_exists(
                    'country_origin',
                    $header
                )
            ) {

                $iplUpdate['country_of_origin'] =
                    $header['country_origin'];
            }


            if (
                array_key_exists(
                    'port_loading',
                    $header
                )
            ) {

                $iplUpdate['port_loading'] =
                    $header['port_loading'];
            }


            if (
                array_key_exists(
                    'port_discharge',
                    $header
                )
            ) {

                $iplUpdate['port_discharge'] =
                    $header['port_discharge'];
            }


            if (
                array_key_exists(
                    'rex',
                    $header
                )
            ) {

                $iplUpdate['rex'] =
                    $header['rex'];
            }


            if (
                array_key_exists(
                    'igst_no',
                    $header
                )
            ) {

                $iplUpdate['igst_no'] =
                    $header['igst_no'];
            }


            if (
                array_key_exists(
                    'invoice_date',
                    $header
                )
                &&
                !empty($header['invoice_date'])
            ) {

                $iplUpdate['date'] =
                    $header['invoice_date'];
            }


            if (
                array_key_exists(
                    'vessel_name',
                    $header
                )
            ) {

                $iplUpdate['vessel_name'] =
                    $header['vessel_name'];
            }


            if (
                array_key_exists(
                    'container_no',
                    $header
                )
            ) {

                $iplUpdate['container_no'] =
                    $header['container_no'];
            }


            if (
                array_key_exists(
                    'container_type',
                    $header
                )
            ) {

                $iplUpdate['container_type'] =
                    $header['container_type'];
            }


            // =====================================================
            // 5. RELEASE EXPORT IPL
            // =====================================================

            if ($releaseInvoice) {

                $iplUpdate['released'] = 1;
                $iplUpdate['release_date'] = now();

            } else {

                $iplUpdate['released'] = 0;
                $iplUpdate['release_date'] = null;
            }


            // =====================================================
            // 6. SIMPAN EXPORT IPL
            // =====================================================

            if (!empty($iplUpdate)) {

                $ipl->update($iplUpdate);
            }


            // =====================================================
            // 7. UPDATE ITEMS
            // =====================================================

            $items =
                $request->input('items', []);

            foreach ($items as $itemData) {

                if (empty($itemData['id'])) {
                    continue;
                }


                $item = ExportIplItem::where(
                    'id',
                    $itemData['id']
                )
                    ->where(
                        'export_ipl_id',
                        $ipl->id
                    )
                    ->first();


                if (!$item) {
                    continue;
                }


                $itemUpdate = [];


                if (
                    array_key_exists(
                        'po_number',
                        $itemData
                    )
                ) {

                    $itemUpdate['blde'] =
                        $itemData['po_number'];
                }


                if (
                    array_key_exists(
                        'article_code',
                        $itemData
                    )
                ) {

                    $itemUpdate['article_nr'] =
                        $itemData['article_code'];
                }


                if (
                    array_key_exists(
                        'description',
                        $itemData
                    )
                ) {

                    $itemUpdate['desc_custome'] =
                        $itemData['description'];
                }


                if (
                    array_key_exists(
                        'hts_code',
                        $itemData
                    )
                ) {

                    $itemUpdate['hs_code'] =
                        $itemData['hts_code'];
                }


                if (
                    array_key_exists(
                        'qty',
                        $itemData
                    )
                ) {

                    $itemUpdate['qty_pcs'] =
                        $itemData['qty'];
                }


                if (
                    array_key_exists(
                        'price',
                        $itemData
                    )
                ) {

                    $itemUpdate['unit_price'] =
                        $itemData['price'];
                }


                if (
                    array_key_exists(
                        'net_weight',
                        $itemData
                    )
                ) {

                    $itemUpdate['net_weight'] =
                        $itemData['net_weight'];
                }


                if (
                    array_key_exists(
                        'gross_weight',
                        $itemData
                    )
                ) {

                    $itemUpdate['gross_weight'] =
                        $itemData['gross_weight'];
                }


                if (
                    array_key_exists(
                        'marks_1',
                        $itemData
                    )
                ) {

                    $itemUpdate['remark'] =
                        $itemData['marks_1'];
                }


                // TOTAL PRICE
                if (
                    array_key_exists('qty', $itemData)
                    &&
                    array_key_exists('price', $itemData)
                ) {

                    $qty =
                        (float) $itemData['qty'];

                    $price =
                        (float) $itemData['price'];

                    $itemUpdate['total_price'] =
                        $qty * $price;
                }


                if (!empty($itemUpdate)) {

                    $item->update(
                        $itemUpdate
                    );
                }
            }


            // =====================================================
            // 8. CARI EXPORT AR
            // =====================================================

            $ar = ExportAr::where(
                'export_ipl_id',
                $ipl->id
            )->first();


            // =====================================================
            // 9. CREATE EXPORT AR JIKA BELUM ADA
            // =====================================================

            if (!$ar) {

                $ar = ExportAr::create([

                    'export_ipl_id' =>
                        $ipl->id,

                    'status' => 0,

                    'tanggal_invoice' =>
                        !empty($header['invoice_date'])
                        ? $header['invoice_date']
                        : null,

                    'created_by' =>
                        auth()->id(),
                ]);
            }


            // =====================================================
            // 10. UPDATE STATUS AR
            // =====================================================

            $ar->status =
                $releaseInvoice ? 1 : 0;


            // =====================================================
            // 11. UPDATE DATA AR
            // =====================================================

            $arData = [];


            $arFields = [

                'tanggal_invoice',
                'jatuh_tempo',
                'fob_usd',
                'fob_peb_usd',
                'kurs_kemenkeu',
                'jumlah_rupiah',
                'jumlah_container',
                'no_pengajuan_peb',
                'no_peb',
                'keterangan',
                'remark',

            ];


            foreach ($arFields as $field) {

                if (
                    $request->has(
                        'ar.' . $field
                    )
                ) {

                    $arData[$field] =
                        $request->input(
                            'ar.' . $field
                        );
                }
            }


            if (!empty($arData)) {

                $ar->fill($arData);
            }


            // =====================================================
            // 12. SIMPAN EXPORT AR
            // =====================================================

            $ar->save();


            // =====================================================
            // 13. PAYMENT
            // =====================================================

            $payments =
                $request->input(
                    'payments',
                    []
                );


            foreach ($payments as $paymentData) {


                // =================================================
                // PAYMENT EXISTING
                // =================================================

                if (
                    !empty(
                    $paymentData['id']
                )
                ) {

                    $payment =
                        ExportArPayment::where(
                            'id',
                            $paymentData['id']
                        )
                            ->where(
                                'export_ar_id',
                                $ar->id
                            )
                            ->first();


                    if (!$payment) {
                        continue;
                    }


                    $paymentUpdate = [];


                    if (
                        array_key_exists(
                            'payment_type',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['payment_type'] =
                            $paymentData['payment_type'];
                    }


                    if (
                        array_key_exists(
                            'payment_date',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['payment_date'] =
                            !empty(
                            $paymentData['payment_date']
                        )
                            ? $paymentData['payment_date']
                            : null;
                    }


                    if (
                        array_key_exists(
                            'ref_po',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['ref_po'] =
                            $paymentData['ref_po'];
                    }


                    if (
                        array_key_exists(
                            'amount',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['amount'] =
                            $paymentData['amount'];
                    }


                    if (
                        array_key_exists(
                            'reference',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['reference'] =
                            $paymentData['reference'];
                    }


                    if (
                        array_key_exists(
                            'keterangan',
                            $paymentData
                        )
                    ) {

                        $paymentUpdate['keterangan'] =
                            $paymentData['keterangan'];
                    }


                    if (!empty($paymentUpdate)) {

                        $payment->update(
                            $paymentUpdate
                        );
                    }


                    continue;
                }


                // =================================================
                // PAYMENT BARU
                // =================================================

                $paymentType =
                    $paymentData['payment_type']
                    ?? null;

                $amount =
                    $paymentData['amount']
                    ?? null;


                /*
                 * Payment kosong jangan dibuat.
                 */

                if (
                    empty($paymentType)
                    ||
                    $amount === null
                    ||
                    (float) $amount <= 0
                ) {

                    continue;
                }


                ExportArPayment::create([

                    'export_ar_id' =>
                        $ar->id,

                    'payment_type' =>
                        $paymentType,

                    'payment_date' =>
                        !empty(
                        $paymentData['payment_date']
                    )
                        ? $paymentData['payment_date']
                        : null,

                    'ref_po' =>
                        $paymentData['ref_po']
                        ?? null,

                    'amount' =>
                        $amount,

                    'reference' =>
                        $paymentData['reference']
                        ?? null,

                    'keterangan' =>
                        $paymentData['keterangan']
                        ?? null,

                    'created_by' =>
                        auth()->id(),

                ]);
            }


            // =====================================================
            // 14. COMMIT
            // =====================================================

            DB::commit();


            // =====================================================
            // 15. RESPONSE
            // =====================================================

            return response()->json([

                'success' => true,

                'message' => $releaseInvoice
                    ? 'Loberon berhasil diupdate dan invoice berhasil di-release.'
                    : 'Loberon berhasil diupdate sebagai draft.',

                'invoice_no' =>
                    $ipl->invoice_no,

                'export_ipl_id' =>
                    $ipl->id,

                'export_ar_id' =>
                    $ar->id,

                'ar_status' =>
                    $ar->status,

                'released' =>
                    $ipl->released,
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

                'line' =>
                    $e->getLine(),

                'file' =>
                    basename($e->getFile()),

            ], 500);
        }
    }
    // EXPORT DOWNLOAD
    // ada di helpers
    public function downloadCipl($id)
    {
        $invoice = ExportIpl::findOrFail($id);

        return (new ExportCipl())
            ->download($invoice);
    }
    public function si($id)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD IPL + ITEMS
        |--------------------------------------------------------------------------
        */

        $ipl = ExportIpl::with([
            'items'
        ])->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | HS CODE
        |--------------------------------------------------------------------------
        | Ambil HS Code dari ExportIplItem.
        |
        | Contoh:
        | 9401.53.00
        | 9401.53.00
        | 4602.12.90
        | 4602.12.90
        |
        | Menjadi:
        | 9401.53.00
        | 4602.12.90
        |--------------------------------------------------------------------------
        */

        $hsCodes = $ipl->items
            ->pluck('hs_code')
            ->filter(function ($value) {

                return trim((string) $value) !== '';

            })
            ->map(function ($value) {

                return trim((string) $value);

            })
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | PO NUMBER
        |--------------------------------------------------------------------------
        | Mengikuti BLDE / PO No yang tersimpan di ExportIplItem.
        |--------------------------------------------------------------------------
        */

        $poNumbers = $ipl->items
            ->map(function ($item) {

                $po = $item->blde
                    ?? $item->po_no
                    ?? '';

                return trim((string) $po);

            })
            ->filter(function ($value) {

                return $value !== '';

            })
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | TOTAL QUANTITY / CARTONS
        |--------------------------------------------------------------------------
        */

        $totalQty = $ipl->items->sum(function ($item) {

            return (float) (
                $item->qty_box ?? 0
            );

        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL NET WEIGHT
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | net_weight = berat per carton
        | qty_box    = jumlah carton
        |
        | Maka:
        |
        | total net = net_weight × qty_box
        |--------------------------------------------------------------------------
        */

        $totalNet = $ipl->items->sum(function ($item) {

            $netWeight = (float) (
                $item->net_weight ?? 0
            );

            $qtyBox = (float) (
                $item->qty_box ?? 0
            );

            return $netWeight * $qtyBox;

        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL GROSS WEIGHT
        |--------------------------------------------------------------------------
        |
        | gross_weight = berat gross per carton
        | qty_box      = jumlah carton
        |
        | Maka:
        |
        | total gross = gross_weight × qty_box
        |--------------------------------------------------------------------------
        */

        $totalGross = $ipl->items->sum(function ($item) {

            $grossWeight = (float) (
                $item->gross_weight ?? 0
            );

            $qtyBox = (float) (
                $item->qty_box ?? 0
            );

            return $grossWeight * $qtyBox;

        });


        /*
        |--------------------------------------------------------------------------
        | TOTAL CBM
        |--------------------------------------------------------------------------
        |
        | total_cbm sudah merupakan total:
        |
        | CBM per carton × Qty Box
        |
        | Jadi JANGAN dikali qty_box lagi.
        |--------------------------------------------------------------------------
        */

        $totalCbm = $ipl->items->sum(function ($item) {

            return (float) (
                $item->total_cbm ?? 0
            );

        });


        /*
        |--------------------------------------------------------------------------
        | HS CODE DISPLAY
        |--------------------------------------------------------------------------
        */

        $hsCodeDisplay = $hsCodes->implode(', ');


        /*
        |--------------------------------------------------------------------------
        | PO DISPLAY
        |--------------------------------------------------------------------------
        */

        $poNumberDisplay = $poNumbers->implode(' ; ');


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.exports.si',
            compact(
                'ipl',
                'hsCodes',
                'hsCodeDisplay',
                'poNumbers',
                'poNumberDisplay',
                'totalQty',
                'totalNet',
                'totalGross',
                'totalCbm'
            )
        );
    }


    /**
     * ============================================================
     * UPDATE SHIPPING INSTRUCTION FIELD
     * ============================================================
     *
     * Dipanggil ketika user selesai edit field
     * lalu pindah / blur ke field berikutnya.
     *
     * AJAX:
     *
     * PUT /export/{id}/si/update
     *
     */
    public function updateSiField(Request $request, $id)
    {
        $ipl = ExportIpl::findOrFail($id);

        $allowedFields = [
            'date',
            'shipping_forwarder',
            'attn',
            'booking_no',
            'peb_no',
            'peb_date',
            'kpbc_no',
            'lc_no',
            'freight',
            'contract_no',
            'notify_party',
            'connect_to',
            'bill_of_lading',
            'tare',
            'vgm',
            'location',
            'stuffing_date',
            'emkl',
            'vessel_name',
            'port_loading',
            'port_discharge',
            'fumigation',
            'container_type',
            'container_no',
            'seal_no',
            'etd',
            'eta',
        ];

        $field = $request->input('field');

        if (!in_array($field, $allowedFields, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Field tidak diizinkan.',
            ], 422);
        }

        $value = $request->input('value');

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            $value = null;
        }

        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        $dateFields = [
            'date',
            'peb_date',
            'stuffing_date',
            'etd',
            'eta',
        ];

        if (in_array($field, $dateFields, true)) {

            if ($value !== null) {

                try {

                    $value = \Carbon\Carbon::parse($value)
                        ->format('Y-m-d');

                } catch (\Throwable $e) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Format tanggal tidak valid.',
                    ], 422);
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | NUMERIC
        |--------------------------------------------------------------------------
        */

        $numericFields = [
            'tare',
            'vgm',
        ];

        if (in_array($field, $numericFields, true)) {

            if ($value !== null) {

                $value = str_replace(',', '.', $value);

                if (!is_numeric($value)) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Nilai harus berupa angka.',
                    ], 422);
                }

                $value = (float) $value;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $ipl->update([
            $field => $value,
        ]);

        $ipl->refresh();

        return response()->json([
            'success' => true,
            'message' => 'SI berhasil diperbarui.',
            'field' => $field,
            'value' => $ipl->{$field},
        ]);
    }
    public function updateSiHsCode(
        Request $request,
        $id
    ) {

        /*
        |--------------------------------------------------------------------------
        | FIND IPL
        |--------------------------------------------------------------------------
        */

        $ipl = ExportIpl::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | OLD HS CODE
        |--------------------------------------------------------------------------
        */

        $oldHsCode = trim(
            (string) $request->input(
                'old_hs_code'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | NEW HS CODE
        |--------------------------------------------------------------------------
        */

        $newHsCode = trim(
            (string) $request->input(
                'new_hs_code'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATE OLD
        |--------------------------------------------------------------------------
        */

        if (
            $oldHsCode === ''
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'HS Code lama tidak ditemukan.',

            ], 422);

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE NEW
        |--------------------------------------------------------------------------
        */

        if (
            $newHsCode === ''
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'HS Code tidak boleh kosong.',

            ], 422);

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ITEM
        |--------------------------------------------------------------------------
        |
        | Hanya item milik IPL ini yang diubah.
        |--------------------------------------------------------------------------
        */

        $updated = ExportIplItem::where(
            'export_ipl_id',
            $ipl->id
        )
            ->where(
                'hs_code',
                $oldHsCode
            )
            ->update([

                'hs_code' =>
                    $newHsCode,

            ]);


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
                'HS Code berhasil diperbarui.',

            'updated_items' =>
                $updated,

            'old_hs_code' =>
                $oldHsCode,

            'new_hs_code' =>
                $newHsCode,

        ]);
    }public function downloadSiExcel($id)
{
    $ipl = \App\Models\ExportIpl::with([
        'items',
    ])->findOrFail($id);

    return (new ExportSi())->download($ipl);
}
    public function downloadSiPdf($id)
    {
        $ipl = \App\Models\ExportIpl::with([
            'items',
        ])->findOrFail($id);

        $pdf = Pdf::loadView(
            'pages.exports.si_pdf',
            compact('ipl')
        );

        $pdf->setPaper('a4', 'portrait');

        // ==========================================
        // AMANKAN NAMA FILE
        // ==========================================
        $invoiceNo = $ipl->invoice_no ?: $ipl->id;

        $safeInvoiceNo = preg_replace(
            '/[\/\\\\:*?"<>|]+/',
            '-',
            $invoiceNo
        );

        $safeInvoiceNo = trim($safeInvoiceNo, '.- ');

        if ($safeInvoiceNo === '') {
            $safeInvoiceNo = $ipl->id;
        }

        return $pdf->download(
            'SI_' . $safeInvoiceNo . '.pdf'
        );
    }

}
