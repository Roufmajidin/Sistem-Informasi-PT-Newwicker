@extends('master.master')
@section('title', 'Pengajuan')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="box">

        {{-- ====== HEADER ====== --}}
        <div class="box-header d-flex justify-content-between align-items-center">
            <div>
                <h2>Pengajuan</h2>

                <div style="display:flex; align-items:center; gap:10px; margin-top:10px;">

                    <!-- FILTER TYPE -->
                    <select id="filter-type" class="form-control" style="width:180px;">
                        <option value="">-- Semua --</option>
                        {{-- <option value="Purchasing">Purchasing</option> --}}

                        <option value="All Divisi">All Divisi</option>
                        <option value="Finance">Finance</option>
                        <option value="Spk">Pengajuan SPK</option>
                    </select>

                    <!-- QR BUTTON -->
                    <div id="btn-qr"
                        style="
        width:40px;
        height:40px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#007bff;
        color:#fff;
        border-radius:8px;
        cursor:pointer;
    ">
                        <i class="fa fa-qrcode"></i>
                    </div>



                    <button class="btn btn-primary btn-sm" id="btn-add">
                        + Pengajuan
                    </button>
                </div>
            </div>


        </div>
        <div id="modal-qr" class="modal-qr">

            <div style="background:#111; padding:20px; border-radius:10px; text-align:center;opacity:0.95;">

                <h4 style="color:#fff;">Scan QR Pengajuan</h4>

                <!-- KOTAK SCANNER -->
                <div id="qr-reader"
                    style="
            width:300px;
            height:300px;
            margin:auto;
            border-radius:10px;
            overflow:hidden;
        ">
                </div>

                <br>

                <button id="close-qr" class="btn btn-danger">Tutup</button>

            </div>

        </div>


    </div>
    {{-- ====== BODY (DUMMY TABLE) ====== --}}
    <div class="box-body">
        @php
            $dummy = [
                ['no' => 'A-001', 'user' => 'Admin', 'status' => 'approved', 'date' => now()],
                ['no' => 'A-002', 'user' => 'RND', 'status' => 'pending', 'date' => now()],
            ];
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead style="background:#f5f5f5">
                    <tr>
                        <th>No</th>
                        <th>No Pengajuan</th>
                        <th>Uploaded By</th>
                        <th>Status</th>
                        <th>peng. v</th>
                        <th>Created At</th>
                        <th>Qr</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <div id="not-allowed-msg"
                        style="
    display:none;
    margin-bottom:10px;
    padding:10px;
    background:#fff1f0;
    color:#cf1322;
    border:1px solid #ffa39e;
    border-radius:6px;
">
                    </div>
            </table>
        </div>
    </div>

    </div>
    </div>
    <!-- modal preview  -->
    {{-- ================= MODAL VIEW ================= --}}
    <div id="modal-view" class="modal-full">
        <div class="modal-full-content">

            <div class="modal-header">
                <h4>Detail Pengajuan</h4>
                <button type="button" class="btn-close close-modal" id="btn-close-detail" aria-label="Close">
                    ✕
                </button>
            </div>

            <div class="modal-body">

                {{-- META --}}
                <div id="view-meta" style="margin-bottom:15px;"></div>

                {{-- CONTENT --}}
                <div id="view-content"></div>

            </div>
        </div>
    </div>


    {{-- ================= MODAL FULLSCREEN ================= --}}
    <div id="modal-pengajuan" class="modal-full">
        <div class="modal-full-content nw-pengajuan-modal-content">

            <div class="modal-header nw-pengajuan-header">
                <div class="nw-pengajuan-title-wrap">
                    <div class="nw-pengajuan-icon">＋</div>
                    <div>
                        <h4>Tambah Pengajuan</h4>
                        <small>Buat pengajuan baru dengan data yang lengkap</small>
                    </div>
                </div>
                <button type="button" class="btn-close close-modal nw-pengajuan-close" aria-label="Tutup">✕</button>
            </div>

            <div class="modal-body nw-pengajuan-body">
                <form id="form-pengajuan" onsubmit="return false;">
                    @csrf
                    <input type="hidden" name="meta_json" id="meta_json">
                    <input type="hidden" name="details_json" id="details_json">
                    <input type="hidden" name="approval_json" id="approval_json">
                    <input type="file" id="cameraUpload" accept="image/*" capture="environment" multiple
                        style="display:none;">

                    <div class="nw-form-card">
                        <div class="nw-card-heading">
                            <span class="nw-heading-number">01</span>
                            <div>
                                <strong>Informasi Pengajuan</strong>
                                <small>Pilih jenis pengajuan yang akan dibuat</small>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="nw-label">Type Pengajuan <span>*</span></label>
                            <select name="type_pengajuan" class="form-control nw-control" required>
                                <option value="">-- pilih --</option>
                                <option value="All Divisi">All Divisi</option>
                                <option value="Finance">Finance</option>
                            </select>
                        </div>
                    </div>

                    {{-- FINANCE: struktur/ID dipertahankan agar JS lama tetap bekerja --}}
                    <div id="finance-section" class="nw-form-card nw-finance-card" style="display:none;">
                        <div class="nw-card-heading">
                            <span class="nw-heading-number">02</span>
                            <div>
                                <strong>Dokumen Finance</strong>
                                <small>Upload Excel untuk membaca detail pembayaran</small>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="nw-label">Upload Excel <span>*</span></label>
                            <div class="nw-upload-box">
                                <div class="nw-upload-icon">↥</div>
                                <div class="nw-upload-copy">
                                    <strong>Pilih file Excel</strong>
                                    <small>Format .xlsx atau .xls</small>
                                </div>
                                <input type="file" id="excelInput" accept=".xlsx,.xls"
                                    class="form-control nw-file-input">
                            </div>
                        </div>

                        <div id="excel-meta" class="nw-excel-meta" style="margin-bottom:15px; display:none;">
                            <div class="nw-meta-item">
                                <span>Tanggal</span>
                                <strong id="meta-tanggal">-</strong>
                            </div>
                            <div class="nw-meta-item">
                                <span>Nomor</span>
                                <strong id="meta-nomor">-</strong>
                            </div>
                            <div class="nw-meta-item">
                                <span>Type Pembayaran</span>
                                <strong id="meta-type">-</strong>
                            </div>
                        </div>

                        <div id="excel-preview" class="excel-wrapper nw-excel-preview"
                            style="overflow:auto; max-height:360px; border:1px solid #ddd;">
                            <table id="excel-table" class="table table-bordered">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="nw-form-card" id="normal-information-card">
                        <div class="nw-card-heading">
                            <span class="nw-heading-number">03</span>
                            <div>
                                <strong>Detail Pengajuan</strong>
                                <small>Lengkapi informasi pendukung</small>
                            </div>
                        </div>

                        <div class="form-group mb-3" id="no-spk-section">
                            <label class="nw-label">No. SPK</label>
                            <textarea name="no_spk" class="form-control nw-control" rows="2"
                                placeholder="Masukkan nomor SPK bila ada..."></textarea>
                        </div>

                        <div class="form-group mb-3" id="divisi-section">
                            <label class="nw-label">Divisi</label>
                            <select name="divisi_id" class="form-control nw-control">
                                <option value="">-- pilih divisi --</option>
                                @foreach ($divisis as $divisi)
                                    <option value="{{ $divisi->id }}">{{ $divisi->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3" id="camera-section">
                            <label class="nw-label">Lampiran Foto</label>
                            <div class="nw-photo-actions">
                                <label class="nw-photo-btn nw-photo-gallery">
                                    <span>▧</span> Pilih dari Galeri
                                    <input type="file" id="galleryInput" accept="image/*" multiple>
                                </label>
                                <button type="button" id="btn-camera" class="nw-photo-btn nw-photo-camera">
                                    <span>📷</span> Ambil dari Kamera
                                </button>
                            </div>
                            <small class="nw-help-text">Anda dapat memilih beberapa foto sekaligus.</small>
                        </div>

                        <div id="preview-container" class="nw-image-preview"></div>

                        <div class="form-group mb-3" id="keterangan-section">
                            <label class="nw-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control nw-control" rows="3"
                                placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                        </div>

                        <div class="form-group mb-0" id="urgent-section">
                            <label class="nw-label">Prioritas</label>
                            <div class="nw-radio-group">
                                <label class="nw-radio-card">
                                    <input type="radio" name="urgent" value="1">
                                    <span class="nw-radio-dot"></span>
                                    <span><strong>Urgent</strong><small>Perlu diproses segera</small></span>
                                </label>
                                <label class="nw-radio-card active">
                                    <input type="radio" name="urgent" value="0" checked>
                                    <span class="nw-radio-dot"></span>
                                    <span><strong>Normal</strong><small>Proses sesuai antrean</small></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="nw-submit-area">
                        <button type="button" id="btn-submit" class="btn btn-success nw-submit-btn">
                            <span>✓</span> Simpan Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL ZOOM IMAGE ================= --}}
    <div id="imageModal" class="image-modal">

        <span class="close-modal" onclick="closeZoom()">✕</span>

        <div class="zoomist-container">
            <div class="zoomist-wrapper">
                <div class="zoomist-image">
                    <img id="zoomistImg" src="">
                </div>
            </div>
        </div>

    </div>

    <div id="chat-panel"
        style="
    position:fixed;
    right:0;
    top:0;
    width:350px;
    height:100%;
    background:#fff;
    border-left:1px solid #ddd;
    display:none;
    flex-direction:column;
    z-index:9999;
">
        <div
            style="
    padding:10px;
    border-bottom:1px solid #eee;
    display:flex;
    justify-content:space-between;
    align-items:center;
">
            <b>Discussion</b>

            <span id="btn-close-chat"
                style="
        cursor:pointer;
        font-size:18px;
        color:#999;
    ">✕</span>
        </div>

        <div id="chat-body" style="
    flex:1;
    overflow:auto;
    padding:10px;
    background:#f9f9f9;
"></div>
        <div style="padding:10px; border-top:1px solid #eee; display:flex; gap:8px; background:#fafafa;">

            <input id="chat-input" placeholder="Tulis pesan..."
                style="
            flex:1;
            border-radius:20px;
            border:1px solid #ddd;
            padding:8px 12px;
            outline:none;
        ">

            <button id="btn-send-chat-p"
                style="
            background:#25D366;
            color:#fff;
            border:none;
            border-radius:50%;
            width:40px;
            height:40px;
            cursor:pointer;
        ">
                ➤
            </button>

        </div>
        {{-- ================= STYLE ================= --}}


        {{-- ================= SCRIPT ================= --}}
        <style>
            /* ===== MODAL PENGAJUAN - MODERN UI ===== */
            #modal-pengajuan {
                background: rgba(15, 23, 42, .58);
                backdrop-filter: blur(5px);
            }

            #modal-pengajuan .nw-pengajuan-modal-content {
                background: #f6f8fb;
            }

            #modal-pengajuan .nw-pengajuan-header {
                background: #fff;
                border-bottom: 1px solid #e9edf3;
                padding: 18px 26px;
                position: sticky;
                top: 0;
                z-index: 20;
            }

            .nw-pengajuan-title-wrap {
                display: flex;
                align-items: center;
                gap: 13px;
            }

            .nw-pengajuan-icon {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #eaf2ff;
                color: #1769e0;
                font-size: 25px;
                font-weight: 700;
            }

            .nw-pengajuan-title-wrap h4 {
                margin: 0;
                font-size: 19px;
                font-weight: 700;
                color: #182230;
            }

            .nw-pengajuan-title-wrap small {
                color: #7b8794;
                display: block;
                margin-top: 3px;
            }

            .nw-pengajuan-close {
                border: 0;
                background: #f1f3f6;
                border-radius: 10px;
                width: 38px;
                height: 38px;
                color: #667085;
                font-size: 18px;
                cursor: pointer;
            }

            .nw-pengajuan-close:hover {
                background: #feecec;
                color: #dc3545;
            }

            .nw-pengajuan-body {
                padding: 26px;
                background: #f6f8fb;
            }

            .nw-form-card {
                max-width: 1100px;
                margin: 0 auto 16px;
                background: #fff;
                border: 1px solid #e8edf3;
                border-radius: 16px;
                padding: 22px;
                box-shadow: 0 4px 18px rgba(16, 24, 40, .04);
            }

            .nw-card-heading {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 20px;
            }

            .nw-heading-number {
                width: 34px;
                height: 34px;
                border-radius: 10px;
                background: #f0f5ff;
                color: #246bdb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .3px;
            }

            .nw-card-heading strong {
                display: block;
                color: #202938;
                font-size: 15px;
            }

            .nw-card-heading small {
                display: block;
                color: #8a94a3;
                margin-top: 2px;
            }

            .nw-label {
                display: block;
                color: #344054;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .nw-label span {
                color: #dc3545;
            }

            .nw-control {
                border: 1px solid #dfe5ec;
                border-radius: 10px;
                min-height: 44px;
                box-shadow: none !important;
                padding: 10px 12px;
            }

            .nw-control:focus {
                border-color: #7aa7ee;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, .08) !important;
            }

            .nw-upload-box {
                position: relative;
                min-height: 76px;
                border: 1px dashed #b9c7da;
                border-radius: 12px;
                background: #fafcff;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 14px;
                overflow: hidden;
            }

            .nw-upload-icon {
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: #eaf2ff;
                color: #246bdb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
            }

            .nw-upload-copy strong {
                display: block;
                font-size: 13px;
                color: #344054;
            }

            .nw-upload-copy small {
                color: #98a2b3;
            }

            .nw-file-input {
                position: absolute;
                inset: 0;
                opacity: 0;
                cursor: pointer;
                width: 100%;
                height: 100%;
            }

            .nw-excel-meta {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
            }

            .nw-meta-item {
                background: #f8fafc;
                border: 1px solid #edf0f4;
                border-radius: 10px;
                padding: 11px 13px;
            }

            .nw-meta-item span {
                display: block;
                color: #98a2b3;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: .4px;
            }

            .nw-meta-item strong {
                display: block;
                margin-top: 3px;
                color: #344054;
                font-size: 13px;
            }

            .nw-excel-preview {
                border-radius: 12px;
                background: #fff;
            }

            .nw-excel-preview table {
                margin-bottom: 0;
                white-space: nowrap;
                font-size: 12px;
            }

            .nw-excel-preview thead th {
                position: sticky;
                top: 0;
                z-index: 3;
                background: #1769e0 !important;
                color: #fff !important;
                border-color: #1769e0 !important;
            }

            .nw-excel-preview td,
            .nw-excel-preview th {
                padding: 9px 10px;
                vertical-align: middle;
            }

            .nw-photo-actions {
                display: flex;
                gap: 9px;
                flex-wrap: wrap;
            }

            .nw-photo-btn {
                min-height: 42px;
                border-radius: 10px;
                padding: 9px 14px;
                border: 1px solid #dfe5ec;
                background: #fff;
                color: #344054;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 7px;
                margin: 0;
            }

            .nw-photo-btn input {
                display: none;
            }

            .nw-photo-btn:hover {
                border-color: #9bb9e9;
                background: #f8fbff;
            }

            .nw-photo-camera {
                background: #1769e0;
                color: #fff;
                border-color: #1769e0;
            }

            .nw-photo-camera:hover {
                background: #125bc2;
                color: #fff;
            }

            .nw-help-text {
                color: #98a2b3;
                display: block;
                margin-top: 7px;
            }

            .nw-image-preview {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 12px;
                min-height: 10px;
            }

            .nw-radio-group {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .nw-radio-card {
                flex: 1;
                min-width: 200px;
                border: 1px solid #e1e7ef;
                border-radius: 11px;
                padding: 11px 13px;
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                margin: 0;
                background: #fff;
            }

            .nw-radio-card input {
                display: none;
            }

            .nw-radio-dot {
                width: 17px;
                height: 17px;
                border: 2px solid #c5ccd6;
                border-radius: 50%;
                position: relative;
                flex: none;
            }

            .nw-radio-card input:checked+.nw-radio-dot {
                border-color: #1769e0;
            }

            .nw-radio-card input:checked+.nw-radio-dot:after {
                content: '';
                position: absolute;
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #1769e0;
                left: 3px;
                top: 3px;
            }

            .nw-radio-card:has(input:checked) {
                border-color: #9bb9e9;
                background: #f8fbff;
            }

            .nw-radio-card strong {
                display: block;
                font-size: 13px;
                color: #344054;
            }

            .nw-radio-card small {
                display: block;
                color: #98a2b3;
                font-size: 11px;
                margin-top: 2px;
            }

            .nw-submit-area {
                max-width: 1100px;
                margin: 0 auto;
                padding: 2px 0 10px;
            }

            .nw-submit-btn {
                width: 100%;
                min-height: 48px;
                border: 0;
                border-radius: 12px;
                font-weight: 700;
                box-shadow: 0 5px 14px rgba(25, 135, 84, .15);
            }

            .nw-submit-btn span {
                margin-right: 5px;
            }

            @media(max-width:700px) {
                .nw-pengajuan-body {
                    padding: 14px;
                }

                .nw-form-card {
                    padding: 16px;
                    border-radius: 13px;
                }

                .nw-excel-meta {
                    grid-template-columns: 1fr;
                }

                .nw-radio-card {
                    min-width: 100%;
                }

                .nw-pengajuan-header {
                    padding: 14px 16px;
                }
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
        <script>
            // ===== FINANCE EXCEL STATE =====
            let excelMeta = {};
            let excelDetails = [];
            let excelApproval = {};
            let transferValue = 0;
            let grandTotalValue = 0;

            // ===== TYPE PENGAJUAN =====
            $(document).on('change', '[name="type_pengajuan"]', function() {
                let val = $(this).val();
                if (val === 'Finance') {
                    $('#finance-section').show();
                    $('#divisi-section, #no-spk-section, #camera-section, #keterangan-section, #urgent-section').hide();
                } else {
                    $('#finance-section').hide();
                    $('#divisi-section, #no-spk-section, #camera-section, #keterangan-section, #urgent-section').show();
                }
            });

            // ===== OPEN / CLOSE MODAL =====
            $(document).on('click', '#btn-add', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#modal-pengajuan').addClass('active');
                $('[name="type_pengajuan"]').trigger('change');
            });

            $(document).on('click', '#modal-pengajuan .close-modal', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#modal-pengajuan').removeClass('active');
            });

            // ===== EXCEL PREVIEW + PARSING =====
            $(document).on('change', '#excelInput', function(e) {
                let file = e.target.files[0];
                if (!file) return;

                let reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        let data = new Uint8Array(e.target.result);
                        let workbook = XLSX.read(data, {
                            type: 'array'
                        });
                        let sheet = workbook.Sheets[workbook.SheetNames[0]];
                        let json = XLSX.utils.sheet_to_json(sheet, {
                            header: 1
                        });

                        let meta = extractMeta(json);
                        let totals = extractTotals(json);

                        if (!meta.nomor || !meta.tanggal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Excel tidak valid',
                                text: 'Pastikan file memiliki data Nomor dan Tanggal.'
                            });
                            return;
                        }

                        $('#excel-meta').show();
                        $('#meta-tanggal').text(meta.tanggal);
                        $('#meta-nomor').text(meta.nomor);
                        $('#meta-type').text(meta.type || '-');

                        transferValue = totals.transfer;
                        grandTotalValue = totals.grand;
                        excelMeta = {
                            tanggal: meta.tanggal,
                            nomor: meta.nomor,
                            type_pembayaran: meta.type,
                            transfer: transferValue,
                            grand_total: grandTotalValue
                        };

                        let headerIndex = findHeaderRow(json);
                        excelDetails = [];

                        for (let i = headerIndex + 1; i < json.length; i++) {
                            let row = json[i];
                            if (!row || row.length === 0) continue;
                            if (typeof row[0] === 'string' && row[0].toLowerCase().includes('transfer')) break;
                            if (!row[0] || isNaN(row[0])) continue;

                            excelDetails.push({
                                no: row[0],
                                date: convertDate(row[1]),
                                no_po: row[2],
                                no_inv: row[3],
                                type_biaya: row[4],
                                nama_barang: row[5],
                                qty: parseInt(row[6]) || 0,
                                harga_satuan: parseNumber(row[7]),
                                total_harga: parseNumber(row[8])
                            });
                        }

                        excelApproval = {
                            checked_by: "YANTI SUSANTI",
                            knowing_by: "Mr Stanley",
                            approve_by: "Mr Jan",
                            record_and_cashied_1: "EKA WL",
                            record_and_cashied_2: "AINUN"
                        };

                        $('#meta_json').val(JSON.stringify(excelMeta));
                        $('#details_json').val(JSON.stringify(excelDetails));
                        $('#approval_json').val(JSON.stringify(excelApproval));

                        renderExcel(json);
                    } catch (err) {
                        console.error('Excel parse error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal membaca Excel',
                            text: 'File tidak dapat diproses.'
                        });
                    }
                };
                reader.readAsArrayBuffer(file);
            });

            $('[name="type_pengajuan"]').trigger('change');

            let lastTap = 0;

            let lastData = [];
            $(document).on('click', '.btn-delete', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: "Pengajuan akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    // 🔥 kirim ke route DELETE
                    $.ajax({
                        url: '/pengajuan/' + id,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        success: function(res) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // atau refresh table
                            });

                        },

                        error: function(err) {

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: err.responseJSON?.message || 'Tidak bisa hapus'
                            });

                        }
                    });

                });

            });
            // downloads
            $(document).on('click', '.btn-download-qr', function() {

                let id = $(this).data('id');
                let type = $(this).data('type');
                let user = $(this).data('user');
                let date = $(this).data('date');

                let textQR = 'A-' + id;

                let qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(id)}`;

                let img = new Image();
                img.crossOrigin = "anonymous";

                img.onload = function() {

                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');

                    canvas.width = 420;
                    canvas.height = 550;

                    // 🔥 background putih
                    ctx.fillStyle = "#fff";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // 🔥 border
                    ctx.strokeStyle = "#000";
                    ctx.lineWidth = 2;
                    ctx.strokeRect(10, 10, 400, 530);

                    // =========================
                    // 🔥 TITLE
                    // =========================
                    ctx.fillStyle = "#000";
                    ctx.font = "bold 24px Arial";
                    ctx.textAlign = "center";

                    ctx.fillText("E-Ticket Pengajuan", canvas.width / 2, 35);

                    // =========================
                    // 🔥 QR CODE
                    // =========================
                    ctx.drawImage(img, 110, 60, 200, 200);

                    // garis pemisah
                    ctx.beginPath();
                    ctx.moveTo(40, 285);
                    ctx.lineTo(380, 285);
                    ctx.stroke();

                    // =========================
                    // 🔥 DETAIL TEXT
                    // =========================
                    ctx.textAlign = "left";
                    ctx.font = "16px Arial";

                    ctx.fillText("Type Pengajuan : " + type, 40, 320);
                    ctx.fillText("Created by     : " + user, 40, 350);
                    ctx.fillText("Created at     : " + formatDate(date), 40, 380);

                    // =========================
                    // 🔥 FOOTER
                    // =========================
                    ctx.font = "13px Arial";
                    ctx.fillStyle = "#666";

                    ctx.fillText("Generated by System", 40, 500);

                    // =========================
                    // 🔥 DOWNLOAD
                    // =========================
                    let link = document.createElement('a');

                    link.download = `QR_A-${id}.png`;
                    link.href = canvas.toDataURL();

                    link.click();
                };

                img.src = qrUrl;
            });

            function fixImageOrientation(file, callback) {
                let reader = new FileReader();

                reader.onload = function(e) {
                    let img = new Image();

                    img.onload = function() {

                        let canvas = document.createElement('canvas');
                        let ctx = canvas.getContext('2d');

                        let width = img.width;
                        let height = img.height;

                        // 🔥 kalau portrait → jadikan landscape
                        if (height > width) {

                            canvas.width = height;
                            canvas.height = width;

                            // 🔥 FIX ROTASI (pakai -90)
                            ctx.translate(height / 2, width / 2);
                            ctx.rotate(-90 * Math.PI / 180);
                            ctx.drawImage(img, -width / 2, -height / 2);

                        } else {

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0);

                        }

                        canvas.toBlob(function(blob) {
                            callback(blob);
                        }, 'image/jpeg', 0.9);
                    };

                    img.src = e.target.result;
                };

                reader.readAsDataURL(file);
            }
            // qr
            let html5QrCode = null;

            // =========================
            // OPEN QR
            // =========================


            $('#btn-qr').on('click', function() {

                $('#modal-qr').addClass('active');
                startScanner();

            });
            let scanner = null;
            let lastScanAt = 0;

            function startScanner() {
                if (scanner) return;

                scanner = new Html5QrcodeScanner(
                    "qr-reader", {
                        fps: 15, // lebih banyak frame → peluang decode naik
                        qrbox: {
                            width: 260,
                            height: 260
                        }
                    },
                    false
                );

                scanner.render(onScanSuccess, () => {});
            }
            $(document).on('click', '#close-qr', function() {

                stopScanner();
                $('#modal-qr').removeClass('active');
            });

            function onScanSuccess(decodedText) {
                const now = Date.now();
                let beep = new Audio('/assets/beep.mp3');
                beep.preload = 'auto';
                // anti double / flapping
                if (now - lastScanAt < 2000) return;
                lastScanAt = now;

                console.log("✅ DETECTED:", decodedText);
                beep.currentTime = 0;
                beep.play().catch(() => {});
                stopScanner();
                $('#modal-qr').removeClass('active');

                // validasi angka
                const id = decodedText.trim();
                if (!/^\d+$/.test(id)) {
                    alert('QR tidak valid');
                    return;
                }
                openPengajuanDetail(id);
            }

            function stopScanner() {
                if (scanner) {
                    scanner.clear().catch(() => {});
                    scanner = null;
                }
            }
            $('#filter-type').on('change', function() {

                let type = $(this).val();
                // jumping to the purchasing tab
                if (type === 'Purchasing') {
                    window.location.href = '/pengajuan_purchasing?published=true';
                    return;
                }
                $.ajax({
                    url: '/pengajuan/list',
                    data: {
                        type: type
                    },
                    success: function(res) {
                        $('#not-allowed-msg').hide();
                        lastData = res; // 🔥 simpan

                        let html = '';

                        res.forEach((item, i) => {
                            let allApproved = true;

                            if (item.approval_steps && item.approval_steps.length > 0) {
                                allApproved = item.approval_steps.every(s => s.status ===
                                    'approved');
                            }

                            // =========================
                            // 🔥 BUTTON EXPORT (CONDITIONAL)
                            // =========================
                            let btnExport = '';
                            if (item.user_id == window.authUserIdd) {
                                btnDelete = `
        <button class="btn btn-sm btn-danger btn-delete"
            data-id="${item.id}">
            Delete
        </button>
    `;
                                if (allApproved) {
                                    btnExport = `
        <button class="btn btn-sm btn-primary btn-export-excel" data-id="${item.id}">
            Export
        </button>
            ${btnDelete}

    `;
                                }

                            }
                            // =========================
                            // 🔥 LOGIC STATUS
                            // =========================
                            // =========================
                            // 🔥 LOGIC STATUS + TIMELINE
                            // =========================
                            let statusHtml = '';

                            if (item.type_pengajuan === 'All Divisi') {

                                if (item.status === 'approved') {

                                    let tanggal = item.approved_date ?
                                        formatDateTime(item.approved_date) :
                                        '-';
                                    statusHtml = `
            <span class="badge badge-success">
                Approved<br>
                <small>on ${tanggal} </small>
            </span>
        `;
                                } else {
                                    statusHtml = '<span class="badge badge-warning">Pending</span>';
                                }



                            } else if (item.type_pengajuan === 'Finance') {

                                if (item.status === 'approved') {
                                    statusHtml =
                                        '<span class="badge badge-success">Approved</span>';
                                } else {

                                    // 🔥 cari step yang masih pending
                                    let pendingStep = item.approval_steps.find(s => s.status ===
                                        'pending');

                                    if (pendingStep) {
                                        statusHtml = `
                                <span class="badge badge-warning">
                                    ⏳ ${pendingStep.step_name} - ${pendingStep.user_name ?? '-' } - ${pendingStep.status ?? '-' }
                                </span>
                            `;
                                    }
                                }

                            } else {
                                statusHtml = '<span class="badge badge-warning">Pending</span>';
                            }

                            // =========================
                            // 🔥 BUTTON VIEW
                            // =========================
                            let btnView = `
                    <button class="btn btn-sm btn-info btn-view-pengajuan" data-id="${item.id}">
                        View
                    </button>

                    <button
                        class="btn btn-sm btn-success btn-download-qr"
                        data-id="${item.id}"
                        data-type="${item.type_pengajuan}"
                        data-user="${item.user ? item.user.name : '-'}"
                        data-date="${item.created_at}"
                    > QR</button>
                 ${btnExport}


                `;

                            html += `
                    <tr>
                        <td>${i+1}</td>
                        <td><b>A-${item.id}</b></td>
                        <td>${item.user ? item.user.name : '-'}</td>
                        <td>${statusHtml}</td>
                          <td>
        ${item.urgent == 1
            ? '<span class="badge bg-danger">Urgent</span>'
            : '<span class="badge bg-secondary">Normal</span>'}
    </td>

                        <td>${formatDate(item.created_at)}</td>
                        <td>${btnView}</td>
                    </tr>
                `;
                        });

                        $('#table-body').html(html);
                    },
                    error: function(err) {

                        if (err.status === 403) {

                            let msg = err.responseJSON?.message || 'You are not allowed';

                            showNotAllowed(msg);

                            // 🔥 kosongkan tabel biar gak misleading
                            $('#table-body').html(`
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:#999;">
                        Tidak ada data
                    </td>
                </tr>
            `);
                        }
                    }
                });

            });

            // =========================
            // 🔥 CLICK VIEW (EXPAND)
            // =========================
            $(document).on('click', '.btn-view-pengajuan', function() {
                let id = $(this).data('id');
                openPengajuanDetail(id);
            });

            function getQueryParam(param) {
                let urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }
            //    open MOdal
            function openPengajuanDetail(id) {

                $.ajax({
                    url: '/pengajuan/detail/' + id,
                    success: function(res) {
                        currentPengajuanId = id;

                        console.log(res);

                        // buka modal
                        $('#modal-view').addClass('active');

                        // =========================
                        // META
                        // =========================
                        let metaHtml = '';

                        if (res.meta) {
                            metaHtml = `
                    <div style="display:flex; gap:30px; margin-bottom:10px;">
                        <div><b>Tanggal:</b> ${res.meta.tanggal ?? '-'}</div>
                        <div><b>Nomor:</b> ${res.meta.nomor ?? '-'}</div>
                        <div><b>Type:</b> ${res.meta.type_pembayaran ?? '-'}</div>
                    </div>
                        <div id="btn-chat" style="
                        width:35px;
                        height:35px;
                        background:#ffc107;
                        border-radius:8px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        cursor:pointer;
                    ">
                        💬
                    </div>
                `;
                        }

                        $('#view-meta').html(metaHtml);

                        let contentHtml = '';
                        if (res.user_id == window.authUserIdd) {

                            contentHtml += `
        <div style="margin-bottom:15px;">
            <button
                class="btn btn-primary btn-add-image"
                data-id="${res.id}">
                📷 Tambah Gambar
            </button>

            <input
                type="file"
                id="addImageInput"
                multiple
                accept="image/*"
                style="display:none;">
        </div>
    `;
                        }
                        // =========================
                        // 🔥 FINANCE → TABLE
                        // =========================
                        if (res.type_pengajuan === 'Finance') {

                            contentHtml += `
                    <div class="excel-wrapper">
                        <table class="table table-bordered">
                            <thead>
                                <tr style="background:#007bff;color:#fff;">
                                    <th>No</th>
                                    <th>Date</th>
                                    <th>NO PO</th>
                                    <th>NO. INV / NO. SPK</th>
                                    <th>TYPE BIAYA</th>
                                    <th>Nama Barang/Item/Jasa</th>
                                    <th>QTY</th>
                                    <th>Estimasi Harga Satuan</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                            // isi finance
                            // =========================
                            // 🔥 PISAHKAN UPAH HARIAN
                            // =========================
                            let upahHarian = [];
                            let lainnya = [];

                            res.details.forEach(d => {

                                let type =
                                    (d.type_biaya || '').toLowerCase();

                                if (type.includes('upah harian')) {
                                    upahHarian.push(d);
                                } else {
                                    lainnya.push(d);
                                }
                            });

                            // =========================
                            // 🔥 GABUNGKAN
                            // =========================
                            let finalDetails = [
                                ...upahHarian,
                                ...lainnya
                            ];

                            // =========================
                            // 🔥 TOTAL
                            // =========================
                            let total = 0;
                            let totalUpahHarian = 0;

                            // =========================
                            // 🔥 LOOP
                            // =========================
                            finalDetails.forEach((d, index) => {

                                let harga = Number(d.total_harga || 0);

                                total += harga;

                                let isUpah =
                                    (d.type_biaya || '')
                                    .toLowerCase()
                                    .includes('upah harian');

                                if (isUpah) {
                                    totalUpahHarian += harga;
                                }

                                contentHtml += `
        <tr class="row-inv"
            onclick="handleTap(${d.id}, this)">

            <td>${d.no}</td>
            <td>${d.date}</td>
            <td>${d.no_po}</td>
            <td>${d.no_inv}</td>
            <td>${d.type_biaya}</td>
            <td>${d.nama_barang}</td>
            <td>${d.qty}</td>
            <td>${formatRupiah(d.harga_satuan)}</td>
            <td>${formatRupiah(harga)}</td>

        </tr>
    `;

                                // =========================
                                // 🔥 FOOTER SETELAH BLOCK UPAH HARIAN
                                // =========================
                                if (
                                    upahHarian.length > 0 &&
                                    index === upahHarian.length - 1
                                ) {

                                    contentHtml += `
            <tr style="
                background:#000;
                color:#fff;
                font-weight:bold;
            ">

                <td colspan="7"
                    style="
                        text-align:right;
                        padding:18px;
                        font-size:18px;
                    ">

                    TOTAL UPAH HARIAN

                </td>

                <td colspan="2"
                    style="
                        text-align:right;
                        padding:18px;
                        font-size:24px;
                    ">

                    ${formatRupiah(totalUpahHarian)}

                </td>

            </tr>
        `;
                                }
                            });

                            // =========================
                            // 🔥 FOOTER GRAND TOTAL
                            // =========================
                            let grandTotal = res.meta?.grand_total ?? total;
                            let onHold = Number(res.meta?.on_hold ?? 0);

                            contentHtml += `
<tr style="background:#111; color:#fff;">
    <td colspan="9"
        style="
            text-align:right;
            padding:25px 20px;
        ">

        <div style="
            font-size:32px;
            font-weight:bold;
        ">
            ${formatRupiah(grandTotal)}
        </div>

        <div style="
            font-size:12px;
            color:#aaa;
            margin-top:5px;
        ">
            yang dihold
        </div>

        <div style="
            font-size:18px;
            color:#ccc;
        ">
            ${formatRupiah(onHold)}
        </div>

    </td>
</tr>
`;

                            contentHtml += `</tbody></table></div>`;
                        }

                        // =========================
                        // 🔥 ALL DIVISI → IMAGE tai
                        // =========================
                        else {

                            contentHtml += `<div style="display:flex; flex-wrap:wrap; gap:10px;">`;

                            if (res.type_pengajuan === 'All Divisi') {

                                contentHtml += `
                                <div style="margin-bottom:15px;width:100%">
                                    <button class="btn btn-success w-100 btn-approve-all"
                                        data-id="${res.id}">
                                        ✅ Approve This Pengajuan
                                    </button>
                                </div>
                                <p>hover image to rotate, scroll in/o to zoom</p>
                            `;

                                contentHtml += `<div class="pdf-container">`;

                                res.files?.forEach((f, i) => {
                                    contentHtml += `
                                    <div class="page">

                                        <div class="page-header">
                                            Halaman ${i + 1}
                                        </div>

                                        <div class="image-wrapper">
                                            <img src="/storage/${f.file_path}" class="zoomable">
                                            <button class="btn-rotate">⟳</button>
                                        </div>

                                    </div>
                                `;
                                });

                                contentHtml += `</div>`;
                            }

                            contentHtml += `</div>`;

                        }


                        // 🔥 pastikan authUserId ada
                        if (!window.authUserId) {
                            console.warn('authUserId belum diset');
                        }

                        let approvalHtml = `
                        <div style="margin-top:25px;">
                            <h5>Approval</h5>
                            <div style="display:flex; gap:80px; flex-wrap:wrap;">
                    `;

                        res.approval_steps.forEach((step, index) => {

                            // =========================
                            // STATUS
                            // =========================
                            let isApproved = step.status === 'approved';
                            let isPending = !isApproved;

                            // =========================
                            // 🔥 FIX UTAMA (ANTI GAGAL)
                            // =========================
                            let canApprove = (
                                step.user_id == window.authUserId ||
                                step.user?.id == window.authUserId
                            );

                            // =========================
                            // FALLBACK USER NAME
                            // =========================
                            let userName = step.user?.name ?? step.user_name ?? '-';

                            // =========================
                            // IMAGE STEP
                            // =========================
                            let stepNumber = index + 1;
                            let stepImage = `/assets/${stepNumber}.png`;

                            let block = '';

                            // =========================
                            // APPROVED
                            // =========================
                            if (isApproved) {

                                block = `
                        <div style="text-align:center;">
                            <small>${step.step_name}</small><br>
                            <img src="${stepImage}" width="100" height="80"><br>
                            <small>${userName}</small>
                        </div>
                    `;

                            }

                            // =========================
                            // PENDING (OPEN APPROVAL)
                            // =========================
                            else if (isPending) {

                                if (canApprove) {

                                    block = `
                                <div style="text-align:center;">
                                    <button class="btn btn-success btn-approve"
                                        data-id="${step.id}"
                                        style="padding:10px 16px; cursor:pointer;">
                                        TAP APPROVE
                                    </button><br>
                                    <small>${step.step_name}</small><br>
                                    <small>${userName}</small>
                                </div>
                            `;

                                } else {

                                    block = `
                            <div style="text-align:center;">
                                <span class="badge badge-secondary">Waiting</span><br>
                                <small>${step.step_name}</small><br>
                                <small>${userName}</small>
                            </div>
                        `;
                                }
                            }

                            approvalHtml += block;
                        });

                        // =========================
                        // CLOSE WRAPPER
                        // =========================
                        approvalHtml += `
                            </div>
                        </div>
                    `;

                        // =========================
                        // RENDER FINAL
                        // =========================
                        $('#view-content').html(contentHtml + approvalHtml);
                        initImageViewer();
                    }
                });

            }

            function initImageViewer() {
                document.querySelectorAll('.image-wrapper').forEach(wrapper => {

                    const img = wrapper.querySelector('.zoomable');
                    const rotateBtn = wrapper.querySelector('.btn-rotate');

                    if (!img) return;

                    let scale = 1;
                    let rotation = 0;
                    let posX = 0;
                    let posY = 0;

                    let isDragging = false;
                    let startX, startY;

                    function update() {
                        img.style.transform =
                            `translate(${posX}px, ${posY}px) scale(${scale}) rotate(${rotation}deg)`;

                        setTimeout(() => {
                            if (rotation % 180 !== 0) {
                                wrapper.style.height = img.offsetWidth + 'px';
                            } else {
                                wrapper.style.height = img.offsetHeight + 'px';
                            }
                        }, 30);
                    }

                    rotateBtn?.addEventListener('click', (e) => {
                        e.stopPropagation();
                        rotation += 90;
                        scale = 1;
                        posX = 0;
                        posY = 0;
                        update();
                    });

                    wrapper.addEventListener('wheel', (e) => {
                        e.preventDefault();
                        let delta = e.deltaY > 0 ? -0.1 : 0.1;
                        scale += delta;
                        if (scale < 1) scale = 1;
                        if (scale > 4) scale = 4;
                        update();
                    });

                    img.addEventListener('dblclick', () => {
                        scale = 1;
                        rotation = 0;
                        posX = 0;
                        posY = 0;
                        update();
                    });

                    img.addEventListener('mousedown', (e) => {
                        if (scale <= 1) return;
                        isDragging = true;
                        startX = e.clientX - posX;
                        startY = e.clientY - posY;
                        img.style.cursor = 'grabbing';
                    });

                    window.addEventListener('mousemove', (e) => {
                        if (!isDragging) return;
                        posX = e.clientX - startX;
                        posY = e.clientY - startY;
                        update();
                    });

                    window.addEventListener('mouseup', () => {
                        isDragging = false;
                        img.style.cursor = 'grab';
                    });

                });
            }

            function excelDateToJSDate(serial) {
                let utc_days = Math.floor(serial - 25569);
                let utc_value = utc_days * 86400;
                let date_info = new Date(utc_value * 1000);
                let day = String(date_info.getDate()).padStart(2, '0');
                let month = String(date_info.getMonth() + 1).padStart(2, '0');
                let year = date_info.getFullYear();
                return `${day}/${month}/${year}`;
            }

            function isExcelDate(value) {
                return typeof value === 'number' && value > 30000 && value < 60000;
            }

            function extractMeta(data) {
                let tanggal = '';
                let nomor = '';
                let type = '';

                function getNextValue(row, start) {
                    for (let j = start + 1; j < row.length; j++) {
                        let v = row[j];
                        if (v && v !== ':' && v !== '') return v;
                    }
                    return '';
                }
                data.forEach(row => {
                    row.forEach((cell, i) => {
                        if (typeof cell !== 'string') return;
                        let val = cell.toLowerCase().trim();
                        if (val.includes('tanggal') && !tanggal) tanggal = getNextValue(row, i);
                        if (val.includes('nomor') && !nomor) nomor = getNextValue(row, i);
                        if (val.includes('type pembayaran') && !type) type = getNextValue(row, i);
                    });
                });
                if (typeof tanggal === 'number') tanggal = excelDateToJSDate(tanggal);
                return {
                    tanggal,
                    nomor,
                    type
                };
            }

            function findHeaderRow(data) {
                for (let i = 0; i < data.length; i++) {
                    let row = data[i].join(' ').toLowerCase();
                    if (row.includes('no') && row.includes('date')) return i;
                }
                return 0;
            }

            function renderExcel(data) {
                let thead = $('#excel-table thead');
                let tbody = $('#excel-table tbody');
                thead.html('');
                tbody.html('');
                let headerIndex = findHeaderRow(data);
                let headers = data[headerIndex] || [];
                let headHtml = '<tr>';
                headers.forEach(h => headHtml += `<th>${h ?? ''}</th>`);
                headHtml += '</tr>';
                thead.html(headHtml);
                for (let i = headerIndex + 1; i < data.length; i++) {
                    let row = data[i];
                    if (!row || row.length === 0) continue;
                    let tr = '<tr>';
                    row.forEach(cell => {
                        if (isExcelDate(cell)) cell = excelDateToJSDate(cell);
                        tr += `<td>${cell ?? ''}</td>`;
                    });
                    tr += '</tr>';
                    tbody.append(tr);
                }
            }

            function extractTotals(data) {
                let transfer = 0;
                let grand = 0;
                data.forEach(row => {
                    if (!row) return;
                    row.forEach(cell => {
                        if (typeof cell === 'string' && cell.toLowerCase().includes('transfer')) {
                            let nums = row.filter(v => typeof v === 'number' || /\d/.test(v));
                            if (nums.length >= 2) {
                                transfer = parseNumber(nums[nums.length - 2]);
                                grand = parseNumber(nums[nums.length - 1]);
                            }
                        }
                    });
                });
                return {
                    transfer,
                    grand
                };
            }

            function parseNumber(val) {
                if (!val) return 0;
                return parseFloat(val.toString().replace(/[^0-9]/g, '')) || 0;
            }

            function convertDate(value) {
                if (!value) return null;
                if (typeof value === 'number') return excelDateToJSDate(value);
                if (typeof value === 'string') return value;
                return value;
            }

            function formatRupiah(angka) {
                if (!angka) return '0';
                return Number(angka).toLocaleString('id-ID');
            }
            $(document).ready(function() {

                let pengajuanId = getQueryParam('pengajuan_id');

                if (pengajuanId) {

                    console.log('AUTO OPEN:', pengajuanId);

                    // 🔥 trigger function yang sama kayak klik tombol
                    openPengajuanDetail(pengajuanId);

                }

            });

            // =========================
            // 🔥 CLICK APPROVE
            // =========================
            $(document).on('click', '.btn-approve', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin approve?',
                    text: "Data akan disetujui",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, approve!',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.post('/pengajuan/approve/' + id, {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            })
                            .done(function(res) {

                                Swal.fire('Berhasil!', 'Data sudah di-approve', 'success')
                                    .then(() => location.reload());

                            })
                            .fail(function(xhr) {

                                let res = xhr.responseJSON;

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res?.message || 'Tidak diizinkan'
                                });

                            });

                    }

                });

            });

            $('#btn-qr').on('click', function() {
                alert('Open QR Scanner'); // nanti bisa arahkan ke scanner
            });
            $('#search-pengajuan').on('keyup', function() {

                let keyword = $(this).val().toLowerCase();

                $('#table-body tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
                });

            });


            // =========================
            // 🔥 CLOSE MODAL VIEW
            // =========================

            // helper format date
            function formatDate(date) {
                let d = new Date(date);
                return d.toLocaleString();
            }

            function formatDateTime(datetime) {
                if (!datetime) return '-';

                let d = new Date(datetime);

                let tanggal = d.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });

                let jam = d.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });

                return `${tanggal} ${jam}`;
            }

            function formatDateOnly(date) {
                let d = new Date(date);
                let day = String(d.getDate()).padStart(2, '0');
                let month = String(d.getMonth() + 1).padStart(2, '0');
                let year = d.getFullYear();

                return `${day}/${month}/${year}`;
            }
        </script>
        <script>
            $(document).ready(function() {
                @if (session('error') || session('success'))
                    $('#modal-pengajuan').addClass('active');
                    $('.modal-body').scrollTop(0);
                @endif
            });
            // =========================
            // STORAGE FILE
            // =========================
            // =========================
            // GLOBAL STORAGE
            // =========================
            let allFiles = [];

            // =========================
            // PILIH DARI GALERI
            // =========================
            $('#galleryInput').on('change', function(e) {

                let files = Array.from(e.target.files);

                files.forEach(file => {
                    allFiles.push(file);
                });

                renderPreview();

            });

            // =========================
            // OPEN CAMERA
            // =========================
            $('#btn-camera').on('click', function() {

                $('#cameraInput').trigger('click');

            });

            // =========================
            // HASIL FOTO KAMERA
            // =========================
            $('#cameraInput').on('change', function(e) {

                let files = Array.from(e.target.files);

                files.forEach(file => {
                    allFiles.push(file);
                });

                renderPreview();

            });

            // =========================
            // PREVIEW
            // =========================
            async function renderPreview() {

                let container = $('#preview-container');

                container.html('');

                for (let index = 0; index < allFiles.length; index++) {

                    let file = allFiles[index];

                    await new Promise((resolve) => {

                        let reader = new FileReader();

                        reader.onload = function(e) {

                            let html = `
                    <div style="
                        width:120px;
                        position:relative;
                    ">

                        <img
                            src="${e.target.result}"
                            loading="lazy"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:10px;
                                border:1px solid #ddd;
                                cursor:pointer;
                            "
                            onclick="openZoom('${e.target.result}')"
                        >

                        <button
                            type="button"
                            class="remove-image"
                            data-index="${index}"
                            style="
                                position:absolute;
                                top:-8px;
                                right:-8px;
                                width:25px;
                                height:25px;
                                border:none;
                                border-radius:50%;
                                background:red;
                                color:white;
                                font-weight:bold;
                                cursor:pointer;
                            "
                        >
                            ×
                        </button>

                    </div>
                `;

                            container.append(html);

                            // kasih jeda kecil supaya browser napas
                            setTimeout(resolve, 30);
                        };

                        reader.readAsDataURL(file);

                    });

                }

            }
            // =========================
            // REMOVE IMAGE
            // =========================
            $(document).on('click', '.remove-image', function() {

                let index = $(this).data('index');

                allFiles.splice(index, 1);

                renderPreview();

            });
            // =========================
            // SYNC INPUT FILES
            // =========================
            function syncFiles() {

                let dt = new DataTransfer();

                allFiles.forEach(file => {
                    dt.items.add(file);
                });

                $('#cameraInput')[0].files = dt.files;

            }

            // =========================
            // RENDER PREVIEW
            // =========================
            function renderPreview() {

                $('#preview-container').html('');

                allFiles.forEach((file, index) => {

                    let reader = new FileReader();

                    reader.onload = function(e) {

                        let html = `
                <div style="
                    width:120px;
                    position:relative;
                ">

                    <img
                        src="${e.target.result}"
                        style="
                            width:120px;
                            height:120px;
                            object-fit:cover;
                            border-radius:10px;
                            border:1px solid #ddd;
                            cursor:pointer;
                        "
                        onclick="openZoom('${e.target.result}')"
                    >

                    <button
                        type="button"
                        class="remove-image"
                        data-index="${index}"
                        style="
                            position:absolute;
                            top:-8px;
                            right:-8px;
                            width:25px;
                            height:25px;
                            border:none;
                            border-radius:50%;
                            background:red;
                            color:white;
                            font-weight:bold;
                            cursor:pointer;
                        "
                    >
                        ×
                    </button>

                </div>
            `;

                        $('#preview-container').append(html);

                    };

                    reader.readAsDataURL(file);

                });

            }

            // =========================
            // REMOVE IMAGE
            // =========================
            $(document).on('click', '.remove-image', function() {

                let index = $(this).data('index');

                allFiles.splice(index, 1);

                syncFiles();
                renderPreview();

            });

            function openZoom(src) {
                document.getElementById('modalImg').src = src;
                document.getElementById('imageModal').style.display = 'flex';
            }

            function closeZoom() {
                document.getElementById('imageModal').style.display = 'none';
            }
            $(document).on('click', '.btn-approve-all', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin approve?',
                    text: 'Data akan disetujui',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, approve!',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/pengajuan/approve-all/' + id,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },

                        success: function(res) {

                            Swal.fire('Berhasil!', res.message || 'Approved', 'success')
                                .then(() => location.reload());
                        },

                        error: function(xhr) {

                            console.log(xhr);

                            let res = xhr.responseJSON;

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res?.message || 'Tidak diizinkan'
                            });
                        }
                    });

                });

            });
            // submit pengajuan
            $(document).ready(function() {

                $(document).ready(function() {

                    $('#btn-submit').off('click').on('click', function(e) {

                        e.preventDefault();

                        let form = document.getElementById('form-pengajuan');

                        let formData = new FormData(form);

                        let type = $('[name="type_pengajuan"]').val();

                        console.log('AJAX jalan');

                        // 🔥 VALIDASI
                        if (!type) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Type wajib dipilih'
                            });
                            return;
                        }

                        // =========================
                        // ALL DIVISI
                        // =========================
                        if (type === 'All Divisi') {

                            sendChunkAjax('/pengajuan/store-all-divisi', formData);

                            return;
                        }

                        // =========================
                        // FINANCE
                        // =========================
                        formData.set('meta_json', JSON.stringify(excelMeta || []));
                        formData.set('details_json', JSON.stringify(excelDetails || []));
                        formData.set('approval_json', JSON.stringify(excelApproval || []));

                        sendAjax('/pengajuan/store', formData);

                    });

                });
            });
            $(document).on('click', '.btn-add-image', function() {

                let id = $(this).data('id');

                $('#addImageInput')
                    .data('id', id)
                    .trigger('click');
            });
            $(document).on('change', '#addImageInput', function() {

                let pengajuanId = $(this).data('id');

                let fd = new FormData();

                Array.from(this.files).forEach(file => {
                    fd.append('images[]', file);
                });

                $.ajax({

                    url: '/pengajuan/add-image/' + pengajuanId,
                    method: 'POST',

                    data: fd,

                    processData: false,
                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(res) {

                        Swal.fire(
                            'Berhasil',
                            'Gambar berhasil ditambahkan',
                            'success'
                        ).then(() => {

                            openPengajuanDetail(pengajuanId);

                        });

                    },

                    error: function(xhr) {

                        Swal.fire(
                            'Gagal',
                            xhr.responseJSON?.message ??
                            'Upload gagal',
                            'error'
                        );

                    }
                });

            });
            async function sendChunkAjax(url, formData) {

                try {

                    let files = allFiles;

                    if (files.length === 0) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Foto wajib diisi'
                        });

                        return;
                    }

                    $('#btn-submit').prop('disabled', true);

                    Swal.fire({
                        title: 'Mengirim...',
                        html: '0%',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,

                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let chunkSize = 10;

                    let total = files.length;

                    let uploaded = 0;

                    let pengajuanId = null;

                    for (let i = 0; i < total; i += chunkSize) {

                        let fd = new FormData();

                        // copy semua selain image
                        for (let pair of formData.entries()) {

                            if (pair[0] !== 'images[]') {
                                fd.append(pair[0], pair[1]);
                            }
                        }

                        // append chunk image
                        let chunk = Array.from(files).slice(i, i + chunkSize);

                        chunk.forEach(file => {
                            fd.append('images[]', file);
                        });

                        // request kedua dst
                        if (pengajuanId) {
                            fd.append('pengajuan_id', pengajuanId);
                        }

                        let res = await $.ajax({

                            url: url,
                            method: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            timeout: 0,

                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        // ambil id pertama
                        if (!pengajuanId) {
                            pengajuanId = res.pengajuan_id;
                        }

                        uploaded += chunk.length;

                        let percent = Math.round((uploaded / total) * 100);

                        Swal.update({
                            html: percent + '%'
                        });
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Semua file berhasil diupload',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 2000);

                } catch (xhr) {

                    console.log(xhr);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message || 'Server error'
                    });

                } finally {

                    $('#btn-submit').prop('disabled', false);
                }
            }

            function sendAjax(url, formData) {

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    beforeSend: function() {

                        $('#btn-submit').prop('disabled', true);

                        Swal.fire({
                            title: 'Mengirim...',
                            html: 'Mohon tunggu...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,

                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },

                    success: function(res) {

                        console.log(res);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Berhasil disimpan',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    },

                    error: function(xhr) {

                        console.log(xhr);

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Server error'
                        });
                    },

                    complete: function() {
                        $('#btn-submit').prop('disabled', false);
                    }
                });
            }

            function openCameraUpload(detailId) {
                currentDetailId = detailId;

                // trigger camera
                document.getElementById('cameraUpload').click();
            }
            $('#cameraUpload').on('change', function() {

                let files = this.files;

                if (!files.length) return;

                let formData = new FormData();

                formData.append('detail_id', currentDetailId);

                let promises = [];

                Array.from(files).forEach(file => {

                    promises.push(new Promise(resolve => {

                        fixImageOrientation(file, function(blob) {
                            let fixedFile = new File([blob], file.name, {
                                type: 'image/jpeg'
                            });

                            resolve(fixedFile);
                        });

                    }));

                });

                Promise.all(promises).then(fixedFiles => {

                    fixedFiles.forEach(file => {
                        formData.append('images[]', file);
                    });

                    uploadAjax(formData); // lanjut upload
                });

                Swal.fire({
                    title: 'Uploading...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                function uploadAjax(formData) {

                    $.ajax({
                        url: '/pengajuan/upload-detail-image',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            Swal.fire('Berhasil!', 'Foto berhasil diupload', 'success');
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Upload gagal'
                            });
                        }
                    });

                }

            });
            let clickTimer = null;

            function handleClick(detailId) {

                clickTimer = setTimeout(() => {
                    openViewer(detailId);
                }, 250); // delay
            }

            function openViewer(detailId) {
                window.open('/pengajuan/view-detail/' + detailId, '_blank');
            }

            function handleDoubleClick(detailId) {

                clearTimeout(clickTimer); // 🔥 batalkan click

                openCameraUpload(detailId); // 📷 upload
            }

            function handleTap(detailId, rowEl) {
                // 🔥 RESET semua active
                document.querySelectorAll('.row-inv').forEach(r => {
                    r.classList.remove('row-active');
                });

                // 🔥 set active
                rowEl.classList.add('row-active');
                let now = new Date().getTime();
                let tapGap = now - lastTap;

                if (tapGap < 300 && tapGap > 0) {
                    // 🔥 DOUBLE TAP
                    openCameraUpload(detailId);

                } else {
                    // 🔥 SINGLE TAP
                    setTimeout(() => {
                        // kalau tidak double tap
                        if (new Date().getTime() - lastTap >= 300) {
                            openViewer(detailId);
                        }
                    }, 300);
                }

                lastTap = now;
            }

            function openCameraUpload(detailId) {

                currentDetailId = detailId;

                Swal.fire({
                    title: 'Upload foto?',
                    text: 'Akan membuka kamera',
                    icon: 'question',
                    showCancelButton: true
                }).then(res => {
                    if (res.isConfirmed) {
                        document.getElementById('cameraUpload').click();
                    }
                });

            }
            let currentPengajuanId = getQueryParam('pengajuan_id');

            $(document).on('click', '#btn-chat', function() {
                console.log('CHAT CLICKED', currentPengajuanId);

                $('#chat-panel').show();

                if (currentPengajuanId) {
                    loadMessages();
                } else {
                    console.warn('ID belum ada');
                }
            });

            function loadMessages() {

                $.get('/pengajuan/messages/' + currentPengajuanId, function(res) {

                    let html = '';
                    let currentUser = (window.authUserName || '').toLowerCase().trim();
                    console.log('AUTH USER:', window.authUserName);
                    res.forEach(m => {

                        let name = (m.user.name || '').toLowerCase().trim();
                        let isMe = name.includes(currentUser);

                        let time = new Date(m.created_at);
                        let jam = time.getHours().toString().padStart(2, '0') + ':' +
                            time.getMinutes().toString().padStart(2, '0');

                        html += `
                <div class="chat-row ${isMe ? 'chat-me' : 'chat-other'}">
                    <div class="chat-bubble">

                        ${!isMe ? `<div class="chat-name">${m.user.name}</div>` : ''}

                        <div>${m.message}</div>

                        <div class="chat-time">${jam}</div>

                    </div>
                </div>
            `;
                    });

                    $('#chat-body').html(html);

                    // 🔥 auto scroll bawah
                    setTimeout(() => {
                        let el = $('#chat-body')[0];
                        if (el) {
                            $('#chat-body').scrollTop(el.scrollHeight);
                        }
                    }, 50);
                });
            }
            $(document).on('keypress', '#chat-input', function(e) {
                if (e.which === 13) {
                    $('#btn-send-chat-p').click();
                }
            });
            $(document).on('click', '#btn-close-chat', function() {
                $('#chat-panel').hide();
            });
            $(document).on('click', '#btn-send-chat-p', function() {

                let text = $('#chat-input').val().trim();

                if (!text) return;

                if (!currentPengajuanId) {
                    alert('Pengajuan belum dipilih');
                    return;
                }
                console.log('SEND MESSAGE', {
                    pengajuan_id: currentPengajuanId,
                    message: text
                });
                $.post('/pengajuan/send-message', {
                    pengajuan_id: currentPengajuanId,
                    message: text,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function() {

                    $('#chat-input').val('');
                    loadMessages();

                }).fail(function(xhr) {
                    console.log(xhr.responseText);
                    alert('Gagal kirim pesan');
                });

            });
            $(document).on('click', '.btn-export-excel', function() {

                let id = $(this).data('id');

                window.open('/pengajuan/export/' + id, '_blank');
            });
        </script>
        <script>
            window.authUserIdd = "{{ auth()->id() }}";

            window.authUserName = "{{ auth()->user()->name ?? '' }}";
            window.authUserEmail = "{{ auth()->user()->email ?? '' }}";

            // 🔥 mapping khusus
            if (window.authUserEmail === 'factory@newwicker.com') {
                window.authUserName = 'Mr Stanley';
            }
            if (window.authUserEmail === 'office@newwicker.com') {
                window.authUserAlias = 'Eka Wahyuning Lestari';
            } else {
                window.authUserAlias = (window.authUserName || '').toLowerCase().trim();
            }

            function showNotAllowed(message) {
                $('#not-allowed-msg')
                    .text(message)
                    .show();
            }
        </script>
        <script>
            $('#filter-type').change(function() {

                if ($(this).val() == 'Spk') {
                    window.location.href = '/spk/request-r';
                }

            });
            // =====================================================
            // CLOSE MODAL DETAIL PENGAJUAN
            // =====================================================
            $(document).on('click', '#btn-close-detail', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $('#modal-view').removeClass('active');
            });
        </script>
    @endsection
