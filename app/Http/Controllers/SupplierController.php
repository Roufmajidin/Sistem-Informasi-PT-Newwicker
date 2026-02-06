<?php
namespace App\Http\Controllers;

use App\Models\JenisSupplier;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function index()
    {
        return view('pages.supplier.index', [
            'suppliers' => Supplier::latest()->get(),
            'jenis'     => JenisSupplier::all(),
        ]);
    }
    public function search(Request $request)
    {
        $q = $request->q;

        return Supplier::where('name', 'LIKE', "%$q%")
            ->limit(10)
            ->get(['id', 'name']);
    }
    /* ================= JENIS ================= */

    public function storeJenis(Request $r)
    {
        JenisSupplier::create([
            'name'       => $r->name,
            'updated_by' => $this->log(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateJenis(Request $r, $id)
    {
        $j = JenisSupplier::findOrFail($id);

        $j->update([
            'name'       => $r->name,
            'updated_by' => $this->appendLog($j->updated_by, 'update jenis'),
        ]);

        return response()->json(['success' => true]);
    }

    /* ================= SUPPLIER ================= */

    public function storeSupplier(Request $r)
    {
        Supplier::create([
            'name'              => $r->name,
            'alamat'            => $r->alamat,
            'jenis_supplier_id' => $r->jenis_supplier_id,
            'updated_by'        => $this->log(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateSupplier(Request $r, $id)
    {
        $s = Supplier::findOrFail($id);

        $s->update([
            'name'              => $r->name,
            'alamat'            => $r->alamat,
            'jenis_supplier_id' => $r->jenis_supplier_id,
            'updated_by'        => $this->appendLog($s->updated_by, 'update supplier'),
        ]);

        return response()->json(['success' => true]);
    }

    /* ================= HELPER ================= */

    private function log()
    {
        return [[
            'user_id'   => auth()->id() ?? 1,
            'timestamp' => now(),
        ]];
    }

    private function appendLog($old, $remark)
    {
        $old = $old ?? [];

        $old[] = [
            'user_id'   => auth()->id() ?? 1,
            'timestamp' => now(),
            'remark'    => $remark,
        ];

        return $old;
    }

}
