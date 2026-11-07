<?php



namespace App\Http\Controllers;



use App\Models\BahanKategori;

use App\Models\DetailPo;

use App\Models\KategoriBahan;

use App\Models\PengajuanHarga;

use App\Models\PengajuanHargaDetail;

use App\Models\SubBahan;
use App\Models\Supplier;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;

use App\Models\MaterialPriceProd;

use App\Models\MaterialFinishingProd;



class PengajuanHargaController extends Controller
{





    public function searchSupplier(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2)
            return response()->json([]);
        return response()->json(Supplier::query()
            ->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($q) . '%'])
            ->orderBy('name')->limit(20)->get(['id', 'name']));
    }

    public function searchMaterial(Request $request)
    {

        $q = trim((string) $request->query('q', ''));



        if (mb_strlen($q) < 2) {

            return response()->json([]);

        }



        $materials = MaterialPriceProd::query()

            ->where('nama_material', 'like', "%{$q}%")

            ->orderBy('nama_material')

            ->limit(15)

            ->get(['id', 'nama_material', 'harga', 'satuan'])

            ->map(fn($row) => [

                'id' => $row->id,

                'source' => 'material',

                'name' => $row->nama_material,

                'harga' => $row->harga,

                'unit' => $row->satuan,

            ]);



        $finishings = MaterialFinishingProd::query()

            ->where('nama', 'like', "%{$q}%")

            ->orderBy('nama')

            ->limit(10)

            ->get(['id', 'nama'])

            ->map(fn($row) => [

                'id' => $row->id,

                'source' => 'finishing',

                'name' => $row->nama,

                'harga' => null,

                'unit' => null,

            ]);



        return response()->json(

            $materials->concat($finishings)

                ->values()

                ->all()

        );

    }




    public function index(Request $request)
    {
        $user = auth()->user();

        abort_unless($user, 403, 'Silakan login terlebih dahulu.');

        $email = strtolower(trim((string) $user->email));

        $isFactory = str_starts_with($email, 'factory@');

        $query = KategoriBahan::query()
            ->with([
                'pengajuanHarga.user',
                'kategori',
                'subBahan',
                'details',
                'pic',
                'approver',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER BERDASARKAN USER LOGIN
        |--------------------------------------------------------------------------
        |
        | Factory:
        | - Tidak melihat Draft
        | - Melihat Pending, Revision, Approved, Rejected
        |
        | Writer:
        | - Melihat Draft miliknya sendiri
        | - Melihat pengajuan yang sudah dikirim
        |
        */

        if ($isFactory) {

            $query->whereIn('status', [
                'pending',
                'revision',
                'approved',
                'rejected',
            ]);

        } else {

            $query->where(function ($builder) use ($user) {

                // Pengajuan yang sudah dikirim
                $builder->whereIn('status', [
                    'pending',
                    'revision',
                    'approved',
                    'rejected',
                ]);

                // ATAU Draft milik user login
                $builder->orWhere(function ($draft) use ($user) {

                    $draft->where('status', 'draft')
                        ->whereHas('pengajuanHarga', function ($header) use ($user) {

                            $header->where('user_id', $user->id);

                        });

                });

            });

        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH ARTICLE / ITEM / SUB BAHAN / SUPPLIER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('q')) {

            $q = trim((string) $request->input('q'));

            $query->where(function ($builder) use ($q) {

                $builder->whereHas('pengajuanHarga', function ($header) use ($q) {

                    $header->where(function ($search) use ($q) {

                        $search->where('article_nr', 'like', "%{$q}%")
                            ->orWhere('item_name', 'like', "%{$q}%")
                            ->orWhere('sub_name', 'like', "%{$q}%");

                    });

                });

                $builder->orWhereHas('subBahan', function ($sub) use ($q) {

                    $sub->where('name', 'like', "%{$q}%");

                });

                $builder->orWhereHas('kategori', function ($kategori) use ($q) {

                    $kategori->where('name', 'like', "%{$q}%");

                });

            });

        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kategori')) {

            $query->where(
                'kategori_id',
                $request->input('kategori')
            );

        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->input('status')
            );

        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $pengajuans = $query
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        $kategoriList = BahanKategori::query()
            ->orderBy('name')
            ->get();

        $subBahanList = SubBahan::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.pengajuan_harga.index',
            compact(
                'pengajuans',
                'kategoriList',
                'subBahanList'
            )
        );
    }




    /** Searches article number and product descriptions stored in detail_po.detail JSON. */

    public function searchArticle(Request $request)
    {

        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2)

            return response()->json([]);



        $result = [];

        // JSON keys vary between uploaded PO formats; chunk to avoid loading the entire table.

        DetailPo::query()->with('po:id,order_no,company_name')

            ->whereNotNull('detail')->orderBy('id')

            ->chunkById(300, function ($records) use ($q, &$result) {

                foreach ($records as $record) {

                    $d = $record->detail;

                    if (!is_array($d))

                        continue;

                    $article = $this->firstValue($d, [

                        'article_nr_',

                        'article_code',

                        'article_nr',

                        'article',

                        "buyer's_article_nr_",

                        'buyers_article_nr',

                        'buyer_article_nr',

                    ]);

                    $nw = $this->firstValue($d, ['nw_description', 'nw desc', 'nw_desc']);

                    $description = $this->firstValue($d, [

                        'description',

                        'buyer desc',

                        "buyer's_desc_",

                        'buyers_desc',

                        'buyer_desc',

                        'buyer_description',

                        'buyers',

                    ]);

                    $buyerArticle = $this->firstValue($d, ["buyer's_article_nr_", 'buyers_article_nr', 'buyer_article_nr']);

                    $haystack = implode(' ', array_filter([$article, $nw, $description, $buyerArticle]));

                    if ($haystack === '' || mb_stripos($haystack, $q) === false)

                        continue;

                    $item = $nw ?: $description ?: $article;

                    $photo = $this->firstValue($d, ['photo', 'image', 'picture']);

                    $result[] = [

                        'detail_po_id' => $record->id,

                        'po_id' => $record->po_id,

                        'po_number' => $record->po?->order_no,

                        'buyer' => $record->po?->company_name,

                        'article' => $article ?: $buyerArticle,

                        'buyer_article' => $buyerArticle,

                        'description' => $description,

                        'nw_description' => $nw,

                        'item_name' => $item,

                        'photo' => $photo,

                        'item_w' => $this->firstValue($d, ['item_w']),

                        'item_d' => $this->firstValue($d, ['item_d']),

                        'item_h' => $this->firstValue($d, ['item_h']),

                        'qty' => $this->firstValue($d, ['qty']),

                    ];

                    if (count($result) >= 20)

                        return false;

                }

            });

        return response()->json($result);

    }



    private function firstValue(array $data, array $keys): ?string
    {

        foreach ($keys as $key) {

            if (!isset($data[$key]) || !is_scalar($data[$key]))

                continue;

            $v = trim((string) $data[$key]);

            if ($v !== '')

                return $v;

        }

        return null;

    }



    public function store(Request $request)
    {

        $data = $request->validate([

            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'detail_po_id' => ['required', 'integer', 'exists:detail_po,id'],

            'item_name' => ['required', 'string', 'max:255'],

            'article_nr' => ['required', 'string', 'max:100'],

            'item_w' => ['nullable', 'string', 'max:30'],

            'item_d' => ['nullable', 'string', 'max:30'],

            'item_h' => ['nullable', 'string', 'max:30'],

            'kategori_id' => ['required', 'integer', 'exists:bahan_kategori,id'],

            'sub_bahan_id' => ['required', 'integer', 'exists:sub_bahan,id'],

            'sub_name' => ['required', 'string', 'max:255'],

            'remark' => ['nullable', 'string', 'max:5000'],

            'details' => ['required', 'array', 'min:1'],

            'details.*.nama_bahan' => ['required', 'string', 'max:255'],

            'details.*.qty' => ['required', 'numeric', 'gte:0'],

            'details.*.unit' => ['required', 'string', 'max:30'],

            'details.*.harga' => ['required', 'numeric', 'gte:0'],

            'details.*.name_sub' => ['nullable', 'string', 'max:150'],

        ]);

        $sub = SubBahan::findOrFail($data['sub_bahan_id']);

        if ((int) $sub->bahan_kategori_id !== (int) $data['kategori_id']) {

            throw ValidationException::withMessages(['sub_bahan_id' => 'Sub bahan tidak sesuai kategori.']);

        }

        $detailPo = DetailPo::with('po')->findOrFail($data['detail_po_id']);

        $json = $detailPo->detail;

        if (!is_array($json)) {

            throw ValidationException::withMessages(['detail_po_id' => 'Detail PO tidak valid.']);

        }

        $article = $this->firstValue($json, ['article_nr_', 'article_code', 'article_nr', 'article', "buyer's_article_nr_"]);

        if (!$article) {

            throw ValidationException::withMessages(['detail_po_id' => 'Article pada Detail PO kosong.']);

        }

        $name = trim($data['item_name']);

        $article = trim($data['article_nr']);

        $dimensions = implode('x', [

            trim($data['item_w'] ?? ''),

            trim($data['item_d'] ?? ''),

            trim($data['item_h'] ?? ''),

        ]);

        $photo = $this->firstValue($json, ['photo', 'image', 'picture']);

        $userId = auth()->id();

        if (!$userId)

            abort(403, 'Login diperlukan.');



        $uploadedPath = $request->hasFile('image_file')
            ? $request->file('image_file')->store('pengajuan-harga', 'public') : null;
        try {
            $created = DB::transaction(function () use ($data, $detailPo, $article, $name, $dimensions, $photo, $userId, $uploadedPath) {

                // Each Create generates a separate header; this preserves independent submissions.

                $header = PengajuanHarga::create([

                    'po_id' => $detailPo->po_id,

                    'article_nr' => $article,

                    'item_name' => $name,

                    'image' => $uploadedPath ? 'storage/' . $uploadedPath : $photo,

                    'wdh' => $dimensions,

                    'tgl_pengajuan' => now()->toDateString(),

                    'user_id' => $userId,

                    'remark' => $data['remark'] ?? null,
                    'sub_name' => $data['sub_name'],

                ]);

                $sum = 0;

                foreach ($data['details'] as $detail)

                    $sum += (float) $detail['qty'] * (float) $detail['harga'];

                $category = KategoriBahan::create([

                    'pengajuan_harga_id' => $header->id,

                    'kategori_id' => $data['kategori_id'],

                    'sub_bahan_id' => $data['sub_bahan_id'],


                    'harga' => round($sum, 2),

                    'pic_user_id' => $userId,

                    'remark' => $data['remark'] ?? null,

                ]);

                foreach ($data['details'] as $detail) {

                    PengajuanHargaDetail::create([

                        'pengajuan_harga_id' => $header->id,

                        'kategori_bahan_id' => $category->id,

                        'sub_bahan_id' => $data['sub_bahan_id'],

                        'nama_bahan' => $detail['nama_bahan'],

                        'qty' => $detail['qty'],

                        'unit' => $detail['unit'],

                        'harga' => $detail['harga'],

                        'name_sub' => $detail['name_sub'] ?? null,

                    ]);

                }

                return $category;

            });

        } catch (\Throwable $e) {
            if ($uploadedPath)
                Storage::disk('public')->delete($uploadedPath);
            throw $e;
        }
        return redirect()->route('pengajuan-harga.index')->with('success', "Draft pengajuan #{$created->id} berhasil disimpan.");

    }



    /** Pemilik boleh mengedit pengajuan yang belum approved. Edit mengembalikan status ke Draft. */
    public function update(Request $request, KategoriBahan $pengajuan)
    {
        $header = $pengajuan->pengajuanHarga;
        abort_unless($header, 404);
        abort_unless(auth()->check() && (int) $header->user_id === (int) auth()->id(), 403, 'Hanya pembuat pengajuan yang boleh mengedit.');

        $data = $request->validate([
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'item_name' => ['required', 'string', 'max:255'],
            'article_nr' => ['required', 'string', 'max:100'],
            'wdh' => ['nullable', 'string', 'max:100'],
            'sub_name' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'integer', 'exists:bahan_kategori,id'],
            'sub_bahan_id' => ['required', 'integer', 'exists:sub_bahan,id'],
            'remark' => ['nullable', 'string', 'max:5000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.nama_bahan' => ['required', 'string', 'max:255'],
            'details.*.qty' => ['required', 'numeric', 'gt:0'],
            'details.*.unit' => ['required', 'string', 'max:30'],
            'details.*.harga' => ['required', 'numeric', 'gte:0'],
        ]);
        $sub = SubBahan::findOrFail($data['sub_bahan_id']);
        if ((int) $sub->bahan_kategori_id !== (int) $data['kategori_id']) {
            throw ValidationException::withMessages(['sub_bahan_id' => 'Sub bahan tidak sesuai kategori.']);
        }

        $uploadedPath = $request->hasFile('image_file')
            ? $request->file('image_file')->store('pengajuan-harga', 'public') : null;
        try {
            DB::transaction(function () use ($pengajuan, $data, $uploadedPath) {
                $locked = KategoriBahan::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();
                if ($locked->status === 'approved') {
                    throw ValidationException::withMessages(['status' => 'Pengajuan yang sudah disetujui tidak bisa diedit.']);
                }
                $header = PengajuanHarga::whereKey($locked->pengajuan_harga_id)->lockForUpdate()->firstOrFail();
                // Mencegah perubahan header bersama yang dapat memengaruhi pengajuan kategori lain.
                if (KategoriBahan::where('pengajuan_harga_id', $header->id)->whereKeyNot($locked->id)->exists()) {
                    throw ValidationException::withMessages(['status' => 'Header digunakan oleh pengajuan lain; edit header harus dipisahkan.']);
                }
                $header->update([
                    'item_name' => trim($data['item_name']),
                    'article_nr' => trim($data['article_nr']),
                    'wdh' => $data['wdh'] ?? null,
                    'sub_name' => trim($data['sub_name']),
                    ...($uploadedPath ? ['image' => 'storage/' . $uploadedPath] : []),
                    'remark' => $data['remark'] ?? null,
                ]);
                $total = collect($data['details'])->sum(fn($d) => (float) $d['qty'] * (float) $d['harga']);
                $locked->kategori_id = $data['kategori_id'];
                $locked->sub_bahan_id = $data['sub_bahan_id'];
                $locked->harga = round($total, 2);
                $locked->remark = $data['remark'] ?? null;
                $locked->status = 'draft';
                $locked->submitted_at = null;
                $locked->approved_by = null;
                $locked->approved_at = null;
                $locked->approved_total = null;
                $locked->save();
                // Ganti seluruh rincian pada kategori ini; rincian kategori lain tidak disentuh.
                PengajuanHargaDetail::where('kategori_bahan_id', $locked->id)->delete();
                foreach ($data['details'] as $d) {
                    PengajuanHargaDetail::create([
                        'pengajuan_harga_id' => $header->id,
                        'kategori_bahan_id' => $locked->id,
                        'sub_bahan_id' => $data['sub_bahan_id'],
                        'nama_bahan' => $d['nama_bahan'],
                        'qty' => $d['qty'],
                        'unit' => $d['unit'],
                        'harga' => $d['harga'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($uploadedPath)
                Storage::disk('public')->delete($uploadedPath);
            throw $e;
        }
        return redirect()->route('pengajuan-harga.index')->with('success', 'Perubahan berhasil disimpan sebagai Draft. Kirim ulang untuk approval.');
    }

    public function submit(KategoriBahan $pengajuan)
    {

        if (!auth()->check())

            abort(403);

        if ($pengajuan->status !== 'draft') {

            return back()->withErrors(['status' => 'Hanya draft yang dapat diajukan.']);

        }

        if ((int) $pengajuan->pengajuanHarga?->user_id !== (int) auth()->id())

            abort(403);

        if (!$pengajuan->details()->exists())

            return back()->withErrors(['details' => 'Rincian biaya masih kosong.']);

        $pengajuan->status = 'pending';

        $pengajuan->submitted_at = now();

        $pengajuan->save();

        return back()->with('success', 'Pengajuan dikirim untuk approval.');

    }



    /** Only factory@... accounts may approve negotiated prices. */
    private function isFactoryApprover(): bool
    {
        $email = strtolower(trim((string) auth()->user()?->email));
        return str_starts_with($email, 'factory@');
    }

    public function approve(Request $request, KategoriBahan $pengajuan)
    {
        abort_unless($this->isFactoryApprover(), 403, 'Hanya akun factory@ yang dapat menyetujui harga.');
        $data = $request->validate([
            'approve_negosiasi_harga' => ['required', 'numeric', 'gte:0'],
            'remark_2' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($pengajuan, $data) {
            $locked = KategoriBahan::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();
            if (!in_array($locked->status, ['pending', 'revision'], true)) {
                throw ValidationException::withMessages(['status' => 'Hanya status Pending/Revision yang dapat disetujui.']);
            }
            $locked->status = 'approved';
            $locked->approved_by = auth()->id();
            $locked->approved_at = now();
            $locked->approved_total = $data['approve_negosiasi_harga'];
            // Catatan pengaju tidak boleh ditimpa oleh approver.
            $header = PengajuanHarga::whereKey($locked->pengajuan_harga_id)->lockForUpdate()->firstOrFail();
            $header->remark_2 = $data['remark_2'] ?? null;
            $header->save();
            $locked->save();
        });
        return redirect()->route('pengajuan-harga.index')->with('success', 'Harga negosiasi berhasil disetujui.');
    }

    public function destroy(KategoriBahan $pengajuan)
    {
        $header = $pengajuan->pengajuanHarga;
        $ownerId = (int) $header?->user_id;
        $isOwner = auth()->check() && $ownerId === (int) auth()->id();
        abort_unless($isOwner || $this->isFactoryApprover(), 403, 'Tidak memiliki izin menghapus pengajuan.');
        abort_if($pengajuan->status === 'approved', 403, 'Pengajuan yang sudah disetujui tidak dapat dihapus.');

        DB::transaction(function () use ($pengajuan) {
            $locked = KategoriBahan::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status === 'approved', 403, 'Pengajuan yang sudah disetujui tidak dapat dihapus.');
            $headerId = $locked->pengajuan_harga_id;
            PengajuanHargaDetail::where('kategori_bahan_id', $locked->id)->delete();
            $locked->delete();
            if ($headerId && !KategoriBahan::where('pengajuan_harga_id', $headerId)->exists()) {
                // Defensive: do not remove header if other details still refer to it.
                if (!PengajuanHargaDetail::where('pengajuan_harga_id', $headerId)->exists()) {
                    PengajuanHarga::whereKey($headerId)->delete();
                }
            }
        });
        return redirect()->route('pengajuan-harga.index')->with('success', 'Pengajuan berhasil dihapus.');
    }

    public function storeMasterKategori(Request $request)
    {

        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('bahan_kategori', 'name')]]);

        $row = BahanKategori::create($data);

        return response()->json(['data' => $row->only('id', 'name')], 201);

    }



    public function updateMasterKategori(Request $request, BahanKategori $kategori)
    {

        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('bahan_kategori', 'name')->ignore($kategori->id)]]);

        $kategori->update($data);

        return response()->json(['data' => $kategori->only('id', 'name')]);

    }



    public function storeMasterSub(Request $request)
    {

        $data = $request->validate([

            'bahan_kategori_id' => ['required', 'integer', 'exists:bahan_kategori,id'],

            'name' => ['required', 'string', 'max:255', Rule::unique('sub_bahan', 'name')->where('bahan_kategori_id', $request->input('bahan_kategori_id'))],

        ]);

        $row = SubBahan::create($data);

        return response()->json(['data' => $row->only('id', 'name', 'bahan_kategori_id')], 201);

    }



    public function updateMasterSub(Request $request, SubBahan $sub)
    {

        $data = $request->validate([

            'name' => ['required', 'string', 'max:255', Rule::unique('sub_bahan', 'name')->where('bahan_kategori_id', $sub->bahan_kategori_id)->ignore($sub->id)],

        ]);

        $sub->update($data);

        return response()->json(['data' => $sub->only('id', 'name', 'bahan_kategori_id')]);

    }

}
