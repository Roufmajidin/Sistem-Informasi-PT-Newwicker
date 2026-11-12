<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\EmployeeLoan;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    public function index()
    {
        $loans = EmployeeLoan::with([
            'divisi',
            'approverUser',
        ])
            ->latest()
            ->get();

        return view('pages.employee.index', compact('loans'));
    }

    public function create()
    {
        $divisis = Divisi::all();

        return view('employee-loans.create', compact('divisis'));
    }

    public function store(Request $request)
    {
        // =========================
        // AUTH
        // =========================

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

        // =========================
        // VALIDASI
        // =========================

        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'divisi_id' => 'required|exists:divisis,id',
            'nominal_pengajuan' => 'required|numeric|gt:0',
            'alasan_pengajuan' => 'required|string',
            'cara_pengembalian' => 'required|in:pemotongan_gaji,tunai',
            'nominal_potongan_gaji' => 'nullable|numeric|min:0',
            'periode_pembayaran' => 'required|integer|min:1',
            'pelunasan_terakhir' => 'nullable|date',
        ]);

        // =========================
        // MULTIPLE PENDING ALLOWED
        // =========================

        /*
         * User diperbolehkan membuat pengajuan baru
         * meskipun masih mempunyai pengajuan pending.
         *
         * Tidak melakukan update pengajuan sebelumnya.
         * Setiap submit membuat record baru.
         */

        // =========================
        // CREATE
        // =========================

        $loan = EmployeeLoan::create([
            'nama_karyawan' => $validated['nama_karyawan'],
            'jabatan' => $validated['jabatan'],
            'divisi_id' => $validated['divisi_id'],
            'nominal_pengajuan' => $validated['nominal_pengajuan'],
            'alasan_pengajuan' => $validated['alasan_pengajuan'],
            'cara_pengembalian' => $validated['cara_pengembalian'],
            'nominal_potongan_gaji' =>
                $validated['nominal_potongan_gaji'] ?? null,
            'periode_pembayaran' => $validated['periode_pembayaran'],
            'pelunasan_terakhir' =>
                $validated['pelunasan_terakhir'] ?? null,
            'status' => 'pending',
            'approver' => null,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Pengajuan pinjaman berhasil dibuat',
            'data' => $loan,
        ], 201);
    }

    public function approve($id)
    {
        // =========================
        // FIND DATA
        // =========================

        $loan = EmployeeLoan::find($id);

        if (!$loan) {
            return redirect()
                ->back()
                ->with('error', 'Data pengajuan tidak ditemukan');
        }

        // =========================
        // VALIDASI STATUS
        // =========================

        if ($loan->status === 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan sudah di approve');
        }

        if ($loan->status === 'rejected') {
            return redirect()
                ->back()
                ->with('error', 'Pengajuan sudah di reject');
        }

        // =========================
        // APPROVE
        // =========================

        $loan->update([
            'status' => 'approved',
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

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Token tidak valid',
            ], 401);
        }

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
            'data' => $loans,
        ], 200);
    }

    public function destroy($id)
    {
        $loan = EmployeeLoan::find($id);

        if (!$loan) {
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
