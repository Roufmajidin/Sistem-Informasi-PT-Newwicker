{{-- ========================================================= --}}
{{-- HISTORY STOCK MONITORING --}}
{{-- ========================================================= --}}

@forelse ($history as $header)

    @php
        /*
        |--------------------------------------------------------------------------
        | FLATTEN LOADING HISTORY
        |--------------------------------------------------------------------------
        | Semua history loading dikumpulkan berdasarkan ARTICLE.
        | Article yang sama akan dibuat menjadi satu group dan nanti
        | cell-nya di-merge menggunakan rowspan.
        */

        $articleGroups = collect();

        foreach ($header->detailPos as $detail) {

            $item = $detail->item ?? [];

            $article = trim(
                (string) ($item['article_nr_'] ?? '')
            );

            $groupKey = $article !== ''
                ? $article
                : 'detail-' . $detail->id;

            if (!$articleGroups->has($groupKey)) {

                $articleGroups->put($groupKey, [
                    'article' => $article,
                    'description' => $item['description'] ?? '-',
                    'qty' => (float) ($item['qty'] ?? 0),
                    'cbm' => (float) ($item['cbm'] ?? 0),
                    'total_cbm' => (float) ($item['total_cbm'] ?? 0),
                    'loads' => collect(),
                ]);

            }

            foreach (($detail->loading_history ?? []) as $load) {

                $articleGroups[$groupKey]['loads']->push([
                    'qty' => (float) ($load['qty'] ?? 0),
                    'release_date' => $load['release_date'] ?? null,
                    'ipl_id' => $load['ipl_id'] ?? null,
                    'invoice_no' => $load['invoice_no'] ?? null,
                    'container_no' => $load['container_no'] ?? null,
                ]);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | HANYA TAMPILKAN ARTICLE YANG MEMILIKI HISTORY
        |--------------------------------------------------------------------------
        */

        $articleGroups = $articleGroups
            ->filter(function ($group) {
                return $group['loads']->isNotEmpty();
            })
            ->values();

    @endphp


    <div
        class="po-group mb-4"
        data-company="{{ strtolower($header->company_name) }}"
        data-po="{{ strtolower($header->order_no) }}"
    >

        {{-- ========================================================= --}}
        {{-- HEADER PO --}}
        {{-- ========================================================= --}}
        <div class="bg-success text-white px-3 py-2 rounded-top">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <strong style="font-size:16px">
                        {{ strtoupper($header->company_name) }}
                    </strong>

                    <span class="mx-2">|</span>

                    <strong>
                        {{ $header->order_no }}
                    </strong>

                </div>

                <div>

                    <span class="badge bg-light text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Fully Loaded
                    </span>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- TABLE --}}
        {{-- ========================================================= --}}
        <div class="table-responsive">

            <table class="table table-bordered table-hover table-sm mb-0">

                <thead class="table-light">

                    <tr>

                        <th width="50">No</th>
                        <th width="120">Article</th>
                        <th>Description</th>
                        <th width="80">Qty</th>
                        <th width="100">Qty Loaded</th>
                        <th width="80">CBM</th>
                        <th width="90">Total CBM</th>
                        <th width="100">Ket</th>
                        <th width="130">Date / Released</th>
                        <th width="70">IPL</th>
                        <th width="140">Invoice</th>
                        <th width="120">Container</th>

                    </tr>

                </thead>


                <tbody>

                    @php
                        $no = 1;
                    @endphp


                    @forelse ($articleGroups as $group)

                        @php
                            $rowspan = $group['loads']->count();

                            $article = $group['article'];
                            $description = $group['description'];
                            $qtyPo = $group['qty'];
                            $cbm = $group['cbm'];
                            $totalCbm = $group['total_cbm'];
                        @endphp


                        @foreach ($group['loads'] as $loadIndex => $load)

                            <tr
                                class="search-row"
                                data-search="{{ strtolower(
                                    $header->company_name . ' ' .
                                    $header->order_no . ' ' .
                                    $article . ' ' .
                                    $description . ' ' .
                                    ($load['invoice_no'] ?? '') . ' ' .
                                    ($load['container_no'] ?? '')
                                ) }}"
                            >

                                {{-- ================================================= --}}
                                {{-- MERGED COLUMNS --}}
                                {{-- ================================================= --}}

                                @if ($loadIndex === 0)

                                    {{-- NO --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="text-center"
                                    >
                                        {{ $no++ }}
                                    </td>


                                    {{-- ARTICLE --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="align-middle"
                                    >
                                        <strong>
                                            {{ $article ?: '-' }}
                                        </strong>
                                    </td>


                                    {{-- DESCRIPTION --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="align-middle item-b"
                                    >
                                        {{ $description ?: '-' }}
                                    </td>


                                    {{-- QTY PO --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="text-center align-middle"
                                    >
                                        {{ number_format($qtyPo) }}
                                    </td>

                                @endif


                                {{-- ================================================= --}}
                                {{-- QTY LOADED --}}
                                {{-- ================================================= --}}

                                <td class="text-center">

                                    <strong class="text-success">

                                        {{ number_format(
                                            (float)($load['qty'] ?? 0)
                                        ) }}

                                    </strong>

                                </td>


                                {{-- ================================================= --}}
                                {{-- CBM --}}
                                {{-- ================================================= --}}

                                @if ($loadIndex === 0)

                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="text-center align-middle"
                                    >
                                        {{
                                            rtrim(
                                                rtrim(
                                                    number_format(
                                                        $cbm,
                                                        2,
                                                        '.',
                                                        ''
                                                    ),
                                                    '0'
                                                ),
                                                '.'
                                            )
                                        }}
                                    </td>


                                    {{-- TOTAL CBM --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="text-center align-middle"
                                    >
                                        {{
                                            rtrim(
                                                rtrim(
                                                    number_format(
                                                        $totalCbm,
                                                        2,
                                                        '.',
                                                        ''
                                                    ),
                                                    '0'
                                                ),
                                                '.'
                                            )
                                        }}
                                    </td>


                                    {{-- KETERANGAN --}}
                                    <td
                                        rowspan="{{ $rowspan }}"
                                        class="text-center align-middle"
                                    >
                                        <span class="text-success">
                                            Loaded
                                        </span>
                                    </td>

                                @endif


                                {{-- ================================================= --}}
                                {{-- RELEASE DATE --}}
                                {{-- ================================================= --}}

                                <td class="text-center">

                                    @if (!empty($load['release_date']))

                                        {{ \Carbon\Carbon::parse(
                                            $load['release_date']
                                        )->format('d/m/Y') }}

                                    @else

                                        <span class="text-muted">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- IPL --}}
                                {{-- ================================================= --}}

                                <td class="text-center">

                                    @if (!empty($load['ipl_id']))

                                        <span class="badge bg-secondary">
                                            #{{ $load['ipl_id'] }}
                                        </span>

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- ================================================= --}}
                                {{-- INVOICE --}}
                                {{-- ================================================= --}}

                                <td>
                                    {{ $load['invoice_no'] ?? '-' }}
                                </td>


                                {{-- ================================================= --}}
                                {{-- CONTAINER --}}
                                {{-- ================================================= --}}

                                <td>
                                    {{ $load['container_no'] ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    @empty

                        <tr>

                            <td
                                colspan="12"
                                class="text-center py-4 text-muted"
                            >
                                Tidak ada history loading.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@empty

    <div class="text-center py-5 text-muted">

        <i class="fas fa-history fa-2x mb-2"></i>

        <div>
            Belum ada history PO yang fully loaded.
        </div>

    </div>

@endforelse
