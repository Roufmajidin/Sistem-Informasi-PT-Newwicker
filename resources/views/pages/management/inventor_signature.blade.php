@extends('master.master')
@section('title', 'SPK Signature Center')
@section('content')
    <style>
        .sig-wrap {
            max-width: 1180px;
            margin: 20px auto;
            padding: 0 16px 60px;
            font: 13px Arial, sans-serif;
            color: #26374d
        }

        .sig-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px
        }

        .sig-top h2 {
            font-size: 22px;
            margin: 0
        }

        .sig-search {
            width: 100%;
            padding: 12px;
            border: 1px solid #d6dee8;
            border-radius: 8px;
            margin-bottom: 18px
        }

        .sig-doc {
            background: #fff;
            border: 1px solid #dce3eb;
            border-radius: 10px;
            margin-bottom: 26px;
            overflow: hidden;
            box-shadow: 0 5px 16px #26374d0b
        }

        .sig-doc-head {
            padding: 12px 18px;
            background: #f1f5fa;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .sig-paper {
            padding: 25px;
            overflow-x: auto
        }

        .sig-title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 18px
        }

        .sig-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
            margin-bottom: 20px
        }

        .sig-info div {
            padding: 8px;
            border-bottom: 1px solid #e4e9ef
        }

        .sig-table {
            border-collapse: collapse;
            width: 100%;
            min-width: 850px
        }

        .sig-table th {
            background: #26374d;
            color: #fff;
            padding: 9px
        }

        .sig-table td {
            padding: 8px;
            border: 1px solid #dce3eb;
            vertical-align: middle
        }

        .sig-table img {
            width: 65px;
            height: 65px;
            object-fit: contain
        }

        .sig-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px
        }

        .sig-signs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 25px
        }

        .sig-sign {
            border: 1px solid #dce3eb;
            text-align: center;
            padding: 10px 4px
        }

        .sig-sign .space {
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #21835d
        }

        .sig-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            padding: 14px 18px;
            border-top: 1px solid #e2e8f0
        }

        .sig-actions input {
            padding: 10px;
            border: 1px solid #dce3eb;
            border-radius: 6px;
            flex: 1
        }

        .sig-actions button {
            background: #2563eb;
            color: white;
            border: 0;
            border-radius: 7px;
            padding: 11px 22px;
            cursor: pointer
        }

        .sig-actions button:disabled {
            opacity: .5
        }

        .sig-empty {
            padding: 40px;
            text-align: center;
            background: white;
            border: 1px solid #dce3eb;
            border-radius: 10px
        }

        @media(max-width:750px) {

            .sig-info,
            .sig-bottom {
                grid-template-columns: 1fr
            }

            .sig-signs {
                grid-template-columns: repeat(2, 1fr)
            }
        }


        /* Signature Center mengikuti SPK Pages */
        :root {
            --spk-green: #50b95a;
            --spk-border: #d8dee8;
            --spk-text: #172033;
            --spk-muted: #737b88
        }

        .sig-wrap {
            max-width: 1560px;
            margin: 10px auto;
            padding: 0 12px 40px;
            font: 12px "Segoe UI", Arial, sans-serif;
            color: var(--spk-text)
        }

        .sig-top {
            background: #fff;
            border: 1px solid var(--spk-border);
            border-radius: 6px;
            padding: 13px 16px;
            margin: 0 0 10px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 3px #0000000a
        }

        .sig-top h2 {
            font-size: 15px;
            letter-spacing: .2px
        }

        .sig-top strong {
            background: #eaf8ed;
            color: #25823d;
            border-radius: 5px;
            padding: 8px 11px
        }

        .sig-search {
            height: 36px;
            padding: 8px 12px;
            border: 1px solid var(--spk-border);
            border-radius: 5px;
            margin: 0 0 12px;
            font-size: 11px
        }

        .sig-doc {
            border: 1px solid var(--spk-border);
            border-radius: 6px;
            margin: 0 0 20px;
            box-shadow: 0 1px 4px #00000008
        }

        .sig-doc-head {
            padding: 10px 13px;
            background: #f8fafc;
            border-bottom: 1px solid var(--spk-border);
            gap: 12px
        }

        .sig-doc-head strong {
            font-size: 12px
        }

        .sig-doc-head span {
            color: #217f37;
            font-weight: 700;
            font-size: 11px
        }

        .sig-paper {
            padding: 0;
            overflow-x: auto
        }

        .sig-title {
            background: var(--spk-green);
            color: #fff;
            text-align: left;
            font-size: 11px;
            letter-spacing: .4px;
            padding: 10px 13px;
            margin: 0
        }

        .sig-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
            margin: 0;
            border-bottom: 1px solid var(--spk-border)
        }

        .sig-info div {
            padding: 10px 12px;
            border-right: 1px solid var(--spk-border);
            border-bottom: 1px solid var(--spk-border);
            font-size: 11px;
            min-width: 0;
            overflow-wrap: anywhere
        }

        .sig-info b {
            display: block;
            font-size: 9px;
            color: var(--spk-muted);
            margin-bottom: 5px;
            text-transform: uppercase
        }

        .sig-table {
            width: 100%;
            min-width: 1100px;
            font-size: 11px
        }

        .sig-table th {
            background: var(--spk-green);
            padding: 7px 6px;
            font-size: 10px;
            border: 1px solid #65a96b;
            white-space: nowrap
        }

        .sig-table td {
            padding: 5px 6px;
            border: 1px solid var(--spk-border);
            font-size: 11px
        }

        .sig-table tbody tr:nth-child(even) {
            background: #f8fafc
        }

        .sig-table img {
            width: 58px;
            height: 48px;
            object-fit: contain
        }

        .sig-bottom {
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, .95fr);
            gap: 10px;
            padding: 10px;
            margin: 0
        }

        .sig-bottom>div {
            border: 1px solid var(--spk-border);
            border-radius: 5px;
            overflow: hidden;
            padding: 0 10px 8px;
            font-size: 11px;
            min-height: 95px
        }

        .sig-bottom>div>b {
            display: block;
            background: var(--spk-green);
            color: #fff;
            padding: 9px 10px;
            margin: 0 -10px 8px;
            text-transform: uppercase;
            font-size: 10px
        }

        .sig-signs {
            padding: 0 10px 12px;
            margin: 0;
            gap: 8px
        }

        .sig-sign {
            border: 1px solid var(--spk-border);
            border-radius: 4px;
            padding: 9px 5px;
            font-size: 10px
        }

        .sig-sign b {
            font-size: 10px
        }

        .sig-sign .space {
            height: 50px
        }

        .sig-sign small {
            font-size: 9px;
            color: #64748b
        }

        .sig-actions {
            background: #f8fafc;
            border-top: 1px solid var(--spk-border);
            padding: 11px 13px
        }

        .sig-actions input {
            border-radius: 4px;
            padding: 9px 10px;
            font-size: 11px
        }

        .sig-actions button {
            background: var(--spk-green);
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 11px;
            font-weight: 700
        }

        .sig-actions button:hover {
            background: #439d4c
        }

        @media(max-width:800px) {
            .sig-info {
                grid-template-columns: 1fr 1fr
            }

            .sig-bottom {
                grid-template-columns: 1fr
            }

            .sig-signs {
                grid-template-columns: repeat(2, 1fr)
            }

            .sig-top {
                position: static
            }
        }

        .sig-person {
            font-weight: 700;
            font-size: 11px;
            margin-top: 6px;
            color: #26374d;
            overflow-wrap: anywhere
        }

        .sig-monitor {
            font-size: 11px;
            color: #64748b;
            justify-content: flex-start
        }

        /* Signature table matches SPK index approval area */
        .sig-approval {
            margin: 10px 0 0;
            background: #fff
        }

        .sig-approval-head {
            background: #26374d;
            color: white;
            font-weight: 700;
            padding: 10px 12px;
            font-size: 11px
        }

        .sig-approval-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            text-align: center
        }

        .sig-approval-table th {
            background: #50b95a;
            color: #fff;
            border: 1px solid #d5deeb;
            padding: 7px 4px;
            font-size: 10px
        }

        .sig-approval-table td {
            border: 1px solid #d5deeb;
            padding: 6px;
            vertical-align: middle
        }

        .sig-approval-images td {
            height: 85px
        }

        .sig-approval-images .sig-real-image {
            max-width: 90%;
            max-height: 70px;
            object-fit: contain
        }

        .sig-approval-names td {
            height: 50px;
            font-size: 10px
        }

        .sig-approval-names strong,
        .sig-approval-names small {
            display: block;
            overflow-wrap: anywhere
        }

        .sig-approval-names small {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px
        }

        .sig-tap {
            background: #50b95a;
            color: #fff;
            border: 0;
            padding: 8px 12px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer
        }

        .sig-tap:disabled {
            opacity: .5
        }

        .sig-wait {
            color: #94a3b8
        }

        .sig-supplier {
            font-weight: 700
        }

        .sig-inline-remark {
            padding: 8px 10px;
            background: #f8fafc
        }

        .sig-inline-remark input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d8dee8;
            border-radius: 4px;
            font-size: 11px
        }

        /* === Document boundaries and signing motion === */
        .sig-wrap {
            max-width: 1560px;
            padding-bottom: 70px
        }

        .sig-doc {
            position: relative;
            margin-bottom: 38px !important;
            border: 1px solid #bac9d9 !important;
            border-left: 5px solid #26394e !important;
            border-radius: 9px !important;
            box-shadow: 0 8px 24px rgba(22, 39, 60, .11) !important;
            transform: translateX(0);
            opacity: 1;
            transition: transform .65s cubic-bezier(.2, .75, .25, 1), opacity .5s ease, box-shadow .25s ease;
            will-change: transform, opacity
        }

        .sig-doc:hover {
            box-shadow: 0 12px 30px rgba(22, 39, 60, .15) !important
        }

        .sig-doc-head {
            background: #26394e !important;
            color: #fff !important;
            padding: 13px 17px !important;
            position: relative
        }

        .sig-doc-head strong {
            color: #fff !important;
            font-size: 13px !important;
            letter-spacing: .2px
        }

        .sig-doc-head span {
            color: #fff !important;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .2);
            padding: 6px 9px;
            border-radius: 5px
        }

        .sig-doc-ordinal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #50b95a;
            color: #fff;
            padding: 5px 9px;
            margin-right: 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 800
        }

        .sig-doc-end {
            background: #eef2f6;
            border-top: 1px solid #d3deea;
            padding: 10px 16px;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #5e7085
        }

        .sig-next-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: -20px 0 21px;
            font-size: 10px;
            letter-spacing: .8px;
            font-weight: 800;
            color: #667b91
        }

        .sig-next-divider:before,
        .sig-next-divider:after {
            content: '';
            height: 1px;
            background: #bfcbd8;
            flex: 1
        }

        .sig-tap {
            transition: transform .2s, box-shadow .2s, background .2s;
            animation: sigTapPulse 1.9s ease-in-out infinite
        }

        .sig-tap:hover {
            transform: translateY(-2px) scale(1.06);
            box-shadow: 0 5px 15px rgba(80, 185, 90, .35)
        }

        .sig-tap:focus-visible {
            outline: 3px solid #26394e;
            outline-offset: 3px
        }

        .sig-tap:disabled {
            animation: none
        }

        @keyframes sigTapPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(80, 185, 90, .36)
            }

            50% {
                box-shadow: 0 0 0 7px rgba(80, 185, 90, 0)
            }
        }

        .sig-doc.is-signing {
            box-shadow: 0 0 0 3px rgba(80, 185, 90, .22) !important
        }

        .sig-doc.is-signed {
            transform: translateX(115%);
            opacity: 0;
            pointer-events: none
        }

        .sig-success-toast {
            position: fixed;
            top: 85px;
            right: 22px;
            z-index: 99999;
            background: #187844;
            color: #fff;
            padding: 13px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 12px 30px #0f51323d;
            animation: sigToastIn .3s ease-out
        }

        @keyframes sigToastIn {
            from {
                opacity: 0;
                transform: translateY(-12px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @media(prefers-reduced-motion:reduce) {

            .sig-doc,
            .sig-tap {
                animation: none !important;
                transition: none !important
            }
        }

        @media(max-width:760px) {
            .sig-doc-head {
                flex-wrap: wrap
            }

            .sig-doc-ordinal {
                margin-bottom: 4px
            }
        }

        .sig-grand-total td {
            background: #edf7ee !important;
            color: #1d4930 !important;
            font-weight: 800 !important;
            padding: 12px 8px !important;
            border-top: 2px solid #50b95a !important
        }

        .sig-grand-total td:first-child {
            text-align: right;
            letter-spacing: .4px
        }

        .sig-grand-total td:nth-child(2) {
            white-space: nowrap;
            font-variant-numeric: tabular-nums
        }

        .sig-sign-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            flex-wrap: wrap
        }

        .sig-reject {
            background: #dc3545;
            border: 0;
            color: #fff;
            border-radius: 4px;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .2s, background .2s
        }

        .sig-reject:hover {
            background: #b91c1c;
            transform: translateY(-1px)
        }

        .sig-doc.is-rejected {
            transform: translateX(115%);
            opacity: 0;
            pointer-events: none;
            transition: transform .65s ease, opacity .55s ease
        }

        .sig-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px
        }

        .sig-modal-overlay[hidden] {
            display: none !important
        }

        .sig-modal-card {
            width: min(520px, 100%);
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 24px 60px #0003
        }

        .sig-modal-head {
            background: #26394e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            font-size: 15px
        }

        .sig-modal-head button {
            border: 0;
            background: transparent;
            color: white;
            font-size: 25px;
            cursor: pointer
        }

        .sig-modal-body {
            padding: 20px
        }

        .sig-modal-body p {
            margin: 0 0 16px
        }

        .sig-modal-body label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px
        }

        .sig-modal-body textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            font: inherit;
            resize: vertical
        }

        .sig-modal-body small {
            display: block;
            color: #b91c1c;
            margin-top: 8px
        }

        .sig-modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0
        }

        .sig-modal-foot button {
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 9px 16px;
            background: white;
            cursor: pointer;
            font-weight: 700
        }

        #sig-reject-submit {
            background: #dc3545;
            border-color: #dc3545;
            color: white
        }

        #sig-reject-submit:disabled {
            opacity: .6;
            cursor: wait
        }

        /* Filters for cutoff date and exact division */
        .sig-filter-form {
            display: grid;
            grid-template-columns: minmax(200px, 2fr) minmax(120px, 1fr) minmax(140px, 1fr) minmax(140px, 1fr) auto auto;
            gap: 9px;
            align-items: end;
            margin-bottom: 14px;
            background: #fff;
            border: 1px solid #d8dee8;
            border-radius: 7px;
            padding: 12px
        }

        .sig-filter-form label {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            display: flex;
            flex-direction: column;
            gap: 5px
        }

        .sig-filter-form .sig-search {
            margin: 0;
            height: 35px;
            width: 100%
        }

        .sig-filter-input {
            height: 35px;
            width: 100%;
            border: 1px solid #d8dee8;
            border-radius: 5px;
            background: #fff;
            padding: 0 9px;
            font-size: 11px;
            color: #26374d
        }

        .sig-filter-submit,
        .sig-filter-reset {
            height: 35px;
            padding: 0 13px;
            border-radius: 5px;
            border: 0;
            background: #26394e;
            color: white;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none
        }

        .sig-filter-reset {
            background: #e2e8f0;
            color: #26394e
        }

        @media(max-width:1000px) {
            .sig-filter-form {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }
        }

        @media(max-width:550px) {
            .sig-filter-form {
                grid-template-columns: 1fr
            }
        }

        /* Payment Request — read-only table matching SPK index */
        .sig-bottom>.sig-payment-panel {
            padding: 0 !important;
            min-width: 0
        }

        .sig-bottom>.sig-payment-panel>b {
            margin: 0 !important
        }

        .sig-payment-scroll {
            overflow-x: auto;
            width: 100%;
            scrollbar-width: thin
        }

        .sig-payment-table {
            width: 100%;
            min-width: 590px;
            border-collapse: collapse;
            table-layout: auto;
            font-size: 10px
        }

        .sig-payment-table th {
            background: #e9f5eb;
            color: #244b31;
            border: 1px solid #cbded0;
            padding: 8px 6px;
            text-align: center;
            font-weight: 800;
            white-space: nowrap
        }

        .sig-payment-table td {
            border: 1px solid #d8e2ea;
            padding: 9px 7px;
            vertical-align: middle;
            color: #26374d;
            background: white
        }

        .sig-payment-table tbody tr:nth-child(even) td {
            background: #f8fafc
        }

        .sig-payment-table tbody tr:hover td {
            background: #f1f8f3
        }

        .sig-payment-table .sig-pay-money {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            font-weight: 700
        }

        .sig-payment-table .sig-pay-center {
            text-align: center;
            white-space: nowrap
        }

        .sig-payment-table .sig-pay-description {
            min-width: 95px;
            overflow-wrap: anywhere
        }

        .sig-pay-request {
            color: #15803d;
            font-size: 14px;
            font-weight: 900
        }

        .sig-pay-muted {
            color: #94a3b8
        }

        .sig-pay-note {
            display: inline-block;
            background: #edf2f8;
            border: 1px solid #dce4ed;
            border-radius: 3px;
            padding: 3px 5px;
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap
        }

        .sig-payment-table tfoot td {
            background: #eaf6ed !important;
            border-top: 2px solid #50b95a;
            font-weight: 800;
            color: #174a2c
        }

        .sig-payment-table .sig-pay-empty {
            text-align: center;
            padding: 18px;
            color: #94a3b8
        }
    </style>
    <div class="sig-wrap">
        <div class="sig-top">
            <div>
                <h2>SPK Signature Center</h2>
                <div>Antrean tanda tangan dan monitoring SPK {{ auth()->user()->name }}</div>
            </div><strong id="sig-count">{{ $documents->count() }} Pending</strong>
        </div>
        <form method="GET" class="sig-filter-form">
            <label>Cari SPK / PO / Supplier<input class="sig-search" name="q" value="{{ $search }}"
                    placeholder="Cari nomor SPK, PO, atau supplier..." /></label>
            <label>Divisi
                <select name="key" class="sig-filter-input">
                    @foreach (['all' => 'Semua divisi', 'nw' => 'NW', 'nwd' => 'NWD', 'nws' => 'NWS', 'nwr' => 'NWR'] as $code => $label)
                        <option value="{{ $code }}" @selected($key === $code)>{{ $label }}</option>
                    @endforeach
                    @if (!in_array($key, ['all', 'nw', 'nwd', 'nws', 'nwr'], true))
                        <option value="{{ $key }}" selected>{{ strtoupper($key) }}</option>
                    @endif
                </select>
            </label>
            <label>Tanggal terima mulai<input type="date" name="date_from" class="sig-filter-input"
                    value="{{ $dateFrom }}"></label>
            <button type="submit" class="sig-filter-submit">Terapkan</button>
            <a class="sig-filter-reset" href="{{ url('/produksi/inventor/signature') }}">Reset</a>
        </form>
        @forelse($documents as $doc)
            @php
                $d = $doc['data'];
                $s = $doc['signature'];
                $model = $doc['spk'];
                $type = $doc['type'];
                $stage = $doc['stage'];
                $isMaker = $doc['isMaker'];
                $items = $d['items'] ?? [];
                if (!is_array($items)) {
                    $items = [];
                }
                $supplier = $d['nama'] ?? ($d['sup'] ?? '-');
                $companyCode = strtoupper((string) ($doc['company'] ?? ''));
                if (
                    !$companyCode &&
                    preg_match(
                        '/(?<![A-Z])NW[A-Z]*(?![A-Z])/i',
                        (string) (($d['no_po'] ?? '') . ' ' . ($d['no_spk'] ?? '')),
                        $companyMatch,
                    )
                ) {
                    $companyCode = strtoupper($companyMatch[0]);
                }
                $displayMakerName = match ($companyCode) {
                    'NW', 'NWR' => 'Nur Khasanah',
                    'NWD', 'NWS' => 'Intan',
                    default => $s->madeBy?->name ?? 'Belum ditentukan',
                };
                $payments = $d['payments'] ?? [];
                if (!is_array($payments)) {
                    $payments = [];
                }

                $rupiah = function ($value) {
                    if (is_array($value) || is_object($value)) {
                        return 'Rp 0';
                    }
                    $value = trim((string) ($value ?? 0));
                    $value = preg_replace('/[^0-9,.\-]/', '', $value);
                    if (str_contains($value, ',') && str_contains($value, '.')) {
                        $value =
                            strrpos($value, ',') > strrpos($value, '.')
                                ? str_replace(',', '.', str_replace('.', '', $value))
                                : str_replace(',', '', $value);
                    } elseif (str_contains($value, ',')) {
                        $parts = explode(',', $value);
                        $value =
                            count($parts) === 2 && strlen($parts[1]) <= 2
                                ? str_replace(',', '.', $value)
                                : str_replace(',', '', $value);
                    } elseif (substr_count($value, '.') === 1) {
                        $parts = explode('.', $value);
                        if (strlen($parts[1]) === 3) {
                            $value = str_replace('.', '', $value);
                        }
                    } elseif (substr_count($value, '.') > 1) {
                        $value = str_replace('.', '', $value);
                    }
                    return 'Rp ' . number_format((float) $value, 0, ',', '.');
                };
                $asNumber = function ($value) {
                    if (!is_scalar($value)) {
                        return 0;
                    }
                    $v = preg_replace('/[^0-9,.\-]/', '', (string) $value);
                    if (str_contains($v, ',') && str_contains($v, '.')) {
                        $v =
                            strrpos($v, ',') > strrpos($v, '.')
                                ? str_replace(',', '.', str_replace('.', '', $v))
                                : str_replace(',', '', $v);
                    } elseif (str_contains($v, ',')) {
                        $p = explode(',', $v);
                        $v =
                            count($p) === 2 && strlen($p[1]) <= 2
                                ? str_replace(',', '.', $v)
                                : str_replace(',', '', $v);
                    } elseif (substr_count($v, '.') === 1) {
                        $p = explode('.', $v);
                        if (strlen($p[1]) === 3) {
                            $v = str_replace('.', '', $v);
                        }
                    } elseif (substr_count($v, '.') > 1) {
                        $v = str_replace('.', '', $v);
                    }
                    return (float) $v;
                };
                $paymentTotal = 0;
                foreach ($payments as $payment) {
                    if (is_array($payment)) {
                        $paymentTotal += $asNumber($payment['amount'] ?? 0);
                    }
                }
                $grandTotal = 0;
                foreach ($items as $it) {
                    $line = $asNumber($it['total'] ?? 0);
                    $grandTotal += $line ?: $asNumber($it['harga'] ?? 0) * $asNumber($it['qty'] ?? ($it['pcs'] ?? 0));
                }

                $special = (bool) preg_match(
                    '/\bNWS\b|\bNWD\b/i',
                    (string) (($d['no_po'] ?? '') . ' ' . ($d['no_spk'] ?? '')),
                );
                $signSlots = [
                    ['Made By', $s->madeBy, $s->made_by, $s->made_at, 'made'],
                    ['Checker 1', $s->checkedBy, $s->checked_by, $special ? 'skip' : $s->checked_at, 'checked'],
                    ['Checker 2', $s->checkedBy2, $s->checked_by_2, $s->checked_at_2, 'checked_2'],
                    ['Approved By', $s->approvedBy, $s->approved_by, $s->approved_at, 'approved'],
                ];
            @endphp
            <article class="sig-doc" id="spk-{{ $model->id }}" data-spk-id="{{ $model->id }}">
                <div class="sig-doc-head">
                    <div><span class="sig-doc-ordinal">SPK
                            #{{ $loop->iteration }}</span><strong>{{ $d['no_spk'] ?? 'SPK #' . $model->id }}</strong></div>
                    <span>Menunggu:
                        {{ ['checked' => 'Checker 1', 'checked_2' => 'Checker 2', 'approved' => 'Approved By'][$stage] ?? 'Menunggu' }}
                        @if ($isMaker && !$type)
                            · Monitoring Maker
                        @endif
                    </span>
                </div>
                <div class="sig-paper">
                    <div class="sig-title">DETAIL ITEM SPK / SURAT PERINTAH KERJA</div>
                    <div class="sig-info">
                        <div><b>No SPK</b> {{ $d['no_spk'] ?? '-' }}</div>
                        <div><b>No PO</b> {{ $d['no_po'] ?? '-' }}</div>
                        <div><b>Nama Supplier</b> {{ $supplier }}</div>
                        <div><b>Kategori</b> {{ $d['kategori'] ?? '-' }}</div>
                        <div><b>Tanggal Terima</b> {{ $d['tgl_terima'] ?? '-' }}</div>
                        <div><b>Tanggal Selesai</b> {{ $d['tgl_selesai'] ?? '-' }}</div>
                    </div>
                    <table class="sig-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Foto</th>
                                <th>Nama Item</th>
                                <th>P</th>
                                <th>L</th>
                                <th>T</th>
                                <th>Material</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Total</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $photos = $item['images'] ?? [];
                                    if (is_string($photos)) {
                                        $photos = [$photos];
                                    }
                                    $firstPhoto = is_array($photos) ? reset($photos) : null;
                                @endphp
                                <tr>
                                    <td>{{ $item['kode'] ?? '-' }}</td>
                                    <td>
                                        @if (is_string($firstPhoto) && $firstPhoto !== '')
                                            <img src="{{ $firstPhoto }}" alt="Foto item">
                                        @endif
                                    </td>
                                    <td>{{ $item['nama'] ?? '-' }}</td>
                                    <td>{{ $item['p'] ?? '-' }}</td>
                                    <td>{{ $item['l'] ?? '-' }}</td>
                                    <td>{{ $item['t'] ?? '-' }}</td>
                                    <td>{{ is_scalar($item['material'] ?? null) ? $item['material'] : '-' }}</td>
                                    <td>{{ $item['qty'] ?? '-' }}</td>
                                    <td>{{ $rupiah($item['harga'] ?? 0) }}</td>
                                    <td>{{ $rupiah($item['total'] ?? $asNumber($item['harga'] ?? 0) * $asNumber($item['qty'] ?? ($item['pcs'] ?? 0))) }}
                                    </td>
                                    <td>{{ is_scalar($item['catatan'] ?? null) ? $item['catatan'] : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="sig-grand-total">
                                <td colspan="9">GRAND TOTAL SPK</td>
                                <td>{{ $rupiah($grandTotal) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="sig-bottom">
                        <div><b>Terms & Conditions</b>
                            <p>{{ is_scalar($d['terms'] ?? null) ? $d['terms'] : 'Mengikuti ketentuan SPK yang berlaku.' }}</p>
                        </div>
                        <div class="sig-payment-panel">
                            <b>Payment Request / Schedule</b>
                            <div class="sig-payment-scroll">
                                <table class="sig-payment-table">
                                    <thead>
                                        <tr>
                                            <th style="width:34px">No</th>
                                            <th style="width:38px">Req</th>
                                            <th>Amount</th>
                                            <th style="width:95px">Date</th>
                                            <th style="width:90px">Note</th>
                                            <th>Keterangan</th>
                                            <th>Adj Finance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $payment)
                                            @php
                                                $pay = is_array($payment) ? $payment : [];
                                                $payAmount = $pay['amount'] ?? 0;
                                                $payAdjustment =
                                                    $pay['adjustment'] ?? ($pay['payment_request_amount'] ?? 0);
                                                $payNote = (string) ($pay['note'] ?? ($pay['type'] ?? '-'));
                                                $payDescription =
                                                    (string) ($pay['note_tambahan'] ?? ($pay['keterangan'] ?? '-'));
                                                $payDate = (string) ($pay['date'] ?? '-');
                                            @endphp
                                            <tr>
                                                <td class="sig-pay-center">{{ $loop->iteration }}</td>
                                                <td class="sig-pay-center">
                                                    @if (!empty($pay['is_request']))
                                                    <span class="sig-pay-request">✓</span>@else<span
                                                            class="sig-pay-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="sig-pay-money">{{ $rupiah($payAmount) }}</td>
                                                <td class="sig-pay-center">{{ $payDate }}</td>
                                                <td><span class="sig-pay-note">{{ strtoupper($payNote) }}</span></td>
                                                <td class="sig-pay-description">{{ $payDescription }}</td>
                                                <td class="sig-pay-money">{{ $rupiah($payAdjustment) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="sig-pay-empty">Belum ada jadwal pembayaran</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">TOTAL PAYMENT</td>
                                            <td class="sig-pay-money">{{ $rupiah($paymentTotal) }}</td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <section class="sig-approval">
                        <div class="sig-approval-head">Approval SPK</div>
                        <table class="sig-approval-table">
                            <thead>
                                <tr>
                                    <th>Made By</th>
                                    <th colspan="2">Checked By</th>
                                    <th>Approved By</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="sig-approval-images">
                                    @foreach ($signSlots as $slot)
                                        @php
                                            $signed = $slot[3] && $slot[3] !== 'skip';
                                            $canSign = $type === $slot[4] && !$signed;
                                        @endphp
                                        <td @if ($slot[3] === 'skip') class="sig-skip" @endif>
                                            @if ($slot[3] === 'skip')
                                                <span>—</span>
                                            @elseif($signed)
                                                <img class="sig-real-image"
                                                    src="{{ asset('assets/signature/' . $slot[2] . '.png') }}"
                                                    alt="Tanda tangan {{ $slot[1]?->name ?? '' }}"
                                                    onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
                                                <span style="display:none;color:#25844b">✓ Signed</span>
                                            @elseif($canSign)
                                                <div class="sig-sign-buttons"><button type="button"
                                                        class="sig-approve sig-tap" data-id="{{ $model->id }}"
                                                        data-type="{{ $slot[4] }}">TAP TO SIGN</button><button
                                                        type="button" class="sig-reject" data-id="{{ $model->id }}"
                                                        data-type="{{ $slot[4] }}">REJECT</button></div>
                                            @else
                                                <span class="sig-wait">Pending</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="sig-supplier">{{ $s->supplier?->name ?? $supplier }}</td>
                                </tr>
                                <tr class="sig-approval-names">
                                    @foreach ($signSlots as $slot)
                                        <td><strong>{{ $slot[3] === 'skip' ? '—' : ($slot[4] === 'made' ? $displayMakerName : $slot[1]?->name ?? 'Belum ditentukan') }}</strong>
                                            <small>{{ $slot[3] === 'skip' ? 'Tidak diperlukan (NWS/NWD)' : $slot[1]?->karyawan?->divisi?->nama ?? '' }}</small>
                                            <small>{{ $slot[3] === 'skip' ? '' : ($slot[3] ? $slot[3]->format('d/m/Y H:i') : 'Pending') }}</small>
                                        </td>
                                    @endforeach
                                    <td><strong>{{ $s->supplier?->name ?? $supplier }}</strong><small>Supplier</small></td>
                                </tr>
                            </tbody>
                        </table>
                        @if ($type)
                            <div class="sig-inline-remark"><input class="sig-remark" maxlength="2000"
                                    placeholder="Remark tanda tangan (opsional)"></div>
                        @endif
                    </section>
                </div>
                <div class="sig-doc-end">AKHIR DOKUMEN SPK #{{ $loop->iteration }}</div>
            </article>
            @unless ($loop->last)
                <div class="sig-next-divider">SPK BERIKUTNYA</div>
            @endunless
        @empty <div class="sig-empty">Tidak ada SPK yang menunggu tanda tangan Anda.</div>
        @endforelse
    </div>
    <div id="sig-reject-modal" class="sig-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="sig-reject-title"
        hidden>
        <div class="sig-modal-card">
            <div class="sig-modal-head"><strong id="sig-reject-title">Reject SPK With Comment's</strong><button type="button"
                    id="sig-reject-close" aria-label="Tutup">×</button></div>
            <div class="sig-modal-body">
                <p>Comments here <strong id="sig-reject-number"></strong>.</p><label
                    for="sig-reject-message">reason <span style="color:#dc2626">*</span></label>
                <textarea id="sig-reject-message" rows="5" maxlength="2000"
                    placeholder="Jelaskan bagian yang harus diperbaiki..." required></textarea><small id="sig-reject-error" role="alert"></small>
            </div>
            <div class="sig-modal-foot"><button type="button" id="sig-reject-cancel">Batal</button><button
                    type="button" id="sig-reject-submit">Kirim Reject</button></div>
        </div>
    </div>
    <script>
        (function() {
            const list = document.querySelector('.sig-wrap');
            const countLabel = document.getElementById('sig-count');

            function updateCount() {
                const count = list.querySelectorAll('.sig-doc').length;
                countLabel.textContent = count + ' Pending';
                if (count === 0 && !list.querySelector('.sig-empty')) {
                    const empty = document.createElement('div');
                    empty.className = 'sig-empty';
                    empty.textContent = 'Tidak ada SPK yang menunggu tanda tangan Anda.';
                    list.appendChild(empty);
                }
            }

            function notify(message) {
                const old = document.querySelector('.sig-success-toast');
                if (old) old.remove();
                const toast = document.createElement('div');
                toast.className = 'sig-success-toast';
                toast.setAttribute('role', 'status');
                toast.textContent = '✓ ' + message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3500);
            }

            function tidySeparators() {
                const docs = [...list.querySelectorAll('.sig-doc')];
                list.querySelectorAll('.sig-next-divider').forEach(el => el.remove());
                docs.forEach((doc, index) => {
                    const badge = doc.querySelector('.sig-doc-ordinal');
                    const end = doc.querySelector('.sig-doc-end');
                    if (badge) badge.textContent = 'SPK #' + (index + 1);
                    if (end) end.textContent = 'AKHIR DOKUMEN SPK #' + (index + 1);
                    if (index < docs.length - 1) {
                        const divider = document.createElement('div');
                        divider.className = 'sig-next-divider';
                        divider.textContent = 'SPK BERIKUTNYA';
                        doc.after(divider);
                    }
                });
            }
            const modal = document.getElementById('sig-reject-modal');
            const message = document.getElementById('sig-reject-message');
            const error = document.getElementById('sig-reject-error');
            const submit = document.getElementById('sig-reject-submit');
            let rejectTarget = null;

            function closeReject() {
                modal.hidden = true;
                rejectTarget = null;
                message.value = '';
                error.textContent = '';
            }
            ['sig-reject-close', 'sig-reject-cancel'].forEach(id => document.getElementById(id).addEventListener(
                'click', closeReject));
            modal.addEventListener('click', e => {
                if (e.target === modal) closeReject()
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && !modal.hidden) closeReject()
            });
            list.addEventListener('click', event => {
                const btn = event.target.closest('.sig-reject');
                if (!btn) return;
                rejectTarget = {
                    id: btn.dataset.id,
                    type: btn.dataset.type,
                    doc: btn.closest('.sig-doc')
                };
                document.getElementById('sig-reject-number').textContent = rejectTarget.doc.querySelector(
                    '.sig-doc-head strong')?.textContent || '';
                modal.hidden = false;
                message.focus();
            });
            submit.addEventListener('click', async () => {
                if (!rejectTarget) return;
                const reason = message.value.trim();
                if (reason.length < 5) {
                    error.textContent = 'Alasan reject wajib diisi minimal 5 karakter.';
                    message.focus();
                    return;
                }
                submit.disabled = true;
                submit.textContent = 'MENGIRIM...';
                error.textContent = '';
                const {
                    id,
                    type,
                    doc
                } = rejectTarget;
                try {
                    const response = await fetch(@json(url('/produksi/inventor/signature')) + '/' + encodeURIComponent(
                        id) + '/reject', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || @json(csrf_token())
                            },
                            body: JSON.stringify({
                                type,
                                message: reason
                            })
                        });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw Error(result.message ||
                        'Reject gagal disimpan');
                    closeReject();
                    notify('SPK berhasil di-reject dan alasan tersimpan');
                    const remove = () => {
                        doc.remove();
                        tidySeparators();
                        updateCount()
                    };
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) remove();
                    else {
                        doc.classList.add('is-rejected');
                        setTimeout(remove, 680)
                    }
                } catch (e) {
                    error.textContent = e.message
                } finally {
                    submit.disabled = false;
                    submit.textContent = 'Kirim Reject'
                }
            });
            list.addEventListener('click', async event => {
                const btn = event.target.closest('.sig-approve');
                if (!btn) return;
                const doc = btn.closest('.sig-doc');
                if (!doc || btn.disabled) return;
                if (!confirm('Tanda tangani SPK ini?')) return;
                const id = btn.dataset.id,
                    type = btn.dataset.type;
                btn.disabled = true;
                btn.textContent = 'MENYIMPAN...';
                doc.classList.add('is-signing');
                try {
                    const response = await fetch(@json(url('/produksi/inventor/signature')) + '/' + encodeURIComponent(
                        id) + '/approve', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || @json(csrf_token())
                            },
                            body: JSON.stringify({
                                type,
                                remark: doc.querySelector('.sig-remark')?.value || ''
                            })
                        });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw Error(result.message ||
                        'Gagal menyimpan tanda tangan');
                    notify('Tanda tangan berhasil disimpan');
                    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const removeDoc = () => {
                        doc.remove();
                        tidySeparators();
                        updateCount()
                    };
                    if (reduceMotion) {
                        removeDoc();
                        return
                    }
                    doc.classList.remove('is-signing');
                    doc.classList.add('is-signed');
                    setTimeout(removeDoc, 680);
                } catch (e) {
                    doc.classList.remove('is-signing');
                    btn.disabled = false;
                    btn.textContent = 'TAP TO SIGN';
                    alert(e.message);
                }
            });
        })();
    </script>
@endsection
