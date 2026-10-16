@extends('master.master')
@section('title', 'Release Order Marketing')
@section('content')
    @include('pages.spk.stylespk')
    @include('pages.marketing.style')

    <style>
        /* =========================================================
                       RELEASE ORDER - DETAIL TABLE UI
                       ========================================================= */
        #detail-view .box-body {
            padding-bottom: 12px;
        }

        #detail-view .table-info {
            margin-bottom: 12px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(31, 52, 95, .06);
        }

        #detail-view .table-info td {
            padding: 9px 14px;
            border-color: #e8ecf4;
        }

        #detail-view .table-info td:first-child {
            width: 190px;
            background: #f6f8fc;
            color: #52607a;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .45px;
        }

        #detail-view .table-info td:last-child {
            color: #17233d;
            font-weight: 600;
        }

        #btn-back {
            border: 0;
            border-radius: 8px;
            padding: 7px 12px;
            font-weight: 600;
        }

        #detail-action-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin: 0 0 12px 0;
        }

        #btn-add-item {
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            box-shadow: 0 4px 10px rgba(13, 110, 253, .14);
        }

        #detail-table tbody tr.new-item-row td {
            background: #eef6ff !important;
            border-top: 2px solid #0d6efd !important;
            border-bottom: 2px solid #b9d8ff !important;
        }

        #detail-table tbody tr.new-item-row td:first-child {
            box-shadow: inset 5px 0 0 #0d6efd;
        }

        #detail-table tbody tr.new-item-row .new-item-input {
            min-width: 70px;
            height: 34px;
            border: 1px solid #7aa7e8;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, .05);
        }

        #detail-table tbody tr.new-item-row .new-item-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .12);
        }

        .new-item-field-wrap,
        .new-item-photo-wrap {
            position: relative;
        }

        .new-item-badge {
            display: inline-block;
            margin-bottom: 4px;
            padding: 2px 6px;
            border-radius: 999px;
            background: #0d6efd;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .new-image-editor {
            min-width: 92px;
            text-align: center;
            outline: none;
            cursor: pointer;
        }

        .new-image-editor.paste-target {
            box-shadow: 0 0 0 2px rgba(13, 110, 253, .18);
            border-radius: 8px;
        }

        .new-act-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            color: #aab3c2;
            font-size: 16px;
            font-weight: 700;
        }

        .new-image-preview {
            width: 76px;
            height: 58px;
            margin: 0 auto 5px;
            border: 1px dashed #9db5d5;
            border-radius: 8px;
            background: #f8fbff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #7a8aa3;
            font-size: 9px;
        }

        .new-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .new-image-actions {
            display: flex;
            justify-content: center;
            gap: 3px;
            flex-wrap: wrap;
        }

        .new-image-actions .btn {
            font-size: 9px;
            padding: 3px 6px;
        }

        .paste-success {
            position: absolute;
            right: 3px;
            bottom: 3px;
            padding: 2px 5px;
            border-radius: 5px;
            background: #198754;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
        }

        #btn-save-all {
            margin: 0 0 12px 0;
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            box-shadow: 0 4px 10px rgba(25, 135, 84, .14);
        }

        /* Horizontal + vertical scroll area.
                       Sticky THEAD tetap bekerja saat body table discroll. */
        .freeze-wrapper {
            position: relative;
            width: 100%;
            max-height: 68vh;
            overflow: auto;
            border: 1px solid #dfe5ef;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(31, 52, 95, .07);
            scrollbar-width: thin;
            scrollbar-color: #aab5c9 #f1f4f8;
        }

        .freeze-wrapper::-webkit-scrollbar {
            width: 9px;
            height: 10px;
        }

        .freeze-wrapper::-webkit-scrollbar-track {
            background: #f1f4f8;
        }

        .freeze-wrapper::-webkit-scrollbar-thumb {
            background: #aab5c9;
            border-radius: 10px;
            border: 2px solid #f1f4f8;
        }

        /* =========================================================
                       TOP HORIZONTAL SCROLL INDICATOR
                       Scrollbar tambahan yang selalu terlihat tepat di bawah
                       2 baris header ketika tabel mempunyai banyak kolom.
                       Posisi ini disinkronkan dengan .freeze-wrapper.
                       ========================================================= */
        .detail-scroll-top {
            position: sticky;
            top: 68px;
            left: 1px;
            right: 1px;
            height: 13px;
            margin-bottom: -13px;
            overflow-x: auto;
            overflow-y: hidden;
            z-index: 35;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid #dfe5ef;
            scrollbar-width: thin;
            scrollbar-color: #7f8da8 #eef2f7;
        }

        .detail-scroll-top::-webkit-scrollbar {
            height: 10px;
        }

        .detail-scroll-top::-webkit-scrollbar-track {
            background: #eef2f7;
        }

        .detail-scroll-top::-webkit-scrollbar-thumb {
            background: #7f8da8;
            border-radius: 10px;
            border: 2px solid #eef2f7;
        }

        .detail-scroll-top-inner {
            height: 1px;
        }

        @media (max-width: 768px) {
            .detail-scroll-top {
                top: 62px;
            }
        }

        #detail-table {
            margin: 0;
            width: max-content;
            min-width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            color: #25324b;
            font-size: 12px;
        }

        #detail-table th,
        #detail-table td {
            border-right: 1px solid #e3e8f0;
            border-bottom: 1px solid #e8ecf2;
            vertical-align: middle;
        }

        #detail-table thead th {
            position: sticky;
            background: #2f437f;
            color: #fff;
            text-align: center;
            vertical-align: middle;
            font-weight: 700;
            white-space: nowrap;
            z-index: 20;
            box-shadow: 0 1px 0 rgba(255, 255, 255, .08);
        }

        #detail-table thead .header-top th {
            top: 0;
            height: 38px;
            padding: 7px 10px;
            font-size: 11px;
            letter-spacing: .35px;
        }

        #detail-table thead .header-bottom th {
            top: 38px;
            height: 30px;
            padding: 5px 8px;
            font-size: 11px;
            z-index: 21;
        }

        #detail-table thead .dimension-group {
            letter-spacing: .55px;
            border-left: 1px solid rgba(255, 255, 255, .22);
            border-right: 1px solid rgba(255, 255, 255, .22);
        }

        #detail-table thead .dimension-sub {
            width: 56px !important;
            min-width: 56px !important;
            max-width: 56px !important;
        }

        #detail-table thead .item-group {
            background: #344b8c;
        }

        #detail-table thead .packing-group {
            background: #344b8c;
        }

        #detail-table tbody td {
            height: 58px;
            padding: 8px 10px;
            background: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #detail-table tbody tr:nth-child(even) td {
            background: #f9fbfd;
        }

        #detail-table tbody tr:hover td {
            background: #eef4ff;
        }

        #detail-table tbody tr.editing td {
            background: #fff8e1 !important;
        }

        #detail-table thead .inch-group {
            background: #226c82;
        }

        #detail-table tbody td[data-key$="_inch"] {
            background: #f0f9fc;
        }

        #detail-table .dimension-cell {
            text-align: center;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: #33415f;
        }

        #detail-table .number-cell {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        #detail-table .description-cell {
            text-align: left;
            font-weight: 600;
            color: #17233d;
        }

        #detail-table .photo-cell {
            text-align: center;
        }

        #detail-table .photo-cell img {
            display: block;
            width: 54px;
            height: 54px;
            object-fit: contain;
            margin: auto;
            border-radius: 8px;
            background: #f7f8fb;
            border: 1px solid #e5e9f0;
        }

        #detail-table .cell-text {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        #detail-table .inline-input {
            min-width: 70px;
            height: 32px;
            padding: 4px 7px;
            border-radius: 6px;
            border: 1px solid #b8c4d8;
            font-size: 12px;
        }

        #detail-table .inline-input:focus {
            border-color: #2f437f;
            box-shadow: 0 0 0 2px rgba(47, 67, 127, .12);
        }

        #detail-table .btn-xs {
            margin: 2px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
        }

        #detail-table tfoot td {
            position: sticky;
            bottom: 0;
            z-index: 10;
            padding: 9px 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Keep first visible detail column readable on horizontal scroll.
                       This does not alter the existing horizontal scrolling behavior. */
        #detail-table .sticky-col {
            position: sticky;
            left: 0;
            z-index: 22;
            background: #fff !important;
            box-shadow: 5px 0 10px -10px rgba(20, 35, 65, .5);
        }

        #detail-table thead .sticky-col {
            z-index: 30;
            background: #2f437f !important;
        }

        #detail-table tbody tr:nth-child(even) .sticky-col {
            background: #f9fbfd !important;
        }

        #detail-table tbody tr:hover .sticky-col {
            background: #eef4ff !important;
        }

        @media (max-width: 768px) {
            .freeze-wrapper {
                max-height: 64vh;
                border-radius: 8px;
            }

            #detail-table {
                font-size: 11px;
            }

            #detail-table thead .header-top th {
                height: 34px;
                font-size: 10px;
            }

            #detail-table thead .header-bottom th {
                top: 34px;
                height: 28px;
            }
        }

        /* Chat shortcut: animated typing caret, isolated from table styles */
        #chatModal .modal-dialog {
            max-width: 680px;
        }

        #chatModal #chat-input:focus {
            border-color: #2f437f;
            box-shadow: 0 0 0 2px rgba(47, 67, 127, .12);
            caret-color: #2f437f;
        }

        #chatModal .release-chat-caret {
            display: inline-block;
            width: 2px;
            height: 13px;
            background: #2f437f;
            vertical-align: -2px;
            margin-left: 3px;
            animation: releaseChatBlink .85s steps(1) infinite;
        }

        @keyframes releaseChatBlink {
            50% {
                opacity: 0;
            }
        }
    </style>
    <div class="padding">
        <div class="box">
            <div class="box-header">
                <h2>Release PFI</h2>
                <small>___</small>
            </div>
            <input type="hidden" id="role" value="{{ auth()->user()->role }}">
            <input type="hidden" id="id" value="{{ auth()->user()->role }}">
            <div class="box-body">
                <div class="box-header d-flex justify-content-between align-items-center flex-wrap gap-3">

                    {{-- LEFT : SEARCH --}}
                    <div style="max-width:350px; width:100%;">

                        <input type="text" id="search-qc" class="form-control" placeholder="Search PO / Item / Vendor">

                    </div>

                    {{-- RIGHT : FILTER --}}
                    <div style="width:180px;">

                        <select id="filter-spk-type" class="form-control">

                            <option value="">
                                Semua
                            </option>

                            <option value="NW">
                                NW
                            </option>

                            <option value="NWS">
                                NWS
                            </option>

                        </select>

                    </div>

                </div>
                <!--<div class="col-12 d-flex justify-content-end">-->
                <!--    <a href="/semua-spk"-->
                <!--        class="btn btn-primary btn-sm">-->
                <!--        All SPK-->
                <!--    </a>-->
                <!--</div>-->
                <div class="row" id="default-table">
                    <div class="col-sm-12">
                        <div class="box">
                            <div class="table-responsive po-wrapper">

                                <table id="po-table" class="table-bordered">
                                    <thead>
                                        <tr class="">
                                            <th id="sort-order-no"
                                                style="cursor:pointer;white-space:nowrap;user-select:none;">

                                                Order No

                                                <i id="sort-order-icon" class="fa fa-sort ml-1" style="color:white">
                                                </i>

                                            </th>
                                            <th>Company Name</th>
                                            <th>Country</th>

                                            <th>Release date</th>
                                            <th>Shipment Date</th>
                                            <th>Category</th>
                                            <th>Actual Ship</th>
                                            @if (in_array(strtolower(auth()->user()->role), ['marketing', 'sales', 'export', 'finance']))
                                                <th>Value</th>
                                            @endif
                                            <th>Cont Numb</th>
                                            <th>DO Released</th>
                                            <th>Remark</th>

                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="po-table-body">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="detail-view" style="display:none;">
                    <div class="col-sm-12">
                        <div class="box">
                            <div class="box-body">
                                <button class="btn btn-default btn-sm" id="btn-back">
                                    ← Show All Order Release
                                </button>
                                <hr>
                                @php
                                    $user = auth()->user();
                                    $divisi = optional($user->karyawan->divisi)->nama;
                                @endphp
                                @if ($divisi == 'PURCHASING')
                                    <div class="row">
                                        <div class="col-12 d-flex justify-content-end">
                                            <a href="#" class="btn btn-primary btn-sm" id="btn-buat-spk">
                                                Buat SPK
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <table class="table table-bordered table-info">
                                    <tr>
                                        <td width="200"><b>Order No</b></td>
                                        <td id="d-order"></td>
                                    </tr>
                                    <tr>
                                        <td><b>Company Name</b></td>
                                        <td id="d-company"></td>
                                    </tr>
                                    <tr>
                                        <td><b>Shipment Date</b></td>
                                        <td id="d-ship"></td>
                                    </tr>
                                    <tr>
                                        <td><b>Country</b></td>
                                        <td id="d-country"></td>
                                    </tr>

                                </table>
                            </div>
                            <div id="detail-action-bar">
                                <button id="btn-save-all" class="btn btn-success btn-sm">
                                    💾 Save All Changes
                                </button>

                                @if (strtolower((string) auth()->user()->name) === 'rodiyah')
                                    <button id="btn-add-item" type="button" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> Add Items
                                    </button>
                                @endif
                            </div>
                            <div class="freeze-wrapper">
                                <div class="detail-scroll-top" id="detail-scroll-top" aria-label="Horizontal table scroll">
                                    <div class="detail-scroll-top-inner" id="detail-scroll-top-inner"></div>
                                </div>
                                <table class="table table-bordered table-striped" id="detail-table">
                                    <thead id="detail-table-head"></thead>
                                    <tbody id="detail-item-table"></tbody>
                                    <tfoot id="detail-table-foot"></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- CHAT MODAL -->
        <div class="modal fade" id="chatModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Chat Room <span class="release-chat-caret" aria-hidden="true"></span></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="chat-box" style="height:400px; overflow:auto; background:#f5f5f5; padding:10px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="chat-room-id">
                        <div class="input-group">
                            <input type="text" id="chat-input" class="form-control" placeholder="Type message...">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" id="btn-send-chat">Send</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="save-status">
            ✔ All changes saved
        </div>
        <div id="loadingOverlay">
            <div class="loading-content">
                <div class="spinner-border text-primary" style="width:60px;height:60px"></div>

                <h5 class="mt-3">
                    Sedang mengambil data...
                </h5>

                <small class="text-muted">
                    Mohon tunggu sebentar
                </small>
            </div>
        </div>
        <pre id="result"></pre>
        @push('scripts')
            <script>
                let orderSort = 'asc';
                const currentUsername = @json(auth()->user()->name);
                window.currentUsername = currentUsername;
            </script>
            <script>
                const CURRENT_USER_ID = {{ auth()->id() }};
            </script>
            @push('scripts')
                <script>
                    /* =====================================================
                                                                                   INIT PAGE (WAJIB UNTUK PJAX)
                                                                                ===================================================== */
                    /* ===== ROLE ===== */
                    const role = ($('#role').val() || '').toLowerCase();
                    const canEdit = ['marketing', 'sales', 'export', 'finance'].includes(role);
                    const canSeeValue = ['marketing', 'sales', 'export', 'finance'].includes(role);

                    function initPage() {
                        console.log('INIT PAGE JALAN');



                        if (role !== 'marketing') {
                            $('#btn-save-all')
                                .prop('disabled', true)
                                .addClass('disabled');
                        }
                        /* ===== LOAD TABLE ===== */
                        loadPoTable();
                        initDetailTopScroll();
                        $('#search-qc')
                            .off('keyup')
                            .on('keyup', function() {

                                loadPoTable(
                                    this.value,
                                    $('#filter-spk-type').val()
                                );

                            });
                        /* ===== BACK BUTTON ===== */
                        $('#btn-back')
                            .off('click')
                            .on('click', function() {
                                $('#detail-view').hide();
                                $('#default-table').show();
                            });
                        $('#filter-spk-type')
                            .off('change')
                            .on('change', function() {

                                loadPoTable(
                                    $('#search-qc').val(),
                                    $(this).val()
                                );

                            });
                    }
                    /* =====================================================
                       TOP HORIZONTAL SCROLL SYNC
                       Menampilkan indicator/scrollbar horizontal tepat di
                       bawah header dan menyinkronkannya dengan tabel utama.
                    ===================================================== */
                    function initDetailTopScroll() {
                        const wrapper = document.querySelector('.freeze-wrapper');
                        const topScroll = document.getElementById('detail-scroll-top');
                        const topInner = document.getElementById('detail-scroll-top-inner');
                        const table = document.getElementById('detail-table');

                        if (!wrapper || !topScroll || !topInner || !table) return;

                        const syncWidth = () => {
                            topInner.style.width = table.scrollWidth + 'px';
                            topScroll.scrollLeft = wrapper.scrollLeft;
                        };

                        topScroll.onscroll = function() {
                            wrapper.scrollLeft = topScroll.scrollLeft;
                        };

                        wrapper.addEventListener('scroll', function() {
                            if (Math.abs(topScroll.scrollLeft - wrapper.scrollLeft) > 1) {
                                topScroll.scrollLeft = wrapper.scrollLeft;
                            }
                        });

                        requestAnimationFrame(syncWidth);
                        setTimeout(syncWidth, 250);
                        setTimeout(syncWidth, 700);

                        if (window.ResizeObserver) {
                            const ro = new ResizeObserver(syncWidth);
                            ro.observe(table);
                        }
                    }

                    /* =====================================================
                       LOAD TABLE
                    ===================================================== */
                    // const role = ($('#role').val() || '').toLowerCase();

                    function canEditField(field) {

                        switch (role) {

                            case 'marketing':
                                return true;

                            case 'export':
                                return [
                                    'act_ship',
                                    'cont_numb',
                                    'do_released'
                                ].includes(field);

                            default:
                                return false;
                        }

                    }

                    function editableTd(field, value, id, type = 'text') {

                        let editable = canEditField(field);

                        return `
                <td>
                    <input
                        type="${type}"
                        class="po-edit ${editable ? '' : 'readonly-input'}"
                        data-id="${id}"
                        data-field="${field}"
                        value="${value ?? ''}"
                        ${editable ? '' : 'readonly tabindex="-1"'}
                    >
                </td>
            `;
                    }
                    // dedaline
                    function deadlineProgress(releaseDate, shipmentDate) {

                        if (!releaseDate || !shipmentDate) {
                            return '<span class="text-muted">-</span>';
                        }

                        let start = new Date(releaseDate);
                        let end = new Date(shipmentDate);
                        let now = new Date();

                        let totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

                        if (totalDays <= 0) {
                            return '-';
                        }

                        let elapsedDays = Math.ceil((now - start) / (1000 * 60 * 60 * 24));

                        let percent = (elapsedDays / totalDays) * 100;

                        percent = Math.max(0, Math.min(100, percent));

                        let barColor = '#28a745';
                        let textColor = '#2F437F';

                        if (percent >= 60) {
                            barColor = '#f0ad4e';
                            textColor = '#2F437F';
                        }

                        if (percent >= 85) {
                            barColor = '#dc3545';
                            textColor = '#dc3545';
                        }

                        // ===== OVERDUE =====

                        if (now > end) {

                            let late = Math.ceil((now - end) / (1000 * 60 * 60 * 24));

                            return `
        <div style="min-width:180px">

            <div class="progress" style="height:18px;border-radius:20px">

                <div class="progress-bar"
                    style="
                        width:100%;
                        background:#dc3545;
                        font-size:11px;
                    ">
                    100%
                </div>

            </div>

            <div style="
                font-size:11px;
                margin-top:3px;
                color:#dc3545;
                font-weight:bold;
            ">
                ${totalDays} / ${totalDays} Hari
                <br>
                🔴 Late ${late} Hari
            </div>

        </div>
        `;
                        }

                        let sisa = Math.ceil((end - now) / (1000 * 60 * 60 * 24));

                        return `
    <div style="min-width:180px">

        <div class="progress" style="height:18px;border-radius:20px">

            <div class="progress-bar"
                style="
                    width:${percent}%;
                    background:${barColor};
                    font-size:11px;
                ">
                ${Math.round(percent)}%
            </div>

        </div>

        <div style="
            font-size:11px;
            margin-top:3px;
            color:${textColor};
            font-weight:bold;
        ">

            ${Math.max(0,elapsedDays)} / ${totalDays} Hari
            <br>

            🟢 Sisa ${sisa} Hari

        </div>

    </div>
    `;
                    }

                    function loadPoTable(keyword = '', type = '') {
                        fetch(
                                `{{ route('marketing.ajax.po') }}
?q=${encodeURIComponent(keyword)}
&type=${encodeURIComponent(type)}
&sort=${orderSort}`
                            )
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(po => {
                                    console.log(po.shipment_date);
                                });
                                const tbody = document.getElementById('po-table-body');

                                if (!tbody) return;
                                tbody.innerHTML = '';
                                if (!data.length) {
                                    tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada data
                        </td>
                    </tr>`;
                                    return;
                                }
                                data.forEach(po => {
                                    let deleteButton = '';

                                    if (currentUsername.toLowerCase() === 'rodiyah') {
                                        deleteButton = `
            <button
                class="btn btn-danger btn-xs btn-delete-po"
                data-id="${po.id}">
                Delete
            </button>
        `;
                                    }
                                    tbody.innerHTML += `
                       <tr>

                            ${editableTd('order_no',po.order_no,po.id)}

                            ${editableTd('company_name',po.company_name,po.id)}

                            ${editableTd('country',po.country,po.id)}

                            ${editableTd('release_date',po.release_date,po.id,'date')}

                            ${editableTd('shipment_date',normalizeDate(po.shipment_date),po.id,'date')}

                            <td>
                                ${deadlineProgress(
                                    normalizeDate(po.release_date),
                                    normalizeDate(po.shipment_date)
                                )}
                            </td>

                            ${editableTd('act_ship',po.act_ship,po.id,'date')}

                          ${canSeeValue
    ? editableTd('value', po.value, po.id, 'number')
    : ''
}

                            ${editableTd('cont_numb',po.cont_numb,po.id)}

                            ${editableTd('do_released',po.do_released,po.id)}

                            ${editableTd('remark',po.remark,po.id)}

                            <td>
                                <button class="btn btn-success btn-xs btn-view"
                                    data-id="${po.id}">
                                    View
                                </button>

                                ${deleteButton}
                            </td>

                        </tr>
                        `;
                                });
                            })
                            .catch(err => console.error(err));
                    }
                    /* =====================================================
                       VIEW DETAIL
                    ===================================================== */
                    // normalize
                    function normalizeDate(date) {

                        if (!date || date == '-') return '';

                        date = date.trim();

                        // Kalau sudah format YYYY-MM-DD
                        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                            return date;
                        }

                        // Buang tulisan dalam kurung
                        date = date.replace(/\(.*?\)/g, '');

                        const months = {
                            january: '01',
                            february: '02',
                            march: '03',
                            april: '04',
                            may: '05',
                            june: '06',
                            july: '07',
                            august: '08',
                            september: '09',
                            october: '10',
                            november: '11',
                            december: '12'
                        };

                        let m = date.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/i);

                        if (!m) return '';

                        let day = m[1].padStart(2, '0');
                        let month = months[m[2].toLowerCase()];

                        return `${m[3]}-${month}-${day}`;
                    }
                    // update
                    $(document).on('change', '.po-edit', function() {
                        if ($(this).prop('readonly')) {
                            return;
                        }
                        let input = $(this);

                        $.ajax({
                            beforeSend: function() {

                                showSaving();

                            },
                            url: '/marketing/po/update-field',

                            type: 'POST',

                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },

                            data: {
                                id: input.data('id'),
                                field: input.data('field'),
                                value: input.val()
                            },

                            success: function() {
                                showSaved();
                                input.addClass('is-valid');

                                setTimeout(function() {

                                    input.removeClass('is-valid');

                                }, 800);

                            }

                        });

                    });
                    // delete
                    $(document).off('click', '.btn-delete-po')
                        .on('click', '.btn-delete-po', function() {

                            let id = $(this).data('id');

                            Swal.fire({
                                    title: 'Yakin hapus?',
                                    text: 'Data PO dan detail akan dihapus',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya Hapus',
                                    cancelButtonText: 'Batal',
                                    confirmButtonColor: '#d33'
                                })
                                .then((result) => {

                                    if (!result.isConfirmed) return;

                                    $.ajax({

                                        url: '/marketing/po-delete/' + id,
                                        type: 'DELETE',

                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },

                                        success: function(res) {

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                text: res.message
                                            });

                                            loadPoTable();

                                        },

                                        error: function(xhr) {

                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Gagal',
                                                text: xhr.responseJSON?.message || 'Delete gagal'
                                            });

                                        }

                                    });

                                });

                        });
                    $(document).off('click', '.btn-view').on('click', '.btn-view', function() {
                        let id = $(this).data('id');
                        $('#btn-buat-spk').attr('href', '/spk/' + id);
                        fetch(`/marketing/po-detail/${id}`)
                            .then(res => res.json())
                            .then(res => {
                                $('#default-table').hide();
                                $('#detail-view').show();
                                $('#d-order').text(res.po.order_no);
                                $('#d-company').text(res.po.company_name);
                                $('#d-ship').text(res.po.shipment_date ?? '-');
                                $('#d-country').text(res.po.country ?? '-');
                                let thead = $('#detail-table-head');
                                let tbody = $('#detail-item-table');
                                let foot = $('#detail-table-foot');
                                thead.html('');
                                tbody.html('');
                                foot.html('');
                                if (!res.items.length) return;
                                // =====================================================
                                // COLLECT ALL KEYS FROM ALL ITEMS
                                // =====================================================
                                let allKeysSet = new Set();

                                res.items.forEach(item => {
                                    const detail = item.detail || {};
                                    Object.keys(detail).forEach(key => allKeysSet.add(key));
                                });

                                // INCH: tampil hanya bila minimal satu item memiliki nilai.
                                // Alias sumber Excel lama tetap dibaca tanpa mengubah database.
                                const inchAliases = {
                                    item_w_inch: ['item_w_inch', 'item_w_(inch)', 'item_w_(in)', 'dimension_(inch)',
                                        'dimention_(inch)', 'dimension_inch', 'dimention_inch'
                                    ],
                                    item_d_inch: ['item_d_inch', 'item_d_(inch)', 'item_d_(in)', 'd_inch'],
                                    item_h_inch: ['item_h_inch', 'item_h_(inch)', 'item_h_(in)', 'h_inch'],
                                    pack_w_inch: ['pack_w_inch', 'packing_w_inch', 'pack_w_(inch)', 'packing_w_(inch)'],
                                    pack_d_inch: ['pack_d_inch', 'packing_d_inch', 'pack_d_(inch)', 'packing_d_(inch)'],
                                    pack_h_inch: ['pack_h_inch', 'packing_h_inch', 'pack_h_(inch)', 'packing_h_(inch)']
                                };
                                const hasDimensionValue = value => value !== null && value !== undefined && String(value)
                                    .trim() !== '';
                                const getInchValue = (detail, key) => {
                                    for (const alias of (inchAliases[key] || [key])) {
                                        if (hasDimensionValue(detail[alias])) return detail[alias];
                                    }
                                    return '';
                                };
                                const activeInchKeys = Object.keys(inchAliases).filter(key =>
                                    res.items.some(item => hasDimensionValue(getInchValue(item.detail || {}, key)))
                                );
                                // Hilangkan alias mentah agar tidak muncul dua kolom untuk satu nilai.
                                const inchAliasSet = new Set(Object.values(inchAliases).flat());
                                allKeysSet = new Set([...allKeysSet].filter(key => !inchAliasSet.has(key)));
                                activeInchKeys.forEach(key => allKeysSet.add(key));
                                let allKeys = [...allKeysSet];

                                // =====================================================
                                // HEADER PRIORITY
                                // =====================================================
                                const priority = [
                                    'no_', 'photo', 'description', 'article_nr_', 'article_nr_nw',
                                    'nw_code', 'sub_category', 'qty', 'remark', 'cushion', 'glass',
                                    'item_w', 'item_d', 'item_h',
                                    'item_w_inch', 'item_d_inch', 'item_h_inch',
                                    'pack_w', 'pack_d', 'pack_h',
                                    'pack_w_inch', 'pack_d_inch', 'pack_h_inch',
                                    'composition', 'finishing', 'cbm', 'total_cbm',
                                    'value_in_usd', 'fob_jakarta_in_usd'
                                ];

                                const role = $('#role').val();

                                // =====================================================
                                // BUILD KEYS
                                // =====================================================
                                let keys = [
                                    ...priority.filter(k => allKeys.includes(k)),
                                    ...allKeys.filter(k => !priority.includes(k))
                                ];

                                // =====================================================
                                // ROLE FILTER
                                // =====================================================
                                if (role !== 'marketing') {
                                    keys = keys.filter(k =>
                                        k !== 'value_in_usd' &&
                                        k !== 'fob_jakarta_in_usd'
                                    );
                                }

                                // =====================================================
                                // INSERT ACT COLUMN BEFORE QTY
                                // =====================================================
                                let qtyIndex = keys.indexOf('qty');
                                if (qtyIndex !== -1) {
                                    keys.splice(qtyIndex, 0, 'act');
                                }

                                // =====================================================
                                // INDEX
                                // =====================================================
                                let descIndex = keys.indexOf('description');
                                let cbmIndex = keys.includes('total_cbm') ?
                                    keys.indexOf('total_cbm') :
                                    keys.indexOf('cbm');
                                let priceIndex = keys.includes('value_in_usd') ?
                                    keys.indexOf('value_in_usd') :
                                    keys.indexOf('fob_jakarta_in_usd');

                                // =====================================================
                                // NORMALIZE PACKING ALIAS UNTUK DISPLAY
                                // packing_w/d/h tetap didukung jika data lama memakainya.
                                // =====================================================
                                const packingAlias = {
                                    packing_w: 'pack_w',
                                    packing_d: 'pack_d',
                                    packing_h: 'pack_h'
                                };

                                keys = keys.map(k => packingAlias[k] || k);

                                // Hilangkan duplikat akibat pack_* + packing_* muncul bersamaan.
                                keys = [...new Set(keys)];

                                // Pastikan grup W/D/H berurutan, termasuk data lama yang tidak punya W.
                                const dimensionGroups = [
                                    ['item_w', 'item_d', 'item_h'],
                                    ['item_w_inch', 'item_d_inch', 'item_h_inch'],
                                    ['pack_w', 'pack_d', 'pack_h'],
                                    ['pack_w_inch', 'pack_d_inch', 'pack_h_inch']
                                ];
                                for (const group of dimensionGroups) {
                                    const present = group.filter(key => keys.includes(key));
                                    if (!present.length) continue;
                                    const firstIndex = Math.min(...present.map(key => keys.indexOf(key)));
                                    keys = keys.filter(key => !group.includes(key));
                                    keys.splice(firstIndex, 0, ...present);
                                }
                                // Hitung ulang index setelah alias dan pengurutan kolom.
                                descIndex = keys.indexOf('description');
                                cbmIndex = keys.includes('total_cbm') ? keys.indexOf('total_cbm') : keys.indexOf('cbm');
                                priceIndex = keys.includes('value_in_usd') ? keys.indexOf('value_in_usd') : keys.indexOf(
                                    'fob_jakarta_in_usd');

                                // Dipakai oleh modal Add Items. ACT bukan field detail_po.
                                window.currentMarketingPoId = id;
                                window.currentDetailKeys = keys;
                                window.currentDetailItems = res.items || [];

                                // =====================================================
                                // COLUMN WIDTHS
                                // Ini yang menghilangkan jarak besar antar W/D/H.
                                // =====================================================
                                const columnWidth = key => {
                                    const widths = {
                                        no_: 58,
                                        photo: 82,
                                        description: 220,
                                        article_nr_: 120,
                                        article_nr_nw: 120,
                                        nw_code: 110,
                                        sub_category: 130,
                                        act: 115,
                                        qty: 70,
                                        remark: 180,
                                        cushion: 90,
                                        glass: 80,
                                        item_w: 56,
                                        item_d: 56,
                                        item_h: 56,
                                        item_w_inch: 66,
                                        item_d_inch: 66,
                                        item_h_inch: 66,
                                        pack_w: 56,
                                        pack_d: 56,
                                        pack_h: 56,
                                        pack_w_inch: 66,
                                        pack_d_inch: 66,
                                        pack_h_inch: 66,
                                        composition: 180,
                                        finishing: 180,
                                        cbm: 78,
                                        total_cbm: 92,
                                        value_in_usd: 125,
                                        fob_jakarta_in_usd: 145
                                    };

                                    return widths[key] || 120;
                                };

                                // =====================================================
                                // COLGROUP - WIDTH TETAP
                                // =====================================================
                                $('#detail-table colgroup').remove();
                                let colgroup = $('<colgroup></colgroup>');
                                keys.forEach(k => {
                                    colgroup.append(
                                        `<col style="width:${columnWidth(k)}px;min-width:${columnWidth(k)}px;">`
                                    );
                                });
                                $('#detail-table').prepend(colgroup);

                                // =====================================================
                                // HEADER 2 LEVEL
                                // =====================================================
                                let headerTop = $('<tr class="header-top"></tr>');
                                let headerBottom = $('<tr class="header-bottom"></tr>');

                                const itemDimensionKeys = ['item_w', 'item_d', 'item_h', 'item_w_inch', 'item_d_inch',
                                    'item_h_inch'
                                ];
                                const packingDimensionKeys = ['pack_w', 'pack_d', 'pack_h', 'pack_w_inch', 'pack_d_inch',
                                    'pack_h_inch'
                                ];
                                const dimensionHeaderGroups = [{
                                        keys: ['item_w', 'item_d', 'item_h'],
                                        title: 'ITEM DIMENSION (CM)',
                                        cls: 'item-group'
                                    },
                                    {
                                        keys: ['item_w_inch', 'item_d_inch', 'item_h_inch'],
                                        title: 'ITEM DIMENSION (INCH)',
                                        cls: 'item-group inch-group'
                                    },
                                    {
                                        keys: ['pack_w', 'pack_d', 'pack_h'],
                                        title: 'PACKING DIMENSION (CM)',
                                        cls: 'packing-group'
                                    },
                                    {
                                        keys: ['pack_w_inch', 'pack_d_inch', 'pack_h_inch'],
                                        title: 'PACKING DIMENSION (INCH)',
                                        cls: 'packing-group inch-group'
                                    }
                                ];
                                const groupedKeys = new Set(dimensionHeaderGroups.flatMap(group => group.keys));
                                keys.forEach((k, i) => {
                                    const group = dimensionHeaderGroups.find(group => group.keys.includes(k));
                                    if (group) {
                                        const present = group.keys.filter(key => keys.includes(key));
                                        if (k !== present[0]) return;
                                        headerTop.append(
                                            `<th colspan="${present.length}" class="dimension-group ${group.cls}">${group.title}</th>`
                                            );
                                        present.forEach(key => {
                                            const axis = key.match(/_([wdh])(?:_inch)?$/)?.[1]
                                            ?.toUpperCase() || key;
                                            headerBottom.append(
                                                `<th class="dimension-sub" data-header-key="${key}">${axis}</th>`
                                                );
                                        });
                                        return;
                                    }
                                    const cls = i === descIndex ? 'sticky-col' : '';
                                    const label = k === 'act' ? 'ACT' : k.replaceAll('_', ' ').toUpperCase();
                                    headerTop.append(`<th rowspan="2" class="${cls}">${escapeHtml(label)}</th>`);
                                });

                                thead.append(headerTop);
                                thead.append(headerBottom);
                                // =========================
                                // TOTAL
                                // =========================
                                let totalCbm = 0;
                                let totalPrice = 0;
                                // =========================
                                // BODY
                                // =========================
                                res.items.forEach(item => {
                                    let detail = item.detail || {};
                                    let row = $(`<tr class="editable-row" data-id="${item.id}"></tr>`);
                                    let rawCode =
                                        detail.article_code ||
                                        detail.article_nr_ ||
                                        detail.nw_code ||
                                        '';
                                    let articleCode = encodeURIComponent(rawCode);
                                    keys.forEach((key, i) => {
                                        let cls = (i === descIndex) ? 'sticky-col' : '';
                                        // 🔥 ACT COLUMN
                                        if (key === 'act') {
                                            let rawCode =
                                                // detail.article_code ||
                                                detail.article_nr_ ||
                                                detail.nw_code ||
                                                '';
                                            let articleCode = encodeURIComponent(rawCode);
                                            row.append(`
        <td class="${cls}">
            <a href="/cad/${articleCode}" class="btn btn-xs btn-primary">CAD</a>
          <button class="btn btn-xs btn-warning btn-chat"
    data-id="${item.id}">
    CHAT
</button>
        </td>
    `);
                                            return;
                                        }
                                        // =====================================================
                                        // VALUE + CELL CLASS
                                        // =====================================================
                                        let value = detail[key] ?? '';
                                        if (activeInchKeys.includes(key)) value = getInchValue(detail, key);

                                        // Backward compatibility untuk data lama.
                                        if (value === '' && key === 'item_w') {
                                            value = detail['dimention_(cm)'] ?? detail['dimension_(cm)'] ??
                                                detail['dimension'] ?? '';
                                        }
                                        if (value === '' && key === 'item_d') {
                                            value = detail['d'] ?? '';
                                        }
                                        if (value === '' && key === 'item_h') {
                                            value = detail['h'] ?? '';
                                        }
                                        if (value === '' && key === 'pack_w') {
                                            value = detail['packing_w'] ?? '';
                                        }
                                        if (value === '' && key === 'pack_d') {
                                            value = detail['packing_d'] ?? '';
                                        }
                                        if (value === '' && key === 'pack_h') {
                                            value = detail['packing_h'] ?? '';
                                        }

                                        let cellClasses = [cls];

                                        if (itemDimensionKeys.includes(key) || packingDimensionKeys
                                            .includes(key)) {
                                            cellClasses.push('dimension-cell');
                                        }

                                        if (key === 'description') {
                                            cellClasses.push('description-cell');
                                        }

                                        if (key.includes('photo')) {
                                            cellClasses.push('photo-cell');
                                        }

                                        if (['qty', 'cbm', 'total_cbm', 'value_in_usd',
                                                'fob_jakarta_in_usd'
                                            ].includes(key)) {
                                            cellClasses.push('number-cell');
                                        }

                                        let td = $(
                                            `<td class="${cellClasses.filter(Boolean).join(' ')}" data-key="${key}"></td>`
                                        );
                                        // FORMAT CBM
                                        if ((key === 'cbm' || key === 'total_cbm') && value !== '') {
                                            value = isNaN(value) ? '0.00' : parseFloat(value).toFixed(2);
                                            if (key === 'total_cbm') totalCbm += parseFloat(value);
                                        }
                                        // FORMAT PRICE
                                        if (key === 'value_in_usd' || key === 'fob_jakarta_in_usd') {
                                            totalPrice += parseFloat(value || 0);
                                        }
                                        // INCH: pembulatan hanya pada tampilan, nilai asli tetap disimpan.
                                        const isInchDimension = activeInchKeys.includes(key);
                                        const rawInchValue = isInchDimension ? value : null;
                                        if (isInchDimension && value !== '' && value !== null &&
                                            Number.isFinite(Number(value))) {
                                            value = Number(value).toFixed(2);
                                            td.attr('data-raw-inch', String(rawInchValue));
                                            td.attr('title', 'Nilai asli: ' + rawInchValue);
                                        }
                                        // IMAGE
                                        if (key.includes('photo') && typeof value === 'string' && value
                                            .startsWith('http')) {
                                            td.html(`<img src="${value}" alt="Photo">`);
                                        } else {
                                            td.html(`<span class="cell-text">${value}</span>`);
                                        }
                                        row.append(td);
                                    });
                                    tbody.append(row);
                                });
                                // =========================
                                // FOOTER
                                // =========================
                                // TOTAL CBM harus tampil untuk SEMUA ROLE.
                                // TOTAL FOB PRICE tetap khusus marketing.
                                // Sebelumnya priceIndex = -1 pada role non-marketing
                                // menyebabkan emptyTds(-1) dan footer gagal dirender.
                                let footerHtml = '';

                                if (cbmIndex >= 0) {
                                    footerHtml += `
                <tr style="font-weight:bold;background:#f4f6f9">
                    ${emptyTds(cbmIndex)}
                    <td>TOTAL CBM</td>
                    <td>${isNaN(totalCbm) ? '0.00' : totalCbm.toFixed(1)}</td>
                    ${emptyTds(Math.max(0, keys.length - cbmIndex - 2))}
                </tr>`;
                                }

                                if (role === 'marketing' && priceIndex >= 0) {
                                    footerHtml += `
                <tr style="font-weight:bold;background:#e8f5e9">
                    ${emptyTds(priceIndex)}
                    <td>TOTAL FOB PRICE</td>
                    <td>${totalPrice.toLocaleString('id-ID')}</td>
                    ${emptyTds(Math.max(0, keys.length - priceIndex - 2))}
                </tr>`;
                                }

                                foot.html(footerHtml);
                            });
                    });
                    /* =====================================================
                       EDIT ROW (AMAN PJAX)
                    ===================================================== */
                    $(document).off('click', '.editable-row').on('click', '.editable-row', function() {
                        const role = $('#role').val();
                        if (role !== 'marketing') return;
                        let row = $(this);
                        $('.editable-row.editing').not(row).each(function() {
                            exitEdit($(this));
                        });
                        if (row.hasClass('editing')) return;
                        row.addClass('editing').css('background', '#fff8e1');
                        row.find('td[data-key]').each(function() {
                            let td = $(this);
                            let key = td.data('key');
                            if (key && key.toLowerCase().includes('photo')) return;
                            let text = td.attr('data-raw-inch') ?? td.find('.cell-text').text().trim();
                            td.attr('data-original', text);
                            td.html(`
            <input type="text"
                class="form-control form-control-sm inline-input"
                value="${text}">
        `);
                        });
                    });
                    /* =====================================================
                       EXIT EDIT
                    ===================================================== */
                    function exitEdit(row) {
                        row.removeClass('editing').css('background', '');
                        row.find('td[data-key]').each(function() {
                            let td = $(this);
                            let input = td.find('input');
                            if (!input.length) return;
                            let val = input.val();
                            td.html(`<span class="cell-text">${val}</span>`);
                        });
                    }
                    /* =====================================================
                       ADD ITEM INLINE - KHUSUS RODIYAH
                       Add langsung menjadi BARIS PALING ATAS.
                       Baris diberi marker NEW + mode editing.
                    ===================================================== */
                    function addItemLabel(key) {
                        const labels = {
                            no_: 'NO',
                            photo: 'PHOTO',
                            description: 'DESCRIPTION',
                            article_nr_: 'ARTICLE NR',
                            article_nr_nw: 'ARTICLE NR NW',
                            nw_code: 'NW CODE',
                            sub_category: 'SUB CATEGORY',
                            qty: 'QTY',
                            remark: 'REMARK',
                            cushion: 'CUSHION',
                            glass: 'GLASS',
                            item_w: 'ITEM W',
                            item_d: 'ITEM D',
                            item_h: 'ITEM H',
                            pack_w: 'PACK W',
                            pack_d: 'PACK D',
                            pack_h: 'PACK H',
                            item_w_inch: 'ITEM W (INCH)',
                            item_d_inch: 'ITEM D (INCH)',
                            item_h_inch: 'ITEM H (INCH)',
                            pack_w_inch: 'PACK W (INCH)',
                            pack_d_inch: 'PACK D (INCH)',
                            pack_h_inch: 'PACK H (INCH)',
                            composition: 'COMPOSITION',
                            finishing: 'FINISHING',
                            cbm: 'CBM',
                            total_cbm: 'TOTAL CBM',
                            value_in_usd: 'VALUE IN USD',
                            fob_jakarta_in_usd: 'FOB JAKARTA IN USD'
                        };
                        return labels[key] || key.replaceAll('_', ' ').toUpperCase();
                    }

                    function addItemInputType(key) {
                        if (['qty', 'item_w', 'item_d', 'item_h', 'pack_w', 'pack_d', 'pack_h', 'item_w_inch', 'item_d_inch',
                                'item_h_inch', 'pack_w_inch', 'pack_d_inch', 'pack_h_inch', 'cbm', 'total_cbm', 'value_in_usd',
                                'fob_jakarta_in_usd'
                            ].includes(key)) {
                            return 'number';
                        }
                        return 'text';
                    }

                    function nextItemNo() {
                        let maxNo = 0;
                        (window.currentDetailItems || []).forEach(item => {
                            const raw = item.detail?.no_;
                            const n = parseInt(raw, 10);
                            if (!isNaN(n)) maxNo = Math.max(maxNo, n);
                        });
                        return maxNo + 1;
                    }

                    function escapeHtml(value) {
                        return $('<div>').text(value ?? '').html();
                    }

                    function buildNewItemInput(key) {
                        const value = key === 'no_' ? nextItemNo() : '';
                        const type = addItemInputType(key);
                        const isLong = ['description', 'remark', 'cushion', 'glass', 'composition', 'finishing', 'packaging'].includes(
                            key);

                        if (key.includes('photo')) {
                            return `
                    <div class="new-image-editor" data-image-key="${key}" tabindex="0" title="Klik area foto lalu Ctrl+V untuk paste gambar dari Excel">
                        <input type="file"
                            class="new-photo-file"
                            accept="image/*"
                            style="display:none">
                        <input type="hidden"
                            class="new-photo-value"
                            data-key="${key}"
                            value="">
                        <div class="new-image-preview">
                            <span class="new-image-empty">
                                <i class="fa fa-image"></i>
                                <br>Browse / Paste
                            </span>
                        </div>
                        <div class="new-image-actions">
                            <button type="button" class="btn btn-xs btn-primary btn-browse-new-image">
                                <i class="fa fa-folder-open"></i> Browse
                            </button>
                            <button type="button" class="btn btn-xs btn-default btn-paste-help">
                                <i class="fa fa-paste"></i> Paste
                            </button>
                        </div>
                    </div>
                `;
                        }

                        const input = isLong ?
                            `<textarea class="form-control form-control-sm new-item-input" data-key="${key}" placeholder="${addItemLabel(key)}"></textarea>` :
                            `<input type="${type}" class="form-control form-control-sm new-item-input" data-key="${key}" value="${escapeHtml(value)}" placeholder="${addItemLabel(key)}" ${type === 'number' ? 'step="any"' : ''}>`;

                        return input;
                    }

                    function createNewItemRow() {
                        if (!window.currentMarketingPoId) {
                            alert('PO belum dipilih');
                            return;
                        }

                        const tbody = $('#detail-item-table');
                        if (!tbody.length) return;

                        // Jangan membuat 2 baris baru sekaligus.
                        const existingNew = tbody.find('tr.new-item-row');
                        if (existingNew.length) {
                            existingNew.find('.new-item-input').first().focus();
                            return;
                        }

                        const keys = (window.currentDetailKeys || []);
                        if (!keys.length) {
                            alert('Header item belum tersedia');
                            return;
                        }

                        const row = $('<tr class="editable-row editing new-item-row" data-id="new"></tr>');

                        keys.forEach((key, index) => {
                            const cls = (key === 'description') ? 'sticky-col' : '';
                            let html = buildNewItemInput(key);

                            if (key === 'no_') {
                                html = `
                        <div class="new-item-field-wrap">
                            <span class="new-item-badge">NEW</span>
                            ${html}
                        </div>
                    `;
                            }

                            if (key === 'photo') {
                                html = `
                        <div class="new-item-photo-wrap">
                            <span class="new-item-badge">NEW</span>
                            ${html}
                        </div>
                    `;
                            }

                            if (key === 'act') {
                                // ACT adalah kolom CAD/CHAT pada item lama.
                                // Pada row NEW kolom ini sengaja dikosongkan agar
                                // posisi kolom setelahnya (terutama QTY) tidak bergeser.
                                html = '<span class="new-act-placeholder">—</span>';
                            }

                            row.append(`<td class="${cls}" data-key="${key}">${html}</td>`);
                        });

                        tbody.prepend(row);

                        // Scroll kembali ke bagian paling atas agar baris baru terlihat.
                        $('.freeze-wrapper').scrollTop(0);

                        const firstInput = row.find('.new-item-input').first();
                        if (firstInput.length) {
                            firstInput.focus();
                        }
                    }

                    $(document).off('click', '#btn-add-item').on('click', '#btn-add-item', function() {
                        const username = (window.currentUsername || '').toLowerCase();
                        if (username !== 'rodiyah') {
                            alert('❌ Tidak ada akses');
                            return;
                        }
                        createNewItemRow();
                    });

                    // Browse gambar dari gallery/file picker.
                    $(document).off('click', '.btn-browse-new-image').on('click', '.btn-browse-new-image', function(e) {
                        e.preventDefault();
                        $(this).closest('.new-image-editor').find('.new-photo-file').trigger('click');
                    });

                    $(document).off('change', '.new-photo-file').on('change', '.new-photo-file', function() {
                        const file = this.files?.[0];
                        if (!file) return;
                        uploadNewItemImage(file, $(this).closest('.new-image-editor'));
                    });

                    // Upload gambar ke storage Laravel.
                    function uploadNewItemImage(file, editor) {
                        if (!file || !editor?.length) return Promise.reject(new Error('File gambar tidak valid'));

                        const formData = new FormData();
                        formData.append('image', file);

                        editor.attr('data-uploading', '1');
                        editor.find('.new-image-empty').html('<i class="fa fa-spinner fa-spin"></i><br>Uploading...');

                        return fetch('/marketing/po-detail/upload-image', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                body: formData
                            })
                            .then(async res => {
                                const data = await res.json().catch(() => ({}));
                                if (!res.ok) {
                                    throw new Error(data.message || 'Upload gambar gagal');
                                }
                                return data;
                            })
                            .then(data => {
                                const url = data.url || data.path;
                                if (!url) throw new Error('URL gambar tidak diterima server');

                                editor.find('.new-photo-value').val(url);
                                editor.find('.new-image-preview').html(`<img src="${escapeHtml(url)}" alt="Preview">`);
                                editor.attr('data-uploading', '0');
                                return url;
                            })
                            .catch(err => {
                                editor.attr('data-uploading', '0');
                                editor.find('.new-image-preview').html(`
                    <span class="new-image-empty text-danger">
                        <i class="fa fa-exclamation-triangle"></i><br>${escapeHtml(err.message)}
                    </span>
                `);
                                throw err;
                            });
                    }

                    // =====================================================
                    // PASTE IMAGE DARI EXCEL / CLIPBOARD
                    // =====================================================
                    let newItemPasteBusy = false;

                    function getActiveNewImageEditor() {
                        const focused = $(document.activeElement).closest('.new-image-editor');
                        if (focused.length) return focused;

                        const row = $('#detail-item-table tr.new-item-row');
                        return row.find('.new-image-editor.paste-target').first();
                    }

                    async function uploadClipboardImageFromBrowser(editor) {
                        if (!editor?.length || newItemPasteBusy) return false;

                        // Chrome/Edge HTTPS: Clipboard API biasanya bisa membaca
                        // image/png dari gambar yang di-copy dari Excel.
                        if (!navigator.clipboard || !navigator.clipboard.read) {
                            return false;
                        }

                        try {
                            const clipboardItems = await navigator.clipboard.read();

                            for (const clipboardItem of clipboardItems) {
                                const imageType = clipboardItem.types.find(type =>
                                    type === 'image/png' ||
                                    type === 'image/jpeg' ||
                                    type === 'image/webp'
                                );

                                if (!imageType) continue;

                                const blob = await clipboardItem.getType(imageType);
                                const extension = imageType.split('/')[1] || 'png';
                                const file = new File(
                                    [blob],
                                    `excel-paste-${Date.now()}.${extension}`, {
                                        type: imageType
                                    }
                                );

                                newItemPasteBusy = true;
                                try {
                                    await uploadNewItemImage(file, editor);
                                    editor.find('.paste-success').remove();
                                    editor.find('.new-image-actions').append(
                                        '<span class="paste-success">✓ Pasted</span>'
                                    );
                                } finally {
                                    newItemPasteBusy = false;
                                }

                                return true;
                            }
                        } catch (err) {
                            console.debug('[NEW ITEM] Clipboard API:', err);
                        }

                        return false;
                    }

                    async function handleNewItemImagePaste(event) {
                        const row = $('#detail-item-table tr.new-item-row');
                        if (!row.length) return;

                        const editor = getActiveNewImageEditor();
                        if (!editor.length) return;

                        const clipboard = event.clipboardData;
                        if (!clipboard) return;

                        let file = null;

                        // Cara pertama: ClipboardEvent langsung menyediakan image/png.
                        for (const item of clipboard.items) {
                            if (item.type && item.type.startsWith('image/')) {
                                file = item.getAsFile();
                                if (file) break;
                            }
                        }

                        // Cara kedua: Excel/browser memasukkan image sebagai HTML data URI.
                        if (!file) {
                            const html = clipboard.getData('text/html');
                            const match = html && html.match(
                                /<img[^>]+src=["']([^"']+)["']/i
                            );

                            if (match && match[1] && match[1].startsWith('data:image/')) {
                                try {
                                    const response = await fetch(match[1]);
                                    const blob = await response.blob();
                                    file = new File(
                                        [blob],
                                        `excel-paste-${Date.now()}.png`, {
                                            type: blob.type || 'image/png'
                                        }
                                    );
                                } catch (err) {
                                    console.debug('[NEW ITEM] HTML image:', err);
                                }
                            }
                        }

                        if (!file) {
                            // Jangan preventDefault kalau clipboard event tidak berisi image,
                            // supaya Ctrl+V tetap normal untuk input teks.
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        if (newItemPasteBusy) return;

                        newItemPasteBusy = true;
                        try {
                            await uploadNewItemImage(file, editor);
                            editor.find('.paste-success').remove();
                            editor.find('.new-image-actions').append(
                                '<span class="paste-success">✓ Pasted</span>'
                            );
                        } catch (err) {
                            alert('❌ ' + (err.message || 'Gagal paste gambar'));
                        } finally {
                            newItemPasteBusy = false;
                        }
                    }

                    // Klik area foto = jadikan target paste aktif.
                    $(document).off('click.newItemImageFocus', '.new-image-editor')
                        .on('click.newItemImageFocus', '.new-image-editor', function(e) {
                            if ($(e.target).closest('button,input').length) return;

                            $('.new-image-editor.paste-target').removeClass('paste-target');
                            $(this).addClass('paste-target').trigger('focus');
                        });

                    // Tombol Paste juga langsung mengaktifkan area foto dan mencoba
                    // Clipboard API dengan user gesture.
                    $(document).off('click.newItemPaste', '.btn-paste-help')
                        .on('click.newItemPaste', '.btn-paste-help', async function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const editor = $(this).closest('.new-image-editor');
                            $('.new-image-editor.paste-target').removeClass('paste-target');
                            editor.addClass('paste-target').trigger('focus');

                            const pasted = await uploadClipboardImageFromBrowser(editor);

                            if (!pasted) {
                                alert(
                                    'Gambar belum terbaca dari clipboard. ' +
                                    'Klik area PHOTO, lalu tekan Ctrl+V setelah menyalin gambar dari Excel.'
                                );
                            }
                        });

                    // Paste langsung dari Ctrl+V.
                    $(document).off('paste.newItemImage')
                        .on('paste.newItemImage', handleNewItemImagePaste);

                    // Fallback khusus Ctrl+V menggunakan Clipboard API.
                    // Ini penting pada Chrome/Edge ketika Excel tidak mengirim image
                    // sebagai ClipboardEvent image/* tetapi Clipboard API tetap bisa membacanya.
                    $(document).off('keydown.newItemPaste')
                        .on('keydown.newItemPaste', async function(e) {
                            if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 'v') return;

                            const editor = getActiveNewImageEditor();
                            if (!editor.length) return;

                            // Kalau sedang mengetik di field biasa, jangan ganggu paste teks.
                            const active = document.activeElement;
                            const isTextField = active && ['INPUT', 'TEXTAREA'].includes(active.tagName) &&
                                !$(active).closest('.new-image-editor').length;

                            if (isTextField) return;

                            const pasted = await uploadClipboardImageFromBrowser(editor);
                            if (pasted) {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                        });

                    /* =====================================================
                       SAVE - EXISTING + NEW ROW
                    ===================================================== */
                    $(document).off('click', '#btn-save-all').on('click', '#btn-save-all', function() {
                        const role = $('#role').val();
                        if (role !== 'marketing') {
                            alert('❌ Tidak ada akses');
                            return;
                        }

                        const button = $(this);
                        const newRow = $('#detail-item-table tr.new-item-row');

                        if (newRow.find('.new-image-editor[data-uploading="1"]').length) {
                            alert('Mohon tunggu upload gambar selesai.');
                            return;
                        }

                        let payload = [];
                        let newItems = [];

                        $('#detail-item-table tr.editable-row').each(function() {
                            let row = $(this);

                            // ==============================================
                            // BARIS BARU
                            // ==============================================
                            if (row.hasClass('new-item-row')) {
                                let detail = {};

                                row.find('td[data-key]').each(function() {
                                    const td = $(this);
                                    const key = td.data('key');
                                    if (!key || key === 'act') return;

                                    if (key.toLowerCase().includes('photo')) {
                                        detail[key] = td.find('.new-photo-value').val() || null;
                                        return;
                                    }

                                    const input = td.find('.new-item-input');
                                    let value = input.length ? input.val() : '';
                                    value = value === null ? '' : String(value).trim();
                                    detail[key] = value === '' ? null : value;
                                });

                                // Tidak dianggap kosong hanya karena sebagian field belum diisi.
                                // Yang penting ada minimal satu value nyata.
                                const hasValue = Object.values(detail).some(v => v !== null && String(v).trim() !== '');
                                if (hasValue) {
                                    newItems.push(detail);
                                }
                                return;
                            }

                            // ==============================================
                            // BARIS LAMA - LOGIC EXISTING TETAP
                            // ==============================================
                            let itemId = row.data('id');
                            let changedData = {};

                            row.find('td[data-key]').each(function() {
                                let td = $(this);
                                let key = td.data('key');
                                if (!key || key.toLowerCase().includes('photo')) return;
                                let newVal = td.find('input').length ?
                                    td.find('input').val().trim() :
                                    td.find('.cell-text').text().trim();
                                let oldVal = td.attr('data-original') ?? '';
                                if (newVal !== oldVal) {
                                    changedData[key] = newVal;
                                }
                            });

                            if (Object.keys(changedData).length > 0) {
                                payload.push({
                                    id: itemId,
                                    detail: changedData
                                });
                            }
                        });

                        if (!payload.length && !newItems.length) {
                            alert('Tidak ada perubahan');
                            return;
                        }

                        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                        showSaving();

                        const requests = [];

                        if (payload.length) {
                            requests.push(
                                fetch('/marketing/po-item-update-bulk', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    body: JSON.stringify({
                                        items: payload
                                    })
                                }).then(async res => {
                                    const data = await res.json().catch(() => ({}));
                                    if (!res.ok || !data.success) {
                                        throw new Error(data.message || 'Gagal menyimpan perubahan item lama');
                                    }
                                    return data;
                                })
                            );
                        }

                        newItems.forEach(detail => {
                            requests.push(
                                fetch('/marketing/po-detail/add-item', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    body: JSON.stringify({
                                        po_id: window.currentMarketingPoId,
                                        detail: detail
                                    })
                                }).then(async res => {
                                    const data = await res.json().catch(() => ({}));
                                    if (!res.ok || !data.success) {
                                        throw new Error(data.message || 'Gagal menambah item baru');
                                    }
                                    return data;
                                })
                            );
                        });

                        Promise.all(requests)
                            .then(() => {
                                showSaved();
                                $(`.btn-view[data-id="${window.currentMarketingPoId}"]`).trigger('click');
                            })
                            .catch(err => {
                                alert('❌ ' + (err.message || 'Gagal menyimpan'));
                                $('#save-status').hide();
                            })
                            .finally(() => {
                                button.prop('disabled', false).html('💾 Save All Changes');
                            });
                    });

                    /* =====================================================
                       ENTER = EXIT EDIT
                    ===================================================== */
                    $(document).off('keydown', '.inline-input').on('keydown', '.inline-input', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            exitEdit($(this).closest('tr'));
                        }
                    });

                    /* =====================================================
                       SAVE
                    ===================================================== */
                    /* =====================================================
                           ENTER = EXIT EDIT
                        ===================================================== */
                    $(document).off('keydown', '.inline-input').on('keydown', '.inline-input', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            exitEdit($(this).closest('tr'));
                        }
                    });
                    /* =====================================================
                       HELPER
                    ===================================================== */
                    function emptyTds(count) {
                        return '<td></td>'.repeat(count);
                    }
                    /* =====================================================
                       TRIGGER
                    ===================================================== */
                    $(document).ready(initPage);
                    $(document).on('pjax:end', initPage);

                    function showSaving() {

                        $('#save-status')
                            .stop(true, true)
                            .css('background', '#0d6efd')
                            .text('💾 Saving...')
                            .fadeIn(150);

                    }

                    function showSaved() {

                        $('#save-status')
                            .css('background', '#198754')
                            .text('✔ All changes saved');

                        setTimeout(function() {

                            $('#save-status').fadeOut();

                        }, 1200);

                    }
                    $(document).on('click', '#sort-order-no', function() {

                        orderSort = orderSort === 'asc' ?
                            'desc' :
                            'asc';

                        loadPoTable(
                            $('#search-qc').val(),
                            $('#filter-spk-type').val()
                        );

                    });
                </script>
            @endpush
        @endpush


        {{-- Pusher Live Chat: shared presence-upah-transaksi, independent of Upah transaction scripts. --}}
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
        <script>
            (function() {
                'use strict';
                if (window.__releaseOrderUpahPusherSetup) return;
                window.__releaseOrderUpahPusherSetup = true;

                function setup() {
                    if (window.upahRealtimeChannel) return;
                    const key = @json(config('broadcasting.connections.pusher.key'));
                    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
                    if (!key || typeof Pusher === 'undefined') {
                        console.warn('[Release Order Chat] Pusher belum dikonfigurasi.');
                        return;
                    }
                    const pusher = new Pusher(key, {
                        cluster: cluster || 'ap1',
                        forceTLS: true,
                        authEndpoint: @json(route('pusher.auth')),
                        auth: {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                    @json(csrf_token())
                            }
                        }
                    });
                    window.upahRealtimePusher = pusher;
                    window.upahRealtimeChannelName = 'presence-upah-transaksi';
                    window.upahRealtimeChannel = pusher.subscribe(window.upahRealtimeChannelName);
                }
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
                else setup();
            })();
        </script>

        <!-- =========================================================
         UPAH LIVE CHAT - WHATSAPP STYLE
         Realtime Pusher + typing indicator
         Menggunakan channel Pusher yang sama:
         presence-upah-transaksi
         Tidak mengubah logic transaksi/cursor yang sudah ada.
    ========================================================= -->
        <script>
            (function() {
                'use strict';

                let upahChatInitialized = false;
                let upahChatSubscribed = false;
                let upahTypingTimer = null;
                let upahTypingActive = false;
                let upahTypingUsers = {};

                const UPAH_CHAT_EVENT = 'client-upah-live-message';
                const UPAH_TYPING_EVENT = 'client-upah-typing';
                // Endpoint Laravel untuk menyimpan gambar hasil paste/upload.
                // Sesuaikan hanya jika route Anda menggunakan URL lain.
                const UPAH_CHAT_UPLOAD_URL = '/transaksi/upah/chat/upload-image';

                let upahPendingImage = null;
                let upahImageUploading = false;

                const currentUserId = String(@json(auth()->id()));
                const currentUserName = @json(auth()->user()->name ?? 'User');

                function getUpahChatChannel() {
                    return window.upahRealtimeChannel || null;
                }

                function escapeUpahChatHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function scrollUpahChatToBottom() {
                    const box = document.getElementById('upahChatMessages');

                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                }

                function injectUpahChatStyle() {

                    if (document.getElementById('upahChatRealtimeStyle')) {
                        return;
                    }

                    const style = document.createElement('style');
                    style.id = 'upahChatRealtimeStyle';

                    style.innerHTML = `
            #upahChatModal {
                position: fixed !important;
                inset: 0 !important;
                z-index: 99999 !important;
                display: none;
                background: rgba(0,0,0,.38);
                backdrop-filter: blur(2px);
            }

            #upahChatModal.show {
                display: block !important;
            }

            #upahChatModal .upah-chat-window {
                position: absolute;
                right: 28px;
                bottom: 28px;
                width: min(390px, calc(100vw - 30px));
                height: min(610px, calc(100vh - 50px));
                display: flex;
                flex-direction: column;
                overflow: hidden;
                background: #efeae2;
                border-radius: 18px;
                box-shadow: 0 18px 55px rgba(0,0,0,.25);
                border: 1px solid rgba(0,0,0,.08);
            }

            #upahChatModal .upah-chat-header {
                min-height: 68px;
                padding: 11px 14px;
                display: flex;
                align-items: center;
                gap: 11px;
                background: #075e54;
                color: #fff;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-avatar {
                width: 43px;
                height: 43px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,.18);
                font-size: 20px;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-title {
                flex: 1;
                min-width: 0;
            }

            #upahChatModal .upah-chat-title strong {
                display: block;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.2;
            }

            #upahChatModal .upah-chat-status {
                display: block;
                margin-top: 3px;
                font-size: 11px;
                opacity: .85;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #upahChatModal .upah-chat-close {
                width: 36px;
                height: 36px;
                border: 0;
                border-radius: 50%;
                background: transparent;
                color: #fff;
                font-size: 25px;
                line-height: 1;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #upahChatModal .upah-chat-close:hover {
                background: rgba(255,255,255,.14);
            }

            #upahChatModal .upah-chat-messages {
                flex: 1;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 16px 12px 10px;
                background-color: #efeae2;
                background-image:
                    radial-gradient(rgba(0,0,0,.035) 1px, transparent 1px);
                background-size: 18px 18px;
                scroll-behavior: smooth;
            }

            #upahChatModal .upah-chat-empty {
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: #667781;
                font-size: 12px;
                padding: 30px;
            }

            #upahChatModal .upah-chat-message {
                max-width: 82%;
                width: fit-content;
                margin-bottom: 7px;
                padding: 7px 9px 5px;
                border-radius: 9px;
                position: relative;
                word-break: break-word;
                font-size: 13px;
                line-height: 1.45;
                box-shadow: 0 1px 1px rgba(0,0,0,.08);
                clear: both;
            }

            #upahChatModal .upah-chat-message.mine {
                float: right;
                margin-left: 18%;
                background: #d9fdd3;
                border-top-right-radius: 3px;
            }

            #upahChatModal .upah-chat-message.other {
                float: left;
                margin-right: 18%;
                background: #fff;
                border-top-left-radius: 3px;
            }

            #upahChatModal .upah-chat-message-name {
                color: #075e54;
                font-size: 11px;
                font-weight: 700;
                margin-bottom: 2px;
            }

            #upahChatModal .upah-chat-message-text {
                white-space: pre-wrap;
                color: #111b21;
                padding-right: 42px;
            }

            #upahChatModal .upah-chat-message-content {
                white-space: pre-wrap !important;
                overflow-wrap: anywhere !important;
                word-break: break-word !important;
                color: #111b21 !important;
                display: block !important;
                visibility: visible !important;
            }

            #upahChatModal .upah-chat-message-meta {
                display: block !important;
                text-align: right !important;
                color: #667781 !important;
                font-size: 9px !important;
                line-height: 12px !important;
                margin-top: 3px !important;
                min-height: 12px;
            }

            #upahChatModal .upah-chat-message {
                visibility: visible !important;
                opacity: 1 !important;
            }

            #upahChatModal .upah-chat-message-time {
                float: right;
                color: #667781;
                font-size: 9px;
                margin: 4px 0 0 8px;
                line-height: 12px;
            }

            #upahChatModal .upah-chat-typing {
                min-height: 25px;
                padding: 0 13px 5px;
                background: #efeae2;
                color: #667781;
                font-size: 11px;
                font-style: italic;
                display: none;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-typing.show {
                display: block;
            }

            #upahChatModal .upah-typing-dots {
                display: inline-flex;
                gap: 2px;
                margin-left: 2px;
                vertical-align: middle;
            }

            #upahChatModal .upah-typing-dots span {
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background: #667781;
                animation: upahTypingDot 1.2s infinite ease-in-out;
            }

            #upahChatModal .upah-typing-dots span:nth-child(2) {
                animation-delay: .15s;
            }

            #upahChatModal .upah-typing-dots span:nth-child(3) {
                animation-delay: .30s;
            }

            @keyframes upahTypingDot {
                0%, 60%, 100% { transform: translateY(0); opacity: .45; }
                30% { transform: translateY(-3px); opacity: 1; }
            }

            #upahChatModal .upah-chat-input-area {
                display: flex;
                align-items: flex-end;
                gap: 7px;
                padding: 9px;
                background: #f0f2f5;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-input-wrap {
                flex: 1;
                min-width: 0;
                background: #fff;
                border-radius: 22px;
                display: flex;
                align-items: center;
                padding: 0 12px;
            }

            #upahChatModal #upahChatInput {
                width: 100%;
                min-height: 40px;
                max-height: 105px;
                resize: none;
                border: 0 !important;
                outline: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 10px 0 !important;
                font-size: 13px;
                color: #111b21;
                caret-color: #087f5b;
            }

            #upahChatModal #upahChatInput:focus {
                caret-color: #087f5b;
            }

            #upahChatModal .upah-chat-send {
                width: 42px;
                height: 42px;
                border: 0;
                border-radius: 50%;
                background: #075e54;
                color: #fff;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 17px;
                transition: transform .12s ease, opacity .12s ease;
            }

            #upahChatModal .upah-chat-send:hover {
                transform: scale(1.04);
            }

            #upahChatModal .upah-chat-send:active {
                transform: scale(.96);
            }


            #upahChatModal .upah-chat-image {
                display: block;
                max-width: 260px;
                max-height: 260px;
                width: auto;
                height: auto;
                border-radius: 8px;
                margin-top: 4px;
                cursor: pointer;
                object-fit: cover;
            }

            #upahChatModal .upah-chat-image-preview {
                display: none;
                padding: 6px 9px;
                background: #f0f2f5;
                border-top: 1px solid rgba(0,0,0,.06);
            }

            #upahChatModal .upah-chat-image-preview.show {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            #upahChatModal .upah-chat-image-preview img {
                width: 54px;
                height: 54px;
                object-fit: cover;
                border-radius: 7px;
            }

            #upahChatModal .upah-chat-image-preview-info {
                flex: 1;
                min-width: 0;
                font-size: 11px;
                color: #667781;
            }

            #upahChatModal .upah-chat-image-remove {
                border: 0;
                background: transparent;
                color: #667781;
                font-size: 20px;
                cursor: pointer;
            }

            #upahChatModal .upah-chat-attach {
                width: 38px;
                height: 38px;
                border: 0;
                background: transparent;
                color: #54656f;
                cursor: pointer;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-attach:hover {
                background: rgba(0,0,0,.06);
            }

            @media (max-width: 600px) {
                #upahChatModal .upah-chat-window {
                    right: 8px;
                    bottom: 8px;
                    width: calc(100vw - 16px);
                    height: calc(100vh - 16px);
                    border-radius: 14px;
                }
            }
        `;

                    document.head.appendChild(style);
                }

                function ensureUpahChatModal() {

                    // Jangan hanya mengecek #upahChatModal. Pada halaman Upah bisa saja
                    // sudah ada modal dengan ID tersebut tetapi isinya berasal dari Blade
                    // lama dan belum mempunyai #upahChatMessages.
                    let modal = document.getElementById('upahChatModal');

                    if (!modal) {
                        $('body').append(`
            <div id="upahChatModal" aria-hidden="true">

                <div class="upah-chat-window">

                    <div class="upah-chat-header">

                        <div class="upah-chat-avatar">
                            <i class="fas fa-comments"></i>
                        </div>

                        <div class="upah-chat-title">
                            <strong>Chat</strong>
                            <span class="upah-chat-status">
                                Live Chat
                            </span>
                        </div>

                        <button
                            type="button"
                            id="upahChatClose"
                            class="upah-chat-close"
                            aria-label="Close"
                        >
                            &times;
                        </button>

                    </div>

                    <div
                        id="upahChatMessages"
                        class="upah-chat-messages"
                    >
                        <div class="upah-chat-empty">
                            Belum ada pesan.<br>
                            Mulai percakapan dengan user lain.
                        </div>
                    </div>

                    <div
                        id="upahChatTyping"
                        class="upah-chat-typing"
                    ></div>

                    <div id="upahChatImagePreview" class="upah-chat-image-preview">
                        <img id="upahChatImagePreviewImg" src="" alt="Preview">
                        <div id="upahChatImagePreviewInfo" class="upah-chat-image-preview-info">Gambar siap dikirim</div>
                        <button type="button" id="upahChatImageRemove" class="upah-chat-image-remove" title="Hapus gambar">&times;</button>
                    </div>

                    <div class="upah-chat-input-area">

                        <input type="file" id="upahChatImageInput" accept="image/*" style="display:none;">

                        <button type="button" id="upahChatAttach" class="upah-chat-attach" title="Kirim gambar">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <div class="upah-chat-input-wrap">

                            <textarea
                                id="upahChatInput"
                                rows="1"
                                maxlength="1000"
                                placeholder="Ketik pesan..."
                                autocomplete="off"
                            ></textarea>

                        </div>

                        <button
                            type="button"
                            id="upahChatSend"
                            class="upah-chat-send"
                            title="Kirim"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>

                    </div>

                </div>

            </div>
        `);

                        modal = document.getElementById('upahChatModal');
                    }

                    // Repair modal yang sudah ada tetapi elemen chat-nya tidak lengkap.
                    if (!modal) {
                        console.error('[UPAH CHAT] Gagal membuat #upahChatModal.');
                        return;
                    }

                    if (!document.getElementById('upahChatMessages')) {
                        const windowEl = modal.querySelector('.upah-chat-window') || modal;
                        const typingEl = document.getElementById('upahChatTyping');
                        const inputArea = modal.querySelector('.upah-chat-input-area');

                        const messagesEl = document.createElement('div');
                        messagesEl.id = 'upahChatMessages';
                        messagesEl.className = 'upah-chat-messages';
                        messagesEl.innerHTML =
                            '<div class="upah-chat-empty">Belum ada pesan.<br>Mulai percakapan dengan user lain.</div>';

                        if (typingEl && typingEl.parentNode === windowEl) {
                            windowEl.insertBefore(messagesEl, typingEl);
                        } else if (inputArea && inputArea.parentNode === windowEl) {
                            windowEl.insertBefore(messagesEl, inputArea);
                        } else {
                            windowEl.appendChild(messagesEl);
                        }

                        console.log('[UPAH CHAT] #upahChatMessages diperbaiki/dibuat.');
                    }

                    // Pastikan typing area juga tersedia.
                    if (!document.getElementById('upahChatTyping')) {
                        const windowEl = modal.querySelector('.upah-chat-window') || modal;
                        const typingEl = document.createElement('div');
                        typingEl.id = 'upahChatTyping';
                        typingEl.className = 'upah-chat-typing';
                        windowEl.appendChild(typingEl);
                    }
                }

                function clearEmptyState() {
                    $('#upahChatMessages .upah-chat-empty').remove();
                }

                function scrollUpahChatToBottom() {
                    const box = document.getElementById('upahChatMessages');

                    if (box) {
                        box.scrollTop = box.scrollHeight;
                    }
                }


                function clearUpahPendingImage() {
                    upahPendingImage = null;
                    $('#upahChatImageInput').val('');
                    $('#upahChatImagePreview')
                        .removeClass('show');
                    $('#upahChatImagePreviewImg').attr('src', '');
                    $('#upahChatImagePreviewInfo').text('');
                }

                function setUpahPendingImage(file) {
                    if (!file || !file.type || !file.type.startsWith('image/')) {
                        alert('File yang dipilih harus berupa gambar.');
                        return;
                    }

                    if (file.size > 8 * 1024 * 1024) {
                        alert('Ukuran gambar maksimal 8 MB.');
                        return;
                    }

                    upahPendingImage = file;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#upahChatImagePreviewImg').attr('src', e.target.result);
                        $('#upahChatImagePreviewInfo').text(
                            file.name + ' • ' + Math.round(file.size / 1024) + ' KB'
                        );
                        $('#upahChatImagePreview').addClass('show');
                        $('#upahChatInput').trigger('focus');
                    };
                    reader.readAsDataURL(file);
                }

                async function uploadUpahChatImage(file) {
                    const csrf = $('meta[name="csrf-token"]').attr('content') || '';
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('_token', csrf);

                    const response = await fetch(UPAH_CHAT_UPLOAD_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error('Upload gambar gagal (' + response.status + '): ' + text);
                    }

                    const result = await response.json();

                    if (!result.success || !result.url) {
                        throw new Error(result.message || 'URL gambar tidak diterima dari server.');
                    }

                    return result.url;
                }

                function appendUpahChatMessage(data) {

                    console.log('[UPAH CHAT] RENDER MESSAGE:', data);

                    // Pastikan modal dan container selalu tersedia, termasuk ketika
                    // pesan diterima sebelum user menekan Ctrl+Shift+Z.
                    ensureUpahChatModal();

                    let box = document.getElementById('upahChatMessages');

                    if (!box && document.body) {
                        console.warn('[UPAH CHAT] Container pesan belum ada, membuat ulang modal.');
                        ensureUpahChatModal();
                        box = document.getElementById('upahChatMessages');
                    }

                    if (!box) {
                        console.error('[UPAH CHAT] #upahChatMessages tidak ditemukan saat render.');
                        return;
                    }

                    if (!data || typeof data !== 'object') {
                        console.warn('[UPAH CHAT] Payload message tidak valid:', data);
                        return;
                    }

                    const message = String(data.message ?? '').trim();
                    const imageUrl = String(data.image_url ?? '').trim();

                    if (!message && !imageUrl) {
                        console.warn('[UPAH CHAT] Message kosong:', data);
                        return;
                    }

                    clearEmptyState();

                    const senderId = String(data.user_id ?? '');
                    const name = escapeUpahChatHtml(data.name || 'User');
                    const safeMessage = escapeUpahChatHtml(message);
                    const safeImageUrl = escapeUpahChatHtml(imageUrl);
                    const isMine = senderId === currentUserId;

                    const timestamp = Number(data.timestamp);
                    const date = Number.isFinite(timestamp) && timestamp > 0 ?
                        new Date(timestamp) :
                        new Date();

                    const time = date.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const messageEl = document.createElement('div');
                    messageEl.className = 'upah-chat-message ' + (isMine ? 'mine' : 'other');

                    const nameEl = document.createElement('div');
                    nameEl.className = 'upah-chat-message-name';
                    nameEl.textContent = data.name || 'User';
                    messageEl.appendChild(nameEl);

                    if (message) {
                        const textEl = document.createElement('div');
                        textEl.className = 'upah-chat-message-content';
                        textEl.textContent = message;
                        messageEl.appendChild(textEl);
                    }

                    if (imageUrl) {
                        const image = document.createElement('img');
                        image.className = 'upah-chat-image';
                        image.src = imageUrl;
                        image.alt = 'Gambar';
                        image.loading = 'lazy';
                        image.addEventListener('click', function() {
                            window.open(imageUrl, '_blank', 'noopener');
                        });
                        messageEl.appendChild(image);
                    }

                    const meta = document.createElement('div');
                    meta.className = 'upah-chat-message-meta';
                    meta.textContent = time + (isMine ? '  ✓✓' : '');
                    messageEl.appendChild(meta);

                    box.appendChild(messageEl);

                    // Paksa browser melakukan layout sebelum scroll.
                    requestAnimationFrame(function() {
                        box.scrollTop = box.scrollHeight;
                    });

                    console.log(
                        '[UPAH CHAT] MESSAGE RENDERED:', {
                            senderId: senderId,
                            mine: isMine,
                            message: message
                        }
                    );
                }

                function renderUpahTyping() {

                    const typingBox = $('#upahChatTyping');

                    if (!typingBox.length) {
                        return;
                    }

                    const now = Date.now();

                    Object.keys(upahTypingUsers).forEach(function(userId) {

                        if (
                            now - upahTypingUsers[userId].lastSeen >
                            3500
                        ) {
                            delete upahTypingUsers[userId];
                        }
                    });

                    const names = Object.keys(upahTypingUsers)
                        .map(function(id) {
                            return upahTypingUsers[id].name;
                        })
                        .filter(Boolean);

                    if (!names.length) {

                        typingBox
                            .removeClass('show')
                            .html('');

                        return;
                    }

                    let text;

                    if (names.length === 1) {

                        text = escapeUpahChatHtml(names[0]) +
                            ' sedang mengetik';

                    } else if (names.length === 2) {

                        text =
                            escapeUpahChatHtml(names[0]) +
                            ' dan ' +
                            escapeUpahChatHtml(names[1]) +
                            ' sedang mengetik';

                    } else {

                        text =
                            names.length +
                            ' orang sedang mengetik';
                    }

                    typingBox
                        .addClass('show')
                        .html(`
                ${text}
                <span class="upah-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            `);
                }

                function receiveTyping(data) {

                    if (!data) {
                        return;
                    }

                    const userId = String(data.user_id ?? '');

                    if (!userId || userId === currentUserId) {
                        return;
                    }

                    if (data.typing === false) {

                        delete upahTypingUsers[userId];

                    } else {

                        upahTypingUsers[userId] = {
                            name: data.name || 'User',
                            lastSeen: Date.now()
                        };
                    }

                    renderUpahTyping();
                }

                function sendTypingState(isTyping) {

                    const channel = getUpahChatChannel();

                    if (!channel || !upahChatSubscribed) {
                        return;
                    }

                    try {

                        channel.trigger(UPAH_TYPING_EVENT, {
                            user_id: currentUserId,
                            name: currentUserName,
                            typing: !!isTyping,
                            timestamp: Date.now()
                        });

                    } catch (error) {

                        console.warn(
                            '[UPAH CHAT] typing trigger gagal:',
                            error
                        );
                    }
                }

                function stopTyping() {

                    if (!upahTypingActive) {
                        return;
                    }

                    upahTypingActive = false;

                    if (upahTypingTimer) {
                        clearTimeout(upahTypingTimer);
                        upahTypingTimer = null;
                    }

                    sendTypingState(false);
                }

                function handleTypingInput() {

                    if (!upahChatSubscribed) {
                        return;
                    }

                    if (!upahTypingActive) {

                        upahTypingActive = true;

                        sendTypingState(true);
                    }

                    if (upahTypingTimer) {
                        clearTimeout(upahTypingTimer);
                    }

                    upahTypingTimer = setTimeout(function() {

                        stopTyping();

                    }, 1800);
                }

                function bindUpahChat() {

                    const channel = getUpahChatChannel();

                    if (!channel) {
                        return false;
                    }

                    if (upahChatInitialized) {
                        return true;
                    }

                    console.log(
                        '[UPAH CHAT] Binding channel:',
                        window.upahRealtimeChannelName || channel.name
                    );

                    /*
                     * PENTING:
                     * subscription_succeeded menentukan bahwa client benar-benar
                     * sudah masuk ke presence-upah-transaksi.
                     */
                    channel.bind(
                        'pusher:subscription_succeeded',
                        function(members) {

                            upahChatSubscribed = true;

                            console.log(
                                '[UPAH CHAT] Pusher subscription READY:',
                                window.upahRealtimeChannelName || channel.name,
                                'members:',
                                members ? members.count : '?'
                            );

                            const status = $('#upahChatModal .upah-chat-status');

                            if (status.length) {
                                status.text('online • Live Chat');
                            }
                        }
                    );

                    /*
                     * MESSAGE
                     */
                    channel.bind(
                        UPAH_CHAT_EVENT,
                        function(data) {

                            console.log(
                                '[UPAH CHAT] MESSAGE RECEIVED:',
                                data
                            );

                            appendUpahChatMessage(data);

                            // AUTO OPEN: pesan dari user lain langsung membuka chat.
                            // Gunakan currentUserId (variabel yang memang tersedia di chat).
                            if (String(data.user_id) !== String(currentUserId)) {
                                setTimeout(function() {
                                    if (typeof window.openUpahChat === 'function') {
                                        window.openUpahChat();
                                    } else {
                                        // Fallback jika fungsi global belum siap.
                                        ensureUpahChatModal();
                                        const modal = $('#upahChatModal');
                                        if (modal.length) {
                                            modal.addClass('show')
                                                .css('display', 'block')
                                                .attr('aria-hidden', 'false');
                                            setTimeout(function() {
                                                $('#upahChatInput').trigger('focus');
                                                scrollUpahChatToBottom();
                                            }, 50);
                                        }
                                    }
                                }, 50);
                            }
                        }
                    );

                    /*
                     * TYPING
                     */
                    channel.bind(
                        UPAH_TYPING_EVENT,
                        function(data) {

                            console.log(
                                '[UPAH CHAT] TYPING RECEIVED:',
                                data
                            );

                            receiveTyping(data);
                        }
                    );

                    /*
                     * DEBUG ERROR
                     */
                    channel.bind(
                        'pusher:subscription_error',
                        function(status) {

                            upahChatSubscribed = false;

                            console.error(
                                '[UPAH CHAT] Subscription error:',
                                status
                            );
                        }
                    );

                    upahChatInitialized = true;

                    console.log(
                        '[UPAH CHAT] Event listener berhasil dipasang.'
                    );

                    return true;
                }

                window.sendUpahChat = async function() {

                    const input = $('#upahChatInput');

                    if (!input.length) {
                        return;
                    }

                    const message = String(
                        input.val() || ''
                    ).trim();

                    if (!message && !upahPendingImage) {
                        return;
                    }

                    const channel = getUpahChatChannel();

                    if (!channel) {
                        console.error(
                            '[UPAH CHAT] Channel Pusher tidak ditemukan.'
                        );
                        return;
                    }

                    if (!upahChatSubscribed) {
                        console.warn(
                            '[UPAH CHAT] Channel belum subscribed. Pesan tidak dikirim.'
                        );
                        return;
                    }

                    if (upahImageUploading) {
                        return;
                    }

                    stopTyping();

                    let imageUrl = '';

                    try {

                        if (upahPendingImage) {

                            upahImageUploading = true;

                            $('#upahChatSend')
                                .prop('disabled', true)
                                .css('opacity', '.6');

                            $('#upahChatImagePreviewInfo')
                                .text('Mengunggah gambar...');

                            imageUrl = await uploadUpahChatImage(
                                upahPendingImage
                            );
                        }

                        const data = {
                            user_id: currentUserId,
                            name: currentUserName,
                            message: message,
                            image_url: imageUrl,
                            timestamp: Date.now()
                        };

                        console.log(
                            '[UPAH CHAT] SEND:',
                            data
                        );

                        channel.trigger(
                            UPAH_CHAT_EVENT,
                            data
                        );

                        appendUpahChatMessage(data);

                        input
                            .val('')
                            .css('height', 'auto')
                            .trigger('focus');

                        clearUpahPendingImage();

                    } catch (error) {

                        console.error(
                            '[UPAH CHAT] Kirim/upload gagal:',
                            error
                        );

                        alert(
                            'Gagal mengirim pesan/gambar. Periksa route upload gambar.'
                        );

                    } finally {

                        upahImageUploading = false;

                        $('#upahChatSend')
                            .prop('disabled', false)
                            .css('opacity', '');
                    }
                };

                window.openUpahChat = function() {

                    ensureUpahChatModal();

                    const modal = $('#upahChatModal');

                    if (!modal.length) {
                        return;
                    }

                    modal
                        .addClass('show')
                        .css('display', 'block')
                        .attr('aria-hidden', 'false');

                    // Fokus ke input chat dan posisikan caret (kursor berkedip)
                    // di akhir teks, termasuk setelah dialog selesai dirender.
                    function focusLiveChatCaret() {
                        if (!modal.hasClass('show')) return;
                        const input = document.getElementById('upahChatInput');
                        if (!input) return;
                        input.focus({
                            preventScroll: true
                        });
                        const end = input.value.length;
                        try {
                            input.setSelectionRange(end, end);
                        } catch (err) {}
                        scrollUpahChatToBottom();
                    }

                    requestAnimationFrame(focusLiveChatCaret);
                    setTimeout(focusLiveChatCaret, 80);
                    setTimeout(function() {
                        // Jangan merebut fokus jika user sudah pindah ke kontrol lain.
                        if (modal.hasClass('show') &&
                            (!modal[0].contains(document.activeElement) ||
                                document.activeElement === document.body)) {
                            focusLiveChatCaret();
                        }
                    }, 250);
                };

                window.closeUpahChat = function() {

                    const modal = $('#upahChatModal');

                    if (!modal.length) {
                        return;
                    }

                    stopTyping();

                    modal
                        .removeClass('show')
                        .css('display', '')
                        .attr('aria-hidden', 'true');

                    $('#upahChatInput').val('');
                };


                /*
                 * Attachment button.
                 */
                $(document).on(
                    'click',
                    '#upahChatAttach',
                    function(e) {
                        e.preventDefault();
                        $('#upahChatImageInput').trigger('click');
                    }
                );

                $(document).on(
                    'change',
                    '#upahChatImageInput',
                    function() {
                        if (this.files && this.files[0]) {
                            setUpahPendingImage(this.files[0]);
                        }
                    }
                );

                $(document).on(
                    'click',
                    '#upahChatImageRemove',
                    function(e) {
                        e.preventDefault();
                        clearUpahPendingImage();
                        $('#upahChatInput').trigger('focus');
                    }
                );

                /*
                 * WhatsApp-style paste image from clipboard.
                 * Ctrl+V screenshot / copied image => preview => Send.
                 */
                $(document).on(
                    'paste',
                    '#upahChatInput',
                    function(e) {

                        const clipboard = e.originalEvent && e.originalEvent.clipboardData;

                        if (!clipboard || !clipboard.items) {
                            return;
                        }

                        for (let i = 0; i < clipboard.items.length; i++) {

                            const item = clipboard.items[i];

                            if (item.type && item.type.indexOf('image/') === 0) {

                                const file = item.getAsFile();

                                if (file) {
                                    e.preventDefault();
                                    setUpahPendingImage(file);
                                }

                                return;
                            }
                        }
                    }
                );

                function initUpahChat() {

                    injectUpahChatStyle();
                    ensureUpahChatModal();

                    if (bindUpahChat()) {
                        return;
                    }

                    let attempts = 0;

                    const timer = setInterval(function() {

                        attempts++;

                        if (bindUpahChat()) {

                            clearInterval(timer);
                            return;
                        }

                        if (attempts >= 80) {

                            clearInterval(timer);

                            console.error(
                                '[UPAH CHAT] Channel Pusher tidak ditemukan setelah 20 detik.'
                            );
                        }

                    }, 250);
                }

                /*
                 * Ctrl + Shift + Z
                 */
                window.addEventListener(
                    'keydown',
                    function(e) {

                        if (
                            e.ctrlKey &&
                            e.shiftKey &&
                            e.code === 'KeyZ'
                        ) {

                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();

                            window.openUpahChat();

                            return false;
                        }

                        if (
                            e.key === 'Escape' &&
                            $('#upahChatModal').hasClass('show')
                        ) {

                            e.preventDefault();

                            window.closeUpahChat();
                        }

                    },
                    true
                );

                /*
                 * Close
                 */
                $(document).on(
                    'click',
                    '#upahChatClose',
                    function(e) {

                        e.preventDefault();
                        e.stopPropagation();

                        window.closeUpahChat();
                    }
                );

                /*
                 * Send
                 */
                $(document).on(
                    'click',
                    '#upahChatSend',
                    function(e) {

                        e.preventDefault();
                        e.stopPropagation();

                        window.sendUpahChat();
                    }
                );

                /*
                 * Input:
                 * - Enter = send
                 * - Shift + Enter = new line
                 * - Ctrl + Enter = send
                 */
                $(document).on(
                    'keydown',
                    '#upahChatInput',
                    function(e) {

                        if (
                            e.key === 'Enter' &&
                            !e.shiftKey
                        ) {

                            e.preventDefault();

                            window.sendUpahChat();

                            return;
                        }

                        if (
                            e.key === 'Enter' &&
                            e.ctrlKey
                        ) {

                            e.preventDefault();

                            window.sendUpahChat();
                        }
                    }
                );

                /*
                 * Detect typing realtime.
                 */
                $(document).on(
                    'input',
                    '#upahChatInput',
                    function() {

                        const value = String(
                            $(this).val() || ''
                        ).trim();

                        if (value.length > 0) {

                            handleTypingInput();

                        } else {

                            stopTyping();
                        }

                        /*
                         * Auto resize textarea seperti WhatsApp.
                         */
                        this.style.height = 'auto';
                        this.style.height =
                            Math.min(this.scrollHeight, 105) + 'px';
                    }
                );

                /*
                 * Jika pindah tab / window, kirim typing=false.
                 */
                $(window).on(
                    'blur',
                    function() {
                        stopTyping();
                    }
                );

                $(document).ready(function() {

                    initUpahChat();

                    /*
                     * Refresh typing indicator setiap 1 detik
                     * supaya user otomatis hilang setelah timeout.
                     */
                    setInterval(function() {
                        renderUpahTyping();
                    }, 1000);
                });

            })();
        </script>

        {{-- Remote mouse cursors: same Pusher event/channel as Transaksi Upah. --}}
        <style>
            #releaseRemoteCursors {
                position: fixed;
                inset: 0;
                pointer-events: none;
                z-index: 9999999;
            }

            .release-remote-cursor {
                position: fixed;
                pointer-events: none;
                transform: translate(-1px, -1px);
                transition: left 90ms linear, top 90ms linear;
                will-change: left, top;
            }

            .release-remote-cursor-arrow {
                width: 0;
                height: 0;
                border-bottom: 15px solid transparent;
                border-left: 11px solid #2563eb;
                transform: rotate(-42deg);
                filter: drop-shadow(0 1px 1px rgba(0, 0, 0, .25));
            }

            .release-remote-cursor-name {
                position: absolute;
                left: 9px;
                top: 12px;
                padding: 3px 7px;
                border-radius: 4px;
                background: #2563eb;
                color: #fff;
                font-size: 10px;
                font-weight: 700;
                line-height: 1.2;
                white-space: nowrap;
                box-shadow: 0 2px 5px rgba(0, 0, 0, .18);
            }

            .release-remote-cursor.is-idle {
                opacity: .45;
            }
        </style>
        <div id="releaseRemoteCursors" aria-hidden="true"></div>
        <script>
            (function() {
                'use strict';
                // Share the existing Pusher connection created for the chat; never subscribe twice.
                const myId = @json(auth()->id());
                const myName = @json(auth()->user()->name ?? 'User');
                const root = document.getElementById('releaseRemoteCursors');
                if (!myId || !root) return;
                const cursors = new Map();
                let channel = null,
                    attached = false,
                    lastSent = 0,
                    lastX = null,
                    lastY = null;

                function remove(id) {
                    const key = String(id),
                        state = cursors.get(key);
                    if (state) {
                        state.el.remove();
                        cursors.delete(key);
                    }
                }

                function update(data) {
                    if (!data || data.user_id == null || String(data.user_id) === String(myId)) return;
                    const x = Number(data.x),
                        y = Number(data.y);
                    if (!Number.isFinite(x) || !Number.isFinite(y)) return;
                    const id = String(data.user_id);
                    let state = cursors.get(id);
                    if (!state) {
                        const el = document.createElement('div');
                        el.className = 'release-remote-cursor';
                        const arrow = document.createElement('div');
                        arrow.className = 'release-remote-cursor-arrow';
                        const name = document.createElement('div');
                        name.className = 'release-remote-cursor-name';
                        el.append(arrow, name);
                        root.appendChild(el);
                        state = {
                            el,
                            name,
                            lastMove: 0
                        };
                        cursors.set(id, state);
                    }
                    state.name.textContent = String(data.name || 'User');
                    state.el.style.left = x + 'px';
                    state.el.style.top = y + 'px';
                    state.lastMove = Date.now();
                    state.el.classList.remove('is-idle');
                }

                function onMove(event) {
                    if (!channel || !channel.subscribed) return;
                    const now = Date.now(),
                        x = event.clientX,
                        y = event.clientY;
                    if (now - lastSent < 150) return;
                    if (lastX !== null && Math.hypot(x - lastX, y - lastY) < 5) return;
                    lastSent = now;
                    lastX = x;
                    lastY = y;
                    try {
                        channel.trigger('client-upah-cursor', {
                            user_id: myId,
                            name: myName,
                            x,
                            y
                        });
                    } catch (err) {
                        console.warn('[RELEASE ORDER Cursor] Send failed', err);
                    }
                }

                function attach() {
                    if (attached) return true;
                    const shared = window.upahRealtimeChannel;
                    if (!shared || typeof shared.bind !== 'function') return false;
                    channel = shared;
                    attached = true;
                    channel.bind('client-upah-cursor', update);
                    channel.bind('pusher:member_removed', function(member) {
                        if (member) remove(member.id);
                    });
                    document.addEventListener('mousemove', onMove, {
                        passive: true
                    });
                    console.log('[RELEASE ORDER Cursor] Attached to', channel.name);
                    return true;
                }
                if (!attach()) {
                    let attempts = 0;
                    const wait = setInterval(function() {
                        if (attach() || ++attempts >= 100) clearInterval(wait);
                    }, 100);
                }
                const idle = setInterval(function() {
                    const now = Date.now();
                    cursors.forEach(function(state) {
                        state.el.classList.toggle('is-idle', now - state.lastMove > 5000);
                    });
                }, 1000);
                window.addEventListener('beforeunload', function() {
                    clearInterval(idle);
                    document.removeEventListener('mousemove', onMove);
                    if (channel) channel.unbind('client-upah-cursor', update);
                });
            })();
        </script>
    @endsection
