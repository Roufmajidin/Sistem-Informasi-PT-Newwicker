{{-- @extends('master.master')

@if (request()->is('ar_buyer'))

<style>

    #aside {
        display: none !important;
    }

    #content {
        margin-left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    #arSidebarToggle {
        position: fixed !important;

        left: 0 !important;
        top: 70px !important;

        width: 36px !important;
        height: 38px !important;

        margin: 0 !important;
        padding: 0 !important;

        border: 0 !important;
        border-radius: 0 6px 6px 0 !important;

        background: #26364a !important;
        color: #ffffff !important;

        display: flex !important;
        align-items: center !important;
        justify-content: center !important;

        cursor: pointer !important;

        z-index: 2147483647 !important;

        pointer-events: auto !important;

        box-shadow: 2px 2px 8px rgba(0,0,0,.25);

        transition: left .2s ease, width .15s ease;
    }

    #arSidebarToggle:hover {
        width: 42px !important;
        background: #304783 !important;
    }

    #arSidebarToggle i {
        pointer-events: none !important;
        font-size: 17px !important;
        color: #fff !important;
    }

    body.ar-sidebar-open #aside {
        display: block !important;

        width: 230px !important;
        min-width: 230px !important;
        max-width: 230px !important;

        z-index: 2147483000 !important;
    }

    body.ar-sidebar-open #aside .left.navside {
        width: 230px !important;
        min-width: 230px !important;
        max-width: 230px !important;
    }

    body.ar-sidebar-open #content {
        margin-left: 230px !important;
        width: calc(100% - 230px) !important;
    }

    body.ar-sidebar-open #arSidebarToggle {
        left: 230px !important;
    }
</style>

<button
    type="button"
    id="arSidebarToggle"
    aria-label="Toggle Sidebar"
    title="Buka menu">

    <i class="fa fa-bars"></i>

</button>

<script>
(function () {

    function initArSidebar() {

        const button = document.getElementById('arSidebarToggle');

        if (!button) {
            console.log('[AR SIDEBAR] tombol tidak ditemukan');
            return;
        }

        console.log('[AR SIDEBAR] tombol siap');

        button.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            const isOpen =
                document.body.classList.toggle('ar-sidebar-open');

            console.log(
                '[AR SIDEBAR]',
                isOpen ? 'OPEN' : 'CLOSED'
            );

            if (isOpen) {

                button.innerHTML =
                    '<i class="fa fa-times"></i>';

                button.setAttribute(
                    'title',
                    'Tutup menu'
                );

            } else {

                button.innerHTML =
                    '<i class="fa fa-bars"></i>';

                button.setAttribute(
                    'title',
                    'Buka menu'
                );

            }

        }, true);
    }

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            initArSidebar
        );

    } else {

        initArSidebar();

    }

})();
</script>

@endif
@section('content')

    <style>
        .ar-table .ar-field,
.ar-table .ar-field-ipl {
    border-bottom: 1px solid transparent !important;
    text-decoration: none !important;
}

.ar-table .ar-field:hover,
.ar-table .ar-field-ipl:hover {
    border-bottom-color: #aaa !important;
}

.ar-table .ar-field:focus,
.ar-table .ar-field-ipl:focus {
    border-bottom-color: #2563eb !important;
    outline: none !important;
}

        .ar-page {
            padding: 20px;
        }

        .ar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .ar-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .ar-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .ar-actions {
            display: flex;
            gap: 8px;
        }

        .ar-btn {
            border: 0;
            border-radius: 7px;
            padding: 9px 15px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .ar-btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .ar-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .ar-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .ar-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
        }

        .ar-card-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .ar-card-value {
            font-size: 21px;
            font-weight: 700;
            color: #111827;
        }

        .ar-card-small {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .ar-filter {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .ar-filter-row {
            display: grid;
            grid-template-columns: 180px 180px 180px 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .ar-field label {
            display: block;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .ar-field input,
        .ar-field select {
            width: 100%;
            height: 36px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 13px;
        }

        .ar-table-wrapper {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .ar-table-scroll {
            overflow: auto;
            max-height: calc(100vh - 235px);
        }

        .ar-table {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 10px;
        }

        .ar-table th,
        .ar-table td {
            border-right: 1px solid #d7dce2;
            border-bottom: 1px solid #d7dce2;
            padding: 3px 5px;
            white-space: nowrap;
            vertical-align: middle;
            line-height: 1.15;
            box-sizing: border-box;
        }

        .ar-table thead th {
            position: sticky;
            z-index: 20;
            text-align: center;
            font-weight: 700;
            background-clip: padding-box;
            box-sizing: border-box;
        }

        .ar-table thead tr:first-child th {
            top: 0;
            height: 24px;
        }

        .ar-table thead tr:nth-child(2) th {
            top: 24px;
            height: 24px;
        }

        .ar-table {
            --col-no: 36px;
            --col-customer: 125px;
            --col-po: 105px;
            --sticky-left-po: 161px;
        }

        .ar-table .ar-row > td:nth-child(1) {
            position: sticky;
            left: 0;
            width: var(--col-no);
            min-width: var(--col-no);
            max-width: var(--col-no);
            z-index: 12;
            background: #fff;
            box-sizing: border-box;
        }

        .ar-table .ar-row > td:nth-child(2) {
            position: sticky;
            left: var(--col-no);
            width: var(--col-customer);
            min-width: var(--col-customer);
            max-width: var(--col-customer);
            z-index: 11;
            background: #fff;
            box-sizing: border-box;
        }

        .ar-table .ar-row > td:nth-child(3) {
            position: sticky;
            left: var(--sticky-left-po);
            width: var(--col-po);
            min-width: var(--col-po);
            max-width: var(--col-po);
            z-index: 11;
            background: #fff;
            box-sizing: border-box;
        }

        .ar-table thead tr:first-child > th:first-child {
            position: sticky;
            left: 0;
            width: var(--col-no);
            min-width: var(--col-no);
            max-width: var(--col-no);
            z-index: 40;
            background: #26364a;
            box-sizing: border-box;
        }

        .ar-table thead tr:nth-child(2) > th:nth-child(1) {
            position: sticky;
            left: var(--col-no);
            width: var(--col-customer);
            min-width: var(--col-customer);
            max-width: var(--col-customer);
            z-index: 39;
            background: #26364a;
            box-sizing: border-box;
        }

        .ar-table thead tr:nth-child(2) > th:nth-child(2) {
            position: sticky;
            left: var(--sticky-left-po);
            width: var(--col-po);
            min-width: var(--col-po);
            max-width: var(--col-po);
            z-index: 39;
            background: #26364a;
            box-sizing: border-box;
        }

        .ar-table .ar-row > td:nth-child(1),
        .ar-table .ar-row > td:nth-child(2),
        .ar-table .ar-row > td:nth-child(3),
        .ar-table thead tr:first-child > th:first-child,
        .ar-table thead tr:nth-child(2) > th:nth-child(1),
        .ar-table thead tr:nth-child(2) > th:nth-child(2) {
            box-shadow: inset -1px 0 0 #d7dce2;
        }

        .ar-table .ar-row:hover > td:nth-child(1),
        .ar-table .ar-row:hover > td:nth-child(2),
        .ar-table .ar-row:hover > td:nth-child(3) {
            background: #f9fafb;
        }

        .ar-table tbody tr:hover {
            background: #f9fafb;
        }

        .th-customer {
            background: #dbeafe;
            color: #1e40af;
        }

        .th-document {
            background: #e0e7ff;
            color: #3730a3;
        }

        .th-export {
            background: #dcfce7;
            color: #166534;
        }

        .th-value {
            background: #fef3c7;
            color: #92400e;
        }

        .th-payment {
            background: #fce7f3;
            color: #9d174d;
        }

        .th-ar {
            background: #ede9fe;
            color: #5b21b6;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .currency {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .month-group-row {
            position: sticky;
            top: 48px;
            z-index: 18;
        }

        .month-group-row td {
            position: sticky;
            top: 48px;
            z-index: 18;
            background: #dbeafe !important;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 12px;
            padding: 8px 10px !important;
            border-top: 2px solid #93c5fd !important;
            border-bottom: 1px solid #93c5fd !important;
            box-shadow: inset 0 -1px 0 #93c5fd;
        }

        .month-total-row td {
            background: #f8fafc !important;
            font-weight: 700;
            color: #374151;
            border-top: 1px solid #cbd5e1 !important;
        }

        .month-total-label {
            text-align: right;
        }

        .po-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .po-item {
            background: #f3f4f6;
            border-radius: 4px;
            padding: 2px 6px;
            display: inline-block;
        }

        .ar-editable-input {
    width: 100%;
    min-width: 90px;
    height: 30px;

    border: 0 !important;
    border-bottom: 1px solid transparent !important;

    border-radius: 0 !important;

    padding: 3px 2px;

    background: transparent !important;

    color: #374151 !important;

    font-size: 12px;
    font-style: normal !important;

    outline: none !important;

    box-shadow: none !important;

    transition: .15s ease;
}

       .ar-editable-input:hover {
    border: 0 !important;
    border-bottom: 1px solid transparent !important;

    background: #f9fafb !important;

    color: #374151 !important;
}

        .ar-editable-input:focus {
            border: 0 !important;
            border-bottom: 2px solid #2563eb !important;

            background: #fff !important;

            color: #374151 !important;

            outline: none !important;

            box-shadow: none !important;
        }

        .ar-editable-input.number {
            text-align: right;
        }

        .ar-editable-input.date {
            min-width: 125px;
        }

        .ar-editable-input.text {
            min-width: 110px;
        }

        .ar-field-legacy {
            color: #374151 !important;
        }

        .ar-legacy-row .ar-editable-input.date {
            min-width: 115px;
            font-size: 10px;
        }

        .ar-legacy-row .money-cell {
            min-width: 105px;
        }

        .ar-legacy-row .money-input {
            min-width: 72px;
        }

        .ar-legacy-row .ar-editable-input.number {
            min-width: 58px;
            width: 65px;
        }

        .ar-legacy-row .field-save-status {
            min-width: 65px;
        }

        .field-save-status {
            display: inline-block;
            min-width: 70px;
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
        }

        .field-save-status.success {
            color: #198754;
        }

        .field-save-status.error {
            color: #dc3545;
        }

        .ar-editable-input.is-saving {
            opacity: .65;
        }

        .ar-editable-input.is-saved {
            box-shadow: 0 0 0 1px #198754 !important;
        }

        .ar-editable-input.is-save-error {
            box-shadow: 0 0 0 1px #dc3545 !important;
        }

        .ar-table input.ar-editable-input,
        .ar-table input.ar-editable-input:visited,
        .ar-table input.ar-editable-input:hover,
        .ar-table input.ar-editable-input:focus {
            color: #374151 !important;
            font-style: normal !important;
            text-decoration: none !important;
        }

        .money-input {
            text-align: right;
            min-width: 120px;
        }

        .money-cell {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 125px;
        }

        .money-cell .currency-prefix {
            flex: 0 0 auto;
            font-weight: 600;
            color: #26364a;
            white-space: nowrap;
        }

        .money-cell .money-input {
            flex: 1 1 auto;
            min-width: 0;
            text-align: right;
        }

        .calculated-rupiah {
            font-weight: 700;
            color: #111827;
            text-align: right;
        }

        .calc-note {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .ar-status {
            display: inline-flex;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .status-lunas {
            background: #dcfce7;
            color: #166534;
        }

        .status-belum {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-sebagian {
            background: #fef3c7;
            color: #92400e;
        }

        .status-overdue {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-save-row {
            border: 0;
            border-radius: 5px;

            padding: 4px 8px;

            font-size: 11px;

            background: #2563eb;
            color: #fff;

            cursor: pointer;

            display: none;
        }

        .ar-row.is-dirty .btn-save-row {
            display: inline-block;
        }

        .btn-save-row:hover {
            background: #1d4ed8;
        }

        .btn-save-row:disabled {
            opacity: .6;
            cursor: wait;
        }

        .btn-add-payment {
            border: 0;
            border-radius: 5px;

            padding: 2px 5px;

            font-size: 9px;

            background: #f3f4f6;
            color: #374151;

            cursor: pointer;

            margin-top: 1px;
        }

        .btn-add-payment:hover {
            background: #e5e7eb;
        }

        .ar-empty {
            text-align: center;
            padding: 50px;
            color: #9ca3af;
        }

        .ar-row.is-dirty {
            background: #fffdf0;
        }

        @media (max-width: 900px) {

            .ar-summary {
                grid-template-columns: repeat(2, 1fr);
            }

            .ar-filter-row {
                grid-template-columns: 1fr 1fr;
            }

        }

        .ar-filter-actions {
            display: flex;
            gap: 7px;
            align-items: end;
        }

        .ar-legacy-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .ar-legacy-modal.show {
            display: flex;
        }

        .ar-legacy-dialog {
            width: min(1500px, 96vw);
            height: min(820px, 94vh);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .ar-legacy-modal-header {
            padding: 11px 14px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex: 0 0 auto;
        }

        .ar-legacy-modal-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .ar-legacy-modal-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .ar-legacy-close {
            border: 0;
            background: #f3f4f6;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            color: #374151;
        }

        .ar-legacy-body {
            padding: 12px;
            display: grid;
            grid-template-columns: minmax(330px, .75fr) minmax(600px, 1.5fr);
            gap: 12px;
            min-height: 0;
            flex: 1 1 auto;
        }

        .ar-legacy-pane {
            min-width: 0;
            min-height: 0;
            border: 1px solid #d7dce2;
            border-radius: 7px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .ar-legacy-pane-title {
            flex: 0 0 auto;
            padding: 7px 9px;
            background: #f8fafc;
            border-bottom: 1px solid #d7dce2;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
        }

        #legacyImportText {
            flex: 1 1 auto;
            width: 100%;
            min-height: 0;
            resize: none;
            border: 0;
            outline: none;
            padding: 9px;
            font-family: Consolas, "Courier New", monospace;
            font-size: 11px;
            line-height: 1.45;
            color: #111827;
        }

        .legacy-preview-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow: auto;
            position: relative;
        }

        .legacy-preview-table {
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 10px;
        }

        .legacy-preview-table th,
        .legacy-preview-table td {
            padding: 4px 6px;
            border-right: 1px solid #d7dce2;
            border-bottom: 1px solid #d7dce2;
            white-space: nowrap;
            vertical-align: middle;
        }

        .legacy-preview-table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #26364a;
            color: #fff;
            font-weight: 700;
        }

        .legacy-preview-table {
            --preview-no: 42px;
            --preview-customer: 125px;
            --preview-po: 105px;
            --preview-po-left: 167px;
        }

        .legacy-preview-table thead th:nth-child(1),
        .legacy-preview-table tbody td:nth-child(1) {
            position: sticky;
            left: 0;
            width: var(--preview-no);
            min-width: var(--preview-no);
            z-index: 30;
            box-shadow: inset -1px 0 0 #d7dce2;
        }

        .legacy-preview-table thead th:nth-child(2),
        .legacy-preview-table tbody td:nth-child(2) {
            position: sticky;
            left: var(--preview-no);
            width: var(--preview-customer);
            min-width: var(--preview-customer);
            z-index: 29;
            box-shadow: inset -1px 0 0 #d7dce2;
        }

        .legacy-preview-table thead th:nth-child(3),
        .legacy-preview-table tbody td:nth-child(3) {
            position: sticky;
            left: var(--preview-po-left);
            width: var(--preview-po);
            min-width: var(--preview-po);
            z-index: 29;
            box-shadow: inset -1px 0 0 #d7dce2;
        }

        .legacy-preview-table tbody td:nth-child(1),
        .legacy-preview-table tbody td:nth-child(2),
        .legacy-preview-table tbody td:nth-child(3) {
            background: #fff;
        }

        .legacy-preview-table tbody tr:nth-child(even) td:nth-child(1),
        .legacy-preview-table tbody tr:nth-child(even) td:nth-child(2),
        .legacy-preview-table tbody tr:nth-child(even) td:nth-child(3) {
            background: #f8fafc;
        }

        .legacy-preview-table thead th:nth-child(1),
        .legacy-preview-table thead th:nth-child(2),
        .legacy-preview-table thead th:nth-child(3) {
            background: #26364a;
            z-index: 40;
        }

        .legacy-preview-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .legacy-preview-empty {
            padding: 30px !important;
            text-align: center;
            color: #9ca3af;
        }

        .ar-legacy-footer {
            flex: 0 0 auto;
            padding: 9px 12px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .ar-legacy-count {
            font-size: 10px;
            color: #6b7280;
        }

        .ar-legacy-footer-actions {
            display: flex;
            gap: 7px;
        }

        .ar-btn-danger {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .ar-btn-success {
            background: #16a34a;
            color: #fff;
        }

        .ar-btn-success:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .legacy-preview-table .money-preview {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 1000px) {
            .ar-legacy-dialog {
                width: 98vw;
                height: 96vh;
            }

            .ar-legacy-body {
                grid-template-columns: 1fr;
                grid-template-rows: 38% 62%;
            }
        }

    </style>

    @php

        $formatUsd = function ($value) {
            return '$ ' . number_format((float) ($value ?? 0), 2, '.', ',');
        };

        $formatRupiah = function ($value) {
            return 'Rp ' . number_format((float) ($value ?? 0), 2, ',', '.');
        };

        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $arsByMonth = $ars
            ->filter(function ($ar) {
                return !empty($ar->exportIpl?->invoice_no);
            })
            ->groupBy(function ($ar) {
                $invoiceNo = trim($ar->exportIpl->invoice_no ?? '');

                if (preg_match('/\/(\d{1,2})\/(\d{4})$/', $invoiceNo, $matches)) {
                    $month = (int) $matches[1];
                    $year  = (int) $matches[2];

                    if ($month >= 1 && $month <= 12) {
                        return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                    }
                }

                return !empty($ar->tanggal_invoice)
                    ? $ar->tanggal_invoice->format('Y-m')
                    : 'unknown';
            })
            ->sortKeys();

                        $arsLegacyByMonth = collect($arsLegacy ?? [])
                            ->filter(function ($ar) {
                                return $ar->m_cont !== null
                                    && (int) $ar->m_cont >= 1
                                    && (int) $ar->m_cont <= 12;
                            })
                            ->sortBy(function ($ar) {
                                return (int) $ar->m_cont;
                            })
                            ->groupBy(function ($ar) {
                                return (int) $ar->m_cont;
                            });

        $totalInvoice = $ars->count() + $arsLegacyByMonth->flatten(1)->count();

        $totalPenjualan = $ars->sum(function ($ar) {
            return (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu;
        }) + $arsLegacyByMonth->flatten(1)->sum(function ($ar) {
            return (float) $ar->fob_usd * (float) $ar->kurs_kemenkeu;
        });

        $totalPiutang = $ars->sum(function ($ar) {
            $kurs = (float) ($ar->kurs_kemenkeu ?? 0);
            $surchargeUsd = $kurs > 0
                ? ((float) $ar->total_surcharge / $kurs)
                : 0;

            return (float) $ar->fob_peb_usd
                + $surchargeUsd
                - (float) $ar->total_deposit
                - (float) $ar->total_pelunasan;
        }) + $arsLegacyByMonth->flatten(1)->sum(function ($ar) {
            $deposit = (float) ($ar->deposit_usd ?? 0);
            $pelunasan = (float) ($ar->pelunasan_usd ?? 0);
            return max(0, (float) ($ar->fob_usd ?? 0) - $deposit - $pelunasan);
        });

        $containerMonth = collect();
        foreach ($arsByMonth as $monthKey => $monthArs) {
            $containerMonth->put($monthKey, (int) $monthArs->sum(fn($ar) => (int) ($ar->jumlah_container ?? 0)));
        }
        foreach ($arsLegacyByMonth as $legacyMonthKey => $legacyGroup) {
            $monthNumber = (int) $legacyMonthKey;
            $matchingKey = $containerMonth->keys()
                ->filter(fn($key) => (int) substr($key, 5, 2) === $monthNumber)
                ->sortDesc()
                ->first();
            if ($matchingKey !== null) {
                $containerMonth->put($matchingKey, $containerMonth->get($matchingKey, 0) + (int) $legacyGroup->sum(fn($ar) => (int) ($ar->jumlah_container ?? 0)));
            } else {
                $containerMonth->put('legacy-' . str_pad($monthNumber, 2, '0', STR_PAD_LEFT), (int) $legacyGroup->sum(fn($ar) => (int) ($ar->jumlah_container ?? 0)));
            }
        }
        $averageContainerPerMonth = $containerMonth->count()
            ? $containerMonth->sum() / $containerMonth->count()
            : 0;
    @endphp

    <div class="ar-page">

        <div class="ar-header">

        @section('btn')
            <div>

                <div class="ar-title">
                    Rekap AR Buyer / Penjualan Export
                </div>

                <div class="ar-subtitle">
                    Monitoring invoice, pembayaran, dan sisa piutang customer
                </div>

            </div>
        @endsection

        <div class="ar-actions mt-4">

        </div>

    </div>

    <div class="ar-summary">

        <div class="ar-card">

            <div class="ar-card-label">
                TOTAL INVOICE
            </div>

            <div class="ar-card-value">

                {{ number_format($totalInvoice, 0, ',', '.') }}

            </div>

            <div class="ar-card-small">
                Invoice / AR
            </div>

        </div>

        <div class="ar-card">
            <div class="ar-card-label">RATA-RATA CONTAINER / BULAN</div>
            <div class="ar-card-value">{{ number_format($averageContainerPerMonth, 2, ',', '.') }}</div>
            <div class="ar-card-small">Container / Bulan</div>
        </div>

        <div class="ar-card">

            <div class="ar-card-label">
                TOTAL PENJUALAN
            </div>

            <div class="ar-card-value">

                {{ $formatRupiah($totalPenjualan) }}

            </div>

        </div>

        <div class="ar-card">

            <div class="ar-card-label">
                SISA PIUTANG
            </div>

            <div class="ar-card-value">

                {{ $formatUsd($totalPiutang) }}

            </div>

        </div>

    </div>

    <div class="ar-filter">

        <div class="ar-filter-row">

            <div class="ar-field">

                <label>
                    Periode
                </label>

                <select id="filterYear">

                    <option value="">
                        Semua Tahun
                    </option>

                    @php
                        $currentYears = $ars
                            ->filter(fn($ar) => !empty($ar->tanggal_invoice))
                            ->map(fn($ar) => $ar->tanggal_invoice->format('Y'))
                            ->values()
                            ->toBase();

                        $legacyYears = $arsLegacyByMonth
                            ->flatten(1)
                            ->filter(fn($ar) => !empty($ar->tanggal_invoice))
                            ->map(fn($ar) => $ar->tanggal_invoice->format('Y'))
                            ->values()
                            ->toBase();

                        $filterYears = $currentYears
                            ->merge($legacyYears)
                            ->unique()
                            ->sortDesc()
                            ->values();
                    @endphp

                    @foreach ($filterYears as $year)
                        <option value="{{ $year }}">
                            {{ $year }}
                        </option>
                    @endforeach

                </select>

            </div>

            <div class="ar-field">

                <label>
                    Status AR
                </label>

                <select id="filterStatus">

                    <option value="">
                        Semua Status
                    </option>

                    <option value="belum_dibayar">
                        Belum Dibayar
                    </option>

                    <option value="deposit">
                        Deposit
                    </option>

                    <option value="sebagian">
                        Sebagian
                    </option>

                    <option value="lunas">
                        Lunas
                    </option>

                    <option value="overdue">
                        Overdue
                    </option>

                </select>

            </div>

            <div class="ar-field">

                <label>
                    Customer
                </label>

                <input type="text" id="filterCustomer" placeholder="Nama customer...">

            </div>

            <div class="ar-field">

                <label>
                    Pencarian
                </label>

                <input type="text" id="filterSearch" placeholder="Invoice / PO / PEB...">

            </div>

            <div class="ar-filter-actions">
                <button type="button" class="ar-btn ar-btn-primary" onclick="filterAr()">
                    Filter
                </button>

                <button type="button" class="ar-btn ar-btn-success" onclick="openLegacyImport()">
                    <i class="fa fa-plus"></i> Add AR Lama
                </button>
            </div>

        </div>

    </div>

    <div class="ar-table-wrapper">

        <div class="ar-table-scroll">

            <table class="ar-table">

                <thead>

                    <tr>

                        <th rowspan="2">
                            No
                        </th>

                        <th colspan="4" class="th-customer">
                            CUSTOMER
                        </th>

                        <th colspan="2" class="th-document">
                            DOKUMEN EXPORT
                        </th>

                        <th colspan="5" class="th-value">
                            NILAI PENJUALAN
                        </th>

                        <th colspan="3" class="th-payment">
                            PEMBAYARAN
                        </th>

                        <th colspan="2" class="th-ar">
                            ACCOUNT RECEIVABLE
                        </th>

                        <th rowspan="2">
                            Keterangan
                        </th>

                        <th rowspan="2">
                            Action
                        </th>

                    </tr>

                    <tr>

                        <th>
                            Nama Pelanggan
                        </th>

                        <th>
                            No. PO
                        </th>

                        <th>
                            No. Invoice
                        </th>

                        <th>
                            Tanggal Shipment
                        </th>

                        <th>
                            No. Pengajuan PEB
                        </th>

                        <th>
                            No. PEB
                        </th>

                        <th>
                            FOB (USD)
                        </th>

                        <th>
                            FOB PEB (USD)
                        </th>

                        <th>
                            Kurs Kemenkeu
                        </th>

                        <th>
                            Jumlah Container
                        </th>

                        <th>
                            Jumlah (Rp)
                        </th>

                        <th>
                            Deposit / Uang Muka
                        </th>

                        <th>
                            Pelunasan
                        </th>

                        <th>
                            Surcharge
                        </th>

                        <th>
                            Sisa Piutang
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @if ($arsByMonth->isEmpty() && $arsLegacyByMonth->isEmpty())
                        <tr>
                            <td colspan="19" class="ar-empty">
                                Belum ada data AR.
                            </td>
                        </tr>
                    @else

                        @php
                            $combinedMonths = collect();

                            foreach ($arsByMonth as $monthKey => $currentMonthArs) {
                                [$combinedYear, $combinedMonth] = explode('-', $monthKey);

                                $combinedMonths->put($monthKey, [
                                    'key' => $monthKey,
                                    'year' => (int) $combinedYear,
                                    'month' => (int) $combinedMonth,
                                    'current' => $currentMonthArs,
                                    'legacy' => collect(),
                                ]);
                            }

                            foreach ($arsLegacyByMonth as $legacyMonthKey => $legacyGroup) {
                                $legacyMonthNumber = (int) $legacyMonthKey;

                                $matchingCurrentKey = $combinedMonths
                                    ->filter(function ($group) use ($legacyMonthNumber) {
                                        return (int) $group['month'] === $legacyMonthNumber;
                                    })
                                    ->sortByDesc('year')
                                    ->keys()
                                    ->first();

                                if ($matchingCurrentKey !== null) {
                                    $group = $combinedMonths->get($matchingCurrentKey);
                                    $group['legacy'] = $legacyGroup;
                                    $combinedMonths->put($matchingCurrentKey, $group);
                                } else {
                                    $legacyOnlyKey = 'legacy-' . str_pad($legacyMonthNumber, 2, '0', STR_PAD_LEFT);

                                    $combinedMonths->put($legacyOnlyKey, [
                                        'key' => $legacyOnlyKey,
                                        'year' => null,
                                        'month' => $legacyMonthNumber,
                                        'current' => collect(),
                                        'legacy' => $legacyGroup,
                                    ]);
                                }
                            }

                            $combinedMonths = $combinedMonths
                                ->sortBy(function ($group) {
                                    return sprintf(
                                        '%04d-%02d',
                                        $group['year'] ?? 0,
                                        $group['month']
                                    );
                                });
                        @endphp

                        @foreach ($combinedMonths as $combinedGroup)
                            @php
                                $monthArs = $combinedGroup['current'];
                                $legacyMonthArs = $combinedGroup['legacy'];
                                $month = (int) $combinedGroup['month'];
                                $year = $combinedGroup['year'];
                                $monthName = $bulanIndonesia[$month] ?? 'Tanpa Bulan';

                                $legacyMonth = $month;
                                $legacyMonthName = $monthName;

                                $legacyFobUsd = $legacyMonthArs->sum(fn($ar) => (float) $ar->fob_usd);
                                $legacyFobPebUsd = $legacyMonthArs->sum(fn($ar) => (float) $ar->fob_peb_usd);
                                $legacyContainer = $legacyMonthArs->sum(fn($ar) => (int) $ar->jumlah_container);
                                $legacyJumlahRp = $legacyMonthArs->sum(fn($ar) =>
                                    (float) $ar->fob_usd * (float) $ar->kurs_kemenkeu
                                );
                                $legacyDeposit = $legacyMonthArs->sum(fn($ar) =>
                                    (float) ($ar->deposit_usd ?? 0)
                                );
                                $legacyPelunasan = $legacyMonthArs->sum(fn($ar) =>
                                    (float) ($ar->pelunasan_usd ?? 0)
                                );
                                $legacyPiutangUsd = $legacyMonthArs->sum(function ($ar) {
                                    $deposit = (float) ($ar->deposit_usd ?? 0);
                                    $pelunasan = (float) ($ar->pelunasan_usd ?? 0);
                                    return max(0, (float) ($ar->fob_usd ?? 0) - $deposit - $pelunasan);
                                });

                                $monthFobUsd = $monthArs->sum(fn($ar) => (float) $ar->fob_usd);
                                $monthFobPebUsd = $monthArs->sum(fn($ar) => (float) $ar->fob_peb_usd);
                                $monthContainer = $monthArs->sum(fn($ar) => (int) $ar->jumlah_container);
                                $monthJumlahRp = $monthArs->sum(fn($ar) =>
                                    (float) $ar->fob_peb_usd * (float) $ar->kurs_kemenkeu
                                );
                                $monthDeposit = $monthArs->sum(fn($ar) => (float) $ar->total_deposit);
                                $monthPelunasan = $monthArs->sum(fn($ar) => (float) $ar->total_pelunasan);
                                $monthSurcharge = $monthArs->sum(fn($ar) => (float) $ar->total_surcharge);
                                $monthPiutangUsd = $monthArs->sum(function ($ar) {
                                    $kurs = (float) ($ar->kurs_kemenkeu ?? 0);
                                    $surchargeUsd = $kurs > 0
                                        ? ((float) $ar->total_surcharge / $kurs)
                                        : 0;

                                    return (float) $ar->fob_peb_usd
                                        + $surchargeUsd
                                        - (float) $ar->total_deposit
                                        - (float) $ar->total_pelunasan;
                                });

                                $combinedFobUsd = $legacyFobUsd + $monthFobUsd;
                                $combinedFobPebUsd = $legacyFobPebUsd + $monthFobPebUsd;
                                $combinedContainer = $legacyContainer + $monthContainer;
                                $combinedJumlahRp = $legacyJumlahRp + $monthJumlahRp;
                                $combinedDeposit = $legacyDeposit + $monthDeposit;
                                $combinedPelunasan = $legacyPelunasan + $monthPelunasan;
                                $combinedSurcharge = $monthSurcharge;
                                $combinedPiutangUsd = $legacyPiutangUsd + $monthPiutangUsd;

                                $combinedDataMonth = $combinedGroup['key'];
                            @endphp

                            <tr class="month-group-row" data-month="{{ $combinedDataMonth }}">
                                <td colspan="19">
                                    <i class="fa fa-calendar mr-1"></i>
                                    {{ strtoupper($monthName) }}@if($year) {{ $year }}@endif
                                    <span style="font-weight:500;margin-left:8px;">
                                        ({{ $legacyMonthArs->count() + $monthArs->count() }} Invoice)
                                    </span>
                                </td>
                            </tr>

                            @if ($legacyMonthArs->isNotEmpty())
@foreach ($legacyMonthArs as $legacyIndex => $legacy)
    @php
        $legacyFob = (float) ($legacy->fob_usd ?? 0);
        $legacyFobPeb = (float) ($legacy->fob_peb_usd ?? 0);
        $legacyKurs = (float) ($legacy->kurs_kemenkeu ?? 0);

        $legacyRp = $legacyFob * $legacyKurs;

        $legacyDeposit = (float) ($legacy->deposit_usd ?? 0);
        $legacyPelunasan = (float) ($legacy->pelunasan_usd ?? 0);
        $legacySisa = max(0, $legacyFob - $legacyDeposit - $legacyPelunasan);

        $legacyStatus = $legacySisa <= 0.005
            ? 'lunas'
            : (($legacyDeposit > 0 && $legacyPelunasan <= 0.005)
                ? 'deposit'
                : (($legacyDeposit > 0 || $legacyPelunasan > 0) ? 'sebagian' : 'belum_dibayar'));

        $legacyStatusLabel = match ($legacyStatus) {
            'lunas' => 'Lunas',
            'deposit' => 'Deposit',
            'sebagian' => 'Sebagian',
            default => 'Belum Dibayar',
        };

        $legacyStatusClass = match ($legacyStatus) {
            'lunas' => 'status-lunas',
            'deposit', 'sebagian' => 'status-sebagian',
            default => 'status-belum',
        };

        $legacyCustomer = $legacy->nama_pelanggan ?? '';
        $legacyPo = $legacy->no_po ?? '';
        $legacyInvoice = $legacy->no_invoice ?? '';
    @endphp

    <tr class="ar-row ar-legacy-row"
        data-legacy="1"
        data-legacy-id="{{ $legacy->id }}"
        data-m-cont="{{ (int) ($legacy->m_cont ?? 0) }}"
        data-year="{{ optional($legacy->tanggal_invoice)->format('Y') }}"
        data-status="{{ $legacyStatus }}"
        data-customer="{{ strtolower($legacyCustomer) }}"
        data-search="{{ strtolower($legacyInvoice . ' ' . $legacyPo . ' ' . ($legacy->no_peb ?? '') . ' ' . ($legacy->no_pengajuan_peb ?? '')) }}"
        data-deposit-usd="{{ $legacyDeposit }}"
        data-pelunasan-usd="{{ $legacyPelunasan }}">

        <td class="text-center">L{{ $legacyIndex + 1 }}</td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="nama_pelanggan"
                value="{{ $legacyCustomer }}">
        </td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="no_po"
                value="{{ $legacyPo }}">
        </td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="no_invoice"
                value="{{ $legacyInvoice }}">
        </td>

        <td>
            <input type="date"
                class="ar-editable-input date ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="tanggal_shipment"
                value="{{ !empty($legacy->tanggal_shipment) ? \Carbon\Carbon::parse($legacy->tanggal_shipment)->format('Y-m-d') : '' }}">
        </td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="no_pengajuan_peb"
                value="{{ $legacy->no_pengajuan_peb ?? '' }}">
        </td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="no_peb"
                value="{{ $legacy->no_peb ?? '' }}">
        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field-legacy calc-trigger"
                    data-id="{{ $legacy->id }}"
                    data-field="fob_usd"
                    data-money="usd"
                    value="{{ $legacyFob }}">
            </div>
        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field-legacy"
                    data-id="{{ $legacy->id }}"
                    data-field="fob_peb_usd"
                    data-money="usd"
                    value="{{ $legacyFobPeb }}">
            </div>
        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">Rp</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field-legacy calc-trigger"
                    data-id="{{ $legacy->id }}"
                    data-field="kurs_kemenkeu"
                    data-money="idr"
                    value="{{ number_format($legacyKurs, 2, ',', '.') }}">
            </div>
        </td>

        <td class="text-center">
            <input type="number"
                min="0"
                step="1"
                class="ar-editable-input number ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="jumlah_container"
                value="{{ (int) $legacy->jumlah_container }}">
        </td>

        <td class="calculated-rupiah"
            data-jumlah-rupiah-legacy="{{ $legacy->id }}">
            {{ $formatRupiah($legacyRp) }}
        </td>

        <td class="currency">
            <input type="date"
                class="ar-editable-input date ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="deposit_date"
                value="{{ !empty($legacy->deposit_date) ? \Carbon\Carbon::parse($legacy->deposit_date)->format('Y-m-d') : '' }}"
                title="Tanggal Deposit">

            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field-legacy calc-legacy-trigger"
                    data-id="{{ $legacy->id }}"
                    data-field="deposit_usd"
                    data-money="usd"
                    value="{{ $legacyDeposit }}"
                    title="Jumlah Deposit USD">
            </div>
        </td>

        <td class="currency">
            <input type="date"
                class="ar-editable-input date ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="pelunasan_date"
                value="{{ !empty($legacy->pelunasan_date) ? \Carbon\Carbon::parse($legacy->pelunasan_date)->format('Y-m-d') : '' }}"
                title="Tanggal Pelunasan">

            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field-legacy calc-legacy-trigger"
                    data-id="{{ $legacy->id }}"
                    data-field="pelunasan_usd"
                    data-money="usd"
                    value="{{ $legacyPelunasan }}"
                    title="Jumlah Pelunasan USD">
            </div>
        </td>

        <td class="currency">Rp 0,00</td>

        <td class="currency" data-sisa-piutang-legacy="{{ $legacy->id }}">
            {{ $formatUsd($legacySisa) }}
        </td>

        <td class="text-center" data-status-legacy="{{ $legacy->id }}">
            <span class="ar-status {{ $legacyStatusClass }}">
                {{ $legacyStatusLabel }}
            </span>
        </td>

        <td>
            <input type="text"
                class="ar-editable-input text ar-field-legacy"
                data-id="{{ $legacy->id }}"
                data-field="keterangan"
                value="{{ $legacy->keterangan ?? '' }}">
        </td>

        <td class="text-center">
            <span class="field-save-status"
                data-save-status="{{ $legacy->id }}"></span>
        </td>
    </tr>
@endforeach

                            @endif

                            @if ($monthArs->isNotEmpty())
@foreach ($monthArs as $monthIndex => $ar)
    @php

        $ipl = $ar->exportIpl;

        $status = $ar->status_ar_calculated;

        $statusLabel = match ($status) {
            'lunas' => 'Lunas',

            'belum_dibayar' => 'Belum Dibayar',

            'deposit' => 'Deposit',

            'sebagian' => 'Sebagian',

            'overdue' => 'Overdue',

            default => ucfirst($status),
        };

        $statusClass = match ($status) {
            'lunas' => 'status-lunas',

            'overdue' => 'status-overdue',

            'sebagian', 'deposit' => 'status-sebagian',

            default => 'status-belum',
        };

        $poNos = collect();

        if ($ipl && $ipl->pos) {
            $poNos = $ipl->pos
                ->pluck('po_no')
                ->filter()
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter()
                ->unique()
                ->values();
        }

        if ($poNos->isEmpty() && $ipl && $ipl->items) {
            $poNos = $ipl->items
                ->pluck('po_no')
                ->filter()
                ->map(function ($value) {
                    return trim($value);
                })
                ->filter()
                ->unique()
                ->values();
        }

        $fobUsdValue = (float) ($ar->fob_usd ?? 0);
        $fobPebUsdValue = (float) ($ar->fob_peb_usd ?? 0);
        $kursValue = (float) ($ar->kurs_kemenkeu ?? 0);

        $jumlahRp = $fobPebUsdValue * $kursValue;

        $surchargeUsd = $kursValue > 0
            ? ((float) $ar->total_surcharge / $kursValue)
            : 0;

        $sisaPiutang =
            $fobPebUsdValue
            + $surchargeUsd
            - (float) $ar->total_deposit
            - (float) $ar->total_pelunasan;
    @endphp

    <tr class="ar-row"
        data-deposit-usd="{{ (float) $ar->total_deposit }}"
        data-pelunasan-usd="{{ (float) $ar->total_pelunasan }}"
        data-id="{{ $ar->id }}"
        data-year="{{ preg_match('/\/(\d{4})$/', trim($ipl->invoice_no ?? ''), $ym) ? $ym[1] : optional($ar->tanggal_invoice)->format('Y') }}"
        data-status="{{ $status }}"
        data-customer="{{ strtolower($ipl->buyer ?? '') }}"
        data-search="{{ strtolower(
            ($ipl->invoice_no ?? '') .
                ' ' .
                $poNos->implode(' ') .
                ' ' .
                ($ar->no_peb ?? '') .
                ' ' .
                ($ar->no_pengajuan_peb ?? ''),
        ) }}">

        <td class="text-center">
            {{ $monthIndex + 1 }}
        </td>

        <td>

            <input type="text" class="ar-editable-input text ar-field-ipl"
                data-id="{{ $ipl->id ?? '' }}" data-field="buyer"
                value="{{ $ipl->buyer ?? '' }}">

        </td>

        <td>

            <div class="po-list">

                @forelse($poNos as $poNo)
                    <span class="po-item">
                        {{ $poNo }}
                    </span>

                @empty

                    -
                @endforelse

            </div>

        </td>

        <td>

            <input type="text" class="ar-editable-input text ar-field-ipl"
                data-id="{{ $ipl->id ?? '' }}" data-field="invoice_no"
                value="{{ $ipl->invoice_no ?? '' }}">

        </td>

        <td>

            <input type="date" class="ar-editable-input date ar-field-ipl"
                data-id="{{ $ipl->id ?? '' }}" data-field="release_date"
                value="{{ !empty($ipl->release_date) ? \Carbon\Carbon::parse($ipl->release_date)->format('Y-m-d') : '' }}">

        </td>

        <td>

            <input type="text" class="ar-editable-input text ar-field"
                data-id="{{ $ar->id }}" data-field="no_pengajuan_peb"
                value="{{ $ar->no_pengajuan_peb ?? '' }}">

        </td>

        <td>

            <input type="text" class="ar-editable-input text ar-field"
                data-id="{{ $ar->id }}" data-field="no_peb"
                value="{{ $ar->no_peb ?? '' }}">

        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field"
                    data-id="{{ $ar->id }}"
                    data-field="fob_usd"
                    data-money="usd"
                    value="{{ $fobUsdValue }}">
            </div>
        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">$</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field calc-trigger"
                    data-id="{{ $ar->id }}"
                    data-field="fob_peb_usd"
                    data-money="usd"
                    value="{{ $fobPebUsdValue }}">
            </div>
        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">Rp</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field calc-trigger"
                    data-id="{{ $ar->id }}" data-field="kurs_kemenkeu"
                    data-money="idr" data-raw-value="{{ $kursValue }}"
                    value="{{ number_format($kursValue, 2, ',', '.') }}">
            </div>
        </td>

        <td class="text-center">

            <input type="number" min="0" step="1"
                class="ar-editable-input number ar-field" data-id="{{ $ar->id }}"
                data-field="jumlah_container" value="{{ $ar->jumlah_container }}">

        </td>

        <td class="calculated-rupiah" data-jumlah-rupiah="{{ $ar->id }}">

            {{ $formatRupiah($jumlahRp) }}

        </td>

        <td class="currency">

            {{ $formatUsd($ar->total_deposit) }}

            <br>

            <button type="button" class="btn-add-payment"
                onclick="addPayment({{ $ar->id }}, 'deposit')">
                + Deposit
            </button>

        </td>

        <td class="currency">

            {{ $formatUsd($ar->total_pelunasan) }}

            <br>

            <button type="button" class="btn-add-payment"
                onclick="addPayment({{ $ar->id }}, 'pelunasan')">
                + Pelunasan
            </button>

        </td>

        <td class="currency">
            <div class="money-cell">
                <span class="currency-prefix">Rp</span>
                <input type="text"
                    class="ar-editable-input money-input ar-field calc-trigger"
                    data-id="{{ $ar->id }}" data-field="total_surcharge"
                    data-money="idr"
                    value="{{ number_format((float) $ar->total_surcharge, 2, ',', '.') }}">
            </div>
        </td>

        <td class="currency" data-sisa-piutang="{{ $ar->id }}">

            {{ $formatUsd($sisaPiutang) }}

        </td>

        <td>

            <input type="text" class="ar-editable-input text ar-field"
                data-id="{{ $ar->id }}" data-field="keterangan"
                value="{{ $ar->keterangan ?? '' }}">

        </td>

        <td class="text-center">
            <span class="field-save-status"
                data-save-status="{{ $ar->id }}"></span>
        </td>

    </tr>
@endforeach

                            @endif

                            <tr class="month-total-row">
                                <td></td>
                                <td colspan="6" class="month-total-label">
                                    TOTAL {{ $monthName }}@if($year) {{ $year }}@endif
                                </td>
                                <td class="currency">{{ $formatUsd($combinedFobUsd) }}</td>
                                <td class="currency">{{ $formatUsd($combinedFobPebUsd) }}</td>
                                <td class="currency">-</td>
                                <td class="text-center">{{ number_format($combinedContainer, 0, ',', '.') }}</td>
                                <td class="currency">{{ $formatRupiah($combinedJumlahRp) }}</td>
                                <td class="currency">{{ $formatUsd($combinedDeposit) }}</td>
                                <td class="currency">{{ $formatUsd($combinedPelunasan) }}</td>
                                <td class="currency">{{ $formatRupiah($combinedSurcharge) }}</td>
                                <td class="currency">{{ $formatUsd($combinedPiutangUsd) }}</td>
                                <td class="text-center">-</td>
                                <td>-</td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endif

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================================
     MODAL IMPORT AR LAMA MASSAL
========================================================== -->
<div id="legacyImportModal" class="ar-legacy-modal" aria-hidden="true">
    <div class="ar-legacy-dialog">
        <div class="ar-legacy-modal-header">
            <div>
                <div class="ar-legacy-modal-title">
                    <i class="fa fa-upload mr-1"></i>
                    Add AR Lama — Paste dari Excel
                </div>
                <div class="ar-legacy-modal-subtitle">
                    Copy dari Excel lalu paste langsung ke textarea. Preview akan dibuat otomatis.
                    Data yang disimpan mengikuti kolom AR Lama yang tersedia.
                </div>
            </div>

            <button type="button" class="ar-legacy-close" onclick="closeLegacyImport()">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div style="padding:12px 16px 0;">
            <label for="legacyMCont"
                style="display:block;font-size:11px;color:#374151;font-weight:600;margin-bottom:5px;">
                Bulan Container (m_cont)
            </label>
            <input
                type="number"
                id="legacyMCont"
                min="1"
                max="12"
                step="1"
                inputmode="numeric"
                placeholder="Contoh: 4"
                style="width:180px;height:34px;border:1px solid #d1d5db;border-radius:6px;padding:0 9px;font-size:12px;"
            >
            <div style="font-size:10px;color:#9ca3af;margin-top:4px;margin-bottom:8px;">
                Isi angka bulan 1–12. Nilai ini berlaku untuk semua baris yang diimport.
            </div>
        </div>

        <div class="ar-legacy-body">
            <div class="ar-legacy-pane">
                <div class="ar-legacy-pane-title">
                    DATA EXCEL / CLIPBOARD
                </div>

                <textarea
                    id="legacyImportText"
                    spellcheck="false"
                    placeholder="Paste data dari Excel di sini..."></textarea>
            </div>

            <div class="ar-legacy-pane">
                <div class="ar-legacy-pane-title">
                    PREVIEW
                </div>

                <div class="legacy-preview-scroll">
                    <table class="legacy-preview-table">
                        <thead id="legacyPreviewHead"></thead>
                        <tbody id="legacyPreviewBody">
                            <tr>
                                <td class="legacy-preview-empty">
                                    Belum ada data. Paste data Excel di sebelah kiri.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ar-legacy-footer">
            <div class="ar-legacy-count" id="legacyImportCount">
                0 baris siap diimport
            </div>

            <div class="ar-legacy-footer-actions">
                <button type="button" class="ar-btn ar-btn-danger" onclick="closeLegacyImport()">
                    Batal
                </button>

                <button type="button" class="ar-btn ar-btn-success" id="btnSaveLegacyImport" disabled
                    onclick="saveLegacyImport()">
                    <i class="fa fa-database"></i>
                    Simpan AR Lama
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    function parseNumber(value) {
        if (value === null || value === undefined) return 0;

        let str = String(value).trim();
        if (!str) return 0;

        str = str.replace(/Rp/gi, '').replace(/\$/g, '').trim();

        if (str.includes('.') && str.includes(',')) {
            const lastDot = str.lastIndexOf('.');
            const lastComma = str.lastIndexOf(',');

            if (lastComma > lastDot) {
                str = str.replace(/\./g, '').replace(',', '.');
            } else {
                str = str.replace(/,/g, '');
            }
        } else if (str.includes(',')) {
            const parts = str.split(',');
            const last = parts[parts.length - 1];
            if (last.length <= 2) {
                str = str.replace(/,/g, '.');
            } else {
                str = str.replace(/,/g, '');
            }
        }

        str = str.replace(/[^0-9.-]/g, '');
        const result = Number(str);
        return Number.isFinite(result) ? result : 0;
    }

    function formatUsd(value) {
        value =
            parseFloat(value) || 0;

        return '$ ' +
            value.toLocaleString(
                'en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }

    function formatRupiah(value) {
        value =
            parseFloat(value) || 0;

        return 'Rp ' +
            value.toLocaleString(
                'id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }

    function formatKurs(value) {
        value =
            parseFloat(value) || 0;

        return 'Rp ' +
            value.toLocaleString(
                'id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );
    }

    function formatMoneyInput(input) {
        if (!input) {
            return;
        }

        const type =
            input.dataset.money;

        const raw = input.dataset.rawValue;
        const value = raw !== undefined && raw !== '' ?
            (Number(raw) || 0) :
            parseNumber(input.value);

        if (type === 'usd') {
            input.value = value.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else if (type === 'idr') {

            input.value =
                value.toLocaleString(
                    'id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );

        }

    }

    function calculateJumlahRp(row) {
        if (!row) {
            return 0;
        }

        const fobPebInput =
            row.querySelector(
                '[data-field="fob_peb_usd"]'
            );

        const kursInput =
            row.querySelector(
                '[data-field="kurs_kemenkeu"]'
            );

        const fobPeb =
            parseNumber(
                fobPebInput ?
                fobPebInput.value :
                0
            );

        const kurs =
            parseNumber(
                kursInput ?
                kursInput.value :
                0
            );

        const jumlahRp =
            fobPeb * kurs;

        const target =
            row.querySelector(
                '[data-jumlah-rupiah]'
            );

        if (target) {

            target.innerHTML =
                formatRupiah(jumlahRp);

        }

        updateSisaPiutang(
            row,
            jumlahRp
        );

        return jumlahRp;
    }

    function updateSisaPiutang(row, jumlahRp = null) {
        if (!row) return;

        const fobPebInput = row.querySelector('[data-field="fob_peb_usd"]');
        const kursInput = row.querySelector('[data-field="kurs_kemenkeu"]');
        const surchargeInput = row.querySelector('[data-field="total_surcharge"]');

        const fobPeb = parseNumber(fobPebInput ? fobPebInput.value : 0);
        const kurs = parseNumber(kursInput ? kursInput.value : 0);
        const surchargeRp = parseNumber(surchargeInput ? surchargeInput.value : 0);

        const depositUsd = parseFloat(row.dataset.depositUsd || 0) || 0;
        const pelunasanUsd = parseFloat(row.dataset.pelunasanUsd || 0) || 0;
        const surchargeUsd = kurs > 0 ? surchargeRp / kurs : 0;

        const sisaUsd = fobPeb + surchargeUsd - depositUsd - pelunasanUsd;
        const target = row.querySelector('[data-sisa-piutang]');

        if (target) target.innerText = formatUsd(sisaUsd);
    }

    function calculateJumlahRpOnly(row) {
        const fobPebInput =
            row.querySelector(
                '[data-field="fob_peb_usd"]'
            );

        const kursInput =
            row.querySelector(
                '[data-field="kurs_kemenkeu"]'
            );

        const fobPeb =
            parseNumber(
                fobPebInput ?
                fobPebInput.value :
                0
            );

        const kurs =
            parseNumber(
                kursInput ?
                kursInput.value :
                0
            );

        return fobPeb * kurs;
    }

    document.addEventListener(
        'blur',
        function(e) {

            if (
                !e.target.classList.contains(
                    'money-input'
                )
            ) {
                return;
            }

            formatMoneyInput(
                e.target
            );

        },
        true
    );

    document.addEventListener(
        'input',
        function(e) {

            const field =
                e.target;

            if (field.classList.contains('money-input')) {
                delete field.dataset.rawValue;
            }

            if (
                field.classList.contains(
                    'ar-field'
                ) ||
                field.classList.contains(
                    'ar-field-ipl'
                )
            ) {

                const row =
                    field.closest(
                        '.ar-row'
                    );

                if (row) {

                    row.classList.add(
                        'is-dirty'
                    );

                }

            }

            if (
                field.dataset.field ===
                'fob_peb_usd' ||
                field.dataset.field ===
                'kurs_kemenkeu' ||
                field.dataset.field ===
                'total_surcharge'
            ) {

                const row =
                    field.closest(
                        '.ar-row'
                    );

                calculateJumlahRp(
                    row
                );

            }

        }
    );

    document.addEventListener(
        'change',
        function(e) {

            if (
                e.target.classList.contains(
                    'ar-field'
                ) ||
                e.target.classList.contains(
                    'ar-field-ipl'
                )
            ) {

                const row =
                    e.target.closest(
                        '.ar-row'
                    );

                if (row) {

                    row.classList.add(
                        'is-dirty'
                    );

                }

            }

        }
    );

    const savingFields = new WeakMap();

    function setFieldSaveStatus(row, type = 'saving', message = '') {
        if (!row) return;

        const status = row.querySelector('.field-save-status');
        if (!status) return;

        status.className = 'field-save-status ' + type;

        if (type === 'saving') {
            status.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan';
        } else if (type === 'success') {
            status.innerHTML = '<i class="fa fa-check"></i> Tersimpan';
            setTimeout(() => {
                if (status.classList.contains('success')) {
                    status.innerHTML = '';
                    status.className = 'field-save-status';
                }
            }, 1200);
        } else if (type === 'error') {
            status.innerHTML = '<i class="fa fa-times"></i> ' + (message || 'Gagal');
        } else {
            status.innerHTML = '';
        }
    }

    function getCleanFieldValue(input) {
        let value = input.value;

        if (input.dataset.money) {
            return parseNumber(value);
        }

        return value;
    }

    async function saveSingleField(input) {
        if (!input || !input.dataset.field || !input.dataset.id) {
            return;
        }

        const currentValue = getCleanFieldValue(input);
        const oldValue = input.dataset.originalValue ?? '';

        if (String(currentValue) === String(oldValue)) {
            return;
        }

        if (savingFields.get(input)) {
            return savingFields.get(input);
        }

        const row = input.closest('.ar-row');
        const isIpl = input.classList.contains('ar-field-ipl');
        const isLegacy = input.classList.contains('ar-field-legacy');

        const url = isLegacy
            ? '/ar_buyer/legacy/' + input.dataset.id
            : (isIpl
                ? '/ar_buyer/ipl/' + input.dataset.id
                : '/ar_buyer/' + input.dataset.id);

        const request = (async () => {
            setFieldSaveStatus(row, 'saving');
            input.classList.add('is-saving');

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        field: input.dataset.field,
                        value: currentValue
                    })
                });

                const result = await response.json().catch(() => ({}));

                if (!response.ok || result.success === false) {
                    throw new Error(
                        result.message ||
                        ('Gagal menyimpan ' + input.dataset.field)
                    );
                }

                input.dataset.originalValue = String(currentValue);
                input.classList.remove('is-saving');
                input.classList.add('is-saved');
                setFieldSaveStatus(row, 'success');

                setTimeout(() => {
                    input.classList.remove('is-saved');
                }, 1000);

                if (row) {
                    if (isLegacy) {
                        calculateLegacyRow(row);
                    } else if (
                        input.dataset.field === 'fob_peb_usd' ||
                        input.dataset.field === 'kurs_kemenkeu' ||
                        input.dataset.field === 'total_surcharge'
                    ) {
                        calculateJumlahRp(row);
                    }
                }

                if (row) {
                    if (isLegacy && [
                        'nama_pelanggan',
                        'no_po',
                        'no_invoice',
                        'no_peb',
                        'no_pengajuan_peb'
                    ].includes(input.dataset.field)) {
                        rebuildRowSearch(row);
                    }

                    if (input.dataset.field === 'no_peb') {
                        rebuildRowSearch(row);
                    }
                }

            } catch (error) {
                console.error(error);
                input.classList.remove('is-saving');
                input.classList.add('is-save-error');
                setFieldSaveStatus(row, 'error', error.message);

                setTimeout(() => {
                    input.classList.remove('is-save-error');
                }, 1800);
            } finally {
                savingFields.delete(input);
            }
        })();

        savingFields.set(input, request);
        return request;
    }

    document.querySelectorAll('.ar-editable-input').forEach(function(input) {
        input.dataset.originalValue = String(getCleanFieldValue(input));
    });

    document.addEventListener('keydown', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            input.blur();
        }
    });

    document.addEventListener('blur', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        if (input.classList.contains('money-input')) {
            formatMoneyInput(input);
        }

        saveSingleField(input);
    }, true);

    document.addEventListener('focus', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        input.classList.add('is-editing');
    }, true);

    document.addEventListener('input', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        const row = input.closest('.ar-row');
        if (row) row.classList.add('is-dirty');

        if (input.classList.contains('ar-field-legacy')) {
            if ([
                'fob_usd',
                'kurs_kemenkeu',
                'deposit_usd',
                'pelunasan_usd'
            ].includes(input.dataset.field)) {
                calculateLegacyRow(row);
            }
        } else if (
            input.dataset.field === 'fob_peb_usd' ||
            input.dataset.field === 'kurs_kemenkeu'
        ) {
            calculateJumlahRp(row);
        }
    });

    document.addEventListener('change', function(e) {
        const input = e.target.closest('.ar-editable-input');
        if (!input) return;

        if (input.type === 'date' || input.type === 'number') {
            saveSingleField(input);
        }
    });

    function calculateLegacyRow(row) {
        if (!row || !row.classList.contains('ar-legacy-row')) {
            return;
        }

        const fobInput = row.querySelector('[data-field="fob_usd"]');
        const kursInput = row.querySelector('[data-field="kurs_kemenkeu"]');
        const depositInput = row.querySelector('[data-field="deposit_usd"]');
        const pelunasanInput = row.querySelector('[data-field="pelunasan_usd"]');

        const fob = parseNumber(fobInput ? fobInput.value : 0);
        const kurs = parseNumber(kursInput ? kursInput.value : 0);
        const deposit = parseNumber(depositInput ? depositInput.value : 0);
        const pelunasan = parseNumber(pelunasanInput ? pelunasanInput.value : 0);

        const jumlahRp = fob * kurs;
        const sisa = Math.max(0, fob - deposit - pelunasan);

        const jumlahTarget = row.querySelector('[data-jumlah-rupiah-legacy]');
        if (jumlahTarget) {
            jumlahTarget.innerText = formatRupiah(jumlahRp);
        }

        const sisaTarget = row.querySelector('[data-sisa-piutang-legacy]');
        if (sisaTarget) {
            sisaTarget.innerText = formatUsd(sisa);
        }

        row.dataset.depositUsd = String(deposit);
        row.dataset.pelunasanUsd = String(pelunasan);

        updateLegacyStatus(row, sisa, deposit, pelunasan);

        return { jumlahRp, sisa, deposit, pelunasan };
    }

    function updateLegacyStatus(row, sisa = null, deposit = null, pelunasan = null) {
        if (!row) return;

        if (sisa === null || deposit === null || pelunasan === null) {
            const result = calculateLegacyRow(row);
            if (!result) return;
            sisa = result.sisa;
            deposit = result.deposit;
            pelunasan = result.pelunasan;
        }

        let status = 'belum_dibayar';
        let label = 'Belum Dibayar';
        let className = 'status-belum';

        if (sisa <= 0.005) {
            status = 'lunas';
            label = 'Lunas';
            className = 'status-lunas';
        } else if (deposit > 0 && pelunasan <= 0.005) {
            status = 'deposit';
            label = 'Deposit';
            className = 'status-sebagian';
        } else if (deposit > 0 || pelunasan > 0) {
            status = 'sebagian';
            label = 'Sebagian';
            className = 'status-sebagian';
        }

        row.dataset.status = status;

        const target = row.querySelector('[data-status-legacy]');
        if (target) {
            target.innerHTML = `<span class="ar-status ${className}">${label}</span>`;
        }
    }

    function rebuildRowSearch(row) {
        if (!row) return;

        const fields = [
            'nama_pelanggan',
            'no_po',
            'no_invoice',
            'no_peb',
            'no_pengajuan_peb'
        ];

        const values = Array.from(
            row.querySelectorAll('.ar-field-legacy')
        )
        .filter(input => fields.includes(input.dataset.field))
        .map(input => input.value || '');

        row.dataset.search = values.join(' ').toLowerCase();
        row.dataset.customer = (
            row.querySelector('[data-field="nama_pelanggan"]')?.value || ''
        ).toLowerCase();
    }

    function filterAr() {

        const year =
            (
                document.getElementById(
                    'filterYear'
                )?.value ||
                ''
            ).toLowerCase();

        const status =
            (
                document.getElementById(
                    'filterStatus'
                )?.value ||
                ''
            ).toLowerCase();

        const customer =
            (
                document.getElementById(
                    'filterCustomer'
                )?.value ||
                ''
            ).toLowerCase()
            .trim();

        const search =
            (
                document.getElementById(
                    'filterSearch'
                )?.value ||
                ''
            ).toLowerCase()
            .trim();

        document
            .querySelectorAll(
                '.ar-row'
            )
            .forEach(function(row) {

                const rowYear =
                    (
                        row.dataset.year ||
                        ''
                    ).toLowerCase();

                const rowStatus =
                    (
                        row.dataset.status ||
                        ''
                    ).toLowerCase();

                const rowCustomer =
                    (
                        row.dataset.customer ||
                        ''
                    ).toLowerCase();

                const rowSearch =
                    (
                        row.dataset.search ||
                        ''
                    ).toLowerCase();

                let show =
                    true;

                if (
                    year &&
                    rowYear !== year
                ) {

                    show = false;

                }

                if (
                    status &&
                    rowStatus !== status
                ) {

                    show = false;

                }

                if (
                    customer &&
                    !rowCustomer.includes(
                        customer
                    )
                ) {

                    show = false;

                }

                if (
                    search &&
                    !rowSearch.includes(
                        search
                    )
                ) {

                    show = false;

                }

                row.style.display =
                    show ?
                    '' :
                    'none';

            });

        document
            .querySelectorAll(
                '.month-group-row'
            )
            .forEach(function(header) {

                let next =
                    header.nextElementSibling;

                let visible =
                    false;

                let totalRow =
                    null;

                while (
                    next &&
                    !next.classList.contains(
                        'month-group-row'
                    )
                ) {

                    if (
                        next.classList.contains(
                            'month-total-row'
                        )
                    ) {

                        totalRow =
                            next;

                        break;

                    }

                    if (
                        next.classList.contains(
                            'ar-row'
                        ) &&
                        next.style.display !==
                        'none'
                    ) {

                        visible =
                            true;

                    }

                    next =
                        next.nextElementSibling;

                }

                header.style.display =
                    visible ?
                    '' :
                    'none';

                if (totalRow) {

                    totalRow.style.display =
                        visible ?
                        '' :
                        'none';

                }

            });

    }

(function () {

    const liveFilterIds = [
        'filterYear',
        'filterStatus',
        'filterCustomer',
        'filterSearch'
    ];

    liveFilterIds.forEach(function (id) {

        const element = document.getElementById(id);

        if (!element) return;

        element.addEventListener('change', function () {
            filterAr();
        });

        element.addEventListener('input', function () {
            filterAr();
        });

    });

})();

    function addPayment(arId, type) {
        const title =
            type === 'deposit' ?
            'Tambah Deposit / Uang Muka' :
            'Tambah Pelunasan';

        Swal.fire({

            title: title,

            html: `

            <div style="text-align:left">

                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Tanggal Pembayaran
                </label>

                <input
                    id="swal-payment-date"
                    type="date"
                    class="swal2-input"
                    style="margin:0 0 12px 0;width:100%;"
                >

                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Jumlah Pembayaran (USD)
                </label>

                <input
                    id="swal-payment-amount"
                    type="text"
                    class="swal2-input"
                    style="margin:0 0 12px 0;width:100%;"
                    placeholder="$ 0.00"
                >

                <label style="
                    display:block;
                    font-size:12px;
                    margin-bottom:5px;
                    color:#6b7280;
                ">
                    Reference
                </label>

                <input
                    id="swal-payment-reference"
                    type="text"
                    class="swal2-input"
                    style="margin:0;width:100%;"
                    placeholder="No. bukti / reference"
                >

            </div>

        `,

            showCancelButton: true,

            confirmButtonText: 'Simpan',

            cancelButtonText: 'Batal',

            reverseButtons: true,

            didOpen: function() {

                const amountInput =
                    document.getElementById(
                        'swal-payment-amount'
                    );

                amountInput.addEventListener(
                    'blur',
                    function() {

                        const value =
                            parseNumber(
                                this.value
                            );

                        this.value =
                            value.toLocaleString(
                                'id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }
                            );

                    }
                );

            },

            preConfirm: function() {

                const date =
                    document.getElementById(
                        'swal-payment-date'
                    ).value;

                const amount =
                    document.getElementById(
                        'swal-payment-amount'
                    ).value;

                const reference =
                    document.getElementById(
                        'swal-payment-reference'
                    ).value;

                if (!date) {

                    Swal.showValidationMessage(
                        'Tanggal pembayaran wajib diisi.'
                    );

                    return false;

                }

                const amountNumber =
                    parseNumber(
                        amount
                    );

                if (
                    !amountNumber ||
                    amountNumber <= 0
                ) {

                    Swal.showValidationMessage(
                        'Jumlah pembayaran harus lebih dari 0.'
                    );

                    return false;

                }

                return {

                    payment_date: date,

                    amount: amountNumber,

                    reference: reference

                };

            }

        }).then(
            async function(result) {

                if (!result.isConfirmed) {
                    return;
                }

                try {

                    const response =
                        await fetch(
                            '/ar_buyer/' +
                            arId +
                            '/payment', {

                                method: 'PUT',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'Accept': 'application/json',

                                    'X-CSRF-TOKEN': document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        ?.getAttribute(
                                            'content'
                                        )

                                },

                                body: JSON.stringify({

                                    payment_type: type,

                                    payment_date: result.value
                                        .payment_date,

                                    amount: result.value
                                        .amount,

                                    reference: result.value
                                        .reference

                                })

                            }
                        );

                    const data =
                        await response
                        .json()
                        .catch(
                            () => ({})
                        );

                    if (!response.ok) {

                        throw new Error(
                            data.message ||
                            'Gagal menyimpan pembayaran.'
                        );

                    }

                    Swal.fire({

                        icon: 'success',

                        title: 'Berhasil',

                        text: type === 'deposit' ?
                            'Deposit berhasil ditambahkan.' :
                            'Pelunasan berhasil ditambahkan.',

                        timer: 1300,

                        showConfirmButton: false

                    }).then(function() {

                        location.reload();

                    });

                } catch (error) {

                    console.error(
                        'PAYMENT ERROR:',
                        error
                    );

                    Swal.fire({

                        icon: 'error',

                        title: 'Gagal',

                        text: error.message ||
                            'Terjadi kesalahan saat menyimpan pembayaran.'

                    });

                }

            }
        );
    }

    const LEGACY_HEADERS = [
        'No',
        'Nama Pelanggan',
        'No. PO',
        'No. Invoice',
        'Tanggal Shipment',
        'No. Pengajuan PEB',
        'No. PEB',
        'FOB (USD)',
        'FOB PEB (USD)',
        'KURS KEMENKEU (Rp)',
        'JUMLAH CONTAINER',
        'Jumlah (Rp)',
        'Deposit / Uang Muka',
        'Pelunasan',
        'Sisa Piutang',
        'Keterangan'
    ];

    function openLegacyImport() {
        const modal = document.getElementById('legacyImportModal');
        if (!modal) return;

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');

        setTimeout(function () {
            document.getElementById('legacyImportText')?.focus();
            previewLegacyImport();
        }, 50);
    }

    function closeLegacyImport() {
        const modal = document.getElementById('legacyImportModal');
        if (!modal) return;

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        const mContInput = document.getElementById('legacyMCont');
        if (mContInput) {
            mContInput.value = '';
        }
    }

    function cleanClipboardCell(value) {
        return String(value ?? '')
            .replace(/\u00a0/g, ' ')
            .replace(/\u200b/g, '')
            .trim();
    }

    function parseLegacyClipboard(text) {
        if (!text || !text.trim()) return [];

        const lines = text
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split('\n')
            .map(line => line.trim())
            .filter(line => line !== '');

        const rows = [];

        for (const line of lines) {
            let cells;

            if (line.includes('\t')) {
                cells = line.split('\t').map(cleanClipboardCell);
            }
            else if (line.includes('|')) {
                cells = line.split('|').map(cleanClipboardCell);
                if (cells[0] === '') cells.shift();
                if (cells[cells.length - 1] === '') cells.pop();
            }
            else {
                cells = [cleanClipboardCell(line)];
            }

            if (!cells.length) continue;

            const isSeparator = cells.every(cell =>
                /^:?-{2,}:?$/.test(cell.replace(/\s/g, ''))
            );

            if (isSeparator) continue;

            rows.push(cells);
        }

        if (!rows.length) return [];

        const headerIndex = rows.findIndex(row => {
            const joined = row.join(' ').toLowerCase();
            return joined.includes('nama pelanggan') &&
                (joined.includes('no. invoice') || joined.includes('no invoice'));
        });

        let dataRows = headerIndex >= 0
            ? rows.slice(headerIndex + 1)
            : rows;

        dataRows = dataRows.filter(row => {
            const joined = row.join(' ').toLowerCase();
            const first = cleanClipboardCell(row[0] || '');

            return !(
                !/^\d+$/.test(first) &&
                joined.includes('tanggal') &&
                joined.includes('jumlah')
            );
        });

        return dataRows.filter(row => {
            const no = cleanClipboardCell(row[0] || '');
            const customer = cleanClipboardCell(row[1] || '');
            return /^\d+$/.test(no) && customer !== '';
        });
    }

    function legacyPreviewValue(row, index) {
        return cleanClipboardCell(row[index] || '');
    }

    function legacyDepositPreview(row) {
        const date = legacyPreviewValue(row, 12);
        const amount = legacyPreviewValue(row, 13);

        if (!date && !amount) return '-';
        if (date && amount) return `${date} — ${amount}`;
        return date || amount;
    }

    function legacyPelunasanPreview(row) {
        const date = legacyPreviewValue(row, 14);
        const amount = legacyPreviewValue(row, 15);

        if (!date && !amount) return '-';
        if (date && amount) return `${date} — ${amount}`;
        return date || amount;
    }

    function previewLegacyImport() {
        const textarea = document.getElementById('legacyImportText');
        const head = document.getElementById('legacyPreviewHead');
        const body = document.getElementById('legacyPreviewBody');
        const count = document.getElementById('legacyImportCount');
        const button = document.getElementById('btnSaveLegacyImport');

        if (!textarea || !head || !body) return;

        const rows = parseLegacyClipboard(textarea.value);

        const mContInput = document.getElementById('legacyMCont');
        const mCont = mContInput ? parseInt(mContInput.value, 10) : NaN;

        if (!Number.isInteger(mCont) || mCont < 1 || mCont > 12) {
            Swal.fire({
                icon: 'warning',
                title: 'Bulan Container belum benar',
                text: 'Isi Bulan Container (m_cont) dengan angka 1 sampai 12.'
            });

            if (mContInput) {
                mContInput.focus();
            }

            return;
        }

        head.innerHTML = `
            <tr>
                ${LEGACY_HEADERS.map(header =>
                    `<th>${escapeLegacyHtml(header)}</th>`
                ).join('')}
            </tr>
        `;

        if (!rows.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="${LEGACY_HEADERS.length}" class="legacy-preview-empty">
                        Belum ada baris data yang valid.
                    </td>
                </tr>
            `;

            if (count) count.textContent = '0 baris siap diimport';
            if (button) button.disabled = true;
            return;
        }

        body.innerHTML = rows.map(row => {
            const values = [
                legacyPreviewValue(row, 0),
                legacyPreviewValue(row, 1),
                legacyPreviewValue(row, 2),
                legacyPreviewValue(row, 3),
                legacyPreviewValue(row, 4),
                legacyPreviewValue(row, 5),
                legacyPreviewValue(row, 6),
                legacyPreviewValue(row, 7),
                legacyPreviewValue(row, 8),
                legacyPreviewValue(row, 9),
                legacyPreviewValue(row, 10),
                legacyPreviewValue(row, 11),
                legacyDepositPreview(row),
                legacyPelunasanPreview(row),
                legacyPreviewValue(row, 16),
                legacyPreviewValue(row, 17)
            ];

            return `
                <tr>
                    ${values.map((value, index) => {
                        const cls = [7, 8, 9, 10, 11, 12, 13, 14].includes(index)
                            ? 'money-preview'
                            : '';

                        return `<td class="${cls}">${escapeLegacyHtml(value || '-')}</td>`;
                    }).join('')}
                </tr>
            `;
        }).join('');

        if (count) {
            count.textContent = `${rows.length} baris siap diimport · sumber Excel A:R`;
        }

        if (button) button.disabled = false;
    }

    function escapeLegacyHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function saveLegacyImport() {
        const textarea = document.getElementById('legacyImportText');
        const button = document.getElementById('btnSaveLegacyImport');

        if (!textarea || !button) return;

        const rows = parseLegacyClipboard(textarea.value);

        if (!rows.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Data kosong',
                text: 'Paste data Excel terlebih dahulu.'
            });
            return;
        }

        const confirmed = await Swal.fire({
            icon: 'question',
            title: 'Simpan AR Lama?',
            html: '<b>' + rows.length + '</b> baris akan ditambahkan ke AR Lama.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        });

        if (!confirmed.isConfirmed) return;

        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const response = await fetch('/ar_buyer/legacy/mass', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || ''
                },
                body: JSON.stringify({ rows: rows, m_cont: mCont })
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok || result.success === false) {
                throw new Error(
                    result.message || 'Gagal menyimpan AR Lama.'
                );
            }

            closeLegacyImport();

            await Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                html: result.message || (
                    (result.inserted_count ?? 0) + ' AR Lama berhasil ditambahkan.'
                ),
                timer: 1800,
                showConfirmButton: false
            });

            location.reload();

        } catch (error) {
            console.error('LEGACY IMPORT ERROR:', error);

            Swal.fire({
                icon: 'error',
                title: 'Import gagal',
                text: error.message || 'Terjadi kesalahan saat menyimpan data.'
            });

            button.disabled = false;
            button.innerHTML = '<i class="fa fa-database"></i> Simpan AR Lama';
        }
    }

    const legacyImportTextarea = document.getElementById('legacyImportText');

    if (legacyImportTextarea) {
        legacyImportTextarea.addEventListener('input', previewLegacyImport);

        legacyImportTextarea.addEventListener('paste', function () {
            setTimeout(previewLegacyImport, 50);
        });
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('legacyImportModal');

        if (e.key === 'Escape' && modal?.classList.contains('show')) {
            closeLegacyImport();
        }
    });

    const legacyImportModal = document.getElementById('legacyImportModal');

    if (legacyImportModal) {
        legacyImportModal.addEventListener('click', function(e) {
            if (e.target === legacyImportModal) {
                closeLegacyImport();
            }
        });
    }

    document.addEventListener(
        'DOMContentLoaded',
        function() {

            document
                .querySelectorAll(
                    '.money-input'
                )
                .forEach(function(input) {

                    formatMoneyInput(
                        input
                    );

                });

            document
                .querySelectorAll(
                    '.ar-row'
                )
                .forEach(function(row) {

                    if (row.classList.contains('ar-legacy-row')) {
                        calculateLegacyRow(row);
                    } else {
                        calculateJumlahRp(row);
                    }

                });

        }
    );
</script>

@endsection --}}

@include('pages.maintenance.index')