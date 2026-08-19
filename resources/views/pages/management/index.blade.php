@extends('master.master')

@section('title', 'Production Monitoring')

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

        <style>
            /* =========================================================
           MONITORING FILTER / TOOLBAR
           ========================================================= */

            .mn-filter {
                background: #fff;
                border: 1px solid #edf0f4;
                border-radius: 18px;
                padding: 18px;
                box-shadow: 0 4px 20px rgba(15, 23, 42, .045);
            }

            .mn-toolbar {
                display: grid;
                grid-template-columns: minmax(280px, 1fr) auto auto auto;
                align-items: end;
                gap: 14px;
            }

            .mn-field-label {
                height: 18px;
                margin: 0 0 6px 2px;
                color: #94a3b8;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .55px;
            }

            .mn-search {
                min-width: 0;
            }

            .mn-search-box {
                height: 44px;
                display: flex;
                align-items: center;
                padding: 0 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 11px;
                transition: .18s ease;
            }

            .mn-search-box:focus-within {
                background: #fff;
                border-color: #cbd5e1;
                box-shadow: 0 0 0 3px rgba(100, 116, 139, .07);
            }

            .mn-search-box>i {
                margin-right: 9px;
                color: #94a3b8;
                font-size: 12px;
            }

            .mn-search-box input {
                width: 100%;
                min-width: 0;
                border: 0;
                outline: 0;
                background: transparent;
                color: #1e293b;
                font-size: 13px;
            }

            .mn-search-box input::placeholder {
                color: #94a3b8;
            }

            .mn-search-clear {
                width: 25px;
                height: 25px;
                flex: 0 0 25px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: 7px;
                background: #e2e8f0;
                color: #64748b;
                cursor: pointer;
            }

            .mn-search-help {
                margin: 5px 0 0 2px;
                color: #94a3b8;
                font-size: 10px;
            }

            .mn-search-help i {
                margin-right: 3px;
            }

            /* =========================================================
                   AJAX FILTER / SEARCH
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
                background: rgba(248, 250, 252, .45);
                backdrop-filter: blur(2px);
                pointer-events: none;
            }

            .mn-ajax-loader-card {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #fff;
                color: #475569;
                box-shadow: 0 8px 30px rgba(15, 23, 42, .10);
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
                border-radius: 11px;
                background: #fff7f7;
                color: #b91c1c;
                font-size: 11px;
            }

            .mn-brand-btn:focus-visible,
            .mn-sort-btn:focus-visible,
            .btn-mn-filter:focus-visible,
            .btn-mn-reset:focus-visible,
            .mn-search-clear:focus-visible {
                outline: 2px solid #94a3b8;
                outline-offset: 2px;
            }

            .mn-brand-btn {
                user-select: none;
            }

            .mn-sort-value {
                min-width: 30px;
                text-align: center;
                letter-spacing: .25px;
            }

            .mn-sort-btn.is-loading,
            .mn-brand-btn.is-loading {
                pointer-events: none;
                opacity: .65;
            }

            .mn-sort-btn.is-loading .mn-sort-icon {
                animation: mnSpin .55s linear infinite;
            }

            @keyframes mnSpin {
                to {
                    transform: rotate(360deg);
                }
            }

            @media (max-width: 576px) {
                .mn-toolbar {
                    grid-template-columns: 1fr 1fr;
                }

                .mn-brand-group {
                    display: flex;
                }

                .mn-brand-btn {
                    flex: 1;
                }

                .mn-sort-btn {
                    width: 100%;
                }

                .mn-actions {
                    margin-top: 1px;
                }

                .mn-search-help {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }

            @media (max-width: 390px) {
                .mn-filter {
                    padding: 11px;
                }

                .mn-toolbar {
                    gap: 8px;
                }

                .mn-field-label {
                    font-size: 9px;
                }

                .mn-brand-btn {
                    padding-left: 5px;
                    padding-right: 5px;
                    font-size: 10px;
                }

                .mn-sort-btn,
                .btn-mn-filter,
                .btn-mn-reset,
                .mn-search-box {
                    height: 42px;
                }
            }

            .mn-brand-filter,
            .mn-sort,
            .mn-actions {
                flex-shrink: 0;
            }

            .mn-brand-group {
                height: 44px;
                display: inline-flex;
                align-items: center;
                padding: 3px;
                border: 1px solid #e2e8f0;
                border-radius: 11px;
                background: #f8fafc;
            }

            .mn-brand-btn {
                height: 36px;
                min-width: 48px;
                padding: 0 12px;
                border: 0;
                border-radius: 8px;
                background: transparent;
                color: #64748b;
                font-size: 11px;
                font-weight: 700;
                cursor: pointer;
                transition: .18s ease;
            }

            .mn-brand-btn:hover {
                color: #334155;
                background: #fff;
            }

            .mn-brand-btn.active {
                background: #fff;
                color: #1e293b;
                box-shadow: 0 1px 4px rgba(15, 23, 42, .10);
            }

            .mn-sort-btn {
                height: 44px;
                min-width: 88px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 0 11px;
                border: 1px solid #e2e8f0;
                border-radius: 11px;
                background: #f8fafc;
                color: #475569;
                font-size: 11px;
                font-weight: 700;
                cursor: pointer;
                transition: .18s ease;
            }

            .mn-sort-btn:hover {
                background: #fff;
                border-color: #cbd5e1;
                color: #1e293b;
            }

            .mn-sort-icon {
                width: 23px;
                height: 23px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 7px;
                background: #e2e8f0;
                font-size: 9px;
            }

            .mn-actions {
                display: flex;
                align-items: end;
                gap: 7px;
            }

            .btn-mn-filter {
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 0 17px;
                border: 0;
                border-radius: 11px;
                background: #1e293b;
                color: #fff;
                font-size: 11px;
                font-weight: 700;
                cursor: pointer;
                transition: .18s ease;
            }

            .btn-mn-filter:hover {
                background: #0f172a;
                transform: translateY(-1px);
            }

            .btn-mn-reset {
                width: 44px;
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #e2e8f0;
                border-radius: 11px;
                background: #fff;
                color: #64748b;
                text-decoration: none;
                transition: .18s ease;
            }

            .btn-mn-reset:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
                color: #334155;
            }


            /* =========================================================
           RESPONSIVE FILTER
           ========================================================= */

            @media (max-width: 992px) {
                .mn-toolbar {
                    grid-template-columns: minmax(220px, 1fr) auto auto;
                }

                .mn-search {
                    grid-column: 1 / -1;
                }
            }

            @media (max-width: 576px) {
                .mn-filter {
                    padding: 13px;
                    border-radius: 15px;
                }

                .mn-toolbar {
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                }

                .mn-search {
                    grid-column: 1 / -1;
                }

                .mn-brand-filter,
                .mn-sort {
                    min-width: 0;
                }

                .mn-brand-group,
                .mn-sort-btn {
                    width: 100%;
                }

                .mn-brand-btn {
                    flex: 1;
                    min-width: 0;
                    padding: 0 7px;
                }

                .mn-actions {
                    grid-column: 1 / -1;
                    width: 100%;
                }

                .btn-mn-filter {
                    flex: 1;
                }
            }


            /* =========================================================
           EXISTING TABLE / CONTENT STYLES
           ========================================================= */

            body {
                background: #f5f7fb;
            }

            .item-col {
                /* width:150px; */
                /* min-width:150px; */
                /* max-width:150px; */
            }

            .item-link {
                display: inline-block;
                width: 120px;
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mn-table {
                min-width: 1100px;
                table-layout: fixed;
            }

            /* Kolom Qty */
            .qty-col {
                width: 55px;
                min-width: 55px;
                max-width: 55px;
                text-align: center;
            }

            /* Kolom IN & PASS */
            .status-col {
                width: 45px;
                min-width: 45px;
                max-width: 45px;
                padding-left: 4px !important;
                padding-right: 4px !important;
                text-align: center;
            }

            .mn-table th,
            .mn-table td {
                white-space: nowrap;
            }

            .product-image {

                width: 65px;
                height: 65px;

            }

            .mn-table thead th {

                position: sticky;
                top: 0;
                z-index: 5;

            }

            .item-name {
                display: inline-block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 220px;
            }

            /* Tablet */
            @media (max-width:992px) {

                .item-name {
                    max-width: 140px;
                    font-size: 13px;
                }

            }

            /* Mobile */
            @media (max-width:768px) {

                .item-name {
                    max-width: 90px;
                    font-size: 12px;
                }

            }

            /* iPhone Portrait */
            @media (max-width:576px) {

                .item-name {
                    max-width: 70px;
                    font-size: 11px;
                }

            }

            .mn-card {
                border: none;
                /* border-radius:20px; */
                overflow: hidden;
                /* box-shadow:0 6px 25px rgba(0,0,0,0.06); */
                background: #fff;
            }

            .mn-header {
                /* background:linear-gradient(135deg,#243b55,#141e30); */
                /* color:white; */
                padding: 18px 24px;
            }

            .mn-header h5 {
                margin: 0;
                font-weight: 700;
                letter-spacing: .5px;
            }

            .mn-table {
                margin-bottom: 0;
            }

            .mn-table thead tr:first-child {
                background: #2c3e50;
                color: white;
            }

            .mn-table thead tr:nth-child(2) {
                background: #ecf0f1;
                color: #2c3e50;
            }

            .mn-table th {
                padding: 6px 4px;
                font-size: 11px;
            }

            .mn-table td {
                padding: 5px 4px;
                font-size: 12px;
            }

            .mn-table tbody tr {
                transition: .2s;
            }

            .mn-table tbody tr:hover {
                background: #f8fafc;
            }


            .product-image {
                width: 78px;
                height: 78px;
                object-fit: cover;
                border-radius: 16px;
                border: 1px solid #e5e7eb;
                background: white;
                padding: 4px;
            }

            .item-link {
                font-weight: 700;
                color: #2563eb;
                text-decoration: none;
                transition: .2s;
            }

            .item-link:hover {
                color: #1d4ed8;
                text-decoration: underline;
            }

            .qty-badge {
                background: #eef2ff;
                /* color:#4338ca; */
                /* font-weight:700; */
                border-radius: 999px;
                padding: 6px 14px;
                display: inline-block;
                min-width: 55px;
                text-align: center;
            }

            .pass-box {
                /* color:#16a34a; */
                /* font-weight:700; */
                font-size: 15px;
            }

            .reject-box {
                /* color:#dc2626; */
                /* font-weight:700; */
                font-size: 15px;
            }

            .mn-empty {
                border-radius: 16px;
                padding: 40px;
                text-align: center;
                background: white;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .item-name {
                display: inline-block;
                /* max-width:250px; */
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .mn-filter label {
                font-size: 13px;
            }

            .mn-filter .form-control,
            .mn-filter .form-select {

                height: 46px;
                border-radius: 12px;

            }

            .mn-filter .input-group-text {

                border-radius: 12px 0 0 12px;

            }

            .mn-filter .form-control {

                border-radius: 0 12px 12px 0;

            }

            .mn-filter .btn {

                height: 46px;
                border-radius: 12px;
            }
               .mn-erp-page {
        --mn-primary: #2563eb;
        --mn-primary-hover: #1d4ed8;
        --mn-success: #16a34a;
        --mn-danger: #dc2626;
        --mn-text: #172033;
        --mn-muted: #667085;
        --mn-border: #e4e7ec;
        --mn-soft: #f8fafc;
        --mn-blue-soft: #eff6ff;
        color: var(--mn-text);
        font-size: 11px;
        padding: 5px 8px 25px;
        }

        .mn-erp-page .mn-filter,
        .mn-erp-page .mn-card,
        .mn-erp-page .mn-empty {
        background: #fff;
        border: 1px solid var(--mn-border);
        border-radius: 8px;
        box-shadow: 0 1px 5px rgba(16, 24, 40, .035);
        }

        .mn-erp-page .mn-filter {
        padding: 12px;
        margin-bottom: 10px !important;
        }

        .mn-erp-page .mn-toolbar {
        gap: 9px;
        }

        .mn-erp-page .mn-field-label {
        color: var(--mn-muted);
        font-size: 8.5px;
        font-weight: 750;
        letter-spacing: .05em;
        margin-bottom: 4px;
        }

        .mn-erp-page .mn-search-box,
        .mn-erp-page .mn-brand-group,
        .mn-erp-page .mn-sort-btn,
        .mn-erp-page .btn-mn-reset {
        height: 36px;
        border-radius: 6px;
        border-color: var(--mn-border);
        }

        .mn-erp-page .mn-search-box {
        padding: 0 9px;
        background: #fff;
        }

        .mn-erp-page .mn-search-box input {
        font-size: 10px;
        }

        .mn-erp-page .mn-search-help {
        margin-top: 3px;
        font-size: 8px;
        color: #98a2b3;
        }

        .mn-erp-page .mn-brand-group {
        padding: 2px;
        background: var(--mn-soft);
        }

        .mn-erp-page .mn-brand-btn {
        height: 30px;
        min-width: 44px;
        border-radius: 5px;
        padding: 0 9px;
        font-size: 9px;
        }

        .mn-erp-page .mn-brand-btn.active {
        color: var(--mn-primary);
        box-shadow: 0 1px 4px rgba(16, 24, 40, .08);
        }

        .mn-erp-page .mn-sort-btn {
        min-width: 78px;
        padding: 0 8px;
        background: #fff;
        font-size: 9px;
        }

        .mn-erp-page .mn-sort-icon {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        background: var(--mn-blue-soft);
        color: var(--mn-primary);
        font-size: 8px;
        }

        .mn-erp-page .btn-mn-filter {
        height: 36px;
        padding: 0 12px;
        border-radius: 6px;
        background: var(--mn-primary);
        font-size: 9px;
        }

        .mn-erp-page .btn-mn-filter:hover {
        background: var(--mn-primary-hover);
        }

        .mn-erp-page .btn-mn-reset {
        width: 36px;
        background: #fff;
        }

        /* ---------- PO CARD ---------- */
        .mn-erp-page .mn-card {
        overflow: hidden;
        margin-bottom: 10px !important;
        }

        .mn-erp-page .mn-header {
        min-height: 45px;
        padding: 8px 12px;
        background: #fff !important;
        color: var(--mn-text) !important;
        border-bottom: 1px solid #edf0f4;
        }

        .mn-erp-page .mn-header h6 {
        margin: 0;
        font-size: 11.5px;
        line-height: 1.25;
        font-weight: 750;
        letter-spacing: -.01em;
        }

        .mn-erp-page .mn-header h6 span {
        color: var(--mn-muted);
        font-weight: 500;
        }

        .mn-erp-page .btn-toggle-po {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbe2ea;
        border-radius: 5px;
        background: #fff;
        color: #667085;
        font-size: 9px;
        box-shadow: none;
        }

        .mn-erp-page .btn-toggle-po:hover {
        background: var(--mn-soft);
        color: var(--mn-primary);
        }

        /* ---------- MAIN TABLE ---------- */
        .mn-erp-page .po-table {
        border-top: 0;
        }

        .mn-erp-page .mn-table {
        min-width: 1100px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 9px;
        }

        .mn-erp-page .mn-table thead tr:first-child {
        background: #f8fafc;
        color: #344054;
        }

        .mn-erp-page .mn-table thead tr:nth-child(2) {
        background: #fcfcfd;
        color: #667085;
        }

        .mn-erp-page .mn-table thead th {
        padding: 6px 6px;
        border-color: #e8edf2;
        font-size: 8.5px;
        font-weight: 750;
        letter-spacing: .01em;
        vertical-align: middle;
        }

        .mn-erp-page .mn-table tbody td {
        padding: 6px;
        border-color: #edf0f4;
        color: #344054;
        font-size: 9px;
        vertical-align: middle;
        }

        .mn-erp-page .mn-table tbody tr {
        transition: background .15s ease;
        }

        .mn-erp-page .mn-table tbody tr:hover {
        background: #fbfdff;
        }

        .mn-erp-page .mn-table thead th:first-child {
        border-top-left-radius: 5px;
        }

        .mn-erp-page .product-image {
        width: 54px;
        height: 54px;
        padding: 3px;
        border-radius: 6px;
        border: 1px solid #e4e7ec;
        object-fit: cover;
        }

        .mn-erp-page .item-link {
        color: var(--mn-primary);
        font-size: 9.5px;
        font-weight: 700;
        text-decoration: none;
        }

        .mn-erp-page .item-link:hover {
        color: var(--mn-primary-hover);
        text-decoration: underline;
        }

        .mn-erp-page .qty-badge {
        min-width: 38px;
        padding: 4px 7px;
        border-radius: 5px;
        background: var(--mn-blue-soft);
        color: #344054;
        font-size: 9px;
        }

        .mn-erp-page .status-col {
        width: 42px;
        min-width: 42px;
        max-width: 42px;
        padding-left: 3px !important;
        padding-right: 3px !important;
        }

        .mn-erp-page .pass-box,
        .mn-erp-page .reject-box {
        font-size: 10px;
        font-weight: 700;
        }

        /* ---------- EMPTY ---------- */
        .mn-erp-page .mn-empty {
        padding: 30px 15px;
        box-shadow: none;
        }

        .mn-erp-page .mn-empty h5 {
        margin: 0 0 4px;
        font-size: 12px;
        font-weight: 750;
        }

        .mn-erp-page .mn-empty .text-muted {
        font-size: 9px;
        color: #98a2b3 !important;
        }

        /* ---------- ITEM / SPK MODAL ---------- */
        .mn-erp-page + .modal .modal-content,
        .modal .modal-content {
        border: 1px solid var(--mn-border);
        border-radius: 9px;
        overflow: hidden;
        box-shadow: 0 18px 60px rgba(15, 23, 42, .16);
        }

        .mn-erp-page + .modal .modal-header,
        .modal .modal-header {
        min-height: 46px;
        padding: 8px 12px;
        background: #fff !important;
        color: var(--mn-text) !important;
        border-bottom: 1px solid #edf0f4;
        }

        .modal .modal-title {
        margin: 0;
        font-size: 12px;
        font-weight: 750;
        }

        .modal .modal-body {
        padding: 12px;
        background: #fff;
        }

        .modal .modal-footer {
        padding: 8px 12px;
        border-top: 1px solid #edf0f4;
        background: #fff;
        }

        .modal .card {
        border: 1px solid var(--mn-border) !important;
        border-radius: 7px !important;
        box-shadow: 0 1px 4px rgba(16, 24, 40, .03) !important;
        overflow: hidden;
        }

        .modal .card-body {
        padding: 9px !important;
        }

        .modal .badge {
        border-radius: 5px;
        padding: 5px 7px !important;
        font-size: 8px;
        font-weight: 700;
        }

        .modal .table {
        margin-bottom: 0;
        font-size: 9px;
        }

        .modal .table td,
        .modal .table th {
        padding: 5px 6px;
        border-color: #edf0f4;
        vertical-align: middle;
        }

        .modal .border.rounded-4 {
        border-color: var(--mn-border) !important;
        border-radius: 7px !important;
        background: #f8fafc !important;
        padding: 9px !important;
        }

        .modal .product-image {
        width: 58px;
        height: 58px;
        border-radius: 6px;
        }

        .modal .form-control,
        .modal .form-select {
        min-height: 30px;
        height: 30px;
        padding: 4px 7px;
        border: 1px solid #dfe3e8;
        border-radius: 5px;
        color: #344054;
        font-size: 9px;
        box-shadow: none !important;
        }

        .modal .form-control:focus,
        .modal .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .07) !important;
        }

        .modal .btn {
        min-height: 29px;
        height: 29px;
        padding: 0 9px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 650;
        }

        .modal .btn-close {
        width: 26px;
        height: 26px;
        }

        /* ---------- PRICE MODAL ---------- */
        #pricePasswordModal .modal-content {
        border-radius: 9px;
        }

        #pricePasswordModal .modal-header {
        background: #fff !important;
        color: var(--mn-text) !important;
        }

        /* ---------- AJAX LOADER ---------- */
        .mn-erp-page .mn-ajax-loader-card {
        padding: 8px 11px;
        border-radius: 7px;
        border-color: var(--mn-border);
        box-shadow: 0 10px 35px rgba(16, 24, 40, .10);
        font-size: 9px;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 1000px) {
        .mn-erp-page .mn-toolbar {
        gap: 8px;
        }

        .mn-erp-page .modal-dialog {
        max-width: calc(100% - 20px);
        margin: 10px auto;
        }
        }

        @media (max-width: 700px) {
        .mn-erp-page {
        padding: 4px 4px 20px;
        }

        .mn-erp-page .mn-filter {
        padding: 10px;
        border-radius: 7px;
        }

        .mn-erp-page .mn-toolbar {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        }

        .mn-erp-page .mn-search,
        .mn-erp-page .mn-actions {
        grid-column: 1 / -1;
        }

        .mn-erp-page .mn-search-box,
        .mn-erp-page .mn-brand-group,
        .mn-erp-page .mn-sort-btn,
        .mn-erp-page .btn-mn-filter,
        .mn-erp-page .btn-mn-reset {
        height: 34px;
        }

        .mn-erp-page .mn-brand-group,
        .mn-erp-page .mn-sort-btn {
        width: 100%;
        }

        .mn-erp-page .mn-brand-btn {
        flex: 1;
        min-width: 0;
        padding: 0 5px;
        }

        .mn-erp-page .mn-actions {
        width: 100%;
        }

        .mn-erp-page .btn-mn-filter {
        flex: 1;
        }

        .modal .modal-body {
        padding: 9px;
        }

        .modal .modal-dialog {
        max-width: calc(100% - 12px);
        margin: 6px auto;
        }
        }

        @media (max-width: 390px) {
        .mn-erp-page .mn-toolbar {
        gap: 6px;
        }

        .mn-erp-page .mn-field-label {
        font-size: 8px;
        }

        .mn-erp-page .mn-brand-btn,
        .mn-erp-page .mn-sort-btn,
        .mn-erp-page .btn-mn-filter {
        font-size: 8.5px;
        }
        }
        </style>



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
                    $currentBrand = in_array($currentBrand, ['all', 'nw', 'nws']) ? $currentBrand : 'all';

                    $nextSort = $currentSort === 'asc' ? 'desc' : 'asc';
                @endphp

                <form method="GET" action="{{ route('produksi.mn') }}" id="monitoringFilterForm">

                    {{-- STATE --}}
                    <input type="hidden" name="brand" id="monitoringBrand" value="{{ $currentBrand }}">

                    <input type="hidden" name="sort" id="monitoringSort" value="{{ $currentSort }}">

                    <div class="mn-toolbar">

                        {{-- SEARCH --}}
                        <div class="mn-search">

                            <div class="mn-field-label">
                                <span>Pencarian</span>
                            </div>

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
                                No PO atau nama buyer
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
                                        ({{ $po['buyer_name'] }})
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

                            $categories = [
                                'rangka' => 'Rangka',
                                'anyam' => 'Anyam',
                                'unfinish' => 'Unfinish',
                                // 'accessories' => 'Accessories',
                                // 'decor' => 'Decor',
                                // 'ikat' => 'Ikat',
                                'final' => 'Final',
                                'box' => 'Packaging',
                            ];

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
                                                    <img src="{{ $item['item_image'] }}" class="product-image"
                                                        loading="lazy" decoding="async">
                                                @else
                                                    -
                                                @endif

                                            </td>

                                            {{-- QTY --}}
                                            <td class="">

                                                <span class="">
                                                    {{ $item['qty'] }}
                                                </span>

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
                                            @foreach ($categories as $categoryKey => $categoryLabel)
                                                @foreach ($statuses as $statusKey => $status)
                                                    @continue($statusKey == 'out')
                                                    @continue($statusKey == 'reject')

                                                    {{-- Final & Packaging hanya PASS --}}
                                                    @if (in_array($categoryKey, ['final', 'box']) && $statusKey == 'in')
                                                        @continue
                                                    @endif

                                                    @php
                                                        $field = $categoryKey . '_' . $statusKey;
                                                    @endphp

                                                    <td class="text-center">

                                                        <div class="{{ $status['class'] }}">
                                                            {{ $item[$field] ?? 0 }}
                                                        </div>

                                                        @if (isset($item['detail_kategori'][$categoryKey]) && count($item['detail_kategori'][$categoryKey]) > 1)
                                                            <hr class="my-1">

                                                            @foreach ($item['detail_kategori'][$categoryKey] as $jenis => $detail)
                                                                <div style="font-size:9px">

                                                                    {{ str_replace('RANGKA ', 'R. ', $jenis) }}
                                                                    :
                                                                    {{ $detail[$statusKey] ?? 0 }}

                                                                </div>
                                                            @endforeach
                                                        @endif

                                                    </td>
                                                @endforeach
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

                                            @if ($item['item_image'])
                                                <img src="{{ $item['item_image'] }}" class="product-image"
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

                                                    {{-- HEADER --}}
                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div class="d-flex gap-2 flex-wrap">

                                                            <span class="badge bg-primary px-3 py-2">
                                                                {{ strtoupper($spk['jenis_asli']) }}
                                                            </span>

                                                            @if (strtolower($spk['kategori']) != strtolower($spk['jenis_asli']))
                                                                <span class="badge bg-secondary px-3 py-2">
                                                                    {{ strtoupper($spk['jenis_asli']) }}
                                                                </span>
                                                            @endif

                                                        </div>

                                                        <div>

                                                            <span class="badge bg-success px-3 py-2">

                                                                {{ strtoupper($spk['status']) }}
                                                                id [{{ $spk['id'] }}]
                                                            </span>

                                                        </div>

                                                    </div>

                                                    {{-- ROW --}}
                                                    <div class="row">

                                                        {{-- LEFT --}}
                                                        <div class="col-md-8">

                                                            <table class="table table-sm mb-0">

                                                                <tr>

                                                                    <td width="140">

                                                                        Supplier

                                                                    </td>

                                                                    <td>

                                                                        :
                                                                        {{ $spk['supplier'] }}

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        No SPK

                                                                    </td>

                                                                    <td>

                                                                        :

                                                                        <a href="{{ url('spk/edit/' . $spk['id']) }}"
                                                                            class="fw-bold text-primary text-decoration-underline">

                                                                            {{ $spk['no_spk'] }}

                                                                        </a>

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        Qty

                                                                    </td>

                                                                    <td>

                                                                        :
                                                                        {{ $spk['qty'] }}

                                                                    </td>

                                                                </tr>

                                                                <tr>

                                                                    <td>

                                                                        Harga

                                                                    </td>

                                                                    <td>
                                                                        :
                                                                        <span class="price-container"
                                                                            data-price="{{ number_format($spk['harga']) }}">
                                                                            <a href="#"
                                                                                class="show-price text-primary text-decoration-underline">
                                                                                Lihat Harga? Tap disini
                                                                            </a>
                                                                        </span>
                                                                    </td>

                                                                </tr>

                                                            </table>

                                                        </div>

                                                        {{-- RIGHT --}}
                                                        <div class="col-md-4">

                                                            @php

                                                                $kategoriSpk = strtolower($spk['kategori']);

                                                                $hideQcResult =
                                                                    str_contains($kategoriSpk, 'cushion') ||
                                                                    str_contains($kategoriSpk, 'box');

                                                            @endphp

                                                            @unless ($hideQcResult)
                                                                <div class="border rounded-4 p-3 h-100 bg-light">
                                                                    <div class="row">

                                                                        <div class="fw-bold mb-3">

                                                                            QC RESULT

                                                                        </div>
                                                                        <div class="fw-bold mb-3 ml-4">
                                                                            @if (!empty($spk['inspect_schedule_id']))
                                                                                <a href="{{ url(
                                                                                    'qc/laporan-qc?' .
                                                                                        http_build_query([
                                                                                            'detail_po_id' => $spk['detail_po_id'],
                                                                                            'kategori' => $spk['kategori'],
                                                                                        ]),
                                                                                ) }}"
                                                                                    target="_blank"
                                                                                    class="fw-bold text-primary text-decoration-none">

                                                                                    Lihat Laporan

                                                                                </a>
                                                                            @else
                                                                                <span class="text-muted">

                                                                                    Tidak/belum ada inspeksin

                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>


                                                                    {{-- PASSED --}}
                                                                    <div class="d-flex justify-content-between mb-2">

                                                                        <span>

                                                                            Passed

                                                                        </span>

                                                                        <span class="fw-bold text-success">
                                                                            <pre>

{{-- {{ print_r($spk, true) }} --}}

</pre>
                                                                            {{ $spk['passed'] }}

                                                                        </span>

                                                                    </div>

                                                                    {{-- REJECT --}}
                                                                    <div class="d-flex justify-content-between">

                                                                        <span>

                                                                            Rejected

                                                                        </span>

                                                                        <span class="fw-bold text-danger">

                                                                            {{ $spk['rejected'] }}

                                                                        </span>

                                                                    </div>

                                                                </div>
                                                            @endunless

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
                            | NW/NWS, ASC/DESC dan search berjalan tanpa reload halaman.
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
            </script>

        @endsection
