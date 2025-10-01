<?php
namespace App\Http\Controllers;

use App\Exports\AbsenExport;
use App\Models\Absen;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

// class AbsenController extends Controller
// {
//     public function absen(Request $request)
//     {
//         $request->validate([
//             'latitude'  => 'required|numeric',
//             'longitude' => 'required|numeric',
//             'foto'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
//         ]);

//         // Lokasi kantor (PT NewWicker)
//         $officeLatitude  = -6.7169786;
//         $officeLongitude = 108.4942703;
// // https://www.google.com/maps/place/PT.+NewWicker+Indonesia/@3,3a,75y,193.65h,79.02t/data=!3m7!1e1!3m5!1skhc3P3ofipAi4EcjfJ8cww!2e0!6shttps:%2F%2Fstreetviewpixels-pa.googleapis.com%2Fv1%2Fthumbnail%3Fcb_client%3Dmaps_sv.tactile%26w%3D900%26h%3D600%26pitch%3D10.980861744083313%26panoid%3Dkhc3P3ofipAi4EcjfJ8cww%26yaw%3D193.65404224941724!7i16384!8i8192!4m7!3m6!1s0x2e6ee1da6368bf61:0x4635ae075c78bf34!8m2!3d-6.7169786!4d108.4942703!10e5!16s%2Fg%2F11j1yk0jx6?entry=ttu&g_ep=EgoyMDI1MDYxNy4wIKXMDSoASAFQAw%3D%3D

// https://www.google.com/maps/place/Bank+Mandiri+Plered+Cirebon/@-6.7000304,107.319629,9z/data=!4m10!1m2!2m1!1splered+bank+mandiri!3m6!1s0x2e6ee18ce7e2f81d:0x73dc20856ab130bd!8m2!3d-6.7074391!4d108.5157608!15sChNwbGVyZWQgYmFuayBtYW5kaXJpIgOIAQFaFSITcGxlcmVkIGJhbmsgbWFuZGlyaZIBBGJhbmuaASNDaFpEU1VoTk1HOW5TMFZKUTBGblNVTjZjV1p0ZDBsUkVBRaoBaAoNL2cvMTFiYzY5NGJ4bQoJL20vMDMzd2R5EAEqECIMYmFuayBtYW5kaXJpKCYyHxABIhsm3aUwAelTELsrApholuuW1s2pmjF2c6XQOL8yFxACIhNwbGVyZWQgYmFuayBtYW5kaXJp4AEA-gEECAAQOQ!16s%2Fg%2F1hc41rs3l?entry=ttu&g_ep=EgoyMDI1MDYxNy4wIKXMDSoASAFQAw%3D%3D
// // Validasi lokasi: harus dalam 100 meter
//         if (! $this->isWithinRadius($request->latitude, $request->longitude, $officeLatitude, $officeLongitude, 0.1)) {
//             return response()->json([
//                 'message' => 'Lokasi Anda di luar jangkauan area kantor (maksimal 100 meter).',
//             ], 403);
//         }

//         $user  = $request->user();
//         $today = now()->toDateString();

//         $absen = Absen::where('user_id', $user->id)
//                       ->where('tanggal', $today)
//                       ->first();

//         $fotoPath = null;
//         if ($request->hasFile('foto')) {
//             $fotoPath = $request->file('foto')->store('absen_foto', 'public');
//         }

//         if (! $absen) {
//             // Absen masuk
//             Absen::create([
//                 'user_id'    => $user->id,
//                 'tanggal'    => $today,
//                 'jam_masuk'  => now()->format('H:i:s'),
//                 'latitude'   => $request->latitude,
//                 'longitude'  => $request->longitude,
//                 'foto'       => $fotoPath,
//                 'keterangan' => 'Hadir',
//             ]);

//             return response()->json(['message' => 'Absen masuk tercatat'], 201);
//         } else {
//             // Absen keluar: cek waktu
//             if (now()->format('H:i') < '17:00') {
//                 return response()->json([
//                     'message' => 'Belum bisa absen keluar. Minimal pukul 17:00.',
//                 ], 403);
//             }

//             $absen->update([
//                 'jam_keluar' => now()->format('H:i:s'),
//                 'latitude'   => $request->latitude,
//                 'longitude'  => $request->longitude,
//                 'foto'       => $fotoPath ?? $absen->foto,
//             ]);

//             return response()->json(['message' => 'Absen keluar tercatat'], 200);
//         }
//     }

//     private function isWithinRadius($lat1, $lon1, $lat2, $lon2, $radiusInKm = 0.1) // 100 meter
//     {
//         $earthRadius = 6371; // km
//         $dLat = deg2rad($lat2 - $lat1);
//         $dLon = deg2rad($lon2 - $lon1);

//         $a = sin($dLat / 2) * sin($dLat / 2) +
//             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
//             sin($dLon / 2) * sin($dLon / 2);

//         $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
//         $distance = $earthRadius * $c;

//         return $distance <= $radiusInKm;
//     }

class AbsenController extends Controller
{
    public function absen(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        $user  = $request->user();
        $today = now()->toDateString();
        $now   = now();

        // ==================== Cek waktu minimal absen masuk ====================
        $minTime = now()->setTime(7, 0, 0);
        if ($now->lt($minTime)) {
            return response()->json([
                'message' => 'Belum bisa absen. Absen dimulai pukul 07:00',
            ], 403);
        }

        // ==================== Lokasi kantor ====================
        $officeLat = config('office.lat');
        $officeLng = config('office.lon');
        $radius    = config('office.radius'); // meter

        $userLat = $request->latitude;
        $userLng = $request->longitude;

        if (! isset($userLat) || ! isset($userLng)) {
            return response()->json(['message' => 'Lokasi tidak terdeteksi'], 400);
        }

        $jarak = $this->distance($userLat, $userLng, $officeLat, $officeLng);
        if ($jarak > $radius) {
            return response()->json([
                'message' => 'Anda berada di luar area kantor (' . round($jarak) . ' meter). Absen ditolak.',
            ], 403);
        }

        // ==================== Cek data absen hari ini ====================
        $absen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($absen && $absen->jam_masuk && $absen->jam_keluar) {
            return response()->json([
                'message' => 'Absen hari ini sudah komplit',
            ], 200);
        }

// ==================== Waktu batas absen keluar ====================
        $cutOff = now()->setTime(17, 0, 0);

// ==================== Upload foto opsional ====================
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absen_foto', 'public');
        }

// ==================== Kalau belum ada absen hari ini ====================
        if (! $absen) {
            if ($now->gte($cutOff)) {
                // Sudah lewat jam 17:00 → catat jam keluar saja
                Absen::create([
                    'user_id'     => $user->id,
                    'tanggal'     => $today,
                    'jam_keluar'  => $now->format('H:i:s'),
                    'latitude'    => $userLat,
                    'longitude'   => $userLng,
                    'latitude_k'  => null,
                    'longitude_k' => null,
                    'foto_keluar' => $fotoPath,
                    'keterangan'  => 'Lupa Absen Masuk',
                ]);

                return response()->json([
                    'message' => 'Anda lupa absen masuk! Sistem otomatis mencatat jam keluar pukul ' . $now->format('H:i') . '.',
                ], 200);
            }

            // Masih sebelum jam 17:00 → catat absen masuk normal
            Absen::create([
                'user_id'     => $user->id,
                'tanggal'     => $today,
                'jam_masuk'   => $now->format('H:i:s'),
                'latitude'    => $userLat,
                'longitude'   => $userLng,
                'latitude_k'  => $userLat,
                'longitude_k' => $userLng,
                'foto'        => $fotoPath,
                'keterangan'  => 'Hadir',
            ]);

            return response()->json(['message' => 'Absen masuk tercatat'], 201);
        }

// ==================== Kalau sudah ada jam_masuk tapi belum ada jam_keluar ====================
        if ($absen->jam_masuk && ! $absen->jam_keluar) {
            if ($now->lt($cutOff)) {
                return response()->json([
                    'message' => 'Belum bisa absen keluar. Minimal pukul 17:00.',
                ], 403);
            }

            // Sudah >= 17:00 → bisa absen keluar
            $absen->update([
                'jam_keluar'  => $now->format('H:i:s'),
                'latitude'    => $userLat,
                'longitude'   => $userLng,
                'latitude_k'  => $userLat,
                'longitude_k' => $userLng,
                'foto_keluar' => $fotoPath,
                'keterangan'  => "Hadir",
            ]);

            return response()->json([
                'message' => 'Absen keluar berhasil dicatat pada ' . $now->format('H:i'),
            ], 200);
        }

    }

    public function ajukanIzin(Request $request)
    {
        $today = now()->toDateString();
        $now   = now();

        if (! $request->user()) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        $user = $request->user();

        // Cek apakah sudah ada izin hari ini
        $existing = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->where('keterangan', 'izin')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah mengajukan izin hari ini',
                'data'    => $existing,
            ], 200);
        }

        // Upload foto opsional
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('izin', 'public');
        }

        // Simpan data izin
        $izin = Absen::create([
            'user_id'    => $user->id,
            'tanggal'    => $today,
            'messages'   => $request->messages, // wajib diisi
            'foto'       => $fotoPath,
            'keterangan' => 'izin',
            'validate'   => 0, // langsung set 0
        ]);

        return response()->json([
            'message' => 'Izin berhasil diajukan',
            'data'    => $izin,
        ], 201);
    }

/**
 * Hitung jarak antara dua titik koordinat (meter)
 */
    protected function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function export(Request $request)
    {
                                        // Ambil range tanggal dari input
        $start = request('start_date'); // format Y-m-d
        $end   = request('end_date');   // format Y-m-d

        // Ambil data absen
        $absens = Absen::with('user')
            ->whereBetween('tanggal', [$start, $end])
            ->get();

        return Excel::download(new AbsenExport($start, $end), "absen_{$start}_sd_{$end}.xlsx");
    }
    public function validateIzin($id)
    {
        $absen = Absen::find($id);
        if (! $absen) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $absen->update([
            'validate' => 1,
        ]);

        return response()->json(['message' => 'Izin berhasil divalidasi'], 200);
    }
}
