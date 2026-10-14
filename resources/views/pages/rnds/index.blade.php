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



            <form method="GET" action="{{ route('rnd.sample-pictures') }}" class="rnd-search">

                <i class="fa fa-search"></i>



                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nomor PO atau buyer..."
                    autocomplete="off">



                @if (!empty($search))
                    <a href="{{ route('rnd.sample-pictures') }}" class="rnd-search-clear" title="Hapus pencarian">

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

                <h3>Kelola Foto Sampel</h3>

                <p id="rndDrawerSubtitle">Daftar artikel dari Purchase Order.</p>

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



                <a id="rndOpenDriveFolder" href="#" target="_blank" rel="noopener noreferrer" class="rnd-drive-link"
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



                detailBody.innerHTML = items.map(function(item, index) {

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
                    driveCurrentItems = data.items || [];
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

            function renderDriveItems() {
                driveItems.replaceChildren();
                driveItems.classList.add('rnd-drive-items-modern');
                if (!driveCurrentItems.length) {
                    const empty = document.createElement('div');
                    empty.className = 'rnd-drive-empty rnd-drive-empty-modern';
                    empty.innerHTML =
                        '<div class="rnd-empty-icon"><i class="fa fa-folder-open-o"></i></div><strong>Folder ini masih kosong</strong><span>Buat folder baru atau upload foto untuk mulai mengisi arsip.</span>';
                    driveItems.appendChild(empty);
                    return;
                }

                driveCurrentItems.forEach(item => {
                    const card = document.createElement('article');
                    card.className = 'rnd-drive-item rnd-drive-item-modern';
                    card.dataset.type = item.type;

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
                    meta.textContent = item.type === 'folder' ? 'Folder' : (driveSize(item.size) || item
                        .mime_type || 'Foto');
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
                    } else if (item.url) {
                        card.addEventListener('click', () => {
                            const previewUrl = item.thumbnail_url || item.thumbnailLink || item
                                .thumbnail ||
                                (item.id ? ('https://drive.google.com/thumbnail?id=' +
                                    encodeURIComponent(item.id) + '&sz=w1600') : item.url);
                            showPreview(previewUrl, item.name, 'Foto tersimpan');
                        });
                        card.title = 'Klik dua kali untuk membuka foto';
                        card.addEventListener('dblclick', () => window.open(item.url, '_blank',
                            'noopener,noreferrer'));
                        preview.style.cursor = 'pointer';
                        preview.addEventListener('click', () => window.open(item.url, '_blank',
                            'noopener,noreferrer'));
                    }

                    const actions = document.createElement('div');
                    actions.className = 'rnd-drive-item-actions rnd-drive-item-actions-modern';
                    if (item.type !== 'folder' && item.url) {
                        const open = document.createElement('button');
                        open.type = 'button';
                        open.innerHTML = '<i class="fa fa-external-link"></i> Buka';
                        open.addEventListener('click', () => window.open(item.url, '_blank',
                            'noopener,noreferrer'));
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
                            await fetchJson(driveEndpoint('/items/' + encodeURIComponent(item
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
                            await loadDriveFolder(activeFolderDbId);
                            await refreshItems();
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
                            await loadDriveFolder(activeFolderDbId);
                            await refreshItems();
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
                try {
                    await fetchJson(driveEndpoint('/folders'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name.trim(),
                            parent_folder_id: activeFolderDbId
                        })
                    });
                    await loadDriveFolder(activeFolderDbId);
                } catch (e) {
                    alert(e.message || 'Gagal membuat folder.');
                }
            });

            async function openArticleArchive(button) {

                if (uploadInProgress) return;



                activeArchiveUrl = button.dataset.archiveUrl;

                activeUploadUrl = button.dataset.uploadUrl;
                activeRootFolderDbId = null;
                activeFolderDbId = null;
                driveBreadcrumbs = [];



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

                        '<i class="fa fa-check-circle"></i> Folder artikel siap di Google Drive';



                    if (result.folder && result.folder.url) {

                        driveLink.href = result.folder.url;

                        driveLink.hidden = false;

                    }



                    renderArchiveFiles(result.files || []);
                    if (result.folder && result.folder.database_id) {
                        activeRootFolderDbId = result.folder.database_id;
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
                previewLocalFiles(files);

                setUploadMessage('');

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

                const csrf = document.querySelector('meta[name="csrf-token"]');
                const successful = [];
                const failed = [];

                const formatBytes = bytes => {
                    if (!bytes) return '0 KB';
                    return bytes >= 1024 * 1024
                        ? (bytes / (1024 * 1024)).toFixed(1) + ' MB'
                        : Math.max(1, Math.round(bytes / 1024)) + ' KB';
                };

                // Upload satu per satu agar server/Google Drive tidak menerima request besar
                // sekaligus dan progress setiap file bisa terlihat.
                try {
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const current = i + 1;
                        const percentBefore = Math.round((i / files.length) * 100);

                        uploadButton.innerHTML =
                            '<i class="fa fa-circle-o-notch fa-spin"></i><span>Upload ' +
                            current + '/' + files.length + '</span>';
                        progressBar.style.width = percentBefore + '%';
                        progressText.textContent =
                            'Mengunggah ' + current + '/' + files.length +
                            ': ' + file.name + ' (' + formatBytes(file.size) + ')';
                        setUploadMessage(
                            'Berhasil ' + successful.length + '/' + files.length +
                            ' · Sedang diproses ' + current + '/' + files.length,
                            ''
                        );

                        const formData = new FormData();
                        if (activeFolderDbId) formData.append('folder_id', activeFolderDbId);
                        formData.append('photos[]', file, file.name);

                        try {
                            // Timeout per file menghindari UI menunggu tanpa batas.
                            const controller = new AbortController();
                            const timeout = setTimeout(() => controller.abort(), 180000);
                            let result;

                            try {
                                result = await fetchJson(activeUploadUrl, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
                                    body: formData,
                                    signal: controller.signal
                                });
                            } finally {
                                clearTimeout(timeout);
                            }

                            if (result && result.success === false) {
                                throw new Error(result.message || 'Server menolak file.');
                            }
                            successful.push(file.name);
                        } catch (error) {
                            const message = error.name === 'AbortError'
                                ? 'Waktu upload habis (3 menit).'
                                : (error.message || 'Upload gagal.');
                            failed.push({ name: file.name, message: message });
                            console.error('Upload gagal untuk ' + file.name, error);
                        }

                        const completed = i + 1;
                        progressBar.style.width = Math.round((completed / files.length) * 100) + '%';
                        progressText.textContent =
                            'Selesai diproses ' + completed + '/' + files.length +
                            ' · Berhasil ' + successful.length +
                            ' · Gagal ' + failed.length;
                        setUploadMessage(
                            'Progres ' + completed + '/' + files.length +
                            ' foto · Berhasil ' + successful.length +
                            ' · Gagal ' + failed.length,
                            failed.length ? 'error' : ''
                        );
                    }

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
                            'Selesai dengan ' + failed.length + ' kegagalan. Pilih ulang hanya file yang gagal.';
                    } else {
                        setUploadMessage('Semua ' + successful.length + ' foto berhasil diunggah.', 'success');
                        progressText.textContent = 'Semua foto berhasil diunggah.';
                    }

                    // Refresh sesudah semua file diproses, bukan sesudah tiap file.
                    const refreshTasks = [];
                    if (activeFolderDbId) refreshTasks.push(loadDriveFolder(activeFolderDbId));
                    if (activeItemsUrl) refreshTasks.push(refreshItems());
                    await Promise.all(refreshTasks);

                } catch (error) {
                    console.error('Proses upload gagal:', error);
                    setUploadMessage(error.message || 'Proses upload gagal.', 'error');
                } finally {
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
@endsection
