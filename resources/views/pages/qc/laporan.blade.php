@extends('master.master')



@section('title', 'QC Monitor')



@section('content')

    {{-- @include('pages.exports.partials.style')

    @include('pages.spk.stylespk')

    @include('pages.marketing.style') --}}



    <div class="padding">

        <div class="box qc-monitor-box">

            <div class="box-header">

            @section('btn')

                <h4>QC Monitor</h4>

            @endsection



            <div class="header-controls">

                <div class="filter-toolbar">

                    <div class="filter-item">

                        <label for="qc-user">Inspector</label>

                        <select class="form-control" id="qc-user">

                            <option value="">Semua Inspector</option>

                            @foreach ($qcs as $qc)
                                <option value="{{ $qc->id }}">

                                    {{ optional($qc->karyawan)->nama_lengkap ?: $qc->name }}

                                    @if (optional($qc->karyawan)->divisi)
                                        ({{ $qc->karyawan->divisi->nama }})
                                    @endif

                                </option>
                            @endforeach

                        </select>

                    </div>



                    <div class="filter-item">
                        <label for="qc-po-prefix">Prefix PO</label>
                        <select class="form-control" id="qc-po-prefix">
                            <option value="">Semua Prefix</option>
                            <option value="NW">NW</option>
                            <option value="NWS">NWS</option>
                            <option value="NWR">NWR</option>
                            <option value="NWD">NWD</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="qc-company">Company Name / Buyer</label>
                        <select class="form-control" id="qc-company">
                            <option value="">Semua Company</option>
                        </select>
                    </div>

                    <div class="filter-item">

                        <label for="date_from">From</label>

                        <input type="date" class="form-control date-filter" id="date_from">

                    </div>



                    <div class="filter-item">

                        <label for="date_to">To</label>

                        <input type="date" class="form-control date-filter" id="date_to">

                    </div>



                    <div class="filter-buttons">

                        <button type="button" class="btn btn-primary" id="btn-filter">

                            <i class="fa fa-search"></i> Filter

                        </button>



                        <button type="button" class="btn btn-outline-secondary" id="btn-reset">

                            <i class="fas fa-rotate-left"></i> Reset

                        </button>



                        <button type="button" class="btn btn-warning" id="btn-classify">

                            <i class="fas fa-layer-group"></i> Klasifikasi

                        </button>



                        {{-- <button type="button" class="btn btn-success" id="btn-export" disabled>

                                <i class="fa fa-file-excel"></i> Download QC Pass

                            </button> --}}

                    </div>

                </div>

            </div>

        </div>



        <div class="box-body">

            <div class="monitor-wrapper">

                <div class="panel">

                    <div class="card-header qc-table-header d-flex justify-content-between align-items-center">

                        <div>

                            <div class="qc-table-title">

                                <i class="fas fa-table-list"></i> Inspection QC

                            </div>

                            <div class="qc-table-subtitle" id="table-mode-label">

                                Data inspection

                            </div>

                        </div>



                        <button type="button" class="btn btn-light btn-sm shadow-sm" id="btn-detail"
                            style="display:none;color:#111;">

                            <i class="fas fa-eye me-1"></i> Detail Report

                        </button>

                    </div>



                    <div class="card-body p-0">

                        <div class="panel-scroll">

                            <table class="table table-bordered mb-0" id="inspection-table">

                                <thead>

                                    <tr>

                                        <th class="col-no">#</th>

                                        <th class="col-date">TANGGAL, JAM</th>

                                        <th class="col-po">PO</th>

                                        <th class="col-item">NAME ITEMS</th>

                                        <th class="col-buyer">BUYER</th>

                                        <th class="col-spk">NO. SPK</th>

                                        <th class="col-subkon">SUBKON</th>

                                        <th class="col-person">PERSON</th>

                                        <th class="col-siti">SITI (IN)</th>

                                        <th class="col-pass">PASS</th>



                                    </tr>

                                </thead>

                                <tbody id="detail-list">

                                    @forelse ($inspection as $inspected)
                                        @php

                                            $detail = $inspected->detailPo->detail ?? [];

                                            $spkData = $inspected->spk->data ?? [];

                                            $kategori = optional($inspected->kategori)->kategori ?? '';

                                            $kategoriId = (int) ($inspected->kategori_id ?? 0);

                                            $description = data_get($detail, 'description', '-');

                                            $noSpk = data_get($spkData, 'no_spk', '-');

                                            $sup = data_get($spkData, 'sup', '-');

                                        @endphp

                                        <tr class="inspection-row" data-id="{{ $inspected->id }}"
                                            data-spk-id="{{ $inspected->spk_id }}"
                                            data-detail-po-id="{{ $inspected->detail_po_id }}"
                                            data-category="{{ $kategori }}" data-category-id="{{ $kategoriId }}"
                                            data-spk="{{ $noSpk }}" data-item="{{ $description }}"
                                            data-buyer="{{ optional($inspected->po)->company_name ?? '-' }}"
                                            data-po="{{ optional($inspected->po)->order_no ?? '-' }}"
                                            data-user="{{ $inspected->user_id }}"
                                            data-date="{{ $inspected->tanggal_inspect }}"
                                            data-url="{{ route('qc.laporans', [
                                                'user_id' => $inspected->user_id,
                                            
                                                'from' => request('from'),
                                            
                                                'to' => request('to'),
                                            
                                                'detail_po_id' => $inspected->detail_po_id,
                                            
                                                'kategori' => $kategori,
                                            ]) }}">

                                            <td>{{ $loop->iteration }}</td>

                                            <td>

                                                {{ $inspected->tanggal_inspect ? \Carbon\Carbon::parse($inspected->tanggal_inspect)->format('d M Y') : '-' }}

                                                <br>

                                                <small class="text-muted">

                                                    {{ $inspected->created_at ? $inspected->created_at->diffForHumans() : '' }}

                                                </small>

                                            </td>

                                            <td>{{ optional($inspected->po)->order_no ?? '-' }}</td>

                                            <td class="item-cell">{{ $description }}</td>

                                            <td>{{ optional($inspected->po)->company_name ?? '-' }}</td>

                                            @if (in_array($kategoriId, [6, 7, 8], true))
                                                <td colspan="2" class="category-merged-cell">

                                                    {{ [6 => 'Unfinish', 7 => 'Final', 8 => 'Packaging'][$kategoriId] }}

                                                </td>
                                            @else
                                                <td>

                                                    <div>{{ $noSpk }}</div>

                                                </td>

                                                <td>{{ $sup }}</td>
                                            @endif

                                            <td>{{ optional($inspected->user)->name ?? '-' }}</td>

                                            <td class="text-center siti-cell">
                                                {{ number_format((float) ($sitiInMap[(int) $inspected->spk_id . ':' . (int) $inspected->detail_po_id] ?? 0), 0, ',', '.') }}
                                            </td>

                                            <td class="text-center pass-cell">{{ $inspected->passed ?? 0 }}</td>



                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="10" class="empty-row">Belum ada data inspection.</td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



@php

    // Siapkan data untuk JavaScript DI LUAR directive @json.

    // Jangan taruh closure map() langsung di @json karena parser Blade dapat

    // salah membaca tanda kurung/array dan menghasilkan ParseError.

    $allInspectionsForJs = $inspection
        ->values()
        ->map(function ($item) use ($sitiInMap) {
            $detail = optional($item->detailPo)->detail ?? [];

            $spkData = optional($item->spk)->data ?? [];

            // Beberapa data SPK tersimpan sebagai JSON string/object.

            // Normalisasi dulu supaya no_spk dan sup selalu terbaca oleh JS.

            if (is_string($spkData)) {
                $decodedSpk = json_decode($spkData, true);

                $spkData = is_array($decodedSpk) ? $decodedSpk : [];
            } elseif (is_object($spkData)) {
                $spkData = json_decode(json_encode($spkData), true) ?: [];
            }

            if (is_string($detail)) {
                $decodedDetail = json_decode($detail, true);

                $detail = is_array($decodedDetail) ? $decodedDetail : [];
            } elseif (is_object($detail)) {
                $detail = json_decode(json_encode($detail), true) ?: [];
            }

            $spkNo = $spkData['no_spk'] ?? '';

            $supplier = $spkData['sup'] ?? '';

            $person = optional($item->user)->name ?? '';

            return [
                'id' => $item->id,

                'user_id' => $item->user_id,

                'tanggal_inspect' => $item->tanggal_inspect,

                'created_at' => optional($item->created_at)->toIso8601String(),

                'po_id' => $item->po_id,
                'spk_id' => $item->spk_id,
                'siti_in' => (float) ($sitiInMap[(int) $item->spk_id . ':' . (int) $item->detail_po_id] ?? 0),
                'siti_key' => (int) $item->spk_id . ':' . (int) $item->detail_po_id,

                'detail_po_id' => $item->detail_po_id,

                'kategori_id' => $item->kategori_id,

                'kategori' => optional($item->kategori)->kategori ?? '',

                'po' => optional($item->po)->order_no ?? '-',

                'item' => $detail['description'] ?? '-',

                'buyer' => optional($item->po)->company_name ?? '-',

                'spk' => $spkNo ?: '-',

                'sup' => $supplier ?: '-',

                'person' => $person ?: '-',

                'jumlah_inspect' => (float) ($item->jumlah_inspect ?? 0),

                'passed' => (float) ($item->passed ?? 0),

                'rejected' => (float) ($item->rejected ?? 0),
            ];
        })
        ->values()
        ->all();

@endphp



{{-- QC SPK Detail: mengikuti panel Informasi SPK / Items / Inventory Timeline pada inventor --}}
{{-- Detail SPK: layout mengikuti modal Monitoring SPK /produksi/inventor --}}
<style>
    #qcSpkDetailModal .modal-dialog {
        max-width: 98vw;
        width: 98vw;
        margin: 2vh auto;
    }

    #qcSpkDetailModal .modal-content {
        min-height: 86vh;
        border: 0;
    }

    #qcSpkDetailModal .modal-body {
        background: #f8fafc;
    }

    #qcSpkDetailModal .card {
        border: 0;
        box-shadow: 0 1px 5px #00000010;
    }

    #qcSpkDetailModal .card-header {
        font-weight: 700;
        font-size: 12px;
    }

    #qcSpkDetailModal .table {
        font-size: 12px;
    }

    #qcSpkDetailModal .table thead th {
        background: #30465e;
        color: white;
        white-space: nowrap;
    }

    #qcSpkDetailModal .qc-timeline-scroll {
        max-height: 230px;
        overflow: auto;
    }

    #qcSpkDetailModal .qc-payment-scroll {
        max-height: 48vh;
        overflow: auto;
    }

    #qcSpkDetailModal .qc-detail-row {
        cursor: pointer;
    }

    #qcSpkDetailModal .qc-item-ellipsis {
        display: block;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #qcSpkDetailModal .qc-timeline-in-range {
        background: #e5f4e9 !important;
    }

    #qcSpkDetailModal .qc-timeline-out-range {
        opacity: .65;
    }

    #qcSpkDetailModal .qc-timeline-scroll {
        max-height: 280px;
    }

    @media (max-width: 767px) {
        #qcSpkDetailModal .modal-dialog {
            max-width: 100vw;
            width: 100vw;
            margin: 0;
        }
    }
</style>
<div class="modal fade" id="qcSpkDetailModal" tabindex="-1" aria-labelledby="qcSpkModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title" id="qcSpkModalTitle">DETAIL SPK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">INFORMASI SPK</div>
                            <div class="card-body" id="qcSpkInfo">Memuat...</div>
                        </div>
                        <div class="card">
                            <div class="card-header bg-light">LIST ITEM</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Global IN</th>
                                            <th>IN Range</th>
                                            <th>Pass Range</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qcSpkItems"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="card mb-3">
                            <div class="card-header bg-warning">INVENTORY TIMELINE <small class="ms-2"
                                    id="qcTimelineRange"></small></div>
                            <div class="qc-timeline-scroll">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Items</th>
                                            <th>Supplier</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qcSpkTimeline"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card">
                            <div
                                class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <span>LIST PAYMENT</span><a id="qcSpkOpen" href="#" target="_blank"
                                    rel="noopener noreferrer" class="btn btn-sm text-white">GO TO SPK</a>
                            </div>
                            <div class="qc-payment-scroll">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Type</th>
                                            <th>Nilai</th>
                                            <th>Saldo</th>
                                            <th>Keterangan</th>
                                            <th>Harga</th>
                                            <th>Total</th>
                                            <th>Status Request</th>
                                            <th>Adjustment Finance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="qcSpkPayments"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="rejectHoverPopup" class="reject-hover-popup" aria-hidden="true"></div>



<script src="https\://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const TOKEN = @json(optional(auth()->user())->api_token);

    const ALL_INSPECTIONS = @json($allInspectionsForJs);



    let currentRows = [...ALL_INSPECTIONS];

    let classifiedRows = null;

    let isClassified = false;



    function esc(value) {

        return $('<div>').text(value ?? '-').html();

    }



    function numberValue(value) {

        const n = Number(value);

        return Number.isFinite(n) ? n : 0;

    }



    function formatNumber(value) {

        const n = numberValue(value);

        return Number.isInteger(n)

            ?
            n.toLocaleString('id-ID')

            :
            n.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });

    }



    function formatDate(value) {

        if (!value) return '-';

        const d = new Date(value + (String(value).length === 10 ? 'T00:00:00' : ''));

        if (Number.isNaN(d.getTime())) return value;

        return d.toLocaleDateString('en-GB', {

            day: '2-digit',

            month: 'short',

            year: 'numeric'

        });

    }



    function relativeTime(value) {

        if (!value) return '';

        const d = new Date(value);

        if (Number.isNaN(d.getTime())) return '';

        const seconds = Math.floor((Date.now() - d.getTime()) / 1000);

        if (seconds < 60) return `${Math.max(seconds, 0)} seconds ago`;

        const minutes = Math.floor(seconds / 60);

        if (minutes < 60) return `${minutes} minutes ago`;

        const hours = Math.floor(minutes / 60);

        if (hours < 24) return `${hours} hours ago`;

        const days = Math.floor(hours / 24);

        return `${days} days ago`;

    }



    function rowUrl(row) {

        const base = @json(route('qc.laporans'));

        const params = new URLSearchParams({

            user_id: row.user_id ?? '',

            from: $('#date_from').val() || '',

            to: $('#date_to').val() || '',

            detail_po_id: row.detail_po_id ?? '',

            kategori: row.kategori ?? ''

        });

        return `${base}?${params.toString()}`;

    }



    function rejectSourceRows(row, isGrouped = false) {

        if (isGrouped && Array.isArray(row.source_rows)) {

            return row.source_rows.filter(item => numberValue(item.rejected) > 0);

        }



        return numberValue(row.rejected) > 0 ? [row] : [];

    }



    function formatRejectDateTime(row) {

        const date = formatDate(row?.tanggal_inspect);

        const created = row?.created_at ? new Date(row.created_at) : null;



        if (created && !Number.isNaN(created.getTime())) {

            const hh = String(created.getHours()).padStart(2, '0');

            const mm = String(created.getMinutes()).padStart(2, '0');

            return `${esc(date)}<br><small>${hh}:${mm}</small>`;

        }



        return esc(date);

    }



    function renderRejectCell(row, isGrouped = false) {

        const rejected = numberValue(row.rejected);

        const details = rejectSourceRows(row, isGrouped);



        if (rejected <= 0 || !details.length) {

            return `<span class="reject-number">${formatNumber(rejected)}</span>`;

        }



        const rowsHtml = details.map((item, index) => `

                <tr>

                    <td>${index + 1}</td>

                    <td>${formatRejectDateTime(item)}</td>

                    <td class="text-right">${formatNumber(item.rejected)}</td>

                    <td>${displayValue(item.person)}</td>

                </tr>

            `).join('');



        return `

                <span class="reject-hover-trigger" tabindex="0" data-reject-total="${rejected}">

                    ${formatNumber(rejected)}

                </span>

                <div class="reject-hover-data" hidden>

                    <div class="reject-hover-title">

                        <span>DETAIL REJECT</span>

                        <strong>Total: ${formatNumber(rejected)}</strong>

                    </div>

                    <div class="reject-hover-scroll">

                        <table>

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>TANGGAL JAM</th>

                                    <th>QTY</th>

                                    <th>PERSON</th>

                                </tr>

                            </thead>

                            <tbody>

                                ${rowsHtml}

                            </tbody>

                        </table>

                    </div>

                </div>

            `;

    }



    function renderRows(rows, classified = false) {
        window.qcVisibleRows = rows;


        const $tbody = $('#detail-list');

        $tbody.empty();



        if (!rows.length) {

            $tbody.html('<tr><td colspan="10" class="empty-row">Tidak ada data untuk filter yang dipilih.</td></tr>');

            updateSummary([]);

            return;

        }



        rows.forEach((row, index) => {

            // Hanya baris yang benar-benar hasil grouping yang diberi tampilan grouping.

            // Baris Anyam yang key-nya tidak lengkap tetap ditampilkan sebagai data normal.

            const isGrouped = classified && row._grouped === true;



            const kategoriId = Number(row.kategori_id);

            const isCategoryMerge = [6, 7, 8].includes(kategoriId);

            const categoryLabels = {

                6: 'Unfinish',

                7: 'Final',

                8: 'Packaging'

            };



            const spkLine = `<div>${displayValue(row.spk)}</div>`;



            const subkon = isGrouped

                ?
                ((row.subkons || []).filter(Boolean).map(esc).join(', ') || '')

                :
                displayValue(row.sup);



            const person = isGrouped

                ?
                ((row.persons || []).filter(Boolean).map(esc).join(', ') || '')

                :
                displayValue(row.person);



            const dateText = isGrouped

                ?
                (row.date_range ?
                    `${esc(row.date_range)}<br><small class="text-muted">tanggal inspection</small>` : '')

                :
                `${formatDate(row.tanggal_inspect)}<br><small class="text-muted">${esc(relativeTime(row.created_at))}</small>`;



            const url = isGrouped ? '#' : rowUrl(row);



            $tbody.append(`

                    <tr class="inspection-row ${isGrouped ? 'classified-row' : ''}"

                        data-url="${esc(url)}"
                        data-row-index="${index}"
                        data-spk-id="${esc(row.spk_id || (row.source_rows || []).find(r => Number(r.spk_id) > 0)?.spk_id || '')}"
                        data-detail-po-id="${esc(row.detail_po_id || '')}"

                        data-category="${esc(row.kategori || '')}"

                        data-spk="${esc(row.spk || '')}"

                        data-item="${esc(row.item || '')}"

                        data-buyer="${esc(row.buyer || '')}">

                        <td>${index + 1}</td>

                        <td>${dateText}</td>

                        <td>${displayValue(row.po)}</td>

                        <td class="item-cell">${displayValue(row.item)}</td>

                        <td>${displayValue(row.buyer)}</td>

                        ${isCategoryMerge

                            ? `<td colspan="2" class="category-merged-cell">${esc(categoryLabels[kategoriId] || row.kategori || '')}</td>`

                            : `<td>${spkLine}</td><td>${subkon}</td>`

                        }

                        <td>${person}</td>

                        <td class="text-center siti-cell">${formatNumber(row.siti_in)}</td>

                        <td class="text-center pass-cell">${formatNumber(row.passed)}</td>



                    </tr>

                `);

        });



        updateSummary(rows);

        bindRowClick();

    }



    function cleanText(value) {

        if (value === null || value === undefined) return '';

        const text = String(value).trim();

        if (!text || text === '-' || text.toLowerCase() === 'null' || text.toLowerCase() === 'undefined') {

            return '';

        }

        return text;

    }



    function displayValue(value) {

        const text = cleanText(value);

        return text ? esc(text) : '';

    }



    function isValidGroupValue(value) {

        return cleanText(value) !== '';

    }



    function updateSummary(rows) {







    }



    function getFilteredRows() {

        const inspector = $('#qc-user').val();
        const poPrefix = $('#qc-po-prefix').val();
        const company = $('#qc-company').val();

        const from = $('#date_from').val();

        const to = $('#date_to').val();



        return ALL_INSPECTIONS.filter(row => {

            if (inspector && String(row.user_id) !== String(inspector)) return false;

            // Prefix dipilih berdasarkan awalan nomor PO, bukan substring.
            // NW hanya cocok dengan NW, tidak termasuk NWS/NWR/NWD.
            const poNumber = cleanText(row.po).toUpperCase();
            const detectedPrefix = (poNumber.match(/^(NWS|NWR|NWD|NW)(?=[^A-Z]|$)/) || [])[1] || '';
            if (poPrefix && detectedPrefix !== poPrefix) return false;
            if (company && cleanText(row.buyer).toLowerCase() !== company.toLowerCase()) return false;



            // Kalau filter range tanggal dipakai, data tanpa tanggal inspection

            // TIDAK boleh ikut hasil filter.

            if (from || to) {

                const inspectionDate = cleanText(row.tanggal_inspect).substring(0, 10);

                if (!inspectionDate) return false;

                if (from && inspectionDate < from) return false;

                if (to && inspectionDate > to) return false;

            }



            return true;

        });

    }



    function applyFilter() {

        const from = $('#date_from').val();

        const to = $('#date_to').val();



        if ((from && !to) || (!from && to)) {

            alert('Silakan isi From dan To terlebih dahulu.');

            return;

        }



        if (from && to && from > to) {

            alert('Tanggal From tidak boleh lebih besar dari To.');

            return;

        }



        isClassified = false;

        classifiedRows = null;

        currentRows = getFilteredRows();



        renderRows(currentRows, false);

        $('#table-mode-label').text('Data inspection hasil filter');

        $('#btn-detail').toggle(currentRows.length > 0);

        $('#btn-export').prop('disabled', !(from && to));



        // Klasifikasi tersedia untuk semua hasil filter, tanggal opsional.

        if (currentRows.length) {

            $('#btn-classify').fadeIn(150);

        } else {

            $('#btn-classify').hide();

        }



        $('#btn-classify').html('<i class="fas fa-layer-group"></i> Klasifikasi');

    }



    function classifyAnyamRows() {

        const from = $('#date_from').val();

        const to = $('#date_to').val();



        // Klasifikasi dapat dijalankan tanpa filter tanggal.
        // Gunakan currentRows agar filter inspector, buyer, PO, dan tanggal tetap dihormati.
        // Klasifikasi seluruh kategori SPK kecuali Unfinish/Final/Packaging.
        // Nama kategori tidak lagi menentukan apakah baris bisa digabung.
        const source = currentRows.filter(row =>
            ![6, 7, 8].includes(Number(row.kategori_id))
        );

        const otherRows = currentRows.filter(row => [6, 7, 8].includes(Number(row.kategori_id)));

        const map = new Map();

        const ungroupableAnyam = [];



        source.forEach(row => {

            const spk = cleanText(row.spk);

            const item = cleanText(row.item);

            const buyer = cleanText(row.buyer);



            // Prioritaskan ID database, agar beda penulisan teks tidak memecah grup.
            // Jika ID SPK kosong, gunakan nomor SPK dan item sebagai fallback.
            const spkId = Number(row.spk_id || 0);
            const detailId = Number(row.detail_po_id || 0);
            const hasIds = spkId > 0 && detailId > 0;
            if (!hasIds && (!isValidGroupValue(spk) || !isValidGroupValue(item))) {
                ungroupableAnyam.push({
                    ...row,
                    _grouped: false
                });
                return;
            }

            const normalized = v => cleanText(v).toLowerCase().replace(/\s+/g, ' ');
            const key = hasIds ?
                `id:${spkId}:${detailId}` :
                `text:${normalized(spk)}||${normalized(item)}||${normalized(buyer)}`;

            if (!map.has(key)) {

                map.set(key, {

                    ...row,

                    spk,

                    item,

                    buyer,

                    kategori: row.kategori,

                    persons: [],

                    subkons: [],

                    dates: [],

                    date_range: '',

                    group_count: 0,

                    jumlah_inspect: 0,
                    siti_in: 0,
                    siti_keys: [],

                    passed: 0,

                    rejected: 0,

                    source_rows: [],

                    _grouped: true

                });

            }



            const target = map.get(key);



            target.source_rows.push({
                ...row
            });

            // SITI IN berasal dari mutasi SPK-item, bukan akumulasi inspection.
            // Satu pasangan SPK-item hanya dihitung sekali dalam satu grup.
            if (row.siti_key && !target.siti_keys.includes(row.siti_key)) {
                target.siti_keys.push(row.siti_key);
                target.siti_in += numberValue(row.siti_in);
            }
            target.jumlah_inspect += numberValue(row.jumlah_inspect);

            target.passed += numberValue(row.passed);

            target.rejected += numberValue(row.rejected);

            target.group_count += 1;



            const person = cleanText(row.person);

            if (person && !target.persons.includes(person)) {

                target.persons.push(person);

            }



            const subkon = cleanText(row.sup);

            if (subkon && !target.subkons.includes(subkon)) {

                target.subkons.push(subkon);

            }



            const inspectionDate = cleanText(row.tanggal_inspect).substring(0, 10);

            if (inspectionDate && !target.dates.includes(inspectionDate)) {

                target.dates.push(inspectionDate);

            }

        });



        map.forEach(target => {

            target.dates.sort();



            if (target.dates.length === 1) {

                target.date_range = formatDate(target.dates[0]);

            } else if (target.dates.length > 1) {

                target.date_range =
                    `${formatDate(target.dates[0])} - ${formatDate(target.dates[target.dates.length - 1])}`;

            }

        });



        // Gabungan:

        // 1. hasil group Anyam yang key lengkap

        // 2. Anyam yang key-nya tidak lengkap -> tetap normal

        // 3. kategori lain -> tetap normal

        const classified = [

            ...map.values(),

            ...ungroupableAnyam,

            ...otherRows

        ];



        // Urutkan berdasarkan tanggal inspection terbaru.

        classified.sort((a, b) => {

            const da = cleanText(a.tanggal_inspect) || (a.dates?.[a.dates.length - 1] ?? '');

            const db = cleanText(b.tanggal_inspect) || (b.dates?.[b.dates.length - 1] ?? '');

            return String(db).localeCompare(String(da));

        });



        classifiedRows = classified;

        isClassified = true;



        renderRows(classifiedRows, true);

        $('#table-mode-label').text(
            'Klasifikasi: digabung berdasarkan SPK + Item (Unfinish/Final/Packaging tetap terpisah)');





        $('#btn-classify').html('<i class="fas fa-list"></i> Data Normal');

    }



    function resetFilter() {
        if (window.location.search.includes('date_from=') || window.location.search.includes('date_to=')) {
            window.location.href = window.location.pathname;
            return;
        }

        $('#qc-user').val('');
        $('#qc-po-prefix').val('');
        $('#qc-company').val('');

        $('#date_from').val('');

        $('#date_to').val('');

        currentRows = [...ALL_INSPECTIONS];

        classifiedRows = null;

        isClassified = false;

        renderRows(currentRows, false);

        $('#btn-classify').hide();



        $('#btn-detail').hide();

        $('#btn-export').prop('disabled', true);

        $('#table-mode-label').text('Data inspection');

        $('#btn-classify').html('<i class="fas fa-layer-group"></i> Klasifikasi');

    }



    function bindRowClick() {
        $('#detail-list').off('click.qcSpk', '.inspection-row')
            .on('click.qcSpk', '.inspection-row', function(event) {
                if ($(event.target).closest('a, button, input, select').length) return;
                $('#detail-list .inspection-row').removeClass('active');
                $(this).addClass('active');
                const spkId = Number($(this).attr('data-spk-id') || 0);
                const detailPoId = Number($(this).attr('data-detail-po-id') || 0);
                if (!spkId) {
                    alert('Baris ini tidak memiliki SPK yang dapat dibuka.');
                    return;
                }
                const rowIndex = Number($(this).attr("data-row-index"));
                const selectedRow = (window.qcVisibleRows || [])[rowIndex] || null;
                openQcSpkModal(spkId, detailPoId, selectedRow);
            });
    }

    function openQcSpkModal(spkId, detailPoId, selectedRow) {
        const from = $("#date_from").val();
        const to = $("#date_to").val();
        const inRange = date => {
            const day = String(date || "").slice(0, 10);
            return !!day && (!from || day >= from) && (!to || day <= to);
        };
        const filteredInspections = selectedRow ?
            (Array.isArray(selectedRow.source_rows) ? selectedRow.source_rows : [selectedRow])
            .filter(r => inRange(r.tanggal_inspect)) :
            [];
        const filteredPassed = filteredInspections.reduce((sum, r) => sum + Number(r.passed || 0), 0);

        const $modal = $('#qcSpkDetailModal');
        $("#qcTimelineRange").text(from && to ? `(Global; periode ${from} s/d ${to} ditandai hijau)` : "(Global)");
        $('#qcSpkInfo').html('<span class="text-muted">Memuat data SPK...</span>');
        $('#qcSpkItems').html('');
        $('#qcSpkTimeline').html('');
        $('#qcSpkPayments').html('<tr><td colspan="10" class="text-center">Memuat payment...</td></tr>');
        $('#qcSpkOpen').attr('href', '/spk/edit/' + encodeURIComponent(spkId));
        if ($.fn.modal) $modal.modal('show');
        else if (window.bootstrap?.Modal) bootstrap.Modal.getOrCreateInstance($modal[0]).show();
        $.ajax({
            url: '/inventor/spk/' + encodeURIComponent(spkId),
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                const safe = v => esc(v === null || v === undefined ? '-' : String(v));
                const supplier = typeof res.supplier === 'object' ? res.supplier?.name : res.supplier;
                $('#qcSpkInfo').html(`
                        <table class="table table-sm table-bordered mb-0">
                            <tr><th>No SPK</th><td>${safe(res.spk_no)}</td></tr>
                            <tr><th>Supplier</th><td>${safe(supplier)}</td></tr>
                            <tr><th>Kategori</th><td>${safe(res.kategori)}</td></tr>
                            <tr><th>Status</th><td>${safe(res.status)}</td></tr>
                        </table>`);
                const items = (Array.isArray(res.items) ? res.items : []).filter(item =>
                    !detailPoId || Number(item.detail_po_id) === detailPoId
                );
                const timelines = (Array.isArray(res.timelines) ? res.timelines : []).filter(row =>
                    !detailPoId || Number(row.detail_po_id || 0) === detailPoId ||
                    (!row.detail_po_id && items.length === 1)
                );
                const netOf = rows => rows.reduce((total, row) => {
                    const type = String(row.type || '').toLowerCase();
                    if (!['in', 'service_masuk', 'service_keluar'].includes(type)) return total;
                    return total + (type === 'service_keluar' ? -1 : 1) * Number(row.qty || 0);
                }, 0);
                const globalIn = Math.max(0, netOf(timelines));
                const rangeIn = Math.max(0, netOf(timelines.filter(row => inRange(row.date))));
                $('#qcSpkItems').html(items.length ? items.map(item => `
                        <tr>
                            <td><span class="qc-item-ellipsis" title="${safe(item.nama)}"><strong>${safe(item.nama)}</strong></span><small class="text-muted">${safe(item.kode)}</small></td>
                            <td class="text-end">${formatNumber(item.qty)}</td>
                            <td class="text-end">${formatNumber(globalIn)}</td>
                            <td class="text-end">${formatNumber(rangeIn)}</td>
                            <td class="text-end">${formatNumber(filteredPassed)}</td>
                        </tr>`).join('') : '<tr><td colspan="5" class="text-center">Tidak ada item</td></tr>');
                $('#qcSpkTimeline').html(timelines.length ? timelines.map((row, i) => `
                        <tr class="${inRange(row.date) ? 'qc-timeline-in-range' : 'qc-timeline-out-range'}"><td>${i + 1}</td><td>${safe(row.date)}</td>
                        <td>${safe(row.item_name)}<div class="small text-muted">${safe(row.item_code)}</div></td>
                        <td>${safe(supplier)}</td><td>${safe(row.type)}</td><td class="text-end">${formatNumber(row.qty)}</td>
                        <td>${safe(row.remark)}</td></tr>`).join('') :
                    '<tr><td colspan="7" class="text-center">Tidak ada mutasi SPK</td></tr>');
                // Data payment dari endpoint yang sama dengan modal Inventor.
                // Nilai di sini ditampilkan sesuai response; tidak menghitung ulang saldo/adjustment.
                const payments = Array.isArray(res.payments) ? res.payments : [];
                const money = v => v === null || v === undefined || v === '' ? '-' : 'Rp ' + Number(v || 0)
                    .toLocaleString('id-ID');
                const paymentRows = payments.map((pay, i) => {
                    const amount = pay.payment_request_amount ?? pay.amount ?? 0;
                    return `<tr><td>${i + 1}</td><td>${safe(pay.date)}</td>
                            <td>${safe(pay.payment_type || pay.type || pay.note)}</td>
                            <td>${money(amount)}</td><td>${money(pay.saldo ?? pay.balance)}</td>
                            <td>${safe(pay.keterangan ?? pay.note)}</td><td>${money(pay.harga ?? pay.price)}</td>
                            <td>${money(pay.total ?? amount)}</td>
                            <td>${safe(pay.status_request ?? pay.status)}</td>
                            <td>${safe(pay.adjustment_finance)}</td></tr>`;
                });
                $('#qcSpkPayments').html(paymentRows.length ? paymentRows.join('') :
                    '<tr><td colspan="10" class="text-center text-muted">Tidak ada data payment</td></tr>'
                    );
            },
            error: function(xhr) {
                $('#qcSpkPayments').html(
                    '<tr><td colspan="10" class="text-danger text-center">Gagal memuat payment</td></tr>'
                    );
                $('#qcSpkInfo').html('<div class="alert alert-danger">Gagal memuat SPK (HTTP ' + xhr
                    .status + '). Periksa akses endpoint /inventor/spk/{id}.</div>');
            }
        });
    }

    function bindRejectHover() {

        const popup = document.getElementById('rejectHoverPopup');

        if (!popup) return;



        let activeTrigger = null;

        let hideTimer = null;



        function hidePopup() {

            clearTimeout(hideTimer);

            popup.style.display = 'none';

            popup.innerHTML = '';

            activeTrigger = null;

        }



        function showPopup(trigger) {

            clearTimeout(hideTimer);



            const source = trigger.parentElement.querySelector('.reject-hover-data');

            if (!source) return;



            activeTrigger = trigger;

            popup.innerHTML = source.innerHTML;

            popup.style.display = 'block';

            popup.style.visibility = 'hidden';



            const rect = trigger.getBoundingClientRect();

            const popupWidth = Math.min(520, window.innerWidth - 24);

            const popupHeight = Math.min(popup.offsetHeight || 260, 360);



            popup.style.width = popupWidth + 'px';

            popup.style.maxHeight = '360px';



            let left = rect.right - popupWidth;

            if (left < 12) left = 12;

            if (left + popupWidth > window.innerWidth - 12) {

                left = window.innerWidth - popupWidth - 12;

            }



            let top = rect.bottom + 8;

            if (top + popupHeight > window.innerHeight - 12) {

                top = rect.top - popupHeight - 8;

            }

            if (top < 12) top = 12;



            popup.style.left = left + 'px';

            popup.style.top = top + 'px';

            popup.style.visibility = 'visible';

        }



        $(document).off('mouseenter.rejectHover', '.reject-hover-trigger')

            .on('mouseenter.rejectHover', '.reject-hover-trigger', function() {

                showPopup(this);

            })

            .off('mouseleave.rejectHover', '.reject-hover-trigger')

            .on('mouseleave.rejectHover', '.reject-hover-trigger', function() {

                hideTimer = setTimeout(hidePopup, 100);

            });



        $(document).off('mouseenter.rejectPopup', '#rejectHoverPopup')

            .on('mouseenter.rejectPopup', '#rejectHoverPopup', function() {

                clearTimeout(hideTimer);

            })

            .off('mouseleave.rejectPopup', '#rejectHoverPopup')

            .on('mouseleave.rejectPopup', '#rejectHoverPopup', function() {

                hideTimer = setTimeout(hidePopup, 100);

            });



        $(document).off('focus.rejectHover', '.reject-hover-trigger')

            .on('focus.rejectHover', '.reject-hover-trigger', function() {

                showPopup(this);

            })

            .off('blur.rejectHover', '.reject-hover-trigger')

            .on('blur.rejectHover', '.reject-hover-trigger', function() {

                hideTimer = setTimeout(hidePopup, 100);

            });



        window.addEventListener('scroll', hidePopup, true);

        window.addEventListener('resize', hidePopup);

    }



    /* =========================================================

       TOUR GUIDE QC MONITOR

       From -> To -> Filter -> Klasifikasi

       Bisa dinonaktifkan dan disimpan di browser.

       ========================================================= */

    const QC_TOUR_STORAGE_KEY = 'qc_monitor_tour_disabled_v1';

    let qcTourStep = 0;

    let qcTourActive = false;

    let qcTourWaitingFilter = false;



    const qcTourSteps = [

        {

            target: '#date_from',

            title: '1. Pilih Start Date',

            text: 'Tentukan tanggal mulai laporan QC di sini.',

            placement: 'bottom'

        },

        {

            target: '#date_to',

            title: '2. Pilih End Date',

            text: 'Setelah Start Date, pilih tanggal akhir laporan di sini.',

            placement: 'bottom'

        },

        {

            target: '#btn-filter',

            title: '3. Jalankan Filter',

            text: 'Klik tombol Filter untuk menampilkan data berdasarkan range tanggal dan inspector yang dipilih.',

            placement: 'bottom',

            waitForFilter: true

        },

        {

            target: '#btn-classify',

            title: '4. Klasifikasi Data',

            text: 'Setelah filter selesai, klik Klasifikasi untuk menggabungkan data Anyam yang memiliki SPK + Item + Buyer yang sama. Kategori 6, 7, dan 8 tetap ditampilkan sesuai kategorinya dan tidak digabung.',

            placement: 'bottom'

        }

    ];



    function qcTourTarget() {

        const step = qcTourSteps[qcTourStep];

        return step ? document.querySelector(step.target) : null;

    }



    function qcTourEnsureUi() {

        if (document.getElementById('qcTourOverlay')) return;



        document.body.insertAdjacentHTML('beforeend', `

                <div id="qcTourOverlay" aria-hidden="true">

                    <div id="qcTourBackdrop"></div>

                    <div id="qcTourFocus"></div>

                    <div id="qcTourCard" role="dialog" aria-modal="true" aria-live="polite">

                        <div class="qc-tour-arrow"></div>

                        <div class="qc-tour-title" id="qcTourTitle"></div>

                        <div class="qc-tour-text" id="qcTourText"></div>

                        <div class="qc-tour-footer">

                            <label class="qc-tour-disable">

                                <input type="checkbox" id="qcTourDisable">

                                <span>Jangan tampilkan lagi</span>

                            </label>

                            <div class="qc-tour-actions">

                                <button type="button" class="qc-tour-btn qc-tour-skip" id="qcTourSkip">Lewati</button>

                                <button type="button" class="qc-tour-btn qc-tour-next" id="qcTourNext">Berikutnya</button>

                            </div>

                        </div>

                    </div>

                </div>

            `);



        $('#qcTourSkip').on('click', function() {
            qcTourClose(true);
        });

        $('#qcTourNext').on('click', function() {
            qcTourNext();
        });

        $('#qcTourDisable').on('change', function() {

            if (this.checked) localStorage.setItem(QC_TOUR_STORAGE_KEY, '1');

        });

    }



    function qcTourPosition() {

        const target = qcTourTarget();

        const focus = document.getElementById('qcTourFocus');

        const card = document.getElementById('qcTourCard');

        if (!target || !focus || !card) return;



        if ($(target).is(':hidden') || $(target).css('display') === 'none') {

            return;

        }



        const rect = target.getBoundingClientRect();

        const pad = 7;

        focus.style.left = Math.max(4, rect.left - pad) + 'px';

        focus.style.top = Math.max(4, rect.top - pad) + 'px';

        focus.style.width = (rect.width + pad * 2) + 'px';

        focus.style.height = (rect.height + pad * 2) + 'px';



        const cardWidth = Math.min(360, window.innerWidth - 24);

        card.style.width = cardWidth + 'px';



        let left = rect.left;

        let top = rect.bottom + 14;



        if (left + cardWidth > window.innerWidth - 12) {

            left = window.innerWidth - cardWidth - 12;

        }

        if (left < 12) left = 12;



        const cardHeight = card.offsetHeight || 170;

        if (top + cardHeight > window.innerHeight - 12) {

            top = rect.top - cardHeight - 14;

        }

        if (top < 12) top = 12;



        card.style.left = left + 'px';

        card.style.top = top + 'px';



        const arrow = card.querySelector('.qc-tour-arrow');

        if (arrow) {

            const arrowLeft = Math.max(18, Math.min(cardWidth - 28, rect.left + rect.width / 2 - left));

            arrow.style.left = arrowLeft + 'px';

        }

    }



    function qcTourRender() {

        qcTourEnsureUi();

        const step = qcTourSteps[qcTourStep];

        if (!step) return qcTourClose(false);



        $('#qcTourTitle').text(step.title);

        $('#qcTourText').text(step.text);

        $('#qcTourNext').text(qcTourStep === qcTourSteps.length - 1 ? 'Selesai' : 'Berikutnya');

        $('#qcTourOverlay').addClass('show').attr('aria-hidden', 'false');



        qcTourWaitingFilter = !!step.waitForFilter;

        if (qcTourWaitingFilter) {

            $('#qcTourNext').text('Sudah Filter');

        }



        setTimeout(qcTourPosition, 30);

    }



    function qcTourStart() {

        if (localStorage.getItem(QC_TOUR_STORAGE_KEY) === '1') return;

        qcTourActive = true;

        qcTourStep = 0;

        qcTourRender();

    }



    function qcTourNext() {

        if (!qcTourActive) return;



        if (qcTourWaitingFilter) {

            const from = $('#date_from').val();

            const to = $('#date_to').val();

            if (!from || !to) {

                $('#date_from, #date_to').addClass('qc-tour-invalid');

                setTimeout(function() {
                    $('#date_from, #date_to').removeClass('qc-tour-invalid');
                }, 700);

                return;

            }

            // Jalankan tombol Filter asli agar alurnya sama seperti penggunaan normal.

            $('#btn-filter').trigger('click');

            return;

        }



        if (qcTourStep >= qcTourSteps.length - 1) {

            qcTourClose(false);

            return;

        }



        qcTourStep++;

        qcTourRender();

    }



    function qcTourClose(saveDisabled) {

        qcTourActive = false;

        qcTourWaitingFilter = false;

        $('#qcTourOverlay').removeClass('show').attr('aria-hidden', 'true');

        if (saveDisabled || $('#qcTourDisable').is(':checked')) {

            localStorage.setItem(QC_TOUR_STORAGE_KEY, '1');

        }

    }



    function qcTourAfterFilter() {

        if (!qcTourActive) return;

        if (qcTourStep === 2) {

            qcTourWaitingFilter = false;

            qcTourStep = 3;

            qcTourRender();

        }

    }



    $(window).on('resize.qcTour scroll.qcTour', function() {

        if (qcTourActive) qcTourPosition();

    });



    $(document).on('click', '#btn-filter.qc-tour-click', function() {

        qcTourAfterFilter();

    });



    $(document).ready(function() {
        // Company options dari data inspeksi yang sudah dimuat.
        const companies = [...new Set(ALL_INSPECTIONS
            .map(row => cleanText(row.buyer))
            .filter(Boolean))].sort((a, b) => a.localeCompare(b, 'id'));
        companies.forEach(company => {
            $('#qc-company').append($('<option>', {
                value: company,
                text: company
            }));
        });


        bindRowClick();

        bindRejectHover();



        // SITI (IN) tabel dihitung server berdasarkan tanggal mutasi.
        // Reload hanya saat range berubah; filter lain tetap client-side.
        const initialParams = new URLSearchParams(window.location.search);
        $('#date_from').val(initialParams.get('date_from') || '');
        $('#date_to').val(initialParams.get('date_to') || '');
        $('#btn-filter').on('click', function() {
            const f = $('#date_from').val(),
                t = $('#date_to').val();
            if (f !== (initialParams.get('date_from') || '') || t !== (initialParams.get('date_to') ||
                    '')) {
                const next = new URLSearchParams(window.location.search);
                if (f) next.set('date_from', f);
                else next.delete('date_from');
                if (t) next.set('date_to', t);
                else next.delete('date_to');
                window.location.href = window.location.pathname + '?' + next.toString();
                return;
            }

            const $btn = $(this);

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            setTimeout(function() {

                applyFilter();

                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Filter');

                qcTourAfterFilter();

            }, 80);

        });



        $('#btn-reset').on('click', resetFilter);



        $('#btn-classify').on('click', function() {

            if (isClassified) {

                isClassified = false;

                renderRows(currentRows, false);

                $('#table-mode-label').text('Data inspection hasil filter');



                $(this).html('<i class="fas fa-layer-group"></i> Klasifikasi');

            } else {

                classifyAnyamRows();

            }

        });



        $('#btn-detail').on('click', function() {

            const userId = $('#qc-user').val();

            const from = $('#date_from').val();

            const to = $('#date_to').val();

            const base = @json(route('qc.laporans'));

            window.location.href =
                `${base}?user_id=${encodeURIComponent(userId)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;

        });



        $('#btn-export').on('click', function() {

            const inspector = $('#qc-user').val();

            const from = $('#date_from').val();

            const to = $('#date_to').val();

            window.location.href = '/?inspector=' + encodeURIComponent(inspector) +

                '&from=' + encodeURIComponent(from) +

                '&to=' + encodeURIComponent(to);

        });



        // Mulai tour setelah halaman siap, kecuali user sudah menonaktifkannya.

        setTimeout(qcTourStart, 700);

    });
</script>



<style>
    /* =========================================================

           SIMPLE / FLAT UI

           Tidak menggunakan gradient atau efek berlebihan.

           ========================================================= */

    .qc-monitor-box {
        background: #fff;
    }



    .header-controls {

        width: 100% !important;

        margin-top: 8px;

    }



    .filter-toolbar {

        width: 100%;

        display: flex;

        align-items: flex-end;

        flex-wrap: wrap;

        gap: 16px;

        padding: 20px 22px;

        background: #fff;

        border: 1px solid #cfd5dc;

        border-radius: 6px;

        box-sizing: border-box;

    }



    .filter-item {

        display: flex;

        flex-direction: column;

        gap: 6px;

        flex: 1 1 250px;

        min-width: 220px;

    }



    .filter-item label {

        margin: 0;

        font-size: 13px;

        font-weight: 700;

        color: #374151;

    }



    .filter-item .form-control {

        width: 100% !important;

        min-width: 0;

        height: 44px;

        padding: 8px 12px;

        border: 1px solid #c5ccd5;

        border-radius: 5px;

        box-shadow: none;

        font-size: 14px;

        background: #fff;

    }



    .filter-item .form-control:focus {

        border-color: #2563eb;

        box-shadow: none;

    }



    .filter-buttons {

        display: flex;

        align-items: center;

        flex-wrap: wrap;

        gap: 8px;

        flex: 0 0 auto;

        padding-bottom: 0;

    }



    .filter-buttons .btn {

        min-width: 105px;

        height: 44px;

        padding: 8px 15px;

        border-radius: 5px;

        box-shadow: none !important;

        font-size: 13px;

        font-weight: 600;

    }



    .btn-warning {

        color: #111827 !important;

        background: #f3c969 !important;

        border-color: #d8ad4b !important;

    }



    .qc-table-header {

        position: sticky;

        top: 0;

        z-index: 100;

        min-height: 56px;

        padding: 10px 14px;

        background: #2563eb !important;

        color: #fff;

        border: 1px solid #1d4ed8;

    }



    .qc-table-title {

        font-size: 14px;

        font-weight: 700;

    }



    .qc-table-subtitle {

        margin-top: 2px;

        font-size: 11px;

        opacity: .9;

    }



    .panel-scroll {

        max-height: calc(100vh - 280px);

        overflow: auto;

        position: relative;

        border-left: 1px solid #d9dee5;

        border-right: 1px solid #d9dee5;

        border-bottom: 1px solid #d9dee5;

    }



    #inspection-table {

        width: 100%;

        min-width: 1200px;

        margin: 0;

        border-collapse: separate;

        border-spacing: 0;

        table-layout: fixed;

    }



    #inspection-table th,

    #inspection-table td {

        border: 1px solid #d9dee5 !important;

        border-left: 0 !important;

        border-top: 0 !important;

        padding: 8px 9px;

        vertical-align: middle;

        color: #1f2937;

        font-size: 12px;

    }



    #inspection-table thead th {

        position: sticky;

        top: 0;

        z-index: 90;

        background: #f3f4f6 !important;

        color: #111827;

        font-weight: 700;

        text-align: center;

        white-space: nowrap;

        border-top: 1px solid #d9dee5 !important;

    }



    #inspection-table tbody tr {
        background: #fff;
        cursor: pointer;
    }

    #inspection-table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #inspection-table tbody tr:hover {
        background: #eef5ff;
    }

    #inspection-table tbody tr.active {
        background: #dbeafe !important;
    }

    #inspection-table tbody tr.classified-row {
        cursor: default;
    }



    .col-no {
        width: 45px;
    }

    .col-date {
        width: 125px;
    }

    .col-po {
        width: 105px;
    }

    .col-item {
        width: 300px;
    }

    .col-buyer {
        width: 180px;
    }

    .col-spk {
        width: 230px;
    }

    .col-person {
        width: 150px;
    }

    .col-siti {
        width: 90px;
        text-align: center;
        background: #eaf7ee;
    }

    .siti-cell {
        text-align: center;
        font-weight: 700;
        color: #176534;
    }

    .col-pass {
        width: 65px;
        text-align: center;
    }

    .col-reject {
        width: 70px;
        text-align: center;
    }



    .category-merged-cell {

        text-align: center !important;

        font-weight: 700;

        background: #f8fafc !important;

        color: #374151 !important;

        letter-spacing: .2px;

        vertical-align: middle !important;

    }



    .item-cell {

        white-space: normal !important;

        line-height: 1.35;

        word-break: break-word;

    }



    .empty-row {

        padding: 45px !important;

        text-align: center;

        color: #6b7280 !important;

        background: #fff !important;

    }



    .panel-scroll small {

        font-size: 10px;

    }



    .qc-classified-info {
        border-color: #d8ad4b;
    }



    /* =========================================================

           REJECT HOVER DETAIL

           ========================================================= */

    .reject-cell {

        position: relative;

        font-weight: 700;

    }



    .reject-hover-trigger {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 28px;

        min-height: 24px;

        padding: 2px 7px;

        border-radius: 4px;

        cursor: help;

        color: #b91c1c;

        text-decoration: underline dotted;

        text-underline-offset: 3px;

    }



    .reject-hover-trigger:hover,

    .reject-hover-trigger:focus {

        background: #fee2e2;

        outline: none;

    }



    .reject-hover-data {

        display: none !important;

    }



    #rejectHoverPopup {

        position: fixed;

        display: none;

        z-index: 999999;

        background: #fff;

        color: #111827;

        border: 1px solid #bfc7d1;

        border-radius: 6px;

        box-shadow: 0 8px 24px rgba(0, 0, 0, .18);

        overflow: hidden;

        pointer-events: auto;

        font-size: 11px;

    }



    #rejectHoverPopup .reject-hover-title {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        padding: 9px 11px;

        background: #f3f4f6;

        border-bottom: 1px solid #cfd5dc;

        font-size: 11px;

        font-weight: 700;

    }



    #rejectHoverPopup .reject-hover-title strong {

        color: #b91c1c;

    }



    #rejectHoverPopup .reject-hover-scroll {

        max-height: 300px;

        overflow-y: auto;

    }



    #rejectHoverPopup table {

        width: 100%;

        border-collapse: collapse;

        table-layout: fixed;

        margin: 0;

    }



    #rejectHoverPopup th,

    #rejectHoverPopup td {

        padding: 7px 8px;

        border-right: 1px solid #d9dee5;

        border-bottom: 1px solid #d9dee5;

        white-space: nowrap;

    }



    #rejectHoverPopup th {

        background: #2563eb;

        color: #fff;

        font-size: 10px;

        font-weight: 700;

        text-align: center;

        position: sticky;

        top: 0;

    }



    #rejectHoverPopup td {

        background: #fff;

        color: #374151;

    }



    #rejectHoverPopup tbody tr:hover td {

        background: #eff6ff;

    }



    #rejectHoverPopup th:nth-child(1),

    #rejectHoverPopup td:nth-child(1) {

        width: 38px;

        text-align: center;

    }



    #rejectHoverPopup th:nth-child(2),

    #rejectHoverPopup td:nth-child(2) {

        width: 130px;

    }



    #rejectHoverPopup th:nth-child(3),

    #rejectHoverPopup td:nth-child(3) {

        width: 55px;

        text-align: right;

    }



    #rejectHoverPopup th:nth-child(4),

    #rejectHoverPopup td:nth-child(4) {

        width: 150px;

    }





    /* =========================================================

           QC TOUR GUIDE

           ========================================================= */

    #qcTourOverlay {

        display: none;

        position: fixed;

        inset: 0;

        z-index: 1000000;

        pointer-events: none;

    }



    #qcTourOverlay.show {
        display: block;
    }



    #qcTourBackdrop {

        position: fixed;

        inset: 0;

        background: rgba(15, 23, 42, .62);

        pointer-events: auto;

    }



    #qcTourFocus {

        position: fixed;

        border: 3px solid #60a5fa;

        border-radius: 7px;

        box-shadow: 0 0 0 9999px rgba(15, 23, 42, .62), 0 0 0 5px rgba(96, 165, 250, .18);

        pointer-events: none;

        transition: all .18s ease;

        z-index: 1000002;

    }



    #qcTourCard {

        position: fixed;

        z-index: 1000003;

        background: #fff;

        color: #111827;

        border: 1px solid #cbd5e1;

        border-radius: 8px;

        box-shadow: 0 14px 40px rgba(0, 0, 0, .25);

        padding: 16px;

        pointer-events: auto;

        transition: left .18s ease, top .18s ease;

    }



    .qc-tour-arrow {

        position: absolute;

        top: -7px;

        width: 14px;

        height: 14px;

        background: #fff;

        border-left: 1px solid #cbd5e1;

        border-top: 1px solid #cbd5e1;

        transform: rotate(45deg);

    }



    .qc-tour-title {

        position: relative;

        font-size: 15px;

        font-weight: 700;

        margin-bottom: 7px;

    }



    .qc-tour-text {

        position: relative;

        color: #4b5563;

        font-size: 13px;

        line-height: 1.55;

    }



    .qc-tour-footer {

        position: relative;

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 10px;

        margin-top: 15px;

        padding-top: 12px;

        border-top: 1px solid #e5e7eb;

    }



    .qc-tour-disable {

        display: flex;

        align-items: center;

        gap: 6px;

        margin: 0;

        color: #6b7280;

        font-size: 11px;

        font-weight: 500;

        cursor: pointer;

    }



    .qc-tour-actions {

        display: flex;

        gap: 6px;

    }



    .qc-tour-btn {

        height: 32px;

        padding: 5px 11px;

        border-radius: 5px;

        border: 1px solid #cbd5e1;

        background: #fff;

        font-size: 12px;

        font-weight: 600;

        cursor: pointer;

    }



    .qc-tour-next {

        color: #fff;

        background: #2563eb;

        border-color: #2563eb;

    }



    .qc-tour-btn:hover {
        opacity: .9;
    }



    .qc-tour-invalid {

        border-color: #ef4444 !important;

        box-shadow: 0 0 0 3px rgba(239, 68, 68, .15) !important;

    }



    @media (max-width: 600px) {

        #qcTourCard {
            max-width: calc(100vw - 24px);
        }

        .qc-tour-footer {
            align-items: flex-end;
            flex-direction: column;
        }

    }



    @media (max-width: 900px) {

        .filter-item .form-control {
            width: 160px;
        }

    }
</style>

@endsection
