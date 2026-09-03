@extends('master.master')

@section('content')

<style>
    .purchasing-page {
        padding: 20px;
        background: #f5f7fa;
        min-height: calc(100vh - 70px);
    }

    .purchasing-card {
        background: #fff;
        border: 1px solid #dce2e8;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        overflow: hidden;
    }

    .page-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #263445;
    }

    .page-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: #7b8794;
    }

    .search-section {
        padding: 20px 22px;
        background: #fafbfc;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-title,
    .table-title {
        font-size: 14px;
        font-weight: 700;
        color: #263445;
        margin-bottom: 12px;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        height: 42px;
        padding-left: 42px;
        padding-right: 45px;
        border: 1px solid #ccd4dd;
        border-radius: 7px;
        font-size: 13px;
        width: 100%;
        background: #fff;
    }

    .search-box input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.10);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 12px;
        color: #7b8794;
    }

    .search-button {
        position: absolute;
        right: 5px;
        top: 5px;
        height: 32px;
        width: 32px;
        border: 0;
        border-radius: 5px;
        background: #1f4e78;
        color: #fff;
    }

    .search-result {
        margin-top: 8px;
        display: none;
        background: #fff;
        border: 1px solid #dce2e8;
        border-radius: 7px;
        max-height: 260px;
        overflow-y: auto;
    }

    .search-result.show {
        display: block;
    }

    .material-result {
        padding: 11px 14px;
        border-bottom: 1px solid #edf0f3;
        cursor: pointer;
        transition: .15s;
    }

    .material-result:last-child {
        border-bottom: 0;
    }

    .material-result:hover {
        background: #f1f6fb;
    }

    .material-code {
        font-size: 12px;
        font-weight: 700;
        color: #263445;
    }

    .material-name {
        font-size: 12px;
        color: #5f6b76;
        margin-top: 2px;
    }

    .material-stock {
        font-size: 11px;
        margin-top: 5px;
        color: #6b7280;
    }

    .stock-danger {
        color: #dc3545 !important;
    }

    .stock-warning {
        color: #d97706 !important;
    }

    .stock-success {
        color: #198754 !important;
    }

    .selected-material {
        display: none;
        margin-top: 15px;
        padding: 15px;
        border: 1px solid #cfd8e3;
        border-radius: 8px;
        background: #fff;
    }

    .selected-material.show {
        display: block;
    }

    .selected-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .selected-name {
        font-size: 14px;
        font-weight: 700;
        color: #263445;
    }

    .selected-code {
        font-size: 11px;
        color: #7b8794;
        margin-top: 3px;
    }

    .stock-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .stock-box {
        padding: 11px 13px;
        border: 1px solid #e0e5eb;
        border-radius: 6px;
        background: #fafbfc;
    }

    .stock-label {
        font-size: 10px;
        color: #7b8794;
        margin-bottom: 4px;
    }

    .stock-value {
        font-size: 16px;
        font-weight: 700;
        color: #263445;
    }

    .request-input {
        margin-top: 15px;
        display: grid;
        grid-template-columns: 180px 1fr auto;
        gap: 10px;
        align-items: end;
    }

    .form-label-custom {
        font-size: 11px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 5px;
        display: block;
    }

    .form-control-custom,
    .form-select-custom {
        height: 38px;
        border: 1px solid #ccd4dd;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 12px;
        width: 100%;
        background: #fff;
    }

    .btn-add-material {
        height: 38px;
        padding: 0 16px;
        border: 0;
        border-radius: 6px;
        background: #198754;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-add-material:hover {
        background: #157347;
    }

    .request-info {
        padding: 15px 22px;
        border-bottom: 1px solid #e5e7eb;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }

    .info-box label {
        display: block;
        font-size: 10px;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .info-box input,
    .info-box select {
        width: 100%;
        height: 34px;
        border: 1px solid #ccd4dd;
        border-radius: 5px;
        padding: 5px 9px;
        font-size: 11px;
    }

    .table-section {
        padding: 20px 22px;
    }

    .request-table-wrapper {
        overflow-x: auto;
        border: 1px solid #cfd6dd;
    }

    .request-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1150px;
        font-size: 11px;
    }

    .request-table th {
        background: #293b4d;
        color: #fff;
        padding: 9px 7px;
        border: 1px solid #1f2e3b;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .request-table td {
        border: 1px solid #cfd6dd;
        padding: 6px 7px;
        height: 38px;
        vertical-align: middle;
    }

    .request-table tbody tr:hover {
        background: #f8fafc;
    }

    .request-table input,
    .request-table select {
        width: 100%;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 11px;
        min-width: 70px;
    }

    .request-table input:focus,
    .request-table select:focus {
        background: #eef5ff;
    }

    .no-column {
        width: 40px;
        text-align: center;
    }

    .material-column {
        min-width: 200px;
    }

    .supplier-column {
        min-width: 150px;
    }

    .payment-column {
        min-width: 120px;
    }

    .description-column {
        min-width: 180px;
    }

    .warehouse-column {
        min-width: 140px;
    }

    .qty-column {
        width: 70px;
    }

    .unit-column {
        width: 70px;
    }

    .price-column {
        width: 120px;
    }

    .total-column {
        width: 130px;
    }

    .status-column {
        width: 80px;
    }

    .remove-row {
        color: #dc3545;
        cursor: pointer;
        border: 0;
        background: transparent;
        font-size: 13px;
    }

    .empty-request {
        text-align: center;
        padding: 35px 15px !important;
        color: #9ca3af;
    }

    .empty-request i {
        font-size: 30px;
        display: block;
        margin-bottom: 8px;
        color: #cbd5e1;
    }

    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }

    .btn-add-row {
        border: 1px solid #198754;
        color: #198754;
        background: #fff;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
    }

    .btn-add-row:hover {
        background: #198754;
        color: #fff;
    }

    .stock-rule {
        margin-top: 12px;
        padding: 10px 12px;
        border: 1px solid #f6d98b;
        background: #fff8e5;
        border-radius: 6px;
        font-size: 11px;
        color: #6b4f00;
    }

    @media(max-width: 768px) {
        .request-info,
        .stock-info,
        .request-input {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="purchasing-page">

    <div class="purchasing-card">

        {{-- HEADER --}}
        <div class="page-header">
            <div>
                <h4 class="page-title">
                    Pengajuan Barang Inventory
                </h4>
                <div class="page-subtitle">
                    Pengajuan Barang / Buat Pengajuan
                </div>
            </div>

            <button type="button"
                    class="btn btn-primary btn-sm"
                    id="btnSaveRequest">
                <i class="fa fa-save"></i>
                Simpan Draft
            </button>
        </div>

        {{-- INFORMASI PENGAJUAN --}}
        <div class="request-info">

            <div class="info-box">
                <label>Tanggal Pengajuan</label>
                <input type="date"
                       id="requestDate"
                       value="{{ date('Y-m-d') }}">
            </div>

            <div class="info-box">
                <label>Departemen / Bagian</label>
                <select id="department">
                    <option value="">-- Pilih Departemen --</option>
                    <option>Purchasing</option>
                    <option>Production</option>
                    <option>Warehouse</option>
                    <option>Finishing</option>
                    <option>QC</option>
                    <option>Maintenance</option>
                </select>
            </div>

            <div class="info-box">
                <label>Needed By Date</label>
                <input type="date" id="neededDate">
            </div>

        </div>

        {{-- SEARCH INVENTORY --}}
        <div class="search-section">

            <div class="search-title">
                <i class="fa fa-search"></i>
                Cari & Cek Barang di Gudang
            </div>

            <div class="search-box">

                <i class="fa fa-search search-icon"></i>

                <input type="text"
                       id="materialSearch"
                       autocomplete="off"
                       placeholder="Cari kode atau nama barang di gudang...">

                <button type="button"
                        class="search-button"
                        id="btnSearchMaterial">
                    <i class="fa fa-search"></i>
                </button>

            </div>

            <div id="searchResult" class="search-result"></div>

            {{-- BARANG TERPILIH --}}
            <div id="selectedMaterial" class="selected-material">

                <div class="selected-header">

                    <div>
                        <div class="selected-name"
                             id="selectedMaterialName">
                            -
                        </div>

                        <div class="selected-code"
                             id="selectedMaterialCode">
                            -
                        </div>
                    </div>

                    <span class="badge bg-info"
                          id="selectedUnit">
                        -
                    </span>

                </div>

                <div class="stock-info">

                    <div class="stock-box">
                        <div class="stock-label">Stok Gudang</div>
                        <div class="stock-value"
                             id="warehouseStock">
                            0
                        </div>
                    </div>

                    <div class="stock-box">
                        <div class="stock-label">-</div>
                        {{-- <div class="stock-value"
                             id="requestQtyDisplay">
                            0
                        </div> --}}
                    </div>

                    <div class="stock-box">
                        <div class="stock-label">Status Stock</div>
                        <div class="stock-value"
                             id="stockStatus">
                            Belum dicek
                        </div>
                    </div>

                </div>

                <div class="request-input">

                    <div>
                        <label class="form-label-custom">
                            Qty Kebutuhan
                        </label>

                        <input type="number"
                               min="0.01"
                               step="0.01"
                               id="requestQty"
                               class="form-control-custom"
                               placeholder="Masukkan qty">
                    </div>

                    <div>
                        <label class="form-label-custom">
                            Alasan Pengajuan
                        </label>

                        <select id="requestReason"
                                class="form-select-custom">
                            <option value="">
                                -- Pilih alasan --
                            </option>
                            <option value="stok_habis">
                                Stok habis
                            </option>
                            <option value="stok_tidak_mencukupi">
                                Stok tidak mencukupi
                            </option>
                            <option value="kebutuhan_project">
                                Kebutuhan project
                            </option>
                            <option value="kebutuhan_produksi">
                                Kebutuhan produksi
                            </option>
                            <option value="lainnya">
                                Lainnya
                            </option>
                        </select>
                    </div>

                    <button type="button"
                            class="btn-add-material"
                            id="btnAddMaterial">
                        <i class="fa fa-plus"></i>
                        Tambah ke Pengajuan
                    </button>

                </div>

            </div>

            <div class="stock-rule">
                <b><i class="fa fa-info-circle"></i> Aturan cek stock:</b>
                Barang wajib dipilih melalui pencarian inventory terlebih dahulu.
                Jika stok gudang masih mencukupi kebutuhan, barang tidak dapat ditambahkan
                ke pengajuan.
            </div>

        </div>

        {{-- TABLE --}}
        <div class="table-section">

            <div class="table-title">
                Daftar Barang yang Diajukan
            </div>

            <div class="request-table-wrapper">

                <table class="request-table">

                    <thead>
                        <tr>
                            <th class="no-column">No</th>
                            <th class="material-column">Kebutuhan / Material</th>
                            <th class="supplier-column">Supplier / Vendor</th>
                            <th class="payment-column">Payment</th>
                            <th class="description-column">Description</th>
                            <th class="warehouse-column">Warehouse</th>
                            <th class="qty-column">Qty</th>
                            <th class="unit-column">Sat</th>
                            <th class="price-column">Unit Price</th>
                            <th class="total-column">Total</th>
                            <th class="status-column">Status</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>

                    <tbody id="requestTableBody">
                        <tr id="emptyRequestRow">
                            <td colspan="12" class="empty-request">
                                <i class="fa fa-cubes"></i>
                                Belum ada barang yang ditambahkan
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="9"
                                style="text-align:right;font-weight:700;">
                                TOTAL
                            </td>
                            <td id="grandTotal"
                                style="font-weight:700;">
                                0
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                </table>

            </div>

            <div class="table-footer">

                <button type="button"
                        class="btn-add-row"
                        id="btnAddManualRow">
                    <i class="fa fa-plus"></i>
                    Add Row
                </button>

                <button type="button"
                        class="btn btn-success btn-sm"
                        id="btnSubmitRequest">
                    <i class="fa fa-paper-plane"></i>
                    Submit Pengajuan
                </button>

            </div>

        </div>

    </div>

</div>

<script>
$(document).ready(function () {

    let selectedMaterial = null;
    let requestItems = [];

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 2
        });
    }

    function searchMaterial() {

        let keyword = $('#materialSearch').val().trim();

        if (keyword.length < 1) {
            $('#searchResult')
                .removeClass('show')
                .html('');
            return;
        }

        $('#searchResult')
            .html(`
                <div class="material-result"
                     style="cursor:default;text-align:center;color:#777;">
                    <i class="fa fa-spinner fa-spin"></i>
                    Mencari barang...
                </div>
            `)
            .addClass('show');

        $.ajax({
            url: "{{ route('pengajuan_purchasing.search') }}",
            type: "GET",
            data: {
                q: keyword
            },
            success: function (res) {

                let html = '';

                if (!res.success || !res.data || !res.data.length) {

                    html = `
                        <div class="material-result"
                             style="cursor:default;text-align:center;color:#999;">
                            <i class="fa fa-search"></i>
                            Barang tidak ditemukan
                        </div>
                    `;

                } else {

                    res.data.forEach(function (item) {

                        let stock = Number(item.stok_akhir || 0);

                        let stockClass =
                            stock <= 0
                                ? 'stock-danger'
                                : 'stock-success';

                        html += `
                            <div class="material-result"
                                 data-id="${item.id}">

                                <div class="material-code">
                                    ${escapeHtml(item.kode_barang)}
                                    -
                                    ${escapeHtml(item.nama_barang)}
                                </div>

                                <div class="material-name">
                                    Jenis:
                                    ${escapeHtml(item.jenis || '-')}
                                    |
                                    Satuan:
                                    ${escapeHtml(item.satuan || '-')}
                                </div>

                                <div class="material-stock ${stockClass}">
                                    Stok tersedia:
                                    <b>
                                        ${formatNumber(stock)}
                                        ${escapeHtml(item.satuan || '')}
                                    </b>
                                </div>

                            </div>
                        `;
                    });
                }

                $('#searchResult')
                    .html(html)
                    .addClass('show');
            },

            error: function () {

                $('#searchResult')
                    .html(`
                        <div class="material-result"
                             style="cursor:default;text-align:center;color:#dc3545;">
                            Gagal mengambil data inventory
                        </div>
                    `)
                    .addClass('show');
            }
        });
    }

    $('#materialSearch').on('keyup', function () {
        searchMaterial();
    });

    $('#materialSearch').on('focus', function () {

        if ($(this).val().trim() !== '') {
            searchMaterial();
        }

    });

    $('#btnSearchMaterial').on('click', function () {
        searchMaterial();
    });

    $(document).on('click', '.material-result[data-id]', function () {

        let id = $(this).data('id');

        $.ajax({
            url: "{{ url('/pengajuan_purchasing/barang') }}/" + id,
            type: "GET",

            success: function (res) {

                if (!res.success || !res.data) {
                    alert('Data barang tidak ditemukan.');
                    return;
                }

                let item = res.data;

                selectedMaterial = {
                    id: item.id,
                    code: item.kode_barang,
                    name: item.nama_barang,
                    stock: Number(item.stok_akhir || 0),
                    unit: item.satuan,
                    price: Number(item.harga || 0),
                    jenis: item.jenis
                };

                $('#selectedMaterialName')
                    .text(
                        item.kode_barang +
                        ' - ' +
                        item.nama_barang
                    );

                $('#selectedMaterialCode')
                    .text(
                        'Jenis: ' +
                        (item.jenis || '-') +
                        ' | Kode: ' +
                        item.kode_barang
                    );

                $('#selectedUnit')
                    .text(item.satuan || '-');

                $('#warehouseStock')
                    .text(
                        formatNumber(item.stok_akhir) +
                        ' ' +
                        (item.satuan || '')
                    );

                $('#requestQty').val('');
                $('#requestQtyDisplay')
                    .text('0 ' + (item.satuan || ''));

                $('#stockStatus')
                    .text('Belum dicek')
                    .removeClass(
                        'stock-danger stock-warning stock-success'
                    );

                $('#requestReason').val('');

                $('#selectedMaterial')
                    .addClass('show');

                $('#materialSearch')
                    .val(
                        item.kode_barang +
                        ' - ' +
                        item.nama_barang
                    );

                $('#searchResult')
                    .removeClass('show');

                $('#requestQty').focus();
            },

            error: function () {
                alert('Gagal mengambil detail barang.');
            }
        });
    });

    $('#requestQty').on('input', function () {

        if (!selectedMaterial) {
            return;
        }

        let qty = Number($(this).val()) || 0;
        let stock = Number(selectedMaterial.stock) || 0;
        let unit = selectedMaterial.unit || '';

        $('#requestQtyDisplay')
            .text(formatNumber(qty) + ' ' + unit);

        $('#stockStatus')
            .removeClass(
                'stock-danger stock-warning stock-success'
            );

        if (qty <= 0) {

            $('#stockStatus')
                .text('Belum dicek');

            return;
        }

        if (stock >= qty) {

            $('#stockStatus')
                .text('Stock mencukupi')
                .addClass('stock-success');

        } else if (stock > 0) {

            $('#stockStatus')
                .text('Stock tidak mencukupi')
                .addClass('stock-warning');

        } else {

            $('#stockStatus')
                .text('Stock habis')
                .addClass('stock-danger');
        }
    });

    $('#btnAddMaterial').on('click', function () {

        if (!selectedMaterial) {
            alert('Silakan cari dan pilih barang dari inventory terlebih dahulu.');
            return;
        }

        let qty = Number($('#requestQty').val()) || 0;

        if (qty <= 0) {
            alert('Masukkan qty kebutuhan.');
            $('#requestQty').focus();
            return;
        }

        let reason = $('#requestReason').val();

        if (!reason) {
            alert('Pilih alasan pengajuan.');
            $('#requestReason').focus();
            return;
        }

        let stock = Number(selectedMaterial.stock) || 0;

        if (stock >= qty) {

            alert(
                'Stock gudang masih mencukupi kebutuhan. ' +
                'Barang tidak perlu diajukan.'
            );

            return;
        }

        /*
         * Hindari barang yang sama masuk dua kali.
         */
        let duplicate = requestItems.some(function (item) {
            return Number(item.id) === Number(selectedMaterial.id);
        });

        if (duplicate) {
            alert('Barang tersebut sudah ada di daftar pengajuan.');
            return;
        }

        requestItems.push({
            id: selectedMaterial.id,
            code: selectedMaterial.code,
            name: selectedMaterial.name,
            jenis: selectedMaterial.jenis,
            warehouse: 'Gudang Utama',
            stock: stock,
            qty: qty,
            unit: selectedMaterial.unit,
            reason: reason,
            supplier: '',
            payment: '',
            description: '',
            unit_price: selectedMaterial.price || 0,
            total: (selectedMaterial.price || 0) * qty
        });

        renderTable();

        $('#selectedMaterial')
            .removeClass('show');

        $('#materialSearch')
            .val('');

        $('#requestQty')
            .val('');

        $('#requestReason')
            .val('');

        selectedMaterial = null;
    });

    function renderTable() {

        let tbody = $('#requestTableBody');

        tbody.empty();

        if (requestItems.length === 0) {

            tbody.html(`
                <tr id="emptyRequestRow">
                    <td colspan="12"
                        class="empty-request">
                        <i class="fa fa-cubes"></i>
                        Belum ada barang yang ditambahkan
                    </td>
                </tr>
            `);

            $('#grandTotal').text('0');

            return;
        }

        requestItems.forEach(function (item, index) {

            let statusHtml = '';

            if (Number(item.stock) <= 0) {

                statusHtml = `
                    <span class="badge bg-danger">
                        Stock Habis
                    </span>
                `;

            } else {

                statusHtml = `
                    <span class="badge bg-warning text-dark">
                        Tidak Cukup
                    </span>
                `;
            }

            tbody.append(`
                <tr>

                    <td class="text-center">
                        ${index + 1}
                    </td>

                    <td>
                        <b>${escapeHtml(item.code)}</b><br>
                        ${escapeHtml(item.name)}
                    </td>

                    <td>
                        <input type="text"
                               class="row-supplier"
                               data-index="${index}"
                               value="${escapeHtml(item.supplier)}"
                               placeholder="Supplier / Vendor">
                    </td>

                    <td>
                        <select class="row-payment"
                                data-index="${index}">
                            <option value="">-</option>
                            <option value="cash"
                                ${item.payment === 'cash' ? 'selected' : ''}>
                                Cash
                            </option>
                            <option value="transfer"
                                ${item.payment === 'transfer' ? 'selected' : ''}>
                                Transfer
                            </option>
                            <option value="tempo"
                                ${item.payment === 'tempo' ? 'selected' : ''}>
                                Tempo
                            </option>
                        </select>
                    </td>

                    <td>
                        <input type="text"
                               class="row-description"
                               data-index="${index}"
                               value="${escapeHtml(item.description)}"
                               placeholder="Description">
                    </td>

                    <td>
                        ${escapeHtml(item.warehouse)}
                    </td>

                    <td class="text-center">
                        ${formatNumber(item.qty)}
                    </td>

                    <td class="text-center">
                        ${escapeHtml(item.unit || '-')}
                    </td>

                    <td>
                        <input type="number"
                               min="0"
                               step="0.01"
                               class="unit-price"
                               data-index="${index}"
                               value="${Number(item.unit_price || 0)}">
                    </td>

                    <td class="item-total"
                        data-index="${index}">
                        ${formatNumber(item.total)}
                    </td>

                    <td class="text-center">
                        ${statusHtml}
                    </td>

                    <td class="text-center">
                        <button type="button"
                                class="remove-row"
                                data-index="${index}"
                                title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>

                </tr>
            `);
        });

        calculateGrandTotal();
    }

    $(document).on('input', '.row-supplier', function () {

        let index = Number($(this).data('index'));

        if (requestItems[index]) {
            requestItems[index].supplier = $(this).val();
        }
    });

    $(document).on('change', '.row-payment', function () {

        let index = Number($(this).data('index'));

        if (requestItems[index]) {
            requestItems[index].payment = $(this).val();
        }
    });

    $(document).on('input', '.row-description', function () {

        let index = Number($(this).data('index'));

        if (requestItems[index]) {
            requestItems[index].description = $(this).val();
        }
    });

    $(document).on('input', '.unit-price', function () {

        let index = Number($(this).data('index'));

        if (!requestItems[index]) {
            return;
        }

        let price = Number($(this).val()) || 0;
        let qty = Number(requestItems[index].qty) || 0;
        let total = price * qty;

        requestItems[index].unit_price = price;
        requestItems[index].total = total;

        $(this)
            .closest('tr')
            .find('.item-total')
            .text(formatNumber(total));

        calculateGrandTotal();
    });

    function calculateGrandTotal() {

        let total = 0;

        requestItems.forEach(function (item) {
            total += Number(item.total) || 0;
        });

        $('#grandTotal')
            .text(formatNumber(total));
    }

    $(document).on('click', '.remove-row', function () {

        let index = Number($(this).data('index'));

        requestItems.splice(index, 1);

        renderTable();
    });

    $(document).on('click', function (e) {

        if (
            !$(e.target).closest('.search-box').length &&
            !$(e.target).closest('#searchResult').length
        ) {
            $('#searchResult')
                .removeClass('show');
        }
    });

    $('#btnAddManualRow').on('click', function () {

        alert(
            'Untuk menjaga validasi stok, barang harus dipilih melalui pencarian inventory terlebih dahulu.'
        );
    });

    $('#btnSaveRequest').on('click', function () {

        alert(
            'UI sudah siap. Endpoint penyimpanan draft dibuat pada tahap berikutnya.'
        );
    });

    $('#btnSubmitRequest').on('click', function () {

        if (requestItems.length === 0) {

            alert('Belum ada barang yang diajukan.');

            return;
        }

        if (!$('#department').val()) {

            alert('Pilih departemen terlebih dahulu.');

            $('#department').focus();

            return;
        }

        alert(
            'UI sudah siap. Proses submit approval dibuat pada tahap berikutnya.'
        );
    });

});
</script>

@endsection
