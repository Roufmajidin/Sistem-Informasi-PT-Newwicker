<?php
namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\EmployeeLoan;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    public function index()
    {
        $loans = EmployeeLoan::with('divisi')
            ->latest()
            ->get();
// dd($loans);
        return view('pages.employee.index', compact('loans'));
    }

    public function create()
    {
        $divisis = Divisi::all();

        return view('employee-loans.create', compact('divisis'));
    }

    public function store(Request $request)
    {

        if (! $request->user()) {

            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

        $user = $request->user();

        // =========================
        // VALIDASI
        // =========================

        $validated = $request->validate([

            'nama_karyawan'         => 'required',
            'jabatan'               => 'required',
            'divisi_id'             => 'required|exists:divisis,id',
            'nominal_pengajuan'     => 'required|numeric',
            'alasan_pengajuan'      => 'required',
            'cara_pengembalian'     => 'required|in:pemotongan_gaji,tunai',
            'nominal_potongan_gaji' => 'nullable|numeric',
            'periode_pembayaran'    => 'required|integer',
            'pelunasan_terakhir'    => 'nullable',
        ]);

        // =========================
        // CEK PENDING
        // =========================

        $pendingLoan = EmployeeLoan::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        // jika masih ada pending
        if ($pendingLoan) {

            return response()->json([

                'message' => 'Masih ada pengajuan pinjaman yang pending',

                'data'    => $pendingLoan,

            ], 422);
        }

        // =========================
        // CREATE
        // =========================

        $loan = EmployeeLoan::create([

            'nama_karyawan'         => $validated['nama_karyawan'],
            'jabatan'               => $validated['jabatan'],
            'divisi_id'             => $validated['divisi_id'],
            'nominal_pengajuan'     => $validated['nominal_pengajuan'],
            'alasan_pengajuan'      => $validated['alasan_pengajuan'],
            'cara_pengembalian'     => $validated['cara_pengembalian'],
            'nominal_potongan_gaji' => $validated['nominal_potongan_gaji'],
            'periode_pembayaran'    => $validated['periode_pembayaran'],
            'pelunasan_terakhir'    => $validated['pelunasan_terakhir'],
            'status'                => 'pending',
            'approver'              => null,
            'user_id'               => $user->id,
        ]);

        return response()->json([

            'message' => 'Pengajuan pinjaman berhasil dibuat',

            'data'    => $loan,

        ], 201);
    }
    public function approve($id)
    {
        // =========================
        // FIND DATA
        // =========================

        $loan = EmployeeLoan::find($id);

        if (! $loan) {

            return redirect()
                ->back()
                ->with('error', 'Data pengajuan tidak ditemukan');
        }

        // =========================
        // VALIDASI
        // =========================

        if ($loan->status == 'approved') {

            return redirect()
                ->back()
                ->with('error', 'Pengajuan sudah di approve');
        }

        if ($loan->status == 'rejected') {

            return redirect()
                ->back()
                ->with('error', 'Pengajuan sudah di reject');
        }

        // =========================
        // APPROVE
        // =========================

        $loan->update([

            'status'   => 'approved',

            'approver' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengajuan berhasil di approve');
    }
    public function myLoans(Request $request)
    {
        // =========================
        // AUTH
        // =========================

        if (! $request->user()) {

            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

        $user = $request->user();

        // =========================
        // GET DATA
        // =========================

        $loans = EmployeeLoan::with([
            'divisi',
            'approverUser',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([

            'message' => 'Data pengajuan pinjaman',

            'data'    => $loans,

        ], 200);
    }
    public function destroy($id)
{
    $loan = EmployeeLoan::find($id);

    if (! $loan) {

        return redirect()
            ->back()
            ->with('error', 'Data tidak ditemukan');
    }

    $loan->delete();

    return redirect()
        ->back()
        ->with('success', 'Pengajuan berhasil dihapus');
}

}
