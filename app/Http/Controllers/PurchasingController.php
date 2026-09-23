<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\TransaksiStok;
use App\Models\Pengajuan;
use App\Models\PengajuanMeta;
use App\Models\PengajuanDivisi;
use App\Models\PengajuanApprovalStep;
use App\Models\User;
use App\Models\Divisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PengajuanFile;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class PurchasingController extends Controller
{
    /**
     * Halaman Pengajuan Barang Inventory
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        $karyawanById = Karyawan::with('divisi')
            ->whereIn('id', $users->pluck('karyawan_id')->filter()->unique())
            ->get()
            ->keyBy('id');
        $divisis = Divisi::orderBy('nama')->get();

        $pengajuans = Pengajuan::with([
            'user',
            'divisi',
            'meta',
            'divisiItems',
            'files'

        ])
            ->where('type_pengajuan', 'purchasing')
            ->orderByDesc('id')
            ->get();

        return view('pages.purchasing.index', compact(
            'users',
            'divisis',
            'pengajuans'
        ));
    }


    /**
     * Pencarian barang dari inventory/gudang
     */
    public function searchBarang(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if ($keyword === '') {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $stoks = Stok::query()
            ->where(function ($query) use ($keyword) {
                $query->where(
                    'kode_barang',
                    'like',
                    '%' . $keyword . '%'
                )
                    ->orWhere(
                        'nama_barang',
                        'like',
                        '%' . $keyword . '%'
                    );
            })
            ->orderBy('nama_barang')
            ->limit(20)
            ->get();

        $data = $stoks->map(function ($stok) {

            $totalIn = $stok->transaksi()
                ->where('tipe', 'in')
                ->sum('qty');

            $totalOut = $stok->transaksi()
                ->where('tipe', 'out')
                ->sum('qty');

            $stokAkhir =
                (float) $stok->stok_awal
                + (float) $totalIn
                - (float) $totalOut;

            return [
                'id' => $stok->id,
                'kode_barang' => $stok->kode_barang,
                'nama_barang' => $stok->nama_barang,
                'jenis' => $stok->jenis,
                'satuan' => $stok->satuan,
                'harga' => (float) $stok->harga,
                'stok_awal' => (float) $stok->stok_awal,
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'stok_akhir' => $stokAkhir,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    /**
     * Detail satu barang
     */
    public function detailBarang($id)
    {
        $stok = Stok::findOrFail($id);

        $totalIn = $stok->transaksi()
            ->where('tipe', 'in')
            ->sum('qty');

        $totalOut = $stok->transaksi()
            ->where('tipe', 'out')
            ->sum('qty');

        $stokAkhir =
            (float) $stok->stok_awal
            + (float) $totalIn
            - (float) $totalOut;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $stok->id,
                'kode_barang' => $stok->kode_barang,
                'nama_barang' => $stok->nama_barang,
                'jenis' => $stok->jenis,
                'satuan' => $stok->satuan,
                'harga' => (float) $stok->harga,
                'stok_awal' => (float) $stok->stok_awal,
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'stok_akhir' => $stokAkhir,
            ]
        ]);
    }


    /**
     * Simpan Pengajuan Purchasing
     */
    public function saveDraft(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'pengajuan_id' => 'nullable|integer',

            'tanggal' => 'required|date',

            /*
             * Dari Blade saat ini yang dikirim adalah:
             * Purchasing
             * Production
             * Warehouse
             * dst.
             */
            'divisi_id' => 'required|integer',

            'need_date' => 'nullable|date',

            'items' => 'required|array|min:1',

            /*
             * Detail masing-masing row
             */
            'items.*.id_stock' => 'nullable|integer',

            'items.*.nama_barang' =>
                'required|string|max:255',

            'items.*.po_no' =>
                'nullable|string|max:255',

            'items.*.supplier' =>
                'nullable|string|max:255',

            'items.*.payment' =>
                'nullable|string|max:100',

            'items.*.description' =>
                'nullable|string',

            'items.*.keterangan' =>
                'nullable|string',

            'items.*.qty' =>
                'required|numeric|min:0.01',

            'items.*.unit' =>
                'nullable|string|max:50',

            'items.*.price' =>
                'nullable|numeric|min:0',

            'items.*.status' =>
                'nullable|string|in:urgent,non urgent,terjadwal',

            /* Attachment disimpan bersamaan saat tombol Simpan ditekan */
            'images' => 'nullable|array|max:200',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            /*
             * Signature
             */
            'signature' => 'nullable|array',

            'signature.checked_by_1' =>
                'nullable|integer',

            'signature.checked_by_2' =>
                'nullable|integer',

            'signature.checked_by_3' =>
                'nullable|integer',

            'signature.checked_by_4' =>
                'nullable|integer',

            'signature.checked_by_finance' =>
                'nullable|integer',

            'signature.approved_by' =>
                'nullable|integer',
        ]);


        try {

            /*
            |--------------------------------------------------------------------------
            | CARI DIVISI
            |--------------------------------------------------------------------------
            */

            // Hanya pembuat pengajuan yang boleh mengubah pengajuan.
            if ($request->filled('pengajuan_id')) {
                $targetPengajuan = Pengajuan::where('type_pengajuan', 'purchasing')
                    ->findOrFail($request->pengajuan_id);

                if ((int) $targetPengajuan->user_id !== (int) auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak untuk mengubah pengajuan ini.'
                    ], 403);
                }

                /*
                 * Setelah dipublish, jangan izinkan saveDraft lagi.
                 * Jika tetap disimpan, approvalSteps akan dihapus dan
                 * tanda tangan yang sudah masuk bisa hilang.
                 */
                if ((int) ($targetPengajuan->is_draft ?? 0) === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pengajuan sudah dipublish dan tidak dapat diedit lagi.'
                    ], 403);
                }
            }


            $divisiId = (int) $request->input('divisi_id');

            /*
             * Jangan gunakan validation `exists:divisi,id` di sini.
             * Nama tabel master mengikuti konfigurasi/model Divisi.
             * Cari menggunakan Eloquent agar Laravel memakai table yang
             * memang didefinisikan oleh model Divisi.
             */
            $divisi = Divisi::find($divisiId);

            if (!$divisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departemen "' . $divisiId . '" tidak ditemukan di master divisi.'
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | TRANSACTION
            |--------------------------------------------------------------------------
            */

            $pengajuan = DB::transaction(function () use ($request, $divisiId) {

                /*
                |--------------------------------------------------------------------------
                | 1. PENGAJUAN
                |--------------------------------------------------------------------------
                */

                if ($request->filled('pengajuan_id')) {

                    $pengajuan =
                        Pengajuan::findOrFail(
                            $request->pengajuan_id
                        );

                    /*
                     * Hanya status pending yang dianggap draft
                     */
                    if ((int) $pengajuan->user_id !== (int) auth()->id()) {
                        throw new \Exception(
                            'Anda tidak memiliki hak untuk mengubah pengajuan ini.'
                        );
                    }

                    if (
                        $pengajuan->status !== 'pending'
                    ) {
                        throw new \Exception(
                            'Pengajuan ini sudah tidak berstatus draft.'
                        );
                    }

                    $pengajuan->update([
                        'type_pengajuan' =>
                            'purchasing',

                        'divisi_id' =>
                            $divisiId,

                        'need_date' =>
                            $request->input('need_date') ?: null,

                        'urgent' =>
                            0,

                        'is_draft' =>
                            0,
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Buat pengajuan baru
                    |--------------------------------------------------------------------------
                    */

                    $pengajuan =
                        Pengajuan::create([
                            'type_pengajuan' =>
                                'purchasing',

                            'user_id' =>
                                auth()->id(),

                            /*
                             * Status pending karena status
                             * "is_draft" tidak valid pada database.
                             */
                            'status' =>
                                'pending',

                            'divisi_id' =>
                                $divisiId,

                            'need_date' =>
                                $request->input('need_date') ?: null,

                            'urgent' =>
                                0,

                            'is_draft' =>
                                0,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | 2. PENGAJUAN META
                |--------------------------------------------------------------------------
                */

                PengajuanMeta::updateOrCreate(
                    [
                        'pengajuan_id' =>
                            $pengajuan->id,
                    ],
                    [
                        'tanggal' =>
                            $request->tanggal,

                        'nomor' =>
                            null,

                        'type_pembayaran' =>
                            'purchasing',
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | 3. DETAIL BARANG
                |--------------------------------------------------------------------------
                |
                | Setiap row mempunyai:
                |
                | - id_stock
                | - nama_barang
                | - po_no
                | - supplier
                | - description
                | - keterangan
                | - qty
                | - unit
                | - price
                |
                |--------------------------------------------------------------------------
                */

                /*
                 * Hapus detail lama terlebih dahulu.
                 *
                 * Supaya ketika user klik Simpan Draft
                 * berkali-kali tidak terjadi duplicate.
                 */
                PengajuanDivisi::where(
                    'pengajuan_id',
                    $pengajuan->id
                )->delete();


                foreach ($request->items as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | Ambil id stock
                    |--------------------------------------------------------------------------
                    */

                    $idStock =
                        !empty($item['id_stock'])
                        ? $item['id_stock']
                        : null;


                    /*
                    |--------------------------------------------------------------------------
                    | Simpan detail masing-masing row
                    |--------------------------------------------------------------------------
                    */

                    $detail = PengajuanDivisi::create([
                        'pengajuan_id' => $pengajuan->id,

                        'id_stock' => $idStock,

                        'divisi_id' => $divisiId,

                        'nama_barang' => $item['nama_barang'],

                        'po_no' => $item['po_no'] ?? null,

                        'supplier' => $item['supplier'] ?? null,

                        'payment_type' => $item['payment'] ?? null,

                        'description' => $item['description'] ?? null,

                        'keterangan' => $item['keterangan'] ?? null,

                        'qty' => $item['qty'],

                        'unit' => $item['unit'] ?? null,

                        'price' => $item['price'] ?? 0,

                        'added_to_warehouse' => !empty($idStock) ? 0 : 0,
                    ]);

                    // Status item disimpan terpisah agar tidak bergantung
                    // pada $fillable di model PengajuanDivisi.
                    $detail->status = $item['status'] ?? 'urgent';
                    $detail->save();
                }


                /*
                |--------------------------------------------------------------------------
                | 4. APPROVAL / SIGNATURE
                |--------------------------------------------------------------------------
                */

                /*
                 * Hapus approval lama ketika draft
                 * disimpan ulang.
                 */
                PengajuanApprovalStep::where(
                    'pengajuan_id',
                    $pengajuan->id
                )->delete();


                $signature =
                    $request->input(
                        'signature',
                        []
                    );


                /*
                |--------------------------------------------------------------------------
                | MADE BY
                |--------------------------------------------------------------------------
                */

                PengajuanApprovalStep::create([

                    'pengajuan_id' =>
                        $pengajuan->id,

                    'step_order' =>
                        1,

                    'step_name' =>
                        'Made by',

                    'user_name' =>
                        auth()->user()->name,

                    'status' =>
                        'pending',

                    'approved_at' =>
                        null,
                ]);


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY APPROVER 1
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    2,
                    'Checked by',
                    $signature['checked_by_1'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY APPROVER 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    3,
                    'Checked by',
                    $signature['checked_by_2'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY APPROVER 1 GROUP 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    4,
                    'Checked by',
                    $signature['checked_by_3'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY APPROVER 2 GROUP 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    5,
                    'Checked by',
                    $signature['checked_by_4'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | FINANCE
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    6,
                    'Checked by Finance',
                    $signature['checked_by_finance'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | APPROVED BY
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    7,
                    'Approved by',
                    $signature['approved_by'] ?? null
                );


                return $pengajuan;
            });



            /*
            |--------------------------------------------------------------------------
            | 5. ATTACHMENT
            |--------------------------------------------------------------------------
            | Attachment baru hanya disimpan ketika tombol SIMPAN ditekan.
            | File yang sudah ada tidak dihapus saat edit.
            */

            $uploadedFiles = [];

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $filename = 'pengajuan_' .
                        $pengajuan->id . '_' .
                        time() . '_' .
                        \Illuminate\Support\Str::random(8) . '.' .
                        $image->getClientOriginalExtension();

                    $path = $image->storeAs(
                        'pengajuan',
                        $filename,
                        'public'
                    );

                    $file = PengajuanFile::create([
                        'pengajuan_id' => $pengajuan->id,
                        'file_path' => $path,
                        'type' => 'image',
                    ]);

                    $uploadedFiles[] = [
                        'id' => $file->id,
                        'file_path' => $file->file_path,
                        'url' => Storage::disk('public')->url($file->file_path),
                    ];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,

                'message' =>
                    'Pengajuan purchasing berhasil disimpan.',

                'pengajuan_id' =>
                    $pengajuan->id,

                'status' =>
                    $pengajuan->status,

                'files' =>
                    $uploadedFiles,
            ]);


        } catch (\Throwable $e) {

            Log::error(
                'SAVE DRAFT PURCHASING ERROR',
                [
                    'message' =>
                        $e->getMessage(),

                    'line' =>
                        $e->getLine(),

                    'file' =>
                        $e->getFile(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );


            return response()->json([
                'success' => false,

                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Tanda tangan / approval per step.
     *
     * Hanya user yang namanya tercantum pada step tersebut
     * yang boleh melakukan approval.
     *
     * step_order:
     * 2 = Checked by Person 1
     * 3 = Checked by Person 2
     * 4 = Checked by Person 1 Group 2
     * 5 = Checked by Person 2 Group 2
     * 6 = Finance
     * 7 = Approved by
     */
    public function approveStep(Request $request, $id)
    {
        try {
            $request->validate([
                'step_order' => 'required|integer|in:2,3,4,5,6,7',
            ]);

            $pengajuan = Pengajuan::where('type_pengajuan', 'purchasing')
                ->findOrFail($id);

            // Approval hanya boleh dilakukan setelah pengajuan dipublish.
            if ((int) ($pengajuan->is_draft ?? 0) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan belum dipublish. Approval belum dapat dilakukan.',
                ], 422);
            }

            $stepOrder = (int) $request->input('step_order');

            $step = PengajuanApprovalStep::where('pengajuan_id', $pengajuan->id)
                ->where('step_order', $stepOrder)
                ->first();

            if (!$step) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval step tidak ditemukan.',
                ], 404);
            }

            // Identitas approver HARUS sama dengan nama yang sudah ditentukan
            // pada step. User tidak boleh memilih nama lain.
            $currentUserName = (string) auth()->user()->name;
            $assignedUserName = (string) ($step->user_name ?? '');

            if ($assignedUserName === '' || $assignedUserName !== $currentUserName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk melakukan tanda tangan pada step ini. Tanda tangan hanya dapat dilakukan oleh "' .
                        ($assignedUserName ?: 'user yang ditentukan') . '".',
                ], 403);
            }

            if (strtolower((string) $step->status) === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Step ini sudah ditandatangani.',
                ], 422);
            }

            $step->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan berhasil disimpan.',
                'pengajuan_id' => $pengajuan->id,
                'step_order' => $stepOrder,
                'user_name' => $currentUserName,
                'status' => 'approved',
                'approved_at' => optional($step->approved_at)->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('APPROVE PURCHASING ERROR', [
                'pengajuan_id' => $id,
                'step_order' => $request->input('step_order'),
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan tanda tangan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Membuat approval step.
     */
    private function createApprovalStep(
        $pengajuanId,
        $stepOrder,
        $stepName,
        $userId
    ) {

        $userName = null;

        if ($userId) {

            $user = User::find($userId);

            if ($user) {
                $userName = $user->name;
            }
        }


        PengajuanApprovalStep::create([

            'pengajuan_id' =>
                $pengajuanId,

            'step_order' =>
                $stepOrder,

            'step_name' =>
                $stepName,

            'user_name' =>
                $userName,

            'status' =>
                'pending',

            'approved_at' =>
                null,
        ]);
    }
    public function uploadAttachments(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Hanya pembuat pengajuan yang boleh upload
        if ((int) $pengajuan->user_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat pengajuan yang dapat menambahkan attachment.'
            ], 403);
        }

        $request->validate([
            'images' => 'required|array|max:200',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ], [
            'images.required' => 'Silakan pilih gambar terlebih dahulu.',
            'images.max' => 'Maksimal 200 gambar.',
            'images.*.image' => 'File harus berupa gambar.',
            'images.*.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 10 MB.',
        ]);

        $uploaded = [];

        foreach ($request->file('images', []) as $image) {

            $filename = 'pengajuan_' .
                $pengajuan->id . '_' .
                time() . '_' .
                \Illuminate\Support\Str::random(8) . '.' .
                $image->getClientOriginalExtension();

            $path = $image->storeAs(
                'pengajuan',
                $filename,
                'public'
            );

            $file = PengajuanFile::create([
                'pengajuan_id' => $pengajuan->id,
                'file_path' => $path,
                'type' => 'image',
            ]);

            $uploaded[] = [
                'id' => $file->id,
                'file_path' => $file->file_path,
                'url' => Storage::disk('public')->url($file->file_path),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' gambar berhasil diupload.',
            'files' => $uploaded,
        ]);
    }
    public function deleteAttachment($id, $fileId)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Hanya pembuat yang boleh menghapus
        if ((int) $pengajuan->user_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembuat pengajuan yang dapat menghapus attachment.'
            ], 403);
        }

        $file = PengajuanFile::where('id', $fileId)
            ->where('pengajuan_id', $pengajuan->id)
            ->firstOrFail();

        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment berhasil dihapus.'
        ]);
    }
    /**
     * Publish pengajuan purchasing.
     *
     * is_draft:
     * 0 = draft / belum publish
     * 1 = published
     */
    public function publish($id)
    {
        try {
            $pengajuan = Pengajuan::where('type_pengajuan', 'purchasing')
                ->findOrFail($id);

            // Publish hanya dari kondisi draft.
            if ((int) ($pengajuan->is_draft ?? 0) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan ini sudah dipublish.'
                ], 422);
            }

            $pengajuan->update([
                'is_draft' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan #' . $pengajuan->id . ' berhasil dipublish.',
                'pengajuan_id' => $pengajuan->id,
                'is_draft' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::error('PUBLISH PURCHASING ERROR', [
                'pengajuan_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal publish pengajuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $users = User::orderBy('name')->get();

        // Mapping user -> karyawan -> divisi.
        // Variabel ini wajib dibuat di dalam edit() karena
        // approval_steps di bawah juga diproses di dalam edit().
        $karyawanById = Karyawan::with('divisi')
            ->whereIn('id', $users->pluck('karyawan_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $divisis = Divisi::orderBy('nama')->get();

        $pengajuans = Pengajuan::with([
            'user',
            'divisi',
            'meta',
            'divisiItems.stok',
            'files'

        ])
            ->where('type_pengajuan', 'purchasing')
            ->orderByDesc('id')
            ->get();

        $editPengajuan = Pengajuan::with([
            'user',
            'divisi',
            'meta',
            'divisiItems.stok',
            'approvalSteps',
            'files'
        ])
            ->where('type_pengajuan', 'purchasing')
            ->findOrFail($id);
        $canEdit = (int) $editPengajuan->user_id === (int) auth()->id();
        $editData = [
            'id' => $editPengajuan->id,
            'tanggal' => optional($editPengajuan->meta)->tanggal
                ? \Carbon\Carbon::parse($editPengajuan->meta->tanggal)->format('Y-m-d')
                : '',
            'divisi_id' => $editPengajuan->divisi_id,
            'need_date' => $editPengajuan->need_date ?? '',
            'items' => $editPengajuan->divisiItems->map(function ($item) {
                return [
                    'detail_id' => $item->id,
                    'id' => $item->id_stock,

                    'code' => optional($item->stok)->kode_barang ?? '',
                    'name' => $item->nama_barang,
                    'jenis' => optional($item->stok)->jenis ?? '',

                    'warehouse' => $item->id_stock
                        ? 'Gudang Utama'
                        : 'Belum ada di inventory',

                    'stock' => $item->id_stock
                        ? (float) (optional($item->stok)->stok_awal ?? 0)
                        : 0,

                    'qty' => (float) $item->qty,
                    'unit' => $item->unit ?? optional($item->stok)->satuan ?? '',

                    'reason' => '',

                    'supplier' => $item->supplier ?? '',
                    'po_no' => $item->po_no ?? '',

                    // PERBAIKAN PAYMENT
                    'payment' => $item->payment_type ?? '',

                    'description' => $item->description ?? '',
                    'keterangan' => $item->keterangan ?? '',

                    'unit_price' => (float) ($item->price ?? 0),

                    'total' => (float) (
                        ($item->price ?? 0) * ($item->qty ?? 0)
                    ),

                    'status' => $item->status ?? 'urgent',

                    'is_new' => empty($item->id_stock),


                    'added_to_warehouse' => (bool) $item->added_to_warehouse,
                ];
            })->values(),
            'files' => $editPengajuan->files
                ->where('type', 'image')
                ->map(function ($file) {
                    $path = ltrim((string) $file->file_path, '/');

                    return [
                        'id' => $file->id,
                        'file_path' => $path,
                        'url' => $path !== ''
                            ? Storage::disk('public')->url($path)
                            : null,
                    ];
                })
                ->values()
                ->all(),

            'signature' => [
                'checked_by_1' => null,
                'checked_by_2' => null,
                'checked_by_3' => null,
                'checked_by_4' => null,
                'checked_by_finance' => null,
                'approved_by' => null,
            ],
            'approval_steps' => [],
        ];

        foreach ($editPengajuan->approvalSteps as $step) {
            $order = (int) $step->step_order;
            $keyMap = [
                2 => 'checked_by_1',
                3 => 'checked_by_2',
                4 => 'checked_by_3',
                5 => 'checked_by_4',
                6 => 'checked_by_finance',
                7 => 'approved_by',
            ];

            if (isset($keyMap[$order]) && $step->user_name) {
                $user = $users->firstWhere('name', $step->user_name);
                $editData['signature'][$keyMap[$order]] = $user?->id;
            }

            if ($order >= 2 && $order <= 7) {
                $user = $step->user_name
                    ? $users->firstWhere('name', $step->user_name)
                    : null;

                $editData['approval_steps'][] = [
                    'id' => $step->id,
                    'step_order' => $order,
                    'step_name' => $step->step_name,
                    'user_id' => $user?->id,
                    'user_name' => $step->user_name,
                    'division_name' => $user?->karyawan_id
                        ? optional(optional($karyawanById->get($user->karyawan_id))->divisi)->nama
                        : null,
                    'status' => $step->status,
                    'approved_at' => optional($step->approved_at)->toDateTimeString(),
                ];
            }
        }

        return view('pages.purchasing.index', compact(
            'users',
            'divisis',
            'pengajuans',
            'editPengajuan',
            'editData',
            'canEdit'
        ));
    }



    public function addToWarehouse($id)
    {
        if (strtolower((string) optional(auth()->user())->email) !== 'sumanti@gmail.com') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menambahkan barang ke warehouse.',
            ], 403);
        }

        try {
            $result = DB::transaction(function () use ($id) {
                $item = PengajuanDivisi::with(['pengajuan', 'stok'])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $pengajuan = $item->pengajuan;

                if (!$pengajuan || $pengajuan->type_pengajuan !== 'purchasing') {
                    throw new \Exception('Data pengajuan purchasing tidak ditemukan.');
                }
                if ((int) ($pengajuan->is_draft ?? 0) !== 1) {
                    throw new \Exception('Pengajuan belum dipublish.');
                }
                $approvalSteps = PengajuanApprovalStep::where('pengajuan_id', $pengajuan->id)
                    ->whereIn('step_order', [2, 3, 4, 5, 6, 7])
                    ->get();
                if (
                    $approvalSteps->count() !== 6 || $approvalSteps->contains(function ($step) {
                        return strtolower((string) $step->status) !== 'approved';
                    })
                ) {
                    throw new \Exception('Pengajuan belum selesai approval. Semua approval harus sudah TTD terlebih dahulu.');
                }

                if ((bool) ($item->added_to_warehouse ?? false)) {
                    throw new \Exception('Barang ini sudah ditambahkan ke warehouse.');
                }

                $qty = (float) $item->qty;
                if ($qty <= 0) {
                    throw new \Exception('Qty barang tidak valid.');
                }

                $stok = null;

                // Jika detail purchasing sudah menunjuk ke stok, gunakan stok tersebut.
                if (!empty($item->id_stock)) {
                    $stok = Stok::lockForUpdate()->find($item->id_stock);
                }

                // Untuk barang baru, cek lagi berdasarkan nama barang yang sama persis.
                if (!$stok) {
                    $namaBarang = trim((string) $item->nama_barang);
                    $stok = Stok::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower($namaBarang)])
                        ->lockForUpdate()
                        ->first();
                }

                // Jika belum ada di inventory, buat master stok baru.
                if (!$stok) {
                    $namaBarang = trim((string) $item->nama_barang);
                    $unit = trim((string) ($item->unit ?? '')) ?: 'pcs';
                    $harga = (float) ($item->price ?? 0);

                    // Kolom jenis di master Stok wajib dan berupa enum.
                    // Barang baru dari form purchasing belum menyimpan jenis,
                    // sehingga default yang aman untuk purchasing adalah bahan penolong.
                    $kodeBarang = 'PUR-' . date('ymdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

                    $stok = Stok::create([
                        'kode_barang' => $kodeBarang,
                        'nama_barang' => $namaBarang,
                        'jenis' => 'bahan penolong',
                        'satuan' => $unit,
                        'harga' => $harga,
                        'stok_awal' => 0,
                    ]);
                }

                // Harga master hanya diisi jika transaksi berasal dari barang baru / harga master masih 0.
                // Untuk barang existing, harga master tidak diubah.
                TransaksiStok::create([
                    'stok_id' => $stok->id,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'in',
                    'qty' => $qty,
                    'po' => $item->po_no ?? null,
                    'spk_id' => null,
                    'keterangan' => 'Pembelian Purchasing #' . $pengajuan->id . ' - ' . $item->nama_barang,
                    'harga_vivi' => $item->price ?? null,
                    'no_invoice' => null,
                ]);

                // Hubungkan item purchasing ke stok dan tandai sudah masuk warehouse.
                $item->update([
                    'id_stock' => $stok->id,
                    'added_to_warehouse' => 1,
                ]);

                return [
                    'item_id' => $item->id,
                    'stok_id' => $stok->id,
                    'kode_barang' => $stok->kode_barang,
                    'nama_barang' => $stok->nama_barang,
                    'qty' => $qty,
                    'unit' => $item->unit ?? $stok->satuan,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan ke warehouse sebagai transaksi IN.',
                'data' => $result,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Detail barang pengajuan tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('ADD TO WAREHOUSE ERROR', [
                'item_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export Pengajuan Purchasing ke Excel menggunakan template.
     *
     * Template:
     * storage/app/templates/templates-pengajuan.xlsx
     */
    public function exportpurchasing($id)
    {
        try {
            $pengajuan = Pengajuan::with([
                'user',
                'divisi',
                'meta',
                'divisiItems.stok',
                'approvalSteps',
                'files',
            ])
                ->where('type_pengajuan', 'purchasing')
                ->findOrFail($id);

            $templatePath = storage_path(
                'app/templates/templates-pengajuan-approver.xlsx'
            );

            if (!is_file($templatePath)) {
                abort(
                    500,
                    'Template Excel tidak ditemukan: ' . $templatePath
                );
            }

            $spreadsheet = IOFactory::load($templatePath);

            $sheet = $spreadsheet->getSheetByName('mizan (4)')
                ?: $spreadsheet->getActiveSheet();

            /*
             * ============================================================
             * IMPORTANT: HAPUS DRAWING DARI TEMPLATE
             * ============================================================
             *
             * Template mempunyai object/shape lama pada area signature.
             * Kalau dibiarkan, object tersebut akan tetap ikut diekspor
             * dan menimbulkan kotak putih/duplicate text seperti pada
             * hasil sebelumnya.
             *
             * PhpSpreadsheet mendukung collection Drawing, tetapi tidak
             * menyediakan API native untuk membuat Excel "Insert Shape"
             * seperti UI Excel. Karena itu kita gunakan:
             *
             * - border + merged cells sebagai shape/container signature
             * - Drawing floating untuk file PNG TTD
             *
             * Drawing dibuat dengan ukuran FIXED sehingga tidak mengikuti
             * tinggi row.
             */
            /*
             * PENTING:
             * Jangan menghapus Drawing pada sheet utama.
             * Logo NewWicker berasal dari Drawing di template Excel.
             * TTD baru akan ditambahkan sebagai Drawing terpisah.
             */

            /*
             * ============================================================
             * HEADER
             * ============================================================
             */
            $tanggal = optional($pengajuan->meta)->tanggal;
            $needDate = $pengajuan->need_date;

            $tanggalText = $tanggal
                ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
                : '-';

            $needDateText = $needDate
                ? \Carbon\Carbon::parse($needDate)->format('d-M-y')
                : '-';

            $departmentName = optional($pengajuan->divisi)->nama
                ?? optional($pengajuan->divisi)->name
                ?? '-';

            $madeByName = optional($pengajuan->user)->name ?? '-';

            $sheet->setCellValue(
                'B9',
                'Requisition Date : ' . $tanggalText
            );
            $sheet->setCellValue(
                'H9',
                'Department : ' . $departmentName
            );
            /*
             * NEED BY DATE:
             * Template sudah mempunyai SATU kotak Need by Date di P6:P7.
             * Jangan membuat kotak/header kedua di L9:P10.
             */
            $sheet->setCellValue('P6', 'Need by Date :');
            $sheet->setCellValue('P7', $needDate ? \Carbon\Carbon::parse($needDate)->format('d-M-y') : '-');

            $sheet->getStyle('P6:P7')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getStyle('P6')
                ->getFont()
                ->setBold(true);

            $sheet->getStyle('P7')
                ->getNumberFormat()
                ->setFormatCode('d-M-yy');

            /*
             * Hapus hanya ISI Need by Date lama di header tabel.
             * BORDER TEMPLATE TIDAK disentuh, karena area ini masih merupakan
             * bagian dari header Purchase Request.
             */
            $sheet->setCellValue('L9', null);
            $sheet->setCellValue('L10', null);

            $sheet->setCellValue(
                'C10',
                'Made by : ' . $madeByName
            );

            /*
             * Pastikan border header tabel tetap utuh.
             * Area L9:P10 tetap mengikuti border asli template.
             */
            $tableHeaderRanges = [
                "B9:Q9",
                "B10:Q10",
                "B12:Q12",
                "B13:Q13",
            ];

            foreach ($tableHeaderRanges as $headerRange) {
                $sheet->getStyle($headerRange)
                    ->getBorders()
                    ->getTop()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->getStyle($headerRange)
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            /*
             * Status column tetap mempunyai garis kiri/kanan.
             */
            $sheet->getStyle('Q12:Q13')
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $sheet->getStyle('Q12:Q13')
                ->getBorders()
                ->getRight()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            /*
             * ============================================================
             * ITEM TABLE
             * ============================================================
             */
            $items = $pengajuan->divisiItems
                ->sortBy('id')
                ->values();

            $itemStartRow = 14;

            /*
             * Simpan format currency TOTAL dari template SEBELUM insert row.
             * Pada template terbaru, format Rp berada pada P17. Setelah row
             * baru disisipkan, nomor row TOTAL dapat bergeser, jadi format ini
             * harus diambil terlebih dahulu.
             */
            $templateTotalCurrencyFormat = $sheet->getStyle('P17')
                ->getNumberFormat()
                ->getFormatCode();

            // Template terbaru: hanya row 14 yang merupakan master item.
            // Row 15 adalah row TOTAL dan harus dipertahankan stylenya.
            $baseItemRows = 1;
            $itemCount = max(1, $items->count());

            /*
             * Hapus nilai/formula lama pada area item + total bawaan template.
             * Style dan border tidak dihapus.
             */
            for ($clearRow = 14; $clearRow <= 17; $clearRow++) {
                foreach (range(2, 17) as $clearCol) {
                    $sheet->getCellByColumnAndRow(
                        (int) $clearCol,
                        (int) $clearRow
                    )->setValue(null);
                }
            }

            /*
             * Hapus merge item/total lama pada area template.
             * Template terbaru: row 14 = item, row 15 = TOTAL.
             */
            foreach ($sheet->getMergeCells() as $merged) {
                $parts = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange(
                    (string) $merged
                );

                if (
                    !isset($parts[0][0], $parts[0][1]) ||
                    !isset($parts[0][1][0], $parts[0][1][1])
                ) {
                    continue;
                }

                $left = $parts[0][0];
                $right = $parts[0][1];

                $minCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    preg_replace('/\d+/', '', $left)
                );
                $maxCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    preg_replace('/\d+/', '', $right)
                );
                $minRow = (int) preg_replace('/\D+/', '', $left);
                $maxRow = (int) preg_replace('/\D+/', '', $right);

                if (
                    $minCol <= 17 &&
                    $maxCol >= 2 &&
                    $minRow <= 15 &&
                    $maxRow >= 14
                ) {
                    $sheet->unmergeCells($merged);
                }
            }

            /*
             * ============================================================
             * TEMPLATE ROW RULE
             * ============================================================
             *
             * Template terbaru Anda memang dibuat seperti ini:
             *   Row 14 = MASTER ITEM
             *   Row 15 = TOTAL
             *
             * Jadi JANGAN memakai row 16 sebagai master item.
             * Jika item > 1, kita sisipkan row tepat sebelum row TOTAL (15).
             * Dengan begitu row TOTAL asli otomatis terdorong ke bawah dan
             * tetap mempertahankan style/border TOTAL dari template.
             *
             * Setiap row item tambahan menyalin style ROW 14 persis.
             */
            if ($itemCount > 1) {
                $extraItemCount = $itemCount - 1;

                // Sisipkan sebelum TOTAL row 15.
                $sheet->insertNewRowBefore(15, $extraItemCount);

                // Copy tinggi + style row 14 ke setiap row item baru.
                for ($copyRow = 15; $copyRow <= 14 + $itemCount - 1; $copyRow++) {
                    $sheet->getRowDimension($copyRow)->setRowHeight(
                        $sheet->getRowDimension(14)->getRowHeight() ?: 32
                    );

                    foreach (range(2, 17) as $copyCol) {
                        $sourceCell = $sheet->getCellByColumnAndRow(
                            (int) $copyCol,
                            14
                        );
                        $targetCell = $sheet->getCellByColumnAndRow(
                            (int) $copyCol,
                            (int) $copyRow
                        );

                        // Copy style template row 14, termasuk border.
                        $targetCell->setXfIndex($sourceCell->getXfIndex());
                    }
                }
            }

            $lastItemRow = $itemStartRow + $itemCount - 1;
            $totalRow = $lastItemRow + 1;

            /*
             * Struktur per item:
             * B No
             * C PO
             * D Supplier
             * E Payment
             * F:J Description
             * L Qty
             * M Sat
             * N:O Unit Price
             * P Total
             * Q Status
             */
            $grandTotal = 0;

            foreach ($items as $index => $item) {
                $row = $itemStartRow + $index;

                $qty = (float) ($item->qty ?? 0);
                $price = (float) ($item->price ?? 0);
                $lineTotal = $qty * $price;
                $grandTotal += $lineTotal;

                $unit = $item->unit
                    ?: optional($item->stok)->satuan
                    ?: '-';

                /*
                 * Clear cells.
                 */
                foreach (range(2, 17) as $col) {
                    $sheet->getCellByColumnAndRow(
                        $col,
                        $row
                    )->setValue(null);
                }

                /*
                 * Merge only the cells which need a wider area.
                 */
                $sheet->mergeCells("F{$row}:J{$row}");
                $sheet->mergeCells("N{$row}:O{$row}");

                $sheet->setCellValue("B{$row}", $index + 1);
                $sheet->setCellValue("C{$row}", $item->po_no ?: '-');
                $sheet->setCellValue("D{$row}", $item->supplier ?: '-');
                $sheet->setCellValue(
                    "E{$row}",
                    $item->payment_type ?: '-'
                );

                // Description = nama_barang.
                $sheet->setCellValue(
                    "F{$row}",
                    $item->nama_barang ?: '-'
                );

                $sheet->setCellValue("L{$row}", $qty);
                $sheet->setCellValue("M{$row}", $unit);
                $sheet->setCellValue("N{$row}", $price);
                $sheet->setCellValue("P{$row}", "=L{$row}*N{$row}");

                $sheet->setCellValue(
                    "Q{$row}",
                    (bool) ($item->added_to_warehouse ?? false)
                    ? 'Added to Warehouse'
                    : '-'
                );

                $sheet->getRowDimension($row)->setRowHeight(32);

                $sheet->getStyle("B{$row}:Q{$row}")
                    ->getAlignment()
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet->getStyle("B{$row}:E{$row}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                /* Description F:J: merged + left aligned. */
                $sheet->getStyle("F{$row}:J{$row}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_LEFT
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    )
                    ->setWrapText(true);

                $sheet->getStyle("L{$row}:P{$row}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                /*
                 * Harga dan Total:
                 * "Rp." berada di sisi kiri melalui accounting-style format,
                 * angka berada di sisi kanan cell.
                 */
                $sheet->getStyle("N{$row}:P{$row}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_RIGHT
                    );

                $sheet->getStyle("Q{$row}")
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                $sheet->getStyle("N{$row}:P{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('"Rp."* #,##0');
            }

            /*
             * Kalau tidak ada item, tetap tampil satu row kosong.
             */
            if ($items->isEmpty()) {
                $row = $itemStartRow;

                $sheet->mergeCells("F{$row}:J{$row}");
                $sheet->mergeCells("N{$row}:O{$row}");

                $sheet->setCellValue("B{$row}", 1);
                $sheet->setCellValue("F{$row}", '-');
                $sheet->setCellValue("L{$row}", 0);
                $sheet->setCellValue("M{$row}", '-');
                $sheet->setCellValue("N{$row}", 0);
                $sheet->setCellValue("P{$row}", "=L{$row}*N{$row}");
                $sheet->setCellValue("Q{$row}", '-');
            }

            /*
             * Total.
             */
            $sheet->mergeCells("C{$totalRow}:M{$totalRow}");

            $sheet->setCellValue(
                "N{$totalRow}",
                'TOTAL'
            );

            /*
             * TOTAL HARUS MENGGUNAKAN RUMUS EXCEL, BUKAN VALUE PHP.
             *
             * Contoh:
             *   1 item  -> =SUM(P14:P14)
             *   2 item  -> =SUM(P14:P15)
             *   7 item  -> =SUM(P14:P20)
             *
             * Dengan begitu jika harga/qty diedit di Excel, TOTAL ikut
             * menghitung ulang secara otomatis.
             */
            $totalFormula = "=SUM(P{$itemStartRow}:P{$lastItemRow})";
            $sheet->setCellValue("P{$totalRow}", $totalFormula);

            /*
             * Pertahankan format currency/Rp dari TOTAL template.
             * Template Anda menggunakan accounting format Rp pada cell
             * TOTAL, jadi jangan menggantinya dengan #,##0 biasa.
             */
            /*
             * Explicit Rupiah format. TOTAL remains an Excel formula.
             */
            $totalCurrencyFormat = '"Rp."* #,##0';

            $sheet->getStyle("P{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode($totalCurrencyFormat);

            $sheet->setCellValue(
                "Q{$totalRow}",
                ''
            );

            $sheet->getRowDimension($totalRow)->setRowHeight(24);

            $sheet->getStyle("N{$totalRow}:P{$totalRow}")
                ->getFont()
                ->setBold(true)
                ->setSize(9);

            $sheet->getStyle("N{$totalRow}:P{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode('"Rp."* #,##0');

            $sheet->getStyle("P{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode('"Rp."* #,##0');

            $sheet->getStyle("N{$totalRow}:P{$totalRow}")
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                )
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );

            /*
             * ============================================================
             * CLEAN TEMPLATE TOTAL ROW RESIDUE
             * ============================================================
             *
             * Template asli mempunyai 3 baris item (14:16) dan total
             * bawaan setelahnya. Jika item hanya 1 atau 2, total dinamis
             * berada di row 15/16, sehingga row template lama masih dapat
             * berisi formula seperti =SUM(P14:P15).
             *
             * Hapus NILAI/FORMULA pada row setelah total dinamis sampai
             * row 17 agar tidak muncul angka ganda seperti:
             *   333,000
             *   666,000
             *
             * Hanya isi yang dibersihkan; border/layout template tetap.
             */
            $templateLastTableRow = 17;

            if ($totalRow < $templateLastTableRow) {
                for ($clearRow = $totalRow + 1; $clearRow <= $templateLastTableRow; $clearRow++) {
                    foreach (range(2, 17) as $clearCol) {
                        $sheet->getCellByColumnAndRow(
                            (int) $clearCol,
                            (int) $clearRow
                        )->setValue(null);
                    }
                }
            }

            /*
             * ============================================================
             * SIGNATURE / APPROVAL
             * ============================================================
             *
             * IKUTI PROPORSI TEMPLATE ASLI.
             *
             * Block yang dipakai template:
             *
             * B:C   Made by
             * D:E   Approver 1
             * F:G   Approver 2
             * H:I   Approver 3
             * J:K   Approver 3 / Approver 4 sesuai step
             * L:M   Approver 4
             * N:Q   Approver 5 / final approver
             *
             * Jangan memakai block L:N dan O:Q karena akan mengubah
             * proporsi template asli.
             */
            $approvalSteps = $pengajuan->approvalSteps
                ->sortBy('step_order')
                ->values();

            /*
             * Step 1 = Made by.
             * Jika step 1 tidak ada di approval_steps, buat virtual step
             * menggunakan user pembuat pengajuan.
             */
            $madeStep = $approvalSteps->firstWhere('step_order', 1);

            if (!$madeStep) {
                $madeStep = new PengajuanApprovalStep();
                $madeStep->step_order = 1;
                $madeStep->step_name = 'Made by';
                $madeStep->user_name = $madeByName;
                $madeStep->status = 'approved';
                $madeStep->approved_at = $pengajuan->created_at;
            }

            $approvalSteps = collect([$madeStep])
                ->merge(
                    $approvalSteps->reject(
                        fn($step) => (int) $step->step_order === 1
                    )
                )
                ->values();

            /*
             * Cari user berdasarkan user_name karena tabel
             * pengajuan_approval_steps tidak mempunyai user_id.
             */
            $userNames = $approvalSteps
                ->pluck('user_name')
                ->filter()
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values();

            $usersByName = User::with('karyawan.divisi')
                ->whereIn('name', $userNames->all())
                ->get()
                ->keyBy('name');

            /*
             * ============================================================
             * SIGNATURE / APPROVAL — FINAL CLEAN LAYOUT
             * ============================================================
             *
             * Proporsi mengikuti template:
             * B:C = Made by
             * D:E = Approver 1
             * F:G = Approver 2
             * H:I = Approver 3
             * J:K = Approver 4 (blok sempit / VP SALES)
             * L:M = Approver 5
             * N:Q = Final Approver
             *
             * Fokus perbaikan:
             * - area header/nama dibuat cukup tinggi
             * - area TTD dipisahkan dari nama dan tanggal
             * - blok J:K diperlakukan khusus karena sangat sempit
             * - tanggal tidak dipaksa satu baris pada blok sempit
             * - TTD dicrop dari whitespace PNG lalu di-center
             */
            $approvalSteps = $pengajuan->approvalSteps
                ->sortBy('step_order')
                ->values();

            $madeStep = $approvalSteps->firstWhere('step_order', 1);

            if (!$madeStep) {
                $madeStep = new PengajuanApprovalStep();
                $madeStep->step_order = 1;
                $madeStep->step_name = 'Made by';
                $madeStep->user_name = $madeByName;
                $madeStep->status = 'approved';
                $madeStep->approved_at = $pengajuan->created_at;
            }

            $approvalSteps = collect([$madeStep])
                ->merge(
                    $approvalSteps->reject(
                        fn($step) => (int) $step->step_order === 1
                    )
                )
                ->values();

            $userNames = $approvalSteps
                ->pluck('user_name')
                ->filter()
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->unique()
                ->values();

            $usersByName = User::with('karyawan.divisi')
                ->whereIn('name', $userNames->all())
                ->get()
                ->keyBy('name');

            /* Proporsi block TTD sesuai template. */
            $signatureBlocks = [
                1 => ['B', 'C'],
                2 => ['D', 'E'],
                3 => ['F', 'G'],
                4 => ['H', 'I'],
                5 => ['J', 'K'],
                6 => ['L', 'M'],
                7 => ['N', 'Q'],
            ];

            $signatureTopRow = $totalRow + 2;
            $signatureRoleRow = $signatureTopRow + 1;
            $signatureBodyStartRow = $signatureRoleRow + 1;
            $signatureBodyEndRow = $signatureBodyStartRow + 4; // 5 row area
            $signatureDateRow = $signatureBodyEndRow + 1;

            /* Hapus merge lama yang menyentuh signature area. */
            $signatureMerges = $sheet->getMergeCells();
            foreach ($signatureMerges as $merged) {
                $parts = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::splitRange((string) $merged);
                $range = $parts[0] ?? [];
                if (count($range) < 2) {
                    continue;
                }

                $from = $range[0];
                $to = $range[1];
                $fromCol = preg_replace('/\d+/', '', $from);
                $toCol = preg_replace('/\d+/', '', $to);
                $fromRow = (int) preg_replace('/\D+/', '', $from);
                $toRow = (int) preg_replace('/\D+/', '', $to);

                $fromIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($fromCol);
                $toIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($toCol);

                if (
                    $fromIndex <= 17 &&
                    $toIndex >= 2 &&
                    $fromRow <= $signatureDateRow &&
                    $toRow >= $signatureTopRow
                ) {
                    $sheet->unmergeCells($merged);
                }
            }

            /* Bersihkan isi lama seperti Person 1, Person 2, dst. */
            for ($r = $signatureTopRow; $r <= $signatureDateRow; $r++) {
                foreach (range(2, 17) as $col) {
                    $sheet->getCellByColumnAndRow($col, $r)->setValue(null);
                }
            }

            /* Judul section. */
            $sheet->mergeCells("B{$signatureTopRow}:Q{$signatureTopRow}");
            $sheet->setCellValue(
                "B{$signatureTopRow}",
                'Signature / Approval'
            );
            $sheet->getRowDimension($signatureTopRow)->setRowHeight(18);
            $sheet->getStyle("B{$signatureTopRow}:Q{$signatureTopRow}")
                ->getFont()
                ->setBold(true)
                ->setSize(9);
            $sheet->getStyle("B{$signatureTopRow}:Q{$signatureTopRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setVertical(Alignment::VERTICAL_CENTER);

            /*
             * Tinggi dibuat sedikit lebih longgar agar setiap elemen punya
             * breathing room dan TTD tidak mepet ke nama/tanggal.
             */
            $sheet->getRowDimension($signatureRoleRow)->setRowHeight(43);
            for ($r = $signatureBodyStartRow; $r <= $signatureBodyEndRow; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(25);
            }
            $sheet->getRowDimension($signatureDateRow)->setRowHeight(34);

            /* Buat container tiap signature block. */
            foreach ($signatureBlocks as [$left, $right]) {
                $sheet->mergeCells(
                    "{$left}{$signatureRoleRow}:{$right}{$signatureRoleRow}"
                );
                $sheet->mergeCells(
                    "{$left}{$signatureBodyStartRow}:{$right}{$signatureBodyEndRow}"
                );
                $sheet->mergeCells(
                    "{$left}{$signatureDateRow}:{$right}{$signatureDateRow}"
                );

                /*
                 * Signature TIDAK memakai border kotak.
                 * Template tetap dipakai, tetapi seluruh area signature
                 * dibuat clean tanpa garis vertikal/horizontal.
                 */
                /*
                 * SIGNATURE CLEAN — tidak ada border pada area approver.
                 */
                $range = "{$left}{$signatureRoleRow}:{$right}{$signatureDateRow}";

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                        'left' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                        'right' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                        'insideHorizontal' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                        'insideVertical' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                        ],
                    ],
                ]);
            }

            /*
             * Crop whitespace PNG TTD.
             * Ini membuat ukuran Drawing benar-benar mengikuti tinta/signature.
             */
            $preparedSignatureFiles = [];

            $prepareSignature = function (string $sourcePath) use (&$preparedSignatureFiles): ?string {
                if (!function_exists('imagecreatefrompng')) {
                    return $sourcePath;
                }

                $info = @getimagesize($sourcePath);
                if (!$info || empty($info[0]) || empty($info[1])) {
                    return $sourcePath;
                }

                $src = @imagecreatefrompng($sourcePath);
                if (!$src) {
                    return $sourcePath;
                }

                imagealphablending($src, false);
                imagesavealpha($src, true);

                $w = imagesx($src);
                $h = imagesy($src);
                $minX = $w;
                $minY = $h;
                $maxX = -1;
                $maxY = -1;

                for ($y = 0; $y < $h; $y++) {
                    for ($x = 0; $x < $w; $x++) {
                        $rgba = imagecolorat($src, $x, $y);
                        $a = ($rgba >> 24) & 0x7F;
                        $r = ($rgba >> 16) & 0xFF;
                        $g = ($rgba >> 8) & 0xFF;
                        $b = $rgba & 0xFF;

                        /* Putih hampir murni dianggap background. */
                        $isInk = ($r < 238 || $g < 238 || $b < 238 || $a > 10);

                        if ($isInk) {
                            $minX = min($minX, $x);
                            $minY = min($minY, $y);
                            $maxX = max($maxX, $x);
                            $maxY = max($maxY, $y);
                        }
                    }
                }

                /* Tidak ditemukan tinta. */
                if ($maxX < 0 || $maxY < 0) {
                    imagedestroy($src);
                    return $sourcePath;
                }

                /* Tambahkan padding kecil agar tinta tidak terpotong. */
                $paddingX = max(4, (int) round(($maxX - $minX + 1) * 0.04));
                $paddingY = max(4, (int) round(($maxY - $minY + 1) * 0.08));

                $cropX = max(0, $minX - $paddingX);
                $cropY = max(0, $minY - $paddingY);
                $cropRight = min($w - 1, $maxX + $paddingX);
                $cropBottom = min($h - 1, $maxY + $paddingY);

                $cropW = max(1, $cropRight - $cropX + 1);
                $cropH = max(1, $cropBottom - $cropY + 1);

                $dst = imagecreatetruecolor($cropW, $cropH);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                imagefilledrectangle($dst, 0, 0, $cropW, $cropH, $transparent);

                imagecopy(
                    $dst,
                    $src,
                    0,
                    0,
                    $cropX,
                    $cropY,
                    $cropW,
                    $cropH
                );

                $tmpPng = storage_path(
                    'app/tmp/ttd_' . uniqid('', true) . '.png'
                );

                if (!is_dir(dirname($tmpPng))) {
                    @mkdir(dirname($tmpPng), 0775, true);
                }

                imagepng($dst, $tmpPng, 6);
                imagedestroy($dst);
                imagedestroy($src);

                if (!is_file($tmpPng)) {
                    return $sourcePath;
                }

                $preparedSignatureFiles[] = $tmpPng;
                return $tmpPng;
            };

            /* Hitung ukuran gambar tanpa merusak aspect ratio. */
            $getTtdSize = function (string $path, float $maxWidth, float $maxHeight): array {
                $info = @getimagesize($path);
                if (!$info || empty($info[0]) || empty($info[1])) {
                    return [0, 0];
                }

                $width = (float) $info[0];
                $height = (float) $info[1];
                $ratio = min($maxWidth / $width, $maxHeight / $height);

                return [
                    max(1, (int) round($width * $ratio)),
                    max(1, (int) round($height * $ratio)),
                ];
            };

            /*
             * Isi 7 approver.
             */
            foreach ($signatureBlocks as $blockNo => [$left, $right]) {
                $step = $approvalSteps->get($blockNo - 1);

                if (!$step) {
                    continue;
                }

                $stepOrder = (int) $step->step_order;
                $userName = trim((string) ($step->user_name ?: '-'));
                $user = $usersByName->get($userName);
                $userId = $user?->id;
                $isNarrowBlock = ($blockNo === 5); // J:K / VP SALES
                $isFinalBlock = ($blockNo === 7);  // N:Q

                if ($blockNo === 1) {
                    $role = 'Made by';
                    $divisionName = null;
                } elseif ($isFinalBlock) {
                    $role = 'Approved by';
                    $divisionName = null;
                } else {
                    $role = 'Checked by';
                    $divisionName = optional(
                        optional($user?->karyawan)->divisi
                    )->nama;

                    if ($stepOrder === 6 || $blockNo === 6) {
                        $divisionName = 'FINANCE ACC';
                    }
                }

                /*
                 * Header lebih pendek khusus block J:K.
                 * Jangan sampai "Approved at" / role dipaksa terlalu sempit.
                 */
                if ($isNarrowBlock) {
                    $headerParts = [$role];
                    if ($divisionName) {
                        $headerParts[] = $divisionName;
                    }
                    $headerParts[] = $userName;
                    $header = implode("\n", $headerParts);
                } else {
                    $header = $role;
                    if ($divisionName) {
                        $header .= "\n{$divisionName}";
                    }
                    $header .= "\n{$userName}";
                }

                $sheet->setCellValue(
                    "{$left}{$signatureRoleRow}",
                    $header
                );

                $roleStyle = $sheet->getStyle(
                    "{$left}{$signatureRoleRow}:{$right}{$signatureRoleRow}"
                );

                $roleStyle->getFont()
                    ->setBold(false)
                    ->setSize($isNarrowBlock ? 6.2 : 7.5);

                $roleStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                /* Area TTD benar-benar terpisah dari header dan tanggal. */
                $bodyRange =
                    "{$left}{$signatureBodyStartRow}:{$right}{$signatureBodyEndRow}";

                $sheet->getStyle($bodyRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(false);

                /* Status / approved at. */
                $approved = strtolower(trim((string) $step->status)) === 'approved';

                if ($approved) {
                    $approvedAt = $step->approved_at
                        ? \Carbon\Carbon::parse($step->approved_at)
                        : null;

                    if ($isNarrowBlock) {
                        /* 3 baris agar tidak pecah menjadi karakter vertikal. */
                        $dateText = $approvedAt
                            ? "Approved\n" . $approvedAt->format('d-m-Y') . "\n" . $approvedAt->format('H:i')
                            : "Approved\n-";
                    } else {
                        $dateText = $approvedAt
                            ? "Approved at:\n" . $approvedAt->format('d-m-Y H:i')
                            : "Approved at:\n-";
                    }
                } else {
                    $dateText = 'Pending';
                }

                $sheet->setCellValue(
                    "{$left}{$signatureDateRow}",
                    $dateText
                );

                $dateStyle = $sheet->getStyle(
                    "{$left}{$signatureDateRow}:{$right}{$signatureDateRow}"
                );

                $dateStyle->getFont()
                    ->setBold(true)
                    ->setSize($isNarrowBlock ? 5.8 : 7.0);

                $dateStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                /* ========================================================
                 * TTD — CENTERED, PROPORTIONAL, DENGAN SAFE AREA
                 * ======================================================== */
                if ($userId) {
                    $sourceTtdPath = public_path(
                        'assets/ttd_png/' . $userId . '.png'
                    );

                    if (is_file($sourceTtdPath)) {
                        $ttdPath = $prepareSignature($sourceTtdPath);

                        $leftIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($left);
                        $rightIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($right);

                        $blockWidthExcel = 0.0;
                        for ($c = $leftIndex; $c <= $rightIndex; $c++) {
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                            $width = $sheet->getColumnDimension($col)->getWidth();
                            if (!$width || $width <= 0) {
                                $width = 8.43;
                            }
                            $blockWidthExcel += $width;
                        }

                        /* Excel width -> pendekatan pixel. */
                        $blockWidthPx = $blockWidthExcel * 7.0;
                        $bodyHeightPx = 5 * 25 * (96 / 72);

                        /*
                         * Block sempit tidak dipaksa besar karena akan menyentuh
                         * border. Block normal dibuat lebih besar.
                         */
                        if ($isNarrowBlock) {
                            // VP SALES tetap proporsional, tetapi gunakan hampir
                            // seluruh lebar kotak agar TTD tidak terlihat kecil.
                            $maxWidth = max(30, floor($blockWidthPx * 0.84));
                            $maxHeight = 58;
                        } else {
                            // Signature normal dibuat jauh lebih besar dan
                            // memanfaatkan area body tanpa menyentuh border.
                            $maxWidth = max(42, floor($blockWidthPx * 0.92));
                            $maxHeight = $isFinalBlock ? 84 : 74;
                        }

                        [$ttdWidth, $ttdHeight] = $getTtdSize(
                            $ttdPath,
                            $maxWidth,
                            $maxHeight
                        );

                        if ($ttdWidth > 0 && $ttdHeight > 0) {
                            $drawing = new Drawing();
                            $drawing->setName('TTD - ' . $userName);
                            $drawing->setDescription('TTD - ' . $userName);
                            $drawing->setPath($ttdPath);
                            $drawing->setWidth($ttdWidth);
                            $drawing->setHeight($ttdHeight);
                            $drawing->setCoordinates(
                                "{$left}{$signatureBodyStartRow}"
                            );

                            /* Center horizontal terhadap block. */
                            $offsetX = max(
                                1,
                                (int) round(($blockWidthPx - $ttdWidth) / 2)
                            );

                            /*
                             * Center vertikal tetapi diberi sedikit bias ke atas.
                             * Tujuannya memberi jarak aman dari Approved at.
                             */
                            $safeBodyHeightPx = $bodyHeightPx - 12;
                            $offsetY = max(
                                4,
                                (int) round(($safeBodyHeightPx - $ttdHeight) / 2) - 4
                            );

                            $drawing->setOffsetX($offsetX);
                            $drawing->setOffsetY($offsetY);
                            $drawing->setWorksheet($sheet);
                        }
                    }
                }
            }

            /*
             * File temp TTD jangan dihapus sebelum writer selesai membaca
             * gambar. Cleanup dilakukan setelah XLSX selesai di-stream.
             */

            /*
             * ============================================================
             * CLEANUP ROW 26-27
             * ============================================================
             * Baris kosong setelah signature tidak boleh memiliki border
             * dari template lama.
             */
            /*
             * Hanya hapus border jika row 26-27 memang berada DI BAWAH
             * signature. Jangan menyentuh border signature apabila jumlah
             * item membuat signature bergeser sampai row tersebut.
             */
            if ($signatureDateRow < 26) {
                $sheet->getStyle('B26:Q27')
                    ->getBorders()
                    ->getTop()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                $sheet->getStyle('B26:Q27')
                    ->getBorders()
                    ->getBottom()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                $sheet->getStyle('B26:Q27')
                    ->getBorders()
                    ->getLeft()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                $sheet->getStyle('B26:Q27')
                    ->getBorders()
                    ->getRight()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                // PhpSpreadsheet tidak menyediakan getInsideHorizontal()/getInsideVertical().
                // Border internal pada area kosong 26:27 tidak perlu disentuh;
                // cukup hilangkan border luar area tersebut.
            }

            /*
             * ============================================================
             * LAMPIRAN — SHEET 2 KHUSUS
             * ============================================================
             *
             * Attachment TIDAK lagi ditempel di bawah Signature pada sheet
             * Purchase Request. Semua attachment dikumpulkan di SHEET 2.
             *
             * Sheet 1 : Purchase Request
             * Sheet 2 : Lampiran
             *
             * Dengan cara ini jumlah item sebanyak apa pun tidak akan pernah
             * membuat gambar attachment terpotong di batas page sheet 1.
             */
            $attachments = $pengajuan->files
                ->where('type', 'image')
                ->values();

            /*
             * Pastikan workbook memiliki sheet kedua.
             */
            if ($spreadsheet->getSheetCount() < 2) {
                $attachmentSheet = $spreadsheet->createSheet();
            } else {
                $attachmentSheet = $spreadsheet->getSheet(1);
            }

            $attachmentSheet->setTitle('Lampiran');
            $attachmentSheet->setShowGridlines(false);
            $attachmentSheet->setPrintGridlines(false);

            /*
             * Bersihkan isi/merge/drawing lama pada sheet 2.
             */
            foreach ($attachmentSheet->getMergeCells() as $merged) {
                try {
                    $attachmentSheet->unmergeCells($merged);
                } catch (\Throwable $ignored) {
                    // Ignore invalid legacy merge.
                }
            }

            /*
             * Clear template content safely.
             * getColumnIndex() pada beberapa versi PhpSpreadsheet mengembalikan
             * huruf kolom (mis. "A"), sedangkan getCellByColumnAndRow()
             * membutuhkan integer. Karena itu jangan passing nilai tersebut
             * langsung ke getCellByColumnAndRow().
             */
            $highestRow = max(200, (int) $attachmentSheet->getHighestRow());
            $highestColumnIndex = max(
                18,
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                    $attachmentSheet->getHighestColumn()
                )
            );

            for ($rowNo = 1; $rowNo <= $highestRow; $rowNo++) {
                for ($colNo = 1; $colNo <= $highestColumnIndex; $colNo++) {
                    $attachmentSheet->getCellByColumnAndRow($colNo, $rowNo)
                        ->setValue(null);
                }
            }

            /*
             * Sheet Lampiran dibuat baru, jadi tidak ada logo template
             * yang perlu dipertahankan di sini.
             */
            $attachmentSheet->getDrawingCollection()->exchangeArray([]);

            /*
             * Lebar area kiri/kanan dibuat seimbang agar dua gambar benar-benar
             * berada berdampingan dalam A4 Landscape.
             */
            $attachmentSheet->getColumnDimension('A')->setWidth(2);
            foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                $attachmentSheet->getColumnDimension($col)->setWidth(11);
            }
            foreach (['J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'] as $col) {
                $attachmentSheet->getColumnDimension($col)->setWidth(11);
            }

            $attachmentSheet->getColumnDimension('R')->setWidth(2);

            /*
             * A4 Landscape:
             *  - 2 gambar per halaman
             *  - pasangan berikutnya selalu page baru
             */
            $attachmentSheet->getPageSetup()
                ->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                )
                ->setPaperSize(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                )
                ->setFitToWidth(1)
                ->setFitToHeight(0)
                ->setFitToPage(true)
                ->setHorizontalCentered(true)
                ->setVerticalCentered(false);

            $attachmentSheet->getPageMargins()
                ->setTop(0.25)
                ->setRight(0.25)
                ->setBottom(0.25)
                ->setLeft(0.25)
                ->setHeader(0)
                ->setFooter(0);

            $attachmentSheet->getHeaderFooter()
                ->setOddFooter('&CPage &P of &N');

            $attachmentSheet->setCellValue('B1', 'Purchase Request');
            $attachmentSheet->mergeCells('B1:Q1');
            $attachmentSheet->getStyle('B1:Q1')
                ->getFont()
                ->setBold(true)
                ->setSize(14);
            $attachmentSheet->getStyle('B1:Q1')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $attachmentSheet->getRowDimension(1)->setRowHeight(24);

            $attachmentSheet->setCellValue(
                'B2',
                'Lampiran Purchase Request #' . $pengajuan->id
            );
            $attachmentSheet->mergeCells('B2:Q2');
            $attachmentSheet->getStyle('B2:Q2')
                ->getFont()
                ->setBold(true)
                ->setSize(10);
            $attachmentSheet->getStyle('B2:Q2')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $attachmentSheet->getRowDimension(2)->setRowHeight(18);

            $attachmentColumns = [
                ['B', 'I'],
                ['J', 'Q'],
            ];

            /*
             * Ukuran image dibuat besar, tetapi tidak lebih besar dari setengah
             * area printable A4 agar dua gambar tetap muat berdampingan.
             */
            $attachmentMaxWidth = 360;
            $attachmentMaxHeight = 390;

            $attachmentRow = 4;
            $attachmentIndex = 0;

            foreach ($attachments->chunk(2) as $pairIndex => $pair) {
                /*
                 * Pair pertama = halaman pertama SHEET 2.
                 * Pair berikutnya = halaman berikutnya.
                 */
                if ($pairIndex > 0) {
                    $breakAfterRow = $attachmentRow - 1;
                    $attachmentSheet->setBreak(
                        "B{$breakAfterRow}",
                        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW
                    );
                }

                $pageLabel = 'Lampiran : Hal ' . ($pairIndex + 2);

                $attachmentSheet->mergeCells(
                    "B{$attachmentRow}:Q{$attachmentRow}"
                );
                $attachmentSheet->setCellValue(
                    "B{$attachmentRow}",
                    $pageLabel
                );
                $attachmentSheet->getStyle(
                    "B{$attachmentRow}:Q{$attachmentRow}"
                )
                    ->getFont()
                    ->setBold(true)
                    ->setSize(11);
                $attachmentSheet->getStyle(
                    "B{$attachmentRow}:Q{$attachmentRow}"
                )
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $attachmentSheet->getRowDimension(
                    $attachmentRow
                )->setRowHeight(22);

                $imageRow = $attachmentRow + 1;
                $reservedRows = 27;

                for (
                    $r = $imageRow;
                    $r < $imageRow + $reservedRows;
                    $r++
                ) {
                    $attachmentSheet->getRowDimension($r)->setRowHeight(15);
                }

                foreach ($pair->values() as $pairOffset => $attachment) {
                    $filePath = null;

                    try {
                        if (!empty($attachment->file_path)) {
                            $filePath = Storage::disk('public')
                                ->path($attachment->file_path);
                        }
                    } catch (\Throwable $attachmentPathError) {
                        Log::warning(
                            'PURCHASING EXPORT ATTACHMENT PATH ERROR',
                            [
                                'pengajuan_id' => $pengajuan->id,
                                'file_id' => $attachment->id,
                                'file_path' => $attachment->file_path,
                                'message' => $attachmentPathError->getMessage(),
                            ]
                        );
                    }

                    if (!$filePath || !is_file($filePath)) {
                        continue;
                    }

                    $extension = strtolower(
                        pathinfo($filePath, PATHINFO_EXTENSION)
                    );

                    if (
                        !in_array(
                            $extension,
                            ['jpg', 'jpeg', 'png', 'gif'],
                            true
                        )
                    ) {
                        continue;
                    }

                    $info = @getimagesize($filePath);
                    if (!$info || empty($info[0]) || empty($info[1])) {
                        continue;
                    }

                    $originalWidth = (int) $info[0];
                    $originalHeight = (int) $info[1];

                    $ratio = min(
                        $attachmentMaxWidth / $originalWidth,
                        $attachmentMaxHeight / $originalHeight,
                        1
                    );

                    $imageWidth = max(
                        120,
                        (int) round($originalWidth * $ratio)
                    );
                    $imageHeight = max(
                        100,
                        (int) round($originalHeight * $ratio)
                    );

                    [$leftCol, $rightCol] =
                        $attachmentColumns[$pairOffset];

                    /*
                     * Tidak ada border container.
                     */
                    $attachmentSheet->mergeCells(
                        "{$leftCol}{$imageRow}:{$rightCol}" .
                        ($imageRow + $reservedRows - 1)
                    );

                    $attachmentSheet->getStyle(
                        "{$leftCol}{$imageRow}:{$rightCol}" .
                        ($imageRow + $reservedRows - 1)
                    )
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $drawing = new Drawing();
                    $drawing->setName(
                        'Attachment ' . ($attachmentIndex + 1)
                    );
                    $drawing->setDescription(
                        'Attachment ' . ($attachmentIndex + 1)
                    );
                    $drawing->setPath($filePath);
                    $drawing->setWidth($imageWidth);
                    $drawing->setHeight($imageHeight);
                    $drawing->setCoordinates(
                        "{$leftCol}{$imageRow}"
                    );

                    /*
                     * Hitung lebar blok agar image benar-benar center.
                     */
                    $leftIndex =
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                            $leftCol
                        );
                    $rightIndex =
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                            $rightCol
                        );

                    $blockWidthExcel = 0.0;
                    for ($c = $leftIndex; $c <= $rightIndex; $c++) {
                        $col =
                            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                                $c
                            );
                        $columnWidth =
                            $attachmentSheet->getColumnDimension($col)->getWidth();
                        $blockWidthExcel +=
                            ($columnWidth && $columnWidth > 0)
                            ? $columnWidth
                            : 8.43;
                    }

                    $blockWidthPx = $blockWidthExcel * 7.0;
                    $blockHeightPx =
                        $reservedRows * 15 * (96 / 72);

                    $offsetX = max(
                        0,
                        (int) round(
                            ($blockWidthPx - $imageWidth) / 2
                        )
                    );
                    $offsetY = max(
                        0,
                        (int) round(
                            ($blockHeightPx - $imageHeight) / 2
                        )
                    );

                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY($offsetY);
                    $drawing->setWorksheet($attachmentSheet);

                    $attachmentIndex++;
                }

                /*
                 * Jarak kecil sebelum pair berikutnya.
                 */
                $attachmentRow =
                    $imageRow + $reservedRows + 2;
            }

            $attachmentLastRow = max(4, $attachmentRow - 1);

            $attachmentSheet->getPageSetup()
                ->setPrintArea("B1:Q{$attachmentLastRow}");

            $attachmentSheet->setSelectedCell('B1');

            /*
             * ============================================================
             * READY TO PRINT — SHEET 1
             * ============================================================
             */
            $lastPrintRow = max(
                $signatureDateRow + 1,
                $totalRow + 1
            );

            $sheet->setShowGridlines(false);
            $sheet->setPrintGridlines(false);

            $sheet->getPageSetup()
                ->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                )
                ->setPaperSize(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                )
                ->setFitToWidth(1)
                ->setFitToHeight(0)
                ->setFitToPage(true)
                ->setHorizontalCentered(true)
                ->setVerticalCentered(false);

            $sheet->getPageMargins()
                ->setTop(0.20)
                ->setRight(0.20)
                ->setBottom(0.20)
                ->setLeft(0.20)
                ->setHeader(0)
                ->setFooter(0);

            $sheet->getPageSetup()
                ->setPrintArea("B1:Q{$lastPrintRow}");

            $sheet->getHeaderFooter()
                ->setOddFooter('&CPage &P of &N');

            /*
             * Sheet 2 harus tetap visible. Hanya sheet setelah sheet 2 yang
             * disembunyikan bila template suatu saat mempunyai sheet tambahan.
             */
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $index = $spreadsheet->getIndex($worksheet);
                if ($index >= 2) {
                    $worksheet->setSheetState(
                        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN
                    );
                }
            }

            $spreadsheet->setActiveSheetIndex(
                $spreadsheet->getIndex($sheet)
            );

            $filename =
                'Purchase_Request_' .
                $pengajuan->id .
                '_' .
                now()->format(
                    'Ymd_His'
                ) .
                '.xlsx';

            $writer =
                new Xlsx(
                    $spreadsheet
                );

            $writer->setPreCalculateFormulas(
                true
            );

            return response()->streamDownload(
                function () use ($writer, &$preparedSignatureFiles) {
                    $writer->save(
                        'php://output'
                    );

                    foreach ($preparedSignatureFiles as $tempSignatureFile) {
                        if (is_file($tempSignatureFile)) {
                            @unlink($tempSignatureFile);
                        }
                    }
                },
                $filename,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' =>
                        'max-age=0',
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                'EXPORT PURCHASING ERROR',
                [
                    'pengajuan_id' => $id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            abort(
                500,
                'Gagal export Purchase Request: ' .
                $e->getMessage()
            );
        }
    }

}