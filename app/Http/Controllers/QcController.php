<?php
namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\DetailPo;
use App\Models\Kategori;
use App\Models\Po;
use App\Models\QcReport;
use App\Models\ReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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

    public function ajaxPoList()
    {
        $pos = Po::latest()->get();

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
    public function getData(string $kategoriId, string $detailPo)
    {
        // ambil kategori berdasarkan nama
        $kategori = Kategori::where('kategori', $kategoriId)->firstOrFail();

        // ambil checkpoint berdasarkan kategori
        $checkpoints = Checkpoint::where('kategori_id', $kategori->id)->get();

        // ambil id checkpoint
        $checkpointIds = $checkpoints->pluck('id');

        // ambil qc report (keyBy check_point_id)
        $qcReports = QcReport::whereIn('check_point_id', $checkpointIds)
            ->where('detail_po_id', $detailPo)
            ->get()
            ->keyBy('check_point_id');

        // ambil semua report_photo SEKALIGUS
        $reportIds = $qcReports->pluck('id');

        $photos = ReportPhoto::whereIn('qc_report_id', $reportIds)
            ->get()
            ->groupBy('qc_report_id'); // 🔥 group per report

        // ===============================
        // MERGE FINAL
        // ===============================
        $merged = [];

        foreach ($checkpoints as $cp) {
            $report = $qcReports[$cp->id] ?? null;

            $merged[$cp->name] = [
                'size'   => $report->size ?? null,
                'remark' => $report->remark ?? null,
                'photos' => $report
                    ? ($photos[$report->id] ?? collect())->map(function ($p) {
                    return [
                        'keterangan' => $p->keterangan,
                        'path'       => $p->path,
                    ];
                })->values()
                    : [],
            ];
        }

        return response()->json([
            'kategori'      => $kategori->kategori,
            'merged_result' => $merged,
        ]);
    }


    public function insertDummy()
    {
        $checkpoints = [
            3, 4, 5, 6, 7, 8, 9, 10,
            11, 12, 13, 14, 15, 16,
            17, 18, 19, 20, 21, 22,
            23, 24, 25, 26, 27, 28,
            29, 30,
        ];

        $po_id        = 7;
        $detail_po_id = 17;

        $remarks = [
            'OK',
            'Minor defect',
            'Aktual lebih 2 cm',
            'Tidak sesuai drawing',
            'Perlu koreksi',
        ];

        DB::beginTransaction();

        try {

            foreach ($checkpoints as $checkpointId) {

                /* ===============================
               INSERT QC REPORT
            =============================== */
                $qcReport = QcReport::create([
                    'check_point_id' => $checkpointId,
                    'po_id'          => $po_id,
                    'detail_po_id'   => $detail_po_id,
                    'size'           => rand(30, 120), // bebas
                    'remark'         => $remarks[array_rand($remarks)],
                ]);

                /* ===============================
               INSERT REPORT PHOTOS (1–3 FOTO)
            =============================== */
                $photoCount = rand(1, 3);

                for ($i = 1; $i <= $photoCount; $i++) {
                    ReportPhoto::create([
                        'qc_report_id' => $qcReport->id,
                        'keterangan'   => 'Foto dummy ' . $i,
                        'path'         => 'uploads/qc/' . Str::random(10) . '.jpg',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Dummy QC Report & Photo berhasil dibuat',
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
