@extends('master.master')

@section('content')
    <style>
        .ipl-table th,
        .ipl-table td {
            vertical-align: middle !important;
            white-space: nowrap;
        }

        .ipl-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #343a40;
            color: #fff;
        }

        .btn-download {
            min-width: 48px;
        }

        .ipl-active-row {
            background-color: rgba(255, 193, 7, 0.28) !important;
            box-shadow: inset 5px 0 0 #ffc107;
            transition: background-color 0.25s ease;
        }

        .ipl-active-row td {
            background-color: rgba(255, 193, 7, 0.28) !important;
        }

        .ipl-active-row:hover td {
            background-color: rgba(255, 193, 7, 0.42) !important;
        }

        #customInvoiceModal .modal-dialog {
            max-width: 95%;
            width: 95%;
            margin: 1.75rem auto;
        }

        #customInvoiceModal .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        #customInvoiceModal .modal-header {
            background: linear-gradient(135deg, #343a40, #495057);
            color: #fff;
            border-bottom: 0;
        }

        #customInvoiceModal .modal-header .close {
            color: #fff;
            opacity: 1;
        }

        .ci-info-box {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            height: 100%;
            background: #f8f9fa;
        }

        .ci-info-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .ci-info-value {
            font-size: 15px;
            font-weight: 600;
            color: #212529;
        }

        .ci-summary {
            border-radius: 10px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .ci-summary-item {
            text-align: center;
            border-right: 1px solid #dee2e6;
        }

        .ci-summary-item:last-child {
            border-right: 0;
        }

        .ci-summary-label {
            display: block;
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .ci-summary-value {
            display: block;
            font-size: 18px;
            font-weight: 700;
            margin-top: 3px;
        }

        .ci-table-wrapper {
            max-height: 55vh;
            overflow: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .ci-table {
            margin-bottom: 0;
            min-width: 1550px;
        }

        .ci-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #343a40;
            color: #fff;
            white-space: nowrap;
            vertical-align: middle;
            font-size: 12px;
        }

        .ci-table td {
            vertical-align: middle !important;
            font-size: 12px;
        }

        .ci-table .description-cell {
            min-width: 250px;
            white-space: normal;
        }

        .ci-custom-description {
            min-width: 260px;
        }

        .ci-qty-input {
            width: 100px;
            text-align: right;
            font-weight: 600;
        }

        .ci-original-qty {
            text-align: right;
            font-weight: 600;
            color: #495057;
        }

        .ci-valid {
            background-color: rgba(40, 167, 69, 0.08) !important;
        }

        .ci-invalid {
            background-color: rgba(220, 53, 69, 0.08) !important;
        }

        .ci-status {
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }

        .ci-desc-status {
            font-size: 11px;
            margin-left: 5px;
        }

        .ci-loading {
            padding: 50px;
            text-align: center;
            color: #6c757d;
        }

        .ci-validation-box {
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 12px;
            display: none;
        }

        .ci-validation-box.valid {
            display: block;
            background: rgba(40, 167, 69, 0.10);
            border: 1px solid rgba(40, 167, 69, 0.30);
            color: #155724;
        }

        .ci-validation-box.invalid {
            display: block;
            background: rgba(220, 53, 69, 0.10);
            border: 1px solid rgba(220, 53, 69, 0.30);
            color: #721c24;
        }

        .ci-footer-total {
            background: #f8f9fa;
            font-weight: 700;
        }

        .ci-generate-disabled {
            cursor: not-allowed;
        }

        .ci-saving {
            border-color: #ffc107 !important;
            background: #fffdf2 !important;
        }

        .ci-saved {
            border-color: #28a745 !important;
            background: #f5fff7 !important;
        }

        .ci-save-error {
            border-color: #dc3545 !important;
            background: #fff6f6 !important;
        }

        /* =========================================================
                       HS CODE HOVER DETAIL
                       ========================================================= */
        .ci-hs-hover-trigger {
            position: relative;
            cursor: pointer;
            color: #007bff;
            border-bottom: 1px dashed #007bff;
            font-weight: 700;
        }

        .ci-hs-hover-trigger:hover {
            color: #0056b3;
        }

        #ciHsHoverPopup {
            position: fixed;
            z-index: 99999;
            display: none;
            width: 430px;
            max-width: calc(100vw - 30px);
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            pointer-events: none;
        }

        #ciHsHoverPopup .ci-hs-popup-title {
            padding: 9px 12px;
            background: #343a40;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        #ciHsHoverPopup .ci-hs-popup-body {
            max-height: 280px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #ciHsHoverPopup table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        #ciHsHoverPopup th,
        #ciHsHoverPopup td {
            padding: 7px 9px;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
            vertical-align: middle;
        }

        #ciHsHoverPopup th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        #ciHsHoverPopup th:first-child,
        #ciHsHoverPopup td:first-child {
            width: 75%;
            white-space: normal;
            word-break: break-word;
        }

        #ciHsHoverPopup th:last-child,
        #ciHsHoverPopup td:last-child {
            width: 25%;
            text-align: right;
            white-space: nowrap;
        }

        #ciHsHoverPopup tr:last-child td {
            border-bottom: 0;
        }

        @media (max-width: 768px) {
            #customInvoiceModal .modal-dialog {
                max-width: 98%;
                width: 98%;
            }

            .ci-summary-item {
                border-right: 0;
                border-bottom: 1px solid #dee2e6;
                padding: 8px 0;
            }

            .ci-summary-item:last-child {
                border-bottom: 0;
            }
        }
    </style>

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
            @section('btn')
                <h4 class="mb-1">
                    <i class="fa fa-file-invoice mr-2"></i>
                    Invoice Packing List
                </h4>
                <small class="text-muted">
                    Daftar Invoice Packing List Export
                </small>
            @endsection
        </div>
    </div>

    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover mb-0 ipl-table">

                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Invoice No</th>
                            <th>Sales Order</th>
                            <th>Buyer</th>
                            <th class="text-center">Items</th>
                            <th>Container</th>
                            <th>ETD</th>
                            <th>Created By</th>
                            <th class="text-center">Released</th>
                            <th class="text-center">Action</th>
                            <th class="text-center">Download</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($datas as $index => $data)
                            @php
                                $activeRef = trim((string) request()->query('ref', ''));
                                $isActive = $activeRef !== '' && trim((string) $data->invoice_no) === $activeRef;
                            @endphp

                            <tr class="{{ $isActive ? 'ipl-active-row' : '' }}"
                                @if ($isActive) id="active-ipl-row" @endif>

                                <td class="text-center">
                                    {{ $datas->firstItem() + $index }}
                                </td>

                                <td>
                                    <strong>{{ $data->invoice_no ?? '-' }}</strong>
                                </td>

                                <td>
                                    {{ $data->sales_order ?? '-' }}
                                </td>

                                <td>
                                    {{ $data->buyer ?? '-' }}
                                </td>

                                <td class="text-center">
                                    <span class="badge badge-info">
                                        {{ $data->items_count ?? 0 }}
                                    </span>
                                </td>

                                <td>
                                    {{ $data->container_type ?? '-' }}
                                </td>

                                <td>
                                    @if ($data->etd)
                                        {{ \Carbon\Carbon::parse($data->etd)->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if (isset($data->creator))
                                        {{ $data->creator->name ?? '-' }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-center">

                                    @if (!empty($data->release_date))
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fa fa-check-circle mr-1"></i>
                                            {{ \Carbon\Carbon::parse($data->release_date)->format('d-m-Y') }}
                                        </span>
                                    @else
                                        <label
                                            style="
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            margin: 0;
        ">

                                            <input type="checkbox" class="release-checkbox"
                                                data-id="{{ $data->id }}" data-invoice="{{ $data->invoice_no }}"
                                                style="
                    width: 18px;
                    height: 18px;
                    cursor: pointer;
                ">

                                            <span>
                                                Release
                                            </span>

                                        </label>
                                    @endif

                                </td>

                                <td class="text-center">

                                    <a href="{{ route('export.ipl.edit', $data->id) }}" class="btn btn-sm btn-primary"
                                        title="Edit IPL">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                </td>

                                <td class="text-center">

                                    <div class="btn-group">

                                        <a href="{{ route('export.packing-list', $data->id) }}"
                                            class="btn btn-sm btn-success btn-download" title="Packing List">
                                            <i class="fa fa-download"></i>
                                            <span>PL</span>
                                        </a>

                                        <a href="{{ route('export.inv-list', $data->id) }}"
                                            class="btn btn-sm btn-warning btn-download" title="Invoice List">
                                            <i class="fa fa-download"></i>
                                            <span>IL</span>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-primary btn-custom-invoice"
                                            data-id="{{ $data->id }}" title="Custom Invoice">
                                            <i class="fa fa-file-invoice"></i>
                                            <span>CI</span>
                                        </button>
                                        <a href="{{ url('/export/ipl') }}?ref={{ urlencode($data->invoice_no) }}"
                                            class="btn btn-sm btn-info btn-download" title="Database Loberon">

                                            <i class="fa fa-database"></i>
                                            <span>LOB</span>
                                        </a>
                                        <a href="{{ route('export.si', $data->id) }}"
                                            class="btn btn-sm btn-info btn-download" title="Shipping Instruction">

                                            <i class="fa fa-file-alt"></i>
                                            <span>SI</span>
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    Tidak ada data Invoice Packing List.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        @if ($datas->hasPages())
            <div class="card-footer">
                {{ $datas->links() }}
            </div>
        @endif

    </div>

</div>


{{-- =========================================================
     CUSTOM INVOICE MODAL
========================================================= --}}

<div class="modal fade" id="customInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-xl" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h5 class="modal-title mb-1">
                        <i class="fa fa-file-invoice mr-2"></i>
                        Custom Invoice
                    </h5>

                    <small>
                        Validation & Product Allocation
                    </small>
                </div>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>


            <div class="modal-body">

                <div class="row mb-3">

                    <div class="col-md-4 mb-2">
                        <div class="ci-info-box">
                            <div class="ci-info-label">Invoice No</div>
                            <div class="ci-info-value" id="ciInvoice">-</div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="ci-info-box">
                            <div class="ci-info-label">Sales Order</div>
                            <div class="ci-info-value" id="ciSalesOrder">-</div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="ci-info-box">
                            <div class="ci-info-label">Buyer</div>
                            <div class="ci-info-value" id="ciBuyer">-</div>
                        </div>
                    </div>

                </div>


                <div class="ci-summary mb-3">

                    <div class="row">

                        <div class="col-md-3 ci-summary-item">
                            <span class="ci-summary-label">Total HS Code</span>
                            <span class="ci-summary-value" id="ciTotalProduct">0</span>
                        </div>

                        <div class="col-md-3 ci-summary-item">
                            <span class="ci-summary-label">Total PCS</span>
                            <span class="ci-summary-value" id="ciTotalPcs">0</span>
                        </div>

                        <div class="col-md-3 ci-summary-item">
                            <span class="ci-summary-label">Total BOX</span>
                            <span class="ci-summary-value" id="ciTotalBox">0</span>
                        </div>

                        <div class="col-md-3 ci-summary-item">
                            <span class="ci-summary-label">Total Value</span>
                            <span class="ci-summary-value" id="ciTotalValue">0</span>
                        </div>

                    </div>

                </div>


                <div id="ciValidationBox" class="ci-validation-box">
                    <div id="ciValidationMessage"></div>
                </div>


                <div class="ci-table-wrapper">

                    <table class="table table-bordered table-hover ci-table">

                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>HS Code</th>
                                <th>Description</th>
                                <th>Custom Description</th>
                                <th class="text-right">Qty PCS</th>
                                <th class="text-right">Qty BOX</th>
                                <th class="text-right">CI PCS</th>
                                <th class="text-right">CI BOX</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total Price</th>
                                <th class="text-right">CBM</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody id="ciProductTableBody">

                            <tr>
                                <td colspan="12" class="ci-loading">
                                    <i class="fa fa-spinner fa-spin fa-2x mb-2"></i>
                                    <br>
                                    Menunggu data...
                                </td>
                            </tr>

                        </tbody>

                        <tfoot>

                            <tr class="ci-footer-total">

                                <td colspan="4" class="text-right">
                                    TOTAL
                                </td>

                                <td class="text-right" id="ciFooterOriginalPcs">
                                    0
                                </td>

                                <td class="text-right" id="ciFooterOriginalBox">
                                    0
                                </td>

                                <td class="text-right" id="ciFooterCiPcs">
                                    0
                                </td>

                                <td class="text-right" id="ciFooterCiBox">
                                    0
                                </td>

                                <td></td>

                                <td class="text-right" id="ciFooterValue">
                                    0
                                </td>

                                <td class="text-right" id="ciFooterCbm">
                                    0
                                </td>

                                <td></td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>


            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>
                    Batal
                </button>

                <button type="button" class="btn btn-success" id="btnValidateCI">
                    <i class="fa fa-file-excel mr-1"></i>
                    Export CI
                </button>

            </div>

        </div>

    </div>

</div>

{{-- Floating HS Code hover detail popup --}}
<div id="ciHsHoverPopup">
    <div class="ci-hs-popup-title"></div>
    <div class="ci-hs-popup-body"></div>
</div>


<script>
    $(document).ready(function() {

        /*
        |--------------------------------------------------------------------------
        | GLOBAL CI DATA
        |--------------------------------------------------------------------------
        */

        let currentCustomInvoiceId = null;


        /*
        |--------------------------------------------------------------------------
        | HELPER
        |--------------------------------------------------------------------------
        */

        function numberValue(value) {
            if (value === null || value === undefined || value === '') {
                return 0;
            }

            const parsed = parseFloat(value);

            return isNaN(parsed) ? 0 : parsed;
        }


        function formatNumber(value) {
            return numberValue(value).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }


        function formatDecimal(value) {
            return numberValue(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            });
        }


        function formatMoney(value) {
            return numberValue(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }


        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return $('<div>').text(value).html();
        }




        /*
        |--------------------------------------------------------------------------
        | HS CODE HOVER / CLICK DETAIL
        |--------------------------------------------------------------------------
        | Hover  : show detail popup.
        | Click  : keep popup open. Click the same HS Code again to close.
        | Outside: close popup.
        */

        let ciHsPopupLocked = false;
        let ciHsActiveTrigger = null;

        // Simpan source items di memory JS, jangan ditaruh langsung sebagai
        // JSON di attribute HTML karena tanda kutip JSON bisa terpotong oleh HTML.
        window.ciHsSourceItems = {};

        function hideCIHsPopup() {
            ciHsPopupLocked = false;
            ciHsActiveTrigger = null;
            $('#ciHsHoverPopup').hide();
        }

        function showCIHsPopup(trigger, lockPopup = false) {
            const popup = $('#ciHsHoverPopup');
            const sourceKey =
                trigger.attr('data-source-key') || '';

            const sourceItems =
                window.ciHsSourceItems[sourceKey] || [];

            const hsCode = trigger.attr('data-hs-code') || '-';

            let rows = '';

            sourceItems.forEach(function(source) {
                const description = String(
                    source.description ?? source.article_nr ?? '-'
                ).trim() || '-';

                rows += `
                <tr>
                    <td>${escapeHtml(description)}</td>
                    <td>${formatNumber(numberValue(source.qty_pcs))} PCS</td>
                </tr>
            `;
            });

            popup.find('.ci-hs-popup-title').text(
                'Items - HS Code ' + hsCode
            );

            popup.find('.ci-hs-popup-body').html(sourceItems.length ? `
            <table>
                <thead>
                    <tr>
                        <th>Nama / Description</th>
                        <th>Qty Loaded</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        ` : `
            <div class="p-3 text-muted text-center">
                Tidak ada item.
            </div>
        `);

            popup.show();

            const rect = trigger[0].getBoundingClientRect();
            const popupEl = popup[0];
            const gap = 8;

            let left = rect.left;
            let top = rect.bottom + gap;

            const popupWidth = popupEl.offsetWidth;
            const popupHeight = popupEl.offsetHeight;

            if (left + popupWidth > window.innerWidth - 10) {
                left = window.innerWidth - popupWidth - 10;
            }
            if (left < 10) {
                left = 10;
            }
            if (top + popupHeight > window.innerHeight - 10) {
                top = rect.top - popupHeight - gap;
            }
            if (top < 10) {
                top = 10;
            }

            popup.css({
                left: left + 'px',
                top: top + 'px'
            });

            ciHsActiveTrigger = trigger[0];
            ciHsPopupLocked = lockPopup;
        }

        $(document).on(
            'mouseenter',
            '.ci-hs-hover-trigger',
            function() {
                if (!ciHsPopupLocked) {
                    showCIHsPopup($(this), false);
                }
            }
        );

        $(document).on(
            'click',
            '.ci-hs-hover-trigger',
            function(event) {
                event.preventDefault();
                event.stopPropagation();

                const trigger = $(this);

                if (
                    ciHsPopupLocked &&
                    ciHsActiveTrigger === trigger[0]
                ) {
                    hideCIHsPopup();
                    return;
                }

                showCIHsPopup(trigger, true);
            }
        );

        // Popup tidak menutup saat mouse keluar dari HS Code.
        // Popup baru ditutup ketika user klik lagi atau klik area lain.
        $(document).on(
            'click',
            '#ciHsHoverPopup',
            function(event) {
                event.stopPropagation();
            }
        );

        $(document).on(
            'click',
            function(event) {
                if (
                    ciHsPopupLocked &&
                    !$(event.target).closest(
                        '.ci-hs-hover-trigger, #ciHsHoverPopup'
                    ).length
                ) {
                    hideCIHsPopup();
                }
            }
        );

        $(window).on(
            'scroll resize',
            function() {
                if (ciHsPopupLocked) {
                    hideCIHsPopup();
                }
            }
        );

        $(document).on(
            'hidden.bs.modal',
            '#customInvoiceModal',
            function() {
                hideCIHsPopup();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | RESET MODAL
        |--------------------------------------------------------------------------
        */

        function resetCustomInvoiceModal() {
            $('#ciInvoice').text('-');
            $('#ciSalesOrder').text('-');
            $('#ciBuyer').text('-');

            $('#ciTotalProduct').text('0');
            $('#ciTotalPcs').text('0');
            $('#ciTotalBox').text('0');
            $('#ciTotalValue').text('0');

            $('#ciFooterOriginalPcs').text('0');
            $('#ciFooterOriginalBox').text('0');
            $('#ciFooterCiPcs').text('0');
            $('#ciFooterCiBox').text('0');
            $('#ciFooterValue').text('0');
            $('#ciFooterCbm').text('0');

            $('#ciValidationBox')
                .removeClass('valid invalid')
                .hide();

            $('#ciValidationMessage').html('');

            $('#btnValidateCI')
                .prop('disabled', false)
                .removeClass('ci-generate-disabled');

            $('#ciProductTableBody').html(`
            <tr>
                <td colspan="12" class="ci-loading">
                    <i class="fa fa-spinner fa-spin fa-2x mb-2"></i>
                    <br>
                    Mengambil data IPL...
                </td>
            </tr>
        `);
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN CUSTOM INVOICE
        |--------------------------------------------------------------------------
        */

        $(document).on('click', '.btn-custom-invoice', function() {

            const button = $(this);
            const id = button.data('id');

            if (!id) {
                Swal.fire('Error', 'ID IPL tidak ditemukan.', 'error');
                return;
            }

            currentCustomInvoiceId = id;

            const originalHtml = button.html();

            button
                .prop('disabled', true)
                .html(`
                <i class="fa fa-spinner fa-spin"></i>
                CI
            `);

            resetCustomInvoiceModal();

            $('#customInvoiceModal').modal('show');

            $.ajax({

                url: "{{ url('/export') }}/" +
                    id +
                    "/custom-invoice/data?_t=" +
                    Date.now(),

                type: 'GET',
                cache: false,
                dataType: 'json',

                success: function(response) {

                    if (!response || !response.success || !response.data) {

                        $('#ciProductTableBody').html(`
                        <tr>
                            <td colspan="12"
                                class="text-center text-danger py-4">

                                <i class="fa fa-exclamation-circle mr-1"></i>

                                Data IPL tidak ditemukan.

                            </td>
                        </tr>
                    `);

                        return;
                    }

                    const data = response.data;

                    $('#ciInvoice').text(data.invoice_no || '-');
                    $('#ciSalesOrder').text(data.sales_order || '-');
                    $('#ciBuyer').text(data.buyer || '-');

                    renderCustomInvoiceItems(data.items || []);

                },

                error: function(xhr) {

                    console.error('Custom Invoice Data Error:', xhr);

                    let message = 'Gagal mengambil data IPL.';

                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {
                        message = xhr.responseJSON.message;
                    }

                    $('#ciProductTableBody').html(`
                    <tr>
                        <td colspan="12"
                            class="text-center text-danger py-4">

                            <i class="fa fa-exclamation-triangle mr-1"></i>

                            ${escapeHtml(message)}

                        </td>
                    </tr>
                `);

                },

                complete: function() {

                    button
                        .prop('disabled', false)
                        .html(originalHtml);

                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | GROUP HS CODE + RENDER
        |--------------------------------------------------------------------------
        */

        window.renderCustomInvoiceItems = function(items) {
            const tbody = $('#ciProductTableBody');

            tbody.empty();

            if (!items || !items.length) {

                tbody.html(`
                <tr>
                    <td colspan="12"
                        class="text-center text-muted py-5">

                        <i class="fa fa-box-open fa-2x mb-2 d-block"></i>

                        Tidak ada item IPL.

                    </td>
                </tr>
            `);

                updateCISummary();

                return;
            }

            const grouped = {};

            items.forEach(function(item) {

                let hsCode = String(item.hs_code || '').trim();

                if (!hsCode) {
                    hsCode = 'NO HS CODE';
                }

                if (!grouped[hsCode]) {

                    grouped[hsCode] = {

                        hs_code: hsCode,

                        description: item.description || '',

                        desc_custome: String(item.desc_custome ?? '').trim(),

                        qty_pcs: 0,

                        qty_box: 0,

                        total_price: 0,

                        total_cbm: 0,

                        net_weight: 0,

                        gross_weight: 0,

                        item_ids: [],

                        source_items: []

                    };

                }

                grouped[hsCode].qty_pcs +=
                    numberValue(item.qty_pcs);

                grouped[hsCode].qty_box +=
                    numberValue(item.qty_box);

                grouped[hsCode].total_price +=
                    numberValue(item.total_price);

                grouped[hsCode].total_cbm +=
                    numberValue(item.total_cbm);

                grouped[hsCode].net_weight +=
                    numberValue(item.net_weight);

                grouped[hsCode].gross_weight +=
                    numberValue(item.gross_weight);

                if (
                    !grouped[hsCode].description &&
                    item.description
                ) {
                    grouped[hsCode].description =
                        item.description;
                }

                /*
                |--------------------------------------------------------------------------
                | CUSTOM DESCRIPTION
                |--------------------------------------------------------------------------
                | Ambil nilai pertama yang tidak kosong dari semua item dalam
                | HS Code yang sama. Jadi ID 304/306/305 tetap terbaca.
                |--------------------------------------------------------------------------
                */
                const customDescription = String(
                    item.desc_custome ?? ''
                ).trim();

                if (
                    !grouped[hsCode].desc_custome &&
                    customDescription !== ''
                ) {
                    grouped[hsCode].desc_custome =
                        customDescription;
                }

                grouped[hsCode].source_items.push(item);

                if (item.id) {
                    grouped[hsCode].item_ids.push(item.id);
                }

            });

            const groupedItems = Object.values(grouped);

            /*
            |--------------------------------------------------------------------------
            | FINAL CUSTOM DESCRIPTION FALLBACK
            |--------------------------------------------------------------------------
            */
            groupedItems.forEach(function(group) {

                if (!group.desc_custome) {

                    const found = group.source_items.find(function(source) {
                        return String(
                            source.desc_custome ?? ''
                        ).trim() !== '';
                    });

                    if (found) {
                        group.desc_custome = String(
                            found.desc_custome ?? ''
                        ).trim();
                    }
                }
            });

            // Reset source item map setiap render.
            window.ciHsSourceItems = {};

            groupedItems.forEach(function(item, index) {

                window.ciHsSourceItems['ci-hs-' + index] =
                    item.source_items || [];

                let unitPrice = 0;

                if (item.qty_pcs > 0) {
                    unitPrice =
                        item.total_price /
                        item.qty_pcs;
                }

                const row = `
                <tr
                    class="ci-product-row"
                    data-hs-code="${escapeHtml(item.hs_code)}"
                    data-item-ids='${JSON.stringify(item.item_ids)}'
                    data-qty-pcs="${item.qty_pcs}"
                    data-qty-box="${item.qty_box}"
                    data-unit-price="${unitPrice}"
                    data-total-price="${item.total_price}"
                    data-cbm="${item.total_cbm}"
                >

                    <td class="text-center">
                        ${index + 1}
                    </td>

                    <td>
                        <span
                            class="ci-hs-hover-trigger"
                            data-hs-code="${escapeHtml(item.hs_code)}"
                            data-source-key="ci-hs-${index}"
                        >
                            ${escapeHtml(item.hs_code)}
                        </span>
                    </td>

                    <td class="description-cell">
                        ${escapeHtml(item.description || '-')}
                    </td>

                    <td style="min-width:280px;">

                        <div class="d-flex align-items-center">

                            <input
                                type="text"
                                class="form-control form-control-sm ci-custom-description"
                                value="${escapeHtml(item.desc_custome || '')}"
                                placeholder="Custom description..."
                            >

                            <span
                                class="ci-desc-status text-muted"
                                title="Status save"
                            ></span>

                        </div>

                    </td>

                    <td class="ci-original-qty">
                        ${formatNumber(item.qty_pcs)}
                    </td>

                    <td class="ci-original-qty">
                        ${formatNumber(item.qty_box)}
                    </td>

                    <td>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            class="form-control form-control-sm ci-qty-input ci-pcs-input"
                            value="${item.qty_pcs}"
                        >
                    </td>

                    <td>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            class="form-control form-control-sm ci-qty-input ci-box-input"
                            value="${item.qty_box}"
                        >
                    </td>

                    <td class="text-right">
                        ${formatMoney(unitPrice)}
                    </td>

                    <td class="text-right ci-row-total-price">
                        ${formatMoney(item.total_price)}
                    </td>

                    <td class="text-right">
                        ${formatDecimal(item.total_cbm)}
                    </td>

                    <td class="text-center">

                        <span class="badge badge-success ci-status">
                            Valid
                        </span>

                    </td>

                </tr>
            `;

                tbody.append(row);

            });

            $('#ciTotalProduct')
                .text(formatNumber(groupedItems.length));

            updateCISummary();
        };


        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        function updateCISummary() {
            let originalPcs = 0;
            let originalBox = 0;
            let ciPcs = 0;
            let ciBox = 0;
            let value = 0;
            let cbm = 0;

            $('.ci-product-row').each(function() {

                const row = $(this);

                const rowOriginalPcs =
                    numberValue(row.attr('data-qty-pcs'));

                const rowOriginalBox =
                    numberValue(row.attr('data-qty-box'));

                const rowUnitPrice =
                    numberValue(row.attr('data-unit-price'));

                const rowCbm =
                    numberValue(row.attr('data-cbm'));

                const pcs =
                    numberValue(
                        row.find('.ci-pcs-input').val()
                    );

                const box =
                    numberValue(
                        row.find('.ci-box-input').val()
                    );

                originalPcs += rowOriginalPcs;
                originalBox += rowOriginalBox;

                ciPcs += pcs;
                ciBox += box;

                value += pcs * rowUnitPrice;
                cbm += rowCbm;

            });

            $('#ciFooterOriginalPcs')
                .text(formatNumber(originalPcs));

            $('#ciFooterOriginalBox')
                .text(formatNumber(originalBox));

            $('#ciFooterCiPcs')
                .text(formatNumber(ciPcs));

            $('#ciFooterCiBox')
                .text(formatNumber(ciBox));

            $('#ciFooterValue')
                .text(formatMoney(value));

            $('#ciFooterCbm')
                .text(formatDecimal(cbm));
        }


        /*
        |--------------------------------------------------------------------------
        | QTY CHANGE
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'input',
            '.ci-pcs-input, .ci-box-input',
            function() {
                const input = $(this);
                const row = input.closest('.ci-product-row');

                const originalPcs =
                    numberValue(row.attr('data-qty-pcs'));

                const originalBox =
                    numberValue(row.attr('data-qty-box'));

                let pcs =
                    numberValue(
                        row.find('.ci-pcs-input').val()
                    );

                let box =
                    numberValue(
                        row.find('.ci-box-input').val()
                    );

                if (pcs < 0) {
                    pcs = 0;
                    row.find('.ci-pcs-input').val(0);
                }

                if (box < 0) {
                    box = 0;
                    row.find('.ci-box-input').val(0);
                }

                const unitPrice =
                    numberValue(row.attr('data-unit-price'));

                const totalPrice =
                    pcs * unitPrice;

                row.find('.ci-row-total-price')
                    .text(formatMoney(totalPrice));

                const invalid =
                    pcs > originalPcs ||
                    box > originalBox;

                const status =
                    row.find('.ci-status');

                if (invalid) {

                    row
                        .removeClass('ci-valid')
                        .addClass('ci-invalid');

                    status
                        .removeClass('badge-success')
                        .addClass('badge-danger')
                        .text('Invalid');

                } else {

                    row
                        .removeClass('ci-invalid')
                        .addClass('ci-valid');

                    status
                        .removeClass('badge-danger')
                        .addClass('badge-success')
                        .text('Valid');
                }

                updateCISummary();

                $('#btnValidateCI')
                    .prop('disabled', false);

                $('#ciValidationBox')
                    .removeClass('valid invalid')
                    .hide();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | SAVE CUSTOM DESCRIPTION VIA AJAX
        |--------------------------------------------------------------------------
        |
        | Karena tabel sudah di-group berdasarkan HS Code, perubahan
        | desc_custome disimpan ke SEMUA ExportIplItem dalam IPL tersebut
        | yang mempunyai HS Code yang sama.
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'blur',
            '.ci-custom-description',
            function() {
                const input = $(this);

                const row =
                    input.closest('.ci-product-row');

                const hsCode =
                    row.attr('data-hs-code');

                const itemIds = JSON.parse(
                    row.attr('data-item-ids') || '[]'
                );

                const value =
                    input.val().trim();

                if (!currentCustomInvoiceId) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Tandai sedang save
                |--------------------------------------------------------------------------
                */

                input
                    .removeClass('ci-saved ci-save-error')
                    .addClass('ci-saving');

                const status =
                    row.find('.ci-desc-status');

                status
                    .removeClass('text-success text-danger')
                    .addClass('text-warning')
                    .html(
                        '<i class="fa fa-spinner fa-spin"></i>'
                    );


                $.ajax({

                    url: "{{ url('/export') }}/" +
                        currentCustomInvoiceId +
                        "/custom-invoice/description",

                    type: 'POST',

                    dataType: 'json',

                    data: {

                        _token: "{{ csrf_token() }}",

                        _method: 'PATCH',

                        item_ids: itemIds,

                        desc_custome: value

                    },

                    success: function(response) {

                        if (
                            response &&
                            response.success
                        ) {

                            input
                                .removeClass('ci-saving ci-save-error')
                                .addClass('ci-saved')
                                .val(response.desc_custome ?? value);


                            status
                                .removeClass('text-warning text-danger')
                                .addClass('text-success')
                                .html(
                                    '<i class="fa fa-check-circle"></i>'
                                );


                            setTimeout(function() {

                                status
                                    .fadeOut(150, function() {

                                        $(this)
                                            .removeClass(
                                                'text-success text-warning text-danger'
                                            )
                                            .show()
                                            .html('');

                                    });

                            }, 1800);

                        } else {

                            throw new Error(
                                response.message ||
                                'Gagal menyimpan Custom Description.'
                            );

                        }

                    },

                    error: function(xhr) {

                        console.error(
                            'Save Custom Description Error:',
                            xhr
                        );

                        input
                            .removeClass('ci-saving ci-saved')
                            .addClass('ci-save-error');


                        status
                            .removeClass('text-warning text-success')
                            .addClass('text-danger')
                            .html(
                                '<i class="fa fa-times-circle"></i>'
                            );


                        let message =
                            'Custom Description gagal disimpan.';


                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {
                            message =
                                xhr.responseJSON.message;
                        }


                        Swal.fire(
                            'Gagal menyimpan',
                            message,
                            'error'
                        );

                    }

                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CI
        |--------------------------------------------------------------------------
        */

        $('#btnValidateCI').on('click', function() {
            let originalPcs = 0;
            let originalBox = 0;
            let ciPcs = 0;
            let ciBox = 0;
            let invalidRows = 0;
            let errors = [];

            $('.ci-product-row').each(function() {

                const row = $(this);

                const originalQtyPcs =
                    numberValue(row.attr('data-qty-pcs'));

                const originalQtyBox =
                    numberValue(row.attr('data-qty-box'));

                const pcs =
                    numberValue(
                        row.find('.ci-pcs-input').val()
                    );

                const box =
                    numberValue(
                        row.find('.ci-box-input').val()
                    );

                originalPcs += originalQtyPcs;
                originalBox += originalQtyBox;

                ciPcs += pcs;
                ciBox += box;

                let rowInvalid = false;

                if (pcs > originalQtyPcs) {

                    rowInvalid = true;

                    errors.push(
                        'HS ' +
                        (
                            row.attr('data-hs-code') || '-'
                        ) +
                        ': CI PCS melebihi Qty PCS IPL.'
                    );
                }

                if (box > originalQtyBox) {

                    rowInvalid = true;

                    errors.push(
                        'HS ' +
                        (
                            row.attr('data-hs-code') || '-'
                        ) +
                        ': CI BOX melebihi Qty BOX IPL.'
                    );
                }

                const status =
                    row.find('.ci-status');

                if (rowInvalid) {

                    invalidRows++;

                    row
                        .removeClass('ci-valid')
                        .addClass('ci-invalid');

                    status
                        .removeClass('badge-success')
                        .addClass('badge-danger')
                        .text('Invalid');

                } else {

                    row
                        .removeClass('ci-invalid')
                        .addClass('ci-valid');

                    status
                        .removeClass('badge-danger')
                        .addClass('badge-success')
                        .text('Valid');
                }

            });

            updateCISummary();

            const tolerance = 0.0001;

            const pcsMatch =
                Math.abs(originalPcs - ciPcs) <= tolerance;

            const boxMatch =
                Math.abs(originalBox - ciBox) <= tolerance;

            if (
                invalidRows > 0 ||
                !pcsMatch ||
                !boxMatch
            ) {

                let html = '';

                if (invalidRows > 0) {

                    html += `
                    <strong>
                        Terdapat ${invalidRows} item yang tidak valid.
                    </strong>
                    <br>
                `;
                }

                if (!pcsMatch) {

                    html += `
                    <div class="mt-1">
                        <i class="fa fa-times-circle mr-1"></i>
                        Total CI PCS harus sama dengan total PCS IPL.
                        <br>
                        IPL:
                        <strong>${formatNumber(originalPcs)}</strong>
                        &nbsp; | &nbsp;
                        CI:
                        <strong>${formatNumber(ciPcs)}</strong>
                    </div>
                `;
                }

                if (!boxMatch) {

                    html += `
                    <div class="mt-1">
                        <i class="fa fa-times-circle mr-1"></i>
                        Total CI BOX harus sama dengan total BOX IPL.
                        <br>
                        IPL:
                        <strong>${formatNumber(originalBox)}</strong>
                        &nbsp; | &nbsp;
                        CI:
                        <strong>${formatNumber(ciBox)}</strong>
                    </div>
                `;
                }

                if (errors.length) {

                    html += `
                    <hr>
                    <strong>Detail:</strong>
                    <ul class="mb-0 mt-1">
                        ${errors.map(function (error) {
                            return '<li>' + escapeHtml(error) + '</li>';
                        }).join('')}
                    </ul>
                `;
                }

                $('#ciValidationBox')
                    .removeClass('valid')
                    .addClass('invalid')
                    .show();

                $('#ciValidationMessage')
                    .html(
                        '<i class="fa fa-exclamation-triangle mr-1"></i>' +
                        html
                    );

                $('#btnValidateCI')
                    .prop('disabled', false);

                return;
            }

            $('#ciValidationBox')
                .removeClass('invalid')
                .addClass('valid')
                .show();

            $('#ciValidationMessage')
                .html(`
                <i class="fa fa-check-circle mr-1"></i>
                <strong>Valid.</strong>
                Seluruh alokasi CI sesuai dengan Qty IPL.
                Total PCS dan BOX sudah balance.
            `);

            const exportUrl =
                "{{ url('/export') }}/" +
                currentCustomInvoiceId +
                "/custom-commercial";

            window.location.href = exportUrl;

            $('#btnValidateCI')
                .prop('disabled', true)
                .html(`
                <i class="fa fa-spinner fa-spin mr-1"></i>
                Exporting...
            `);

            setTimeout(function() {
                $('#btnValidateCI')
                    .prop('disabled', false)
                    .html(`
                    <i class="fa fa-file-excel mr-1"></i>
                    Export CI
                `);
            }, 2500);

            /*
            |--------------------------------------------------------------------------
            | RELEASE AJAX
            |--------------------------------------------------------------------------
            */

            $(document).on('change', '.release-checkbox', function() {

                const checkbox = $(this);

                const id = checkbox.data('id');

                const invoice = checkbox.data('invoice');

                if (!checkbox.is(':checked')) {
                    return;
                }

                Swal.fire({

                    title: 'Release IPL?',

                    html: 'Invoice ' +
                        '<strong>' +
                        escapeHtml(invoice) +
                        '</strong>' +
                        ' akan di-release.',

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonText: 'Ya, Release',

                    cancelButtonText: 'Batal',

                    reverseButtons: true

                }).then(function(result) {

                    if (!result.isConfirmed) {

                        checkbox.prop('checked', false);

                        return;
                    }

                    Swal.fire({

                        title: 'Processing...',

                        text: 'Sedang melakukan release.',

                        allowOutsideClick: false,

                        didOpen: function() {
                            Swal.showLoading();
                        }

                    });

                    checkbox.prop('disabled', true);

                    $.ajax({

                        url: "{{ url('/export') }}/" +
                            id +
                            "/release",

                        type: 'POST',

                        data: {
                            _token: "{{ csrf_token() }}"
                        },

                        success: function(response) {

                            if (response && response.success) {

                                Swal.fire({

                                    icon: 'success',

                                    title: 'Released',

                                    text: 'IPL berhasil di-release.',

                                    timer: 1500,

                                    showConfirmButton: false

                                }).then(function() {

                                    checkbox
                                        .closest('td')
                                        .html(`
                                    <span class="badge badge-success px-3 py-2">
                                        <i class="fa fa-check-circle mr-1"></i>
                                        ${escapeHtml(
                                            response.release_date || '-'
                                        )}
                                    </span>
                                `);

                                });

                            } else {

                                checkbox
                                    .prop('checked', false)
                                    .prop('disabled', false);

                                Swal.fire(
                                    'Gagal',
                                    response.message ||
                                    'Release gagal dilakukan.',
                                    'error'
                                );
                            }

                        },

                        error: function(xhr) {

                            checkbox
                                .prop('checked', false)
                                .prop('disabled', false);

                            let message =
                                'Terjadi kesalahan saat melakukan release.';

                            if (
                                xhr.responseJSON &&
                                xhr.responseJSON.message
                            ) {
                                message =
                                    xhr.responseJSON.message;
                            }

                            Swal.fire(
                                'Error',
                                message,
                                'error'
                            );
                        }

                    });

                });

            });


            /*
            |--------------------------------------------------------------------------
            | AUTO SCROLL REF
            |--------------------------------------------------------------------------
            */

            @if (request()->filled('ref'))

                setTimeout(function() {

                    const activeRow =
                        document.getElementById(
                            'active-ipl-row'
                        );

                    if (activeRow) {

                        activeRow.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                    }

                }, 500);
            @endif

        });
    });
</script>
<script>
    $(document).on('change', '.release-checkbox', function() {

        const checkbox = $(this);
        const id = checkbox.data('id');
        const invoice = checkbox.data('invoice');

        console.log('RELEASE CLICKED');
        console.log('ID:', id);
        console.log('Invoice:', invoice);

        if (!checkbox.is(':checked')) return;

        Swal.fire({
            title: 'Release IPL?',
            text: `Invoice ${invoice} akan di-release.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Release',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {

            console.log('SWAL RESULT:', result);

            if (!result.isConfirmed) {
                checkbox.prop('checked', false);
                return;
            }

            const url = "{{ url('/export') }}/" + id + "/release";

            console.log('AJAX URL:', url);

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },

                beforeSend: function() {
                    console.log('AJAX DIKIRIM');
                },

                success: function(res) {

                    console.log('AJAX SUCCESS:', res);

                    if (res.success) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });

                    } else {

                        checkbox.prop('checked', false);

                        Swal.fire(
                            'Gagal',
                            res.message || 'Release gagal.',
                            'error'
                        );
                    }
                },

                error: function(xhr) {

                    console.log('AJAX ERROR');
                    console.log('STATUS:', xhr.status);
                    console.log('RESPONSE:', xhr.responseText);

                    checkbox.prop('checked', false);

                    Swal.fire(
                        'Error ' + xhr.status,
                        xhr.responseJSON?.message ||
                        'Terjadi kesalahan.',
                        'error'
                    );
                }
            });
        });
    });
</script>
@endsection
