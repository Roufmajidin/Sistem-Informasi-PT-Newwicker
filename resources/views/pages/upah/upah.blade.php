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


    <style>
        /* =========================================================
   UPAH - COMPACT ERP UI
   UI ONLY
   Tidak mengubah fungsi, AJAX, ID, atau Blade logic.
   ========================================================= */

        .upah-page {
            padding: 8px !important;
            font-size: 10px;
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
            font-size: 17px;
            line-height: 1.2;
            font-weight: 750;
            letter-spacing: -.02em;
        }

        .upah-subtitle {
            margin-top: 3px;
            color: #98a2b3;
            font-size: 9px;
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

        .upah-page .form-control {
            min-height: 32px;
            height: 32px;
            padding: 4px 9px;
            border: 1px solid #dfe3e8;
            border-radius: 6px;
            color: #344054;
            font-size: 9.5px;
            box-shadow: none !important;
        }

        .upah-page .form-control:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .07) !important;
        }


        /* =========================================================
   BUTTON
   ========================================================= */

        .upah-page .btn {
            min-height: 30px;
            height: 30px;
            padding: 0 10px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 650;
            line-height: 28px;
            box-shadow: none !important;
        }

        .upah-page .btn-sm {
            min-height: 30px;
            height: 30px;
        }

        .upah-page #btnAddUpahTransaksi,
        .upah-page #btnExportUpah {
            padding: 0 11px;
        }


        /* =========================================================
   SEARCH
   ========================================================= */

        .upah-page #searchUpahTable {
            height: 32px !important;
            min-height: 32px !important;
            width: 240px !important;
            padding-right: 30px !important;
        }

        .upah-page #clearSearchUpah {
            height: 27px !important;
            min-height: 27px !important;
            width: 27px;
            padding: 0 !important;
            top: 2px !important;
            line-height: 25px !important;
            color: #98a2b3;
        }


        /* =========================================================
   DATE INPUT
   ========================================================= */

        .upah-page #filterDateFrom,
        .upah-page #filterDateTo {
            height: 32px !important;
            min-height: 32px !important;
            width: 135px !important;
        }

        .upah-page .text-muted {
            color: #667085 !important;
        }

        .upah-page #upahAlert {
            margin: 8px 0 0;
            padding: 7px 9px;
            border-radius: 6px;
            font-size: 9px;
        }


        /* =========================================================
   MAIN TABLE
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

        .upah-table {
            width: 100%;
            min-width: 1050px;
            margin: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            font-size: 9px;
        }


        /* =========================================================
   TABLE HEADER
   ========================================================= */

        .upah-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            height: 34px;
            padding: 7px 8px !important;
            background: #f8f9fb !important;
            color: #667085 !important;
            border: 0 !important;
            border-bottom: 1px solid #e4e7ec !important;
            font-size: 8.5px !important;
            font-weight: 700 !important;
            line-height: 1.15;
            letter-spacing: .01em;
            white-space: nowrap;
            vertical-align: middle !important;
        }

        .upah-table thead th+th {
            border-left: 1px solid #eef0f3 !important;
        }


        /* =========================================================
   TABLE BODY
   ========================================================= */

        .upah-table tbody tr {
            height: 38px;
            background: #fff;
            transition: background .12s ease;
        }

        .upah-table tbody tr:hover {
            background: #f8fbff !important;
        }

        .upah-table tbody td {
            height: 38px;
            padding: 5px 8px !important;
            color: #344054;
            background: transparent !important;
            border: 0 !important;
            border-bottom: 1px solid #edf0f2 !important;
            font-size: 9px !important;
            line-height: 1.2;
            vertical-align: middle !important;
        }

        .upah-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }

        .upah-table tbody td:first-child {
            width: 42px;
            color: #667085;
            text-align: center;
            font-size: 8.5px !important;
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
   EMPTY STATE
   ========================================================= */

        .upah-table tbody tr td[colspan] {
            height: 110px !important;
            color: #98a2b3 !important;
            font-size: 9.5px !important;
            background: #fff !important;
        }


        /* =========================================================
   PAGINATION
   ========================================================= */

        .upah-page .pagination {
            margin: 8px 0 0 !important;
        }

        .upah-page .pagination .page-link {
            min-width: 29px;
            height: 29px;
            padding: 5px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-color: #e2e6eb;
            color: #475467;
            font-size: 9px;
            box-shadow: none !important;
        }

        .upah-page .pagination .active .page-link {
            color: #fff;
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
            padding: 8px 10px;
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
            font-size: 9.5px;
        }

        .article-result-description {
            margin-top: 2px;
            color: #667085;
            font-size: 8.5px;
        }

        .article-result-type {
            margin-top: 2px;
            color: #475467;
            font-size: 8.5px;
        }


        /* =========================================================
   MODAL - BASE
   ========================================================= */

        /*
 * Normal modal mengikuti isi form.
 *
 * Tidak menggunakan fixed height.
 * Tidak menggunakan min-height besar.
 */

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
            min-height: 48px;
            padding: 9px 13px;

            flex: 0 0 auto !important;

            border-bottom: 1px solid #e8edf2;
            background: #fff;
        }

        #modalInsertUpah .modal-title {
            color: #172033;
            font-size: 12px;
            font-weight: 750;
        }


        /* =========================================================
   MODAL NORMAL BODY
   ========================================================= */

        #modalInsertUpah:not(.mass-mode) .modal-body {
            height: auto !important;
            min-height: 0 !important;

            flex: 0 1 auto !important;

            overflow-y: auto !important;

            padding: 12px 13px !important;
        }


        /*
 * Form tidak boleh memberikan tinggi kosong.
 */

        #modalInsertUpah:not(.mass-mode) form {
            height: auto !important;
            min-height: 0 !important;
        }


        /* =========================================================
   FORM GROUP
   ========================================================= */

        .upah-form-label {
            margin-bottom: 4px;
            color: #344054;
            font-size: 9px;
            font-weight: 700;
        }

        #modalInsertUpah:not(.mass-mode) .upah-form-group {
            margin-bottom: 8px !important;
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
   ARTICLE NOT FOUND
   ========================================================= */

        .article-not-found {
            display: none;
            margin-top: 4px;
            padding: 6px 8px;
            color: #92400e;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 5px;
            font-size: 8.5px;
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

            min-height: 47px !important;

            padding: 8px 13px !important;

            border-top: 1px solid #e8edf2;
            background: #fbfcfd;
        }


        /* =========================================================
   MODAL BUTTONS
   ========================================================= */

        #modalInsertUpah .btn {
            height: 30px;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 6px;
            font-size: 9px;
        }


        /* =========================================================
   MASS INPUT
   ========================================================= */

        #modalInsertUpah.mass-mode .modal-dialog {
            max-width: 1220px !important;
            width: calc(100% - 24px) !important;

            margin: 1rem auto !important;

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

            padding: 12px 13px !important;

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
            margin-bottom: 9px;
            width: 100%;
        }

        /* Judul tetap di kiri */
        .mass-toolbar>div:first-child {
            margin-right: auto;
        }

        /* Tombol berada di kanan dan berdampingan */
        .mass-toolbar #btnAddMassRow,
        .mass-toolbar #btnBackNormalUpah {
            flex: 0 0 auto;
        }

        /* Jarak antar tombol */
        .mass-toolbar #btnBackNormalUpah {
            margin-left: 2px;
        }

        .mass-toolbar strong {
            color: #172033;
            font-size: 11px;
        }

        .mass-toolbar .small {
            color: #98a2b3 !important;
            font-size: 8px !important;
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


        /*
 * Saat mass mode:
 * tabel menggunakan area viewport sendiri.
 */

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
            width: 100%;
            min-width: 1450px;

            border-collapse: separate;
            border-spacing: 0;

            background: #fff;
            font-size: 9px;
        }

        .mass-upah-table th {
            position: sticky;
            top: 0;

            z-index: 5;

            height: 31px;

            padding: 6px 7px !important;

            background: #f8f9fb !important;
            color: #667085 !important;

            border: 0 !important;
            border-bottom: 1px solid #e4e7ec !important;

            font-size: 8.5px !important;
            font-weight: 700 !important;

            white-space: nowrap;
        }

        .mass-upah-table td {
            height: 38px;

            padding: 5px 6px !important;

            border: 0 !important;
            border-bottom: 1px solid #edf0f2 !important;

            vertical-align: middle;

            background: #fff;
        }

        .mass-upah-table tbody tr:hover td {
            background: #f8fbff;
        }


        /* =========================================================
   MASS INPUT
   ========================================================= */

        .mass-upah-table .form-control {
            min-width: 105px;

            height: 29px;
            min-height: 29px;

            padding: 3px 7px;

            font-size: 9px;

            border-radius: 5px;
        }

        .mass-upah-table textarea.form-control {
            min-width: 210px;
            height: 29px;

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
            padding: 7px 9px;

            border-bottom: 1px solid #edf0f2;

            cursor: pointer;

            font-size: 9px;
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
            font-size: 8px;
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

            min-height: 47px !important;

            margin-top: 0 !important;

            padding: 8px 13px !important;

            border-top: 1px solid #e8edf2;
            background: #fbfcfd;
        }


        /* =========================================================
   MASS TABLE STICKY HEADER
   ========================================================= */

        #modalInsertUpah.mass-mode .mass-upah-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        #modalInsertUpah.mass-mode .mass-upah-table thead th {
            position: sticky !important;

            top: 0 !important;

            z-index: 30 !important;

            background: #f8f9fb !important;

            background-clip: padding-box !important;

            box-shadow: 0 1px 0 #e4e7ec !important;
        }


        /* =========================================================
   MAIN TABLE STICKY HEADER
   ========================================================= */

        .upah-page .upah-table-wrapper {
            position: relative !important;

            overflow-x: auto !important;
            overflow-y: visible !important;
        }

        .upah-page .upah-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        .upah-page .upah-table thead {
            position: sticky !important;

            top: 0 !important;

            z-index: 100 !important;
        }

        .upah-page .upah-table thead th {
            position: sticky !important;

            top: var(--upah-sticky-top) !important;

            z-index: 101 !important;

            background: #f8f9fb !important;

            background-clip: padding-box !important;

            box-shadow: 0 1px 0 #e4e7ec !important;
        }


        /*
 * Solid background.
 */

        .upah-page .upah-table thead th::before {
            content: "";

            position: absolute;

            inset: 0;

            z-index: -1;

            background: #f8f9fb;
        }


        /* =========================================================
   MAIN TABLE SCROLLBAR
   ========================================================= */

        .upah-page .upah-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .upah-page .upah-table-wrapper::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        .upah-page .upah-table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd1d8;
            border-radius: 20px;
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


            /* ---------------------------------------------
       NORMAL MODAL MOBILE
       --------------------------------------------- */

            #modalInsertUpah:not(.mass-mode) .modal-dialog {
                width: calc(100% - 16px) !important;
                max-width: none !important;

                margin: .5rem auto !important;
            }


            /* ---------------------------------------------
       MASS MODAL MOBILE
       --------------------------------------------- */

            #modalInsertUpah.mass-mode .modal-dialog {
                width: calc(100% - 12px) !important;

                margin: .5rem auto !important;
            }


            #modalInsertUpah.mass-mode .mass-table-wrapper {
                max-height: calc(100vh - 220px) !important;
            }
        }


        /* =========================================================
   FINAL MODAL OVERRIDE
   Memastikan Bootstrap / CSS master tidak memberi
   tinggi paksa pada modal normal.
   ========================================================= */

        #modalInsertUpah:not(.mass-mode),
        #modalInsertUpah:not(.mass-mode) .modal-dialog,
        #modalInsertUpah:not(.mass-mode) .modal-content,
        #modalInsertUpah:not(.mass-mode) form {
            height: auto !important;
            min-height: 0 !important;
        }


        /*
 * Normal modal tidak boleh memiliki ruang kosong
 * akibat flex-grow dari CSS global.
 */

        #modalInsertUpah:not(.mass-mode) .modal-body {
            flex-grow: 0 !important;
        }


        /*
 * Mass modal boleh mengambil ruang yang tersedia.
 */

        #modalInsertUpah.mass-mode .modal-content {
            min-height: 0 !important;
        }

        #modalInsertUpah.mass-mode #massUpahBody {
            flex-grow: 1 !important;
        }
    </style>

    @include('pages.upah.upah-script')

@endsection
