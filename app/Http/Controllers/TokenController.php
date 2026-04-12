<?php
namespace App\Http\Controllers;

use App\Models\Exhibition;
use App\Models\ProductPameran;
use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function catalogue()
    {

        /* =========================
       CEK TOKEN SESSION
    ========================= */

        $tokenValue = session('catalogue_token');

        if ($tokenValue) {

            $token = Token::where('token', $tokenValue)->first();

            if (! $token) {

                session()->forget(['catalogue_access', 'catalogue_token']);

            } else {

                $expired = Carbon::parse($token->created_at)
                    ->addMinutes($token->duration);

                if (now()->greaterThan($expired)) {

                    session()->forget(['catalogue_access', 'catalogue_token']);

                }

            }

        }

        /* =========================
       AMBIL EXHIBITION
    ========================= */

        $activeExhibitions = Exhibition::where('active', 1)->get();

        if ($activeExhibitions->isEmpty()) {
            return response()->json([
                'status'   => false,
                'message'  => 'Tidak ada exhibition aktif',
                'products' => [],
            ], 404);
        }

        $activeIds = $activeExhibitions->pluck('id')->toArray();

        $products = ProductPameran::whereIn('exhibition_id', $activeIds)
            ->orderBy('id', 'desc')
            ->get();

        $activeExhibition = $activeExhibitions->first();

        $nm = $activeExhibition->name;

        return view('pages.pameran.catalogue', compact('nm'));
    }
    public function getcatalogue()
    {
        $activeExhibitions = Exhibition::where('active', 1)->get();

        if ($activeExhibitions->isEmpty()) {
            return response()->json([
                'status'   => false,
                'message'  => 'Tidak ada exhibition aktif',
                'products' => [],
            ], 404);
        }

        $activeIds = $activeExhibitions->pluck('id')->toArray();

        // CACHE PRODUK 60 DETIK
        $products = Cache::remember('catalogue_products', 60, function () use ($activeIds) {
            return ProductPameran::whereIn('exhibition_id', $activeIds)
                ->orderBy('id', 'desc')
                ->limit(60) // supaya tidak terlalu berat
                ->get();
        });

        return response()->json([
            'status'   => true,
            'message'  => 'Berhasil mengambil data produk',
            'products' => $products,
        ]);
    }

    public function checkToken(Request $request)
    {
        $token = Token::where('token', $request->token)->first();

        /* =========================
       TOKEN TIDAK ADA
    ========================= */

        if (! $token) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak ditemukan',
            ]);
        }

        /* =========================
       CEK CREATED_AT NULL
    ========================= */

        if (! $token->created_at) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak valid',
            ]);
        }

        /* =========================
       HITUNG EXPIRED
    ========================= */

        $expired = Carbon::parse($token->created_at)
            ->addMinutes((int) $token->duration);

        if (now()->greaterThan($expired)) {

            session()->forget([
                'catalogue_access',
                'catalogue_token',
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Token expired',
            ]);
        }

        /* =========================
       SIMPAN SESSION
    ========================= */

        session([
            'catalogue_access' => true,
            'catalogue_token'  => $token->token,
        ]);

        /* =========================
       CEK DATA VISITOR
    ========================= */

        if ($token->name || $token->company_name || $token->email) {

            return response()->json([
                'status' => true,
                'direct' => true,
            ]);
        }

        return response()->json([
            'status'   => true,
            'direct'   => false,
            'token_id' => $token->id,
        ]);
    }
    public function saveVisitor(Request $request)
    {

        $token = Token::find($request->token_id);

        $token->update([
            'name'         => $request->name,
            'company_name' => $request->company,
            'email'        => $request->email,
            'used'         => true,
        ]);

        session(['catalogue_access' => true]);

        return response()->json([
            'status' => true,
        ]);

    }
    public function tokenPage()
    {
        //
        $token = Token::all();
        return view('pages.token.index', compact('token'));
    }
    public function generateToken(Request $request)
    {
        $total = $request->total;

        for ($i = 0; $i < $total; $i++) {

            $token = strtoupper(Str::random(6));

            Token::create([
                'token'    => $token,
                'duration' => 120,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
    public function list()
    {
        $tokens = Token::orderBy('id', 'desc')->get();

        return response()->json($tokens);
    }
  public function updateToken(Request $request)
{

    $token = Token::find($request->id);

    if (!$token) {
        return response()->json([
            'status'  => false,
            'message' => 'Token tidak ditemukan',
        ]);
    }

    /* =========================
       FIELD YANG BOLEH DIUPDATE
    ========================= */

    $allowedFields = [
        'duration',
        'name',
        'company_name',
        'email',
    ];

    if (!in_array($request->field, $allowedFields)) {
        return response()->json([
            'status'  => false,
            'message' => 'Field tidak diizinkan',
        ]);
    }

    $value = trim($request->value);

    /* =========================
       PARSE DURATION
    ========================= */

    if ($request->field == 'duration') {

        preg_match('/(\d+)\s*([DMH]?)/i', $value, $match);

        $number = isset($match[1]) ? (int)$match[1] : 0;
        $unit   = strtoupper($match[2] ?? 'M');

        if ($unit == 'D') {
            $minutes = $number * 1440;
        } elseif ($unit == 'H') {
            $minutes = $number * 60;
        } else {
            $minutes = $number;
        }

        $token->duration = $minutes;

        /* =========================
           HITUNG EXPIRED
        ========================= */

        $startTime = $token->created_at;

        $token->expired_at = Carbon::parse($startTime)
            ->addMinutes($minutes);

    } else {

        $token->{$request->field} = $value;

    }

    $token->save();

    return response()->json([
        'status' => true,
    ]);

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

}
