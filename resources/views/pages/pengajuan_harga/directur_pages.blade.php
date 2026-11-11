@extends('master.master')

@section('content')

    <style>
        .dp-wrap {

            padding: 20px;

            background: #f4f7fb;

            min-height: 80vh;

            font-size: 13px
        }

        .dp-card {

            background: #fff;

            border: 1px solid #dbe4f0;

            border-radius: 9px;

            padding: 16px;

            margin-bottom: 15px
        }

        .dp-title {

            font-size: 20px;

            font-weight: 700;

            color: #17345c;

            margin: 0 0 8px
        }

        .dp-filter {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            align-items: end
        }

        .dp-filter label {

            display: block;

            font-weight: 600;

            margin-bottom: 5px
        }

        .dp-input,

        .dp-select {

            border: 1px solid #cbd5e1;

            border-radius: 6px;

            padding: 8px;

            font-size: 12px;

            width: 100%;

            background: white
        }

        .dp-table-wrap {

            overflow-x: auto
        }

        .dp-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1350px
        }

        .dp-table th {

            background: #203c5c;

            color: #fff;

            padding: 11px 8px;

            text-align: left;

            white-space: nowrap
        }

        .dp-table td {

            border-bottom: 1px solid #e5eaf2;

            padding: 9px 8px;

            vertical-align: top
        }

        .dp-table tbody tr:nth-child(even) {

            background: #f8fafd
        }

        .dp-photo {

            width: 76px;

            height: 76px;

            object-fit: contain;

            background: #fff;

            border: 1px solid #e2e8f0;

            border-radius: 5px
        }

        .dp-muted {

            color: #64748b;

            font-size: 11px
        }

        .dp-btn {

            border: 0;

            border-radius: 6px;

            padding: 8px 13px;

            cursor: pointer;

            font-weight: 650;

            font-size: 12px
        }

        .dp-blue {

            background: #1260d9;

            color: #fff
        }

        .dp-gray {

            background: #e2e8f0;

            color: #26364c
        }

        .dp-btn:disabled {

            opacity: .5;

            cursor: wait
        }

        .dp-status {

            display: block;

            margin-top: 5px;

            font-size: 11px
        }

        .dp-price {

            text-align: right;

            min-width: 65px
        }

        .dp-remark {

            min-width: 190px;

            min-height: 64px;

            resize: vertical
        }

        .dp-badge {

            display: inline-block;

            border-radius: 5px;

            padding: 4px 7px;

            font-size: 11px;

            font-weight: 700
        }

        .dp-open {

            background: #fef3c7;

            color: #92400e
        }

        .dp-closed {

            background: #dcfce7;

            color: #166534
        }

        /* Scroll container: one scrollbar for both axes, sticky header */

        .dp-table-wrap {

            max-height: 72vh;

            overflow: auto;

            position: relative;

            padding: 0;

            overscroll-behavior: contain
        }

        .dp-table {

            border-collapse: separate;

            border-spacing: 0;

            min-width: 1050px
        }

        .dp-table thead th {

            position: sticky;

            top: 0;

            z-index: 10;

            background: #203c5c;

            box-shadow: 0 1px 0 #dbe4f0
        }

        .dp-table td {

            border-right: 1px solid #edf1f7
        }

        .dp-money {

            display: flex;

            align-items: stretch;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            background: #fff;

            min-width: 82px;

            overflow: hidden;

            transition: border-color .15s
        }

        .dp-money:focus-within {

            border-color: #4385ee;

            box-shadow: 0 0 0 2px #4385ee18
        }

        .dp-money-prefix {

            display: flex;

            align-items: center;

            padding: 0 6px;

            background: #eef4fc;

            color: #1e4b7d;

            font-weight: 750;

            border-right: 1px solid #d6e2f2
        }

        .dp-money input {

            border: 0;

            outline: 0;

            width: 100%;

            min-width: 0;

            padding: 7px 5px;

            text-align: right;

            font-size: 12px;

            font-weight: 650;

            background: transparent;

            color: #193354;

            font-variant-numeric: tabular-nums
        }

        .dp-status-select {

            position: relative;

            width: 96px;

            max-width: 100%
        }

        .dp-status-select select {

            appearance: none;

            -webkit-appearance: none;

            width: 100%;

            border: 1px solid #cbd5e1;

            border-radius: 7px;

            padding: 8px 23px 8px 8px;

            background: #fff;

            cursor: pointer;

            font-weight: 700;

            color: #9a5b00
        }

        .dp-status-select select.is-closed {

            color: #166534;

            background: #f0fdf4;

            border-color: #bbf7d0
        }

        .dp-status-select:after {

            content: '⌄';

            position: absolute;

            right: 8px;

            top: 50%;

            transform: translateY(-60%);

            pointer-events: none;

            font-size: 19px;

            font-weight: 700;

            color: #475569
        }

        .dp-status-help {

            margin-top: 7px;

            max-width: 100px;

            font-size: 10px;

            line-height: 1.4;

            color: #64748b
        }

        .dp-status-help.is-closed {

            color: #15803d
        }

        /* Compact status + item column with truncated description */

        .dp-table th:nth-child(3),

        .dp-table td:nth-child(3) {

            width: 174px;

            min-width: 174px;

            max-width: 174px
        }

        .dp-item-cell {

            width: 174px;

            min-width: 174px;

            max-width: 174px;

            overflow: hidden
        }

        .dp-item-code {

            display: block;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap
        }

        .dp-item-description {

            display: block;

            max-width: 100%;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            cursor: help
        }

        .dp-item-po {

            display: block;

            max-width: 100%;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap
        }

        .dp-table th:nth-child(10),

        .dp-table td:nth-child(10) {

            width: 112px;

            min-width: 112px;

            max-width: 112px
        }

        .dp-table tbody tr:hover {

            background: #eef5ff
        }

        .dp-table th:nth-child(6),

        .dp-table td:nth-child(6) {

            width: 112px;

            min-width: 112px;

            max-width: 112px
        }

        .dp-money {

            min-width: 0;

            width: 100%;

            max-width: 105px
        }

        .dp-money input {

            font-size: 11px;

            padding: 7px 4px
        }

        .dp-price-date {

            display: block;

            margin-top: 5px;

            color: #64748b;

            font-size: 10px;

            white-space: nowrap
        }

        .dp-bom-status {

            display: flex;

            align-items: center;

            gap: 5px;

            flex-wrap: wrap;

            margin-top: 6px;

            font-size: 10px
        }

        .dp-bom-status a {

            color: #145bd0;

            text-decoration: none;

            font-weight: 700
        }

        .dp-bom-found {

            color: #15803d
        }

        .dp-bom-sample {

            color: #1d4ed8
        }

        .dp-bom-missing {

            color: #64748b
        }

        .dp-money input:disabled,

        .dp-remark:disabled {

            background: #f0f3f8;

            color: #7a8597;

            cursor: not-allowed
        }

        /* Mobile horizontal-scroll onboarding */

        .dp-mobile-hint {

            display: none;

            align-items: center;

            gap: 8px;

            padding: 9px 12px;

            margin-bottom: 10px;

            background: #eff6ff;

            border: 1px solid #bfdbfe;

            border-radius: 7px;

            color: #1d4ed8;

            font-size: 12px;

            font-weight: 600
        }

        .dp-mobile-hint button {

            margin-left: auto;

            border: 0;

            background: transparent;

            color: #1d4ed8;

            text-decoration: underline;

            cursor: pointer
        }

        @media(max-width:767px) {

            .dp-wrap {

                padding: 10px
            }

            .dp-card {

                padding: 10px
            }

            .dp-mobile-hint {

                display: flex
            }

            .dp-table-wrap {

                max-height: 70vh;

                overflow: auto;

                -webkit-overflow-scrolling: touch;

                touch-action: pan-x pan-y
            }

        }

        .dp-tour-overlay {

            position: fixed;

            inset: 0;

            z-index: 99999;

            display: none
        }

        .dp-tour-overlay.is-visible {

            display: block
        }

        .dp-tour-shade {

            position: absolute;

            inset: 0;

            background: rgba(15, 23, 42, .66)
        }

        .dp-tour-focus {

            position: fixed;

            border: 2px solid #60a5fa;

            border-radius: 8px;

            box-shadow: 0 0 0 4px rgba(96, 165, 250, .22);

            pointer-events: none;

            z-index: 1
        }

        .dp-tour-card {

            position: fixed;

            z-index: 2;

            left: 12px;

            right: 12px;

            max-width: 380px;

            margin: auto;

            padding: 17px;

            background: #fff;

            border-radius: 12px;

            box-shadow: 0 16px 50px rgba(0, 0, 0, .25);

            color: #1e293b
        }

        .dp-tour-title {

            font-size: 15px;

            font-weight: 750;

            margin-bottom: 7px
        }

        .dp-tour-text {

            font-size: 13px;

            line-height: 1.55;

            color: #475569
        }

        .dp-tour-actions {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;

            margin-top: 15px
        }

        .dp-tour-actions label {

            font-size: 11px;

            display: flex;

            align-items: center;

            gap: 5px;

            cursor: pointer
        }

        .dp-tour-actions button {

            border: 0;

            border-radius: 7px;

            padding: 8px 12px;

            background: #2563eb;

            color: #fff;

            font-weight: 650;

            cursor: pointer
        }

        .dp-tour-actions .dp-tour-skip {

            background: #e2e8f0;

            color: #334155
        }

        /* Remarks: readable, untruncated, horizontally scrollable table */

        .dp-table {

            min-width: 1780px;

            table-layout: auto;

        }

        .dp-table th:nth-child(8),

        .dp-table td:nth-child(8),

        .dp-table th:nth-child(9),

        .dp-table td:nth-child(9),

        .dp-table th:nth-child(10),

        .dp-table td:nth-child(10) {

            width: 270px;

            min-width: 270px;

            max-width: 330px;

        }

        .dp-remark-text {

            white-space: pre-wrap;

            overflow-wrap: anywhere;

            word-break: normal;

            line-height: 1.55;

        }

        .dp-table .dp-remark {

            width: 100%;

            min-width: 0;

            min-height: 110px;

            box-sizing: border-box;

            resize: vertical;

        }

        .dp-table th:nth-child(11),

        .dp-table td:nth-child(11) {

            width: 125px;

            min-width: 125px;

            max-width: 125px;

        }

        .dp-table th:nth-child(12),

        .dp-table td:nth-child(12) {

            min-width: 90px;

        }

        /* Sub Name inserted after Pekerjaan: keep remark widths via class, not old column positions */

        .dp-table {

            min-width: 1900px;

        }

        .dp-table th:nth-child(9),

        .dp-table td:nth-child(9),

        .dp-table th:nth-child(10),

        .dp-table td:nth-child(10),

        .dp-table th:nth-child(11),

        .dp-table td:nth-child(11) {

            min-width: 260px;

            width: 260px;

            max-width: 330px;

        }

        .dp-table th:nth-child(12),

        .dp-table td:nth-child(12) {

            min-width: 125px;

            width: 125px;

            max-width: 125px;

        }

        .dp-table th:nth-child(13),

        .dp-table td:nth-child(13) {

            min-width: 90px;

        }

        .ph-price-history,

        .dp-price-history {

            margin-top: 5px;

            font-size: 10px;

            color: #64748b;

            line-height: 1.4;

        }

        .ph-history-entry,

        .dp-history-entry {

            padding: 3px 5px;

            border-radius: 4px;

            background: #f1f5f9;

            white-space: normal;

        }

        .ph-history-entry small,

        .dp-history-entry small {

            display: block;

            font-size: 9px;

            color: #64748b;

        }

        .ph-history-entry.is-latest,

        .dp-history-entry.is-latest {

            background: #dcfce7;

            color: #166534;

            font-weight: 700;

        }

        .ph-history-arrow,

        .dp-history-arrow {

            text-align: center;

            color: #15803d;

            font-size: 14px;

            font-weight: 800;

        }

        .dp-price-editor input {

            width: 105px;

            max-width: 100%;

            border: 1px solid #cbd5e1;

            border-radius: 5px;

            padding: 7px 5px;

            text-align: right;

            font-size: 11px;

            color: #193354;

            background: white;

        }

        .dp-price-editor input:disabled {

            background: #f0f3f8;

            color: #64748b;

        }
    </style>

    <div class="dp-wrap">

        <div class="dp-card">

        @section('btn')
            <h2 class="dp-title">Pengajuan Harga — Director Approval</h2>

            <p class="dp-muted"></p>
        @endsection

        <form method="GET" action="{{ route('pengajuan-harga.directur') }}" class="dp-filter">

            <div style="flex:2;min-width:220px"><label>Cari PO / Article / Pekerjaan</label><input class="dp-input"
                    name="q" value="{{ request('q') }}" placeholder="Cari PO, article, nama pekerjaan..."></div>

            <div style="min-width:155px"><label>Filter Pengajuan</label>

                <select class="dp-select" name="view" aria-label="Filter pengajuan"
                    onchange="this.form.requestSubmit()">

                    <option value="" @selected(request('view') !== 'history')>Belum Close (Open)</option>

                    <option value="history" @selected(request('view') === 'history')>Sudah Close (History)</option>

                </select>

            </div>

            <button type="submit" class="dp-btn dp-blue">Cari</button>

            <a class="dp-btn dp-gray" href="{{ route('pengajuan-harga.directur') }}"
                style="text-decoration:none">Reset</a>

        </form>

    </div>

    <div class="dp-card" style="padding:10px 15px;color:#47627f;font-size:12px">

        {{ request('view') === 'history' ? 'History — pengajuan yang sudah Closed (arsip).' : 'Main UI — hanya pengajuan Open. Setelah Closed, data otomatis pindah ke History.' }}

    </div>

    <div class="dp-mobile-hint" id="dpMobileHint"><span>↔ Geser tabel ke kiri / kanan untuk melihat semua

            kolom.</span><button type="button" id="dpShowTour">Panduan</button></div>

    <div class="dp-card dp-table-wrap" id="dpTableScroll">

        <table class="dp-table">

            <thead>

                <tr>

                    <th>Gambar</th>

                    <th>Qty</th>

                    <th>Item</th>

                    <th>Pekerjaan</th>

                    <th>Sub Name</th>

                    <th>Kalkulasi PPIC</th>

                    <th>Director Price</th>

                    <th>Pengajuan Sub</th>

                    <th>PPIC Remark</th>

                    <th>Sub Remark</th>

                    <th>Director Remark</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($pekerjaans as $p)
                    @php

                        $detail = $p->detailPo?->detail ?? [];

                        if (is_string($detail)) {
                            $detail = json_decode($detail, true) ?: [];
                        }

                        if (!is_array($detail)) {
                            $detail = [];
                        }

                        // NW Code diutamakan, Article NR sebagai fallback.

                        // Jangan menggunakan $p->article_code sebagai prioritas karena

                        // record lama mungkin masih menyimpan Article NR.

                        $itemCodes = collect([
                            $detail['nw_code'] ?? null,

                            $detail['nw_code_'] ?? null,

                            $detail['nwCode'] ?? null,

                            $detail['article_code'] ?? null,

                            $detail['article_nr_'] ?? null,

                            $detail['article_nr'] ?? null,

                            $detail['article'] ?? null,
                        ])
                            ->filter(fn($v) => is_scalar($v) && trim((string) $v) !== '' && trim((string) $v) !== '-')

                            ->map(fn($v) => trim((string) $v))

                            ->unique()

                            ->values();

                        $article = $itemCodes->first() ?? ($p->article_code ?: '-');

                        $codes = $itemCodes->map(fn($v) => mb_strtolower($v));

                        $foundCog = $codes->map(fn($v) => $cogMatches->get($v))->first(fn($v) => $v !== null);

                        $foundSample = $foundCog
                            ? null
                            : $codes->map(fn($v) => $bomSampleMatches->get($v))->first(fn($v) => $v !== null);

                        $description =
                            $detail['nw_description'] ?? ($detail['description'] ?? ($detail['buyer_desc'] ?? ''));

                        $photo = $detail['photo'] ?? ($detail['image'] ?? ($detail['picture'] ?? null));

                        $qty = $detail['qty'] ?? ($detail['quantity'] ?? ($p->detailPo?->qty ?? '-'));

                        $photoUrl = null;

                        if (is_string($photo) && $photo !== '') {
                            $photoUrl = preg_match('~^(https?://|/)~i', $photo)
                                ? $photo
                                : asset('storage/' . ltrim($photo, '/'));
                        }

                    @endphp

                    <tr data-id="{{ $p->id }}">

                        <td>

                            @if ($photoUrl)
                                <img class="dp-photo" src="{{ $photoUrl }}" alt="Foto item"
                                loading="lazy">@else<span class="dp-muted">No image</span>
                            @endif

                        </td>

                        <td>{{ $qty }}</td>

                        <td class="dp-item-cell"><strong class="dp-item-code"
                                title="{{ $article }}">{{ $article }}</strong>

                            <div class="dp-muted dp-item-description" title="{{ $description }}">{{ $description }}

                            </div>

                            <div class="dp-muted dp-item-po"
                                title="PO: {{ $p->po?->order_no ?? ($p->po_code ?? '-') }}">

                                PO: {{ $p->po?->order_no ?? ($p->po_code ?? '-') }}</div>

                            <div class="dp-bom-status">

                                @if ($foundCog)
                                    <span class="dp-bom-found">✓ Found COG</span>

                                    <a href="{{ url('/bom-produksi/show/' . $foundCog->id) }}" target="_blank"
                                        rel="noopener noreferrer">Detail ↗</a>
                                @elseif($foundSample)
                                    <span class="dp-bom-sample">✓ BOM Sample</span>

                                    <a href="{{ url('/bom/show/' . $foundSample->id) }}" target="_blank"
                                        rel="noopener noreferrer">Detail ↗</a>
                                @else
                                    <span class="dp-bom-missing">BOM Not Found</span>
                                @endif

                            </div>

                        </td>

                        <td>{{ $p->kategori?->name ?? '-' }}</td>

                        <td>{{ $p->sub_name ?: '-' }}</td>

                        <td style="text-align:right">{{ number_format((float) $p->ppic_price, 2, ',', '.') }}

                            <small class="dp-price-date">{{ $p->ppic_date?->format('d-m-y H:i') ?? '—' }}</small>

                            @php $priceHist = data_get($p->price_history ?? [], 'ppic_price', []); @endphp

                            <div class="dp-price-history" data-history="ppic_price">

                                @if (is_array($priceHist) && count($priceHist) > 1)
                                    @foreach ($priceHist as $entry)
                                        @if (!$loop->first)
                                            <div class="dp-history-arrow">↓</div>
                                        @endif

                                        <div class="dp-history-entry {{ $loop->last ? 'is-latest' : '' }}">

                                            {{ isset($entry['price']) ? number_format((float) $entry['price'], 2, ',', '.') : '—' }}<small>{{ $loop->first ? 'Harga Awal' : 'Revisi Harga' }}

                                                · {{ $entry['at'] ?? '' }} ·

                                                {{ $entry['user_name'] ?? 'User' }}</small></div>
                                    @endforeach
                                @endif

                            </div>

                        </td>

                        <td>

                            <div class="dp-price-editor"><input type="text" inputmode="numeric" autocomplete="off"
                                    class="js-director-price"
                                    value="{{ $p->director_price === null ? '' : number_format((float) $p->director_price, 2, ',', '.') }}"
                                    placeholder="0" aria-label="Director Price" @disabled(!$isFactory || request('view') === 'history' || $p->closed)></div>

                            <small
                                class="dp-price-date js-director-date">{{ $p->directur_date?->format('d-m-y H:i') ?? '—' }}</small>

                            @php $priceHist = data_get($p->price_history ?? [], 'director_price', []); @endphp

                            <div class="dp-price-history" data-history="director_price">

                                @if (is_array($priceHist) && count($priceHist) > 1)
                                    @foreach ($priceHist as $entry)
                                        @if (!$loop->first)
                                            <div class="dp-history-arrow">↓</div>
                                        @endif

                                        <div class="dp-history-entry {{ $loop->last ? 'is-latest' : '' }}">

                                            {{ isset($entry['price']) ? number_format((float) $entry['price'], 2, ',', '.') : '—' }}<small>{{ $loop->first ? 'Harga Awal' : 'Revisi Harga' }}

                                                · {{ $entry['at'] ?? '' }} ·

                                                {{ $entry['user_name'] ?? 'User' }}</small></div>
                                    @endforeach
                                @endif

                            </div>

                        </td>

                        <td style="text-align:right">

                            {{ (float) $p->pengajuan_sub_price == 0.0 ? 'N/A' : number_format((float) $p->pengajuan_sub_price, 2, ',', '.') }}

                            <small class="dp-price-date">{{ $p->sub_date?->format('d-m-y H:i') ?? '—' }}</small>

                            @php $priceHist = data_get($p->price_history ?? [], 'pengajuan_sub_price', []); @endphp

                            <div class="dp-price-history" data-history="pengajuan_sub_price">

                                @if (is_array($priceHist) && count($priceHist) > 1)
                                    @foreach ($priceHist as $entry)
                                        @if (!$loop->first)
                                            <div class="dp-history-arrow">↓</div>
                                        @endif

                                        <div class="dp-history-entry {{ $loop->last ? 'is-latest' : '' }}">

                                            {{ isset($entry['price']) ? number_format((float) $entry['price'], 2, ',', '.') : '—' }}<small>{{ $loop->first ? 'Harga Awal' : 'Revisi Harga' }}

                                                · {{ $entry['at'] ?? '' }} ·

                                                {{ $entry['user_name'] ?? 'User' }}</small></div>
                                    @endforeach
                                @endif

                            </div>

                        </td>

                        <td>

                            <div class="dp-remark-text">{{ $p->ppic_remark ?: '-' }}</div>

                        </td>

                        <td>

                            <div class="dp-remark-text">{{ $p->sub_remark ?: '-' }}</div>

                        </td>

                        <td>

                            <textarea class="dp-input dp-remark js-director-remark" @disabled(!$isFactory || request('view') === 'history' || $p->closed)>{{ $p->director_remark }}</textarea>

                        </td>

                        <td>

                            <span
                                class="dp-badge js-status-badge {{ $p->closed ? 'dp-closed' : 'dp-open' }}">{{ $p->closed ? 'Closed' : 'Open' }}</span>

                            <div class="dp-status-help js-status-help {{ $p->closed ? 'is-closed' : '' }}">

                                {{ $p->closed ? 'This case has been closed.' : 'Save Director to close this case.' }}

                            </div>

                        </td>

                        <td><button type="button" class="dp-btn dp-blue js-save"
                                @disabled(!$isFactory || request('view') === 'history' || $p->closed)>Save</button><span class="dp-status js-status"
                                aria-live="polite"></span></td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="13" style="text-align:center;padding:25px">Tidak ada data pekerjaan.</td>

                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

    <div>{{ $pekerjaans->links() }}</div>

</div>

<div class="dp-tour-overlay" id="dpTourOverlay" aria-hidden="true">

    <div class="dp-tour-shade"></div>

    <div class="dp-tour-focus" id="dpTourFocus"></div>

    <div class="dp-tour-card" id="dpTourCard" role="dialog" aria-modal="true" aria-labelledby="dpTourTitle">

        <div class="dp-tour-title" id="dpTourTitle">↔ Geser tabel ke samping</div>

        <div class="dp-tour-text">Di layar HP, tabel memiliki banyak kolom. Geser tabel ke kiri atau kanan dengan jari

            untuk melihat Director Price, Remark, Status, dan tombol Save. Header tetap terlihat saat scroll ke bawah.

        </div>

        <div class="dp-tour-actions">

            <label><input type="checkbox" id="dpTourNever"> Jangan tampilkan lagi</label>

            <div><button type="button" class="dp-tour-skip" id="dpTourSkip">Lewati</button> <button type="button"
                    id="dpTourNext">Mengerti</button></div>

        </div>

    </div>

</div>

<script>
    (() => {

        'use strict';

        // Tour mobile mengadaptasi pola overlay + spotlight + localStorage pada QC Monitor.

        // Tour hanya mengajarkan scroll horizontal, tidak mengubah nilai input atau mengirim data.

        const tourKey = 'pengajuan_directur_mobile_scroll_tour_v1';

        const tourOverlay = document.getElementById('dpTourOverlay');

        const tourFocus = document.getElementById('dpTourFocus');

        const tourCard = document.getElementById('dpTourCard');

        const tableScroll = document.getElementById('dpTableScroll');

        const isPhone = () => window.matchMedia('(max-width: 767px)').matches;

        const tourDisabled = () => {

            try {

                return localStorage.getItem(tourKey) === '1';

            } catch (_) {

                return false;

            }

        };

        function positionTour() {

            if (!tourOverlay.classList.contains('is-visible')) return;

            const r = tableScroll.getBoundingClientRect();

            tourFocus.style.left = Math.max(5, r.left - 2) + 'px';

            tourFocus.style.top = Math.max(5, r.top - 2) + 'px';

            tourFocus.style.width = Math.min(window.innerWidth - 10, r.width + 4) + 'px';

            tourFocus.style.height = Math.max(25, Math.min(r.height, window.innerHeight - Math.max(5, r.top) -

                10)) + 'px';

            const h = tourCard.offsetHeight || 190;

            const below = r.top + 18;

            const top = below + h < window.innerHeight - 12 ? below : Math.max(12, window.innerHeight - h - 14);

            tourCard.style.top = top + 'px';

        }

        function closeTour() {

            if (document.getElementById('dpTourNever').checked) {

                try {

                    localStorage.setItem(tourKey, '1');

                } catch (_) {}

            }

            tourOverlay.classList.remove('is-visible');

            tourOverlay.setAttribute('aria-hidden', 'true');

        }

        function openTour() {

            if (!isPhone()) return;

            tourOverlay.classList.add('is-visible');

            tourOverlay.setAttribute('aria-hidden', 'false');

            positionTour();

        }

        document.getElementById('dpShowTour').addEventListener('click', openTour);

        document.getElementById('dpTourSkip').addEventListener('click', closeTour);

        document.getElementById('dpTourNext').addEventListener('click', closeTour);

        window.addEventListener('resize', () => {

            if (!isPhone()) closeTour();

            else positionTour();

        });

        window.addEventListener('scroll', positionTour, {

            passive: true

        });

        document.addEventListener('keydown', e => {

            if (e.key === 'Escape') closeTour();

        });

        if (isPhone() && !tourDisabled()) setTimeout(openTour, 650);

        const baseUrl = @json(url('/pengajuan-harga/directur'));

        const csrf = @json(csrf_token());

        const formatRupiah = value => {
            const raw = String(value ?? '').trim();
            if (!raw) return '';
            // Blade outputs id-ID format, e.g. 245.000,00.
            // Ignore the decimal portion rather than appending its zeroes.
            const integerPart = raw.split(',')[0];
            const digits = integerPart.replace(/\D/g, '');
            return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        };

        document.querySelectorAll('.js-director-price').forEach(input => {

            input.value = formatRupiah(input.value);

            input.addEventListener('input', () => {

                const before = input.value;

                const pos = input.selectionStart ?? before.length;

                const digitsBefore = before.slice(0, pos).replace(/\D/g, '').length;

                const formatted = formatRupiah(before);

                input.value = formatted;

                let cursor = 0,

                    count = 0;

                while (cursor < formatted.length && count < digitsBefore) {

                    if (/\d/.test(formatted[cursor])) count++;

                    cursor++;

                }

                input.setSelectionRange(cursor, cursor);

            });

        });

        document.querySelectorAll('.js-save').forEach(button => {

            button.addEventListener('click', async () => {

                const row = button.closest('tr[data-id]');

                const status = row.querySelector('.js-status');

                const price = row.querySelector('.js-director-price').value.replace(/\./g, '')

                    .replace(',', '.');

                const remark = row.querySelector('.js-director-remark').value;

                if (price !== '' && (!Number.isFinite(Number(price)) || Number(price) < 0)) {

                    status.textContent = 'Harga tidak valid.';

                    status.style.color = '#b91c1c';

                    return;

                }

                button.disabled = true;

                status.textContent = 'Menyimpan...';

                status.style.color = '#64748b';

                try {

                    const response = await fetch(

                        `${baseUrl}/${encodeURIComponent(row.dataset.id)}`, {

                            method: 'PATCH',

                            credentials: 'same-origin',

                            headers: {

                                'Content-Type': 'application/json',

                                'Accept': 'application/json',

                                'X-CSRF-TOKEN': csrf

                            },

                            body: JSON.stringify({

                                director_price: price === '' ? null : Number(price),

                                director_remark: remark,

                                closed: 1

                            })

                        });

                    const result = await response.json().catch(() => ({}));

                    if (!response.ok || !result.success) {

                        const errors = result.errors ? Object.values(result.errors).flat().join(

                            '; ') : '';

                        throw new Error(errors || result.message || `HTTP ${response.status}`);

                    }

                    status.textContent = 'Tersimpan ✓';

                    status.style.color = '#15803d';

                    const badge = row.querySelector('.js-status-badge');

                    badge.textContent = 'Closed';

                    badge.classList.remove('dp-open');

                    badge.classList.add('dp-closed');

                    const help = row.querySelector('.js-status-help');

                    help.textContent = 'This case has been closed.';

                    help.classList.add('is-closed');

                    if (result.price_history) {

                        const el = row.querySelector(

                            '.dp-price-history[data-history="director_price"]');

                        if (el) {

                            el.replaceChildren();

                            const items = result.price_history.director_price || [];

                            if (items.length > 1) items.forEach((entry, index) => {

                                if (index) {

                                    const arrow = document.createElement('div');

                                    arrow.className = 'dp-history-arrow';

                                    arrow.textContent = '↓';

                                    el.appendChild(arrow);

                                }

                                const line = document.createElement('div');

                                line.className = 'dp-history-entry' + (index === items

                                    .length - 1 ? ' is-latest' : '');

                                line.textContent = entry.price == null ? '—' : Number(

                                    entry.price).toLocaleString('id-ID', {

                                    minimumFractionDigits: 2,

                                    maximumFractionDigits: 2

                                });

                                const meta = document.createElement('small');

                                meta.textContent = (index === 0 ? 'Harga Awal' :

                                        'Revisi Harga') + ' · ' + (entry.at || '') +

                                    ' · ' + (entry.user_name || 'User');

                                line.appendChild(meta);

                                el.appendChild(line);

                            });

                        }

                    }

                    // Closed rows leave Main UI immediately; they remain accessible in History.

                    if (result.closed && new URLSearchParams(window.location.search).get(

                            'view') !== 'history') {

                        row.remove();

                    }

                    row.querySelector('.js-director-date').textContent = result.directur_date ||

                        '—';

                } catch (e) {

                    status.textContent = e.message;

                    status.style.color = '#b91c1c';

                } finally {

                    button.disabled = false;

                }

            });

        });

    })();
</script>

@endsection
