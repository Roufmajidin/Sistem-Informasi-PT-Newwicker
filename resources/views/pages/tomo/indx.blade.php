@extends('master.master')

@section('title', 'Dashboard Pengajuan Tomo')

<style>
    .tomo-page {

        background: #f5f7fb;

        *min-height*: 85vh;

        padding: 12px 8px;

        color: #172b4d
    }



    .tomo-wrap {

        *max-width*: 1050px;

        margin: auto
    }



    .tomo-title {

        background: #e7f0ff;

        *border-radius*: 14px;

        padding: 15px;

        *margin-bottom*: 10px
    }



    .tomo-title h2 {

        *font-size*: 21px;

        *font-weight*: 800;

        margin: 0 0 5px
    }



    .tomo-title p {

        *font-size*: 13px;

        color: #475569;

        margin: 0;

        *line-height*: 1.5
    }



    .tomo-sticky {

        position: sticky;

        top: 52px;

        *z-index*: 90;

        background: #f5f7fb;

        padding: 7px 0
    }



    .tomo-search {

        display: flex;

        gap: 7px
    }



    .tomo-search input {

        *min-width*: 0;

        flex: 1;

        height: 37px;

        padding: 6px 10px;

        border: 1px solid #cbd5e1;

        *border-radius*: 9px;

        *font-size*: 14px
    }



    .tomo-btn {

        background: #1555b2;

        color: white;

        border: 0;

        *border-radius*: 9px;

        padding: 9px 14px;

        *font-weight*: 750;

        cursor: pointer
    }



    .tomo-tabs {

        display: flex;

        gap: 7px;

        *overflow-x*: auto;

        margin: 10px 0 13px
    }



    .tomo-tab {

        border: 1px solid #cbd5e1;

        background: white;

        *border-radius*: 9px;

        padding: 10px 12px;

        *white-space*: nowrap;

        *font-weight*: 700;

        cursor: pointer
    }



    .tomo-tab.active {

        background: #1555b2;

        color: white;

        *border-color*: #1555b2
    }



    .tomo-po {

        background: white;

        border: 1px solid #dce4ef;

        *border-radius*: 14px;

        overflow: hidden;

        *margin-bottom*: 14px
    }



    .tomo-po-head {

        background: #dce8fb;

        padding: 12px 14px;

        display: flex;

        *justify-content*: space-between;

        *align-items*: center
    }



    .tomo-po-head strong {

        *font-size*: 17px
    }



    .tomo-buyer {

        color: #475569;

        *font-size*: 12px;

        *margin-top*: 3px
    }



    .tomo-items {

        padding: 9px;

        display: grid;

        gap: 8px
    }



    .tomo-item {

        display: grid;

        *grid-template-columns*: 86px minmax(0, 1fr);

        gap: 10px;

        padding: 9px;

        border: 1px solid #e2e8f0;

        *border-radius*: 12px;

        background: white
    }



    .tomo-image {

        width: 86px;

        height: 86px;

        *object-fit*: contain;

        background: white;

        border: 1px solid #e2e8f0;

        *border-radius*: 9px
    }



    .tomo-photo {

        border: 0;

        background: none;

        padding: 0;

        cursor: zoom-in
    }



    .tomo-name {

        *font-weight*: 800;

        *font-size*: 15px;

        *line-height*: 1.3;

        *overflow-wrap*: anywhere
    }



    .tomo-meta {

        *font-size*: 12px;

        color: #64748b;

        *margin-top*: 3px
    }



    .tomo-actions {

        display: flex;

        *flex-wrap*: wrap;

        *align-items*: center;

        gap: 7px;

        *margin-top*: 8px
    }



    .tomo-ajukan {

        background: #f59e0b;

        border: 0;

        *border-radius*: 9px;

        padding: 9px 16px;

        *font-weight*: 800;

        color: #172033;

        cursor: pointer
    }



    .tomo-detail {

        background: #e9f1ff;

        color: #134d9e;

        border: 1px solid #bad0f4;

        *border-radius*: 9px;

        padding: 8px 12px;

        *font-weight*: 800;

        cursor: pointer
    }



    .tomo-qty {

        *margin-left*: auto;

        background: #e6f4e4;

        color: #22642b;

        padding: 6px 10px;

        *border-radius*: 9px;

        *text-align*: center;

        *font-size*: 11px
    }



    .tomo-qty b {

        display: block;

        *font-size*: 19px
    }



    .tomo-spk {

        background: #f1f5f9;

        padding: 6px 8px;

        *border-radius*: 7px;

        *margin-top*: 6px;

        display: flex;

        *justify-content*: space-between;

        gap: 8px;

        *font-size*: 12px
    }



    .tomo-spk strong {

        color: #166534
    }



    .tomo-status {

        display: inline-block;

        *border-radius*: 20px;

        padding: 5px 9px;

        *font-weight*: 800;

        *font-size*: 12px
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

        *text-align*: center;

        color: #64748b;

        background: white;

        *border-radius*: 12px
    }



    .tomo-modal {

        display: none;

        position: fixed;

        inset: 0;

        *z-index*: 99999;

        background: #0f172ab8;

        *align-items*: center;

        *justify-content*: center;

        padding: 6px
    }



    .tomo-modal.open {

        display: flex
    }



    .tomo-dialog {

        width: min(100%, 720px);

        *max-height*: calc(100dvh - 12px);

        background: white;

        *border-radius*: 15px;

        display: flex;

        *flex-direction*: column;

        overflow: hidden
    }



    .tomo-dialog-head {

        display: flex;

        *justify-content*: space-between;

        *align-items*: center;

        gap: 10px;

        padding: 12px;

        *border-bottom*: 1px solid #e2e8f0
    }



    .tomo-dialog-head h3 {

        margin: 0;

        *font-size*: 17px;

        *font-weight*: 800
    }



    .tomo-x {

        width: 40px;

        height: 40px;

        border: 0;

        background: #eef2f7;

        *border-radius*: 9px;

        *font-size*: 24px;

        cursor: pointer
    }



    .tomo-dialog-body {

        overflow: auto;

        padding: 11px;

        *min-height*: 0
    }



    .tomo-dialog-foot {

        padding: 10px;

        *border-top*: 1px solid #e2e8f0;

        display: flex;

        gap: 8px;

        *justify-content*: flex-end
    }



    .tomo-product {

        display: flex;

        gap: 10px;

        *align-items*: center;

        background: #f1f6fd;

        *border-radius*: 11px;

        padding: 8px;

        *margin-bottom*: 12px
    }



    .tomo-product img {

        width: 86px;

        height: 80px;

        *object-fit*: contain;

        background: white;

        *border-radius*: 8px;

        cursor: zoom-in
    }



    .tomo-product strong {

        *font-size*: 14px
    }



    .tomo-product small {

        display: block;

        color: #64748b;

        *margin-top*: 3px
    }



    .tomo-table {

        width: 100%;

        *border-collapse*: collapse;

        *table-layout*: fixed
    }



    .tomo-table th {

        background: #e9eff7;

        *font-size*: 11px;

        padding: 8px 2px
    }



    .tomo-table td {

        *border-bottom*: 1px solid #e5e7eb;

        padding: 5px 2px;

        *font-size*: 12px
    }



    .tomo-table td:first-child {

        *font-weight*: 750;

        *overflow-wrap*: anywhere
    }



    .tomo-cost-input {

        width: 100%;

        height: 40px;

        border: 1px solid #cbd5e1;

        *border-radius*: 7px;

        padding: 4px;

        *font-size*: 16px;

        *text-align*: right;

        *box-sizing*: border-box
    }



    .tomo-result {

        *text-align*: right;

        *font-weight*: 750;

        color: #166534;

        *overflow-wrap*: anywhere
    }



    .tomo-total {

        position: sticky;

        bottom: 0;

        display: flex;

        *justify-content*: space-between;

        background: #e3f5e6;

        border: 1px solid #bbdfc1;

        *border-radius*: 10px;

        padding: 12px;

        *font-weight*: 850;

        *margin-top*: 9px
    }



    .tomo-note {

        display: block;

        width: 100%;

        *min-height*: 60px;

        border: 1px solid #cbd5e1;

        *border-radius*: 9px;

        padding: 10px;

        *font-size*: 14px;

        *box-sizing*: border-box;

        *margin-top*: 6px
    }



    .tomo-history-card {

        border: 1px solid #d7e2f2;

        *border-radius*: 11px;

        padding: 12px;

        margin: 8px 0;

        background: white
    }



    .tomo-history-card h4 {

        margin: 0 0 7px;

        *font-size*: 15px
    }



    .tomo-history-card p {

        margin: 4px 0;

        *font-size*: 13px
    }



    .tomo-alert {

        background: #fff7e6;

        border: 1px solid #fcd98a;

        padding: 10px;

        *border-radius*: 9px;

        *font-size*: 12px;

        margin: 9px 0
    }



    .tomo-zoom {

        display: none;

        position: fixed;

        inset: 0;

        background: #0f172af2;

        *z-index*: 100001;

        *align-items*: center;

        *justify-content*: center;

        *flex-direction*: column;

        gap: 15px;

        padding: 12px
    }



    .tomo-zoom.open {

        display: flex
    }



    .tomo-zoom img {

        *max-width*: 94vw;

        *max-height*: 75vh;

        *object-fit*: contain;

        transform: scale(var(--scale, 1))
    }



    .tomo-zoom button {

        padding: 10px 15px;

        border: 0;

        *border-radius*: 9px;

        *font-weight*: 800
    }



    .tomo-history-list {

        display: grid;

        gap: 8px
    }



    .tomo-history-entry {

        border: 1px solid #dbe4ef;

        background: white;

        *border-radius*: 12px;

        padding: 12px;

        display: flex;

        *align-items*: center;

        *justify-content*: space-between;

        gap: 10px
    }



    .tomo-history-entry strong {

        *font-size*: 14px
    }



    .tomo-history-entry small {

        display: block;

        color: #64748b;

        *margin-top*: 4px
    }



    .tomo-hidden {

        display: none !important
    }



    .tomo-help {

        *font-size*: 12px;

        color: #64748b;

        *margin-top*: 8px
    }



    @media(*max-width*:500px) {

        .tomo-title h2 {

            *font-size*: 19px
        }



        .tomo-item {

            *grid-template-columns*: 75px minmax(0, 1fr)
        }



        .tomo-image {

            width: 75px;

            height: 75px
        }



        .tomo-name {

            *font-size*: 14px
        }



        .tomo-dialog-body {

            padding: 8px
        }



        .tomo-table td {

            *font-size*: 11px
        }



        .tomo-table th {

            *font-size*: 10px
        }



        .tomo-cost-input {

            height: 39px;

            padding: 2px;

            *font-size*: 15px
        }



        .tomo-result {

            *font-size*: 10px
        }



        .tomo-product img {

            width: 76px;

            height: 76px
        }

    }



    .tomo-unit {

        width: 100%;

        *max-width*: 130px;

        *margin-top*: 5px;

        padding: 5px 6px;

        border: 1px solid #cbd5e1;

        *border-radius*: 7px;

        background: #f8fafc;

        *font-size*: 12px;

        color: #334155
    }



    .tomo-unit-label {

        display: block;

        *font-size*: 10px;

        color: #64748b;

        *margin-top*: 3px
    }



    @media(*max-width*:500px) {

        .tomo-unit {

            *font-size*: 11px;

            padding: 4px 2px
        }

    }



    /* Header tabel kontras dan mudah dibaca di HP */

    .tomo-dialog .tomo-table thead th {

        background: #164b88 !important;

        color: #fff !important;

        *font-size*: 12px;

        *font-weight*: 800;

        *letter-spacing*: .1px;

        border: 1px solid #326ba5;

        padding: 10px 4px;

        *text-align*: center;

        *white-space*: nowrap
    }



    .tomo-dialog .tomo-table thead th:first-child {

        *text-align*: left
    }



    .tomo-stn-input {

        display: block;

        width: 100%;

        height: 40px;

        *box-sizing*: border-box;

        border: 1px solid #cbd5e1;

        *border-radius*: 7px;

        padding: 4px 3px;

        *font-size*: 13px;

        *text-align*: center;

        color: #172b4d;

        background: #fff
    }



    .tomo-dialog .tomo-table td {

        *vertical-align*: middle
    }



    @media(*max-width*:500px) {

        .tomo-dialog .tomo-table thead th {

            *font-size*: 11px;

            padding: 9px 2px
        }



        .tomo-stn-input {

            height: 39px;

            *font-size*: 12px;

            padding: 2px
        }



        .tomo-dialog .tomo-table td:first-child {

            *font-size*: 11px
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

        *margin-left*: 0 !important;

        *padding-top*: 0 !important;

        *min-height*: 100vh
    }



    .tomo-sticky {

        top: 0 !important
    }



    .tomo-table th {

        background: #194c87 !important;

        color: #fff !important;

        *font-weight*: 800 !important;

        border: 1px solid #346497 !important
    }



    .tomo-table td {

        border: 1px solid #dbe3ed
    }



    .tomo-table .tomo-stn-input {

        display: block;

        *box-sizing*: border-box;

        width: 100%;

        height: 40px;

        *min-width*: 0;

        padding: 3px;

        border: 1px solid #cbd5e1;

        *border-radius*: 7px;

        *text-align*: center;

        *font-size*: 13px;

        background: #fff;

        color: #172b4d
    }



    .tomo-table .tomo-cost-input {

        *min-width*: 0
    }



    @media(*max-width*:500px) {

        .tomo-table .tomo-stn-input {

            height: 39px;

            *font-size*: 12px;

            padding: 2px
        }



        .tomo-table .tomo-result {

            *font-size*: 10px
        }

    }



    .tomo-search-field {

        position: relative;

        flex: 1;

        *min-width*: 0
    }



    .tomo-search-field input {

        width: 100%;

        *padding-right*: 38px !important;

        *box-sizing*: border-box
    }



    .tomo-search-clear {

        position: absolute;

        right: 6px;

        top: 50%;

        transform: translateY(-50%);

        border: 0;

        *border-radius*: 50%;

        width: 28px;

        height: 28px;

        background: #e2e8f0;

        color: #334155;

        *font-size*: 21px;

        *line-height*: 26px;

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

        top: 51px !important;

        *z-index*: 40;

        background: #dce8fb;

        *box-shadow*: 0 2px 5px rgba(15, 35, 70, .12);

        *border-radius*: 13px 13px 0 0
    }



    .tomo-sticky {

        *z-index*: 90 !important;

        top: 0 !important
    }



    @media(*max-width*:600px) {

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
</style>

@section('content')

    <div class="tomo-page">

        <div class="tomo-wrap">

            <div class="tomo-title">

                <h2>Dashboard Pengajuan Tomo</h2>

                <p>Cari PO / nama items, klik <b>Ajukan</b>. Sudah mengajukan? Klik pada items untuk melihat pengajuan.</p>

            </div>

            <div class="tomo-sticky">

                <form method="GET" action="{{ route('produksi.tomo') }}" class="tomo-search">

                    <div class="tomo-search-field"><input id="tomoSearchInput" type="search" name="search"
                            value="{{ $search ?? '' }}" placeholder="Cari PO / nama item..."
                            aria-label="Cari PO atau nama item" oninput="tomoToggleSearchClear()"><button
                            id="tomoSearchClear" class="tomo-search-clear" type="button" aria-label="Hapus pencarian"
                            title="Hapus pencarian" onclick="tomoClearSearch()"
                            {{ empty($search) ? 'hidden' : '' }}>×</button></div><button class="tomo-btn">Cari</button>

                </form>

            </div>

            <div class="tomo-tabs"><button type="button" class="tomo-tab active" data-tab="items"
                    onclick="tomoSwitchTab('items')">🏠 Daftar PO</button><button type="button" class="tomo-tab"
                    data-tab="history" onclick="tomoSwitchTab('history')">📋 Riwayat Pengajuan <span
                        id="tomoHistoryCount"></span></button></div>

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

                    <div><small>NO PO <b id="tomoModalPo">-</b></small><strong id="tomoModalName">-</strong><small>Article:

                            <b id="tomoModalArticle">-</b> · Qty: <b id="tomoModalQty">-</b></small></div>

                </div>

                <div id="tomoFormPanel"><b>Rincian Biaya Finishing</b>

                    <table class="tomo-table">

                        <colgroup>

                            <col style="width:27%">

                            <col style="width:20%">

                            <col style="width:14%">

                            <col style="width:13%">

                            <col style="width:26%">

                        </colgroup>

                        <thead>

                            <tr>

                                <th>Proses</th>

                                <th>Harga</th>

                                <th>Qty</th>

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

                tomoPoints = points;

            }

            const rupiah = value *= > * 'Rp ' + new Intl.NumberFormat('id-ID', {

                maximumFractionDigits: 0

            }).format(Number(value) || 0);

            const esc = value *= > * String(value ?? '').replace(/[&<>"']/g, c *= > * ({

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

                return submissions.filter(s *= > * keyFromSubmission(s) === key).sort((a, b) *= > * Number(b.id) -
                    Number(a.id))

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

                document.querySelectorAll('.tomo-item').forEach(el *= > * {

                    const entries = submissionsForItem(el),

                        latest = entries[0],

                        badge = el.querySelector('.tomo-item-status'),

                        ajukan = el.querySelector('.tomo-item-ajukan'),

                        detail = el.querySelector('.tomo-item-detail');

                    badge.style.display = latest ? 'inline-block' : 'none';

                    badge.className = 'tomo-status tomo-item-status ' + statusClass(latest?.status);

                    badge.textContent = latest ? statusLabel(latest.status) : '';

                    detail.style.display = entries.length ? 'inline-block' : 'none';

                    ajukan.style.display = (!latest || statusClass(latest.status) === 'ditolak') ?

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

                submissions.slice().sort((a, b) *= > * Number(b.id) - Number(a.id)).forEach(s *= > * {

                    const div = document.createElement('div');

                    div.className = 'tomo-history-entry';

                    div.innerHTML = '<div><strong>PO: ' + esc(s.po_code) + '</strong><small>' + esc(s

                        .item_name) + ' · ' + esc(s.article_code) + '</small><small>' + esc(s.created_at) +

                    ' · ' + rupiah(s.total) + '</small><span class="tomo-status ' + statusClass(s.status) +

                    '">' + statusLabel(s.status) +

                    '</span></div><button type="button" class="tomo-detail">Detail ›</button>';

                    div.querySelector('button').addEventListener('click', () *= > * openDetail(s));

                    target.appendChild(div)

                })

            }

            window.tomoSwitchTab = function(tab) {

                document.getElementById('tomoItemsPanel').classList.toggle('tomo-hidden', tab !== 'items');

                document.getElementById('tomoHistoryPanel').classList.toggle('tomo-hidden', tab !== 'history');

                document.querySelectorAll('.tomo-tab').forEach(b *= > * b.classList.toggle('active', b.dataset
                    .tab ===

                    tab))

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

                tomoPoints.forEach(point *= > * {

                    const name = String(point.name),

                        tr = document.createElement('tr');

                    tr.dataset.pointId = point.id;

                    tr.innerHTML = '<td>' + esc(name) + '</td>' +

                    '<td><input class="tomo-cost-input" type="number" min="0" step="any" inputmode="decimal" aria-label="Harga ' +

                    esc(name) + '" placeholder="0"></td>' +

                    '<td><input class="tomo-cost-input" type="number" min="0" step="any" inputmode="decimal" aria-label="Qty ' +

                    esc(name) + '" placeholder="0"></td>' +

                    '<td><input class="tomo-stn-input" type="text" maxlength="30" placeholder="pcs" value="pcs" aria-label="Satuan ' +

                    esc(name) + '"></td>' +

                    '<td class="tomo-result">—</td>';

                    tr.querySelectorAll('input[type="number"]').forEach(input *= > * input.addEventListener(
                        'input',

                        recalc));

                    body.appendChild(tr);

                });

                recalc();

            }



            function getRows() {

                return Array.from(document.querySelectorAll('#tomoCostRows tr[data-point-id]')).map(tr *= > * {

                    const numbers = tr.querySelectorAll('input[type="number"]');

                    return {

                        point_id: Number(tr.dataset.pointId),

                        satuan: tr.querySelector('.tomo-stn-input').value.trim() || 'pcs',

                        harga: Number(numbers[0].value) || 0,

                        qty: Number(numbers[1].value) || 0

                    };

                })

            }



            function recalc() {

                let total = 0;

                document.querySelectorAll('#tomoCostRows tr[data-point-id]').forEach(tr *= > * {

                    const inputs = tr.querySelectorAll('input[type="number"]'),

                        a = Number(inputs[0].value) || 0,

                        b = Number(inputs[1].value) || 0,

                        amount = a * b;

                    total += amount;

                    tr.querySelector('.tomo-result').textContent = (inputs[0].value && inputs[1].value) ?

                    rupiah(amount) : '—';

                });

                document.getElementById('tomoGrandTotal').textContent = rupiah(total)

            }

            window.tomoSubmit = async function() {

                if (!activeItem) return;

                const rows = getRows();

                if (rows.some(r *= > * r.harga < 0 || r.qty < 0 || !Number.isFinite(r.harga) || !Number
                        .isFinite(r

                            .qty))) {

                    alert('Harga dan Qty harus angka positif.');

                    return

                }

                const total = rows.reduce((a, r) *= > * a + r.harga * r.qty, 0);

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

                rows.forEach(r *= > * {

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

            document.addEventListener('keydown', e *= > * {

                if (e.key === 'Escape') {

                    if (document.getElementById('tomoZoom').classList.contains('open')) tomoZoomClose();

                    else tomoClose()

                }

            });

            loadHistory();

        })();
    </script>

@endsection
