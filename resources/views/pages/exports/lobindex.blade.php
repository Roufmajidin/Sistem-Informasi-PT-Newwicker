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
                        class="btn btn-sm btn-primary"
                        onclick="window.print()">

                    <i class="fa fa-print"></i>
                    Print

                </button>

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

                                // PO NUMBER sengaja dikosongkan untuk LOBERON.
                                $poNumber = '';

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


                            <tr class="lob-item-row">

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

                            <tr class="lob-item-row">

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

                        <tr>
                            <td>
                                ./. Deposit BLDE-25170
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="deposit25170"
                                       value="0">

                            </td>
                        </tr>

                        <tr>
                            <td>
                                ./. Deposit BLDE-25171
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="deposit25171"
                                       value="0">

                            </td>
                        </tr>

                        <tr>
                            <td>
                                ./. Deposit BLDE-25718
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="deposit25718"
                                       value="0">

                            </td>
                        </tr>

                        <tr>
                            <td>
                                ./. Deposit BLDE-25719
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="deposit25719"
                                       value="0">

                            </td>
                        </tr>

                        <tr>
                            <td>
                                ./. Deposit BLDE-25458
                            </td>

                            <td class="lob-payment-value">

                                <input type="number"
                                       step="0.01"
                                       class="lob-input"
                                       id="deposit25458"
                                       value="0">

                            </td>
                        </tr>

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
                                   class="lob-input"
                                   value="0">

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
                                   class="lob-input"
                                   value="0">

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
                                   class="lob-input"
                                   value="0">

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
       
       Mengikuti Excel:
       
       BLDE-25170 = 40% Z16:Z18
       BLDE-25171 = 40% Z20:Z22
       BLDE-25718 = 40% Z23:Z28
       BLDE-25719 = 40% Z29
       BLDE-25458 = 40% Z30:Z37
    ========================================================= */

    function calculateDeposit() {

        let values = {};


        tbody.querySelectorAll(
            '.lob-item-row'
        ).forEach(function (row) {

            const po =
                String(
                    row.querySelector(
                        '[name*="[po_number]"]'
                    )?.value || ''
                ).trim();


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


            if (!values[po]) {
                values[po] = 0;
            }


            values[po] += amount;

        });


        const deposit25170 =
            (values['BLDE-25170'] || 0) * .40;


        const deposit25171 =
            (values['BLDE-25171'] || 0) * .40;


        const deposit25718 =
            (values['BLDE-25718'] || 0) * .40;


        const deposit25719 =
            (values['BLDE-25719'] || 0) * .40;


        const deposit25458 =
            (values['BLDE-25458'] || 0) * .40;


        document.getElementById(
            'deposit25170'
        ).value =
            deposit25170.toFixed(2);


        document.getElementById(
            'deposit25171'
        ).value =
            deposit25171.toFixed(2);


        document.getElementById(
            'deposit25718'
        ).value =
            deposit25718.toFixed(2);


        document.getElementById(
            'deposit25719'
        ).value =
            deposit25719.toFixed(2);


        document.getElementById(
            'deposit25458'
        ).value =
            deposit25458.toFixed(2);


        const finalInvoice =
            number(
                document.getElementById(
                    'finalInvoiceAmount'
                )?.value
            );


        const finalPayment =
            finalInvoice
            - deposit25170
            - deposit25171
            - deposit25718
            - deposit25719
            - deposit25458;


        document.getElementById(
            'finalAmountPaid'
        ).value =
            finalPayment.toFixed(2);

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

});
</script>

@endsection