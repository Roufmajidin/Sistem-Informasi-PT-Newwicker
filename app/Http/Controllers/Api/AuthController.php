<?php
namespace App\Http\Controllers\Api;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user  = User::where('email', $request->email)->first();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated (token salah atau sudah logout)',
            ], 401);
        }

        $user->load(['karyawan.divisi']);

        $today = \Carbon\Carbon::today();

        $absenHariIni = $user->absens()
            ->whereDate('tanggal', $today)
            ->get();

        return response()->json([
            'user'  => $user,
            'absen' => $absenHariIni,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'Token tidak valid atau sudah logout',
                ], 401);
            }

            // hapus token yang sedang dipakai
            $user->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout berhasil']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat logout',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
    public function absenMe(Request $request)
    {
        $user = $request->user();

        // Jika token tidak valid / user null
        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized atau token tidak valid',
            ], 401); // HTTP 401 Unauthorized
        }

        $user->load(['karyawan.divisi']);

        $absens = $user->absens()
            ->orderBy('tanggal', 'desc')   // urutkan berdasarkan tanggal terbaru
            ->orderBy('jam_masuk', 'desc') // kalau tanggal sama, urutkan jam masuk
            ->get();

        return response()->json([
            'user'  => $user,
            'absen' => $absens,
        ]);
    }
}
