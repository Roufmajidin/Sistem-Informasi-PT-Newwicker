<?php



namespace App\Http\Controllers;



use App\Models\Po;

use App\Models\DetailPo;

use App\Models\RndArchiveFolder;

use App\Models\RndArchiveFile;

use App\Services\GoogleDriveService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Throwable;



class RndController extends Controller
{

    /**

     * Halaman utama daftar PO R&D.

     */

    public function samplePictures(Request $request)
    {

        $search = trim((string) $request->input('search', ''));



        $pos = Po::query()

            ->where(function ($query) {

                $query->where('order_no', 'like', 'NWS%')

                    ->orWhere('order_no', 'like', 'NWD%');

            })

            ->when($search !== '', function ($query) use ($search) {

                $query->where(function ($subQuery) use ($search) {

                    $subQuery->where('order_no', 'like', "%{$search}%")

                        ->orWhere(

                            'company_name',

                            'like',

                            "%{$search}%"

                        );

                });

            })

            ->withCount('detailPos')

            ->orderByDesc('id')

            ->paginate(8)

            ->withQueryString();



        // return view('pages.rnds.index', compact('pos', 'search'));
        // return view('pages.maintenance.index');

    }



    /**

     * Normalisasi kolom detail artikel menjadi array.

     */

    private function getItemDetail(DetailPo $item): array
    {

        $detail = $item->detail;



        if (is_string($detail)) {

            $decoded = json_decode($detail, true);

            $detail = is_array($decoded) ? $decoded : [];

        }



        return is_array($detail) ? $detail : [];

    }



    /**

     * Mengambil kode dan nama artikel dari JSON detail PO.

     */

    private function getArticleInfo(DetailPo $item): array
    {

        $detail = $this->getItemDetail($item);



        $articleCode = data_get($detail, 'article_code')

            ?: data_get($detail, 'article_nr_')

            ?: data_get($detail, 'nw_code')

            ?: '';



        $description = data_get($detail, 'description')

            ?: data_get($detail, 'nama_barang')

            ?: data_get($detail, 'item_name')

            ?: data_get($detail, 'name')

            ?: data_get($detail, 'detail')

            ?: '';



        return [

            'article_code' => trim((string) $articleCode),

            'description' => trim((string) $description),

            'detail' => $detail,

        ];

    }



    /**

     * Membuat atau mengambil folder PO dan folder artikel.

     *

     * Struktur:

     * RND archives / NWS 26 - 45 / KODE - NAMA

     */

    private function ensureArticleFolder(

        Po $po,

        DetailPo $item,

        GoogleDriveService $drive

    ): RndArchiveFolder {

        // Pastikan artikel benar-benar milik PO yang dipilih.

        $belongsToPo = DetailPo::query()

            ->where('po_id', $po->id)

            ->where('id', $item->id)

            ->exists();



        abort_unless($belongsToPo, 404, 'Artikel tidak ditemukan pada PO ini.');



        $info = $this->getArticleInfo($item);



        if ($info['article_code'] === '') {

            throw new \RuntimeException(

                'Kode artikel kosong. Periksa data detail PO.'

            );

        }



        /*

         * 1. Cari folder PO di database.

         */

        $poFolder = RndArchiveFolder::query()

            ->where('po_id', $po->id)

            ->whereNull('parent_id')

            ->where('folder_type', 'po')

            ->first();



        /*

         * 2. Jika belum ada, buat folder PO di Google Drive.

         */

        if (!$poFolder) {

            $driveFolder = $drive->createFolder($po->order_no);



            $poFolder = RndArchiveFolder::create([

                'parent_id' => null,

                'po_id' => $po->id,

                'detail_po_id' => null,

                'item_key' => null,

                'folder_name' => $po->order_no,

                'folder_type' => 'po',

                'google_drive_folder_id' => $driveFolder['id'],

                'created_by' => auth()->id(),

            ]);

        }



        /*

         * 3. Cari folder artikel di database.

         */

        $articleFolder = RndArchiveFolder::query()

            ->where('po_id', $po->id)

            ->where('detail_po_id', $item->id)

            ->where('folder_type', 'article')

            ->first();



        /*

         * 4. Jika belum ada, buat folder artikel di Google Drive.

         */

        if (!$articleFolder) {

            $folderName = $info['article_code'];



            if ($info['description'] !== '') {

                $folderName .= ' - ' . $info['description'];

            }



            $driveFolder = $drive->createFolder(

                Str::limit($folderName, 180, ''),

                $poFolder->google_drive_folder_id

            );



            $articleFolder = RndArchiveFolder::create([

                'parent_id' => $poFolder->id,

                'po_id' => $po->id,

                'detail_po_id' => $item->id,

                'item_key' => (string) $item->id,

                'folder_name' => Str::limit($folderName, 180, ''),

                'folder_type' => 'article',

                'google_drive_folder_id' => $driveFolder['id'],

                'created_by' => auth()->id(),

            ]);

        }



        return $articleFolder;

    }



    /**

     * Mengambil artikel PO beserta status folder dan jumlah foto.

     */

    public function samplePicturesItems(Po $po)
    {

        $items = DetailPo::query()

            ->where('po_id', $po->id)

            ->orderBy('id')

            ->get();



        $data = $items->map(function ($item, $index) use ($po) {

            $info = $this->getArticleInfo($item);



            $folder = RndArchiveFolder::query()

                ->where('po_id', $po->id)

                ->where('detail_po_id', $item->id)

                ->where('folder_type', 'article')

                ->first();



            $fileCount = $folder

                ? RndArchiveFile::where('folder_id', $folder->id)->count()

                : 0;



            return [

                'id' => $item->id,

                'number' => $index + 1,

                'article_code' => $info['article_code'],

                'description' => $info['description'],

                'detail' => $info['detail'],

                'folder_exists' => (bool) $folder,

                'file_count' => $fileCount,

                'archive_url' => route(

                    'rnd.sample-pictures.archive',

                    [

                        'po' => $po->id,

                        'detailPo' => $item->id,

                    ]

                ),

                'upload_url' => route(

                    'rnd.sample-pictures.archive.upload',

                    [

                        'po' => $po->id,

                        'detailPo' => $item->id,

                    ]

                ),

            ];

        })->values();



        return response()->json([

            'success' => true,

            'po' => [

                'id' => $po->id,

                'order_no' => $po->order_no,

                'company_name' => $po->company_name,

                'release_date' => $po->release_date,

                'total_items' => $data->count(),

            ],

            'items' => $data,

        ]);

    }



    /**

     * Membuka arsip foto sebuah artikel.

     * Folder otomatis dibuat saat pertama kali dibuka.

     */

    public function openArticleArchive(

        Po $po,

        DetailPo $detailPo,

        GoogleDriveService $drive

    ) {

        try {

            $folder = $this->ensureArticleFolder(

                $po,

                $detailPo,

                $drive

            );



            $files = RndArchiveFile::query()

                ->where('folder_id', $folder->id)

                ->orderByDesc('id')

                ->get()

                ->map(function ($file) {

                    return [

                        'id' => $file->id,

                        'name' => $file->original_name,

                        'mime_type' => $file->mime_type,

                        'file_size' => $file->file_size,

                        'url' => 'https://drive.google.com/file/d/'

                            . $file->google_drive_file_id . '/view',

                    ];

                })

                ->values();



            return response()->json([

                'success' => true,

                'folder' => [

                    'id' => $folder->google_drive_folder_id,

                    'database_id' => $folder->id,

                    'name' => $folder->folder_name,

                    'url' => 'https://drive.google.com/drive/folders/'

                        . $folder->google_drive_folder_id,

                ],

                'files' => $files,

            ]);

        } catch (Throwable $e) {

            Log::error('RND openArticleArchive gagal', [

                'po_id' => $po->id,

                'detail_po_id' => $detailPo->id,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'success' => false,

                'message' => config('app.debug')

                    ? $e->getMessage()

                    : 'Gagal membuka arsip foto. Periksa log Laravel.',

            ], 500);

        }

    }



    /**

     * Upload foto artikel ke Google Drive.

     */

    public function uploadArticlePhotos(
        Request $request,
        Po $po,
        DetailPo $detailPo,
        GoogleDriveService $drive
    ) {
        $validated = $request->validate([
            'folder_id' => ['nullable', 'integer'],
            // UI mengirim satu file per request agar progress dan kegagalan terisolasi.
            // Tetap menerima sampai 50 file jika endpoint dipanggil langsung.
            'photos' => ['required', 'array', 'min:1', 'max:50'],
            'photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:25600',
            ],
        ]);

        $startedAt = microtime(true);
        $folder = null;
        $uploaded = [];
        $failed = [];

        try {
            $folder = $this->resolveArticleArchiveFolder(
                $po,
                $detailPo,
                $validated['folder_id'] ?? null,
                $drive
            );

            foreach ($request->file('photos', []) as $index => $photo) {
                $fileStartedAt = microtime(true);
                $originalName = $photo->getClientOriginalName();

                try {
                    if (!$photo->isValid()) {
                        throw new \RuntimeException(
                            'File gagal diterima server (upload error: '.$photo->getError().').'
                        );
                    }

                    $mimeType = $photo->getMimeType() ?: 'application/octet-stream';
                    $extension = strtolower(
                        $photo->guessExtension()
                        ?: $photo->getClientOriginalExtension()
                        ?: 'jpg'
                    );

                    $driveFileName = Str::uuid().'.'.$extension;

                    // Ukur transfer ke Google Drive secara terpisah.
                    $driveStartedAt = microtime(true);

                    $driveFile = $drive->uploadFile(
                        $photo->getRealPath(),
                        $driveFileName,
                        $folder->google_drive_folder_id,
                        $mimeType
                    );

                    $driveDurationMs = (int) round(
                        (microtime(true) - $driveStartedAt) * 1000
                    );

                    if (empty($driveFile['id'])) {
                        throw new \RuntimeException('Google Drive tidak mengembalikan ID file.');
                    }

                    $dbStartedAt = microtime(true);

                    $savedFile = RndArchiveFile::create([
                        'folder_id' => $folder->id,
                        'original_name' => $originalName,
                        'google_drive_file_id' => $driveFile['id'],
                        'mime_type' => $driveFile['mime_type'] ?? $mimeType,
                        'file_size' => (int) ($driveFile['size'] ?? $photo->getSize()),
                        'uploaded_by' => auth()->id(),
                    ]);

                    $dbDurationMs = (int) round(
                        (microtime(true) - $dbStartedAt) * 1000
                    );

                    $uploaded[] = [
                        'id' => $savedFile->id,
                        'name' => $savedFile->original_name,
                    ];

                    Log::info('RND photo uploaded', [
                        'po_id' => $po->id,
                        'detail_po_id' => $detailPo->id,
                        'folder_id' => $folder->id,
                        'file' => $originalName,
                        'file_size_bytes' => (int) ($photo->getSize() ?? 0),
                        'drive_upload_ms' => $driveDurationMs,
                        'database_save_ms' => $dbDurationMs,
                        'total_file_ms' => (int) round((microtime(true) - $fileStartedAt) * 1000),
                    ]);
                } catch (Throwable $fileError) {
                    $failed[] = [
                        'name' => $originalName,
                        'message' => $fileError->getMessage(),
                    ];

                    Log::error('RND upload satu foto gagal', [
                        'po_id' => $po->id,
                        'detail_po_id' => $detailPo->id,
                        'folder_id' => $folder->id,
                        'file' => $originalName,
                        'file_size_bytes' => (int) ($photo->getSize() ?? 0),
                        'total_file_ms' => (int) round((microtime(true) - $fileStartedAt) * 1000),
                        'error' => $fileError->getMessage(),
                    ]);
                }
            }

            Cache::forget('rnd_drive_folder_items:'.$folder->google_drive_folder_id);

            $count = RndArchiveFile::where('folder_id', $folder->id)->count();
            $success = count($uploaded) > 0 && count($failed) === 0;

            return response()->json([
                'success' => $success,
                'partial' => count($uploaded) > 0 && count($failed) > 0,
                'message' => count($uploaded).' foto berhasil, '.count($failed).' gagal.',
                'uploaded' => $uploaded,
                'failed' => $failed,
                'file_count' => $count,
                'folder' => [
                    'database_id' => $folder->id,
                    'drive_id' => $folder->google_drive_folder_id,
                ],
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ], count($uploaded) > 0 ? 200 : (count($failed) > 0 ? 500 : 200));
        } catch (Throwable $e) {
            Log::error('RND uploadArticlePhotos gagal', [
                'po_id' => $po->id,
                'detail_po_id' => $detailPo->id,
                'folder_id' => $folder?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Gagal mengunggah foto. Periksa log Laravel.',
            ], 500);
        }
    }

    /**

     * Halaman detail PO lama, tetap dipertahankan.

     */

    public function samplePicturesDetail(Po $po)
    {

        $po->load('detailPos');



        return view('pages.rnds.detail', compact('po'));

    }

    private function resolveArticleArchiveFolder(

        Po $po,

        DetailPo $detailPo,

        string|int|null $folderId,

        GoogleDriveService $drive

    ): RndArchiveFolder {

        abort_unless(

            $detailPo->po_id == $po->id,

            404,

            'Artikel tidak termasuk PO ini.'

        );



        // Folder artikel utama otomatis dibuat jika belum ada.

        $articleFolder = $this->ensureArticleFolder(

            $po,

            $detailPo,

            $drive

        );



        if (!$folderId || (string) $folderId === (string) $articleFolder->id) {

            return $articleFolder;

        }



        $folder = RndArchiveFolder::query()

            ->whereKey($folderId)

            ->where('po_id', $po->id)

            ->where('detail_po_id', $detailPo->id)

            ->firstOrFail();



        // Telusuri parent hingga ditemukan folder artikel utama.

        $current = $folder;

        $visited = [];



        while ($current) {

            if (isset($visited[$current->id])) {

                abort(403, 'Struktur folder tidak valid.');

            }



            $visited[$current->id] = true;



            if ((int) $current->id === (int) $articleFolder->id) {

                return $folder;

            }



            if (!$current->parent_id) {

                break;

            }



            $current = RndArchiveFolder::query()

                ->whereKey($current->parent_id)

                ->where('po_id', $po->id)

                ->where('detail_po_id', $detailPo->id)

                ->first();

        }



        abort(403, 'Folder bukan bagian dari arsip artikel ini.');

    }



    public function browseArchiveFolder(

        Po $po,

        DetailPo $detailPo,

        string $folder,

        GoogleDriveService $drive

    ) {

        try {

            $activeFolder = $this->resolveArticleArchiveFolder(

                $po,

                $detailPo,

                $folder,

                $drive

            );



            // Cache singkat untuk mengurangi round-trip Google Drive saat folder dibuka berulang.
            // Cache akan dibersihkan ketika folder berubah.
            $driveCacheKey = 'rnd_drive_folder_items:' . $activeFolder->google_drive_folder_id;

            $driveItems = Cache::remember(
                $driveCacheKey,
                now()->addSeconds(8),
                function () use ($drive, $activeFolder) {
                    return $drive->listFolderContents(
                        $activeFolder->google_drive_folder_id
                    );
                }
            );



            $items = [];



            foreach ($driveItems as $item) {

                $isFolder = $item['type'] === 'folder';



                if ($isFolder) {

                    // Sinkronkan folder Drive dengan metadata database.

                    $child = RndArchiveFolder::firstOrCreate(

                        [

                            'google_drive_folder_id' => $item['id'],

                        ],

                        [

                            'parent_id' => $activeFolder->id,

                            'po_id' => $po->id,

                            'detail_po_id' => $detailPo->id,

                            'item_key' => null,

                            'folder_name' => $item['name'],

                            'folder_type' => 'subfolder',

                            'created_by' => auth()->id(),

                        ]

                    );



                    // Jangan tampilkan folder yang sudah tercatat di artikel lain.

                    if (

                        (int) $child->po_id !== (int) $po->id ||

                        (int) $child->detail_po_id !== (int) $detailPo->id

                    ) {

                        continue;

                    }

                }



                $items[] = [

                    'id' => $item['id'],

                    'database_id' => $isFolder

                        ? ($child->id ?? null)

                        : null,

                    'name' => $item['name'],

                    'type' => $item['type'],

                    'mime_type' => $item['mime_type'],

                    'size' => $item['size'],

                    'url' => $item['url'],

                    'created_time' => $item['created_time'],

                    'modified_time' => $item['modified_time'],

                ];

            }



            $breadcrumbs = [];

            $cursor = $activeFolder;



            while ($cursor) {

                array_unshift($breadcrumbs, [

                    'id' => $cursor->id,

                    'name' => $cursor->folder_name,

                ]);



                if (!$cursor->parent_id) {

                    break;

                }



                $cursor = RndArchiveFolder::query()

                    ->whereKey($cursor->parent_id)

                    ->where('po_id', $po->id)

                    ->where('detail_po_id', $detailPo->id)

                    ->first();

            }



            return response()->json([

                'success' => true,

                'active_folder' => [

                    'id' => $activeFolder->id,

                    'drive_id' => $activeFolder->google_drive_folder_id,

                    'name' => $activeFolder->folder_name,

                    'url' => 'https://drive.google.com/drive/folders/'

                        . $activeFolder->google_drive_folder_id,

                ],

                'breadcrumbs' => $breadcrumbs,

                'items' => $items,

            ]);

        } catch (Throwable $e) {

            Log::error('RND browseArchiveFolder gagal', [

                'po_id' => $po->id,

                'detail_po_id' => $detailPo->id,

                'folder_id' => $folder,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'success' => false,

                'message' => config('app.debug')

                    ? $e->getMessage()

                    : 'Gagal membuka isi folder.',

            ], 500);

        }

    }



    public function createArchiveFolder(

        Request $request,

        Po $po,

        DetailPo $detailPo,

        GoogleDriveService $drive

    ) {

        $validated = $request->validate([

            'name' => ['required', 'string', 'max:150'],

            'parent_folder_id' => ['nullable', 'integer'],

        ]);



        try {

            $parent = $this->resolveArticleArchiveFolder(

                $po,

                $detailPo,

                $validated['parent_folder_id'] ?? null,

                $drive

            );



            $name = trim($validated['name']);



            if ($name === '' || in_array($name, ['.', '..'], true)) {

                return response()->json([

                    'success' => false,

                    'message' => 'Nama folder tidak valid.',

                ], 422);

            }



            // Jangan izinkan folder dengan nama yang sama pada parent ini.

            $existing = collect(

                $drive->listFolderContents(

                    $parent->google_drive_folder_id

                )

            )->first(

                    fn($item) =>

                        $item['type'] === 'folder'

                        && mb_strtolower($item['name']) === mb_strtolower($name)

                );



            if ($existing) {

                return response()->json([

                    'success' => false,

                    'message' => 'Folder dengan nama tersebut sudah ada.',

                ], 422);

            }



            $created = $drive->createFolder(

                $name,

                $parent->google_drive_folder_id

            );



            $child = RndArchiveFolder::create([

                'parent_id' => $parent->id,

                'po_id' => $po->id,

                'detail_po_id' => $detailPo->id,

                'item_key' => null,

                'folder_name' => $name,

                'folder_type' => 'subfolder',

                'google_drive_folder_id' => $created['id'],

                'created_by' => auth()->id(),

            ]);



            Cache::forget(
                'rnd_drive_folder_items:' . $parent->google_drive_folder_id
            );

            return response()->json([

                'success' => true,

                'message' => 'Folder berhasil dibuat.',

                'item' => [

                    'id' => $created['id'],

                    'database_id' => $child->id,

                    'name' => $name,

                    'type' => 'folder',

                    'url' => $created['url']

                        ?? 'https://drive.google.com/drive/folders/'

                        . $created['id'],

                ],

            ]);

        } catch (Throwable $e) {

            Log::error('RND createArchiveFolder gagal', [

                'po_id' => $po->id,

                'detail_po_id' => $detailPo->id,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'success' => false,

                'message' => config('app.debug')

                    ? $e->getMessage()

                    : 'Gagal membuat folder.',

            ], 500);

        }

    }



    /**

     * Pastikan item Drive merupakan isi langsung dari folder

     * yang sudah diverifikasi sebagai bagian dari arsip artikel.

     */

    private function assertDriveItemInsideFolder(
        GoogleDriveService $drive,
        RndArchiveFolder $parent,
        string $driveItemId
    ): array {
        // Hindari request listFolderContents ke Google Drive hanya untuk validasi rename/delete.
        // Validasi menggunakan metadata lokal yang telah disinkronkan saat folder dibuka.
        $dbFolder = RndArchiveFolder::query()
            ->where('google_drive_folder_id', $driveItemId)
            ->where('parent_id', $parent->id)
            ->where('po_id', $parent->po_id)
            ->where('detail_po_id', $parent->detail_po_id)
            ->first();

        if ($dbFolder) {
            return [
                'id' => $dbFolder->google_drive_folder_id,
                'name' => $dbFolder->folder_name,
                'type' => 'folder',
                'mime_type' => 'application/vnd.google-apps.folder',
            ];
        }

        $dbFile = RndArchiveFile::query()
            ->where('google_drive_file_id', $driveItemId)
            ->where('folder_id', $parent->id)
            ->first();

        if ($dbFile) {
            return [
                'id' => $dbFile->google_drive_file_id,
                'name' => $dbFile->original_name,
                'type' => 'file',
                'mime_type' => $dbFile->mime_type,
            ];
        }

        abort(404, 'Item tidak ditemukan di folder aktif.');
    }

    public function renameArchiveItem(

        Request $request,

        Po $po,

        DetailPo $detailPo,

        string $folder,

        GoogleDriveService $drive

    ) {

        $validated = $request->validate([

            'name' => ['required', 'string', 'max:150'],

            'parent_folder_id' => ['required', 'integer'],

        ]);



        try {

            $parent = $this->resolveArticleArchiveFolder(

                $po,

                $detailPo,

                $validated['parent_folder_id'],

                $drive

            );



            $item = $this->assertDriveItemInsideFolder(

                $drive,

                $parent,

                $folder

            );



            $name = trim($validated['name']);



            if ($name === '' || in_array($name, ['.', '..'], true)) {

                return response()->json([

                    'success' => false,

                    'message' => 'Nama item tidak valid.',

                ], 422);

            }



            $updated = $drive->renameItem($folder, $name);

            Cache::forget(
                'rnd_drive_folder_items:' . $parent->google_drive_folder_id
            );



            if ($item['type'] === 'folder') {

                RndArchiveFolder::query()

                    ->where('google_drive_folder_id', $folder)

                    ->where('po_id', $po->id)

                    ->where('detail_po_id', $detailPo->id)

                    ->update(['folder_name' => $name]);

            } else {

                RndArchiveFile::query()

                    ->where('google_drive_file_id', $folder)

                    ->update(['original_name' => $name]);

            }



            return response()->json([

                'success' => true,

                'message' => 'Nama berhasil diperbarui.',

                'item' => [

                    'id' => $updated['id'],

                    'name' => $updated['name'],

                    'type' => $item['type'],

                ],

            ]);

        } catch (Throwable $e) {

            Log::error('RND renameArchiveItem gagal', [

                'drive_item_id' => $folder,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'success' => false,

                'message' => config('app.debug')

                    ? $e->getMessage()

                    : 'Gagal mengganti nama item.',

            ], 500);

        }

    }



    public function deleteArchiveItem(

        Request $request,

        Po $po,

        DetailPo $detailPo,

        string $folder,

        GoogleDriveService $drive

    ) {

        $validated = $request->validate([

            'parent_folder_id' => ['required', 'integer'],

        ]);



        try {

            $parent = $this->resolveArticleArchiveFolder(

                $po,

                $detailPo,

                $validated['parent_folder_id'],

                $drive

            );



            $item = $this->assertDriveItemInsideFolder(

                $drive,

                $parent,

                $folder

            );



            if ($item['type'] === 'folder') {

                $dbFolder = RndArchiveFolder::query()

                    ->where('google_drive_folder_id', $folder)

                    ->where('po_id', $po->id)

                    ->where('detail_po_id', $detailPo->id)

                    ->first();



                // Jangan izinkan menghapus folder artikel utama.

                if (

                    !$dbFolder ||

                    $dbFolder->folder_type === 'article'

                ) {

                    return response()->json([

                        'success' => false,

                        'message' => 'Folder ini tidak dapat dihapus.',

                    ], 422);

                }

            }



            // Pindahkan ke Trash Google Drive.

            $drive->trashItem($folder);

            Cache::forget(
                'rnd_drive_folder_items:' . $parent->google_drive_folder_id
            );



            if ($item['type'] === 'folder') {

                // Hapus metadata anak secara rekursif setelah folder masuk Trash.

                $this->deleteArchiveFolderMetadata($dbFolder);

            } else {

                RndArchiveFile::query()

                    ->where('google_drive_file_id', $folder)

                    ->delete();

            }



            return response()->json([

                'success' => true,

                'message' => 'Item dipindahkan ke Trash Google Drive.',

            ]);

        } catch (Throwable $e) {

            Log::error('RND deleteArchiveItem gagal', [

                'drive_item_id' => $folder,

                'error' => $e->getMessage(),

            ]);



            return response()->json([

                'success' => false,

                'message' => config('app.debug')

                    ? $e->getMessage()

                    : 'Gagal menghapus item.',

            ], 500);

        }

    }



    private function deleteArchiveFolderMetadata(

        RndArchiveFolder $folder

    ): void {

        foreach ($folder->children()->get() as $child) {

            $this->deleteArchiveFolderMetadata($child);

        }



        RndArchiveFile::where('folder_id', $folder->id)->delete();



        $folder->delete();

    }



}
