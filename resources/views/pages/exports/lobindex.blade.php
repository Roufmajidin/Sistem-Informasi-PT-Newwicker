@extends('master.master')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | LOBERON CIPL
    |--------------------------------------------------------------------------
    */

    // Controller lobIndex() mengirim data dengan nama $ipl dan $invoiceNo.
    // Sebelumnya Blade hanya mencari $data / $exportIpl / $invoiceData,
    // sehingga $ipl menjadi null dan tabel tidak mendapatkan item.
    $invoice = trim((string)($invoiceNo ?? $activeRef ?? ''));

    $ipl = $ipl ?? null;

    if (!$ipl && isset($data)) {
        $ipl = $data;
    }

    if (!$ipl && isset($exportIpl)) {
        $ipl = $exportIpl;
    }

    if (!$ipl && isset($invoiceData)) {
        $ipl = $invoiceData;
    }

    $items = collect();

    if ($ipl) {
        $items = collect(data_get($ipl, 'items', []));
    }

    /*
    |--------------------------------------------------------------------------
    | BLDE UNTUK PAYMENT
    |--------------------------------------------------------------------------
    | Ambil langsung dari ExportIplItem.blde pada invoice ini.
    | Hanya BLDE yang benar-benar ada di item invoice yang ditampilkan.
    |--------------------------------------------------------------------------
    */
    $bldeNumbers = $items
        ->map(function ($item) {
            return trim((string) data_get($item, 'blde', ''));
        })
        ->filter()
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | DEPOSIT DARI EXPORT AR PAYMENT
    |--------------------------------------------------------------------------
    | Controller lobIndex() mengirim $depositByPo.
    | Key  = ref_po / BLDE
    | Value = total payment_type=deposit untuk BLDE tersebut.
    |
    | Fallback collect() membuat Blade tetap aman jika controller belum
    | mengirim variable ini.
    |--------------------------------------------------------------------------
    */
    $depositByPo = collect($depositByPo ?? []);


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    $consigneeName =
        data_get($ipl, 'consignee_name')
        ?? 'Loberon GmbH';

    $consigneeAddress =
        data_get($ipl, 'consignee_address')
        ?? "Steinstraße 21\nD-90419 Nuernberg";

    $destinationName =
        data_get($ipl, 'destination_name')
        ?? 'Loberon Fulfillment GmbH';

    $destinationAddress =
        data_get($ipl, 'destination_address')
        ?? "Norisstraße 3a\nD-91257 Pegnitz";

    $eori =
        data_get($ipl, 'eori')
        ?? 'DE474271633964684';

    $incoterm =
        data_get($ipl, 'incoterm')
        ?? 'FOB';

    $portLoading =
        data_get($ipl, 'port_loading')
        ?? 'Jakarta';

    $portDischarge =
        data_get($ipl, 'port_discharge')
        ?? 'DE-Hamburg';

    $countryOrigin =
        data_get($ipl, 'country_origin')
        ?? 'Indonesia';

    $rex =
        data_get($ipl, 'rex')
        ?? 'IDREX8120216262165';

    $igstNo =
        data_get($ipl, 'igst_no')
        ?? '';

    $invoiceDate =
        data_get($ipl, 'invoice_date')
        ?? '';

    $onBoardDate =
        data_get($ipl, 'on_board_date')
        ?? '';

    $vesselName =
        data_get($ipl, 'vessel_name')
        ?? '';

    $containerNo =
        data_get($ipl, 'container_no')
        ?? '';

    $containerType =
        data_get($ipl, 'container_type')
        ?? "40' HC";
@endphp


<style>
    /* =========================================================
       PAGE
    ========================================================= */

    .lob-page {
        background: #fff;
        min-height: 100vh;
        padding: 15px;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
    }

    .lob-toolbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 8px 12px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }

    .lob-toolbar-title {
        font-size: 14px;
        font-weight: 700;
    }

    .lob-toolbar-ref {
        font-size: 11px;
        color: #666;
        margin-left: 10px;
    }


    /* =========================================================
       DOCUMENT
    ========================================================= */

    .lob-document {
        width: 100%;
        max-width: 1900px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #ddd;
        padding: 24px 25px 35px;
    }


    /* =========================================================
       COMPANY
    ========================================================= */

    .lob-company {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .lob-address {
        font-size: 10px;
        line-height: 1.35;
    }

    .lob-title {
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        margin: 20px 0;
    }


    /* =========================================================
       HEADER INFORMATION
    ========================================================= */

    .lob-info {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 12px;
    }

    .lob-info td {
        border: 0;
        padding: 3px 5px;
        vertical-align: top;
        font-size: 10px;
    }

    .lob-info-left {
        width: 50%;
        padding-right: 20px !important;
    }

    .lob-info-right {
        width: 50%;
        padding-left: 20px !important;
    }

    .lob-info-label {
        width: 115px;
        font-weight: 700;
        white-space: nowrap;
    }

    .lob-input {
        width: 100%;
        border: 0;
        border-bottom: 1px dotted #999;
        outline: none;
        background: #fff;
        color: #000;
        font-size: 10px;
        padding: 1px 2px;
    }

    .lob-input:focus {
        background: #fffbea;
    }

    .lob-textarea {
        width: 100%;
        min-height: 32px;
        border: 0;
        border-bottom: 1px dotted #999;
        outline: none;
        resize: vertical;
        background: #fff;
        font-size: 10px;
        color: #000;
        padding: 1px 2px;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .lob-table-wrapper {
        width: 100%;
        position: relative;
    }


    /*
     * HEADER CONTAINER
     */
    .lob-header-scroll {
        width: 100%;
        overflow: hidden;
        background: #fff;
    }


    /*
     * TABLE HEADER
     */
    .lob-header-table {
        width: 2050px;
        min-width: 2050px;
        table-layout: fixed;
        border-collapse: collapse;
        background: #fff;
        font-size: 9px;
        transform: translateX(0);
        will-change: transform;
    }

    .lob-header-table th {
        border: 1px solid #222;
        background: #fff !important;
        color: #000 !important;
        text-align: center;
        vertical-align: middle;
        font-weight: 700;
        line-height: 1.1;
        padding: 4px 3px;
        height: 34px;
    }

    .lob-header-table tr:nth-child(2) th {
        height: 22px;
    }


    /*
     * SCROLLBAR
     *
     * Tepat di bawah TH
     */
    .lob-scrollbar {
        width: 100%;
        height: 17px;
        overflow-x: auto;
        overflow-y: hidden;
        background: #fff;
        border-left: 1px solid #222;
        border-right: 1px solid #222;
        border-bottom: 1px solid #222;
    }

    .lob-scrollbar-inner {
        width: 2050px;
        min-width: 2050px;
        height: 1px;
    }

    .lob-scrollbar::-webkit-scrollbar {
        height: 13px;
    }

    .lob-scrollbar::-webkit-scrollbar-track {
        background: #fff;
    }

    .lob-scrollbar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 8px;
    }

    .lob-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }


    /*
     * BODY
     */
    .lob-body {
        width: 100%;
        overflow: hidden;
        background: #fff;
    }

    .lob-body-table {
        width: 2050px;
        min-width: 2050px;
        table-layout: fixed;
        border-collapse: collapse;
        background: #fff;
        font-size: 9px;
        transform: translateX(0);
        will-change: transform;
    }

    .lob-body-table td {
        border: 1px solid #222;
        background: #fff !important;
        color: #000;
        padding: 3px;
        vertical-align: middle;
        height: 34px;
    }

    .lob-body-table input,
    .lob-body-table textarea {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        color: #000;
        font-size: 9px;
        padding: 1px;
    }

    .lob-body-table textarea {
        resize: vertical;
        min-height: 25px;
    }

    .lob-body-table input:focus,
    .lob-body-table textarea:focus {
        background: #fffbea;
    }


    /* =========================================================
       TOTAL
    ========================================================= */

    .lob-total-row td {
        font-weight: 700;
        background: #fff !important;
        height: 30px;
    }

    .lob-center {
        text-align: center !important;
    }

    .lob-right {
        text-align: right !important;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .lob-footer {
        margin-top: 25px;
        font-size: 10px;
    }

    .lob-footer-grid {
        display: grid;
        grid-template-columns: 32% 28% 40%;
        gap: 20px;
    }

    .lob-bank {
        white-space: pre-line;
        line-height: 1.45;
    }

    .lob-footer-title {
        font-weight: 700;
        margin-bottom: 6px;
    }

    .lob-payment-table {
        width: 100%;
        border-collapse: collapse;
    }

    .lob-payment-table td {
        padding: 3px 4px;
        vertical-align: middle;
    }

    .lob-payment-label {
        width: 70%;
    }

    .lob-payment-value {
        width: 30%;
        text-align: right;
    }

    .lob-note {
        margin-top: 25px;
        font-size: 9px;
        font-weight: 600;
    }

    .lob-signature {
        margin-top: 25px;
        min-height: 50px;
        font-size: 10px;
    }


    /* =========================================================
       EDIT MODE
    ========================================================= */

    .lob-edit-control {
        margin-left: 4px;
    }

    .lob-page.lob-editing .lob-input,
    .lob-page.lob-editing .lob-body-table input,
    .lob-page.lob-editing .lob-body-table textarea {
        background: #fffbea !important;
        border-bottom-color: #f0ad4e;
    }

    .lob-page:not(.lob-editing) .lob-body-table input,
    .lob-page:not(.lob-editing) .lob-body-table textarea {
        pointer-events: none;
    }

    .lob-page:not(.lob-editing) .lob-input {
        pointer-events: none;
    }

    .lob-payment-row-new td {
        padding: 3px 4px;
    }

    .lob-payment-row-new select,
    .lob-payment-row-new input {
        width: 100%;
        border: 0;
        border-bottom: 1px dotted #999;
        outline: none;
        font-size: 10px;
        background: #fffbea;
    }

    .lob-payment-remove {
        border: 0;
        background: transparent;
        color: #dc3545;
        cursor: pointer;
    }

    /* =========================================================
       PRINT
    ========================================================= */

    @media print {

        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .lob-page {
            padding: 0 !important;
            background: #fff !important;
        }

        .lob-toolbar,
        .no-print {
            display: none !important;
        }

        .lob-document {
            max-width: none;
            border: 0;
            padding: 0;
        }

        .lob-scrollbar {
            display: none !important;
        }

        .lob-header-scroll,
        .lob-body {
            overflow: visible !important;
        }

        .lob-header-table,
        .lob-body-table {
            width: 100%;
            min-width: 0;
            transform: none !important;
        }

        .lob-header-table,
        .lob-body-table {
            font-size: 6.5px;
        }

        .lob-header-table th,
        .lob-body-table td {
            padding: 2px;
        }

        .lob-body-table input,
        .lob-body-table textarea {
            font-size: 6.5px;
        }

        .lob-footer {
            page-break-inside: avoid;
        }
    }
</style>


<div class="lob-page">

    {{-- =========================================================
         TOOLBAR
    ========================================================== --}}
    <div class="lob-toolbar no-print">

        <div class="d-flex align-items-center justify-content-between">

            <div>

                <span class="lob-toolbar-title">
                    <i class="fa fa-file-invoice"></i>
                    Loberon CIPL
                </span>

                @if($invoice)
                    <span class="lob-toolbar-ref">
                        Ref:
                        <strong>{{ $invoice }}</strong>
                    </span>
                @endif

            </div>

            <div>

                <button type="button"
                        id="btnLobEdit"
                        class="btn btn-sm btn-warning">
                    <i class="fa fa-edit"></i>
                    Edit
                </button>

                <button type="button"
                        id="btnLobAddPayment"
                        class="btn btn-sm btn-success lob-edit-control"
                        style="display:none;">
                    <i class="fa fa-plus"></i>
                    Payment
                </button>

                <button type="button"
                        id="btnLobSave"
                        class="btn btn-sm btn-primary lob-edit-control"
                        style="display:none;">
                    <i class="fa fa-save"></i>
                    Update
                </button>

                <button type="button"
                        id="btnLobCancel"
                        class="btn btn-sm btn-secondary lob-edit-control"
                        style="display:none;">
                    <i class="fa fa-times"></i>
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-sm btn-primary"
                        onclick="window.print()">
                    <i class="fa fa-print"></i>
                    Print
                </button>

                <a href="{{ route('export.loberon.downloadCipl', $ipl->id) }}"
                   class="btn btn-sm btn-success no-print">
                    <i class="fa fa-file-excel"></i>
                    Download CIPL
                </a>

                <a href="{{ url('/export/ipl') }}"
                   class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i>
                    Back
                </a>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DOCUMENT
    ========================================================== --}}
    <div class="lob-document">


        {{-- COMPANY --}}
        <div class="lob-company">
            PT. NEWWICKER INDONESIA
        </div>

        <div class="lob-address">
            JL. KISABA LANANG RT. 019 RW. 002,
            BODELOR, PLUMBON, CIREBON 45155 INDONESIA
        </div>


        {{-- TITLE --}}
        <div class="lob-title">
            COMMERCIAL INVOICE CUM PACKING LIST
        </div>


        {{-- =====================================================
             HEADER INFORMATION
        ====================================================== --}}
        <table class="lob-info">

            <tr>

                {{-- LEFT --}}
                <td class="lob-info-left">

                    <table class="lob-info">

                        <tr>

                            <td class="lob-info-label">
                                Consignee:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="consignee_name"
                                       value="{{ $consigneeName }}">
                            </td>

                        </tr>

                        <tr>

                            <td></td>

                            <td>
                                <textarea class="lob-textarea"
                                          name="consignee_address">{{ $consigneeAddress }}</textarea>
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Final destination:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="destination_name"
                                       value="{{ $destinationName }}">
                            </td>

                        </tr>

                        <tr>

                            <td></td>

                            <td>
                                <textarea class="lob-textarea"
                                          name="destination_address">{{ $destinationAddress }}</textarea>
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                EORI:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="eori"
                                       value="{{ $eori }}">
                            </td>

                        </tr>

                    </table>

                </td>


                {{-- RIGHT --}}
                <td class="lob-info-right">

                    <table class="lob-info">

                        <tr>

                            <td class="lob-info-label">
                                Incoterm:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="incoterm"
                                       value="{{ $incoterm }}">
                            </td>

                            <td class="lob-info-label">
                                Country of Origin:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="country_origin"
                                       value="{{ $countryOrigin }}">
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Port of loading:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="port_loading"
                                       value="{{ $portLoading }}">
                            </td>

                            <td class="lob-info-label">
                                REX:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="rex"
                                       value="{{ $rex }}">
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                port of discharge:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="port_discharge"
                                       value="{{ $portDischarge }}">
                            </td>

                            <td class="lob-info-label">
                                IGST No.:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="igst_no"
                                       value="{{ $igstNo }}">
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Invoice No:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="invoice_no"
                                       value="{{ $invoice }}">
                            </td>

                            <td class="lob-info-label">
                                Date of invoice:
                            </td>

                            <td>
                                <input type="date"
                                       class="lob-input"
                                       name="invoice_date"
                                       value="{{ $invoiceDate }}">
                            </td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                On board date:
                            </td>

                            <td>
                                <input type="date"
                                       class="lob-input"
                                       name="on_board_date"
                                       value="{{ $onBoardDate }}">
                            </td>

                            <td></td>
                            <td></td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Vessel Name:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="vessel_name"
                                       value="{{ $vesselName }}">
                            </td>

                            <td></td>
                            <td></td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Container No.
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="container_no"
                                       value="{{ $containerNo }}">
                            </td>

                            <td></td>
                            <td></td>

                        </tr>

                        <tr>

                            <td class="lob-info-label">
                                Container Type:
                            </td>

                            <td>
                                <input type="text"
                                       class="lob-input"
                                       name="container_type"
                                       value="{{ $containerType }}">
                            </td>

                            <td></td>
                            <td></td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>


        {{-- =====================================================
             ITEM TABLE
        ====================================================== --}}
        <div class="lob-table-wrapper">


            {{-- =================================================
                 HEADER
            ================================================== --}}
            <div class="lob-header-scroll"
                 id="lobHeaderScroll">

                <table class="lob-header-table">

                    <colgroup>

                        <col style="width:35px">
                        <col style="width:90px">
                        <col style="width:105px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:125px">
                        <col style="width:120px">
                        <col style="width:180px">
                        <col style="width:80px">
                        <col style="width:90px">
                        <col style="width:75px">
                        <col style="width:75px">
                        <col style="width:80px">

                        <col style="width:65px">
                        <col style="width:65px">
                        <col style="width:65px">

                        <col style="width:90px">
                        <col style="width:100px">

                        <col style="width:60px">
                        <col style="width:60px">
                        <col style="width:60px">

                        <col style="width:95px">
                        <col style="width:95px">

                        <col style="width:65px">
                        <col style="width:100px">
                        <col style="width:100px">

                    </colgroup>


                    <thead>

                        <tr>

                            <th rowspan="2">#</th>

                            <th rowspan="2">
                                PO NUMBER
                            </th>

                            <th rowspan="2">
                                Loberon<br>
                                article code
                            </th>

                            <th rowspan="2">
                                Color ID
                            </th>

                            <th rowspan="2">
                                Size ID
                            </th>

                            <th rowspan="2">
                                EAN CODE
                            </th>

                            <th rowspan="2">
                                EUDR DDS CODE
                            </th>

                            <th rowspan="2">
                                Article description
                            </th>

                            <th rowspan="2">
                                HTS CODE
                            </th>

                            <th rowspan="2">
                                BULKY<br>
                                GOODS CLASS
                            </th>

                            <th rowspan="2">
                                NET WT / CRT
                            </th>

                            <th rowspan="2">
                                GROSS WT / CRT
                            </th>

                            <th rowspan="2">
                                Total Number<br>
                                Cartons
                            </th>

                            <th colspan="3">
                                Marks and number
                            </th>

                            <th rowspan="2">
                                TOTAL NET WT
                            </th>

                            <th rowspan="2">
                                TOTAL GROSS WT
                            </th>

                            <th colspan="3">
                                Carton Size (cm)
                            </th>

                            <th rowspan="2">
                                Volume / Carton
                            </th>

                            <th rowspan="2">
                                Total Volume
                            </th>

                            <th rowspan="2">
                                QTY
                            </th>

                            <th rowspan="2">
                                Price per article<br>
                                (USD)
                            </th>

                            <th rowspan="2">
                                AMOUNT<br>
                                (USD)
                            </th>

                        </tr>

                        <tr>

                            <th></th>
                            <th></th>
                            <th></th>

                            <th>L</th>
                            <th>W</th>
                            <th>H</th>

                        </tr>

                    </thead>

                </table>

            </div>


            {{-- =================================================
                 SCROLLBAR TEPAT DI BAWAH TH
            ================================================== --}}
            <div class="lob-scrollbar"
                 id="lobScrollbar">

                <div class="lob-scrollbar-inner"
                     id="lobScrollbarInner"></div>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}
            <div class="lob-body"
                 id="lobBody">

                <table class="lob-body-table"
                       id="lobBodyTable">

                    <colgroup>

                        <col style="width:35px">
                        <col style="width:90px">
                        <col style="width:105px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:125px">
                        <col style="width:120px">
                        <col style="width:180px">
                        <col style="width:80px">
                        <col style="width:90px">
                        <col style="width:75px">
                        <col style="width:75px">
                        <col style="width:80px">

                        <col style="width:65px">
                        <col style="width:65px">
                        <col style="width:65px">

                        <col style="width:90px">
                        <col style="width:100px">

                        <col style="width:60px">
                        <col style="width:60px">
                        <col style="width:60px">

                        <col style="width:95px">
                        <col style="width:95px">

                        <col style="width:65px">
                        <col style="width:100px">
                        <col style="width:100px">

                    </colgroup>


                    <tbody id="lobItemsBody">

                        @forelse($items as $index => $item)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | DATA SOURCE LOBERON
                                |--------------------------------------------------------------------------
                                | Controller sudah menempelkan:
                                | - $item->loberon     = master Loberon berdasarkan article_code
                                | - $item->detail_data = detail PO
                                | - $item                  = data ExportIplItem
                                |--------------------------------------------------------------------------
                                */

                                $loberon = data_get($item, 'loberon');
                                $detail = data_get($item, 'detail_data', []);

                                if (!is_array($detail)) {
                                    $detail = [];
                                }

                                // PO NUMBER langsung menggunakan BLDE
                                // yang sudah tersimpan pada ExportIplItem.
                                $poNumber =
                                    data_get($item, 'blde')
                                    ?? data_get($item, 'po_no')
                                    ?? '';

                                $articleCode =
                                    data_get($loberon, 'article_code')
                                    ?? data_get($item, 'article_nr')
                                    ?? '';

                                $colorId =
                                    data_get($loberon, 'color_id')
                                    ?? '';

                                $sizeId =
                                    data_get($loberon, 'size_id')
                                    ?? '';

                                $ean =
                                    data_get($loberon, 'ean_code')
                                    ?? '';

                                $eudr =
                                    data_get($loberon, 'eudr_dds_code')
                                    ?? '';

                                $description =
                                    data_get($loberon, 'description')
                                    ?? data_get($item, 'description')
                                    ?? data_get($item, 'desc_custome')
                                    ?? data_get($detail, 'description')
                                    ?? '';

                                $hts =
                                    data_get($loberon, 'hts_code')
                                    ?? data_get($item, 'hs_code')
                                    ?? '';

                                $goodsClass =
                                    data_get($loberon, 'bulky_goods_class')
                                    ?? 'Normal Item';

                                $net =
                                    data_get($item, 'net_weight')
                                    ?? data_get($detail, 'net_weight')
                                    ?? 0;

                                $gross =
                                    data_get($item, 'gross_weight')
                                    ?? data_get($detail, 'gross_weight')
                                    ?? 0;

                                $cartons =
                                    data_get($item, 'qty_box')
                                    ?? 0;

                                $cartonL =
                                    data_get($detail, 'pack_w')
                                    ?? '';

                                $cartonW =
                                    data_get($detail, 'pack_d')
                                    ?? '';

                                $cartonH =
                                    data_get($detail, 'pack_h')
                                    ?? '';

                                $qty =
                                    data_get($item, 'qty_pcs')
                                    ?? 0;

                                $price =
                                    data_get($item, 'unit_price')
                                    ?? 0;

                                $totalNet =
                                    is_numeric($net) && is_numeric($cartons)
                                        ? (float) $net * (float) $cartons
                                        : 0;

                                $totalGross =
                                    is_numeric($gross) && is_numeric($cartons)
                                        ? (float) $gross * (float) $cartons
                                        : 0;

                                // Gunakan CBM yang tersimpan di ExportIplItem.
                                // Controller/Detail PO sudah menentukan source CBM-nya.
                                $volume =
                                    is_numeric(data_get($item, 'cbm'))
                                        ? (float) data_get($item, 'cbm')
                                        : 0;

                                $totalVolume =
                                    is_numeric(data_get($item, 'total_cbm'))
                                        ? (float) data_get($item, 'total_cbm')
                                        : ($volume * (float) $cartons);

                                $amount =
                                    is_numeric($qty) && is_numeric($price)
                                        ? (float) $qty * (float) $price
                                        : 0;

                            @endphp


                            <tr class="lob-item-row" data-item-id="{{ data_get($item, 'id') }}">

                                <td class="lob-center row-number">
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][po_number]"
                                           value="{{ $poNumber }}">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][article_code]"
                                           value="{{ $articleCode }}">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][color_id]"
                                           value="{{ $colorId }}"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][size_id]"
                                           value="{{ $sizeId }}"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][ean_code]"
                                           value="{{ $ean }}">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][eudr_dds_code]"
                                           value="{{ $eudr }}">
                                </td>

                                <td>
                                    <textarea name="items[{{ $index }}][description]"
                                              rows="2">{{ $description }}</textarea>
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][hts_code]"
                                           value="{{ $hts }}"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][bulky_goods_class]"
                                           value="{{ $goodsClass }}">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[{{ $index }}][net_weight]"
                                           value="{{ $net }}"
                                           class="weight-net lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[{{ $index }}][gross_weight]"
                                           value="{{ $gross }}"
                                           class="weight-gross lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           name="items[{{ $index }}][qty_carton]"
                                           value="{{ $cartons }}"
                                           class="qty-carton lob-right">
                                </td>

                                {{-- MARKS AND NUMBER = 3 COLUMN --}}
                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][marks_1]"
                                           value="{{ data_get($item, 'marks_1', '') }}">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][marks_2]"
                                           value="{{ data_get($item, 'marks_2', '') }}">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[{{ $index }}][marks_3]"
                                           value="{{ data_get($item, 'marks_3', '') }}">
                                </td>

                                <td class="lob-right total-net">
                                    {{ number_format($totalNet, 2, '.', '') }}
                                </td>

                                <td class="lob-right total-gross">
                                    {{ number_format($totalGross, 2, '.', '') }}
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[{{ $index }}][carton_l]"
                                           value="{{ $cartonL }}"
                                           class="carton-l lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[{{ $index }}][carton_w]"
                                           value="{{ $cartonW }}"
                                           class="carton-w lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[{{ $index }}][carton_h]"
                                           value="{{ $cartonH }}"
                                           class="carton-h lob-right">
                                </td>

                                <td class="lob-right volume-carton">
                                    {{ number_format($volume, 3, '.', ',') }}
                                </td>

                                <td class="lob-right total-volume">
                                    {{ number_format($totalVolume, 3, '.', ',') }}
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           name="items[{{ $index }}][qty]"
                                           value="{{ $qty }}"
                                           class="item-qty lob-right">
                                </td>

                                <td>
                                    <input type="text"
                                           inputmode="decimal"
                                           name="items[{{ $index }}][price]"
                                           value="{{ is_numeric($price) ? number_format((float) $price, 2, '.', ',') : '' }}"
                                           class="item-price lob-right">
                                </td>

                                <td class="lob-right total-price">
                                    {{ number_format($amount, 2, '.', ',') }}
                                </td>

                            </tr>

                        @empty

                            <tr class="lob-item-row" data-item-id="">

                                <td class="lob-center row-number">
                                    1
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][po_number]">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][article_code]">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][color_id]"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][size_id]"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][ean_code]">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][eudr_dds_code]">
                                </td>

                                <td>
                                    <textarea name="items[0][description]"
                                              rows="2"></textarea>
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][hts_code]"
                                           class="lob-center">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][bulky_goods_class]"
                                           value="Normal Item">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[0][net_weight]"
                                           class="weight-net lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[0][gross_weight]"
                                           class="weight-gross lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           name="items[0][qty_carton]"
                                           value="0"
                                           class="qty-carton lob-right">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][marks_1]">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][marks_2]">
                                </td>

                                <td>
                                    <input type="text"
                                           name="items[0][marks_3]">
                                </td>

                                <td class="lob-right total-net">
                                    0.00
                                </td>

                                <td class="lob-right total-gross">
                                    0.00
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[0][carton_l]"
                                           class="carton-l lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[0][carton_w]"
                                           class="carton-w lob-right">
                                </td>

                                <td>
                                    <input type="number"
                                           step="0.01"
                                           name="items[0][carton_h]"
                                           class="carton-h lob-right">
                                </td>

                                <td class="lob-right volume-carton">
                                    0.000
                                </td>

                                <td class="lob-right total-volume">
                                    0.000
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           name="items[0][qty]"
                                           value="0"
                                           class="item-qty lob-right">
                                </td>

                                <td>
                                    <input type="text"
                                           inputmode="decimal"
                                           name="items[0][price]"
                                           class="item-price lob-right">
                                </td>

                                <td class="lob-right total-price">
                                    0.00
                                </td>

                            </tr>

                        @endforelse

                    </tbody>


                    {{-- =================================================
                         TOTAL
                    ================================================== --}}
                    <tfoot>

                        <tr class="lob-total-row">

                            <td colspan="12"></td>

                            <td id="totalCartons"
                                class="lob-right">
                                0
                            </td>

                            <td colspan="3"></td>

                            <td id="totalNetWeight"
                                class="lob-right">
                                0.00
                            </td>

                            <td id="totalGrossWeight"
                                class="lob-right">
                                0.00
                            </td>

                            <td colspan="3"></td>

                            <td></td>

                            <td id="totalVolume"
                                class="lob-right">
                                0.000
                            </td>

                            <td id="totalQty"
                                class="lob-right">
                                0
                            </td>

                            <td></td>

                            <td id="grandTotal"
                                class="lob-right">
                                0.00
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>


        {{-- =========================================================
             FOOTER
        ========================================================== --}}
        <div class="lob-footer">

            <div class="lob-footer-grid">


                {{-- BANK --}}
                <div class="lob-bank">

                    <div class="lob-footer-title">
                        Bankdetails:
                    </div>

Bank Name : MANDIRI BANK
Branch : 36-38 JL.GATOT SUBROTO Indonesia
Account Name : NEWWICKER Indonesia
Account Number : 134-001-110-1788
Swift Code : BMRIIDJAXXX

                </div>


                {{-- VALUE IN WORDS --}}
                <div>

                    <div class="lob-footer-title">
                        Value in words
                    </div>

                    <textarea class="lob-textarea"
                              rows="5"
                              name="value_in_words"></textarea>

                </div>


                {{-- PAYMENT --}}
                <div>

                    <div class="lob-footer-title">
                        Payment Information for Accounting:
                    </div>

                    <table class="lob-payment-table">

                        <tr>
                            <td class="lob-payment-label">
                                Final Invoice amount:
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="finalInvoiceAmount"
                                       value="0">

                            </td>
                        </tr>

                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | PAYMENT SOURCE
                            |--------------------------------------------------------------------------
                            | Payment record tetap disimpan di DOM secara hidden agar JS
                            | bisa mengambil ID/detail payment existing.
                            |
                            | Yang ditampilkan ke user hanya:
                            |   1. satu baris Deposit per BLDE
                            |   2. satu baris total Surcharge 1/2/3
                            |
                            | Jadi payment record tidak akan menumpuk di index.
                            |--------------------------------------------------------------------------
                            */
                            $lobPayments = collect($lobPayments ?? []);

                            /*
                             * Normalize BLDE / ref_po agar karakter invisible tidak
                             * membuat payment terlihat seperti payment yang berbeda.
                             */
                            $normalizeRefPoBlade = function ($value) {
                                $value = preg_replace(
                                    '/[\x{00AD}\x{200B}-\x{200D}\x{FEFF}]/u',
                                    '',
                                    (string) $value
                                );

                                $value = preg_replace('/\s+/u', ' ', $value);

                                return trim($value);
                            };

                            /*
                             * Deposit total per BLDE.
                             */
                            $depositByPo = [];
                            $depositMetaByPo = [];

                            foreach ($lobPayments as $payment) {

                                if (
                                    strtolower((string) data_get($payment, 'payment_type'))
                                    !== 'deposit'
                                ) {
                                    continue;
                                }

                                $refPo = $normalizeRefPoBlade(
                                    data_get($payment, 'ref_po', '')
                                );

                                if ($refPo === '') {
                                    continue;
                                }

                                $depositByPo[$refPo] =
                                    ($depositByPo[$refPo] ?? 0)
                                    + (float) data_get($payment, 'amount', 0);

                                /*
                                 * Ambil payment pertama sebagai metadata row.
                                 * ID ini dipakai supaya existing payment tetap di-update,
                                 * bukan dibuat ulang setiap kali Save.
                                 */
                                if (!isset($depositMetaByPo[$refPo])) {

                                    $depositMetaByPo[$refPo] = [
                                        'id' => data_get($payment, 'id'),
                                        'payment_date' => data_get($payment, 'payment_date')
                                            ? \Carbon\Carbon::parse(
                                                data_get($payment, 'payment_date')
                                            )->format('Y-m-d')
                                            : '',
                                        'reference' => data_get($payment, 'reference', ''),
                                        'keterangan' => data_get($payment, 'keterangan', ''),
                                    ];
                                }
                            }

                            /*
                             * Surcharge total per slot.
                             */
                            $surchargeTotals = [
    1 => 0,
    2 => 0,
    3 => 0,
];

foreach ($lobPayments as $payment) {

    if (
        strtolower(
            (string) data_get(
                $payment,
                'payment_type',
                ''
            )
        ) !== 'surcharge'
    ) {
        continue;
    }

    $amount = (float) data_get(
        $payment,
        'amount',
        0
    );

    $keterangan = trim(
        (string) data_get(
            $payment,
            'keterangan',
            ''
        )
    );

    $slot = null;

    if (
        preg_match(
            '/surcharge\s*([123])/i',
            $keterangan,
            $match
        )
    ) {
        $slot = (int) $match[1];
    }

    // Data surcharge lama tanpa marker dianggap Surcharge 1.
    if (
        $slot === null
        && $amount > 0
    ) {
        $slot = 1;
    }

    if (
        $slot !== null
        && isset($surchargeTotals[$slot])
    ) {
        $surchargeTotals[$slot] += $amount;
    }
}

@endphp

                        {{-- =====================================================
                             DEPOSIT
                             SATU BARIS PER BLDE
                        ====================================================== --}}

                        @foreach ($bldeNumbers as $blde)

                            @php
                                $blde = $normalizeRefPoBlade($blde);

                                $depositId =
                                    'deposit_' .
                                    preg_replace(
                                        '/[^A-Za-z0-9_-]/',
                                        '_',
                                        $blde
                                    );

                                $depositAmount =
                                    (float) ($depositByPo[$blde] ?? 0);

                                $depositMeta =
                                    $depositMetaByPo[$blde] ?? [];

                                $depositPaymentId =
                                    $depositMeta['id'] ?? '';

                                $depositPaymentDate =
                                    $depositMeta['payment_date'] ?? '';

                                $depositPaymentReference =
                                    $depositMeta['reference'] ?? '';

                                $depositPaymentKeterangan =
                                    $depositMeta['keterangan'] ?? '';
                            @endphp

                            <tr
                                class="deposit-row"
                                data-blde="{{ $blde }}"
                                data-payment-id="{{ $depositPaymentId }}"
                                data-payment-date="{{ $depositPaymentDate }}"
                                data-payment-reference="{{ $depositPaymentReference }}"
                                data-payment-keterangan="{{ $depositPaymentKeterangan }}"
                                title="Klik untuk mengisi detail deposit"
                            >

                                <td class="lob-payment-label">
                                    ./. Deposit {{ $blde }}
                                </td>

                                <td class="lob-payment-value">

                                    <input
                                        type="number"
                                        step="0.01"
                                        class="lob-input deposit-value"
                                        id="{{ $depositId }}"
                                        value="{{ number_format($depositAmount, 2, '.', '') }}"
                                        readonly
                                    >

                                </td>

                            </tr>

                        @endforeach

                        {{-- =====================================================
                             HIDDEN EXISTING PAYMENT RECORDS

                             Jangan dihapus.
                             Dipakai JavaScript untuk mengambil:
                             - ID payment
                             - tanggal
                             - reference
                             - keterangan
                             - surcharge per BLDE

                             Tidak terlihat di index sehingga tidak menumpuk.
                        ====================================================== --}}

                        @foreach ($lobPayments as $payment)

                            @php
                                $paymentId =
                                    data_get($payment, 'id');

                                $paymentType =
                                    strtolower(
                                        (string) data_get(
                                            $payment,
                                            'payment_type',
                                            ''
                                        )
                                    );

                                $refPo =
                                    $normalizeRefPoBlade(
                                        data_get(
                                            $payment,
                                            'ref_po',
                                            ''
                                        )
                                    );

                                $paymentDate =
                                    data_get($payment, 'payment_date')
                                        ? \Carbon\Carbon::parse(
                                            data_get(
                                                $payment,
                                                'payment_date'
                                            )
                                        )->format('Y-m-d')
                                        : '';

                                $paymentAmount =
                                    (float) data_get(
                                        $payment,
                                        'amount',
                                        0
                                    );

                                $reference =
                                    data_get(
                                        $payment,
                                        'reference',
                                        ''
                                    );

                                $keterangan =
                                    data_get(
                                        $payment,
                                        'keterangan',
                                        ''
                                    );

                                $surchargeSlot = '';

                                if (
                                    $paymentType === 'surcharge'
                                    && preg_match(
                                        '/surcharge\s*([123])/i',
                                        $keterangan,
                                        $m
                                    )
                                ) {
                                    $surchargeSlot = (int) $m[1];
                                }
                            @endphp

                            <tr
                                class="payment-existing-row"
                                data-payment-id="{{ $paymentId }}"
                                data-payment-type="{{ $paymentType }}"
                                data-surcharge-slot="{{ $surchargeSlot }}"
                                style="display:none;"
                            >

                                <td>

                                    <input
                                        type="hidden"
                                        class="payment-type"
                                        value="{{ $paymentType }}"
                                    >

                                    <input
                                        type="hidden"
                                        class="payment-ref-po"
                                        value="{{ $refPo }}"
                                    >

                                    <input
                                        type="hidden"
                                        class="payment-date"
                                        value="{{ $paymentDate }}"
                                    >

                                    <input
                                        type="hidden"
                                        class="payment-reference"
                                        value="{{ $reference }}"
                                    >

                                    <input
                                        type="hidden"
                                        class="payment-keterangan"
                                        value="{{ $keterangan }}"
                                    >

                                    <input
                                        type="hidden"
                                        class="payment-amount"
                                        value="{{ number_format($paymentAmount, 2, '.', '') }}"
                                    >

                                </td>

                            </tr>

                        @endforeach

                        <tbody id="lobNewPaymentsBody"></tbody>

                        <tr>
                            <td>
                                <strong>
                                    Final Amount to be paid:
                                </strong>
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="finalAmountPaid"
                                       value="0">

                            </td>
                        </tr>

                        <tr>
                            <td>
                                Payment Terms:
                            </td>

                            <td>

                                <input type="text"
                                       class="lob-input"
                                       name="payment_terms">

                            </td>
                        </tr>

                    </table>

                </div>

            </div>


            {{-- =====================================================
                 ORDER VALUE CALCULATION
            ====================================================== --}}
            <div style="margin-top:18px;">

                <table class="lob-payment-table"
                       style="max-width:650px;">

                    <tr>

                        <td>
                            Trade Discount 8%
                        </td>

                        <td class="lob-payment-value">

                            <input type="number"
                                   step="0.01"
                                   id="tradeDiscount"
                                   class="lob-input"
                                   value="0">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            surcharge 1:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="surcharge1"
                                   class="lob-input surcharge-trigger"
                                   value="{{ number_format((float) ($surchargeTotals[1] ?? 0), 2, '.', '') }}"
                                   readonly
                                   title="Klik untuk mengatur Surcharge 1 per BLDE">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            surcharge 2:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="surcharge2"
                                   class="lob-input surcharge-trigger"
                                   value="{{ number_format((float) ($surchargeTotals[2] ?? 0), 2, '.', '') }}"
                                   readonly
                                   title="Klik untuk mengatur Surcharge 2 per BLDE">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            surcharge 3:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="surcharge3"
                                   class="lob-input surcharge-trigger"
                                   value="{{ number_format((float) ($surchargeTotals[3] ?? 0), 2, '.', '') }}"
                                   readonly
                                   title="Klik untuk mengatur Surcharge 3 per BLDE">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            deduction 1:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="deduction1"
                                   class="lob-input"
                                   value="0">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            deduction 2:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="deduction2"
                                   class="lob-input"
                                   value="0">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            Sample:
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="sample"
                                   class="lob-input"
                                   value="0">

                        </td>

                    </tr>

                    <tr>

                        <td>
                            <strong>
                                Total Order Value
                            </strong>
                        </td>

                        <td>

                            <input type="number"
                                   step="0.01"
                                   id="totalOrderValue"
                                   class="lob-input"
                                   value="0">

                        </td>

                    </tr>

                </table>

            </div>


            {{-- NOTE --}}
            <div class="lob-note">
                "OUR PRODUCTS DO NOT CONTAIN ANY RAW MATERIALS OF RUSSIAN ORIGIN"
            </div>


            {{-- SIGNATURE --}}
            <div class="lob-signature">
                Stamp and/or Signature:
            </div>

        </div>

    </div>

</div>


<style>
    .lob-payment-clickable { cursor: pointer; }
    .lob-payment-clickable:hover { background: #fff8e1 !important; }
    .lob-modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; text-align:left; }
    .lob-modal-grid .full { grid-column:1 / -1; }
    .lob-modal-grid label { display:block; font-size:12px; font-weight:600; margin-bottom:4px; }
    .lob-modal-grid input, .lob-modal-grid select, .lob-modal-grid textarea { width:100%; box-sizing:border-box; padding:7px 8px; border:1px solid #ced4da; border-radius:4px; }
    .lob-surcharge-grid { max-height:430px; overflow:auto; text-align:left; }
    .lob-surcharge-grid table { width:100%; border-collapse:collapse; font-size:12px; }
    .lob-surcharge-grid th, .lob-surcharge-grid td { border:1px solid #ddd; padding:5px; vertical-align:middle; }
    .lob-surcharge-grid th { background:#f5f5f5; position:sticky; top:0; z-index:1; }
    .lob-surcharge-grid input { width:100%; min-width:90px; box-sizing:border-box; padding:5px; border:1px solid #ced4da; border-radius:3px; }
    @media (max-width:700px) { .lob-modal-grid { grid-template-columns:1fr; } .lob-modal-grid .full { grid-column:auto; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const scrollbar =
        document.getElementById('lobScrollbar');

    const scrollbarInner =
        document.getElementById('lobScrollbarInner');

    const headerTable =
        document.querySelector('.lob-header-table');

    const bodyTable =
        document.getElementById('lobBodyTable');

    const tbody =
        document.getElementById('lobItemsBody');


    /* =========================================================
       HORIZONTAL SCROLL
       
       Scrollbar di bawah TH adalah MASTER.
       
       Saat digeser:
       1. TH bergerak
       2. BODY bergerak
    ========================================================= */

    function syncHorizontalScroll() {

        const x =
            scrollbar.scrollLeft;

        headerTable.style.transform =
            'translateX(-' + x + 'px)';

        bodyTable.style.transform =
            'translateX(-' + x + 'px)';

    }


    scrollbar.addEventListener(
        'scroll',
        syncHorizontalScroll
    );


    /*
     * Buat scrollbar mengikuti lebar tabel.
     */
    function updateScrollbarWidth() {

        const width =
            Math.max(
                headerTable.scrollWidth,
                bodyTable.scrollWidth
            );

        scrollbarInner.style.width =
            width + 'px';

    }


    updateScrollbarWidth();


    /* =========================================================
       NUMBER
    ========================================================= */

    function number(value) {

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return 0;
        }

        const result =
            parseFloat(
                String(value).replace(/,/g, '')
            );

        return Number.isFinite(result)
            ? result
            : 0;

    }


    function format(value, decimals = 2) {

        return number(value).toLocaleString(
            'en-US',
            {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }
        );

    }


    /* =========================================================
       CALCULATE ROW
    ========================================================= */

    function formatPriceInput(input) {

        const raw = String(input.value || '')
            .replace(/,/g, '')
            .trim();

        if (raw === '') {
            input.value = '';
            return;
        }

        const value = parseFloat(raw);

        if (!Number.isFinite(value)) {
            input.value = '';
            return;
        }

        input.value = value.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    document.querySelectorAll('.item-price').forEach(function (input) {

        input.addEventListener('blur', function () {
            formatPriceInput(this);

            const row = this.closest('.lob-item-row');

            if (row) {
                calculateRow(row);
            }

            calculateTotals();
        });
    });

    function calculateRow(row) {

        const net =
            number(
                row.querySelector(
                    '.weight-net'
                )?.value
            );

        const gross =
            number(
                row.querySelector(
                    '.weight-gross'
                )?.value
            );

        const cartons =
            number(
                row.querySelector(
                    '.qty-carton'
                )?.value
            );


        /* NET */
        const totalNet =
            net * cartons;


        const netCell =
            row.querySelector(
                '.total-net'
            );


        if (netCell) {

            netCell.textContent =
                format(totalNet);

        }


        /* GROSS */
        const totalGross =
            gross * cartons;


        const grossCell =
            row.querySelector(
                '.total-gross'
            );


        if (grossCell) {

            grossCell.textContent =
                format(totalGross);

        }


        /* =====================================================
           VOLUME
        ====================================================== */

        const L =
            number(
                row.querySelector(
                    '.carton-l'
                )?.value
            );

        const W =
            number(
                row.querySelector(
                    '.carton-w'
                )?.value
            );

        const H =
            number(
                row.querySelector(
                    '.carton-h'
                )?.value
            );


        const volume =
            (
                L *
                W *
                H
            ) / 1000000;


        const totalVolume =
            volume * cartons;


        const volumeCell =
            row.querySelector(
                '.volume-carton'
            );


        const totalVolumeCell =
            row.querySelector(
                '.total-volume'
            );


        if (volumeCell) {

            volumeCell.textContent =
                format(volume, 3);

        }


        if (totalVolumeCell) {

            totalVolumeCell.textContent =
                format(totalVolume, 3);

        }


        /* =====================================================
           AMOUNT
        ====================================================== */

        const qty =
            number(
                row.querySelector(
                    '.item-qty'
                )?.value
            );

        const price =
            number(
                row.querySelector(
                    '.item-price'
                )?.value
            );


        const amount =
            qty * price;


        const amountCell =
            row.querySelector(
                '.total-price'
            );


        if (amountCell) {

            amountCell.textContent =
                format(amount);

        }

    }


    /* =========================================================
       TOTAL
    ========================================================= */

    function calculateTotals() {

        let cartons = 0;
        let net = 0;
        let gross = 0;
        let volume = 0;
        let qty = 0;
        let amount = 0;


        tbody.querySelectorAll(
            '.lob-item-row'
        ).forEach(function (row) {

            const rowCartons =
                number(
                    row.querySelector(
                        '.qty-carton'
                    )?.value
                );


            const rowNet =
                number(
                    row.querySelector(
                        '.weight-net'
                    )?.value
                );


            const rowGross =
                number(
                    row.querySelector(
                        '.weight-gross'
                    )?.value
                );


            const L =
                number(
                    row.querySelector(
                        '.carton-l'
                    )?.value
                );


            const W =
                number(
                    row.querySelector(
                        '.carton-w'
                    )?.value
                );


            const H =
                number(
                    row.querySelector(
                        '.carton-h'
                    )?.value
                );


            const rowVolume =
                (
                    L *
                    W *
                    H
                ) / 1000000;


            const rowQty =
                number(
                    row.querySelector(
                        '.item-qty'
                    )?.value
                );


            const rowPrice =
                number(
                    row.querySelector(
                        '.item-price'
                    )?.value
                );


            cartons += rowCartons;

            net +=
                rowNet *
                rowCartons;

            gross +=
                rowGross *
                rowCartons;

            volume +=
                rowVolume *
                rowCartons;

            qty += rowQty;

            amount +=
                rowQty *
                rowPrice;

        });


        document.getElementById(
            'totalCartons'
        ).textContent =
            format(cartons, 0);


        document.getElementById(
            'totalNetWeight'
        ).textContent =
            format(net);


        document.getElementById(
            'totalGrossWeight'
        ).textContent =
            format(gross);


        document.getElementById(
            'totalVolume'
        ).textContent =
            format(volume, 3);


        document.getElementById(
            'totalQty'
        ).textContent =
            format(qty, 0);


        document.getElementById(
            'grandTotal'
        ).textContent =
            format(amount);


        calculateOrderValue(amount);

    }


    /* =========================================================
       ORDER VALUE
       
       Mengikuti formula Excel:
       
       Z48 =
       Z38 - Z39 + Z40 + Z41 + Z42 - Z43 - Z44 - Z47
    ========================================================= */

    function calculateOrderValue(amount) {

        const discount =
            number(
                document.getElementById(
                    'tradeDiscount'
                )?.value
            );


        const surcharge1 =
            number(
                document.getElementById(
                    'surcharge1'
                )?.value
            );


        const surcharge2 =
            number(
                document.getElementById(
                    'surcharge2'
                )?.value
            );


        const surcharge3 =
            number(
                document.getElementById(
                    'surcharge3'
                )?.value
            );


        const deduction1 =
            number(
                document.getElementById(
                    'deduction1'
                )?.value
            );


        const deduction2 =
            number(
                document.getElementById(
                    'deduction2'
                )?.value
            );


        const sample =
            number(
                document.getElementById(
                    'sample'
                )?.value
            );


        const total =
            amount
            - discount
            + surcharge1
            + surcharge2
            + surcharge3
            - deduction1
            - deduction2
            - sample;


        const totalOrderValue =
            document.getElementById(
                'totalOrderValue'
            );


        if (totalOrderValue) {

            totalOrderValue.value =
                total.toFixed(2);

        }


        const finalInvoiceAmount =
            document.getElementById(
                'finalInvoiceAmount'
            );


        if (finalInvoiceAmount) {

            finalInvoiceAmount.value =
                total.toFixed(2);

        }


        calculateDeposit();

    }


    /* =========================================================
       DEPOSIT

       Deposit TIDAK lagi dihitung 40% dari nilai item.
       Nilai deposit berasal langsung dari ExportArPayment
       berdasarkan ref_po / BLDE.
    ========================================================= */

    function calculateDeposit() {

        let totalPaid = 0;

        // Deposit visible per BLDE. Jangan ikut hidden payment-existing-row
        // agar payment tidak terhitung dua kali.
        document.querySelectorAll(
            '.deposit-row .deposit-value'
        ).forEach(function (input) {

            totalPaid += number(input.value);

        });

        // Payment baru umum.
        document.querySelectorAll(
            '.lob-new-payment-amount'
        ).forEach(function (input) {

            totalPaid += number(input.value);

        });

        const finalInvoice = number(
            document.getElementById('finalInvoiceAmount')?.value
        );

        const finalPayment = finalInvoice - totalPaid;

        const finalAmountPaid =
            document.getElementById('finalAmountPaid');

        if (finalAmountPaid) {
            finalAmountPaid.value = finalPayment.toFixed(2);
        }
    }

    /* =========================================================
       BIND INPUT
    ========================================================= */

    tbody.querySelectorAll(
        '.lob-item-row'
    ).forEach(function (row) {

        row.querySelectorAll(
            'input, textarea'
        ).forEach(function (input) {

            input.addEventListener(
                'input',
                function () {

                    calculateRow(row);

                    calculateTotals();

                }
            );

        });


        calculateRow(row);

    });


    /* =========================================================
       COMMERCIAL INPUT
    ========================================================= */

    document.querySelectorAll(
        '#tradeDiscount,' +
        '#surcharge1,' +
        '#surcharge2,' +
        '#surcharge3,' +
        '#deduction1,' +
        '#deduction2,' +
        '#sample'
    ).forEach(function (input) {

        input.addEventListener(
            'input',
            function () {

                calculateTotals();

            }
        );

    });


    /* =========================================================
       INITIAL
    ========================================================= */

    calculateTotals();

    updateScrollbarWidth();


    /* =========================================================
       EDIT / UPDATE LOBERON CIPL
    ========================================================= */

    const lobPage = document.querySelector('.lob-page');
    const btnEdit = document.getElementById('btnLobEdit');
    const btnSave = document.getElementById('btnLobSave');
    const btnCancel = document.getElementById('btnLobCancel');
    const btnAddPayment = document.getElementById('btnLobAddPayment');
    const editControls = document.querySelectorAll('.lob-edit-control');

    let lobSnapshot = null;

    const surchargeDetails = { 1: {}, 2: {}, 3: {} };
    const bldeList = @json($bldeNumbers->values());

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeRefPo(value) {
        return String(value ?? '').replace(/[\u00AD\u200B-\u200D\uFEFF]/g, '').trim();
    }

    function paymentDateFallback() {
        const invoiceDate = document.querySelector('[name="invoice_date"]')?.value || '';
        if (invoiceDate) return invoiceDate;
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function getExistingSurchargeSlot(row) {
        const slot = parseInt(row.dataset.surchargeSlot || '1', 10);
        return [1,2,3].includes(slot) ? slot : 1;
    }

    document.querySelectorAll('.payment-existing-row').forEach(function(row) {
        const type = row.querySelector('.payment-type')?.value || '';
        if (type !== 'surcharge') return;
        const slot = getExistingSurchargeSlot(row);
        const refPo = normalizeRefPo(row.querySelector('.payment-ref-po')?.value || '');
        if (!refPo) return;
        surchargeDetails[slot][refPo] = {
            id: row.dataset.paymentId || null, ref_po: refPo,
            amount: number(row.querySelector('.payment-amount')?.value),
            payment_date: row.querySelector('.payment-date')?.value || paymentDateFallback(),
            reference: row.querySelector('.payment-reference')?.value || '',
            surcharge_slot: slot,
            keterangan: row.querySelector('.payment-keterangan')?.value || `Surcharge ${slot}`
        };
    });

    /*
     * Existing surcharge dari database langsung ditampilkan
     * ke widget Surcharge 1/2/3 saat halaman dibuka.
     */
    [1, 2, 3].forEach(function (slot) {

        const total = Object.values(
            surchargeDetails[slot] || {}
        ).reduce(function (sum, detail) {

            return sum + number(detail.amount);

        }, 0);

        const input = document.getElementById(
            `surcharge${slot}`
        );

        if (input) {
            input.value = total.toFixed(2);
        }
    });

    function openDepositModal(row) {
        if (!lobPage.classList.contains('lob-editing')) return;
        const refPo = normalizeRefPo(row.dataset.blde || '');
        const amountInput = row.querySelector('.deposit-value');
        const currentAmount = number(amountInput?.value);
        Swal.fire({
            title: `Deposit ${esc(refPo)}`, width:650, showCancelButton:true,
            confirmButtonText:'Simpan', cancelButtonText:'Batal', focusConfirm:false,
            html:`<div class="lob-modal-grid">
                <div><label>Tanggal Payment *</label><input id="lobDepositDate" type="date" value="${esc(row.dataset.paymentDate || paymentDateFallback())}"></div>
                <div><label>Amount *</label><input id="lobDepositAmount" type="number" step="0.01" min="0" value="${currentAmount.toFixed(2)}"></div>
                <div><label>Reference</label><input id="lobDepositReference" type="text" value="${esc(row.dataset.paymentReference || '')}"></div>
                <div><label>Keterangan</label><input id="lobDepositKeterangan" type="text" value="${esc(row.dataset.paymentKeterangan || '')}"></div>
                <div class="full"><small>BLDE: <strong>${esc(refPo)}</strong></small></div>
            </div>`,
            preConfirm:function(){
                const date=document.getElementById('lobDepositDate')?.value||'';
                const amount=number(document.getElementById('lobDepositAmount')?.value);
                if(!date){Swal.showValidationMessage('Tanggal payment wajib diisi.');return false;}
                if(amount<=0){Swal.showValidationMessage('Amount harus lebih besar dari 0.');return false;}
                return {date,amount,reference:document.getElementById('lobDepositReference')?.value||'',keterangan:document.getElementById('lobDepositKeterangan')?.value||''};
            }
        }).then(function(result){
            if(!result.isConfirmed)return; const v=result.value;
            row.dataset.paymentDate=v.date; row.dataset.paymentReference=v.reference; row.dataset.paymentKeterangan=v.keterangan;
            if(amountInput) amountInput.value=v.amount.toFixed(2);
            calculateTotals();
        });
    }

    function openExistingPaymentModal(row) {
        if (!lobPage.classList.contains('lob-editing')) return;
        const type=row.querySelector('.payment-type')?.value||'deposit';
        if(type==='surcharge'){ openSurchargeModal(getExistingSurchargeSlot(row)); return; }
        const amountInput=row.querySelector('.payment-amount');
        const dateInput=row.querySelector('.payment-date');
        const refInput=row.querySelector('.payment-reference');
        const ketInput=row.querySelector('.payment-keterangan');
        const refPo=normalizeRefPo(row.querySelector('.payment-ref-po')?.value||'');
        Swal.fire({
            title:`${type==='pelunasan'?'Pelunasan':'Deposit'} ${esc(refPo)}`,width:650,showCancelButton:true,
            confirmButtonText:'Simpan',cancelButtonText:'Batal',
            html:`<div class="lob-modal-grid">
                <div><label>Tanggal Payment *</label><input id="lobExistingDate" type="date" value="${esc(dateInput?.value||paymentDateFallback())}"></div>
                <div><label>Amount *</label><input id="lobExistingAmount" type="number" step="0.01" min="0" value="${number(amountInput?.value).toFixed(2)}"></div>
                <div><label>Reference</label><input id="lobExistingReference" type="text" value="${esc(refInput?.value||'')}"></div>
                <div><label>Keterangan</label><input id="lobExistingKeterangan" type="text" value="${esc(ketInput?.value||'')}"></div>
            </div>`,
            preConfirm:function(){
                const date=document.getElementById('lobExistingDate')?.value||''; const amount=number(document.getElementById('lobExistingAmount')?.value);
                if(!date){Swal.showValidationMessage('Tanggal payment wajib diisi.');return false;}
                if(amount<=0){Swal.showValidationMessage('Amount harus lebih besar dari 0.');return false;}
                return {date,amount,reference:document.getElementById('lobExistingReference')?.value||'',keterangan:document.getElementById('lobExistingKeterangan')?.value||''};
            }
        }).then(function(result){
            if(!result.isConfirmed)return; const v=result.value;
            if(dateInput)dateInput.value=v.date; if(amountInput)amountInput.value=v.amount.toFixed(2); if(refInput)refInput.value=v.reference; if(ketInput)ketInput.value=v.keterangan; calculateTotals();
        });
    }

    function openSurchargeModal(slot) {
        if (!lobPage.classList.contains('lob-editing')) return;
        const current=surchargeDetails[slot]||{}; let rows='';
        bldeList.forEach(function(blde){
            const refPo=normalizeRefPo(blde), d=current[refPo]||{};
            rows+=`<tr data-ref-po="${esc(refPo)}">
                <td><strong>${esc(refPo)}</strong></td>
                <td><input class="lob-surcharge-amount" type="number" step="0.01" min="0" value="${number(d.amount).toFixed(2)}"></td>
                <td><input class="lob-surcharge-date" type="date" value="${esc(d.payment_date||paymentDateFallback())}"></td>
                <td><input class="lob-surcharge-reference" type="text" value="${esc(d.reference||'')}"></td>
                <td><input class="lob-surcharge-keterangan" type="text" value="${esc(d.keterangan||`Surcharge ${slot}`)}"></td>
            </tr>`;
        });
        if(!rows) rows='<tr><td colspan="5">Tidak ada BLDE pada invoice ini.</td></tr>';
        Swal.fire({
            title:`Surcharge ${slot} per BLDE`,width:1100,showCancelButton:true,confirmButtonText:'Simpan Surcharge',cancelButtonText:'Batal',
            html:`<div class="lob-surcharge-grid"><table><thead><tr><th>BLDE</th><th>Amount</th><th>Tanggal *</th><th>Reference</th><th>Keterangan</th></tr></thead><tbody>${rows}</tbody></table></div><div style="margin-top:10px;text-align:right;font-weight:700">Total Surcharge ${slot}: <span id="lobSurchargeModalTotal">0.00</span></div>`,
            didOpen:function(){
                const modal=Swal.getHtmlContainer(); const update=function(){let total=0;modal.querySelectorAll('.lob-surcharge-amount').forEach(i=>total+=number(i.value));const el=modal.querySelector('#lobSurchargeModalTotal');if(el)el.textContent=total.toFixed(2);};
                modal.querySelectorAll('.lob-surcharge-amount').forEach(i=>i.addEventListener('input',update)); update();
            },
            preConfirm:function(){
                const modal=Swal.getHtmlContainer(); const result={}; let error=false;
                modal.querySelectorAll('tbody tr[data-ref-po]').forEach(function(row){
                    const refPo=normalizeRefPo(row.dataset.refPo); const amount=number(row.querySelector('.lob-surcharge-amount')?.value); const date=row.querySelector('.lob-surcharge-date')?.value||'';
                    if(amount>0&&!date){error=true;return;} if(amount<=0)return;
                    result[refPo]={id:current[refPo]?.id||null,ref_po:refPo,amount:amount,payment_date:date,reference:row.querySelector('.lob-surcharge-reference')?.value||'',surcharge_slot:slot,
                        surcharge_slot:slot,
                        keterangan:(function(){
                            const ket = String(
                                row.querySelector('.lob-surcharge-keterangan')?.value || ''
                            ).trim();

                            if (!ket) {
                                return `Surcharge ${slot}`;
                            }

                            if (/^Surcharge\s*[123]\b/i.test(ket)) {
                                return ket;
                            }

                            return `Surcharge ${slot} - ${ket}`;
                        })()};
                });
                if(error){Swal.showValidationMessage('Tanggal payment wajib diisi untuk setiap BLDE yang memiliki amount.');return false;}
                return result;
            }
        }).then(function(result){
            if(!result.isConfirmed)return; surchargeDetails[slot]=result.value||{};
            const total=Object.values(surchargeDetails[slot]).reduce((sum,d)=>sum+number(d.amount),0); const input=document.getElementById(`surcharge${slot}`); if(input)input.value=total.toFixed(2); calculateTotals();
        });
    }

    function bindPaymentModalTriggers() {
        document.querySelectorAll('.deposit-row').forEach(function(row){row.classList.add('lob-payment-clickable');row.addEventListener('click',()=>openDepositModal(row));});
        document.querySelectorAll('.payment-existing-row').forEach(function(row){row.classList.add('lob-payment-clickable');row.addEventListener('click',function(e){if(e.target.closest('button,a'))return;openExistingPaymentModal(row);});});
        [1,2,3].forEach(function(slot){const input=document.getElementById(`surcharge${slot}`);if(!input)return;input.addEventListener('click',()=>openSurchargeModal(slot));});
    }

    bindPaymentModalTriggers();

    function setLobEditing(editing) {

        if (!lobPage) return;

        lobPage.classList.toggle('lob-editing', editing);

        editControls.forEach(function (button) {
            button.style.display = editing ? '' : 'none';
        });

        if (btnEdit) {
            btnEdit.style.display = editing ? 'none' : '';
        }

        document.querySelectorAll(
            '.lob-page input, .lob-page textarea, .lob-page select'
        ).forEach(function (input) {

            // Payment amount existing is edited through modal.
            if (input.classList.contains('payment-amount')) {
                input.readOnly = true;
                return;
            }

            if (input.classList.contains('deposit-value')) {
                input.readOnly = true;
                return;
            }

            if (input.classList.contains('surcharge-trigger')) {
                input.readOnly = true;
                return;
            }

            // Hidden fields never need editing.
            if (input.type === 'hidden') {
                return;
            }

            // Final calculated amount remains readonly.
            if (
                input.id === 'finalAmountPaid' ||
                input.id === 'totalOrderValue' ||
                input.id === 'finalInvoiceAmount'
            ) {
                input.readOnly = true;
                return;
            }

            if (
                input.tagName === 'TEXTAREA' ||
                input.type === 'text' ||
                input.type === 'date' ||
                input.type === 'number'
            ) {
                input.readOnly = !editing;
            }

            if (input.tagName === 'SELECT') {
                input.disabled = !editing;
            }
        });
    }


    function collectLobPayload() {

        const items = [];

        document.querySelectorAll(
            '#lobItemsBody .lob-item-row'
        ).forEach(function (row, index) {

            function value(selector) {
                return row.querySelector(selector)?.value ?? '';
            }

            items.push({
                id: row.dataset.itemId || null,
                index: index,

                po_number: value('input[name$="[po_number]"]'),
                article_code: value('input[name$="[article_code]"]'),
                color_id: value('input[name$="[color_id]"]'),
                size_id: value('input[name$="[size_id]"]'),
                ean_code: value('input[name$="[ean_code]"]'),
                eudr_dds_code: value('input[name$="[eudr_dds_code]"]'),
                description: value('textarea[name$="[description]"]'),
                hts_code: value('input[name$="[hts_code]"]'),
                bulky_goods_class: value('input[name$="[bulky_goods_class]"]'),
                net_weight: value('input[name$="[net_weight]"]'),
                gross_weight: value('input[name$="[gross_weight]"]'),
                qty_carton: value('input[name$="[qty_carton]"]'),

                marks_1: value('input[name$="[marks_1]"]'),
                marks_2: value('input[name$="[marks_2]"]'),
                marks_3: value('input[name$="[marks_3]"]'),

                carton_l: value('input[name$="[carton_l]"]'),
                carton_w: value('input[name$="[carton_w]"]'),
                carton_h: value('input[name$="[carton_h]"]'),

                qty: value('input[name$="[qty]"]'),
                price: value('input[name$="[price]"]')
            });
        });


        const payments = [];

        // Existing payment non-deposit.
        // Deposit dikumpulkan dari .deposit-row agar tidak double.
        // Surcharge dikumpulkan dari surchargeDetails per BLDE.
        document.querySelectorAll('.payment-existing-row').forEach(function (row) {

            const type =
                row.querySelector('.payment-type')?.value || '';

            if (type === 'deposit' || type === 'surcharge') {
                return;
            }

            const amount =
                number(
                    row.querySelector('.payment-amount')?.value
                );

            if (amount <= 0) {
                return;
            }

            payments.push({
                id: row.dataset.paymentId || null,
                payment_type: type,
                ref_po: normalizeRefPo(
                    row.querySelector('.payment-ref-po')?.value || ''
                ),
                payment_date: row.querySelector('.payment-date')?.value || paymentDateFallback(),
                amount: amount,
                reference: row.querySelector('.payment-reference')?.value || '',
                keterangan: row.querySelector('.payment-keterangan')?.value || ''
            });
        });

        // Deposit BLDE yang belum mempunyai payment record.
        document.querySelectorAll('.deposit-row').forEach(function (row) {
            const amount = number(row.querySelector('.deposit-value')?.value);
            if (amount <= 0) return;
            payments.push({
                id: row.dataset.paymentId || null,
                payment_type: 'deposit',
                ref_po: normalizeRefPo(row.dataset.blde || ''),
                payment_date: row.dataset.paymentDate || paymentDateFallback(),
                amount: amount,
                reference: row.dataset.paymentReference || '',
                keterangan: row.dataset.paymentKeterangan || ''
            });
        });

        // Surcharge 1/2/3, satu payment record per BLDE yang berisi amount.
        [1, 2, 3].forEach(function (slot) {
            Object.values(surchargeDetails[slot] || {}).forEach(function (detail) {
                const amount = number(detail.amount);
                if (amount <= 0) return;
                payments.push({
                    id: detail.id || null,
                    payment_type: 'surcharge',
                    ref_po: normalizeRefPo(detail.ref_po || ''),
                    payment_date: detail.payment_date || paymentDateFallback(),
                    amount: amount,
                    reference: detail.reference || '',
                    surcharge_slot: slot,
                    surcharge_slot: slot,
                    keterangan:
                        (
                            detail.keterangan &&
                            !/^Surcharge\s*[123]\b/i.test(
                                String(detail.keterangan).trim()
                            )
                        )
                            ? `Surcharge ${slot} - ${String(detail.keterangan).trim()}`
                            : `Surcharge ${slot}`
                });
            });
        });

        // Payment baru umum.
        document.querySelectorAll('.lob-new-payment-row').forEach(function (row) {
            const amount = number(row.querySelector('.lob-new-payment-amount')?.value);
            if (amount <= 0) return;
            payments.push({
                id: null,
                payment_type: row.querySelector('.lob-new-payment-type')?.value || 'deposit',
                ref_po: normalizeRefPo(row.querySelector('.lob-new-payment-ref-po')?.value || ''),
                payment_date: row.querySelector('.lob-new-payment-date')?.value || paymentDateFallback(),
                amount: amount,
                reference: row.querySelector('.lob-new-payment-reference')?.value || '',
                keterangan: row.querySelector('.lob-new-payment-keterangan')?.value || ''
            });
        });

        return {
            invoice_no: @json($invoice),

            header: {
                consignee_name:
                    document.querySelector('[name="consignee_name"]')?.value || '',
                consignee_address:
                    document.querySelector('[name="consignee_address"]')?.value || '',
                destination_name:
                    document.querySelector('[name="destination_name"]')?.value || '',
                destination_address:
                    document.querySelector('[name="destination_address"]')?.value || '',
                eori:
                    document.querySelector('[name="eori"]')?.value || '',
                incoterm:
                    document.querySelector('[name="incoterm"]')?.value || '',
                country_origin:
                    document.querySelector('[name="country_origin"]')?.value || '',
                port_loading:
                    document.querySelector('[name="port_loading"]')?.value || '',
                port_discharge:
                    document.querySelector('[name="port_discharge"]')?.value || '',
                rex:
                    document.querySelector('[name="rex"]')?.value || '',
                igst_no:
                    document.querySelector('[name="igst_no"]')?.value || '',
                invoice_date:
                    document.querySelector('[name="invoice_date"]')?.value || '',
                on_board_date:
                    document.querySelector('[name="on_board_date"]')?.value || '',
                vessel_name:
                    document.querySelector('[name="vessel_name"]')?.value || '',
                container_no:
                    document.querySelector('[name="container_no"]')?.value || '',
                container_type:
                    document.querySelector('[name="container_type"]')?.value || '',
                value_in_words:
                    document.querySelector('[name="value_in_words"]')?.value || '',
                payment_terms:
                    document.querySelector('[name="payment_terms"]')?.value || ''
            },

            commercial: {
                trade_discount:
                    document.getElementById('tradeDiscount')?.value || 0,
                surcharge1:
                    document.getElementById('surcharge1')?.value || 0,
                surcharge2:
                    document.getElementById('surcharge2')?.value || 0,
                surcharge3:
                    document.getElementById('surcharge3')?.value || 0,
                deduction1:
                    document.getElementById('deduction1')?.value || 0,
                deduction2:
                    document.getElementById('deduction2')?.value || 0,
                sample:
                    document.getElementById('sample')?.value || 0
            },

            items: items,
            payments: payments
        };
    }


    function restoreLobSnapshot() {

        if (!lobSnapshot) {
            window.location.reload();
            return;
        }

        window.location.reload();
    }


    if (btnEdit) {
        btnEdit.addEventListener('click', function () {

            lobSnapshot = collectLobPayload();

            setLobEditing(true);

        });
    }


    if (btnCancel) {
        btnCancel.addEventListener('click', function () {

            Swal.fire({
                title: 'Batalkan perubahan?',
                text: 'Perubahan yang belum disimpan akan dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Kembali'
            }).then(function (result) {

                if (result.isConfirmed) {
                    restoreLobSnapshot();
                }

            });

        });
    }


    function addNewPaymentRow() {

        const body =
            document.getElementById('lobNewPaymentsBody');

        if (!body) return;

        const options = @json($bldeNumbers->values());

        let refOptions =
            '<option value="">-- Pilih BLDE --</option>';

        options.forEach(function (blde) {

            refOptions +=
                '<option value="' +
                String(blde).replace(/"/g, '&quot;') +
                '">' +
                String(blde).replace(/</g, '&lt;') +
                '</option>';

        });


        const tr =
            document.createElement('tr');

        tr.className =
            'lob-new-payment-row';

        tr.innerHTML = `
            <td class="lob-payment-label">

                <div style="display:flex; gap:4px;">

                    <select class="lob-new-payment-type">
                        <option value="deposit">Deposit</option>
                        <option value="pelunasan">Pelunasan</option>
                        <option value="surcharge">Surcharge</option>
                    </select>

                    <select class="lob-new-payment-ref-po">
                        ${refOptions}
                    </select>

                </div>

                <div style="display:flex; gap:4px; margin-top:3px;">

                    <input
                        type="date"
                        class="lob-new-payment-date">

                    <input
                        type="text"
                        class="lob-new-payment-reference"
                        placeholder="Reference">

                    <input
                        type="text"
                        class="lob-new-payment-keterangan"
                        placeholder="Keterangan">

                </div>

            </td>

            <td class="lob-payment-value">

                <div style="display:flex; gap:3px;">

                    <input
                        type="number"
                        step="0.01"
                        class="lob-new-payment-amount"
                        value="0">

                    <button
                        type="button"
                        class="lob-payment-remove"
                        title="Hapus">
                        <i class="fa fa-trash"></i>
                    </button>

                </div>

            </td>
        `;

        body.appendChild(tr);

        tr.querySelector('.lob-new-payment-amount')
            .addEventListener('input', calculateDeposit);

        tr.querySelector('.lob-payment-remove')
            .addEventListener('click', function () {

                tr.remove();
                calculateDeposit();

            });

    }


    if (btnAddPayment) {

        btnAddPayment.addEventListener(
            'click',
            addNewPaymentRow
        );

    }


    if (btnSave) {

        btnSave.addEventListener('click', async function () {

            const payload =
                collectLobPayload();

            const csrf =
                document.querySelector(
                    'meta[name="csrf-token"]'
                )?.getAttribute('content');


            Swal.fire({
                title: 'Menyimpan...',
                text: 'Mohon tunggu.',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });


            try {

                const response =
                    await fetch(
                        "{{ url('/export/loberon/update') }}",
                        {
                            method: 'PUT',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrf
                            },

                            body:
                                JSON.stringify(payload)
                        }
                    );


                const result =
                    await response.json();


                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message ||
                        'Gagal memperbarui Loberon CIPL.'
                    );

                }


                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: result.message ||
                        'Loberon CIPL berhasil diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                });


                window.location.reload();


            } catch (error) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message ||
                        'Terjadi kesalahan saat update.'
                });

            }

        });

    }


    // Awal halaman = VIEW MODE.
    setLobEditing(false);
});
</script>

@endsection