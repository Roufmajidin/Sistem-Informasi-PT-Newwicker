<?php

namespace App\Helpers\ProductionMonitoring;

use App\Models\InspectSchedule;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\Spk;

class MonitoringQueryHelper
{
    public static function inspectionDates(array $filters)
    {
        return InspectSchedule::query()
            ->when($filters['searchPo'], function ($q) use ($filters) {
                $q->whereHas('po', function ($qq) use ($filters) {
                    $qq->where(
                        'order_no',
                        'like',
                        '%' . $filters['searchPo'] . '%'
                    );
                });
            })
            ->select('tanggal_inspect')
            ->distinct()
            ->orderBy('tanggal_inspect')
            ->pluck('tanggal_inspect');
    }

    public static function pos(array $filters)
    {
        $poQuery = Po::with([
            'detailPos',
            'spks',
        ]);

        $poQuery->orderBy(
            'release_date',
            $filters['sort']
        );

        if ($filters['searchPo']) {
            $poQuery->where(function ($q) use ($filters) {
                $q->where(
                    'order_no',
                    'like',
                    '%' . $filters['searchPo'] . '%'
                )->orWhere(
                    'company_name',
                    'like',
                    '%' . $filters['searchPo'] . '%'
                );
            });
        }

        if ($filters['brand'] === 'nw') {
            $poQuery
                ->where('order_no', 'like', 'NW%')
                ->where('order_no', 'not like', 'NWS%');
        } elseif ($filters['brand'] === 'nws') {
            $poQuery->where(
                'order_no',
                'like',
                'NWS%'
            );
        }

        return $poQuery->get();
    }

    public static function detailPoIds($pos): array
    {
        $detailPoIds = [];

        foreach ($pos as $po) {
            foreach ($po->detailPos as $detailPo) {
                $detailPoIds[] = $detailPo->id;
            }
        }

        return $detailPoIds;
    }

    public static function inspections(
        array $detailPoIds,
        ?string $selectedDate = null
    ) {
        $inspectQuery = InspectSchedule::query();

        if (!empty($detailPoIds)) {
            $inspectQuery->whereIn(
                'detail_po_id',
                $detailPoIds
            );
        } else {
            $inspectQuery->whereRaw('1 = 0');
        }

        if ($selectedDate) {
            $inspectQuery->whereDate(
                'tanggal_inspect',
                $selectedDate
            );
        }

        return $inspectQuery
            ->get()
            ->groupBy(function ($item) {
                return (string) $item->detail_po_id;
            });
    }

    public static function inventories(array $detailPoIds)
    {
        return ProductionTimeline::query()
            ->whereIn(
                'detail_po_id',
                $detailPoIds
            )
            ->get()
            ->groupBy('detail_po_id');
    }

    public static function spks()
    {
        return Spk::query()
            ->get()
            ->keyBy('id');
    }

    public static function inspectionTotals()
    {
        return InspectSchedule::query()
            ->selectRaw('
                spk_id,
                detail_po_id,
                SUM(passed) as total_passed,
                SUM(rejected) as total_rejected
            ')
            ->whereNotNull('spk_id')
            ->groupBy(
                'spk_id',
                'detail_po_id'
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->spk_id
                    . '_'
                    . $item->detail_po_id;
            });
    }

    public static function context(
        $pos,
        array $filters
    ): array {
        $detailPoIds = self::detailPoIds($pos);

        return [
            'detailPoIds' => $detailPoIds,
            'inspects' => self::inspections(
                $detailPoIds,
                $filters['selectedDate']
            ),
            'inventories' => self::inventories(
                $detailPoIds
            ),
            'spks' => self::spks(),
            'inspectTotals' => self::inspectionTotals(),
        ];
    }
}
