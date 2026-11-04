<?php

namespace App\Http\Controllers;

use App\Models\Moms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MomController extends Controller
{
    public function index()
    {
        return view('pages.mom.index', ['mom' => null]);
    }

    public function listing(Request $request)
    {
        // Access to this controller is restricted by MoM route middleware.
        $moms = Moms::query()
            ->select([
                'id', 'client_key', 'meeting_date', 'subject',
                'status', 'write_by', 'divisi', 'updated_at',
            ])
            ->orderByDesc('updated_at')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($moms)
                ->header('Cache-Control', 'private, no-store');
        }

        return view('pages.mom.list', compact('moms'));
    }

    public function show(Request $request, Moms $mom)
    {

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => $mom]);
        }

        return view('pages.mom.detail', compact('mom'));
    }

    public function edit(Moms $mom)
    {

        // Repair legacy records without changing their ID or meeting content.
        if (blank($mom->client_key)) {
            DB::transaction(function () use ($mom) {
                $locked = Moms::query()->lockForUpdate()->findOrFail($mom->id);

                if (blank($locked->client_key)) {
                    $locked->client_key = (string) Str::uuid();
                    $locked->save();
                }
            });

            $mom->refresh();
        }

        return view('pages.mom.index', compact('mom'));
    }

    public function autosave(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],
            'client_key' => ['required', 'string', 'max:100'],
            'meeting_date' => ['nullable', 'date'],
            'start_month' => ['nullable', 'date_format:Y-m'],
            'subject' => ['nullable', 'string', 'max:255'],
            'attendees' => ['present', 'array'],
            'attendees.*.id' => ['required', 'integer'],
            'attendees.*.name' => ['nullable', 'string', 'max:255'],
            'attendees.*.position' => ['nullable', 'string', 'max:255'],
            'discussions' => ['present', 'array'],
            'discussions.*.id' => ['required', 'integer'],
            'discussions.*.personId' => ['nullable', 'integer'],
            'discussions.*.description' => ['nullable', 'string'],
            'discussions.*.notes' => ['nullable', 'string'],
            'divisi' => ['nullable', Rule::in(['HRD', 'Produksi', 'Marketing', 'Finance'])],
        ]);

        $payload = [
            'meeting_date' => $data['meeting_date'] ?? now()->toDateString(),
            'start_month' => $data['start_month'] ?? now()->format('Y-m'),
            'subject' => $data['subject'] ?? '',
            'attendees' => $data['attendees'],
            'discussions' => $data['discussions'],
            'divisi' => $data['divisi'] ?? null,
        ];

        $result = DB::transaction(function () use ($data, $payload) {
            $created = false;

            if (!empty($data['id'])) {
                $mom = Moms::query()->lockForUpdate()->findOrFail($data['id']);
        
                if ($mom->client_key !== $data['client_key']) {
                    return ['error' => 'ID MoM dan client_key tidak cocok.', 'status' => 409];
                }
            } else {
                $mom = Moms::query()
                    ->where('client_key', $data['client_key'])
                    ->lockForUpdate()
                    ->first();

                if (!$mom) {
                    $mom = Moms::create($payload + [
                        'client_key' => $data['client_key'],
                        'status' => 'draft',
                        'write_by' => Auth::user()->name,
                    ]);
                    $created = true;
                }
            }

            if ($mom->status !== 'draft') {
                return ['error' => 'MoM bukan draft dan tidak dapat diubah.', 'status' => 409];
            }

            if (!$created) {
                // Preserve original writer and status.
                $mom->fill($payload);
                $mom->save();
            }

            return ['mom' => $mom->refresh(), 'created' => $created];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], $result['status']);
        }

        $mom = $result['mom'];
        $created = $result['created'];

        return response()->json([
            'success' => true,
            'message' => $created
                ? 'Draft MoM berhasil dibuat.'
                : 'Draft MoM berhasil diperbarui.',
            'id' => $mom->id,
            'client_key' => $mom->client_key,
            'updated_at' => $mom->updated_at?->toIso8601String(),
            'edit_url' => route('mom.edit', $mom),
        ], $created ? 201 : 200);
    }

    public function store(Request $request)
    {
        return $this->autosave($request);
    }

    public function destroy(Moms $mom)
    {

        $mom->delete();

        return redirect()
            ->route('mom.list')
            ->with('success', 'Minutes of Meeting berhasil dihapus.');
    }

}
