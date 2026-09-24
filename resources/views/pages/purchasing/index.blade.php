@extends('master.master')

@section('content')
    <style>
        .signature-section {
            margin-top: 30px;
            overflow-x: auto;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            text-align: center;
            font-size: 11px;
        }

        .signature-table th {
            border: 1px solid #333;
            padding: 7px;
            background: #293B4D;
            color: #111;
            font-weight: 600;
             color:white;
        }

        .signature-table td {
           
            border: 1px solid #333;
            padding: 6px;
            height: 45px;
        }

        .signature-role td {
            font-size: 10px;
            color: #555;
            height: 30px;
        }

        .signature-input-row td {
            height: 60px;
            vertical-align: bottom;
        }

        .signature-input,
        .signature-select {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            text-align: center;
            font-size: 11px;
        }

        .signature-select {
            cursor: pointer;
        }

        .signature-select:focus {
            background: #eef5ff;
        }

        .purchasing-page {
            padding: 20px;
            background: #f5f7fa;
            min-height: calc(100vh - 70px);
        }

        .purchasing-card {
            background: #fff;
            border: 1px solid #dce2e8;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .page-header {
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #263445;
        }

        .page-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #7b8794;
        }

        .search-section {
            padding: 20px 22px;
            background: #fafbfc;
            border-bottom: 1px solid #e5e7eb;
        }

        .search-title,
        .table-title {
            font-size: 14px;
            font-weight: 700;
            color: #263445;
            margin-bottom: 12px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            height: 42px;
            padding-left: 42px;
            padding-right: 45px;
            border: 1px solid #ccd4dd;
            border-radius: 7px;
            font-size: 13px;
            width: 100%;
            background: #fff;
        }

        .search-box input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .10);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 12px;
            color: #7b8794;
        }

        .search-button {
            position: absolute;
            right: 5px;
            top: 5px;
            height: 32px;
            width: 32px;
            border: 0;
            border-radius: 5px;
            background: #1f4e78;
            color: #fff;
        }

        .search-result {
            margin-top: 8px;
            display: none;
            background: #fff;
            border: 1px solid #dce2e8;
            border-radius: 7px;
            max-height: 260px;
            overflow-y: auto;
        }

        .search-result.show {
            display: block;
        }

        .material-result {
            padding: 11px 14px;
            border-bottom: 1px solid #edf0f3;
            cursor: pointer;
            transition: .15s;
        }

        .material-result:last-child {
            border-bottom: 0;
        }

        .material-result:hover {
            background: #f1f6fb;
        }

        .material-code {
            font-size: 12px;
            font-weight: 700;
            color: #263445;
        }

        .material-name {
            font-size: 12px;
            color: #5f6b76;
            margin-top: 2px;
        }

        .material-stock {
            font-size: 11px;
            margin-top: 5px;
            color: #6b7280;
        }

        .new-material-result {
            padding: 13px 14px;
            border-bottom: 0;
            cursor: pointer;
            background: #fffaf0;
        }

        .new-material-result:hover {
            background: #fff3d6;
        }

        .new-material-title {
            font-size: 12px;
            font-weight: 700;
            color: #9a6700;
        }

        .new-material-desc {
            margin-top: 4px;
            font-size: 11px;
            color: #7b8794;
        }

        .new-material-badge {
            display: inline-block;
            margin-top: 7px;
            padding: 3px 7px;
            border-radius: 4px;
            background: #fff0c2;
            color: #8a5a00;
            font-size: 10px;
            font-weight: 700;
        }

        .new-material-form {
            display: none;
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #f0d58a;
            border-radius: 8px;
            background: #fffdf7;
        }

        .new-material-form.show {
            display: block;
        }

        .new-material-form-title {
            font-size: 13px;
            font-weight: 700;
            color: #6b4f00;
            margin-bottom: 12px;
        }

        .new-material-grid {
            display: grid;
            grid-template-columns: 1fr 180px 180px;
            gap: 10px;
            align-items: end;
        }

        .new-material-actions {
            margin-top: 12px;
            display: flex;
            gap: 8px;
        }

        .btn-new-material {
            height: 38px;
            padding: 0 15px;
            border: 0;
            border-radius: 6px;
            background: #d97706;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-new-material:hover {
            background: #b45309;
        }

        .btn-cancel-new-material {
            height: 38px;
            padding: 0 15px;
            border: 1px solid #ccd4dd;
            border-radius: 6px;
            background: #fff;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
        }

        .new-item-row {
            background: #fffaf0;
        }

        .new-item-label {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 6px;
            border-radius: 4px;
            background: #fff0c2;
            color: #8a5a00;
            font-size: 9px;
            font-weight: 700;
        }

        .stock-danger {
            color: #dc3545 !important;
        }

        .stock-warning {
            color: #d97706 !important;
        }

        .stock-success {
            color: #198754 !important;
        }

        .selected-material {
            display: none;
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #cfd8e3;
            border-radius: 8px;
            background: #fff;
        }

        .selected-material.show {
            display: block;
        }

        .selected-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .selected-name {
            font-size: 14px;
            font-weight: 700;
            color: #263445;
        }

        .selected-code {
            font-size: 11px;
            color: #7b8794;
            margin-top: 3px;
        }

        .stock-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .stock-box {
            padding: 11px 13px;
            border: 1px solid #e0e5eb;
            border-radius: 6px;
            background: #fafbfc;
        }

        .stock-label {
            font-size: 10px;
            color: #7b8794;
            margin-bottom: 4px;
        }

        .stock-value {
            font-size: 16px;
            font-weight: 700;
            color: #263445;
        }

        .request-input {
            margin-top: 15px;
            display: grid;
            grid-template-columns: 180px 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .form-label-custom {
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 5px;
            display: block;
        }

        .form-control-custom,
        .form-select-custom {
            height: 38px;
            border: 1px solid #ccd4dd;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 12px;
            width: 100%;
            background: #fff;
        }

        .btn-add-material {
            height: 38px;
            padding: 0 16px;
            border: 0;
            border-radius: 6px;
            background: #198754;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-add-material:hover {
            background: #157347;
        }

        .request-info {
            padding: 15px 22px;
            border-bottom: 1px solid #e5e7eb;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        .info-box label {
            display: block;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .info-box input,
        .info-box select {
            width: 100%;
            height: 34px;
            border: 1px solid #ccd4dd;
            border-radius: 5px;
            padding: 5px 9px;
            font-size: 11px;
        }

        .table-section {
            padding: 20px 22px;
        }

        .request-table-wrapper {
            overflow-x: auto;
            border: 1px solid #cfd6dd;
        }

        .request-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1150px;
            font-size: 11px;
        }

        .request-table th {
            background: #293b4d;
            color: #fff;
            padding: 9px 7px;
            border: 1px solid #1f2e3b;
            font-size: 10px;
            font-weight: 600;
            white-space: nowrap;
        }

        .request-table td {
            border: 1px solid #cfd6dd;
            padding: 6px 7px;
            height: 38px;
            vertical-align: middle;
        }

        .request-table tbody tr:hover {
            background: #f8fafc;
        }

        .request-table input,
        .request-table select {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            font-size: 11px;
            min-width: 70px;
        }

        .request-table input:focus,
        .request-table select:focus {
            background: #eef5ff;
        }

        .no-column {
            width: 40px;
            text-align: center;
        }

        .material-column {
            min-width: 200px;
        }

        .supplier-column {
            min-width: 150px;
        }

        .payment-column {
            min-width: 120px;
        }

        .description-column {
            min-width: 180px;
        }

        .warehouse-column {
            min-width: 140px;
        }

        .qty-column {
            width: 70px;
        }

        .unit-column {
            width: 70px;
        }

        .price-column {
            width: 120px;
        }

        .total-column {
            width: 130px;
        }

        .status-column {
            width: 80px;
        }

        .remove-row {
            color: #dc3545;
            cursor: pointer;
            border: 0;
            background: transparent;
            font-size: 13px;
        }

        .empty-request {
            text-align: center;
            padding: 35px 15px !important;
            color: #9ca3af;
        }

        .empty-request i {
            font-size: 30px;
            display: block;
            margin-bottom: 8px;
            color: #cbd5e1;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        .btn-add-row {
            border: 1px solid #198754;
            color: #198754;
            background: #fff;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
        }

        .btn-add-row:hover {
            background: #198754;
            color: #fff;
        }

        .draft-cache-status {
            margin-top: 6px;
            font-size: 10px;
            color: #6b7280;
            text-align: right;
            min-height: 15px;
        }

        .draft-cache-status.saved {
            color: #198754;
        }

        .draft-cache-status.cleared {
            color: #dc3545;
        }

        .stock-rule {
            margin-top: 12px;
            padding: 10px 12px;
            border: 1px solid #f6d98b;
            background: #fff8e5;
            border-radius: 6px;
            font-size: 11px;
            color: #6b4f00;
        }

        @media(max-width: 768px) {

            .request-info,
            .stock-info,
            .request-input {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================================
           TAB VIEW PURCHASING
           ============================================================ */
        .purchasing-tabs {
            display: flex;
            gap: 6px;
            padding: 12px 22px 0;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }

        .purchasing-tab {
            border: 1px solid #dce2e8;
            border-bottom: 0;
            background: #f8fafc;
            color: #5f6b76;
            padding: 9px 16px;
            border-radius: 7px 7px 0 0;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .purchasing-tab.active {
            background: #293b4d;
            color: #fff;
            border-color: #293b4d;
        }

        .purchasing-tab-content {
            display: none;
        }

        .purchasing-tab-content.active {
            display: block !important;
        }

        .purchasing-tab-content.active {
            display: block !important;
        }

        .submission-list-section {
            padding: 20px 22px;
        }

        .submission-list-toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .submission-search {
            height: 36px;
            width: 280px;
            border: 1px solid #ccd4dd;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 12px;
        }

        .submission-table-wrapper {
            overflow-x: auto;
            border: 1px solid #cfd6dd;
            border-radius: 6px;
        }

        .submission-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
            font-size: 11px;
        }

        .submission-table th {
            background: #293b4d;
            color: #fff;
            padding: 9px 7px;
            border: 1px solid #1f2e3b;
            font-size: 10px;
            white-space: nowrap;
        }

        .submission-table td {
            border: 1px solid #cfd6dd;
            padding: 8px 7px;
            vertical-align: middle;
        }

        .submission-table tbody tr:hover {
            background: #f8fafc;
        }

        .submission-empty {
            text-align: center;
            padding: 35px !important;
            color: #9ca3af;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }

        .status-pending {
            background: #fff0c2;
            color: #8a5a00;
        }

        .status-approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-rejected {
            background: #f8d7da;
            color: #842029;
        }

        .btn-open-purchasing-detail {
            border: 1px solid #1f4e78;
            background: #fff;
            color: #1f4e78;
            padding: 4px 9px;
            border-radius: 5px;
            font-size: 10px;
            cursor: pointer;
        }

        .btn-open-purchasing-detail:hover {
            background: #1f4e78;
            color: #fff;
        }

        .draft-id-label {
            font-size: 10px;
            color: #7b8794;
            margin-left: 6px;
        }

        .purchasing-readonly-banner {
            margin: 12px 22px 0;
            padding: 10px 12px;
            border: 1px solid #f1c40f;
            background: #fff8db;
            color: #7a5b00;
            border-radius: 6px;
            font-size: 11px;
        }

        .btn-open-purchasing-detail:disabled {
            opacity: .5;
            cursor: not-allowed;
            background: #f3f4f6;
            color: #9ca3af;
            border-color: #d1d5db;
        }


        /* ============================================================
           DETAIL TAB DINAMIS
           ============================================================ */
        .purchasing-detail-tab {
            position: relative;
            padding-right: 30px;
        }

        .purchasing-detail-close {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: inherit;
            opacity: .75;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
        }

        .purchasing-detail-close:hover {
            opacity: 1;
        }

        .purchasing-detail-frame-wrap {
            width: 100%;
            min-height: calc(100vh - 150px);
            background: #f5f7fa;
        }

        .purchasing-detail-frame {
            display: block;
            width: 100%;
            min-height: calc(100vh - 150px);
            height: calc(100vh - 150px);
            border: 0;
            background: #f5f7fa;
        }
    </style>
      @section('btn')
            <div>
                <h6 class="mb-0 fw-bold">Pengajuan purchasing</h6>
                <small class="text-muted">Bahan baku's, bahan penolong, dlll</small>
            </div>
        @endsection
    <div class="purchasing-page">

        <div class="purchasing-card">

            <div class="purchasing-tabs">
                <button type="button" class="purchasing-tab" data-tab="draftFormTab"
                    onclick="showPurchasingTab('draftFormTab', this, true)">
                    <i class="fa fa-edit"></i> Draft Form
                </button>

                <button type="button" class="purchasing-tab" data-tab="listPengajuanTab"
                    onclick="showPurchasingTab('listPengajuanTab', this)">
                    <i class="fa fa-list"></i> List Pengajuan
                </button>
            </div>

            <div id="draftFormTab" class="purchasing-tab-content">
                @include('pages.purchasing.partials.form')
            </div>

            <div id="listPengajuanTab" class="purchasing-tab-content">
                @include('pages.purchasing.partials.list')
            </div>

        </div>
    </div>

    <script>
        function showPurchasingTab(tabId, button, userRequestedDraft) {
            var tabs = document.querySelectorAll('.purchasing-tab-content');
            var buttons = document.querySelectorAll('.purchasing-tab');

            tabs.forEach(function(tab) {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });

            buttons.forEach(function(btn) {
                btn.classList.remove('active');
            });

            var target = document.getElementById(tabId);

            if (target) {
                target.classList.add('active');
                target.style.display = 'block';
            }

            if (button) {
                button.classList.add('active');
            }

            // Draft Form dibersihkan HANYA saat user benar-benar klik tab Draft Form.
            // Saat halaman pertama kali dibuka, jangan reset mode edit/detail.
            if (tabId === 'draftFormTab' && userRequestedDraft === true) {
                if (typeof window.clearPurchasingDraftCache === 'function') {
                    window.clearPurchasingDraftCache(true);
                } else {
                    // Fallback jika partial form belum selesai dimuat.
                    localStorage.removeItem('pengajuan_purchasing_draft_v1');
                    localStorage.removeItem('pengajuan_purchasing_id');
                }
            }
        }

        // ============================================================
        // DETAIL PENGAJUAN -> TAB BARU DI SEBELAH LIST PENGAJUAN
        // ============================================================
        function openPurchasingDetailTab(id, viewOnly) {
            if (!id) return;

            var tabId = 'purchasingDetailTab_' + id;
            var contentId = 'purchasingDetailContent_' + id;

            var existingTab = document.getElementById(tabId);
            var existingContent = document.getElementById(contentId);

            if (existingTab && existingContent) {
                showPurchasingTab(contentId, existingTab);
                return;
            }

            var tabsBar = document.querySelector('.purchasing-tabs');
            var listTab = document.querySelector(
                '.purchasing-tab[data-tab="listPengajuanTab"]'
            );

            if (!tabsBar || !listTab) {
                console.error('Container tab Purchasing tidak ditemukan.');
                alert('Tab Purchasing belum siap. Silakan refresh halaman.');
                return;
            }

            var tabButton = document.createElement('button');
            tabButton.type = 'button';
            tabButton.id = tabId;
            tabButton.className = 'purchasing-tab purchasing-detail-tab';
            tabButton.setAttribute('data-tab', contentId);
            tabButton.innerHTML =
                '<i class="fa fa-file-text-o"></i> Detail #' + id +
                '<button type="button" class="purchasing-detail-close" title="Tutup">&times;</button>';

            // Tab detail selalu ditempatkan tepat setelah List Pengajuan.
            tabsBar.insertBefore(tabButton, listTab.nextSibling);

            var content = document.createElement('div');
            content.id = contentId;
            content.className = 'purchasing-tab-content purchasing-detail-content';
            content.innerHTML =
                '<div class="purchasing-detail-frame-wrap">' +
                    '<iframe class="purchasing-detail-frame" style="visibility:hidden;" ' +
                        'src="{{ url('/pengajuan_purchasing/edit') }}/' + id +
                        (viewOnly ? '?view_only=1&iframe_detail=1' : '?iframe_detail=1') + '" ' +
                        'title="Detail Pengajuan #' + id + '"></iframe>' +
                '</div>';

            var card = document.querySelector('.purchasing-card');
            card.appendChild(content);

            // Karena route edit mengembalikan halaman Purchasing lengkap,
            // sembunyikan navigasi/tab bagian dalam iframe agar yang terlihat
            // hanya form detail pengajuan.
            var iframe = content.querySelector('.purchasing-detail-frame');
            if (iframe) {
                iframe.addEventListener('load', function() {
                    try {
                        var doc = iframe.contentDocument || iframe.contentWindow.document;
                        /*
                         * Route /edit mengembalikan halaman Purchasing lengkap
                         * karena view detail memakai view yang sama.
                         *
                         * Jangan hanya menyembunyikan tab Purchasing di dalam
                         * iframe. Layout MASTER juga harus dilepas, yaitu:
                         *   - sidebar #aside
                         *   - header .app-header
                         *   - footer .app-footer
                         *   - profile drawer / overlay
                         *   - margin kiri #content
                         *
                         * Dengan begitu iframe hanya menampilkan isi detail.
                         */
                        var innerTabs = doc.querySelector('.purchasing-tabs');
                        var innerList = doc.getElementById('listPengajuanTab');
                        var innerPage = doc.querySelector('.purchasing-page');
                        var innerCard = doc.querySelector('.purchasing-card');

                        if (innerTabs) innerTabs.style.display = 'none';
                        if (innerList) innerList.style.display = 'none';

                        if (innerPage) {
                            innerPage.style.padding = '0';
                            innerPage.style.margin = '0';
                            innerPage.style.minHeight = 'auto';
                            innerPage.style.background = '#fff';
                        }

                        if (innerCard) {
                            innerCard.style.border = '0';
                            innerCard.style.borderRadius = '0';
                            innerCard.style.boxShadow = 'none';
                            innerCard.style.overflow = 'visible';
                        }

                        var innerHead = doc.querySelector('#aside');
                        var innerHeader = doc.querySelector('#content .app-header');
                        var innerFooter = doc.querySelector('#content .app-footer');
                        var innerOverlay = doc.querySelector('#drawerOverlay');
                        var innerDrawer = doc.querySelector('#profileDrawer');
                        var innerContent = doc.querySelector('#content');
                        var innerView = doc.querySelector('#view');

                        if (innerHead) innerHead.style.display = 'none';
                        if (innerHeader) innerHeader.style.display = 'none';
                        if (innerFooter) innerFooter.style.display = 'none';
                        if (innerOverlay) innerOverlay.style.display = 'none';
                        if (innerDrawer) innerDrawer.style.display = 'none';

                        if (innerContent) {
                            innerContent.style.marginLeft = '0';
                            innerContent.style.width = '100%';
                            innerContent.style.padding = '0';
                            innerContent.style.minHeight = '0';
                        }

                        if (innerView) {
                            innerView.style.margin = '0';
                            innerView.style.padding = '0';
                            innerView.style.width = '100%';
                        }

                        if (doc.body) {
                            doc.body.style.margin = '0';
                            doc.body.style.padding = '0';
                            doc.body.style.overflowX = 'hidden';
                        }

                        /*
                         * CSS override tambahan agar aturan CSS dari master
                         * yang lebih spesifik tidak mengembalikan sidebar/header.
                         */
                        var cleanStyle = doc.getElementById('purchasingDetailCleanLayout');
                        if (!cleanStyle) {
                            cleanStyle = doc.createElement('style');
                            cleanStyle.id = 'purchasingDetailCleanLayout';
                            cleanStyle.textContent = `
                                html, body { margin:0 !important; padding:0 !important; }
                                #aside { display:none !important; width:0 !important; }
                                #content { margin-left:0 !important; width:100% !important; padding:0 !important; }
                                #content > .app-header,
                                #content > .app-footer,
                                #drawerOverlay,
                                #profileDrawer { display:none !important; }
                                #view { margin:0 !important; padding:0 !important; width:100% !important; }
                                .purchasing-page { padding:0 !important; margin:0 !important; min-height:auto !important; }
                                .purchasing-card { border:0 !important; border-radius:0 !important; box-shadow:none !important; }
                            `;
                            (doc.head || doc.documentElement).appendChild(cleanStyle);
                        }

                        // Jangan tampilkan iframe sebelum layout internal selesai dibersihkan.
                        // Ini mencegah sidebar/header terlihat sepersekian detik saat iframe pertama kali load.
                        iframe.style.visibility = 'visible';
                    } catch (error) {
                        console.warn('Tidak dapat merapikan iframe detail:', error);
                        // Fallback: tetap tampilkan iframe jika ada error agar detail tidak blank.
                        iframe.style.visibility = 'visible';
                    }
                });
            }

            // Tombol close detail.
            tabButton.querySelector('.purchasing-detail-close').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var detailContent = document.getElementById(contentId);
                if (detailContent) detailContent.remove();
                if (tabButton) tabButton.remove();

                showPurchasingTab(
                    'listPengajuanTab',
                    document.querySelector('.purchasing-tab[data-tab="listPengajuanTab"]')
                );
            });

            // Klik area tab selain tombol close -> aktifkan detail.
            tabButton.addEventListener('click', function(e) {
                if (e.target.closest('.purchasing-detail-close')) return;
                showPurchasingTab(contentId, tabButton);
            });

            showPurchasingTab(contentId, tabButton);
        }

        window.openPurchasingDetailTab = openPurchasingDetailTab;

        // ============================================================
        // GLOBAL DETAIL CLICK GUARD
        // ============================================================
        // Capture phase berjalan sebelum delegated click handler dari
        // partial form/list. Ini mencegah handler lama melakukan
        // window.location.href dan menghilangkan tab detail.
        document.addEventListener('click', function (e) {
            const button = e.target.closest('.btn-open-purchasing-detail');
            if (!button) return;

            const id = button.getAttribute('data-id');
            const viewOnly = String(button.getAttribute('data-view-only')) === '1';

            if (!id) {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert('ID pengajuan tidak ditemukan.');
                return;
            }

            if (typeof window.openPurchasingDetailTab === 'function') {
                e.preventDefault();
                e.stopImmediatePropagation();
                window.openPurchasingDetailTab(id, viewOnly);
            }
        }, true);

      document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);

    const uri = params.get('uri');
    const published = params.get('published');

    const openList =
        uri === 'list_pengajuan' ||
        published === 'true' ||
        published === '1';

    if (openList) {
        showPurchasingTab(
            'listPengajuanTab',
            document.querySelector(
                '.purchasing-tab[data-tab="listPengajuanTab"]'
            )
        );
    } else {
        showPurchasingTab(
            'draftFormTab',
            document.querySelector(
                '.purchasing-tab[data-tab="draftFormTab"]'
            )
        );
    }
});
      </script>
@endsection
