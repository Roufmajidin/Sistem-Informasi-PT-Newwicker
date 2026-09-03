@extends('master.master')
@section('title', 'SPK Pages')
@section('content')
    {{-- @include('pages.spk.stylespk') --}}

    <style>
        /* =========================================================
       SPK EXPORT / SCREENSHOT / COPY - A4 LANDSCAPE
       ========================================================= */
       .bahan-keterangan-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #dee2e6;
    border-top-color: #0d6efd;
    border-radius: 50%;
    display: inline-block;
    animation: bahanKeteranganSpin 0.7s linear infinite;
}

@keyframes bahanKeteranganSpin {
    to {
        transform: rotate(360deg);
    }
}
        .spk-excel-image {
            width: 1123px !important;
            height: 794px !important;
            min-width: 1123px !important;
            max-width: 1123px !important;
            min-height: 794px !important;
            max-height: 794px !important;
            padding: 10px 12px !important;
            margin: 0 !important;
            background: #fff !important;
            color: #000 !important;
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 8px !important;
            line-height: 1.1 !important;
            overflow: hidden !important;
            box-sizing: border-box !important
        }

        .spk-excel-image *,
        .spk-excel-image *::before,
        .spk-excel-image *::after {
            box-sizing: border-box !important
        }

        .spk-excel-top {
            width: 100% !important;
            height: 58px !important;
            display: grid !important;
            grid-template-columns: 150px 1fr 300px !important;
            align-items: start !important;
            border-bottom: 2px solid #000 !important
        }

        .spk-excel-logo {
            height: 56px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important
        }

        .spk-excel-logo img {
            width: 110px !important;
            height: 48px !important;
            object-fit: contain !important
        }

        .spk-excel-company {
            text-align: right !important;
            font-size: 8px !important;
            line-height: 1.25 !important;
            padding-top: 0 !important
        }

        .spk-excel-company strong {
            font-size: 10px !important
        }

        .spk-excel-info {
            width: 100% !important;
            height: 55px !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 !important
        }

        .spk-excel-info col.label-col {
            width: 85px !important
        }

        .spk-excel-info col.po-col {
            width: 150px !important
        }

        .spk-excel-info td {
            border: 0 !important;
            height: 13px !important;
            padding: 0 3px !important;
            font-size: 8px !important;
            line-height: 1 !important;
            vertical-align: middle !important
        }

        .spk-excel-info .label {
            font-weight: bold !important;
            white-space: nowrap !important
        }

        .spk-excel-info .po {
            background: #ffff00 !important;
            font-weight: bold !important;
            text-align: center !important;
            white-space: nowrap !important
        }

        .spk-excel-item-area {
            width: 100% !important;
            height: 438px !important;
            overflow: hidden !important
        }

        .spk-excel-items {
            width: 100% !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            table-layout: fixed !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 7.5px !important
        }

        .spk-excel-items col.c-code {
            width: 10% !important
        }

        .spk-excel-items col.c-image {
            width: 10% !important
        }

        .spk-excel-items col.c-name {
            width: 16% !important
        }

        .spk-excel-items col.c-p,
        .spk-excel-items col.c-l,
        .spk-excel-items col.c-t {
            width: 4% !important
        }

        .spk-excel-items col.c-material {
            width: 13% !important
        }

        .spk-excel-items col.c-pcs,
        .spk-excel-items col.c-set {
            width: 4% !important
        }

        .spk-excel-items col.c-price {
            width: 10% !important
        }

        .spk-excel-items col.c-total {
            width: 12% !important
        }

        .spk-excel-items col.c-note {
            width: 9% !important
        }

        .spk-excel-items th,
        .spk-excel-items td {
            border: 1px solid #000 !important;
            padding: 2px 3px !important;
            margin: 0 !important;
            overflow: hidden !important;
            line-height: 1.05 !important;
            vertical-align: middle !important
        }

        .spk-excel-items thead th {
            height: 18px !important;
            font-weight: bold !important;
            text-align: center !important;
            white-space: nowrap !important
        }

        .spk-excel-items thead tr:nth-child(2) th {
            height: 15px !important
        }

        .spk-excel-items tbody td {
            text-align: left !important
        }

        .spk-excel-items tbody td.ex-center {
            text-align: center !important
        }

        .spk-excel-items tbody td.ex-right {
            text-align: right !important;
            white-space: nowrap !important
        }

        .spk-excel-item-row td {
            height: 62px !important
        }

        .spk-excel-item-row.extra-row td {
            height: 48px !important
        }

        .excel-code-text,
        .excel-name-text,
        .excel-material-text,
        .excel-note-text {
            width: 100% !important;
            max-height: 58px !important;
            overflow: hidden !important;
            white-space: normal !important;
            word-break: break-word !important
        }

        .excel-name-text {
            font-weight: 500 !important
        }

        .excel-image-cell {
            width: 100% !important;
            height: 58px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important
        }

        .excel-image-cell img {
            max-width: 65px !important;
            max-height: 52px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important
        }

        .excel-number {
            text-align: center !important;
            white-space: nowrap !important
        }

        .excel-money {
            text-align: right !important;
            white-space: nowrap !important
        }

        .spk-excel-spacer td {
            padding: 0 !important;
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
            border-top: 0 !important;
            border-bottom: 1px solid #000 !important
        }

        .ex-grand-total td {
            height: 20px !important;
            font-weight: bold !important;
            vertical-align: middle !important;
            border-top: 1px solid #000 !important
        }

        .ex-grand-total-label {
            text-align: right !important
        }

        .spk-excel-bottom {
            width: 100% !important;
            height: 122px !important;
            display: grid !important;
            grid-template-columns: 57% 43% !important;
            margin-top: 2px !important;
            overflow: hidden !important
        }

        .spk-excel-terms {
            padding: 0 8px 0 0 !important;
            font-size: 7px !important;
            line-height: 1.32 !important;
            overflow: hidden !important
        }

        .spk-excel-terms div {
            margin: 0 !important;
            padding: 0 !important
        }

        .spk-excel-terms .agreement-end {
            margin-top: 7px !important
        }

        .spk-excel-payment {
            padding-left: 2px !important;
            overflow: hidden !important
        }

        .spk-excel-payment table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            font-size: 8px !important
        }

        .spk-excel-payment th,
        .spk-excel-payment td {
            border: 1px solid #000 !important;
            padding: 2px 3px !important;
            height: 18px !important;
            line-height: 1 !important;
            overflow: hidden !important;
            white-space: nowrap !important
        }

        .spk-excel-payment th {
            text-align: center !important
        }

        .spk-excel-payment .ex-pay-amount {
            width: 45% !important
        }

        .spk-excel-payment .ex-pay-date {
            width: 25% !important
        }

        .spk-excel-payment .ex-pay-note {
            width: 30% !important
        }

        .spk-excel-signature {
            width: 100% !important;
            height: 67px !important;
            margin: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important
        }

        .spk-excel-signature td {
            border: 0 !important;
            text-align: center !important;
            vertical-align: top !important;
            padding: 0 !important;
            font-size: 8px !important
        }

        .ex-sign-title {
            height: 14px !important;
            font-weight: bold !important
        }

        .ex-sign-space {
            height: 28px !important
        }

        .ex-sign-name {
            height: 13px !important;
            font-weight: bold !important
        }

        .ex-sign-role {
            height: 12px !important;
            font-size: 7px !important
        }

        .ex-sign-date {
            font-size: 7px !important
        }

        /* =========================================================
       PUSHER REALTIME MOUSE CURSOR
       ========================================================= */
        .spk-search-input:empty::before {
            content: attr(data-placeholder);
            color: #94a3b8;
            pointer-events: none;
        }

        .spk-search-input {
            cursor: text;
        }

        #spkRemoteCursors {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9999999;
        }

        .spk-remote-cursor {
            position: fixed;
            pointer-events: none;
            transform: translate(-1px, -1px);
            transition:
                left 90ms linear,
                top 90ms linear;
            will-change: left, top;
        }

        .spk-remote-cursor-arrow {
            width: 0;
            height: 0;

            border-top: 0 solid transparent;
            border-bottom: 15px solid transparent;
            border-left: 11px solid #2563eb;

            transform: rotate(-42deg);

            filter:
                drop-shadow(0 1px 1px rgba(0, 0, 0, .25));
        }

        .spk-remote-cursor-name {
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

            box-shadow:
                0 2px 5px rgba(0, 0, 0, .18);
        }

        .spk-remote-cursor.is-idle {
            opacity: .45;
        }

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

            background: rgba(255, 255, 255, .97);

            border: 1px solid #dce3ec;

            border-radius: 12px;

            box-shadow:
                0 14px 35px rgba(15, 23, 42, .14),
                0 3px 8px rgba(15, 23, 42, .06);

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
                0 20px 45px rgba(15, 23, 42, .20),
                0 5px 12px rgba(15, 23, 42, .08);

        }

        .spk-floating-actions.is-dragging .spk-floating-header {

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

                right: 0px;
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

        /* =========================================================
       SUPPLIER SEARCH LOADING
       ========================================================= */

        .supplier-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 12px 10px;

            color: #64748b;
            font-size: 11px;
            background: #fff;
        }

        .supplier-loading-spinner {
            width: 15px;
            height: 15px;

            border: 2px solid #e2e8f0;
            border-top-color: #22c55e;

            border-radius: 50%;

            animation: supplierSpin .7s linear infinite;
        }

        @keyframes supplierSpin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .supplier-loading-text {
            line-height: 1;
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
            white-space: nowrap !important;
            position: relative !important;
            padding: 3px 4px !important;
        }

        .spk-table .ppn-cell.ppn-hidden,
        .spk-table thead .ppn-header.ppn-hidden {
            display: none !important;
        }

        .spk-table .ppn-cell.ppn-active {
            background: #eff6ff !important;
            color: #2563eb !important;
        }

        .ppn-value {
            display: inline-block;
            line-height: 18px;
            vertical-align: middle;
            font-weight: 800;
            font-size: 10px;
        }

        .ppn-remove {
            width: 17px !important;
            height: 17px !important;
            min-width: 17px !important;
            padding: 0 !important;
            margin-left: 3px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            vertical-align: middle !important;
            border: 0 !important;
            border-radius: 50% !important;
            background: #ef4444 !important;
            color: #ffffff !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .14) !important;
            transition: transform .12s ease, background .12s ease, box-shadow .12s ease !important;
        }

        .ppn-remove:hover {
            background: #dc2626 !important;
            transform: scale(1.08);
            box-shadow: 0 2px 5px rgba(15, 23, 42, .18) !important;
        }

        .ppn-remove:active {
            transform: scale(.94);
        }

        .spk-table .ppn-cell.ppn-excluded {
            background: #fff !important;
            color: #94a3b8 !important;
        }

        .spk-table .ppn-cell.ppn-excluded::after {
            content: '—';
            color: #cbd5e1;
            font-weight: 600;
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


        /* =========================================================
           PUSHER LIVE MESSAGE / COLLABORATION
           Ctrl + Shift + Z
           ========================================================= */
        .spk-live-message-modal {
            position: fixed;
            inset: 0;
            z-index: 1000000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .20);
            backdrop-filter: blur(2px);
        }

        .spk-live-message-modal.show {
            display: flex;
        }

        .spk-live-message-card {
            width: min(520px, calc(100vw - 40px));
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .20);
            overflow: hidden;
            animation: spkLiveMessageIn .16s ease-out;
        }

        .spk-live-message-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #edf1f5;
        }

        .spk-live-message-title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .spk-live-message-icon {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef6ff;
            color: #2563eb;
            font-size: 14px;
        }

        .spk-live-message-title strong {
            display: block;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.2;
        }

        .spk-live-message-title small {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 9px;
        }

        .spk-live-message-close {
            width: 27px;
            height: 27px;
            border: 0;
            border-radius: 7px;
            background: #f8fafc;
            color: #64748b;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }

        .spk-live-message-close:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .spk-live-message-body {
            padding: 13px 14px 14px;
        }

        .spk-live-message-textarea {
            width: 100%;
            min-height: 115px;
            resize: vertical;
            box-sizing: border-box;
            padding: 11px 12px;
            border: 1px solid #dbe2ea;
            border-radius: 9px;
            outline: none;
            color: #1e293b;
            background: #fff;
            font: inherit;
            font-size: 12px;
            line-height: 1.5;
        }

        .spk-live-message-textarea:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .08);
        }

        .spk-live-message-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 9px;
        }

        .spk-live-message-hint {
            color: #94a3b8;
            font-size: 9px;
        }

        .spk-live-message-send {
            border: 0;
            border-radius: 7px;
            padding: 7px 13px;
            background: #2563eb;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .spk-live-message-send:hover {
            background: #1d4ed8;
        }

        .spk-live-message-live {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 999999;
            width: min(390px, calc(100vw - 40px));
            display: none;
            background: #fff;
            border: 1px solid #dfe6ee;
            border-radius: 11px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .17);
            overflow: hidden;
            animation: spkLiveMessageIn .16s ease-out;
        }

        .spk-live-message-live.show {
            display: block;
        }

        .spk-live-message-live-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 11px;
            border-bottom: 1px solid #edf1f5;
        }

        .spk-live-message-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .12);
        }

        .spk-live-message-live-name {
            color: #334155;
            font-size: 10px;
            font-weight: 800;
        }

        .spk-live-message-live-label {
            margin-left: auto;
            color: #94a3b8;
            font-size: 8px;
        }

        .spk-live-message-live-body {
            padding: 10px 11px 12px;
            color: #475569;
            font-size: 11px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 180px;
            overflow-y: auto;
        }

        @keyframes spkLiveMessageIn {
            from {
                opacity: 0;
                transform: translateY(7px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    @php
        $checkedTypes = $spk['checked_types'] ?? [];
        $status = $spk['status'] ?? 'draft';
    @endphp
    <input type="hidden" id="view_only" value="{{ $viewOnly ? 1 : 0 }}">
    <input type="hidden" id="spk_mode" value="{{ $spk['mode'] }}">
    <input type="hidden" id="spk_id" value="{{ $spk['id'] }}">

    <div id="spkRemoteCursors"></div>

    <!-- =========================================================
         PUSHER LIVE MESSAGE MODAL
         Hanya aktif pada SPK yang sedang dibuka.
         ========================================================= -->
    <div id="spkLiveMessageModal" class="spk-live-message-modal" aria-hidden="true">
        <div class="spk-live-message-card" role="dialog" aria-modal="true" aria-labelledby="spkLiveMessageTitle">
            <div class="spk-live-message-head">
                <div class="spk-live-message-title">
                    <div class="spk-live-message-icon">💬</div>
                    <div>
                        <strong id="spkLiveMessageTitle">Pesan realtime</strong>
                        <small>Hanya terlihat oleh user yang sedang membuka SPK ini</small>
                    </div>
                </div>
                <button type="button" id="spkLiveMessageClose" class="spk-live-message-close" title="Tutup">×</button>
            </div>
            <div class="spk-live-message-body">
                <textarea id="spkLiveMessageInput" class="spk-live-message-textarea" placeholder="Ketik pesan..." maxlength="1000"
                    autocomplete="off" spellcheck="true"></textarea>
                <div class="spk-live-message-footer">
                    <span class="spk-live-message-hint">Ctrl + Shift + Z untuk membuka • Esc untuk menutup</span>
                    <button type="button" id="spkLiveMessageSend" class="spk-live-message-send">Kirim</button>
                </div>
            </div>
        </div>
    </div>

    <div id="spkLiveMessageLive" class="spk-live-message-live" aria-live="polite">
        <div class="spk-live-message-live-head">
            <span class="spk-live-message-dot"></span>
            <span id="spkLiveMessageLiveName" class="spk-live-message-live-name">User</span>
            <span class="spk-live-message-live-label">mengetik realtime</span>
        </div>
        <div id="spkLiveMessageLiveBody" class="spk-live-message-live-body"></div>
    </div>

    <div class="box">
        <!-- TOP TOOLBAR -->
    @section('btn')
        <header class="box-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <h3>SPK PRODUKSI</h3>
                @if ($spk['mode'] === 'edit')
                    <span class="mode-badge edit">EDIT MODE</span>
                @else
                    <span class="mode-badge create">CREATE MODE</span>
                @endif
            </div>

            <div class="toolbar-actions">
                <select name="spk_type" id="spk_type" class="form-control-sm">
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($jenisSuppliers as $jenis)
                        <option value="{{ strtolower($jenis->name) }}"
                            {{ strtolower($spk['type'] ?? '') == strtolower($jenis->name) ? 'selected' : '' }}>
                            {{ $jenis->name }}
                        </option>
                    @endforeach
                </select>

                <button type="button" class="btn-tool" id="btnRiwayatSpk">
                    🕘 Riwayat SPK
                </button>
                <button type="button" class="btn-tool" id="previewBtn">
                    Preview
                </button>
                <button type="button" class="btn-tool"
                    onclick="window.location.href='{{ route('spk.export', $spk['id']) }}'">⬇ Download Excel</button>
                <button type="button" class="btn-tool" id="screenshotSpkBtn">📸 Screenshot</button>
                <button type="button" class="btn-tool" id="copyJpegBtn">📋 Salin</button>
            </div>
        </header>
    @endsection

    <!-- MAIN WORKSPACE -->
    <div class="box-body spk-wrapper" id="printArea">
        <div class="spk-main-card mt-4">
            <!-- LOGO & SEARCH AREA -->
            {{-- <div class="spk-card-head">
                <div class="spk-logo-area">
                    <img src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}" alt="NewWicker"
                        onerror="this.outerHTML='<div style=\'font-family:Georgia,serif; font-size:28px; color:#858b95; font-weight:bold;\'>NewWicker</div>'">
                </div>

            </div> --}}
            <div class="spk-search-area">
                <div class="spk-searchbox">
                    <label>Ketik article / nama item</label>
                    <div class="editable spk-search-input" id="itemSearch" contenteditable="true"
                        data-placeholder="Ketik article / nama item"></div>
                    <div id="itemSuggest" class="suggest-box"></div>
                </div>
            </div>
            <!-- META INFO GRID (NO SPK, NO PO, ACTIONS, NAMA SUPPLIER) -->
            <div class="spk-meta-grid mt-2">
                <div class="spk-meta-col">
                    <span class="spk-meta-label">NO SPK</span>
                    <div class="editable spk-meta-value no-spk" contenteditable="true">{{ $spk['no_spk'] }}</div>
                </div>
                <div class="spk-meta-col">
                    <span class="spk-meta-label">NO PO</span>
                    <div class="editable spk-meta-value no-po" contenteditable="true">{{ $spk['no_po'] }}</div>
                </div>
                <div class="spk-meta-col">
                    <span class="spk-meta-label">ACTION</span>
                    <div class="spk-meta-actions">
                        <button id="btnSaveSpk" class="btn-spk-save">
                            💾 Save SPK
                        </button>
                        @if ($status != 'finished')
                            <button class="btn-spk-status btn-status-spk" data-status="closed">
                                🔒 Close SPK
                            </button>
                            <button id="btnCleanUnchecked" class="btn-spk-clean" style="display:none">
                                🗑 Bersihkan
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="spk-meta-grid" style="grid-template-columns: 1fr;">
                <div class="spk-meta-col" style="position:relative;">
                    <span class="spk-meta-label">NAMA SUPPLIER</span>
                    <div contenteditable="true" class="editable spk-meta-value" id="supplierInput">
                        {{ $spk['nama'] }}
                    </div>
                    <div id="supplierSuggest" class="suggest-box"></div>
                </div>
            </div>

            @php
                $tglTerimaRaw = trim((string) ($spk['tgl_terima'] ?? ''));
                $tglSelesaiRaw = trim((string) ($spk['tgl_selesai'] ?? ''));

                $tglTerimaValue = '';
                $tglSelesaiValue = '';

                if ($tglTerimaRaw !== '' && $tglTerimaRaw !== '-') {
                    try {
                        $tglTerimaValue = Carbon\Carbon::parse($tglTerimaRaw)->format('Y-m-d');
                    } catch (Throwable $e) {
                        $tglTerimaValue = '';
                    }
                }

                if ($tglSelesaiRaw !== '' && $tglSelesaiRaw !== '-') {
                    try {
                        $tglSelesaiValue = Carbon\Carbon::parse($tglSelesaiRaw)->format('Y-m-d');
                    } catch (Throwable $e) {
                        $tglSelesaiValue = '';
                    }
                }
            @endphp

            <!-- DATES GRID -->
            <div class="spk-dates-grid">
                <div class="spk-date-col">
                    <span class="spk-meta-label">TGL TERIMA</span>
                    <div class="editable tgl-terima">
                        <div class="spk-date-wrap">
                            <input type="text" class="spk-date-display"
                                value="{{ $tglTerimaValue ? date('d/m/y', strtotime($tglTerimaValue)) : '' }}"
                                placeholder="dd/mm/yy" inputmode="numeric" autocomplete="off" readonly
                                onclick="openSpkDatePicker(this)">
                            <input type="date" class="spk-date-picker" value="{{ $tglTerimaValue }}"
                                tabindex="-1" aria-hidden="true">
                        </div>
                    </div>
                </div>
                <div class="spk-date-col">
                    <span class="spk-meta-label">TGL SELESAI</span>
                    <div class="editable tgl-selesai">
                        <div class="spk-date-wrap">
                            <input type="text" class="spk-date-display"
                                value="{{ $tglSelesaiValue ? date('d/m/y', strtotime($tglSelesaiValue)) : '' }}"
                                placeholder="dd/mm/yy" inputmode="numeric" autocomplete="off" readonly
                                onclick="openSpkDatePicker(this)">
                            <input type="date" class="spk-date-picker" value="{{ $tglSelesaiValue }}"
                                tabindex="-1" aria-hidden="true">
                        </div>
                    </div>
                </div>
            </div>

            <!-- PPN CONTROL -->
            <div class="spk-ppn-actions">
                <button type="button" id="btnAddPpn" class="btn-add-ppn" data-rate="11">
                    ＋ Add PPN 11%
                </button>
                <span id="ppnStatus" style="font-size:9px;color:var(--spk-muted);">PPN belum diterapkan</span>
            </div>

            <!-- ITEMS SECTION -->
            <div class="spk-section-bar">
                <span><b>ITEM SPK</b></span>
                <span id="spkItemCount">{{ count($spk['items'] ?? []) }} item</span>
            </div>


            <div class="spk-table-scroll">
                <table class="table table-bordered spk-table" id="itemsTable"
                    data-ppn-enabled="{{ !empty($spk['ppn_enabled']) ? '1' : '0' }}"
                    data-ppn-rate="{{ $spk['ppn_rate'] ?? 11 }}">
                    <thead>
                        <tr>
                            <th class="c select-item-cell">
                                <input type="checkbox" id="checkAllItems">
                            </th>
                            <th class="a">Article</th>
                            <th class="im">Gambar</th>
                            <th class="nm">
                                Nama Barang
                                <button type="button" class="btn-add-header-plus" id="btnAddHeader"
                                    title="Tambah Kolom Dinamis">+</button>
                            </th>
                            @foreach ($spk['custom_headers'] ?? [] as $header)
                                <th class="spk-dynamic-header" data-custom="{{ $header['key'] }}">
                                    {{ $header['label'] }}
                                </th>
                            @endforeach
                            <th class="p-header" style="display:none"></th>
                            <th class="num">P</th>
                            <th class="num">L</th>
                            <th class="num">T</th>
                            <th class="mat">Material</th>
                            <th class="num pcs qty-unit-header">
                                <span id="qtyUnitLabel">PCS</span>
                                <button type="button" id="btnEditQtyUnit" class="btn-edit-qty-unit"
                                    title="Ubah satuan quantity">✎</button>
                            </th>
                            <th class="num set">SET</th>
                            <th class="pr harga">Harga</th>
                            <th class="ppn-header ppn-hidden">PPN</th>
                            <th class="tot total">Total</th>
                            <th class="nt">Catatan</th>
                            <th class="act">#</th>
                        </tr>
                    </thead>
                    <tbody id="spkItemsBody">
                        @foreach ($spk['items'] as $item)
                            @php
                                $extraRowCount = count(array_slice($item['custom_columns'] ?? [], 1));
                                $itemRowspan = $extraRowCount + 1;
                            @endphp
                            @php
                                /*
                                 * QTY FIX:
                                 * Gunakan qty asli jika controller sudah mengirimkannya.
                                 * Fallback tetap ke pcs/set agar fungsi lama tidak berubah.
                                 */
                                $itemUnit = strtolower(trim((string) ($item['satuan'] ?? 'pcs')));
                                $itemQty = array_key_exists('qty', $item)
                                    ? $item['qty']
                                    : ($itemUnit === 'set'
                                        ? $item['set'] ?? 0
                                        : $item['pcs'] ?? 0);

                                if ($itemQty === null || $itemQty === '') {
                                    $itemQty = 0;
                                }
                            @endphp
                            <tr class="spk-rowa" data-detail-id="{{ $item['detail_id'] }}"
                                data-satuan="{{ $itemUnit }}" data-qty="{{ $itemQty }}"
                                data-ppn-enabled="{{ !empty($spk['ppn_enabled']) ? '1' : '0' }}">
                                <td class="text-center select-item-cell">
                                    <input type="checkbox" class="spk-item-check">
                                </td>
                                <!-- KODE -->
                                <td rowspan="{{ $itemRowspan }}" class="editable text-center kode-item delete-row"
                                    contenteditable="true">
                                    {{ $item['kode'] }}
                                </td>
                                <!-- GAMBAR -->
                                <td rowspan="{{ $itemRowspan }}" class="gambar-cell">
                                    <div class="image-box gambar-cell" contenteditable="true"
                                        onpaste="handlePaste(event,this)">
                                        @foreach ($item['images'] as $img)
                                            <img src="{{ $img }}" class="preview-img">
                                        @endforeach
                                    </div>
                                    <input type="file" accept="image/*" multiple capture="environment"
                                        onchange="uploadPreview(this)" style="display:none"
                                        id="file_{{ $item['detail_id'] }}">
                                    <label for="file_{{ $item['detail_id'] }}"
                                        style="font-size:8px; cursor:pointer; color:var(--spk-blue); display:block; margin-top:2px;">📷
                                        Upload</label>
                                </td>
                                <!-- NAMA -->
                                <td rowspan="{{ $itemRowspan }}" class="editable nama" contenteditable="true">
                                    {{ $item['nama'] }}
                                </td>
                                <!-- CUSTOM COLUMNS -->
                                @php
                                    $mainCustom = $item['custom_columns'][0] ?? [];
                                @endphp
                                @foreach ($spk['custom_headers'] ?? [] as $header)
                                    <td class="editable custom-column" contenteditable="true"
                                        data-custom="{{ $header['key'] }}">
                                        {{ $mainCustom[$header['key']] ?? '' }}
                                    </td>
                                @endforeach
                                <!-- P, L, T -->
                                <td class="editable text-center p" contenteditable="true">{{ $item['p'] }}</td>
                                <td class="editable text-center l" contenteditable="true">{{ $item['l'] }}</td>
                                <td class="editable text-center t" contenteditable="true">{{ $item['t'] }}</td>
                                <!-- MATERIAL -->
                                <td class="editable material" contenteditable="true">{{ $item['material'] }}</td>
                                <!-- PCS / KG / UNIT UTAMA & SET -->
                                <td class="editable text-center pcs" contenteditable="true">
                                    @if ($itemUnit !== 'set')
                                        {{ is_numeric($itemQty) ? rtrim(rtrim(number_format((float) $itemQty, 2, ',', '.'), '0'), ',') : $itemQty }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="editable text-center set" contenteditable="true">
                                    @if ($itemUnit === 'set')
                                        {{ is_numeric($itemQty) ? rtrim(rtrim(number_format((float) $itemQty, 2, ',', '.'), '0'), ',') : $itemQty }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <!-- HARGA & TOTAL -->
                                <td class="editable text-right harga" contenteditable="true">{{ $item['harga'] }}
                                </td>
                                <td class="ppn-cell ppn-hidden" data-ppn-rate="{{ $spk['ppn_rate'] ?? 11 }}"
                                    data-ppn-enabled="{{ !empty($spk['ppn_enabled']) ? '1' : '0' }}">
                                    <span class="ppn-value">{{ $spk['ppn_rate'] ?? 11 }}%</span>
                                </td>
                                <td class="text-right total">0</td>
                                <!-- CATATAN -->
                                <td>
                                    <div class="editable note-box" contenteditable="true"
                                        onpaste="handlePaste(event,this)">
                                        {!! $item['catatan']['remark'] ?? '' !!}
                                    </div>
                                </td>
                                <!-- ACTION -->
                                <td class="text-center action-cell">
                                    <button type="button" class="btn-add-extra" title="Tambah Sub Baris">➕</button>
                                </td>
                            </tr>
                            <!-- EXTRA SUB-ROWS -->
                            @foreach (array_slice($item['custom_columns'] ?? [], 1) as $extra)
                                <tr class="extra-row"
                                    data-ppn-enabled="{{ !empty($spk['ppn_enabled']) ? '1' : '0' }}">
                                    <td class="hallo"></td>
                                    @foreach ($spk['custom_headers'] ?? [] as $header)
                                        <td class="editable custom-column" contenteditable="true"
                                            data-custom="{{ $header['key'] }}">
                                            {{ $extra[$header['key']] ?? '' }}
                                        </td>
                                    @endforeach
                                    <td class="editable text-center p" contenteditable="true">
                                        {{ $extra['p'] ?? '-' }}</td>
                                    <td class="editable text-center l" contenteditable="true">
                                        {{ $extra['l'] ?? '-' }}</td>
                                    <td class="editable text-center t" contenteditable="true">
                                        {{ $extra['t'] ?? '-' }}</td>
                                    <td class="editable material" contenteditable="true">
                                        {{ $extra['material'] ?? '-' }}</td>
                                    <td class="editable text-center pcs" contenteditable="true">
                                        {{ $extra['pcs'] ?? 0 }}</td>
                                    <td class="editable text-center set" contenteditable="true">
                                        {{ $extra['set'] ?? 0 }}</td>
                                    <td class="editable text-right harga" contenteditable="true">
                                        {{ $extra['harga'] ?? 0 }}</td>
                                    <td class="ppn-cell ppn-hidden" data-ppn-rate="{{ $spk['ppn_rate'] ?? 11 }}"
                                        data-ppn-enabled="{{ !empty($spk['ppn_enabled']) ? '1' : '0' }}">
                                        <span class="ppn-value">{{ $spk['ppn_rate'] ?? 11 }}%</span>
                                    </td>
                                    <td class="text-right total">{{ number_format($extra['total'] ?? 0) }}</td>
                                    <td class="editable note-box" contenteditable="true">
                                        {{ $extra['catatan'] ?? '' }}</td>
                                    <td class="text-center action-cell">
                                        <button type="button" class="btn-delete-extra"
                                            title="Hapus Sub Baris">❌</button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        <tr id="spkItemAnchor"></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- LOWER 2-COLUMN SECTION: LIST BAHAN BAKU & PAYMENT REQUEST (Like preview.html) -->
        <div class="spk-lower-grid">
            <!-- LEFT: BAHAN BAKU WAREHOUSE -->
            <section class="spk-sub-card">
                <div class="spk-sub-title-green">
                    LIST BAHAN BAKU PENGAMBILAN (by warehouse)
                </div>
                <div style="overflow-x:auto;">
                    <table class="spk-simple-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Bahan</th>
                                <th>Potong Bahan</th>
                                <th>Harga</th>
                                <th>Total</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse (($bahanBaku ?? collect()) as $bahan)
                                @php
                                    $tanggalBahan = $bahan->tanggal ?? null;

                                    $hargaInv = (float) ($bahan->stok->harga ?? 0);
                                    $qtyBahan = (float) ($bahan->qty ?? 0);

                                    /*
                                     * Jika harga_vivi memiliki nilai:
                                     * gunakan harga_vivi.
                                     *
                                     * Jika null / kosong:
                                     * gunakan harga inventory.
                                     */
                                    $hargaViviRaw = $bahan->harga_vivi ?? null;

                                    $harga =
                                        $hargaViviRaw !== null && trim((string) $hargaViviRaw) !== ''
                                            ? (float) $hargaViviRaw
                                            : $hargaInv;

                                    $totalBahan = $qtyBahan * $harga;
                                @endphp

                                <tr class="bahan-baku-row">
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="text-center">
                                        @if ($tanggalBahan)
                                            {{ \Carbon\Carbon::parse($tanggalBahan)->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span
                                            style="
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    padding:2px 7px;
                    border-radius:999px;
                    font-size:9px;
                    font-weight:700;
                    text-transform:uppercase;
                    background:{{ strtolower($bahan->tipe ?? '') === 'out' ? '#fff1f2' : '#ecfdf5' }};
                    color:{{ strtolower($bahan->tipe ?? '') === 'out' ? '#be123c' : '#047857' }};
                ">
                                            {{ $bahan->tipe ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div style="font-weight:600;">
                                            {{ $bahan->stok->nama_barang ?? '-' }}
                                        </div>

                                        @if (!empty($bahan->stok->kode_barang))
                                            <small style="color:#94a3b8;">
                                                {{ $bahan->stok->kode_barang }}
                                            </small>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        {{ rtrim(rtrim(number_format($qtyBahan, 2, ',', '.'), '0'), ',') }}

                                        @if (!empty($bahan->stok->satuan))
                                            <span style="color:#64748b;">
                                                {{ $bahan->stok->satuan }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($harga, 0, ',', '.') }}
                                    </td>

                                    <td class="text-right" style="font-weight:700;">
                                        Rp {{ number_format($totalBahan, 0, ',', '.') }}
                                    </td>

                                  <td
    class="bahan-keterangan-edit"
    data-transaksi-id="{{ $bahan->id }}"
    data-spk-id="{{ $bahan->spk_id }}"
    title="Double click untuk edit"
>
    <span class="keterangan-text">
        {{ $bahan->keterangan ?: '-' }}
    </span>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty-text">
                                        Belum ada pengambilan bahan baku dari warehouse.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- RIGHT: PAYMENT REQUEST -->
            <section class="spk-sub-card">
                <div class="spk-sub-title-navy">
                    <span><b>PAYMENT REQUEST</b></span>
                    <button type="button" id="btnAddPayment" class="btn-add-pay">➕ Add Row</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="spk-simple-table">
                        <thead>
                            <tr>
                                <th width="36">Req</th>
                                <th width="105">Amount</th>
                                <th width="85">Date</th>
                                <th width="90">Note</th>
                                <th>Keterangan</th>
                                <th width="115">Adj Finance</th>
                            </tr>
                        </thead>
                        <tbody id="paymentBody">
                            @php
                                $payments = $spk['payments'] ?? [];
                            @endphp
                            @if (count($payments))
                                @foreach ($payments as $pay)
                                    @php
                                        $paymentId = $pay['payment_id'] ?? 'pay_' . uniqid();
                                    @endphp
                                    <tr class="payment-row" data-adjustment="{{ $pay['adjustment'] ?? 0 }}"
                                        data-payment-id="{{ $paymentId }}"
                                        data-pr-id="{{ $pay['pr_id'] ?? '' }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="payment-request-check"
                                                {{ $pay['is_request'] ?? false ? 'checked' : '' }}>
                                        </td>
                                        <td class="editable total-amount" contenteditable="true">
                                            @php
                                                $amount = $pay['amount'] ?? '';
                                                if ($amount !== '' && $amount !== null) {
                                                    $amount = preg_replace('/[^\d-]/', '', (string) $amount);
                                                    $amount = (int) $amount;
                                                }
                                            @endphp
                                            {{ $amount !== '' ? number_format($amount, 0, ',', '.') : '' }}
                                        </td>
                                        <td class="editable date-isian" contenteditable="true">
                                            {{ $pay['date'] ?? '' }}
                                        </td>
                                        <td>
                                            <select class="payment-type">
                                                <option value="">-- Pilih --</option>
                                                <option value="dp"
                                                    {{ ($pay['note'] ?? '') == 'dp' ? 'selected' : '' }}>DP
                                                </option>
                                                <option value="pelunasan"
                                                    {{ ($pay['note'] ?? '') == 'pelunasan' ? 'selected' : '' }}>
                                                    Pelunasan</option>
                                                <option value="bahan"
                                                    {{ ($pay['note'] ?? '') == 'bahan' ? 'selected' : '' }}>
                                                    Bahan
                                                </option>
                                                <option value="return_bahan"
                                                    {{ ($pay['note'] ?? '') == 'return_bahan' ? 'selected' : '' }}>
                                                    Return Bahan</option>
                                                <option value="kasbon"
                                                    {{ ($pay['note'] ?? '') == 'kasbon' ? 'selected' : '' }}>
                                                    Kasbon
                                                </option>
                                                <option value="ppn"
                                                    {{ ($pay['note'] ?? '') == 'ppn' ? 'selected' : '' }}>PPN
                                                </option>
                                            </select>
                                        </td>
                                        <td class="editable note-tambahan" contenteditable="true">
                                            {{ $pay['note_tambahan'] ?? '' }}
                                        </td>
                                        <td>
                                            @if (($pay['adjustment'] ?? 0) > 0)
                                                <div style="color:#16a34a; font-weight:bold; font-size:9px;">
                                                    Bayar: Rp {{ number_format($pay['adjustment'], 0, ',', '.') }}
                                                </div>
                                                <div style="color:#dc2626; font-size:9px;">
                                                    Sisa: Rp
                                                    {{ number_format(($pay['amount'] ?? 0) - ($pay['adjustment'] ?? 0), 0, ',', '.') }}
                                                </div>
                                            @elseif ($pay['is_request'] ?? false)
                                                <span style="color:#16a34a; font-weight:600;">Full Payment</span>
                                            @else
                                                <span style="color:#94a3b8;">Belum Request</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="payment-row" data-payment-id="pay_{{ uniqid() }}"
                                    data-adjustment="0">
                                    <td class="text-center">
                                        <input type="checkbox" class="payment-request-check">
                                    </td>
                                    <td class="editable total-amount" contenteditable="true"></td>
                                    <td class="editable date-isian" contenteditable="true"></td>
                                    <td>
                                        <select class="payment-type">
                                            <option value="">-- Pilih --</option>
                                            <option value="dp">DP</option>
                                            <option value="pelunasan">Pelunasan</option>
                                            <option value="bahan">Bahan</option>
                                            <option value="return_bahan">Return Bahan</option>
                                            <option value="kasbon">Kasbon</option>
                                            <option value="ppn">PPN</option>
                                        </select>
                                    </td>
                                    <td class="editable note-tambahan" contenteditable="true"></td>
                                    <td>-</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div id="paymentSummary"></div>
            </section>
        </div>

        <!-- APPROVAL SIGNATURE CARD (IF EXISTS) -->
        @if (isset($spk['signature']) && $spk['signature'])
            @php $sign = $spk['signature']; @endphp
            <div class="spk-signature-card">
                <div class="spk-signature-header">
                    Approval SPK
                </div>
                <div class="spk-signature-body">
                    <table class="spk-simple-table" style="text-align:center;">
                        <thead>
                            <tr>
                                <th>Made By</th>
                                <th>Checked By</th>
                                <th>Approved By</th>
                                <th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="height:90px;">
                                <td>
                                    @if ($sign->made_at)
                                        <img src="{{ asset('assets/signature/' . $sign->made_by . '.png') }}"
                                            style="max-height:70px;">
                                    @else
                                        <span style="color:#94a3b8;">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex; justify-content:space-around; align-items:center;">
                                        <div style="flex:1;">
                                            @if ($sign->checked_at)
                                                <img src="{{ asset('assets/signature/' . $sign->checked_by . '.png') }}"
                                                    style="max-height:65px;">
                                            @else
                                                <button class="btn btn-warning btn-sm btn-sign"
                                                    data-id="{{ $sign->id }}" data-type="checked">TAP TO
                                                    SIGN</button>
                                            @endif
                                        </div>
                                        <div style="flex:1;">
                                            @if ($sign->checked_at_2)
                                                <img src="{{ asset('assets/signature/' . $sign->checked_by_2 . '.png') }}"
                                                    style="max-height:65px;">
                                            @else
                                                <button class="btn btn-warning btn-sm btn-sign"
                                                    data-id="{{ $sign->id }}" data-type="checked_2">TAP TO
                                                    SIGN</button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($sign->approved_at)
                                        <img src="{{ asset('assets/signature/' . $sign->approved_by . '.png') }}"
                                            style="max-height:70px;">
                                    @else
                                        <button class="btn btn-success btn-sm btn-sign" data-id="{{ $sign->id }}"
                                            data-type="approved">TAP TO SIGN</button>
                                    @endif
                                </td>
                                <td>
                                    <b>{{ $sign->supplier->name ?? '-' }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>{{ $sign->madeBy->name ?? '-' }}</b><br>
                                    <small>{{ strtoupper($sign->madeBy?->karyawan?->divisi?->nama ?? '-') }}</small><br>
                                    <small
                                        style="color:#64748b;">{{ $sign->made_at ? $sign->made_at->format('d/m/Y H:i') : 'Pending' }}</small>
                                </td>
                                <td>
                                    <div style="display:flex; justify-content:space-around;">
                                        <div>
                                            <b>{{ $sign->checkedBy->name ?? '-' }}</b><br>
                                            <small>{{ strtoupper($sign->checkedBy?->karyawan?->divisi?->nama ?? '-') }}</small><br>
                                            <small
                                                style="color:#64748b;">{{ $sign->checked_at ? $sign->checked_at->format('d/m/Y H:i') : 'Pending' }}</small>
                                        </div>
                                        <div>
                                            <b>{{ $sign->checkedBy2->name ?? '-' }}</b><br>
                                            <small>{{ strtoupper($sign->checkedBy2?->karyawan?->divisi?->nama ?? '-') }}</small><br>
                                            <small
                                                style="color:#64748b;">{{ $sign->checked_at_2 ? $sign->checked_at_2->format('d/m/Y H:i') : 'Pending' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <b>{{ $sign->approvedBy->name ?? '-' }}</b><br>
                                    <small>{{ strtoupper($sign->approvedBy?->karyawan?->divisi?->nama ?? '-') }}</small><br>
                                    <small
                                        style="color:#64748b;">{{ $sign->approved_at
                                            ? $sign->approved_at->format('d/m/Y
                                                                            H:i')
                                            : 'Pending' }}</small>
                                </td>
                                <td>
                                    {{ $sign->supplier->name ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- MODAL RIWAYAT -->
<div class="modal fade" id="modalRiwayatSpk" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header bg-dark text-white" style="padding:10px 16px;">
                <h5 class="modal-title" style="font-size:14px; margin:0;">Riwayat SPK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="timelineContainer"
                style="background:#ece5dd; min-height:450px; padding:16px; overflow-y:auto;"></div>
        </div>
    </div>
</div>
<!-- =========================================================
     FLOATING ACTION PANEL
     ========================================================= -->
<div id="spkFloatingActions" class="spk-floating-actions">

    <div class="spk-floating-header" id="spkFloatingDrag">

        <div class="spk-floating-title">
            <i class="fa fa-bolt"></i>
            <span>Action</span>
        </div>

        <span class="spk-drag-hint">
            <i class="fa fa-grip-lines"></i>
        </span>

    </div>


    <div class="spk-floating-body">

        <!-- Jump Signature -->
        <button type="button" class="spk-floating-btn spk-btn-signature" id="btnJumpSignature">

            <span class="spk-btn-icon">
                <i class="fa fa-angle-double-down"></i>
            </span>

            <span>
                <strong>Signature</strong>
                <small>Jump to signature</small>
            </span>

        </button>





    </div>

</div>
<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500
    });
    /* =========================================
           ADD DYNAMIC HEADER BUTTON
           ========================================= */
           /* =========================================================
   EDIT KETERANGAN BAHAN BAKU
   DOUBLE CLICK -> EDIT
   ENTER -> SAVE
   ========================================================= */

$(document).on('dblclick', '.bahan-keterangan-edit', function () {

    const td = this;

    // WAJIB ambil dari data-transaksi-id
    const transaksiId = td.dataset.transaksiId;

    console.log('=== EDIT KETERANGAN ===');
    console.log('Transaksi Stok ID:', transaksiId);
    console.log('SPK ID:', td.dataset.spkId);

    if (!transaksiId) {
        Swal.fire({
            icon: 'error',
            title: 'ID transaksi tidak ditemukan',
            text: 'Data TransaksiStok pada baris ini tidak memiliki ID.'
        });

        return;
    }

    // Jangan membuat editor kedua kali
    if (td.querySelector('.bahan-keterangan-input')) {
        return;
    }

    const oldValue =
        td.querySelector('.keterangan-text')?.textContent.trim() || '';

    td.innerHTML = '';

    const input = document.createElement('input');

    input.type = 'text';
    input.className = 'form-control form-control-sm bahan-keterangan-input';
    input.value = oldValue === '-' ? '' : oldValue;

    td.appendChild(input);

    input.focus();
    input.select();

    input.addEventListener('keydown', function (e) {

        // ENTER = SAVE
        if (e.key === 'Enter') {

            e.preventDefault();

            const value = input.value.trim();

            saveBahanKeterangan(
                td,
                transaksiId,
                value,
                oldValue
            );
        }

        // ESC = CANCEL
        if (e.key === 'Escape') {

            e.preventDefault();

            td.innerHTML = `
                <span class="keterangan-text">
                    ${escapeHtml(oldValue || '-')}
                </span>
            `;
        }
    });
});
function saveBahanKeterangan(td, transaksiId, value, oldValue) {

    console.log('SAVE TRANSAKSI STOK ID:', transaksiId);
    console.log('KETERANGAN:', value);

    // Loading circular
    td.innerHTML = `
        <div style="
            display:flex;
            align-items:center;
            gap:8px;
            min-height:32px;
        ">
            <span class="bahan-keterangan-spinner"></span>

            <span style="
                color:#6c757d;
                font-size:12px;
            ">
                Menyimpan...
            </span>
        </div>
    `;

    fetch("{{ route('spk.bahan-baku.keterangan') }}", {

        method: "POST",

        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),

            "Accept": "application/json"
        },

        body: JSON.stringify({

            // INI ID TRANSAKSI_STOK
            id: transaksiId,

            keterangan: value
        })
    })

    .then(response => {

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        return response.json();
    })

    .then(result => {

        console.log('RESPONSE SAVE:', result);

        td.innerHTML = `
            <span class="keterangan-text">
                ${escapeHtml(result.keterangan || '-')}
            </span>
        `;

        td.style.backgroundColor = '#d1fae5';

        setTimeout(() => {
            td.style.backgroundColor = '';
        }, 800);
    })

    .catch(error => {

        console.error('SAVE KETERANGAN ERROR:', error);

        td.innerHTML = `
            <span class="keterangan-text">
                ${escapeHtml(oldValue || '-')}
            </span>
        `;

        Swal.fire({
            icon: 'error',
            title: 'Gagal menyimpan',
            text: 'Keterangan gagal disimpan.'
        });
    });
}
function escapeHtml(value) {

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
    document.getElementById('btnAddHeader')?.addEventListener('click', function(e) {
        e.stopPropagation();
        const label = prompt('Nama Kolom Header Baru:');
        if (!label) return;
        const key = label.toLowerCase().replace(/\s+/g, '_');
        addDynamicHeader({
            key,
            label
        });
    });

    function addDynamicHeader(header) {
        const th = document.createElement('th');
        th.classList.add('spk-dynamic-header');
        th.dataset.custom = header.key;
        th.innerText = header.label;
        document.querySelector('#itemsTable thead tr .num').before(th);

        document.querySelectorAll('.spk-rowa').forEach(parentRow => {
            const td = document.createElement('td');
            td.classList.add('editable', 'custom-column');
            td.contentEditable = true;
            td.dataset.custom = header.key;
            parentRow.querySelector('.p').before(td);

            let next = parentRow.nextElementSibling;
            while (next && next.classList.contains('extra-row')) {
                const extraTd = document.createElement('td');
                extraTd.classList.add('editable', 'custom-column');
                extraTd.contentEditable = true;
                extraTd.dataset.custom = header.key;
                next.querySelector('.p').before(extraTd);
                next = next.nextElementSibling;
            }
        });
    }

    /* =========================================
       CHECKBOX & CLEAN UNCHECKED ITEMS
       ========================================= */
    document.getElementById('checkAllItems')?.addEventListener('change', function(e) {
        document.querySelectorAll('.spk-item-check').forEach(cb => {
            cb.checked = e.target.checked;
        });
        toggleCleanButton();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('spk-item-check')) {
            toggleCleanButton();
        }
    });

    function toggleCleanButton() {
        const checked = document.querySelectorAll('.spk-item-check:checked').length;
        const btn = document.getElementById('btnCleanUnchecked');
        if (btn) {
            btn.style.display = checked ? 'inline-flex' : 'none';
        }
    }

    document.getElementById('btnCleanUnchecked')?.addEventListener('click', function() {
        document.querySelectorAll('.spk-rowa').forEach(function(row) {
            const cb = row.querySelector('.spk-item-check');
            if (cb && !cb.checked) {
                let next = row.nextElementSibling;
                while (next && next.classList.contains('extra-row')) {
                    const toRemove = next;
                    next = next.nextElementSibling;
                    toRemove.remove();
                }
                row.remove();
            }
        });
        hitungGrandTotal();
        updateItemCount();
    });

    function updateItemCount() {
        const count = document.querySelectorAll('.spk-rowa').length;
        const badge = document.getElementById('spkItemCount');
        if (badge) badge.innerText = count + ' item';
    }

    /* =========================================
       ADD & DELETE EXTRA SUB ROWS
       ========================================= */
    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn-add-extra')) return;

        const parentRow = e.target.closest('.spk-rowa');
        const tr = document.createElement('tr');
        tr.classList.add('extra-row');

        let html = `<td class="hallo"></td>`;

        document.querySelectorAll('.spk-dynamic-header').forEach(th => {
            html +=
                `<td class="editable custom-column" contenteditable="true" data-custom="${th.dataset.custom}"></td>`;
        });

        html += `
                <td class="editable text-center p" contenteditable="true"></td>
                <td class="editable text-center l" contenteditable="true"></td>
                <td class="editable text-center t" contenteditable="true"></td>
                <td class="editable material" contenteditable="true"></td>
                <td class="editable text-center pcs" contenteditable="true">0</td>
                <td class="editable text-center set" contenteditable="true">0</td>
                <td class="editable text-right harga" contenteditable="true">0</td>
                <td class="ppn-cell ppn-hidden"
                    data-ppn-rate="11"
                    data-ppn-enabled="0">
                    <span class="ppn-value">11%</span>
                </td>
                <td class="text-right total">0</td>
                <td class="editable note-box" contenteditable="true"></td>
                <td class="text-center action-cell">
                    <button type="button" class="btn-delete-extra" title="Hapus">❌</button>
                </td>
            `;

        tr.innerHTML = html;

        /*
         * Extra row mengikuti state PPN parent row.
         * Jika parent sedang memakai PPN, row baru langsung
         * ikut PPN. Jika parent dikecualikan, row baru juga
         * mengikuti kondisi tersebut.
         */
        tr.dataset.ppnEnabled =
            parentRow?.dataset.ppnEnabled ??
            (
                document.getElementById('itemsTable')
                ?.dataset.ppnEnabled === '1' ?
                '1' :
                '0'
            );

        const newPpnCell =
            tr.querySelector('.ppn-cell');

        if (newPpnCell) {
            newPpnCell.dataset.ppnEnabled =
                tr.dataset.ppnEnabled;

            newPpnCell.dataset.ppnRate =
                document.getElementById('itemsTable')
                ?.dataset.ppnRate || '11';
        }

        let lastRow = parentRow;
        let next = parentRow.nextElementSibling;
        while (next && next.classList.contains('extra-row')) {
            lastRow = next;
            next = next.nextElementSibling;
        }

        lastRow.after(tr);
        updateRowspan(parentRow);

        /*
         * Row baru langsung mengikuti PPN parent.
         */
        if (typeof hitungTotal === 'function') {
            hitungTotal(tr);
        }

        if (typeof window.refreshPpnState === 'function') {
            window.refreshPpnState();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete-extra')) {
            const row = e.target.closest('.extra-row');
            let prev = row.previousElementSibling;
            while (prev && !prev.classList.contains('spk-rowa')) {
                prev = prev.previousElementSibling;
            }
            row.remove();
            if (prev) {
                updateRowspan(prev);
            }
            hitungGrandTotal();
        }
    });

    function updateRowspan(parentRow) {
        let rowspan = 1;
        let next = parentRow.nextElementSibling;
        while (next && next.classList.contains('extra-row')) {
            rowspan++;
            next = next.nextElementSibling;
        }
        const rowspanCells = ['.kode-item', '.gambar-cell', '.nama'];
        rowspanCells.forEach(selector => {
            const cell = parentRow.querySelector(selector);
            if (cell) {
                cell.rowSpan = rowspan;
                cell.style.verticalAlign = 'middle';
            }
        });
    }

    /* =========================================
       NOTE & IMAGE EXTRACTION
       ========================================= */
    function extractNoteData(noteBox) {
        let remark = '';
        let images = [];
        noteBox.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                remark += node.textContent.trim();
            }
            if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'IMG') {
                images.push(node.src);
            }
        });
        return {
            remark,
            images
        };
    }

    /* =========================================
       ROW DELETION WITH SWEETALERT
       ========================================= */
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-row')) {
            const row = e.target.closest('.spk-rowa');
            if (!row) return;

            Swal.fire({
                title: 'Hapus Item?',
                text: 'Baris item ini beserta sub-barisnya akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let next = row.nextElementSibling;
                    while (next && next.classList.contains('extra-row')) {
                        const toDel = next;
                        next = next.nextElementSibling;
                        toDel.remove();
                    }
                    row.remove();
                    hitungGrandTotal();
                    renderAllPpnRows();
                    updateItemCount();
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });


    /* =========================================
       CALCULATIONS & FORMATTING
       ========================================= */
    /*
     * getNumber() SENGAJA TIDAK DIUBAH.
     * Fungsi ini dipakai banyak logic existing (harga, payment, dll).
     * Untuk quantity KG/decimal kita gunakan parser khusus di bawah agar
     * perubahan KG tidak merusak fungsi lama.
     */
    function getQuantityNumber(el) {
        if (!el) return 0;

        let value = (el.textContent || '')
            .toString()
            .trim()
            .replace(/\s/g, '')
            .replace(/[^0-9,.-]/g, '');

        if (!value) return 0;

        // Format Indonesia: 1.234,56 -> 1234.56
        if (value.includes('.') && value.includes(',')) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else if (value.includes(',')) {
            // 56,5 / 56,50 = decimal
            const parts = value.split(',');
            if (parts.length === 2 && parts[1].length <= 2) {
                value = parts[0] + '.' + parts[1];
            } else {
                value = value.replace(/,/g, '');
            }
        } else if (value.includes('.')) {
            // Satu titik + 1/2 digit di belakang = decimal.
            // Banyak titik = pemisah ribuan.
            const parts = value.split('.');
            if (parts.length > 2) {
                value = value.replace(/\./g, '');
            }
        }

        const number = Number(value);
        return Number.isFinite(number) ? number : 0;
    }

    function getQtyUnit() {
        return (document.getElementById('qtyUnit')?.value || 'PCS')
            .toString()
            .trim()
            .toUpperCase() || 'PCS';
    }

    function getSatuan(row) {
        const unit = getQtyUnit();

        if (unit === 'SET') return 'set';
        if (unit === 'KG') return 'kg';
        if (unit) return unit.toLowerCase();

        // fallback persis behavior lama
        const pcs = parseFloat(row?.querySelector('.pcs')?.innerText) || 0;
        const set = parseFloat(row?.querySelector('.set')?.innerText) || 0;
        if (pcs > 0) return 'pcs';
        if (set > 0) return 'set';
        return '';
    }

    function getNumber(el) {
        if (!el) return 0;
        const value = el.textContent.replace(/\./g, '').replace(/,/g, '').trim();
        return Number(value) || 0;
    }

    function format(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function parseNumber(value) {
        return parseInt((value || '').toString().replace(/\./g, '')) || 0;
    }

    function formatRupiah(value) {
        value = parseInt(value.toString().replace(/[^\d]/g, '')) || 0;
        return new Intl.NumberFormat('id-ID').format(value);
    }

    function hitungTotal(row) {
        if (!row) return;

        const unit = getQtyUnit();
        const pcs = getQuantityNumber(row.querySelector('.pcs'));
        const set = getQuantityNumber(row.querySelector('.set'));
        const hargaCell = row.querySelector('.harga');

        if (!hargaCell) return;

        // PCS dan KG menggunakan kolom quantity utama (.pcs).
        // SET tetap menggunakan kolom .set seperti behavior existing.
        const qty = unit === 'SET' ? set : pcs;

        // Harga dasar PER PCS/SET tetap menjadi harga yang disimpan.
        let baseHarga = Number(row.dataset.baseHarga || 0);

        if (!baseHarga) {
            baseHarga = getNumber(hargaCell);
            row.dataset.baseHarga = String(Math.round(baseHarga));
        }

        const baseTotal = qty * baseHarga;

        row.dataset.baseTotal = String(Math.round(baseTotal));

        const table = document.getElementById('itemsTable');
        const globalPpnEnabled = table?.dataset.ppnEnabled === '1';
        const ppnRate = parseFloat(table?.dataset.ppnRate || '11') || 11;

        /*
         * PPN sekarang mempunyai 2 level:
         *
         * 1. Global:
         *    table.dataset.ppnEnabled
         *
         * 2. Per-row:
         *    row.dataset.ppnEnabled
         *
         * Jadi Add PPN dapat mengaktifkan semua row,
         * tetapi tombol X pada row dapat mematikan PPN
         * hanya untuk row tersebut.
         */
        let rowPpnEnabled =
            row.dataset.ppnEnabled === '1';

        /*
         * Jika state row belum pernah dibuat:
         * ikuti state global saat ini.
         */
        if (
            row.dataset.ppnEnabled === undefined ||
            row.dataset.ppnEnabled === ''
        ) {
            rowPpnEnabled = globalPpnEnabled;
            row.dataset.ppnEnabled =
                globalPpnEnabled ? '1' : '0';
        }

        const ppnEnabled =
            globalPpnEnabled && rowPpnEnabled;

        const ppnAmount = ppnEnabled ?
            Math.round(baseTotal * (ppnRate / 100)) :
            0;

        const finalTotal =
            baseTotal + ppnAmount;

        const ppnCell =
            row.querySelector('.ppn-cell');

        if (ppnCell) {

            ppnCell.dataset.ppnAmount =
                String(ppnAmount);

            ppnCell.dataset.ppnEnabled =
                ppnEnabled ? '1' : '0';

            ppnCell.dataset.ppnRate =
                String(ppnRate);

            if (ppnEnabled) {

                ppnCell.classList.remove(
                    'ppn-hidden',
                    'ppn-excluded'
                );

                ppnCell.classList.add(
                    'ppn-active'
                );

                ppnCell.innerHTML = `
                    <span class="ppn-value">
                        ${ppnRate}%
                    </span>
                    <button
                        type="button"
                        class="ppn-remove"
                        title="Hapus PPN dari item ini"
                        aria-label="Hapus PPN dari item ini"
                    >×</button>
                `;

            } else {

                ppnCell.classList.add(
                    'ppn-hidden'
                );

                ppnCell.classList.remove(
                    'ppn-active',
                    'ppn-excluded'
                );

                ppnCell.innerHTML = '';

            }
        }

        const totalCell =
            row.querySelector('.total');

        if (totalCell) {
            totalCell.innerText =
                format(finalTotal);
        }

        // Harga yang tampil tetap harga dasar.
        if (document.activeElement !== hargaCell) {
            hargaCell.innerText =
                format(baseHarga);
        }

        hitungGrandTotal();
    }

    function hitungGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.spk-table .total').forEach(td => {
            grandTotal += getNumber(td);
        });
        updatePaymentSummary();
    }

    /* =========================================================
       EXCEL STYLE NAVIGATION
       Arrow Left / Right / Up / Down
       Enter / Shift + Enter
       ========================================================= */

    (function() {

        function editableCellsInRow(row) {
            return Array.from(
                row.querySelectorAll('td.editable[contenteditable="true"]')
            ).filter(el => !el.classList.contains('image-box'));
        }

        function allEditableRows() {
            return Array.from(
                document.querySelectorAll('#spkItemsBody tr')
            );
        }

        function placeCaret(cell, position = 'end') {

            if (!cell) return;

            cell.focus();

            const selection = window.getSelection();
            const range = document.createRange();

            range.selectNodeContents(cell);

            if (position === 'start') {
                range.collapse(true);
            } else {
                range.collapse(false);
            }

            selection.removeAllRanges();
            selection.addRange(range);
        }


        document.addEventListener('keydown', function(e) {

            const cell = e.target.closest(
                '#spkItemsBody td.editable[contenteditable="true"]'
            );

            if (!cell) return;

            /*
            |--------------------------------------------------------------------------
            | Jangan navigasi kalau sedang memilih text.
            |--------------------------------------------------------------------------
            */
            const selection = window.getSelection();

            const hasTextSelection =
                selection &&
                selection.rangeCount &&
                !selection.isCollapsed;

            /*
            |--------------------------------------------------------------------------
            | ENTER
            |--------------------------------------------------------------------------
            */

            if (e.key === 'Enter') {

                e.preventDefault();

                const rows = allEditableRows();
                const row = cell.closest('tr');

                const rowIndex = rows.indexOf(row);

                if (rowIndex === -1) return;

                const currentCells = editableCellsInRow(row);
                const colIndex = currentCells.indexOf(cell);

                if (colIndex === -1) return;

                const nextIndex = e.shiftKey ?
                    rowIndex - 1 :
                    rowIndex + 1;

                if (
                    nextIndex >= 0 &&
                    nextIndex < rows.length
                ) {

                    const targetCells =
                        editableCellsInRow(rows[nextIndex]);

                    const target =
                        targetCells[colIndex];

                    if (target) {
                        placeCaret(target, 'end');
                    }
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ARROW LEFT
            |--------------------------------------------------------------------------
            */

            if (e.key === 'ArrowLeft') {

                /*
                 * Kalau sedang blok text di dalam cell,
                 * biarkan browser menggeser selection.
                 */
                if (hasTextSelection) return;

                const selection = window.getSelection();

                if (
                    selection &&
                    selection.rangeCount
                ) {

                    const range = selection.getRangeAt(0);

                    /*
                     * Kalau cursor masih di tengah text,
                     * biarkan bergerak normal.
                     */
                    if (range.startOffset > 0) {
                        return;
                    }
                }

                e.preventDefault();

                const row = cell.closest('tr');
                const cells = editableCellsInRow(row);
                const index = cells.indexOf(cell);

                if (index > 0) {

                    const target = cells[index - 1];

                    placeCaret(target, 'end');
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ARROW RIGHT
            |--------------------------------------------------------------------------
            */

            if (e.key === 'ArrowRight') {

                if (hasTextSelection) return;

                const selection = window.getSelection();

                if (
                    selection &&
                    selection.rangeCount
                ) {

                    const range = selection.getRangeAt(0);

                    const node = range.startContainer;

                    let length = 0;

                    if (node.nodeType === Node.TEXT_NODE) {
                        length = node.textContent.length;
                    } else {
                        length = node.textContent?.length || 0;
                    }

                    /*
                     * Kalau cursor belum sampai akhir text,
                     * biarkan browser bergerak normal.
                     */
                    if (range.startOffset < length) {
                        return;
                    }
                }

                e.preventDefault();

                const row = cell.closest('tr');
                const cells = editableCellsInRow(row);
                const index = cells.indexOf(cell);

                if (
                    index >= 0 &&
                    index < cells.length - 1
                ) {

                    const target = cells[index + 1];

                    placeCaret(target, 'start');
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ARROW UP
            |--------------------------------------------------------------------------
            */

            if (e.key === 'ArrowUp') {

                if (hasTextSelection) return;

                e.preventDefault();

                const rows = allEditableRows();
                const row = cell.closest('tr');

                const rowIndex = rows.indexOf(row);

                if (rowIndex <= 0) return;

                const currentCells = editableCellsInRow(row);
                const colIndex = currentCells.indexOf(cell);

                if (colIndex === -1) return;


                /*
                 * Cari row sebelumnya yang memiliki
                 * kolom editable tersebut.
                 */
                for (
                    let i = rowIndex - 1; i >= 0; i--
                ) {

                    const targetCells =
                        editableCellsInRow(rows[i]);

                    const target =
                        targetCells[colIndex];

                    if (target) {

                        placeCaret(target, 'end');

                        return;
                    }
                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ARROW DOWN
            |--------------------------------------------------------------------------
            */

            if (e.key === 'ArrowDown') {

                if (hasTextSelection) return;

                e.preventDefault();

                const rows = allEditableRows();
                const row = cell.closest('tr');

                const rowIndex = rows.indexOf(row);

                if (rowIndex === -1) return;

                const currentCells = editableCellsInRow(row);
                const colIndex = currentCells.indexOf(cell);

                if (colIndex === -1) return;


                for (
                    let i = rowIndex + 1; i < rows.length; i++
                ) {

                    const targetCells =
                        editableCellsInRow(rows[i]);

                    const target =
                        targetCells[colIndex];

                    if (target) {

                        placeCaret(target, 'end');

                        return;
                    }
                }

                return;
            }

        });

    })();
    // grabb
    /* =========================================================
       FLOATING SPK ACTION
       ========================================================= */
    (function() {

        const panel =
            document.getElementById('spkFloatingActions');

        const handle =
            document.getElementById('spkFloatingDrag');

        if (!panel || !handle) return;


        /* =====================================================
           DRAG PANEL
           ===================================================== */

        let dragging = false;

        let offsetX = 0;
        let offsetY = 0;


        handle.addEventListener('mousedown', function(e) {

            dragging = true;

            const rect =
                panel.getBoundingClientRect();

            offsetX =
                e.clientX - rect.left;

            offsetY =
                e.clientY - rect.top;

            panel.classList.add('is-dragging');

            /*
             * Hilangkan right/bottom agar
             * posisi bisa dikontrol dengan left/top.
             */

            panel.style.left =
                rect.left + 'px';

            panel.style.top =
                rect.top + 'px';

            panel.style.right =
                'auto';

            panel.style.bottom =
                'auto';

            e.preventDefault();

        });


        document.addEventListener('mousemove', function(e) {

            if (!dragging) return;

            let x =
                e.clientX - offsetX;

            let y =
                e.clientY - offsetY;


            /*
             * Jangan sampai keluar layar.
             */

            const maxX =
                window.innerWidth -
                panel.offsetWidth;

            const maxY =
                window.innerHeight -
                panel.offsetHeight;


            x =
                Math.max(
                    0,
                    Math.min(x, maxX)
                );

            y =
                Math.max(
                    0,
                    Math.min(y, maxY)
                );


            panel.style.left =
                x + 'px';

            panel.style.top =
                y + 'px';

        });


        document.addEventListener('mouseup', function() {

            if (!dragging) return;

            dragging = false;

            panel.classList.remove(
                'is-dragging'
            );

        });


        /* =====================================================
           TOUCH DRAG
           ===================================================== */

        let touchOffsetX = 0;
        let touchOffsetY = 0;


        handle.addEventListener(
            'touchstart',
            function(e) {

                const touch =
                    e.touches[0];

                const rect =
                    panel.getBoundingClientRect();

                touchOffsetX =
                    touch.clientX - rect.left;

                touchOffsetY =
                    touch.clientY - rect.top;

                panel.style.left =
                    rect.left + 'px';

                panel.style.top =
                    rect.top + 'px';

                panel.style.right =
                    'auto';

                panel.style.bottom =
                    'auto';

                panel.classList.add(
                    'is-dragging'
                );

            }, {
                passive: true
            }
        );


        handle.addEventListener(
            'touchmove',
            function(e) {

                const touch =
                    e.touches[0];

                let x =
                    touch.clientX -
                    touchOffsetX;

                let y =
                    touch.clientY -
                    touchOffsetY;


                const maxX =
                    window.innerWidth -
                    panel.offsetWidth;

                const maxY =
                    window.innerHeight -
                    panel.offsetHeight;


                x =
                    Math.max(
                        0,
                        Math.min(x, maxX)
                    );

                y =
                    Math.max(
                        0,
                        Math.min(y, maxY)
                    );


                panel.style.left =
                    x + 'px';

                panel.style.top =
                    y + 'px';

            }, {
                passive: true
            }
        );


        handle.addEventListener(
            'touchend',
            function() {

                panel.classList.remove(
                    'is-dragging'
                );

            }
        );


        /* =====================================================
           JUMP TO SIGNATURE
           ===================================================== */

        document
            .getElementById('btnJumpSignature')
            ?.addEventListener(
                'click',
                function() {

                    /*
                     * Cari beberapa kemungkinan
                     * ID section signature.
                     */

                    /*
                     * Target sebenarnya pada Blade ini adalah
                     * .spk-signature-card.
                     */
                    const target =
                        document.querySelector('.spk-signature-card') ||
                        document.getElementById('signatureSection') ||
                        document.getElementById('approvalSection') ||
                        document.getElementById('signature') ||
                        document.querySelector('.signature-section') ||
                        document.querySelector('.approval-section');


                    if (!target) {

                        console.warn(
                            'Signature section tidak ditemukan.'
                        );

                        return;

                    }


                    /*
                     * Smooth scroll
                     */

                    /*
                     * Sticky .box-header = header atas.
                     * Hitung posisi target agar Approval SPK
                     * muncul tepat di bawah header.
                     */
                    const header =
                        document.querySelector('.box-header');

                    const headerHeight =
                        header ? header.getBoundingClientRect().height : 0;

                    const targetTop =
                        target.getBoundingClientRect().top +
                        window.pageYOffset -
                        headerHeight -
                        14;

                    window.scrollTo({
                        top: Math.max(0, targetTop),
                        behavior: 'smooth'
                    });

                    /*
                     * Flash singkat agar posisi Signature terlihat.
                     */
                    target.classList.add('spk-jump-highlight');

                    setTimeout(function() {
                        target.classList.remove('spk-jump-highlight');
                    }, 1400);

                }
            );


        /* =====================================================
           SAVE SPK
           ===================================================== */

        document
            .getElementById('floatingSaveSpk')
            ?.addEventListener(
                'click',
                function() {

                    /*
                     * Gunakan tombol Save existing.
                     * Tidak membuat proses save baru.
                     */

                    const originalSave =
                        document.getElementById(
                            'btnSaveSpk'
                        );

                    if (originalSave) {

                        originalSave.click();

                    }

                }
            );


        /* =====================================================
           CLOSE SPK
           ===================================================== */

        document
            .getElementById('floatingCloseSpk')
            ?.addEventListener(
                'click',
                function() {

                    /*
                     * Gunakan tombol Close existing
                     * jika tersedia.
                     */

                    const originalClose =
                        document.getElementById(
                            'btnCloseSpk'
                        ) ||
                        document.querySelector(
                            '.btn-close-spk'
                        );


                    if (originalClose) {

                        originalClose.click();

                    }

                }
            );

    })();
    /* =========================================================
       QTY UNIT EDITOR
       Tambahan saja. Tidak mengubah handler existing lainnya.
       ========================================================= */
    (function initQtyUnitEditor() {
        const button = document.getElementById('btnEditQtyUnit');
        const label = document.getElementById('qtyUnitLabel');

        if (!button || !label) return;

        // Ambil satuan dari data SPK yang sudah ada.
        // Jika tidak ada, tetap PCS seperti behavior lama.
        let initialUnit = @json(strtoupper(
                (string) (collect($spk['items'] ?? [])->pluck('satuan')->filter()->first() ?? 'PCS')));

        if (!initialUnit) initialUnit = 'PCS';

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.id = 'qtyUnit';
        hidden.value = initialUnit;
        document.body.appendChild(hidden);

        function normalizeUnit(value) {
            return String(value || 'PCS').trim().toUpperCase() || 'PCS';
        }

        function applyUnit(unit) {
            unit = normalizeUnit(unit);

            const oldUnit = normalizeUnit(hidden.value);

            // Pindahkan qty terlebih dahulu.
            document.querySelectorAll(
                '#spkItemsBody tr.spk-rowa, #spkItemsBody tr.extra-row'
            ).forEach(row => {

                const pcsCell = row.querySelector('.pcs');
                const setCell = row.querySelector('.set');

                if (!pcsCell || !setCell) {
                    return;
                }

                // Ambil qty dari satuan yang sedang aktif.
                let currentQty = 0;

                if (oldUnit === 'SET') {
                    currentQty = getQuantityNumber(setCell);
                } else {
                    // PCS dan KG menggunakan kolom utama.
                    currentQty = getQuantityNumber(pcsCell);
                }

                // Pindahkan, bukan copy.
                if (unit === 'SET') {
                    setCell.textContent = currentQty;
                    pcsCell.textContent = '0';
                } else {
                    pcsCell.textContent = currentQty;
                    setCell.textContent = '0';
                }

                row.dataset.qty = String(currentQty);
                row.dataset.satuan = unit.toLowerCase();
            });

            // PENTING: update hidden unit SEBELUM hitungTotal().
            // Kalau tidak, hitungTotal masih membaca satuan lama
            // sehingga saat PCS -> SET qty dibaca dari .pcs yang sudah 0.
            hidden.value = unit;
            label.textContent = unit;

            // Setelah unit berubah, hitung ulang semua total.
            document.querySelectorAll(
                '#spkItemsBody tr.spk-rowa, #spkItemsBody tr.extra-row'
            ).forEach(row => {
                if (typeof hitungTotal === 'function') {
                    hitungTotal(row);
                }
            });
        }

        function closePopover() {
            document.querySelector('.qty-unit-popover')?.remove();
        }

        function openPopover() {
            closePopover();

            const pop = document.createElement('div');
            pop.className = 'qty-unit-popover';

            /*
             * Penting: popover ditempel langsung ke BODY agar tidak ikut
             * terpotong oleh overflow/scroll container tabel.
             */
            document.body.appendChild(pop);

            ['PCS', 'KG', 'SET'].forEach(unit => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'qty-unit-option' +
                    (normalizeUnit(hidden.value) === unit ? ' active' : '');
                option.textContent = unit;

                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    applyUnit(unit);
                    closePopover();
                });

                pop.appendChild(option);
            });

            const custom = document.createElement('button');
            custom.type = 'button';
            custom.className = 'qty-unit-option';
            custom.textContent = '✎ Custom';
            custom.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (pop.querySelector('.qty-unit-custom')) return;

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'qty-unit-custom';
                input.placeholder = 'Contoh: KG / M / LTR';
                input.value = normalizeUnit(hidden.value);
                pop.appendChild(input);
                input.focus();
                input.select();

                const submit = () => {
                    const value = normalizeUnit(input.value);
                    if (!value) return;
                    applyUnit(value);
                    closePopover();
                };

                input.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        submit();
                    }
                    if (ev.key === 'Escape') {
                        ev.preventDefault();
                        closePopover();
                    }
                });
            });

            pop.appendChild(custom);

            /*
             * Gunakan position:fixed + !important secara inline.
             * Ini mencegah CSS parent/table/Bootstrap menggeser popover
             * ke pojok kiri atas.
             */
            pop.style.setProperty('position', 'fixed', 'important');
            pop.style.setProperty('z-index', '2147483647', 'important');
            pop.style.setProperty('display', 'block', 'important');
            pop.style.setProperty('visibility', 'hidden', 'important');
            pop.style.setProperty('left', '0px', 'important');
            pop.style.setProperty('top', '0px', 'important');

            /* Ukur setelah DOM benar-benar terpasang. */
            requestAnimationFrame(function() {
                const rect = button.getBoundingClientRect();
                const popRect = pop.getBoundingClientRect();
                const gap = 6;
                const margin = 8;

                let left = rect.left;
                let top = rect.bottom + gap;

                /* Jangan keluar sisi kanan viewport. */
                if (left + popRect.width > window.innerWidth - margin) {
                    left = window.innerWidth - popRect.width - margin;
                }

                if (left < margin) {
                    left = margin;
                }

                /* Jika bawah tidak cukup, buka ke atas tombol. */
                if (top + popRect.height > window.innerHeight - margin) {
                    top = rect.top - popRect.height - gap;
                }

                /* Safety supaya tidak keluar bagian atas. */
                if (top < margin) {
                    top = margin;
                }

                pop.style.setProperty('left', Math.round(left) + 'px', 'important');
                pop.style.setProperty('top', Math.round(top) + 'px', 'important');
                pop.style.setProperty('visibility', 'visible', 'important');
            });
        }

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openPopover();
        });

        document.addEventListener('click', function(e) {
            const pop = document.querySelector('.qty-unit-popover');
            if (pop && !pop.contains(e.target) && e.target !== button) {
                closePopover();
            }
        });

        applyUnit(initialUnit);
    })();

    function initializePpnBaseTotals() {
        document.querySelectorAll(
            '#spkItemsBody tr.spk-rowa, #spkItemsBody tr.extra-row'
        ).forEach(row => {
            const unit = getQtyUnit();
            const pcs = getQuantityNumber(row.querySelector('.pcs'));
            const set = getQuantityNumber(row.querySelector('.set'));
            const harga = getNumber(row.querySelector('.harga'));
            const qty = unit === 'SET' ? set : pcs;

            row.dataset.baseHarga = String(Math.round(harga));
            row.dataset.baseTotal = String(
                Math.round(qty * harga)
            );

            hitungTotal(row);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePpnBaseTotals);
    } else {
        initializePpnBaseTotals();
    }

    document.addEventListener('input', function(e) {
        if (
            e.target.classList.contains('pcs') ||
            e.target.classList.contains('set') ||
            e.target.classList.contains('harga')
        ) {
            const row = e.target.closest('tr');
            if (!row) return;

            // PENTING:
            // Saat user mengedit harga, ambil harga BARU dari cell
            // sebelum hitungTotal() dijalankan.
            if (e.target.classList.contains('harga')) {
                const hargaBaru = getNumber(e.target);

                row.dataset.baseHarga = String(
                    Math.round(hargaBaru)
                );
            }

            hitungTotal(row);
        }
    });

    document.addEventListener('paste', function(e) {
        if (e.target.closest('.pcs, .set, .harga')) {
            setTimeout(() => {
                const row = e.target.closest('tr');
                if (!row) return;

                const hargaCell = row.querySelector('.harga');

                if (hargaCell) {
                    row.dataset.baseHarga = String(
                        Math.round(getNumber(hargaCell))
                    );
                }

                hitungTotal(row);
            }, 10);
        }
    });

    /* =========================================
       PPN STATE API
       ========================================= */
    window.isPpnEnabled = function() {
        const table = document.getElementById('itemsTable');
        return String(table?.dataset.ppnEnabled || '0') === '1';
    };

    window.getPpnRate = function() {
        const table = document.getElementById('itemsTable');
        const rate = Number(table?.dataset.ppnRate || 11);
        return Number.isFinite(rate) ? rate : 11;
    };

    /* =========================================
               PPN 11% CONTROL
               ========================================= */
    (function() {
        const table = document.getElementById('itemsTable');
        const btn = document.getElementById('btnAddPpn');
        const status = document.getElementById('ppnStatus');
        if (!table || !btn) return;
        table.dataset.ppnEnabled =
            table.dataset.ppnEnabled === '1' ? '1' : '0';
        table.dataset.ppnRate =
            table.dataset.ppnRate || '11';

        window.spkPpnEnabled =
            table.dataset.ppnEnabled === '1';

        window.spkPpnRate =
            Number(table.dataset.ppnRate || 11);

        function updatePpnButtonState() {

            const enabled =
                table.dataset.ppnEnabled === '1';

            const rate =
                parseFloat(
                    table.dataset.ppnRate || '11'
                ) || 11;

            const rows =
                document.querySelectorAll(
                    '#itemsTable .spk-rowa, ' +
                    '#itemsTable .extra-row'
                );

            let activeCount = 0;

            rows.forEach(row => {

                const cell =
                    row.querySelector('.ppn-cell');

                if (
                    enabled &&
                    row.dataset.ppnEnabled === '1'
                ) {
                    activeCount++;
                }

            });

            const totalRows = rows.length;

            btn.classList.toggle(
                'active',
                enabled && activeCount > 0
            );

            if (!enabled || activeCount === 0) {

                btn.innerHTML =
                    '＋ Add PPN ' + rate + '%';

                if (status) {
                    status.innerText =
                        'PPN belum diterapkan';

                    status.style.color =
                        'var(--spk-muted)';
                }

                return;
            }

            if (
                totalRows > 0 &&
                activeCount < totalRows
            ) {

                btn.innerHTML =
                    '✓ PPN Sebagian';

                if (status) {
                    status.innerText =
                        activeCount +
                        ' dari ' +
                        totalRows +
                        ' item menggunakan PPN';

                    status.style.color =
                        '#2563eb';
                }

                return;
            }

            btn.innerHTML =
                '✓ PPN ' + rate + '% Aktif';

            if (status) {
                status.innerText =
                    'Total item dihitung + PPN ' +
                    rate + '%';

                status.style.color =
                    '#2563eb';
            }
        }

        function refreshPpnState(options = {}) {

            const enabled =
                table.dataset.ppnEnabled === '1';

            const rate =
                parseFloat(
                    table.dataset.ppnRate || '11'
                ) || 11;

            /*
             * applyAll = true hanya ketika user menekan
             * tombol Add PPN.
             *
             * Refresh biasa TIDAK menghidupkan kembali
             * row yang sebelumnya sudah dihapus PPN-nya.
             */
            const applyAll =
                options.applyAll === true;

            document
                .querySelectorAll(
                    '#itemsTable .ppn-header'
                )
                .forEach(el => {

                    el.classList.toggle(
                        'ppn-hidden',
                        !enabled
                    );

                });

            document
                .querySelectorAll(
                    '#itemsTable .spk-rowa, ' +
                    '#itemsTable .extra-row'
                )
                .forEach(row => {

                    let cell =
                        row.querySelector('.ppn-cell');

                    if (!cell) return;

                    if (applyAll) {

                        row.dataset.ppnEnabled =
                            enabled ? '1' : '0';

                    } else if (
                        row.dataset.ppnEnabled ===
                        undefined
                    ) {

                        row.dataset.ppnEnabled =
                            enabled ? '1' : '0';
                    }

                    cell.dataset.ppnRate =
                        String(rate);

                    hitungTotal(row);
                });

            window.spkPpnEnabled =
                enabled;

            window.spkPpnRate =
                rate;

            updatePpnButtonState();
        }

        /*
         * GLOBAL ADD / REMOVE PPN
         *
         * Klik:
         * - jika OFF -> semua row ON
         * - jika ON  -> semua row OFF
         *
         * PPN per row tetap dapat dihapus dengan tombol X.
         */
        btn.addEventListener(
            'click',
            function() {

                const nextEnabled =
                    table.dataset.ppnEnabled !== '1';

                table.dataset.ppnEnabled =
                    nextEnabled ? '1' : '0';

                refreshPpnState({
                    applyAll: true
                });
            }
        );

        /*
         * DELETE PPN PER ROW
         */
        document.addEventListener(
            'click',
            function(event) {

                const removeBtn =
                    event.target.closest(
                        '.ppn-remove'
                    );

                if (!removeBtn) return;

                const row =
                    removeBtn.closest('tr');

                if (!row) return;

                const cell =
                    row.querySelector('.ppn-cell');

                if (!cell) return;

                /*
                 * Hanya row ini yang OFF.
                 */
                row.dataset.ppnEnabled = '0';

                cell.dataset.ppnEnabled = '0';
                cell.dataset.ppnAmount = '0';

                cell.classList.add(
                    'ppn-excluded'
                );

                /*
                 * Jangan hapus kolom PPN.
                 * Kolom tetap ada sehingga user dapat
                 * melihat bahwa item ini dikecualikan.
                 */
                cell.classList.remove(
                    'ppn-hidden',
                    'ppn-active'
                );

                cell.innerHTML = `
                    <span
                        style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:600;
                        "
                        title="PPN tidak diterapkan pada item ini"
                    >—</span>
                `;

                /*
                 * Hitung ulang:
                 * total = qty × harga
                 */
                hitungTotal(row);

                /*
                 * hitungTotal() akan mengosongkan cell
                 * karena state row sudah OFF.
                 * Tampilkan kembali tanda —.
                 */
                cell.classList.remove(
                    'ppn-hidden'
                );

                cell.classList.add(
                    'ppn-excluded'
                );

                cell.innerHTML = `
                    <span
                        style="
                            color:#94a3b8;
                            font-size:10px;
                            font-weight:600;
                        "
                        title="PPN tidak diterapkan pada item ini"
                    >—</span>
                `;

                updatePpnButtonState();
            }
        );

        window.isPpnEnabled =
            () => table.dataset.ppnEnabled === '1';

        window.getPpnRate =
            () => parseFloat(
                table.dataset.ppnRate || '11'
            ) || 11;

        window.refreshPpnState =
            refreshPpnState;

        /*
         * Initial state.
         *
         * Kalau SPK tersimpan PPN aktif, semua row
         * awalnya aktif.
         */
        document
            .querySelectorAll(
                '#itemsTable .spk-rowa, ' +
                '#itemsTable .extra-row'
            )
            .forEach(row => {

                if (
                    row.dataset.ppnEnabled ===
                    undefined
                ) {
                    row.dataset.ppnEnabled =
                        table.dataset.ppnEnabled === '1' ?
                        '1' :
                        '0';
                }

            });

        refreshPpnState();
    })();

    /* =========================================
    /* =========================================
   SUPPLIER AUTOCOMPLETE
   ========================================= */

    const supInput =
        document.getElementById('supplierInput');

    const supSuggest =
        document.getElementById('supplierSuggest');

    let supTimer = null;

    let supRequest = null;


    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

    function showSupplierLoading() {

        if (!supSuggest) {
            return;
        }

        supSuggest.innerHTML = `
        <div class="supplier-loading">

            <span class="supplier-loading-spinner"></span>

            <span class="supplier-loading-text">
                Mencari supplier...
            </span>

        </div>
    `;

        supSuggest.style.display = 'block';
    }


    /*
    |--------------------------------------------------------------------------
    | HIDE
    |--------------------------------------------------------------------------
    */

    function hideSupplierSuggest() {

        if (!supSuggest) {
            return;
        }

        supSuggest.innerHTML = '';

        supSuggest.style.display = 'none';
    }


    /*
    |--------------------------------------------------------------------------
    | SUPPLIER INPUT
    |--------------------------------------------------------------------------
    */

    supInput?.addEventListener(
        'input',
        function() {

            const keyword =
                supInput.innerText
                .replace(/\u00a0/g, ' ')
                .trim();


            /*
            |--------------------------------------------------------------------------
            | CANCEL TIMER
            |--------------------------------------------------------------------------
            */

            clearTimeout(
                supTimer
            );


            /*
            |--------------------------------------------------------------------------
            | CANCEL REQUEST SEBELUMNYA
            |--------------------------------------------------------------------------
            */

            if (supRequest) {

                supRequest.abort();

                supRequest = null;
            }


            /*
            |--------------------------------------------------------------------------
            | KURANG DARI 2 KARAKTER
            |--------------------------------------------------------------------------
            */

            if (
                keyword.length < 2
            ) {

                hideSupplierSuggest();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | TAMPILKAN LOADING LANGSUNG
            |--------------------------------------------------------------------------
            */

            showSupplierLoading();


            /*
            |--------------------------------------------------------------------------
            | DEBOUNCE 300ms
            |--------------------------------------------------------------------------
            */

            supTimer =
                setTimeout(
                    async function() {

                            /*
                            |--------------------------------------------------------------------------
                            | ABORT CONTROLLER
                            |--------------------------------------------------------------------------
                            */

                            supRequest =
                                new AbortController();


                            try {

                                /*
                                |--------------------------------------------------------------------------
                                | REQUEST
                                |--------------------------------------------------------------------------
                                */

                                const response =
                                    await fetch(
                                        `/supplier/search?q=${encodeURIComponent(keyword)}`, {
                                            signal: supRequest.signal,

                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',

                                                'Accept': 'application/json'
                                            }
                                        }
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | CEK HTTP
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !response.ok
                                ) {

                                    throw new Error(
                                        `HTTP ${response.status}`
                                    );
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | JSON
                                |--------------------------------------------------------------------------
                                */

                                const data =
                                    await response.json();


                                /*
                                |--------------------------------------------------------------------------
                                | BERSIHKAN LOADING
                                |--------------------------------------------------------------------------
                                */

                                supSuggest.innerHTML =
                                    '';


                                /*
                                |--------------------------------------------------------------------------
                                | TIDAK ADA DATA
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !data ||
                                    data.length === 0
                                ) {

                                    supSuggest.innerHTML = `
                                <div class="supplier-loading">
                                    Supplier tidak ditemukan
                                </div>
                            `;

                                    supSuggest.style.display =
                                        'block';

                                    return;
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | RENDER RESULT
                                |--------------------------------------------------------------------------
                                */

                                data.forEach(
                                    function(item) {

                                        const div =
                                            document.createElement(
                                                'div'
                                            );


                                        div.className =
                                            'suggest-item';


                                        div.textContent =
                                            item.name;


                                        /*
                                        |--------------------------------------------------------------------------
                                        | CLICK SUPPLIER
                                        |--------------------------------------------------------------------------
                                        */

                                        div.onclick =
                                            function() {

                                                supInput.innerText =
                                                    item.name;


                                                /*
                                                |--------------------------------------------------------------------------
                                                | SET JENIS SUPPLIER
                                                |--------------------------------------------------------------------------
                                                */

                                                if (
                                                    item.jenis
                                                ) {

                                                    const type =
                                                        document.getElementById(
                                                            'spk_type'
                                                        );


                                                    if (type) {

                                                        type.value =
                                                            item.jenis;
                                                    }
                                                }


                                                /*
                                                |--------------------------------------------------------------------------
                                                | HIDE RESULT
                                                |--------------------------------------------------------------------------
                                                */

                                                hideSupplierSuggest();


                                                /*
                                                |--------------------------------------------------------------------------
                                                | FOCUS
                                                |--------------------------------------------------------------------------
                                                */

                                                supInput.focus();
                                            };


                                        supSuggest.appendChild(
                                            div
                                        );
                                    }
                                );


                                supSuggest.style.display =
                                    'block';


                            } catch (error) {

                                /*
                                |--------------------------------------------------------------------------
                                | ABORT = ABAIKAN
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    error.name ===
                                    'AbortError'
                                ) {

                                    return;
                                }


                                console.error(
                                    'Supplier search:',
                                    error
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | ERROR MESSAGE
                                |--------------------------------------------------------------------------
                                */

                                supSuggest.innerHTML = `
                            <div class="supplier-loading"
                                 style="color:#dc2626;">

                                Gagal mencari supplier

                            </div>
                        `;


                                supSuggest.style.display =
                                    'block';


                                /*
                                |--------------------------------------------------------------------------
                                | HILANGKAN ERROR SETELAH 2 DETIK
                                |--------------------------------------------------------------------------
                                */

                                setTimeout(
                                    function() {

                                        /*
                                        | Jangan menghilangkan
                                        | hasil request baru.
                                        */
                                        if (
                                            supSuggest &&
                                            supSuggest.innerText
                                            .includes(
                                                'Gagal mencari supplier'
                                            )
                                        ) {

                                            hideSupplierSuggest();
                                        }

                                    },
                                    2000
                                );


                            } finally {

                                supRequest =
                                    null;
                            }

                        },
                        300
                );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLICK DI LUAR SUPPLIER
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function(e) {

            if (
                supInput &&
                !supInput.contains(e.target) &&
                supSuggest &&
                !supSuggest.contains(e.target)
            ) {

                hideSupplierSuggest();
            }
        }
    );
    /* =========================================
      ITEM SEARCH & ADD ROW
      ========================================= */
    (function() {

        const itemInput = document.getElementById('itemSearch');
        const itemSuggest = document.getElementById('itemSuggest');

        if (!itemInput || !itemSuggest) return;

        let itemTimer = null;
        let itemRequest = null;

        /*
        |--------------------------------------------------------------------------
        | AMBIL TEXT SEARCH
        |--------------------------------------------------------------------------
        */
        function getSearchKeyword() {
            return (itemInput.innerText || '')
                .replace(/\u00a0/g, ' ')
                .trim();
        }

        /*
        |--------------------------------------------------------------------------
        | CLEAR SEARCH
        |--------------------------------------------------------------------------
        */
        function clearItemSearch() {
            itemInput.innerHTML = '';
            itemSuggest.innerHTML = '';
            itemSuggest.style.display = 'none';
        }

        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */
        function escapeHtml(value) {
            if (value === null || value === undefined) return '';

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        itemInput.addEventListener('input', function() {

            const keyword = getSearchKeyword();

            clearTimeout(itemTimer);

            /*
             * Kalau kosong
             */
            if (!keyword) {
                itemSuggest.innerHTML = '';
                itemSuggest.style.display = 'none';
                return;
            }

            /*
             * Minimal 2 karakter
             */
            if (keyword.length < 2) {
                itemSuggest.innerHTML = '';
                itemSuggest.style.display = 'none';
                return;
            }

            itemTimer = setTimeout(async function() {

                /*
                 * Batalkan request sebelumnya kalau masih berjalan
                 */
                if (itemRequest) {
                    itemRequest.abort();
                }

                itemRequest = new AbortController();

                try {

                    itemSuggest.innerHTML = `
                    <div class="suggest-item"
                         style="color:#94a3b8;cursor:default;">
                        <i class="fa fa-spinner fa-spin"></i>
                        Mencari "${escapeHtml(keyword)}"...
                    </div>
                `;

                    itemSuggest.style.display = 'block';

                    const url =
                        "{{ route('detailpo.search') }}" +
                        "?q=" +
                        encodeURIComponent(keyword);

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: itemRequest.signal
                    });

                    if (!response.ok) {
                        throw new Error(
                            'HTTP ' + response.status
                        );
                    }

                    const data = await response.json();

                    /*
                     * Pastikan keyword masih sama.
                     * Kalau user sudah mengetik keyword baru,
                     * hasil lama jangan ditampilkan.
                     */
                    if (getSearchKeyword() !== keyword) {
                        return;
                    }

                    itemSuggest.innerHTML = '';

                    /*
                     * Tidak ditemukan
                     */
                    if (!Array.isArray(data) || data.length === 0) {

                        itemSuggest.innerHTML = `
                        <div class="suggest-item"
                             style="color:#94a3b8;cursor:default;">
                            <i class="fa fa-search"></i>
                            Item tidak ditemukan
                        </div>
                    `;

                        itemSuggest.style.display = 'block';

                        return;
                    }

                    /*
                     * Tampilkan hasil
                     */
                    data.forEach(function(item) {

                        const div = document.createElement('div');

                        div.className = 'suggest-item';

                        div.innerHTML = `
                        <div style="
                            display:flex;
                            flex-direction:column;
                            gap:2px;
                        ">
                            <strong style="
                                font-size:11px;
                                color:#172033;
                            ">
                                ${escapeHtml(item.nama || '-')}
                            </strong>

                            <small style="
                                color:#64748b;
                                font-size:9px;
                            ">
                                Article:
                                <b>${escapeHtml(item.kode || '-')}</b>
                            </small>
                        </div>
                    `;

                        /*
                         * PENTING:
                         * Klik hasil -> langsung masuk ke tabel
                         */
                        div.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                        });

                        div.addEventListener('click', function(e) {

                            e.preventDefault();
                            e.stopPropagation();

                            /*
                             * Tambahkan item ke tabel
                             */
                            addItemRow(item);

                            /*
                             * Bersihkan search
                             */
                            clearItemSearch();

                        });

                        itemSuggest.appendChild(div);

                    });

                    itemSuggest.style.display = 'block';

                } catch (error) {

                    /*
                     * Abort bukan error yang perlu ditampilkan
                     */
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error(
                        'ITEM SEARCH ERROR:',
                        error
                    );

                    itemSuggest.innerHTML = `
                    <div class="suggest-item"
                         style="color:#dc2626;cursor:default;">
                        <i class="fa fa-exclamation-triangle"></i>
                        Gagal mencari item
                    </div>
                `;

                    itemSuggest.style.display = 'block';
                }

            }, 250);

        });


        /*
        |--------------------------------------------------------------------------
        | ENTER = PILIH HASIL PERTAMA
        |--------------------------------------------------------------------------
        */
        itemInput.addEventListener('keydown', function(e) {

            if (e.key !== 'Enter') return;

            const firstResult =
                itemSuggest.querySelector('.suggest-item[data-item-index="0"]');

            /*
             * Kalau ada hasil, pilih hasil pertama
             */
            if (firstResult) {

                e.preventDefault();

                firstResult.click();

                return;
            }

            /*
             * Jangan membuat newline di contenteditable
             */
            e.preventDefault();

        });


        /*
        |--------------------------------------------------------------------------
        | UPDATE SUGGESTION INDEX
        |--------------------------------------------------------------------------
        */
        const observer = new MutationObserver(function() {

            const results =
                itemSuggest.querySelectorAll('.suggest-item');

            results.forEach(function(el, index) {
                el.dataset.itemIndex = index;
            });

        });

        observer.observe(itemSuggest, {
            childList: true
        });


        /*
        |--------------------------------------------------------------------------
        | KLIK DI LUAR
        |--------------------------------------------------------------------------
        */
        document.addEventListener('mousedown', function(e) {

            if (
                !itemInput.contains(e.target) &&
                !itemSuggest.contains(e.target)
            ) {
                itemSuggest.style.display = 'none';
            }

        });


        /*
        |--------------------------------------------------------------------------
        | ADD ITEM ROW
        |--------------------------------------------------------------------------
        */
        function addItemRow(item) {

            const tbody =
                document.getElementById('spkItemsBody');

            const anchor =
                document.getElementById('spkItemAnchor');

            if (!tbody || !anchor) {
                console.error(
                    'spkItemsBody / spkItemAnchor tidak ditemukan'
                );
                return;
            }

            const tr =
                document.createElement('tr');

            tr.classList.add('spk-rowa');

            /*
             * Simpan detail ID
             */
            tr.dataset.detailId =
                item.detail_id ||
                item.id ||
                ('new_' + Date.now());


            /*
            |--------------------------------------------------------------------------
            | DYNAMIC COLUMNS
            |--------------------------------------------------------------------------
            */
            let dynamicCols = '';

            document
                .querySelectorAll('.spk-dynamic-header')
                .forEach(function(th) {

                    dynamicCols += `
                    <td
                        class="editable custom-column"
                        contenteditable="true"
                        data-custom="${escapeHtml(th.dataset.custom || '')}">
                    </td>
                `;

                });


            /*
            |--------------------------------------------------------------------------
            | DATA ITEM
            |--------------------------------------------------------------------------
            */
            const kode =
                item.kode ??
                item.article ??
                item.code ??
                '';

            const nama =
                item.nama ??
                item.name ??
                item.nama_barang ??
                '';

            const p =
                item.p ??
                item.panjang ??
                '';

            const l =
                item.l ??
                item.lebar ??
                '';

            const t =
                item.t ??
                item.tinggi ??
                '';

            const material =
                item.material ??
                '';

            const itemSatuan = String(
                item.satuan ??
                item.unit ??
                'pcs'
            ).trim().toLowerCase() || 'pcs';

            const qty =
                item.qty ??
                item.quantity ??
                item.pcs ??
                0;

            const pcsQty = itemSatuan === 'set' ? 0 : qty;
            const setQty = itemSatuan === 'set' ? qty : 0;


            /*
            |--------------------------------------------------------------------------
            | HTML ROW
            |--------------------------------------------------------------------------
            */
            tr.innerHTML = `

            <!-- CHECK -->
            <td class="text-center select-item-cell">
                <input
                    type="checkbox"
                    class="spk-item-check">
            </td>


            <!-- ARTICLE -->
            <td
                class="editable text-center kode-item delete-row"
                contenteditable="true">
                ${escapeHtml(kode)}
            </td>


            <!-- GAMBAR -->
          <td class="gambar-cell">

    <div
        class="image-box gambar-cell"
        contenteditable="true"
        onpaste="handlePaste(event,this)">

        ${(item.images && item.images.length)
                    ? item.images.map(function (img) {
                        return `
                        <img
                            src="${escapeHtml(img)}"
                            class="preview-img"
                            onerror="this.style.display='none'">
                    `;
                    }).join('')
                    : (
                        item.photo
                            ? ` <
                img
            src = "${escapeHtml(item.photo)}"
            class = "preview-img"
            onerror = "this.style.display='none'" >
                `
                            : ''
                    )
                }

    </div>

</td>


            <!-- NAMA -->
            <td
                class="editable nama"
                contenteditable="true">
                ${escapeHtml(nama)}
            </td>


            ${dynamicCols}


            <!-- P -->
            <td
                class="editable text-center p"
                contenteditable="true">
                ${escapeHtml(p)}
            </td>


            <!-- L -->
            <td
                class="editable text-center l"
                contenteditable="true">
                ${escapeHtml(l)}
            </td>


            <!-- T -->
            <td
                class="editable text-center t"
                contenteditable="true">
                ${escapeHtml(t)}
            </td>


            <!-- MATERIAL -->
            <td
                class="editable material"
                contenteditable="true">
                ${escapeHtml(material)}
            </td>


            <!-- PCS -->
            <td
                class="editable text-center pcs"
                contenteditable="true">
                ${escapeHtml(pcsQty)}
            </td>


            <!-- SET -->
            <td
                class="editable text-center set"
                contenteditable="true">
                ${escapeHtml(setQty)}
            </td>


            <!-- HARGA -->
            <td
                class="editable text-right harga"
                contenteditable="true">
                0
            </td>


            <!-- PPN -->
            <td
                class="ppn-cell ppn-hidden"
                data-ppn-rate="11"
                data-ppn-enabled="0">
                <span class="ppn-value">11%</span>
            </td>


            <!-- TOTAL -->
            <td class="text-right total">
                0
            </td>


            <!-- CATATAN -->
            <td class="catatan-cell">

                <div
                    class="editable note-box"
                    contenteditable="true"
                    onpaste="handlePaste(event,this)">
                </div>

            </td>


            <!-- ACTION -->
            <td class="text-center action-cell">

                <button
                    type="button"
                    class="btn-add-extra"
                    title="Tambah Sub Baris">
                    ➕
                </button>

            </td>

        `;


            /*
            |--------------------------------------------------------------------------
            | MASUKKAN SEBELUM ANCHOR
            |--------------------------------------------------------------------------
            */
            anchor.before(tr);


            /*
            |--------------------------------------------------------------------------
            | HITUNG TOTAL
            |--------------------------------------------------------------------------
            */
            if (typeof hitungTotal === 'function') {
                hitungTotal(tr);
            }

            if (typeof hitungGrandTotal === 'function') {
                hitungGrandTotal();
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE COUNT
            |--------------------------------------------------------------------------
            */
            if (typeof updateItemCount === 'function') {
                updateItemCount();
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE PPN
            |--------------------------------------------------------------------------
            */
            if (typeof window.refreshPpnState === 'function') {
                window.refreshPpnState();
            }


            /*
            |--------------------------------------------------------------------------
            | FOCUS NAMA ITEM
            |--------------------------------------------------------------------------
            */
            const namaCell =
                tr.querySelector('.nama');

            if (namaCell) {

                requestAnimationFrame(function() {

                    namaCell.focus();

                    /*
                     * Cursor di akhir nama
                     */
                    const range =
                        document.createRange();

                    range.selectNodeContents(namaCell);
                    range.collapse(false);

                    const selection =
                        window.getSelection();

                    selection.removeAllRanges();
                    selection.addRange(range);

                });

            }


            /*
            |--------------------------------------------------------------------------
            | SCROLL KE ROW BARU
            |--------------------------------------------------------------------------
            */
            requestAnimationFrame(function() {

                tr.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

            });

        }

    })();
    /* =========================================
       IMAGE PASTE & UPLOAD
       ========================================= */
    function handlePaste(e, container) {
        let items = (e.clipboardData || window.clipboardData).items;
        for (let i = 0; i < items.length; i++) {
            let item = items[i];
            if (item.type.indexOf("image") !== -1) {
                e.preventDefault();
                let blob = item.getAsFile();
                let reader = new FileReader();
                reader.onload = function(event) {
                    let img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'preview-img';
                    container.appendChild(img);
                };
                reader.readAsDataURL(blob);
            }
        }
    }

    function uploadPreview(input) {
        let container = input.previousElementSibling;
        Array.from(input.files).forEach(file => {
            let reader = new FileReader();
            reader.onload = e => {
                let img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
        input.value = '';
    }

    /* =========================================
       PAYMENT REQUEST & CALCULATIONS
       ========================================= */
    document.getElementById('btnAddPayment')?.addEventListener('click', function() {
        let tr = document.createElement('tr');
        let paymentId = 'pay_' + Date.now();
        tr.classList.add('payment-row');
        tr.setAttribute('data-payment-id', paymentId);
        tr.innerHTML = `
                <td class="text-center">
                    <input type="checkbox" class="payment-request-check">
                </td>
                <td class="editable total-amount" contenteditable="true"></td>
                <td class="editable date-isian" contenteditable="true"></td>
                <td>
                    <select class="payment-type">
                        <option value="">-- Pilih --</option>
                        <option value="dp">DP</option>
                        <option value="bahan">Bahan</option>
                        <option value="return_bahan">Return Bahan</option>
                        <option value="kasbon">Kasbon</option>
                        <option value="pelunasan">Pelunasan</option>
                        <option value="ppn">PPN</option>
                    </select>
                </td>
                <td class="editable note-tambahan" contenteditable="true"></td>
                <td>-</td>
            `;
        document.getElementById('paymentBody').appendChild(tr);
    });

    function validatePaymentLimit() {
        let grandTotal = 0;
        document.querySelectorAll('.spk-table .total').forEach(td => {
            grandTotal += parseNumber(td.innerText);
        });

        let totalPayment = 0;
        document.querySelectorAll('.payment-row').forEach(row => {
            let originalAmount = parseNumber(row.querySelector('.total-amount')?.innerText);
            let adjustment = parseFloat(row.dataset.adjustment || 0);
            let amount = adjustment > 0 ? adjustment : originalAmount;
            const type = row.querySelector('.payment-type')?.value;

            switch (type) {
                case 'return_bahan':
                    totalPayment -= amount;
                    break;
                case 'dp':
                case 'bahan':
                case 'kasbon':
                case 'pelunasan':
                case 'ppn':
                    totalPayment += amount;
                    break;
            }
        });

        return {
            grandTotal,
            totalPayment,
            valid: totalPayment <= grandTotal
        };
    }
    /* =========================================================
       PAYMENT REQUEST - DIRECT SAVE
       ========================================================= */

    async function savePaymentRequestRow(row) {

        try {

            if (!row) {
                return;
            }

            /* =========================
               AMBIL DATA ROW
               ========================= */

            const checkbox =
                row.querySelector('.payment-request-check');

            const amount =
                (
                    row.querySelector('.total-amount')
                    ?.innerText || ''
                )
                .replace(/\./g, '')
                .trim();

            const date =
                row.querySelector('.date-isian')
                ?.innerText
                .trim() || '';

            const note =
                row.querySelector('.payment-type')
                ?.value || '';

            const noteTambahan =
                row.querySelector('.note-tambahan')
                ?.innerText
                .trim() || '';

            const isRequest =
                checkbox?.checked || false;


            /* =========================
               VALIDASI PAYMENT LIMIT
               ========================= */

            const validation = validatePaymentLimit();

            if (!validation.valid) {

                if (checkbox) {
                    checkbox.checked = false;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Total Payment Melebihi SPK',
                    text: 'Total payment Rp ' +
                        formatRupiah(validation.totalPayment) +
                        ' melebihi total SPK Rp ' +
                        formatRupiah(validation.grandTotal)
                });

                return;
            }


            /* =========================
               VALIDASI JENIS PAYMENT
               ========================= */

            if (!note) {

                if (checkbox) {
                    checkbox.checked = false;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis payment kosong',
                    text: 'Pilih jenis payment terlebih dahulu'
                });

                return;
            }


            /* =========================
               VALIDASI NOMINAL
               ========================= */

            if (!amount || parseInt(amount) <= 0) {

                if (checkbox) {
                    checkbox.checked = false;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal kosong',
                    text: 'Isi nominal payment terlebih dahulu'
                });

                return;
            }


            /* =========================
               FORMAT TANGGAL
               12/05/26 -> 12/05/2026
               ========================= */

            let finalDate = date;

            if (date) {

                const split = date.split('/');

                if (
                    split.length === 3 &&
                    split[2].length === 2
                ) {

                    finalDate =
                        split[0] + '/' +
                        split[1] + '/20' +
                        split[2];
                }
            }


            /* =========================
               PAYLOAD
               ========================= */

            const payload = {

                spk_id: document.getElementById('spk_id')
                    ?.value,

                no_spk: document.querySelector('.no-spk')
                    ?.innerText
                    .trim(),

                payment: {

                    payment_id: row.dataset.paymentId || null,

                    amount: amount,

                    date: finalDate,

                    note: note,

                    note_tambahan: noteTambahan,

                    is_request: isRequest
                }
            };


            console.log(
                'PAYMENT REQUEST PAYLOAD:',
                payload
            );


            /* =========================
               LOADING ROW
               ========================= */

            row.style.opacity = '0.6';
            row.style.pointerEvents = 'none';


            /* =========================
               SAVE KE PAYMENT REQUEST
               ========================= */

            const response = await fetch(
                '/payment-request/store', {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },

                    body: JSON.stringify(payload)
                }
            );


            /* =========================
               RESPONSE
               ========================= */

            let result = {};

            try {

                result =
                    await response.json();

            } catch (e) {

                throw new Error(
                    'Response server tidak valid'
                );
            }


            console.log(
                'PAYMENT REQUEST RESULT:',
                result
            );


            /* =========================
               HTTP ERROR
               ========================= */

            if (!response.ok) {

                throw new Error(
                    result.message ||
                    'Terjadi kesalahan server'
                );
            }


            /* =========================
               APPLICATION ERROR
               ========================= */

            if (!result.success) {

                throw new Error(
                    result.message ||
                    'Gagal membuat payment request'
                );
            }


            /* =========================
               SUCCESS
               ========================= */

            Toast.fire({
                icon: 'success',

                title: isRequest ?
                    'Payment request dibuat' : 'Payment request dibatalkan'
            });


            /* =========================
               SYNC SPK JSON
               ========================= */

            const saveBtn =
                document.getElementById('btnSaveSpk');

            if (saveBtn) {
                saveBtn.click();
            }


        } catch (err) {

            console.error(
                'PAYMENT REQUEST ERROR:',
                err
            );


            /* =========================
               ROLLBACK CHECKBOX
               ========================= */

            const checkbox =
                row.querySelector(
                    '.payment-request-check'
                );

            if (checkbox) {
                checkbox.checked = !checkbox.checked;
            }


            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message ||
                    'Terjadi kesalahan'
            });


        } finally {

            /* =========================
               ENABLE ROW
               ========================= */

            row.style.opacity = '1';
            row.style.pointerEvents = 'auto';

        }
    }
    /* =========================================================
       PAYMENT REQUEST CHECKBOX EVENT
       ========================================================= */

    document.addEventListener('change', function(e) {

        if (
            !e.target.classList.contains(
                'payment-request-check'
            )
        ) {
            return;
        }

        const row =
            e.target.closest('.payment-row');

        if (!row) {
            return;
        }

        savePaymentRequestRow(row);

    });

    function updatePaymentSummary() {
        let grandTotal = 0;
        document.querySelectorAll('.spk-table .total').forEach(td => {
            grandTotal += parseNumber(td.innerText);
        });

        let totalPpn = 0,
            totalDp = 0,
            totalBahan = 0,
            totalReturnBahan = 0,
            totalPelunasan = 0,
            totalKasbon = 0;

        document.querySelectorAll('.payment-row').forEach(row => {
            let originalAmount = parseNumber(row.querySelector('.total-amount')?.innerText);
            let adjustment = parseFloat(row.dataset.adjustment || 0);
            let amount = adjustment > 0 ? adjustment : originalAmount;
            let type = row.querySelector('.payment-type')?.value;

            if (type === 'ppn') totalPpn += amount;
            if (type === 'dp') totalDp += amount;
            if (type === 'bahan') totalBahan += amount;
            if (type === 'return_bahan') totalReturnBahan += amount;
            if (type === 'pelunasan') totalPelunasan += amount;
            if (type === 'kasbon') totalKasbon += amount;
        });

        let bahanBersih = Math.max(0, totalBahan - totalReturnBahan);
        let grandTotalSetelahPpn = grandTotal + totalPpn;
        let sisaPelunasan = grandTotalSetelahPpn - totalDp - bahanBersih - totalKasbon - totalPelunasan;

        let summary = `
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span>Grand Total :</span>
                    <b>Rp ${formatRupiah(grandTotal)}</b>
                </div>
            `;

        if (totalDp > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px;"><span>Total DP:</span> <b>Rp ${formatRupiah(totalDp)}</b></div>`;
        }
        if (totalBahan > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#16a34a;"><span>Total Bahan:</span> <b>Rp ${formatRupiah(totalBahan)}</b></div>`;
        }
        if (totalReturnBahan > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#dc2626;"><span>Return Bahan:</span> <b>Rp ${formatRupiah(totalReturnBahan)}</b></div>`;
        }
        if (totalKasbon > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#dc2626;"><span>Total Kasbon:</span> <b>Rp ${formatRupiah(totalKasbon)}</b></div>`;
        }
        if (totalPelunasan > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px;"><span>Total Pelunasan:</span> <b>Rp ${formatRupiah(totalPelunasan)}</b></div>`;
        }
        if (totalPpn > 0) {
            summary +=
                `<div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#2563eb;"><span>Total PPN:</span> <b>Rp ${formatRupiah(totalPpn)}</b></div>`;
        }

        summary += `
                <div style="margin-top:8px; padding:6px 10px; border-radius:4px; background:#fff0f0; border:1px solid #fecaca; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-weight:700; color:#dc2626; font-size:11px;">⚠ Sisa Pelunasan :</span>
                    <span style="font-weight:700; color:#dc2626; font-size:12px;">Rp ${formatRupiah(sisaPelunasan)}</span>
                </div>
            `;

        const container = document.getElementById('paymentSummary');
        if (container) container.innerHTML = summary;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('payment-type') || e.target.classList.contains(
                'payment-request-check')) {
            updatePaymentSummary();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('total-amount')) {
            updatePaymentSummary();
        }
    });

    /* =========================================
       SAVE SPK PAYLOAD
       ========================================= */
    document.getElementById('btnSaveSpk')?.addEventListener('click', function() {
        let items = [];
        let payments = [];
        const validation = validatePaymentLimit();

        if (!validation.valid) {
            Swal.fire({
                icon: 'error',
                title: 'Total Payment Melebihi SPK',
                text: 'Total payment Rp ' + formatRupiah(validation.totalPayment) +
                    ' melebihi total SPK Rp ' + formatRupiah(validation.grandTotal)
            });
            return;
        }

        document.querySelectorAll('.payment-row').forEach(row => {
            let originalAmount = parseNumber(row.querySelector('.total-amount')?.innerText);
            let adjustment = parseFloat(row.dataset.adjustment || 0);

            payments.push({
                amount: originalAmount,
                adjustment: adjustment,
                payment_request_amount: adjustment,
                remaining_amount: originalAmount - adjustment,
                date: row.querySelector('.date-isian')?.innerText.trim() || '',
                note: row.querySelector('.payment-type')?.value || '',
                note_tambahan: row.querySelector('.note-tambahan')?.innerText.trim() || '',
                payment_id: row.dataset.paymentId || null,
                pr_id: row.dataset.prId || null,
                is_request: row.querySelector('.payment-request-check')?.checked || false
            });
        });

        document.querySelectorAll('.spk-rowa').forEach(row => {
            const detailId = row.dataset.detailId;
            if (!detailId) return;

            let images = [];
            row.querySelectorAll('.image-box img').forEach(img => images.push(img.src));
            const noteBox = row.querySelector('.note-box');

            let customColumns = [];
            let parentCustom = {};
            row.querySelectorAll('.custom-column').forEach(col => {
                const key = col.dataset.custom.trim().toLowerCase();
                parentCustom[key] = col.innerText.trim();
            });
            if (Object.keys(parentCustom).length) {
                customColumns.push(parentCustom);
            }

            let next = row.nextElementSibling;
            while (next && next.classList.contains('extra-row')) {
                let extraData = {};
                next.querySelectorAll('.custom-column').forEach(col => {
                    const key = col.dataset.custom.trim().toLowerCase();
                    extraData[key] = col.innerText.trim();
                });
                extraData.p = next.querySelector('.p')?.innerText.trim() || '';
                extraData.l = next.querySelector('.l')?.innerText.trim() || '';
                extraData.t = next.querySelector('.t')?.innerText.trim() || '';
                extraData.material = next.querySelector('.material')?.innerText.trim() || '';
                extraData.pcs = next.querySelector('.pcs')?.innerText.trim() || '';
                extraData.set = next.querySelector('.set')?.innerText.trim() || '';
                extraData.harga = Number(next.dataset.baseHarga || getNumber(next.querySelector(
                    '.harga')));
                extraData.total = Number(next.dataset.baseTotal || 0);
                extraData.ppn_enabled =
                    next.dataset.ppnEnabled === '1';
                extraData.ppn_rate =
                    Number(
                        document.getElementById('itemsTable')
                        ?.dataset.ppnRate || 11
                    );
                customColumns.push(extraData);
                next = next.nextElementSibling;
            }

            items.push({
                detail_id: detailId,
                kode: row.querySelector('.kode-item')?.innerText.trim() || '',
                nama: row.querySelector('.nama')?.innerText.trim() || '',
                p: row.querySelector('.p')?.innerText.trim() || '',
                l: row.querySelector('.l')?.innerText.trim() || '',
                t: row.querySelector('.t')?.innerText.trim() || '',
                material: row.querySelector('.material')?.innerText.trim() || '',
                pcs: row.querySelector('.pcs')?.innerText.trim() || '',
                set: row.querySelector('.set')?.innerText.trim() || '',
                satuan: getSatuan(row),
                harga: Number(row.dataset.baseHarga || getNumber(row.querySelector('.harga'))),
                total: Number(row.dataset.baseTotal || 0),

                // PPN per row:
                // tidak mengubah field lama, hanya menambahkan
                // state agar pengecualian per item dapat diketahui.
                ppn_enabled: row.dataset.ppnEnabled === '1',
                ppn_rate: Number(
                    document.getElementById('itemsTable')
                    ?.dataset.ppnRate || 11
                ),
                images: images,
                catatan: noteBox ? extractNoteData(noteBox) : {
                    remark: '',
                    images: []
                },
                custom_columns: customColumns
            });
        });

        const mode = document.getElementById('spk_mode')?.value;
        const spkId = document.getElementById('spk_id')?.value;
        let customHeaders = [];
        document.querySelectorAll('.spk-dynamic-header').forEach(th => {
            const label = th.innerText.replace('+', '').trim();
            const key = th.dataset.custom || label.toLowerCase().replace(/\s+/g, '_');
            customHeaders.push({
                key,
                label
            });
        });

        const payload = {
            spk_id: mode === 'edit' ? spkId : null,
            spk_type: document.getElementById('spk_type').value,
            custom_headers: customHeaders,
            no_spk: document.querySelector('.no-spk')?.innerText.trim() || '',
            no_po: document.querySelector('.no-po')?.innerText.trim() || '',
            nama: document.getElementById('supplierInput')?.innerText.trim() || '',
            tgl_terima: getSpkDateValue('.tgl-terima'),
            tgl_selesai: getSpkDateValue('.tgl-selesai'),

            // =========================================
            // PPN - SATU-SATUNYA SUMBER UNTUK SAVE
            // =========================================
            ppn_enabled: String(
                document.getElementById('itemsTable')?.dataset.ppnEnabled || '0'
            ) === '1',

            ppn_rate: Number(
                document.getElementById('itemsTable')?.dataset.ppnRate || 11
            ),

            // PPN DISIMPAN DI DATA SPK, BUKAN DI SETIAP ITEM.
            // Harga dan total item tetap NILAI SEBELUM PPN.

            items: items,
            payments: payments
        };
        console.log('=== PPN SAVE DEBUG ===');
        console.log('TABLE:', document.getElementById('itemsTable'));
        console.log(
            'PPN ENABLED:',
            document.getElementById('itemsTable')?.dataset.ppnEnabled
        );
        console.log(
            'PPN RATE:',
            document.getElementById('itemsTable')?.dataset.ppnRate
        );
        console.log('PAYLOAD:', payload);
        let url = mode === 'edit' ? "{{ url('/spk/update') }}/" + spkId : "{{ url('/spk/create') }}/" +
            spkId;

        fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'SPK Berhasil Disimpan',
                        html: `<div style="font-size:13px">${res.message || ''}<br><b>No SPK:</b> <span style="color:#16a34a; font-weight:700;">${res.no_spk || ''}</span></div>`,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Gagal menyimpan SPK'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Server',
                    text: 'Terjadi kesalahan koneksi'
                });
            });
    });

    function collectSpkData() {
        const data = {
            supplier: {
                no_spk: document.querySelector('.no-spk')?.innerText.trim() || '',
                no_po: document.querySelector('.no-po')?.innerText.trim() || '',
                nama_supplier: document.getElementById('supplierInput')?.innerText.trim() || '',
                tgl_terima: document.querySelector('.tgl-terima .spk-date-display')?.value || '',
                tgl_selesai: document.querySelector('.tgl-selesai .spk-date-display')?.value || ''
            },
            headers: [],
            items: [],
            payments: [],
        };

        document.querySelectorAll('.spk-dynamic-header').forEach(th => {
            data.headers.push({
                key: th.dataset.custom,
                label: th.innerText.trim()
            });
        });

        document.querySelectorAll('.spk-rowa').forEach(row => {
            let item = {
                kode: row.querySelector('.kode-item')?.innerText.trim() || '',
                nama: row.querySelector('.nama')?.innerText.trim() || '',
                images: [],
                catatan: row.querySelector('.note-box')?.innerText.trim() || '',
                rows: []
            };
            row.querySelectorAll('.image-box img').forEach(img => item.images.push(img.src));

            let parentCustom = {};
            row.querySelectorAll('.custom-column').forEach(col => parentCustom[col.dataset.custom] = col
                .innerText.trim());

            item.rows.push({
                custom: parentCustom,
                p: row.querySelector('.p')?.innerText.trim() || '',
                l: row.querySelector('.l')?.innerText.trim() || '',
                t: row.querySelector('.t')?.innerText.trim() || '',
                material: row.querySelector('.material')?.innerText.trim() || '',
                pcs: row.querySelector('.pcs')?.innerText.trim() || '',
                set: row.querySelector('.set')?.innerText.trim() || '',
                harga: row.querySelector('.harga')?.innerText.trim() || '',
                ppn: row.querySelector('.ppn-cell')?.innerText.trim() || '',
                ppn_enabled: row.dataset.ppnEnabled === '1',
                total: row.querySelector('.total')?.innerText.trim() || ''
            });

            let next = row.nextElementSibling;
            while (next && next.classList.contains('extra-row')) {
                let extraCustom = {};
                next.querySelectorAll('.custom-column').forEach(col => extraCustom[col.dataset.custom] = col
                    .innerText.trim());
                item.rows.push({
                    custom: extraCustom,
                    p: next.querySelector('.p')?.innerText.trim() || '',
                    l: next.querySelector('.l')?.innerText.trim() || '',
                    t: next.querySelector('.t')?.innerText.trim() || '',
                    material: next.querySelector('.material')?.innerText.trim() || '',
                    pcs: next.querySelector('.pcs')?.innerText.trim() || '',
                    set: next.querySelector('.set')?.innerText.trim() || '',
                    harga: next.querySelector('.harga')?.innerText.trim() || '',
                    ppn: next.querySelector('.ppn-cell')?.innerText.trim() || '',
                    ppn_enabled: next.dataset.ppnEnabled === '1',
                    total: next.querySelector('.total')?.innerText.trim() || ''
                });
                next = next.nextElementSibling;
            }
            data.items.push(item);
        });

        document.querySelectorAll('.payment-row').forEach(row => {
            data.payments.push({
                amount: row.querySelector('.total-amount')?.innerText.trim() || '',
                date: row.querySelector('.date-isian')?.innerText.trim() || '',
                type: row.querySelector('.payment-type')?.value || '',
                note: row.querySelector('.note-tambahan')?.innerText.trim() || '',
                note_tambahan: row.querySelector('.note-tambahan')?.innerText.trim() || '',
                is_request: row.querySelector('.payment-request-check')?.checked || false,
                adjustment: parseFloat(row.dataset.adjustment || 0) || 0
            });
        });

        return data;
    }

    /* =========================================
       PRINT PREVIEW - SAME LAYOUT AS INDEX.BLADE (8)
       ========================================= */
    document.getElementById('previewBtn')?.addEventListener('click', function() {
        const data = collectSpkData();
        renderPrevieww(data);
    });

    function renderPrevieww(data) {
        let dynamicHeader = '';
        data.headers.forEach(h => {
            dynamicHeader += `
            <th>${h.label}</th>
        `;
        });
        let rows = '';
        let customHeaderHtml = '';
        data.headers.forEach(h => {
            customHeaderHtml += `
        <th rowspan="2">
            ${h.label}
        </th>
    `;
        });
        data.items.forEach(item => {
            item.rows.forEach((detail, index) => {
                let customCols = '';
                data.headers.forEach(h => {
                    customCols += `
                    <td>
                        ${detail.custom[h.key] ?? ''}
                    </td>
                `;
                });
                rows += `
            <tr>
                ${index === 0
                        ? `
                    <td rowspan="${item.rows.length}">
                        ${item.kode}
                    </td>
                    `
                        : ''
                    }
                ${index === 0
                        ? `
                    <td rowspan="${item.rows.length}">
                        ${item.images.length
                            ? `<img src="${item.images[0]}" style="max-width:90px">`
                            : ''
                        }
                    </td>
                    `
                        : ''
                    }
                ${index === 0
                        ? `
                    <td rowspan="${item.rows.length}">
                        ${item.nama}
                    </td>
                    `
                        : ''
                    }
                ${customCols}
                <td>${detail.p}</td>
                <td>${detail.l}</td>
                <td>${detail.t}</td>
                <td style="white-space:pre-line">
                    ${detail.material}
                </td>
                <td>${detail.pcs}</td>
                <td>${detail.set}</td>
                <td>${detail.harga}</td>
                ${data.ppn_enabled ? `<td>${detail.ppn || data.ppn_rate + '%'}</td>` : ''}
                <td>${detail.total}</td>
                ${index === 0
                        ? `
                    <td rowspan="${item.rows.length}">
                        ${item.catatan}
                    </td>
                    `
                        : ''
                    }
            </tr>
            `;
            });
        });
        let paymentRows = '';
        data.payments.forEach(pay => {
            paymentRows += `
        <tr>
            <td>${pay.amount}</td>
            <td>${pay.date}</td>
            <td>${pay.type}</td>
            <td>${pay.note}</td>
        </tr>
        `;
        });
        const html = `
    <html>
    <head>
        <title>Preview SPK</title>
        <style>
            @page { size: landscape; margin: 10mm; }
            *{ box-sizing:border-box; }
            body{
                font-family:Arial, sans-serif;
                padding:0;
                color:#111;
            }
            table{
                width:100%;
                border-collapse:collapse;
                margin-bottom:20px;
            }
            th,td{
                border:1px solid #000;
                padding:5px;
                font-size:12px;
                vertical-align:middle;
            }
            th{
                background:#2f437f;
                color:#fff;
            }
            .spk-print-table{ table-layout:auto; }
            .spk-print-table th,
            .spk-print-table td{ font-size:10px; }
            .spk-print-table td{ word-break:break-word; }
            .print-bottom-grid{ display:flex; gap:18px; align-items:flex-start; margin-top:20px; }
            .print-agreement-column{ flex:1 1 auto; min-width:0; font-size:11px; line-height:1.6; }
            .print-payment-column{ flex:0 0 38%; min-width:360px; }
            .print-payment-section{ margin-top:0 !important; }
            @media print{
                .spk-print-table thead{ display:table-header-group; }
                .spk-print-table tr{ break-inside:avoid; page-break-inside:avoid; }
            }
            img{
                display:block;
                margin:auto;
            }
            .header{
                margin-bottom:20px;
            }
            .header div{
                margin-bottom:4px;
            }
        
        /* BAHAN BAKU TABLE */
        .bahan-baku-row td {
            white-space: nowrap;
        }

        .bahan-baku-row td:nth-child(4),
        .bahan-baku-row td:nth-child(9) {
            white-space: normal;
        }

        .bahan-baku-row:hover td {
            background: #f8fafc !important;
        }



        /* =========================================================
           QTY UNIT EDITOR (PCS / KG / SET / CUSTOM)
           Tambahan saja - tidak mengubah style existing.
           ========================================================= */
      /* =========================================================
   QTY UNIT HEADER
   ========================================================= */

.qty-unit-header {
    position: relative !important;
    min-width: 74px !important;
    width: 74px !important;
    padding: 0 !important;
    text-align: center;
    vertical-align: middle;
    overflow: visible !important;
}

.qty-unit-control {
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    user-select: none;
}

#qtyUnitLabel {
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    color: #fff;
    letter-spacing: .2px;
}

/* tombol dropdown */
.qty-unit-toggle {
    width: 20px;
    height: 20px;

    padding: 0;
    margin: 0;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid rgba(255,255,255,.65);
    border-radius: 4px;

    background: rgba(255,255,255,.16);
    color: #fff;

    cursor: pointer;

    transition:
        background .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.qty-unit-toggle:hover {
    background: rgba(255,255,255,.30);
    border-color: #fff;
}

.qty-unit-toggle:active {
    transform: scale(.94);
}

.qty-chevron {
    display: block;
    font-size: 12px;
    line-height: 1;
    transform: translateY(-1px);
}


/* =========================================================
   DROPDOWN
   ========================================================= */

.qty-unit-dropdown {
    position: absolute;

    top: calc(100% + 5px);
    left: 50%;

    transform: translateX(-50%) translateY(-4px);

    width: 82px;

    padding: 4px;

    background: #fff;

    border: 1px solid #d9dee7;
    border-radius: 7px;

    box-shadow:
        0 6px 18px rgba(0,0,0,.16);

    z-index: 99999;

    display: none;

    opacity: 0;
    visibility: hidden;

    transition:
        opacity .15s ease,
        transform .15s ease,
        visibility .15s ease;
}

.qty-unit-dropdown.show {
    display: block;

    opacity: 1;
    visibility: visible;

    transform: translateX(-50%) translateY(0);
}


/* item dropdown */
.qty-unit-dropdown button {
    width: 100%;

    height: 28px;

    padding: 0 8px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 0;
    border-radius: 5px;

    background: transparent;

    color: #273444;

    font-size: 10px;
    font-weight: 700;

    cursor: pointer;

    transition:
        background .12s ease,
        color .12s ease;
}

.qty-unit-dropdown button:hover {
    background: #f0f3f7;
}

.qty-unit-dropdown button.active {
    background: #4dbd5a;
    color: #fff;
}


/* =========================================================
   PANAH KECIL DI ATAS DROPDOWN
   ========================================================= */

.qty-unit-dropdown::before {
    content: "";

    position: absolute;

    top: -5px;
    left: 50%;

    width: 9px;
    height: 9px;

    background: #fff;

    border-left: 1px solid #d9dee7;
    border-top: 1px solid #d9dee7;

    transform: translateX(-50%) rotate(45deg);
}

        .btn-edit-qty-unit {
            border: 0;
            background: transparent;
            padding: 0 2px;
            margin-left: 2px;
            color: inherit;
            cursor: pointer;
            font-size: 10px;
            line-height: 1;
            vertical-align: middle;
        }

        .btn-edit-qty-unit:hover {
            opacity: .7;
            transform: scale(1.08);
        }

        .qty-unit-popover {
            position: fixed !important;
            z-index: 2147483647 !important;
            width: 160px !important;
            min-width: 160px !important;
            max-width: 160px !important;
            padding: 5px !important;
            margin: 0 !important;
            background: #fff !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            box-shadow: 0 8px 25px rgba(0,0,0,.18) !important;
            overflow: visible !important;
            transform: none !important;
            float: none !important;
        }

        .qty-unit-option {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            height: 30px !important;
            border: 0 !important;
            background: transparent !important;
            text-align: left !important;
            padding: 7px 9px !important;
            margin: 0 !important;
            border-radius: 5px !important;
            cursor: pointer !important;
            font-size: 11px !important;
            line-height: 16px !important;
            color: #111827 !important;
        }

        .qty-unit-option:hover {
            background: #f1f5f9;
        }

        .qty-unit-option.active {
            background: #dcfce7;
            color: #15803d;
            font-weight: 700;
        }

        .qty-unit-custom {
            display: block !important;
            width: 100% !important;
            margin-top: 5px !important;
            padding: 6px 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 5px !important;
            outline: none !important;
            font-size: 11px !important;
            box-sizing: border-box !important;
        }
</style>
    </head>
    <body>
       ${getKopHtml(data)}
        <table class="spk-print-table">
    <thead>
<tr>
    <th rowspan="2">Article Nr</th>
    <th rowspan="2">Gambar</th>
    <th rowspan="2">Nama Barang</th>
    ${customHeaderHtml}
    <th colspan="3">
        Ukuran
    </th>
    <th rowspan="2">
        Material
    </th>
    <th colspan="2">
        Qty
    </th>
    <th rowspan="2">
        Harga
    </th>
    ${data.ppn_enabled ? `<th rowspan="2">PPN</th>` : ''}
    <th rowspan="2">
        Total
    </th>
    <th rowspan="2">
        Catatan
    </th>
</tr>
<tr>
    <th>P</th>
    <th>L</th>
    <th>T</th>
    <th>PCS</th>
    <th>SET</th>
</tr>
</thead>
            <tbody>
                ${rows}
            </tbody>
        </table>
 <div class="print-bottom-grid" style="
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-top:20px;
">
    <!-- kiri -->
 <div class="print-agreement-column" style="
    flex:1;
    padding-right:20px;
    font-size:13px;
    line-height:1.8;
">
    ${renderAgreement()}
</div>
    <!-- kanan -->
    <div class="print-payment-column" style="
        width:500px;
        flex-shrink:0;
    ">
        ${renderPaymentSection(data)}
    </div>
</div>
 ${renderSignaturePreview(data)}
        <script>
            window.onload = function(){
                window.print();
            }
        <\/script>
    </body>
    </html>
    `;
        const win = window.open('', '_blank');
        win.document.open();
        win.document.write(html);
        win.document.close();
    }
    /* =========================================================
       EXCEL-LIKE SPK RENDERER
       KHUSUS SCREENSHOT + SALIN
       ---------------------------------------------------------
       CATATAN:
       - Semua fungsi SPK lain TIDAK disentuh.
       - Renderer ini hanya digunakan oleh Screenshot + Salin.
       - Ukuran output dikunci A4 landscape 1123 x 794 px.
       ========================================================= */
    const SPK_EXPORT_WIDTH = 1123;
    const SPK_EXPORT_HEIGHT = 794;

    function cleanText(value) {
        return String(value ?? '')
            .replace(/\u00a0/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getExcelSpkData() {
        const data = {
            supplier: {
                no_spk: cleanText(document.querySelector('.no-spk')?.innerText),
                no_po: cleanText(document.querySelector('.no-po')?.innerText),
                nama_supplier: cleanText(document.getElementById('supplierInput')?.innerText),
                tgl_terima: typeof getSpkDateValue === 'function' ?
                    cleanText(getSpkDateValue('.tgl-terima')) :
                    cleanText(document.querySelector('.tgl-terima')?.innerText),
                tgl_selesai: typeof getSpkDateValue === 'function' ?
                    cleanText(getSpkDateValue('.tgl-selesai')) :
                    cleanText(document.querySelector('.tgl-selesai')?.innerText)
            },
            items: [],
            payments: [],
            qtyUnit: (document.getElementById('qtyUnit')?.value ||
                    document.getElementById('qtyUnitLabel')?.innerText || 'PCS')
                .toString()
                .trim()
                .toUpperCase() || 'PCS'
        };

        document.querySelectorAll('.spk-rowa').forEach(row => {
            const item = {
                kode: cleanText(row.querySelector('.kode-item')?.innerText),
                nama: cleanText(row.querySelector('.nama')?.innerText),
                image: row.querySelector('.image-box img')?.src || '',
                catatan: cleanText(row.querySelector('.note-box')?.innerText),
                satuan: cleanText(row.dataset.satuan || getQtyUnit()),
                rows: []
            };

            const readDetail = source => ({
                p: cleanText(source.querySelector('.p')?.innerText),
                l: cleanText(source.querySelector('.l')?.innerText),
                t: cleanText(source.querySelector('.t')?.innerText),
                material: cleanText(source.querySelector('.material')?.innerText),
                pcs: cleanText(source.querySelector('.pcs')?.innerText),
                set: cleanText(source.querySelector('.set')?.innerText),
                harga: cleanText(source.querySelector('.harga')?.innerText),
                ppn: cleanText(source.querySelector('.ppn-cell')?.innerText),
                ppn_enabled: source.dataset.ppnEnabled === '1',
                total: cleanText(source.querySelector('.total')?.innerText)
            });

            item.rows.push(readDetail(row));

            let next = row.nextElementSibling;
            while (next && next.classList.contains('extra-row')) {
                item.rows.push(readDetail(next));
                next = next.nextElementSibling;
            }

            data.items.push(item);
        });

        document.querySelectorAll('.payment-row').forEach(row => {
            data.payments.push({
                amount: cleanText(row.querySelector('.total-amount')?.innerText),
                date: cleanText(row.querySelector('.date-isian')?.innerText),
                type: cleanText(row.querySelector('.payment-type')?.value),
                note: cleanText(row.querySelector('.note-tambahan')?.innerText)
            });
        });

        return data;
    }

    function excelEscape(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function excelNumber(value) {
        if (value === null || value === undefined || value === '') return 0;

        let text = String(value)
            .replace(/Rp/gi, '')
            .replace(/\s/g, '')
            .trim();

        let number;

        if (text.includes('.') && text.includes(',')) {
            number = text.lastIndexOf(',') > text.lastIndexOf('.') ?
                parseFloat(text.replace(/\./g, '').replace(',', '.')) :
                parseFloat(text.replace(/,/g, ''));
        } else if (text.includes('.')) {
            const parts = text.split('.');
            number = parts.length > 2 ?
                parseFloat(text.replace(/\./g, '')) :
                parseFloat(text);
        } else if (text.includes(',')) {
            const parts = text.split(',');
            number = parts.length === 2 && parts[1].length <= 2 ?
                parseFloat(text.replace(',', '.')) :
                parseFloat(text.replace(/,/g, ''));
        } else {
            number = parseFloat(text);
        }

        return Number.isNaN(number) ? 0 : number;
    }

    function excelMoney(value) {
        return 'Rp ' + Math.round(excelNumber(value)).toLocaleString('id-ID');
    }

    function excelImage(src) {
        if (!src) return '';

        return `<img
            src="${excelEscape(src)}"
            crossorigin="anonymous"
            alt=""
        >`;
    }

    function getSignatureName(type) {
        const approval = document.querySelector('.card-header + .card-body table');
        if (!approval) return '-';

        const rows = approval.querySelectorAll('tr');
        if (rows.length < 3) return '-';

        const cells = rows[2].querySelectorAll('td');
        if (!cells.length) return '-';

        const index =
            type === 'made' ? 0 :
            type === 'checked' ? 1 :
            type === 'approved' ? 2 : -1;

        if (index < 0) return '-';

        return cells[index]
            ?.innerText
            ?.split('\n')
            ?.map(cleanText)
            ?.filter(Boolean)[0] || '-';
    }

    function buildExcelLikeSpk(data) {
        const logo = document.querySelector('#printArea img')?.src || '';

        let grandTotal = 0;
        data.items.forEach(item => {
            item.rows.forEach(detail => {
                grandTotal += excelNumber(detail.total);
            });
        });

        let html = `
            <div class="spk-excel-image">
                <div class="spk-excel-top">
                    <div class="spk-excel-logo">
                        ${logo ? `<img src="${excelEscape(logo)}" crossorigin="anonymous" alt="">` : ''}
                    </div>
                    <div></div>
                    <div class="spk-excel-company">
                        <strong>PT. NewWicker Indonesia</strong><br>
                        Jalan Kisaba Nganti RT 019 RW 002, Bode Lor<br>
                        Plumbon, Cirebon 45155<br>
                        Indonesia<br>
                        <u>factory@newwicker.com</u>
                    </div>
                </div>

                <table class="spk-excel-info">
                    <colgroup>
                        <col class="label-col">
                        <col class="value-col">
                        <col class="po-col">
                    </colgroup>
                    <tr>
                        <td class="label">NO Spk</td>
                        <td>${excelEscape(data.supplier.no_spk)}</td>
                        <td class="po">${excelEscape(data.supplier.no_po)}</td>
                    </tr>
                    <tr>
                        <td class="label">Nama</td>
                        <td colspan="2">${excelEscape(data.supplier.nama_supplier)}</td>
                    </tr>
                    <tr>
                        <td class="label">Tgl Terima</td>
                        <td>${excelEscape(data.supplier.tgl_terima)}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="label">Tgl Selesai</td>
                        <td>${excelEscape(data.supplier.tgl_selesai)}</td>
                        <td></td>
                    </tr>
                </table>

                <div class="spk-excel-item-area">
                    <table class="spk-excel-items">
                        <colgroup>
                            <col class="c-code">
                            <col class="c-image">
                            <col class="c-name">
                            <col class="c-p">
                            <col class="c-l">
                            <col class="c-t">
                            <col class="c-material">
                            <col class="c-pcs">
                            <col class="c-set">
                            <col class="c-price">
                            <col class="c-total">
                            <col class="c-note">
                        </colgroup>
                        <thead>
                            <tr>
                                <th rowspan="2">Kode Barang</th>
                                <th rowspan="2">Gambar</th>
                                <th rowspan="2">Nama Barang</th>
                                <th colspan="3">Ukuran</th>
                                <th rowspan="2">Material</th>
                                <th colspan="2">QTY</th>
                                <th rowspan="2">Harga</th>
                                <th rowspan="2">Total</th>
                                <th rowspan="2">Catatan</th>
                            </tr>
                            <tr>
                                <th>P</th>
                                <th>L</th>
                                <th>T</th>
                                <th>${excelEscape(String(data.qtyUnit || 'PCS').toUpperCase())}</th>
                                <th>SET</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        const ITEM_AREA_HEIGHT = 438;
        const HEADER_HEIGHT = 33;
        const TOTAL_HEIGHT = 20;

        let estimatedRowsHeight = 0;

        data.items.forEach(item => {
            item.rows.forEach((detail, index) => {
                estimatedRowsHeight += index === 0 ? 62 : 48;
            });
        });

        const spacerHeight = Math.max(
            0,
            ITEM_AREA_HEIGHT - HEADER_HEIGHT - TOTAL_HEIGHT - estimatedRowsHeight
        );

        data.items.forEach(item => {
            item.rows.forEach((detail, index) => {
                const isParent = index === 0;
                const rowspan = item.rows.length;

                html += `<tr class="spk-excel-item-row${isParent ? '' : ' extra-row'}">`;

                if (isParent) {
                    html += `
                        <td rowspan="${rowspan}">
                            <div class="excel-code-text">${excelEscape(item.kode)}</div>
                        </td>
                        <td rowspan="${rowspan}">
                            <div class="excel-image-cell">
                                ${excelImage(item.image)}
                            </div>
                        </td>
                        <td rowspan="${rowspan}">
                            <div class="excel-name-text">${excelEscape(item.nama)}</div>
                        </td>
                    `;
                }

                html += `
                    <td class="excel-number">${excelEscape(detail.p)}</td>
                    <td class="excel-number">${excelEscape(detail.l)}</td>
                    <td class="excel-number">${excelEscape(detail.t)}</td>
                    <td>
                        <div class="excel-material-text">${excelEscape(detail.material)}</div>
                    </td>
                    <td class="excel-number">${excelEscape(detail.pcs)}</td>
                    <td class="excel-number">${excelEscape(detail.set)}</td>
                    <td class="excel-money">${excelMoney(detail.harga)}</td>
                    <td class="excel-money">${excelMoney(detail.total)}</td>
                `;

                if (isParent) {
                    html += `
                        <td rowspan="${rowspan}">
                            <div class="excel-note-text">${excelEscape(item.catatan)}</div>
                        </td>
                    `;
                }

                html += '</tr>';
            });
        });

        html += `
                        </tbody>
                        <tfoot>
                            <tr class="spk-excel-spacer" style="height:${spacerHeight}px">
                                <td colspan="12"></td>
                            </tr>
                            <tr class="ex-grand-total">
                                <td colspan="10" class="ex-grand-total-label">TOTAL</td>
                                <td class="excel-money">${excelMoney(grandTotal)}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="spk-excel-bottom">
                    <div class="spk-excel-terms">
                        <div>1. Spesifikasi barang harus sesuai dengan sample.</div>
                        <div>2. Harga belum termasuk transportasi sampai gudang NewWicker.</div>
                        <div>3. Supplier bertanggung jawab atas ketidaksesuaian spesifikasi barang.</div>
                        <div>4. Final Quality Controlling akan dilaksanakan di gudang NewWicker.</div>
                        <div>5. Supplier dikenakan penalty 1% setiap harinya atas keterlambatan produksi.</div>
                        <div>6. Supplier wajib melaporkan perkembangan produksi dan permasalahan yang dapat menghambat kelancaran produksi.</div>
                        <div>7. Penyelesaian pembayaran dilakukan setelah supplier memenuhi semua kewajibannya.</div>
                        <div>8. Supplier dilarang memberikan hadiah atau komisi dalam bentuk uang kepada karyawan dan staff PT. NewWicker.</div>
                        <div class="agreement-end">Dengan Anda.</div>
                    </div>

                    <div class="spk-excel-payment">
                        <table>
                            <colgroup>
                                <col class="ex-pay-amount">
                                <col class="ex-pay-date">
                                <col class="ex-pay-note">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Ammount</th>
                                    <th>Date</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        const paymentRows = data.payments.length ?
            data.payments :
            [{}, {}, {}, {}];

        paymentRows.slice(0, 5).forEach(pay => {
            html += `
                <tr>
                    <td class="excel-money">${pay.amount ? excelMoney(pay.amount) : ''}</td>
                    <td>${excelEscape(pay.date || '')}</td>
                    <td>${excelEscape(pay.note || pay.type || '')}</td>
                </tr>
            `;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>

                <table class="spk-excel-signature">
                    <tr>
                        <td class="ex-sign-title">Made by :</td>
                        <td class="ex-sign-title">Checked By:</td>
                        <td class="ex-sign-title">Approved by:</td>
                        <td class="ex-sign-title">Know by:</td>
                    </tr>
                    <tr>
                        <td class="ex-sign-space"></td>
                        <td class="ex-sign-space"></td>
                        <td class="ex-sign-space"></td>
                        <td class="ex-sign-space"></td>
                    </tr>
                    <tr>
                        <td class="ex-sign-name">${excelEscape(getSignatureName('made'))}</td>
                        <td class="ex-sign-name">${excelEscape(getSignatureName('checked'))}</td>
                        <td class="ex-sign-name">${excelEscape(getSignatureName('approved'))}</td>
                        <td class="ex-sign-name">${excelEscape(data.supplier.nama_supplier || '-')}</td>
                    </tr>
                </table>
            </div>
        `;

        return html;
    }

    async function captureExcelSpk() {
        if (typeof html2canvas === 'undefined') {
            throw new Error('Library html2canvas belum tersedia.');
        }

        const wrapper = document.createElement('div');

        /*
         * Jangan menggunakan scrollWidth / scrollHeight.
         * Output harus selalu tepat A4 landscape.
         */
        Object.assign(wrapper.style, {
            position: 'fixed',
            left: '0px',
            top: '0px',
            width: `${SPK_EXPORT_WIDTH}px`,
            height: `${SPK_EXPORT_HEIGHT}px`,
            minWidth: `${SPK_EXPORT_WIDTH}px`,
            maxWidth: `${SPK_EXPORT_WIDTH}px`,
            minHeight: `${SPK_EXPORT_HEIGHT}px`,
            maxHeight: `${SPK_EXPORT_HEIGHT}px`,
            overflow: 'hidden',
            background: '#ffffff',
            zIndex: '-999999',
            pointerEvents: 'none'
        });

        wrapper.innerHTML = buildExcelLikeSpk(getExcelSpkData());
        document.body.appendChild(wrapper);

        try {
            const images = Array.from(wrapper.querySelectorAll('img'));

            await Promise.all(images.map(img => {
                if (img.complete) return Promise.resolve();

                return new Promise(resolve => {
                    let done = false;
                    const finish = () => {
                        if (done) return;
                        done = true;
                        resolve();
                    };

                    img.onload = finish;
                    img.onerror = finish;

                    /* Jangan biarkan satu gambar menggantung selamanya. */
                    setTimeout(finish, 2500);
                });
            }));

            /* Beri browser satu frame untuk menyelesaikan layout gambar/font. */
            await new Promise(resolve => {
                requestAnimationFrame(() => requestAnimationFrame(resolve));
            });

            return await html2canvas(wrapper, {
                backgroundColor: '#ffffff',
                scale: 2,
                useCORS: true,
                allowTaint: false,
                logging: false,
                width: SPK_EXPORT_WIDTH,
                height: SPK_EXPORT_HEIGHT,
                windowWidth: SPK_EXPORT_WIDTH,
                windowHeight: SPK_EXPORT_HEIGHT,
                scrollX: 0,
                scrollY: 0
            });
        } finally {
            wrapper.remove();
        }
    }

    document.getElementById('copyJpegBtn')?.addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;

        try {
            if (!navigator.clipboard || typeof ClipboardItem === 'undefined') {
                throw new Error('Browser tidak mendukung salin gambar.');
            }

            btn.disabled = true;
            btn.innerHTML = '⏳ Menyiapkan...';

            const canvas = await captureExcelSpk();
            const blob = await new Promise(resolve =>
                canvas.toBlob(resolve, 'image/png')
            );

            if (!blob) {
                throw new Error('Gambar gagal dibuat.');
            }

            await navigator.clipboard.write([
                new ClipboardItem({
                    'image/png': blob
                })
            ]);

            btn.innerHTML = '✅ Tersalin';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1800);
        } catch (error) {
            console.error('Excel-like SPK:', error);

            Swal.fire({
                icon: 'error',
                title: 'Gagal menyalin',
                text: error.message || 'Gagal membuat gambar SPK.'
            });

            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    document.getElementById('screenshotSpkBtn')?.addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;

        try {
            btn.disabled = true;
            btn.innerHTML = '⏳ Membuat...';

            const canvas = await captureExcelSpk();
            const blob = await new Promise(resolve =>
                canvas.toBlob(resolve, 'image/png')
            );

            if (!blob) {
                throw new Error('Screenshot gagal dibuat.');
            }

            const noSpk = cleanText(
                document.querySelector('.no-spk')?.innerText
            ).replace(/[\\/:*?"<>|]/g, '-') || 'SPK';

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');

            a.href = url;
            a.download = `SPK-${noSpk}.png`;
            document.body.appendChild(a);
            a.click();
            a.remove();

            setTimeout(() => URL.revokeObjectURL(url), 1000);

            btn.innerHTML = '✅ Selesai';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1500);
        } catch (error) {
            console.error('Screenshot SPK:', error);

            Swal.fire({
                icon: 'error',
                title: 'Screenshot gagal',
                text: error.message || 'Gagal membuat screenshot.'
            });

            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    function renderAgreement() {
        return `
        <div>
            <ol style="padding-left:18px;margin:0;">
                <li>Spesifikasi barang harus sesuai dengan sample.</li>
                <li>Harga belum termasuk transportasi sampai gudang NewWicker.</li>
                <li>Supplier bertanggung jawab atas ketidaksesuaian spesifikasi barang.</li>
                <li>Final Quality Controlling akan dilaksanakan di gudang NewWicker.</li>
                <li>Supplier dikenakan penalty 1% setiap harinya atas keterlambatan produksi.</li>
                <li>Supplier wajib melaporkan perkembangan produksi dan permasalahan yang dapat menghambat kelancaran produksi.</li>
                <li>Penyelesaian pembayaran dilakukan setelah supplier memenuhi semua kewajibannya.</li>
                <li>Supplier dilarang memberikan hadiah atau komisi dalam bentuk uang kepada karyawan dan staff PT. NewWicker.</li>
            </ol>

            <div style="margin-top:15px">
                Dengan Anda.
            </div>
        </div>
    `;
    }

    function renderSignatureSection(data) {
        return `
    <div style="
        width:100%;
    " id="signatureSection">
        <table style="
            width:100%;
            border-collapse:collapse;
            border:none;
        ">
            <tr>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Made By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Checked By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Approved By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Know By :
                </td>
                <td style="
                    border:none;
                    width:20%;
                    text-align:center;
                    font-weight:bold;
                ">
                    Supplier
                </td>
            </tr>
            <tr>
                <td colspan="5"
                    style="
                        border:none;
                        height:80px;
                    ">
                </td>
            </tr>
            <tr>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                  "Nur"
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    VIVI
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    Mr. Stanley
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    ${data.supplier.nama_supplier}
                </td>
            </tr>
            <tr>
                <td style="border:none"></td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    Purchasing
                </td>
                <td style="
                    border:none;
                    text-align:center;
                    font-weight:bold;
                ">
                    General Manager
                </td>
                <td style="border:none"></td>
                <td style="border:none"></td>
            </tr>
        </table>
    </div>
    `;
    }

    function renderPaymentSection(data) {
        let grandTotal = 0;
        data.items.forEach(item => {
            item.rows.forEach(detail => {
                grandTotal += parseFloat(
                    String(detail.total || 0)
                    .replace(/\./g, '')
                    .replace(/,/g, '')
                ) || 0;
            });
        });
        const totalFormat = new Intl.NumberFormat('id-ID')
            .format(grandTotal);
        let paymentRows = '';
        data.payments.forEach(pay => {
            const note =
                pay.type ||
                pay.note ||
                '';
            const keterangan =
                pay.keterangan ||
                pay.note_tambahan ||
                note;
            paymentRows += `
            <tr>
                <td style="text-align:center">
                    ${pay.is_request ? '✓' : ''}
                </td>
                <td>
                    ${pay.amount || ''}
                </td>
                <td>
                    ${pay.date || ''}
                </td>
                <td>
                    ${note}
                </td>
                <td>
                    ${keterangan}
                </td>
            </tr>
        `;
        });
        const emptyRows = Math.max(
            0,
            6 - data.payments.length
        );
        for (let i = 0; i < emptyRows; i++) {
            paymentRows += `
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        `;
        }
        return `
    <div class="print-payment-section" style="
        margin-top:0;
        width:100%;
        display:flex;
        justify-content:flex-end;
    ">
        <div style="
            width:500px;
        ">
            <!-- TOTAL -->
            <table style="
                width:100%;
                border-collapse:collapse;
                margin-bottom:6px;
            ">
                <tr>
                    <td style="
                        border:1px solid #000;
                        text-align:right;
                        padding-right:10px;
                        font-weight:bold;
                    ">
                        ${totalFormat}
                    </td>
                </tr>
            </table>
            <!-- PAYMENT -->
            <table style="
                width:100%;
                border-collapse:collapse;
            ">
                <thead>
                    <tr>
                        <th width="40">
                            Req
                        </th>
                        <th width="120">
                            Amount
                        </th>
                        <th width="80">
                            Date
                        </th>
                        <th width="100">
                            Note
                        </th>
                        <th>
                            Keterangan
                        </th>
                    </tr>
                </thead>
                <tbody>
                    ${paymentRows}
                </tbody>
            </table>
        </div>
    </div>
    `;
    }

    function getKopHtml(data) {
        return `
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        margin-bottom:20px;
    ">
        <!-- kiri -->
        <div>
            <img
                src="${window.location.origin}/assets/images/NEWWICKER WHITE.png"
                style="
                    width:220px;
                    height:auto;
                ">
        </div>
        <!-- kanan -->
        <div style="
            text-align:right;
            line-height:1.6;
        ">
            <div style="
                font-size:28px;
                font-weight:bold;
            ">
                PT. NewWicker Indonesia
            </div>
            Jalan Kisaba Lanang RT 019 RW 002,
            Bode Lor
            <br>
            Plumbon, Cirebon 45155
            <br>
            Indonesia
            <br><br>
            <span style="
                color:#0d6efd;
                text-decoration:underline;
            ">
                factory@newwicker.com
            </span>
        </div>
    </div>
<div style="
    border-top:2px solid #000;
    width:100%;
    margin-bottom:10px;
"></div>
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        margin-bottom:20px;
    ">
        <!-- kiri -->
        <div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    No Spk
                </span>
                :
                <span>
                    ${data.supplier.no_spk}
                </span>
            </div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Nama
                </span>
                :
                <span style="
                    background:yellow;
                    padding:2px 6px;
                ">
                    ${data.supplier.nama_supplier}
                </span>
            </div>
            <div style="margin-bottom:6px">
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Tgl Terima
                </span>
                :
                ${data.supplier.tgl_terima}
            </div>
            <div>
                <span style="
                    display:inline-block;
                    width:110px;
                ">
                    Tgl Selesai
                </span>
                :
                <span style="
                    background:yellow;
                    font-weight:bold;
                    padding:2px 6px;
                ">
                    ${data.supplier.tgl_selesai}
                </span>
            </div>
        </div>
        <!-- kanan -->
        <div style="
            background:yellow;
            padding:4px 12px;
            font-weight:bold;
            min-width:140px;
            text-align:center;
        ">
            ${data.supplier.no_po}
        </div>
    </div>
    `;
    }




    function renderSignaturePreview(data) {
        let html = '';

        @if (isset($spk['signature']) && $spk['signature'])
            html = `
                <div style="margin-top:40px;width:100%;">
                    <table style="width:100%;border-collapse:collapse;text-align:center;font-size:12px;">
                        <tr>
                            <td style="border:1px solid #000;"><b>Made By</b></td>
                            <td style="border:1px solid #000;"><b>Checked By</b></td>
                            <td style="border:1px solid #000;"><b>Approved By</b></td>
                            <td style="border:1px solid #000;"><b>Supplier</b></td>
                        </tr>
                        <tr>
                            <td style="height:90px;border:1px solid #000;">
                                @if ($spk['signature']->made_at)
                                    <img src="{{ asset('assets/signature/' . $spk['signature']->made_by . '.png') }}"
                                         style="max-height:70px;max-width:120px;">
                                @endif
                            </td>
                            <td style="height:90px;border:1px solid #000;">
                                <div style="display:flex;justify-content:space-around;align-items:center;height:90px;">
                                    <div style="flex:1;">
                                        @if ($spk['signature']->checked_at)
                                            <img src="{{ asset('assets/signature/' . $spk['signature']->checked_by . '.png') }}"
                                                 style="max-height:70px;max-width:120px;">
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        @if ($spk['signature']->checked_at_2)
                                            <img src="{{ asset('assets/signature/' . $spk['signature']->checked_by_2 . '.png') }}"
                                                 style="max-height:70px;max-width:120px;">
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="height:90px;border:1px solid #000;">
                                @if ($spk['signature']->approved_at)
                                    <img src="{{ asset('assets/signature/' . $spk['signature']->approved_by . '.png') }}"
                                         style="max-height:70px;max-width:120px;">
                                @endif
                            </td>
                            <td style="height:90px;border:1px solid #000;"></td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000;">
                                <b>{{ $spk['signature']->madeBy->name ?? '-' }}</b><br>
                                @if ($spk['signature']->made_at)
                                    Approved On<br>{{ $spk['signature']->made_at->format('d/m/Y H:i') }}
                                @else
                                    Pending
                                @endif
                            </td>
                            <td style="border:1px solid #000;">
                                <div style="display:flex;justify-content:space-around;gap:20px;">
                                    <div style="flex:1;">
                                        <b>{{ $spk['signature']->checkedBy->name ?? '-' }}</b><br>
                                        @if ($spk['signature']->checked_at)
                                            Approved On<br>{{ $spk['signature']->checked_at->format('d/m/Y H:i') }}
                                        @else
                                            Pending
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        <b>{{ $spk['signature']->checkedBy2->name ?? '-' }}</b><br>
                                        @if ($spk['signature']->checked_at_2)
                                            Approved On<br>{{ $spk['signature']->checked_at_2->format('d/m/Y H:i') }}
                                        @else
                                            Pending
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="border:1px solid #000;">
                                <b>{{ $spk['signature']->approvedBy->name ?? '-' }}</b><br>
                                @if ($spk['signature']->approved_at)
                                    Approved On<br>{{ $spk['signature']->approved_at->format('d/m/Y H:i') }}
                                @else
                                    Pending
                                @endif
                            </td>
                            <td style="border:1px solid #000;">
                                <b>${data.supplier.nama_supplier}</b>
                            </td>
                        </tr>
                    </table>
                </div>
            `;
        @endif

        return html;
    }

    /* =========================================
       COPY TO CLIPBOARD (JPEG / PNG)
       ========================================= */

    /* =========================================
       TIMELINE RIWAYAT MODAL
       ========================================= */
    document.getElementById('btnRiwayatSpk')?.addEventListener('click', function() {
        const spkId = document.getElementById('spk_id')?.value;
        if (!spkId) {
            Swal.fire({
                icon: 'warning',
                text: 'SPK belum disimpan'
            });
            return;
        }
        fetch(`/spk/timeline/${spkId}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (!data || !data.length) {
                    html = '<div class="text-center text-muted p-4">Belum ada riwayat tercatat.</div>';
                } else {
                    data.forEach(item => {
                        let row = item.data;
                        if (typeof row === 'string') {
                            try {
                                row = JSON.parse(row);
                            } catch (e) {}
                        }
                        html += `
                                <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
                                    <div style="background:#dcf8c6; max-width:80%; padding:10px 14px; border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,0.1);">
                                        <div style="font-weight:700; font-size:12px; margin-bottom:4px;">${row.user ?? 'System'}</div>
                                        <div style="font-size:11px;">${row.remark ?? row.type ?? 'Update'}</div>
                                        <div style="text-align:right; font-size:9px; color:#64748b; margin-top:4px;">${row.time ?? ''}</div>
                                    </div>
                                </div>
                            `;
                    });
                }
                document.getElementById('timelineContainer').innerHTML = html;
                $('#modalRiwayatSpk').modal('show');
            });
    });


    /* =========================================
       SIGNATURE / APPROVAL
       Same workflow as index.blade (8)
       ========================================= */
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-sign');
        if (!btn) return;

        const id = btn.dataset.id;
        const type = btn.dataset.type;
        if (!id || !type) return;

        let title = 'Approve?';
        switch (type) {
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
            title: title,
            input: 'textarea',
            inputLabel: 'Remark',
            inputPlaceholder: 'Masukkan remark...',
            inputAttributes: {
                rows: 4
            },
            showCancelButton: true,
            confirmButtonText: 'Approve',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch(`/spk/signature/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ?.content || ''
                    },
                    body: JSON.stringify({
                        type: type,
                        remark: result.value || ''
                    })
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                    return data;
                })
                .then(res => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Approval berhasil.'
                    }).then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message || 'Terjadi kesalahan'
                    });
                });
        });
    });

    (function initViewOnly() {
        if (document.getElementById('view_only')?.value != '1') return;

        document.querySelectorAll('[contenteditable]')
            .forEach(el => el.setAttribute('contenteditable', 'false'));

        document.querySelectorAll('input, textarea, select')
            .forEach(el => el.disabled = true);

        document.getElementById('btnSaveSpk')?.style.setProperty('display', 'none');
        document.getElementById('btnAddPayment')?.style.setProperty('display', 'none');

        document.querySelectorAll('.btn-add-extra, .btn-delete-extra')
            .forEach(el => el.style.display = 'none');
    })();

    /* =========================================
       EXCEL-STYLE KEYBOARD & NAVIGATION
       ========================================= */

    (function() {
        function isEditableCell(el) {
            return el && el.isContentEditable && !el.classList.contains('image-box') && !!el.closest('.spk-table');
        }

        document.addEventListener('keydown', function(e) {
            const cell = e.target.closest('.spk-table td.editable');
            if (!cell || !isEditableCell(cell)) return;

            const row = cell.closest('tr');
            const rows = Array.from(document.querySelectorAll('.spk-table tbody tr'));
            const rowIndex = rows.indexOf(row);
            const colIndex = cell.cellIndex;

            if (e.key === 'Enter') {
                e.preventDefault();
                const nextRow = rows[rowIndex + (e.shiftKey ? -1 : 1)];
                if (nextRow && nextRow.cells[colIndex]) nextRow.cells[colIndex].focus();
            } else if (e.key === 'ArrowUp' && rowIndex > 0) {
                e.preventDefault();
                if (rows[rowIndex - 1].cells[colIndex]) rows[rowIndex - 1].cells[colIndex].focus();
            } else if (e.key === 'ArrowDown' && rowIndex < rows.length - 1) {
                e.preventDefault();
                if (rows[rowIndex + 1].cells[colIndex]) rows[rowIndex + 1].cells[colIndex].focus();
            }
        });
    })();

    /* =========================================================
   MULTI CELL SELECT + DELETE
   ========================================================= */
    (function() {

        let selectionStartCell = null;
        let selectionEndCell = null;


        function getCellFromPoint(node) {

            if (!node) return null;

            let element = node.nodeType === Node.TEXT_NODE ?
                node.parentElement :
                node;

            return element?.closest(
                '#spkItemsBody td.editable[contenteditable="true"]'
            ) || null;
        }


        function getRows() {

            return Array.from(
                document.querySelectorAll('#spkItemsBody tr')
            );

        }


        function getEditableCells(row) {

            return Array.from(
                row.querySelectorAll(
                    'td.editable[contenteditable="true"]'
                )
            ).filter(cell =>
                !cell.classList.contains('image-box')
            );

        }


        function clearCellSelection() {

            document
                .querySelectorAll('.spk-cell-selected')
                .forEach(cell => {

                    cell.classList.remove(
                        'spk-cell-selected'
                    );

                });

        }


        function getCellPosition(cell) {

            const row = cell?.closest('tr');

            if (!row) return null;

            const rows = getRows();

            const rowIndex = rows.indexOf(row);

            if (rowIndex < 0) return null;

            const cells = getEditableCells(row);

            const colIndex = cells.indexOf(cell);

            if (colIndex < 0) return null;

            return {
                rowIndex,
                colIndex
            };

        }


        function selectCellRange(startCell, endCell) {

            if (!startCell || !endCell) return;

            const start = getCellPosition(startCell);
            const end = getCellPosition(endCell);

            if (!start || !end) return;

            clearCellSelection();

            const rows = getRows();

            const minRow = Math.min(
                start.rowIndex,
                end.rowIndex
            );

            const maxRow = Math.max(
                start.rowIndex,
                end.rowIndex
            );

            const minCol = Math.min(
                start.colIndex,
                end.colIndex
            );

            const maxCol = Math.max(
                start.colIndex,
                end.colIndex
            );


            for (
                let r = minRow; r <= maxRow; r++
            ) {

                const cells = getEditableCells(
                    rows[r]
                );

                for (
                    let c = minCol; c <= maxCol; c++
                ) {

                    if (cells[c]) {

                        cells[c].classList.add(
                            'spk-cell-selected'
                        );

                    }

                }

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SHIFT + CLICK
        |--------------------------------------------------------------------------
        |
        | Klik cell pertama
        | SHIFT + klik cell terakhir
        |
        | contoh:
        |
        | HARGA → CATATAN
        |
        */

        document.addEventListener(
            'click',
            function(e) {

                const cell = e.target.closest(
                    '#spkItemsBody td.editable[contenteditable="true"]'
                );

                if (!cell) return;


                if (e.shiftKey && selectionStartCell) {

                    selectionEndCell = cell;

                    selectCellRange(
                        selectionStartCell,
                        selectionEndCell
                    );

                    return;
                }


                /*
                 * klik biasa:
                 * mulai selection baru
                 */

                clearCellSelection();

                selectionStartCell = cell;
                selectionEndCell = null;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | DRAG SELECTION
        |--------------------------------------------------------------------------
        |
        | Klik + tahan lalu geser ke cell lain.
        |
        */

        let isDragging = false;

        document.addEventListener(
            'mousedown',
            function(e) {

                const cell = e.target.closest(
                    '#spkItemsBody td.editable[contenteditable="true"]'
                );

                if (!cell) return;

                /*
                 * Jangan ganggu drag text biasa
                 * kalau tidak pakai Ctrl.
                 */
                if (e.detail === 1) {

                    selectionStartCell = cell;
                    selectionEndCell = cell;

                    isDragging = true;

                    clearCellSelection();

                    cell.classList.add(
                        'spk-cell-selected'
                    );
                }

            }
        );


        document.addEventListener(
            'mouseover',
            function(e) {

                if (!isDragging) return;

                const cell = e.target.closest(
                    '#spkItemsBody td.editable[contenteditable="true"]'
                );

                if (!cell) return;

                selectionEndCell = cell;

                selectCellRange(
                    selectionStartCell,
                    selectionEndCell
                );

            }
        );


        document.addEventListener(
            'mouseup',
            function() {

                isDragging = false;

            }
        );


        /*
        |--------------------------------------------------------------------------
        | DELETE / BACKSPACE
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function(e) {

                if (
                    e.key !== 'Delete' &&
                    e.key !== 'Backspace'
                ) {
                    return;
                }


                const selectedCells =
                    Array.from(
                        document.querySelectorAll(
                            '#spkItemsBody .spk-cell-selected'
                        )
                    );


                /*
                 * Tidak ada multi-selection.
                 * Biarkan Backspace/Delete normal.
                 */

                if (selectedCells.length <= 1) {
                    return;
                }


                /*
                 * PENTING:
                 * cegah browser menghapus text saja.
                 */

                e.preventDefault();
                e.stopPropagation();


                selectedCells.forEach(
                    function(cell) {

                        /*
                         * Kosongkan isi cell.
                         */

                        cell.innerHTML = '';


                        const row =
                            cell.closest('tr');

                        if (!row) return;


                        /*
                         * HARGA
                         */

                        if (
                            cell.classList.contains(
                                'harga'
                            )
                        ) {

                            row.dataset.baseHarga = '0';
                            row.dataset.baseTotal = '0';

                            const total =
                                row.querySelector(
                                    '.total'
                                );

                            if (total) {
                                total.innerText = '0';
                            }

                        }

                    }
                );


                /*
                 * Hitung ulang semua row.
                 */

                document
                    .querySelectorAll(
                        '#spkItemsBody tr.spk-rowa, #spkItemsBody tr.extra-row'
                    )
                    .forEach(function(row) {

                        if (
                            typeof hitungTotal ===
                            'function'
                        ) {

                            hitungTotal(row);

                        }

                    });


                if (
                    typeof hitungGrandTotal ===
                    'function'
                ) {

                    hitungGrandTotal();

                }


                /*
                 * Hilangkan selection.
                 */

                clearCellSelection();

                selectionStartCell = null;
                selectionEndCell = null;


                /*
                 * Jangan sampai browser
                 * melakukan Backspace kedua.
                 */

                return false;

            },
            true
        );


        /*
        |--------------------------------------------------------------------------
        | KLIK DI LUAR TABLE
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'mousedown',
            function(e) {

                if (
                    !e.target.closest(
                        '#spkItemsBody'
                    )
                ) {

                    clearCellSelection();

                    selectionStartCell = null;
                    selectionEndCell = null;

                }

            }
        );

    })();
    /* =========================================
       DATE PICKER HELPER
       ========================================= */
    (function() {
        function pad2(val) {
            return String(val).padStart(2, '0');
        }

        function isoToDisplay(iso) {
            if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return '';
            const parts = iso.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0].slice(-2);
        }

        function displayToIso(display) {
            if (!display) return '';
            const m = String(display).trim().match(/^(\d{2})\/(\d{2})\/(\d{2})$/);
            if (!m) return '';
            let year = Number(m[3]) + (Number(m[3]) >= 70 ? 1900 : 2000);
            return year + '-' + pad2(m[2]) + '-' + pad2(m[1]);
        }

        window.openSpkDatePicker = function(displayInput) {
            const wrap = displayInput.closest('.spk-date-wrap');
            const picker = wrap?.querySelector('.spk-date-picker');
            if (!picker) return;
            try {
                if (typeof picker.showPicker === 'function') {
                    picker.showPicker();
                    return;
                }
            } catch (err) {}
            picker.style.pointerEvents = 'auto';
            picker.click();
            picker.style.pointerEvents = 'none';
        };

        document.addEventListener('change', function(event) {
            const picker = event.target.closest('.spk-date-picker');
            if (!picker) return;
            const wrap = picker.closest('.spk-date-wrap');
            const display = wrap?.querySelector('.spk-date-display');
            if (!display) return;
            display.value = isoToDisplay(picker.value);
        });

        window.getSpkDateValue = function(selector) {
            const picker = document.querySelector(selector + ' .spk-date-picker');
            if (picker?.value) return picker.value;
            const display = document.querySelector(selector + ' .spk-date-display');
            return display ? displayToIso(display.value) : '';
        };
    })();

    /* =========================================================
       PAYMENT AMOUNT FORMATTER
       Format Indonesia: 120000 -> 120.000
       - Berlaku saat mengetik
       - Berlaku saat row pertama kali tampil
       - Berlaku juga untuk row baru dari Add Row
       - Nilai yang dikirim tetap angka tanpa titik
       - Tidak mengubah fungsi Payment Request / Summary
       ========================================================= */
    (function() {
        function digitsOnly(value) {
            return String(value ?? '').replace(/[^0-9]/g, '');
        }

        function formatPaymentAmount(value) {
            const digits = digitsOnly(value);
            if (!digits) return '';
            return new Intl.NumberFormat('id-ID').format(Number(digits));
        }

        function setCaretByDigitPosition(el, digitPosition) {
            const walker = document.createTreeWalker(
                el,
                NodeFilter.SHOW_TEXT,
                null
            );

            let node;
            let counted = 0;
            let lastTextNode = null;

            while (node = walker.nextNode()) {
                lastTextNode = node;
                const text = node.textContent || '';

                for (let i = 0; i < text.length; i++) {
                    if (/\d/.test(text[i])) {
                        counted++;
                    }

                    if (counted >= digitPosition) {
                        const range = document.createRange();
                        range.setStart(node, i + 1);
                        range.collapse(true);

                        const sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        return;
                    }
                }
            }

            const range = document.createRange();
            if (lastTextNode) {
                range.selectNodeContents(lastTextNode);
                range.collapse(false);
            } else {
                range.selectNodeContents(el);
                range.collapse(false);
            }

            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }

        function formatPaymentCell(cell, keepCaret = true) {
            if (!cell) return;

            const currentText = cell.innerText || '';
            const rawDigits = digitsOnly(currentText);

            let digitPosition = rawDigits.length;

            if (keepCaret) {
                const selection = window.getSelection();
                if (selection && selection.rangeCount) {
                    const range = selection.getRangeAt(0);

                    if (cell.contains(range.startContainer)) {
                        const beforeRange = range.cloneRange();
                        beforeRange.selectNodeContents(cell);
                        beforeRange.setEnd(
                            range.startContainer,
                            range.startOffset
                        );

                        const beforeText = beforeRange.toString();
                        digitPosition = digitsOnly(beforeText).length;
                    }
                }
            }

            const formatted = formatPaymentAmount(currentText);

            if (cell.innerText !== formatted) {
                cell.innerText = formatted;
            }

            if (keepCaret && document.activeElement === cell) {
                setCaretByDigitPosition(cell, digitPosition);
            }
        }

        function formatAllPaymentAmounts() {
            document.querySelectorAll('.payment-row .total-amount').forEach(cell => {
                formatPaymentCell(cell, false);
            });
        }

        // Saat mengetik: langsung tampil 1.000 / 10.000 / 120.000 / dst.
        document.addEventListener('input', function(e) {
            const cell = e.target.closest('.payment-row .total-amount');
            if (!cell) return;

            formatPaymentCell(cell, true);
        }, true);

        // Saat paste angka mentah.
        document.addEventListener('paste', function(e) {
            const cell = e.target.closest('.payment-row .total-amount');
            if (!cell) return;

            setTimeout(function() {
                formatPaymentCell(cell, true);
                updatePaymentSummary();
            }, 0);
        }, true);

        // Saat keluar dari cell, pastikan tampilannya rapi.
        document.addEventListener('blur', function(e) {
            const cell = e.target.closest('.payment-row .total-amount');
            if (!cell) return;

            formatPaymentCell(cell, false);
            updatePaymentSummary();
        }, true);

        // Format data payment yang sudah ada saat halaman pertama kali dibuka.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', formatAllPaymentAmounts);
        } else {
            formatAllPaymentAmounts();
        }

        // Bisa dipanggil setelah row Payment Request baru dibuat.
        window.formatPaymentAmountCell = formatPaymentCell;
        window.formatAllPaymentAmounts = formatAllPaymentAmounts;
    })();

    // Initial Calculations on load
    setTimeout(() => {
        document.querySelectorAll('.spk-table tr.spk-rowa, .spk-table tr.extra-row').forEach(r => hitungTotal(
            r));
        if (window.refreshPpnState) window.refreshPpnState();
        updatePaymentSummary();
    }, 200);
    // pusher 
    /* =========================================================
   REALTIME MOUSE CURSOR - PUSHER
   ========================================================= */

    (function() {

        'use strict';

        const spkId =
            document.getElementById('spk_id')?.value;

        if (!spkId) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        const currentUserId =
            @json(auth()->id());

        const currentUserName =
            @json(auth()->user()->name ?? 'User');


        /*
        |--------------------------------------------------------------------------
        | PUSHER
        |--------------------------------------------------------------------------
        */

        const pusherKey =
            @json(config('broadcasting.connections.pusher.key'));

        const pusherCluster =
            @json(config('broadcasting.connections.pusher.options.cluster'));


        if (!pusherKey) {

            console.warn(
                '[SPK Cursor] Pusher key belum tersedia.'
            );

            return;
        }


        const pusher =
            new Pusher(
                pusherKey, {
                    cluster: pusherCluster || 'ap1',

                    forceTLS: true,

                    authEndpoint: @json(route('pusher.auth')),

                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.getAttribute('content') || ''
                        }
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | CHANNEL PER SPK
        |--------------------------------------------------------------------------
        */

        const channelName =
            'presence-spk-' + spkId;


        const channel =
            pusher.subscribe(channelName);

        pusher.connection.bind('connected', function() {
            console.log(
                '[PUSHER] Connected:',
                pusher.connection.socket_id
            );
        });

        pusher.connection.bind('error', function(err) {
            console.error(
                '[PUSHER] Connection error:',
                err
            );
        });

        channel.bind('pusher:subscription_succeeded', function(members) {

            console.log(
                '[PUSHER] Presence connected'
            );

            console.log(
                '[PUSHER] Members:',
                members.count
            );

            members.each(function(member) {

                console.log(
                    '[PUSHER] Member:',
                    member.id,
                    member.info
                );

            });

        });
        /*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

        const container =
            document.getElementById(
                'spkRemoteCursors'
            );


        if (!container) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CURSOR STORAGE
        |--------------------------------------------------------------------------
        */

        const remoteCursors = {};


        /*
        |--------------------------------------------------------------------------
        | CREATE CURSOR
        |--------------------------------------------------------------------------
        */

        function createCursor(
            userId,
            name
        ) {

            const id =
                'spk-remote-cursor-' + userId;


            let cursor =
                document.getElementById(id);


            if (cursor) {
                return cursor;
            }


            cursor =
                document.createElement('div');


            cursor.id = id;

            cursor.className =
                'spk-remote-cursor';


            const arrow =
                document.createElement('div');

            arrow.className =
                'spk-remote-cursor-arrow';


            const label =
                document.createElement('div');

            label.className =
                'spk-remote-cursor-name';

            label.textContent =
                name || 'User';


            cursor.appendChild(arrow);

            cursor.appendChild(label);


            container.appendChild(cursor);


            remoteCursors[userId] = {
                element: cursor,
                lastMove: Date.now(),
                x: 0,
                y: 0
            };


            return cursor;
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE CURSOR
        |--------------------------------------------------------------------------
        */

        function removeCursor(userId) {

            const data =
                remoteCursors[userId];


            if (!data) {
                return;
            }


            data.element.remove();


            delete remoteCursors[userId];
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE CURSOR
        |--------------------------------------------------------------------------
        */

        function updateCursor(data) {

            if (!data) {
                return;
            }


            const userId =
                String(data.user_id);


            /*
            | Jangan tampilkan cursor sendiri
            */

            if (
                userId ===
                String(currentUserId)
            ) {
                return;
            }


            const cursor =
                createCursor(
                    userId,
                    data.name
                );


            const state =
                remoteCursors[userId];


            if (!state) {
                return;
            }


            const x =
                Number(data.x);


            const y =
                Number(data.y);


            if (
                !Number.isFinite(x) ||
                !Number.isFinite(y)
            ) {
                return;
            }


            state.x = x;

            state.y = y;

            state.lastMove =
                Date.now();


            cursor.style.left =
                x + 'px';

            cursor.style.top =
                y + 'px';


            cursor.classList.remove(
                'is-idle'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SEND CURSOR
        |--------------------------------------------------------------------------
        |
        | 150ms = sekitar 6-7 event/detik.
        |
        | Tidak dikirim kalau mouse hanya bergerak
        | sedikit.
        */

        let lastSend = 0;

        let lastX = null;

        let lastY = null;


        const SEND_INTERVAL = 150;

        const MIN_DISTANCE = 5;


        document.addEventListener(
            'mousemove',
            function(event) {

                const now =
                    Date.now();


                if (
                    now - lastSend <
                    SEND_INTERVAL
                ) {
                    return;
                }


                const x =
                    event.clientX;


                const y =
                    event.clientY;


                if (
                    lastX !== null &&
                    lastY !== null
                ) {

                    const dx =
                        x - lastX;

                    const dy =
                        y - lastY;


                    const distance =
                        Math.sqrt(
                            dx * dx +
                            dy * dy
                        );


                    if (
                        distance <
                        MIN_DISTANCE
                    ) {
                        return;
                    }
                }


                lastX = x;

                lastY = y;

                lastSend = now;


                /*
                | Client event.
                | Tidak melewati Laravel.
                */

                try {
                    console.log('[PUSHER DEBUG]', {
                        connection: pusher.connection.state,
                        channel: channelName,
                        user: currentUserId,
                        x: x,
                        y: y
                    });
                    channel.trigger(
                        'client-spk-cursor', {
                            user_id: currentUserId,

                            name: currentUserName,

                            x: x,

                            y: y
                        }
                    );

                } catch (error) {

                    console.warn(
                        '[SPK Cursor]',
                        error
                    );

                }

            }, {
                passive: true
            }
        );


        /*
        |--------------------------------------------------------------------------
        | RECEIVE CURSOR
        |--------------------------------------------------------------------------
        */

        channel.bind(
            'client-spk-cursor',
            function(data) {

                console.log(
                    '[PUSHER] Cursor received:',
                    data
                );

                updateCursor(data);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MEMBER ADDED
        |--------------------------------------------------------------------------
        */

        channel.bind(
            'pusher:member_added',
            function(member) {

                /*
                | Tidak perlu membuat cursor.
                | Cursor dibuat ketika user mulai bergerak.
                */

                console.log(
                    '[SPK Cursor] User masuk:',
                    member.info?.name ||
                    member.id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MEMBER REMOVED
        |--------------------------------------------------------------------------
        */

        channel.bind(
            'pusher:member_removed',
            function(member) {

                removeCursor(
                    String(member.id)
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | IDLE CURSOR
        |--------------------------------------------------------------------------
        |
        | Kalau tidak bergerak 5 detik,
        | cursor dibuat sedikit transparan.
        */

        setInterval(
            function() {

                const now =
                    Date.now();


                Object.keys(
                    remoteCursors
                ).forEach(
                    function(userId) {

                        const state =
                            remoteCursors[
                                userId
                            ];


                        if (!state) {
                            return;
                        }


                        if (
                            now -
                            state.lastMove >
                            5000
                        ) {

                            state.element.classList.add(
                                'is-idle'
                            );

                        }

                    }
                );

            },
            1000
        );




        /*
        |--------------------------------------------------------------------------
        | LIVE MESSAGE / TYPING
        |--------------------------------------------------------------------------
        |
        | Ctrl + Shift + Z membuka kotak input.
        | Setiap perubahan teks dikirim melalui client event Pusher
        | ke user lain yang sedang berada di channel SPK yang sama.
        | Cursor existing TIDAK disentuh.
        */
        const liveMessageModal =
            document.getElementById('spkLiveMessageModal');

        const liveMessageInput =
            document.getElementById('spkLiveMessageInput');

        const liveMessageClose =
            document.getElementById('spkLiveMessageClose');

        const liveMessageSend =
            document.getElementById('spkLiveMessageSend');

        const liveMessageLive =
            document.getElementById('spkLiveMessageLive');

        const liveMessageLiveName =
            document.getElementById('spkLiveMessageLiveName');

        const liveMessageLiveBody =
            document.getElementById('spkLiveMessageLiveBody');

        let liveMessageTypingTimer = null;
        let liveMessageLastSent = '';
        let liveMessageLastSentAt = 0;
        let liveMessageRemoteTimer = null;

        const LIVE_MESSAGE_SEND_INTERVAL = 120;
        const LIVE_MESSAGE_MAX_LENGTH = 1000;

        function escapeLiveMessage(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openLiveMessage() {
            if (!liveMessageModal || !liveMessageInput) return;

            liveMessageModal.classList.add('show');
            liveMessageModal.setAttribute('aria-hidden', 'false');

            setTimeout(function() {
                liveMessageInput.focus();
            }, 30);
        }

        function closeLiveMessage(clearInput = true) {
            if (!liveMessageModal) return;

            liveMessageModal.classList.remove('show');
            liveMessageModal.setAttribute('aria-hidden', 'true');

            if (clearInput && liveMessageInput) {
                liveMessageInput.value = '';
            }

            liveMessageLastSent = '';
        }

        function sendLiveMessageTyping(force = false) {
            if (!liveMessageInput || !channel) return;

            let message =
                liveMessageInput.value || '';

            message = message
                .replace(/\r\n/g, '\n')
                .slice(0, LIVE_MESSAGE_MAX_LENGTH);

            if (liveMessageInput.value !== message) {
                liveMessageInput.value = message;
            }

            const now = Date.now();

            if (!force && now - liveMessageLastSentAt < LIVE_MESSAGE_SEND_INTERVAL) {
                clearTimeout(liveMessageTypingTimer);
                liveMessageTypingTimer = setTimeout(function() {
                    sendLiveMessageTyping(false);
                }, LIVE_MESSAGE_SEND_INTERVAL - (now - liveMessageLastSentAt));
                return;
            }

            if (!force && message === liveMessageLastSent) {
                return;
            }

            try {
                channel.trigger(
                    'client-spk-live-message', {
                        spk_id: String(spkId),
                        user_id: String(currentUserId),
                        name: currentUserName,
                        message: message,
                        typing: message.length > 0,
                        timestamp: now
                    }
                );

                liveMessageLastSent = message;
                liveMessageLastSentAt = now;
            } catch (error) {
                console.warn('[PUSHER Live Message]', error);
            }
        }

        function sendLiveMessageStopped() {
            if (!channel) return;

            try {
                channel.trigger(
                    'client-spk-live-message', {
                        spk_id: String(spkId),
                        user_id: String(currentUserId),
                        name: currentUserName,
                        message: '',
                        typing: false,
                        timestamp: Date.now()
                    }
                );
            } catch (error) {
                console.warn('[PUSHER Live Message]', error);
            }
        }

        function showRemoteLiveMessage(data) {
            if (!data) return;

            const userId =
                String(data.user_id ?? '');

            if (!userId || userId === String(currentUserId)) {
                return;
            }

            if (String(data.spk_id ?? spkId) !== String(spkId)) {
                return;
            }

            const message =
                String(data.message ?? '')
                .slice(0, LIVE_MESSAGE_MAX_LENGTH);

            if (!data.typing || !message.length) {
                if (liveMessageRemoteTimer) {
                    clearTimeout(liveMessageRemoteTimer);
                }

                if (liveMessageLive) {
                    liveMessageLive.classList.remove('show');
                }

                return;
            }

            if (liveMessageLiveName) {
                liveMessageLiveName.textContent =
                    data.name || 'User';
            }

            if (liveMessageLiveBody) {
                liveMessageLiveBody.textContent =
                    message;
            }

            if (liveMessageLive) {
                liveMessageLive.classList.add('show');
            }

            if (liveMessageRemoteTimer) {
                clearTimeout(liveMessageRemoteTimer);
            }

            liveMessageRemoteTimer = setTimeout(function() {
                if (liveMessageLive) {
                    liveMessageLive.classList.remove('show');
                }
            }, 1800);
        }

        /*
        | RECEIVE LIVE MESSAGE
        */
        channel.bind(
            'client-spk-live-message',
            function(data) {
                console.log(
                    '[PUSHER] Live message received:',
                    data
                );

                showRemoteLiveMessage(data);
            }
        );

        /*
        | SHORTCUT CTRL + SHIFT + Z
        */
        document.addEventListener('keydown', function(event) {
            if (
                event.ctrlKey &&
                event.shiftKey &&
                event.key.toLowerCase() === 'z'
            ) {
                event.preventDefault();
                event.stopPropagation();
                openLiveMessage();
            }

            if (
                event.key === 'Escape' &&
                liveMessageModal?.classList.contains('show')
            ) {
                event.preventDefault();
                closeLiveMessage();
                sendLiveMessageStopped();
            }
        }, true);

        /*
        | INPUT REALTIME
        */
        liveMessageInput?.addEventListener('input', function() {
            sendLiveMessageTyping(false);
        });

        /*
        | SEND BUTTON
        |
        | Pesan terakhir dikirim lagi sebagai final message.
        | User lain sudah melihatnya realtime saat mengetik.
        */
        liveMessageSend?.addEventListener('click', function() {
            sendLiveMessageTyping(true);
            sendLiveMessageStopped();
            closeLiveMessage();
        });

        liveMessageClose?.addEventListener('click', function() {
            sendLiveMessageStopped();
            closeLiveMessage();
        });

        liveMessageModal?.addEventListener('click', function(event) {
            if (event.target === liveMessageModal) {
                sendLiveMessageStopped();
                closeLiveMessage();
            }
        });

        /*
        | Saat window kehilangan fokus, hentikan indikator typing.
        */
        window.addEventListener('blur', function() {
            if (liveMessageModal?.classList.contains('show')) {
                sendLiveMessageStopped();
            }
        });


        /*
        |--------------------------------------------------------------------------
        | CLEANUP
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'beforeunload',
            function() {

                try {
                    pusher.unsubscribe(
                        channelName
                    );
                } catch (e) {}

            }
        );


    })();
</script>
@endsection
