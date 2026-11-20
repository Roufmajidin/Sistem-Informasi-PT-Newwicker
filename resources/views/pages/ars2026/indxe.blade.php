@extends('master.master')
@section('content')
    <div id="ars2026-app" class="ars-wrap">
        <div class="ars-heading">
            <div>
                <h2 class="mt-4">ARS 2026</h2>
                <p>Account Receivable Shipment · Editable seperti Excel · Tersimpan di database</p>
            </div>
            <div class="ars-toolbar"><label class="ars-mode-control" for="ars-edit-mode"><input type="checkbox"
                        id="ars-edit-mode" role="switch" aria-label="Aktifkan mode edit"><span
                        class="ars-mode-track"></span><span id="ars-mode-label">Mode Lihat</span></label><button
                    type="button" id="ars-export">Export CSV</button><span id="ars-status" role="status"></span></div>
        </div>
        <div class="ars-filters"><input id="ars-search" type="search"
                placeholder="Cari pelanggan, PO, invoice, PEB..."><select id="ars-month">
                <option value="">Semua bulan</option>
            </select><span id="ars-count"></span></div>
        <div class="ars-excel-tools"><strong>Tampilan Excel</strong><button type="button"
                id="ars-zoom-out">−</button><input type="range" id="ars-zoom-range" min="60" max="160"
                step="10" value="100"><button type="button" id="ars-zoom-in">+</button><button type="button"
                id="ars-zoom-reset">100%</button><span id="ars-zoom-value">100%</span><small>Ctrl+V = paste dari Excel ·
                Shift + scroll = horizontal · Ctrl + scroll = zoom</small></div>
        <div class="ars-scroll" id="ars-scroll" tabindex="0">
            <table class="ars-table" id="ars-table">
                <colgroup>
                    <col style="width:48px">
                    <col style="width:210px">
                    <col style="width:165px">
                    <col style="width:235px">
                    <col style="width:145px">
                    <col style="width:185px">
                    <col style="width:130px">
                    <col style="width:145px">
                    <col style="width:145px">
                    <col style="width:165px">
                    <col style="width:115px">
                    <col style="width:185px">
                    <col style="width:145px">
                    <col style="width:150px">
                    <col style="width:145px">
                    <col style="width:150px">
                    <col style="width:155px">
                    <col style="width:220px">
                    <col style="width:220px">
                    <col style="width:90px">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2" class="ars-sticky-no">No</th>
                        <th rowspan="2" class="ars-sticky-name">Nama Pelanggan</th>
                        <th rowspan="2" class="ars-sticky-po">No. PO</th>
                        <th rowspan="2">No. Invoice</th>
                        <th rowspan="2">Tanggal Shipment</th>
                        <th rowspan="2">No. Pengajuan PEB</th>
                        <th rowspan="2">No. PEB</th>
                        <th rowspan="2">FOB (USD)</th>
                        <th rowspan="2">FOB PEB (USD)</th>
                        <th rowspan="2">KURS KEMENKEU (Rp)</th>
                        <th rowspan="2">JUMLAH CONTAINER</th>
                        <th rowspan="2">Jumlah (Rp)</th>
                        <th colspan="2">Deposit / Uang Muka</th>
                        <th colspan="2">Pelunasan</th>
                        <th rowspan="2">Sisa Piutang (USD)</th>
                        <th rowspan="2">Keterangan</th>
                        <th rowspan="2">Remark</th>
                        <th rowspan="2">Aksi</th>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah (USD)</th>
                        <th>Tanggal</th>
                        <th>Jumlah (USD)</th>
                    </tr>
                </thead>
                <tbody id="ars-body"></tbody>
                <tfoot id="ars-foot"></tfoot>
            </table>
        </div>
        <div class="ars-bottom">
            <div class="ars-card">
                <h3>TOTAL 2026 <small>Rekap berdasarkan <span id="ars-summary-basis">Bulan ARS</span></small></h3>
                <table class="ars-summary">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Accounting IDR</th>
                            <th>TOTAL USD 2026</th>
                        </tr>
                    </thead>
                    <tbody id="ars-summary-body"></tbody>
                    <tfoot id="ars-summary-foot"></tfoot>
                </table>
            </div>

        </div>
    </div>
    <style>
        .ars-wrap {
            font-family: Inter, Arial, sans-serif;
            color: #263238;
            padding: 16px;
            background: #f6f7f8;
            min-height: 85vh
        }

        .ars-heading {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 13px
        }

        .ars-heading h2 {
            font-size: 21px;
            font-weight: 700;
            margin: 0 0 3px
        }

        .ars-heading p {
            font-size: 12px;
            color: #65717d;
            margin: 0
        }

        .ars-toolbar {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap
        }

        .ars-toolbar button {
            background: #303b45;
            color: white;
            border: 1px solid #303b45;
            border-radius: 5px;
            padding: 8px 11px;
            font-size: 12px;
            cursor: pointer
        }

        .ars-toolbar button:disabled {
            opacity: .5;
            cursor: wait
        }

        #ars-status {
            font-size: 12px;
            color: #475569
        }

        .ars-filters {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap
        }

        .ars-filters input,
        .ars-filters select {
            padding: 9px;
            border: 1px solid #cbd1d6;
            border-radius: 5px;
            background: white;
            font-size: 12px
        }

        .ars-filters input {
            min-width: 270px;
            flex: 1
        }

        .ars-filters span {
            font-size: 12px;
            color: #64748b
        }

        .ars-excel-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            background: white;
            border: 1px solid #cdd2d8;
            border-bottom: 0;
            border-radius: 5px 5px 0 0;
            padding: 7px 10px;
            font-size: 12px
        }

        .ars-excel-tools button {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: white;
            color: #263238;
            min-width: 30px;
            height: 28px;
            padding: 0 8px;
            font-weight: 600;
            cursor: pointer
        }

        .ars-excel-tools input {
            width: 125px
        }

        .ars-excel-tools small {
            margin-left: auto;
            color: #64748b
        }

        .ars-scroll {
            max-width: 100%;
            overflow: auto;
            max-height: 570px;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            background: white;
            border: 1px solid #cdd2d8;
            border-radius: 0 0 5px 5px;
            scrollbar-color: #9ca3af #e5e7eb
        }

        .ars-table {
            zoom: 1;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 2500px;
            width: max-content;
            font-size: 11px
        }

        .ars-table th,
        .ars-table td {
            border-right: 1px solid #d9dfe5;
            border-bottom: 1px solid #d9dfe5;
            padding: 0;
            white-space: nowrap
        }

        .ars-table th {
            background: #323d47;
            color: white;
            font-weight: 600;
            padding: 10px 9px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 4
        }

        .ars-table thead tr:nth-child(2) th {
            top: 35px
        }

        .ars-table tbody tr:hover td {
            background: #f7f9fa
        }

        .ars-table td input {
            font: inherit;
            color: #253341;
            border: 0;
            background: transparent;
            padding: 9px 7px;
            width: 100%;
            min-width: 0;
            outline: none;
            box-sizing: border-box
        }

        .ars-table td input:focus {
            background: #fff;
            box-shadow: inset 0 0 0 2px #6a7885
        }

        .ars-table td input[type=date] {
            min-width: 120px
        }

        .ars-table td input[data-field=customer_name] {
            min-width: 160px
        }

        .ars-table td input[data-field=description],
        .ars-table td input[data-field=remark] {
            min-width: 150px
        }

        .ars-table td input[data-type=number] {
            min-width: 115px;
            text-align: right
        }

        .ars-table .ars-num {
            text-align: right;
            padding: 9px 8px;
            background: #f8fafb;
            font-variant-numeric: tabular-nums;
            min-width: 110px
        }

        .ars-table .ars-sticky-no {
            position: sticky;
            left: 0;
            z-index: 5;
            min-width: 45px
        }

        .ars-table .ars-sticky-name {
            position: sticky;
            left: 45px;
            z-index: 5;
            min-width: 170px
        }

        .ars-table tbody .ars-sticky-no,
        .ars-table tbody .ars-sticky-name {
            background: white
        }

        .ars-table tbody .ars-sticky-no {
            text-align: center
        }

        .ars-table .ars-delete {
            border: 0;
            background: transparent;
            color: #9c3e3e;
            cursor: pointer;
            padding: 8px
        }

        .ars-table .ars-delete:disabled {
            opacity: .4
        }

        .ars-table tr.ars-month-total td {
            background: #e8ebee !important;
            font-weight: 700;
            padding: 9px 8px
        }

        .ars-table tfoot td {
            background: #dce2e7;
            font-weight: 700;
            padding: 10px 8px
        }

        .ars-bottom {
            display: grid;
            grid-template-columns: minmax(400px, 630px) minmax(230px, 1fr);
            gap: 15px;
            margin-top: 16px;
            align-items: start
        }

        .ars-card {
            border: 1px solid #d7dce0;
            border-radius: 6px;
            background: white;
            padding: 14px
        }

        .ars-card h3 {
            font-size: 14px;
            text-align: center;
            margin: 0 0 10px
        }

        .ars-card h3 small {
            display: block;
            color: #73808b;
            font-weight: 400;
            font-size: 11px;
            margin-top: 3px
        }

        .ars-summary {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px
        }

        .ars-summary th,
        .ars-summary td {
            border: 1px solid #d7dce0;
            padding: 7px 9px
        }

        .ars-summary th {
            background: #e8ecef;
            text-align: center
        }

        .ars-summary td:not(:first-child) {
            text-align: right;
            font-variant-numeric: tabular-nums
        }

        .ars-summary tfoot {
            background: #e8ecef;
            font-weight: 700;
            font-size: 13px
        }

        .ars-help h3 {
            text-align: left
        }

        .ars-help p {
            font-size: 12px;
            line-height: 1.7;
            color: #52606d;
            margin: 7px 0
        }

        @media(max-width:850px) {
            .ars-bottom {
                grid-template-columns: 1fr
            }

            .ars-wrap {
                padding: 10px
            }
        }

        .ars-table td.ars-usd-cell,
        .ars-table td.ars-idr-cell {
            position: relative
        }

        .ars-table td.ars-usd-cell::before,
        .ars-table td.ars-idr-cell::before {
            position: absolute;
            left: 7px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            pointer-events: none;
            z-index: 1
        }

        .ars-table td.ars-usd-cell::before {
            content: "$"
        }

        .ars-table td.ars-idr-cell::before {
            content: "Rp"
        }

        .ars-table td.ars-usd-cell input,
        .ars-table td.ars-idr-cell input {
            padding-left: 27px;
            min-width: 138px;
            font-variant-numeric: tabular-nums
        }

        .ars-table td.ars-idr-cell input {
            padding-left: 30px
        }

        .ars-table tr.ars-month-total td {
            background: #e5edf6 !important;
            border-top: 2px solid #8196aa;
            border-bottom: 2px solid #8196aa;
            color: #20354a;
            font-weight: 700
        }

        .ars-table tr.ars-month-total td.ars-total-title {
            text-align: left;
            padding-left: 12px;
            letter-spacing: .1px
        }

        .ars-table tr.ars-grand-total td {
            background: #d5e3f0 !important;
            font-weight: 800;
            border-top: 2px solid #334155
        }

        .ars-table tr.ars-month-total td.ars-num {
            background: #e5edf6 !important
        }

        .ars-table tr.ars-grand-total td.ars-num {
            background: #d5e3f0 !important
        }

        /* Status autosave tepat pada sel yang sedang diedit */
        #ars-body td:has(> input[data-field]) {
            position: relative
        }

        #ars-body td.ars-editing {
            z-index: 8
        }

        #ars-body td .ars-save-tip {
            position: absolute;
            right: 3px;
            bottom: calc(100% + 5px);
            padding: 5px 9px;
            border-radius: 5px;
            background: #334155;
            color: white;
            font-size: 11px;
            line-height: 1.3;
            font-weight: 600;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 3px 10px #0002;
            z-index: 20;
            display: none
        }

        #ars-body td .ars-save-tip::after {
            content: "";
            position: absolute;
            right: 12px;
            top: 100%;
            border: 5px solid transparent;
            border-top-color: #334155
        }

        #ars-body td.ars-editing .ars-save-tip {
            display: block
        }

        #ars-body td.ars-editing[data-save-state="saved"] .ars-save-tip {
            background: #167647
        }

        #ars-body td.ars-editing[data-save-state="saved"] .ars-save-tip::after {
            border-top-color: #167647
        }

        #ars-body td.ars-editing[data-save-state="error"] .ars-save-tip {
            background: #b91c1c
        }

        #ars-body td.ars-editing[data-save-state="error"] .ars-save-tip::after {
            border-top-color: #b91c1c
        }

        #ars-body td.ars-editing[data-save-state="saving"] .ars-save-tip {
            background: #2563eb
        }

        #ars-body td.ars-editing[data-save-state="saving"] .ars-save-tip::after {
            border-top-color: #2563eb
        }

        .ars-mode-control {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: #fff;
            border: 1px solid #cbd5e1;
            padding: 6px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            user-select: none
        }

        .ars-mode-control input {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px
        }

        .ars-mode-track {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
            background: #94a3b8;
            border-radius: 999px;
            transition: background .2s;
            flex-shrink: 0
        }

        .ars-mode-track::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            top: 3px;
            left: 3px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 1px 3px #0003;
            transition: transform .2s
        }

        .ars-mode-control input:checked+.ars-mode-track {
            background: #15803d
        }

        .ars-mode-control input:checked+.ars-mode-track::after {
            transform: translateX(16px)
        }

        .ars-mode-control input:focus-visible+.ars-mode-track {
            outline: 2px solid #2563eb;
            outline-offset: 3px
        }

        .ars-view-mode #ars-body input[data-field] {
            cursor: default;
            color: #334155
        }

        .ars-view-mode #ars-body input[data-field]:focus {
            box-shadow: none;
            background: transparent
        }

        .ars-view-mode #ars-body .ars-delete {
            display: none
        }

        .ars-toolbar #ars-add:disabled {
            opacity: .4;
            cursor: not-allowed
        }

        /* FIX: exact grid width, no sticky overlap or input overflow */
        #ars-table.ars-table {
            table-layout: fixed;
            width: 3148px !important;
            min-width: 3148px !important;
            max-width: 3148px !important;
            border-collapse: separate;
            border-spacing: 0
        }

        #ars-table.ars-table th,
        #ars-table.ars-table td {
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis
        }

        #ars-table.ars-table td input {
            display: block;
            width: 100%;
            min-width: 0 !important;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis
        }

        #ars-table.ars-table td input[type="date"] {
            min-width: 0 !important
        }

        #ars-table.ars-table .ars-sticky-no {
            left: 0 !important;
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            z-index: 6
        }

        #ars-table.ars-table .ars-sticky-name {
            left: 48px !important;
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            z-index: 6
        }

        #ars-table.ars-table thead .ars-sticky-no,
        #ars-table.ars-table thead .ars-sticky-name {
            z-index: 11
        }

        #ars-table.ars-table tbody .ars-sticky-name {
            box-shadow: 2px 0 3px #33415518
        }

        #ars-table.ars-table tbody tr.ars-month-total td {
            background: #dbe8f5 !important;
            color: #142f4a !important;
            border-top: 2px solid #526b85 !important;
            border-bottom: 2px solid #526b85 !important;
            font-weight: 800 !important;
            padding: 11px 9px !important;
            overflow: visible
        }

        #ars-table.ars-table tbody tr.ars-month-total td.ars-total-title {
            text-align: left !important;
            letter-spacing: .2px !important
        }

        #ars-table.ars-table tbody tr.ars-month-total td.ars-num {
            background: #dbe8f5 !important
        }

        #ars-table.ars-table tfoot tr.ars-grand-total td {
            background: #c5d8e9 !important;
            border-top: 2px solid #31465b !important;
            font-weight: 800 !important
        }

        #ars-table.ars-table tbody tr.ars-data-row td {
            height: 34px
        }

        #ars-table.ars-table td.ars-usd-cell,
        #ars-table.ars-table td.ars-idr-cell {
            position: relative
        }

        #ars-table.ars-table td.ars-usd-cell input,
        #ars-table.ars-table td.ars-idr-cell input {
            padding-left: 30px !important;
            padding-right: 7px !important
        }

        #ars-table.ars-table td.ars-usd-cell::before,
        #ars-table.ars-table td.ars-idr-cell::before {
            z-index: 2
        }

        #ars-table.ars-table td.ars-editing {
            overflow: visible;
            z-index: 12
        }

        /* Alignment final: lebar kolom sesuai colgroup (total 3148px). */
        #ars2026-app #ars-table {
            table-layout: fixed !important;
            width: 3148px !important;
            min-width: 3148px !important;
            max-width: 3148px !important
        }

        #ars2026-app #ars-table th,
        #ars2026-app #ars-table td {
            box-sizing: border-box !important;
            min-width: 0 !important;
            max-width: none;
            overflow: hidden !important
        }

        #ars2026-app #ars-table thead th {
            overflow: hidden !important;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-no,
        #ars2026-app #ars-table thead th.ars-sticky-no {
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            left: 0 !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name,
        #ars2026-app #ars-table thead th.ars-sticky-name {
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            left: 48px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name {
            background: #fff !important;
            box-shadow: 1px 0 0 #cbd5e1 !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td input {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            display: block !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name input {
            padding: 8px 9px !important;
            text-align: left !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td:nth-child(3) input {
            padding: 8px 9px !important;
            text-align: left !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-total td {
            background: #dbeafe !important;
            color: #1e3a5f !important;
            border-top: 2px solid #547a9d !important;
            border-bottom: 2px solid #547a9d !important;
            font-weight: 800 !important;
            overflow: hidden !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-total td:first-child {
            padding-left: 12px !important
        }

        #ars2026-app #ars-table tfoot tr.ars-grand-total td {
            background: #c5d8e9 !important;
            overflow: hidden !important
        }

        /* Freeze kolom No + Nama Pelanggan saat scroll horizontal.
           Lebar konsisten dengan dua kolom pertama pada colgroup (48px + 210px). */
        #ars2026-app #ars-table thead th.ars-sticky-no,
        #ars2026-app #ars-table tbody td.ars-sticky-no {
            position: sticky !important;
            left: 0 !important;
            right: auto !important;
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            background: #fff !important;
            z-index: 7 !important;
            transform: none !important;
        }

        #ars2026-app #ars-table thead th.ars-sticky-name,
        #ars2026-app #ars-table tbody td.ars-sticky-name {
            position: sticky !important;
            left: 48px !important;
            right: auto !important;
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            background: #fff !important;
            z-index: 7 !important;
            transform: none !important;
            box-shadow: 2px 0 0 #cbd5e1 !important;
        }

        #ars2026-app #ars-table thead th.ars-sticky-no,
        #ars2026-app #ars-table thead th.ars-sticky-name {
            background: #323d47 !important;
            color: #fff !important;
            z-index: 20 !important;
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name input {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            padding: 8px 9px !important;
            text-align: left !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td:nth-child(3) input {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            padding: 8px 9px !important;
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-usd-cell input {
            padding-left: 27px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-idr-cell input {
            padding-left: 30px !important
        }

        .ars-basis-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            white-space: nowrap
        }

        #ars-basis {
            min-width: 145px;
            border-color: #64748b;
            background: #f8fafc;
            font-weight: 700
        }

        #ars2026-app #ars-table tbody tr:hover td.ars-sticky-no,
        #ars2026-app #ars-table tbody tr:hover td.ars-sticky-name {
            background: #f7f9fa !important
        }

        /* Freeze tiga kolom: No (48px), Nama Pelanggan (210px), No. PO (165px).
           Offset disamakan persis dengan colgroup untuk mencegah overlap. */
        #ars2026-app #ars-table thead th.ars-sticky-no,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-no {
            position: sticky !important;
            left: 0 !important;
            right: auto !important;
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            transform: none !important;
            box-shadow: none !important;
        }

        #ars2026-app #ars-table thead th.ars-sticky-name,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name {
            position: sticky !important;
            left: 48px !important;
            right: auto !important;
            width: 210px !important;
            min-width: 210px !important;
            max-width: 210px !important;
            transform: none !important;
            box-shadow: 1px 0 0 #cbd5e1 !important;
        }

        #ars2026-app #ars-table thead th.ars-sticky-po,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-po {
            position: sticky !important;
            left: 258px !important;
            right: auto !important;
            width: 165px !important;
            min-width: 165px !important;
            max-width: 165px !important;
            transform: none !important;
            box-shadow: 2px 0 0 #94a3b8 !important;
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-no,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-po {
            background: #fff !important;
            z-index: 8 !important;
        }

        #ars2026-app #ars-table tbody tr.ars-data-row:hover td.ars-sticky-no,
        #ars2026-app #ars-table tbody tr.ars-data-row:hover td.ars-sticky-name,
        #ars2026-app #ars-table tbody tr.ars-data-row:hover td.ars-sticky-po {
            background: #f7f9fa !important;
        }

        #ars2026-app #ars-table thead th.ars-sticky-no,
        #ars2026-app #ars-table thead th.ars-sticky-name,
        #ars2026-app #ars-table thead th.ars-sticky-po {
            background: #323d47 !important;
            color: #fff !important;
            z-index: 21 !important;
        }

        #ars2026-app #ars-table tbody td.ars-sticky-po input {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
            padding: 8px 9px !important;
            margin: 0 !important;
        }

        /* Tombol tambah baris per bulan seperti lembar kerja Excel */
        #ars2026-app #ars-table tbody tr.ars-month-add td {
            background: #f8fafc !important;
            padding: 5px 9px !important;
            overflow: visible !important;
            height: 34px
        }

        #ars2026-app #ars-table tbody .ars-add-month {
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            background: #fff;
            color: #2563eb;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 11px;
            cursor: pointer
        }

        #ars2026-app #ars-table tbody .ars-add-month:hover {
            background: #eff6ff;
            border-color: #2563eb
        }

        #ars2026-app #ars-table tbody .ars-add-month:disabled {
            opacity: .45;
            cursor: not-allowed
        }

        #ars2026-app #ars-table tbody .ars-month-add .ars-add-note {
            font-size: 11px;
            color: #64748b;
            margin-left: 12px
        }

        #ars2026-app #ars-table tbody tr.ars-add-next td {
            background: #f1f5f9 !important;
            padding: 9px 12px !important
        }

        #ars2026-app .ars-add-next-month {
            border: 1px dashed #2563eb;
            border-radius: 5px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer
        }

        #ars2026-app .ars-add-next-month:disabled {
            opacity: .4;
            cursor: not-allowed
        }

        /* Tambah baris: ikon + persis di kolom No, satu ukuran dengan sel nomor. */
        #ars2026-app #ars-table tbody tr.ars-month-add td.ars-add-no {
            width: 48px !important;
            min-width: 48px !important;
            max-width: 48px !important;
            height: 34px !important;
            padding: 0 !important;
            text-align: center !important;
            background: #f8fafc !important;
            position: sticky !important;
            left: 0 !important;
            z-index: 8 !important;
            border-right: 1px solid #d9dfe5 !important;
        }

        #ars2026-app #ars-table tbody tr.ars-month-add td.ars-add-blank {
            height: 34px !important;
            padding: 0 !important;
            background: #fff !important;
        }

        #ars2026-app #ars-table tbody tr.ars-month-add .ars-add-month {
            display: block !important;
            width: 100% !important;
            height: 33px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: #eff6ff !important;
            color: #2563eb !important;
            font-size: 20px !important;
            line-height: 33px !important;
            font-weight: 700 !important;
            text-align: center !important;
        }

        #ars2026-app #ars-table tbody tr.ars-month-add .ars-add-month:hover {
            background: #dbeafe !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-add .ars-add-month:disabled {
            opacity: .4 !important
        }


        /* ARS 2026 — compact worksheet styling based on supplied Excel reference */
        #ars2026-app.ars-wrap {
            padding: 10px !important;
            background: #fff !important
        }

        #ars2026-app .ars-heading {
            margin-bottom: 7px !important
        }

        #ars2026-app .ars-heading h2 {
            font-size: 17px !important
        }

        #ars2026-app .ars-heading p {
            font-size: 10px !important
        }

        #ars2026-app .ars-filters {
            margin-bottom: 6px !important;
            gap: 6px !important
        }

        #ars2026-app .ars-excel-tools {
            padding: 4px 7px !important
        }

        #ars2026-app #ars-table {
            font-family: Arial, sans-serif !important;
            font-size: 10px !important
        }

        #ars2026-app #ars-table thead th {
            padding: 5px 4px !important;
            font-size: 10px !important;
            line-height: 13px !important;
            background: #e8edf2 !important;
            color: #18212b !important;
            border-color: #aab5c2 !important
        }

        #ars2026-app #ars-table thead th.ars-sticky-no,
        #ars2026-app #ars-table thead th.ars-sticky-name,
        #ars2026-app #ars-table thead th.ars-sticky-po {
            background: #e8edf2 !important;
            color: #18212b !important
        }

        #ars2026-app #ars-table thead tr:nth-child(2) th {
            top: 23px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td {
            height: 24px !important;
            border-color: #c8d1da !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td input {
            height: 23px !important;
            padding: 3px 5px !important;
            font-size: 10px !important;
            line-height: 16px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-name input,
        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-sticky-po input {
            padding: 3px 5px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-usd-cell input {
            padding-left: 23px !important
        }

        #ars2026-app #ars-table tbody tr.ars-data-row td.ars-idr-cell input {
            padding-left: 27px !important
        }

        #ars2026-app #ars-table .ars-num {
            padding: 4px 5px !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-add td.ars-add-no,
        #ars2026-app #ars-table tbody tr.ars-month-add td.ars-add-blank {
            height: 24px !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-add .ars-add-month {
            height: 23px !important;
            line-height: 23px !important;
            font-size: 17px !important;
            background: #fff !important;
            color: #1d4ed8 !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-add .ars-add-month:hover {
            background: #dbeafe !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-total td {
            padding: 5px 5px !important;
            border-top: 1px solid #526b85 !important;
            border-bottom: 1px solid #526b85 !important;
            background: #e6e9ed !important;
            color: #172536 !important;
            font-size: 10px !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-total td.ars-total-title {
            padding-left: 8px !important
        }

        #ars2026-app #ars-table tbody tr.ars-add-next td {
            padding: 5px !important;
            background: #fff !important
        }

        #ars2026-app .ars-add-next-month {
            padding: 4px 9px !important;
            font-size: 10px !important
        }

        #ars2026-app #ars-table tbody tr.ars-month-add td.ars-add-no {
            background: #fff !important
        }
    </style>
    <script>
        (function() {
            'use strict';
            const urls = {
                data: @json(route('ars2026.data')),
                store: @json(route('ars2026.store')),
                update: @json(route('ars2026.update', ['ar' => '__ID__'])),
                destroy: @json(route('ars2026.destroy', ['ar' => '__ID__'])),
                addMonth: @json(route('ars2026.months.store'))
            };
            const token = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
            const el = id => document.getElementById(id),
                months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                    'Oktober', 'November', 'Desember'
                ];
            const fields = [
                ['customer_name', 'text'],
                ['po_number', 'text'],
                ['invoice_number', 'text'],
                ['shipment_date', 'date'],
                ['peb_submission_number', 'text'],
                ['peb_number', 'text'],
                ['fob_usd', 'number'],
                ['fob_peb_usd', 'number'],
                ['exchange_rate', 'number'],
                ['container_count', 'number'],
                ['deposit_date', 'date'],
                ['deposit_amount', 'number'],
                ['settlement_date', 'date'],
                ['settlement_amount', 'number'],
                ['description', 'text'],
                ['remark', 'text']
            ];
            let activeMonths = [],
                rows = [],
                zoom = 100,
                busy = false,
                pasteProcessing = false,
                editMode = false,
                modeChanging = false;
            const empty = () => Object.fromEntries(fields.map(([key]) => [key, '']));
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            // Decimal: 56.000 -> 56000; 56.000,50 -> 56000.50; 56000.50 -> 56000.50
            function num(v) {
                let s = String(v ?? '').trim().replace(/Rp|USD|\$/gi, '').replace(/\s/g, '');
                if (!s) return 0;
                const comma = s.lastIndexOf(','),
                    dot = s.lastIndexOf('.');
                if (comma >= 0 && dot >= 0) {
                    s = comma > dot ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, '');
                } else if (comma >= 0) {
                    s = /^-?\d{1,3}(,\d{3})+$/.test(s) ? s.replace(/,/g, '') : s.replace(',', '.');
                } else if (dot >= 0 && /^-?\d{1,3}(\.\d{3})+$/.test(s)) {
                    s = s.replace(/\./g, '');
                }
                const n = Number(s);
                return Number.isFinite(n) ? n : NaN
            }
            const fmt = (n, d = 2) => new Intl.NumberFormat('en-US', {
                minimumFractionDigits: d,
                maximumFractionDigits: d
            }).format(n || 0);
            const calc = r => ({
                idr: num(r.fob_usd) * num(r.exchange_rate),
                usd: num(r.fob_usd) - num(r.deposit_amount) - num(r.settlement_amount)
            });
            // Satu-satunya sumber pengelompokan: month_number dari database.
            // NULL / di luar 1-12 ditampilkan sebagai Belum Terkategori.
            const month = r => {
                const value = r.month_number;
                const m = Number(value);
                return value !== null && value !== undefined && String(value).trim() !== '' && Number.isInteger(
                    m) && m >= 1 && m <= 12 ? m - 1 : -1;
            };
            const filtered = () => {
                let q = el('ars-search').value.toLowerCase().trim(),
                    m = el('ars-month').value;
                return rows.filter(r => (m === '' || String(month(r)) === m) && (!q || [r.customer_name, r
                    .po_number, r.invoice_number, r.peb_number, r.peb_submission_number
                ].some(v => String(v ?? '').toLowerCase().includes(q))))
            };
            const api = async (url, method = 'GET', payload) => {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin',
                    ...(payload !== undefined ? {
                        body: JSON.stringify(payload)
                    } : {})
                });
                let data;
                try {
                    data = await res.json()
                } catch {
                    throw new Error('Server tidak mengirim JSON (HTTP ' + res.status +
                        '). Periksa login/route.')
                }
                if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})?.flat()?.[0] ||
                    'HTTP ' + res.status);
                return data
            };
            const url = (template, id) => template.replace('__ID__', encodeURIComponent(id));

            function payload(r) {
                let out = {
                    month_number: r.month_number === null || r.month_number === undefined || String(r.month_number)
                        .trim() === '' ? null : Number(r.month_number)
                };
                fields.forEach(([k, t]) => {
                    let v = r[k];
                    if (t === 'number') {
                        if (String(v ?? '').trim() === '') out[k] = null;
                        else {
                            const n = num(v);
                            if (!Number.isFinite(n) || n < 0) throw new Error('Nominal tidak valid pada ' + k);
                            out[k] = n
                        }
                    } else out[k] = String(v ?? '').trim() || null
                });
                return out
            }

            function status(s, error = false) {
                el('ars-status').textContent = s;
                el('ars-status').style.color = error ? '#b91c1c' : '#475569'
            }

            function totalRow(label, group) {
                const sum = k => group.reduce((a, r) => a + num(r[k]), 0);
                const idr = group.reduce((a, r) => a + calc(r).idr, 0);
                const usd = group.reduce((a, r) => a + calc(r).usd, 0);
                return `<tr class="ars-month-total">
   <td colspan="7" class="ars-total-title">${esc(label)} · ${group.length} invoice</td>
   <td class="ars-num">$ ${fmt(sum('fob_usd'))}</td>
   <td class="ars-num">$ ${fmt(sum('fob_peb_usd'))}</td>
   <td></td><td class="ars-num">${fmt(sum('container_count'),0)}</td>
   <td class="ars-num">Rp ${fmt(idr)}</td>
   <td></td><td class="ars-num">$ ${fmt(sum('deposit_amount'))}</td>
   <td></td><td class="ars-num">$ ${fmt(sum('settlement_amount'))}</td>
   <td class="ars-num">$ ${fmt(usd)}</td><td colspan="3"></td>
  </tr>`
            }

            function addMonthRow(m) {
                return `<tr class="ars-month-add" data-add-section="${m}"><td class="ars-sticky-no ars-add-no"><button type="button" class="ars-add-month" data-add-month="${m}" title="Tambah baris ${months[m]}" aria-label="Tambah baris ${months[m]}" ${editMode&&!busy&&!pasteProcessing&&!modeChanging?'':'disabled'}>+</button></td><td colspan="19" class="ars-add-blank"></td></tr>`;
            }

            function addNextMonthRow() {
                const last = Math.max(0, ...activeMonths, ...rows.map(r => Number(r.month_number) || 0));
                if (last >= 12) return '';
                return `<tr class="ars-month-add ars-add-next"><td colspan="20"><button type="button" class="ars-add-next-month" ${editMode&&!busy&&!pasteProcessing&&!modeChanging?'':'disabled'}>+ Tambah Bulan ${months[last]}</button></td></tr>`;
            }

            function render() {
                const shown = filtered().slice().sort((a, b) => {
                    const ma = month(a),
                        mb = month(b);
                    if (ma !== mb) return (ma < 0 ? 13 : ma) - (mb < 0 ? 13 : mb);
                    return Number(a.sort_order || a.id) - Number(b.sort_order || b.id) || Number(a.id) - Number(
                        b.id);
                });
                el('ars-count').textContent = shown.length + ' dari ' + rows.length + ' baris';
                const byMonth = Array.from({
                        length: 12
                    }, () => []),
                    uncategorized = [];
                shown.forEach(r => {
                    const m = month(r);
                    (m >= 0 ? byMonth[m] : uncategorized).push(r)
                });
                let html = '';

                function renderDataRow(r, n) {
                    const c = calc(r);

                    function inputCell(k, t, sticky = false) {
                        const money = ['fob_usd', 'fob_peb_usd', 'deposit_amount', 'settlement_amount'].includes(k);
                        const rupiah = k === 'exchange_rate';
                        const val = t === 'number' && String(r[k] ?? '').trim() !== '' ? fmt(num(r[k]), k ===
                            'container_count' ? 0 : 2) : r[k];
                        const classes = [sticky ? 'ars-sticky-name' : '', k === 'po_number' ? 'ars-sticky-po' : '',
                            money ? 'ars-usd-cell' : '', rupiah ? 'ars-idr-cell' : ''
                        ].filter(Boolean).join(' ');
                        return `<td class="${classes}"><input data-id="${r.id}" data-field="${k}" data-type="${t}" type="${t==='date'?'date':'text'}" ${t==='number'?'inputmode="decimal"':''} value="${esc(val)}" title="${esc(k)}" ${editMode?'':'readonly tabindex="-1"'}></td>`;
                    }
                    const cells = fields.map(([k, t], index) => inputCell(k, t, index === 0));
                    cells.splice(10, 0,
                        `<td class="ars-num" data-calculated="idr" data-id="${r.id}">Rp ${fmt(c.idr)}</td>`);
                    cells.splice(15, 0,
                        `<td class="ars-num" data-calculated="usd" data-id="${r.id}">$ ${fmt(c.usd)}</td>`);
                    return `<tr class="ars-data-row"><td class="ars-sticky-no">${n}</td>${cells.join('')}<td><button type="button" class="ars-delete" data-delete="${r.id}" ${editMode?'':'disabled'}>Hapus</button></td></tr>`;
                }
                const searchActive = el('ars-search').value.trim() !== '';
                const selectedMonth = el('ars-month').value;
                for (const m of [...new Set([...activeMonths, ...rows.map(r => Number(r.month_number))].filter(n =>
                        Number.isInteger(n) && n >= 1 && n <= 12))].sort((a, b) => a - b).map(n => n - 1)) {
                    if (selectedMonth !== '' && selectedMonth !== String(m)) continue;
                    const group = byMonth[m];
                    // Saat mencari, sembunyikan bulan kosong agar hasil pencarian tetap ringkas.
                    if (searchActive && !group.length) continue;
                    group.forEach((r, i) => html += renderDataRow(r, i + 1));
                    if (!searchActive) html += addMonthRow(m);
                    html += totalRow('TOTAL ' + months[m].toUpperCase() + ' 2026', group);
                }
                if (!searchActive && selectedMonth === '') html += addNextMonthRow();
                if (uncategorized.length) {
                    uncategorized.forEach((r, i) => html += renderDataRow(r, i + 1));
                    html += totalRow('TOTAL DATA BELUM TERKATEGORI', uncategorized);
                }
                el('ars-body').innerHTML = html ||
                    '<tr><td colspan="20" style="padding:18px;text-align:center">Tidak ada data sesuai pencarian.</td></tr>';
                applyEditMode();
                el('ars-foot').innerHTML = totalRow('TOTAL KESELURUHAN (FILTER AKTIF)', shown).replace(
                    'ars-month-total', 'ars-grand-total');
                summary();
            }

            function summary() {
                el('ars-summary-basis').textContent = 'Bulan ARS';
                const totals = months.map((_, m) => rows.filter(r => month(r) === m).reduce((a, r) => {
                    a.idr += calc(r).idr;
                    a.usd += num(r.fob_usd);
                    return a
                }, {
                    idr: 0,
                    usd: 0
                }));
                el('ars-summary-body').innerHTML = totals.map((t, i) =>
                    `<tr><td>${months[i]}</td><td>${t.idr?'Rp '+fmt(t.idr):'–'}</td><td>${t.usd?'$ '+fmt(t.usd):'–'}</td></tr>`
                ).join('');
                const total = totals.reduce((a, t) => ({
                    idr: a.idr + t.idr,
                    usd: a.usd + t.usd
                }), {
                    idr: 0,
                    usd: 0
                });
                el('ars-summary-foot').innerHTML =
                    `<tr><td>TOTAL</td><td>Rp ${fmt(total.idr)}</td><td>$ ${fmt(total.usd)}</td></tr>`
            }

            function recalc(id) {
                const r = rows.find(x => x.id === id);
                if (!r) return;
                const c = calc(r);
                document.querySelectorAll('[data-calculated][data-id="' + id + '"]').forEach(td => td.textContent = (td
                    .dataset.calculated === 'idr' ? 'Rp ' : '$ ') + fmt(c[td.dataset.calculated]));
                summary()
            }
            // Autosave per baris, debounce 700ms. Request untuk baris yang sama diserialkan.
            const autosave = new Map();

            function applyEditMode() {
                const toggle = el('ars-edit-mode');
                toggle.checked = editMode;
                toggle.disabled = modeChanging || busy || pasteProcessing;
                el('ars-mode-label').textContent = editMode ? 'Mode Edit Aktif' : 'Mode Lihat';
                el('ars2026-app').classList.toggle('ars-view-mode', !editMode);
                el('ars-body').querySelectorAll('.ars-add-month,.ars-add-next-month').forEach(btn => btn.disabled = !
                    editMode || busy || pasteProcessing || modeChanging);
                el('ars-body').querySelectorAll('input[data-field]').forEach(input => {
                    input.readOnly = !editMode;
                    input.tabIndex = editMode ? 0 : -1;
                    if (input.type === 'date') input.disabled = !editMode;
                });
                el('ars-body').querySelectorAll('[data-delete]').forEach(btn => btn.disabled = !editMode || busy ||
                    modeChanging);
            }
            async function switchMode(next) {
                if (modeChanging || busy || pasteProcessing) {
                    applyEditMode();
                    return
                }
                if (next === editMode) return;
                if (!next) {
                    modeChanging = true;
                    applyEditMode();
                    const active = document.activeElement;
                    if (active?.matches?.('#ars-body input[data-field]')) active.blur();
                    const pending = [...autosave.entries()].filter(([id, q]) => q.revision !== q.savedRevision);
                    await Promise.all(pending.map(async ([id, q]) => {
                        clearTimeout(q.timer);
                        if (q.running) {
                            // Tunggu request yang sedang berjalan selesai, termasuk perubahan terbaru.
                            for (let attempt = 0; attempt < 80 && q.running; attempt++)
                                await new Promise(resolve => setTimeout(resolve, 100));
                        }
                        if (q.revision !== q.savedRevision) await saveRow(id);
                    }));
                    const stillPending = [...autosave.values()].some(q => q.running || q.revision !== q
                        .savedRevision);
                    modeChanging = false;
                    if (stillPending) {
                        status('Ada perubahan yang belum tersimpan. Periksa kesalahan, lalu coba nonaktifkan Mode Edit.',
                            true);
                        applyEditMode();
                        return;
                    }
                }
                editMode = next;
                applyEditMode();
                status(editMode ? 'Mode Edit aktif. Anda dapat menambah, mengubah, dan menghapus data.' :
                    'Mode Lihat aktif. Data tidak dapat diubah.');
            }
            el('ars-edit-mode').addEventListener('change', e => switchMode(e.target.checked));

            function tip(t, message, state = 'typing') {
                if (!t || !t.isConnected) return;
                const cell = t.closest('td');
                if (!cell) return;
                cell.classList.add('ars-editing');
                cell.dataset.saveState = state;
                let label = cell.querySelector('.ars-save-tip');
                if (!label) {
                    label = document.createElement('span');
                    label.className = 'ars-save-tip';
                    cell.appendChild(label)
                }
                label.textContent = message;
            }

            function getQueue(id) {
                if (!autosave.has(id)) autosave.set(id, {
                    timer: null,
                    revision: 0,
                    savedRevision: 0,
                    running: false,
                    pending: false
                });
                return autosave.get(id);
            }
            async function saveRow(id) {
                const q = getQueue(id);
                clearTimeout(q.timer);
                if (q.running) {
                    q.pending = true;
                    return
                }
                const r = rows.find(x => x.id === id);
                if (!r || q.revision === q.savedRevision) return;
                q.running = true;
                const revision = q.revision;
                const field = document.querySelector('#ars-body input[data-id="' + id + '"]:focus');
                if (field) tip(field, 'Menyimpan...', 'saving');
                try {
                    const snapshot = payload(r);
                    const response = await api(url(urls.update, id), 'PUT', snapshot);
                    q.savedRevision = revision;
                    if (q.revision === revision) {
                        Object.assign(r, response.row);
                        if (field && field.isConnected) {
                            field.dataset.before = String(r[field.dataset.field] ?? '');
                            tip(field, '✓ Tersimpan', 'saved')
                        }
                        status('Tersimpan');
                    }
                } catch (err) {
                    status('Gagal menyimpan: ' + err.message, true);
                    const active = document.querySelector('#ars-body input[data-id="' + id + '"]:focus');
                    if (active) tip(active, 'Gagal tersimpan', 'error');
                } finally {
                    q.running = false;
                    if (q.pending || q.revision !== q.savedRevision) {
                        q.pending = false;
                        if (q.revision !== q.savedRevision) q.timer = setTimeout(() => saveRow(id), 900)
                    }
                }
            }

            function scheduleSave(t, immediate = false) {
                const id = Number(t.dataset.id),
                    q = getQueue(id);
                q.revision++;
                clearTimeout(q.timer);
                tip(t, immediate ? 'Menyimpan...' : 'Mengetik...', 'typing');
                q.timer = setTimeout(() => saveRow(id), immediate ? 0 : 700);
            }
            el('ars-body').addEventListener('input', e => {
                const t = e.target;
                if (!editMode || !t.matches('input[data-field]') || pasteProcessing) return;
                const r = rows.find(x => x.id === Number(t.dataset.id));
                if (!r) return;
                r[t.dataset.field] = t.value;
                recalc(r.id);
                scheduleSave(t);
            });
            el('ars-body').addEventListener('focusin', e => {
                const t = e.target;
                if (!t.matches('input[data-field]')) return;
                const r = rows.find(x => x.id === Number(t.dataset.id));
                t.dataset.before = String(r?.[t.dataset.field] ?? '');
                if (t.dataset.type === 'number') {
                    t.value = t.dataset.before;
                    t.select()
                }
                tip(t, 'Siap mengedit', 'typing');
            });
            el('ars-body').addEventListener('keydown', e => {
                if (e.target.matches('input[data-field]') && e.key === 'Enter') {
                    e.preventDefault();
                    const t = e.target;
                    const q = getQueue(Number(t.dataset.id));
                    if (q.revision !== q.savedRevision) saveRow(Number(t.dataset.id));
                    t.blur();
                }
            });
            // Excel paste: multiple cells/rows; uses existing store/update endpoints.
            // Columns follow the visible worksheet, including computed columns that are skipped.
            const pasteColumns = [
                'customer_name', 'po_number', 'invoice_number', 'shipment_date',
                'peb_submission_number', 'peb_number', 'fob_usd', 'fob_peb_usd',
                'exchange_rate', 'container_count', null, 'deposit_date', 'deposit_amount',
                'settlement_date', 'settlement_amount', null, 'description', 'remark'
            ];

            // Format Excel shipment yang diberikan user: 16 kolom,
            // tanpa FOB PEB dan Description, tetapi ada dua kolom hasil rumus.
            const shipmentExcelColumns = [
                'customer_name', 'po_number', 'invoice_number', 'shipment_date',
                'peb_submission_number', 'peb_number', 'fob_usd', 'exchange_rate',
                'container_count', null, 'deposit_date', 'deposit_amount',
                'settlement_date', 'settlement_amount', null, 'remark'
            ];

            // Shipment lengkap dengan FOB PEB: 17 kolom, termasuk 2 kolom rumus.
            // Urutan: customer, PO, invoice, shipment, pengajuan PEB, PEB,
            // FOB, FOB PEB, kurs, container, jumlah IDR, deposit date,
            // deposit USD, settlement date, settlement USD, sisa USD, remark.
            const shipmentExcelWithPebColumns = [
                'customer_name', 'po_number', 'invoice_number', 'shipment_date',
                'peb_submission_number', 'peb_number', 'fob_usd', 'fob_peb_usd',
                'exchange_rate', 'container_count', null, 'deposit_date',
                'deposit_amount', 'settlement_date', 'settlement_amount', null, 'remark'
            ];

            function isShipmentWithPebLayout(cells) {
                return cells.length === 17 &&
                    isExcelDateText(cells[3]) &&
                    isExcelDateText(cells[11]) &&
                    isExcelDateText(cells[13]) &&
                    Number.isFinite(excelNumber(cells[9]) === '' ? NaN : Number(excelNumber(cells[9])));
            }

            function isExcelDateText(value) {
                return /^\d{1,4}[\/-]\d{1,2}[\/-]\d{2,4}$/.test(String(value ?? '').trim());
            }

            function isShipmentExcelLayout(cells) {
                // Pembeda dari 16 field database biasa: kolom 11 adalah tanggal
                // deposit, kolom 10 adalah jumlah Rp (rumus), kolom 9 container.
                return cells.length === 16 &&
                    isExcelDateText(cells[3]) &&
                    isExcelDateText(cells[10]) &&
                    isExcelDateText(cells[12]) &&
                    /^\s*\d+(?:[.,]\d+)?\s*$/.test(String(cells[8] ?? ''));
            }

            function excelDate(value) {
                const v = String(value ?? '').trim();
                if (!v) return '';

                function iso(year, month, day) {
                    const d = new Date(Date.UTC(year, month - 1, day));
                    if (d.getUTCFullYear() !== year || d.getUTCMonth() !== month - 1 || d.getUTCDate() !== day)
                        throw new Error('Tanggal Excel tidak valid: ' + v);
                    return `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                }
                // Format Excel yang kehilangan pemisah hari/bulan: 3107/2026 -> 31/07/2026.
                // Hanya terima DDMM/YYYY jika hari > 12 agar tidak ambigu.
                const compact = v.match(/^(\d{2})(\d{2})[\/-]?(\d{4})$/);
                if (compact && Number(compact[1]) > 12 && Number(compact[2]) >= 1 && Number(compact[2]) <= 12)
                    return iso(+compact[3], +compact[2], +compact[1]);
                const ymd = v.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                if (ymd) return iso(+ymd[1], +ymd[2], +ymd[3]);
                const parts = v.match(/^(\d{1,2})([\/-])(\d{1,2})\2(\d{2,4})$/);
                if (parts) {
                    let year = +parts[4];
                    if (year < 100) year += year >= 70 ? 1900 : 2000;
                    const first = +parts[1],
                        second = +parts[3];
                    // Excel US format M/D/YYYY is the default when ambiguous.
                    // D/M/YYYY is recognized when first number >12.
                    const month = first > 12 ? second : first;
                    const day = first > 12 ? first : second;
                    return iso(year, month, day);
                }
                if (/^\d{1,5}(?:\.\d+)?$/.test(v)) {
                    const serial = Number(v);
                    if (serial < 1 || serial > 2958465) throw new Error('Serial tanggal Excel tidak valid: ' + v);
                    const d = new Date(Date.UTC(1899, 11, 30) + Math.floor(serial) * 86400000);
                    return d.toISOString().slice(0, 10);
                }
                throw new Error('Format tanggal tidak dikenali: ' + v +
                    ' (gunakan M/D/YYYY, D/M/YYYY atau YYYY-MM-DD). Pastikan posisi kolom Excel sesuai kolom ARS.');
            }

            function excelNumber(value) {
                let s = String(value ?? '').trim().replace(/(?:Rp|USD|US\$|\$)/gi, '').replace(/\s/g, '');
                if (!s) return '';
                // 1,234.56 / 1.234,56 / 56.000 / 56000,50 / 56000.50
                const comma = s.lastIndexOf(','),
                    dot = s.lastIndexOf('.');
                if (comma >= 0 && dot >= 0) {
                    s = comma > dot ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, '')
                } else if (comma >= 0) {
                    s = /^-?\d{1,3}(,\d{3})+$/.test(s) ? s.replace(/,/g, '') : s.replace(',', '.');
                } else if (dot >= 0 && /^-?\d{1,3}(\.\d{3})+$/.test(s)) {
                    s = s.replace(/\./g, '')
                }
                const n = Number(s);
                if (!Number.isFinite(n) || n < 0) throw new Error('Angka Excel tidak valid: ' + value);
                return String(n);
            }

            function pasteValue(field, value) {
                const type = fields.find(([key]) => key === field)?.[1];
                return type === 'date' ? excelDate(value) : type === 'number' ? excelNumber(value) : String(value ?? '')
                    .trim();
            }
            el('ars-body').addEventListener('paste', async e => {
                const target = e.target.closest('input[data-field]');
                if (!editMode || !target || busy || pasteProcessing) return;
                const clipboard = e.clipboardData?.getData('text/plain') || '';
                if (!clipboard.includes('\t') && !clipboard.includes('\n') && !clipboard.includes('\r'))
                    return;
                e.preventDefault();
                const startColumn = pasteColumns.indexOf(target.dataset.field);
                if (startColumn < 0) {
                    status('Kolom tujuan tidak dikenali.', true);
                    return
                }
                const grid = clipboard.replace(/\r\n?/g, '\n').replace(/\n$/, '').split('\n').map(line =>
                    line.split('\t'));
                // Baris yang diklik menentukan BULAN TUJUAN paste.
                // Jangan pernah melanjutkan paste ke baris bulan berikutnya.
                const targetId = Number(target.dataset.id);
                const anchor = rows.find(r => Number(r.id) === targetId);
                if (!anchor) return;
                const targetMonth = Number(anchor.month_number);
                if (!Number.isInteger(targetMonth) || targetMonth < 1 || targetMonth > 12) {
                    status('Bulan target belum valid. Pilih baris pada bulan ARS yang benar.', true);
                    return;
                }
                const visible = filtered().slice().sort((a, b) =>
                    Number(a.month_number) - Number(b.month_number) ||
                    Number(a.sort_order || a.id) - Number(b.sort_order || b.id) ||
                    Number(a.id) - Number(b.id)
                );
                const monthRows = visible.filter(r => Number(r.month_number) === targetMonth);
                const startRow = monthRows.findIndex(r => Number(r.id) === targetId);
                if (startRow < 0) return;
                const plan = [];
                try {
                    grid.forEach((originalCells, rowOffset) => {
                        let cells = originalCells.slice();
                        let columnMap = pasteColumns.slice(startColumn);

                        // Paste satu baris penuh dapat berasal dari Excel dengan atau tanpa
                        // kolom hasil rumus (Jumlah Rp dan Sisa Piutang USD).
                        // 16 = semua field database; 18 = semua kolom worksheet tanpa No.
                        // 17/19 = format yang sama tetapi dengan kolom No di paling kiri.
                        if (startColumn === 0) {
                            const firstIsNo = /^\d+$/.test(String(cells[0] ?? '').trim());
                            if (firstIsNo && ((cells.length === 17 || cells.length === 19) ||
                                (cells.length === 18 && isShipmentWithPebLayout(cells.slice(1))))) {
                                cells.shift();
                            }
                            if (isShipmentWithPebLayout(cells)) {
                                columnMap = shipmentExcelWithPebColumns;
                            } else if (isShipmentExcelLayout(cells)) {
                                columnMap = shipmentExcelColumns;
                            } else if (cells.length === 16) {
                                columnMap = pasteColumns.filter(field => field !== null);
                            } else if (cells.length === 18) {
                                columnMap = pasteColumns.slice();
                            }
                        }

                        if (cells.length > columnMap.length) {
                            throw new Error(`Baris ${rowOffset + 1}: jumlah kolom Excel (${cells.length}) melebihi kolom ARS yang tersedia (${columnMap.length}). Periksa kolom No atau posisi sel awal paste.`);
                        }

                        const patch = {};
                        cells.forEach((value, colOffset) => {
                            const field = columnMap[colOffset];
                            if (!field) return; // Kolom rumus otomatis tidak ditimpa.
                            try {
                                patch[field] = pasteValue(field, value);
                            } catch (err) {
                                const label = {
                                    shipment_date: 'Tanggal Shipment',
                                    deposit_date: 'Tanggal Deposit',
                                    settlement_date: 'Tanggal Pelunasan'
                                }[field] || field;
                                throw new Error(`Baris ${rowOffset + 1}, kolom ${label}: ${err.message}. Pastikan sel awal paste dan urutan kolom Excel benar.`);
                            }
                        });
                        if (Object.keys(patch).length) plan.push({
                            rowOffset,
                            patch
                        });
                    });
                } catch (err) {
                    status(err.message, true);
                    return;
                }
                if (!plan.length) return;
                // Avoid writing into hidden rows when a search/month filter is active.
                // Appended rows are created at the bottom of the unfiltered sheet.
                const existingCount = Math.min(plan.length, Math.max(0, monthRows.length - startRow));
                const createCount = plan.length - existingCount;
                if (createCount > 0 && el('ars-search').value.trim()) {
                    status('Untuk paste yang menambah baris, kosongkan pencarian dahulu. Filter bulan boleh tetap aktif.',
                        true);
                    return;
                }
                if (!confirm(
                        `Paste ${plan.length} baris ke bulan ${months[targetMonth - 1]} 2026? ${existingCount} baris bulan ini akan diperbarui, ${createCount} baris baru dibuat. Baris bulan lain tidak akan diubah.`
                    )) return;
                pasteProcessing = true;
                busy = true;
                el('ars-body').querySelectorAll('.ars-add-month').forEach(btn => btn.disabled = true);
                let completed = 0;
                try {
                    for (let i = 0; i < plan.length; i++) {
                        const {
                            rowOffset,
                            patch
                        } = plan[i];
                        const current = monthRows[startRow + rowOffset];
                        if (current) {
                            const candidate = {
                                ...current,
                                ...patch
                            };
                            candidate.month_number = targetMonth;
                            const result = await api(url(urls.update, current.id), 'PUT', payload(
                                candidate));
                            Object.assign(current, result.row);
                        } else {
                            const candidate = {
                                ...empty(),
                                ...patch,
                                month_number: targetMonth
                            };
                            const result = await api(urls.store, 'POST', payload(candidate));
                            rows.push(result.row);
                        }
                        completed++;
                        status(`Menyimpan hasil paste ${completed}/${plan.length} baris...`);
                    }
                    sortRows();
                    render();
                    status(`${completed} baris berhasil ditempel ke bulan ${months[targetMonth - 1]} 2026 dan disimpan.`);
                } catch (err) {
                    status(`Paste berhenti pada baris ${completed+1}: ${err.message}. ${completed} baris sebelumnya sudah tersimpan. Memuat ulang data...`,
                        true);
                    try {
                        const latest = await api(urls.data);
                        rows = latest.rows || [];
                        activeMonths = latest.months || activeMonths;
                        sortRows();
                        render()
                    } catch {}
                } finally {
                    pasteProcessing = false;
                    busy = false;
                    applyEditMode()
                }
            });
            el('ars-body').addEventListener('focusout', e => {
                const t = e.target;
                if (!editMode || !t.matches('input[data-field]') || pasteProcessing) return;
                const id = Number(t.dataset.id),
                    r = rows.find(x => x.id === id);
                if (!r) return;
                const q = getQueue(id);
                if (q.revision !== q.savedRevision) saveRow(id);
                if (t.dataset.type === 'number') {
                    const value = String(r[t.dataset.field] ?? '').trim();
                    t.value = value === '' ? '' : fmt(num(value), t.dataset.field === 'container_count' ? 0 :
                        2);
                }
                const td = t.closest('td');
                if (td) {
                    td.classList.remove('ars-editing');
                    td.removeAttribute('data-save-state')
                }
                if (t.dataset.field === 'invoice_number') setTimeout(() => {
                    if (!document.activeElement?.matches?.('#ars-body input[data-field]')) {
                        sortRows();
                        render()
                    }
                }, 1200);
            });
            el('ars-body').addEventListener('click', async e => {
                const btn = e.target.closest('[data-delete]');
                if (!editMode || !btn || busy || modeChanging) return;
                if (!confirm('Hapus baris ini secara permanen?')) return;
                busy = true;
                btn.disabled = true;
                try {
                    await api(url(urls.destroy, btn.dataset.delete), 'DELETE');
                    rows = rows.filter(r => r.id !== Number(btn.dataset.delete));
                    render();
                    status('Baris dihapus')
                } catch (err) {
                    status(err.message, true);
                    btn.disabled = false
                } finally {
                    busy = false;
                    applyEditMode();
                }
            });
            el('ars-body').addEventListener('click', async e => {
                const btn = e.target.closest('button[data-add-month]');
                if (!btn || !editMode || busy || pasteProcessing || modeChanging) return;
                const m = Number(btn.dataset.addMonth);
                if (!Number.isInteger(m) || m < 0 || m > 11) return;
                busy = true;
                applyEditMode();
                status('Menambahkan baris ' + months[m] + '...');
                try {
                    const candidate = empty();
                    candidate.month_number = m + 1;
                    const data = await api(urls.store, 'POST', payload(candidate));
                    rows.push(data.row);
                    // Filter bulan tetap di tempat; pencarian dibersihkan agar baris baru terlihat.
                    el('ars-search').value = '';
                    sortRows();
                    render();
                    status('Baris ' + months[m] + ' berhasil ditambahkan');
                    const target = el('ars-body').querySelector(
                        `input[data-id="${data.row.id}"][data-field="customer_name"]`);
                    if (target) {
                        target.focus();
                        target.scrollIntoView({
                            block: 'nearest',
                            inline: 'nearest'
                        })
                    }
                } catch (err) {
                    status('Gagal menambah baris: ' + err.message, true)
                } finally {
                    busy = false;
                    applyEditMode()
                }
            });
            el('ars-body').addEventListener('click', async e => {
                const btn = e.target.closest('.ars-add-next-month');
                if (!btn || !editMode || busy || pasteProcessing || modeChanging) return;
                busy = true;
                applyEditMode();
                try {
                    // Kirim bulan tujuan secara eksplisit; jangan bergantung pada bulan terakhir yang mungkin stale.
                    const last = Math.max(0, ...activeMonths, ...rows.map(r => Number(r.month_number) ||
                        0));
                    const requested = last + 1;
                    if (requested > 12) throw new Error('Desember sudah menjadi bulan terakhir.');
                    const result = await api(urls.addMonth, 'POST', {
                        month_number: requested
                    });
                    if (Number(result.row?.month_number) !== requested) throw new Error(
                        'Bulan data yang dikembalikan tidak sesuai permintaan.');
                    rows.push(result.row);
                    activeMonths = [...new Set([...(result.months || []), ...activeMonths, requested])]
                        .sort((a, b) => a - b);
                    el('ars-search').value = '';
                    el('ars-month').value = '';
                    sortRows();
                    render();
                    status('Bulan ' + months[requested - 1] + ' ditambahkan.');
                    const target = el('ars-body').querySelector(
                        `input[data-id="${result.row.id}"][data-field="customer_name"]`);
                    if (target) {
                        target.focus();
                        target.scrollIntoView({
                            block: 'nearest',
                            inline: 'nearest'
                        })
                    }
                } catch (err) {
                    status('Gagal menambah bulan: ' + err.message, true)
                } finally {
                    busy = false;
                    applyEditMode()
                }
            });

            function sortRows() {
                rows.sort((a, b) => {
                    const ma = month(a),
                        mb = month(b);
                    if (ma !== mb) return (ma < 0 ? 13 : ma) - (mb < 0 ? 13 : mb);
                    return Number(a.sort_order || a.id) - Number(b.sort_order || b.id) || a.id - b.id
                })
            }
            el('ars-search').addEventListener('input', render);
            el('ars-month').addEventListener('change', render);

            function setZoom(v) {
                zoom = Math.max(60, Math.min(160, Math.round(Number(v) / 10) * 10));
                el('ars-table').style.zoom = String(zoom / 100);
                el('ars-zoom-range').value = zoom;
                el('ars-zoom-value').textContent = zoom + '%'
            }
            el('ars-zoom-in').onclick = () => setZoom(zoom + 10);
            el('ars-zoom-out').onclick = () => setZoom(zoom - 10);
            el('ars-zoom-reset').onclick = () => setZoom(100);
            el('ars-zoom-range').oninput = e => setZoom(e.target.value);
            el('ars-scroll').addEventListener('wheel', e => {
                if (e.ctrlKey) {
                    e.preventDefault();
                    setZoom(zoom + (e.deltaY < 0 ? 10 : -10))
                } else if (e.shiftKey) {
                    e.preventDefault();
                    el('ars-scroll').scrollLeft += e.deltaY || e.deltaX
                }
            }, {
                passive: false
            });
            el('ars-export').addEventListener('click', () => {
                const exportRows = filtered().slice().sort((a, b) => {
                    const ma = month(a),
                        mb = month(b);
                    return (ma < 0 ? 13 : ma) - (mb < 0 ? 13 : mb) || Number(a.sort_order || a.id) -
                        Number(b.sort_order || b.id)
                });
                let previousMonth = null,
                    monthNo = 0;
                const head = ['No', 'Nama Pelanggan', 'No. PO', 'No. Invoice', 'Tanggal Shipment',
                    'No. Pengajuan PEB', 'No. PEB', 'FOB USD', 'FOB PEB USD', 'Kurs Kemenkeu',
                    'Jumlah Container', 'Jumlah Rp', 'Deposit Tanggal', 'Deposit USD', 'Pelunasan Tanggal',
                    'Pelunasan USD', 'Sisa Piutang USD', 'Keterangan', 'Remark'
                ];
                const records = [head, ...exportRows.map(r => {
                    const m = month(r);
                    if (m !== previousMonth) {
                        monthNo = 0;
                        previousMonth = m;
                    }
                    monthNo++;
                    return [monthNo, r.customer_name, r.po_number, r.invoice_number, r
                        .shipment_date, r.peb_submission_number, r.peb_number, r.fob_usd, r
                        .fob_peb_usd, r.exchange_rate, r.container_count, calc(r).idr, r
                        .deposit_date, r.deposit_amount, r.settlement_date, r.settlement_amount,
                        calc(r).usd, r.description, r.remark
                    ]
                })];
                const csv = '\uFEFF' + records.map(record => record.map(v => '"' + String(v ?? '').replace(/"/g,
                    '""') + '"').join(',')).join('\r\n');
                const a = document.createElement('a'),
                    u = URL.createObjectURL(new Blob([csv], {
                        type: 'text/csv;charset=utf-8;'
                    }));
                a.href = u;
                a.download = 'ARS-2026.csv';
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(u), 1000)
            });
            months.forEach((m, i) => el('ars-month').insertAdjacentHTML('beforeend',
                `<option value="${i}">${m}</option>`));
            (async () => {
                status('Memuat data...');
                try {
                    const data = await api(urls.data);
                    rows = data.rows || [];
                    activeMonths = [...new Set([...(data.months || [1]), ...rows.map(r => Number(r
                        .month_number)).filter(n => n >= 1 && n <= 12)])].sort((a, b) => a - b);
                    sortRows();
                    render();
                    status('Data siap')
                } catch (err) {
                    status(err.message, true)
                }
            })();
        })();
    </script>
@endsection
