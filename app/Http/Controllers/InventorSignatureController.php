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
        $userId = (int) $request->user()->id;

        $search = trim((string) $request->query('q', ''));
        $key = strtoupper(trim((string) $request->query('key', '')));
        $dateFromInput = trim((string) $request->query('date_from', ''));

        // Validasi filter
        $request->validate([
            'q' => 'nullable|string|max:200',
            'key' => 'nullable|string|max:20|regex:/^[a-zA-Z0-9_-]+$/',
            'date_from' => 'nullable|date_format:Y-m-d',
        ]);

        $dateFrom = $dateFromInput !== ''
            ? \Carbon\Carbon::createFromFormat(
                '!Y-m-d',
                $dateFromInput
            )->startOfDay()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Helper: Ambil data JSON SPK
        |--------------------------------------------------------------------------
        */
        $getData = function ($spk) {
            if (is_array($spk->data)) {
                return $spk->data;
            }

            return json_decode(
                (string) $spk->data,
                true
            ) ?: [];
        };

        /*
        |--------------------------------------------------------------------------
        | Helper: Deteksi divisi SPK
        |--------------------------------------------------------------------------
        | Menghindari NW tercampur dengan NWD/NWS/NWR.
        */
        $getDivision = function ($data) {
            $text = strtoupper(
                trim(
                    (string) ($data['no_spk'] ?? '') . ' ' .
                    (string) ($data['no_po'] ?? '')
                )
            );

            if (
                preg_match(
                    '/(?<![A-Z0-9])(NWD|NWS|NWR|NW)(?![A-Z0-9])/i',
                    $text,
                    $matches
                )
            ) {
                return strtoupper($matches[1]);
            }

            return null;
        };

        /*
        |--------------------------------------------------------------------------
        | Helper: Tanggal terima SPK
        |--------------------------------------------------------------------------
        */
        $parseDate = function ($value) {
            if (!$value) {
                return null;
            }

            if ($value instanceof \DateTimeInterface) {
                return \Carbon\Carbon::instance($value)
                    ->startOfDay();
            }

            $value = trim((string) $value);

            foreach ([
                'Y-m-d',
                'd/m/Y',
                'd-m-Y',
                'Y/m/d',
                'd/m/y',
                'd-m-y',
            ] as $format) {
                try {
                    $date = \Carbon\Carbon::createFromFormat(
                        '!' . $format,
                        $value
                    );

                    if ($date && $date->format($format) === $value) {
                        return $date->startOfDay();
                    }
                } catch (\Throwable $e) {
                    // Coba format berikutnya.
                }
            }

            return null;
        };

        /*
        |--------------------------------------------------------------------------
        | Ambil semua SPK yang melibatkan user login
        |--------------------------------------------------------------------------
        */
        $signatures = SignatureSpk::with([
            'madeBy.karyawan.divisi',
            'checkedBy.karyawan.divisi',
            'checkedBy2.karyawan.divisi',
            'approvedBy.karyawan.divisi',
            'supplier',
        ])
            ->where(function ($query) use ($userId) {
                $query->where('made_by', $userId)
                    ->orWhere('checked_by', $userId)
                    ->orWhere('checked_by_2', $userId)
                    ->orWhere('approved_by', $userId);
            })
            ->orderByDesc('spk_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ambil data SPK
        |--------------------------------------------------------------------------
        */
        $spks = Spk::whereIn(
            'id',
            $signatures->pluck('spk_id')->unique()
        )
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | Susun antrean dokumen
        |--------------------------------------------------------------------------
        */
        $documents = $signatures->map(function ($signature) use ($spks, $userId, $search, $key, $dateFrom, $getData, $getDivision, $parseDate) {
            $spk = $spks->get($signature->spk_id);

            if (!$spk) {
                return null;
            }

            /*
            |--------------------------------------------------------------
            | Abaikan SPK dalam revisi / rejected
            |--------------------------------------------------------------
            */
            $status = strtolower(trim((string) $spk->status));

            if (
                in_array($status, [
                    'revision',
                    'rejected',
                ], true)
            ) {
                return null;
            }

            $data = $getData($spk);
            $division = $getDivision($data);

            /*
            |--------------------------------------------------------------
            | Filter divisi
            |--------------------------------------------------------------
            */
            if ($key !== '' && $division !== $key) {
                return null;
            }

            /*
            |--------------------------------------------------------------
            | Filter tanggal terima (cutoff)
            |--------------------------------------------------------------
            */
            $tanggalTerima = $parseDate(
                $data['tgl_terima']
                ?? $data['tanggal_terima']
                ?? null
            );

            if ($dateFrom) {
                // Tanggal yang tidak valid tidak lolos cutoff.
                if (
                    !$tanggalTerima ||
                    $tanggalTerima->lt($dateFrom)
                ) {
                    return null;
                }
            }

            /*
            |--------------------------------------------------------------
            | Filter pencarian
            |--------------------------------------------------------------
            */
            if ($search !== '') {
                $searchText = mb_strtolower(
                    implode(' ', [
                        (string) ($data['no_spk'] ?? ''),
                        (string) ($data['no_po'] ?? ''),
                        (string) ($data['sup'] ?? ''),
                        (string) ($signature->supplier?->name ?? ''),
                    ])
                );

                if (
                    !str_contains(
                        $searchText,
                        mb_strtolower($search)
                    )
                ) {
                    return null;
                }
            }

            /*
            |--------------------------------------------------------------
            | Tentukan Checker 1 wajib atau tidak
            |--------------------------------------------------------------
            | Mengikuti helper special() existing:
            | NWS dan NWD melewati Checker 1.
            */
            $skipChecker1 = $this->special($spk);

            /*
            |--------------------------------------------------------------
            | Cek tanda tangan internal yang belum lengkap
            |--------------------------------------------------------------
            | Made By dan Supplier TIDAK dihitung.
            */
            $pendingChecker1 = !$skipChecker1
                && !$signature->checked_at;

            $pendingChecker2 = !$signature->checked_at_2;

            $pendingApproved = !$signature->approved_at;

            $hasPendingInternal =
                $pendingChecker1
                || $pendingChecker2
                || $pendingApproved;

            /*
            |--------------------------------------------------------------
            | Jika seluruh tanda tangan internal selesai,
            | keluarkan dari antrean.
            |--------------------------------------------------------------
            */
            if (!$hasPendingInternal) {
                return null;
            }

            /*
            |--------------------------------------------------------------
            | Identifikasi keterlibatan user login
            |--------------------------------------------------------------
            */
            $isMaker = (int) $signature->made_by === $userId;

            $isChecker1 = !$skipChecker1
                && (int) $signature->checked_by === $userId;

            $isChecker2 =
                (int) $signature->checked_by_2 === $userId;

            $isApprover =
                (int) $signature->approved_by === $userId;

            $isAssigned =
                $isMaker
                || $isChecker1
                || $isChecker2
                || $isApprover;

            if (!$isAssigned) {
                return null;
            }

            /*
            |--------------------------------------------------------------
            | Tahap yang sedang berjalan
            |--------------------------------------------------------------
            */
            $stage = $this->nextStage($signature, $spk);

            /*
            |--------------------------------------------------------------
            | Hak TAP TO SIGN / REJECT
            |--------------------------------------------------------------
            | Null berarti user hanya bisa monitoring.
            */
            $type = $this->pendingType(
                $signature,
                $spk,
                $userId
            );

            /*
            |--------------------------------------------------------------
            | Nama Made By berdasarkan divisi
            |--------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------
            | Status untuk header dokumen
            |--------------------------------------------------------------
            */
            $statusLabel = match ($stage) {
                'checked' => 'Menunggu Checker 1',
                'checked_2' => 'Menunggu Checker 2',
                'approved' => 'Menunggu Approved By',
                default => 'Menunggu Made By',
            };

            return [
                'spk' => $spk,
                'signature' => $signature,
                'data' => $data,

                'type' => $type,
                'stage' => $stage,
                'isMaker' => $isMaker,

                'division' => $division,
                'makerName' => $makerName,
                'makerDivision' => $makerDivision,

                'status_label' => $statusLabel,

                'pendingChecker1' => $pendingChecker1,
                'pendingChecker2' => $pendingChecker2,
                'pendingApproved' => $pendingApproved,
            ];
        })
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Total antrean
        |--------------------------------------------------------------------------
        */
        $totalPending = $documents->count();

        return view(
            'pages.management.inventor_signature',
            compact(
                'documents',
                'search',
                'key',
                'dateFromInput',
                'totalPending'
            )
        );
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
