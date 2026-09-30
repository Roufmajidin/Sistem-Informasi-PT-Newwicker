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
use App\Models\ExportArLegacy;

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

    $arsLegacy = ExportArLegacy::orderByDesc('tanggal_invoice')
        ->orderByDesc('id')
        ->get();

    return view('pages.exports.ar', compact(
        'ars',
        'arsLegacy'
    ));
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
    /*
    |--------------------------------------------------------------------------
    | MASS IMPORT AR LEGACY
    |--------------------------------------------------------------------------
    | Source Excel A:R:
    | A No
    | B Nama Pelanggan
    | C No. PO
    | D No. Invoice
    | E Tanggal Shipment
    | F No. Pengajuan PEB
    | G No. PEB
    | H FOB USD
    | I FOB PEB USD
    | J KURS KEMENKEU
    | K JUMLAH CONTAINER
    | L Jumlah (Rp)
    | M Deposit - Tanggal
    | N Deposit - Jumlah
    | O Pelunasan - Tanggal
    | P Pelunasan - Jumlah
    | Q Sisa Piutang
    | R Keterangan
    |
    | Deposit, pelunasan, dan sisa piutang disimpan langsung ke
    | export_ar_legacy.
    |
    */
  public function addArLegacyMass(Request $request)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA
        |--------------------------------------------------------------------------
        */

        $rows  = $request->input('rows');
        $mCont = $request->input('m_cont');


        /*
        |--------------------------------------------------------------------------
        | VALIDASI M_CONT
        |--------------------------------------------------------------------------
        */

        if (
            $mCont === null ||
            $mCont === '' ||
            !is_numeric($mCont) ||
            (int) $mCont < 1 ||
            (int) $mCont > 12
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Bulan Container (m_cont) wajib diisi angka 1 sampai 12.',
            ], 422);
        }

        $mCont = (int) $mCont;


        /*
        |--------------------------------------------------------------------------
        | VALIDASI ROW
        |--------------------------------------------------------------------------
        */

        if (!is_array($rows) || count($rows) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Data rows kosong atau tidak valid.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | PARSE NUMBER
        |--------------------------------------------------------------------------
        */

        $parseNumber = function ($value) {

            if ($value === null || $value === '') {
                return 0;
            }

            $str = trim((string) $value);

            $str = str_replace(
                [
                    "\xc2\xa0",
                    'Rp',
                    'rp',
                    '$',
                    'USD',
                    'usd',
                    ' '
                ],
                '',
                $str
            );

            if ($str === '' || $str === '-') {
                return 0;
            }


            /*
            |--------------------------------------------------------------------------
            | Format Indonesia / US
            |--------------------------------------------------------------------------
            */

            if (
                strpos($str, ',') !== false &&
                strpos($str, '.') !== false
            ) {

                /*
                | 19.335,50
                */

                if (strrpos($str, ',') > strrpos($str, '.')) {

                    $str = str_replace('.', '', $str);
                    $str = str_replace(',', '.', $str);

                }

                /*
                | 19,335.50
                */

                else {

                    $str = str_replace(',', '', $str);

                }

            }

            /*
            | Hanya koma
            */

            elseif (strpos($str, ',') !== false) {

                $parts = explode(',', $str);

                $last = end($parts);

                if (strlen($last) <= 2) {

                    $str = str_replace(',', '.', $str);

                } else {

                    $str = str_replace(',', '', $str);

                }

            }

            /*
            | Banyak titik
            */

            elseif (substr_count($str, '.') > 1) {

                $str = str_replace('.', '', $str);

            }


            return is_numeric($str)
                ? (float) $str
                : 0;
        };


        /*
        |--------------------------------------------------------------------------
        | PARSE DATE
        |--------------------------------------------------------------------------
        */

        $parseDate = function ($value) {

            if (
                $value === null ||
                trim((string) $value) === ''
            ) {
                return null;
            }

            $value = trim((string) $value);


            foreach ([
                'm/d/Y',
                'm-d-Y',
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
            ] as $format) {

                try {

                    return \Carbon\Carbon::createFromFormat(
                        $format,
                        $value
                    )->format('Y-m-d');

                } catch (\Throwable $e) {

                    // lanjut

                }
            }


            /*
            |--------------------------------------------------------------------------
            | Excel Serial Date
            |--------------------------------------------------------------------------
            */

            if (
                is_numeric($value) &&
                (float) $value > 20000
            ) {

                try {

                    return \PhpOffice\PhpSpreadsheet\Shared\Date
                        ::excelToDateTimeObject((float) $value)
                        ->format('Y-m-d');

                } catch (\Throwable $e) {

                    // abaikan
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Carbon fallback
            |--------------------------------------------------------------------------
            */

            try {

                return \Carbon\Carbon::parse($value)
                    ->format('Y-m-d');

            } catch (\Throwable $e) {

                return null;
            }
        };


        /*
        |--------------------------------------------------------------------------
        | AMBIL TANGGAL DARI NOMOR INVOICE
        |
        | Contoh:
        | INV-xxx/NWxx/04/2026
        |--------------------------------------------------------------------------
        */

        $invoiceDateFromNumber = function ($invoice) {

            if (
                preg_match(
                    '/\/(\d{1,2})\/(\d{4})\s*$/',
                    (string) $invoice,
                    $match
                )
            ) {

                $month = (int) $match[1];
                $year  = (int) $match[2];

                if (
                    $month >= 1 &&
                    $month <= 12
                ) {

                    return sprintf(
                        '%04d-%02d-01',
                        $year,
                        $month
                    );
                }
            }

            return null;
        };


        /*
        |--------------------------------------------------------------------------
        | HELPER GET DATA
        |--------------------------------------------------------------------------
        */

        $get = function (
            $row,
            array $keys = [],
            $index = null
        ) {

            if (!is_array($row)) {
                return '';
            }


            foreach ($keys as $key) {

                if (
                    array_key_exists($key, $row) &&
                    $row[$key] !== null &&
                    trim((string) $row[$key]) !== ''
                ) {

                    return $row[$key];
                }
            }


            if (
                $index !== null &&
                array_key_exists($index, $row)
            ) {

                return $row[$index];
            }


            return '';
        };


        /*
        |--------------------------------------------------------------------------
        | COUNTER
        |--------------------------------------------------------------------------
        */

        $inserted = 0;
        $skipped  = 0;
        $errors   = [];


        /*
        |--------------------------------------------------------------------------
        | LOOP ROW
        |--------------------------------------------------------------------------
        */

        foreach ($rows as $rowIndex => $row) {

            try {

                if (!is_array($row)) {

                    $skipped++;

                    $errors[] =
                        'Baris ' .
                        ($rowIndex + 1) .
                        ': format row tidak valid.';

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | MAPPING EXCEL A:R
                |--------------------------------------------------------------------------
                */

                $customer = trim((string) $get(
                    $row,
                    [
                        'nama_pelanggan',
                        'customer',
                        'buyer',
                        'nama_customer'
                    ],
                    1
                ));


                $po = trim((string) $get(
                    $row,
                    [
                        'no_po',
                        'po',
                        'customer_po_no'
                    ],
                    2
                ));


                $invoice = trim((string) $get(
                    $row,
                    [
                        'no_invoice',
                        'invoice',
                        'invoice_no'
                    ],
                    3
                ));


                $shipmentRaw = $get(
                    $row,
                    [
                        'tanggal_shipment',
                        'shipment_date',
                        'tanggal_shipment_raw'
                    ],
                    4
                );


                $pengajuanPeb = trim((string) $get(
                    $row,
                    [
                        'no_pengajuan_peb',
                        'pengajuan_peb'
                    ],
                    5
                ));


                $noPeb = trim((string) $get(
                    $row,
                    [
                        'no_peb',
                        'peb'
                    ],
                    6
                ));


                $fobUsd = $parseNumber(
                    $get(
                        $row,
                        ['fob_usd', 'fob'],
                        7
                    )
                );


                $fobPebUsd = $parseNumber(
                    $get(
                        $row,
                        ['fob_peb_usd', 'fob_peb'],
                        8
                    )
                );


                $kurs = $parseNumber(
                    $get(
                        $row,
                        ['kurs_kemenkeu', 'kurs'],
                        9
                    )
                );


                $container = (int) round(
                    $parseNumber(
                        $get(
                            $row,
                            [
                                'jumlah_container',
                                'container'
                            ],
                            10
                        )
                    )
                );


                /*
                | Jumlah Rp dari Excel
                */

                $jumlahRp = $parseNumber(
                    $get(
                        $row,
                        [
                            'jumlah_rupiah',
                            'jumlah_rp'
                        ],
                        11
                    )
                );


                /*
                | Deposit
                */

                $depositDate = $parseDate(
                    $get(
                        $row,
                        [
                            'deposit_date',
                            'deposit_tanggal',
                            'deposit_payment_date'
                        ],
                        12
                    )
                );


                $depositUsd = $parseNumber(
                    $get(
                        $row,
                        [
                            'deposit_usd',
                            'deposit_amount',
                            'deposit',
                            'uang_muka'
                        ],
                        13
                    )
                );


                /*
                | Pelunasan
                */

                $pelunasanDate = $parseDate(
                    $get(
                        $row,
                        [
                            'pelunasan_date',
                            'pelunasan_tanggal',
                            'pelunasan_payment_date'
                        ],
                        14
                    )
                );


                $pelunasanUsd = $parseNumber(
                    $get(
                        $row,
                        [
                            'pelunasan_usd',
                            'pelunasan_amount',
                            'pelunasan'
                        ],
                        15
                    )
                );


                /*
                | Sisa Piutang
                */

                $sisaPiutangUsd = $parseNumber(
                    $get(
                        $row,
                        [
                            'sisa_piutang_usd',
                            'sisa_piutang',
                            'sisa'
                        ],
                        16
                    )
                );


                $keterangan = trim((string) $get(
                    $row,
                    [
                        'keterangan',
                        'remark',
                        'description'
                    ],
                    17
                ));


                /*
                |--------------------------------------------------------------------------
                | VALIDASI DASAR
                |--------------------------------------------------------------------------
                */

                if (
                    $customer === '' &&
                    $invoice === ''
                ) {

                    $skipped++;

                    continue;
                }


                if ($invoice === '') {

                    $skipped++;

                    $errors[] =
                        'Baris ' .
                        ($rowIndex + 1) .
                        ': No. Invoice kosong.';

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | DUPLICATE INVOICE
                |--------------------------------------------------------------------------
                */

                if (
                    ExportArLegacy::where(
                        'no_invoice',
                        $invoice
                    )->exists()
                ) {

                    $skipped++;

                    $errors[] =
                        'Baris ' .
                        ($rowIndex + 1) .
                        ': Invoice ' .
                        $invoice .
                        ' sudah ada.';

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | TANGGAL SHIPMENT
                |--------------------------------------------------------------------------
                */

                $shipment = $parseDate($shipmentRaw);


                if (
                    $shipmentRaw !== null &&
                    trim((string) $shipmentRaw) !== '' &&
                    !$shipment
                ) {

                    $skipped++;

                    $errors[] =
                        'Baris ' .
                        ($rowIndex + 1) .
                        ' / ' .
                        $invoice .
                        ': tanggal shipment tidak valid.';

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | TANGGAL INVOICE
                |--------------------------------------------------------------------------
                */

                $invoiceDate =
                    $invoiceDateFromNumber($invoice)
                    ?: $shipment;


                /*
                |--------------------------------------------------------------------------
                | JUMLAH RUPIAH
                |--------------------------------------------------------------------------
                */

                if (
                    $jumlahRp <= 0 &&
                    $fobUsd > 0 &&
                    $kurs > 0
                ) {

                    $jumlahRp =
                        $fobUsd *
                        $kurs;
                }


                /*
                |--------------------------------------------------------------------------
                | SISA PIUTANG
                |--------------------------------------------------------------------------
                */

                if (
                    $sisaPiutangUsd == 0 &&
                    (
                        $fobUsd > 0 ||
                        $depositUsd > 0 ||
                        $pelunasanUsd > 0
                    )
                ) {

                    $sisaPiutangUsd =
                        $fobUsd -
                        $depositUsd -
                        $pelunasanUsd;
                }


                if (
                    $sisaPiutangUsd < 0 &&
                    abs($sisaPiutangUsd) < 0.01
                ) {

                    $sisaPiutangUsd = 0;
                }


                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                */

                $legacy = new ExportArLegacy();


                $legacy->forceFill([

                    'nama_pelanggan' =>
                        $customer,

                    'no_po' =>
                        $po,

                    'no_invoice' =>
                        $invoice,

                    'tanggal_invoice' =>
                        $invoiceDate,

                    'tanggal_shipment' =>
                        $shipment,

                    'no_pengajuan_peb' =>
                        $pengajuanPeb,

                    'no_peb' =>
                        $noPeb,

                    'fob_usd' =>
                        $fobUsd,

                    'fob_peb_usd' =>
                        $fobPebUsd,

                    'kurs_kemenkeu' =>
                        $kurs,

                    'jumlah_container' =>
                        $container,

                    /*
                    |--------------------------------------------------------------------------
                    | INI YANG SEBELUMNYA HILANG
                    |--------------------------------------------------------------------------
                    */

                    'm_cont' =>
                        $mCont,

                    'deposit_date' =>
                        $depositDate,

                    'deposit_usd' =>
                        $depositUsd,

                    'pelunasan_date' =>
                        $pelunasanDate,

                    'pelunasan_usd' =>
                        $pelunasanUsd,

                    'sisa_piutang_usd' =>
                        $sisaPiutangUsd,

                    'keterangan' =>
                        (
                            $keterangan !== '' &&
                            $keterangan !== '-'
                        )
                            ? $keterangan
                            : null,

                    'remark' =>
                        'IMPORT AR LAMA',

                    'created_by' =>
                        auth()->id(),

                ]);


                $legacy->save();


                $inserted++;

            } catch (\Throwable $e) {

                $skipped++;

                $errors[] =
                    'Baris ' .
                    ($rowIndex + 1) .
                    ': ' .
                    $e->getMessage();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                true,

            'message' =>
                "Import AR lama selesai. {$inserted} data masuk, {$skipped} dilewati.",

            'inserted_count' =>
                $inserted,

            'skipped_count' =>
                $skipped,

            'm_cont' =>
                $mCont,

            'errors' =>
                array_slice(
                    $errors,
                    0,
                    50
                ),

        ]);

    } catch (\Throwable $e) {

        return response()->json([

            'success' =>
                false,

            'message' =>
                $e->getMessage(),

        ], 500);
    }
}
    public function updateArLegacyField(Request $request, $id)
{
    try {

        $ar = ExportArLegacy::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | FIELD YANG BOLEH DIEDIT
        |--------------------------------------------------------------------------
        */

        $allowed = [
            'nama_pelanggan',
            'no_po',
            'no_invoice',
            'tanggal_shipment',
            'no_pengajuan_peb',
            'no_peb',

            'fob_usd',
            'fob_peb_usd',
            'kurs_kemenkeu',
            'jumlah_container',

            'deposit_date',
            'deposit_usd',

            'pelunasan_date',
            'pelunasan_usd',

            'keterangan',
        ];


        /*
        |--------------------------------------------------------------------------
        | VALIDASI FIELD
        |--------------------------------------------------------------------------
        */

        if (!in_array($request->field, $allowed, true)) {

            return response()->json([
                'success' => false,
                'message' => 'Field AR Legacy tidak diperbolehkan.'
            ], 422);

        }


        $field = $request->field;
        $value = $request->value;


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        if (in_array($field, [
            'tanggal_shipment',
            'deposit_date',
            'pelunasan_date',
        ], true)) {

            $value = $value ?: null;

        }


        /*
        |--------------------------------------------------------------------------
        | NUMERIC
        |--------------------------------------------------------------------------
        |
        | Karena input dari Blade bisa berupa:
        |
        | 19,335.00
        | 19.335,00
        | $ 19,335.00
        | Rp 19.335,00
        |
        */

        if (in_array($field, [
            'fob_usd',
            'fob_peb_usd',
            'kurs_kemenkeu',
            'deposit_usd',
            'pelunasan_usd',
            'jumlah_container',
        ], true)) {

            $raw = trim((string) $value);

            // Hapus currency / whitespace
            $raw = str_replace([
                'Rp',
                'rp',
                '$',
                'USD',
                'usd',
                ' ',
                "\xc2\xa0",
            ], '', $raw);

            /*
            |--------------------------------------------------------------------------
            | DETEKSI FORMAT ANGKA
            |--------------------------------------------------------------------------
            */

            if (
                str_contains($raw, ',') &&
                str_contains($raw, '.')
            ) {

                $lastComma = strrpos($raw, ',');
                $lastDot   = strrpos($raw, '.');

                /*
                | 19.335,50
                | berarti format Indonesia
                */

                if ($lastComma > $lastDot) {

                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);

                }

                /*
                | 19,335.50
                | berarti format US
                */

                else {

                    $raw = str_replace(',', '', $raw);

                }

            }

            elseif (str_contains($raw, ',')) {

                /*
                | Kalau hanya ada koma:
                | 19335,50 -> 19335.50
                */

                $parts = explode(',', $raw);

                if (
                    count($parts) === 2 &&
                    strlen($parts[1]) <= 2
                ) {

                    $raw = $parts[0] . '.' . $parts[1];

                } else {

                    $raw = str_replace(',', '', $raw);

                }

            }

            elseif (substr_count($raw, '.') > 1) {

                /*
                | Contoh:
                | 19.335.500
                */

                $raw = str_replace('.', '', $raw);

            }

            $value = is_numeric($raw)
                ? (float) $raw
                : 0;

        }


        /*
        |--------------------------------------------------------------------------
        | JUMLAH CONTAINER
        |--------------------------------------------------------------------------
        */

        if ($field === 'jumlah_container') {

            $value = (int) round((float) $value);

        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN FIELD YANG DIUBAH
        |--------------------------------------------------------------------------
        */

        $ar->{$field} = $value;


        /*
        |--------------------------------------------------------------------------
        | HITUNG ULANG SISA PIUTANG
        |--------------------------------------------------------------------------
        |
        | Sisa = FOB USD
        |      - Deposit USD
        |      - Pelunasan USD
        |
        */

        $fobUsd = (float) $ar->fob_usd;

        $depositUsd = (float) $ar->deposit_usd;

        $pelunasanUsd = (float) $ar->pelunasan_usd;


        $sisaPiutang = $fobUsd
            - $depositUsd
            - $pelunasanUsd;


        /*
        |--------------------------------------------------------------------------
        | Jangan negatif
        |--------------------------------------------------------------------------
        */

        if ($sisaPiutang < 0) {
            $sisaPiutang = 0;
        }


        $ar->sisa_piutang_usd = round(
            $sisaPiutang,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN
        |--------------------------------------------------------------------------
        */

        $ar->save();


        /*
        |--------------------------------------------------------------------------
        | JUMLAH RP
        |--------------------------------------------------------------------------
        |
        | Mengikuti Excel Legacy:
        |
        | FOB USD × Kurs Kemenkeu
        |
        */

        $jumlahRp =
            (float) $ar->fob_usd
            *
            (float) $ar->kurs_kemenkeu;


        return response()->json([
            'success' => true,

            'message' => 'Data AR Legacy berhasil diperbarui.',

            'data' => [
                'id' => $ar->id,

                'field' => $field,

                'value' => $ar->{$field},

                'sisa_piutang_usd' => (float) $ar->sisa_piutang_usd,

                'jumlah_rupiah' => round(
                    $jumlahRp,
                    2
                ),
            ],
        ]);


    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);

    }
}
}