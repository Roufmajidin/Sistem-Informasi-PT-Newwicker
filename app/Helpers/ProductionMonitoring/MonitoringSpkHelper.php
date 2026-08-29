<?php

namespace App\Helpers\ProductionMonitoring;

class MonitoringSpkHelper
{
    public static function resolveCategory(?string $kategori): ?string
    {
        $kategori = strtolower(trim($kategori ?? ''));

        if ($kategori === '') {
            return null;
        }

        /*
         * KAKI KAYU harus dicek sebelum RANGKA.
         */
        if (
            str_contains($kategori, 'kaki kayu') ||
            str_contains($kategori, 'kayu kaki')
        ) {
            return 'accessories';
        }

        if (
            str_contains($kategori, 'aksesori') ||
            str_contains($kategori, 'aksesor') ||
            str_contains($kategori, 'accessor')
        ) {
            return 'accessories';
        }

        if (str_contains($kategori, 'rangka')) {
            return 'rangka';
        }

        if (str_contains($kategori, 'anyam')) {
            return 'anyam';
        }

        if (str_contains($kategori, 'unfinish')) {
            return 'unfinish';
        }

        if (str_contains($kategori, 'final')) {
            return 'final';
        }

        if (
            str_contains($kategori, 'decor') ||
            str_contains($kategori, 'dekor')
        ) {
            return 'decor';
        }

        if (
            str_contains($kategori, 'box') ||
            str_contains($kategori, 'packaging')
        ) {
            return 'box';
        }

        return null;
    }

    public static function prepareSpkCategories(
        $po,
        $detailPo
    ): array {
        $spkCategoryQty = [];
        $componentGroups = [];

        foreach ($po->spks as $spk) {
            $spkData = self::decodeData($spk->data);

            $spkPrefix = self::resolveCategory(
                $spkData['kategori'] ?? ''
            );

            if (!$spkPrefix) {
                continue;
            }

            foreach (($spkData['items'] ?? []) as $spkItem) {
                if (
                    ($spkItem['detail_po_id'] ?? null)
                    != $detailPo->id
                ) {
                    continue;
                }

                $qtySpk = (float) (
                    $spkItem['qty'] ?? 0
                );

                $customColumns =
                    $spkItem['custom_columns'] ?? [];

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
                    is_array($customColumns) &&
                    !empty($customColumns)
                ) {
                    foreach ($customColumns as $component) {
                        if (!is_array($component)) {
                            continue;
                        }

                        $componentName =
                            self::componentName($component);

                        if ($componentName === '') {
                            continue;
                        }

                        $componentQty =
                            isset($component['pcs']) &&
                            $component['pcs'] !== '' &&
                            is_numeric($component['pcs'])
                                ? (float) $component['pcs']
                                : $qtySpk;

                        $componentGroups[$spkPrefix][] = [
                            'name' => $componentName,
                            'qty_spk' => $componentQty,
                        ];
                    }
                }

                $spkCategoryQty[$spkPrefix] =
                    ($spkCategoryQty[$spkPrefix] ?? 0)
                    + $qtySpk;
            }
        }

        return [
            'spkCategoryQty' => $spkCategoryQty,
            'componentGroups' => $componentGroups,
        ];
    }

    public static function buildSpkList(
        $po,
        $detailPo,
        $inspectTotals
    ): array {
        $spks = [];

        foreach ($po->spks as $spk) {
            $spkData = self::decodeData($spk->data);

            $spkItems =
                $spkData['items'] ?? [];

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

                $spks[] = [
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

        return $spks;
    }

    public static function anyamComponents(
        array $componentGroups
    ): array {
        $anyamComponents = [];

        foreach (
            ($componentGroups['anyam'] ?? [])
            as $component
        ) {
            $name = trim(
                (string) ($component['name'] ?? '')
            );

            if ($name === '') {
                continue;
            }

            $normalized = strtolower(
                preg_replace('/\s+/', ' ', $name)
            );

            if ($normalized === '') {
                continue;
            }

            $anyamComponents[$normalized] =
                $name;
        }

        return array_values($anyamComponents);
    }

    public static function componentName(
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
                $component[$key] ?? null;

            if (
                is_string($value) &&
                trim($value) !== ''
            ) {
                $clean =
                    strtolower(trim($value));

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

    public static function decodeData($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode(
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
