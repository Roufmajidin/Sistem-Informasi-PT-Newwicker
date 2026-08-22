@extends('master.master')
@section('title', 'SPK Approval Assignment')

@section('content')
    <style>
        /* =========================================================
                   SPK PRODUKSI - CLEAN MODERN LAYOUT (Matching preview.html)
                   ========================================================= */
        :root {
            --spk-navy: #50b95a;
            --spk-blue: #6f7174;
            --spk-green: #50b95a;
            --spk-pink: #f16b89;
            --spk-border: #d8dee8;
            --spk-bg: #f4f6f9;
            --spk-text: #172033;
            --spk-muted: #737b88;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            margin: 0;
            padding: 0;
            background: var(--spk-bg);
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--spk-text);
            font-size: 12px;
        }
/* =========================================================
   FLOATING SPK ACTION
   ========================================================= */

.spk-floating-actions {
    position: fixed;

    right: 18px;
    top: 50%;
    transform: translateY(-50%);

    width: 120px;

    z-index: 9999;

    background: rgba(255,255,255,.97);

    border: 1px solid #dce3ec;

    border-radius: 12px;

    box-shadow:
        0 14px 35px rgba(15,23,42,.14),
        0 3px 8px rgba(15,23,42,.06);

    overflow: hidden;

    user-select: none;

    transition:
        box-shadow .15s ease,
        opacity .15s ease;
}


/* =========================================================
   HEADER / DRAG HANDLE
   ========================================================= */

.spk-floating-header {

    height: 38px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 11px;

    background: #f8fafc;

    border-bottom: 1px solid #e8edf3;

    cursor: move;

}

.spk-floating-title {

    display: flex;

    align-items: center;

    gap: 7px;

    color: #1e293b;

    font-size: 11px;

    font-weight: 750;

}

.spk-floating-title i {

    width: 23px;
    height: 23px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 6px;

    background: #eef3ff;

    color: #3158c9;

    font-size: 10px;

}

.spk-drag-hint {

    color: #94a3b8;

    font-size: 11px;

}


/* =========================================================
   BODY
   ========================================================= */

.spk-floating-body {

    padding: 7px;

    display: flex;

    flex-direction: column;

    gap: 4px;

}


/* =========================================================
   BUTTON
   ========================================================= */

.spk-floating-btn {

    width: 100%;

    min-height: 42px;

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 6px 8px;

    border: 1px solid transparent;

    border-radius: 8px;

    background: #fff;

    text-align: left;

    cursor: pointer;

    transition:
        background .15s ease,
        border-color .15s ease,
        transform .12s ease;

}

.spk-floating-btn:hover {

    transform: translateX(-1px);

}


/* ICON */

.spk-btn-icon {

    width: 28px;
    height: 28px;

    min-width: 28px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 7px;

    font-size: 11px;

}


/* TEXT */

.spk-floating-btn strong {

    display: block;

    font-size: 10px;

    line-height: 1.2;

    font-weight: 750;

}

.spk-floating-btn small {

    display: block;

    margin-top: 2px;

    font-size: 8px;

    color: #8a96a6;

    line-height: 1.2;

}


/* =========================================================
   SIGNATURE
   ========================================================= */

.spk-btn-signature {

    border-color: #dbe5fb;

}

.spk-btn-signature .spk-btn-icon {

    background: #eef3ff;

    color: #3158c9;

}

.spk-btn-signature strong {

    color: #3158c9;

}

.spk-btn-signature:hover {

    background: #f7f9ff;

    border-color: #b9c9f3;

}


/* =========================================================
   SAVE
   ========================================================= */

.spk-btn-save {

    border-color: #d9efe1;

}

.spk-btn-save .spk-btn-icon {

    background: #edf9f1;

    color: #159957;

}

.spk-btn-save strong {

    color: #16834d;

}

.spk-btn-save:hover {

    background: #f7fcf8;

    border-color: #b8dfc7;

}


/* =========================================================
   CLOSE
   ========================================================= */

.spk-btn-close {

    border-color: #f1dada;

}

.spk-btn-close .spk-btn-icon {

    background: #fff2f2;

    color: #d64545;

}

.spk-btn-close strong {

    color: #c63d3d;

}

.spk-btn-close:hover {

    background: #fffafa;

    border-color: #e7bebe;

}


/* =========================================================
   DRAGGING
   ========================================================= */

.spk-floating-actions.is-dragging {

    transition: none;

    transform: none;

    box-shadow:
        0 20px 45px rgba(15,23,42,.20),
        0 5px 12px rgba(15,23,42,.08);

}

.spk-floating-actions.is-dragging
.spk-floating-header {

    cursor: grabbing;

}

.spk-floating-actions:not(.is-dragging) {
    will-change: transform;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .spk-floating-actions {

        width: 185px;

        right: 10px;
        top: 50%;
        transform: translateY(-50%);

    }

}
        /* CONTAINER BOX */
        .box {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .spk-cell-selected {
            background: #dbeafe !important;
            outline: 2px solid #3b82f6 !important;
            outline-offset: -2px;
        }

        /* TOP STICKY TOOLBAR */
        .box-header {
            height: 52px;
            background: #ffffff;
            border-bottom: 1px solid var(--spk-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .box-header h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--spk-text);
            letter-spacing: 0.3px;
        }

        .box-header .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .mode-badge {
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mode-badge.edit {
            background: var(--spk-pink);
        }

        .mode-badge.create {
            background: var(--spk-green);
        }

        .box-header select.form-control-sm {
            height: 30px;
            border: 1px solid var(--spk-border);
            background: #ffffff;
            border-radius: 4px;
            padding: 0 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--spk-text);
            outline: none;
        }

        .box-header .btn-tool {
            height: 30px;
            border: 1px solid var(--spk-border);
            background: #ffffff;
            border-radius: 4px;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 600;
            color: var(--spk-text);
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .box-header .btn-tool:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .box-header .btn-tool.btn-blue {
            background: var(--spk-blue);
            color: #ffffff;
            border-color: var(--spk-blue);
        }

        .box-header .btn-tool.btn-blue:hover {
            background: #0669b8;
        }

        /* MAIN CARD CONTAINER */
        .spk-wrapper {
            max-width: 1560px;
            margin: 10px auto !important;
            padding: 0 12px 24px !important;
        }

        .spk-main-card {
            background: #ffffff;
            border: 1px solid var(--spk-border);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }

        /* HEAD LOGO & SEARCH GRID */
        .spk-card-head {
            display: grid;
            grid-template-columns: 38% 62%;
            min-height: 82px;
            border-bottom: 1px solid var(--spk-border);
        }

        .spk-logo-area {
            border-right: 1px solid var(--spk-border);
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background: #fafbfc;
        }

        .spk-logo-area img {
            max-height: 56px;
            width: auto;
            object-fit: contain;
        }

        .spk-search-area {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 10px 16px;
            background: #ffffff;
            position: relative;
        }

        .spk-searchbox {
            width: min(480px, 100%);
            position: relative;
        }

        .spk-searchbox label {
            display: block;
            text-align: right;
            color: var(--spk-muted);
            font-size: 9px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .spk-search-input {
            width: 100%;
            min-height: 32px;
            border: 1px solid var(--spk-border);
            border-radius: 4px;
            padding: 6px 10px;
            background: #ffffff;
            font-size: 11px;
            color: var(--spk-text);
            outline: none;
            transition: border-color 0.15s ease;
        }

        .spk-search-input:focus {
            border-color: var(--spk-blue);
            box-shadow: 0 0 0 2px rgba(8, 123, 217, 0.15);
        }

        /* AUTOCOMPLETE RESULTS */
        .suggest-box,
        #supplierSuggest,
        #itemSuggest {
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            background: #ffffff;
            border: 1px solid var(--spk-border);
            border-radius: 4px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            max-height: 240px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .suggest-item {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            font-size: 11px;
        }

        .suggest-item:hover {
            background: #eff6ff;
        }

        /* META INFORMATION GRID */
        .spk-meta-grid {
            display: grid;
            grid-template-columns: 1.3fr 1.3fr 1fr;
            border-bottom: 1px solid var(--spk-border);
        }

        .spk-meta-col {
            padding: 8px 12px;
            border-right: 1px solid var(--spk-border);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .spk-meta-col:last-child {
            border-right: none;
        }

        .spk-meta-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: var(--spk-muted);
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .spk-meta-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--spk-text);
            min-height: 22px;
            outline: none;
        }

        .spk-meta-value.editable:focus {
            background: #eff6ff;
            outline: 1px solid var(--spk-blue);
            padding: 2px 4px;
            border-radius: 3px;
        }

        .spk-meta-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-spk-save {
            height: 29px;
            background: var(--spk-green);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0 10px;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.15s ease;
        }

        .btn-spk-save:hover {
            background: #439d4c;
        }

        .btn-spk-status {
            height: 29px;
            background: #1e293b;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0 9px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-spk-status:hover {
            background: #0f172a;
        }

        .btn-spk-clean {
            height: 29px;
            background: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 0 8px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-spk-clean:hover {
            background: #dc2626;
        }

        /* DATES GRID */
        .spk-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid var(--spk-border);
        }

        .spk-date-col {
            padding: 7px 12px;
            border-right: 1px solid var(--spk-border);
        }

        .spk-date-col:last-child {
            border-right: none;
        }

        /* DATE PICKER COMPONENT */
        .spk-date-wrap {
            position: relative;
            width: 100%;
            max-width: 220px;
            height: 28px;
        }

        .spk-date-display {
            width: 100%;
            height: 28px;
            border: 1px solid var(--spk-border);
            border-radius: 4px;
            padding: 0 28px 0 8px;
            font-size: 11px;
            font-weight: 600;
            color: var(--spk-text);
            background: #ffffff;
            cursor: pointer;
            outline: none;
        }

        .spk-date-display:focus {
            border-color: var(--spk-blue);
            box-shadow: 0 0 0 2px rgba(8, 123, 217, 0.15);
        }

        .spk-date-picker {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
            right: 2px;
            top: 50%;
        }

        /* PPN CONTROL */
        .spk-ppn-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
        }

        .btn-add-ppn {
            height: 24px;
            padding: 0 9px;
            border: 1px solid var(--spk-blue);
            border-radius: 4px;
            background: #fff;
            color: var(--spk-blue);
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-add-ppn:hover {
            background: #eff6ff;
        }

        .btn-add-ppn.active {
            background: var(--spk-blue);
            color: #fff;
        }

        .spk-table .ppn-cell {
            width: 58px !important;
            min-width: 58px !important;
            text-align: center !important;
            font-weight: 700;
            color: #2563eb;
            font-variant-numeric: tabular-nums;
        }

        .spk-table .ppn-cell.ppn-hidden,
        .spk-table thead .ppn-header.ppn-hidden {
            display: none !important;
        }

        .spk-table .ppn-cell:not(.ppn-hidden) {
            background: #eff6ff;
        }

        .spk-date-wrap::after {
            content: '📅';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            pointer-events: none;
        }

        /* ITEM SECTION & TABLE */
        .spk-section-bar {
            height: 34px;
            background: var(--spk-navy);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .spk-table-scroll {
            width: 100%;
            overflow-x: auto;
            background: #ffffff;
            scrollbar-width: thin;
        }

        .spk-table {
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            font-size: 11px;
            min-width: 1100px;
        }

        .spk-table thead th {
            background: var(--spk-navy) !important;
            color: #ffffff !important;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            padding: 6px 6px !important;
            border: 1px solid #3b5299 !important;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .spk-table tbody td {
            border: 1px solid var(--spk-border) !important;
            padding: 4px 6px !important;
            vertical-align: middle;
            color: var(--spk-text);
            background: #ffffff;
            font-size: 11px;
        }

        .spk-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* SPECIFIC COLUMN WIDTHS */
        .select-item-cell,
        .spk-table th.c {
            width: 32px !important;
            text-align: center !important;
        }

        .kode-item,
        .spk-table th.a {
            width: 75px !important;
            text-align: center !important;
            font-weight: 600;
        }

        .gambar-cell,
        .spk-table th.im {
            width: 72px !important;
            text-align: center !important;
        }

        .nama,
        .spk-table th.nm {
            min-width: 130px !important;
            font-weight: 600;
        }

        .spk-dynamic-header,
        .custom-column {
            min-width: 65px !important;
        }

        .spk-table .p,
        .spk-table .l,
        .spk-table .t {
            width: 44px !important;
            text-align: center !important;
        }

        .spk-table .material {
            min-width: 110px !important;
            line-height: 1.35;
            white-space: pre-line;
        }

        .spk-table .pcs,
        .spk-table .set {
            width: 50px !important;
            text-align: center !important;
        }

        .spk-table .harga {
            width: 85px !important;
            text-align: right !important;
            font-variant-numeric: tabular-nums;
        }

        .spk-table .total {
            width: 95px !important;
            text-align: right !important;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .spk-table .note-box,
        .spk-table .catatan-cell {
            min-width: 80px !important;
        }

        .spk-table .action-cell {
            width: 34px !important;
            text-align: center !important;
        }

        /* IMAGE PREVIEW & UPLOAD */
        .image-box {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 3px;
            min-height: 44px;
        }

        .preview-img {
            max-width: 58px !important;
            max-height: 44px !important;
            object-fit: contain;
            border-radius: 3px;
            border: 1px solid var(--spk-border);
        }

        /* PLUS BUTTON ON HEADER */
        .btn-add-header-plus {
            width: 18px;
            height: 18px;
            border: 0;
            border-radius: 50%;
            background: #ffffff;
            color: var(--spk-navy);
            font-weight: bold;
            font-size: 11px;
            line-height: 18px;
            cursor: pointer;
            margin-left: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
        }

        .btn-add-header-plus:hover {
            background: #f1f5f9;
        }

        /* ADD & DELETE EXTRA ROW BUTTONS */
        .btn-add-extra {
            width: 22px !important;
            height: 22px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            background: #f8fafc !important;
            color: #334155 !important;
            cursor: pointer;
            font-size: 10px !important;
        }

        .btn-add-extra:hover {
            background: #e2e8f0 !important;
        }

        .btn-delete-extra {
            width: 22px !important;
            height: 22px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 4px !important;
            background: #ef4444 !important;
            color: #ffffff !important;
            cursor: pointer;
            font-size: 10px !important;
        }

        .btn-delete-extra:hover {
            background: #dc2626 !important;
        }

        /* EDITABLE & EXCEL SELECTION */
        .spk-table td.editable {
            cursor: text;
            user-select: text;
            transition: background-color .1s ease, box-shadow .1s ease;
        }

        .spk-table td.editable:hover {
            background: #f7faff !important;
        }

        .spk-table td.editable:focus {
            outline: 2px solid var(--spk-blue) !important;
            outline-offset: -2px;
            background: #eff6ff !important;
        }

        .spk-table td.excel-selected {
            background: #dbeafe !important;
            box-shadow: inset 0 0 0 1px #3b82f6 !important;
        }

        .spk-table td.excel-selected:focus {
            background: #bfdbfe !important;
            outline: 2px solid var(--spk-blue) !important;
        }

        /* BOTTOM SECTION (2-COL SPLIT LIKE preview.html) */
        .spk-lower-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(360px, 0.95fr);
            gap: 10px;
            margin-top: 10px;
        }

        .spk-sub-card {
            background: #ffffff;
            border: 1px solid var(--spk-border);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }

        .spk-sub-title-green {
            height: 32px;
            background: var(--spk-green);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .spk-sub-title-navy {
            height: 32px;
            background: var(--spk-navy);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .spk-sub-title-navy .btn-add-pay {
            height: 22px;
            background: var(--spk-blue);
            color: #ffffff;
            border: none;
            border-radius: 3px;
            padding: 0 7px;
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
        }

        .spk-simple-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .spk-simple-table th {
            background: var(--spk-navy);
            color: #ffffff;
            padding: 6px 5px;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #3b5299;
            white-space: nowrap;
        }

        .spk-simple-table td {
            border: 1px solid var(--spk-border);
            padding: 5px 6px;
            font-size: 10px;
            vertical-align: middle;
        }

        .spk-simple-table td.empty-text {
            color: var(--spk-muted);
            text-align: center;
            padding: 16px !important;
        }

        /* PAYMENT ROW CONTROLS */
        .payment-row .total-amount {
            text-align: right;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .payment-row .payment-type {
            width: 100%;
            height: 26px;
            border: 1px solid var(--spk-border);
            border-radius: 3px;
            font-size: 10px;
            padding: 0 4px;
        }

        /* PAYMENT SUMMARY BOX */
        #paymentSummary {
            padding: 10px 12px;
            border-top: 1px solid var(--spk-border);
            background: #f8fafc;
            font-size: 11px;
            line-height: 1.6;
        }

        /* =========================================================
           JUMP TO SIGNATURE HIGHLIGHT
           ========================================================= */
        .spk-signature-card.spk-jump-highlight {
            animation: spkSignatureFlash 1.4s ease;
        }

        @keyframes spkSignatureFlash {
            0% {
                box-shadow: 0 0 0 0 rgba(49, 88, 201, 0);
            }
            25% {
                box-shadow: 0 0 0 5px rgba(49, 88, 201, .16);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(49, 88, 201, 0);
            }
        }

        /* SIGNATURE AREA */
        .spk-signature-card {
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid var(--spk-border);
            border-radius: 6px;
            overflow: hidden;
        }

        .spk-signature-header {
            height: 32px;
            background: #1e293b;
            color: #ffffff;
            display: flex;
            align-items: center;
            padding: 0 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .spk-signature-body {
            padding: 10px;
            overflow-x: auto;
        }

        @media (max-width: 1000px) {
            .spk-card-head {
                grid-template-columns: 1fr;
            }

            .spk-logo-area {
                border-right: none;
                border-bottom: 1px solid var(--spk-border);
            }

            .spk-meta-grid {
                grid-template-columns: 1fr 1fr;
            }

            .spk-meta-col:last-child {
                grid-column: 1 / -1;
                border-top: 1px solid var(--spk-border);
                border-right: none;
            }

            .spk-lower-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            .box-header {
                height: auto;
                padding: 8px 10px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .box-header .toolbar-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .spk-meta-grid,
            .spk-dates-grid {
                grid-template-columns: 1fr;
            }

            .spk-meta-col,
            .spk-date-col {
                border-right: none;
                border-bottom: 1px solid var(--spk-border);
            }
        }
    </style>
<style>
/* =========================================================
   SPK ASSIGN / MULTI SPK MAP
   ========================================================= */
.spk-assign-page{
    position:relative;
    min-height:100vh;
    padding:10px 305px 50px 10px;
    background:var(--spk-bg,#f4f6f9);
}
.spk-assign-content{max-width:1560px;margin:0 auto;}
.spk-assign-card{
    position:relative;
    margin:0 0 18px;
    background:#fff;
    border:1px solid var(--spk-border,#d8dee8);
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 2px 8px rgba(15,23,42,.05);
    scroll-margin-top:70px;
    transition:box-shadow .2s ease,border-color .2s ease;
}
.spk-assign-card.is-current{
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(59,130,246,.10),0 8px 24px rgba(15,23,42,.07);
}
.spk-assign-card-head{
    min-height:46px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:7px 12px;
    background:#fff;
    border-bottom:1px solid var(--spk-border,#d8dee8);
    position:sticky;
    top:0;
    z-index:20;
}
.spk-assign-card-title{display:flex;align-items:center;gap:8px;min-width:0;}
.spk-assign-index{
    min-width:25px;height:25px;display:inline-flex;align-items:center;justify-content:center;
    border-radius:6px;background:#eef3ff;color:#3158c9;font-size:9px;font-weight:800;
}
.spk-assign-card-title strong{font-size:12px;color:var(--spk-text,#172033);white-space:nowrap;}
.spk-assign-card-title small{font-size:9px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.spk-assign-open{
    height:28px;padding:0 9px;display:inline-flex;align-items:center;gap:5px;
    border-radius:5px;background:#1e293b;color:#fff;text-decoration:none;font-size:9px;font-weight:700;
    white-space:nowrap;
}
.spk-assign-open:hover{color:#fff;background:#0f172a;text-decoration:none;}
.spk-assign-inner{padding:10px 12px 14px;}
.spk-assign-meta{display:grid;grid-template-columns:1fr 1fr 1.5fr;border:1px solid var(--spk-border,#d8dee8);margin-bottom:10px;}
.spk-assign-meta-col{padding:7px 10px;border-right:1px solid var(--spk-border,#d8dee8);min-width:0;}
.spk-assign-meta-col:last-child{border-right:0;}
.spk-assign-meta-col label{display:block;font-size:8px;color:#737b88;font-weight:800;text-transform:uppercase;margin-bottom:3px;}
.spk-assign-meta-col strong{font-size:11px;color:#172033;}
.spk-assign-table-wrap{width:100%;overflow-x:auto;background:#fff;}
.spk-assign-items{width:100%;min-width:1100px;border-collapse:collapse;font-size:10px;}
.spk-assign-items th{background:var(--spk-navy,#50b95a);color:#fff;padding:6px 5px;border:1px solid #3b5299;font-size:9px;white-space:nowrap;text-align:center;}
.spk-assign-items td{padding:5px 6px;border:1px solid var(--spk-border,#d8dee8);font-size:10px;vertical-align:middle;background:#fff;}
.spk-assign-items .num{text-align:center;white-space:nowrap;}
.spk-assign-items .money{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap;}
.spk-assign-items .name{font-weight:600;min-width:140px;}
.spk-assign-items .material{min-width:110px;white-space:pre-line;}
.spk-assign-section-title{height:32px;background:var(--spk-navy,#50b95a);color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 10px;margin-top:10px;font-size:10px;font-weight:700;}
.spk-assign-lower{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(360px,.95fr);gap:10px;margin-top:10px;}
.spk-assign-subcard{background:#fff;border:1px solid var(--spk-border,#d8dee8);border-radius:6px;overflow:hidden;}
.spk-assign-subtitle{height:32px;display:flex;align-items:center;justify-content:center;padding:0 10px;background:var(--spk-green,#50b95a);color:#fff;font-size:9px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;}
.spk-assign-subtitle.dark{background:#1e293b;}
.spk-assign-simple{width:100%;border-collapse:collapse;font-size:9px;}
.spk-assign-simple th{background:var(--spk-navy,#50b95a);color:#fff;padding:6px 4px;border:1px solid #3b5299;font-size:8px;white-space:nowrap;text-align:center;}
.spk-assign-simple td{border:1px solid var(--spk-border,#d8dee8);padding:5px 5px;vertical-align:middle;font-size:9px;}
.spk-assign-simple .right{text-align:right;font-variant-numeric:tabular-nums;}
.spk-assign-signature{margin-top:10px;background:#fff;border:1px solid var(--spk-border,#d8dee8);border-radius:6px;overflow:hidden;}
.spk-assign-signature-head{height:34px;background:#1e293b;color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 12px;font-size:10px;font-weight:700;}
.spk-assign-signature-head small{font-size:8px;color:#cbd5e1;font-weight:500;}
.spk-assign-signature-body{padding:10px;overflow-x:auto;}
.spk-sign-grid{width:100%;min-width:720px;border-collapse:collapse;text-align:center;}
.spk-sign-grid th{background:#f8fafc;color:#334155;border:1px solid var(--spk-border,#d8dee8);padding:6px 5px;font-size:8px;font-weight:800;}
.spk-sign-grid td{border:1px solid var(--spk-border,#d8dee8);padding:7px 5px;vertical-align:middle;font-size:9px;}
.spk-sign-grid .sign-box{height:78px;display:flex;align-items:center;justify-content:center;}
.spk-sign-grid img{max-height:62px;max-width:105px;object-fit:contain;}
.spk-sign-grid .pending{display:inline-flex;align-items:center;gap:4px;padding:4px 7px;border-radius:5px;background:#fff7ed;color:#c2410c;font-size:8px;font-weight:700;}
.spk-sign-grid .signed{display:inline-flex;align-items:center;gap:4px;padding:3px 6px;border-radius:5px;background:#ecfdf5;color:#047857;font-size:8px;font-weight:700;}
.spk-sign-grid .person{font-weight:700;color:#172033;}
.spk-sign-grid .division{display:block;font-size:7px;color:#64748b;margin-top:2px;}
.spk-sign-grid .date{display:block;font-size:7px;color:#94a3b8;margin-top:2px;}
.spk-map{
    position:fixed;right:16px;top:50%;transform:translateY(-50%);width:270px;max-height:72vh;
    background:rgba(255,255,255,.98);border:1px solid #dce3ec;border-radius:12px;
    box-shadow:0 15px 40px rgba(15,23,42,.14);overflow:hidden;z-index:9999;
}
.spk-map-header{height:44px;padding:0 11px;display:flex;align-items:center;justify-content:space-between;background:#1e293b;color:#fff;cursor:move;user-select:none;}
.spk-map-header strong{display:block;font-size:10px;}
.spk-map-header small{display:block;margin-top:2px;font-size:8px;color:#cbd5e1;}
.spk-map-count{min-width:22px;height:22px;display:flex;align-items:center;justify-content:center;border-radius:6px;background:#50b95a;color:#fff;font-size:8px;font-weight:800;}
.spk-map-list{max-height:calc(72vh - 44px);overflow-y:auto;padding:6px;}
.spk-map-item{width:100%;display:flex;align-items:center;gap:7px;padding:7px;border:0;border-bottom:1px solid #eef2f7;background:#fff;text-align:left;cursor:pointer;border-radius:6px;transition:.15s ease;}
.spk-map-item:hover{background:#f8fafc;}
.spk-map-item.active{background:#eff6ff;}
.spk-map-dot{width:8px;height:8px;min-width:8px;border-radius:50%;background:#cbd5e1;}
.spk-map-item.active .spk-map-dot{background:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12);}
.spk-map-info{min-width:0;flex:1;}
.spk-map-info strong{display:block;font-size:9px;color:#172033;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.spk-map-info small{display:block;font-size:7px;color:#8a96a6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;}
.spk-map-status{min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;border-radius:5px;background:#f1f5f9;color:#64748b;font-size:7px;font-weight:800;}
.spk-map-item.active .spk-map-status{background:#dbeafe;color:#1d4ed8;}
.spk-assign-float-actions{position:fixed;left:18px;top:50%;transform:translateY(-50%);z-index:9998;display:flex;flex-direction:column;gap:5px;}
.spk-assign-float-btn{height:34px;padding:0 9px;border:1px solid #dbe2ea;border-radius:7px;background:rgba(255,255,255,.97);box-shadow:0 8px 20px rgba(15,23,42,.10);font-size:9px;font-weight:700;color:#334155;cursor:pointer;}
.spk-assign-float-btn:hover{background:#f8fafc;}
@media(max-width:1100px){
    .spk-assign-page{padding-right:15px;}
    .spk-map{right:10px;width:220px;}
    .spk-assign-lower{grid-template-columns:1fr;}
}
@media(max-width:800px){
    .spk-map{width:205px;max-height:55vh;}
    .spk-map-list{max-height:calc(55vh - 44px);}
    .spk-assign-card-head{position:relative;}
    .spk-assign-meta{grid-template-columns:1fr;}
    .spk-assign-meta-col{border-right:0;border-bottom:1px solid var(--spk-border,#d8dee8);}
    .spk-assign-meta-col:last-child{border-bottom:0;}
}
</style>


@php
    $spks = $spks ?? [];
@endphp

<div class="spk-assign-page" id="spkAssignPage">

    {{-- =========================================================
         FLOATING MINI MAP
         ========================================================= --}}
    <aside class="spk-map" id="spkAssignMap">
        <div class="spk-map-header" id="spkAssignMapDrag">
            <div>
                <strong>SPK APPROVAL</strong>
                <small>SPK yang belum lengkap tanda tangan</small>
            </div>
            <span class="spk-map-count">{{ count($spks) }}</span>
        </div>

        <div class="spk-map-list" id="spkAssignMapList">
            @foreach($spks as $index => $spk)
                <button type="button"
                    class="spk-map-item {{ $index === 0 ? 'active' : '' }}"
                    data-target="assign-spk-{{ $spk['id'] }}">
                    <span class="spk-map-dot"></span>
                    <span class="spk-map-info">
                        <strong>{{ $spk['no_spk'] ?? '-' }}</strong>
                        <small>{{ $spk['nama'] ?? '-' }}</small>
                    </span>
                    <span class="spk-map-status">{{ $index + 1 }}</span>
                </button>
            @endforeach
        </div>
    </aside>

    <div class="spk-assign-float-actions">
        <button type="button" class="spk-assign-float-btn" id="btnAssignTop">↑ TOP</button>
        <button type="button" class="spk-assign-float-btn" id="btnAssignSignature">↓ SIGN</button>
    </div>

    <main class="spk-assign-content" id="spkAssignContent">

        @forelse($spks as $index => $spk)

            @php
                $items = collect($spk['items'] ?? []);
                $signature = $spk['signature'] ?? null;
                $payments = $spk['payments'] ?? [];
                $customHeaders = $spk['custom_headers'] ?? [];
                $grandTotal = 0;

                foreach ($items as $item) {
                    $grandTotal += (float) ($item['total'] ?? 0);
                }
            @endphp

            <section class="spk-assign-card {{ $index === 0 ? 'is-current' : '' }}"
                id="assign-spk-{{ $spk['id'] }}"
                data-spk-id="{{ $spk['id'] }}"
                data-no-spk="{{ $spk['no_spk'] ?? '-' }}">

                {{-- =================================================
                     CARD HEADER
                     ================================================= --}}
                <div class="spk-assign-card-head">
                    <div class="spk-assign-card-title">
                        <span class="spk-assign-index">#{{ $index + 1 }}</span>
                        <strong>{{ $spk['no_spk'] ?? '-' }}</strong>
                        <small>{{ $spk['nama'] ?? '-' }}</small>
                    </div>

                    <div style="display:flex;align-items:center;gap:6px;">
                        <span class="mode-badge edit">APPROVAL</span>
                        <a href="{{ route('spk.edit', $spk['id']) }}"
                            target="_blank"
                            class="spk-assign-open">
                            ↗ Buka SPK
                        </a>
                    </div>
                </div>

                <div class="spk-assign-inner">

                    {{-- =================================================
                         HEADER META - SAME FEEL AS INDEX SPK
                         ================================================= --}}
                    <div class="spk-assign-meta">
                        <div class="spk-assign-meta-col">
                            <label>NO SPK</label>
                            <strong>{{ $spk['no_spk'] ?? '-' }}</strong>
                        </div>
                        <div class="spk-assign-meta-col">
                            <label>NO PO</label>
                            <strong>{{ $spk['no_po'] ?? '-' }}</strong>
                        </div>
                        <div class="spk-assign-meta-col">
                            <label>NAMA SUPPLIER</label>
                            <strong>{{ $spk['nama'] ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="spk-assign-meta" style="grid-template-columns:1fr 1fr 1fr;">
                        <div class="spk-assign-meta-col">
                            <label>TGL TERIMA</label>
                            <strong>
                                @if(!empty($spk['tgl_terima']))
                                    {{ \Carbon\Carbon::parse($spk['tgl_terima'])->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                        <div class="spk-assign-meta-col">
                            <label>TGL SELESAI</label>
                            <strong>
                                @if(!empty($spk['tgl_selesai']))
                                    {{ \Carbon\Carbon::parse($spk['tgl_selesai'])->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                        <div class="spk-assign-meta-col">
                            <label>JENIS</label>
                            <strong>{{ strtoupper($spk['type'] ?? '-') }}</strong>
                        </div>
                    </div>

                    {{-- =================================================
                         ITEM SPK
                         ================================================= --}}
                    <div class="spk-section-bar">
                        <span><b>ITEM SPK</b></span>
                        <span>{{ $items->count() }} item</span>
                    </div>

                    <div class="spk-assign-table-wrap">
                        <table class="spk-assign-items">
                            <thead>
                                <tr>
                                    <th style="width:30px;">No</th>
                                    <th style="width:75px;">Article</th>
                                    <th style="width:70px;">Gambar</th>
                                    <th>Nama Barang</th>
                                    @foreach($customHeaders as $header)
                                        <th>{{ $header['label'] ?? $header['key'] ?? '-' }}</th>
                                    @endforeach
                                    <th>P</th>
                                    <th>L</th>
                                    <th>T</th>
                                    <th>Material</th>
                                    <th>PCS</th>
                                    <th>SET</th>
                                    <th>Harga</th>
                                    <th>PPN</th>
                                    <th>Total</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $itemIndex => $item)
                                    @php
                                        $mainCustom = $item['custom_columns'][0] ?? [];
                                        $images = $item['images'] ?? [];
                                        $itemTotal = (float) ($item['total'] ?? 0);
                                        if ($itemTotal <= 0) {
                                            $qty = (float) ($item['pcs'] ?? 0) + (float) ($item['set'] ?? 0);
                                            $itemTotal = $qty * (float) ($item['harga'] ?? 0);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="num">{{ $itemIndex + 1 }}</td>
                                        <td class="num">{{ $item['kode'] ?? '-' }}</td>
                                        <td class="num">
                                            @if(count($images))
                                                <div class="image-box">
                                                    @foreach($images as $img)
                                                        <img src="{{ $img }}" class="preview-img">
                                                    @endforeach
                                                </div>
                                            @else
                                                <span style="color:#94a3b8;font-size:8px;">-</span>
                                            @endif
                                        </td>
                                        <td class="name">{{ $item['nama'] ?? '-' }}</td>
                                        @foreach($customHeaders as $header)
                                            <td>
                                                {{ $mainCustom[$header['key'] ?? ''] ?? '' }}
                                            </td>
                                        @endforeach
                                        <td class="num">{{ $item['p'] ?? '-' }}</td>
                                        <td class="num">{{ $item['l'] ?? '-' }}</td>
                                        <td class="num">{{ $item['t'] ?? '-' }}</td>
                                        <td class="material">{{ $item['material'] ?? '-' }}</td>
                                        <td class="num">{{ $item['pcs'] ?? 0 }}</td>
                                        <td class="num">{{ $item['set'] ?? 0 }}</td>
                                        <td class="money">Rp {{ number_format((float)($item['harga'] ?? 0), 0, ',', '.') }}</td>
                                        <td class="num">
                                            @if(!empty($spk['ppn_enabled']))
                                                {{ $spk['ppn_rate'] ?? 11 }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="money">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                                        <td>{{ is_array($item['catatan'] ?? null) ? ($item['catatan']['remark'] ?? '') : ($item['catatan'] ?? '') }}</td>
                                    </tr>

                                    @foreach(array_slice($item['custom_columns'] ?? [], 1) as $extra)
                                        <tr style="background:#fafbfc;">
                                            <td></td>
                                            <td colspan="2"></td>
                                            <td></td>
                                            @foreach($customHeaders as $header)
                                                <td>{{ $extra[$header['key'] ?? ''] ?? '' }}</td>
                                            @endforeach
                                            <td class="num">{{ $extra['p'] ?? '-' }}</td>
                                            <td class="num">{{ $extra['l'] ?? '-' }}</td>
                                            <td class="num">{{ $extra['t'] ?? '-' }}</td>
                                            <td>{{ $extra['material'] ?? '-' }}</td>
                                            <td class="num">{{ $extra['pcs'] ?? 0 }}</td>
                                            <td class="num">{{ $extra['set'] ?? 0 }}</td>
                                            <td class="money">Rp {{ number_format((float)($extra['harga'] ?? 0), 0, ',', '.') }}</td>
                                            <td>-</td>
                                            <td class="money">Rp {{ number_format((float)($extra['total'] ?? 0), 0, ',', '.') }}</td>
                                            <td>{{ $extra['catatan'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="{{ 14 + count($customHeaders) }}" style="text-align:center;color:#94a3b8;padding:20px;">
                                            Tidak ada item SPK.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ 12 + count($customHeaders) }}" style="text-align:right;font-weight:800;background:#f8fafc;">
                                        GRAND TOTAL
                                    </td>
                                    <td colspan="2" class="money" style="font-weight:800;background:#f8fafc;">
                                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- =================================================
                         LOWER SECTION - SAME STRUCTURE AS INDEX
                         ================================================= --}}
                    <div class="spk-assign-lower">

                        <section class="spk-assign-subcard">
                            <div class="spk-assign-subtitle">
                                LIST BAHAN BAKU PENGAMBILAN (by warehouse)
                            </div>
                            <div style="overflow-x:auto;">
                                <table class="spk-assign-simple">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Bahan</th>
                                            <th>Potong Bahan</th>
                                            <th>Harga Inv</th>
                                            <th>Harga Adj</th>
                                            <th>Total</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" style="text-align:center;color:#94a3b8;padding:16px;">
                                                Waiting Warehouse to Out Bahan Baku's
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="spk-assign-subcard">
                            <div class="spk-assign-subtitle dark">
                                PAYMENT REQUEST
                            </div>
                            <div style="overflow-x:auto;">
                                <table class="spk-assign-simple">
                                    <thead>
                                        <tr>
                                            <th>Req</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Note</th>
                                            <th>Keterangan</th>
                                            <th>Adj Finance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $pay)
                                            @php
                                                $amount = preg_replace('/[^\\d-]/', '', (string)($pay['amount'] ?? 0));
                                                $amount = (float)($amount ?: 0);
                                                $adjustment = (float)($pay['adjustment'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td style="text-align:center;">
                                                    {{ !empty($pay['is_request']) ? '✓' : '—' }}
                                                </td>
                                                <td class="right">Rp {{ number_format($amount,0,',','.') }}</td>
                                                <td>{{ $pay['date'] ?? '-' }}</td>
                                                <td>{{ strtoupper($pay['note'] ?? '-') }}</td>
                                                <td>{{ $pay['note_tambahan'] ?? '' }}</td>
                                                <td>
                                                    @if($adjustment > 0)
                                                        <div style="color:#16a34a;font-weight:700;">Bayar: Rp {{ number_format($adjustment,0,',','.') }}</div>
                                                        <div style="color:#dc2626;">Sisa: Rp {{ number_format($amount-$adjustment,0,',','.') }}</div>
                                                    @elseif(!empty($pay['is_request']))
                                                        <span style="color:#16a34a;font-weight:700;">Full Payment</span>
                                                    @else
                                                        <span style="color:#94a3b8;">Belum Request</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align:center;color:#94a3b8;padding:16px;">Belum ada payment request.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                    </div>

                    {{-- =================================================
                         APPROVAL SIGNATURE
                         ================================================= --}}
                    @if($signature)
                        <div class="spk-assign-signature signature-target">

                            <div class="spk-assign-signature-head">
                                <span>APPROVAL SPK</span>
                                <small>Signature ID #{{ $signature->id }}</small>
                            </div>

                            <div class="spk-assign-signature-body">
                                <table class="spk-sign-grid">
                                    <thead>
                                        <tr>
                                            <th>Made By</th>
                                            <th>Checked By</th>
                                            <th>Checked By 2</th>
                                            <th>Approved By</th>
                                            <th>Supplier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            {{-- MADE --}}
                                            <td>
                                                <div class="sign-box">
                                                    @if($signature->made_at)
                                                        <div>
                                                            <img src="{{ asset('assets/signature/'.$signature->made_by.'.png') }}">
                                                            <div class="signed">✓ Signed</div>
                                                        </div>
                                                    @else
                                                        <span class="pending">● Pending</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- CHECKED --}}
                                            <td>
                                                <div class="sign-box">
                                                    @if($signature->checked_at)
                                                        <div>
                                                            <img src="{{ asset('assets/signature/'.$signature->checked_by.'.png') }}">
                                                            <div class="signed">✓ Signed</div>
                                                        </div>
                                                    @else
                                                        <button type="button" class="btn btn-warning btn-sm btn-sign"
                                                            data-id="{{ $signature->id }}" data-type="checked">
                                                            TAP TO SIGN
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- CHECKED 2 --}}
                                            <td>
                                                <div class="sign-box">
                                                    @if($signature->checked_at_2)
                                                        <div>
                                                            <img src="{{ asset('assets/signature/'.$signature->checked_by_2.'.png') }}">
                                                            <div class="signed">✓ Signed</div>
                                                        </div>
                                                    @else
                                                        <button type="button" class="btn btn-warning btn-sm btn-sign"
                                                            data-id="{{ $signature->id }}" data-type="checked_2">
                                                            TAP TO SIGN
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- APPROVED --}}
                                            <td>
                                                <div class="sign-box">
                                                    @if($signature->approved_at)
                                                        <div>
                                                            <img src="{{ asset('assets/signature/'.$signature->approved_by.'.png') }}">
                                                            <div class="signed">✓ Signed</div>
                                                        </div>
                                                    @else
                                                        <button type="button" class="btn btn-success btn-sm btn-sign"
                                                            data-id="{{ $signature->id }}" data-type="approved">
                                                            TAP TO SIGN
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- SUPPLIER --}}
                                            <td>
                                                <div class="sign-box">
                                                    <strong>{{ $signature->supplier->name ?? $spk['nama'] ?? '-' }}</strong>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            {{-- MADE INFO --}}
                                            <td>
                                                <span class="person">{{ $signature->madeBy->name ?? '-' }}</span>
                                                <span class="division">{{ strtoupper($signature->madeBy?->karyawan?->divisi?->nama ?? '-') }}</span>
                                                <span class="date">{{ $signature->made_at ? $signature->made_at->format('d/m/Y H:i') : 'Pending' }}</span>
                                            </td>

                                            {{-- CHECKED INFO --}}
                                            <td>
                                                <span class="person">{{ $signature->checkedBy->name ?? '-' }}</span>
                                                <span class="division">{{ strtoupper($signature->checkedBy?->karyawan?->divisi?->nama ?? '-') }}</span>
                                                <span class="date">{{ $signature->checked_at ? $signature->checked_at->format('d/m/Y H:i') : 'Pending' }}</span>
                                            </td>

                                            {{-- CHECKED 2 INFO --}}
                                            <td>
                                                <span class="person">{{ $signature->checkedBy2->name ?? '-' }}</span>
                                                <span class="division">{{ strtoupper($signature->checkedBy2?->karyawan?->divisi?->nama ?? '-') }}</span>
                                                <span class="date">{{ $signature->checked_at_2 ? $signature->checked_at_2->format('d/m/Y H:i') : 'Pending' }}</span>
                                            </td>

                                            {{-- APPROVED INFO --}}
                                            <td>
                                                <span class="person">{{ $signature->approvedBy->name ?? '-' }}</span>
                                                <span class="division">{{ strtoupper($signature->approvedBy?->karyawan?->divisi?->nama ?? '-') }}</span>
                                                <span class="date">{{ $signature->approved_at ? $signature->approved_at->format('d/m/Y H:i') : 'Pending' }}</span>
                                            </td>

                                            <td>{{ $signature->supplier->name ?? $spk['nama'] ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>
            </section>

        @empty

            <div style="background:#fff;border:1px solid #d8dee8;border-radius:8px;padding:50px 20px;text-align:center;">
                <div style="font-size:30px;margin-bottom:10px;">✓</div>
                <strong style="font-size:14px;color:#172033;">Semua SPK sudah lengkap</strong>
                <div style="font-size:10px;color:#94a3b8;margin-top:5px;">Tidak ada SPK yang menunggu approval.</div>
            </div>

        @endforelse

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
    'use strict';

    const page = document.getElementById('spkAssignPage');
    if (!page) return;

    const cards = Array.from(document.querySelectorAll('.spk-assign-card'));
    const mapItems = Array.from(document.querySelectorAll('.spk-map-item'));

    /* =========================================================
       MINI MAP CLICK -> SCROLL SPK
       ========================================================= */
    mapItems.forEach(function(item){
        item.addEventListener('click', function(){
            const target = document.getElementById(this.dataset.target);
            if (!target) return;

            target.scrollIntoView({
                behavior:'smooth',
                block:'start'
            });
        });
    });

    /* =========================================================
       SCROLL -> ACTIVE SPK DI MINI MAP
       ========================================================= */
    if ('IntersectionObserver' in window && cards.length) {
        const observer = new IntersectionObserver(function(entries){
            const visible = entries
                .filter(entry => entry.isIntersecting)
                .sort((a,b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (!visible) return;

            const id = visible.target.id;

            cards.forEach(card => {
                card.classList.toggle('is-current', card.id === id);
            });

            mapItems.forEach(item => {
                item.classList.toggle('active', item.dataset.target === id);
            });

            const activeMap = mapItems.find(item => item.dataset.target === id);
            if (activeMap) {
                activeMap.scrollIntoView({
                    behavior:'smooth',
                    block:'nearest'
                });
            }
        }, {
            threshold:[0.15,0.35,0.55],
            rootMargin:'-65px 0px -50% 0px'
        });

        cards.forEach(card => observer.observe(card));
    }

    /* =========================================================
       TOP
       ========================================================= */
    document.getElementById('btnAssignTop')?.addEventListener('click', function(){
        window.scrollTo({top:0, behavior:'smooth'});
    });

    /* =========================================================
       JUMP TO SIGNATURE YANG PALING DEKAT / AKTIF
       ========================================================= */
    document.getElementById('btnAssignSignature')?.addEventListener('click', function(){
        const current = document.querySelector('.spk-assign-card.is-current');
        const target = current?.querySelector('.signature-target') || document.querySelector('.signature-target');

        if (!target) {
            Swal.fire({icon:'info',title:'Tidak ada signature',text:'Tidak ditemukan area approval.'});
            return;
        }

        target.scrollIntoView({behavior:'smooth', block:'center'});

        target.animate([
            {boxShadow:'0 0 0 0 rgba(49,88,201,0)'},
            {boxShadow:'0 0 0 6px rgba(49,88,201,.18)'},
            {boxShadow:'0 0 0 0 rgba(49,88,201,0)'}
        ], {duration:1400});
    });

    /* =========================================================
       SIGNATURE / APPROVAL
       - sama endpoint dengan index SPK
       - tidak bergantung pada ID global
       ========================================================= */
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.btn-sign');
        if (!btn) return;

        const id = btn.dataset.id;
        const type = btn.dataset.type;
        if (!id || !type) return;

        let title = 'Approve?';

        switch(type){
            case 'made':
                title = 'Approve by Maker?';
                break;
            case 'checked':
                title = 'Approve by Checker 1?';
                break;
            case 'checked_2':
                title = 'Approve by Checker 2?';
                break;
            case 'approved':
                title = 'Approve by Mr. Stanley?';
                break;
        }

        Swal.fire({
            title:title,
            input:'textarea',
            inputLabel:'Remark',
            inputPlaceholder:'Masukkan remark...',
            inputAttributes:{rows:4},
            showCancelButton:true,
            confirmButtonText:'Approve',
            cancelButtonText:'Batal',
            focusConfirm:false
        }).then(function(result){
            if (!result.isConfirmed) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            btn.disabled = true;

            fetch(`/spk/signature/${id}`, {
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json',
                    'X-CSRF-TOKEN':csrf
                },
                body:JSON.stringify({
                    type:type,
                    remark:result.value || ''
                })
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan saat approval.');
                }
                return data;
            })
            .then(res => {
                Swal.fire({
                    icon:'success',
                    title:'Berhasil',
                    text:res.message || 'Approval berhasil.'
                }).then(() => window.location.reload());
            })
            .catch(err => {
                btn.disabled = false;
                Swal.fire({
                    icon:'error',
                    title:'Gagal',
                    text:err.message || 'Terjadi kesalahan.'
                });
            });
        });
    });

    /* =========================================================
       DRAG MINI MAP
       ========================================================= */
    const map = document.getElementById('spkAssignMap');
    const handle = document.getElementById('spkAssignMapDrag');

    if (map && handle) {
        let dragging = false;
        let offsetX = 0;
        let offsetY = 0;

        handle.addEventListener('mousedown', function(e){
            dragging = true;

            const rect = map.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;

            map.style.left = rect.left + 'px';
            map.style.top = rect.top + 'px';
            map.style.right = 'auto';
            map.style.transform = 'none';

            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', function(e){
            if (!dragging) return;

            const width = map.offsetWidth;
            const height = map.offsetHeight;

            let left = e.clientX - offsetX;
            let top = e.clientY - offsetY;

            left = Math.max(5, Math.min(left, window.innerWidth - width - 5));
            top = Math.max(5, Math.min(top, window.innerHeight - height - 5));

            map.style.left = left + 'px';
            map.style.top = top + 'px';
        });

        document.addEventListener('mouseup', function(){
            dragging = false;
            document.body.style.userSelect = '';
        });

        /* Touch */
        handle.addEventListener('touchstart', function(e){
            const touch = e.touches[0];
            const rect = map.getBoundingClientRect();
            dragging = true;
            offsetX = touch.clientX - rect.left;
            offsetY = touch.clientY - rect.top;
            map.style.left = rect.left + 'px';
            map.style.top = rect.top + 'px';
            map.style.right = 'auto';
            map.style.transform = 'none';
        }, {passive:true});

        document.addEventListener('touchmove', function(e){
            if (!dragging) return;
            const touch = e.touches[0];
            const width = map.offsetWidth;
            const height = map.offsetHeight;
            let left = touch.clientX - offsetX;
            let top = touch.clientY - offsetY;
            left = Math.max(5, Math.min(left, window.innerWidth - width - 5));
            top = Math.max(5, Math.min(top, window.innerHeight - height - 5));
            map.style.left = left + 'px';
            map.style.top = top + 'px';
        }, {passive:true});

        document.addEventListener('touchend', function(){
            dragging = false;
        });
    }
})();
</script>
@endsection
