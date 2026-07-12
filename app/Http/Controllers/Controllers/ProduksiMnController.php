<?php
namespace App\Http\Controllers;

use App\Models\InspectSchedule;
use App\Models\Kategori;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\QcReport;
use App\Models\Spk;
use App\Models\Supplier;
use App\Models\TransaksiStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SignatureSpk;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\PaymentRequestApproval;


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
                'check_point_id' =>
                $report->check_point_id,
                'remark'         =>
                $remark,
                'created_at'     =>
                $report->created_at,
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
        $detailData  = [];
        $itemName    = '-';
        $articleCode = '-';
        $qty         = '-';
        $itemImage   = null;
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
            'w'  =>
            $detailData['w'] ?? '-',
            'd'  =>
            $detailData['d'] ?? '-',
            'h'  =>
            $detailData['h'] ?? '-',
            'sw' =>
            $detailData['sw'] ?? '-',
            'sd' =>
            $detailData['sd'] ?? '-',
            'sh' =>
            $detailData['sh'] ?? '-',
        ];
        /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */
        return view(
            'pages.management.qc_report',
            [
                'inspect'     => $inspect,
                'grouped'     => $grouped,
                'photos'      => $photos,
                'detailData'  => $detailData,
                'itemName'    => $itemName,
                'articleCode' => $articleCode,
                'qty'         => $qty,
                'itemImage'   => $itemImage,
                'pfi'         => $pfi,
            ]
        );
    }
    public function index(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */
        $searchPo =
        $request->search_po;
        $selectedDate =
        $request->tanggal;
        /*
    |--------------------------------------------------------------------------
    | CATEGORY MAP
    |--------------------------------------------------------------------------
    */
        $categories = [
            'rangka'    => 'rangka',
            'anyam'     => 'anyam',
            'unfinish'  => 'unfinish',
            'final'     => 'final',
            'decor'     => 'decor',
            /*
        |--------------------------------------------------------------------------
        | ALIAS
        |--------------------------------------------------------------------------
        */
            'packaging' => 'box',
            'box'       => 'box',
        ];
        /*
    |--------------------------------------------------------------------------
    | GET DATES
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
    | FILTER PO
    |--------------------------------------------------------------------------
    */
        if ($searchPo) {
            $poQuery->where(
                'order_no',
                'like',
                '%' . $searchPo . '%'
            );
        }
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
    | PRELOAD INSPECT
    |--------------------------------------------------------------------------
    */
        $inspectQuery = InspectSchedule::with('kategori');
        if ($selectedDate) {
            $inspectQuery->whereDate(
                'tanggal_inspect',
                $selectedDate
            );
        }
        $allInspects = $inspectQuery
            ->get()
            ->groupBy(function ($item) {
                return $item->po_id . '_' . $item->detail_po_id;
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
    | PRELOAD QC TOTAL
    |--------------------------------------------------------------------------
    */
        $inspectTotals = InspectSchedule::query()
            ->selectRaw('
            spk_id,
            detail_po_id,
            SUM(passed) as total_passed,
            SUM(rejected) as total_rejected
        ')
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return;
                $item->spk_id .
                '_' .
                $item->detail_po_id;
            });
        /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */
        $datas = [];
        foreach ($pos as $po) {
            $poId         = $po->id;
            $datas[$poId] = [
                'po_number' =>
                $po->order_no,
                'items'     =>
                [],
            ];
            /*
        |--------------------------------------------------------------------------
        | DETAIL ITEM
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
                $detail['qty'] ?? 0;
                $itemName =
                $detail['description'] ?? $detail['nama'] ?? $detail['item'] ?? '-';
                $image =
                $detail['photo'] ?? null;
                /*
            |--------------------------------------------------------------------------
            | DEFAULT ITEM
            |--------------------------------------------------------------------------
            */
                $itemData = [
                    'item_name'  =>
                    $itemName,
                    'item_image' =>
                    $image,
                    'qty'        =>
                    $qty,
                    'spks'       =>
                    [],
                ];
                /*
            |--------------------------------------------------------------------------
            | INIT CATEGORY STATUS
            |--------------------------------------------------------------------------
            */
                foreach ($categories as $category) {
                    $itemData[$category . '_pass']   = 0;
                    $itemData[$category . '_reject'] = 0;
                    $itemData[$category . '_in']     = 0;
                    $itemData[$category . '_out']    = 0;
                }
                /*
            |--------------------------------------------------------------------------
            | GET INSPECT
            |--------------------------------------------------------------------------
            */
                $inspectKey =
                $poId . '_' . $detailPo->id;
                $inspects =
                $allInspects[$inspectKey] ?? collect();
                /*
            |--------------------------------------------------------------------------
            | LOOP INSPECT
            |--------------------------------------------------------------------------
            */
                foreach ($inspects as $inspect) {
                    $kategoriName = strtolower(
                        optional(
                            $inspect->kategori
                        )->kategori ?? ''
                    );
                    /*
                |--------------------------------------------------------------------------
                | CATEGORY MAPPING
                |--------------------------------------------------------------------------
                */
                    $prefix =
                    $categories[$kategoriName] ?? null;
                    if (! $prefix) {
                        continue;
                    }
                    $itemData[$prefix . '_pass']
                    += $inspect->passed;
                    $itemData[$prefix . '_reject']
                    += $inspect->rejected;
                }
                /*
            |--------------------------------------------------------------------------
            | GET SPK
            |--------------------------------------------------------------------------
            */
                foreach ($po->spks as $spk) {
                    $spkData =
                    $spk->data;
                    if (is_string($spkData)) {
                        $spkData = json_decode(
                            $spkData,
                            true
                        );
                    }
                    $spkItems =
                    $spkData['items'] ?? [];
                    foreach ($spkItems as $spkItem) {
                        if (
                            ($spkItem['detail_po_id'] ?? null)
                            != $detailPo->id
                        ) {
                            continue;
                        }
                        /*
                    |--------------------------------------------------------------------------
                    | QC TOTAL
                    |--------------------------------------------------------------------------
                    */
                        $inspectTotalKey =
                        $spk->id .
                        '_' .
                        $detailPo->id;
                        $inspectTotal =
                        $inspectTotals[$inspectTotalKey] ?? null;
                        /*
                    |--------------------------------------------------------------------------
                    | PUSH SPK
                    |--------------------------------------------------------------------------
                    */
                        $itemData['spks'][] = [
                            'id'       =>
                            $spk->id,
                            'supplier' =>
                            $spkData['sup'] ?? '-',
                            'kategori' =>
                            $spkData['kategori'] ?? '-',
                            'no_spk'   =>
                            $spkData['no_spk'] ?? '-',
                            'status'   =>
                            $spk->status ?? '-',
                            'harga'    =>
                            $spkItem['harga'] ?? 0,
                            'qty'      =>
                            $spkItem['qty'] ?? 0,
                            'passed'   =>
                            $inspectTotal->total_passed ?? 0,
                            'rejected' =>
                            $inspectTotal->total_rejected ?? 0,
                        ];
                    }
                }
                /*
            |--------------------------------------------------------------------------
            | INVENTORY
            |--------------------------------------------------------------------------
            */
                $inventories =
                $allInventories[$detailPo->id] ?? collect();
                foreach ($inventories as $inventory) {
                    /*
                |--------------------------------------------------------------------------
                | SPK
                |--------------------------------------------------------------------------
                */
                    $spkInv =
                    $allSpks[$inventory->spk_id] ?? null;
                    if (! $spkInv) {
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
                        $spkInvData = json_decode(
                            $spkInvData,
                            true
                        );
                    }
                    /*
                |--------------------------------------------------------------------------
                | CATEGORY
                |--------------------------------------------------------------------------
                */
                    $kategoriInv = strtolower(
                        $spkInvData['kategori'] ?? ''
                    );
                    $prefix =
                    $categories[$kategoriInv] ?? null;
                    if (! $prefix) {
                        continue;
                    }
                    /*
                |--------------------------------------------------------------------------
                | TYPE
                |--------------------------------------------------------------------------
                */
                    $type = strtolower(
                        $inventory->type ?? ''
                    );
                    $qtyInventory =
                    $inventory->qty ?? 0;
                    /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                */
                    if ($type == 'in') {
                        $itemData[$prefix . '_in']
                        += $qtyInventory;
                    } else {
                        $itemData[$prefix . '_out']
                        += $qtyInventory;
                    }
                }
                /*
            |--------------------------------------------------------------------------
            | PUSH ITEM
            |--------------------------------------------------------------------------
            */
                $datas[$poId]['items'][] =
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
                'datas'        =>
                $datas,
                'searchPo'     =>
                $searchPo,
                'selectedDate' =>
                $selectedDate,
                'dates'        =>
                $dates,
            ]
        );
    }
    public function inventor()
    {
        $processes = [
            'rangka'      => 'Rangka',
            'anyam'       => 'Anyam',
            'unfinish'    => 'Unfinish',
            'accessories' => 'Accessories',
            'decor'       => 'Decor',
            'ikat'        => 'Ikat',
            'final'       => 'Final',
            'packaging'   => 'Packaging',
        ];
        $signatures = SignatureSpk::with([
            'madeBy',
            'checkedBy',
            'approvedBy'
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
        $deadlineColor   = 'secondary';
        $deadlineText    = 'No Deadline';

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
                            'nama'         =>
                            $item['nama'] ?? '-',
                            'kode'         =>
                            $item['kode'] ?? '-',
                            'qty'          =>
                            $item['qty'] ?? 0,
                            'l'            =>
                            $item['l'] ?? '-',
                            'p'            =>
                            $item['p'] ?? '-',
                            't'            =>
                            $item['t'] ?? '-',
                            'material'     =>
                            $item['material'] ?? '-',
                            'images'       =>
                            $item['images'] ?? [],
                            'detail_po_id' =>
                            $item['detail_po_id'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();
                return [
                    'id'          =>
                    $spk->id,
                    'no_spk'      =>
                    $data['no_spk'] ?? '-',
                    'supplier'    =>
                    $data['sup'] ?? '-',
                    'supplier_id' =>
                    $data['sup_id'] ?? null,
                    'kategori'    =>
                    $data['kategori'] ?? '-',
                    'no_po'       =>
                    $data['no_po'] ?? '-',
                    'status'      =>
                    $spk->status ?? '-',
                    'tgl_terima'  =>
                    $data['tgl_terima'] ?? '-',
                    'tgl_selesai' =>
                    $data['tgl_selesai'] ?? '-',
                    'items'       =>
                    $items,
                    'deadline_percent' => $deadlinePercent,
                    'deadline_color'   => $deadlineColor,
                    'deadline_text'    => $deadlineText,
                // signature

                      'signature' => [
                    'made_at'      => $signature?->made_at,
                    'checked_at'   => $signature?->checked_at,
                    'approved_at'  => $signature?->approved_at,

                    'made_by'      => $signature?->madeBy?->name,
                    'checked_by'   => $signature?->checkedBy?->name,
                    'approved_by'  => $signature?->approvedBy?->name,
                ],

                ];

            })
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
    // helper

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
        ->selectRaw("
            detail_po_id,
            SUM(passed) as passed,
            SUM(rejected) as rejected
        ")
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
        $grandTotal = $items->sum('total');

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
                    'id'           => $row->id,

                    'detail_po_id' => $row->detail_po_id,

                    'item_name'    =>
                    $itemMap[$row->detail_po_id]['nama'] ?? '-',

                    'item_code'    =>
                    $itemMap[$row->detail_po_id]['kode'] ?? '-',

                    'qty'          => $row->qty,

                    'type'         => $row->type,

                    'process'      => $row->process,

                    'next_process' => $row->next_process,

                    'remark'       => $row->remark ?? '-',

                    'date'         => \Carbon\Carbon::parse(
                        $row->date
                    )->format('Y-m-d'),

                    'time'         => \Carbon\Carbon::parse(
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
                    'id'          => $row->id,

                    'tanggal'     => $row->tanggal,

                    'tipe'        => $row->tipe,

                    'qty'         => $row->qty,

                    'po'          => $row->po,

                    'keterangan'  => $row->keterangan,

                    'stok_id'     => $row->stok_id,

                    'kode_barang' =>
                    $row->stok->kode_barang ?? '-',

                    'nama_barang' =>
                    $row->stok->nama_barang ?? '-',

                    'satuan'      =>
                    $row->stok->satuan ?? '-',
                    'harga_vivi' => $row->harga_vivi ?? null,
                    'harga'       =>
                    $row->stok->harga ?? 0,
'sst'  =>
                    $row->stok->qty ?? 0,
                    'stok_akhir'  =>
                    $row->stok->stok_akhir ?? 0,
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

            'bahan_baku'  => $bahanBaku,

            'kategori'    =>
            $data['kategori'] ?? '-',

            'status'      =>
            $spk->status ?? '-',

            'spk'         => $spk,

            'items'       => $items,

            'spk_no'      =>
            $data['no_spk'] ?? '-',

            'payments'    =>
            $data['payments'] ?? [],

            'supplier'    => [
                'id'   =>
                $supplier->id ?? null,

                'name' =>
                $supplier->name ?? '-',
            ],

            'timelines'   => $timelines,

    'payments' => collect(
        $data['payments'] ?? []
)->map(function ($payment) {
        $amount = (float)(
            $payment['amount'] ?? 0
        );

        $adjustment = (float)(
            $payment['adjustment'] ?? 0
        );

        return [

            'date' =>
                $payment['date'] ?? null,

            'note' =>
                $payment['note'] ?? '-',
  'finance_approved' =>
            $payment['finance_approved'] ?? false,
            'amount' =>
                $amount,
                    'is_request' =>
                $payment['is_request'] ?? null,
            'payment_id' =>
                $payment['payment_id'] ?? null,

            'note_tambahan' =>
                $payment['note_tambahan'] ?? null,

            'adjustment' =>
                $adjustment,

            'payment_request_amount' =>
                $adjustment > 0
                    ? $adjustment
                    : $amount,

            'remaining_amount' =>
                $adjustment > 0
                    ? ($amount - $adjustment)
                    : 0,

            'adjustment_by' =>
                $payment['adjustment_by'] ?? null,

            'adjustment_at' =>
                $payment['adjustment_at'] ?? null,
           'finance_approved' =>
            $payment['finance_approved'] ?? false,
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
            'harga_vivi' => $request->harga
        ]);

        return response()->json([
            'success' => true
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
            'spk_id'       => 'required',
            'detail_po_id' => 'required|array',
            'qty'          => 'required|array',
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
                ! empty($request->date[$i]) &&
                ! empty($request->time[$i])
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
                'po_id'        =>
                $poId,
                'spk_id'       =>
                $request->spk_id,
                'detail_po_id' =>
                $detailPoId,
                'qty'          =>
                $request->qty[$i] ?? 0,
                'sup_id'       =>
                $request->sup_id[$i] ?? null,
                'process'      =>
                $request->process[$i] ?? null,
                'next_process' =>
                $request->next_process[$i] ?? null,
                'date'         =>
                $dateTime,
                'type'         =>
                $request->type[$i] ?? 'in',
                'remark'       =>
                $request->remark[$i] ?? null,
                'source_type'  =>
                'inventor',
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
        if (! $timeline) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        $timeline->delete();
        return response()->json([
            'message' => 'Data berhasil dihapus',
        ]);
    }
   private function parseDate($date)
{
    if (empty($date)) {
        return null;
    }

    try {

        $date = trim($date);

        $bulan = [
            'JANUARI'   => 'JANUARY',
            'FEBRUARI'  => 'FEBRUARY',
            'MARET'     => 'MARCH',
            'APRIL'     => 'APRIL',
            'MEI'       => 'MAY',
            'JUNI'      => 'JUNE',
            'JULI'      => 'JULY',
            'AGUSTUS'   => 'AUGUST',
            'SEPTEMBER' => 'SEPTEMBER',
            'OKTOBER'   => 'OCTOBER',
            'NOVEMBER'  => 'NOVEMBER',
            'DESEMBER'  => 'DECEMBER',
        ];

        $date = strtoupper($date);

        $date = str_replace(
            array_keys($bulan),
            array_values($bulan),
            $date
        );

        $date = str_replace('/', '-', $date);

        return Carbon::parse($date);

    } catch (\Exception $e) {

        return null;

    }
}
}
