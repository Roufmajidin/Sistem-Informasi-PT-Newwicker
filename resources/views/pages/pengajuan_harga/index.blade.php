@extends('master.master')
@section('content')
    <style>
        .ph-wrap {
            padding: 18px;
            background: #f3f6fb;
            min-height: 80vh;
            color: #17335c;
            font-size: 12px
        }

        .ph-card {
            background: #fff;
            border: 1px solid #d9e4f3;
            border-radius: 9px;
            box-shadow: 0 2px 7px #17335c0a;
            margin-bottom: 12px;
            overflow: hidden
        }

        .ph-filter {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 9px;
            padding: 14px
        }

        .ph-filter-group {
            min-width: 140px
        }

        .ph-filter-search {
            width: min(370px, 100%)
        }

        .ph-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #35527c;
            margin-bottom: 5px
        }

        .ph-input,
        .ph-select,
        .ph-textarea {
            border: 1px solid #c8d8ec;
            border-radius: 5px;
            background: #fff;
            padding: 7px 9px;
            font-size: 12px;
            width: 100%;
            min-width: 0;
            color: #233a5a;
            outline: none
        }

        .ph-input:focus,
        .ph-select:focus,
        .ph-textarea:focus {
            border-color: #3672d5;
            box-shadow: 0 0 0 2px #3672d51a
        }

        .ph-textarea {
            resize: vertical;
            min-height: 53px
        }

        .ph-btn {
            border: 1px solid transparent;
            border-radius: 5px;
            padding: 7px 11px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap
        }

        .ph-btn:disabled {
            opacity: .55;
            cursor: wait
        }

        .ph-blue {
            background: #155cd0;
            color: white
        }

        .ph-green {
            background: #09956a;
            color: #fff
        }

        .ph-red {
            background: #fff1f1;
            color: #b73333;
            border-color: #f5d4d4
        }

        .ph-outline {
            background: #eef4ff;
            color: #1557c0;
            border-color: #cbdcff
        }

        .ph-pohead {
            padding: 13px 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center
        }

        .ph-pohead:hover {
            background: #f7faff
        }

        .ph-pohead small {
            font-weight: 500;
            color: #6780a5
        }

        .ph-scroll {
            overflow: auto;
            max-width: 100%;
            max-height: 74vh
        }

        .ph-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 970px;
            width: 100%
        }

        .ph-table th {
            background: #263c55;
            color: #fff;
            text-align: left;
            font-size: 11px;
            padding: 10px;
            border-right: 1px solid #4b6078;
            position: sticky;
            top: 0;
            z-index: 3
        }

        .ph-table td {
            vertical-align: top;
            border-bottom: 1px solid #d6e0ee;
            border-right: 1px solid #e3e9f2;
            padding: 10px
        }

        .ph-table th:first-child,
        .ph-table td:first-child {
            width: 76px
        }

        .ph-table th:nth-child(2),
        .ph-table td:nth-child(2) {
            width: 52px
        }

        .ph-table th:nth-child(3),
        .ph-table td:nth-child(3) {
            width: 215px;
            max-width: 215px
        }

        .ph-thumb {
            width: 55px;
            height: 55px;
            border: 1px solid #dce6f3;
            border-radius: 5px;
            object-fit: contain;
            background: #fff
        }

        .ph-item-code {
            font-weight: 800;
            color: #1459c8;
            font-size: 12px;
            overflow-wrap: anywhere
        }

        .ph-desc {
            font-size: 10px;
            color: #6882a2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 195px;
            margin-top: 5px;
            cursor: help
        }

        .ph-job {
            border: 1px solid #d7e3f3;
            border-radius: 7px;
            padding: 10px;
            margin-bottom: 8px;
            background: #fcfdff
        }

        .ph-jobgrid {
            display: grid;
            grid-template-columns: minmax(155px, 1.5fr) minmax(125px, 1.05fr) repeat(3, minmax(94px, .78fr)) minmax(105px, auto);
            gap: 8px;
            align-items: start
        }

        .ph-jobgrid>div {
            min-width: 0
        }

        .ph-jobgrid>.ph-save,
        .ph-jobgrid>.ph-delete {
            margin-top: 18px;
            align-self: start
        }

        .ph-job-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: flex-end;
            grid-column: 6;
            grid-row: 1;
            margin-top: 0;
            min-width: 112px
        }

        .ph-job-actions .ph-action-buttons {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: flex-end
        }

        .ph-job-actions .ph-status {
            display: block;
            margin: 0 0 4px 0;
            min-height: 13px;
            font-size: 10px;
            text-align: right;
            white-space: normal;
            overflow-wrap: anywhere
        }

        .ph-job-actions .ph-status:empty {
            visibility: hidden
        }

        .ph-job-actions {
            flex-direction: column;
            align-items: stretch
        }

        .ph-job-actions .ph-action-buttons {
            margin-top: 0
        }

        .ph-job-actions .ph-save,
        .ph-job-actions .ph-delete {
            align-self: auto;
            margin-top: 0
        }

        .ph-remarks {
            display: grid;
            grid-template-columns: repeat(3, minmax(135px, 1fr));
            gap: 9px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e4ebf6
        }

        .ph-remark-toggle {
            border: 0;
            background: transparent;
            color: #175cc7;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 0;
            cursor: pointer
        }

        .ph-status {
            display: inline-block;
            margin-left: 7px;
            color: #147c57;
            font-size: 11px
        }

        .ph-actions {
            display: flex;
            align-items: center;
            margin-top: 8px
        }

        .ph-combo {
            position: relative
        }

        .ph-combo-input {
            padding-right: 26px
        }

        .ph-combo-arrow {
            position: absolute;
            right: 6px;
            top: 5px;
            border: 0;
            background: transparent;
            color: #6b83a4;
            cursor: pointer
        }

        .ph-combo-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #c7d8ee;
            border-radius: 5px;
            box-shadow: 0 8px 22px #123b6b22;
            max-height: 220px;
            overflow-y: auto;
            z-index: 12;
            margin-top: 3px
        }

        .ph-combo-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 5px;
            padding: 5px 7px;
            border-bottom: 1px solid #f0f3f8
        }

        .ph-combo-option:hover {
            background: #eef5ff
        }

        .ph-combo-pick {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            background: none;
            border: 0;
            cursor: pointer;
            font-size: 11px;
            padding: 4px;
            color: #243c5e
        }

        .ph-combo-edit {
            border: 0;
            background: #edf3ff;
            color: #195cc5;
            cursor: pointer;
            border-radius: 3px;
            padding: 4px 6px
        }

        .ph-combo-editor {
            display: flex;
            gap: 4px;
            width: 100%
        }

        .ph-combo-editor input {
            min-width: 0;
            flex: 1;
            padding: 5px;
            border: 1px solid #abc3e6;
            border-radius: 4px;
            font-size: 11px
        }

        .ph-combo-editor button {
            padding: 4px 6px;
            font-size: 10px;
            cursor: pointer;
            border: 1px solid #d2dfee;
            border-radius: 3px;
            background: white
        }

        .ph-combo-empty {
            padding: 9px;
            font-size: 11px;
            color: #7c8aa1
        }

        .ph-combo-add {
            width: 100%;
            text-align: left;
            padding: 9px;
            border: 0;
            background: #f0f6ff;
            color: #155cc4;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer
        }

        .ph-bom-status {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
            margin-top: 7px;
            font-size: 10px
        }

        .ph-bom-status span {
            border-radius: 4px;
            padding: 3px 6px;
            font-weight: 700
        }

        .ph-bom-found {
            background: #e5f7ed;
            color: #13743c
        }

        .ph-bom-sample {
            background: #e9f1ff;
            color: #1a57bd
        }

        .ph-bom-missing {
            background: #f0f2f5;
            color: #687385
        }

        .ph-bom-status a {
            font-weight: 700;
            color: #145bd0;
            text-decoration: none
        }

        .ph-bom-status a:hover {
            text-decoration: underline
        }

        .ph-price-date {
            display: block;
            margin-top: 5px;
            color: #71819b;
            font-size: 10px;
            white-space: nowrap
        }

        .ph-input:disabled,
        .ph-textarea:disabled {
            background: #f0f3f8;
            color: #75839b;
            cursor: not-allowed
        }

        .ph-mobile-hint {
            display: none;
            background: #edf4ff;
            color: #245aaf;
            border: 1px solid #c9d9f5;
            border-radius: 6px;
            padding: 9px;
            margin: 8px 10px
        }

        @media(max-width:767px) {
            .ph-wrap {
                padding: 9px
            }

            .ph-filter-search {
                width: 100%
            }

            .ph-mobile-hint {
                display: block
            }

            .ph-remarks {
                grid-template-columns: 1fr
            }

            .ph-jobgrid {
                min-width: 790px
            }

            .ph-job {
                overflow-x: auto
            }
        }
        .ph-job.is-closed { background: #e7f8ec; border-color: #85c99c; opacity: .70; }
        .ph-job.is-closed .ph-close { background: #16803d; color: white; }
        .ph-close { width: 100%; margin-top: 6px; padding: 5px 8px; background: #fff5e9; color: #98520b; border: 1px solid #f2d4ae; border-radius: 5px; cursor: pointer; font-size: 10px; font-weight: 700; }
        .ph-close:disabled { cursor: default; }
        .ph-price { font-variant-numeric: tabular-nums; padding-left: 5px; padding-right: 5px; font-size: 11px; }
        .ph-subname { min-width: 0; }
    
.ph-price-history,.dp-price-history { margin-top:5px;font-size:10px;color:#64748b;line-height:1.4; }
.ph-history-entry,.dp-history-entry { padding:3px 5px;border-radius:4px;background:#f1f5f9;white-space:normal; }
.ph-history-entry small,.dp-history-entry small { display:block;font-size:9px;color:#64748b; }
.ph-history-entry.is-latest,.dp-history-entry.is-latest {background:#dcfce7;color:#166534;font-weight:700;}
.ph-history-arrow,.dp-history-arrow {text-align:center;color:#15803d;font-size:14px;font-weight:800;}
</style>
    <div class="ph-wrap" id="pengajuanHargaIndex">
        <div class="ph-card">
            <form method="GET" action="{{ route('pengajuan-harga.index') }}" class="ph-filter">
                <div class="ph-filter-group ph-filter-search"><label class="ph-label">CARI PO / BUYER / NW CODE /
                        ARTICLE</label><input class="ph-input" type="search" name="q" value="{{ $search }}"
                        placeholder="Contoh: 26-80 atau article code"></div>
                <button class="ph-btn ph-blue" type="submit">Cari</button>
                <div class="ph-filter-group"><label class="ph-label">NW/NWS</label><select class="ph-select"
                        name="company">
                        <option value="">Select</option>
                        @foreach (['NW', 'NWS', 'NWD'] as $co)
                            <option value="{{ $co }}" @selected($company === $co)>{{ $co }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ph-filter-group"><label class="ph-label">RELEASE</label><select class="ph-select"
                        name="release">
                        <option value="desc" @selected($release === 'desc')>Terbaru</option>
                        <option value="asc" @selected($release === 'asc')>Terlama</option>
                    </select></div>
                <a href="{{ route('pengajuan-harga.index') }}" class="ph-btn ph-outline"
                    style="text-decoration:none">Reset</a>
                <a href="{{ route('pengajuan-harga.index', array_merge(request()->except('page'), ['submitted' => empty($submittedOnly) ? '1' : '0'])) }}"
                    class="ph-btn {{ !empty($submittedOnly) ? 'ph-green' : 'ph-outline' }}" style="text-decoration:none">
                    {{ !empty($submittedOnly) ? 'Go to All' : 'Go to History' }}
                </a>
            </form>
        </div>

        @forelse($pos as $po)
            <div class="ph-card">
                <div class="ph-pohead" role="button" tabindex="0" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                    <span>PO: {{ $po->order_no }} <small>({{ $po->company_name }})</small></span><span
                        class="ph-chevron">{{ $loop->first ? '▾' : '▸' }}</span>
                </div>
                <div class="ph-pobody" @if (!$loop->first) hidden @endif>
                    <div class="ph-mobile-hint">↔ Geser tabel ke kanan atau kiri untuk melihat seluruh kolom dan tombol
                        Save.</div>
                    <div class="ph-scroll">
                        <table class="ph-table">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Qty</th>
                                    <th>Item</th>
                                    <th>Pekerjaan &amp; Pengajuan Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($po->details as $item)
                                    @php
                                        $d = $item->detail ?? [];
                                        if (is_string($d)) {
                                            $d = json_decode($d, true) ?: [];
                                        }
                                        if (is_object($d)) {
                                            $d = (array) $d;
                                        }
                                        if (!is_array($d)) {
                                            $d = [];
                                        }
                                        $codes = collect([
                                            'nw_code',
                                            'nw_code_',
                                            'nwCode',
                                            'article_code',
                                            'article_nr_',
                                            'article_nr',
                                            'article',
                                        ])
                                            ->map(fn($key) => $d[$key] ?? null)
                                            ->filter(
                                                fn($value) => is_scalar($value) &&
                                                    trim((string) $value) !== '' &&
                                                    trim((string) $value) !== '-',
                                            )
                                            ->map(fn($value) => trim((string) $value))
                                            ->unique()
                                            ->values();
                                        $article = $codes->first() ?? '-';
                                        $description =
                                            $d['nw_description'] ?? ($d['description'] ?? ($d['buyer_desc'] ?? '-'));
                                        $photo = $d['photo'] ?? ($d['image'] ?? ($d['picture'] ?? null));
                                        if (is_array($photo)) {
                                            $photo = $photo['url'] ?? ($photo['path'] ?? null);
                                        }
                                        $qty = $d['qty'] ?? ($d['quantity'] ?? ($item->qty ?? '-'));
                                        $jobs = $pekerjaan->get($item->id, collect());
                                        $foundCog = $codes
                                            ->map(fn($code) => $cogMatches->get(mb_strtolower($code)))
                                            ->first(fn($found) => $found !== null);
                                        $foundSample = $foundCog
                                            ? null
                                            : $codes
                                                ->map(fn($code) => $bomSampleMatches->get(mb_strtolower($code)))
                                                ->first(fn($found) => $found !== null);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($photo)
                                                <img class="ph-thumb"
                                                    src="{{ filter_var($photo, FILTER_VALIDATE_URL) ? $photo : asset(ltrim((string) $photo, '/')) }}"
                                                alt="Foto {{ $article }}" loading="lazy">@else<span
                                                    style="color:#8ca0b9">No image</span>
                                            @endif
                                        </td>
                                        <td style="font-weight:800;color:#1757bc">{{ $qty }}</td>
                                        <td>
                                            <div class="ph-item-code">{{ $article }}</div>
                                            <div class="ph-desc" title="{{ $description }}">{{ $description }}</div>
                                            <div class="ph-bom-status">
                                                @if ($foundCog)
                                                    <span class="ph-bom-found">✓ Found COG</span>
                                                    <a href="{{ url('/bom-produksi/show/' . $foundCog->id) }}"
                                                        target="_blank" rel="noopener noreferrer">Detail (click) ↗</a>
                                                @elseif($foundSample)
                                                    <span class="ph-bom-sample">✓ Found BOM Samples</span>
                                                    <a href="{{ url('/bom/show/' . $foundSample->id) }}" target="_blank"
                                                        rel="noopener noreferrer">Detail (click) ↗</a>
                                                @else
                                                    <span class="ph-bom-missing">BOM Not Found</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="ph-work" data-po-id="{{ $po->id }}"
                                                data-detail-id="{{ $item->id }}" data-article="{{ $article }}">
                                                <div class="ph-jobs">
                                                    @forelse($jobs as $job)
                                                        <div class="ph-job {{ $job->closed ? 'is-closed' : '' }}" data-job-id="{{ $job->id }}" data-closed="{{ $job->closed ? 1 : 0 }}">
                                                            <div class="ph-jobgrid">
                                                                <div><label class="ph-label">Pekerjaan</label>
                                                                    <div class="ph-combo"
                                                                        data-selected="{{ $job->bahan_kategori_id }}">
                                                                        <input type="hidden" class="job-category"
                                                                            value="{{ $job->bahan_kategori_id }}"><input
                                                                            type="text" class="ph-input ph-combo-input"
                                                                            placeholder="Cari / ketik pekerjaan..."
                                                                            autocomplete="off"
                                                                            value="{{ $masterPekerjaan->firstWhere('id', $job->bahan_kategori_id)?->name ?? '' }}"><button
                                                                            type="button" class="ph-combo-arrow"
                                                                            aria-label="Pilih pekerjaan">▾</button>
                                                                        <div class="ph-combo-menu" hidden></div>
                                                                    </div>
                                                                </div>
                                                                <div class="ph-subname"><label class="ph-label">Sub Name</label>
                                                                    <input type="text" class="ph-input job-sub-name" maxlength="255"
                                                                        placeholder="Nama subkontraktor" value="{{ $job->sub_name ?? '' }}" @disabled($isFactory)>
                                                                </div>
                                                                @foreach (['ppic_price' => 'PPIC Price', 'pengajuan_sub_price' => 'Pengajuan Sub', 'director_price' => 'Director Price'] as $field => $label)
                                                                    <div><label
                                                                            class="ph-label">{{ $label }}</label><input
                                                                            type="text" inputmode="decimal"
                                                                            class="ph-input ph-price"
                                                                            data-field="{{ $field }}"
                                                                            value="{{ $job->{$field} !== null ? number_format((float) $job->{$field}, 2, ',', '.') : '' }}"
                                                                            placeholder="0"
                                                                            @disabled($isFactory ? $field !== 'director_price' : $field === 'director_price')><small
                                                                            class="ph-price-date"
                                                                            data-date="{{ $field }}">{{ $job->{$field === 'ppic_price' ? 'ppic_date' : ($field === 'director_price' ? 'directur_date' : 'sub_date')}?->format('d-m-y H:i') ?? '—' }}</small>
                                                                        <div class="ph-price-history" data-history="{{ $field }}">
@php $hist = data_get($job->price_history ?? [], $field, []); @endphp
@if(is_array($hist) && count($hist) > 1)
@foreach($hist as $i => $entry)
@if($i > 0)<div class="ph-history-arrow">↓</div>@endif
<div data-history-index="{{ $i }}" class="ph-history-entry {{ $loop->last ? 'is-latest' : '' }}">{{ isset($entry['price']) ? number_format((float)$entry['price'], 2, ',', '.') : '—' }}<small>{{ $entry['at'] ?? '' }} · {{ $entry['user_name'] ?? 'User' }}</small></div>
@endforeach
@endif
</div>
                                                                    </div>
                                                                @endforeach
                                                                <div class="ph-job-actions"><span class="ph-status"
                                                                        aria-live="polite"></span>
                                                                    <div class="ph-action-buttons"><button type="button"
                                                                            class="ph-btn ph-green ph-save">Save</button><button
                                                                            type="button" class="ph-btn ph-red ph-delete"
                                                                            title="Hapus pekerjaan">×</button></div>
                                                                    <button type="button" class="ph-close" @disabled($job->closed) >{{ $job->closed ? '✓ Closed' : 'Close this?' }}</button>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="ph-remark-toggle"
                                                                aria-expanded="false">▸ Show Remark</button>
                                                            <div class="ph-remarks" hidden>
                                                                @foreach (['ppic_remark' => 'PPIC Remark', 'sub_remark' => 'Sub Remark', 'director_remark' => 'Director Remark'] as $field => $label)
                                                                    <div><label
                                                                            class="ph-label">{{ $label }}</label>
                                                                        <textarea class="ph-textarea ph-remark" data-field="{{ $field }}" @disabled($isFactory ? $field !== 'director_remark' : $field === 'director_remark')
                                                                            placeholder="{{ $label }}">{{ $job->{$field} ?? '' }}</textarea>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="ph-job" data-job-id="">
                                                            <div class="ph-jobgrid">
                                                                <div><label class="ph-label">Pekerjaan</label>
                                                                    <div class="ph-combo" data-selected=""><input
                                                                            type="hidden" class="job-category"><input
                                                                            type="text" class="ph-input ph-combo-input"
                                                                            placeholder="Cari / ketik pekerjaan..."
                                                                            autocomplete="off"><button type="button"
                                                                            class="ph-combo-arrow"
                                                                            aria-label="Pilih pekerjaan">▾</button>
                                                                        <div class="ph-combo-menu" hidden></div>
                                                                    </div>
                                                                </div>
                                                                <div class="ph-subname"><label class="ph-label">Sub Name</label>
                                                                    <input type="text" class="ph-input job-sub-name" maxlength="255"
                                                                        placeholder="Nama subkontraktor" @disabled($isFactory)>
                                                                </div>
                                                                @foreach (['ppic_price' => 'PPIC Price', 'pengajuan_sub_price' => 'Pengajuan Sub', 'director_price' => 'Director Price'] as $field => $label)
                                                                    <div><label
                                                                            class="ph-label">{{ $label }}</label><input
                                                                            type="text" inputmode="decimal"
                                                                            class="ph-input ph-price"
                                                                            data-field="{{ $field }}"
                                                                            placeholder="0"
                                                                            @disabled($isFactory ? $field !== 'director_price' : $field === 'director_price')><small
                                                                            class="ph-price-date"
                                                                            data-date="{{ $field }}">—</small>
                                                                    </div>
                                                                @endforeach
                                                                <div class="ph-job-actions"><span class="ph-status"
                                                                        aria-live="polite"></span>
                                                                    <div class="ph-action-buttons"><button type="button"
                                                                            class="ph-btn ph-green ph-save">Save</button><button
                                                                            type="button" class="ph-btn ph-red ph-delete"
                                                                            title="Hapus pekerjaan">×</button></div>
                                                                    <button type="button" class="ph-close">Close this?</button>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="ph-remark-toggle"
                                                                aria-expanded="false">▸ Show Remark</button>
                                                            <div class="ph-remarks" hidden>
                                                                @foreach (['ppic_remark' => 'PPIC Remark', 'sub_remark' => 'Sub Remark', 'director_remark' => 'Director Remark'] as $field => $label)
                                                                    <div><label
                                                                            class="ph-label">{{ $label }}</label>
                                                                        <textarea class="ph-textarea ph-remark" data-field="{{ $field }}" @disabled($isFactory ? $field !== 'director_remark' : $field === 'director_remark')
                                                                            placeholder="{{ $label }}"></textarea>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforelse
                                                </div>
                                                <div class="ph-actions"><button type="button"
                                                        class="ph-btn ph-outline ph-add">+ Tambah Pekerjaan</button></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center;padding:20px">PO ini belum memiliki
                                            item.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="ph-card" style="padding:22px;text-align:center">Tidak ada item pengajuan yang sesuai dengan filter.</div>
        @endforelse
        <div>{{ $pos->links() }}</div>
    </div>
    <script>
        (function() {
            'use strict';
            const root = document.getElementById('pengajuanHargaIndex');
            if (!root) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
            const historyUrlBase = @json(url('/pengajuan-harga/pekerjaan'));
            const saveUrl = @json(route('pengajuan-harga.save'));
            const deleteUrl = @json(route('pengajuan-harga.delete'));
            const closeUrlBase = @json(url('/pengajuan-harga/pekerjaan'));
            const categoryCreateUrl = @json(route('pengajuan-harga.category.store'));
            const categoryUpdateTemplate = @json(url('/pengajuan-harga/kategori/__ID__'));
            const categories = @json($masterPekerjaan->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values());

            async function send(url, method, payload) {
                const response = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat().join(
                    ', ') || `HTTP ${response.status}`);
                return data;
            }

            function escapeHTML(s) {
                return String(s).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            function categoryById(id) {
                return categories.find(c => String(c.id) === String(id));
            }

            function closeMenus(except) {
                root.querySelectorAll('.ph-combo-menu').forEach(menu => {
                    if (menu !== except) menu.hidden = true;
                });
            }

            function renderMenu(combo, query = '') {
                const menu = combo.querySelector('.ph-combo-menu');
                const q = query.trim().toLocaleLowerCase();
                const matches = categories.filter(c => c.name.toLocaleLowerCase().includes(q));
                menu.innerHTML = matches.map(c =>
                    `<div class="ph-combo-option" data-id="${c.id}"><button type="button" class="ph-combo-pick" title="${escapeHTML(c.name)}">${escapeHTML(c.name)}</button><button type="button" class="ph-combo-edit" title="Edit kategori">✎</button></div>`
                    ).join('');
                if (!matches.length) menu.innerHTML = '<div class="ph-combo-empty">Kategori tidak ditemukan.</div>';
                if (q && !categories.some(c => c.name.toLocaleLowerCase() === q)) {
                    menu.insertAdjacentHTML('beforeend',
                        `<button type="button" class="ph-combo-add">+ Tambah kategori: ${escapeHTML(query.trim())}</button>`
                        );
                }
                closeMenus(menu);
                menu.hidden = false;
            }

            function selectCategory(combo, category) {
                combo.querySelector('.job-category').value = category.id;
                combo.querySelector('.ph-combo-input').value = category.name;
                combo.querySelector('.ph-combo-menu').hidden = true;
            }

            function resetJob(row) {
                row.dataset.jobId = '';
                row.querySelector('.job-category').value = '';
                row.querySelector('.ph-combo-input').value = '';
                row.querySelector('.job-sub-name').value = '';
                row.classList.remove('is-closed');
                row.dataset.closed = '0';
                const closeBtn = row.querySelector('.ph-close');
                if (closeBtn) { closeBtn.disabled = false; closeBtn.textContent = 'Close this?'; }
                row.querySelectorAll('.ph-price, .ph-remark').forEach(el => el.value = '');
                row.querySelectorAll('.ph-price-date').forEach(el => el.textContent = '—');
                row.querySelector('.ph-remarks').hidden = true;
                const toggle = row.querySelector('.ph-remark-toggle');
                toggle.textContent = '▸ Show Remark';
                toggle.setAttribute('aria-expanded', 'false');
                row.querySelector('.ph-status').textContent = '';
                row.querySelector('.ph-combo-menu').hidden = true;
            }
            // Format Indonesia: 1234567,89 -> 1.234.567,89
            // Tanda titik sebagai pemisah ribuan, koma sebagai desimal.
            function numericValue(value) {
                let s = String(value ?? '').trim().replace(/\s/g, '').replace(/Rp/gi, '');
                if (!s) return null;
                if (s.includes(',')) {
                    s = s.replace(/\./g, '').replace(',', '.');
                } else if (/^\d{1,3}(\.\d{3})+$/.test(s)) {
                    s = s.replace(/\./g, '');
                }
                const n = Number(s);
                return Number.isFinite(n) && n >= 0 ? n : NaN;
            }

            function formatPriceInput(el) {
                let raw = el.value.replace(/[^\d,.]/g, '');
                // Koma = pemisah desimal. Titik lama dari database boleh sebagai desimal.
                if (!raw.includes(',') && /^\d+\.\d{1,2}$/.test(raw)) {
                    raw = raw.replace('.', ',');
                }
                const commaIndex = raw.indexOf(',');
                let integer = commaIndex >= 0 ? raw.slice(0, commaIndex) : raw;
                let decimal = commaIndex >= 0 ? raw.slice(commaIndex + 1).replace(/\D/g, '').slice(0, 2) : '';
                integer = integer.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
                integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                el.value = integer + (commaIndex >= 0 ? ',' + decimal : '');
            }
            root.querySelectorAll('.ph-price').forEach(el => {
                if (el.value) formatPriceInput(el);
            });

            root.addEventListener('input', e => {
                const price = e.target.closest('.ph-price');
                if (price && !price.disabled) {
                    const oldLength = price.value.length;
                    const oldCursor = price.selectionStart ?? oldLength;
                    const digitsBefore = (price.value.slice(0, oldCursor).match(/\d/g) || []).length;
                    formatPriceInput(price);
                    let cursor = 0,
                        digits = 0;
                    while (cursor < price.value.length && digits < digitsBefore) {
                        if (/\d/.test(price.value[cursor])) digits++;
                        cursor++;
                    }
                    if (oldCursor === oldLength) cursor = price.value.length;
                    price.setSelectionRange(cursor, cursor);
                    return;
                }
                const input = e.target.closest('.ph-combo-input');
                if (!input) return;
                input.closest('.ph-combo').querySelector('.job-category').value = '';
                renderMenu(input.closest('.ph-combo'), input.value);
            });
            root.addEventListener('blur', e => {
                const price = e.target.closest('.ph-price');
                if (!price || price.disabled || !price.value.trim()) return;
                const n = numericValue(price.value);
                if (!Number.isNaN(n) && n !== null) {
                    price.value = n.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }, true);
            root.addEventListener('focusin', e => {
                const input = e.target.closest('.ph-combo-input');
                if (input && !input.closest('.ph-combo').querySelector('.ph-combo-editor')) renderMenu(input
                    .closest('.ph-combo'), input.value);
            });
            document.addEventListener('click', e => {
                if (!e.target.closest('.ph-combo')) closeMenus(null);
            });
            root.addEventListener('keydown', e => {
                const editorInput = e.target.closest('.ph-combo-editor input');
                if (!editorInput) return;
                if (e.key === 'Enter') {
                    e.preventDefault();
                    editorInput.closest('.ph-combo-editor').querySelector('.ph-combo-edit-save').click();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    editorInput.closest('.ph-combo-editor').querySelector('.ph-combo-edit-cancel').click();
                }
            });
            // Jangan biarkan menu ter-render ulang saat tombol edit diklik.
            root.addEventListener('mousedown', e => {
                if (e.target.closest('.ph-combo-edit, .ph-combo-editor')) {
                    e.stopPropagation();
                }
            });
            root.addEventListener('click', async e => {
                const header = e.target.closest('.ph-pohead');
                if (header) {
                    const body = header.nextElementSibling;
                    body.hidden = !body.hidden;
                    header.setAttribute('aria-expanded', String(!body.hidden));
                    header.querySelector('.ph-chevron').textContent = body.hidden ? '▸' : '▾';
                    return;
                }
                const arrow = e.target.closest('.ph-combo-arrow');
                if (arrow) {
                    const combo = arrow.closest('.ph-combo'),
                        menu = combo.querySelector('.ph-combo-menu');
                    if (!menu.hidden) menu.hidden = true;
                    else renderMenu(combo, '');
                    return;
                }
                const pick = e.target.closest('.ph-combo-pick');
                if (pick) {
                    const option = pick.closest('.ph-combo-option'),
                        cat = categoryById(option.dataset.id);
                    if (cat) selectCategory(pick.closest('.ph-combo'), cat);
                    return;
                }
                const edit = e.target.closest('.ph-combo-edit');
                if (edit) {
                    e.preventDefault();
                    e.stopPropagation();
                    const option = edit.closest('.ph-combo-option');
                    const cat = option ? categoryById(option.dataset.id) : null;
                    if (!cat) {
                        console.error('Kategori tidak ditemukan untuk ID:', option?.dataset.id);
                        return;
                    }
                    const combo = edit.closest('.ph-combo');
                    const menu = combo.querySelector('.ph-combo-menu');
                    const editor = document.createElement('div');
                    editor.className = 'ph-combo-editor';
                    editor.innerHTML =
                        '<input type="text" maxlength="150" aria-label="Edit nama kategori">' +
                        '<button type="button" class="ph-combo-edit-save">Save</button>' +
                        '<button type="button" class="ph-combo-edit-cancel">Cancel</button>';
                    option.replaceChildren(editor);
                    editor.querySelector('input').value = cat.name;
                    menu.hidden = false;
                    editor.querySelector('input').focus();
                    editor.querySelector('input').select();
                    return;
                }
                const cancelEdit = e.target.closest('.ph-combo-edit-cancel');
                if (cancelEdit) {
                    const combo = cancelEdit.closest('.ph-combo');
                    renderMenu(combo, combo.querySelector('.ph-combo-input').value);
                    return;
                }
                const saveEdit = e.target.closest('.ph-combo-edit-save');
                if (saveEdit) {
                    const option = saveEdit.closest('.ph-combo-option'),
                        id = option.dataset.id;
                    const name = option.querySelector('input').value.trim();
                    if (!name) return alert('Nama kategori wajib diisi.');
                    saveEdit.disabled = true;
                    try {
                        const result = await send(categoryUpdateTemplate.replace('__ID__',
                            encodeURIComponent(id)), 'PATCH', {
                            name
                        });
                        const cat = categoryById(id);
                        if (cat) cat.name = result.name;
                        root.querySelectorAll('.ph-combo').forEach(combo => {
                            if (String(combo.querySelector('.job-category').value) === String(id)) {
                                combo.querySelector('.ph-combo-input').value = result.name;
                            }
                        });
                        renderMenu(saveEdit.closest('.ph-combo'), '');
                    } catch (error) {
                        alert(error.message);
                        saveEdit.disabled = false;
                    }
                    return;
                }
                const addCategory = e.target.closest('.ph-combo-add');
                if (addCategory) {
                    const combo = addCategory.closest('.ph-combo'),
                        name = combo.querySelector('.ph-combo-input').value.trim();
                    if (!name) return;
                    addCategory.disabled = true;
                    try {
                        const result = await send(categoryCreateUrl, 'POST', {
                            name
                        });
                        if (!categoryById(result.id)) categories.push({
                            id: result.id,
                            name: result.name
                        });
                        selectCategory(combo, result);
                    } catch (error) {
                        alert(error.message);
                        addCategory.disabled = false;
                    }
                    return;
                }
                const toggle = e.target.closest('.ph-remark-toggle');
                if (toggle) {
                    const remarks = toggle.closest('.ph-job').querySelector('.ph-remarks');
                    remarks.hidden = !remarks.hidden;
                    toggle.textContent = remarks.hidden ? '▸ Show Remark' : '▾ Hide Remark';
                    toggle.setAttribute('aria-expanded', String(!remarks.hidden));
                    return;
                }
                const add = e.target.closest('.ph-add');
                if (add) {
                    const list = add.closest('.ph-work').querySelector('.ph-jobs');
                    const clone = list.querySelector('.ph-job').cloneNode(true);
                    resetJob(clone);
                    list.appendChild(clone);
                    return;
                }
                const save = e.target.closest('.ph-save');
                if (save) {
                    const row = save.closest('.ph-job'),
                        work = save.closest('.ph-work'),
                        status = row.querySelector('.ph-status');
                    const data = {
                        id: row.dataset.jobId || null,
                        po_id: work.dataset.poId,
                        detail_po_id: work.dataset.detailId,
                        article_code: work.dataset.article,
                        bahan_kategori_id: row.querySelector('.job-category').value,
                        sub_name: row.querySelector('.job-sub-name').value
                    };
                    if (!data.bahan_kategori_id) {
                        status.textContent = 'Pilih pekerjaan dahulu.';
                        return;
                    }
                    for (const el of row.querySelectorAll('.ph-price')) {
                        const n = numericValue(el.value);
                        if (Number.isNaN(n)) {
                            status.textContent = 'Nominal tidak valid.';
                            el.focus();
                            return;
                        }
                        if (!el.disabled) data[el.dataset.field] = n;
                    }
                    row.querySelectorAll('.ph-remark:not(:disabled)').forEach(el => data[el.dataset.field] =
                        el.value);
                    save.disabled = true;
                    status.textContent = 'Menyimpan...';
                    try {
                        const result = await send(saveUrl, 'POST', data);
                        row.dataset.jobId = result.id;
                        ['ppic_price', 'director_price', 'pengajuan_sub_price'].forEach((field) => {
                            const key = field === 'ppic_price' ? 'ppic_date' : field ===
                                'director_price' ? 'directur_date' : 'sub_date';
                            const dateEl = row.querySelector('[data-date="' + field + '"]');
                            if (dateEl) dateEl.textContent = result[key] || '—';
                        });
                        if (result.price_history) {
                            row.querySelectorAll('.ph-price-history').forEach(el => {
                                const items = result.price_history[el.dataset.history] || [];
                                el.replaceChildren();
                                if (items.length < 2) return;
                                items.forEach((entry, index) => {
                                    if (index) { const arrow = document.createElement('div'); arrow.className = 'ph-history-arrow'; arrow.textContent = '↓'; el.appendChild(arrow); }
                                    const line = document.createElement('div'); line.className = 'ph-history-entry' + (index === items.length - 1 ? ' is-latest' : '');
                                    line.textContent = entry.price == null ? '—' : Number(entry.price).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2});
                                    const meta = document.createElement('small'); meta.textContent = (entry.at || '') + ' · ' + (entry.user_name || 'User'); line.appendChild(meta); el.appendChild(line);
                                });
                            });
                        }
                        status.textContent = 'Tersimpan ✓';
                        if (result.closed) { row.classList.add('is-closed'); row.dataset.closed = '1'; }
                    } catch (error) {
                        status.textContent = error.message;
                    } finally {
                        save.disabled = false;
                    }
                    return;
                }
                const close = e.target.closest('.ph-close');
                if (close) {
                    const row = close.closest('.ph-job');
                    const id = row.dataset.jobId;
                    if (!id) { alert('Simpan pekerjaan terlebih dahulu sebelum Close.'); return; }
                    if (row.dataset.closed === '1') return;
                    if (!confirm('Are you sure? Close pengajuan ini?')) return;
                    close.disabled = true;
                    try {
                        await send(closeUrlBase + '/' + encodeURIComponent(id) + '/close', 'PATCH', {});
                        row.classList.add('is-closed');
                        row.dataset.closed = '1';
                        close.textContent = '✓ Closed';
                        row.querySelector('.ph-status').textContent = 'Closed ✓';
                    } catch (error) {
                        close.disabled = false;
                        alert(error.message);
                    }
                    return;
                }
                const del = e.target.closest('.ph-delete');
                if (del) {
                    const row = del.closest('.ph-job'),
                        list = row.closest('.ph-jobs'),
                        id = row.dataset.jobId;
                    if (!confirm('Hapus pekerjaan ini?')) return;
                    del.disabled = true;
                    try {
                        if (id) await send(deleteUrl, 'DELETE', {
                            id
                        });
                        if (list.querySelectorAll('.ph-job').length > 1) row.remove();
                        else resetJob(row);
                    } catch (error) {
                        alert(error.message);
                    } finally {
                        del.disabled = false;
                    }
                }
            });

            // Hover on the latest green PPIC history to reveal Edit/Delete.
            function formatHistoryPrice(v) { return v == null ? '—' : Number(v).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2}); }
            function renderPpicHistory(row, items) {
                const el = row.querySelector('.ph-price-history[data-history="ppic_price"]');
                if (!el) return;
                el.replaceChildren();
                if (items.length < 2) return;
                items.forEach((entry, index) => {
                    if (index) { const a=document.createElement('div');a.className='ph-history-arrow';a.textContent='↓';el.appendChild(a); }
                    const line=document.createElement('div');line.className='ph-history-entry'+(index===items.length-1?' is-latest':'');
                    line.dataset.historyIndex=index;
                    line.append(document.createTextNode(formatHistoryPrice(entry.price)));
                    const meta=document.createElement('small');meta.textContent=(entry.at||'')+' · '+(entry.user_name||'User');line.appendChild(meta);
                    el.appendChild(line);
                });
            }
            // Actions are rendered on mouse hover, no timer / long-press.
            root.addEventListener('pointerover', e => {
                const entry=e.target.closest('.ph-price-history[data-history="ppic_price"] .ph-history-entry.is-latest');
                if (!entry || entry.closest('.ph-job')?.dataset.closed==='1' || entry.querySelector('.ph-history-tools') || entry.querySelector('.ph-history-inline')) return;
                const tools=document.createElement('div');tools.className='ph-history-tools';
                tools.innerHTML='<button type="button" class="ph-hist-edit" title="Edit harga" aria-label="Edit harga">✎</button><button type="button" class="ph-hist-delete" title="Hapus riwayat" aria-label="Hapus riwayat">🗑</button>';
                entry.appendChild(tools);
            });
            root.addEventListener('pointerout', e => {
                const entry=e.target.closest('.ph-price-history[data-history="ppic_price"] .ph-history-entry.is-latest');
                if (!entry || entry.contains(e.relatedTarget)) return;
                entry.querySelector('.ph-history-tools')?.remove();
            });
            root.addEventListener('click', async e => {
                const edit=e.target.closest('.ph-hist-edit');
                const del=e.target.closest('.ph-hist-delete');
                const commit=e.target.closest('.ph-hist-commit');
                const cancel=e.target.closest('.ph-hist-cancel');
                if(!edit && !del && !commit && !cancel) return;
                e.preventDefault();e.stopPropagation();
                const entry=e.target.closest('.ph-history-entry');
                const row=entry.closest('.ph-job');
                const id=row.dataset.jobId;
                if(!id) return alert('Simpan pekerjaan terlebih dahulu.');
                if(cancel){entry.querySelector('.ph-history-inline')?.remove();entry.querySelector('.ph-history-tools')?.remove();return;}
                if(edit){
                    entry.querySelector('.ph-history-tools')?.remove();
                    const form=document.createElement('div');form.className='ph-history-inline';
                    form.innerHTML='<input type="text" inputmode="decimal" aria-label="Revisi harga PPIC"><button type="button" class="ph-hist-commit">✓</button><button type="button" class="ph-hist-cancel">×</button>';
                    const raw=entry.firstChild?.textContent?.trim()||'0';form.querySelector('input').value=raw;
                    entry.appendChild(form);form.querySelector('input').focus();return;
                }
                let action='delete', price=null;
                if(commit){
                    action='edit';
                    const raw=entry.querySelector('.ph-history-inline input').value.trim();
                    price=Number(raw.replace(/\./g,'').replace(',','.'));
                    if(!raw || !Number.isFinite(price) || price<0) return alert('Harga tidak valid.');
                } else if(!confirm('Are you sure? Hapus riwayat harga PPIC ini?')) return;
                try {
                    const payload={index:Number(entry.dataset.historyIndex),action};
                    if(action==='edit') payload.price=price;
                    const result=await send(historyUrlBase+'/'+encodeURIComponent(id)+'/ppic-history','PATCH',payload);
                    renderPpicHistory(row,result.price_history.ppic_price||[]);
                    const input=row.querySelector('.ph-price[data-field="ppic_price"]');
                    if(input) input.value=result.ppic_price==null?'':formatHistoryPrice(result.ppic_price);
                    const date=row.querySelector('[data-date="ppic_price"]');if(date) date.textContent=result.ppic_date||'—';
                }catch(error){alert(error.message);}
            });
        })();
    </script>
@endsection
