<?php
namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\CadModel;
use App\Models\Checkpoint;
use App\Models\DetailPo;
use App\Models\InspectSchedule;
use App\Models\Kategori;
use App\Models\Po;
use App\Models\QcReport;
use App\Models\ReportPhoto;
use App\Models\Spk;
use App\Models\TimelineQc;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Karyawan;
use Carbon\Carbon;

use App\Models\InspectScheduleTest;
use App\Models\QcReportTest;

use App\Models\ReportPhotoTest;


// aa
class QcController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('pages.qc.index');
    }
    public function marketing()
    {
        //
        return view('pages.marketing.index');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
    /**
     * Display the specified resource.
     */

    // public function convert(Request $request)
    // {
    //     $raw   = trim($request->excel_data);
    //     $lines = preg_split("/\r\n|\n|\r/", $raw);
    //     // =========================
    //     // HEADER
    //     // =========================
    //     $headerLine   = array_map('trim', explode("\t", $lines[0]));
    //     $headers      = [];
    //     $wdhCount     = 0;
    //     $headerRepeat = []; // untuk Remark Remark dll
    //     foreach ($headerLine as $col) {
    //         // ===== HANDLE W D H =====
    //         if (in_array($col, ['W', 'D', 'H'])) {
    //             $wdhCount++;
    //             if ($wdhCount <= 3) {
    //                 $headers[] = 'item_' . strtolower($col);
    //             } else {
    //                 $headers[] = 'packing_' . strtolower($col);
    //             }
    //             continue;
    //         }
    //         // ===== NORMAL HEADER =====
    //         $key = strtolower(str_replace([' ', '.', "\n"], '_', $col));
    //         // ===== DUPLICATE HEADER (Remark Remark, dll) =====
    //         if (isset($headerRepeat[$key])) {
    //             $headerRepeat[$key]++;
    //             $key .= '_' . $headerRepeat[$key];
    //         } else {
    //             $headerRepeat[$key] = 1;
    //             // suffix _1 hanya jika nanti ada duplikat
    //             // remark pertama tetap "remark"
    //         }
    //         $headers[] = $key;
    //     }
    //     // =========================
    //     // DATA
    //     // =========================
    //     $items = [];
    //     for ($i = 1; $i < count($lines); $i++) {
    //         $cols = array_map('trim', explode("\t", $lines[$i]));
    //         // skip baris bukan item
    //         if (! isset($cols[0]) || ! is_numeric($cols[0])) {
    //             continue;
    //         }
    //         $row = [];
    //         foreach ($headers as $idx => $key) {
    //             $row[$key] = $cols[$idx] ?? null;
    //         }
    //         $items[] = $row;
    //     }
    //     return response()->json([
    //         // 'headers' => $headers,
    //         'items' => $items,
    //     ]);
    // }
    public function convert(Request $request)
    {
        $raw = trim($request->excel_data);

        if ($raw === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data Excel kosong.'
            ], 422);
        }

        $lines = preg_split("/\r\n|\n|\r/", $raw);

        // =====================================================
        // HEADER
        // =====================================================

        $headerLine = array_map(
            'trim',
            explode("\t", $lines[0])
        );

        $headers = [];
        $wdhCount = 0;
        $headerRepeat = [];

        foreach ($headerLine as $col) {

            $originalCol = trim($col);

            // =================================================
            // NORMALIZE HEADER
            // =================================================

            $normalizedCol = strtoupper(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $originalCol
                )
            );

            // =================================================
            // DIMENSION / W-D-H
            // =================================================

            /*
            |--------------------------------------------------------------------------
            | CASE 1
            |--------------------------------------------------------------------------
            |
            | W | D | H
            |
            | menjadi:
            |
            | item_w | item_d | item_h
            |
            */

            if (
                in_array(
                    $normalizedCol,
                    ['W', 'D', 'H'],
                    true
                )
            ) {

                $wdhCount++;

                if ($wdhCount <= 3) {

                    $headers[] =
                        'item_' .
                        strtolower($normalizedCol);

                } else {

                    $headers[] =
                        'packing_' .
                        strtolower($normalizedCol);

                }

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CASE 2
            |--------------------------------------------------------------------------
            |
            | DIMENSION (CM)
            |
            | D
            | H
            |
            | menjadi:
            |
            | item_w
            | item_d
            | item_h
            |
            */

            if (
                in_array(
                    $normalizedCol,
                    [
                        'DIMENSION (CM)',
                        'DIMENTION (CM)',
                        'DIMENSION',
                        'DIMENTION'
                    ],
                    true
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | DIMENSION (CM) dianggap sebagai WIDTH
                |--------------------------------------------------------------------------
                */

                $wdhCount = 1;

                $headers[] = 'item_w';

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CASE 3
            |--------------------------------------------------------------------------
            |
            | ITEM W
            | ITEM D
            | ITEM H
            |
            */

            if (
                in_array(
                    $normalizedCol,
                    [
                        'ITEM W',
                        'ITEM_W'
                    ],
                    true
                )
            ) {

                $headers[] = 'item_w';

                continue;
            }

            if (
                in_array(
                    $normalizedCol,
                    [
                        'ITEM D',
                        'ITEM_D'
                    ],
                    true
                )
            ) {

                $headers[] = 'item_d';

                continue;
            }

            if (
                in_array(
                    $normalizedCol,
                    [
                        'ITEM H',
                        'ITEM_H'
                    ],
                    true
                )
            ) {

                $headers[] = 'item_h';

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CASE 4
            |--------------------------------------------------------------------------
            |
            | PACK W / PACK D / PACK H
            |
            */

            if (
                in_array(
                    $normalizedCol,
                    [
                        'PACK W',
                        'PACK_W',
                        'PACKING W',
                        'PACKING_W'
                    ],
                    true
                )
            ) {

                $headers[] = 'packing_w';

                continue;
            }

            if (
                in_array(
                    $normalizedCol,
                    [
                        'PACK D',
                        'PACK_D',
                        'PACKING D',
                        'PACKING_D'
                    ],
                    true
                )
            ) {

                $headers[] = 'packing_d';

                continue;
            }

            if (
                in_array(
                    $normalizedCol,
                    [
                        'PACK H',
                        'PACK_H',
                        'PACKING H',
                        'PACKING_H'
                    ],
                    true
                )
            ) {

                $headers[] = 'packing_h';

                continue;
            }


            // =================================================
            // NORMAL HEADER
            // =================================================

            $key = strtolower(
                str_replace(
                    [
                        ' ',
                        '.',
                        "\n"
                    ],
                    '_',
                    $originalCol
                )
            );


            // =================================================
            // DUPLICATE HEADER
            // =================================================

            if (
                isset(
                $headerRepeat[$key]
            )
            ) {

                $headerRepeat[$key]++;

                $key .= '_' .
                    $headerRepeat[$key];

            } else {

                $headerRepeat[$key] = 1;

            }

            $headers[] = $key;
        }


        // =====================================================
        // DATA
        // =====================================================

        $items = [];

        for (
            $i = 1;
            $i < count($lines);
            $i++
        ) {

            $cols = array_map(
                'trim',
                explode("\t", $lines[$i])
            );


            // =================================================
            // SKIP BARIS BUKAN ITEM
            // =================================================

            if (
                !isset($cols[0]) ||
                !is_numeric($cols[0])
            ) {

                continue;
            }


            $row = [];


            // =================================================
            // MAP DATA KE HEADER
            // =================================================

            foreach (
                $headers as $idx => $key
            ) {

                $row[$key] =
                    $cols[$idx] ?? '';
            }


            // =================================================
            // OPTIONAL: NORMALIZE LEGACY DATA
            // =================================================

            /*
            |--------------------------------------------------------------------------
            | Kalau somehow masih ada data:
            |
            | dimension_(cm)
            |
            | kita pindahkan ke item_w
            |--------------------------------------------------------------------------
            */

            if (
                (
                    !isset($row['item_w']) ||
                    $row['item_w'] === ''
                ) &&
                isset($row['dimension_(cm)'])
            ) {

                $row['item_w'] =
                    $row['dimension_(cm)'];
            }


            /*
            |--------------------------------------------------------------------------
            | Legacy D / H
            |--------------------------------------------------------------------------
            */

            if (
                (
                    !isset($row['item_d']) ||
                    $row['item_d'] === ''
                ) &&
                isset($row['d'])
            ) {

                $row['item_d'] =
                    $row['d'];
            }


            if (
                (
                    !isset($row['item_h']) ||
                    $row['item_h'] === ''
                ) &&
                isset($row['h'])
            ) {

                $row['item_h'] =
                    $row['h'];
            }


            // =================================================
            // REMOVE LEGACY FIELD
            // =================================================

            unset(
                $row['dimension_(cm)'],
                $row['dimention_(cm)']
            );


            // =================================================
            // ADD ITEM
            // =================================================

            $items[] = $row;
        }


        // =====================================================
        // RESPONSE
        // =====================================================

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'items' => $items,
        ]);
    }
    public function releaseOrder()
    {
        return view('pages.marketing.release-order');
    }
    public function save(Request $request)
    {
        $buyer = $request->input('order_info');
        $items = $request->input('parsed_excel_json.items', []);
        DB::beginTransaction();
        try {
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            // 1. PO
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            $po = Po::firstOrCreate(
                ['order_no' => $buyer['Order_No.'] ?? '-'],
                [
                    'company_name' => $buyer['Company_Name'] ?? '-',
                    'country' => $buyer['Country'] ?? '-',
                    'shipment_date' => $buyer['Shipment_Date'] ?? '-',
                    'packing' => $buyer['Packing'] ?? '-',
                    'contact_person' => $buyer['Contact_Person'] ?? '-',
                ]
            );
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            // 2. DETAIL ( ROW PER ITEM )
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            foreach ($items as $item) {
                DetailPo::updateOrCreate(
                    [
                        'po_id' => $po->id,
                        'detail->article_nr_' => $item['article_nr_'] ?? null,
                    ],
                    [
                        'detail' => $item,
                    ]
                );
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'po_id' => $po->id,
                'total_detail_row' => count($items),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function poList()
    {
        $pos = Po::latest()->get();
        // dd( $pos );
        return response()->json($pos);
    }
    public function ajaxPoList(Request $request)
    {
        $q = $request->q;
        $type = $request->type;
        $sort = $request->sort ?? 'desc'; // default terbaru

        $query = Po::with('details')

            // =========================
            // SEARCH
            // =========================
            ->when($q, function ($query) use ($q) {

                $query->where(function ($sub) use ($q) {

                    $sub->where('order_no', 'like', "%{$q}%")
                        ->orWhere('company_name', 'like', "%{$q}%");

                });

            })

            // =========================
            // FILTER TYPE
            // =========================
            ->when($type, function ($query) use ($type) {

                if ($type === 'NWS') {

                    $query->where('order_no', 'like', 'NWS%');

                }

                if ($type === 'NW') {

                    $query->where('order_no', 'like', 'NW%')
                        ->where('order_no', 'not like', 'NWS%');

                }

            });

        // =========================
        // SORT ORDER NO
        // =========================
        if ($sort == 'asc') {

            $query->orderBy('order_no', 'asc');

        } else {

            $query->orderBy('order_no', 'desc');

        }

        $pos = $query->get();

        return response()->json($pos);
    }
    public function ajaxPo(Request $request)
    {
        $q = $request->q;
        $pos = Po::query()
            ->when($q, function ($query) use ($q) {
                $query->where('order_no', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(50)
            ->get();
        return response()->json($pos);
    }
    // public function cek($id)
    // {
    //     $report = $qcReportModel::create([
    //         'check_point_id' => 1,
    //         'remark'         => 'OK',
    //         'po_id'          => $id,
    //         'detail_po_id'   => 7,
    //     ]);
    //     $report->photos()->create([
    //         'keterangan' => 'Foto rangka depan',
    //         'path'       => 'uploads/qc/photo1.jpg',
    //     ]);
    // }
    // for api
    // public function getPo()
    // {
    //     $userId = auth()->id();
    //     $pos = Po::with('details')->get();
    //     $detailPoIds = $pos->pluck('details')->flatten()->pluck('id');
    //     // ðŸ”¥ ambil article
    //     $articleNumbers = $pos
    //         ->pluck('details')
    //         ->flatten()
    //         ->pluck('detail.nw_code')
    //         ->filter()
    //         ->unique()
    //         ->values();
    //     // ðŸ”¥ BOM
    //     $boms = Bom::with(['groups.items'])
    //         ->whereIn('article_number', $articleNumbers)
    //         ->get();
    //     $bomMap = $boms->keyBy('article_number');
    //     // ðŸ”¥ CAD
    //     $cads = CadModel::whereIn('article_code', $articleNumbers)
    //         ->orderByDesc('version')
    //         ->get()
    //         ->groupBy('article_code');
    //     // ðŸ”¥ MAPPING
    //     $pos->each(function ($po) use ($bomMap, $cads) {
    //         $po->details->each(function ($detail) use ($bomMap, $cads) {
    //             $article = $detail->detail['nw_code'] ?? null;
    //             // BOM
    //             $detail->bom = ($article && isset($bomMap[$article]))
    //                 ? $bomMap[$article]
    //                 : null;
    //             // CAD
    //             $detail->cad = ($article && isset($cads[$article]))
    //                 ? $cads[$article]->first()
    //                 : null;
    //         });
    //     });
    //     // ðŸ”¥ schedule
    //     $inspectionSchedules = $inspectionModel::with('kategori')
    //         ->whereIn('detail_po_id', $detailPoIds)
    //         ->where('user_id', $userId)
    //         ->get();
    //     return response()->json([
    //         'status'               => 'success',
    //         'data'                 => $pos,
    //         'inspection_schedules' => $inspectionSchedules,
    //     ]);
    // }
    // new

    public function getInspect()
    {
    }

    public function exportPdf($kategori, $po_id)
    {
        $po = DB::table('po')->where('id', $po_id)->first();
        $items = $this->buildQcData($kategori, $po_id);
        // dd($items);
        $pdf = Pdf::loadView('pages.qc.pdf', [
            'items' => $items,
            'kategori' => $kategori,
            'po' => $po,
        ])->setPaper('a4');
        return $pdf->stream("QC-{$kategori}.pdf");
    }
    private function buildQcData($kategori, $po_id)
    {
        $rows = DB::table('inspect_schedule')
            ->join('kategori', 'kategori.id', '=', 'inspect_schedule.kategori_id')
            ->where('inspect_schedule.po_id', $po_id)
            ->whereRaw('LOWER(kategori.kategori) = ?', [strtolower($kategori)])
            ->select('inspect_schedule.*')
            ->get();
        $reports = DB::table('qc_report')
            ->get()
            ->groupBy('inspect_schedule_id');
        $photos = DB::table('report_photo')
            ->get()
            ->groupBy('qc_report_id');
        $items = [];
        foreach ($rows as $r) {
            $itemId = $r->detail_po_id;
            $batch = $r->batch;
            // ðŸ”¥ ambil detail item (JSON)
            if (!isset($items[$itemId])) {
                $detail = DB::table('detail_po')->where('id', $itemId)->first();
                $json = json_decode($detail->detail ?? '{}', true);
                // dd($detail);
                $items[$itemId] = [
                    'article' => $json['article_nr'] ?? $json['article_code'] ?? $json['nw_code'] ?? $json['article_nr_nw'] ?? $json['no'] ?? '-',
                    'name' => $json['description'] ?? $json['nama'] ?? '-',
                    'qty' => (int) ($json['qty'] ?? 0),
                    'batches' => [],
                ];
            }
            // ðŸ”¥ batch init
            if (!isset($items[$itemId]['batches'][$batch])) {
                $items[$itemId]['batches'][$batch] = [
                    'tanggal' => $r->tanggal_inspect,
                    'inspect' => 0,
                    'passed' => 0,
                    'rejected' => 0,
                    'checkpoints' => [],
                ];
            }
            // ðŸ”¥ agregasi
            $items[$itemId]['batches'][$batch]['inspect'] += $r->jumlah_inspect;
            $items[$itemId]['batches'][$batch]['passed'] += $r->passed;
            $items[$itemId]['batches'][$batch]['rejected'] += $r->rejected;
            // ðŸ”¥ ambil qc_report
            $qcRows = $reports[$r->id] ?? [];
            foreach ($qcRows as $qc) {
                $cpId = $qc->check_point_id;
                $a = Checkpoint::find($cpId);
                $cpName = $a->name;
                $items[$itemId]['batches'][$batch]['checkpoints'][$cpName] = [
                    'name' => $cpName,
                    'size' => $qc->size,
                    'remark' => $qc->remark,
                    'photos' => $photos[$qc->id] ?? [],
                ];
            }
            // dd($items);
        }
        return $items;
    }
        public function getDate()
    {
        return response()->json([
            'success' => true,
            'rejected_start' => '2026-09-21',
        ]);
    }
    // public function getDataApi(string $kategoriName, string $detailPoId, string $poId)
    // {
    //     $kategori = Kategori::where('kategori', $kategoriName)
    //         ->firstOrFail();

    //     $detail_po = DetailPo::findOrFail($detailPoId);

    //     $nwCode = $detail_po->detail['nw_code'] ?? null;

    //     $cad = CadModel::where('article_code', $nwCode)
    //         ->orderByDesc('version')
    //         ->first();

    //     $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();

    //     $checkpointIds = $checkpoints->pluck('id');

    //     $qcReports = $qcReportModel::with([
    //         'inspectSchedule:id,po_id,detail_po_id,batch,jumlah_inspect,tanggal_inspect,user_id,passed,rejected',
    //         'photos:id,qc_report_id,keterangan,path',
    //         'checkpoint:id,name',
    //     ])
    //         ->where('po_id', $poId)
    //         ->where('detail_po_id', $detailPoId)
    //         ->whereIn('check_point_id', $checkpointIds)
    //         ->get();

    //     $batches = [];

    //     foreach ($qcReports as $report) {

    //         $schedule = $report->inspectSchedule;

    //         if (! $schedule) {
    //             continue;
    //         }

    //         $batchKey = 'Batch ' . $schedule->batch;

    //         /// ===================================================
    //         /// CREATE BATCH
    //         /// ===================================================
    //         if (! isset($batches[$batchKey])) {

    //             /// ===============================================
    //             /// TEMUAN GLOBAL
    //             /// qc_report_id = NULL
    //             /// ===============================================
    //             $temuan = $reportPhotoModel::where(
    //                 'inspect_schedule_id',
    //                 $schedule->id
    //             )
    //                 ->whereNull('qc_report_id')
    //                 ->get()
    //                 ->map(function ($p) {

    //                     return [
    //                         'keterangan' => $p->keterangan,

    //                         'path'       => url(
    //                             '/storage/' . $p->path
    //                         ),

    //                         'raw_path'   => $p->path,
    //                     ];
    //                 })
    //                 ->values();

    //             $batches[$batchKey] = [

    //                 'batch_ke'       => $schedule->batch,

    //                 'items'          => $detail_po->detail,

    //                 'tanggal'        => $schedule->tanggal_inspect,

    //                 'jumlah_inspect' => $schedule->jumlah_inspect,

    //                 'jenis'          => $kategori->kategori,

    //                 'passed'         => $schedule->passed,

    //                 'rejected'       => $schedule->rejected,

    //                 'inspector'      =>
    //                 User::find(
    //                     $schedule->user_id
    //                 )->name ?? 'N/A',

    //                 'master_sample'  =>
    //                 $cad->master_sample ?? null,

    //                 // ✅ TEMUAN
    //                 'temuan'         => $temuan,

    //                 // ✅ CHECKPOINTS
    //                 'checkpoints'    => [],
    //             ];
    //         }

    //         /// ===================================================
    //         /// CHECKPOINTS
    //         /// ===================================================
    //         $batches[$batchKey]
    //         ['checkpoints']
    //         [$report->checkpoint->name] = [

    //             'size'   => $report->size,

    //             'remark' => $report->remark,

    //             // ✅ FOTO PER CHECKPOINT
    //             'photos' => $report->photos
    //                 ->map(function ($p) {

    //                     return [

    //                         'keterangan' =>
    //                         $p->keterangan,

    //                         'path'       => url(
    //                             '/storage/' . $p->path
    //                         ),

    //                         'raw_path'   =>
    //                         $p->path,
    //                     ];

    //                 })
    //                 ->values(),
    //         ];
    //     }

    //     return response()->json([

    //         'kategori'     => $kategori->kategori,

    //         'po_id'        => $poId,

    //         'detail_po_id' => $detailPoId,

    //         'batches'      => $batches,
    //     ]);
    // }
    public function getDataApi(string $kategoriName, string $detailPoId, string $poId)
    {
        $qcReportModel = $this->qcReportModel();
        $reportPhotoModel = $this->reportPhotoModel();

        $qcReportModel = $this->qcReportModel();
        $reportPhotoModel = $this->reportPhotoModel();

        $kategori = Kategori::where('kategori', $kategoriName)
            ->firstOrFail();

        $detail_po = DetailPo::findOrFail($detailPoId);

        $nwCode = $detail_po->detail['nw_code'] ?? null;

        $cad = CadModel::where('article_code', $nwCode)
            ->orderByDesc('version')
            ->first();

        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();

        $checkpointIds = $checkpoints->pluck('id');

        $qcReports = $qcReportModel::with([
            'inspectSchedule:id,po_id,detail_po_id,spk_id,batch,jumlah_inspect,tanggal_inspect,user_id,passed,rejected',
            'inspectSchedule.spk',
            'photos:id,qc_report_id,keterangan,path',
            'checkpoint:id,name',
        ])
            ->where('po_id', $poId)
            ->where('detail_po_id', $detailPoId)
            ->whereIn('check_point_id', $checkpointIds)
            ->get();

        $batches = [];

        foreach ($qcReports as $report) {

            $schedule = $report->inspectSchedule;

            if (!$schedule) {
                continue;
            }

            $spk = $schedule->spk;

            // $kategoriSpk = data_get($spk->data, 'kategori', 'SPK');
            $kategoriSpk = data_get($spk?->data, 'kategori', 'SPK');
            $noSpk = data_get($spk?->data, 'no_spk', '');
            $supplier = data_get($spk?->data, 'sup', '');

            // ==========================================================
            // KEY = SPK + BATCH
            // ==========================================================
            $batchKey = $noSpk . ' | Batch ' . $schedule->batch;

            if (!isset($batches[$batchKey])) {

                $temuan = $reportPhotoModel::where(
                    'inspect_schedule_id',
                    $schedule->id
                )
                    ->whereNull('qc_report_id')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'keterangan' => $p->keterangan,
                            'path' => url('/storage/' . $p->path),
                            'raw_path' => $p->path,
                        ];
                    })
                    ->values();

                $batches[$batchKey] = [

                    'batch_ke' => $schedule->batch,

                    'batch_title' => $batchKey,

                    'batch_name' => $kategoriSpk,

                    'items' => $detail_po->detail,

                    'tanggal' => $schedule->tanggal_inspect,

                    'jumlah_inspect' => $schedule->jumlah_inspect,

                    'jenis' => $kategori->kategori,

                    'passed' => $schedule->passed,

                    'rejected' => $schedule->rejected,

                    'no_spk' => $noSpk,

                    'supplier' => $supplier,

                    'kategori_spk' => $kategoriSpk,

                    'qty_spk' => $spk->qty ?? 0,

                    'inspector' => optional($schedule->user)->name
                        ?? User::find($schedule->user_id)->name
                        ?? 'N/A',

                    'master_sample' => $cad->master_sample ?? null,

                    'temuan' => $temuan,

                    'checkpoints' => [],
                ];
            }

            $batches[$batchKey]['checkpoints'][$report->checkpoint->name] = [

                'size' => $report->size,

                'remark' => $report->remark,

                'photos' => $report->photos
                    ->map(function ($p) {
                        return [
                            'keterangan' => $p->keterangan,
                            'path' => url('/storage/' . $p->path),
                            'raw_path' => $p->path,
                        ];
                    })
                    ->values(),
            ];
        }

        return response()->json([
            'kategori' => $kategori->kategori,
            'po_id' => $poId,
            'detail_po_id' => $detailPoId,
            'batches' => $batches,
        ]);
    }
    public function insertDummy(string $kategoriName, Request $request)
    {
        $inspectionModel = $this->inspectionScheduleModel();
        $qcReportModel = $this->qcReportModel();
        $reportPhotoModel = $this->reportPhotoModel();

        $po_id = $request->po_id;
        $detail_po_id = $request->detail_po_id;
        /* ===============================
       AMBIL KATEGORI
    =============================== */
        $kategori = Kategori::where('kategori', $kategoriName)->firstOrFail();
        /* ===============================
       CHECKPOINT SESUAI KATEGORI
    =============================== */
        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->pluck('id');
        if ($checkpoints->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Checkpoint untuk kategori ini belum ada',
            ], 400);
        }
        /* ===============================
       AMBIL QTY PO
    =============================== */
        $detailPo = DetailPo::findOrFail($detail_po_id);
        $detail = $detailPo->detail;
        $qtyDetail = (int) ($detail['qty'] ?? 0);
        if ($qtyDetail <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Qty pada detail_po tidak valid',
            ], 400);
        }
        /* ===============================
       HITUNG TOTAL INSPECT PER KATEGORI ðŸ”¥
    =============================== */
        $totalInspect = $inspectionModel::where('detail_po_id', $detail_po_id)
            ->where('kategori_id', $kategori->id)
            ->sum('jumlah_inspect');
        if ($totalInspect >= $qtyDetail) {
            return response()->json([
                'status' => 'error',
                'message' => "Inspect kategori {$kategoriName} sudah memenuhi qty PO",
            ], 400);
        }
        $sisaQty = $qtyDetail - $totalInspect;
        $jumlahInspectBatch = min(10, $sisaQty); // simulasi
        DB::beginTransaction();
        try {
            /* ===============================
           BATCH KE (PER KATEGORI ðŸ”¥)
        =============================== */
            $batchKe = $inspectionModel::where('detail_po_id', $detail_po_id)
                ->where('kategori_id', $kategori->id)
                ->count() + 1;
            /* ===============================
           INSPECT SCHEDULE
        =============================== */
            $inspectSchedule = $inspectionModel::create([
                'po_id' => $po_id,
                'detail_po_id' => $detail_po_id,
                'kategori_id' => $kategori->id,
                'batch' => $batchKe,
                'jumlah_inspect' => $jumlahInspectBatch,
                'tanggal_inspect' => now()->toDateString(),
                'user_id' => 1,
            ]);
            /* ===============================
           QC REPORT + PHOTO
        =============================== */
            foreach ($checkpoints as $checkpointId) {
                $qcReport = $qcReportModel::create([
                    'inspect_schedule_id' => $inspectSchedule->id,
                    'check_point_id' => $checkpointId,
                    'po_id' => $po_id,
                    'detail_po_id' => $detail_po_id,
                    'size' => rand(30, 120),
                    'remark' => $request->remark,
                ]);
                // request dari form foto upload
                foreach (range(1, rand(1, 3)) as $i) {
                    $reportPhotoModel::create([
                        'qc_report_id' => $qcReport->id,
                        'keterangan' => "Foto {$kategoriName} batch {$batchKe}",
                        'path' => 'uploads/qc/' . Str::random(12) . '.jpg',
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'kategori' => $kategoriName,
                'batch' => $batchKe,
                'inspect_batch' => $jumlahInspectBatch,
                'total_inspect' => $totalInspect + $jumlahInspectBatch,
                'qty_po' => $qtyDetail,
                'message' => 'Batch inspect berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //
    private function saveTimelineQC($inspectSchedule)
    {
        $inspectionModel = $this->inspectionScheduleModel();

        try {
            /* ===============================
           CEK APAKAH LANJUTAN
        =============================== */
            $previous = $inspectionModel::where('detail_po_id', $inspectSchedule->detail_po_id)
                ->where('kategori_id', $inspectSchedule->kategori_id)
                ->where('id', '!=', $inspectSchedule->id)
                ->exists();
            $isLanjutan = $previous ? 1 : 0;
            TimelineQc::create([
                'po_id' => $inspectSchedule->po_id,
                'detail_po_id' => $inspectSchedule->detail_po_id,
                'kategori_id' => $inspectSchedule->kategori_id,
                'inspect_schedule_id' => $inspectSchedule->id,
                'user_id' => $inspectSchedule->user_id,
                'qty' => $inspectSchedule->jumlah_inspect,
                'tanggal' => $inspectSchedule->tanggal_inspect,
                'is_lanjutan' => $isLanjutan,
            ]);
            Log::info('Timeline QC Saved', [
                'schedule_id' => $inspectSchedule->id,
                'lanjutan' => $isLanjutan,
            ]);
        } catch (\Throwable $e) {
            Log::error('Timeline QC Error', [
                'msg' => $e->getMessage(),
            ]);
        }
    }
    // timeline qc
    public function timeline()
    {
        $timelines = TimelineQc::with([
            'user:id,name',
            'kategori:id,kategori',
            'schedule:id,batch',
            'detailPo.po:id,order_no,company_name',
        ])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $data = $timelines->map(function ($t) {
            return [
                'po_id' => $t->detailPo->po->id ?? null,
                'order_no' => $t->detailPo->po->order_no ?? '-',
                'company_name' => $t->detailPo->po->company_name ?? '-',
                'detail_po_id' => $t->detail_po_id,
                'tanggal' => $t->tanggal,
                'user' => $t->user->name ?? '-',
                'divisi' => $t->kategori->kategori ?? '-',
                'batch' => $t->schedule->batch ?? null,
                'qty' => $t->qty,
                'is_lanjutan' => (bool) $t->is_lanjutan,
                'label' => $this->buildLabel($t),
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    private function buildLabel($t)
    {
        $tanggal = \Carbon\Carbon::parse($t->tanggal)->translatedFormat('d M');
        $user = $t->user->name ?? '-';
        $div = $t->kategori->kategori ?? '-';
        $qty = $t->qty;
        $lanjutan = $t->is_lanjutan ? ' (lanjutan)' : '';
        return "{$tanggal} â€” {$user} (Div. {$div}) inspect qty = {$qty}{$lanjutan}";
    }
    public function laporan()
    {
        $inspectionModel = $this->inspectionScheduleModel();

        $inspection = $inspectionModel::with(['kategori', 'user', 'spk', 'detailPo', 'po'])
            ->orderBy('tanggal_inspect', 'desc')
            ->get();
        // dd($inspection);
        $qcs = User::with(['karyawan.divisi'])
            ->whereHas('karyawan.divisi', function ($q) {
                $q->where('nama', 'like', 'QC%');
            })
            ->orderBy('name')
            ->get();
        // dd($qcs);
        return view('pages.qc.laporan', compact('qcs', 'inspection'));

    }
    public function monitorDetail($id)
    {
        $po = Po::with([

            /*
        |--------------------------------------------------------------------------
        | SPK
        |--------------------------------------------------------------------------
        */

            'spks.detailPo',

            /*
        |--------------------------------------------------------------------------
        | INSPECT SCHEDULE
        |--------------------------------------------------------------------------
        */

            'spks.inspectSchedules.user',
            'spks.inspectSchedules.kategori',

        ])->findOrFail($id);
        // dd($po);
        return response()->json([
            'spks' => $po->spks,
        ]);
    }
    public function getData(string $kategoriName, string $detailPoId, string $poId)
    {
        $qcReportModel = $this->qcReportModel();

        /* ===============================
       KATEGORI
    =============================== */
        $kategori = Kategori::where('kategori', $kategoriName)
            ->firstOrFail();

        /* ===============================
       CHECKPOINT
    =============================== */
        $checkpoints = Checkpoint::where(
            'kategori_id',
            $kategori->id
        )->get();

        $checkpointIds = $checkpoints->pluck('id');

        /* ===============================
       QC REPORT
    =============================== */
        $qcReports = $qcReportModel::with([
            'inspectSchedule:id,po_id,detail_po_id,batch,jumlah_inspect,tanggal_inspect,user_id,passed,rejected',
            'checkpoint:id,name',
            'photos:id,qc_report_id,inspect_schedule_id,keterangan,path',
        ])
            ->where('po_id', $poId)
            ->where('detail_po_id', $detailPoId)
            ->where(function ($q) use ($checkpointIds) {

                $q->whereIn('check_point_id', $checkpointIds)

                    // ✅ TEMUAN GLOBAL
                    ->orWhereNull('check_point_id');
            })
            ->get();

        /* ===============================
       GROUP BATCH
    =============================== */
        $batches = [];

        foreach ($qcReports as $report) {

            $schedule = $report->inspectSchedule;

            if (!$schedule) {
                continue;
            }

            $batchKey = 'Batch ' . $schedule->batch;

            /* ===============================
           INIT BATCH
        =============================== */
            if (!isset($batches[$batchKey])) {

                $batches[$batchKey] = [

                    'batch_ke' => $schedule->batch,

                    'tanggal' => $schedule->tanggal_inspect,

                    'jumlah_inspect' => $schedule->jumlah_inspect,

                    'passed' => $schedule->passed ?? 0,

                    'rejected' => $schedule->rejected ?? 0,

                    'jenis' => $kategori->kategori,

                    'inspector' => User::find(
                        $schedule->user_id
                    )->name ?? 'N/A',

                    // ✅ TEMUAN GLOBAL
                    'temuan' => [],

                    // ✅ CHECKPOINTS
                    'checkpoints' => [],
                ];
            }

            /* ==================================================
           TEMUAN GLOBAL
           checkpoint_id NULL
        ================================================== */
            if ($report->check_point_id == null) {

                foreach ($report->photos as $photo) {

                    $batches[$batchKey]['temuan'][] = [

                        'keterangan' => $photo->keterangan,

                        'path' => url('/storage/' . $photo->path),

                        'raw_path' => $photo->path,
                    ];
                }

                continue;
            }

            /* ==================================================
           CHECKPOINT
        ================================================== */
            $checkpointName = $report->checkpoint->name ?? 'Unknown';

            if (
                !isset(
                $batches[$batchKey]['checkpoints'][$checkpointName]
            )
            ) {

                $batches[$batchKey]['checkpoints'][$checkpointName] = [

                    'size' => $report->size,

                    'remark' => $report->remark,

                    // ✅ FOTO CHECKPOINT
                    'photos' => [],
                ];
            }

            /* ==================================================
           FOTO CHECKPOINT
        ================================================== */
            foreach ($report->photos as $photo) {

                $batches[$batchKey]['checkpoints'][$checkpointName]['photos'][] = [

                    'keterangan' => $photo->keterangan,

                    'path' => url('/storage/' . $photo->path),

                    'raw_path' => $photo->path,
                ];
            }
        }

        return response()->json([

            'kategori' => $kategori->kategori,

            'po_id' => $poId,

            'detail_po_id' => $detailPoId,

            'batches' => $batches,
        ]);
    }

    public function getCheckpointData(string $kategoriName)
    {
        $kategori = Kategori::where('kategori', $kategoriName)->firstOrFail();
        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();
        return response()->json([
            'status' => 'success',
            'kategori' => $kategoriName,
            'checkpoints' => $checkpoints,
        ]);
    }

    private const USE_TEST_INSPECTION = true;
    //  helpers

    private function inspectionScheduleModel(): string
    {
        return self::USE_TEST_INSPECTION
            ? InspectScheduleTest::class
            : InspectSchedule::class;
    }

    private function qcReportModel(): string
    {
        return self::USE_TEST_INSPECTION
            ? QcReportTest::class
            : QcReport::class;
    }

    private function reportPhotoModel(): string
    {
        return self::USE_TEST_INSPECTION
            ? ReportPhotoTest::class
            : ReportPhoto::class;
    }
    public function insertInspection(
        string $kategoriName,
        Request $request
    ) {
        $inspectionModel = $this->inspectionScheduleModel();
        $qcReportModel = $this->qcReportModel();
        $reportPhotoModel = $this->reportPhotoModel();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | MODEL / MODE
            |--------------------------------------------------------------------------
            |
            | Semua query inspection di function ini akan otomatis
            | menggunakan tabel TEST atau PRODUCTION berdasarkan
            | USE_TEST_INSPECTION.
            |
            */

            $inspectionModel = $this->inspectionScheduleModel();
            $qcReportModel = $this->qcReportModel();
            $reportPhotoModel = $this->reportPhotoModel();

            Log::info('========== QC MODE ==========', [
                'mode' => self::USE_TEST_INSPECTION
                    ? 'TEST'
                    : 'PRODUCTION',
                'inspect_schedule_model' => $inspectionModel,
                'qc_report_model' => $qcReportModel,
                'report_photo_model' => $reportPhotoModel,
            ]);


            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */

            $kategoriName = strtolower(
                trim($kategoriName)
            );

            $kategoriName = str_replace(
                'qc ',
                '',
                $kategoriName
            );

            $kategori = Kategori::whereRaw(
                'LOWER(kategori) = ?',
                [$kategoriName]
            )->first();

            $kategoriTanpaSpk = [
                'unfinish',
                'final',
                'packaging'
            ];

            $useSpk = !in_array(
                $kategoriName,
                $kategoriTanpaSpk
            );

            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori tidak ditemukan',
                ], 404);
            }


            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            $po_id =
                $request->po_id;

            $detail_po_id =
                $request->detail_po_id;

            $spk_id =
                $request->spk_id;

            $reports =
                $request->reports ?? [];

            $findings =
                $request->findings ?? [];

            $qtyInspection =
                (int) $request->qty_inspection;

            $passed =
                (int) $request->passed;

            $rejected =
                (int) $request->rejected;


            /*
            |--------------------------------------------------------------------------
            | FLAG INSPECTION
            |--------------------------------------------------------------------------
            */

            $isReinspect =
                $request->boolean('is_reinspect');

            $isService =
                $request->boolean('is_service');

            $nwService =
                $request->boolean('nw_service');


            Log::info(
                '================ QC INSERT ================='
            );

            Log::info('QC REQUEST', [
                'po' => $po_id,
                'detail_po' => $detail_po_id,
                'spk' => $spk_id,
                'kategori' => $kategoriName,

                'is_reinspect' => $isReinspect,
                'is_service' => $isService,
                'nw_service' => $nwService,

                'mode' => self::USE_TEST_INSPECTION
                    ? 'TEST'
                    : 'PRODUCTION',
            ]);


            /*
            |--------------------------------------------------------------------------
            | VALIDASI PASSED + REJECTED
            |--------------------------------------------------------------------------
            */

            if (
                ($passed + $rejected)
                != $qtyInspection
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Passed + Rejected tidak sesuai',
                ], 400);
            }


            /*
            |--------------------------------------------------------------------------
            | BATCH
            |--------------------------------------------------------------------------
            */

            if ($useSpk) {

                $batchKe = $inspectionModel::where(
                    'spk_id',
                    $spk_id
                )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->count() + 1;

            } else {

                $batchKe = $inspectionModel::where(
                    'detail_po_id',
                    $detail_po_id
                )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->count() + 1;
            }


            /*
            |--------------------------------------------------------------------------
            | QTY SPK / INSPECTION
            |--------------------------------------------------------------------------
            */

            if ($useSpk) {

                $spk = \App\Models\Spk::findOrFail(
                    $spk_id
                );

                $spkData = is_array($spk->data)
                    ? $spk->data
                    : json_decode(
                        $spk->data,
                        true
                    );

                $item = collect(
                    $spkData['items'] ?? []
                )->first(
                        function ($i) use ($detail_po_id) {

                            return (
                                $i['detail_po_id'] ?? null
                            ) == $detail_po_id;
                        }
                    );

                if (!$item) {
                    throw new \Exception(
                        'Item SPK tidak ditemukan'
                    );
                }

                $qtyPo = (int) (
                    $item['qty'] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | TOTAL INSPECT
                |--------------------------------------------------------------------------
                */

                $totalInspect = $inspectionModel::where(
                    'spk_id',
                    $spk_id
                )
                    ->where(
                        'detail_po_id',
                        $detail_po_id
                    )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->sum(
                        'jumlah_inspect'
                    );


                /*
                |--------------------------------------------------------------------------
                | TOTAL REJECTED
                |--------------------------------------------------------------------------
                */

                $totalRejected = $inspectionModel::where(
                    'spk_id',
                    $spk_id
                )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->sum(
                        'rejected'
                    );

            } else {

                /*
                |--------------------------------------------------------------------------
                | DETAIL PO
                |--------------------------------------------------------------------------
                */

                $detailPo = DetailPo::findOrFail(
                    $detail_po_id
                );

                $qtyPo = (int) (
                    $detailPo->detail['qty'] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | TOTAL INSPECT
                |--------------------------------------------------------------------------
                */

                $totalInspect = $inspectionModel::where(
                    'detail_po_id',
                    $detail_po_id
                )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->sum(
                        'jumlah_inspect'
                    );


                /*
                |--------------------------------------------------------------------------
                | TOTAL REJECTED
                |--------------------------------------------------------------------------
                */

                $totalRejected = $inspectionModel::where(
                    'detail_po_id',
                    $detail_po_id
                )
                    ->where(
                        'kategori_id',
                        $kategori->id
                    )
                    ->sum(
                        'rejected'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | INSERT INSPECT SCHEDULE
            |--------------------------------------------------------------------------
            */

            $inspectSchedule =
                $inspectionModel::create([

                    'po_id' =>
                        $po_id,

                    'detail_po_id' =>
                        $detail_po_id,

                    'kategori_id' =>
                        $kategori->id,

                    'batch' =>
                        $batchKe,

                    'jumlah_inspect' =>
                        $qtyInspection,

                    'passed' =>
                        $passed,

                    'rejected' =>
                        $rejected,

                    'tanggal_inspect' =>
                        now()->toDateString(),

                    'user_id' =>
                        auth()->id() ?? 1,

                    'spk_id' =>
                        $useSpk
                        ? $spk_id
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | FLAG
                    |--------------------------------------------------------------------------
                    */

                    'is_reinspect' =>
                        $isReinspect,

                    'is_service' =>
                        $isService,

                    'nw_service' =>
                        $nwService,
                ]);


            /*
            |--------------------------------------------------------------------------
            | SAVE TIMELINE
            |--------------------------------------------------------------------------
            */

            $this->saveTimelineQC(
                $inspectSchedule
            );


            /*
            |--------------------------------------------------------------------------
            | REPORTS
            |--------------------------------------------------------------------------
            */

            $qcReportMap = [];

            foreach ($reports as $report) {

                $checkpointName = strtolower(
                    trim(
                        $report['checkpoint_name']
                    )
                );

                $value =
                    $report['value'];


                /*
                |--------------------------------------------------------------------------
                | KEY
                |--------------------------------------------------------------------------
                */

                $key = str_replace(
                    ' ',
                    '_',
                    $checkpointName
                );


                /*
                |--------------------------------------------------------------------------
                | JSON CHECK
                |--------------------------------------------------------------------------
                */

                $decoded =
                    json_decode(
                        $value,
                        true
                    );


                /*
                |--------------------------------------------------------------------------
                | REMARK FORMAT
                |--------------------------------------------------------------------------
                */

                if (
                    json_last_error()
                    === JSON_ERROR_NONE
                ) {

                    $remark = [
                        $key => $decoded,
                    ];

                } else {

                    $remark = [
                        $key => $value,
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | INSERT QC REPORT
                |--------------------------------------------------------------------------
                */

                $qcReport = $qcReportModel::create([

                    'inspect_schedule_id' =>
                        $inspectSchedule->id,

                    'check_point_id' =>
                        $report['checkpoint_id'],

                    'po_id' =>
                        $po_id,

                    'detail_po_id' =>
                        $detail_po_id,

                    'remark' =>
                        json_encode(
                            $remark
                        ),
                ]);


                $qcReportMap[
                    $report['checkpoint_id']
                ] = $qcReport;
            }


            /*
            |--------------------------------------------------------------------------
            | FINDING PHOTOS
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'finding_images'
                )
            ) {

                foreach (
                    $request->file(
                        'finding_images'
                    ) as $index => $file
                ) {

                    $filename =
                        Str::uuid()
                        . '.'
                        . $file
                            ->getClientOriginalExtension();


                    $path = $file->storeAs(
                        'uploads/qc',
                        $filename,
                        'public'
                    );


                    $reportPhotoModel::create([

                        /*
                        |--------------------------------------------------------------------------
                        | TEMUAN GLOBAL
                        |--------------------------------------------------------------------------
                        */

                        'inspect_schedule_id' =>
                            $inspectSchedule->id,

                        'keterangan' =>
                            $findings[$index]['remark']
                            ?? null,

                        'path' =>
                            $path,
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CHECKPOINT PHOTOS
            |--------------------------------------------------------------------------
            */

            if (
                $request->has(
                    'checkpoint_photos'
                )
            ) {

                foreach (
                    $request->checkpoint_photos
                    as $checkpointId => $photos
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | AMBIL QC REPORT SESUAI CHECKPOINT
                    |--------------------------------------------------------------------------
                    */

                    $checkpointReport =
                        $qcReportModel::where(
                            'inspect_schedule_id',
                            $inspectSchedule->id
                        )
                            ->where(
                                'check_point_id',
                                $checkpointId
                            )
                            ->first();


                    if (!$checkpointReport) {
                        continue;
                    }


                    foreach (
                        $photos as $index => $file
                    ) {

                        if (
                            !$file instanceof
                            \Illuminate\Http\UploadedFile
                        ) {
                            continue;
                        }


                        $filename =
                            Str::uuid()
                            . '.'
                            . $file
                                ->getClientOriginalExtension();


                        $path = $file->storeAs(
                            'uploads/qc',
                            $filename,
                            'public'
                        );


                        $reportPhotoModel::create([

                            /*
                            |--------------------------------------------------------------------------
                            | QC REPORT YANG BENAR
                            |--------------------------------------------------------------------------
                            */

                            'qc_report_id' =>
                                $checkpointReport->id,

                            'inspect_schedule_id' =>
                                $inspectSchedule->id,

                            'keterangan' =>
                                $request
                                    ->checkpoint_photo_remarks[
                                    $checkpointId
                                ][$index]
                                ?? null,

                            'path' =>
                                $path,
                        ]);
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

            DB::commit();


            Log::info(
                '===== QC INSERT SUCCESS =====',
                [
                    'mode' =>
                        self::USE_TEST_INSPECTION
                        ? 'TEST'
                        : 'PRODUCTION',

                    'inspect_schedule_id' =>
                        $inspectSchedule->id,

                    'batch' =>
                        $batchKe,
                ]
            );


            return response()->json([

                'status' =>
                    'success',

                'message' =>
                    'Inspection berhasil',

                'batch' =>
                    $batchKe,

                'inspect_schedule_id' =>
                    $inspectSchedule->id,

                /*
                |--------------------------------------------------------------------------
                | DEBUG MODE
                |--------------------------------------------------------------------------
                */

                'mode' =>
                    self::USE_TEST_INSPECTION
                    ? 'TEST'
                    : 'PRODUCTION',
            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'QC ERROR',
                [
                    'msg' =>
                        $e->getMessage(),

                    'line' =>
                        $e->getLine(),

                    'file' =>
                        $e->getFile(),

                    'mode' =>
                        self::USE_TEST_INSPECTION
                        ? 'TEST'
                        : 'PRODUCTION',
                ]
            );


            return response()->json([
                'status' =>
                    'error',

                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }
    //   public function insertInspection(
//     string $kategoriName,
//     Request $request
// ) {
//     DB::beginTransaction();

    //     try {
//         /*
//         |--------------------------------------------------------------------------
//         | KATEGORI
//         |--------------------------------------------------------------------------
//         */
//         $kategoriName = strtolower(
//             trim($kategoriName)
//         );

    //         $kategoriName = str_replace(
//             'qc ',
//             '',
//             $kategoriName
//         );

    //         $kategori = Kategori::whereRaw(
//             'LOWER(kategori) = ?',
//             [$kategoriName]
//         )->first();

    //         $kategoriTanpaSpk = [
//             'unfinish',
//             'final',
//             'packaging'
//         ];

    //         $useSpk = ! in_array(
//             $kategoriName,
//             $kategoriTanpaSpk
//         );

    //         if (! $kategori) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Kategori tidak ditemukan',
//             ], 404);
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | DATA
//         |--------------------------------------------------------------------------
//         */
//         $po_id =
//             $request->po_id;

    //         $detail_po_id =
//             $request->detail_po_id;

    //         $spk_id =
//             $request->spk_id;

    //         $reports =
//             $request->reports ?? [];

    //         $findings =
//             $request->findings ?? [];

    //         $qtyInspection =
//             (int) $request
//                 ->qty_inspection;

    //         $passed =
//             (int) $request
//                 ->passed;

    //         $rejected =
//             (int) $request
//                 ->rejected;

    //         // ============================================================
//         // TAMBAHAN FLAG INSPECTION
//         // ============================================================
//         $isReinspect =
//             $request->boolean('is_reinspect');

    //         $isService =
//             $request->boolean('is_service');

    //         $nwService =
//             $request->boolean('nw_service');

    //         Log::info(
//             '================ QC INSERT ================='
//         );

    //         Log::info('QC REQUEST', [
//             'po' => $po_id,
//             'detail_po' => $detail_po_id,
//             'spk' => $spk_id,
//             'kategori' => $kategoriName,

    //             // debug flag
//             'is_reinspect' => $isReinspect,
//             'is_service' => $isService,
//             'nw_service' => $nwService,
//         ]);

    //         /*
//         |--------------------------------------------------------------------------
//         | VALIDASI
//         |--------------------------------------------------------------------------
//         */
//         if (
//             ($passed + $rejected)
//             != $qtyInspection
//         ) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Passed + Rejected tidak sesuai',
//             ], 400);
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | BATCH
//         |--------------------------------------------------------------------------
//         */
//         if ($useSpk) {

    //             $batchKe = $inspectionModel::where(
//                 'spk_id',
//                 $spk_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->count() + 1;

    //         } else {

    //             $batchKe = $inspectionModel::where(
//                 'detail_po_id',
//                 $detail_po_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->count() + 1;
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | VALIDASI QTY
//         |--------------------------------------------------------------------------
//         */

    //         if ($useSpk) {

    //             $batchKe = $inspectionModel::where(
//                 'spk_id',
//                 $spk_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->count() + 1;

    //         } else {

    //             $batchKe = $inspectionModel::where(
//                 'detail_po_id',
//                 $detail_po_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->count() + 1;
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | QTY SPK / INSPECTION
//         |--------------------------------------------------------------------------
//         */
//         if ($useSpk) {

    //             $spk = \App\Models\Spk::findOrFail(
//                 $spk_id
//             );

    //             $spkData = is_array($spk->data)
//                 ? $spk->data
//                 : json_decode(
//                     $spk->data,
//                     true
//                 );

    //             $item = collect(
//                 $spkData['items'] ?? []
//             )->first(
//                 function ($i) use ($detail_po_id) {

    //                     return (
//                         $i['detail_po_id'] ?? null
//                     ) == $detail_po_id;
//                 }
//             );

    //             if (! $item) {
//                 throw new \Exception(
//                     'Item SPK tidak ditemukan'
//                 );
//             }

    //             $qtyPo = (int) (
//                 $item['qty'] ?? 0
//             );

    //             $totalInspect = $inspectionModel::where(
//                 'spk_id',
//                 $spk_id
//             )
//                 ->where(
//                     'detail_po_id',
//                     $detail_po_id
//                 )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->sum(
//                     'jumlah_inspect'
//                 );

    //             $totalRejected = $inspectionModel::where(
//                 'spk_id',
//                 $spk_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->sum(
//                     'rejected'
//                 );

    //         } else {

    //             $detailPo = DetailPo::findOrFail(
//                 $detail_po_id
//             );

    //             $qtyPo = (int) (
//                 $detailPo->detail['qty'] ?? 0
//             );

    //             $totalInspect = $inspectionModel::where(
//                 'detail_po_id',
//                 $detail_po_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->sum(
//                     'jumlah_inspect'
//                 );

    //             $totalRejected = $inspectionModel::where(
//                 'detail_po_id',
//                 $detail_po_id
//             )
//                 ->where(
//                     'kategori_id',
//                     $kategori->id
//                 )
//                 ->sum(
//                     'rejected'
//                 );
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | INSERT INSPECT SCHEDULE
//         |--------------------------------------------------------------------------
//         */
//         $inspectSchedule =
//             $inspectionModel::create([

    //                 'po_id' =>
//                     $po_id,

    //                 'detail_po_id' =>
//                     $detail_po_id,

    //                 'kategori_id' =>
//                     $kategori->id,

    //                 'batch' =>
//                     $batchKe,

    //                 'jumlah_inspect' =>
//                     $qtyInspection,

    //                 'passed' =>
//                     $passed,

    //                 'rejected' =>
//                     $rejected,

    //                 'tanggal_inspect' =>
//                     now()->toDateString(),

    //                 'user_id' =>
//                     auth()->id() ?? 1,

    //                 'spk_id' =>
//                     $useSpk
//                         ? $spk_id
//                         : null,

    //                 // ====================================================
//                 // TAMBAHAN FLAG
//                 // ====================================================
//                 'is_reinspect' =>
//                     $isReinspect,

    //                 'is_service' =>
//                     $isService,

    //                 'nw_service' =>
//                     $nwService,
//             ]);

    //         /*
//         |--------------------------------------------------------------------------
//         | SAVE TIMELINE
//         |--------------------------------------------------------------------------
//         */
//         $this->saveTimelineQC(
//             $inspectSchedule
//         );

    //         /*
//         |--------------------------------------------------------------------------
//         | REPORTS
//         |--------------------------------------------------------------------------
//         */
//         foreach ($reports as $report) {

    //             $checkpointName = strtolower(
//                 trim(
//                     $report['checkpoint_name']
//                 )
//             );

    //             $value =
//                 $report['value'];

    //             /*
//             |--------------------------------------------------------------------------
//             | KEY
//             |--------------------------------------------------------------------------
//             */
//             $key = str_replace(
//                 ' ',
//                 '_',
//                 $checkpointName
//             );

    //             /*
//             |--------------------------------------------------------------------------
//             | JSON CHECK
//             |--------------------------------------------------------------------------
//             */
//             $decoded =
//                 json_decode(
//                     $value,
//                     true
//                 );

    //             /*
//             |--------------------------------------------------------------------------
//             | REMARK FORMAT
//             |--------------------------------------------------------------------------
//             */
//             if (
//                 json_last_error()
//                 === JSON_ERROR_NONE
//             ) {

    //                 $remark = [
//                     $key => $decoded,
//                 ];

    //             } else {

    //                 $remark = [
//                     $key => $value,
//                 ];
//             }

    //             /*
//             |--------------------------------------------------------------------------
//             | INSERT REPORT
//             |--------------------------------------------------------------------------
//             */
//             $qcReport = $qcReportModel::create([

    //                 'inspect_schedule_id' =>
//                     $inspectSchedule->id,

    //                 'check_point_id' =>
//                     $report['checkpoint_id'],

    //                 'po_id' =>
//                     $po_id,

    //                 'detail_po_id' =>
//                     $detail_po_id,

    //                 'remark' =>
//                     json_encode(
//                         $remark
//                     ),
//             ]);

    //             $qcReportMap[
//                 $report['checkpoint_id']
//             ] = $qcReport;
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | FINDING PHOTOS
//         |--------------------------------------------------------------------------
//         */
//         if (
//             $request->hasFile(
//                 'finding_images'
//             )
//         ) {

    //             foreach (
//                 $request->file(
//                     'finding_images'
//                 ) as $index => $file
//             ) {

    //                 $filename =
//                     Str::uuid()
//                     . '.'
//                     . $file
//                         ->getClientOriginalExtension();

    //                 $path = $file->storeAs(
//                     'uploads/qc',
//                     $filename,
//                     'public'
//                 );

    //                 $reportPhotoModel::create([

    //                     // TEMUAN GLOBAL
//                     // 'qc_report_id' => $qcReport->id,

    //                     'inspect_schedule_id' =>
//                         $inspectSchedule->id,

    //                     'keterangan' =>
//                         $findings[$index]['remark']
//                         ?? null,

    //                     'path' =>
//                         $path,
//                 ]);
//             }
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | CHECKPOINT PHOTOS
//         |--------------------------------------------------------------------------
//         */
//         if (
//             $request->has(
//                 'checkpoint_photos'
//             )
//         ) {

    //             foreach (
//                 $request->checkpoint_photos
//                 as $checkpointId => $photos
//             ) {

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | AMBIL QC REPORT SESUAI CHECKPOINT
//                 |--------------------------------------------------------------------------
//                 */
//                 $checkpointReport =
//                     $qcReportModel::where(
//                         'inspect_schedule_id',
//                         $inspectSchedule->id
//                     )
//                         ->where(
//                             'check_point_id',
//                             $checkpointId
//                         )
//                         ->first();

    //                 if (! $checkpointReport) {
//                     continue;
//                 }

    //                 foreach (
//                     $photos as $index => $file
//                 ) {

    //                     if (
//                         ! $file instanceof
//                         \Illuminate\Http\UploadedFile
//                     ) {
//                         continue;
//                     }

    //                     $filename =
//                         Str::uuid()
//                         . '.'
//                         . $file
//                             ->getClientOriginalExtension();

    //                     $path = $file->storeAs(
//                         'uploads/qc',
//                         $filename,
//                         'public'
//                     );

    //                     $reportPhotoModel::create([

    //                         // QC REPORT YANG BENAR
//                         'qc_report_id' =>
//                             $checkpointReport->id,

    //                         'inspect_schedule_id' =>
//                             $inspectSchedule->id,

    //                         'keterangan' =>
//                             $request
//                                 ->checkpoint_photo_remarks[
//                                     $checkpointId
//                                 ][$index]
//                             ?? null,

    //                         'path' =>
//                             $path,
//                     ]);
//                 }
//             }
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | COMMIT
//         |--------------------------------------------------------------------------
//         */
//         DB::commit();

    //         Log::info(
//             '===== QC INSERT SUCCESS ====='
//         );

    //         return response()->json([
//             'status' =>
//                 'success',

    //             'message' =>
//                 'Inspection berhasil',

    //             'batch' =>
//                 $batchKe,

    //             'inspect_schedule_id' =>
//                 $inspectSchedule->id,
//         ]);

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         Log::error(
//             'QC ERROR',
//             [
//                 'msg' =>
//                     $e->getMessage(),

    //                 'line' =>
//                     $e->getLine(),

    //                 'file' =>
//                     $e->getFile(),
//             ]
//         );

    //         return response()->json([
//             'status' =>
//                 'error',

    //             'message' =>
//                 $e->getMessage(),
//         ], 500);
//     }
// }
//   public function insertInspection(
//         string $kategoriName,
//         Request $request
//     ) {

    //         DB::beginTransaction();
//         try {
//             /*
//         |--------------------------------------------------------------------------
//         | KATEGORI
//         |--------------------------------------------------------------------------
//         */
//             $kategoriName = strtolower(
//                 trim($kategoriName)
//             );
//             $kategoriName = str_replace(
//                 'qc ',
//                 '',
//                 $kategoriName
//             );
//             $kategori = Kategori::whereRaw(
//                 'LOWER(kategori) = ?',
//                 [$kategoriName]
//             )->first();
//             $kategoriTanpaSpk = ['unfinish', 'final', 'packaging'];

    //             $useSpk = ! in_array($kategoriName, $kategoriTanpaSpk);
//             if (! $kategori) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => 'Kategori tidak ditemukan',
//                 ], 404);
//             }
//             /*
//         |--------------------------------------------------------------------------
//         | DATA
//         |--------------------------------------------------------------------------
//         */
//             $po_id =
//             $request->po_id;
//             $detail_po_id =
//             $request->detail_po_id;
//             $spk_id =
//             $request->spk_id;
//             $reports =
//             $request->reports ?? [];
//             $findings =
//             $request->findings ?? [];
//             $qtyInspection =
//             (int) $request
//                 ->qty_inspection;
//             $passed =
//             (int) $request
//                 ->passed;
//             $rejected =
//             (int) $request
//                 ->rejected;
//             $isReinspect =
//             $request->boolean('is_reinspect');
//             Log::info('================ QC INSERT =================');

    //             Log::info('QC REQUEST', [
//                 'po' => $po_id,
//                 'detail_po' => $detail_po_id,
//                 'spk' => $spk_id,
//                 'kategori' => $kategoriName,
//             ]);
//             /*
//         |--------------------------------------------------------------------------
//         | VALIDASI
//         |--------------------------------------------------------------------------
//         */
//             if (
//                 ($passed + $rejected)
//                 != $qtyInspection
//             ) {
//                 return response()->json([
//                     'status' => 'error',
//                     'message' => 'Passed + Rejected tidak sesuai',
//                 ], 400);
//             }
//             /*
//         |--------------------------------------------------------------------------
//         | BATCH
//         |--------------------------------------------------------------------------
//         */
//             if ($useSpk) {

    //                 $batchKe = $inspectionModel::where('spk_id', $spk_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->count() + 1;

    //             } else {

    //                 $batchKe = $inspectionModel::where('detail_po_id', $detail_po_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->count() + 1;

    //             }

    //             //
//             /*
//             |--------------------------------------------------------------------------
//             | VALIDASI QTY
//             |--------------------------------------------------------------------------
//             */

    //             if ($useSpk) {

    //                 $batchKe = $inspectionModel::where('spk_id', $spk_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->count() + 1;

    //             } else {

    //                 $batchKe = $inspectionModel::where('detail_po_id', $detail_po_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->count() + 1;

    //             }

    //             if ($useSpk) {

    //                 $spk = \App\Models\Spk::findOrFail($spk_id);
//                 $spkData = is_array($spk->data) ? $spk->data : json_decode($spk->data, true);

    //                 $item = collect($spkData['items'] ?? [])->first(function ($i) use ($detail_po_id) {
//                     return ($i['detail_po_id'] ?? null) == $detail_po_id;
//                 });

    //                 if (! $item) {
//                     throw new \Exception('Item SPK tidak ditemukan');
//                 }

    //                 $qtyPo = (int) ($item['qty'] ?? 0);

    //                 $totalInspect = $inspectionModel::where('spk_id', $spk_id)
//                     ->where('detail_po_id', $detail_po_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->sum('jumlah_inspect');

    //                 $totalRejected = $inspectionModel::where('spk_id', $spk_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->sum('rejected');

    //             } else {

    //                 $detailPo = DetailPo::findOrFail($detail_po_id);
//                 $qtyPo = (int) ($detailPo->detail['qty'] ?? 0);

    //                 $totalInspect = $inspectionModel::where('detail_po_id', $detail_po_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->sum('jumlah_inspect');

    //                 $totalRejected = $inspectionModel::where('detail_po_id', $detail_po_id)
//                     ->where('kategori_id', $kategori->id)
//                     ->sum('rejected');

    //             }

    //             $inspectSchedule =
//             $inspectionModel::create([
//                 'po_id' => $po_id,
//                 'detail_po_id' => $detail_po_id,
//                 'kategori_id' => $kategori->id,
//                 'batch' => $batchKe,
//                 'jumlah_inspect' => $qtyInspection,
//                 'passed' => $passed,
//                 'rejected' => $rejected,
//                 'tanggal_inspect' => now()
//                     ->toDateString(),
//                 'user_id' => auth()->id() ?? 1,
//                 'spk_id' => $useSpk ? $spk_id : null,
//             ]);
//             /*
//         |--------------------------------------------------------------------------
//         | SAVE TIMELINE
//         |--------------------------------------------------------------------------
//         */
//             $this->saveTimelineQC(
//                 $inspectSchedule
//             );
//             /*
//         |--------------------------------------------------------------------------
//         | REPORTS
//         |--------------------------------------------------------------------------
//         */
//             foreach ($reports as $report) {
//                 $checkpointName = strtolower(
//                     trim(
//                         $report['checkpoint_name']
//                     )
//                 );
//                 $value =
//                     $report['value'];
//                 /*
//             |--------------------------------------------------------------------------
//             | KEY
//             |--------------------------------------------------------------------------
//             */
//                 $key = str_replace(
//                     ' ',
//                     '_',
//                     $checkpointName
//                 );
//                 /*
//             |--------------------------------------------------------------------------
//             | JSON CHECK
//             |--------------------------------------------------------------------------
//             */
//                 $decoded =
//                     json_decode(
//                         $value,
//                         true
//                     );
//                 /*
//             |--------------------------------------------------------------------------
//             | REMARK FORMAT
//             |--------------------------------------------------------------------------
//             */
//                 if (
//                     json_last_error()
//                     === JSON_ERROR_NONE
//                 ) {
//                     $remark = [
//                         $key => $decoded,
//                     ];
//                 } else {
//                     $remark = [
//                         $key => $value,
//                     ];
//                 }
//                 /*
//             |--------------------------------------------------------------------------
//             | INSERT REPORT
//             |--------------------------------------------------------------------------
//             */
//                 $qcReport = $qcReportModel::create([
//                     'inspect_schedule_id' => $inspectSchedule->id,
//                     'check_point_id' => $report['checkpoint_id'],
//                     'po_id' => $po_id,
//                     'detail_po_id' => $detail_po_id,
//                     'remark' => json_encode(
//                         $remark
//                     ),
//                 ]);
//                 $qcReportMap[
//                     $report['checkpoint_id']
//                 ] = $qcReport;
//             }
//             /*
//         |--------------------------------------------------------------------------
//         | FINDING PHOTOS
//         |--------------------------------------------------------------------------
//         */
//             if ($request->hasFile('finding_images')) {
//                 foreach ($request->file('finding_images') as $index => $file) {
//                     $filename =
//                     Str::uuid().'.'.
//                     $file->getClientOriginalExtension();
//                     $path = $file->storeAs(
//                         'uploads/qc',
//                         $filename,
//                         'public'
//                     );
//                     $reportPhotoModel::create([
//                         // ✅ TEMUAN GLOBAL
//                         // 'qc_report_id'        => $qcReport->id,
//                         'inspect_schedule_id' => $inspectSchedule->id,
//                         'keterangan' => $findings[$index]['remark'] ?? null,
//                         'path' => $path,
//                     ]);
//                 }
//             }
//             if ($request->has('checkpoint_photos')) {
//                 foreach (
//                     $request->checkpoint_photos as $checkpointId => $photos
//                 ) {
//                     // ✅ AMBIL QC REPORT SESUAI CHECKPOINT
//                     $checkpointReport = $qcReportModel::where(
//                         'inspect_schedule_id',
//                         $inspectSchedule->id
//                     )
//                         ->where(
//                             'check_point_id',
//                             $checkpointId
//                         )
//                         ->first();
//                     if (! $checkpointReport) {
//                         continue;
//                     }
//                     foreach ($photos as $index => $file) {
//                         if (
//                             ! $file instanceof \Illuminate\Http\UploadedFile
//                         ) {
//                             continue;
//                         }
//                         $filename =
//                         Str::uuid().'.'.
//                         $file->getClientOriginalExtension();
//                         $path = $file->storeAs(
//                             'uploads/qc',
//                             $filename,
//                             'public'
//                         );
//                         $reportPhotoModel::create([
//                             // ✅ QC REPORT YANG BENAR
//                             'qc_report_id' => $checkpointReport->id,
//                             'inspect_schedule_id' => $inspectSchedule->id,
//                             'keterangan' => $request
//                                 ->checkpoint_photo_remarks[$checkpointId][$index] ?? null,
//                             'path' => $path,
//                         ]);
//                     }
//                 }
//             }
//             DB::commit();
//             Log::info(
//                 '===== QC INSERT SUCCESS ====='
//             );

    //             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Inspection berhasil',
//                 'batch' => $batchKe,
//                 'inspect_schedule_id' => $inspectSchedule->id,
//             ]);
//         } catch (\Throwable $e) {
//             DB::rollBack();
//             Log::error(
//                 'QC ERROR',
//                 [
//                     'msg' => $e->getMessage(),
//                     'line' => $e->getLine(),
//                     'file' => $e->getFile(),
//                 ]
//             );

    //             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage(),
//             ], 500);
//         }
//     }

    public function show(string $id)
    {
        //
        $data = Po::find($id);
        $detailP = DetailPo::where('po_id', $data->id)->get();
        // dd( $detailP );
        $jenis = Kategori::all();
        return view('pages.qc.detail', compact('data', 'detailP', 'jenis'));
    }

    //      public function getPo()
//     {
//         $userId = auth()->id();
//         $user = auth()->user();

    //         $user->load('karyawan.divisi');

    //         // divisi dari request (khusus Sobana)
//         $requestDivisi = request('divisi');

    //         // kalau tidak ada, pakai divisi user login
//         $divisiQc = strtoupper(
//             $requestDivisi
//                 ?: ($user->karyawan?->divisi?->nama ?? '')
//         );
//           Log::info('hallo', [
//                 'schedule_id' => $divisiQc,
//             ]);
//         /*
//     |--------------------------------------------------------------------------
//     | GET PO
//     |--------------------------------------------------------------------------
//     */

    //         $pos = Po::with([
//             'details',
//             'spks',
//         ])->get();

    //         $detailPoIds = $pos
//             ->pluck('details')
//             ->flatten()
//             ->pluck('id');

    //         /*
//     |--------------------------------------------------------------------------
//     | ARTICLE
//     |--------------------------------------------------------------------------
//     */

    //       $articleNumbers = $pos
//             ->pluck('details')
//             ->flatten()
//             ->map(function ($detail) {
//                 $articleNr = $detail->detail['article_nr_'] ?? null;
//                 $nwCode = $detail->detail['nw_code'] ?? null;

    //                 return $nwCode === null
//                     ? $articleNr
//                     : ($articleNr ?? $nwCode);
//             })
//             ->filter()
//             ->unique()
//             ->values();

    //         /*
//     |--------------------------------------------------------------------------
//     | BOM
//     |--------------------------------------------------------------------------
//     */

    //         $boms = Bom::with([
//             'groups.items',
//         ])
//             ->whereIn(
//                 'article_number',
//                 $articleNumbers
//             )
//             ->get();

    //         $bomMap = $boms->keyBy(
//             'article_number'
//         );

    //         /*
//     |--------------------------------------------------------------------------
//     | CAD
//     |--------------------------------------------------------------------------
//     */

    //         $cads = CadModel::whereIn(
//             'article_code',
//             $articleNumbers
//         )
//             ->orderByDesc('version')
//             ->get()
//             ->groupBy(function ($item) {

    //                 return (string)
//                 $item->article_code;

    //             });

    //         /*
//     |--------------------------------------------------------------------------
//     | INSPECTION
//     |--------------------------------------------------------------------------
//     */

    //         $inspectionSchedules =
//         $inspectionModel::with([
//             'kategori',
//             'user',
//         ])
//             ->whereIn(
//                 'detail_po_id',
//                 $detailPoIds
//             )
//             ->get();

    //         /*
//     |--------------------------------------------------------------------------
//     | MAPPING
//     |--------------------------------------------------------------------------
//     */

    //         $pos->each(function ($po) use (

    //         $bomMap,
//         $cads,
//         $inspectionSchedules,
//           $divisiQc




    //         ) {

    //             $po->details->each(function ($detail) use (

    //                 $po,
//                 $bomMap,
//                 $cads,
//                 $inspectionSchedules,
//           $divisiQc

    //             ) {

    //                 /*
//             |--------------------------------------------------------------------------
//             | ARTICLE
//             |--------------------------------------------------------------------------
//             */

    //                 $article = (string) (

    //                     $detail->detail['article_nr_'] ?? ''

    //                 );

    //                 /*
//             |--------------------------------------------------------------------------
//             | BOM
//             |--------------------------------------------------------------------------
//             */

    //                 $detail->bom = (

    //                     $article &&
//                     isset($bomMap[$article])

    //                 )
//                     ? $bomMap[$article]
//                     : null;

    //                 /*
//             |--------------------------------------------------------------------------
//             | CAD
//             |--------------------------------------------------------------------------
//             */

    //                 $detail->cad = (

    //                     $article &&
//                     isset($cads[$article])

    //                 )
//                     ? $cads[$article]->first()
//                     : null;

    //                 /*
//             |--------------------------------------------------------------------------
//             | INSPECTION
//             |--------------------------------------------------------------------------
//             */

    //                 $detail->inspection_schedules =
//                 $inspectionSchedules
//                     ->where(
//                         'detail_po_id',
//                         $detail->id
//                     )
//                     ->values();

    //                 /*
//             |--------------------------------------------------------------------------
//             | SPK TERKAIT
//             |--------------------------------------------------------------------------
//             */

    //                 $relatedSpks = [];

    //                 foreach ($po->spks as $spk) {

    //                     $spkData = $spk->data;
// // each baru
//             $kategoriSpk = strtoupper(
//                 $spkData['kategori'] ?? ''
//             );

    //             if (
//                 !$this->matchDivisi(
//                     $divisiQc,
//                     $kategoriSpk
//                 )
//             ) {
//                 continue;
//             }
//                     if (
//                         is_string($spkData)
//                     ) {

    //                         $spkData = json_decode(
//                             $spkData,
//                             true
//                         );

    //                     }

    //                     $items =
//                     $spkData['items'] ?? [];

    //                     foreach ($items as $item) {

    //                         if (

    //                             ($item['detail_po_id'] ?? null)

    //                             ==

    //                             $detail->id

    //                         ) {
//                             $inspect = $inspectionSchedules
//                                 ->where('detail_po_id', $detail->id)
//                                 ->where('spk_id', $spk->id);

    //                             $passed = $inspect->sum('passed');

    //                             $rejected = $inspect->sum('rejected');

    //                             $relatedSpks[] = [
//                             // TAMBAHAN





    //                                 'passed'      => $passed,

    //                                 'rejected'    => $rejected,
//                                 'id'          =>
//                                 $spk->id,

    //                                 'supplier'    =>
//                                 $spkData['sup'] ?? null,

    //                                 'kategori'    =>
//                                 $spkData['kategori'] ?? null,

    //                                 'status'      =>
//                                 $spkData['status'] ?? null,

    //                                 'no_spk'      =>
//                                 $spkData['no_spk'] ?? null,

    //                                 'tgl_terima'  =>
//                                 $spkData['tgl_terima'] ?? null,

    //                                 'tgl_selesai' =>
//                                 $spkData['tgl_selesai'] ?? null,

    //                                 'material'    =>
//                                 $item['material'] ?? '',

    //                                 'qty'         =>
//                                 $item['qty'] ?? 0,

    //                                 'harga'       =>
//                                 $item['harga'] ?? 0,

    //                                 'total'       =>
//                                 $item['total'] ?? 0,

    //                             ];

    //                         }

    //                     }

    //                 }

    //                 $detail->spks =
//                     $relatedSpks;

    //             });

    //         });

    //         /*
//     |--------------------------------------------------------------------------
//     | RETURN
//     |--------------------------------------------------------------------------
//     */

    //         return response()->json([

    //             'status' => 'success',

    //             'data'   => $pos,

    //         ]);
//     }
// public function getPo()
// {
//     $userId = auth()->id();
//     $user = auth()->user();

    //     $user->load('karyawan.divisi');

    //     // divisi dari request (khusus Sobana)
//     $requestDivisi = request('divisi');

    //     // kalau tidak ada, pakai divisi user login
//     $divisiQc = strtoupper(
//         $requestDivisi
//         ?: ($user->karyawan?->divisi?->nama ?? '')
//     );

    //     Log::info('hallo', [
//         'schedule_id' => $divisiQc,
//     ]);

    //     /*
//     |--------------------------------------------------------------------------
//     | HELPER PEMBULATAN NUMERIC
//     |--------------------------------------------------------------------------
//     |
//     | Hanya bekerja pada response API.
//     | Tidak mengubah database.
//     |
//     | Contoh:
//     |
//     | 56       -> 56
//     | 56.4     -> 56
//     | 56.5     -> 57
//     | "56,5"   -> 57
//     | "56.5"   -> 57
//     |
//     |--------------------------------------------------------------------------
//     */
//     $roundNumeric = function ($value) {

    //         if ($value === null || $value === '') {
//             return $value;
//         }

    //         if (is_int($value)) {
//             return $value;
//         }

    //         if (is_float($value)) {
//             return (int) round($value);
//         }

    //         if (is_string($value)) {

    //             $value = trim($value);

    //             if ($value === '') {
//                 return $value;
//             }

    //             /*
//             |--------------------------------------------------------------------------
//             | HANYA PROSES STRING YANG MEMANG ANGKA
//             |--------------------------------------------------------------------------
//             */

    //             $normalized = $value;

    //             // 56,5 -> 56.5
//             if (
//                 str_contains($normalized, ',') &&
//                 !str_contains($normalized, '.')
//             ) {

    //                 $normalized = str_replace(
//                     ',',
//                     '.',
//                     $normalized
//                 );
//             }

    //             /*
//             |--------------------------------------------------------------------------
//             | FORMAT 1.250,5
//             |--------------------------------------------------------------------------
//             */

    //             elseif (
//                 str_contains($normalized, ',') &&
//                 str_contains($normalized, '.')
//             ) {

    //                 $lastComma =
//                     strrpos($normalized, ',');

    //                 $lastDot =
//                     strrpos($normalized, '.');

    //                 if ($lastComma > $lastDot) {

    //                     // 1.250,5 -> 1250.5

    //                     $normalized =
//                         str_replace(
//                             '.',
//                             '',
//                             $normalized
//                         );

    //                     $normalized =
//                         str_replace(
//                             ',',
//                             '.',
//                             $normalized
//                         );
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | FORMAT 1,250.5
//                 |--------------------------------------------------------------------------
//                 */

    //                 else {

    //                     $normalized =
//                         str_replace(
//                             ',',
//                             '',
//                             $normalized
//                         );
//                 }
//             }

    //             if (is_numeric($normalized)) {

    //                 return (int) round(
//                     (float) $normalized
//                 );
//             }

    //             /*
//             |--------------------------------------------------------------------------
//             | BUKAN ANGKA
//             |--------------------------------------------------------------------------
//             |
//             | Misalnya:
//             | "ZIVANA CHAIR"
//             | "56 KG"
//             | "RANGKA BESI"
//             |
//             | Jangan disentuh.
//             |--------------------------------------------------------------------------
//             */

    //             return $value;
//         }

    //         return $value;
//     };

    //     /*
//     |--------------------------------------------------------------------------
//     | RECURSIVE NORMALIZATION
//     |--------------------------------------------------------------------------
//     |
//     | Collection / Model / Array akan dibaca sampai nested paling dalam.
//     |
//     | Tetapi ID integer tetap integer.
//     |
//     |--------------------------------------------------------------------------
//     */

    //     $normalizeResponse = function ($value) use (
//         &$normalizeResponse,
//         $roundNumeric
//     ) {

    //         /*
//         |--------------------------------------------------------------------------
//         | COLLECTION
//         |--------------------------------------------------------------------------
//         */

    //         if ($value instanceof \Illuminate\Support\Collection) {

    //             return $value
//                 ->map(function ($item) use (
//                     $normalizeResponse
//                 ) {

    //                     return $normalizeResponse(
//                         $item
//                     );
//                 })
//                 ->values()
//                 ->all();
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | ELOQUENT MODEL
//         |--------------------------------------------------------------------------
//         */

    //         if (
//             $value instanceof
//             \Illuminate\Database\Eloquent\Model
//         ) {

    //             return $normalizeResponse(
//                 $value->toArray()
//             );
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | ARRAY
//         |--------------------------------------------------------------------------
//         */

    //         if (is_array($value)) {

    //             $result = [];

    //             foreach (
//                 $value as $key => $item
//             ) {

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | BOOLEAN
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (is_bool($item)) {

    //                     $result[$key] = $item;

    //                     continue;
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | ARRAY / OBJECT / COLLECTION
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (
//                     is_array($item) ||
//                     $item instanceof
//                         \Illuminate\Support\Collection ||
//                     $item instanceof
//                         \Illuminate\Database\Eloquent\Model
//                 ) {

    //                     $result[$key] =
//                         $normalizeResponse(
//                             $item
//                         );

    //                     continue;
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | NUMERIC
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (
//                     is_int($item) ||
//                     is_float($item) ||
//                     (
//                         is_string($item) &&
//                         is_numeric(
//                             str_replace(
//                                 ',',
//                                 '.',
//                                 trim($item)
//                             )
//                         )
//                     )
//                 ) {

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | JANGAN UBAH FIELD TEXT TERTENTU
//                     |--------------------------------------------------------------------------
//                     |
//                     | Contoh:
//                     | article number
//                     | kode
//                     | no_spk
//                     | no_po
//                     | phone
//                     | tanggal
//                     |
//                     |--------------------------------------------------------------------------
//                     */

    //                     $protectedKeys = [

    //                         'id',
//                         'po_id',
//                         'spk_id',
//                         'detail_po_id',
//                         'user_id',
//                         'kategori_id',
//                         'batch',
//                         'version',

    //                         'kode',
//                         'article_code',
//                         'article_number',
//                         'article_nr_',
//                         'nw_code',

    //                         'no_spk',
//                         'no_po',
//                         'nomor_invoice',

    //                         'tanggal',
//                         'tanggal_invoice',
//                         'tanggal_inspect',
//                         'tgl_terima',
//                         'tgl_selesai',

    //                         'status',
//                         'kategori',
//                         'supplier',
//                         'material',
//                         'nama',
//                         'description',
//                     ];

    //                     if (
//                         in_array(
//                             $key,
//                             $protectedKeys,
//                             true
//                         )
//                     ) {

    //                         $result[$key] = $item;

    //                     } else {

    //                         $result[$key] =
//                             $roundNumeric(
//                                 $item
//                             );
//                     }

    //                     continue;
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | VALUE LAIN
//                 |--------------------------------------------------------------------------
//                 */

    //                 $result[$key] = $item;
//             }

    //             return $result;
//         }

    //         /*
//         |--------------------------------------------------------------------------
//         | NUMERIC LANGSUNG
//         |--------------------------------------------------------------------------
//         */

    //         if (
//             is_int($value) ||
//             is_float($value)
//         ) {

    //             return $roundNumeric(
//                 $value
//             );
//         }

    //         return $value;
//     };

    //     /*
//     |--------------------------------------------------------------------------
//     | GET PO
//     |--------------------------------------------------------------------------
//     */

    //     $pos = Po::with([
//         'details',
//         'spks',
//     ])->get();

    //     $detailPoIds = $pos
//         ->pluck('details')
//         ->flatten()
//         ->pluck('id');

    //     /*
//     |--------------------------------------------------------------------------
//     | ARTICLE
//     |--------------------------------------------------------------------------
//     */

    //     $articleNumbers = $pos
//         ->pluck('details')
//         ->flatten()
//         ->map(function ($detail) {

    //             $articleNr =
//                 $detail->detail['article_nr_']
//                 ?? null;

    //             $nwCode =
//                 $detail->detail['nw_code']
//                 ?? null;

    //             return $nwCode === null
//                 ? $articleNr
//                 : ($articleNr ?? $nwCode);
//         })
//         ->filter()
//         ->unique()
//         ->values();

    //     /*
//     |--------------------------------------------------------------------------
//     | BOM
//     |--------------------------------------------------------------------------
//     */

    //     $boms = Bom::with([
//         'groups.items',
//     ])
//         ->whereIn(
//             'article_number',
//             $articleNumbers
//         )
//         ->get();

    //     $bomMap = $boms->keyBy(
//         'article_number'
//     );

    //     /*
//     |--------------------------------------------------------------------------
//     | CAD
//     |--------------------------------------------------------------------------
//     */

    //     $cads = CadModel::whereIn(
//         'article_code',
//         $articleNumbers
//     )
//         ->orderByDesc('version')
//         ->get()
//         ->groupBy(function ($item) {

    //             return (string)
//                 $item->article_code;
//         });

    //     /*
//     |--------------------------------------------------------------------------
//     | INSPECTION
//     |--------------------------------------------------------------------------
//     */

    //     $inspectionSchedules =
//         $inspectionModel::with([
//             'kategori',
//             'user',
//         ])
//             ->whereIn(
//                 'detail_po_id',
//                 $detailPoIds
//             )
//             ->get();

    //     /*
//     |--------------------------------------------------------------------------
//     | MAPPING
//     |--------------------------------------------------------------------------
//     */

    //     $pos->each(function ($po) use (
//         $bomMap,
//         $cads,
//         $inspectionSchedules,
//         $divisiQc,
//         $roundNumeric
//     ) {

    //         $po->details->each(function ($detail) use (
//             $po,
//             $bomMap,
//             $cads,
//             $inspectionSchedules,
//             $divisiQc,
//             $roundNumeric
//         ) {

    //             /*
//             |--------------------------------------------------------------------------
//             | ARTICLE
//             |--------------------------------------------------------------------------
//             */

    //             $article = (string) (
//                 $detail->detail['article_nr_']
//                 ?? ''
//             );

    //             /*
//             |--------------------------------------------------------------------------
//             | BOM
//             |--------------------------------------------------------------------------
//             */

    //             $detail->bom = (

    //                 $article &&
//                 isset($bomMap[$article])

    //             )
//                 ? $bomMap[$article]
//                 : null;

    //             /*
//             |--------------------------------------------------------------------------
//             | CAD
//             |--------------------------------------------------------------------------
//             */

    //             $detail->cad = (

    //                 $article &&
//                 isset($cads[$article])

    //             )
//                 ? $cads[$article]->first()
//                 : null;

    //             /*
//             |--------------------------------------------------------------------------
//             | INSPECTION
//             |--------------------------------------------------------------------------
//             */

    //             $detail->inspection_schedules =
//                 $inspectionSchedules
//                     ->where(
//                         'detail_po_id',
//                         $detail->id
//                     )
//                     ->values();

    //             /*
//             |--------------------------------------------------------------------------
//             | SPK TERKAIT
//             |--------------------------------------------------------------------------
//             */

    //             $relatedSpks = [];

    //             foreach ($po->spks as $spk) {

    //                 $spkData = $spk->data;

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | KATEGORI SPK
//                 |--------------------------------------------------------------------------
//                 */

    //                 $kategoriSpk = '';

    //                 if (is_array($spkData)) {

    //                     $kategoriSpk = strtoupper(
//                         $spkData['kategori']
//                         ?? ''
//                     );
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | FILTER DIVISI
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (
//                     !$this->matchDivisi(
//                         $divisiQc,
//                         $kategoriSpk
//                     )
//                 ) {
//                     continue;
//                 }

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | DECODE DATA JIKA STRING
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (is_string($spkData)) {

    //                     $spkData =
//                         json_decode(
//                             $spkData,
//                             true
//                         );

    //                     if (!is_array($spkData)) {
//                         $spkData = [];
//                     }
//                 }

    //                 $items =
//                     $spkData['items']
//                     ?? [];

    //                 foreach ($items as $item) {

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | DETAIL PO
//                     |--------------------------------------------------------------------------
//                     */

    //                     if (
//                         ($item['detail_po_id'] ?? null)
//                         !=
//                         $detail->id
//                     ) {
//                         continue;
//                     }

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | INSPECTION SPK
//                     |--------------------------------------------------------------------------
//                     */

    //                     $inspect =
//                         $inspectionSchedules
//                             ->where(
//                                 'detail_po_id',
//                                 $detail->id
//                             )
//                             ->where(
//                                 'spk_id',
//                                 $spk->id
//                             );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | PASSED
//                     |--------------------------------------------------------------------------
//                     */

    //                     $passed =
//                         $roundNumeric(
//                             $inspect->sum(
//                                 'passed'
//                             )
//                         );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | REJECTED
//                     |--------------------------------------------------------------------------
//                     */

    //                     $rejected =
//                         $roundNumeric(
//                             $inspect->sum(
//                                 'rejected'
//                             )
//                         );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | QTY
//                     |--------------------------------------------------------------------------
//                     */

    //                     $qty =
//                         $roundNumeric(
//                             $item['qty']
//                             ?? 0
//                         );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | HARGA
//                     |--------------------------------------------------------------------------
//                     */

    //                     $harga =
//                         $roundNumeric(
//                             $item['harga']
//                             ?? 0
//                         );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | TOTAL
//                     |--------------------------------------------------------------------------
//                     */

    //                     $total =
//                         $roundNumeric(
//                             $item['total']
//                             ?? 0
//                         );

    //                     /*
//                     |--------------------------------------------------------------------------
//                     | RELATED SPK
//                     |--------------------------------------------------------------------------
//                     */

    //                     $relatedSpks[] = [

    //                         'passed' =>
//                             $passed,

    //                         'rejected' =>
//                             $rejected,

    //                         'id' =>
//                             $spk->id,

    //                         'supplier' =>
//                             $spkData['sup']
//                             ?? null,

    //                         'kategori' =>
//                             $spkData['kategori']
//                             ?? null,

    //                         'status' =>
//                             $spkData['status']
//                             ?? null,

    //                         'no_spk' =>
//                             $spkData['no_spk']
//                             ?? null,

    //                         'tgl_terima' =>
//                             $spkData['tgl_terima']
//                             ?? null,

    //                         'tgl_selesai' =>
//                             $spkData['tgl_selesai']
//                             ?? null,

    //                         'material' =>
//                             $item['material']
//                             ?? '',

    //                         'qty' =>
//                             $qty,

    //                         'harga' =>
//                             $harga,

    //                         'total' =>
//                             $total,
//                     ];
//                 }
//             }

    //             /*
//             |--------------------------------------------------------------------------
//             | SIMPAN SPK KE DETAIL
//             |--------------------------------------------------------------------------
//             */

    //             $detail->spks =
//                 $relatedSpks;
//         });
//     });

    //     /*
//     |--------------------------------------------------------------------------
//     | NORMALISASI FINAL RESPONSE
//     |--------------------------------------------------------------------------
//     |
//     | Ini bagian penting.
//     |
//     | Sebelumnya kita hanya membulatkan relatedSpks.
//     | Sekarang seluruh nested $pos diproses.
//     |
//     |--------------------------------------------------------------------------
//     */

    //     $responseData =
//         $normalizeResponse($pos);

    //     /*
//     |--------------------------------------------------------------------------
//     | RETURN
//     |--------------------------------------------------------------------------
//     */

    //     return response()->json([

    //         'status' => 'success',

    //         'data' =>
//             $responseData,

    //     ]);
// }
// public function getPo()
// {
//     $user = auth()->user();

    //     // ============================================================
//     // USER / DIVISI
//     // ============================================================

    //     $user->loadMissing('karyawan.divisi');

    //     $requestDivisi = request('divisi');

    //     $divisiQc = strtoupper(
//         $requestDivisi
//         ?: ($user->karyawan?->divisi?->nama ?? '')
//     );


    //     // ============================================================
//     // HELPER ROUND NUMERIC
//     // ============================================================

    //     $roundNumeric = static function ($value) {

    //         if ($value === null || $value === '') {
//             return $value;
//         }

    //         if (is_int($value)) {
//             return $value;
//         }

    //         if (is_float($value)) {
//             return (int) round($value);
//         }

    //         if (!is_string($value)) {
//             return $value;
//         }

    //         $value = trim($value);

    //         if ($value === '') {
//             return $value;
//         }

    //         $normalized = $value;

    //         /*
//          * 56,5
//          */
//         if (
//             str_contains($normalized, ',') &&
//             !str_contains($normalized, '.')
//         ) {

    //             $normalized = str_replace(
//                 ',',
//                 '.',
//                 $normalized
//             );
//         }

    //         /*
//          * 1.250,5
//          * 1,250.5
//          */
//         elseif (
//             str_contains($normalized, ',') &&
//             str_contains($normalized, '.')
//         ) {

    //             $lastComma = strrpos(
//                 $normalized,
//                 ','
//             );

    //             $lastDot = strrpos(
//                 $normalized,
//                 '.'
//             );

    //             /*
//              * Indonesia:
//              * 1.250,5
//              */
//             if ($lastComma > $lastDot) {

    //                 $normalized = str_replace(
//                     '.',
//                     '',
//                     $normalized
//                 );

    //                 $normalized = str_replace(
//                     ',',
//                     '.',
//                     $normalized
//                 );
//             }

    //             /*
//              * English:
//              * 1,250.5
//              */
//             else {

    //                 $normalized = str_replace(
//                     ',',
//                     '',
//                     $normalized
//                 );
//             }
//         }

    //         if (is_numeric($normalized)) {

    //             return (int) round(
//                 (float) $normalized
//             );
//         }

    //         return $value;
//     };


    //     // ============================================================
//     // PROTECTED NUMERIC KEYS
//     // ============================================================

    //     $protectedKeys = [

    //         'id',
//         'po_id',
//         'spk_id',
//         'detail_po_id',
//         'user_id',
//         'kategori_id',
//         'batch',
//         'version',

    //         'kode',
//         'article_code',
//         'article_number',
//         'article_nr_',
//         'nw_code',

    //         'no_spk',
//         'no_po',
//         'nomor_invoice',

    //         'tanggal',
//         'tanggal_invoice',
//         'tanggal_inspect',
//         'tgl_terima',
//         'tgl_selesai',

    //         'status',
//         'kategori',
//         'supplier',
//         'material',
//         'nama',
//         'description',
//     ];


    //     // ============================================================
//     // NORMALIZE ARRAY
//     // ============================================================
//     //
//     // PENTING:
//     // Model hanya di-convert ke array SATU KALI di bagian akhir.
//     //
//     // ============================================================

    //     $normalizeArray = static function (
//         array $data
//     ) use (
//         &$normalizeArray,
//         $roundNumeric,
//         $protectedKeys
//     ) {

    //         foreach ($data as $key => $value) {

    //             // ----------------------------------------------------
//             // ARRAY
//             // ----------------------------------------------------

    //             if (is_array($value)) {

    //                 $data[$key] =
//                     $normalizeArray($value);

    //                 continue;
//             }


    //             // ----------------------------------------------------
//             // INTEGER / FLOAT
//             // ----------------------------------------------------

    //             if (
//                 is_int($value) ||
//                 is_float($value)
//             ) {

    //                 if (
//                     !in_array(
//                         $key,
//                         $protectedKeys,
//                         true
//                     )
//                 ) {

    //                     $data[$key] =
//                         $roundNumeric($value);
//                 }

    //                 continue;
//             }


    //             // ----------------------------------------------------
//             // STRING NUMERIC
//             // ----------------------------------------------------

    //             if (
//                 is_string($value) &&
//                 !in_array(
//                     $key,
//                     $protectedKeys,
//                     true
//                 )
//             ) {

    //                 $trimmed = trim($value);

    //                 if (
//                     $trimmed !== '' &&
//                     is_numeric(
//                         str_replace(
//                             ',',
//                             '.',
//                             $trimmed
//                         )
//                     )
//                 ) {

    //                     $data[$key] =
//                         $roundNumeric($value);
//                 }
//             }
//         }

    //         return $data;
//     };


    //     // ============================================================
//     // 1. GET PO + RELATION
//     // ============================================================

    //     $pos = Po::with([
//         'details',
//         'spks',
//     ])->get();


    //     // ============================================================
//     // 2. FLATTEN DETAILS SEKALI
//     // ============================================================

    //     $details = $pos
//         ->pluck('details')
//         ->flatten();


    //     $detailPoIds = $details
//         ->pluck('id')
//         ->filter()
//         ->unique()
//         ->values();


    //     // ============================================================
//     // 3. ARTICLE NUMBERS
//     // ============================================================

    //     $articleNumbers = $details
//         ->map(
//             static function ($detail) {

    //                 $detailData =
//                     is_array($detail->detail)
//                         ? $detail->detail
//                         : [];

    //                 $articleNr =
//                     $detailData['article_nr_']
//                     ?? null;

    //                 $nwCode =
//                     $detailData['nw_code']
//                     ?? null;

    //                 return $nwCode === null
//                     ? $articleNr
//                     : ($articleNr ?? $nwCode);
//             }
//         )
//         ->filter()
//         ->unique()
//         ->values();


    //     // ============================================================
//     // 4. BOM
//     // ============================================================

    //     $bomMap = collect();

    //     if ($articleNumbers->isNotEmpty()) {

    //         $bomMap = Bom::with([
//             'groups.items',
//         ])
//             ->whereIn(
//                 'article_number',
//                 $articleNumbers
//             )
//             ->get()
//             ->keyBy('article_number');
//     }


    //     // ============================================================
//     // 5. CAD
//     // ============================================================

    //     $cadMap = collect();

    //     if ($articleNumbers->isNotEmpty()) {

    //         $cadMap = CadModel::whereIn(
//             'article_code',
//             $articleNumbers
//         )
//             ->orderByDesc('version')
//             ->get()
//             ->groupBy(
//                 static function ($item) {

    //                     return (string)
//                         $item->article_code;
//                 }
//             )
//             ->map(
//                 static function ($items) {

    //                     return $items->first();
//                 }
//             );
//     }


    //     // ============================================================
//     // 6. INSPECTION
//     // ============================================================

    //     $inspectionByDetail = [];

    //     $inspectionByDetailSpk = [];


    //     if ($detailPoIds->isNotEmpty()) {

    //         $inspectionSchedules =
//             $inspectionModel::with([
//                 'kategori',
//                 'user',
//             ])
//                 ->whereIn(
//                     'detail_po_id',
//                     $detailPoIds
//                 )
//                 ->get();


    //         // --------------------------------------------------------
//         // INDEX INSPECTION
//         // --------------------------------------------------------

    //         foreach (
//             $inspectionSchedules
//             as $inspection
//         ) {

    //             $detailId =
//                 $inspection->detail_po_id;

    //             $spkId =
//                 $inspection->spk_id;


    //             // ================================================
//             // BY DETAIL
//             // ================================================

    //             if (
//                 !isset(
//                     $inspectionByDetail[
//                         $detailId
//                     ]
//                 )
//             ) {

    //                 $inspectionByDetail[
//                     $detailId
//                 ] = [];
//             }

    //             $inspectionByDetail[
//                 $detailId
//             ][] = $inspection;


    //             // ================================================
//             // BY DETAIL + SPK
//             // ================================================

    //             $key =
//                 $detailId
//                 . ':'
//                 . ($spkId ?? 'null');


    //             if (
//                 !isset(
//                     $inspectionByDetailSpk[
//                         $key
//                     ]
//                 )
//             ) {

    //                 $inspectionByDetailSpk[
//                     $key
//                 ] = [
//                     'passed'   => 0,
//                     'rejected' => 0,
//                 ];
//             }


    //             $inspectionByDetailSpk[
//                 $key
//             ]['passed'] +=
//                 (float) (
//                     $inspection->passed
//                     ?? 0
//                 );


    //             $inspectionByDetailSpk[
//                 $key
//             ]['rejected'] +=
//                 (float) (
//                     $inspection->rejected
//                     ?? 0
//                 );
//         }
//     }


    //     // ============================================================
//     // 7. PROCESS PO
//     // ============================================================

    //     $pos->each(
//         function ($po) use (
//             $bomMap,
//             $cadMap,
//             $inspectionByDetail,
//             $inspectionByDetailSpk,
//             $divisiQc,
//             $roundNumeric
//         ) {

    //             // ====================================================
//             // INDEX SPK BY DETAIL
//             // ====================================================

    //             $spksByDetail = [];


    //             foreach ($po->spks as $spk) {

    //                 // ------------------------------------------------
//                 // DECODE DATA HANYA SEKALI
//                 // ------------------------------------------------

    //                 $spkData = $spk->data;


    //                 if (is_string($spkData)) {

    //                     $decoded =
//                         json_decode(
//                             $spkData,
//                             true
//                         );

    //                     $spkData =
//                         is_array($decoded)
//                             ? $decoded
//                             : [];
//                 }


    //                 if (!is_array($spkData)) {
//                     $spkData = [];
//                 }


    //                 // ------------------------------------------------
//                 // KATEGORI
//                 // ------------------------------------------------

    //                 $kategoriSpk =
//                     strtoupper(
//                         $spkData['kategori']
//                         ?? ''
//                     );


    //                 // ------------------------------------------------
//                 // FILTER DIVISI
//                 // ------------------------------------------------

    //                 if (
//                     !$this->matchDivisi(
//                         $divisiQc,
//                         $kategoriSpk
//                     )
//                 ) {

    //                     continue;
//                 }


    //                 // ------------------------------------------------
//                 // ITEMS
//                 // ------------------------------------------------

    //                 $items =
//                     $spkData['items']
//                     ?? [];


    //                 if (!is_array($items)) {
//                     continue;
//                 }


    //                 // ------------------------------------------------
//                 // INDEX ITEM
//                 // ------------------------------------------------

    //                 foreach ($items as $item) {

    //                     $detailId =
//                         $item['detail_po_id']
//                         ?? null;


    //                     if (!$detailId) {
//                         continue;
//                     }


    //                     $spksByDetail[
//                         $detailId
//                     ][] = [

    //                         'spk' =>
//                             $spk,

    //                         'spkData' =>
//                             $spkData,

    //                         'item' =>
//                             $item,
//                     ];
//                 }
//             }


    //             // ====================================================
//             // PROCESS DETAILS
//             // ====================================================

    //             foreach (
//                 $po->details
//                 as $detail
//             ) {

    //                 // ================================================
//                 // ARTICLE
//                 // ================================================

    //                 $detailData =
//                     is_array($detail->detail)
//                         ? $detail->detail
//                         : [];


    //                 $article =
//                     (string) (
//                         $detailData['article_nr_']
//                         ?? ''
//                     );


    //                 // ================================================
//                 // BOM
//                 // ================================================

    //                 $detail->bom =
//                     (
//                         $article &&
//                         isset(
//                             $bomMap[$article]
//                         )
//                     )
//                         ? $bomMap[$article]
//                         : null;


    //                 // ================================================
//                 // CAD
//                 // ================================================

    //                 $detail->cad =
//                     (
//                         $article &&
//                         isset(
//                             $cadMap[$article]
//                         )
//                     )
//                         ? $cadMap[$article]
//                         : null;


    //                 // ================================================
//                 // INSPECTION SCHEDULE
//                 // ================================================

    //                 $detail->inspection_schedules =
//                     $inspectionByDetail[
//                         $detail->id
//                     ] ?? [];


    //                 // ================================================
//                 // RELATED SPK
//                 // ================================================

    //                 $relatedSpks = [];


    //                 $detailSpks =
//                     $spksByDetail[
//                         $detail->id
//                     ] ?? [];


    //                 foreach (
//                     $detailSpks
//                     as $entry
//                 ) {

    //                     $spk =
//                         $entry['spk'];

    //                     $spkData =
//                         $entry['spkData'];

    //                     $item =
//                         $entry['item'];


    //                     // ============================================
//                     // INSPECTION SUMMARY
//                     // ============================================

    //                     $inspectionKey =
//                         $detail->id
//                         . ':'
//                         . $spk->id;


    //                     $inspectionSummary =
//                         $inspectionByDetailSpk[
//                             $inspectionKey
//                         ]
//                         ?? [
//                             'passed'   => 0,
//                             'rejected' => 0,
//                         ];


    //                     $passed =
//                         $roundNumeric(
//                             $inspectionSummary[
//                                 'passed'
//                             ]
//                         );


    //                     $rejected =
//                         $roundNumeric(
//                             $inspectionSummary[
//                                 'rejected'
//                             ]
//                         );


    //                     // ============================================
//                     // QTY
//                     // ============================================

    //                     $qty =
//                         $roundNumeric(
//                             $item['qty']
//                             ?? 0
//                         );


    //                     // ============================================
//                     // HARGA
//                     // ============================================

    //                     $harga =
//                         $roundNumeric(
//                             $item['harga']
//                             ?? 0
//                         );


    //                     // ============================================
//                     // TOTAL
//                     // ============================================

    //                     $total =
//                         $roundNumeric(
//                             $item['total']
//                             ?? 0
//                         );


    //                     // ============================================
//                     // RELATED SPK
//                     // ============================================

    //                     $relatedSpks[] = [

    //                         'passed' =>
//                             $passed,

    //                         'rejected' =>
//                             $rejected,

    //                         'id' =>
//                             $spk->id,

    //                         'supplier' =>
//                             $spkData['sup']
//                             ?? null,

    //                         'kategori' =>
//                             $spkData['kategori']
//                             ?? null,

    //                         'status' =>
//                             $spkData['status']
//                             ?? null,

    //                         'no_spk' =>
//                             $spkData['no_spk']
//                             ?? null,

    //                         'tgl_terima' =>
//                             $spkData['tgl_terima']
//                             ?? null,

    //                         'tgl_selesai' =>
//                             $spkData['tgl_selesai']
//                             ?? null,

    //                         'material' =>
//                             $item['material']
//                             ?? '',

    //                         'qty' =>
//                             $qty,

    //                         'harga' =>
//                             $harga,

    //                         'total' =>
//                             $total,
//                     ];
//                 }


    //                 // ================================================
//                 // SIMPAN
//                 // ================================================

    //                 $detail->spks =
//                     $relatedSpks;
//             }
//         }
//     );


    //     // ============================================================
//     // 8. CONVERT ELOQUENT → ARRAY SEKALI
//     // ============================================================

    //     $responseData =
//         $pos->toArray();


    //     // ============================================================
//     // 9. NORMALIZE
//     // ============================================================

    //     $responseData =
//         $normalizeArray(
//             $responseData
//         );


    //     // ============================================================
//     // 10. RESPONSE
//     // ============================================================

    //     return response()->json([

    //         'status' =>
//             'success',

    //         'data' =>
//             $responseData,

    //     ]);
// }
// public function getPo()
// {
//     /*
//     |--------------------------------------------------------------------------
//     | START
//     |--------------------------------------------------------------------------
//     */

    //     $requestStart = microtime(true);


    //     /*
//     |--------------------------------------------------------------------------
//     | USER / DIVISI
//     |--------------------------------------------------------------------------
//     */

    //     $user = auth()->user();

    //     $user->loadMissing('karyawan.divisi');

    //     $requestDivisi = request('divisi');

    //     $divisiQc = strtoupper(
//         trim(
//             $requestDivisi
//                 ?: ($user->karyawan?->divisi?->nama ?? '')
//         )
//     );


    //     /*
//     |--------------------------------------------------------------------------
//     | ROUND NUMERIC
//     |--------------------------------------------------------------------------
//     */

    //     $roundNumeric = static function ($value) {

    //         if ($value === null || $value === '') {
//             return $value;
//         }

    //         if (is_int($value)) {
//             return $value;
//         }

    //         if (is_float($value)) {
//             return (int) round($value);
//         }

    //         if (!is_string($value)) {
//             return $value;
//         }

    //         $value = trim($value);

    //         if ($value === '') {
//             return $value;
//         }

    //         $normalized = $value;


    //         if (
//             str_contains($normalized, ',') &&
//             !str_contains($normalized, '.')
//         ) {

    //             $normalized = str_replace(
//                 ',',
//                 '.',
//                 $normalized
//             );
//         }

    //         elseif (
//             str_contains($normalized, ',') &&
//             str_contains($normalized, '.')
//         ) {

    //             $lastComma =
//                 strrpos(
//                     $normalized,
//                     ','
//                 );

    //             $lastDot =
//                 strrpos(
//                     $normalized,
//                     '.'
//                 );


    //             if ($lastComma > $lastDot) {

    //                 $normalized =
//                     str_replace(
//                         '.',
//                         '',
//                         $normalized
//                     );

    //                 $normalized =
//                     str_replace(
//                         ',',
//                         '.',
//                         $normalized
//                     );
//             }

    //             else {

    //                 $normalized =
//                     str_replace(
//                         ',',
//                         '',
//                         $normalized
//                     );
//             }
//         }


    //         if (is_numeric($normalized)) {

    //             return (int) round(
//                 (float) $normalized
//             );
//         }

    //         return $value;
//     };


    //     /*
//     |--------------------------------------------------------------------------
//     | PROTECTED KEYS
//     |--------------------------------------------------------------------------
//     */

    //     $protectedKeys = array_fill_keys([

    //         'id',
//         'po_id',
//         'spk_id',
//         'detail_po_id',
//         'user_id',
//         'kategori_id',
//         'batch',
//         'version',

    //         'kode',
//         'article_code',
//         'article_number',
//         'article_nr_',
//         'nw_code',

    //         'no_spk',
//         'no_po',
//         'nomor_invoice',

    //         'tanggal',
//         'tanggal_invoice',
//         'tanggal_inspect',
//         'tgl_terima',
//         'tgl_selesai',

    //         'status',
//         'kategori',
//         'supplier',
//         'material',
//         'nama',
//         'description',

    //     ], true);


    //     /*
//     |--------------------------------------------------------------------------
//     | NORMALIZE
//     |--------------------------------------------------------------------------
//     */

    //     $normalizeArray = static function (
//         array $data
//     ) use (
//         &$normalizeArray,
//         $roundNumeric,
//         $protectedKeys
//     ) {

    //         foreach ($data as $key => $value) {

    //             /*
//             |--------------------------------------------------------------------------
//             | ARRAY
//             |--------------------------------------------------------------------------
//             */

    //             if (is_array($value)) {

    //                 $data[$key] =
//                     $normalizeArray(
//                         $value
//                     );

    //                 continue;
//             }


    //             /*
//             |--------------------------------------------------------------------------
//             | INTEGER / FLOAT
//             |--------------------------------------------------------------------------
//             */

    //             if (
//                 is_int($value) ||
//                 is_float($value)
//             ) {

    //                 if (
//                     !isset(
//                         $protectedKeys[$key]
//                     )
//                 ) {

    //                     $data[$key] =
//                         $roundNumeric(
//                             $value
//                         );
//                 }

    //                 continue;
//             }


    //             /*
//             |--------------------------------------------------------------------------
//             | STRING NUMERIC
//             |--------------------------------------------------------------------------
//             */

    //             if (
//                 is_string($value) &&
//                 !isset(
//                     $protectedKeys[$key]
//                 )
//             ) {

    //                 $trimmed =
//                     trim($value);


    //                 if ($trimmed === '') {
//                     continue;
//                 }


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | Hanya proses string yang terlihat numeric
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (
//                     preg_match(
//                         '/^-?[0-9.,]+$/',
//                         $trimmed
//                     )
//                 ) {

    //                     $candidate =
//                         str_replace(
//                             ',',
//                             '.',
//                             $trimmed
//                         );


    //                     if (
//                         is_numeric(
//                             $candidate
//                         )
//                     ) {

    //                         $data[$key] =
//                             $roundNumeric(
//                                 $value
//                             );
//                     }
//                 }
//             }
//         }

    //         return $data;
//     };


    //     /*
//     |--------------------------------------------------------------------------
//     | 1. PO
//     |--------------------------------------------------------------------------
//     */

    //     $t1 = microtime(true);

    //     $pos = Po::with([
//         'details',
//         'spks',
//     ])->get();

    //     $queryTime =
//         microtime(true) - $t1;


    //     /*
//     |--------------------------------------------------------------------------
//     | 2. DETAILS
//     |--------------------------------------------------------------------------
//     */

    //     $details = $pos
//         ->pluck('details')
//         ->flatten();


    //     $detailPoIds = $details
//         ->pluck('id')
//         ->filter()
//         ->unique()
//         ->values();


    //     /*
//     |--------------------------------------------------------------------------
//     | 3. ARTICLE
//     |--------------------------------------------------------------------------
//     */

    //     $articleNumbers = $details
//         ->map(
//             static function ($detail) {

    //                 $detailData =
//                     is_array(
//                         $detail->detail
//                     )
//                         ? $detail->detail
//                         : [];


    //                 $articleNr =
//                     $detailData[
//                         'article_nr_'
//                     ]
//                     ?? null;


    //                 $nwCode =
//                     $detailData[
//                         'nw_code'
//                     ]
//                     ?? null;


    //                 return $nwCode === null
//                     ? $articleNr
//                     : (
//                         $articleNr
//                         ?? $nwCode
//                     );
//             }
//         )
//         ->filter()
//         ->unique()
//         ->values();


    //     /*
//     |--------------------------------------------------------------------------
//     | 4. BOM
//     |--------------------------------------------------------------------------
//     */

    //     $tBom = microtime(true);

    //     $bomMap = collect();

    //     if (
//         $articleNumbers->isNotEmpty()
//     ) {

    //         $bomMap =
//             Bom::with([
//                 'groups.items',
//             ])
//                 ->whereIn(
//                     'article_number',
//                     $articleNumbers
//                 )
//                 ->get()
//                 ->keyBy(
//                     'article_number'
//                 );
//     }

    //     $bomTime =
//         microtime(true) - $tBom;


    //     /*
//     |--------------------------------------------------------------------------
//     | 5. CAD
//     |--------------------------------------------------------------------------
//     */

    //     $tCad = microtime(true);

    //     $cadMap = collect();

    //     if (
//         $articleNumbers->isNotEmpty()
//     ) {

    //         $cadMap =
//             CadModel::whereIn(
//                 'article_code',
//                 $articleNumbers
//             )
//                 ->orderByDesc(
//                     'version'
//                 )
//                 ->get()
//                 ->groupBy(
//                     static function ($item) {

    //                         return (string)
//                             $item->article_code;
//                     }
//                 )
//                 ->map(
//                     static function ($items) {

    //                         return $items->first();
//                     }
//                 );
//     }

    //     $cadTime =
//         microtime(true) - $tCad;


    //     /*
//     |--------------------------------------------------------------------------
//     | 6. INSPECTION
//     |--------------------------------------------------------------------------
//     */

    //     $tInspection = microtime(true);

    //     $inspectionByDetail = [];

    //     $inspectionByDetailSpk = [];


    //     if (
//         $detailPoIds->isNotEmpty()
//     ) {

    //         $inspectionSchedules =
//             $inspectionModel::with([
//                 'kategori',
//                 'user',
//             ])
//                 ->whereIn(
//                     'detail_po_id',
//                     $detailPoIds
//                 )
//                 ->get();


    //         foreach (
//             $inspectionSchedules
//             as $inspection
//         ) {

    //             $detailId =
//                 $inspection->detail_po_id;

    //             $spkId =
//                 $inspection->spk_id;


    //             /*
//             |--------------------------------------------------------------------------
//             | Convert sekali
//             |--------------------------------------------------------------------------
//             */

    //             $inspectionData =
//                 $inspection->toArray();


    //             /*
//             |--------------------------------------------------------------------------
//             | BY DETAIL
//             |--------------------------------------------------------------------------
//             */

    //             $inspectionByDetail[
//                 $detailId
//             ][] =
//                 $inspectionData;


    //             /*
//             |--------------------------------------------------------------------------
//             | BY DETAIL + SPK
//             |--------------------------------------------------------------------------
//             */

    //             $key =
//                 $detailId .
//                 ':' .
//                 ($spkId ?? 'null');


    //             if (
//                 !isset(
//                     $inspectionByDetailSpk[
//                         $key
//                     ]
//                 )
//             ) {

    //                 $inspectionByDetailSpk[
//                     $key
//                 ] = [

    //                     'passed'   => 0,
//                     'rejected' => 0,

    //                 ];
//             }


    //             $inspectionByDetailSpk[
//                 $key
//             ]['passed'] +=
//                 (float) (
//                     $inspection->passed
//                     ?? 0
//                 );


    //             $inspectionByDetailSpk[
//                 $key
//             ]['rejected'] +=
//                 (float) (
//                     $inspection->rejected
//                     ?? 0
//                 );
//         }
//     }


    //     $inspectionTime =
//         microtime(true) -
//         $tInspection;


    //     /*
//     |--------------------------------------------------------------------------
//     | 7. PROCESS
//     |--------------------------------------------------------------------------
//     */

    //     $tProcess = microtime(true);


    //     $pos->each(
//         function ($po) use (
//             $bomMap,
//             $cadMap,
//             $inspectionByDetail,
//             $inspectionByDetailSpk,
//             $divisiQc,
//             $roundNumeric
//         ) {

    //             /*
//             |--------------------------------------------------------------------------
//             | SPK INDEX
//             |--------------------------------------------------------------------------
//             */

    //             $spksByDetail = [];

    //             $spkMeta = [];


    //             /*
//             |--------------------------------------------------------------------------
//             | SPK
//             |--------------------------------------------------------------------------
//             */

    //             foreach (
//                 $po->spks
//                 as $spk
//             ) {

    //                 /*
//                 |--------------------------------------------------------------------------
//                 | Decode JSON sekali
//                 |--------------------------------------------------------------------------
//                 */

    //                 $spkData =
//                     $spk->data;


    //                 if (
//                     is_string(
//                         $spkData
//                     )
//                 ) {

    //                     $decoded =
//                         json_decode(
//                             $spkData,
//                             true
//                         );


    //                     $spkData =
//                         is_array(
//                             $decoded
//                         )
//                             ? $decoded
//                             : [];
//                 }


    //                 if (
//                     !is_array(
//                         $spkData
//                     )
//                 ) {

    //                     $spkData = [];
//                 }


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | KATEGORI
//                 |--------------------------------------------------------------------------
//                 */

    //                 $kategoriSpk =
//                     strtoupper(
//                         trim(
//                             (string) (
//                                 $spkData[
//                                     'kategori'
//                                 ]
//                                 ?? ''
//                             )
//                         )
//                     );


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | FILTER DIVISI
//                 |--------------------------------------------------------------------------
//                 */

    //                 if (
//                     !$this->matchDivisi(
//                         $divisiQc,
//                         $kategoriSpk
//                     )
//                 ) {

    //                     continue;
//                 }


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | ITEMS
//                 |--------------------------------------------------------------------------
//                 */

    //                 $items =
//                     $spkData[
//                         'items'
//                     ]
//                     ?? [];


    //                 if (
//                     !is_array(
//                         $items
//                     )
//                 ) {

    //                     continue;
//                 }


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | SPK META
//                 |--------------------------------------------------------------------------
//                 */

    //                 $spkId =
//                     $spk->id;


    //                 $spkMeta[
//                     $spkId
//                 ] = [

    //                     'supplier' =>
//                         $spkData[
//                             'sup'
//                         ] ?? null,

    //                     'kategori' =>
//                         $spkData[
//                             'kategori'
//                         ] ?? null,

    //                     'status' =>
//                         $spkData[
//                             'status'
//                         ] ?? null,

    //                     'no_spk' =>
//                         $spkData[
//                             'no_spk'
//                         ] ?? null,

    //                     'tgl_terima' =>
//                         $spkData[
//                             'tgl_terima'
//                         ] ?? null,

    //                     'tgl_selesai' =>
//                         $spkData[
//                             'tgl_selesai'
//                         ] ?? null,

    //                 ];


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | INDEX ITEM
//                 |--------------------------------------------------------------------------
//                 */

    //                 foreach (
//                     $items
//                     as $item
//                 ) {

    //                     $detailId =
//                         $item[
//                             'detail_po_id'
//                         ]
//                         ?? null;


    //                     if (
//                         !$detailId
//                     ) {
//                         continue;
//                     }


    //                     $spksByDetail[
//                         $detailId
//                     ][] = [

    //                         'spk' =>
//                             $spk,

    //                         'spk_id' =>
//                             $spkId,

    //                         'item' =>
//                             $item,

    //                     ];
//                 }
//             }


    //             /*
//             |--------------------------------------------------------------------------
//             | DETAILS
//             |--------------------------------------------------------------------------
//             */

    //             foreach (
//                 $po->details
//                 as $detail
//             ) {

    //                 $detailData =
//                     is_array(
//                         $detail->detail
//                     )
//                         ? $detail->detail
//                         : [];


    //                 $article =
//                     (string) (
//                         $detailData[
//                             'article_nr_'
//                         ]
//                         ?? ''
//                     );


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | BOM
//                 |--------------------------------------------------------------------------
//                 */

    //                 $detail->bom =
//                     (
//                         $article !== '' &&
//                         isset(
//                             $bomMap[
//                                 $article
//                             ]
//                         )
//                     )
//                         ? $bomMap[
//                             $article
//                         ]
//                         : null;


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | CAD
//                 |--------------------------------------------------------------------------
//                 */

    //                 $detail->cad =
//                     (
//                         $article !== '' &&
//                         isset(
//                             $cadMap[
//                                 $article
//                             ]
//                         )
//                     )
//                         ? $cadMap[
//                             $article
//                         ]
//                         : null;


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | INSPECTION
//                 |--------------------------------------------------------------------------
//                 */

    //                 $detail->inspection_schedules =
//                     $inspectionByDetail[
//                         $detail->id
//                     ]
//                     ?? [];


    //                 /*
//                 |--------------------------------------------------------------------------
//                 | RELATED SPK
//                 |--------------------------------------------------------------------------
//                 */

    //                 $relatedSpks = [];


    //                 $detailSpks =
//                     $spksByDetail[
//                         $detail->id
//                     ]
//                     ?? [];


    //                 foreach (
//                     $detailSpks
//                     as $entry
//                 ) {

    //                     $spkId =
//                         $entry[
//                             'spk_id'
//                         ];


    //                     $item =
//                         $entry[
//                             'item'
//                         ];


    //                     $meta =
//                         $spkMeta[
//                             $spkId
//                         ]
//                         ?? [];


    //                     /*
//                     |--------------------------------------------------------------------------
//                     | INSPECTION SUMMARY
//                     |--------------------------------------------------------------------------
//                     */

    //                     $inspectionKey =
//                         $detail->id .
//                         ':' .
//                         $spkId;


    //                     $summary =
//                         $inspectionByDetailSpk[
//                             $inspectionKey
//                         ]
//                         ?? [

    //                             'passed'   => 0,
//                             'rejected' => 0,

    //                         ];


    //                     /*
//                     |--------------------------------------------------------------------------
//                     | DATA
//                     |--------------------------------------------------------------------------
//                     */

    //                     $relatedSpks[] = [

    //                         'passed' =>
//                             $roundNumeric(
//                                 $summary[
//                                     'passed'
//                                 ]
//                             ),

    //                         'rejected' =>
//                             $roundNumeric(
//                                 $summary[
//                                     'rejected'
//                                 ]
//                             ),

    //                         'id' =>
//                             $spkId,

    //                         'supplier' =>
//                             $meta[
//                                 'supplier'
//                             ] ?? null,

    //                         'kategori' =>
//                             $meta[
//                                 'kategori'
//                             ] ?? null,

    //                         'status' =>
//                             $meta[
//                                 'status'
//                             ] ?? null,

    //                         'no_spk' =>
//                             $meta[
//                                 'no_spk'
//                             ] ?? null,

    //                         'tgl_terima' =>
//                             $meta[
//                                 'tgl_terima'
//                             ] ?? null,

    //                         'tgl_selesai' =>
//                             $meta[
//                                 'tgl_selesai'
//                             ] ?? null,

    //                         'material' =>
//                             $item[
//                                 'material'
//                             ] ?? '',

    //                         'qty' =>
//                             $roundNumeric(
//                                 $item[
//                                     'qty'
//                                 ] ?? 0
//                             ),

    //                         'harga' =>
//                             $roundNumeric(
//                                 $item[
//                                     'harga'
//                                 ] ?? 0
//                             ),

    //                         'total' =>
//                             $roundNumeric(
//                                 $item[
//                                     'total'
//                                 ] ?? 0
//                             ),

    //                     ];
//                 }


    //                 $detail->spks =
//                     $relatedSpks;
//             }
//         }
//     );


    //     $processTime =
//         microtime(true) -
//         $tProcess;


    //     /*
//     |--------------------------------------------------------------------------
//     | 8. ELOQUENT → ARRAY
//     |--------------------------------------------------------------------------
//     */

    //     $tArray = microtime(true);

    //     $responseData =
//         $pos->toArray();

    //     $toArrayTime =
//         microtime(true) -
//         $tArray;


    //     /*
//     |--------------------------------------------------------------------------
//     | 9. NORMALIZE
//     |--------------------------------------------------------------------------
//     */

    //     $tNormalize = microtime(true);

    //     $responseData =
//         $normalizeArray(
//             $responseData
//         );

    //     $normalizeTime =
//         microtime(true) -
//         $tNormalize;


    //     /*
//     |--------------------------------------------------------------------------
//     | 10. PREPARE PAYLOAD
//     |--------------------------------------------------------------------------
//     */

    //     $payload = [

    //         'status' =>
//             'success',

    //         'data' =>
//             $responseData,

    //     ];


    //     /*
//     |--------------------------------------------------------------------------
//     | 11. JSON ENCODE
//     |--------------------------------------------------------------------------
//     |
//     | INI YANG SEBELUMNYA BELUM KITA UKUR.
//     |
//     */

    //     $tJson = microtime(true);


    //     $json = json_encode(
//         $payload,
//         JSON_UNESCAPED_UNICODE |
//         JSON_UNESCAPED_SLASHES
//     );


    //     $jsonTime =
//         microtime(true) -
//         $tJson;


    //     /*
//     |--------------------------------------------------------------------------
//     | JSON ERROR CHECK
//     |--------------------------------------------------------------------------
//     */

    //     if (
//         $json === false
//     ) {

    //         return response()->json([

    //             'status' =>
//                 'error',

    //             'message' =>
//                 json_last_error_msg(),

    //         ], 500);
//     }


    //     /*
//     |--------------------------------------------------------------------------
//     | TOTAL SERVER PROCESS
//     |--------------------------------------------------------------------------
//     */

    //     $totalTime =
//         microtime(true) -
//         $requestStart;


    //     /*
//     |--------------------------------------------------------------------------
//     | RESPONSE
//     |--------------------------------------------------------------------------
//     |
//     | JSON SUDAH DI-ENCODE.
//     |
//     | Jadi Laravel tidak perlu encode payload sekali lagi.
//     |
//     */

    //     return response(
//         $json,
//         200
//     )
//         ->header(
//             'Content-Type',
//             'application/json; charset=UTF-8'
//         )
//         ->header(
//             'Cache-Control',
//             'no-cache, no-store, must-revalidate'
//         )
//         ->header(
//             'Pragma',
//             'no-cache'
//         )
//         ->header(
//             'Expires',
//             '0'
//         );
// }
    public function getPo()
    {
        $inspectionModel = $this->inspectionScheduleModel();

        /*
        |--------------------------------------------------------------------------
        | START TIMER
        |--------------------------------------------------------------------------
        */

        $requestStart = microtime(true);


        /*
        |--------------------------------------------------------------------------
        | USER / DIVISI
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        $user->loadMissing('karyawan.divisi');

        $requestDivisi = request('divisi');

        $divisiQc = strtoupper(
            trim(
                $requestDivisi
                ?: ($user->karyawan?->divisi?->nama ?? '')
            )
        );


        /*
        |--------------------------------------------------------------------------
        | HELPER ROUND NUMERIC
        |--------------------------------------------------------------------------
        */

        $roundNumeric = static function ($value) {

            if ($value === null || $value === '') {
                return $value;
            }

            if (is_int($value)) {
                return $value;
            }

            if (is_float($value)) {
                return (int) round($value);
            }

            if (!is_string($value)) {
                return $value;
            }

            $value = trim($value);

            if ($value === '') {
                return $value;
            }

            $normalized = $value;


            /*
            |--------------------------------------------------------------------------
            | 56,5
            |--------------------------------------------------------------------------
            */

            if (
                str_contains($normalized, ',') &&
                !str_contains($normalized, '.')
            ) {

                $normalized = str_replace(
                    ',',
                    '.',
                    $normalized
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 1.250,5
            | 1,250.5
            |--------------------------------------------------------------------------
            */ elseif (
                str_contains($normalized, ',') &&
                str_contains($normalized, '.')
            ) {

                $lastComma =
                    strrpos(
                        $normalized,
                        ','
                    );

                $lastDot =
                    strrpos(
                        $normalized,
                        '.'
                    );


                /*
                |--------------------------------------------------------------------------
                | Indonesia
                |--------------------------------------------------------------------------
                */

                if ($lastComma > $lastDot) {

                    $normalized =
                        str_replace(
                            '.',
                            '',
                            $normalized
                        );

                    $normalized =
                        str_replace(
                            ',',
                            '.',
                            $normalized
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | English
                |--------------------------------------------------------------------------
                */ else {

                    $normalized =
                        str_replace(
                            ',',
                            '',
                            $normalized
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ROUND
            |--------------------------------------------------------------------------
            */

            if (is_numeric($normalized)) {

                return (int) round(
                    (float) $normalized
                );
            }

            return $value;
        };


        /*
        |--------------------------------------------------------------------------
        | PROTECTED NUMERIC KEYS
        |--------------------------------------------------------------------------
        |
        | Associative lookup lebih cepat daripada in_array()
        |--------------------------------------------------------------------------
        */

        $protectedKeys = array_fill_keys([

            'id',
            'po_id',
            'spk_id',
            'detail_po_id',
            'user_id',
            'kategori_id',
            'batch',
            'version',

            'kode',
            'article_code',
            'article_number',
            'article_nr_',
            'nw_code',

            'no_spk',
            'no_po',
            'nomor_invoice',

            'tanggal',
            'tanggal_invoice',
            'tanggal_inspect',
            'tgl_terima',
            'tgl_selesai',

            'status',
            'kategori',
            'supplier',
            'material',
            'nama',
            'description',

        ], true);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE ARRAY
        |--------------------------------------------------------------------------
        */

        $normalizeArray = static function (array $data) use (&$normalizeArray, $roundNumeric, $protectedKeys) {

            foreach ($data as $key => $value) {

                /*
                |--------------------------------------------------------------------------
                | ARRAY
                |--------------------------------------------------------------------------
                */

                if (is_array($value)) {

                    $data[$key] =
                        $normalizeArray(
                            $value
                        );

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | INTEGER / FLOAT
                |--------------------------------------------------------------------------
                */

                if (
                    is_int($value) ||
                    is_float($value)
                ) {

                    if (
                        !isset(
                        $protectedKeys[$key]
                    )
                    ) {

                        $data[$key] =
                            $roundNumeric(
                                $value
                            );
                    }

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | STRING NUMERIC
                |--------------------------------------------------------------------------
                */

                if (
                    is_string($value) &&
                    !isset(
                    $protectedKeys[$key]
                )
                ) {

                    $trimmed =
                        trim($value);


                    if ($trimmed === '') {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Hindari is_numeric() terhadap string biasa
                    |--------------------------------------------------------------------------
                    */

                    if (
                        preg_match(
                            '/^-?[0-9.,]+$/',
                            $trimmed
                        )
                    ) {

                        $candidate =
                            str_replace(
                                ',',
                                '.',
                                $trimmed
                            );


                        if (
                            is_numeric(
                                $candidate
                            )
                        ) {

                            $data[$key] =
                                $roundNumeric(
                                    $value
                                );
                        }
                    }
                }
            }

            return $data;
        };


        /*
        |--------------------------------------------------------------------------
        | 1. GET PO + RELATION
        |--------------------------------------------------------------------------
        */

        $tQuery = microtime(true);

        $pos = Po::with([
            'details',
            'spks',
        ])->get();

        $queryTime =
            microtime(true) - $tQuery;


        /*
        |--------------------------------------------------------------------------
        | 2. FLATTEN DETAILS SEKALI
        |--------------------------------------------------------------------------
        */

        $details = $pos
            ->pluck('details')
            ->flatten();


        /*
        |--------------------------------------------------------------------------
        | 3. DETAIL PO IDS
        |--------------------------------------------------------------------------
        */

        $detailPoIds = $details
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 4. ARTICLE NUMBERS
        |--------------------------------------------------------------------------
        */

        $articleNumbers = $details
            ->map(
                static function ($detail) {

                    $detailData =
                        is_array($detail->detail)
                        ? $detail->detail
                        : [];


                    $articleNr =
                        $detailData[
                            'article_nr_'
                        ]
                        ?? null;


                    $nwCode =
                        $detailData[
                            'nw_code'
                        ]
                        ?? null;


                    return $nwCode === null
                        ? $articleNr
                        : (
                            $articleNr
                            ?? $nwCode
                        );
                }
            )
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 5. BOM
        |--------------------------------------------------------------------------
        */

        $tBom = microtime(true);

        $bomMap = collect();

        if (
            $articleNumbers->isNotEmpty()
        ) {

            $bomMap =
                Bom::with([
                    'groups.items',
                ])
                    ->whereIn(
                        'article_number',
                        $articleNumbers
                    )
                    ->get()
                    ->keyBy(
                        'article_number'
                    );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Convert BOM ke array SEKALI.
            |
            | Struktur tetap sama.
            |
            |--------------------------------------------------------------------------
            */

            $bomMap =
                $bomMap->map(
                    static function ($bom) {

                        return $bom->toArray();
                    }
                );
        }

        $bomTime =
            microtime(true) - $tBom;


        /*
        |--------------------------------------------------------------------------
        | 6. CAD
        |--------------------------------------------------------------------------
        */

        $tCad = microtime(true);

        $cadMap = collect();

        if (
            $articleNumbers->isNotEmpty()
        ) {

            $cadMap =
                CadModel::whereIn(
                    'article_code',
                    $articleNumbers
                )
                    ->orderByDesc(
                        'version'
                    )
                    ->get()
                    ->groupBy(
                        static function ($item) {

                            return (string) 
                                $item->article_code;
                        }
                    )
                    ->map(
                        static function ($items) {

                            /*
                            |--------------------------------------------------------------------------
                            | Karena sudah ORDER BY version DESC,
                            | first = version terbaru.
                            |--------------------------------------------------------------------------
                            */

                            $item =
                                $items->first();


                            return $item
                                ? $item->toArray()
                                : null;
                        }
                    );
        }

        $cadTime =
            microtime(true) - $tCad;


        /*
        |--------------------------------------------------------------------------
        | 7. INSPECTION
        |--------------------------------------------------------------------------
        */

        $tInspection = microtime(true);

        $inspectionByDetail = [];

        $inspectionByDetailSpk = [];


        if (
            $detailPoIds->isNotEmpty()
        ) {

            $inspectionSchedules =
                $inspectionModel::with([
                    'kategori',
                    'user',
                ])
                    ->whereIn(
                        'detail_po_id',
                        $detailPoIds
                    )
                    ->get();


            /*
            |--------------------------------------------------------------------------
            | INDEX INSPECTION
            |--------------------------------------------------------------------------
            */

            foreach (
                $inspectionSchedules
                as $inspection
            ) {

                $detailId =
                    $inspection->detail_po_id;

                $spkId =
                    $inspection->spk_id;


                /*
                |--------------------------------------------------------------------------
                | Convert Eloquent → Array SEKALI
                |--------------------------------------------------------------------------
                */

                $inspectionData =
                    $inspection->toArray();


                /*
                |--------------------------------------------------------------------------
                | BY DETAIL
                |--------------------------------------------------------------------------
                */

                if (
                    !isset(
                    $inspectionByDetail[
                        $detailId
                    ]
                )
                ) {

                    $inspectionByDetail[
                        $detailId
                    ] = [];
                }


                $inspectionByDetail[
                    $detailId
                ][] =
                    $inspectionData;


                /*
                |--------------------------------------------------------------------------
                | BY DETAIL + SPK
                |--------------------------------------------------------------------------
                */

                $key =
                    $detailId .
                    ':' .
                    ($spkId ?? 'null');


                if (
                    !isset(
                    $inspectionByDetailSpk[
                        $key
                    ]
                )
                ) {

                    $inspectionByDetailSpk[
                        $key
                    ] = [

                        'passed' => 0,
                        'rejected' => 0,

                    ];
                }


                $inspectionByDetailSpk[
                    $key
                ]['passed'] +=
                    (float) (
                        $inspection->passed
                        ?? 0
                    );


                $inspectionByDetailSpk[
                    $key
                ]['rejected'] +=
                    (float) (
                        $inspection->rejected
                        ?? 0
                    );
            }
        }


        $inspectionTime =
            microtime(true) -
            $tInspection;


        /*
        |--------------------------------------------------------------------------
        | 8. PROCESS PO
        |--------------------------------------------------------------------------
        */

        $tProcess = microtime(true);


        $pos->each(
            function ($po) use ($bomMap, $cadMap, $inspectionByDetail, $inspectionByDetailSpk, $divisiQc, $roundNumeric) {

                /*
                |--------------------------------------------------------------------------
                | INDEX SPK BY DETAIL
                |--------------------------------------------------------------------------
                */

                $spksByDetail = [];

                /*
                |--------------------------------------------------------------------------
                | META SPK
                |--------------------------------------------------------------------------
                */

                $spkMeta = [];


                /*
                |--------------------------------------------------------------------------
                | PROCESS SPK
                |--------------------------------------------------------------------------
                */

                foreach (
                    $po->spks
                    as $spk
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | DATA
                    |--------------------------------------------------------------------------
                    */

                    $spkData =
                        $spk->data;


                    if (
                        is_string(
                            $spkData
                        )
                    ) {

                        $decoded =
                            json_decode(
                                $spkData,
                                true
                            );


                        $spkData =
                            is_array(
                                $decoded
                            )
                            ? $decoded
                            : [];
                    }


                    if (
                        !is_array(
                            $spkData
                        )
                    ) {

                        $spkData = [];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | KATEGORI
                    |--------------------------------------------------------------------------
                    */

                    $kategoriSpk =
                        strtoupper(
                            trim(
                                (string) (
                                    $spkData[
                                        'kategori'
                                    ]
                                    ?? ''
                                )
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | FILTER DIVISI
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$this->matchDivisi(
                            $divisiQc,
                            $kategoriSpk
                        )
                    ) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ITEMS
                    |--------------------------------------------------------------------------
                    */

                    $items =
                        $spkData[
                            'items'
                        ]
                        ?? [];


                    if (
                        !is_array(
                            $items
                        )
                    ) {

                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SPK ID
                    |--------------------------------------------------------------------------
                    */

                    $spkId =
                        $spk->id;


                    /*
                    |--------------------------------------------------------------------------
                    | CACHE META
                    |--------------------------------------------------------------------------
                    */

                    $spkMeta[
                        $spkId
                    ] = [

                        'supplier' =>
                            $spkData[
                                'sup'
                            ] ?? null,

                        'kategori' =>
                            $spkData[
                                'kategori'
                            ] ?? null,

                        'status' =>
                            $spkData[
                                'status'
                            ] ?? null,

                        'no_spk' =>
                            $spkData[
                                'no_spk'
                            ] ?? null,

                        'tgl_terima' =>
                            $spkData[
                                'tgl_terima'
                            ] ?? null,

                        'tgl_selesai' =>
                            $spkData[
                                'tgl_selesai'
                            ] ?? null,

                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | INDEX ITEM
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $items
                        as $item
                    ) {

                        $detailId =
                            $item[
                                'detail_po_id'
                            ]
                            ?? null;


                        if (
                            !$detailId
                        ) {

                            continue;
                        }


                        $spksByDetail[
                            $detailId
                        ][] = [

                            'spk' =>
                                $spk,

                            'spk_id' =>
                                $spkId,

                            'item' =>
                                $item,

                        ];
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | PROCESS DETAILS
                |--------------------------------------------------------------------------
                */

                foreach (
                    $po->details
                    as $detail
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL DATA
                    |--------------------------------------------------------------------------
                    */

                    $detailData =
                        is_array(
                            $detail->detail
                        )
                        ? $detail->detail
                        : [];


                    /*
                    |--------------------------------------------------------------------------
                    | ARTICLE
                    |--------------------------------------------------------------------------
                    */

                    $article =
                        (string) (
                            $detailData[
                                'article_nr_'
                            ]
                            ?? ''
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | BOM
                    |--------------------------------------------------------------------------
                    */

                    $detail->bom =
                        (
                            $article !== '' &&
                            isset(
                            $bomMap[
                                $article
                            ]
                        )
                        )
                        ? $bomMap[
                            $article
                        ]
                        : null;


                    /*
                    |--------------------------------------------------------------------------
                    | CAD
                    |--------------------------------------------------------------------------
                    */

                    $detail->cad =
                        (
                            $article !== '' &&
                            isset(
                            $cadMap[
                                $article
                            ]
                        )
                        )
                        ? $cadMap[
                            $article
                        ]
                        : null;


                    /*
                    |--------------------------------------------------------------------------
                    | INSPECTION
                    |--------------------------------------------------------------------------
                    */

                    $detail->inspection_schedules =
                        $inspectionByDetail[
                            $detail->id
                        ]
                        ?? [];


                    /*
                    |--------------------------------------------------------------------------
                    | RELATED SPK
                    |--------------------------------------------------------------------------
                    */

                    $relatedSpks = [];


                    $detailSpks =
                        $spksByDetail[
                            $detail->id
                        ]
                        ?? [];


                    /*
                    |--------------------------------------------------------------------------
                    | RELATED SPK LOOP
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $detailSpks
                        as $entry
                    ) {

                        $spkId =
                            $entry[
                                'spk_id'
                            ];


                        $item =
                            $entry[
                                'item'
                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | META
                        |--------------------------------------------------------------------------
                        */

                        $meta =
                            $spkMeta[
                                $spkId
                            ]
                            ?? [];


                        /*
                        |--------------------------------------------------------------------------
                        | INSPECTION KEY
                        |--------------------------------------------------------------------------
                        */

                        $inspectionKey =
                            $detail->id .
                            ':' .
                            $spkId;


                        /*
                        |--------------------------------------------------------------------------
                        | INSPECTION SUMMARY
                        |--------------------------------------------------------------------------
                        */

                        $inspectionSummary =
                            $inspectionByDetailSpk[
                                $inspectionKey
                            ]
                            ?? [

                                'passed' => 0,
                                'rejected' => 0,

                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | PASSED
                        |--------------------------------------------------------------------------
                        */

                        $passed =
                            $roundNumeric(
                                $inspectionSummary[
                                    'passed'
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | REJECTED
                        |--------------------------------------------------------------------------
                        */

                        $rejected =
                            $roundNumeric(
                                $inspectionSummary[
                                    'rejected'
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | QTY
                        |--------------------------------------------------------------------------
                        */

                        $qty =
                            $roundNumeric(
                                $item[
                                    'qty'
                                ]
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | HARGA
                        |--------------------------------------------------------------------------
                        */

                        $harga =
                            $roundNumeric(
                                $item[
                                    'harga'
                                ]
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL
                        |--------------------------------------------------------------------------
                        */

                        $total =
                            $roundNumeric(
                                $item[
                                    'total'
                                ]
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | RELATED SPK
                        |--------------------------------------------------------------------------
                        */

                        $relatedSpks[] = [

                            'passed' =>
                                $passed,

                            'rejected' =>
                                $rejected,

                            'id' =>
                                $spkId,

                            'supplier' =>
                                $meta[
                                    'supplier'
                                ]
                                ?? null,

                            'kategori' =>
                                $meta[
                                    'kategori'
                                ]
                                ?? null,

                            'status' =>
                                $meta[
                                    'status'
                                ]
                                ?? null,

                            'no_spk' =>
                                $meta[
                                    'no_spk'
                                ]
                                ?? null,

                            'tgl_terima' =>
                                $meta[
                                    'tgl_terima'
                                ]
                                ?? null,

                            'tgl_selesai' =>
                                $meta[
                                    'tgl_selesai'
                                ]
                                ?? null,

                            'material' =>
                                $item[
                                    'material'
                                ]
                                ?? '',

                            'qty' =>
                                $qty,

                            'harga' =>
                                $harga,

                            'total' =>
                                $total,

                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SET RELATED SPK
                    |--------------------------------------------------------------------------
                    */

                    $detail->spks =
                        $relatedSpks;
                }
            }
        );


        $processTime =
            microtime(true) -
            $tProcess;


        /*
        |--------------------------------------------------------------------------
        | 9. ELOQUENT → ARRAY
        |--------------------------------------------------------------------------
        |
        | Tetap menggunakan toArray()
        | agar field bawaan model + struktur relation tidak berubah.
        |
        */

        $tArray = microtime(true);

        $responseData =
            $pos->toArray();

        $toArrayTime =
            microtime(true) -
            $tArray;


        /*
        |--------------------------------------------------------------------------
        | 10. NORMALIZE
        |--------------------------------------------------------------------------
        */

        $tNormalize = microtime(true);

        $responseData =
            $normalizeArray(
                $responseData
            );

        $normalizeTime =
            microtime(true) -
            $tNormalize;


        /*
        |--------------------------------------------------------------------------
        | 11. JSON ENCODE
        |--------------------------------------------------------------------------
        |
        | Ukur JSON encoding secara terpisah.
        |--------------------------------------------------------------------------
        */

        $tJson = microtime(true);

        $json =
            json_encode(
                [
                    'status' =>
                        'success',

                    'data' =>
                        $responseData,
                ],
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );

        $jsonTime =
            microtime(true) -
            $tJson;


        /*
        |--------------------------------------------------------------------------
        | JSON ERROR
        |--------------------------------------------------------------------------
        */

        if (
            $json === false
        ) {

            return response()->json([

                'status' =>
                    'error',

                'message' =>
                    json_last_error_msg(),

            ], 500);
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL SERVER TIME
        |--------------------------------------------------------------------------
        */

        $totalTime =
            microtime(true) -
            $requestStart;


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL LOG
        |--------------------------------------------------------------------------
        |
        | Tidak masuk response API.
        |
        | Bisa dihapus setelah selesai testing.
        |--------------------------------------------------------------------------
        */

        Log::info(
            'QC PO API PERFORMANCE',
            [

                'divisi' =>
                    $divisiQc,

                'query' =>
                    round(
                        $queryTime,
                        4
                    ),

                'bom' =>
                    round(
                        $bomTime,
                        4
                    ),

                'cad' =>
                    round(
                        $cadTime,
                        4
                    ),

                'inspection' =>
                    round(
                        $inspectionTime,
                        4
                    ),

                'process' =>
                    round(
                        $processTime,
                        4
                    ),

                'to_array' =>
                    round(
                        $toArrayTime,
                        4
                    ),

                'normalize' =>
                    round(
                        $normalizeTime,
                        4
                    ),

                'json' =>
                    round(
                        $jsonTime,
                        4
                    ),

                'total' =>
                    round(
                        $totalTime,
                        4
                    ),

                'json_bytes' =>
                    strlen($json),

                'po_count' =>
                    $pos->count(),

                'detail_count' =>
                    $details->count(),

                'article_count' =>
                    $articleNumbers->count(),

                'detail_po_id_count' =>
                    $detailPoIds->count(),

            ]
        );


        /*
        |--------------------------------------------------------------------------
        | 12. RESPONSE
        |--------------------------------------------------------------------------
        |
        | JSON sudah di-encode sehingga Laravel tidak perlu
        | melakukan json_encode() lagi pada payload.
        |--------------------------------------------------------------------------
        */

        return response(
            $json,
            200
        )
            ->header(
                'Content-Type',
                'application/json; charset=UTF-8'
            )
            ->header(
                'Cache-Control',
                'no-cache, no-store, must-revalidate'
            )
            ->header(
                'Pragma',
                'no-cache'
            )
            ->header(
                'Expires',
                '0'
            );
    }
    public function detailPoReports($detailPoId)
    {
        $inspectionModel = $this->inspectionScheduleModel();

        $inspectSchedules = $inspectionModel::with([
            'user',
            'kategori',
            'qcReports',
            'reportPhotos',
        ])
            ->where('detail_po_id', $detailPoId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'inspect_schedules' => $inspectSchedules,
        ]);
    }
    // o
    private function matchDivisi(
        string $divisi,
        string $kategori
    ): bool {

        $divisi = strtoupper($divisi);
        $kategori = strtoupper($kategori);

        $mapping = [

            'QC RANGKA' => [
                'RANGKA',
                'LASIO',
                'BASE SWIVEL',
                'PLAT BESI',
                'ACCESSORIES',
                'AKSESORIES',
                'AKESOSORIS',
                'TRIPLEK',
                'POWDER COATING'
            ],

            'QC ANYAM' => [
                'ANYAM',
                'DEKOR',
                'ECENG',
                'BANANA',
                'SONGKET',
                'WEBBING',
                'SYNTETIC',
                'ACCESSORIES',
                'AKSESORIES',
                'AKESOSORIS',
            ],

            'QC UNFINISH' => [
                'BASKET',
                'PAKET',
                'SUB BORONGAN',
            ],

        ];

        foreach ($mapping[$divisi] ?? [] as $keyword) {

            if (str_contains($kategori, $keyword)) {

                return true;

            }

        }

        return false;
    }

    public function laporanQc(Request $request)
    {
        $inspectionModel = $this->inspectionScheduleModel();

        $inspectionModel = $this->inspectionScheduleModel();

        $query = $inspectionModel::with([
            'po',
            'detailPo',
            'user.karyawan',
            'kategori',
            'spk',
            'qcReports',
            'reportPhotos',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filter User
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        // tambahan
        /*
    |--------------------------------------------------------------------------
    | Filter Detail PO
    |--------------------------------------------------------------------------
    */

        if ($request->filled('detail_po_id')) {
            $query->where('detail_po_id', $request->detail_po_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kategori Monitoring
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kategori')) {

            $kategori = strtolower($request->kategori);

            $mapping = [
                'rangka' => [
                    'RANGKA',
                    'RANGKA BESI',
                    'RANGKA KAYU',
                    'RANGKA ROTAN',
                    'RANGKA ALUMUNIUN',
                    'RANGKA TRIPLEK',
                    'TRIPLEK',

                    'PLAT BESI',
                ],

                'anyam' => [
                    'ANYAM',
                    'ANYAM SINTETIS',
                    'ANYAM KARAKTER',
                    'RANGKA + ANYAM',
                    'ANYAM + DEKOR',
                    'BASKET JOGJA',
                    'BASKE JOGJA',
                    'BASKET LOMBOKAN',
                    'BASKET TASIK',
                ],

                'unfinish' => [

                ],
            ];

            if (isset($mapping[$kategori])) {

                $query->whereHas('kategori', function ($q) use ($mapping, $kategori) {

                    $q->whereIn('kategori', $mapping[$kategori]);

                });

            }
        }
        /*
        |--------------------------------------------------------------------------
        | Filter Date
        |--------------------------------------------------------------------------
        */

        $from = null;
        $to = null;

        try {

            if ($request->filled('from')) {
                $from = Carbon::parse($request->from)->format('Y-m-d');
            }

            if ($request->filled('to')) {
                $to = Carbon::parse($request->to)->format('Y-m-d');
            }

            if ($from && $to) {

                $query->whereBetween('tanggal_inspect', [
                    $from,
                    $to
                ]);

            } elseif ($from) {

                $query->whereDate('tanggal_inspect', '>=', $from);

            } elseif ($to) {

                $query->whereDate('tanggal_inspect', '<=', $to);

            }

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Format tanggal tidak valid'
            ], 422);

        }

        $data = $query
            ->orderByDesc('tanggal_inspect')
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Transform Data
        |--------------------------------------------------------------------------
        */

        $data->transform(function ($inspect) use ($from, $to) {

            $inspect->spk_item = null;

            if ($inspect->spk && !empty($inspect->spk->data)) {

                $spkData = is_array($inspect->spk->data)
                    ? $inspect->spk->data
                    : json_decode($inspect->spk->data, true);

                if (isset($spkData['items'])) {

                    foreach ($spkData['items'] as $item) {

                        if (($item['detail_po_id'] ?? null) == $inspect->detail_po_id) {

                            $inspect->spk_item = $item;
                            break;
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Batch History
            |--------------------------------------------------------------------------
            */

            $batchHistory = $inspectionModel::select(
                'id',
                'batch',
                'passed',
                'rejected',
                'jumlah_inspect',
                'tanggal_inspect'
            )
                ->where('detail_po_id', $inspect->detail_po_id)
                ->where('kategori_id', $inspect->kategori_id);

            if ($from && $to) {

                $batchHistory->whereBetween('tanggal_inspect', [
                    $from,
                    $to
                ]);

            } elseif ($from) {

                $batchHistory->whereDate('tanggal_inspect', '>=', $from);

            } elseif ($to) {

                $batchHistory->whereDate('tanggal_inspect', '<=', $to);

            }

            $inspect->batch_history = $batchHistory
                ->orderBy('batch')
                ->get();

            return $inspect;
        });

        return view('pages.qc.lap', [
            'response' => [
                'success' => true,
                'count' => $data->count(),
                'filters' => [
                    'user_id' => $request->user_id,
                    'from' => $request->from,
                    'to' => $request->to,
                ],
                'data' => $data,
            ]
        ]);
    }
    public function filterInspection(Request $request)
    {
        $inspectionModel = $this->inspectionScheduleModel();

        $query = $inspectionModel::with([
            'po',
            'spk',
            'user'
        ]);

        // Filter Inspector
        if ($request->filled('inspector')) {
            $query->where('user_id', $request->inspector);
        }

        // Filter Tanggal Awal
        if ($request->filled('from')) {
            $query->whereDate('tanggal_inspect', '>=', $request->from);
        }

        // Filter Tanggal Akhir
        if ($request->filled('to')) {
            $query->whereDate('tanggal_inspect', '<=', $request->to);
        }

        $inspection = $query
            ->latest('tanggal_inspect')
            ->get();
        // dd($inspection);
        $html = view(
            'pages.qc.partial.inspection_table',
            compact('inspection')
        )->render();

        return response()->json([
            'html' => $html,
            'total' => $inspection->count()
        ]);
    }
}

