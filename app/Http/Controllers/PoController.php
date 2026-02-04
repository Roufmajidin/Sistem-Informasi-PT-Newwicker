<?php
namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class PoController extends Controller
{
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
        $raw = trim($request->excel_data);
        $raw = str_replace('Â', '', $raw);

        /*
    =====================================
    CSV STYLE PARSER (TAB DELIMITER)
    SUPPORT MULTILINE CELL
    =====================================
    */
        $rows     = [];
        $row      = [];
        $cell     = '';
        $inQuotes = false;

        $len = strlen($raw);

        for ($i = 0; $i < $len; $i++) {

            $char = $raw[$i];

            if ($char === '"') {
                $inQuotes = ! $inQuotes;
                continue;
            }

            if ($char === "\t" && ! $inQuotes) {
                $row[] = trim($cell);
                $cell  = '';
                continue;
            }

            if (($char === "\n" || $char === "\r") && ! $inQuotes) {

                if ($cell !== '' || ! empty($row)) {
                    $row[]  = trim($cell);
                    $rows[] = $row;
                }

                $row  = [];
                $cell = '';
                continue;
            }

            $cell .= $char;
        }

        if ($cell !== '') {
            $row[]  = trim($cell);
            $rows[] = $row;
        }

        $rows = array_values(array_filter($rows));

        /*
    =====================================
    HEADER 3 LEVEL
    =====================================
    */

        $header1 = $rows[0] ?? [];
        $header2 = $rows[1] ?? [];
        $header3 = $rows[2] ?? [];

        $headers    = [];
        $wdhCounter = 0;

        foreach ($header1 as $i => $col1) {

            $h3 = strtolower(trim($header3[$i] ?? ''));

            if (in_array($h3, ['w', 'd', 'h'])) {

                $wdhCounter++;

                if ($wdhCounter <= 3) {
                    $headers[] = "item_$h3";
                } else {
                    $headers[] = "packing_$h3";
                }

                continue;
            }

            $key       = strtolower(str_replace([' ', '.', "\n"], '_', $col1));
            $headers[] = $key;
        }

        /*
    =====================================
    DATA
    =====================================
    */

        $items = [];

        for ($r = 3; $r < count($rows); $r++) {

            $cols = $rows[$r];

            // skip bukan item
            if (! isset($cols[0]) || ! is_numeric($cols[0])) {
                continue;
            }

            $rowData = [];

            foreach ($headers as $idx => $key) {
                $rowData[$key] = $cols[$idx] ?? null;
            }

            $items[] = $rowData;
        }

        return response()->json([
            'items' => $items,
            'items' => $items,
        ]);
    }
//  contro
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('excel_file');

        // Load spreadsheet
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();

        $rows       = [];
        $imagesData = [];

        // --- Ambil gambar embedded ---
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $coords = $drawing->getCoordinates();            // misal B12
            $row    = preg_replace('/\D/', '', $coords) - 1; // 0-based index
            $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(preg_replace('/\d/', '', $coords)) - 1;

            $filename = uniqid('excel_img_') . '.png';
            $path     = storage_path('app/public/' . $filename);

            if ($drawing instanceof MemoryDrawing) {
                // Gambar dibuat di memory
                ob_start();
                $renderingFunction = $drawing->getRenderingFunction();
                $renderingFunction($drawing->getImageResource());
                $imageContents = ob_get_contents();
                ob_end_clean();

                file_put_contents($path, $imageContents);

            } elseif ($drawing instanceof Drawing) {
                // Gambar dari file
                $source = $drawing->getPath();
                copy($source, $path);
            }

            // Mapping row-col → URL publik
            $imagesData["$row-$col"] = asset('storage/' . $filename);
        }

        // --- Ambil semua text dari Excel ---
        $highestRow      = $sheet->getHighestRow();
        $highestCol      = $sheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        for ($r = 1; $r <= $highestRow; $r++) {
            $rowData = [];
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $cell      = $sheet->getCellByColumnAndRow($c, $r);
                $rowData[] = $cell ? $cell->getCalculatedValue() : null;
            }
            $rows[] = $rowData;
        }

        return response()->json([
            'rows'   => $rows,
            'images' => $imagesData,
        ]);
    }

    public function saveExcelData(Request $request)
    {
        $company = $request->company;
        $items   = $request->items;

        if (empty($items)) {
            return response()->json(['items' => []]);
        }

        $orderNo     = $company['order_no_'] ?? null;
        $companyName = $company['company_name'] ?? null;

        // ✅ VALIDASI DUPLIKAT PO
        $exists = Po::where('order_no', $orderNo)
            ->orWhere('company_name', $companyName)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'PO dengan Order No atau Company Profile sudah ada',
            ], 422);
        }

        // ======================
        // SIMPAN PO
        // ======================

        $po = Po::create([
            'order_no'       => $orderNo ?? "-",
            'company_name'   => $companyName ?? "-",
            'country'        => $company['country'] ?? "-",
            'shipment_date'  => $company['shipment_date'] ?? "-",
            'packing'        => $company['packing'] ?? "-",
            'contact_person' => $company['contact_person'] ?? "-",
        ]);

        // ======================
        // NORMALIZE KEY
        // ======================

        $normalizeKeys = function ($array) use (&$normalizeKeys) {
            $result = [];
            foreach ($array as $key => $value) {
                $key          = preg_replace('/[\s\.\-\/]+/', '_', $key);
                $key          = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
                $result[$key] = is_array($value) ? $normalizeKeys($value) : $value;
            }
            return $result;
        };

        // Skip index 0
        $items = array_slice($items, 1);

        foreach ($items as $item) {
            DetailPo::create([
                'po_id'  => $po->id,
                'detail' => $normalizeKeys($item),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'po_id'  => $po->id,
        ]);
    }
    public function getPoDetail($id)
    {
        $po = Po::findOrFail($id);

        $items = DetailPo::where('po_id', $id)->get();

        return response()->json([
            'po'    => $po,
            'items' => $items,
        ]);
    }
// updaye row
    public function updateItemBulk(Request $request)
    {
        $items = $request->input('items', []);

        if (! is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload',
            ], 422);
        }

        foreach ($items as $row) {

            if (! isset($row['id'])) {
                continue;
            }

            $item = DetailPo::find($row['id']);
            if (! $item) {
                continue;
            }

            $oldDetail = $item->detail ?? [];
            $newDetail = $row['detail'] ?? [];

            $changedFields = [];

            // ================= DETECT CHANGE =================
            foreach ($newDetail as $key => $value) {

                if ($value === '') {
                    $value = null;
                }

                if (in_array($key, [
                    'qty',
                    'cbm',
                    'total_cbm',
                    'value_in_usd',
                    'fob_jakarta_in_usd',
                ])) {
                    $value = is_numeric($value) ? (float) $value : 0;
                }

                $oldValue = $oldDetail[$key] ?? null;

                if ($oldValue != $value) {
                    $changedFields[] = "$key ($oldValue → $value)";
                }

                $newDetail[$key] = $value;
            }

            // ================= MERGE =================
            $merged = array_merge($oldDetail, $newDetail);

            if (empty($changedFields)) {
                continue;
            }

            // ================= HISTORY UPDATED_BY =================
            $history = $item->updated_by ?? [];

            // kalau lama masih object, convert ke array
            if (! is_array($history) || isset($history['user_id'])) {
                $history = [$history];
            }

            $history[] = [
                'user_id'   => Auth::id(),
                'timestamp' => now()->toDateTimeString(),
                'remark'    => 'change: ' . implode(', ', $changedFields),
            ];

            // ================= UPDATE =================
            $item->update([
                'detail'     => $merged,
                'updated_by' => $history,
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
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

        $pos = Po::query()
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
