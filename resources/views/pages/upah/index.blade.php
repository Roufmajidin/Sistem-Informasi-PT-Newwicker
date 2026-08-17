@extends('master.master')

@section('content')


<style>
/* =========================================================
   TABLE STYLE - SAME VISUAL LANGUAGE AS BOM C_CREATE
   This is UI ONLY. Existing markup, IDs, AJAX and JS are kept.
   ========================================================= */

#tableUpahWrapper{
    width:100%;
    overflow-x:auto;
    overflow-y:visible;
    background:#fff;
    border:1px solid #e2e6eb;
    border-radius:8px;
    box-shadow:0 1px 4px rgba(16,24,40,.035);
    scrollbar-width:thin;
}

#tableUpahWrapper::-webkit-scrollbar{
    height:6px;
}

#tableUpahWrapper::-webkit-scrollbar-track{
    background:#f3f4f6;
}

#tableUpahWrapper::-webkit-scrollbar-thumb{
    background:#cbd1d8;
    border-radius:20px;
}

/* Main table */
#tableUpah{
    width:100%;
    min-width:900px;
    margin:0!important;
    border:0!important;
    border-collapse:separate!important;
    border-spacing:0!important;
    background:#fff;
    font-size:10px;
}

/* Header */
#tableUpah thead th{
    height:34px;
    padding:7px 9px!important;
    background:#f8f9fb!important;
    color:#667085!important;
    border:0!important;
    border-bottom:1px solid #e4e7ec!important;
    font-size:9px!important;
    font-weight:700!important;
    line-height:1.2;
    letter-spacing:.01em;
    white-space:nowrap;
    vertical-align:middle!important;
}

#tableUpah thead th + th{
    border-left:1px solid #eef0f3!important;
}

/* Body */
#tableUpah tbody tr{
    height:39px;
    background:#fff;
    transition:background .12s ease;
}

#tableUpah tbody tr:hover{
    background:#f8fbff!important;
}

#tableUpah tbody td{
    height:39px;
    padding:6px 9px!important;
    color:#344054;
    background:transparent!important;
    border:0!important;
    border-bottom:1px solid #edf0f2!important;
    font-size:9.5px!important;
    line-height:1.25;
    vertical-align:middle!important;
}

#tableUpah tbody tr:last-child td{
    border-bottom:0!important;
}

/* Number column */
#tableUpah tbody td:first-child{
    width:48px;
    color:#667085;
    text-align:center;
    font-size:9px!important;
}

/* Article */
#tableUpah tbody td:nth-child(2){
    color:#172033;
    font-weight:650;
}

/* Description */
#tableUpah tbody td:nth-child(3){
    color:#667085;
}

/* Jenis badge-like appearance without changing markup */
#tableUpah tbody td:nth-child(4){
    color:#344054;
    font-weight:600;
}

/* Price */
#tableUpah tbody td:nth-child(5){
    color:#172033;
    font-weight:700;
    font-variant-numeric:tabular-nums;
    white-space:nowrap;
}

/* Updated */
#tableUpah tbody td:nth-child(6){
    color:#667085;
    white-space:nowrap;
}

/* Action */
#tableUpah tbody td:last-child{
    width:125px;
    white-space:nowrap;
    text-align:center;
}

/* Compact action buttons.
   Existing IDs/classes are untouched. */
#tableUpah .btn{
    width:29px;
    height:29px;
    min-width:29px;
    min-height:29px;
    padding:0!important;
    margin:0 2px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:6px!important;
    font-size:9px!important;
    line-height:1;
    box-shadow:none!important;
    transition:all .12s ease;
}

#tableUpah .btn i{
    font-size:9px;
}

#tableUpah .btn-info{
    color:#2563eb!important;
    background:#eff6ff!important;
    border:1px solid #bfdbfe!important;
}

#tableUpah .btn-info:hover{
    background:#dbeafe!important;
    border-color:#93c5fd!important;
}

#tableUpah .btn-warning{
    color:#b45309!important;
    background:#fffbeb!important;
    border:1px solid #fde68a!important;
}

#tableUpah .btn-warning:hover{
    background:#fef3c7!important;
    border-color:#fcd34d!important;
}

#tableUpah .btn-danger{
    color:#dc2626!important;
    background:#fef2f2!important;
    border:1px solid #fecaca!important;
}

#tableUpah .btn-danger:hover{
    background:#fee2e2!important;
    border-color:#fca5a5!important;
}

/* Empty state */
#tableUpah .empty-row td{
    height:120px!important;
    color:#98a2b3!important;
    background:#fff!important;
    font-size:10px!important;
}

/* Search/toolbar to visually match compact table */
.upah-toolbar{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.upah-search{
    height:34px;
    position:relative;
    width:300px;
}

.upah-search .form-control{
    height:34px!important;
    min-height:34px!important;
    padding:5px 34px 5px 30px!important;
    border:1px solid #dfe3e8!important;
    border-radius:6px!important;
    box-shadow:none!important;
    font-size:10px!important;
}

.upah-search .form-control:focus{
    border-color:#93c5fd!important;
    box-shadow:0 0 0 2px rgba(37,99,235,.07)!important;
}

.upah-search-icon{
    position:absolute;
    left:10px;
    top:50%;
    transform:translateY(-50%);
    z-index:2;
    color:#98a2b3;
    font-size:10px;
}

.upah-search-clear{
    position:absolute;
    right:7px;
    top:50%;
    transform:translateY(-50%);
    width:22px;
    height:22px;
    padding:0;
    border:0;
    background:transparent;
    color:#98a2b3;
    cursor:pointer;
    border-radius:4px;
}

.upah-search-clear:hover{
    background:#f2f4f7;
    color:#475467;
}

.btn-tambah-upah{
    height:34px;
    min-height:34px;
    padding:0 11px!important;
    border-radius:6px!important;
    font-size:9.5px!important;
    font-weight:650!important;
    box-shadow:none!important;
}

/* Search info */
.upah-search-info{
    min-height:18px;
    color:#98a2b3;
    font-size:8.5px;
}

/* Responsive */
@media(max-width:800px){
    #tableUpahWrapper{
        border-radius:7px;
    }

    .upah-toolbar{
        align-items:stretch;
    }

    .upah-search{
        width:100%;
    }

    .btn-tambah-upah{
        width:100%;
    }
}
</style>


    <div class="container-fluid">

        {{-- =====================================================
            HEADER
        ====================================================== --}}

        @section('btn')
        <div class="mb-3">

            <h4 class="mb-1">
                Upah Borongan
            </h4>

            <small class="text-muted">
                Daftar harga upah borongan
            </small>

        </div>
        
        @endsection


        {{-- =====================================================
            TOOLBAR
        ====================================================== --}}

        <div class="upah-toolbar mb-3" style="margin-top: 50px">

            <div class="upah-search">

                <i class="fa fa-search upah-search-icon"></i>

                <input type="text"
                       id="searchUpah"
                       class="form-control"
                       placeholder="Cari article, description, jenis, harga...">

                <button type="button"
                        id="clearSearchUpah"
                        class="upah-search-clear">

                    <i class="fas fa-times"></i>

                </button>

            </div>


            <div id="searchUpahInfo"
                 class="upah-search-info">
            </div>


            <button type="button"
                    class="btn btn-primary btn-tambah-upah"
                    id="btnTambahUpah">

                <i class="fa fa-plus mr-1"></i>

                Tambah Upah

            </button>

        </div>


        {{-- =====================================================
            TABLE
        ====================================================== --}}

        <div id="tableUpahWrapper">

            <table id="tableUpah"
                   class="table table-bordered table-hover mb-0">

                <thead>

                    <tr>

                        <th>
                            NO
                        </th>

                        <th>
                            ARTICLE
                        </th>

                        <th>
                            DESCRIPTION
                        </th>

                        <th>
                            JENIS
                        </th>

                        <th class="text-right">
                            HARGA
                        </th>

                        <th>
                            UPDATED
                        </th>

                        <th class="text-center">
                            ACTION
                        </th>

                    </tr>

                </thead>

                <tbody id="upahTableBody">

                    @forelse($upah as $index => $item)

                        <tr>

                            <td>
                                {{ $index + 1 }}
                            </td>

                            <td>
                                {{ $item->article }}
                            </td>

                            <td>
                                {{ $item->description ?? '-' }}
                            </td>

                            <td>
                                {{ $item->jenis }}
                            </td>

                            <td class="text-right">

                                Rp
                                {{ number_format(
                                    $item->harga,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>

                            <td>

                                {{ $item->updated_at
                                    ? $item->updated_at->format('d/m/Y H:i')
                                    : '-' }}

                            </td>
                         
                            <td class="text-center">
           <button type="button"
        class="btn btn-sm btn-info btn-copy-upah"
        data-id="{{ $item->id }}"
        title="Copy Item">

    <i class="fa fa-copy"></i>

</button>
                                <button type="button"
                                        class="btn btn-sm btn-warning btn-edit-upah"
                                        data-id="{{ $item->id }}"
                                        title="Edit">

                                    <i class="fa fa-edit"></i>

                                </button>


                                <button type="button"
                                        class="btn btn-sm btn-danger btn-delete-upah"
                                        data-id="{{ $item->id }}"
                                        title="Hapus">

                                    <i class="fa fa-trash"></i>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr class="empty-row">

                            <td colspan="7"
                                class="text-center py-4">

                                Belum ada data upah borongan.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- =========================================================
        MODAL TAMBAH / EDIT
    ========================================================== --}}

  <div class="modal fade"
     id="modalUpah"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-md"
         id="modalUpahDialog"
         role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title"
                    id="modalUpahTitle">
                    Tambah Upah Borongan
                </h5>

                <button type="button"
                        class="close"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

            </div>


            {{-- =====================================================
                NORMAL FORM
            ====================================================== --}}

            <form id="formUpah">

                @csrf

                <input type="hidden"
                       id="upah_id"
                       name="upah_id">


                <div class="modal-body">

                    <div id="formUpahError"
                         class="alert alert-danger d-none">
                    </div>


                    {{-- ARTICLE --}}

                    <div class="form-group">

                        <label>
                            Article Code
                            <span class="text-danger">*</span>
                        </label>

                        <div class="article-input-wrapper">

                            <div class="article-input-container">

                                <i class="fas fa-search article-search-icon"></i>

                                <input type="text"
                                       name="article"
                                       id="article"
                                       class="form-control article-input"
                                       autocomplete="off"
                                       placeholder="Cari atau ketik article code...">

                                <button type="button"
                                        id="clearArticle"
                                        class="clear-article">

                                    <i class="fas fa-times"></i>

                                </button>

                            </div>

                            <div id="articleSearchResult"
                                 class="article-search-result">
                            </div>

                        </div>

                        <div id="articleNotFound"
                             class="article-not-found d-none">

                            <i class="fas fa-exclamation-circle"></i>

                            <span>
                                Article tidak ditemukan.
                                Silahkan lanjut mengisi article code
                                dengan seksama!
                            </span>

                        </div>

                    </div>


                    {{-- DESCRIPTION --}}

                    <div class="form-group">

                        <label>
                            Description
                        </label>

                        <textarea name="description"
                                  id="description"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Masukkan description"></textarea>

                    </div>


                    {{-- JENIS --}}

                    <div class="form-group">

                        <label>
                            Jenis
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="jenis"
                               id="jenis"
                               class="form-control"
                               placeholder="Contoh: Packing">

                    </div>


                    {{-- HARGA --}}

                    <div class="form-group mb-0">

                        <label>
                            Harga
                            <span class="text-danger">*</span>
                        </label>

                        <div class="input-group">

                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    Rp
                                </span>
                            </div>

                            <input type="number"
                                   name="harga"
                                   id="harga"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   placeholder="0">

                        </div>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-outline-primary"
                            id="btnAddMass">

                        <i class="fas fa-layer-group mr-1"></i>
                        Add Mass

                    </button>

                    <div class="ml-auto">

                        <button type="button"
                                class="btn btn-secondary"
                                data-dismiss="modal">

                            Batal

                        </button>

                        <button type="submit"
                                class="btn btn-primary"
                                id="btnSimpanUpah">

                            <i class="fas fa-save"></i>
                            Simpan

                        </button>

                    </div>

                </div>

            </form>


            {{-- =====================================================
                MASS FORM
            ====================================================== --}}

            <div id="massUpahSection"
                 class="d-none">

                <div class="mass-header">

                    <div>

                        

                        <div class="text-muted small">
                            Tambahkan beberapa artikel sekaligus
                        </div>

                    </div>

                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="btnCloseMass">

                        <i class="fas fa-compress-alt mr-1"></i>
                        Form Satu

                    </button>

                </div>


                <div class="mass-table-wrapper">

                    <table class="table table-bordered mb-0"
                           id="massUpahTable">

                        <thead>

                            <tr>

                                <th style="width: 40px;">
                                    #
                                </th>

                                <th>
                                    Article Code
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Jenis
                                </th>

                                <th style="width: 160px;">
                                    Harga
                                </th>

                                <th style="width: 50px;">
                                </th>

                            </tr>

                        </thead>

                        <tbody id="massUpahBody">

                        </tbody>

                    </table>

                </div>


                <div class="mass-footer">

                    <button type="button"
                            class="btn btn-outline-primary"
                            id="btnAddMassRow">

                        <i class="fas fa-plus mr-1"></i>
                        Tambah Baris

                    </button>


                    <div class="ml-auto">

                        <button type="button"
                                class="btn btn-secondary"
                                id="btnCancelMass">

                            Batal

                        </button>

                        <button type="button"
                                class="btn btn-primary"
                                id="btnSaveMass">

                            <i class="fas fa-save mr-1"></i>
                            Simpan Semua

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


  
    {{-- =========================================================
        SCRIPT
    ========================================================== --}}

    @include('pages.upah.style')
    @include('pages.upah.script')

@endsection
