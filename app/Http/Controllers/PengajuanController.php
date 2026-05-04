<?php
namespace App\Http\Controllers;

use App\Models\ApprovalMessage;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Pengajuan;
use App\Models\PengajuanApprovalStep;
use App\Models\PengajuanDetail;
use App\Models\PengajuanMeta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class PengajuanController extends Controller
{
    //
    public function index()
    {
        $divisis = Divisi::orderBy('nama')->get();

        return view('pages.pengajuan.index', compact('divisis'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $type = $request->type_pengajuan;

            // =========================
            // 🔥 ALL DIVISI
            // =========================
            if ($type === 'All Divisi') {

                $pengajuan = Pengajuan::create([
                    'type_pengajuan' => $type,
                    'user_id'        => auth()->id() ?? 1,
                    'status'         => 'pending',
                    'no_spk'         => $request->no_spk ?? '',
                    'keterangan'     => $request->keterangan,
                    'divisi_id'      => $request->divisi_id,
                ]);

                // =========================
                // FILE UPLOAD
                // =========================
                if ($request->hasFile('images')) {

                    foreach ($request->file('images') as $img) {

                        $filename = 'pengajuan_' . time() . '_' . Str::random(5) . '.' . $img->getClientOriginalExtension();

                        $path = $img->storeAs('pengajuan', $filename, 'public');

                        DB::table('pengajuan_files')->insert([
                            'pengajuan_id' => $pengajuan->id,
                            'file_path'    => $path,
                            'type'         => 'image',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Berhasil disimpan (All Divisi)',
                ]);
            }

            // =========================
            // 🔥 FINANCE
            // =========================
            if ($type === 'Finance') {

                $meta     = json_decode($request->meta_json, true);
                $details  = json_decode($request->details_json, true) ?? [];
                $approval = json_decode($request->approval_json, true) ?? [];

                // VALIDASI META
                if (! $meta || ! isset($meta['nomor'])) {
                    return redirect('/pengajuan')
                        ->with('error', 'Meta tidak valid');
                }

                $tanggal = \Carbon\Carbon::createFromFormat('d/m/Y', $meta['tanggal'])->format('Y-m-d');

                // CEK DUPLIKAT
                $exists = PengajuanMeta::where('tanggal', $tanggal)
                    ->where('nomor', $meta['nomor'])
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status'  => false,
                        'message' => '❌ Data sudah ada (Tanggal: ' . $meta['tanggal'] . ' / No: ' . $meta['nomor'] . ')',
                    ], 422);
                }

                // =========================
                // PENGAJUAN
                // =========================
                $pengajuan = Pengajuan::create([
                    'type_pengajuan' => $type,
                    'user_id'        => auth()->id() ?? 1,
                    'status'         => 'pending',
                    'keterangan'     => $request->keterangan,
                ]);

                // =========================
                // META
                // =========================
                PengajuanMeta::create([
                    'pengajuan_id'    => $pengajuan->id,
                    'tanggal'         => $tanggal,
                    'nomor'           => $meta['nomor'],
                    'type_pembayaran' => $meta['type_pembayaran'] ?? null,
                    'total_transfer'  => $meta['transfer'] ?? 0,
                    'grand_total'     => $meta['grand_total'] ?? 0,
                ]);

                // =========================
                // DETAIL
                // =========================
                $insertDetails = [];

                foreach ($details as $d) {

                    if (empty($d['no'])) {
                        continue;
                    }

                    $insertDetails[] = [
                        'pengajuan_id' => $pengajuan->id,
                        'no'           => $d['no'],
                        'date'         => isset($d['date'])
                            ? \Carbon\Carbon::createFromFormat('d/m/Y', $d['date'])->format('Y-m-d')
                            : null,
                        'no_po'        => $d['no_po'],
                        'no_inv'       => $d['no_inv'],
                        'type_biaya'   => $d['type_biaya'],
                        'nama_barang'  => $d['nama_barang'],
                        'qty'          => $d['qty'],
                        'harga_satuan' => $d['harga_satuan'],
                        'total_harga'  => $d['total_harga'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                if (! empty($insertDetails)) {
                    PengajuanDetail::insert($insertDetails);
                }

                // =========================
                // APPROVAL STEP
                // =========================
                $steps = [
                    ['name' => 'Made By', 'user' => auth()->user()->name ?? 'System'],
                    ['name' => 'Checked By', 'user' => 'YANTI SUSANTI'],
                    ['name' => 'RECORD & CASHIED By', 'user' => 'Eka Wahyuning Lestari'],
                    ['name' => 'Knowing By', 'user' => 'Stanley'],
                    ['name' => 'Approve By', 'user' => 'HBJ TANS'],
                    ['name' => 'RECORD & CASHIED By', 'user' => 'Ainunnisyah Uwiyah'],
                ];

                $insertSteps = [];

                foreach ($steps as $i => $s) {
                    $insertSteps[] = [
                        'pengajuan_id' => $pengajuan->id,
                        'step_order'   => $i + 1,
                        'step_name'    => $s['name'],
                        'user_name'    => $s['user'],
                        'status'       => $i === 0 ? 'approved' : 'pending',
                        'approved_at'  => $i === 0 ? now() : null,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                PengajuanApprovalStep::insert($insertSteps);

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => '✅ Pengajuan Finance berhasil disimpan',
                ]);
            }

        } catch (\Exception $e) {

            DB::rollback();

            return redirect('/pengajuan')
                ->with('error', $e->getMessage());
        }
    }
    public function storeAllDivisi(Request $request)
    {
        DB::beginTransaction();

        try {

            if (! $request->hasFile('images')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Foto wajib diisi',
                ], 422);
            }
            if ($request->type_pengajuan === 'Finance' && ! $request->divisi_id) {
                return back()->with('error', 'Divisi wajib diisi untuk Finance');
            }

            // =========================
            // 1. SIMPAN PENGAJUAN
            // =========================

            $pengajuan = Pengajuan::create([
                'type_pengajuan' => $request->type_pengajuan,
                'user_id'        => auth()->id() ?? 1,
                'status'         => 'pending',
                'no_spk'         => $request->no_spk ?? '',
                'keterangan'     => $request->keterangan,
                'divisi_id'      => $request->divisi_id,
            ]);

            // =========================
            // 2. SIMPAN FILE
            // =========================
            foreach ($request->file('images') as $img) {

                $filename = 'pengajuan_' . time() . '_' . Str::random(5) . '.' . $img->getClientOriginalExtension();

                $path = $img->storeAs('pengajuan', $filename, 'public');

                // 🔥 pakai tabel kamu
                DB::table('pengajuan_files')->insert([
                    'pengajuan_id' => $pengajuan->id,
                    'file_path'    => $path,
                    'type'         => 'image', // 🔥 bisa nanti pdf dll
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => '✅ Pengajuan berhasil disimpan',
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reset()
    {
        if (app()->environment('production')) {
            abort(403);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('pengajuan_approval_steps')->truncate();
        DB::table('pengajuan_details')->truncate();
        DB::table('pengajuan_meta')->truncate();
        DB::table('pengajuans')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return "🔥 Reset selesai";
    }
    public function list(Request $request)
    {
        $user     = auth()->user();
        $karyawan = Karyawan::find($user->karyawan_id);

        if (! $karyawan) {
            return response()->json([]);
        }

        $divisiId = $karyawan->divisi_id;

        // 🔥 divisi yang boleh akses Finance & All Divisi
        $allowedDivisi = [26, 38, 32, 46, 37];

        $query = Pengajuan::with(['user', 'approvalSteps','divisi']);

        $type = trim($request->type);

        // =========================
        // 🔥 ADMIN HRD (25)
        // =========================
        if ($divisiId == 25) {

            // ❌ selain All Divisi ditolak
            if ($type && strtolower($type) !== 'all divisi') {
                return response()->json([
                    'message' => 'You are not allowed',
                ], 403);
            }

            // ✅ hanya All Divisi
            $query->where('type_pengajuan', 'All Divisi');
        }

        // =========================
        // 🔥 DIVISI ALLOWED
        // =========================
        elseif (in_array($divisiId, $allowedDivisi)) {

            if ($type) {

                // ❌ selain Finance & All Divisi ditolak
                if (! in_array($type, ['Finance', 'All Divisi'])) {
                    return response()->json([
                        'message' => 'Type not allowed',
                    ], 403);
                }

                $query->where('type_pengajuan', $type);
            }

            // kalau kosong → tampil semua (Finance + All Divisi)
        }

        // =========================
        // 🔥 USER BIASA
        // =========================
        else {

            // ❌ Finance tidak boleh
            if ($type === 'Finance') {
                return response()->json([
                    'message' => 'You are not allowed',
                ], 403);
            }

            // ✅ hanya data milik sendiri
            $query->where('user_id', $user->id);

            if ($type) {
                $query->where('type_pengajuan', $type);
            }
        }

        return response()->json(
            $query->latest()->get()
        );
    }
    public function detail($id)
    {
        $data = Pengajuan::with([
            'user',
            'files',
            'meta',
            'details',
            'approvalSteps.user:id,name,email', // 🔥 FIX
        ])->findOrFail($id);

        // 🔥 HITUNG ON HOLD
        $onHold = $data->details
            ->where('qty', 0)
            ->sum('total_harga');

        // 🔥 HITUNG QTY ON HOLD
        $qtyOnHold = $data->details
            ->where('qty', 0)
            ->count(); // 🔥 lebih masuk akal dari sum qty

        // 🔥 SUNTIK KE META
        if ($data->meta) {
            $data->meta->on_hold     = $onHold;
            $data->meta->qty_on_hold = $qtyOnHold;
        }

        return response()->json($data);
    }
    public function approveAll($id)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 🔥 ambil karyawan
        $karyawan = Karyawan::find($user->karyawan_id);
        $div      = Divisi::find($karyawan->divisi_id);

        // 🔥 VALIDASI DIVISI CO
        if (strtoupper($div->nama) !== 'CO') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya divisi CO yang bisa approve',
            ], 403);
        }

        $pengajuan = Pengajuan::findOrFail($id);

        if ($pengajuan->type_pengajuan !== 'All Divisi') {
            return response()->json([
                'status'  => false,
                'message' => 'Bukan pengajuan All Divisi',
            ], 400);
        }

        $pengajuan->update([
            'remark'        => 'Approved by ' . $user->name,
            'approved_date' => now(),
            'status'        => 'approved',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Berhasil approve',
        ]);
    }
    public function updateMassCustom(Request $request)
    {
        $data = $request->all();

        if (! is_array($data)) {
            return response()->json([
                'status'  => false,
                'message' => 'Format harus array',
            ], 422);
        }

        $updated = 0;

        foreach ($data as $item) {

            // validasi minimal
            if (! isset($item['id'])) {
                continue;
            }

            $user = User::find($item['id']);
            if (! $user) {
                continue;
            }

            if (! empty($item['email'])) {
                $user->email = $item['email'];
            }

            if (! empty($item['password'])) {
                $user->password = Hash::make($item['password']);
            }

            $user->save();
            $updated++;
        }

        return response()->json([
            'status'  => true,
            'message' => 'Berhasil update massal',
            'updated' => $updated,
        ]);
    }
    public function approveStep($id)
    {
        DB::beginTransaction();

        try {

            // =========================
            // 🔥 AMBIL STEP
            // =========================
            $step = PengajuanApprovalStep::findOrFail($id);

            $currentUser = strtolower(trim(auth()->user()->name));
            $stepUser    = strtolower(trim($step->user_name));

            // =========================
            // 🔥 VALIDASI USER
            // =========================
            if ($currentUser !== $stepUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak punya akses approve/step sebelumnya belum di-approve',
                ], 403);
            }

            // =========================
            // 🔥 VALIDASI SUDAH APPROVED
            // =========================
            if ($step->status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah di-approve sebelumnya',
                ], 400);
            }

            // =========================
            // 🔥 OPTIONAL: SEQUENTIAL APPROVAL
            // =========================
            $previousNotApproved = PengajuanApprovalStep::where('pengajuan_id', $step->pengajuan_id)
                ->where('step_order', '<', $step->step_order)
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhere('status', 'pending');
                })
                ->exists();

            if ($previousNotApproved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step sebelumnya belum di-approve',
                ], 400);
            }

            // =========================
            // 🔥 APPROVE STEP
            // =========================
            $step->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            // =========================
            // 🔥 CEK SEMUA STEP
            // =========================
            $remaining = PengajuanApprovalStep::where('pengajuan_id', $step->pengajuan_id)
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhere('status', 'pending');
                })
                ->count();

            // =========================
            // 🔥 UPDATE STATUS PENGAJUAN
            // =========================
            if ($remaining == 0) {
                Pengajuan::where('id', $step->pengajuan_id)
                    ->update([
                        'status'        => 'approved',
                        'approved_date' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Berhasil approve',
                'step_id'      => $step->id,
                'pengajuan_id' => $step->pengajuan_id,
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function uploadDetailImage(Request $request)
    {
        DB::beginTransaction();

        try {

            $detailId = $request->detail_id;

            $detail = \App\Models\PengajuanDetail::findOrFail($detailId);

            foreach ($request->file('images') as $img) {

                $filename = 'detail_' . time() . '_' . Str::random(5) . '.' . $img->getClientOriginalExtension();

                $path = $img->storeAs('pengajuan', $filename, 'public');

                DB::table('pengajuan_files')->insert([
                    'pengajuan_id'        => $detail->pengajuan_id,
                    'pengajuan_detail_id' => $detailId, // 🔥 INI PENTING
                    'file_path'           => $path,
                    'type'                => 'image',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Upload berhasil',
            ]);

        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function viewDetailImage($detailId)
    {
        $files = \App\Models\PengajuanFile::where('pengajuan_detail_id', $detailId)->get();

        if ($files->isEmpty()) {
            abort(404, 'Tidak ada gambar');
        }

        return view('pages.pengajuan.viewer', compact('files'));
    }
    public function detailFile($no_inv)
    {
        // 🔥 cari detail berdasarkan no_inv
        //     $detail = \App\Models\PengajuanDetail::where('no_inv', $no_inv)->first();

        //     if (! $detail) {
        //         abort(404, 'Data tidak ditemukan');
        //     }

        //     // 🔥 ambil pengajuan + files
        //     $pengajuan = \App\Models\Pengajuan::with('files')
        //         ->find($detail->pengajuan_id);

        //     return view('pengajuan.detail-file', [
        //         'detail'    => $detail,
        //         'pengajuan' => $pengajuan,
        //     ]);
        // }
    }
    public function sendMessage(Request $req)
    {

        $msg = ApprovalMessage::create([
            'pengajuan_id' => $req->pengajuan_id,
            'user_id'      => auth()->id(),
            'message'      => $req->message,
        ]);
        return response()->json($msg);
    }
    public function getMessages($id)
    {
        $messages = ApprovalMessage::with('user')
            ->where('pengajuan_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
    public function getNameByEmail($email, $prefix)
    {

        // fallback kalau ga ketemu

        return $email;
    }
    public function pendingMyApproval()
    {
        $user = auth()->user();

        $isCO = in_array($user->email, ['info@newwicker.com']);

        $steps = \App\Models\PengajuanApprovalStep::whereRaw(
            'LOWER(user_name) = ?',
            [strtolower($user->name)]
        )
            ->where('status', 'pending')
            ->get();

        $stepCount = $steps->count();

        $pengajuanIds = $steps->pluck('pengajuan_id')->unique();

        $data = \App\Models\Pengajuan::whereIn('id', $pengajuanIds)
            ->latest()
            ->get();

        if ($isCO) {
            $coData = \App\Models\Pengajuan::where('type_pengajuan', 'All Divisi')
                ->whereNull('remark')
                ->get();

            $data = $data->merge($coData)
                ->unique('id')
                ->values();
        }

        return response()->json([
            'total_pengajuan' => $data->count(),
            'total_step'      => $stepCount,
            'data'            => $data,
        ]);
    }
    public function exportExcel($id)
    {
        $pengajuan = Pengajuan::with(['meta', 'details', 'user'])->findOrFail($id);

        $templatePath = storage_path('app/templates/finance_template.xlsx');

        if (! file_exists($templatePath)) {
            abort(404, 'Template tidak ditemukan');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getActiveSheet();

        // =========================
        // 🔥 META
        // =========================
        $sheet->setCellValue('C5', $pengajuan->meta->tanggal ?? '-');
        $sheet->setCellValue('C6', $pengajuan->meta->nomor ?? '-');
        $sheet->setCellValue('E6', $pengajuan->meta->type_pembayaran ?? '-');

                                   // =========================
                                   // 🔥 SETUP TEMPLATE POSITION
                                   // =========================
        $startRow            = 11; // 🔥 DATA MULAI DI SINI (PENTING)
        $footerTemplateRow   = 12; // TRANSFER
        $approvalTemplateRow = 18; // HEADER APPROVAL

        $details     = $pengajuan->details ?? [];
        $totalDetail = count($details);

        $extraRows = max(0, $totalDetail - 1);

        // =========================
        // 🔥 SHIFT FOOTER & APPROVAL
        // =========================
        if ($extraRows > 0) {
            $sheet->insertNewRowBefore($footerTemplateRow, $extraRows);
        }

        // =========================
        // 🔥 ISI DATA
        // =========================
        $row = $startRow;

        foreach ($details as $i => $d) {

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $d->date);
            $sheet->setCellValue('C' . $row, $d->no_po);
            $sheet->setCellValue('D' . $row, $d->no_inv);
            $sheet->setCellValue('E' . $row, $d->type_biaya);
            $sheet->setCellValue('F' . $row, $d->nama_barang);
            $sheet->setCellValue('G' . $row, $d->qty);
            $sheet->setCellValue('H' . $row, $d->harga_satuan);
            $sheet->setCellValue('I' . $row, $d->total_harga);

            // copy style biar tidak rusak
            $sheet->duplicateStyle(
                $sheet->getStyle('A' . $startRow . ':I' . $startRow),
                'A' . $row . ':I' . $row
            );

            $row++;
        }

        // =========================
        // 🔥 HITUNG POSISI BARU
        // =========================
        $footerRow   = $footerTemplateRow + $extraRows;
        $approvalRow = $approvalTemplateRow + $extraRows;

        // =========================
        // 🔥 TRANSFER
        // =========================
        $sheet->setCellValue('H' . $footerRow, $pengajuan->meta->transfer ?? 0);

        // =========================
        // 🔥 TOTAL (RUMUS EXCEL)
        // =========================
        $lastDataRow = $startRow + $totalDetail - 1;

        $sheet->setCellValue(
            'H' . ($footerRow + 1),
            "=SUM(I" . $startRow . ":I" . $lastDataRow . ")"
        );

        // =========================
        // 🔥 APPROVAL TEXT
        // =========================
        $nameRow = $approvalRow + 4;

        $sheet->setCellValue('B' . $nameRow, $pengajuan->user->name ?? '-');
        $sheet->setCellValue('D' . $nameRow, 'YANTI SUSANTI');
        $sheet->setCellValue('E' . $nameRow, 'Mr Stanley');
        $sheet->setCellValue('F' . $nameRow, 'EKA WL');
        $sheet->setCellValue('G' . $nameRow, 'Mr Jan');
        $sheet->setCellValue('I' . $nameRow, 'AINUN');

        // =========================
        // 🔥 TTD IMAGE
        // =========================
        $ttdRow = $approvalRow + 1;

        $this->insertSignature($sheet, '1.png', 'B' . $ttdRow);
        $this->insertSignature($sheet, '2.png', 'D' . $ttdRow);
        $this->insertSignature($sheet, '4.png', 'E' . $ttdRow);
        $this->insertSignature($sheet, '3.png', 'F' . $ttdRow);
        $this->insertSignature($sheet, '5.png', 'G' . $ttdRow);
        $this->insertSignature($sheet, '6.png', 'I' . $ttdRow);

        // =========================
        // 🔥 FORMAT ANGKA
        // =========================
        for ($i = $startRow; $i <= $lastDataRow; $i++) {
            $sheet->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->getStyle('H' . $footerRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H' . ($footerRow + 1))->getNumberFormat()->setFormatCode('#,##0');

        // =========================
        // 🔥 DOWNLOAD
        // =========================
        $fileName = 'Pengajuan_' . $pengajuan->id . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    // =========================
    // 🔥 INSERT TTD
    // =========================
    private function insertSignature($sheet, $filename, $cell)
    {
        $path = public_path('assets/' . $filename);

        if (! file_exists($path)) {
            \Log::error('TTD tidak ditemukan: ' . $path);
            return;
        }

        $drawing = new Drawing();
        $drawing->setName('TTD');
        $drawing->setDescription('Signature');
        $drawing->setPath($path);
        $drawing->setHeight(70);
        $drawing->setCoordinates($cell);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }
    public function destroy($id)
{
    DB::beginTransaction();

    try {
        $pengajuan = Pengajuan::with('files')->findOrFail($id);

        // 🔥 VALIDASI: hanya owner yang boleh delete
        if ($pengajuan->user_id != auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak punya akses hapus'
            ], 403);
        }

        // =========================
        // 🔥 HAPUS FILE STORAGE
        // =========================
        foreach ($pengajuan->files as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        // =========================
        // 🔥 HAPUS RELASI
        // =========================
        DB::table('pengajuan_files')->where('pengajuan_id', $id)->delete();
        DB::table('pengajuan_details')->where('pengajuan_id', $id)->delete();
        DB::table('pengajuan_meta')->where('pengajuan_id', $id)->delete();
        DB::table('pengajuan_approval_steps')->where('pengajuan_id', $id)->delete();

        // =========================
        // 🔥 HAPUS PENGAJUAN
        // =========================
        $pengajuan->delete();

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Pengajuan berhasil dihapus'
        ]);

    } catch (\Exception $e) {

        DB::rollback();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
