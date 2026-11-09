<?php

namespace App\Http\Controllers;

use App\Models\Moms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MomController extends Controller
{
    public function index() { return view('pages.mom.index', ['mom' => null]); }

    public function listing()
    {
        $moms = Moms::query()->select(['id','meeting_date','subject','status','write_by','divisi','updated_at'])
            ->orderByDesc('updated_at')->paginate(20);
        return view('pages.mom.list', compact('moms'));
    }

    public function show(Moms $mom) { return view('pages.mom.detail', compact('mom')); }

    public function edit(Moms $mom) { return view('pages.mom.index', compact('mom')); }

    // Manual Save only. An existing MoM is updated strictly by its ID.
    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable','integer','min:1','exists:moms,id'],
            'meeting_date' => ['nullable','date'],
            'start_month' => ['nullable','date_format:Y-m'],
            'subject' => ['required','string','max:255'],
            'attendees' => ['present','array'],
            'attendees.*.id' => ['required','integer'],
            'attendees.*.name' => ['nullable','string','max:255'],
            'attendees.*.position' => ['nullable','string','max:255'],
            'discussions' => ['present','array'],
            'discussions.*.id' => ['required','integer'],
            'discussions.*.personId' => ['nullable','integer'],
            'discussions.*.description' => ['nullable','string'],
            'discussions.*.notes' => ['nullable','string'],
            'divisi' => ['nullable',Rule::in(['HRD','Produksi','Marketing','Finance'])],
        ]);

        $payload = [
            'meeting_date' => $data['meeting_date'] ?? now()->toDateString(),
            'start_month' => $data['start_month'] ?? now()->format('Y-m'),
            'subject' => $data['subject'],
            'attendees' => $data['attendees'],
            'discussions' => $data['discussions'],
            'divisi' => $data['divisi'] ?? null,
        ];

        if (!empty($data['id'])) {
            $mom = Moms::findOrFail($data['id']);
            if ($mom->status !== 'draft') {
                return response()->json(['success'=>false,'message'=>'MoM bukan draft dan tidak dapat diubah.'],409);
            }
            $mom->forceFill($payload)->save();
            $created = false;
        } else {
            $mom = new Moms();
            $mom->forceFill($payload + [
                'status' => 'draft',
                'write_by' => Auth::user()->name,
            ]);
            $mom->save();
            $created = true;
        }

        return response()->json([
            'success'=>true,
            'message'=>$created ? 'MoM berhasil dibuat.' : 'Perubahan MoM berhasil diperbarui.',
            'id'=>$mom->id,
            'updated_at'=>$mom->updated_at?->toIso8601String(),
            'edit_url'=>route('mom.edit',$mom),
        ],$created ? 201 : 200);
    }

    public function destroy(Moms $mom)
    {
        $mom->delete();
        return redirect()->route('mom.list')->with('success','Minutes of Meeting berhasil dihapus.');
    }
}
