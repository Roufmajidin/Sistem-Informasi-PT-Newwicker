<?php

namespace App\Helpers;

class ProductionMonitoringHelper
{
    /**
     * Classify kategori SPK ke kategori Production Monitoring.
     *
     * Return:
     * [
     *     'original'        => kategori asli,
     *     'category'        => kategori monitoring,
     *     'classification'  => hasil klasifikasi,
     *     'is_exception'    => bool,
     *     'exception_rule'  => rule yang digunakan,
     * ]
     */
    public static function classifySpkCategory(?string $kategori): array
    {
        /*
        |--------------------------------------------------------------------------
        | NORMALIZE
        |--------------------------------------------------------------------------
        */

        $original = trim(
            strtolower(
                $kategori ?? ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        if ($original === '') {
            return [
                'original' => $kategori,
                'category' => null,
                'classification' => 'other',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | HANGER
        |--------------------------------------------------------------------------
        |
        | HANGER TIDAK BOLEH MASUK MONITORING.
        |
        | Contoh:
        | HANGER
        | HANGER BESI
        | AKSESORIS HANGER
        | AKSESORIS HANGER BESI
        |
        */

        if (
            str_contains(
                $original,
                'hanger'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => null,
                'classification' => 'excluded',
                'is_exception' => true,
                'exception_rule' => 'hanger',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PLAT
        |--------------------------------------------------------------------------
        |
        | PLAT TIDAK BOLEH MASUK MONITORING.
        |
        | Contoh:
        | PLAT
        | PLAT BESI
        | PLAT SENG
        | AKSESORIS PLAT
        | AKSESORIS PLAT BESI
        |
        */

        if (
            str_contains(
                $original,
                'plat'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => null,
                'classification' => 'excluded',
                'is_exception' => true,
                'exception_rule' => 'plat',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | FINISHING
        |--------------------------------------------------------------------------
        |
        | FINISHING BUKAN ACCESSORIES.
        |
        | Contoh:
        | FINISHING
        | FINISHING TOMO
        | FINISHING DARTO
        |
        */

        if (
            str_contains(
                $original,
                'finishing'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'finishing',
                'classification' => 'finishing',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Cermin / Mirror
        |--------------------------------------------------------------------------
        |
        | Cermin secara khusus masuk ACCESSORIES.
        |
        | Contoh:
        | Cermin
        | Cermin Bilbao
        | Mirror
        | Mirror Bilbao
        | Mirror Natural Rattan
        |
        */

        if (
            str_contains(
                $original,
                'cermin'
            )
            ||
            str_contains(
                $original,
                'mirror'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'accessories',
                'classification' => 'accessories',
                'is_exception' => true,
                'exception_rule' => 'cermin/mirror',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ACCESSORIES UMUM
        |--------------------------------------------------------------------------
        |
        | PENTING:
        | Bagian ini diletakkan SETELAH pengecekan
        | hanger dan plat.
        |
        | Jadi:
        |
        | AKSESORIS HANGER BESI
        | -> excluded
        |
        | AKSESORIS PLAT BESI
        | -> excluded
        |
        | AKSESORIS KARET
        | -> accessories
        |
        */

        if (
            str_contains(
                $original,
                'aksesori'
            )
            ||
            str_contains(
                $original,
                'aksesor'
            )
            ||
            str_contains(
                $original,
                'accessor'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'accessories',
                'classification' => 'accessories',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | RANGKA
        |--------------------------------------------------------------------------
        */

        $rangka = [
            'rangka',
            'rangka besi',
            'rangka kayu',
            'rangka rotan',
            'rangka alumunium',
            'rangka aluminium',
            'rangka triplek',
        ];

        foreach ($rangka as $keyword) {

            if (
                str_contains(
                    $original,
                    $keyword
                )
            ) {
                return [
                    'original' => $kategori,
                    'category' => 'rangka',
                    'classification' => 'rangka',
                    'is_exception' => false,
                    'exception_rule' => null,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ANYAM
        |--------------------------------------------------------------------------
        */

        $anyam = [
            'anyam',
            'anyam sintetis',
            'anyam karakter',
        ];

        foreach ($anyam as $keyword) {

            if (
                str_contains(
                    $original,
                    $keyword
                )
            ) {
                return [
                    'original' => $kategori,
                    'category' => 'anyam',
                    'classification' => 'anyam',
                    'is_exception' => false,
                    'exception_rule' => null,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | DECOR / DEKOR
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $original,
                'decor'
            )
            ||
            str_contains(
                $original,
                'dekor'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'decor',
                'classification' => 'decor',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PACKAGING
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | PACKAGING
        | CARTON
        | CARTON BOX
        | BOX
        | CARTON BOX PACKAGING
        |
        */

        if (
            str_contains(
                $original,
                'packaging'
            )
            ||
            str_contains(
                $original,
                'carton'
            )
            ||
            str_contains(
                $original,
                'box'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'packaging',
                'classification' => 'packaging',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | UNFINISH
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $original,
                'unfinish'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'unfinish',
                'classification' => 'unfinish',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | FINAL
        |--------------------------------------------------------------------------
        */

        if (
            str_contains(
                $original,
                'final'
            )
        ) {
            return [
                'original' => $kategori,
                'category' => 'final',
                'classification' => 'final',
                'is_exception' => false,
                'exception_rule' => null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | OTHER
        |--------------------------------------------------------------------------
        */

        return [
            'original' => $kategori,
            'category' => null,
            'classification' => 'other',
            'is_exception' => false,
            'exception_rule' => null,
        ];
    }
}