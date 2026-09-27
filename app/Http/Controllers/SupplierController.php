<?php

namespace App\Http\Controllers;

use App\Models\JenisSupplier;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));

        $query = Supplier::with(['jenis', 'vendor']);

        if ($search !== '') {
            $this->applySupplierSearch($query, $search);
        }

        $suppliers = $query->latest()->get();

        return view('pages.supplier.index', [
            'suppliers' => $suppliers,
            'jenis'     => JenisSupplier::orderBy('name')->get(),
            'search'    => $search,
        ]);
    }

    public function search(Request $request)
    {
        $search = trim($request->get('q', $request->get('search', '')));

        $query = Supplier::with(['jenis', 'vendor']);

        if ($search !== '') {
            $this->applySupplierSearch($query, $search);
        }

        $suppliers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'count'   => $suppliers->count(),
            'search'  => $search,
            'data'    => $suppliers->map(function ($supplier) {
                return $this->supplierJson($supplier);
            })->values(),
        ]);
    }

    public function searchVendor(Request $request)
    {
        $q = trim($request->get('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $like = '%' . $q . '%';

        $vendors = Vendor::query()
            ->where(function ($query) use ($like) {
                $query->where('nama_vendor', 'like', $like)
                    ->orWhere('nomor_rekening', 'like', $like)
                    ->orWhere('nama_rekening', 'like', $like)
                    ->orWhere('bank', 'like', $like)
                    ->orWhere('uniq', 'like', $like);
            })
            ->orderBy('nama_vendor')
            ->limit(15)
            ->get([
                'id',
                'nama_vendor',
                'nomor_rekening',
                'nama_rekening',
                'bank',
                'uniq',
            ]);

        return response()->json($vendors);
    }

    public function storeJenis(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $jenis = JenisSupplier::create([
            'name'       => trim($request->name),
            'updated_by' => $this->log(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis supplier berhasil ditambahkan.',
            'data'    => $jenis,
        ]);
    }

    public function updateJenis(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $jenis = JenisSupplier::findOrFail($id);

        $jenis->update([
            'name'       => trim($request->name),
            'updated_by' => $this->appendLog($jenis->updated_by, 'update jenis'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis supplier berhasil diperbarui.',
            'data'    => $jenis,
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'alamat'            => ['nullable', 'string'],
            'jenis_supplier_id' => ['nullable', 'exists:jenis_suppliers,id'],
            'vendor_id'         => ['nullable', 'exists:vendors,id'],
        ]);

        $supplier = Supplier::create([
            'name'              => trim($request->name),
            'alamat'            => $request->alamat,
            'jenis_supplier_id' => $request->jenis_supplier_id,
            'vendor_id'         => $request->vendor_id,
            'updated_by'        => $this->log(),
        ]);

        $supplier->load(['jenis', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan.',
            'data'    => $this->supplierJson($supplier),
        ]);
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'alamat'            => ['nullable', 'string'],
            'jenis_supplier_id' => ['nullable', 'exists:jenis_suppliers,id'],
            'vendor_id'         => ['nullable', 'exists:vendors,id'],
        ]);

        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'name'              => trim($request->name),
            'alamat'            => $request->alamat,
            'jenis_supplier_id' => $request->jenis_supplier_id,
            'vendor_id'         => $request->has('vendor_id') ? $request->vendor_id : $supplier->vendor_id,
            'updated_by'        => $this->appendLog($supplier->updated_by, 'update supplier'),
        ]);

        $supplier->load(['jenis', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diperbarui.',
            'data'    => $this->supplierJson($supplier),
        ]);
    }

    public function linkVendor(Request $request, $id)
    {
        $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
        ]);

        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'vendor_id'  => $request->vendor_id,
            'updated_by' => $this->appendLog($supplier->updated_by, 'link vendor'),
        ]);

        $supplier->load(['jenis', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Rekening vendor berhasil dihubungkan.',
            'data'    => $this->supplierJson($supplier),
        ]);
    }

    public function unlinkVendor($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'vendor_id'  => null,
            'updated_by' => $this->appendLog($supplier->updated_by, 'unlink vendor'),
        ]);

        $supplier->load(['jenis', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Rekening vendor berhasil dilepas.',
            'data'    => $this->supplierJson($supplier),
        ]);
    }

    private function applySupplierSearch($query, string $search): void
    {
        $like = '%' . $search . '%';

        $query->where(function ($q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('alamat', 'like', $like)
                ->orWhereHas('jenis', function ($q2) use ($like) {
                    $q2->where('name', 'like', $like);
                })
                ->orWhereHas('vendor', function ($q2) use ($like) {
                    $q2->where('nama_vendor', 'like', $like)
                        ->orWhere('nomor_rekening', 'like', $like)
                        ->orWhere('nama_rekening', 'like', $like)
                        ->orWhere('bank', 'like', $like)
                        ->orWhere('uniq', 'like', $like);
                });
        });
    }

    private function supplierJson(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'alamat' => $supplier->alamat,
            'jenis_supplier_id' => $supplier->jenis_supplier_id,
            'jenis' => $supplier->jenis?->name,
            'vendor_id' => $supplier->vendor_id,
            'vendor' => $supplier->vendor ? [
                'id' => $supplier->vendor->id,
                'nama_vendor' => $supplier->vendor->nama_vendor,
                'nomor_rekening' => $supplier->vendor->nomor_rekening,
                'nama_rekening' => $supplier->vendor->nama_rekening,
                'bank' => $supplier->vendor->bank,
                'uniq' => $supplier->vendor->uniq,
            ] : null,
        ];
    }

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
