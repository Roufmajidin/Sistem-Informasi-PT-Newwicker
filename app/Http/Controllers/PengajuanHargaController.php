<?php

namespace App\Http\Controllers;

use App\Models\BahanKategori;
use App\Models\Bom;
use App\Models\BomProd;
use App\Models\DetailPo;
use App\Models\PengajuanHargaPekerjaan;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengajuanHargaController extends Controller
{
    private function detailArray($detail): array
    {
        if (is_string($detail)) {
            $detail = json_decode($detail, true) ?: [];
        } elseif (is_object($detail)) {
            $detail = (array) $detail;
        }
        return is_array($detail) ? $detail : [];
    }

    private function itemCodes($detail): array
    {
        $detail = $this->detailArray($detail);
        $codes = [];
        foreach (['nw_code', 'nw_code_', 'nwCode', 'article_code', 'article_nr_', 'article_nr', 'article'] as $key) {
            $value = $detail[$key] ?? null;
            if (!is_scalar($value))
                continue;
            $value = trim((string) $value);
            if ($value !== '' && $value !== '-' && !in_array($value, $codes, true)) {
                $codes[] = $value;
            }
        }
        return $codes;
    }

    private function itemCode($detail): ?string
    {
        return $this->itemCodes($detail)[0] ?? null;
    }

    private function isFactory(Request $request): bool
    {
        // Sesuaikan jika akun factory menggunakan identitas selain email factory@...
        return str_starts_with(mb_strtolower((string) $request->user()?->email), 'factory@');
    }

    /**
     * Only BOMs with an explicit released marker are eligible for the lookup.
     * Check the actual schema, so an absent column never causes a SQL error.
     * If no supported release marker exists, do not expose unverified BOMs.
     */
    private function releasedBomQuery($model)
    {
        $query = $model::query();
        $table = $query->getModel()->getTable();

        // Prefer explicit release flags over general workflow status.
        foreach (['is_released', 'is_release', 'released'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $query->where($column, 1);
            }
        }

        foreach (['released_at', 'release_date', 'tanggal_release'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $query->whereNotNull($column);
            }
        }

        foreach (['release_status', 'status_release', 'status'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $query->whereRaw('LOWER(TRIM(' . $column . ')) IN (?, ?)', ['release', 'released']);
            }
        }

        // Fail closed: unknown release schema should not show unreleased BOMs.
        return $query->whereRaw('1 = 0');
    }

    private function bomLookup($codes): array
    {
        $codes = collect($codes)->filter()->map(fn($v) => trim((string) $v))->unique()->values();
        if ($codes->isEmpty())
            return [collect(), collect()];

        $cog = $this->releasedBomQuery(BomProd::class)
            ->whereIn('article_number', $codes->all())
            ->orderByDesc('id')->get(['id', 'article_number'])
            ->keyBy(fn($b) => mb_strtolower(trim((string) $b->article_number)));

        $samples = $this->releasedBomQuery(Bom::class)
            ->whereIn('article_number', $codes->all())
            ->orderByDesc('id')->get(['id', 'article_number'])
            ->keyBy(fn($b) => mb_strtolower(trim((string) $b->article_number)));

        return [$cog, $samples];
    }

    public function storeKategori(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $name = trim($data['name']);
        $existing = BahanKategori::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        if ($existing)
            return response()->json(['id' => $existing->id, 'name' => $existing->name]);
        $category = BahanKategori::create(['name' => $name]);
        return response()->json(['id' => $category->id, 'name' => $category->name], 201);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $company = trim((string) $request->input('company', ''));
        $release = in_array($request->input('release'), ['asc', 'desc'], true)
            ? $request->input('release') : 'desc';

        $posQuery = Po::query()->with('details');
        if ($search !== '') {
            $normalizedSearch = mb_strtolower(preg_replace('/[\s\-]+/u', '', $search));
            $posQuery->where(function ($query) use ($search, $normalizedSearch) {
                $query->whereRaw(
                    "LOWER(REPLACE(REPLACE(order_no, ' ', ''), '-', '')) LIKE ?",
                    ['%' . $normalizedSearch . '%']
                )->orWhere('company_name', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('details', function ($detailQuery) use ($search) {
                        $detailQuery->where('detail', 'LIKE', '%' . $search . '%');
                    });
            });
        }
        if ($company !== '') {
            $posQuery->whereRaw(
                "UPPER(REPLACE(order_no, ' ', '')) LIKE ?",
                [strtoupper($company) . '%']
            );
        }

        $pos = $posQuery->orderBy('release_date', $release)
            ->orderByDesc('id')->paginate(10)->withQueryString();
        $detailIds = $pos->getCollection()->flatMap(fn($po) => $po->details->pluck('id'))->unique()->values();
        $pekerjaan = PengajuanHargaPekerjaan::query()->whereIn('detail_po_id', $detailIds)
            ->orderBy('id')->get()->groupBy('detail_po_id');
        $masterPekerjaan = BahanKategori::query()->orderBy('name')->get();

        // Ambil semua alternatif kode pada item yang sedang tampil.
        $codes = $pos->getCollection()->flatMap(function ($po) {
            return $po->details->flatMap(fn($item) => $this->itemCodes($item->detail));
        })->unique()->values();

        [$cogMatches, $bomSampleMatches] = $this->bomLookup($codes);
        $isFactory = $this->isFactory($request);

        return view('pages.pengajuan_harga.index', compact(
            'pos',
            'pekerjaan',
            'masterPekerjaan',
            'search',
            'company',
            'release',
            'cogMatches',
            'bomSampleMatches',
            'isFactory'
        ));
    }

    public function updateKategori(Request $request, $id)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:150']]);
        $name = trim($validated['name']);
        $kategori = BahanKategori::findOrFail($id);
        $duplicate = BahanKategori::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where('id', '!=', $kategori->id)->exists();
        if ($duplicate)
            return response()->json(['message' => 'Nama kategori sudah digunakan.'], 422);
        $kategori->name = $name;
        $kategori->save();
        return response()->json([
            'success' => true,
            'id' => $kategori->id,
            'name' => $kategori->name,
            'message' => 'Kategori berhasil diperbarui.',
        ]);
    }

    public function savePekerjaan(Request $request)
    {
        $isFactory = $this->isFactory($request);
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:pengajuan_harga_pekerjaans,id'],
            'po_id' => ['required', 'integer', 'exists:po,id'],
            'detail_po_id' => ['required', 'integer', 'exists:detail_po,id'],
            'bahan_kategori_id' => ['required', 'integer', 'exists:bahan_kategori,id'],
            'article_code' => ['nullable', 'string', 'max:150'],
            'ppic_price' => ['nullable', 'numeric', 'min:0'],
            'director_price' => ['nullable', 'numeric', 'min:0'],
            'pengajuan_sub_price' => ['nullable', 'numeric', 'min:0'],
            'ppic_remark' => ['nullable', 'string'],
            'sub_remark' => ['nullable', 'string'],
            'director_remark' => ['nullable', 'string'],
        ]);

        $po = Po::findOrFail($data['po_id']);
        $detail = DetailPo::where('po_id', $po->id)->findOrFail($data['detail_po_id']);
        $job = !empty($data['id'])
            ? PengajuanHargaPekerjaan::findOrFail($data['id'])
            : new PengajuanHargaPekerjaan();

        if ($job->exists && ((int) $job->po_id !== (int) $po->id || (int) $job->detail_po_id !== (int) $detail->id)) {
            throw ValidationException::withMessages(['id' => 'Pekerjaan tidak sesuai dengan PO/item.']);
        }

        // Server adalah sumber kebenaran: field yang dilarang tidak dapat diubah lewat DevTools/API.
        // PPIC dan Sub hanya untuk user non-factory. Director hanya untuk factory@.
        $priceFields = $isFactory
            ? ['director_price' => 'directur_date']
            : ['ppic_price' => 'ppic_date', 'pengajuan_sub_price' => 'sub_date'];

        foreach ($priceFields as $field => $dateField) {
            if (array_key_exists($field, $data)) {
                $old = $job->{$field};
                $new = $data[$field];
                $changed = !$job->exists || ($old === null) !== ($new === null)
                    || ($old !== null && $new !== null && round((float) $old, 2) !== round((float) $new, 2));
                if ($changed) {
                    $job->{$field} = $new;
                    $job->{$dateField} = now();
                }
            }
        }
        $job->bahan_kategori_id = $data['bahan_kategori_id'];
        if (!$isFactory) {
            $job->ppic_remark = $data['ppic_remark'] ?? $job->ppic_remark;
            $job->sub_remark = $data['sub_remark'] ?? $job->sub_remark;
        } else {
            $job->director_remark = $data['director_remark'] ?? $job->director_remark;
        }
        $job->po_id = $po->id;
        $job->detail_po_id = $detail->id;
        $job->po_code = $po->order_no;
        $job->article_code = $this->itemCode($detail->detail);
        if (!$job->exists)
            $job->created_by = $request->user()->id;
        $job->save();

        return response()->json([
            'success' => true,
            'id' => $job->id,
            'article_code' => $job->article_code,
            'ppic_date' => $job->ppic_date?->format('d-m-y H:i'),
            'directur_date' => $job->directur_date?->format('d-m-y H:i'),
            'sub_date' => $job->sub_date?->format('d-m-y H:i'),
            'message' => 'Pekerjaan berhasil disimpan.',
        ]);
    }

    public function deletePekerjaan(Request $request)
    {
        $data = $request->validate(['id' => ['required', 'integer', 'exists:pengajuan_harga_pekerjaans,id']]);
        PengajuanHargaPekerjaan::findOrFail($data['id'])->delete();
        return response()->json(['success' => true, 'message' => 'Pekerjaan dihapus.']);
    }

    public function directurPages(Request $request)
    {
        $query = PengajuanHargaPekerjaan::with(['po', 'detailPo', 'kategori']);
        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($q) use ($term) {
                $q->where('po_code', 'like', "%{$term}%")
                    ->orWhere('article_code', 'like', "%{$term}%")
                    ->orWhereHas('po', fn($po) => $po->where('order_no', 'like', "%{$term}%"))
                    ->orWhereHas('kategori', fn($cat) => $cat->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('detailPo', fn($detail) => $detail->where('detail', 'like', "%{$term}%"));
            });
        }
        if ($request->status === 'open') {
            $query->where(fn($q) => $q->where('closed', 0)->orWhereNull('closed'));
        } elseif ($request->status === 'closed') {
            $query->where('closed', 1);
        }
        $pekerjaans = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $codes = $pekerjaans->getCollection()->flatMap(
            fn($p) => $this->itemCodes($p->detailPo?->detail)
        )->unique()->values();
        [$cogMatches, $bomSampleMatches] = $this->bomLookup($codes);
        $isFactory = $this->isFactory($request);
        return view('pages.pengajuan_harga.directur_pages', compact(
            'pekerjaans',
            'cogMatches',
            'bomSampleMatches',
            'isFactory'
        ));
    }

    public function updateDirectur(Request $request, PengajuanHargaPekerjaan $pekerjaan)
    {
        abort_unless($this->isFactory($request), 403, 'Hanya akun factory@ yang dapat menyimpan Director Price.');
        $data = $request->validate([
            'director_price' => ['nullable', 'numeric', 'min:0'],
            'director_remark' => ['nullable', 'string'],
        ]);
        $old = $pekerjaan->director_price;
        $new = $data['director_price'] ?? null;
        $changed = ($old === null) !== ($new === null)
            || ($old !== null && $new !== null && round((float) $old, 2) !== round((float) $new, 2));
        $pekerjaan->director_price = $new;
        $pekerjaan->director_remark = $data['director_remark'] ?? null;
        $pekerjaan->closed = true; // Save Director otomatis menutup case.
        if ($changed)
            $pekerjaan->directur_date = now();
        $pekerjaan->save();
        return response()->json([
            'success' => true,
            'message' => 'Updated Director tersimpan.',
            'id' => $pekerjaan->id,
            'closed' => false,
            'directur_date' => $pekerjaan->directur_date?->format('d-m-y H:i'),
        ]);
    }
}
