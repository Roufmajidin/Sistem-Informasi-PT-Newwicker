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

            $article = $detail['article_nr_'] ?? null;

            if (!$article) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | HANYA SEARCH ARTICLE CODE
            |--------------------------------------------------------------------------
            */

            if (
                stripos($article, $search) === false
            ) {
                continue;
            }

            $results[] = [
                'article' => $article,
                'description' =>
                    $detail['description'] ?? '',
            ];

            if (count($results) >= 20) {
                break;
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

        $data = UpahBorongan::query()
            ->where('article', 'like', "%{$keyword}%")
            ->orderBy('article')
            ->limit(15)
            ->get([
                'id',
                'article',
                'description',
                'jenis',
                'harga',
            ]);

        return response()->json(
            $data->map(function ($item) {

                return [
                    'id' => $item->id,
                    'article' => $item->article,
                    'description' => $item->description,
                    'jenis' => $item->jenis,
                    'harga' => $item->harga,
                ];

            })
        );
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

        ]);


        $validated['updated_by'] = [
            [
                'action' => 'created',

                'timestamp' =>
                    now()->format('Y-m-d H:i:s'),

                'user_id' =>
                    auth()->id(),

                'user_name' =>
                    auth()->user()?->name,
            ]
        ];


        $upah = Upah::create($validated);


        return response()->json([

            'success' => true,

            'message' =>
                'Data upah berhasil disimpan.',

            'data' =>
                $upah,

        ]);
    }
    public function searchPekerjaan(Request $request)
    {
        $article = trim($request->get('article', ''));
        $keyword = trim($request->get('q', ''));

        if ($article === '') {
            return response()->json([]);
        }

        $query = UpahBorongan::query()
            ->where('article', $article);

        if ($keyword !== '') {
            $query->where(
                'jenis',
                'like',
                "%{$keyword}%"
            );
        }

        return response()->json(
            $query
                ->orderBy('jenis')
                ->limit(20)
                ->get([
                    'id',
                    'article',
                    'jenis',
                    'harga',
                ])
                ->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'article' => $item->article,
                        'jenis' => $item->jenis,
                        'harga' => $item->harga,
                    ];

                })
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