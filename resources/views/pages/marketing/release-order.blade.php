@extends('master.master')
@section('title', "Release Order Marketing")
@section('content')

<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Release PFI</h2>
            <small>___</small>
        </div>
        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">

        <div class="box-body">
            <div class="box-header d-flex justify-content-between align-items-center">
                <input type="text"
                    id="search-qc"
                    class="form-control"
                    style="width:300px"
                    placeholder="Search PO / Item / Vendor">
            </div>
            <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
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
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="#"
                                        class="btn btn-primary btn-sm"
                                        id="btn-buat-spk">
                                        Buat SPK
                                    </a>
                                </div>
                            </div>
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


    <pre id="result"></pre>
    @endsection

    @push('scripts')
    <!-- jQuery (WAJIB PERTAMA) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (SETELAH jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>


    <script>
        document.getElementById('btn-show-form').addEventListener('click', function() {
            const form = document.getElementById('qc-form-wrapper');
            form.style.display = 'block';
            this.style.display = 'none'; // tombol hilang setelah diklik
        });
    </script>
    <script>
        $(document).ready(function() {
            const role = $('#role').val();
            if (role !== 'marketing') {
                $('#btn-save-all')
                    .prop('disabled', true)
                    .addClass('disabled');
            }
        });

        function loadPoTable(keyword = '') {
            fetch(`{{ route('marketing.ajax.po') }}?q=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('po-table-body');
                    tbody.innerHTML = '';

                    if (!data.length) {
                        tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada data
                            </td>
                        </tr>
                    `;
                        return;
                    }

                    data.forEach(po => {
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
                            </td>
                        </tr>
                    `;
                    });
                })
                .catch(err => console.error(err));
        }

        // load awal
        document.addEventListener('DOMContentLoaded', () => {
            loadPoTable();

            document.getElementById('search-qc')
                .addEventListener('keyup', function() {
                    loadPoTable(this.value);
                });
        });
        $('#btn-back').click(function() {

            $('#detail-view').hide();
            $('#default-table').show();

        });




        /* =====================================================
           VIEW DETAIL PO
        ===================================================== */
        $(document).on('click', '.btn-view', function() {

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
                        'no_', 'photo', 'description', 'article_nr_', 'article_nr_nw',
                        'remark', 'cushion', 'glass',
                        'item_w', 'item_d', 'item_h',
                        'pack_w', 'pack_d', 'pack_h',
                        'composition', 'finishing',
                        'qty', 'cbm', 'total_cbm',
                        'value_in_usd', 'fob_jakarta_in_usd'
                    ];

                    let keys = [
                        ...priority.filter(k => allKeys.includes(k)),
                        ...allKeys.filter(k => !priority.includes(k))
                    ];

                    /* ===== HEADER ===== */
                    let headerRow = $('<tr></tr>');
                    keys.forEach(k => {
                        headerRow.append(`<th>${k.replaceAll('_',' ').toUpperCase()}</th>`);
                    });
                    thead.append(headerRow);

                    /* ===== TOTAL ===== */
                    let totalCbm = 0;
                    let totalPrice = 0;

                    res.items.forEach(item => {
                        let d = item.detail || {};
                        totalCbm += Number(d.total_cbm || d.cbm || 0);
                        totalPrice += Number(d.value_in_usd || d.fob_jakarta_in_usd || 0);
                    });

                    let cbmIndex = keys.includes('total_cbm') ? keys.indexOf('total_cbm') : keys.indexOf('cbm');
                    let priceIndex = keys.includes('value_in_usd') ? keys.indexOf('value_in_usd') : keys.indexOf('fob_jakarta_in_usd');

                    /* ===== BODY ===== */
                    res.items.forEach(item => {

                        let detail = item.detail || {};
                        let row = $(`<tr class="editable-row" data-id="${item.id}"></tr>`);

                        keys.forEach(key => {

                            let value = detail[key] ?? '';
                            let td = $(`<td data-key="${key}"></td>`);

                            if (key.includes('photo') && typeof value === 'string' && value.startsWith('http')) {
                                td.html(`<img src="${value}" width="70" style="border-radius:6px">`);
                            } else {
                                td.html(`<span class="cell-text">${value}</span>`);
                            }

                            row.append(td);
                        });

                        tbody.append(row);
                    });

                    /* ===== FOOTER ===== */
                    foot.html(`
            <tr style="font-weight:bold;background:#f4f6f9">
                ${emptyTds(cbmIndex)}
                <td>TOTAL CBM</td>
                <td>${totalCbm.toFixed(3)}</td>
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

        function emptyTds(count) {
            return '<td></td>'.repeat(count);
        }
        /* =====================================================
           EDIT ROW
        ===================================================== */
        $(document).on('click', '.editable-row', function() {
            const role = $('#role').val();

            // ❌ BLOK JIKA BUKAN MARKETING
            if (role !== 'marketing') {
                return; // tidak masuk edit mode
            }

            let row = $(this);

            // keluar edit row lain
            $('.editable-row.editing').not(row).each(function() {
                exitEdit($(this));
            });

            if (row.hasClass('editing')) return;

            row.addClass('editing').css('background', '#fff8e1');

            row.find('td[data-key]').each(function() {

                let td = $(this);
                let key = td.data('key');

                // skip photo
                if (key && key.toLowerCase().includes('photo')) return;

                let text = td.find('.cell-text').text().trim();

                // ===== SIMPAN ORIGINAL VALUE =====
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
           SAVE ALL (HANYA YANG BERUBAH)
        ===================================================== */
        $('#btn-save-all').on('click', function() {
            const role = $('#role').val();

            if (role !== 'marketing') {
                alert('❌ Anda tidak memiliki akses untuk menyimpan data ini');
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

                    // ===== hanya kirim jika berubah =====
                    if (newVal !== oldVal) {
                        changedData[key] = newVal;
                    }

                });

                // ===== hanya push jika ada perubahan =====
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
                .catch(err => {
                    console.error(err);
                    alert('❌ Gagal menyimpan');
                });

        });


        /* =====================================================
           ENTER AUTO EXIT EDIT
        ===================================================== */
        $(document).on('keydown', '.inline-input', function(e) {

            if (e.key === 'Enter') {
                e.preventDefault();
                exitEdit($(this).closest('tr'));
            }

        });
    </script>
    <style>
        .freeze-wrapper {
            max-height: 600px;
            overflow: auto;
            position: relative;
            border: 1px solid #ddd;
        }

        /* ===== HEADER FREEZE ===== */
        #detail-table thead th {
            position: sticky;
            top: 0;
            background: #f4f6f9;
            /* WARNA HEADER */
            color: #333;
            z-index: 20;
            border-bottom: 2px solid #ccc;
        }

        /* kasih bayangan supaya keliatan pas scroll */
        #detail-table thead {
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }

        /* ================= COLUMN WIDTH ================= */
        #detail-table th:nth-child(1),
        #detail-table td:nth-child(1) {
            min-width: 60px;
        }

        #detail-table th:nth-child(2),
        #detail-table td:nth-child(2) {
            min-width: 90px;
        }

        #detail-table th:nth-child(3),
        #detail-table td:nth-child(3) {
            min-width: 280px;
        }

        /* ================= FREEZE COL 1 ================= */
        #detail-table th:nth-child(1),
        #detail-table td:nth-child(1) {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 8;
        }

        /* ================= FREEZE COL 2 ================= */
        #detail-table th:nth-child(2),
        #detail-table td:nth-child(2) {
            position: sticky;
            left: 60px;
            background: #fff;
            z-index: 8;
        }

        /* ================= FREEZE COL 3 ================= */
        #detail-table th:nth-child(3),
        #detail-table td:nth-child(3) {
            position: sticky;
            left: 150px;
            background: #fff;
            z-index: 8;
            box-shadow: 2px 0 6px rgba(0, 0, 0, .1);
        }

        /* header freeze priority */
        #detail-table thead th:nth-child(1),
        #detail-table thead th:nth-child(2),
        #detail-table thead th:nth-child(3) {
            z-index: 12;
        }

        #detail-table tbody tr:hover td {
            background: #f9f9f9;
        }
    </style>
    @endpush
