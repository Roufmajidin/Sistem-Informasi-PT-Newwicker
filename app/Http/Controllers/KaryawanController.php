<?php
namespace App\Http\Controllers;

use App\Imports\KaryawanImport;
use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $karyawan = Karyawan::get();
        return view('pages.karyawan.karyawan', compact('karyawan'));
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
        $validated = $request->validate([
            'nama_lengkap.*'      => 'required|string|max:255',
            'nik.*'               => 'required|string|max:100|unique:karyawans,nik',
            'jenis_kelamin.*'     => 'nullable|string|in:L,P',
            'ttl.*'               => 'nullable|string|max:255',
            'alamat.*'            => 'nullable|string|max:255',
            'status_perkawinan.*' => 'nullable|string|max:100',
            'divisi_id.*'         => 'nullable|integer|exists:divisis,id',
            'status.*'            => 'nullable|string|max:100',
            'lokasi.*'            => 'nullable|string|max:255',
            'tanggal_join.*'      => 'nullable|date',
            'photo.*'             => 'nullable|image|max:2048',
        ]);

        $dataCount = count($request->nama_lengkap);

        for ($i = 0; $i < $dataCount; $i++) {
            $namaLengkap = trim($request->nama_lengkap[$i]);
            $nik         = trim($request->nik[$i]);

            // Pisah nama depan dan belakang
            $parts       = explode(' ', $namaLengkap);
            $firstName   = strtolower($parts[0]);                                       // Rayen
            $lastInitial = isset($parts[1]) ? strtolower(substr($parts[1], 0, 1)) : ''; // a

            // Email dengan format: firstname + lastInitial + @gmail.com
            $emailBase = $firstName . $lastInitial;
            $email     = $emailBase . '@gmail.com';

            // Kalau email sudah ada → tambahkan angka
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = $emailBase . $counter . '@gmail.com';
                $counter++;
            }

            // Password default = nama depan
            $password = Hash::make($firstName);
            // dd($request->divisi_id[$i]);
            // === Simpan Karyawan ===
            $karyawan                    = new Karyawan();
            $karyawan->nama_lengkap      = $namaLengkap;
            $karyawan->nik               = $nik;
            $karyawan->jenis_kelamin     = $request->jenis_kelamin[$i] ?? null;
            $karyawan->tempat            = $request->ttl[$i] ?? null;
            $karyawan->alamat            = $request->alamat[$i] ?? null;
            $karyawan->status_perkawinan = $request->status_perkawinan[$i] ?? null;
            $karyawan->divisi_id         = ! empty($request->divisi_id[$i]) ? (int) $request->divisi_id[$i] : null;
            $karyawan->status            = $request->status[$i] ?? null;
            $karyawan->lokasi            = $request->lokasi[$i] ?? null;
            $karyawan->tanggal_join      = $request->tanggal_join[$i] ?? null;
            $karyawan->status            = $request->status[$i] ?? null;

            // Upload photo kalau ada
            if ($request->hasFile("photo.$i")) {
                $file     = $request->file("photo.$i");
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/karyawan'), $filename);
                // $karyawan->photo = $filename;
            }

            $karyawan->save();

            // === Simpan User baru ===
            $user              = new User();
            $user->name        = $namaLengkap;
            $user->karyawan_id = $karyawan->id;
            $user->email       = $email;
            $user->password    = $password;
            $user->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data baru berhasil ditambahkan!',
        ]);
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

    public function import(Request $request)
    {
        try {
            $rows = Excel::toArray(new KaryawanImport, $request->file('file'));
            $data = [];

            foreach ($rows[0] as $i => $row) {
                if ($i === 0) {
                    continue;
                }
                // skip header
                $tanggal = Date::excelToDateTimeObject($row[11])->format('Y-m-d');

                $data[] = [
                    'nama_lengkap'         => $row[1],
                    'nik'                  => $row[2],
                    'jenis_kelamin'        => $row[3],
                    'tempat_tanggal_lahir' => $row[4],
                    'alamat'               => $row[5],
                    'status_perkawinan'    => $row[6] ?? $row[7],
                    'divisi'               => $row[8],
                    'status_karyawan'      => $row[9],
                    'lokasi'               => $row[10],
                    'tanggal_join'         => $row[11] == null ? '' : $tanggal,
                    'user_id'              => $row[12],
                ];
            }
            // Log::info('Import triggered', $data);

            return response()->json([
                'success' => true,
                'rows'    => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function checkExistingNames(Request $request)
    {
        $names = $request->input('names', []);
        $found = [];

        foreach ($names as $name) {
            if (! $name) {
                continue;
            }

            $matching = Karyawan::where('nama_lengkap', 'like', '%' . $name . '%')->pluck('nama_lengkap');

            foreach ($matching as $match) {
                $found[] = $match;
            }
        }

        return response()->json($found);
    }

    public function bulkSave(Request $request)
    {
        $rows     = $request->input('rows', []);
        $inserted = [];
        $updated  = [];

        foreach ($rows as $index => $row) {
            // Lewati baris pertama (header) atau baris kosong
            if ($index === 0 || empty($row['nama_lengkap']) || empty($row['nik'])) {
                continue;
            }

            // --- Pisahkan tempat & tanggal lahir ---
            $tempat_lahir  = null;
            $tanggal_lahir = null;
            if (! empty($row['tempat_tanggal_lahir']) && str_contains($row['tempat_tanggal_lahir'], ',')) {
                [$tempat_lahir, $tanggal_lahir] = array_map('trim', explode(',', $row['tempat_tanggal_lahir'], 2));
                try {
                    $tanggal_lahir = \Carbon\Carbon::parse($tanggal_lahir)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal_lahir = null;
                }
            }

            // --- Format tanggal join ---
            $tanggal_join = null;
            if (! empty($row['tanggal_join'])) {
                if (is_numeric($row['tanggal_join'])) {
                    $tanggal_join = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_join'])->format('Y-m-d');
                } else {
                    try {
                        $tanggal_join = \Carbon\Carbon::parse($row['tanggal_join'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tanggal_join = null;
                    }
                }
            }

            // --- Cari divisi id ---
            $divisiId = null;
            if (! empty($row['divisi'])) {
                $divisi   = \App\Models\Divisi::where('nama', trim($row['divisi']))->first();
                $divisiId = $divisi?->id;
            }

            // --- Insert / Update Karyawan ---
            $karyawan = \App\Models\Karyawan::updateOrCreate(
                ['nik' => $row['nik']],
                [
                    'nama_lengkap'      => $row['nama_lengkap'],
                    'jenis_kelamin'     => $row['jenis_kelamin'] ?? null,
                    'tempat'            => $tempat_lahir,
                    'tanggal_lahir'     => $tanggal_lahir,
                    'alamat'            => $row['alamat'] ?? null,
                    'status_perkawinan' => $row['status_perkawinan'] ?? null,
                    'divisi_id'         => $divisiId,
                    'status'            => $row['status_karyawan'] ?? null,
                    'lokasi'            => $row['lokasi'] ?? null,
                    'tanggal_join'      => $tanggal_join,
                ]
            );

            if ($karyawan->wasRecentlyCreated) {
                $inserted[] = $karyawan;
            } else {
                $updated[] = $karyawan;
            }

            // --- Handle User ---
            $namaParts    = explode(' ', trim($karyawan->nama_lengkap));
            $namaDepan    = strtolower($namaParts[0]);
            $namaBelakang = isset($namaParts[1]) ? strtolower(substr($namaParts[1], 0, 1)) : '';

            $baseEmail = $namaDepan . $namaBelakang . '@gmail.com';
            $email     = $baseEmail;
            $counter   = 1;

            // Cari email unik
            while (\App\Models\User::where('email', $email)->exists()) {
                $email = strtolower($namaDepan) . $namaBelakang . '@gmail.com';
                $counter++;
            }
            // Cek apakah sudah ada user untuk karyawan ini
            $user = \App\Models\User::where('karyawan_id', $karyawan->id)->first();

            if ($user) {
                $user->update([
                    'name'     => $karyawan->nama_lengkap,
                    'email'    => $email, // ganti email hanya kalau perlu
                    'password' => bcrypt($namaDepan),
                ]);
            } else {
                // insert baru
                \App\Models\User::create([
                    'name'        => $karyawan->nama_lengkap,
                    'email'       => $email,
                    'password'    => bcrypt($namaDepan),
                    'karyawan_id' => $karyawan->id,
                ]);
            }
        }

        return response()->json([
            'success'       => true,
            'inserted'      => count($inserted),
            'updated'       => count($updated),
            'updated_niks'  => array_column($updated, 'nik'),
            'inserted_niks' => array_column($inserted, 'nik'),
        ]);
    }

    public function scan()
    {
        $officeLat = config('office.lat');
        $officeLng = config('office.lon');
        $radius    = config('office.radius');
        $riwayat   = Absen::where('user_id', auth()->id())
            ->whereDate('tanggal', now()->toDateString())
            ->get();

        return view('pages.karyawan.cam', compact('riwayat', 'officeLat', 'officeLng', 'radius'));
    }
     public function riwayat(Request $request)
    {
        $bulanInput = $request->get('bulan', null);
        $tahunInput = $request->get('tahun', now()->year);

        $tahun = is_numeric($tahunInput) ? (int) $tahunInput : now()->year;

        $bulan = $this->normalizeMonth($bulanInput, $tahun);

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }

        $riwayat = Absen::where('user_id', auth()->id())
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->get();

        return view('pages.karyawan.history-absen', compact('riwayat', 'bulan', 'tahun'));
    }


    protected function normalizeMonth($bulanInput, $tahun = null)
    {
        if (is_null($bulanInput) || $bulanInput === '') {
            return (int) now()->month;
        }

        if (is_numeric($bulanInput)) {
            return (int) $bulanInput;
        }

        $s = trim(mb_strtolower((string)$bulanInput));

        $map = [
            'january'   => 1, 'february' => 2, 'march'    => 3, 'april'    => 4,
            'may'       => 5, 'june'     => 6, 'july'     => 7, 'august'   => 8,
            'september' => 9, 'october'  => 10, 'november' => 11, 'december' => 12,
            'januari'   => 1, 'februari' => 2, 'maret'    => 3, 'april'    => 4,
            'mei'       => 5, 'juni'     => 6, 'juli'     => 7, 'agustus'  => 8,
            'september' => 9, 'oktober'  => 10, 'november' => 11, 'desember' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8,
            'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12, 'mei' => 5,
        ];

        if (isset($map[$s])) {
            return $map[$s];
        }

        try {
            $y = $tahun ?: now()->year;
            $dt = Carbon::parse("1 {$bulanInput} {$y}");
            return (int) $dt->month;
        } catch (\Throwable $e) {
            // fallback ke bulan sekarang
            return (int) now()->month;
        }
    }

    // ... method lain ...


    public function storeAbsen(Request $request)
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

        // ==================== Jalankan dalam transaksi aman ====================
        return DB::transaction(function () use ($absen, $user, $today, $now, $cutOff, $userLat, $userLng, $fotoPath) {

            // Ambil ulang untuk memastikan tidak ada perubahan dalam transaksi
            $absen = Absen::lockForUpdate()
                ->where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();

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

                    'latitude'  => $userLat,
                    'longitude' => $userLng,
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
                    'latitude_k'  => $userLat,
                    'longitude_k' => $userLng,
                    'foto_keluar' => $fotoPath,
                    'keterangan'  => "Hadir",
                ]);

                return response()->json([
                    'message' => 'Absen keluar berhasil dicatat pada ' . $now->format('H:i'),
                ], 200);
            }

            return response()->json(['message' => 'Data absen tidak valid'], 400);
        });
    }

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
    public function login()
    {
        return view('pages.karyawan.login');
    }
    public function updateAbsen(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status'  => 'required|in:izin,cuti,sakit',
        ]);

        $absen = Absen::firstOrCreate([
            'user_id' => $request->user_id,
            'tanggal' => $request->tanggal,
        ]);

        $absen->keterangan = $request->status;
        $absen->save();

        return redirect()->back()->with('success', 'Status absen diperbarui.');
    }

    public function new ()
    {
        //

        // $karyawans = User::with(['absens','karyawan'])->get();
        // $today = Carbon::today()->toDateString();
        $today = '2025-06-26';

        $karyawans = User::with(['absens' => function ($query) use ($today) {
            $query->whereDate('tanggal', $today);
        }, 'karyawan'])->get();

        // dd($karyawans);
        return view('pages.karyawan.absens', compact('karyawans'));
    }
    public function absenkaryawan()
    {
        $today       = Carbon::now();
        $year        = $today->year;
        $month       = $today->month;
        $daysInMonth = $today->daysInMonth;

        $karyawans = User::with(['absens' => function ($q) use ($year, $month) {
            $q->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month);
        }])->get();
        // dd(env('OFFICE_LAT'), env('OFFICE_LON'), env('OFFICE_RADIUS'));
        // dd(config('office'));

        return view('pages.karyawan.absen', compact('karyawans', 'month', 'year', 'daysInMonth'));
    }
    public function izinKaryawan(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);
        $date  = $request->get('date'); // opsional

        // Query izin karyawan
        $query = Absen::with('user')
            ->where('keterangan', 'izin')
            ->when($month, function ($q) use ($month) {
                return $q->whereMonth('tanggal', $month);
            })
            ->when($year, function ($q) use ($year) {
                return $q->whereYear('tanggal', $year);
            })
            ->when($date, function ($q) use ($date) {
                return $q->whereDate('tanggal', $date);
            });

        $absens = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        dd($absens);
        return view('pages.karyawan.izin', [
            'absens' => $absens,
            'month'  => $month,
            'year'   => $year,
            'date'   => $date,
        ]);
    }

    public function filter(Request $request)
    {
        try {
            $month = $request->month;
            $year  = $request->year;
            $date  = $request->date ?? now()->toDateString();

            // Ambil data absensi + relasi karyawan
            $absens = Absen::with('user')
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->when($date, fn($q) => $q->whereDate('tanggal', $date))
                ->get();
            // dd($absens);
            // $karyawans = Karyawan::get();

            $html = view('pages.widgets.absen-table  ', compact('absens'))->render();

            return response()->json(['html' => $html]);
        } catch (\Throwable $e) {
            // Debug error ke log
            Log::error($e);

            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function bulanan(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $karyawans = User::with(['absens' => function ($q) use ($month, $year) {
            $q->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year);
        }])->get();

        $bulanSekarang = \Carbon\Carbon::create($year, $month, 1);
        $jumlahHari    = $bulanSekarang->daysInMonth;

        $html = view('pages.widgets.absen-table-bulanan', compact('karyawans', 'bulanSekarang', 'jumlahHari'))->render();

        return response()->json(['html' => $html]);
    }
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'id'    => 'required|exists:karyawans,id',
            'photo' => 'required|image|max:2048',
        ]);

        $karyawan = \App\Models\Karyawan::find($request->id);
        $file     = $request->file('photo');
        $filename = 'user_' . time() . '.' . $file->getClientOriginalExtension();

        $file->move(public_path('assets/images/users/'), $filename);

        // hapus foto lama jika bukan default
        if ($karyawan->photo && $karyawan->photo !== 'default.png') {
            @unlink(public_path('assets/images/users/' . $karyawan->photo));
        }

        $karyawan->photo = $filename;
        $karyawan->save();

        return response()->json([
            'success' => true,
            'id'      => $karyawan->id,
            'message' => 'Foto berhasil diupdate',
            'url'     => asset('assets/images/users/' . $filename),
        ]);
    }
}
