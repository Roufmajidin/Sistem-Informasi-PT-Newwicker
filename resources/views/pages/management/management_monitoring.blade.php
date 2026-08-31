<div id="monitoringResult">

                @forelse($poJson as $poIndex => $poNode)

                    @php
                        $poModel = $poNode;
                        $details = collect($poNode['items'] ?? []);

                        $poNumber = data_get($poModel, 'po_number')
                            ?? data_get($poModel, 'order_no')
                            ?? data_get($poModel, 'nomor_po')
                            ?? data_get($poModel, 'po')
                            ?? data_get($poModel, 'number')
                            ?? '-';

                        $buyer = data_get($poModel, 'buyer')
                            ?? data_get($poModel, 'company_name')
                            ?? data_get($poModel, 'nama_buyer')
                            ?? data_get($poModel, 'customer')
                            ?? '-';

                        $country = data_get($poModel, 'country')
                            ?? data_get($poModel, 'destination')
                            ?? data_get($poModel, 'negara')
                            ?? data_get($poModel, 'ship_to')
                            ?? null;

                        $release = data_get($poModel, 'release')
                            ?? data_get($poModel, 'release_date')
                            ?? data_get($poModel, 'tgl_release')
                            ?? data_get($poModel, 'tanggal_release')
                            ?? null;

                        $shipment = data_get($poModel, 'shipment')
                            ?? data_get($poModel, 'shipment_date')
                            ?? data_get($poModel, 'tgl_shipment')
                            ?? data_get($poModel, 'tanggal_shipment')
                            ?? null;

                        $totalItems = $details->count();

                        $totalValue = data_get($poModel, 'total')
                            ?? data_get($poModel, 'grand_total')
                            ?? data_get($poModel, 'total_amount')
                            ?? data_get($poModel, 'amount')
                            ?? null;

                        $categoryOrder = [
                            'rangka' => 'Rangka',
                            'anyam' => 'Anyam',
                            'dekor' => 'Dekor',
                            'unfinish' => 'Unfinish',
                            'final' => 'Final',
                            'accessories' => 'Accessories',
                            'packaging' => 'Packaging / Box',
                        ];

                        $foundCategories = [];

                        foreach ($details as $detailNode) {
                            foreach (collect($detailNode['spks'] ?? []) as $spkInfo) {
                                $kategori = strtolower(trim((string) ($spkInfo['kategori'] ?? '')));

                                if ($kategori === '') {
                                    continue;
                                }

                                if (str_contains($kategori, 'rangka')) {
                                    $foundCategories['rangka'] = true;
                                }

                                if (str_contains($kategori, 'anyam')) {
                                    $foundCategories['anyam'] = true;
                                }

                                if (str_contains($kategori, 'dekor') || str_contains($kategori, 'decor')) {
                                    $foundCategories['dekor'] = true;
                                }


                                if (str_contains($kategori, 'unfinish')) {
                                    $foundCategories['unfinish'] = true;
                                }

                                if (str_contains($kategori, 'final')) {
                                    $foundCategories['final'] = true;
                                }

                                if (
                                    str_contains($kategori, 'accessor') ||
                                    str_contains($kategori, 'aksesor') ||
                                    str_contains($kategori, 'aksesori')
                                ) {
                                    $foundCategories['accessories'] = true;
                                }

                                if (
                                    str_contains($kategori, 'box') ||
                                    str_contains($kategori, 'packaging') ||
                                    str_contains($kategori, 'carton')
                                ) {
                                    $foundCategories['packaging'] = true;
                                }
                            }
                        }

                        $categories = [];

                        foreach ($categoryOrder as $categoryKey => $categoryLabel) {
                            if (isset($foundCategories[$categoryKey])) {
                                $categories[$categoryKey] = $categoryLabel;
                            }
                        }

                        /*
                         * Kalau data SPK belum mempunyai kategori, jangan
                         * membuat tabel kosong tanpa header. Gunakan header
                         * dasar monitoring.
                         */
                        if (empty($categories)) {
                            $categories = [
                                'rangka' => 'Rangka',
                                'anyam' => 'Anyam',
                                'dekor' => 'Dekor',
                                   'packaging' => 'Packaging / Box',
                            ];
                        }
                    @endphp

                    <div class="mn-card mb-4">

                        {{-- =================================================
                             HEADER PO
                             ================================================= --}}
                        <div class="mn-header mn-po-header">

                            <div class="mn-po-header-left">
                                <h6>
                                    PO : {{ $poNumber }}
                                    <span>({{ $buyer }})</span>
                                </h6>

                                <div class="mn-po-meta">
                                    @if ($country)
                                        <span class="mn-po-tag">
                                            {{ $country }}
                                        </span>
                                    @endif

                                    @if ($release)
                                        <span>
                                            Release:
                                            <strong>{{ $release }}</strong>
                                        </span>
                                    @endif

                                    @if ($shipment)
                                        <span>
                                            Shipment:
                                            <strong>{{ $shipment }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mn-po-header-right">
                                <span class="mn-po-count-badge">
                                    {{ $totalItems }} Items
                                </span>

                                @if ($totalValue !== null && $totalValue !== '')
                                    <span class="mn-po-total-badge">
                                        {{ is_numeric($totalValue) ? number_format((float) $totalValue, 0, ',', '.') : $totalValue }}
                                    </span>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-success btn-sm btn-toggle-po"
                                    title="Collapse / Expand"
                                >
                                    <i class="fa fa-chevron-down"></i>
                                </button>
                            </div>

                        </div>


                        {{-- =================================================
                             TABLE PO
                             1 ITEM = 1 BARIS
                             ================================================= --}}
                        <div class="table-responsive po-table">
                            <table class="table mn-table mn-po-monitoring-table align-middle">

                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center mn-image-head">Gambar</th>
                                        <th rowspan="2" class="text-center mn-qty-head">Qty</th>
                                        <th rowspan="2" class="text-start mn-item-head">Items Desc</th>

                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            <th colspan="2" class="text-center">
                                                {{ $categoryLabel }}
                                            </th>
                                        @endforeach
                                    </tr>

                                    <tr>
                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            <th class="text-center status-col">In</th>
                                            <th class="text-center status-col">Pass</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($details as $detailIndex => $detailNode)

                                        @php
                                            $detail = $detailNode;
                                            $detailSpks = collect($detailNode['spks'] ?? []);

                                            /*
                                             * Item utama diambil dari SPK yang
                                             * terkait dengan detail PO ini.
                                             */
                                            $firstSpkItem = null;

                                            foreach ($detailSpks as $spkTmp) {
                                                $candidate = collect($spkTmp['items'] ?? [])->first();

                                                if ($candidate) {
                                                    $firstSpkItem = $candidate;
                                                    break;
                                                }
                                            }

                                            $itemSource = $firstSpkItem ?: $detail;

                                            $itemName = data_get($itemSource, 'nama')
                                                ?? data_get($itemSource, 'item_name')
                                                ?? data_get($itemSource, 'nama_item')
                                                ?? data_get($itemSource, 'name')
                                                ?? data_get($itemSource, 'product_name')
                                                ?? data_get($itemSource, 'item')
                                                ?? data_get($detail, 'detail.description')
                                                ?? data_get($detail, 'description')
                                                ?? data_get($detail, 'item_name')
                                                ?? data_get($detail, 'nama_item')
                                                ?? data_get($detail, 'name')
                                                ?? '-';

                                            $qty = data_get($itemSource, 'qty')
                                                ?? data_get($itemSource, 'quantity')
                                                ?? data_get($itemSource, 'qty_spk')
                                                ?? data_get($detail, 'qty')
                                                ?? data_get($detail, 'quantity')
                                                ?? data_get($detail, 'qty_po')
                                                ?? 0;

                                            $image = data_get($itemSource, 'images.0')
                                                ?? data_get($itemSource, 'item_image')
                                                ?? data_get($itemSource, 'image')
                                                ?? data_get($itemSource, 'gambar')
                                                ?? data_get($detail, 'detail.photo')
                                                ?? data_get($detail, 'photo')
                                                ?? data_get($detail, 'item_image')
                                                ?? data_get($detail, 'image')
                                                ?? data_get($detail, 'gambar');

                                            $detailPoId = $detailNode['detail_po_id']
                                                ?? data_get($detail, 'id');

                                            /*
                                             * Mapping SPK ke kategori.
                                             * Satu SPK RANGKA + ANYAM dapat masuk
                                             * ke dua kategori sekaligus.
                                             */
                                            $spksByCategory = [];

                                            foreach ($categories as $categoryKey => $categoryLabel) {
                                                $spksByCategory[$categoryKey] = collect();
                                            }

                                            foreach ($detailSpks as $spkInfo) {
                                                $kategori = strtolower(trim((string) ($spkInfo['kategori'] ?? '')));

                                                if ($kategori === '') {
                                                    continue;
                                                }

                                                $targetCategories = [];

                                                if (str_contains($kategori, 'rangka')) {
                                                    $targetCategories[] = 'rangka';
                                                }

                                                if (str_contains($kategori, 'anyam')) {
                                                    $targetCategories[] = 'anyam';
                                                }

                                                if (str_contains($kategori, 'dekor') || str_contains($kategori, 'decor')) {
                                                    $targetCategories[] = 'dekor';
                                                }

                                                if (str_contains($kategori, 'finishing')) {
                                                    $targetCategories[] = 'finishing';
                                                }

                                                if (str_contains($kategori, 'unfinish')) {
                                                    $targetCategories[] = 'unfinish';
                                                }

                                                if (str_contains($kategori, 'final')) {
                                                    $targetCategories[] = 'final';
                                                }

                                                if (
                                                    str_contains($kategori, 'accessor') ||
                                                    str_contains($kategori, 'aksesor') ||
                                                    str_contains($kategori, 'aksesori')
                                                ) {
                                                    $targetCategories[] = 'accessories';
                                                }

                                                if (
                                                    str_contains($kategori, 'box') ||
                                                    str_contains($kategori, 'packaging') ||
                                                    str_contains($kategori, 'carton')
                                                ) {
                                                    $targetCategories[] = 'packaging';
                                                }

                                                foreach (array_unique($targetCategories) as $targetCategory) {
                                                    if (array_key_exists($targetCategory, $spksByCategory)) {
                                                        $spksByCategory[$targetCategory]->push($spkInfo);
                                                    }
                                                }
                                            }
                                        @endphp

                                        <tr>

                                            {{-- GAMBAR --}}
                                            <td class="text-center mn-image-cell">
                                                @if (!empty($image))
                                                    <img
                                                        src="{{ $image }}"
                                                        class="product-image"
                                                        loading="lazy"
                                                        decoding="async"
                                                        alt="{{ $itemName }}"
                                                    >
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            {{-- QTY --}}
                                            <td class="qty-col text-center">
                                                <span class="qty-badge">
                                                    {{ $qty }}
                                                </span>
                                            </td>

                                            {{-- ITEM --}}
                                            <td class="item-col">
                                                <a
                                                    href="#"
                                                    class="item-link"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#spkMappingModal{{ $poIndex }}{{ $detailIndex }}"
                                                    title="Lihat SPK {{ $itemName }}"
                                                >
                                                    {{ $itemName }}
                                                </a>

                                                @if ($detailPoId)
                                                    <small class="d-block text-muted mt-1">
                                                        Detail PO: {{ $detailPoId }}
                                                    </small>
                                                @endif
                                            </td>

                                            {{-- KATEGORI --}}
                                            @foreach ($categories as $categoryKey => $categoryLabel)

                                                @if ($categoryKey === 'finishing')
                                                    @continue
                                                @endif

                                                @php
                                                    $categorySpks = ($spksByCategory[$categoryKey] ?? collect())
                                                ->unique('spk_id')
                                                ->values();

                                            /*
                                             * =============================================================
                                             * QTY IN / PASS - GROUP PER COMPONENT
                                             * =============================================================
                                             *
                                             * 1. SPK tanpa custom component:
                                             *      Qty IN = total timeline type IN pada SPK.
                                             *
                                             * 2. SPK dengan component:
                                             *      - component diambil dari custom_columns
                                             *      - ProductionTimeline dicocokkan melalui remark
                                             *      - component yang sama digabung lintas SPK
                                             *      - Qty barang utuh = MIN(qty component)
                                             *
                                             * 3. RANGKA + ANYAM:
                                             *      Rangka dan Anyam dipisahkan berdasarkan component/remark.
                                             *
                                             * 4. PACKAGING / BOX:
                                             *      BOX, EMPTY, LAYER, SIKU, dst dihitung sebagai component.
                                             *
                                             * 5. Tooltip menggunakan Qty IN aktual per SPK.
                                             */

                                            $categoryIn = 0;
                                            $categoryPass = 0;

                                            $normalizeComponent = function ($value) {
                                                $value = strtoupper(trim((string) ($value ?? '')));
                                                $value = preg_replace('/[^A-Z0-9]+/', ' ', $value);
                                                $value = preg_replace('/\s+/', ' ', $value);

                                                $aliases = [
                                                    'B0X' => 'BOX',
                                                    'CARTON BOX' => 'BOX',
                                                    'CARTON' => 'BOX',
                                                ];

                                                return $aliases[$value] ?? $value;
                                            };

                                            /*
                                             * Ambil nama component dari custom_columns.
                                             */
                                            $getComponentName = function ($customColumn) use ($normalizeComponent) {
                                                if (is_string($customColumn)) {
                                                    return $normalizeComponent($customColumn);
                                                }

                                                if (!is_array($customColumn)) {
                                                    return '';
                                                }

                                                foreach ([
                                                    'proses',
                                                    'process',
                                                    'deskripsi',
                                                    'description',
                                                    'component',
                                                    'komponen',
                                                    'nama',
                                                    'name',
                                                    'jenis',
                                                ] as $key) {
                                                    $value = $customColumn[$key] ?? null;

                                                    if (
                                                        is_string($value)
                                                        && trim($value) !== ''
                                                    ) {
                                                        return $normalizeComponent($value);
                                                    }
                                                }

                                                return '';
                                            };

                                            /*
                                             * Apakah component termasuk kategori yang sedang dihitung?
                                             */
                                            $componentBelongsToCategory = function ($componentName, $categoryKey) use ($normalizeComponent) {
                                                $name = $normalizeComponent($componentName);

                                                if ($name === '') {
                                                    return false;
                                                }

                                                return match ($categoryKey) {
                                                    'rangka' =>
                                                        str_contains($name, 'RANGKA')
                                                        && !str_contains($name, 'ANYAM'),
                                                    'anyam' =>
                                                        str_contains($name, 'ANYAM'),
                                                    'dekor' =>
                                                        str_contains($name, 'DEKOR')
                                                        || str_contains($name, 'DECOR'),
                                                    'unfinish' =>
                                                        str_contains($name, 'UNFINISH'),
                                                    'final' =>
                                                        str_contains($name, 'FINAL'),
                                                    'accessories' =>
                                                        str_contains($name, 'ACCESSOR')
                                                        || str_contains($name, 'AKSESOR'),
                                                    'packaging' =>
                                                        str_contains($name, 'BOX')
                                                        || str_contains($name, 'LAYER')
                                                        || str_contains($name, 'EMPTY')
                                                        || str_contains($name, 'CARTON')
                                                        || str_contains($name, 'PACKAGING')
                                                        || str_contains($name, 'SIKU'),
                                                    default => false,
                                                };
                                            };

                                            /*
                                             * =============================================================
                                             * DATA PER SPK
                                             * =============================================================
                                             */
                                            $spkInTotals = [];
                                            $spkComponentIn = [];
                                            $spkComponentDefinitions = [];
                                            $passTotalsBySpk = [];

                                            /*
                                             * Global component map.
                                             *
                                             * [COMPONENT] => true
                                             * Hanya nama component yang benar-benar didefinisikan
                                             * oleh SPK kategori ini.
                                             */
                                            $globalComponentNames = [];

                                            foreach ($categorySpks as $spkDetail) {

                                                $spkId = (int) ($spkDetail['spk_id'] ?? 0);
                                                $spkItems = collect($spkDetail['items'] ?? []);

                                                $spkQty = 0;

                                                foreach ($spkItems as $spkItem) {
                                                    $candidateQty =
                                                        data_get($spkItem, 'qty')
                                                        ?? data_get($spkItem, 'quantity')
                                                        ?? data_get($spkItem, 'qty_spk')
                                                        ?? 0;

                                                    if (is_numeric($candidateQty)) {
                                                        $spkQty += (float) $candidateQty;
                                                    }
                                                }

                                                if ($spkQty <= 0) {
                                                    $spkQty = (float) $qty;
                                                }

                                                /* ---------------------------------------------
                                                 * COMPONENT DEFINITION SPK
                                                 * --------------------------------------------- */
                                                $definitions = [];

                                                foreach ($spkItems as $spkItem) {

                                                    $customColumns = $spkItem['custom_columns'] ?? [];

                                                    if (is_string($customColumns)) {
                                                        $decoded = json_decode($customColumns, true);
                                                        $customColumns = is_array($decoded) ? $decoded : [];
                                                    }

                                                    if (!is_array($customColumns)) {
                                                        continue;
                                                    }

                                                    foreach ($customColumns as $customColumn) {

                                                        $componentName = $getComponentName($customColumn);

                                                        if ($componentName === '') {
                                                            continue;
                                                        }

                                                        if (!$componentBelongsToCategory(
                                                            $componentName,
                                                            $categoryKey
                                                        )) {
                                                            continue;
                                                        }

                                                        /*
                                                         * pcs = total kebutuhan component untuk SPK.
                                                         * Jika tidak ada pcs, 1 component = 1 unit produk.
                                                         */
                                                        $componentPcs = is_array($customColumn)
                                                            ? (
                                                                $customColumn['pcs']
                                                                ?? $customColumn['qty']
                                                                ?? $customColumn['quantity']
                                                                ?? null
                                                            )
                                                            : null;

                                                        $requiredTotal =
                                                            is_numeric($componentPcs)
                                                            && (float) $componentPcs > 0
                                                                ? (float) $componentPcs
                                                                : $spkQty;

                                                        $requirementPerUnit =
                                                            $spkQty > 0
                                                                ? $requiredTotal / $spkQty
                                                                : 1;

                                                        if ($requirementPerUnit <= 0) {
                                                            $requirementPerUnit = 1;
                                                        }

                                                        /* Jangan double count component yang sama dalam satu SPK. */
                                                        if (!isset($definitions[$componentName])) {
                                                            $definitions[$componentName] = [
                                                                'requirement_per_unit' => $requirementPerUnit,
                                                                'required_total' => $requiredTotal,
                                                                'spk_qty' => $spkQty,
                                                            ];

                                                            $globalComponentNames[$componentName] = true;
                                                        }
                                                    }
                                                }

                                                $spkComponentDefinitions[$spkId] = $definitions;

                                                /* ---------------------------------------------
                                                 * PRODUCTION TIMELINE IN PER SPK
                                                 * --------------------------------------------- */
                                                $spkInTotals[$spkId] = 0;
                                                $spkComponentIn[$spkId] = [];

                                                foreach (collect($spkDetail['production_timeline'] ?? []) as $timelineRow) {

                                                    $type = strtolower(trim((string) (
                                                        $timelineRow['type'] ?? ''
                                                    )));

                                                    if (!in_array($type, ['in', 'masuk'], true)) {
                                                        continue;
                                                    }

                                                    $timelineQty = is_numeric($timelineRow['qty'] ?? null)
                                                        ? (float) $timelineRow['qty']
                                                        : 0;

                                                    if ($timelineQty <= 0) {
                                                        continue;
                                                    }

                                                    /* Total SPK untuk fallback SPK tanpa component. */
                                                    $spkInTotals[$spkId] += $timelineQty;

                                                    $remark = $normalizeComponent(
                                                        $timelineRow['remark']
                                                        ?? $timelineRow['keterangan']
                                                        ?? ''
                                                    );

                                                    if ($remark === '' || $remark === 'NULL') {
                                                        continue;
                                                    }

                                                    $spkComponentIn[$spkId][$remark] =
                                                        ($spkComponentIn[$spkId][$remark] ?? 0)
                                                        + $timelineQty;
                                                }

                                                /* ---------------------------------------------
                                                 * INSPECTION PASS PER SPK
                                                 * --------------------------------------------- */
                                                $passTotalsBySpk[$spkId] = 0;

                                                foreach (collect($spkDetail['inspection_schedule'] ?? []) as $inspectionRow) {
                                                    $passed = is_numeric($inspectionRow['passed'] ?? null)
                                                        ? (float) $inspectionRow['passed']
                                                        : 0;

                                                    if ($passed > 0) {
                                                        $passTotalsBySpk[$spkId] += $passed;
                                                    }
                                                }
                                            }

                                            /*
                                             * =============================================================
                                             * GLOBAL COMPONENT IN
                                             * =============================================================
                                             *
                                             * Untuk setiap component:
                                             *   component IN = SUM(component IN yang sudah dikonversi
                                             *                    menjadi unit produk dari masing-masing SPK)
                                             *
                                             * Setelah semua component didapat:
                                             *   Qty IN utuh = MIN(semua component)
                                             */
                                            if (!empty($globalComponentNames)) {

                                                $componentCompleteTotals = [];

                                                foreach (array_keys($globalComponentNames) as $componentName) {

                                                    $completeQty = 0;

                                                    foreach ($categorySpks as $spkDetail) {

                                                        $spkId = (int) ($spkDetail['spk_id'] ?? 0);
                                                        $definitions = $spkComponentDefinitions[$spkId] ?? [];

                                                        if (!isset($definitions[$componentName])) {
                                                            continue;
                                                        }

                                                        $actual = 0;

                                                        foreach (($spkComponentIn[$spkId] ?? []) as $timelineComponent => $timelineQty) {

                                                            if (
                                                                $timelineComponent === $componentName
                                                                || str_contains($timelineComponent, $componentName)
                                                                || str_contains($componentName, $timelineComponent)
                                                            ) {
                                                                $actual += (float) $timelineQty;
                                                            }
                                                        }

                                                        $ratio = (float) (
                                                            $definitions[$componentName]['requirement_per_unit']
                                                            ?? 1
                                                        );

                                                        if ($ratio <= 0) {
                                                            $ratio = 1;
                                                        }

                                                        $completeQty +=
                                                            $actual / $ratio;
                                                    }

                                                    $componentCompleteTotals[$componentName] =
                                                        max(0, $completeQty);
                                                }

                                                if (!empty($componentCompleteTotals)) {
                                                    $categoryIn = min(
                                                        $componentCompleteTotals
                                                    );
                                                }

                                            } else {

                                                /*
                                                 * =============================================================
                                                 * FALLBACK SPK TANPA COMPONENT
                                                 * =============================================================
                                                 *
                                                 * Contoh Ring Driftwood:
                                                 * custom_columns = []
                                                 * timeline = type IN, qty 50, remark NULL
                                                 *
                                                 * Maka hasil = 50.
                                                 */
                                                $categoryIn = array_sum($spkInTotals);
                                            }

                                            /* Jangan pernah melebihi Qty item. */
                                            if ((float) $qty > 0) {
                                                $categoryIn = min(
                                                    $categoryIn,
                                                    (float) $qty
                                                );
                                            }

                                            /*
                                             * PASS belum mempunyai mapping component pada data InspectionSchedule.
                                             * Karena itu PASS menggunakan total passed kategori.
                                             */
                                            $categoryPass = array_sum($passTotalsBySpk);

                                            if ((float) $qty > 0) {
                                                $categoryPass = min(
                                                    $categoryPass,
                                                    (float) $qty
                                                );
                                            }

                                            /*
                                             * =============================================================
                                             * TOOLTIP QTY IN PER SPK
                                             * =============================================================
                                             */
                                            $spkTooltipRows = [];

                                            foreach ($categorySpks as $spkDetail) {

                                                $spkId = (int) ($spkDetail['spk_id'] ?? 0);
                                                $spkDefinitions = $spkComponentDefinitions[$spkId] ?? [];

                                                /*
                                                 * Default: total IN SPK.
                                                 * Ini penting untuk SPK tanpa component sehingga
                                                 * Ring Driftwood tidak menjadi 0.
                                                 */
                                                $spkQtyIn = $spkInTotals[$spkId] ?? 0;

                                                if (!empty($spkDefinitions)) {

                                                    $localComponentResults = [];

                                                    foreach ($spkDefinitions as $componentName => $definition) {

                                                        $actual = 0;

                                                        foreach (($spkComponentIn[$spkId] ?? []) as $timelineComponent => $timelineQty) {

                                                            if (
                                                                $timelineComponent === $componentName
                                                                || str_contains($timelineComponent, $componentName)
                                                                || str_contains($componentName, $timelineComponent)
                                                            ) {
                                                                $actual += (float) $timelineQty;
                                                            }
                                                        }

                                                        $ratio = (float) (
                                                            $definition['requirement_per_unit']
                                                            ?? 1
                                                        );

                                                        if ($ratio <= 0) {
                                                            $ratio = 1;
                                                        }

                                                        $localComponentResults[] =
                                                            $actual / $ratio;
                                                    }

                                                    if (!empty($localComponentResults)) {
                                                        $spkQtyIn = min(
                                                            $localComponentResults
                                                        );
                                                    }
                                                }

                                                $spkTooltipRows[$spkId] = max(
                                                    0,
                                                    $spkQtyIn
                                                );
                                            }

                                            $formatQty = function ($value) {
                                                $value = (float) $value;

                                                return abs($value - round($value)) < 0.000001
                                                    ? number_format($value, 0, ',', '.')
                                                    : rtrim(
                                                        rtrim(
                                                            number_format($value, 2, ',', '.'),
                                                            '0'
                                                        ),
                                                        ','
                                                    );
                                            };

                                        @endphp

                                                <td class="text-center status-col">

                                                    @if ($categorySpks->isNotEmpty())

                                                        <span
                                                            class="spk-hover-target text-primary fw-bold"
                                                            data-tooltip-type="in"
                                                            title="{{ $categorySpks->count() }} SPK"
                                                        >
                                                            {{ $formatQty($categoryIn) }}

                                                            @if ($categorySpks->count() > 0)
                                                                <span class="spk-under-value">
                                                                    <span class="spk-under-value-line">
                                                                        {{ $categorySpks->count() }} SPK
                                                                    </span>
                                                                </span>
                                                            @endif

                                                            <span class="spk-list-tooltip">

                                                                <span class="spk-list-tooltip-title">
                                                                    <strong>{{ $categoryLabel }} — SPK</strong>
                                                                    <span class="spk-list-tooltip-count">
                                                                        {{ $categorySpks->count() }} SPK
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
                                                                                <th class="col-total">QTY IN</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($categorySpks as $spkNo => $spkDetail)
                                                                                @php
                                                                                    $spkDescription = [];

                                                                                    foreach (collect($spkDetail['items'] ?? []) as $componentItem) {
                                                                                        $componentName = data_get($componentItem, 'nama')
                                                                                            ?? data_get($componentItem, 'item_name')
                                                                                            ?? data_get($componentItem, 'nama_item')
                                                                                            ?? data_get($componentItem, 'name')
                                                                                            ?? data_get($componentItem, 'product_name');

                                                                                        if ($componentName) {
                                                                                            $spkDescription[] = $componentName;
                                                                                        }
                                                                                    }
                                                                                @endphp

                                                                                <tr>
                                                                                    <td class="col-no">{{ $spkNo + 1 }}</td>
                                                                                    <td class="col-spk">
                                                                                        <a
                                                                                            href="{{ url('spk/edit/' . ($spkDetail['spk_id'] ?? '')) }}"
                                                                                            class="spk-link"
                                                                                        >
                                                                                            {{ $spkDetail['no_spk'] ?? '-' }}
                                                                                        </a>
                                                                                    </td>
                                                                                    <td class="col-sub">
                                                                                        {{ $spkDetail['supplier'] ?? '-' }}
                                                                                    </td>
                                                                                    <td class="col-category">
                                                                                        {{ $spkDetail['kategori'] ?? '-' }}
                                                                                    </td>
                                                                                    <td class="col-description">
                                                                                        {{ !empty($spkDescription) ? implode(', ', $spkDescription) : '-' }}
                                                                                    </td>
                                                                                    <td class="col-total">
                                                                                        @php
                                                                                            $tooltipQtyIn = 0;
                                                                                            $tooltipSpkId = (int) ($spkDetail['spk_id'] ?? 0);
                                                                                            foreach ($spkTooltipRows as $tooltipRow) {
                                                                                                if ((int) data_get($tooltipRow, 'spk.spk_id') === $tooltipSpkId) {
                                                                                                    $tooltipQtyIn = (float) ($tooltipRow['qty_in'] ?? 0);
                                                                                                    break;
                                                                                                }
                                                                                            }
                                                                                        @endphp
                                                                                        {{ $formatQty($tooltipQtyIn) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </span>

                                                            </span>
                                                        </span>

                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif

                                                </td>

                                                {{-- PASS --}}
                                                <td class="text-center status-col">
                                                    @if ($categorySpks->isNotEmpty())
                                                        <span class="fw-bold">{{ $formatQty($categoryPass) }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>

                                            @endforeach

                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- =====================================================
                         MODAL MAPPING SPK PER ITEM
                         ===================================================== --}}
                    @foreach ($details as $detailIndex => $detailNode)
                        @php
                            $detail = $detailNode;
                            $detailSpks = collect($detailNode['spks'] ?? []);

                            $firstSpkItem = null;
                            foreach ($detailSpks as $spkTmp) {
                                $candidate = collect($spkTmp['items'] ?? [])->first();
                                if ($candidate) {
                                    $firstSpkItem = $candidate;
                                    break;
                                }
                            }

                            $itemSource = $firstSpkItem ?: $detail;

                            $itemName = data_get($itemSource, 'nama')
                                ?? data_get($itemSource, 'item_name')
                                ?? data_get($itemSource, 'nama_item')
                                ?? data_get($itemSource, 'name')
                                ?? data_get($itemSource, 'product_name')
                                ?? data_get($detail, 'detail.description')
                                ?? data_get($detail, 'description')
                                ?? data_get($itemSource, 'item')
                                ?? '-';

                            $qty = data_get($itemSource, 'qty')
                                ?? data_get($itemSource, 'quantity')
                                ?? data_get($itemSource, 'qty_spk')
                                ?? data_get($detail, 'qty')
                                ?? data_get($detail, 'quantity')
                                ?? data_get($detail, 'qty_po')
                                ?? 0;
                        @endphp

                        <div class="modal fade" id="spkMappingModal{{ $poIndex }}{{ $detailIndex }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">

                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title">
                                            SPK — {{ $itemName }}
                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"
                                        ></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <div class="fw-bold">{{ $itemName }}</div>
                                            <div class="text-muted">Qty PO : {{ $qty }}</div>
                                            <div class="text-muted">
                                                Detail PO : {{ $detailNode['detail_po_id'] ?? '-' }}
                                            </div>
                                        </div>

                                        @forelse ($detailSpks as $spk)
                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <span class="badge bg-primary">
                                                                {{ strtoupper($spk['kategori'] ?? '-') }}
                                                            </span>
                                                        </div>

                                                        <span class="badge bg-success">
                                                            SPK #{{ $spk['spk_id'] ?? '-' }}
                                                        </span>
                                                    </div>

                                                    <table class="table table-sm mb-0">
                                                        <tr>
                                                            <td width="140">No SPK</td>
                                                            <td>
                                                                :
                                                                <a
                                                                    href="{{ url('spk/edit/' . ($spk['spk_id'] ?? '')) }}"
                                                                    class="fw-bold text-primary text-decoration-underline"
                                                                >
                                                                    {{ $spk['no_spk'] ?? '-' }}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Supplier</td>
                                                            <td>: {{ $spk['supplier'] ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Kategori</td>
                                                            <td>: {{ $spk['kategori'] ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Jumlah Item SPK</td>
                                                            <td>: {{ collect($spk['items'] ?? [])->count() }}</td>
                                                        </tr>
                                                    </table>

                                                    @if (collect($spk['items'] ?? [])->isNotEmpty())
                                                        <div class="table-responsive mt-2">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Component / Item SPK</th>
                                                                        <th class="text-end">Qty</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach (collect($spk['items'] ?? []) as $componentItem)
                                                                        <tr>
                                                                            <td>
                                                                                {{ data_get($componentItem, 'nama')
                                                                                    ?? data_get($componentItem, 'item_name')
                                                                                    ?? data_get($componentItem, 'nama_item')
                                                                                    ?? data_get($componentItem, 'name')
                                                                                    ?? data_get($componentItem, 'product_name')
                                                                                    ?? '-' }}
                                                                            </td>
                                                                            <td class="text-end">
                                                                                {{ data_get($componentItem, 'qty')
                                                                                    ?? data_get($componentItem, 'quantity')
                                                                                    ?? '-' }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="alert alert-warning mb-0">
                                                Tidak ada SPK yang terkait dengan Detail PO ini.
                                            </div>
                                        @endforelse

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                @empty
                    <div class="mn-empty">
                        <h5 class="mb-2">Data Tidak Ditemukan</h5>
                        <div class="text-muted">
                            Coba cari PO atau batch lain
                        </div>
                    </div>
                @endforelse

            </div>
                