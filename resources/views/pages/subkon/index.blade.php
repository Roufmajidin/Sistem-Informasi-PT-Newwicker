@extends('master.master')
@section('content')

    <style>
    /* =========================================================
       SUB KONTRAK - COMPACT ERP UI
       Visual language mengikuti upah.blade.php.
       UI ONLY:
       - tidak mengubah route
       - tidak mengubah AJAX
       - tidak mengubah ID
       - tidak mengubah form/action
       - tidak mengubah Blade logic
       ========================================================= */

    .subkon-page{
        padding:8px!important;
        font-size:10px;
        color:#172033;
        --subkon-sticky-top:0px;
    }

    /* =========================
       TITLE / HEADER
       ========================= */

    .subkon-page .subkon-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        min-height:56px;
        margin-bottom:10px;
        padding:10px 13px;
        background:#fff;
        border:1px solid #e2e6eb;
        border-radius:8px;
        box-shadow:0 1px 4px rgba(16,24,40,.035);
    }

    .subkon-page .subkon-title{
        margin:0;
        color:#172033;
        font-size:17px;
        line-height:1.2;
        font-weight:750;
        letter-spacing:-.02em;
    }

    .subkon-page .subkon-subtitle{
        margin-top:3px;
        color:#98a2b3;
        font-size:9px;
    }

    /* =========================
       TOOLBAR
       ========================= */

    .subkon-page .subkon-toolbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:8px;
        margin:0 0 8px!important;
        padding:8px 0!important;
    }

    .subkon-page .subkon-search{
        position:relative;
        width:300px;
    }

    .subkon-page .subkon-search .form-control{
        width:100%!important;
        height:32px!important;
        min-height:32px!important;
        padding:4px 31px 4px 31px!important;
        border:1px solid #dfe3e8;
        border-radius:6px;
        color:#344054;
        font-size:9.5px;
        box-shadow:none!important;
    }

    .subkon-page .subkon-search .form-control:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 2px rgba(37,99,235,.07)!important;
    }

    .subkon-page .subkon-search-icon{
        position:absolute;
        left:11px;
        top:9px;
        z-index:2;
        color:#98a2b3;
        font-size:10px;
    }

    .subkon-page .subkon-search-clear{
        position:absolute;
        right:3px;
        top:3px;
        width:26px;
        height:26px;
        padding:0!important;
        border:0;
        background:transparent;
        color:#98a2b3;
        line-height:26px;
        cursor:pointer;
        display:none;
    }

    .subkon-page .subkon-search-clear:hover{
        color:#344054;
    }

    .subkon-page .subkon-search-info{
        min-height:14px;
        margin-left:3px;
        color:#98a2b3;
        font-size:8.5px;
    }

    .subkon-page .btn-tambah-kontrak{
        height:30px;
        min-height:30px;
        padding:0 11px;
        border-radius:6px;
        font-size:9px;
        font-weight:650;
        line-height:28px;
        box-shadow:none!important;
    }

    .subkon-page .btn{
        border-radius:6px;
        box-shadow:none!important;
    }

    .subkon-page .btn-sm{
        min-height:30px;
        height:30px;
        font-size:9px;
    }

    /* =========================
       ALERT
       ========================= */

    .subkon-page .alert{
        margin:0 0 8px;
        padding:7px 9px;
        border-radius:6px;
        font-size:9px;
    }

    /* =========================
       CARD
       ========================= */

    .subkon-page > .card,
    .subkon-page .card{
        border:1px solid #e2e6eb;
        border-radius:8px;
        background:#fff;
        box-shadow:0 1px 4px rgba(16,24,40,.035);
        overflow:hidden;
    }

    .subkon-page .card-body{
        padding:0!important;
    }

    /* =========================
       TABLE WRAPPER
       ========================= */

    .subkon-page #tableSubkonWrapper{
        width:100%;
        overflow-x:auto!important;
        overflow-y:visible!important;
        background:#fff;
        scrollbar-width:thin;
    }

    .subkon-page #tableSubkonWrapper::-webkit-scrollbar{
        height:6px;
    }

    .subkon-page #tableSubkonWrapper::-webkit-scrollbar-track{
        background:#f3f4f6;
    }

    .subkon-page #tableSubkonWrapper::-webkit-scrollbar-thumb{
        background:#cbd1d8;
        border-radius:20px;
    }

    /* =========================
       MAIN TABLE
       ========================= */

    .subkon-page #tableSubkon{
        width:100%;
        min-width:1050px;
        margin:0!important;
        border-collapse:separate!important;
        border-spacing:0!important;
        background:#fff;
        font-size:9px;
    }

    .subkon-page #tableSubkon thead{
        position:sticky!important;
        top:var(--subkon-sticky-top)!important;
        z-index:100;
    }

    .subkon-page #tableSubkon thead th{
        position:sticky!important;
        top:var(--subkon-sticky-top)!important;
        z-index:101;
        height:34px;
        padding:7px 8px!important;
        background:#f8f9fb!important;
        background-clip:padding-box!important;
        color:#667085!important;
        border:0!important;
        border-bottom:1px solid #e4e7ec!important;
        font-size:8.5px!important;
        font-weight:700!important;
        line-height:1.15;
        letter-spacing:.01em;
        white-space:nowrap;
        vertical-align:middle!important;
        box-shadow:0 1px 0 #e4e7ec!important;
    }

    .subkon-page #tableSubkon thead th::before{
        content:"";
        position:absolute;
        inset:0;
        z-index:-1;
        background:#f8f9fb;
    }

    .subkon-page #tableSubkon thead th+th{
        border-left:1px solid #eef0f3!important;
    }

    .subkon-page #tableSubkon tbody tr{
        height:38px;
        background:#fff;
        transition:background .12s ease;
    }

    .subkon-page #tableSubkon tbody tr:hover{
        background:#f8fbff!important;
    }

    .subkon-page #tableSubkon tbody td{
        height:38px;
        padding:5px 8px!important;
        color:#344054;
        background:transparent!important;
        border:0!important;
        border-bottom:1px solid #edf0f2!important;
        font-size:9px!important;
        line-height:1.2;
        vertical-align:middle!important;
    }

    .subkon-page #tableSubkon tbody tr:last-child td{
        border-bottom:0!important;
    }

    .subkon-page #tableSubkon tbody td:first-child{
        width:42px;
        color:#667085;
        text-align:center;
        font-size:8.5px!important;
    }

    .subkon-page #tableSubkon tbody td:nth-child(2){
        color:#172033;
        font-weight:700;
        white-space:nowrap;
    }

    .subkon-page #tableSubkon tbody td:nth-child(3){
        color:#667085;
        min-width:220px;
    }

    .subkon-page #tableSubkon tbody td:nth-child(4){
        color:#172033;
        font-weight:650;
        white-space:nowrap;
    }

    .subkon-page #tableSubkon tbody td:nth-child(5){
        color:#667085;
        white-space:nowrap;
    }

    .subkon-page #tableSubkon tbody td:nth-child(6){
        color:#172033;
        font-weight:700;
        white-space:nowrap;
        font-variant-numeric:tabular-nums;
    }

    .subkon-page #tableSubkon tbody td:nth-child(7){
        color:#667085;
        min-width:180px;
    }

    .subkon-page #tableSubkon tbody td:nth-child(8){
        color:#667085;
        white-space:nowrap;
    }

    .subkon-page #tableSubkon tbody td:last-child{
        white-space:nowrap;
        text-align:center;
    }

    .subkon-page #tableSubkon tbody td[colspan]{
        height:110px!important;
        color:#98a2b3!important;
        font-size:9.5px!important;
        background:#fff!important;
    }

    /* =========================
       ACTION BUTTON
       ========================= */

    .subkon-page #tableSubkon .btn{
        width:29px;
        height:29px;
        min-height:29px;
        padding:0!important;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:6px;
        font-size:9px;
    }

    .subkon-page #tableSubkon .btn + .btn{
        margin-left:3px;
    }

    .subkon-page #tableSubkon form{
        margin:0;
    }

    /* =========================
       CARD FOOTER / PAGINATION
       ========================= */

    .subkon-page .card-footer{
        padding:7px 10px!important;
        background:#fff;
        border-top:1px solid #edf0f2;
    }

    .subkon-page .pagination{
        margin:0!important;
    }

    .subkon-page .pagination .page-link{
        min-width:29px;
        height:29px;
        padding:5px 8px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-color:#e2e6eb;
        color:#475467;
        font-size:9px;
        box-shadow:none!important;
    }

    .subkon-page .pagination .active .page-link{
        color:#fff;
    }

    /* =========================
       MODAL
       ========================= */

    .subkon-page + .modal-subkon,
    .modal-subkon{
        font-size:10px;
    }

    .modal-subkon .modal-dialog{
        max-width:700px;
        margin:1.25rem auto;
    }

    .modal-subkon .modal-content{
        border:0;
        border-radius:9px;
        overflow:hidden;
        box-shadow:0 18px 60px rgba(15,23,42,.18);
    }

    .modal-subkon .modal-header{
        min-height:48px;
        padding:9px 13px;
        border-bottom:1px solid #e8edf2;
        background:#fff;
        align-items:center;
    }

    .modal-subkon .modal-title{
        margin:0;
        color:#172033;
        font-size:12px;
        font-weight:750;
    }

    .modal-subkon .modal-header .close{
        margin:0 0 0 auto;
        padding:0 4px;
        font-size:20px;
        line-height:24px;
        color:#667085;
        opacity:.75;
    }

    .modal-subkon .modal-body{
        padding:12px 13px;
        background:#fff;
    }

    .modal-subkon .modal-footer{
        padding:8px 13px;
        border-top:1px solid #e8edf2;
        background:#fbfcfd;
    }

    .modal-subkon .form-group{
        margin-bottom:10px;
    }

    .modal-subkon label{
        display:block;
        margin-bottom:4px;
        color:#344054;
        font-size:9px;
        font-weight:700;
    }

    .modal-subkon .form-control{
        min-height:32px;
        height:32px;
        padding:4px 9px;
        border:1px solid #dfe3e8;
        border-radius:6px;
        color:#344054;
        font-size:9.5px;
        box-shadow:none!important;
    }

    .modal-subkon textarea.form-control{
        height:auto;
        min-height:68px;
        line-height:1.35;
        resize:vertical;
    }

    .modal-subkon .form-control:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 2px rgba(37,99,235,.07)!important;
    }

    .modal-subkon .text-muted{
        color:#98a2b3!important;
        font-size:8.5px;
    }

    .modal-subkon .modal-footer .btn{
        min-height:30px;
        height:30px;
        padding:0 10px;
        border-radius:6px;
        font-size:9px;
        font-weight:650;
    }

    /* =========================
       ARTICLE / SUPPLIER / KATEGORI SEARCH
       ========================= */

    .modal-subkon .article-search-wrapper{
        position:relative;
    }

    .modal-subkon .article-input-wrapper{
        position:relative;
    }

    .modal-subkon .article-search-result,
    .modal-subkon .search-dropdown{
        position:absolute;
        left:0;
        right:0;
        top:calc(100% + 3px);
        z-index:99999;
        display:none;
        max-height:250px;
        overflow-y:auto;
        background:#fff;
        border:1px solid #dfe3e8;
        border-radius:7px;
        box-shadow:0 10px 25px rgba(16,24,40,.12);
    }

    .modal-subkon .article-search-result.show,
    .modal-subkon .search-dropdown.show{
        display:block;
    }

    .modal-subkon .article-result-item,
    .modal-subkon .search-result-item{
        padding:8px 10px;
        cursor:pointer;
        border-bottom:1px solid #edf0f2;
        transition:background .12s ease;
        font-size:9px;
    }

    .modal-subkon .article-result-item:last-child,
    .modal-subkon .search-result-item:last-child{
        border-bottom:0;
    }

    .modal-subkon .article-result-item:hover,
    .modal-subkon .search-result-item:hover{
        background:#f8fbff;
    }

    .modal-subkon .article-result-code{
        color:#172033;
        font-weight:700;
        font-size:9.5px;
    }

    .modal-subkon .article-result-description{
        margin-top:2px;
        color:#667085;
        font-size:8.5px;
    }

    /* Existing JS may generate generic dropdown content.
       Keep it visually compact without changing selectors/functions. */
    .modal-subkon .search-dropdown > div{
        padding:7px 9px;
        border-bottom:1px solid #edf0f2;
        cursor:pointer;
        font-size:9px;
    }

    .modal-subkon .search-dropdown > div:last-child{
        border-bottom:0;
    }

    .modal-subkon .search-dropdown > div:hover{
        background:#f8fbff;
    }

    /* =========================
       ARTICLE INFO
       ========================= */

    .modal-subkon #articleInfo{
        margin:0 0 10px;
        padding:9px 10px;
        border-radius:7px;
        background:#f8fafc!important;
        border-color:#e4e7ec!important;
    }

    .modal-subkon #articleInfo .row{
        row-gap:7px;
    }

    .modal-subkon #articleDescription,
    .modal-subkon #articleQty,
    .modal-subkon #articleFinishing{
        margin-top:2px;
        color:#172033;
        font-size:9.5px;
        font-weight:650;
    }

    /* =========================
       ERROR
       ========================= */

    .modal-subkon #formTambahKontrakError{
        margin-top:4px;
        padding:7px 9px;
        border-radius:6px;
        font-size:9px;
    }

    /* =========================
       RESPONSIVE
       ========================= */

    @media(max-width:800px){

        .subkon-page{
            padding:5px!important;
        }

        .subkon-page .subkon-toolbar{
            align-items:stretch;
        }

        .subkon-page .subkon-search{
            width:100%;
        }

        .subkon-page .btn-tambah-kontrak{
            width:100%;
        }

        .subkon-page #tableSubkon{
            min-width:1050px;
        }

        .modal-subkon .modal-dialog{
            width:calc(100% - 12px);
            margin:6px auto;
        }

        .modal-subkon .modal-body{
            padding:10px;
        }

        .modal-subkon .modal-footer{
            padding:7px 10px;
        }
    }
    </style>


    @include('pages.subkon.style.style')
    @include('pages.subkon.script.script')

    <div class="container-fluid subkon-page">

        {{-- TITLE --}}
        @section('btn')
        <div class="mb-3">

            <h4 class="subkon-title mb-1">
                Sub Kontrak
            </h4>

            <small class="subkon-subtitle text-muted">
                List Sub Kontrak Supplier
            </small>

        </div>
        
        @endsection



        {{-- TOOLBAR --}}
        <div class="subkon-toolbar mb-3">

        {{-- ALERT --}}
        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

            <div class="subkon-search mt-4">

                <i class="fas fa-search subkon-search-icon"></i>

                <input type="text"
                       id="searchSubkon"
                       class="form-control"
                       placeholder="Cari article, supplier, kategori, harga, remark...">

                <button type="button"
                        id="clearSearchSubkon"
                        class="subkon-search-clear"
                        title="Clear">

                    <i class="fas fa-times"></i>

                </button>

            </div>


            <div class="subkon-search-info"
                 id="searchSubkonInfo">
            </div>


            <button type="button"
                    class="btn btn-primary btn-tambah-kontrak"
                    id="btnTambahKontrak">

                <i class="fas fa-plus mr-1"></i>

                Tambah Kontrak

            </button>

        </div>


    {{-- Table --}}
    <div class="card">

        <div class="card-body p-0">

           <div id="tableSubkonWrapper">


                <table class="table table-bordered table-hover mb-0" id="tableSubkon">


                    <thead class="thead-light">

                        <tr>

                            <th width="50">No</th>

                            <th>Article Code</th>
                            <th>Desc</th>

                            <th>Supplier</th>

                            <th>Kategori</th>

                            <th class="text-right">
                                Harga Kontrak
                            </th>

                            <th>Remark</th>

                            <th width="150">
                                Updated
                            </th>

                            <th width="160">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody id="subkonTableBody">

                        @forelse($kontrak as $index => $item)
                            <tr>

                                <td>
                                    {{ $kontrak->firstItem() + $index }}
                                </td>


                                <td>
                                    <strong>
                                        {{ $item->article_code }}
                                    </strong>
                                </td>
                                  <td>
                                        {{ $item->description }}
                                </td>


                                <td>
                                    {{ $item->supplier?->name ?? '-' }}
                                </td>


                                <td>
                                    {{ $item->kategori ?? '-' }}
                                </td>


                                <td class="text-right">

                                    Rp
                                    {{ number_format($item->harga_kontrak, 2, ',', '.') }}

                                </td>


                                <td>
                                    {{ $item->remark ?? '-' }}
                                </td>


                                <td>

                                    {{ optional($item->updated_at)->format('d/m/Y H:i') }}

                                </td>


                                <td>




                                    <button type="button" class="btn btn-sm btn-warning btn-edit-kontrak"
                                        data-id="{{ $item->id }}" title="Edit">

                                        <i class="fa fa-edit"></i>

                                    </button>

                                    <form action="{{ route('subkon.destroy', $item) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm(
                                        'Hapus kontrak ini?'
                                    )">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger">

                                            <i class="fa fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center py-4">

                                    Belum ada data kontrak supplier.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if ($kontrak->hasPages())
            <div class="card-footer">

                {{ $kontrak->links() }}

            </div>
        @endif

    </div>

</div>

{{-- modals --}}
<!-- =========================================================
                 MODAL TAMBAH KONTRAK
            ========================================================= -->
<div class="modal fade modal-subkon" id="modalTambahKontrak" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="modalKontrakTitle">

                    Tambah Kontrak Supplier

                </h5>

                <button type="button" class="close" data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            <form id="formTambahKontrak">

                @csrf

                <input type="hidden" id="subkon_id" name="subkon_id">
                <div class="modal-body">

                    <!-- =========================================
                                     ARTICLE
                                ========================================== -->

                    <div class="form-group">

                        <label>
                            Article Code
                            <span class="text-danger">*</span>
                        </label>

                        <div class="article-search-wrapper">

                            <div class="article-input-wrapper">

                                <input type="text" id="article_code" name="article_code" class="form-control"
                                    autocomplete="off" placeholder="Cari article code...">

                                <input type="hidden" id="detail_po_id" name="detail_po_id">

                            </div>


                            <div id="articleSearchResult" class="article-search-result">
                            </div>

                        </div>

                        <small class="text-muted">
                            Cari dari Detail PO. Jika tidak ditemukan,
                            article code tetap bisa diketik manual.
                        </small>

                    </div>


                    <!-- INFO ARTICLE -->

                    <div id="articleInfo" class="alert alert-light border d-none">

                        <div class="row">

                            <div class="col-md-6">

                                <small class="text-muted">
                                    Description
                                </small>

                                <div id="articleDescription">
                                    -
                                </div>

                            </div>


                            <div class="col-md-3">

                                <small class="text-muted">
                                    Qty
                                </small>

                                <div id="articleQty">
                                    -
                                </div>

                            </div>


                            <div class="col-md-3">

                                <small class="text-muted">
                                    Finishing
                                </small>

                                <div id="articleFinishing">
                                    -
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="row">

                        <!-- =====================================
                                         SUPPLIER
                                    ====================================== -->

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Supplier
                                    <span class="text-danger">*</span>
                                </label>

                                <div class="position-relative">

                                    <input type="text" id="supplier_search" class="form-control"
                                        autocomplete="off" placeholder="Cari supplier...">

                                    <input type="hidden" id="supplier_id" name="supplier_id">
                                    <input type="hidden" id="description" name="description">
                                    <div id="supplierSearchResult" class="search-dropdown">
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =====================================
                                         KATEGORI
                                    ====================================== -->

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>
                                    Kategori
                                </label>

                                <div class="position-relative">

                                    <input type="text" id="kategori_search" class="form-control"
                                        autocomplete="off" placeholder="Cari kategori...">

                                    <input type="hidden" id="kategori" name="kategori">

                                    <div id="kategoriSearchResult" class="search-dropdown">
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =========================================
                                     HARGA
                                ========================================== -->

                    <div class="form-group">

                        <label>
                            Harga Kontrak
                            <span class="text-danger">*</span>
                        </label>

                        <input type="number" name="harga_kontrak" id="harga_kontrak" class="form-control"
                            min="0" step="0.01" placeholder="0">

                    </div>


                    <!-- =========================================
                                     REMARK
                                ========================================== -->

                    <div class="form-group">

                        <label>
                            Remark
                            <small class="text-muted">
                                (opsional)
                            </small>
                        </label>

                        <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="Catatan kontrak..."></textarea>

                    </div>


                    <div id="formTambahKontrakError" class="alert alert-danger d-none">
                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit" class="btn btn-primary" id="btnSimpanKontrak">

                        <i class="fas fa-save"></i>
                        Simpan Kontrak

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@endsection