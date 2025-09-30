<?php
namespace App\Http\Controllers;

use App\Imports\ProductPameranImport;
use App\Models\Exhibition;
use App\Models\ProductPameran;
use Illuminate\Http\Request;
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
    public function allEventConfig(){
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
