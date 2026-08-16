@extends('master.master')

@section('title', 'Payment Janggal')

@section('content')

<style>

    /* =========================================================
       PAGE
    ========================================================= */

    .janggal-page {
        padding: 12px;
    }

    .janggal-card {
        background: #fff;
        border: 1px solid #dfe4ea;
        border-radius: 6px;
        overflow: hidden;
    }

    .janggal-header {
        padding: 12px 15px;
        background: #f8fafc;
        border-bottom: 1px solid #dfe4ea;
    }

    .janggal-title {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #26384d;
    }

    .janggal-subtitle {
        margin-top: 4px;
        font-size: 10px;
        color: #7b8794;
    }

    .janggal-subtitle strong {
        color: #30445f;
    }


    /* =========================================================
       SUMMARY WIDGET
    ========================================================= */

    .janggal-summary {
        display: grid;

        grid-template-columns:
            repeat(5, minmax(130px, 1fr));

        gap: 8px;

        padding: 9px 15px;

        border-bottom: 1px solid #e1e5ea;

        background: #fff;
    }

    .summary-box {
        min-width: 0;

        padding: 8px 10px;

        border: 1px solid #dce2e8;

        border-radius: 5px;

        background: #fafbfc;
    }

    .summary-label {
        font-size: 8px;

        color: #7b8794;

        text-transform: uppercase;

        letter-spacing: .3px;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .summary-value {
        margin-top: 3px;

        font-size: 14px;

        font-weight: 700;

        color: #26384d;

        white-space: nowrap;

        overflow: hidden;

        text-overflow: ellipsis;
    }


    /* =========================================================
       WIDGET - DP
    ========================================================= */

    .summary-dp {
        background: #fffdf5;
        border-color: #ead9a6;
    }

    .summary-dp .summary-value {
        color: #9a6700;
    }


    /* =========================================================
       WIDGET - KASBON
    ========================================================= */

    .summary-kasbon {
        background: #f7f9ff;
        border-color: #cfd8ef;
    }

    .summary-kasbon .summary-value {
        color: #365a9c;
    }


    /* =========================================================
       WIDGET - TOTAL
    ========================================================= */

    .summary-total {
        background: #fff7f7;
        border-color: #efcaca;
    }

    .summary-total .summary-value {
        color: #b42318;
    }


    /* =========================================================
       TABLE WRAPPER
    ========================================================= */

    .janggal-table-wrapper {
        width: 100%;

        overflow-x: auto;

        overflow-y: visible;
    }


    /* =========================================================
       TABLE
    ========================================================= */

    .janggal-table {
        width: 100%;

        min-width: 1350px;

        border-collapse: collapse;

        font-size: 10px;
    }

    .janggal-table th {
        position: sticky;

        top: 0;

        z-index: 20;

        background: #30445f;

        color: #fff;

        padding: 7px 8px;

        border: 1px solid #26384d;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;

        text-align: left;
    }

    .janggal-table td {
        padding: 6px 8px;

        border: 1px solid #dce2e8;

        vertical-align: top;

        background: #fff;
    }

    .janggal-table tbody tr:hover td {
        background: #f8fafc;
    }


    /* =========================================================
       COLUMN
    ========================================================= */

    .col-no {
        width: 40px;

        min-width: 40px;

        text-align: center !important;
    }

    .col-sub {
        width: 140px;

        min-width: 140px;
    }

    .col-spk {
        width: 230px;

        min-width: 230px;
    }

    .col-date {
        width: 95px;

        min-width: 95px;

        white-space: nowrap;
    }

    .col-total {
        width: 125px;

        min-width: 125px;

        text-align: right;

        white-space: nowrap;
    }

    .col-type {
        width: 90px;

        min-width: 90px;
    }

    .col-timeline {
        min-width: 650px;

        width: auto;
    }


    /* =========================================================
       SPK
    ========================================================= */

    .spk-number {
        font-weight: 700;

        color: #245b8f;

        line-height: 14px;

        word-break: break-word;
    }

    .spk-po {
        display: block;

        margin-top: 3px;

        color: #8a939c;

        font-size: 8px;
    }


    /* =========================================================
       SUPPLIER / SUB
    ========================================================= */

    .supplier-name {
        font-weight: 600;

        color: #293949;

        line-height: 14px;
    }


    /* =========================================================
       DATE
    ========================================================= */

    .first-date {
        font-weight: 600;

        color: #4f5d6b;
    }


    /* =========================================================
       TOTAL
    ========================================================= */

    .before-total-label {
        display: block;

        margin-bottom: 2px;

        font-size: 8px;

        font-weight: 600;

        color: #7d8792;

        text-transform: uppercase;
    }

    .before-total {
        font-size: 11px;

        font-weight: 800;

        color: #b42318;
    }


    /* =========================================================
       JENIS
    ========================================================= */

    .type-badge {
        display: inline-block;

        padding: 3px 6px;

        border-radius: 3px;

        background: #eef2f6;

        color: #526273;

        font-size: 8px;

        font-weight: 700;

        text-transform: uppercase;
    }


    /* =========================================================
       TRANSITION
    ========================================================= */

    .transition-wrapper {
        margin-bottom: 5px;

        display: flex;

        align-items: center;

        gap: 4px;
    }

    .transition-false {
        padding: 2px 5px;

        border-radius: 3px;

        background: #fff0f0;

        color: #b42318;

        font-size: 7px;

        font-weight: 800;
    }

    .transition-true {
        padding: 2px 5px;

        border-radius: 3px;

        background: #eaf8ee;

        color: #237a37;

        font-size: 7px;

        font-weight: 800;
    }

    .transition-arrow {
        color: #89939d;

        font-weight: 700;
    }


    /* =========================================================
       TIMELINE
    ========================================================= */

    .payment-timeline {
        display: flex;

        flex-direction: column;

        gap: 3px;
    }

    .payment-line {
        display: flex;

        align-items: center;

        gap: 7px;

        min-height: 25px;

        padding: 4px 6px;

        border-radius: 3px;

        white-space: nowrap;
    }


    /* BELUM REQUEST */

    .payment-not-request {
        background: #f7f7f7;
    }


    /* SUDAH REQUEST */

    .payment-requested {
        background: #f3f8f4;
    }


    /* =========================================================
       TIMELINE ELEMENT
    ========================================================= */

    .payment-index {
        width: 22px;

        color: #9aa3ac;

        font-size: 8px;

        text-align: center;
    }

    .payment-date {
        width: 78px;

        color: #59636d;

        font-size: 9px;

        white-space: nowrap;
    }

    .payment-note {
        width: 85px;

        color: #3f4c59;

        font-size: 9px;

        font-weight: 600;

        overflow: hidden;

        text-overflow: ellipsis;
    }

    .payment-amount {
        width: 110px;

        text-align: right;

        color: #26384d;

        font-size: 9px;

        font-weight: 700;

        white-space: nowrap;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .payment-status {
        padding: 2px 5px;

        border-radius: 3px;

        font-size: 7px;

        font-weight: 800;

        letter-spacing: .2px;
    }

    .payment-status.requested {
        background: #e8f6ec;

        color: #237a37;
    }

    .payment-status.not-requested {
        background: #eeeeee;

        color: #707880;
    }


    /* =========================================================
       LEGEND
    ========================================================= */

    .janggal-legend {
        display: flex;

        align-items: center;

        gap: 12px;

        padding: 8px 15px;

        border-top: 1px solid #e1e5ea;

        font-size: 8px;

        color: #68737d;

        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;

        align-items: center;

        gap: 4px;
    }

    .legend-box {
        width: 12px;

        height: 10px;

        border-radius: 2px;
    }

    .legend-not-request {
        background: #f7f7f7;

        border: 1px solid #dedede;
    }

    .legend-request {
        background: #f3f8f4;

        border: 1px solid #cfe4d4;
    }


    /* =========================================================
       EMPTY
    ========================================================= */

    .empty-state {
        padding: 40px 20px !important;

        text-align: center;

        color: #8a949d;

        font-size: 11px;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .janggal-summary {
            grid-template-columns:
                repeat(3, minmax(130px, 1fr));
        }

    }

    @media (max-width: 768px) {

        .janggal-page {
            padding: 7px;
        }

        .janggal-title {
            font-size: 15px;
        }

        .janggal-summary {
            grid-template-columns:
                repeat(2, minmax(130px, 1fr));
        }

        .janggal-table {
            min-width: 1250px;
        }

    }

</style>


<div class="janggal-page">

    <div class="janggal-card">


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="janggal-header">

            <h3 class="janggal-title">
                Analisa Payment Janggal
            </h3>

            <div class="janggal-subtitle">

                Menampilkan SPK yang memiliki pola

                <strong>
                    NOT REQUEST → REQUEST
                </strong>

                .

                Payment sebelum request pertama menjadi
                kandidat pembayaran sebelum ERP.

            </div>

        </div>


        {{-- =====================================================
             HITUNG SUMMARY
        ====================================================== --}}

        @php

            /*
            |--------------------------------------------------------------------------
            | JUMLAH SPK JANGGAL
            |--------------------------------------------------------------------------
            */

            $jumlahSpk =
                is_countable($result ?? [])
                    ? count($result)
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | TOTAL SEMUA PAYMENT SEBELUM ERP
            |--------------------------------------------------------------------------
            */

            $totalBeforeErp = 0;


            /*
            |--------------------------------------------------------------------------
            | COUNTER DP
            |--------------------------------------------------------------------------
            */

            $jumlahDpJanggal = 0;

            $totalDpJanggal = 0;


            /*
            |--------------------------------------------------------------------------
            | COUNTER KASBON
            |--------------------------------------------------------------------------
            */

            $jumlahKasbonJanggal = 0;

            $totalKasbonJanggal = 0;


            /*
            |--------------------------------------------------------------------------
            | LOOP RESULT
            |--------------------------------------------------------------------------
            */

            foreach ($result ?? [] as $row) {

                /*
                |--------------------------------------------------------------------------
                | TOTAL BEFORE ERP
                |--------------------------------------------------------------------------
                */

                $totalBeforeErp +=
                    (float) (
                        $row['BEFORE_ERP_TOTAL']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | PAYMENT BEFORE ERP
                |--------------------------------------------------------------------------
                */

                foreach (
                    ($row['BEFORE_ERP'] ?? [])
                    as $payment
                ) {

                    $note = strtolower(
                        trim(
                            $payment['note']
                            ?? ''
                        )
                    );

                    $amount =
                        (float) (
                            $payment['amount']
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | DP
                    |--------------------------------------------------------------------------
                    |
                    | Contoh:
                    | dp
                    | DP
                    | dp bahan
                    |
                    */

                    if (
                        $note === 'dp'
                        ||
                        str_starts_with(
                            $note,
                            'dp '
                        )
                    ) {

                        $jumlahDpJanggal++;

                        $totalDpJanggal +=
                            $amount;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | KASBON
                    |--------------------------------------------------------------------------
                    |
                    | Contoh:
                    | kasbon
                    | Kasbon
                    | KASBON
                    |
                    */

                    elseif (
                        $note === 'kasbon'
                        ||
                        str_starts_with(
                            $note,
                            'kasbon '
                        )
                    ) {

                        $jumlahKasbonJanggal++;

                        $totalKasbonJanggal +=
                            $amount;

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | TOTAL DP + KASBON
            |--------------------------------------------------------------------------
            */

            $totalDpKasbonJanggal =
                $totalDpJanggal
                +
                $totalKasbonJanggal;


            $jumlahDpKasbonJanggal =
                $jumlahDpJanggal
                +
                $jumlahKasbonJanggal;

        @endphp


        {{-- =====================================================
             SUMMARY WIDGET
        ====================================================== --}}

        <div class="janggal-summary">


            {{-- SPK JANGGAL --}}

            <div class="summary-box">

                <div class="summary-label">
                    SPK Janggal
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $jumlahSpk,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- DP --}}

            <div class="summary-box summary-dp">

                <div class="summary-label">
                    DP Not Requested
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $jumlahDpJanggal,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- KASBON --}}

            <div class="summary-box summary-kasbon">

                <div class="summary-label">
                    Kasbon Not Requested
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $jumlahKasbonJanggal,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- NOMINAL DP + KASBON --}}

            <div class="summary-box summary-total">

                <div class="summary-label">
                    Nominal DP + Kasbon Janggal
                </div>

                <div class="summary-value">

                    Rp
                    {{ number_format(
                        $totalDpKasbonJanggal,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>


            {{-- TOTAL SEBELUM ERP --}}

            <div class="summary-box">

                <div class="summary-label">
                    Total Kandidat Sebelum ERP
                </div>

                <div class="summary-value">

                    Rp
                    {{ number_format(
                        $totalBeforeErp,
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

        <div class="janggal-table-wrapper">

            <table class="janggal-table">

                <thead>

                    <tr>

                        <th class="col-no">
                            No.
                        </th>

                        <th class="col-sub">
                            Sub
                        </th>

                        <th class="col-spk">
                            SPK
                        </th>

                        <th class="col-date">
                            Tanggal
                        </th>

                        <th class="col-total">
                            Total
                        </th>

                        <th class="col-type">
                            Jenis
                        </th>

                        <th class="col-timeline">
                            Timeline
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse (
                        $result ?? []
                        as $index => $row
                    )

                        @php

                            $beforeErp =
                                $row['BEFORE_ERP']
                                ?? [];

                            $timeline =
                                $row['PAYMENT_TIMELINE']
                                ?? [];

                            $beforeTotal =
                                (float) (
                                    $row['BEFORE_ERP_TOTAL']
                                    ?? 0
                                );

                            $firstRequest =
                                $row['FIRST_REQUEST']
                                ?? null;

                        @endphp


                        <tr>


                            {{-- =================================================
                                 NO
                            ================================================== --}}

                            <td class="col-no">

                                {{ $index + 1 }}

                            </td>


                            {{-- =================================================
                                 SUB
                            ================================================== --}}

                            <td class="col-sub">

                                <div class="supplier-name">

                                    {{ $row['SUPPLIER'] ?? '-' }}

                                </div>

                            </td>


                            {{-- =================================================
                                 SPK
                            ================================================== --}}

                            <td class="col-spk">

                                <div class="spk-number">

                                    {{ $row['NO_SPK'] ?? '-' }}

                                </div>

                                <span class="spk-po">

                                    PO:
                                    {{ $row['NO_PO'] ?? '-' }}

                                </span>

                            </td>


                            {{-- =================================================
                                 TANGGAL
                            ================================================== --}}

                            <td class="col-date">

                                <div class="first-date">

                                    {{ $beforeErp[0]['date'] ?? '-' }}

                                </div>

                            </td>


                            {{-- =================================================
                                 TOTAL
                            ================================================== --}}

                            <td class="col-total">

                                <span class="before-total-label">
                                    Sebelum ERP
                                </span>

                                <span class="before-total">

                                    Rp
                                    {{ number_format(
                                        $beforeTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </span>

                            </td>


                            {{-- =================================================
                                 JENIS
                            ================================================== --}}

                            <td class="col-type">

                                <span class="type-badge">

                                    {{ $beforeErp[0]['note'] ?? '-' }}

                                </span>

                            </td>


                            {{-- =================================================
                                 TIMELINE
                            ================================================== --}}

                            <td class="col-timeline">


                                {{-- TRANSITION --}}

                                <div class="transition-wrapper">

                                    <span class="transition-false">
                                        NOT REQUEST
                                    </span>

                                    <span class="transition-arrow">
                                        →
                                    </span>

                                    <span class="transition-true">
                                        REQUEST
                                    </span>


                                    @if ($firstRequest)

                                        <span
                                            style="
                                                margin-left:5px;
                                                color:#87919b;
                                                font-size:8px;
                                            "
                                        >

                                            mulai:
                                            {{ $firstRequest['date'] ?? '-' }}

                                        </span>

                                    @endif

                                </div>


                                {{-- PAYMENT TIMELINE --}}

                                <div class="payment-timeline">

                                    @foreach (
                                        $timeline
                                        as $payment
                                    )

                                        @php

                                            $isRequest =
                                                filter_var(
                                                    $payment['is_request']
                                                    ?? false,
                                                    FILTER_VALIDATE_BOOLEAN
                                                );

                                        @endphp


                                        <div
                                            class="
                                                payment-line
                                                {{ $isRequest
                                                    ? 'payment-requested'
                                                    : 'payment-not-request'
                                                }}
                                            "
                                        >


                                            {{-- INDEX --}}

                                            <span class="payment-index">

                                                #{{ ($payment['index'] ?? 0) + 1 }}

                                            </span>


                                            {{-- DATE --}}

                                            <span class="payment-date">

                                                {{ $payment['date'] ?? '-' }}

                                            </span>


                                            {{-- NOTE --}}

                                            <span class="payment-note">

                                                {{ $payment['note'] ?? '-' }}

                                            </span>


                                            {{-- AMOUNT --}}

                                            <span class="payment-amount">

                                                Rp
                                                {{ number_format(
                                                    (float) (
                                                        $payment['amount']
                                                        ?? 0
                                                    ),
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}

                                            </span>


                                            {{-- STATUS --}}

                                            @if ($isRequest)

                                                <span
                                                    class="
                                                        payment-status
                                                        requested
                                                    "
                                                >

                                                    REQUESTED

                                                </span>

                                            @else

                                                <span
                                                    class="
                                                        payment-status
                                                        not-requested
                                                    "
                                                >

                                                    NOT REQUEST

                                                </span>

                                            @endif

                                        </div>

                                    @endforeach

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="empty-state"
                            >

                                Tidak ditemukan SPK dengan pola

                                <strong>
                                    NOT REQUEST → REQUEST
                                </strong>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             LEGEND
        ====================================================== --}}

        <div class="janggal-legend">


            <div class="legend-item">

                <span
                    class="legend-box legend-not-request"
                ></span>

                <span>
                    Payment belum masuk pengajuan
                </span>

            </div>


            <div class="legend-item">

                <span
                    class="legend-box legend-request"
                ></span>

                <span>
                    Payment sudah masuk pengajuan
                </span>

            </div>


            <div class="legend-item">

                <strong>
                    NOT REQUEST → REQUEST
                </strong>

                <span>
                    = indikasi mulai ERP
                </span>

            </div>

        </div>

    </div>

</div>

@endsection