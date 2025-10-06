<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'merk'       => 'required|string|max:255',
            'jenis'      => 'nullable|string|max:255',
            'deskripsi'  => 'nullable|string',
            'karyawan'   => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'catatan'    => 'nullable|string',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // cari karyawan (atau bisa sesuaikan logika Anda)
        $karyawanId = $request->karyawan_id ?? null;

        // upload foto kalau ada
        $fotoName = time() . '_' . $request->file('foto')->getClientOriginalName();
        $request->file('foto')->move(public_path('foto_inventory'), $fotoName);

        $inventory = Inventory::create([
            'merk'        => $request->merk,
            'jenis'       => $request->jenis,
            'deskripsi'   => $request->deskripsi,
            'karyawan_id' => $karyawanId,
            'keterangan'  => $request->keterangan,
            'catatan'     => $request->catatan,
            'foto'        => $fotoName,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $inventory,
        ]);
    }

    public function updateInline(Request $request)
    {
        $id    = $request->input('pk');    // primary key (id karyawan)
        $field = $request->input('name');  // nama kolom yang di-edit
        $value = $request->input('value'); // nilai baru

        $karyawan = Karyawan::find($id);
        if (! $karyawan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        // Simpan perubahan
        $karyawan->$field = $value;
        $karyawan->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbarui',
        ]);
    }
    public function uploadFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $inventory = Inventory::findOrFail($id);

        // hapus foto lama kalau ada
        if ($inventory->foto) {
            Storage::delete('public/foto_inventory/' . $inventory->foto);
        }

        // $fotoName = time() . '_' . $request->file('foto')->getClientOriginalName();
        // $request->file('foto')->storeAs('public/foto_inventory', $fotoName);
        $fotoName = time() . '_' . $request->file('foto')->getClientOriginalName();
        $request->file('foto')->move(public_path('foto_inventory'), $fotoName);

        $inventory->update(['foto' => $fotoName]);

        return response()->json(['success' => true, 'foto' => $fotoName]);
    }
    public function searchKaryawan(Request $request)
    {
        $term = $request->get('term');

        $karyawan = \App\Models\Karyawan::where('nama_lengkap', 'like', "%$term%")
            ->limit(10)
            ->get();

        $results = [];
        foreach ($karyawan as $k) {
            $results[] = [
                'id'    => $k->id,
                'value' => $k->nama_lengkap,
            ];
        }

        return response()->json($results);
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
