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

                            <tr class="pilih-spk" data-id="{{ $spk->id }}" style="cursor:pointer"
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


                function getComponentQtyInFromTimeline(timeline, componentName) {

                    const target = normalizeComponentText(componentName);

                    if (!target) {
                        return 0;
                    }

                    let qtyIn = 0;

                    $.each(timeline || [], function(i, row) {

                        const type = String(row.type || '').toLowerCase();

                        /*
                        | Hanya mutasi masuk yang dihitung sebagai Qty In.
                        | Service Keluar / Kirim Rangka tidak menambah Qty In.
                        */

                        if (
                            type !== 'in' &&
                            type !== 'service_masuk'
                        ) {
                            return;
                        }

                        const remark = normalizeComponentText(row.remark);

                        if (!remark) {
                            return;
                        }

                        /*
                        | Remark menjadi penghubung antara timeline
                        | dengan komponen TOP / BOTTOM.
                        |
                        | Contoh:
                        | component = "BOTTOM TRIPLEK 12MM"
                        | remark    = "BOTTOM TRIPLEK 12"
                        |
                        | Tetap dianggap cocok.
                        */

                        const matched =
                            remark === target ||
                            remark.includes(target) ||
                            target.includes(remark);

                        if (matched) {
                            qtyIn += Number(row.qty || 0);
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

                        $.each(component, function(key, value) {

                            if (
                                !componentName &&
                                typeof value === 'string' &&
                                value.trim() &&
                                ![
                                    'harga',
                                    'material',
                                    'pcs',
                                    'set',
                                    'total',
                                    'p',
                                    'l',
                                    't'
                                ].includes(String(key).toLowerCase())
                            ) {
                                componentName = value;
                            }

                        });

                        if (!componentName) {

                            componentName =
                                component.material ||
                                component.nama ||
                                `Komponen ${index + 1}`;

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
                                componentName
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

            <style>
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
