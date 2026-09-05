@php
    $approvalLocked = !empty($editPengajuan)
        && (
            empty($canEdit)
            || (int) ($editPengajuan->is_draft ?? 0) === 1
        );

    /*
     * CREATE:
     *   Made by = user yang sedang login.
     *
     * EDIT / VIEW:
     *   Made by = pembuat asli dari pengajuan.
     */
    $isViewerOnly = !empty($editPengajuan) && empty($canEdit);

    $madeByName = !empty($editPengajuan)
        ? (optional($editPengajuan->user)->name ?? auth()->user()->name)
        : auth()->user()->name;

    $attachmentCount = !empty($editPengajuan) && isset($editPengajuan->files)
        ? $editPengajuan->files->where('type', 'image')->count()
        : 0;
@endphp

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
                <div id="draftCacheStatus" class="draft-cache-status viewer-action-status"></div>
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
        <div class="search-section" @if($isViewerOnly) style="display:none;" @endif>

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
        <div id="purchasingFormSection" class="table-section">

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

                <button type="button" class="btn-add-row" id="btnAddManualRow" @if($isViewerOnly) style="display:none;" @endif>
                    <i class="fa fa-plus"></i>
                    Add Row
                </button>

                <button type="button"
                        class="btn btn-success btn-sm"
                        id="btnSubmitRequest">
                    <i class="fa fa-paperclip"></i>
                    Attachment
                    @if($attachmentCount > 0)
                        <span class="attachment-count-badge">{{ $attachmentCount }}</span>
                    @endif
                </button>

            </div>


@if($isViewerOnly)
<div id="viewerPurchaseRequest" class="viewer-purchase-request">

    <div class="vpr-top">
        <div class="vpr-brand">
            <img src="{{ asset('images/logo-newwicker.png') }}"
                 alt="NewWicker"
                 class="vpr-logo">
        </div>
        <div class="vpr-title">Purchase Request</div>
        <div class="vpr-need">
            <b>Need by Date :</b>
            <span>
                {{ !empty($editData['need_date'])
                    ? \Carbon\Carbon::parse($editData['need_date'])->format('j-M-y')
                    : '-' }}
            </span>
        </div>
    </div>

    <div class="vpr-meta">
        <div>
            <b>Requisition Date :</b>
            <span>
                {{ !empty($editData['tanggal'])
                    ? \Carbon\Carbon::parse($editData['tanggal'])->format('d/m/Y')
                    : optional($editPengajuan->created_at)->format('d/m/Y') }}
            </span>
        </div>

        <div>
            <b>Department :</b>
            <span>
                {{ optional($editPengajuan->divisi)->nama
                    ?? optional($editPengajuan->divisi)->name
                    ?? '-' }}
            </span>
        </div>

        <div>
            <b>Made by :</b>
            <span>{{ $madeByName }}</span>
        </div>
    </div>

    <div class="vpr-table-wrap">
        <table class="vpr-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>PO</th>
                    <th>Supplier</th>
                    <th>Payment</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Sat</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $vprItems = $editData['items'] ?? [];
                    $vprGrandTotal = 0;
                @endphp

                @forelse($vprItems as $i => $item)
                    @php
                        $qty = (float)($item['qty'] ?? 0);
                        $price = (float)($item['unit_price'] ?? 0);
                        $total = (float)($item['total'] ?? ($qty * $price));
                        $vprGrandTotal += $total;
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="center">{{ $item['po_no'] ?? '-' }}</td>
                        <td>{{ $item['supplier'] ?? '-' }}</td>
                        <td class="center">{{ $item['payment'] ?? '-' }}</td>
                        <td>
    @if(!empty($item['description']))
        {{ $item['description'] }}
    @else
        {{ $item['name'] ?? '-' }}
    @endif
</td>
                        <td class="center">
                            {{ rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') }}
                        </td>
                        <td class="center">{{ $item['unit'] ?? '-' }}</td>
                        <td class="right">{{ number_format($price, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($total, 0, ',', '.') }}</td>
                        <td class="center">{{ $item['status'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="center">Tidak ada item.</td>
                    </tr>
                @endforelse

                <tr class="vpr-total">
                    <td colspan="8" class="right">TOTAL</td>
                    <td class="right">{{ number_format($vprGrandTotal, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ATTACHMENT --}}
<div class="attachment-section" id="attachmentSection" style="display:none;">
    <div class="attachment-header">
        <div>
            <div class="attachment-title">
                <i class="fa fa-paperclip"></i>
                Attachment
            </div>
            <div class="attachment-subtitle">
                Lampiran gambar pengajuan
            </div>
        </div>
    </div>

    {{-- CREATOR: upload --}}
    <div id="attachmentCreator" style="display:none;">
        <input type="file"
               id="attachmentInput"
               name="images[]"
               accept="image/*"
               multiple
               style="display:none;">

        <div class="attachment-upload-box" id="attachmentBrowseBox">
            <i class="fa fa-plus"></i>
            <span>Browse Image</span>
            <small>Bisa pilih lebih dari satu gambar</small>
        </div>

        <div class="attachment-preview" id="attachmentPreview"></div>
    </div>

    {{-- VIEWER: image vertikal --}}
    <div id="attachmentViewer" style="display:none;">
        <div class="attachment-viewer-list" id="attachmentViewerList">
            {{-- gambar existing di-render oleh JS --}}
        </div>

        <div id="attachmentViewerEmpty"
             style="display:none;text-align:center;color:#999;padding:20px;">
            <i class="fa fa-image"
               style="font-size:28px;display:block;margin-bottom:7px;"></i>
            Tidak ada attachment.
        </div>
    </div>
</div>

<style>
    .attachment-section {
        margin-top: 14px;
        margin-bottom: 18px;
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }

    .attachment-header {
        margin-bottom: 12px;
    }

    .attachment-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f2937;
    }

    .attachment-title i {
        margin-right: 6px;
    }

    .attachment-subtitle {
        margin-top: 3px;
        font-size: 11px;
        color: #7b8794;
    }

    .attachment-upload-box {
        min-height: 150px;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        background: #f8fafc;
        transition: .2s;
    }

    .attachment-upload-box:hover {
        border-color: #198754;
        background: #f0fdf4;
    }

    .attachment-upload-box i {
        font-size: 30px;
        margin-bottom: 7px;
        color: #198754;
    }

    .attachment-upload-box span {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
    }

    .attachment-upload-box small {
        margin-top: 4px;
        color: #9ca3af;
        font-size: 10px;
    }

    .attachment-preview {
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .attachment-preview-item,
    .attachment-viewer-item {
        position: relative;
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 6px;
        background: #fff;
    }

    .attachment-preview-item img,
    .attachment-viewer-item img {
        display: block;
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        border-radius: 6px;
        cursor: pointer;
        background: #f8fafc;
    }

    .attachment-remove {
        position: absolute;
        right: 12px;
        top: 12px;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 50%;
        background: rgba(220, 53, 69, .95);
        color: #fff;
        cursor: pointer;
        z-index: 2;
    }

    .attachment-viewer-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .attachment-viewer-item {
        padding: 8px;
    }

    .attachment-number {
        padding: 4px 7px;
        font-size: 10px;
        color: #6b7280;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .attachment-preview-item img,
        .attachment-viewer-item img {
            max-height: 350px;
        }
    }
</style>

{{-- SIGNATURE --}}
@php
    $approvalStepsByOrder = collect($editData['approval_steps'] ?? [])
        ->keyBy('step_order');

    // Fallback langsung dari User -> Karyawan -> Divisi.
    foreach ([2, 3, 4, 5, 6, 7] as $approvalOrder) {
        if (!isset($approvalStepsByOrder[$approvalOrder])) {
            continue;
        }

        $approvalUserId = $approvalStepsByOrder[$approvalOrder]['user_id'] ?? null;
        $approvalUser = $approvalUserId
            ? $users->firstWhere('id', $approvalUserId)
            : null;

        if (empty($approvalStepsByOrder[$approvalOrder]['division_name'])) {
            $approvalStepsByOrder[$approvalOrder]['division_name'] =
                optional(optional($approvalUser?->karyawan)->divisi)->nama;
        }
    }
@endphp

<div id="purchasingSignatureSection" class="signature-section">

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
                
                <td>{{ $madeByName }}</td>

                <td>{{ $approvalStepsByOrder[2]['division_name'] ?? '-' }}</td>
                <td>{{ $approvalStepsByOrder[3]['division_name'] ?? '-' }}</td>

                <td>{{ $approvalStepsByOrder[4]['division_name'] ?? '-' }}</td>
                <td>{{ $approvalStepsByOrder[5]['division_name'] ?? '-' }}</td>

                <td>{{ $approvalStepsByOrder[6]['division_name'] ?? '-' }}</td>

                <td>{{ $approvalStepsByOrder[7]['division_name'] ?? '-' }}</td>
            
            </tr>

            <tr class="signature-input-row">

                {{-- MADE BY --}}
                <td>
                    <input type="text"
                           value="{{ $madeByName }}"
                           readonly
                           class="signature-input">
                </td>

                {{-- CHECKED BY 1 --}}
                <td>
                    <select class="signature-select"
                            id="checked_by_1" {{ $approvalLocked ? "disabled" : "" }}>
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
                            id="checked_by_2" {{ $approvalLocked ? "disabled" : "" }}>
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
                            id="checked_by_3" {{ $approvalLocked ? "disabled" : "" }}>
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
                            id="checked_by_4" {{ $approvalLocked ? "disabled" : "" }}>
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
                            id="checked_by_finance" {{ $approvalLocked ? "disabled" : "" }}>
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
                            id="approved_by" {{ $approvalLocked ? "disabled" : "" }}>
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
    const CURRENT_USER_ID = @json((int) auth()->id());
    const CURRENT_USER_NAME = @json(auth()->user()->name ?? '');
    const APPROVAL_STEPS = @json($editData['approval_steps'] ?? []);
    const IS_PUBLISHED = @json(!empty($editPengajuan) && (int) ($editPengajuan->is_draft ?? 0) === 1);

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


        // Reset attachment
        attachmentFiles = [];
        $('#attachmentInput').val('');
        $('#attachmentPreview').empty();

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


    /*
     * APPROVAL UI
     *
     * Select nama approver TIDAK boleh diubah oleh approver.
     * Hanya user yang ID-nya sama dengan user yang ditugaskan
     * pada step tersebut yang mendapatkan tombol "Tanda Tangan".
     *
     * Creator boleh mengatur assignment selama masih draft.
     * Setelah publish, assignment dikunci.
     */
    function renderApprovalButtons() {
        if (!EDIT_MODE || !Array.isArray(APPROVAL_STEPS)) {
            return;
        }

        const fieldMap = {
            2: '#checked_by_1',
            3: '#checked_by_2',
            4: '#checked_by_3',
            5: '#checked_by_4',
            6: '#checked_by_finance',
            7: '#approved_by'
        };

        Object.keys(fieldMap).forEach(function (order) {
            const field = $(fieldMap[order]);
            if (!field.length) return;

            const step = APPROVAL_STEPS.find(function (item) {
                return String(item.step_order) === String(order);
            });

            if (!step) return;

            // Published = assignment terkunci.
            if (IS_PUBLISHED || !CAN_EDIT) {
                field.prop('disabled', true);
            }

            // Hapus tombol lama jika ada.
            field.closest('td').find('.approval-tap-wrap').remove();

            const assignedUserId = Number(step.user_id || 0);
            const assignedUserName = String(step.user_name || '');
            const status = String(step.status || 'pending').toLowerCase();

            const cell = field.closest('td');

            if (!assignedUserName) {
                cell.append(
                    '<div class="approval-waiting">Belum ada approver</div>'
                );
                return;
            }

            // Sudah ditandatangani.
            if (status === 'approved') {
                cell.append(
                    '<div class="approval-tap-wrap">' +
                        '<span class="approval-tap-done">' +
                            '<i class="fa fa-check-circle"></i> Sudah TTD' +
                        '</span>' +
                    '</div>'
                );
                return;
            }

            // Hanya user yang memang ditugaskan yang boleh tapping.
            if (assignedUserId === CURRENT_USER_ID) {
                cell.append(
                    '<div class="approval-tap-wrap">' +
                        '<button type="button" ' +
                            'class="approval-tap-btn" ' +
                            'data-step-order="' + order + '" ' +
                            'data-step-name="' + escapeHtml(assignedUserName) + '">' +
                            '<i class="fa fa-signature"></i> Tanda Tangan' +
                        '</button>' +
                    '</div>'
                );
            } else {
                cell.append(
                    '<div class="approval-tap-wrap">' +
                        '<span class="approval-waiting">' +
                            'Menunggu ' + escapeHtml(assignedUserName) +
                        '</span>' +
                    '</div>'
                );
            }
        });
    }

    $(document).off('click.purchasingApproval', '.approval-tap-btn');

    $(document).on('click.purchasingApproval', '.approval-tap-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const button = $(this);
        const stepOrder = button.data('step-order');
        const stepName = button.data('step-name') || 'approver';

        if (!stepOrder) return;

        if (!confirm(
            'Konfirmasi tanda tangan\n\n' +
            'Anda akan menandatangani sebagai:\n' +
            stepName +
            '\n\nLanjutkan?'
        )) {
            return;
        }

        button
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: "{{ route('pengajuan_purchasing.approve_step', ['id' => '__ID__']) }}"
                .replace('__ID__', EDIT_DATA.id),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: "{{ csrf_token() }}",
                step_order: stepOrder
            },

            success: function (response) {
                if (!response.success) {
                    alert(response.message || 'Tanda tangan gagal disimpan.');
                    button
                        .prop('disabled', false)
                        .html('<i class="fa fa-signature"></i> Tanda Tangan');
                    return;
                }

                alert(response.message || 'Tanda tangan berhasil disimpan.');

                const cell = button.closest('td');

                button.replaceWith(
                    '<span class="approval-tap-done">' +
                        '<i class="fa fa-check-circle"></i> Sudah TTD' +
                    '</span>'
                );

                // Update state lokal agar reload/aksi berikutnya konsisten.
                const step = APPROVAL_STEPS.find(function (item) {
                    return String(item.step_order) === String(stepOrder);
                });

                if (step) {
                    step.status = 'approved';
                    step.approved_at = response.approved_at || null;
                }
            },

            error: function (xhr) {
                console.error(
                    'APPROVAL ERROR:',
                    xhr.responseJSON || xhr.responseText
                );

                let message = 'Gagal menyimpan tanda tangan.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);

                button
                    .prop('disabled', false)
                    .html('<i class="fa fa-signature"></i> Tanda Tangan');
            }
        });
    });

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
    // ATTACHMENT
    // ============================================================

    let attachmentFiles = [];

    const EXISTING_ATTACHMENT_FILES = @json(
        !empty($editPengajuan) && isset($editPengajuan->files)
            ? $editPengajuan->files->where('type', 'image')->pluck('file_path')->values()
            : []
    );

    function attachmentUrl(path) {
        if (!path) return '';
        path = String(path).replace(/^\/+/, '');
        if (path.indexOf('storage/') === 0) {
            return "{{ url('/') }}/" + path;
        }
        return "{{ asset('storage') }}/" + path;
    }

    function renderAttachmentPreview() {
        const container = $('#attachmentPreview');
        container.empty();

        attachmentFiles.forEach(function(file, index) {
            const url = URL.createObjectURL(file);

            container.append(`
                <div class="attachment-preview-item">
                    <button type="button"
                            class="attachment-remove"
                            data-index="${index}"
                            title="Hapus gambar">
                        <i class="fa fa-times"></i>
                    </button>

                    <img src="${url}"
                         alt="Attachment ${index + 1}"
                         title="Klik untuk membuka gambar">
                </div>
            `);
        });
    }

    function renderExistingAttachments() {
        const container = $('#attachmentViewerList');
        container.empty();

        if (!EXISTING_ATTACHMENT_FILES || EXISTING_ATTACHMENT_FILES.length === 0) {
            $('#attachmentViewerEmpty').show();
            return;
        }

        $('#attachmentViewerEmpty').hide();

        EXISTING_ATTACHMENT_FILES.forEach(function(path, index) {
            const url = attachmentUrl(path);

            container.append(`
                <div class="attachment-viewer-item">
                    <div class="attachment-number">
                        Attachment ${index + 1}
                    </div>

                    <img src="${url}"
                         alt="Attachment ${index + 1}"
                         title="Klik untuk membuka gambar"
                         onerror="this.closest('.attachment-viewer-item').style.display='none';">
                </div>
            `);
        });
    }

    // ============================================================
    // ATTACHMENT VIEWER - AUTO LOAD UNTUK VIEWER / APPROVER
    // ============================================================
    if (EDIT_MODE && !CAN_EDIT) {
        $('#attachmentSection').css({ display: "block", visibility: "visible" });
        $('#attachmentCreator').hide();
        $('#attachmentViewer').css({ display: "block", visibility: "visible" });
        renderExistingAttachments();
    }

    function openAttachmentSection() {
        $('#attachmentSection').slideToggle(180);

        if (!$('#attachmentSection').is(':visible')) {
            return;
        }

        if (CAN_EDIT) {
            $('#attachmentCreator').show();
            $('#attachmentViewer').hide();
        } else {
            $('#attachmentCreator').hide();
            $('#attachmentViewer').show();
            renderExistingAttachments();
        }
    }

    // Tombol Attachment
    $(document).off('click.purchasingAttachment', '#btnSubmitRequest');
    $(document).on('click.purchasingAttachment', '#btnSubmitRequest', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!CAN_EDIT) {
            $('#attachmentSection').show();
            $('#attachmentCreator').hide();
            $('#attachmentViewer').show();
            renderExistingAttachments();
            return;
        }

        $('#attachmentSection').show();
        $('#attachmentCreator').show();
        $('#attachmentViewer').hide();
    });

    // Browse image
    $(document).off('click.purchasingAttachmentBrowse', '#attachmentBrowseBox');
    $(document).on('click.purchasingAttachmentBrowse', '#attachmentBrowseBox', function() {
        if (!CAN_EDIT) return;
        $('#attachmentInput').trigger('click');
    });

    // Pilih banyak image
    $(document).off('change.purchasingAttachmentInput', '#attachmentInput');
    $(document).on('change.purchasingAttachmentInput', '#attachmentInput', function() {
        if (!CAN_EDIT) return;

        const selected = Array.from(this.files || []);

        selected.forEach(function(file) {
            if (!file.type || !file.type.startsWith('image/')) {
                return;
            }

            const duplicate = attachmentFiles.some(function(existing) {
                return existing.name === file.name &&
                       existing.size === file.size &&
                       existing.lastModified === file.lastModified;
            });

            if (!duplicate) {
                attachmentFiles.push(file);
            }
        });

        renderAttachmentPreview();

        // Reset input supaya file yang sama masih bisa dipilih lagi.
        $(this).val('');
    });

    // Hapus preview sebelum upload
    $(document).off('click.purchasingAttachmentRemove', '.attachment-remove');
    $(document).on('click.purchasingAttachmentRemove', '.attachment-remove', function() {
        if (!CAN_EDIT) return;

        const index = Number($(this).data('index'));

        if (!Number.isNaN(index)) {
            attachmentFiles.splice(index, 1);
            renderAttachmentPreview();
        }
    });

    // Klik gambar -> buka tab baru
    $(document).off('click.purchasingAttachmentImage',
        '.attachment-preview-item img, .attachment-viewer-item img');

    $(document).on('click.purchasingAttachmentImage',
        '.attachment-preview-item img, .attachment-viewer-item img',
        function() {
            const src = $(this).attr('src');
            if (src) {
                window.open(src, '_blank');
            }
        });

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

    // Render tombol Tanda Tangan SETELAH data pengajuan
    // dan assignment approver selesai dimuat.
    renderApprovalButtons();

    if (EDIT_MODE && !CAN_EDIT) {
        $('#requestDate, #department, #neededDate').prop('disabled', true);
        $('#materialSearch, #btnSearchMaterial, #requestQty, #requestReason').prop('disabled', true);
        $('#btnAddMaterial, #btnAddNewMaterial, #btnCancelNewMaterial, #btnAddManualRow').prop('disabled', true);
        $('#btnSaveRequest, #btnClearCache').prop('disabled', true);
        
        $('.remove-row').prop('disabled', true);

        $('#checked_by_1, #checked_by_2, #checked_by_3, #checked_by_4, #checked_by_finance, #approved_by')
            .prop('disabled', true);

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
    $(document).off('click.purchasingDetail', '.btn-view-submission');
    $(document).on('click.purchasingDetail', '.btn-view-submission', function () {
        const id = $(this).data('id');
        if (!id) return;

        let url = "{{ url('/pengajuan_purchasing/edit') }}/" + id;
        if (String($(this).data('view-only')) === '1') {
            url += '?view_only=1';
        }
        window.location.href = url;
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


<style>
    .approval-tap-wrap {
        margin-top: 5px;
        text-align: center;
    }

    .approval-tap-btn {
        border: 0;
        border-radius: 5px;
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        background: #198754;
        color: #fff;
        white-space: nowrap;
    }

    .approval-tap-btn:hover {
        opacity: .9;
    }

    .approval-tap-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .approval-tap-done {
        display: inline-block;
        margin-top: 5px;
        padding: 3px 7px;
        border-radius: 5px;
        background: #e8f7ee;
        color: #198754;
        font-size: 10px;
        font-weight: 700;
    }

    .approval-waiting {
        display: inline-block;
        margin-top: 5px;
        font-size: 9px;
        color: #9ca3af;
    }

    .signature-assigned-name {
        font-weight: 700;
    }
</style>


{{-- ============================================================
     FLOATING NAVIGATION
     ============================================================ --}}
<div id="purchasingFloatingNav" class="purchasing-floating-nav">
    <div id="purchasingFloatingHandle" class="purchasing-floating-handle">
        <span><i class="fa fa-bars"></i> Navigasi</span>
        <i class="fa fa-arrows-alt"></i>
    </div>

    <div class="purchasing-floating-body">
        <button type="button"
                class="purchasing-nav-btn"
                data-scroll-target="#purchasingFormSection">
            <i class="fa fa-list"></i>
            <span>List Form</span>
        </button>

        <button type="button"
                class="purchasing-nav-btn"
                data-scroll-target="#purchasingSignatureSection">
            <i class="fa fa-pencil"></i>
            <span>Signature</span>
        </button>
    </div>
</div>

<style>
    .purchasing-floating-nav {
        position: fixed;
        z-index: 99999;
        right: 22px;
        bottom: 24px;
        width: 150px;
        background: #fff;
        border: 1px solid #d9e0e7;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.15);
        overflow: hidden;
        user-select: none;
    }

    .purchasing-floating-handle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        background: #243447;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        cursor: move;
    }

    .purchasing-floating-handle i:last-child {
        font-size: 10px;
        opacity: .8;
    }

    .purchasing-floating-body {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 6px;
    }

    .purchasing-nav-btn {
        width: 100%;
        border: 0;
        border-radius: 6px;
        background: #f5f7fa;
        color: #344054;
        padding: 8px 9px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: .15s;
    }

    .purchasing-nav-btn:hover,
    .purchasing-nav-btn.active {
        background: #243447;
        color: #fff;
    }

    .purchasing-nav-btn i {
        width: 18px;
        margin-right: 5px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .purchasing-floating-nav {
            right: 10px;
            bottom: 12px;
            width: 135px;
        }
    }
</style>

<script>
(function () {
    function initPurchasingFloatingNav() {
        const nav = document.getElementById('purchasingFloatingNav');
        const handle = document.getElementById('purchasingFloatingHandle');

        if (!nav || !handle || nav.dataset.initialized === '1') return;
        nav.dataset.initialized = '1';

        nav.querySelectorAll('.purchasing-nav-btn').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const target = document.querySelector(
                    button.getAttribute('data-scroll-target')
                );

                if (!target) return;

                const top =
                    target.getBoundingClientRect().top +
                    window.pageYOffset -
                    75;

                window.scrollTo({
                    top: Math.max(0, top),
                    behavior: 'smooth'
                });

                nav.querySelectorAll('.purchasing-nav-btn')
                    .forEach(function (btn) {
                        btn.classList.remove('active');
                    });

                button.classList.add('active');
            });
        });

        // Drag mouse + touch.
        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        function point(e) {
            if (e.touches && e.touches.length) {
                return {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY
                };
            }

            return {
                x: e.clientX,
                y: e.clientY
            };
        }

        function startDrag(e) {
            const p = point(e);
            const rect = nav.getBoundingClientRect();

            dragging = true;
            startX = p.x;
            startY = p.y;
            startLeft = rect.left;
            startTop = rect.top;

            nav.style.left = startLeft + 'px';
            nav.style.top = startTop + 'px';
            nav.style.right = 'auto';
            nav.style.bottom = 'auto';

            document.body.style.userSelect = 'none';

            if (e.type === 'touchstart') e.preventDefault();
        }

        function moveDrag(e) {
            if (!dragging) return;

            const p = point(e);
            const dx = p.x - startX;
            const dy = p.y - startY;

            const maxLeft = Math.max(5, window.innerWidth - nav.offsetWidth - 5);
            const maxTop = Math.max(5, window.innerHeight - nav.offsetHeight - 5);

            nav.style.left = Math.max(
                5,
                Math.min(maxLeft, startLeft + dx)
            ) + 'px';

            nav.style.top = Math.max(
                5,
                Math.min(maxTop, startTop + dy)
            ) + 'px';

            if (e.type === 'touchmove') e.preventDefault();
        }

        function endDrag() {
            dragging = false;
            document.body.style.userSelect = '';
        }

        handle.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', moveDrag);
        document.addEventListener('mouseup', endDrag);

        handle.addEventListener('touchstart', startDrag, { passive: false });
        document.addEventListener('touchmove', moveDrag, { passive: false });
        document.addEventListener('touchend', endDrag);

        function updateActive() {
            const form = document.querySelector('#purchasingFormSection');
            const signature = document.querySelector('#purchasingSignatureSection');

            if (!form || !signature) return;

            const marker = window.scrollY + 140;
            const active =
                marker >= signature.offsetTop
                    ? '#purchasingSignatureSection'
                    : '#purchasingFormSection';

            nav.querySelectorAll('.purchasing-nav-btn').forEach(function (button) {
                button.classList.toggle(
                    'active',
                    button.getAttribute('data-scroll-target') === active
                );
            });
        }

        window.addEventListener('scroll', updateActive, { passive: true });
        updateActive();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPurchasingFloatingNav);
    } else {
        initPurchasingFloatingNav();
    }
})();
</script>

<style>
.viewer-only .viewer-action-save,
.viewer-only .viewer-action-clear,
.viewer-only .viewer-action-status {
    display: none !important;
}
.viewer-hide-search-section {
    display: none !important;
}
</style>

<script>
(function () {
    const isViewerOnly = @json(!empty($editPengajuan) && empty($canEdit));
    if (isViewerOnly) {
        document.documentElement.classList.add('viewer-only');
        document.body.classList.add('viewer-only');
    }
})();
</script>


<style>
/* ============================================================
   VIEWER: PURCHASE REQUEST STYLE
   Hanya aktif untuk viewer/approver.
   Attachment + Signature tetap memakai section existing.
   ============================================================ */

body.viewer-only #viewerPurchaseRequest {
    display: block !important;
}

body.viewer-only .page-header,
body.viewer-only .purchasing-readonly-banner,
body.viewer-only .request-info,
body.viewer-only .search-section,
body.viewer-only .table-section > .table-title,
body.viewer-only .table-section > .request-table-wrapper,
body.viewer-only .table-section > .table-footer {
    display: none !important;
}

/* Jangan hide attachment dan signature */
body.viewer-only #attachmentSection,
body.viewer-only #purchasingSignatureSection {
    display: block !important;
}

body.viewer-only #attachmentViewer {
    display: block !important;
}

body.viewer-only #attachmentCreator {
    display: none !important;
}

/* Hardening attachment viewer agar tidak kalah oleh inline display:none / JS lain */
body.viewer-only #attachmentSection,
body.viewer-only #attachmentViewer {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

body.viewer-only #attachmentCreator {
    display: none !important;
}

body.viewer-only .attachment-section {
    height: auto !important;
    overflow: visible !important;
}

body.viewer-only .attachment-viewer-list {
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
}

body.viewer-only .attachment-viewer-item {
    display: block !important;
    visibility: visible !important;
}

body.viewer-only .attachment-viewer-item img {
    display: block !important;
    visibility: visible !important;
    max-width: 100% !important;
    height: auto !important;
}

.viewer-purchase-request {
    border: 1px solid #222;
    background: #fff;
    color: #111;
}

.vpr-top {
    min-height: 76px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    border-bottom: 1px solid #222;
    align-items: stretch;
}

.vpr-brand,
.vpr-title,
.vpr-need {
    min-width: 0;
    display: flex;
    align-items: center;
}

.vpr-brand {
    padding: 8px 14px;
    justify-content: flex-start;
}

.vpr-logo {
    display: block;
    width: 150px;
    max-width: 100%;
    height: auto;
    max-height: 55px;
    object-fit: contain;
    object-position: left center;
}

.vpr-title {
    justify-content: center;
    text-align: center;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 22px;
    font-weight: 700;
    white-space: nowrap;
}

.vpr-need {
    border-left: 1px solid #222;
    padding: 8px 10px;
    align-items: flex-start;
    justify-content: flex-start;
    flex-direction: column;
    font-size: 10px;
}

.vpr-need b {
    display: block;
    margin-bottom: 8px;
}

.vpr-meta {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    border-bottom: 1px solid #222;
    font-size: 10px;
}

.vpr-meta > div {
    min-height: 35px;
    padding: 7px 9px;
    border-right: 1px solid #222;
}

.vpr-meta > div:last-child {
    border-right: 0;
}

.vpr-meta b {
    margin-right: 5px;
}

.vpr-table-wrap {
    overflow-x: auto;
}

.vpr-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    font-size: 9px;
}

.vpr-table th,
.vpr-table td {
    border: 1px solid #222;
    padding: 5px 6px;
}

.vpr-table th {
    text-align: center;
    font-weight: 700;
    background: #293B4D;
    white-space: nowrap;
}

.vpr-table td {
    height: 27px;
}

.vpr-table .center {
    text-align: center;
}

.vpr-table .right {
    text-align: right;
}

.vpr-total td {
    font-weight: 700;
}

@media (max-width: 800px) {
    .viewer-purchase-request {
        margin: 8px;
    }

    .vpr-top {
        grid-template-columns: 1fr;
    }

    .vpr-brand {
        justify-content: center;
    }

    .vpr-logo {
        width: 135px;
        max-height: 48px;
        object-position: center;
    }

    .vpr-title {
        padding: 8px;
    }

    .vpr-need {
        border-left: 0;
        border-top: 1px solid #222;
    }

    .vpr-meta {
        grid-template-columns: 1fr;
    }

    .vpr-meta > div {
        border-right: 0;
        border-bottom: 1px solid #222;
    }
}
</style>