<?php
namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Data tabel kanan
        $agendas = Agenda::orderBy('tanggal', 'desc')->get();

        return view('pages.agenda.index', compact('agendas'));
    }
    public function request_agenda()
    {
        //
        $agendas = Agenda::orderBy('created_at', 'desc')->get();

        return view('pages.agenda.request_a', compact('agendas'));
    }
    public function updateRemark(Request $request)
    {
        $request->validate([
            'agenda_id'   => 'required|exists:agendas,id',
            'remark_rouf' => 'nullable|string|max:255',
        ]);

        Agenda::where('id', $request->agenda_id)
            ->update([
                'remark_rouf' => $request->remark_rouf,
            ]);

        return back()->with('success', 'Remark berhasil diperbarui');
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
        $request->validate([
            'jenis_agenda' => 'required',
            'status'       => 'required',
            'tanggal'      => 'nullable|date',
            'kode_agenda'  => [
                'required_if:jenis_agenda,photo sample,photo produksi',
                function ($attr, $value, $fail) use ($request) {
                    if ($request->jenis_agenda === 'photo sample' &&
                        ! preg_match('/^NWS \d{2} - \d{2}$/', $value)) {
                        $fail('Format kode harus: NWS xx - xx');
                    }

                    if ($request->jenis_agenda === 'photo produksi' &&
                        ! preg_match('/^NW \d{2} - \d{2}$/', $value)) {
                        $fail('Format kode harus: NW xx - xx');
                    }
                },
            ],
        ]);

        Agenda::create([
            'jenis_agenda' => $request->jenis_agenda,
            'kode_agenda'  => in_array($request->jenis_agenda, ['photo sample', 'photo produksi'])
                ? $request->kode_agenda
                : null,
            'dibuat_oleh'  => auth()->user()->name,
            'tanggal'      => $request->tanggal ?? now()->toDateString(),
            'status'       => $request->status,
            'catatan'      => $request->catatan,
        ]);

        return back()->with('success', 'Agenda berhasil disimpan');
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
}
