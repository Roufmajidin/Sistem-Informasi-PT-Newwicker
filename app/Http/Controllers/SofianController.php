<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ExportIpl;
use App\Models\ExportIplItem;
use App\Models\Po;
use App\Models\ExportAr;
use App\Models\ExportArPayment;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Exports\PackingListExport;
use App\Exports\CommercialInvoiceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomCommercialInvoiceExport;
class SofianController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD INVOICE LIST
    |--------------------------------------------------------------------------
    | EXISTING FUNCTION - TIDAK DIUBAH
    |--------------------------------------------------------------------------
    */
    public function downloadCustomCommercial($id)
    {
        $invoice = ExportIpl::with([
            'items',
            'pos',
            'creator'
        ])->findOrFail($id);

        return (new CustomCommercialInvoiceExport())
            ->download($invoice);
    }
    public function downloadInvoiceList($id)
    {
        // 1. Fetch invoice data along with relations
        $invoice = ExportIpl::with([
            'items',
            'pos',
            'creator'
        ])->findOrFail($id);

        $fileName = 'Commercial_Invoice_' .
            str_replace(
                ['/', '\\', ' '],
                '_',
                $invoice->invoice_no
            ) .
            '.xlsx';

        // 2. Export Excel using Laravel-Excel (Maatwebsite)
        return Excel::download(
            new CommercialInvoiceExport($invoice),
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD PACKING LIST
    |--------------------------------------------------------------------------
    | EXISTING FUNCTION - TIDAK DIUBAH
    |--------------------------------------------------------------------------
    */
    public function downloadPackingList($id)
    {
        $ipl = ExportIpl::with([
            'items',
            'pos',
            'creator',
        ])->findOrFail($id);

        $fileName = 'PACKING_LIST_' .
            str_replace(
                '/',
                '_',
                $ipl->invoice_no ?? $ipl->id
            ) .
            '.xlsx';

        return Excel::download(
            new PackingListExport($ipl),
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CUSTOM INVOICE DATA
    |--------------------------------------------------------------------------
    | AJAX:
    | GET /export/{id}/custom-invoice/data
    |
    | Fungsi ini khusus mengambil data untuk modal CI.
    | Tidak mengubah data database.
    |--------------------------------------------------------------------------
    */
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


    /*
    |--------------------------------------------------------------------------
    | UPDATE CUSTOM DESCRIPTION
    |--------------------------------------------------------------------------
    | AJAX:
    | PATCH /export/{id}/custom-invoice/description
    |
    | PENTING:
    | Update menggunakan item_ids, BUKAN hanya hs_code.
    |
    | Contoh:
    | HS 9403.83.0:
    | ID 304
    | ID 306
    | ID 305
    |
    | Maka ketiga ID tersebut dikirim dari Blade dan semuanya di-update.
    |--------------------------------------------------------------------------
    */
    public function updateCustomDescription(
        Request $request,
        $id
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'item_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'item_ids.*' => [
                'required',
                'integer',
            ],

            'desc_custome' => [
                'nullable',
                'string',
                'max:500',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | IPL
        |--------------------------------------------------------------------------
        */

        $ipl = ExportIpl::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE ITEM IDS
        |--------------------------------------------------------------------------
        */

        $itemIds = collect(
            $validated['item_ids']
        )
            ->map(function ($itemId) {

                return (int) $itemId;

            })
            ->filter(function ($itemId) {

                return $itemId > 0;

            })
            ->unique()
            ->values()
            ->toArray();


        if (empty($itemIds)) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Item ID tidak ditemukan.',

            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | CUSTOM DESCRIPTION
        |--------------------------------------------------------------------------
        */

        $descCustome = trim(
            (string) (
                $validated['desc_custome'] ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        |
        | Wajib where export_ipl_id agar ID item dari IPL lain
        | tidak ikut berubah.
        |--------------------------------------------------------------------------
        */

        $updated = ExportIplItem::query()

            ->where(
                'export_ipl_id',
                $ipl->id
            )

            ->whereIn(
                'id',
                $itemIds
            )

            ->update([

                'desc_custome' =>
                    $descCustome !== ''
                    ? $descCustome
                    : null,

                'updated_at' =>
                    now(),

            ]);


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' =>
                'Custom Description berhasil disimpan.',

            'updated' =>
                $updated,

            'item_ids' =>
                $itemIds,

            'desc_custome' =>
                $descCustome,

        ]);
    }
    public function arBuyer()
    {
        $ars = ExportAr::with([
            'exportIpl.pos',
            'exportIpl.items',
            'payments',
        ])
            ->orderByDesc('tanggal_invoice')
            ->orderByDesc('id')
            ->get();

        return view('pages.exports.ar', compact('ars'));
    }
    public function updateArField(Request $request, $id)
    {
        try {

            $ar = ExportAr::findOrFail($id);

            $allowed = [
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

            if (!in_array($request->field, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field tidak diperbolehkan.'
                ], 422);
            }

            $value = $request->value;

            if (
                in_array($request->field, [
                    'tanggal_invoice',
                    'jatuh_tempo'
                ])
            ) {
                $value = $value ?: null;
            }

            $ar->{$request->field} = $value;
            $ar->save();

            return response()->json([
                'success' => true,
                'message' => 'Data AR berhasil diperbarui.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function updateArIplField(Request $request, $id)
    {
        try {

            $ipl = ExportIpl::findOrFail($id);

            $allowed = [
                'invoice_no',
                'buyer',
                'buyer_address',
                'customer_code',
                'customer_po_no',
                'release_date',
            ];

            if (!in_array($request->field, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field tidak diperbolehkan.'
                ], 422);
            }

            $ipl->{$request->field} = $request->value;
            $ipl->save();

            return response()->json([
                'success' => true,
                'message' => 'Data IPL berhasil diperbarui.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ADD PAYMENT
    |--------------------------------------------------------------------------
    | $arId = ID ExportAr
    |
    | Dipakai untuk:
    | - Deposit
    | - Pelunasan
    | - Surcharge
    |
    */

    public function addArPayment(Request $request, $arId)
    {
        try {

            // =====================================================
            // CARI AR
            // =====================================================

            $ar = ExportAr::findOrFail($arId);


            // =====================================================
            // VALIDASI
            // =====================================================

            $request->validate([
                'payment_type' => [
                    'required',
                    'in:deposit,pelunasan,surcharge'
                ],

                'payment_date' => [
                    'required',
                    'date'
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'min:0.01'
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:255'
                ],

                'keterangan' => [
                    'nullable',
                    'string'
                ],
            ]);


            // =====================================================
            // BUAT PAYMENT
            // =====================================================

            $payment = ExportArPayment::create([
                'export_ar_id' => $ar->id,
                'payment_type' => $request->payment_type,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);


            // =====================================================
            // RESPONSE
            // =====================================================

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil ditambahkan.',
                'payment_id' => $payment->id,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE EXISTING PAYMENT
    |--------------------------------------------------------------------------
    | $id = ID ExportArPayment
    |
    | Ini TIDAK membuat payment baru.
    | Hanya mengubah payment yang sudah ada.
    |
    */

    public function updateArPaymentField(Request $request, $id)
    {
        try {

            $payment = ExportArPayment::findOrFail($id);

            $allowed = [
                'payment_date',
                'amount',
                'reference',
                'keterangan',
            ];

            if (!in_array($request->field, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field pembayaran tidak diperbolehkan.'
                ], 422);
            }

            $value = $request->value;

            if ($request->field === 'payment_date') {
                $value = $value ?: null;
            }

            $payment->{$request->field} = $value;
            $payment->save();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diperbarui.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}