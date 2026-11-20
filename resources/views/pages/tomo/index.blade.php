@extends('master.master')
@section('title', 'Dashboard Pengajuan Tomo')

<style>
    .tomo-page {
        font-family: inherit;
        color: #243047;
        padding: 16px 12px;
        background: #f5f7fa;
        min-height: 85vh
    }

    .tomo-wrap {
        max-width: 1120px;
        margin: auto
    }

    .tomo-head {
        background: #fff;
        border: 1px solid #e5e9ef;
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 16px
    }

    .tomo-head h2 {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 5px
    }

    .tomo-muted {
        color: #667085;
        font-size: 14px
    }

    .tomo-search {
        display: flex;
        gap: 9px;
        margin-top: 16px
    }

    .tomo-search input {
        flex: 1;
        min-width: 0;
        border: 2px solid #d7dfea;
        border-radius: 12px;
        padding: 13px;
        font-size: 16px
    }

    .tomo-btn {
        background: #174ea6;
        color: #fff;
        border: 0;
        border-radius: 12px;
        padding: 13px 18px;
        font-size: 16px;
        font-weight: 750;
        cursor: pointer
    }

    .tomo-po {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 3px 12px #1020330a
    }

    .tomo-po-head {
        background: #dce8fb;
        padding: 15px 17px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px
    }

    .tomo-po-head strong {
        font-size: 19px
    }

    .tomo-buyer {
        font-size: 14px;
        color: #475569;
        margin-top: 3px
    }

    .tomo-items {
        padding: 12px;
        display: grid;
        gap: 10px
    }

    .tomo-item {
        width: 100%;
        text-align: left;
        display: grid;
        grid-template-columns: 108px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        border: 2px solid #e5eaf1;
        background: #fff;
        border-radius: 13px;
        padding: 10px;
        cursor: pointer;
        color: inherit
    }

    .tomo-item:active,
    .tomo-item:hover {
        border-color: #6794da;
        background: #f8fbff
    }

    .tomo-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        background: #fff;
        border-radius: 10px;
        display: block
    }

    .tomo-image-box {
        width: 108px;
        height: 108px;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        overflow: hidden;
        position: relative
    }

    .tomo-image-zoom {
        border: 0;
        padding: 4px;
        cursor: zoom-in
    }

    .tomo-image-zoom:focus-visible {
        outline: 3px solid #2563eb;
        outline-offset: 2px
    }

    .tomo-image-zoom::after {
        content: "⌕";
        position: absolute;
        right: 5px;
        bottom: 5px;
        background: #ffffffec;
        color: #1d4ed8;
        border-radius: 7px;
        font-size: 23px;
        line-height: 24px;
        width: 27px;
        height: 27px;
        box-shadow: 0 1px 5px #0002
    }

    .tomo-image-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7a8797;
        font-size: 12px;
        text-align: center
    }

    .tomo-name {
        font-size: 17px;
        font-weight: 800;
        line-height: 1.35;
        overflow-wrap: anywhere
    }

    .tomo-article {
        font-size: 13px;
        color: #64748b;
        margin-top: 5px
    }

    .tomo-spk {
        font-size: 13px;
        color: #475569;
        margin-top: 5px
    }

    .tomo-spk-list {
        margin-top: 9px;
        display: grid;
        gap: 5px
    }

    .tomo-spk-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        background: #f1f5f9;
        border-radius: 7px;
        padding: 7px 9px;
        font-size: 13px
    }

    .tomo-spk-number {
        font-weight: 700;
        color: #334155;
        overflow-wrap: anywhere
    }

    .tomo-spk-price {
        font-weight: 800;
        color: #166534;
        white-space: nowrap
    }

    .tomo-spk-missing {
        color: #94a3b8;
        font-size: 12px
    }

    .tomo-qty {
        background: #e6f4e4;
        color: #22642b;
        border-radius: 10px;
        padding: 10px 12px;
        text-align: center;
        min-width: 68px
    }

    .tomo-qty span {
        display: block;
        font-size: 12px
    }

    .tomo-qty b {
        font-size: 21px
    }

    .tomo-ajukan {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 43px;
        padding: 10px 18px;
        margin-top: 9px;
        background: #f59e0b;
        color: #172033;
        border: 0;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer
    }

    .tomo-ajukan:hover {
        background: #fbbf24
    }

    .tomo-empty {
        background: white;
        border-radius: 14px;
        padding: 35px 20px;
        text-align: center;
        color: #667085
    }

    .tomo-modal {
        position: fixed;
        inset: 0;
        background: #0b1220b3;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 14px
    }

    .tomo-modal.open {
        display: flex
    }

    .tomo-dialog {
        width: min(100%, 680px);
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        max-height: 90vh;
        display: flex;
        flex-direction: column
    }

    .tomo-dialog-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 17px;
        border-bottom: 1px solid #e5e7eb;
        gap: 12px
    }

    .tomo-dialog-head h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 800
    }

    .tomo-close {
        background: #eef2f7;
        border: 0;
        border-radius: 10px;
        width: 46px;
        height: 46px;
        font-size: 28px;
        cursor: pointer
    }

    .tomo-dialog-body {
        padding: 18px;
        min-height: 160px;
        overflow: auto
    }

    .tomo-dialog-foot {
        padding: 12px 18px;
        border-top: 1px solid #e5e7eb;
        text-align: right
    }

    @media(max-width:600px) {
        .tomo-page {
            padding: 10px 7px
        }

        .tomo-head {
            padding: 15px
        }

        .tomo-head h2 {
            font-size: 22px
        }

        .tomo-po-head strong {
            font-size: 18px
        }

        .tomo-items {
            padding: 8px
        }

        .tomo-item {
            grid-template-columns: 82px minmax(0, 1fr) auto;
            gap: 9px;
            padding: 9px
        }

        .tomo-image-box {
            width: 82px;
            height: 82px
        }

        .tomo-image {
            width: 100%;
            height: 100%
        }

        .tomo-name {
            font-size: 16px
        }

        .tomo-qty {
            min-width: 56px;
            padding: 8px
        }

        .tomo-qty b {
            font-size: 18px
        }

        .tomo-search input {
            width: 100%
        }
    }

    .tomo-zoom-overlay {
        position: fixed;
        inset: 0;
        z-index: 100001;
        background: rgba(8, 15, 28, .96);
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px;
        gap: 12px
    }

    .tomo-zoom-overlay.open {
        display: flex
    }

    .tomo-zoom-image-area {
        width: 100%;
        flex: 1;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: auto;
        touch-action: pan-x pan-y
    }

    .tomo-zoom-image-area img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        transform: scale(var(--tomo-scale, 1));
        transition: transform .12s ease;
        transform-origin: center
    }

    .tomo-zoom-tools {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap
    }

    .tomo-zoom-tools button {
        border: 1px solid #ffffff55;
        background: #fff;
        color: #111827;
        border-radius: 12px;
        min-width: 50px;
        min-height: 48px;
        padding: 8px 14px;
        font-size: 20px;
        font-weight: 800;
        cursor: pointer
    }

    .tomo-zoom-tools .tomo-zoom-exit {
        background: #f59e0b
    }

    .tomo-zoom-caption {
        color: #fff;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        max-width: 90%;
        overflow-wrap: anywhere
    }

    @media(max-width:400px) {
        .tomo-item {
            grid-template-columns: 76px minmax(0, 1fr) auto;
            gap: 7px
        }

        .tomo-image-box {
            width: 76px;
            height: 76px
        }

        .tomo-name {
            font-size: 14px
        }

        .tomo-qty {
            min-width: 49px;
            padding: 7px 5px
        }

        .tomo-spk-row {
            flex-wrap: wrap
        }
    }

    /* Form rincian biaya finishing - nyaman untuk pengguna mobile */
    .tomo-cost-meta {
        display: grid;
        grid-template-columns: 1fr;
        gap: 9px;
        margin-bottom: 18px
    }

    .tomo-cost-meta div {
        background: #f3f6fa;
        border-radius: 10px;
        padding: 10px 12px
    }

    .tomo-cost-meta small {
        display: block;
        color: #64748b;
        font-size: 12px;
        margin-bottom: 2px
    }

    .tomo-cost-meta strong {
        font-size: 15px;
        overflow-wrap: anywhere
    }

    .tomo-cost-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed
    }

    .tomo-cost-table th {
        font-size: 12px;
        color: #475569;
        background: #e9eff7;
        text-align: center;
        padding: 10px 4px
    }

    .tomo-cost-table td {
        border-bottom: 1px solid #e7ebf0;
        padding: 9px 3px;
        vertical-align: middle
    }

    .tomo-cost-table .tomo-cost-label {
        font-size: 14px;
        font-weight: 750;
        line-height: 1.25
    }

    .tomo-cost-input {
        width: 100%;
        min-width: 0;
        height: 45px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 7px;
        font-size: 16px;
        text-align: right;
        color: #172033;
        background: #fff
    }

    .tomo-cost-input:focus {
        outline: 2px solid #93c5fd;
        border-color: #2563eb
    }

    .tomo-cost-multiply {
        text-align: center;
        font-size: 17px;
        font-weight: 800;
        color: #64748b
    }

    .tomo-cost-result {
        text-align: right;
        font-size: 14px;
        font-weight: 800;
        color: #14532d;
        overflow-wrap: anywhere
    }

    .tomo-cost-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #e8f5e9;
        border-radius: 12px;
        padding: 15px;
        margin-top: 16px;
        gap: 12px;
        font-size: 19px;
        font-weight: 850
    }

    .tomo-cost-help {
        font-size: 12px;
        color: #64748b;
        margin: 12px 0 0
    }

    @media(max-width:500px) {
        .tomo-dialog {
            max-height: 94dvh
        }

        .tomo-dialog-body {
            padding: 12px
        }

        .tomo-cost-table .tomo-cost-label {
            font-size: 12px
        }

        .tomo-cost-table th {
            font-size: 11px
        }

        .tomo-cost-input {
            font-size: 15px;
            padding: 5px 4px
        }

        .tomo-cost-result {
            font-size: 12px
        }

        .tomo-cost-table td {
            padding: 7px 2px
        }

        .tomo-cost-total {
            font-size: 17px
        }
    }

    /* Modal khusus HP: foto terlihat, form tidak terlalu sempit, tombol mudah ditekan */
    .tomo-dialog {
        width: min(100%, 760px);
        max-height: calc(100dvh - 20px)
    }

    .tomo-dialog-head {
        padding: 11px 14px;
        flex: 0 0 auto
    }

    .tomo-dialog-head h3 {
        font-size: 17px;
        line-height: 1.25
    }

    .tomo-close {
        width: 42px;
        height: 42px;
        flex: 0 0 42px
    }

    .tomo-dialog-body {
        min-height: 0;
        overscroll-behavior: contain
    }

    .tomo-dialog-foot {
        flex: 0 0 auto;
        padding: 9px 14px
    }

    .tomo-dialog-foot .tomo-btn {
        min-height: 46px;
        min-width: 110px
    }

    .tomo-modal-product {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px;
        margin-bottom: 12px;
        background: #f3f7fc;
        border: 1px solid #e2e8f0;
        border-radius: 12px
    }

    .tomo-modal-photo {
        position: relative;
        width: 112px;
        height: 102px;
        flex: 0 0 112px;
        padding: 4px;
        border: 1px solid #dbe4ee;
        border-radius: 10px;
        background: #fff;
        cursor: zoom-in;
        overflow: hidden
    }

    .tomo-modal-photo img {
        width: 100%;
        height: 100%;
        object-fit: contain
    }

    .tomo-modal-photo-hint {
        position: absolute;
        bottom: 2px;
        left: 3px;
        right: 3px;
        background: #1e3a8acc;
        color: white;
        border-radius: 4px;
        font-size: 10px;
        text-align: center;
        padding: 2px
    }

    .tomo-modal-no-photo {
        width: 112px;
        flex: 0 0 112px;
        font-size: 12px;
        text-align: center;
        color: #64748b
    }

    .tomo-modal-product-info {
        min-width: 0;
        flex: 1
    }

    .tomo-modal-meta-label {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4
    }

    .tomo-modal-meta-label strong {
        color: #334155
    }

    .tomo-modal-item-name {
        display: block;
        font-size: 15px;
        line-height: 1.3;
        color: #172033;
        margin: 3px 0;
        overflow-wrap: break-word
    }

    .tomo-cost-table .tomo-cost-label {
        overflow-wrap: normal;
        word-break: normal
    }

    .tomo-cost-table .tomo-cost-result {
        font-variant-numeric: tabular-nums
    }

    .tomo-cost-input {
        min-height: 46px
    }

    .tomo-cost-total {
        position: sticky;
        bottom: 0;
        z-index: 2;
        border: 1px solid #b9e1c0;
        box-shadow: 0 -2px 8px #00000008
    }

    .tomo-modal-photo[hidden],
    .tomo-modal-no-photo[hidden] {
        display: none !important
    }

    @media(max-width:600px) {
        .tomo-modal {
            padding: 5px;
            align-items: center
        }

        .tomo-dialog {
            width: 100%;
            max-height: calc(100dvh - 10px);
            border-radius: 13px
        }

        .tomo-dialog-head {
            padding: 8px 10px
        }

        .tomo-dialog-head h3 {
            font-size: 15px
        }

        .tomo-dialog-body {
            padding: 8px 9px
        }

        .tomo-dialog-foot {
            padding: 7px 10px
        }

        .tomo-modal-product {
            gap: 9px;
            padding: 7px;
            margin-bottom: 9px
        }

        .tomo-modal-photo {
            width: 86px;
            height: 83px;
            flex-basis: 86px
        }

        .tomo-modal-no-photo {
            width: 86px;
            flex-basis: 86px
        }

        .tomo-modal-item-name {
            font-size: 13px;
            line-height: 1.25
        }

        .tomo-modal-meta-label {
            font-size: 11px
        }

        .tomo-cost-table th {
            padding: 7px 2px;
            font-size: 11px
        }

        .tomo-cost-table td {
            padding: 5px 2px
        }

        .tomo-cost-table .tomo-cost-label {
            font-size: 11px;
            line-height: 1.2
        }

        .tomo-cost-input {
            height: 42px;
            min-height: 42px;
            font-size: 16px;
            padding: 3px 3px;
            border-radius: 7px
        }

        .tomo-cost-table .tomo-cost-result {
            font-size: 11px;
            letter-spacing: -.35px
        }

        .tomo-cost-multiply {
            font-size: 13px
        }

        .tomo-cost-total {
            margin-top: 9px;
            padding: 10px 12px;
            font-size: 16px
        }

        .tomo-cost-help {
            margin: 8px 0;
            font-size: 11px
        }
    }

    @media(max-width:370px) {
        .tomo-modal-photo {
            width: 72px;
            height: 75px;
            flex-basis: 72px
        }

        .tomo-cost-table .tomo-cost-label {
            font-size: 10px
        }

        .tomo-cost-table .tomo-cost-result {
            font-size: 10px
        }
    }

    /* Pencarian sticky: tetap terlihat saat daftar PO digulir */
    .tomo-search-sticky {
        position: sticky;
        top: 56px;
        z-index: 80;
        background: #f5f7fa;
        padding: 7px 0 9px;
        margin-bottom: 9px
    }

    .tomo-search-sticky .tomo-search {
        margin-top: 0;
        align-items: stretch;
        gap: 8px
    }

    .tomo-search-sticky .tomo-search input {
        height: 38px;
        min-height: 38px;
        padding: 7px 11px;
        border: 1.5px solid #cbd5e1;
        border-radius: 9px;
        font-size: 16px;
        line-height: 20px;
        background: #fff;
        box-sizing: border-box
    }

    .tomo-search-sticky .tomo-search .tomo-btn {
        height: 38px;
        min-height: 38px;
        padding: 0 15px;
        border-radius: 9px;
        font-size: 14px;
        white-space: nowrap
    }

    @media(max-width:600px) {
        .tomo-head {
            margin-bottom: 5px;
            padding: 13px
        }

        .tomo-head h2 {
            font-size: 20px
        }

        .tomo-muted {
            font-size: 12px;
            line-height: 1.45
        }

        .tomo-search-sticky {
            top: 52px;
            padding: 6px 0 8px
        }

        .tomo-search-sticky .tomo-search input {
            height: 36px;
            min-height: 36px
        }

        .tomo-search-sticky .tomo-search .tomo-btn {
            height: 36px;
            min-height: 36px
        }
    }
</style>

@section('content')
    <div class="tomo-page">
        <div class="tomo-wrap">
            <div class="tomo-head">
                <h2>Dashboard Pengajuan Tomo</h2>
                <div class="tomo-muted">Cari PO / nama items, klik Ajukan. Sudah mengajukan? Klik pada items untuk melihat
                    pengajuan.</div>
            </div>
            <div class="tomo-search-sticky">
                <form method="GET" action="{{ route('produksi.tomo') }}" class="tomo-search">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Cari PO / nama item..."
                        aria-label="Cari PO atau nama item">
                    <button class="tomo-btn" type="submit">Cari</button>
                </form>
            </div>

            @forelse($cards as $card)
                <section class="tomo-po">
                    <div class="tomo-po-head">
                        <div><strong>PO: {{ $card['po'] }}</strong>
                            <div class="tomo-buyer">{{ $card['buyer'] }}</div>
                        </div>
                        <span style="font-size:13px;color:#475569">{{ count($card['items']) }} item</span>
                    </div>
                    <div class="tomo-items">
                        @forelse($card['items'] as $item)
                            <div role="button" tabindex="0" class="tomo-item"
                                onkeydown="if(event.target===this&&(event.key==='Enter'||event.key===' ')){event.preventDefault();tomoOpen(this)}"
                                data-name="{{ $item['name'] }}" data-po="{{ $card['po'] }}"
                                data-article="{{ $item['article'] }}" data-image="{{ $item['image'] ?? '' }}"
                                data-mode="{{ count($item['spks']) ? 'detail' : 'ajukan' }}" onclick="tomoOpen(this)">
                                @if ($item['image'])
                                    <button type="button" class="tomo-image-box tomo-image-zoom"
                                        aria-label="Perbesar foto {{ $item['name'] }}" data-image="{{ $item['image'] }}"
                                        data-caption="{{ $item['name'] }}"
                                        onclick="event.stopPropagation();tomoZoomOpen(this)">
                                        <img class="tomo-image" src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                            loading="lazy"
                                            onerror="this.parentElement.classList.remove('tomo-image-zoom');this.style.display='none';this.nextElementSibling.style.display='flex';this.parentElement.disabled=true">
                                        <span class="tomo-image tomo-image-empty" style="display:none">Tidak ada foto</span>
                                    </button>
                                @else
                                    <div class="tomo-image-box tomo-image-empty">Tidak ada foto</div>
                                @endif
                                <div>
                                    <div class="tomo-name">{{ $item['name'] }}</div>
                                    <div class="tomo-article">Article: {{ $item['article'] }}</div>
                                    @if (count($item['spks']))
                                        <div class="tomo-spk-list">
                                            @foreach ($item['spks'] as $spk)
                                                <div class="tomo-spk-row">
                                                    <span class="tomo-spk-number">{{ $spk['number'] }}</span>
                                                    @if ($spk['harga'] !== null)
                                                        <span class="tomo-spk-price">Rp
                                                            {{ number_format($spk['harga'], 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="tomo-spk-missing">Harga belum ada</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="tomo-spk">SPK TOMO FINISHING: Belum ada</div>
                                        <span class="tomo-ajukan" aria-label="Ajukan SPK TOMO FINISHING">+ Ajukan</span>
                                    @endif
                                </div>
                                <div class="tomo-qty"><span>Qty</span><b>{{ $item['qty'] }}</b></div>
                            </div>
                        @empty
                            <div class="tomo-empty">Belum ada item pada PO ini.</div>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="tomo-empty">Tidak ada PO yang sesuai pencarian.</div>
            @endforelse

            <div style="margin-top:18px">{{ $pos->links() }}</div>
        </div>
    </div>

    <div id="tomoModal" class="tomo-modal" role="dialog" aria-modal="true" aria-labelledby="tomoModalTitle"
        aria-hidden="true" onclick="if(event.target===this)tomoClose()">
        <div class="tomo-dialog">
            <div class="tomo-dialog-head">
                <h3 id="tomoModalTitle">Detail Barang</h3><button type="button" class="tomo-close" aria-label="Tutup"
                    onclick="tomoClose()">&times;</button>
            </div>
            <div class="tomo-dialog-body" id="tomoModalBody">
                <div class="tomo-modal-product">
                    <button type="button" id="tomoCostImageButton" class="tomo-modal-photo" onclick="tomoZoomOpen(this)"
                        aria-label="Perbesar foto produk" hidden>
                        <img id="tomoCostImage" src="" alt="Foto produk"
                            onerror="this.parentElement.hidden=true;document.getElementById('tomoCostNoImage').hidden=false">
                        <span class="tomo-modal-photo-hint">Ketuk untuk zoom</span>
                    </button>
                    <div id="tomoCostNoImage" class="tomo-modal-no-photo" hidden>Foto belum tersedia</div>
                    <div class="tomo-modal-product-info">
                        <div class="tomo-modal-meta-label">NO PO <strong id="tomoCostPo">-</strong></div>
                        <strong id="tomoCostName" class="tomo-modal-item-name">-</strong>
                        <div class="tomo-modal-meta-label">Article: <strong id="tomoCostArticle">-</strong></div>
                    </div>
                </div>
                <table class="tomo-cost-table" aria-label="Perhitungan biaya finishing">
                    <colgroup>
                        <col style="width:32%">
                        <col style="width:21%">
                        <col style="width:4%">
                        <col style="width:19%">
                        <col style="width:24%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="text-align:left">Proses</th>
                            <th>Harga</th>
                            <th>×</th>
                            <th>Qty</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="tomoCostRows"></tbody>
                </table>
                <div class="tomo-cost-total"><span>Total</span><span id="tomoCostGrandTotal">Rp 0</span></div>
                <p class="tomo-cost-help">Isi harga dan jumlah untuk menghitung estimasi. Data belum disimpan ke database.
                </p>
            </div>
            <div class="tomo-dialog-foot"><button type="button" class="tomo-btn" onclick="tomoClose()">Tutup</button>
            </div>
        </div>
    </div>
    <div id="tomoZoomOverlay" class="tomo-zoom-overlay" role="dialog" aria-modal="true"
        aria-label="Perbesar foto produk" aria-hidden="true" onclick="if(event.target===this)tomoZoomClose()">
        <div id="tomoZoomCaption" class="tomo-zoom-caption"></div>
        <div class="tomo-zoom-image-area" onclick="if(event.target===this)tomoZoomClose()"><img id="tomoZoomImage"
                alt="Foto produk diperbesar"></div>
        <div class="tomo-zoom-tools"><button type="button" onclick="tomoZoomChange(-.25)"
                aria-label="Perkecil">−</button><button type="button" onclick="tomoZoomReset()"
                aria-label="Ukuran awal">100%</button><button type="button" onclick="tomoZoomChange(.25)"
                aria-label="Perbesar">+</button><button type="button" class="tomo-zoom-exit"
                onclick="tomoZoomClose()">Tutup ×</button></div>
    </div>
    <script>
        let tomoZoomScale = 1;
        let tomoZoomPreviousFocus = null;

        function tomoZoomOpen(button) {
            if (button.disabled) return;
            tomoZoomPreviousFocus = button;
            document.getElementById('tomoZoomImage').src = button.dataset.image;
            document.getElementById('tomoZoomCaption').textContent = button.dataset.caption || 'Foto produk';
            tomoZoomReset();
            const overlay = document.getElementById('tomoZoomOverlay');
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            overlay.querySelector('.tomo-zoom-exit').focus();
        }

        function tomoZoomChange(delta) {
            tomoZoomScale = Math.max(.5, Math.min(4, tomoZoomScale + delta));
            document.getElementById('tomoZoomImage').style.setProperty('--tomo-scale', tomoZoomScale)
        }

        function tomoZoomReset() {
            tomoZoomScale = 1;
            document.getElementById('tomoZoomImage').style.setProperty('--tomo-scale', 1)
        }

        function tomoZoomClose() {
            const overlay = document.getElementById('tomoZoomOverlay');
            overlay.classList.remove('open');
            overlay.setAttribute('aria-hidden', 'true');
            document.getElementById('tomoZoomImage').removeAttribute('src');
            document.body.style.overflow = document.getElementById('tomoModal').classList.contains('open') ? 'hidden' : '';
            if (tomoZoomPreviousFocus) tomoZoomPreviousFocus.focus();
        }
        const tomoCostNames = ['Kompor', 'Amplas + upah', 'Warna', 'Sanding siller', 'Topcoat', 'Flexycoat', 'wash',
            'upah + amplas + sprey'
        ];
        const tomoRupiah = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });

        function tomoBuildCostRows() {
            const tbody = document.getElementById('tomoCostRows');
            tbody.replaceChildren();
            tomoCostNames.forEach((name, index) => {
                const tr = document.createElement('tr');
                const tdName = document.createElement('td');
                tdName.className = 'tomo-cost-label';
                tdName.textContent = name;
                const tdPrice = document.createElement('td');
                const price = document.createElement('input');
                price.type = 'number';
                price.min = '0';
                price.step = 'any';
                price.inputMode = 'decimal';
                price.placeholder = '0';
                price.className = 'tomo-cost-input';
                price.setAttribute('aria-label', 'Harga ' + name);
                tdPrice.appendChild(price);
                const tdTimes = document.createElement('td');
                tdTimes.className = 'tomo-cost-multiply';
                tdTimes.textContent = '×';
                const tdQty = document.createElement('td');
                const qty = document.createElement('input');
                qty.type = 'number';
                qty.min = '0';
                qty.step = 'any';
                qty.inputMode = 'decimal';
                qty.placeholder = '0';
                qty.className = 'tomo-cost-input';
                qty.setAttribute('aria-label', 'Jumlah ' + name);
                tdQty.appendChild(qty);
                const tdResult = document.createElement('td');
                tdResult.className = 'tomo-cost-result';
                tdResult.textContent = '—';
                const recalc = () => {
                    const p = Number(price.value) || 0,
                        q = Number(qty.value) || 0;
                    tdResult.textContent = price.value !== '' && qty.value !== '' ? tomoRupiah.format(p * q) :
                        '—';
                    tomoUpdateGrandTotal()
                };
                price.addEventListener('input', recalc);
                qty.addEventListener('input', recalc);
                tr.append(tdName, tdPrice, tdTimes, tdQty, tdResult);
                tbody.appendChild(tr);
            });
            tomoUpdateGrandTotal();
        }

        function tomoUpdateGrandTotal() {
            let total = 0;
            document.querySelectorAll('#tomoCostRows tr').forEach(tr => {
                const inputs = tr.querySelectorAll('input');
                total += (Number(inputs[0].value) || 0) * (Number(inputs[1].value) || 0)
            });
            document.getElementById('tomoCostGrandTotal').textContent = tomoRupiah.format(total);
        }

        function tomoOpen(button) {
            const item = {
                name: button.dataset.name
            };
            document.getElementById('tomoModalTitle').textContent = (button.dataset.mode === 'ajukan' ? 'Ajukan SPK — ' :
                '') + (item.name || 'Detail Barang');
            const body = document.getElementById('tomoModalBody');
            document.getElementById('tomoCostPo').textContent = button.dataset.po || '-';
            document.getElementById('tomoCostName').textContent = item.name || '-';
            document.getElementById('tomoCostArticle').textContent = button.dataset.article || '-';
            const photoUrl = button.dataset.image || '';
            const photoButton = document.getElementById('tomoCostImageButton');
            const noPhoto = document.getElementById('tomoCostNoImage');
            const photo = document.getElementById('tomoCostImage');
            photoButton.hidden = !photoUrl;
            noPhoto.hidden = !!photoUrl;
            photoButton.dataset.image = photoUrl;
            photoButton.dataset.caption = item.name || 'Foto produk';
            photo.src = photoUrl;
            tomoBuildCostRows();
            body.scrollTop = 0;
            const modal = document.getElementById('tomoModal');
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            modal.querySelector('.tomo-close').focus();
        }

        function tomoClose() {
            const modal = document.getElementById('tomoModal');
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (document.getElementById('tomoZoomOverlay').classList.contains('open')) tomoZoomClose();
                else tomoClose()
            }
        });
    </script>
@endsection
