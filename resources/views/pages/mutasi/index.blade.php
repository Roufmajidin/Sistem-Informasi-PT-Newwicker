{{-- halaman mutasi  --}}
@extends('master.master')

@section('title', 'Mutasi Barang')

@section('content')

    {{-- Preloader: visible before JS finishes, removed when DOM is ready --}}
    <div id="mutasiPagePreloader" role="status" aria-live="polite">
        <div class="mutasi-preloader-card">
            <div class="spinner-border text-primary" aria-hidden="true"></div>
            <strong>Memuat Monitoring SPK</strong>
            <small>Menyiapkan tampilan...</small>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        Monitoring SPK
                    </h4>
                    <small class="text-muted">
                        Cari berdasarkan PO, Buyer, Supplier, Item, atau No SPK
                    </small>
                </div>

                <div style="min-width:380px; max-width:500px; width:100%;">

                    <div class="input-group">

                        <button type="button" id="mobileSearchButton" class="input-group-text bg-white mutasi-search-button"
                            aria-label="Fokus pencarian SPK" title="Cari SPK">
                            <i class="fas fa-search text-secondary" aria-hidden="true"></i>
                        </button>

                        <input type="text" id="searchSpk" class="form-control border-start-0"
                            placeholder="Cari PO, Buyer, Supplier, Item...">
                        <button type="button" id="mobileSearchClear" aria-label="Hapus pencarian" title="Hapus pencarian"
                            style="display:none" class="btn btn-light border mobile-search-clear">&times;</button>

                    </div>

                </div>

            </div>

        </div>

        <div class="card-body">

            {{-- Search --}}


            <div class="table-responsive spk-table-wrap">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>No SPK</th>
                            <th>PO</th>
                            <th>Buyer</th>
                            <th>Jenis</th>
                            <th>Sub</th>
                        </tr>
                    </thead>

                    <tbody id="tbodySpk">

                        @forelse($a as $index => $spk)
                            @php

                                $searchItem = '';

                                foreach ($spk->data['items'] ?? [] as $item) {
                                    $searchItem .= ' ' . ($item['nama'] ?? '') . ' ' . ($item['kode'] ?? '');
                                }

                            @endphp

                            <tr class="pilih-spk" data-id="{{ $spk->id }}"
                                data-items-b64="{{ base64_encode(json_encode($spk->data['items'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
                                style="cursor:pointer"
                                data-search="{{ strtolower(
                                    ($spk->po->order_no ?? '') .
                                        ' ' .
                                        ($spk->po->company_name ?? '') .
                                        ' ' .
                                        ($spk->data['sup'] ?? '') .
                                        ' ' .
                                        ($spk->data['no_spk'] ?? '') .
                                        ' ' .
                                        ($spk->data['kategori'] ?? '') .
                                        ' ' .
                                        $searchItem,
                                ) }}">

                                <td>{{ $index + 1 }}</td>

                                <td>
                                    {{ $spk->data['no_spk'] ?? '-' }}
                                </td>

                                <td>{{ $spk->po->order_no ?? '-' }}</td>

                                <td>{{ $spk->po->company_name ?? '-' }}</td>

                                <td>{{ $spk->data['kategori'] ?? '-' }}</td>

                                <td>{{ $spk->data['sup'] ?? '-' }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center">
                                    Tidak ada data SPK
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
        {{-- =========================================================
             HOVER PREVIEW ITEM SPK
             ========================================================= --}}
        <div id="spkHoverPreview" aria-hidden="true">
            <div class="spk-hover-title">
                <span>LIST ITEM ON SPK</span>
                <span id="spkHoverCount"></span>
            </div>

            <div class="spk-hover-body">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>In</th>
                        </tr>
                    </thead>
                    <tbody id="spkHoverItems">
                        <tr>
                            <td colspan="4" class="spk-hover-loading">
                                Arahkan mouse ke SPK
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalSpk" tabindex="-1">
            <div class="modal-dialog modal-custom">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="judulSpk">Detail SPK</h5>

                        <button type="button" class="close mutasi-close-button" id="mutasiMobileClose"
                            data-dismiss="modal" aria-label="Tutup detail SPK"><span aria-hidden="true">&times;</span></button>
                    </div>

                    <div class="modal-body">

                        <div class="item-picker-wrap">
                            <div class="item-picker-label">
                                <div class="item-picker-label-left">

                                    <div>
                                        {{-- <div class="item-picker-title">Pilih Item</div> --}}
                                        <div class="item-picker-subtitle">
                                            Pilih item SPK untuk melihat detail & timeline
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="item-select-shell item-select-shell-photo">
                                <span class="item-select-leading-icon item-select-photo-preview"
                                    id="itemSelectPhotoPreview">
                                    <img id="itemSelectPhoto" src="{{ asset('storage/rouf.jpeg') }}" alt="Foto item"
                                        style="display:block;">
                                    <i class="fa fa-cube" id="itemSelectPhotoPlaceholder" style="display:none;"></i>
                                </span>

                                <select id="itemSelect" class="form-select item-select">
                                    <option value="">Pilih Item...</option>
                                </select>

                                <span class="item-select-arrow">
                                    <i class="fa fa-chevron-down"></i>
                                </span>
                            </div>

                            <div id="itemSelectPhotoName" class="item-select-photo-name" style="display:none;">
                                <span class="item-select-photo-name-title"></span>
                                <span class="item-select-photo-name-meta"></span>
                            </div>
                        </div>

                        {{-- Mobile item tabs; original #itemSelect remains the functional source --}}
                        <div id="mobileItemTabs" class="mobile-item-tabs" aria-label="Pilih item SPK">
                            <div class="mobile-item-tabs-track" id="mobileItemTabsTrack" role="tablist"></div>
                            <button type="button" id="mobileItemNext" class="mobile-item-next"
                                aria-label="Geser ke item berikutnya">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>

                        {{-- Detail item --}}
                        <div id="itemInfo"></div>

                        <hr>

                        {{-- Timeline --}}
                        <div id="timelineTable"></div>

                    </div>
                </div>
            </div>
            {{-- scripts --}}
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                let items = [];
                let currentSpkId = null;
                let supplierName = '';
                let currentSupId = null;
                let kategoriSpk = '';
                const spkDetailCache = Object.create(null);
                $(document).on('click', '.pilih-spk', function() {

                    // Reset data/modal sebelumnya terlebih dahulu
                    resetModal();

                    // Tandai HANYA baris SPK yang baru diklik
                    $('#tbodySpk tr.pilih-spk').removeClass('active');
                    $(this).addClass('active');

                    currentSpkId = $(this).data('id');

                    function applySpkDetail(res) {
                        supplierName = res.supplier;
                        currentSupId = res.sup_id;
                        kategoriSpk = res.kategori;

                        $('#judulSpk').text(res.no_spk);
                        items = Array.isArray(res.items) ? res.items : [];

                        let html = '<option value="">Pilih Item...</option>';

                        $.each(items, function(i, item) {
                            html += `
                                <option value="${item.detail_po_id}" data-index="${i}" data-photo="${escapeHtml(item.photo || '')}">
                                    ${escapeHtml(item.kode || '-')} - ${escapeHtml(item.nama || '-')} (${escapeHtml(item.qty ?? 0)} ${escapeHtml(item.satuan || '')})
                                </option>
                            `;
                        });

                        $('#itemSelect').html(html);
                        if (typeof window.renderMobileItemTabs === 'function') window.renderMobileItemTabs();
                        $('#modalSpk').modal('show');
                        if (window.matchMedia('(max-width: 767.98px)').matches) {
                            var $firstItem = $('#itemSelect option').filter(function() { return this.value !== ''; }).first();
                            if ($firstItem.length) {
                                $('#itemSelect').val($firstItem.val());
                                loadSelectedMutasiItem();
                                if (typeof window.syncMutasiMobileActiveTab === 'function') window.syncMutasiMobileActiveTab();
                            } else {
                                $('#itemInfo').html('<div class="alert alert-info">Item belum tersedia untuk SPK ini.</div>');
                            }
                        }
                    }

                    if (spkDetailCache[currentSpkId]) {
                        applySpkDetail(spkDetailCache[currentSpkId]);
                        return;
                    }

                    $.ajax({
                        url: "/mutasi/" + currentSpkId,
                        type: "GET",
                        cache: true,
                        success: function(res) {
                            spkDetailCache[currentSpkId] = res;
                            applySpkDetail(res);
                        },
                        error: function(xhr) {
                            console.error('Gagal mengambil detail SPK:', xhr.status);
                        }
                    });

                });

                /*
                |--------------------------------------------------------------------------
                | RENDER DETAIL ITEM + KOMPONEN
                |--------------------------------------------------------------------------
                */

                function escapeHtml(value) {

                    if (value === null || value === undefined) {
                        return '';
                    }

                    return $('<div>').text(value).html();

                }


                function formatNumber(value) {

                    const number = Number(value);

                    if (Number.isNaN(number)) {
                        return value ?? '-';
                    }

                    return number.toLocaleString('id-ID');
                }


                function getQtyInFromTimeline(timeline) {

                    let qtyIn = 0;

                    $.each(timeline || [], function(i, row) {

                        const type = String(row.type || '').toLowerCase();

                        /*
                        | Qty In item-level tetap dipertahankan untuk kebutuhan
                        | existing. Hanya IN / SERVICE MASUK yang menambah Qty In.
                        */

                        if (type === 'in' || type === 'service_masuk') qtyIn += Number(row.qty || 0);
                        if (type === 'service_keluar') qtyIn -= Number(row.qty || 0);

                    });

                    return qtyIn;
                }


                function normalizeComponentText(value) {

                    return String(value || '')
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                }


                function getComponentQtyInFromTimeline(
                    timeline,
                    componentName,
                    componentIndex = 0
                ) {

                    const target = normalizeComponentText(componentName);

                    if (!target) {
                        return 0;
                    }

                    let qtyIn = 0;

                    $.each(timeline || [], function(i, row) {

                        const type = String(row.type || '').toLowerCase();

                        /*
                        |--------------------------------------------------------------------------
                        | HANYA MUTASI MASUK
                        |--------------------------------------------------------------------------
                        */
                        if (!['in', 'service_masuk', 'service_keluar'].includes(type)) {
                            return;
                        }

                        const qty = Number(row.qty || 0);

                        if (!qty) {
                            return;
                        }

                        const remark = normalizeComponentText(row.remark);

                        /*
                        |--------------------------------------------------------------------------
                        | REMARK KOSONG
                        |--------------------------------------------------------------------------
                        |
                        | Tetap pertahankan behaviour lama:
                        | kalau remark kosong, qty masuk ke komponen pertama.
                        |
                        |--------------------------------------------------------------------------
                        */

                        if (!remark) {

                            if (componentIndex === 0) {
                                qtyIn += type === 'service_keluar' ? -qty : qty;
                            }

                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | MATCHING KOMPONEN
                        |--------------------------------------------------------------------------
                        |
                        | HARUS EXACT MATCH.
                        |
                        | Contoh:
                        |
                        | HANGER U -> HANGER U  = MATCH
                        | HANGER   -> HANGER    = MATCH
                        |
                        | HANGER U -> HANGER    = TIDAK MATCH
                        | HANGER   -> HANGER U  = TIDAK MATCH
                        |
                        | Ini mencegah komponen dengan nama mirip
                        | saling mengambil Qty.
                        |
                        |--------------------------------------------------------------------------
                        */

                        if (remark === target) {
                            qtyIn += type === 'service_keluar' ? -qty : qty;
                        }

                    });

                    return qtyIn;
                }

                function getComponentRows(item, timeline = []) {

                    const customColumns =
                        Array.isArray(item.custom_columns) ?
                        item.custom_columns : [];

                    const rows = [];

                    $.each(customColumns, function(index, component) {

                        if (!component || typeof component !== 'object') {
                            return;
                        }

                        /*
                        | Cari nama komponen.
                        | Contoh:
                        | triplek: "TOP TEBAL 6MM"
                        | triplek: "BOTTOM TRIPLEK 12MM"
                        */

                        let componentName = '';

                        /*
                        |--------------------------------------------------------------------------
                        | CARI NAMA KOMPONEN
                        |--------------------------------------------------------------------------
                        | Struktur SPK bisa berbeda-beda.
                        | Contoh:
                        |
                        | triplek : "TOP TEBAL 6MM"
                        | triplek : "BOTTOM TRIPLEK 12MM"
                        | material: "plywood,"
                        |
                        | Jadi yang kita cari adalah field yang berisi nama pekerjaan/
                        | komponen, BUKAN field material.
                        |--------------------------------------------------------------------------
                        */

                        const preferredNameKeys = [
                            'nama',
                            'name',
                            'nama_material',
                            'nama_bahan',
                            'bahan',
                            'triplek',
                            'finishing',
                            'komponen',
                            'component',
                            'description'
                        ];

                        for (const key of preferredNameKeys) {

                            const value = component[key];

                            if (
                                typeof value === 'string' &&
                                value.trim() &&
                                !['-', 'null', 'undefined', 'n/a', 'na'].includes(
                                    value.trim().toLowerCase()
                                )
                            ) {
                                componentName = value.trim();
                                break;
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FALLBACK
                        |--------------------------------------------------------------------------
                        | Kalau struktur SPK berbeda, cari string yang masuk akal.
                        |--------------------------------------------------------------------------
                        */

                        if (!componentName) {

                            $.each(component, function(key, value) {

                                const keyLower = String(key).toLowerCase();

                                if (
                                    typeof value !== 'string' ||
                                    !value.trim()
                                ) {
                                    return;
                                }

                                const cleanValue = value.trim().toLowerCase();

                                /*
                                | Jangan mengambil field teknis / angka / material.
                                */

                                if (
                                    [
                                        'harga',
                                        'material',
                                        'pcs',
                                        'set',
                                        'total',
                                        'p',
                                        'l',
                                        't',
                                        'qty',
                                        'kode',
                                        'id'
                                    ].includes(keyLower)
                                ) {
                                    return;
                                }

                                /*
                                | Jangan gunakan placeholder.
                                */

                                if (
                                    ['-', 'null', 'undefined', 'n/a', 'na'].includes(cleanValue)
                                ) {
                                    return;
                                }

                                componentName = value.trim();

                                return false;
                            });
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | LAST FALLBACK
                        |--------------------------------------------------------------------------
                        */

                        // Abaikan baris komponen yang tidak memiliki nama valid.
                        // Jika semua baris komponen kosong, fallback item utama
                        // di bawah akan digunakan.
                        if (!componentName) {
                            return;
                        }

                        const qtySpk =
                            component.pcs !== undefined &&
                            component.pcs !== null &&
                            component.pcs !== '' &&
                            !Number.isNaN(Number(component.pcs)) ?
                            Number(component.pcs) :
                            Number(item.qty || 0);

                        /*
                        | Qty In SEKARANG mengikuti REMARK timeline.
                        |
                        | Jadi:
                        | TOP TEBAL 6MM  -> hanya qty dengan remark TOP TEBAL 6MM
                        | BOTTOM ...     -> hanya qty dengan remark BOTTOM ...
                        */

                        const qtyIn =
                            getComponentQtyInFromTimeline(
                                timeline,
                                componentName,
                                index
                            );

                        rows.push({
                            name: componentName,
                            qtySpk: qtySpk,
                            qtyIn: qtyIn,
                            specification: component,
                        });

                    });

                    /*
                    | Bila custom_columns kosong, gunakan item utama.
                    | Untuk fallback ini Qty In tetap menggunakan seluruh
                    | Qty In item-level agar fungsi lama tidak berubah.
                    */

                    if (!rows.length) {

                        rows.push({
                            name: item.nama || '-',
                            qtySpk: Number(item.qty || 0),
                            qtyIn: getQtyInFromTimeline(timeline),
                            specification: null,
                        });

                    }

                    return rows;
                }


                // Ringkasan pengiriman rangka dan service berdasarkan timeline item.
                function renderServiceSummary(timeline = []) {
                    const total = type => (timeline || [])
                        .filter(r => String(r.type || '').toLowerCase() === type)
                        .reduce((sum, r) => sum + (Number(r.qty) || 0), 0);
                    const rangka = total('kirim_rangka');
                    const keluar = total('service_keluar');
                    const masuk = total('service_masuk');
                    const belumKembali = Math.max(0, keluar - masuk);
                    return `<div class="rnd-service-summary">
                        <div class="rnd-service-heading"><i class="fas fa-tools"></i> Monitoring Rangka & Service</div>
                        <div class="rnd-service-grid">
                            <div><small>Kirim Rangka</small><strong>${formatNumber(rangka)}</strong></div>
                            <div><small>Service Keluar</small><strong>${formatNumber(keluar)}</strong></div>
                            <div><small>Service Masuk</small><strong>${formatNumber(masuk)}</strong></div>
                            <div><small>Belum Kembali</small><strong class="${belumKembali > 0 ? 'text-danger' : 'text-success'}">${formatNumber(belumKembali)}</strong></div>
                        </div>
                    </div>`;
                }

                function renderItemInfo(item, detailPoId, timeline = []) {

                    const components =
                        getComponentRows(item, timeline);

                    let componentRows = '';

                    $.each(components, function(index, component) {

                        const qtySpk =
                            Number(component.qtySpk || 0);

                        const qtyIn =
                            Number(component.qtyIn || 0);

                        const balance =
                            qtySpk - qtyIn;

                        const balanceClass =
                            balance > 0 ?
                            'text-warning' :
                            balance < 0 ?
                            'text-danger' :
                            'text-success';

                        componentRows += `
                            <tr>
                                <td>
                                    <div class="component-name">
                                        ${escapeHtml(component.name)}
                                    </div>
                                </td>

                                <td class="text-end fw-semibold">
                                    ${formatNumber(qtySpk)}
                                </td>

                                <td class="text-end">
                                    ${formatNumber(qtyIn)}
                                </td>

                                <td class="text-end fw-semibold ${balanceClass}">
                                    ${formatNumber(balance)}
                                </td>
                            </tr>
                        `;

                    });


                    $('#itemInfo').html(`

                        <div class="item-detail-grid">

                            {{-- LEFT: DETAIL ITEM --}}
                            <div class="item-detail-card">

                                <div class="item-detail-card-header">

                                    <div>

                                        <div class="item-detail-eyebrow">
                                            Detail Item
                                        </div>

                                        <div class="item-detail-title">
                                            ${escapeHtml(item.nama || '-')}
                                        </div>

                                    </div>

                                    <span class="item-code-badge">
                                        ${escapeHtml(item.kode || '-')}
                                    </span>

                                </div>


                                <div class="item-detail-body">

                                    <div class="item-detail-row">
                                        <span>Detail PO ID</span>
                                        <strong>${escapeHtml(detailPoId)}</strong>
                                    </div>

                                    <div class="item-detail-row">
                                        <span>Kode</span>
                                        <strong>${escapeHtml(item.kode || '-')}</strong>
                                    </div>

                                    <div class="item-detail-row">
                                        <span>Qty SPK</span>
                                        <strong>
                                            ${formatNumber(item.qty || 0)}
                                            ${escapeHtml(item.satuan || '')}
                                        </strong>
                                    </div>

                                    <div class="item-detail-row">
                                        <span>Supplier</span>
                                        <strong>${escapeHtml(supplierName || '-')}</strong>
                                    </div>

                                    <div class="item-detail-row">
                                        <span>Kategori</span>
                                        <strong>${escapeHtml(kategoriSpk || '-')}</strong>
                                    </div>

                                </div>


                                <input type="hidden"
                                    class="sup_id"
                                    value="${escapeHtml(currentSupId)}">

                                <input type="hidden"
                                    class="kategori"
                                    value="${escapeHtml(kategoriSpk)}">

                            </div>


                            {{-- RIGHT: COMPONENT DETAIL --}}
                            <div class="item-detail-card component-detail-card">

                                <div class="item-detail-card-header component-header">

                                    <div>

                                        <div class="item-detail-eyebrow">
                                            Rincian Komponen
                                        </div>

                                        <div class="item-detail-title">
                                            Komponen Item
                                        </div>

                                    </div>

                                    <span class="component-count">
                                        ${
                                            components.length === 1 && !components[0].specification
                                                ? 'Item utama'
                                                : components.length + ' komponen'
                                        }
                                    </span>

                                </div>


                                <div class="component-table-wrap">

                                    <table class="component-table">

                                        <thead>
                                            <tr>
                                                <th>Items</th>
                                                <th class="text-end">Qty SPK</th>
                                                <th class="text-end">Qty In</th>
                                                <th class="text-end">Balance</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            ${componentRows}
                                        </tbody>

                                    </table>

                                </div>


                                ${renderServiceSummary(timeline)}

                                <div class="component-note">
                                    <i class="fas fa-circle-info"></i>
                                    Masuk − Service Keluar + Service Masuk.
                                  
                                </div>

                            </div>

                        </div>

                    `);

                }


                function getItemPhotoUrl(photo) {
                    if (!photo) return '';
                    photo = String(photo).trim();
                    if (!photo) return '';
                    if (photo.startsWith('http://') || photo.startsWith('https://') || photo.startsWith('data:image/'))
                return photo;
                    if (photo.startsWith('//')) return window.location.protocol + photo;
                    if (photo.startsWith('/')) return photo;
                    if (photo.startsWith('storage/')) return '/' + photo;
                    return '/storage/' + photo.replace(/^\/+/, '');
                }

                function updateItemSelectPhoto(item) {
                    const $img = $('#itemSelectPhoto');
                    const $placeholder = $('#itemSelectPhotoPlaceholder');
                    const $box = $('#itemSelectPhotoName');
                    if (!item) {
                        $img.attr('src', '').hide();
                        $placeholder.show();
                        $box.hide();
                        return;
                    }
                    const photo = getItemPhotoUrl(item.photo);
                    if (photo) {
                        $img.attr('src', photo).show();
                        $placeholder.hide();
                        $img.off('error.itemPhoto').on('error.itemPhoto', function() {
                            $(this)
                                .attr('src', "{{ asset('storage/rouf.jpeg') }}")
                                .off('error.itemPhoto')
                                .show();
                            $placeholder.hide();
                        });
                    } else {
                        $img
                            .attr('src', "{{ asset('storage/rouf.jpeg') }}")
                            .show();
                        $placeholder.hide();
                    }
                    $box.find('.item-select-photo-name-title').text(item.nama || item.name || '-');
                    $box.find('.item-select-photo-name-meta').text(
                        [item.kode || '', item.qty !== undefined && item.qty !== null ? (formatNumber(item.qty) + (item.satuan ?
                            ' ' + item.satuan : '')) : '']
                        .filter(Boolean).join(' • ')
                    );
                    $box.show();
                }

                let mutasiItemRequestSequence = 0;

                function showMutasiItemLoading() {
                    const skeleton = `
                        <div class="mutasi-loading-grid" aria-live="polite" aria-label="Memuat detail item">
                            <div class="mutasi-loading-panel">
                                <div class="mutasi-skeleton short"></div>
                                <div class="mutasi-skeleton medium"></div>
                                <div class="mutasi-skeleton"></div>
                                <div class="mutasi-skeleton"></div>
                                <div class="mutasi-skeleton"></div>
                            </div>
                            <div class="mutasi-loading-panel">
                                <div class="mutasi-skeleton short"></div>
                                <div class="mutasi-skeleton medium"></div>
                                <div class="mutasi-skeleton"></div>
                                <div class="mutasi-skeleton"></div>
                                <div class="mutasi-skeleton"></div>
                            </div>
                        </div>`;
                    $('#itemInfo').html(skeleton);
                    $('#timelineTable').html(
                        '<div class="mutasi-loading-timeline"><span class="spinner-border spinner-border-sm" role="status"></span> Memuat timeline mutasi...</div>'
                    );
                }
                function loadSelectedMutasiItem() {

                    const requestSequence = ++mutasiItemRequestSequence;
                    const $itemSelect = $('#itemSelect');
                    let detailPoId = $itemSelect.val();
                    if (!detailPoId) {
                        $('#itemInfo, #timelineTable').empty();
                        return;
                    }

                    let index = $itemSelect.find('option:selected').data('index');

                    let item = items[index];
                    if (!item) {
                        $('#itemInfo').html('<div class="alert alert-warning">Data item tidak ditemukan. Silakan pilih ulang.</div>');
                        return;
                    }

                    // Tambahan foto saja; logic existing di bawah tetap sama.
                    updateItemSelectPhoto(item);

                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL ITEM
                    |--------------------------------------------------------------------------
                    | Detail komponen (TOP/BOTTOM/dll) akan dirender setelah timeline
                    | selesai diambil supaya Qty In bisa ikut ditampilkan.
                    */

                    showMutasiItemLoading();

                    // AJAX kedua
                    $.ajax({
                        url: '/mutasi/timeline/detail',
                        type: 'GET',
                        cache: false,
                        data: {
                            spk_id: currentSpkId,
                            detail_po_id: detailPoId
                        },
                        success: function(res) {
                            if (requestSequence !== mutasiItemRequestSequence) return;

                            let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="160">Tanggal</th>
                            <th width="150">Type</th>
                            <th width="100">Qty</th>
                            <th width="100">Remark</th>
                            <th width="70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTimeline">
            `;

                            $.each(res.timeline, function(i, row) {

                                let datetime = row.date ? row.date.split(' ') : ['', ''];

                                let tanggal = datetime[0];

                                html += `
                    <tr data-id="${row.id}" data-detail-po-id="${escapeHtml(detailPoId)}">

                        <td>${i+1}</td>

                        <td>
                            <input type="date"
                                class="form-control form-control-sm tanggal"
                                value="${tanggal}">
                        </td>

                        <td>
                            <select class="form-select form-select-sm type">

                                <option value="in" ${row.type=='in'?'selected':''}>Masuk</option>


                                <option value="kirim_rangka" ${row.type=='kirim_rangka'?'selected':''}>Kirim Rangka</option>


                                <option value="service_masuk" ${row.type=='service_masuk'?'selected':''}>Service Masuk</option>
                                <option value="service_keluar" ${row.type=='service_keluar'?'selected':''}>Service Keluar</option>


                            </select>
                        </td>



                        <td>
                            <input type="text"
                                class="form-control form-control-sm qty"
                                value="${row.qty}">
                        </td>
                            <td>
                            <input type="text"
                                class="form-control form-control-sm remark"
                                value="${escapeHtml(!row.remark || ['null', 'undefined'].includes(String(row.remark).toLowerCase()) ? '' : row.remark)}">
                        </td>

                        <td class="text-center">

                            <button
                                class="btn btn-danger btn-sm hapus-row">
                                <i class="fas fa-trash"></i>
                            </button>

                        </td>

                    </tr>
                `;

                            });

                            /*
                            | Update rincian komponen setelah timeline tersedia.
                            */
                            /*
                            | Rincian TOP / BOTTOM dihitung berdasarkan
                            | remark masing-masing baris timeline.
                            */
                            renderItemInfo(
                                item,
                                detailPoId,
                                res.timeline || []
                            );

                            html += `
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">

                    <button class="btn btn-success btn-sm" id="btnTambah">

                        <i class="fas fa-plus"></i>
                        Tambah Baris

                    </button>

                    <button class="btn btn-primary btn-sm" id="btnSave">

                        <i class="fas fa-save"></i>
                        Simpan

                    </button>

                </div>
            `;

                            $('#timelineTable').html(html);
                        },
                        error: function(xhr) {
                            if (requestSequence !== mutasiItemRequestSequence) return;
                            console.error('Gagal mengambil timeline mutasi:', xhr.responseText);
                            $('#itemInfo').html(
                                '<div class="alert alert-warning mb-0">Detail item belum dapat dimuat. Silakan pilih item kembali.</div>'
                                );
                            $('#timelineTable').html(
                                '<div class="alert alert-warning mb-0">Gagal memuat timeline. Periksa koneksi lalu coba lagi.</div>'
                                );
                        }
                    });

                }
                $('#itemSelect').on('change', loadSelectedMutasiItem);
                window.loadSelectedMutasiItem = loadSelectedMutasiItem;
                // add rw
                $(document).on('click', '#btnTambah', function() {

                    const detailPoId = $('#itemSelect').val();

                    // Pastikan item sudah dipilih
                    if (!detailPoId) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Pilih item terlebih dahulu',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        return;
                    }

                    // Ambil item yang sedang dipilih
                    const index = $('#itemSelect').find(':selected').data('index');
                    const item = items[index];

                    if (!item) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Data item tidak ditemukan',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                        return;
                    }

                    const itemName = item.nama || item.name || '-';

                    let no = $('#tbodyTimeline tr').length + 1;

                    $('#tbodyTimeline').append(`
        <tr
            data-id=""
            data-detail-po-id="${escapeHtml(detailPoId)}"
        >

            <td>${no}</td>

            <td>
                <input type="date" class="form-control form-control-sm tanggal">
            </td>

            <td>
                <select class="form-select form-select-sm type">

                    <option value="in">Masuk</option>
                    <option value="kirim_rangka">Kirim Rangka</option>
                    <option value="service_keluar">Service Keluar</option>
                    <option value="service_masuk">Service Masuk</option>

                </select>
            </td>

            <td>
                <input type="number" class="form-control form-control-sm qty">
            </td>

            <td>
                <input type="text" class="form-control form-control-sm remark">
            </td>

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm hapus-row"
                >
                    <i class="fas fa-trash"></i>
                </button>

            </td>

        </tr>
    `);

                    refreshMutasiSummary();
                    // Toast ketika berhasil menambah baris
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        iconColor: 'white',
                        customClass: {
                            popup: 'colored-toast'
                        },
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });

                    Toast.fire({
                        icon: 'success',
                        title: `Menambah baris untuk Detail PO ID ${detailPoId}`,
                        text: itemName
                    });

                });

                // Alur keyboard: tanggal -> dropdown Type -> Qty -> Remark.
                // showPicker tersedia pada Chrome modern; fallback tetap fokus select.
                $(document).on('change', '#tbodyTimeline .tanggal', function() {
                    const select = $(this).closest('tr').find('.type')[0];
                    if (!select) return;
                    select.focus();
                    if (typeof select.showPicker === 'function') {
                        try {
                            select.showPicker();
                        } catch (_) {
                            /* browser may restrict programmatic picker */ }
                    }
                });
                $(document).on('change', '#tbodyTimeline .type', function() {
                    $(this).closest('tr').find('.qty').trigger('focus').trigger('select');
                });
                $(document).on('keydown', '#tbodyTimeline .qty', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $(this).closest('tr').find('.remark').trigger('focus');
                    }
                });
                $(document).on('keydown', '#tbodyTimeline .remark', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const next = $(this).closest('tr').next('tr').find('.tanggal');
                        if (next.length) next.trigger('focus');
                    }
                });

                // Baris baru dihapus lokal; baris tersimpan dihapus melalui endpoint Laravel.
                $(document).on('click', '.hapus-row', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const $button = $(this);
                    const $row = $button.closest('tr');
                    const rowId = $row.attr('data-id');
                    const detailPoId = $row.attr('data-detail-po-id');
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: 'Data mutasi yang dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545',
                        reverseButtons: true
                    });
                    if (!result.isConfirmed) return;

                    if (!rowId) {
                        $row.remove();
                        $('#tbodyTimeline tr').each(function(i) {
                            $(this).children('td').first().text(i + 1);
                        });
                        refreshMutasiSummary();
                        Swal.fire({icon: 'success', title: 'Baris dibatalkan', timer: 1500, showConfirmButton: false});
                        return;
                    }

                    $button.prop('disabled', true);
                    try {
                        const deleteResponse = await $.ajax({
                            url: "{{ url('/mutasi/timeline') }}/" + encodeURIComponent(rowId),
                            type: 'DELETE',
                            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
                            data: {spk_id: currentSpkId, detail_po_id: detailPoId}
                        });
                        if (deleteResponse?.success !== true) {
                            throw {responseJSON: {message: 'Server tidak mengonfirmasi penghapusan.'}};
                        }
                        // Hapus row langsung agar UI responsif, kemudian sinkronkan dari DB.
                        $row.remove();
                        refreshMutasiSummary();
                        document.dispatchEvent(new CustomEvent('mutasi:timeline-changed', {detail: {spkId: currentSpkId}}));
                        if (String($('#itemSelect').val()) === String(detailPoId)) {
                            $('#itemSelect').trigger('change');
                        }
                        Swal.fire({icon: 'success', title: 'Mutasi berhasil dihapus', timer: 1700, showConfirmButton: false});
                    } catch (xhr) {
                        $button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menghapus',
                            text: xhr.responseJSON?.message || 'Data belum dihapus. Periksa endpoint DELETE di Laravel.'
                        });
                    }
                });

                function refreshMutasiSummary() {
                    const index = $('#itemSelect').find(':selected').data('index');
                    const item = items[index];
                    if (!item) return;
                    const detailPoId = $('#itemSelect').val();
                    const timeline = $('#tbodyTimeline tr').map(function() {
                        return {
                            type: $(this).find('.type').val(),
                            qty: $(this).find('.qty').val(),
                            remark: $(this).find('.remark').val()
                        };
                    }).get();
                    renderItemInfo(item, detailPoId, timeline);
                }
                $(document).on('input change', '#tbodyTimeline .qty, #tbodyTimeline .type, #tbodyTimeline .remark',
                    refreshMutasiSummary);

                // save data
                $(document).on('click', '#btnSave', function() {

                    let rows = [];
                    let invalidRow = null;

                    $('#tbodyTimeline tr').each(function(index) {

                        const detailPoId = $(this).attr('data-detail-po-id');

                        // Pastikan setiap row memiliki Detail PO ID
                        if (!detailPoId) {
                            invalidRow = index + 1;
                            return false;
                        }

                        rows.push({

                            id: $(this).data('id'),

                            spk_id: currentSpkId,

                            // Ambil Detail PO ID dari row masing-masing
                            detail_po_id: detailPoId,

                            sup_id: currentSupId,

                            qty: $(this).find('.qty').val(),

                            type: $(this).find('.type').val(),

                            remark: $(this).find('.remark').val(),

                            date: $(this).find('.tanggal').val()

                        });

                    });

                    // Jangan kirim data jika ada row tanpa Detail PO ID
                    if (invalidRow !== null) {

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: `Detail PO pada baris ${invalidRow} tidak ditemukan`,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        return;
                    }

                    if (!rows.length) {

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Tidak ada baris untuk disimpan',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });

                        return;
                    }

                    $.ajax({

                        url: "{{ route('mutasi.timeline.save') }}",

                        type: "POST",

                        data: {
                            _token: "{{ csrf_token() }}",
                            rows: rows
                        },

                        success: function(res) {

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                iconColor: 'white',
                                customClass: {
                                    popup: 'colored-toast'
                                },
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'ikan hiu ikan hiu, love you'
                            });

                            // Tetap di modal dan refresh item yang sedang dipilih.
                            // Event change memuat ulang timeline dari database,
                            // menampilkan skeleton loading, lalu menghitung ulang ringkasan.
                            document.dispatchEvent(new CustomEvent('mutasi:timeline-changed', {detail: {spkId: currentSpkId}}));
                            const selectedDetailPoId = $('#itemSelect').val();
                            if (selectedDetailPoId) {
                                $('#itemSelect').trigger('change');
                            }

                        },

                        error: function(xhr) {

                            console.log(xhr.responseText);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Gagal menyimpan timeline',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });

                        }

                    });

                });

                function resetModal() {

                    items = [];
                    currentSpkId = null;
                    currentSupId = null;
                    supplierName = '';

                    $('#judulSpk').text('Detail SPK');

                    $('#itemSelect').html('<option value="">Pilih Item...</option>');
                    $('#mobileItemTabsTrack').empty();

                    $('#itemInfo').empty();

                    $('#timelineTable').empty();

                    // Reset select ke option pertama
                    $('#itemSelect').prop('selectedIndex', 0);

                    // Reset foto item
                    $('#itemSelectPhoto')
                        .attr('src', "{{ asset('storage/rouf.jpeg') }}")
                        .show();

                    $('#itemSelectPhotoPlaceholder').hide();

                    $('#itemSelectPhotoName').hide();

                    // Hapus blok/highlight SPK sebelumnya
                    $('#tbodySpk tr.pilih-spk').removeClass('active');
                }
                $('#modalSpk').on('hidden.bs.modal', function() {

                    resetModal();

                });
                $('#searchSpk').on('keyup', function() {

                    let keyword = String($(this).val() ?? '').toLowerCase().trim();

                    $('#tbodySpk tr.pilih-spk').each(function() {

                        let search = String($(this).attr('data-search') ?? '').toLowerCase();

                        $(this).toggle(search.includes(keyword));

                    });

                });
            </script>

            <script>
                /*
                            |--------------------------------------------------------------------------
                            | SPK HOVER PREVIEW + IN
                            |--------------------------------------------------------------------------
                            | - Items + Qty dibaca langsung dari data SPK.
                            | - IN diambil dari endpoint timeline yang SUDAH WORK.
                            | - Menggunakan fetch native, bukan $.ajax().
                            | - Tidak menggunakan $.ajax().then().catch().
                            | - Tidak mengubah click/modal existing.
                            |--------------------------------------------------------------------------
                            */

                (function() {

                    const preview =
                        document.getElementById('spkHoverPreview');

                    const tbody =
                        document.getElementById('spkHoverItems');

                    const count =
                        document.getElementById('spkHoverCount');

                    if (!preview || !tbody) {
                        return;
                    }


                    const cache = {};

                    // Invalidasi hover setelah Save/Delete supaya Qty In tidak stale.
                    document.addEventListener('mutasi:timeline-changed', function(event) {
                        const spkId = String(event.detail?.spkId || '');
                        if (!spkId) return;
                        delete cache[spkId];
                        if (cache.timeline) {
                            Object.keys(cache.timeline).forEach(function(key) {
                                if (key.startsWith(spkId + ':')) delete cache.timeline[key];
                            });
                        }
                    });

                    let activeRow = null;

                    let hoverTimer = null;

                    let requestNo = 0;


                    function escapeHtml(value) {

                        const div =
                            document.createElement('div');

                        div.textContent =
                            value == null ?
                            '' :
                            String(value);

                        return div.innerHTML;

                    }


                    function formatNumber(value) {

                        const n =
                            Number(value);

                        if (!Number.isFinite(n)) {
                            return '-';
                        }

                        return n.toLocaleString('id-ID');

                    }


                    function getItemName(item) {

                        return (
                            item.nama ||
                            item.name ||
                            item.nama_item ||
                            item.item ||
                            item.description ||
                            '-'
                        );

                    }


                    function getItemQty(item) {

                        return Number(
                            item.qty ||
                            item.quantity ||
                            0
                        ) || 0;

                    }


                    function decodeItems(row) {

                        const encoded =
                            row.getAttribute(
                                'data-items-b64'
                            ) || '';


                        if (!encoded) {
                            return [];
                        }


                        const binary =
                            atob(encoded);


                        const bytes =
                            new Uint8Array(
                                binary.length
                            );


                        for (
                            let i = 0; i < binary.length; i++
                        ) {

                            bytes[i] =
                                binary.charCodeAt(i);

                        }


                        let json;


                        if (
                            typeof TextDecoder !==
                            'undefined'
                        ) {

                            json =
                                new TextDecoder(
                                    'utf-8'
                                ).decode(bytes);

                        } else {

                            let encodedText =
                                '';

                            for (
                                let i = 0; i < binary.length; i++
                            ) {

                                encodedText +=
                                    '%' +
                                    ('00' +
                                        binary
                                        .charCodeAt(i)
                                        .toString(16)
                                    ).slice(-2);

                            }


                            json =
                                decodeURIComponent(
                                    encodedText
                                );

                        }


                        const items =
                            JSON.parse(json);


                        return Array.isArray(items) ?
                            items :
                            [];

                    }


                    function render(items) {

                        if (!items.length) {

                            tbody.innerHTML = `
                                <tr>
                                    <td
                                        colspan="4"
                                        class="spk-hover-empty"
                                    >
                                        Tidak ada item
                                    </td>
                                </tr>
                            `;

                            count.textContent =
                                '0 item';

                            return;

                        }


                        let html =
                            '';

                        let displayIndex = 0;

                        items.forEach(
                            function(item) {

                                /*
                                | Jika item mempunyai custom_columns / komponen,
                                | tooltip menampilkan KOMPONEN item tersebut saja.
                                | Struktur dan Qty SPK mengikuti rincian komponen
                                | yang juga dipakai pada Detail Item.
                                |
                                | Jika tidak mempunyai komponen, behaviour lama
                                | tetap dipakai: tampilkan item utama.
                                */
                                const hasComponents =
                                    Array.isArray(item.hover_components) &&
                                    item.hover_components.length > 0;

                                if (hasComponents) {

                                    item.hover_components.forEach(
                                        function(component) {

                                            const name =
                                                component.name ||
                                                '-';

                                            const qty =
                                                Number(component.qtySpk || 0);

                                            const qtyIn =
                                                Number(component.qtyIn || 0) || 0;

                                            let inClass =
                                                'zero';

                                            if (
                                                qty > 0 &&
                                                qtyIn >= qty
                                            ) {
                                                inClass =
                                                    'full';
                                            } else if (
                                                qtyIn > 0
                                            ) {
                                                inClass =
                                                    'partial';
                                            }

                                            displayIndex++;

                                            html += `
                                                <tr>
                                                    <td>
                                                        ${displayIndex}
                                                    </td>

                                                    <td
                                                        class="spk-hover-name"
                                                        title="${escapeHtml(name)}"
                                                    >
                                                        ${escapeHtml(name)}
                                                    </td>

                                                    <td>
                                                        ${formatNumber(qty)}
                                                    </td>

                                                    <td
                                                        class="spk-hover-in ${inClass}"
                                                    >
                                                        ${formatNumber(qtyIn)}
                                                    </td>
                                                </tr>
                                            `;

                                        }
                                    );

                                    return;
                                }


                                /*
                                | Tidak ada komponen -> gunakan tampilan lama.
                                */
                                const name =
                                    getItemName(item);

                                const qty =
                                    getItemQty(item);

                                const qtyIn =
                                    Number(
                                        item.qty_in || 0
                                    ) || 0;

                                let inClass =
                                    'zero';

                                if (
                                    qty > 0 &&
                                    qtyIn >= qty
                                ) {
                                    inClass =
                                        'full';
                                } else if (
                                    qtyIn > 0
                                ) {
                                    inClass =
                                        'partial';
                                }

                                displayIndex++;

                                html += `
                                    <tr>
                                        <td>
                                            ${displayIndex}
                                        </td>

                                        <td
                                            class="spk-hover-name"
                                            title="${escapeHtml(name)}"
                                        >
                                            ${escapeHtml(name)}
                                        </td>

                                        <td>
                                            ${formatNumber(qty)}
                                        </td>

                                        <td
                                            class="spk-hover-in ${inClass}"
                                        >
                                            ${formatNumber(qtyIn)}
                                        </td>
                                    </tr>
                                `;

                            }
                        );


                        tbody.innerHTML =
                            html;


                        /*
                        | Counter tetap berdasarkan jumlah ITEM SPK,
                        | bukan jumlah baris komponen yang dirender.
                        */
                        count.textContent =
                            items.length +
                            ' item';

                    }


                    function movePreview(event) {

                        const gap =
                            16;

                        const padding =
                            10;

                        const rect =
                            preview.getBoundingClientRect();


                        let left =
                            event.clientX + gap;

                        let top =
                            event.clientY + gap;


                        if (
                            left + rect.width >
                            window.innerWidth - padding
                        ) {

                            left =
                                event.clientX -
                                rect.width -
                                gap;

                        }


                        if (
                            top + rect.height >
                            window.innerHeight - padding
                        ) {

                            top =
                                event.clientY -
                                rect.height -
                                gap;

                        }


                        if (left < padding) {
                            left = padding;
                        }


                        if (top < padding) {
                            top = padding;
                        }


                        preview.style.left =
                            left + 'px';

                        preview.style.top =
                            top + 'px';

                    }


                    function showPreview(row, event) {

                        activeRow = row;
                        clearTimeout(hoverTimer);

                        hoverTimer = setTimeout(async function() {

                            if (activeRow !== row) return;

                            const spkId = row.getAttribute('data-id');
                            if (!spkId) return;

                            /* Cache SPK: hover kedua tidak perlu request lagi. */
                            if (cache[spkId]) {
                                render(cache[spkId]);
                                preview.style.display = 'block';
                                preview.setAttribute('aria-hidden', 'false');
                                movePreview(event);
                                return;
                            }

                            const baseItems = decodeItems(row);
                            const items = baseItems.map(function(item) {
                                const copy = Object.assign({}, item);
                                copy.qty_in = 0;
                                copy.hover_components = null;
                                return copy;
                            });

                            /*
                            | Jangan tampilkan preview sementara.
                            | Preview baru dirender setelah timeline selesai dimuat,
                            | supaya tidak terlihat muncul dua kali (data awal lalu data final).
                            */
                            const thisRequest = ++requestNo;

                            if (!items.length) {
                                cache[spkId] = items;
                                render(items);
                                preview.style.display = 'block';
                                preview.setAttribute('aria-hidden', 'false');
                                movePreview(event);
                                return;
                            }

                            /*
                            | Cache timeline per Detail PO.
                            | Semua request dijalankan paralel, tetapi DOM hanya
                            | dirender sekali setelah seluruh response selesai.
                            */
                            const timelinePromises = items.map(function(item) {
                                const detailPoId = item.detail_po_id;

                                if (!detailPoId) {
                                    return Promise.resolve([]);
                                }

                                const cacheKey = spkId + ':' + detailPoId;

                                if (cache.timeline && cache.timeline[cacheKey]) {
                                    return Promise.resolve(cache.timeline[cacheKey]);
                                }

                                return fetch(
                                        '/mutasi/timeline/detail?spk_id=' +
                                        encodeURIComponent(spkId) +
                                        '&detail_po_id=' +
                                        encodeURIComponent(detailPoId), {
                                            method: 'GET',
                                            credentials: 'same-origin',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        }
                                    )
                                    .then(function(response) {
                                        if (!response.ok) {
                                            throw new Error('HTTP ' + response.status);
                                        }
                                        return response.json();
                                    })
                                    .then(function(res) {
                                        const timeline = Array.isArray(res.timeline) ?
                                            res.timeline :
                                            [];

                                        if (!cache.timeline) cache.timeline = {};
                                        cache.timeline[cacheKey] = timeline;

                                        return timeline;
                                    })
                                    .catch(function() {
                                        return [];
                                    });
                            });

                            const timelines = await Promise.all(timelinePromises);

                            if (thisRequest !== requestNo || activeRow !== row) {
                                return;
                            }

                            items.forEach(function(item, index) {
                                const timeline = timelines[index] || [];
                                let qtyIn = 0;

                                timeline.forEach(function(timelineRow) {
                                    const type = String(timelineRow.type || '')
                                        .trim()
                                        .toLowerCase();

                                    if (type === 'in' || type === 'service_masuk') qtyIn += Number(
                                        timelineRow.qty || 0);
                                    if (type === 'service_keluar') qtyIn -= Number(timelineRow
                                        .qty || 0);
                                });

                                item.qty_in = qtyIn;

                                const customColumns = Array.isArray(item.custom_columns) ?
                                    item.custom_columns :
                                    [];

                                item.hover_components = customColumns.length > 0 ?
                                    getComponentRows(item, timeline) :
                                    null;
                            });

                            cache[spkId] = items;

                            /* Render dan tampilkan preview satu kali setelah data final siap. */
                            render(items);
                            preview.style.display = 'block';
                            preview.setAttribute('aria-hidden', 'false');
                            movePreviewFromRow();

                        }, 100);

                    }


                    function movePreviewFromRow() {

                        if (!activeRow) {
                            return;
                        }


                        const rect =
                            activeRow.getBoundingClientRect();


                        const event = {
                            clientX: rect.right,

                            clientY: rect.top
                        };


                        movePreview(
                            event
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MOUSEOVER
                    |--------------------------------------------------------------------------
                    */

                    document.addEventListener(
                        'mouseover',
                        function(event) {

                            const row =
                                event.target.closest(
                                    'tr.pilih-spk'
                                );


                            if (!row) {
                                return;
                            }


                            if (
                                activeRow ===
                                row
                            ) {
                                return;
                            }


                            showPreview(
                                row,
                                event
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | MOUSEMOVE
                    |--------------------------------------------------------------------------
                    */

                    document.addEventListener(
                        'mousemove',
                        function(event) {

                            if (!activeRow) {
                                return;
                            }


                            const row =
                                event.target.closest(
                                    'tr.pilih-spk'
                                );


                            if (
                                row !==
                                activeRow
                            ) {

                                if (row) {

                                    showPreview(
                                        row,
                                        event
                                    );

                                }

                                return;

                            }


                            movePreview(
                                event
                            );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | MOUSEOUT
                    |--------------------------------------------------------------------------
                    */

                    document.addEventListener(
                        'mouseout',
                        function(event) {

                            if (!activeRow) {
                                return;
                            }


                            const fromRow =
                                event.target.closest ?
                                event.target.closest(
                                    'tr.pilih-spk'
                                ) :
                                null;


                            const toRow =
                                event.relatedTarget &&
                                event.relatedTarget.closest ?
                                event.relatedTarget.closest(
                                    'tr.pilih-spk'
                                ) :
                                null;


                            if (
                                fromRow ===
                                activeRow &&
                                toRow !==
                                activeRow
                            ) {

                                activeRow =
                                    null;


                                clearTimeout(
                                    hoverTimer
                                );


                                requestNo++;


                                preview.style.display =
                                    'none';


                                preview.setAttribute(
                                    'aria-hidden',
                                    'true'
                                );

                            }

                        }
                    );


                    window.addEventListener(
                        'scroll',
                        function() {

                            activeRow =
                                null;

                            requestNo++;

                            preview.style.display =
                                'none';

                        },
                        true
                    );

                })();
            </script>

            <style>
                /* =========================================================
                       SPK HOVER PREVIEW
                       ========================================================= */
                #tbodySpk tr.pilih-spk {
                    cursor: pointer;
                    transition: all 0.15s ease;
                }

                #tbodySpk tr.pilih-spk.active td {
                    background-color: #fff3cd !important;
                    color: #000 !important;
                }

                #tbodySpk tr.pilih-spk.active {
                    background-color: #fff3cd !important;
                    box-shadow: inset 4px 0 0 #ffc107;
                }

                #tbodySpk tr.pilih-spk.active:hover td {
                    background-color: #ffe69c !important;
                }

                #spkHoverPreview {
                    position: fixed;
                    display: none;
                    width: 430px;
                    max-width: calc(100vw - 24px);
                    background: #fff;
                    border: 1px solid #d9dee5;
                    border-radius: 9px;
                    box-shadow:
                        0 12px 30px rgba(15, 23, 42, .18),
                        0 3px 8px rgba(15, 23, 42, .08);
                    overflow: hidden;
                    z-index: 999999;
                    pointer-events: none;
                    font-size: 11px;
                }

                #spkHoverPreview .spk-hover-title {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 10px;
                    padding: 8px 11px;
                    background: #ffff00;
                    border-bottom: 1px solid #333;
                    color: #111;
                    font-size: 12px;
                    font-weight: 700;
                    line-height: 1.2;
                }

                #spkHoverPreview .spk-hover-title span:last-child {
                    font-size: 9px;
                    font-weight: 600;
                }

                #spkHoverPreview .spk-hover-body {
                    max-height: 330px;
                    overflow-y: auto;
                }

                #spkHoverPreview table {
                    width: 100%;
                    margin: 0;
                    border-collapse: collapse;
                    table-layout: fixed;
                }

                #spkHoverPreview th {
                    padding: 6px 8px;
                    background: #ffff00;
                    border-right: 1px solid #555;
                    border-bottom: 1px solid #333;
                    color: #111;
                    font-size: 10px;
                    font-weight: 700;
                    white-space: nowrap;
                }

                #spkHoverPreview td {
                    padding: 6px 8px;
                    background: #fff;
                    border-right: 1px solid #777;
                    border-bottom: 1px solid #777;
                    color: #333;
                    vertical-align: middle;
                }

                #spkHoverPreview th:first-child,
                #spkHoverPreview td:first-child {
                    width: 38px;
                    text-align: center;
                }

                #spkHoverPreview th:nth-child(3),
                #spkHoverPreview th:nth-child(4),
                #spkHoverPreview td:nth-child(3),
                #spkHoverPreview td:nth-child(4) {
                    width: 65px;
                    text-align: right;
                }

                #spkHoverPreview .spk-hover-name {
                    max-width: 0;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    font-weight: 600;
                }

                #spkHoverPreview .spk-hover-in {
                    font-weight: 700;
                }

                #spkHoverPreview .spk-hover-in.zero {
                    color: #dc3545;
                }

                #spkHoverPreview .spk-hover-in.partial {
                    color: #d97706;
                }

                #spkHoverPreview .spk-hover-in.full {
                    color: #198754;
                }

                #spkHoverPreview .spk-hover-loading,
                #spkHoverPreview .spk-hover-empty {
                    padding: 14px;
                    border: 0;
                    text-align: center;
                    color: #94a3b8;
                    font-size: 10px;
                }

                .spk-table-wrap tbody tr.pilih-spk {
                    transition: background-color .12s ease;
                }

                .spk-table-wrap tbody tr.pilih-spk:hover td {
                    background: #f0f7ff;
                }


                /* =========================================================
                                   STICKY HEADER
                                   ========================================================= */

                .spk-table-wrap {
                    max-height: calc(100vh - 220px);
                    overflow: auto;
                    position: relative;
                }

                .spk-table-wrap table {
                    margin-bottom: 0;
                    min-width: 850px;
                }

                .spk-table-wrap thead th {
                    position: sticky;
                    top: 0;
                    z-index: 30;
                    /* background: #f8f9fa !important; */
                    box-shadow: 0 1px 0 rgba(0, 0, 0, .12);
                    white-space: nowrap;
                }

                .spk-table-wrap tbody td {
                    background: #fff;
                }

                .spk-table-wrap tbody tr:hover td {
                    background: #f8fbff;
                }

                .modal-custom {
                    max-width: 95%;
                }

                .modal-custom .modal-content {
                    min-height: 85vh;
                }

                /* =========================================================
                                       ITEM DETAIL + COMPONENTS
                                       ========================================================= */

                .item-detail-grid {
                    display: grid;
                    grid-template-columns: minmax(300px, .9fr) minmax(420px, 1.35fr);
                    gap: 14px;
                    margin-bottom: 16px;
                }

                .item-detail-card {
                    min-width: 0;
                    overflow: hidden;
                    border: 1px solid #e7ebf0;
                    border-radius: 14px;
                    background: #fff;
                }

                .item-detail-card-header {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 12px;
                    padding: 14px 16px;
                    border-bottom: 1px solid #edf0f3;
                    background: #fafbfc;
                }

                .item-detail-eyebrow {
                    margin-bottom: 3px;
                    color: #94a3b8;
                    font-size: 9px;
                    font-weight: 700;
                    letter-spacing: .65px;
                    text-transform: uppercase;
                }

                .item-detail-title {
                    max-width: 520px;
                    color: #1e293b;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 1.35;
                }

                .item-code-badge,
                .component-count {
                    flex: 0 0 auto;
                    display: inline-flex;
                    align-items: center;
                    min-height: 26px;
                    padding: 0 9px;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    background: #fff;
                    color: #64748b;
                    font-size: 10px;
                    font-weight: 700;
                }

                .item-detail-table {
                    padding: 4px 16px 8px;
                }

                .item-detail-row {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 18px;
                    min-height: 38px;
                    border-bottom: 1px solid #f1f3f5;
                    color: #64748b;
                    font-size: 11px;
                }

                .item-detail-row:last-child {
                    border-bottom: 0;
                }

                .item-detail-row strong {
                    max-width: 70%;
                    color: #334155;
                    font-weight: 600;
                    text-align: right;
                }

                .item-detail-row-highlight {
                    color: #475569;
                }

                .item-detail-row-highlight strong {
                    color: #0f172a;
                    font-size: 12px;
                }

                .component-detail-card {
                    display: flex;
                    flex-direction: column;
                }

                .component-table-wrap {
                    overflow-x: auto;
                }

                .component-table {
                    width: 100%;
                    margin: 0;
                    border-collapse: collapse;
                    font-size: 11px;
                }

                .component-table th {
                    padding: 9px 12px;
                    border-bottom: 1px solid #e9edf1;
                    background: #fff;
                    color: #94a3b8;
                    font-size: 9px;
                    font-weight: 700;
                    letter-spacing: .35px;
                    text-transform: uppercase;
                    white-space: nowrap;
                }

                .component-table td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #f0f2f5;
                    color: #475569;
                    vertical-align: middle;
                }

                .component-table tbody tr:last-child td {
                    border-bottom: 0;
                }

                .component-table tbody tr:hover {
                    background: #fafbfc;
                }

                .component-name {
                    min-width: 150px;
                    color: #334155;
                    font-weight: 600;
                    line-height: 1.35;
                }

                .component-note {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    margin-top: auto;
                    padding: 9px 12px;
                    border-top: 1px solid #edf0f3;
                    background: #fafbfc;
                    color: #94a3b8;
                    font-size: 9px;
                }

                .component-note i {
                    font-size: 9px;
                }

                @media (max-width: 992px) {

                    .item-detail-grid {
                        grid-template-columns: 1fr;
                    }

                    .item-detail-title {
                        max-width: 75vw;
                    }

                }

                @media (max-width: 576px) {

                    .item-detail-grid {
                        gap: 10px;
                    }

                    .item-detail-card-header {
                        padding: 12px;
                    }

                    .item-detail-table {
                        padding: 3px 12px 7px;
                    }

                    .item-detail-row {
                        min-height: 35px;
                        font-size: 10px;
                    }

                    .item-detail-row strong {
                        max-width: 65%;
                    }

                    .component-table th,
                    .component-table td {
                        padding: 8px 9px;
                    }

                    .component-table {
                        font-size: 10px;
                    }

                    .component-name {
                        min-width: 135px;
                    }

                }

                .colored-toast {
                    background: #198754 !important;
                    color: #fff !important;
                }

                /* Keep the working timeline readable on smaller screens */
                #timelineTable {
                    overflow-x: auto;
                }

                #timelineTable>table {
                    min-width: 900px;
                }

                /* =========================================================
                       ITEM PICKER
                       ========================================================= */

                .item-picker-wrap {
                    margin-bottom: 18px;
                    padding: 14px 16px 16px;
                    border: 1px solid #e8edf3;
                    border-radius: 14px;
                    background: linear-gradient(180deg, #fbfcfe 0%, #f7f9fc 100%);
                }

                .item-picker-label {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }

                .item-picker-label-left {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    min-width: 0;
                }

                .item-picker-icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 34px;
                    height: 34px;
                    flex: 0 0 34px;
                    border: 1px solid #dbe5f0;
                    border-radius: 10px;
                    background: #fff;
                    color: #4f73d9;
                    box-shadow: 0 2px 6px rgba(15, 23, 42, .05);
                }

                .item-picker-title {
                    color: #1e293b;
                    font-size: 12px;
                    font-weight: 700;
                    line-height: 1.25;
                }

                .item-picker-subtitle {
                    margin-top: 2px;
                    color: #94a3b8;
                    font-size: 9px;
                    line-height: 1.35;
                }

                .item-select-shell-photo {
                    position: relative;
                }

                .item-select-shell-photo .item-select-photo-preview {
                    width: 46px;
                    height: 46px;
                    flex: 0 0 46px;
                    overflow: hidden;
                    border-radius: 8px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: #f3f5f7;
                    color: #64748b;
                }

                .item-select-shell-photo .item-select-photo-preview img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                }

                .item-select-photo-name {
                    margin-top: 6px;
                    padding-left: 54px;
                    display: flex;
                    flex-direction: column;
                    line-height: 1.25;
                }

                .item-select-photo-name-title {
                    font-size: 11px;
                    font-weight: 700;
                    color: #1f2937;
                }

                .item-select-photo-name-meta {
                    font-size: 10px;
                    color: #64748b;
                }

                .item-select-shell {
                    position: relative;
                }

                .item-select-leading-icon {
                    position: absolute;
                    top: 50%;
                    left: 13px;
                    z-index: 3;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 24px;
                    height: 24px;
                    transform: translateY(-50%);
                    border-radius: 7px;
                    background: #f1f5f9;
                    color: #64748b;
                    font-size: 10px;
                    pointer-events: none;
                    transition: all .18s ease;
                }

                #itemSelect.item-select {
                    width: 100%;
                    min-height: 46px;
                    padding: 10px 42px 10px 46px;
                    border: 1px solid #dbe2ea;
                    border-radius: 11px;
                    background-color: #fff;
                    color: #334155;
                    font-size: 12px;
                    font-weight: 600;
                    line-height: 1.4;
                    box-shadow: 0 2px 7px rgba(15, 23, 42, .04);
                    cursor: pointer;
                    outline: none;
                    appearance: none;
                    -webkit-appearance: none;
                    transition:
                        border-color .18s ease,
                        box-shadow .18s ease,
                        background-color .18s ease;
                }

                #itemSelect.item-select:hover {
                    border-color: #b8c7dc;
                    background-color: #fcfdff;
                }

                #itemSelect.item-select:focus {
                    border-color: #7b9be8;
                    background-color: #fff;
                    box-shadow:
                        0 0 0 3px rgba(79, 115, 217, .10),
                        0 4px 12px rgba(15, 23, 42, .05);
                }

                #itemSelect.item-select:focus+.item-select-arrow {
                    color: #4f73d9;
                    transform: translateY(-50%) rotate(180deg);
                }

                .item-select-arrow {
                    position: absolute;
                    top: 50%;
                    right: 14px;
                    z-index: 3;
                    color: #94a3b8;
                    font-size: 10px;
                    pointer-events: none;
                    transform: translateY(-50%);
                    transition: color .18s ease, transform .18s ease;
                }

                .item-select-shell:focus-within .item-select-leading-icon {
                    background: #eef3ff;
                    color: #4f73d9;
                }

                /* Selected item gets a slightly stronger visual hierarchy */
                #itemSelect.item-select:not([value=""]) {
                    color: #334155;
                }

                #itemSelect.item-select option {
                    padding: 8px;
                    color: #334155;
                    background: #fff;
                    font-weight: 500;
                }

                #itemSelect.item-select option:first-child {
                    color: #94a3b8;
                }



                /* =========================================================
                       MICRO UI IMPROVEMENT + PERFORMANCE
                       ========================================================= */
                .spk-table-wrap {
                    contain: layout paint;
                    border-radius: 12px;
                }

                #tbodySpk .pilih-spk {
                    transition: background-color .12s ease;
                }

                #tbodySpk .pilih-spk:hover {
                    background: #f7f9fc;
                }

                #tbodySpk .pilih-spk.active {
                    background: #eef4ff;
                    box-shadow: inset 3px 0 0 #4f73d9;
                }

                #spkHoverPreview {
                    contain: layout paint;
                    will-change: left, top;
                    border: 1px solid rgba(148, 163, 184, .28);
                    box-shadow: 0 14px 36px rgba(15, 23, 42, .16);
                }

                .item-detail-card,
                .item-picker-wrap {
                    contain: content;
                }

                @media (max-width: 576px) {
                    .item-picker-wrap {
                        padding: 12px;
                        border-radius: 12px;
                    }

                    .item-picker-icon {
                        width: 32px;
                        height: 32px;
                        flex-basis: 32px;
                    }

                    #itemSelect.item-select {
                        min-height: 44px;
                        font-size: 11px;
                        padding-left: 44px;
                    }
                }

                @media (max-width: 768px) {
                    .modal-custom {
                        max-width: 100%;
                        margin: .5rem;
                    }

                    .modal-custom .modal-content {
                        min-height: auto;
                    }

                    .modal-custom .modal-body {
                        padding: 12px;
                    }
                }

                /* Mutasi detail enhancement: same layout, refreshed inputs and summary */
                #timelineTable>table {
                    min-width: 730px;
                    border-collapse: separate;
                    border-spacing: 0;
                    border: 1px solid #e3eaf3;
                    border-radius: 12px;
                    overflow: hidden;
                }

                #timelineTable>table thead th {
                    background: #2b3e50;
                    color: #fff;
                    font-size: 11px;
                    font-weight: 700;
                    padding: 12px;
                    border-color: #405568;
                }

                #timelineTable>table tbody td {
                    padding: 8px 10px;
                    vertical-align: middle;
                    border-color: #eaf0f6;
                }

                #timelineTable>table tbody tr:hover {
                    background: #f5f9ff;
                }

                #timelineTable .tanggal,
                #timelineTable .type,
                #timelineTable .qty,
                #timelineTable .remark {
                    min-height: 36px;
                    border: 1px solid #dce5f0;
                    border-radius: 7px;
                    font-size: 12px;
                }

                #timelineTable .tanggal:focus,
                #timelineTable .type:focus,
                #timelineTable .qty:focus,
                #timelineTable .remark:focus {
                    border-color: #4d88f3;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
                }

                .rnd-service-summary {
                    margin: 12px 14px 14px;
                    padding: 13px;
                    border: 1px solid #dce7f4;
                    border-radius: 11px;
                    background: #f8fbff;
                }

                .rnd-service-heading {
                    font-size: 12px;
                    font-weight: 800;
                    color: #263b54;
                    margin-bottom: 11px;
                }

                .rnd-service-heading i {
                    margin-right: 7px;
                    color: #3168b7;
                }

                .rnd-service-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 8px;
                }

                .rnd-service-grid>div {
                    border: 1px solid #e4eaf2;
                    border-radius: 8px;
                    background: white;
                    padding: 10px;
                }

                .rnd-service-grid small {
                    display: block;
                    color: #7b8ba3;
                    font-size: 10px;
                    margin-bottom: 4px;
                }

                .rnd-service-grid strong {
                    color: #23374e;
                    font-size: 17px;
                }

                @media(max-width: 650px) {
                    .rnd-service-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }


                /* ===== MODAL MUTASI: VISUAL REFRESH ONLY ===== */
                #modalSpk .modal-dialog.modal-custom {
                    width: min(1160px, calc(100% - 32px));
                    max-width: 1160px;
                    margin: 24px auto;
                }

                #modalSpk .modal-content {
                    min-height: 0;
                    max-height: calc(100dvh - 48px);
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    border: 1px solid #e4eaf2;
                    border-radius: 18px;
                    background: #fff;
                    box-shadow: 0 24px 75px rgba(15, 23, 42, .18);
                }

                #modalSpk .modal-header {
                    flex: 0 0 auto;
                    padding: 18px 24px;
                    border-bottom: 1px solid #e8edf4;
                    background: #fff;
                }

                #modalSpk .modal-title {
                    color: #172b45;
                    font-size: 18px;
                    font-weight: 750;
                    letter-spacing: -.3px;
                }

                #modalSpk .btn-close {
                    padding: 9px;
                    border-radius: 10px;
                    background-color: #f0f4f8;
                    opacity: .8;
                }

                #modalSpk .modal-body {
                    flex: 1 1 auto;
                    overflow-y: auto;
                    overscroll-behavior: contain;
                    padding: 22px 24px 26px;
                    background: #f7f9fc;
                }

                #modalSpk .item-picker-wrap {
                    padding: 16px 18px;
                    margin-bottom: 16px;
                    border: 1px solid #e3eaf4;
                    border-radius: 14px;
                    background: #fff;
                    box-shadow: 0 2px 9px rgba(27, 45, 70, .025);
                }

                #modalSpk .item-picker-subtitle {
                    color: #64748b;
                    font-size: 12px;
                    font-weight: 500;
                    margin-bottom: 9px;
                }

                #modalSpk .item-select-shell {
                    border: 1px solid #dbe4f0;
                    border-radius: 11px;
                    background: #fff;
                    box-shadow: none;
                    transition: border-color .15s, box-shadow .15s;
                }

                #modalSpk .item-select-shell:focus-within {
                    border-color: #5285e8;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
                }

                #modalSpk .item-select {
                    min-height: 48px;
                    font-size: 13px;
                    font-weight: 600;
                    color: #27364b;
                }

                #modalSpk .item-detail-grid {
                    grid-template-columns: minmax(260px, .85fr) minmax(0, 1.25fr);
                    gap: 16px;
                    align-items: stretch;
                }

                #modalSpk .item-detail-card {
                    border: 1px solid #e3eaf3;
                    border-radius: 14px;
                    box-shadow: 0 2px 10px rgba(27, 45, 70, .025);
                }

                #modalSpk .item-detail-card-header {
                    padding: 16px 18px;
                    background: #fff;
                    border-bottom: 1px solid #eef1f6;
                }

                #modalSpk .item-detail-eyebrow {
                    color: #7789a3;
                    font-size: 10px;
                    font-weight: 750;
                    letter-spacing: .65px;
                    text-transform: uppercase;
                }

                #modalSpk .item-detail-title {
                    margin-top: 5px;
                    color: #172b45;
                    font-size: 15px;
                    font-weight: 750;
                    line-height: 1.35;
                }

                #modalSpk .item-detail-row {
                    min-height: 43px;
                    padding: 10px 18px;
                    border-bottom: 1px solid #f0f3f8;
                    font-size: 12px;
                }

                #modalSpk .item-detail-row strong {
                    font-size: 12px;
                    color: #26374c;
                }

                #modalSpk .component-table th {
                    background: #f8fafc;
                    color: #71819a;
                    padding: 11px 14px;
                    font-size: 10px;
                }

                #modalSpk .component-table td {
                    padding: 13px 14px;
                    font-size: 12px;
                }

                #modalSpk .component-name {
                    color: #26374c;
                    font-size: 13px;
                }

                #modalSpk .rnd-service-summary {
                    margin: 14px 16px;
                    padding: 14px;
                    border: 1px solid #e5ebf4;
                    border-radius: 12px;
                    background: #fafcff;
                }

                #modalSpk .rnd-service-heading {
                    font-size: 12px;
                    margin-bottom: 10px;
                }

                #modalSpk .rnd-service-grid {
                    gap: 9px;
                }

                #modalSpk .rnd-service-grid>div {
                    padding: 11px 12px;
                    border: 1px solid #e6ecf5;
                    border-radius: 10px;
                }

                #modalSpk .rnd-service-grid small {
                    font-size: 10px;
                }

                #modalSpk .rnd-service-grid strong {
                    font-size: 19px;
                }

                #modalSpk .rnd-service-grid>div:last-child strong {
                    color: #dc4758;
                }

                #modalSpk .component-note {
                    padding: 11px 16px;
                    font-size: 10px;
                    color: #73839a;
                    background: #fff;
                }

                #modalSpk>.modal-dialog hr,
                #modalSpk .modal-body>hr {
                    border: 0;
                    border-top: 1px solid #e5ebf3;
                    opacity: 1;
                    margin: 18px 0;
                }

                #modalSpk #timelineTable {
                    overflow-x: auto;
                    border-radius: 12px;
                }

                #modalSpk #timelineTable>table {
                    min-width: 700px;
                    width: 100%;
                    border: 1px solid #e2e9f2;
                    border-radius: 12px;
                    background: #fff;
                }

                #modalSpk #timelineTable>table thead th {
                    padding: 13px 12px;
                    background: #34465c;
                    font-size: 11px;
                }

                #modalSpk #timelineTable>table tbody td {
                    padding: 10px 9px;
                }

                #modalSpk #timelineTable .tanggal,
                #modalSpk #timelineTable .type,
                #modalSpk #timelineTable .qty,
                #modalSpk #timelineTable .remark {
                    width: 100%;
                    min-height: 38px;
                    padding: 7px 10px;
                    border-radius: 8px;
                    background: #fff;
                    font-size: 12px;
                }

                #modalSpk #timelineTable .btn {
                    border-radius: 8px;
                }

                @media (max-width: 900px) {
                    #modalSpk .item-detail-grid {
                        grid-template-columns: 1fr;
                    }

                    #modalSpk .modal-dialog.modal-custom {
                        width: calc(100% - 20px);
                        margin: 10px auto;
                    }

                    #modalSpk .modal-content {
                        max-height: calc(100dvh - 20px);
                    }

                    #modalSpk .modal-header {
                        padding: 15px 17px;
                    }

                    #modalSpk .modal-body {
                        padding: 15px;
                    }
                }

                @media (max-width: 600px) {
                    #modalSpk .rnd-service-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }

                    #modalSpk .item-picker-wrap {
                        padding: 13px;
                    }

                    #modalSpk .item-detail-card-header {
                        padding: 13px;
                    }

                    #modalSpk .item-detail-row {
                        padding: 9px 13px;
                    }
                }


                /* ===== WIDER MODAL + COMPACT CONTENT (CSS ONLY) ===== */
                @media (min-width: 901px) {
                    #modalSpk .modal-dialog.modal-custom {
                        width: calc(100vw - 48px) !important;
                        max-width: 1480px !important;
                        margin: 16px auto !important;
                    }

                    #modalSpk .modal-content {
                        max-height: calc(100dvh - 32px);
                    }

                    #modalSpk .item-detail-grid {
                        grid-template-columns: minmax(280px, .75fr) minmax(0, 1.55fr);
                        gap: 12px;
                    }
                }

                #modalSpk .modal-header {
                    padding: 12px 20px;
                }

                #modalSpk .modal-title {
                    font-size: 16px;
                }

                #modalSpk .modal-body {
                    padding: 14px 18px 18px;
                }

                #modalSpk .item-picker-wrap {
                    padding: 10px 14px;
                    margin-bottom: 11px;
                    border-radius: 10px;
                }

                #modalSpk .item-picker-subtitle {
                    margin-bottom: 5px;
                    font-size: 11px;
                }

                #modalSpk .item-select {
                    min-height: 39px;
                }

                #modalSpk #itemSelect.item-select {
                    min-height: 39px;
                }

                #modalSpk .item-select-photo-name {
                    margin-top: 3px;
                }

                #modalSpk .item-detail-card {
                    border-radius: 10px;
                }

                #modalSpk .item-detail-card-header {
                    padding: 10px 13px;
                }

                #modalSpk .item-detail-title {
                    font-size: 13px;
                    margin-top: 2px;
                }

                #modalSpk .item-detail-eyebrow {
                    font-size: 9px;
                }

                #modalSpk .item-detail-row {
                    min-height: 32px;
                    padding: 6px 12px;
                    font-size: 11px;
                }

                #modalSpk .item-detail-row strong {
                    font-size: 11px;
                }

                #modalSpk .component-table th {
                    padding: 8px 11px;
                    font-size: 9px;
                }

                #modalSpk .component-table td {
                    padding: 9px 11px;
                    font-size: 11px;
                }

                #modalSpk .component-name {
                    font-size: 11px;
                }

                #modalSpk .rnd-service-summary {
                    margin: 8px 11px 9px;
                    padding: 9px 11px;
                    border-radius: 9px;
                    background: #f8fbff;
                }

                #modalSpk .rnd-service-heading {
                    margin-bottom: 6px;
                    font-size: 11px;
                }

                #modalSpk .rnd-service-grid {
                    gap: 6px;
                }

                #modalSpk .rnd-service-grid>div {
                    padding: 7px 9px;
                    border-radius: 7px;
                }

                #modalSpk .rnd-service-grid small {
                    margin-bottom: 2px;
                    font-size: 9px;
                }

                #modalSpk .rnd-service-grid strong {
                    font-size: 15px;
                    line-height: 1.2;
                }

                #modalSpk .component-note {
                    padding: 8px 11px;
                    font-size: 10px;
                }

                #modalSpk .modal-body>hr {
                    margin: 12px 0;
                }

                #modalSpk #timelineTable>table thead th {
                    padding: 9px 10px;
                }

                #modalSpk #timelineTable>table tbody td {
                    padding: 6px 8px;
                }

                @media (max-width: 900px) {
                    #modalSpk .modal-dialog.modal-custom {
                        width: calc(100% - 16px);
                        margin: 8px auto;
                    }

                    #modalSpk .modal-body {
                        padding: 12px;
                    }
                }


                /* Compact top selector and attach visually to the detail cards */
                #modalSpk .modal-body {
                    padding-top: 5px !important;
                }

                #modalSpk .item-picker-wrap {
                    margin-top: 0 !important;
                    margin-bottom: 3px !important;
                    padding: 6px 10px 4px !important;
                    border-radius: 10px 10px 4px 4px !important;
                    box-shadow: none !important;
                }

                #modalSpk .item-picker-label {
                    margin: 0 !important;
                    padding: 0 !important;
                }

                #modalSpk .item-picker-subtitle {
                    margin: 0 0 2px !important;
                    line-height: 1.15 !important;
                }

                #modalSpk .item-select-shell {
                    min-height: 34px !important;
                }

                #modalSpk #itemSelect.item-select {
                    min-height: 34px !important;
                    padding-top: 4px !important;
                    padding-bottom: 4px !important;
                }

                #modalSpk .item-select-photo-name {
                    margin-top: 1px !important;
                }

                #modalSpk .item-select-photo-name-title,
                #modalSpk .item-select-photo-name-meta {
                    line-height: 1.1 !important;
                }

                #modalSpk .item-detail-grid {
                    gap: 9px !important;
                }

                /* Sticky header for timeline table within its scrollable region */
                #modalSpk #timelineTable {
                    max-height: min(58vh, 530px);
                    overflow: auto !important;
                    position: relative;
                }

                #modalSpk #timelineTable>table {
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                }

                #modalSpk #timelineTable>table thead th {
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 5 !important;
                    background: #34465c !important;
                    box-shadow: 0 1px 0 #dce5f0;
                }

                #modalSpk .component-table-wrap {
                    max-height: 300px;
                    overflow: auto;
                }

                #modalSpk .component-table thead th {
                    position: sticky;
                    top: 0;
                    z-index: 3;
                    background: #f8fafc;
                }

                #modalSpk .mutasi-loading-grid {
                    display: grid;
                    grid-template-columns: minmax(0, .75fr) minmax(0, 1.55fr);
                    gap: 9px;
                    margin-top: 0;
                }

                #modalSpk .mutasi-loading-panel {
                    min-height: 170px;
                    padding: 14px;
                    border: 1px solid #e4ebf5;
                    border-radius: 10px;
                    background: #fff;
                }

                #modalSpk .mutasi-skeleton {
                    height: 12px;
                    margin: 13px 0;
                    border-radius: 5px;
                    background: linear-gradient(90deg, #edf1f7 25%, #f9fbfe 50%, #edf1f7 75%);
                    background-size: 200% 100%;
                    animation: mutasiSkeletonPulse 1.3s ease-in-out infinite;
                }

                #modalSpk .mutasi-skeleton.short {
                    width: 32%;
                    height: 9px;
                }

                #modalSpk .mutasi-skeleton.medium {
                    width: 58%;
                    height: 17px;
                }

                #modalSpk .mutasi-loading-timeline {
                    padding: 15px;
                    background: #fff;
                    border: 1px solid #e4ebf5;
                    border-radius: 9px;
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    color: #64748b;
                    font-size: 12px;
                }

                @keyframes mutasiSkeletonPulse {
                    from {
                        background-position: 200% 0;
                    }

                    to {
                        background-position: -200% 0;
                    }
                }

                @media (max-width: 900px) {
                    #modalSpk .mutasi-loading-grid {
                        grid-template-columns: 1fr;
                    }

                    #modalSpk .mutasi-loading-panel {
                        min-height: 110px;
                    }
                }
            </style>
        
            {{-- Bottom navigation removed per request --}}
            {{-- <nav class="mutasi-mobile-nav" id="mutasiMobileNav" aria-label="Navigasi Monitoring SPK">
                <a href="#" class="is-active" onclick="event.preventDefault();window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-clipboard-list"></i>SPK</a>
                <a href="#" onclick="event.preventDefault();document.getElementById('searchSpk')?.focus()"><i class="fas fa-search"></i>Cari</a>
                <a href="#" onclick="event.preventDefault();window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-layer-group"></i>Item</a>
                <a href="#" onclick="event.preventDefault();window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-chart-bar"></i>Summary</a>
            </nav> --}}
            {{-- Mobile-only UI layer: no changes to save/delete/timeline endpoints --}}
            <style>
                #mutasiPagePreloader {
                    position: fixed; inset: 0; z-index: 99999;
                    display: grid; place-items: center;
                    background: #f5f8fd;
                    transition: opacity .22s ease, visibility .22s ease;
                }
                #mutasiPagePreloader.is-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
                .mutasi-preloader-card {
                    display: flex; flex-direction: column; align-items: center;
                    gap: 12px; padding: 30px 36px; background: white;
                    border: 1px solid #e4ebf5; border-radius: 18px;
                    box-shadow: 0 14px 44px rgba(26,50,91,.08);
                    color: #132c54; font-size: 14px;
                }
                .mutasi-preloader-card small { color: #71819a; font-size: 12px; }
                .mobile-item-tabs, .mutasi-mobile-nav { display: none; }
                @media (max-width: 767.98px) {
                    .container-fluid.py-4 { padding: 12px !important; }
                    .card-header.bg-white { border-radius: 15px 15px 0 0; padding: 14px !important; }
                    .card-header .d-flex { gap: 12px !important; }
                    .card-header h4 { font-size: 18px; }
                    .card-header .d-flex > div:last-child {
                        min-width: 0 !important; max-width: none !important; width: 100% !important;
                    }
                    .spk-table-wrap { display: none !important; }
                    #mobileSpkCards { display: grid; gap: 8px; padding-bottom: 78px; }
                    .mutasi-mobile-nav { display:flex; }
                    .mutasi-mobile-nav {
                        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1040;
                        background: white; border-top: 1px solid #e2e9f4;
                        display: flex; justify-content: space-around;
                        padding: 9px 8px max(9px, env(safe-area-inset-bottom));
                        box-shadow: 0 -4px 18px rgba(20,48,86,.06);
                    }
                    .mutasi-mobile-nav a { display:flex; flex-direction:column; align-items:center; gap:3px;
                        text-decoration:none; font-size:10px; color:#7a89a1; font-weight:600; }
                    .mutasi-mobile-nav a.is-active { color:#1d5de5; }
                    .mutasi-mobile-nav i { font-size:17px; }
                    .mobile-spk-card { text-align:left; width:100%; font-family:inherit; }
                    .mobile-spk-card .mobile-spk-buyer { margin:5px 0 4px; }
                    #modalSpk #itemInfo:empty::after {
                        content:"Pilih item untuk menampilkan detail dan timeline";
                        display:block; padding:18px; text-align:center;
                        color:#71819a; font-size:12px;
                    }
                    .mobile-spk-card {
                        background: white; border: 1px solid #e2eaf5;
                        border-radius: 14px; padding: 13px; cursor: pointer;
                        box-shadow: 0 3px 12px rgba(16,44,84,.035);
                    }
                    .mobile-spk-card:active { background: #f0f6ff; }
                    .mobile-spk-top { display:flex; align-items:center; justify-content:space-between; gap:8px; }
                    .mobile-spk-number { color:#1454c5; font-size:12px; font-weight:800; overflow-wrap:anywhere; }
                    .mobile-spk-arrow { color:#2365e8; }
                    .mobile-spk-buyer { color:#172c4b; font-size:13px; font-weight:700; margin:8px 0 5px; }
                    .mobile-spk-meta { display:flex; flex-wrap:wrap; gap:6px; }
                    .mobile-spk-meta span { font-size:10px; padding:5px 7px; border-radius:7px; background:#f0f5fb; color:#52647c; }
                    #spkHoverPreview { display:none !important; }
                    #modalSpk .modal-dialog.modal-custom {
                        width:100% !important; max-width:none !important;
                        margin:0 !important; height:100dvh !important;
                    }
                    #modalSpk .modal-content {
                        height:100dvh !important; max-height:100dvh !important;
                        border:0 !important; border-radius:0 !important;
                    }
                    #modalSpk .modal-header { padding:14px 16px !important; }
                    #modalSpk .modal-title { font-size:15px !important; overflow-wrap:anywhere; }
                    #modalSpk .modal-body { padding:12px !important; padding-top:12px !important; overflow-y:auto !important; }
                    #modalSpk .item-picker-wrap { display:none !important; }
                    #modalSpk .mobile-item-tabs {
                        display:flex; align-items:center; position:relative;
                        gap:4px; margin:0 0 8px; min-width:0;
                    }
                    #modalSpk .mobile-item-tabs-track {
                        position:relative; z-index:2;
                        display:flex; flex:1; gap:5px; min-width:0;
                        overflow-x:auto; scroll-behavior:smooth;
                        scroll-snap-type:x proximity; padding:2px 3px 5px 2px;
                        scrollbar-width:none;
                    }
                    #modalSpk .mobile-item-tabs-track::-webkit-scrollbar { display:none; }
                    #modalSpk .mobile-item-tab {
                        position:relative; z-index:2; pointer-events:auto !important;
                        cursor:pointer; touch-action:manipulation;
                        flex:0 0 116px; max-width:116px; min-height:45px;
                        display:flex; align-items:center; gap:5px; text-align:left;
                        background:#fff; border:1px solid #e3eaf4; border-radius:8px;
                        padding:5px; color:#243b5b; scroll-snap-align:start;
                    }
                    #modalSpk .mobile-item-tab.is-active {
                        border-color:#2563eb; background:#edf5ff;
                        box-shadow:0 0 0 1px #2563eb inset;
                    }
                    #modalSpk .mobile-item-tab img {
                        width:30px; height:32px; object-fit:cover; border-radius:5px;
                        background:#f3f6fa; flex-shrink:0;
                    }
                    #modalSpk .mobile-item-tab-label { min-width:0; }
                    #modalSpk .mobile-item-tab-code {
                        display:block; color:#174fc1; font-size:9px; font-weight:800;
                        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
                    }
                    #modalSpk .mobile-item-tab-qty { display:block; font-size:9px; color:#71809a; margin-top:2px; }
                    #modalSpk .mobile-item-next {
                        flex:0 0 28px; height:34px; border:1px solid #d9e6fb;
                        border-radius:11px; background:#fff; color:#1763e5;
                        box-shadow:0 3px 12px rgba(25,86,179,.10);
                    }
                    #modalSpk .mobile-item-next:disabled { opacity:.35; }
                    #modalSpk .item-detail-grid { grid-template-columns:minmax(0,1fr) !important; }
                    #modalSpk .item-detail-card { min-width:0; }
                    #modalSpk .item-detail-card-header { flex-wrap:wrap; gap:7px; }
                    #modalSpk .item-detail-title, #modalSpk .component-name { overflow-wrap:anywhere; }
                    #modalSpk .component-table-wrap { overflow-x:auto; }
                    #modalSpk .component-table { min-width:330px; }
                    #modalSpk .rnd-service-grid { grid-template-columns:repeat(2,minmax(0,1fr)) !important; }
                    #modalSpk #timelineTable { max-height:none !important; overflow:visible !important; }
                    #modalSpk #timelineTable > table,
                    #modalSpk #timelineTable > table tbody,
                    #modalSpk #timelineTable > table tr,
                    #modalSpk #timelineTable > table td { display:block; width:100%; }
                    #modalSpk #timelineTable > table { border:0 !important; }
                    #modalSpk #timelineTable > table thead { display:none; }
                    #modalSpk #timelineTable > table tbody tr {
                        margin:0 0 12px; padding:12px; background:#fff;
                        border:1px solid #e0e8f4; border-radius:12px;
                    }
                    #modalSpk #timelineTable > table tbody td {
                        border:0 !important; padding:5px 0 !important;
                        display:grid; grid-template-columns:70px minmax(0,1fr);
                        gap:8px; align-items:center;
                    }
                    #modalSpk #timelineTable > table tbody td::before {
                        font-size:11px; font-weight:700; color:#667894;
                    }
                    #modalSpk #timelineTable > table tbody td:nth-child(1)::before { content:"No"; }
                    #modalSpk #timelineTable > table tbody td:nth-child(2)::before { content:"Tanggal"; }
                    #modalSpk #timelineTable > table tbody td:nth-child(3)::before { content:"Type"; }
                    #modalSpk #timelineTable > table tbody td:nth-child(4)::before { content:"Qty"; }
                    #modalSpk #timelineTable > table tbody td:nth-child(5)::before { content:"Remark"; }
                    #modalSpk #timelineTable > table tbody td:nth-child(6)::before { content:"Aksi"; }
                    #modalSpk #timelineTable .tanggal, #modalSpk #timelineTable .type,
                    #modalSpk #timelineTable .qty, #modalSpk #timelineTable .remark {
                        width:100%; min-width:0; font-size:16px !important;
                    }
                    #modalSpk #timelineTable > .d-flex { gap:10px; }
                    #modalSpk #timelineTable > .d-flex .btn {
                        flex:1; min-height:42px; font-size:12px;
                    }
                    #modalSpk .mutasi-loading-grid { grid-template-columns:1fr !important; }
                }
                /* V9: mobile-only compact dashboard + editable timeline table */
                @media (max-width: 767.98px) {
                    #mobileSpkCards { gap: 0 !important; padding-bottom: 76px !important; background:#fff;
                        border:1px solid #e8eef7; border-radius:12px; overflow:hidden; }
                    #mobileSpkCards .mobile-spk-card {
                        border:0 !important; border-bottom:1px solid #edf1f7 !important;
                        border-radius:0 !important; box-shadow:none !important;
                        display:flex; align-items:center; gap:10px; padding:11px 9px !important;
                        min-height:82px; background:#fff !important;
                    }
                    #mobileSpkCards .mobile-spk-card:active { background:#eef5ff !important; }
                    #mobileSpkCards .mobile-spk-index { flex:0 0 25px; width:25px; height:27px;
                        display:grid; place-items:center; border-radius:8px; background:#edf4ff;
                        color:#2458b8; font-weight:800; font-size:11px; }
                    #mobileSpkCards .mobile-spk-content { flex:1; min-width:0; }
                    #mobileSpkCards .mobile-spk-top { display:block; }
                    #mobileSpkCards .mobile-spk-number { font-size:11px; line-height:1.4; }
                    #mobileSpkCards .mobile-spk-buyer { font-size:11px; margin:3px 0 !important; }
                    #mobileSpkCards .mobile-spk-meta { display:block; }
                    #mobileSpkCards .mobile-spk-meta span { display:block; background:none;
                        border-radius:0; padding:0; font-size:10px; line-height:1.55; }
                    #mobileSpkCards .mobile-spk-arrow { flex:0 0 18px; color:#244b8e; }
                    #mobileSpkCards .mobile-spk-detail-label { flex:0 0 auto; display:inline-flex;
                        align-items:center; justify-content:center; padding:6px 9px;
                        border-radius:7px; background:#edf4ff; color:#2458b8;
                        font-size:10px; font-weight:800; white-space:nowrap; }
                    #modalSpk #timelineTable { width:100%; overflow-x:auto !important; }
                    #modalSpk #timelineTable > table { display:table !important; width:100% !important;
                        min-width:0 !important; border-collapse:collapse !important; table-layout:fixed !important;
                        font-size:10px !important; }
                    #modalSpk #timelineTable > table thead { display:table-header-group !important; }
                    #modalSpk #timelineTable > table tbody { display:table-row-group !important; }
                    #modalSpk #timelineTable > table tr { display:table-row !important; }
                    #modalSpk #timelineTable > table th,
                    #modalSpk #timelineTable > table td { display:table-cell !important;
                        width:auto !important; border:1px solid #e5ebf4 !important;
                        padding:5px 3px !important; vertical-align:middle !important; }
                    #modalSpk #timelineTable > table thead th { position:sticky; top:0; z-index:2;
                        background:#eef3fb !important; color:#32445f !important;
                        font-size:9px !important; white-space:nowrap; }
                    #modalSpk #timelineTable > table tbody tr { margin:0 !important;
                        background:#fff !important; border:0 !important; border-radius:0 !important; }
                    #modalSpk #timelineTable > table tbody td::before { display:none !important; content:none !important; }
                    #modalSpk #timelineTable > table th:nth-child(1),
                    #modalSpk #timelineTable > table td:nth-child(1) { width:7% !important; text-align:center; }
                    #modalSpk #timelineTable > table th:nth-child(2),
                    #modalSpk #timelineTable > table td:nth-child(2) { width:27% !important; }
                    #modalSpk #timelineTable > table th:nth-child(3),
                    #modalSpk #timelineTable > table td:nth-child(3) { width:23% !important; }
                    #modalSpk #timelineTable > table th:nth-child(4),
                    #modalSpk #timelineTable > table td:nth-child(4) { width:12% !important; }
                    #modalSpk #timelineTable > table th:nth-child(5),
                    #modalSpk #timelineTable > table td:nth-child(5) { width:18% !important; }
                    #modalSpk #timelineTable > table th:nth-child(6),
                    #modalSpk #timelineTable > table td:nth-child(6) { width:13% !important; text-align:center; }
                    #modalSpk #timelineTable .tanggal,
                    #modalSpk #timelineTable .type,
                    #modalSpk #timelineTable .qty,
                    #modalSpk #timelineTable .remark { display:block !important; width:100% !important;
                        min-width:0 !important; height:30px !important; padding:3px 2px !important;
                        font-size:10px !important; border-radius:5px !important; }
                    #modalSpk #timelineTable .hapus-row { padding:5px 6px !important; font-size:11px !important; }
                    #modalSpk #timelineTable > .d-flex { margin-top:10px; }
                }
                @media (max-width: 767.98px) {
                    #mobileSpkCards { padding-bottom: 12px !important; }
                    #modalSpk .modal-header { position:sticky; top:0; z-index:1055; background:#fff; }
                    #modalSpk #mutasiMobileClose { display:flex !important; align-items:center; justify-content:center;
                        width:32px !important; height:32px !important; min-width:32px; opacity:1 !important;
                        cursor:pointer; pointer-events:auto !important; background-color:#f1f5fa;
                        border-radius:50%; }
                    #modalSpk #mutasiMobileClose span { font-size:25px; line-height:1; color:#334155; }
                    #modalSpk #mobileItemNext { cursor:pointer; pointer-events:auto !important; }
                }
                /* V12: keep focused transaction inputs visible above the mobile keyboard */
                @media (max-width: 767.98px) {
                    #modalSpk .modal-dialog.modal-custom,
                    #modalSpk .modal-content {
                        height: var(--mutasi-visible-height, 100dvh) !important;
                        max-height: var(--mutasi-visible-height, 100dvh) !important;
                    }
                    #modalSpk .modal-content { display:flex !important; flex-direction:column !important; }
                    #modalSpk .modal-header { flex:0 0 auto; }
                    #modalSpk .modal-body {
                        flex:1 1 auto !important; min-height:0 !important;
                        overflow-y:auto !important; overscroll-behavior:contain;
                        -webkit-overflow-scrolling:touch;
                        padding-bottom:24px !important;
                    }
                    #modalSpk #timelineTable input,
                    #modalSpk #timelineTable select,
                    #modalSpk #timelineTable textarea { scroll-margin-block:85px 30px; }
                }
                /* V14: mobile search/header remains visible while scrolling the SPK list. */
                @media (max-width: 767.98px) {
                    .container-fluid.py-4 > .card-header.bg-white {
                        position: sticky !important;
                        top: 0 !important;
                        z-index: 1020 !important;
                        background: #fff !important;
                        border: 1px solid #e6edf5;
                        border-radius: 12px !important;
                        box-shadow: 0 4px 14px rgba(18, 43, 80, .08);
                        padding: 10px 12px !important;
                        margin-bottom: 8px;
                    }
                    .container-fluid.py-4 > .card-header.bg-white .d-flex {
                        gap: 7px !important;
                    }
                    .container-fluid.py-4 > .card-header.bg-white h4 {
                        font-size: 15px !important;
                    }
                    .container-fluid.py-4 > .card-header.bg-white small {
                        font-size: 10px !important;
                        line-height: 1.35;
                        display: block;
                    }
                    .container-fluid.py-4 > .card-header.bg-white #searchSpk {
                        min-height: 36px;
                        font-size: 12px;
                    }
                    .container-fluid.py-4 > .card-header.bg-white .input-group-text {
                        padding: 7px 10px;
                    }
                    .container-fluid.py-4 > .card-body { overflow: visible !important; }
                }
                @media (min-width: 768px) { #mobileSpkCards { display:none !important; } }
                @media (prefers-reduced-motion: reduce) {
                    #mutasiPagePreloader, #modalSpk .mobile-item-tabs-track { transition:none; scroll-behavior:auto; }
                }
            </style>
            <script>
                (function () {
                    // Preload initial page only; never block existing AJAX actions.
                    function hidePagePreloader() {
                        var el = document.getElementById('mutasiPagePreloader');
                        if (!el) return;
                        el.classList.add('is-hidden');
                        setTimeout(function () { if (el.parentNode) el.remove(); }, 300);
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', hidePagePreloader, {once:true});
                    } else {
                        hidePagePreloader();
                    }
                    window.addEventListener('load', hidePagePreloader, {once:true});

                    // iOS/Android keyboard: resize modal to visual viewport and reveal active field.
                    (function setupMobileKeyboardScroll() {
                        var modal = document.getElementById('modalSpk');
                        if (!modal) return;
                        var pending;
                        function isMobile() { return window.matchMedia('(max-width: 767.98px)').matches; }
                        function updateViewport() {
                            if (!isMobile()) return;
                            var height = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                            modal.style.setProperty('--mutasi-visible-height', Math.round(height) + 'px');
                            // visualViewport may be offset when the browser pans the layout.
                            modal.style.top = window.visualViewport ? Math.round(window.visualViewport.offsetTop) + 'px' : '0px';
                        }
                        function revealFocusedInput() {
                            if (!isMobile() || !modal.classList.contains('show')) return;
                            var field = document.activeElement;
                            if (!field || !modal.contains(field) || !field.matches('input,textarea,select')) return;
                            var scroller = modal.querySelector('.modal-body');
                            if (!scroller) return;
                            var fieldRect = field.getBoundingClientRect();
                            var bodyRect = scroller.getBoundingClientRect();
                            var topSafe = bodyRect.top + 16;
                            var bottomSafe = bodyRect.bottom - 22;
                            if (fieldRect.bottom > bottomSafe) {
                                scroller.scrollTop += fieldRect.bottom - bottomSafe;
                            } else if (fieldRect.top < topSafe) {
                                scroller.scrollTop -= topSafe - fieldRect.top;
                            }
                        }
                        function refreshFocusedInput() {
                            updateViewport();
                            clearTimeout(pending);
                            pending = setTimeout(revealFocusedInput, 180);
                        }
                        modal.addEventListener('focusin', function(event) {
                            if (!event.target.matches('input,textarea,select')) return;
                            refreshFocusedInput();
                            setTimeout(revealFocusedInput, 400);
                        });
                        if (window.visualViewport) {
                            window.visualViewport.addEventListener('resize', refreshFocusedInput);
                            window.visualViewport.addEventListener('scroll', refreshFocusedInput);
                        }
                        window.addEventListener('resize', refreshFocusedInput);
                        modal.addEventListener('shown.bs.modal', updateViewport);
                        modal.addEventListener('hidden.bs.modal', function() {
                            modal.style.removeProperty('--mutasi-visible-height');
                            modal.style.top = '';
                        });
                        updateViewport();
                    })();
                    function escapeText(value) {
                        return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
                            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
                        });
                    }
                    function buildMobileSpkCards() {
                        var body = document.getElementById('tbodySpk');
                        if (!body) return;
                        var container = document.getElementById('mobileSpkCards');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'mobileSpkCards';
                            container.setAttribute('aria-label','Daftar SPK');
                            var wrap = document.querySelector('.spk-table-wrap');
                            if (wrap) wrap.insertAdjacentElement('afterend',container);
                        }
                        container.innerHTML = '';
                        body.querySelectorAll('tr.pilih-spk').forEach(function(row) {
                            var td = row.querySelectorAll('td');
                            var card = document.createElement('button');
                            card.type='button'; card.className='mobile-spk-card';
                            card.dataset.search = row.getAttribute('data-search') || '';
                            card.innerHTML =
                                '<span class="mobile-spk-index">'+escapeText(td[0]?.textContent.trim() || '-')+'</span>'+
                                '<span class="mobile-spk-content">'+
                                '<span class="mobile-spk-top"><span class="mobile-spk-number">'+escapeText(td[1]?.textContent.trim() || '-')+'</span></span>'+
                                '<span class="mobile-spk-buyer">'+escapeText(td[3]?.textContent.trim() || '-')+'</span>'+
                                '<span class="mobile-spk-meta"><span>PO: '+escapeText(td[2]?.textContent.trim() || '-')+'</span>'+
                                '<span>Jenis: '+escapeText(td[4]?.textContent.trim() || '-')+'</span>'+
                                '<span>Supplier: '+escapeText(td[5]?.textContent.trim() || '-')+'</span></span></span>'+
                                '<span class="mobile-spk-detail-label" aria-hidden="true">Detail</span>';
                            card.addEventListener('click',function(){ row.click(); });
                            container.appendChild(card);
                        });
                        if (!container.children.length) {
                            container.innerHTML='<div class="text-center text-muted p-4">Tidak ada data SPK</div>';
                        }
                    }
                    function syncMobileSearch() {
                        var term = (document.getElementById('searchSpk')?.value || '').toLowerCase().trim();
                        document.querySelectorAll('#mobileSpkCards .mobile-spk-card').forEach(function(card){
                            card.style.display = String(card.dataset.search || '').toLowerCase().includes(term) ? '' : 'none';
                        });
                    }
                    window.renderMobileItemTabs = function() {
                        var track = document.getElementById('mobileItemTabsTrack');
                        var select = document.getElementById('itemSelect');
                        if (!track || !select) return;
                        track.innerHTML = '';
                        (Array.isArray(items) ? items : []).forEach(function(item, index) {
                            var button = document.createElement('button');
                            button.type='button'; button.className='mobile-item-tab';
                            button.setAttribute('role','tab');
                            var photo = typeof getItemPhotoUrl === 'function' ? getItemPhotoUrl(item.photo) : '';
                            var code = item.kode || item.nama || 'Item ' + (index + 1);
                            button.innerHTML =
                                '<img alt="" src="'+escapeText(photo || "{{ asset('storage/rouf.jpeg') }}")+'">'+
                                '<span class="mobile-item-tab-label"><span class="mobile-item-tab-code">'+escapeText(code)+'</span>'+
                                '<span class="mobile-item-tab-qty">'+escapeText(item.qty ?? 0)+' '+escapeText(item.satuan || 'pcs')+'</span></span>';
                            button.querySelector('img').onerror=function(){
                                this.onerror=null; this.src="{{ asset('storage/rouf.jpeg') }}";
                            };
                            button.addEventListener('click',function(event){
                                event.preventDefault();
                                event.stopPropagation();
                                // Select by the same data-index used by the existing change handler.
                                var option = Array.from(select.options).find(function(o){
                                    return o.value !== '' && o.getAttribute('data-index') === String(index);
                                });
                                if (!option) {
                                    document.getElementById('itemInfo').innerHTML =
                                        '<div class="alert alert-warning">Item ini tidak memiliki ID detail PO. Periksa data <code>detail_po_id</code> dari endpoint SPK.</div>';
                                    return;
                                }
                                select.selectedIndex = option.index;
                                document.getElementById('itemInfo').innerHTML =
                                    '<div class="mutasi-loading-timeline"><span class="spinner-border spinner-border-sm" role="status"></span> Memuat detail item...</div>';
                                if (typeof window.loadSelectedMutasiItem === 'function') {
                                    window.loadSelectedMutasiItem();
                                } else {
                                    window.jQuery(select).trigger('change');
                                }
                                syncActiveMobileTab(true);
                            });
                            track.appendChild(button);
                        });
                        syncActiveMobileTab(false);
                        requestAnimationFrame(updateNextButton);
                    };
                    function syncActiveMobileTab(shouldScroll) {
                        var track = document.getElementById('mobileItemTabsTrack');
                        var select = document.getElementById('itemSelect');
                        if (!track || !select) return;
                        var index = Number(select.selectedOptions[0]?.dataset.index);
                        var hasSelection = Boolean(select.value);
                        track.querySelectorAll('.mobile-item-tab').forEach(function(tab, i) {
                            var active = hasSelection && i === index;
                            tab.classList.toggle('is-active', active);
                            tab.setAttribute('aria-selected', active ? 'true':'false');
                            if (active && shouldScroll && window.matchMedia('(max-width: 767.98px)').matches) {
                                // Align the clicked item to the LEFT edge of the horizontal strip.
                                // Scroll the strip only: scrollIntoView can also move the whole modal.
                                var targetLeft = tab.offsetLeft - track.offsetLeft;
                                var maxLeft = Math.max(0, track.scrollWidth - track.clientWidth);
                                track.scrollTo({left: Math.min(Math.max(0, targetLeft), maxLeft), behavior:'smooth'});
                            }
                        });
                        updateNextButton();
                    }
                    window.syncMutasiMobileActiveTab = function() { syncActiveMobileTab(true); };
                    function updateNextButton() {
                        var next = document.getElementById('mobileItemNext');
                        var track = document.getElementById('mobileItemTabsTrack');
                        if (!next || !track) return;
                        var canScroll = track.scrollWidth > track.clientWidth + 2;
                        var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 3;
                        next.disabled = !canScroll || atEnd;
                        next.setAttribute('aria-disabled', next.disabled ? 'true' : 'false');
                    }
                    function selectNextMobileItem() {
                        var track = document.getElementById('mobileItemTabsTrack');
                        if (!track) return;
                        // Arrow only scrolls the tab strip; it must NOT change the selected item.
                        var step = Math.max(110, Math.round(track.clientWidth * 0.72));
                        track.scrollBy({left:step,behavior:'smooth'});
                        window.setTimeout(updateNextButton, 350);
                    }
                    function closeMutasiDetail() {
                        var modal = document.getElementById('modalSpk');
                        if (!modal) return;
                        // The existing page opens this modal through jQuery/Bootstrap.
                        // Use that same instance to close it and trigger hidden.bs.modal.
                        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                            window.jQuery(modal).modal('hide');
                        } else if (window.bootstrap && window.bootstrap.Modal) {
                            var instance = window.bootstrap.Modal.getInstance(modal);
                            if (instance) instance.hide();
                            else window.bootstrap.Modal.getOrCreateInstance(modal).hide();
                        }
                    }
                    // Delegated listener works even if mobile UI is rebuilt.
                    document.addEventListener('click', function(e) {
                        var button = e.target.closest('#mutasiMobileClose');
                        if (!button) return;
                        e.preventDefault();
                        e.stopPropagation();
                        closeMutasiDetail();
                    }, true);
                    document.addEventListener('DOMContentLoaded',function(){
                        buildMobileSpkCards();
                        document.getElementById('searchSpk')?.addEventListener('input',syncMobileSearch);
                        document.getElementById('itemSelect')?.addEventListener('change',function(){
                            syncActiveMobileTab(true);
                        });
                        document.getElementById('mobileItemTabsTrack')?.addEventListener('scroll',updateNextButton,{passive:true});
                        // Handle arrow clicks even if the tabbar is rebuilt dynamically.
                        document.addEventListener('click',function(event){
                            if (!event.target.closest('#mobileItemNext')) return;
                            event.preventDefault();
                            selectNextMobileItem();
                        },true);
                        var search = document.getElementById('searchSpk');
                        document.getElementById('mobileSearchButton')?.addEventListener('click',function(){
                            if (!search) return;
                            search.focus();
                            if (typeof search.select === 'function' && search.value) search.select();
                        });
                        var clear = document.getElementById('mobileSearchClear');
                        function updateSearchClear(){
                            if(clear) clear.style.display = search && search.value ? 'inline-flex':'none';
                        }
                        search?.addEventListener('input',updateSearchClear);
                        clear?.addEventListener('click',function(){
                            if(!search) return;
                            search.value='';
                            search.dispatchEvent(new Event('input',{bubbles:true}));
                            search.dispatchEvent(new Event('keyup',{bubbles:true}));
                            search.focus();
                            updateSearchClear();
                        });
                        updateSearchClear();
                        window.addEventListener('resize',updateNextButton);
                        $('#modalSpk').on('shown.bs.modal',function(){
                            updateNextButton();
                            syncActiveMobileTab(true);
                        });
                        // Mobile cards follow the existing search input; desktop remains untouched.
                        syncMobileSearch();
                    });
                })();
            </script>


            <style>
                @media (max-width:767.98px) {
                    .container-fluid.py-4 > .card-header.bg-white {
                        position:sticky !important; top:0 !important; z-index:1030 !important;
                        background:#fff !important;
                    }
                    .container-fluid.py-4 > .card-header.bg-white .input-group { display:flex; align-items:stretch; }
                    .container-fluid.py-4 > .card-header.bg-white #searchSpk { flex:1 1 auto; min-width:0; }
                    #mobileSearchClear { align-items:center; justify-content:center; width:35px; padding:0;
                        color:#52617a; font-size:20px; border-radius:0 7px 7px 0; }
                    .mutasi-search-button { display:flex; align-items:center; justify-content:center;
                        width:43px; min-width:43px; padding:0; border:1px solid #dce5f2;
                        border-right:0; border-radius:10px 0 0 10px; cursor:pointer;
                        color:#2563eb; touch-action:manipulation; }
                    .mutasi-search-button i { font-size:16px; color:#2563eb !important; }
                    .mutasi-search-button:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
                    #modalSpk #mobileItemNext { position:relative; z-index:20; touch-action:manipulation;
                        display:flex; align-items:center; justify-content:center; cursor:pointer; }
                    #modalSpk #mobileItemNext i { font-size:13px; }
                    #modalSpk #mobileItemNext:disabled { cursor:default; opacity:.35; }
                }
                @media (min-width:768px) { #mobileSearchClear { display:none !important; } }
            </style>
            {{-- Mobile timeline: read-only UI. Desktop editing is unchanged. --}}
            <style>
                @media (max-width: 767.98px) {
                    #modalSpk #timelineTable .mobile-readonly-notice {
                        margin: 0 0 9px; padding: 9px 11px; border-radius: 9px;
                        background: #eef5ff; color: #24518b; font-size: 11px;
                        border: 1px solid #dbeafe;
                    }
                    #modalSpk #timelineTable input:disabled,
                    #modalSpk #timelineTable select:disabled,
                    #modalSpk #timelineTable textarea:disabled {
                        background: #f5f7fb !important; color: #34465e !important;
                        -webkit-text-fill-color: #34465e; opacity: 1 !important;
                        border-color: #e2e8f0 !important; cursor: default;
                    }
                    #modalSpk #timelineTable #btnTambah,
                    #modalSpk #timelineTable #btnSave,
                    #modalSpk #timelineTable .hapus-row { display: none !important; }
                    #modalSpk #timelineTable > table th:last-child,
                    #modalSpk #timelineTable > table td:last-child { display: none !important; }
                }
            </style>
            <script>
                (function () {
                    const mobile = () => window.matchMedia('(max-width: 767.98px)').matches;
                    const timeline = () => document.getElementById('timelineTable');
                    function applyMobileReadonly() {
                        const root = timeline();
                        if (!root || !mobile()) return;
                        root.querySelectorAll('input, select, textarea').forEach(el => {
                            if (!el.disabled) el.disabled = true;
                        });
                        root.querySelectorAll('#btnTambah, #btnSave, .hapus-row').forEach(el => {
                            el.disabled = true;
                            el.setAttribute('aria-hidden', 'true');
                        });
                        if (root.querySelector('table') && !root.querySelector('.mobile-readonly-notice')) {
                            const info = document.createElement('div');
                            info.className = 'mobile-readonly-notice';
                            info.textContent = 'Mode lihat saja di HP. Untuk tambah, edit, atau hapus transaksi, gunakan desktop.';
                            root.prepend(info);
                        }
                    }
                    // Guard actions at capture phase, before existing jQuery handlers run.
                    document.addEventListener('click', function (e) {
                        if (!mobile()) return;
                        const target = e.target.closest('#timelineTable #btnTambah, #timelineTable #btnSave, #timelineTable .hapus-row');
                        if (target) { e.preventDefault(); e.stopImmediatePropagation(); }
                    }, true);
                    // Timeline HTML is regenerated by existing AJAX; reapply read-only on every render.
                    function init() {
                        const root = timeline();
                        if (!root) return;
                        new MutationObserver(applyMobileReadonly).observe(root, {childList:true, subtree:true});
                        applyMobileReadonly();
                        window.addEventListener('resize', applyMobileReadonly);
                    }
                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
                    else init();
                })();
            </script>
        @endsection
