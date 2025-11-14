<?php
namespace App\Imports;

use App\Models\ProductPameran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductPameranImport implements ToCollection, WithCalculatedFormulas, WithChunkReading
{
    private $exhibitionId;

    public function __construct($exhibitionId = null)
    {
        $this->exhibitionId = $exhibitionId;
    }

    public function chunkSize(): int
    {
        return 200; // proses per 200 row biar efisien
    }

    public function collection(Collection $rows)
    {
        $rowsArray = $rows->toArray();
        Log::info("🚀 Collection dipanggil, total rows: " . count($rows));

        if (count($rowsArray) < 2) {
            Log::error('Header Excel kurang dari 2 baris.');
            return;
        }

        $header1 = $rowsArray[0];
        $header2 = $rowsArray[1];
        Log::info('Header Excel', [
            'header1' => $header1,
            'header2' => $header2,
        ]);

        // Isi header kosong dengan parent sebelumnya
        $lastNonEmpty = '';
        foreach ($header1 as $colIndex => $h1) {
            $lastNonEmpty       = ($h1 !== null && $h1 !== '') ? $h1 : $lastNonEmpty;
            $header1[$colIndex] = $lastNonEmpty;
        }

        // Mapping database
        $map = [
            'nr'                       => 'nr',
            'photo'                    => 'photo',
            'article_code'             => 'article_code',
            'name'                     => 'name',
            'categories'               => 'categories',
            'sub_categories'           => 'sub_categories',
            'subcategories'            => 'subcategories',
            'remark'                   => 'remark',
            'item_dimension_w'         => 'item_w',
            'item_dimension_d'         => 'item_d',
            'item_dimension_h'         => 'item_h',
            'packing_dimension_w'      => 'packing_w',
            'packing_dimension_d'      => 'packing_d',
            'packing_dimension_h'      => 'packing_h',

            'size_of_set_set_2'        => 'set2',
            'size_of_set_set_3'        => 'set3',
            'size_of_set_set_4'        => 'set4',
            'size_of_set_set_5'        => 'set5',
            'set2'                     => 'set2',
            'set3'                     => 'set3',
            'set4'                     => 'set4',
            'set5'                     => 'set5',

            'composition'              => 'composition',
            'finishing'                => 'finishing',
            'qty'                      => 'qty',
            'cbm'                      => 'cbm',
            'total_cbm'                => 'cbm',
            'loadability_20'           => 'loadability_20',
            'loadability_40'           => 'loadability_40',
            'loadability_40_hc'        => 'loadability_40hc',
            'rangka'                   => 'rangka',
            'anyam'                    => 'anyam',
            'finishing_powder_coating' => 'finishing_powder_coating',
            'accessories_final'        => 'accessories_final',
            'electricity'              => 'electricity',
            'packing_box'              => 'packingbox',
            'glass'                    => 'glass',
            'cushion'                  => 'cushion',
            'total'                    => 'total',
            'fob_cost_20'              => 'fob_cost_20',
            'fob_cost_40'              => 'fob_cost_40',
            'fob_cost_40_hc'           => 'fob_cost_40hc',
            'biaya_produksi_20'        => 'total_production_cost_20',
            'biaya_produksi_40'        => 'total_production_cost_40',
            'biaya_produksi_40_hc'     => 'total_production_cost_40hc',
            'cog_item_rate_14_500'     => 'cogusd_rate_14500',
            'price_cushion'            => 'price_cushion',
            'price_item'               => 'price_item',
            'fob_jakarta_in_usd'       => 'fob_jakarta_in_usd',
            'cog_cushion_rate_14_500'  => 'cog_cushion_rate_14500',
            'value_in_usd'             => 'value_in_usd',
        ];

        // Flatten header
        $flattenedHeader = [];
        foreach ($header1 as $colIndex => $h1) {
            $h2 = $header2[$colIndex] ?? '';
            $h1 = preg_replace('/_?unnamed.*$/i', '', $h1);
            $h2 = preg_replace('/_?unnamed.*$/i', '', $h2);

            $h1 = trim(preg_replace('/\s+/', ' ', $h1));
            $h2 = trim(preg_replace('/\s+/', ' ', $h2));

            $colName = $h2 !== '' ? $h1 . '_' . $h2 : $h1;
            $colName = strtolower($colName);
            $colName = preg_replace('/[^a-z0-9]+/i', '_', $colName);
            $colName = trim($colName, '_');

            $flattenedHeader[$colIndex] = $map[$colName] ?? $colName;
        }
        Log::info('Flattened header', $flattenedHeader);

        $batchInsert = [];

        for ($i = 2; $i < count($rowsArray); $i++) {
            $rowData    = $rowsArray[$i];
            $normalized = [];

            foreach ($rowData as $colIndex => $value) {
                $key = $flattenedHeader[$colIndex] ?? 'col_' . $colIndex;

                // Numerik only (tidak termasuk set2–set5 karena bisa string)
                if (in_array($key, [
                    'nr',
                    'item_w',
                    'item_d',
                    'item_h',
                    'packing_w',
                    'packing_d',
                    'packing_h',
                    'qty',
                    'cbm',
                    'loadability_20',
                    'loadability_40',
                    'loadability_40hc',
                    'rangka',
                    'anyam',
                    'finishing_powder_coating',
                    'accessories_final',
                    'electricity',
                    'packingbox',
                    'cushion',
                    'total',
                    'fob_cost_20',
                    'fob_cost_40',
                    'fob_cost_40hc',
                    'total_production_cost_20',
                    'total_production_cost_40',
                    'total_production_cost_40hc',
                    'cogusd_rate_14500',
                    'price_cushion',
                    'price_item',
                    'fob_jakarta_in_usd',
                    'value_in_usd',
                ])) {
                    $normalized[$key] = is_numeric($value) ? $value : 0;
                } else {
                    $normalized[$key] = $value === '' ? null : $value;
                }
            }
            $normalized['cbm'] = round(
                ($normalized['packing_w'] * $normalized['packing_d'] * $normalized['packing_h']) / 1000000,
                6// presisi 6 digit biar sama kayak Excel
            );
            $normalized['exhibition_id'] = $this->exhibitionId ?? request('exhibition_id');
            $normalized['created_at']    = now();
            $normalized['updated_at']    = now();

            $batchInsert[] = $normalized;
        }

        if (! empty($batchInsert)) {
            Log::info("Inserted batch of " . count($batchInsert) . " rows. Example:", $batchInsert[0]);

            try {
                // 🔹 Hapus field yang tidak ada di database
                $batchInsert = collect($batchInsert)->map(function ($row) {
                    unset($row['cog_cushion_rate_14_500']);
                    unset($row['margin']);
                    unset($row['subcategories']);
                    return $row;
                })->toArray();

                ProductPameran::insert($batchInsert);
                Log::info("Successfully inserted " . count($batchInsert) . " rows");
            } catch (\Exception $e) {
                Log::error("Failed to insert batch: " . $e->getMessage(), ['batch' => $batchInsert]);
            }
        }
    }
}


