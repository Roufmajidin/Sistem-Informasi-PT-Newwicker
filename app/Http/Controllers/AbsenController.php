<?php
namespace App\Http\Controllers;

// use App\Exports\AbsenExport;

use App\Exports\AbsenExport;
use App\Models\Absen;
use App\Models\Izin;
use App\Models\IzinType;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

// https://www.google.com/maps/place/Bank+Mandiri+Plered+Cirebon/@-6.7000304,107.319629,9z/data=!4m10!1m2!2m1!1splered+bank+mandiri!3m6!1s0x2e6ee18ce7e2f81d:0x73dc20856ab130bd!8m2!3d-6.7074391!4d108.5157608!15   hNwbGVyZWQgYmFuayBtYW5kaXJpIgOIAQFaFSITcGxlcmVkIGJhbmsgbWFuZGlyaZIBBGJhbmuaASNDaFpEU1VoTk1HOW5TMFZKUTBGblNVTjZjV1p0ZDBsUkVBRaoBaAoNL2cvMTFiYzY5NGJ4bQoJL20vMDMzd2R5EAEqECIMYmFuayBtYW5kaXJpKCYyHxABIhsm3aUwAelTELsrApholuuW1s2pmjF2c6XQOL8yFxACIhNwbGVyZWQgYmFuayBtYW5kaXJp4AEA-gEECAAQOQ!16s%2Fg%2F1hc41rs3l?entry=ttu&g_ep=EgoyMDI1MDYxNy4wIKXMDSoASAFQAw%3D%3D
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

        // ==================== Upload foto opsional ====================
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absen_foto', 'public');
        }

        // ==================== Cek data absen hari ini ====================
        $absen = Absen::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // ==================== Belum ada absen hari ini → Absen Masuk ====================
        if (! $absen) {

            Absen::create([
                'user_id'    => $user->id,
                'tanggal'    => $today,
                'jam_masuk'  => $now->format('H:i:s'),
                'latitude'   => $userLat,
                'longitude'  => $userLng,
                'foto'       => $fotoPath,
                'keterangan' => 'Hadir',
            ]);

            return response()->json([
                'message' => 'Absen masuk tercatat pukul ' . $now->format('H:i'),
            ], 201);
        }

        // ==================== Sudah masuk tapi belum keluar → Absen Keluar ====================
        if ($absen->jam_masuk && ! $absen->jam_keluar) {

            $absen->update([
                'jam_keluar'  => $now->format('H:i:s'),
                'latitude_k'  => $userLat,
                'longitude_k' => $userLng,
                'foto_keluar' => $fotoPath,
                'keterangan'  => 'Full H',
            ]);

            return response()->json([
                'message' => 'Absen keluar berhasil dicatat pukul ' . $now->format('H:i'),
            ], 200);
        }

        // ==================== Sudah lengkap ====================
        return response()->json([
            'message' => 'Absen hari ini sudah lengkap',
        ], 200);
    }
    public function absenLembur(Request $request)
    {
        // ==================== Validasi Token ====================
        if (! $request->user()) {
            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

        $user  = $request->user();
        $today = now()->toDateString();
        $now   = now();

        // ==================== Lokasi kantor ====================
        $officeLat = config('office.lat');
        $officeLng = config('office.lon');
        $radius    = config('office.radius');

        $userLat = $request->latitude;
        $userLng = $request->longitude;

        if (! isset($userLat) || ! isset($userLng)) {
            return response()->json([
                'message' => 'Lokasi tidak terdeteksi',
            ], 400);
        }

        // ==================== EXCEPTION USER 182 ====================
        if ($user->id != 182) {

            $jarak = $this->distance($userLat, $userLng, $officeLat, $officeLng);

            if ($jarak > $radius) {
                return response()->json([
                    'message' => 'Anda di luar area kantor (' . round($jarak) . ' meter)',
                ], 403);
            }
        }

        // ==================== Upload foto ====================
        $fotoPath = null;

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('lembur_foto', 'public');
        }

        // ==================== Cek lembur hari ini ====================
        $lembur = Lembur::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // ==================== Masuk lembur ====================
        if (! $lembur) {

            Lembur::create([
                'user_id'    => $user->id,
                'tanggal'    => $today,
                'jam_masuk'  => $now->format('H:i:s'),
                'latitude'   => $userLat,
                'longitude'  => $userLng,
                'foto'       => $fotoPath,
                'keterangan' => 'Mulai Lembur',
            ]);

            return response()->json([
                'message' => 'Absen lembur masuk tercatat pukul ' . $now->format('H:i'),
            ], 201);
        }

        // ==================== Keluar lembur ====================
        if ($lembur->jam_masuk && ! $lembur->jam_keluar) {

            $lembur->update([
                'jam_keluar'  => $now->format('H:i:s'),
                'latitude_k'  => $userLat,
                'longitude_k' => $userLng,
                'foto_keluar' => $fotoPath,
                'keterangan'  => 'Selesai Lembur',
            ]);

            return response()->json([
                'message' => 'Absen lembur selesai pukul ' . $now->format('H:i'),
            ], 200);
        }

        // ==================== Sudah lengkap ====================
        return response()->json([
            'message' => 'Lembur hari ini sudah lengkap',
        ], 200);
    }
    public function ajukanIzin(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        $user = $request->user();

        // Validasi input
        $validated = $request->validate([
            'tanggal'        => 'required|string',
            'mulai_tanggal'  => 'required|date',
            'sampai_tanggal' => 'required|date',
            'messages'       => 'nullable|string|max:255',
            'keterangan'     => 'required|string|max:255',
            'file'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Konversi tanggal
        try {
            $tanggal = \Carbon\Carbon::createFromFormat('d-m-Y', $validated['tanggal'])
                ->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Gunakan dd-mm-yyyy',
            ], 422);
        }

        // Cek existing
        $existing = Absen::where('user_id', $user->id)
            ->whereDate('tanggal', $validated['mulai_tanggal'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah mengajukan izin pada tanggal tersebut',
                'data'    => $existing,
            ], 200);
        }

        // Upload file
        $filePath = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads/izin', $fileName, 'public');
        }

        DB::beginTransaction();

        try {

            // ==================== SIMPAN ABSEN ====================
            $absen = Absen::create([
                'user_id'    => $user->id,
                'tanggal'    => $tanggal,
                'jam_masuk'  => null,
                'jam_keluar' => null,
                'keterangan' => $validated['keterangan'],
                'messages'   => $validated['messages'] ?? null,
                'foto'       => $filePath,
                'status'     => 'pending',
            ]);

            // ==================== SIMPAN IZIN ====================
            $type = IzinType::whereRaw('LOWER(name) = ?', [strtolower($validated['keterangan'])])
                ->first();

            $a    = $type?->id;
            $izin = Izin::create([
                'user_id'        => $user->id,
                'type_id'        => $a,
                'tanggal'        => $validated['keterangan'],

                'mulai_tanggal'  => $validated['mulai_tanggal'],
                'sampai_tanggal' => $validated['sampai_tanggal'],
                'alasan'         => $validated['messages'] ?? null,
                'file'           => $filePath,
                'status'         => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Izin berhasil diajukan',
                'absen'   => $absen,
                'izin'    => $izin,
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Gagal mengajukan izin',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function mapTypeId($keterangan)
    {
        $type = IzinType::whereRaw('LOWER(name) = ?', [strtolower($keterangan)])
            ->first();

        return $type?->id;
    }

    public function izinSaya(Request $request)
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

        $user = $request->user();

        $data = Izin::with(['type'])
            ->where('user_id', $user->id)
            ->orderBy('mulai_tanggal', 'desc')
            ->get();

        return response()->json([
            'message' => 'Data izin berhasil diambil',
            'data'    => $data,
        ]);
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
    public function getTypes()
    {
        $types = IzinType::select('id', 'name', 'code')
            ->orderBy('id')
            ->get();

        return response()->json([
            'message' => 'List tipe izin',
            'data'    => $types,
        ]);
    }
}
