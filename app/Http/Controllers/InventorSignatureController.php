<?php

namespace App\Http\Controllers;

use App\Models\SignatureSpk;
use App\Models\Spk;
use App\Models\SpkTimeline;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventorSignatureController extends Controller
{
    private function spkData(Spk $spk): array
    {
        return is_array($spk->data)
            ? $spk->data
            : (json_decode((string) $spk->data, true) ?: []);
    }

    private function division(Spk $spk): ?string
    {
        $data = $this->spkData($spk);
        $text = strtoupper(trim(implode(' ', [
            (string) ($data['no_spk'] ?? ''),
            (string) ($data['no_po'] ?? ''),
        ])));

        // The longest variants are checked first; NW must not match NWD/NWS/NWR.
        if (preg_match('/(?<![A-Z0-9])(NWD|NWS|NWR|NW)(?![A-Z0-9])/i', $text, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function special(Spk $spk): bool
    {
        return in_array($this->division($spk), ['NWS', 'NWD'], true);
    }

    private function parseReceiveDate($value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        if (!is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd/m/y', 'd-m-y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (\Throwable $exception) {
                // Try another known date format.
            }
        }

        return null;
    }

    private function blocked(Spk $spk): bool
    {
        return in_array(strtolower(trim((string) $spk->status)), ['revision', 'rejected'], true);
    }

    private function pendingStages(SignatureSpk $signature, Spk $spk): array
    {
        $stages = [];
        if (!$this->special($spk) && !$signature->checked_at) {
            $stages[] = 'checked';
        }
        if (!$signature->checked_at_2) {
            $stages[] = 'checked_2';
        }
        if (!$signature->approved_at) {
            $stages[] = 'approved';
        }
        return $stages;
    }

    // No sequential approval: each assigned signer can sign their own pending slot.
    private function pendingTypes(SignatureSpk $signature, Spk $spk, int $userId): array
    {
        $pending = $this->pendingStages($signature, $spk);
        $assignments = [
            'checked' => 'checked_by',
            'checked_2' => 'checked_by_2',
            'approved' => 'approved_by',
        ];

        return array_values(array_filter($pending, function ($type) use ($signature, $userId, $assignments) {
            $field = $assignments[$type];
            return $signature->{$field} !== null && (int) $signature->{$field} === $userId;
        }));
    }

    // Backward compatible with the existing Blade, which expects a single $doc['type'].
    private function pendingType(SignatureSpk $signature, Spk $spk, int $userId): ?string
    {
        return $this->pendingTypes($signature, $spk, $userId)[0] ?? null;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:200',
            'key' => ['nullable', 'string', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'date_from' => 'nullable|date_format:Y-m-d',
        ]);

        $userId = (int) $request->user()->id;
        $search = trim((string) ($validated['q'] ?? ''));
        $key = strtoupper(trim((string) ($validated['key'] ?? '')));
        // Empty / ALL means no division restriction.
        if ($key === 'ALL') {
            $key = '';
        }
        $dateFromInput = (string) ($validated['date_from'] ?? '');
        $dateFrom = $dateFromInput !== '' ? Carbon::createFromFormat('!Y-m-d', $dateFromInput)->startOfDay() : null;

        $signatures = SignatureSpk::with([
            'madeBy.karyawan.divisi',
            'checkedBy.karyawan.divisi',
            'checkedBy2.karyawan.divisi',
            'approvedBy.karyawan.divisi',
            'supplier',
        ])->where(function ($query) use ($userId) {
            $query->where('made_by', $userId)
                ->orWhere('checked_by', $userId)
                ->orWhere('checked_by_2', $userId)
                ->orWhere('approved_by', $userId);
        })->orderByDesc('spk_id')->get();

        // Filter at database level: only submitted SPKs created on/after Start Date.
        // created_at belongs to the SPK record, not the signature record.
        $spkQuery = Spk::query()
            ->whereIn('id', $signatures->pluck('spk_id')->unique())
            ->whereRaw('LOWER(TRIM(status)) = ?', ['diajukan']);

        if ($dateFrom !== null) {
            $spkQuery->where('created_at', '>=', $dateFrom->toDateTimeString());
        }

        $spks = $spkQuery->get()->keyBy('id');

        $documents = $signatures->map(function (SignatureSpk $signature) use ($spks, $userId, $search, $key) {
            $spk = $spks->get($signature->spk_id);
            if (!$spk || $this->blocked($spk)) {
                return null;
            }

            $data = $this->spkData($spk);
            $division = $this->division($spk);
            if ($key !== '' && $division !== $key) {
                return null;
            }

            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($data['no_spk'] ?? ''),
                    (string) ($data['no_po'] ?? ''),
                    (string) ($data['sup'] ?? ''),
                    (string) ($signature->supplier?->name ?? ''),
                ]));
                if (!str_contains($haystack, mb_strtolower($search))) {
                    return null;
                }
            }

            $pendingStages = $this->pendingStages($signature, $spk);
            if ($pendingStages === []) {
                return null; // Supplier and Made By are excluded from completion criteria.
            }

            $isMaker = $signature->made_by !== null && (int) $signature->made_by === $userId;
            $pendingTypes = $this->pendingTypes($signature, $spk, $userId);
            $type = $pendingTypes[0] ?? null;
            $stage = $pendingStages[0];

            $makerName = match ($division) {
                'NW', 'NWR' => 'Nur Khasanah',
                'NWD', 'NWS' => 'Intan',
                default => $signature->madeBy?->name ?? '-',
            };
            $makerDivision = match ($division) {
                'NW', 'NWR' => 'Purchasing',
                'NWD', 'NWS' => 'RnD Engineering',
                default => $signature->madeBy?->karyawan?->divisi?->nama ?? '',
            };

            $statusLabel = match ($stage) {
                'checked' => 'Menunggu Checker 1',
                'checked_2' => 'Menunggu Checker 2',
                'approved' => 'Menunggu Approved By',
            };

            return [
                'spk' => $spk,
                'signature' => $signature,
                'data' => $data,
                'type' => $type,
                'types' => $pendingTypes,
                'stage' => $stage,
                'pendingStages' => $pendingStages,
                'isMaker' => $isMaker,
                'division' => $division,
                'makerName' => $makerName,
                'makerDivision' => $makerDivision,
                'status_label' => $statusLabel,
                'pendingChecker1' => in_array('checked', $pendingStages, true),
                'pendingChecker2' => in_array('checked_2', $pendingStages, true),
                'pendingApproved' => in_array('approved', $pendingStages, true),
            ];
        })->filter()->values();

        $totalPending = $documents->count();

        // The Blade date input needs the original YYYY-MM-DD string.
        $dateFrom = $dateFromInput;

        return view('pages.management.inventor_signature', compact(
            'documents',
            'search',
            'key',
            'dateFrom',
            'dateFromInput',
            'totalPending'
        ));
    }

    public function sign(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:checked,checked_2,approved',
            'remark' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($request, $id, $validated) {
            $signature = SignatureSpk::where('spk_id', $id)->lockForUpdate()->firstOrFail();
            $spk = Spk::findOrFail($id);
            abort_if($this->blocked($spk), 409, 'SPK dalam proses revisi.');

            $type = $validated['type'];
            abort_unless(
                in_array($type, $this->pendingTypes($signature, $spk, (int) $request->user()->id), true),
                403,
                'Anda tidak berhak menandatangani tahap ini atau sudah ditandatangani.'
            );

            $fields = match ($type) {
                'checked' => ['checked_at' => now(), 'checked_remark' => $validated['remark'] ?? null],
                'checked_2' => ['checked_at_2' => now(), 'checked_2_remark' => $validated['remark'] ?? null],
                'approved' => ['approved_at' => now(), 'approved_remark' => $validated['remark'] ?? null],
            };
            $signature->update($fields);
            $signature->refresh();

            // Mark approved only after all required INTERNAL signatures are present.
            if ($this->pendingStages($signature, $spk) === []) {
                $spk->update(['status' => 'approved']);
            }

            SpkTimeline::create([
                'spk_id' => $id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => $type,
                    'user_id' => $request->user()->id,
                    'user' => $request->user()->name,
                    'remark' => $validated['remark'] ?? null,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tanda tangan berhasil disimpan.',
                'spk_id' => $id,
                'remaining_my_stages' => $this->pendingTypes($signature, $spk, (int) $request->user()->id),
            ]);
        });
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:checked,checked_2,approved',
            'message' => 'required|string|min:5|max:2000',
        ]);

        return DB::transaction(function () use ($request, $id, $validated) {
            $signature = SignatureSpk::where('spk_id', $id)->lockForUpdate()->firstOrFail();
            $spk = Spk::findOrFail($id);
            abort_if($this->blocked($spk), 409, 'SPK sudah dalam proses revisi.');

            $type = $validated['type'];
            abort_unless(
                in_array($type, $this->pendingTypes($signature, $spk, (int) $request->user()->id), true),
                403,
                'Anda tidak berhak menolak tahap ini.'
            );

            $spk->update(['status' => 'revision']);
            SpkTimeline::create([
                'spk_id' => $id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => 'signature_rejected',
                    'stage' => $type,
                    'user_id' => $request->user()->id,
                    'user' => $request->user()->name,
                    'remark' => $validated['message'],
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SPK ditolak. Alasan tercatat di timeline.',
                'spk_id' => $id,
            ]);
        });
    }
}
