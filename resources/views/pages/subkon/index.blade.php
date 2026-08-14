@extends('master.master')
@section('content')

    @include('pages.subkon.style.style')
    @include('pages.subkon.script.script')

    <div class="container-fluid">

        {{-- TITLE --}}
        @section('btn')
        <div class="mb-3">

            <h4 class="mb-1">
                Sub Kontrak
            </h4>

            <small class="text-muted">
                List Sub Kontrak Supplier
            </small>

        </div>
        
        @endsection



        {{-- TOOLBAR --}}
        <div class="subkon-toolbar mb-3" style="margin-top: 50px">

        {{-- ALERT --}}
        @if (session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

            <div class="subkon-search">

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
