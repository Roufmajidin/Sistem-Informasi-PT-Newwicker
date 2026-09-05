{{-- HEADER --}}
        <div class="page-header">
            <div>
                <h4 class="page-title"> 
                    Pengajuan Barang Inventory
                </h4>
                <div class="page-subtitle">
                    {{ !empty($editPengajuan)
                        ? (!empty($canEdit)
                            ? 'Pengajuan Barang / Edit Pengajuan #' . $editPengajuan->id
                            : 'Pengajuan Barang / Lihat Pengajuan #' . $editPengajuan->id)
                        : 'Pengajuan Barang / Buat Pengajuan' }}
                </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center;">
                <button type="button"
                        class="btn btn-primary btn-sm"
                        id="btnSaveRequest"
                        {{ !empty($editPengajuan) && empty($canEdit) ? 'disabled' : '' }}>
                    <i class="fa fa-save"></i>
                    <span id="btnSaveRequestText">{{ !empty($editPengajuan) ? "Simpan Perubahan" : "Simpan Draft" }}</span>
                </button>

                <button type="button"
                        class="btn btn-outline-danger btn-sm"
                        id="btnClearCache"
                        {{ !empty($editPengajuan) && empty($canEdit) ? 'disabled' : '' }}>
                    <i class="fa fa-trash"></i>
                    Clear Cache
                </button>
                <div id="draftCacheStatus" class="draft-cache-status"></div>
            </div>
        </div>

        @if(!empty($editPengajuan) && empty($canEdit))
            <div class="purchasing-readonly-banner">
                <i class="fa fa-lock"></i>
                Anda bukan pembuat pengajuan ini. Data hanya dapat dilihat dan tidak dapat diubah.
            </div>
        @endif

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
                    @foreach(($divisis ?? collect()) as $divisi)
                        <option value="{{ $divisi->id }}">{{ $divisi->nama ?? $divisi->name }}</option>
                    @endforeach
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

            <div id="newMaterialForm" class="new-material-form">
                <div class="new-material-form-title">
                    <i class="fa fa-plus-circle"></i>
                    Tambahkan Barang Baru
                </div>

                <div class="new-material-grid">
                    <div>
                        <label class="form-label-custom">Nama Barang *</label>
                        <input type="text"
                               id="newMaterialName"
                               class="form-control-custom"
                               placeholder="Nama barang yang akan dibeli">
                    </div>

                    <div>
                        <label class="form-label-custom">Satuan *</label>
                        <input type="text"
                               id="newMaterialUnit"
                               class="form-control-custom"
                               placeholder="Pcs / Kg / Roll / dll">
                    </div>

                    <div>
                        <label class="form-label-custom">Qty Kebutuhan *</label>
                        <input type="number"
                               id="newMaterialQty"
                               min="0.01"
                               step="0.01"
                               class="form-control-custom"
                               placeholder="Qty">
                    </div>
                </div>

                <div class="new-material-actions">
                    <button type="button"
                            class="btn-new-material"
                            id="btnAddNewMaterial">
                        <i class="fa fa-plus"></i>
                        Tambah Barang Baru
                    </button>

                    <button type="button"
                            class="btn-cancel-new-material"
                            id="btnCancelNewMaterial">
                        Batal
                    </button>
                </div>
            </div>

            <div class="stock-rule">
                <b><i class="fa fa-info-circle"></i> Aturan cek stock:</b>
                Barang existing wajib dicek melalui inventory terlebih dahulu.
                Jika stok gudang masih mencukupi kebutuhan, barang tidak dapat diajukan.
                Jika barang tidak ditemukan di inventory, user tetap dapat menambahkannya
                sebagai barang baru.
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
                            <th class="po-column">PO No</th>
                            <th class="payment-column">Payment</th>
                            <th class="description-column">Description</th>
                            <th class="keterangan-column">Keterangan</th>
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
                            <td colspan="14" class="empty-request">
                                <i class="fa fa-cubes"></i>
                                Belum ada barang yang ditambahkan
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="11"
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
{{-- SIGNATURE --}}
<div class="signature-section">

    <table class="signature-table">

        <thead>
            <tr>
                <th>Made by</th>
                <th colspan="2">Checked by</th>
                <th colspan="2">Checked by</th>
                <th>Checked by Finance</th>
                <th>Approved by</th>
            </tr>
        </thead>

        <tbody>

            <tr class="signature-role">
                <td>{{ auth()->user()->name }}</td>

                <td>Person 1</td>
                <td>Person 2</td>

                <td>Person 1</td>
                <td>Person 2</td>

                <td>Finance</td>

                <td>Approver</td>
            </tr>

            <tr class="signature-input-row">

                {{-- MADE BY --}}
                <td>
                    <input type="text"
                           value="{{ auth()->user()->name }}"
                           readonly
                           class="signature-input">
                </td>

                {{-- CHECKED BY 1 --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_1">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                {{-- CHECKED BY 2 --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_2">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                {{-- CHECKED BY 3 / PERSON 1 --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_3">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                {{-- CHECKED BY 4 / PERSON 2 --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_4">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                {{-- FINANCE --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_finance">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                {{-- APPROVED --}}
                <td>
                    <select class="signature-select"
                            id="approved_by">
                        <option value="">-- Select --</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

            </tr>

        </tbody>

    </table>

</div>
        </div>

<script>
$(document).ready(function () {

    let selectedMaterial = null;
    let requestItems = [];

    const EDIT_MODE = @json(!empty($editPengajuan));
    const EDIT_DATA = @json($editData ?? null);
    const CAN_EDIT = @json(!empty($canEdit) || empty($editPengajuan));

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
                        <div class="new-material-result"
                             id="resultAddNewMaterial">
                            <div class="new-material-title">
                                <i class="fa fa-plus-circle"></i>
                                Barang tidak ditemukan
                            </div>
                            <div class="new-material-desc">
                                Tidak ada barang dengan kata kunci
                                <b>${escapeHtml(keyword)}</b>
                                di database inventory.
                            </div>
                            <span class="new-material-badge">
                                + Tambahkan sebagai barang baru
                            </span>
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
                    jenis: item.jenis,
                    is_new: false
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

    $(document).on('click', '#resultAddNewMaterial', function () {

        let keyword = $('#materialSearch').val().trim();

        if (!keyword) {
            return;
        }

        selectedMaterial = {
            id: null,
            code: '',
            name: keyword,
            stock: 0,
            unit: '',
            price: 0,
            jenis: 'Barang Baru',
            is_new: true
        };

        $('#selectedMaterial').removeClass('show');

        $('#newMaterialName').val(keyword);
        $('#newMaterialUnit').val('');
        $('#newMaterialQty').val('');

        $('#newMaterialForm').addClass('show');
        $('#searchResult').removeClass('show');

        $('#newMaterialName').focus();
    });

    $('#btnCancelNewMaterial').on('click', function () {

        $('#newMaterialForm').removeClass('show');
        $('#newMaterialName').val('');
        $('#newMaterialUnit').val('');
        $('#newMaterialQty').val('');
        $('#materialSearch').val('');

        selectedMaterial = null;
    });

    $('#btnAddNewMaterial').on('click', function () {

        let name = $('#newMaterialName').val().trim();
        let unit = $('#newMaterialUnit').val().trim();
        let qty = Number($('#newMaterialQty').val()) || 0;

        if (!name) {
            alert('Nama barang wajib diisi.');
            $('#newMaterialName').focus();
            return;
        }

        if (!unit) {
            alert('Satuan wajib diisi.');
            $('#newMaterialUnit').focus();
            return;
        }

        if (qty <= 0) {
            alert('Qty kebutuhan wajib diisi.');
            $('#newMaterialQty').focus();
            return;
        }

        let duplicate = requestItems.some(function (item) {
            return item.is_new &&
                String(item.name).toLowerCase() === name.toLowerCase();
        });

        if (duplicate) {
            alert('Barang baru tersebut sudah ada di daftar pengajuan.');
            return;
        }

        requestItems.push({
            id: null,
            code: '',
            name: name,
            jenis: 'Barang Baru',
            warehouse: 'Belum ada di inventory',
            stock: 0,
            qty: qty,
            unit: unit,
            reason: 'barang_baru',
            supplier: '',
            po_no: '',
            payment: '',
            description: '',
            keterangan: '',
            unit_price: 0,
            total: 0,
            is_new: true
        });

        renderTable();

        $('#newMaterialForm').removeClass('show');
        $('#newMaterialName').val('');
        $('#newMaterialUnit').val('');
        $('#newMaterialQty').val('');
        $('#materialSearch').val('');

        selectedMaterial = null;
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

        if (selectedMaterial.is_new) {
            alert('Gunakan pilihan "Tambahkan sebagai barang baru" untuk item yang tidak ada di inventory.');
            return;
        }

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
            po_no: '',
            payment: '',
            description: '',
            keterangan: '',
            unit_price: selectedMaterial.price || 0,
            total: (selectedMaterial.price || 0) * qty,
            is_new: false
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
            let materialHtml = '';
            let rowClass = '';

            if (item.is_new) {

                rowClass = 'new-item-row';

                statusHtml = `
                    <span class="badge bg-info">
                        Barang Baru
                    </span>
                `;

                materialHtml = `
                    <b>${escapeHtml(item.name)}</b><br>
                    <span class="new-item-label">
                        Belum ada di inventory
                    </span>
                `;

            } else {

                statusHtml =
                    Number(item.stock) <= 0
                        ? `<span class="badge bg-danger">Stock Habis</span>`
                        : `<span class="badge bg-warning text-dark">Tidak Cukup</span>`;

                materialHtml = `
                    <b>${escapeHtml(item.code)}</b><br>
                    ${escapeHtml(item.name)}
                `;
            }

            tbody.append(`
                <tr class="${rowClass}">

                    <td class="text-center">
                        ${index + 1}
                    </td>

                    <td>
                        ${materialHtml}
                    </td>

                    <td>
                        <input type="text"
                               class="row-supplier"
                               data-index="${index}"
                               value="${escapeHtml(item.supplier)}"
                               placeholder="Supplier / Vendor"
                                ${CAN_EDIT ? '' : 'disabled'}>
                    </td>

                    <td>
                        <input type="text"
                               class="row-po-no"
                               data-index="${index}"
                               value="${escapeHtml(item.po_no || '')}"
                               placeholder="PO No"
                                ${CAN_EDIT ? '' : 'disabled'}>
                    </td>

                    <td>
                        <select class="row-payment"
                                data-index="${index}"
                                 ${CAN_EDIT ? '' : 'disabled'}>
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
                               placeholder="Description"
                                ${CAN_EDIT ? '' : 'disabled'}>
                    </td>

                    <td>
                        <input type="text"
                               class="row-keterangan"
                               data-index="${index}"
                               value="${escapeHtml(item.keterangan || '')}"
                               placeholder="Keterangan"
                                ${CAN_EDIT ? '' : 'disabled'}>
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
                               value="${Number(item.unit_price || 0)}"
                                ${CAN_EDIT ? '' : 'disabled'}>
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
                                title="${CAN_EDIT ? 'Hapus' : 'Tidak dapat diubah'}"
                                 ${CAN_EDIT ? '' : 'disabled'}>
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

    $(document).on('input', '.row-po-no', function () {

        let index = Number($(this).data('index'));

        if (requestItems[index]) {
            requestItems[index].po_no = $(this).val();
        }
    });

    $(document).on('input', '.row-keterangan', function () {

        let index = Number($(this).data('index'));

        if (requestItems[index]) {
            requestItems[index].keterangan = $(this).val();
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
            'Barang existing harus dipilih dari inventory. Jika barang belum ada, cari nama barang lalu pilih opsi "Tambahkan sebagai barang baru".'
        );
    });

    // ============================================================
    // DRAFT CACHE
    // ============================================================
    // localStorage dipakai agar draft tetap ada walaupun:
    // - halaman direload
    // - browser tidak sengaja tertutup
    // - user pindah halaman lalu kembali
    //
    // Cache hanya tersimpan di browser/perangkat user.
    // Belum dikirim ke database sampai endpoint server dibuat khusus.
    // ============================================================

    const PURCHASING_CACHE_KEY = 'pengajuan_purchasing_draft_v1';

    let cacheSaveTimer = null;

    function getDraftCacheData() {

        return {
            version: 1,

            saved_at: new Date().toISOString(),

            requestDate: $('#requestDate').val() || '',

            department: $('#department').val() || '',

            neededDate: $('#neededDate').val() || '',

            items: requestItems,

            signature: {
                checked_by_1: $('#checked_by_1').val() || '',
                checked_by_2: $('#checked_by_2').val() || '',
                checked_by_3: $('#checked_by_3').val() || '',
                checked_by_4: $('#checked_by_4').val() || '',
                checked_by_finance: $('#checked_by_finance').val() || '',
                approved_by: $('#approved_by').val() || ''
            }
        };
    }


    function showCacheStatus(message, type) {

        $('#draftCacheStatus')
            .removeClass('saved cleared')
            .addClass(type || '')
            .text(message || '');
    }


    function saveDraftCache(showMessage = true) {

        try {

            if (typeof Storage === 'undefined') {
                throw new Error('Browser tidak mendukung localStorage.');
            }

            const data = getDraftCacheData();

            localStorage.setItem(
                PURCHASING_CACHE_KEY,
                JSON.stringify(data)
            );

            // Pastikan benar-benar tersimpan.
            const verify = localStorage.getItem(PURCHASING_CACHE_KEY);

            if (!verify) {
                throw new Error('Data draft tidak ditemukan setelah localStorage.setItem().');
            }

            console.log('Purchasing draft tersimpan:', data);

            if (showMessage) {

                showCacheStatus(
                    'Draft tersimpan di cache browser.',
                    'saved'
                );

            }

            return true;

        } catch (error) {

            console.error(
                'Gagal menyimpan draft cache:',
                error
            );

            showCacheStatus(
                'Cache gagal disimpan.',
                'cleared'
            );

            return false;
        }
    }


    function scheduleDraftCache() {

        clearTimeout(cacheSaveTimer);

        cacheSaveTimer = setTimeout(function () {

            saveDraftCache(false);

            showCacheStatus(
                'Perubahan tersimpan otomatis.',
                'saved'
            );

        }, 350);
    }


    function loadDraftCache() {

        try {

            const raw =
                localStorage.getItem(
                    PURCHASING_CACHE_KEY
                );

            if (!raw) {
                return false;
            }

            const data = JSON.parse(raw);

            if (!data || typeof data !== 'object') {
                return false;
            }


            // Informasi pengajuan
            if (data.requestDate) {
                $('#requestDate').val(data.requestDate);
            }

            if (data.department) {
                $('#department').val(data.department);
            }

            if (data.neededDate) {
                $('#neededDate').val(data.neededDate);
            }


            // Barang
            if (Array.isArray(data.items)) {

                requestItems = data.items.map(function (item) {

                    return {
                        id: item.id ?? null,
                        code: item.code ?? '',
                        name: item.name ?? '',
                        jenis: item.jenis ?? '',
                        warehouse: item.warehouse ?? '',
                        stock: Number(item.stock || 0),
                        qty: Number(item.qty || 0),
                        unit: item.unit ?? '',
                        reason: item.reason ?? '',
                        supplier: item.supplier ?? '',
                        po_no: item.po_no ?? '',
                        payment: item.payment ?? '',
                        description: item.description ?? '',
                        keterangan: item.keterangan ?? '',
                        unit_price: Number(item.unit_price || 0),
                        total: Number(item.total || 0),
                        is_new: Boolean(item.is_new)
                    };

                });

                renderTable();
            }


            // Signature
            if (data.signature) {

                $('#checked_by_1').val(
                    data.signature.checked_by_1 || ''
                );

                $('#checked_by_2').val(
                    data.signature.checked_by_2 || ''
                );

                $('#checked_by_3').val(
                    data.signature.checked_by_3 || ''
                );

                $('#checked_by_4').val(
                    data.signature.checked_by_4 || ''
                );

                $('#checked_by_finance').val(
                    data.signature.checked_by_finance || ''
                );

                $('#approved_by').val(
                    data.signature.approved_by || ''
                );
            }


            showCacheStatus(
                'Draft cache berhasil dipulihkan.',
                'saved'
            );

            return true;

        } catch (error) {

            console.error(
                'Gagal membaca draft cache:',
                error
            );

            localStorage.removeItem(
                PURCHASING_CACHE_KEY
            );

            return false;
        }
    }


    function clearDraftCache() {

        const hasData =
            localStorage.getItem(
                PURCHASING_CACHE_KEY
            );

        if (
            hasData &&
            !confirm(
                'Hapus draft cache ini? Semua data barang dan isian form yang tersimpan di browser akan dihapus.'
            )
        ) {
            return;
        }


        localStorage.removeItem(
            PURCHASING_CACHE_KEY
        );

        // Clear juga ID database draft karena user memang memilih
        // untuk memulai draft baru dari awal.
        localStorage.removeItem('pengajuan_purchasing_id');

        clearTimeout(cacheSaveTimer);


        // Reset barang
        requestItems = [];

        renderTable();


        // Reset informasi
        $('#requestDate').val(
            "{{ date('Y-m-d') }}"
        );

        $('#department').val('');

        $('#neededDate').val('');


        // Reset signature
        $('#checked_by_1').val('');
        $('#checked_by_2').val('');
        $('#checked_by_3').val('');
        $('#checked_by_4').val('');
        $('#checked_by_finance').val('');
        $('#approved_by').val('');


        // Reset material selection
        selectedMaterial = null;

        $('#materialSearch').val('');

        $('#selectedMaterial').removeClass('show');

        $('#newMaterialForm').removeClass('show');

        $('#searchResult')
            .removeClass('show')
            .html('');

        $('#requestQty').val('');

        $('#requestReason').val('');

        $('#warehouseStock').text('0');

        $('#stockStatus')
            .text('Belum dicek')
            .removeClass(
                'stock-danger stock-warning stock-success'
            );


        showCacheStatus(
            'Cache berhasil dibersihkan.',
            'cleared'
        );
    }


    // ============================================================
    // SIMPAN DRAFT KE DATABASE VIA AJAX
    // ============================================================
    $(document).off('click.purchasingSaveDraft', '#btnSaveRequest');

    $(document).on('click.purchasingSaveDraft', '#btnSaveRequest', function (e) {

        e.preventDefault();
        e.stopPropagation();

        const button = $(this);

        if (EDIT_MODE && !CAN_EDIT) {
            alert('Anda bukan pembuat pengajuan ini. Pengajuan hanya dapat dilihat.');
            return;
        }

        // Ambil data terbaru dari form + requestItems
        const draft = getDraftCacheData();

        if (!draft.items || draft.items.length === 0) {
            alert('Belum ada barang yang ditambahkan ke pengajuan.');
            return;
        }

        button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        // Backup browser tetap dilakukan
        saveDraftCache(false);

        $.ajax({
    url: "{{ route('pengajuan_purchasing.save') }}",
    type: "POST",
    dataType: "json",

    data: {
        _token: "{{ csrf_token() }}",

        pengajuan_id: EDIT_MODE && EDIT_DATA ? EDIT_DATA.id : (localStorage.getItem('pengajuan_purchasing_id') || null),

        // SESUAI CONTROLLER
        tanggal: $('#requestDate').val() || '',
        divisi_id: $('#department').val() || '',
        need_date: $('#neededDate').val() || '',

        // WAJIB ARRAY, JANGAN JSON.stringify()
        items: requestItems.map(function(item) {
            return {
                id_stock: item.id || null,
                nama_barang: item.name || '',
                supplier: item.supplier || '',
                po_no: item.po_no || '',
                payment: item.payment || '',
                description: item.description || '',
                keterangan: item.keterangan || '',
                qty: item.qty || 0,
                unit: item.unit || '',
                price: item.unit_price || 0
            };
        }),

        signature: {
            checked_by_1: $('#checked_by_1').val() || null,
            checked_by_2: $('#checked_by_2').val() || null,
            checked_by_3: $('#checked_by_3').val() || null,
            checked_by_4: $('#checked_by_4').val() || null,
            checked_by_finance: $('#checked_by_finance').val() || null,
            approved_by: $('#approved_by').val() || null
        }
    },

    success: function(response) {

        console.log('SAVE SUCCESS:', response);

        if (response.success) {

            if (response.pengajuan_id) {
                // ID database HARUS tetap disimpan agar klik Simpan Draft
                // berikutnya meng-update draft yang sama.
                localStorage.setItem(
                    'pengajuan_purchasing_id',
                    String(response.pengajuan_id)
                );
            }

            // Jangan hapus PURCHASING_CACHE_KEY di sini.
            // Cache browser tetap menjadi backup jika halaman direload.
            showCacheStatus(
                EDIT_MODE ? '✓ Perubahan berhasil disimpan ke database.' : '✓ Draft berhasil disimpan ke database.',
                'saved'
            );

            alert(EDIT_MODE ? 'Perubahan pengajuan berhasil disimpan.' : 'Draft berhasil disimpan.');
        } else {
            alert(response.message || 'Gagal menyimpan draft.');
        }
    },

    error: function(xhr) {

        console.error('SAVE ERROR:', xhr.responseJSON || xhr.responseText);

        let message = 'Gagal menyimpan draft.';

        // Jangan otomatis menghapus localStorage/cache saat gagal.
        // Data user harus tetap aman untuk dicoba kembali.
        if (xhr.responseJSON && xhr.responseJSON.message &&
            String(xhr.responseJSON.message).toLowerCase().includes('no query results for model')) {
            message = 'Draft database sebelumnya tidak ditemukan. ID draft browser tetap dipertahankan agar data cache tidak hilang. Silakan cek database sebelum membuat draft baru.';
        }

        if (xhr.responseJSON) {

            if (xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            if (xhr.responseJSON.errors) {
                console.table(xhr.responseJSON.errors);

                message += '\n\n';

                Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                    message +=
                        key + ': ' +
                        xhr.responseJSON.errors[key].join(', ') +
                        '\n';
                });
            }
        }

        alert(message);
    },

    complete: function() {

        button
            .prop('disabled', false)
            .html('<i class="fa fa-save"></i> ' + (EDIT_MODE ? 'Simpan Perubahan' : 'Simpan Draft'));
    }
}); 
}); 

    // Tombol Clear Cache
    $('#btnClearCache').on('click', function () {

        clearDraftCache();

    });


    // Auto-save perubahan form utama
    $('#requestDate, #department, #neededDate')
        .on('input change', function () {

            scheduleDraftCache();

        });


    // Auto-save signature
    $(document).on(
        'change',
        '#checked_by_1, #checked_by_2, #checked_by_3, #checked_by_4, #checked_by_finance, #approved_by',
        function () {

            scheduleDraftCache();

        }
    );


    // Auto-save saat data barang berubah
    $(document).on(
        'input change',
        '.row-supplier, .row-po-no, .row-payment, .row-description, .row-keterangan, .unit-price',
        function () {

            scheduleDraftCache();

        }
    );


    // Auto-save setelah barang ditambah/dihapus.
    // renderTable() tetap dipanggil oleh handler masing-masing.
    const originalRenderTable = renderTable;

    renderTable = function () {

        originalRenderTable();

        scheduleDraftCache();

    };


    // ============================================================
    // LOAD DATA EDIT DARI DATABASE
    // Jika URL /pengajuan_purchasing/edit/{id}, data database menjadi sumber utama.
    // Cache browser TIDAK boleh menimpa data pengajuan yang sedang diedit.
    // ============================================================
    function loadEditData() {
        if (!EDIT_MODE || !EDIT_DATA) return false;

        $('#requestDate').val(EDIT_DATA.tanggal || '');
        $('#department').val(String(EDIT_DATA.divisi_id || ''));
        $('#neededDate').val(EDIT_DATA.need_date || '');

        requestItems = Array.isArray(EDIT_DATA.items)
            ? EDIT_DATA.items.map(function (item) {
                return {
                    id: item.id || null,
                    code: item.code || '',
                    name: item.name || '',
                    jenis: item.jenis || '',
                    warehouse: item.warehouse || (item.id ? 'Gudang Utama' : 'Belum ada di inventory'),
                    stock: Number(item.stock || 0),
                    qty: Number(item.qty || 0),
                    unit: item.unit || '',
                    reason: item.reason || '',
                    supplier: item.supplier || '',
                    po_no: item.po_no || '',
                    payment: item.payment || '',
                    description: item.description || '',
                    keterangan: item.keterangan || '',
                    unit_price: Number(item.unit_price || 0),
                    total: Number(item.total || 0),
                    is_new: Boolean(item.is_new)
                };
            })
            : [];

        renderTable();

        const sig = EDIT_DATA.signature || {};
        $('#checked_by_1').val(sig.checked_by_1 || '');
        $('#checked_by_2').val(sig.checked_by_2 || '');
        $('#checked_by_3').val(sig.checked_by_3 || '');
        $('#checked_by_4').val(sig.checked_by_4 || '');
        $('#checked_by_finance').val(sig.checked_by_finance || '');
        $('#approved_by').val(sig.approved_by || '');

        $('#btnSaveRequestText').text('Simpan Perubahan');
        $('#btnSaveRequest').attr('title', 'Simpan perubahan pengajuan #' + EDIT_DATA.id);
        showCacheStatus('Sedang mengedit Pengajuan #' + EDIT_DATA.id + '.', 'saved');

        return true;
    }

    if (!loadEditData()) {
        loadDraftCache();
    }

    if (EDIT_MODE && !CAN_EDIT) {
        $('#requestDate, #department, #neededDate').prop('disabled', true);
        $('#materialSearch, #btnSearchMaterial, #requestQty, #requestReason').prop('disabled', true);
        $('#btnAddMaterial, #btnAddNewMaterial, #btnCancelNewMaterial, #btnAddManualRow').prop('disabled', true);
        $('#btnSubmitRequest, #btnSaveRequest, #btnClearCache').prop('disabled', true);
        $('#checked_by_1, #checked_by_2, #checked_by_3, #checked_by_4, #checked_by_finance, #approved_by').prop('disabled', true);
        $('.remove-row').prop('disabled', true);

        showCacheStatus(
            'Mode View Only — hanya pembuat pengajuan yang dapat mengubah data.',
            'cleared'
        );
    }


    // ============================================================
    // TAB DRAFT FORM / LIST PENGAJUAN
    // ============================================================
    $(document).on('click', '.purchasing-tab', function () {
        const tabId = $(this).data('tab');

        $('.purchasing-tab').removeClass('active');
        $(this).addClass('active');

        $('.purchasing-tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Pencarian cepat pada list tanpa reload halaman.
    $('#submissionSearch').on('input', function () {
        const keyword = String($(this).val() || '').toLowerCase().trim();
        $('.submission-row').each(function () {
            const text = String($(this).data('search') || '').toLowerCase();
            $(this).toggle(text.indexOf(keyword) !== -1);
        });
    });

    // Klik Lihat/Edit: buka data yang benar berdasarkan ID pada URL.
    $(document).on('click', '.btn-view-submission:not(:disabled)', function () {
        const id = $(this).data('id');
        if (!id) return;

        window.location.href = "{{ url('/pengajuan_purchasing/edit') }}/" + id;
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

        // Simpan dulu ke database sebelum proses submit approval.
        $('#btnSaveRequest').trigger('click');

        // Proses submit approval tetap dibuat pada tahap berikutnya.
    });

});
</script>

