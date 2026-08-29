<?php

namespace App\Helpers\ProductionMonitoring;

use App\Models\InspectSchedule;
use App\Models\Po;
use App\Models\ProductionTimeline;
use App\Models\Spk;
use Illuminate\Http\Request;

class MonitoringHelper
{
    /**
     * Kategori yang dipakai oleh itemData.
     *
     * PENTING:
     * Tahap ini sengaja TIDAK membatasi header.
     * Struktur output tetap sama seperti index lama.
     */
    public static function categories(): array
    {
        return [
            'rangka' => 'rangka',
            'anyam' => 'anyam',
            'unfinish' => 'unfinish',
            'final' => 'final',
            'decor' => 'decor',
            'accessories' => 'accessories',
            'packaging' => 'box',
            'box' => 'box',
        ];
    }

    /**
     * Mapping kategori inspection.
     */
    public static function inspectionCategoryMap(): array
    {
        return [
            4 => 'rangka',
            5 => 'anyam',
            6 => 'unfinish',
            7 => 'final',
        ];
    }

    /**
     * Ambil state/filter index.
     */
    public static function filters(Request $request): array
    {
        $sort = strtolower(
            $request->input('sort', 'desc')
        );

        if (!in_array($sort, ['asc', 'desc'], true)) {
            $sort = 'desc';
        }

        $brand = strtolower(
            trim($request->input('brand', 'all'))
        );

        return [
            'searchPo' => $request->search_po,
            'selectedDate' => $request->tanggal,
            'sort' => $sort,
            'brand' => $brand,
        ];
    }

    /**
     * Query tanggal inspection.
     */
    public static function dates(string|array|null $searchPo)
    {
        return InspectSchedule::query()
            ->when($searchPo, function ($q) use ($searchPo) {
                $q->whereHas('po', function ($qq) use ($searchPo) {
                    $qq->where(
                        'order_no',
                        'like',
                        '%' . $searchPo . '%'
                    );
                });
            })
            ->select('tanggal_inspect')
            ->distinct()
            ->orderBy('tanggal_inspect')
            ->pluck('tanggal_inspect');
    }

    /**
     * Query PO beserta relation yang dipakai index.
     */
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

    /**
     * Ambil seluruh detail_po_id dari PO hasil query.
     */
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

    /**
     * Query inspection dan group berdasarkan detail_po_id.
     */
    public static function inspections(
        array $detailPoIds,
        ?string $selectedDate
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

    /**
     * Query production timeline dan group berdasarkan detail_po_id.
     */
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

    /**
     * Semua SPK untuk lookup berdasarkan id.
     */
    public static function spks()
    {
        return Spk::query()
            ->get()
            ->keyBy('id');
    }

    /**
     * Total passed/rejected per SPK + detail PO.
     */
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

    /**
     * Build seluruh $datas dengan behavior index lama.
     *
     * Tidak ada dynamic header pada tahap ini.
     * Yang dibentuk tetap:
     * - item_name
     * - item_image
     * - qty
     * - spks
     * - *_pass
     * - *_reject
     * - *_in
     * - *_out
     * - anyam_components
     * - anyam_component_count
     */
    public static function buildData(
        $pos,
        $allInspects,
        $allInventories,
        $allSpks,
        $inspectTotals
    ): array {
        $categories = self::categories();
        $inspectionCategoryMap =
            self::inspectionCategoryMap();

        $datas = [];

        foreach ($pos as $po) {
            $poId = $po->id;

            $datas[$poId] = [
                'po_number' =>
                    $po->order_no,

                'buyer_name' =>
                    $po->company_name
                    ?? $po->company_name
                    ?? $po->buyer
                    ?? '',

                'items' => [],
            ];

            foreach ($po->detailPos as $detailPo) {
                $itemData = self::buildItem(
                    $po,
                    $detailPo,
                    $categories,
                    $inspectionCategoryMap,
                    $allInspects,
                    $allInventories,
                    $allSpks,
                    $inspectTotals
                );

                $datas[$poId]['items'][] =
                    $itemData;
            }
        }

        return $datas;
    }

    /**
     * Build satu item.
     */
    private static function buildItem(
        $po,
        $detailPo,
        array $categories,
        array $inspectionCategoryMap,
        $allInspects,
        $allInventories,
        $allSpks,
        $inspectTotals
    ): array {
        $detail =
            $detailPo->detail ?? [];

        if (is_string($detail)) {
            $detail = json_decode(
                $detail,
                true
            );
        }

        $qty =
            $detail['qty']
            ?? 0;

        $itemName =
            $detail['description']
            ?? $detail['nama']
            ?? $detail['item']
            ?? '-';

        $image =
            $detail['photo']
            ?? null;

        $itemData = [
            'item_name' =>
                $itemName,

            'item_image' =>
                $image,

            'qty' =>
                $qty,

            'spks' =>
                [],
        ];

        /*
        |--------------------------------------------------------------------------
        | INITIAL CATEGORY FIELDS
        |--------------------------------------------------------------------------
        */
        foreach ($categories as $category) {
            $itemData[
                $category . '_pass'
            ] = 0;

            $itemData[
                $category . '_reject'
            ] = 0;

            $itemData[
                $category . '_in'
            ] = 0;

            $itemData[
                $category . '_out'
            ] = 0;
        }

        $inspects =
            $allInspects[
                (string) $detailPo->id
            ] ?? collect();

        /*
        |--------------------------------------------------------------------------
        | SPK CATEGORY QTY + COMPONENT GROUPS
        |--------------------------------------------------------------------------
        */
        $spkCategoryQty = [];
        $componentGroups = [];

        foreach ($po->spks as $spk) {
            $spkData = self::decodeData(
                $spk->data
            );

            $kategoriSpk = strtolower(
                trim(
                    $spkData['kategori'] ?? ''
                )
            );

            $spkPrefix =
                self::resolveCategory(
                    $kategoriSpk
                );

            if (!$spkPrefix) {
                continue;
            }

            foreach (
                ($spkData['items'] ?? [])
                as $spkItem
            ) {
                if (
                    ($spkItem['detail_po_id'] ?? null)
                    != $detailPo->id
                ) {
                    continue;
                }

                $qtySpk = (float) (
                    $spkItem['qty'] ?? 0
                );

                /*
                 * Simpan definisi komponen dari custom_columns.
                 */
                $customColumns =
                    $spkItem['custom_columns']
                    ?? [];

                if (is_string($customColumns)) {
                    $decodedColumns =
                        json_decode(
                            $customColumns,
                            true
                        );

                    $customColumns =
                        is_array($decodedColumns)
                            ? $decodedColumns
                            : [];
                }

                if (
                    is_array($customColumns)
                    && !empty($customColumns)
                ) {
                    foreach (
                        $customColumns
                        as $component
                    ) {
                        if (!is_array($component)) {
                            continue;
                        }

                        $componentName =
                            self::componentName(
                                $component
                            );

                        if ($componentName === '') {
                            continue;
                        }

                        $componentQty =
                            isset($component['pcs'])
                            && $component['pcs'] !== ''
                            && is_numeric(
                                $component['pcs']
                            )
                                ? (float) $component['pcs']
                                : $qtySpk;

                        $componentGroups[
                            $spkPrefix
                        ][] = [
                            'name' =>
                                $componentName,

                            'qty_spk' =>
                                $componentQty,
                        ];
                    }
                }

                $spkCategoryQty[
                    $spkPrefix
                ] =
                    (
                        $spkCategoryQty[
                            $spkPrefix
                        ] ?? 0
                    )
                    + $qtySpk;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KOMPONEN ANYAM UNTUK TAMPILAN
        |--------------------------------------------------------------------------
        */
        $anyamComponents = [];

        foreach (
            ($componentGroups['anyam'] ?? [])
            as $component
        ) {
            $name = trim(
                (string) (
                    $component['name'] ?? ''
                )
            );

            if ($name === '') {
                continue;
            }

            $normalized = strtolower(
                preg_replace(
                    '/\s+/',
                    ' ',
                    $name
                )
            );

            if ($normalized === '') {
                continue;
            }

            $anyamComponents[
                $normalized
            ] = $name;
        }

        $itemData[
            'anyam_components'
        ] =
            array_values(
                $anyamComponents
            );

        $itemData[
            'anyam_component_count'
        ] =
            count($anyamComponents);

        /*
        |--------------------------------------------------------------------------
        | INSPECTION PASS / REJECT
        |--------------------------------------------------------------------------
        */
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

            /*
             * Kategori yang boleh menerima pass.
             */
            $allocationCategories = [
                $inspectionPrefix
            ];

            /*
             * Inspection Rangka:
             * Rangka -> Rangka + Accessories
             * apabila ada SPK accessories/kaki kayu.
             */
            if (
                $inspectionPrefix === 'rangka'
                &&
                !empty(
                    $spkCategoryQty[
                        'accessories'
                    ]
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
             * Fallback: pertahankan behavior lama.
             */
            if (
                $remainingPass > 0
                &&
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
             * Reject mengikuti kategori inspection asli.
             */
            $itemData[
                $inspectionPrefix
                . '_reject'
            ] += $rejected;
        }

        /*
        |--------------------------------------------------------------------------
        | DETAIL SPK UNTUK MODAL BLADE
        |--------------------------------------------------------------------------
        */
        foreach ($po->spks as $spk) {
            $spkData =
                self::decodeData(
                    $spk->data
                );

            $spkItems =
                $spkData['items']
                ?? [];

            foreach ($spkItems as $spkItem) {
                if (
                    ($spkItem['detail_po_id'] ?? null)
                    != $detailPo->id
                ) {
                    continue;
                }

                $inspectTotalKey =
                    $spk->id
                    . '_'
                    . $detailPo->id;

                $inspectTotal =
                    $inspectTotals[
                        $inspectTotalKey
                    ] ?? null;

                $itemData['spks'][] = [
                    'id' =>
                        $spk->id,

                    'supplier' =>
                        $spkData['sup']
                        ?? '-',

                    'kategori' =>
                        $spkData['kategori']
                        ?? '-',

                    'jenis_asli' =>
                        $spkData['kategori']
                        ?? '-',

                    'no_spk' =>
                        $spkData['no_spk']
                        ?? '-',

                    'status' =>
                        $spk->status
                        ?? '-',

                    'harga' =>
                        $spkItem['harga']
                        ?? 0,

                    'qty' =>
                        $spkItem['qty']
                        ?? 0,

                    'detail_po_id' =>
                        $detailPo->id,

                    'inspect_schedule_id' =>
                        $inspectTotal
                            ? true
                            : false,

                    'passed' =>
                        $inspectTotal
                            ->total_passed
                        ?? 0,

                    'rejected' =>
                        $inspectTotal
                            ->total_rejected
                        ?? 0,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | INVENTORY IN / OUT
        |--------------------------------------------------------------------------
        */
        $inventories =
            $allInventories[
                $detailPo->id
            ] ?? collect();

        foreach ($inventories as $inventory) {
            $spkInv =
                $allSpks[
                    $inventory->spk_id
                ] ?? null;

            if (!$spkInv) {
                continue;
            }

            $spkInvData =
                self::decodeData(
                    $spkInv->data
                );

            /*
             * Inventory tetap berdasarkan kategori SPK.
             */
            $kategoriInv =
                strtolower(
                    trim(
                        $spkInvData['kategori']
                        ?? ''
                    )
                );

            $prefix =
                self::resolveCategory(
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
                ] +=
                    $qtyInventory;
            } else {
                $itemData[
                    $prefix . '_out'
                ] +=
                    $qtyInventory;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ELIMINASI KELEBIHAN KOMPONEN
        |--------------------------------------------------------------------------
        */
        foreach (
            $componentGroups
            as $componentCategory => $components
        ) {
            /*
             * Anyam menggunakan total inventory raw.
             * Pembagian display dilakukan Blade.
             */
            if ($componentCategory === 'anyam') {
                continue;
            }

            /*
             * Hindari duplicate component.
             */
            $uniqueComponents = [];

            foreach (
                $components
                as $component
            ) {
                $normalizedName =
                    strtolower(
                        trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                $component['name']
                                ?? ''
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
                                $component['name']
                                ?? ''
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

                foreach (
                    $inventories
                    as $inventory
                ) {
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
                     * Remark kosong dianggap item utama.
                     * Hanya komponen pertama menerima fallback.
                     */
                    if ($remark === '') {
                        if ($index === 0) {
                            $qtyInComponent +=
                                $qty;
                        }

                        continue;
                    }

                    if (
                        $remark === $target
                        ||
                        str_contains(
                            $remark,
                            $target
                        )
                        ||
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
                 * Effective IN = component balance paling kecil.
                 */
                $effectiveIn =
                    min($componentIn);

                $itemData[
                    $componentCategory
                    . '_in'
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

    /**
     * Normalisasi kategori SPK/inventory.
     *
     * Behavior mengikuti index lama.
     */
    public static function resolveCategory(
        ?string $kategori
    ): ?string {
        $kategori =
            strtolower(
                trim($kategori ?? '')
            );

        if ($kategori === '') {
            return null;
        }

        /*
         * Kaki kayu harus dicek sebelum rangka.
         */
        if (
            str_contains(
                $kategori,
                'kaki kayu'
            )
            ||
            str_contains(
                $kategori,
                'kayu kaki'
            )
        ) {
            return 'accessories';
        }

        if (
            str_contains(
                $kategori,
                'aksesori'
            )
            ||
            str_contains(
                $kategori,
                'aksesor'
            )
            ||
            str_contains(
                $kategori,
                'accessor'
            )
        ) {
            return 'accessories';
        }

        if (
            str_contains(
                $kategori,
                'rangka'
            )
        ) {
            return 'rangka';
        }

        if (
            str_contains(
                $kategori,
                'anyam'
            )
        ) {
            return 'anyam';
        }

        if (
            str_contains(
                $kategori,
                'unfinish'
            )
        ) {
            return 'unfinish';
        }

        if (
            str_contains(
                $kategori,
                'final'
            )
        ) {
            return 'final';
        }

        if (
            str_contains(
                $kategori,
                'decor'
            )
            ||
            str_contains(
                $kategori,
                'dekor'
            )
        ) {
            return 'decor';
        }

        if (
            str_contains(
                $kategori,
                'box'
            )
            ||
            str_contains(
                $kategori,
                'packaging'
            )
        ) {
            return 'box';
        }

        return null;
    }

    /**
     * Ambil nama component dari berbagai kemungkinan key
     * yang dipakai custom_columns.
     */
    private static function componentName(
        array $component
    ): string {
        foreach ([
            'nama',
            'name',
            'nama_material',
            'nama_bahan',
            'bahan',
            'triplek',
            'finishing',
            'komponen',
            'component',
            'description',
            'proses',
            'process',
            'nama_proses',
            'jenis_proses',
        ] as $key) {
            $value =
                $component[$key]
                ?? null;

            if (
                is_string($value)
                &&
                trim($value) !== ''
            ) {
                $clean =
                    strtolower(
                        trim($value)
                    );

                if (
                    !in_array(
                        $clean,
                        [
                            '-',
                            'null',
                            'undefined',
                            'n/a',
                            'na',
                        ],
                        true
                    )
                ) {
                    return trim($value);
                }
            }
        }

        return '';
    }

    /**
     * Decode kolom JSON yang bisa berupa string/array.
     */
    private static function decodeData($data): array
    {
        if (is_string($data)) {
            $decoded =
                json_decode(
                    $data,
                    true
                );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        return is_array($data)
            ? $data
            : [];
    }
}