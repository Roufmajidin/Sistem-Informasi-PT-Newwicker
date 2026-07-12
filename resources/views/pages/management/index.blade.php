@extends('master.master')

@section('title','Production Monitoring')

@section('content')

<div class="container-fluid mt-4">

<style>

    body{
        background:#f5f7fb;
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

</style>

<div class="container-fluid py-3">

    {{-- FILTER --}}
    <div class="mn-filter mb-4">

        <form method="GET"
              action="{{ route('produksi.mn') }}">

            <div class="d-flex gap-2 flex-wrap">

                {{-- SEARCH PO --}}
                <input type="text"
                       name="search_po"
                       value="{{ request('search_po') }}"
                       placeholder="Cari PO"
                       class="form-control"
                       style="max-width:260px;">

                {{-- FILTER BATCH --}}
               <select name="tanggal"
        class="form-control"
        style="max-width:220px;">

    <option value="">
        Semua Tanggal
    </option>

    @foreach($dates as $date)

        <option value="{{ $date }}"
            {{ request('tanggal') == $date ? 'selected' : '' }}>

            {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}

        </option>

    @endforeach

</select>

                <button class="btn btn-primary">

                    Filter

                </button>

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

                            Semua Batch

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

                    <th colspan="4" class="text-center">
                        {{ $categoryLabel }}
                    </th>

                @endforeach

            </tr>

            {{-- HEADER STATUS --}}
            <tr>

                @foreach($categories as $categoryKey => $categoryLabel)

                    @foreach($statuses as $statusKey => $status)

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
                    <td>

                        <a href="#"
                           class="item-link"
                           data-bs-toggle="modal"
                           data-bs-target="#spkModal{{ $poIndex }}{{ $itemIndex }}">

                            {{ $item['item_name'] }}

                        </a>

                    </td>

                    {{-- DYNAMIC CATEGORY + STATUS --}}
                    @foreach($categories as $categoryKey => $categoryLabel)

                        @foreach($statuses as $statusKey => $status)

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
                                    Rp {{ number_format($spk['harga']) }}

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

    <a href="{{ route('qc.report', $spk['inspect_schedule_id']) }}"
       target="_blank"
       class="fw-bold text-primary text-decoration-none">

        Lihat Laporan

    </a>

@else

    <span class="text-muted">

        Tidak ada laporan

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

{{ print_r($spk, true) }}

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

</div>

@endsection
