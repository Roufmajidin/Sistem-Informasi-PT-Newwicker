@include('pages.bom2.partials.c_bom_style')


<style>
    /* =========================================================
   ADD MATERIAL MODAL
   SCROLL INTERNAL
   ========================================================= */

    #addMaterialModal .modal-dialog {
        max-height: calc(100vh - 30px);
        margin: 15px auto;
    }

    #addMaterialModal .modal-content {
        max-height: calc(100vh - 30px);
        display: flex;
        flex-direction: column;
    }

    #addMaterialModal .modal-header {
        flex-shrink: 0;
    }

    #addMaterialModal .modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        max-height: calc(100vh - 180px);
    }

    #addMaterialModal .modal-footer {
        flex-shrink: 0;
    }

    /* =========================================================
   BOM CREATE / EDIT - COMPACT ERP UI
   UI ONLY. Existing IDs/classes used by JS are preserved.
   Designed for Chrome zoom 100%.
   ========================================================= */
    #materialPickerModal .material-master-edit {
        border: 1px solid transparent;
        background: transparent;
        height: 28px;
        padding: 3px 6px;
        font-size: 9px;
        box-shadow: none;
    }

    #materialPickerModal .material-master-edit:hover {
        border-color: #d0d5dd;
        background: #fff;
    }

    #materialPickerModal .material-master-edit:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .08);
    }

    #materialPickerModal .material-price-edit {
        text-align: right;
    }

    #materialPickerModal .btn-delete-master-material {
        width: 27px;
        height: 27px;
        padding: 0;
    }

    #materialPickerModal .btn-delete-master-material i {
        font-size: 9px;
    }

    .bom-compact-page {
        --bc-primary: #2563eb;
        --bc-primary-hover: #1d4ed8;
        --bc-success: #16a34a;
        --bc-warning: #d97706;
        --bc-danger: #dc2626;
        --bc-text: #172033;
        --bc-muted: #667085;
        --bc-border: #e4e7ec;
        --bc-soft: #f8fafc;
        --bc-blue-soft: #eff6ff;
        color: var(--bc-text);
        font-size: 11px;
        padding: 5px 8px 25px;
    }

    /* ---------- TOP HEADER ---------- */

    .bom-compact-page .bom-topbar {
        min-height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 14px;
        margin-bottom: 10px;
        background: #fff;
        border: 1px solid var(--bc-border);
        border-radius: 8px;
        box-shadow: 0 1px 5px rgba(16, 24, 40, .04);
    }

    .bom-compact-page .bom-title {
        min-width: 0;
    }

    .bom-compact-page .bom-title h1 {
        margin: 0;
        font-size: 17px;
        line-height: 1.2;
        font-weight: 750;
        letter-spacing: -.02em;
    }

    .bom-compact-page .bom-title p {
        margin: 3px 0 0;
        color: var(--bc-muted);
        font-size: 9px;
    }

    .bom-compact-page .bom-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }

    .bom-compact-page .bom-actions .btn {
        height: 30px;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 6px;
        font-size: 9.5px;
        font-weight: 650;
        line-height: 28px;
    }

    /* ---------- TOP INFO GRID ---------- */

    .bom-compact-page .bom-info-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(245px, .72fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    .bom-compact-page .bom-card {
        background: #fff;
        border: 1px solid var(--bc-border);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 5px rgba(16, 24, 40, .035);
    }

    .bom-compact-page .bom-card-head {
        min-height: 45px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #edf0f4;
    }

    .bom-compact-page .bom-card-title {
        font-size: 11.5px;
        font-weight: 750;
    }

    .bom-compact-page .bom-card-subtitle {
        margin-top: 2px;
        color: #98a2b3;
        font-size: 8.5px;
    }

    /* ---------- PRODUCT TABLE ---------- */

    .bom-compact-page .bom-product-table {
        width: 100%;
        margin: 0;
        border: 0;
    }

    .bom-compact-page .bom-product-table th {
        width: 145px;
        padding: 8px 11px;
        background: #fcfcfd;
        color: #344054;
        border: 0;
        border-right: 1px solid #edf0f4;
        border-bottom: 1px solid #f0f2f5;
        font-size: 9px;
        font-weight: 700;
        vertical-align: middle;
        white-space: nowrap;
    }

    .bom-compact-page .bom-product-table td {
        padding: 7px 10px;
        border: 0;
        border-bottom: 1px solid #f0f2f5;
        vertical-align: middle;
    }

    .bom-compact-page .bom-product-table tr:last-child th,
    .bom-compact-page .bom-product-table tr:last-child td {
        border-bottom: 0;
    }

    .bom-compact-page .form-control {
        min-height: 32px;
        height: 32px;
        padding: 4px 9px;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        color: #344054;
        background: #fff;
        font-size: 10px;
        box-shadow: none !important;
    }

    .bom-compact-page .form-control:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .07) !important;
    }

    .bom-compact-page .dimension-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
    }

    .bom-compact-page .loadability-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
    }

    /* ---------- PHOTO ---------- */

    .bom-compact-page .bom-photo {
        padding: 10px;
    }

    .bom-compact-page #upload-area {
        min-height: 228px !important;
        padding: 10px !important;
        border: 1.5px dashed #cbd5e1 !important;
        border-radius: 8px !important;
        background: linear-gradient(180deg, #fbfdff, #f8fafc);
    }

    .bom-compact-page #preview {
        width: 145px !important;
        height: 145px !important;
        object-fit: contain !important;
        border-radius: 6px;
    }

    .bom-compact-page .upload-caption {
        margin-top: 5px;
        color: #667085;
        font-size: 8.5px;
    }

    .bom-compact-page .upload-hint {
        margin-top: 5px;
        color: #98a2b3;
        font-size: 8px;
    }

    /* ---------- LABOUR & MATERIAL ---------- */

    .bom-compact-page .bom-main-card {
        background: #fff;
        border: 1px solid var(--bc-border);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 5px rgba(16, 24, 40, .035);
    }

    .bom-compact-page .bom-main-head {
        min-height: 48px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        border-bottom: 1px solid #e8edf2;
        background: #fff;
    }

    .bom-compact-page .bom-main-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bom-compact-page .bom-main-title-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #eff6ff;
        color: var(--bc-primary);
        font-size: 12px;
    }

    .bom-compact-page .bom-main-title strong {
        font-size: 11.5px;
    }

    .bom-compact-page .bom-main-title small {
        display: block;
        margin-top: 2px;
        color: #98a2b3;
        font-size: 8.5px;
    }

    .bom-compact-page .bom-main-actions {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }

    .bom-compact-page .bom-main-actions .btn {
        height: 29px;
        min-height: 29px;
        padding: 0 9px;
        border-radius: 6px;
        font-size: 9px;
    }

    .bom-compact-page #bom-sections {
        padding: 8px;
        background: #f8fafc;
    }

    /* Dynamic section headers generated by the existing JS */
    .bom-compact-page #bom-sections .card {
        margin-bottom: 8px !important;
        border: 1px solid #dfe5ec !important;
        border-radius: 7px !important;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(16, 24, 40, .03);
    }

    .bom-compact-page #bom-sections .card-header {
        min-height: 38px;
        padding: 6px 9px !important;
        background: #fff !important;
        color: var(--bc-text) !important;
        border-bottom: 1px solid #edf0f4 !important;
    }

    .bom-compact-page #bom-sections .card-body {
        padding: 7px !important;
    }

    .bom-compact-page #bom-sections .table {
        margin-bottom: 0;
        font-size: 9px;
    }

    .bom-compact-page #bom-sections .table thead th {
        padding: 6px 7px;
        background: #f8fafc;
        color: #667085;
        border-color: #e8edf2;
        font-size: 8.5px;
        font-weight: 750;
        white-space: nowrap;
    }

    .bom-compact-page #bom-sections .table tbody td {
        padding: 5px 6px;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    .bom-compact-page #bom-sections .table tbody tr:hover {
        background: #fbfdff;
    }

    .bom-compact-page #bom-sections .form-control {
        min-height: 29px;
        height: 29px;
        padding: 3px 7px;
        font-size: 9px;
    }

    .bom-compact-page #bom-sections .btn {
        min-height: 27px;
        height: 27px;
        padding: 0 7px;
        border-radius: 5px;
        font-size: 8.5px;
    }

    .bom-compact-page #bom-sections .section-name {
        height: 31px;
        font-size: 9.5px;
    }

    /* ---------- COST SUMMARY ---------- */

    .bom-compact-page .bom-summary {
        margin-top: 8px;
        padding: 9px;
        border: 1px solid #e4e7ec;
        border-radius: 7px;
        background: #fff;
    }

    .bom-compact-page .bom-summary-head {
        min-height: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
    }

    .bom-compact-page .bom-summary-title {
        font-size: 10.5px;
        font-weight: 750;
    }

    .bom-compact-page .bom-summary-sub {
        margin-top: 2px;
        color: #98a2b3;
        font-size: 8px;
    }

    .bom-compact-page .bom-totals {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 8px;
    }

    .bom-compact-page .bom-total {
        padding: 7px 9px;
        border: 1px solid #e8edf2;
        border-radius: 6px;
        background: #fcfcfd;
    }

    .bom-compact-page .bom-total-label {
        color: #98a2b3;
        font-size: 8px;
    }

    .bom-compact-page .bom-total-value {
        margin-top: 2px;
        color: #172033;
        font-size: 13px;
        font-weight: 750;
    }

    .bom-compact-page #summary-body {
        font-size: 9px;
    }

    .bom-compact-page #summary-body td,
    .bom-compact-page #summary-body th {
        padding: 5px 6px;
        border-color: #edf0f4;
    }

    .bom-compact-page .bom-summary-table thead th {
        padding: 6px;
        background: #f8fafc;
        color: #667085;
        border-color: #e8edf2;
        font-size: 8.5px;
    }

    .bom-compact-page .bom-total-hpp {
        margin-top: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #e8edf2;
        background: #fbfcfe;
    }

    .bom-compact-page .bom-total-hpp label {
        margin: 0;
        color: #667085;
        font-size: 9px;
        font-weight: 750;
    }

    .bom-compact-page #total-hpp {
        width: 155px;
        text-align: right;
        font-size: 10.5px;
        font-weight: 750;
    }

    /* ---------- EMPTY STATE ---------- */

    .bom-compact-page #bom-sections:empty:after {
        content: "Belum ada header BOM. Klik Add Header untuk mulai.";
        display: block;
        padding: 25px 10px;
        text-align: center;
        color: #98a2b3;
        font-size: 9.5px;
    }

    /* ---------- MODALS ---------- */

    .bom-compact-page+.modal .modal-content {
        border: 0;
        border-radius: 9px;
        overflow: hidden;
        box-shadow: 0 18px 60px rgba(15, 23, 42, .18);
    }

    /* Modal lives outside the wrapper in this Blade, so keep selectors global
   but compact and harmless to the existing JS. */
    #materialPickerModal .modal-header,
    #addMaterialModal .modal-header {
        padding: 10px 13px;
        border-bottom: 1px solid #edf0f4;
    }

    #materialPickerModal .modal-body,
    #addMaterialModal .modal-body {
        padding: 10px 13px;
    }

    #materialPickerModal .modal-footer,
    #addMaterialModal .modal-footer {
        padding: 8px 13px;
        border-top: 1px solid #edf0f4;
    }

    #materialPickerModal .table {
        font-size: 9.5px;
    }

    #materialPickerModal .table thead th {
        padding: 6px 7px;
        background: #f8fafc;
        color: #667085;
        border-color: #e8edf2;
        font-size: 8.5px;
    }

    #materialPickerModal .table tbody td {
        padding: 5px 6px;
        border-color: #edf0f4;
        vertical-align: middle;
    }

    #searchMasterMaterial {
        height: 32px;
        font-size: 9.5px;
    }

    /* ---------- RESPONSIVE ---------- */

    @media(max-width:1000px) {
        .bom-compact-page .bom-info-grid {
            grid-template-columns: 1fr;
        }

        .bom-compact-page #upload-area {
            min-height: 210px !important;
        }
    }

    @media(max-width:700px) {
        .bom-compact-page {
            padding: 4px 4px 20px;
        }

        .bom-compact-page .bom-topbar,
        .bom-compact-page .bom-main-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .bom-compact-page .bom-actions,
        .bom-compact-page .bom-main-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .bom-compact-page .bom-actions .btn {
            flex: 0 0 auto;
        }

        .bom-compact-page .bom-product-table th {
            width: 105px;
        }

        .bom-compact-page .dimension-grid,
        .bom-compact-page .loadability-grid,
        .bom-compact-page .bom-totals {
            grid-template-columns: 1fr;
        }

        .bom-compact-page .bom-total-hpp {
            justify-content: space-between;
        }
    }
</style>


<div class="bom-compact-page">

    {{-- =====================================================
         HEADER BOM
         Semua ID tombol dipertahankan untuk JavaScript lama.
         ===================================================== --}}
    @section('btn')
    @endsection
    <div class="bom-topbar">

        <div class="bom-title">
            <h6>
                @if (isset($bom))
                    Edit BOM
                @else
                    Create BOM
                @endif
            </h6>
            {{-- 
            <p>
                Kelola informasi produk, ukuran, material, labour, dan HPP.
            </p> --}}
        </div>

        <div class="bom-actions">

            @if (!empty($bom) && isset($bom->id))
                <button type="button" class="btn btn-warning btn-sm" id="btn-update-bom">
                    <i class="fa fa-save"></i>
                    Update BOM
                </button>

                <button type="button" class="btn btn-primary btn-sm" id="btn-copy-bom">
                    <i class="fa fa-copy"></i>
                    Copy BOM
                </button>
            @else
                <button type="button" id="btn-clear-draft" class="btn btn-warning btn-sm">
                    <i class="fa fa-refresh"></i>
                    Refresh Draft
                </button>

                <button type="button" class="btn btn-primary btn-sm" id="btn-save-bom">
                    <i class="fa fa-save"></i>
                    Save BOM
                </button>
            @endif

        </div>

    </div>

    {{-- =====================================================
         INFORMASI PRODUK + FOTO
         ===================================================== --}}
    <div class="bom-info-grid">

        <div class="bom-card">

            <div class="bom-card-head">
                <div>
                    <div class="bom-card-title">
                        Informasi Produk
                    </div>

                    <div class="bom-card-subtitle">
                        Data utama item dan ukuran produk.
                    </div>
                </div>
            </div>

            <table class="table bom-product-table">

                <tr>
                    <th>ITEM</th>
                    <td>
                        <input type="text" class="form-control" name="item">
                    </td>
                </tr>

                <tr>
                    <th>ARTICLE CODE</th>
                    <td>
                        <input type="text" class="form-control" name="article_code">
                    </td>
                </tr>

                <tr>
                    <th>DIMENSION</th>
                    <td>

                        <div class="dimension-grid">

                            <input type="number" class="form-control" name="panjang" placeholder="Panjang">

                            <input type="number" class="form-control" name="lebar" placeholder="Lebar">

                            <input type="number" class="form-control" name="tinggi" placeholder="Tinggi">

                        </div>

                    </td>
                </tr>

                <tr>
                    <th>CARTON SIZE</th>
                    <td>

                        <div class="dimension-grid">

                            <input type="number" class="form-control" placeholder="Panjang" name="carton_panjang">

                            <input type="number" class="form-control" placeholder="Lebar" name="carton_lebar">

                            <input type="number" class="form-control" placeholder="Tinggi" name="carton_tinggi">

                        </div>

                    </td>
                </tr>

                <tr>
                    <th>LOADABILITY</th>
                    <td>

                        <div class="loadability-grid">

                            <input type="number" name="loadability_pcs" class="form-control" placeholder="PCS">

                            <input type="number" step="0.001" name="loadability_cbm" class="form-control"
                                placeholder="CBM">

                        </div>

                    </td>
                </tr>

            </table>

        </div>


        {{-- FOTO --}}
        <div class="bom-card">

            <div class="bom-card-head">
                <div>
                    <div class="bom-card-title">
                        Foto Produk
                    </div>

                    <div class="bom-card-subtitle">
                        Opsional · JPG, PNG, WEBP
                    </div>
                </div>
            </div>

            <div class="bom-photo">

                <div id="upload-area" class="text-center"
                    style="
                        cursor:pointer;
                        display:flex;
                        flex-direction:column;
                        justify-content:center;
                        align-items:center;
                    ">

                    <input type="file" id="bom_image" name="image" accept="image/*" hidden>

                    <img id="preview" src="https://placehold.co/300x200" class="img-fluid">

                    <div class="upload-caption">
                        Klik, Drag & Drop atau Ctrl + V
                    </div>

                    <div class="upload-hint">
                        Maksimalkan kualitas gambar agar preview tetap jelas.
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         LABOUR & MATERIAL
         ===================================================== --}}
    <div class="bom-main-card">

        <div class="bom-main-head">

            <div class="bom-main-title">

                <div class="bom-main-title-icon">
                    <i class="fa fa-cubes"></i>
                </div>

                <div>
                    <strong>Labour & Material</strong>

                    <small>
                        Susun header, material, sub harga, dan komponen BOM.
                    </small>
                </div>

            </div>

            <div class="bom-main-actions">

                <button type="button" class="btn btn-primary btn-sm" id="btn-add-header">
                    <i class="fa fa-plus"></i>
                    Add Header
                </button>

            </div>

        </div>


        <div class="p-0">

            <div id="bom-sections">
                {{-- SECTION DINAMIS DISINI --}}
            </div>


            {{-- =================================================
                 SUMMARY
                 ID lama dipertahankan:
                 #labour-total
                 #material-total-all
                 #btn-add-summary
                 #summary-body
                 #total-hpp
                 ================================================= --}}
            <div class="bom-summary">

                <div class="bom-summary-head">

                    <div>
                        <div class="bom-summary-title">
                            Cost Summary
                        </div>

                        <div class="bom-summary-sub">
                            Ringkasan labour, material, dan biaya tambahan.
                        </div>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" id="btn-add-summary">
                        <i class="fa fa-plus"></i>
                        Add Summary
                    </button>

                </div>


                <div class="bom-totals">

                    <div class="bom-total">

                        <div class="bom-total-label">
                            LABOUR
                        </div>

                        <div class="bom-total-value" id="labour-total">
                            0
                        </div>

                    </div>


                    <div class="bom-total">

                        <div class="bom-total-label">
                            MATERIAL
                        </div>

                        <div class="bom-total-value" id="material-total-all">
                            0
                        </div>

                    </div>

                </div>


                <table class="table table-bordered bom-summary-table">

                    <thead>
                        <tr>

                            <th width="20%">
                                Nama
                            </th>

                            <th width="30%">
                                Remark
                            </th>

                            <th width="10%">
                                Qty
                            </th>

                            <th width="15%">
                                Harga
                            </th>

                            <th width="15%">
                                Total
                            </th>

                            <th width="10%">
                                Action
                            </th>

                        </tr>
                    </thead>

                    <tbody id="summary-body">
                    </tbody>

                </table>


                <div class="bom-total-hpp">

                    <label>
                        TOTAL HPP
                    </label>

                    <input type="text" id="total-hpp" class="form-control" readonly value="0">

                </div>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="materialPickerModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">

                <div>
                    <h5 class="modal-title mb-0">
                        Pilih Material
                    </h5>

                    <small class="text-muted">
                        Pilih material yang akan digunakan pada BOM
                    </small>
                </div>

                <div class="d-flex align-items-center">

                    <span class="text-muted mr-2">
                        Material tidak ada?
                    </span>

                    <button type="button" class="btn btn-success btn-sm" id="btnAddMaterial">

                        <i class="fa fa-plus"></i>
                        Tambah Material

                    </button>

                    <button type="button" class="close ml-3" data-dismiss="modal">

                        <span>&times;</span>

                    </button>

                </div>

            </div>

            <div class="modal-body">
                <input type="text" id="searchMasterMaterial" class="form-control mb-3"
                    placeholder="Cari material...">
                <table class="table table-bordered" id="materialMasterTable">
                    <thead>
                        <tr>
                            <th width="80">
                                Pilih
                            </th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>harga</th>
                        </tr>
                    </thead>
                    <tbody id="materialMasterBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form id="formAddMaterial">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah Material
                    </h5>

                    <button type="button" class="close" data-dismiss="modal">

                        <span>&times;</span>

                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info mb-3">
                        Format:
                        <br>
                        <b>Nama Material,Harga,Satuan</b>
                        <br><br>

                        Contoh:
                        <br>
                        Rotan Sintetis,25000,KG
                        <br>
                        Cushion,120000,PCS
                    </div>

                    <textarea class="form-control" id="materials" name="materials" rows="8"
                        placeholder="Nama Material,Harga,Satuan"></textarea>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit" class="btn btn-success">

                        Simpan

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
{{-- <script src="https://jquery.com"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<!-- 2. Bootstrap Next -->
{{-- <script src="https://jsdelivr.net"></script> --}}

<script>
    console.time('Page Load');
</script>
<script>
    function hitungLuas() {

        let c = parseFloat($('[name="carton_panjang"]').val()) || 0;
        let d = parseFloat($('[name="carton_lebar"]').val()) || 0;
        let e = parseFloat($('[name="carton_tinggi"]').val()) || 0;

        return (
            (c / 100 * e / 100 * 2) +
            (d / 100 * e / 100 * 2) +
            (d / 100 * c / 100 * 2) +
            (0.25 * d / 100 * 4)
        );

    }

    function updateTotalHpp() {

        let labour = 0;
        let material = 0;
        let summary = 0;

        $('.sub-price-value').each(function() {

            labour += parseFloat(
                unFormat($(this).val())
            ) || 0;

        });

        $('.material-total').each(function() {

            material += parseFloat(
                unFormat($(this).val())
            ) || 0;

        });

        $('.summary-total').each(function() {

            summary += parseFloat(
                unFormat($(this).val())
            ) || 0;

        });

        let totalHpp = labour + material + summary;

        $('#total-hpp').val(
            totalHpp.toLocaleString('id-ID')
        );
        console.log($('#total-hpp').val());
    }
    // rumus loadabiity
    function updateDimensionCalculation() {

        let p = parseFloat($('[name="panjang"]').val()) || 0;
        let l = parseFloat($('[name="lebar"]').val()) || 0;
        let t = parseFloat($('[name="tinggi"]').val()) || 0;

        // Auto Carton
        let cp = p + 3;
        let cl = l + 3;
        let ct = t + 5;

        $('[name="carton_panjang"]').val(cp.toFixed(2));
        $('[name="carton_lebar"]').val(cl.toFixed(2));
        $('[name="carton_tinggi"]').val(ct.toFixed(2));

        // Hitung CBM
        let cbm = (cp * cl * ct) / 1000000;

        $('[name="loadability_cbm"]').val(cbm.toFixed(2));

        // Hitung Loadability
        if (cbm > 0) {

            let loadability = Math.round(65 / cbm);

            $('[name="loadability_pcs"]').val(loadability);

        } else {

            $('[name="loadability_pcs"]').val('');

        }

        saveDraft();
    }
    $(document).on(
        'input',
        '[name="panjang"], [name="lebar"], [name="tinggi"]',
        function() {

            updateDimensionCalculation();

        }
    );

    function formatNumber(value) {

        if (value === '' || value === null || value === undefined) {
            return '';
        }

        return Number(unFormat(value)).toLocaleString('id-ID');
    }

    function unFormat(value) {

        if (value === null || value === undefined || value === '') {
            return 0;
        }

        value = value.toString().trim();

        // Kalau ada koma berarti format Indonesia
        if (value.includes(',')) {
            value = value.replace(/\./g, '').replace(',', '.');
            return parseFloat(value) || 0;
        }

        // Kalau titik diikuti tepat 3 digit di akhir → separator ribuan
        if (/\.\d{3}$/.test(value)) {
            value = value.replace(/\./g, '');
            return parseFloat(value) || 0;
        }

        // Selain itu anggap titik adalah desimal
        return parseFloat(value) || 0;
    }
</script>
<script>
    let activeMaterialInput = null;
    let sectionIndex = 0;

    /* =========================================================
       MATERIAL MASTER AJAX - GLOBAL
       ========================================================= */
    window.loadMaterialMaster = function(keyword = '') {

        console.log('loadMaterialMaster:', keyword);

        $.ajax({

            url: '/cog-material-price/ajax',

            type: 'GET',

            data: {
                keyword: keyword
            },

            beforeSend: function() {

                $('#materialMasterBody').html(`
                <tr>
                    <td colspan="6"
                        class="text-center"
                        style="padding:20px;color:#64748b;">

                        <i class="fa fa-spinner fa-spin"></i>
                        Mencari material...

                    </td>
                </tr>
            `);

            },

            success: function(datas) {

                let html = '';

                if (!Array.isArray(datas)) {
                    datas = [];
                }

                if (datas.length === 0) {

                    $('#materialMasterBody').html(`
                    <tr>
                        <td colspan="6"
                            class="text-center"
                            style="padding:20px;color:#94a3b8;">

                            Material tidak ditemukan.

                        </td>
                    </tr>
                `);

                    return;
                }

                datas.forEach(function(item) {

                    const id =
                        item.id || '';

                    const type =
                        item.type || '';

                    const name =
                        item.name || '';

                    const jenis =
                        item.jenis || '';

                    const price =
                        Number(item.price || 0);

                    const unit =
                        item.unit || '';

                    const isMaterial =
                        type === 'material_price';


                    html += `

                    <tr
                        data-id="${id}"
                        data-type="${escapeHtml(type)}"
                    >

                        <!-- PILIH -->
                        <td>

                            <button
                                type="button"

                                class="
                                    btn
                                    btn-primary
                                    btn-sm
                                    btn-select-material
                                "

                                data-id="${id}"

                                data-name="${escapeHtml(name)}"

                                data-type="${escapeHtml(type)}"

                                data-price="${price}"

                                data-unit="${escapeHtml(unit)}"
                            >

                                Pilih

                            </button>

                        </td>


                        <!-- ID -->
                        <td>
                            ${id}
                        </td>


                        <!-- NAMA -->
                        <td>

                            ${
                                isMaterial

                                ?

                                `
                                    <input
                                        type="text"

                                        class="
                                            form-control
                                            material-master-edit
                                            material-name-edit
                                        "

                                        value="${escapeHtml(name)}"

                                        data-id="${id}"

                                        data-field="nama_material"

                                        data-original="${escapeHtml(name)}"
                                    >

                                    <div
                                        class="
                                            material-save-message-container
                                        "
                                    ></div>
                                `

                                :

                                escapeHtml(name)
                            }

                        </td>


                        <!-- JENIS -->
                        <td>
                            ${escapeHtml(jenis)}
                        </td>


                        <!-- HARGA -->
                        <td>

                            ${
                                isMaterial

                                ?

                                `
                                    <input
                                        type="text"

                                        class="
                                            form-control
                                            material-master-edit
                                            material-price-edit
                                        "

                                        value="${formatNumber(price)}"

                                        data-id="${id}"

                                        data-field="harga"

                                        data-original="${price}"
                                    >

                                    <div
                                        class="
                                            material-save-message-container
                                        "
                                    ></div>
                                `

                                :

                                formatNumber(price)
                            }

                        </td>


                        <!-- DELETE -->
                        <td class="text-center">

                            ${
                                isMaterial

                                ?

                                `
                                    <button
                                        type="button"

                                        class="
                                            btn
                                            btn-danger
                                            btn-sm
                                            btn-delete-master-material
                                        "

                                        data-id="${id}"

                                        title="Hapus material"
                                    >

                                        <i
                                            class="fa fa-trash"
                                        ></i>

                                    </button>
                                `

                                :

                                ''
                            }

                        </td>

                    </tr>

                `;

                });


                $('#materialMasterBody')
                    .html(html);

            },


            error: function(xhr) {

                console.error(
                    'LOAD MATERIAL ERROR:',
                    xhr.responseText
                );

                $('#materialMasterBody').html(`
                <tr>
                    <td
                        colspan="6"
                        class="text-center text-danger"
                        style="padding:20px;"
                    >

                        <i class="fa fa-exclamation-circle"></i>

                        Gagal mengambil data material.

                    </td>
                </tr>
            `);

            }

        });

    };

    {{-- let activeMaterialInput = null; --}}

    // pili material dari modal
    $(document).on('click', '.btn-select-material', function() {

        if (!activeMaterialInput) {
            console.error('activeMaterialInput null');
            return;
        }

        let btn = $(this);

        let id = btn.data('id');
        let nama = btn.data('name');
        let type = btn.data('type');
        let price = btn.data('price') || 0;
        let unit = btn.data('unit') || '';

        let row = activeMaterialInput.closest('tr');

        activeMaterialInput.val(nama);

        row.find('.material-id').val(id);
        row.find('.material-type').val(type);
        row.find('.material-price').val(formatNumber(price));
        row.find('.unit').val(unit);

        calculateRow(row);
        updateTotalHpp();

        // pindahkan fokus ke input
        activeMaterialInput.trigger('focus');

        // baru tutup modal
        $('#materialPickerModal').modal('hide');

    });

    function calculateRow(row) {

        let qty = parseFloat(
            row.find('.qty').val()
        ) || 0;

        let price = unFormat(
            row.find('.material-price').val()
        );

        // console.log({
        //     material: row.find('.material-picker').val(),
        //     qty,
        //     price,
        //     raw: row.find('.material-price').val()
        // });

        let total = qty * price;

        row.find('.material-total').val(
            total.toLocaleString('id-ID')
        );

    }
    // search material di modal
    /* =========================================================
     SEARCH MATERIAL MASTER
     AJAX
     ========================================================= */


    $(document).on(
        'input',
        '#searchMasterMaterial',
        function() {

            const keyword =
                $(this).val().trim();

            clearTimeout(
                materialSearchTimer
            );

            materialSearchTimer =
                setTimeout(function() {

                    window.loadMaterialMaster(
                        keyword
                    );

                }, 250);

        }
    ); // ADD HEADER
    $('#btn-add-header').click(function() {
        sectionIndex++;
        let html = `
    <div class="bom-section card mb-3">

    <div class="card-header bg-success text-white">

        <div class="row align-items-center">

            <div class="col-auto">
                <i class="fa fa-bars drag-header"
                    style="cursor:grab;font-size:18px;"></i>
            </div>

            <div class="col">
                <input
                    type="text"
                    class="form-control section-name"
                    placeholder="Nama Header"
                    value="RANGKA ROTAN">
            </div>

            <div class="col-auto text-right">

                <button
                    type="button"
                    class="btn btn-primary btn-sm btn-add-child">
                    Add Child
                </button>

                <button
                    type="button"
                    class="btn btn-success btn-sm btn-add-sub-price">
                    Add Sub Harga
                </button>

                <button
                    type="button"
                    class="btn btn-danger btn-sm btn-remove-header">
                    Delete Header
                </button>

            </div>

        </div>

    </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="35%">Material</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Satuan</th>
                            <th width="10%">Harga</th>
                            <th width="10%">Total</th>
                            <th width="20%">Keterangan</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody class="child-body">
                    </tbody>

                    <tbody class="sub-price-body">
                    </tbody>
                </table>


            </div>
        </div>
        </div>
    `;
        $('#bom-sections').append(html);
        initSortable();
        saveDraft();
    });
    // REMOVE HEADER
    $(document).on(
        'click',
        '.btn-remove-header',
        function() {
            $(this)
                .closest('.bom-section')
                .remove();
            updateSummary();
            saveDraft();
        });
    // ADD CHILD
    $(document).on(
        'click',
        '.btn-add-child',
        function() {
            let tbody = $(this)
                .closest('.bom-section')
                .find('.child-body');
            let row = `
<tr>
    <td>
        <input
            type="hidden"
            class="material-id">
        <input
            type="hidden"
            class="material-type">
        <input
            type="text"
            readonly
            class="form-control material-picker"
            placeholder="Klik untuk pilih material">
    </td>

    <td>
        <input
            type="number"
            step="0.0001"
            class="form-control qty"
            placeholder="Qty">
    </td>

   <td>
    <input
        type="text"
        class="form-control unit"
        placeholder="Kg / pcs / m3">
</td>

<td>

    <input
        type="text"
        class="form-control material-price"
        value="0">

</td>

<td>

    <input
        type="text"
        readonly
        class="form-control material-total"
        value="0">

</td>
  <td>
        <input
            type="text"
            class="form-control specification"
            placeholder="keterangan">
    </td>
<td>
    <button
        type="button"
        class="btn btn-danger btn-sm btn-remove-child">
        Delete
    </button>
</td>
</tr>
`;
            tbody.append(row);
            updateSummary();

            saveDraft();
        });
    // add sub harga
    $(document).on(
        'click',
        '.btn-add-sub-price',
        function() {

            let tbody = $(this)
                .closest('.bom-section')
                .find('.sub-price-body');

            tbody.append(`
            <tr class="table-success sub-price-row">

                <td colspan="5">
                    <input
                        type="text"
                        class="form-control sub-price-name"
                        placeholder="Contoh : JASA + ANYAM Pak Sumantri">
                </td>

                <td>
                    <input
                        type="text"
                        class="form-control sub-price-value"
                        value="0">
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm btn-remove-sub-price">
                        Delete
                    </button>
                </td>

            </tr>
        `);

            saveDraft();

        }
    );
    // REMOVE CHILD
    $(document).on(
        'click',

        '.btn-remove-child',
        function() {
            $(this)
                .closest('tr')
                .remove();
        });
    updateSummary();
    $(document).on(
        'click',
        '.btn-remove-sub-price',
        function() {

            $(this)
                .closest('tr')
                .remove();
            updateSummary();
            saveDraft();

        }
    );
    $(document).on('blur', '.material-price', function() {

        let angka = unFormat($(this).val());

        $(this).val(
            formatNumber(angka)
        );

        calculateRow($(this).closest('tr'));

        updateSummary();
        updateTotalHpp();
        saveDraft();

    });
    $(document).on('blur', '.sub-price-value', function() {

        let angka = unFormat($(this).val());

        $(this).val(formatNumber(angka));

        updateSummary();
        updateTotalHpp();
        saveDraft();

    }); // save bom
    $('#btn-save-bom').click(function() {

        let formData = new FormData();

        formData.append(
            '_token',
            '{{ csrf_token() }}'
        );

        formData.append(
            'bom',
            JSON.stringify(
                collectBomData()
            )
        );

        let image =
            $('#bom_image')[0]
            .files[0];

        if (image) {

            formData.append(
                'image',
                image
            );

        }

        $.ajax({

            url: "/bom-produksi/store",

            type: 'POST',

            data: formData,

            processData: false,

            contentType: false,

            success: function(res) {

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Saved BOM, Duar..',
                    showConfirmButton: false,
                    timer: 500,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });

            }

        });

    });
    // image
    function loadImage(file) {

        if (!file) return;

        let reader = new FileReader();

        reader.onload = function(e) {

            $('#preview').attr('src', e.target.result);

        };

        reader.readAsDataURL(file);

        // Supaya file tetap ikut saat submit FormData
        let dt = new DataTransfer();
        dt.items.add(file);

        $('#bom_image')[0].files = dt.files;

    }
    $('#upload-area').on('click', function() {

        $('#bom_image').click();

    });
    $('#bom_image').on('change', function() {

        if (this.files.length) {

            loadImage(this.files[0]);

        }

    });
    $('#upload-area')

        .on('dragover', function(e) {

            e.preventDefault();

            $(this).css('border-color', '#28a745');

        })

        .on('dragleave', function() {

            $(this).css('border-color', '#cfd8dc');

        })

        .on('drop', function(e) {

            e.preventDefault();

            $(this).css('border-color', '#cfd8dc');

            let file = e.originalEvent.dataTransfer.files[0];

            if (file) {

                loadImage(file);

            }

        });
    $(document).on('paste', function(e) {

        let clipboardData = e.originalEvent.clipboardData || window.clipboardData;

        if (!clipboardData) return;

        let items = clipboardData.items;

        if (!items) return;

        for (let i = 0; i < items.length; i++) {

            let item = items[i];

            if (item.kind === 'file' && item.type.startsWith('image/')) {

                let file = item.getAsFile();

                if (!file) continue;

                loadImage(file);

                e.preventDefault();

                return;
            }
        }

    });

    function saveDraft() {
        let draft = collectBomData();
        localStorage.setItem(
            'bom_draft',
            JSON.stringify(draft)
        );
    }
    $(document).on(
        'input change',
        '.qty, .material-price',

        function() {

            let row = $(this).closest('tr');

            calculateRow(row);
            updateSummary();
            saveDraft();

        }
    );
    $(document).on(
        'keyup change',
        '.qty, .material-price',
        function() {

            calculateRow(
                $(this).closest('tr')
            );
            updateTotalHpp();
            updateSummary();
        }
    );

    function renderDraft(draft) {
        // isi pertama
        $('[name="item"]').val(draft.name || '');

        $('[name="article_code"]').val(draft.article_number || '');

        $('[name="panjang"]').val(draft.panjang || '');
        $('[name="lebar"]').val(draft.lebar || '');
        $('[name="tinggi"]').val(draft.tinggi || '');

        $('[name="carton_panjang"]').val(draft.carton_panjang || '');
        $('[name="carton_lebar"]').val(draft.carton_lebar || '');
        $('[name="carton_tinggi"]').val(draft.carton_tinggi || '');

        $('[name="loadability_pcs"]').val(draft.loadability_pcs || '');
        $('[name="loadability_cbm"]').val(draft.loadability_cbm || '');
        $('#bom-sections').html('');

        draft.groups.forEach(function(group) {

            let html = `
        <div class="bom-section card mb-3">

        <div class="card-header bg-success text-white">

    <div class="row align-items-center">

        <div class="col-auto">
            <i class="fa fa-bars drag-header"
               style="cursor:grab;font-size:18px;"></i>
        </div>

        <div class="col">

            <input
                type="text"
                class="form-control section-name"
                value="${group.name}">

        </div>

        <div class="col-auto text-right">

            <button
                type="button"
                class="btn btn-primary btn-sm btn-add-child">
                Add Child
            </button>

            <button
                type="button"
                class="btn btn-success btn-sm btn-add-sub-price">
                Add Sub Harga
            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm btn-remove-header">
                Delete Header
            </button>

        </div>

    </div>

</div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th width="35%">Material</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Satuan</th>
                            <th width="10%">Harga</th>
                            <th width="10%">Total</th>
                            <th width="20%">Keterangan</th>

                            <th width="10%">Action</th>

                        </tr>

                    </thead>

                    <tbody class="child-body"></tbody>

                    <tbody class="sub-price-body"></tbody>

                </table>

            </div>

        </div>
        `;

            $('#bom-sections').append(html);

            let section = $('#bom-sections .bom-section').last();

            let childBody = section.find('.child-body');

            let subBody = section.find('.sub-price-body');

            // ==========================
            // MATERIAL
            // ==========================
            (group.items || []).forEach(function(item) {

                childBody.append(`
<tr>

    <td>

        <input
            type="hidden"
            class="material-id"
            value="${item.material_id || ''}">

        <input
            type="hidden"
            class="material-type"
            value="${item.material_type || ''}">

        <input
            type="text"
            readonly
            class="form-control material-picker"
            value="${item.name || ''}">

    </td>
    <td>

        <input
            type="number"
            step="0.0001"
            class="form-control qty"
            value="${item.qty || ''}">

    </td>

    <td>

        <input
            type="text"
            class="form-control unit"
            value="${item.unit || ''}">

    </td>

    <td>

      <input
    type="text"
    class="form-control material-price"
    value="${formatNumber(item.price ?? 0)}">

    </td>

    <td>

       <input
        type="text"
        readonly
        class="form-control material-total"
        value="${Number(item.total || 0).toLocaleString('id-ID')}">

    </td>

    <td>

        <input
            type="text"
            class="form-control specification"
            value="${item.notes || ''}">

    </td>
    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-child">

            Delete

        </button>

    </td>

</tr>
            `);

            });

            // ==========================
            // SUB HARGA
            // ==========================
            (group.sub_prices || []).forEach(function(sub) {

                subBody.append(`
<tr class="table-success sub-price-row">

    <td colspan="5">

        <input
            type="text"
            class="form-control sub-price-name"
            value="${sub.name || ''}"
            placeholder="Contoh : JASA + ANYAM Pak Sumantri">

    </td>

    <td>

      <input
    type="text"
    class="form-control sub-price-value"
    value="${Number(sub.price || 0).toLocaleString('id-ID')}">

    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-sub-price">

            Delete

        </button>

    </td>

</tr>
            `);

            });

        });
        updateDimensionCalculation();
        $('#summary-body').html('');

        (draft.summaries || []).forEach(function(summary) {

            $('#summary-body').append(`
<tr class="summary-row">

    <td>

        <input
            type="text"
            class="form-control summary-name"
            value="${summary.name || ''}">

    </td>

    <td>

        <input
            type="text"
            class="form-control summary-remark"
            value="${summary.remark || ''}">

    </td>

    <td>

        <input
            type="number"
            class="form-control summary-qty"
            value="${summary.qty || 1}">

    </td>

    <td>

        <input
            type="text"
            class="form-control summary-price"
            value="${Number(summary.price || 0).toLocaleString('id-ID')}">

    </td>

    <td>

        <input
            type="text"
            readonly
            class="form-control summary-total"
            value="${Number(summary.total || 0).toLocaleString('id-ID')}">

    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">

            Delete

        </button>

    </td>

</tr>
`);

        });


        $('.child-body tr').each(function() {

            calculateRow($(this));

        });
        updateTotalHpp(); // <-- di sini
        updateSummary();

    }


    function collectBomData() {

        let groups = [];

        $('.bom-section').each(function() {

            let group = {

                name: $(this)
                    .find('.section-name')
                    .val(),

                items: [],

                sub_prices: []

            };

            // ==========================
            // MATERIAL
            // ==========================
            $(this)
                .find('.child-body tr')
                .each(function() {

                    group.items.push({

                        material_id: $(this)
                            .find('.material-id')
                            .val(),

                        material_type: $(this)
                            .find('.material-type')
                            .val(),

                        name: $(this)
                            .find('.material-picker')
                            .val(),

                        qty: $(this)
                            .find('.qty')
                            .val(),

                        price: $(this)
                            .find('.material-price')
                            .val()
                            .replace(/\./g, ''),

                        unit: $(this)
                            .find('.unit')
                            .val(),

                        total: $(this)
                            .find('.material-total')
                            .val()
                            .replace(/\./g, ''),

                        notes: $(this)
                            .find('.specification')
                            .val()

                    });

                });

            // ==========================
            // SUB HARGA
            // ==========================
            $(this)
                .find('.sub-price-body tr')
                .each(function() {

                    group.sub_prices.push({

                        name: $(this)
                            .find('.sub-price-name')
                            .val(),

                        price: unFormat(
                            $(this)
                            .find('.sub-price-value')
                            .val()
                        )
                    });

                });

            groups.push(group);

        });

        let summaries = [];
        $('#summary-body tr').each(function() {

            summaries.push({

                name: $(this)
                    .find('.summary-name')
                    .val(),

                remark: $(this)
                    .find('.summary-remark')
                    .val(),

                qty: $(this)
                    .find('.summary-qty')
                    .val(),

                price: unFormat(
                    $(this)
                    .find('.summary-price')
                    .val()
                ),

                total: unFormat(
                    $(this)
                    .find('.summary-total')
                    .val()
                ),

            });

        });
        return {

            name: $('[name="item"]').val(),

            article_number: $('[name="article_code"]').val(),

            panjang: $('[name="panjang"]').val(),

            lebar: $('[name="lebar"]').val(),

            tinggi: $('[name="tinggi"]').val(),

            carton_panjang: $('[name="carton_panjang"]').val(),

            carton_lebar: $('[name="carton_lebar"]').val(),

            carton_tinggi: $('[name="carton_tinggi"]').val(),

            loadability_pcs: $('[name="loadability_pcs"]').val(),

            loadability_cbm: $('[name="loadability_cbm"]').val(),

            groups: groups,
            summaries: summaries

        };

    }
    // init
    function initSortable() {

        const el = document.getElementById('bom-sections');

        if (!el) return;

        if (el.sortableInstance) {
            el.sortableInstance.destroy();
        }

        el.sortableInstance = new Sortable(el, {
            animation: 150,
            ghostClass: 'bg-warning',
            handle: '.drag-header',

            onEnd() {
                saveDraft();
            }
        });

    }
    $(document).ready(function() {
        initSortable();
        let draft = localStorage.getItem('bom_draft');
        if (!draft) {
            return;
        }
        draft = JSON.parse(draft);
        // console.log('draft loaded', draft);
        renderDraft(draft);

    });

    // edit bom
    $(document).on(
        'click',
        '#btn-update-bom',
        function() {

            let formData =
                new FormData();

            formData.append(
                '_token',
                '{{ csrf_token() }}'
            );

            formData.append(
                'bom',
                JSON.stringify(
                    collectBomData()
                )
            );

            let image =
                $('#bom_image')[0]
                .files[0];

            if (image) {

                formData.append(
                    'image',
                    image
                );

            }

            $.ajax({

                url: '/bom-produksi/update/' + bomId,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function(res) {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'pantun dulu, jalan jalan ke bekasi.. cakepp',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });

                },

                error: function(xhr) {

                    console.log(
                        xhr.responseText
                    );

                }

            });

        });

    function updateSummary() {

        let labour = 0;
        let material = 0;

        $('.sub-price-value').each(function() {

            labour += parseFloat(
                unFormat($(this).val())
            ) || 0;

        });

        $('.material-total').each(function() {

            material += parseFloat(
                unFormat($(this).val())
            ) || 0;

        });

        $('#labour-total').text(
            labour.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        );

        $('#material-total-all').text(
            material.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        );

    }
    // add summary
    $('#summary-body').append(`
<tr class="summary-row">

    <td>
        <input
            type="text"
            class="form-control summary-name"
            placeholder="LABOUR / MATERIAL">
    </td>

    <td>
        <input
            type="text"
            class="form-control summary-remark"
            placeholder="Remark">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-qty"
            value="1">
    </td>

    <td>
        <input
            type="text"
            class="form-control summary-price"
            value="0">
    </td>

    <td>
        <input
            type="text"
            readonly
            class="form-control summary-total"
            value="0">
    </td>

    <td>
        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">
            Delete
        </button>
    </td>

</tr>
`);
    $(document).on(
        'input',
        '.summary-qty, .summary-price',
        function() {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.summary-qty').val()) || 0;

            let price = parseFloat(
                unFormat(
                    row.find('.summary-price').val()
                )
            ) || 0;

            let total = qty * price;

            row.find('.summary-total').val(
                formatNumber(total)
            );
            updateTotalHpp();
            saveDraft();

        }
    );
    $(document).on('click', '.btn-remove-summary', function() {

        $(this).closest('tr').remove();

        updateTotalHpp();
        saveDraft();

    });
    $(document).on(
        'click',
        '#btn-add-summary',
        function() {

            $('#summary-body').append(`
<tr class="summary-row">

    <td>
        <input
            type="text"
            class="form-control summary-name">
    </td>

    <td>
        <input
            type="text"
            class="form-control summary-remark">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-qty"
            value="1">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-price"
            value="0">
    </td>

    <td>
        <input
            type="number"
            readonly
            class="form-control summary-total"
            value="0">
    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">

            Delete

        </button>

    </td>

</tr>
`);

            saveDraft();

        }
    );
</script>
<script>
    $('#bom_image').on(
        'change',
        function(e) {

            let file =
                e.target.files[0];

            if (!file) {
                return;
            }

            let reader =
                new FileReader();

            reader.onload =
                function(event) {

                    $('#preview').attr(
                        'src',
                        event.target.result
                    );

                };

            reader.readAsDataURL(
                file
            );

        }
    );
    // hitung box
    function hitungRemark() {

        let c = parseFloat($('[name="carton_panjang"]').val()) || 0;
        let d = parseFloat($('[name="carton_lebar"]').val()) || 0;
        let e = parseFloat($('[name="carton_tinggi"]').val()) || 0;

        return (
            (c / 100 * e / 100 * 2) +
            (d / 100 * e / 100 * 2) +
            (d / 100 * c / 100 * 2) +
            (0.25 * d / 100 * 4)
        );

    }

    $(document).on('change', '.summary-remark', function() {

        let remark = $(this).val().trim().toLowerCase();

        if (remark === 'hitung') {

            let hasil = hitungRemark();

            $(this).val(hasil.toFixed(3)); // ganti tulisan "hitung" menjadi angka

            saveDraft();

        }

    });
    $(document).on(
        'input',
        '.summary-remark, .summary-qty',
        function() {

            let row = $(this).closest('tr');

            let remark = row.find('.summary-remark').val().toLowerCase().trim();

            let qty = parseFloat(
                row.find('.summary-qty').val()
            ) || 0;

            let price = 0;

            if (remark === 'hitung') {

                price = hitungLuas();

                row.find('.summary-price').val(
                    formatNumber(Math.round(price))
                );

            } else {

                price = parseFloat(
                    unFormat(
                        row.find('.summary-price').val()
                    )
                ) || 0;

            }

            let total = qty * price;

            row.find('.summary-total').val(
                formatNumber(Math.round(total))
            );

            updateTotalHpp();
            saveDraft();

        }
    );

    // $(document).on('input', '.material-price', function () {

    //     calculateRow($(this).closest('tr'));

    //     updateSummary();

    //     updateTotalHpp();

    // });
    $(document).on('blur', '.material-price', function() {

        let angka = unFormat($(this).val());

        $(this).val(
            angka.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 4
            })
        );

        calculateRow($(this).closest('tr'));
        updateSummary();
        updateTotalHpp();
    })
    $(document).on('blur', '.material-price', function() {

        saveDraft();

    });
    $(document).on('input', '.summary-price', function() {

        let row = $(this).closest('tr');

        let qty = parseFloat(row.find('.summary-qty').val()) || 0;

        let price = unFormat($(this).val());

        row.find('.summary-total').val(
            formatNumber(qty * price)
        );

        updateTotalHpp();
        saveDraft();

    });
    $(document).on('click', '#btn-copy-bom', function() {

        Swal.fire({

            title: 'Copy BOM?',
            text: 'BOM ini akan diduplikasi menjadi BOM baru.',
            icon: 'question',

            showCancelButton: true,

            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',

            confirmButtonText: 'Ya, Copy',
            cancelButtonText: 'Batal'

        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            let formData = new FormData();

            formData.append('_token', '{{ csrf_token() }}');

            formData.append(
                'bom',
                JSON.stringify(collectBomData())
            );

            let image = $('#bom_image')[0].files[0];

            if (image) {
                formData.append('image', image);
            }

            $.ajax({

                url: "/bom-produksi/bom/copy",

                type: "POST",

                data: formData,

                processData: false,

                contentType: false,

                beforeSend: function() {

                    Swal.fire({

                        title: 'Sedang menyalin...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,

                        didOpen: () => {
                            Swal.showLoading();
                        }

                    });

                },

                success: function(res) {

                    Swal.fire({

                        icon: "success",

                        title: "Berhasil",

                        text: res.message,

                        confirmButtonText: "OK"

                    }).then(() => {

                        window.location = "/bom-produksi/";

                    });

                },

                error: function(xhr) {

                    Swal.fire({

                        icon: "error",

                        title: "Gagal",

                        text: xhr.responseJSON?.message ?? "Terjadi kesalahan."

                    });

                }

            });

        });

    });
    $('#btn-clear-draft').on('click', function() {

        Swal.fire({
            title: 'Buat BOM baru?',
            text: 'Semua draft yang belum disimpan akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Draft',
            cancelButtonText: 'Batal'

        }).then((result) => {

            if (!result.isConfirmed) return;

            localStorage.removeItem('bom_draft');

            location.reload();

        });

    });
    /* =========================================================
       MATERIAL MASTER AJAX
       ========================================================= */

    /* =========================================================
       MATERIAL MASTER AJAX
       GLOBAL
       ========================================================= */



    /* =========================================================
       ESCAPE HTML
       ========================================================= */

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    /* =========================================================
       ALERT INLINE DI BAWAH INPUT
       ========================================================= */

    function showMaterialSaveMessage(input, message, type = 'success') {

        const td = input.closest('td');
        const container = td.find('.material-save-message-container');

        container.empty();

        let icon = 'fa-check-circle';

        if (type === 'error') {
            icon = 'fa-exclamation-circle';
        }

        if (type === 'loading') {
            icon = 'fa-spinner fa-spin';
        }

        container.html(`
        <div class="material-save-message ${type}">
            <i class="fa ${icon}"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

        if (type === 'success' || type === 'error') {
            setTimeout(function() {
                container.find('.material-save-message').fadeOut(200, function() {
                    $(this).remove();
                });
            }, 1800);
        }
    }


    /* =========================================================
       OPEN MATERIAL PICKER
       ========================================================= */

    $(document).on('click', '.material-picker', function() {

        activeMaterialInput = $(this);

        $('#searchMasterMaterial').val('');

        window.loadMaterialMaster('');

        $('#materialPickerModal').modal('show');

        $('#materialPickerModal').one('shown.bs.modal', function() {
            $('#searchMasterMaterial')
                .trigger('focus')
                .select();
        });
    });


    /* =========================================================
       SEARCH MATERIAL MASTER
       ========================================================= */

    let materialSearchTimer = null;

    $(document).on('input', '#searchMasterMaterial', function() {

        const keyword = $(this).val().trim();

        clearTimeout(materialSearchTimer);

        materialSearchTimer = setTimeout(function() {
            window.loadMaterialMaster(keyword);
        }, 250);
    });


    /* =========================================================
       PILIH MATERIAL
       ========================================================= */

    $(document).on('click', '.btn-select-material', function() {

        if (!activeMaterialInput) {
            console.error('activeMaterialInput null');
            return;
        }

        const btn = $(this);

        const id = btn.data('id');
        const nama = btn.data('name');
        const type = btn.data('type');
        const price = btn.data('price') || 0;
        const unit = btn.data('unit') || '';

        const row = activeMaterialInput.closest('tr');

        activeMaterialInput.val(nama);

        row.find('.material-id').val(id);
        row.find('.material-type').val(type);
        row.find('.material-price').val(formatNumber(price));
        row.find('.unit').val(unit);

        calculateRow(row);
        updateTotalHpp();

        activeMaterialInput.trigger('focus');

        $('#materialPickerModal').modal('hide');
    });


    /* =========================================================
       EDIT MATERIAL MASTER
       ENTER = SAVE
       ========================================================= */

    $(document).on('keydown', '.material-master-edit', function(e) {

        if (e.key !== 'Enter') {
            return;
        }

        e.preventDefault();

        const input = $(this);
        const row = input.closest('tr');
        const id = input.data('id');
        const field = input.data('field');

        let value = input.val().trim();
        const original = input.attr('data-original');

        if (String(value) === String(original)) {
            input.blur();
            return;
        }

        let nama = row.find('[data-field="nama_material"]').val() || '';
        let harga = row.find('[data-field="harga"]').val() || '0';

        if (field === 'nama_material') {
            nama = value;
        }

        if (field === 'harga') {
            harga = unFormat(value);
        }

        harga = unFormat(harga);

        const satuan = row.find('.material-unit-edit').val() || '';

        input.prop('disabled', true);

        showMaterialSaveMessage(input, 'Menyimpan...', 'loading');

        $.ajax({

            url: '/cog-material-price/update/' + id,

            type: 'POST',

            data: {
                _token: '{{ csrf_token() }}',
                nama_material: nama,
                harga: harga,
                satuan: satuan
            },

            success: function(res) {

                if (!res || !res.success) {

                    showMaterialSaveMessage(
                        input,
                        res?.message || 'Gagal menyimpan material.',
                        'error'
                    );

                    input.prop('disabled', false);
                    return;
                }

                const material = res.material || {};

                const savedName = material.nama_material ?? nama;
                const savedPrice = material.harga ?? harga;

                /* Update input yang diedit */
                if (field === 'nama_material') {

                    input.val(savedName);

                    input.attr(
                        'data-original',
                        savedName
                    );
                }

                if (field === 'harga') {

                    input.val(
                        formatNumber(savedPrice)
                    );

                    input.attr(
                        'data-original',
                        savedPrice
                    );
                }

                /* Update button Pilih */
                const selectButton =
                    row.find('.btn-select-material');

                if (selectButton.length) {

                    selectButton.attr(
                        'data-name',
                        savedName
                    );

                    selectButton.attr(
                        'data-price',
                        savedPrice
                    );

                    selectButton.data(
                        'name',
                        savedName
                    );

                    selectButton.data(
                        'price',
                        savedPrice
                    );
                }

                /* Update row BOM yang memakai material ini */
                $('.child-body tr').each(function() {

                    const bomRow = $(this);

                    const materialId =
                        bomRow.find('.material-id').val();

                    if (String(materialId) !== String(id)) {
                        return;
                    }

                    if (field === 'nama_material') {
                        bomRow
                            .find('.material-picker')
                            .val(savedName);
                    }

                    if (field === 'harga') {

                        bomRow
                            .find('.material-price')
                            .val(formatNumber(savedPrice));

                        calculateRow(bomRow);
                    }
                });

                updateSummary();
                updateTotalHpp();

                input.prop('disabled', false);

                showMaterialSaveMessage(
                    input,
                    'Berhasil disimpan',
                    'success'
                );
            },

            error: function(xhr) {

                console.error(
                    'UPDATE MATERIAL ERROR:',
                    xhr.responseText
                );

                showMaterialSaveMessage(
                    input,
                    xhr.responseJSON?.message ||
                    'Gagal menyimpan material.',
                    'error'
                );

                input.prop('disabled', false);
            }
        });
    });


    /* =========================================================
       DELETE MATERIAL MASTER
       ========================================================= */

    $(document).on('click', '.btn-delete-master-material', function() {

        const btn = $(this);
        const id = btn.data('id');
        const row = btn.closest('tr');

        const nama =
            row.find('.material-name-edit').val() ||
            'material';

        Swal.fire({

            title: 'Hapus material?',

            text: `"${nama}" akan dihapus dari master material.`,

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Ya, Hapus',

            cancelButtonText: 'Batal',

            confirmButtonColor: '#dc2626'

        }).then(function(result) {

            if (!result.isConfirmed) {
                return;
            }

            $.ajax({

                url: '/cog-material-price/delete/' + id,

                type: 'DELETE',

                data: {
                    _token: '{{ csrf_token() }}'
                },

                success: function(res) {

                    if (!res || !res.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res?.message ||
                                'Material gagal dihapus.'
                        });

                        return;
                    }

                    row.fadeOut(180, function() {
                        $(this).remove();
                    });
                },

                error: function(xhr) {

                    console.error(
                        'DELETE MATERIAL ERROR:',
                        xhr.responseText
                    );

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message ||
                            'Material gagal dihapus.'
                    });
                }
            });
        });
    });
    //add material 
    $('#btnAddMaterial').click(function() {

        $('#addMaterialModal').modal('show');

    });
    // save material 
    $('#formAddMaterial').submit(function(e) {

        e.preventDefault();

        $.ajax({

            url: '/cog-material-price/store',

            type: 'POST',

            data: $(this).serialize(),

            success: function(res) {

                $('#addMaterialModal').modal('hide');

                $('#formAddMaterial')[0].reset();

                // refresh list material
                window.loadMaterialMaster($('#searchMasterMaterial').val());

            }

        });

    });
</script>
