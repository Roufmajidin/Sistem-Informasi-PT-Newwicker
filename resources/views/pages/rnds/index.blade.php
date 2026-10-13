
@extends('master.master')

@section('btn')
<div class="rnd-breadcrumb">
    <span>R&D Archive</span>
    <span>›</span>
    <span>Sample Pictures</span>
</div>
@endsection

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="rnd-page">
    {{-- PAGE HEADER --}}
    <div class="rnd-page-header">
        <div>
            <div class="rnd-eyebrow">
                <i class="fa fa-cloud"></i> GOOGLE DRIVE ARCHIVE
            </div>
            <h2>R&D Sample Pictures</h2>
            <p>Kelola foto sampel berdasarkan Purchase Order dan artikel.</p>
        </div>

        <div class="rnd-cloud-badge">
            <span></span> Cloud Storage
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="rnd-toolbar">
        <div class="rnd-toolbar-title">
            <div class="rnd-toolbar-icon">
                <i class="fa fa-folder-open"></i>
            </div>
            <div>
                <strong>Daftar Purchase Order</strong>
                <small>Pilih PO untuk mengelola foto artikelnya.</small>
            </div>
        </div>

        <form method="GET"
              action="{{ route('rnd.sample-pictures') }}"
              class="rnd-search">
            <i class="fa fa-search"></i>

            <input type="text"
                   name="search"
                   value="{{ $search ?? '' }}"
                   placeholder="Cari nomor PO atau buyer..."
                   autocomplete="off">

            @if(!empty($search))
                <a href="{{ route('rnd.sample-pictures') }}"
                   class="rnd-search-clear"
                   title="Hapus pencarian">
                    <i class="fa fa-times"></i>
                </a>
            @endif

            <button type="submit">Cari</button>
        </form>
    </div>

    {{-- PO TABLE --}}
    <div class="rnd-card">
        <div class="rnd-table-scroll">
            <table class="rnd-table">
                <thead>
                    <tr>
                        <th width="55">No.</th>
                        <th>Nomor PO</th>
                        <th>Buyer</th>
                        <th>Tanggal PO</th>
                        <th class="text-center">Total Artikel</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pos as $index => $po)
                        <tr>
                            <td class="rnd-muted">
                                {{ $pos->firstItem() + $index }}
                            </td>

                            <td>
                                <div class="rnd-po-cell">
                                    <div class="rnd-po-icon">
                                        <i class="fa fa-file-text-o"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $po->order_no ?: '-' }}</strong>
                                        <small>Purchase Order</small>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $po->company_name ?: '-' }}</td>

                            <td>
                                {{ $po->release_date
                                    ? \Carbon\Carbon::parse($po->release_date)->format('d M Y')
                                    : '-' }}
                            </td>

                            <td class="text-center">
                                <span class="rnd-count">
                                    <i class="fa fa-cube"></i>
                                    {{ $po->detail_pos_count ?? 0 }} artikel
                                </span>
                            </td>

                            <td class="text-center">
                                <button type="button"
                                    class="rnd-btn-primary js-open-drawer"
                                    data-po="{{ $po->order_no }}"
                                    data-buyer="{{ $po->company_name }}"
                                    data-date="{{ $po->release_date
                                        ? \Carbon\Carbon::parse($po->release_date)->format('d M Y')
                                        : '-' }}"
                                    data-items-url="{{ route('rnd.sample-pictures.items', $po->id) }}">
                                    <i class="fa fa-folder-open"></i>
                                    Kelola Foto
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="rnd-empty">
                                    <i class="fa fa-folder-open-o"></i>
                                    <strong>PO tidak ditemukan</strong>
                                    <p>
                                        @if(!empty($search))
                                            Coba kata kunci yang berbeda.
                                        @else
                                            Belum ada PO NWS atau NWD.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rnd-table-footer">
            <span>
                Menampilkan
                <strong>{{ $pos->firstItem() ?? 0 }}–{{ $pos->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $pos->total() }}</strong> PO
            </span>

            <div class="rnd-pagination">
                {{ $pos->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

{{-- DRAWER BACKDROP --}}
<div id="rndDrawerBackdrop" class="rnd-backdrop"></div>

{{-- DRAWER --}}
<aside id="rndDrawer" class="rnd-drawer" aria-hidden="true">
    <div class="rnd-drawer-header">
        <div>
            <div class="rnd-drawer-breadcrumb">
                R&D Archive <span>›</span> Purchase Order <span>›</span> Artikel
            </div>
            <h3>Kelola Foto Sampel</h3>
            <p id="rndDrawerSubtitle">Daftar artikel dari Purchase Order.</p>
        </div>

        <button type="button" id="rndCloseDrawer"
                class="rnd-icon-button" aria-label="Tutup">
            <i class="fa fa-times"></i>
        </button>
    </div>

    {{-- PO SUMMARY --}}
    <div class="rnd-po-summary">
        <div class="rnd-summary-item">
            <div class="rnd-summary-icon"><i class="fa fa-file-text-o"></i></div>
            <div>
                <small>Nomor PO</small>
                <strong id="rndDrawerPo">-</strong>
            </div>
        </div>

        <div class="rnd-summary-item">
            <div class="rnd-summary-icon"><i class="fa fa-building-o"></i></div>
            <div>
                <small>Buyer</small>
                <strong id="rndDrawerBuyer">-</strong>
            </div>
        </div>

        <div class="rnd-summary-item">
            <div class="rnd-summary-icon"><i class="fa fa-cube"></i></div>
            <div>
                <small>Total Artikel</small>
                <strong id="rndDrawerTotal">0</strong>
            </div>
        </div>
    </div>

    {{-- ARTICLE LIST --}}
    <div id="rndDetailSection" class="rnd-detail-section">
        <div class="rnd-section-heading">
            <div>
                <h4>Daftar Artikel</h4>
                <p>Pilih artikel untuk melihat dan mengunggah foto.</p>
            </div>
            <span class="rnd-section-tag">ARTIKEL PO</span>
        </div>

        <div class="rnd-detail-table-wrapper">
            <table class="rnd-detail-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Article Code</th>
                        <th>Description</th>
                        <th>Folder</th>
                        <th class="text-center">Foto</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody id="rndDetailItems">
                    <tr>
                        <td colspan="6" class="rnd-message">
                            Pilih PO untuk memuat artikel.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ARTICLE ARCHIVE --}}
    <div id="rndArchivePanel" class="rnd-archive-panel" hidden>
        <div class="rnd-archive-navigation">
            <button type="button" id="rndBackToItems" class="rnd-back-button">
                <i class="fa fa-arrow-left"></i> Daftar Artikel
            </button>

            <a id="rndOpenDriveFolder"
               href="#"
               target="_blank"
               rel="noopener noreferrer"
               class="rnd-drive-link"
               hidden>
                <i class="fa fa-external-link"></i> Buka Google Drive
            </a>
        </div>

        <div class="rnd-article-banner">
            <div class="rnd-article-banner-icon">
                <i class="fa fa-picture-o"></i>
            </div>
            <div>
                <small>ARSIP ARTIKEL</small>
                <h4 id="rndArchiveTitle">-</h4>
                <p id="rndArchiveSubtitle">-</p>
            </div>
        </div>

        <div id="rndArchiveFolderStatus" class="rnd-folder-status">
            <i class="fa fa-circle-o-notch fa-spin"></i>
            Menyiapkan folder Google Drive...
        </div>

        {{-- UPLOAD FORM --}}
        <form id="rndUploadForm" enctype="multipart/form-data">
            <div class="rnd-upload-heading">
                <div>
                    <h4>Upload Foto</h4>
                    <p>Pilih satu atau beberapa foto sampel.</p>
                </div>
                <i class="fa fa-cloud-upload rnd-upload-heading-icon"></i>
            </div>

            <label for="rndPhotoInput" class="rnd-file-picker">
                <i class="fa fa-images"></i>
                <strong>Klik untuk memilih foto</strong>
                <span>JPG, JPEG, PNG atau WEBP · Maksimal 10 MB per foto</span>

                <input type="file"
                       id="rndPhotoInput"
                       name="photos[]"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                       multiple
                       required>
            </label>

            <div id="rndSelectedFiles" class="rnd-selected-files">
                Belum ada foto dipilih.
            </div>

            {{-- TOMBOL UPLOAD SELALU TERSEDIA --}}
            <div class="rnd-upload-actions">
                <div id="rndUploadMessage" role="status" aria-live="polite"></div>

                <button type="submit"
                        id="rndUploadButton"
                        class="rnd-btn-primary">
                    <i class="fa fa-cloud-upload"></i>
                    <span>Upload Foto</span>
                </button>
            </div>

            <div id="rndUploadProgress" class="rnd-upload-progress" hidden>
                <div class="rnd-progress-track">
                    <div id="rndProgressBar" class="rnd-progress-bar"></div>
                </div>
                <small id="rndProgressText">Mengunggah foto...</small>
            </div>
        </form>

        {{-- PHOTO LIST --}}
        <div class="rnd-gallery-heading">
            <div>
                <h4>Foto Tersimpan</h4>
                <p>File yang telah berhasil disimpan ke Google Drive.</p>
            </div>
            <span id="rndArchiveFileCount" class="rnd-gallery-count">0 foto</span>
        </div>

        <div id="rndArchiveFiles" class="rnd-photo-grid">
            <div class="rnd-message">Belum ada foto.</div>
        </div>
    </div>

    <div class="rnd-drawer-footer">
        <span><i class="fa fa-shield"></i> NewWicker R&D Archive</span>
        <button type="button" id="rndFooterClose" class="rnd-footer-button">
            <i class="fa fa-arrow-left"></i> Tutup
        </button>
    </div>
</aside>

<style>
.rnd-page,
.rnd-page *,
.rnd-drawer,
.rnd-drawer * {
    box-sizing: border-box;
}

.rnd-page {
    --primary: #1769e0;
    --primary-dark: #1053b5;
    --text: #17243a;
    --muted: #718096;
    --border: #e5eaf2;
    padding: 22px 18px 32px;
    color: var(--text);
    font-family: Arial, sans-serif;
}

.rnd-breadcrumb {
    display: flex;
    gap: 10px;
    padding: 13px 22px;
    color: #64748b;
    font-size: 12px;
}

.rnd-page-header,
.rnd-toolbar,
.rnd-drawer-header,
.rnd-section-heading,
.rnd-upload-heading,
.rnd-gallery-heading,
.rnd-table-footer,
.rnd-drawer-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.rnd-page-header { margin-bottom: 24px; }

.rnd-eyebrow {
    margin-bottom: 9px;
    color: var(--primary);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
}

.rnd-page-header h2 {
    margin: 0 0 7px;
    font-size: 25px;
    font-weight: 800;
}

.rnd-page-header p,
.rnd-toolbar-title small,
.rnd-drawer-header p,
.rnd-section-heading p,
.rnd-upload-heading p,
.rnd-gallery-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.6;
}

.rnd-cloud-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    border: 1px solid #d9f0e4;
    border-radius: 30px;
    background: #f1fbf5;
    color: #18794e;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.rnd-cloud-badge span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22a06b;
}

.rnd-toolbar {
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.rnd-toolbar-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.rnd-toolbar-title strong {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
}

.rnd-toolbar-icon,
.rnd-po-icon,
.rnd-summary-icon,
.rnd-article-banner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 9px;
    background: #eff6ff;
    color: var(--primary);
}

.rnd-toolbar-icon { width: 42px; height: 42px; font-size: 17px; }

.rnd-search {
    position: relative;
    display: flex;
    align-items: center;
    width: 390px;
    max-width: 100%;
    margin: 0;
}

.rnd-search > i {
    position: absolute;
    left: 13px;
    color: #94a3b8;
}

.rnd-search input {
    width: 100%;
    height: 40px;
    padding: 8px 78px 8px 36px;
    border: 1px solid #dce4ef;
    border-radius: 8px;
    outline: none;
    font-size: 12px;
}

.rnd-search input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(23,105,224,.09);
}

.rnd-search button {
    position: absolute;
    right: 5px;
    height: 30px;
    padding: 0 12px;
    border: 0;
    border-radius: 5px;
    background: var(--primary);
    color: #fff;
    font-size: 11px;
    cursor: pointer;
}

.rnd-search-clear {
    position: absolute;
    right: 57px;
    color: #94a3b8;
}

.rnd-card {
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
}

.rnd-table-scroll { width: 100%; overflow-x: auto; }

.rnd-table {
    width: 100%;
    min-width: 790px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12px;
    white-space: nowrap;
}

.rnd-table thead th,
.rnd-detail-table thead th {
    padding: 14px;
    border: 0;
    border-bottom: 1px solid var(--border);
    background: #f7f9fc;
    color: #536278;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.rnd-table tbody td {
    height: 62px;
    padding: 11px 14px;
    border: 0;
    border-bottom: 1px solid #eef1f6;
    vertical-align: middle;
}

.rnd-table tbody tr:last-child td { border-bottom: 0; }
.rnd-table tbody tr:hover { background: #fbfdff; }

.rnd-muted { color: #94a3b8; }

.rnd-po-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rnd-po-icon { width: 34px; height: 34px; }

.rnd-po-cell strong { display: block; font-size: 12px; }
.rnd-po-cell small { display: block; margin-top: 5px; color: #94a3b8; font-size: 10px; }

.rnd-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border-radius: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 700;
}

.rnd-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 36px;
    padding: 9px 13px;
    border: 1px solid var(--primary);
    border-radius: 7px;
    background: var(--primary);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background .15s;
}

.rnd-btn-primary:hover { background: var(--primary-dark); color: #fff; }
.rnd-btn-primary:disabled { opacity: .6; cursor: wait; }

.rnd-empty,
.rnd-message {
    padding: 30px;
    color: #94a3b8;
    text-align: center;
    font-size: 11px;
}

.rnd-empty i {
    display: block;
    margin-bottom: 12px;
    font-size: 28px;
}

.rnd-empty strong { color: #334155; }
.rnd-empty p { margin-top: 7px; }

.rnd-table-footer {
    flex-wrap: wrap;
    padding: 14px 17px;
    border-top: 1px solid var(--border);
    color: #718096;
    font-size: 11px;
}

.rnd-pagination nav > div:first-child { display: none; }
.rnd-pagination .pagination { display: flex; gap: 4px; margin: 0; }
.rnd-pagination .page-link {
    padding: 6px 9px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #475569;
    font-size: 11px;
    box-shadow: none;
}
.rnd-pagination .page-item.active .page-link {
    border-color: var(--primary);
    background: var(--primary);
    color: #fff;
}

/* DRAWER */
.rnd-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9989;
    background: rgba(15,23,42,.35);
    opacity: 0;
    visibility: hidden;
    transition: .2s;
}

.rnd-backdrop.show { opacity: 1; visibility: visible; }

.rnd-drawer {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 9991;
    display: flex;
    flex-direction: column;
    width: min(940px, 82vw);
    height: 100vh;
    height: 100dvh;
    padding: 20px;
    border-left: 1px solid var(--border);
    background: #fff;
    box-shadow: -15px 0 45px rgba(15,23,42,.13);
    transform: translateX(105%);
    visibility: hidden;
    transition: transform .25s ease, visibility .25s;
}

.rnd-drawer.open { transform: translateX(0); visibility: visible; }

.rnd-drawer-header {
    align-items: flex-start;
    flex-shrink: 0;
    margin-bottom: 16px;
}

.rnd-drawer-breadcrumb { margin-bottom: 8px; color: #94a3b8; font-size: 10px; }
.rnd-drawer-breadcrumb span { padding: 0 4px; }
.rnd-drawer-header h3 { margin: 0 0 6px; font-size: 21px; font-weight: 800; }

.rnd-icon-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #64748b;
    cursor: pointer;
}

.rnd-po-summary {
    display: grid;
    grid-template-columns: 1fr 1.3fr .7fr;
    gap: 12px;
    flex-shrink: 0;
    padding: 14px;
    margin-bottom: 18px;
    border: 1px solid #e0ebfa;
    border-radius: 10px;
    background: linear-gradient(110deg,#f0f7ff,#fbfdff);
}

.rnd-summary-item { display: flex; align-items: center; gap: 9px; min-width: 0; }
.rnd-summary-icon { width: 34px; height: 34px; background: #fff; }
.rnd-summary-item small { display: block; margin-bottom: 5px; color: #718096; font-size: 10px; }
.rnd-summary-item strong { display: block; overflow-wrap: anywhere; font-size: 11px; }

.rnd-detail-section { display: flex; flex-direction: column; flex: 1; min-height: 0; }
.rnd-section-heading { flex-shrink: 0; margin-bottom: 12px; }
.rnd-section-heading h4,
.rnd-gallery-heading h4 { margin: 0 0 5px; font-size: 14px; font-weight: 800; }

.rnd-section-tag {
    padding: 6px 8px;
    border-radius: 5px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 9px;
    font-weight: 800;
}

.rnd-detail-table-wrapper {
    flex: 1;
    min-height: 0;
    overflow: auto;
    border: 1px solid var(--border);
    border-radius: 9px;
}

.rnd-detail-table {
    width: 100%;
    min-width: 680px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11px;
}

.rnd-detail-table thead { position: sticky; top: 0; z-index: 2; }
.rnd-detail-table thead th { padding: 12px 10px; font-size: 9px; }
.rnd-detail-table tbody td { padding: 12px 10px; border-bottom: 1px solid #eef1f6; }
.rnd-article-code { color: #334155; font-weight: 700; }
.rnd-item-description { max-width: 230px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.rnd-folder-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 7px;
    border-radius: 5px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 9px;
    white-space: nowrap;
}
.rnd-folder-pill.ready { background: #dcfce7; color: #166534; }

.rnd-open-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 9px;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}
.rnd-open-btn:disabled { opacity: .5; cursor: not-allowed; }

/* ARCHIVE */
.rnd-archive-panel {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 2px 1px 10px;
}
.rnd-archive-panel[hidden] { display: none !important; }

.rnd-archive-navigation { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }

.rnd-back-button,
.rnd-drive-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.rnd-drive-link { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }

.rnd-article-banner {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 15px;
    margin-bottom: 11px;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: linear-gradient(110deg,#eff6ff,#f8fbff);
}
.rnd-article-banner-icon { width: 44px; height: 44px; background: #fff; font-size: 20px; }
.rnd-article-banner small { display: block; margin-bottom: 5px; color: #2563eb; font-size: 9px; font-weight: 800; }
.rnd-article-banner h4 { margin: 0 0 5px; font-size: 14px; overflow-wrap: anywhere; }
.rnd-article-banner p { margin: 0; color: #64748b; font-size: 10px; overflow-wrap: anywhere; }

.rnd-folder-status {
    padding: 10px 12px;
    margin-bottom: 15px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #f8fafc;
    color: #64748b;
    font-size: 10px;
}
.rnd-folder-status.ready { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
.rnd-folder-status.error { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }

/* UPLOAD FORM */
#rndUploadForm {
    display: block !important;
    padding: 15px;
    margin: 0 0 22px;
    border: 1px solid #e5eaf2;
    border-radius: 10px;
    background: #fff;
}

.rnd-upload-heading { margin-bottom: 14px; }
.rnd-upload-heading h4 { margin: 0 0 5px; font-size: 13px; font-weight: 800; }
.rnd-upload-heading-icon { color: var(--primary); font-size: 20px; }

.rnd-file-picker {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 22px 12px;
    border: 1px dashed #b9cce7;
    border-radius: 8px;
    background: #f8fbff;
    text-align: center;
    cursor: pointer;
}
.rnd-file-picker > i { color: var(--primary); font-size: 25px; }
.rnd-file-picker strong { color: #334155; font-size: 11px; }
.rnd-file-picker span { color: #94a3b8; font-size: 10px; }
.rnd-file-picker input { display: block; width: 100%; max-width: 300px; font-size: 10px; cursor: pointer; }

.rnd-selected-files {
    margin-top: 9px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

/* FIX UTAMA: TOMBOL TIDAK BOLEH HILANG */
#rndUploadForm .rnd-upload-actions {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    min-height: 48px;
    padding-top: 12px;
    margin-top: 12px;
    border-top: 1px solid #edf1f6;
    visibility: visible !important;
    opacity: 1 !important;
}

#rndUploadForm #rndUploadMessage {
    display: block !important;
    flex: 1;
    min-width: 150px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

#rndUploadForm #rndUploadMessage.success { color: #15803d; }
#rndUploadForm #rndUploadMessage.error { color: #dc2626; }

#rndUploadForm #rndUploadButton {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-shrink: 0;
    min-height: 38px;
    padding: 10px 16px;
    border: 1px solid #1769e0 !important;
    border-radius: 7px;
    background: #1769e0 !important;
    color: #fff !important;
    font-size: 11px;
    font-weight: 700;
    visibility: visible !important;
    opacity: 1 !important;
    cursor: pointer;
}
#rndUploadForm #rndUploadButton:disabled { opacity: .65 !important; cursor: wait; }

.rnd-upload-progress { margin-top: 12px; }
.rnd-upload-progress[hidden] { display: none !important; }
.rnd-progress-track { height: 6px; overflow: hidden; border-radius: 20px; background: #e2e8f0; }
.rnd-progress-bar { width: 0; height: 100%; border-radius: inherit; background: #1769e0; transition: width .2s; }
.rnd-upload-progress small { display: block; margin-top: 6px; color: #64748b; font-size: 10px; }

.rnd-gallery-heading { margin-bottom: 12px; }
.rnd-gallery-count { padding: 6px 9px; border-radius: 6px; background: #eff6ff; color: #1d4ed8; font-size: 10px; font-weight: 800; white-space: nowrap; }

.rnd-photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(150px,1fr));
    gap: 11px;
}
.rnd-photo-card { min-width: 0; padding: 12px; border: 1px solid #e5eaf2; border-radius: 8px; background: #fff; }
.rnd-photo-card-icon { display: flex; align-items: center; justify-content: center; height: 62px; margin-bottom: 10px; border-radius: 6px; background: #f1f5f9; color: #64748b; font-size: 25px; }
.rnd-photo-card a { display: block; color: #2563eb; font-size: 10px; font-weight: 700; overflow-wrap: anywhere; text-decoration: none; }
.rnd-photo-card a:hover { text-decoration: underline; }
.rnd-photo-card small { display: block; margin-top: 6px; color: #94a3b8; font-size: 9px; }
.rnd-file-type { display: inline-block; margin-top: 8px; padding: 4px 6px; border-radius: 4px; background: #f1f5f9; color: #64748b; font-size: 8px; text-transform: uppercase; }

.rnd-drawer-footer {
    flex-shrink: 0;
    padding-top: 13px;
    margin-top: 13px;
    border-top: 1px solid #edf1f6;
    color: #94a3b8;
    font-size: 10px;
}
.rnd-footer-button { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; color: #475569; font-size: 10px; cursor: pointer; }

@media (max-width: 900px) {
    .rnd-drawer { width: 94vw; }
}
@media (max-width: 600px) {
    .rnd-page { padding: 14px 9px 25px; }
    .rnd-page-header { align-items: flex-start; }
    .rnd-page-header h2 { font-size: 21px; }
    .rnd-toolbar { align-items: stretch; }
    .rnd-search { width: 100%; }
    .rnd-drawer { width: 100%; padding: 13px; }
    .rnd-po-summary { grid-template-columns: 1fr; gap: 12px; }
    .rnd-summary-item strong { white-space: normal; }
    .rnd-photo-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
}
</style>

<script>
(function () {
    'use strict';

    const drawer = document.getElementById('rndDrawer');
    const backdrop = document.getElementById('rndDrawerBackdrop');
    const closeDrawerButton = document.getElementById('rndCloseDrawer');
    const footerCloseButton = document.getElementById('rndFooterClose');

    const detailSection = document.getElementById('rndDetailSection');
    const detailBody = document.getElementById('rndDetailItems');
    const drawerSubtitle = document.getElementById('rndDrawerSubtitle');
    const drawerPo = document.getElementById('rndDrawerPo');
    const drawerBuyer = document.getElementById('rndDrawerBuyer');
    const drawerTotal = document.getElementById('rndDrawerTotal');

    const archivePanel = document.getElementById('rndArchivePanel');
    const archiveTitle = document.getElementById('rndArchiveTitle');
    const archiveSubtitle = document.getElementById('rndArchiveSubtitle');
    const folderStatus = document.getElementById('rndArchiveFolderStatus');
    const driveLink = document.getElementById('rndOpenDriveFolder');
    const archiveFiles = document.getElementById('rndArchiveFiles');
    const fileCount = document.getElementById('rndArchiveFileCount');

    const uploadForm = document.getElementById('rndUploadForm');
    const photoInput = document.getElementById('rndPhotoInput');
    const selectedFiles = document.getElementById('rndSelectedFiles');
    const uploadButton = document.getElementById('rndUploadButton');
    const uploadMessage = document.getElementById('rndUploadMessage');
    const uploadProgress = document.getElementById('rndUploadProgress');
    const progressBar = document.getElementById('rndProgressBar');
    const progressText = document.getElementById('rndProgressText');

    const backToItems = document.getElementById('rndBackToItems');

    let activeItemsUrl = null;
    let activeArchiveUrl = null;
    let activeUploadUrl = null;
    let activeRequest = null;
    let previousFocus = null;
    let uploadInProgress = false;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function setUploadMessage(message, type = '') {
        uploadMessage.textContent = message || '';
        uploadMessage.className = type;
    }

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {})
            }
        });

        const raw = await response.text();
        let data;

        try {
            data = JSON.parse(raw);
        } catch (error) {
            console.error('Respons server bukan JSON:', {
                url: response.url,
                status: response.status,
                response: raw.substring(0, 1200)
            });

            throw new Error(
                'Server tidak mengirim JSON. Periksa Console dan log Laravel.'
            );
        }

        if (!response.ok || data.success === false) {
            const validationMessage = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : '';

            throw new Error(
                validationMessage || data.message || 'Permintaan gagal.'
            );
        }

        return data;
    }

    function showDrawer() {
        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeArchive() {
        if (activeRequest) {
            activeRequest.abort();
            activeRequest = null;
        }

        archivePanel.hidden = true;
        detailSection.hidden = false;
        activeArchiveUrl = null;
        activeUploadUrl = null;

        if (!uploadInProgress) {
            photoInput.value = '';
            selectedFiles.textContent = 'Belum ada foto dipilih.';
            setUploadMessage('');
        }
    }

    function closeDrawer() {
        if (uploadInProgress) {
            setUploadMessage(
                'Tunggu sampai upload selesai sebelum menutup panel.',
                'error'
            );
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
            activeRequest = null;
        }

        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';

        closeArchive();

        if (previousFocus && document.contains(previousFocus)) {
            previousFocus.focus();
        }
    }

    async function openDrawer(button) {
        if (uploadInProgress) return;

        previousFocus = button;
        closeArchive();

        activeItemsUrl = button.dataset.itemsUrl;
        drawerPo.textContent = button.dataset.po || '-';
        drawerBuyer.textContent = button.dataset.buyer || '-';
        drawerTotal.textContent = 'Memuat...';
        drawerSubtitle.textContent = 'Daftar artikel dari Purchase Order.';

        showDrawer();
        detailBody.innerHTML =
            '<tr><td colspan="6" class="rnd-message">Memuat artikel...</td></tr>';

        if (activeRequest) activeRequest.abort();
        activeRequest = new AbortController();

        try {
            const result = await fetchJson(activeItemsUrl, {
                signal: activeRequest.signal
            });

            renderItems(result.items || []);
        } catch (error) {
            if (error.name === 'AbortError') return;

            drawerTotal.textContent = 'Gagal';
            detailBody.innerHTML =
                `<tr><td colspan="6" class="rnd-message">${escapeHtml(error.message)}</td></tr>`;
        }
    }

    function renderItems(items) {
        drawerTotal.textContent = items.length + ' artikel';

        if (!items.length) {
            detailBody.innerHTML =
                '<tr><td colspan="6" class="rnd-message">PO ini belum memiliki artikel.</td></tr>';
            return;
        }

        detailBody.innerHTML = items.map(function (item, index) {
            const code = escapeHtml(item.article_code || '-');
            const description = escapeHtml(item.description || '-');
            const ready = Boolean(item.folder_exists);

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td><span class="rnd-article-code">${code}</span></td>
                    <td>
                        <div class="rnd-item-description" title="${description}">
                            ${description}
                        </div>
                    </td>
                    <td>
                        <span class="rnd-folder-pill ${ready ? 'ready' : ''}">
                            <i class="fa ${ready ? 'fa-check-circle' : 'fa-folder-o'}"></i>
                            ${ready ? 'Folder siap' : 'Belum dibuat'}
                        </span>
                    </td>
                    <td class="text-center">${Number(item.file_count || 0)}</td>
                    <td class="text-center">
                        <button type="button"
                            class="rnd-open-btn js-open-archive"
                            data-archive-url="${escapeHtml(item.archive_url)}"
                            data-upload-url="${escapeHtml(item.upload_url)}"
                            data-code="${code}"
                            data-description="${description}"
                            ${item.article_code ? '' : 'disabled'}>
                            <i class="fa fa-folder-open"></i> Buka
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function openArticleArchive(button) {
        if (uploadInProgress) return;

        activeArchiveUrl = button.dataset.archiveUrl;
        activeUploadUrl = button.dataset.uploadUrl;

        detailSection.hidden = true;
        archivePanel.hidden = false;

        archiveTitle.textContent = button.dataset.code || 'Arsip Artikel';
        archiveSubtitle.textContent = button.dataset.description || '';
        folderStatus.className = 'rnd-folder-status';
        folderStatus.innerHTML =
            '<i class="fa fa-circle-o-notch fa-spin"></i> Menyiapkan folder Google Drive...';

        driveLink.hidden = true;
        archiveFiles.innerHTML =
            '<div class="rnd-message">Memuat foto...</div>';
        fileCount.textContent = 'Memuat...';

        photoInput.value = '';
        selectedFiles.textContent = 'Belum ada foto dipilih.';
        setUploadMessage('');

        if (activeRequest) activeRequest.abort();
        activeRequest = new AbortController();

        try {
            const result = await fetchJson(activeArchiveUrl, {
                signal: activeRequest.signal
            });

            folderStatus.className = 'rnd-folder-status ready';
            folderStatus.innerHTML =
                '<i class="fa fa-check-circle"></i> Folder artikel siap di Google Drive';

            if (result.folder && result.folder.url) {
                driveLink.href = result.folder.url;
                driveLink.hidden = false;
            }

            renderArchiveFiles(result.files || []);
        } catch (error) {
            if (error.name === 'AbortError') return;

            folderStatus.className = 'rnd-folder-status error';
            folderStatus.textContent = error.message;
            archiveFiles.innerHTML =
                '<div class="rnd-message">Arsip tidak dapat dimuat.</div>';
            fileCount.textContent = '0 foto';
        }
    }

    function renderArchiveFiles(files) {
        fileCount.textContent = files.length + ' foto';

        if (!files.length) {
            archiveFiles.innerHTML =
                '<div class="rnd-message">Belum ada foto. Upload foto pertama untuk artikel ini.</div>';
            return;
        }

        archiveFiles.innerHTML = files.map(function (file) {
            const name = escapeHtml(file.name || 'Foto sampel');
            const url = file.url ? escapeHtml(file.url) : '';
            const size = Number(file.file_size || 0);
            const sizeText = size > 0
                ? (size / 1048576).toFixed(2) + ' MB'
                : 'Ukuran tidak tersedia';

            const link = url
                ? `<a href="${url}" target="_blank" rel="noopener noreferrer">${name}</a>`
                : `<span>${name}</span>`;

            return `
                <div class="rnd-photo-card">
                    <div class="rnd-photo-card-icon">
                        <i class="fa fa-file-image-o"></i>
                    </div>
                    ${link}
                    <small>${sizeText}</small>
                    <span class="rnd-file-type">${escapeHtml(file.mime_type || 'image')}</span>
                </div>
            `;
        }).join('');
    }

    photoInput.addEventListener('change', function () {
        const files = Array.from(photoInput.files || []);

        if (!files.length) {
            selectedFiles.textContent = 'Belum ada foto dipilih.';
            return;
        }

        selectedFiles.textContent = files.length + ' foto dipilih: ' +
            files.map(file => file.name).join(', ');

        setUploadMessage('');
    });

    function validatePhotos(files) {
        if (!files.length) {
            throw new Error('Pilih minimal satu foto terlebih dahulu.');
        }

        if (files.length > 10) {
            throw new Error('Maksimal 10 foto dalam satu kali upload.');
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        for (const file of files) {
            if (!allowedTypes.includes(file.type)) {
                throw new Error('Format foto tidak didukung: ' + file.name);
            }

            if (file.size > 10 * 1024 * 1024) {
                throw new Error('Ukuran foto melebihi 10 MB: ' + file.name);
            }
        }
    }

    uploadForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (uploadInProgress) return;

        const files = Array.from(photoInput.files || []);

        try {
            if (!activeUploadUrl) {
                throw new Error('Buka artikel terlebih dahulu.');
            }

            validatePhotos(files);
        } catch (error) {
            setUploadMessage(error.message, 'error');
            return;
        }

        const formData = new FormData();
        files.forEach(file => formData.append('photos[]', file));

        uploadInProgress = true;
        uploadButton.disabled = true;
        uploadButton.innerHTML =
            '<i class="fa fa-circle-o-notch fa-spin"></i><span>Mengunggah...</span>';

        uploadProgress.hidden = false;
        progressBar.style.width = '25%';
        progressText.textContent = 'Mengirim foto ke server...';
        setUploadMessage('Upload sedang berjalan. Jangan tutup panel.', '');

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]');

            const result = await fetchJson(activeUploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : ''
                },
                body: formData
            });

            progressBar.style.width = '100%';
            progressText.textContent = 'Upload selesai.';
            setUploadMessage(
                result.message || 'Foto berhasil diunggah.',
                'success'
            );

            photoInput.value = '';
            selectedFiles.textContent = 'Upload berhasil. Pilih foto lain jika diperlukan.';

            await refreshArchive();
            await refreshItems();
        } catch (error) {
            console.error('Upload foto gagal:', error);
            progressBar.style.width = '100%';
            progressText.textContent = 'Upload gagal.';
            setUploadMessage(error.message || 'Upload gagal.', 'error');
        } finally {
            uploadInProgress = false;
            uploadButton.disabled = false;
            uploadButton.innerHTML =
                '<i class="fa fa-cloud-upload"></i><span>Upload Foto</span>';

            setTimeout(function () {
                uploadProgress.hidden = true;
                progressBar.style.width = '0%';
            }, 1800);
        }
    });

    async function refreshArchive() {
        if (!activeArchiveUrl) return;

        const result = await fetchJson(activeArchiveUrl);
        renderArchiveFiles(result.files || []);

        if (result.folder && result.folder.url) {
            driveLink.href = result.folder.url;
            driveLink.hidden = false;
        }
    }

    async function refreshItems() {
        if (!activeItemsUrl) return;

        const result = await fetchJson(activeItemsUrl);
        renderItems(result.items || []);
    }

    document.addEventListener('click', function (event) {
        const poButton = event.target.closest('.js-open-drawer');

        if (poButton) {
            openDrawer(poButton);
            return;
        }

        const articleButton = event.target.closest('.js-open-archive');

        if (articleButton) {
            openArticleArchive(articleButton);
        }
    });

    closeDrawerButton.addEventListener('click', closeDrawer);
    footerCloseButton.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    backToItems.addEventListener('click', function () {
        if (uploadInProgress) return;
        closeArchive();
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            drawer.classList.contains('open')
        ) {
            closeDrawer();
        }
    });
})();
</script>
@endsection
