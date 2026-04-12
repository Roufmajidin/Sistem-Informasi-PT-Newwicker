<?php
namespace App\Http\Controllers\Api;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user = Auth::user();

        // generate token (Sanctum)
        $token = $user->createToken('api-token')->plainTextToken;

        // default role
        $role       = 'USER';
        $isQc       = false;
        $namaDivisi = null;

        // ambil karyawan
        $karyawan = Karyawan::where('id', $user->karyawan_id)->first();

        if ($karyawan) {
            $divisi = Divisi::find($karyawan->divisi_id);

            $namaDivisi = strtoupper(trim($divisi?->nama ?? ''));

            if (in_array($namaDivisi, ['QC RANGKA', 'QC ANYAM'])) {
                $role = 'QC';
                $isQc = true;
            }
        }

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $user,
            'role'    => $role,
            'qc'      => $isQc,
            'divisi'  => $namaDivisi,
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

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized atau token tidak valid',
            ], 401);
        }

        $user->load(['karyawan.divisi']);

        $today = now()->toDateString();

        // ======================
        // ABSEN
        // ======================
        $absens = $user->absens()
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->get();

        $hasToday = $absens->where('tanggal', $today)->first();

        if (! $hasToday) {
            $dummy = [
                'id'          => null,
                'user_id'     => $user->id,
                'tanggal'     => $today,
                'jam_masuk'   => null,
                'jam_keluar'  => null,
                'keterangan'  => null,
                'created_at'  => null,
                'updated_at'  => null,
                'latitude'    => null,
                'longitude'   => null,
                'foto'        => null,
                'foto_keluar' => null,
                'latitude_k'  => null,
                'longitude_k' => null,
                'messages'    => null,
                'validate'    => null,
            ];

            $absens->prepend((object) $dummy);
        }

        // ======================
        // LEMBUR LIST (RIWAYAT)
        // ======================
        $lemburList = $user->lemburs()
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_masuk', 'desc')
            ->get();

        // ======================
        // LEMBUR TODAY
        // ======================
        $lemburToday = $lemburList
            ->where('tanggal', $today)
            ->first();

        if (! $lemburToday) {
            $lemburToday = (object) [
                'id'          => null,
                'user_id'     => $user->id,
                'tanggal'     => $today,
                'jam_masuk'   => null,
                'jam_keluar'  => null,
                'latitude'    => null,
                'longitude'   => null,
                'latitude_k'  => null,
                'longitude_k' => null,
                'foto'        => null,
                'foto_keluar' => null,
                'keterangan'  => null,
                'validate'    => null,
            ];
        }

        return response()->json([
            'user'         => $user,
            'absen'        => $absens->values(),
            'lembur'       => $lemburList->values(), // ✅ TAMBAHAN
            'lembur_today' => $lemburToday,
        ]);
    }
    public function getLemburSaya(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        $user = $request->user();

        $lembur = Lembur::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $lembur,
        ]);
    }
}
