<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Lembur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class AuthController extends Controller
{
    // neww
      public function login(Request $request)
    {
        DB::enableQueryLog();

        $start       = microtime(true);
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
        // Log::info('LOGIN_TIME', [
        //     'seconds' => microtime(true) - $start,
        // ]);

        // Log::info(DB::getQueryLog());
        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $user,
            'role'    => $role,
            'qc'      => $isQc,
            'divisi'  => $namaDivisi,
        ]);
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email'    => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     if (! Auth::attempt($request->only('email', 'password'))) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $user  = User::where('email', $request->email)->first();
    //     $token = $user->createToken('api-token')->plainTextToken;

    //     return response()->json([
    //         'token' => $token,
    //         'user'  => $user,
    //     ]);
    // }

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
    // public function absenMe(Request $request)
    // {
    //     $user = $request->user();

    //     if (! $user) {
    //         return response()->json([
    //             'message' => 'Unauthorized atau token tidak valid',
    //         ], 401);
    //     }

    //     $user->load(['karyawan.divisi']);

    //     $today = now()->toDateString();

    //     // Ambil semua absen user
    //     $absens = $user->absens()
    //         ->orderBy('tanggal', 'desc')
    //         ->orderBy('jam_masuk', 'desc')
    //         ->get();

    //     // Cek apakah ada absen hari ini
    //     $hasToday = $absens->where('tanggal', $today)->first();

    //     if (! $hasToday) {
    //         // Tambahkan absen dummy ke paling depan
    //         $dummy = [
    //             'id'          => null,
    //             'user_id'     => $user->id,
    //             'tanggal'     => $today,
    //             'jam_masuk'   => null,
    //             'jam_keluar'  => null,
    //             'keterangan'  => null,
    //             'created_at'  => null,
    //             'updated_at'  => null,
    //             'latitude'    => null,
    //             'longitude'   => null,
    //             'foto'        => null,
    //             'foto_keluar' => null,
    //             'latitude_k'  => null,
    //             'longitude_k' => null,
    //             'messages'    => null,
    //             'validate'    => null,
    //         ];

    //         $absens->prepend((object) $dummy);
    //     }

    //     return response()->json([
    //         'user'  => $user,
    //         'absen' => $absens->values(),
    //     ]);
    // }
    // NEW
//     public function absenMe(Request $request)
// {
//     $user = $request->user();

//     if (!$user) {
//         return response()->json([
//             'message' => 'Unauthorized atau token tidak valid',
//         ], 401);
//     }

//     $user->load(['karyawan.divisi']);

//     $today = now()->toDateString();

//     // =============================
//     // Ambil semua absen user
//     // =============================
//     $absens = $user->absens()
//         ->orderBy('tanggal', 'desc')
//         ->orderBy('jam_masuk', 'desc')
//         ->get();

//     // =============================
//     // Filter absen hari ini
//     // =============================
//     $absenHariIni = $absens->where('tanggal', $today);

//     $countHariIni = $absenHariIni->count();

//     // =============================
//     // Jika ada absen hari ini
//     // =============================
//     if ($countHariIni > 0) {

//         // Ambil record terakhir hari ini
//         $lastToday = $absenHariIni
//             ->sortByDesc('jam_masuk')
//             ->first();

//         // Pindahkan ke paling depan collection
//         $absens = $absens
//             ->reject(fn($item) => $item->id == $lastToday->id)
//             ->prepend($lastToday);
//     } else {

//         // =============================
//         // Jika belum ada absen hari ini → dummy
//         // =============================
//         $dummy = [
//             'id'          => null,
//             'user_id'     => $user->id,
//             'tanggal'     => $today,
//             'jam_masuk'   => null,
//             'jam_keluar'  => null,
//             'keterangan'  => null,
//             'created_at'  => null,
//             'updated_at'  => null,
//             'latitude'    => null,
//             'longitude'   => null,
//             'foto'        => null,
//             'foto_keluar' => null,
//             'latitude_k'  => null,
//             'longitude_k' => null,
//             'messages'    => null,
//             'validate'    => null,
//         ];

//         $absens->prepend((object) $dummy);
//     }

//     return response()->json([
//         'user'  => $user,
//         'absen' => $absens->values(),
//     ]);
// }
// new new

// public function absenMe(Request $request)
// {
//     $user = $request->user();

//     if (! $user) {
//         return response()->json([
//             'message' => 'Unauthorized atau token tidak valid',
//         ], 401);
//     }

//     $user->load(['karyawan.divisi']);

//     $today = now()->toDateString();

//     // ======================
//     // ABSEN
//     // ======================
//     $absens = $user->absens()
//         ->orderBy('tanggal', 'desc')
//         ->orderBy('jam_masuk', 'desc')
//         ->get();

//     $hasToday = $absens->where('tanggal', $today)->first();

//     if (! $hasToday) {
//         $dummy = [
//             'id'          => null,
//             'user_id'     => $user->id,
//             'tanggal'     => $today,
//             'jam_masuk'   => null,
//             'jam_keluar'  => null,
//             'keterangan'  => null,
//             'created_at'  => null,
//             'updated_at'  => null,
//             'latitude'    => null,
//             'longitude'   => null,
//             'foto'        => null,
//             'foto_keluar' => null,
//             'latitude_k'  => null,
//             'longitude_k' => null,
//             'messages'    => null,
//             'validate'    => null,
//         ];

//         $absens->prepend((object) $dummy);
//     }

//     // ======================
//     // LEMBUR LIST (RIWAYAT)
//     // ======================
//     $lemburList = $user->lemburs()
//         ->orderBy('tanggal', 'desc')
//         ->orderBy('jam_masuk', 'desc')
//         ->get();

//     // ======================
//     // LEMBUR TODAY
//     // ======================
//     $lemburToday = $lemburList
//         ->where('tanggal', $today)
//         ->first();

//     if (! $lemburToday) {
//         $lemburToday = (object) [
//             'id'          => null,
//             'user_id'     => $user->id,
//             'tanggal'     => $today,
//             'jam_masuk'   => null,
//             'jam_keluar'  => null,
//             'latitude'    => null,
//             'longitude'   => null,
//             'latitude_k'  => null,
//             'longitude_k' => null,
//             'foto'        => null,
//             'foto_keluar' => null,
//             'keterangan'  => null,
//             'validate'    => null,
//         ];
//     }

//     return response()->json([
//         'user'         => $user,
//         'absen'        => $absens->values(),
//         'lembur'       => $lemburList->values(), // ✅ TAMBAHAN
//         'lembur_today' => $lemburToday,
//     ]);
// }
 public function absenMe(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized atau token tidak valid',
            ], 401);
        }

        $user->load(['karyawan.divisi']);

        $today = $this->getBusinessDate($user);

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
        // ======================================================
// JIKA LEMBUR SUDAH SELESAI
// ======================================================
if (
    $hasToday &&
    $lemburToday &&
    !empty($lemburToday->jam_keluar)
) {

    // Hilangkan status Full H agar APK tidak
    // menganggap masih bisa lembur lagi.
   if ($hasToday && $lemburToday) {

    // Masih lembur
    if (empty($lemburToday->jam_keluar)) {

        $hasToday->keterangan =
            'Sedang Lembur sejak ' .
            substr($lemburToday->jam_masuk, 0, 5) .
            '. Silakan lakukan Absen Keluar Lembur.';
    }

    // Sudah selesai lembur
    else {

        $hasToday->keterangan =
            'Lembur selesai (' .
            substr($lemburToday->jam_masuk, 0, 5) .
            ' - ' .
            substr($lemburToday->jam_keluar, 0, 5) .
            '). \n Silakan ambil foto dan kirim untuk absensi hari ini.';
    }
}
}

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
$absens = $absens->map(function ($item) use ($hasToday) {

    if ($hasToday && $item->id == $hasToday->id) {
        return $hasToday;
    }

    return $item;
});
        return response()->json([
            'user'         => $user,
            'absen'        => $absens->values(),
            'lembur'       => $lemburList->values(), // ✅ TAMBAHAN
            'lembur_today' => $lemburToday,
        ]);
    }
  private function getBusinessDate(User $user)
{
    $now = now();

    // Setelah jam 07.00 selalu hari baru
    if ($now->hour >= 7) {
        return $now->toDateString();
    }

    // Cari lembur terakhir
    $lastLembur = Lembur::where('user_id', $user->id)
        ->orderByDesc('tanggal')
        ->orderByDesc('jam_masuk')
        ->first();

    if ($lastLembur) {

        // Jika lembur kemarin maka tetap tampilkan tanggal kemarin
        if ($lastLembur->tanggal != $now->toDateString()) {
            return $lastLembur->tanggal;
        }
    }

    return $now->toDateString();
}
}
