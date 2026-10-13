<?php

namespace App\Http\Controllers;

use App\Models\Po;
use App\Models\DetailPo;
use App\Models\RndArchiveFolder;
use App\Models\RndArchiveFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        try {
            $folder = $this->ensureArticleFolder(
                $po,
                $detailPo,
                $drive
            );

            $uploaded = [];

            foreach ($request->file('photos', []) as $photo) {
                if (!$photo->isValid()) {
                    throw new \RuntimeException(
                        'Ada file foto yang gagal diunggah ke server.'
                    );
                }

                $originalName = $photo->getClientOriginalName();
                $mimeType = $photo->getMimeType()
                    ?: 'application/octet-stream';

                // Nama unik di Drive, tetapi nama asli tetap disimpan di database.
                $extension = strtolower(
                    $photo->guessExtension()
                        ?: $photo->getClientOriginalExtension()
                        ?: 'jpg'
                );

                $driveFileName = Str::uuid() . '.' . $extension;

                $driveFile = $drive->uploadFile(
                    $photo->getRealPath(),
                    $driveFileName,
                    $folder->google_drive_folder_id,
                    $mimeType
                );

                $savedFile = RndArchiveFile::create([
                    'folder_id' => $folder->id,
                    'original_name' => $originalName,
                    'google_drive_file_id' => $driveFile['id'],
                    'mime_type' => $driveFile['mime_type'] ?? $mimeType,
                    'file_size' => (int) (
                        $driveFile['size'] ?? $photo->getSize()
                    ),
                    'uploaded_by' => auth()->id(),
                ]);

                $uploaded[] = [
                    'id' => $savedFile->id,
                    'name' => $savedFile->original_name,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' foto berhasil diunggah.',
                'uploaded' => $uploaded,
                'file_count' => RndArchiveFile::where(
                    'folder_id',
                    $folder->id
                )->count(),
            ]);
        } catch (Throwable $e) {
            Log::error('RND uploadArticlePhotos gagal', [
                'po_id' => $po->id,
                'detail_po_id' => $detailPo->id,
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
}
