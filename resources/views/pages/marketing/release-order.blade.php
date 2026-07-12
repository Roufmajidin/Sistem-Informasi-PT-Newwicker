@extends('master.master')
@section('title', "Release Order Marketing")
@section('content')
@include('pages.spk.stylespk')
@include('pages.marketing.style')
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Release PFI</h2>
            <small>___</small>
        </div>
        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">
        <div class="box-body">
            <div class="box-header d-flex justify-content-between align-items-center flex-wrap gap-3">

                {{-- LEFT : SEARCH --}}
                <div style="max-width:350px; width:100%;">

                    <input type="text"
                        id="search-qc"
                        class="form-control"
                        placeholder="Search PO / Item / Vendor">

                </div>

                {{-- RIGHT : FILTER --}}
                <div style="width:180px;">

                    <select id="filter-spk-type"
                        class="form-control">

                        <option value="">
                            Semua
                        </option>

                        <option value="NW">
                            NW
                        </option>

                        <option value="NWS">
                            NWS
                        </option>

                    </select>

                </div>

            </div>
            <div class="col-12 d-flex justify-content-end">
                <a href="/semua-spk"
                    class="btn btn-primary btn-sm">
                    All SPK
                </a>
            </div>
            <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr class="spk-header">
                                        <th>Order No</th>
                                        <th>Company Name</th>
                                        <th>Shipment Date</th>
                                        <th>Country</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="po-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="detail-view" style="display:none;">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-body">
                            <button class="btn btn-default btn-sm" id="btn-back">
                                ← Show All Order Release
                            </button>
                            <hr>
                            @php
                            $user = auth()->user();
                            $divisi = optional($user->karyawan->divisi)->nama;
                            @endphp
                            @if ($divisi == 'PURCHASING')
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="#"
                                        class="btn btn-primary btn-sm"
                                        id="btn-buat-spk">
                                        Buat SPK
                                    </a>
                                </div>
                            </div>
                            @endif
                            <table class="table table-bordered">
                                <tr>
                                    <td width="200"><b>Order No</b></td>
                                    <td id="d-order"></td>
                                </tr>
                                <tr>
                                    <td><b>Company Name</b></td>
                                    <td id="d-company"></td>
                                </tr>
                                <tr>
                                    <td><b>Shipment Date</b></td>
                                    <td id="d-ship"></td>
                                </tr>
                                <tr>
                                    <td><b>Country</b></td>
                                    <td id="d-country"></td>
                                </tr>
                            </table>
                        </div>
                        <button id="btn-save-all"
                            class="btn btn-success btn-sm">
                            💾 Save All Changes
                        </button>
                        <div class="freeze-wrapper">
                            <table class="table table-bordered table-striped" id="detail-table">
                                <thead id="detail-table-head"></thead>
                                <tbody id="detail-item-table"></tbody>
                                <tfoot id="detail-table-foot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CHAT MODAL -->
    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Chat Room</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="chat-box" style="height:400px; overflow:auto; background:#f5f5f5; padding:10px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="chat-room-id">
                    <div class="input-group">
                        <input type="text" id="chat-input" class="form-control" placeholder="Type message...">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" id="btn-send-chat">Send</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <pre id="result"></pre>
    @push('scripts')
    <script>
const currentUsername = @json(auth()->user()->name);
</script>
    <script>
        const CURRENT_USER_ID = {{    auth() -> id()   }};
    </script>
    @push('scripts')
    <script>
        /* =====================================================
   INIT PAGE (WAJIB UNTUK PJAX)
===================================================== */
        function initPage() {
            console.log('INIT PAGE JALAN');
            /* ===== ROLE ===== */
            const role = $('#role').val();
            if (role !== 'marketing') {
                $('#btn-save-all')
                    .prop('disabled', true)
                    .addClass('disabled');
            }
            /* ===== LOAD TABLE ===== */
            loadPoTable();
            $('#search-qc')
                .off('keyup')
                .on('keyup', function() {

                    loadPoTable(
                        this.value,
                        $('#filter-spk-type').val()
                    );

                });
            /* ===== BACK BUTTON ===== */
            $('#btn-back')
                .off('click')
                .on('click', function() {
                    $('#detail-view').hide();
                    $('#default-table').show();
                });
            $('#filter-spk-type')
                .off('change')
                .on('change', function() {

                    loadPoTable(
                        $('#search-qc').val(),
                        $(this).val()
                    );

                });
        }
        /* =====================================================
           LOAD TABLE
        ===================================================== */
        function loadPoTable(keyword = '', type = '') {
            fetch(
                    `{{ route('marketing.ajax.po') }}?q=${encodeURIComponent(keyword)}&type=${encodeURIComponent(type)}`
                )
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('po-table-body');

                    if (!tbody) return;
                    tbody.innerHTML = '';
                    if (!data.length) {
                        tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada data
                        </td>
                    </tr>`;
                        return;
                    }
                    data.forEach(po => {
                        let deleteButton = '';

    if (currentUsername.toLowerCase() === 'rodiyah') {
        deleteButton = `
            <button
                class="btn btn-danger btn-xs btn-delete-po"
                data-id="${po.id}">
                Delete
            </button>
        `;
    }
                        tbody.innerHTML += `
                    <tr>
                        <td>${po.order_no}</td>
                        <td>${po.company_name}</td>
                        <td>${po.shipment_date ?? '-'}</td>
                        <td>${po.country ?? '-'}</td>
                        <td>
                            <button class="btn btn-xs btn-success btn-view"
                                data-id="${po.id}">
                                View
                            </button>
                                           ${deleteButton}

                        </td>
                    </tr>`;
                    });
                })
                .catch(err => console.error(err));
        }
        /* =====================================================
           VIEW DETAIL
        ===================================================== */
        // delete
        $(document).off('click', '.btn-delete-po')
.on('click', '.btn-delete-po', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Yakin hapus?',
        text: 'Data PO dan detail akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
    })
    .then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({

            url: '/marketing/po-delete/' + id,
            type: 'DELETE',

            headers: {
                'X-CSRF-TOKEN':
                    $('meta[name="csrf-token"]').attr('content')
            },

            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                });

                loadPoTable();

            },

            error: function (xhr) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Delete gagal'
                });

            }

        });

    });

});
        $(document).off('click', '.btn-view').on('click', '.btn-view', function() {
            let id = $(this).data('id');
            $('#btn-buat-spk').attr('href', '/spk/' + id);
            fetch(`/marketing/po-detail/${id}`)
                .then(res => res.json())
                .then(res => {
                    $('#default-table').hide();
                    $('#detail-view').show();
                    $('#d-order').text(res.po.order_no);
                    $('#d-company').text(res.po.company_name);
                    $('#d-ship').text(res.po.shipment_date ?? '-');
                    $('#d-country').text(res.po.country ?? '-');
                    let thead = $('#detail-table-head');
                    let tbody = $('#detail-item-table');
                    let foot = $('#detail-table-foot');
                    thead.html('');
                    tbody.html('');
                    foot.html('');
                    if (!res.items.length) return;
                    let firstDetail = res.items[0].detail || {};
                    let allKeys = Object.keys(firstDetail);
                    let priority = [
                        'no_', 'photo', 'description', 'article_nr_', 'article_nr_nw', 'nw_code', 'sub_category',
                        'qty', 'remark', 'cushion', 'glass',
                        'item_w', 'item_d', 'item_h',
                        'pack_w', 'pack_d', 'pack_h',
                        'composition', 'finishing',
                        'cbm', 'total_cbm',
                        'value_in_usd', 'fob_jakarta_in_usd'
                    ];
                    const role = $('#role').val();
                    // =========================
                    // BUILD KEYS
                    // =========================
                    let keys = [
                        ...priority.filter(k => allKeys.includes(k)),
                        ...allKeys.filter(k => !priority.includes(k))
                    ];
                    // =========================
                    // FILTER ROLE
                    // =========================
                    if (role !== 'marketing') {
                        keys = keys.filter(k =>
                            k !== 'value_in_usd' &&
                            k !== 'fob_jakarta_in_usd'
                        );
                    }
                    // =========================
                    // INSERT ACT COLUMN BEFORE QTY
                    // =========================
                    let qtyIndex = keys.indexOf('qty');
                    if (qtyIndex !== -1) {
                        keys.splice(qtyIndex, 0, 'act');
                    }
                    // =========================
                    // INDEX
                    // =========================
                    let descIndex = keys.indexOf('description');
                    let cbmIndex = keys.includes('total_cbm') ?
                        keys.indexOf('total_cbm') :
                        keys.indexOf('cbm');
                    let priceIndex = keys.includes('value_in_usd') ?
                        keys.indexOf('value_in_usd') :
                        keys.indexOf('fob_jakarta_in_usd');
                    // =========================
                    // HEADER
                    // =========================
                    let headerRow = $('<tr></tr>');
                    keys.forEach((k, i) => {
                        let cls = (i === descIndex) ? 'sticky-col' : '';
                        if (k === 'act') {
                            headerRow.append(`<th class="${cls}">ACT</th>`);
                        } else {
                            headerRow.append(`<th class="${cls}">${k.replaceAll('_',' ').toUpperCase()}</th>`);
                        }
                    });
                    thead.append(headerRow);
                    // =========================
                    // TOTAL
                    // =========================
                    let totalCbm = 0;
                    let totalPrice = 0;
                    // =========================
                    // BODY
                    // =========================
                    res.items.forEach(item => {
                        let detail = item.detail || {};
                        let row = $(`<tr class="editable-row" data-id="${item.id}"></tr>`);
                        let rawCode =
                            detail.article_code ||
                            detail.article_nr_ ||
                            detail.nw_code ||
                            '';
                        let articleCode = encodeURIComponent(rawCode);
                        keys.forEach((key, i) => {
                            let cls = (i === descIndex) ? 'sticky-col' : '';
                            // 🔥 ACT COLUMN
                            if (key === 'act') {
                                let rawCode =
                                    // detail.article_code ||
                                    detail.article_nr_ ||
                                    detail.nw_code ||
                                    '';
                                let articleCode = encodeURIComponent(rawCode);
                                row.append(`
        <td class="${cls}">
            <a href="/cad/${articleCode}" class="btn btn-xs btn-primary">CAD</a>
          <button class="btn btn-xs btn-warning btn-chat"
    data-id="${item.id}">
    CHAT
</button>
        </td>
    `);
                                return;
                            }
                            let value = detail[key] ?? '';
                            let td = $(`<td class="${cls}" data-key="${key}"></td>`);
                            // FORMAT CBM
                            if ((key === 'cbm' || key === 'total_cbm') && value !== '') {
                                value = isNaN(value) ? '0.00' : parseFloat(value).toFixed(2);
                                totalCbm += parseFloat(value);
                            }
                            // FORMAT PRICE
                            if (key === 'value_in_usd' || key === 'fob_jakarta_in_usd') {
                                totalPrice += parseFloat(value || 0);
                            }
                            // IMAGE
                            if (key.includes('photo') && typeof value === 'string' && value.startsWith('http')) {
                                td.html(`<img src="${value}" width="70">`);
                            } else {
                                td.html(`<span class="cell-text">${value}</span>`);
                            }
                            row.append(td);
                        });
                        tbody.append(row);
                    });
                    // =========================
                    // FOOTER
                    // =========================
                    foot.html(`
                <tr style="font-weight:bold;background:#f4f6f9">
                    ${emptyTds(cbmIndex)}
                    <td>TOTAL CBM</td>
                    <td>${isNaN(totalCbm) ? '0.00' : totalCbm.toFixed(2)}</td>
                    ${emptyTds(keys.length - cbmIndex - 2)}
                </tr>
                <tr style="font-weight:bold;background:#e8f5e9">
                    ${emptyTds(priceIndex)}
                    <td>TOTAL FOB PRICE</td>
                    <td>${totalPrice.toLocaleString('id-ID')}</td>
                    ${emptyTds(keys.length - priceIndex - 2)}
                </tr>
            `);
                });
        });
        /* =====================================================
           EDIT ROW (AMAN PJAX)
        ===================================================== */
        $(document).off('click', '.editable-row').on('click', '.editable-row', function() {
            const role = $('#role').val();
            if (role !== 'marketing') return;
            let row = $(this);
            $('.editable-row.editing').not(row).each(function() {
                exitEdit($(this));
            });
            if (row.hasClass('editing')) return;
            row.addClass('editing').css('background', '#fff8e1');
            row.find('td[data-key]').each(function() {
                let td = $(this);
                let key = td.data('key');
                if (key && key.toLowerCase().includes('photo')) return;
                let text = td.find('.cell-text').text().trim();
                td.attr('data-original', text);
                td.html(`
            <input type="text"
                class="form-control form-control-sm inline-input"
                value="${text}">
        `);
            });
        });
        /* =====================================================
           EXIT EDIT
        ===================================================== */
        function exitEdit(row) {
            row.removeClass('editing').css('background', '');
            row.find('td[data-key]').each(function() {
                let td = $(this);
                let input = td.find('input');
                if (!input.length) return;
                let val = input.val();
                td.html(`<span class="cell-text">${val}</span>`);
            });
        }
        /* =====================================================
           SAVE
        ===================================================== */
        $(document).off('click', '#btn-save-all').on('click', '#btn-save-all', function() {
            const role = $('#role').val();
            if (role !== 'marketing') {
                alert('❌ Tidak ada akses');
                return;
            }
            let payload = [];
            $('#detail-item-table tr.editable-row').each(function() {
                let row = $(this);
                let itemId = row.data('id');
                let changedData = {};
                row.find('td[data-key]').each(function() {
                    let td = $(this);
                    let key = td.data('key');
                    if (!key || key.toLowerCase().includes('photo')) return;
                    let newVal = td.find('input').length ?
                        td.find('input').val().trim() :
                        td.find('.cell-text').text().trim();
                    let oldVal = td.attr('data-original') ?? '';
                    if (newVal !== oldVal) {
                        changedData[key] = newVal;
                    }
                });
                if (Object.keys(changedData).length > 0) {
                    payload.push({
                        id: itemId,
                        detail: changedData
                    });
                }
            });
            if (!payload.length) {
                alert('Tidak ada perubahan');
                return;
            }
            fetch('/marketing/po-item-update-bulk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        items: payload
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert('✅ Data berhasil disimpan');
                        $('.editable-row.editing').each(function() {
                            exitEdit($(this));
                        });
                    }
                })
                .catch(() => alert('❌ Gagal menyimpan'));
        });
        /* =====================================================
           ENTER = EXIT EDIT
        ===================================================== */
        $(document).off('keydown', '.inline-input').on('keydown', '.inline-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                exitEdit($(this).closest('tr'));
            }
        });
        /* =====================================================
           HELPER
        ===================================================== */
        function emptyTds(count) {
            return '<td></td>'.repeat(count);
        }
        /* =====================================================
           TRIGGER
        ===================================================== */
        $(document).ready(initPage);
        $(document).on('pjax:end', initPage);
    </script>

    @endpush
    @endpush
    @endsection
