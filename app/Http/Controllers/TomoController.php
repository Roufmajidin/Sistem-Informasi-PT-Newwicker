<?php

namespace App\Http\Controllers;

use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TomoController extends Controller
{
    public function masterPoints()
    {
        return response()->json(DB::table('tomo_points')
            ->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'name', 'harga_mutlak', 'satuan', 'sort_order', 'is_active']));
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        // Normalisasi Article Code agar beda huruf besar/kecil
        // dan spasi tidak mengganggu pencocokan.
        $normalizeArticle = static function ($value): string {
            return mb_strtoupper(
                preg_replace('/\s+/u', '', trim((string) $value))
            );
        };

        $getArticle = static function (array $data): string {
            return (string) (
                $data['article_nr_']
                ?? $data['article_nr']
                ?? $data['article_code']
                ?? $data['article']
                ?? $data['code']
                ?? ''
            );
        };

        // ==================================================
        // 1. AMBIL RIWAYAT SPK TOMO DARI SEMUA PO
        // ==================================================
        // Nama tabel 'spk' mengikuti screenshot database.
        $historicalSpks = DB::table('spk')
            ->where('data', 'like', '%TOMO FINISHING%')
            ->orderByDesc('id')
            ->get(['id', 'po_id', 'data']);

        // ==================================================
        // 2. AMBIL DETAIL PO HISTORIS
        // Untuk menemukan Article Code asli berdasarkan
        // detail_po_id pada setiap SPK.
        // ==================================================
        $historicalPoIds = $historicalSpks
            ->pluck('po_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $articleByDetailId = [];

        if (!empty($historicalPoIds)) {
            Po::query()
                ->with('detailPos')
                ->whereIn('id', $historicalPoIds)
                ->get()
                ->each(function ($po) use (&$articleByDetailId, $getArticle, $normalizeArticle) {
                    foreach ($po->detailPos as $detailPo) {
                        $detail = is_array($detailPo->detail)
                            ? $detailPo->detail
                            : json_decode((string) $detailPo->detail, true);

                        if (!is_array($detail)) {
                            continue;
                        }

                        $article = $normalizeArticle(
                            $getArticle($detail)
                        );

                        if ($article !== '') {
                            $articleByDetailId[(string) $detailPo->id]
                                = $article;
                        }
                    }
                });
        }

        // ==================================================
        // 3. INDEX HARGA BERDASARKAN ARTICLE CODE
        // ==================================================
        $pricesByArticle = [];

        foreach ($historicalSpks as $spk) {
            $data = is_array($spk->data)
                ? $spk->data
                : json_decode((string) $spk->data, true);

            if (!is_array($data)) {
                continue;
            }

            $supplier = mb_strtoupper(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    trim((string) ($data['sup'] ?? ''))
                )
            );

            if ($supplier !== 'TOMO FINISHING') {
                continue;
            }

            $spkNumber = $data['no_spk']
                ?? ('SPK #' . $spk->id);

            foreach (($data['items'] ?? []) as $row) {
                if (!is_array($row)) {
                    continue;
                }

                if (
                    !isset($row['harga'])
                    || !is_numeric($row['harga'])
                ) {
                    continue;
                }

                $detailId = (string) (
                    $row['detail_po_id'] ?? ''
                );

                // Prioritas: Article Code dari detail PO asli.
                // Fallback: kode pada JSON SPK.
                $article = $articleByDetailId[$detailId]
                    ?? $normalizeArticle($row['kode'] ?? '');

                if ($article === '') {
                    continue;
                }

                $pricesByArticle[$article][] = [
                    'number' => $spkNumber,
                    'qty' => $row['qty'] ?? null,
                    'harga' => (float) $row['harga'],
                    'source_detail_po_id' => $detailId,
                ];
            }
        }

        // ==================================================
        // 4. QUERY PO EXISTING
        // ==================================================
        $pos = Po::query()
            ->with(['detailPos', 'spks'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'order_no',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'company_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'detailPos',
                            function ($detailQuery) use ($search) {
                                $detailQuery->where(
                                    'detail',
                                    'like',
                                    '%' . $search . '%'
                                );
                            }
                        );
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // ==================================================
        // 5. TAMPILKAN HARGA HISTORIS PADA SEMUA REPEAT PO
        // ==================================================
        $cards = $pos->getCollection()
            ->map(function ($po) use ($pricesByArticle, $getArticle, $normalizeArticle) {
                return [
                    'po' => $po->order_no,
                    'buyer' => $po->company_name ?: '-',

                    'items' => $po->detailPos
                        ->map(function ($detailPo) use ($pricesByArticle, $getArticle, $normalizeArticle) {
                            $data = is_array($detailPo->detail)
                                ? $detailPo->detail
                                : json_decode(
                                    (string) $detailPo->detail,
                                    true
                                );

                            $data = is_array($data) ? $data : [];

                            $article = $getArticle($data);
                            $articleKey = $normalizeArticle($article);

                            $photo = $data['photo'] ?? null;

                            if (is_array($photo)) {
                                $photo = $photo['url']
                                    ?? $photo['path']
                                    ?? null;
                            }

                            if (is_string($photo) && $photo !== '') {
                                if (
                                    !preg_match(
                                        '~^(https?://|/)~i',
                                        $photo
                                    )
                                ) {
                                    $photo = asset(
                                        'storage/' . ltrim($photo, '/')
                                    );
                                }
                            } else {
                                $photo = null;
                            }

                            return [
                                'id' => $detailPo->id,

                                'name' => $data['description']
                                    ?? $data['nama']
                                    ?? $data['item']
                                    ?? '-',

                                'article' => $article ?: '-',

                                'qty' => $data['qty'] ?? 0,

                                'image' => $photo,

                                // Riwayat dari SEMUA PO,
                                // bukan hanya detail PO saat ini.
                                'spks' => $articleKey !== ''
                                    ? ($pricesByArticle[$articleKey] ?? [])
                                    : [],
                            ];
                        })
                        ->values()
                        ->all(),
                ];
            })
            ->values();

        return view(
            'pages.tomo.indx',
            compact('cards', 'pos', 'search')
        );
    }
    // Publik: hanya point aktif. Pengelolaan master point dilakukan admin di database.
    public function pointUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'harga_mutlak' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'satuan' => ['required', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $exists = DB::table('tomo_points')->where('id', $id)->exists();
        if (!$exists) {
            return response()->json(['message' => 'Master point tidak ditemukan.'], 404);
        }

        $changes = [
            'name' => trim($validated['name']),
            'harga_mutlak' => $validated['harga_mutlak'],
            'satuan' => trim($validated['satuan']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'updated_at' => now(),
        ];
        if ($request->exists('is_active')) {
            $changes['is_active'] = $request->boolean('is_active') ? 1 : 0;
        }
        DB::table('tomo_points')->where('id', $id)->update($changes);

        return response()->json([
            'message' => 'Master point berhasil diperbarui.',
            'point' => DB::table('tomo_points')->where('id', $id)->first(),
        ]);
    }

    public function points()
    {
        $points = DB::table('tomo_points')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'name',
                'harga_mutlak',
                'satuan',
                'sort_order',
            ]);

        return response()->json($points);
    }


    public function submissions(Request $request)
    {
        $query = DB::table('tomo_submissions')->orderByDesc('id');
        if ($request->filled('search')) {
            $term = trim((string) $request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('po_code', 'like', "%{$term}%")
                    ->orWhere('item_name', 'like', "%{$term}%")
                    ->orWhere('article_code', 'like', "%{$term}%");
            });
        }
        return response()->json($query->limit(1000)->get()->map(function ($s) {
            $s->rows = json_decode($s->points_json ?: '[]', true) ?: [];
            unset($s->points_json);
            return $s;
        }));
    }



    public function store(Request $request)
    {
        // ==========================================
        // 1. VALIDASI INPUT
        // ==========================================

        $data = $request->validate([
            'po_code' => ['required', 'string', 'max:150'],
            'detail_po_id' => ['required', 'integer', 'min:1'],
            'article_code' => ['nullable', 'string', 'max:150'],
            'item_name' => ['required', 'string', 'max:500'],
            'qty' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:3000'],

            'rows' => ['required', 'array', 'min:1', 'max:100'],
            'rows.*.point_id' => ['required', 'integer', 'min:1'],
            'rows.*.satuan' => ['required', 'string', 'max:30'],
            'rows.*.harga' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999'
            ],
            'rows.*.qty' => [
                'required',
                'numeric',
                'min:0',
                'max:999999'
            ],
        ]);

        // ==========================================
        // 2. VALIDASI MASTER POINTS
        // ==========================================

        $ids = array_map(
            'intval',
            array_column($data['rows'], 'point_id')
        );

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'rows' => 'Point finishing tidak boleh duplikat.',
            ]);
        }

        $points = DB::table('tomo_points')
            ->where('is_active', 1)
            ->whereIn('id', $ids)
            ->get([
                'id',
                'name',
                'sort_order',
                'harga_mutlak',
                'satuan',
            ])
            ->keyBy('id');

        if ($points->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'rows' => 'Master point berubah. Refresh halaman.',
            ]);
        }

        // ==========================================
        // 3. CARI PO BERDASARKAN NOMOR PO
        // Contoh: NW 26 - 79 / NWS 26 - 73
        // ==========================================

        $poCode = trim($data['po_code']);

        $po = Po::query()
            ->where('order_no', $poCode)
            ->first();

        if (!$po) {
            throw ValidationException::withMessages([
                'po_code' =>
                    'Nomor PO tidak ditemukan: ' . $poCode,
            ]);
        }

        // ==========================================
        // 4. VALIDASI DETAIL ITEM PO
        // ==========================================

        $detail = $po->detailPos()
            ->whereKey($data['detail_po_id'])
            ->first();

        if (!$detail) {
            throw ValidationException::withMessages([
                'detail_po_id' =>
                    'Item tidak ditemukan pada PO ' . $poCode,
            ]);
        }

        $item = is_array($detail->detail)
            ? $detail->detail
            : json_decode((string) $detail->detail, true);

        $item = is_array($item) ? $item : [];

        $article = (string) (
            $item['article_nr_']
            ?? $item['article_nr']
            ?? $item['article_code']
            ?? $item['article']
            ?? $item['code']
            ?? '-'
        );

        $name = (string) (
            $item['description']
            ?? $item['nama']
            ?? $item['item']
            ?? '-'
        );

        $qty = (float) ($item['qty'] ?? 0);

        // ==========================================
        // 5. HITUNG TOTAL FINISHING
        // ==========================================

        $snapshot = [];

        foreach ($data['rows'] as $row) {
            $point = $points->get(
                (int) $row['point_id']
            );

            // Harga dan satuan dari master point
            $harga = (float) $point->harga_mutlak;
            $satuan = trim((string) $point->satuan);
            $jumlahQty = (float) $row['qty'];

            $snapshot[] = [
                'point_id' => (int) $point->id,
                'point_name' => $point->name,
                'satuan' => $satuan,
                'process' => $point->name,
                'harga' => $harga,
                'qty' => $jumlahQty,
                'jumlah' => round(
                    $harga * $jumlahQty,
                    2
                ),
            ];
        }

        $total = round(
            array_sum(array_column($snapshot, 'jumlah')),
            2
        );

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'rows' =>
                    'Total biaya finishing harus lebih dari 0.',
            ]);
        }

        // ==========================================
        // 6. SIMPAN KE DUA TABEL
        // ==========================================

        $result = DB::transaction(function () use ($data, $po, $detail, $article, $name, $qty, $total, $snapshot) {
            // Lock item PO untuk mencegah submit bersamaan
            $lockedDetail = $po->detailPos()
                ->whereKey($detail->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedDetail) {
                throw ValidationException::withMessages([
                    'detail_po_id' =>
                        'Item PO tidak ditemukan.',
                ]);
            }

            // ======================================
            // CEK DUPLIKAT TOMO SUBMISSIONS
            // ======================================

            $exists = DB::table('tomo_submissions')
                ->where('detail_po_id', $detail->id)
                ->whereIn('status', [
                    'pending',
                    'diproses',
                    'disetujui',
                ])
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'item' =>
                        'Barang ini sudah memiliki pengajuan Tomo aktif.',
                ]);
            }

            // ======================================
            // CEK DUPLIKAT PENGAJUAN HARGA TOMO
            // ======================================

            $hargaExists = DB::table(
                'pengajuan_harga_pekerjaans'
            )
                ->where('po_id', $po->id)
                ->where('detail_po_id', $detail->id)
                ->where('sub_name', 'TOMO')
                ->where('submission_by', 'TOMO')
                ->where('closed', 0)
                ->exists();

            if ($hargaExists) {
                throw ValidationException::withMessages([
                    'item' =>
                        'Pengajuan harga TOMO untuk item ini sudah ada.',
                ]);
            }

            // ======================================
            // A. SIMPAN KE TOMO_SUBMISSIONS
            // ======================================

            $submittedAt = now();

            $tomoId = DB::table('tomo_submissions')
                ->insertGetId([
                    'po_code' => $po->order_no,
                    'detail_po_id' => $detail->id,
                    'article_code' => $article,
                    'item_name' => $name,
                    'qty' => $qty,
                    'image' => $data['image'] ?? null,
                    'notes' => $data['notes'] ?? null,

                    'points_json' => json_encode(
                        $snapshot,
                        JSON_UNESCAPED_UNICODE
                        | JSON_THROW_ON_ERROR
                    ),

                    'total' => $total,
                    'status' => 'pending',

                    'created_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ]);

            // ======================================
            // B. SIMPAN KE PENGAJUAN_HARGA_PEKERJAANS
            // ======================================

            $pengajuanHargaId = DB::table(
                'pengajuan_harga_pekerjaans'
            )->insertGetId([
                        // Relasi ke pengajuan Tomo
                        'tomo_submission_id' => $tomoId,

                        // Data PO
                        'po_id' => $po->id,
                        'detail_po_id' => $detail->id,
                        'po_code' => $po->order_no,
                        'article_code' => $article,

                        // Kategori pekerjaan TOMO
                        'bahan_kategori_id' => 10,

                        // Identitas pengajuan
                        'sub_name' => 'TOMO',
                        'submission_by' => 'TOMO',

                        // Harga pengajuan sub
                        'pengajuan_sub_price' => $total,

                        // TANGGAL SUBMIT TOMO
                        'sub_date' => $submittedAt,

                        // Catatan
                        'sub_remark' => $data['notes'] ?? null,

                        // Status awal
                        'closed' => 0,

                        'created_at' => $submittedAt,
                        'updated_at' => $submittedAt,
                    ]);

            return [
                'tomo_id' => $tomoId,
                'pengajuan_harga_id' => $pengajuanHargaId,
                'submitted_at' => $submittedAt,
            ];
        }, 3);

        // ==========================================
        // 7. RESPONSE BERHASIL
        // ==========================================

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan Tomo berhasil disimpan.',

            'id' => $result['tomo_id'],
            'tomo_submission_id' => $result['tomo_id'],
            'pengajuan_harga_id' =>
                $result['pengajuan_harga_id'],

            'po_id' => $po->id,
            'po_code' => $po->order_no,
            'detail_po_id' => $detail->id,

            'bahan_kategori_id' => 10,
            'sub_name' => 'TOMO',
            'submission_by' => 'TOMO',

            'total' => $total,
            'sub_date' => $result['submitted_at']->toDateTimeString(),
            'status' => 'pending',
        ], 201);
    }


}
