<?php

namespace App\Http\Controllers;

use App\Models\Loberon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LobController extends Controller
{
    /**
     * LIST
     */
    public function index(Request $request)
    {
        $query = Loberon::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('buyer', 'like', "%{$search}%")
                    ->orWhere('article_code', 'like', "%{$search}%")
                    ->orWhere('ean_code', 'like', "%{$search}%")
                    ->orWhere('article_description', 'like', "%{$search}%")
                    ->orWhere('hts_code', 'like', "%{$search}%");
            });
        }

        $buyers = $query
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('pages.exports.buyers.index', compact('buyers'));
    }

    /**
     * ADD SATU / BANYAK DATA
     *
     * Dipakai oleh modal Excel-like.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rows' => ['required', 'array', 'min:1'],
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->rows as $row) {

                // Skip row kosong
                if (
                    empty($row['article_code']) &&
                    empty($row['ean_code']) &&
                    empty($row['article_description'])
                ) {
                    continue;
                }

                Loberon::create([
                    'buyer' => $row['buyer'] ?? null,

                    'article_code' => $row['article_code'] ?? null,
                    'color_id' => $row['color_id'] ?? null,
                    'size_id' => $row['size_id'] ?? null,
                    'ean_code' => $row['ean_code'] ?? null,

                    'article_description' =>
                        $row['article_description'] ?? null,

                    'hts_code' => $row['hts_code'] ?? null,

                    'bulky_goods_class' =>
                        $row['bulky_goods_class'] ?? null,

                    'net_wt_crt' =>
                        $this->number($row['net_wt_crt'] ?? 0),

                    'gross_wt_crt' =>
                        $this->number($row['gross_wt_crt'] ?? 0),

                    'total_number_cartons' =>
                        $this->number($row['total_number_cartons'] ?? 0),

                    'carton_l' =>
                        $this->number($row['carton_l'] ?? 0),

                    'carton_w' =>
                        $this->number($row['carton_w'] ?? 0),

                    'carton_h' =>
                        $this->number($row['carton_h'] ?? 0),

                    'volume_carton' =>
                        $this->number($row['volume_carton'] ?? 0),

                    'qty' =>
                        $this->number($row['qty'] ?? 0),

                    'price_per_article' =>
                        $this->number($row['price_per_article'] ?? 0),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Database Loberon berhasil ditambahkan.',
        ]);
    }

    /**
     * INLINE EDIT
     */
    public function update(Request $request, $id)
    {
        $item = Loberon::findOrFail($id);

        $field = $request->input('field');
        $value = $request->input('value');

        $allowed = [
            'buyer',
            'article_code',
            'color_id',
            'size_id',
            'ean_code',
            'article_description',
            'hts_code',
            'bulky_goods_class',
            'net_wt_crt',
            'gross_wt_crt',
            'total_number_cartons',
            'carton_l',
            'carton_w',
            'carton_h',
            'volume_carton',
            'qty',
            'price_per_article',
        ];

        if (!in_array($field, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Field tidak diperbolehkan.',
            ], 422);
        }

        $numericFields = [
            'net_wt_crt',
            'gross_wt_crt',
            'total_number_cartons',
            'carton_l',
            'carton_w',
            'carton_h',
            'volume_carton',
            'qty',
            'price_per_article',
        ];

        if (in_array($field, $numericFields, true)) {
            $value = $this->number($value);
        }

        $item->{$field} = $value;
        $item->save();

        return response()->json([
            'success' => true,
            'value' => $item->{$field},
        ]);
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {
        $item = Loberon::findOrFail($id);

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    /**
     * Convert number:
     * 1,234.56
     * 1.234,56
     * 1234.56
     */
    private function number($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = trim((string) $value);

        /*
         * Kalau format Indonesia:
         * 1.234,56
         */
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');

            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value)
            ? $value
            : 0;
    }
}