@extends('master.master')

@section('content')



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
                        style="width: 240px; padding-right: 30px;" placeholder="Cari data..."
                        value="{{ $search ?? '' }}">
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

                <button type="button" class="btn btn-sm btn-info" id="btnOverviewPO" data-toggle="modal" data-target="#modalOverviewPO" style="white-space:nowrap;">
                    <i class="fas fa-chart-bar mr-1"></i> Overview
                </button>

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

        <div class="card mt-2">

            <div class="card-body p-0">

                <div class="upah-table-wrapper mt-4">

                    <table class="upah-table" id="upahTable">
                        <thead>

                            <tr>

                                <th width="50">
                                    NO
                                </th>
                                <th style="width:55px;text-align:center;">
                                    AKSI
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
                                    <td style="text-align:center;">

                                        <button type="button" class="btn btn-sm btn-primary btn-edit-upah mr-1"
                                            data-id="{{ $item->id }}" data-article="{{ e($item->article) }}"
                                            data-description="{{ e($item->description) }}"
                                            data-tanggal="{{ optional($item->tanggal)->format('Y-m-d') }}"
                                            data-pekerjaan="{{ e($item->pekerjaan) }}"
                                            data-person="{{ e($item->person) }}" data-qty="{{ $item->qty }}"
                                            data-harga="{{ $item->harga }}" data-total="{{ $item->total }}"
                                            data-no-po="{{ e($item->no_po) }}" data-no-spk="{{ e($item->no_spk) }}"
                                            title="Edit transaksi"
                                            style="
            width:28px;
            height:28px;
            min-height:28px;
            padding:0;
            line-height:28px;
        ">
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger btn-delete-upah"
                                            data-id="{{ $item->id }}" data-article="{{ $item->article }}"
                                            data-description="{{ $item->description }}" title="Hapus transaksi"
                                            style="
            width:28px;
            height:28px;
            min-height:28px;
            padding:0;
            line-height:28px;
        ">
                                            <i class="fa fa-remove"></i>
                                        </button>

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
                                        {{ number_format($item->qty, 0, ',', '.') }}
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

                                    <td colspan="12" class="text-center text-muted py-4">

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
                            Input Massal
                        </button>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">

                        <span>&times;</span>

                    </button>

                </div>


                <form id="formInsertUpah">

                    {{-- ID transaksi yang sedang diedit.
                         Dipakai oleh realtime qty check untuk mengecualikan
                         transaksi ini dari total yang sudah terpakai. --}}
                    <input type="hidden" id="insert_upah_id" name="id" value="">


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



                        {{-- PEKERJAAN --}}

                        <div class="upah-form-group">

                            <label class="upah-form-label">
                                Pekerjaan
                                <span class="required">*</span>
                            </label>

                            {{--
                                Article yang sudah ada:
                                gunakan dropdown pekerjaan dari master UpahBorongan.
                            --}}
                            <select id="insert_pekerjaan" name="pekerjaan" class="form-control">
                                <option value="">Pilih pekerjaan...</option>
                            </select>

                            {{--
                                Article NOT YET IN DATABASE:
                                user dapat mengetik jenis pekerjaan baru secara manual.
                                Field ini hanya ditampilkan oleh upah-script saat article baru.
                            --}}
                            <input type="text" id="insert_pekerjaan_new" class="form-control d-none"
                                placeholder="Masukkan jenis pekerjaan baru..." autocomplete="off">

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
                                    <div id="insert_qty_limit_info" class="mt-2" style="display:none;">
                                        <div class="small text-muted">
                                            <span class="me-3">Qty PO: <strong id="insert_qty_po">0</strong></span>
                                            <span class="me-3">Sudah Upah: <strong
                                                    id="insert_qty_used">0</strong></span>
                                            <span>Sisa: <strong id="insert_qty_remaining">0</strong></span>
                                        </div>
                                        <div id="insert_qty_limit_message" class="small mt-1"></div>
                                    </div>


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

                                    <select id="insert_no_po" name="no_po" class="form-control">
                                        <option value="">Pilih No PO...</option>
                                    </select>

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

                            <div style="display:flex; align-items:center; gap:7px;">
                                <img src="{{ asset('storage/rouf.jpeg') }}" alt="Foto item"
                                    style="
            width:28px;
            height:28px;
            object-fit:cover;
            border-radius:50%;
        ">

                                <div>
                                    <strong>Mass Input Upah</strong>

                                    <div class="text-muted small">
                                        Tambahkan beberapa transaksi sekaligus.
                                        Article dan pekerjaan dicari dengan AJAX.
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="btnAddMassRow">
                                <i class="fas fa-plus mr-1"></i>
                                Add Row
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnBackNormalUpah">
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
                                        <th>No PO</th>
                                        <th>Person</th>
                                        <th>Qty <span class="mass-required">*</span></th>
                                        <th>Harga <span class="mass-required">*</span></th>
                                        <th>Total</th>
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



    {{-- OVERVIEW PO: tambahan independen; tidak mengubah form transaksi --}}
    <div class="modal fade" id="modalOverviewPO" tabindex="-1" role="dialog" aria-labelledby="overviewPOTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div><h5 class="modal-title" id="overviewPOTitle"><i class="fas fa-clipboard-list text-primary mr-2"></i>Overview No PO</h5>
                    <small class="text-muted">Detail barang dan Qty per pekerjaan, dikelompokkan berdasarkan No PO.</small></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="overview-search-line">
                        <div class="overview-search-wrap"><i class="fas fa-search"></i><input id="overviewPOSearch" type="search" class="form-control" placeholder="Cari No PO / Nama Barang / Article Code..." autocomplete="off"></div>
                        <button type="button" class="btn btn-light border" id="overviewPOReset"><i class="fas fa-redo mr-1"></i>Reset</button>
                    </div>
                    <div class="overview-notice"><i class="fas fa-info-circle mr-1"></i>Overview mengambil seluruh transaksi dari database (maksimal 500 baris per pencarian).</div>
                    <div id="overviewPOList" class="overview-po-list"><div class="text-muted text-center py-4">Memuat data...</div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>

    <style>
        
        /* =========================================================
   UPAH - COMPACT & COMFORTABLE ERP UI
   Chrome 100% friendly
   ========================================================= */

        .upah-page {
            padding: 8px !important;
            font-size: 14px;
            color: #172033;
        }


        /* =========================================================
   HEADER
   ========================================================= */

        .upah-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 56px;
            margin-bottom: 10px;
            padding: 10px 13px;
            background: #fff;
            border: 1px solid #e2e6eb;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(16, 24, 40, .035);
        }

        .upah-title {
            margin: 0;
            color: #172033;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 750;
            letter-spacing: -.02em;
        }

        .upah-subtitle {
            margin-top: 3px;
            color: #98a2b3;
            font-size: 11px;
        }


        /* =========================================================
   TOOLBAR
   ========================================================= */

        .upah-page>.card-body {
            padding: 8px 0 !important;
        }

        .upah-page {
            --upah-sticky-top: 0px;
        }

        .upah-page .card-body.py-2 {
            padding: 8px 0 !important;
        }

        .upah-page .card {
            border: 1px solid #e2e6eb;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(16, 24, 40, .035);
        }


        /* =========================================================
   FORM CONTROL
   ========================================================= */

        .upah-page .form-control,
        .upah-page .form-select {
            min-height: 34px;
            height: 34px;
            padding: 5px 10px;
            border: 1px solid #dfe3e8;
            border-radius: 6px;
            color: #344054;
            font-size: 13px;
            box-shadow: none !important;
        }

        .upah-page textarea.form-control {
            height: auto;
            min-height: 60px;
        }

        .upah-page .form-control:focus,
        .upah-page .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .07) !important;
        }


        /* =========================================================
   BUTTON
   ========================================================= */

        .upah-page .btn {
            min-height: 32px;
            height: 32px;
            padding: 0 11px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 650;
            line-height: 30px;
            box-shadow: none !important;
        }

        .upah-page .btn-sm {
            min-height: 32px;
            height: 32px;
            font-size: 12px;
        }

        .upah-page #btnAddUpahTransaksi,
        .upah-page #btnExportUpah {
            padding: 0 12px;
        }


        /* =========================================================
   SEARCH
   ========================================================= */

        .upah-page #searchUpahTable {
            height: 34px !important;
            min-height: 34px !important;
            width: 240px !important;
            padding-right: 32px !important;
            font-size: 13px !important;
        }

        .upah-page #clearSearchUpah {
            height: 29px !important;
            min-height: 29px !important;
            width: 29px;
            padding: 0 !important;
            top: 2px !important;
            line-height: 27px !important;
            color: #98a2b3;
        }


        /* =========================================================
   DATE INPUT
   ========================================================= */

        .upah-page #filterDateFrom,
        .upah-page #filterDateTo {
            height: 34px !important;
            min-height: 34px !important;
            width: 135px !important;
            font-size: 13px !important;
        }

        .upah-page .text-muted {
            color: #667085 !important;
        }


        /* =========================================================
   ALERT
   ========================================================= */

        .upah-page #upahAlert {
            margin: 8px 0 0;
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 12px;
        }


        /* =========================================================
   MAIN TABLE WRAPPER
   ========================================================= */

        .upah-table-wrapper {
            width: 100%;
            margin-top: 0 !important;
            overflow-x: auto;
            overflow-y: visible;
            border: 0;
            border-radius: 8px;
            background: #fff;
            scrollbar-width: thin;
        }

        .upah-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .upah-table-wrapper::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        .upah-table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd1d8;
            border-radius: 20px;
        }


        /* =========================================================
   MAIN TABLE
   ========================================================= */

        .upah-table {
            width: 100%;
            min-width: 1050px;
            margin: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;

            /* FIX:
       sebelumnya 4px */
            font-size: 13px;
        }


        /* =========================================================
   TABLE HEADER
   ========================================================= */

        .upah-table thead {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
        }

        .upah-table thead th {
            position: sticky !important;
            top: var(--upah-sticky-top) !important;
            z-index: 101 !important;

            height: 38px;
            padding: 8px 9px !important;

            background: #f8f9fb !important;
            color: #667085 !important;

            border: 0 !important;
            border-bottom: 1px solid #e4e7ec !important;

            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.2;

            letter-spacing: .01em;
            white-space: nowrap;
            vertical-align: middle !important;

            background-clip: padding-box !important;
            box-shadow: 0 1px 0 #e4e7ec !important;
        }

        .upah-table thead th+th {
            border-left: 1px solid #eef0f3 !important;
        }

        .upah-page .upah-table thead th::before {
            content: "";

            position: absolute;
            inset: 0;

            z-index: -1;

            background: #f8f9fb;
        }


        /* =========================================================
   TABLE BODY
   ========================================================= */

        .upah-table tbody tr {
            height: 42px;
            background: #fff;
            transition: background .12s ease;
        }

        .upah-table tbody tr:hover {
            background: #f8fbff !important;
        }

        .upah-table tbody td {
            height: 42px;
            padding: 7px 9px !important;

            color: #344054;
            background: transparent !important;

            border: 0 !important;
            border-bottom: 1px solid #edf0f2 !important;

            font-size: 13px !important;
            line-height: 1.3;

            vertical-align: middle !important;
        }

        .upah-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }


        /* =========================================================
   TABLE COLUMN STYLING
   ========================================================= */

        .upah-table tbody td:first-child {
            width: 42px;
            color: #667085;
            text-align: center;
            font-size: 12px !important;
        }

        .upah-table tbody td:nth-child(2) {
            color: #172033;
            font-weight: 650;
        }

        .upah-table tbody td:nth-child(3) {
            color: #667085;
        }

        .upah-table tbody td:nth-child(4),
        .upah-table tbody td:nth-child(5),
        .upah-table tbody td:nth-child(6),
        .upah-table tbody td:nth-child(10),
        .upah-table tbody td:nth-child(11) {
            white-space: nowrap;
        }

        .upah-table tbody td:nth-child(7),
        .upah-table tbody td:nth-child(8),
        .upah-table tbody td:nth-child(9) {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .upah-table tbody td:nth-child(8),
        .upah-table tbody td:nth-child(9) {
            color: #172033;
            font-weight: 650;
        }


        /* =========================================================
   ACTION BUTTON
   ========================================================= */

        .upah-table .btn {
            font-size: 12px !important;
        }


        /* =========================================================
   EMPTY STATE
   ========================================================= */

        .upah-table tbody tr td[colspan] {
            height: 110px !important;
            color: #98a2b3 !important;
            font-size: 13px !important;
            background: #fff !important;
        }


        /* =========================================================
   PAGINATION
   ========================================================= */

        .upah-page .pagination {
            margin: 8px 0 0 !important;
        }

        .upah-page .pagination .page-link {
            min-width: 31px;
            height: 31px;

            padding: 5px 9px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-color: #e2e6eb;
            color: #475467;

            font-size: 12px;

            box-shadow: none !important;
        }


        /* =========================================================
   ARTICLE SEARCH DROPDOWN
   ========================================================= */

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

            border: 1px solid #dfe3e8;
            border-radius: 7px;

            box-shadow: 0 10px 25px rgba(16, 24, 40, .12);

            max-height: 250px;
            overflow-y: auto;
        }

        .article-search-result.show {
            display: block;
        }

        .article-result-item {
            padding: 10px 11px;

            cursor: pointer;

            border-bottom: 1px solid #edf0f2;

            transition: background .12s ease;
        }

        .article-result-item:last-child {
            border-bottom: 0;
        }

        .article-result-item:hover {
            background: #f8fbff;
        }

        .article-result-code {
            color: #172033;
            font-weight: 700;
            font-size: 13px;
        }

        .article-result-description {
            margin-top: 3px;
            color: #667085;
            font-size: 11px;
        }

        .article-result-type {
            margin-top: 3px;
            color: #475467;
            font-size: 11px;
        }


        /* =========================================================
   MODAL BASE
   ========================================================= */

        #modalInsertUpah .modal-dialog {
            max-width: 700px !important;
            width: calc(100% - 30px) !important;

            margin: 1rem auto !important;

            height: auto !important;
            min-height: 0 !important;

            display: block !important;
        }

        #modalInsertUpah .modal-content {
            height: auto !important;
            min-height: 0 !important;

            max-height: calc(100vh - 32px) !important;

            display: flex !important;
            flex-direction: column !important;

            border: 0;
            border-radius: 9px;

            overflow: hidden !important;

            box-shadow: 0 18px 60px rgba(15, 23, 42, .18);
        }


        /* =========================================================
   MODAL HEADER
   ========================================================= */

        #modalInsertUpah .modal-header {
            min-height: 52px;
            padding: 10px 14px;

            flex: 0 0 auto !important;

            border-bottom: 1px solid #e8edf2;
            background: #fff;
        }

        #modalInsertUpah .modal-title {
            color: #172033;
            font-size: 15px;
            font-weight: 750;
        }


        /* =========================================================
   NORMAL MODAL BODY
   ========================================================= */

        #modalInsertUpah:not(.mass-mode) .modal-body {
            height: auto !important;
            min-height: 0 !important;

            flex: 0 1 auto !important;

            overflow-y: auto !important;

            padding: 14px !important;
        }

        #modalInsertUpah:not(.mass-mode) form {
            height: auto !important;
            min-height: 0 !important;
        }


        /* =========================================================
   FORM GROUP
   ========================================================= */

        .upah-form-label {
            margin-bottom: 5px;
            color: #344054;
            font-size: 12px;
            font-weight: 700;
        }

        #modalInsertUpah:not(.mass-mode) .upah-form-group {
            margin-bottom: 10px !important;
        }

        .required {
            color: #dc2626;
        }

        .total-input {
            color: #172033 !important;
            font-weight: 700 !important;
            background: #f8fafc !important;
        }


        /* =========================================================
   QTY LIMIT INFO
   ========================================================= */

        #insert_qty_limit_info {
            margin-top: 5px !important;
        }

        #insert_qty_limit_info .small {
            font-size: 11px !important;
        }

        #insert_qty_limit_message {
            font-size: 11px !important;
            line-height: 1.35;
        }


        /* =========================================================
   ARTICLE NOT FOUND
   ========================================================= */

        .article-not-found {
            display: none;

            margin-top: 5px;
            padding: 7px 9px;

            color: #92400e;
            background: #fffbeb;

            border: 1px solid #fde68a;
            border-radius: 5px;

            font-size: 11px;
        }

        .article-not-found.show {
            display: block;
        }


        /* =========================================================
   NORMAL MODAL FOOTER
   ========================================================= */

        #modalInsertUpah:not(.mass-mode) .modal-footer {
            flex: 0 0 auto !important;

            margin-top: 0 !important;

            min-height: 50px !important;

            padding: 9px 14px !important;

            border-top: 1px solid #e8edf2;
            background: #fbfcfd;
        }


        /* =========================================================
   MODAL BUTTONS
   ========================================================= */

        #modalInsertUpah .btn {
            height: 32px;
            min-height: 32px;

            padding: 0 11px;

            border-radius: 6px;

            font-size: 12px;
        }


        /* =========================================================
   MASS INPUT
   ========================================================= */
        #modalInsertUpah.mass-mode .modal-dialog {
            width: calc(100vw - 24px) !important;
            max-width: 1500px !important;

            margin: .75rem auto !important;

            height: auto !important;
            min-height: 0 !important;
        }

        #modalInsertUpah.mass-mode .modal-content {
            height: auto !important;
            min-height: 0 !important;

            max-height: calc(100vh - 32px) !important;

            display: flex !important;
            flex-direction: column !important;

            overflow: hidden !important;
        }


        /* =========================================================
   MASS MODE - HIDE NORMAL BODY
   ========================================================= */

        #modalInsertUpah.mass-mode form>.modal-body:not(#massUpahBody) {
            display: none !important;
        }


        /* =========================================================
   MASS BODY
   ========================================================= */

        #modalInsertUpah.mass-mode #massUpahBody {
            display: block !important;

            flex: 1 1 auto !important;

            min-height: 0 !important;

            height: auto !important;

            padding: 14px !important;

            overflow: hidden !important;
        }


        /* =========================================================
   MASS TOOLBAR
   ========================================================= */

        .mass-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-start;

            gap: 8px;

            margin-bottom: 10px;

            width: 100%;
        }

        .mass-toolbar>div:first-child {
            margin-right: auto;
        }

        .mass-toolbar #btnAddMassRow,
        .mass-toolbar #btnBackNormalUpah {
            flex: 0 0 auto;
        }

        .mass-toolbar #btnBackNormalUpah {
            margin-left: 2px;
        }

        .mass-toolbar strong {
            color: #172033;
            font-size: 14px;
        }

        .mass-toolbar .small {
            color: #98a2b3 !important;
            font-size: 11px !important;
        }


        /* =========================================================
   MASS TABLE WRAPPER
   ========================================================= */

        .mass-table-wrapper {
            width: 100%;

            overflow-x: auto;
            overflow-y: auto;

            border: 1px solid #e2e6eb;
            border-radius: 7px;

            background: #fff;
        }

        #modalInsertUpah.mass-mode .mass-table-wrapper {
            position: relative !important;

            overflow-x: auto !important;
            overflow-y: auto !important;

            max-height: calc(100vh - 240px) !important;
        }


        /* =========================================================
   MASS TABLE
   ========================================================= */

        .mass-upah-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;

            border-collapse: separate;
            border-spacing: 0;
            background: #fff;

            font-size: 12px;
        }

        .mass-upah-table th {
            position: sticky;
            top: 0;

            z-index: 30;

            height: 36px;

            padding: 7px 8px !important;

            background: #f8f9fb !important;
            color: #667085 !important;

            border: 0 !important;
            border-bottom: 1px solid #e4e7ec !important;

            font-size: 11px !important;
            font-weight: 700 !important;

            white-space: nowrap;

            background-clip: padding-box !important;
            box-shadow: 0 1px 0 #e4e7ec !important;
        }

        .mass-upah-table td {
            height: 42px;

            padding: 6px 7px !important;

            border: 0 !important;
            border-bottom: 1px solid #edf0f2 !important;

            vertical-align: middle;

            background: #fff;

            font-size: 12px;
        }

        .mass-upah-table tbody tr:hover td {
            background: #f8fbff;
        }


        /* =========================================================
   MASS INPUT
   ========================================================= */

        .mass-upah-table .form-control {
            min-width: 105px;

            height: 32px;
            min-height: 32px;

            padding: 4px 8px;

            font-size: 12px;

            border-radius: 5px;
        }

        .mass-upah-table textarea.form-control {
            min-width: 210px;
            height: 32px;

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
            background: #f8fafc !important;
            font-weight: 700 !important;
        }


        /* =========================================================
   MASS SEARCH
   ========================================================= */

        .mass-search-wrapper {
            position: relative;
        }

        .mass-search-result {
            position: absolute;

            left: 0;
            top: calc(100% + 2px);

            width: 300px;

            max-height: 210px;

            overflow-y: auto;

            background: #fff;

            border: 1px solid #dfe3e8;
            border-radius: 6px;

            box-shadow: 0 10px 25px rgba(16, 24, 40, .12);

            z-index: 99999;

            display: none;
        }

        .mass-search-result.show {
            display: block;
        }

        .mass-search-item {
            padding: 9px 10px;

            border-bottom: 1px solid #edf0f2;

            cursor: pointer;

            font-size: 12px;
        }

        .mass-search-item:hover {
            background: #f8fbff;
        }

        .mass-search-code {
            color: #172033;
            font-weight: 700;
        }

        .mass-search-desc {
            color: #667085;
            font-size: 11px;
            margin-top: 2px;
        }

        .mass-required {
            color: #dc2626;
        }


        /* =========================================================
   MASS FOOTER
   ========================================================= */

        #modalInsertUpah.mass-mode .modal-footer {
            flex: 0 0 auto !important;

            min-height: 50px !important;

            margin-top: 0 !important;

            padding: 9px 14px !important;

            border-top: 1px solid #e8edf2;
            background: #fbfcfd;
        }


        /* =========================================================
   FINAL MODAL OVERRIDE
   ========================================================= */

        #modalInsertUpah:not(.mass-mode),
        #modalInsertUpah:not(.mass-mode) .modal-dialog,
        #modalInsertUpah:not(.mass-mode) .modal-content,
        #modalInsertUpah:not(.mass-mode) form {
            height: auto !important;
            min-height: 0 !important;
        }

        #modalInsertUpah:not(.mass-mode) .modal-body {
            flex-grow: 0 !important;
        }

        #modalInsertUpah.mass-mode .modal-content {
            min-height: 0 !important;
        }

        #modalInsertUpah.mass-mode #massUpahBody {
            flex-grow: 1 !important;
        }


        /* =========================================================
   RESPONSIVE
   ========================================================= */

        @media (max-width: 800px) {

            .upah-page {
                padding: 5px !important;
            }

            .upah-header {
                align-items: flex-start;
            }

            .upah-page #searchUpahTable {
                width: 100% !important;
            }

            .upah-table {
                min-width: 1050px;
            }

            .upah-page .card-body.py-2>.d-flex {
                align-items: stretch !important;
            }


            /* NORMAL MODAL */

            #modalInsertUpah:not(.mass-mode) .modal-dialog {
                width: calc(100% - 16px) !important;
                max-width: none !important;

                margin: .5rem auto !important;
            }


            /* MASS MODAL */

            #modalInsertUpah.mass-mode .modal-dialog {
                width: calc(100% - 12px) !important;

                margin: .5rem auto !important;
            }

            #modalInsertUpah.mass-mode .mass-table-wrapper {
                max-height: calc(100vh - 220px) !important;
            }
        }

        /* =========================================================
   UPAH LIVE CHAT
========================================================= */

        #upahChatModal {
            position: fixed;
            inset: 0;
            z-index: 999999;

            display: none;

            align-items: center;
            justify-content: center;

            background: rgba(15, 23, 42, .15);
        }

        #upahChatModal.show {
            display: flex;
        }

        .upah-chat-box {

            width: 480px;
            max-width: calc(100vw - 30px);

            background: #fff;

            border-radius: 12px;

            overflow: hidden;

            border: 1px solid #e2e8f0;

            box-shadow:
                0 20px 60px rgba(15, 23, 42, .20);
        }

        .upah-chat-header {

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 12px 14px;

            border-bottom: 1px solid #edf0f3;
        }

        .upah-chat-header strong {
            display: block;

            font-size: 14px;
            color: #172033;
        }

        .upah-chat-header small {
            display: block;

            margin-top: 2px;

            color: #98a2b3;
            font-size: 10px;
        }

        #upahChatClose {

            width: 28px;
            height: 28px;

            border: 0;
            border-radius: 6px;

            background: #f5f7fa;

            color: #64748b;

            font-size: 18px;

            cursor: pointer;
        }

        .upah-chat-body {
            padding: 14px;
        }

        #upahChatInput {

            width: 100%;
            min-height: 120px;

            resize: vertical;

            padding: 10px;

            border: 1px solid #dfe4ea;
            border-radius: 8px;

            outline: none;

            font-family: inherit;
            font-size: 13px;
        }

        #upahChatInput:focus {

            border-color: #93c5fd;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, .08);
        }

        .upah-chat-footer {

            display: flex;

            align-items: center;
            justify-content: space-between;

            padding: 9px 14px;

            background: #fafbfc;

            border-top: 1px solid #edf0f3;
        }

        .upah-chat-footer span {

            color: #98a2b3;

            font-size: 10px;
        }

        #upahChatSend {

            border: 0;

            border-radius: 7px;

            padding: 7px 15px;

            background: #2563eb;

            color: #fff;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;
        }


        /* =========================================================
   INCOMING
========================================================= */

        #upahChatIncoming {

            position: fixed;

            right: 20px;
            bottom: 20px;

            width: 360px;
            max-width: calc(100vw - 40px);

            display: none;

            background: #fff;

            border: 1px solid #dfe5ec;

            border-radius: 10px;

            box-shadow:
                0 15px 40px rgba(15, 23, 42, .17);

            overflow: hidden;

            z-index: 999998;
        }

        #upahChatIncoming.show {
            display: block;
        }

        .upah-chat-incoming-header {

            display: flex;
            align-items: center;

            gap: 7px;

            padding: 9px 11px;

            border-bottom: 1px solid #edf0f3;
        }

        .upah-chat-dot {

            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #22c55e;
        }

        .upah-chat-incoming-header strong {

            font-size: 11px;

            color: #172033;
        }

        .upah-chat-incoming-header small {

            margin-left: auto;

            color: #98a2b3;

            font-size: 9px;
        }

        #upahChatIncomingMessage {

            padding: 11px;

            color: #475569;

            font-size: 12px;

            line-height: 1.5;

            white-space: pre-wrap;

            word-break: break-word;
        }

        /* MASS INPUT: kolom lega, scroll horizontal, tanpa mengubah JS */
        #modalInsertUpah.mass-mode .modal-dialog {
            width: calc(100vw - 20px) !important;
            max-width: none !important;
        }
        #modalInsertUpah.mass-mode .mass-table-wrapper {
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
        }
        #modalInsertUpah.mass-mode .mass-upah-table {
            width: 1800px !important;
            min-width: 1800px !important;
            table-layout: fixed !important;
        }
        #modalInsertUpah.mass-mode .mass-upah-table th,
        #modalInsertUpah.mass-mode .mass-upah-table td {
            box-sizing: border-box;
        }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(1) { width: 38px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(2) { width: 175px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(3) { width: 230px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(4) { width: 150px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(5) { width: 185px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(6) { width: 155px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(7) { width: 145px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(8) { width: 155px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(9) { width: 125px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(10) { width: 140px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(11) { width: 155px; }
        #modalInsertUpah.mass-mode .mass-upah-table th:nth-child(12) { width: 47px; }
        #modalInsertUpah.mass-mode .mass-upah-table .form-control {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }
        #modalInsertUpah.mass-mode .mass-upah-table .mass-search-wrapper {
            width: 100%;
        }
        #modalInsertUpah.mass-mode .mass-upah-table .mass-search-result {
            width: 320px;
            max-width: 320px;
        }
        #modalInsertUpah.mass-mode .mass-upah-table textarea.form-control {
            min-width: 0 !important;
        }

        /* Mass Input: 50% layar saat awal, lebih tinggi saat baris bertambah. */
        #modalInsertUpah.mass-mode .modal-dialog {
            width: calc(100vw - 20px) !important;
            max-width: none !important;
            margin: 2vh auto !important;
        }
        #modalInsertUpah.mass-mode .modal-content {
            height: 50vh !important;
            min-height: 360px !important;
            max-height: 94vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        /* Saat minimal 3 baris, berikan ruang kerja lebih luas. */
        #modalInsertUpah.mass-mode:has(#massUpahBodyRows tr:nth-child(3)) .modal-content {
            height: 84vh !important;
        }
        #modalInsertUpah.mass-mode .modal-content > form {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }
        #modalInsertUpah.mass-mode .modal-header,
        #modalInsertUpah.mass-mode .modal-footer {
            flex: 0 0 auto !important;
        }
        #modalInsertUpah.mass-mode #massUpahBody {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            height: auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }
        #modalInsertUpah.mass-mode .mass-toolbar,
        #modalInsertUpah.mass-mode #massUpahError:not(.d-none) {
            flex: 0 0 auto !important;
        }
        #modalInsertUpah.mass-mode .mass-table-wrapper {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
        }
        #modalInsertUpah.mass-mode .mass-upah-table td {
            vertical-align: top;
            padding-top: 10px;
            padding-bottom: 12px;
        }
        @media (max-height: 550px) {
            #modalInsertUpah.mass-mode .modal-content {
                min-height: 0 !important;
                height: 94vh !important;
            }
        }
        #modalInsertUpah.mass-mode .mass-upah-row .mass-qty {
    width: 50% !important;
    min-width: 50px;
    max-width: 100%;
}

        /* Overview PO: scoped, tidak memengaruhi modal Mass Input */
        #modalOverviewPO .modal-dialog { max-width: 1180px; width: calc(100% - 24px); margin: 3vh auto; }
        #modalOverviewPO .modal-content { height: 86vh; max-height: 94vh; display: flex; flex-direction: column; border-radius: 10px; overflow: hidden; }
        #modalOverviewPO .modal-header, #modalOverviewPO .modal-footer { flex: 0 0 auto; background: #fff; }
        #modalOverviewPO .modal-body { min-height: 0; display: flex; flex-direction: column; gap: 12px; padding: 15px; }
        #modalOverviewPO .overview-search-line { display: flex; gap: 9px; }
        #modalOverviewPO .overview-search-wrap { flex: 1; position: relative; }
        #modalOverviewPO .overview-search-wrap i { position: absolute; top: 13px; left: 12px; color: #94a3b8; }
        #modalOverviewPO #overviewPOSearch { padding-left: 36px; height: 40px; }
        #modalOverviewPO .overview-notice { font-size: 11px; color: #64748b; }
        #modalOverviewPO .overview-po-list { flex: 1; min-height: 0; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
        #modalOverviewPO .overview-po-card { margin: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
        #modalOverviewPO .overview-po-heading { position: sticky; top: 0; z-index: 4; display: flex; justify-content: space-between; gap: 10px; align-items: center; padding: 12px 14px; background: #eaf3ff; border-bottom: 1px solid #d7e5f8; }
        #modalOverviewPO .overview-po-heading strong { font-size: 15px; color: #172033; }
        #modalOverviewPO .overview-po-heading small { color: #64748b; }
        #modalOverviewPO .overview-table-wrap { overflow-x: auto; }
        #modalOverviewPO .overview-table { width: 100%; min-width: 790px; border-collapse: separate; border-spacing: 0; font-size: 12px; }
        #modalOverviewPO .overview-table th { position: sticky; top: 0; background: #f1f5f9; z-index: 2; color: #475569; font-weight: 700; padding: 10px; white-space: nowrap; border-bottom: 1px solid #e2e8f0; }
        #modalOverviewPO .overview-table td { padding: 9px 10px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        #modalOverviewPO .overview-job td { background: #edf7f2; font-weight: 700; color: #166534; }
        #modalOverviewPO .overview-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        #modalOverviewPO .overview-progress { height: 7px; background: #e2e8f0; border-radius: 10px; min-width: 75px; overflow: hidden; }
        #modalOverviewPO .overview-progress > span { display: block; height: 100%; background: #16a34a; border-radius: inherit; }
        #modalOverviewPO .overview-po-total td { background: #f1f5f9; font-weight: 700; }
        @media(max-width: 600px) { #modalOverviewPO .modal-content { height: 90vh; } #modalOverviewPO .overview-po-heading { align-items: flex-start; flex-direction: column; } }

    </style>
    <div id="upahChatModal" aria-hidden="true">

        <div class="upah-chat-window">

            <div class="upah-chat-header">

                <div class="upah-chat-avatar">
                    <i class="fas fa-comments"></i>
                </div>

                <div class="upah-chat-title">
                    <strong>Transaksi Upah</strong>
                    <span class="upah-chat-status">
                        Live Chat
                    </span>
                </div>

                <button type="button" id="upahChatClose" class="upah-chat-close" aria-label="Close">
                    &times;
                </button>

            </div>

            <div id="upahChatMessages" class="upah-chat-messages">
                <div class="upah-chat-empty">
                    Belum ada pesan.<br>
                    Mulai percakapan dengan user lain.
                </div>
            </div>

            <div id="upahChatTyping" class="upah-chat-typing" aria-live="polite"></div>

            <div id="upahChatImagePreview" class="upah-chat-image-preview">
                <img id="upahChatImagePreviewImg" src="" alt="Preview">

                <div id="upahChatImagePreviewInfo" class="upah-chat-image-preview-info">
                    Gambar siap dikirim
                </div>

                <button type="button" id="upahChatImageRemove" class="upah-chat-image-remove" title="Hapus gambar">
                    &times;
                </button>
            </div>

            <div class="upah-chat-input-area">

                <input type="file" id="upahChatImageInput" accept="image/*" style="display:none;">

                <button type="button" id="upahChatAttach" class="upah-chat-attach" title="Kirim gambar">
                    <i class="fas fa-paperclip"></i>
                </button>

                <div class="upah-chat-input-wrap">

                    <textarea id="upahChatInput" rows="1" maxlength="1000" placeholder="Ketik pesan..." autocomplete="off"></textarea>

                </div>

                <button type="button" id="upahChatSend" class="upah-chat-send" title="Kirim">
                    <i class="fas fa-paper-plane"></i>
                </button>

            </div>

        </div>

    </div>

    <div id="upahChatIncoming" class="upah-chat-incoming">

        <div class="upah-chat-incoming-header">

            <span class="upah-chat-dot"></span>

            <strong id="upahChatIncomingName">
                User
            </strong>

            <small>
                mengirim pesan
            </small>

        </div>

        <div id="upahChatIncomingMessage"></div>

    </div>
    @include('pages.upah.upah-script')

{{-- Tambahan terisolasi: validasi Qty realtime untuk Mass Input. --}}
<style>
#massUpahBodyRows .mass-qty-info { font-size:11px; line-height:1.4; margin-top:5px; white-space:normal; min-width:115px; }
#massUpahBodyRows .mass-qty.mass-qty-invalid { border-color:#dc3545 !important; background:#fff5f5 !important; }
</style>
<script>
(function () {
    'use strict';
    const endpoint = {{ \Illuminate\Support\Js::from(route('upah.transaksi.check.qty')) }};
    let revision = 0, debounce = null, running = false, valid = false;
    const $rows = () => $('#massUpahBodyRows tr');
    const field = ($row, cls, name) => $row.find(cls + ', [name="' + name + '"]').first();
    const read = ($row, cls, name) => String(field($row, cls, name).val() ?? '').trim();
    const fmt = n => new Intl.NumberFormat('id-ID', {maximumFractionDigits:2}).format(Number(n) || 0);
    const esc = s => $('<div>').text(String(s ?? '')).html();
    function status($row, message, error) {
        const $qty = field($row, '.mass-qty', 'qty');
        if (!$qty.length) return;
        let $info = $row.find('.mass-qty-info');
        if (!$info.length) $info = $('<div class="mass-qty-info"></div>').insertAfter($qty);
        $qty.toggleClass('mass-qty-invalid', !!error);
        $info.attr('class', 'mass-qty-info ' + (error ? 'text-danger' : 'text-success')).html(esc(message));
    }
    async function validate() {
        const mine = ++revision;
        const $button = $('#btnSaveMassUpah');
        const groups = new Map();
        const rows = $rows().toArray();
        let okay = rows.length > 0;
        running = true; valid = false;
        $button.prop('disabled', true);
        for (const tr of rows) {
            if (mine !== revision) return;
            const $row = $(tr);
            const article = read($row, '.mass-article', 'article');
            const pekerjaan = read($row, '.mass-pekerjaan', 'pekerjaan');
            const no_po = read($row, '.mass-no-po', 'no_po');
            const raw = read($row, '.mass-qty', 'qty');
            const qty = Number(raw);
            if (!article || !pekerjaan || !raw || !Number.isFinite(qty) || qty <= 0) {
                okay = false; status($row, 'Lengkapi Article, Pekerjaan, dan Qty yang valid.', true); continue;
            }
            const key = JSON.stringify([article.toLowerCase(), pekerjaan.toLowerCase(), no_po.toLowerCase()]);
            const sum = (groups.get(key) || 0) + qty;
            groups.set(key, sum);
            try {
                const response = await $.getJSON(endpoint, {article, pekerjaan, no_po, qty:sum});
                if (mine !== revision) return;
                const isValid = response.valid === true && response.over !== true;
                if (!isValid) okay = false;
                const stats = response.found === true
                    ? 'PO: ' + fmt(response.qty_po) + ' | Terpakai: ' + fmt(response.used_qty) + ' | Sisa: ' + fmt(response.remaining_qty) + ' | Total mass: ' + fmt(sum)
                    : 'Batas Qty PO tidak ditemukan';
                status($row, (isValid ? '✓ ' : '✕ ') + stats + (isValid ? '' : ' — ' + (response.message || 'Qty melebihi batas')), !isValid);
            } catch (e) {
                if (mine !== revision) return;
                okay = false; status($row, 'Gagal mengecek Qty. Coba lagi.', true);
            }
        }
        if (mine === revision) {
            running = false; valid = okay;
            $button.prop('disabled', !okay);
        }
    }
    function schedule() {
        revision++; valid = false;
        $('#btnSaveMassUpah').prop('disabled', true);
        clearTimeout(debounce);
        debounce = setTimeout(validate, 300);
    }
    $(document).on('input change', '#massUpahBodyRows .mass-qty, #massUpahBodyRows .mass-article, #massUpahBodyRows .mass-pekerjaan, #massUpahBodyRows .mass-no-po, #massUpahBodyRows [name="qty"], #massUpahBodyRows [name="article"], #massUpahBodyRows [name="pekerjaan"], #massUpahBodyRows [name="no_po"]', schedule);
    $(document).on('click', '#btnAddMassRow, #massUpahBodyRows button', function () { setTimeout(schedule, 0); });
    $(document).on('click', '#btnToggleMassUpah', function () { setTimeout(schedule, 0); });
    // Capture click sebelum handler penyimpanan lama berjalan jika validasi belum selesai.
    document.addEventListener('click', function (e) {
        const button = e.target.closest && e.target.closest('#btnSaveMassUpah');
        if (button && (running || !valid)) {
            e.preventDefault(); e.stopImmediatePropagation(); schedule();
        }
    }, true);
})();
</script>



<script>
(function () {
    'use strict';
    const $list = $('#overviewPOList');
    const $search = $('#overviewPOSearch');
    const url = {{ \Illuminate\Support\Js::from(route('upah.transaksi.check.qty')) }};
    const fmt = n => Number(n || 0).toLocaleString('id-ID', {maximumFractionDigits: 2});
    const esc = v => $('<div>').text(String(v == null ? '' : v)).html();
    let xhr = null, timer = null, serial = 0;

    function render(items, hasMore) {
        if (!items.length) {
            $list.html('<div class="text-center text-muted py-5">Tidak ada PO / barang yang cocok.</div>');
            return;
        }
        const pos = new Map();
        items.forEach(r => {
            if (!pos.has(r.po)) pos.set(r.po, new Map());
            const jobs = pos.get(r.po);
            if (!jobs.has(r.pekerjaan)) jobs.set(r.pekerjaan, []);
            jobs.get(r.pekerjaan).push(r);
        });
        let html = hasMore ? '<div class="alert alert-info m-2">Hasil dibatasi 500 baris. Ketik No PO atau nama barang untuk mempersempit pencarian.</div>' : '';
        for (const [po, jobs] of pos) {
            let rows = '', total = 0;
            for (const [job, entries] of jobs) {
                rows += '<tr class="overview-job"><td colspan="7">Pekerjaan: ' + esc(job || '-') + '</td></tr>';
                entries.forEach(r => {
                    const qty = r.qty_po;
                    const pct = r.progress;
                    total += Number(r.used_qty || 0);
                    rows += '<tr><td>' + esc(r.article) + '</td><td>' + esc(r.description) + '</td>' +
                        '<td class="overview-num">' + (qty == null ? '—' : fmt(qty)) + '</td>' +
                        '<td class="overview-num">' + fmt(r.used_qty) + '</td>' +
                        '<td class="overview-num">' + (r.remaining_qty == null ? '—' : fmt(r.remaining_qty)) + '</td>' +
                        '<td><div class="overview-progress"><span style="width:' + (pct == null ? 0 : pct) + '%"></span></div></td>' +
                        '<td class="overview-num">' + (pct == null ? '—' : Number(pct).toFixed(1) + '%') + '</td></tr>';
                });
            }
            rows += '<tr class="overview-po-total"><td colspan="3">Total Sudah Upah</td><td class="overview-num">' + fmt(total) + '</td><td colspan="3"></td></tr>';
            html += '<div class="overview-po-card"><div class="overview-po-heading"><strong>No PO: ' + esc(po) + '</strong><small>' + jobs.size + ' jenis pekerjaan</small></div>' +
                '<div class="overview-table-wrap"><table class="overview-table"><thead><tr><th>Article Code</th><th>Nama Barang</th><th class="overview-num">Qty PO</th><th class="overview-num">Sudah Upah</th><th class="overview-num">Sisa Qty</th><th>Progress</th><th class="overview-num">%</th></tr></thead><tbody>' + rows + '</tbody></table></div></div>';
        }
        $list.html(html);
    }
    function load() {
        const current = ++serial;
        if (xhr) xhr.abort();
        $list.html('<div class="text-center text-muted py-5">Memuat Overview PO...</div>');
        xhr = $.ajax({url, method:'GET', dataType:'json', data:{overview_po:1, q:$search.val().trim()}})
            .done(r => { if (current === serial) render(r.items || [], !!r.has_more); })
            .fail((request, status) => {
                if (status !== 'abort' && current === serial) $list.html('<div class="text-center text-danger py-5">Gagal memuat Overview. Periksa Network / Laravel log.</div>');
            });
    }
    $('#modalOverviewPO').on('shown.bs.modal', load);
    $('#modalOverviewPO').on('hidden.bs.modal', function () { serial++; if (xhr) xhr.abort(); });
    $search.on('input', function () { clearTimeout(timer); timer = setTimeout(load, 350); });
    $('#overviewPOReset').on('click', function () { $search.val(''); load(); });
})();
</script>

@endsection
