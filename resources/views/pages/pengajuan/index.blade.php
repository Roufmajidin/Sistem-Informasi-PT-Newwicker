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
                    <th>peng. v</th>
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
            <button type="button" style="color: red;" class="btn-close close-modal" id="btn-close">✕</button>
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

           <form id="form-pengajuan" onsubmit="return false;">
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

               <button type="button" id="btn-submit" class="btn btn-success w-100">
    Simpan
</button>
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


    {{-- ================= SCRIPT ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/zoomist@1/dist/zoomist.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exif-js"></script>
    <script>
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

                              let tanggal = item.approved_date
    ? formatDateTime(item.approved_date)
    : '-';
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
        // submit pengajuan
      $(document).ready(function(){

  $(document).ready(function () {

    $('#btn-submit').off('click').on('click', function (e) {

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
async function sendChunkAjax(url, formData) {

    try {

        let files = $('input[name="images[]"]')[0].files;

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

        beforeSend: function () {

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

        success: function (res) {

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

        error: function (xhr) {

            console.log(xhr);

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: xhr.responseJSON?.message || 'Server error'
            });
        },

        complete: function () {
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

        function handleTap(detailId,rowEl) {
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
    @endsection
