@extends('master.master')

@section('title', 'Pemasukkan 7 Hari Terakhir')

@section('content')

    <style>
        .spk-test-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .spk-search-box {
            position: relative;
            width: 240px;
        }

        .spk-search-box>i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 12px;
            z-index: 2;
            pointer-events: none;
        }

        .spk-search-box input {
            height: 34px;
            padding-left: 32px;
            padding-right: 32px;
            border: 1px solid #dfe4ea;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: none;
        }

        .spk-search-box input:focus {
            border-color: #94a3b8;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, .12);
        }

        .search-clear {
            position: absolute;
            right: 7px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #94a3b8;
            width: 24px;
            height: 24px;
            padding: 0;
            cursor: pointer;
        }

        .search-clear:hover {
            color: #dc2626;
        }

        .spk-test-period {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 12px;
            color: #475569;
            white-space: nowrap;
            gap: 6px;
        }

        /* =========================================================
               MAIN TEST TABLE
               ========================================================= */
        .spk-test-page {
            padding: 18px;
        }

        .modal-header-spk {
            position: relative;
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: center !important;
            padding: 10px 120px !important;
        }

        .modal-spk-title {
            width: 100%;
            text-align: center;
        }

        .modal-spk-title #judulSpk {
            color: #1f2937;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.3;
        }

        .modal-siti-page {
            margin-top: 2px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .modal-spk-actions {
            position: absolute;
            right: 55px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #btnGoToSpk {
            font-size: 11px;
            padding: 5px 80px;
            white-space: nowrap;
        }

        .modal-close-spk {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
        }

        .spk-test-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .spk-test-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .spk-test-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #1f2937;
        }

        .spk-test-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }

        .spk-test-period {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 12px;
            color: #475569;
            white-space: nowrap;
            gap: 6px;
        }

        .table-wrap {
            width: 100%;
            max-height: calc(100vh - 170px);
            overflow: auto;
            position: relative;
        }

        .spk-test-table {
            width: 100%;
            min-width: 1520px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
        }

        .spk-test-table th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #2f4052;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            border-right: 1px solid #526273;
            border-bottom: 1px solid #526273;
            padding: 10px 9px;
            text-align: left;
        }

        .spk-test-table td {
            background: #fff;
            color: #334155;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            padding: 9px;
            vertical-align: top;
        }

        .spk-test-table tbody tr.pilih-item {
            cursor: pointer;
        }

        .spk-test-table tbody tr.pilih-item:hover td {
            background: #f4f8fc;
        }

        .text-wrap-100 {
            width: 100px;
            min-width: 100px;
            max-width: 100px;
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.4;
        }

        .nowrap {
            white-space: nowrap;
        }

        .num {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .all-in {
            color: #0f766e;
            font-weight: 700;
            white-space: nowrap;
        }

        .saldo-payment {
            color: #16a34a;
            font-weight: 700;
            white-space: nowrap;
        }

        .empty-row td {
            text-align: center;
            padding: 40px 15px;
            color: #94a3b8;
        }

        /* =========================================================
               SAME MODAL STYLE AS MUTASI ADMIN
               ========================================================= */
        .modal-custom {
            max-width: 95%;
        }

        .modal-custom .modal-content {
            min-height: 85vh;
        }

        .modal-custom .modal-header {
            padding: 14px 16px;
        }

        .modal-custom .modal-title {
            font-size: 17px;
            font-weight: 700;
        }

        .modal-custom .modal-body {
            padding: 14px 16px;
        }

        .item-detail-grid {
            display: grid;
            grid-template-columns: minmax(300px, .9fr) minmax(420px, 1.35fr);
            gap: 14px;
            margin-bottom: 16px;
        }

        .item-detail-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #e7ebf0;
            border-radius: 14px;
            background: #fff;
        }

        .item-detail-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #edf0f3;
            background: #fafbfc;
        }

        .item-detail-eyebrow {
            margin-bottom: 3px;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .65px;
            text-transform: uppercase;
        }

        .item-detail-title {
            max-width: 520px;
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
        }

        .item-code-badge,
        .component-count {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 0 9px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
        }

        .item-detail-table {
            padding: 4px 16px 8px;
        }

        .item-detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 38px;
            border-bottom: 1px solid #f1f3f5;
            color: #64748b;
            font-size: 11px;
        }

        .item-detail-row:last-child {
            border-bottom: 0;
        }

        .item-detail-row strong {
            max-width: 70%;
            color: #334155;
            font-weight: 600;
            text-align: right;
        }

        .component-detail-card {
            display: flex;
            flex-direction: column;
        }

        .component-table-wrap {
            overflow-x: auto;
        }

        .component-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            font-size: 11px;
        }

        .component-table th {
            padding: 9px 12px;
            border-bottom: 1px solid #e9edf1;
            background: #fff;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .35px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .component-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f2f5;
            color: #475569;
            vertical-align: middle;
        }

        .component-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .component-table tbody tr:hover {
            background: #fafbfc;
        }

        .component-name {
            min-width: 150px;
            color: #334155;
            font-weight: 600;
            line-height: 1.35;
        }

        .component-note {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
            padding: 9px 12px;
            border-top: 1px solid #edf0f3;
            background: #fafbfc;
            color: #94a3b8;
            font-size: 9px;
        }

        .component-note i {
            font-size: 9px;
        }

        #timelineTable {
            overflow-x: auto;
        }

        #timelineTable>table {
            min-width: 900px;
        }

        #itemSelect {
            max-width: 100%;
        }

        #timelineTable .form-control,
        #timelineTable .form-select {
            font-size: 12px;
        }

        .colored-toast {
            background: #198754 !important;
            color: #fff !important;
        }

        @media (max-width: 992px) {
            .item-detail-grid {
                grid-template-columns: 1fr;
            }

            .item-detail-title {
                max-width: 75vw;
            }
        }

        @media (max-width: 768px) {
            .modal-custom {
                max-width: 100%;
                margin: .5rem;
            }

            .modal-custom .modal-content {
                min-height: auto;
            }

            .modal-custom .modal-body {
                padding: 12px;
            }
        }

        @media (max-width: 576px) {
            .item-detail-grid {
                gap: 10px;
            }

            .item-detail-card-header {
                padding: 12px;
            }

            .item-detail-table {
                padding: 3px 12px 7px;
            }

            .item-detail-row {
                min-height: 35px;
                font-size: 10px;
            }

            .item-detail-row strong {
                max-width: 65%;
            }

            .component-table th,
            .component-table td {
                padding: 8px 9px;
            }

            .component-table {
                font-size: 10px;
            }

            .component-name {
                min-width: 135px;
            }
        }
    </style>

    <div class="spk-test-page">

        <div class="spk-test-card">

            <div class="spk-test-header">

            @section('btn')
                <div>
                    <h3 class="spk-test-title">
                        Pemasukkan 7 Hari Terakhir
                    </h3>

                    <div class="spk-test-subtitle">
                        Production Timeline + Inspection Schedule
                        &mdash; klik baris untuk membuka detail item
                    </div>
                </div>

            @endsection

            <div class="spk-test-tools">

                <div class="spk-search-box">
                    <i class="fas fa-search"></i>

                    <input type="text" id="searchAll" class="form-control" placeholder="Search..." autocomplete="off">

                    <button type="button" id="clearSearch" class="search-clear" title="Clear" style="display:none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="spk-test-period">
                    <span>Periode</span>

                    <strong>
                        {{ $startDate->format('d M Y') }}
                        -
                        {{ $endDate->format('d M Y') }}
                    </strong>
                </div>

            </div>

        </div>

        <div class="table-wrap">

            <table class="spk-test-table">

                <thead>
                    <tr>

                        <th style="width:45px;text-align:center;">
                            NO
                        </th>

                        <th style="width:90px;">
                            TANGGAL
                        </th>

                        <th style="width:90px;">
                            ARTICLE NR
                        </th>

                        <th class="text-wrap-100">
                            DESCRIPTION
                        </th>

                        <th style="width:120px;">
                            NO. PFI
                        </th>

                        <th style="width:180px;">
                            NO. SPK
                        </th>

                        <th style="width:150px;">
                            SUPPLIER
                        </th>

                        <th style="width:130px;">
                            KATEGORI
                        </th>

                        <th style="width:70px;text-align:right;">
                            QTY
                        </th>

                        <th style="width:70px;text-align:right;">
                            IN
                        </th>

                        <th style="width:90px;text-align:right;">
                            ALL IN
                        </th>

                        <th style="width:70px;text-align:right;">
                            PASS
                        </th>
{{-- 
                        <th style="width:75px;text-align:right;">
                            REJECT
                        </th> --}}

                        <th style="width:125px;text-align:right;">
                            SALDO PAYMENT
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @forelse($rows as $row)
                        <tr class="pilih-item" data-spk-id="{{ $row['spk_id'] }}"
                            data-detail-po-id="{{ $row['detail_po_id'] }}" title="Klik untuk melihat detail item">

                            <td style="text-align:center;">
                                {{ $loop->iteration }}
                            </td>

                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}
                            </td>

                            <td class="nowrap" style="font-weight:700;">
                                {{ $row['article_nr'] }}
                            </td>

                            <td class="text-wrap-100">
                                {{ $row['description'] }}
                            </td>

                            <td class="nowrap">
                                {{ $row['no_pfi'] }}
                            </td>

                            <td class="text-wrap-100">
                                {{ $row['no_spk'] }}
                            </td>

                            <td class="text-wrap-100">
                                {{ $row['supplier'] }}
                            </td>

                            <td class="text-wrap-100">
                                {{ $row['kategori'] }}
                            </td>

                            <td class="num">
                                {{ number_format($row['qty'], 0, ',', '.') }}
                            </td>

                            <td class="num">
                                {{ number_format($row['in'], 0, ',', '.') }}
                            </td>

                            <td class="num all-in">
                                {{ number_format($row['all_in'], 0, ',', '.') }}
                            </td>

                            <td class="num" style="color:#16a34a;font-weight:700;">
                                {{ number_format($row['pass'], 0, ',', '.') }}
                            </td>

                            {{-- <td class="num" style="color:#dc2626;font-weight:700;">
                                {{ number_format($row['rejected'], 0, ',', '.') }}
                            </td> --}}

                            <td class="num saldo-payment">
                                Rp {{ number_format($row['saldo_payment'], 0, ',', '.') }}
                            </td>

                        </tr>

                    @empty

                        <tr class="empty-row">
                            <td colspan="14">
                                Tidak ada barang masuk
                                dalam 7 hari terakhir.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL REUSE MUTASI ADMIN
     ========================================================= --}}
<div class="modal fade" id="modalSpk" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-custom">

        <div class="modal-content">

            <div class="modal-header modal-header-spk">

                <div class="modal-spk-title">

                    <div id="judulSpk">
                        Detail SPK
                    </div>

                    <div class="modal-siti-page">
                        Siti's Page
                    </div>

                </div>

                <div class="modal-spk-actions">

                    <a href="#" id="btnGoToSpk" class="btn btn-sm btn-outline-primary" target="_blank"
                        title="Buka SPK">
                        <i class="fas fa-external-link-alt"></i>
                        Go to SPK
                    </a>

                    <button type="button" class="btn-close modal-close-spk" data-bs-dismiss="modal" aria-label="Close">
                    </button>

                </div>

            </div>
            <div class="modal-body">

                <div class="mb-3">

                    <label class="form-label">
                        Item
                    </label>

                    <select id="itemSelect" class="form-select">
                        <option value="">
                            Pilih Item
                        </option>
                    </select>

                </div>

                {{-- Detail item + komponen --}}
                <div id="itemInfo"></div>

                <hr>

                {{-- Production Timeline --}}
                <div id="timelineTable"></div>

            </div>

        </div>

    </div>

</div>


<script>
    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let items = [];
    let currentSpkId = null;
    let currentSupId = null;
    let supplierName = '';
    let kategoriSpk = '';

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        if (
            value === null ||
            value === undefined
        ) {
            return '';
        }

        return $('<div>')
            .text(value)
            .html();
    }


    function formatNumber(value) {

        const number = Number(value);

        if (Number.isNaN(number)) {
            return value ?? '-';
        }

        return number.toLocaleString('id-ID');
    }


    /*
    |--------------------------------------------------------------------------
    | QTY IN ITEM
    |--------------------------------------------------------------------------
    */

    function getQtyInFromTimeline(timeline) {

        let qtyIn = 0;

        $.each(
            timeline || [],
            function(i, row) {

                const type =
                    String(
                        row.type || ''
                    ).toLowerCase();

                if (
                    type === 'in' ||
                    type === 'service_masuk'
                ) {

                    qtyIn += Number(
                        row.qty || 0
                    );
                }
            }
        );

        return qtyIn;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE COMPONENT
    |--------------------------------------------------------------------------
    */

    function normalizeComponentText(value) {

        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }


    /*
    |--------------------------------------------------------------------------
    | QTY IN COMPONENT
    |--------------------------------------------------------------------------
    */

    function getComponentQtyInFromTimeline(
        timeline,
        componentName
    ) {

        const target =
            normalizeComponentText(
                componentName
            );

        if (!target) {
            return 0;
        }

        let qtyIn = 0;

        $.each(
            timeline || [],
            function(i, row) {

                const type =
                    String(
                        row.type || ''
                    ).toLowerCase();

                if (
                    type !== 'in' &&
                    type !== 'service_masuk'
                ) {
                    return;
                }

                const remark =
                    normalizeComponentText(
                        row.remark
                    );

                if (!remark) {
                    return;
                }

                const matched =
                    remark === target ||
                    remark.includes(target) ||
                    target.includes(remark);

                if (matched) {

                    qtyIn += Number(
                        row.qty || 0
                    );
                }
            }
        );

        return qtyIn;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT ROWS
    |--------------------------------------------------------------------------
    */

    function getComponentRows(
        item,
        timeline = []
    ) {

        const customColumns =
            Array.isArray(
                item.custom_columns
            ) ?
            item.custom_columns : [];

        const rows = [];

        $.each(
            customColumns,
            function(index, component) {

                if (
                    !component ||
                    typeof component !== 'object'
                ) {
                    return;
                }

                let componentName = '';

                $.each(
                    component,
                    function(key, value) {

                        if (
                            !componentName &&
                            typeof value === 'string' &&
                            value.trim() &&
                            ![
                                'harga',
                                'material',
                                'pcs',
                                'set',
                                'total',
                                'p',
                                'l',
                                't'
                            ].includes(
                                String(key)
                                .toLowerCase()
                            )
                        ) {

                            componentName = value;
                        }
                    }
                );

                if (!componentName) {

                    componentName =
                        component.material ||
                        component.nama ||
                        `Komponen ${index + 1}`;
                }

                const qtySpk =
                    component.pcs !== undefined &&
                    component.pcs !== null &&
                    component.pcs !== '' &&
                    !Number.isNaN(
                        Number(component.pcs)
                    ) ?
                    Number(component.pcs) :
                    Number(item.qty || 0);

                const qtyIn =
                    getComponentQtyInFromTimeline(
                        timeline,
                        componentName
                    );

                rows.push({

                    name: componentName,

                    qtySpk: qtySpk,

                    qtyIn: qtyIn,

                    specification: component
                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        if (!rows.length) {

            rows.push({

                name: item.nama || '-',

                qtySpk: Number(item.qty || 0),

                qtyIn: getQtyInFromTimeline(
                    timeline
                ),

                specification: null
            });
        }

        return rows;
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER DETAIL ITEM + COMPONENT
    |--------------------------------------------------------------------------
    */

    function renderItemInfo(
        item,
        detailPoId,
        timeline = []
    ) {

        const components =
            getComponentRows(
                item,
                timeline
            );

        let componentRows = '';

        $.each(
            components,
            function(index, component) {

                const qtySpk =
                    Number(
                        component.qtySpk || 0
                    );

                const qtyIn =
                    Number(
                        component.qtyIn || 0
                    );

                const balance =
                    qtySpk - qtyIn;

                const balanceClass =
                    balance > 0 ?
                    'text-warning' :
                    balance < 0 ?
                    'text-danger' :
                    'text-success';

                componentRows += `

                <tr>

                    <td>
                        <div class="component-name">
                            ${escapeHtml(component.name)}
                        </div>
                    </td>

                    <td class="text-end fw-semibold">
                        ${formatNumber(qtySpk)}
                    </td>

                    <td class="text-end">
                        ${formatNumber(qtyIn)}
                    </td>

                    <td class="
                        text-end
                        fw-semibold
                        ${balanceClass}
                    ">
                        ${formatNumber(balance)}
                    </td>

                </tr>
            `;
            }
        );


        $('#itemInfo').html(`

        <div class="item-detail-grid">

            {{-- LEFT --}}
            <div class="item-detail-card">

                <div class="item-detail-card-header">

                    <div>

                        <div class="item-detail-eyebrow">
                            Detail Item
                        </div>

                        <div class="item-detail-title">
                            ${escapeHtml(item.nama || '-')}
                        </div>

                    </div>

                    <span class="item-code-badge">
                        ${escapeHtml(item.kode || '-')}
                    </span>

                </div>


                <div class="item-detail-table">

                    <div class="item-detail-row">

                        <span>
                            Detail PO ID
                        </span>

                        <strong>
                            ${escapeHtml(detailPoId)}
                        </strong>

                    </div>


                    <div class="item-detail-row">

                        <span>
                            Kode
                        </span>

                        <strong>
                            ${escapeHtml(item.kode || '-')}
                        </strong>

                    </div>


                    <div class="item-detail-row">

                        <span>
                            Qty SPK
                        </span>

                        <strong>

                            ${formatNumber(item.qty || 0)}

                            ${escapeHtml(
                                item.satuan || ''
                            )}

                        </strong>

                    </div>


                    <div class="item-detail-row">

                        <span>
                            Supplier
                        </span>

                        <strong>
                            ${escapeHtml(
                                supplierName || '-'
                            )}
                        </strong>

                    </div>


                    <div class="item-detail-row">

                        <span>
                            Kategori
                        </span>

                        <strong>
                            ${escapeHtml(
                                kategoriSpk || '-'
                            )}
                        </strong>

                    </div>

                </div>


                <input
                    type="hidden"
                    class="sup_id"
                    value="${escapeHtml(currentSupId)}"
                >

                <input
                    type="hidden"
                    class="kategori"
                    value="${escapeHtml(kategoriSpk)}"
                >

            </div>


            {{-- RIGHT --}}
            <div class="item-detail-card component-detail-card">

                <div class="
                    item-detail-card-header
                    component-header
                ">

                    <div>

                        <div class="item-detail-eyebrow">
                            Rincian Komponen
                        </div>

                        <div class="item-detail-title">
                            Komponen Item
                        </div>

                    </div>

                    <span class="component-count">
                        ${components.length} komponen
                    </span>

                </div>


                <div class="component-table-wrap">

                    <table class="component-table">

                        <thead>

                            <tr>

                                <th>
                                    Items
                                </th>

                                <th class="text-end">
                                    Qty SPK
                                </th>

                                <th class="text-end">
                                    Qty In
                                </th>

                                <th class="text-end">
                                    Balance
                                </th>

                            </tr>

                        </thead>

                        <tbody>
                            ${componentRows}
                        </tbody>

                    </table>

                </div>


                <div class="component-note">

                    <i class="fas fa-circle-info"></i>

                    Qty In setiap komponen mengikuti
                    <strong>Remark</strong>
                    pada timeline.

                </div>

            </div>

        </div>

    `);
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD TIMELINE
    |--------------------------------------------------------------------------
    */

    function loadTimeline(
        spkId,
        detailPoId,
        item
    ) {

        $('#timelineTable').html(`

        <div class="text-center text-muted py-4">

            <div
                class="spinner-border spinner-border-sm me-2"
            ></div>

            Memuat Production Timeline...

        </div>

    `);


        $.ajax({

            url: "{{ url('/mutasi/timeline/detail') }}",

            type: 'GET',

            data: {

                spk_id: spkId,

                detail_po_id: detailPoId

            },

            success: function(res) {

                if (
                    !res ||
                    !res.success
                ) {

                    $('#timelineTable').html(`

                    <div class="alert alert-warning">
                        Timeline tidak ditemukan.
                    </div>

                `);

                    return;
                }


                let html = `

                <table
                    class="table table-bordered table-sm"
                >

                    <thead class="table-light">

                        <tr>

                            <th width="50">
                                No
                            </th>

                            <th width="160">
                                Tanggal
                            </th>

                            <th width="100">
                                Jam
                            </th>

                            <th width="150">
                                Type
                            </th>

                            <th width="100">
                                Qty
                            </th>

                            <th width="100">
                                Remark
                            </th>

                            <th width="70">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody id="tbodyTimeline">

            `;


                $.each(
                    res.timeline || [],
                    function(i, row) {

                        let datetime =
                            row.date ?
                            row.date.split(' ') : ['', ''];

                        let tanggal =
                            datetime[0] || '';

                        let jam =
                            datetime[1] ?? '';


                        html += `

                        <tr data-id="${row.id}">

                            <td>
                                ${i + 1}
                            </td>

                            <td>

                                <input
                                    type="date"
                                    class="
                                        form-control
                                        form-control-sm
                                        tanggal
                                    "
                                    value="${escapeHtml(tanggal)}"
                                >

                            </td>

                            <td>

                                <input
                                    type="time"
                                    class="
                                        form-control
                                        form-control-sm
                                        jam
                                    "
                                    value="${escapeHtml(jam)}"
                                >

                            </td>

                            <td>

                                <select
                                    class="
                                        form-select
                                        form-select-sm
                                        type
                                    "
                                >

                                    <option
                                        value="in"
                                        ${row.type == 'in'
                                            ? 'selected'
                                            : ''}
                                    >
                                        Masuk
                                    </option>

                                    <option
                                        value="kirim_rangka"
                                        ${row.type == 'kirim_rangka'
                                            ? 'selected'
                                            : ''}
                                    >
                                        Kirim Rangka
                                    </option>

                                    <option
                                        value="service_masuk"
                                        ${row.type == 'service_masuk'
                                            ? 'selected'
                                            : ''}
                                    >
                                        Service
                                    </option>

                                    <option
                                        value="service_keluar"
                                        ${row.type == 'service_keluar'
                                            ? 'selected'
                                            : ''}
                                    >
                                        Service Keluar
                                    </option>

                                </select>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="
                                        form-control
                                        form-control-sm
                                        qty
                                    "
                                    value="${escapeHtml(row.qty ?? '')}"
                                >

                            </td>

                            <td>

                                <input
                                    type="text"
                                    class="
                                        form-control
                                        form-control-sm
                                        remark
                                    "
                                    value="${escapeHtml(row.remark ?? '')}"
                                >

                            </td>

                            <td class="text-center">

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-danger
                                        btn-sm
                                        hapus-row
                                    "
                                >
                                    <i class="fas fa-trash"></i>
                                </button>

                            </td>

                        </tr>

                    `;
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | UPDATE COMPONENT SETELAH TIMELINE
                |--------------------------------------------------------------------------
                */

                renderItemInfo(
                    item,
                    detailPoId,
                    res.timeline || []
                );


                html += `

                    </tbody>

                </table>


                <div class="
                    d-flex
                    justify-content-between
                    align-items-center
                    mt-2
                ">

                    <button
                        type="button"
                        class="
                            btn
                            btn-success
                            btn-sm
                        "
                        id="btnTambah"
                    >

                        <i class="fas fa-plus"></i>

                        Tambah Baris

                    </button>


                    <button
                        type="button"
                        class="
                            btn
                            btn-primary
                            btn-sm
                        "
                        id="btnSave"
                    >

                        <i class="fas fa-save"></i>

                        Simpan

                    </button>

                </div>

            `;


                $('#timelineTable').html(
                    html
                );

            },

            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                $('#timelineTable').html(`

                <div class="alert alert-danger">
                    Gagal mengambil Production Timeline.
                </div>

            `);

            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SPK
    |--------------------------------------------------------------------------
    |
    | Ini yang membedakan halaman TEST:
    |
    | row tabel -> SPK + Detail PO
    | -> API /mutasi/{spk_id}
    | -> item otomatis dipilih
    |--------------------------------------------------------------------------
    */

    function loadSpkFromTestRow(
        spkId,
        detailPoId
    ) {

        resetModal();

        currentSpkId =
            spkId;
        $('#btnGoToSpk').attr(
            'href',
            "{{ url('/spk/edit') }}/" + encodeURIComponent(spkId)
        );

        $.ajax({

            url: "{{ url('/mutasi') }}/" +
                encodeURIComponent(spkId),

            type: 'GET',

            success: function(res) {

                if (
                    !res ||
                    !res.success
                ) {

                    alert(
                        'Data SPK tidak ditemukan.'
                    );

                    return;
                }


                supplierName =
                    res.supplier || '';

                currentSupId =
                    res.sup_id || null;

                kategoriSpk =
                    res.kategori || '';

                $('#judulSpk').text(
                    res.no_spk || 'Detail SPK'
                );


                items =
                    Array.isArray(res.items) ?
                    res.items : [];


                let html =
                    '<option value="">Pilih Item</option>';


                $.each(
                    items,
                    function(i, item) {

                        html += `

                        <option
                            value="${escapeHtml(
                                item.detail_po_id
                            )}"
                            data-index="${i}"
                        >

                            ${escapeHtml(
                                item.kode || '-'
                            )}
                            -
                            ${escapeHtml(
                                item.nama || '-'
                            )}

                            (${formatNumber(
                                item.qty || 0
                            )}
                            ${escapeHtml(
                                item.satuan || ''
                            )})

                        </option>

                    `;
                    }
                );


                $('#itemSelect').html(
                    html
                );


                /*
                |--------------------------------------------------------------------------
                | AUTO SELECT ITEM YANG DIKLIK
                |--------------------------------------------------------------------------
                */

                $('#itemSelect')
                    .val(String(detailPoId));


                /*
                |--------------------------------------------------------------------------
                | JIKA DETAIL PO TIDAK DITEMUKAN
                |--------------------------------------------------------------------------
                */

                if (
                    $('#itemSelect').val() === null
                ) {

                    $('#itemSelect')
                        .val('');

                    $('#itemInfo').html(`

                    <div class="alert alert-warning">
                        Item dengan Detail PO ID
                        <strong>
                            ${escapeHtml(detailPoId)}
                        </strong>
                        tidak ditemukan pada SPK.
                    </div>

                `);

                    $('#timelineTable').empty();

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | BUKA MODAL YANG SAMA DENGAN MUTASI ADMIN
                |--------------------------------------------------------------------------
                */

                $('#modalSpk').modal(
                    'show'
                );


                /*
                |--------------------------------------------------------------------------
                | LOAD ITEM + TIMELINE
                |--------------------------------------------------------------------------
                */

                const selectedIndex =
                    $('#itemSelect')
                    .find(':selected')
                    .data('index');


                const selectedItem =
                    items[selectedIndex];


                if (!selectedItem) {
                    return;
                }


                renderItemInfo(
                    selectedItem,
                    detailPoId,
                    []
                );


                loadTimeline(
                    currentSpkId,
                    detailPoId,
                    selectedItem
                );

            },

            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                alert(
                    'Gagal mengambil detail SPK.'
                );

            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | CLICK ROW TEST
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.pilih-item',
        function(e) {

            /*
            | Kalau suatu saat row mempunyai button/link,
            | jangan buka modal dari click element tersebut.
            */

            if (
                $(e.target).closest(
                    'button, a, input, select'
                ).length
            ) {
                return;
            }


            const spkId =
                $(this).data('spk-id');

            const detailPoId =
                $(this).data('detail-po-id');


            if (
                !spkId ||
                !detailPoId
            ) {

                console.warn(
                    'spk_id / detail_po_id tidak tersedia', {
                        spkId,
                        detailPoId
                    }
                );

                return;
            }


            loadSpkFromTestRow(
                spkId,
                detailPoId
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGE ITEM
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'change',
        '#itemSelect',
        function() {

            const detailPoId =
                $(this).val();

            if (
                !detailPoId ||
                !currentSpkId
            ) {
                return;
            }


            const index =
                $(this)
                .find(':selected')
                .data('index');


            const item =
                items[index];


            if (!item) {
                return;
            }


            renderItemInfo(
                item,
                detailPoId,
                []
            );


            loadTimeline(
                currentSpkId,
                detailPoId,
                item
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TAMBAH BARIS
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '#btnTambah',
        function() {

            const no =
                $('#tbodyTimeline tr')
                .length + 1;


            $('#tbodyTimeline').append(`

            <tr data-id="">

                <td>
                    ${no}
                </td>

                <td>

                    <input
                        type="date"
                        class="
                            form-control
                            form-control-sm
                            tanggal
                        "
                    >

                </td>

                <td>

                    <input
                        type="time"
                        class="
                            form-control
                            form-control-sm
                            jam
                        "
                    >

                </td>

                <td>

                    <select
                        class="
                            form-select
                            form-select-sm
                            type
                        "
                    >

                        <option value="in">
                            Masuk
                        </option>

                        <option value="kirim_rangka">
                            Kirim Rangka
                        </option>

                        <option value="service_masuk">
                            Service Masuk
                        </option>

                        <option value="service_keluar">
                            Service Keluar
                        </option>

                    </select>

                </td>

                <td>

                    <input
                        type="number"
                        class="
                            form-control
                            form-control-sm
                            qty
                        "
                    >

                </td>

                <td>

                    <input
                        type="text"
                        class="
                            form-control
                            form-control-sm
                            remark
                        "
                    >

                </td>

                <td class="text-center">

                    <button
                        type="button"
                        class="
                            btn
                            btn-danger
                            btn-sm
                            hapus-row
                        "
                    >

                        <i class="fas fa-trash"></i>

                    </button>

                </td>

            </tr>

        `);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | HAPUS BARIS
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.hapus-row',
        function() {

            $(this)
                .closest('tr')
                .remove();


            /*
            | Renumber.
            */

            $('#tbodyTimeline tr')
                .each(function(index) {

                    $(this)
                        .children('td')
                        .first()
                        .text(index + 1);

                });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SAVE TIMELINE
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '#btnSave',
        function() {

            if (!currentSpkId) {
                return;
            }


            const rows = [];


            $('#tbodyTimeline tr')
                .each(function() {

                    rows.push({

                        id: $(this).data('id') || '',

                        spk_id: currentSpkId,

                        detail_po_id: $('#itemSelect').val(),

                        sup_id: currentSupId,

                        qty: $(this)
                            .find('.qty')
                            .val(),

                        type: $(this)
                            .find('.type')
                            .val(),

                        remark: $(this)
                            .find('.remark')
                            .val(),

                        date: $(this)
                            .find('.tanggal')
                            .val(),

                        time: $(this)
                            .find('.jam')
                            .val()

                    });

                });


            const button =
                $(this);


            button
                .prop(
                    'disabled',
                    true
                )
                .html(`
                <i class="fas fa-spinner fa-spin"></i>
                Menyimpan...
            `);


            $.ajax({

                url: "{{ route('mutasi.timeline.save') }}",

                type: 'POST',

                data: {

                    _token: "{{ csrf_token() }}",

                    rows: rows

                },

                success: function(res) {

                    const Toast =
                        Swal.mixin({

                            toast: true,

                            position: 'top-end',

                            iconColor: 'white',

                            customClass: {
                                popup: 'colored-toast'
                            },

                            showConfirmButton: false,

                            timer: 2500,

                            timerProgressBar: true

                        });


                    Toast.fire({

                        icon: 'success',

                        title: res.message ||
                            'Timeline berhasil disimpan.'

                    });


                    $('#modalSpk')
                        .modal('hide');


                    /*
                    | Reload supaya ALL IN / IN / PASS
                    | di tabel utama langsung mengikuti data terbaru.
                    */

                    setTimeout(
                        function() {
                            location.reload();
                        },
                        700
                    );

                },

                error: function(xhr) {

                    console.log(
                        xhr.responseText
                    );

                    let message =
                        'Gagal menyimpan timeline.';


                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;

                    }


                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text: message

                    });

                },

                complete: function() {

                    button
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`
                        <i class="fas fa-save"></i>
                        Simpan
                    `);

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | RESET MODAL
    |--------------------------------------------------------------------------
    */

    function resetModal() {

        items = [];

        currentSpkId = null;

        currentSupId = null;

        supplierName = '';

        kategoriSpk = '';


        $('#judulSpk')
            .text('Detail SPK');


        $('#itemSelect')
            .html(`
            <option value="">
                Pilih Item
            </option>
        `);


        $('#itemInfo')
            .empty();


        $('#timelineTable')
            .empty();

    }


    /*
    |--------------------------------------------------------------------------
    | RESET SAAT MODAL DITUTUP
    |--------------------------------------------------------------------------
    */

    $('#modalSpk').on(
        'hidden.bs.modal',
        function() {

            resetModal();

        }
    );
    /*
|--------------------------------------------------------------------------
| SEARCH SELURUH DATA TABLE BERDASARKAN FULL TEXT TR
|--------------------------------------------------------------------------
*/

    function filterTableByFullRow() {

        const keyword =
            String(
                $('#searchAll').val() || ''
            )
            .toLowerCase()
            .trim();

        let visibleCount = 0;

        $('.spk-test-table tbody tr.pilih-item')
            .each(function() {

                const row =
                    $(this);

                /*
                 * Ambil seluruh isi TR.
                 *
                 * Jadi pencarian mencakup:
                 * tanggal
                 * article
                 * description
                 * no pfi
                 * no spk
                 * supplier
                 * kategori
                 * qty
                 * in
                 * all in
                 * pass
                 * reject
                 * saldo payment
                 */
                const fullText =
                    row.text()
                    .toLowerCase()
                    .replace(/\s+/g, ' ')
                    .trim();

                const matched =
                    keyword === '' ||
                    fullText.includes(keyword);

                row.toggle(matched);

                if (matched) {
                    visibleCount++;
                }

            });


        /*
         |--------------------------------------------------------------------------
         | EMPTY SEARCH RESULT
         |--------------------------------------------------------------------------
         */

        $('#searchEmptyRow')
            .remove();

        if (
            keyword !== '' &&
            visibleCount === 0
        ) {

            $('.spk-test-table tbody')
                .append(`

                <tr
                    id="searchEmptyRow"
                    class="empty-row"
                >
                    <td colspan="14">
                        Tidak ada data yang cocok dengan
                        "<strong>${escapeHtml(keyword)}</strong>"
                    </td>
                </tr>

            `);
        }


        /*
         |--------------------------------------------------------------------------
         | CLEAR BUTTON
         |--------------------------------------------------------------------------
         */

        $('#clearSearch')
            .toggle(keyword !== '');

    }


    /*
    |--------------------------------------------------------------------------
    | INPUT SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'input',
        '#searchAll',
        function() {

            filterTableByFullRow();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '#clearSearch',
        function() {

            $('#searchAll')
                .val('')
                .trigger('input')
                .focus();

        }
    );
</script>

@endsection
