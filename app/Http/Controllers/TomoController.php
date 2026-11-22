<?php

namespace App\Http\Controllers;

use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TomoController extends Controller
{
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
    public function points()
    {
        return response()->json(DB::table('tomo_points')
            ->where('is_active', 1)->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'name', 'sort_order']));
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
            'rows.*.harga' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'rows.*.qty' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);
        $ids = array_map('intval', array_column($data['rows'], 'point_id'));
        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['rows' => 'Point tidak boleh duplikat.']);
        }
        $points = DB::table('tomo_points')->where('is_active', 1)
            ->whereIn('id', $ids)->get(['id', 'name', 'sort_order'])->keyBy('id');
        if ($points->count() !== count($ids)) {
            throw ValidationException::withMessages(['rows' => 'Master point berubah. Refresh halaman sebelum mengajukan.']);
        }
        $po = Po::query()->where('order_no', $data['po_code'])->first();
        $detail = $po?->detailPos()->whereKey($data['detail_po_id'])->first();
        if (!$detail) {
            throw ValidationException::withMessages(['detail_po_id' => 'Item PO tidak ditemukan. Refresh halaman.']);
        }
        $item = is_array($detail->detail) ? $detail->detail : json_decode((string) $detail->detail, true);
        $item = is_array($item) ? $item : [];
        $article = (string) ($item['article_nr_'] ?? $item['article_nr'] ?? $item['article_code'] ?? $item['article'] ?? $item['code'] ?? '-');
        $name = (string) ($item['description'] ?? $item['nama'] ?? $item['item'] ?? '-');
        $qty = (float) ($item['qty'] ?? 0);
        // Nama point di-snapshot dari server, bukan dipercayakan pada browser.
        $snapshot = [];
        foreach ($data['rows'] as $row) {
            $point = $points->get((int) $row['point_id']);
            $harga = (float) $row['harga'];
            $jumlahQty = (float) $row['qty'];
            $snapshot[] = [
                'point_id' => (int) $point->id,
                'point_name' => $point->name,
                'satuan' => trim((string) $row['satuan']),
                'process' => $point->name, // kompatibel dengan tampilan riwayat lama
                'harga' => $harga,
                'qty' => $jumlahQty,
                'jumlah' => round($harga * $jumlahQty, 2),
            ];
        }
        $total = round(array_sum(array_column($snapshot, 'jumlah')), 2);
        if ($total <= 0) {
            throw ValidationException::withMessages(['rows' => 'Isi minimal satu biaya finishing.']);
        }
        $id = DB::transaction(function () use ($data, $po, $name, $article, $qty, $total, $snapshot) {
            $po->detailPos()->whereKey($data['detail_po_id'])->lockForUpdate()->first();
            $exists = DB::table('tomo_submissions')->where('detail_po_id', $data['detail_po_id'])
                ->whereIn('status', ['pending', 'diproses', 'disetujui'])->exists();
            if ($exists) {
                throw ValidationException::withMessages(['item' => 'Barang ini sudah memiliki pengajuan aktif.']);
            }
            return DB::table('tomo_submissions')->insertGetId([
                'po_code' => $po->order_no,
                'detail_po_id' => $data['detail_po_id'],
                'article_code' => $article,
                'item_name' => $name,
                'qty' => $qty,
                'image' => $data['image'] ?? null,
                'notes' => $data['notes'] ?? null,
                'points_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'total' => $total,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
        return response()->json(['message' => 'Pengajuan berhasil dikirim', 'id' => $id, 'status' => 'pending'], 201);
    }
}
