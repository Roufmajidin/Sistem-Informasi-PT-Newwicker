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

                // Case-insensitive, partial match, termasuk frasa seperti "floor lamp".

                $like = '%' . addcslashes(mb_strtolower($search), '%_\\\\') . '%';

                $query->where(function ($q) use ($like) {

                    $q->whereRaw('LOWER(order_no) LIKE ?', [$like])

                        ->orWhereRaw('LOWER(company_name) LIKE ?', [$like])

                        ->orWhereHas('detailPos', function ($detail) use ($like) {

                            // JSON detail memuat article_code, article_nr_, nw_code, nw_description.

                            $detail->whereRaw('LOWER(CAST(detail AS CHAR)) LIKE ?', [$like]);

                        })

                        ->orWhereHas('detailPos', function ($detail) use ($like) {

                            // Mencari nama file di folder artikel DAN subfoldernya.

                            $detail->whereExists(function ($sub) use ($like) {

                                $sub->selectRaw('1')

                                    ->from('rnd_archive_folders as raf')

                                    ->join('rnd_archive_files as rfile', 'rfile.folder_id', '=', 'raf.id')

                                    ->whereColumn('raf.detail_po_id', 'detail_po.id')

                                    ->whereRaw('LOWER(rfile.original_name) LIKE ?', [$like]);

                            });

                        });

                });

            })

            ->withCount('detailPos')

            ->orderByDesc('id')

            ->paginate(8)

            ->withQueryString();

        return view('pages.rnds.index', compact('pos', 'search'));

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

        // ARTICLE CODE

        $articleCode = data_get($detail, 'article_code')

            ?: data_get($detail, 'article_nr_')

            ?: data_get($detail, 'nw_code')

            ?: '';

        // DESCRIPTION

        $description = data_get($detail, 'nw_description')

            ?: data_get($detail, 'description')

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

     * Pindahkan folder PO lama dari root RND archives ke folder Buyer saat PO dibuka.

     * Tidak mengubah ID Google Drive, DB, atau isi folder; hanya parent Drive.

     * Tidak memindahkan folder yang sudah berada di lokasi lain.

     */

    private function groupExistingPoFolderByBuyer(

        Po $po,

        RndArchiveFolder $poFolder,

        GoogleDriveService $drive

    ): void {

        $rootId = trim((string) config('services.google_drive.root_folder_id'));

        $sourceId = trim((string) $poFolder->google_drive_folder_id);

        if ($rootId === '' || $sourceId === '') return;

        try {

            Cache::lock('rnd_group_po:' . $po->id, 60)->block(10, function () use ($po, $drive, $rootId, $sourceId) {

                $source = $drive->getArchiveItem($sourceId);

                if (($source['trashed'] ?? true)

                    || ($source['mime_type'] ?? '') !== 'application/vnd.google-apps.folder') return;

                $parents = $source['parents'] ?? [];

                if (count($parents) !== 1) return;

                // Only legacy PO folders directly in RND root can be moved.

                if ($parents[0] !== $rootId) return;

                $buyer = trim((string) ($po->company_name ?: 'BUYER TANPA NAMA'));

                $buyer = trim(preg_replace('/\s+/u', ' ', preg_replace('/[\x00-\x1F\x7F]/u', ' ', $buyer)));

                if ($buyer === '') $buyer = 'BUYER TANPA NAMA';

                $matches = array_values(array_filter($drive->listFolderContents($rootId),

                    fn ($entry) => ($entry['type'] ?? '') === 'folder'

                        && mb_strtolower(trim((string) ($entry['name'] ?? ''))) === mb_strtolower($buyer)

                ));

                if (count($matches) > 1) {

                    Log::warning('RND buyer folder duplicate; migration skipped', ['po_id' => $po->id, 'buyer' => $buyer]);

                    return;

                }

                $buyerId = count($matches) === 1

                    ? $matches[0]['id']

                    : $drive->createFolder($buyer, $rootId)['id'];

                foreach ($drive->listFolderContents($buyerId) as $child) {

                    if (($child['type'] ?? '') === 'folder'

                        && ($child['id'] ?? '') !== $sourceId

                        && mb_strtolower(trim((string) ($child['name'] ?? '')))

                            === mb_strtolower(trim((string) ($source['name'] ?? $po->order_no)))) {

                        Log::warning('RND PO destination collision; migration skipped', ['po_id' => $po->id, 'buyer' => $buyer]);

                        return;

                    }

                }

                $drive->moveArchiveItem($sourceId, $rootId, $buyerId);

                Log::info('RND legacy PO folder grouped under Buyer', [

                    'po_id' => $po->id, 'buyer' => $buyer, 'drive_folder_id' => $sourceId,

                ]);

            });

        } catch (Throwable $e) {

            // Jangan blokir fitur buka artikel/upload jika proses pengelompokan gagal.

            Log::warning('RND grouping skipped', ['po_id' => $po->id, 'error' => $e->getMessage()]);

        }

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

        $bindingStarted = microtime(true);

        $bindingPhase = 'start';

        $bindingLast = $bindingStarted;

        $bindingTimings = [];

        $markBinding = static function (string $phase) use (&$bindingPhase, &$bindingLast, &$bindingTimings): void {

            $now = microtime(true);

            $bindingTimings[$bindingPhase . '_ms'] = (int) round(($now - $bindingLast) * 1000);

            $bindingPhase = $phase;

            $bindingLast = $now;

        };

        try {

        // Pastikan artikel benar-benar milik PO yang dipilih.

        $belongsToPo = DetailPo::query()

            ->where('po_id', $po->id)

            ->where('id', $item->id)

            ->exists();

        abort_unless($belongsToPo, 404, 'Artikel tidak ditemukan pada PO ini.');

        $markBinding('article_info');

        $info = $this->getArticleInfo($item);

        if ($info['article_code'] === '' && $info['description'] === '') {

            throw new \RuntimeException(

                'Kode artikel dan description kosong. Isi minimal description untuk membuat folder.'

            );

        }

        // Struktur baru: RND archives / BUYER / PO / ARTIKEL.

        // Buyer hanya disimpan di Drive, tanpa mengubah schema database.

        // Folder PO lama dipindahkan secara bertahap ketika artikel PO dibuka.

        $buyerName = trim((string) ($po->company_name ?: 'BUYER TANPA NAMA'));

        $buyerName = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $buyerName);

        $buyerName = trim(preg_replace('/\s+/u', ' ', $buyerName));

        if ($buyerName === '') $buyerName = 'BUYER TANPA NAMA';

        /*

         * 1. Cari folder PO di database.

         */

        $markBinding('po_folder_db_lookup');

        // Default false: PO yang sudah ada tidak dibuat ulang pada request ini.

        $poFolderJustCreated = false;

        $poFolder = RndArchiveFolder::query()

            ->where('po_id', $po->id)

            ->whereNull('parent_id')

            ->where('folder_type', 'po')

            ->first();

        /*

         * 2. Jika belum ada, buat folder PO di Google Drive.

         */

        if (!$poFolder) {

            $rootId = (string) config('services.google_drive.root_folder_id');

            if ($rootId === '') throw new \RuntimeException('Google Drive root folder belum dikonfigurasi.');

            // Hindari folder buyer duplikat; gunakan lock untuk request bersamaan.

            $markBinding('buyer_folder_lock_and_drive');

            $buyerFolderId = Cache::lock('rnd_buyer_folder:' . sha1($rootId . '|' . mb_strtolower($buyerName)), 30)

                ->block(15, function () use ($drive, $rootId, $buyerName) {

                    foreach ($drive->listFolderContents($rootId) as $candidate) {

                        if (($candidate['type'] ?? '') === 'folder'

                            && mb_strtolower(trim($candidate['name'] ?? '')) === mb_strtolower($buyerName)) {

                            return $candidate['id'];

                        }

                    }

                    return $drive->createFolder($buyerName, $rootId)['id'];

                });

            // Reuse PO folder bila sudah dibuat di Buyer (mis. setelah retry).

            $markBinding('buyer_folder_resolved');

            $driveFolder = null;

            foreach ($drive->listFolderContents($buyerFolderId) as $candidate) {

                if (($candidate['type'] ?? '') === 'folder'

                    && trim($candidate['name'] ?? '') === trim((string) $po->order_no)) {

                    $driveFolder = ['id' => $candidate['id']];

                    break;

                }

            }

            if (!$driveFolder) {

                $driveFolder = $drive->createFolder($po->order_no, $buyerFolderId);

            }

            $poFolderJustCreated = true;

            $markBinding('po_folder_db_create');

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

        // Folder artikel yang sudah tercatat dapat langsung digunakan tanpa

        // melakukan operasi jaringan (termasuk migrasi PO lama) pada request ini.

        // Migrasi PO lama tetap berlangsung saat folder artikel baru dibuat.

        $markBinding('existing_article_db_lookup');

        $existingArticleFolder = RndArchiveFolder::query()

            ->where('po_id', $po->id)

            ->where('detail_po_id', $item->id)

            ->where('folder_type', 'article')

            ->first();

        if ($existingArticleFolder && trim((string) $existingArticleFolder->google_drive_folder_id) !== '') {

            return $existingArticleFolder;

        }

        // Rapikan PO lama secara bertahap hanya ketika artikelnya dibuka.

        // Jika gagal, arsip tetap bisa dibuka dari folder lamanya.

        // Folder PO baru sudah dibuat di bawah Buyer, tidak perlu migrasi lagi.

        // Folder PO lama tetap ditangani oleh migrasi kompatibilitas.

        if (!$poFolderJustCreated) {

            $markBinding('po_folder_migration');

            $this->groupExistingPoFolderByBuyer($po, $poFolder, $drive);

        }

        /*

         * Folder artikel: Google Drive adalah sumber utama. Jangan langsung

         * createFolder() ketika metadata DB belum ditemukan.

         */

        // Nama folder memakai kode + deskripsi, bukan kode saja.
        // detail_po_id tetap menjadi identitas unik di database.
        $parts = array_filter([$info['article_code'], $info['description']],
            static fn ($value) => trim((string) $value) !== '');
        $folderName = trim(implode(' - ', $parts));
        $folderName = trim(preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', ' ', $folderName));
        $folderName = Str::limit($folderName, 160, '');
        $legacyFolderName = $info['article_code'] !== ''
            ? $info['article_code'] : $info['description'];

        $markBinding('article_lock_wait_and_work');

        $articleFolder = Cache::lock('rnd_article_folder:' . $po->id . ':' . $item->id, 60)

            ->block(15, function () use ($po, $item, $drive, $poFolder, $folderName, $legacyFolderName, $bindingStarted, $markBinding) {

                $markBinding('article_lock_acquired');

                Log::info('RND article lock acquired', [

                    'po_id' => $po->id, 'detail_po_id' => $item->id,

                    'elapsed_from_start_ms' => (int) round((microtime(true) - $bindingStarted) * 1000),

                ]);

                $record = RndArchiveFolder::query()

                    ->where('po_id', $po->id)

                    ->where('detail_po_id', $item->id)

                    ->where('folder_type', 'article')

                    ->first();

                $poDriveId = (string) $poFolder->google_drive_folder_id;

                if ($poDriveId === '') {

                    throw new \RuntimeException('ID Google Drive folder PO kosong.');

                }

                // Fast path: bila DB sudah punya ID folder artikel, gunakan ID tersebut.

                // Hindari list seluruh isi folder PO pada setiap buka artikel.

                // Jika folder di Drive dipindah/hapus manual, operasi file berikutnya

                // akan mengembalikan error yang bisa ditangani terpisah.

                if ($record && trim((string) $record->google_drive_folder_id) !== '') {

                    return $record;

                }

                // DB belum mempunyai folder: cari di Drive sebelum create agar

                // retry tidak membuat folder duplikat.

                $driveLookupStarted = microtime(true);

                $markBinding('article_drive_lookup');

                $children = $drive->listFolderContents($poDriveId);

                $markBinding('article_drive_lookup_done');

                Log::info('RND article folder lookup timing', [

                    'po_id' => $po->id, 'detail_po_id' => $item->id,

                    'drive_lookup_ms' => (int) round((microtime(true) - $driveLookupStarted) * 1000),

                ]);

                if ($record) {
                    throw new \RuntimeException('Folder artikel sudah ada di database tetapi Google Drive Folder ID kosong. Periksa metadata sebelum membuat folder baru.');
                }

                // Hindari mengadopsi folder milik detail PO lain.
                $isBoundToOther = static function (string $driveId) use ($po, $item): bool {
                    return RndArchiveFolder::query()
                        ->where('google_drive_folder_id', $driveId)
                        ->where(function ($q) use ($po, $item) {
                            $q->where('po_id', '!=', $po->id)
                                ->orWhere('detail_po_id', '!=', $item->id)
                                ->orWhereNull('detail_po_id');
                        })->exists();
                };

                $findFolders = static function (string $name) use ($children): array {
                    return array_values(array_filter($children, static function ($child) use ($name) {
                        return ($child['type'] ?? '') === 'folder'
                            && mb_strtolower(trim((string) ($child['name'] ?? '')))
                                === mb_strtolower(trim($name));
                    }));
                };

                $matches = $findFolders($folderName);
                if (count($matches) > 1) {
                    throw new \RuntimeException('Ada beberapa folder Google Drive dengan nama ' . $folderName . '. Periksa duplikat sebelum melanjutkan.');
                }

                // Bila nama deskripsi identik, gunakan ID detail PO sebagai pembeda.
                if (count($matches) === 1 && $isBoundToOther((string) $matches[0]['id'])) {
                    $folderName = Str::limit($folderName, 140, '') . ' - ITEM-' . $item->id;
                    $matches = $findFolders($folderName);
                    if (count($matches) > 1 || (count($matches) === 1 && $isBoundToOther((string) $matches[0]['id']))) {
                        throw new \RuntimeException('Konflik folder artikel unik. Periksa metadata Google Drive.');
                    }
                }

                // Kompatibilitas folder lama (hanya bila belum terikat artikel lain).
                if (count($matches) === 0 && $legacyFolderName !== $folderName) {
                    $legacyMatches = $findFolders($legacyFolderName);
                    if (count($legacyMatches) === 1 && !$isBoundToOther((string) $legacyMatches[0]['id'])) {
                        $matches = $legacyMatches;
                        $folderName = $legacyFolderName;
                    }
                }

                if (count($matches) === 1) {
                    $driveFolderId = (string) $matches[0]['id'];
                } else {
                    $driveCreateStarted = microtime(true);
                    $markBinding('article_drive_create');
                    $created = $drive->createFolder($folderName, $poDriveId);
                    $markBinding('article_drive_create_done');
                    Log::info('RND article folder create timing', [
                        'po_id' => $po->id, 'detail_po_id' => $item->id,
                        'drive_create_ms' => (int) round((microtime(true) - $driveCreateStarted) * 1000),
                    ]);
                    $driveFolderId = (string) $created['id'];
                }

                // A Drive folder ID must map to exactly one DB folder row.

                // Reuse the existing row only when it belongs to this article.

                // Never silently assign another article to the same Drive folder.

                $existingByDriveId = RndArchiveFolder::query()

                    ->where('google_drive_folder_id', $driveFolderId)

                    ->first();

                if ($existingByDriveId) {

                    if ((int) $existingByDriveId->po_id === (int) $po->id

                        && (int) $existingByDriveId->detail_po_id === (int) $item->id

                        && $existingByDriveId->folder_type === 'article') {

                        return $existingByDriveId;

                    }

                    Log::warning('RND Drive folder already bound to another DB record', [

                        'drive_folder_id' => $driveFolderId,

                        'existing_db_folder_id' => $existingByDriveId->id,

                        'existing_po_id' => $existingByDriveId->po_id,

                        'existing_detail_po_id' => $existingByDriveId->detail_po_id,

                        'requested_po_id' => $po->id,

                        'requested_detail_po_id' => $item->id,

                    ]);

                    throw new \RuntimeException(

                        'Folder Google Drive sudah terhubung ke artikel lain (DB folder ID '

                        . $existingByDriveId->id . '). Tidak membuat relasi duplikat. '

                        . 'Periksa metadata folder dan artikel sebelum melanjutkan.'

                    );

                }

                $markBinding('article_db_create');

                try {

                    return RndArchiveFolder::create([

                    'parent_id' => $poFolder->id,

                    'po_id' => $po->id,

                    'detail_po_id' => $item->id,

                    'item_key' => (string) $item->id,

                    'folder_name' => $folderName,

                    'folder_type' => 'article',

                    'google_drive_folder_id' => $driveFolderId,

                    'created_by' => auth()->id(),

                    ]);

                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {

                    // Concurrent request / different cache lock driver: check the winner.

                    $winner = RndArchiveFolder::query()

                        ->where('google_drive_folder_id', $driveFolderId)->first();

                    if ($winner && (int) $winner->po_id === (int) $po->id

                        && (int) $winner->detail_po_id === (int) $item->id

                        && $winner->folder_type === 'article') {

                        return $winner;

                    }

                    throw new \RuntimeException(

                        'Folder Google Drive sudah dipakai oleh metadata artikel lain. '

                        . 'Periksa rnd_archive_folders sebelum mencoba kembali.', 0, $e

                    );

                }

            });

        $markBinding('complete');

        return $articleFolder;

        } finally {

            $markBinding('finished');

            Log::info('RND article binding full timing', [

                'po_id' => $po->id,

                'detail_po_id' => $item->id,

                'total_ms' => (int) round((microtime(true) - $bindingStarted) * 1000),

                'phases' => $bindingTimings,

            ]);

        }

    }

    /**

     * Mengambil artikel PO beserta status folder dan jumlah foto.

     */

    private function getArchiveFolderIds(int $folderId): array

    {

        $ids = [$folderId];

        $pending = [$folderId];

        while (!empty($pending)) {

            $children = RndArchiveFolder::query()

                ->whereIn('parent_id', $pending)

                ->pluck('id')

                ->all();

            $children = array_values(array_diff($children, $ids));

            if (empty($children)) {

                break;

            }

            $ids = array_merge($ids, $children);

            $pending = $children;

        }

        return $ids;

    }

    private function countArticlePhotos(RndArchiveFolder $folder): int

    {

        $folderIds = $this->getArchiveFolderIds($folder->id);

        return RndArchiveFile::query()

            ->whereIn('folder_id', $folderIds)

            ->where(function ($query) {

                $query->where('mime_type', 'like', 'image/%')

                    ->orWhere('original_name', 'like', '%.jpg')

                    ->orWhere('original_name', 'like', '%.jpeg')

                    ->orWhere('original_name', 'like', '%.png')

                    ->orWhere('original_name', 'like', '%.webp');

            })

            ->count();

    }

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

                ? $this->countArticlePhotos($folder)

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

            // Drive adalah sumber kebenaran. Jangan menampilkan "kosong" ketika

            // sebenarnya folder Drive berisi file yang belum tercatat di DB.

            $openListStarted = microtime(true);

            $driveItems = $drive->listFolderContents($folder->google_drive_folder_id);

            Log::info('RND archive open Drive listing timing', ['detail_po_id' => $detailPo->id, 'ms' => (int) round((microtime(true) - $openListStarted) * 1000)]);

            $openSyncStarted = microtime(true);

            $this->syncDriveFilesToDatabase($folder, $driveItems);

            Log::info('RND archive open DB sync timing', ['detail_po_id' => $detailPo->id, 'ms' => (int) round((microtime(true) - $openSyncStarted) * 1000)]);

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

                        'url' => 'https\://drive.google.com/file/d/'

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

                    'url' => 'https\://drive.google.com/drive/folders/'

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

     * Sinkronisasi aman: hanya mendaftarkan file yang memang ada di folder Drive.

     * Tidak menghapus data DB atau memindahkan file.

     */

    private function syncDriveFilesToDatabase(RndArchiveFolder $folder, array $items): void

    {

        foreach ($items as $item) {

            if (($item['type'] ?? '') !== 'file' || empty($item['id'])) continue;

            $existing = RndArchiveFile::query()

                ->where('google_drive_file_id', $item['id'])->first();

            if ($existing) {

                if ((int) $existing->folder_id !== (int) $folder->id) {

                    Log::warning('RND file Drive tercatat di folder DB berbeda; tidak dipindahkan otomatis', [

                        'drive_file_id' => $item['id'], 'expected_folder_id' => $folder->id,

                        'actual_folder_id' => $existing->folder_id,

                    ]);

                }

                continue;

            }

            RndArchiveFile::create([

                'folder_id' => $folder->id,

                'original_name' => $item['name'] ?? $item['id'],

                'google_drive_file_id' => $item['id'],

                'mime_type' => $item['mime_type'] ?? 'application/octet-stream',

                'file_size' => (int) ($item['size'] ?? 0),

                'uploaded_by' => auth()->id(),

            ]);

        }

    }

    /**

     * Upload foto artikel ke Google Drive.

     */

    /**

     * Fast path khusus upload: validasi folder berdasarkan relasi DB yang sudah

     * tercatat. Hindari panggilan Google Drive untuk setiap file/request.

     * Folder baru tetap melalui alur lama ensureArticleFolder().

     */

    private function resolveUploadFolderFast(

        Po $po,

        DetailPo $detailPo,

        string|int|null $folderId,

        GoogleDriveService $drive

    ): RndArchiveFolder {

        abort_unless((int) $detailPo->po_id === (int) $po->id, 404);

        $article = RndArchiveFolder::query()

            ->where('po_id', $po->id)

            ->where('detail_po_id', $detailPo->id)

            ->where('folder_type', 'article')

            ->first();

        if (!$article || !$article->google_drive_folder_id) {

            return $this->resolveArticleArchiveFolder($po, $detailPo, $folderId, $drive);

        }

        if (!$folderId || (string) $folderId === (string) $article->id) {

            return $article;

        }

        $folder = RndArchiveFolder::query()

            ->whereKey($folderId)

            ->where('po_id', $po->id)

            ->where('detail_po_id', $detailPo->id)

            ->firstOrFail();

        $cursor = $folder;

        $visited = [];

        while ($cursor) {

            abort_if(isset($visited[$cursor->id]), 403, 'Struktur folder tidak valid.');

            $visited[$cursor->id] = true;

            if ((int) $cursor->id === (int) $article->id) {

                abort_unless($folder->google_drive_folder_id, 409, 'ID folder Drive kosong.');

                return $folder;

            }

            if (!$cursor->parent_id) break;

            $cursor = RndArchiveFolder::query()

                ->whereKey($cursor->parent_id)

                ->where('po_id', $po->id)

                ->where('detail_po_id', $detailPo->id)

                ->first();

        }

        abort(403, 'Folder bukan bagian dari arsip artikel ini.');

    }

    /** Browser-direct upload handshake on the EXISTING photo-upload route. */
    private function handleDirectPhotoUpload(Request $request, Po $po, DetailPo $detailPo, GoogleDriveService $drive)
    {
        abort_unless((int) $detailPo->po_id === (int) $po->id, 404);
        $action = $request->input('direct_action');
        if ($action === 'start') {
            $data = $request->validate([
                'folder_id' => ['nullable', 'integer'],
                'name' => ['required', 'string', 'max:255'],
                'size' => ['required', 'integer', 'min:1', 'max:26214400'],
                'mime_type' => ['required', 'in:image/jpeg,image/png,image/webp'],
            ]);
            $folder = $this->resolveUploadFolderFast($po, $detailPo, $data['folder_id'] ?? null, $drive);
            // Simpan nama asli di Drive; ticket UUID menjadi identitas upload unik.
            $driveName = basename(str_replace('\\', '/', $data['name']));
            $driveName = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $driveName));
            abort_if($driveName === '' || in_array($driveName, ['.', '..'], true), 422, 'Nama foto tidak valid.');
            $ticket = (string) Str::uuid();
            $sessionUrl = $drive->startBrowserUpload($driveName, $folder->google_drive_folder_id,
                $data['mime_type'], (int) $data['size'], $ticket);
            Cache::put('rnd_direct_upload:' . $ticket, [
                'user_id' => auth()->id(), 'po_id' => $po->id, 'detail_po_id' => $detailPo->id,
                'folder_id' => $folder->id, 'drive_folder_id' => $folder->google_drive_folder_id,
                'drive_name' => $driveName, 'original_name' => $data['name'],
                'size' => (int) $data['size'], 'mime_type' => $data['mime_type'],
            ], now()->addMinutes(30));
            return response()->json(['success' => true, 'ticket' => $ticket,
                'session_url' => $sessionUrl]);
        }
        if ($action === 'finish' || $action === 'recover') {
            $data = $request->validate([
                'ticket' => ['required', 'uuid'],
                'drive_id' => ['nullable', 'string', 'max:255'],
            ]);
            $cacheKey = 'rnd_direct_upload:' . $data['ticket'];
            $session = Cache::get($cacheKey);
            abort_unless($session && (int) $session['user_id'] === (int) auth()->id()
                && (int) $session['po_id'] === (int) $po->id
                && (int) $session['detail_po_id'] === (int) $detailPo->id, 403,
                'Sesi upload tidak ditemukan atau sudah kedaluwarsa.');
            // Lock prevents duplicate DB records from repeated finish requests.
            return Cache::lock('rnd_direct_finish:' . $data['ticket'], 20)->block(10,
                function () use ($drive, $data, $session, $cacheKey) {
                    // Recovery: Google dapat menyimpan file walaupun browser kehilangan respons akibat CORS.
                    // Cari HANYA file dengan ticket khusus sesi ini, bukan sekadar nama yang bisa sama.
                    $driveId = $data['drive_id'] ?? null;
                    if (!$driveId) {
                        $found = $drive->findBrowserUploadByTicket($data['ticket'], $session['drive_folder_id']);
                        if (!$found) {
                            return response()->json(['success' => false, 'pending' => true,
                                'message' => 'Foto belum terkonfirmasi di Google Drive.'], 202);
                        }
                        $driveId = $found['id'];
                    }
                    $existing = RndArchiveFile::query()->where('google_drive_file_id', $driveId)
                        ->where('folder_id', $session['folder_id'])->first();
                    if ($existing) {
                        Cache::forget($cacheKey);
                        return response()->json(['success' => true, 'uploaded' => [[
                            'id' => $existing->id, 'drive_id' => $existing->google_drive_file_id,
                            'name' => $existing->original_name,
                        ]]]);
                    }
                    $verified = $drive->verifyBrowserUpload($driveId,
                        $session['drive_folder_id'], $session['drive_name'], $session['size'], $data['ticket']);
                    abort_unless($verified['mime_type'] === $session['mime_type'], 422,
                        'Tipe file tidak sesuai sesi upload.');
                    $record = RndArchiveFile::create([
                        'folder_id' => $session['folder_id'],
                        'original_name' => $session['original_name'],
                        'google_drive_file_id' => $verified['id'],
                        'mime_type' => $verified['mime_type'],
                        'file_size' => $verified['size'],
                        'uploaded_by' => auth()->id(),
                    ]);
                    Cache::forget($cacheKey);
                    return response()->json(['success' => true, 'uploaded' => [[
                        'id' => $record->id, 'drive_id' => $record->google_drive_file_id,
                        'name' => $record->original_name,
                    ]]]);
                });
        }
        abort(422, 'Aksi direct upload tidak dikenal.');
    }

    public function uploadArticlePhotos(

        Request $request,

        Po $po,

        DetailPo $detailPo,

        GoogleDriveService $drive

    ) {

        if ($request->filled('direct_action')) {
            return $this->handleDirectPhotoUpload($request, $po, $detailPo, $drive);
        }

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

            $folder = $this->resolveUploadFolderFast(

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

                            'File gagal diterima server (upload error: ' . $photo->getError() . ').'

                        );

                    }

                    $mimeType = $photo->getMimeType() ?: 'application/octet-stream';

                    $extension = strtolower(

                        $photo->guessExtension()

                        ?: $photo->getClientOriginalExtension()

                        ?: 'jpg'

                    );

                    $driveFileName = basename(str_replace('\\', '/', $originalName));
                    $driveFileName = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $driveFileName));
                    if ($driveFileName === '' || in_array($driveFileName, ['.', '..'], true)) {
                        throw new \RuntimeException('Nama foto tidak valid.');
                    }

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

                        'drive_id' => $savedFile->google_drive_file_id,

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

            Cache::forget('rnd_drive_folder_items:' . $folder->google_drive_folder_id);

            $count = RndArchiveFile::where('folder_id', $folder->id)->count();

            $success = count($uploaded) > 0 && count($failed) === 0;

            return response()->json([

                'success' => $success,

                'partial' => count($uploaded) > 0 && count($failed) > 0,

                'message' => count($uploaded) . ' foto berhasil, ' . count($failed) . ' gagal.',

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

            // Selalu baca kondisi Drive terbaru agar Refresh tidak menampilkan cache kosong.

            $driveItems = $drive->listFolderContents($activeFolder->google_drive_folder_id);

            $this->syncDriveFilesToDatabase($activeFolder, $driveItems);

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

                    'url' => 'https\://drive.google.com/drive/folders/'

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

                        ?? 'https\://drive.google.com/drive/folders/'

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

    /** Move item within a single article; never allow moving the article root. */

    public function moveArchiveItem(Request $request, Po $po, DetailPo $detailPo, string $folder, GoogleDriveService $drive)

    {

        $data = $request->validate(['parent_folder_id' => ['required', 'integer'], 'target_folder_id' => ['required', 'integer']]);

        try {

            $source = $this->resolveArticleArchiveFolder($po, $detailPo, $data['parent_folder_id'], $drive);

            $target = $this->resolveArticleArchiveFolder($po, $detailPo, $data['target_folder_id'], $drive);

            $item = $this->assertDriveItemInsideFolder($drive, $source, $folder);

            if ($source->id === $target->id)

                return response()->json(['success' => false, 'message' => 'Item sudah berada di folder tujuan.'], 422);

            $folderRow = null;

            if ($item['type'] === 'folder') {

                $folderRow = RndArchiveFolder::where('google_drive_folder_id', $folder)->where('parent_id', $source->id)->firstOrFail();

                if ($folderRow->folder_type !== 'subfolder')

                    abort(403, 'Folder utama tidak boleh dipindahkan.');

                $cursor = $target;

                $visited = [];

                while ($cursor) {

                    if (isset($visited[$cursor->id]) || $cursor->id === $folderRow->id)

                        abort(422, 'Tidak dapat memindahkan folder ke dirinya atau turunannya.');

                    $visited[$cursor->id] = true;

                    $cursor = $cursor->parent_id ? RndArchiveFolder::whereKey($cursor->parent_id)->first() : null;

                }

            }

            $drive->moveArchiveItem($folder, $source->google_drive_folder_id, $target->google_drive_folder_id);

            try {

                if ($folderRow) {

                    $folderRow->parent_id = $target->id;

                    $folderRow->save();

                } else

                    RndArchiveFile::where('google_drive_file_id', $folder)->where('folder_id', $source->id)->update(['folder_id' => $target->id]);

            } catch (Throwable $e) {

                try {

                    $drive->moveArchiveItem($folder, $target->google_drive_folder_id, $source->google_drive_folder_id);

                } catch (Throwable $rollback) {

                    Log::critical('RND move rollback gagal', ['id' => $folder, 'error' => $rollback->getMessage()]);

                }

                throw $e;

            }

            Cache::forget('rnd_drive_folder_items:' . $source->google_drive_folder_id);

            Cache::forget('rnd_drive_folder_items:' . $target->google_drive_folder_id);

            return response()->json(['success' => true, 'message' => 'Item berhasil dipindahkan.']);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {

            throw $e;

        } catch (Throwable $e) {

            Log::error('RND move gagal', ['id' => $folder, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => config('app.debug') ? $e->getMessage() : 'Gagal memindahkan item.'], 500);

        }

    }

    /** Copy file and persist local metadata. Folder-copy intentionally rejected. */

    public function copyArchiveItem(Request $request, Po $po, DetailPo $detailPo, string $folder, GoogleDriveService $drive)

    {

        $data = $request->validate(['parent_folder_id' => ['required', 'integer'], 'target_folder_id' => ['required', 'integer']]);

        try {

            $source = $this->resolveArticleArchiveFolder($po, $detailPo, $data['parent_folder_id'], $drive);

            $target = $this->resolveArticleArchiveFolder($po, $detailPo, $data['target_folder_id'], $drive);

            $item = $this->assertDriveItemInsideFolder($drive, $source, $folder);

            if ($item['type'] !== 'file')

                return response()->json(['success' => false, 'message' => 'Copy folder belum didukung. Gunakan Move untuk folder.'], 422);

            $dbFile = RndArchiveFile::where('google_drive_file_id', $folder)->where('folder_id', $source->id)->firstOrFail();

            $original = $drive->getArchiveItem($folder);

            if (!in_array($source->google_drive_folder_id, $original['parents'], true) || $original['trashed'])

                abort(409, 'File telah berubah. Refresh folder.');

            $copied = $drive->copyArchiveItem($folder, $target->google_drive_folder_id, $dbFile->original_name);

            try {

                RndArchiveFile::create([

                    'folder_id' => $target->id,

                    'original_name' => $dbFile->original_name,

                    'google_drive_file_id' => $copied['id'],

                    'mime_type' => $copied['mime_type'] ?: $dbFile->mime_type,

                    'file_size' => (int) ($copied['size'] ?: $dbFile->file_size),

                    'uploaded_by' => auth()->id()

                ]);

            } catch (Throwable $e) {

                try {

                    $drive->trashItem($copied['id']);

                } catch (Throwable $rollback) {

                    Log::critical('RND copy rollback gagal', ['id' => $copied['id']]);

                }

                throw $e;

            }

            Cache::forget('rnd_drive_folder_items:' . $target->google_drive_folder_id);

            return response()->json(['success' => true, 'message' => 'File berhasil disalin.', 'item' => $copied]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {

            throw $e;

        } catch (Throwable $e) {

            Log::error('RND copy gagal', ['id' => $folder, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => config('app.debug') ? $e->getMessage() : 'Gagal menyalin file.'], 500);

        }

    }

    /** ZIP for selected Drive photos or a whole subfolder, including nested folders. */
    public function downloadArchiveZip(Request $request, Po $po, DetailPo $detailPo, GoogleDriveService $drive)
    {
        $validated = $request->validate([
            'folder_id' => ['required', 'integer'],
            'mode' => ['required', 'in:selected,folder'],
            'file_ids' => ['nullable', 'array', 'max:200'],
            'file_ids.*' => ['string', 'max:200'],
            'target_folder_id' => ['nullable', 'integer'],
        ]);
        abort_unless(class_exists(\ZipArchive::class), 503, 'Ekstensi PHP ZipArchive belum aktif.');
        $active = $this->resolveArticleArchiveFolder($po, $detailPo, $validated['folder_id'], $drive);
        $articleInfo = $this->getArticleInfo($detailPo);
        $description = trim($articleInfo['description']) ?: 'Article-' . $detailPo->id;
        $safeName = static function (string $value): string {
            $value = preg_replace('/[\x00-\x1F\x7F\/\\\\:*?"<>|]+/u', '-', $value);
            $value = trim(preg_replace('/\s+/u', ' ', $value), ' .-');
            return mb_substr($value ?: 'Tanpa Nama', 0, 130);
        };
        $selected = [];
        $rootName = $description;
        if ($validated['mode'] === 'folder') {
            $targetId = $validated['target_folder_id'] ?? null;
            abort_unless($targetId, 422, 'Folder yang akan diunduh belum dipilih.');
            $target = RndArchiveFolder::query()
                ->whereKey($targetId)->where('parent_id', $active->id)
                ->where('po_id', $po->id)->where('detail_po_id', $detailPo->id)->firstOrFail();
            $rootName = $target->folder_name . ' - ' . $description;
            $selected[] = ['type' => 'folder', 'id' => $target->google_drive_folder_id, 'name' => $target->folder_name];
        } else {
            $ids = array_values(array_unique($validated['file_ids'] ?? []));
            abort_unless(count($ids) > 0, 422, 'Pilih minimal satu foto.');
            // Validate against the current Drive parent, not client-supplied names.
            $available = collect($drive->listFolderContents($active->google_drive_folder_id))
                ->filter(fn ($x) => ($x['type'] ?? '') === 'file')->keyBy('id');
            foreach ($ids as $id) {
                abort_unless($available->has($id), 403, 'Ada foto yang tidak berada di folder aktif.');
                $selected[] = $available->get($id);
            }
        }
        $workDir = storage_path('app/rnd-zip/' . (string) \Illuminate\Support\Str::uuid());
        if (!is_dir($workDir) && !mkdir($workDir, 0700, true) && !is_dir($workDir)) {
            abort(500, 'Gagal membuat direktori ZIP sementara.');
        }
        $zipPath = $workDir . '/archive.zip';
        $zip = new \ZipArchive();
        $count = 0;
        $bytes = 0;
        $used = [];
        $addUnique = static function (string $name) use (&$used): string {
            $base = $name; $n = 2;
            while (isset($used[mb_strtolower($name)])) {
                $ext = pathinfo($base, PATHINFO_EXTENSION);
                $stem = $ext ? mb_substr($base, 0, -(mb_strlen($ext) + 1)) : $base;
                $name = $stem . ' (' . $n++ . ')' . ($ext ? '.' . $ext : '');
            }
            $used[mb_strtolower($name)] = true;
            return $name;
        };
        $walk = function (array $item, string $prefix, int $depth) use (&$walk, $drive, $zip, $workDir, &$count, &$bytes, $safeName, $addUnique): void {
            if ($depth > 15) throw new \RuntimeException('Folder terlalu dalam untuk di-ZIP.');
            $name = $safeName((string) ($item['name'] ?? 'File'));
            if (($item['type'] ?? '') === 'folder') {
                $path = $prefix . $name . '/';
                $zip->addEmptyDir($path);
                foreach ($drive->listFolderContents((string) $item['id']) as $child) {
                    $walk($child, $path, $depth + 1);
                }
                return;
            }
            if (++$count > 200) throw new \RuntimeException('ZIP dibatasi maksimal 200 file.');
            $bytes += (int) ($item['size'] ?? 0);
            if ($bytes > 1024 * 1024 * 1024) throw new \RuntimeException('ZIP dibatasi total 1 GB.');
            $local = $workDir . '/file-' . $count;
            $drive->downloadFileToPath((string) $item['id'], $local);
            $actualSize = filesize($local);
            $bytes += max(0, (int) $actualSize - (int) ($item['size'] ?? 0));
            if ($bytes > 1024 * 1024 * 1024) throw new \RuntimeException('ZIP dibatasi total 1 GB.');
            $entry = $addUnique($prefix . $name);
            if (!$zip->addFile($local, $entry)) throw new \RuntimeException('Gagal menambahkan file ke ZIP.');
        };
        try {
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Gagal membuat ZIP.');
            }
            try {
                foreach ($selected as $item) $walk($item, '', 0);
            } finally {
                $zip->close();
            }
            // All temporary source files are deleted after ZIP is finalized.
            foreach (glob($workDir . '/file-*') ?: [] as $temporary) @unlink($temporary);
            return response()->download($zipPath, $safeName($rootName) . '.zip', [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-store',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            foreach (glob($workDir . '/*') ?: [] as $temporary) @unlink($temporary);
            @rmdir($workDir);
            \Illuminate\Support\Facades\Log::error('RND ZIP gagal', ['detail_po_id' => $detailPo->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['zip' => $e->getMessage()]);
        }
    }


    /** List a bounded folder tree for browser-side ZIP generation. */
    public function browserZipManifest(Request $request, Po $po, DetailPo $detailPo, GoogleDriveService $drive)
    {
        $data = $request->validate([
            'folder_id' => ['required','integer'],
            'mode' => ['required','in:selected,folder'],
            'file_ids' => ['array','max:200'],
            'file_ids.*' => ['string','max:200'],
            'target_folder_id' => ['nullable','integer'],
        ]);
        $active = $this->resolveArticleArchiveFolder($po, $detailPo, $data['folder_id'], $drive);
        $description = trim((string) ($this->getArticleInfo($detailPo)['description'] ?? '')) ?: 'Article-' . $detailPo->id;
        $clean = static fn (string $v) => mb_substr(trim(preg_replace('/[\\x00-\\x1F\\x7F\\\\\/:*?"<>|]+/u', '-', $v), ' .-') ?: 'File', 0, 120);
        $items = [];
        $totalBytes = 0;
        $walk = function (array $item, string $prefix, int $depth) use (&$walk, &$items, &$totalBytes, $drive, $clean) {
            if ($depth > 15) abort(422, 'Subfolder terlalu dalam.');
            $name = $clean((string) ($item['name'] ?? 'File'));
            $path = $prefix . $name;
            if (($item['type'] ?? '') === 'folder') {
                $items[] = ['type' => 'folder', 'path' => $path . '/'];
                foreach ($drive->listFolderContents((string) $item['id']) as $child) $walk($child, $path . '/', $depth + 1);
            } else {
                $totalBytes += (int) ($item['size'] ?? 0);
                if ($totalBytes > 1024 * 1024 * 1024) abort(422, 'Maksimal 1 GB untuk ZIP browser. Untuk file besar gunakan laptop dengan RAM cukup atau unduh per folder.');
                $items[] = ['type' => 'file', 'id' => (string) $item['id'], 'path' => $path, 'size' => (int) ($item['size'] ?? 0)];
            }
            if (count($items) > 250) abort(422, 'Maksimal 250 item per ZIP.');
        };
        if ($data['mode'] === 'folder') {
            $target = RndArchiveFolder::query()->whereKey($data['target_folder_id'] ?? 0)
                ->where('parent_id', $active->id)->where('po_id', $po->id)->where('detail_po_id', $detailPo->id)->firstOrFail();
            $rootName = $target->folder_name . ' - ' . $description;
            // Root folder contents appear directly in the downloaded ZIP.
            foreach ($drive->listFolderContents($target->google_drive_folder_id) as $item) $walk($item, '', 0);
        } else {
            $rootName = $description;
            $available = collect($drive->listFolderContents($active->google_drive_folder_id))->keyBy('id');
            $ids = array_unique($data['file_ids'] ?? []);
            abort_unless(count($ids) > 0, 422, 'Pilih file atau folder.');
            foreach ($ids as $id) {
                abort_unless($available->has($id), 403, 'Item bukan anggota folder aktif.');
                $walk($available->get($id), '', 0);
            }
        }
        // Each manifest item carries an encrypted, short-lived authorization ticket.
        // The manifest already validated folder membership; don't traverse Drive parents again for each image.
        foreach ($items as &$entry) {
            if ($entry['type'] !== 'file') continue;
            $entry['ticket'] = \Illuminate\Support\Facades\Crypt::encryptString(json_encode([
                'id' => $entry['id'], 'po' => (int) $po->id,
                'article' => (int) $detailPo->id, 'folder' => (int) $active->id,
                'expires' => time() + 1800,
            ]));
        }
        unset($entry);
        return response()->json(['name' => $clean($rootName) . '.zip', 'items' => $items, 'total_bytes' => $totalBytes]);
    }

    /** Download a manifest-authorized binary without repeated Drive parent lookups. */
    public function browserZipFile(Request $request, Po $po, DetailPo $detailPo, GoogleDriveService $drive)
    {
        $data = $request->validate([
            'folder_id' => ['required', 'integer'],
            'ticket' => ['required', 'string', 'max:4096'],
        ]);
        abort_unless((int) $detailPo->po_id === (int) $po->id, 404);
        $active = RndArchiveFolder::query()->whereKey($data['folder_id'])
            ->where('po_id', $po->id)->where('detail_po_id', $detailPo->id)->firstOrFail();
        try {
            $ticket = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($data['ticket']), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            abort(403, 'Tiket unduhan tidak valid. Silakan ulangi Download ZIP.');
        }
        abort_unless(
            is_array($ticket)
            && (int) ($ticket['po'] ?? 0) === (int) $po->id
            && (int) ($ticket['article'] ?? 0) === (int) $detailPo->id
            && (int) ($ticket['folder'] ?? 0) === (int) $active->id
            && (int) ($ticket['expires'] ?? 0) >= time()
            && preg_match('/^[a-zA-Z0-9_-]{10,200}$/', (string) ($ticket['id'] ?? '')),
            403, 'Tiket unduhan kedaluwarsa atau tidak sesuai artikel.'
        );
        // Start the Drive request BEFORE returning headers, so API errors remain JSON.
        // Stream bytes directly to browser: no second copy in storage/app.
        try {
            $upstream = $drive->openDownloadStream((string) $ticket['id']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('RND browser ZIP upstream failed', [
                'article' => $detailPo->id, 'file' => $ticket['id'], 'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal mengambil file dari Google Drive. Silakan coba lagi.'], 502);
        }
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="file.bin"',
            'Cache-Control' => 'private, no-store, no-transform',
            'X-Accel-Buffering' => 'no',
        ];
        $length = $upstream->getHeaderLine('Content-Length');
        if ($length !== '' && ctype_digit($length)) $headers['Content-Length'] = $length;
        return response()->stream(function () use ($upstream, $detailPo) {
            $body = $upstream->getBody();
            try {
                while (!$body->eof()) {
                    $chunk = $body->read(65536);
                    if ($chunk === '') {
                        if ($body->eof()) break;
                        continue;
                    }
                    echo $chunk;
                    if (ob_get_level() > 0) @ob_flush();
                    flush();
                    if (connection_aborted()) break;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('RND browser ZIP stream interrupted', [
                    'article' => $detailPo->id, 'error' => $e->getMessage(),
                ]);
            } finally {
                $body->close();
            }
        }, 200, $headers);
    }

    

}
