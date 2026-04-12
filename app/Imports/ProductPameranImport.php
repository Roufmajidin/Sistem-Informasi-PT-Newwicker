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
        $this->exhibitionId = (int) $exhibitionId;
    }

    public function chunkSize(): int
    {
        return 300;
    }

    /**
     * 🔥 GLOBAL CLEANER (ANTI ERROR MYSQL DECIMAL)
     */
    private function cleanValue($value, $isNumeric = false)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        // Hapus NBSP
        $value = str_replace("\xC2\xA0", '', $value);

        // Hapus unicode space lainnya
        $value = preg_replace('/\x{00A0}/u', '', $value);

        // Trim
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($isNumeric) {

            // Hapus format accounting (1,421,832)
            $value = str_replace(',', '', $value);

            // Hapus karakter selain angka dan titik
            $value = preg_replace('/[^0-9.\-]/', '', $value);

            return is_numeric($value) ? $value : null;
        }

        return $value;
    }

    public function collection(Collection $rows)
    {
        $rowsArray = $rows->toArray();

        if (count($rowsArray) < 3) {
            Log::warning("File kosong atau header tidak valid.");
            return;
        }

        $header1 = $rowsArray[0];
        $header2 = $rowsArray[1];

        // Isi header kosong
        $lastNonEmpty = '';
        foreach ($header1 as $i => $h1) {
            $lastNonEmpty = ($h1 !== null && $h1 !== '') ? $h1 : $lastNonEmpty;
            $header1[$i]  = $lastNonEmpty;
        }

        $map = [
            'nr'                       => 'nr',
            'photo'                    => 'photo',
            'article_code'             => 'article_code',
            'name'                     => 'name',
            'categories'               => 'categories',
            'sub_categories'           => 'sub_categories',
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
            'composition'              => 'composition',
            'finishing'                => 'finishing',
            'qty'                      => 'qty',
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
            'value_in_usd'             => 'value_in_usd',
        ];

        $flattenedHeader = [];

        foreach ($header1 as $i => $h1) {
            $h2 = $header2[$i] ?? '';

            $h1 = preg_replace('/_?unnamed.*$/i', '', $h1);
            $h2 = preg_replace('/_?unnamed.*$/i', '', $h2);

            $colName = trim(strtolower(
                preg_replace('/[^a-z0-9]+/i', '_', trim($h1 . '_' . $h2))
            ), '_');

            $flattenedHeader[$i] = $map[$colName] ?? null;
        }

        $batchInsert = [];

        for ($i = 2; $i < count($rowsArray); $i++) {

            $rowData = $rowsArray[$i];
            $normalized = [];

            foreach ($rowData as $colIndex => $value) {

                $key = $flattenedHeader[$colIndex] ?? null;
                if (!$key) continue;

                // Tentukan apakah numeric
                $numericFields = [
                    'nr','item_w','item_d','item_h',
                    'packing_w','packing_d','packing_h',
                    'qty','loadability_20','loadability_40','loadability_40hc',
                    'rangka','anyam','finishing_powder_coating',
                    'accessories_final','electricity','packingbox',
                    'glass','cushion','total',
                    'fob_cost_20','fob_cost_40','fob_cost_40hc',
                    'total_production_cost_20','total_production_cost_40',
                    'total_production_cost_40hc','cogusd_rate_14500',
                    'price_cushion','price_item','fob_jakarta_in_usd','value_in_usd'
                ];

                $normalized[$key] = $this->cleanValue(
                    $value,
                    in_array($key, $numericFields)
                );
            }

            if (empty($normalized['article_code'])) {
                continue;
            }

            // Hitung ulang CBM
            if (
                isset($normalized['packing_w']) &&
                isset($normalized['packing_d']) &&
                isset($normalized['packing_h'])
            ) {
                $normalized['cbm'] = round(
                    ($normalized['packing_w'] *
                     $normalized['packing_d'] *
                     $normalized['packing_h']) / 1000000,
                    6
                );
            }

            $normalized['exhibition_id'] = $this->exhibitionId;
            $normalized['created_at']    = now();
            $normalized['updated_at']    = now();

            $batchInsert[] = $normalized;
        }

        if (!empty($batchInsert)) {
            ProductPameran::insert($batchInsert);
            Log::info("✅ Inserted " . count($batchInsert) . " rows successfully.");
        }
    }
}
