@extends('master.master')

@section('content')

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