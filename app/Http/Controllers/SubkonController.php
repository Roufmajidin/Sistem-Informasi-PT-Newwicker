<?php

namespace App\Http\Controllers;

use App\Models\SupKontrak;
use App\Models\Supplier;
use App\Models\DetailPo;
use App\Models\JenisSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubkonController extends Controller
{
    public function index(Request $request)
    {
        $query = SupKontrak::with('supplier')
            ->orderByDesc('id');

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'article_code',
                    'like',
                    "%{$search}%"
                )

                    ->orWhere(
                        'remark',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhereHas('supplier', function ($supplier) use ($search) {

                        $supplier->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );

                    });

            });
        }

        if ($request->filled('supplier_id')) {

            $query->where(
                'supplier_id',
                $request->supplier_id
            );
        }

        $kontrak = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'pages.subkon.index',
            compact('kontrak')
        );
    }
    /**
     * Search Article Code dari detail_po
     */
    public function searchArticle(Request $request)
    {
        $search = trim($request->get('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $data = DetailPo::query()
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(detail, '$.article_nr_')) LIKE ?",
                ["%{$search}%"]
            )
            ->with('po')
            ->limit(20)
            ->get();

        $results = $data->map(function ($detailPo) {

            $detail = is_array($detailPo->detail)
                ? $detailPo->detail
                : [];

            return [
                'id' => $detailPo->id,

                'article_code' => $detail['article_nr_'] ?? '',

                'description' => $detail['description'] ?? '',

                'qty' => $detail['qty'] ?? 0,

                'finishing' => $detail['finishing'] ?? '',

                'po_id' => $detailPo->po_id,

                'po' => $detailPo->po?->no_po
                    ?? $detailPo->po?->po
                    ?? $detailPo->po_id,
            ];
        });

        return response()->json($results);
    }


    /**
     * Search supplier
     */
    public function searchSupplier(Request $request)
    {
        $search = trim($request->get('q', ''));

        $query = Supplier::query()
            ->with('jenis');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $suppliers = $query
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(

            $suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'text' => $supplier->name,

                    'jenis_id' => $supplier->jenis_supplier_id,

                    'jenis' => $supplier->jenis?->name,

                    'kategori' => $supplier->jenis?->kategori,
                ];
            })
        );
    }


    /**
     * Search kategori
     *
     * Sumber kategori:
     * jenis_suppliers.kategori
     */
    public function searchKategori(Request $request)
    {
        $search = trim($request->get('q', ''));

        $query = JenisSupplier::query()
            ->whereNotNull('name')
            ->where('name', '!=', '');

        if ($search !== '') {
            $query->where(
                'name',
                'like',
                "%{$search}%"
            );
        }

        $kategori = $query
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(
            $kategori->map(function ($item) {
                return [
                    'id' => $item->name,
                    'text' => $item->name,
                ];
            })
        );
    }


    /**
     * Store dari modal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_code' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],
            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'harga_kontrak' => [
                'required',
                'numeric',
                'min:0',
            ],

            'remark' => [
                'nullable',
                'string',
            ],
        ]);

        $timeline = [];

        if (!empty($validated['remark'])) {
            $timeline[] = [
                'action' => 'created',
                'timestamp' => now()->toDateTimeString(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,

                'remark_lama' => null,
                'remark_baru' => $validated['remark'],

                'harga_lama' => null,
                'harga_baru' => $validated['harga_kontrak'],
            ];
        }

        $kontrak = SupKontrak::create([
            'article_code' => $validated['article_code'],

            'detail_po_id' =>
                $validated['detail_po_id'] ?? null,

            'description' =>
                $validated['description'] ?? null,

            'supplier_id' =>
                $validated['supplier_id'],

            'kategori' =>
                $validated['kategori'] ?? null,

            'harga_kontrak' =>
                $validated['harga_kontrak'],

            'remark' =>
                $validated['remark'] ?? null,

            'update_remark' => $timeline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kontrak supplier berhasil ditambahkan.',
            'data' => $kontrak->load('supplier'),
        ]);
    }
    public function editData(SupKontrak $subkon)
    {
        $subkon->load('supplier', 'detailPo');

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $subkon->id,

                'article_code' => $subkon->article_code,

                'detail_po_id' => $subkon->detail_po_id,

                'description' => $subkon->description,

                'supplier_id' => $subkon->supplier_id,

                'supplier_name' => $subkon->supplier?->name,

                'kategori' => $subkon->kategori,

                'harga_kontrak' => $subkon->harga_kontrak,

                'remark' => $subkon->remark,
            ],
        ]);
    }
    public function update(Request $request, SupKontrak $subkon)
    {
        $validated = $request->validate([

            'article_code' => [
                'required',
                'string',
                'max:100',
            ],

            'detail_po_id' => [
                'nullable',
                'exists:detail_po,id',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'harga_kontrak' => [
                'required',
                'numeric',
                'min:0',
            ],

            'remark' => [
                'nullable',
                'string',
            ],
        ]);


        $timeline = $subkon->update_remark ?? [];

        $changes = [];

        /*
        |--------------------------------------------------------------------------
        | ARTICLE
        |--------------------------------------------------------------------------
        */

        if (
            $subkon->article_code !==
            $validated['article_code']
        ) {
            $changes['article_code'] = [
                'lama' => $subkon->article_code,
                'baru' => $validated['article_code'],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION
        |--------------------------------------------------------------------------
        */

        if (
            ($subkon->description ?? '') !==
            ($validated['description'] ?? '')
        ) {
            $changes['description'] = [
                'lama' => $subkon->description,
                'baru' => $validated['description'] ?? null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | DETAIL PO
        |--------------------------------------------------------------------------
        */

        if (
            (int) ($subkon->detail_po_id ?? 0) !==
            (int) ($validated['detail_po_id'] ?? 0)
        ) {
            $changes['detail_po_id'] = [
                'lama' => $subkon->detail_po_id,
                'baru' => $validated['detail_po_id'] ?? null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */

        if (
            (int) $subkon->supplier_id !==
            (int) $validated['supplier_id']
        ) {
            $changes['supplier_id'] = [
                'lama' => $subkon->supplier_id,
                'baru' => $validated['supplier_id'],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        if (
            ($subkon->kategori ?? '') !==
            ($validated['kategori'] ?? '')
        ) {
            $changes['kategori'] = [
                'lama' => $subkon->kategori,
                'baru' => $validated['kategori'] ?? null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */

        if (
            (float) $subkon->harga_kontrak !==
            (float) $validated['harga_kontrak']
        ) {
            $changes['harga_kontrak'] = [
                'lama' => $subkon->harga_kontrak,
                'baru' => $validated['harga_kontrak'],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | REMARK
        |--------------------------------------------------------------------------
        */

        if (
            ($subkon->remark ?? '') !==
            ($validated['remark'] ?? '')
        ) {
            $changes['remark'] = [
                'lama' => $subkon->remark,
                'baru' => $validated['remark'] ?? null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN TIMELINE
        |--------------------------------------------------------------------------
        */

        if (!empty($changes)) {

            $timeline[] = [
                'action' => 'updated',

                'timestamp' =>
                    now()->toDateTimeString(),

                'user_id' =>
                    auth()->id(),

                'user_name' =>
                    auth()->user()?->name,

                'changes' =>
                    $changes,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $subkon->update([

            'article_code' =>
                $validated['article_code'],

            'detail_po_id' =>
                $validated['detail_po_id'] ?? null,

            'description' =>
                $validated['description'] ?? null,

            'supplier_id' =>
                $validated['supplier_id'],

            'kategori' =>
                $validated['kategori'] ?? null,

            'harga_kontrak' =>
                $validated['harga_kontrak'],

            'remark' =>
                $validated['remark'] ?? null,

            'update_remark' =>
                $timeline,
        ]);


        return response()->json([
            'success' => true,

            'message' =>
                'Kontrak supplier berhasil diperbarui.',

            'data' =>
                $subkon->load('supplier'),
        ]);
    }
    public function destroy(SupKontrak $subkon)
    {
        try {

            $subkon->delete();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Kontrak supplier berhasil dihapus.'
                );

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kontrak supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}