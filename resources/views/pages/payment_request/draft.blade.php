@extends('master.master')
@section('title', 'Draft payment request')
@section('content')
    <div class="box mt-4">
        @section('btn')
            <div class="box-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Payment Request</h3>
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
                    <div style="
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
                                    <h2 style="
                            margin:0;
                            font-size:28px;
                        ">
                                        Payment Request
                                    </h2>
                                </td>
                                {{-- NEED DATE --}}
                                <td width="25%">
                                    <table width="100%" style="
                            border-collapse:collapse;
                        ">
                                        <tr>
                                            <td style="
                                    border:1px solid black;
                                    padding:4px;
                                    font-size:11px;
                                ">
                                                Need by Date :
                                            </td>
                                            <td style="
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
                        <table width="100%" style="
                border-collapse:collapse;
                margin-bottom:10px;
            ">
                            <tr>
                                <td style="
                        border:1px solid black;
                        padding:4px;
                        width:180px;
                        font-weight:bold;
                    ">
                                    Requisition Date :
                                </td>
                                <td style="
                        border:1px solid black;
                        padding:4px;
                    ">
                                    <input type="date" id="request_date" value="{{ now()->format('Y-m-d') }}" style="
                            width:100%;
                            border:none;
                            outline:none;
                            background:transparent;
                            font-size:11px;
                        ">
                                </td>
                                <td style="
                        border:1px solid black;
                        padding:4px;
                        width:180px;
                        font-weight:bold;
                    ">
                                    Department :
                                </td>
                                <td style="
                        border:1px solid black;
                        padding:4px;
                    ">
                                    <input type="text" value="Purchasing" style="
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
                            <button id="btn-save-request" style="
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
                        <table width="100%" style="
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
                                                                {{ !empty($row['payment_date'])
                                    ? \Carbon\Carbon::createFromFormat('d/m/Y', $row['payment_date'])->format('d/m/Y')
                                    : '-' }}
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
                            <table width="100%" style="
                text-align:center;
                font-size:11px;
            ">
                                <tr>
                                    {{-- 1. AUTH USER --}}
                                    <td width="12.5%">
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Made By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $authUser->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Checked By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $kepalaPurchasing->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Checked By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $prodManager->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Approved By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $ceo->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Approved By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $vpSales->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Checked By Finance
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $finance->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                        <div style="
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                                            Approved By
                                        </div>
                                        <div style="height:70px;">
                                            <img src="
                            {{ $coo->signature ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN' }}
                            " style="
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
                                <button type="button" id="btnBackDraftList" class="btn-back-draft" style="display:none;"
                                    title="Back to Payment Requests">
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
                                                                                                        {{-- Magic Approval Link --}}
                                                                                                        {{-- Magic Approval Link --}}
                                                                                                        <button type="button"
                                                                                                            class="draft-action-btn draft-magic-btn btn-magic-approval"
                                                                                                            data-id="{{ $draft['id'] }}"
                                                                                                            data-request="{{ $draft['request_no'] }}"
                                                                                                            data-approvals='@json($draft['approvals'])'
                                                                                                            title="Generate Approval Link"
                                                                                                            aria-label="Generate Approval Link">

                                                                                                            <i class="fa fa-link"></i>

                                                                                                        </button>
                                                                                                        {{-- Export Excel --}}
                                                                                                        <a href="{{ route('payment-request-saved.export', $draft['id']) }}"
                                                                                                            class="draft-action-btn draft-export-btn"
                                                                                                            title="Download Excel Payment Request"
                                                                                                            aria-label="Download Excel Payment Request">

                                                                                                            <i class="fa fa-download"></i>

                                                                                                        </a>

                                                                                                        <a href="{{ url('/payment-request-saved/' . $draft['id'] . '/export-excel-2up') }}"
                                                                                                            class="draft-action-btn"
                                                                                                            title="Download Excel 2-UP per SUBKON"
                                                                                                            aria-label="Download Excel 2-UP per SUBKON" style="
                                                       display:inline-flex;
                                                       align-items:center;
                                                       justify-content:center;
                                                       border:1px solid #198754;
                                                       color:#198754;
                                                       background:#fff;
                                                       text-decoration:none;
                                                       padding:6px 10px;
                                                       border-radius:6px;
                                                   ">
                                                                                                            <i class="fa fa-file-excel-o"></i>
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
                                                                                                    data-id="{{ $draft['id'] }}" {{ $draft['ainun_saved_recon'] ? 'checked' : '' }}>
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
    <!-- =========================================================
         MODAL GENERATE MAGIC APPROVAL LINK
         ========================================================= -->

    <div class="modal fade" id="magicApprovalModal" tabindex="-1" aria-labelledby="magicApprovalModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content magic-modal-content">

                <!-- HEADER -->
                <div class="modal-header magic-modal-header">

                    <div>
                        <h5 class="modal-title" id="magicApprovalModalLabel">

                            <i class="fa fa-link me-1"></i>
                            Generate Approval Link

                        </h5>

                        <div class="magic-request-label">

                            Request:

                            <strong id="magicRequestNo">
                                -
                            </strong>

                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>

                </div>


                <!-- BODY -->
                <div class="modal-body">

                    <div class="magic-section-title">
                        Pilih approver:
                    </div>


                    <!-- APPROVER LIST -->
                    <div id="magicApproverList" class="magic-approver-list">

                        <!-- Diisi oleh Javascript -->

                    </div>


                    <!-- GENERATED LINK -->
                    <div id="magicGeneratedArea" class="magic-generated-area" style="display:none;">

                        <div class="magic-generated-title">
                            <i class="fa fa-check-circle"></i>
                            Approval Link berhasil dibuat
                        </div>

                        <div class="magic-link-box">

                            <input type="text" id="magicGeneratedUrl" class="form-control" readonly>

                            <button type="button" id="btnCopyMagicLink" class="btn btn-copy-magic">

                                <i class="fa fa-copy"></i>
                                Copy

                            </button>

                            <a href="#" id="btnOpenMagicLink" class="btn btn-success" target="_blank" rel="noopener">

                                <i class="fa fa-external-link"></i>
                                Open

                            </a>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->
                <div class="modal-footer magic-modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                        Tutup

                    </button>

                    <button type="button" id="btnGenerateMagicLink" class="btn btn-primary">

                        <i class="fa fa-link"></i>

                        Generate Link

                    </button>

                </div>

            </div>

        </div>

    </div>
    <style>
        /* =========================================================
       MAGIC APPROVAL MODAL
    ========================================================= */

        .magic-approver-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .magic-approver-item {
            width: 100%;
            display: flex;
            align-items: center;

            border: 1px solid #e5e7eb;
            background: #fff;

            border-radius: 7px;

            padding: 9px 10px;

            text-align: left;

            cursor: pointer;

            transition: .15s ease;
        }

        .magic-approver-item:hover {
            background: #f8fafc;
            border-color: #0d6efd;
        }

        .magic-approver-item>i:first-child {
            width: 30px;
            height: 30px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-right: 9px;

            border-radius: 50%;

            background: #eff6ff;
            color: #0d6efd;

            font-size: 11px;
        }

        .magic-approver-item>div {
            flex: 1;
            min-width: 0;
        }

        .magic-approver-item strong {
            display: block;

            font-size: 11px;
            font-weight: 600;

            color: #344054;
        }

        .magic-approver-item small {
            display: block;

            margin-top: 2px;

            font-size: 8px;

            color: #98a2b3;
        }

        .magic-approver-item .arrow {
            width: auto;
            height: auto;

            margin: 0;

            background: transparent;

            color: #98a2b3;

            font-size: 9px;
        }

        #magicLinkInput {
            font-size: 10px;
        }

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

        /* =========================================================
           FINAL UI OVERRIDE
           - Sticky header benar-benar bekerja saat LIST di-scroll
           - Font list dibuat lebih nyaman dibaca
           - Tidak mengubah ID / class / JS / Blade
           - Horizontal scroll tetap tersedia
           ========================================================= */

        /* LIST: jadikan panel list sebagai vertical scrolling container.
           Sticky thead tidak akan bekerja jika parent hanya overflow-y:hidden. */
        .draft-list {
            max-height: calc(100vh - 250px) !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .draft-list::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        .draft-list::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .draft-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 20px;
        }

        .draft-list::-webkit-scrollbar-thumb:hover {
            background: #98a2b3;
        }

        /* Sticky header list */
        .draft-list table thead {
            position: sticky !important;
            top: 0 !important;
            z-index: 50 !important;
        }

        .draft-list table thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 51 !important;
            background: #f8f9fb !important;
            font-size: 10px !important;
            min-height: 38px;
            height: 38px !important;
            padding: 8px 9px !important;
            white-space: nowrap;
            box-shadow: 0 1px 0 #dfe3e8;
        }

        /* Font tabel utama: sebelumnya 9px, terlalu kecil */
        .draft-list table {
            font-size: 11px !important;
        }

        .draft-list table tbody td {
            font-size: 10px !important;
            padding: 8px 9px !important;
            height: 42px;
            line-height: 1.35;
        }

        /* Nomor Draft */
        .draft-request-no {
            font-size: 11px !important;
            line-height: 1.3;
        }

        /* Tombol action tetap compact tetapi teks/icon lebih terbaca */
        .draft-list .btn,
        .draft-list .btn-sm {
            font-size: 10px !important;
        }

        .draft-action-btn {
            font-size: 10px !important;
        }

        /* Badge Pending Sign */
        .draft-list .badge {
            font-size: 9px !important;
            min-height: 20px;
            padding: 4px 7px;
        }

        /* Kolom action tidak ikut mengecil */
        .draft-actions {
            gap: 6px;
        }

        /* Header card sedikit lebih nyaman */
        #draft-request-tab>.card>.card-header h5 {
            font-size: 16px !important;
        }

        #draft-request-tab>.card>.card-header small {
            font-size: 11px !important;
        }

        /* Tablet */
        @media (max-width: 992px) {
            .draft-list {
                max-height: calc(100vh - 235px) !important;
            }

            .draft-list table thead th {
                font-size: 10px !important;
            }

            .draft-list table tbody td {
                font-size: 10px !important;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .draft-list {
                max-height: calc(100vh - 220px) !important;
            }

            .draft-list table thead th {
                font-size: 9px !important;
                padding: 7px 8px !important;
            }

            .draft-list table tbody td {
                font-size: 9px !important;
                padding: 7px 8px !important;
            }

            .draft-request-no {
                font-size: 10px !important;
            }
        }

        /* Very small screen */
        @media (max-width: 480px) {
            .draft-list {
                max-height: calc(100vh - 205px) !important;
            }

            .draft-list table thead th {
                font-size: 9px !important;
            }

            .draft-list table tbody td {
                font-size: 9px !important;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('pages.payment_request.script')
    <script>
        $(document).on(
            'change',
            '#check-all-request',
            function () {
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
            function () {
                let requestDate =
                    $('#request_date').val();
                let needDate =
                    $('#need_date').val();
                let ids = [];
                $('.request-check-item:checked')
                    .each(function () {
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
                    beforeSend: function () {
                        $('#btn-save-request')
                            .prop('disabled', true)
                            .html('Saving...');
                    },
                    success: function (res) {
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
                    error: function (err) {
                        console.log(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error'
                        });
                    },
                    complete: function () {
                        $('#btn-save-request')
                            .prop('disabled', false)
                            .html('ðŸ’¾ Save Draft Request');
                    }
                });
            }
        );
    </script>

    <script>
        (function () {
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
                .on('click.paymentRequestBack', '.btn-detail-draft', function () {
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
                .on('click.paymentRequestBackList', '#btnBackDraftList', function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const wrapper = getWrapper();
                    if (!wrapper) return;

                    hideBackButton();

                    wrapper.scrollTo({
                        left: 0,
                        behavior: 'smooth'
                    });

                    setTimeout(function () {
                        wrapper.scrollLeft = 0;
                        hideBackButton();
                    }, 600);
                });

            /*
             * Auto-open by ?no_req=... also gets the button.
             */
            window.addEventListener('load', function () {
                bind();

                setTimeout(function () {
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

    <script>
        /*
    |--------------------------------------------------------------------------
    | FAST PDF EXPORT - PER SUBKON
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | - Tidak memakai html2canvas
    | - Tidak memakai AutoTable
    | - Tidak memakai groupBySupplier global
    | - Semua helper ada di dalam handler ini
    | - QTY diambil dari qty item SPK
    | - QTY IN diambil dari production_timeline.type = IN
    | - Matching QTY IN: spk_id + detail_po_id
    | - Nama item diambil dari response spk_items
    |--------------------------------------------------------------------------
    */
        (function () {

            function pdfEscape(value) {
                return String(value ?? '')
                    .replace(/\\/g, '\\\\')
                    .replace(/\(/g, '\\(')
                    .replace(/\)/g, '\\)');
            }

            function safeFileName(value, fallback) {
                const name = String(value || fallback || 'payment-request')
                    .trim()
                    .replace(/[\\/:*?"<>|]+/g, '-')
                    .replace(/\s+/g, '_');

                return name || 'payment-request';
            }

            function money(value) {
                const number = Number(value || 0);
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function formatQty(value, satuan) {
                const number = Number(value ?? 0);
                if (!Number.isFinite(number)) return '';

                const formatted = new Intl.NumberFormat('id-ID', {
                    maximumFractionDigits: 3
                }).format(number);

                return satuan ? `${formatted} ${satuan}` : formatted;
            }

            function normalizeSupplier(value) {
                const result = String(value ?? '').trim();
                return result || '-';
            }

            function normalizeSpk(value) {
                const result = String(value ?? '').trim();
                return result || '-';
            }

            function normalizeItems(payment) {
                return Array.isArray(payment && payment.spk_items) ?
                    payment.spk_items :
                    [];
            }

            /*
             * QTY IN SPK = BALANCING KOMPONEN
             *
             * Sama dengan logic pada Modal Mutasi:
             * - component qty_in tidak dijumlahkan antar komponen.
             * - setiap component mempunyai progress sendiri:
             *       qty_in / qty_spk_component
             * - Qty In item/SPK mengikuti component dengan progress terendah.
             *
             * Contoh:
             *   TOP    64 / 16  -> 25%
             *   BOTTOM 64 / 16  -> 25%
             *   => Qty In SPK = 64 x 25% = 16
             *
             * Jika backend sudah mengirim qty_in balanced tetapi components
             * tidak tersedia, gunakan qty_in tersebut sebagai fallback.
             */
            /*
             * =========================================================================
             * QTY IN PDF = LOGIC MUTASI DETAIL MODAL, LANGSUNG DARI PRODUCTION TIMELINE
             * =========================================================================
             *
             * Jangan menggunakan qty_in hasil balancing backend sebagai sumber utama.
             * PDF mengambil:
             *   1. custom_columns dari SPK
             *   2. production_timeline dari SPK + detail_po_id
             *   3. mencocokkan remark timeline ke nama component EXACT MATCH
             *   4. mengambil progress component TERENDAH sebagai QTY IN item.
             *
             * Ini sengaja dibuat sama dengan Modal Mutasi.
             */

            function normalizeComponentText(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function getComponentQtyInFromTimeline(
                timeline,
                componentName,
                componentIndex = 0
            ) {
                const target = normalizeComponentText(componentName);

                if (!target) {
                    return 0;
                }

                let qtyIn = 0;

                $.each(timeline || [], function (i, row) {
                    const type = String(row?.type || '').toLowerCase().trim();

                    // PERSIS seperti Mutasi: hanya IN dan SERVICE_MASUK.
                    if (
                        type !== 'in' &&
                        type !== 'service_masuk'
                    ) {
                        return;
                    }

                    const qty = Number(row?.qty || 0);

                    if (!Number.isFinite(qty) || qty === 0) {
                        return;
                    }

                    const remark = normalizeComponentText(row?.remark);

                    // Remark kosong -> komponen pertama.
                    if (!remark) {
                        if (componentIndex === 0) {
                            qtyIn += qty;
                        }
                        return;
                    }

                    // Remark ada -> EXACT MATCH nama component.
                    if (remark === target) {
                        qtyIn += qty;
                    }
                });

                return qtyIn;
            }

            function getPdfComponentRows(item) {
                const customColumns =
                    Array.isArray(item?.custom_columns) ?
                        item.custom_columns :
                        [];

                const timeline =
                    Array.isArray(item?.production_timeline) ?
                        item.production_timeline :
                        [];

                const rows = [];

                $.each(customColumns, function (index, component) {
                    if (!component || typeof component !== 'object') {
                        return;
                    }

                    let componentName = '';

                    const preferredNameKeys = [
                        'nama',
                        'name',
                        'nama_material',
                        'nama_bahan',
                        'bahan',
                        'triplek',
                        'finishing',
                        'komponen',
                        'component',
                        'description'
                    ];

                    // PERSIS urutan pencarian nama component di Mutasi.
                    for (const key of preferredNameKeys) {
                        const value = component[key];

                        if (
                            typeof value === 'string' &&
                            value.trim() &&
                            ![
                                '-',
                                'null',
                                'undefined',
                                'n/a',
                                'na'
                            ].includes(value.trim().toLowerCase())
                        ) {
                            componentName = value.trim();
                            break;
                        }
                    }

                    // PERSIS fallback Mutasi.
                    if (!componentName) {
                        $.each(component, function (key, value) {
                            const keyLower = String(key).toLowerCase();

                            if (
                                typeof value !== 'string' ||
                                !value.trim()
                            ) {
                                return;
                            }

                            const cleanValue =
                                value.trim().toLowerCase();

                            if (
                                [
                                    'harga',
                                    'material',
                                    'pcs',
                                    'set',
                                    'total',
                                    'p',
                                    'l',
                                    't',
                                    'qty',
                                    'kode',
                                    'id'
                                ].includes(keyLower)
                            ) {
                                return;
                            }

                            if (
                                [
                                    '-',
                                    'null',
                                    'undefined',
                                    'n/a',
                                    'na'
                                ].includes(cleanValue)
                            ) {
                                return;
                            }

                            componentName = value.trim();
                            return false;
                        });
                    }

                    if (!componentName) {
                        componentName = `Komponen ${index + 1}`;
                    }

                    const qtySpk =
                        component.pcs !== undefined &&
                            component.pcs !== null &&
                            component.pcs !== '' &&
                            !Number.isNaN(Number(component.pcs)) ?
                            Number(component.pcs) :
                            Number(item?.qty || 0);

                    const qtyIn =
                        getComponentQtyInFromTimeline(
                            timeline,
                            componentName,
                            index
                        );

                    rows.push({
                        name: componentName,
                        qtySpk: qtySpk,
                        qtyIn: qtyIn
                    });
                });

                // Sama dengan Mutasi: jika tidak ada component,
                // fallback ke seluruh Qty In item-level.
                if (!rows.length) {
                    let qtyIn = 0;

                    $.each(timeline, function (i, row) {
                        const type =
                            String(row?.type || '')
                                .toLowerCase()
                                .trim();

                        if (
                            type === 'in' ||
                            type === 'service_masuk'
                        ) {
                            qtyIn += Number(row?.qty || 0);
                        }
                    });

                    rows.push({
                        name: item?.nama || '-',
                        qtySpk: Number(item?.qty || 0),
                        qtyIn: qtyIn
                    });
                }

                return rows;
            }

            function getBalancedQtyIn(item) {
                const qtySpk =
                    Number(item?.qty ?? 0) || 0;

                if (qtySpk <= 0) {
                    return 0;
                }

                const components =
                    getPdfComponentRows(item);

                const progressValues =
                    components
                        .map(function (component) {
                            const componentQty =
                                Number(component?.qtySpk ?? 0) || 0;

                            const componentIn =
                                Number(component?.qtyIn ?? 0) || 0;

                            if (componentQty <= 0) {
                                return null;
                            }

                            return Math.max(
                                0,
                                componentIn / componentQty
                            );
                        })
                        .filter(function (value) {
                            return value !== null &&
                                Number.isFinite(value);
                        });

                if (progressValues.length) {
                    const minimumProgress =
                        Math.min.apply(
                            null,
                            progressValues
                        );

                    return Math.min(
                        qtySpk,
                        qtySpk * minimumProgress
                    );
                }

                return 0;
            }

            /*
             * Nominal yang dipakai di PDF:
             * - jika adjustment berisi angka selain 0 -> adjustment
             * - jika adjustment kosong/null/0 -> payment_amount
             */
            function effectiveAmount(payment) {
                const adjustmentRaw = payment && payment.adjustment;

                if (
                    adjustmentRaw !== null &&
                    adjustmentRaw !== undefined &&
                    adjustmentRaw !== '' &&
                    Number(adjustmentRaw) !== 0
                ) {
                    return Number(adjustmentRaw);
                }

                return Number(payment?.payment_amount || 0);
            }

            function buildGroups(payments) {
                const groups = new Map();

                payments.forEach(function (payment) {
                    const supplier = normalizeSupplier(
                        payment.supplier ?? payment.sup
                    );

                    if (!groups.has(supplier)) {
                        groups.set(supplier, {
                            supplier: supplier,
                            payments: [],
                            spks: new Map(),
                            total: 0
                        });
                    }

                    const group = groups.get(supplier);
                    group.payments.push(payment);
                    group.total += effectiveAmount(payment);

                    const spkNo = normalizeSpk(payment.spk_no);

                    if (!group.spks.has(spkNo)) {
                        group.spks.set(spkNo, {
                            spk_no: spkNo,
                            no_po: payment.no_po ?? '-',
                            items: []
                        });
                    }

                    const spk = group.spks.get(spkNo);
                    const items = normalizeItems(payment);

                    items.forEach(function (raw) {
                        const name = String(raw?.nama ?? '').trim();

                        if (!name) return;

                        // Ambil identitas + quantity dari SPK.
                        // qty_in berasal dari production_timeline yang sudah
                        // dipetakan oleh endpoint detailDraft().
                        const duplicate = spk.items.some(function (old) {
                            return String(old.nama).trim() === name;
                        });

                        if (!duplicate) {
                            spk.items.push({
                                nama: name,
                                kode: raw?.kode ?? '-',
                                qty: raw?.qty ?? 0,
                                qty_in: raw?.qty_in ?? 0,
                                components: Array.isArray(raw?.components) ?
                                    raw.components :
                                    [],
                                custom_columns: Array.isArray(raw?.custom_columns) ?
                                    raw.custom_columns :
                                    (typeof raw?.custom_columns === 'string' ?
                                        (() => {
                                            try {
                                                const parsed = JSON.parse(raw
                                                    .custom_columns);
                                                return Array.isArray(parsed) ? parsed : [];
                                            } catch (e) {
                                                return [];
                                            }
                                        })() :
                                        []),
                                production_timeline: Array.isArray(raw?.production_timeline) ?
                                    raw.production_timeline :
                                    [],
                                satuan: raw?.satuan ?? raw?.sat ?? 'pcs',
                                detail_po_id: raw?.detail_po_id ?? raw?.detail_id ?? null
                            });
                        }
                    });
                });

                return Array.from(groups.values());
            }

            function addHeader(pdf, requestNo, requestDate, supplier, pageWidth) {
                let y = 12;

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(13);
                pdf.text('LIST PAYMENT', pageWidth / 2, y, {
                    align: 'center'
                });

                y += 5;

                pdf.setFontSize(8);
                pdf.text(
                    `PENGAJUAN TANGGAL ${requestDate || '-'}`,
                    pageWidth / 2,
                    y, {
                    align: 'center'
                }
                );

                y += 7;

                pdf.setFontSize(9);
                pdf.text(`REQUEST : ${requestNo || '-'}`, 10, y);

                y += 5;

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(10);
                pdf.text(`SUBKON : ${supplier}`, 10, y);

                return y + 7;
            }

            function drawPaymentTable(pdf, payments, startY, pageWidth, pageHeight) {
                let y = startY;
                const x = 10;
                const widths = [12, 45, 105, 45, 70];
                const headers = ['No', 'PO', 'NO SPK', 'Payment', 'Amount'];
                const rowHeight = 6;
                const tableWidth = widths.reduce((a, b) => a + b, 0);

                function header() {
                    pdf.setFillColor(245, 247, 250);
                    pdf.rect(x, y, tableWidth, rowHeight, 'F');
                    pdf.setDrawColor(80, 80, 80);
                    pdf.rect(x, y, tableWidth, rowHeight);

                    pdf.setFont('helvetica', 'bold');
                    pdf.setFontSize(6.5);

                    let cx = x;
                    headers.forEach(function (head, i) {
                        pdf.rect(cx, y, widths[i], rowHeight);
                        pdf.text(head, cx + 2, y + 4);
                        cx += widths[i];
                    });

                    y += rowHeight;
                }

                function newPage() {
                    pdf.addPage();
                    y = 12;
                    header();
                }

                header();

                payments.forEach(function (payment, index) {
                    if (y + rowHeight > pageHeight - 12) {
                        newPage();
                    }

                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(6.5);

                    const values = [
                        String(index + 1),
                        String(payment.no_po ?? '-'),
                        String(payment.spk_no ?? '-'),
                        String(payment.payment_note ?? '-'),
                        `Rp ${money(effectiveAmount(payment))}`
                    ];

                    let cx = x;

                    values.forEach(function (value, i) {
                        pdf.rect(cx, y, widths[i], rowHeight);

                        const maxChars = i === 2 ? 28 : (i === 1 ? 18 : 20);
                        let text = value;
                        if (text.length > maxChars) {
                            text = text.substring(0, maxChars - 1) + '…';
                        }

                        pdf.text(text, cx + 2, y + 4);
                        cx += widths[i];
                    });

                    y += rowHeight;
                });

                return y + 7;
            }

            /*
             * ================================================================
             * TIMELINE PEMASUKAN + TIMELINE QC INSPECT
             * ================================================================
             *
             * UI PDF dibuat sengaja lebih clean:
             * - Section header berbeda untuk Production dan QC.
             * - Header tabel kontras tetapi tidak terlalu berat.
             * - Angka rata kanan / center sesuai jenis data.
             * - Total ditampilkan sebagai summary row.
             * - Jika data melewati halaman, header tabel diulang.
             */

            function pdfText(value, fallback = '-') {
                const result = String(value ?? '').trim();
                return result || fallback;
            }

            function truncatePdfText(value, maxLength) {
                const text = pdfText(value, '');
                if (!text) return '-';
                if (text.length <= maxLength) return text;
                return text.substring(0, Math.max(1, maxLength - 1)) + '…';
            }

            function drawSectionTitle(pdf, title, subtitle, y, pageWidth, accent) {
                const x = 10;
                const width = pageWidth - 20;
                const titleHeight = 9;

                pdf.setFillColor(...accent);
                pdf.rect(x, y, 3, titleHeight, 'F');

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(8.2);
                pdf.setTextColor(30, 41, 59);
                pdf.text(title, x + 7, y + 4.3);

                if (subtitle) {
                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(5.8);
                    pdf.setTextColor(100, 116, 139);
                    pdf.text(subtitle, x + 7, y + 7.1);
                }

                // garis tipis sebagai separator
                pdf.setDrawColor(226, 232, 240);
                pdf.line(x + 7, y + titleHeight, x + width, y + titleHeight);

                pdf.setTextColor(30, 41, 59);

                return y + titleHeight + 3;
            }

            function drawTableHeader(pdf, x, y, widths, labels, options = {}) {
                const height = options.height || 7;
                const fill = options.fill || [241, 245, 249];
                const textColor = options.textColor || [51, 65, 85];

                pdf.setFillColor(...fill);
                pdf.setDrawColor(203, 213, 225);
                pdf.rect(
                    x,
                    y,
                    widths.reduce((a, b) => a + b, 0),
                    height,
                    'FD'
                );

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(options.fontSize || 6.2);
                pdf.setTextColor(...textColor);

                let cx = x;

                labels.forEach(function (label, index) {
                    const width = widths[index];

                    if (index > 0) {
                        pdf.setDrawColor(226, 232, 240);
                        pdf.line(cx, y, cx, y + height);
                    }

                    pdf.text(
                        pdfText(label, ''),
                        cx + width / 2,
                        y + 4.6, {
                        align: 'center'
                    }
                    );

                    cx += width;
                });

                pdf.setTextColor(30, 41, 59);

                return y + height;
            }

            function drawTimelineRow(pdf, x, y, widths, values, options = {}) {
                const height = options.height || 7;
                const fill = options.fill || [255, 255, 255];
                const fontSize = options.fontSize || 6.1;
                const aligns = options.aligns || [];

                const tableWidth = widths.reduce((a, b) => a + b, 0);

                pdf.setFillColor(...fill);
                pdf.setDrawColor(226, 232, 240);
                pdf.rect(x, y, tableWidth, height, 'FD');

                let cx = x;

                values.forEach(function (value, index) {
                    const width = widths[index];

                    if (index > 0) {
                        pdf.setDrawColor(238, 242, 247);
                        pdf.line(cx, y, cx, y + height);
                    }

                    pdf.setFont(
                        'helvetica',
                        options.boldIndexes?.includes(index) ?
                            'bold' :
                            'normal'
                    );
                    pdf.setFontSize(fontSize);

                    const align = aligns[index] || 'left';
                    const display = truncatePdfText(
                        value,
                        options.maxChars?.[index] || 35
                    );

                    let textX = cx + 2;

                    if (align === 'center') {
                        textX = cx + width / 2;
                    } else if (align === 'right') {
                        textX = cx + width - 2;
                    }

                    pdf.text(
                        display,
                        textX,
                        y + 4.5, {
                        align
                    }
                    );

                    cx += width;
                });

                return y + height;
            }

            function drawSummaryRow(pdf, x, y, widths, label, totals, options = {}) {
                const height = options.height || 7;
                const tableWidth = widths.reduce((a, b) => a + b, 0);

                pdf.setFillColor(...(options.fill || [248, 250, 252]));
                pdf.setDrawColor(203, 213, 225);
                pdf.rect(x, y, tableWidth, height, 'FD');

                let cx = x;

                for (let i = 0; i < widths.length; i++) {
                    if (i > 0) {
                        pdf.setDrawColor(226, 232, 240);
                        pdf.line(cx, y, cx, y + height);
                    }
                    cx += widths[i];
                }

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(6.2);
                pdf.setTextColor(51, 65, 85);

                // label berada di dua kolom terakhir sebelum angka total,
                // tetapi caller bisa menentukan total secara eksplisit.
                const labelIndex = options.labelIndex ?? 0;

                let labelX = x;
                for (let i = 0; i < labelIndex; i++) {
                    labelX += widths[i];
                }

                const labelWidth = widths
                    .slice(labelIndex, widths.length - totals.length)
                    .reduce((a, b) => a + b, 0);

                pdf.text(
                    label,
                    labelX + labelWidth / 2,
                    y + 4.5, {
                    align: 'center'
                }
                );

                let totalX = labelX + labelWidth;

                totals.forEach(function (total, index) {
                    const width = widths[widths.length - totals.length + index];

                    pdf.text(
                        total,
                        totalX + width / 2,
                        y + 4.5, {
                        align: 'center'
                    }
                    );

                    totalX += width;
                });

                pdf.setTextColor(30, 41, 59);

                return y + height;
            }

            /* ================================================================
             * TIMELINE GABUNGAN PEMASUKAN + QC INSPECT
             * Satu tabel: tanggal yang sama digabung menjadi satu baris.
             * ================================================================ */

            function normalizeTimelineDate(value) {
                const raw = String(value ?? '').trim();
                if (!raw) return '';

                let match = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
                if (match) {
                    return `${match[1]}-${String(match[2]).padStart(2, '0')}-${String(match[3]).padStart(2, '0')}`;
                }

                match = raw.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})/);
                if (match) {
                    return `${match[3]}-${String(match[2]).padStart(2, '0')}-${String(match[1]).padStart(2, '0')}`;
                }

                return raw;
            }

            function displayTimelineDate(value) {
                const key = normalizeTimelineDate(value);
                const match = key.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                return match ? `${match[3]}/${match[2]}/${match[1]}` : pdfText(value, '-');
            }

            function drawCombinedTimeline(pdf, item, startY, x, contentWidth, pageHeight) {
                let y = startY;
                const widths = [8, 29, contentWidth - 8 - 29 - 31 - 31, 31, 31];
                const unit = item?.satuan ?? item?.sat ?? '';
                const rowsMap = new Map();

                function ensure(key, date) {
                    if (!rowsMap.has(key)) {
                        rowsMap.set(key, {
                            dateKey: key,
                            date: date || '-',
                            descriptions: [],
                            inQty: 0,
                            passed: 0,
                            hasIn: false,
                            hasQc: false
                        });
                    }
                    return rowsMap.get(key);
                }

                const production = Array.isArray(item?.production_timeline) ?
                    item.production_timeline.filter(function (row) {
                        const type = String(row?.type || '').toLowerCase().trim();
                        return type === 'in' || type === 'service_masuk';
                    }) : [];

                production.forEach(function (row, idx) {
                    const rawDate = row?.tanggal ?? row?.date ?? row?.created_at ?? '';
                    const key = normalizeTimelineDate(rawDate) || `production-${idx}`;
                    const entry = ensure(key, displayTimelineDate(rawDate));
                    entry.inQty += Number(row?.qty ?? 0) || 0;
                    entry.hasIn = true;
                    const remark = String(row?.remark ?? '').trim();
                    if (remark && !entry.descriptions.includes(remark)) entry.descriptions.push(remark);
                });

                const inspect = Array.isArray(item?.inspect_timeline) ? item.inspect_timeline : [];
                inspect.forEach(function (row, idx) {
                    const rawDate = row?.tanggal_inspect ?? row?.tanggal ?? row?.date ?? '';
                    const key = normalizeTimelineDate(rawDate) || `qc-${idx}`;
                    const entry = ensure(key, displayTimelineDate(rawDate));
                    entry.passed += Number(row?.passed ?? 0) || 0;
                    entry.hasQc = true;
                });

                const rows = Array.from(rowsMap.values()).sort(function (a, b) {
                    return String(a.dateKey).localeCompare(String(b.dateKey));
                });
                if (!rows.length) return y;

                const titleHeight = 8;
                pdf.setFillColor(34, 197, 94);
                pdf.rect(x, y, 2.5, titleHeight, 'F');
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(8);
                pdf.setTextColor(30, 41, 59);
                pdf.text('TIMELINE PEMASUKAN & QC', x + 5, y + 3.6);
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(5.8);
                pdf.setTextColor(100, 116, 139);
                pdf.text('IN dan PASSED berdasarkan tanggal', x + 5, y + 5.7);
                pdf.setDrawColor(226, 232, 240);
                pdf.line(x + 5, y + titleHeight, x + contentWidth, y + titleHeight);
                y += titleHeight + 2;

                y = drawTableHeader(pdf, x, y, widths, ['NO', 'TANGGAL', 'DESCRIPTION', 'IN', 'PASSED'], {
                    height: 7,
                    fill: [241, 245, 249],
                    textColor: [51, 65, 85],
                    fontSize: 6
                });

                let totalIn = 0;
                let totalPassed = 0;
                rows.forEach(function (row, index) {
                    totalIn += row.inQty;
                    totalPassed += row.passed;
                    const description = row.descriptions.length ? row.descriptions.join(', ') : (row.hasQc ?
                        'QC Inspect' : 'Barang masuk');
                    y = drawTimelineRow(pdf, x, y, widths, [
                        String(index + 1), row.date, description,
                        row.hasIn ? formatQty(row.inQty, unit) : '-',
                        row.hasQc ? formatQty(row.passed, unit) : '-'
                    ], {
                        height: 7,
                        fontSize: 5.8,
                        aligns: ['center', 'center', 'left', 'right', 'right'],
                        boldIndexes: [3, 4],
                        maxChars: [4, 10, 34, 12, 12],
                        fill: index % 2 === 0 ? [255, 255, 255] : [248, 250, 252]
                    });
                });

                const tableWidth = widths.reduce((a, b) => a + b, 0);
                pdf.setFillColor(248, 250, 252);
                pdf.setDrawColor(203, 213, 225);
                pdf.rect(x, y, tableWidth, 7, 'FD');
                let sx = x;
                widths.forEach(function (width, index) {
                    if (index > 0) {
                        pdf.setDrawColor(226, 232, 240);
                        pdf.line(sx, y, sx, y + 7);
                    }
                    sx += width;
                });
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(5.8);
                pdf.setTextColor(51, 65, 85);
                const labelX = x + widths[0] + widths[1];
                pdf.text('TOTAL', labelX + widths[2] / 2, y + 4.5, {
                    align: 'center'
                });
                pdf.text(formatQty(totalIn, unit), labelX + widths[2] + widths[3] / 2, y + 4.5, {
                    align: 'center'
                });
                pdf.text(formatQty(totalPassed, unit), labelX + widths[2] + widths[3] + widths[4] / 2, y + 4.5, {
                    align: 'center'
                });
                pdf.setTextColor(30, 41, 59);
                return y + 9;
            }

            function drawColumnHeader(pdf, requestNo, requestDate, supplier, x, y, width) {
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(12);
                pdf.setTextColor(30, 41, 59);
                pdf.text('LIST PAYMENT', x + width / 2, y, {
                    align: 'center'
                });
                y += 4.5;
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(7);
                pdf.text(`PENGAJUAN ${requestDate || '-'}`, x + width / 2, y, {
                    align: 'center'
                });
                y += 6;
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(8);
                pdf.text(`REQUEST : ${requestNo || '-'}`, x, y);
                y += 4;
                pdf.text(`SUBKON : ${truncatePdfText(supplier, 38)}`, x, y);
                return y + 5;
            }

            function drawColumnPaymentTable(pdf, payments, x, y, width) {
                // Lebar tabel dibuat compact agar tidak ada kolom yang terlalu lebar.
                // NO SPK tetap ditampilkan FULL, tanpa ellipsis.
                const widths = [7, 24, 67, 25, 32];
                const rowH = 6.5;
                y = drawTableHeader(pdf, x, y, widths, ['No', 'PO', 'NO SPK', 'Payment', 'Amount'], {
                    height: rowH,
                    fill: [30, 41, 59],
                    textColor: [255, 255, 255],
                    fontSize: 6
                });

                function drawFitText(value, cellX, cellWidth, baseSize, align) {
                    const text = String(value ?? '-').trim() || '-';
                    let size = baseSize;
                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(size);
                    while (size > 4.0 && pdf.getTextWidth(text) > cellWidth - 3) {
                        size -= 0.2;
                        pdf.setFontSize(size);
                    }
                    const tx = align === 'right' ?
                        cellX + cellWidth - 1.5 :
                        align === 'center' ?
                            cellX + cellWidth / 2 :
                            cellX + 1.5;
                    pdf.text(text, tx, y + 4.4, {
                        align
                    });
                }

                payments.forEach(function (payment, index) {
                    const fill = index % 2 === 0 ? [255, 255, 255] : [248, 250, 252];
                    const values = [
                        String(index + 1),
                        String(payment.no_po ?? '-'),
                        String(payment.spk_no ?? '-'),
                        String(payment.payment_note ?? '-'),
                        `Rp ${money(effectiveAmount(payment))}`
                    ];

                    pdf.setFillColor(...fill);
                    pdf.setDrawColor(226, 232, 240);
                    const tableWidth = widths.reduce((a, b) => a + b, 0);
                    pdf.rect(x, y, tableWidth, rowH, 'FD');

                    let cx = x;
                    values.forEach(function (value, i) {
                        if (i > 0) {
                            pdf.setDrawColor(238, 242, 247);
                            pdf.line(cx, y, cx, y + rowH);
                        }
                        pdf.setTextColor(30, 41, 59);
                        const align = i === 0 ? 'center' : (i === 4 ? 'right' : 'left');
                        drawFitText(value, cx, widths[i], i === 2 ? 5.2 : 5.5, align);
                        cx += widths[i];
                    });
                    y += rowH;
                });

                return y + 4;
            }

            function estimatePaymentHeight(payment) {
                const items = Array.isArray(payment.spk_items) ? payment.spk_items : [];
                return items.reduce(function (sum, item) {
                    const p = Array.isArray(item?.production_timeline) ? item.production_timeline : [];
                    const q = Array.isArray(item?.inspect_timeline) ? item.inspect_timeline : [];
                    const timelineRows = Math.max(1, p.length + q.length);
                    return sum + 11 + 6 + timelineRows * 6 + 9;
                }, 0) + 5;
            }

            function drawCompactPaymentBlock(pdf, payment, x, y, width, pageHeight) {
                const noWidth = 8;
                const poWidth = 29;
                const qtyWidth = 28;
                const qtyInWidth = 30;
                const nameWidth = width - noWidth - poWidth - qtyWidth - qtyInWidth;
                const spkNo = String(payment.spk_no ?? '-').trim() || '-';
                const noPo = String(payment.no_po ?? '-').trim() || '-';
                const required = estimatePaymentHeight(payment);

                if (y + required > pageHeight - 8) return {
                    fits: false,
                    y
                };

                pdf.setFillColor(248, 250, 252);
                pdf.setDrawColor(148, 163, 184);
                pdf.rect(x, y, width, 5.5, 'FD');
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(6.2);
                pdf.setTextColor(30, 41, 59);
                pdf.text('NO SPK', x + 1.5, y + 3.7);

                // NO SPK wajib full, tetapi ukuran font otomatis diperkecil bila perlu.
                let spkSize = 6;
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(spkSize);
                const spkX = x + 12;
                const spkAvailable = width - 13.5;
                while (spkSize > 4.5 && pdf.getTextWidth(spkNo) > spkAvailable) {
                    spkSize -= 0.2;
                    pdf.setFontSize(spkSize);
                }
                pdf.text(spkNo, spkX, y + 3.7);
                y += 5.5;

                pdf.setFillColor(30, 41, 59);
                pdf.rect(x, y, width, 6, 'F');
                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(5.8);
                pdf.setTextColor(255, 255, 255);
                pdf.text('NO', x + 1.5, y + 4.5);
                pdf.text('NAME', x + noWidth + 1.5, y + 4.5);
                pdf.text('PO', x + noWidth + nameWidth + 1.5, y + 4.5);
                pdf.text('QTY', x + noWidth + nameWidth + poWidth + 1.5, y + 4.5);
                pdf.text('QTY IN', x + noWidth + nameWidth + poWidth + qtyWidth + 1.5, y + 4.5);
                pdf.setTextColor(30, 41, 59);
                y += 6;

                const items = Array.isArray(payment.spk_items) ? payment.spk_items : [];
                if (!items.length) {
                    pdf.setFillColor(255, 255, 255);
                    pdf.rect(x, y, width, 6, 'FD');
                    pdf.setFont('helvetica', 'normal');
                    pdf.setFontSize(5.8);
                    pdf.text('1.', x + 1.5, y + 4.5);
                    pdf.text('Item detail tidak tersedia', x + noWidth + 1.5, y + 4.5);
                    y += 6;
                } else {
                    items.forEach(function (rawItem, itemIndex) {
                        const name = String(rawItem?.nama ?? '').trim();
                        if (!name) return;
                        const rowH = 7;
                        pdf.setFillColor(itemIndex % 2 === 0 ? 255 : 248, itemIndex % 2 === 0 ? 255 : 250,
                            itemIndex % 2 === 0 ? 255 : 252);
                        pdf.setDrawColor(203, 213, 225);
                        pdf.rect(x, y, width, rowH, 'FD');
                        pdf.setFont('helvetica', 'normal');
                        pdf.setFontSize(5.8);
                        pdf.text(`${itemIndex + 1}.`, x + 1.5, y + 4.5);
                        pdf.text(truncatePdfText(name, 48), x + noWidth + 1.5, y + 4.5);
                        pdf.text(truncatePdfText(noPo, 14), x + noWidth + nameWidth + 1.5, y + 4.5);
                        pdf.text(formatQty(rawItem?.qty ?? 0, rawItem?.satuan ?? rawItem?.sat ?? ''), x +
                            noWidth + nameWidth + poWidth + 1.5, y + 4.5);

                        const balancedQtyIn = getBalancedQtyIn(rawItem);
                        pdf.setFillColor(240, 253, 244);
                        pdf.rect(x + noWidth + nameWidth + poWidth + qtyWidth, y, qtyInWidth, rowH, 'F');
                        pdf.setTextColor(22, 101, 52);
                        pdf.setFont('helvetica', 'bold');
                        pdf.text(formatQty(balancedQtyIn, rawItem?.satuan ?? rawItem?.sat ?? ''), x + noWidth +
                            nameWidth + poWidth + qtyWidth + 1.5, y + 4.5);
                        pdf.setTextColor(30, 41, 59);
                        y += rowH;
                        y = drawCombinedTimeline(pdf, rawItem, y + 2, x, width, pageHeight);
                        y += 1;
                    });
                }
                return {
                    fits: true,
                    y: y + 4
                };
            }

            function drawTwoUpGroup(pdf, group, requestNo, requestDate, pageWidth, pageHeight) {
                const margin = 10;
                const gutter = 8;
                const colWidth = (pageWidth - margin * 2 - gutter) / 2;
                // Konten dibuat compact dan centered agar tidak terlihat terlalu melebar.
                const contentWidth = Math.min(colWidth, 165);
                const sheets = [
                    [
                        [],
                        []
                    ]
                ];
                let sheetIndex = 0;
                let colIndex = 0;
                const usableHeight = pageHeight - margin - 8;
                const headerEstimate = 35;

                group.payments.forEach(function (payment) {
                    const estimated = estimatePaymentHeight(payment);
                    const current = sheets[sheetIndex][colIndex];
                    const currentHeight = current.reduce(function (sum, p) {
                        return sum + estimatePaymentHeight(p);
                    }, 0);
                    const topSpace = headerEstimate + 5.5 + (current.length * 5.5) + 4;

                    if (current.length && topSpace + currentHeight + estimated > usableHeight) {
                        if (colIndex === 0) {
                            colIndex = 1;
                        } else {
                            sheets.push([
                                [],
                                []
                            ]);
                            sheetIndex += 1;
                            colIndex = 0;
                        }
                    }
                    sheets[sheetIndex][colIndex].push(payment);
                });

                sheets.forEach(function (sheet, physicalIndex) {
                    if (physicalIndex > 0) {
                        pdf.addPage();
                    }

                    sheet.forEach(function (payments, idx) {
                        // Jangan gambar kolom/header kosong.
                        if (!Array.isArray(payments) || payments.length === 0) return;

                        const xBase = margin + idx * (colWidth + gutter);
                        const x = xBase + (colWidth - contentWidth) / 2;
                        let y = drawColumnHeader(pdf, requestNo, requestDate, group.supplier, x, margin,
                            contentWidth);
                        y = drawColumnPaymentTable(pdf, payments, x, y, contentWidth);

                        payments.forEach(function (payment) {
                            const result = drawCompactPaymentBlock(pdf, payment, x, y,
                                contentWidth, pageHeight);
                            if (result.fits) y = result.y;
                        });
                    });

                    // Garis pemisah hanya bila kedua sisi memang terisi.
                    if (sheet[0].length && sheet[1].length) {
                        pdf.setDrawColor(226, 232, 240);
                        pdf.setLineDashPattern([1.2, 1.2], 0);
                        pdf.line(
                            margin + colWidth + gutter / 2,
                            margin,
                            margin + colWidth + gutter / 2,
                            pageHeight - margin
                        );
                        pdf.setLineDashPattern([], 0);
                    }
                });
            }

            $(document).on(
                'click.paymentRequestPdf',
                '.btn-download-draft-pdf',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const button = $(this);
                    const draftId = button.data('id');
                    const requestNo = button.data('request') || '';
                    const originalHtml = button.html();

                    if (!draftId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'ID draft tidak ditemukan.'
                        });
                        return;
                    }

                    if (
                        !window.jspdf ||
                        typeof window.jspdf.jsPDF !== 'function'
                    ) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Library PDF belum siap',
                            text: 'Silakan refresh halaman lalu coba lagi.'
                        });
                        return;
                    }

                    button
                        .prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: `/payment-request-saved/${draftId}/detail`,
                        type: 'GET',
                        dataType: 'json',

                        success: function (res) {
                            try {
                                const payments = Array.isArray(res.items) ?
                                    res.items :
                                    [];

                                if (!payments.length) {
                                    throw new Error('Payment pada draft ini tidak ditemukan.');
                                }

                                // Semua grouping dibuat lokal di sini.
                                // Tidak ada dependency groupBySupplier global.
                                const groups = buildGroups(payments);
                                const {
                                    jsPDF
                                } = window.jspdf;
                                const pdf = new jsPDF({
                                    // Satu lembar fisik = dua logical page A4 portrait berdampingan.
                                    // Menggunakan A3 landscape agar masing-masing sisi tetap lega.
                                    orientation: 'landscape',
                                    unit: 'mm',
                                    format: 'a3',
                                    compress: true
                                });

                                const pageWidth = pdf.internal.pageSize.getWidth();
                                const pageHeight = pdf.internal.pageSize.getHeight();

                                groups.forEach(function (group, groupIndex) {
                                    if (groupIndex > 0) {
                                        pdf.addPage();
                                    }

                                    drawTwoUpGroup(
                                        pdf,
                                        group,
                                        requestNo || res.request_no,
                                        res.request_date,
                                        pageWidth,
                                        pageHeight
                                    );
                                });

                                const fileName =
                                    `Payment-Request-${safeFileName(
                                        requestNo || res.request_no,
                                        draftId
                                    )}.pdf`;

                                pdf.save(fileName);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'PDF berhasil dibuat',
                                    text: `${groups.length} SUBKON dibuat dengan layout 2 halaman dalam 1 lembar.`,
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            } catch (error) {
                                console.error('PDF EXPORT ERROR:', error);

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal membuat PDF',
                                    text: error.message || 'Terjadi kesalahan saat membuat PDF.'
                                });
                            }
                        },

                        error: function (xhr) {
                            console.error('PDF DETAIL ERROR:', xhr);

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal mengambil detail',
                                text: xhr.responseJSON?.message ||
                                    'Gagal mengambil detail draft.'
                            });
                        },

                        complete: function () {
                            button
                                .prop('disabled', false)
                                .html(originalHtml);
                        }
                    });
                }
            );
        })();
    </script>



    <script>
        /*
    |--------------------------------------------------------------------------
    | RECON AINUN - CHECKBOX HANDLER
    |--------------------------------------------------------------------------
    | Endpoint yang digunakan: POST /payment-request-saved/{id}/set-recon
    | Backend saat ini hanya menandai recon sebagai selesai (tidak ada undo).
    */
        (function () {
            if (window.paymentRequestReconBound) return;
            window.paymentRequestReconBound = true;

            $(document).on('change.paymentRequestRecon', '.ainun-recon-check', function () {
                const checkbox = $(this);
                const savedId = checkbox.data('id');
                const isChecked = checkbox.is(':checked');
                const previousChecked = !isChecked;
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                if (!savedId) {
                    checkbox.prop('checked', previousChecked);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'ID Payment Request tidak ditemukan.'
                    });
                    return;
                }

                // Controller setSavedRecon hanya menjalankan recon saat dicentang.
                // Mencegah status di UI dibatalkan tanpa endpoint undo dari backend.
                if (!isChecked) {
                    checkbox.prop('checked', true);
                    Swal.fire({
                        icon: 'info',
                        title: 'Recon sudah diproses',
                        text: 'Status recon tidak dapat dibatalkan dari checkbox ini.'
                    });
                    return;
                }

                checkbox.prop('disabled', true);

                $.ajax({
                    url: `/payment-request-saved/${encodeURIComponent(savedId)}/set-recon`,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: csrfToken
                    },
                    success: function (response) {
                        if (!response || response.success !== true) {
                            checkbox.prop('checked', false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Recon gagal',
                                text: response?.message ||
                                    'Server tidak berhasil menyimpan reconciliation.'
                            });
                            return;
                        }

                        checkbox.prop('checked', true);
                        Swal.fire({
                            icon: 'success',
                            title: 'Recon berhasil',
                            text: response.message || 'Reconciliation berhasil diproses.',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        checkbox.prop('checked', false);
                        console.error('PAYMENT REQUEST RECON ERROR:', xhr);

                        Swal.fire({
                            icon: 'error',
                            title: 'Recon gagal',
                            text: xhr.responseJSON?.message ||
                                xhr.responseJSON?.error ||
                                'Tidak dapat memproses reconciliation. Periksa log Laravel untuk detailnya.'
                        });
                    },
                    complete: function () {
                        checkbox.prop('disabled', false);
                    }
                });
            });
        })();
    </script>

@endsection