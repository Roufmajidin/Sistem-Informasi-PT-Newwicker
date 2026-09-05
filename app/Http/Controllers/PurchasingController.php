<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
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
class PurchasingController extends Controller
{
    /**
     * Halaman Pengajuan Barang Inventory
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        $karyawanByUserId = Karyawan::with('divisi')
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

            /* Attachment disimpan bersamaan saat tombol Simpan ditekan */
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

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

                    PengajuanDivisi::create([

                        'pengajuan_id' =>
                            $pengajuan->id,

                        /*
                         * Barang lama:
                         * id_stock ada.
                         *
                         * Barang baru:
                         * id_stock = null.
                         */
                        'id_stock' =>
                            $idStock,

                        /*
                         * Divisi tetap menggunakan ID
                         * dari master divisi.
                         */
                        'divisi_id' =>
                            $divisiId,

                        /*
                         * Nama barang
                         */
                        'nama_barang' =>
                            $item['nama_barang'],

                        /*
                         * PO NO PER ROW
                         */
                        'po_no' =>
                            $item['po_no'] ?? null,

                        /*
                         * Supplier PER ROW
                         */
                        'supplier' =>
                            $item['supplier'] ?? null,

                        /*
                         * Description PER ROW
                         */
                        'description' =>
                            $item['description'] ?? null,

                        /*
                         * Keterangan PER ROW
                         */
                        'keterangan' =>
                            $item['keterangan'] ?? null,

                        /*
                         * Quantity
                         */
                        'qty' =>
                            $item['qty'],

                        /*
                         * Unit
                         */
                        'unit' =>
                            $item['unit'] ?? null,

                        /*
                         * Harga
                         */
                        'price' =>
                            $item['price'] ?? 0,

                        /*
                         * Barang inventory:
                         * 1 = sudah terkait warehouse
                         *
                         * Barang baru:
                         * 0 = belum ada di warehouse
                         */
                        'added_to_warehouse' =>
                            !empty($idStock)
                            ? 0
                            : 0,
                    ]);
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
                | CHECKED BY PERSON 1
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    2,
                    'Checked by Person 1',
                    $signature['checked_by_1'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY PERSON 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    3,
                    'Checked by Person 2',
                    $signature['checked_by_2'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY PERSON 1 GROUP 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    4,
                    'Checked by Person 1',
                    $signature['checked_by_3'] ?? null
                );


                /*
                |--------------------------------------------------------------------------
                | CHECKED BY PERSON 2 GROUP 2
                |--------------------------------------------------------------------------
                */

                $this->createApprovalStep(
                    $pengajuan->id,
                    5,
                    'Checked by Person 2',
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
        'images' => 'required|array|max:10',
        'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
    ], [
        'images.required' => 'Silakan pilih gambar terlebih dahulu.',
        'images.max' => 'Maksimal 10 gambar.',
        'images.*.image' => 'File harus berupa gambar.',
        'images.*.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
        'images.*.max' => 'Ukuran setiap gambar maksimal 5 MB.',
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
        $karyawanByUserId = Karyawan::with('divisi')
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

        // Hanya pembuat yang boleh mengedit. User lain tetap dapat melihat.
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
                    'id' => $item->id_stock,
                    'code' => optional($item->stok)->kode_barang ?? '',
                    'name' => $item->nama_barang,
                    'jenis' => optional($item->stok)->jenis ?? '',
                    'warehouse' => $item->id_stock ? 'Gudang Utama' : 'Belum ada di inventory',
                    'stock' => $item->id_stock
                        ? (float) (optional($item->stok)->stok_awal ?? 0)
                        : 0,
                    'qty' => (float) $item->qty,
                    'unit' => $item->unit ?? optional($item->stok)->satuan ?? '',
                    'reason' => '',
                    'supplier' => $item->supplier ?? '',
                    'po_no' => $item->po_no ?? '',
                    'payment' => $item->payment ?? '',
                    'description' => $item->description ?? '',
                    'keterangan' => $item->keterangan ?? '',
                    'unit_price' => (float) ($item->price ?? 0),
                    'total' => (float) (($item->price ?? 0) * ($item->qty ?? 0)),
                    'is_new' => empty($item->id_stock),
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
                        ? optional(optional($karyawanByUserId->get($user->karyawan_id))->divisi)->nama
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

}