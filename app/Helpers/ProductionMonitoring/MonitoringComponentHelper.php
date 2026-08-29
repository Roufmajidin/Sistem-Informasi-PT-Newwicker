<?php

namespace App\Helpers\ProductionMonitoring;

class MonitoringComponentHelper
{
    public static function apply(
        array $itemData,
        array $componentGroups,
        $inventories
    ): array {
        foreach (
            $componentGroups
            as $componentCategory => $components
        ) {
            /*
             * Anyam IN tetap memakai total inventory.
             * Pembagian untuk display tetap dilakukan Blade,
             * sama seperti behavior controller lama.
             */
            if ($componentCategory === 'anyam') {
                continue;
            }

            $uniqueComponents = [];

            foreach ($components as $component) {
                $normalizedName =
                    strtolower(
                        trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                $component['name'] ?? ''
                            )
                        )
                    );

                if ($normalizedName === '') {
                    continue;
                }

                $uniqueComponents[
                    $normalizedName
                ] = $component;
            }

            $components =
                array_values(
                    $uniqueComponents
                );

            if (count($components) < 2) {
                continue;
            }

            $componentIn = [];

            foreach (
                $components
                as $index => $component
            ) {
                $target =
                    strtolower(
                        trim(
                            preg_replace(
                                '/[^a-z0-9]+/i',
                                ' ',
                                $component['name'] ?? ''
                            )
                        )
                    );

                $target =
                    preg_replace(
                        '/\s+/',
                        ' ',
                        $target
                    );

                if ($target === '') {
                    continue;
                }

                $qtyInComponent = 0;

                foreach ($inventories as $inventory) {
                    $type =
                        strtolower(
                            trim(
                                $inventory->type
                                ?? ''
                            )
                        );

                    if (
                        !in_array(
                            $type,
                            [
                                'in',
                                'service_masuk',
                            ],
                            true
                        )
                    ) {
                        continue;
                    }

                    $qty =
                        (float) (
                            $inventory->qty
                            ?? 0
                        );

                    if ($qty <= 0) {
                        continue;
                    }

                    $remark =
                        strtolower(
                            trim(
                                preg_replace(
                                    '/[^a-z0-9]+/i',
                                    ' ',
                                    (string) (
                                        $inventory->remark
                                        ?? ''
                                    )
                                )
                            )
                        );

                    $remark =
                        preg_replace(
                            '/\s+/',
                            ' ',
                            $remark
                        );

                    /*
                     * Remark kosong tetap dianggap
                     * sebagai item utama; hanya component
                     * pertama yang menerima fallback.
                     */
                    if ($remark === '') {
                        if ($index === 0) {
                            $qtyInComponent +=
                                $qty;
                        }

                        continue;
                    }

                    if (
                        $remark === $target ||
                        str_contains(
                            $remark,
                            $target
                        ) ||
                        str_contains(
                            $target,
                            $remark
                        )
                    ) {
                        $qtyInComponent +=
                            $qty;
                    }
                }

                /*
                 * Jangan melebihi Qty SPK component.
                 */
                $qtySpkComponent =
                    (float) (
                        $component['qty_spk']
                        ?? 0
                    );

                if ($qtySpkComponent > 0) {
                    $qtyInComponent =
                        min(
                            $qtyInComponent,
                            $qtySpkComponent
                        );
                }

                $componentIn[] =
                    $qtyInComponent;
            }

            if (count($componentIn) >= 2) {
                /*
                 * Produk efektif mengikuti
                 * component dengan balance terkecil.
                 */
                $effectiveIn =
                    min($componentIn);

                $itemData[
                    $componentCategory . '_in'
                ] =
                    min(
                        (float) (
                            $itemData[
                                $componentCategory
                                . '_in'
                            ] ?? 0
                        ) ?: $effectiveIn,
                        $effectiveIn
                    );
            }
        }

        return $itemData;
    }
}
