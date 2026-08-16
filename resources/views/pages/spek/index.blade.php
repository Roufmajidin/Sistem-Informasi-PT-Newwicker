@extends('master.master')

@section('content')

<style>
    .spek-page{
        --blue:#2563eb;
        --blue-dark:#1d4ed8;
        --blue-soft:#eff6ff;
        --text:#172033;
        --muted:#667085;
        --border:#e5e7eb;
        --soft:#f8fafc;
        --danger:#ef4444;
        color:var(--text);
        font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;
        padding:4px 8px 30px;
    }

    .spek-page *{box-sizing:border-box}

    .spek-toolbar{
        min-height:58px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        border-bottom:1px solid #edf0f4;
        gap:15px;
    }

    .breadcrumb{
        display:flex;
        align-items:center;
        gap:10px;
        color:#667085;
        font-size:13px;
    }

    .breadcrumb strong{color:#172033}
    .breadcrumb .arrow{color:#98a2b3;font-size:18px}

    .page-head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:20px;
        padding:18px 0 14px;
    }

    .page-head h1{
        margin:0 0 4px;
        font-size:22px;
        line-height:1.25;
        font-weight:700;
        letter-spacing:-.02em;
    }

    .page-head p{
        margin:0;
        color:var(--muted);
        font-size:12px;
    }

    .btn{
        height:37px;
        padding:0 13px;
        border:1px solid var(--border);
        border-radius:7px;
        background:#fff;
        color:#344054;
        font-size:12px;
        font-weight:600;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        cursor:pointer;
        transition:.15s ease;
        text-decoration:none!important;
    }

    .btn:hover{
        background:#f8fafc;
        color:#172033;
    }

    .btn-primary{
        color:#fff!important;
        background:var(--blue);
        border-color:var(--blue);
    }

    .btn-primary:hover{
        background:var(--blue-dark);
        border-color:var(--blue-dark);
    }

    .btn-danger{
        color:var(--danger)!important;
        border-color:#fecaca;
    }

    .btn-danger:hover{
        background:#fff5f5;
    }

    .stats{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:12px;
        margin-bottom:13px;
    }

    .stat-card{
        min-height:82px;
        border:1px solid var(--border);
        border-radius:8px;
        background:#fff;
        padding:13px 15px;
        box-shadow:0 1px 2px rgba(16,24,40,.025);
    }

    .stat-label{
        color:#667085;
        font-size:10.5px;
        margin-bottom:7px;
    }

    .stat-value{
        font-size:21px;
        line-height:1;
        font-weight:700;
        color:#172033;
    }

    .stat-desc{
        margin-top:6px;
        color:#98a2b3;
        font-size:9.5px;
    }

    .card{
        background:#fff;
        border:1px solid var(--border);
        border-radius:8px;
        overflow:hidden;
        box-shadow:0 1px 2px rgba(16,24,40,.025);
    }

    .card-head{
        min-height:64px;
        padding:12px 16px;
        border-bottom:1px solid #eef1f5;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }

    .card-title{
        font-size:14px;
        font-weight:700;
        margin-bottom:3px;
    }

    .card-subtitle{
        color:#98a2b3;
        font-size:10.5px;
    }

    .tools{
        display:flex;
        align-items:center;
        gap:7px;
    }

    .search{
        position:relative;
    }

    .search input{
        width:235px;
        height:35px;
        padding:0 10px 0 34px;
        border:1px solid #dfe3e8;
        border-radius:6px;
        outline:none;
        font-size:11.5px;
        color:#344054;
    }

    .search span{
        position:absolute;
        left:11px;
        top:9px;
        color:#98a2b3;
        font-size:13px;
    }

    .table-wrap{
        width:100%;
        overflow:auto;
    }

    .spec-list{
        width:100%;
        min-width:950px;
        border-collapse:collapse;
    }

    .spec-list th{
        height:38px;
        padding:0 10px;
        background:#fcfcfd;
        border-bottom:1px solid #e9edf2;
        color:#475467;
        text-align:left;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .spec-list td{
        padding:9px 10px;
        border-bottom:1px solid #f0f2f5;
        color:#475467;
        font-size:11.5px;
        vertical-align:middle;
    }

    .spec-list tr:last-child td{border-bottom:0}

    .spec-list tbody tr{
        transition:.12s ease;
    }

    .spec-list tbody tr:hover{
        background:#fafcff;
    }

    .id-col{width:55px;text-align:center!important}
    .name-col{width:245px}
    .article-col{width:145px}
    .field-col{width:100px;text-align:center!important}
    .image-col{width:110px}
    .creator-col{width:150px}
    .date-col{width:150px}
    .action-col{width:120px;text-align:center!important}

    .product-name{
        display:flex;
        align-items:center;
        gap:9px;
    }

    .product-icon{
        width:35px;
        height:35px;
        border-radius:7px;
        background:var(--blue-soft);
        color:var(--blue);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:15px;
        flex:0 0 35px;
    }

    .product-name strong{
        display:block;
        color:#172033;
        font-size:12px;
        font-weight:650;
        margin-bottom:2px;
    }

    .product-name small{
        color:#98a2b3;
        font-size:9.5px;
    }

    .article{
        display:inline-flex;
        align-items:center;
        padding:4px 8px;
        border-radius:999px;
        background:#f2f4f7;
        color:#344054;
        font-size:10px;
        font-weight:700;
    }

    .count-badge{
        display:inline-flex;
        min-width:27px;
        height:24px;
        align-items:center;
        justify-content:center;
        padding:0 7px;
        border-radius:6px;
        background:var(--blue-soft);
        color:var(--blue);
        font-size:10px;
        font-weight:700;
    }

    .image-badge{
        color:#475467;
        font-size:10px;
    }

    .creator{
        display:flex;
        align-items:center;
        gap:7px;
    }

    .avatar{
        width:27px;
        height:27px;
        border-radius:50%;
        background:#eef2f6;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#475467;
        font-size:10px;
        font-weight:700;
    }

    .creator-name{
        color:#344054;
        font-size:10.5px;
        font-weight:600;
    }

    .date{
        color:#667085;
        font-size:10px;
    }

    .actions{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:5px;
    }

    .icon-btn{
        width:31px;
        height:31px;
        border:1px solid #dfe5ec;
        border-radius:6px;
        background:#fff;
        color:#475467;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        text-decoration:none!important;
        font-size:13px;
    }

    .icon-btn:hover{
        background:#f8fafc;
        color:var(--blue);
    }

    .icon-btn.delete{
        color:var(--danger);
        border-color:#fecaca;
    }

    .empty{
        text-align:center;
        padding:55px 20px;
        color:#98a2b3;
        font-size:12px;
    }

    .empty .empty-icon{
        width:48px;
        height:48px;
        margin:0 auto 10px;
        border-radius:10px;
        background:#f2f4f7;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:19px;
    }

    .pagination-area{
        min-height:54px;
        padding:10px 15px;
        border-top:1px solid #eef1f5;
        display:flex;
        align-items:center;
        justify-content:space-between;
        color:#98a2b3;
        font-size:10px;
    }

    .json-modal{
        position:fixed;
        inset:0;
        background:rgba(15,23,42,.38);
        z-index:99999;
        display:none;
        align-items:center;
        justify-content:center;
        padding:20px;
    }

    .json-modal.show{display:flex}

    .modal-box{
        width:min(800px,100%);
        max-height:85vh;
        background:#fff;
        border-radius:10px;
        overflow:hidden;
        box-shadow:0 20px 60px rgba(15,23,42,.22);
    }

    .modal-head{
        min-height:55px;
        padding:0 16px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        border-bottom:1px solid #edf0f4;
    }

    .modal-head strong{font-size:13px}

    .modal-close{
        border:0;
        background:none;
        color:#667085;
        font-size:20px;
        cursor:pointer;
    }

    .modal-body{
        padding:15px;
        overflow:auto;
        max-height:calc(85vh - 110px);
    }

    .json-code{
        margin:0;
        padding:15px;
        border-radius:7px;
        background:#111827;
        color:#d1fae5;
        overflow:auto;
        font:11px/1.6 Consolas,monospace;
    }

    .modal-foot{
        min-height:55px;
        padding:10px 15px;
        border-top:1px solid #edf0f4;
        display:flex;
        justify-content:flex-end;
    }

    @media(max-width:1100px){
        .stats{grid-template-columns:repeat(2,1fr)}
    }

    @media(max-width:700px){
        .spek-toolbar,.page-head{
            align-items:flex-start;
            flex-direction:column;
            padding:10px 0
        }

        .tools{
            width:100%;
            flex-wrap:wrap;
            align-items:stretch;
        }

        .tools .btn-primary{
            flex:1 1 100%;
        }

        .search{
            flex:1 1 auto;
        }

        .search input{
            width:100%;
        }

        .stats{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="spek-page">

    {{-- TOP BAR --}}
    <div class="spek-toolbar">
        <div class="breadcrumb">
            <span>Master</span>
            <span class="arrow">›</span>
            <strong>Spesifikasi Produk</strong>
        </div>

        <a href="{{ url('/spek/create') }}" class="btn btn-primary">
            + Tambah Spesifikasi
        </a>
    </div>

    {{-- HEADER --}}
    @section('btn')
    
    <div class="page-head">
        <div>
            <h1>Spesifikasi Produk</h1>
            <p>Kelola data spesifikasi produk secara dinamis.</p>
        </div>
    </div>
    @endsection

    {{-- STAT --}}
    @php
        $totalSpecifications = $specifications->count();

        $totalFields = $specifications->sum(function ($spec) {
            return is_array($spec->data) ? count($spec->data) : 0;
        });

        $totalImages = $specifications->sum(function ($spec) {
            if (!is_array($spec->data)) {
                return 0;
            }

            return collect($spec->data)->sum(function ($field) {
                return isset($field['images']) && is_array($field['images'])
                    ? count($field['images'])
                    : 0;
            });
        });

        $totalCreators = $specifications->pluck('created_by')->filter()->unique()->count();
    @endphp

    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Total Produk</div>
            <div class="stat-value">{{ number_format($totalSpecifications) }}</div>
            <div class="stat-desc">Data spesifikasi tersimpan</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Field</div>
            <div class="stat-value">{{ number_format($totalFields) }}</div>
            <div class="stat-desc">Field dinamis seluruh produk</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Attachment</div>
            <div class="stat-value">{{ number_format($totalImages) }}</div>
            <div class="stat-desc">Gambar spesifikasi</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Pembuat</div>
            <div class="stat-value">{{ number_format($totalCreators) }}</div>
            <div class="stat-desc">User yang membuat data</div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">

        <div class="card-head">
            <div>
                <div class="card-title">Daftar Spesifikasi</div>
                <div class="card-subtitle">
                    Setiap artikel dapat memiliki struktur spesifikasi yang berbeda.
                </div>
            </div>

            <div class="tools">

                {{-- Tambah Spesifikasi --}}
                <a
                    href="{{ route('spek.create') }}"
                    class="btn btn-primary"
                >
                    <span style="font-size:15px;line-height:1;">＋</span>
                    Tambah Spesifikasi
                </a>

                {{-- Search --}}
                <div class="search">
                    <span>⌕</span>
                    <input
                        type="text"
                        id="searchSpecification"
                        placeholder="Cari nama / article code..."
                        autocomplete="off"
                    >
                </div>

                {{-- Reset --}}
                <button
                    type="button"
                    class="btn"
                    id="resetSearch"
                >
                    Reset
                </button>

            </div>
        </div>

        <div class="table-wrap">

            <table class="spec-list">
                <thead>
                    <tr>
                        <th class="id-col">ID</th>
                        <th class="name-col">Produk</th>
                        <th class="article-col">Article Code</th>
                        <th class="field-col">Field</th>
                        <th class="image-col">Attachment</th>
                        <th class="creator-col">Created By</th>
                        <th class="date-col">Created At</th>
                        <th class="action-col">Aksi</th>
                    </tr>
                </thead>

                <tbody id="specificationRows">

                    @forelse($specifications as $spec)

                        @php
                            $data = is_array($spec->data) ? $spec->data : [];

                            $fieldCount = count($data);

                            $imageCount = collect($data)->sum(function ($field) {
                                return isset($field['images']) && is_array($field['images'])
                                    ? count($field['images'])
                                    : 0;
                            });

                            $creatorName = $spec->creator?->name
                                ?? $spec->creator?->nama
                                ?? ('User #' . ($spec->created_by ?? '-'));

                            $initial = strtoupper(substr($creatorName, 0, 1));
                        @endphp

                        <tr
                            class="spec-row"
                            data-search="{{ strtolower($spec->name . ' ' . $spec->article_code) }}"
                        >

                            <td class="id-col">
                                #{{ $spec->id }}
                            </td>

                            <td>
                                <div class="product-name">

                                    <div class="product-icon">
                                        ◈
                                    </div>

                                    <div>
                                        <strong>{{ $spec->name }}</strong>
                                        <small>Spesifikasi dinamis</small>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <span class="article">
                                    {{ $spec->article_code }}
                                </span>
                            </td>

                            <td class="field-col">
                                <span class="count-badge">
                                    {{ $fieldCount }}
                                </span>
                            </td>

                            <td>
                                <span class="image-badge">
                                    ◫ &nbsp;{{ $imageCount }} gambar
                                </span>
                            </td>

                            <td>
                                <div class="creator">

                                    <div class="avatar">
                                        {{ $initial }}
                                    </div>

                                    <span class="creator-name">
                                        {{ $creatorName }}
                                    </span>

                                </div>
                            </td>

                            <td>
                                <span class="date">
                                    {{ optional($spec->created_at)->format('d M Y H:i') }}
                                </span>
                            </td>

                            <td class="action-col">

                                <div class="actions">

                                    {{-- Edit --}}
                                    <a
                                        href="{{ url('/spek/' . $spec->id . '/edit') }}"
                                        class="icon-btn"
                                        title="Edit"
                                    >
                                        ✎
                                    </a>

                                    {{-- JSON --}}
                                    <button
                                        type="button"
                                        class="icon-btn btn-json"
                                        title="Lihat JSON"
                                        data-json='@json($data)'
                                    >
                                        { }
                                    </button>

                                    {{-- Delete --}}
                                    <form
                                        action="{{ url('/spek/' . $spec->id) }}"
                                        method="POST"
                                        class="delete-form"
                                        style="display:inline"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="icon-btn delete"
                                            title="Hapus"
                                        >
                                            ×
                                        </button>
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">

                                <div class="empty">

                                    <div class="empty-icon">
                                        ◈
                                    </div>

                                    <div style="font-weight:600;color:#475467;margin-bottom:4px;">
                                        Belum ada spesifikasi produk
                                    </div>

                                    <div>
                                        Tambahkan spesifikasi produk pertama Anda.
                                    </div>

                                    <div style="margin-top:13px;">
                                        <a
                                            href="{{ url('/spek/create') }}"
                                            class="btn btn-primary"
                                        >
                                            + Tambah Spesifikasi
                                        </a>
                                    </div>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        <div class="pagination-area">

            <span id="resultInfo">
                Menampilkan {{ $specifications->count() }} data
            </span>

            <span>
                Total {{ $specifications->count() }} spesifikasi
            </span>

        </div>

    </div>

</div>

{{-- JSON MODAL --}}
<div class="json-modal" id="jsonModal">

    <div class="modal-box">

        <div class="modal-head">

            <strong>JSON Spesifikasi</strong>

            <button
                type="button"
                class="modal-close"
                id="closeJsonModal"
            >
                ×
            </button>

        </div>

        <div class="modal-body">
            <pre class="json-code" id="jsonContent">{}</pre>
        </div>

        <div class="modal-foot">

            <button
                type="button"
                class="btn"
                id="closeJsonModalBottom"
            >
                Tutup
            </button>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    const searchInput = document.getElementById('searchSpecification');
    const resetSearch = document.getElementById('resetSearch');
    const rows = Array.from(document.querySelectorAll('.spec-row'));
    const resultInfo = document.getElementById('resultInfo');

    function filterRows() {

        const keyword = (searchInput.value || '').trim().toLowerCase();

        let visible = 0;

        rows.forEach(function (row) {

            const searchText = row.dataset.search || '';

            const matched = !keyword || searchText.includes(keyword);

            row.style.display = matched ? '' : 'none';

            if (matched) {
                visible++;
            }
        });

        if (resultInfo) {
            resultInfo.textContent =
                'Menampilkan ' + visible + ' dari ' + rows.length + ' data';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }

    if (resetSearch) {
        resetSearch.addEventListener('click', function () {

            searchInput.value = '';

            filterRows();

            searchInput.focus();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | JSON MODAL
    |--------------------------------------------------------------------------
    */

    const modal = document.getElementById('jsonModal');
    const jsonContent = document.getElementById('jsonContent');

    function openJson(data) {

        try {

            if (typeof data === 'string') {
                data = JSON.parse(data);
            }

            jsonContent.textContent =
                JSON.stringify(data, null, 4);

            modal.classList.add('show');

        } catch (error) {

            jsonContent.textContent =
                'JSON tidak dapat dibaca.';

            modal.classList.add('show');
        }
    }

    function closeJson() {
        modal.classList.remove('show');
    }

    document.querySelectorAll('.btn-json').forEach(function (button) {

        button.addEventListener('click', function () {

            openJson(this.dataset.json);
        });

    });

    document.getElementById('closeJsonModal')
        ?.addEventListener('click', closeJson);

    document.getElementById('closeJsonModalBottom')
        ?.addEventListener('click', closeJson);

    modal?.addEventListener('click', function (event) {

        if (event.target === modal) {
            closeJson();
        }

    });

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeJson();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | DELETE CONFIRM
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.delete-form').forEach(function (form) {

        form.addEventListener('submit', function (event) {

            const confirmed = confirm(
                'Hapus spesifikasi produk ini?'
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });

});
</script>

@endsection
