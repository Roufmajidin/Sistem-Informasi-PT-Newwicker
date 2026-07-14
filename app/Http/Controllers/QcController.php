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
use App\Models\TimelineQc;
use App\Models\User;
use App\Models\Karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
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
    public function convert(Request $request)
    {
        $raw   = trim($request->excel_data);
        $lines = preg_split("/\r\n|\n|\r/", $raw);
        // =========================
        // HEADER
        // =========================
        $headerLine   = array_map('trim', explode("\t", $lines[0]));
        $headers      = [];
        $wdhCount     = 0;
        $headerRepeat = []; // untuk Remark Remark dll
        foreach ($headerLine as $col) {
            // ===== HANDLE W D H =====
            if (in_array($col, ['W', 'D', 'H'])) {
                $wdhCount++;
                if ($wdhCount <= 3) {
                    $headers[] = 'item_' . strtolower($col);
                } else {
                    $headers[] = 'packing_' . strtolower($col);
                }
                continue;
            }
            // ===== NORMAL HEADER =====
            $key = strtolower(str_replace([' ', '.', "\n"], '_', $col));
            // ===== DUPLICATE HEADER (Remark Remark, dll) =====
            if (isset($headerRepeat[$key])) {
                $headerRepeat[$key]++;
                $key .= '_' . $headerRepeat[$key];
            } else {
                $headerRepeat[$key] = 1;
                // suffix _1 hanya jika nanti ada duplikat
                // remark pertama tetap "remark"
            }
            $headers[] = $key;
        }
        // =========================
        // DATA
        // =========================
        $items = [];
        for ($i = 1; $i < count($lines); $i++) {
            $cols = array_map('trim', explode("\t", $lines[$i]));
            // skip baris bukan item
            if (! isset($cols[0]) || ! is_numeric($cols[0])) {
                continue;
            }
            $row = [];
            foreach ($headers as $idx => $key) {
                $row[$key] = $cols[$idx] ?? null;
            }
            $items[] = $row;
        }
        return response()->json([
            // 'headers' => $headers,
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
                    'company_name'   => $buyer['Company_Name'] ?? '-',
                    'country'        => $buyer['Country'] ?? '-',
                    'shipment_date'  => $buyer['Shipment_Date'] ?? '-',
                    'packing'        => $buyer['Packing'] ?? '-',
                    'contact_person' => $buyer['Contact_Person'] ?? '-',
                ]
            );
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            // 2. DETAIL ( ROW PER ITEM )
            // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  =
            foreach ($items as $item) {
                DetailPo::updateOrCreate(
                    [
                        'po_id'               => $po->id,
                        'detail->article_nr_' => $item['article_nr_'] ?? null,
                    ],
                    [
                        'detail' => $item,
                    ]
                );
            }
            DB::commit();
            return response()->json([
                'status'           => 'success',
                'po_id'            => $po->id,
                'total_detail_row' => count($items),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
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
        $q    = $request->q;
        $type = $request->type;
        $pos  = Po::with('details')
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
                // NWS
                if ($type === 'NWS') {
                    $query->where('order_no', 'like', 'NWS%');
                }
                // NW (bukan NWS)
                if ($type === 'NW') {
                    $query->where('order_no', 'like', 'NW%')
                        ->where('order_no', 'not like', 'NWS%');
                }
            })
            ->latest()
            ->get();
        return response()->json($pos);
    }
    public function ajaxPo(Request $request)
    {
        $q   = $request->q;
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
    //     $report = QcReport::create([
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
    //     $inspectionSchedules = InspectSchedule::with('kategori')
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
    {}
    public function exportPdf($kategori, $po_id)
    {
        $po    = DB::table('po')->where('id', $po_id)->first();
        $items = $this->buildQcData($kategori, $po_id);
        // dd($items);
        $pdf = Pdf::loadView('pages.qc.pdf', [
            'items'    => $items,
            'kategori' => $kategori,
            'po'       => $po,
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
            $batch  = $r->batch;
            // ðŸ”¥ ambil detail item (JSON)
            if (! isset($items[$itemId])) {
                $detail = DB::table('detail_po')->where('id', $itemId)->first();
                $json   = json_decode($detail->detail ?? '{}', true);
                // dd($detail);
                $items[$itemId] = [
                    'article' => $json['article_nr'] ?? $json['article_code'] ?? $json['nw_code'] ?? $json['article_nr_nw'] ?? $json['no'] ?? '-',
                    'name'    => $json['description'] ?? $json['nama'] ?? '-',
                    'qty'     => (int) ($json['qty'] ?? 0),
                    'batches' => [],
                ];
            }
            // ðŸ”¥ batch init
            if (! isset($items[$itemId]['batches'][$batch])) {
                $items[$itemId]['batches'][$batch] = [
                    'tanggal'     => $r->tanggal_inspect,
                    'inspect'     => 0,
                    'passed'      => 0,
                    'rejected'    => 0,
                    'checkpoints' => [],
                ];
            }
            // ðŸ”¥ agregasi
            $items[$itemId]['batches'][$batch]['inspect']  += $r->jumlah_inspect;
            $items[$itemId]['batches'][$batch]['passed']   += $r->passed;
            $items[$itemId]['batches'][$batch]['rejected'] += $r->rejected;
            // ðŸ”¥ ambil qc_report
            $qcRows  = $reports[$r->id] ?? [];
            foreach ($qcRows as $qc) {
                $cpId                                                      = $qc->check_point_id;
                $a                                                         = Checkpoint::find($cpId);
                $cpName                                                    = $a->name;
                $items[$itemId]['batches'][$batch]['checkpoints'][$cpName] = [
                    'name'   => $cpName,
                    'size'   => $qc->size,
                    'remark' => $qc->remark,
                    'photos' => $photos[$qc->id] ?? [],
                ];
            }
            // dd($items);
        }
        return $items;
    }
    public function getDataApi(string $kategoriName, string $detailPoId, string $poId)
    {
        $kategori = Kategori::where('kategori', $kategoriName)
            ->firstOrFail();
        $detail_po = DetailPo::findOrFail($detailPoId);
        $nwCode    = $detail_po->detail['nw_code'] ?? null;
        $cad       = CadModel::where('article_code', $nwCode)
            ->orderByDesc('version')
            ->first();
        $checkpoints   = Checkpoint::where('kategori_id', $kategori->id)->get();
        $checkpointIds = $checkpoints->pluck('id');
        $qcReports     = QcReport::with([
            'inspectSchedule:id,po_id,detail_po_id,batch,jumlah_inspect,tanggal_inspect,user_id,passed,rejected',
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
            if (! $schedule) {
                continue;
            }
            $batchKey = 'Batch ' . $schedule->batch;
            /// ===================================================
            /// CREATE BATCH
            /// ===================================================
            if (! isset($batches[$batchKey])) {
                /// ===============================================
                /// TEMUAN GLOBAL
                /// qc_report_id = NULL
                /// ===============================================
                $temuan = ReportPhoto::where(
                    'inspect_schedule_id',
                    $schedule->id
                )
                    ->whereNull('qc_report_id')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'keterangan' => $p->keterangan,
                            'path'       => url(
                                '/storage/' . $p->path
                            ),
                            'raw_path'   => $p->path,
                        ];
                    })
                    ->values();
                $batches[$batchKey] = [
                    'batch_ke'       => $schedule->batch,
                    'items'          => $detail_po->detail,
                    'tanggal'        => $schedule->tanggal_inspect,
                    'jumlah_inspect' => $schedule->jumlah_inspect,
                    'jenis'          => $kategori->kategori,
                    'passed'         => $schedule->passed,
                    'rejected'       => $schedule->rejected,
                    'inspector'      =>
                    User::find(
                        $schedule->user_id
                    )->name ?? 'N/A',
                    'master_sample'  =>
                    $cad->master_sample ?? null,
                    // ✅ TEMUAN
                    'temuan'         => $temuan,
                    // ✅ CHECKPOINTS
                    'checkpoints'    => [],
                ];
            }
            /// ===================================================
            /// CHECKPOINTS
            /// ===================================================
            $batches[$batchKey]
            ['checkpoints']
            [$report->checkpoint->name] = [
                'size'   => $report->size,
                'remark' => $report->remark,
                // ✅ FOTO PER CHECKPOINT
                'photos' => $report->photos
                    ->map(function ($p) {
                        return [
                            'keterangan' =>
                            $p->keterangan,
                            'path'       => url(
                                '/storage/' . $p->path
                            ),
                            'raw_path'   =>
                            $p->path,
                        ];
                    })
                    ->values(),
            ];
        }
        return response()->json([
            'kategori'     => $kategori->kategori,
            'po_id'        => $poId,
            'detail_po_id' => $detailPoId,
            'batches'      => $batches,
        ]);
    }
    public function insertDummy(string $kategoriName, Request $request)
    {
        $po_id        = $request->po_id;
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
                'status'  => 'error',
                'message' => 'Checkpoint untuk kategori ini belum ada',
            ], 400);
        }
        /* ===============================
       AMBIL QTY PO
    =============================== */
        $detailPo  = DetailPo::findOrFail($detail_po_id);
        $detail    = $detailPo->detail;
        $qtyDetail = (int) ($detail['qty'] ?? 0);
        if ($qtyDetail <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Qty pada detail_po tidak valid',
            ], 400);
        }
        /* ===============================
       HITUNG TOTAL INSPECT PER KATEGORI ðŸ”¥
    =============================== */
        $totalInspect = InspectSchedule::where('detail_po_id', $detail_po_id)
            ->where('kategori_id', $kategori->id)
            ->sum('jumlah_inspect');
        if ($totalInspect >= $qtyDetail) {
            return response()->json([
                'status'  => 'error',
                'message' => "Inspect kategori {$kategoriName} sudah memenuhi qty PO",
            ], 400);
        }
        $sisaQty            = $qtyDetail - $totalInspect;
        $jumlahInspectBatch = min(10, $sisaQty); // simulasi
        DB::beginTransaction();
        try {
            /* ===============================
           BATCH KE (PER KATEGORI ðŸ”¥)
        =============================== */
            $batchKe = InspectSchedule::where('detail_po_id', $detail_po_id)
                ->where('kategori_id', $kategori->id)
                ->count() + 1;
            /* ===============================
           INSPECT SCHEDULE
        =============================== */
            $inspectSchedule = InspectSchedule::create([
                'po_id'           => $po_id,
                'detail_po_id'    => $detail_po_id,
                'kategori_id'     => $kategori->id,
                'batch'           => $batchKe,
                'jumlah_inspect'  => $jumlahInspectBatch,
                'tanggal_inspect' => now()->toDateString(),
                'user_id'         => 1,
            ]);
            /* ===============================
           QC REPORT + PHOTO
        =============================== */
            foreach ($checkpoints as $checkpointId) {
                $qcReport = QcReport::create([
                    'inspect_schedule_id' => $inspectSchedule->id,
                    'check_point_id'      => $checkpointId,
                    'po_id'               => $po_id,
                    'detail_po_id'        => $detail_po_id,
                    'size'                => rand(30, 120),
                    'remark'              => $request->remark,
                ]);
                // request dari form foto upload
                foreach (range(1, rand(1, 3)) as $i) {
                    ReportPhoto::create([
                        'qc_report_id' => $qcReport->id,
                        'keterangan'   => "Foto {$kategoriName} batch {$batchKe}",
                        'path' => 'uploads/qc/' . Str::random(12) . '.jpg',
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status'        => 'success',
                'kategori'      => $kategoriName,
                'batch'         => $batchKe,
                'inspect_batch' => $jumlahInspectBatch,
                'total_inspect' => $totalInspect + $jumlahInspectBatch,
                'qty_po'        => $qtyDetail,
                'message'       => 'Batch inspect berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    //
    private function saveTimelineQC(InspectSchedule $inspectSchedule)
    {
        try {
            /* ===============================
           CEK APAKAH LANJUTAN
        =============================== */
            $previous = InspectSchedule::where('detail_po_id', $inspectSchedule->detail_po_id)
                ->where('kategori_id', $inspectSchedule->kategori_id)
                ->where('id', '!=', $inspectSchedule->id)
                ->exists();
            $isLanjutan = $previous ? 1 : 0;
            TimelineQc::create([
                'po_id'               => $inspectSchedule->po_id,
                'detail_po_id'        => $inspectSchedule->detail_po_id,
                'kategori_id'         => $inspectSchedule->kategori_id,
                'inspect_schedule_id' => $inspectSchedule->id,
                'user_id'             => $inspectSchedule->user_id,
                'qty'                 => $inspectSchedule->jumlah_inspect,
                'tanggal'             => $inspectSchedule->tanggal_inspect,
                'is_lanjutan'         => $isLanjutan,
            ]);
            Log::info('Timeline QC Saved', [
                'schedule_id' => $inspectSchedule->id,
                'lanjutan'    => $isLanjutan,
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
                'po_id'        => $t->detailPo->po->id ?? null,
                'order_no'     => $t->detailPo->po->order_no ?? '-',
                'company_name' => $t->detailPo->po->company_name ?? '-',
                'detail_po_id' => $t->detail_po_id,
                'tanggal'      => $t->tanggal,
                'user'         => $t->user->name ?? '-',
                'divisi'       => $t->kategori->kategori ?? '-',
                'batch'        => $t->schedule->batch ?? null,
                'qty'          => $t->qty,
                'is_lanjutan'  => (bool) $t->is_lanjutan,
                'label'        => $this->buildLabel($t),
            ];
        });
        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }
    private function buildLabel($t)
    {
        $tanggal  = \Carbon\Carbon::parse($t->tanggal)->translatedFormat('d M');
        $user     = $t->user->name ?? '-';
        $div      = $t->kategori->kategori ?? '-';
        $qty      = $t->qty;
        $lanjutan = $t->is_lanjutan ? ' (lanjutan)' : '';
        return "{$tanggal} â€” {$user} (Div. {$div}) inspect qty = {$qty}{$lanjutan}";
    }
    public function laporan()
    {
        $inspection= InspectSchedule::with(['kategori','user', 'spk', 'detailPo', 'po'])
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
            'detail_pos' => $po->detailPos,

            'spks'       => $po->spks,
        ]);
    }

    public function getData(string $kategoriName, string $detailPoId, string $poId)
    {
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
        $qcReports = QcReport::with([
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
            if (! $schedule) {
                continue;
            }
            $batchKey = 'Batch ' . $schedule->batch;
            /* ===============================
           INIT BATCH
        =============================== */
            if (! isset($batches[$batchKey])) {
                $batches[$batchKey] = [
                    'batch_ke'       => $schedule->batch,
                    'tanggal'        => $schedule->tanggal_inspect,
                    'jumlah_inspect' => $schedule->jumlah_inspect,
                    'passed'         => $schedule->passed ?? 0,
                    'rejected'       => $schedule->rejected ?? 0,
                    'jenis'          => $kategori->kategori,
                    'inspector'      => User::find(
                        $schedule->user_id
                    )->name ?? 'N/A',
                    // ✅ TEMUAN GLOBAL
                    'temuan'         => [],
                    // ✅ CHECKPOINTS
                    'checkpoints'    => [],
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
                        'path'       => url('/storage/' . $photo->path),
                        'raw_path'   => $photo->path,
                    ];
                }
                continue;
            }
            /* ==================================================
           CHECKPOINT
        ================================================== */
            $checkpointName = $report->checkpoint->name ?? 'Unknown';
            if (! isset(
                $batches[$batchKey]['checkpoints'][$checkpointName]
            )) {
                $batches[$batchKey]['checkpoints'][$checkpointName] = [
                    'size'   => $report->size,
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
                    'path'       => url('/storage/' . $photo->path),
                    'raw_path'   => $photo->path,
                ];
            }
        }
        return response()->json([
            'kategori'     => $kategori->kategori,
            'po_id'        => $poId,
            'detail_po_id' => $detailPoId,
            'batches'      => $batches,
        ]);
    }

    public function getCheckpointData(String $kategoriName)
    {
        $kategori    = Kategori::where('kategori', $kategoriName)->firstOrFail();
        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();
        return response()->json([
            'status'      => 'success',
            'kategori'    => $kategoriName,
            'checkpoints' => $checkpoints,
        ]);
    }
    public function insertInspection(
        string $kategoriName,
        Request $request
    ) {
        Log::info(
            '===== QC INSERT START ====='
        );
        Log::info(
            'Request',
            $request->all()
        );
        DB::beginTransaction();
        try {
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
            if (! $kategori) {
                return response()->json([
                    'status'  =>
                    'error',
                    'message' =>
                    'Kategori tidak ditemukan',
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
            (int) $request
                ->qty_inspection;
            $passed =
            (int) $request
                ->passed;
            $rejected =
            (int) $request
                ->rejected;
            $isReinspect =
            $request->boolean('is_reinspect');

            /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */
            if (
                ($passed + $rejected)
                != $qtyInspection
            ) {
                return response()->json([
                    'status'  =>
                    'error',
                    'message' =>
                    'Passed + Rejected tidak sesuai',
                ], 400);
            }
            /*
        |--------------------------------------------------------------------------
        | BATCH
        |--------------------------------------------------------------------------
        */
            $batchKe =
            InspectSchedule::where(
                'detail_po_id',
                $detail_po_id
            )
                ->where(
                    'kategori_id',
                    $kategori->id
                )
                ->count() + 1;

                //
                /*
            |--------------------------------------------------------------------------
            | VALIDASI QTY
            |--------------------------------------------------------------------------
            */

          $detailPo = DetailPo::findOrFail($detail_po_id);

            $detail = is_array($detailPo->detail)
                ? $detailPo->detail
                : json_decode($detailPo->detail, true);

            $qtyPo = (int) ($detail['qty'] ?? 0);
            $totalInspect = InspectSchedule::where(
                'detail_po_id',
                $detail_po_id
            )
            ->where(
                'kategori_id',
                $kategori->id
            )
            ->sum('jumlah_inspect');

            //
            $totalRejected = InspectSchedule::where(
                    'detail_po_id',
                    $detail_po_id
                )
                ->where(
                    'kategori_id',
                    $kategori->id
                )
                ->sum('rejected');
            if (!$isReinspect) {

                // First Inspection

              $remaining = $qtyPo - $totalInspect;

                if ($qtyInspection > $remaining) {

                    return response()->json([
                        'status' => 'error',
                        'message' =>
                            'Sisa qty hanya '.$remaining,
                    ],422);

                }

            } else {

                // Re-Inspection

                if ($qtyInspection > $totalRejected) {

                    return response()->json([

                        'status' => 'error',

                        'message' =>
                            'Qty reject yang bisa di-reinspect hanya ' . $totalRejected,

                    ], 422);

                }

            }
            $inspectSchedule =
            InspectSchedule::create([
                'po_id'           =>
                $po_id,
                'detail_po_id'    =>
                $detail_po_id,
                'kategori_id'     =>
                $kategori->id,
                'batch'           =>
                $batchKe,
                'jumlah_inspect'  =>
                $qtyInspection,
                'passed'          =>
                $passed,
                'rejected'        =>
                $rejected,
                'tanggal_inspect' =>
                now()
                    ->toDateString(),
                'user_id'         =>
                auth()->id() ?? 1,
                'spk_id'          =>
                $spk_id,
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
            | INSERT REPORT
            |--------------------------------------------------------------------------
            */
                $qcReport = QcReport::create([
                    'inspect_schedule_id' =>
                    $inspectSchedule->id,
                    'check_point_id'      =>
                    $report['checkpoint_id'],
                    'po_id'               =>
                    $po_id,
                    'detail_po_id'        =>
                    $detail_po_id,
                    'remark'              =>
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
            if ($request->hasFile('finding_images')) {
                foreach ($request->file('finding_images') as $index => $file) {
                    $filename =
                    Str::uuid() . '.' .
                    $file->getClientOriginalExtension();
                    $path = $file->storeAs(
                        'uploads/qc',
                        $filename,
                        'public'
                    );
                    ReportPhoto::create([
                        // ✅ TEMUAN GLOBAL
                        // 'qc_report_id'        => $qcReport->id,
                        'inspect_schedule_id' =>
                        $inspectSchedule->id,
                        'keterangan'          =>
                        $findings[$index]['remark'] ?? null,
                        'path'                => $path,
                    ]);
                }
            }
            if ($request->has('checkpoint_photos')) {
                foreach (
                    $request->checkpoint_photos as $checkpointId => $photos
                ) {
                    // ✅ AMBIL QC REPORT SESUAI CHECKPOINT
                    $checkpointReport = QcReport::where(
                        'inspect_schedule_id',
                        $inspectSchedule->id
                    )
                        ->where(
                            'check_point_id',
                            $checkpointId
                        )
                        ->first();
                    if (! $checkpointReport) {
                        continue;
                    }
                    foreach ($photos as $index => $file) {
                        if (
                            ! $file instanceof
                            \Illuminate\Http\UploadedFile
                        ) {
                            continue;
                        }
                        $filename =
                        Str::uuid() . '.' .
                        $file->getClientOriginalExtension();
                        $path = $file->storeAs(
                            'uploads/qc',
                            $filename,
                            'public'
                        );
                        ReportPhoto::create([
                            // ✅ QC REPORT YANG BENAR
                            'qc_report_id'        =>
                            $checkpointReport->id,
                            'inspect_schedule_id' =>
                            $inspectSchedule->id,
                            'keterangan'          =>
                            $request
                                ->checkpoint_photo_remarks
                            [$checkpointId][$index] ?? null,
                            'path'                => $path,
                        ]);
                    }
                }
            }
            DB::commit();
            Log::info(
                '===== QC INSERT SUCCESS ====='
            );
            return response()->json([
                'status'              =>
                'success',
                'message'             =>
                'Inspection berhasil',
                'batch'               =>
                $batchKe,
                'inspect_schedule_id' =>
                $inspectSchedule->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(
                'QC ERROR',
                [
                    'msg'  =>
                    $e->getMessage(),
                    'line' =>
                    $e->getLine(),
                    'file' =>
                    $e->getFile(),
                ]
            );
            return response()->json([
                'status'  =>
                'error',
                'message' =>
                $e->getMessage(),
            ], 500);
        }
    }
    public function show(string $id)
    {
        //
        $data    = Po::find($id);
        $detailP = DetailPo::where('po_id', $data->id)->get();
        // dd( $detailP );
        $jenis = Kategori::all();
        return view('pages.qc.detail', compact('data', 'detailP', 'jenis'));
    }

    public function getPo()
    {
        $userId = auth()->id();
        $user = auth()->user();

        $user->load('karyawan.divisi');

        $divisiQc = strtoupper(
            $user->karyawan?->divisi?->nama ?? ''
        );
        /*
    |--------------------------------------------------------------------------
    | GET PO
    |--------------------------------------------------------------------------
    */

        $pos = Po::with([
            'details',
            'spks',
        ])->get();

        $detailPoIds = $pos
            ->pluck('details')
            ->flatten()
            ->pluck('id');

        /*
    |--------------------------------------------------------------------------
    | ARTICLE
    |--------------------------------------------------------------------------
    */

       $articleNumbers = $pos
            ->pluck('details')
            ->flatten()
            ->map(function ($detail) {
                $articleNr = $detail->detail['article_nr_'] ?? null;
                $nwCode = $detail->detail['nw_code'] ?? null;

                return $nwCode === null
                    ? $articleNr
                    : ($articleNr ?? $nwCode);
            })
            ->filter()
            ->unique()
            ->values();

        /*
    |--------------------------------------------------------------------------
    | BOM
    |--------------------------------------------------------------------------
    */

        $boms = Bom::with([
            'groups.items',
        ])
            ->whereIn(
                'article_number',
                $articleNumbers
            )
            ->get();

        $bomMap = $boms->keyBy(
            'article_number'
        );

        /*
    |--------------------------------------------------------------------------
    | CAD
    |--------------------------------------------------------------------------
    */

        $cads = CadModel::whereIn(
            'article_code',
            $articleNumbers
        )
            ->orderByDesc('version')
            ->get()
            ->groupBy(function ($item) {

                return (string)
                $item->article_code;

            });

        /*
    |--------------------------------------------------------------------------
    | INSPECTION
    |--------------------------------------------------------------------------
    */

        $inspectionSchedules =
        InspectSchedule::with([
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
    | MAPPING
    |--------------------------------------------------------------------------
    */

        $pos->each(function ($po) use (

        $bomMap,
        $cads,
        $inspectionSchedules,
           $divisiQc




        ) {

            $po->details->each(function ($detail) use (

                $po,
                $bomMap,
                $cads,
                $inspectionSchedules,
           $divisiQc

            ) {

                /*
            |--------------------------------------------------------------------------
            | ARTICLE
            |--------------------------------------------------------------------------
            */

                $article = (string) (

                    $detail->detail['article_nr_'] ?? ''

                );

                /*
            |--------------------------------------------------------------------------
            | BOM
            |--------------------------------------------------------------------------
            */

                $detail->bom = (

                    $article &&
                    isset($bomMap[$article])

                )
                    ? $bomMap[$article]
                    : null;

                /*
            |--------------------------------------------------------------------------
            | CAD
            |--------------------------------------------------------------------------
            */

                $detail->cad = (

                    $article &&
                    isset($cads[$article])

                )
                    ? $cads[$article]->first()
                    : null;

                /*
            |--------------------------------------------------------------------------
            | INSPECTION
            |--------------------------------------------------------------------------
            */

                $detail->inspection_schedules =
                $inspectionSchedules
                    ->where(
                        'detail_po_id',
                        $detail->id
                    )
                    ->values();

                /*
            |--------------------------------------------------------------------------
            | SPK TERKAIT
            |--------------------------------------------------------------------------
            */

                $relatedSpks = [];

                foreach ($po->spks as $spk) {

                    $spkData = $spk->data;
// each baru
            $kategoriSpk = strtoupper(
                $spkData['kategori'] ?? ''
            );

            if (
                !$this->matchDivisi(
                    $divisiQc,
                    $kategoriSpk
                )
            ) {
                continue;
            }
                    if (
                        is_string($spkData)
                    ) {

                        $spkData = json_decode(
                            $spkData,
                            true
                        );

                    }

                    $items =
                    $spkData['items'] ?? [];

                    foreach ($items as $item) {

                        if (

                            ($item['detail_po_id'] ?? null)

                            ==

                            $detail->id

                        ) {
                            $inspect = $inspectionSchedules
                                ->where('detail_po_id', $detail->id)
                                ->where('spk_id', $spk->id);

                            $passed = $inspect->sum('passed');

                            $rejected = $inspect->sum('rejected');

                            $relatedSpks[] = [
                            // TAMBAHAN





                                'passed'      => $passed,

                                'rejected'    => $rejected,
                                'id'          =>
                                $spk->id,

                                'supplier'    =>
                                $spkData['sup'] ?? null,

                                'kategori'    =>
                                $spkData['kategori'] ?? null,

                                'status'      =>
                                $spkData['status'] ?? null,

                                'no_spk'      =>
                                $spkData['no_spk'] ?? null,

                                'tgl_terima'  =>
                                $spkData['tgl_terima'] ?? null,

                                'tgl_selesai' =>
                                $spkData['tgl_selesai'] ?? null,

                                'material'    =>
                                $item['material'] ?? '',

                                'qty'         =>
                                $item['qty'] ?? 0,

                                'harga'       =>
                                $item['harga'] ?? 0,

                                'total'       =>
                                $item['total'] ?? 0,

                            ];

                        }

                    }

                }

                $detail->spks =
                    $relatedSpks;

            });

        });

        /*
    |--------------------------------------------------------------------------
    | RETURN
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'status' => 'success',

            'data'   => $pos,

        ]);
    }
    public function detailPoReports($detailPoId)
    {
        $inspectSchedules = InspectSchedule::with([
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

        $divisi   = strtoupper($divisi);
        $kategori = strtoupper($kategori);

        $mapping = [

            'QC RANGKA' => [
                'RANGKA',
                'LASIO',
                'PLAT BESI',
            ],

            'QC ANYAM' => [
                'ANYAM',
                'DEKOR',
                'ECENG',
                'BANANA',
                'SONGKET',
                'WEBBING',
                'SYNTETIC',
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
    $query = InspectSchedule::with([
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
            'PLAT BESI',
        ],

        'anyam' => [
            'ANYAM',
            'ANYAM SINTETIS',
            'ANYAM KARAKTER',
        ],

        'unfinish' => [
            'RANGKA + ANYAM',
            'ANYAM + DEKOR',
            'BASKET JOGJA',
            'BASKE JOGJA',
            'BASKET LOMBOKAN',
            'BASKET TASIK',
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
    $to   = null;

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

        $batchHistory = InspectSchedule::select(
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
            'count'   => $data->count(),
            'filters' => [
                'user_id' => $request->user_id,
                'from'    => $request->from,
                'to'      => $request->to,
            ],
            'data' => $data,
        ]
    ]);
}
    public function filterInspection(Request $request)
    {
        $query = InspectSchedule::with([
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
