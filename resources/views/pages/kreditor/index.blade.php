@extends('master.master')

@section('content')

<style>
/* =========================================================
   PAGE
========================================================= */

.creditor-page {
    padding: 5px 8px 20px;
    background: #f5f6f8;
    min-height: calc(100vh - 60px);
}


/* =========================================================
   SUMMARY
========================================================= */

.creditor-summary {
    display: flex;
    justify-content: flex-end;
    align-items: stretch;
    gap: 4px;
    margin-bottom: 5px;
    flex-wrap: wrap;
}

.summary-box {
    min-width: 125px;
    background: #fff;
    border: 1px solid #9ca6b0;
}

.summary-title {
    background: #30445f;
    color: #fff;
    text-align: center;
    font-size: 7px;
    line-height: 11px;

    padding: 1px 4px;
}

.summary-value {
    text-align: right;
    font-size: 11px;
    line-height: 16px;

    padding: 1px 5px;
}


/* =========================================================
   TABLE WRAPPER
========================================================= */

.creditor-table-wrapper {
    width: 100%;

    /*
    |--------------------------------------------------------------------------
    | Tinggi area tabel
    |--------------------------------------------------------------------------
    */

    max-height: calc(100vh - 100px);

    /*
    |--------------------------------------------------------------------------
    | Horizontal + vertical scroll
    |--------------------------------------------------------------------------
    */

    overflow: auto;

    position: relative;

    background: #fff;

    border: 1px solid #8e99a5;
}


/* =========================================================
   TABLE
========================================================= */

.creditor-table {
    width: 100%;

    /*
    |--------------------------------------------------------------------------
    | Agar horizontal scroll tetap tersedia
    |--------------------------------------------------------------------------
    */

    min-width: 1500px;

    /*
    |--------------------------------------------------------------------------
    | PENTING
    |--------------------------------------------------------------------------
    | separate membantu sticky column bekerja lebih stabil
    |--------------------------------------------------------------------------
    */

    border-collapse: separate;
    border-spacing: 0;

    table-layout: fixed;

    font-size: 8px;
}


/* =========================================================
   CELL
========================================================= */

.creditor-table th,
.creditor-table td {
    border-right: 1px solid #8e99a5;
    border-bottom: 1px solid #8e99a5;

    padding: 2px 4px;

    height: 20px;

    line-height: 13px;

    vertical-align: middle;
}


/* =========================================================
   COLUMN WIDTH
========================================================= */

.col-no {
    width: 32px;
}

.col-kategori {
    width: 65px;
}

.col-tgl {
    width: 78px;
}

.col-spk {
    width: 175px;
}

.col-po {
    width: 90px;
}

.col-jt {
    width: 85px;
}

.col-supplier {
    width: 165px;
}

.col-pembelian {
    width: 105px;
}

.col-bahan {
    width: 105px;
}

.col-pembayaran {
    width: 115px;
}

.col-saldo {
    width: 110px;
}

.col-term {
    width: 80px;
}


/* =========================================================
   STICKY HEADER
========================================================= */

.creditor-table thead th {
    position: sticky;

    top: 0;

    z-index: 100;

    background: #30445f;

    color: #fff;

    text-align: center;

    font-size: 8px;



    white-space: normal;

    height: 28px;

    padding: 3px 4px;
}


/* =========================================================
   FREEZE NO INVOICE / SPK
=========================================================

   Kolom:
   1. NO        = 32
   2. KATEGORI  = 65
   3. TGL       = 78

   Total:
   32 + 65 + 78 = 175px

   Jadi kolom ke-4 dimulai dari 175px.
========================================================= */

.creditor-table tbody td:nth-child(4) {

    position: sticky;

    left: 175px;

    z-index: 40;

    background: #fff;

    box-shadow:
        2px 0 4px rgba(0, 0, 0, .14);
}


/* =========================================================
   FREEZE HEADER NO INVOICE / SPK
========================================================= */

.creditor-table thead th:nth-child(4) {

    position: sticky;

    top: 0;

    left: 175px;

    z-index: 150;

    background: #30445f;

    color: #fff;

    box-shadow:
        2px 0 4px rgba(0, 0, 0, .20);
}


/* =========================================================
   BODY
========================================================= */

.creditor-table tbody td {
    background: #fff;
}

.creditor-table tbody tr:hover td {
    background: #f6f8fb;
}


/*
|--------------------------------------------------------------------------
| Background kolom freeze ketika hover
|--------------------------------------------------------------------------
*/

.creditor-table tbody tr:hover td:nth-child(4) {
    background: #f6f8fb;
}


/* =========================================================
   FOOTER
========================================================= */

.creditor-table tfoot td {
    background: #e7ecf2;



    border-top: 2px solid #30445f;

    position: static;
}


/* =========================================================
   TEXT
========================================================= */

.text-center {
    text-align: center !important;
}

.text-end {
    text-align: right !important;
}

.money {
    text-align: right;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.muted {
    color: #999;
}


/* =========================================================
   SUPPLIER
========================================================= */

.supplier-name {
    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/* =========================================================
   SPK
========================================================= */

.spk-number {
    display: block;



    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =========================================================
   SALDO
========================================================= */

.saldo-negative {
    color: #d32f2f;


}


/* =========================================================
   TERM
========================================================= */

.term-lunas {
    color: #2e7d32;

    /* */
}

.term-belum {
    color: #c62828;

    /* */
}


/* =========================================================
   PAYMENT HOVER
========================================================= */

.payment-wrapper,
.bahan-wrapper {
    display: inline-block;

    position: relative;
}

.payment-value,
.bahan-value {
    cursor: help;

    /* */
}

.payment-value:hover,
.bahan-value:hover {
    text-decoration: underline;
}


/* =========================================================
   TOOLTIP GENERAL
========================================================= */

.credit-tooltip,
.bahan-tooltip {

    display: none;

    position: fixed;

    width: 370px;

    max-width: calc(100vw - 20px);

    max-height: 70vh;

    overflow-y: auto;

    background: #fff;

    border: 1px solid #aab4c0;

    border-radius: 5px;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, .25);

    padding: 9px;

    z-index: 2147483647;

    text-align: left;

    white-space: normal;

    font-size: 9px;
}

.credit-tooltip.show,
.bahan-tooltip.show {
    display: block;
}


/* =========================================================
   TOOLTIP TITLE
========================================================= */

.tooltip-title {
    color: #30445f;

    font-size: 10px;

    /* */

    padding-bottom: 6px;

    margin-bottom: 5px;

    border-bottom: 1px solid #d9dee5;
}


/* =========================================================
   TOOLTIP SECTION
========================================================= */

.tooltip-section {
    margin-top: 7px;
}

.tooltip-section-title {

    background: #eef2f6;

    padding: 3px 5px;

    color: #30445f;

    font-size: 8px;



    border-left: 3px solid #30445f;
}


/* =========================================================
   TOOLTIP ITEM
========================================================= */

.tooltip-item {

    padding: 5px 2px;

    border-bottom: 1px solid #edf0f3;
}

.tooltip-item:last-child {
    border-bottom: none;
}


/* =========================================================
   TOOLTIP ROW
========================================================= */

.tooltip-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    line-height: 15px;
}

.tooltip-row span {
    color: #777;
}

.tooltip-row strong {
    white-space: nowrap;
}


/* =========================================================
   BEFORE ERP
========================================================= */

.before-erp {
    color: #9a6700 !important;
}

.before-erp-badge {

    display: inline-block;

    margin-left: 3px;

    padding: 1px 4px;

    background: #f0b429;

    color: #fff;

    border-radius: 3px;

    font-size: 7px;


}


/* =========================================================
   RECON
========================================================= */

.recon-value {
    color: #2e7d32 !important;
}

.recon-badge {

    display: inline-block;

    margin-left: 3px;

    padding: 1px 4px;

    background: #2e7d32;

    color: #fff;

    border-radius: 3px;

    font-size: 7px;


}


/* =========================================================
   TOOLTIP TOTAL
========================================================= */

.tooltip-total {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-top: 2px solid #30445f;

    margin-top: 7px;

    padding-top: 7px;

    font-size: 10px;


}

.tooltip-total strong {
    color: #1e5aa8;
}


/* =========================================================
   BAHAN
========================================================= */

.bahan-tooltip {
    width: 340px;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-row td {

    height: 60px;

    text-align: center;

    color: #888;
}


/* =========================================================
   SCROLLBAR
========================================================= */

.creditor-table-wrapper::-webkit-scrollbar {
    width: 9px;

    height: 9px;
}

.creditor-table-wrapper::-webkit-scrollbar-track {
    background: #e5e9ed;
}

.creditor-table-wrapper::-webkit-scrollbar-thumb {

    background: #9aa4af;

    border-radius: 5px;
}

.creditor-table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #737e89;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .creditor-summary {
        justify-content: flex-start;
    }

    .summary-box {
        min-width: 110px;
    }

}
</style>


<div class="creditor-page">


    {{-- =====================================================
         SUMMARY
    ====================================================== --}}

    <div class="creditor-summary">


        {{-- TOTAL PEMBELIAN --}}

        <div class="summary-box">

            <div class="summary-title">
                TOTAL PEMBELIAN
            </div>

            <div class="summary-value">

                Rp
                {{ number_format(
                    $totalSpk ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>


        {{-- POTONGAN BAHAN --}}

        <div class="summary-box">

            <div class="summary-title">
                POT. BAHAN
            </div>

            <div class="summary-value">

                Rp
                {{ number_format(
                    $totalPotonganBahan ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>


        {{-- SEBELUM ERP --}}

        <div class="summary-box">

            <div class="summary-title">
                SEBELUM ERP
            </div>

            <div class="summary-value">

                Rp
                {{ number_format(
                    $totalSebelumErp ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>


        {{-- TOTAL PEMBAYARAN --}}

        <div class="summary-box">

            <div class="summary-title">
                TOTAL PEMBAYARAN
            </div>

            <div class="summary-value">

                Rp
                {{ number_format(
                    $totalPembayaran ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>


        {{-- TOTAL HUTANG --}}

        <div class="summary-box">

            <div class="summary-title">
                TOTAL HUTANG
            </div>

            <div class="summary-value">

                Rp
                {{ number_format(
                    $totalHutang ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </div>

        </div>

    </div>



    {{-- =====================================================
         TABLE
    ====================================================== --}}

    <div class="creditor-table-wrapper">

        <table class="creditor-table">


            {{-- =================================================
                 COLUMN WIDTH
            ================================================== --}}

            <colgroup>

                <col class="col-no">

                <col class="col-kategori">

                <col class="col-tgl">

                <col class="col-spk">

                <col class="col-po">

                <col class="col-jt">

                <col class="col-supplier">

                <col class="col-pembelian">

                <col class="col-bahan">

                <col class="col-pembayaran">

                <col class="col-saldo">

                <col class="col-term">

            </colgroup>



            {{-- =================================================
                 HEADER
            ================================================== --}}

            <thead>

                <tr>

                    <th>
                        NO
                    </th>

                    <th>
                        KATEGORI
                    </th>

                    <th>
                        TGL<br>
                        TERIMA
                    </th>

                    <th>
                        NO INVOICE / SPK
                    </th>

                    <th>
                        NO PO
                    </th>

                    <th>
                        TANGGAL JT
                    </th>

                    <th>
                        NAMA SUPPLIER / PENGESUB
                    </th>

                    <th>
                        PEMBELIAN
                    </th>

                    <th>
                        POT.<br>
                        BAHAN
                    </th>

                    <th>
                        PEMBAYARAN
                    </th>

                    <th>
                        SALDO AKHIR
                    </th>

                    <th>
                        TERM
                    </th>

                </tr>

            </thead>



            {{-- =================================================
                 BODY
            ================================================== --}}

            <tbody>


                @forelse (
                    $rows ?? []
                    as $item
                )


                    @php

                        $pembelian =
                            (float) (
                                $item['pembelian']
                                ?? 0
                            );

                        $potonganBahan =
                            (float) (
                                $item['potongan_bahan']
                                ?? 0
                            );

                        $pembayaran =
                            (float) (
                                $item['pembayaran']
                                ?? 0
                            );

                        $saldo =
                            (float) (
                                $item['saldo_akhir']
                                ?? 0
                            );

                        $sebelumErp =
                            (float) (
                                $item['sebelum_erp']
                                ?? 0
                            );

                        $paymentDetails =
                            $item['payment_details']
                            ?? [];

                        $timelineBahan =
                            $item['timeline_bahan']
                            ?? [];

                        $hasPaymentDetails =
                            !empty(
                                $paymentDetails
                            );

                        $hasBahan =
                            !empty(
                                $timelineBahan
                            );

                    @endphp



                    <tr>


                        {{-- NO --}}

                        <td class="text-center">

                            {{
                                $item['no']
                                ?? $loop->iteration
                            }}

                        </td>


                        {{-- KATEGORI --}}

                        <td class="text-center">

                            {{
                                strtoupper(
                                    $item['kategori']
                                    ?? 'SPK'
                                )
                            }}

                        </td>


                        {{-- TGL TERIMA --}}

                        <td class="text-center">

                            {{
                                $item['tgl_invoice']
                                ?? '-'
                            }}

                        </td>


                        {{-- =================================================
                             NO INVOICE / SPK
                        ================================================== --}}

                        <td>

                            <span
                                class="spk-number"
                                title="{{ $item['no_spk'] ?? '' }}"
                            >

                                {{
                                    $item['no_spk']
                                    ?? '-'
                                }}

                            </span>

                        </td>


                        {{-- NO PO --}}

                        <td class="text-center">

                            {{
                                $item['no_po']
                                ?? '-'
                            }}

                        </td>


                        {{-- TANGGAL JT --}}

                        <td class="text-center">

                            {{
                                $item['tanggal_jt']
                                ?? '-'
                            }}

                        </td>


                        {{-- SUPPLIER --}}

                        <td>

                            <span
                                class="supplier-name"
                                title="{{ $item['supplier'] ?? '' }}"
                            >

                                {{
                                    $item['supplier']
                                    ?? '-'
                                }}

                            </span>

                        </td>


                        {{-- PEMBELIAN --}}

                        <td class="money">

                            @if (
                                $pembelian > 0
                            )

                                {{
                                    number_format(
                                        $pembelian,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             POTONGAN BAHAN
                        ================================================== --}}

                        <td class="money">


                            @if (
                                $potonganBahan > 0
                            )


                                <div
                                    class="bahan-wrapper"
                                >


                                    <span
                                        class="bahan-value"
                                    >

                                        {{
                                            number_format(
                                                $potonganBahan,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}

                                    </span>



                                    @if (
                                        $hasBahan
                                    )


                                        <div
                                            class="bahan-tooltip"
                                        >


                                            <div
                                                class="tooltip-title"
                                            >

                                                Timeline
                                                Potongan Bahan

                                            </div>



                                            @foreach (
                                                $timelineBahan
                                                as $bahan
                                            )


                                                @php

                                                    $nominal =
                                                        (float) (
                                                            $bahan['nominal']
                                                            ?? 0
                                                        );

                                                    $adjustment =
                                                        (float) (
                                                            $bahan['adjustment']
                                                            ?? 0
                                                        );

                                                @endphp


                                                <div
                                                    class="tooltip-item"
                                                >


                                                    <div
                                                        class="tooltip-row"
                                                    >

                                                        <span>
                                                            Tanggal
                                                        </span>

                                                        <strong>

                                                            {{
                                                                $bahan['date']
                                                                ?? '-'
                                                            }}

                                                        </strong>

                                                    </div>



                                                    <div
                                                        class="tooltip-row"
                                                    >

                                                        <span>
                                                            Nominal
                                                        </span>

                                                        <strong>

                                                            Rp
                                                            {{
                                                                number_format(
                                                                    $nominal,
                                                                    0,
                                                                    ',',
                                                                    '.'
                                                                )
                                                            }}

                                                        </strong>

                                                    </div>



                                                    @if (
                                                        $adjustment > 0
                                                    )

                                                        <div
                                                            class="tooltip-row"
                                                        >

                                                            <span>
                                                                Adjustment
                                                            </span>

                                                            <strong>

                                                                Rp
                                                                {{
                                                                    number_format(
                                                                        $adjustment,
                                                                        0,
                                                                        ',',
                                                                        '.'
                                                                    )
                                                                }}

                                                            </strong>

                                                        </div>

                                                    @endif



                                                    <div
                                                        style="
                                                            margin-top:3px;
                                                            color:#777;
                                                            font-size:8px;
                                                            text-transform:uppercase;
                                                        "
                                                    >

                                                        {{
                                                            $bahan['note']
                                                            ?? 'BAHAN'
                                                        }}

                                                    </div>


                                                </div>


                                            @endforeach



                                            <div
                                                class="tooltip-total"
                                            >

                                                <span>
                                                    TOTAL
                                                </span>

                                                <strong>

                                                    Rp
                                                    {{
                                                        number_format(
                                                            $potonganBahan,
                                                            0,
                                                            ',',
                                                            '.'
                                                        )
                                                    }}

                                                </strong>

                                            </div>


                                        </div>


                                    @endif


                                </div>


                            @else


                                <span class="muted">
                                    -
                                </span>


                            @endif


                        </td>



                        {{-- =================================================
                             PEMBAYARAN
                        ================================================== --}}

                        <td class="money">


                            @if (
                                $pembayaran > 0
                            )


                                <div
                                    class="payment-wrapper"
                                >


                                    <span
                                        class="payment-value"
                                    >

                                        {{
                                            number_format(
                                                $pembayaran,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}

                                    </span>



                                    @if (
                                        $hasPaymentDetails
                                    )


                                        <div
                                            class="credit-tooltip"
                                        >


                                            <div
                                                class="tooltip-title"
                                            >

                                                Detail Pembayaran SPK

                                            </div>



                                            {{-- =================================================
                                                 SEBELUM ERP
                                            ================================================== --}}

                                            @php

                                                $beforeItems =
                                                    collect(
                                                        $paymentDetails
                                                    )->filter(
                                                        function (
                                                            $payment
                                                        ) {

                                                            return
                                                                ($payment[
                                                                    'is_sebelum_erp'
                                                                ] ?? false)
                                                                === true;

                                                        }
                                                    );

                                            @endphp



                                            @if (
                                                $beforeItems->isNotEmpty()
                                            )


                                                <div
                                                    class="tooltip-section"
                                                >


                                                    <div
                                                        class="tooltip-section-title before-erp"
                                                    >

                                                        SEBELUM ERP

                                                    </div>



                                                    @foreach (
                                                        $beforeItems
                                                        as $payment
                                                    )


                                                        <div
                                                            class="tooltip-item"
                                                        >


                                                            <div
                                                                class="tooltip-row"
                                                            >


                                                                <span>

                                                                    {{
                                                                        $payment['ket']
                                                                        ?? 'PAYMENT'
                                                                    }}


                                                                    <span
                                                                        class="before-erp-badge"
                                                                    >

                                                                        SEBELUM ERP

                                                                    </span>

                                                                </span>



                                                                <strong
                                                                    class="before-erp"
                                                                >

                                                                    Rp
                                                                    {{
                                                                        number_format(
                                                                            $payment['nominal']
                                                                            ?? 0,
                                                                            0,
                                                                            ',',
                                                                            '.'
                                                                        )
                                                                    }}

                                                                </strong>


                                                            </div>


                                                        </div>


                                                    @endforeach


                                                </div>


                                            @endif



                                            {{-- =================================================
                                                 PENGAJUAN
                                            ================================================== --}}

                                            @php

                                                $requestItems =
                                                    collect(
                                                        $paymentDetails
                                                    )->filter(
                                                        function (
                                                            $payment
                                                        ) {

                                                            return
                                                                ($payment[
                                                                    'is_sebelum_erp'
                                                                ] ?? false)
                                                                !== true;

                                                        }
                                                    );

                                            @endphp



                                            @if (
                                                $requestItems->isNotEmpty()
                                            )


                                                <div
                                                    class="tooltip-section"
                                                >


                                                    <div
                                                        class="tooltip-section-title"
                                                    >

                                                        PEMBAYARAN PENGAJUAN

                                                    </div>



                                                    @foreach (
                                                        $requestItems
                                                        as $payment
                                                    )


                                                        <div
                                                            class="tooltip-item"
                                                        >


                                                            <div
                                                                class="tooltip-row"
                                                            >


                                                                <span>

                                                                    @if (
                                                                        !empty(
                                                                            $payment[
                                                                                'request_no'
                                                                            ]
                                                                        )
                                                                    )

                                                                        {{
                                                                            $payment[
                                                                                'request_no'
                                                                            ]
                                                                        }}

                                                                    @else

                                                                        {{
                                                                            $payment[
                                                                                'ket'
                                                                            ]
                                                                            ?? 'PAYMENT'
                                                                        }}

                                                                    @endif


                                                                </span>



                                                                <strong
                                                                    class="{{
                                                                        ($payment['is_recon'] ?? false)
                                                                            ? 'recon-value'
                                                                            : ''
                                                                    }}"
                                                                >

                                                                    Rp
                                                                    {{
                                                                        number_format(
                                                                            $payment['nominal']
                                                                            ?? 0,
                                                                            0,
                                                                            ',',
                                                                            '.'
                                                                        )
                                                                    }}



                                                                    @if (
                                                                        ($payment['is_recon'] ?? false)
                                                                    )

                                                                        <span
                                                                            class="recon-badge"
                                                                        >

                                                                            RECON

                                                                        </span>

                                                                    @endif


                                                                </strong>


                                                            </div>



                                                            @if (
                                                                !empty(
                                                                    $payment[
                                                                        'request_date'
                                                                    ]
                                                                )
                                                            )

                                                                <div
                                                                    style="
                                                                        color:#999;
                                                                        font-size:8px;
                                                                    "
                                                                >

                                                                    Tgl:
                                                                    {{
                                                                        $payment[
                                                                            'request_date'
                                                                        ]
                                                                    }}

                                                                </div>

                                                            @endif



                                                            <div
                                                                style="
                                                                    color:#777;
                                                                    font-size:8px;
                                                                    text-transform:uppercase;
                                                                "
                                                            >

                                                                {{
                                                                    $payment[
                                                                        'ket'
                                                                    ]
                                                                    ?? '-'
                                                                }}

                                                            </div>


                                                        </div>


                                                    @endforeach


                                                </div>


                                            @endif



                                            {{-- =================================================
                                                 TOTAL
                                            ================================================== --}}

                                            <div
                                                class="tooltip-total"
                                            >

                                                <span>
                                                    TOTAL PEMBAYARAN
                                                </span>

                                                <strong>

                                                    Rp
                                                    {{
                                                        number_format(
                                                            $pembayaran,
                                                            0,
                                                            ',',
                                                            '.'
                                                        )
                                                    }}

                                                </strong>

                                            </div>



                                            @if (
                                                $sebelumErp > 0
                                            )


                                                <div
                                                    class="tooltip-row"
                                                    style="
                                                        margin-top:5px;
                                                        color:#9a6700;
                                                        font-size:8px;
                                                    "
                                                >

                                                    <span>
                                                        Termasuk Sebelum ERP
                                                    </span>

                                                    <strong>

                                                        Rp
                                                        {{
                                                            number_format(
                                                                $sebelumErp,
                                                                0,
                                                                ',',
                                                                '.'
                                                            )
                                                        }}

                                                    </strong>

                                                </div>


                                            @endif


                                        </div>


                                    @endif


                                </div>


                            @else


                                <span class="muted">
                                    -
                                </span>


                            @endif


                        </td>



                        {{-- =================================================
                             SALDO
                        ================================================== --}}

                        <td class="money">


                            @if (
                                $saldo != 0
                            )


                                <strong
                                    class="{{
                                        $saldo < 0
                                            ? 'saldo-negative'
                                            : ''
                                    }}"
                                >


                                    @if (
                                        $saldo < 0
                                    )
                                        (
                                    @endif


                                    {{
                                        number_format(
                                            abs($saldo),
                                            0,
                                            ',',
                                            '.'
                                        )
                                    }}


                                    @if (
                                        $saldo < 0
                                    )
                                        )
                                    @endif


                                </strong>


                            @else


                                <span class="muted">
                                    -
                                </span>


                            @endif


                        </td>



                        {{-- =================================================
                             TERM
                        ================================================== --}}

                        <td class="text-center">


                            @if (
                                strtolower(
                                    trim(
                                        $item['term']
                                        ?? ''
                                    )
                                ) === 'lunas'
                            )


                                <span
                                    class="term-lunas"
                                >
                                    Lunas
                                </span>


                            @else


                                <span
                                    class="term-belum"
                                >
                                    Belum Lunas
                                </span>


                            @endif


                        </td>


                    </tr>


                @empty


                    <tr
                        class="empty-row"
                    >

                        <td
                            colspan="12"
                        >

                            Tidak ada data kreditor.

                        </td>

                    </tr>


                @endforelse


            </tbody>



            {{-- =================================================
                 FOOTER
            ================================================== --}}

            @if (
                isset($rows)
                &&
                count($rows) > 0
            )


                <tfoot>

                    <tr>


                        <td
                            colspan="7"
                            class="footer-label"
                        >

                            TOTAL

                        </td>



                        {{-- PEMBELIAN --}}

                        <td class="money">

                            {{
                                number_format(
                                    $totalSpk ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>



                        {{-- POTONGAN BAHAN --}}

                        <td class="money">

                            {{
                                number_format(
                                    $totalPotonganBahan ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>



                        {{-- PEMBAYARAN --}}

                        <td class="money">

                            {{
                                number_format(
                                    $totalPembayaran ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>



                        {{-- SALDO --}}

                        <td class="money">

                            {{
                                number_format(
                                    $totalHutang ?? 0,
                                    0,
                                    ',',
                                    '.'
                                )
                            }}

                        </td>



                        <td></td>


                    </tr>

                </tfoot>


            @endif


        </table>

    </div>

</div>



<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | GENERIC TOOLTIP
        |--------------------------------------------------------------------------
        */

        function setupTooltip(
            wrapperSelector,
            tooltipSelector
        ) {


            const wrappers =
                document.querySelectorAll(
                    wrapperSelector
                );


            wrappers.forEach(
                function (wrapper) {


                    const tooltip =
                        wrapper.querySelector(
                            tooltipSelector
                        );


                    if (!tooltip) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | POSITION
                    |--------------------------------------------------------------------------
                    */

                    function positionTooltip() {


                        const rect =
                            wrapper.getBoundingClientRect();


                        tooltip.style.display =
                            'block';

                        tooltip.style.visibility =
                            'hidden';


                        /*
                        |--------------------------------------------------------------------------
                        | Reset
                        |--------------------------------------------------------------------------
                        */

                        tooltip.style.left =
                            '0px';

                        tooltip.style.top =
                            '0px';


                        const tooltipRect =
                            tooltip.getBoundingClientRect();


                        const gap = 6;

                        const padding = 8;


                        /*
                        |--------------------------------------------------------------------------
                        | Default:
                        | tooltip berada di bawah kanan cell
                        |--------------------------------------------------------------------------
                        */

                        let left =
                            rect.right
                            -
                            tooltipRect.width;


                        let top =
                            rect.bottom
                            +
                            gap;


                        /*
                        |--------------------------------------------------------------------------
                        | Jangan keluar kiri
                        |--------------------------------------------------------------------------
                        */

                        if (
                            left < padding
                        ) {

                            left =
                                padding;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Jangan keluar kanan
                        |--------------------------------------------------------------------------
                        */

                        if (
                            left
                            +
                            tooltipRect.width
                            >
                            window.innerWidth
                            -
                            padding
                        ) {

                            left =
                                window.innerWidth
                                -
                                tooltipRect.width
                                -
                                padding;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Kalau tidak cukup ruang bawah,
                        | tampilkan di atas cell
                        |--------------------------------------------------------------------------
                        */

                        if (
                            top
                            +
                            tooltipRect.height
                            >
                            window.innerHeight
                            -
                            padding
                        ) {

                            top =
                                rect.top
                                -
                                tooltipRect.height
                                -
                                gap;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Jangan keluar atas
                        |--------------------------------------------------------------------------
                        */

                        if (
                            top < padding
                        ) {

                            top =
                                padding;

                        }


                        tooltip.style.left =
                            left + 'px';


                        tooltip.style.top =
                            top + 'px';


                        tooltip.style.visibility =
                            'visible';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SHOW
                    |--------------------------------------------------------------------------
                    */

                    function showTooltip() {

                        tooltip.classList.add(
                            'show'
                        );

                        positionTooltip();

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HIDE
                    |--------------------------------------------------------------------------
                    */

                    function hideTooltip() {

                        tooltip.classList.remove(
                            'show'
                        );

                        tooltip.style.display =
                            '';

                        tooltip.style.visibility =
                            '';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MOUSE ENTER
                    |--------------------------------------------------------------------------
                    */

                    wrapper.addEventListener(
                        'mouseenter',
                        showTooltip
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | MOUSE LEAVE
                    |--------------------------------------------------------------------------
                    */

                    wrapper.addEventListener(
                        'mouseleave',
                        hideTooltip
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | SCROLL
                    |--------------------------------------------------------------------------
                    */

                    window.addEventListener(
                        'scroll',
                        function () {


                            if (
                                tooltip.classList.contains(
                                    'show'
                                )
                            ) {

                                positionTooltip();

                            }

                        },
                        true
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | RESIZE
                    |--------------------------------------------------------------------------
                    */

                    window.addEventListener(
                        'resize',
                        function () {


                            if (
                                tooltip.classList.contains(
                                    'show'
                                )
                            ) {

                                positionTooltip();

                            }

                        }
                    );


                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        setupTooltip(
            '.payment-wrapper',
            '.credit-tooltip'
        );


        /*
        |--------------------------------------------------------------------------
        | BAHAN
        |--------------------------------------------------------------------------
        */

        setupTooltip(
            '.bahan-wrapper',
            '.bahan-tooltip'
        );


    }
);
</script>

@endsection