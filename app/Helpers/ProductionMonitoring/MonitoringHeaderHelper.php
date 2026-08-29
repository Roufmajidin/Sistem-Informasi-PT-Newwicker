<?php

namespace App\Helpers\ProductionMonitoring;

class MonitoringHeaderHelper
{
    /**
     * Urutan header monitoring.
     *
     * Kategori yang tidak memiliki SPK tidak akan ditampilkan.
     * Urutan tetap mengikuti daftar ini.
     */
    private const CATEGORY_ORDER = [
        'rangka',
        'anyam',
        'decor',
        'unfinish',
        'final',
        'accessories',
        'box',
    ];

    /**
     * Label yang ditampilkan di header Blade.
     */
    private const CATEGORY_LABELS = [
        'rangka' => 'Rangka',
        'anyam' => 'Anyam',
        'decor' => 'Decor',
        'unfinish' => 'Unfinish',
        'final' => 'Final',
        'accessories' => 'Accessories',
        'box' => 'Packaging',
    ];

    /**
     * Ambil kategori header berdasarkan SPK yang benar-benar
     * terkait dengan detail PO pada PO tersebut.
     *
     * Contoh:
     * Rangka + Anyam + Decor + Packaging
     * akan menghasilkan hanya 4 header tersebut.
     */
    public static function forPo($po): array
    {
        $detailPoIds = [];

        foreach ($po->detailPos as $detailPo) {
            $detailPoIds[(string) $detailPo->id] = true;
        }

        $found = [];

        foreach ($po->spks as $spk) {
            $spkData = self::decodeData($spk->data ?? null);

            $kategori = $spkData['kategori'] ?? '';

            $categoryKey = self::resolveCategory($kategori);

            if (!$categoryKey) {
                continue;
            }

            foreach (($spkData['items'] ?? []) as $spkItem) {
                $detailPoId =
                    $spkItem['detail_po_id'] ?? null;

                if (
                    $detailPoId === null ||
                    !isset($detailPoIds[(string) $detailPoId])
                ) {
                    continue;
                }

                $found[$categoryKey] = true;
                break;
            }
        }

        $categories = [];

        foreach (self::CATEGORY_ORDER as $categoryKey) {
            if (!isset($found[$categoryKey])) {
                continue;
            }

            $categories[$categoryKey] =
                self::CATEGORY_LABELS[$categoryKey];
        }

        return $categories;
    }

    /**
     * Normalisasi kategori SPK menjadi kategori monitoring.
     *
     * Kaki Kayu harus dicek sebelum Rangka karena
     * "RANGKA KAKI KAYU" juga mengandung kata "rangka".
     */
    public static function resolveCategory(?string $kategori): ?string
    {
        $kategori = strtolower(trim($kategori ?? ''));

        if ($kategori === '') {
            return null;
        }

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

        if (
            str_contains($kategori, 'decor') ||
            str_contains($kategori, 'dekor')
        ) {
            return 'decor';
        }

        if (str_contains($kategori, 'unfinish')) {
            return 'unfinish';
        }

        if (str_contains($kategori, 'final')) {
            return 'final';
        }

        if (
            str_contains($kategori, 'box') ||
            str_contains($kategori, 'packaging')
        ) {
            return 'box';
        }

        return null;
    }

    private static function decodeData($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);

            return is_array($decoded)
                ? $decoded
                : [];
        }

        return is_array($data)
            ? $data
            : [];
    }
}