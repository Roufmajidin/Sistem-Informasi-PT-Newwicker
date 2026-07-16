@extends('master.master')
@section('title','Draft payment request')
@section('content')
<div class="box mt-4">
    <div class="box-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Payment Request (Nur)</h3>
    </div>
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
                                    Purchase Request
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
                                            <input type="date" id="need_date"
                                                value="{{ now()->format('Y-m-d') }}" style="
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
                                <input type="date" id="request_date"
                                    value="{{ now()->format('Y-m-d') }}" style="
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
                            💾 Save Draft Request
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
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no = 1; @endphp
                                @foreach($requests as $row)
                                    <tr>
                                        <td class="pr-td" align="center">
                                            <input type="checkbox" class="request-check-item"
                                                value="{{ $row['id'] }}">
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
                                            {{ number_format($row['payment_amount'],0,',','.') }}
                                        </td>
                                        <td class="pr-td" align="right">
                                            Rp
                                            {{ number_format($row['payment_amount'],0,',','.') }}
                                        </td>
                                        <td class="pr-td">
                                            <span style="color:red;font-weight:bold;">
                                                urgent
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                        </tbody>
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
                        {{
                            $authUser->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $kepalaPurchasing->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $prodManager->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $ceo->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $vpSales->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $finance->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                        {{
                            $coo->signature
                            ?? 'https://dummyimage.com/120x50/ffffff/000000&text=SIGN'
                        }}
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
                    <div class="card-header">
                        <h5 class="mb-0">
                            Payment Requests
                        </h5>

                        <small class="text-success">
                            <i class="fa fa-info-circle"></i>
                            Klik <b>Detail</b> pada pengajuan paling atas <b>(NEW)</b>. Setelah halaman detail terbuka, scroll ke bawah untuk melakukan <b>Approve</b>.
                        </small>
                       <div class="alert alert-warning mb-3">


                    </div>

                    <div class="card-body">
                        <div class="draft-wrapper">
                            <div class="draft-list">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Draft No</th>
                                            <th>Request Date</th>
                                            <th>Need Date</th>
                                            <th>Total baris</th>
                                            <th>Grand Total</th>
                                            <th>Status</th>
                                            <th>Pending Sign</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draftRequests as $draft)
                                            <tr class="draft-row" data-id="{{ $draft['id'] }}">
                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td>
                                                    {{ $draft['request_no'] }}
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
                                                    {{ number_format(
                                    $draft['grand_total'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                                                </td>
                                                <td>
    <span class="badge bg-success text-dark">
      Pending  {{ $draft['pending_sign'] }}
    </span>
</td>
                                                <td>
                                                    {{ $draft['status'] }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary btn-detail-draft"
                                                        data-id="{{ $draft['id'] }}"
                                                        data-request="{{ $draft['request_no'] }}">
                                                        Detail
                                                    </button>
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
                            <div class="draft-detail" id="draftDetailArea" >

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

    .pr-th {
        border: 1px solid black;
        padding: 4px;
        text-align: center;
        font-size: 11px;
    }

    .pr-td {
        border: 1px solid black;
        padding: 3px;
        font-size: 11px;
    }

    .draft-wrapper {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
    }

    .draft-list {
        min-width: 100%;
    }

    .draft-detail {
        min-width: 100%;
        padding-left: 20px;
    }

    .draft-row.active-row {
        background: rgba(13, 110, 253, .15) !important;
    }

    .draft-row.active-row td {
        font-weight: bold;
    }

</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                        .html('💾 Save Draft Request');
                }
            });
        }
    );

</script>
@endsection
