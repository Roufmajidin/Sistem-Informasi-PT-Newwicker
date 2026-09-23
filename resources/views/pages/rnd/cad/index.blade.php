@extends('master.master')
@section('title', 'CAD - Master Data')
@section('content')

    @php
        $detail = $find->detail ?? [];

        /*
    |--------------------------------------------------------------------------
    | PRODUCT / ARTICLE
    |--------------------------------------------------------------------------
    */
        $articleFallback = $detail['article_nr_'] ?? ($detail['article_nr_nw'] ?? ($detail['nw_code'] ?? '-'));

        /*
    |--------------------------------------------------------------------------
    | REVISION COUNT
    |
    | Tidak mengubah database.
    | Count dihitung dari data $cads yang sudah dikirim controller.
    |--------------------------------------------------------------------------
    */
        $cadCollection = collect($cads ?? []);

        /*
         * REVISION = JUMLAH FILE CAD UNTUK ARTICLE TERSEBUT.
         * Jadi 2 upload/file pada article yang sama = 2 Revisi.
         * Tidak menggunakan unique(version), karena beberapa file
         * bisa berada pada version/revisi yang sama.
         */
        $fileCountByArticle = $cadCollection
            ->groupBy(function ($cad) {
                return trim((string) ($cad->article_code ?? ''));
            })
            ->map(function ($items) {
                return $items->count();
            });
    @endphp

    <style>
        /* =========================================================
           CAD PAGE
           Modern / Clean / Comfortable
           ========================================================= */

        .cad-page {
            --cad-blue: #2563eb;
            --cad-blue-soft: #eff6ff;
            --cad-blue-border: #bfdbfe;

            --cad-text: #172033;
            --cad-text-2: #344054;
            --cad-muted: #667085;

            --cad-border: #e7eaf0;
            --cad-border-2: #eef1f5;

            --cad-bg: #f7f8fb;
            --cad-white: #ffffff;

            --cad-green: #15803d;
            --cad-green-soft: #dcfce7;

            --cad-red: #dc2626;
            --cad-red-soft: #fee2e2;

            background: var(--cad-bg);
            min-height: calc(100vh - 60px);
            padding: 22px;
        }

        .cad-shell {
            max-width: 1550px;
            margin: 0 auto;
        }

        /* =========================================================
           TOP BAR
           ========================================================= */

        .cad-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 18px;
        }

        .cad-heading {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cad-heading-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--cad-blue-soft);
            color: var(--cad-blue);

            font-size: 18px;
        }

        .cad-heading h2 {
            margin: 0;
            color: var(--cad-text);
            font-size: 22px;
            font-weight: 750;
            letter-spacing: -.3px;
        }

        .cad-heading p {
            margin: 3px 0 0;
            color: var(--cad-muted);
            font-size: 12px;
        }

        .cad-back-btn {
            height: 37px;

            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 0 12px !important;

            border: 1px solid var(--cad-border) !important;
            border-radius: 9px !important;

            background: #fff !important;
            color: #475467 !important;

            font-size: 11px !important;
            font-weight: 650 !important;

            box-shadow: 0 2px 8px rgba(15, 23, 42, .03);
        }

        .cad-back-btn:hover {
            background: #f9fafb !important;
        }

        /* =========================================================
           PRODUCT INFO
           ========================================================= */

        .cad-product-card {
            background: #fff;
            border: 1px solid var(--cad-border);
            border-radius: 15px;

            padding: 14px 16px;

            display: flex;
            align-items: center;
            gap: 15px;

            margin-bottom: 18px;

            box-shadow: 0 5px 20px rgba(15, 23, 42, .035);
        }

        .cad-product-photo {
            width: 65px;
            height: 65px;
            flex: 0 0 65px;

            overflow: hidden;
            border-radius: 11px;

            border: 1px solid var(--cad-border);

            background: #f3f4f6;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cad-product-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cad-product-photo-empty {
            color: #98a2b3;
            font-size: 18px;
        }

        .cad-product-info {
            min-width: 0;
            flex: 1;
        }

        .cad-label {
            color: #98a2b3;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .cad-product-code {
            margin-top: 2px;
            color: var(--cad-text);
            font-size: 17px;
            font-weight: 750;
        }

        .cad-product-description {
            color: var(--cad-muted);
            font-size: 11px;

            margin-top: 2px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cad-stats {
            display: flex;
            gap: 8px;
        }

        .cad-stat {
            min-width: 110px;

            border: 1px solid var(--cad-border);
            border-radius: 10px;

            background: #fafbfc;

            padding: 8px 11px;
        }

        .cad-stat-label {
            color: #98a2b3;
            font-size: 9px;
            font-weight: 700;
        }

        .cad-stat-value {
            margin-top: 2px;

            color: var(--cad-text);
            font-size: 17px;
            font-weight: 750;
        }

        .cad-stat-value.blue {
            color: var(--cad-blue);
        }

        /* =========================================================
           MAIN CARD
           ========================================================= */

        .cad-main-card {
            background: #fff;

            border: 1px solid var(--cad-border);
            border-radius: 15px;

            box-shadow: 0 5px 20px rgba(15, 23, 42, .035);

            overflow: hidden;
        }

        .cad-main-header {
            padding: 16px 18px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;

            border-bottom: 1px solid var(--cad-border-2);
        }

        .cad-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cad-section-icon {
            width: 35px;
            height: 35px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: var(--cad-blue-soft);
            color: var(--cad-blue);
        }

        .cad-section-title h4 {
            margin: 0;

            color: var(--cad-text);
            font-size: 15px;
            font-weight: 750;
        }

        .cad-section-title small {
            display: block;

            margin-top: 2px;

            color: #98a2b3;
            font-size: 10px;
        }

        .cad-upload-btn {
            height: 35px;

            display: inline-flex;
            align-items: center;
            gap: 6px;

            border: 0 !important;
            border-radius: 8px !important;

            padding: 0 12px !important;

            background: var(--cad-blue) !important;
            color: #fff !important;

            font-size: 11px !important;
            font-weight: 700 !important;

            box-shadow: 0 4px 12px rgba(37, 99, 235, .18);
        }

        .cad-upload-btn:hover {
            filter: brightness(.96);
            transform: translateY(-1px);
        }

        /* =========================================================
           SEARCH
           ========================================================= */

        .cad-toolbar {
            padding: 13px 18px;

            display: flex;
            align-items: center;
            gap: 10px;

            border-bottom: 1px solid var(--cad-border-2);
        }

        .cad-search {
            position: relative;
            flex: 1;
        }

        .cad-search i {
            position: absolute;
            left: 12px;
            top: 50%;

            transform: translateY(-50%);

            color: #98a2b3;
            font-size: 12px;

            pointer-events: none;
        }

        .cad-search input {
            width: 100%;
            height: 38px;

            border: 1px solid #e1e5eb !important;
            border-radius: 9px !important;

            background: #fff !important;

            padding: 0 12px 0 34px !important;

            color: #344054;
            font-size: 11px;

            box-shadow: none !important;
            outline: none !important;

            transition: .18s ease;
        }

        .cad-search input:focus {
            border-color: #93c5fd !important;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, .07) !important;
        }

        .cad-result-count {
            color: #98a2b3;
            font-size: 10px;
            white-space: nowrap;
        }

        /* =========================================================
           TABLE
           ========================================================= */

        .cad-table-container {
            width: 100%;
            overflow: auto;
            max-height: calc(100vh - 330px);
            position: relative;
        }

        /* Sticky table header
           Tetap terlihat saat halaman di-scroll, tanpa mengganggu horizontal scroll. */
        .cad-main-table thead {
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .cad-main-table thead th {
            position: sticky;
            top: 0;
            z-index: 31;
            background: #f8fafc !important;
            box-shadow: 0 1px 0 #e4e7ec;
        }

        .cad-table-container::-webkit-scrollbar {
            height: 7px;
        }

        .cad-table-container::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .cad-table-container::-webkit-scrollbar-thumb {
            background: #d5dbe3;
            border-radius: 20px;
        }

        .cad-main-table {
            width: 100%;

            min-width: 900px;

            border-collapse: separate;
            border-spacing: 0;

            margin: 0 !important;
        }

        .cad-main-table thead th {
            height: 42px;

            background: #f8fafc !important;

            color: #667085 !important;

            border: 0 !important;
            border-bottom: 1px solid #e4e7ec !important;

            padding: 0 13px !important;

            font-size: 9px !important;
            font-weight: 800 !important;

            letter-spacing: .5px;
            text-transform: uppercase;

            white-space: nowrap;

            vertical-align: middle !important;
        }

        .cad-main-table tbody td {
            height: 61px;

            padding: 9px 13px !important;

            color: #344054;

            border: 0 !important;
            border-bottom: 1px solid #f0f2f5 !important;

            font-size: 11px;

            vertical-align: middle !important;

            white-space: nowrap;
        }

        .cad-main-table tbody tr {
            background: #fff;

            transition:
                background .15s ease,
                transform .15s ease;
        }

        .cad-main-table tbody tr:hover {
            background: #f8fbff;
        }

        .cad-main-table tbody tr:last-child td {
            border-bottom: 0 !important;
        }

        /* number */

        .cad-number {
            width: 45px;

            text-align: center;

            color: #98a2b3 !important;
            font-size: 10px !important;
            font-weight: 650;
        }

        /* article */

        .cad-article {
            color: #1d4ed8 !important;
            font-weight: 750;

            letter-spacing: .1px;
        }

        /* item */

        .cad-item-cell {
            min-width: 230px;
        }

        .cad-item-name {
            max-width: 290px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: #263244;
            font-weight: 650;
        }

        .cad-item-empty {
            color: #98a2b3;
        }

        /* master */

        .cad-master-cell {
            min-width: 150px;

            color: #475467;
            font-size: 10px;
        }

        /* uploader */

        .cad-uploader {
            min-width: 170px;

            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cad-avatar {
            width: 29px;
            height: 29px;
            flex: 0 0 29px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #eef2ff;
            color: #4f46e5;

            font-size: 9px;
            font-weight: 800;
        }

        .cad-uploader-name {
            max-width: 130px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: #475467;
            font-size: 10px;
            font-weight: 650;
        }

        /* revision count */

        .revision-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            padding: 5px 8px;

            border-radius: 20px;

            background: #eef2ff;
            color: #4338ca;

            font-size: 10px;
            font-weight: 750;

            white-space: nowrap;
        }

        .revision-badge i {
            font-size: 9px;
        }

        .revision-sub {
            margin-left: 4px;
            color: #98a2b3;
            font-size: 9px;
        }

        /* history */

        .history-btn {
            height: 30px;

            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 0 9px !important;

            border: 0 !important;
            border-radius: 8px !important;

            background: #e0f2fe !important;
            color: #0284c7 !important;

            font-size: 10px !important;
            font-weight: 750 !important;

            transition: .16s ease;
        }

        .history-btn:hover {
            background: #bae6fd !important;
            color: #0369a1 !important;
            transform: translateY(-1px);
        }

        /* action */

        .delete-btn {
            width: 30px;
            height: 30px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 !important;

            border: 0 !important;
            border-radius: 8px !important;

            background: var(--cad-red-soft) !important;
            color: var(--cad-red) !important;

            font-size: 10px !important;

            transition: .16s ease;
        }

        .delete-btn:hover {
            background: #fecaca !important;
            transform: translateY(-1px);
        }

        /* =========================================================
           EMPTY
           ========================================================= */

        .cad-empty {
            padding: 55px 20px !important;

            text-align: center;

            color: #98a2b3 !important;
        }

        .cad-empty-icon {
            width: 48px;
            height: 48px;

            margin: 0 auto 10px;

            border-radius: 13px;

            background: #f2f4f7;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 18px;
        }

        .cad-empty strong {
            display: block;

            color: #667085;
            font-size: 13px;
        }

        .cad-empty span {
            display: block;

            margin-top: 3px;

            color: #98a2b3;
            font-size: 10px;
        }

        /* =========================================================
           HISTORY DRAWER
           ========================================================= */

        .cad-drawer {
            position: fixed;

            top: 0;
            right: -620px;

            width: min(620px, 94vw);
            height: 100vh;

            z-index: 10000;

            background: #fff;

            box-shadow: -15px 0 45px rgba(15, 23, 42, .15);

            transition: right .25s ease;

            display: flex;
            flex-direction: column;
        }

        .cad-drawer.show {
            right: 0;
        }

        .cad-drawer-overlay {
            position: fixed;
            inset: 0;

            z-index: 9999;

            background: rgba(15, 23, 42, .32);

            backdrop-filter: blur(2px);

            opacity: 0;
            visibility: hidden;

            transition: .2s ease;
        }

        .cad-drawer-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .cad-drawer-head {
            padding: 17px 19px;

            border-bottom: 1px solid var(--cad-border);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .cad-drawer-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cad-drawer-icon {
            width: 36px;
            height: 36px;

            border-radius: 9px;

            background: var(--cad-blue-soft);
            color: var(--cad-blue);

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cad-drawer-title h4 {
            margin: 0;

            color: var(--cad-text);
            font-size: 15px;
            font-weight: 750;
        }

        .cad-drawer-title small {
            display: block;

            margin-top: 2px;

            color: #98a2b3;
            font-size: 10px;
        }

        .cad-drawer-close {
            width: 32px;
            height: 32px;

            border: 0;
            border-radius: 8px;

            background: #f2f4f7;
            color: #667085;

            cursor: pointer;
        }

        .cad-drawer-close:hover {
            background: #e4e7ec;
        }

        .cad-drawer-body {
            flex: 1;

            overflow-y: auto;

            padding: 17px;
        }

        .cad-selected-article {
            padding: 11px 13px;

            border: 1px solid #dbeafe;
            border-radius: 10px;

            background: #f8fbff;

            margin-bottom: 13px;
        }

        .cad-selected-label {
            color: #93a4bf;

            font-size: 9px;
            font-weight: 750;

            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .cad-selected-value {
            margin-top: 2px;

            color: #1d4ed8;

            font-size: 13px;
            font-weight: 750;
        }

        /* upload new version */

        .cad-version-btn {
            width: 100%;

            height: 38px;

            border: 1px solid #dbeafe !important;
            border-radius: 9px !important;

            background: #eff6ff !important;
            color: #2563eb !important;

            font-size: 11px !important;
            font-weight: 700 !important;
        }

        .cad-version-btn:hover {
            background: #dbeafe !important;
        }

        .cad-upload-card {
            margin-top: 11px;

            padding: 14px;

            border: 1px solid var(--cad-border);
            border-radius: 11px;

            background: #fafbfc;
        }

        .cad-upload-card label {
            display: block;

            margin-bottom: 5px;

            color: #475467;
            font-size: 10px;
            font-weight: 750;
        }

        .cad-upload-card input {
            height: 37px;

            border: 1px solid #dfe3e8 !important;
            border-radius: 8px !important;

            box-shadow: none !important;

            font-size: 11px;
        }

        .cad-upload-card .btn {
            border-radius: 8px !important;
            font-size: 10px !important;
        }

        /* history list */

        .history-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .history-item {
            border: 1px solid var(--cad-border);
            border-radius: 11px;

            padding: 11px;

            background: #fff;
        }

        .history-item:hover {
            background: #fafcff;
            border-color: #dbeafe;
        }

        .history-item-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .history-version {
            display: inline-flex;
            align-items: center;

            min-width: 39px;
            height: 28px;

            justify-content: center;

            border-radius: 8px;

            background: #eef2ff;
            color: #4338ca;

            font-size: 10px;
            font-weight: 800;
        }

        .history-version.latest {
            background: #2563eb;
            color: #fff;
        }

        .history-file {
            margin-top: 8px;

            display: flex;
            align-items: center;
            gap: 9px;
        }

        .history-file-icon {
            width: 31px;
            height: 31px;

            flex: 0 0 31px;

            border-radius: 8px;

            background: #f2f4f7;

            color: #667085;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .history-file-name {
            min-width: 0;
            flex: 1;

            color: #344054;

            font-size: 11px;
            font-weight: 650;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .history-meta {
            margin-top: 7px;

            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .history-meta span {
            padding: 4px 7px;

            border-radius: 6px;

            background: #f8fafc;

            color: #667085;

            font-size: 9px;
        }

        .history-actions {
            margin-top: 9px;

            display: flex;
            gap: 6px;
        }

        .history-actions .btn {
            border-radius: 7px !important;
            font-size: 9px !important;
            padding: 5px 8px !important;
        }

        .history-loading,
        .history-empty {
            padding: 40px 10px;

            text-align: center;

            color: #98a2b3;

            font-size: 11px;
        }

        /* =========================================================
           UPLOAD MODAL
           ========================================================= */

        #modal-upload {
            position: fixed;
            inset: 0;

            z-index: 11000;

            display: none;

            align-items: center;
            justify-content: center;

            padding: 20px;

            background: rgba(15, 23, 42, .48);

            backdrop-filter: blur(4px);
        }

        .cad-upload-modal {
            width: min(500px, 100%);

            overflow: hidden;

            border-radius: 16px;

            background: #fff;

            box-shadow: 0 25px 70px rgba(15, 23, 42, .22);
        }

        .cad-upload-modal-head {
            padding: 16px 18px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            border-bottom: 1px solid var(--cad-border);
        }

        .cad-upload-modal-head h4 {
            margin: 0;

            color: var(--cad-text);

            font-size: 15px;
            font-weight: 750;
        }

        .cad-upload-modal-head small {
            display: block;

            margin-top: 2px;

            color: #98a2b3;

            font-size: 10px;
        }

        .cad-modal-close {
            width: 31px;
            height: 31px;

            border: 0;
            border-radius: 8px;

            background: #f2f4f7;
            color: #667085;

            cursor: pointer;
        }

        .cad-upload-modal-body {
            padding: 18px;
        }

        .cad-form-group {
            margin-bottom: 13px;
        }

        .cad-form-group label {
            display: block;

            margin-bottom: 5px;

            color: #475467;

            font-size: 10px;
            font-weight: 750;
        }

        .cad-form-group input {
            width: 100%;
            height: 38px;

            border: 1px solid #dfe3e8 !important;
            border-radius: 8px !important;

            padding: 0 10px !important;

            font-size: 11px;

            box-shadow: none !important;
        }

        .cad-progress {
            display: none;

            margin-top: 12px;
        }

        .cad-progress-track {
            height: 7px;

            overflow: hidden;

            border-radius: 20px;

            background: #edf0f3;
        }

        #progress-bar {
            width: 0%;
            height: 100%;

            border-radius: 20px;

            background: var(--cad-blue);

            transition: width .15s ease;
        }

        .cad-progress-label {
            margin-top: 5px;

            display: flex;
            justify-content: space-between;

            color: #98a2b3;

            font-size: 9px;
        }

        .cad-upload-modal-footer {
            padding: 12px 18px;

            background: #fafbfc;

            border-top: 1px solid var(--cad-border);

            display: flex;
            justify-content: flex-end;
            gap: 7px;
        }

        .cad-upload-modal-footer .btn {
            border-radius: 8px !important;

            font-size: 10px !important;
            font-weight: 650 !important;

            padding: 7px 12px !important;
        }

        /* =========================================================
           RESPONSIVE
           ========================================================= */

        @media (max-width: 1000px) {

            .cad-product-card {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .cad-stats {
                width: 100%;
            }

            .cad-stat {
                flex: 1;
            }
        }

        @media (max-width: 700px) {

            .cad-page {
                padding: 12px;
            }

            .cad-topbar {
                align-items: flex-start;
            }

            .cad-heading h2 {
                font-size: 19px;
            }

            .cad-main-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .cad-upload-btn {
                width: 100%;
                justify-content: center;
            }

            .cad-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .cad-result-count {
                text-align: right;
            }

            .cad-product-card {
                flex-direction: column;
            }

            .cad-stats {
                width: 100%;
            }
        }
    </style>


    <div class="cad-page">

        <div class="cad-shell">

            {{-- =====================================================
             TOP BAR
        ====================================================== --}}
            <div class="cad-topbar">

                <div class="cad-heading">

                    <div class="cad-heading-icon">
                        <i class="fa fa-drafting-compass"></i>
                    </div>

                @section('btn')
                    <div>
                        <h5>CAD Drawing</h5>
                        <p>Kelola file CAD, revisi, dan history upload.</p>
                    </div>

                @endsection

            </div>


        </div>


        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">


        {{-- =====================================================
             PRODUCT INFORMATION
        ====================================================== --}}



        {{-- =====================================================
             MAIN CAD LIST
        ====================================================== --}}
        <div class="cad-main-card">

            <div class="cad-main-header">

                <div class="cad-section-title">

                    <div class="cad-section-icon">
                        <i class="fa fa-files-o"></i>
                    </div>

                    <div>

                        <h4>CAD Files</h4>

                        <small>
                            Setiap article memiliki history revisi dan beberapa file upload.
                        </small>

                    </div>

                </div>


                <button type="button" class="btn cad-upload-btn" id="btn-upload-cad">
                    <i class="fa fa-cloud-upload"></i>
                    Upload CAD
                </button>

            </div>


            {{-- SEARCH --}}
            <div class="cad-toolbar">

                <div class="cad-search">

                    <i class="fa fa-search"></i>

                    <input type="text" id="cadSearch" autocomplete="off"
                        placeholder="Search Article, Item, Uploader, Master Sample...">

                </div>

                <div class="cad-result-count">
                    <span id="cadVisibleCount">
                        {{ $cadCollection->count() }}
                    </span>
                    data
                </div>

            </div>


            {{-- TABLE --}}
            <div class="cad-table-container">

                <table class="cad-main-table">

                    <thead>

                        <tr>

                            <th width="45">
                                #
                            </th>

                            <th width="150">
                                Article Code/Nr.
                            </th>
                            <th width="105">
                                History
                            </th>
                            <th width="250">
                                Item Name
                            </th>

                            <th width="155">
                                Master Sample
                            </th>

                            {{-- <th width="105">
                                Revisi
                            </th> --}}



                            <th width="80">
                                Action
                            </th>

                            <th width="180">
                                Uploader
                            </th>

                        </tr>

                    </thead>


                    <tbody id="cadTableBody">

                        @forelse($cads as $i => $cad)
                            @php

                                $articleCode = trim((string) ($cad->article_code ?? ''));

                                /*
                                 * Revision count mengikuti jumlah file CAD
                                 * pada article ini. Contoh: 2 file upload = 2 Revisi.
                                 */
                                $revisionCount = $fileCountByArticle[$articleCode] ?? 0;

                                $itemName = $cad->item_name ?? ($cad->item ?? ($detail['description'] ?? '-'));

                                $uploader = $cad->user->name ?? '-';

                                $initial = strtoupper(substr(trim($uploader) ?: 'U', 0, 1));

                            @endphp


                            <tr class="cad-row"
                                data-search="
                                    {{ strtolower($articleCode . ' ' . $itemName . ' ' . ($cad->master_sample ?? '') . ' ' . $uploader) }}
                                ">

                                {{-- NUMBER --}}
                                <td class="cad-number">
                                    {{ $i + 1 }}
                                </td>


                                {{-- ARTICLE --}}
                                <td class="cad-article-cell">
                                    <span class="cad-article" title="{{ $articleCode ?: '-' }}">
                                        {{ $articleCode ?: '-' }}
                                    </span>
                                </td>

                                <td>

                                    <button type="button" class="btn history-btn btn-history"
                                        data-article="{{ $articleCode }}">

                                        <i class="fa fa-history"></i>

                                        Lihat File

                                    </button>

                                </td>
                                {{-- ITEM --}}
                                <td class="cad-item-cell">

                                    @if ($itemName !== '-')
                                        <div class="cad-item-name" title="{{ $itemName }}">
                                            {{ $itemName }}
                                        </div>
                                    @else
                                        <span class="cad-item-empty">
                                            -
                                        </span>
                                    @endif

                                </td>


                                {{-- MASTER SAMPLE --}}
                                <td class="cad-master-cell">

                                    {{ $cad->master_sample ?? '-' }}

                                </td>


                                {{-- REVISION --}}
                                {{-- <td>

                                    <span class="revision-badge">

                                        <i class="fa fa-code-fork"></i>

                                        {{ $revisionCount }}
                                        Revisi

                                    </span>


                                </td> --}}


                                {{-- HISTORY --}}



                                {{-- ACTION --}}
                                <td>

                                    <button type="button" class="btn delete-btn btn-delete"
                                        data-id="{{ $cad->id }}" title="Delete CAD">

                                        <i class="fa fa-trash"></i>

                                    </button>

                                </td>


                                {{-- UPLOADER --}}
                                <td>

                                    <div class="cad-uploader">

                                        <div class="cad-avatar">
                                            {{ $initial }}
                                        </div>

                                        <div class="cad-uploader-name" title="{{ $uploader }}">
                                            {{ $uploader }}
                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="cad-empty">

                                    <div class="cad-empty-icon">
                                        <i class="fa fa-folder-open-o"></i>
                                    </div>

                                    <strong>
                                        Belum ada CAD
                                    </strong>

                                    <span>
                                        Upload file CAD pertama untuk article ini.
                                    </span>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     DRAWER OVERLAY
========================================================= --}}
<div id="historyOverlay" class="cad-drawer-overlay"></div>


{{-- =========================================================
     HISTORY DRAWER
========================================================= --}}
<div id="historyDrawer" class="cad-drawer">

    <div class="cad-drawer-head">

        <div class="cad-drawer-title">

            <div class="cad-drawer-icon">
                <i class="fa fa-history"></i>
            </div>

            <div>

                <h4>CAD History</h4>

                <small>
                    Riwayat revisi dan file upload.
                </small>

            </div>

        </div>


        <button type="button" class="cad-drawer-close" id="closeDrawer" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>

    </div>


    <div class="cad-drawer-body">

        <div class="cad-selected-article">

            <div class="cad-selected-label">
                ARTICLE CODE / NR
            </div>

            <div id="historyArticle" class="cad-selected-value">
                -
            </div>

        </div>


        <button type="button" id="btn-show-upload" class="btn cad-version-btn">
            <i class="fa fa-upload"></i>
            &nbsp; Upload New Version
        </button>


        {{-- UPLOAD NEW VERSION --}}
        <div id="upload-card" class="cad-upload-card" style="display:none;">

            <div class="cad-form-group">

                <label>
                    FILE CAD
                </label>

                <input type="file" id="drawer-cad-file" class="form-control">

            </div>


            <div class="cad-form-group">

                <label>
                    MASTER SAMPLE
                </label>

                <input type="text" id="drawer-master-sample" class="form-control"
                    placeholder="Master Sample / Ukuran">

            </div>


            <div style="display:flex;gap:6px;">

                <button type="button" id="btn-upload-version" class="btn btn-success btn-sm">
                    <i class="fa fa-upload"></i>
                    Upload
                </button>

                <button type="button" id="btn-cancel-upload" class="btn btn-default btn-sm">
                    Cancel
                </button>

            </div>


            <div id="upload-wrapper" class="progress" style="display:none;height:7px;margin-top:12px;">

                <div id="upload-progress" class="progress-bar progress-bar-striped progress-bar-animated"
                    style="width:0%;">
                </div>

            </div>

        </div>


        <div id="historyContent" style="margin-top:13px;">
        </div>

    </div>

</div>


{{-- =========================================================
     UPLOAD CAD MODAL
========================================================= --}}
<div id="modal-upload">

    <div class="cad-upload-modal">

        <div class="cad-upload-modal-head">

            <div>

                <h4>
                    Upload CAD
                </h4>

                <small>
                    Tambahkan file CAD baru.
                </small>

            </div>

            <button type="button" class="cad-modal-close" id="btn-close-modal">
                <i class="fa fa-times"></i>
            </button>

        </div>


        <div class="cad-upload-modal-body">

            <div class="cad-form-group">

                <label>
                    FILE CAD
                </label>

                <input type="file" id="cad-file" class="form-control">

            </div>


            <div class="cad-form-group">

                <label>
                    MASTER SAMPLE / UKURAN
                </label>

                <input type="text" id="master-sample" class="form-control" placeholder="Contoh: 60 x 40 x 30 cm">

            </div>


            <div class="cad-progress">

                <div class="cad-progress-track">

                    <div id="progress-bar"></div>

                </div>

                <div class="cad-progress-label">

                    <span>
                        Uploading...
                    </span>

                    <span id="progress-percent">
                        0%
                    </span>

                </div>

            </div>

        </div>


        <div class="cad-upload-modal-footer">

            <button type="button" class="btn btn-default" id="btn-cancel-main-upload">
                Batal
            </button>

            <button type="button" class="btn btn-success" id="btn-submit-upload">
                <i class="fa fa-cloud-upload"></i>
                Upload
            </button>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1700,
        timerProgressBar: true
    });

    let selectedRow = null;
    let currentArticle = null;


    /* =========================================================
       SEARCH
    ========================================================= */

    $(document).on('input', '#cadSearch', function() {

        const keyword = $(this).val().toLowerCase().trim();

        let visible = 0;

        $('.cad-row').each(function() {

            const searchText =
                ($(this).attr('data-search') || '')
                .toLowerCase();

            const match =
                keyword === '' ||
                searchText.includes(keyword);

            $(this).toggle(match);

            if (match) {
                visible++;
            }

        });

        $('#cadVisibleCount').text(visible);

    });


    /* =========================================================
       MAIN UPLOAD MODAL
    ========================================================= */

    function openCadUploadModal() {

        $('#modal-upload')
            .css('display', 'flex')
            .hide()
            .fadeIn(130);

        $('body').css('overflow', 'hidden');

    }


    function closeCadUploadModal() {

        $('#modal-upload').fadeOut(120, function() {

            $(this).css('display', 'none');

        });

        $('body').css('overflow', '');

    }


    $(document).on('click', '#btn-upload-cad', function(e) {

        e.preventDefault();

        openCadUploadModal();

    });


    $(document).on(
        'click',
        '#btn-close-modal, #btn-cancel-main-upload',
        function(e) {

            e.preventDefault();

            closeCadUploadModal();

        }
    );


    $(document).on('click', '#modal-upload', function(e) {

        if (e.target === this) {

            closeCadUploadModal();

        }

    });


    $(document).on('keydown', function(e) {

        if (
            e.key === 'Escape' &&
            $('#modal-upload').is(':visible')
        ) {

            closeCadUploadModal();

        }

    });


    /* =========================================================
       MAIN CAD UPLOAD
       Existing route preserved:
       POST /cad/upload
    ========================================================= */

    $(document).on('click', '#btn-submit-upload', function() {

        let fileInput = $('#cad-file')[0];

        let file =
            fileInput &&
            fileInput.files ?
            fileInput.files[0] :
            null;

        if (!file) {

            Toast.fire({
                icon: 'warning',
                title: 'Pilih file CAD terlebih dahulu.'
            });

            return;

        }


        let btn = $(this);


        let formData = new FormData();

        formData.append(
            'file',
            file
        );

        formData.append(
            'article_code',
            @json($articleFallback)
        );

        formData.append(
            'master_sample',
            $('#master-sample').val()
        );

        formData.append(
            '_token',
            @json(csrf_token())
        );


        btn.prop('disabled', true);

        $('.cad-progress').show();

        $('#progress-bar')
            .css('width', '0%');

        $('#progress-percent')
            .text('0%');


        $.ajax({

            url: '/cad/upload',

            method: 'POST',

            data: formData,

            contentType: false,

            processData: false,


            xhr: function() {

                let xhr =
                    new window.XMLHttpRequest();


                xhr.upload.addEventListener(
                    'progress',
                    function(e) {

                        if (!e.lengthComputable) {
                            return;
                        }

                        let percent =
                            Math.round(
                                (e.loaded / e.total) * 100
                            );


                        $('#progress-bar')
                            .css(
                                'width',
                                percent + '%'
                            );


                        $('#progress-percent')
                            .text(
                                percent + '%'
                            );

                    }
                );


                return xhr;

            },


            success: function(res) {

                Toast.fire({
                    icon: 'success',
                    title: 'CAD berhasil diupload.'
                });


                closeCadUploadModal();


                $('#cad-file').val('');
                $('#master-sample').val('');


                $('.cad-progress').hide();


                $('#progress-bar')
                    .css('width', '0%');


                $('#progress-percent')
                    .text('0%');


                setTimeout(function() {
                    location.reload();
                }, 500);

            },


            error: function(xhr) {

                console.error(
                    xhr.responseText
                );


                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message ||
                        'Upload CAD gagal.'
                });

            },


            complete: function() {

                btn.prop('disabled', false);

            }

        });

    });


    /* =========================================================
       HISTORY DRAWER
    ========================================================= */

    function openHistoryDrawer(article, row) {

        currentArticle = article;

        selectedRow = row;


        $('.cad-row')
            .removeClass('cad-row-active');


        if (selectedRow) {

            selectedRow.addClass(
                'cad-row-active'
            );

        }


        $('#historyArticle')
            .text(article || '-');


        $('#historyOverlay')
            .addClass('show');


        $('#historyDrawer')
            .addClass('show');


        $('body')
            .css('overflow', 'hidden');


        loadHistory(article);

    }


    function closeHistoryDrawer() {

        $('#historyDrawer')
            .removeClass('show');


        $('#historyOverlay')
            .removeClass('show');


        $('.cad-row')
            .removeClass('cad-row-active');


        $('body')
            .css('overflow', '');


        $('#upload-card')
            .hide();


        $('#btn-show-upload')
            .show();


        $('#drawer-cad-file')
            .val('');


        $('#drawer-master-sample')
            .val('');

    }


    /* =========================================================
       HISTORY BUTTON
    ========================================================= */

    $(document).on(
        'click',
        '.btn-history',
        function(e) {

            e.preventDefault();

            e.stopPropagation();


            let article =
                $(this).data('article');


            let row =
                $(this).closest('.cad-row');


            openHistoryDrawer(
                article,
                row
            );

        }
    );


    /* =========================================================
       CLOSE DRAWER
    ========================================================= */

    $(document).on(
        'click',
        '#closeDrawer, #historyOverlay',
        function() {

            closeHistoryDrawer();

        }
    );


    $(document).on('keydown', function(e) {

        if (
            e.key === 'Escape' &&
            $('#historyDrawer').hasClass('show')
        ) {

            closeHistoryDrawer();

        }

    });


    /* =========================================================
       SHOW UPLOAD NEW VERSION
    ========================================================= */

    $(document).on(
        'click',
        '#btn-show-upload',
        function() {

            $('#upload-card')
                .slideDown(180);

            $(this).hide();

        }
    );


    /* =========================================================
       CANCEL NEW VERSION
    ========================================================= */

    $(document).on(
        'click',
        '#btn-cancel-upload',
        function() {

            $('#upload-card')
                .slideUp(150);


            $('#btn-show-upload')
                .show();


            $('#drawer-cad-file')
                .val('');


            $('#drawer-master-sample')
                .val('');

        }
    );


    /* =========================================================
       UPLOAD NEW VERSION
       Existing route preserved:
       POST /cad/upload
    ========================================================= */

    $(document).on(
        'click',
        '#btn-upload-version',
        function() {

            let fileInput =
                $('#drawer-cad-file')[0];


            let file =
                fileInput &&
                fileInput.files ?
                fileInput.files[0] :
                null;


            if (!file) {

                Toast.fire({
                    icon: 'warning',
                    title: 'Pilih file terlebih dahulu.'
                });

                return;

            }


            if (!currentArticle) {

                Toast.fire({
                    icon: 'warning',
                    title: 'Article belum dipilih.'
                });

                return;

            }


            let btn = $(this);


            let formData =
                new FormData();


            formData.append(
                'file',
                file
            );


            formData.append(
                'article_code',
                currentArticle
            );


            formData.append(
                'master_sample',
                $('#drawer-master-sample').val()
            );


            formData.append(
                '_token',
                @json(csrf_token())
            );


            btn.prop(
                'disabled',
                true
            );


            $('#upload-wrapper')
                .show();


            $('#upload-progress')
                .css(
                    'width',
                    '0%'
                );


            $.ajax({

                url: '/cad/upload',

                method: 'POST',

                data: formData,

                contentType: false,

                processData: false,


                xhr: function() {

                    let xhr =
                        new window.XMLHttpRequest();


                    xhr.upload.addEventListener(
                        'progress',
                        function(e) {

                            if (!e.lengthComputable) {
                                return;
                            }


                            let percent =
                                Math.round(
                                    (e.loaded / e.total) * 100
                                );


                            $('#upload-progress')
                                .css(
                                    'width',
                                    percent + '%'
                                );

                        }
                    );


                    return xhr;

                },


                success: function(res) {

                    Toast.fire({
                        icon: 'success',
                        title: 'CAD version berhasil diupload.'
                    });


                    $('#upload-card')
                        .slideUp(150);


                    $('#btn-show-upload')
                        .show();


                    $('#drawer-cad-file')
                        .val('');


                    $('#drawer-master-sample')
                        .val('');


                    $('#upload-wrapper')
                        .hide();


                    $('#upload-progress')
                        .css(
                            'width',
                            '0%'
                        );


                    loadHistory(
                        currentArticle
                    );

                },


                error: function(xhr) {

                    console.error(
                        xhr.responseText
                    );


                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message ||
                            'Upload failed.'
                    });

                },


                complete: function() {

                    btn.prop(
                        'disabled',
                        false
                    );

                }

            });

        }
    );


    /* =========================================================
       LOAD HISTORY
       Existing route preserved:
       GET /cad/history/{article}
    ========================================================= */

    function loadHistory(article) {

        $('#historyContent')
            .html(`
            <div class="history-loading">
                <i class="fa fa-spinner fa-spin"></i>
                &nbsp; Loading CAD history...
            </div>
        `);


        $.get(
                '/cad/history/' + encodeURIComponent(article),
                function(res) {

                    let rows =
                        Array.isArray(res) ?
                        res : [];


                    if (!rows.length) {

                        $('#historyContent')
                            .html(`
                        <div class="history-empty">
                            <i class="fa fa-folder-open-o"
                               style="font-size:22px;margin-bottom:8px;"></i>

                            <div>
                                Belum ada history CAD.
                            </div>
                        </div>
                    `);

                        return;

                    }


                    /*
                     * Group by version.
                     * Satu revisi dapat memiliki beberapa file.
                     */
                    let grouped = {};


                    rows.forEach(function(row) {

                        let version =
                            row.version ?? 1;


                        if (!grouped[version]) {

                            grouped[version] = [];

                        }


                        grouped[version].push(row);

                    });


                    let versions =
                        Object.keys(grouped)
                        .sort(
                            (a, b) =>
                            Number(b) - Number(a)
                        );


                    let html = `
                <div class="history-list">
            `;


                    versions.forEach(
                        function(version, versionIndex) {

                            let files =
                                grouped[version];


                            let latest =
                                versionIndex === 0;


                            html += `
                        <div class="history-item">

                            <div class="history-item-top">

                                <div style="display:flex;align-items:center;gap:8px;">

                                    <span class="history-version ${latest ? 'latest' : ''}">
                                        V${version}
                                    </span>

                                    <div>
                                        <div style="
                                            font-size:11px;
                                            font-weight:750;
                                            color:#344054;
                                        ">
                                            Revisi ${version}
                                        </div>

                                        <div style="
                                            margin-top:2px;
                                            color:#98a2b3;
                                            font-size:9px;
                                        ">
                                            ${files.length}
                                            file${files.length > 1 ? 's' : ''}
                                        </div>
                                    </div>

                                </div>

                                ${
                                    latest
                                    ? `
                                        <span style="
                                            padding:4px 7px;
                                            border-radius:20px;
                                            background:#dcfce7;
                                            color:#15803d;
                                            font-size:8px;
                                            font-weight:800;
                                        ">
                                            LATEST
                                        </span>
                                    `
                                    : ''
                                }

                            </div>

                            <div style="margin-top:9px;">
                    `;


                            files.forEach(
                                function(row) {

                                    let filePath =
                                        row.file_path || '';


                                    let fileName =
                                        filePath
                                        .split('/')
                                        .pop() ||
                                        'CAD File';


                                    html += `
                                <div
                                    class="history-file"
                                    style="
                                        padding:8px;
                                        border:1px solid #f0f2f5;
                                        border-radius:8px;
                                        margin-bottom:6px;
                                    "
                                >

                                    <div class="history-file-icon">
                                        <i class="fa fa-file-o"></i>
                                    </div>

                                    <div
                                        class="history-file-name"
                                        title="${escapeHtml(fileName)}"
                                    >
                                        ${escapeHtml(fileName)}
                                    </div>

                                    <div class="history-actions"
                                         style="margin-top:0;">

                                        <a
                                            href="/storage/${encodeURI(filePath)}"
                                            target="_blank"
                                            class="btn btn-info btn-xs"
                                        >
                                            <i class="fa fa-eye"></i>
                                            View
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-xs btn-delete-history"
                                            data-id="${row.id}"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>

                                    </div>

                                </div>
                            `;

                                }
                            );


                            /*
                             * Meta informasi revisi
                             */
                            let first =
                                files[0] || {};


                            html += `

                            <div class="history-meta">

                                <span>
                                    <i class="fa fa-info-circle"></i>
                                    Status:
                                    ${escapeHtml(first.status ?? '-')}
                                </span>

                                <span>
                                    <i class="fa fa-cube"></i>
                                    Master:
                                    ${escapeHtml(first.master_sample ?? '-')}
                                </span>

                            </div>

                        </div>

                    `;

                        }
                    );


                    html += `
                </div>
            `;


                    $('#historyContent')
                        .html(html);

                }
            )
            .fail(function(xhr) {

                console.error(
                    xhr.responseText
                );


                $('#historyContent')
                    .html(`
                <div class="history-empty"
                     style="color:#dc2626;">
                    <i class="fa fa-exclamation-circle"></i>
                    &nbsp; Gagal mengambil history CAD.
                </div>
            `);

            });

    }


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }
</script>

@endsection
