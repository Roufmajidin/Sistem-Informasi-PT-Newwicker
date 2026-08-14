@extends('master.master')

@section('content')

    <style>
        .upah-page {
            padding: 20px;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .upah-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;
        }


        .upah-title {
            margin: 0;

            font-size: 24px;

            font-weight: 600;
        }


        .upah-subtitle {
            color: #6c757d;

            font-size: 13px;
        }


        /* =====================================================
           SEARCH ARTICLE
        ===================================================== */

        .article-search-wrapper {
            position: relative;
        }


        .article-search-result {
            position: absolute;

            left: 0;

            right: 0;

            top: calc(100% + 3px);

            z-index: 99999;

            display: none;

            background: #fff;

            border: 1px solid #ced4da;

            border-radius: 6px;

            box-shadow:
                0 6px 18px rgba(0, 0, 0, .15);

            max-height: 280px;

            overflow-y: auto;
        }


        .article-search-result.show {
            display: block;
        }


        .article-result-item {
            padding: 10px 12px;

            cursor: pointer;

            border-bottom: 1px solid #eee;
        }


        .article-result-item:last-child {
            border-bottom: 0;
        }


        .article-result-item:hover {
            background: #f5f7fa;
        }


        .article-result-code {
            font-weight: 600;

            font-size: 13px;
        }


        .article-result-description {
            margin-top: 3px;

            color: #6c757d;

            font-size: 11px;
        }


        .article-result-type {
            margin-top: 3px;

            font-size: 11px;

            color: #495057;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .upah-table-wrapper {
            overflow-x: auto;
        }


        .upah-table {
            width: 100%;

            border-collapse: separate;

            border-spacing: 0;

            min-width: 1000px;
        }


        .upah-table thead th {

            position: sticky;

            top: 0;

            z-index: 10;

            background: #17212b;

            color: #fff;

            padding: 10px;

            font-size: 12px;

            white-space: nowrap;
        }


        .upah-table tbody td {

            padding: 9px 10px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

            vertical-align: middle;
        }


        /* =====================================================
           MODAL
        ===================================================== */

        #modalInsertUpah .modal-dialog {

            max-width: 720px;

            margin: 1.5rem auto;
        }


        #modalInsertUpah .modal-content {

            border: 0;

            border-radius: 10px;
        }


        #modalInsertUpah .modal-header {

            padding: 15px 20px;

            border-bottom: 1px solid #dee2e6;
        }


        #modalInsertUpah .modal-body {

            padding: 20px;
        }


        #modalInsertUpah .modal-footer {

            padding: 12px 20px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .upah-form-label {

            font-size: 13px;

            font-weight: 500;

            margin-bottom: 5px;
        }


        .upah-form-group {

            margin-bottom: 14px;
        }


        .required {

            color: #dc3545;
        }


        .total-input {

            font-weight: 600;

            background: #f8f9fa;
        }


        /* =====================================================
           ARTICLE NOT FOUND
        ===================================================== */

        .article-not-found {

            display: none;

            margin-top: 5px;

            font-size: 11px;

            color: #856404;

            background: #fff8e1;

            border: 1px solid #ffeeba;

            border-radius: 4px;

            padding: 7px 9px;
        }


        .article-not-found.show {

            display: block;
        }


        /* MASS INPUT */
        #modalInsertUpah.mass-mode .modal-dialog {
            max-width: 1250px;
            width: calc(100% - 30px);
        }

        .mass-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .mass-table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }

        .mass-upah-table {
            width: 100%;
            min-width: 1450px;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        .mass-upah-table th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #17212b;
            color: #fff;
            padding: 8px;
            font-size: 11px;
            white-space: nowrap;
        }

        .mass-upah-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            background: #fff;
        }

        .mass-upah-table .form-control {
            min-width: 105px;
            height: 34px;
            font-size: 12px;
        }

        .mass-upah-table textarea.form-control {
            min-width: 210px;
            height: 34px;
            resize: vertical;
        }

        .mass-upah-table .mass-article {
            min-width: 150px;
        }

        .mass-upah-table .mass-pekerjaan {
            min-width: 150px;
        }

        .mass-upah-table .mass-person {
            min-width: 140px;
        }

        .mass-upah-table .mass-no-po,
        .mass-upah-table .mass-no-spk {
            min-width: 120px;
        }

        .mass-total-input {
            background: #f8f9fa;
            font-weight: 600;
        }

        .mass-search-wrapper {
            position: relative;
        }

        .mass-search-result {
            position: absolute;
            left: 0;
            top: calc(100% + 2px);
            width: 300px;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .15);
            z-index: 99999;
            display: none;
        }

        .mass-search-result.show {
            display: block;
        }

        .mass-search-item {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            font-size: 12px;
        }

        .mass-search-item:hover {
            background: #f5f7fa;
        }

        .mass-search-code {
            font-weight: 600;
        }

        .mass-search-desc {
            color: #6c757d;
            font-size: 11px;
            margin-top: 2px;
        }

        .mass-required {
            color: #dc3545;
        }
    </style>


    <div class="container-fluid upah-page">


        {{-- =====================================================
         HEADER
    ===================================================== --}}

        <div class="upah-header">

        @section('btn')
            <div>

                <h4 class="upah-title">
                    Transaksi Upah
                </h4>

                <div class="upah-subtitle">
                    Input transaksi upah borongan
                </div>

            </div>
        @endsection


        

    </div>


    {{-- =====================================================
         ALERT
    ===================================================== --}}
    <div class="card-body py-2">

        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">

            {{-- LEFT TOOLS --}}
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">

                {{-- DATE FROM --}}
                <div class="d-flex align-items-center" style="gap: 5px;">

                    <small class="text-muted">
                        Dari
                    </small>

                    <input type="date" id="filterDateFrom" class="form-control form-control-sm"
                        style="width: 145px;">

                </div>


                <div class="d-flex align-items-center" style="gap: 5px;">

                    <small class="text-muted">
                        Sampai
                    </small>

                    <input type="date" id="filterDateTo" class="form-control form-control-sm" style="width: 145px;">

                </div>


                <button type="button" class="btn btn-sm btn-light border" id="btnResetDate">

                    <i class="fas fa-redo mr-1"></i>
                    Reset

                </button>


                {{-- SEARCH ALL TABLE --}}
                <div style="position: relative;">

                    <input type="text" id="searchUpahTable" class="form-control form-control-sm"
    style="width: 240px; padding-right: 30px;" placeholder="Cari data...">

                    <button type="button" id="clearSearchUpah" class="btn btn-link btn-sm"
                        style="
                                position:absolute;
                                right:2px;
                                top:1px;
                                display:none;
                            ">

                        <i class="fas fa-times"></i>

                    </button>

                </div>

            </div>


            {{-- RIGHT TOOLS --}}
            <div class="d-flex align-items-center" style="gap: 8px;">

                <button type="button" class="btn btn-sm btn-primary" id="btnAddUpahTransaksi">

                    <i class="fas fa-plus mr-1"></i>
                    Tambah

                </button>


                <button type="button" class="btn btn-sm btn-success" id="btnExportUpah">

                    <i class="fas fa-file-excel mr-1"></i>
                    Export

                </button>

            </div>

        </div>
        <div id="upahAlert" class="alert d-none">
        </div>


        {{-- =====================================================
         TABLE
    ===================================================== --}}

        <div class="card">

            <div class="card-body p-0">

                <div class="upah-table-wrapper mt-4">

                    <table class="upah-table" id="upahTable">
                        <thead>

                            <tr>

                                <th width="50">
                                    NO
                                </th>

                                <th>
                                    ARTICLE
                                </th>

                                <th>
                                    DESCRIPTION
                                </th>

                                <th>
                                    TANGGAL
                                </th>

                                <th>
                                    PEKERJAAN
                                </th>

                                <th>
                                    PERSON
                                </th>

                                <th>
                                    QTY
                                </th>

                                <th>
                                    HARGA
                                </th>

                                <th>
                                    TOTAL
                                </th>

                                <th>
                                    NO PO
                                </th>

                                <th>
                                    NO SPK
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($data as $index => $item)
                             <tr class="upah-data-row">

                                    <td>
                                        {{ $data->firstItem() + $index }}
                                    </td>

                                    <td>
                                        {{ $item->article }}
                                    </td>

                                    <td>
                                        {{ $item->description }}
                                    </td>

                                    <td>
                                        {{ optional($item->tanggal)->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        {{ $item->pekerjaan }}
                                    </td>

                                    <td>
                                        {{ $item->person }}
                                    </td>

                                    <td>
                                        {{ $item->qty }}
                                    </td>

                                    <td>
                                        Rp
                                        {{ number_format($item->harga, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp
                                        {{ number_format($item->total, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        {{ $item->no_po }}
                                    </td>

                                    <td>
                                        {{ $item->no_spk }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="11" class="text-center text-muted py-4">

                                        Belum ada transaksi upah.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <div class="mt-3">

            {{ $data->links() }}

        </div>

    </div>



    {{-- =====================================================
     MODAL INSERT
===================================================== --}}

    <div class="modal fade" id="modalInsertUpah" tabindex="-1" role="dialog">

        <div class="modal-dialog" role="document">

            <div class="modal-content">


                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="fas fa-money-bill-wave mr-1"></i>

                        Tambah Transaksi Upah

                    </h5>

                    <div class="ml-auto mr-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleMassUpah">
                            <i class="fas fa-layer-group mr-1"></i>
                            Add Mass
                        </button>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">

                        <span>&times;</span>

                    </button>

                </div>


                <form id="formInsertUpah">


                    <div class="modal-body">


                        <div id="formUpahError" class="alert alert-danger d-none">
                        </div>


                        {{-- ARTICLE --}}

                        <div class="upah-form-group">

                            <label class="upah-form-label">

                                Article Code

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <div class="article-search-wrapper">

                                <input type="text" id="insert_article" name="article" class="form-control"
                                    autocomplete="off" placeholder="Cari article code...">


                                <div id="articleSearchResult" class="article-search-result">
                                </div>

                            </div>


                            <div id="articleNotFound" class="article-not-found">

                                Article tidak ditemukan pada tabel
                                upah borongan. Silahkan isi article
                                code secara manual.

                            </div>

                        </div>


                        {{-- DESCRIPTION --}}

                        <div class="upah-form-group">

                            <label class="upah-form-label">
                                Description
                            </label>

                            <textarea id="insert_description" name="description" class="form-control" rows="2" placeholder="Description"></textarea>

                        </div>


                        <div class="row">


                            {{-- TANGGAL --}}

                            <div class="col-md-6">

                                <div class="upah-form-group">

                                    <label class="upah-form-label">

                                        Tanggal

                                        <span class="required">
                                            *
                                        </span>

                                    </label>

                                    <input type="date" id="insert_tanggal" name="tanggal" class="form-control"
                                        value="{{ date('Y-m-d') }}">

                                </div>

                            </div>


                            {{-- PERSON --}}

                            <div class="col-md-6">

                                <div class="upah-form-group">

                                    <label class="upah-form-label">
                                        Person
                                    </label>

                                    <input type="text" id="insert_person" name="person" class="form-control"
                                        placeholder="Nama person">

                                </div>

                            </div>


                        </div>


                        {{-- PEKERJAAN --}}

                        <div class="upah-form-group">

                            <label class="upah-form-label">

                                Pekerjaan

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <div class="article-search-wrapper">

                                <input type="text" id="insert_pekerjaan" name="pekerjaan" class="form-control"
                                    autocomplete="off" placeholder="Pilih atau ketik jenis pekerjaan...">

                                <div id="pekerjaanSearchResult" class="article-search-result">
                                </div>

                            </div>

                            <div class="row">


                                {{-- QTY --}}

                                <div class="col-md-4">

                                    <div class="upah-form-group">

                                        <label class="upah-form-label">

                                            Qty

                                            <span class="required">
                                                *
                                            </span>

                                        </label>

                                        <input type="number" id="insert_qty" name="qty" class="form-control"
                                            value="1" min="0" step="0.01">

                                    </div>

                                </div>


                                {{-- HARGA --}}

                                <div class="col-md-4">

                                    <div class="upah-form-group">

                                        <label class="upah-form-label">

                                            Harga

                                            <span class="required">
                                                *
                                            </span>

                                        </label>

                                        <input type="number" id="insert_harga" name="harga" class="form-control"
                                            value="0" min="0" step="0.01">

                                    </div>

                                </div>


                                {{-- TOTAL --}}

                                <div class="col-md-4">

                                    <div class="upah-form-group">

                                        <label class="upah-form-label">
                                            Total
                                        </label>

                                        <input type="text" id="insert_total" class="form-control total-input"
                                            value="0" readonly>

                                    </div>

                                </div>


                            </div>


                            <div class="row">


                                {{-- NO PO --}}

                                <div class="col-md-6">

                                    <div class="upah-form-group">

                                        <label class="upah-form-label">
                                            No PO
                                        </label>

                                        <input type="text" id="insert_no_po" name="no_po" class="form-control"
                                            placeholder="No PO">

                                    </div>

                                </div>


                                {{-- NO SPK --}}

                                <div class="col-md-6">

                                    <div class="upah-form-group">

                                        <label class="upah-form-label">

                                            No SPK

                                            <small class="text-muted">
                                                (optional)
                                            </small>

                                        </label>

                                        <input type="text" id="insert_no_spk" name="no_spk" class="form-control"
                                            placeholder="No SPK">

                                    </div>

                                </div>


                            </div>


                        </div>



                        <div class="modal-body d-none" id="massUpahBody">

                            <div class="mass-toolbar">

                                <div>
                                    <strong>Mass Input Upah</strong>
                                    <div class="text-muted small">
                                        Tambahkan beberapa transaksi sekaligus.
                                        Article dan pekerjaan dicari dengan AJAX.
                                    </div>
                                </div>

                                <button type="button" class="btn btn-success btn-sm" id="btnAddMassRow">
                                    <i class="fas fa-plus mr-1"></i>
                                    Add Row
                                </button>

                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    id="btnBackNormalUpah">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Normal
                                </button>

                            </div>

                            <div id="massUpahError" class="alert alert-danger d-none">
                            </div>

                            <div class="mass-table-wrapper">

                                <table class="mass-upah-table">

                                    <thead>
                                        <tr>
                                            <th width="35">#</th>
                                            <th>Article Code <span class="mass-required">*</span></th>
                                            <th>Description</th>
                                            <th>Tanggal <span class="mass-required">*</span></th>
                                            <th>Pekerjaan <span class="mass-required">*</span></th>
                                            <th>Person</th>
                                            <th>Qty <span class="mass-required">*</span></th>
                                            <th>Harga <span class="mass-required">*</span></th>
                                            <th>Total</th>
                                            <th>No PO</th>
                                            <th>No SPK</th>
                                            <th width="45"></th>
                                        </tr>
                                    </thead>

                                    <tbody id="massUpahBodyRows"></tbody>

                                </table>

                            </div>

                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-dismiss="modal">

                                Batal

                            </button>


                            <button type="button" class="btn btn-primary d-none" id="btnSaveMassUpah">
                                <i class="fas fa-save mr-1"></i>
                                Simpan Semua
                            </button>

                            <button type="submit" class="btn btn-primary" id="btnSaveUpahTransaksi">

                                <i class="fas fa-save mr-1"></i>

                                Simpan

                            </button>

                        </div>


                </form>

            </div>

        </div>

    </div>

    @include('pages.upah.upah-script')

@endsection
