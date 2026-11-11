<?php
namespace App\Http\Controllers;

use App\Models\SignatureSpk;
use App\Models\Spk;
use App\Models\SpkTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;

class InventorSignatureController extends Controller
{
    private function payload(Spk $spk): array
    {
        return is_array($spk->data) ? $spk->data : (json_decode((string) $spk->data, true) ?: []);
    }

    /** Extract the exact company code, never match NW inside NWD/NWS/NWR. */
    private function companyKey(array $data): string
    {
        foreach (['no_po', 'no_spk'] as $field) {
            $value = strtoupper(trim((string) ($data[$field] ?? '')));
            if (preg_match('/(?<![A-Z])NW[A-Z]*(?![A-Z])/i', $value, $matches)) {
                return strtolower($matches[0]);
            }
        }
        return '';
    }

    private function special(Spk $spk): bool
    {
        // All non-NW divisions skip Checker 1, per this Signature Center workflow.
        $key = $this->companyKey($this->payload($spk));
        return $key !== '' && $key !== 'nw';
    }

    /** Return YYYY-MM-DD for accepted input formats; null for invalid/blank dates. */
    private function receiveDate(array $data): ?string
    {
        $value = trim((string) ($data['tgl_terima'] ?? ''));
        if ($value === '')
            return null;
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'd.m.Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value)
                    return $date->format('Y-m-d');
            } catch (\Throwable $e) { /* try next date format */
            }
        }
        return null;
    }

    // Tahap berikutnya hanya aktif setelah tahap sebelumnya selesai.
    private function nextStage(SignatureSpk $signature, Spk $spk): ?string
    {
        if (!$signature->made_at)
            return null;
        if (!$this->special($spk) && !$signature->checked_at)
            return 'checked';
        if (!$signature->checked_at_2)
            return 'checked_2';
        if (!$signature->approved_at)
            return 'approved';
        return null;
    }

    private function pendingType(SignatureSpk $signature, Spk $spk, int $userId): ?string
    {
        $type = $this->nextStage($signature, $spk);
        $field = match ($type) {
            'checked' => 'checked_by',
            'checked_2' => 'checked_by_2',
            'approved' => 'approved_by',
            default => null,
        };
        return $field && $signature->{$field} !== null && (int) $signature->{$field} === $userId ? $type : null;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:200',
            'key' => ['nullable', 'string', 'regex:/^(all|nw[a-z]*)$/i'],
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);
        $userId = (int) $request->user()->id;
        $search = trim((string) ($filters['q'] ?? ''));
        $key = strtolower((string) ($filters['key'] ?? 'all'));
        $dateFrom = $filters['date_from'] ?? '';
        $dateTo = $filters['date_to'] ?? '';

        $signatures = SignatureSpk::with(['madeBy', 'checkedBy', 'checkedBy2', 'approvedBy', 'supplier'])
            ->where(function ($q) use ($userId) {
                $q->where('made_by', $userId)
                    ->orWhere('checked_by', $userId)
                    ->orWhere('checked_by_2', $userId)
                    ->orWhere('approved_by', $userId);
            })->orderByDesc('spk_id')->get();

        $spks = Spk::whereIn('id', $signatures->pluck('spk_id'))->get()->keyBy('id');
        $documents = $signatures->map(function ($signature) use ($spks, $userId, $search, $key, $dateFrom, $dateTo) {
            $spk = $spks->get($signature->spk_id);
            if (!$spk || in_array(strtolower((string) $spk->status), ['revision', 'rejected'], true))
                return null;

            $stage = $this->nextStage($signature, $spk);
            $type = $this->pendingType($signature, $spk, $userId);
            $isMaker = (int) $signature->made_by === $userId;
            if (!$type && !($isMaker && $signature->made_at && $stage))
                return null;

            $data = $this->payload($spk);
            $company = $this->companyKey($data);
            if ($key !== 'all' && $company !== $key)
                return null;

            $receiveDate = $this->receiveDate($data);
            if ($dateFrom !== '' && (!$receiveDate || $receiveDate < $dateFrom))
                return null;
            if ($dateTo !== '' && (!$receiveDate || $receiveDate > $dateTo))
                return null;

            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $data['no_spk'] ?? '',
                    $data['no_po'] ?? '',
                    $data['sup'] ?? '',
                    $signature->supplier?->name ?? '',
                ]));
                if (!str_contains($haystack, mb_strtolower($search)))
                    return null;
            }
            return compact('spk', 'signature', 'type', 'stage', 'isMaker', 'data');
        })->filter()->values();

        return view('pages.management.inventor_signature', compact('documents', 'search', 'key', 'dateFrom', 'dateTo'));
    }

    public function sign(Request $request, int $id)
    {
        $validated = $request->validate(['type' => 'required|in:checked,checked_2,approved', 'remark' => 'nullable|string|max:2000']);
        return DB::transaction(function () use ($request, $id, $validated) {
            $signature = SignatureSpk::query()->where('spk_id', $id)->lockForUpdate()->firstOrFail();
            $spk = Spk::findOrFail($id);
            abort_if(in_array(strtolower((string) $spk->status), ['revision', 'rejected'], true), 409, 'SPK dalam proses revisi.');
            $type = $this->pendingType($signature, $spk, (int) $request->user()->id);
            abort_unless($type && $type === $validated['type'], 403, 'Bukan giliran tanda tangan Anda atau SPK sudah ditandatangani.');
            $fields = match ($type) {
                'checked' => ['checked_at' => now(), 'checked_remark' => $validated['remark'] ?? null],
                'checked_2' => ['checked_at_2' => now(), 'checked_2_remark' => $validated['remark'] ?? null],
                'approved' => ['approved_at' => now(), 'approved_remark' => $validated['remark'] ?? null],
            };
            $signature->update($fields);
            if ($type === 'approved')
                $spk->update(['status' => 'approved']);
            SpkTimeline::create([
                'spk_id' => $id,
                'data' => json_encode([
                    'time' => now()->format('d M Y H:i'),
                    'type' => $type,
                    'user' => $request->user()->name,
                    'remark' => $validated['remark'] ?? null,
                ])
            ]);
            return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.', 'spk_id' => $id]);
        });
    }
    /** Reject hanya oleh approver yang sedang mendapat giliran; alasan wajib. */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:checked,checked_2,approved',
            'message' => 'required|string|min:5|max:2000',
        ]);
        return DB::transaction(function () use ($request, $id, $validated) {
            $signature = SignatureSpk::where('spk_id', $id)->lockForUpdate()->firstOrFail();
            $spk = Spk::findOrFail($id);
            abort_if(in_array(strtolower((string) $spk->status), ['revision', 'rejected'], true), 409, 'SPK sudah dalam proses revisi.');
            $type = $this->pendingType($signature, $spk, (int) $request->user()->id);
            abort_unless($type && $type === $validated['type'], 403, 'Anda tidak berhak me-reject SPK ini.');
            // Status revision menjaga dokumen keluar dari antrean tanpa menghapus tanda tangan sebelumnya.
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
                ])
            ]);
            return response()->json(['success' => true, 'message' => 'SPK ditolak. Alasan tercatat di timeline.', 'spk_id' => $id]);
        });
    }

}
