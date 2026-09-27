                    <div class="mn-card mb-5" data-monitoring-po-id="{{ $po['po_id'] ?? '' }}">

                        {{-- HEADER --}}
                        <div class="mn-header d-flex justify-content-between align-items-center spk-header">

                            <div>

                                <h6>
                                    PO : {{ $po['po_number'] }}
                                    <span class="">
                                        ({{ $po['buyer'] ?? $po['buyer_name'] ?? '-' }})
                                    </span>
                                </h6>

                            </div>

                            <div>

                                <button type="button" class="btn btn-success btn-sm btn-toggle-po">

                                    <i class="fa fa-chevron-down"></i>

                                </button>
                            </div>

                        </div>
                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | KATEGORI HEADER DARI SPK ITEM
                            |--------------------------------------------------------------------------
                            | Header mengikuti kategori_monitoring yang benar-benar ada
                            | pada SPK item. Unfinish/Final berasal dari QC tanpa SPK.
                            |--------------------------------------------------------------------------
                            */
                            /*
                            |--------------------------------------------------------------------------
                            | KATEGORI HEADER PER PO
                            |--------------------------------------------------------------------------
                            | Header diambil dari SEMUA SPK yang benar-benar ada
                            | di seluruh item pada PO ini.
                            |
                            | Unfinish / Final juga ditampilkan berdasarkan
                            | keberadaan field, bukan nilai passed/rejected.
                            |--------------------------------------------------------------------------
                            */
                            $categoryOrder = [
                                'rangka' => 'Rangka',
                                'anyam' => 'Anyam',
                                'unfinish' => 'Unfinish',
                                'final' => 'Final',
                                'decor' => 'Decor',
                                'accessories' => 'Accessories',
                                'packaging' => 'Packaging',
                                'box' => 'Packaging',
                            ];

                            $foundCategories = [];

                            foreach (($po['items'] ?? []) as $headerItem) {

                                /*
                                |--------------------------------------------------------------------------
                                | SEMUA SPK PADA ITEM
                                |--------------------------------------------------------------------------
                                */
                                foreach (($headerItem['spks'] ?? []) as $headerSpk) {

                                    $categoryKey = strtolower(
                                        trim(
                                            $headerSpk['kategori_monitoring']
                                            ?? ''
                                        )
                                    );

                                    /*
                                    |--------------------------------------------------------------
                                    | FALLBACK CATEGORY -> ACCESSORIES
                                    |--------------------------------------------------------------
                                    | Jika kategori_monitoring kosong/null, gunakan kategori asli.
                                    | Cushion, kaca/glass, cermin/mirror, dan kaki kayu
                                    | ditampilkan pada kolom Accessories.
                                    */
                                    if ($categoryKey === '') {

                                        $kategoriRaw = strtolower(
                                            trim($headerSpk['kategori'] ?? '')
                                        );

                                        if (
                                            str_contains($kategoriRaw, 'kaki kayu')
                                            || str_contains($kategoriRaw, 'kayu kaki')
                                            || str_contains($kategoriRaw, 'cushion')
                                            || str_contains($kategoriRaw, 'kaca')
                                            || str_contains($kategoriRaw, 'glass')
                                            || str_contains($kategoriRaw, 'cermin')
                                            || str_contains($kategoriRaw, 'mirror')
                                            || str_contains($kategoriRaw, 'aksesor')
                                            || str_contains($kategoriRaw, 'accessor')
                                        ) {
                                            $categoryKey = 'accessories';
                                        }
                                    }

                                    /*
                                    | Compatibility jika controller lama
                                    | masih menghasilkan "box".
                                    */
                                    if ($categoryKey === 'box') {
                                        $categoryKey = 'packaging';
                                    }

                                    if (
                                        $categoryKey !== ''
                                        &&
                                        isset($categoryOrder[$categoryKey])
                                    ) {
                                        $foundCategories[$categoryKey] = true;
                                    }
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | UNFINISH
                                |--------------------------------------------------------------------------
                                */
                                if (
                                    array_key_exists(
                                        'unfinish',
                                        $headerItem
                                    )
                                ) {
                                    $foundCategories['unfinish'] = true;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | FINAL
                                |--------------------------------------------------------------------------
                                */
                                if (
                                    array_key_exists(
                                        'final',
                                        $headerItem
                                    )
                                ) {
                                    $foundCategories['final'] = true;
                                }
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | BUILD HEADER SESUAI URUTAN
                            |--------------------------------------------------------------------------
                            */
                            $categories = [];

                            foreach (
                                $categoryOrder
                                as $categoryKey => $categoryLabel
                            ) {

                                if (
                                    isset(
                                        $foundCategories[$categoryKey]
                                    )
                                ) {
                                    $categories[$categoryKey] =
                                        $categoryLabel;
                                }
                            }

                            $statuses = [
                                'in' => [
                                    'label' => 'In',
                                    'class' => 'text-primary fw-bold',
                                ],

                                'pass' => [
                                    'label' => 'Pass',
                                    'class' => 'pass-box',
                                ],

                                'reject' => [
                                    'label' => 'Reject',
                                    'class' => 'reject-box',
                                ],

                                'out' => [
                                    'label' => 'Out',
                                    'class' => 'text-dark fw-bold',
                                ],
                            ];

                        @endphp

                        {{-- TABLE --}}
                        <div class="table-responsive po-table">

                            <table class="table mn-table align-middle">

                                <thead>

                                    {{-- HEADER CATEGORY --}}
                                    <tr>

                                        <th rowspan="2" class="text-center">
                                            Gambar
                                        </th>

                                        <th rowspan="2" class="text-center">
                                            Qty
                                        </th>

                                        <th rowspan="2" class="text-center">
                                            Item
                                        </th>

                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            <th colspan="{{ in_array($categoryKey, ['final', 'box', 'packaging']) ? 1 : 2 }}"
                                                class="text-center">
                                                {{ $categoryLabel }}
                                            </th>
                                        @endforeach

                                    </tr>

                                    {{-- HEADER STATUS --}}
                                    <tr>

                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            @foreach ($statuses as $statusKey => $status)
                                                @continue($statusKey == 'out')
                                                @continue($statusKey == 'reject')

                                                {{-- Final & Packaging hanya PASS --}}
                                                @if (in_array($categoryKey, ['final', 'box']) && $statusKey == 'in')
                                                    @continue
                                                @endif
                                                <th class="text-center status-col {{ $status['class'] }}">
                                                    {{ $status['label'] }}
                                                </th>
                                            @endforeach
                                        @endforeach

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($po['items'] as $itemIndex => $item)
                                        <tr>

                                            {{-- IMAGE --}}
                                            <td class="text-center">

                                                @if (!empty($item['item_image']))
                                                    <img src="{{ $item['item_image'] ?? '' }}" class="product-image"
                                                        loading="lazy" decoding="async">
                                                @else
                                                    -
                                                @endif

                                            </td>

                                            {{-- QTY --}}
                                            <td class="qty-col text-center">

                                                {{-- Qty utama PO --}}
                                                <div class="qty-main-value">
                                                    {{ $item['qty'] }}
                                                </div>

                                                @php
                                                    /*
                                                    |--------------------------------------------------------------------------
                                                    | KETERANGAN SPK DI BAWAH QTY
                                                    |--------------------------------------------------------------------------
                                                    | Hanya untuk informasi visual.
                                                    | Tidak mengubah perhitungan In / Pass / Reject.
                                                    |
                                                    | RANGKA KAKI KAYU ditampilkan sebagai Kaki Kayu.
                                                    */
                                                    $spkQtyByLabel = [];

                                                    foreach (($item['spks'] ?? []) as $spkInfo) {
                                                        $kategoriSpk = strtolower(trim($spkInfo['kategori'] ?? ''));

                                                        if ($kategoriSpk === '') {
                                                            continue;
                                                        }

                                                        if (
                                                            str_contains($kategoriSpk, 'kaki kayu') ||
                                                            str_contains($kategoriSpk, 'kayu kaki')
                                                        ) {
                                                            $spkLabel = 'Kaki Kayu';
                                                        } elseif (str_contains($kategoriSpk, 'rangka')) {
                                                            $spkLabel = 'Rangka';
                                                        } elseif (str_contains($kategoriSpk, 'anyam')) {
                                                            $spkLabel = 'Anyam';
                                                        } elseif (str_contains($kategoriSpk, 'unfinish')) {
                                                            $spkLabel = 'Unfinish';
                                                        } elseif (str_contains($kategoriSpk, 'final')) {
                                                            $spkLabel = 'Final';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'box') ||
                                                            str_contains($kategoriSpk, 'packaging')
                                                        ) {
                                                            $spkLabel = 'Packaging';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'dekor') ||
                                                            str_contains($kategoriSpk, 'decor')
                                                        ) {
                                                            $spkLabel = 'Decor';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'aksesor') ||
                                                            str_contains($kategoriSpk, 'aksesori') ||
                                                            str_contains($kategoriSpk, 'accessor')
                                                        ) {
                                                            $spkLabel = 'Accessories';
                                                        } else {
                                                            $spkLabel = ucwords($kategoriSpk);
                                                        }

                                                        $spkQty = (float) ($spkInfo['qty'] ?? 0);

                                                        if (!isset($spkQtyByLabel[$spkLabel])) {
                                                            $spkQtyByLabel[$spkLabel] = 0;
                                                        }

                                                        $spkQtyByLabel[$spkLabel] += $spkQty;
                                                    }

                                                    /*
                                                    | Map label hasil SPK ke kolom monitoring.
                                                    | Kaki Kayu -> Accessories.
                                                    */
                                                    $spkQtyByCategory = [];

                                                    foreach ($spkQtyByLabel as $spkLabel => $spkQty) {
                                                        $labelLower = strtolower(trim($spkLabel));

                                                        if (str_contains($labelLower, 'kaki kayu')) {
                                                            $spkCategoryKey = 'accessories';
                                                        } elseif (str_contains($labelLower, 'rangka')) {
                                                            $spkCategoryKey = 'rangka';
                                                        } elseif (str_contains($labelLower, 'anyam')) {
                                                            $spkCategoryKey = 'anyam';
                                                        } elseif (str_contains($labelLower, 'unfinish')) {
                                                            $spkCategoryKey = 'unfinish';
                                                        } elseif (str_contains($labelLower, 'final')) {
                                                            $spkCategoryKey = 'final';
                                                        } elseif (str_contains($labelLower, 'packaging') || str_contains($labelLower, 'box')) {
                                                            $spkCategoryKey = 'box';
                                                        } elseif (str_contains($labelLower, 'decor')) {
                                                            $spkCategoryKey = 'decor';
                                                        } elseif (str_contains($labelLower, 'accessories')) {
                                                            $spkCategoryKey = 'accessories';
                                                        } else {
                                                            continue;
                                                        }

                                                        if (!isset($spkQtyByCategory[$spkCategoryKey])) {
                                                            $spkQtyByCategory[$spkCategoryKey] = [];
                                                        }

                                                        $spkQtyByCategory[$spkCategoryKey][] = [
                                                            'label' => $spkLabel,
                                                            'qty' => $spkQty,
                                                        ];
                                                    }
                                                @endphp


                                            </td>

                                            {{-- ITEM --}}
                                            <td style="max-width:150px;">

                                                <a href="#" class="item-link text-truncate d-inline-block"
                                                    style="max-width:250px;" title="{{ $item['item_name'] }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#spkModal{{ $poIndex }}{{ $itemIndex }}">

                                                    {{ $item['item_name'] }}

                                                </a>

                                            </td>

                                            {{-- DYNAMIC CATEGORY + STATUS --}}
                                            @php
                                                $monitoring = [];

                                                foreach ($categories as $categoryKey => $categoryLabel) {
                                                    $monitoring[$categoryKey] = [
                                                        'in' => 0,
                                                        'pass' => 0,
                                                        'reject' => 0,
                                                        'spks' => [],
                                                    ];
                                                }

                                                $monitoringSeen = [];

                                                foreach (($item['spks'] ?? []) as $spk) {

    /*
    |--------------------------------------------------------------------------
    | HINDARI DUPLIKASI SPK IDENTIK
    |--------------------------------------------------------------------------
    | Composite Rangka + Anyam tetap aman karena kategori/component berbeda.
    | Hanya record yang benar-benar identik yang dilewati.
    */
    $monitoringFingerprint = implode('|', [
        $spk['spk_id'] ?? '',
        $spk['kategori_monitoring'] ?? '',
        $spk['kategori'] ?? '',
        $spk['qty'] ?? '',
        $spk['qty_in'] ?? '',
        $spk['passed'] ?? '',
        $spk['rejected'] ?? '',
        $spk['component_name'] ?? '',
        $spk['material'] ?? ($item['material'] ?? ''),
    ]);

    if (isset($monitoringSeen[$monitoringFingerprint])) {
        continue;
    }

    $monitoringSeen[$monitoringFingerprint] = true;

    $categoryKey = strtolower(
        trim($spk['kategori_monitoring'] ?? '')
    );

    /*
    |--------------------------------------------------------------------------
    | FALLBACK CATEGORY -> ACCESSORIES
    |--------------------------------------------------------------------------
    | SPK seperti Cushion, Kaca/Glass, Cermin/Mirror, dan Kaki Kayu
    | kadang tidak mempunyai kategori_monitoring.
    */
    if ($categoryKey === '') {

        $kategoriRaw = strtolower(
            trim($spk['kategori'] ?? '')
        );

        if (
            str_contains($kategoriRaw, 'kaki kayu')
            || str_contains($kategoriRaw, 'kayu kaki')
            || str_contains($kategoriRaw, 'cushion')
            || str_contains($kategoriRaw, 'kaca')
            || str_contains($kategoriRaw, 'glass')
            || str_contains($kategoriRaw, 'cermin')
            || str_contains($kategoriRaw, 'mirror')
            || str_contains($kategoriRaw, 'aksesor')
            || str_contains($kategoriRaw, 'accessor')
        ) {
            $categoryKey = 'accessories';
        }
    }

    if ($categoryKey === 'box') {
        $categoryKey = 'packaging';
    }

    if (!isset($monitoring[$categoryKey])) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN SEMUA SPK UNTUK TOOLTIP
    |--------------------------------------------------------------------------
    */
    $monitoring[$categoryKey]['spks'][] = $spk;


    /*
    |--------------------------------------------------------------------------
    | DETEKSI RANGKA KAKI KAYU
    |--------------------------------------------------------------------------
    | Tetap tampil di tooltip,
    | tetapi tidak ikut total Rangka.
    |--------------------------------------------------------------------------
    */
    $kategoriSpk = strtoupper(
        trim($spk['kategori'] ?? '')
    );

    $exceptionRule = strtoupper(
        trim($spk['exception_rule'] ?? '')
    );

    $isKakiKayu =
        str_contains($kategoriSpk, 'KAKI KAYU')
        ||
        str_contains($kategoriSpk, 'KAYU KAKI')
        ||
        str_contains($exceptionRule, 'KAKI KAYU')
        ||
        str_contains($exceptionRule, 'KAYU KAKI');


    /*
    |--------------------------------------------------------------------------
    | RANGKA KAKI KAYU
    |--------------------------------------------------------------------------
    */
    if (
        $categoryKey === 'rangka'
        &&
        $isKakiKayu
    ) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMAL TOTAL
    |--------------------------------------------------------------------------
    */
    if ($categoryKey === 'rangka') {

    $kategoriSpk = strtoupper(
        trim($spk['kategori'] ?? '')
    );

    $allRangkaSpks = $monitoring['rangka']['spks'] ?? [];

    $hasRangkaKayu = false;
    $hasRangkaTriplek = false;

    foreach ($allRangkaSpks as $rangkaSpk) {
        $kategoriRangka = strtoupper(
            trim($rangkaSpk['kategori'] ?? '')
        );

        if (str_contains($kategoriRangka, 'RANGKA KAYU')) {
            $hasRangkaKayu = true;
        }

        if (str_contains($kategoriRangka, 'RANGKA TRIPLEK')) {
            $hasRangkaTriplek = true;
        }
    }

    /*
     * Jika dalam item yang sama ada Rangka Kayu + Rangka Triplek,
     * yang dihitung hanya Rangka Kayu.
     */
    if (
        $hasRangkaKayu
        && $hasRangkaTriplek
        && !str_contains($kategoriSpk, 'RANGKA KAYU')
    ) {
        continue;
    }
}
    $monitoring[$categoryKey]['in'] +=
        (float) ($spk['qty_in'] ?? 0);

    $monitoring[$categoryKey]['pass'] +=
        (float) ($spk['passed'] ?? 0);

    $monitoring[$categoryKey]['reject'] +=
        (float) ($spk['rejected'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| ANYAM COMPONENT
|--------------------------------------------------------------------------
|
| Composite Anyam:
| - Jika ketiga component memiliki data IN, gunakan MIN(IN component).
| - Jika ketiga component memiliki data PASSED, gunakan MIN(PASSED component).
| - Jika data component tidak tersedia / semuanya 0, JANGAN menimpa
|   nilai SPK. Gunakan qty_in / passed dari SPK seperti Anyam biasa.
|
| Ini penting karena hasil inspection bisa tersimpan pada level SPK,
| sedangkan components hanya berisi struktur/proses Anyam.
|--------------------------------------------------------------------------
*/
$anyamSpks = $monitoring['anyam']['spks'] ?? [];

if (!empty($anyamSpks)) {

    $componentIn = [];
    $componentPass = [];

    foreach ($anyamSpks as $spk) {

        foreach (($spk['components'] ?? []) as $component) {

            $componentName = strtoupper(
                trim(
                    $component['name']
                    ?? $component['proses']
                    ?? $component['deskripsi']
                    ?? ''
                )
            );

            if ($componentName === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | NORMALISASI COMPONENT ANYAM
            |--------------------------------------------------------------------------
            */
            if (str_contains($componentName, 'ANYAM RANGKA')) {
                $componentName = 'ANYAM RANGKA';
            } elseif (str_contains($componentName, 'ANYAM DUDUKAN')) {
                $componentName = 'ANYAM DUDUKAN';
            } elseif (str_contains($componentName, 'ANYAM SANDARAN')) {
                $componentName = 'ANYAM SANDARAN';
            } else {
                /*
                | ANYAM biasa tidak masuk MIN component.
                | Nilai SPK tetap menjadi sumber utama.
                */
                continue;
            }

            $componentIn[$componentName] =
                ($componentIn[$componentName] ?? 0)
                + (float) ($component['qty_in'] ?? 0);

            $componentPass[$componentName] =
                ($componentPass[$componentName] ?? 0)
                + (float) ($component['passed'] ?? 0);
        }
    }

    $requiredAnyamComponents = [
        'ANYAM RANGKA',
        'ANYAM DUDUKAN',
        'ANYAM SANDARAN',
    ];

    /*
    |--------------------------------------------------------------------------
    | CEK COMPOSITE LENGKAP
    |--------------------------------------------------------------------------
    */
    $hasFullAnyamComposite = true;

    foreach ($requiredAnyamComponents as $requiredComponent) {
        if (!array_key_exists($requiredComponent, $componentIn)) {
            $hasFullAnyamComposite = false;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ANYAM COMPONENT - IN
    |--------------------------------------------------------------------------
    |
    | Hanya pakai MIN component apabila memang ada data IN component.
    | Kalau ketiga component ada tetapi semuanya 0, berarti component
    | tidak menjadi sumber inspection; pertahankan qty_in dari SPK.
    |--------------------------------------------------------------------------
    */
    $componentInHasRealData = false;

    if ($hasFullAnyamComposite) {
        foreach ($requiredAnyamComponents as $requiredComponent) {
            if ((float) ($componentIn[$requiredComponent] ?? 0) > 0) {
                $componentInHasRealData = true;
                break;
            }
        }
    }

    if ($hasFullAnyamComposite && $componentInHasRealData) {
        /*
        |--------------------------------------------------------------------------
        | ANYAM COMPONENT - IN
        |--------------------------------------------------------------------------
        | Untuk composite Anyam, qty_in tiap component bukan dijumlahkan
        | sebagai total produk karena component mewakili proses yang berbeda.
        |
        | Contoh:
        | ANYAM RANGKA    = 40
        | ANYAM DUDUKAN   = 30
        | ANYAM SANDARAN  = 37
        |
        | IN Anyam = MIN(40, 30, 37) = 30
        |--------------------------------------------------------------------------
        */
        $anyamComponentValues = [];

        foreach ($requiredAnyamComponents as $requiredComponent) {
            if (array_key_exists($requiredComponent, $componentIn)) {
                $anyamComponentValues[] =
                    (float) ($componentIn[$requiredComponent] ?? 0);
            }
        }

        if (!empty($anyamComponentValues)) {
            $monitoring['anyam']['in'] = min($anyamComponentValues);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ANYAM COMPONENT - PASSED
    |--------------------------------------------------------------------------
    |
    | Sama seperti IN.
    | Jika data Passed component benar-benar tersedia, gunakan MIN().
    | Jika tidak, gunakan nilai passed dari SPK yang sudah dihitung di atas.
    |--------------------------------------------------------------------------
    */
    $hasFullAnyamCompositePass = true;

    foreach ($requiredAnyamComponents as $requiredComponent) {
        if (!array_key_exists($requiredComponent, $componentPass)) {
            $hasFullAnyamCompositePass = false;
            break;
        }
    }

    $componentPassHasRealData = false;

    if ($hasFullAnyamCompositePass) {
        foreach ($requiredAnyamComponents as $requiredComponent) {
            if ((float) ($componentPass[$requiredComponent] ?? 0) > 0) {
                $componentPassHasRealData = true;
                break;
            }
        }
    }

    if ($hasFullAnyamCompositePass && $componentPassHasRealData) {
        $monitoring['anyam']['pass'] = min(
            $componentPass['ANYAM RANGKA'],
            $componentPass['ANYAM DUDUKAN'],
            $componentPass['ANYAM SANDARAN']
        );
    }
}

$packagingSpks =
    $monitoring['packaging']['spks'] ?? [];

if (!empty($packagingSpks)) {

    $boxIn = 0;
    $boxPass = 0;

    foreach ($packagingSpks as $spk) {

        foreach (($spk['components'] ?? []) as $component) {

            $componentName = strtoupper(
                trim(
                    $component['name']
                    ?? $component['proses']
                    ?? $component['deskripsi']
                    ?? ''
                )
            );


            /*
            |--------------------------------------------------------------------------
            | HANYA BOX MENJADI ACUAN ANGKA UTAMA
            |--------------------------------------------------------------------------
            */
            if (
                $componentName === 'BOX'
                ||
                str_contains(
                    $componentName,
                    'CARTON BOX'
                )
            ) {

                $boxIn +=
                    (float) ($component['qty_in'] ?? 0);

                $boxPass +=
                    (float) ($component['passed'] ?? 0);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SET TOTAL PACKAGING
    |--------------------------------------------------------------------------
    */
    if ($boxIn > 0 || $boxPass > 0) {

        $monitoring['packaging']['in'] =
            $boxIn;

        $monitoring['packaging']['pass'] =
            $boxPass;
    }
}

                                                $formatQty = function ($value) {
                                                    $value = (float) $value;

                                                    return floor($value) == $value
                                                        ? number_format($value, 0, ',', '.')
                                                        : number_format($value, 2, ',', '.');
                                                };
                                            @endphp

                                            @foreach ($categories as $categoryKey => $categoryLabel)

                                                @php
                                                    $categorySpks = $monitoring[$categoryKey]['spks'] ?? [];

                                                    /*
                                                    | Tooltip-only dedupe.
                                                    | Exact duplicate SPK rows are removed from the list,
                                                    | but monitoring totals tetap menggunakan perhitungan asli.
                                                    */
                                                    $tooltipSeen = [];
                                                    $categorySpks = collect($categorySpks)
                                                        ->filter(function ($spkInfo) use (&$tooltipSeen) {
                                                            $fingerprint = implode('|', [
                                                                $spkInfo['spk_id'] ?? '',
                                                                $spkInfo['kategori_monitoring'] ?? '',
                                                                $spkInfo['kategori'] ?? '',
                                                                $spkInfo['qty'] ?? '',
                                                                $spkInfo['qty_in'] ?? '',
                                                                $spkInfo['passed'] ?? '',
                                                                $spkInfo['rejected'] ?? '',
                                                                $spkInfo['component_name'] ?? '',
                                                                $spkInfo['material'] ?? '',
                                                            ]);

                                                            if (isset($tooltipSeen[$fingerprint])) {
                                                                return false;
                                                            }

                                                            $tooltipSeen[$fingerprint] = true;
                                                            return true;
                                                        })
                                                        ->values()
                                                        ->all();
                                                @endphp

                                                {{-- IN --}}
                                               @if ($categoryKey !== 'final')
                                                    <td class="text-center status-col text-primary fw-bold">

                                                        @if (!empty($categorySpks))
                                                            <span
                                                                class="spk-hover-target"
                                                                data-tooltip-type="in"
                                                                data-monitor-metric="qty_in"
                                                            >
                                                                {{ $formatQty($monitoring[$categoryKey]['in'] ?? 0) }}

                                                                <span class="spk-list-tooltip">
                                                                    <span class="spk-list-tooltip-title">
                                                                        <strong>
                                                                            {{ strtoupper($categoryLabel) }}
                                                                            — TOTAL IN
                                                                        </strong>
                                                                        <span class="spk-list-tooltip-count">
                                                                            {{ count($categorySpks) }} SPK
                                                                        </span>
                                                                    </span>

                                                                    <span class="spk-list-tooltip-scroll">
                                                                        <table class="spk-list-table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="col-no">#</th>
                                                                                    <th class="col-spk">NO SPK</th>
                                                                                    <th class="col-sub">SUB NAME</th>
                                                                                    <th class="col-category">JENIS/KATEGORI</th>
                                                                                    <th class="col-description">KETERANGAN</th>
                                                                                    <th class="col-total">TOTAL IN</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($categorySpks as $spkInfo)
                                                                                    @php
                                                                                        /*
                                                                                        |--------------------------------------------------------------------------
                                                                                        | SUB NAME / SUPPLIER
                                                                                        |--------------------------------------------------------------------------
                                                                                        | Untuk tooltip IN dan PASSED harus identik.
                                                                                        | Sumbernya adalah supplier dari SPK:
                                                                                        |
                                                                                        | $spkData['sup']
                                                                                        |      ↓
                                                                                        | controller
                                                                                        |      ↓
                                                                                        | $spkInfo['supplier']
                                                                                        |--------------------------------------------------------------------------
                                                                                        */
                                                                                        $spkSubName =
                                                                                            $spkInfo['supplier']
                                                                                            ?? '-';
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td class="col-no">
                                                                                            {{ $loop->iteration }}
                                                                                        </td>
                                                                                        <td class="col-spk">
                                                                                            <a
                                                                                                href="{{ url('spk/edit/' . ($spkInfo['spk_id'] ?? '')) }}"
                                                                                                class="spk-link"
                                                                                            >
                                                                                                {{ $spkInfo['no_spk'] ?? '-' }}
                                                                                            </a>
                                                                                        </td>
                                                                                        <td class="col-sub">
                                                                                            {{ $spkSubName }}
                                                                                        </td>
                                                                                        <td class="col-category">

                                                                                            {{ strtoupper($spkInfo['kategori'] ?? '-') }}

                                                                                            @if (!empty($spkInfo['is_exception']))
                                                                                                <span class="exception-badge">
                                                                                                    EXCEPTION
                                                                                                </span>
                                                                                            @endif

                                                                                        </td>

                                                                                        <td class="col-description">
@php
                                                                                                 /*
                                                                                                 | KETERANGAN TOOLTIP
                                                                                                 | Prioritas: component/process, lalu material SPK.
                                                                                                 */
                                                                                                 $componentNames = collect($spkInfo['components'] ?? [])
                                                                                                     ->map(function ($component) {
                                                                                                         return trim((string) (
                                                                                                             $component['name']
                                                                                                             ?? $component['proses']
                                                                                                             ?? $component['deskripsi']
                                                                                                             ?? ''
                                                                                                         ));
                                                                                                     })
                                                                                                     ->filter()
                                                                                                     ->unique()
                                                                                                     ->values()
                                                                                                     ->all();

                                                                                                 $materialName = trim((string) (
                                                                                                     $spkInfo['material']
                                                                                                     ?? $item['material']
                                                                                                     ?? ''
                                                                                                 ));

                                                                                                 if (!empty($componentNames)) {
                                                                                                     $description = implode(', ', $componentNames);
                                                                                                 } elseif ($materialName !== '') {
                                                                                                     $description = $materialName;
                                                                                                 } else {
                                                                                                     $description = '-';
                                                                                                 }
                                                                                             @endphp

                                                                                             {{ $description }}

                                                                                        </td>

                                                                                        <td class="col-total"
                                                                                            data-tooltip-in-value="{{ $formatQty(
                                                                                                $categoryKey === 'anyam'
                                                                                                    ? collect($spkInfo['components'] ?? [])->sum(fn ($component) => (float) ($component['qty_in'] ?? 0))
                                                                                                    : ($spkInfo['qty_in'] ?? 0)
                                                                                            ) }}"
                                                                                            data-tooltip-pass-value="{{ $formatQty($spkInfo['passed'] ?? 0) }}">
                                                                                            {{ $formatQty(
                                                                                                $categoryKey === 'anyam'
                                                                                                    ? collect($spkInfo['components'] ?? [])->sum(fn ($component) => (float) ($component['qty_in'] ?? 0))
                                                                                                    : ($spkInfo['qty_in'] ?? 0)
                                                                                            ) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        @else
                                                            {{ $formatQty($monitoring[$categoryKey]['in'] ?? 0) }}
                                                        @endif

                                                        @if (!empty($categorySpks))
                                                            <div class="spk-under-value">
                                                                <div class="spk-under-value-line">
                                                                    {{ count($categorySpks) }} SPK
                                                                </div>
                                                            </div>
                                                        @endif

                                                    </td>
                                                @endif

                                                {{-- PASS --}}
                                                <td class="text-center status-col pass-box">

                                                    @if (!empty($categorySpks))
                                                        <span
                                                            class="spk-hover-target"
                                                            data-tooltip-type="pass"
                                                            data-monitor-metric="passed"
                                                        >
                                                            {{ $formatQty($monitoring[$categoryKey]['pass'] ?? 0) }}

                                                            <span class="spk-list-tooltip">
                                                                <span class="spk-list-tooltip-title">
                                                                    <strong>
                                                                        {{ strtoupper($categoryLabel) }}
                                                                        — PASSED
                                                                    </strong>
                                                                    <span class="spk-list-tooltip-count">
                                                                        {{ count($categorySpks) }} SPK
                                                                    </span>
                                                                </span>

                                                                <span class="spk-list-tooltip-scroll">
                                                                    <table class="spk-list-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="col-no">#</th>
                                                                                <th class="col-spk">NO SPK</th>
                                                                                <th class="col-sub">SUB NAME</th>
                                                                                <th class="col-category">JENIS/KATEGORI</th>
                                                                                <th class="col-description">KETERANGAN</th>
                                                                                <th class="col-total">TOTAL PASSED</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($categorySpks as $spkInfo)
                                                                                @php
                                                                                    // Supplier / sub name untuk PASSED harus sama persis dengan IN.
                                                                                    $spkSubName = $spkInfo['supplier'] ?? '-';
                                                                                @endphp
                                                                                <tr>
                                                                                    <td class="col-no">
                                                                                        {{ $loop->iteration }}
                                                                                    </td>
                                                                                    <td class="col-spk">
                                                                                        <a
                                                                                            href="{{ url('spk/edit/' . ($spkInfo['spk_id'] ?? '')) }}"
                                                                                            class="spk-link"
                                                                                        >
                                                                                            {{ $spkInfo['no_spk'] ?? '-' }}
                                                                                        </a>
                                                                                    </td>
                                                                                    <td class="col-sub">
                                                                                        {{ $spkSubName }}
                                                                                    </td>
                                                                                    <td class="col-category">
                                                                                        {{ strtoupper($spkInfo['kategori'] ?? '-') }}
                                                                                        @if (!empty($spkInfo['is_exception']))
                                                                                            <span class="exception-badge">
                                                                                                EXCEPTION
                                                                                            </span>
                                                                                        @endif

                                                                                        </td>

                                                                                        <td class="col-description">
@php
                                                                                                 /*
                                                                                                 | KETERANGAN TOOLTIP
                                                                                                 | Prioritas: component/process, lalu material SPK.
                                                                                                 */
                                                                                                 $componentNames = collect($spkInfo['components'] ?? [])
                                                                                                     ->map(function ($component) {
                                                                                                         return trim((string) (
                                                                                                             $component['name']
                                                                                                             ?? $component['proses']
                                                                                                             ?? $component['deskripsi']
                                                                                                             ?? ''
                                                                                                         ));
                                                                                                     })
                                                                                                     ->filter()
                                                                                                     ->unique()
                                                                                                     ->values()
                                                                                                     ->all();

                                                                                                 $materialName = trim((string) (
                                                                                                     $spkInfo['material']
                                                                                                     ?? $item['material']
                                                                                                     ?? ''
                                                                                                 ));

                                                                                                 if (!empty($componentNames)) {
                                                                                                     $description = implode(', ', $componentNames);
                                                                                                 } elseif ($materialName !== '') {
                                                                                                     $description = $materialName;
                                                                                                 } else {
                                                                                                     $description = '-';
                                                                                                 }
                                                                                             @endphp

                                                                                             {{ $description }}

                                                                                        </td>

                                                                                        <td class="col-total"
                                                                                        data-tooltip-in-value="{{ $formatQty($spkInfo['qty_in'] ?? 0) }}"
                                                                                        data-tooltip-pass-value="{{ $formatQty($spkInfo['passed'] ?? 0) }}">
                                                                                        {{ $formatQty($spkInfo['passed'] ?? 0) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </span>
                                                            </span>
                                                        @else
                                                            {{ $formatQty($monitoring[$categoryKey]['pass'] ?? 0) }}
                                                    @endif

                                                    @if (!empty($categorySpks))
                                                        <div class="spk-under-value">
                                                            <div class="spk-under-value-line">
                                                                {{ count($categorySpks) }} SPK
                                                            </div>
                                                        </div>
                                                    @endif

                                                </td>

                                            @endforeach

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>




                        </div>
                    </div>
                    {{-- ========================================================= --}}
                    {{-- MODAL LUAR TABLE --}}
                    {{-- ========================================================= --}}

                    @foreach ($po['items'] as $itemIndex => $item)
                        <div class="modal fade" id="spkModal{{ $poIndex }}{{ $itemIndex }}" tabindex="-1">

                            <div class="modal-dialog modal-lg modal-dialog-centered">

                                <div class="modal-content border-0 shadow">

                                    {{-- HEADER --}}
                                    <div class="modal-header bg-dark text-white">

                                        <h5 class="modal-title">

                                            SPK ITEM

                                        </h5>

                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                                        </button>

                                    </div>

                                    {{-- BODY --}}
                                    <div class="modal-body">

                                        {{-- ITEM INFO --}}
                                        <div class="d-flex gap-3 mb-4">

                                            @if (!empty($item['item_image']))
                                                <img src="{{ $item['item_image'] ?? '' }}" class="product-image"
                                                    loading="lazy" decoding="async">
                                            @endif

                                            <div>

                                                <div class="fw-bold fs-5">

                                                    {{ $item['item_name'] }}

                                                </div>

                                                <div class="text-muted">

                                                    Qty :
                                                    {{ $item['qty'] }}

                                                </div>

                                            </div>

                                        </div>

                                        {{-- LIST SPK --}}
                                        @php
                                            /*
                                            |--------------------------------------------------------------------------
                                            | MODAL SPK - HINDARI DUPLIKASI
                                            |--------------------------------------------------------------------------
                                            | Untuk SPK composite, controller dapat membuat beberapa
                                            | record monitoring dengan SPK ID yang sama:
                                            |   ANYAM RANGKA
                                            |   ANYAM DUDUKAN
                                            |   ANYAM SANDARAN
                                            |
                                            | Record tersebut tetap diperlukan untuk tabel monitoring,
                                            | tetapi di modal cukup tampilkan 1 kartu untuk 1 SPK.
                                            |--------------------------------------------------------------------------
                                            */
                                            $modalSpks = [];

                                            foreach (($item['spks'] ?? []) as $modalSpk) {
                                                $modalSpkKey = (string) (
                                                    $modalSpk['spk_id']
                                                    ?? $modalSpk['no_spk']
                                                    ?? md5(json_encode($modalSpk))
                                                );

                                                if (!isset($modalSpks[$modalSpkKey])) {
                                                    $modalSpks[$modalSpkKey] = $modalSpk;
                                                }
                                            }
                                        @endphp

                                        @forelse($modalSpks as $spk)

                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-body">

                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div class="d-flex gap-2 flex-wrap">

                                                            <span class="badge bg-primary px-3 py-2">
                                                                {{ strtoupper($spk['kategori'] ?? '-') }}
                                                            </span>

                                                            <span class="badge bg-secondary px-3 py-2">
                                                                {{ strtoupper($spk['kategori_monitoring'] ?? '-') }}
                                                            </span>

                                                            @if (!empty($spk['is_exception']))
                                                                <span class="badge bg-warning text-dark px-3 py-2">
                                                                    Exception:
                                                                    {{ strtoupper($spk['exception_rule'] ?? 'RULE') }}
                                                                </span>
                                                            @endif

                                                        </div>

                                                        <span class="badge bg-success px-3 py-2">
                                                            SPK #{{ $spk['spk_id'] ?? '-' }}
                                                        </span>

                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-8">

                                                            <table class="table table-sm mb-0">

                                                                <tr>
                                                                    <td width="140">Supplier</td>
                                                                    <td>:
                                                                        {{ $spk['supplier'] ?? '-' }}
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>No SPK</td>
                                                                    <td>:
                                                                        <a href="{{ url('spk/edit/' . ($spk['spk_id'] ?? '')) }}"
                                                                           class="fw-bold text-primary text-decoration-underline">
                                                                            {{ $spk['no_spk'] ?? '-' }}
                                                                        </a>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Qty SPK</td>
                                                                    <td>:
                                                                        {{ $spk['qty'] ?? 0 }}
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Qty In</td>
                                                                    <td>:
                                                                        <span class="fw-bold text-primary">
                                                                            {{ $spk['qty_in'] ?? 0 }}
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Harga</td>
                                                                    <td>:
                                                                        <span class="price-container"
                                                                              data-price="{{ number_format($spk['harga'] ?? 0) }}">
                                                                            <a href="#"
                                                                               class="show-price text-primary text-decoration-underline">
                                                                                Lihat Harga? Tap disini
                                                                            </a>
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                            </table>

                                                        </div>

                                                        <div class="col-md-4">

                                                            <div class="border rounded-4 p-3 h-100 bg-light">

                                                                <div class="fw-bold mb-3">
                                                                    MONITORING RESULT
                                                                </div>

                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <span>Passed</span>
                                                                    <span class="fw-bold text-success">
                                                                        {{ $spk['passed'] ?? 0 }}
                                                                    </span>
                                                                </div>

                                                                <div class="d-flex justify-content-between">
                                                                    <span>Rejected</span>
                                                                    <span class="fw-bold text-danger">
                                                                        {{ $spk['rejected'] ?? 0 }}
                                                                    </span>
                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        @empty

                                            <div class="alert alert-warning mb-0">
                                                Tidak ada SPK untuk item ini
                                            </div>

                                        @endforelse
                                    </div>

                                </div>

                            </div>

                        </div>
                    @endforeach
