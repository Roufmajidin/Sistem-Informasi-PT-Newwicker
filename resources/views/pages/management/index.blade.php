@extends('master.master')

@section('title','Production Monitoring')

@section('content')

<div class="container-fluid mt-4">

<style>

    body{
        background:#f5f7fb;
    }
    .table-responsive{
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    }

    .mn-table{
        min-width:1700px;
    }

    .mn-table th,
    .mn-table td{
        white-space:nowrap;
    }
    .product-image{

    width:65px;
    height:65px;

}
.mn-table thead th{

    position:sticky;
    top:0;
    z-index:5;

}
.item-name{
    display:inline-block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    max-width:220px;
}

/* Tablet */
@media (max-width:992px){

    .item-name{
        max-width:140px;
        font-size:13px;
    }

}

/* Mobile */
@media (max-width:768px){

    .item-name{
        max-width:90px;
        font-size:12px;
    }

}

/* iPhone Portrait */
@media (max-width:576px){

    .item-name{
        max-width:70px;
        font-size:11px;
    }

}
    .mn-card{
        border:none;
        /* border-radius:20px; */
        overflow:hidden;
        /* box-shadow:0 6px 25px rgba(0,0,0,0.06); */
        background:#fff;
    }

    .mn-header{
        /* background:linear-gradient(135deg,#243b55,#141e30); */
        /* color:white; */
        padding:18px 24px;
    }

    .mn-header h5{
        margin:0;
        font-weight:700;
        letter-spacing:.5px;
    }

    .mn-filter{
        background:white;
        border-radius:18px;
        padding:18px;
        box-shadow:0 4px 20px rgba(0,0,0,0.05);
    }

    .mn-filter .form-control{
        border-radius:12px;
        border:1px solid #dfe6e9;
        height:44px;
    }

    .mn-filter .btn{
        border-radius:12px;
        padding:0 26px;
        font-weight:600;
    }

    .mn-table{
        margin-bottom:0;
    }

    .mn-table thead tr:first-child{
        background:#2c3e50;
        color:white;
    }

    .mn-table thead tr:nth-child(2){
        background:#ecf0f1;
        color:#2c3e50;
    }

    .mn-table th{
        font-size:12px;
        font-weight:700;
        text-transform:uppercase;
        border:none !important;
        vertical-align:middle !important;
        padding:14px 10px;
    }

    .mn-table td{
        vertical-align:middle !important;
        border-color:#f1f2f6 !important;
        font-size:14px;
        padding:14px 10px;
    }

    .mn-table tbody tr{
        transition:.2s;
    }

    .mn-table tbody tr:hover{
        background:#f8fafc;
    }

    .product-image{
        width:78px;
        height:78px;
        object-fit:cover;
        border-radius:16px;
        border:1px solid #e5e7eb;
        background:white;
        padding:4px;
    }

    .item-link{
        font-weight:700;
        color:#2563eb;
        text-decoration:none;
        transition:.2s;
    }

    .item-link:hover{
        color:#1d4ed8;
        text-decoration:underline;
    }

    .qty-badge{
        background:#eef2ff;
        /* color:#4338ca; */
        /* font-weight:700; */
        border-radius:999px;
        padding:6px 14px;
        display:inline-block;
        min-width:55px;
        text-align:center;
    }

    .pass-box{
        /* color:#16a34a; */
        /* font-weight:700; */
        font-size:15px;
    }

    .reject-box{
        /* color:#dc2626; */
        /* font-weight:700; */
        font-size:15px;
    }

    .mn-empty{
        border-radius:16px;
        padding:40px;
        text-align:center;
        background:white;
        box-shadow:0 2px 10px rgba(0,0,0,0.05);
    }
.mn-filter{
    background:#fff;
    border-radius:18px;
    padding:20px 24px;
    border:1px solid #edf1f5;
    box-shadow:0 3px 15px rgba(0,0,0,.04);
}
.item-name{
    display:inline-block;
    max-width:250px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.mn-filter label{
    font-size:13px;
}

.mn-filter .form-control,
.mn-filter .form-select{

    height:46px;
    border-radius:12px;

}

.mn-filter .input-group-text{

    border-radius:12px 0 0 12px;

}

.mn-filter .form-control{

    border-radius:0 12px 12px 0;

}

.mn-filter .btn{

    height:46px;
    border-radius:12px;
}
</style>

<div class="container-fluid py-3">

    <div class="mn-filter mb-4">

    <form method="GET" action="{{ route('produksi.mn') }}">

        <div class="row align-items-end g-3">

            {{-- SEARCH --}}
            <div class="col-lg-5">

                <label class="form-label fw-semibold text-muted mb-2">
                    Cari Purchase Order
                </label>

                <div class="input-group">

                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-secondary"></i>
                    </span>

                    <input type="text"
                           name="search_po"
                           value="{{ request('search_po') }}"
                           class="form-control border-start-0"
                           placeholder="Contoh : NW26-39">

                </div>

            </div>

            {{-- DATE --}}
            {{-- <div class="col-lg-3">

                <label class="form-label fw-semibold text-muted mb-2">
                    Tanggal Inspect
                </label>

                <select name="tanggal" class="form-select">

                    <option value="">
                        Semua Tanggal
                    </option>

                    @foreach($dates as $date)

                        <option value="{{ $date }}"
                            {{ request('tanggal') == $date ? 'selected' : '' }}>

                            {{ \Carbon\Carbon::parse($date)->format('d M Y') }}

                        </option>

                    @endforeach

                </select>

            </div> --}}

            {{-- BUTTON --}}
            <div class="col-lg-4">

                <div class="d-flex gap-2">

                    <button class="btn btn-primary px-4">

                        <i class="fas fa-search me-2"></i>

                        Filter

                    </button>

                    <a href="{{ route('produksi.mn') }}"
                       class="btn btn-light border px-4">

                        <i class="fas fa-rotate-left me-2"></i>

                        Reset

                    </a>

                </div>

            </div>

        </div>

    </form>

</div>

    {{-- DATA --}}
    @forelse($datas as $poIndex => $po)

        <div class="mn-card mb-5">

            {{-- HEADER --}}
            <div class="mn-header d-flex justify-content-between align-items-center spk-header">

                <div>

                    <h5>

                        PO : {{ $po['po_number'] }}

                    </h5>

                </div>

                <div>

                    @if(request('batch'))

                        <span class="badge bg-warning text-dark px-3 py-2">

                            Batch {{ request('batch') }}

                        </span>

                    @else

                        <span class="badge bg-success px-3 py-2">

                            {{-- Semua Batch --}}

                        </span>

                    @endif

                </div>

            </div>
@php

   $categories = [
        'rangka'   => 'Rangka',
        'anyam'    => 'Anyam',
        'unfinish' => 'Unfinish',
        'accessories' => 'Accessories',
        'decor' => 'Decor',
        'ikat' => 'Ikat',
        'final'    => 'Final',
        'box'      => 'Packaging',
    ];

    $statuses = [

        'in' => [
            'label' => 'In',
            'class' => 'text-primary fw-bold'
        ],

        'pass' => [
            'label' => 'Pass',
            'class' => 'pass-box'
        ],

        'reject' => [
            'label' => 'Reject',
            'class' => 'reject-box'
        ],

        'out' => [
            'label' => 'Out',
            'class' => 'text-dark fw-bold'
        ],
    ];

@endphp

{{-- TABLE --}}
<div class="table-responsive">

    <table class="table mn-table align-middle">

        <thead>

            {{-- HEADER CATEGORY --}}
            <tr>

                <th rowspan="2" class="text-center">
                    Gambar
                </th>

                <th rowspan="2" class="text-center">
                    Qty
                </th>

                <th rowspan="2" class="text-center">
                    Item
                </th>

                @foreach($categories as $categoryKey => $categoryLabel)

                   <th colspan="3" class="text-center">
                        {{ $categoryLabel }}
                    </th>

                @endforeach

            </tr>

            {{-- HEADER STATUS --}}
            <tr>

                @foreach($categories as $categoryKey => $categoryLabel)

                   @foreach($statuses as $statusKey => $status)

                    @continue($statusKey == 'out')

                    <th class="text-center {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </th>

                @endforeach

                @endforeach

            </tr>

        </thead>

        <tbody>

            @foreach($po['items'] as $itemIndex => $item)

                <tr>

                    {{-- IMAGE --}}
                    <td class="text-center">

                        @if(!empty($item['item_image']))

                            <img src="{{ $item['item_image'] }}"
                                 class="product-image">

                        @else

                            -

                        @endif

                    </td>

                    {{-- QTY --}}
                    <td class="text-center">

                        <span class="qty-badge">
                            {{ $item['qty'] }}
                        </span>

                    </td>

                    {{-- ITEM --}}
                 <td style="max-width:250px;">

    <a href="#"
       class="item-link text-truncate d-inline-block"
       style="max-width:250px;"
       title="{{ $item['item_name'] }}"
       data-bs-toggle="modal"
       data-bs-target="#spkModal{{ $poIndex }}{{ $itemIndex }}">

        {{ $item['item_name'] }}

    </a>

</td>

                    {{-- DYNAMIC CATEGORY + STATUS --}}
                    @foreach($categories as $categoryKey => $categoryLabel)

                     @foreach($statuses as $statusKey => $status)

                        @continue($statusKey == 'out')

                        @php
                            $field = $categoryKey . '_' . $statusKey;
                        @endphp

                        <td class="text-center">

                            <span class="{{ $status['class'] }}">
                                {{ $item[$field] ?? 0 }}
                            </span>

                        </td>

                    @endforeach

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>

        </div>

        {{-- ========================================================= --}}
        {{-- MODAL LUAR TABLE --}}
        {{-- ========================================================= --}}

        @foreach($po['items'] as $itemIndex => $item)

            <div class="modal fade"
                 id="spkModal{{ $poIndex }}{{ $itemIndex }}"
                 tabindex="-1">

                <div class="modal-dialog modal-lg modal-dialog-centered">

                    <div class="modal-content border-0 shadow">

                        {{-- HEADER --}}
                        <div class="modal-header bg-dark text-white">

                            <h5 class="modal-title">

                                SPK ITEM

                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal">
                            </button>

                        </div>

                        {{-- BODY --}}
                        <div class="modal-body">

                            {{-- ITEM INFO --}}
                            <div class="d-flex gap-3 mb-4">

                                @if($item['item_image'])

                                    <img src="{{ $item['item_image'] }}"
                                         class="product-image">

                                @endif

                                <div>

                                    <div class="fw-bold fs-5">

                                        {{ $item['item_name'] }}

                                    </div>

                                    <div class="text-muted">

                                        Qty :
                                        {{ $item['qty'] }}

                                    </div>

                                </div>

                            </div>

                            {{-- LIST SPK --}}
                       @forelse($item['spks'] as $spk)



        <div class="card border-0 shadow-sm mb-3">

            <div class="card-body">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <span class="badge bg-primary px-3 py-2">

                            {{ strtoupper($spk['kategori']) }}

                        </span>

                    </div>

                    <div>

                        <span class="badge bg-success px-3 py-2">

                            {{ strtoupper($spk['status']) }}
{{ $spk['id'] }}
                        </span>

                    </div>

                </div>

                {{-- ROW --}}
                <div class="row">

                    {{-- LEFT --}}
                    <div class="col-md-8">

                        <table class="table table-sm mb-0">

                            <tr>

                                <td width="140">

                                    Supplier

                                </td>

                                <td>

                                    :
                                    {{ $spk['supplier'] }}

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    No SPK

                                </td>

                                <td>

                                    :

                                    <a href="{{ url('spk/edit/' . $spk['id']) }}"
                                       class="fw-bold text-primary text-decoration-underline">

                                        {{ $spk['no_spk'] }}

                                    </a>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    Qty

                                </td>

                                <td>

                                    :
                                    {{ $spk['qty'] }}

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    Harga

                                </td>

                               <td>
                                    :
                                    <span class="price-container"
                                        data-price="{{ number_format($spk['harga']) }}">
                                        <a href="#"
                                        class="show-price text-primary text-decoration-underline">
                                            Lihat Harga? Tap disini
                                        </a>
                                    </span>
                                </td>

                            </tr>

                        </table>

                    </div>

                 {{-- RIGHT --}}
<div class="col-md-4">

    @php

        $kategoriSpk = strtolower(
            $spk['kategori']
        );

        $hideQcResult =

            str_contains(
                $kategoriSpk,
                'cushion'
            )

            ||

            str_contains(
                $kategoriSpk,
                'box'
            );

    @endphp

    @unless($hideQcResult)

        <div class="border rounded-4 p-3 h-100 bg-light">
<div class="row">

            <div class="fw-bold mb-3">

                QC RESULT

            </div>
             <div class="fw-bold mb-3 ml-4">
            @if(!empty($spk['inspect_schedule_id']))

            <a href="{{ url('qc/laporan-qc?' . http_build_query([
                'detail_po_id' => $spk['detail_po_id'],
                'kategori'     => $spk['kategori'],
            ])) }}"
            target="_blank"
            class="fw-bold text-primary text-decoration-none">

                Lihat Laporan

            </a>

            @else

                <span class="text-muted">

                    Tidak/belum ada inspeksin

                </span>

            @endif
                        </div>
            </div>


            {{-- PASSED --}}
            <div class="d-flex justify-content-between mb-2">

                <span>

                    Passed

                </span>

                <span class="fw-bold text-success">
<pre>

{{-- {{ print_r($spk, true) }} --}}

</pre>
                    {{ $spk['passed'] }}

                </span>

            </div>

            {{-- REJECT --}}
            <div class="d-flex justify-content-between">

                <span>

                    Rejected

                </span>

                <span class="fw-bold text-danger">

                    {{ $spk['rejected'] }}

                </span>

            </div>

        </div>

    @endunless

</div>
                </div>

            </div>

        </div>


@empty

    <div class="alert alert-warning mb-0">

        Tidak ada SPK untuk item ini

    </div>

@endforelse
                        </div>

                    </div>

                </div>

            </div>

        @endforeach

    @empty

        <div class="mn-empty">

            <h5 class="mb-2">

                Data Tidak Ditemukan

            </h5>

            <div class="text-muted">

                Coba cari PO atau batch lain

            </div>

        </div>

    @endforelse

    <div class="modal fade" id="pricePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Verifikasi Password
                    </h5>
                </div>

                <div class="modal-body">

                    <input
                        type="password"
                        id="pricePassword"
                        class="form-control"
                        placeholder="Masukkan Password">

                    <div
                        id="priceError"
                        class="text-danger mt-2"
                        style="display:none;">

                        Password salah

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        class="btn btn-primary"
                        id="btnCheckPricePassword">

                        Lihat Harga

                    </button>

                </div>

            </div>
        </div>
    </div>
</div>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

let currentPriceContainer = null;

$(document).on('click','.show-price',function(e){

    e.preventDefault();

    currentPriceContainer = $(this).closest('.price-container');

    $('#pricePassword').val('');
    $('#priceError').hide();

    $('#pricePasswordModal').modal('show');

});

$('#btnCheckPricePassword').click(function(){

    if($('#pricePassword').val() !== 'Nwidn@2026'){

        $('#priceError').show();
        return;

    }

    let harga = currentPriceContainer.data('price');

    currentPriceContainer.html(
        '<strong>Rp ' + harga + '</strong>'
    );

    $('#pricePasswordModal').modal('hide');

});

</script>

@endsection
