<?php
namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\DetailPo;
use App\Models\InspectSchedule;
use App\Models\Kategori;
use App\Models\Po;
use App\Models\QcReport;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function show(string $id)
    {
        //
        $data    = Po::find($id);
        $detailP = DetailPo::where('po_id', $data->id)->get();
        // dd( $detailP );
        $jenis = Kategori::all();

        return view('pages.qc.detail', compact('data', 'detailP', 'jenis'));
    }

    public function convert(Request $request)
    {
        $raw   = trim($request->excel_data);
        $lines = preg_split("/\r\n|\n|\r/", $raw);

        // =========================
        // HEADER
        // =========================
        $headerLine = array_map('trim', explode("\t", $lines[0]));

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
    public function releaseOrder(){
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
         $q = $request->q;

    $pos = Po::with('details') // 🔥 ambil relasi detail_po
        ->when($q, function ($query) use ($q) {
            $query->where('order_no', 'like', "%{$q}%")
                  ->orWhere('company_name', 'like', "%{$q}%");
        })
        ->latest()
        ->get();

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

    public function getPo()
    {
        $userId = auth()->id();

        $pos = Po::with('details')->get();

        $detailPoIds = $pos->pluck('details')->flatten()->pluck('id');

        $inspectionSchedules = InspectSchedule::with('kategori')
            ->whereIn('detail_po_id', $detailPoIds)
            ->where('user_id', $userId)
            ->get();

        return response()->json([
            'status'               => 'success',
            'data'                 => $pos,
            'inspection_schedules' => $inspectionSchedules,
        ]);
    }
    public function getInspect()
    {

    }

    public function getData(string $kategoriName, string $detailPoId, String $poId)
    {
        // $poId = 7;

        /* ===============================
       KATEGORI
    =============================== */
        $kategori = Kategori::where('kategori', $kategoriName)->firstOrFail();

        /* ===============================
       CHECKPOINT SESUAI KATEGORI
    =============================== */
        $checkpoints   = Checkpoint::where('kategori_id', $kategori->id)->get();
        $checkpointIds = $checkpoints->pluck('id');

        /* ===============================
       QC REPORT + RELASI
    =============================== */
        $qcReports = QcReport::with([
            'inspectSchedule:id,po_id,detail_po_id,batch,jumlah_inspect,tanggal_inspect,user_id',
            'photos:id,qc_report_id,keterangan,path',
            'checkpoint:id,name',
        ])
            ->where('po_id', $poId)
            ->where('detail_po_id', $detailPoId)
            ->whereIn('check_point_id', $checkpointIds)
            ->get();

        /* ===============================
       GROUP PER BATCH
    =============================== */
        $batches = [];

        foreach ($qcReports as $report) {

            $schedule = $report->inspectSchedule;
            if (! $schedule) {
                continue;
            }

            $batchKey = 'Batch ' . $schedule->batch;

            if (! isset($batches[$batchKey])) {

                $batches[$batchKey] = [
                    'batch_ke'       => $schedule->batch,
                    'tanggal'        => $schedule->tanggal_inspect,
                    'jumlah_inspect' => $schedule->jumlah_inspect,
                    'jenis'          => $kategori->kategori,
                    'inspector'      => User::find($schedule->user_id)->name ?? 'N/A',
                    'checkpoints'    => [],
                ];
            }

            $batches[$batchKey]['checkpoints'][$report->checkpoint->name] = [
                'size'   => $report->size,
                'remark' => $report->remark,
                'photos' => $report->photos->map(function ($p) {
                    return [
                        'keterangan' => $p->keterangan,
                        'path'       => $p->path,
                    ];
                })->values(),
            ];
        }

        return response()->json([
            'kategori'     => $kategori->kategori,
            'po_id'        => $poId,
            'detail_po_id' => $detailPoId,
            'batches'      => $batches,
        ]);
    }

    public function insertDummy(string $kategoriName)
    {
        $po_id        = 7;
        $detail_po_id = 23;

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
        $detailPo = DetailPo::findOrFail($detail_po_id);
        $detail   = $detailPo->detail;

        $qtyDetail = (int) ($detail['qty'] ?? 0);

        if ($qtyDetail <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Qty pada detail_po tidak valid',
            ], 400);
        }

        /* ===============================
       HITUNG TOTAL INSPECT PER KATEGORI 🔥
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
           BATCH KE (PER KATEGORI 🔥)
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
            $remarks = [
                'OK',
                'Minor defect',
                'Aktual lebih 2 cm',
                'Tidak sesuai drawing',
                'Perlu koreksi',
            ];

            foreach ($checkpoints as $checkpointId) {

                $qcReport = QcReport::create([
                    'inspect_schedule_id' => $inspectSchedule->id,
                    'check_point_id'      => $checkpointId,
                    'po_id'               => $po_id,
                    'detail_po_id'        => $detail_po_id,
                    'size'                => rand(30, 120),
                    'remark'              => $remarks[array_rand($remarks)],
                ]);

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
    public function getCheckpointData(String $kategoriName)
    {
        $kategori = Kategori::where('kategori', $kategoriName)->firstOrFail();

        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();

        return response()->json([
            'status'      => 'success',
            'kategori'    => $kategoriName,
            'checkpoints' => $checkpoints,
        ]);
    }
    public function insertInspection(string $kategoriName, Request $request)
    {
        $po_id          = $request->po_id;
        $detail_po_id   = $request->detail_po_id;
        $qty_inspection = $request->qty_inspction;

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
        $detailPo = DetailPo::findOrFail($detail_po_id);
        $detail   = $detailPo->detail;

        $qtyDetail = (int) ($detail['qty'] ?? 0);

        if ($qtyDetail <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Qty pada detail_po tidak valid',
            ], 400);
        }

        /* ===============================
       HITUNG TOTAL INSPECT
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
        $jumlahInspectBatch = min($qty_inspection, $sisaQty);

        DB::beginTransaction();

        try {

            /* ===============================
           BATCH KE
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
                'user_id'         => auth()->id() ?? 1,
            ]);

            /* ===============================
           QC REPORT + PHOTO
        =============================== */
            $remarks = [
                'OK',
                'Minor defect',
                'Aktual lebih 2 cm',
                'Tidak sesuai drawing',
                'Perlu koreksi',
            ];

            foreach ($checkpoints as $checkpointId) {

                $qcReport = QcReport::create([
                    'inspect_schedule_id' => $inspectSchedule->id,
                    'check_point_id'      => $checkpointId,
                    'po_id'               => $po_id,
                    'detail_po_id'        => $detail_po_id,
                    'size'                => rand(30, 120),
                    'remark'              => $remarks[array_rand($remarks)],
                ]);

                /* ===============================
               UPLOAD FOTO (SERVICE)
            =============================== */
                if ($request->hasFile("photos.$checkpointId")) {

                    foreach ($request->file("photos.$checkpointId") as $file) {

                        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

                        $path = $file->storeAs(
                            'uploads/qc',
                            $filename,
                            'public'
                        );

                        ReportPhoto::create([
                            'qc_report_id' => $qcReport->id,
                            'keterangan'   => "Foto {$kategoriName} batch {$batchKe}",
                            'path' => $path,
                        ]);
                    }
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

}
