<?php
namespace App\Http\Controllers;

use App\Imports\ProductPameranImport;
use App\Models\Exhibition;
use App\Models\ProductPameran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    public function storeE(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        Exhibition::create([
            'name'   => $request->name,
            'year'   => $request->year,
            'active' => 0, // default non-aktif
        ]);

        return redirect()->back()->with('success', 'Exhibition berhasil ditambahkan');
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
    public function allEventConfig()
    {
        $ex = Exhibition::get();
        return view('pages.pameran.config', compact('ex'));
    }

    public function import(Request $request)
    {
        Log::info("🔥 MASUK CONTROLLER IMPORT");

        Log::info("🔥 File ada? : " . ($request->hasFile('file') ? 'YA' : 'TIDAK'));
        Log::info("🔥 exhibition_id = " . $request->exhibition_id);

        $request->validate([
            'file'          => 'required|mimes:xlsx,xls,csv',
            'exhibition_id' => 'required|exists:exhibitions,id',
        ]);

        try {

            Log::info("🚀 Mulai proses Excel::import...");

            Excel::import(new ProductPameranImport($request->exhibition_id), $request->file('file'));

            Log::info("✔ Import selesai tanpa error");

            return response()->json([
                'status'  => 'success',
                'message' => 'Data produk pameran berhasil diimport!',
            ]);
        } catch (\Exception $e) {

            Log::error("❌ ERROR CONTROLLER: " . $e->getMessage(), [
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png,gif,webp',
            // ❌ max:5000 dihapus
        ]);

        $paths = [];

        foreach ($request->file('images') as $image) {

            // Nama asli file digunakan sepenuhnya
            $originalName = $image->getClientOriginalName();
            $cleanName    = str_replace(' ', '_', $originalName);

            $folder    = storage_path('app/public/pameran/');
            $finalName = $cleanName;

            // Jika file dengan nama sama sudah ada → tambahkan counter (1), (2), ...
            $counter = 1;
            while (file_exists($folder . $finalName)) {
                $finalName = pathinfo($cleanName, PATHINFO_FILENAME)
                . "_($counter)."
                . $image->getClientOriginalExtension();

                $counter++;
            }

            // Simpan file
            $image->storeAs('pameran', $finalName, 'public');

            $paths[] = '/storage/pameran/' . $finalName;
        }

        return response()->json([
            'status' => 'success',
            'paths'  => $paths,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function getPameranData(Request $request)
    {
        $startTime = microtime(true);

        $activeExhibitions = Exhibition::where('active', 1)->get();
        $category          = $request->get('category'); // filter kategori opsional
        $page              = (int) $request->get('page', 1);
        $perPage           = (int) $request->get('per_page', 50);

        if ($activeExhibitions->isEmpty()) {
            return response()->json([
                'status'   => false,
                'message'  => 'Tidak ada exhibition aktif',
                'products' => [],
            ], 404);
        }

        // GET semua ID exhibition aktif
        $activeIds = $activeExhibitions->pluck('id')->toArray();

        $query      = ProductPameran::whereIn('exhibition_id', $activeIds);
        $totalCount = ProductPameran::whereIn('exhibition_id', $activeIds)
            ->when(! empty($category), fn($q) => $q->where('categories', 'LIKE', "%{$category}%"))
            ->count();

        if (! empty($category)) {
            $query->where('categories', 'LIKE', "%{$category}%");
        }

        $products = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        if (! empty($category)) {
            $products->appends(['category' => $category]);
        }

        // Transform data
        $data = $products->getCollection()->map(function ($p) use ($activeExhibitions) {
            $exhibition  = $activeExhibitions->firstWhere('id', $p->exhibition_id);
            $articleCode = trim($p->article_code);
            $photoPath   = "pameran/{$exhibition->name}/{$articleCode}.webp";
            $photoUrl    = asset("storage/{$photoPath}");

            return [
                'nr'                 => $p->id,
                'photo'              => $photoUrl,
                'article_code'       => $p->article_code,
                'name'               => $p->name,
                'categories'         => $p->categories,
                'remark'             => $p->remark,
                'item_dimension'     => ['w' => $p->item_w, 'd' => $p->item_d, 'h' => $p->item_h],
                'packing_dimension'  => ['w' => $p->packing_w, 'd' => $p->packing_d, 'h' => $p->packing_h],
                'size_of_set'        => ['set_2' => $p->set2, 'set_3' => $p->set3, 'set_4' => $p->set4, 'set_5' => $p->set5],
                'composition'        => $p->composition,
                'finishing'          => $p->finishing,
                'cbm'                => round((float) $p->cbm, 2),
                'loadability_20'     => round((float) $p->loadability_20, 0),
                'loadability_40'     => round((float) $p->loadability_40, 0),
                'loadability_40_hc'  => round((float) $p->loadability_40hc, 0),
                'price_item'         => (double) $p->fob_jakarta_in_usd,
                'fob_jakarta_in_usd' => (double) $p->fob_jakarta_in_usd,
                'exhibition_name'    => $exhibition->name,
            ];
        });

        $products->setCollection($data);

        $duration = microtime(true) - $startTime;

        return response()->json([
            'status'           => true,
            'message'          => 'Berhasil mengambil data produk',
            'products'         => $products,
            'duration_seconds' => round($duration, 3),
        ]);
    }
    public function downloadPameranJson(Request $request)
    {
        $startTime = microtime(true);

        $category          = $request->get('category'); // opsional
        $activeExhibitions = Exhibition::where('active', 1)->get();

        if ($activeExhibitions->isEmpty()) {
            return response()->json([
                'status'   => false,
                'message'  => 'Tidak ada exhibition aktif',
                'products' => [],
            ], 404);
        }

        $activeIds = $activeExhibitions->pluck('id')->toArray();

        $query = ProductPameran::whereIn('exhibition_id', $activeIds);
        if (! empty($category)) {
            $query->where('categories', 'LIKE', "%{$category}%");
        }

        $products = $query->orderBy('id', 'desc')->get();

        // Transform data
        $data = $products->map(function ($p) use ($activeExhibitions) {
            $exhibition  = $activeExhibitions->firstWhere('id', $p->exhibition_id);
            $articleCode = trim($p->article_code);
            $photoPath   = "pameran/{$exhibition->name}/{$articleCode}.webp";
            $photoUrl    = asset("storage/{$photoPath}");

            return [
                'nr'                 => $p->id,
                'photo'              => $photoUrl,
                'article_code'       => $p->article_code,
                'name'               => $p->name,
                'categories'         => $p->categories,
                'remark'             => $p->remark,
                'item_dimension'     => ['w' => $p->item_w, 'd' => $p->item_d, 'h' => $p->item_h],
                'packing_dimension'  => ['w' => $p->packing_w, 'd' => $p->packing_d, 'h' => $p->packing_h],
                'size_of_set'        => ['set_2' => $p->set2, 'set_3' => $p->set3, 'set_4' => $p->set4, 'set_5' => $p->set5],
                'composition'        => $p->composition,
                'finishing'          => $p->finishing,
                'cbm'                => round((float) $p->cbm, 2),
                'loadability_20'     => round((float) $p->loadability_20, 0),
                'loadability_40'     => round((float) $p->loadability_40, 0),
                'loadability_40_hc'  => round((float) $p->loadability_40hc, 0),
                'price_item'         => (double) $p->fob_jakarta_in_usd,
                'fob_jakarta_in_usd' => (double) $p->fob_jakarta_in_usd,
                'exhibition_name'    => $exhibition->name,
            ];
        });

        $json = [
            'status'           => true,
            'message'          => 'Berhasil mengambil data produk',
            'products'         => $data,
            'total'            => $data->count(),
            'duration_seconds' => round(microtime(true) - $startTime, 3),
        ];

        // Opsional: simpan sebagai file di server
        $fileName = 'pameran_all' . ($category ? "_$category" : '') . '.json';
        Storage::disk('public')->put(
            $fileName,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return response()->json($json, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function getCategories()
    {
        $active = Exhibition::where('active', 1)->first();
        if (! $active) {
            return response()->json([
                'status'     => false,
                'message'    => 'Tidak ada exhibition aktif',
                'categories' => [],
            ], 404);
        }

        // Ambil semua kategori unik
        $categories = ProductPameran::where('exhibition_id', $active->id)
            ->pluck('categories') // ambil kolom categories
            ->filter()            // buang null / empty
            ->unique()            // ambil unique
            ->values();           // reset index

        return response()->json([
            'status'     => true,
            'categories' => $categories,
        ]);
    }

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
