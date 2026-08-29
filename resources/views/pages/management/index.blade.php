@extends('master.master')

@section('title', 'Production Monitoring')
<style>

/* =========================================================
   PRODUCTION MONITORING
   CLEAN UI — REPLACE ALL OLD CSS
   ========================================================= */

.mn-erp-page {
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --success: #16a34a;
    --danger: #dc2626;
    --text: #172033;
    --muted: #667085;
    --border: #e2e8f0;
    --soft: #f8fafc;
    --header: #2c3e50;

    color: var(--text);
    font-size: 13px;
    line-height: 1.4;
    padding: 10px 12px 30px;

    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.mn-erp-page *,
.mn-erp-page *::before,
.mn-erp-page *::after {
    box-sizing: border-box;
}


/* =========================================================
   FILTER
   ========================================================= */

.mn-filter {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 14px !important;
    box-shadow: 0 2px 10px rgba(16,24,40,.04);

    /*
    |--------------------------------------------------------------------------
    | STICKY FILTER / PENCARIAN
    |--------------------------------------------------------------------------
    | Filter tetap terlihat ketika halaman di-scroll jauh ke bawah.
    | Tidak mengubah fungsi search, company, sort, AJAX, maupun reset.
    */
    position: sticky;
    top: 50px;
    z-index: 100;
}

.mn-toolbar {
    display: grid;
    grid-template-columns: minmax(280px, 1fr) auto auto auto;
    align-items: end;
    gap: 10px;
}

.mn-field-label {
    margin: 0 0 5px 2px;
    color: var(--muted);
    font-size: 10px;
    line-height: 1.2;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.mn-search {
    min-width: 0;
}

.mn-search-box {
    height: 42px;
    display: flex;
    align-items: center;
    padding: 0 11px;

    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;

    transition: .18s ease;
}

.mn-search-box:focus-within {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37,99,235,.07);
}

.mn-search-box > i {
    margin-right: 8px;
    color: #98a2b3;
    font-size: 13px;
}

.mn-search-box input {
    width: 100%;
    min-width: 0;

    border: 0;
    outline: 0;
    background: transparent;

    color: var(--text);
    font-size: 12px;
}

.mn-search-box input::placeholder {
    color: #98a2b3;
}

.mn-search-clear {
    width: 27px;
    height: 27px;

    flex: 0 0 27px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 0;
    border-radius: 6px;

    background: #eef2f6;
    color: #667085;

    cursor: pointer;
}

.mn-search-help {
    margin: 4px 0 0 2px;
    color: #98a2b3;
    font-size: 12px;
}

.mn-search-help i {
    margin-right: 3px;
}


/* =========================================================
   BRAND FILTER
   ========================================================= */

.mn-brand-filter,
.mn-sort,
.mn-actions {
    flex-shrink: 0;
}

.mn-brand-group {
    height: 42px;

    display: inline-flex;
    align-items: center;

    padding: 3px;

    border: 1px solid var(--border);
    border-radius: 8px;

    background: var(--soft);
}

.mn-brand-btn {
    height: 34px;
    min-width: 48px;

    padding: 0 11px;

    border: 0;
    border-radius: 6px;

    background: transparent;
    color: #667085;

    font-size: 10px;
    font-weight: 700;

    cursor: pointer;
    user-select: none;

    transition: .15s ease;
}

.mn-brand-btn:hover {
    color: #344054;
    background: #fff;
}

.mn-brand-btn.active {
    color: var(--primary);
    background: #fff;
    box-shadow: 0 1px 4px rgba(16,24,40,.08);
}


/* =========================================================
   SORT
   ========================================================= */

.mn-sort-btn {
    height: 42px;
    min-width: 88px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;
    padding: 0 10px;

    border: 1px solid var(--border);
    border-radius: 8px;

    background: #fff;
    color: #475467;

    font-size: 10px;
    font-weight: 700;

    cursor: pointer;
}

.mn-sort-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.mn-sort-icon {
    width: 24px;
    height: 24px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 6px;

    background: #eff6ff;
    color: var(--primary);

    font-size: 9px;
}

.mn-sort-value {
    min-width: 30px;
    text-align: center;
}


/* =========================================================
   ACTION BUTTON
   ========================================================= */

.mn-actions {
    display: flex;
    align-items: end;
    gap: 6px;
}

.btn-mn-filter {
    height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 6px;
    padding: 0 15px;

    border: 0;
    border-radius: 8px;

    background: var(--primary);
    color: #fff;

    font-size: 10px;
    font-weight: 700;

    cursor: pointer;
}

.btn-mn-filter:hover {
    background: var(--primary-hover);
}

.btn-mn-reset {
    width: 42px;
    height: 42px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid var(--border);
    border-radius: 8px;

    background: #fff;
    color: #667085;

    text-decoration: none;
}

.btn-mn-reset:hover {
    background: #f8fafc;
    color: #344054;
}


/* =========================================================
   AJAX
   ========================================================= */

#monitoringResult {
    position: relative;
    min-height: 40px;
    transition: opacity .18s ease;
}

#monitoringResult.is-loading {
    opacity: .45;
    pointer-events: none;
}

.mn-ajax-loader {
    position: fixed;
    inset: 0;

    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(248,250,252,.45);
    backdrop-filter: blur(2px);

    pointer-events: none;
}

.mn-ajax-loader-card {
    display: inline-flex;
    align-items: center;

    gap: 9px;
    padding: 10px 14px;

    border: 1px solid var(--border);
    border-radius: 9px;

    background: #fff;
    color: #475467;

    box-shadow: 0 10px 35px rgba(16,24,40,.1);

    font-size: 11px;
    font-weight: 600;
}

.mn-ajax-loader-card .spinner-border {
    width: 17px;
    height: 17px;
    border-width: 2px;
}

.mn-filter-error {
    margin-bottom: 14px;
    padding: 11px 13px;

    border: 1px solid #fecaca;
    border-radius: 8px;

    background: #fff7f7;
    color: #b91c1c;

    font-size: 11px;
}


/* =========================================================
   PO CARD
   ========================================================= */

.mn-card {
    overflow: hidden;

    margin-bottom: 14px !important;

    border: 1px solid var(--border);
    border-radius: 10px;

    background: #fff;

    box-shadow: 0 2px 10px rgba(16,24,40,.035);
}

.mn-header {
    min-height: 48px;

    display: flex;
    align-items: center;

    padding: 8px 13px;

    background: #fff !important;
    color: var(--text) !important;

    border-bottom: 1px solid #e8edf2;
}

.mn-header h5,
.mn-header h6 {
    margin: 0;

    font-size: 13px;
    line-height: 1.35;
    font-weight: 750;
}

.mn-header h6 span {
    color: var(--muted);
    font-weight: 500;
}

.btn-toggle-po {
    width: 29px;
    height: 29px;

    padding: 0;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #dbe2ea;
    border-radius: 6px;

    background: #fff;
    color: #667085;

    font-size: 9px;
}

.btn-toggle-po:hover {
    background: #f8fafc;
    color: var(--primary);
}


/* =========================================================
   TABLE WRAPPER
   ========================================================= */

.table-responsive {
    width: 100%;

    overflow-x: auto;
    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;

    scrollbar-width: thin;
}


/* =========================================================
   MAIN MONITORING TABLE
   ========================================================= */

.mn-table {
    width: 100% !important;

    /*
     * Jangan terlalu kecil.
     * Ini membuat kolom Item punya ruang yang cukup.
     */
    min-width: 1380px;

    margin-bottom: 0;

    table-layout: fixed;

    border-collapse: separate;
    border-spacing: 0;

    font-size: 12px;
}

.mn-table th,
.mn-table td {
    white-space: nowrap;
}


/* =========================================================
   HEADER TABLE
   ========================================================= */

.mn-table thead tr:first-child {
    background: #2c3e50;
    color: #fff;
}

.mn-table thead tr:nth-child(2) {
    background: #f1f5f9;
    color: #344054;
}

.mn-table thead th {
    position: sticky;
    top: 0;

    z-index: 5;

    padding: 8px 6px;

    border-color: #dfe5ec;

    font-size: 10px;
    font-weight: 750;
    line-height: 1.2;

    vertical-align: middle;
    text-align: center;
}


/* =========================================================
   BODY TABLE
   ========================================================= */

.mn-table tbody td {
    padding: 8px 6px;

    border-color: #edf0f4;

    color: #344054;

    font-size: 12px;
    line-height: 1.3;

    vertical-align: middle;
}

.mn-table tbody tr {
    transition: background .15s ease;
}

.mn-table tbody tr:hover {
    background: #fbfdff;
}


/* =========================================================
   COLUMN: IMAGE
   ========================================================= */

.mn-table th:first-child,
.mn-table td:first-child {
    width: 82px;
    min-width: 82px;
    max-width: 82px;

    text-align: center;
}


/* =========================================================
   COLUMN: QTY
   ========================================================= */

.mn-table th:nth-child(2),
.mn-table td:nth-child(2) {
    width: 72px;
    min-width: 72px;
    max-width: 72px;

    text-align: center;
}


/* =========================================================
   COLUMN: ITEM / NAME
   ========================================================= */

/*
 * Ini bagian terpenting untuk masalah screenshot.
 *
 * Item dibuat cukup lebar sehingga:
 *
 * Rattan Thermos/
 * Water Jug
 *
 * bukan:
 *
 * Ratta
 * n
 * Ther
 * mos
 * /
 * Wate
 * r
 * Jug
 */

.mn-table th:nth-child(3),
.mn-table td:nth-child(3) {
    width: 190px;
    min-width: 190px;
    max-width: 190px;
}

.item-col {
    width: 190px !important;
    min-width: 190px !important;
    max-width: 190px !important;

    text-align: left !important;

    white-space: normal !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;

    vertical-align: middle;
}

.item-link,
.item-name {
    display: block !important;

    width: 100% !important;
    max-width: 100% !important;

    margin: 0;

    overflow: hidden !important;

    text-overflow: ellipsis !important;

    white-space: normal !important;

    /*
     * Jangan pernah gunakan break-all / anywhere.
     */
    word-break: normal !important;
    overflow-wrap: break-word !important;

    line-height: 1.35;
}

.item-link {
    color: var(--primary);

    font-size: 12px;
    font-weight: 700;

    text-decoration: none;
}

.item-link:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.item-name {
    color: #344054;

    font-size: 12px;
    font-weight: 600;

    display: -webkit-box !important;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;

    overflow: hidden !important;
}


/* =========================================================
   IMAGE
   ========================================================= */

.product-image {
    width: 64px;
    height: 64px;

    padding: 3px;

    object-fit: cover;

    border-radius: 8px;
    border: 1px solid #e4e7ec;

    background: #fff;
}


/* =========================================================
   QTY BADGE
   ========================================================= */

.qty-col {
    width: 72px;
    min-width: 72px;
    max-width: 72px;

    text-align: center;
}

.qty-badge {
    min-width: 42px;

    display: inline-block;

    padding: 4px 7px;

    border-radius: 6px;

    background: #eff6ff;
    color: #344054;

    font-size: 11px;
    font-weight: 650;

    text-align: center;
}


/* =========================================================
   STATUS COLUMNS
   ========================================================= */

.status-col {
    width: 72px;
    min-width: 72px;
    max-width: 72px;

    padding-left: 4px !important;
    padding-right: 4px !important;

    text-align: center;
}

.pass-box,
.reject-box {
    font-size: 12px;
    font-weight: 700;
}

.pass-box {
    color: var(--success);
}

.reject-box {
    color: var(--danger);
}


/* =========================================================
   SPK DETAIL
   Hanya label kecil di bawah nilai monitoring.
   ========================================================= */

.spk-under-value {
    margin-top: 2px;

    line-height: 1;

    text-align: center;
}

.spk-under-value-line {
    display: flex;

    justify-content: center;
    align-items: center;

    white-space: nowrap;

    font-size: 7px;

    color: #98a2b3;
}

.spk-hover-target {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    min-height: 18px;
    padding: 1px 5px;
    border-radius: 4px;
    cursor: pointer;
}

.spk-hover-target:hover {
    background: #f2f4f7;
}

/* =========================================================
   LARGE SPK LIST TOOLTIP
   ========================================================= */
.spk-list-tooltip {
    display: none !important;
}

/* =========================================================
   GLOBAL SPK TOOLTIP
   ========================================================= */
.spk-global-tooltip {
    position: fixed !important;
    left: -9999px;
    top: -9999px;
    z-index: 2147483647 !important;
    width: min(680px, calc(100vw - 24px));
    max-height: min(430px, calc(100vh - 24px));
    overflow: hidden;
    padding: 12px;
    border: 1px solid #e4e7ec;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 16px 40px rgba(16, 24, 40, .20);
    color: #344054;
    text-align: left;
    opacity: 0;
    visibility: hidden;
    pointer-events: auto;
    transform: none !important;
    transition: opacity .12s ease, visibility .12s ease;
}

.spk-global-tooltip.visible {
    opacity: 1;
    visibility: visible;
}

.spk-global-tooltip .spk-list-tooltip-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 9px;
}

.spk-global-tooltip .spk-list-tooltip-title strong {
    font-size: 11px;
    font-weight: 800;
    color: #101828;
}

.spk-global-tooltip .spk-list-tooltip-count {
    padding: 3px 7px;
    border-radius: 999px;
    background: #f2f4f7;
    color: #667085;
    font-size: 8px;
    font-weight: 700;
    white-space: nowrap;
}

.spk-global-tooltip .spk-list-tooltip-scroll {
    display: block;
    max-height: 360px;
    overflow: auto;
    border: 1px solid #eaecf0;
    border-radius: 7px;
}

.spk-global-tooltip .spk-list-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 9px;
    background: #fff;
}

.spk-global-tooltip .spk-list-table th {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 7px 8px;
    border-bottom: 1px solid #d0d5dd;
    background: #fff9a8;
    color: #344054;
    font-size: 8px;
    font-weight: 800;
    text-align: left;
    white-space: nowrap;
}

.spk-global-tooltip .spk-list-table td {
    padding: 7px 8px;
    border-bottom: 1px solid #f0f2f5;
    color: #475467;
    vertical-align: middle;
    white-space: nowrap;
}

.spk-global-tooltip .spk-list-table tbody tr:hover td {
    background: #f9fafb;
}

.spk-global-tooltip .spk-list-table .col-no {
    width: 28px;
    text-align: center;
}

.spk-global-tooltip .spk-list-table .col-spk {
    min-width: 190px;
}

.spk-global-tooltip .spk-list-table .col-sub {
    min-width: 120px;
}

.spk-global-tooltip .spk-list-table .col-category {
    min-width: 140px;
}
.spk-global-tooltip .spk-list-table .col-description {
    min-width: 190px;
    max-width: 280px;
    white-space: normal;
    word-break: break-word;
    line-height: 1.35;
}


.spk-global-tooltip.metric-in .spk-list-tooltip-title strong {
    color: #067647;
}

.spk-global-tooltip.metric-pass .spk-list-tooltip-title strong {
    color: #175cd3;
}

.spk-global-tooltip.metric-in .spk-list-table .col-total {
    width: 70px;
    text-align: right;
    font-weight: 800;
    color: #101828;
}

.spk-global-tooltip.metric-pass .spk-list-table .col-total {
    width: 70px;
    text-align: right;
    font-weight: 800;
    color: #175cd3;
}

.spk-global-tooltip .spk-list-table .spk-link {
    color: #175cd3;
    font-weight: 700;
    text-decoration: none;
}

.spk-global-tooltip .spk-list-table .spk-link:hover {
    text-decoration: underline;
}

.spk-global-tooltip .exception-badge {
    display: inline-block;
    margin-left: 4px;
    padding: 2px 4px;
    border-radius: 3px;
    background: #fff3cd;
    color: #946200;
    font-size: 7px;
    font-weight: 800;
}

.spk-list-tooltip-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 9px;
}

.spk-list-tooltip-title strong {
    font-size: 11px;
    font-weight: 800;
    color: #101828;
}

.spk-list-tooltip-count {
    padding: 3px 7px;
    border-radius: 999px;
    background: #f2f4f7;
    color: #667085;
    font-size: 8px;
    font-weight: 700;
    white-space: nowrap;
}

.spk-list-tooltip-scroll {
    max-height: 360px;
    overflow: auto;
    border: 1px solid #eaecf0;
    border-radius: 7px;
}

.spk-list-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 9px;
    background: #fff;
}

.spk-list-table th {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 7px 8px;
    border-bottom: 1px solid #d0d5dd;
    background: #fff9a8;
    color: #344054;
    font-size: 8px;
    font-weight: 800;
    text-align: left;
    white-space: nowrap;
}

.spk-list-table td {
    padding: 7px 8px;
    border-bottom: 1px solid #f0f2f5;
    color: #475467;
    vertical-align: middle;
    white-space: nowrap;
}

.spk-list-table tbody tr:last-child td {
    border-bottom: 0;
}

.spk-list-table tbody tr:hover td {
    background: #f9fafb;
}

.spk-list-table .col-no {
    width: 28px;
    text-align: center;
}

.spk-list-table .col-spk {
    min-width: 190px;
}

.spk-list-table .col-sub {
    min-width: 120px;
}

.spk-list-table .col-category {
    min-width: 100px;
}

.spk-list-table .col-total {
    width: 70px;
    text-align: right;
    font-weight: 800;
    color: #101828;
}

.spk-list-table .col-pass {
    width: 65px;
    text-align: right;
    font-weight: 800;
    color: #039855;
}

.spk-list-table .col-reject {
    width: 65px;
    text-align: right;
    font-weight: 800;
    color: #d92d20;
}

.spk-list-table .spk-link {
    color: #175cd3;
    font-weight: 700;
    text-decoration: none;
}

.spk-list-table .spk-link:hover {
    text-decoration: underline;
}

.spk-list-table .exception-badge {
    display: inline-block;
    margin-left: 4px;
    padding: 2px 4px;
    border-radius: 3px;
    background: #fff3cd;
    color: #946200;
    font-size: 7px;
    font-weight: 800;
}

.spk-list-tooltip-empty {
    padding: 14px;
    color: #98a2b3;
    font-size: 9px;
    text-align: center;
}
/* =========================================================
   EMPTY STATE
   ========================================================= */

.mn-empty {
    padding: 40px 20px;

    border: 1px solid var(--border);
    border-radius: 10px;

    background: #fff;

    text-align: center;
}

.mn-empty h5 {
    margin: 0 0 5px;

    font-size: 14px;
    font-weight: 750;
}

.mn-empty .text-muted {
    font-size: 10px;

    color: #98a2b3 !important;
}


/* =========================================================
   MODAL
   ========================================================= */

.modal .modal-content {
    overflow: hidden;

    border: 1px solid var(--border);
    border-radius: 10px;

    box-shadow: 0 18px 60px rgba(15,23,42,.16);
}

.modal .modal-header {
    min-height: 48px;

    padding: 9px 13px;

    background: #fff !important;
    color: var(--text) !important;

    border-bottom: 1px solid #edf0f4;
}

.modal .modal-title {
    margin: 0;

    font-size: 13px;
    font-weight: 750;
}

.modal .modal-body {
    padding: 13px;

    background: #fff;
}

.modal .modal-footer {
    padding: 9px 13px;

    border-top: 1px solid #edf0f4;

    background: #fff;
}

.modal .card {
    overflow: hidden;

    border: 1px solid var(--border) !important;
    border-radius: 8px !important;

    box-shadow: 0 1px 5px rgba(16,24,40,.035) !important;
}

.modal .card-body {
    padding: 11px !important;
}

.modal .table {
    margin-bottom: 0;

    font-size: 11px;
}

.modal .table th,
.modal .table td {
    padding: 6px;

    border-color: #edf0f4;

    vertical-align: middle;
}

.modal .form-control,
.modal .form-select {
    min-height: 35px;
    height: 35px;

    padding: 6px 8px;

    border: 1px solid #dfe3e8;
    border-radius: 6px;

    color: #344054;

    font-size: 11px;
}

.modal .btn {
    min-height: 33px;
    height: 33px;

    padding: 0 11px;

    border-radius: 6px;

    font-size: 10px;
    font-weight: 650;
}


/* =========================================================
   FOCUS
   ========================================================= */

.mn-brand-btn:focus-visible,
.mn-sort-btn:focus-visible,
.btn-mn-filter:focus-visible,
.btn-mn-reset:focus-visible,
.mn-search-clear:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
}


/* =========================================================
   LOADING
   ========================================================= */

@keyframes mnSpin {
    to {
        transform: rotate(360deg);
    }
}

.mn-sort-btn.is-loading,
.mn-brand-btn.is-loading {
    pointer-events: none;
    opacity: .65;
}

.mn-sort-btn.is-loading .mn-sort-icon {
    animation: mnSpin .55s linear infinite;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1200px) {

    .mn-toolbar {
        grid-template-columns:
            minmax(250px, 1fr)
            auto
            auto;
    }

    .mn-search {
        grid-column: 1 / -1;
    }

    .mn-table {
        min-width: 1380px;
    }
}


@media (max-width: 900px) {

    .mn-toolbar {
        grid-template-columns: 1fr 1fr;
    }

    .mn-search {
        grid-column: 1 / -1;
    }

    .mn-actions {
        grid-column: 1 / -1;
    }

    .mn-brand-group,
    .mn-sort-btn {
        width: 100%;
    }

    .mn-brand-btn {
        flex: 1;
        min-width: 0;
    }

    .mn-actions {
        width: 100%;
    }

    .btn-mn-filter {
        flex: 1;
    }

    .mn-table {
        min-width: 1350px;
    }

    .mn-table th:nth-child(3),
    .mn-table td:nth-child(3),
    .item-col {
        width: 180px !important;
        min-width: 180px !important;
        max-width: 180px !important;
    }
}


@media (max-width: 768px) {

    .mn-erp-page {
        padding: 6px 6px 20px;

        font-size: 12px;
    }

    .mn-toolbar {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .mn-search,
    .mn-actions {
        grid-column: 1 / -1;
    }

    .mn-brand-group,
    .mn-sort-btn {
        width: 100%;
    }

    .mn-actions {
        width: 100%;
    }

    .btn-mn-filter {
        flex: 1;
    }

    .mn-table {
        min-width: 1300px;
    }

    .mn-table th:nth-child(3),
    .mn-table td:nth-child(3),
    .item-col {
        width: 170px !important;
        min-width: 170px !important;
        max-width: 170px !important;
    }

    .product-image {
        width: 58px;
        height: 58px;
    }
}


@media (max-width: 576px) {

    .mn-filter {
        padding: 11px;
    }

    .mn-field-label {
        font-size: 9px;
    }

    .mn-brand-btn,
    .mn-sort-btn,
    .btn-mn-filter {
        font-size: 9px;
    }

    .mn-table thead th {
        font-size: 9px;
    }

    .mn-table tbody td {
        font-size: 11px;
    }

    .mn-table th:nth-child(3),
    .mn-table td:nth-child(3),
    .item-col {
        width: 160px !important;
        min-width: 160px !important;
        max-width: 160px !important;
    }

    .item-link,
    .item-name {
        font-size: 11px;
    }

    .product-image {
        width: 54px;
        height: 54px;
    }
}

</style>
@section('content')
    @section('btn')
<div>
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-boxes text-primary me-5"></i>
                        Production Monitoring
                    </h4>
                    <small class="text-muted">
                        
                    </small>
                </div>    
    @endsection
    <div class="container-fluid mt-4 mn-erp-page">

     


        <div class="container-fluid py-3">

            <div class="mn-filter mb-4">

                @php
                    /*
                    |--------------------------------------------------------------------------
                    | FILTER STATE
                    |--------------------------------------------------------------------------
                    | Semua kontrol membaca state yang sama.
                    | Jadi ketika NW dipilih lalu ASC/DESC diklik,
                    | brand NW tetap ikut terkirim.
                    */

                    $currentSort = strtolower(request('sort', 'desc'));
                    $currentSort = in_array($currentSort, ['asc', 'desc']) ? $currentSort : 'desc';

                    $currentBrand = strtolower(request('brand', 'all'));
                    $currentBrand = in_array($currentBrand, ['all', 'nw', 'nws', 'nwr', 'nwd']) ? $currentBrand : 'all';

                    $nextSort = $currentSort === 'asc' ? 'desc' : 'asc';
                @endphp

                <form method="GET" action="{{ route('produksi.mn') }}" id="monitoringFilterForm">

                    {{-- STATE --}}
                    <input type="hidden" name="brand" id="monitoringBrand" value="{{ $currentBrand }}">

                    <input type="hidden" name="sort" id="monitoringSort" value="{{ $currentSort }}">

                    <div class="mn-toolbar">

                        {{-- SEARCH --}}
                        <div class="mn-search">

                            {{-- <div class="mn-field-label">
                                <span>Pencarian</span>
                            </div> --}}

                            <div class="mn-search-box">

                                <i class="fa fa-search"></i>

                                <input type="text" id="searchMonitoring" name="search_po"
                                    value="{{ request('search_po') }}" placeholder="Cari No PO atau Buyer..."
                                    autocomplete="off">

                                @if (request('search_po'))
                                    <button type="button" class="mn-search-clear" id="clearMonitoringSearch"
                                        title="Hapus pencarian">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif

                            </div>

                            <div class="mn-search-help">
                                <i class="fas fa-circle-info"></i>
                                Cari, No PO atau nama buyer, hover mouse on qty for see details
                            </div>

                        </div>


                        {{-- COMPANY --}}
                        <div class="mn-brand-filter">

                            <div class="mn-field-label">
                                <span>Company</span>
                            </div>

                            <div class="mn-brand-group" role="group" aria-label="Filter company">

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'all' ? 'active' : '' }}"
                                    data-brand="all" aria-pressed="{{ $currentBrand === 'all' ? 'true' : 'false' }}">
                                    All
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nw' ? 'active' : '' }}"
                                    data-brand="nw" aria-pressed="{{ $currentBrand === 'nw' ? 'true' : 'false' }}">
                                    NW
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nws' ? 'active' : '' }}"
                                    data-brand="nws" aria-pressed="{{ $currentBrand === 'nws' ? 'true' : 'false' }}">
                                    NWS
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nwr' ? 'active' : '' }}"
                                    data-brand="nwr" aria-pressed="{{ $currentBrand === 'nwr' ? 'true' : 'false' }}">
                                    NWR
                                </button>

                                <button type="button" class="mn-brand-btn {{ $currentBrand === 'nwd' ? 'active' : '' }}"
                                    data-brand="nwd" aria-pressed="{{ $currentBrand === 'nwd' ? 'true' : 'false' }}">
                                    NWD
                                </button>

                            </div>

                        </div>


                        {{-- SORT --}}
                        <div class="mn-sort">

                            <div class="mn-field-label">
                                <span>Release</span>
                            </div>

                            <button type="button" id="monitoringSortButton" class="mn-sort-btn"
                                title="{{ $currentSort === 'asc' ? 'Klik untuk menampilkan release terbaru' : 'Klik untuk menampilkan release terlama' }}"
                                aria-label="Ubah urutan release">

                                <span class="mn-sort-icon">
                                    <i
                                        class="fas {{ $currentSort === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                </span>

                                <span class="mn-sort-value">
                                    {{ $currentSort === 'asc' ? 'ASC' : 'DESC' }}
                                </span>

                            </button>

                        </div>


                        {{-- ACTION --}}
                        <div class="mn-actions">

                            <button type="submit" class="btn-mn-filter" id="monitoringFilterButton">
                                <i class="fas fa-filter"></i>
                                <span>Filter</span>
                            </button>

                            <a href="{{ route('produksi.mn') }}" class="btn-mn-reset" title="Reset Filter">
                                <i class="fa fa-rotate-left"></i>
                            </a>

                        </div>

                    </div>

                </form>
            </div>

            {{-- DATA --}}
            <div id="monitoringResult">
                @forelse($datas as $poIndex => $po)

                    <div class="mn-card mb-5">

                        {{-- HEADER --}}
                        <div class="mn-header d-flex justify-content-between align-items-center spk-header">

                            <div>

                                <h6>
                                    PO : {{ $po['po_number'] }}
                                    <span class="">
                                        ({{ $po['buyer'] ?? $po['buyer_name'] ?? '-' }})
                                    </span>
                                </h6>

                            </div>

                            <div>

                                <button type="button" class="btn btn-success btn-sm btn-toggle-po">

                                    <i class="fa fa-chevron-down"></i>

                                </button>
                            </div>

                        </div>
                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | KATEGORI HEADER DARI SPK ITEM
                            |--------------------------------------------------------------------------
                            | Header mengikuti kategori_monitoring yang benar-benar ada
                            | pada SPK item. Unfinish/Final berasal dari QC tanpa SPK.
                            |--------------------------------------------------------------------------
                            */
                            /*
                            |--------------------------------------------------------------------------
                            | KATEGORI HEADER PER PO
                            |--------------------------------------------------------------------------
                            | Header diambil dari SEMUA SPK yang benar-benar ada
                            | di seluruh item pada PO ini.
                            |
                            | Unfinish / Final juga ditampilkan berdasarkan
                            | keberadaan field, bukan nilai passed/rejected.
                            |--------------------------------------------------------------------------
                            */
                            $categoryOrder = [
                                'rangka' => 'Rangka',
                                'anyam' => 'Anyam',
                                'unfinish' => 'Unfinish',
                                'final' => 'Final',
                                'decor' => 'Decor',
                                'accessories' => 'Accessories',
                                'packaging' => 'Packaging',
                                'box' => 'Packaging',
                            ];

                            $foundCategories = [];

                            foreach (($po['items'] ?? []) as $headerItem) {

                                /*
                                |--------------------------------------------------------------------------
                                | SEMUA SPK PADA ITEM
                                |--------------------------------------------------------------------------
                                */
                                foreach (($headerItem['spks'] ?? []) as $headerSpk) {

                                    $categoryKey = strtolower(
                                        trim(
                                            $headerSpk['kategori_monitoring']
                                            ?? ''
                                        )
                                    );

                                    /*
                                    | Compatibility jika controller lama
                                    | masih menghasilkan "box".
                                    */
                                    if ($categoryKey === 'box') {
                                        $categoryKey = 'packaging';
                                    }

                                    if (
                                        $categoryKey !== ''
                                        &&
                                        isset($categoryOrder[$categoryKey])
                                    ) {
                                        $foundCategories[$categoryKey] = true;
                                    }
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | UNFINISH
                                |--------------------------------------------------------------------------
                                */
                                if (
                                    array_key_exists(
                                        'unfinish',
                                        $headerItem
                                    )
                                ) {
                                    $foundCategories['unfinish'] = true;
                                }

                                /*
                                |--------------------------------------------------------------------------
                                | FINAL
                                |--------------------------------------------------------------------------
                                */
                                if (
                                    array_key_exists(
                                        'final',
                                        $headerItem
                                    )
                                ) {
                                    $foundCategories['final'] = true;
                                }
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | BUILD HEADER SESUAI URUTAN
                            |--------------------------------------------------------------------------
                            */
                            $categories = [];

                            foreach (
                                $categoryOrder
                                as $categoryKey => $categoryLabel
                            ) {

                                if (
                                    isset(
                                        $foundCategories[$categoryKey]
                                    )
                                ) {
                                    $categories[$categoryKey] =
                                        $categoryLabel;
                                }
                            }

                            $statuses = [
                                'in' => [
                                    'label' => 'In',
                                    'class' => 'text-primary fw-bold',
                                ],

                                'pass' => [
                                    'label' => 'Pass',
                                    'class' => 'pass-box',
                                ],

                                'reject' => [
                                    'label' => 'Reject',
                                    'class' => 'reject-box',
                                ],

                                'out' => [
                                    'label' => 'Out',
                                    'class' => 'text-dark fw-bold',
                                ],
                            ];

                        @endphp

                        {{-- TABLE --}}
                        <div class="table-responsive po-table">

                            <table class="table mn-table align-middle">

                                <thead>

                                    {{-- HEADER CATEGORY --}}
                                    <tr>

                                        <th rowspan="2" class="text-center">
                                            Gambar
                                        </th>

                                        <th rowspan="2" class="text-center">
                                            Qty
                                        </th>

                                        <th rowspan="2" class="text-center">
                                            Item
                                        </th>

                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            <th colspan="{{ in_array($categoryKey, ['final', 'box', 'packaging']) ? 1 : 2 }}"
                                                class="text-center">
                                                {{ $categoryLabel }}
                                            </th>
                                        @endforeach

                                    </tr>

                                    {{-- HEADER STATUS --}}
                                    <tr>

                                        @foreach ($categories as $categoryKey => $categoryLabel)
                                            @foreach ($statuses as $statusKey => $status)
                                                @continue($statusKey == 'out')
                                                @continue($statusKey == 'reject')

                                                {{-- Final & Packaging hanya PASS --}}
                                                @if (in_array($categoryKey, ['final', 'box']) && $statusKey == 'in')
                                                    @continue
                                                @endif
                                                <th class="text-center status-col {{ $status['class'] }}">
                                                    {{ $status['label'] }}
                                                </th>
                                            @endforeach
                                        @endforeach

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($po['items'] as $itemIndex => $item)
                                        <tr>

                                            {{-- IMAGE --}}
                                            <td class="text-center">

                                                @if (!empty($item['item_image']))
                                                    <img src="{{ $item['item_image'] ?? '' }}" class="product-image"
                                                        loading="lazy" decoding="async">
                                                @else
                                                    -
                                                @endif

                                            </td>

                                            {{-- QTY --}}
                                            <td class="qty-col text-center">

                                                {{-- Qty utama PO --}}
                                                <div class="qty-main-value">
                                                    {{ $item['qty'] }}
                                                </div>

                                                @php
                                                    /*
                                                    |--------------------------------------------------------------------------
                                                    | KETERANGAN SPK DI BAWAH QTY
                                                    |--------------------------------------------------------------------------
                                                    | Hanya untuk informasi visual.
                                                    | Tidak mengubah perhitungan In / Pass / Reject.
                                                    |
                                                    | RANGKA KAKI KAYU ditampilkan sebagai Kaki Kayu.
                                                    */
                                                    $spkQtyByLabel = [];

                                                    foreach (($item['spks'] ?? []) as $spkInfo) {
                                                        $kategoriSpk = strtolower(trim($spkInfo['kategori'] ?? ''));

                                                        if ($kategoriSpk === '') {
                                                            continue;
                                                        }

                                                        if (
                                                            str_contains($kategoriSpk, 'kaki kayu') ||
                                                            str_contains($kategoriSpk, 'kayu kaki')
                                                        ) {
                                                            $spkLabel = 'Kaki Kayu';
                                                        } elseif (str_contains($kategoriSpk, 'rangka')) {
                                                            $spkLabel = 'Rangka';
                                                        } elseif (str_contains($kategoriSpk, 'anyam')) {
                                                            $spkLabel = 'Anyam';
                                                        } elseif (str_contains($kategoriSpk, 'unfinish')) {
                                                            $spkLabel = 'Unfinish';
                                                        } elseif (str_contains($kategoriSpk, 'final')) {
                                                            $spkLabel = 'Final';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'box') ||
                                                            str_contains($kategoriSpk, 'packaging')
                                                        ) {
                                                            $spkLabel = 'Packaging';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'dekor') ||
                                                            str_contains($kategoriSpk, 'decor')
                                                        ) {
                                                            $spkLabel = 'Decor';
                                                        } elseif (
                                                            str_contains($kategoriSpk, 'aksesor') ||
                                                            str_contains($kategoriSpk, 'aksesori') ||
                                                            str_contains($kategoriSpk, 'accessor')
                                                        ) {
                                                            $spkLabel = 'Accessories';
                                                        } else {
                                                            $spkLabel = ucwords($kategoriSpk);
                                                        }

                                                        $spkQty = (float) ($spkInfo['qty'] ?? 0);

                                                        if (!isset($spkQtyByLabel[$spkLabel])) {
                                                            $spkQtyByLabel[$spkLabel] = 0;
                                                        }

                                                        $spkQtyByLabel[$spkLabel] += $spkQty;
                                                    }

                                                    /*
                                                    | Map label hasil SPK ke kolom monitoring.
                                                    | Kaki Kayu -> Accessories.
                                                    */
                                                    $spkQtyByCategory = [];

                                                    foreach ($spkQtyByLabel as $spkLabel => $spkQty) {
                                                        $labelLower = strtolower(trim($spkLabel));

                                                        if (str_contains($labelLower, 'kaki kayu')) {
                                                            $spkCategoryKey = 'accessories';
                                                        } elseif (str_contains($labelLower, 'rangka')) {
                                                            $spkCategoryKey = 'rangka';
                                                        } elseif (str_contains($labelLower, 'anyam')) {
                                                            $spkCategoryKey = 'anyam';
                                                        } elseif (str_contains($labelLower, 'unfinish')) {
                                                            $spkCategoryKey = 'unfinish';
                                                        } elseif (str_contains($labelLower, 'final')) {
                                                            $spkCategoryKey = 'final';
                                                        } elseif (str_contains($labelLower, 'packaging') || str_contains($labelLower, 'box')) {
                                                            $spkCategoryKey = 'box';
                                                        } elseif (str_contains($labelLower, 'decor')) {
                                                            $spkCategoryKey = 'decor';
                                                        } elseif (str_contains($labelLower, 'accessories')) {
                                                            $spkCategoryKey = 'accessories';
                                                        } else {
                                                            continue;
                                                        }

                                                        if (!isset($spkQtyByCategory[$spkCategoryKey])) {
                                                            $spkQtyByCategory[$spkCategoryKey] = [];
                                                        }

                                                        $spkQtyByCategory[$spkCategoryKey][] = [
                                                            'label' => $spkLabel,
                                                            'qty' => $spkQty,
                                                        ];
                                                    }
                                                @endphp


                                            </td>

                                            {{-- ITEM --}}
                                            <td style="max-width:150px;">

                                                <a href="#" class="item-link text-truncate d-inline-block"
                                                    style="max-width:250px;" title="{{ $item['item_name'] }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#spkModal{{ $poIndex }}{{ $itemIndex }}">

                                                    {{ $item['item_name'] }}

                                                </a>

                                            </td>

                                            {{-- DYNAMIC CATEGORY + STATUS --}}
                                            @php
                                                $monitoring = [];

                                                foreach ($categories as $categoryKey => $categoryLabel) {
                                                    $monitoring[$categoryKey] = [
                                                        'in' => 0,
                                                        'pass' => 0,
                                                        'reject' => 0,
                                                        'spks' => [],
                                                    ];
                                                }

                                                foreach (($item['spks'] ?? []) as $spk) {
                                                    $categoryKey = strtolower(
                                                        trim($spk['kategori_monitoring'] ?? '')
                                                    );

                                                    if ($categoryKey === 'box') {
                                                        $categoryKey = 'packaging';
                                                    }

                                                    if (!isset($monitoring[$categoryKey])) {
                                                        continue;
                                                    }

                                                    $monitoring[$categoryKey]['in'] +=
                                                        (float) ($spk['qty_in'] ?? 0);

                                                    $monitoring[$categoryKey]['pass'] +=
                                                        (float) ($spk['passed'] ?? 0);

                                                    $monitoring[$categoryKey]['reject'] +=
                                                        (float) ($spk['rejected'] ?? 0);

                                                    $monitoring[$categoryKey]['spks'][] = $spk;
                                                }

                                                if (isset($monitoring['unfinish'])) {
                                                    $monitoring['unfinish']['pass'] =
                                                        (float) ($item['unfinish']['passed'] ?? 0);

                                                    $monitoring['unfinish']['reject'] =
                                                        (float) ($item['unfinish']['rejected'] ?? 0);
                                                }

                                                if (isset($monitoring['final'])) {
                                                    $monitoring['final']['pass'] =
                                                        (float) ($item['final']['passed'] ?? 0);

                                                    $monitoring['final']['reject'] =
                                                        (float) ($item['final']['rejected'] ?? 0);
                                                }

                                                $formatQty = function ($value) {
                                                    $value = (float) $value;

                                                    return floor($value) == $value
                                                        ? number_format($value, 0, ',', '.')
                                                        : number_format($value, 2, ',', '.');
                                                };
                                            @endphp

                                            @foreach ($categories as $categoryKey => $categoryLabel)

                                                @php
                                                    $categorySpks = $monitoring[$categoryKey]['spks'] ?? [];
                                                @endphp

                                                {{-- IN --}}
                                                @if (!in_array($categoryKey, ['final', 'packaging']))
                                                    <td class="text-center status-col text-primary fw-bold">

                                                        @if (!empty($categorySpks))
                                                            <span
                                                                class="spk-hover-target"
                                                                data-tooltip-type="in"
                                                                data-monitor-metric="qty_in"
                                                            >
                                                                {{ $formatQty($monitoring[$categoryKey]['in'] ?? 0) }}

                                                                <span class="spk-list-tooltip">
                                                                    <span class="spk-list-tooltip-title">
                                                                        <strong>
                                                                            {{ strtoupper($categoryLabel) }}
                                                                            — TOTAL IN
                                                                        </strong>
                                                                        <span class="spk-list-tooltip-count">
                                                                            {{ count($categorySpks) }} SPK
                                                                        </span>
                                                                    </span>

                                                                    <span class="spk-list-tooltip-scroll">
                                                                        <table class="spk-list-table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="col-no">#</th>
                                                                                    <th class="col-spk">NO SPK</th>
                                                                                    <th class="col-sub">SUB NAME</th>
                                                                                    <th class="col-category">JENIS/KATEGORI</th>
                                                                                    <th class="col-description">KETERANGAN</th>
                                                                                    <th class="col-total">TOTAL IN</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($categorySpks as $spkInfo)
                                                                                    @php
                                                                                        /*
                                                                                        |--------------------------------------------------------------------------
                                                                                        | SUB NAME / SUPPLIER
                                                                                        |--------------------------------------------------------------------------
                                                                                        | Untuk tooltip IN dan PASSED harus identik.
                                                                                        | Sumbernya adalah supplier dari SPK:
                                                                                        |
                                                                                        | $spkData['sup']
                                                                                        |      ↓
                                                                                        | controller
                                                                                        |      ↓
                                                                                        | $spkInfo['supplier']
                                                                                        |--------------------------------------------------------------------------
                                                                                        */
                                                                                        $spkSubName =
                                                                                            $spkInfo['supplier']
                                                                                            ?? '-';
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td class="col-no">
                                                                                            {{ $loop->iteration }}
                                                                                        </td>
                                                                                        <td class="col-spk">
                                                                                            <a
                                                                                                href="{{ url('spk/edit/' . ($spkInfo['spk_id'] ?? '')) }}"
                                                                                                class="spk-link"
                                                                                            >
                                                                                                {{ $spkInfo['no_spk'] ?? '-' }}
                                                                                            </a>
                                                                                        </td>
                                                                                        <td class="col-sub">
                                                                                            {{ $spkSubName }}
                                                                                        </td>
                                                                                        <td class="col-category">

                                                                                            {{ strtoupper($spkInfo['kategori'] ?? '-') }}

                                                                                            @if (!empty($spkInfo['is_exception']))
                                                                                                <span class="exception-badge">
                                                                                                    EXCEPTION
                                                                                                </span>
                                                                                            @endif

                                                                                        </td>

                                                                                        <td class="col-description">
@php
                                                                                                 $componentNames = collect($spkInfo['components'] ?? [])
                                                                                                     ->map(function ($component) {
                                                                                                         return trim((string) (
                                                                                                             $component['name']
                                                                                                             ?? $component['proses']
                                                                                                             ?? ''
                                                                                                         ));
                                                                                                     })
                                                                                                     ->filter()
                                                                                                     ->unique()
                                                                                                     ->values()
                                                                                                     ->all();
                                                                                             @endphp

                                                                                             @if (!empty($componentNames))
                                                                                                 {{ implode(', ', $componentNames) }}
                                                                                             @else
                                                                                                 -
                                                                                             @endif

                                                                                        </td>

                                                                                        <td class="col-total">
                                                                                            {{ $formatQty($spkInfo['qty_in'] ?? 0) }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        @else
                                                            {{ $formatQty($monitoring[$categoryKey]['in'] ?? 0) }}
                                                        @endif

                                                        @if (!empty($categorySpks))
                                                            <div class="spk-under-value">
                                                                <div class="spk-under-value-line">
                                                                    {{ count($categorySpks) }} SPK
                                                                </div>
                                                            </div>
                                                        @endif

                                                    </td>
                                                @endif

                                                {{-- PASS --}}
                                                <td class="text-center status-col pass-box">

                                                    @if (!empty($categorySpks))
                                                        <span
                                                            class="spk-hover-target"
                                                            data-tooltip-type="pass"
                                                            data-monitor-metric="passed"
                                                        >
                                                            {{ $formatQty($monitoring[$categoryKey]['pass'] ?? 0) }}

                                                            <span class="spk-list-tooltip">
                                                                <span class="spk-list-tooltip-title">
                                                                    <strong>
                                                                        {{ strtoupper($categoryLabel) }}
                                                                        — PASSED
                                                                    </strong>
                                                                    <span class="spk-list-tooltip-count">
                                                                        {{ count($categorySpks) }} SPK
                                                                    </span>
                                                                </span>

                                                                <span class="spk-list-tooltip-scroll">
                                                                    <table class="spk-list-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="col-no">#</th>
                                                                                <th class="col-spk">NO SPK</th>
                                                                                <th class="col-sub">SUB NAME</th>
                                                                                <th class="col-category">JENIS/KATEGORI</th>
                                                                                <th class="col-description">KETERANGAN</th>
                                                                                <th class="col-total">TOTAL PASSED</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($categorySpks as $spkInfo)
                                                                                @php
                                                                                    // Supplier / sub name untuk PASSED harus sama persis dengan IN.
                                                                                    $spkSubName = $spkInfo['supplier'] ?? '-';
                                                                                @endphp
                                                                                <tr>
                                                                                    <td class="col-no">
                                                                                        {{ $loop->iteration }}
                                                                                    </td>
                                                                                    <td class="col-spk">
                                                                                        <a
                                                                                            href="{{ url('spk/edit/' . ($spkInfo['spk_id'] ?? '')) }}"
                                                                                            class="spk-link"
                                                                                        >
                                                                                            {{ $spkInfo['no_spk'] ?? '-' }}
                                                                                        </a>
                                                                                    </td>
                                                                                    <td class="col-sub">
                                                                                        {{ $spkSubName }}
                                                                                    </td>
                                                                                    <td class="col-category">
                                                                                        {{ strtoupper($spkInfo['kategori'] ?? '-') }}
                                                                                        @if (!empty($spkInfo['is_exception']))
                                                                                            <span class="exception-badge">
                                                                                                EXCEPTION
                                                                                            </span>
                                                                                        @endif

                                                                                        </td>

                                                                                        <td class="col-description">
@php
                                                                                                 $componentNames = collect($spkInfo['components'] ?? [])
                                                                                                     ->map(function ($component) {
                                                                                                         return trim((string) (
                                                                                                             $component['name']
                                                                                                             ?? $component['proses']
                                                                                                             ?? ''
                                                                                                         ));
                                                                                                     })
                                                                                                     ->filter()
                                                                                                     ->unique()
                                                                                                     ->values()
                                                                                                     ->all();
                                                                                             @endphp

                                                                                             @if (!empty($componentNames))
                                                                                                 {{ implode(', ', $componentNames) }}
                                                                                             @else
                                                                                                 -
                                                                                             @endif

                                                                                        </td>

                                                                                        <td class="col-total"
                                                                                        data-tooltip-in-value="{{ $formatQty($spkInfo['qty_in'] ?? 0) }}"
                                                                                        data-tooltip-pass-value="{{ $formatQty($spkInfo['passed'] ?? 0) }}">
                                                                                        {{ $formatQty($spkInfo['passed'] ?? 0) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </span>
                                                            </span>
                                                        @else
                                                            {{ $formatQty($monitoring[$categoryKey]['pass'] ?? 0) }}
                                                    @endif

                                                    @if (!empty($categorySpks))
                                                        <div class="spk-under-value">
                                                            <div class="spk-under-value-line">
                                                                {{ count($categorySpks) }} SPK
                                                            </div>
                                                        </div>
                                                    @endif

                                                </td>

                                            @endforeach

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>




                        </div>
                    </div>
                    {{-- ========================================================= --}}
                    {{-- MODAL LUAR TABLE --}}
                    {{-- ========================================================= --}}

                    @foreach ($po['items'] as $itemIndex => $item)
                        <div class="modal fade" id="spkModal{{ $poIndex }}{{ $itemIndex }}" tabindex="-1">

                            <div class="modal-dialog modal-lg modal-dialog-centered">

                                <div class="modal-content border-0 shadow">

                                    {{-- HEADER --}}
                                    <div class="modal-header bg-dark text-white">

                                        <h5 class="modal-title">

                                            SPK ITEM

                                        </h5>

                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                                        </button>

                                    </div>

                                    {{-- BODY --}}
                                    <div class="modal-body">

                                        {{-- ITEM INFO --}}
                                        <div class="d-flex gap-3 mb-4">

                                            @if (!empty($item['item_image']))
                                                <img src="{{ $item['item_image'] ?? '' }}" class="product-image"
                                                    loading="lazy" decoding="async">
                                            @endif

                                            <div>

                                                <div class="fw-bold fs-5">

                                                    {{ $item['item_name'] }}

                                                </div>

                                                <div class="text-muted">

                                                    Qty :
                                                    {{ $item['qty'] }}

                                                </div>

                                            </div>

                                        </div>

                                        {{-- LIST SPK --}}
                                        @forelse($item['spks'] as $spk)

                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-body">

                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div class="d-flex gap-2 flex-wrap">

                                                            <span class="badge bg-primary px-3 py-2">
                                                                {{ strtoupper($spk['kategori'] ?? '-') }}
                                                            </span>

                                                            <span class="badge bg-secondary px-3 py-2">
                                                                {{ strtoupper($spk['kategori_monitoring'] ?? '-') }}
                                                            </span>

                                                            @if (!empty($spk['is_exception']))
                                                                <span class="badge bg-warning text-dark px-3 py-2">
                                                                    Exception:
                                                                    {{ strtoupper($spk['exception_rule'] ?? 'RULE') }}
                                                                </span>
                                                            @endif

                                                        </div>

                                                        <span class="badge bg-success px-3 py-2">
                                                            SPK #{{ $spk['spk_id'] ?? '-' }}
                                                        </span>

                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-8">

                                                            <table class="table table-sm mb-0">

                                                                <tr>
                                                                    <td width="140">Supplier</td>
                                                                    <td>:
                                                                        {{ $spk['supplier'] ?? '-' }}
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>No SPK</td>
                                                                    <td>:
                                                                        <a href="{{ url('spk/edit/' . ($spk['spk_id'] ?? '')) }}"
                                                                           class="fw-bold text-primary text-decoration-underline">
                                                                            {{ $spk['no_spk'] ?? '-' }}
                                                                        </a>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Qty SPK</td>
                                                                    <td>:
                                                                        {{ $spk['qty'] ?? 0 }}
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Qty In</td>
                                                                    <td>:
                                                                        <span class="fw-bold text-primary">
                                                                            {{ $spk['qty_in'] ?? 0 }}
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>Harga</td>
                                                                    <td>:
                                                                        <span class="price-container"
                                                                              data-price="{{ number_format($spk['harga'] ?? 0) }}">
                                                                            <a href="#"
                                                                               class="show-price text-primary text-decoration-underline">
                                                                                Lihat Harga? Tap disini
                                                                            </a>
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                            </table>

                                                        </div>

                                                        <div class="col-md-4">

                                                            <div class="border rounded-4 p-3 h-100 bg-light">

                                                                <div class="fw-bold mb-3">
                                                                    MONITORING RESULT
                                                                </div>

                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <span>Passed</span>
                                                                    <span class="fw-bold text-success">
                                                                        {{ $spk['passed'] ?? 0 }}
                                                                    </span>
                                                                </div>

                                                                <div class="d-flex justify-content-between">
                                                                    <span>Rejected</span>
                                                                    <span class="fw-bold text-danger">
                                                                        {{ $spk['rejected'] ?? 0 }}
                                                                    </span>
                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        @empty

                                            <div class="alert alert-warning mb-0">
                                                Tidak ada SPK untuk item ini
                                            </div>

                                        @endforelse
                                    </div>

                                </div>

                            </div>

                        </div>
                    @endforeach

                    @empty

                        <div class="mn-empty">

                            <h5 class="mb-2">

                                Data Tidak Ditemukan

                            </h5>

                            <div class="text-muted">

                                Coba cari PO atau batch lain

                            </div>

                        </div>
                    @endforelse
                </div>

                <div class="modal fade" id="pricePasswordModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Verifikasi Password
                                </h5>
                            </div>

                            <div class="modal-body">

                                <input type="password" id="pricePassword" class="form-control"
                                    placeholder="Masukkan Password">

                                <div id="priceError" class="text-danger mt-2" style="display:none;">

                                    Password salah

                                </div>

                            </div>

                            <div class="modal-footer">

                                <button class="btn btn-secondary" data-bs-dismiss="modal">

                                    Batal

                                </button>

                                <button class="btn btn-primary" id="btnCheckPricePassword">

                                    Lihat Harga

                                </button>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script>
                /*
                            |--------------------------------------------------------------------------
                            | MONITORING AJAX FILTER
                            |--------------------------------------------------------------------------
                            | NW/NWS/NWR/NWD, ASC/DESC dan search berjalan tanpa reload halaman.
                            */

                (function() {

                    const form = document.getElementById('monitoringFilterForm');
                    const brandInput = document.getElementById('monitoringBrand');
                    const sortInput = document.getElementById('monitoringSort');
                    const sortButton = document.getElementById('monitoringSortButton');
                    const searchInput = document.getElementById('searchMonitoring');
                    const clearSearch = document.getElementById('clearMonitoringSearch');
                    const result = document.getElementById('monitoringResult');

                    if (!form || !brandInput || !sortInput || !result) {
                        return;
                    }

                    let searchTimer = null;
                    let requestController = null;

                    function buildUrl() {

                        const params = new URLSearchParams();

                        const search = searchInput ?
                            searchInput.value.trim() :
                            '';

                        const brand = brandInput.value || 'all';
                        const sort = sortInput.value || 'desc';

                        if (search) {
                            params.set('search_po', search);
                        }

                        if (brand !== 'all') {
                            params.set('brand', brand);
                        }

                        params.set('sort', sort);

                        return form.action + '?' + params.toString();
                    }

                    function setLoading(loading) {

                        result.classList.toggle('is-loading', loading);

                        const filterButton =
                            document.getElementById('monitoringFilterButton');

                        if (filterButton) {
                            filterButton.disabled = loading;
                        }

                        document.querySelectorAll(
                            '.mn-brand-btn, .mn-sort-btn'
                        ).forEach(function(button) {
                            button.classList.toggle('is-loading', loading);
                        });
                    }

                    function showLoader() {

                        if (document.getElementById('mnAjaxLoader')) {
                            return;
                        }

                        const loader = document.createElement('div');

                        loader.id = 'mnAjaxLoader';
                        loader.className = 'mn-ajax-loader';

                        loader.innerHTML = `
                            <div class="mn-ajax-loader-card">
                                <span class="spinner-border" role="status"></span>
                                <span>Memuat data...</span>
                            </div>
                        `;

                        document.body.appendChild(loader);
                    }

                    function hideLoader() {

                        const loader =
                            document.getElementById('mnAjaxLoader');

                        if (loader) {
                            loader.remove();
                        }
                    }

                    async function loadMonitoring(updateBrowserUrl = true) {

                        const url = buildUrl();

                        if (requestController) {
                            requestController.abort();
                        }

                        requestController = new AbortController();

                        setLoading(true);
                        showLoader();

                        try {

                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                },
                                signal: requestController.signal
                            });

                            if (!response.ok) {
                                throw new Error('HTTP ' + response.status);
                            }

                            const html = await response.text();

                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newResult =
                                doc.getElementById('monitoringResult');

                            if (!newResult) {
                                throw new Error(
                                    'Container monitoringResult tidak ditemukan.'
                                );
                            }

                            result.innerHTML = newResult.innerHTML;

                            if (updateBrowserUrl) {
                                window.history.replaceState({
                                        monitoring: true
                                    },
                                    '',
                                    url
                                );
                            }

                        } catch (error) {

                            if (error.name === 'AbortError') {
                                return;
                            }

                            console.error('Monitoring AJAX error:', error);

                            result.insertAdjacentHTML(
                                'afterbegin',
                                `
                                <div class="mn-filter-error">
                                    <i class="fas fa-circle-exclamation me-1"></i>
                                    Gagal memuat data. Silakan coba lagi.
                                </div>
                                `
                            );

                        } finally {

                            setLoading(false);
                            hideLoader();
                            requestController = null;

                        }
                    }

                    /*
                    | COMPANY
                    */

                    document.querySelectorAll('.mn-brand-btn')
                        .forEach(function(button) {

                            button.addEventListener('click', function() {

                                brandInput.value =
                                    this.dataset.brand || 'all';

                                document.querySelectorAll(
                                    '.mn-brand-btn'
                                ).forEach(function(btn) {

                                    const active =
                                        btn.dataset.brand ===
                                        brandInput.value;

                                    btn.classList.toggle('active', active);

                                    btn.setAttribute(
                                        'aria-pressed',
                                        active ? 'true' : 'false'
                                    );

                                });

                                loadMonitoring();

                            });

                        });

                    /*
                    | ASC / DESC
                    */

                    if (sortButton) {

                        sortButton.addEventListener('click', function() {

                            sortInput.value =
                                sortInput.value === 'asc' ?
                                'desc' :
                                'asc';

                            loadMonitoring();

                        });

                    }

                    /*
                    | SEARCH REALTIME
                    */

                    if (searchInput) {

                        searchInput.addEventListener('input', function() {

                            clearTimeout(searchTimer);

                            searchTimer = setTimeout(function() {
                                loadMonitoring();
                            }, 450);

                        });

                        searchInput.addEventListener('keydown', function(event) {

                            if (event.key === 'Enter') {

                                event.preventDefault();

                                clearTimeout(searchTimer);

                                loadMonitoring();

                            }

                        });

                    }

                    /*
                    | CLEAR SEARCH
                    */

                    if (clearSearch) {

                        clearSearch.addEventListener('click', function() {

                            if (searchInput) {
                                searchInput.value = '';
                            }

                            loadMonitoring();

                            if (searchInput) {
                                searchInput.focus();
                            }

                        });

                    }

                    /*
                    | FILTER BUTTON
                    */

                    form.addEventListener('submit', function(event) {

                        event.preventDefault();

                        clearTimeout(searchTimer);

                        loadMonitoring();

                    });

                    /*
                    | BACK / FORWARD
                    */

                    window.addEventListener('popstate', function() {

                        const params =
                            new URLSearchParams(
                                window.location.search
                            );

                        brandInput.value =
                            params.get('brand') || 'all';

                        sortInput.value =
                            params.get('sort') || 'desc';

                        if (searchInput) {
                            searchInput.value =
                                params.get('search_po') || '';
                        }

                        document.querySelectorAll(
                            '.mn-brand-btn'
                        ).forEach(function(btn) {

                            const active =
                                btn.dataset.brand ===
                                brandInput.value;

                            btn.classList.toggle('active', active);
                            btn.setAttribute(
                                'aria-pressed',
                                active ? 'true' : 'false'
                            );

                        });

                        loadMonitoring(false);

                    });

                })();


                /*
                |--------------------------------------------------------------------------
                | PRICE PASSWORD
                |--------------------------------------------------------------------------
                */

                let currentPriceContainer = null;

                $(document).on('click', '.show-price', function(e) {

                    e.preventDefault();

                    currentPriceContainer = $(this).closest('.price-container');

                    $('#pricePassword').val('');
                    $('#priceError').hide();

                    $('#pricePasswordModal').modal('show');

                });


                $('#btnCheckPricePassword').click(function() {

                    if ($('#pricePassword').val() !== 'Nwidn@2026') {

                        $('#priceError').show();
                        return;

                    }

                    let harga = currentPriceContainer.data('price');

                    currentPriceContainer.html(
                        '<strong>Rp ' + harga + '</strong>'
                    );

                    $('#pricePasswordModal').modal('hide');

                });


                /*
                |--------------------------------------------------------------------------
                | COLLAPSE PO
                |--------------------------------------------------------------------------
                */

                $(document).on('click', '.btn-toggle-po', function() {

                    let card = $(this).closest('.mn-card');

                    card.find('.po-table').slideToggle(200);

                    let icon = $(this).find('i');

                    if (icon.hasClass('fa-chevron-down')) {

                        icon.removeClass('fa-chevron-down')
                            .addClass('fa-chevron-right');

                    } else {

                        icon.removeClass('fa-chevron-right')
                            .addClass('fa-chevron-down');

                    }

                });
            

/* =========================================================
   HOVER LIST SPK - GLOBAL TOOLTIP
   =========================================================
   Tooltip sengaja dipindahkan ke <body>.
   Jangan dibiarkan sebagai child dari table/cell karena parent
   bisa memiliki overflow, transform, atau stacking context yang
   membuat position: fixed kadang muncul di kiri atas / posisi salah.
   ========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    let activeTarget = null;
    let hideTimer = null;
    let tooltipOpen = false;

    const tooltip = document.createElement('div');

    tooltip.className = 'spk-global-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.innerHTML = '';

    document.body.appendChild(tooltip);


    /*
    |--------------------------------------------------------------------------
    | HIDE
    |--------------------------------------------------------------------------
    */
    function hideTooltip(immediate = false) {

        clearTimeout(hideTimer);

        const execute = function () {

            activeTarget = null;
            tooltipOpen = false;

            tooltip.classList.remove('visible');
            tooltip.style.visibility = 'hidden';
            tooltip.style.opacity = '0';
            tooltip.style.left = '-9999px';
            tooltip.style.top = '-9999px';
        };

        if (immediate) {
            execute();
            return;
        }

        hideTimer = setTimeout(function () {

            /*
            | Jangan tutup jika mouse sudah masuk ke tooltip.
            */
            if (tooltip.matches(':hover')) {
                return;
            }

            if (
                activeTarget &&
                activeTarget.matches(':hover')
            ) {
                return;
            }

            execute();

        }, 160);
    }


    /*
    |--------------------------------------------------------------------------
    | POSITION
    |--------------------------------------------------------------------------
    */
    function positionTooltip(target) {

        if (
            !target ||
            !tooltip.classList.contains('visible')
        ) {
            return;
        }

        const rect = target.getBoundingClientRect();

        const padding = 12;
        const gap = 10;

        const maxWidth = Math.min(
            680,
            window.innerWidth - (padding * 2)
        );

        tooltip.style.width = maxWidth + 'px';

        const tooltipWidth = tooltip.offsetWidth;
        const tooltipHeight = tooltip.offsetHeight;

        let left =
            rect.left +
            (rect.width / 2) -
            (tooltipWidth / 2);

        let top =
            rect.top -
            tooltipHeight -
            gap;

        tooltip.classList.remove('tooltip-below');

        /*
        | Tidak cukup ruang di atas -> bawah.
        */
        if (top < padding) {

            top = rect.bottom + gap;

            tooltip.classList.add('tooltip-below');
        }

        /*
        | Clamp kiri.
        */
        if (left < padding) {
            left = padding;
        }

        /*
        | Clamp kanan.
        */
        if (
            left + tooltipWidth >
            window.innerWidth - padding
        ) {

            left =
                window.innerWidth -
                tooltipWidth -
                padding;
        }

        /*
        | Kalau bagian bawah masih keluar layar,
        | geser ke posisi paling aman.
        */
        if (
            top + tooltipHeight >
            window.innerHeight - padding
        ) {

            top = Math.max(
                padding,
                window.innerHeight -
                tooltipHeight -
                padding
            );
        }

        tooltip.style.left =
            Math.round(left) + 'px';

        tooltip.style.top =
            Math.round(top) + 'px';
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    function showTooltip(target) {

        clearTimeout(hideTimer);

        const sourceTooltip =
            target.querySelector('.spk-list-tooltip');

        if (!sourceTooltip) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | METRIC HARUS MENGIKUTI KOLOM YANG DI-HOVER
        |--------------------------------------------------------------------------
        |
        | IN   -> qty_in
        | PASS -> passed
        |
        | Jadi tooltip tidak pernah menggunakan nilai metric yang salah.
        |--------------------------------------------------------------------------
        */
        const metric =
            target.dataset.monitorMetric
            || (
                target.dataset.tooltipType === 'pass'
                    ? 'passed'
                    : 'qty_in'
            );

        activeTarget = target;
        tooltipOpen = true;

        tooltip.innerHTML =
            sourceTooltip.innerHTML;

        /*
        | Metric class untuk styling.
        */
        tooltip.classList.remove(
            'metric-in',
            'metric-pass'
        );

        if (metric === 'passed') {
            tooltip.classList.add('metric-pass');
        } else {
            tooltip.classList.add('metric-in');
        }

        /*
        |--------------------------------------------------------------------------
        | HARD SAFETY:
        | Pastikan judul dan kolom mengikuti metric target.
        |--------------------------------------------------------------------------
        */
        const title =
            tooltip.querySelector(
                '.spk-list-tooltip-title strong'
            );

        const totalHeader =
            tooltip.querySelector(
                '.spk-list-table .col-total'
            );

        if (metric === 'passed') {

            if (title) {
                title.textContent =
                    title.textContent
                        .replace(
                            /—\s*(TOTAL IN|TOTAL PASS|PASSED|PASS)/i,
                            '— PASSED'
                        );
            }

            if (totalHeader) {
                totalHeader.textContent =
                    'TOTAL PASSED';
            }

            /*
            | Ambil nilai passed dari baris yang sudah dibuat Blade.
            | Jika ada data-value-passed, gunakan itu.
            */
            tooltip
                .querySelectorAll(
                    '[data-tooltip-in-value], [data-tooltip-pass-value]'
                )
                .forEach(function (element) {

                    const value =
                        element.dataset.tooltipPassValue;

                    if (value !== undefined) {
                        element.textContent = value;
                    }
                });

        } else {

            if (title) {
                title.textContent =
                    title.textContent
                        .replace(
                            /—\s*(TOTAL IN|TOTAL PASS|PASSED|PASS)/i,
                            '— TOTAL IN'
                        );
            }

            if (totalHeader) {
                totalHeader.textContent =
                    'TOTAL IN';
            }

            tooltip
                .querySelectorAll(
                    '[data-tooltip-in-value], [data-tooltip-pass-value]'
                )
                .forEach(function (element) {

                    const value =
                        element.dataset.tooltipInValue;

                    if (value !== undefined) {
                        element.textContent = value;
                    }
                });
        }

        tooltip.classList.remove('visible');
        tooltip.classList.remove('tooltip-below');

        tooltip.style.visibility = 'hidden';
        tooltip.style.opacity = '0';
        tooltip.style.left = '-9999px';
        tooltip.style.top = '-9999px';

        tooltip.style.maxHeight = Math.min(
            430,
            window.innerHeight - 24
        ) + 'px';

        void tooltip.offsetHeight;

        tooltip.style.visibility = 'visible';
        tooltip.style.opacity = '1';
        tooltip.classList.add('visible');

        positionTooltip(target);
    }


    /*
    |--------------------------------------------------------------------------
    | TARGET EVENTS
    |--------------------------------------------------------------------------
    */
    function bindTargets() {

        document
            .querySelectorAll('.spk-hover-target')
            .forEach(function (target) {

                if (target.dataset.tooltipBound === '1') {
                    return;
                }

                target.dataset.tooltipBound = '1';

                target.addEventListener(
                    'mouseenter',
                    function () {

                        clearTimeout(hideTimer);

                        /*
                        | Jika pindah ke angka lain,
                        | langsung update tooltip.
                        */
                        showTooltip(target);
                    }
                );

                target.addEventListener(
                    'mouseleave',
                    function () {

                        hideTooltip(false);
                    }
                );
            });
    }


    bindTargets();


    /*
    |--------------------------------------------------------------------------
    | TOOLTIP EVENTS
    |--------------------------------------------------------------------------
    |
    | Tooltip sekarang INTERACTIVE.
    | Mouse boleh masuk ke sini dan tooltip tetap terbuka.
    |
    */
    tooltip.addEventListener(
        'mouseenter',
        function () {

            clearTimeout(hideTimer);
            tooltipOpen = true;
        }
    );


    tooltip.addEventListener(
        'mouseleave',
        function () {

            hideTooltip(false);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLICK NO SPK
    |--------------------------------------------------------------------------
    |
    | Link dibiarkan normal supaya bisa:
    | - klik
    | - Ctrl + klik
    | - middle click
    | - buka tab baru
    |
    | Kita tidak menutup tooltip sebelum browser memproses link.
    |
    */
    tooltip.addEventListener(
        'click',
        function (event) {

            const link =
                event.target.closest('.spk-link');

            if (!link) {
                return;
            }

            /*
            | Jangan preventDefault.
            */
            clearTimeout(hideTimer);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    |
    | Klik area lain -> tutup.
    | Klik tooltip -> jangan tutup.
    |
    */
    document.addEventListener(
        'mousedown',
        function (event) {

            if (!tooltipOpen) {
                return;
            }

            if (
                tooltip.contains(event.target)
                ||
                (
                    activeTarget &&
                    activeTarget.contains(event.target)
                )
            ) {
                return;
            }

            hideTooltip(true);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | RESIZE
    |--------------------------------------------------------------------------
    */
    window.addEventListener(
        'resize',
        function () {

            if (activeTarget) {
                positionTooltip(activeTarget);
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | SCROLL
    |--------------------------------------------------------------------------
    |
    | Jika scroll terjadi di luar tooltip,
    | posisi dihitung ulang terhadap target.
    |
    */
    window.addEventListener(
        'scroll',
        function () {

            if (activeTarget) {
                positionTooltip(activeTarget);
            }
        },
        true
    );


    /*
    |--------------------------------------------------------------------------
    | AJAX / DOM CHANGE
    |--------------------------------------------------------------------------
    |
    | Blade ini menggunakan AJAX untuk reload monitoring.
    | Setelah tabel diganti, bind ulang target baru.
    |
    */
    const observer = new MutationObserver(
        function () {
            bindTargets();
        }
    );

    observer.observe(
        document.body,
        {
            childList: true,
            subtree: true,
        }
    );


    window.addEventListener(
        'beforeunload',
        function () {
            tooltip.remove();
            observer.disconnect();
        }
    );
});

</script>

        @endsection

    