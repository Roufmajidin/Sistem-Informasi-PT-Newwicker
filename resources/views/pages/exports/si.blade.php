@extends('master.master')



@section('content')



@php

    /*

    |--------------------------------------------------------------------------

    | SHIPPING INSTRUCTION

    | Content / field mengikuti file Excel SI yang diberikan.

    | Layout dibuat menyerupai form SI pada referensi gambar:

    | section header, 2-column table, compact A4 document.

    |--------------------------------------------------------------------------

    */



    $poNumbers = $ipl->items

        ->pluck('blde')

        ->filter()

        ->map(fn ($v) => trim((string) $v))

        ->unique()

        ->values()

        ->implode(' ; ');



    $totalQty = (int) $ipl->items->sum('qty_box');



    $totalNet = (float) $ipl->items->sum(function ($item) {

        return (float) ($item->net_weight ?? 0)
            * (float) ($item->qty_box ?? 0);

    });



    $totalGross = (float) $ipl->items->sum(function ($item) {

        return (float) ($item->gross_weight ?? 0)
            * (float) ($item->qty_box ?? 0);

    });



    $totalCbm = (float) $ipl->items->sum(

        fn ($item) => (float) ($item->total_cbm ?? 0)

    );

@endphp



<style>

    .si-page {

        background: #f1f3f5;

        min-height: calc(100vh - 60px);

        padding: 20px;

    }



    .si-toolbar {

        max-width: 1080px;

        margin: 0 auto 14px;

        display: flex;

        justify-content: space-between;

        align-items: center;

    }



    .si-toolbar-title {

        font-size: 18px;

        font-weight: 700;

    }



    .si-toolbar-sub {

        font-size: 12px;

        color: #6c757d;

    }



    .si-paper {

        width: 100%;

        max-width: 1080px;

        margin: auto;

        background: #fff;

        padding: 30px 34px 34px;

        box-shadow: 0 5px 20px rgba(0,0,0,.10);

        color: #111;

        font-family: Arial, Helvetica, sans-serif;

    }



    /* Company header */

    .si-company {
        position: relative;
        text-align: center;
        line-height: 1.35;
        margin-bottom: 14px;
        min-height: 92px;
        padding-left: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .si-company-logo {
        position: absolute;
        left: 4px;
        top: 50%;
        transform: translateY(-50%);
        width: 102px;
        height: 82px;
        object-fit: contain;
    }



    .si-company-name {

        font-size: 15px;

        font-weight: 800;

    }



    .si-company-line {

        font-size: 9px;

    }



    /* Main title */

    .si-main-title {

        border: 2px solid #111;

        text-align: center;

        padding: 8px 10px;

        font-size: 18px;

        font-weight: 800;

        letter-spacing: .2px;

        margin-bottom: 0;

    }



    .si-main-title small {

        display: block;

        font-size: 9px;

        margin-top: 2px;

        font-weight: 700;

    }



    /* Excel-style top information */

    .si-info {

        display: grid;

        grid-template-columns: 1fr 1fr;

        border-left: 1px solid #111;

        border-top: 1px solid #111;

    }



    .si-info-cell {

        display: grid;

        grid-template-columns: 125px 12px minmax(0, 1fr);

        min-height: 28px;

        border-right: 1px solid #111;

        border-bottom: 1px solid #111;

    }



    .si-label {

        font-size: 9px;

        font-weight: 700;

        padding: 5px 6px;

        display: flex;

        align-items: center;

    }



    .si-colon {

        font-size: 9px;

        font-weight: 700;

        padding-top: 5px;

        text-align: center;

    }



    .si-value {

        min-width: 0;

        padding: 3px 5px;

        display: flex;

        align-items: center;

    }



    .si-value input,

    .si-value textarea {

        width: 100%;

        border: 0;

        outline: 0;

        resize: vertical;

        background: transparent;

        padding: 2px 3px;

        font-size: 9px;

        color: #111;

        box-shadow: none !important;

    }



    .si-value textarea {

        line-height: 1.35;

        min-height: 50px;

    }



    .si-value input:focus,

    .si-value textarea:focus {

        background: #f7f9fb;

        outline: 1px solid #adb5bd !important;

    }



    /* Section like reference image */

    .si-section {

        border: 1px solid #111;

        border-top: 0;

        background: #f2f2f2;

        text-align: center;

        font-size: 10px;

        font-weight: 800;

        padding: 5px;

        letter-spacing: .15px;

    }



    .si-two-col {

        display: grid;

        grid-template-columns: 1fr 1fr;

        border-left: 1px solid #111;

        border-top: 1px solid #111;

    }



    .si-box {

        border-right: 1px solid #111;

        border-bottom: 1px solid #111;

        min-height: 29px;

        display: grid;

        grid-template-columns: 125px 12px minmax(0, 1fr);

    }



    .si-box.tall {

        min-height: 92px;

    }



    .si-box.medium {

        min-height: 62px;

    }



    .si-box.full {

        grid-column: 1 / -1;

    }



    .si-box .si-value {

        align-items: flex-start;

    }



    .si-text {

        font-size: 9px;

        line-height: 1.35;

        white-space: pre-line;

        width: 100%;

        padding: 2px 3px;

    }



    /* Main Excel body */

    .si-description {

        border: 1px solid #111;

        border-top: 0;

        padding: 13px 12px;

        font-size: 9px;

        line-height: 1.6;

    }



    .si-description .line {

        margin-top: 7px;

    }



    .si-simple-list {

        border-left: 1px solid #111;

        border-right: 1px solid #111;

    }



    .si-row {

        display: grid;

        grid-template-columns: 155px 12px minmax(0, 1fr);

        min-height: 28px;

        border-bottom: 1px solid #111;

    }



    .si-row:first-child {

        border-top: 0;

    }



    .si-row .si-label {

        border-right: 0;

    }



    /* Footer */

    .si-footer-text {

        padding: 14px 2px 6px;

        font-size: 9px;

    }



    .si-signature {

        margin-top: 28px;

        font-size: 9px;

    }



    .si-signature-name {

        margin-top: 45px;

        font-weight: 700;

    }



    .si-actions {

        max-width: 1080px;

        margin: 14px auto 0;

        display: flex;

        justify-content: flex-end;

        gap: 7px;

    }



    .si-save-status {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 9999;
        background: #212529;
        color: #fff;
        padding: 7px 12px;
        border-radius: 5px;
        font-size: 11px;
        opacity: 0;
        transform: translateY(5px);
        transition: .2s;
        pointer-events: none;
    }

    .si-save-status.show {
        opacity: 1;
        transform: translateY(0);
    }

    .si-saving {
        background: #fff8e1 !important;
    }

    .si-saved {
        background: #f1fff3 !important;
    }

    .si-error {
        background: #fff1f1 !important;
    }

    /* ==========================================================
       FLOATING COMPONENT NAVIGATION
       - fixed on right side
       - transparent / glass effect
       - minimized leaves a small tab visible
       - clicking an item smooth-scrolls to the component
    ========================================================== */

    .si-floating-nav {
        position: fixed;
        top: 50%;
        right: 0;
        width: 235px;
        transform: translate(190px, -50%);
        z-index: 9998;
        transition: transform .25s ease;
        pointer-events: auto;
    }

    .si-floating-nav.open {
        transform: translate(0, -50%);
    }

    .si-floating-nav-inner {
        position: relative;
        padding: 10px 10px 10px 46px;
        border: 1px solid rgba(255,255,255,.55);
        border-right: 0;
        border-radius: 12px 0 0 12px;
        background: rgba(255,255,255,.38);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 5px 24px rgba(0,0,0,.14);
    }

    .si-floating-toggle {
        position: absolute;
        left: 0;
        top: 50%;
        width: 38px;
        height: 74px;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.65);
        border-right: 0;
        border-radius: 10px 0 0 10px;
        background: rgba(20,35,58,.72);
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: 0 3px 14px rgba(0,0,0,.12);
    }

    .si-floating-toggle:hover {
        background: rgba(20,35,58,.88);
    }

    .si-floating-title {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .2px;
        color: #172033;
        margin-bottom: 7px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(0,0,0,.12);
    }

    .si-floating-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
        max-height: 62vh;
        overflow-y: auto;
        padding-right: 2px;
    }

    .si-floating-list::-webkit-scrollbar {
        width: 4px;
    }

    .si-floating-list::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,.20);
        border-radius: 20px;
    }

    .si-floating-item {
        width: 100%;
        border: 0;
        border-radius: 6px;
        padding: 6px 8px;
        text-align: left;
        background: rgba(255,255,255,.34);
        color: #172033;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: .15s ease;
    }

    .si-floating-item:hover {
        background: rgba(255,255,255,.72);
        transform: translateX(-2px);
    }

    .si-floating-item.active {
        background: rgba(20,35,58,.82);
        color: #fff;
    }

    .si-nav-target {
        scroll-margin-top: 75px;
    }

    @media (max-width: 800px) {

        .si-floating-nav {
            width: 205px;
            transform: translate(165px, -50%);
        }

        .si-floating-nav.open {
            transform: translate(0, -50%);
        }

        .si-floating-nav-inner {
            padding-left: 42px;
        }

        .si-floating-item {
            font-size: 9px;
            padding: 6px 7px;
        }
    }


    @media (max-width: 800px) {

        .si-page {

            padding: 8px;

        }



        .si-paper {

            padding: 18px;

        }



        .si-info,

        .si-two-col {

            grid-template-columns: 1fr;

        }



        .si-box.full {

            grid-column: auto;

        }

    }



    @media print {

        @page {

            size: A4 portrait;

            margin: 8mm;

        }



        body {

            background: #fff !important;

        }



        .si-page {

            background: #fff;

            padding: 0;

        }



        .si-toolbar,

        .si-actions {

            display: none !important;

        }



        .si-paper {

            max-width: none;

            padding: 0;

            box-shadow: none;

        }



        .si-value input,

        .si-value textarea {

            font-size: 8px;

        }

    }

</style>



<div class="si-page">

    <div id="siSaveStatus" class="si-save-status">Saving...</div>



    {{-- TOOLBAR --}}

    <div class="si-toolbar">

        <div>

            <div class="si-toolbar-title">Shipping Instruction</div>

            <div class="si-toolbar-sub">

                {{ $ipl->invoice_no ?? 'Export IPL' }}

            </div>

        </div>



        <div class="d-flex gap-2">

            <button type="button"
                    class="btn btn-outline-secondary btn-sm"
                    onclick="window.print()">
                <i class="fa fa-print"></i>
                Print
            </button>

            <a href="{{ route('export.si.excel', $ipl->id) }}"
               class="btn btn-outline-success btn-sm">
                <i class="fa fa-file-excel"></i>
                Excel
            </a>

            <a href="{{ route('export.si.pdf', $ipl->id) }}"
               class="btn btn-outline-danger btn-sm">
                <i class="fa fa-file-pdf"></i>
                PDF
            </a>



            <a href="{{ url()->previous() }}"

               class="btn btn-light border btn-sm">

                Back

            </a>

        </div>

    </div>





    {{-- ==========================================================
         FLOATING COMPONENT NAVIGATION
    =========================================================== --}}
    <div class="si-floating-nav" id="siFloatingNav">

        <div class="si-floating-nav-inner">

            <button
                type="button"
                class="si-floating-toggle"
                id="siFloatingToggle"
                aria-label="Toggle SI navigation"
                title="Navigasi SI"
            >
                <span id="siFloatingToggleIcon">‹</span>
            </button>

            <div class="si-floating-title">
                SI NAVIGATION
            </div>

            <div class="si-floating-list">

                <button
                    type="button"
                    class="si-floating-item active"
                    data-target="si-top"
                >
                    01. Header / SI
                </button>

                <button
                    type="button"
                    class="si-floating-item"
                    data-target="si-shipping-instruction"
                >
                    02. Shipping Instruction
                </button>

                <button
                    type="button"
                    class="si-floating-item"
                    data-target="si-shipment-details"
                >
                    03. Shipment Details
                </button>

                <button
                    type="button"
                    class="si-floating-item"
                    data-target="si-shipper-consignee"
                >
                    04. Shipper / Consignee
                </button>

                <button
                    type="button"
                    class="si-floating-item"
                    data-target="si-transport-cargo"
                >
                    05. Transport & Cargo
                </button>

                <button
                    type="button"
                    class="si-floating-item"
                    data-target="si-footer"
                >
                    06. Footer / Signature
                </button>

            </div>

        </div>

    </div>


    {{-- PAPER --}}

    <div class="si-paper">



        {{-- COMPANY HEADER --}}

        <div class="si-company">

            <img
                src="{{ asset('assets/images/newwicker.jpg') }}"
                alt="PT Newwicker Indonesia"
                class="si-company-logo"
            >

            <div class="si-company-name">

                PT. NEWWICKER INDONESIA

            </div>



            <div class="si-company-line">

                JL. KISABA LANANG RT. 019 RW. 002,

            </div>



            <div class="si-company-line">

                BODELOR, PLUMBON, CIREBON 45155

            </div>



            <div class="si-company-line">

                INDONESIA

            </div>



            <div class="si-company-line">

                PHONE : 0231 - 325880 - export@newwicker@com

            </div>

        </div>





        {{-- TITLE --}}

        <div class="si-main-title si-nav-target" id="si-top">

            SHIPPING INSTRUCTION

            <small>(SHIPPER'S LETTER OF INSTRUCTION)</small>

        </div>





        {{-- ==========================================================

             TOP INFORMATION

             FIELD EXACTLY FROM EXCEL

        =========================================================== --}}

        <div class="si-info">



            <div class="si-info-cell">

                <div class="si-label">Date</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input type="text"
                           name="date" data-si-field="date"
                           value="{{ optional($ipl->date)->format('d/m/Y') }}"
                           placeholder="dd/mm/yyyy"
                           inputmode="numeric">

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">Booking No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="booking_no"
       data-si-field="booking_no"
       value="{{ $ipl->booking_no }}">

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">Shipping Forwarder</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="shipping_forwarder"
       data-si-field="shipping_forwarder"
       value="{{ $ipl->shipping_forwarder }}">

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">PEB No. & Date</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div style="display:flex;gap:5px;align-items:center;">
    <input name="peb_no"
           data-si-field="peb_no"
           value="{{ $ipl->peb_no }}"
           placeholder="PEB No.">
    <input type="text"
           name="peb_date"
           data-si-field="peb_date"
           value="{{ optional($ipl->peb_date)->format('d/m/Y') }}"
           placeholder="dd/mm/yyyy"
           inputmode="numeric">
</div>

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">Attn</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="attn"
       data-si-field="attn"
       value="{{ $ipl->attn }}">

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">KPBC No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="kpbc_no"
       data-si-field="kpbc_no"
       value="{{ $ipl->kpbc_no }}">

                </div>

            </div>



            <div class="si-info-cell">

                <div class="si-label"></div>

                <div class="si-colon"></div>

                <div class="si-value"></div>

            </div>



            <div class="si-info-cell">

                <div class="si-label">HS Code</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-hs-list">
                        @forelse($hsCodes as $hsCode)
                            <div class="si-hs-row">
                                {{ $hsCode }}
                            </div>
                        @empty
                            <div class="si-hs-row">-</div>
                        @endforelse
                    </div>

                </div>

            </div>



        </div>





        {{-- MAIN EXCEL TITLE --}}

        <div class="si-section si-nav-target" id="si-shipping-instruction">

            SHIPPING INSTRUCTION

        </div>





        {{-- DESCRIPTION --}}

        <div class="si-description">

            <div>

                We would appreciate it very much if you could help us in the shipment

                of our export commodities rattan furniture with the below description:

            </div>



            <div class="line">

                <strong>Documentation Original B/L to show:</strong>

            </div>

        </div>





        {{-- ==========================================================

             SHIPMENT / ORDER DETAILS

        =========================================================== --}}

        <div class="si-section si-nav-target" id="si-shipment-details">

            SHIPMENT DETAILS

        </div>



        <div class="si-simple-list">



            <div class="si-row">

                <div class="si-label">Quantity</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-text">{{ number_format($totalQty, 0, '.', ',') }}</div>

                    <span class="ms-1" style="font-size:9px; white-space:nowrap;">

                        CTNS OF Rattan Furnitures

                    </span>

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">Purchase Order No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-text">{{ $poNumbers ?: '-' }}</div>

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">Port of Loading</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="port_loading" data-si-field="port_loading"

                           value="{{ $ipl->port_loading }}">

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">Port of Destination</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="port_destination" data-si-field="port_discharge"

                           value="{{ $ipl->port_discharge }}">

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">L/C No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="lc_no"
                           data-si-field="lc_no"
                           value="{{ $ipl->lc_no }}">

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">Freight</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="freight"
                           data-si-field="freight"
                           value="{{ $ipl->freight }}">

                </div>

            </div>



            <div class="si-row">

                <div class="si-label">Contract No</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="contract_no"
                           data-si-field="contract_no"
                           value="{{ $ipl->contract_no }}">

                </div>

            </div>



        </div>





        {{-- ==========================================================

             SHIPPER / CONSIGNEE

        =========================================================== --}}

        <div class="si-section si-nav-target" id="si-shipper-consignee">

            SHIPPER / CONSIGNEE

        </div>



        <div class="si-two-col">



            <div class="si-box tall">

                <div class="si-label">Shipper</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <textarea name="shipper">PT. NEWWICKER INDONESIA

JL. KISABA LANANG RT. 019 RW. 002,

BODELOR, PLUMBON, CIREBON 45155

INDONESIA

Tel : +62 231 325880</textarea>

                </div>

            </div>



            <div class="si-box tall">

                <div class="si-label">Consignee</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <textarea name="consignee">{{ trim($ipl->buyer . "\n" . $ipl->buyer_address) }}</textarea>

                </div>

            </div>



            <div class="si-box medium">

                <div class="si-label">Notify Party</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <textarea name="notify_party"
                          data-si-field="notify_party"
                          rows="2">{{ $ipl->notify_party }}</textarea>

                </div>

            </div>



        </div>





        {{-- ==========================================================

             TRANSPORT / WEIGHT / CONTAINER

             FIELD EXACTLY FROM EXCEL

        =========================================================== --}}

        <div class="si-section si-nav-target" id="si-transport-cargo">

            TRANSPORT AND CARGO INFORMATION

        </div>



        <div class="si-two-col">



            <div class="si-box">

                <div class="si-label">Vessel Name</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="vessel_name" data-si-field="vessel_name"

                           value="{{ $ipl->vessel_name }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Connect to</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="connect_to"
                           data-si-field="connect_to"
                           value="{{ $ipl->connect_to }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">ETD</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input type="text"
                           name="etd" data-si-field="etd"
                           value="{{ optional($ipl->etd)->format('d/m/Y') }}"
                           placeholder="dd/mm/yyyy"
                           inputmode="numeric">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Bill of Lading</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="bill_of_lading" data-si-field="bill_of_lading"

                           value="{{ $ipl->bill_of_lading }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">TOTAL Nett Weight</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-text">{{ number_format($totalNet, 2, '.', '') }}</div>

                    <span class="ms-1" style="font-size:9px;">kgs</span>

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">TOTAL Gross Weight</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-text">{{ number_format($totalGross, 2, '.', '') }}</div>

                    <span class="ms-1" style="font-size:9px;">kgs</span>

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">TARE</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="tare"
                           data-si-field="tare"
                           value="{{ $ipl->tare }}">

                    <span class="ms-1" style="font-size:9px;">kgs</span>

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">VGM</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="vgm"
                           data-si-field="vgm"
                           value="{{ $ipl->vgm }}">

                    <span class="ms-1" style="font-size:9px;">kgs</span>

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">TOTAL Volume</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <div class="si-text">{{ number_format($totalCbm, 2, '.', '') }}</div>

                    <span class="ms-1" style="font-size:9px;">m3</span>

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Location</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="location" data-si-field="location"

                           value="{{ $ipl->location }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Stuffing Date</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input type="text"
                           name="stuffing_date"
                           data-si-field="stuffing_date"
                           value="{{ optional($ipl->stuffing_date)->format('d/m/Y') }}"
                           placeholder="dd/mm/yyyy"
                           inputmode="numeric">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">EMKL</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="emkl"
                           data-si-field="emkl"
                           value="{{ $ipl->emkl }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Fumigation</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="fumigation" data-si-field="fumigation"

                           value="{{ $ipl->fumigation }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Container Type</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="container_type" data-si-field="container_type"

                           value="{{ $ipl->container_type }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Container No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="container_no" data-si-field="container_no"

                           value="{{ $ipl->container_no }}">

                </div>

            </div>



            <div class="si-box">

                <div class="si-label">Seal No.</div>

                <div class="si-colon">:</div>

                <div class="si-value">

                    <input name="seal_no" data-si-field="seal_no"

                           value="{{ $ipl->seal_no }}">

                </div>

            </div>



        </div>





        {{-- FOOTER --}}

        <div class="si-footer-text si-nav-target" id="si-footer">

            Appreciate your kind cooperation

        </div>



        <div class="si-signature">

            Best Regards



            <div style="margin-top:20px;">

                PT NEWWICKER INDONESIA

            </div>



            <div class="si-signature-name">

                Sofian

            </div>

        </div>



    </div>



    <div class="si-actions">

        <button type="button"

                class="btn btn-primary btn-sm"

                onclick="window.print()">

            <i class="fa fa-print"></i>

            Print

        </button>

    </div>



</div>





<script>
document.addEventListener('DOMContentLoaded', function () {

    const floatingNav =
        document.getElementById('siFloatingNav');

    const floatingToggle =
        document.getElementById('siFloatingToggle');

    const floatingToggleIcon =
        document.getElementById('siFloatingToggleIcon');

    const navItems =
        document.querySelectorAll('.si-floating-item');

    let navOpen = false;


    /*
    |--------------------------------------------------------------------------
    | DEFAULT: MINIMIZED
    |--------------------------------------------------------------------------
    */

    floatingNav.classList.remove('open');

    floatingToggleIcon.textContent = '‹';


    /*
    |--------------------------------------------------------------------------
    | TOGGLE
    |--------------------------------------------------------------------------
    */

    floatingToggle.addEventListener('click', function () {

        navOpen = !navOpen;

        floatingNav.classList.toggle(
            'open',
            navOpen
        );

        floatingToggleIcon.textContent =
            navOpen ? '›' : '‹';

    });


    /*
    |--------------------------------------------------------------------------
    | CLICK NAV ITEM
    |--------------------------------------------------------------------------
    */

    navItems.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                const targetId =
                    this.dataset.target;

                const target =
                    document.getElementById(
                        targetId
                    );

                if (!target) {
                    return;
                }


                /*
                |----------------------------------------------------------
                | Active state
                |----------------------------------------------------------
                */

                navItems.forEach(function (item) {

                    item.classList.remove(
                        'active'
                    );

                });

                this.classList.add(
                    'active'
                );


                /*
                |----------------------------------------------------------
                | Smooth scroll
                |----------------------------------------------------------
                */

                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });


                /*
                |----------------------------------------------------------
                | Setelah memilih component,
                | navigasi boleh mengecil lagi.
                |----------------------------------------------------------
                */

                setTimeout(function () {

                    navOpen = false;

                    floatingNav.classList.remove(
                        'open'
                    );

                    floatingToggleIcon.textContent =
                        '‹';

                }, 250);

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | ACTIVE COMPONENT BERDASARKAN SCROLL
    |--------------------------------------------------------------------------
    */

    const targets = [
        'si-top',
        'si-shipping-instruction',
        'si-shipment-details',
        'si-shipper-consignee',
        'si-transport-cargo',
        'si-footer'
    ]
    .map(function (id) {

        return document.getElementById(id);

    })
    .filter(Boolean);


    const observer =
        new IntersectionObserver(
            function (entries) {

                /*
                | Cari target yang paling terlihat.
                */

                const visible =
                    entries
                        .filter(function (entry) {
                            return entry.isIntersecting;
                        })
                        .sort(function (a, b) {

                            return (
                                b.intersectionRatio
                                -
                                a.intersectionRatio
                            );

                        });


                if (!visible.length) {
                    return;
                }


                const currentId =
                    visible[0].target.id;


                navItems.forEach(function (item) {

                    item.classList.toggle(
                        'active',
                        item.dataset.target === currentId
                    );

                });

            },
            {
                root: null,

                rootMargin:
                    '-80px 0px -55% 0px',

                threshold: [
                    0.05,
                    0.2,
                    0.5
                ]
            }
        );


    targets.forEach(function (target) {

        observer.observe(target);

    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const updateUrl = @json(route('export.si.update', $ipl->id));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const status = document.getElementById('siSaveStatus');

    function showStatus(message) {
        if (!status) return;
        status.textContent = message;
        status.classList.add('show');
        clearTimeout(window.__siStatusTimer);
        window.__siStatusTimer = setTimeout(function () {
            status.classList.remove('show');
        }, 900);
    }

    const dateFields = [
        'date',
        'peb_date',
        'etd',
        'stuffing_date',
        'eta'
    ];

    function normalizeDateInput(value) {
        value = String(value || '').trim();

        if (!value) {
            return value;
        }

        // Support dd/mm/yyyy as the visible SI format.
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(value)) {
            const parts = value.split('/');
            return String(parts[0]).padStart(2, '0') + '/' +
                   String(parts[1]).padStart(2, '0') + '/' +
                   parts[2];
        }

        return value;
    }

    async function saveField(el) {

        const field = el.dataset.siField;
        if (!field) return;

        const value = el.value ?? '';
        const original = el.dataset.originalValue ?? '';

        if (String(value) === String(original)) return;

        el.classList.remove('si-saved', 'si-error');
        el.classList.add('si-saving');
        showStatus('Saving...');

        try {

            const response = await fetch(updateUrl, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                throw new Error(
                    'Server tidak mengembalikan JSON. HTTP ' + response.status
                );
            }

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || ('Gagal menyimpan. HTTP ' + response.status)
                );
            }

            el.dataset.originalValue = value;
            el.classList.remove('si-saving');
            el.classList.add('si-saved');
            showStatus('Saved');

            setTimeout(function () {
                el.classList.remove('si-saved');
            }, 700);

        } catch (error) {

            console.error('SI SAVE ERROR:', error);

            el.classList.remove('si-saving');
            el.classList.add('si-error');

            showStatus('Gagal menyimpan');

            alert(error.message || 'Gagal menyimpan data SI.');
        }
    }

    document.querySelectorAll('[data-si-field]').forEach(function (el) {

        el.dataset.originalValue = el.value ?? '';

        el.addEventListener('blur', function () {
            saveField(this);
        });

        // Enter pada input text: simpan dan pindah ke field berikutnya.
        el.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && this.tagName !== 'TEXTAREA') {
                event.preventDefault();
                this.blur();
            }
        });
    });

});
</script>

@endsection
