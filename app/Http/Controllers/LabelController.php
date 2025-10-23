<?php
namespace App\Http\Controllers;

use App\Models\Labeling;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //
    public function index()
    {
        $labelings = Labeling::latest()->get();
        return view('labeling.index', compact('labelings'));
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
    // Normalisasi format tanggal (jadwal_container)
    $jadwal = $request->jadwal;

    // Cek apakah formatnya seperti d/m/Y, misal 15/10/2025
    if (!empty($jadwal) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $jadwal)) {
        try {
            // Ubah ke format konsisten Y-m-d agar bisa diproses Carbon bila perlu
            $jadwal = \Carbon\Carbon::createFromFormat('d/m/Y', $jadwal)->format('Y-m-d');
        } catch (\Exception $e) {
            // Abaikan jika gagal parse
        }
    }

    // Jika ada ID, berarti update data
    if ($request->id) {
        $label = Labeling::find($request->id);
        if (!$label) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $label->update([
            'description'      => $request->description,
            'labels'           => $request->labels,
            'jadwal_container' => $jadwal,
            'status_rouf'      => $request->status_rouf,
            'status_yogi'      => $request->status_yogi,
        ]);

        return response()->json(['message' => 'Data berhasil diperbarui']);
    }

    // Jika ID kosong, buat data baru
    $new = Labeling::create([
        'description'      => $request->description,
        'labels'           => $request->labels,
        'jadwal_container' => $jadwal,
        'status_rouf'      => $request->status_rouf,
        'status_yogi'      => $request->status_yogi,
    ]);

    return response()->json([
        'message' => 'Data baru berhasil disimpan',
        'id'      => $new->id,
    ]);
}

    /**
     * Hapus data labeling.
     */
    public function destroy(string $id)
    {
        $label = Labeling::findOrFail($id);
        $label->delete();

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
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

}
