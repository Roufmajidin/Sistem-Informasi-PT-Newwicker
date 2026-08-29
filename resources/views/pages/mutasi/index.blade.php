@extends('master.master')

@section('title', 'Mutasi Barang')

@section('content')

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

                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-secondary"></i>
                        </span>

                        <input type="text" id="searchSpk" class="form-control border-start-0"
                            placeholder="Cari PO, Buyer, Supplier, Item...">

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

                            <tr class="pilih-spk"
                                data-id="{{ $spk->id }}"
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

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Item</label>

                            <select id="itemSelect" class="form-select">
                                <option>Pilih Item</option>
                            </select>
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
                $(document).on('click', '.pilih-spk', function() {
                    resetModal();

                    currentSpkId = $(this).data('id');
                    $.ajax({

                        url: "/mutasi/" + currentSpkId,

                        type: "GET",

                        success: function(res) {
                            supplierName = res.supplier;
                            currentSupId = res.sup_id;
                            kategoriSpk = res.kategori;

                            console.log(res);

                            $('#judulSpk').text(res.no_spk);

                            items = res.items;

                            let html = '<option value="">Pilih Item</option>';

                            $.each(res.items, function(i, item) {

                                html += `
                    <option value="${item.detail_po_id}" data-index="${i}">
                        ${item.kode} - ${item.nama} (${item.qty} ${item.satuan})
                    </option>
                `;

                            });
                            $('#modalSpk').modal('show');

                            $('#itemSelect').html(html);

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

                        if (type === 'in' || type === 'service_masuk') {
                            qtyIn += Number(row.qty || 0);
                        }

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
                        if (
                            type !== 'in' &&
                            type !== 'service_masuk'
                        ) {
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
                                qtyIn += qty;
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
                            qtyIn += qty;
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

                        if (!componentName) {
                            componentName = `Komponen ${index + 1}`;
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
                                        ${components.length} komponen
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


                                <div class="component-note">
                                    <i class="fas fa-circle-info"></i>
                                    Qty In setiap komponen mengikuti
                                    <strong>Remark</strong> pada timeline.
                                </div>

                            </div>

                        </div>

                    `);

                }


                $('#itemSelect').on('change', function() {

                    let detailPoId = $(this).val();

                    let index = $(this).find(':selected').data('index');

                    let item = items[index];
                    console.log(item)
                    if (!item) return;

                    /*
                    |--------------------------------------------------------------------------
                    | DETAIL ITEM
                    |--------------------------------------------------------------------------
                    | Detail komponen (TOP/BOTTOM/dll) akan dirender setelah timeline
                    | selesai diambil supaya Qty In bisa ikut ditampilkan.
                    */

                    renderItemInfo(item, detailPoId, 0);

                    // AJAX kedua
                    $.ajax({
                        url: '/mutasi/timeline/detail',
                        type: 'GET',
                        data: {
                            spk_id: currentSpkId,
                            detail_po_id: detailPoId
                        },
                        success: function(res) {

                            let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="160">Tanggal</th>
                            <th width="100">Jam</th>
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
                                let jam = datetime[1] ?? '';

                                html += `
                    <tr data-id="${row.id}">

                        <td>${i+1}</td>

                        <td>
                            <input type="date"
                                class="form-control form-control-sm tanggal"
                                value="${tanggal}">
                        </td>

                        <td>
                            <input type="time"
                                class="form-control form-control-sm jam"
                                value="${jam}">
                        </td>

                        <td>
                            <select class="form-select form-select-sm type">

                                <option value="in" ${row.type=='in'?'selected':''}>Masuk</option>


                                <option value="kirim_rangka" ${row.type=='kirim_rangka'?'selected':''}>Kirim Rangka</option>


                                <option value="service_masuk" ${row.type=='service_masuk'?'selected':''}>Service</option>
                                <option value="service_keluar" ${row.type=='service_keluar'?'selected':''}>Service Keluar</option>


                            </select>
                        </td>



                        <td>
                            <input type="number"
                                class="form-control form-control-sm qty"
                                value="${row.qty}">
                        </td>
                            <td>
                            <input type="text"
                                class="form-control form-control-sm remark"
                                value="${row.remark}">
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
                            console.log(xhr.responseText);
                        }
                    });

                });
                // add rw
                $(document).on('click', '#btnTambah', function() {

                    let no = $('#tbodyTimeline tr').length + 1;

                    $('#tbodyTimeline').append(`
        <tr data-id="">

            <td>${no}</td>

            <td>
                <input type="date" class="form-control form-control-sm tanggal">
            </td>

            <td>
                <input type="time" class="form-control form-control-sm jam">
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

                <button class="btn btn-danger btn-sm hapus-row">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>
    `);

                });
                // hapus row
                $(document).on('click', '.hapus-row', function() {

                    $(this).closest('tr').remove();

                });
                // save data
                $(document).on('click', '#btnSave', function() {

                    let rows = [];

                    $('#tbodyTimeline tr').each(function() {

                        rows.push({

                            id: $(this).data('id'),

                            spk_id: currentSpkId,

                            detail_po_id: $('#itemSelect').val(),

                            sup_id: currentSupId,

                            qty: $(this).find('.qty').val(),

                            type: $(this).find('.type').val(),

                            remark: $(this).find('.remark').val(),

                            date: $(this).find('.tanggal').val(),

                            time: $(this).find('.jam').val()

                        });

                    });

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

                            $('#modalSpk').modal('hide');

                        },

                        error: function(xhr) {

                            console.log(xhr.responseText);

                        }

                    });

                });

                function resetModal() {

                    items = [];
                    currentSpkId = null;
                    currentSupId = null;
                    supplierName = '';

                    $('#judulSpk').text('Detail SPK');

                    $('#itemSelect').html('<option value="">Pilih Item</option>');

                    $('#itemInfo').empty();

                    $('#timelineTable').empty();

                    // Reset select ke option pertama
                    $('#itemSelect').prop('selectedIndex', 0);

                }

                $('#modalSpk').on('hidden.bs.modal', function() {

                    resetModal();

                });
                $('#searchSpk').on('keyup', function() {

                    let keyword = $(this).val().toLowerCase().trim();

                    $('#tbodySpk tr.pilih-spk').each(function() {

                        let search = ($(this).data('search') || '').toLowerCase();

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

                (function () {

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

                    let activeRow = null;

                    let hoverTimer = null;

                    let requestNo = 0;


                    function escapeHtml(value) {

                        const div =
                            document.createElement('div');

                        div.textContent =
                            value == null
                                ? ''
                                : String(value);

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
                            let i = 0;
                            i < binary.length;
                            i++
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
                                let i = 0;
                                i < binary.length;
                                i++
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


                        return Array.isArray(items)
                            ? items
                            : [];

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


                        items.forEach(
                            function (item, index) {

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


                                html += `
                                    <tr>

                                        <td>
                                            ${index + 1}
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

                        activeRow =
                            row;


                        clearTimeout(
                            hoverTimer
                        );


                        hoverTimer =
                            setTimeout(
                                function () {

                                    if (
                                        activeRow !==
                                        row
                                    ) {
                                        return;
                                    }


                                    const spkId =
                                        row.getAttribute(
                                            'data-id'
                                        );


                                    if (!spkId) {
                                        return;
                                    }


                                    let items;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | CACHE
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        cache[spkId]
                                    ) {

                                        render(
                                            cache[spkId]
                                        );

                                    } else {

                                        /*
                                        | Ambil items langsung
                                        | dari Blade.
                                        */

                                        items =
                                            decodeItems(
                                                row
                                            );


                                        /*
                                        | Tambahkan qty_in = 0
                                        | supaya tabel langsung tampil.
                                        */

                                        items =
                                            items.map(
                                                function (item) {

                                                    const copy =
                                                        Object.assign(
                                                            {},
                                                            item
                                                        );

                                                    copy.qty_in =
                                                        0;

                                                    return copy;

                                                }
                                            );


                                        render(
                                            items
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | FETCH TIMELINE
                                        |--------------------------------------------------------------------------
                                        */

                                        const thisRequest =
                                            ++requestNo;


                                        let pending =
                                            items.length;


                                        if (!pending) {
                                            return;
                                        }


                                        items.forEach(
                                            function (item, index) {

                                                const detailPoId =
                                                    item.detail_po_id;


                                                if (!detailPoId) {

                                                    pending--;

                                                    return;

                                                }


                                                /*
                                                | Native fetch.
                                                |
                                                | TIDAK ADA:
                                                | $.ajax
                                                | .catch
                                                */

                                                fetch(
                                                    '/mutasi/timeline/detail?spk_id=' +
                                                    encodeURIComponent(spkId) +
                                                    '&detail_po_id=' +
                                                    encodeURIComponent(detailPoId),
                                                    {
                                                        method: 'GET',
                                                        credentials: 'same-origin',
                                                        headers: {
                                                            'Accept':
                                                                'application/json',
                                                            'X-Requested-With':
                                                                'XMLHttpRequest'
                                                        }
                                                    }
                                                )
                                                .then(
                                                    function (response) {

                                                        return response.json();

                                                    }
                                                )
                                                .then(
                                                    function (res) {

                                                        let qtyIn =
                                                            0;


                                                        /*
                                                        | Response Anda:
                                                        |
                                                        | timeline:
                                                        | 39 in
                                                        | 21 in
                                                        |
                                                        | hasil = 60
                                                        */

                                                        (res.timeline || [])
                                                            .forEach(
                                                                function (timelineRow) {

                                                                    const type =
                                                                        String(
                                                                            timelineRow.type ||
                                                                            ''
                                                                        )
                                                                        .trim()
                                                                        .toLowerCase();


                                                                    if (
                                                                        type ===
                                                                        'in'
                                                                    ) {

                                                                        qtyIn +=
                                                                            Number(
                                                                                timelineRow.qty ||
                                                                                0
                                                                            );

                                                                    }


                                                                    /*
                                                                    | Service masuk
                                                                    | juga dihitung sebagai IN,
                                                                    | mengikuti logic existing.
                                                                    */

                                                                    if (
                                                                        type ===
                                                                        'service_masuk'
                                                                    ) {

                                                                        qtyIn +=
                                                                            Number(
                                                                                timelineRow.qty ||
                                                                                0
                                                                            );

                                                                    }

                                                                }
                                                            );


                                                        items[index]
                                                            .qty_in =
                                                                qtyIn;


                                                        /*
                                                        | Jangan render jika
                                                        | mouse sudah pindah.
                                                        */

                                                        if (
                                                            thisRequest ===
                                                            requestNo
                                                        ) {

                                                            render(
                                                                items
                                                            );

                                                            movePreviewFromRow();

                                                        }


                                                        pending--;

                                                        if (
                                                            pending <= 0 &&
                                                            thisRequest ===
                                                            requestNo
                                                        ) {

                                                            cache[spkId] =
                                                                items;

                                                        }

                                                    }
                                                );

                                            }
                                        );

                                    }


                                    preview.style.display =
                                        'block';


                                    preview.setAttribute(
                                        'aria-hidden',
                                        'false'
                                    );


                                    movePreview(
                                        event
                                    );


                                },
                                120
                            );

                    }


                    function movePreviewFromRow() {

                        if (!activeRow) {
                            return;
                        }


                        const rect =
                            activeRow.getBoundingClientRect();


                        const event =
                            {
                                clientX:
                                    rect.right,

                                clientY:
                                    rect.top
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
                        function (event) {

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
                        function (event) {

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
                        function (event) {

                            if (!activeRow) {
                                return;
                            }


                            const fromRow =
                                event.target.closest
                                    ?
                                    event.target.closest(
                                        'tr.pilih-spk'
                                    )
                                    :
                                    null;


                            const toRow =
                                event.relatedTarget &&
                                event.relatedTarget.closest
                                    ?
                                    event.relatedTarget.closest(
                                        'tr.pilih-spk'
                                    )
                                    :
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
                        function () {

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

                #itemSelect {
                    max-width: 100%;
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
            </style>
        @endsection
