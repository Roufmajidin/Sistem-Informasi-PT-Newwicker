@extends('master.master')
@section('title', "Pengajuan")

@section('content')
@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
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
                    <option value="All Divisi">All Divisi</option>
                    <option value="Finance">Finance</option>
                </select>

                <!-- QR BUTTON -->
                <div id="btn-qr" style="
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

                <!-- SEARCH -->
                <input type="text"
                    id="search-pengajuan"
                    class="form-control"
                    placeholder="Cari pengajuan..."
                    style="width:250px;">

            </div>
        </div>

        <button class="btn btn-primary btn-sm" id="btn-add">
            + Tambah Pengajuan
        </button>
    </div>
    <div id="modal-qr" class="modal-qr">

        <div style="background:#111; padding:20px; border-radius:10px; text-align:center;opacity:0.95;">

            <h4 style="color:#fff;">Scan QR Pengajuan</h4>

            <!-- KOTAK SCANNER -->
            <div id="qr-reader" style="
            width:300px;
            height:300px;
            margin:auto;
            border-radius:10px;
            overflow:hidden;
        "></div>

            <br>

            <button id="close-qr" class="btn btn-danger">Tutup</button>

        </div>

    </div>


</div>
{{-- ====== BODY (DUMMY TABLE) ====== --}}
<div class="box-body">
    @php
    $dummy = [
    ['no'=>'A-001','user'=>'Admin','status'=>'approved','date'=>now()],
    ['no'=>'A-002','user'=>'RND','status'=>'pending','date'=>now()],
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
                    <th>Created At</th>
                    <th>Qr</th>
                    <th>#</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <div id="not-allowed-msg" style="
    display:none;
    margin-bottom:10px;
    padding:10px;
    background:#fff1f0;
    color:#cf1322;
    border:1px solid #ffa39e;
    border-radius:6px;
"></div>
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
            <button type="button" class="btn-close close-modal" id="btn-close">✕</button>
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
    <div class="modal-full-content">

        {{-- HEADER --}}
        <div class="modal-header">
            <h4>Tambah Pengajuan</h4>
            <button type="button" class="btn-close close-modal" id="btn-close">✕</button>
        </div>

        {{-- BODY --}}
        <div class="modal-body">

            <form id="form-pengajuan" action="/pengajuan/store" method="POST">
                @csrf
                <input type="hidden" name="meta_json" id="meta_json">
                <input type="hidden" name="details_json" id="details_json">
                <input type="hidden" name="approval_json" id="approval_json">
                {{-- TYPE --}}
                <input type="file"
                    id="cameraUpload"
                    accept="image/*"
                    capture="environment"
                    multiple
                    style="display:none;">
                <div class="form-group mb-3">
                    <label>Type Pengajuan</label>
                    <select name="type_pengajuan" class="form-control" required>
                        <option value="">-- pilih --</option>
                        <option value="All Divisi">All Divisi</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>
                <!-- utk finance -->
                <div id="finance-section" style="display:none;">

                    <div class="form-group mb-3">
                        <label>Upload Excel</label>
                        <input type="file" id="excelInput" accept=".xlsx,.xls" class="form-control">
                    </div>
                    <div id="excel-meta" style="margin-bottom:15px; display:none;">
                        <div style="display:flex; gap:40px; flex-wrap:wrap;">

                            <div>
                                <b>tanggal</b> : <span id="meta-tanggal">-</span>
                            </div>

                            <div>
                                <b>nomor</b> : <span id="meta-nomor">-</span>
                            </div>

                            <div>
                                <b>type pembayaran</b> : <span id="meta-type">-</span>
                            </div>

                        </div>

                    </div>

                    <div id="excel-preview" class="excel-wrapper" style="overflow:auto; max-height:300px; border:1px solid #ddd;">
                        <table id="excel-table" class="table table-bordered">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
                <div class="form-group mb-3" id="keterangan-section">
                    <label>No. SPK</label>
                    <textarea name="no_spk" class="form-control"></textarea>
                </div>
                {{-- DIVISI --}}
                <div class="form-group mb-3" id="divisi-section">
                    <label>Divisi</label>
                    <select name="divisi_id" class="form-control">
                        <option value="">-- pilih divisi --</option>
                        @foreach($divisis as $divisi)
                        <option value="{{ $divisi->id }}">{{ $divisi->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- CAMERA (NATIVE HP) --}}
                <div class="form-group mb-3" id="camera-section">
                    <label>Ambil Foto</label>

                    <input type="file"
                        id="cameraInput"
                        name="images[]"
                        accept="image/*"
                        capture="environment"
                        multiple
                        class="form-control">
                </div>

                {{-- PREVIEW --}}
                <div id="preview-container"></div>

                {{-- KETERANGAN --}}
                <div class="form-group mb-3" id="keterangan-section">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
                </div>

                {{-- URGENT --}}
                <div class="form-group mb-3" id="urgent-section">
                    <label>Urgent</label><br>
                    <input type="radio" name="urgent" value="1"> Urgent
                    <input type="radio" name="urgent" value="0" checked> Normal
                </div>

                <button class="btn btn-success w-100">Simpan</button>
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

<div id="chat-panel" style="
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
    <div style="
    padding:10px;
    border-bottom:1px solid #eee;
    display:flex;
    justify-content:space-between;
    align-items:center;
">
        <b>Discussion</b>

        <span id="btn-close-chat" style="
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

        <input id="chat-input"
            placeholder="Tulis pesan..."
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
    <style>
        /* =========================
   MODAL FULL
========================= */
        /* =========================
   UPLOAD PREVIEW (KECIL)
========================= */
        .chat-row {
            margin-bottom: 10px;
            display: flex;
        }

        .chat-me {
            justify-content: flex-end;
        }

        .chat-other {
            justify-content: flex-start;
        }

        .chat-bubble {
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 13px;
        }

        .chat-me .chat-bubble {
            background: #DCF8C6;
        }

        .chat-other .chat-bubble {
            background: #fff;
            border: 1px solid #eee;
        }

        .chat-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .chat-time {
            font-size: 10px;
            color: #888;
            margin-top: 3px;
            text-align: right;
        }

        .table-footer-main {
            background: #111;
            color: #fff;
            font-weight: bold;
        }

        .table-footer-sub {
            background: #222;
            color: #aaa;
            font-size: 12px;
        }

        .swal-top {
            z-index: 99999 !important;
        }

        .swal2-container {
            z-index: 99999 !important;
        }

        #preview-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .preview-item {
            flex: 0 0 auto;
        }

        .img-upload-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: zoom-in;
            border: 1px solid #ddd;
            transition: 0.2s;
        }

        .img-upload-preview:hover {
            transform: scale(1.05);
        }

        /* =========================
   ZOOM MODAL
========================= */
        .image-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 99999;

            justify-content: center;
            align-items: center;
        }

        #imageWrapper {
            max-width: 95vw;
            max-height: 95vh;
            overflow: auto;
        }

        /* 🔥 ZOOM IMAGE */
        .img-zoom {
            max-width: 90vw;
            max-height: 90vh;
            object-fit: contain;
            /* 🔥 supaya tidak kepotong */
        }

        /* CLOSE */
        .close-modal {
            position: absolute;
            top: 20px;
            right: 25px;
            font-size: 28px;
            color: #fff;
            cursor: pointer;
        }

        .modal-full {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: #fff;
            height: 100vh;
        }

        .modal-full.active {
            display: flex;
            flex-direction: column;
        }

        .modal-full-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* =========================
   HEADER
========================= */
        .modal-header {
            flex: 0 0 auto;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            background: #fff;
            z-index: 10;
        }

        /* =========================
   BODY (SCROLL)
========================= */
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            height: calc(100vh - 70px);
        }

        /* =========================
   GRID IMAGE (DETAIL)
========================= */
        .img-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .img-item {
            /* width: 150px;
    height: 150px; */
            overflow: hidden;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
        }

        /* 🔥 IMAGE DETAIL */
        .img-detail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.3s;
            cursor: zoom-in;
        }

        .img-item:hover .img-detail {
            transform: scale(1.1);
        }

        /* =========================
   UPLOAD PREVIEW
========================= */
        #preview-container {
            display: flex;
            gap: 8px;
            overflow-x: auto;
        }

        .preview-item {
            position: relative;
            flex: 0 0 auto;
        }

        .img-upload-preview {
            width: 90px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        /* remove button */
        .btn-remove {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: #fff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            border: none;
            cursor: pointer;
        }

        /* =========================
   IMAGE MODAL (ZOOM)
========================= */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 99999;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);

            justify-content: center;
            align-items: center;
        }

        /* wrapper biar bisa scroll kalau besar */
        #imageWrapper {
            /* max-width: 95vw; */
            /* max-height: 95vh; */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* 🔥 IMAGE ZOOM */
        .img-zoom {
            /* max-width: vw; */
            /* max-height: 100vh; */
            /* object-fit: contain; */
            max-width: none;
            max-height: none;

            cursor: grab;
            transition: transform 0.2s ease;
            object-fit: contain;
            /* 🔥 ini kuncinya */

        }

        /* =========================
   CLOSE BUTTON
========================= */
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            color: #fff;
            font-size: 25px;
            cursor: pointer;
        }

        /* =========================
   EXCEL TABLE
========================= */
        .excel-wrapper {
            max-height: 300px;
            overflow: auto;
            border: 1px solid #ddd;
        }

        #excel-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #007bff;
            color: #fff;
            border: 1px solid #ddd;
        }

        #excel-table th,
        #excel-table td {
            white-space: nowrap;
        }

        /* =========================
   META BOX
========================= */
        #excel-meta {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
        }

        /* =========================
   QR MODAL
========================= */
        .modal-qr {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);

            justify-content: center;
            align-items: center;
        }

        .modal-qr.active {
            display: flex;
        }

        .modal-qr-box {
            background: #111;
            padding: 20px;
            border-radius: 12px;
            width: 340px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* =========================
   QR BUTTON
========================= */
        #btn-qr {
            transition: 0.2s;
        }

        #btn-qr:hover {
            background: #0056b3;
            transform: scale(1.05);
        }
    </style>

    {{-- ================= SCRIPT ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.js"></script>
    <script>
        let lastData = [];
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

                // background putih
                ctx.fillStyle = "#fff";
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // border
                ctx.strokeStyle = "#000";
                ctx.strokeRect(10, 10, 400, 530);

                // QR
                ctx.drawImage(img, 110, 40, 200, 200);

                // text
                ctx.fillStyle = "#000";
                ctx.font = "16px Arial";

                ctx.fillText("Type Pengajuan : " + type, 40, 300);
                ctx.fillText("Created by     : " + user, 40, 330);
                ctx.fillText("Created at     : " + formatDate(date), 40, 360);

                // download
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
                            allApproved = item.approval_steps.every(s => s.status === 'approved');
                        }

                        // =========================
                        // 🔥 BUTTON EXPORT (CONDITIONAL)
                        // =========================
                        let btnExport = '';

                        if (allApproved) {
                            btnExport = `
        <button class="btn btn-sm btn-primary btn-export-excel" data-id="${item.id}">
            Export
        </button>
    `;
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
                                    formatDateOnly(item.approved_date) :
                                    '-';

                                statusHtml = `
            <span class="badge badge-success">
                Approved<br>
                <small>on ${tanggal}</small>
            </span>
        `;
                            } else {
                                statusHtml = '<span class="badge badge-warning">Pending</span>';
                            }



                        } else if (item.type_pengajuan === 'Finance') {

                            if (item.status === 'approved') {
                                statusHtml = '<span class="badge badge-success">Approved</span>';
                            } else {

                                // 🔥 cari step yang masih pending
                                let pendingStep = item.approval_steps.find(s => s.status === 'pending');

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
>
    QR
</button>
    ${btnExport}


                `;

                        html += `
                    <tr>
                        <td>${i+1}</td>
                        <td><b>A-${item.id}</b></td>
                        <td>${item.user ? item.user.name : '-'}</td>
                        <td>${statusHtml}</td>
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

                        let total = 0;

                        res.details.forEach(d => {

                            total += Number(d.total_harga || 0);

                            contentHtml += `
                        <tr>
                            <td>${d.no}</td>
                            <td>${d.date}</td>
                            <td>${d.no_po}</td>
                     <td
    onclick="handleClick(${d.id})"
    ondblclick="handleDoubleClick(${d.id})"
    style="cursor:pointer; color:#007bff;"
>
    ${d.no_inv}
</td>
                            <td>${d.type_biaya}</td>
                            <td>${d.nama_barang}</td>
                            <td>${d.qty}</td>
                            <td>${formatRupiah(d.harga_satuan)}</td>
                            <td>${formatRupiah(d.total_harga)}</td>
                        </tr>
                    `;
                        });

                        // 🔥 FOOTER
                        let grandTotal = res.meta?.grand_total ?? total;
                        let onHold = Number(res.meta?.on_hold ?? 0);

                        contentHtml += `
<tr style="background:#111; color:#fff;">
    <td colspan="9" style="text-align:right; padding:15px 20px;">

        <div style="font-size:20px; font-weight:bold;">
            ${formatRupiah(grandTotal)}
        </div>

        <div style="font-size:12px; color:#aaa; margin-top:5px;">
            yang dihold
        </div>

        <div style="font-size:13px; color:#ccc;">
            ${formatRupiah(onHold)}
        </div>

    </td>
</tr>
`;

                        contentHtml += `</tbody></table></div>`;
                    }

                    // =========================
                    // 🔥 ALL DIVISI → IMAGE
                    // =========================
                    else {

                        contentHtml += `<div style="display:flex; flex-wrap:wrap; gap:10px;">`;

                        if (res.type_pengajuan === 'All Divisi') {

                            contentHtml += `
        <div style="margin-bottom:15px;">
            <button class="btn btn-success btn-approve-all"
                data-id="${res.id}">
                ✅ Approve This Pengajuan
            </button>
        </div>
    `;

                            contentHtml += `<div class="img-grid">`;

                            res.files?.forEach(f => {
                                contentHtml += `
            <div class="img-item">
                <img src="/storage/${f.file_path}"
                     class="img-detail"
                     data-src="/storage/${f.file_path}">
            </div>
        `;
                            });

                            contentHtml += `</div>`;
                        }

                        contentHtml += `</div>`;
                    }
                    // =========================
                    // 🔥 APPROVAL SECTION
                    // =========================

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
                }
            });

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
            @if(session('error') || session('success'))
            $('#modal-pengajuan').addClass('active');
            $('.modal-body').scrollTop(0);
            @endif
        });
        const input = document.querySelector('input[type="file"]');
        const container = document.getElementById('preview-container');

        input.addEventListener('change', function() {

            container.innerHTML = '';

            Array.from(this.files).forEach(file => {

                let reader = new FileReader();

                reader.onload = function(e) {

                    let wrapper = document.createElement('div');
                    wrapper.classList.add('preview-item');

                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-upload-preview');

                    // 🔥 CLICK → ZOOM
                    img.onclick = function() {
                        openZoom(this.src);
                    };

                    wrapper.appendChild(img);
                    container.appendChild(wrapper);
                };

                reader.readAsDataURL(file);
            });

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
        $(document).ready(function() {

            $('#form-pengajuan').on('submit', function(e) {

                e.preventDefault(); // 🔥 WAJIB

                let type = $('[name="type_pengajuan"]').val();

                let formData = new FormData(this);

                if (type === 'All Divisi') {

                    // selectedFiles.forEach((file) => {
                    //     formData.append('images[]', file);
                    // });

                    sendAjax('/pengajuan/store-all-divisi', formData);
                    return;
                }

                // FINANCE
                formData.set('meta_json', JSON.stringify(excelMeta));
                formData.set('details_json', JSON.stringify(excelDetails));
                formData.set('approval_json', JSON.stringify(excelApproval));

                sendAjax('/pengajuan/store', formData);

            });

        });
        let currentDetailId = null;

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
    @endsection
