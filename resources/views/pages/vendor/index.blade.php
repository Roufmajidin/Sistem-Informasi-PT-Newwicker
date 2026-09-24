@extends('master.master')

@section('content')

<style>

/* =========================================================
   PAGE
========================================================= */

.vendor-page {
    padding: 20px;
}

.vendor-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #edf0f2;
    box-shadow: 0 4px 18px rgba(0,0,0,.06);
    overflow: hidden;
}


/* =========================================================
   HEADER
========================================================= */

.vendor-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 18px 20px;
    border-bottom: 1px solid #edf0f2;
}

.vendor-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.vendor-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: #6b7280;
}

.vendor-header-actions {
    display: flex;
    gap: 8px;
}


/* =========================================================
   BUTTON
========================================================= */

.btn-vendor {
    border: 0;
    border-radius: 8px;

    padding: 9px 14px;

    font-size: 13px;
    font-weight: 600;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    cursor: pointer;

    transition: .2s;
}

.btn-vendor:hover {
    transform: translateY(-1px);
}

.btn-add {
    background: #2563eb;
    color: #fff;
}

.btn-add:hover {
    background: #1d4ed8;
}

.btn-mass {
    background: #0f766e;
    color: #fff;
}

.btn-mass:hover {
    background: #115e59;
}

.btn-edit {
    background: #f59e0b;
    color: #fff;
}

.btn-delete {
    background: #dc2626;
    color: #fff;
}


/* =========================================================
   TABLE
========================================================= */

.vendor-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.vendor-table {
    width: 100%;
    min-width: 1250px;
    border-collapse: collapse;
}

.vendor-table thead th {
    background: #f8fafc;

    color: #475569;

    font-size: 12px;
    font-weight: 700;

    padding: 13px 12px;

    border-bottom: 1px solid #e2e8f0;

    white-space: nowrap;
}

.vendor-table tbody td {
    padding: 12px;

    font-size: 13px;

    color: #334155;

    border-bottom: 1px solid #f1f5f9;

    vertical-align: middle;

    white-space: nowrap;
}

.vendor-table tbody tr:hover {
    background: #f8fafc;
}

.vendor-no {
    width: 55px;
    text-align: center;
}

.vendor-uniq {
    font-family: Consolas, Monaco, monospace;

    font-size: 12px;

    font-weight: 700;

    color: #0f766e;

    background: #ecfdf5;

    border-radius: 6px;

    padding: 5px 8px;

    display: inline-block;
}

.vendor-actions {
    display: flex;
    gap: 5px;
}

.vendor-actions button {
    width: 32px;
    height: 32px;

    border: 0;

    border-radius: 7px;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;
}


/* =========================================================
   BADGE
========================================================= */

.vendor-badge {
    display: inline-flex;

    padding: 4px 8px;

    border-radius: 6px;

    background: #eff6ff;

    color: #2563eb;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   PAGINATION
========================================================= */

.vendor-pagination {
    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 15px 20px;

    border-top: 1px solid #edf0f2;
}

.vendor-pagination-info {
    font-size: 12px;
    color: #64748b;
}


/* =========================================================
   NORMAL MODAL
========================================================= */

.modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
}

.vendor-modal-header {
    padding: 17px 20px;

    border-bottom: 1px solid #edf0f2;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.vendor-modal-title {
    margin: 0;

    font-size: 17px;

    font-weight: 700;
}

.vendor-modal-body {
    padding: 20px;
}

.vendor-modal-footer {
    padding: 14px 20px;

    border-top: 1px solid #edf0f2;

    display: flex;

    justify-content: flex-end;

    gap: 8px;
}


/* =========================================================
   FORM
========================================================= */

.vendor-form-grid {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 0 15px;
}

.form-group-vendor {
    margin-bottom: 15px;
}

.form-group-vendor label {
    display: block;

    margin-bottom: 6px;

    font-size: 12px;

    font-weight: 600;

    color: #475569;
}

.form-group-vendor input,
.form-group-vendor textarea,
.form-group-vendor select {
    width: 100%;

    border: 1px solid #dbe2ea;

    border-radius: 8px;

    padding: 9px 11px;

    font-size: 13px;

    outline: none;
}

.form-group-vendor input:focus {
    border-color: #2563eb;

    box-shadow: 0 0 0 3px rgba(37,99,235,.08);
}


/* =========================================================
   MASS MODAL
========================================================= */

.mass-modal-content {
    height: 90vh;

    max-height: 90vh;

    border-radius: 14px;

    overflow: hidden;

    display: flex;

    flex-direction: column;
}

.mass-modal-header {
    flex: 0 0 auto;

    padding: 16px 20px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.mass-title-wrapper {
    display: flex;

    align-items: center;

    gap: 12px;
}

.mass-title-icon {
    width: 38px;
    height: 38px;

    border-radius: 9px;

    background: #ecfdf5;

    color: #059669;

    display: flex;

    align-items: center;

    justify-content: center;
}

.mass-title {
    margin: 0;

    font-size: 17px;

    font-weight: 700;
}

.mass-title-sub {
    margin-top: 2px;

    color: #64748b;

    font-size: 11px;
}


/* =========================================================
   MASS BODY
========================================================= */

.mass-modal-body {
    flex: 1 1 auto;

    min-height: 0;

    display: grid;

    grid-template-columns: 42% 58%;

    overflow: hidden;
}


/* =========================================================
   INPUT PANEL
========================================================= */

.mass-input-panel {
    min-width: 0;

    min-height: 0;

    display: flex;

    flex-direction: column;

    padding: 18px;

    border-right: 1px solid #e5e7eb;
}

.mass-section-title {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 10px;
}

.mass-section-title strong {
    font-size: 13px;
}

.mass-section-title span {
    font-size: 11px;

    color: #64748b;
}


/* =========================================================
   GUIDE
========================================================= */

.mass-guide {
    flex: 0 0 auto;

    margin-bottom: 12px;

    padding: 12px;

    border-radius: 9px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;
}

.mass-guide-title {
    font-size: 11px;

    font-weight: 700;

    margin-bottom: 8px;
}

.mass-guide-list {
    display: grid;

    grid-template-columns: repeat(2,1fr);

    gap: 5px 12px;

    padding: 0;

    margin: 0;

    list-style: none;
}

.mass-guide-list li {
    font-size: 10px;

    color: #64748b;
}


/* =========================================================
   TEXTAREA
========================================================= */

.mass-textarea-wrapper {
    flex: 1;

    min-height: 0;

    display: flex;

    flex-direction: column;
}

#mass_vendor_data {
    width: 100%;

    height: 100%;

    flex: 1;

    min-height: 0;

    resize: none;

    overflow-y: auto;

    overflow-x: auto;

    white-space: pre;

    tab-size: 4;

    padding: 13px;

    border: 1px solid #d1d5db;

    border-radius: 9px;

    background: #f8fafc;

    font-family: Consolas, Monaco, monospace;

    font-size: 12px;

    line-height: 1.6;

    outline: none;
}

#mass_vendor_data:focus {
    background: #fff;

    border-color: #2563eb;

    box-shadow: 0 0 0 3px rgba(37,99,235,.08);
}


/* =========================================================
   PREVIEW
========================================================= */

.mass-preview-panel {
    min-width: 0;

    min-height: 0;

    display: flex;

    flex-direction: column;

    padding: 18px;

    background: #f8fafc;
}

.mass-preview-header {
    flex: 0 0 auto;

    display: flex;

    justify-content: space-between;

    gap: 10px;

    margin-bottom: 10px;
}

.mass-preview-title {
    font-size: 13px;

    font-weight: 700;
}

.mass-preview-status {
    margin-top: 2px;

    font-size: 11px;

    color: #64748b;
}

.mass-preview-counter {
    white-space: nowrap;

    padding: 5px 9px;

    border-radius: 7px;

    background: #e0f2fe;

    color: #0369a1;

    font-size: 11px;

    font-weight: 700;
}


/* =========================================================
   PREVIEW CONTAINER
========================================================= */

.mass-preview-container {
    flex: 1;

    min-width: 0;

    min-height: 0;

    overflow: auto;

    border: 1px solid #e2e8f0;

    border-radius: 9px;

    background: #fff;
}

.mass-preview-container table {
    width: max-content;

    min-width: 100%;

    border-collapse: separate;

    border-spacing: 0;
}

.mass-preview-container thead {
    position: sticky;

    top: 0;

    z-index: 20;
}

.mass-preview-container th {
    position: sticky;

    top: 0;

    z-index: 20;

    padding: 10px 11px;

    background: #f1f5f9;

    color: #475569;

    border-bottom: 1px solid #dbe2ea;

    border-right: 1px solid #e5e7eb;

    font-size: 10px;

    white-space: nowrap;
}

.mass-preview-container td {
    padding: 8px 11px;

    color: #475569;

    border-bottom: 1px solid #f1f5f9;

    border-right: 1px solid #f1f5f9;

    font-size: 11px;

    white-space: nowrap;
}

.mass-preview-container tbody tr:hover {
    background: #f8fafc;
}


/* =========================================================
   UNIQUE PREVIEW
========================================================= */

.preview-uniq {
    display: inline-block;

    padding: 4px 7px;

    border-radius: 6px;

    background: #ecfdf5;

    color: #047857;

    font-family: Consolas, Monaco, monospace;

    font-size: 10px;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   EMPTY
========================================================= */

.mass-empty-preview {
    height: 100%;

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    color: #94a3b8;

    font-size: 12px;

    padding: 30px;
}


/* =========================================================
   PREVIEW PAGINATION
========================================================= */

.mass-preview-pagination {
    flex: 0 0 auto;

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 10px;
}

.mass-page-info {
    font-size: 11px;

    color: #64748b;
}

.mass-page-buttons {
    display: flex;

    gap: 5px;
}

.mass-page-buttons button {
    border: 1px solid #dbe2ea;

    background: #fff;

    color: #475569;

    border-radius: 6px;

    padding: 5px 9px;

    font-size: 11px;

    cursor: pointer;
}

.mass-page-buttons button:disabled {
    opacity: .45;

    cursor: not-allowed;
}


/* =========================================================
   FOOTER
========================================================= */

.mass-modal-footer {
    flex: 0 0 auto;

    padding: 12px 18px;

    border-top: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.mass-footer-info {
    font-size: 11px;

    color: #64748b;
}


/* =========================================================
   SCROLLBAR
========================================================= */

#mass_vendor_data::-webkit-scrollbar,
.mass-preview-container::-webkit-scrollbar {
    width: 8px;

    height: 8px;
}

#mass_vendor_data::-webkit-scrollbar-thumb,
.mass-preview-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;

    border-radius: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1000px) {

    .mass-modal-content {
        height: 95vh;
    }

    .mass-modal-body {
        grid-template-columns: 1fr;

        overflow-y: auto;
    }

    .mass-input-panel {
        height: 45vh;

        border-right: 0;

        border-bottom: 1px solid #e5e7eb;
    }

    .mass-preview-panel {
        height: 45vh;
    }
}

@media(max-width:700px) {

    .vendor-page {
        padding: 10px;
    }

    .vendor-header {
        flex-direction: column;

        align-items: flex-start;
    }

    .vendor-form-grid {
        grid-template-columns: 1fr;
    }

    .mass-guide-list {
        grid-template-columns: 1fr;
    }

    .mass-modal-footer {
        flex-direction: column;

        align-items: stretch;
    }
}

</style>


<div class="vendor-page">

<div class="vendor-card">


{{-- =========================================================
HEADER
========================================================= --}}

<div class="vendor-header">

    <div>

        <h3 class="vendor-title">
            Master Vendor
        </h3>

        <p class="vendor-subtitle">
            Kelola data vendor perusahaan
        </p>

    </div>


    <div class="vendor-header-actions">

        <button
            type="button"
            class="btn-vendor btn-mass"
            onclick="openMassVendor()"
        >

            <i class="fas fa-layer-group"></i>

            Mass Add

        </button>


        <button
            type="button"
            class="btn-vendor btn-add"
            onclick="openAddVendor()"
        >

            <i class="fas fa-plus"></i>

            Tambah Vendor

        </button>

    </div>

</div>


{{-- =========================================================
TABLE
========================================================= --}}

<div class="vendor-table-wrapper">

<table class="vendor-table">

<thead>

<tr>

    <th class="vendor-no">
        No.
    </th>

    <th>
        Nama Vendor
    </th>

    <th>
        UNIQ
    </th>

    <th>
        Alamat
    </th>

    <th>
        No. Rekening
    </th>

    <th>
        Nama Rekening
    </th>

    <th>
        Bank
    </th>

    <th>
        NPWP
    </th>

    <th>
        Vendor Type
    </th>

    <th>
        Vendor Type2
    </th>

    <th>
        Action
    </th>

</tr>

</thead>


<tbody>

@forelse($vendors as $index => $vendor)

<tr>

    <td class="vendor-no">
        {{ $vendors->firstItem() + $index }}
    </td>


    <td>
        <strong>
            {{ $vendor->nama_vendor }}
        </strong>
    </td>


    <td>

        @if($vendor->uniq)

            <span class="vendor-uniq">
                {{ $vendor->uniq }}
            </span>

        @else
            -
        @endif

    </td>


    <td>
        {{ $vendor->alamat ?: '-' }}
    </td>


    <td>
        {{ $vendor->nomor_rekening ?: '-' }}
    </td>


    <td>
        {{ $vendor->nama_rekening ?: '-' }}
    </td>


    <td>
        {{ $vendor->bank ?: '-' }}
    </td>


    <td>
        {{ $vendor->npwp ?: '-' }}
    </td>


    <td>

        @if($vendor->vendor_type)

            <span class="vendor-badge">
                {{ $vendor->vendor_type }}
            </span>

        @else
            -
        @endif

    </td>


    <td>

        @if($vendor->vendor_type2)

            <span class="vendor-badge">
                {{ $vendor->vendor_type2 }}
            </span>

        @else
            -
        @endif

    </td>


    <td>

        <div class="vendor-actions">

            <button
                type="button"
                class="btn-edit"
                title="Edit"
                onclick='editVendor(@json($vendor))'
            >
                <i class="fas fa-edit"></i>
            </button>


            <button
                type="button"
                class="btn-delete"
                title="Delete"
                onclick="deleteVendor({{ $vendor->id }})"
            >
                <i class="fas fa-trash"></i>
            </button>

        </div>

    </td>

</tr>

@empty

<tr>

<td colspan="11"
    style="text-align:center;padding:40px;color:#94a3b8;">

    <i
        class="fas fa-box-open"
        style="font-size:30px;margin-bottom:10px;"
    ></i>

    <div>
        Belum ada data vendor
    </div>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


{{-- =========================================================
PAGINATION
========================================================= --}}

@if($vendors->count() > 0)

<div class="vendor-pagination">

    <div class="vendor-pagination-info">

        Menampilkan

        <strong>
            {{ $vendors->firstItem() }}
        </strong>

        -

        <strong>
            {{ $vendors->lastItem() }}
        </strong>

        dari

        <strong>
            {{ $vendors->total() }}
        </strong>

        vendor

    </div>


    <div>
        {{ $vendors->links() }}
    </div>

</div>

@endif


</div>

</div>


{{-- =========================================================
ADD / EDIT MODAL
========================================================= --}}

<div
    class="modal fade"
    id="vendorModal"
    tabindex="-1"
>

<div class="modal-dialog modal-lg modal-dialog-centered">

<div class="modal-content">


<div class="vendor-modal-header">

<h5
    class="vendor-modal-title"
    id="vendorModalTitle"
>
    Tambah Vendor
</h5>


<button
    type="button"
    class="close"
    data-dismiss="modal"
>
    <span>&times;</span>
</button>

</div>


<div class="vendor-modal-body">

<form id="vendorForm">

@csrf

<input
    type="hidden"
    id="vendor_id"
>


<div class="vendor-form-grid">


<div class="form-group-vendor">

<label>
Nama Vendor
</label>

<input
    type="text"
    id="nama_vendor"
    required
>

</div>


<div class="form-group-vendor">

<label>
Alamat
</label>

<input
    type="text"
    id="alamat"
>

</div>


<div class="form-group-vendor">

<label>
No. Rekening
</label>

<input
    type="text"
    id="nomor_rekening"
>

</div>


<div class="form-group-vendor">

<label>
Nama Rekening
</label>

<input
    type="text"
    id="nama_rekening"
>

</div>


<div class="form-group-vendor">

<label>
Bank
</label>

<input
    type="text"
    id="bank"
>

</div>


<div class="form-group-vendor">

<label>
NPWP
</label>

<input
    type="text"
    id="npwp"
>

</div>


<div class="form-group-vendor">

<label>
Vendor Type
</label>

<input
    type="text"
    id="vendor_type"
>

</div>


<div class="form-group-vendor">

<label>
Vendor Type2
</label>

<input
    type="text"
    id="vendor_type2"
>

</div>


</div>

</form>

</div>


<div class="vendor-modal-footer">

<button
    type="button"
    class="btn-vendor"
    style="background:#e5e7eb;color:#374151;"
    data-dismiss="modal"
>
    Batal
</button>


<button
    type="button"
    class="btn-vendor btn-add"
    onclick="saveVendor()"
>
    <i class="fas fa-save"></i>
    Simpan
</button>

</div>


</div>

</div>

</div>


{{-- =========================================================
MASS MODAL
========================================================= --}}

<div
    class="modal fade"
    id="massVendorModal"
    tabindex="-1"
>

<div
    class="modal-dialog modal-xl modal-dialog-centered"
    style="max-width:1500px;width:95%;"
>

<div class="modal-content mass-modal-content">


{{-- HEADER --}}

<div class="mass-modal-header">

<div class="mass-title-wrapper">

<div class="mass-title-icon">

<i class="fas fa-layer-group"></i>

</div>


<div>

<h5 class="mass-title">
Mass Add Vendor
</h5>

<div class="mass-title-sub">
Paste data dari Excel / Google Sheets
</div>

</div>

</div>


<button
    type="button"
    class="close"
    data-dismiss="modal"
>
<span>&times;</span>
</button>

</div>


{{-- BODY --}}

<div class="mass-modal-body">


{{-- LEFT --}}

<div class="mass-input-panel">


<div class="mass-section-title">

<strong>

<i class="fas fa-paste"></i>

Paste Data

</strong>

<span>
Excel / Google Sheets
</span>

</div>


{{-- GUIDE --}}

<div class="mass-guide">

<div class="mass-guide-title">

Urutan kolom:

</div>


<ul class="mass-guide-list">

<li>
<strong>1.</strong> Nama Vendor
</li>

<li>
<strong>2.</strong> Alamat
</li>

<li>
<strong>3.</strong> No. Rekening
</li>

<li>
<strong>4.</strong> Nama Rekening
</li>

<li>
<strong>5.</strong> Bank
</li>

<li>
<strong>6.</strong> NPWP
</li>

<li>
<strong>7.</strong> Vendor Type
</li>

<li>
<strong>8.</strong> Vendor Type2
</li>

</ul>

</div>


<div class="mass-textarea-wrapper">

<textarea
    id="mass_vendor_data"
    placeholder="Paste dari Excel:

HENDRIK JEPARA	JEPARA	589301004032532	HENDRIK PRABOWO	BRI	-	WIP/Sub	WIP/Sub
SUMBER SULAWESI		1340687767	ELFIS LIANDI	BCA	-	Material	Material

Atau:

| HENDRIK JEPARA | JEPARA | 589301004032532 | HENDRIK PRABOWO | BRI | - | WIP/Sub | WIP/Sub |"
></textarea>

</div>

</div>


{{-- RIGHT --}}

<div class="mass-preview-panel">


<div class="mass-preview-header">

<div>

<div class="mass-preview-title">

<i class="fas fa-table"></i>

Preview Data

</div>


<div
    class="mass-preview-status"
    id="previewStatus"
>
Belum ada data
</div>

</div>


<div
    class="mass-preview-counter"
    id="pasteRowCount"
>
0 data
</div>

</div>


<div class="mass-preview-container">

<table>

<thead>

<tr>

<th>
No.
</th>

<th>
Nama Vendor
</th>

<th>
UNIQ
</th>

<th>
Alamat
</th>

<th>
No. Rekening
</th>

<th>
Nama Rekening
</th>

<th>
Bank
</th>

<th>
NPWP
</th>

<th>
Vendor Type
</th>

<th>
Vendor Type2
</th>

</tr>

</thead>


<tbody id="vendorPreviewBody">

<tr>

<td colspan="10">

<div class="mass-empty-preview">

<div>

<i
    class="fas fa-table"
    style="font-size:30px;margin-bottom:10px;"
></i>

<div>
Paste data di sebelah kiri
</div>

</div>

</div>

</td>

</tr>

</tbody>

</table>

</div>


<div class="mass-preview-pagination">

<div
    class="mass-page-info"
    id="massPageInfo"
>
0 data
</div>


<div class="mass-page-buttons">

<button
    type="button"
    id="massPrevPage"
    onclick="changeMassPage(-1)"
    disabled
>

<i class="fas fa-chevron-left"></i>

</button>


<button
    type="button"
    id="massNextPage"
    onclick="changeMassPage(1)"
    disabled
>

<i class="fas fa-chevron-right"></i>

</button>

</div>

</div>

</div>

</div>


{{-- FOOTER --}}

<div class="mass-modal-footer">

<div
    class="mass-footer-info"
    id="footerVendorCount"
>
Belum ada data vendor
</div>


<div style="display:flex;gap:8px;">

<button
    type="button"
    class="btn-vendor"
    style="background:#e5e7eb;color:#374151;"
    data-dismiss="modal"
>
Batal
</button>


<button
    type="button"
    class="btn-vendor btn-mass"
    id="btnMassSave"
    onclick="saveMassVendor()"
    disabled
>

<i class="fas fa-file-import"></i>

Import Vendor

</button>

</div>

</div>


</div>

</div>

</div>


<script>

(function () {

'use strict';


/* =========================================================
   GLOBAL
========================================================= */

let massVendorRows = [];

let massCurrentPage = 1;

const massPageSize = 100;


/* =========================================================
   ESCAPE
========================================================= */

function escapeHtml(value)
{
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
}


/* =========================================================
   GENERATE UNIQUE
========================================================= */

function generatePreviewUniq(
    nama,
    rekening,
    bank,
    used
) {

    /*
     * Nama
     */
    nama = String(nama || '')
        .trim()
        .replace(/\s+/g,' ');


    const words = nama
        .toUpperCase()
        .split(/\s+/);


    let initials = '';


    words.forEach(function(word) {

        word = word
            .replace(/[^A-Z0-9]/g,'');

        if (word !== '') {

            initials += word.substring(0,1);

        }

    });


    if (!initials) {
        initials = 'V';
    }


    /*
     * Rekening
     */
    let rekeningClean =
        String(rekening || '')
        .replace(/[^0-9]/g,'');


    if (!rekeningClean) {

        rekeningClean = '000';

    }


    rekeningClean =
        rekeningClean
        .padStart(3,'0')
        .substring(0,3);


    /*
     * Bank
     */
    let bankClean =
        String(bank || '')
        .toUpperCase()
        .replace(/[^A-Z0-9]/g,'');


    if (!bankClean) {

        bankClean = 'BANK';

    }


    /*
     * Base
     */
    const base =
        initials +
        '-' +
        rekeningClean +
        '-' +
        bankClean;


    let uniq = base;

    let counter = 1;


    while (
        Object.prototype.hasOwnProperty.call(
            used,
            uniq
        )
    ) {

        counter++;

        uniq =
            base +
            '-' +
            counter;
    }


    used[uniq] = true;


    return uniq;
}


/* =========================================================
   OPEN ADD
========================================================= */

window.openAddVendor = function()
{

    $('#vendorForm')[0].reset();

    $('#vendor_id').val('');

    $('#vendorModalTitle')
        .text('Tambah Vendor');

    $('#vendorModal').modal('show');

};


/* =========================================================
   EDIT
========================================================= */

window.editVendor = function(vendor)
{

    $('#vendor_id').val(vendor.id || '');

    $('#nama_vendor').val(
        vendor.nama_vendor || ''
    );

    $('#alamat').val(
        vendor.alamat || ''
    );

    $('#nomor_rekening').val(
        vendor.nomor_rekening || ''
    );

    $('#nama_rekening').val(
        vendor.nama_rekening || ''
    );

    $('#bank').val(
        vendor.bank || ''
    );

    $('#npwp').val(
        vendor.npwp || ''
    );

    $('#vendor_type').val(
        vendor.vendor_type || ''
    );

    $('#vendor_type2').val(
        vendor.vendor_type2 || ''
    );


    $('#vendorModalTitle')
        .text('Edit Vendor');


    $('#vendorModal').modal('show');

};


/* =========================================================
   SAVE SINGLE
========================================================= */

window.saveVendor = function()
{

    const id =
        $('#vendor_id').val();


    const isEdit =
        id !== '';


    const url = isEdit

        ? '{{ url("/vendor_list/update") }}/' + id

        : '{{ route("vendor.store") }}';


    const method =
        isEdit
        ? 'PUT'
        : 'POST';


    const data = {

        _token:
            '{{ csrf_token() }}',

        nama_vendor:
            $('#nama_vendor').val(),

        alamat:
            $('#alamat').val(),

        nomor_rekening:
            $('#nomor_rekening').val(),

        nama_rekening:
            $('#nama_rekening').val(),

        bank:
            $('#bank').val(),

        npwp:
            $('#npwp').val(),

        vendor_type:
            $('#vendor_type').val(),

        vendor_type2:
            $('#vendor_type2').val()

    };


    if (!data.nama_vendor.trim()) {

        Swal.fire({
            icon:'warning',
            title:'Nama vendor wajib diisi'
        });

        return;
    }


    $.ajax({

        url:url,

        type:method,

        data:data,

        beforeSend:function(){

            $('#vendorModal button')
                .prop('disabled',true);

        },

        success:function(response){

            if(response.success){

                $('#vendorModal')
                    .modal('hide');


                Swal.fire({

                    icon:'success',

                    title:'Berhasil',

                    text:
                        response.message ||
                        'Vendor berhasil disimpan',

                    timer:1500,

                    showConfirmButton:false

                }).then(function(){

                    location.reload();

                });

            } else {

                Swal.fire({

                    icon:'error',

                    title:'Gagal',

                    text:
                        response.message ||
                        'Data gagal disimpan'

                });

            }

        },

        error:function(xhr){

            let message =
                'Terjadi kesalahan saat menyimpan data.';


            if(
                xhr.responseJSON &&
                xhr.responseJSON.message
            ){

                message =
                    xhr.responseJSON.message;

            }


            Swal.fire({

                icon:'error',

                title:'Gagal',

                text:message

            });

        },

        complete:function(){

            $('#vendorModal button')
                .prop('disabled',false);

        }

    });

};


/* =========================================================
   DELETE
========================================================= */

window.deleteVendor = function(id)
{

    Swal.fire({

        title:'Hapus vendor?',

        text:
            'Data vendor yang dihapus tidak dapat dikembalikan.',

        icon:'warning',

        showCancelButton:true,

        confirmButtonText:'Ya, Hapus',

        cancelButtonText:'Batal',

        confirmButtonColor:'#dc2626'

    }).then(function(result){

        if(!result.isConfirmed){
            return;
        }


        $.ajax({

            url:
                '{{ url("/vendor_list/delete") }}/' + id,

            type:'DELETE',

            data:{
                _token:
                    '{{ csrf_token() }}'
            },

            success:function(response){

                if(response.success){

                    Swal.fire({

                        icon:'success',

                        title:'Berhasil',

                        text:
                            response.message ||
                            'Vendor berhasil dihapus',

                        timer:1200,

                        showConfirmButton:false

                    }).then(function(){

                        location.reload();

                    });

                } else {

                    Swal.fire({

                        icon:'error',

                        title:'Gagal',

                        text:
                            response.message ||
                            'Vendor gagal dihapus'

                    });

                }

            },

            error:function(xhr){

                Swal.fire({

                    icon:'error',

                    title:'Gagal',

                    text:
                        xhr.responseJSON?.message ||
                        'Vendor gagal dihapus.'

                });

            }

        });

    });

};


/* =========================================================
   OPEN MASS
========================================================= */

window.openMassVendor = function()
{

    massVendorRows = [];

    massCurrentPage = 1;


    $('#mass_vendor_data').val('');


    $('#vendorPreviewBody').html(`

        <tr>

            <td colspan="10">

                <div class="mass-empty-preview">

                    <div>

                        <i
                            class="fas fa-table"
                            style="font-size:30px;margin-bottom:10px;"
                        ></i>

                        <div>
                            Paste data di sebelah kiri
                        </div>

                    </div>

                </div>

            </td>

        </tr>

    `);


    $('#pasteRowCount')
        .text('0 data');


    $('#previewStatus')
        .text('Belum ada data');


    $('#footerVendorCount')
        .text('Belum ada data vendor');


    $('#massPageInfo')
        .text('0 data');


    $('#massPrevPage')
        .prop('disabled',true);


    $('#massNextPage')
        .prop('disabled',true);


    $('#btnMassSave')
        .prop('disabled',true);


    $('#massVendorModal')
        .modal('show');

};


/* =========================================================
   NORMALIZE
========================================================= */

function normalizeValue(value)
{

    value =
        String(value || '')
        .trim();


    if(value === '-'){
        return '';
    }


    return value;

}


/* =========================================================
   PARSE
========================================================= */

function parseMassVendorData(raw)
{

    if(!raw || !raw.trim()){
        return [];
    }


    const lines =
        raw
        .replace(/\r\n/g,'\n')
        .replace(/\r/g,'\n')
        .split('\n');


    const result = [];


    lines.forEach(function(line){

        if(!line.trim()){
            return;
        }


        let columns;


        /*
         * Excel
         */
        if(line.indexOf('\t') !== -1){

            columns =
                line.split('\t');

        } else {

            let cleanLine =
                line.trim();


            if(
                cleanLine.startsWith('|') &&
                cleanLine.endsWith('|')
            ){

                cleanLine =
                    cleanLine.substring(
                        1,
                        cleanLine.length - 1
                    );

            }


            columns =
                cleanLine.split('|');

        }


        columns =
            columns.map(normalizeValue);


        /*
         * Separator
         */
        if(
            columns.length &&
            columns.every(function(value){

                return (
                    value === '' ||
                    /^-+$/.test(value)
                );

            })
        ){

            return;
        }


        /*
         * Header
         */
        const first =
            String(columns[0] || '')
            .toLowerCase()
            .replace(/\s+/g,'')
            .replace(/_/g,'');


        if(
            first === 'namavendor' ||
            first === 'nama' ||
            first === 'vendor'
        ){

            return;

        }


        /*
         * 8 kolom
         */
        while(columns.length < 8){

            columns.push('');

        }


        /*
         * Lebih dari 8
         */
        if(columns.length > 8){

            columns = [

                columns[0],
                columns[1],
                columns[2],
                columns[3],
                columns[4],
                columns[5],
                columns[6],

                columns
                    .slice(7)
                    .join(' | ')

            ];

        }


        /*
         * Nama wajib
         */
        if(!columns[0]){
            return;
        }


        result.push(columns);

    });


    return result;

}


/* =========================================================
   RENDER PREVIEW
========================================================= */

function renderMassPreview()
{

    const total =
        massVendorRows.length;


    if(!total){

        $('#vendorPreviewBody').html(`

            <tr>

                <td colspan="10">

                    <div class="mass-empty-preview">

                        Tidak ada data valid.

                    </div>

                </td>

            </tr>

        `);


        $('#pasteRowCount')
            .text('0 data');


        $('#previewStatus')
            .text('Belum ada data');


        $('#footerVendorCount')
            .text('Belum ada data vendor');


        $('#massPageInfo')
            .text('0 data');


        $('#massPrevPage')
            .prop('disabled',true);


        $('#massNextPage')
            .prop('disabled',true);


        $('#btnMassSave')
            .prop('disabled',true);


        return;
    }


    const totalPages =
        Math.ceil(
            total / massPageSize
        );


    if(
        massCurrentPage > totalPages
    ){

        massCurrentPage =
            totalPages;

    }


    const startIndex =
        (massCurrentPage - 1) *
        massPageSize;


    const endIndex =
        Math.min(
            startIndex + massPageSize,
            total
        );


    const pageRows =
        massVendorRows.slice(
            startIndex,
            endIndex
        );


    /*
     * Generate preview uniq.
     *
     * Semua data dari awal,
     * supaya duplicate antar halaman
     * tetap diketahui.
     */
    const used = {};

    const allUniq = [];


    massVendorRows.forEach(function(row){

        const uniq =
            generatePreviewUniq(
                row[0],
                row[2],
                row[4],
                used
            );


        allUniq.push(uniq);

    });


    let html = '';


    pageRows.forEach(function(row,index){

        const absoluteIndex =
            startIndex + index;


        html += `

            <tr>

                <td>
                    ${absoluteIndex + 1}
                </td>

                <td>
                    <strong>
                        ${escapeHtml(row[0])}
                    </strong>
                </td>

                <td>
                    <span class="preview-uniq">
                        ${escapeHtml(
                            allUniq[absoluteIndex]
                        )}
                    </span>
                </td>

                <td>
                    ${escapeHtml(row[1])}
                </td>

                <td>
                    ${escapeHtml(row[2])}
                </td>

                <td>
                    ${escapeHtml(row[3])}
                </td>

                <td>
                    ${escapeHtml(row[4])}
                </td>

                <td>
                    ${escapeHtml(row[5])}
                </td>

                <td>
                    ${escapeHtml(row[6])}
                </td>

                <td>
                    ${escapeHtml(row[7])}
                </td>

            </tr>

        `;

    });


    $('#vendorPreviewBody')
        .html(html);


    $('#pasteRowCount')
        .text(
            total.toLocaleString('id-ID') +
            ' data'
        );


    $('#previewStatus').text(

        'Menampilkan ' +

        (startIndex + 1)
            .toLocaleString('id-ID') +

        ' - ' +

        endIndex
            .toLocaleString('id-ID') +

        ' dari ' +

        total
            .toLocaleString('id-ID') +

        ' data'

    );


    $('#footerVendorCount').html(

        '<strong>' +

        total.toLocaleString('id-ID') +

        '</strong> vendor siap diimport'

    );


    $('#massPageInfo').text(

        'Halaman ' +

        massCurrentPage.toLocaleString('id-ID') +

        ' / ' +

        totalPages.toLocaleString('id-ID')

    );


    $('#massPrevPage')
        .prop(
            'disabled',
            massCurrentPage <= 1
        );


    $('#massNextPage')
        .prop(
            'disabled',
            massCurrentPage >= totalPages
        );


    $('#btnMassSave')
        .prop('disabled',false);

}


/* =========================================================
   PAGE
========================================================= */

window.changeMassPage = function(direction)
{

    const totalPages =
        Math.ceil(
            massVendorRows.length /
            massPageSize
        );


    massCurrentPage += direction;


    if(massCurrentPage < 1){

        massCurrentPage = 1;

    }


    if(
        massCurrentPage > totalPages
    ){

        massCurrentPage =
            totalPages;

    }


    renderMassPreview();

};


/* =========================================================
   INPUT
========================================================= */

$('#mass_vendor_data').on(
    'input paste',
    function(){

        setTimeout(function(){

            const raw =
                $('#mass_vendor_data')
                .val();


            massVendorRows =
                parseMassVendorData(raw);


            massCurrentPage = 1;


            renderMassPreview();

        },50);

    }
);


/* =========================================================
   SAVE MASS
========================================================= */

window.saveMassVendor = function()
{

    if(!massVendorRows.length){

        Swal.fire({

            icon:'warning',

            title:'Tidak ada data',

            text:
                'Silakan paste data vendor terlebih dahulu.'

        });

        return;
    }


    Swal.fire({

        title:'Import vendor?',

        html:

            'Sebanyak <strong>' +

            massVendorRows.length
                .toLocaleString('id-ID') +

            '</strong> vendor akan disimpan.',

        icon:'question',

        showCancelButton:true,

        confirmButtonText:'Ya, Import',

        cancelButtonText:'Batal',

        confirmButtonColor:'#0f766e'

    }).then(function(result){

        if(!result.isConfirmed){
            return;
        }


        const button =
            $('#btnMassSave');


        button.prop(
            'disabled',
            true
        );


        button.html(`

            <i class="fas fa-spinner fa-spin"></i>

            Importing...

        `);


        $.ajax({

            url:
                '{{ route("vendor.massStore") }}',

            type:'POST',

            data:{

                _token:
                    '{{ csrf_token() }}',

                data:
                    $('#mass_vendor_data')
                    .val()

            },

            success:function(response){

                if(response.success){

                    $('#massVendorModal')
                        .modal('hide');


                    Swal.fire({

                        icon:'success',

                        title:'Berhasil',

                        text:
                            response.message ||
                            'Vendor berhasil diimport.',

                        timer:1800,

                        showConfirmButton:false

                    }).then(function(){

                        location.reload();

                    });

                } else {

                    Swal.fire({

                        icon:'error',

                        title:'Import gagal',

                        text:
                            response.message ||
                            'Data gagal diimport.'

                    });

                }

            },

            error:function(xhr){

                let message =
                    'Terjadi kesalahan saat import.';


                if(
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ){

                    message =
                        xhr.responseJSON.message;

                }


                Swal.fire({

                    icon:'error',

                    title:'Import gagal',

                    text:message

                });

            },

            complete:function(){

                button.prop(
                    'disabled',
                    false
                );


                button.html(`

                    <i class="fas fa-file-import"></i>

                    Import Vendor

                `);

            }

        });

    });

};


})();

</script>

@endsection