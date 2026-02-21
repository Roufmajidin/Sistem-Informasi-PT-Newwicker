<?php
namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Kategori;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    //
    public function index()
    {
        return view('pages.setting.index', [
            'kategori'   => Kategori::all(),
            'checkpoint' => Checkpoint::with('kategori')->get(),
        ]);
    }
    public function storeKategori(Request $request)
    {
        Kategori::create([
            'kategori' => $request->nama,
        ]);

        return back();
    }

    public function storeCheckpoint(Request $request)
    {
        Checkpoint::create([
            'name'        => $request->name,
            'kategori_id' => $request->kategori_id,
        ]);

        return back();
    }
    public function storeCheckpointMass(Request $request)
    {
        $request->validate([
            'kategori_id'     => 'required|exists:kategori,id',
            'checkpoints_raw' => 'required|string',
        ]);

        // Ambil teks mentah
        $raw = $request->checkpoints_raw;

        /**
         * STEP 1:
         * - Ganti ENTER jadi koma
         * - Pecah berdasarkan koma
         */
        $raw   = str_replace(["\r\n", "\n"], ',', $raw);
        $items = explode(',', $raw);

        /**
         * STEP 2:
         * - Bersihkan teks
         * - Buang ":" di belakang
         * - Trim spasi
         * - Buang data kosong
         */
        $clean = collect($items)
            ->map(function ($item) {
                return trim(rtrim($item, ':'));
            })
            ->filter()
            ->unique(); // cegah duplikat

        /**
         * STEP 3:
         * INSERT SATU-SATU
         */
        foreach ($clean as $name) {
            Checkpoint::firstOrCreate([
                'name'        => $name,
                'kategori_id' => $request->kategori_id,
            ]);
        }

        return redirect()->back()->with('success', 'Checkpoint berhasil ditambahkan');
    }
    public function destroyCheckpoint($id)
    {
        $checkpoint = Checkpoint::findOrFail($id);

        $checkpoint->delete();

        return redirect()->back()->with('success', 'Checkpoint berhasil dihapus');
    }

}
