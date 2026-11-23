@extends('master.master')

@section('title', 'Dashboard Pengajuan Tomo')

<style>
    .tomo-page {

        background: #f5f7fb;

        min-height: 85vh;

        padding: 12px 8px;

        color: #172b4d
    }



    .tomo-wrap {

        max-width: 1050px;

        margin: auto
    }



    .tomo-title {

        background: #e7f0ff;

        border-radius: 14px;

        padding: 15px;

        margin-bottom: 10px
    }



    .tomo-title h2 {

        font-size: 21px;

        font-weight: 800;

        margin: 0 0 5px
    }



    .tomo-title p {

        font-size: 13px;

        color: #475569;

        margin: 0;

        line-height: 1.5
    }



    .tomo-sticky {

        position: sticky;

        top: 52px;

        z-index: 90;

        background: #f5f7fb;

        padding: 7px 0
    }



    .tomo-search {

        display: flex;

        gap: 7px
    }



    .tomo-search input {

        min-width: 0;

        flex: 1;

        height: 37px;

        padding: 6px 10px;

        border: 1px solid #cbd5e1;

        border-radius: 9px;

        font-size: 14px
    }



    .tomo-btn {

        background: #1555b2;

        color: white;

        border: 0;

        border-radius: 9px;

        padding: 9px 14px;

        font-weight: 750;

        cursor: pointer
    }



    .tomo-tabs {

        display: flex;

        gap: 7px;

        overflow-x: auto;

        margin: 10px 0 13px
    }



    .tomo-tab {

        border: 1px solid #cbd5e1;

        background: white;

        border-radius: 9px;

        padding: 10px 12px;

        white-space: nowrap;

        font-weight: 700;

        cursor: pointer
    }



    .tomo-tab.active {

        background: #1555b2;

        color: white;

        border-color: #1555b2
    }



    .tomo-po {

        background: white;

        border: 1px solid #dce4ef;

        border-radius: 14px;

        overflow: hidden;

        margin-bottom: 14px
    }



    .tomo-po-head {

        background: #dce8fb;

        padding: 12px 14px;

        display: flex;

        justify-content: space-between;

        align-items: center
    }



    .tomo-po-head strong {

        font-size: 17px
    }



    .tomo-buyer {

        color: #475569;

        font-size: 12px;

        margin-top: 3px
    }



    .tomo-items {

        padding: 9px;

        display: grid;

        gap: 8px
    }



    .tomo-item {

        display: grid;

        grid-template-columns: 86px minmax(0, 1fr);

        gap: 10px;

        padding: 9px;

        border: 1px solid #e2e8f0;

        border-radius: 12px;

        background: white
    }



    .tomo-image {

        width: 86px;

        height: 86px;

        object-fit: contain;

        background: white;

        border: 1px solid #e2e8f0;

        border-radius: 9px
    }



    .tomo-photo {

        border: 0;

        background: none;

        padding: 0;

        cursor: zoom-in
    }



    .tomo-name {

        font-weight: 800;

        font-size: 15px;

        line-height: 1.3;

        overflow-wrap: anywhere
    }



    .tomo-meta {

        font-size: 12px;

        color: #64748b;

        margin-top: 3px
    }



    .tomo-actions {

        display: flex;

        flex-wrap: wrap;

        align-items: center;

        gap: 7px;

        margin-top: 8px
    }



    .tomo-ajukan {

        background: #f59e0b;

        border: 0;

        border-radius: 9px;

        padding: 9px 16px;

        font-weight: 800;

        color: #172033;

        cursor: pointer
    }



    .tomo-detail {

        background: #e9f1ff;

        color: #134d9e;

        border: 1px solid #bad0f4;

        border-radius: 9px;

        padding: 8px 12px;

        font-weight: 800;

        cursor: pointer
    }



    .tomo-qty {

        margin-left: auto;

        background: #e6f4e4;

        color: #22642b;

        padding: 6px 10px;

        border-radius: 9px;

        text-align: center;

        font-size: 11px
    }



    .tomo-qty b {

        display: block;

        font-size: 19px
    }



    .tomo-spk {

        background: #f1f5f9;

        padding: 6px 8px;

        border-radius: 7px;

        margin-top: 6px;

        display: flex;

        justify-content: space-between;

        gap: 8px;

        font-size: 12px
    }



    .tomo-spk strong {

        color: #166534
    }



    .tomo-status {

        display: inline-block;

        border-radius: 20px;

        padding: 5px 9px;

        font-weight: 800;

        font-size: 12px
    }



    .tomo-status.pending {

        background: #fff3c4;

        color: #8a5100
    }



    .tomo-status.diproses {

        background: #dbeafe;

        color: #1d4ed8
    }



    .tomo-status.disetujui {

        background: #d9f7e2;

        color: #166534
    }



    .tomo-status.ditolak {

        background: #fee2e2;

        color: #b91c1c
    }



    .tomo-status.unknown {

        background: #e2e8f0;

        color: #475569
    }



    .tomo-empty {

        padding: 22px;

        text-align: center;

        color: #64748b;

        background: white;

        border-radius: 12px
    }



    .tomo-modal {

        display: none;

        position: fixed;

        inset: 0;

        z-index: 99999;

        background: #0f172ab8;

        align-items: center;

        justify-content: center;

        padding: 6px
    }



    .tomo-modal.open {

        display: flex
    }



    .tomo-dialog {

        width: min(100%, 720px);

        max-height: calc(100dvh - 12px);

        background: white;

        border-radius: 15px;

        display: flex;

        flex-direction: column;

        overflow: hidden
    }



    .tomo-dialog-head {

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 10px;

        padding: 12px;

        border-bottom: 1px solid #e2e8f0
    }



    .tomo-dialog-head h3 {

        margin: 0;

        font-size: 17px;

        font-weight: 800
    }



    .tomo-x {

        width: 40px;

        height: 40px;

        border: 0;

        background: #eef2f7;

        border-radius: 9px;

        font-size: 24px;

        cursor: pointer
    }



    .tomo-dialog-body {

        overflow: auto;

        padding: 11px;

        min-height: 0
    }



    .tomo-dialog-foot {

        padding: 10px;

        border-top: 1px solid #e2e8f0;

        display: flex;

        gap: 8px;

        justify-content: flex-end
    }



    .tomo-product {

        display: flex;

        gap: 10px;

        align-items: center;

        background: #f1f6fd;

        border-radius: 11px;

        padding: 8px;

        margin-bottom: 12px
    }



    .tomo-product img {

        width: 86px;

        height: 80px;

        object-fit: contain;

        background: white;

        border-radius: 8px;

        cursor: zoom-in
    }



    .tomo-product strong {

        font-size: 14px
    }



    .tomo-product small {

        display: block;

        color: #64748b;

        margin-top: 3px
    }



    .tomo-table {

        width: 100%;

        border-collapse: collapse;

        table-layout: fixed
    }



    .tomo-table th {

        background: #e9eff7;

        font-size: 11px;

        padding: 8px 2px
    }



    .tomo-table td {

        border-bottom: 1px solid #e5e7eb;

        padding: 5px 2px;

        font-size: 12px
    }



    .tomo-table td:first-child {

        font-weight: 750;

        overflow-wrap: anywhere
    }



    .tomo-cost-input {

        width: 100%;

        height: 40px;

        border: 1px solid #cbd5e1;

        border-radius: 7px;

        padding: 4px;

        font-size: 16px;

        text-align: right;

        box-sizing: border-box
    }



    .tomo-result {

        text-align: right;

        font-weight: 750;

        color: #166534;

        overflow-wrap: anywhere
    }



    .tomo-total {

        position: sticky;

        bottom: 0;

        display: flex;

        justify-content: space-between;

        background: #e3f5e6;

        border: 1px solid #bbdfc1;

        border-radius: 10px;

        padding: 12px;

        font-weight: 850;

        margin-top: 9px
    }



    .tomo-note {

        display: block;

        width: 100%;

        min-height: 60px;

        border: 1px solid #cbd5e1;

        border-radius: 9px;

        padding: 10px;

        font-size: 14px;

        box-sizing: border-box;

        margin-top: 6px
    }



    .tomo-history-card {

        border: 1px solid #d7e2f2;

        border-radius: 11px;

        padding: 12px;

        margin: 8px 0;

        background: white
    }



    .tomo-history-card h4 {

        margin: 0 0 7px;

        font-size: 15px
    }



    .tomo-history-card p {

        margin: 4px 0;

        font-size: 13px
    }



    .tomo-alert {

        background: #fff7e6;

        border: 1px solid #fcd98a;

        padding: 10px;

        border-radius: 9px;

        font-size: 12px;

        margin: 9px 0
    }



    .tomo-zoom {

        display: none;

        position: fixed;

        inset: 0;

        background: #0f172af2;

        z-index: 100001;

        align-items: center;

        justify-content: center;

        flex-direction: column;

        gap: 15px;

        padding: 12px
    }



    .tomo-zoom.open {

        display: flex
    }



    .tomo-zoom img {

        max-width: 94vw;

        max-height: 75vh;

        object-fit: contain;

        transform: scale(var(--scale, 1))
    }



    .tomo-zoom button {

        padding: 10px 15px;

        border: 0;

        border-radius: 9px;

        font-weight: 800
    }



    .tomo-history-list {

        display: grid;

        gap: 8px
    }



    .tomo-history-entry {

        border: 1px solid #dbe4ef;

        background: white;

        border-radius: 12px;

        padding: 12px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px
    }



    .tomo-history-entry strong {

        font-size: 14px
    }



    .tomo-history-entry small {

        display: block;

        color: #64748b;

        margin-top: 4px
    }



    .tomo-hidden {

        display: none !important
    }



    .tomo-help {

        font-size: 12px;

        color: #64748b;

        margin-top: 8px
    }



    @media(max-width:500px) {

        .tomo-title h2 {

            font-size: 19px
        }



        .tomo-item {

            grid-template-columns: 75px minmax(0, 1fr)
        }



        .tomo-image {

            width: 75px;

            height: 75px
        }



        .tomo-name {

            font-size: 14px
        }



        .tomo-dialog-body {

            padding: 8px
        }



        .tomo-table td {

            font-size: 11px
        }



        .tomo-table th {

            font-size: 10px
        }



        .tomo-cost-input {

            height: 39px;

            padding: 2px;

            font-size: 15px
        }



        .tomo-result {

            font-size: 10px
        }



        .tomo-product img {

            width: 76px;

            height: 76px
        }

    }



    .tomo-unit {

        width: 100%;

        max-width: 130px;

        margin-top: 5px;

        padding: 5px 6px;

        border: 1px solid #cbd5e1;

        border-radius: 7px;

        background: #f8fafc;

        font-size: 12px;

        color: #334155
    }



    .tomo-unit-label {

        display: block;

        font-size: 10px;

        color: #64748b;

        margin-top: 3px
    }



    @media(max-width:500px) {

        .tomo-unit {

            font-size: 11px;

            padding: 4px 2px
        }

    }



    /* Header tabel kontras dan mudah dibaca di HP */

    .tomo-dialog .tomo-table thead th {

        background: #164b88 !important;

        color: #fff !important;

        font-size: 12px;

        font-weight: 800;

        letter-spacing: .1px;

        border: 1px solid #326ba5;

        padding: 10px 4px;

        text-align: center;

        white-space: nowrap
    }



    .tomo-dialog .tomo-table thead th:first-child {

        text-align: left
    }



    .tomo-stn-input {

        display: block;

        width: 100%;

        height: 40px;

        box-sizing: border-box;

        border: 1px solid #cbd5e1;

        border-radius: 7px;

        padding: 4px 3px;

        font-size: 13px;

        text-align: center;

        color: #172b4d;

        background: #fff
    }



    .tomo-dialog .tomo-table td {

        vertical-align: middle
    }



    @media(max-width:500px) {

        .tomo-dialog .tomo-table thead th {

            font-size: 11px;

            padding: 9px 2px
        }



        .tomo-stn-input {

            height: 39px;

            font-size: 12px;

            padding: 2px
        }



        .tomo-dialog .tomo-table td:first-child {

            font-size: 11px
        }

    }



    /* Tomo full-width: sembunyikan navbar/header dan sidebar bawaan master pada halaman ini saja. */

    body:has(.tomo-page) .main-header,

    body:has(.tomo-page) .main-sidebar,

    body:has(.tomo-page) .navbar.navbar-expand-lg,

    body:has(.tomo-page) .app-header,

    body:has(.tomo-page) .topbar,

    body:has(.tomo-page) .page-header.navbar {

        display: none !important
    }



    body:has(.tomo-page) .content-wrapper,

    body:has(.tomo-page) .main-content,

    body:has(.tomo-page) .app-content,

    body:has(.tomo-page) .page-wrapper {

        margin-left: 0 !important;

        padding-top: 0 !important;

        min-height: 100vh
    }



    .tomo-sticky {

        top: 0 !important
    }



    .tomo-table th {

        background: #194c87 !important;

        color: #fff !important;

        font-weight: 800 !important;

        border: 1px solid #346497 !important
    }



    .tomo-table td {

        border: 1px solid #dbe3ed
    }



    .tomo-table .tomo-stn-input {

        display: block;

        box-sizing: border-box;

        width: 100%;

        height: 40px;

        min-width: 0;

        padding: 3px;

        border: 1px solid #cbd5e1;

        border-radius: 7px;

        text-align: center;

        font-size: 13px;

        background: #fff;

        color: #172b4d
    }



    .tomo-table .tomo-cost-input {

        min-width: 0
    }



    @media(max-width:500px) {

        .tomo-table .tomo-stn-input {

            height: 39px;

            font-size: 12px;

            padding: 2px
        }



        .tomo-table .tomo-result {

            font-size: 10px
        }

    }



    .tomo-search-field {

        position: relative;

        flex: 1;

        min-width: 0
    }



    .tomo-search-field input {

        width: 100%;

        padding-right: 38px !important;

        box-sizing: border-box
    }



    .tomo-search-clear {

        position: absolute;

        right: 6px;

        top: 50%;

        transform: translateY(-50%);

        border: 0;

        border-radius: 50%;

        width: 28px;

        height: 28px;

        background: #e2e8f0;

        color: #334155;

        font-size: 21px;

        line-height: 26px;

        cursor: pointer
    }



    .tomo-search-clear[hidden] {

        display: none !important
    }



    /* Sticky header PO: menempel tepat di bawah pencarian, hanya selama card PO aktif. */

    .tomo-po {

        overflow: visible !important;

        position: relative;

        isolation: isolate
    }



    .tomo-po-head {

        position: sticky !important;

        top: var(--tomo-search-offset, 56px) !important;

        z-index: 40;

        background: #dce8fb;

        box-shadow: 0 2px 5px rgba(15, 35, 70, .12);

        border-radius: 13px 13px 0 0
    }



    .tomo-sticky {

        z-index: 90 !important;

        top: 0 !important
    }



    @media(max-width:600px) {

        .tomo-po-head {

            top: 51px !important
        }

    }

    /* Label riwayat harga SPK untuk artikel yang pernah dibuat */
    .tomo-repeat-note {
        display: inline-block;
        margin-top: 5px;
        padding: 5px 9px;
        border-radius: 7px;
        background: #eaf7ee;
        color: #166534;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* ===== Tomo refined UI (scoped; existing functions untouched) ===== */
    .tomo-page {
        background: #f6f8fc;
        color: #1e293b;
    }

    .tomo-wrap {
        max-width: 1180px;
    }

    .tomo-title {
        background: linear-gradient(115deg, #eff6ff, #f8fbff);
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 3px 15px rgba(15, 35, 75, .035);
    }

    .tomo-title h2 {
        color: #17355f;
        letter-spacing: -.45px;
    }

    .tomo-title p {
        color: #60738c;
    }

    .tomo-sticky {
        background: rgba(246, 248, 252, .97);
        border-radius: 14px;
        padding: 9px 0;
        z-index: 300 !important;
    }

    .tomo-sticky.tomo-search-fixed {
        position: fixed !important;
        top: 0 !important;
        margin: 0 !important;
        padding: 9px 12px !important;
        background: rgba(255, 255, 255, .98);
        border: 1px solid #dbe5f2;
        border-radius: 0 0 14px 14px;
        box-shadow: 0 8px 26px rgba(19, 49, 90, .14);
    }

    .tomo-search {
        gap: 10px;
    }

    .tomo-search-field input {
        height: 44px;
        background: #fff;
        border: 1px solid #d5dfed;
        border-radius: 11px;
        padding-left: 15px;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .tomo-search-field input:focus {
        border-color: #397bd0;
        box-shadow: 0 0 0 3px rgba(57, 123, 208, .12);
    }

    .tomo-search .tomo-btn {
        min-width: 88px;
        border-radius: 11px;
        background: #1959ae;
        box-shadow: 0 3px 8px rgba(25, 89, 174, .12);
    }

    .tomo-tabs {
        gap: 8px;
        padding: 2px 0 4px;
    }

    .tomo-tab {
        border: 1px solid #dbe4f0;
        border-radius: 11px;
        padding: 11px 15px;
        color: #405875;
        transition: background .2s, border-color .2s;
    }

    .tomo-tab.active {
        background: #1959ae;
        border-color: #1959ae;
        box-shadow: 0 4px 12px rgba(25, 89, 174, .15);
    }

    .tomo-po {
        border: 1px solid #dfe7f2;
        border-radius: 15px;
        box-shadow: 0 4px 18px rgba(19, 49, 90, .045);
        background: #fff;
        margin-bottom: 16px;
    }

    .tomo-po-head {
        background: #eaf2fd;
        padding: 14px 16px;
        border-bottom: 1px solid #dce8f8;
    }

    .tomo-po-head strong {
        color: #193e70;
    }

    .tomo-items {
        padding: 12px;
        gap: 10px;
    }

    .tomo-item {
        padding: 13px;
        border: 1px solid #e4ebf4;
        border-radius: 12px;
        transition: border-color .18s, box-shadow .18s;
    }

    .tomo-item:hover {
        border-color: #c7d9f1;
        box-shadow: 0 4px 14px rgba(18, 57, 103, .06);
    }

    .tomo-image {
        border-color: #e0e8f3;
        border-radius: 10px;
    }

    .tomo-name {
        color: #233b5d;
    }

    .tomo-meta {
        color: #73839a;
    }

    .tomo-spk {
        background: #f5f8fc;
        border: 1px solid #e6edf6;
        border-radius: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .tomo-spk strong {
        color: #177247;
    }

    .tomo-repeat-note {
        background: #eaf7f0;
        border: 1px solid #cdebd9;
        border-radius: 8px;
        color: #167146;
    }

    .tomo-ajukan {
        background: #f7b543;
        border-radius: 9px;
    }

    .tomo-detail {
        background: #eff5ff;
        border-color: #cbdcf6;
        border-radius: 9px;
    }

    .tomo-qty {
        background: #eef9f1;
        border: 1px solid #d9eedf;
        border-radius: 9px;
    }

    .tomo-dialog {
        box-shadow: 0 22px 75px rgba(7, 25, 52, .23);
    }

    .tomo-dialog-head {
        background: #f7faff;
    }

    .tomo-btn:focus-visible,
    .tomo-tab:focus-visible,
    .tomo-ajukan:focus-visible,
    .tomo-detail:focus-visible {
        outline: 3px solid rgba(37, 99, 235, .3);
        outline-offset: 2px;
    }

    @media(max-width:600px) {
        .tomo-title {
            padding: 15px;
        }

        .tomo-sticky.tomo-search-fixed {
            padding: 8px !important;
        }

        .tomo-items {
            padding: 8px;
        }

        .tomo-item {
            padding: 10px;
        }
    }


    /* ===== Mobile refinement: compact, readable, no overlapping PO header ===== */
    .tomo-po-head {
        top: var(--tomo-search-offset, 64px) !important;
    }

    @media (max-width: 600px) {
        .tomo-page {
            padding: 8px 7px 24px;
            background: #f3f6fb;
        }

        .tomo-wrap {
            width: 100%;
            max-width: 100%;
        }

        .tomo-title {
            padding: 13px 14px;
            margin-bottom: 8px;
            border-radius: 12px;
        }

        .tomo-title h2 {
            font-size: 17px;
        }

        .tomo-title p {
            font-size: 11px;
        }

        .tomo-sticky {
            padding: 6px 0;
            border-radius: 11px;
        }

        .tomo-sticky.tomo-search-fixed {
            padding: 7px 9px !important;
            border-radius: 0 0 12px 12px;
        }

        .tomo-search {
            gap: 7px;
            align-items: center;
        }

        .tomo-search-field input {
            height: 43px;
            font-size: 14px;
            padding: 0 38px 0 12px;
            border-radius: 10px;
        }

        .tomo-search .tomo-btn {
            min-width: 69px;
            padding: 0 12px;
            height: 43px;
            font-size: 13px;
            border-radius: 10px;
        }

        .tomo-tabs {
            margin: 7px 0 10px;
            gap: 7px;
        }

        .tomo-tab {
            padding: 9px 11px;
            font-size: 12px;
        }

        .tomo-po {
            border-radius: 13px;
            margin-bottom: 12px;
            overflow: visible;
        }

        /* A sticky PO header was being hidden under the fixed search on phones.
           Keep PO headers in document flow on mobile. */
        .tomo-po-head {
            position: relative !important;
            top: auto !important;
            z-index: 1 !important;
            padding: 11px 12px;
            border-radius: 12px 12px 0 0;
            box-shadow: none !important;
            gap: 10px;
        }

        .tomo-po-head strong {
            font-size: 15px;
            line-height: 1.25;
        }

        .tomo-po-head small {
            white-space: nowrap;
            font-size: 11px;
            color: #526b89;
        }

        .tomo-buyer {
            font-size: 11px;
        }

        .tomo-items {
            padding: 8px;
            gap: 8px;
        }

        .tomo-item {
            grid-template-columns: 68px minmax(0, 1fr);
            gap: 9px;
            padding: 10px 9px;
            border-radius: 11px;
        }

        .tomo-image {
            width: 68px;
            height: 68px;
            border-radius: 9px;
        }

        .tomo-name {
            font-size: 13px;
            font-weight: 750;
            line-height: 1.38;
        }

        .tomo-meta {
            font-size: 11px;
            margin: 4px 0 7px;
            overflow-wrap: anywhere;
        }

        .tomo-spk {
            display: flex;
            align-items: flex-start;
            gap: 4px 8px;
            padding: 7px 8px;
            margin-top: 5px;
            font-size: 10.5px;
            line-height: 1.35;
            border-radius: 8px;
        }

        .tomo-spk span {
            min-width: 0;
            flex: 1 1 130px;
            overflow-wrap: anywhere;
        }

        .tomo-spk strong {
            white-space: nowrap;
            margin-left: auto;
            font-size: 11px;
        }

        .tomo-repeat-note {
            font-size: 10px;
            padding: 5px 7px;
            line-height: 1.3;
            margin-top: 7px;
        }

        .tomo-actions {
            margin-top: 8px;
            gap: 6px;
        }

        .tomo-qty {
            margin-left: 0;
            padding: 5px 9px;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .tomo-qty b {
            display: inline;
            font-size: 15px;
        }

        .tomo-ajukan,
        .tomo-detail {
            padding: 7px 10px;
            font-size: 11px;
        }

        .tomo-status {
            font-size: 10px;
            padding: 5px 7px;
        }

        .tomo-dialog {
            border-radius: 12px;
        }

        .tomo-dialog-head {
            padding: 10px;
        }

        .tomo-dialog-head h3 {
            font-size: 15px;
        }
    }

    @media (max-width: 360px) {
        .tomo-item {
            grid-template-columns: 58px minmax(0, 1fr);
            gap: 7px;
            padding: 8px 7px;
        }

        .tomo-image {
            width: 58px;
            height: 58px;
        }

        .tomo-name {
            font-size: 12px;
        }

        .tomo-spk strong {
            font-size: 10px;
        }
    }

    /* Compact search + underline tabbar, based on mobile references. */
    .tomo-sticky {
        background: #fff !important;
        padding: 9px 12px 0 !important;
        border: 1px solid #e3e9f0;
        border-radius: 12px;
    }

    .tomo-sticky.tomo-search-fixed {
        padding: 8px 12px 0 !important;
        border-radius: 0 0 10px 10px !important;
    }

    .tomo-search {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .tomo-search-field {
        position: relative;
        flex: 1;
        min-width: 0;
    }

    .tomo-search-field .tomo-search-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: #64748b;
        pointer-events: none;
    }

    .tomo-search-field input {
        height: 36px !important;
        width: 100%;
        padding: 0 33px 0 34px !important;
        border: 1px solid #dce4ec !important;
        border-radius: 7px !important;
        background: #f8fafc !important;
        font-size: 12px !important;
        box-shadow: none !important;
    }

    .tomo-search-field input:focus {
        border-color: #6e9dd6 !important;
        background: #fff !important;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .09) !important;
    }

    .tomo-search-clear {
        width: 23px;
        height: 23px;
        font-size: 17px;
        line-height: 21px;
        right: 6px;
    }

    .tomo-search .tomo-search-submit {
        height: 36px !important;
        min-width: 52px !important;
        padding: 0 10px !important;
        border-radius: 7px !important;
        font-size: 12px !important;
        box-shadow: none !important;
    }

    .tomo-tabs {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 0 !important;
        overflow: visible !important;
        margin: 8px 0 0 !important;
        padding: 0 !important;
        border-bottom: 1px solid #dce5ed;
    }

    .tomo-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 0;
        min-height: 35px;
        border: 0 !important;
        border-bottom: 2px solid transparent !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 600;
        padding: 8px 5px !important;
        white-space: nowrap;
        box-shadow: none !important;
    }

    .tomo-tab.active {
        color: #155e45 !important;
        border-bottom-color: #15803d !important;
        background: rgba(22, 163, 74, .035) !important;
    }

    .tomo-tab:focus-visible {
        outline: 2px solid #60a5fa;
        outline-offset: -2px;
    }

    @media(max-width:600px) {
        .tomo-sticky {
            padding: 7px 8px 0 !important;
        }

        .tomo-sticky.tomo-search-fixed {
            padding: 7px 8px 0 !important;
        }

        .tomo-search-field input {
            height: 33px !important;
            font-size: 11px !important;
        }

        .tomo-search .tomo-search-submit {
            height: 33px !important;
            min-width: 47px !important;
            font-size: 11px !important;
        }

        .tomo-tabs {
            margin-top: 6px !important;
        }

        .tomo-tab {
            min-height: 32px;
            font-size: 11px !important;
        }
    }


    /* Mobile compact item rows: photo | details | actions (reference layout) */
    @media (max-width: 600px) {
        .tomo-items {
            gap: 0 !important;
            padding: 0 9px 7px !important;
            background: #fff;
        }

        .tomo-item {
            display: grid !important;
            grid-template-columns: 48px minmax(0, 1fr) !important;
            align-items: start !important;
            gap: 9px !important;
            padding: 11px 3px !important;
            margin: 0 !important;
            border: 0 !important;
            border-bottom: 1px solid #e9edf2 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .tomo-item:last-child {
            border-bottom: 0 !important;
        }

        .tomo-item>div:first-child {
            width: 48px;
            min-width: 0;
        }

        .tomo-image,
        .tomo-photo {
            width: 48px !important;
            height: 48px !important;
            border-radius: 50% !important;
        }

        .tomo-image {
            object-fit: cover !important;
            font-size: 9px !important;
        }

        .tomo-item>div:nth-child(2) {
            min-width: 0;
        }

        .tomo-name {
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
            color: #182b44 !important;
            margin: 0 0 3px !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            overflow-wrap: anywhere;
        }

        .tomo-meta {
            font-size: 10px !important;
            margin: 0 0 5px !important;
            color: #7a8799 !important;
        }

        .tomo-spk {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
            margin: 3px 0 0 !important;
            padding: 3px 0 !important;
            border: 0 !important;
            background: transparent !important;
            border-radius: 0 !important;
            font-size: 10px !important;
        }

        .tomo-spk span {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tomo-spk strong {
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #16734c !important;
            white-space: nowrap;
        }

        .tomo-repeat-note {
            display: inline-block !important;
            max-width: 100%;
            font-size: 9px !important;
            padding: 3px 6px !important;
            margin: 4px 0 0 !important;
            border-radius: 5px !important;
            font-weight: 600 !important;
        }

        .tomo-actions {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 5px !important;
            margin-top: 6px !important;
            align-items: center !important;
        }

        .tomo-qty {
            margin: 0 !important;
            padding: 3px 7px !important;
            border-radius: 6px !important;
            font-size: 10px !important;
            gap: 4px !important;
        }

        .tomo-qty b {
            font-size: 11px !important;
        }

        .tomo-status {
            padding: 3px 6px !important;
            font-size: 9px !important;
        }

        .tomo-ajukan,
        .tomo-detail {
            padding: 5px 8px !important;
            font-size: 10px !important;
            border-radius: 6px !important;
        }

        .tomo-po {
            overflow: visible !important;
        }

        .tomo-po-head {
            padding: 9px 12px !important;
        }

        .tomo-po-head strong {
            font-size: 12px !important;
        }

        .tomo-buyer,
        .tomo-po-head small {
            font-size: 10px !important;
        }

        /* Search and tabs stay as one sticky unit; PO headers remain in normal flow. */
        .tomo-sticky {
            z-index: 300 !important;
        }

        .tomo-po-head {
            position: relative !important;
            top: auto !important;
            z-index: 1 !important;
        }
    }


    /* FINAL FIX: PO header sticks beneath the compact search + tab bar on phones. */
    @media (max-width: 600px) {
        #tomoItemsPanel .tomo-po {
            overflow: visible !important;
            position: relative !important;
        }

        #tomoItemsPanel .tomo-po>.tomo-po-head {
            position: sticky !important;
            top: var(--tomo-search-offset, 72px) !important;
            z-index: 80 !important;
            background: #eaf2fd !important;
            box-shadow: 0 2px 8px rgba(25, 62, 112, .10) !important;
            border-radius: 8px 8px 0 0 !important;
        }

        #tomoSearchBar {
            z-index: 300 !important;
        }
    }

    /* Harga mutlak ditampilkan, tetapi tidak dapat diubah pengguna. */
    .tomo-dialog .tomo-row-harga[readonly],
    .tomo-dialog .tomo-stn-input[readonly] {
        background: #eef2f7 !important;
        color: #334155 !important;
        cursor: not-allowed;
        opacity: 1;
    }
</style>

@section('content')

    <div class="tomo-page">

        <div class="tomo-wrap">

            <div class="tomo-title">

                <h2>Dashboard Pengajuan Tomo</h2>

                <p>Cari PO / nama items, klik <b>Ajukan</b>. Sudah mengajukan? Klik pada items untuk melihat pengajuan.</p>

            </div>

            <div id="tomoSearchAnchor" aria-hidden="true"></div>
            <div class="tomo-sticky" id="tomoSearchBar">
                <form method="GET" action="{{ route('produksi.tomo') }}" class="tomo-search" role="search">
                    <div class="tomo-search-field">
                        <svg class="tomo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-4-4"></path>
                        </svg>
                        <input id="tomoSearchInput" type="search" name="search" value="{{ $search ?? '' }}"
                            placeholder="Search PO, article, item..." aria-label="Cari PO, article, atau nama item"
                            oninput="tomoToggleSearchClear()">
                        <button id="tomoSearchClear" class="tomo-search-clear" type="button" aria-label="Hapus pencarian"
                            title="Hapus pencarian" onclick="tomoClearSearch()"
                            {{ empty($search) ? 'hidden' : '' }}>×</button>
                    </div>
                    <button class="tomo-btn tomo-search-submit" type="submit" aria-label="Cari">Cari</button>
                </form>
                <div class="tomo-tabs" role="tablist" aria-label="Navigasi Tomo">
                    <button type="button" role="tab" aria-selected="true" class="tomo-tab active" data-tab="items"
                        onclick="tomoSwitchTab('items')">Daftar PO</button>
                    <button type="button" role="tab" aria-selected="false" class="tomo-tab" data-tab="history"
                        onclick="tomoSwitchTab('history')">Riwayat Pengajuan <span id="tomoHistoryCount"></span></button>
                </div>
            </div>

            <div id="tomoItemsPanel">

                @forelse($cards as $card)

                    <section class="tomo-po">

                        <div class="tomo-po-head">

                            <div><strong>PO: {{ $card['po'] }}</strong>

                                <div class="tomo-buyer">{{ $card['buyer'] }}</div>

                            </div><small>{{ count($card['items']) }} item</small>

                        </div>

                        <div class="tomo-items">

                            @forelse($card['items'] as $item)
                                <div class="tomo-item" data-po="{{ $card['po'] }}" data-name="{{ $item['name'] }}"
                                    data-article="{{ $item['article'] }}" data-image="{{ $item['image'] ?? '' }}"
                                    data-has-repeat="{{ !empty($item['spks']) ? '1' : '0' }}"
                                    data-qty="{{ $item['qty'] }}"
                                    data-item-id="{{ $item['detail_po_id'] ?? ($item['id'] ?? '') }}">

                                    <div>

                                        @if (!empty($item['image']))
                                            <button type="button" class="tomo-photo" aria-label="Perbesar foto"
                                                onclick="tomoZoomOpen(this.closest('.tomo-item').dataset.image)"><img
                                                    class="tomo-image" src="{{ $item['image'] }}"
                                                    alt="{{ $item['name'] }}" loading="lazy"
                                                onerror="this.style.display='none'"></button>@else<div
                                                class="tomo-image"
                                                style="display:grid;place-items:center;font-size:11px;color:#64748b">Tanpa

                                                foto</div>
                                        @endif

                                    </div>

                                    <div>

                                        <div class="tomo-name">{{ $item['name'] ?: '-' }}</div>

                                        <div class="tomo-meta">Article: {{ $item['article'] ?: '-' }}</div>

                                        @foreach ($item['spks'] ?? [] as $spk)
                                            <div class="tomo-spk">

                                                <span>{{ $spk['number'] ?? '-' }}</span><strong>{{ isset($spk['harga']) && $spk['harga'] !== null ? 'Rp ' . number_format((float) $spk['harga'], 0, ',', '.') : 'Harga belum ada' }}</strong>

                                            </div>
                                        @endforeach

                                        @if (!empty($item['spks']))
                                            <div class="tomo-repeat-note">Repeating (harga SPK sebelumnya)</div>
                                        @endif

                                        <div class="tomo-actions"><span
                                                class="tomo-qty">Qty<b>{{ $item['qty'] }}</b></span><span
                                                class="tomo-status unknown tomo-item-status"
                                                style="display:none"></span><button type="button"
                                                class="tomo-ajukan tomo-item-ajukan"
                                                style="{{ !empty($item['spks']) ? 'display:none' : '' }}"
                                                onclick="tomoOpenForm(this.closest('.tomo-item'))">+ Ajukan</button><button
                                                type="button" class="tomo-detail tomo-item-detail" style="display:none"
                                                onclick="tomoOpenItemHistory(this.closest('.tomo-item'))">👁 Lihat

                                                Pengajuan</button></div>

                                    </div>

                                </div>

                            @empty<div class="tomo-empty">Belum ada item pada PO ini.</div>
                            @endforelse

                        </div>

                    </section>

                @empty<div class="tomo-empty">Tidak ada PO yang sesuai pencarian.</div>

                @endforelse

                <div style="margin:16px 0">{{ $pos->links() }}</div>

            </div>

            <div id="tomoHistoryPanel" class="tomo-hidden">

                <div id="tomoHistoryList" class="tomo-history-list">

                    <div class="tomo-empty">Memuat riwayat...</div>

                </div>

            </div>

        </div>

    </div>

    <div class="tomo-modal" id="tomoModal" role="dialog" aria-modal="true" aria-labelledby="tomoModalTitle"
        onclick="if(event.target===this)tomoClose()">

        <div class="tomo-dialog">

            <div class="tomo-dialog-head">

                <h3 id="tomoModalTitle">Ajukan SPK Tomo Finishing</h3><button class="tomo-x" type="button"
                    onclick="tomoClose()">×</button>

            </div>

            <div class="tomo-dialog-body" id="tomoModalBody">

                <div class="tomo-product"><img id="tomoModalPhoto" src="" alt="Foto produk"
                        onclick="tomoZoomOpen(this.src)" style="display:none">

                    <div><small>NO PO <b id="tomoModalPo">-</b></small><strong
                            id="tomoModalName">-</strong><small>Article:

                            <b id="tomoModalArticle">-</b> · Qty: <b id="tomoModalQty">-</b></small></div>

                </div>

                <div id="tomoFormPanel"><b>Rincian Biaya Finishing</b>

                    <table class="tomo-table">

                        <colgroup>

                            <col style="width:27%">

                            <col style="width:14%">

                            <col style="width:20%">

                            <col style="width:13%">

                            <col style="width:26%">

                        </colgroup>

                        <thead>

                            <tr>

                                <th>Proses</th>

                                <th>Qty</th>

                                <th>Harga</th>

                                <th>Stn</th>

                                <th>Jumlah</th>

                            </tr>

                        </thead>

                        <tbody id="tomoCostRows"></tbody>

                    </table>

                    <div class="tomo-total"><span>Total Biaya</span><span id="tomoGrandTotal">Rp 0</span></div><label
                        style="display:block;margin-top:12px;font-weight:700">Catatan (opsional)

                        <textarea id="tomoNotes" class="tomo-note" placeholder="Tuliskan catatan jika ada..."></textarea>

                    </label>

                    <p class="tomo-help">Nama proses mengikuti master point terbaru. Riwayat menyimpan nama proses saat

                        diajukan.</p>

                </div>

                <div id="tomoDetailPanel" class="tomo-hidden">

                    <div id="tomoDetailContent"></div>

                </div>

            </div>

            <div class="tomo-dialog-foot"><button type="button" class="tomo-btn"
                    style="background:#e2e8f0;color:#334155" onclick="tomoClose()">Tutup</button><button type="button"
                    class="tomo-btn" id="tomoSubmitBtn" onclick="tomoSubmit()">Kirim Pengajuan</button></div>

        </div>

    </div>

    <div id="tomoZoom" class="tomo-zoom" onclick="if(event.target===this)tomoZoomClose()"><img id="tomoZoomImage"
            alt="Foto diperbesar">

        <div style="display:flex;gap:8px"><button onclick="tomoZoomChange(-.25)">−</button><button
                onclick="tomoZoomChange(.25)">+</button><button onclick="tomoZoomClose()">Tutup ×</button></div>

    </div>

    <script>
        function tomoToggleSearchClear() {

            const input = document.getElementById('tomoSearchInput');

            document.getElementById('tomoSearchClear').hidden = !input.value.length;

        }



        function tomoClearSearch() {

            const input = document.getElementById('tomoSearchInput');

            input.value = '';

            tomoToggleSearchClear();

            input.focus();

            if (new URLSearchParams(location.search).has('search')) {

                location.href = location.pathname;

            }

        }



        (function() {

            'use strict';

            const API_URL = @json(route('produksi.tomo.submissions.index'));

            const STORE_URL = @json(route('produksi.tomo.submissions.store'));

            const CSRF = @json(csrf_token());

            let tomoPoints = [];

            const POINTS_URL = @json(route('produksi.tomo.points'));

            async function tomoLoadPoints() {

                const res = await fetch(POINTS_URL, {

                    headers: {

                        'Accept': 'application/json'

                    },

                    cache: 'no-store'

                });

                if (!res.ok) throw Error('Master point gagal dimuat');

                const points = await res.json();

                if (!Array.isArray(points) || !points.length) throw Error('Belum ada master point aktif');

                // NULL berarti harga master belum diisi, bukan API rusak.
                if (points.some(p => !Object.prototype.hasOwnProperty.call(p, 'harga_mutlak'))) {
                    throw Error('API master point tidak memiliki kolom harga_mutlak.');
                }

                tomoPoints = points;

            }

            const rupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID', {

                maximumFractionDigits: 0

            }).format(Number(value) || 0);

            const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({

                '&': '&amp;',

                '<': '&lt;',

                '>': '&gt;',

                '"': '&quot;',

                "'": '&#39;'

            } [c]));

            let submissions = [],

                activeItem = null,

                zoomScale = 1;



            function itemKey(po, article, itemId) {

                return itemId ? 'id:' + itemId : 'po:' + String(po).trim().toLowerCase() + '|article:' + String(article)

                    .trim().toLowerCase()

            }



            function statusClass(s) {

                let k = String(s || 'pending').toLowerCase();

                return ['pending', 'diproses', 'disetujui', 'ditolak'].includes(k) ? k : 'unknown'

            }



            function statusLabel(s) {

                return ({

                    pending: '⏳ Pending',

                    diproses: '⚙ Diproses',

                    disetujui: '✓ Disetujui',

                    ditolak: '✕ Ditolak'

                })[statusClass(s)] || esc(s || 'Tidak diketahui')

            }



            function keyFromElement(el) {

                return itemKey(el.dataset.po, el.dataset.article, el.dataset.itemId)

            }



            function keyFromSubmission(s) {

                return itemKey(s.po_code, s.article_code, s.detail_po_id)

            }



            function submissionsForItem(el) {

                const key = keyFromElement(el);

                return submissions.filter(s => keyFromSubmission(s) === key).sort((a, b) => Number(b.id) - Number(a.id))

            }

            async function loadHistory() {

                try {

                    const res = await fetch(API_URL, {

                        headers: {

                            'Accept': 'application/json'

                        },

                        credentials: 'same-origin',

                        cache: 'no-store'

                    });

                    if (!res.ok) throw Error('HTTP ' + res.status);

                    const data = await res.json();

                    submissions = Array.isArray(data) ? data : (data.data || []);

                    renderItems();

                    renderHistory()

                } catch (e) {

                    document.getElementById('tomoHistoryList').innerHTML =

                        '<div class="tomo-empty">Riwayat gagal dimuat. Silakan refresh halaman.</div>';

                    console.error('Tomo history:', e)

                }

            }



            function renderItems() {

                document.querySelectorAll('.tomo-item').forEach(el => {

                    const entries = submissionsForItem(el),

                        latest = entries[0],

                        badge = el.querySelector('.tomo-item-status'),

                        ajukan = el.querySelector('.tomo-item-ajukan'),

                        detail = el.querySelector('.tomo-item-detail');

                    badge.style.display = latest ? 'inline-block' : 'none';

                    badge.className = 'tomo-status tomo-item-status ' + statusClass(latest?.status);

                    badge.textContent = latest ? statusLabel(latest.status) : '';

                    detail.style.display = entries.length ? 'inline-block' : 'none';

                    // Artikel dengan harga SPK sebelumnya = repeating.
                    // Jangan tampilkan Ajukan walaupun belum ada submission TOMO.
                    const isRepeating = el.dataset.hasRepeat === '1';
                    ajukan.style.display = !isRepeating &&
                        (!latest || statusClass(latest.status) === 'ditolak') ?
                        'inline-block' : 'none';

                    ajukan.textContent = latest && statusClass(latest.status) === 'ditolak' ? '+ Ajukan Lagi' :

                        '+ Ajukan';

                });

                document.getElementById('tomoHistoryCount').textContent = submissions.length ? '(' + submissions

                    .length + ')' : ''

            }



            function renderHistory() {

                const target = document.getElementById('tomoHistoryList');

                if (!submissions.length) {

                    target.innerHTML = '<div class="tomo-empty">Belum ada riwayat pengajuan.</div>';

                    return

                }

                target.replaceChildren();

                submissions.slice().sort((a, b) => Number(b.id) - Number(a.id)).forEach(s => {

                    const div = document.createElement('div');

                    div.className = 'tomo-history-entry';

                    div.innerHTML = '<div><strong>PO: ' + esc(s.po_code) + '</strong><small>' + esc(s

                            .item_name) + ' · ' + esc(s.article_code) + '</small><small>' + esc(s.created_at) +

                        ' · ' + rupiah(s.total) + '</small><span class="tomo-status ' + statusClass(s.status) +

                        '">' + statusLabel(s.status) +

                        '</span></div><button type="button" class="tomo-detail">Detail ›</button>';

                    div.querySelector('button').addEventListener('click', () => openDetail(s));

                    target.appendChild(div)

                })

            }

            window.tomoSwitchTab = function(tab) {

                document.getElementById('tomoItemsPanel').classList.toggle('tomo-hidden', tab !== 'items');

                document.getElementById('tomoHistoryPanel').classList.toggle('tomo-hidden', tab !== 'history');

                document.querySelectorAll('.tomo-tab').forEach(b => {
                    const active = b.dataset.tab === tab;
                    b.classList.toggle('active', active);
                    b.setAttribute('aria-selected', String(active));
                })

            };



            function fillProduct(data) {

                document.getElementById('tomoModalPo').textContent = data.po_code || '-';

                document.getElementById('tomoModalName').textContent = data.item_name || '-';

                document.getElementById('tomoModalArticle').textContent = data.article_code || '-';

                document.getElementById('tomoModalQty').textContent = data.qty || '-';

                const photo = document.getElementById('tomoModalPhoto');

                photo.style.display = data.image ? 'block' : 'none';

                if (data.image) photo.src = data.image

            }



            function modalOpen() {

                document.getElementById('tomoModal').classList.add('open');

                document.body.style.overflow = 'hidden';

                document.getElementById('tomoModalBody').scrollTop = 0

            }

            let tomoPointRequestId = 0;

            window.tomoOpenForm = async function(el) {

                const requestId = ++tomoPointRequestId;

                if (el.dataset.hasRepeat === '1') return;
                activeItem = el;

                const data = {

                    po_code: el.dataset.po,

                    item_name: el.dataset.name,

                    article_code: el.dataset.article,

                    qty: el.dataset.qty,

                    image: el.dataset.image

                };

                fillProduct(data);

                document.getElementById('tomoModalTitle').textContent = 'Ajukan SPK Tomo Finishing';

                document.getElementById('tomoFormPanel').classList.remove('tomo-hidden');

                document.getElementById('tomoDetailPanel').classList.add('tomo-hidden');

                document.getElementById('tomoNotes').value = '';

                const submit = document.getElementById('tomoSubmitBtn');

                submit.style.display = 'block';

                submit.disabled = true;

                submit.textContent = 'Memuat point...';

                document.getElementById('tomoCostRows').innerHTML =

                    '<tr><td colspan="5" style="padding:20px;text-align:center">Memuat master point terbaru...</td></tr>';

                document.getElementById('tomoGrandTotal').textContent = rupiah(0);

                modalOpen(); // buka modal terlebih dahulu, kemudian AJAX GET master point

                try {

                    await tomoLoadPoints();

                    if (requestId !== tomoPointRequestId || !document.getElementById('tomoModal').classList

                        .contains('open')) return;

                    buildRows();

                    submit.disabled = false;

                    submit.textContent = 'Kirim Pengajuan';

                } catch (e) {

                    if (requestId !== tomoPointRequestId) return;

                    document.getElementById('tomoCostRows').innerHTML =

                        '<tr><td colspan="5" style="padding:18px;color:#b91c1c;text-align:center">' + esc(e

                            .message) + '</td></tr>';

                    submit.textContent = 'Point tidak tersedia';

                }

            };

            window.tomoOpenItemHistory = function(el) {

                const entries = submissionsForItem(el);

                if (entries.length) openDetail(entries[0])

            };



            function buildRows() {
                const body = document.getElementById('tomoCostRows');
                body.replaceChildren();

                tomoPoints.forEach(point => {
                    const name = String(point.name ?? '');
                    const harga = point.harga_mutlak === null || point.harga_mutlak === '' ? null : Number(point
                        .harga_mutlak);
                    const satuan = String(point.satuan ?? '').trim();
                    const tr = document.createElement('tr');
                    tr.dataset.pointId = point.id;

                    tr.innerHTML = '<td>' + esc(name) + '</td>' +
                        '<td><input class="tomo-cost-input tomo-row-qty" type="number" min="0" step="any" inputmode="decimal" aria-label="Qty ' +
                        esc(name) + '" value="0"></td>' +
                        '<td><input class="tomo-cost-input tomo-row-harga" type="number" min="0" step="any" aria-label="Harga mutlak ' +
                        esc(name) +
                        '" readonly tabindex="-1" title="Harga dari master point, tidak dapat diedit"></td>' +
                        '<td><input class="tomo-stn-input" type="text" maxlength="30" readonly tabindex="-1" aria-label="Satuan ' +
                        esc(name) + '"></td>' +
                        '<td class="tomo-result">—</td>';

                    // Nilai harga dan satuan bersumber dari API master point.
                    tr.querySelector('.tomo-row-harga').value = harga !== null && Number.isFinite(harga) &&
                        harga >= 0 ? harga : '';
                    if (harga === null || !Number.isFinite(harga) || harga < 0 || !satuan) {
                        tr.querySelector('.tomo-row-qty').disabled = true;
                        tr.querySelector('.tomo-row-qty').title =
                            'Lengkapi harga dan satuan pada Master Points terlebih dahulu';
                        tr.querySelector('.tomo-row-qty').value = '0';
                        tr.querySelector('.tomo-row-harga').placeholder = '—';
                        tr.title = 'Harga atau satuan master belum diisi';
                    }
                    tr.querySelector('.tomo-stn-input').value = satuan;
                    tr.querySelector('.tomo-row-qty').addEventListener('input', recalc);
                    body.appendChild(tr);
                });
                recalc();
            }

            function getRows() {
                return Array.from(document.querySelectorAll('#tomoCostRows tr[data-point-id]')).filter(tr => !tr
                    .querySelector('.tomo-row-qty').disabled).map(tr => ({
                    point_id: Number(tr.dataset.pointId),
                    satuan: tr.querySelector('.tomo-stn-input').value.trim(),
                    harga: Number(tr.querySelector('.tomo-row-harga').value),
                    qty: Number(tr.querySelector('.tomo-row-qty').value)
                }));
            }

            function recalc() {
                let total = 0;
                document.querySelectorAll('#tomoCostRows tr[data-point-id]').forEach(tr => {
                    const qtyInput = tr.querySelector('.tomo-row-qty');
                    const hargaInput = tr.querySelector('.tomo-row-harga');
                    const qty = Number(qtyInput.value) || 0;
                    const harga = Number(hargaInput.value) || 0;
                    const amount = qty * harga;
                    total += amount;
                    tr.querySelector('.tomo-result').textContent = qty > 0 ? rupiah(amount) : '—';
                });
                document.getElementById('tomoGrandTotal').textContent = rupiah(total);
            }

            window.tomoSubmit = async function() {

                if (!activeItem) return;

                const rows = getRows();

                if (rows.some(r => r.harga < 0 || r.qty < 0 || !Number.isFinite(r.harga) || !Number.isFinite(r

                        .qty))) {

                    alert('Harga dan Qty harus angka positif.');

                    return

                }

                const total = rows.reduce((a, r) => a + r.harga * r.qty, 0);

                if (total <= 0) {

                    alert('Isi minimal satu biaya sebelum mengajukan.');

                    return

                }

                if (!activeItem.dataset.itemId) {

                    alert('ID item PO tidak ditemukan. Hubungi admin.');

                    return

                }

                if (!confirm('Kirim pengajuan biaya ' + rupiah(total) + '?')) return;

                const btn = document.getElementById('tomoSubmitBtn');

                btn.disabled = true;

                btn.textContent = 'Mengirim...';

                try {

                    const payload = {

                        po_code: activeItem.dataset.po,

                        article_code: activeItem.dataset.article,

                        item_name: activeItem.dataset.name,

                        detail_po_id: activeItem.dataset.itemId || null,

                        qty: Number(activeItem.dataset.qty) || 0,

                        image: activeItem.dataset.image || null,

                        notes: document.getElementById('tomoNotes').value,

                        rows

                    };

                    const res = await fetch(STORE_URL, {

                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {

                            'Accept': 'application/json',

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': CSRF

                        },

                        body: JSON.stringify(payload)

                    });

                    const data = await res.json();

                    if (!res.ok) throw Error(data.message || (data.errors ? Object.values(data.errors).flat()

                        .join(' ') : 'Gagal menyimpan'));

                    window.tomoClose();

                    await loadHistory();

                    alert('Pengajuan berhasil dikirim. Status: Pending.')

                } catch (e) {

                    alert('Gagal mengirim: ' + e.message)

                } finally {

                    btn.disabled = false;

                    btn.textContent = 'Kirim Pengajuan'

                }

            };



            function openDetail(s) {

                activeItem = null;

                fillProduct(s);

                document.getElementById('tomoModalTitle').textContent = 'Detail Pengajuan #' + s.id;

                document.getElementById('tomoFormPanel').classList.add('tomo-hidden');

                document.getElementById('tomoDetailPanel').classList.remove('tomo-hidden');

                document.getElementById('tomoSubmitBtn').style.display = 'none';

                const rows = Array.isArray(s.rows) ? s.rows : [];

                let html = '<div class="tomo-history-card"><span class="tomo-status ' + statusClass(s.status) + '">' +

                    statusLabel(s.status) + '</span><p><b>Diajukan:</b> ' + esc(s.created_at) +

                    '</p><p><b>Total:</b> ' + rupiah(s.total) +

                    '</p></div><b>Rincian Biaya Finishing</b><table class="tomo-table"><thead><tr><th>Proses</th><th>Harga</th><th>Qty</th><th>Stn</th><th>Jumlah</th></tr></thead><tbody>';

                rows.forEach(r => {

                    html += '<tr><td>' + esc(r.point_name || r.process || '-') + '</td><td>' + rupiah(r.harga) +

                        '</td><td style="text-align:center">' + esc(r.qty) +

                        '</td><td style="text-align:center">' + esc(r.satuan || 'pcs') +

                        '</td><td class="tomo-result">' + rupiah(Number(r.harga) * Number(r.qty)) + '</td></tr>'

                });

                html += '</tbody></table><div class="tomo-total"><span>Total</span><span>' + rupiah(s.total) +

                    '</span></div>';

                if (s.notes) html += '<div class="tomo-history-card"><b>Catatan</b><p>' + esc(s.notes) + '</p></div>';

                html += '<div class="tomo-history-card"><b>Riwayat Status</b><p>⏳ Pengajuan dibuat: ' + esc(s

                    .created_at) + '</p>';

                if (s.reviewed_at) html += '<p>' + statusLabel(s.status) + ' : ' + esc(s.reviewed_at) + '</p>';

                if (s.review_notes) html += '<p>Catatan pemeriksa: ' + esc(s.review_notes) + '</p>';

                html += '</div>';

                document.getElementById('tomoDetailContent').innerHTML = html;

                modalOpen()

            }

            window.tomoClose = function() {

                tomoPointRequestId++;

                document.getElementById('tomoModal').classList.remove('open');

                document.body.style.overflow = ''

            };

            window.tomoZoomOpen = function(url) {

                if (!url) return;

                zoomScale = 1;

                document.getElementById('tomoZoomImage').src = url;

                document.getElementById('tomoZoomImage').style.setProperty('--scale', 1);

                document.getElementById('tomoZoom').classList.add('open')

            };

            window.tomoZoomChange = function(delta) {

                zoomScale = Math.max(.5, Math.min(4, zoomScale + delta));

                document.getElementById('tomoZoomImage').style.setProperty('--scale', zoomScale)

            };

            window.tomoZoomClose = function() {

                document.getElementById('tomoZoom').classList.remove('open')

            };

            document.addEventListener('keydown', e => {

                if (e.key === 'Escape') {

                    if (document.getElementById('tomoZoom').classList.contains('open')) tomoZoomClose();

                    else tomoClose()

                }

            });

            loadHistory();

        })();
    </script>


    <script>
        /* Sticky search fallback: works even when master layout has overflow containers. */
        (function() {
            const bar = document.getElementById('tomoSearchBar');
            const anchor = document.getElementById('tomoSearchAnchor');
            const wrap = document.querySelector('.tomo-wrap');
            if (!bar || !anchor || !wrap) return;
            let frame = 0;

            function syncSearch() {
                frame = 0;
                const isOpen = document.body.style.overflow === 'hidden';
                if (isOpen) return;
                const fixed = anchor.getBoundingClientRect().top < 0;
                if (fixed) {
                    if (!bar.classList.contains('tomo-search-fixed')) {
                        anchor.style.height = bar.offsetHeight + 'px';
                        bar.classList.add('tomo-search-fixed');
                    }
                    const rect = wrap.getBoundingClientRect();
                    bar.style.left = Math.max(0, rect.left) + 'px';
                    bar.style.width = Math.min(rect.width, window.innerWidth) + 'px';
                } else {
                    bar.classList.remove('tomo-search-fixed');
                    bar.style.left = '';
                    bar.style.width = '';
                    anchor.style.height = '';
                }
                document.documentElement.style.setProperty('--tomo-search-offset', fixed ? bar.offsetHeight + 'px' : bar
                    .offsetHeight + 'px');
            }

            function schedule() {
                if (!frame) frame = requestAnimationFrame(syncSearch);
            }
            window.addEventListener('scroll', schedule, {
                passive: true
            });
            document.addEventListener('scroll', schedule, {
                passive: true,
                capture: true
            });
            window.addEventListener('resize', schedule, {
                passive: true
            });
            syncSearch();
        })();
    </script>

@endsection
