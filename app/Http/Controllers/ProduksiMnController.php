<?php

namespace App\Http\Controllers;

use App\Models\DetailPo;
use App\Models\InspectSchedule;
use App\Models\Kategori;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestApproval;
use App\Models\PaymentRequestSaved;
use App\Models\Po;
use App\Models\MonitoringInvoice;
use App\Models\ProductionTimeline;
use App\Models\SignatureSpk;
use App\Models\Spk;
use App\Models\Supplier;
use App\Models\TransaksiStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\BarangJadiExport;
use App\Exports\AdminReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SpkLama;
use App\Models\InvLama;
use Carbon\Carbon;
class ProduksiMnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function qcReport($inspectScheduleId)
    {
        /*
    |--------------------------------------------------------------------------
    | INSPECT
    |--------------------------------------------------------------------------
    */
        $inspect = InspectSchedule::with([
            'kategori',
        ])->findOrFail($inspectScheduleId);
        /*
    |--------------------------------------------------------------------------
    | GET QC REPORT
    |--------------------------------------------------------------------------
    */
        $reports = DB::table('qc_report')
            ->where(
                'inspect_schedule_id',
                $inspect->id
            )
            ->orderBy('check_point_id')
            ->get();
        /*
    |--------------------------------------------------------------------------
    | GET PHOTOS
    |--------------------------------------------------------------------------
    */
        $photos = DB::table('report_photo')
            ->where(
                'inspect_schedule_id',
                $inspect->id
            )
            ->get();
        /*
    |--------------------------------------------------------------------------
    | GROUPED
    |--------------------------------------------------------------------------
    */
        $grouped = [];
        foreach ($reports as $report) {
            $dateKey = \Carbon\Carbon::parse(
                $report->created_at
            )->format('Y-m-d');
            /*
        |--------------------------------------------------------------------------
        | REMARK
        |--------------------------------------------------------------------------
        */
            $remark = [];
            if ($report->remark) {
                $decoded = json_decode(
                    $report->remark,
                    true
                );
                if (
                    json_last_error()
                    === JSON_ERROR_NONE
                ) {
                    $remark = $decoded;
                } else {
                    $remark = [
                        'text' => $report->remark,
                    ];
                }
            }
            /*
        |--------------------------------------------------------------------------
        | PUSH DATA
        |--------------------------------------------------------------------------
        */
            $grouped[$dateKey][] = [
                'check_point_id' => $report->check_point_id,
                'remark' => $remark,
                'created_at' => $report->created_at,
            ];
        }
        /*
    |--------------------------------------------------------------------------
    | DETAIL PO
    |--------------------------------------------------------------------------
    */
        $detailPo = DB::table('detail_po')
            ->where(
                'id',
                $inspect->detail_po_id
            )
            ->first();
        $detailData = [];
        $itemName = '-';
        $articleCode = '-';
        $qty = '-';
        $itemImage = null;
        if ($detailPo && $detailPo->detail) {
            $detailData = json_decode(
                $detailPo->detail,
                true
            );
            /*
        |--------------------------------------------------------------------------
        | ITEM INFO
        |--------------------------------------------------------------------------
        */
            $itemName =
                $detailData['description'] ?? $detailData['nama'] ?? $detailData['item'] ?? '-';
            $articleCode =
                $detailData['article'] ?? $detailData['article_code'] ?? $detailData['article_no'] ?? $detailData['code'] ?? '-';
            $qty =
                $detailData['qty'] ?? '-';
            $itemImage =
                $detailData['photo'] ?? null;
        }
        /*
    |--------------------------------------------------------------------------
    | PFI SIZE
    |--------------------------------------------------------------------------
    */
        $pfi = [
            'w' => $detailData['w'] ?? '-',
            'd' => $detailData['d'] ?? '-',
            'h' => $detailData['h'] ?? '-',
            'sw' => $detailData['sw'] ?? '-',
            'sd' => $detailData['sd'] ?? '-',
            'sh' => $detailData['sh'] ?? '-',
        ];

        /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */
        return view(
            'pages.management.qc_report',
            [
                'inspect' => $inspect,
                'grouped' => $grouped,
                'photos' => $photos,
                'detailData' => $detailData,
                'itemName' => $itemName,
                'articleCode' => $articleCode,
                'qty' => $qty,
                'itemImage' => $itemImage,
                'pfi' => $pfi,
            ]
        );
    }

    // monitoring
    // monitoring
    // pew
    // monitoring
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        $searchPo = $request->search_po;
        $selectedDate = $request->tanggal;


        /*
        |--------------------------------------------------------------------------
        | CATEGORY MAP
        |--------------------------------------------------------------------------
        */

        $categories = [
            'rangka' => 'rangka',
            'anyam' => 'anyam',
            'unfinish' => 'unfinish',
            'final' => 'final',
            'decor' => 'decor',

            'packaging' => 'box',
            'box' => 'box',
        ];


        /*
        |--------------------------------------------------------------------------
        | INSPECTION CATEGORY ID
        |--------------------------------------------------------------------------
        |
        | 4 = Rangka
        | 5 = Anyam
        | 6 = Unfinish
        | 7 = Final
        |
        */

        $inspectionCategoryMap = [
            4 => 'rangka',
            5 => 'anyam',
            6 => 'unfinish',
            7 => 'final',
        ];


        /*
        |--------------------------------------------------------------------------
        | GET AVAILABLE DATES
        |--------------------------------------------------------------------------
        */

        $dates = InspectSchedule::query()
            ->when($searchPo, function ($q) use ($searchPo) {

                $q->whereHas('po', function ($qq) use ($searchPo) {

                    $qq->where(
                        'order_no',
                        'like',
                        '%' . $searchPo . '%'
                    );
                });
            })
            ->select('tanggal_inspect')
            ->distinct()
            ->orderBy('tanggal_inspect')
            ->pluck('tanggal_inspect');


        /*
        |--------------------------------------------------------------------------
        | GET PO
        |--------------------------------------------------------------------------
        */

        $poQuery = Po::with([
            'detailPos',
            'spks',
        ]);


        /*
        |--------------------------------------------------------------------------
        | SORT RELEASE DATE
        |--------------------------------------------------------------------------
        */

        $sort = strtolower($request->input('sort', 'desc'));

        if (!in_array($sort, ['asc', 'desc'])) {
            $sort = 'desc';
        }

        $poQuery->orderBy('release_date', $sort);


        /*
        |--------------------------------------------------------------------------
        | FILTER SEARCH
        |--------------------------------------------------------------------------
        */

        if ($searchPo) {

            $poQuery->where(function ($q) use ($searchPo) {

                $q->where(
                    'order_no',
                    'like',
                    '%' . $searchPo . '%'
                )

                    ->orWhere(
                        'company_name',
                        'like',
                        '%' . $searchPo . '%'
                    );

            });

        }


        /*
        |--------------------------------------------------------------------------
        | FILTER COMPANY
        |--------------------------------------------------------------------------
        | NW  = hanya PO NW
        | NWS = hanya PO NWS
        | all = semua
        |--------------------------------------------------------------------------
        */

        $brand = strtolower(
            trim($request->input('brand', 'all'))
        );

        if ($brand === 'nw') {

            $poQuery
                ->where('order_no', 'like', 'NW%')
                ->where('order_no', 'not like', 'NWS%');

        } elseif ($brand === 'nws') {

            $poQuery
                ->where('order_no', 'like', 'NWS%');

        }


        /*
        |--------------------------------------------------------------------------
        | GET DATA
        |--------------------------------------------------------------------------
        */

        $pos = $poQuery->get();


        /*
        |--------------------------------------------------------------------------
        | GET ALL DETAIL PO IDS
        |--------------------------------------------------------------------------
        */

        $detailPoIds = [];

        foreach ($pos as $po) {

            foreach ($po->detailPos as $detailPo) {

                $detailPoIds[] = $detailPo->id;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PRELOAD INSPECTION
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | Untuk HEADER MONITORING:
        |
        |     detail_po_id
        |          +
        |     kategori_id
        |
        | yang menentukan kolom.
        |
        | Tidak bergantung kepada kategori SPK.
        |
        | Contoh:
        |
        | detail_po_id = 459
        | kategori_id  = 5
        | passed       = 13
        |
        | hasil:
        |
        | anyam_pass = 13
        |
        |--------------------------------------------------------------------------
        */

        $inspectQuery = InspectSchedule::query();


        /*
        |--------------------------------------------------------------------------
        | HANYA INSPECTION DARI ITEM YANG ADA DI PO YANG DITAMPILKAN
        |--------------------------------------------------------------------------
        */

        if (!empty($detailPoIds)) {

            $inspectQuery->whereIn(
                'detail_po_id',
                $detailPoIds
            );

        } else {

            /*
            |--------------------------------------------------------------------------
            | Kalau tidak ada detail PO
            |--------------------------------------------------------------------------
            */

            $inspectQuery->whereRaw('1 = 0');
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
        */

        if ($selectedDate) {

            $inspectQuery->whereDate(
                'tanggal_inspect',
                $selectedDate
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GROUP BY DETAIL PO
        |--------------------------------------------------------------------------
        */

        $allInspects = $inspectQuery
            ->get()
            ->groupBy(function ($item) {

                return (string) $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | PRELOAD INVENTORY
        |--------------------------------------------------------------------------
        */

        $allInventories = ProductionTimeline::query()
            ->whereIn(
                'detail_po_id',
                $detailPoIds
            )
            ->get()
            ->groupBy('detail_po_id');


        /*
        |--------------------------------------------------------------------------
        | PRELOAD SPK
        |--------------------------------------------------------------------------
        */

        $allSpks = Spk::query()
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | PRELOAD QC TOTAL PER SPK
        |--------------------------------------------------------------------------
        |
        | Ini khusus untuk QC RESULT pada modal SPK.
        |
        | Bukan untuk menentukan header Rangka / Anyam.
        |
        */

        $inspectTotals = InspectSchedule::query()
            ->selectRaw('
            spk_id,
            detail_po_id,
            SUM(passed) as total_passed,
            SUM(rejected) as total_rejected
        ')
            ->whereNotNull('spk_id')
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {

                return
                    $item->spk_id
                    . '_'
                    . $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */

        $datas = [];


        /*
        |--------------------------------------------------------------------------
        | LOOP PO
        |--------------------------------------------------------------------------
        */

        foreach ($pos as $po) {

            $poId = $po->id;


            $datas[$poId] = [

                'po_number' =>
                    $po->order_no,

                'buyer_name' =>
                    $po->company_name
                    ?? $po->company_name
                    ?? $po->buyer
                    ?? '',

                'items' => [],
            ];


            /*
            |--------------------------------------------------------------------------
            | LOOP DETAIL PO / ITEM
            |--------------------------------------------------------------------------
            */

            foreach ($po->detailPos as $detailPo) {


                /*
                |--------------------------------------------------------------------------
                | DETAIL
                |--------------------------------------------------------------------------
                */

                $detail =
                    $detailPo->detail ?? [];


                if (is_string($detail)) {

                    $detail = json_decode(
                        $detail,
                        true
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | ITEM INFO
                |--------------------------------------------------------------------------
                */

                $qty =
                    $detail['qty']
                    ?? 0;


                $itemName =
                    $detail['description']
                    ?? $detail['nama']
                    ?? $detail['item']
                    ?? '-';


                $image =
                    $detail['photo']
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | DEFAULT ITEM
                |--------------------------------------------------------------------------
                */

                $itemData = [

                    'item_name' =>
                        $itemName,

                    'item_image' =>
                        $image,

                    'qty' =>
                        $qty,

                    'spks' =>
                        [],
                ];


                /*
                |--------------------------------------------------------------------------
                | INIT ALL CATEGORY STATUS
                |--------------------------------------------------------------------------
                */

                foreach ($categories as $category) {

                    $itemData[
                        $category . '_pass'
                    ] = 0;

                    $itemData[
                        $category . '_reject'
                    ] = 0;

                    $itemData[
                        $category . '_in'
                    ] = 0;

                    $itemData[
                        $category . '_out'
                    ] = 0;
                }


                /*
                |--------------------------------------------------------------------------
                | GET INSPECTION MILIK DETAIL PO INI
                |--------------------------------------------------------------------------
                |
                | CONTOH ALINA:
                |
                | detail_po_id = 459
                |
                | inspection:
                |
                | kategori_id = 5
                | passed      = 13
                |
                | Maka:
                |
                | anyam_pass = 13
                |
                |--------------------------------------------------------------------------
                */

                $inspects =
                    $allInspects[
                        (string) $detailPo->id
                    ] ?? collect();


                /*
                |--------------------------------------------------------------------------
                | LOOP INSPECTION
                |--------------------------------------------------------------------------
                */

                foreach ($inspects as $inspect) {


                    /*
                    |--------------------------------------------------------------------------
                    | CATEGORY ID INSPECTION
                    |--------------------------------------------------------------------------
                    */

                    $kategoriId =
                        (int) $inspect->kategori_id;


                    /*
                    |--------------------------------------------------------------------------
                    | TENTUKAN HEADER
                    |--------------------------------------------------------------------------
                    */

                    $prefix =
                        $inspectionCategoryMap[
                            $kategoriId
                        ] ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | CATEGORY TIDAK TERDAFTAR
                    |--------------------------------------------------------------------------
                    */

                    if (!$prefix) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PASSED
                    |--------------------------------------------------------------------------
                    */

                    $passed =
                        (int) (
                            $inspect->passed
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | REJECTED
                    |--------------------------------------------------------------------------
                    */

                    $rejected =
                        (int) (
                            $inspect->rejected
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | MASUKKAN KE HEADER
                    |--------------------------------------------------------------------------
                    */

                    $itemData[
                        $prefix . '_pass'
                    ] += $passed;


                    $itemData[
                        $prefix . '_reject'
                    ] += $rejected;
                }


                /*
                |--------------------------------------------------------------------------
                | GET SPK
                |--------------------------------------------------------------------------
                */

                foreach ($po->spks as $spk) {


                    /*
                    |--------------------------------------------------------------------------
                    | SPK DATA
                    |--------------------------------------------------------------------------
                    */

                    $spkData =
                        $spk->data;


                    if (is_string($spkData)) {

                        $spkData = json_decode(
                            $spkData,
                            true
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SPK ITEMS
                    |--------------------------------------------------------------------------
                    */

                    $spkItems =
                        $spkData['items']
                        ?? [];


                    /*
                    |--------------------------------------------------------------------------
                    | LOOP SPK ITEMS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($spkItems as $spkItem) {


                        /*
                        |--------------------------------------------------------------------------
                        | DETAIL PO HARUS SAMA
                        |--------------------------------------------------------------------------
                        */

                        if (
                            ($spkItem['detail_po_id'] ?? null)
                            != $detailPo->id
                        ) {

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | QC TOTAL KEY
                        |--------------------------------------------------------------------------
                        */

                        $inspectTotalKey =
                            $spk->id
                            . '_'
                            . $detailPo->id;


                        /*
                        |--------------------------------------------------------------------------
                        | GET QC TOTAL
                        |--------------------------------------------------------------------------
                        */

                        $inspectTotal =
                            $inspectTotals[
                                $inspectTotalKey
                            ] ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | PUSH SPK
                        |--------------------------------------------------------------------------
                        */

                        $itemData['spks'][] = [

                            'id' =>
                                $spk->id,

                            'supplier' =>
                                $spkData['sup']
                                ?? '-',

                            'kategori' =>
                                $spkData['kategori']
                                ?? '-',

                            'jenis_asli' =>
                                $spkData['kategori']
                                ?? '-',

                            'no_spk' =>
                                $spkData['no_spk']
                                ?? '-',

                            'status' =>
                                $spk->status
                                ?? '-',

                            'harga' =>
                                $spkItem['harga']
                                ?? 0,

                            'qty' =>
                                $spkItem['qty']
                                ?? 0,

                            'detail_po_id' =>
                                $detailPo->id,

                            'inspect_schedule_id' =>
                                $inspectTotal
                                ? true
                                : false,

                            'passed' =>
                                $inspectTotal
                                    ->total_passed
                                ?? 0,

                            'rejected' =>
                                $inspectTotal
                                    ->total_rejected
                                ?? 0,
                        ];
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | INVENTORY
                |--------------------------------------------------------------------------
                */

                $inventories =
                    $allInventories[
                        $detailPo->id
                    ] ?? collect();


                /*
                |--------------------------------------------------------------------------
                | LOOP INVENTORY
                |--------------------------------------------------------------------------
                */

                foreach ($inventories as $inventory) {


                    /*
                    |--------------------------------------------------------------------------
                    | GET SPK INVENTORY
                    |--------------------------------------------------------------------------
                    */

                    $spkInv =
                        $allSpks[
                            $inventory->spk_id
                        ] ?? null;


                    if (!$spkInv) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SPK DATA
                    |--------------------------------------------------------------------------
                    */

                    $spkInvData =
                        $spkInv->data;


                    if (is_string($spkInvData)) {

                        $spkInvData =
                            json_decode(
                                $spkInvData,
                                true
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CATEGORY INVENTORY
                    |--------------------------------------------------------------------------
                    |
                    | INVENTORY tetap berdasarkan kategori SPK.
                    |
                    | Jangan menggunakan kategori inspection di sini.
                    |
                    */

                    $kategoriInv =
                        strtolower(
                            trim(
                                $spkInvData['kategori']
                                ?? ''
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | NORMALIZE CATEGORY
                    |--------------------------------------------------------------------------
                    */

                    $prefix = null;


                    if (
                        str_contains(
                            $kategoriInv,
                            'rangka'
                        )
                    ) {

                        $prefix = 'rangka';

                    } elseif (
                        str_contains(
                            $kategoriInv,
                            'anyam'
                        )
                    ) {

                        $prefix = 'anyam';

                    } elseif (
                        str_contains(
                            $kategoriInv,
                            'unfinish'
                        )
                    ) {

                        $prefix = 'unfinish';

                    } elseif (
                        str_contains(
                            $kategoriInv,
                            'final'
                        )
                    ) {

                        $prefix = 'final';

                    } elseif (
                        str_contains(
                            $kategoriInv,
                            'box'
                        )
                        ||
                        str_contains(
                            $kategoriInv,
                            'packaging'
                        )
                    ) {

                        $prefix = 'box';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UNKNOWN CATEGORY
                    |--------------------------------------------------------------------------
                    */

                    if (!$prefix) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | INVENTORY TYPE
                    |--------------------------------------------------------------------------
                    */

                    $type =
                        strtolower(
                            trim(
                                $inventory->type
                                ?? ''
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | QTY
                    |--------------------------------------------------------------------------
                    */

                    $qtyInventory =
                        (float) (
                            $inventory->qty
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | IN
                    |--------------------------------------------------------------------------
                    */

                    if ($type === 'in') {

                        $itemData[
                            $prefix . '_in'
                        ] +=
                            $qtyInventory;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OUT
                    |--------------------------------------------------------------------------
                    */ else {

                        $itemData[
                            $prefix . '_out'
                        ] +=
                            $qtyInventory;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | PUSH ITEM
                |--------------------------------------------------------------------------
                */

                $datas[
                    $poId
                ]['items'][] =
                    $itemData;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.management.index',
            [
                'datas' =>
                    $datas,

                'searchPo' =>
                    $searchPo,

                'selectedDate' =>
                    $selectedDate,

                'dates' =>
                    $dates,
            ]
        );
    }

    // pew
    private function getMonitoringCategory($jenis)
    {
        $jenis = strtoupper(trim($jenis));

        $rangka = [
            'RANGKA',
            'RANGKA BESI',
            'RANGKA KAYU',
            'RANGKA ROTAN',
            'RANGKA ALUMUNIUN',
            'RANGKA TRIPLEK',
            // 'PLAT BESI',
        ];

        $anyam = [
            'ANYAM',
            'ANYAM SINTETIS',
            'ANYAM KARAKTER',
        ];

        $unfinish = [
            'RANGKA + ANYAM',
            'ANYAM + DEKOR',
            'BASKET JOGJA',
            'BASKE JOGJA',
            'BASKET LOMBOKAN',
            'BASKET TASIK',
        ];

        if (in_array($jenis, $rangka)) {
            return 'rangka';
        }

        if (in_array($jenis, $anyam)) {
            return 'anyam';
        }

        if (in_array($jenis, $unfinish)) {
            return 'unfinish';
        }

        return strtolower($jenis);
    }

    public function inventor()
    {
        $processes = [
            'rangka' => 'Rangka',
            'anyam' => 'Anyam',
            'unfinish' => 'Unfinish',
            'accessories' => 'Accessories',
            'decor' => 'Decor',
            'ikat' => 'Ikat',
            'final' => 'Final',
            'packaging' => 'Packaging',
        ];
        $signatures = SignatureSpk::with([
            'madeBy',
            'checkedBy',
            'checkedBy2',
            'approvedBy',
        ])
            ->get()
            ->keyBy('spk_id');
        $spks = \App\Models\Spk::latest()
            ->get()
            ->map(function ($spk) use ($signatures) {
                $data = $spk->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                $signature = $signatures->get($spk->id);

                $deadlinePercent = 0;
                $deadlineColor = 'secondary';
                $deadlineText = 'No Deadline';

                $tglTerima = $this->parseDate(
                    $data['tgl_terima'] ?? null
                );

                $tglSelesai = $this->parseDate(
                    $data['tgl_selesai'] ?? null
                );

                if ($tglTerima && $tglSelesai) {

                    $today = now();

                    $totalHari = max(
                        $tglTerima->diffInDays($tglSelesai),
                        1
                    );

                    $hariBerjalan = max(
                        $tglTerima->diffInDays(
                            $today,
                            false
                        ),
                        0
                    );

                    $deadlinePercent = min(
                        round(
                            ($hariBerjalan / $totalHari) * 100
                        ),
                        100
                    );

                    $sisaHari = (int) $today->diffInDays(
                        $tglSelesai,
                        false
                    );

                    if ($sisaHari < 0) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Overdue ' .
                            abs($sisaHari) .
                            ' Hari';

                        $deadlinePercent = 100;

                    } elseif ($sisaHari <= 3) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Critical (' .
                            $sisaHari .
                            ' hari)';

                    } elseif ($sisaHari <= 7) {

                        $deadlineColor = 'warning';
                        $deadlineText =
                            'Warning (' .
                            $sisaHari .
                            ' hari)';

                    } elseif ($sisaHari <= 14) {

                        $deadlineColor = 'info';
                        $deadlineText =
                            'Normal (' .
                            $sisaHari .
                            ' hari)';

                    } else {

                        $deadlineColor = 'success';
                        $deadlineText =
                            'Safe (' .
                            $sisaHari .
                            ' hari)';
                    }
                }

                $items = collect($data['items'] ?? [])
                    ->map(function ($item) {
                        return [
                            'nama' => $item['nama'] ?? '-',
                            'kode' => $item['kode'] ?? '-',
                            'qty' => $item['qty'] ?? 0,
                            'satuan' => $item['satuan'] ?? 'pcs',

                            'l' => $item['l'] ?? '-',
                            'p' => $item['p'] ?? '-',
                            't' => $item['t'] ?? '-',
                            'material' => $item['material'] ?? '-',
                            'images' => $item['images'] ?? [],
                            'detail_po_id' => $item['detail_po_id'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                // payment blm selesai
                $totalSpk = collect($data['items'] ?? [])->sum(function ($item) {

                    $total = floatval($item['total'] ?? 0);

                    foreach ($item['custom_columns'] ?? [] as $col) {
                        $total += floatval($col['total'] ?? 0);
                    }

                    return $total;
                });

                $totalPayment = collect($data['payments'] ?? [])->sum(function ($pay) {
                    return floatval($pay['amount'] ?? 0);
                });

                $isFinished = $totalPayment >= $totalSpk;

                return [
                    'is_finished' => $isFinished,
                    'id' => $spk->id,
                    'no_spk' => $data['no_spk'] ?? '-',
                    'supplier' => $data['sup'] ?? '-',
                    'supplier_id' => $data['sup_id'] ?? null,
                    'kategori' => $data['kategori'] ?? '-',
                    'no_po' => $data['no_po'] ?? '-',
                    'status' => $spk->status ?? '-',
                    'tgl_terima' => $tglTerima
                        ? $tglTerima->format('d/m/y')
                        : '-',

                    'tgl_selesai' => $tglSelesai
                        ? $tglSelesai->format('d/m/y')
                        : '-',
                    'items' => $items,
                    'deadline_percent' => $deadlinePercent,
                    'deadline_color' => $deadlineColor,
                    'deadline_text' => $deadlineText,
                    // signature
    
                    'signature' => [
                        'made_at' => $signature?->made_at,
                        'checked_at' => $signature?->checked_at,
                        'checked_at_2' => $signature?->checked_at_2,
                        'approved_at' => $signature?->approved_at,

                        'made_by' => $signature?->madeBy?->name,
                        'checked_by' => $signature?->checkedBy?->name,
                        'checked_by_2' => $signature?->checkedBy2?->name,
                        'approved_by' => $signature?->approvedBy?->name,
                    ],

                ];

            })
            ->values()
            ->toArray();
        $spks = collect($spks)
            ->where('is_finished', false)
            ->values()
            ->toArray();

        // dd($spks);
        return view(
            'pages.management.inventor',
            compact(
                'spks',
                'processes'
            )
        );
    }

    // arsip
    public function inventorArsip()
    {
        $processes = [
            'rangka' => 'Rangka',
            'anyam' => 'Anyam',
            'unfinish' => 'Unfinish',
            'accessories' => 'Accessories',
            'decor' => 'Decor',
            'ikat' => 'Ikat',
            'final' => 'Final',
            'packaging' => 'Packaging',
        ];
        $signatures = SignatureSpk::with([
            'madeBy',
            'checkedBy',
            'checkedBy2',
            'approvedBy',
        ])
            ->get()
            ->keyBy('spk_id');
        $spks = \App\Models\Spk::latest()
            ->get()
            ->map(function ($spk) use ($signatures) {
                $data = $spk->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                $signature = $signatures->get($spk->id);

                $deadlinePercent = 0;
                $deadlineColor = 'secondary';
                $deadlineText = 'No Deadline';

                $tglTerima = $this->parseDate(
                    $data['tgl_terima'] ?? null
                );

                $tglSelesai = $this->parseDate(
                    $data['tgl_selesai'] ?? null
                );

                if ($tglTerima && $tglSelesai) {

                    $today = now();

                    $totalHari = max(
                        $tglTerima->diffInDays($tglSelesai),
                        1
                    );

                    $hariBerjalan = max(
                        $tglTerima->diffInDays(
                            $today,
                            false
                        ),
                        0
                    );

                    $deadlinePercent = min(
                        round(
                            ($hariBerjalan / $totalHari) * 100
                        ),
                        100
                    );

                    $sisaHari = (int) $today->diffInDays(
                        $tglSelesai,
                        false
                    );

                    if ($sisaHari < 0) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Overdue ' .
                            abs($sisaHari) .
                            ' Hari';

                        $deadlinePercent = 100;

                    } elseif ($sisaHari <= 3) {

                        $deadlineColor = 'danger';
                        $deadlineText =
                            'Critical (' .
                            $sisaHari .
                            ' hari)';

                    } elseif ($sisaHari <= 7) {

                        $deadlineColor = 'warning';
                        $deadlineText =
                            'Warning (' .
                            $sisaHari .
                            ' hari)';

                    } elseif ($sisaHari <= 14) {

                        $deadlineColor = 'info';
                        $deadlineText =
                            'Normal (' .
                            $sisaHari .
                            ' hari)';

                    } else {

                        $deadlineColor = 'success';
                        $deadlineText =
                            'Safe (' .
                            $sisaHari .
                            ' hari)';
                    }
                }

                $items = collect($data['items'] ?? [])
                    ->map(function ($item) {
                        return [
                            'nama' => $item['nama'] ?? '-',
                            'kode' => $item['kode'] ?? '-',
                            'qty' => $item['qty'] ?? 0,
                            'satuan' => $item['satuan'] ?? 'pcs',

                            'l' => $item['l'] ?? '-',
                            'p' => $item['p'] ?? '-',
                            't' => $item['t'] ?? '-',
                            'material' => $item['material'] ?? '-',
                            'images' => $item['images'] ?? [],
                            'detail_po_id' => $item['detail_po_id'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                // payment blm selesai
                $totalSpk = collect($data['items'] ?? [])->sum(function ($item) {

                    $total = floatval($item['total'] ?? 0);

                    foreach ($item['custom_columns'] ?? [] as $col) {
                        $total += floatval($col['total'] ?? 0);
                    }

                    return $total;
                });

                $totalPayment = collect($data['payments'] ?? [])->sum(function ($pay) {
                    return floatval($pay['amount'] ?? 0);
                });

                $isFinished = $totalPayment >= $totalSpk;

                return [
                    'is_finished' => $isFinished,
                    'id' => $spk->id,
                    'no_spk' => $data['no_spk'] ?? '-',
                    'supplier' => $data['sup'] ?? '-',
                    'supplier_id' => $data['sup_id'] ?? null,
                    'kategori' => $data['kategori'] ?? '-',
                    'no_po' => $data['no_po'] ?? '-',
                    'status' => $spk->status ?? '-',
                    'tgl_terima' => $tglTerima
                        ? $tglTerima->format('d/m/y')
                        : '-',

                    'tgl_selesai' => $tglSelesai
                        ? $tglSelesai->format('d/m/y')
                        : '-',
                    'items' => $items,
                    'deadline_percent' => $deadlinePercent,
                    'deadline_color' => $deadlineColor,
                    'deadline_text' => $deadlineText,
                    // signature
    
                    'signature' => [
                        'made_at' => $signature?->made_at,
                        'checked_at' => $signature?->checked_at,
                        'checked_at_2' => $signature?->checked_at_2,
                        'approved_at' => $signature?->approved_at,

                        'made_by' => $signature?->madeBy?->name,
                        'checked_by' => $signature?->checkedBy?->name,
                        'checked_by_2' => $signature?->checkedBy2?->name,
                        'approved_by' => $signature?->approvedBy?->name,
                    ],

                ];

            })
            ->values()
            ->toArray();
        $spks = collect($spks)
            ->where('is_finished', true)
            ->values()
            ->toArray();

        // dd($spks);
        return view(
            'pages.management.inventor_arsip',
            compact(
                'spks',
                'processes'
            )
        );
    }
    //  use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use App\Models\Spk;

    private function normalizeInvoice($value)
    {
        if (!$value) {
            return '';
        }

        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                trim($value)
            )
        );
    }


    /**
     * ============================================================
     * NORMALISASI SUPPLIER
     * ============================================================
     */
    private function normalizeSupplier($value)
    {
        $value = strtoupper(trim((string) $value));

        if (str_contains($value, 'TOMO')) {
            return 'TOMO';
        }

        if (str_contains($value, 'DARTO')) {
            return 'DARTO';
        }

        return null;
    }


    /**
     * ============================================================
     * HITUNG TOTAL DETAIL BAHAN
     * ============================================================
     */
    private function calculateDetailTotal($detailBahan)
    {
        $grandTotal = 0;

        if (!is_array($detailBahan)) {
            return 0;
        }

        foreach ($detailBahan as $item) {

            $total = $item['total'] ?? null;

            if ($total !== null && $total !== '') {

                if (is_string($total)) {

                    $total = str_replace(
                        ['Rp', 'rp', '.', ',', ' '],
                        ['', '', '', '', ''],
                        $total
                    );
                }

                $grandTotal += (float) $total;

                continue;
            }


            $qty = (float) (
                $item['qty'] ?? 0
            );

            $harga = (float) (
                $item['harga'] ?? 0
            );

            $grandTotal += $qty * $harga;
        }

        return $grandTotal;
    }


    /**
     * ============================================================
     * INDEX
     * ============================================================
     */
    public function monitoringFinishing(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. MONITORING INVOICE
        |--------------------------------------------------------------------------
        */

        $monitoringInvoices = MonitoringInvoice::query()
            ->orderBy('tanggal_invoice')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 2. INVOICE LAMA
        |--------------------------------------------------------------------------
        */

        $invLamas = InvLama::query()
            ->orderBy('tanggal_invoice')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. BUAT MAP INVOICE LAMA
        |--------------------------------------------------------------------------
        */

        $invLamaMap = [];

        foreach ($invLamas as $invLama) {

            $key = $this->normalizeInvoice(
                $invLama->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            /*
             * Kalau ada duplikat, gunakan record terakhir.
             */
            $invLamaMap[$key] = $invLama;
        }


        /*
        |--------------------------------------------------------------------------
        | 4. BUAT MAP MONITORING INVOICE
        |--------------------------------------------------------------------------
        */

        $monitoringMap = [];

        foreach ($monitoringInvoices as $invoice) {

            $key = $this->normalizeInvoice(
                $invoice->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            $monitoringMap[$key] = $invoice;
        }


        /*
        |--------------------------------------------------------------------------
        | 5. GABUNG MONITORING INVOICE + INVOICE LAMA
        |--------------------------------------------------------------------------
        |
        | Jika invoice ada di monitoring:
        |     gunakan monitoring.
        |
        | Jika hanya ada di inv_lama:
        |     tetap masukkan.
        |
        */

        $allInvoices = collect();

        foreach ($monitoringInvoices as $invoice) {

            $key = $this->normalizeInvoice(
                $invoice->nomor_invoice
            );

            $allInvoices->push([
                'key' => $key,
                'model' => $invoice,
                'source' => 'monitoring_invoice',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Tambahkan invoice yang hanya ada di INV LAMA
        |--------------------------------------------------------------------------
        */

        foreach ($invLamas as $invLama) {

            $key = $this->normalizeInvoice(
                $invLama->nomor_invoice
            );

            if ($key === '') {
                continue;
            }

            /*
             * Kalau sudah ada di MonitoringInvoice,
             * jangan dibuat dua kali.
             */
            if (isset($monitoringMap[$key])) {
                continue;
            }

            $allInvoices->push([
                'key' => $key,
                'model' => $invLama,
                'source' => 'inv_lama',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 6. SORT SEMUA INVOICE BERDASARKAN TANGGAL
        |--------------------------------------------------------------------------
        */

        $allInvoices = $allInvoices
            ->sortBy(function ($item) {

                return $item['model']->tanggal_invoice
                    ? Carbon::parse(
                        $item['model']->tanggal_invoice
                    )->timestamp
                    : PHP_INT_MAX;
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 7. AMBIL SPK LAMA
        |--------------------------------------------------------------------------
        |
        | SPK lama digunakan untuk PO lama.
        |
        */

        $spkLamas = SpkLama::query()
            ->orderBy('tanggal_potong')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 8. AMBIL SPK BARU
        |--------------------------------------------------------------------------
        */

        $spks = Spk::query()
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 9. LEDGER
        |--------------------------------------------------------------------------
        */

        $ledger = [];

        $groupNumber = 0;


        /*
        |--------------------------------------------------------------------------
        | RUNNING SALDO GLOBAL
        |--------------------------------------------------------------------------
        |
        | Saldo ini TIDAK di-reset ketika masuk invoice baru.
        |
        | Contoh:
        |
        | Invoice A       + 1.000.000
        | Pemotongan        - 600.000
        | Saldo               400.000
        |
        | Invoice B       + 2.000.000
        | Saldo             2.400.000
        |
        */

        $runningSaldo = 0;


        /*
        |--------------------------------------------------------------------------
        | 10. PROSES SETIAP INVOICE
        |--------------------------------------------------------------------------
        */

        foreach ($allInvoices as $invoiceItem) {

            $invoice = $invoiceItem['model'];

            $invoiceKey = $invoiceItem['key'];

            $invoiceSource = $invoiceItem['source'];


            /*
            |--------------------------------------------------------------------------
            | NOMOR INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceNumber = trim(
                $invoice->nomor_invoice ?? ''
            );

            if ($invoiceNumber === '') {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL BAHAN
            |--------------------------------------------------------------------------
            */

            $detailBahan = $invoice->detail_bahan ?? [];

            if (!is_array($detailBahan)) {
                $detailBahan = [];
            }


            /*
            |--------------------------------------------------------------------------
            | GRAND TOTAL
            |--------------------------------------------------------------------------
            */

            $invoiceTotal = $this->calculateDetailTotal(
                $detailBahan
            );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceDate = $invoice->tanggal_invoice;


            /*
            |--------------------------------------------------------------------------
            | BARIS INVOICE
            |--------------------------------------------------------------------------
            */

            $rows = [];

            $rows[] = [

                'id' => 'INV-' . $invoiceSource . '-' . $invoice->id,

                'source_id' => $invoice->id,

                'source' => $invoiceSource,

                'type' => 'invoice',

                'tanggal' => $invoiceDate,

                'description' => 'Invoice',

                'sub' => $invoiceNumber,

                'supplier' => '',

                'debet' => $invoiceTotal,

                'kredit' => 0,

                /*
                 * Untuk sementara saldo akan diisi ulang
                 * setelah semua pemotongan selesai diproses.
                 */
                'saldo' => 0,

                'invoice' => $invoiceNumber,

                'no_inv' => $invoiceNumber,

                'po' => null,

                'no_spk' => null,

                'note_tambahan' => null,

                'detail_bahan' => $detailBahan,

                'sort_date' => $invoiceDate,
            ];


            /*
            |--------------------------------------------------------------------------
            | 11. CARI PEMOTONGAN SPK LAMA
            |--------------------------------------------------------------------------
            |
            | Untuk SPK lama kita cari berdasarkan no_inv.
            |
            */

            foreach ($spkLamas as $spkLama) {

                $spkInvoiceKey = $this->normalizeInvoice(
                    $spkLama->no_inv
                );


                /*
                |--------------------------------------------------------------------------
                | Invoice harus sama
                |--------------------------------------------------------------------------
                */

                if (
                    $spkInvoiceKey === '' ||
                    $spkInvoiceKey !== $invoiceKey
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Supplier / sub
                |--------------------------------------------------------------------------
                */

                $supplierLabel = $spkLama->name_sub;


                /*
                |--------------------------------------------------------------------------
                | Hanya DARTO dan TOMO
                |--------------------------------------------------------------------------
                */

                $supplierNormalized = $this->normalizeSupplier(
                    $supplierLabel
                );


                /*
                |--------------------------------------------------------------------------
                | Kalau name_sub bukan TOMO / DARTO,
                | tetap boleh tampil kalau data lama Anda
                | memang menggunakan nama seperti PRODUKSI.
                |--------------------------------------------------------------------------
                */

                if (
                    $supplierNormalized
                ) {
                    $supplierLabel = $supplierNormalized;
                }


                /*
                |--------------------------------------------------------------------------
                | NOMINAL PEMOTONGAN
                |--------------------------------------------------------------------------
                */

                $amount = (float) (
                    $spkLama->pemotongan_bahan ?? 0
                );


                if ($amount <= 0) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | JANGAN KURANGI SALDO DI SINI
                |--------------------------------------------------------------------------
                |
                | Saldo akan dihitung ulang setelah semua
                | transaksi invoice selesai dikumpulkan dan di-sort.
                |
                */


                /*
                |--------------------------------------------------------------------------
                | TAMBAHKAN PEMOTONGAN
                |--------------------------------------------------------------------------
                */

                $rows[] = [

                    /*
                     * ID UNIK
                     */
                    'id' => 'LAMA-' . $spkLama->id,

                    /*
                     * ID ASLI SPK LAMA
                     */
                    'source_id' => $spkLama->id,

                    'spk_lama_id' => $spkLama->id,

                    'source' => 'spk_lama',

                    'type' => 'pemotongan',

                    'tanggal' => $spkLama->tanggal_potong,

                    'description' => 'Pemotongan bahan',

                    'sub' => $spkLama->no_spk
                        ?: $spkLama->po,

                    'supplier' => $supplierLabel,
                    'kategori' => strtolower(trim($supplierLabel)),
                    'debet' => 0,

                    'kredit' => $amount,

                    'saldo' => 0,

                    'invoice' => $invoiceNumber,

                    'no_inv' => $spkLama->no_inv,

                    'po' => $spkLama->po,

                    'no_spk' => $spkLama->no_spk,

                    'note_tambahan' => null,

                    'detail_bahan' => [],

                    'sort_date' => $spkLama->tanggal_potong,
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | 12. CARI SPK BARU
            |--------------------------------------------------------------------------
            */

            foreach ($spks as $spk) {

                $data = $spk->data;

                if (!is_array($data)) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | SUPPLIER
                |--------------------------------------------------------------------------
                */

                $supplier = $data['sup']
                    ?? $spk->supplier->name
                    ?? null;


                $supplierNormalized =
                    $this->normalizeSupplier($supplier);


                /*
                |--------------------------------------------------------------------------
                | HANYA TOMO / DARTO
                |--------------------------------------------------------------------------
                */

                if (!$supplierNormalized) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | PAYMENTS
                |--------------------------------------------------------------------------
                */

                $payments = $data['payments'] ?? [];

                if (!is_array($payments)) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | LOOP PAYMENT
                |--------------------------------------------------------------------------
                */

                foreach ($payments as $paymentIndex => $payment) {

                    if (!is_array($payment)) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HANYA PAYMENT BAHAN
                    |--------------------------------------------------------------------------
                    */

                    $note = strtolower(
                        trim(
                            $payment['note'] ?? ''
                        )
                    );


                    if ($note !== 'bahan') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NOTE TAMBAHAN
                    |--------------------------------------------------------------------------
                    */

                    $noteTambahan =
                        trim(
                            $payment['note_tambahan']
                            ?? ''
                        );


                    if ($noteTambahan === '') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL NOMOR INVOICE DARI NOTE
                    |--------------------------------------------------------------------------
                    |
                    | Contoh:
                    |
                    | INVOICE LEGENDA KAJ2290626 (29/6/2026)
                    |
                    | INVC LEGENDA KAJ180526 (23/6/2026)
                    |
                    */

                    preg_match(
                        '/([A-Z]{2,10}\d{4,})/i',
                        $noteTambahan,
                        $matches
                    );


                    if (empty($matches[1])) {
                        continue;
                    }


                    $paymentInvoice =
                        $this->normalizeInvoice(
                            $matches[1]
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT HARUS TERKAIT INVOICE INI
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $paymentInvoice === '' ||
                        $paymentInvoice !== $invoiceKey
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NOMINAL
                    |--------------------------------------------------------------------------
                    */

                    $amount = (float) (
                        $payment['amount'] ?? 0
                    );


                    if ($amount <= 0) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TANGGAL
                    |--------------------------------------------------------------------------
                    */

                    $paymentDate = null;

                    if (!empty($payment['date'])) {

                        try {

                            $paymentDate =
                                Carbon::parse(
                                    $payment['date']
                                );

                        } catch (\Throwable $e) {

                            $paymentDate = $invoiceDate;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | JANGAN KURANGI SALDO DI SINI
                    |--------------------------------------------------------------------------
                    |
                    | Saldo akan dihitung setelah semua row
                    | selesai di-sort.
                    |
                    */


                    /*
                    |--------------------------------------------------------------------------
                    | NOMOR SPK
                    |--------------------------------------------------------------------------
                    */

                    $noSpk = $data['no_spk']
                        ?? $spk->no_spk
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | PO
                    |--------------------------------------------------------------------------
                    */

                    $noPo = $data['no_po']
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | ROW PEMOTONGAN
                    |--------------------------------------------------------------------------
                    */

                    $rows[] = [

                        /*
                         * ID UNIK
                         */
                        'id' =>
                            'SPK-' .
                            $spk->id .
                            '-PAY-' .
                            $paymentIndex,

                        'source_id' => $spk->id,

                        'spk_id' => $spk->id,

                        'source' => 'spk',

                        'type' => 'pemotongan',

                        'tanggal' => $paymentDate,

                        'description' =>
                            'Pemotongan bahan',

                        'sub' => $noSpk,

                        'supplier' =>
                            $supplierNormalized,
                        'kategori' => strtolower(trim($supplierNormalized)),
                        'debet' => 0,

                        'kredit' => $amount,

                        'saldo' => 0,

                        'invoice' => $invoiceNumber,

                        'no_inv' => $paymentInvoice,

                        'po' => $noPo,

                        'no_spk' => $noSpk,

                        'note_tambahan' =>
                            $noteTambahan,

                        'detail_bahan' => [],

                        'sort_date' => $paymentDate,
                    ];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 13. SORT BARIS BERDASARKAN TANGGAL
            |--------------------------------------------------------------------------
            |
            | Invoice harus selalu menjadi baris pertama.
            |
            */

            $invoiceRow = $rows[0];

            $otherRows = array_slice(
                $rows,
                1
            );


            usort(
                $otherRows,
                function ($a, $b) {

                    $dateA = !empty($a['sort_date'])
                        ? Carbon::parse(
                            $a['sort_date']
                        )->timestamp
                        : PHP_INT_MAX;

                    $dateB = !empty($b['sort_date'])
                        ? Carbon::parse(
                            $b['sort_date']
                        )->timestamp
                        : PHP_INT_MAX;

                    return $dateA <=> $dateB;
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 14. RUNNING SALDO
            |--------------------------------------------------------------------------
            |
            | INVOICE = DEBET
            | PEMOTONGAN = KREDIT
            |
            | Rumus:
            |
            | saldo = saldo sebelumnya + debet - kredit
            |
            */

            /*
            |--------------------------------------------------------------------------
            | INVOICE MENAMBAH SALDO GLOBAL
            |--------------------------------------------------------------------------
            */

            $runningSaldo += (float) $invoiceTotal;


            /*
            |--------------------------------------------------------------------------
            | SALDO INVOICE
            |--------------------------------------------------------------------------
            */

            $invoiceRow['saldo'] = $runningSaldo;

            $finalRows = [
                $invoiceRow
            ];


            /*
            |--------------------------------------------------------------------------
            | PROSES PEMOTONGAN
            |--------------------------------------------------------------------------
            */

            foreach ($otherRows as $row) {

                $debet = (float) (
                    $row['debet'] ?? 0
                );

                $kredit = (float) (
                    $row['kredit'] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | RUNNING BALANCE
                |--------------------------------------------------------------------------
                */

                $runningSaldo += $debet;

                $runningSaldo -= $kredit;


                /*
                |--------------------------------------------------------------------------
                | SIMPAN SALDO ROW
                |--------------------------------------------------------------------------
                */

                $row['saldo'] = $runningSaldo;


                /*
                |--------------------------------------------------------------------------
                | TAMBAHKAN KE FINAL ROWS
                |--------------------------------------------------------------------------
                */

                $finalRows[] = $row;
            }


            /*
            |--------------------------------------------------------------------------
            | 15. GROUP
            |--------------------------------------------------------------------------
            */

            $groupNumber++;

            /*
|--------------------------------------------------------------------------
| KATEGORI INVOICE
|--------------------------------------------------------------------------
|
| Ambil kategori dari invoice jika field tersebut tersedia.
|
*/

            $kategoriInvoice = '';

            foreach ([
                'kategori',
                'kategori_invoice',
                'jenis_invoice',
                'tipe_invoice',
                'category',
                'type_invoice',
            ] as $field) {

                if (
                    isset($invoice->{$field}) &&
                    trim((string) $invoice->{$field}) !== ''
                ) {
                    $kategoriInvoice = trim(
                        (string) $invoice->{$field}
                    );

                    break;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | GROUP
            |--------------------------------------------------------------------------
            */

            $ledger[] = [

                'group' => $groupNumber,

                'invoice' => $invoiceNumber,

                'tanggal' => $invoiceDate,

                'source' => $invoiceSource,

                'kategori' => $kategoriInvoice,

                'rows' => $finalRows,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | 16. KIRIM KE VIEW
        |--------------------------------------------------------------------------
        */
        // dd([
        //     'allInvoices' => $allInvoices->count(),

        //     'ledger_group' => count($ledger),

        //     'ledger_invoices' => collect($ledger)
        //         ->map(function ($group) {
        //             return [
        //                 'group' => $group['group'],
        //                 'invoice' => $group['invoice'],
        //                 'source' => $group['source'],
        //                 'rows' => count($group['rows']),
        //             ];
        //         })
        //         ->values(),
        // ]);
        return view(
            'pages.finishing.monitoring',
            compact(
                'ledger'
            )
        );
    }



    public function inventorDetail($id)
    {
        $spk = Spk::findOrFail($id);

        $data = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;

        $itemMap = collect(
            $data['items'] ?? []
        )->keyBy('detail_po_id');

        /*

    |--------------------------------------------------------------------------
    | ITEMS + CUSTOM COLUMN TOTAL
    |--------------------------------------------------------------------------
    */
        // inspection result
        $detailPoIds = collect(
            $data['items'] ?? []
        )->pluck('detail_po_id')->filter();
        $kategoriId = Kategori::where(
            'kategori',
            $data['kategori']
        )->value('id');
        $inspectSummary = InspectSchedule::where(
            'spk_id',
            $spk->id
        )
            ->selectRaw('
            detail_po_id,
            SUM(passed) as passed,
            SUM(rejected) as rejected
        ')
            ->groupBy('detail_po_id')
            ->get()
            ->keyBy('detail_po_id');
        $items = collect(
            $data['items'] ?? []
        )->map(function ($item) use ($inspectSummary) {

            $inspect = $inspectSummary->get(
                $item['detail_po_id'] ?? null
            );

            $item['passed'] = (int) (
                $inspect->passed ?? 0
            );

            $item['rejected'] = (int) (
                $inspect->rejected ?? 0
            );

            return $item;

        });

        $items = collect(
            $data['items'] ?? []
        )->map(function ($item) use ($inspectSummary) {

            $inspect = $inspectSummary->get(
                $item['detail_po_id'] ?? null
            );

            $item['passed'] = (int) (
                $inspect->passed ?? 0
            );

            $item['rejected'] = (int) (
                $inspect->rejected ?? 0
            );

            return $item;

        });

        /*
            |--------------------------------------------------------------------------
            | GRAND TOTAL SPK
            |--------------------------------------------------------------------------
            */
        $grandTotal = $items->sum(function ($item) {

            $total = (float) ($item['total'] ?? 0);

            $customTotal = collect(
                $item['custom_columns'] ?? []
            )->sum(function ($custom) {

                return (float) (
                    $custom['total'] ?? 0
                );

            });

            return $total + $customTotal;
        });

        /*
    |--------------------------------------------------------------------------
    | SUPPLIER
    |--------------------------------------------------------------------------
    */
        $supplier = Supplier::where(
            'name',
            $data['sup'] ?? ''
        )->first();

        /*
    |--------------------------------------------------------------------------
    | TIMELINE
    |--------------------------------------------------------------------------
    */
        $timelines = ProductionTimeline::where(
            'spk_id',
            $spk->id
        )
            ->orderBy('id')
            ->get()
            ->map(function ($row) use ($itemMap) {

                return [
                    'id' => $row->id,

                    'detail_po_id' => $row->detail_po_id,

                    'item_name' => $itemMap[$row->detail_po_id]['nama'] ?? '-',

                    'item_code' => $itemMap[$row->detail_po_id]['kode'] ?? '-',

                    'qty' => $row->qty,

                    'type' => $row->type,

                    'process' => $row->process,

                    'next_process' => $row->next_process,

                    'remark' => $row->remark ?? '-',

                    'date' => Carbon::parse(
                        $row->date
                    )->format('Y-m-d'),

                    'time' => Carbon::parse(
                        $row->date
                    )->format('H:i'),
                ];
            });

        /*
    |--------------------------------------------------------------------------
    | BAHAN BAKU
    |--------------------------------------------------------------------------
    */
        $bahanBaku = TransaksiStok::with('stok')
            ->where('spk_id', $spk->id)
            ->orderBy('tanggal')
            ->get()
            ->map(function ($row) {

                return [
                    'id' => $row->id,

                    'tanggal' => $row->tanggal,

                    'tipe' => $row->tipe,

                    'qty' => $row->qty,

                    'po' => $row->po,

                    'keterangan' => $row->keterangan,

                    'stok_id' => $row->stok_id,

                    'kode_barang' => $row->stok->kode_barang ?? '-',

                    'nama_barang' => $row->stok->nama_barang ?? '-',

                    'satuan' => $row->stok->satuan ?? '-',
                    'harga_vivi' => $row->harga_vivi ?? null,
                    'harga' => $row->stok->harga ?? 0,
                    'sst' => $row->stok->qty ?? 0,
                    'stok_akhir' => $row->stok->stok_akhir ?? 0,
                ];
            });
        //             dd(
        //     collect($data['items'])->pluck('detail_po_id')
        // );
        // dd($items);
        // dd(
        //     $detailPoIds->toArray(),
        //     InspectSchedule::where('spk_id', $spk->id)->get()->toArray()
        // );
        //   $financeApproved = false;

        $financeApproved = false;

        $paymentRequestIds = PaymentRequest::where(
            'spk_id',
            $spk->id
        )->pluck('id')->toArray();

        if (!empty($paymentRequestIds)) {

            $draft = PaymentRequestSaved::all()
                ->first(function ($row) use ($paymentRequestIds) {

                    return count(
                        array_intersect(
                            $row->payment_request_ids ?? [],
                            $paymentRequestIds
                        )
                    ) > 0;

                });

            if ($draft) {

                $financeApproved =
                    PaymentRequestApproval::where(
                        'payment_request_saved_id',
                        $draft->id
                    )
                        ->where('status', 'Approved')
                        ->where(function ($q) {

                            $q->where('user_id', 174)
                                ->orWhere('role', 'Finance');

                        })
                        ->exists();
            }

        }

        return response()->json([
            'can_edit_harga' => auth()->id() == 171,

            'grand_total' => $grandTotal,

            'bahan_baku' => $bahanBaku,

            'kategori' => $data['kategori'] ?? '-',

            'status' => $spk->status ?? '-',

            'spk' => $spk,

            'items' => $items,

            'spk_no' => $data['no_spk'] ?? '-',

            'payments' => $data['payments'] ?? [],

            'supplier' => [
                'id' => $supplier->id ?? null,

                'name' => $supplier->name ?? '-',
            ],

            'timelines' => $timelines,

            'payments' => collect(
                $data['payments'] ?? []
            )->map(function ($payment) {
                $amount = (float) (
                    $payment['amount'] ?? 0
                );

                $adjustment = (float) (
                    $payment['adjustment'] ?? 0
                );

                return [

                    'date' => $payment['date'] ?? null,

                    'note' => $payment['note'] ?? '-',
                    'finance_approved' => $payment['finance_approved'] ?? false,
                    'amount' => $amount,
                    'is_request' => $payment['is_request'] ?? null,
                    'payment_id' => $payment['payment_id'] ?? null,

                    'note_tambahan' => $payment['note_tambahan'] ?? null,

                    'adjustment' => $adjustment,

                    'payment_request_amount' => $adjustment > 0
                        ? $adjustment
                        : $amount,

                    'remaining_amount' => $adjustment > 0
                        ? ($amount - $adjustment)
                        : 0,

                    'adjustment_by' => $payment['adjustment_by'] ?? null,

                    'adjustment_at' => $payment['adjustment_at'] ?? null,
                    'finance_approved' => $payment['finance_approved'] ?? false,
                    // vivi
                ];

            })->values(),

        ]);
    }

    // vivi update
    public function updateHargaVivi(Request $request)
    {
        abort_unless(auth()->id() == 171, 403);

        $transaksi = TransaksiStok::findOrFail(
            $request->id
        );

        $transaksi->update([
            'harga_vivi' => $request->harga,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function inventorStore(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */
        $request->validate([
            'spk_id' => 'required',
            'detail_po_id' => 'required|array',
            'qty' => 'required|array',
        ]);
        /*
    |--------------------------------------------------------------------------
    | GET SPK
    |--------------------------------------------------------------------------
    */
        $spk = Spk::findOrFail(
            $request->spk_id
        );
        $spkData = is_string($spk->data)
            ? json_decode($spk->data, true)
            : $spk->data;
        /*
    |--------------------------------------------------------------------------
    | GET PO ID
    |--------------------------------------------------------------------------
    */
        $poId =
            $spkData['po_id'] ?? $spk->po_id ?? null;
        /*
    |--------------------------------------------------------------------------
    | DELETE OLD
    |--------------------------------------------------------------------------
    */
        ProductionTimeline::where(
            'spk_id',
            $request->spk_id
        )->delete();
        /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */
        foreach ($request->detail_po_id as $i => $detailPoId) {
            /*
        |--------------------------------------------------------------------------
        | DATE TIME
        |--------------------------------------------------------------------------
        */
            $dateTime = now();
            if (
                !empty($request->date[$i]) &&
                !empty($request->time[$i])
            ) {
                $dateTime =
                    $request->date[$i]
                    . ' ' .
                    $request->time[$i];
            }
            /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */
            ProductionTimeline::create([
                'po_id' => $poId,
                'spk_id' => $request->spk_id,
                'detail_po_id' => $detailPoId,
                'qty' => $request->qty[$i] ?? 0,
                'sup_id' => $request->sup_id[$i] ?? null,
                'process' => $request->process[$i] ?? null,
                'next_process' => $request->next_process[$i] ?? null,
                'date' => $dateTime,
                'type' => $request->type[$i] ?? 'in',
                'remark' => $request->remark[$i] ?? null,
                'source_type' => 'inventor',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */
        return response()->json([
            'success' => true,
            'message' => 'Inventory berhasil disimpan',
        ]);
    }

    public function delete($id)
    {
        $timeline = ProductionTimeline::find($id);
        if (!$timeline) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        $timeline->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus',
        ]);
    }
    //     private function parseDate($date)
    // {
    //     if (empty($date) || $date == '-') {
    //         return null;
    //     }

    //     try {
    //         return \Carbon\Carbon::parse($date);
    //     } catch (\Exception $e) {

    //         try {
    //             return \Carbon\Carbon::createFromFormat(
    //                 'd/m/Y',
    //                 $date
    //             );
    //         } catch (\Exception $e) {

    //             try {
    //                 return \Carbon\Carbon::createFromFormat(
    //                     'd-M-Y',
    //                     $date
    //                 );
    //             } catch (\Exception $e) {

    //                 return null;
    //             }
    //         }
    //     }
    // }
    private function parseDate($date)
    {
        if (blank($date) || $date == '-') {
            return null;
        }

        $date = trim($date);

        // Samakan nama bulan Indonesia → Inggris
        $replace = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'Oktober' => 'October',
            'Desember' => 'December',
        ];

        $date = str_ireplace(array_keys($replace), array_values($replace), $date);

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'd-n-Y',
            'd-M-Y',
            'd-F-Y',
            'd M Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
            }
        }

        try {
            return \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function barangJadi(Request $request)
    {
        $query = ProductionTimeline::with([
            'po',
            'spk',
            'detailPo',
        ]);

        // Filter tanggal
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $timelines = $query
            ->orderByDesc('date')
            ->get();

        return view('pages.admin.laporan_admin', [
            'timelines' => $timelines,
            'from' => $request->from,
            'to' => $request->to,
        ]);
    }
    public function paymentSpk(Request $request)
    {
        $spk = Spk::all();

        $sort = $request->sort ?? 'po';

        $spk = $spk->sortBy(function ($spk) use ($sort) {

            $data = is_array($spk->data)
                ? $spk->data
                : json_decode($spk->data, true);

            switch ($sort) {
                case 'sub':
                    return $data['sup'] ?? '';

                case 'kategori':
                    return $data['kategori'] ?? '';

                case 'po':
                default:
                    return $data['no_po'] ?? '';
            }
        })->values();

        $spkIds = $spk->pluck('id');

        $qtyIn = ProductionTimeline::where('type', 'in')
            ->selectRaw('spk_id, detail_po_id, SUM(qty) as qty_in')
            ->groupBy('spk_id', 'detail_po_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->spk_id . '_' . $row->detail_po_id => $row->qty_in
                ];
            });

        return view(
            'pages.spk.monitoring-payment.index',
            compact('spk', 'qtyIn')
        );
    }
    // export 
    public function exportBarangJadi(Request $request)
    {
        // ==========================
        // Barang Jadi
        // ==========================
        $query = ProductionTimeline::with([
            'po',
            'spk',
            'detailPo',
        ]);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $timelines = $query
            ->orderByDesc('date')
            ->get();

        // ==========================
        // QC PASS
        // ==========================
        $inspection = InspectSchedule::with([
            'kategori',
            'user',
            'spk',
            'detailPo',
            'po',
        ]);

        if ($request->filled('from')) {
            $inspection->whereDate('tanggal_inspect', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $inspection->whereDate('tanggal_inspect', '<=', $request->to);
        }

        $inspection = $inspection
            ->orderByDesc('tanggal_inspect')
            ->get();

        return Excel::download(
            new AdminReportExport(
                $timelines,
                $inspection,
                $request->from,
                $request->to
            ),
            'LAPORAN_BARANG_JADI_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function barangJadiRekap(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Barang Masuk
        |--------------------------------------------------------------------------
        */

        $barangMasuk = ProductionTimeline::with([
            'spk',
            'detailPo'
        ])
            ->selectRaw("
        spk_id,
        detail_po_id,
        SUM(qty) as qty_in
    ")
            ->where('type', 'in')

            ->whereBetween('date', [
                $request->from,
                $request->to
            ])
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | QC PASS
        |--------------------------------------------------------------------------
        */

        $qcPass = InspectSchedule::with([
            'spk',
            'detailPo'
        ])

            ->selectRaw("
        spk_id,
        detail_po_id,
        SUM(passed) as qty_pass
    ")
            ->whereBetween('tanggal_inspect', [
                $request->from,
                $request->to
            ])
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Merge
        |--------------------------------------------------------------------------
        */

        $rows = [];

        foreach ($barangMasuk as $item) {

            $qc = $qcPass->first(function ($q) use ($item) {
                return $q->spk_id == $item->spk_id
                    && $q->detail_po_id == $item->detail_po_id;
            });

            $detail = optional($item->detailPo)->detail;

            if (is_string($detail)) {
                $detail = json_decode($detail, true);
            }

            $rows[] = [
                'description' => $detail['description'] ?? '-',

                'spk' => optional($item->spk)->data['no_spk'] ?? '-',
                'sub' => optional($item->spk)->data['sup'] ?? '-',
                'kategori' => optional($item->spk)->data['kategori'] ?? '-',
                'qty_in' => $item->qty_in,
                'qty_pass' => $qc->qty_pass ?? 0,
                'selisih' => $item->qty_in - ($qc->qty_pass ?? 0),
            ];
        }

        return response()->json($rows);
    }
 public function test()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. PERIODE
        |--------------------------------------------------------------------------
        |
        | Hari ini sampai 7 hari ke belakang.
        |
        */

        $now = now();

        $startDate = $now->copy()
            ->subDays(7)
            ->startOfDay();

        $endDate = $now->copy()
            ->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | 2. PRODUCTION TIMELINE
        |--------------------------------------------------------------------------
        |
        | ProductionTimeline menjadi sumber utama.
        |
        | Hanya:
        | - type = in
        | - tanggal 7 hari terakhir
        |
        */

        $timelines = ProductionTimeline::query()
            ->whereRaw(
                'LOWER(TRIM(type)) = ?',
                ['in']
            )
            ->whereBetween(
                DB::raw('DATE(date)'),
                [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d'),
                ]
            )
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. JIKA TIDAK ADA DATA
        |--------------------------------------------------------------------------
        */

        if ($timelines->isEmpty()) {
            return view('pages.spk.test', [
                'rows' => collect(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. AMBIL SPK
        |--------------------------------------------------------------------------
        */

        $spkIds = $timelines
            ->pluck('spk_id')
            ->filter()
            ->unique()
            ->values();

        $spks = Spk::query()
            ->with('po')
            ->whereIn('id', $spkIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | 5. DETAIL PO
        |--------------------------------------------------------------------------
        */

        $detailPoIds = $timelines
            ->pluck('detail_po_id')
            ->filter()
            ->unique()
            ->values();

        $detailPos = DetailPo::query()
            ->whereIn('id', $detailPoIds)
            ->get()
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | 6. ALL IN
        |--------------------------------------------------------------------------
        |
        | Total seluruh pemasukan item di ProductionTimeline
        | sepanjang waktu.
        |
        | TIDAK dibatasi periode 7 hari.
        |
        | Kunci:
        | spk_id + detail_po_id
        |
        */

        $allIn = ProductionTimeline::query()
            ->whereRaw(
                'LOWER(TRIM(type)) = ?',
                ['in']
            )
            ->whereIn('spk_id', $spkIds)
            ->whereIn('detail_po_id', $detailPoIds)
            ->select(
                'spk_id',
                'detail_po_id',
                DB::raw(
                    'SUM(COALESCE(qty, 0)) AS total_in'
                )
            )
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->spk_id
                    . '-'
                    . $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | 7. INSPECTION SCHEDULE
        |--------------------------------------------------------------------------
        |
        | PASS dan REJECT dicocokkan dengan:
        | spk_id + detail_po_id
        |
        */

        $inspections = InspectSchedule::query()
            ->whereIn('spk_id', $spkIds)
            ->whereIn('detail_po_id', $detailPoIds)
            ->select(
                'spk_id',
                'detail_po_id',
                DB::raw(
                    'SUM(COALESCE(passed, 0)) AS total_passed'
                ),
                DB::raw(
                    'SUM(COALESCE(rejected, 0)) AS total_rejected'
                )
            )
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->spk_id
                    . '-' .
                    $item->detail_po_id;
            });


        /*
        |--------------------------------------------------------------------------
        | 8. BENTUK ROW
        |--------------------------------------------------------------------------
        */

        $rows = collect();

        foreach ($timelines as $timeline) {

            $spk = $spks->get($timeline->spk_id);

            if (!$spk) {
                continue;
            }

            $detail = $detailPos->get($timeline->detail_po_id);

            if (!$detail) {
                continue;
            }

            $detailData = $detail->detail;

            if (!is_array($detailData)) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL ITEM
            |--------------------------------------------------------------------------
            */

            $articleNr = trim(
                (string) (
                    $detailData['article_nr_']
                    ?? ''
                )
            );

            if ($articleNr === '') {
                continue;
            }

            $description = trim(
                (string) (
                    $detailData['description']
                    ?? '-'
                )
            );

            $sub = trim(
                (string) (
                    $detailData['sub_category']
                    ?? '-'
                )
            );

            $qty = (float) (
                $detailData['qty']
                ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | DATA SPK
            |--------------------------------------------------------------------------
            */

            $spkData = $spk->data ?? [];

            if (!is_array($spkData)) {
                $spkData = [];
            }

            $noSpk = trim(
                (string) (
                    $spkData['no_spk']
                    ?? '-'
                )
            );

            $supplier = trim(
                (string) (
                    $spkData['sup']
                    ?? '-'
                )
            );

            $kategori = trim(
                (string) (
                    $spkData['kategori']
                    ?? '-'
                )
            );


            /*
            |--------------------------------------------------------------------------
            | NO PFI
            |--------------------------------------------------------------------------
            */

            $noPfi = optional(
                $spk->po
            )->order_no ?? '-';


            /*
            |--------------------------------------------------------------------------
            | TANGGAL TIMELINE
            |--------------------------------------------------------------------------
            */

            try {
                $timelineDate = Carbon::parse(
                    $timeline->date
                );
            } catch (\Throwable $e) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | QTY IN
            |--------------------------------------------------------------------------
            */

            $inQty = (float) (
                $timeline->qty
                ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | INSPECTION
            |--------------------------------------------------------------------------
            */

            $inspectionKey =
                $timeline->spk_id
                . '-'
                . $timeline->detail_po_id;

            $inspection = $inspections->get(
                $inspectionKey
            );

            $passQty = $inspection
                ? (float) (
                    $inspection->total_passed
                    ?? 0
                )
                : 0;

            $rejectQty = $inspection
                ? (float) (
                    $inspection->total_rejected
                    ?? 0
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | ALL IN
            |--------------------------------------------------------------------------
            |
            | Total pemasukan item tersebut di seluruh
            | ProductionTimeline, tanpa batas tanggal.
            |
            */

            $allInKey =
                $timeline->spk_id
                . '-'
                . $timeline->detail_po_id;

            $allInRecord = $allIn->get(
                $allInKey
            );

            $allInQty = $allInRecord
                ? (float) (
                    $allInRecord->total_in
                    ?? 0
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | SALDO ITEM
            |--------------------------------------------------------------------------
            */

            $saldoItem = max(
                0,
                $qty - $passQty
            );


            /*
            |--------------------------------------------------------------------------
            | SALDO PAYMENT SPK
            |--------------------------------------------------------------------------
            |
            | Untuk setiap SPK:
            |
            | TOTAL SPK
            | - seluruh payment yang benar-benar mengurangi saldo
            |
            | Data lama bisa memiliki:
            | amount > 0
            | adjustment = 0
            | payment_request_amount = 0
            |
            | Jadi sumber nilai payment:
            |
            | adjustment jika > 0
            | ELSE amount
            |
            */

            $totalSpk = 0;

            $items = $spkData['items'] ?? [];

            if (is_array($items)) {
                foreach ($items as $spkItem) {
                    $totalSpk += (float) (
                        $spkItem['total']
                        ?? 0
                    );
                }
            }


            $totalPayment = 0;

            $payments = $spkData['payments'] ?? [];

            if (is_array($payments)) {

                foreach ($payments as $payment) {

                    $amount = (float) (
                        $payment['amount']
                        ?? 0
                    );

                    $adjustment = (float) (
                        $payment['adjustment']
                        ?? 0
                    );

                    $paymentValue =
                        $adjustment > 0
                            ? $adjustment
                            : $amount;

                    if ($paymentValue <= 0) {
                        continue;
                    }

                    $note = strtolower(
                        trim(
                            (string) (
                                $payment['note']
                                ?? ''
                            )
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RETURN BAHAN
                    |--------------------------------------------------------------------------
                    |
                    | Return bahan mengembalikan saldo.
                    |
                    */

                    if ($note === 'return_bahan') {
                        $totalPayment -= $paymentValue;
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPAH
                    |--------------------------------------------------------------------------
                    |
                    | Payment upah tidak mengurangi saldo SPK.
                    |
                    */

                    if (
                        $note === 'upah'
                        ||
                        str_contains($note, 'upah')
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT NORMAL
                    |--------------------------------------------------------------------------
                    */

                    $totalPayment += $paymentValue;
                }
            }


            $saldoPayment = max(
                0,
                $totalSpk - $totalPayment
            );


            /*
            |--------------------------------------------------------------------------
            | PUSH ROW
            |--------------------------------------------------------------------------
            */

            $rows->push([

                'tanggal' =>
                    $timelineDate,

                'article_nr' =>
                    $articleNr,

                'description' =>
                    $description,

                'no_pfi' =>
                    $noPfi,

                'no_spk' =>
                    $noSpk,

                'supplier' =>
                    $supplier,

                'kategori' =>
                    $kategori,

                'sub' =>
                    $sub,

                'qty' =>
                    $qty,

                'in' =>
                    $inQty,

                'all_in' =>
                    $allInQty,

                'pass' =>
                    $passQty,

                'rejected' =>
                    $rejectQty,

                'saldo' =>
                    $saldoItem,

                'saldo_payment' =>
                    $saldoPayment,

                'spk_id' =>
                    $timeline->spk_id,

                'detail_po_id' =>
                    $timeline->detail_po_id,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 9. GROUP
        |--------------------------------------------------------------------------
        |
        | Satu item dapat memiliki beberapa production timeline.
        |
        | Group:
        | article + spk + detail_po
        |
        */

        $rows = $rows
            ->groupBy(function ($row) {

                return implode('|', [
                    $row['article_nr'],
                    $row['spk_id'],
                    $row['detail_po_id'],
                ]);
            })
            ->map(function ($group) {

                $first = $group
                    ->sortBy('tanggal')
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | TOTAL IN
                |--------------------------------------------------------------------------
                */

                $first['in'] =
                    $group->sum('in');


                /*
                |--------------------------------------------------------------------------
                | ALL IN
                |--------------------------------------------------------------------------
                |
                | Jangan dijumlahkan lagi karena setiap row timeline
                | sudah membawa total lifetime.
                |
                */

                $first['all_in'] =
                    $group->max('all_in');


                /*
                |--------------------------------------------------------------------------
                | PASS
                |--------------------------------------------------------------------------
                */

                $first['pass'] =
                    $group->max('pass');


                /*
                |--------------------------------------------------------------------------
                | REJECT
                |--------------------------------------------------------------------------
                */

                $first['rejected'] =
                    $group->max('rejected');


                /*
                |--------------------------------------------------------------------------
                | SALDO ITEM
                |--------------------------------------------------------------------------
                */

                $first['saldo'] = max(
                    0,
                    $first['qty']
                    - $first['pass']
                );


                /*
                |--------------------------------------------------------------------------
                | SALDO PAYMENT
                |--------------------------------------------------------------------------
                |
                | Sama untuk seluruh item dalam SPK.
                |
                */

                $first['saldo_payment'] =
                    $group->sortByDesc('tanggal')
                        ->first()['saldo_payment']
                    ?? 0;


                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                $first['tanggal'] =
                    $group
                        ->pluck('tanggal')
                        ->sort()
                        ->first();

                return $first;
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 10. SORT
        |--------------------------------------------------------------------------
        */

        $rows = $rows
            ->sortBy([
                ['tanggal', 'asc'],
                ['article_nr', 'asc'],
            ])
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 11. RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('pages.spk.test', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }


}
