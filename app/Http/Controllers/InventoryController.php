<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $data = Inventory::with('karyawan')->get();
        // dd($data);
        return view('pages.inventory.index', compact('data'));
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
    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'required|string',
            'pk'    => 'required|integer',
            'value' => 'nullable|string',
        ]);

        $inventory                   = Inventory::findOrFail($request->pk);
        $inventory->{$request->name} = $request->value;
        $inventory->save();

        return response()->json([
            'status' => 'success',
            'msg'    => 'Data berhasil diupdate.',
        ]);
    }
    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $inventory = \App\Models\Inventory::findOrFail($id);

        $file         = $request->file('foto');
        $fileBaseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $file->getClientOriginalExtension();

        $directory = storage_path('app/public/foto_inventory');
        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = time() . '-' . Str::slug($fileBaseName) . '.' . $extension;
        $path     = $directory . '/' . $filename;

        $imageContents = file_get_contents($file->getRealPath());
        file_put_contents($path, $imageContents);

        $inventory->foto = $filename;
        $inventory->save();

        return response()->json([
            'success'  => true,
            'filename' => $filename,
            'url'      => asset('storage/foto_inventory/' . $filename),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
