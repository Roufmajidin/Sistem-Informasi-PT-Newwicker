{{-- 
    PDF VERSION OF SI UI
    IMPORTANT:
    - Content / field mengikuti si.blade.php
    - Layout dibuat sama secara visual
    - CSS GRID/FLEX diganti TABLE karena DomPDF tidak merender CSS Grid secara konsisten.
--}}

@php
    $poNumbers = $ipl->items
        ->pluck('blde')
        ->filter()
        ->map(fn ($v) => trim((string) $v))
        ->unique()
        ->values()
        ->implode(' ; ');

    $hsCodes = $ipl->items
        ->pluck('hs_code')
        ->filter()
        ->map(fn ($v) => trim((string) $v))
        ->unique()
        ->values();

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

    $fmtDate = function ($date) {
        if (!$date) return '';
        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };

    $fmt = function ($value, $decimals = 2) {
        return number_format((float) ($value ?? 0), $decimals, '.', '');
    };
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Shipping Instruction</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        .si-page {
            width: auto;
            margin: 0 10mm;
            background: #fff;
        }

        /* =========================================================
           COMPANY HEADER
        ========================================================= */

        .si-company {
            width: 100%;
            line-height: 1.35;
            padding: 0 0 5px;
        }

        /*
         * Jangan gunakan position:absolute untuk logo di DomPDF.
         * DomPDF dapat memposisikannya relatif ke halaman, bukan header.
         * Gunakan table 3 kolom supaya logo benar-benar berada di kiri
         * header sementara nama perusahaan tetap benar-benar di tengah.
         */
        .si-company-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .si-company-logo-cell {
            width: 102px;
            text-align: left;
            vertical-align: middle;
        }

        .si-company-center-cell {
            width: auto;
            text-align: center;
            vertical-align: middle;
        }

        .si-company-spacer-cell {
            width: 72px;
        }

        .si-company-logo {
            display: block;
            width: 102px;
            height: auto;
        }

        .si-company-name {
            font-size: 15px;
            font-weight: 800;
        }

        .si-company-line {
            font-size: 9px;
        }

        /* =========================================================
           MAIN TITLE
        ========================================================= */

        .si-main-title {
            width: 100%;
            border: 2px solid #111;
            text-align: center;
            padding: 5px 8px 4px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.15;
        }

        .si-main-title small {
            display: block;
            font-size: 8px;
            margin-top: 2px;
            font-weight: 700;
        }

        /* =========================================================
           GENERIC 2-COLUMN FORM
        ========================================================= */

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .si-info {
            width: 100%;
            table-layout: fixed;
        }

        .si-info td {
            width: 50%;
            padding: 0;
            border-right: 1px solid #111;
            border-bottom: 1px solid #111;
            border-left: 1px solid #111;
            vertical-align: top;
        }

        .si-info td + td {
            border-left: 0;
        }

        .si-cell {
            width: 100%;
            min-height: 24px;
            display: table;
            table-layout: fixed;
        }

        .si-cell-label {
            display: table-cell;
            width: 125px;
            padding: 3px 5px;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: middle;
        }

        .si-cell-colon {
            display: table-cell;
            width: 12px;
            padding-top: 3px;
            text-align: center;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: top;
        }

        .si-cell-value {
            display: table-cell;
            padding: 3px 5px;
            font-size: 8.5px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .si-cell-value div {
            line-height: 1.35;
        }

        /* =========================================================
           SECTION
        ========================================================= */

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

        /* =========================================================
           DESCRIPTION
        ========================================================= */

        .si-description {
            border: 1px solid #111;
            border-top: 0;
            padding: 8px 10px;
            font-size: 9px;
            line-height: 1.55;
        }

        .si-description .line {
            margin-top: 6px;
        }

        /* =========================================================
           SIMPLE FULL-WIDTH ROWS
        ========================================================= */

        .si-simple-list {
            width: 100%;
            border-left: 1px solid #111;
            border-right: 1px solid #111;
        }

        .si-row {
            display: table;
            width: 100%;
            min-height: 24px;
            table-layout: fixed;
            border-bottom: 1px solid #111;
        }

        .si-row-label {
            display: table-cell;
            width: 155px;
            padding: 3px 5px;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: middle;
        }

        .si-row-colon {
            display: table-cell;
            width: 12px;
            padding-top: 3px;
            text-align: center;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: top;
        }

        .si-row-value {
            display: table-cell;
            padding: 3px 5px;
            font-size: 8.5px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .si-text {
            font-size: 9px;
            line-height: 1.35;
            white-space: pre-line;
        }

        /* =========================================================
           SHIPPER / CONSIGNEE / NOTIFY
        ========================================================= */

        .si-two-col {
            width: 100%;
            table-layout: fixed;
        }

        .si-two-col > tbody > tr > td {
            width: 50%;
            padding: 0;
            vertical-align: top;
            border-left: 1px solid #111;
            border-bottom: 1px solid #111;
            border-right: 1px solid #111;
        }

        .si-two-col > tbody > tr > td + td {
            border-left: 0;
        }

        .si-box {
            width: 100%;
            min-height: 24px;
            display: table;
            table-layout: fixed;
        }

        .si-box-label {
            display: table-cell;
            width: 125px;
            padding: 3px 5px;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: top;
        }

        .si-box-colon {
            display: table-cell;
            width: 12px;
            padding-top: 3px;
            text-align: center;
            font-size: 8.5px;
            font-weight: 700;
            vertical-align: top;
        }

        .si-box-value {
            display: table-cell;
            padding: 3px 5px;
            font-size: 8.5px;
            vertical-align: top;
            word-wrap: break-word;
            line-height: 1.35;
        }

        .si-box.tall {
            min-height: 78px;
        }

        .si-box.medium {
            min-height: 48px;
        }

        .si-box-value.multiline {
            white-space: pre-line;
        }

        /* =========================================================
           PRINT / PAGE BREAK
        ========================================================= */

        .avoid-break {
            page-break-inside: avoid;
        }

        .si-footer-text {
            padding: 7px 2px 3px;
            font-size: 9px;
        }

        .si-signature {
            margin-top: 10px;
            font-size: 9px;
            page-break-inside: avoid;
        }

        .si-signature-name {
            margin-top: 30px;
            font-weight: 700;
        }
    </style>
</head>

<body>
<div class="si-page">

    {{-- COMPANY HEADER --}}
    <div class="si-company">
        <table class="si-company-table">
            <tr>
                <td class="si-company-logo-cell">
                    <img
                        src="{{ public_path('assets/images/newwicker.jpg') }}"
                        class="si-company-logo"
                    >
                </td>

                <td class="si-company-center-cell">
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
                        PHONE : 0231 - 325880 - export@newwicker.com
                    </div>
                </td>

                <td class="si-company-spacer-cell"></td>
            </tr>
        </table>
    </div>

    {{-- TITLE --}}
    <div class="si-main-title">
        SHIPPING INSTRUCTION
        <small>(SHIPPER'S LETTER OF INSTRUCTION)</small>
    </div>

    {{-- =========================================================
         TOP INFORMATION
    ========================================================== --}}
    <table class="si-info avoid-break">
        <tr>
            <td>
                <div class="si-cell">
                    <div class="si-cell-label">Date</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $fmtDate($ipl->date) }}
                    </div>
                </div>
            </td>

            <td>
                <div class="si-cell">
                    <div class="si-cell-label">Booking No.</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $ipl->booking_no }}
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-cell">
                    <div class="si-cell-label">Shipping Forwarder</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $ipl->shipping_forwarder }}
                    </div>
                </div>
            </td>

            <td>
                <div class="si-cell">
                    <div class="si-cell-label">PEB No. &amp; Date</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $ipl->peb_no }}
                        @if($ipl->peb_date)
                            &nbsp;&nbsp;{{ $fmtDate($ipl->peb_date) }}
                        @endif
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-cell">
                    <div class="si-cell-label">Attn</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $ipl->attn }}
                    </div>
                </div>
            </td>

            <td>
                <div class="si-cell">
                    <div class="si-cell-label">KPBC No.</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        {{ $ipl->kpbc_no }}
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-cell">
                    <div class="si-cell-label"></div>
                    <div class="si-cell-colon"></div>
                    <div class="si-cell-value"></div>
                </div>
            </td>

            <td>
                <div class="si-cell">
                    <div class="si-cell-label">HS Code</div>
                    <div class="si-cell-colon">:</div>
                    <div class="si-cell-value">
                        @forelse($hsCodes as $hsCode)
                            <div>{{ $hsCode }}</div>
                        @empty
                            -
                        @endforelse
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SHIPPING INSTRUCTION --}}
    <div class="si-section">
        SHIPPING INSTRUCTION
    </div>

    <div class="si-description">
        <div>
            We would appreciate it very much if you could help us in the shipment
            of our export commodities rattan furniture with the below description:
        </div>

        <div class="line">
            <strong>Documentation Original B/L to show:</strong>
        </div>
    </div>

    {{-- SHIPMENT DETAILS --}}
    <div class="si-section">
        SHIPMENT DETAILS
    </div>

    <div class="si-simple-list avoid-break">

        <div class="si-row">
            <div class="si-row-label">Quantity</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                <span class="si-text">
                    {{ number_format($totalQty, 0, '.', ',') }}
                </span>
                <span style="font-size:9px;">
                    CTNS OF Rattan Furnitures
                </span>
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">Purchase Order No.</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                <span class="si-text">{{ $poNumbers ?: '-' }}</span>
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">Port of Loading</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                {{ $ipl->port_loading }}
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">Port of Destination</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                {{ $ipl->port_discharge }}
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">L/C No.</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                {{ $ipl->lc_no }}
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">Freight</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                {{ $ipl->freight }}
            </div>
        </div>

        <div class="si-row">
            <div class="si-row-label">Contract No</div>
            <div class="si-row-colon">:</div>
            <div class="si-row-value">
                {{ $ipl->contract_no }}
            </div>
        </div>

    </div>

    {{-- SHIPPER / CONSIGNEE --}}
    <div class="si-section">
        SHIPPER / CONSIGNEE
    </div>

    <table class="si-two-col avoid-break">
        <tr>
            <td>
                <div class="si-box tall">
                    <div class="si-box-label">Shipper</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value multiline">PT. NEWWICKER INDONESIA
JL. KISABA LANANG RT. 019 RW. 002,
BODELOR, PLUMBON, CIREBON 45155
INDONESIA
Tel : +62 231 325880</div>
                </div>
            </td>

            <td>
                <div class="si-box tall">
                    <div class="si-box-label">Consignee</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value multiline">{{ trim($ipl->buyer . "\n" . $ipl->buyer_address) }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box medium">
                    <div class="si-box-label">Notify Party</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value multiline">{{ $ipl->notify_party }}</div>
                </div>
            </td>

            <td>
                {{-- empty cell intentionally, exactly like the 2-column UI --}}
            </td>
        </tr>
    </table>

    {{-- TRANSPORT --}}
    <div class="si-section">
        TRANSPORT AND CARGO INFORMATION
    </div>

    <table class="si-two-col">
        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">Vessel Name</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->vessel_name }}</div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">Connect to</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->connect_to }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">ETD</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $fmtDate($ipl->etd) }}</div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">Bill of Lading</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->bill_of_lading }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">TOTAL Nett Weight</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">
                        {{ $fmt($totalNet, 2) }} <span>kgs</span>
                    </div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">TOTAL Gross Weight</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">
                        {{ $fmt($totalGross, 2) }} <span>kgs</span>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">TARE</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">
                        {{ $ipl->tare }} <span>kgs</span>
                    </div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">VGM</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">
                        {{ $ipl->vgm }} <span>kgs</span>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">TOTAL Volume</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">
                        {{ $fmt($totalCbm, 2) }} <span>m3</span>
                    </div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">Location</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->location }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">Stuffing Date</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $fmtDate($ipl->stuffing_date) }}</div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">EMKL</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->emkl }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">Fumigation</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->fumigation }}</div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">Container Type</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->container_type }}</div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="si-box">
                    <div class="si-box-label">Container No.</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->container_no }}</div>
                </div>
            </td>

            <td>
                <div class="si-box">
                    <div class="si-box-label">Seal No.</div>
                    <div class="si-box-colon">:</div>
                    <div class="si-box-value">{{ $ipl->seal_no }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="si-footer-text">
        Appreciate your kind cooperation
    </div>

    <div class="si-signature">
        Best Regards

        <div style="margin-top:10px;">
            PT NEWWICKER INDONESIA
        </div>

        <div class="si-signature-name">
            Sofian
        </div>
    </div>

</div>
</body>
</html>
