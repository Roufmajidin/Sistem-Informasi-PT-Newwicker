<?php
namespace App\Http\Controllers;

use App\Imports\KaryawanImport;
use App\Models\Absen;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

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
    public function absenkaryawan()
    {
        //
        $karyawans = User::with(['absens'])->get();

        $today       = Carbon::now();
        $year        = $today->year;
        $month       = $today->month;
        $daysInMonth = $today->daysInMonth;

        $today       = Carbon::now();
        $year        = $today->year;
        $month       = $today->month;
        $daysInMonth = $today->daysInMonth;

        return view('pages.karyawan.absen', compact('karyawans', 'daysInMonth', 'month', 'year'));
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
                    'tanggal_join'         => is_numeric($row[11])
                    ? Carbon::instance(ExcelDate::excelToDateTimeObject($row[11]))->format('Y-m-d')
                    : $row[11],
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

        foreach ($rows as $row) {
            if (empty($row['nama_lengkap'])) {
                continue;
            }

            $exists = Karyawan::where('nama_lengkap', 'like', '%' . $row['nama_lengkap'] . '%')->exists();
            if ($exists) {
                continue;
            }

            $inserted[] = Karyawan::create([
                'nama_lengkap'      => $row['nama_lengkap'],
                'nik'               => $row['nik'],
                'jenis_kelamin'     => $row['jenis_kelamin'],
                'tempat'            => explode(',', $row['tempat_tanggal_lahir'])[0] ?? null,
                'tanggal_lahir'     => explode(',', $row['tempat_tanggal_lahir'])[1] ?? null,
                'alamat'            => $row['alamat'],
                'status_perkawinan' => $row['status_perkawinan'],
                'divisi_id'         => $row['divisi'],
                'status'            => $row['status_karyawan'],
                'lokasi'            => $row['lokasi'],
                'tanggal_join'      => (function ($tgl) {
                    if (is_numeric($tgl)) {
                        return Date::excelToDateTimeObject($tgl)->format('Y-m-d');
                    }
                    try {
                        return Carbon::parse($tgl)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                })($row['tanggal_join'] ?? null)]);
        }

        return response()->json([
            'success'  => true,
            'inserted' => count($inserted),
        ]);
    }
    public function scan()
    {
        return view('pages.karyawan.scan');
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

  public function filter(Request $request)
{
    $month = $request->month ?? now()->month;
    $year = $request->year ?? now()->year;
    $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

    $karyawans = User::with('absens')->get();

    // Gunakan view string (tanpa partial) — render langsung sebagai string
    $html = view('pages.karyawan.absen-table', compact('karyawans', 'month', 'year', 'daysInMonth'))->render();

    return response()->json(['html' => $html]);
}


}
