<?php

namespace App\Http\Controllers;

use App\Models\Ar2026;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArController2026 extends Controller
{
    private const FIELDS = [
        'customer_name',
        'po_number',
        'invoice_number',
        'shipment_date',
        'peb_submission_number',
        'peb_number',
        'fob_usd',
        'fob_peb_usd',
        'exchange_rate',
        'container_count',
        'deposit_date',
        'deposit_amount',
        'settlement_date',
        'settlement_amount',
        'description',
        'remark',
    ];

    public function index()
    {
        return view('pages.ars2026.indxe');
    }

    public function data()
    {
        return response()->json([
            'months' => range(1, max(1, (int) Ar2026::query()->max('month_number'))),
            'rows' => Ar2026::query()->orderBy('month_number')->orderBy('sort_order')->orderBy('id')
                ->get()->map(fn($r) => $this->serialize($r)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $record = DB::transaction(function () use ($validated) {
            $validated['sort_order'] = ((int) Ar2026::where('month_number', $validated['month_number'] ?? null)->max('sort_order')) + 1;
            return Ar2026::create($validated);
        });
        return response()->json(['message' => 'Baris berhasil ditambahkan.', 'row' => $this->serialize($record)], 201);
    }

    public function update(Request $request, Ar2026 $ar)
    {
        $validated = $request->validate($this->rules());
        $ar->update($validated);
        return response()->json(['message' => 'Perubahan berhasil disimpan.', 'row' => $this->serialize($ar->fresh())]);
    }

    public function destroy(Ar2026 $ar)
    {
        $ar->delete();
        return response()->json(['message' => 'Baris berhasil dihapus.']);
    }

    public function storeMonth(Request $request)
    {
        $validated = $request->validate([
            'month_number' => 'required|integer|between:1,12',
        ]);
        $requested = (int) $validated['month_number'];

        // Bulan yang dipilih user adalah sumber kebenaran. Jangan menolak
        // hanya karena MAX(month_number) berbeda dari cache pada browser.
        // Tabel ars_2026 hanya menyimpan baris, bukan master bulan.
        $record = DB::transaction(function () use ($requested) {
            $nextOrder = ((int) Ar2026::query()
                ->where('month_number', $requested)
                ->max('sort_order')) + 1;

            return Ar2026::create([
                'month_number' => $requested,
                'sort_order' => $nextOrder,
            ]);
        });

        $last = max($requested, (int) Ar2026::query()->max('month_number'));
        return response()->json([
            'added' => $requested,
            'row' => $this->serialize($record),
            'months' => range(1, max(1, min(12, $last))),
        ], 201);
    }

    private function rules(): array
    {
        return [
            'month_number' => 'required|integer|between:1,12',
            'customer_name' => 'nullable|string|max:255',
            'po_number' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:150',
            'shipment_date' => 'nullable|date_format:Y-m-d',
            'peb_submission_number' => 'nullable|string|max:150',
            'peb_number' => 'nullable|string|max:150',
            'fob_usd' => 'nullable|numeric|min:0|max:9999999999999999',
            'fob_peb_usd' => 'nullable|numeric|min:0|max:9999999999999999',
            'exchange_rate' => 'nullable|numeric|min:0|max:99999999999999',
            'container_count' => 'nullable|numeric|min:0|max:99999999',
            'deposit_date' => 'nullable|date_format:Y-m-d',
            'deposit_amount' => 'nullable|numeric|min:0|max:9999999999999999',
            'settlement_date' => 'nullable|date_format:Y-m-d',
            'settlement_amount' => 'nullable|numeric|min:0|max:9999999999999999',
            'description' => 'nullable|string',
            'remark' => 'nullable|string',
        ];
    }

    private function serialize(Ar2026 $r): array
    {
        $out = ['id' => $r->id, 'month_number' => $r->month_number === null ? null : (int) $r->month_number, 'sort_order' => (int) $r->sort_order];
        foreach (self::FIELDS as $field) {
            $value = $r->{$field};
            $out[$field] = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
        }
        return $out;
    }
}
