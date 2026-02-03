<?php
namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // TODO:: login via web
    // app/Http/Middleware/RedirectIfAuthenticated.php
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/dashboard'); // atau route dashboard kamu
            }
        }

        return $next($request);
    }
    public function showLoginForm()
    {
        return view('pages.login.login ');
    }

    // Proses login
    public function loginWeb(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return view('pages.dashboard.dashboard');

        }

        // Jika gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // Logout
    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
   public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Email atau password salah',
        ], 401);
    }

    $request->session()->regenerate();

    $user = Auth::user();

    // ambil karyawan
    $karyawan = Karyawan::where('user_id', $user->id)->first();

    if (!$karyawan) {
        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
        ]);
    }

    // ambil divisi
    $divisi = Divisi::find($karyawan->divisi_id);

    $isQc = in_array($divisi->nama, [
        'QC RANGKA',
        'QC ANYAM',
    ]);

    return response()->json([
        'message' => 'Login berhasil',
        'user' => $user,
        'qc' => $isQc,
        'divisi' => $divisi->nama,
    ]);
}


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        // Jika token tidak valid / user null
        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized atau token tidak valid',
            ], 401); // HTTP 401 Unauthorized
        }

        $user->load(['karyawan.divisi']);

        $today = Carbon::today();

        $absenHariIni = $user->absens()
            ->whereDate('tanggal', $today)
            ->get();

        return response()->json([
            'user'  => $user,
            'absen' => $absenHariIni,
        ]);
    }

    public function locationCantor()
    {
        $officeLat = config('office.lat');
        $officeLng = config('office.lon');
        $radius    = config('office.radius'); // meter
        return response()->json([
            'success' => true,
            'data'    => [
                'lat'    => $officeLat,
                'lng'    => $officeLng,
                'radius' => $radius,
            ],
        ]);
    }

}
