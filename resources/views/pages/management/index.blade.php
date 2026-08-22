@extends('master.master')

@section('title', 'Production Monitoring')

@section('content')
    @section('btn')
<div>
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-boxes text-primary me-5"></i>
                        Production Monitoring
                    </h4>
                    <small class="text-muted">
                        
                    </small>
                </div>    
    @endsection
    <div class="container-fluid mt-4 mn-erp-page">

        <style>
/* =========================================================
   PRODUCTION MONITORING — READABLE SCALE
   Optimized for Chrome 100%
   ========================================================= */

.mn-erp-page {
    --mn-primary: #2563eb;
    --mn-primary-hover: #1d4ed8;
    --mn-success: #16a34a;
    --mn-danger: #dc2626;
    --mn-warning: #d97706;
    --mn-text: #172033;
    --mn-muted: #667085;
    --mn-border: #dfe5ec;
    --mn-soft: #f8fafc;
    --mn-blue-soft: #eff6ff;

    color: var(--mn-text);
    font-size: 14px;
    line-height: 1.45;
    padding: 10px 12px 30px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.mn-erp-page * {
    box-sizing: border-box;
}

/* FILTER */
.mn-filter {
    background: #fff;
    border: 1px solid var(--mn-border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px !important;
    box-shadow: 0 2px 10px rgba(16,24,40,.045);
}

.mn-toolbar {
    display: grid;
    grid-template-columns: minmax(300px,1fr) auto auto auto;
    align-items: end;
    gap: 12px;
}

.mn-field-label {
    margin: 0 0 6px 2px;
    color: var(--mn-muted);
    font-size: 11px;
    line-height: 1.2;
    font-weight: 750;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.mn-search { min-width: 0; }

.mn-search-box {
    height: 44px;
    display: flex;
    align-items: center;
    padding: 0 12px;
    background: #fff;
    border: 1px solid var(--mn-border);
    border-radius: 8px;
    transition: .18s ease;
}

.mn-search-box:focus-within {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37,99,235,.07);
}

.mn-search-box > i {
    margin-right: 9px;
    color: #98a2b3;
    font-size: 14px;
}

.mn-search-box input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--mn-text);
    font-size: 13px;
}

.mn-search-box input::placeholder { color: #98a2b3; }

.mn-search-clear {
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 6px;
    background: #eef2f6;
    color: #667085;
    cursor: pointer;
}

.mn-search-help {
    margin: 5px 0 0 2px;
    color: #98a2b3;
    font-size: 10px;
}

.mn-search-help i { margin-right: 3px; }

.mn-brand-filter,
.mn-sort,
.mn-actions { flex-shrink: 0; }

.mn-brand-group {
    height: 44px;
    display: inline-flex;
    align-items: center;
    padding: 3px;
    border: 1px solid var(--mn-border);
    border-radius: 8px;
    background: var(--mn-soft);
}

.mn-brand-btn {
    height: 36px;
    min-width: 52px;
    padding: 0 12px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #667085;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: .15s ease;
    user-select: none;
}

.mn-brand-btn:hover { color: #344054; background: #fff; }
.mn-brand-btn.active { color: var(--mn-primary); background: #fff; box-shadow: 0 1px 4px rgba(16,24,40,.09); }

.mn-sort-btn {
    height: 44px;
    min-width: 92px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 11px;
    border: 1px solid var(--mn-border);
    border-radius: 8px;
    background: #fff;
    color: #475467;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.mn-sort-btn:hover { background: #f8fafc; border-color: #cbd5e1; }

.mn-sort-icon {
    width: 25px;
    height: 25px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: var(--mn-blue-soft);
    color: var(--mn-primary);
    font-size: 10px;
}

.mn-sort-value { min-width: 34px; text-align: center; }

.mn-actions { display: flex; align-items: end; gap: 7px; }

.btn-mn-filter {
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 17px;
    border: 0;
    border-radius: 8px;
    background: var(--mn-primary);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.btn-mn-filter:hover { background: var(--mn-primary-hover); }

.btn-mn-reset {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--mn-border);
    border-radius: 8px;
    background: #fff;
    color: #667085;
    text-decoration: none;
}

.btn-mn-reset:hover { background: #f8fafc; color: #344054; }

/* AJAX */
#monitoringResult { position: relative; min-height: 40px; transition: opacity .18s ease; }
#monitoringResult.is-loading { opacity: .45; pointer-events: none; }

.mn-ajax-loader {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(248,250,252,.45);
    backdrop-filter: blur(2px);
    pointer-events: none;
}

.mn-ajax-loader-card {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 11px 15px;
    border: 1px solid var(--mn-border);
    border-radius: 9px;
    background: #fff;
    color: #475467;
    box-shadow: 0 10px 35px rgba(16,24,40,.10);
    font-size: 12px;
    font-weight: 600;
}

.mn-ajax-loader-card .spinner-border { width: 18px; height: 18px; border-width: 2px; }

.mn-filter-error {
    margin-bottom: 14px;
    padding: 12px 14px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fff7f7;
    color: #b91c1c;
    font-size: 12px;
}

/* CARD */
.mn-card {
    overflow: hidden;
    margin-bottom: 14px !important;
    border: 1px solid var(--mn-border);
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(16,24,40,.035);
}

.mn-header {
    min-height: 50px;
    display: flex;
    align-items: center;
    padding: 9px 14px;
    background: #fff !important;
    color: var(--mn-text) !important;
    border-bottom: 1px solid #e8edf2;
}

.mn-header h5,
.mn-header h6 {
    margin: 0;
    font-size: 14px;
    line-height: 1.35;
    font-weight: 750;
}

.mn-header h6 span { color: var(--mn-muted); font-weight: 500; }

.btn-toggle-po {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #dbe2ea;
    border-radius: 6px;
    background: #fff;
    color: #667085;
    font-size: 10px;
}

.btn-toggle-po:hover { background: #f8fafc; color: var(--mn-primary); }

/* TABLE */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

.mn-table {
    min-width: 1100px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 13px;
}

.mn-table th,
.mn-table td { white-space: nowrap; }

.mn-table thead tr:first-child { background: #2c3e50; color: #fff; }
.mn-table thead tr:nth-child(2) { background: #f1f5f9; color: #344054; }

.mn-table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    padding: 9px 7px;
    border-color: #dfe5ec;
    font-size: 11px;
    font-weight: 750;
    line-height: 1.25;
    vertical-align: middle;
}

.mn-table tbody td {
    padding: 9px 7px;
    border-color: #edf0f4;
    color: #344054;
    font-size: 13px;
    line-height: 1.35;
    vertical-align: middle;
}

.mn-table tbody tr { transition: background .15s ease; }
.mn-table tbody tr:hover { background: #fbfdff; }

.item-col { min-width: 145px; }

.item-link {
    display: inline-block;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--mn-primary);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.item-link:hover { color: var(--mn-primary-hover); text-decoration: underline; }

.item-name {
    display: inline-block;
    max-width: 260px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 13px;
}

.product-image {
    width: 70px;
    height: 70px;
    padding: 3px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e4e7ec;
    background: #fff;
}

.qty-col {
    width: 62px;
    min-width: 62px;
    max-width: 62px;
    text-align: center;
}

.qty-badge {
    min-width: 46px;
    display: inline-block;
    padding: 5px 8px;
    border-radius: 6px;
    background: var(--mn-blue-soft);
    color: #344054;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
}

.status-col {
    width: 50px;
    min-width: 50px;
    max-width: 50px;
    padding-left: 4px !important;
    padding-right: 4px !important;
    text-align: center;
}

.pass-box,
.reject-box {
    font-size: 13px;
    font-weight: 700;
}

.pass-box { color: var(--mn-success); }
.reject-box { color: var(--mn-danger); }

/* EMPTY */
.mn-empty {
    padding: 42px 20px;
    border: 1px solid var(--mn-border);
    border-radius: 10px;
    background: #fff;
    text-align: center;
}

.mn-empty h5 { margin: 0 0 5px; font-size: 15px; font-weight: 750; }
.mn-empty .text-muted { font-size: 11px; color: #98a2b3 !important; }

/* MODAL */
.modal .modal-content {
    border: 1px solid var(--mn-border);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 18px 60px rgba(15,23,42,.16);
}

.modal .modal-header {
    min-height: 50px;
    padding: 10px 14px;
    background: #fff !important;
    color: var(--mn-text) !important;
    border-bottom: 1px solid #edf0f4;
}

.modal .modal-title { margin: 0; font-size: 14px; font-weight: 750; }
.modal .modal-body { padding: 14px; background: #fff; }
.modal .modal-footer { padding: 10px 14px; border-top: 1px solid #edf0f4; background: #fff; }

.modal .card {
    border: 1px solid var(--mn-border) !important;
    border-radius: 8px !important;
    box-shadow: 0 1px 5px rgba(16,24,40,.035) !important;
    overflow: hidden;
}

.modal .card-body { padding: 12px !important; }
.modal .table { margin-bottom: 0; font-size: 12px; }
.modal .table th,
.modal .table td { padding: 7px; border-color: #edf0f4; vertical-align: middle; }

.modal .form-control,
.modal .form-select {
    min-height: 36px;
    height: 36px;
    padding: 6px 9px;
    border: 1px solid #dfe3e8;
    border-radius: 6px;
    color: #344054;
    font-size: 12px;
}

.modal .btn { min-height: 34px; height: 34px; padding: 0 12px; border-radius: 6px; font-size: 11px; font-weight: 650; }

/* FOCUS */
.mn-brand-btn:focus-visible,
.mn-sort-btn:focus-visible,
.btn-mn-filter:focus-visible,
.btn-mn-reset:focus-visible,
.mn-search-clear:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
}

@keyframes mnSpin { to { transform: rotate(360deg); } }
.mn-sort-btn.is-loading,
.mn-brand-btn.is-loading { pointer-events: none; opacity: .65; }
.mn-sort-btn.is-loading .mn-sort-icon { animation: mnSpin .55s linear infinite; }

/* RESPONSIVE */
@media (max-width: 1100px) {
    .mn-toolbar { grid-template-columns: minmax(250px,1fr) auto auto; }
    .mn-search { grid-column: 1 / -1; }
}

@media (max-width: 768px) {
    .mn-erp-page { padding: 6px 6px 20px; font-size: 13px; }
    .mn-toolbar { grid-template-columns: 1fr 1fr; gap: 9px; }
    .mn-search,
    .mn-actions { grid-column: 1 / -1; }
    .mn-brand-group,
    .mn-sort-btn { width: 100%; }
    .mn-brand-btn { flex: 1; min-width: 0; }
    .mn-actions { width: 100%; }
    .btn-mn-filter { flex: 1; }
    .item-name { max-width: 180px; }
}

@media (max-width: 576px) {
    .mn-filter { padding: 12px; }
    .mn-toolbar { gap: 8px; }
    .mn-field-label { font-size: 10px; }
    .mn-brand-btn,
    .mn-sort-btn,
    .btn-mn-filter { font-size: 10px; }
    .mn-table thead th { font-size: 10px; }
    .mn-table tbody td { font-size: 11px; }
    .product-image { width: 58px; height: 58px; }
}







/* =========================================================
   ITEM COLUMN - ADJUSTABLE WIDTH + NATURAL WRAPPING
   ========================================================= */

.mn-erp-page .mn-table {
    width: 100% !important;
    table-layout: fixed !important;
}

.mn-erp-page .mn-table .item-col {
    width: 340px !important;
    min-width: 280px !important;
    max-width: 380px !important;

    white-space: normal !important;
    vertical-align: middle;
}

.mn-erp-page .mn-table .item-link,
.mn-erp-page .mn-table .item-name {
    display: block !important;

    width: 100% !important;
    max-width: 100% !important;

    overflow: visible !important;
    text-overflow: unset !important;

    white-space: normal !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;

    line-height: 1.45;
    vertical-align: middle;
}

.mn-erp-page .mn-table .item-link {
    color: var(--mn-primary);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

.mn-erp-page .mn-table .item-link:hover {
    color: var(--mn-primary-hover);
    text-decoration: underline;
}

.mn-erp-page .mn-table .item-name {
    color: #344054;
    font-size: 13px;
}

.mn-erp-page .mn-table .item-col > div,
.mn-erp-page .mn-table .item-col .item-wrapper {
    width: 100% !important;
    max-width: 100% !important;

    overflow: visible !important;
    white-space: normal !important;
}

@media (max-width: 1100px) {
    .mn-erp-page .mn-table .item-col {
        width: 300px !important;
        min-width: 250px !important;
        max-width: 340px !important;
    }
}

@media (max-width: 768px) {
    .mn-erp-page .mn-table .item-col {
        width: 240px !important;
        min-width: 220px !important;
        max-width: 280px !important;
    }
}

</style>



        <div class="container-fluid py-3">

            <div class="mn-filter mb-4">

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | FILTER STATE
                    |--------------------------------------------------------------------------
                    | Semua kontrol membaca state yang sama.
                    | Jadi ketika NW dipilih lalu ASC/DESC diklik,
                    | brand NW tetap ikut terkirim.
                    */

                    $currentSort = strtolower(request('sort', 'desc'));
                    $currentSort = in_array($currentSort, ['asc', 'desc']) ? $currentSort : 'desc';

                    $currentBrand = strtolower(request('brand', 'all'));
                    $currentBrand = in_array($currentBrand, ['all', 'nw', 'nws']) ? $currentBrand : 'all';

                    $nextSort = $currentSort === 'asc' ? 'desc' : 'asc';
                @endphp

                <form method="GET" action="{{ route('produksi.mn') }}" id="monitoringFilterForm">

                    {{-- STATE --}}
                    <input type="hidden" name="brand" id="monitoringBrand" value="{{ $currentBrand }}">

                    <input type="hidden" name="sort" id="monitoringSort" value="{{ $currentSort }}">

                    <div class="mn-toolbar">

                        {{-- SEARCH --}}
                        <div class="mn-search">

                            <div class="mn-field-label">
                                <span>Pencarian</span>
                            </div>

                            <div class="mn-search-box">

                                <i class="fa fa-search"></i>

                                <input type="text" id="searchMonitoring" name="search_po"
                                    value="{{ request('search_po') }}" placeholder="Cari No PO atau Buyer..."
                                    autocomplete="off">

                                @if (request('search_po'))
                                    <button type="button" class="mn-search-clear" id="clearMonitoringSearch"
                                        title="Hapus pencarian">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                            </div>

                            <div class="mn-search-help">
                                <i class="fas fa-circle-info"></i>
                                No PO atau nama buyer
                            </div>

                        </div>


                        {{-- COMPANY --}}
                        <div class="mn-brand-filter">

                            <div class="mn-field-label">
                                <span>Company</span>
                            </div>

                            <div class="mn-brand-group" role="group" aria-label="Filter company">

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'all' ? 'active' : '' }}"
                                    data-brand="all" aria-pressed="{{ $currentBrand === 'all' ? 'true' : 'false' }}">
                                    All
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nw' ? 'active' : '' }}"
                                    data-brand="nw" aria-pressed="{{ $currentBrand === 'nw' ? 'true' : 'false' }}">
                                    NW
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nws' ? 'active' : '' }}"
                                    data-brand="nws" aria-pressed="{{ $currentBrand === 'nws' ? 'true' : 'false' }}">
                                    NWS
                                </button>

                            </div>

                        </div>


                        {{-- SORT --}}
                        <div class="mn-sort">

                            <div class="mn-field-label">
                                <span>Release</span>
                            </div>

                            <button type="button" id="monitoringSortButton" class="mn-sort-btn"
                                title="{{ $currentSort === 'asc' ? 'Klik untuk menampilkan release terbaru' : 'Klik untuk menampilkan release terlama' }}"
                                aria-label="Ubah urutan release">

                                <span class="mn-sort-icon">
                                    <i
                                        class="fas {{ $currentSort === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                </span>

                                <span class="mn-sort-value">
                                    {{ $currentSort === 'asc' ? 'ASC' : 'DESC' }}
                                </span>

                            </button>

                        </div>


                        {{-- ACTION --}}
                        <div class="mn-actions">

                            <button type="submit" class="btn-mn-filter" id="monitoringFilterButton">
                                <i class="fas fa-filter"></i>
                                <span>Filter</span>
                            </button>

                            <a href="{{ route('produksi.mn') }}" class="btn-mn-reset" title="Reset Filter">
                                <i class="fa fa-rotate-left"></i>
                            </a>

                        </div>

                    </div>

                </form>
            </div>

            {{-- DATA --}}
            <div id="monitoringResult">
                @forelse($datas as $poIndex => $po)

                    <div class="mn-card mb-5">

                        {{-- HEADER --}}
                        <div class="mn-header d-flex justify-content-between align-items-center spk-header">

                            <div>

                                <h6>
                                    PO : {{ $po['po_number'] }}
                                    <span class="">
                                        ({{ $po['buyer_name'] }})
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

                            $categories = [
                                'rangka' => 'Rangka',
                                'anyam' => 'Anyam',
                                'unfinish' => 'Unfinish',
                                // 'accessories' => 'Accessories',
                                // 'decor' => 'Decor',
                                // 'ikat' => 'Ikat',
                                'final' => 'Final',
                                'box' => 'Packaging',
                            ];

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
                                                    <img src="{{ $item['item_image'] }}" class="product-image"
                                                        loading="lazy" decoding="async">
                                                @else
                                                    -
                                                @endif

                                            </td>

                                            {{-- QTY --}}
                                            <td class="">

                                                <span class="">
                                                    {{ $item['qty'] }}
                                                </span>

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
                                            @foreach ($categories as $categoryKey => $categoryLabel)
                                                @foreach ($statuses as $statusKey => $status)
                                                    @continue($statusKey == 'out')
                                                    @continue($statusKey == 'reject')

                                                    {{-- Final & Packaging hanya PASS --}}
                                                    @if (in_array($categoryKey, ['final', 'box']) && $statusKey == 'in')
                                                        @continue
                                                    @endif

                                                    @php
                                                        $field = $categoryKey . '_' . $statusKey;
                                                    @endphp

                                                    <td class="text-center">

                                                        <div class="{{ $status['class'] }}">
                                                            {{ $item[$field] ?? 0 }}
                                                        </div>

                                                        @if (isset($item['detail_kategori'][$categoryKey]) && count($item['detail_kategori'][$categoryKey]) > 1)
                                                            <hr class="my-1">

                                                            @foreach ($item['detail_kategori'][$categoryKey] as $jenis => $detail)
                                                                <div style="font-size:9px">

                                                                    {{ str_replace('RANGKA ', 'R. ', $jenis) }}
                                                                    :
                                                                    {{ $detail[$statusKey] ?? 0 }}

                                                                </div>
                                                            @endforeach
                                                        @endif

                                                    </td>
                                                @endforeach
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

                                            @if ($item['item_image'])
                                                <img src="{{ $item['item_image'] }}" class="product-image"
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
                                        @forelse($item['spks'] as $spk)
                                            <div class="card border-0 shadow-sm mb-3">

                                                <div class="card-body">

                                                    {{-- HEADER --}}
                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div class="d-flex gap-2 flex-wrap">

                                                            <span class="badge bg-primary px-3 py-2">
                                                                {{ strtoupper($spk['jenis_asli']) }}
                                                            </span>

                                                            @if (strtolower($spk['kategori']) != strtolower($spk['jenis_asli']))
                                                                <span class="badge bg-secondary px-3 py-2">
                                                                    {{ strtoupper($spk['jenis_asli']) }}
                                                                </span>
                                                            @endif

                                                        </div>

                                                        <div>

                                                            <span class="badge bg-success px-3 py-2">

                                                                {{ strtoupper($spk['status']) }}
                                                                id [{{ $spk['id'] }}]
                                                            </span>

                                                        </div>

                                                    </div>

                                                    {{-- ROW --}}
                                                    <div class="row">

                                                        {{-- LEFT --}}
                                                        <div class="col-md-8">

                                                            <table class="table table-sm mb-0">

                                                                <tr>

                                                                    <td width="140">

                                                                        Supplier

                                                                    </td>

                                                                    <td>

                                                                        :
                                                                        {{ $spk['supplier'] }}

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        No SPK

                                                                    </td>

                                                                    <td>

                                                                        :

                                                                        <a href="{{ url('spk/edit/' . $spk['id']) }}"
                                                                            class="fw-bold text-primary text-decoration-underline">

                                                                            {{ $spk['no_spk'] }}

                                                                        </a>

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        Qty

                                                                    </td>

                                                                    <td>

                                                                        :
                                                                        {{ $spk['qty'] }}

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        Harga

                                                                    </td>

                                                                    <td>
                                                                        :
                                                                        <span class="price-container"
                                                                            data-price="{{ number_format($spk['harga']) }}">
                                                                            <a href="#"
                                                                                class="show-price text-primary text-decoration-underline">
                                                                                Lihat Harga? Tap disini
                                                                            </a>
                                                                        </span>
                                                                    </td>

                                                                </tr>

                                                            </table>

                                                        </div>

                                                        {{-- RIGHT --}}
                                                        <div class="col-md-4">

                                                            @php

                                                                $kategoriSpk = strtolower($spk['kategori']);

                                                                $hideQcResult =
                                                                    str_contains($kategoriSpk, 'cushion') ||
                                                                    str_contains($kategoriSpk, 'box');

                                                            @endphp

                                                            @unless ($hideQcResult)
                                                                <div class="border rounded-4 p-3 h-100 bg-light">
                                                                    <div class="row">

                                                                        <div class="fw-bold mb-3">

                                                                            QC RESULT

                                                                        </div>
                                                                        <div class="fw-bold mb-3 ml-4">
                                                                            @if (!empty($spk['inspect_schedule_id']))
                                                                                <a href="{{ url(
                                                                                    'qc/laporan-qc?' .
                                                                                        http_build_query([
                                                                                            'detail_po_id' => $spk['detail_po_id'],
                                                                                            'kategori' => $spk['kategori'],
                                                                                        ]),
                                                                                ) }}"
                                                                                    target="_blank"
                                                                                    class="fw-bold text-primary text-decoration-none">

                                                                                    Lihat Laporan

                                                                                </a>
                                                                            @else
                                                                                <span class="text-muted">

                                                                                    Tidak/belum ada inspeksin

                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>


                                                                    {{-- PASSED --}}
                                                                    <div class="d-flex justify-content-between mb-2">

                                                                        <span>

                                                                            Passed

                                                                        </span>

                                                                        <span class="fw-bold text-success">
                                                                            <pre>

{{-- {{ print_r($spk, true) }} --}}

</pre>
                                                                            {{ $spk['passed'] }}

                                                                        </span>

                                                                    </div>

                                                                    {{-- REJECT --}}
                                                                    <div class="d-flex justify-content-between">

                                                                        <span>

                                                                            Rejected

                                                                        </span>

                                                                        <span class="fw-bold text-danger">

                                                                            {{ $spk['rejected'] }}

                                                                        </span>

                                                                    </div>

                                                                </div>
                                                            @endunless

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

                    @empty

                        <div class="mn-empty">

                            <h5 class="mb-2">

                                Data Tidak Ditemukan

                            </h5>

                            <div class="text-muted">

                                Coba cari PO atau batch lain

                            </div>

                        </div>
                    @endforelse
                </div>

                <div class="modal fade" id="pricePasswordModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Verifikasi Password
                                </h5>
                            </div>

                            <div class="modal-body">

                                <input type="password" id="pricePassword" class="form-control"
                                    placeholder="Masukkan Password">

                                <div id="priceError" class="text-danger mt-2" style="display:none;">

                                    Password salah

                                </div>

                            </div>

                            <div class="modal-footer">

                                <button class="btn btn-secondary" data-bs-dismiss="modal">

                                    Batal

                                </button>

                                <button class="btn btn-primary" id="btnCheckPricePassword">

                                    Lihat Harga

                                </button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script>
                /*
                            |--------------------------------------------------------------------------
                            | MONITORING AJAX FILTER
                            |--------------------------------------------------------------------------
                            | NW/NWS, ASC/DESC dan search berjalan tanpa reload halaman.
                            */

                (function() {

                    const form = document.getElementById('monitoringFilterForm');
                    const brandInput = document.getElementById('monitoringBrand');
                    const sortInput = document.getElementById('monitoringSort');
                    const sortButton = document.getElementById('monitoringSortButton');
                    const searchInput = document.getElementById('searchMonitoring');
                    const clearSearch = document.getElementById('clearMonitoringSearch');
                    const result = document.getElementById('monitoringResult');

                    if (!form || !brandInput || !sortInput || !result) {
                        return;
                    }

                    let searchTimer = null;
                    let requestController = null;

                    function buildUrl() {

                        const params = new URLSearchParams();

                        const search = searchInput ?
                            searchInput.value.trim() :
                            '';

                        const brand = brandInput.value || 'all';
                        const sort = sortInput.value || 'desc';

                        if (search) {
                            params.set('search_po', search);
                        }

                        if (brand !== 'all') {
                            params.set('brand', brand);
                        }

                        params.set('sort', sort);

                        return form.action + '?' + params.toString();
                    }

                    function setLoading(loading) {

                        result.classList.toggle('is-loading', loading);

                        const filterButton =
                            document.getElementById('monitoringFilterButton');

                        if (filterButton) {
                            filterButton.disabled = loading;
                        }

                        document.querySelectorAll(
                            '.mn-brand-btn, .mn-sort-btn'
                        ).forEach(function(button) {
                            button.classList.toggle('is-loading', loading);
                        });
                    }

                    function showLoader() {

                        if (document.getElementById('mnAjaxLoader')) {
                            return;
                        }

                        const loader = document.createElement('div');

                        loader.id = 'mnAjaxLoader';
                        loader.className = 'mn-ajax-loader';

                        loader.innerHTML = `
                            <div class="mn-ajax-loader-card">
                                <span class="spinner-border" role="status"></span>
                                <span>Memuat data...</span>
                            </div>
                        `;

                        document.body.appendChild(loader);
                    }

                    function hideLoader() {

                        const loader =
                            document.getElementById('mnAjaxLoader');

                        if (loader) {
                            loader.remove();
                        }
                    }

                    async function loadMonitoring(updateBrowserUrl = true) {

                        const url = buildUrl();

                        if (requestController) {
                            requestController.abort();
                        }

                        requestController = new AbortController();

                        setLoading(true);
                        showLoader();

                        try {

                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                },
                                signal: requestController.signal
                            });

                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }

                            const html = await response.text();

                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newResult =
                                doc.getElementById('monitoringResult');

                            if (!newResult) {
                                throw new Error(
                                    'Container monitoringResult tidak ditemukan.'
                                );
                            }

                            result.innerHTML = newResult.innerHTML;

                            if (updateBrowserUrl) {
                                window.history.replaceState({
                                        monitoring: true
                                    },
                                    '',
                                    url
                                );
                            }

                        } catch (error) {

                            if (error.name === 'AbortError') {
                                return;
                            }

                            console.error('Monitoring AJAX error:', error);

                            result.insertAdjacentHTML(
                                'afterbegin',
                                `
                                <div class="mn-filter-error">
                                    <i class="fas fa-circle-exclamation me-1"></i>
                                    Gagal memuat data. Silakan coba lagi.
                                </div>
                                `
                            );

                        } finally {

                            setLoading(false);
                            hideLoader();
                            requestController = null;

                        }
                    }

                    /*
                    | COMPANY
                    */

                    document.querySelectorAll('.mn-brand-btn')
                        .forEach(function(button) {

                            button.addEventListener('click', function() {

                                brandInput.value =
                                    this.dataset.brand || 'all';

                                document.querySelectorAll(
                                    '.mn-brand-btn'
                                ).forEach(function(btn) {

                                    const active =
                                        btn.dataset.brand ===
                                        brandInput.value;

                                    btn.classList.toggle('active', active);

                                    btn.setAttribute(
                                        'aria-pressed',
                                        active ? 'true' : 'false'
                                    );

                                });

                                loadMonitoring();

                            });

                        });

                    /*
                    | ASC / DESC
                    */

                    if (sortButton) {

                        sortButton.addEventListener('click', function() {

                            sortInput.value =
                                sortInput.value === 'asc' ?
                                'desc' :
                                'asc';

                            loadMonitoring();

                        });

                    }

                    /*
                    | SEARCH REALTIME
                    */

                    if (searchInput) {

                        searchInput.addEventListener('input', function() {

                            clearTimeout(searchTimer);

                            searchTimer = setTimeout(function() {
                                loadMonitoring();
                            }, 450);

                        });

                        searchInput.addEventListener('keydown', function(event) {

                            if (event.key === 'Enter') {

                                event.preventDefault();

                                clearTimeout(searchTimer);

                                loadMonitoring();

                            }

                        });

                    }

                    /*
                    | CLEAR SEARCH
                    */

                    if (clearSearch) {

                        clearSearch.addEventListener('click', function() {

                            if (searchInput) {
                                searchInput.value = '';
                            }

                            loadMonitoring();

                            if (searchInput) {
                                searchInput.focus();
                            }

                        });

                    }

                    /*
                    | FILTER BUTTON
                    */

                    form.addEventListener('submit', function(event) {

                        event.preventDefault();

                        clearTimeout(searchTimer);

                        loadMonitoring();

                    });

                    /*
                    | BACK / FORWARD
                    */

                    window.addEventListener('popstate', function() {

                        const params =
                            new URLSearchParams(
                                window.location.search
                            );

                        brandInput.value =
                            params.get('brand') || 'all';

                        sortInput.value =
                            params.get('sort') || 'desc';

                        if (searchInput) {
                            searchInput.value =
                                params.get('search_po') || '';
                        }

                        document.querySelectorAll(
                            '.mn-brand-btn'
                        ).forEach(function(btn) {

                            const active =
                                btn.dataset.brand ===
                                brandInput.value;

                            btn.classList.toggle('active', active);
                            btn.setAttribute(
                                'aria-pressed',
                                active ? 'true' : 'false'
                            );

                        });

                        loadMonitoring(false);

                    });

                })();


                /*
                |--------------------------------------------------------------------------
                | PRICE PASSWORD
                |--------------------------------------------------------------------------
                */

                let currentPriceContainer = null;

                $(document).on('click', '.show-price', function(e) {

                    e.preventDefault();

                    currentPriceContainer = $(this).closest('.price-container');

                    $('#pricePassword').val('');
                    $('#priceError').hide();

                    $('#pricePasswordModal').modal('show');

                });


                $('#btnCheckPricePassword').click(function() {

                    if ($('#pricePassword').val() !== 'Nwidn@2026') {

                        $('#priceError').show();
                        return;

                    }

                    let harga = currentPriceContainer.data('price');

                    currentPriceContainer.html(
                        '<strong>Rp ' + harga + '</strong>'
                    );

                    $('#pricePasswordModal').modal('hide');

                });


                /*
                |--------------------------------------------------------------------------
                | COLLAPSE PO
                |--------------------------------------------------------------------------
                */

                $(document).on('click', '.btn-toggle-po', function() {

                    let card = $(this).closest('.mn-card');

                    card.find('.po-table').slideToggle(200);

                    let icon = $(this).find('i');

                    if (icon.hasClass('fa-chevron-down')) {

                        icon.removeClass('fa-chevron-down')
                            .addClass('fa-chevron-right');

                    } else {

                        icon.removeClass('fa-chevron-right')
                            .addClass('fa-chevron-down');

                    }

                });
            </script>

        @endsection

    