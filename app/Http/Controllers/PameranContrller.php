<?php
namespace App\Http\Controllers;

use App\Imports\ProductPameranImport;
use App\Models\Exhibition;
use App\Models\ProductPameran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PameranContrller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
                                //
        $e = Exhibition::all(); // dropdown

        $pm = ProductPameran::query();

        if ($request->filled('exhibition_id')) {
            $pm->where('exhibition_id', $request->exhibition_id);
        }
        $pm = $pm->get(); // eksekusi query jadi collection

        // $pm = ProductPameran::get();
        return view('pages.pameran.index', compact('e', 'pm'));
    }
    public function getByExhibition(Request $request)
    {
        $pm = ProductPameran::query();

        if ($request->filled('exhibition_id')) {
            $pm->where('exhibition_id', $request->exhibition_id);
        }

        $pm = $pm->get();

        $html = view('pages.pameran._table', compact('pm'))->render();

        return response()->json([
            'status' => 'success',
            'data'   => $html,
        ]);
    }
    public function allEventConfig()
    {
        $ex = Exhibition::get();
        return view('pages.pameran.config', compact('ex'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'          => 'required|mimes:xlsx,xls,csv',
            'exhibition_id' => 'required|exists:exhibitions,id',
        ]);

        $exhibitionId = $request->exhibition_id;

        try {
            Excel::import(new ProductPameranImport($exhibitionId), $request->file('file'));

            return response()->json([
                'status'  => 'success',
                'message' => 'Data produk pameran berhasil diimport!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getpameranData()
    {
        $isactive = Exhibition::where('active', 1)->first();
        // $product = ProductPameran::where('exhibition_id', $isactive->id)->get();
        // dd($product);
        $product = ProductPameran::where('exhibition_id', $isactive->id)->get()->map(function ($p, $i) {
            $articleCode = trim($p->article_code); // hapus spasi sebelum/akhir
                                                   // dd($articleCode);
            $photoPath = "pameran/{$articleCode}.jpg";
            // dd($photoPath);
            $photo = Storage::path($photoPath)
                ? asset("storage/{$photoPath}")
                : asset('images/default.jpg');

            return [
                // 'nr'                 => $i + 1, // nomor urut
                'photo'         => $photo,
                'article_code'       => $p->article_code,
                'name'               => $p->name,
                'categories'         => $p->categories,
                'remark'         => $p->remark,
                // 'sub_categories'     => $p->sub_categories,
                'item_dimension'     => [
                    'w' => $p->item_w,
                    'd' => $p->item_d,
                    'h' => $p->item_h,
                ],
                'packing_dimension'  => [
                    'w' => $p->packing_w,
                    'd' => $p->packing_d,
                    'h' => $p->packing_h,
                ],
                'size_of_set'        => [
                    'set_2' => $p->set2,
                    'set_3' => $p->set3,
                    'set_4' => $p->set4,
                    'set_5' => $p->set5,
                ],
                'composition'        => $p->composition,
                'finishing'          => $p->finishing,
                // 'qty'                => (float) $p->qty,
                'cbm'                => round((float) $p->cbm,2 ),
                // 'total_cbm'          => (float) $p->total_cbm,
                // 'rangka'             => (float) $p->rangka,
                // 'anyam'              => (float) $p->anyam,
                // 'value_in_usd'       => (float) $p->value_in_usd,
                'loadability_20'     => round((float) $p->loadability_20, 0),
                'loadability_40'     => round((float) $p->loadability_40, 0),
                'loadability_40_hc'  => round((float) $p->loadability_40hc, 0),
                'price_item'         => (double) $p->fob_jakarta_in_usd,
                'fob_jakarta_in_usd' => (double) $p->fob_jakarta_in_usd,
            ];
        });

        return response()->json([
            'status'   => true,
            'products' => $product,
        ]);

    }
    public function create()
    {
        //
    }

/**
 * Store a newly created resource in storage.
 */
    public function store(Request $request)
    {
        //
    }

/**
 * Display the specified resource.
 */
    public function show(string $id)
    {
        //
    }

/**
 * Show the form for editing the specified resource.
 */
    public function edit(string $id)
    {
        //
    }

/**
 * Update the specified resource in storage.
 */
    public function update(Request $request, string $id)
    {
        //
    }

/**
 * Remove the specified resource from storage.
 */
    public function destroy(string $id)
    {
        //
    }
}
