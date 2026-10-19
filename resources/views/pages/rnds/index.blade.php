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

         


{{-- 
            <div class="rnd-cloud-badge">

                <span></span> Cloud Storage

            </div> --}}

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



            <form method="GET" action="{{ route('rnd.sample-pictures') }}" class="rnd-search">

                <i class="fa fa-search"></i>



                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari PO, Article Code, nama barang, atau nama file..."
                    autocomplete="off">



                @if (!empty($search))
                    <a href="{{ route('rnd.sample-pictures') }}" class="rnd-search-clear" title="Hapus pencarian">

                        <i class="fa fa-times"></i>

                    </a>
                @endif



                <button type="submit">Cari</button>

            </form>

        </div>



        @if (!empty($search))
            <div class="rnd-search-result" role="status">
                <i class="fa fa-search"></i>
                Found <strong>{{ $pos->total() }}</strong> records untuk "{{ $search }}"
            </div>
        @endif

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

                                    {{ $po->release_date ? \Carbon\Carbon::parse($po->release_date)->format('d M Y') : '-' }}

                                </td>



                                <td class="text-center">

                                    <span class="rnd-count">

                                        <i class="fa fa-cube"></i>

                                        {{ $po->detail_pos_count ?? 0 }} artikel

                                    </span>

                                </td>



                                <td class="text-center">

                                    <button type="button" class="rnd-btn-primary js-open-drawer"
                                        data-po="{{ $po->order_no }}" data-buyer="{{ $po->company_name }}"
                                        data-date="{{ $po->release_date ? \Carbon\Carbon::parse($po->release_date)->format('d M Y') : '-' }}"
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

                                            @if (!empty($search))
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

                <H6>Kelola Foto Sampel</H6>

                <p id="rndDrawerSubtitle" class="rnd-sr-only">Daftar artikel dari Purchase Order.</p>

            </div>



            <button type="button" id="rndCloseDrawer" class="rnd-icon-button" aria-label="Tutup">

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



            <div class="rnd-inline-search">
                <i class="fa fa-search"></i>
                <input id="rndArticleSearch" type="search" placeholder="Cari Article Code atau nama barang..." autocomplete="off">
                <span id="rndArticleSearchCount" aria-live="polite">Found 0 records</span>
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

            {{-- Compact article context: original IDs preserved for existing JavaScript --}}
            <div class="rnd-archive-topbar">
                {{-- Ringkasan PO + buyer, hanya pada tampilan artikel aktif --}}
                <div class="rnd-compact-po" title="Purchase Order dan Buyer">
                    <span class="rnd-compact-po-icon"><i class="fa fa-file-text-o"></i></span>
                    <div class="rnd-compact-po-copy">
                        <strong id="rndCompactPo">-</strong>
                        <small id="rndCompactBuyer">-</small>
                    </div>
                    <i class="fa fa-angle-right rnd-compact-divider" aria-hidden="true"></i>
                </div>
                <div class="rnd-article-banner">
                    <div class="rnd-article-banner-icon"><i class="fa fa-picture-o"></i></div>
                    <div class="rnd-article-banner-copy">
                        <small>ARTIKEL AKTIF</small>
                        <h4 id="rndArchiveTitle">-</h4>
                        <p id="rndArchiveSubtitle">-</p>
                    </div>
                </div>
                <div id="rndArchiveFolderStatus" class="rnd-folder-status" role="status">
                    <i class="fa fa-circle-o-notch fa-spin"></i> Menyiapkan folder Google Drive...
                </div>
                <div class="rnd-archive-navigation">
                    <button type="button" id="rndBackToItems" class="rnd-back-button">
                        <i class="fa fa-arrow-left"></i> Daftar Artikel
                    </button>
                    <a id="rndOpenDriveFolder" href="#" target="_blank" rel="noopener noreferrer"
                        class="rnd-drive-link" hidden>
                        {{-- <i class="fa fa-external-link"></i> Buka Google Drive --}}
                    </a>
                </div>
            </div>

            {{-- Loading focus while Laravel prepares/binds the article folder --}}
            <div id="rndArchiveBindingOverlay" class="rnd-binding-overlay" role="status" aria-live="polite" aria-busy="true" hidden>
                <div class="rnd-binding-card">
                    <div class="rnd-binding-spinner" aria-hidden="true"></div>
                    <strong>Menyiapkan arsip artikel...</strong>
                    <p id="rndArchiveBindingMessage">Menghubungkan folder artikel dengan Google Drive. Mohon tunggu.</p>
                    <span><i class="fa fa-info-circle"></i> Proses sedang berjalan, halaman tidak macet.</span>
                </div>
            </div>

            {{-- DUAL PANE: PREVIEW KIRI, FILE MANAGER KANAN --}}
            <div class="rnd-archive-workspace">
                <aside class="rnd-preview-pane">
                    <div class="rnd-preview-pane-head">
                        <div><i class="fa fa-eye"></i> Preview</div><span id="rndPreviewType">Belum dipilih</span>
                    </div>
                    <div class="rnd-preview-stage" id="rndPreviewStage">
                        <div class="rnd-preview-placeholder"><i class="fa fa-picture-o"></i><strong>Pratinjau
                                foto</strong><span>Pilih foto dari komputer atau klik foto tersimpan.</span></div>
                    </div>
                    <div class="rnd-preview-caption" id="rndPreviewCaption">Belum ada foto dipilih.</div>
                    <div class="rnd-preview-thumbs" id="rndPreviewThumbs"></div>
                </aside>
                <div class="rnd-archive-main">
                    {{-- GOOGLE DRIVE FILE MANAGER --}}
                    <div class="rnd-drive-manager" id="rndDriveManager">
                        <div class="rnd-drive-manager-heading">
                            <div>
                                <h4><i class="fa fa-folder-open"></i> File Manager Arsip</h4>
                                <p>Double-click folder untuk membuka. Upload masuk ke folder aktif.</p>
                            </div>
                            <div class="rnd-drive-manager-actions">
                                <button type="button" id="rndDriveBack" disabled><i class="fa fa-arrow-left"></i>
                                    Kembali</button>
                                <button type="button" id="rndDriveNewFolder"><i class="fa fa-folder"></i> Folder
                                    Baru</button>
                                <button type="button" id="rndDriveRefresh"><i class="fa fa-refresh"></i>
                                    Refresh</button>
                            </div>
                        </div>
                        <nav id="rndDriveBreadcrumb" class="rnd-drive-breadcrumb" aria-label="Lokasi folder"></nav>
                        <div id="rndDriveCurrentPath" class="rnd-drive-current-path">Pilih artikel untuk memuat folder.
                        </div>
                        <div class="rnd-view-toolbar" role="group" aria-label="Mode tampilan file">
                            <span class="rnd-view-caption">Tampilan file</span>
                            <button type="button" id="rndViewTile" class="rnd-view-toggle active" aria-pressed="true"
                                title="Tile / Grid Foto"><i class="fa fa-th-large"></i><span>Tile</span></button>
                            <button type="button" id="rndViewList" class="rnd-view-toggle" aria-pressed="false"
                                title="List File"><i class="fa fa-list"></i><span>List</span></button>
                        </div>
                        <div class="rnd-inline-search">
                            <i class="fa fa-search"></i>
                            <input id="rndFileSearch" type="search" placeholder="Cari nama file atau folder aktif..." autocomplete="off">
                            <span id="rndFileSearchCount" aria-live="polite">Found 0 records</span>
                        </div>
                        <div id="rndDriveItems" class="rnd-drive-items">
                            <div class="rnd-drive-empty">Folder belum dimuat.</div>
                        </div>
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

                            <span>JPG, JPEG, PNG atau WEBP · Maksimal 25 MB per foto · Maksimal 50 foto per batch</span>



                            <input type="file" id="rndPhotoInput" name="photos[]"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required>

                        </label>



                        <div id="rndSelectedFiles" class="rnd-selected-files">

                            Belum ada foto dipilih.

                        </div>



                        {{-- TOMBOL UPLOAD SELALU TERSEDIA --}}

                        <div class="rnd-upload-actions">

                            <div id="rndUploadMessage" role="status" aria-live="polite"></div>



                            <button type="submit" id="rndUploadButton" class="rnd-btn-primary">

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



                    <div id="rndArchiveFiles" class="rnd-photo-grid" hidden>

                        <div class="rnd-message">Belum ada foto.</div>

                    </div>
                </div><!-- /.rnd-archive-main -->
            </div><!-- /.rnd-archive-workspace -->

        </div>



        <div class="rnd-drawer-footer">

            <span><i class="fa fa-shield"></i> NewWicker R&D Archive</span>

            <button type="button" id="rndFooterClose" class="rnd-footer-button">

                <i class="fa fa-arrow-left"></i> Tutup

            </button>

        </div>

    </aside>

    {{-- UPLOAD PROGRESS LAYER: above archive modal --}}
    <div id="rndUploadLayer" class="rnd-upload-layer" hidden role="dialog" aria-modal="true"
        aria-labelledby="rndLayerTitle">
        <div class="rnd-upload-layer-card" role="status" aria-live="polite">
            <div class="rnd-layer-icon"><i class="fa fa-cloud-upload"></i></div>
            <h3 id="rndLayerTitle">Mengunggah Foto</h3>
            <p id="rndLayerFilename">Menyiapkan file...</p>
            <div class="rnd-layer-ring" id="rndLayerRing"><span id="rndLayerPercent">0%</span></div>
            <div class="rnd-layer-track">
                <div id="rndLayerBar"></div>
            </div>
            <div class="rnd-layer-stats" id="rndLayerStats">Memulai upload...</div>
            <small>Mohon tunggu sampai proses upload selesai.</small>
        </div>
    </div>

    <style>
        .rnd-page,

        .rnd-page \*,

        .rnd-drawer,

        .rnd-drawer \* {

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



        .rnd-page-header {
            margin-bottom: 24px;
        }



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



        .rnd-toolbar-icon {
            width: 42px;
            height: 42px;
            font-size: 17px;
        }



        .rnd-search {

            position: relative;

            display: flex;

            align-items: center;

            width: 390px;

            max-width: 100%;

            margin: 0;

        }



        .rnd-search>i {

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

            box-shadow: 0 0 0 3px rgba(23, 105, 224, .09);

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



        .rnd-table-scroll {
            width: 100%;
            overflow-x: auto;
        }



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



        .rnd-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .rnd-table tbody tr:hover {
            background: #fbfdff;
        }



        .rnd-muted {
            color: #94a3b8;
        }



        .rnd-po-cell {

            display: flex;

            align-items: center;

            gap: 10px;

        }



        .rnd-po-icon {
            width: 34px;
            height: 34px;
        }



        .rnd-po-cell strong {
            display: block;
            font-size: 12px;
        }

        .rnd-po-cell small {
            display: block;
            margin-top: 5px;
            color: #94a3b8;
            font-size: 10px;
        }



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



        .rnd-btn-primary:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .rnd-btn-primary:disabled {
            opacity: .6;
            cursor: wait;
        }



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



        .rnd-empty strong {
            color: #334155;
        }

        .rnd-empty p {
            margin-top: 7px;
        }



        .rnd-table-footer {

            flex-wrap: wrap;

            padding: 14px 17px;

            border-top: 1px solid var(--border);

            color: #718096;

            font-size: 11px;

        }



        .rnd-pagination nav>div:first-child {
            display: none;
        }

        .rnd-pagination .pagination {
            display: flex;
            gap: 4px;
            margin: 0;
        }

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



        /\ DRAWER \/ .rnd-backdrop {

            position: fixed;

            inset: 0;

            z-index: 9989;

            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(3px);

            opacity: 0;

            visibility: hidden;

            transition: .2s;

        }



        .rnd-backdrop.show {
            opacity: 1;
            visibility: visible;
        }



        .rnd-drawer {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 9991;
            display: flex;
            flex-direction: column;
            width: min(1500px, calc(100vw - 48px));
            height: min(940px, calc(100dvh - 48px));
            min-height: 420px;
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 25px 90px rgba(15, 23, 42, .28);
            transform: translate(-50%, -46%) scale(.985);
            visibility: hidden;
            opacity: 0;
            transition: transform .22s ease, opacity .22s ease, visibility .22s;
        }


        .rnd-drawer.open {
            transform: translate(-50%, -50%) scale(1);
            visibility: visible;
            opacity: 1;
        }



        .rnd-drawer-header {

            align-items: flex-start;

            flex-shrink: 0;

            margin-bottom: 16px;

        }



        .rnd-drawer-breadcrumb {
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 10px;
        }

        .rnd-drawer-breadcrumb span {
            padding: 0 4px;
        }

        .rnd-drawer-header h3 {
            margin: 0 0 6px;
            font-size: 21px;
            font-weight: 800;
        }



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

            background: linear-gradient(110deg, #f0f7ff, #fbfdff);

        }



        .rnd-summary-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .rnd-summary-icon {
            width: 34px;
            height: 34px;
            background: #fff;
        }

        .rnd-summary-item small {
            display: block;
            margin-bottom: 5px;
            color: #718096;
            font-size: 10px;
        }

        .rnd-summary-item strong {
            display: block;
            overflow-wrap: anywhere;
            font-size: 11px;
        }



        .rnd-detail-section {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .rnd-section-heading {
            flex-shrink: 0;
            margin-bottom: 12px;
        }

        .rnd-section-heading h4,

        .rnd-gallery-heading h4 {
            margin: 0 0 5px;
            font-size: 14px;
            font-weight: 800;
        }



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



        .rnd-detail-table thead {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .rnd-detail-table thead th {
            padding: 12px 10px;
            font-size: 9px;
        }

        .rnd-detail-table tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #eef1f6;
        }

        .rnd-article-code {
            color: #334155;
            font-weight: 700;
        }

        .rnd-item-description {
            max-width: 230px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }



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

        .rnd-folder-pill.ready {
            background: #dcfce7;
            color: #166534;
        }



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

        .rnd-open-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }



        /\ ARCHIVE \/ .rnd-archive-panel {

            flex: 1;

            min-height: 0;

            overflow-y: auto;

            padding: 2px 1px 10px;

        }

        .rnd-archive-panel[hidden] {
            display: none !important;
        }



        .rnd-archive-navigation {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }



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

        .rnd-drive-link {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }



        .rnd-article-banner {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 15px;

            margin-bottom: 11px;

            border: 1px solid #dbeafe;

            border-radius: 10px;

            background: linear-gradient(110deg, #eff6ff, #f8fbff);

        }

        .rnd-article-banner-icon {
            width: 44px;
            height: 44px;
            background: #fff;
            font-size: 20px;
        }

        .rnd-article-banner small {
            display: block;
            margin-bottom: 5px;
            color: #2563eb;
            font-size: 9px;
            font-weight: 800;
        }

        .rnd-article-banner h4 {
            margin: 0 0 5px;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .rnd-article-banner p {
            margin: 0;
            color: #64748b;
            font-size: 10px;
            overflow-wrap: anywhere;
        }



        .rnd-folder-status {

            padding: 10px 12px;

            margin-bottom: 15px;

            border: 1px solid #e2e8f0;

            border-radius: 7px;

            background: #f8fafc;

            color: #64748b;

            font-size: 10px;

        }

        .rnd-folder-status.ready {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .rnd-folder-status.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }



        /\ UPLOAD FORM \/ #rndUploadForm {

            display: block !important;

            padding: 15px;

            margin: 0 0 22px;

            border: 1px solid #e5eaf2;

            border-radius: 10px;

            background: #fff;

        }



        .rnd-upload-heading {
            margin-bottom: 14px;
        }

        .rnd-upload-heading h4 {
            margin: 0 0 5px;
            font-size: 13px;
            font-weight: 800;
        }

        .rnd-upload-heading-icon {
            color: var(--primary);
            font-size: 20px;
        }



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

        .rnd-file-picker>i {
            color: var(--primary);
            font-size: 25px;
        }

        .rnd-file-picker strong {
            color: #334155;
            font-size: 11px;
        }

        .rnd-file-picker span {
            color: #94a3b8;
            font-size: 10px;
        }

        .rnd-file-picker input {
            display: block;
            width: 100%;
            max-width: 300px;
            font-size: 10px;
            cursor: pointer;
        }



        .rnd-selected-files {

            margin-top: 9px;

            color: #64748b;

            font-size: 10px;

            line-height: 1.7;

            overflow-wrap: anywhere;

        }



        /\ FIX UTAMA: TOMBOL TIDAK BOLEH HILANG \/ #rndUploadForm .rnd-upload-actions {

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



        #rndUploadForm #rndUploadMessage.success {
            color: #15803d;
        }

        #rndUploadForm #rndUploadMessage.error {
            color: #dc2626;
        }



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

        #rndUploadForm #rndUploadButton:disabled {
            opacity: .65 !important;
            cursor: wait;
        }



        .rnd-upload-progress {
            margin-top: 12px;
        }

        .rnd-upload-progress[hidden] {
            display: none !important;
        }

        .rnd-progress-track {
            height: 6px;
            overflow: hidden;
            border-radius: 20px;
            background: #e2e8f0;
        }

        .rnd-progress-bar {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: #1769e0;
            transition: width .2s;
        }

        .rnd-upload-progress small {
            display: block;
            margin-top: 6px;
            color: #64748b;
            font-size: 10px;
        }



        .rnd-gallery-heading {
            margin-bottom: 12px;
        }

        .rnd-gallery-count {
            padding: 6px 9px;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }




        /* Multi-select photos: separate from single-photo preview selection. */
        .rnd-drive-item { position: relative; }
        .rnd-photo-multiselect { position:absolute; z-index:8; top:6px; left:6px; width:19px; height:19px; border-radius:50%; border:1.5px solid #94a3b8; background:rgba(255,255,255,.95); display:flex; align-items:center; justify-content:center; cursor:pointer; color:transparent; box-shadow:0 2px 7px rgba(15,23,42,.18); }
        .rnd-photo-multiselect:hover { border-color:#2563eb; }
        .rnd-photo-multiselect i { font-size:10px; line-height:1; }
        .rnd-photo-multiselect[aria-pressed="true"] { background:#2563eb; border-color:#2563eb; color:white; }
        .rnd-drive-item.rnd-multi-selected { outline:3px solid #2563eb; outline-offset:-3px; background:#eff6ff; }
        .rnd-multi-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:9px 12px; margin:8px 0; border:1px solid #bfdbfe; border-radius:10px; background:#eff6ff; color:#1e40af; font-size:12px; }
        .rnd-multi-toolbar[hidden] { display:none !important; }
        .rnd-multi-toolbar button { border:1px solid #93c5fd; border-radius:7px; padding:6px 10px; background:#fff; color:#1e40af; cursor:pointer; }
        .rnd-multi-toolbar button:hover { background:#dbeafe; }
        .rnd-batch-download-panel { position:fixed; z-index:2147483000; right:20px; bottom:20px; width:min(380px,calc(100vw - 40px)); max-height:70vh; overflow:auto; padding:15px; background:#fff; border:1px solid #bfdbfe; border-radius:12px; box-shadow:0 10px 38px rgba(15,23,42,.24); color:#1e293b; font-size:12px; }
        .rnd-batch-download-panel > strong { font-size:14px; color:#1d4ed8; }
        .rnd-batch-download-panel > button { float:right; border:1px solid #cbd5e1; border-radius:6px; background:white; padding:4px 9px; cursor:pointer; }
        .rnd-batch-download-panel p { clear:both; padding-top:8px; color:#475569; }
        .rnd-batch-download-links { display:flex; flex-direction:column; gap:5px; margin-top:10px; }
        .rnd-batch-download-links a { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; padding:9px; border-radius:6px; background:#eff6ff; color:#1d4ed8; text-decoration:none; }
        .rnd-batch-download-links a:hover { background:#dbeafe; }


        .rnd-operation-overlay[hidden] { display:none !important; }
        .rnd-operation-overlay { position:fixed; inset:0; z-index:2147483600; background:rgba(15,23,42,.55); display:flex; align-items:center; justify-content:center; padding:20px; }
        .rnd-operation-dialog { width:min(440px,100%); background:#fff; border-radius:14px; padding:28px; text-align:center; box-shadow:0 18px 70px rgba(0,0,0,.25); color:#1e293b; }
        .rnd-operation-dialog strong { display:block; margin:12px 0; font-size:18px; }
        .rnd-operation-dialog p { font-size:12px; line-height:1.6; color:#64748b; }
        .rnd-operation-dialog small { display:block; margin-top:10px; color:#64748b; }
        .rnd-operation-spinner { width:38px; height:38px; border:4px solid #dbeafe; border-top-color:#2563eb; border-radius:50%; animation:rnd-op-spin .8s linear infinite; margin:auto; }
        @keyframes rnd-op-spin { to { transform:rotate(360deg); } }
        .rnd-operation-track { height:9px; border-radius:20px; background:#e2e8f0; overflow:hidden; margin-top:18px; }
        #rndOperationFill { height:100%; background:#2563eb; border-radius:20px; transition:width .2s; }
        #rndOperationFill.indeterminate { animation:rnd-op-slide 1.5s ease-in-out infinite alternate; }
        @keyframes rnd-op-slide { from { transform:translateX(-110%); } to { transform:translateX(310%); } }

        .rnd-photo-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));

            gap: 11px;

        }

        .rnd-photo-card {
            min-width: 0;
            padding: 12px;
            border: 1px solid #e5eaf2;
            border-radius: 8px;
            background: #fff;
        }

        .rnd-photo-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 62px;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 25px;
        }

        .rnd-photo-card a {
            display: block;
            color: #2563eb;
            font-size: 10px;
            font-weight: 700;
            overflow-wrap: anywhere;
            text-decoration: none;
        }

        .rnd-photo-card a:hover {
            text-decoration: underline;
        }

        .rnd-photo-card small {
            display: block;
            margin-top: 6px;
            color: #94a3b8;
            font-size: 9px;
        }

        .rnd-file-type {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
        }



        .rnd-drawer-footer {

            flex-shrink: 0;

            padding-top: 13px;

            margin-top: 13px;

            border-top: 1px solid #edf1f6;

            color: #94a3b8;

            font-size: 10px;

        }

        .rnd-footer-button {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #475569;
            font-size: 10px;
            cursor: pointer;
        }



        @media (max-width: 900px) {

            .rnd-drawer {
                width: calc(100vw - 24px);
                height: calc(100dvh - 24px);
                padding: 16px;
                border-radius: 12px;
            }

        }

        @media (max-width: 600px) {

            .rnd-page {
                padding: 14px 9px 25px;
            }

            .rnd-page-header {
                align-items: flex-start;
            }

            .rnd-page-header h2 {
                font-size: 21px;
            }

            .rnd-toolbar {
                align-items: stretch;
            }

            .rnd-search {
                width: 100%;
            }

            .rnd-drawer {
                width: calc(100vw - 12px);
                height: calc(100dvh - 12px);
                min-height: 0;
                padding: 12px;
                border-radius: 10px;
            }

            .rnd-po-summary {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .rnd-summary-item strong {
                white-space: normal;
            }

            .rnd-photo-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }


        /* Google Drive-style file manager */
        .rnd-drive-manager {
            margin: 18px 0 22px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff
        }

        .rnd-drive-manager-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            padding: 15px 16px;
            border-bottom: 1px solid #e2e8f0
        }

        .rnd-drive-manager-heading h4 {
            margin: 0 0 5px;
            color: #172033
        }

        .rnd-drive-manager-heading p {
            margin: 0;
            color: #64748b;
            font-size: 12px
        }

        .rnd-drive-manager-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px
        }

        .rnd-drive-manager-actions button,
        .rnd-drive-item-actions button {
            border: 1px solid #dbe2ea;
            border-radius: 8px;
            padding: 8px 10px;
            background: #fff;
            color: #334155;
            cursor: pointer;
            font-size: 12px
        }

        .rnd-drive-manager-actions button:hover,
        .rnd-drive-item-actions button:hover {
            background: #f1f5f9
        }

        .rnd-drive-manager-actions button:disabled {
            opacity: .4;
            cursor: not-allowed
        }

        .rnd-drive-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            padding: 10px 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #94a3b8;
            font-size: 12px
        }

        .rnd-drive-breadcrumb button {
            border: 0;
            background: transparent;
            color: #2563eb;
            padding: 3px 0;
            cursor: pointer;
            font-weight: 600
        }

        .rnd-drive-breadcrumb button:disabled {
            color: #334155;
            cursor: default
        }

        .rnd-drive-current-path {
            padding: 9px 15px;
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            overflow-wrap: anywhere
        }

        .rnd-drive-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
            gap: 10px;
            padding: 14px;
            max-height: 390px;
            overflow: auto
        }

        .rnd-drive-item {
            min-width: 0;
            padding: 13px 10px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            background: #fff;
            text-align: center
        }

        .rnd-drive-item[data-type=folder] {
            cursor: pointer
        }

        .rnd-drive-item:hover {
            border-color: #93c5fd;
            background: #f8fbff
        }

        .rnd-drive-item-icon {
            font-size: 29px;
            color: #64748b;
            margin-bottom: 8px
        }

        .rnd-drive-item[data-type=folder] .rnd-drive-item-icon {
            color: #eab308
        }

        .rnd-drive-item-name {
            min-height: 34px;
            overflow-wrap: anywhere;
            word-break: break-word;
            color: #334155;
            font-size: 12px;
            font-weight: 600
        }

        .rnd-drive-item-meta {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 10px
        }

        .rnd-drive-item-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px
        }

        .rnd-drive-item-actions button {
            padding: 5px 7px;
            font-size: 10px
        }

        .rnd-drive-item-actions .danger {
            color: #b91c1c;
            border-color: #fecaca
        }

        .rnd-drive-empty {
            grid-column: 1/-1;
            padding: 25px 12px;
            color: #94a3b8;
            text-align: center
        }

        @media(max-width:640px) {
            .rnd-drive-manager-heading {
                align-items: flex-start;
                flex-direction: column
            }

            .rnd-drive-manager-actions {
                width: 100%
            }

            .rnd-drive-manager-actions button {
                flex: 1
            }

            .rnd-drive-items {
                grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
                padding: 9px;
                gap: 8px
            }
        }


        /* Modern, calmer archive file manager redesign */
        .rnd-drive-manager {
            margin: 16px 0 18px !important;
            border: 1px solid #dce5f2 !important;
            border-radius: 16px !important;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .035);
        }

        .rnd-drive-manager-heading {
            padding: 17px 20px !important;
            align-items: center !important;
            background: #fff;
        }

        .rnd-drive-manager-heading h4 {
            font-size: 19px !important;
            font-weight: 800;
            letter-spacing: -.35px;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .rnd-drive-manager-heading h4 i {
            font-size: 21px;
            color: #172b4d;
        }

        .rnd-drive-manager-heading p {
            font-size: 12px !important;
            margin-top: 4px !important;
        }

        .rnd-drive-manager-actions button {
            border-radius: 9px !important;
            padding: 9px 12px !important;
            font-weight: 600 !important;
            transition: .15s ease;
        }


        /* Pending state: kartu folder/foto transparan selama proses berjalan */
        .rnd-drive-item.is-pending {
            opacity: .42 !important;
            filter: saturate(.65);
            pointer-events: none;
            position: relative;
        }
        .rnd-drive-item.is-pending::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 4;
            background: rgba(255,255,255,.18);
            border-radius: inherit;
        }
        .rnd-pending-overlay {
            position: absolute;
            z-index: 5;
            top: 8px;
            left: 8px;
            right: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding: 6px 8px;
            border-radius: 8px;
            background: rgba(255,255,255,.94);
            color: #475569;
            box-shadow: 0 2px 8px rgba(15,23,42,.10);
            font-size: 10px;
            font-weight: 700;
        }
        .rnd-drive-item.is-pending .rnd-drive-preview img { opacity: .55; }

        #rndDriveNewFolder {
            background: #1769e8 !important;
            border-color: #1769e8 !important;
            color: #fff !important;
        }

        #rndDriveNewFolder:hover {
            background: #0e57c7 !important;
        }

        .rnd-drive-breadcrumb {
            padding: 12px 20px !important;
            background: #fbfdff;
            border-bottom: 1px solid #edf2f8 !important;
        }

        .rnd-drive-current-path {
            padding: 10px 20px !important;
            background: #f7f9fc !important;
            font-weight: 600;
            color: #64748b !important;
        }

        .rnd-drive-items-modern {
            grid-template-columns: repeat(auto-fill, minmax(158px, 1fr)) !important;
            gap: 13px !important;
            padding: 16px !important;
            max-height: 440px !important;
            background: #fff;
        }

        .rnd-drive-item-modern {
            position: relative;
            text-align: left !important;
            padding: 0 !important;
            overflow: hidden;
            border: 1px solid #dfe7f2 !important;
            border-radius: 12px !important;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }

        .rnd-drive-item-modern:hover {
            border-color: #8bb8ff !important;
            background: #fff !important;
            box-shadow: 0 5px 15px rgba(37, 99, 235, .08);
            transform: translateY(-1px);
        }

        .rnd-drive-preview {
            height: 116px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f8fc;
            color: #8da0ba;
            font-size: 40px;
            overflow: hidden;
        }

        .rnd-drive-preview.is-folder {
            background: linear-gradient(135deg, #f6faff, #edf5ff);
            color: #f2b400;
            font-size: 48px;
        }

        .rnd-drive-preview.is-file {
            font-size: 38px;
            color: #8aa1bd;
        }

        .rnd-drive-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .rnd-drive-card-menu {
            position: absolute;
            right: 8px;
            top: 8px;
            width: 27px;
            height: 27px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .95);
            border: 1px solid #e5ebf3;
            border-radius: 8px;
            color: #64748b;
            cursor: pointer;
            z-index: 2;
        }

        .rnd-drive-item-info {
            padding: 11px 12px 12px;
            min-width: 0;
        }

        .rnd-drive-item-modern .rnd-drive-item-name {
            font-size: 12px !important;
            font-weight: 750 !important;
            color: #1e293b !important;
            min-height: 0 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rnd-drive-item-modern .rnd-drive-item-meta {
            margin-top: 5px !important;
            font-size: 10px !important;
            color: #8a99ad !important;
        }

        .rnd-drive-item-actions-modern {
            display: none !important;
            position: absolute;
            top: 38px;
            right: 7px;
            z-index: 3;
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 4px !important;
            padding: 6px;
            background: #fff;
            border: 1px solid #e1e8f0;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .14);
        }

        .rnd-drive-item-actions-modern.is-open {
            display: flex !important;
        }

        .rnd-drive-item-actions-modern button {
            white-space: nowrap;
            text-align: left !important;
            border: 0 !important;
            padding: 7px 9px !important;
            background: #fff !important;
            color: #334155 !important;
            font-size: 11px !important;
            border-radius: 6px !important;
        }

        .rnd-drive-item-actions-modern button:hover {
            background: #f1f5f9 !important;
        }

        .rnd-drive-item-actions-modern button.danger {
            color: #dc2626 !important;
        }

        .rnd-drive-empty-modern {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 170px;
            color: #64748b !important;
        }

        .rnd-empty-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #eff6ff;
            color: #3977d5;
            font-size: 22px;
            margin-bottom: 3px;
        }

        .rnd-drive-empty-modern strong {
            font-size: 13px;
            color: #334155;
        }

        .rnd-drive-empty-modern span {
            font-size: 11px;
            max-width: 300px;
            line-height: 1.5;
        }

        #rndUploadForm {
            padding: 0 0 5px;
        }

        .rnd-upload-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 10px !important;
        }

        .rnd-upload-heading h4 {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: #1e293b;
        }

        .rnd-upload-heading p {
            font-size: 11px !important;
            color: #8190a5 !important;
        }

        .rnd-file-picker {
            min-height: 145px !important;
            border: 1.5px dashed #8db8ff !important;
            border-radius: 13px !important;
            background: linear-gradient(135deg, #f7fbff, #eef6ff) !important;
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 22px !important;
            cursor: pointer;
            transition: .15s ease;
        }

        .rnd-file-picker:hover {
            background: #eaf3ff !important;
            border-color: #3977e5 !important;
        }

        .rnd-file-picker>i {
            font-size: 27px !important;
            color: #1769e8 !important;
            margin-bottom: 1px;
        }

        .rnd-file-picker strong {
            font-size: 13px !important;
            color: #243247 !important;
        }

        .rnd-file-picker span {
            font-size: 10px !important;
            color: #8292a8 !important;
        }

        .rnd-upload-actions {
            margin-top: 10px !important;
        }

        .rnd-upload-actions .rnd-btn-primary {
            border-radius: 9px !important;
            padding: 10px 16px !important;
        }

        @media(max-width:640px) {
            .rnd-drive-manager-heading {
                padding: 14px !important
            }

            .rnd-drive-manager-actions {
                width: auto !important
            }

            .rnd-drive-manager-actions button {
                flex: initial !important
            }

            .rnd-drive-items-modern {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                padding: 10px !important;
                gap: 9px !important
            }

            .rnd-drive-preview {
                height: 100px
            }

            .rnd-drive-manager-heading h4 {
                font-size: 16px !important
            }

            .rnd-file-picker {
                min-height: 125px !important
            }
        }


        /* Fullscreen-friendly archive with left preview and right file browser */
        .rnd-drawer {
            width: min(1680px, calc(100vw - 24px)) !important;
            height: calc(100dvh - 20px) !important;
            max-height: calc(100dvh - 20px) !important;
            min-height: 0 !important;
            padding: 20px !important;
            overflow: hidden !important
        }

        .rnd-drawer-header {
            margin-bottom: 9px !important
        }

        .rnd-po-summary {
            flex-shrink: 0 !important;
            margin-bottom: 9px !important
        }

        .rnd-archive-panel {
            display: flex;
            flex-direction: column;
            overflow: hidden !important;
            min-height: 0 !important;
            padding-bottom: 0 !important
        }

        .rnd-archive-panel[hidden] {
            display: none !important
        }

        .rnd-archive-navigation,
        .rnd-article-banner,
        .rnd-folder-status {
            flex-shrink: 0;
            margin-bottom: 8px !important
        }

        .rnd-article-banner {
            padding: 9px 12px !important
        }

        .rnd-archive-workspace {
            display: grid;
            grid-template-columns: minmax(270px, 34%) minmax(0, 1fr);
            gap: 14px;
            flex: 1;
            min-height: 0;
            overflow: hidden
        }

        .rnd-preview-pane {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            border: 1px solid #dbe5f1;
            border-radius: 14px;
            background: #f8fafc;
            overflow: hidden
        }

        .rnd-preview-pane-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #fff;
            border-bottom: 1px solid #e5edf6;
            font-size: 13px;
            font-weight: 800;
            color: #1e293b
        }

        .rnd-preview-pane-head i {
            color: #2563eb;
            margin-right: 7px
        }

        .rnd-preview-pane-head span {
            font-size: 10px;
            color: #8a99ad;
            font-weight: 600
        }

        .rnd-preview-stage {
            flex: 1;
            min-height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            overflow: hidden;
            background: linear-gradient(145deg, #f8fbff, #eef4fb)
        }

        .rnd-preview-stage>img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 5px 22px #0f172a1f
        }

        .rnd-preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            color: #94a3b8;
            padding: 20px;
            max-width: 300px
        }

        .rnd-preview-placeholder i {
            font-size: 48px;
            color: #b9c9de
        }

        .rnd-preview-placeholder strong {
            font-size: 14px;
            color: #475569
        }

        .rnd-preview-placeholder span {
            font-size: 11px;
            line-height: 1.6
        }

        .rnd-preview-caption {
            padding: 10px 12px;
            background: #fff;
            border-top: 1px solid #e5edf6;
            font-size: 11px;
            color: #475569;
            overflow-wrap: anywhere
        }

        .rnd-preview-thumbs {
            display: flex;
            gap: 7px;
            padding: 9px;
            background: #fff;
            border-top: 1px solid #e5edf6;
            overflow-x: auto
        }

        .rnd-preview-thumb {
            width: 54px;
            height: 48px;
            flex: 0 0 auto;
            border: 2px solid transparent;
            border-radius: 7px;
            overflow: hidden;
            padding: 0;
            background: #eef2f7;
            cursor: pointer
        }

        .rnd-preview-thumb.active {
            border-color: #2563eb
        }

        .rnd-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .rnd-archive-main {
            display: flex;
            flex-direction: column;
            gap: 9px;
            min-width: 0;
            min-height: 0;
            overflow-y: auto;
            padding: 0 2px 5px 0
        }

        .rnd-drive-manager {
            display: flex;
            flex-direction: column;
            min-height: 0;
            margin: 0 !important;
            flex: 1 0 auto
        }

        .rnd-drive-manager-heading {
            padding: 11px 14px !important;
            flex-shrink: 0
        }

        .rnd-drive-breadcrumb {
            padding: 8px 14px !important;
            flex-shrink: 0
        }

        .rnd-drive-current-path {
            padding: 7px 14px !important;
            flex-shrink: 0
        }

        .rnd-drive-items-modern {
            max-height: none !important;
            min-height: 130px;
            flex: 1;
            overflow: auto;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
            align-content: start
        }

        #rndUploadForm {
            flex-shrink: 0;
            padding: 0 2px 4px !important
        }

        .rnd-upload-heading {
            margin: 0 0 7px !important
        }

        .rnd-file-picker {
            min-height: 95px !important;
            padding: 12px !important;
            gap: 5px !important
        }

        .rnd-file-picker>i {
            font-size: 22px !important
        }

        .rnd-gallery-heading,
        #rndArchiveFiles {
            display: none !important
        }

        @media(max-width:850px) {
            .rnd-drawer {
                padding: 12px !important;
                width: calc(100vw - 12px) !important;
                height: calc(100dvh - 12px) !important
            }

            .rnd-archive-workspace {
                grid-template-columns: minmax(210px, 32%) minmax(0, 1fr);
                gap: 9px
            }
        }

        @media(max-width:620px) {
            .rnd-archive-workspace {
                grid-template-columns: 1fr;
                grid-template-rows: minmax(160px, 35%) minmax(0, 1fr);
                overflow: auto
            }

            .rnd-preview-pane {
                min-height: 160px
            }

            .rnd-preview-stage {
                min-height: 90px
            }

            .rnd-archive-main {
                overflow: visible
            }

            .rnd-drive-items-modern {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important
            }
        }


        /* FIX: upload button always visible and clearly styled */
        #rndUploadForm .rnd-upload-actions {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 8 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
            min-height: 58px !important;
            margin: 10px -2px -4px !important;
            padding: 10px 8px calc(10px + env(safe-area-inset-bottom)) !important;
            border-top: 1px solid #dbe5f1 !important;
            background: rgba(255, 255, 255, .97) !important;
            backdrop-filter: blur(8px);
            visibility: visible !important;
            opacity: 1 !important;
        }

        #rndUploadForm #rndUploadButton {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            min-width: 145px !important;
            min-height: 42px !important;
            padding: 11px 18px !important;
            border: 1px solid #1769e8 !important;
            border-radius: 10px !important;
            background: #1769e8 !important;
            color: #fff !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 12px rgba(23, 105, 232, .18) !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        #rndUploadForm #rndUploadButton:disabled {
            opacity: .65 !important;
            cursor: wait !important;
        }

        #rndUploadForm .rnd-upload-message {
            min-width: 0;
        }

        @media(max-width:620px) {
            #rndUploadForm .rnd-upload-actions {
                position: sticky !important;
                bottom: 0 !important;
            }

            #rndUploadForm #rndUploadButton {
                width: 100% !important;
            }
        }

        /* R&D Archive: compact layout redesign, presentation-only */
        .rnd-sr-only {
            position: absolute !important;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0
        }

        .rnd-drawer {
            --primary: #1769e8;
            padding: 22px !important;
            border-radius: 18px !important;
        }

        .rnd-drawer-header {
            margin-bottom: 13px !important;
            align-items: center !important
        }

        .rnd-drawer-header h3 {
            font-size: 24px !important;
            letter-spacing: -.6px;
            margin: 0 !important
        }

        .rnd-po-summary {
            grid-template-columns: 1fr 1fr .65fr !important;
            background: #fff !important;
            padding: 12px 15px !important;
            margin-bottom: 13px !important;
            gap: 0 !important;
            border-radius: 12px !important
        }

        .rnd-summary-item {
            padding: 4px 15px !important;
            border-right: 1px solid #e6edf5;
            gap: 10px !important
        }

        .rnd-summary-item:last-child {
            border-right: 0
        }

        .rnd-summary-icon {
            width: 39px !important;
            height: 39px !important;
            background: #eff6ff !important
        }

        .rnd-summary-item strong {
            font-size: 13px !important;
            color: #152238
        }

        .rnd-archive-topbar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            min-height: 74px;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid #dce6f4;
            border-radius: 12px;
            background: #f8fbff
        }

        .rnd-archive-topbar .rnd-article-banner {
            flex: 1 1 220px;
            min-width: 0;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important
        }

        .rnd-archive-topbar .rnd-article-banner-icon {
            width: 43px;
            height: 43px;
            border-radius: 10px
        }

        .rnd-archive-topbar .rnd-article-banner h4 {
            font-size: 17px !important;
            margin-bottom: 3px !important
        }

        .rnd-archive-topbar .rnd-article-banner p {
            font-size: 11px !important
        }

        .rnd-archive-topbar .rnd-folder-status {
            flex: 0 1 240px;
            margin: 0 !important;
            padding: 9px 12px !important;
            line-height: 1.45;
            border-radius: 9px
        }

        .rnd-archive-topbar .rnd-archive-navigation {
            margin: 0 !important;
            flex: 0 0 auto;
            justify-content: flex-end;
            gap: 7px
        }

        .rnd-archive-topbar .rnd-back-button,
        .rnd-archive-topbar .rnd-drive-link {
            min-height: 36px;
            padding: 9px 11px !important;
            white-space: nowrap
        }

        .rnd-archive-workspace {
            grid-template-columns: minmax(250px, 35%) minmax(0, 65%) !important;
            gap: 14px !important
        }

        .rnd-preview-pane,
        .rnd-drive-manager {
            border-radius: 12px !important;
            border: 1px solid #dce6f4 !important
        }

        .rnd-preview-pane-head {
            padding: 14px !important
        }

        .rnd-drive-manager-heading {
            gap: 10px !important;
            flex-wrap: wrap;
            padding: 13px 15px !important
        }

        .rnd-drive-manager-heading h4 {
            font-size: 17px !important
        }

        .rnd-drive-manager-actions {
            gap: 6px !important
        }

        .rnd-drive-manager-actions button {
            padding: 8px 10px !important
        }

        .rnd-drive-items-modern {
            grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)) !important
        }

        @media(max-width:1050px) {
            .rnd-archive-topbar .rnd-folder-status {
                flex: 1 1 180px
            }

            .rnd-archive-topbar .rnd-archive-navigation {
                flex: 1 1 100%;
                justify-content: flex-end
            }
        }

        @media(max-width:700px) {
            .rnd-drawer {
                padding: 12px !important
            }

            .rnd-po-summary {
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important
            }

            .rnd-summary-item {
                border: 0 !important;
                padding: 4px !important
            }

            .rnd-archive-workspace {
                grid-template-columns: 1fr !important;
                grid-template-rows: auto auto !important;
                overflow: auto !important
            }

            .rnd-preview-pane {
                min-height: 230px
            }

            .rnd-archive-topbar {
                align-items: stretch
            }

            .rnd-archive-topbar .rnd-article-banner {
                flex-basis: 100%
            }

            .rnd-archive-topbar .rnd-folder-status {
                flex-basis: 100%
            }

            .rnd-archive-topbar .rnd-archive-navigation {
                justify-content: space-between
            }
        }


        /* ==========================================================
               R&D ARCHIVE MODAL V3 - COMPACT SECOND SCREEN
               Visual-only overrides. Existing IDs/events remain intact.
               ========================================================== */
        .rnd-drawer {
            height: min(900px, calc(100dvh - 32px)) !important;
            padding: 18px 20px !important;
        }

        .rnd-drawer-header {
            margin-bottom: 10px !important;
        }

        .rnd-drawer-breadcrumb {
            display: none !important;
        }

        .rnd-drawer-header h3 {
            font-size: 21px !important;
        }

        .rnd-po-summary {
            margin-bottom: 10px !important;
            padding: 9px 12px !important;
        }

        .rnd-summary-icon {
            width: 32px !important;
            height: 32px !important;
        }

        .rnd-archive-panel {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .rnd-archive-topbar {
            flex: 0 0 auto !important;
            min-height: 58px !important;
            padding: 8px 12px !important;
            margin-bottom: 10px !important;
            gap: 10px !important;
        }

        .rnd-archive-topbar .rnd-article-banner-icon {
            width: 36px !important;
            height: 36px !important;
        }

        .rnd-archive-topbar .rnd-article-banner h4 {
            font-size: 15px !important;
        }

        .rnd-archive-topbar .rnd-folder-status {
            font-size: 10px !important;
            padding: 7px 10px !important;
        }

        .rnd-archive-topbar .rnd-back-button,
        .rnd-archive-topbar .rnd-drive-link {
            min-height: 31px !important;
            padding: 6px 9px !important;
        }

        .rnd-archive-workspace {
            display: grid !important;
            grid-template-columns: minmax(250px, 34%) minmax(0, 1fr) !important;
            gap: 12px !important;
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .rnd-preview-pane {
            min-height: 0 !important;
            height: 100% !important;
        }

        .rnd-preview-pane-head {
            padding: 10px 12px !important;
        }

        .rnd-preview-stage {
            min-height: 0 !important;
            flex: 1 1 auto !important;
        }

        .rnd-preview-caption {
            flex: 0 0 auto !important;
            padding: 8px 10px !important;
        }

        .rnd-preview-thumbs:empty {
            display: none !important;
        }

        .rnd-archive-main {
            display: grid !important;
            grid-template-rows: minmax(180px, 1fr) auto !important;
            gap: 10px !important;
            min-height: 0 !important;
            overflow: hidden !important;
            padding: 0 2px 2px 0 !important;
            align-content: stretch !important;
        }

        .rnd-drive-manager {
            height: 100% !important;
            min-height: 0 !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .rnd-drive-manager-heading {
            padding: 10px 12px !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }

        .rnd-drive-manager-heading h4 {
            font-size: 15px !important;
            margin: 0 0 3px !important;
        }

        .rnd-drive-manager-heading p {
            font-size: 10px !important;
        }

        .rnd-drive-manager-actions button {
            padding: 6px 9px !important;
            font-size: 10px !important;
        }

        .rnd-drive-breadcrumb {
            padding: 7px 12px !important;
        }

        .rnd-drive-current-path {
            padding: 6px 12px !important;
        }

        #rndDriveItems {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            max-height: none !important;
            overflow-y: auto !important;
            align-content: start !important;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
            padding: 10px !important;
            gap: 9px !important;
        }

        .rnd-drive-preview {
            height: 80px !important;
        }

        .rnd-drive-item-info {
            padding: 8px 10px !important;
        }

        #rndUploadForm {
            display: block !important;
            flex: 0 0 auto !important;
            margin: 0 !important;
            padding: 10px 12px !important;
            border: 1px solid #dce6f4 !important;
            border-radius: 12px !important;
            overflow: visible !important;
        }

        #rndUploadForm .rnd-upload-heading {
            margin: 0 0 7px !important;
        }

        #rndUploadForm .rnd-upload-heading h4 {
            font-size: 12px !important;
        }

        #rndUploadForm .rnd-upload-heading p {
            font-size: 10px !important;
        }

        #rndUploadForm .rnd-file-picker {
            min-height: 0 !important;
            padding: 8px 10px !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
            gap: 7px !important;
            text-align: left !important;
        }

        #rndUploadForm .rnd-file-picker>i {
            font-size: 16px !important;
        }

        #rndUploadForm .rnd-file-picker strong {
            font-size: 10px !important;
        }

        #rndUploadForm .rnd-file-picker span {
            font-size: 9px !important;
        }

        #rndUploadForm #rndPhotoInput {
            width: 100% !important;
            max-width: none !important;
            font-size: 10px !important;
        }

        #rndUploadForm .rnd-selected-files {
            max-height: 36px !important;
            overflow-y: auto !important;
            margin-top: 5px !important;
        }

        #rndUploadForm .rnd-upload-actions {
            position: static !important;
            min-height: 0 !important;
            padding: 7px 0 0 !important;
            margin: 5px 0 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        #rndUploadForm #rndUploadButton {
            min-height: 34px !important;
            min-width: 125px !important;
            padding: 7px 12px !important;
            font-size: 11px !important;
        }

        .rnd-gallery-heading,
        #rndArchiveFiles {
            display: none !important;
        }

        .rnd-drawer-footer {
            padding-top: 8px !important;
            margin-top: 9px !important;
        }

        @media(max-height:730px) and (min-width:701px) {
            .rnd-drawer {
                height: calc(100dvh - 16px) !important;
                padding: 12px 16px !important;
            }

            .rnd-drawer-header {
                margin-bottom: 6px !important;
            }

            .rnd-po-summary {
                margin-bottom: 6px !important;
            }

            .rnd-archive-topbar {
                margin-bottom: 6px !important;
            }

            .rnd-drive-manager-heading p {
                display: none !important;
            }

            #rndUploadForm .rnd-file-picker span {
                display: none !important;
            }
        }

        @media(max-width:850px) and (min-width:701px) {
            .rnd-archive-workspace {
                grid-template-columns: minmax(205px, 32%) minmax(0, 1fr) !important;
                gap: 8px !important;
            }

            .rnd-drive-manager-heading {
                align-items: flex-start !important;
            }
        }

        @media(max-width:700px) {
            .rnd-drawer {
                height: calc(100dvh - 12px) !important;
                padding: 10px !important;
            }

            .rnd-archive-panel {
                overflow-y: auto !important;
            }

            .rnd-archive-workspace {
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
            }

            .rnd-preview-pane {
                height: 220px !important;
                min-height: 220px !important;
            }

            .rnd-archive-main {
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
            }

            .rnd-drive-manager {
                height: 310px !important;
                min-height: 310px !important;
            }

            #rndUploadForm {
                flex-shrink: 0 !important;
            }

            .rnd-archive-topbar .rnd-folder-status {
                flex: 1 1 100% !important;
            }
        }


        /* V4: PO + buyer + article + cloud status + actions in ONE row */
        .rnd-drawer:has(#rndArchivePanel:not([hidden]))>.rnd-po-summary {
            display: none !important;
        }

        .rnd-archive-topbar {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 12px !important;
            min-width: 0 !important;
        }

        .rnd-compact-po {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            flex: 0 1 230px;
            padding-right: 12px;
            border-right: 1px solid #dbe5f1;
        }

        .rnd-compact-po-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 33px;
            height: 33px;
            flex: 0 0 33px;
            border-radius: 8px;
            background: #fff;
            color: #1769e8;
        }

        .rnd-compact-po-copy {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .rnd-compact-po-copy strong {
            font-size: 12px;
            color: #17243a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rnd-compact-po-copy small {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rnd-compact-divider {
            margin-left: auto;
            color: #94a3b8;
        }

        .rnd-archive-topbar .rnd-article-banner {
            flex: 1 1 190px !important;
            min-width: 120px !important;
        }

        .rnd-archive-topbar .rnd-folder-status {
            flex: 0 1 200px !important;
            min-width: 140px !important;
            max-width: 220px !important;
            margin: 0 !important;
        }

        .rnd-archive-topbar .rnd-archive-navigation {
            flex: 0 0 auto !important;
            min-width: 0 !important;
            margin: 0 !important;
            justify-content: flex-end !important;
        }

        @media(max-width:1100px) {
            .rnd-archive-topbar {
                gap: 7px !important;
            }

            .rnd-compact-po {
                flex-basis: 175px;
                padding-right: 7px;
                gap: 5px;
            }

            .rnd-archive-topbar .rnd-folder-status {
                max-width: 165px !important;
                min-width: 100px !important;
            }

            .rnd-archive-topbar .rnd-back-button,
            .rnd-archive-topbar .rnd-drive-link {
                padding: 6px !important;
                font-size: 9px !important;
            }
        }

        @media(max-width:800px) {
            .rnd-archive-topbar {
                flex-wrap: wrap !important;
            }

            .rnd-compact-po {
                flex: 1 1 40%;
            }

            .rnd-archive-topbar .rnd-article-banner {
                flex: 1 1 45% !important;
            }

            .rnd-archive-topbar .rnd-folder-status {
                flex: 1 1 40% !important;
                max-width: none !important;
            }

            .rnd-archive-topbar .rnd-archive-navigation {
                flex: 1 1 auto !important;
            }
        }

        /* R&D responsive desktop layout and tile/list toggle */
        .rnd-drawer {
            width: min(1560px, calc(100vw - 24px)) !important;
            height: calc(100dvh - 24px) !important;
            padding: 14px 18px !important
        }

        .rnd-archive-topbar {
            margin-bottom: 8px !important
        }

        .rnd-view-toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
            padding: 7px 12px;
            border-bottom: 1px solid #e7edf5;
            background: #fff;
            flex-shrink: 0
        }

        .rnd-view-caption {
            font-size: 11px;
            color: #64748b;
            margin-right: 8px
        }

        .rnd-view-toggle {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border: 1px solid #dce5f1;
            border-radius: 7px;
            background: #fff;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer
        }

        .rnd-view-toggle.active {
            background: #1769e0;
            color: #fff;
            border-color: #1769e0
        }

        #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)) !important;
            gap: 12px !important;
            padding: 12px !important
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
            height: 116px !important
        }

        #rndDriveItems .rnd-drive-preview img {
            object-fit: contain !important;
            background: #f6f8fc
        }

        #rndDriveItems.rnd-list-mode {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
            padding: 4px 10px !important;
            align-content: stretch !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item {
            display: grid !important;
            grid-template-columns: 62px minmax(0, 1fr) 40px !important;
            align-items: center !important;
            min-height: 66px !important;
            gap: 12px !important;
            border: 0 !important;
            border-bottom: 1px solid #e9eef5 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            transform: none !important;
            overflow: visible !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-preview {
            width: 62px !important;
            height: 54px !important;
            border-radius: 7px !important;
            grid-column: 1 !important;
            grid-row: 1 !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-info {
            grid-column: 2 !important;
            grid-row: 1 !important;
            padding: 5px 0 !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-card-menu {
            position: relative !important;
            top: auto !important;
            right: auto !important;
            grid-column: 3 !important;
            grid-row: 1 !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-actions-modern {
            top: 52px !important;
            right: 0 !important
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-name {
            font-size: 12px !important
        }

        @media(max-width:1100px) {
            .rnd-archive-workspace {
                grid-template-columns: minmax(220px, 32%) minmax(0, 1fr) !important
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)) !important
            }
        }

        @media(max-width:700px) {
            .rnd-drawer {
                width: calc(100vw - 8px) !important;
                height: calc(100dvh - 8px) !important;
                padding: 10px !important
            }

            .rnd-archive-workspace {
                grid-template-columns: 1fr !important;
                overflow-y: auto !important
            }

            .rnd-preview-pane {
                height: 260px !important
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important
            }

            .rnd-view-caption {
                display: none
            }
        }


        /* Photo selection and in-place Google Drive preview */
        #rndDriveItems .rnd-drive-item.rnd-drive-selected {
            border-color: #1769e8 !important;
            box-shadow: 0 0 0 2px rgba(23, 105, 232, .16) !important;
            background: #f8fbff !important
        }

        #rndDriveItems .rnd-drive-item[data-type="file"] {
            cursor: pointer
        }

        .rnd-preview-stage {
            position: relative !important
        }

        .rnd-preview-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border: 1px solid #dce5f1;
            border-radius: 50%;
            background: rgba(255, 255, 255, .96);
            color: #1e3a5f;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 4;
            box-shadow: 0 2px 9px rgba(15, 23, 42, .12)
        }

        .rnd-preview-nav-prev {
            left: 9px
        }

        .rnd-preview-nav-next {
            right: 9px
        }

        .rnd-preview-nav:hover {
            background: #eff6ff;
            color: #1769e8
        }

        .rnd-preview-thumbs {
            min-height: 0
        }

        /* V6 - Desktop 100%: prioritize file/folder area, scroll workspace instead of clipping */
        @media (min-width: 701px) {
            .rnd-archive-panel {
                overflow-y: auto !important;
                overflow-x: hidden !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .rnd-archive-workspace {
                flex: 1 0 auto !important;
                min-height: 510px !important;
                height: auto !important;
                overflow: visible !important;
                align-items: stretch !important;
                grid-template-columns: minmax(260px, 32%) minmax(0, 1fr) !important;
            }

            .rnd-preview-pane {
                height: 510px !important;
                min-height: 510px !important;
                position: sticky !important;
                top: 0 !important;
            }

            .rnd-archive-main {
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
                min-height: 0 !important;
                gap: 12px !important;
            }

            .rnd-drive-manager {
                height: auto !important;
                min-height: 370px !important;
                max-height: none !important;
                flex: 0 0 auto !important;
                overflow: hidden !important;
            }

            #rndDriveItems {
                flex: 0 0 auto !important;
                min-height: 230px !important;
                max-height: 420px !important;
                overflow-y: auto !important;
                align-content: start !important;
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
                gap: 12px !important;
            }

            #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
                height: 112px !important;
            }

            #rndDriveItems.rnd-list-mode {
                min-height: 230px !important;
            }

            #rndUploadForm {
                flex: 0 0 auto !important;
            }

            .rnd-drawer-footer {
                position: relative !important;
                z-index: 2 !important;
                background: #fff !important;
            }
        }

        @media (min-width:701px) and (max-height:760px) {
            .rnd-archive-workspace {
                min-height: 465px !important;
            }

            .rnd-preview-pane {
                height: 465px !important;
                min-height: 465px !important;
            }

            .rnd-drive-manager {
                min-height: 345px !important;
            }

            #rndDriveItems {
                min-height: 205px !important;
                max-height: 390px !important;
            }
        }

        /* V7: Fix compressed thumbnail rows at browser zoom 100% */
        #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)) !important;
            grid-auto-rows: 166px !important;
            align-content: start !important;
            align-items: start !important;
            gap: 12px !important;
            overflow-y: auto !important;
        }

        #rndDriveItems:not(.rnd-list-mode)>.rnd-drive-item-modern {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            height: 166px !important;
            min-height: 166px !important;
            max-height: 166px !important;
            overflow: hidden !important;
            flex-shrink: 0 !important;
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
            display: flex !important;
            flex: 0 0 116px !important;
            width: 100% !important;
            height: 116px !important;
            min-height: 116px !important;
            max-height: 116px !important;
            overflow: hidden !important;
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview img {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            object-position: center !important;
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-item-info {
            flex: 0 0 48px !important;
            min-height: 48px !important;
            padding: 7px 10px !important;
        }

        @media(max-width:700px) {
            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        /* Compact archive layout matching reference 2 */
        .rnd-drawer {
            width: min(1500px, calc(100vw - 20px)) !important;
            height: calc(100dvh - 20px) !important;
            padding: 14px 16px !important;
        }

        .rnd-drawer-header {
            margin-bottom: 8px !important
        }

        .rnd-drawer-breadcrumb {
            display: none !important
        }

        .rnd-drawer-header h6 {
            font-size: 15px !important;
            font-weight: 750;
            margin: 0 !important
        }

        .rnd-archive-topbar {
            padding: 8px 10px !important;
            gap: 10px !important
        }

        .rnd-archive-workspace {
            grid-template-columns: minmax(235px, 32%) minmax(0, 1fr) !important;
            gap: 8px !important;
            align-items: stretch !important
        }

        .rnd-drive-manager {
            margin: 0 0 8px !important;
            border-radius: 10px !important
        }

        .rnd-drive-manager-heading {
            padding: 9px 12px !important;
            gap: 8px !important
        }

        .rnd-drive-manager-heading h4 {
            font-size: 14px !important;
            margin: 0 !important
        }

        .rnd-drive-manager-heading p {
            display: none !important
        }

        .rnd-drive-manager-actions button {
            padding: 6px 8px !important;
            font-size: 10px !important
        }

        .rnd-drive-breadcrumb {
            padding: 7px 11px !important
        }

        .rnd-drive-current-path {
            padding: 7px 11px !important
        }

        #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
            grid-template-columns: repeat(auto-fill, minmax(125px, 1fr)) !important;
            gap: 9px !important;
            padding: 10px !important
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
            height: 95px !important
        }

        #rndDriveItems:not(.rnd-list-mode) .rnd-drive-item-info {
            padding: 7px 9px !important
        }

        #rndDriveItems {
            min-height: 155px !important;
            max-height: 280px !important
        }

        #rndUploadForm {
            padding: 10px 12px !important;
            margin: 0 0 10px !important
        }

        .rnd-upload-heading {
            margin-bottom: 6px !important
        }

        .rnd-file-picker {
            padding: 9px 10px !important;
            gap: 3px !important
        }

        .rnd-file-picker>i {
            display: none !important
        }

        .rnd-upload-actions {
            padding-top: 7px !important;
            margin-top: 7px !important
        }

        .rnd-preview-pane {
            min-height: 340px !important
        }

        .rnd-drawer-footer {
            padding-top: 7px !important;
            margin-top: 7px !important
        }

        .rnd-upload-layer[hidden] {
            display: none !important
        }

        .rnd-upload-layer {
            position: fixed;
            inset: 0;
            z-index: 10050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .42);
            backdrop-filter: blur(3px)
        }

        .rnd-upload-layer-card {
            width: min(370px, 100%);
            padding: 27px 26px 23px;
            text-align: center;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 25px 85px rgba(15, 23, 42, .28);
            font-family: Arial, sans-serif;
            color: #17243a
        }

        .rnd-layer-icon {
            font-size: 25px;
            color: #1769e8;
            margin-bottom: 8px
        }

        .rnd-upload-layer-card h3 {
            font-size: 17px;
            margin: 0 0 8px;
            font-weight: 800
        }

        .rnd-upload-layer-card p {
            font-size: 11px;
            color: #64748b;
            overflow-wrap: anywhere;
            min-height: 30px;
            margin: 0 0 12px
        }

        .rnd-layer-ring {
            width: 124px;
            height: 124px;
            margin: 0 auto 16px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: conic-gradient(#1769e8 0%, #e9f0fa 0%);
            position: relative
        }

        .rnd-layer-ring:before {
            content: '';
            position: absolute;
            inset: 11px;
            border-radius: 50%;
            background: white
        }

        .rnd-layer-ring span {
            position: relative;
            font-size: 27px;
            font-weight: 800;
            color: #1769e8
        }

        .rnd-layer-track {
            height: 8px;
            background: #e9f0fa;
            border-radius: 20px;
            overflow: hidden
        }

        #rndLayerBar {
            height: 100%;
            width: 0;
            background: #1769e8;
            border-radius: 20px;
            transition: width .2s
        }

        .rnd-layer-stats {
            font-size: 12px;
            color: #475569;
            font-weight: 700;
            margin: 12px 0
        }

        .rnd-upload-layer-card small {
            color: #94a3b8;
            font-size: 10px
        }

        @media(max-width:700px) {
            .rnd-archive-workspace {
                grid-template-columns: 1fr !important
            }

            .rnd-preview-pane {
                min-height: 220px !important
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important
            }
        }

        /* V8: modal reference layout - preview left, flexible file area, upload at bottom */
        @media (min-width:701px) {
            .rnd-drawer {
                width: calc(100vw - 20px) !important;
                max-width: 1560px !important;
                height: calc(100dvh - 20px) !important;
                padding: 12px 16px !important;
            }

            .rnd-archive-panel:not([hidden]) {
                display: flex !important;
                flex-direction: column !important;
                flex: 1 1 0 !important;
                min-height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
            }

            .rnd-archive-topbar {
                flex: 0 0 auto !important;
                margin: 0 0 8px !important;
                padding: 7px 10px !important;
            }

            .rnd-archive-workspace {
                display: grid !important;
                grid-template-columns: minmax(235px, 32%) minmax(0, 1fr) !important;
                gap: 8px !important;
                flex: 1 1 0 !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: hidden !important;
                align-items: stretch !important;
            }

            .rnd-preview-pane {
                position: relative !important;
                top: auto !important;
                height: 100% !important;
                min-height: 0 !important;
                overflow: hidden !important;
            }

            .rnd-archive-main {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
                height: 100% !important;
                min-height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
            }

            .rnd-drive-manager {
                display: flex !important;
                flex-direction: column !important;
                flex: 1 1 0 !important;
                height: auto !important;
                min-height: 130px !important;
                max-height: none !important;
                margin: 0 !important;
                overflow: hidden !important;
            }

            #rndDriveItems {
                flex: 1 1 0 !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow-y: auto !important;
                align-content: start !important;
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(auto-fill, minmax(125px, 1fr)) !important;
                gap: 9px !important;
                padding: 10px !important;
            }

            #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
                height: 95px !important;
            }

            #rndUploadForm {
                flex: 0 0 auto !important;
                margin: 0 !important;
                padding: 9px 11px !important;
            }

            #rndUploadForm .rnd-file-picker {
                padding: 7px 10px !important;
            }

            #rndUploadForm .rnd-upload-actions {
                padding-top: 5px !important;
                margin-top: 3px !important;
            }

            .rnd-drawer-footer {
                flex: 0 0 auto !important;
                margin-top: 7px !important;
                padding-top: 7px !important;
            }
        }

        @media (min-width:701px) and (max-height:620px) {
            .rnd-archive-topbar {
                padding: 5px 8px !important;
            }

            .rnd-upload-heading p,
            #rndUploadForm .rnd-file-picker span {
                display: none !important;
            }

            #rndUploadForm .rnd-selected-files {
                max-height: 20px !important;
            }
        }


        /* V9 Responsive Explorer: final overrides, preserving existing handlers */
        .rnd-drawer {
            font-size: 10px !important
        }

        .rnd-drawer h4,
        .rnd-drawer h6 {
            font-size: 12px !important
        }

        .rnd-drawer button,
        .rnd-drawer a,
        .rnd-drawer input,
        .rnd-drawer small,
        .rnd-drawer p,
        .rnd-drawer span,
        .rnd-drawer strong,
        .rnd-drawer label {
            font-size: inherit
        }

        .rnd-drive-manager-heading h4 {
            font-size: 15px !important
        }

        .rnd-drive-manager-heading p,
        .rnd-upload-heading p {
            font-size: 9px !important
        }

        .rnd-archive-topbar .rnd-article-banner h4 {
            font-size: 12px !important
        }

        .rnd-drive-item-modern .rnd-drive-item-name {
            font-size: 10px !important
        }

        .rnd-drive-item-modern .rnd-drive-item-meta {
            font-size: 8px !important
        }

        .rnd-drive-manager-actions button,
        .rnd-view-toggle,
        .rnd-back-button,
        .rnd-drive-link {
            font-size: 9px !important
        }

        .rnd-upload-heading h4 {
            font-size: 11px !important
        }

        .rnd-upload-layer-card h3 {
            font-size: 14px !important
        }

        .rnd-upload-layer-card p {
            font-size: 9px !important
        }

        .rnd-upload-layer-card .rnd-layer-ring span {
            font-size: 23px !important
        }

        .rnd-mobile-preview-close {
            display: none
        }

        @media (min-width:701px) {
            .rnd-archive-workspace {
                grid-template-columns: minmax(220px, 30%) minmax(0, 1fr) !important
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(auto-fill, minmax(122px, 1fr)) !important
            }
        }

        @media (max-width:700px) {
            .rnd-drawer {
                inset: 0 !important;
                top: 0 !important;
                left: 0 !important;
                transform: translateY(8px) !important;
                width: 100vw !important;
                height: 100dvh !important;
                min-height: 0 !important;
                border-radius: 0 !important;
                padding: 9px !important
            }

            .rnd-drawer.open {
                transform: translateY(0) !important
            }

            .rnd-drawer-header {
                margin-bottom: 7px !important
            }

            .rnd-drawer-header h6 {
                font-size: 13px !important
            }

            .rnd-archive-panel:not([hidden]) {
                display: block !important;
                flex: 1 1 auto !important;
                min-height: 0 !important;
                overflow-y: auto !important;
                overscroll-behavior: contain
            }

            .rnd-archive-topbar {
                padding: 7px !important;
                gap: 6px !important;
                margin-bottom: 8px !important
            }

            .rnd-archive-workspace {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
                overflow: visible !important;
                height: auto !important;
                min-height: 0 !important
            }

            .rnd-archive-main {
                display: flex !important;
                flex-direction: column !important;
                min-width: 0 !important;
                overflow: visible !important;
                height: auto !important
            }

            .rnd-preview-pane {
                display: none !important
            }

            .rnd-preview-pane.rnd-mobile-preview-open {
                display: flex !important;
                position: fixed !important;
                inset: 0 !important;
                z-index: 10020 !important;
                width: 100vw !important;
                height: 100dvh !important;
                min-height: 0 !important;
                background: #fff !important;
                border: 0 !important;
                border-radius: 0 !important;
                padding: 12px !important;
                flex-direction: column !important
            }

            .rnd-mobile-preview-close {
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: 8px;
                right: 9px;
                z-index: 3;
                width: 32px;
                height: 32px;
                border: 1px solid #dbe5f2;
                border-radius: 8px;
                background: white;
                color: #334155
            }

            .rnd-preview-pane.rnd-mobile-preview-open .rnd-preview-stage {
                flex: 1 !important;
                min-height: 0 !important;
                height: auto !important
            }

            .rnd-drive-manager {
                margin: 0 0 8px !important;
                min-height: 280px !important;
                max-height: none !important;
                overflow: hidden !important
            }

            .rnd-drive-manager-heading {
                padding: 9px !important;
                gap: 7px !important;
                flex-direction: column !important;
                align-items: stretch !important
            }

            .rnd-drive-manager-heading h4 {
                font-size: 13px !important
            }

            .rnd-drive-manager-actions {
                justify-content: flex-start !important
            }

            .rnd-drive-manager-actions button {
                padding: 7px 8px !important
            }

            .rnd-drive-breadcrumb,
            .rnd-drive-current-path {
                padding: 7px 9px !important
            }

            #rndDriveItems {
                max-height: none !important;
                min-height: 240px !important;
                overflow: visible !important
            }

            #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
                padding: 9px !important
            }

            #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
                height: 105px !important
            }

            #rndUploadForm {
                margin: 0 0 65px !important;
                padding: 10px !important
            }

            #rndUploadForm .rnd-upload-actions {
                position: sticky !important;
                bottom: 0 !important;
                background: white !important;
                z-index: 5 !important
            }

            #rndUploadButton {
                min-height: 35px !important
            }

            .rnd-drawer-footer {
                padding: 7px 0 !important;
                margin-top: 4px !important
            }

            .rnd-upload-layer-card {
                width: min(340px, calc(100vw - 30px)) !important;
                padding: 18px !important
            }

            .rnd-layer-ring {
                width: 100px !important;
                height: 100px !important
            }
        }


        /* V10 FIX: keep folder cards visible; avoid shrinking manager below its content. */
        @media (min-width:701px) {
            #rndArchivePanel.rnd-archive-panel:not([hidden]) {
                display: block !important;
                flex: 1 1 auto !important;
                min-height: 0 !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
            }

            #rndArchivePanel .rnd-archive-workspace {
                display: grid !important;
                grid-template-columns: minmax(220px, 30%) minmax(0, 1fr) !important;
                align-items: start !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                gap: 10px !important;
            }

            #rndArchivePanel .rnd-preview-pane {
                position: sticky !important;
                top: 0 !important;
                height: clamp(340px, 65dvh, 600px) !important;
                min-height: 340px !important;
                overflow: hidden !important;
            }

            #rndArchivePanel .rnd-archive-main {
                display: flex !important;
                flex-direction: column !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                gap: 10px !important;
            }

            #rndArchivePanel #rndDriveManager {
                display: flex !important;
                flex-direction: column !important;
                flex: none !important;
                height: auto !important;
                min-height: 350px !important;
                max-height: none !important;
                overflow: hidden !important;
                margin: 0 !important;
            }

            #rndArchivePanel #rndDriveItems {
                flex: none !important;
                height: clamp(245px, 39dvh, 480px) !important;
                min-height: 245px !important;
                max-height: none !important;
                overflow-y: auto !important;
                align-content: start !important;
            }

            #rndArchivePanel #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(auto-fill, minmax(132px, 1fr)) !important;
                grid-auto-rows: max-content !important;
                gap: 10px !important;
            }

            #rndArchivePanel #rndDriveItems:not(.rnd-list-mode) .rnd-drive-preview {
                height: 105px !important
            }

            #rndArchivePanel #rndUploadForm {
                flex: none !important;
                margin: 0 !important
            }
        }

        @media (max-width:700px) {
            #rndArchivePanel .rnd-preview-pane:not(.rnd-mobile-preview-open) {
                display: none !important
            }

            #rndArchivePanel #rndDriveManager {
                min-height: 300px !important;
                height: auto !important
            }

            #rndArchivePanel #rndDriveItems {
                height: auto !important;
                min-height: 260px !important;
                max-height: none !important;
                overflow: visible !important
            }

            #rndArchivePanel #rndDriveItems.rnd-drive-items-modern:not(.rnd-list-mode) {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important
            }
        }
        .rnd-detail-table tbody tr.rnd-search-highlight td { background: #fff1b8 !important; border-top: 1px solid #f5c542; border-bottom: 1px solid #f5c542; }
        .rnd-detail-table tbody tr.rnd-search-highlight td:first-child { box-shadow: inset 4px 0 #eab308; }
        .rnd-search-result { margin: 0 0 14px; padding: 11px 14px; border-radius: 9px; background: #eff6ff; color: #1d4ed8; font-size: 12px; }
        .rnd-inline-search { display: flex; align-items: center; gap: 9px; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; color: #64748b; }
        .rnd-inline-search input { flex: 1; min-width: 0; border: 0; outline: 0; background: transparent; font-size: 12px; color: #17243a; }
        .rnd-inline-search span { font-size: 11px; white-space: nowrap; font-weight: 700; color: #2563eb; }
    </style>



    <script>
        (function() {

            'use strict';



            const drawer = document.getElementById('rndDrawer');

            const backdrop = document.getElementById('rndDrawerBackdrop');

            const closeDrawerButton = document.getElementById('rndCloseDrawer');

            const footerCloseButton = document.getElementById('rndFooterClose');



            const detailSection = document.getElementById('rndDetailSection');

            const detailBody = document.getElementById('rndDetailItems');
            const articleSearch = document.getElementById('rndArticleSearch');
            const articleSearchCount = document.getElementById('rndArticleSearchCount');
            const fileSearch = document.getElementById('rndFileSearch');
            const fileSearchCount = document.getElementById('rndFileSearchCount');
            let allArticleItems = [];
            const initialSearchKeyword = @json($search ?? '');
            let autoSearchDone = false;
            let highlightedArticleKey = null;
            articleSearch.addEventListener('input', () => renderItems(allArticleItems, false));
            fileSearch.addEventListener('input', () => renderDriveItems());


            const drawerSubtitle = document.getElementById('rndDrawerSubtitle');

            const drawerPo = document.getElementById('rndDrawerPo');

            const drawerBuyer = document.getElementById('rndDrawerBuyer');

            const drawerTotal = document.getElementById('rndDrawerTotal');



            const archivePanel = document.getElementById('rndArchivePanel');

            const archiveTitle = document.getElementById('rndArchiveTitle');

            const archiveSubtitle = document.getElementById('rndArchiveSubtitle');

            const folderStatus = document.getElementById('rndArchiveFolderStatus');
            const bindingOverlay = document.getElementById('rndArchiveBindingOverlay');
            const bindingMessage = document.getElementById('rndArchiveBindingMessage');
            let bindingSequence = 0;
            function setArchiveBinding(active, message) {
                if (!bindingOverlay) return;
                bindingOverlay.hidden = !active;
                if (message && bindingMessage) bindingMessage.textContent = message;
            }


            const driveLink = document.getElementById('rndOpenDriveFolder');

            const archiveFiles = document.getElementById('rndArchiveFiles');

            const fileCount = document.getElementById('rndArchiveFileCount');



            const uploadForm = document.getElementById('rndUploadForm');

            const photoInput = document.getElementById('rndPhotoInput');

            const selectedFiles = document.getElementById('rndSelectedFiles');
            const previewStage = document.getElementById('rndPreviewStage');
            const previewCaption = document.getElementById('rndPreviewCaption');
            const previewType = document.getElementById('rndPreviewType');
            const previewThumbs = document.getElementById('rndPreviewThumbs');

            const uploadButton = document.getElementById('rndUploadButton');

            const uploadMessage = document.getElementById('rndUploadMessage');

            const uploadProgress = document.getElementById('rndUploadProgress');

            const progressBar = document.getElementById('rndProgressBar');

            const progressText = document.getElementById('rndProgressText');



            const backToItems = document.getElementById('rndBackToItems');
            const driveBack = document.getElementById('rndDriveBack');
            const driveNewFolder = document.getElementById('rndDriveNewFolder');
            const driveRefresh = document.getElementById('rndDriveRefresh');
            const driveBreadcrumb = document.getElementById('rndDriveBreadcrumb');
            const driveCurrentPath = document.getElementById('rndDriveCurrentPath');
            const driveItems = document.getElementById('rndDriveItems');
            let activeRootFolderDbId = null;
            let activeFolderDbId = null;
            let driveBreadcrumbs = [];
            let driveCurrentItems = [];




            let activeItemsUrl = null;

            let activeArchiveUrl = null;

            let activeUploadUrl = null;

            let activeRequest = null;

            let previousFocus = null;

            let uploadInProgress = false;



            function escapeHtml(value) {

                return String(value ?? '').replace(/[&<>"']/g, function(char) {

                    return {

                        '&': '&amp;',

                        '<': '&lt;',

                        '>': '&gt;',

                        '"': '&quot;',

                        "'": '&#039;'

                    } [char];

                });

            }



            function setUploadMessage(message, type = '') {

                uploadMessage.textContent = message || '';

                uploadMessage.className = type;

            }



            async function fetchJson(url, options = {}) {
                // Ambil token CSRF terbaru dari meta tag pada halaman.
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content');

                const response = await fetch(url, {
                    credentials: 'same-origin',
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? {
                            'X-CSRF-TOKEN': csrfToken
                        } : {}),
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

                    if (response.status === 419) {
                        throw new Error(
                            'Sesi atau CSRF token kedaluwarsa. Refresh halaman (Ctrl+F5), login kembali jika diminta, lalu coba lagi.'
                        );
                    }

                    throw new Error(
                        `Server tidak mengirim JSON (HTTP ${response.status}). Periksa Console dan log Laravel.`
                    );
                }

                if (!response.ok || data.success === false) {
                    const validationMessage = data.errors ?
                        Object.values(data.errors).flat().join(' ') :
                        '';

                    if (response.status === 419) {
                        throw new Error(
                            'CSRF token mismatch. Refresh halaman (Ctrl+F5), login kembali jika diminta, lalu coba lagi.'
                        );
                    }

                    throw new Error(
                        validationMessage || data.message || `Permintaan gagal (HTTP ${response.status}).`
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
                bindingSequence++;
                setArchiveBinding(false);


                if (activeRequest) {

                    activeRequest.abort();

                    activeRequest = null;

                }



                archivePanel.hidden = true;

                detailSection.hidden = false;

                activeArchiveUrl = null;

                activeUploadUrl = null;
                activeRootFolderDbId = null;
                activeFolderDbId = null;
                driveBreadcrumbs = [];



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



            async function openDrawer(button, options = {}) {

                if (uploadInProgress) return;



                previousFocus = button;

                closeArchive();



                activeItemsUrl = button.dataset.itemsUrl;
                articleSearch.value = options.articleKeyword || '';
                highlightedArticleKey = options.highlightKey || null;
                allArticleItems = [];

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
                    if (highlightedArticleKey) {
                        requestAnimationFrame(() => {
                            const row = detailBody.querySelector('tr[data-article-key="' + highlightedArticleKey + '"]');
                            if (row) row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                    }

                } catch (error) {

                    if (error.name === 'AbortError') return;



                    drawerTotal.textContent = 'Gagal';

                    detailBody.innerHTML =

                        `<tr><td colspan="6" class="rnd-message">${escapeHtml(error.message)}</td></tr>`;

                }

            }



            // Pencarian tidak sensitif huruf besar/kecil dan spasi berlebih.
            function normalizeArchiveSearch(value) {
                return String(value ?? '')
                    .normalize('NFKC')
                    .toLocaleLowerCase('id-ID')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function renderItems(items, updateSource = true) {
                if (updateSource) allArticleItems = items;
                drawerTotal.textContent = allArticleItems.length + ' artikel';
                const keyword = normalizeArchiveSearch(articleSearch.value);
                items = allArticleItems.filter(item => {
                    const searchable = normalizeArchiveSearch([
                        item.article_code, item.article_nr_, item.nw_code,
                        item.description, item.nw_description,
                        item.nama_barang, item.item_name, item.name
                    ].filter(value => value != null).join(' '));
                    return !keyword || searchable.includes(keyword);
                });
                articleSearchCount.textContent = 'Found ' + items.length + ' records';



                if (!items.length) {

                    detailBody.innerHTML =

                        '<tr><td colspan="6" class="rnd-message">Tidak ada artikel yang cocok.</td></tr>';

                    return;

                }



                detailBody.innerHTML = items.map(function(item, index) {
                    const itemKey = String(item.id ?? item.article_code ?? index).replace(/[^a-zA-Z0-9_-]/g, '_');
                    const highlighted = highlightedArticleKey === itemKey;

                    const code = escapeHtml(item.article_code || '-');

                    const description = escapeHtml(item.description || '-');

                    const ready = Boolean(item.folder_exists);



                    return `

                <tr data-article-key="${itemKey}" class="${highlighted ? 'rnd-search-highlight' : ''}">

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

                            ${(String(item.article_code || '').trim() || String(item.description || '').trim()) ? '' : 'disabled'}>

                            <i class="fa fa-folder-open"></i> Buka

                        </button>

                    </td>

                </tr>

            `;

                }).join('');

            }




            function driveEndpoint(path = '') {
                if (!activeArchiveUrl) throw new Error('Arsip artikel belum dipilih.');
                return activeArchiveUrl.replace(/\/$/, '') + path;
            }

            function driveSize(bytes) {
                const n = Number(bytes || 0);
                if (!n) return '';
                return n < 1048576 ? Math.round(n / 1024) + ' KB' : (n / 1048576).toFixed(2) + ' MB';
            }

            async function loadDriveFolder(folderDbId) {
                if (!activeArchiveUrl || !folderDbId) return;
                driveItems.innerHTML =
                    '<div class="rnd-drive-empty rnd-drive-empty-modern"><i class="fa fa-spinner fa-spin"></i><strong>Memuat folder...</strong><span>Sebentar ya, bro.</span></div>';
                try {
                    const data = await fetchJson(driveEndpoint('/folders/' + encodeURIComponent(folderDbId)));
                    activeFolderDbId = data.active_folder.id;
                    driveBreadcrumbs = data.breadcrumbs || [];
                    rndMultiSelectedIds.clear();
                    driveCurrentItems = data.items || [];
                    fileSearch.value = '';
                    rndSelectedDriveId = null;
                    previewThumbs.replaceChildren();
                    renderDriveBreadcrumbs();
                    renderDriveItems();
                    driveCurrentPath.textContent = data.active_folder.name || 'Folder aktif';
                    driveBack.disabled = driveBreadcrumbs.length <= 1;
                    if (data.active_folder.url) {
                        driveLink.href = data.active_folder.url;
                        driveLink.hidden = false;
                    }
                } catch (e) {
                    driveItems.replaceChildren();
                    const error = document.createElement('div');
                    error.className = 'rnd-drive-empty';
                    error.textContent = e.message || 'Gagal memuat folder.';
                    driveItems.appendChild(error);
                    driveCurrentPath.textContent = 'Gagal membuka folder';
                }
            }

            function renderDriveBreadcrumbs() {
                driveBreadcrumb.replaceChildren();
                driveBreadcrumbs.forEach((crumb, index) => {
                    if (index) {
                        const sep = document.createElement('span');
                        sep.textContent = '›';
                        driveBreadcrumb.appendChild(sep);
                    }
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = crumb.name;
                    button.disabled = index === driveBreadcrumbs.length - 1;
                    button.addEventListener('click', () => loadDriveFolder(crumb.id));
                    driveBreadcrumb.appendChild(button);
                });
            }

            function showPreview(src, name, type = 'Foto') {
                previewStage.replaceChildren();
                previewType.textContent = type;
                previewCaption.textContent = name || 'Foto sampel';
                if (!src) {
                    previewStage.innerHTML =
                        '<div class="rnd-preview-placeholder"><i class="fa fa-picture-o"></i><strong>Pratinjau foto</strong><span>Pilih foto dari komputer atau klik foto tersimpan.</span></div>';
                    return;
                }
                const img = document.createElement('img');
                img.src = src;
                img.alt = name || 'Preview foto';
                img.onerror = () => {
                    previewStage.innerHTML =
                        '<div class="rnd-preview-placeholder"><i class="fa fa-file-image-o"></i><strong>Preview tidak tersedia</strong><span>File tetap dapat dibuka melalui Google Drive.</span></div>';
                };
                previewStage.appendChild(img);
            }

            function previewLocalFiles(files) {
                previewThumbs.replaceChildren();
                const images = Array.from(files || []).filter(file => file.type && file.type.startsWith('image/'));
                if (!images.length) {
                    showPreview('', '', 'Belum dipilih');
                    return;
                }
                const entries = images.map(file => ({
                    file,
                    url: URL.createObjectURL(file)
                }));
                showPreview(entries[0].url, entries[0].file.name, 'Foto akan diupload');
                entries.forEach((entry, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'rnd-preview-thumb' + (index === 0 ? ' active' : '');
                    button.title = entry.file.name;
                    const img = document.createElement('img');
                    img.src = entry.url;
                    img.alt = entry.file.name;
                    button.appendChild(img);
                    button.addEventListener('click', () => {
                        previewThumbs.querySelectorAll('.rnd-preview-thumb').forEach(el => el.classList
                            .remove('active'));
                        button.classList.add('active');
                        showPreview(entry.url, entry.file.name, 'Foto akan diupload');
                    });
                    previewThumbs.appendChild(button);
                });
            }

            const viewTileButton = document.getElementById('rndViewTile');
            const viewListButton = document.getElementById('rndViewList');
            let rndViewMode = 'tile';
            try {
                rndViewMode = localStorage.getItem('rndArchiveViewMode') === 'list' ? 'list' : 'tile';
            } catch (_) {}

            function applyRndViewMode(mode) {
                rndViewMode = mode;
                driveItems.classList.toggle('rnd-list-mode', mode === 'list');
                viewTileButton.classList.toggle('active', mode === 'tile');
                viewListButton.classList.toggle('active', mode === 'list');
                viewTileButton.setAttribute('aria-pressed', String(mode === 'tile'));
                viewListButton.setAttribute('aria-pressed', String(mode === 'list'));
                try {
                    localStorage.setItem('rndArchiveViewMode', mode);
                } catch (_) {}
            }
            viewTileButton.addEventListener('click', () => applyRndViewMode('tile'));
            viewListButton.addEventListener('click', () => applyRndViewMode('list'));
            applyRndViewMode(rndViewMode);

            // Operation overlay: ZIP uses elapsed-time/indeterminate progress because
            // the existing synchronous endpoint does not expose server progress.
            const rndOperationOverlay = document.createElement('div');
            rndOperationOverlay.className = 'rnd-operation-overlay';
            rndOperationOverlay.hidden = true;
            rndOperationOverlay.innerHTML = `<div class="rnd-operation-dialog" role="status" aria-live="polite"><div class="rnd-operation-spinner"></div><strong id="rndOperationTitle">Memproses...</strong><p id="rndOperationMessage"></p><div class="rnd-operation-track"><div id="rndOperationFill"></div></div><small id="rndOperationDetail"></small></div>`;
            document.body.appendChild(rndOperationOverlay);
            let rndOperationBusy = false;
            function rndOperationShow(title, message, percent = null, detail = '') {
                rndOperationOverlay.hidden = false;
                document.getElementById('rndOperationTitle').textContent = title;
                document.getElementById('rndOperationMessage').textContent = message;
                const fill = document.getElementById('rndOperationFill');
                fill.classList.toggle('indeterminate', percent === null);
                fill.style.width = percent === null ? '35%' : Math.max(0, Math.min(100, percent)) + '%';
                document.getElementById('rndOperationDetail').textContent = detail;
            }
            function rndOperationHide() { rndOperationOverlay.hidden = true; }
            function rndZipFilenameFromResponse(response) {
                const header = response.headers.get('Content-Disposition') || '';
                const utf = header.match(/filename\*=UTF-8''([^;]+)/i);
                if (utf) { try { return decodeURIComponent(utf[1]); } catch (_) {} }
                const plain = header.match(/filename="?([^";]+)"?/i);
                return plain ? plain[1] : 'Sample Pictures.zip';
            }
            async function rndDeleteItems(items) {
                if (rndOperationBusy || !items.length) return;
                if (!confirm('Pindahkan ' + items.length + ' item terpilih ke Trash Google Drive?')) return;
                rndOperationBusy = true;
                let done = 0;
                const failures = [];
                try {
                    rndOperationShow('Menghapus item', 'Menyiapkan proses hapus...', 0, '0 / ' + items.length);
                    for (const item of items) {
                        rndOperationShow('Menghapus item', 'Memproses: ' + item.name, Math.round(done / items.length * 100), done + ' / ' + items.length);
                        try {
                            await fetchJson(driveEndpoint('/items/' + encodeURIComponent(item.id)), {
                                method: 'DELETE', headers: {'Content-Type':'application/json'},
                                body: JSON.stringify({parent_folder_id: activeFolderDbId})
                            });
                            rndMultiSelectedIds.delete(String(item.id));
                            removeDriveItem(item.id);
                        } catch (error) { failures.push(item.name + ': ' + (error.message || 'Gagal')); }
                        done++;
                        rndOperationShow('Menghapus item', 'Memproses penghapusan...', Math.round(done / items.length * 100), done + ' / ' + items.length);
                    }
                } finally { rndOperationBusy = false; rndOperationHide(); rndUpdateMultiToolbar(); }
                explorerNotify((done - failures.length) + ' berhasil dihapus.' + (failures.length ? ' ' + failures.length + ' gagal.' : ''));
                if (failures.length) alert('Gagal menghapus:\n' + failures.slice(0, 10).join('\n'));
            }

            // Selection is based on Drive IDs; a photo preview click remains independent.
            const rndMultiSelectedIds = new Set();
            const rndMultiToolbar = document.createElement('div');
            rndMultiToolbar.className = 'rnd-multi-toolbar';
            rndMultiToolbar.hidden = true;
            driveItems.parentNode.insertBefore(rndMultiToolbar, driveItems);

            // Google Drive download URLs are cross-origin. Browsers can block automatic
            // multiple downloads; keep a clickable per-file fallback visible to users.
            function rndDriveDownloadUrl(item) {
                return 'https://drive.google.com/uc?export=download&id=' + encodeURIComponent(item.id);
            }

            function rndDownloadDriveFile(item) {
                if (!item || item.type === 'folder' || !item.id) return;
                const link = document.createElement('a');
                link.href = rndDriveDownloadUrl(item);
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                document.body.appendChild(link);
                link.click();
                link.remove();
            }

            // JSZip is loaded only when requested. No server-side ZIP request.
            let rndJsZipPromise = null;
            function rndLoadJsZip() {
                if (window.JSZip) return Promise.resolve(window.JSZip);
                if (!rndJsZipPromise) rndJsZipPromise = new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
                    script.onload = () => window.JSZip ? resolve(window.JSZip) : reject(new Error('JSZip tidak tersedia.'));
                    script.onerror = () => reject(new Error('Gagal memuat JSZip. Periksa koneksi atau CSP hosting.'));
                    document.head.appendChild(script);
                }).catch(error => { rndJsZipPromise = null; throw error; });
                return rndJsZipPromise;
            }
            async function rndSubmitZip(mode, selectedIds = [], folderId = null) {
                if (!activeArchiveUrl || !activeFolderDbId) return alert('Folder arsip belum siap.');
                if (rndOperationBusy) return;
                rndOperationBusy = true;
                try {
                    rndOperationShow('Menyiapkan ZIP', 'Membaca daftar file dan subfolder...', null, 'Menyiapkan manifest');
                    const JSZip = await rndLoadJsZip();
                    const form = new FormData();
                    form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    form.append('folder_id', activeFolderDbId);
                    form.append('mode', mode);
                    if (folderId) form.append('target_folder_id', folderId);
                    selectedIds.forEach(id => form.append('file_ids[]', id));
                    const manifestResponse = await fetch(driveEndpoint('/zip-manifest'), {
                        method: 'POST', body: form, credentials: 'same-origin', headers: {'Accept':'application/json'}
                    });
                    const manifest = await manifestResponse.json();
                    if (!manifestResponse.ok) throw new Error(manifest.message || 'Gagal membaca isi folder.');
                    const zip = new JSZip();
                    const files = manifest.items.filter(x => x.type === 'file');
                    manifest.items.filter(x => x.type === 'folder').forEach(x => zip.folder(x.path));
                    if (!manifest.items.length) throw new Error('Folder kosong.');
                    const seen = new Set();
                    function uniquePath(path) {
                        let candidate = path, n = 2;
                        while (seen.has(candidate.toLowerCase())) {
                            const slash = path.lastIndexOf('/');
                            const dot = path.lastIndexOf('.');
                            const stem = dot > slash ? path.slice(0, dot) : path;
                            const ext = dot > slash ? path.slice(dot) : '';
                            candidate = stem + ' (' + n++ + ')' + ext;
                        }
                        seen.add(candidate.toLowerCase());
                        return candidate;
                    }
                    // Small batches prevent flooding PHP and keep progress truthful.
                    let completed = 0;
                    let cursor = 0;
                    async function worker() {
                        while (cursor < files.length) {
                            const item = files[cursor++];
                            const url = driveEndpoint('/zip-file');
                            const body = new FormData();
                            body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            body.append('folder_id', activeFolderDbId);
                            body.append('file_id', item.id);
                            const response = await fetch(url, {method:'POST', body, credentials:'same-origin'});
                            if (!response.ok) throw new Error('Gagal mengambil ' + item.path + ' (HTTP ' + response.status + ')');
                            const blob = await response.blob();
                            zip.file(uniquePath(item.path), blob);
                            completed++;
                            rndOperationShow('Mengambil gambar', completed + ' dari ' + files.length + ' file berhasil diambil',
                                files.length ? Math.round(completed / files.length * 80) : 80, completed + ' / ' + files.length);
                        }
                    }
                    await Promise.all(Array.from({length: Math.min(3, files.length)}, () => worker()));
                    rndOperationShow('Membuat ZIP', 'Mengompresi file di browser...', 80, 'Kompresi dimulai');
                    const blob = await zip.generateAsync({type:'blob', compression:'DEFLATE', compressionOptions:{level:3}}, metadata => {
                        rndOperationShow('Membuat ZIP', 'Mengompresi file di browser...', 80 + Math.round(metadata.percent * .2),
                            Math.round(metadata.percent) + '% kompresi');
                    });
                    const url = URL.createObjectURL(blob);
                    const anchor = document.createElement('a');
                    anchor.href = url;
                    anchor.download = manifest.name || 'Sample Pictures.zip';
                    document.body.appendChild(anchor);
                    anchor.click();
                    anchor.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 60000);
                    explorerNotify('ZIP berhasil: ' + anchor.download);
                } catch (error) {
                    explorerNotify(error.message || 'Download ZIP gagal.');
                    alert(error.message || 'Download ZIP gagal.');
                } finally { rndOperationBusy = false; rndOperationHide(); }
            }

            function rndDownloadSelectedPhotos(chosen) {
                chosen = (chosen || []).filter(item => item && item.id);
                if (!chosen.length) return;
                rndSubmitZip('selected', chosen.map(item => String(item.id)));
            }

            function rndUpdateMultiToolbar() {
                for (const id of Array.from(rndMultiSelectedIds)) {
                    if (!driveCurrentItems.some(x => String(x.id) === id)) rndMultiSelectedIds.delete(id);
                }
                const count = rndMultiSelectedIds.size;
                rndMultiToolbar.hidden = false;
                rndMultiToolbar.replaceChildren();
                const selectAll = document.createElement('button');
                selectAll.type = 'button';
                const allItems = driveCurrentItems.filter(x => x.id && !x.__pending);
                const allChecked = allItems.length > 0 && allItems.every(x => rndMultiSelectedIds.has(String(x.id)));
                selectAll.textContent = allChecked ? 'Batalkan Semua' : 'Pilih Semua (' + allItems.length + ')';
                selectAll.onclick = () => {
                    if (allChecked) rndMultiSelectedIds.clear();
                    else allItems.forEach(x => rndMultiSelectedIds.add(String(x.id)));
                    renderDriveItemsStable();
                };
                rndMultiToolbar.appendChild(selectAll);
                if (!count) {
                    if (explorerClipboard) {
                        const pasteOnly = document.createElement('button');
                        pasteOnly.type = 'button';
                        pasteOnly.textContent = 'Paste ke Folder Ini';
                        pasteOnly.onclick = () => explorerPaste({id: activeFolderDbId, name: driveCurrentPath.textContent.trim()});
                        rndMultiToolbar.appendChild(pasteOnly);
                    }
                    return;
                }
                const label = document.createElement('strong');
                label.textContent = count + ' item dipilih';
                const download = document.createElement('button');
                download.type = 'button';
                download.textContent = 'Download terpilih (' + count + ')';
                download.addEventListener('click', () => {
                    const chosen = driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id)));
                    rndDownloadSelectedPhotos(chosen);
                });
                const removeSelected = document.createElement('button');
                removeSelected.type = 'button';
                removeSelected.textContent = 'Hapus Terpilih (' + count + ')';
                removeSelected.style.color = '#b91c1c';
                removeSelected.onclick = () => rndDeleteItems(driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id))));
                const clear = document.createElement('button');
                clear.type = 'button';
                clear.textContent = 'Batal pilih';
                clear.addEventListener('click', () => {
                    rndMultiSelectedIds.clear();
                    renderDriveItemsStable();
                });
                const cut = document.createElement('button');
                cut.type = 'button';
                cut.textContent = 'Cut / Pindahkan (' + count + ')';
                cut.onclick = () => {
                    explorerClipboard = {
                        mode: 'move',
                        items: driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id)))
                            .map(x => ({...x, parent_folder_id: activeFolderDbId}))
                    };
                    explorerNotify(count + ' item di-cut. Buka folder tujuan lalu klik Paste di toolbar atau menu folder.');
                    rndUpdateMultiToolbar();
                };
                const pasteHere = document.createElement('button');
                pasteHere.type = 'button';
                pasteHere.textContent = 'Paste ke Folder Ini';
                pasteHere.disabled = !explorerClipboard;
                pasteHere.onclick = () => explorerPaste({id: activeFolderDbId, name: driveCurrentPath.textContent.trim()});
                rndMultiToolbar.append(label, download, cut, pasteHere, removeSelected, clear);
            }

            function rndToggleMultiPhoto(item) {
                if (!item || !item.id) return;
                const id = String(item.id);
                if (rndMultiSelectedIds.has(id)) rndMultiSelectedIds.delete(id);
                else rndMultiSelectedIds.add(id);
                driveItems.querySelectorAll('.rnd-drive-item').forEach(card => {
                    const checked = rndMultiSelectedIds.has(String(card.dataset.fileKey));
                    card.classList.toggle('rnd-multi-selected', checked);
                    const circle = card.querySelector('.rnd-photo-multiselect');
                    if (circle) {
                        circle.setAttribute('aria-pressed', checked ? 'true' : 'false');
                        circle.innerHTML = checked ? '<i class="fa fa-check"></i>' : '';
                    }
                });
                rndUpdateMultiToolbar();
            }

            // Preview foto Google Drive: tetap di panel kiri, tanpa navigasi keluar.
            let rndSelectedDriveId = null;

            function rndDrivePhotoItems() {
                return driveCurrentItems.filter(item => item.type !== 'folder' &&
                    (item.thumbnail_url || item.thumbnailLink || item.thumbnail || item.url || item.id));
            }

            function rndDrivePreviewUrl(item) {
                return item.thumbnail_url || item.thumbnailLink || item.thumbnail ||
                    (item.id ? 'https://drive.google.com/thumbnail?id=' + encodeURIComponent(item.id) + '&sz=w1600' :
                        item.url || '');
            }

            function rndSelectDrivePhoto(item) {
                rndSelectedDriveId = String(item.id || item.name);
                showPreview(rndDrivePreviewUrl(item), item.name, 'Foto tersimpan');
                driveItems.querySelectorAll('.rnd-drive-item').forEach(card => {
                    card.classList.toggle('rnd-drive-selected', card.dataset.fileKey === rndSelectedDriveId);
                });
                previewThumbs.replaceChildren();
                const photos = rndDrivePhotoItems();
                photos.forEach(photo => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'rnd-preview-thumb' + (String(photo.id || photo.name) ===
                        rndSelectedDriveId ? ' active' : '');
                    button.title = photo.name || 'Foto';
                    const img = document.createElement('img');
                    img.src = rndDrivePreviewUrl(photo);
                    img.alt = photo.name || 'Foto';
                    img.loading = 'lazy';
                    button.appendChild(img);
                    button.addEventListener('click', () => rndSelectDrivePhoto(photo));
                    previewThumbs.appendChild(button);
                });
                // Navigasi panah hanya untuk foto di folder aktif.
                previewStage.querySelectorAll('.rnd-preview-nav').forEach(el => el.remove());
                const index = photos.findIndex(photo => String(photo.id || photo.name) === rndSelectedDriveId);
                if (photos.length > 1 && index >= 0) {
                    [
                        ['prev', -1, 'fa-chevron-left'],
                        ['next', 1, 'fa-chevron-right']
                    ].forEach(([direction, offset, icon]) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'rnd-preview-nav rnd-preview-nav-' + direction;
                        btn.setAttribute('aria-label', direction === 'prev' ? 'Foto sebelumnya' :
                            'Foto berikutnya');
                        btn.innerHTML = '<i class="fa ' + icon + '"></i>';
                        btn.addEventListener('click', () => rndSelectDrivePhoto(photos[(index + offset + photos
                            .length) % photos.length]));
                        previewStage.appendChild(btn);
                    });
                }
            }

            // Perbarui grid tanpa mengubah posisi scroll, folder aktif, atau preview.
            function renderDriveItemsStable() {
                const scrollTop = driveItems.scrollTop;
                const scrollLeft = driveItems.scrollLeft;
                renderDriveItems();
                driveItems.scrollTop = scrollTop;
                driveItems.scrollLeft = scrollLeft;
            }

            function upsertDriveItem(item) {
                if (!item || !item.id) return;
                const index = driveCurrentItems.findIndex(x => String(x.id) === String(item.id));
                if (index >= 0) driveCurrentItems[index] = {
                    ...driveCurrentItems[index],
                    ...item
                };
                else driveCurrentItems.unshift(item);
                renderDriveItemsStable();
            }

            function removeDriveItem(id) {
                driveCurrentItems = driveCurrentItems.filter(x => String(x.id) !== String(id));
                renderDriveItemsStable();
            }

            function renderDriveItems() {
                const keyword = normalizeArchiveSearch(fileSearch.value);
                const visibleDriveItems = driveCurrentItems.filter(item =>
                    !keyword || normalizeArchiveSearch(item.name).includes(keyword));
                fileSearchCount.textContent = 'Found ' + visibleDriveItems.length + ' records';
                driveItems.replaceChildren();
                rndUpdateMultiToolbar();
                driveItems.classList.add('rnd-drive-items-modern');
                if (!visibleDriveItems.length) {
                    const empty = document.createElement('div');
                    empty.className = 'rnd-drive-empty rnd-drive-empty-modern';
                    empty.innerHTML =
                        '<div class="rnd-empty-icon"><i class="fa fa-folder-open-o"></i></div><strong>Folder ini masih kosong</strong><span>Buat folder baru atau upload foto untuk mulai mengisi arsip.</span>';
                    driveItems.appendChild(empty);
                    return;
                }

                visibleDriveItems.forEach(item => {
                    const card = document.createElement('article');
                    card.className = 'rnd-drive-item rnd-drive-item-modern';
                    card.dataset.type = item.type;
                    card.dataset.fileKey = String(item.id || item.name);
                    if (item.__pending) {
                        card.classList.add('is-pending');
                        card.dataset.pendingId = String(item.id);
                    }
                    if (item.type !== 'folder' && card.dataset.fileKey === rndSelectedDriveId) card.classList
                        .add('rnd-drive-selected');

                    if (!item.__pending) {
                        const circle = document.createElement('button');
                        circle.type = 'button';
                        circle.className = 'rnd-photo-multiselect';
                        circle.title = 'Pilih foto untuk tindakan bersama';
                        const checked = rndMultiSelectedIds.has(String(item.id));
                        circle.setAttribute('aria-label', 'Pilih ' + (item.name || 'foto'));
                        circle.setAttribute('aria-pressed', checked ? 'true' : 'false');
                        if (checked) {
                            circle.innerHTML = '<i class="fa fa-check"></i>';
                            card.classList.add('rnd-multi-selected');
                        }
                        circle.addEventListener('click', event => {
                            event.stopPropagation();
                            rndToggleMultiPhoto(item);
                        });
                        card.appendChild(circle);
                    }

                    const preview = document.createElement('div');
                    preview.className = 'rnd-drive-preview';
                    if (item.type === 'folder') {
                        preview.classList.add('is-folder');
                        preview.innerHTML = '<i class="fa fa-folder"></i>';
                    } else {
                        const thumb = item.thumbnail_url || item.thumbnailLink || item.thumbnail ||
                            (item.id ? ('https://drive.google.com/thumbnail?id=' +
                                encodeURIComponent(item.id) + '&sz=w800') : '');
                        if (thumb && /^https?:\/\//i.test(thumb)) {
                            const img = document.createElement('img');
                            img.src = thumb;
                            img.alt = item.name || 'Foto sampel';
                            img.loading = 'lazy';
                            img.onerror = () => {
                                preview.innerHTML = '<i class="fa fa-file-image-o"></i>';
                            };
                            preview.appendChild(img);
                        } else {
                            preview.classList.add('is-file');
                            preview.innerHTML = '<i class="fa fa-file-image-o"></i>';
                        }
                    }

                    if (item.__pending) {
                        const overlay = document.createElement('div');
                        overlay.className = 'rnd-pending-overlay';
                        overlay.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i><span>' +
                            (item.__pendingLabel || 'Memproses...') + '</span>';
                        card.appendChild(overlay);
                    }

                    const topActions = document.createElement('div');
                    topActions.className = 'rnd-drive-card-menu';
                    topActions.innerHTML = '<i class="fa fa-ellipsis-h"></i>';
                    topActions.title = 'Aksi item';

                    const info = document.createElement('div');
                    info.className = 'rnd-drive-item-info';
                    const name = document.createElement('div');
                    name.className = 'rnd-drive-item-name';
                    name.textContent = item.name || 'Tanpa nama';
                    name.title = item.name || '';
                    const meta = document.createElement('div');
                    meta.className = 'rnd-drive-item-meta';
                    if (item.type === 'folder') {
                        const date = item.created_time ? new Date(item.created_time) : null;
                        const formatted = date && !isNaN(date.getTime())
                            ? new Intl.DateTimeFormat('en-GB', {day:'2-digit', month:'short', year:'numeric', timeZone:'Asia/Jakarta'}).format(date)
                            : '—';
                        meta.textContent = 'Folder · Created: ' + formatted;
                    } else {
                        meta.textContent = driveSize(item.size) || item.mime_type || 'Foto';
                    }
                    info.append(name, meta);
                    card.append(preview, topActions, info);

                    if (item.type === 'folder') {
                        card.addEventListener('click', () => {
                            showPreview('', item.name, 'Folder');
                            previewStage.innerHTML =
                                '<div class="rnd-preview-placeholder"><i class="fa fa-folder-open" style="color:#f2b400"></i><strong>' +
                                escapeHtml(item.name || 'Folder') +
                                '</strong><span>Double-click untuk membuka folder ini.</span></div>';
                            previewThumbs.replaceChildren();
                        });
                        card.title = 'Klik dua kali untuk membuka folder';
                        card.addEventListener('dblclick', () => {
                            if (item.database_id) loadDriveFolder(item.database_id);
                            else alert('Metadata folder belum tersedia. Klik Refresh lalu coba lagi.');
                        });
                    } else {
                        card.addEventListener('click', () => rndSelectDrivePhoto(item));
                        card.title = 'Klik untuk preview foto di sebelah kiri';
                        preview.style.cursor = 'pointer';
                    }

                    const actions = document.createElement('div');
                    actions.className = 'rnd-drive-item-actions rnd-drive-item-actions-modern';
                    if (item.type !== 'folder' && item.url) {
                        const open = document.createElement('button');
                        open.type = 'button';
                        open.innerHTML = '<i class="fa fa-external-link"></i> Buka';
                        open.addEventListener('click', event => {
                            event.stopPropagation();
                            window.open(item.url, '_blank',
                                'noopener,noreferrer');
                        });
                        actions.appendChild(open);
                    }
                    const rename = document.createElement('button');
                    rename.type = 'button';
                    rename.innerHTML = '<i class="fa fa-pencil"></i> Rename';
                    rename.addEventListener('click', async event => {
                        event.stopPropagation();
                        const value = prompt('Nama baru:', item.name);
                        if (value === null || !value.trim() || value.trim() === item.name) return;
                        try {
                            const renameResult = await fetchJson(driveEndpoint('/items/' +
                                encodeURIComponent(item
                                    .id)), {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    name: value.trim(),
                                    parent_folder_id: activeFolderDbId
                                })
                            });
                            item.name = renameResult?.item?.name || value.trim();
                            upsertDriveItem(item);
                            if (rndSelectedDriveId === String(item.id)) showPreview('', item.name,
                                'Foto');
                        } catch (e) {
                            alert(e.message || 'Gagal rename item.');
                        }
                    });
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'danger';
                    remove.innerHTML = '<i class="fa fa-trash"></i> Hapus';
                    remove.addEventListener('click', async event => {
                        event.stopPropagation();
                        if (!confirm(`Pindahkan "${item.name}" ke Trash Google Drive?`)) return;
                        try {
                            await fetchJson(driveEndpoint('/items/' + encodeURIComponent(item
                                .id)), {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    parent_folder_id: activeFolderDbId
                                })
                            });
                            removeDriveItem(item.id);
                        } catch (e) {
                            alert(e.message || 'Gagal menghapus item.');
                        }
                    });
                    actions.append(rename, remove);
                    card.appendChild(actions);
                    topActions.addEventListener('click', event => {
                        event.stopPropagation();
                        const isOpen = actions.classList.toggle('is-open');
                        topActions.innerHTML = isOpen ? '<i class="fa fa-times"></i>' :
                            '<i class="fa fa-ellipsis-h"></i>';
                    });
                    driveItems.appendChild(card);
                });
            }

            // Explorer interactions: drag/move, copy/paste, undo.
            // Backend endpoints required: POST /items/{driveId}/move and POST /items/{driveId}/copy.
            let explorerSelected = null;
            let explorerClipboard = null;
            let explorerUndo = null;
            let explorerBusy = false;
            const explorerLayer = document.createElement('div');
            explorerLayer.className = 'rnd-explorer-confirm';
            explorerLayer.hidden = true;
            explorerLayer.innerHTML =
                '<div class="rnd-explorer-dialog" role="dialog" aria-modal="true" aria-labelledby="rndMoveTitle"><i class="fa fa-folder-open"></i><h4 id="rndMoveTitle">Pindahkan item?</h4><p id="rndMoveDescription"></p><div><button type="button" data-answer="cancel">Cancel</button><button type="button" class="primary" data-answer="move">Move</button></div></div>';
            document.body.appendChild(explorerLayer);
            const explorerToast = document.createElement('div');
            explorerToast.className = 'rnd-explorer-toast';
            explorerToast.hidden = true;
            document.body.appendChild(explorerToast);

            function explorerNotify(message, undoable = false) {
                explorerToast.replaceChildren();
                const label = document.createElement('span');
                label.textContent = message;
                explorerToast.appendChild(label);
                if (undoable) {
                    const undo = document.createElement('button');
                    undo.type = 'button';
                    undo.textContent = 'Undo (Ctrl+Z)';
                    undo.addEventListener('click', explorerUndoMove);
                    explorerToast.appendChild(undo);
                }
                explorerToast.hidden = false;
            }

            function explorerConfirm(item, folder) {
                return new Promise(resolve => {
                    document.getElementById('rndMoveDescription').textContent = 'Move "' + item.name +
                        '" into "' + folder.name + '" folder?';
                    explorerLayer.hidden = false;

                    function done(event) {
                        const answer = event.target.closest('[data-answer]');
                        if (!answer) return;
                        explorerLayer.removeEventListener('click', done);
                        explorerLayer.hidden = true;
                        resolve(answer.dataset.answer === 'move');
                    }
                    explorerLayer.addEventListener('click', done);
                });
            }

            function explorerCardItem(card) {
                return driveCurrentItems.find(item => String(item.id) === card.dataset.explorerId);
            }
            async function explorerRequest(item, target, operation) {
                if (explorerBusy || uploadInProgress) return;
                if (!item || !item.id || !target || !target.id) return;
                if (operation === 'copy' && item.type === 'folder') {
                    explorerNotify('Copy folder belum didukung. Gunakan Move.');
                    return;
                }
                if (operation === 'move' && String(item.database_id || '') === String(target.id)) {
                    explorerNotify('Folder tidak dapat dipindahkan ke dirinya sendiri.');
                    return;
                }
                if (operation === 'move' && String(item.parent_folder_id || activeFolderDbId) === String(target
                    .id)) {
                    explorerNotify('Item sudah berada di folder tersebut.');
                    return;
                }
                const fromId = item.parent_folder_id || activeFolderDbId;
                explorerBusy = true;
                try {
                    const operationResult = await fetchJson(driveEndpoint('/items/' + encodeURIComponent(item.id) +
                        '/' + operation), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            target_folder_id: target.id,
                            parent_folder_id: fromId
                        })
                    });
                    if (operation === 'move') explorerUndo = {
                        item: {
                            ...item
                        },
                        fromId,
                        toId: target.id
                    };
                    if (operation === 'move') removeDriveItem(item.id);
                    else if (String(target.id) === String(activeFolderDbId) && operationResult?.item?.id)
                        upsertDriveItem(operationResult.item);
                    explorerNotify(operation === 'move' ? 'Moved "' + item.name + '" into "' + target.name + '".' :
                        'Copied "' + item.name + '".', operation === 'move');
                } catch (error) {
                    explorerNotify(error.message || 'Operasi gagal.');
                } finally {
                    explorerBusy = false;
                }
            }
            async function explorerUndoMove() {
                if (!explorerUndo || explorerBusy || uploadInProgress) return;
                const record = explorerUndo;
                explorerBusy = true;
                try {
                    await fetchJson(driveEndpoint('/items/' + encodeURIComponent(record.item.id) + '/move'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            target_folder_id: record.fromId,
                            parent_folder_id: record.toId
                        })
                    });
                    explorerUndo = null;
                    if (String(record.fromId) === String(activeFolderDbId)) upsertDriveItem(record.item);
                    else removeDriveItem(record.item.id);
                    explorerNotify('Undo berhasil. Item kembali ke folder asal.');
                } catch (error) {
                    explorerNotify(error.message || 'Undo gagal.');
                } finally {
                    explorerBusy = false;
                }
            }

            async function explorerPaste(target) {
                if (!explorerClipboard) return explorerNotify('Clipboard kosong. Pilih item lalu Cut atau Copy.');
                const clipboard = explorerClipboard;
                const items = clipboard.items || (clipboard.item ? [clipboard.item] : []);
                const operation = clipboard.mode === 'move' ? 'move' : 'copy';
                if (!items.length) return;
                if (operation === 'move' && !confirm('Pindahkan ' + items.length + ' item ke folder tujuan?')) return;
                let done = 0;
                for (const item of items) {
                    if (explorerBusy || uploadInProgress) break;
                    // Keep failed items in the clipboard for retry.
                    try {
                        const response = await fetchJson(driveEndpoint('/items/' + encodeURIComponent(item.id) + '/' + operation), {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({target_folder_id: target.id, parent_folder_id: item.parent_folder_id})
                        });
                        done++;
                        if (operation === 'move') removeDriveItem(item.id);
                        else if (String(target.id) === String(activeFolderDbId) && response?.item?.id) upsertDriveItem(response.item);
                    } catch (error) {
                        explorerNotify(done + '/' + items.length + ' berhasil. Gagal pada ' + item.name + ': ' + (error.message || 'error'));
                        if (operation === 'move') explorerClipboard = {mode: 'move', items: items.slice(done)};
                        return;
                    }
                }
                if (operation === 'move') {
                    explorerClipboard = null;
                    rndMultiSelectedIds.clear();
                    renderDriveItemsStable();
                }
                explorerNotify(done + ' item berhasil ' + (operation === 'move' ? 'dipindahkan.' : 'disalin.'));
            }

            function explorerContextMenu(event, card) {
                event.preventDefault();
                const item = explorerCardItem(card);
                if (!item) return;
                explorerSelected = item;
                const menu = document.createElement('div');
                menu.className = 'rnd-explorer-context';
                const copy = document.createElement('button');
                copy.textContent = 'Copy  Ctrl+C';
                copy.onclick = () => {
                    explorerClipboard = {mode: 'copy', item: {...item, parent_folder_id: activeFolderDbId}};
                    explorerNotify('Copied: ' + item.name);
                    menu.remove();
                };
                menu.appendChild(copy);
                const cutItem = document.createElement('button');
                cutItem.textContent = 'Cut / Move  Ctrl+X';
                cutItem.onclick = () => {
                    const chosen = rndMultiSelectedIds.has(String(item.id))
                        ? driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id))) : [item];
                    explorerClipboard = {mode: 'move', items: chosen.map(x => ({...x, parent_folder_id: activeFolderDbId}))};
                    explorerNotify(chosen.length + ' item di-cut. Buka folder tujuan lalu Paste.');
                    rndUpdateMultiToolbar();
                    menu.remove();
                };
                menu.appendChild(cutItem);
                const deleteItem = document.createElement('button');
                deleteItem.textContent = 'Hapus ke Trash';
                deleteItem.onclick = () => {
                    const chosen = rndMultiSelectedIds.has(String(item.id))
                        ? driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id))) : [item];
                    menu.remove(); rndDeleteItems(chosen);
                };
                menu.appendChild(deleteItem);

                if (item.type !== 'folder') {
                    const download = document.createElement('button');
                    download.textContent = 'Download';
                    download.onclick = () => {
                        const chosen = rndMultiSelectedIds.has(String(item.id))
                            ? driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id)))
                            : [item];
                        rndDownloadSelectedPhotos(chosen);
                        menu.remove();
                    };
                    menu.appendChild(download);
                }
                if (item.type === 'folder' && item.database_id) {
                    const zipDownload = document.createElement('button');
                    zipDownload.textContent = 'Download ZIP';
                    zipDownload.onclick = () => {
                        rndSubmitZip('folder', [], item.database_id);
                        menu.remove();
                    };
                    menu.appendChild(zipDownload);
                }
                if (item.type === 'folder' && item.database_id) {
                    const paste = document.createElement('button');
                    paste.textContent = 'Paste into folder  Ctrl+V';
                    paste.disabled = !explorerClipboard;
                    paste.onclick = () => {
                        explorerPaste({
                            id: item.database_id,
                            name: item.name
                        });
                        menu.remove();
                    };
                    menu.appendChild(paste);
                }
                menu.style.left = Math.min(event.clientX, innerWidth - 200) + 'px';
                menu.style.top = Math.min(event.clientY, innerHeight - 100) + 'px';
                document.querySelectorAll('.rnd-explorer-context').forEach(el => el.remove());
                document.body.appendChild(menu);
                setTimeout(() => document.addEventListener('pointerdown', e => {
                    if (!menu.contains(e.target)) menu.remove();
                }, {
                    once: true
                }), 0);
            }
            // MutationObserver preserves existing rendering and all existing click handlers.
            const explorerObserver = new MutationObserver(() => {
                driveItems.querySelectorAll('.rnd-drive-item:not([data-explorer-bound])').forEach(card => {
                    card.dataset.explorerBound = '1';
                    card.dataset.explorerId = card.dataset.fileKey;
                    card.draggable = true;
                    card.addEventListener('click', () => {
                        explorerSelected = explorerCardItem(card);
                    });
                    card.addEventListener('contextmenu', e => explorerContextMenu(e, card));
                    card.addEventListener('dragstart', e => {
                        const item = explorerCardItem(card);
                        if (!item || uploadInProgress) {
                            e.preventDefault();
                            return;
                        }
                        explorerSelected = {
                            ...item,
                            parent_folder_id: activeFolderDbId
                        };
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', String(item.id));
                    });
                    if (card.dataset.type === 'folder') {
                        card.addEventListener('dragover', e => {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                            card.classList.add('rnd-drop-target');
                        });
                        card.addEventListener('dragleave', () => card.classList.remove(
                            'rnd-drop-target'));
                        card.addEventListener('drop', async e => {
                            e.preventDefault();
                            e.stopPropagation();
                            card.classList.remove('rnd-drop-target');
                            const source = driveCurrentItems.find(x => String(x.id) === e
                                .dataTransfer.getData('text/plain'));
                            const target = explorerCardItem(card);
                            if (!source || !target || !target.database_id || source.id ===
                                target.id) return;
                            if (await explorerConfirm({
                                    ...source,
                                    parent_folder_id: activeFolderDbId
                                }, target)) await explorerRequest({
                                ...source,
                                parent_folder_id: activeFolderDbId
                            }, {
                                id: target.database_id,
                                name: target.name
                            }, 'move');
                        });
                    }
                });
            });
            explorerObserver.observe(driveItems, {
                childList: true
            });
            document.addEventListener('keydown', e => {
                if (!drawer.classList.contains('open') || archivePanel.hidden) return;
                if (e.target.closest('input,textarea,[contenteditable="true"]')) return;
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c' && explorerSelected) {
                    e.preventDefault();
                    explorerClipboard = {
                        item: {
                            ...explorerSelected,
                            parent_folder_id: activeFolderDbId
                        }
                    };
                    explorerNotify('Copied: ' + explorerSelected.name + '. Open destination and press Ctrl+V.');
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'x' && explorerSelected) {
                    e.preventDefault();
                    const chosen = rndMultiSelectedIds.has(String(explorerSelected.id))
                        ? driveCurrentItems.filter(x => rndMultiSelectedIds.has(String(x.id))) : [explorerSelected];
                    explorerClipboard = {mode: 'move', items: chosen.map(x => ({...x, parent_folder_id: activeFolderDbId}))};
                    explorerNotify(chosen.length + ' item di-cut. Buka folder tujuan lalu Ctrl+V.');
                    rndUpdateMultiToolbar();
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'v' && explorerClipboard &&
                    activeFolderDbId) {
                    e.preventDefault();
                    explorerPaste({
                        id: activeFolderDbId,
                        name: driveCurrentPath.textContent.trim()
                    });
                }
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z' && explorerUndo) {
                    e.preventDefault();
                    explorerUndoMove();
                }
            });

            driveBack.addEventListener('click', () => {
                if (driveBreadcrumbs.length > 1) loadDriveFolder(driveBreadcrumbs[driveBreadcrumbs.length - 2]
                    .id);
            });
            driveRefresh.addEventListener('click', () => {
                if (activeFolderDbId) loadDriveFolder(activeFolderDbId);
            });
            driveNewFolder.addEventListener('click', async () => {
                if (!activeFolderDbId) return alert('Folder arsip belum siap.');
                const name = prompt('Nama folder baru:');
                if (name === null || !name.trim()) return;

                const tempId = 'pending-folder-' + Date.now();
                driveCurrentItems.unshift({
                    id: tempId, name: name.trim(), type: 'folder',
                    __pending: true, __pendingLabel: 'Membuat folder...'
                });
                renderDriveItemsStable();
                driveNewFolder.disabled = true;
                try {
                    const createdResult = await fetchJson(driveEndpoint('/folders'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name.trim(),
                            parent_folder_id: activeFolderDbId
                        })
                    });
                    removeDriveItem(tempId);
                    if (createdResult?.item?.id) upsertDriveItem(createdResult.item);
                    else await loadDriveFolder(activeFolderDbId);
                } catch (e) {
                    removeDriveItem(tempId);
                    alert(e.message || 'Gagal membuat folder.');
                } finally {
                    driveNewFolder.disabled = false;
                }
            });

            async function openArticleArchive(button) {
                const myBindingSequence = ++bindingSequence;


                if (uploadInProgress) return;



                activeArchiveUrl = button.dataset.archiveUrl;

                activeUploadUrl = button.dataset.uploadUrl;
                activeRootFolderDbId = null;
                activeFolderDbId = null;
                driveBreadcrumbs = [];



                detailSection.hidden = true;

                archivePanel.hidden = false;
                setArchiveBinding(true, 'Memeriksa dan menghubungkan folder artikel di Google Drive. Mohon tunggu...');




                archiveTitle.textContent = button.dataset.code || 'Arsip Artikel';

                archiveSubtitle.textContent = button.dataset.description || '';

                folderStatus.className = 'rnd-folder-status';

                folderStatus.innerHTML =

                    '<i class="fa fa-circle-o-notch fa-spin"></i> Menyiapkan direktori artikel...';



                driveLink.hidden = true;

                archiveFiles.innerHTML =

                    '<div class="rnd-message">Memuat foto...</div>';

                fileCount.textContent = 'Memuat...';



                photoInput.value = '';

                selectedFiles.textContent = 'Belum ada foto dipilih.';
                previewThumbs.replaceChildren();
                showPreview('', '', 'Belum dipilih');

                setUploadMessage('');



                if (activeRequest) activeRequest.abort();

                activeRequest = new AbortController();



                try {

                    const result = await fetchJson(activeArchiveUrl, {

                        signal: activeRequest.signal

                    });



                    folderStatus.className = 'rnd-folder-status ready';

                    folderStatus.innerHTML =

                        '<i class="fa fa-check-circle"></i> Directory article ready in DB';



                    if (result.folder && result.folder.url) {

                        driveLink.href = result.folder.url;

                        driveLink.hidden = false;

                    }



                    renderArchiveFiles(result.files || []);
                    if (result.folder && result.folder.database_id) {
                        activeRootFolderDbId = result.folder.database_id;
                        setArchiveBinding(true, 'Folder ditemukan. Sedang memuat daftar foto dan subfolder...');
                        await loadDriveFolder(activeRootFolderDbId);
                    } else {
                        driveItems.innerHTML =
                            '<div class="rnd-drive-empty">ID folder database tidak ada. Tambahkan folder.database_id di respons controller.</div>';
                    }


                } catch (error) {

                    if (error.name === 'AbortError') return;



                    folderStatus.className = 'rnd-folder-status error';

                    folderStatus.textContent = error.message;

                    archiveFiles.innerHTML =

                        '<div class="rnd-message">Arsip tidak dapat dimuat.</div>';

                    fileCount.textContent = '0 foto';

                } finally {
                    if (myBindingSequence === bindingSequence) setArchiveBinding(false);
                }

            }



            function renderArchiveFiles(files) {

                fileCount.textContent = files.length + ' foto';



                if (!files.length) {

                    archiveFiles.innerHTML =

                        '<div class="rnd-message">Belum ada foto. Upload foto pertama untuk artikel ini.</div>';

                    return;

                }



                archiveFiles.innerHTML = files.map(function(file) {

                    const name = escapeHtml(file.name || 'Foto sampel');

                    const url = file.url ? escapeHtml(file.url) : '';

                    const size = Number(file.file_size || 0);

                    const sizeText = size > 0

                        ?
                        (size / 1048576).toFixed(2) + ' MB'

                        :
                        'Ukuran tidak tersedia';



                    const link = url

                        ?
                        `<a href="${url}" target="_blank" rel="noopener noreferrer">${name}</a>`

                        :
                        `<span>${name}</span>`;



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



            photoInput.addEventListener('change', function() {

                const files = Array.from(photoInput.files || []);



                if (!files.length) {

                    selectedFiles.textContent = 'Belum ada foto dipilih.';

                    return;

                }



                selectedFiles.textContent = files.length + ' foto dipilih: ' +

                    files.map(file => file.name).join(', ');
                // Jangan decode/membuat thumbnail saat file dipilih.
                // Preview baru ditampilkan setelah seluruh batch diproses.
                previewThumbs.replaceChildren();
                showPreview('', '', 'Menunggu upload selesai');
                const waitingPreview = document.createElement('div');
                waitingPreview.className = 'rnd-preview-thumb-waiting';
                waitingPreview.setAttribute('role', 'status');
                waitingPreview.textContent = 'Waiting completed upload — ' + files.length + ' foto siap diunggah. Preview muncul setelah 100%.';
                previewThumbs.appendChild(waitingPreview);

                setUploadMessage('Foto siap diunggah. Preview akan muncul setelah proses mencapai 100%.');

            });



            function validatePhotos(files) {

                if (!files.length) {

                    throw new Error('Pilih minimal satu foto terlebih dahulu.');

                }



                if (files.length > 50) {

                    throw new Error('Maksimal 50 foto dalam satu kali upload.');

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



                    if (file.size > 25 * 1024 * 1024) {

                        throw new Error('Ukuran foto melebihi 25 MB: ' + file.name);

                    }

                }

            }



            // XHR diperlukan untuk membaca progres upload browser -> hosting.
            // fetch() tidak menyediakan event upload progress yang kompatibel secara luas.
            function uploadPhotoWithProgress(url, formData, onProgress, timeoutMs = 600000) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', url, true);
                    xhr.timeout = timeoutMs;
                    xhr.withCredentials = true;
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
                    xhr.upload.addEventListener('progress', event => {
                        if (event.lengthComputable) onProgress(event.loaded, event.total);
                    });
                    xhr.upload.addEventListener('load', () => onProgress(1, 1));
                    xhr.onload = () => {
                        let data;
                        try { data = JSON.parse(xhr.responseText); }
                        catch (_) {
                            reject(new Error(xhr.status === 419 ? 'Sesi berakhir. Refresh halaman.' :
                                'Respons server bukan JSON (HTTP ' + xhr.status + ').'));
                            return;
                        }
                        if (xhr.status < 200 || xhr.status >= 300) {
                            reject(new Error(data.message || 'HTTP ' + xhr.status));
                        } else resolve(data);
                    };
                    xhr.onerror = () => reject(new Error('Koneksi ke hosting terputus.'));
                    xhr.ontimeout = () => reject(new Error('Waktu respons server habis. Periksa foto di aplikasi sebelum mencoba ulang.'));
                    xhr.onabort = () => reject(new Error('Upload dibatalkan.'));
                    xhr.send(formData);
                });
            }

            // Shared-hosting-safe upload: browser -> Laravel -> Google Drive.
            // Direct Google Drive PUT is intentionally disabled because browser CORS
            // can hide a successful upload response and cause misleading failures.

            const uploadLayer = document.getElementById('rndUploadLayer');
            const layerPercent = document.getElementById('rndLayerPercent');
            const layerBar = document.getElementById('rndLayerBar');
            const layerRing = document.getElementById('rndLayerRing');
            const layerFilename = document.getElementById('rndLayerFilename');
            const layerStats = document.getElementById('rndLayerStats');

            function syncUploadLayer() {
                const value = Math.max(0, Math.min(100, parseFloat(progressBar.style.width) || 0));
                layerPercent.textContent = Math.round(value) + '%';
                layerBar.style.width = value + '%';
                layerRing.style.background = 'conic-gradient(#1769e8 ' + value + '%,#e9f0fa ' + value + '%)';
                layerFilename.textContent = progressText.textContent || 'Mengunggah foto...';
                layerStats.textContent = uploadMessage.textContent || 'Sedang diproses...';
            }
            const layerObserver = new MutationObserver(syncUploadLayer);
            layerObserver.observe(progressText, {
                childList: true,
                characterData: true,
                subtree: true
            });
            layerObserver.observe(uploadMessage, {
                childList: true,
                characterData: true,
                subtree: true
            });
            layerObserver.observe(progressBar, {
                attributes: true,
                attributeFilter: ['style']
            });

            uploadForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                if (uploadInProgress) return;

                const files = Array.from(photoInput.files || []);
                try {
                    if (!activeUploadUrl) throw new Error('Buka artikel terlebih dahulu.');
                    validatePhotos(files);
                } catch (error) {
                    setUploadMessage(error.message, 'error');
                    return;
                }

                uploadInProgress = true;
                uploadButton.disabled = true;
                photoInput.disabled = true;
                uploadProgress.hidden = false;
                progressBar.style.width = '0%';
                uploadLayer.hidden = false;
                syncUploadLayer();

                const csrf = document.querySelector('meta[name="csrf-token"]');
                const successful = [];
                const failed = [];

                // Mode ringan: tidak menambahkan placeholder gambar, object URL,
                // atau merender ulang grid saat upload masih berjalan.
                const uploadedBatchItems = [];
                const originalUploadFolderId = activeFolderDbId;
                previewThumbs.replaceChildren();
                showPreview('', '', 'Waiting completed upload');
                const pendingText = document.createElement('div');
                pendingText.className = 'rnd-preview-thumb-waiting';
                pendingText.setAttribute('role', 'status');
                pendingText.textContent = 'Waiting completed upload — preview foto muncul setelah 100%.';
                previewThumbs.appendChild(pendingText);

                const formatBytes = bytes => {
                    if (!bytes) return '0 KB';
                    return bytes >= 1024 * 1024 ?
                        (bytes / (1024 * 1024)).toFixed(1) + ' MB' :
                        Math.max(1, Math.round(bytes / 1024)) + ' KB';
                };

                // Satu request per giliran agar PHP worker shared hosting tidak terbebani.
                try {
                    const MAX_PARALLEL_UPLOADS = 1;
                    let nextFileIndex = 0;
                    let processedCount = 0;
                    let runningCount = 0;
                    // 0..0.9: browser -> hosting, 0.9: menunggu Google Drive,
                    // 1.0: respons sukses dari backend. Progress total berbobot ukuran file.
                    const fileProgress = files.map(() => 0);
                    const filePhases = files.map(() => 'Menunggu giliran');
                    const totalBytes = files.reduce((sum, file) => sum + Math.max(file.size, 1), 0);
                    const updateBatchProgress = () => {
                        const weighted = files.reduce((sum, file, index) =>
                            sum + Math.max(file.size, 1) * fileProgress[index], 0);
                        const percentage = Math.min(99, Math.floor(weighted / totalBytes * 100));
                        const done = processedCount === files.length;
                        progressBar.style.width = (done ? 100 : percentage) + '%';
                        const savingCount = filePhases.filter(p => p === 'Menyimpan ke Google Drive').length;
                        progressText.textContent = done ?
                            'Selesai memproses ' + processedCount + '/' + files.length + ' foto' :
                            'Upload melalui hosting ' + percentage + '% · ' +
                            (savingCount ? savingCount + ' foto sedang disimpan ke Google Drive · ' : '') +
                            'Selesai ' + processedCount + '/' + files.length;
                        setUploadMessage(
                            'Berhasil ' + successful.length + ' · Gagal ' + failed.length +
                            ' · Aktif ' + runningCount + ' · ' +
                            (savingCount ? 'Menunggu konfirmasi Google Drive' : 'Mengirim foto ke hosting'),
                            failed.length ? 'error' : ''
                        );
                        uploadButton.innerHTML =
                            '<i class="fa fa-circle-o-notch fa-spin"></i><span>Upload ' +
                            processedCount + '/' + files.length + '</span>';
                    };

                    const uploadWorker = async () => {
                        while (nextFileIndex < files.length) {
                            const index = nextFileIndex++;
                            const file = files[index];
                            runningCount++;
                            progressText.textContent =
                                'Mengunggah ' + file.name + ' (' + formatBytes(file.size) + ')';
                            try {
                                const formData = new FormData();
                                if (activeFolderDbId) formData.append('folder_id', activeFolderDbId);
                                formData.append('photos[]', file, file.name);

                                filePhases[index] = 'Mengirim ke hosting';
                                updateBatchProgress();
                                const result = await uploadPhotoWithProgress(
                                    activeUploadUrl, formData,
                                    (loaded, total) => {
                                        fileProgress[index] = Math.max(fileProgress[index],
                                            0.9 * Math.min(1, loaded / Math.max(total, 1)));
                                        if (fileProgress[index] >= 0.9) {
                                            filePhases[index] = 'Menyimpan ke Google Drive';
                                        }
                                        updateBatchProgress();
                                    }
                                );

                                if (result && result.success === false) {
                                    throw new Error(result.message || 'Server menolak file.');
                                }
                                successful.push(file.name);
                                fileProgress[index] = 1;
                                filePhases[index] = 'Selesai';
                                // Simpan metadata dalam memori. Jangan refresh grid per foto.
                                for (const uploaded of (result.uploaded || [])) {
                                    if (uploaded.drive_id) {
                                        uploadedBatchItems.push({
                                            id: uploaded.drive_id,
                                            database_id: uploaded.id,
                                            name: uploaded.name || file.name,
                                            type: 'file',
                                            mime_type: file.type || 'image/jpeg',
                                            size: file.size,
                                            url: 'https://drive.google.com/file/d/' +
                                                encodeURIComponent(uploaded.drive_id) + '/view'
                                        });
                                    }
                                }
                            } catch (error) {
                                const message = error.name === 'AbortError' ?
                                    'Waktu respons server habis. Periksa foto sebelum mengulang.' :
                                    (error.message || 'Upload gagal.');
                                failed.push({ name: file.name, message });
                                fileProgress[index] = 1;
                                filePhases[index] = 'Gagal';
                                console.error('Upload gagal untuk ' + file.name, error);
                            } finally {
                                runningCount--;
                                processedCount++;
                                updateBatchProgress();
                            }
                        }
                    };

                    await Promise.all(Array.from(
                        { length: Math.min(MAX_PARALLEL_UPLOADS, files.length) },
                        () => uploadWorker()
                    ));

                    // Semua request sudah selesai: render preview grid HANYA SEKALI.
                    // Jangan mengubah folder lain bila pengguna sempat bernavigasi.
                    if (String(activeFolderDbId) === String(originalUploadFolderId)) {
                        const knownIds = new Set(driveCurrentItems.map(item => String(item.id)));
                        for (const item of uploadedBatchItems) {
                            if (!knownIds.has(String(item.id))) {
                                driveCurrentItems.unshift(item);
                                knownIds.add(String(item.id));
                            }
                        }
                        renderDriveItemsStable();
                    }
                    previewThumbs.replaceChildren();
                    showPreview('', '', 'Upload selesai');
                    // Preview grid sudah diperbarui. Tidak memuat ulang semua thumbnail
                    // dari server pada setiap foto, dan tidak mengulang request upload.

                    photoInput.value = '';
                    selectedFiles.textContent = 'Selesai. Berhasil ' + successful.length +
                        ' foto, gagal ' + failed.length + ' foto.';

                    if (failed.length) {
                        const details = failed.slice(0, 5).map(item =>
                            item.name + ': ' + item.message
                        ).join(' | ');
                        setUploadMessage(
                            'Upload selesai sebagian. Berhasil ' + successful.length +
                            ', gagal ' + failed.length + '. ' + details +
                            (failed.length > 5 ? ' | dan ' + (failed.length - 5) + ' lainnya.' : ''),
                            'error'
                        );
                        progressText.textContent =
                            'Selesai dengan ' + failed.length +
                            ' kegagalan. Periksa daftar foto sebelum mencoba ulang agar tidak duplikat.';
                    } else {
                        setUploadMessage('Semua ' + successful.length + ' foto berhasil diunggah.',
                            'success');
                        progressText.textContent = 'Semua foto berhasil diunggah.';
                    }

                    // Grid diperbarui sekali setelah semua foto diproses.
                    // Jika backend tidak mengirim drive_id, gunakan Refresh manual.


                } catch (error) {
                    console.error('Proses upload gagal:', error);
                    setUploadMessage(error.message || 'Proses upload gagal.', 'error');
                    previewThumbs.replaceChildren();
                    showPreview('', '', 'Upload tidak selesai');
                } finally {
                    syncUploadLayer();
                    uploadLayer.hidden = true;
                    uploadInProgress = false;
                    uploadButton.disabled = false;
                    photoInput.disabled = false;
                    uploadButton.innerHTML =
                        '<i class="fa fa-cloud-upload"></i><span>Upload Foto</span>';
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



            // Auto-open drawer hanya jika kata kunci cocok dengan artikel pada PO di halaman ini.
            // Query server harus mengembalikan PO yang relevan; pencarian nama file memerlukan metadata backend.
            async function autoOpenMatchingArticle() {
                if (autoSearchDone || !normalizeArchiveSearch(initialSearchKeyword)) return;
                autoSearchDone = true;
                const keyword = normalizeArchiveSearch(initialSearchKeyword);
                const buttons = Array.from(document.querySelectorAll('.js-open-drawer'));
                for (const button of buttons) {
                    try {
                        const result = await fetchJson(button.dataset.itemsUrl);
                        const matched = (result.items || []).find(item => {
                            const searchable = normalizeArchiveSearch([
                                item.article_code, item.article_nr_, item.nw_code,
                                item.description, item.nw_description,
                                item.nama_barang, item.item_name, item.name
                            ].filter(v => v != null).join(' '));
                            return searchable.includes(keyword);
                        });
                        if (matched) {
                            const originalIndex = (result.items || []).indexOf(matched);
                            const key = String(matched.id ?? matched.article_code ?? originalIndex)
                                .replace(/[^a-zA-Z0-9_-]/g, '_');
                            await openDrawer(button, { articleKeyword: initialSearchKeyword, highlightKey: key });
                            return;
                        }
                    } catch (error) {
                        console.warn('Tidak dapat memeriksa artikel PO:', error);
                    }
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', autoOpenMatchingArticle, { once: true });
            } else {
                autoOpenMatchingArticle();
            }

            document.addEventListener('click', function(event) {

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

            // Backdrop sengaja tidak menutup modal; penutupan hanya melalui tombol X/Tutup.



            backToItems.addEventListener('click', function() {

                if (uploadInProgress) return;

                closeArchive();

            });



            document.addEventListener('keydown', function(event) {

                if (

                    event.key === 'Escape' &&

                    drawer.classList.contains('open')

                ) {

                    closeDrawer();

                }

            });

        })();
    </script>

    {{-- Sinkronkan label ringkas tanpa mengubah handler Google Drive yang sudah ada. --}}
    <script>
        (function() {
            const sourcePo = document.getElementById('rndDrawerPo');
            const sourceBuyer = document.getElementById('rndDrawerBuyer');
            const compactPo = document.getElementById('rndCompactPo');
            const compactBuyer = document.getElementById('rndCompactBuyer');
            if (!sourcePo || !sourceBuyer || !compactPo || !compactBuyer) return;

            function syncPoInfo() {
                compactPo.textContent = sourcePo.textContent.trim() || '-';
                compactBuyer.textContent = sourceBuyer.textContent.trim() || '-';
            }
            const observer = new MutationObserver(syncPoInfo);
            observer.observe(sourcePo, {
                childList: true,
                characterData: true,
                subtree: true
            });
            observer.observe(sourceBuyer, {
                childList: true,
                characterData: true,
                subtree: true
            });
            syncPoInfo();
        })();
    </script>

    <script>
        /* Mobile preview opens only for actual photo previews; existing preview logic unchanged. */
        (function() {
            const pane = document.querySelector('#rndArchivePanel .rnd-preview-pane');
            const stage = document.getElementById('rndPreviewStage');
            if (!pane || !stage) return;
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'rnd-mobile-preview-close';
            close.setAttribute('aria-label', 'Tutup preview');
            close.innerHTML = '<i class="fa fa-times"></i>';
            pane.appendChild(close);
            close.addEventListener('click', () => pane.classList.remove('rnd-mobile-preview-open'));
            let hadPhoto = false;
            const watch = new MutationObserver(() => {
                const hasPhoto = !!stage.querySelector('img,video,iframe');
                if (hasPhoto && !hadPhoto && window.matchMedia('(max-width:700px)').matches) {
                    pane.classList.add('rnd-mobile-preview-open');
                }
                hadPhoto = hasPhoto;
            });
            watch.observe(stage, {
                childList: true,
                subtree: true
            });
            document.getElementById('rndBackToItems')?.addEventListener('click', () => pane.classList.remove(
                'rnd-mobile-preview-open'));
            document.getElementById('rndCloseDrawer')?.addEventListener('click', () => pane.classList.remove(
                'rnd-mobile-preview-open'));
        })();
    </script>


    <style>
        .rnd-explorer-confirm[hidden],
        .rnd-explorer-toast[hidden] {
            display: none !important
        }

        .rnd-explorer-confirm {
            position: fixed;
            inset: 0;
            z-index: 11050;
            background: rgba(15, 23, 42, .38);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(3px)
        }

        .rnd-explorer-dialog {
            width: min(360px, 100%);
            background: white;
            border: 1px solid #dce5f2;
            border-radius: 14px;
            padding: 22px;
            text-align: center;
            box-shadow: 0 16px 50px #0f172a30;
            font: 12px Arial, sans-serif;
            color: #17243a
        }

        .rnd-explorer-dialog>i {
            font-size: 32px;
            color: #f2b400
        }

        .rnd-explorer-dialog h4 {
            font-size: 15px;
            margin: 12px 0
        }

        .rnd-explorer-dialog p {
            line-height: 1.6;
            overflow-wrap: anywhere
        }

        .rnd-explorer-dialog>div {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 20px
        }

        .rnd-explorer-dialog button {
            border: 1px solid #dce5f2;
            border-radius: 7px;
            padding: 9px 17px;
            background: white;
            cursor: pointer
        }

        .rnd-explorer-dialog button.primary {
            background: #1769e8;
            color: white;
            border-color: #1769e8
        }

        .rnd-explorer-toast {
            position: fixed;
            bottom: 26px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 11060;
            display: flex;
            align-items: center;
            gap: 14px;
            max-width: calc(100vw - 24px);
            padding: 12px 16px;
            border-radius: 9px;
            background: #17243a;
            color: #fff;
            box-shadow: 0 10px 30px #0f172a44;
            font: 12px Arial, sans-serif
        }

        .rnd-explorer-toast button {
            border: 0;
            background: transparent;
            color: #93c5fd;
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap
        }

        .rnd-explorer-context {
            position: fixed;
            z-index: 11070;
            display: flex;
            flex-direction: column;
            min-width: 180px;
            background: white;
            border: 1px solid #dce5f2;
            border-radius: 9px;
            padding: 5px;
            box-shadow: 0 8px 30px #0f172a30
        }

        .rnd-explorer-context button {
            background: white;
            border: 0;
            text-align: left;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px
        }

        .rnd-explorer-context button:hover {
            background: #eff6ff
        }

        .rnd-explorer-context button:disabled {
            opacity: .45
        }

        .rnd-drop-target {
            outline: 3px dashed #1769e8 !important;
            background: #eff6ff !important
        }
    </style>


    {{-- Fullscreen photo viewer: double-click photo, wheel zoom, pointer pan --}}
    <style>
        #rndFullscreenViewer[hidden] {
            display: none !important
        }

        #rndFullscreenViewer {
            position: fixed;
            inset: 0;
            z-index: 2147483000;
            background: rgba(5, 12, 25, .97);
            color: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            touch-action: none;
            font-family: Arial, sans-serif
        }

        .rnd-fv-toolbar {
            height: 54px;
            flex: 0 0 54px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: rgba(18, 28, 47, .9);
            z-index: 2
        }

        .rnd-fv-title {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700
        }

        .rnd-fv-toolbar button {
            border: 1px solid #536178;
            border-radius: 7px;
            background: #24334b;
            color: white;
            min-width: 35px;
            height: 34px;
            padding: 0 10px;
            cursor: pointer;
            font-size: 13px
        }

        .rnd-fv-toolbar button:hover {
            background: #36547c
        }

        .rnd-fv-canvas {
            position: relative;
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
            touch-action: none;
            user-select: none
        }

        .rnd-fv-canvas.is-panning {
            cursor: grabbing
        }

        .rnd-fv-canvas img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            pointer-events: none;
            user-select: none;
            transform-origin: center center;
            will-change: transform
        }

        .rnd-fv-hint {
            position: absolute;
            bottom: 18px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, .76);
            border-radius: 25px;
            padding: 9px 15px;
            color: #dbe5f2;
            font-size: 11px;
            white-space: nowrap;
            pointer-events: none
        }

        .rnd-fv-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #fecaca;
            max-width: 90%
        }

        @media(max-width:600px) {
            .rnd-fv-toolbar {
                padding: 7px;
                gap: 5px
            }

            .rnd-fv-toolbar button {
                padding: 0 7px;
                min-width: 30px
            }

            .rnd-fv-hint {
                font-size: 9px;
                bottom: 10px
            }
        }
    
        /* RND Drive list: truncate long names and place list cards side-by-side */
        #rndDriveItems.rnd-list-mode {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 330px), 1fr)) !important;
            align-content: start !important;
            align-items: start !important;
            gap: 8px 12px !important;
            padding: 8px 10px !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item {
            display: grid !important;
            grid-template-columns: 54px minmax(0, 1fr) 30px !important;
            align-items: center !important;
            min-width: 0 !important;
            min-height: 62px !important;
            gap: 9px !important;
            padding: 7px 8px !important;
            border: 1px solid #e6edf6 !important;
            border-radius: 9px !important;
            overflow: hidden !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-preview {
            grid-column: 1 !important;
            grid-row: 1 !important;
            width: 54px !important;
            height: 48px !important;
            min-width: 0 !important;
            border-radius: 6px !important;
            overflow: hidden !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-info {
            grid-column: 2 !important;
            grid-row: 1 !important;
            min-width: 0 !important;
            width: 100% !important;
            padding: 3px 0 !important;
            overflow: hidden !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-name {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            overflow-wrap: normal !important;
            word-break: normal !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-meta {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-card-menu {
            grid-column: 3 !important;
            grid-row: 1 !important;
            justify-self: end !important;
            position: relative !important;
            top: auto !important;
            right: auto !important;
            width: 27px !important;
            height: 27px !important;
        }

        #rndDriveItems.rnd-list-mode .rnd-drive-item-actions-modern {
            top: 45px !important;
            right: 4px !important;
        }

        @media (max-width: 520px) {
            #rndDriveItems.rnd-list-mode {
                grid-template-columns: minmax(0, 1fr) !important;
            }
        }

        /* Make the article-directory binding step unmistakable. */
        #rndArchivePanel { position: relative !important; }
        #rndArchiveBindingOverlay[hidden] { display: none !important; }
        .rnd-binding-overlay {
            position: absolute; inset: 0; z-index: 75;
            display: flex; align-items: center; justify-content: center;
            padding: 22px; background: rgba(242, 247, 255, .88);
            backdrop-filter: blur(3px);
        }
        .rnd-binding-card {
            width: min(440px, 100%); text-align: center;
            background: #fff; border: 1px solid #d9e5f6;
            border-radius: 18px; padding: 32px 24px;
            box-shadow: 0 18px 45px rgba(25, 56, 100, .14);
        }
        .rnd-binding-spinner {
            width: 48px; height: 48px; margin: 0 auto 18px;
            border: 5px solid #dce9ff; border-top-color: #1462e8;
            border-radius: 50%; animation: rndBindingSpin .85s linear infinite;
        }
        @keyframes rndBindingSpin { to { transform: rotate(360deg); } }
        .rnd-binding-card strong { display: block; font-size: 17px; color: #12366d; }
        .rnd-binding-card p { margin: 10px 0 15px; color: #4d6585; font-size: 13px; line-height: 1.6; }
        .rnd-binding-card span { color: #617899; font-size: 12px; }
        @media (prefers-reduced-motion: reduce) { .rnd-binding-spinner { animation-duration: 2s; } }

</style>
    <div id="rndFullscreenViewer" hidden role="dialog" aria-modal="true" aria-label="Foto layar penuh">
        <div class="rnd-fv-toolbar">
            <span id="rndFvTitle" class="rnd-fv-title">Foto</span>
            <button type="button" id="rndFvOut" title="Perkecil">−</button>
            <span id="rndFvPercent" style="font-size:11px;min-width:40px;text-align:center">100%</span>
            <button type="button" id="rndFvIn" title="Perbesar">+</button>
            <button type="button" id="rndFvReset" title="Reset zoom">Reset</button>
            <button type="button" id="rndFvClose" title="Tutup (Esc)" aria-label="Tutup">✕</button>
        </div>
        <div id="rndFvCanvas" class="rnd-fv-canvas">
            <img id="rndFvImage" alt="Foto fullscreen" draggable="false">
            <div id="rndFvError" class="rnd-fv-error" hidden>Foto tidak dapat ditampilkan. Pastikan gambar dapat diakses
                dari browser.</div>
            <div class="rnd-fv-hint">Scroll untuk zoom · Drag untuk geser · Esc untuk tutup</div>
        </div>
    </div>
    <script>
        (function() {
            'use strict';
            const viewer = document.getElementById('rndFullscreenViewer');
            const canvas = document.getElementById('rndFvCanvas');
            const img = document.getElementById('rndFvImage');
            const title = document.getElementById('rndFvTitle');
            const percent = document.getElementById('rndFvPercent');
            const error = document.getElementById('rndFvError');
            const grid = document.getElementById('rndDriveItems');
            const stage = document.getElementById('rndPreviewStage');
            let zoom = 1,
                dx = 0,
                dy = 0,
                dragging = false,
                startX = 0,
                startY = 0,
                oldOverflow = '',
                lastFocus = null;
            const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

            function draw() {
                img.style.transform = `translate(${dx}px,${dy}px) scale(${zoom})`;
                percent.textContent = Math.round(zoom * 100) + '%';
            }

            function setZoom(next, cx = canvas.clientWidth / 2, cy = canvas.clientHeight / 2) {
                const z = clamp(next, 0.25, 12),
                    ratio = z / zoom;
                dx = (dx - (cx - canvas.clientWidth / 2)) * ratio + (cx - canvas.clientWidth / 2);
                dy = (dy - (cy - canvas.clientHeight / 2)) * ratio + (cy - canvas.clientHeight / 2);
                zoom = z;
                draw();
            }

            function open(src, name) {
                if (!src) return;
                lastFocus = document.activeElement;
                oldOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                zoom = 1;
                dx = 0;
                dy = 0;
                dragging = false;
                error.hidden = true;
                img.style.display = 'block';
                img.removeAttribute('src');
                title.textContent = name || 'Foto Sampel';
                viewer.hidden = false;
                img.src = src;
                draw();
                document.getElementById('rndFvClose').focus();
            }

            function close() {
                if (viewer.hidden) return;
                viewer.hidden = true;
                img.removeAttribute('src');
                document.body.style.overflow = oldOverflow;
                dragging = false;
                canvas.classList.remove('is-panning');
                lastFocus?.focus?.();
            }
            img.addEventListener('error', () => {
                error.hidden = false;
                img.style.display = 'none';
            });
            img.addEventListener('load', () => {
                error.hidden = true;
                img.style.display = 'block';
            });
            document.getElementById('rndFvClose').addEventListener('click', close);
            document.getElementById('rndFvIn').addEventListener('click', () => setZoom(zoom * 1.25));
            document.getElementById('rndFvOut').addEventListener('click', () => setZoom(zoom / 1.25));
            document.getElementById('rndFvReset').addEventListener('click', () => {
                zoom = 1;
                dx = dy = 0;
                draw();
            });
            canvas.addEventListener('wheel', e => {
                if (viewer.hidden) return;
                e.preventDefault();
                const r = canvas.getBoundingClientRect();
                setZoom(zoom * (e.deltaY < 0 ? 1.13 : 1 / 1.13), e.clientX - r.left, e.clientY - r.top);
            }, {
                passive: false
            });
            canvas.addEventListener('pointerdown', e => {
                if (e.button !== 0) return;
                dragging = true;
                startX = e.clientX - dx;
                startY = e.clientY - dy;
                canvas.setPointerCapture(e.pointerId);
                canvas.classList.add('is-panning');
            });
            canvas.addEventListener('pointermove', e => {
                if (!dragging) return;
                dx = e.clientX - startX;
                dy = e.clientY - startY;
                draw();
            });

            function endPan() {
                dragging = false;
                canvas.classList.remove('is-panning');
            }
            canvas.addEventListener('pointerup', endPan);
            canvas.addEventListener('pointercancel', endPan);
            document.addEventListener('keydown', e => {
                if (viewer.hidden) return;
                if (e.key === 'Escape') {
                    e.preventDefault();
                    close();
                } else if (e.key === '+' || e.key === '=') {
                    e.preventDefault();
                    setZoom(zoom * 1.25);
                } else if (e.key === '-') {
                    e.preventDefault();
                    setZoom(zoom / 1.25);
                } else if (e.key === '0') {
                    e.preventDefault();
                    zoom = 1;
                    dx = dy = 0;
                    draw();
                }
            });
            // Capture double click on file tiles without changing the existing selection/drag handlers.
            grid?.addEventListener('dblclick', e => {
                if (e.target.closest('button,.rnd-drive-card-menu,.rnd-drive-item-actions')) return;
                const card = e.target.closest('.rnd-drive-item');
                if (!card || card.dataset.type === 'folder') return;
                const thumbnail = card.querySelector('.rnd-drive-preview img');
                const selected = stage?.querySelector('img');
                const src = (selected && selected.src && selected.src !== location.href ? selected.src :
                    null) || thumbnail?.src;
                const name = card.querySelector('.rnd-drive-item-name')?.textContent || 'Foto Sampel';
                if (src) {
                    e.preventDefault();
                    e.stopPropagation();
                    open(src, name);
                }
            }, true);
            // Also support double-clicking the large preview or locally selected photo.
            stage?.addEventListener('dblclick', e => {
                const selected = stage.querySelector('img');
                if (selected?.src) {
                    e.preventDefault();
                    open(selected.src, document.getElementById('rndPreviewCaption')?.textContent ||
                        'Foto Sampel');
                }
            });
        })();
    </script>
@endsection
