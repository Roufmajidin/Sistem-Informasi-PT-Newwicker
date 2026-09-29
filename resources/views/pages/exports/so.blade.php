@extends('master.master')

@section('content')
    @include('pages.spk.monitoring-payment.style')

    <div class="card shadow-sm">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <div class="card-header py-3 mt-4">

            <div class="row align-items-center">

                <div class="col-md-4">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        Stock Monitoring
                    </h5>
                </div>

                <div class="col-md-5">
                    <input
                        type="text"
                        id="searchTable"
                        class="form-control form-control-sm"
                        placeholder="Cari PO, Company, Description, Article..."
                    >
                </div>

                <div class="col-md-3 text-end">
                    <select id="sortBy" class="form-select form-select-sm">
                        <option value="">Urutkan</option>
                        <option value="po">PO</option>
                        <option value="company">Company</option>
                    </select>
                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- TAB --}}
        {{-- ========================================================= --}}
        <div class="px-3 pt-3">

            <ul class="nav nav-tabs" id="stockTabs">

                <li class="nav-item">
                    <button
                        class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-progress"
                        type="button"
                    >
                        <i class="fas fa-spinner me-1"></i>
                        On Progress

                        <span class="badge bg-warning text-dark ms-1">
                            {{ $onProgress->count() }}
                        </span>
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-history"
                        type="button"
                    >
                        <i class="fas fa-check-circle me-1"></i>
                        History

                        <span class="badge bg-success ms-1">
                            {{ $history->count() }}
                        </span>
                    </button>
                </li>

            </ul>

        </div>


        {{-- ========================================================= --}}
        {{-- TAB CONTENT --}}
        {{-- ========================================================= --}}
        <div class="tab-content">

            {{-- ===================================================== --}}
            {{-- ON PROGRESS --}}
            {{-- ===================================================== --}}
            <div
                class="tab-pane fade show active"
                id="tab-progress"
            >

                <div class="card-body p-2">

                    @forelse ($onProgress as $header)

                        <div
                            class="po-group mb-4"
                            data-company="{{ strtolower($header->company_name) }}"
                            data-po="{{ strtolower($header->order_no) }}"
                        >

                            {{-- HEADER PO --}}
                            <div class="bg-primary text-white px-3 py-2 rounded-top">

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
                                        <span class="badge bg-light text-primary">
                                            <i class="fas fa-spinner me-1"></i>
                                            On Progress
                                        </span>
                                    </div>

                                </div>

                            </div>


                            {{-- TABLE --}}
                            <div class="table-responsive">

                                <table class="table table-bordered table-hover table-sm mb-0">

                                    <thead class="table-light">

                                        <tr>
                                            <th width="50">No</th>
                                            <th width="120">Article</th>
                                            <th>Description</th>
                                            <th width="80">Qty</th>
                                            <th width="100">Qty Loaded</th>
                                            <th width="90">Sisa</th>
                                            <th width="80">CBM</th>
                                            <th width="90">Total CBM</th>
                                            <th width="180">Ket</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        @php
                                            $no = 1;
                                        @endphp

                                        @foreach ($header->detailPos as $detail)

                                            @php
                                                $item = $detail->item ?? [];

                                                $qtyPo = (float) ($item['qty'] ?? 0);
                                                $loadedQty = (float) ($detail->loaded_qty ?? 0);
                                                $availableQty = max(0, $qtyPo - $loadedQty);
                                            @endphp

                                            <tr
                                                class="search-row"
                                                data-search="{{ strtolower(
                                                    $header->company_name . ' ' .
                                                    $header->order_no . ' ' .
                                                    ($item['description'] ?? '') . ' ' .
                                                    ($item['article_nr_'] ?? '')
                                                ) }}"
                                            >

                                                <td>
                                                    {{ $no++ }}
                                                </td>

                                                <td>
                                                    {{ $item['article_nr_'] ?? '-' }}
                                                </td>

                                                <td class="item-b">
                                                    {{ $item['description'] ?? '-' }}
                                                </td>

                                                <td class="text-center">
                                                    {{ number_format($qtyPo) }}
                                                </td>

                                                <td class="text-center">

                                                    @if ($loadedQty > 0)

                                                        <strong class="text-success">
                                                            {{ number_format($loadedQty) }}
                                                        </strong>

                                                    @else

                                                        <span class="text-muted">-</span>

                                                    @endif

                                                </td>

                                                <td class="text-center">

                                                    @if ($availableQty > 0)

                                                        <strong class="text-danger">
                                                            {{ number_format($availableQty) }}
                                                        </strong>

                                                    @else

                                                        <span class="text-success">0</span>

                                                    @endif

                                                </td>

                                                <td class="text-center">
                                                    {{
                                                        rtrim(
                                                            rtrim(
                                                                number_format(
                                                                    (float)($item['cbm'] ?? 0),
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

                                                <td class="text-center">
                                                    {{
                                                        rtrim(
                                                            rtrim(
                                                                number_format(
                                                                    (float)($item['total_cbm'] ?? 0),
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

                                                <td>
                                                    @if ($loadedQty > 0)
                                                        <span class="text-success">
                                                            Partial Loaded
                                                        </span>
                                                    @else
                                                        <span class="text-muted">
                                                            Belum Loaded
                                                        </span>
                                                    @endif
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @empty

                        <div class="text-center py-5 text-muted">

                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>

                            <div>
                                Semua PO sudah fully loaded.
                            </div>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- ===================================================== --}}
            {{-- HISTORY --}}
            {{-- ===================================================== --}}
            <div
                class="tab-pane fade"
                id="tab-history"
            >

                <div class="card-body p-2">

                    @include('pages.exports.so_history')

                </div>

            </div>

        </div>

    </div>


    <style>
        .po-group {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .po-group table {
            margin-bottom: 0;
        }

        .po-group thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 5;
            white-space: nowrap;
        }

        .nav-tabs .nav-link {
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>


    <script>
        $(document).ready(function () {

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            $('#searchTable').on('keyup', function () {

                let keyword = $(this).val().toLowerCase().trim();

                $('.po-group').each(function () {

                    let found = false;

                    $(this).find('.search-row').each(function () {

                        let searchText = $(this).data('search') || '';

                        if (searchText.includes(keyword)) {

                            $(this).show();
                            found = true;

                        } else {

                            $(this).hide();

                        }

                    });

                    $(this).toggle(found);

                });

            });


            /*
            |--------------------------------------------------------------------------
            | SORT
            |--------------------------------------------------------------------------
            */

            $('#sortBy').change(function () {

                let value = $(this).val();

                if (!value) {
                    return;
                }

                $('.tab-pane').each(function () {

                    let container = $(this);

                    let groups = container.find('.po-group').get();

                    groups.sort(function (a, b) {

                        let av;
                        let bv;

                        if (value === 'company') {

                            av = $(a).data('company') || '';
                            bv = $(b).data('company') || '';

                        } else {

                            av = $(a).data('po') || '';
                            bv = $(b).data('po') || '';

                        }

                        return String(av).localeCompare(
                            String(bv),
                            undefined,
                            {
                                numeric: true,
                                sensitivity: 'base'
                            }
                        );

                    });

                    $.each(groups, function (_, group) {

                        container
                            .find('.card-body')
                            .first()
                            .append(group);

                    });

                });

            });


            /*
            |--------------------------------------------------------------------------
            | TAB REMEMBER
            |--------------------------------------------------------------------------
            */

            $('#stockTabs button').on('shown.bs.tab', function (e) {

                localStorage.setItem(
                    'stockMonitoringTab',
                    $(e.target).attr('data-bs-target')
                );

            });


            let savedTab = localStorage.getItem(
                'stockMonitoringTab'
            );

            if (savedTab) {

                let tabButton = $(
                    '#stockTabs button[data-bs-target="' +
                    savedTab +
                    '"]'
                );

                if (tabButton.length) {

                    if (typeof bootstrap !== 'undefined') {

                        new bootstrap.Tab(
                            tabButton[0]
                        ).show();

                    } else {

                        tabButton.tab('show');

                    }

                }

            }

        });
    </script>

@endsection
