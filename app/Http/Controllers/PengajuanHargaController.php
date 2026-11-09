<?php
namespace App\Http\Controllers;

use App\Models\BahanKategori;
use App\Models\DetailPo;
use App\Models\PengajuanHargaPekerjaan;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengajuanHargaController extends Controller
{
    public function storeKategori(\Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100'],
    ]);

    $name = trim($data['name']);
    $existing = \App\Models\BahanKategori::query()
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->first();

    if ($existing) {
        return response()->json(['id' => $existing->id, 'name' => $existing->name]);
    }

    $category = \App\Models\BahanKategori::create(['name' => $name]);

    return response()->json([
        'id' => $category->id,
        'name' => $category->name,
    ], 201);
}

  
public function index(Request $request)
{
    $search = trim((string) $request->input('q', ''));
    $company = trim((string) $request->input('company', ''));
    $release = $request->input('release', 'desc');

    if (!in_array($release, ['asc', 'desc'], true)) {
        $release = 'desc';
    }

    // ==========================================
    // QUERY PO
    // ==========================================
    $posQuery = Po::query()
        ->with(['details']);

    // ==========================================
    // SEARCH PO / BUYER / ARTICLE
    // ==========================================
    if ($search !== '') {

        // Contoh:
        // 26-80     -> 2680
        // NW 26-80  -> nw2680
        // NW 26 - 80 -> nw2680
        $normalizedSearch = mb_strtolower(
            preg_replace('/[\s\-]+/u', '', $search)
        );

        $posQuery->where(function ($query) use (
            $search,
            $normalizedSearch
        ) {

            // Search nomor PO tanpa spasi / strip
            $query->whereRaw(
                "LOWER(
                    REPLACE(
                        REPLACE(order_no, ' ', ''),
                        '-', ''
                    )
                ) LIKE ?",
                ['%' . $normalizedSearch . '%']
            );

            // Search buyer / company
            $query->orWhere(
                'company_name',
                'LIKE',
                '%' . $search . '%'
            );

            // Search article di detail PO
            // Menggunakan kolom JSON detail
            $query->orWhereHas('details', function ($detailQuery) use ($search) {

                $detailQuery->where(function ($q) use ($search) {

                    $q->where(
                        'detail',
                        'LIKE',
                        '%' . $search . '%'
                    );

                });

            });
        });
    }

    // ==========================================
    // FILTER COMPANY
    // ==========================================
    
if ($company !== '') {
    $posQuery->where(function ($query) use ($company) {
        $query->whereRaw(
            "UPPER(REPLACE(order_no, ' ', '')) LIKE ?",
            [strtoupper($company) . '%']
        );
    });
}


    // ==========================================
    // SORT RELEASE DATE
    // ==========================================
    $posQuery->orderBy(
        'release_date',
        $release
    )->orderByDesc('id');

    // ==========================================
    // PAGINATION
    // ==========================================
    $pos = $posQuery
        ->paginate(10)
        ->withQueryString();

    // ==========================================
    // AMBIL ID DETAIL PO
    // ==========================================
    $detailIds = $pos->getCollection()
        ->flatMap(function ($po) {
            return $po->details->pluck('id');
        })
        ->unique()
        ->values();

    // ==========================================
    // DATA PENGAJUAN HARGA PEKERJAAN
    // ==========================================
    $pekerjaan = PengajuanHargaPekerjaan::query()
        ->whereIn('detail_po_id', $detailIds)
        ->orderBy('id')
        ->get()
        ->groupBy('detail_po_id');

    // ==========================================
    // MASTER KATEGORI PEKERJAAN
    // ==========================================
    $masterPekerjaan = BahanKategori::query()
        ->orderBy('name')
        ->get();

    // ==========================================
    // RETURN VIEW
    // ==========================================
    return view('pages.pengajuan_harga.index', [
        'pos' => $pos,
        'pekerjaan' => $pekerjaan,
        'masterPekerjaan' => $masterPekerjaan,
        'search' => $search,
        'company' => $company,
        'release' => $release,
    ]);
}

public function updateKategori(Request $request, $id)
{
    $validated = $request->validate([
        'name' => 'required|string|max:150',
    ]);

    $name = trim($validated['name']);

    $kategori = \App\Models\BahanKategori::findOrFail($id);

    $duplicate = \App\Models\BahanKategori::query()
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->where('id', '!=', $kategori->id)
        ->exists();

    if ($duplicate) {
        return response()->json([
            'message' => 'Nama kategori sudah digunakan.'
        ], 422);
    }

    $kategori->name = $name;
    $kategori->save();

    return response()->json([
        'success' => true,
        'id' => $kategori->id,
        'name' => $kategori->name,
        'message' => 'Kategori berhasil diperbarui.'
    ]);
}

    public function savePekerjaan(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:pengajuan_harga_pekerjaans,id'],
            'po_id' => ['required', 'integer', 'exists:po,id'],
            'detail_po_id' => ['required', 'integer', 'exists:detail_po,id'],
            'bahan_kategori_id' => ['required', 'integer', 'exists:bahan_kategori,id'],
            'article_code' => ['nullable', 'string', 'max:150'],
            'ppic_price' => ['nullable', 'numeric', 'min:0'],
            'director_price' => ['nullable', 'numeric', 'min:0'],
            'pengajuan_sub_price' => ['nullable', 'numeric', 'min:0'],
            'director_sub_price' => ['nullable', 'numeric', 'min:0'],
            'ppic_remark' => ['nullable', 'string'],
            'sub_remark' => ['nullable', 'string'],
            'director_remark' => ['nullable', 'string'],
        ]);
        $po = Po::findOrFail($data['po_id']);
        $detail = DetailPo::where('po_id', $po->id)->findOrFail($data['detail_po_id']);
        $job = isset($data['id']) ? PengajuanHargaPekerjaan::findOrFail($data['id']) : new PengajuanHargaPekerjaan();
        if ($job->exists && ((int) $job->po_id !== (int) $po->id || (int) $job->detail_po_id !== (int) $detail->id)) {
            throw ValidationException::withMessages(['id' => 'Pekerjaan tidak sesuai dengan PO/item.']);
        }
        $fields = ['bahan_kategori_id', 'ppic_price', 'director_price', 'pengajuan_sub_price', 'director_sub_price', 'ppic_remark', 'sub_remark', 'director_remark'];
        foreach ($fields as $field) $job->{$field} = $data[$field] ?? null;
        $job->po_id = $po->id;
        $job->detail_po_id = $detail->id;
        $job->po_code = $po->order_no;
        $job->article_code = $data['article_code'] ?? null;
        if (!$job->exists) $job->created_by = $request->user()->id;
        $job->save();
        return response()->json(['success' => true, 'id' => $job->id, 'message' => 'Pekerjaan berhasil disimpan.']);
    }

    public function deletePekerjaan(Request $request)
    {
        $data = $request->validate(['id' => ['required', 'integer', 'exists:pengajuan_harga_pekerjaans,id']]);
        PengajuanHargaPekerjaan::findOrFail($data['id'])->delete();
        return response()->json(['success' => true, 'message' => 'Pekerjaan dihapus.']);
    }
}
