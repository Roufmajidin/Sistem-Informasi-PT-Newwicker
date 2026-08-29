<?php

namespace App\Helpers\ProductionMonitoring;

class MonitoringInspectionHelper
{
    public static function apply(
        array $itemData,
        $inspects,
        array $spkCategoryQty,
        array $inspectionCategoryMap
    ): array {
        foreach ($inspects as $inspect) {
            $kategoriId =
                (int) $inspect->kategori_id;

            $inspectionPrefix =
                $inspectionCategoryMap[
                    $kategoriId
                ] ?? null;

            if (!$inspectionPrefix) {
                continue;
            }

            $passed = (float) (
                $inspect->passed ?? 0
            );

            $rejected = (float) (
                $inspect->rejected ?? 0
            );

            $allocationCategories = [
                $inspectionPrefix
            ];

            /*
             * Inspection Rangka dapat dialokasikan
             * ke Rangka dan Accessories apabila
             * terdapat SPK Kaki Kayu.
             */
            if (
                $inspectionPrefix === 'rangka' &&
                !empty(
                    $spkCategoryQty['accessories']
                )
            ) {
                $allocationCategories[] =
                    'accessories';
            }

            $remainingPass =
                $passed;

            foreach (
                $allocationCategories
                as $allocationCategory
            ) {
                if ($remainingPass <= 0) {
                    break;
                }

                $capacity =
                    (float) (
                        $spkCategoryQty[
                            $allocationCategory
                        ] ?? 0
                    );

                if ($capacity <= 0) {
                    continue;
                }

                $alreadyPassed =
                    (float) (
                        $itemData[
                            $allocationCategory
                            . '_pass'
                        ] ?? 0
                    );

                $available =
                    max(
                        0,
                        $capacity
                        - $alreadyPassed
                    );

                if ($available <= 0) {
                    continue;
                }

                $allocated =
                    min(
                        $remainingPass,
                        $available
                    );

                $itemData[
                    $allocationCategory
                    . '_pass'
                ] += $allocated;

                $remainingPass -=
                    $allocated;
            }

            /*
             * Fallback tetap mengikuti perilaku lama.
             */
            if (
                $remainingPass > 0 &&
                isset(
                    $itemData[
                        $inspectionPrefix
                        . '_pass'
                    ]
                )
            ) {
                $itemData[
                    $inspectionPrefix
                    . '_pass'
                ] += $remainingPass;
            }

            /*
             * Reject tetap mengikuti kategori inspection asli.
             */
            $itemData[
                $inspectionPrefix
                . '_reject'
            ] += $rejected;
        }

        return $itemData;
    }
}
