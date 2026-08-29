<?php

namespace App\Helpers\ProductionMonitoring;

class MonitoringInventoryHelper
{
    public static function apply(
        array $itemData,
        $inventories,
        $allSpks
    ): array {
        foreach ($inventories as $inventory) {
            $spkInv =
                $allSpks[
                    $inventory->spk_id
                ] ?? null;

            if (!$spkInv) {
                continue;
            }

            $spkInvData =
                MonitoringSpkHelper::decodeData(
                    $spkInv->data
                );

            /*
             * Inventory tetap berdasarkan kategori SPK,
             * bukan kategori inspection.
             */
            $kategoriInv =
                strtolower(
                    trim(
                        $spkInvData['kategori']
                        ?? ''
                    )
                );

            $prefix =
                MonitoringSpkHelper::resolveCategory(
                    $kategoriInv
                );

            if (!$prefix) {
                continue;
            }

            $type =
                strtolower(
                    trim(
                        $inventory->type
                        ?? ''
                    )
                );

            $qtyInventory =
                (float) (
                    $inventory->qty
                    ?? 0
                );

            if ($type === 'in') {
                $itemData[
                    $prefix . '_in'
                ] += $qtyInventory;
            } else {
                $itemData[
                    $prefix . '_out'
                ] += $qtyInventory;
            }
        }

        return $itemData;
    }
}
