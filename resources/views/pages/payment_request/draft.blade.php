@extends('master.master')
@section('title', 'Draft payment request')
@section('content')
    <div class="box mt-4">
    @section('btn')
        <div class="box-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Payment Request (Nur)</h3>
        </div>
    @endsection
    <div class="box-body spk-wrapper">
        <!-- navigasi -->
        <ul class="nav nav-tabs mb-3 mt-4">
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#draft-request-tab">
                    Payment Request
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#payment-request-tab">
                    Draft Request
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade" id="payment-request-tab">
                <div
                    style="
        background:white;
        padding:20px;
        font-family:Arial;
        font-size:11px;
    ">
                    {{-- HEADER --}}
                    <table width="100%" style="
            margin-bottom:10px;
        ">
                        <tr>
                            {{-- LOGO --}}
                            <td width="25%">
                                <img src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}" height="80">


                            </td>
                            {{-- TITLE --}}
                            <td width="50%" align="center">
                                <h2
                                    style="
                        margin:0;
                        font-size:28px;
                    ">
                                    Purchase Request
                                </h2>
                            </td>
                            {{-- NEED DATE --}}
                            <td width="25%">
                                <table width="100%"
                                    style="
                        border-collapse:collapse;
                    ">
                                    <tr>
                                        <td
                                            style="
                                border:1px solid black;
                                padding:4px;
                                font-size:11px;
                            ">
                                            Need by Date :
                                        </td>
                                        <td
                                            style="
                                border:1px solid black;
                                padding:4px;
                                font-size:11px;
                            ">
                                            <input type="date" id="need_date" value="{{ now()->format('Y-m-d') }}"
                                                style="
                                    width:100%;
                                    border:none;
                                    outline:none;
                                    background:transparent;
                                    font-size:11px;
                                ">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    {{-- INFO --}}
                    <table width="100%"
                        style="
            border-collapse:collapse;
            margin-bottom:10px;
        ">
                        <tr>
                            <td
                                style="
                    border:1px solid black;
                    padding:4px;
                    width:180px;
                    font-weight:bold;
                ">
                                Requisition Date :
                            </td>
                            <td
                                style="
                    border:1px solid black;
                    padding:4px;
                ">
                                <input type="date" id="request_date" value="{{ now()->format('Y-m-d') }}"
                                    style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
                            </td>
                            <td
                                style="
                    border:1px solid black;
                    padding:4px;
                    width:180px;
                    font-weight:bold;
                ">
                                Department :
                            </td>
                            <td
                                style="
                    border:1px solid black;
                    padding:4px;
                ">
                                <input type="text" value="Purchasing"
                                    style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
                            </td>
                        </tr>
                    </table>
                    {{-- SAVE BUTTON --}}
                    <div style="
            text-align:right;
            margin-bottom:10px;
        ">
                        <button id="btn-save-request"
                            style="
                background:#111827;
                color:white;
                border:none;
                padding:8px 18px;
                border-radius:6px;
                font-size:12px;
                font-weight:bold;
                cursor:pointer;
            ">
                            ðŸ’¾ Save Draft Request
                        </button>
                    </div>
                    {{-- TABLE --}}
                    <table width="100%"
                        style="
            border-collapse:collapse;
            font-size:11px;
        ">
                        <thead>
                            <tr style="
                    background:#f3f4f6;
                ">
                                <th class="pr-th">
                                    <input type="checkbox" id="check-all-request">
                                </th>
                                <th class="pr-th">
                                    No
                                </th>
                                <th class="pr-th">
                                    PO
                                </th>
                                <th class="pr-th">
                                    TGL
                                </th>
                                <th class="pr-th">
                                    Supplier
                                </th>
                                <th class="pr-th">
                                    Payment
                                </th>
                                <th class="pr-th">
                                    Description
                                </th>
                                <th class="pr-th">
                                    Keterangan
                                </th>
                                <th class="pr-th">
                                    Qty
                                </th>
                                <th class="pr-th">
                                    Sat
                                </th>
                                <th class="pr-th">
                                    Unit Price
                                </th>
                                <th class="pr-th">
                                    Total
                                </th>
                                <th class="pr-th">
                                    Status

                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDraft = collect($requests)->sum('payment_amount');

                                $no = 1;
                            @endphp
                            @foreach ($requests as $row)
                                <tr>
                                    <td class="pr-td" align="center">
                                        <input type="checkbox" class="request-check-item" value="{{ $row['id'] }}">
                                    </td>
                                    <td class="pr-td">
                                        {{ $no++ }}
                                    </td>
                                    <td class="pr-td">
                                        {{ $row['no_po'] }}
                                    </td>
                                    <td class="pr-td">
                                        {{ \Carbon\Carbon::parse($row['request_date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="pr-td">
                                        {{ strtoupper($row['supplier']) }}
                                    </td>
                                    <td class="pr-td">
                                        TF
                                    </td>
                                    <td class="pr-td">
                                        {{ $row['spk_no'] }}
                                    </td>
                                    <td class="pr-td">
                                        {{ strtoupper($row['payment_note']) }}
                                    </td>
                                    <td class="pr-td">
                                        {{ $row['payment_note'] }}
                                    </td>
                                    <td class="pr-td" align="center">
                                        1
                                    </td>
                                    <td class="pr-td" align="right">
                                        Rp
                                        {{ number_format($row['payment_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="pr-td" align="right">
                                        Rp
                                        {{ number_format($row['payment_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="pr-td">
                                        <span style="color:red;font-weight:bold;">
                                            urgent
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <!---->
                        <tfoot>
                            <tr class="table-success">
                                <th colspan="11" class="text-end">
                                    TOTAL DRAFT
                                </th>

                                <th>
                                    Rp {{ number_format($totalDraft, 0, ',', '.') }}
                                </th>

                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                    {{-- SIGNATURE SECTION --}}
                    <div style="
        margin-top:60px;
    ">
                        <table width="100%"
                            style="
            text-align:center;
            font-size:11px;
        ">
                            <tr>
                                {{-- 1. AUTH USER --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Made By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $authUser->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $authUser->name ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $authUser->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                                {{-- 2. KEPALA PURCHASING --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Checked By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $kepalaPurchasing->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $kepalaPurchasing->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $kepalaPurchasing->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                                {{-- 3. PROD MANAGER --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Checked By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $prodManager->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $prodManager->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $prodManager->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                                {{-- 4. CEO --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Approved By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $ceo->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $ceo->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $ceo->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                                {{-- 5. VP SALES --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Approved By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $vpSales->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $vpSales->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $vpSales->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                                {{-- 6. FINANCE --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Checked By Finance
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $finance->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $finance->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $finance->divisi->nama ?? '-' }}
                                    </div>
                                </td>


                                {{-- 8. COO --}}
                                <td width="12.5%">
                                    <div
                                        style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                                        Approved By
                                    </div>
                                    <div style="height:70px;">
                                        <img src="
                        {{ $coo->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                        "
                                            style="
                            max-height:50px;
                        ">
                                    </div>
                                    <div style="
                        font-weight:bold;
                    ">
                                        {{ $coo->nama ?? '-' }}
                                    </div>
                                    <div style="
                        font-size:10px;
                    ">
                                        {{ $coo->divisi->nama ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <!-- tab2 -->
            <!-- {{ print_r($draftRequests, true) }} -->

            <div class="tab-pane fade show active" id="draft-request-tab">
                <div class="card">
                    <div class="card-header payment-request-card-header">
                        <div class="payment-request-title-row">
                            <button type="button" id="btnBackDraftList" class="btn-back-draft"
                                style="display:none;" title="Back to Payment Requests">
                                <i class="fa fa-arrow-left"></i>
                                <span>Back</span>
                            </button>

                            <h5 class="mb-0">
                                Payment Requests
                            </h5>
                        </div>

                        <small class="text-success">
                            <i class="fa fa-info-circle"></i>
                            Klik <b>Detail</b> pada pengajuan paling atas <b>(NEW)</b>. Setelah halaman detail terbuka,
                            scroll ke bawah untuk melakukan <b>Approve</b>.
                        </small>
                    </div>

                    <div class="card-body">
                        <div class="draft-wrapper">
                            <div class="draft-list">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Draft No</th>
                                            <th class="pr-th">
                                                Added Kreditor
                                            </th>
                                            <th>Request Date</th>
                                            <th>Need Date</th>
                                            <th>Total baris</th>
                                            <th>Grand Total</th>
                                            <th>Status</th>
                                            </th>

                                            <th>Pending Sign</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draftRequests as $draft)
                                            <tr class="draft-row" data-id="{{ $draft['id'] }}">
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td>
                                                    <div class="draft-request-cell">

                                                        {{-- Request Number --}}
                                                        <span class="draft-request-no">
                                                            {{ $draft['request_no'] }}
                                                        </span>

                                                        {{-- Actions --}}
                                                        <div class="draft-actions">

                                                            {{-- Export --}}
                                                            <a href="{{ route('payment-request-saved.export', $draft['id']) }}"
                                                                class="draft-action-btn draft-export-btn"
                                                                title="Export Excel" aria-label="Export Excel">

                                                                <i class="fa fa-download"></i>

                                                            </a>

                                                            {{-- Detail --}}
                                                            <button type="button"
                                                                class="draft-action-btn draft-detail-btn btn-detail-draft"
                                                                data-id="{{ $draft['id'] }}"
                                                                data-request="{{ $draft['request_no'] }}"
                                                                title="View Detail">

                                                                Detail

                                                            </button>

                                                        </div>

                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="ainun-recon-check"
                                                        data-id="{{ $draft['id'] }}"
                                                        {{ $draft['ainun_saved_recon'] ? 'checked' : '' }}>
                                                </td>
                                                <td>
                                                    {{ $draft['request_date'] }}
                                                </td>
                                                <td>
                                                    {{ $draft['need_date'] }}
                                                </td>
                                                <td>
                                                    {{ $draft['total_items'] }}
                                                </td>
                                                <td>
                                                    Rp
                                                    {{ number_format($draft['grand_total'], 0, ',', '.') }}
                                                </td>

                                                <td>
                                                    {{ $draft['status'] }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success text-dark">
                                                        Pending {{ $draft['pending_sign'] }}
                                                    </span>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    Belum ada draft request
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="draft-detail" id="draftDetailArea">

                                <div class="alert alert-info">

                                    Klik tombol Detail

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* =========================================================
   DRAFT REQUEST / ACTION STYLE
   ========================================================= */

.draft-request-cell {
    display: flex;
    align-items: center;

    gap: 10px;

    min-width: 260px;

    white-space: nowrap;
}


/* =========================================================
   REQUEST NUMBER
   ========================================================= */

.draft-request-no {
    display: inline-block;

    min-width: 125px;

    color: #344054;

    font-size: 9px;

    font-weight: 600;

    line-height: 1.2;

    white-space: nowrap;
}


/* =========================================================
   ACTION CONTAINER
   ========================================================= */

.draft-actions {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    margin-left: auto;
}


/* =========================================================
   BASE ACTION BUTTON
   ========================================================= */

.draft-action-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    height: 29px;

    min-height: 29px;

    border-radius: 5px;

    font-size: 9px;

    font-weight: 600;

    line-height: 1;

    text-decoration: none !important;

    cursor: pointer;

    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease,
        box-shadow .15s ease,
        transform .1s ease;
}


/* =========================================================
   EXPORT
   ========================================================= */

.draft-export-btn {
    width: 29px;

    min-width: 29px;

    padding: 0;

    border: 1px solid #12b76a;

    background: #ffffff;

    color: #12b76a;
}

.draft-export-btn:hover {
    background: #ecfdf3;

    border-color: #039855;

    color: #039855;

    box-shadow: 0 1px 3px rgba(16, 24, 40, .08);
}

.draft-export-btn:active {
    transform: translateY(1px);
}


/* =========================================================
   DETAIL
   ========================================================= */

.draft-detail-btn {
    min-width: 52px;

    padding: 0 11px;

    border: 1px solid #0d6efd;

    background: #0d6efd;

    color: #ffffff;
}

.draft-detail-btn:hover {
    background: #0b5ed7;

    border-color: #0b5ed7;

    color: #ffffff;

    box-shadow: 0 1px 3px rgba(13, 110, 253, .20);
}

.draft-detail-btn:active {
    transform: translateY(1px);
}


/* =========================================================
   ICON
   ========================================================= */

.draft-export-btn i {
    font-size: 9px;
}


/* =========================================================
   ACTIVE ROW
   ========================================================= */

.draft-row.active-row .draft-request-no {
    color: #175cd3;

    font-weight: 700;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 768px) {

    .draft-request-cell {
        min-width: 240px;

        gap: 8px;
    }

    .draft-request-no {
        min-width: 115px;
    }

    .draft-actions {
        gap: 5px;
    }

}
    /* =========================================================
           PAYMENT REQUEST / DRAFT
           FULL UI FIX

           Tujuan:
           - Tidak mengubah fungsi / AJAX / ID / Blade logic
           - Menghilangkan overlap tabel + detail
           - Desktop 100% tetap proporsional
           - Detail menjadi panel kedua yang benar-benar terpisah
           - Horizontal slide tetap tersedia
           - Responsive
           ========================================================= */

    * {
        box-sizing: border-box;
    }

    /* =========================================================
           MAIN PAGE
           ========================================================= */

    .spk-wrapper {
        width: 100%;
        min-width: 0;
    }

    .box-body.spk-wrapper {
        overflow: visible !important;
    }

    .box {
        width: 100%;
        max-width: 100%;
    }

    .box-header {
        width: 100%;
    }

    /* =========================================================
           TAB
           ========================================================= */

    .spk-wrapper>.nav-tabs {
        display: flex;
        flex-wrap: nowrap;
        gap: 2px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 12px !important;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
    }

    .spk-wrapper>.nav-tabs .nav-link {
        flex: 0 0 auto;
        padding: 9px 14px;
        border: 1px solid transparent;
        border-bottom: 0;
        border-radius: 6px 6px 0 0;
        color: #344054;
        font-size: 12px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .spk-wrapper>.nav-tabs .nav-link:hover {
        color: #175cd3;
        background: #f8fafc;
    }

    .spk-wrapper>.nav-tabs .nav-link.active {
        color: #175cd3;
        background: #fff;
        border-color: #e5e7eb #e5e7eb #fff;
        font-weight: 600;
    }

    /* =========================================================
           TAB CONTENT
           ========================================================= */

    .spk-wrapper .tab-content {
        width: 100%;
        min-width: 0;
        overflow: visible;
    }

    .spk-wrapper .tab-pane {
        width: 100%;
        min-width: 0;
    }

    /* =========================================================
           DRAFT CARD
           ========================================================= */

    #draft-request-tab {
        width: 100%;
        min-width: 0;
    }

    #draft-request-tab>.card {
        width: 100%;
        max-width: 100%;
        margin: 0;
        overflow: visible;
    }

    #draft-request-tab>.card>.card-header {
        position: relative;
        z-index: 5;
        padding: 12px 15px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    #draft-request-tab>.card>.card-header h5 {
        margin: 0 0 4px;
        color: #172033;
        font-size: 15px;
        font-weight: 700;
    }

    #draft-request-tab>.card>.card-header small {
        display: block;
        color: #12b76a;
        font-size: 10px;
        line-height: 1.5;
    }

    #draft-request-tab>.card>.card-body {
        width: 100%;
        min-width: 0;
        padding: 0 !important;
        overflow: visible;
    }

    /* =========================================================
           CRITICAL: SLIDER

           Jangan menggunakan:
           min-width:100% + padding yang membuat ukuran > viewport.

           Setiap panel:
           flex: 0 0 100%
           sehingga list dan detail tidak saling menimpa.
           ========================================================= */

    .draft-wrapper {
        position: relative;

        display: flex !important;

        flex-direction: row !important;
        flex-wrap: nowrap !important;

        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;

        margin: 0 !important;
        padding: 0 !important;

        overflow-x: auto !important;
        overflow-y: hidden !important;

        scroll-behavior: smooth;

        scroll-snap-type: x mandatory;

        -webkit-overflow-scrolling: touch;

        scrollbar-width: thin;

        isolation: isolate;
    }

    .draft-wrapper::-webkit-scrollbar {
        height: 7px;
    }

    .draft-wrapper::-webkit-scrollbar-track {
        background: #f2f4f7;
    }

    .draft-wrapper::-webkit-scrollbar-thumb {
        background: #98a2b3;
        border-radius: 20px;
    }

    .draft-wrapper::-webkit-scrollbar-thumb:hover {
        background: #667085;
    }

    /* =========================================================
           LIST PANEL
           ========================================================= */

    .draft-list {
        position: relative;

        flex: 0 0 100% !important;

        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;

        height: auto;

        margin: 0 !important;
        padding: 0 !important;

        overflow: visible !important;

        scroll-snap-align: start;
        scroll-snap-stop: always;

        background: #fff;

        z-index: 1;
    }

    /* =========================================================
           DETAIL PANEL
           ========================================================= */

    .draft-detail {
        position: relative;

        flex: 0 0 100% !important;

        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;

        height: auto;

        margin: 0 !important;

        /*
             * PENTING:
             * jangan gunakan padding-left 20px pada flex item
             * karena dapat menyebabkan lebar aktual > 100%.
             */
        padding: 0 !important;

        overflow: visible !important;

        scroll-snap-align: start;
        scroll-snap-stop: always;

        background: #fff;

        z-index: 1;
    }

    /* Detail content */
    .draft-detail>* {
        max-width: 100%;
    }

    .draft-detail #printArea {
        width: 100%;
        max-width: 100%;
        overflow: visible;
    }

    /* =========================================================
           TABLE LIST
           ========================================================= */

    .draft-list>table,
    .draft-list table.table {
        width: 100% !important;
        max-width: 100% !important;

        margin: 0 !important;

        border-collapse: separate !important;
        border-spacing: 0 !important;

        table-layout: auto;

        font-size: 10px;

        background: #fff;
    }

    .draft-list table thead {
        position: sticky;
        top: 0;
        z-index: 20;
    }

    .draft-list table thead th {
        position: sticky;
        top: 0;
        z-index: 21;

        height: 36px;

        padding: 7px 8px !important;

        background: #f8f9fb !important;

        color: #475467 !important;

        border: 0 !important;
        border-bottom: 1px solid #dfe3e8 !important;

        font-size: 9px !important;
        font-weight: 700 !important;

        line-height: 1.2;

        white-space: nowrap;

        vertical-align: middle;
    }

    .draft-list table tbody td {
        height: 38px;

        padding: 6px 8px !important;

        color: #344054;

        border: 0 !important;
        border-bottom: 1px solid #edf0f2 !important;

        background: #fff !important;

        font-size: 9px !important;

        line-height: 1.2;

        vertical-align: middle;

        white-space: nowrap;
    }

    .draft-list table tbody tr {
        transition: background .12s ease;
    }

    .draft-list table tbody tr:hover td {
        background: #f8fbff !important;
    }

    /* =========================================================
           ACTIVE ROW
           ========================================================= */

    .draft-row.active-row {
        background: #eef5ff !important;
    }

    .draft-row.active-row td {
        background: #eef5ff !important;
        font-weight: 600;
    }

    /* =========================================================
           BUTTONS
           ========================================================= */

    .draft-list .btn,
    .draft-detail .btn {
        min-height: 30px;
        height: 30px;

        padding: 5px 10px;

        border-radius: 5px;

        font-size: 9px;
        line-height: 18px;

        white-space: nowrap;
    }

    .draft-list .btn-sm,
    .draft-detail .btn-sm {
        min-height: 28px;
        height: 28px;

        padding: 4px 9px;

        font-size: 9px;
    }

    .draft-list .btn-detail-draft {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .draft-list .btn-detail-draft:hover {
        background: #0b5ed7;
        border-color: #0a58ca;
    }

    /* =========================================================
           BADGE
           ========================================================= */

    .draft-list .badge {
        display: inline-flex;
        align-items: center;

        min-height: 18px;

        padding: 3px 6px;

        border-radius: 4px;

        font-size: 8px;
        line-height: 1;
        white-space: nowrap;
    }

    /* =========================================================
           DETAIL AREA
           ========================================================= */

    #draftDetailArea {
        position: relative;

        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;

        overflow-x: auto !important;
        overflow-y: visible !important;

        background: #fff;
    }

    #draftDetailArea>.alert {
        margin: 12px;
        font-size: 10px;
    }

    /*
         * Isi detail dari AJAX menggunakan #printArea.
         * Batasi ukuran agar tabel/detail tidak melebarkan parent.
         */
    #draftDetailArea #printArea {
        display: block;

        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;

        margin: 0;
        padding: 0 !important;

        overflow-x: auto;
        overflow-y: visible;

        background: #fff;
    }

    /* =========================================================
           AJAX DETAIL HEADER
           ========================================================= */

    #draftDetailArea #printArea>.alert {
        margin: 12px 12px 0 !important;
        border-radius: 6px;
        font-size: 10px;
        line-height: 1.6;
    }

    #draftDetailArea #printArea>div[style*="background:white"] {
        width: 100% !important;
        max-width: 100% !important;

        min-width: 0 !important;

        margin: 0 !important;

        padding: 15px !important;

        overflow-x: auto !important;
        overflow-y: visible !important;

        box-sizing: border-box !important;
    }

    /* =========================================================
           DETAIL PURCHASE REQUEST TABLE

           Detail memang punya banyak kolom.
           Biarkan tabel detail scroll horizontal DI DALAM panel,
           bukan melebarkan panel.
           ========================================================= */

    #draftDetailArea table {
        border-collapse: collapse;
    }

    #draftDetailArea #printArea table {
        max-width: 100%;
    }

    #draftDetailArea .card {
        width: 100%;
        max-width: 100%;
        overflow: visible;
    }

    #draftDetailArea .card-header {
        padding: 9px 12px;
    }

    #draftDetailArea .card-header h5 {
        margin: 0;
        font-size: 12px;
    }

    #draftDetailArea .card-body {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
    }

    /* =========================================================
           PRINT AREA HEADER
           ========================================================= */

    #draftDetailArea #printArea>div[style*="font-family:Arial"] {
        font-family: Arial, sans-serif !important;
        font-size: 11px !important;
    }

    #draftDetailArea #printArea>div[style*="font-family:Arial"]>table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed;
    }

    #draftDetailArea #printArea>div[style*="font-family:Arial"] img {
        max-width: 100%;
        object-fit: contain;
    }

    /* =========================================================
           SIGNATURE
           ========================================================= */

    #draftDetailArea .signature-section {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: visible;
    }

    #draftDetailArea .signature-section table {
        width: 100% !important;
        min-width: 760px;
        table-layout: fixed;
    }

    /* =========================================================
           INPUT DETAIL
           ========================================================= */

    #draftDetailArea input.form-control,
    #draftDetailArea .form-control {
        min-height: 30px;
        height: 30px;

        padding: 4px 7px;

        border-radius: 5px;

        font-size: 9px;
    }

    /* =========================================================
           MAIN OLD PURCHASE REQUEST TAB
           ========================================================= */

    #payment-request-tab {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
    }

    #payment-request-tab>div {
        min-width: 850px;
    }


    /* =========================================================
           DETAIL TABLE - SAME COMPACT STYLE AS MAIN TABLE
           UI ONLY - JS/AJAX/Blade functionality untouched
           ========================================================= */

    #draftDetailArea {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        background: #fff !important;
    }

    #draftDetailArea table {
        width: 100%;
        max-width: 100%;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        background: #fff !important;
        color: #344054;
        font-size: 9px;
    }

    #draftDetailArea table thead th {
        height: 34px !important;
        padding: 7px 8px !important;
        background: #f8f9fb !important;
        color: #667085 !important;
        border: 0 !important;
        border-bottom: 1px solid #e4e7ec !important;
        font-size: 8.5px !important;
        font-weight: 700 !important;
        line-height: 1.2;
        white-space: nowrap;
        vertical-align: middle !important;
    }

    #draftDetailArea table thead th+th {
        border-left: 1px solid #eef0f3 !important;
    }

    #draftDetailArea table tbody tr {
        background: #fff !important;
        transition: background .12s ease;
    }

    #draftDetailArea table tbody tr:hover {
        background: #f8fbff !important;
    }

    #draftDetailArea table tbody td {
        min-height: 36px;
        padding: 6px 8px !important;
        background: transparent !important;
        color: #344054 !important;
        border: 0 !important;
        border-bottom: 1px solid #edf0f2 !important;
        font-size: 9px !important;
        line-height: 1.35;
        vertical-align: middle !important;
    }

    #draftDetailArea table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    #draftDetailArea table td strong,
    #draftDetailArea table td b {
        color: #172033 !important;
        font-weight: 650 !important;
    }

    #draftDetailArea table .form-control,
    #draftDetailArea table input,
    #draftDetailArea table select,
    #draftDetailArea table textarea {
        height: 29px !important;
        min-height: 29px !important;
        padding: 4px 7px !important;
        border: 1px solid #dfe3e8 !important;
        border-radius: 5px !important;
        background: #fff !important;
        color: #344054 !important;
        font-size: 9px !important;
        box-shadow: none !important;
    }

    #draftDetailArea table .form-control:focus,
    #draftDetailArea table input:focus,
    #draftDetailArea table select:focus,
    #draftDetailArea table textarea:focus {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .07) !important;
    }

    #draftDetailArea table .badge {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: 3px 6px !important;
        border-radius: 4px !important;
        font-size: 8px !important;
        font-weight: 650 !important;
        line-height: 1 !important;
        white-space: nowrap;
    }

    #draftDetailArea table .btn {
        min-width: 29px;
        min-height: 29px;
        height: 29px;
        padding: 4px 8px !important;
        border-radius: 5px !important;
        font-size: 8.5px !important;
        line-height: 1.2;
        box-shadow: none !important;
    }

    /* Detail cards */
    #draftDetailArea .card {
        width: 100%;
        max-width: 100%;
        border: 1px solid #e2e6eb !important;
        border-radius: 7px !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .035) !important;
        overflow: hidden !important;
    }

    #draftDetailArea .card-header {
        min-height: 38px !important;
        padding: 7px 10px !important;
        background: #fff !important;
        border-bottom: 1px solid #e9edf1 !important;
    }

    #draftDetailArea .card-header h5,
    #draftDetailArea .card-header h6 {
        margin: 0 !important;
        color: #172033 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
    }

    #draftDetailArea .card-body {
        padding: 0 !important;
        background: #fff !important;
    }

    #draftDetailArea .table-bordered th,
    #draftDetailArea .table-bordered td {
        border: 0 !important;
        border-bottom: 1px solid #edf0f2 !important;
    }

    #draftDetailArea table tfoot td {
        padding: 7px 8px !important;
        background: #f8fafc !important;
        color: #172033 !important;
        border: 0 !important;
        border-top: 1px solid #e4e7ec !important;
        font-size: 9px !important;
        font-weight: 700 !important;
    }

    #draftDetailArea .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        border: 0 !important;
        scrollbar-width: thin;
    }

    #draftDetailArea .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    #draftDetailArea .table-responsive::-webkit-scrollbar-track {
        background: #f3f4f6;
    }

    #draftDetailArea .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd1d8;
        border-radius: 20px;
    }

    #draftDetailArea small,
    #draftDetailArea .small {
        color: #98a2b3;
        font-size: 8px !important;
    }

    #draftDetailArea .alert {
        border-radius: 6px !important;
        font-size: 9px !important;
        line-height: 1.45;
    }

    /* Keep signature layout intact while making it visually compact */
    #draftDetailArea .signature-section table,
    #draftDetailArea table.signature-table {
        min-width: 700px;
    }

    #draftDetailArea .signature-section table td,
    #draftDetailArea .signature-section table th {
        background: #fff !important;
        border-bottom: 0 !important;
    }

    @media (max-width: 768px) {
        #draftDetailArea table {
            font-size: 8.5px;
        }

        #draftDetailArea table thead th {
            font-size: 8px !important;
            padding: 6px 7px !important;
        }

        #draftDetailArea table tbody td {
            font-size: 8.5px !important;
            padding: 5px 7px !important;
        }
    }


    /* =========================================================
           BACK TO PAYMENT REQUEST LIST
           ========================================================= */

    .payment-request-card-header {
        position: relative;
        z-index: 100;
    }

    .payment-request-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 30px;
        margin-bottom: 3px;
    }

    .payment-request-title-row h5 {
        margin: 0 !important;
        line-height: 30px !important;
    }

    #btnBackDraftList {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 6px;

        width: auto;
        height: 29px;
        min-height: 29px;

        padding: 0 10px !important;

        border: 1px solid #d0d5dd !important;
        border-radius: 6px !important;

        background: #ffffff !important;
        color: #344054 !important;

        font-size: 9px !important;
        font-weight: 700 !important;
        line-height: 1 !important;

        cursor: pointer;

        box-shadow: none !important;

        transition: all .12s ease;
    }

    #btnBackDraftList:hover {
        background: #f8fafc !important;
        border-color: #98a2b3 !important;
        color: #175cd3 !important;
    }

    #btnBackDraftList i {
        font-size: 9px;
    }

    #btnBackDraftList.is-visible {
        display: inline-flex !important;
    }

    @media (max-width: 600px) {
        #btnBackDraftList span {
            display: none;
        }

        #btnBackDraftList {
            width: 29px;
            padding: 0 !important;
        }
    }

    /* =========================================================
           PRINT
           ========================================================= */

    @media print {

        .draft-wrapper {
            display: block !important;
            overflow: visible !important;
        }

        .draft-list {
            display: none !important;
        }

        .draft-detail {
            display: block !important;

            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;

            padding: 0 !important;
        }

        #draftDetailArea {
            overflow: visible !important;
        }

    }

    /* =========================================================
           TABLET
           ========================================================= */

    @media (max-width: 992px) {

        .draft-list table {
            min-width: 950px;
        }

        .draft-detail {
            padding: 0 !important;
        }

        #draftDetailArea #printArea>div[style*="background:white"] {
            padding: 12px !important;
        }

    }

    /* =========================================================
           MOBILE
           ========================================================= */

    @media (max-width: 768px) {

        .box-body.spk-wrapper {
            padding-left: 8px;
            padding-right: 8px;
        }

        .spk-wrapper>.nav-tabs {
            margin-top: 12px !important;
        }

        #draft-request-tab>.card>.card-header {
            padding: 10px;
        }

        #draft-request-tab>.card>.card-body {
            padding: 0 !important;
        }

        .draft-list table {
            min-width: 950px;
        }

        #draftDetailArea #printArea>div[style*="background:white"] {
            padding: 10px !important;
        }

        #draftDetailArea .signature-section table {
            min-width: 700px;
        }

    }

    /* =========================================================
           VERY SMALL SCREEN
           ========================================================= */

    @media (max-width: 480px) {

        .draft-list table {
            min-width: 900px;
        }

        .draft-list table thead th {
            height: 34px;
            padding: 6px !important;
            font-size: 8px !important;
        }

        .draft-list table tbody td {
            padding: 5px 6px !important;
            font-size: 8px !important;
        }

        .draft-detail {
            padding: 0 !important;
        }

    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('pages.payment_request.script')
<script>
    $(document).on(
        'change',
        '#check-all-request',
        function() {
            $('.request-check-item')
                .prop(
                    'checked',
                    $(this).is(':checked')
                );
        }
    );
    $(document).on(
        'click',
        '#btn-save-request',
        function() {
            let requestDate =
                $('#request_date').val();
            let needDate =
                $('#need_date').val();
            let ids = [];
            $('.request-check-item:checked')
                .each(function() {
                    ids.push($(this).val());
                });
            if (ids.length == 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Pilih request terlebih dahulu'
                });
                return;
            }
            $.ajax({
                url: "{{ route('payment-request.save-draft-group') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: ids,
                    request_date: requestDate,
                    need_date: needDate,
                },
                beforeSend: function() {
                    $('#btn-save-request')
                        .prop('disabled', true)
                        .html('Saving...');
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error'
                    });
                },
                complete: function() {
                    $('#btn-save-request')
                        .prop('disabled', false)
                        .html('ðŸ’¾ Save Draft Request');
                }
            });
        }
    );
</script>

<script>
    (function() {
        function getWrapper() {
            return document.querySelector('.draft-wrapper');
        }

        function getBackButton() {
            return document.getElementById('btnBackDraftList');
        }

        function showBackButton() {
            const btn = getBackButton();
            if (btn) {
                btn.classList.add('is-visible');
                btn.style.display = 'inline-flex';
            }
        }

        function hideBackButton() {
            const btn = getBackButton();
            if (btn) {
                btn.classList.remove('is-visible');
                btn.style.display = 'none';
            }
        }

        function syncBackButton() {
            const wrapper = getWrapper();
            if (!wrapper) return;

            if (wrapper.scrollLeft > 30) {
                showBackButton();
            } else {
                hideBackButton();
            }
        }

        function bind() {
            const wrapper = getWrapper();

            if (wrapper && wrapper.dataset.backButtonBound !== '1') {
                wrapper.dataset.backButtonBound = '1';

                wrapper.addEventListener('scroll', syncBackButton, {
                    passive: true
                });

                syncBackButton();
            }
        }

        /*
         * Existing Detail handler remains untouched.
         * We only add a second delegated listener.
         */
        $(document)
            .off('click.paymentRequestBack', '.btn-detail-draft')
            .on('click.paymentRequestBack', '.btn-detail-draft', function() {
                showBackButton();

                // Existing handler moves the slider after AJAX.
                setTimeout(showBackButton, 100);
                setTimeout(showBackButton, 300);
                setTimeout(showBackButton, 700);
                setTimeout(syncBackButton, 1200);
            });

        /*
         * Back -> first/list panel.
         */
        $(document)
            .off('click.paymentRequestBackList', '#btnBackDraftList')
            .on('click.paymentRequestBackList', '#btnBackDraftList', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const wrapper = getWrapper();
                if (!wrapper) return;

                hideBackButton();

                wrapper.scrollTo({
                    left: 0,
                    behavior: 'smooth'
                });

                setTimeout(function() {
                    wrapper.scrollLeft = 0;
                    hideBackButton();
                }, 600);
            });

        /*
         * Auto-open by ?no_req=... also gets the button.
         */
        window.addEventListener('load', function() {
            bind();

            setTimeout(function() {
                const wrapper = getWrapper();

                if (wrapper && wrapper.scrollLeft > 30) {
                    showBackButton();
                }
            }, 1500);
        });

        document.addEventListener('DOMContentLoaded', bind);

        // In case Bootstrap/tab/AJAX changes the DOM.
        setTimeout(bind, 200);
        setTimeout(bind, 700);
        setTimeout(bind, 1500);
    })();
</script>

@endsection
