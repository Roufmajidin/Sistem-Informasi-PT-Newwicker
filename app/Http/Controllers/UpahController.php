<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UpahBorongan;
use App\Models\DetailPo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Upah;
use App\Exports\ReportExport;
class UpahController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $upah = UpahBorongan::orderByDesc('id')
            ->get();

        return view(
            'pages.upah.index',
            compact('upah')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'jenis' => [
                'required',
                'string',
                'max:100',
            ],

            'harga' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        $timeline = [
            [
                'action' => 'created',

                'timestamp' =>
                    now()->toDateTimeString(),

                'user_id' =>
                    auth()->id(),

                'user_name' =>
                    auth()->user()?->name,

                'harga_lama' =>
                    null,

                'harga_baru' =>
                    $validated['harga'],
            ]
        ];


        $upah = UpahBorongan::create([

            'article' =>
                $validated['article'],

            'description' =>
                $validated['description'] ?? null,

            'jenis' =>
                $validated['jenis'],

            'harga' =>
                $validated['harga'],

            'update_remark' =>
                $timeline,

        ]);


        return response()->json([
            'success' => true,

            'message' =>
                'Upah borongan berhasil ditambahkan.',

            'data' =>
                $upah,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT DATA
    |--------------------------------------------------------------------------
    */

    public function editData(UpahBorongan $upah)
    {
        return response()->json([
            'success' => true,

            'data' => [
                'id' =>
                    $upah->id,

                'article' =>
                    $upah->article,

                'description' =>
                    $upah->description,

                'jenis' =>
                    $upah->jenis,

                'harga' =>
                    $upah->harga,

                'update_remark' =>
                    $upah->update_remark,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        UpahBorongan $upah
    ) {
        $validated = $request->validate([
            'article' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'jenis' => [
                'required',
                'string',
                'max:100',
            ],

            'harga' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        $timeline =
            $upah->update_remark ?? [];


        /*
        |--------------------------------------------------------------------------
        | CEK PERUBAHAN HARGA
        |--------------------------------------------------------------------------
        */

        if (
            (float) $upah->harga !==
            (float) $validated['harga']
        ) {

            $timeline[] = [
                'action' => 'updated',

                'timestamp' =>
                    now()->toDateTimeString(),

                'user_id' =>
                    auth()->id(),

                'user_name' =>
                    auth()->user()?->name,

                'harga_lama' =>
                    $upah->harga,

                'harga_baru' =>
                    $validated['harga'],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $upah->update([

            'article' =>
                $validated['article'],

            'description' =>
                $validated['description'] ?? null,

            'jenis' =>
                $validated['jenis'],

            'harga' =>
                $validated['harga'],

            'update_remark' =>
                $timeline,

        ]);


        return response()->json([
            'success' => true,

            'message' =>
                'Upah borongan berhasil diperbarui.',

            'data' =>
                $upah,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(UpahBorongan $upah)
    {
        try {

            $upah->delete();

            return response()->json([
                'success' => true,

                'message' =>
                    'Upah borongan berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,

                'message' =>
                    'Gagal menghapus upah borongan.',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }
public function searchArticle(Request $request)
{
    $search = trim($request->get('q', ''));

    if ($search === '') {
        return response()->json([]);
    }

    $details = DetailPo::query()
        ->whereNotNull('detail')
        ->get();

    $results = [];

    foreach ($details as $detailPo) {

        $detail = $detailPo->detail;

        if (!is_array($detail)) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | ARTICLE
        |--------------------------------------------------------------------------
        */

        $article =
            $detail['article_nr_']
            ?? $detail['article_code']
            ?? $detail['article_nr']
            ?? $detail['article']
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION / BUYER DESCRIPTION
        |--------------------------------------------------------------------------
        |
        | Mendukung beberapa format field dari JSON Detail PO
        |
        */

        $description = '';

        foreach ([
            'buyer desc',
            "buyer's_desc_",
            'buyers_desc',
            'buyer_desc',
            'description',
        ] as $key) {

            if (
                isset($detail[$key]) &&
                trim((string) $detail[$key]) !== ''
            ) {
                $description = trim(
                    (string) $detail[$key]
                );

                break;
            }
        }

        if (!$article) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH ARTICLE CODE ATAU NAMA BARANG
        |--------------------------------------------------------------------------
        */

        $matchArticle = stripos(
            (string) $article,
            $search
        ) !== false;

        $matchDescription = stripos(
            (string) $description,
            $search
        ) !== false;

        /*
        |--------------------------------------------------------------------------
        | KALAU TIDAK COCOK KEDUANYA
        |--------------------------------------------------------------------------
        */

        if (
            !$matchArticle &&
            !$matchDescription
        ) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | HINDARI DUPLICATE ARTICLE
        |--------------------------------------------------------------------------
        */

        $alreadyExists = false;

        foreach ($results as $existing) {

            if (
                (string) $existing['article'] ===
                (string) $article
            ) {
                $alreadyExists = true;
                break;
            }
        }

        if ($alreadyExists) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | RESULT
        |--------------------------------------------------------------------------
        */

        $results[] = [
            'article' => $article,
            'description' => $description,
        ];

        if (count($results) >= 20) {
            break;
        }
    }

    return response()->json($results);
}
    public function searchPoByArticle(Request $request)
    {
        $article = trim((string) $request->get('article', ''));
        $description = trim((string) $request->get('description', ''));

        if ($article === '' && $description === '') {
            return response()->json([]);
        }

        $results = [];

        /*
        |--------------------------------------------------------------------------
        | LOAD DETAIL PO
        |--------------------------------------------------------------------------
        |
        | Kita tidak hanya bergantung pada article_nr_.
        | Karena struktur detail JSON bisa berbeda-beda:
        |
        | article_code
        | article_nr
        | article_nr_
        | article
        | article_no
        | sku
        |
        */

        $details = DetailPo::with('po')
            ->whereNotNull('detail')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | HELPER NORMALIZE
        |--------------------------------------------------------------------------
        */

        $normalize = function ($value) {
            return strtolower(
                preg_replace(
                    '/\s+/',
                    ' ',
                    trim((string) $value)
                )
            );
        };

        $articleSearch = $normalize($article);
        $descriptionSearch = $normalize($description);

        /*
        |--------------------------------------------------------------------------
        | FIELD ARTICLE YANG DIDUKUNG
        |--------------------------------------------------------------------------
        */

        $articleKeys = [
            'article_code',
            'article_code_',
            'article_nr',
            'article_nr_',
            'article',
            'article_no',
            'article_number',
            'sku',
            'item_code',
            'code',
        ];

        /*
        |--------------------------------------------------------------------------
        | FUNCTION AMBIL SEMUA ARTICLE DARI DETAIL
        |--------------------------------------------------------------------------
        */

        $getArticleValues = function (array $detail) use ($articleKeys) {

            $values = [];

            foreach ($articleKeys as $key) {

                if (
                    array_key_exists($key, $detail) &&
                    $detail[$key] !== null &&
                    trim((string) $detail[$key]) !== ''
                ) {
                    $values[] = trim(
                        (string) $detail[$key]
                    );
                }
            }

            return array_values(
                array_unique($values)
            );
        };

        /*
        |--------------------------------------------------------------------------
        | PASS 1
        |--------------------------------------------------------------------------
        | PRIORITAS:
        | CARI BERDASARKAN ARTICLE / CODE
        |--------------------------------------------------------------------------
        */

        foreach ($details as $detailPo) {

            $detail = $detailPo->detail;

            if (!is_array($detail)) {
                continue;
            }

            $articleValues =
                $getArticleValues($detail);

            if (empty($articleValues)) {
                continue;
            }

            $matched = false;

            foreach ($articleValues as $value) {

                $normalizedValue =
                    $normalize($value);

                /*
                | Exact match
                */

                if (
                    $articleSearch !== '' &&
                    $normalizedValue === $articleSearch
                ) {
                    $matched = true;
                    break;
                }

                /*
                | Partial match
                | Misalnya input:
                | 2631007
                |
                | data:
                | 2631007-S
                */

                if (
                    $articleSearch !== '' &&
                    (
                        str_contains(
                            $normalizedValue,
                            $articleSearch
                        ) ||
                        str_contains(
                            $articleSearch,
                            $normalizedValue
                        )
                    )
                ) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $noPo =
                optional($detailPo->po)->order_no;

            if (!$noPo) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | HINDARI DUPLICATE PO
            |--------------------------------------------------------------------------
            */

            if (
                collect($results)->contains(
                    'no_po',
                    $noPo
                )
            ) {
                continue;
            }

            $results[] = [
                'no_po' => $noPo,
                'po_id' => $detailPo->po_id,
                'matched_by' => 'article',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | PASS 2
        |--------------------------------------------------------------------------
        | KALAU ARTICLE TIDAK MENEMUKAN PO
        |
        | CARI BERDASARKAN DESCRIPTION / NAMA BARANG
        |--------------------------------------------------------------------------
        */

        if (empty($results)) {

            /*
            | Kandidat pencarian nama.
            |
            | Prioritas:
            | 1. description dari modal
            | 2. article input apabila article ternyata
            |    sebenarnya berupa nama barang.
            */

            $nameKeywords = [];

            if ($descriptionSearch !== '') {
                $nameKeywords[] =
                    $descriptionSearch;
            }

            if ($articleSearch !== '') {
                $nameKeywords[] =
                    $articleSearch;
            }

            $nameKeywords =
                array_values(
                    array_unique(
                        array_filter(
                            $nameKeywords
                        )
                    )
                );

            foreach ($details as $detailPo) {

                $detail = $detailPo->detail;

                if (!is_array($detail)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DESCRIPTION / NAMA
                |--------------------------------------------------------------------------
                */

                $detailDescription = '';

                $descriptionKeys = [
                      'Buyer Desc.',
                    'description',
                    'Description',
                    'desc',
                    'nama',
                    'nama_barang',
                    'item_name',
                    'product_name',
                    'name',
                ];

                foreach ($descriptionKeys as $key) {

                    if (
                        array_key_exists($key, $detail) &&
                        $detail[$key] !== null &&
                        trim((string) $detail[$key]) !== ''
                    ) {
                        $detailDescription =
                            trim((string) $detail[$key]);

                        break;
                    }
                }

                if ($detailDescription === '') {
                    continue;
                }

                $normalizedDescription =
                    $normalize($detailDescription);

                $matched = false;

                foreach ($nameKeywords as $keyword) {

                    if (
                        $keyword !== '' &&
                        str_contains(
                            $normalizedDescription,
                            $keyword
                        )
                    ) {
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    continue;
                }

                $noPo =
                    optional($detailPo->po)->order_no;

                if (!$noPo) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | HINDARI DUPLICATE PO
                |--------------------------------------------------------------------------
                */

                if (
                    collect($results)->contains(
                        'no_po',
                        $noPo
                    )
                ) {
                    continue;
                }

                $results[] = [
                    'no_po' => $noPo,
                    'po_id' => $detailPo->po_id,
                    'matched_by' => 'description',
                ];
            }
        }

        return response()->json($results);
    }
    public function storeMass(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',

            'items.*.article' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.description' => [
                'nullable',
                'string',
            ],

            'items.*.jenis' => [
                'required',
                'string',
                'max:100',
            ],

            'items.*.harga' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        try {

            $created = DB::transaction(function () use ($validated) {

                $results = [];

                foreach ($validated['items'] as $item) {

                    $harga = $item['harga'];

                    $timeline = [
                        [
                            'action' => 'created',
                            'timestamp' => now()->format('Y-m-d H:i:s'),
                            'user_id' => auth()->id(),
                            'user_name' => auth()->user()?->name,

                            'harga_lama' => null,
                            'harga_baru' => $harga,
                        ]
                    ];

                    $upah = UpahBorongan::create([
                        'article' => $item['article'],
                        'description' => $item['description'] ?? null,
                        'jenis' => $item['jenis'],
                        'harga' => $harga,
                        'update_remark' => $timeline,
                    ]);

                    $results[] = $upah;
                }

                return $results;
            });

            return response()->json([
                'success' => true,
                'message' => count($created) .
                    ' data upah berhasil disimpan.',
                'data' => $created,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data mass.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function upah()
    {
        $data = Upah::orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(20);

        return view(
            'pages.upah.upah',
            compact('data')
        );
    }
    public function searchUpahArticle(Request $request)
    {
        $keyword = trim($request->get('q', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        /*
        |--------------------------------------------------------------------------
        | 1. CARI DI MASTER UPAH BORONGAN
        |--------------------------------------------------------------------------
        */

        $upahResults = UpahBorongan::query()
            ->where(function ($query) use ($keyword) {

                $query->where(
                    'article',
                    'like',
                    "%{$keyword}%"
                )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$keyword}%"
                    );

            })
            ->orderBy('article')
            ->limit(15)
            ->get([
                'id',
                'article',
                'description',
                'jenis',
                'harga',
            ]);

        $results = [];

        foreach ($upahResults as $item) {

            $results[] = [
                'id' => $item->id,

                'article' =>
                    $item->article,

                'description' =>
                    $item->description,

                'jenis' =>
                    $item->jenis,

                'harga' =>
                    $item->harga,

                /*
                |------------------------------------------
                | SUDAH ADA DI MASTER
                |------------------------------------------
                */

                'exists_in_upah' => true,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. KALAU BELUM ADA, CARI DARI DETAIL PO
        |--------------------------------------------------------------------------
        */

        if (count($results) === 0) {

            $details = DetailPo::query()
                ->whereNotNull('detail')
                ->get();

            $foundArticles = [];

            foreach ($details as $detailPo) {

                $detail = $detailPo->detail;

                if (!is_array($detail)) {
                    continue;
                }

                $article =
                    trim(
                        (string) (
                            $detail['article_nr_'] ?? ''
                        )
                    );

                $description =
                    trim(
                        (string) (
                            $detail['description'] ?? ''
                        )
                    );

                if ($article === '') {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CARI ARTICLE ATAU DESCRIPTION
                |--------------------------------------------------------------------------
                */

                $matchArticle =
                    stripos(
                        $article,
                        $keyword
                    ) !== false;

                $matchDescription =
                    stripos(
                        $description,
                        $keyword
                    ) !== false;

                if (
                    !$matchArticle &&
                    !$matchDescription
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | HINDARI DUPLICATE ARTICLE
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $article,
                        $foundArticles,
                        true
                    )
                ) {
                    continue;
                }

                $foundArticles[] = $article;

                $results[] = [

                    /*
                    | ID BELUM ADA
                    */
                    'id' => null,

                    'article' =>
                        $article,

                    'description' =>
                        $description,

                    'jenis' =>
                        null,

                    'harga' =>
                        0,

                    /*
                    |--------------------------------------
                    | FLAG PENTING
                    |--------------------------------------
                    */

                    'exists_in_upah' => false,

                ];

                if (count($results) >= 15) {
                    break;
                }
            }
        }

        return response()->json($results);
    }
    public function storeUpah(Request $request)
    {
        $validated = $request->validate([
            'article' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'tanggal' => [
                'required',
                'date',
            ],

            'pekerjaan' => [
                'required',
                'string',
                'max:100',
            ],

            'person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'qty' => [
                'required',
                'numeric',
                'min:0',
            ],

            'harga' => [
                'required',
                'numeric',
                'min:0',
            ],

            'total' => [
                'required',
                'numeric',
                'min:0',
            ],

            'no_po' => [
                'nullable',
                'string',
                'max:100',
            ],

            'no_spk' => [
                'nullable',
                'string',
                'max:100',
            ],

            'create_master_upah' => [
                'nullable',
                'boolean',
            ],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {

                $article = trim($validated['article']);
                $pekerjaan = trim($validated['pekerjaan']);

                /*
                |--------------------------------------------------------------------------
                | DUA ARAH MASTER UPAH
                |--------------------------------------------------------------------------
                | Jangan hanya mengecek ARTICLE.
                |
                | Satu article dapat mempunyai beberapa pekerjaan, contoh:
                |   2635096 -> Packing Box      -> 4.000
                |   2635096 -> Packing Foam     -> 2.000
                |   2635096 -> Packing Medium   -> 400
                |
                | Karena itu yang menjadi identitas pekerjaan adalah:
                |   ARTICLE + JENIS PEKERJAAN
                |
                | Jika kombinasi tersebut belum ada di master UpahBorongan,
                | otomatis dibuat dari transaksi yang sedang disimpan.
                |--------------------------------------------------------------------------
                */
                $masterUpah = UpahBorongan::query()
                    ->whereRaw('TRIM(article) = ?', [$article])
                    ->whereRaw('TRIM(jenis) = ?', [$pekerjaan])
                    ->first();

                $masterCreated = false;

                if (!$masterUpah) {
                    $timeline = [
                        [
                            'action' => 'created_from_transaction',
                            'timestamp' => now()->format('Y-m-d H:i:s'),
                            'user_id' => auth()->id(),
                            'user_name' => auth()->user()?->name,
                            'harga_lama' => null,
                            'harga_baru' => $validated['harga'],
                        ]
                    ];

                    $masterUpah = UpahBorongan::create([
                        'article' => $article,
                        'description' => $validated['description'] ?? null,
                        'jenis' => $pekerjaan,
                        'harga' => $validated['harga'],
                        'update_remark' => $timeline,
                    ]);

                    $masterCreated = true;
                }

                /*
                |--------------------------------------------------------------------------
                | SIMPAN TRANSAKSI UPAH
                |--------------------------------------------------------------------------
                */
                $validated['article'] = $article;
                $validated['pekerjaan'] = $pekerjaan;

                // Jangan ikut disimpan sebagai kolom Upah jika field ini
                // tidak tersedia pada tabel transaksi.
                unset($validated['create_master_upah']);

                $validated['updated_by'] = [
                    [
                        'action' => 'created',
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                        'user_id' => auth()->id(),
                        'user_name' => auth()->user()?->name,
                    ]
                ];

                $upah = Upah::create($validated);

                return [
                    'upah' => $upah,
                    'master_created' => $masterCreated,
                    'master_upah' => $masterUpah,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $result['master_created']
                    ? 'Transaksi berhasil disimpan dan pekerjaan baru otomatis ditambahkan ke database upah.'
                    : 'Data upah berhasil disimpan.',
                'master_created' => $result['master_created'],
                'master_upah' => $result['master_upah'],
                'data' => $result['upah'],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data upah.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchPekerjaan(Request $request)
    {
        $article = trim($request->get('article', ''));
        $keyword = trim($request->get('q', ''));

        if ($article === '') {
            return response()->json([]);
        }

        $query = UpahBorongan::query()
            ->whereRaw('TRIM(article) = ?', [$article])
            ->whereNotNull('jenis')
            ->where('jenis', '<>', '');

        // Kalau q diisi, filter pekerjaan.
        // Kalau q kosong, ambil SEMUA pekerjaan
        // dari article yang dipilih.
        if ($keyword !== '') {
            $query->where(
                'jenis',
                'like',
                "%{$keyword}%"
            );
        }

        $data = $query
            ->orderBy('jenis', 'asc')
            ->get([
                'id',
                'article',
                'jenis',
                'harga',
            ]);

        return response()->json(
            $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'article' => $item->article,
                    'jenis' => $item->jenis,
                    'harga' => $item->harga,
                ];
            })->values()
        );
    }
    public function storeMassUpah(Request $request)
    {
        $request->validate([
            'rows' => [
                'required',
                'array',
                'min:1',
            ],

            'rows.*.article' => [
                'required',
                'string',
                'max:100',
            ],

            'rows.*.description' => [
                'nullable',
                'string',
            ],

            'rows.*.tanggal' => [
                'required',
                'date',
            ],

            'rows.*.pekerjaan' => [
                'required',
                'string',
                'max:100',
            ],

            'rows.*.person' => [
                'nullable',
                'string',
                'max:150',
            ],

            'rows.*.qty' => [
                'required',
                'numeric',
                'min:0',
            ],

            'rows.*.harga' => [
                'required',
                'numeric',
                'min:0',
            ],

            'rows.*.total' => [
                'required',
                'numeric',
                'min:0',
            ],

            'rows.*.no_po' => [
                'nullable',
                'string',
                'max:100',
            ],

            'rows.*.no_spk' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);


        DB::beginTransaction();

        try {

            $created = [];

            foreach ($request->rows as $row) {

                $created[] = Upah::create([

                    'article' =>
                        $row['article'],

                    'description' =>
                        $row['description'] ?? null,

                    'tanggal' =>
                        $row['tanggal'],

                    'pekerjaan' =>
                        $row['pekerjaan'],

                    'person' =>
                        $row['person'] ?? null,

                    'qty' =>
                        $row['qty'],

                    'harga' =>
                        $row['harga'],

                    'total' =>
                        $row['total'],

                    'no_po' =>
                        $row['no_po'] ?? null,

                    'no_spk' =>
                        $row['no_spk'] ?? null,

                    'updated_by' => [
                        [
                            'action' => 'created',

                            'timestamp' =>
                                now()->format(
                                    'Y-m-d H:i:s'
                                ),

                            'user_id' =>
                                auth()->id(),

                            'user_name' =>
                                auth()->user()?->name,
                        ]
                    ],

                ]);
            }


            DB::commit();


            return response()->json([

                'success' => true,

                'message' =>
                    count($created) .
                    ' data berhasil disimpan.',

                'data' =>
                    $created,

            ]);


        } catch (\Throwable $e) {

            DB::rollBack();


            return response()->json([

                'success' => false,

                'message' =>
                    'Gagal menyimpan data mass.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = trim(
            $request->input('search', '')
        );


        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = Upah::query();


        /*
        |--------------------------------------------------------------------------
        | DATE RANGE
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {

            $query->whereDate(
                'tanggal',
                '>=',
                $dateFrom
            );

        }

        if ($dateTo) {

            $query->whereDate(
                'tanggal',
                '<=',
                $dateTo
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        |
        | Sama konsepnya dengan pencarian JS:
        | seluruh isi TR.
        |
        */

        if ($search !== '') {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'article',
                    'like',
                    '%' . $search . '%'
                )

                    ->orWhere(
                        'description',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'pekerjaan',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'person',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'qty',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'harga',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'total',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'no_po',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'no_spk',
                        'like',
                        '%' . $search . '%'
                    );

            });

        }


        /*
        |--------------------------------------------------------------------------
        | ORDER
        |--------------------------------------------------------------------------
        */

        $data = $query
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | CEK DATA
        |--------------------------------------------------------------------------
        */

        if ($data->isEmpty()) {

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk diexport.'
            ], 404);

        }


        /*
        |--------------------------------------------------------------------------
        | DETEKSI JENIS LAPORAN
        |--------------------------------------------------------------------------
        */

        $isPacking =
            $data->contains(function ($item) {

                return str_contains(
                    strtolower(
                        trim($item->pekerjaan ?? '')
                    ),
                    'packing'
                );

            });


        /*
        |--------------------------------------------------------------------------
        | REPORT TYPE
        |--------------------------------------------------------------------------
        */

        $reportType =
            $isPacking
            ? 'packing'
            : 'normal';


        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        $periode = '';

        if (
            $dateFrom &&
            $dateTo
        ) {

            $periode =
                date('d-m-Y', strtotime($dateFrom))
                . '_sd_'
                . date('d-m-Y', strtotime($dateTo));

        } elseif ($dateFrom) {

            $periode =
                'mulai_' .
                date('d-m-Y', strtotime($dateFrom));

        } elseif ($dateTo) {

            $periode =
                'sampai_' .
                date('d-m-Y', strtotime($dateTo));

        } else {

            $periode =
                date('Y-m-d');

        }


        $filename =
            'Rekap_Pembayaran_Borongan_'
            . ucfirst($reportType)
            . '_'
            . $periode
            . '.xlsx';


        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        return Excel::download(

            new ReportExport(
                $data,
                $reportType,
                $dateFrom,
                $dateTo
            ),

            $filename

        );
    }

}

