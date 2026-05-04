console.log('hallo')

$(document).on('click', '.btn-chat', function () {

    let itemId = $(this).data('id');

    $('#chatModal').modal('show');

    // ambil / create room
    $.get('/chatroom/get-room/' + itemId, function (res) {

        $('#chat-room-id').val(res.room_id);

        loadChat(res.room_id);
    });

});
$(document).on('click', '.btn-chat', function () {

    let itemId = $(this).data('id');

    $('#chatModal').modal('show');

    // ambil / create room
    $.get('/chatroom/get-room/' + itemId, function (res) {

        $('#chat-room-id').val(res.room_id);

        loadChat(res.room_id);
    });

});

function loadChat(roomId) {

    $.get('/chatroom/messages/' + roomId, function (res) {

        let html = '';
        let lastDate = '';

        res.forEach(msg => {

            let isMe = msg.user_id == CURRENT_USER_ID;
            let position = isMe ? 'right' : 'left';

            let dateLabel = formatDateLabel(msg.created_at);

            // ===== DATE SEPARATOR =====
            if (dateLabel !== lastDate) {
                html += `
                    <div class="date-separator">
                        <span>${dateLabel}</span>
                    </div>
                `;
                lastDate = dateLabel;
            }

            html += `
                <div class="msg ${position}">
                    <div class="bubble">
                        ${!isMe ? `<div class="name">${msg.user.name}</div>` : ''}
                        <div>${msg.message}</div>
                        <div class="time">${formatTime(msg.created_at)}</div>
                    </div>
                </div>
            `;
        });

        $('#chat-box').html(html);
        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
    });
}
$('#btn-send-chat').click(function () {

    let message = $('#chat-input').val();
    let roomId = $('#chat-room-id').val();

    if (!message) return;

    $.post('/chatroom/send', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        room_id: roomId,
        message: message
    }, function () {

        $('#chat-input').val('');
        loadChat(roomId);
    });

});


function formatTime(dateStr) {
    let d = new Date(dateStr);
    return d.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatDateLabel(dateStr) {
    let d = new Date(dateStr);
    let today = new Date();
    let yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    let dStr = d.toDateString();

    if (dStr === today.toDateString()) return 'Hari ini';
    if (dStr === yesterday.toDateString()) return 'Kemarin';

    return d.toLocaleDateString('id-ID');
}


// detail produksi
// $(document).on('click', '.btn-view', function () {

//     let items = $(this).data('items');
//     let timelines = $(this).data('timeline') || [];

//     let html = '';

//     items.forEach(d => {
// console.log('TIMELINE:', timelines);
// console.log('ITEM ID:', d.id);
//         let detail = d.detail;
//         let name = detail.description;
//         let photo = detail.photo;
//         let qty = parseInt(detail.qty);

//         let rangka = 0;
//         let anyam = 0;
//         let unfinish = 0;
//         let final = 0;
// timelines.forEach(row => {

//     if (parseInt(row.detail_po_id) !== parseInt(d.id)) return;

//     let q = parseInt(row.qty) || 0;

//     let type = (row.type || '').toLowerCase().trim();
//     let process = (row.process || '').toLowerCase().trim();
//  let next = (row.next_process || '').toLowerCase().trim();

//     console.log(process, type, q); // 🔥 DEBUG

//     if (type === 'masuk') {
//         if (process === 'rangka') rangka += q;
//         if (process === 'anyam') anyam += q;
//         if (process === 'unfinish') unfinish += q;
//         if (process === 'final') final += q;
//     }
//   if (type === 'keluar' && next === 'unfinish') {

//     // 🔥 TAMBAH KE TUJUAN (next_process)
//     if (next === 'rangka') rangka += q;
//     if (next === 'anyam') anyam += q;
//     if (next === 'unfinish') unfinish += q;
//     if (next === 'final') final += q;
// }
//     if (type === 'service') {



//         if (process === 'rangka') rangka -= q;
//         if (process === 'anyam') anyam -= q;
//         if (process === 'unfinish') unfinish -= q;
//         if (process === 'final') final -= q;
//     }

// });
//         html += renderRow(name, photo, qty, d, rangka, anyam, unfinish, final);
//     });

//     $('#detail-area').html(html);
// });
// $(document).on('click', '.btn-view', function () {

//     let items = $(this).data('items');
//     let po_id = $(this).data('id');
//     globalPoId = po_id;

//     // loading dulu
//     $('#detail-area').html('<tr><td colspan="6">Loading...</td></tr>');

//     $.ajax({
//         url: '/get-timeline',
//         type: 'GET',
//         data: {
//             po_id: po_id
//         },
//         success: function (timelines) {

//             let html = '';

//             items.forEach(d => {

//                 let detail = d.detail;
//                 let name = detail.description;
//                 let photo = detail.photo;
//                 let qty = parseInt(detail.qty);

//                 let rangka = 0;
//                 let anyam = 0;
//                 let unfinish = 0;
//                 let final = 0;

//                 timelines.forEach(row => {

//                     if (parseInt(row.detail_po_id) !== parseInt(d.id)) return;

//                     let q = parseInt(row.qty) || 0;

//                     let type = (row.type || '').toLowerCase().trim();
//                     let process = (row.process || '').toLowerCase().trim();
//                     let next = (row.next_process || '').toLowerCase().trim();

//                     // =====================
//                     // PRODUKSI (MASUK)
//                     // =====================
//                     if (type === 'masuk') {
//                         if (process === 'rangka') rangka += q;
//                         if (process === 'anyam') anyam += q;
//                         if (process === 'unfinish') unfinish += q;
//                         if (process === 'final') final += q;
//                     }
//                     if (type === 'keluar' && next === 'unfinish') {

//                         // 🔥 TAMBAH KE TUJUAN (next_process)
//                         if (next === 'rangka') rangka += q;
//                         if (next === 'anyam') anyam += q;
//                         if (next === 'unfinish') unfinish += q;
//                         if (next === 'final') final += q;
//                     }
//                     if (type === 'service') {



//                         if (process === 'rangka') rangka -= q;
//                         if (process === 'anyam') anyam -= q;
//                         if (process === 'unfinish') unfinish -= q;
//                         if (process === 'final') final -= q;
//                     }

//                 });

//                 html += renderRow(name, photo, qty, d, rangka, anyam, unfinish, final);

//             });

//             $('#detail-area').html(html);
//         }
//     });

// });
$(document).on('click', '.btn-view', function () {

    let items = $(this).data('items');
    let po_id = $(this).data('id');

    // ✅ SIMPAN GLOBAL
    globalItems = items;
    globalPoId = po_id;
    setHeader('produksi');
    // default load produksi
    loadProduksi();
});
// ================= GLOBAL =================
let globalItems = [];
let globalPoId = null;
let globalTimeline = [];
let globalQc = [];
let supplierKategoriMap = {};
let kategoriSupplierMap = {};
let allSuppliers = [];
let allKategori = [];

let originalSuppliers = [];
let originalKategori = [];

let isAutoChange = false;

// pengajuan global var
let excelMeta = {};
let excelDetails = [];
let excelApproval = {};
let transferValue = 0;
let grandTotalValue = 0;
// ================= RESET =================
function resetFilter() {

    let supHtml = '<option value="">-- pilih supplier --</option>';

    originalSuppliers.forEach(s => {
        supHtml += `<option
            value="${s.id}"
            data-qty="${s.qty}"
            data-spk="${s.spk_id}"
        >
            ${s.name} | ${s.qty} ${s.satuan}
        </option>`;
    });

    $('#supplier').html(supHtml);

    let subHtml = '<option value="">-- pilih jenis barang --</option>';

    originalKategori.forEach(k => {
        subHtml += `<option value="${k}">${k.toUpperCase()}</option>`;
    });

    $('#sub_barang').html(subHtml);

    $('#qty').val('');
}


// ================= OPEN MODAL =================
$(document).on('click', '.btn-detail-name', function () {

    let name = $(this).data('name');
    let po_id = $(this).data('id');
    let detail_po_id = $(this).data('detail_po');

    $('#modal-title').text('Input Barang - ' + name);

    let now = new Date();
    $('#date').val(now.toISOString().split('T')[0]);
    $('#time').val(now.toTimeString().slice(0, 5));

    $('#supplier').html('<option>Loading...</option>');
    $('#sub_barang').html('<option>Loading...</option>');
    $('#table-out').html('<tr><td colspan="4">Loading...</td></tr>');
    $('#modalDetail').data('detail_po_id', detail_po_id);
    $('#modalDetail').data('po_id', po_id);
    $.ajax({
        url: '/get-detail-barang',
        type: 'GET',
        data: {
            po_id,
            detail_po_id
        },
        success: function (res) {

            supplierKategoriMap = {};
            kategoriSupplierMap = {};
            allSuppliers = [];
            globalItems = res.items;

            allKategori = [];
            window.productionLogs = res.logs;
            res.data.forEach(r => {

                let supplierId = r.supplier.id;
                let supplierName = r.supplier.name;
                let qty = r.item.qty;
                let satuan = r.item.satuan || 'pcs';
                let kategori = r.kategori;


                if (!allSuppliers.find(s => s.id == supplierId)) {
                    allSuppliers.push({
                        id: supplierId,
                        name: supplierName,
                        qty: qty,
                        satuan: satuan,
                        spk_id: r.spk_id,
                        po_id: r.po_id
                    });
                }


                if (!allKategori.includes(kategori)) {
                    allKategori.push(kategori);
                }

                // mapping supplier → kategori
                if (!supplierKategoriMap[supplierId]) {
                    supplierKategoriMap[supplierId] = [];
                }
                if (!supplierKategoriMap[supplierId].includes(kategori)) {
                    supplierKategoriMap[supplierId].push(kategori);
                }

                // mapping kategori → supplier
                if (!kategoriSupplierMap[kategori]) {
                    kategoriSupplierMap[kategori] = [];
                }
                if (!kategoriSupplierMap[kategori].includes(supplierId)) {
                    kategoriSupplierMap[kategori].push(supplierId);
                }

            });

            // simpan original
            originalSuppliers = [...allSuppliers];
            originalKategori = [...allKategori];

            resetFilter();

            // table kanan
            let table = '';

            if (res.logs && res.logs.length > 0) {

                res.logs.forEach(r => {

                    let typeColor = {
                        masuk: 'success',
                        keluar: 'danger',
                        service: 'warning'
                    } [r.type] || 'secondary';

                    table += `
            <tr>
                <td>${r.date}</td>
                <td>${r.time ?? '-'}</td>
                <td><span class="badge bg-${typeColor}">${r.type}</span></td>
                <td>${r.process}</td>
                <td>${r.next_process ?? '-'}</td>
                <td>${r.qty}</td>
                <td>${r.supplier ?? '-'}</td>
                <td>${r.remark ?? '-'}</td>
            </tr>
        `;
                });

            } else {
                table = '<tr><td colspan="8">No data</td></tr>';
            }

            $('#table-out').html(table);
        }
    });

    $('#modalDetail').modal('show');
});


// ================= SUPPLIER → KATEGORI =================
$(document).on('change', '#supplier', function () {

    if (isAutoChange) return;

    let selected = $(this).find(':selected');
    let supplierId = $(this).val();

    if (!supplierId) {
        resetFilter();
        return;
    }

    let qty = selected.data('qty') || 0;
    $('#qty').val(qty);

    let kategoriList = supplierKategoriMap[supplierId] || [];

    let subHtml = '<option value="">-- pilih jenis barang --</option>';

    kategoriList.forEach(k => {
        subHtml += `<option value="${k}">${k.toUpperCase()}</option>`;
    });

    $('#sub_barang').html(subHtml);

    // auto select kategori
    if (kategoriList.length > 0) {
        isAutoChange = true;
        $('#sub_barang').val(kategoriList[0]);
        isAutoChange = false;
    }

});


// ================= KATEGORI → SUPPLIER =================
$(document).on('change', '#sub_barang', function () {

    if (isAutoChange) return;

    let kategori = $(this).val();

    if (!kategori) {
        resetFilter();
        return;
    }

    let supplierIds = kategoriSupplierMap[kategori] || [];

    let supHtml = '<option value="">-- pilih supplier --</option>';

    allSuppliers.forEach(s => {
        if (supplierIds.includes(s.id)) {
            supHtml += `<option
                value="${s.id}"
                data-qty="${s.qty}"
                data-spk="${s.spk_id}"
            >
                ${s.name} | ${s.qty} ${s.satuan}
            </option>`;
        }
    });

    $('#supplier').html(supHtml);

    // auto select supplier
    if (supplierIds.length > 0) {
        isAutoChange = true;
        $('#supplier').val(supplierIds[0]).trigger('change');
        isAutoChange = false;
    }

});


// ================= TYPE =================
$(document).on('change', '#type', function () {

    let type = $(this).val();

    if (type === 'keluar') {
        $('#process-wrapper').removeClass('d-none');
    } else {
        $('#process-wrapper').addClass('d-none');
        $('#process').val('');
    }

});


// ================= REMARK =================
$(document).on('change', '#type, #supplier, #process, #sub_barang', function () {

    let type = $('#type').val();
    let kategori = $('#sub_barang').val();
    let nextProcess = $('#process').val();
    let supplier = $('#supplier option:selected').text();

    let remark = '';

    // =========================
    // MASUK
    // =========================
    if (type === 'masuk') {
        remark = `${capitalize(kategori)} masuk dari ${supplier}`;
    }

    // =========================
    // KELUAR (FLOW PRODUKSI)
    // =========================
    if (type === 'keluar') {

        if (nextProcess) {

            // 🔥 kalau ke supplier (anyam dll)
            if (!['unfinish', 'final'].includes(nextProcess)) {
                remark = `${capitalize(kategori)} menuju ${capitalize(nextProcess)} (${supplier})`;
            }

            // 🔥 kalau ke factory
            else {
                remark = `${capitalize(kategori)} menuju ${capitalize(nextProcess)}`;
            }

        } else {
            remark = `${capitalize(kategori)} keluar`;
        }
    }

    // =========================
    // SERVICE
    // =========================
    if (type === 'service') {
        remark = `Service ${capitalize(kategori)}`;
    }

    $('#remark').val(remark);
});

// ================= SAVE =================
$('#save-process').click(function () {

    let kategori = $('#sub_barang').val();
    let type = $('#type').val();
    let nextProcess = $('#process').val();
    let detail_po_id = $('#modalDetail').data('detail_po_id');
    let process = null;
    let next_process = null;

    let po_id = $('#modalDetail').data('po_id');
    if (type === 'service') {
        process = kategori;
    }
    if (type === 'masuk') {
        process = kategori;
    }

    if (type === 'keluar') {
        process = kategori;
        next_process = nextProcess;
    }

    let source_type = ['unfinish', 'final'].includes(kategori) ?
        'factory' :
        'supplier';


    let selected = $('#supplier option:selected');
    let spk_id = selected.data('spk');

    let data = {
        spk_id: source_type === 'supplier' ? spk_id : null,
        po_id: po_id, // 🔥 INI

        supplier_id: source_type === 'supplier' ? $('#supplier').val() : null,
        kategori: kategori,
        qty: $('#qty').val(),
        date: $('#date').val(),
        time: $('#time').val(),
        type: type,
        process: process,
        next_process: next_process,
        source_type: source_type,
        remark: $('#remark').val(),
        detail_po_id: detail_po_id, // 🔥 INI
    };

    console.log(data);

    $.post('/save-process', data, function () {
        alert('Berhasil disimpan');
    });

});

let isGraph = false;

$('#toggle-graph').click(function () {

    isGraph = !isGraph;

    if (isGraph) {
        $('#table-out').closest('table').hide();
        $('#graph-view').removeClass('d-none');
        $(this).text('Tampilkan Table');
        renderGraph(window.productionLogs);
    } else {
        $('#table-out').closest('table').show();
        $('#graph-view').addClass('d-none');
        $(this).text('Tampilkan Graph');
    }

});

function capitalize(text) {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
}

function renderGraph(logs) {

    let html = '';

    logs.reverse().forEach((r, i) => {

        let icon = {
            masuk: '⬇️',
            keluar: '⬆️',
            service: '🛠️'
        } [r.type] || '•';

        let color = {
            masuk: '#4CAF50',
            keluar: '#F44336',
            service: '#FFC107'
        } [r.type] || '#999';

        html += `
            <div style="display:flex; align-items:center; margin-bottom:15px;">

                <!-- garis -->
                <div style="width:30px; text-align:center;">
                    <div style="width:2px; height:20px; background:#555; margin:auto;"></div>
                    <div style="
                        width:12px;
                        height:12px;
                        background:${color};
                        border-radius:50%;
                        margin:auto;
                    "></div>
                </div>

                <!-- content -->
                <div style="margin-left:10px;">
                    <div style="font-weight:bold;">
                        ${icon} ${r.process.toUpperCase()}
                        ${r.next_process ? '→ ' + r.next_process.toUpperCase() : ''}
                    </div>

                    <div style="font-size:12px; color:#ccc;">
                        ${r.date} ${r.time ?? ''} |
                        Qty: ${r.qty} |
                        ${r.supplier ?? '-'}
                    </div>

                    <div style="font-size:12px; color:#aaa;">
                        ${r.remark ?? ''}
                    </div>
                </div>

            </div>
        `;
    });

    $('#graph-view').html(html);
}

function renderRow(name, photo, qty, d, rangka, anyam, unfinish, final) {

    return `
        <tr>
       <td>
        <span class="btn-detail-name"
            data-name="${name}"
            data-photo="${photo}"
            data-qty="${qty}"
            data-id="${d.po_id}"
            data-detail_po="${d.id}"
            style="cursor:pointer"
        >
            <img src="${photo}"
                 style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
        </span>
    </td>
            <td>
                <span class="btn-detail-name"
                    data-name="${name}"
                    data-qty="${qty}"
                    data-id="${d.po_id}"
                    data-detail_po="${d.id}">
                    ${name}
                </span>
            </td>

            <td>${qty}</td>

            <td>${rangka}</td>
            <td>${anyam}</td>
            <td>${unfinish}</td>
            <td>${final}</td>
        </tr>
    `;
}

function loadQc() {

    $('#detail-area').html('<tr><td colspan="7">Loading QC...</td></tr>');

    $.get('/get-qc', {
        po_id: globalPoId
    }, function (res) {

        globalQc = Array.isArray(res) ? res : [];


        renderQc();
    });
}

function renderQc() {

    let html = '';

    globalItems.forEach((d, i) => {

        let detail = d.detail;
        let name = detail.description;
        let qty = parseInt(detail.qty);

        let dataQc = {
            rangka: {
                inspect: 0,
                pass: 0,
                reject: 0
            },
            anyam: {
                inspect: 0,
                pass: 0,
                reject: 0
            },
            unfinish: {
                inspect: 0,
                pass: 0,
                reject: 0
            },
            final: {
                inspect: 0,
                pass: 0,
                reject: 0
            }
        };

        // 🔥 FILTER PER ITEM
        let rows = (globalQc || []).filter(q =>
            parseInt(q.detail_po_id) === parseInt(d.id)
        );

        // 🔥 AGREGASI SEMUA BATCH
        rows.forEach(r => {

            let kategori = (r.kategori || '').toLowerCase().trim();

            if (!dataQc[kategori]) {
                console.warn('Kategori tidak dikenal:', kategori);
                return;
            }

            dataQc[kategori].inspect += Number(r.jumlah_inspect || 0);
            dataQc[kategori].pass += Number(r.passed || 0);
            dataQc[kategori].reject += Number(r.rejected || 0);

        });

        function cell(d) {

            if (d.inspect === 0) {
                return `<span class="text-muted">-</span>`;
            }

            let percent = (d.pass / d.inspect * 100).toFixed(0);

            return `
                <div>
                    <small>
                        ${d.inspect} |
                        <span style="color:green">✔ ${d.pass}</span> |
                        <span style="color:red">✖ ${d.reject}</span>
                        (${percent}%)
                    </small>
                </div>
            `;
        }

        html += `
            <tr>
                <td>${i + 1}</td>
                <td>
                    <a href="/qc/${globalPoId}" style="color:#007bff;font-weight:500">
                        ${name}
                    </a>
                </td>
                <td>${qty}</td>

                <td>${cell(dataQc.rangka)}</td>
                <td>${cell(dataQc.anyam)}</td>
                <td>${cell(dataQc.unfinish)}</td>
                <td>${cell(dataQc.final)}</td>
            </tr>
        `;
    });

    $('#detail-area').html(html);
}


$('#filter-process').change(function () {

    let val = $(this).val();

    if (val === 'qc') {
        setHeader('qc');
        loadQc();
    } else {
        setHeader('produksi');
        loadProduksi();
    }

});

function loadProduksi() {

    $('#detail-area').html('<tr><td colspan="7">Loading...</td></tr>');

    $.get('/get-timeline', {
        po_id: globalPoId
    }, function (res) {

        globalTimeline = res;

        renderProduksi();
    });
}

function renderProduksi() {

    let html = '';

    globalItems.forEach(d => {

        let detail = d.detail;
        let name = detail.description;
        let photo = detail.photo;
        let qty = parseInt(detail.qty);

        let rangka = 0,
            anyam = 0,
            unfinish = 0,
            final = 0;

        globalTimeline.forEach(row => {

            if (parseInt(row.detail_po_id) !== parseInt(d.id)) return;

            let q = parseInt(row.qty) || 0;
            let type = (row.type || '').toLowerCase();
            let process = (row.process || '').toLowerCase();
            let next = (row.next_process || '').toLowerCase();

            if (type === 'masuk') {
                if (process === 'rangka') rangka += q;
                if (process === 'anyam') anyam += q;
                if (process === 'unfinish') unfinish += q;
                if (process === 'final') final += q;
            }

            if (type === 'keluar') {
                if (next === 'rangka') rangka += q;
                if (next === 'anyam') anyam += q;
                if (next === 'unfinish') unfinish += q;
                if (next === 'final') final += q;
            }

            if (type === 'service') {
                if (process === 'rangka') rangka -= q;
                if (process === 'anyam') anyam -= q;
                if (process === 'unfinish') unfinish -= q;
                if (process === 'final') final -= q;
            }

        });

        html += renderRow(name, photo, qty, d, rangka, anyam, unfinish, final);
    });

    $('#detail-area').html(html);
}

function setHeader(mode = 'produksi') {

    let html = '';

    if (mode === 'qc') {
        html = `
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Total PO</th>
                <th>QC Result</th>
            </tr>
        `;
    } else {
        html = `
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Total PO</th>
                <th>Rangka</th>
                <th>Anyam</th>
                <th>Unfinish</th>
                <th>Final</th>
            </tr>
        `;
    }

    $('#detail-header').html(html);
}

function cell(d) {
    let percent = d.inspect ? (d.pass / d.inspect * 100) : 0;

    let bg = percent >= 80 ? '#d4edda' : '#f8d7da';

    return `
        <div style="background:${bg};padding:4px;border-radius:4px">
            <small>
                I:${d.inspect} |
                ✔ ${d.pass} |
                ✖ ${d.reject}
            </small>
        </div>
    `;
}
// cari data
let searchTimer;

$('#search-qc').on('keyup', function () {

    let keyword = $(this).val();

    clearTimeout(searchTimer);

    searchTimer = setTimeout(function () {

        let url = new URL(window.location.href);

        if (keyword) {
            url.searchParams.set('search', keyword);
        } else {
            url.searchParams.delete('search');
        }

        window.location.href = url.toString();

    }, 500);

});



// pengajuan
let selectedFiles = [];
let currentIndex = 0;
let scale = 1;

let posX = 0;
let posY = 0;
let startX = 0;
let startY = 0;
let isDragging = false;
let isMouseDown = false;

let typeSelect = document.querySelector('[name="type_pengajuan"]');
let cameraSection = document.querySelector('#cameraInput').closest('.form-group');
let financeSection = document.getElementById('finance-section');

$(document).ready(function () {

    $('[name="type_pengajuan"]').on('change', function () {

        let val = $(this).val();

        if (val === 'Finance') {

            // 🔥 tampilkan excel
            $('#finance-section').show();

            // 🔥 hide lainnya
            $('#divisi-section').hide();
            $('#camera-section').hide();
            $('#keterangan-section').hide();
            $('#urgent-section').hide();

        } else {

            // 🔥 tampilkan normal
            $('#finance-section').hide();

            $('#divisi-section').show();
            $('#camera-section').show();
            $('#keterangan-section').show();
            $('#urgent-section').show();
        }

    });

});

function extractMeta(data) {

    let tanggal = '';
    let nomor = '';
    let type = '';

    function getNextValue(row, start) {
        for (let j = start + 1; j < row.length; j++) {
            let v = row[j];
            if (v && v !== ':' && v !== '') {
                return v;
            }
        }
        return '';
    }

    data.forEach(row => {

        row.forEach((cell, i) => {

            if (typeof cell === 'string') {

                let val = cell.toLowerCase().trim();

                if (val.includes('tanggal') && !tanggal) {
                    tanggal = getNextValue(row, i);
                }

                if (val.includes('nomor') && !nomor) {
                    nomor = getNextValue(row, i);
                }

                if (val.includes('type pembayaran') && !type) {
                    type = getNextValue(row, i);
                }
            }

        });

    });

    // 🔥 FIX DATE
    if (typeof tanggal === 'number') {
        tanggal = excelDateToJSDate(tanggal);
    }

    return {
        tanggal,
        nomor,
        type
    };
}
document.getElementById('excelInput').addEventListener('change', function (e) {

    let file = e.target.files[0];
    let reader = new FileReader();

    reader.onload = function (e) {

        let data = new Uint8Array(e.target.result);
        let workbook = XLSX.read(data, {
            type: 'array'
        });

        let sheet = workbook.Sheets[workbook.SheetNames[0]];
        let json = XLSX.utils.sheet_to_json(sheet, {
            header: 1
        });

        // 🔥 DI SINI SEMUA HARUS DIJALANKAN
        let meta = extractMeta(json);
        let totals = extractTotals(json);
        if (!meta.nomor || !meta.tanggal) {
            alert('⚠️ Excel tidak valid! Pastikan ada "Nomor" dan "Tanggal"');
            return;
        }
        document.getElementById('excel-meta').style.display = 'block';
        document.getElementById('meta-tanggal').innerText = meta.tanggal;
        document.getElementById('meta-nomor').innerText = meta.nomor;
        document.getElementById('meta-type').innerText = meta.type;
        console.log(meta.tanggal);
        console.log(meta.nomor);
        console.log(meta.type);
        // render
        let transferValue = totals.transfer;
        let grandTotalValue = totals.grand;
        excelMeta = {
            tanggal: meta.tanggal,
            nomor: meta.nomor,
            type_pembayaran: meta.type,
            transfer: transferValue, // dari excel
            grand_total: grandTotalValue // dari excel
        };
        // cari header dulu
        let headerIndex = findHeaderRow(json);

        // ambil data rows
        let dataRows = json.slice(headerIndex + 1);

        // 🔥 DETAIL
        excelDetails = [];

        for (let i = headerIndex + 1; i < json.length; i++) {

            let row = json[i];

            // ❌ skip kosong
            if (!row || row.length === 0) continue;

            // ❌ stop kalau ketemu TRANSFER
            if (typeof row[0] === 'string' && row[0].toLowerCase().includes('transfer')) {
                break;
            }

            // ❌ skip row hitam / kosong (row 9-10)
            if (!row[0] || row[0] === '') continue;

            // ❌ skip kalau bukan angka (biar ga ambil header bawah)
            if (isNaN(row[0])) continue;

            // ✅ ambil data valid
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

        document.getElementById('meta_json').value = JSON.stringify(excelMeta);
        document.getElementById('details_json').value = JSON.stringify(excelDetails);
        document.getElementById('approval_json').value = JSON.stringify(excelApproval);

        console.log('META FINAL:', excelMeta);
        renderExcel(json);
    };

    reader.readAsArrayBuffer(file);
});

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

function renderExcel(data) {
    let thead = document.querySelector('#excel-table thead');
    let tbody = document.querySelector('#excel-table tbody');

    thead.innerHTML = '';
    tbody.innerHTML = '';

    if (!data || data.length === 0) return;

    // 🔥 cari header
    let headerIndex = findHeaderRow(data);

    let headers = data[headerIndex];

    // ===== HEADER =====
    let trHead = document.createElement('tr');

    headers.forEach(col => {
        let th = document.createElement('th');
        th.innerText = col ?? '';
        trHead.appendChild(th);
    });

    thead.appendChild(trHead);

    // ===== BODY =====
    for (let i = headerIndex + 1; i < data.length; i++) {
        let row = data[i];

        // skip kosong
        if (!row || row.length === 0) continue;

        let tr = document.createElement('tr');

        row.forEach(cell => {

            // 🔥 FIX DATE
            if (typeof cell === 'number' && cell > 30000 && cell < 60000) {
                cell = excelDateToJSDate(cell);
            }

            let td = document.createElement('td');
            td.innerText = cell ?? '';
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
    }

}

function findHeaderRow(data) {
    for (let i = 0; i < data.length; i++) {
        let row = data[i].join(' ').toLowerCase();

        if (row.includes('no') && row.includes('date')) {
            return i;
        }
    }
    return 0;
}
// OPEN MODAL
document.getElementById('btn-add').onclick = () => {
    document.getElementById('modal-pengajuan').classList.add('active');
};

// CLOSE MODAL
document.getElementById('btn-close').onclick = () => {
    document.getElementById('modal-pengajuan').classList.remove('active');
};
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.onclick = function () {
        this.closest('.modal-full').classList.remove('active');
        $('#view-content').html('');
        $('#view-meta').html('');
    };
});
// ================= COMPRESS =================
async function compressImage(file) {
    return new Promise(resolve => {
        let img = new Image();
        let reader = new FileReader();

        reader.onload = e => img.src = e.target.result;

        img.onload = () => {
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');

            let maxWidth = 800;
            let scale = maxWidth / img.width;

            canvas.width = maxWidth;
            canvas.height = img.height * scale;

            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(blob => {
                resolve(new File([blob], file.name, {
                    type: 'image/jpeg'
                }));
            }, 'image/jpeg', 0.7);
        };

        reader.readAsDataURL(file);
    });
}

// INPUT CHANGE
document.getElementById('cameraInput').addEventListener('change', async function (e) {

    let files = Array.from(e.target.files);

    for (let file of files) {

        let fixed = await new Promise(resolve => {
            fixImageOrientation(file, function(blob) {

                let fixedFile = new File([blob], file.name, {
                    type: 'image/jpeg'
                });

                resolve(fixedFile);
            });
        });

        selectedFiles.push(fixed);
    }

    renderPreview();
});
function fixImageOrientation(file, callback) {

    let reader = new FileReader();

    reader.onload = function(e) {

        let img = new Image();

        img.onload = function() {

            EXIF.getData(file, function () {

                let orientation = EXIF.getTag(this, 'Orientation') || 1;

                let canvas = document.createElement('canvas');
                let ctx = canvas.getContext('2d');

                let width = img.width;
                let height = img.height;

                switch (orientation) {

                    case 6: // 🔥 kebanyakan Android
                        canvas.width = height;
                        canvas.height = width;
                        ctx.rotate(-Math.PI / 2);
                        ctx.drawImage(img, -width, 0);
                        break;

                    case 8:
                        canvas.width = height;
                        canvas.height = width;
                        ctx.rotate(Math.PI / 2);
                        ctx.drawImage(img, 0, -height);
                        break;

                    case 3:
                        canvas.width = width;
                        canvas.height = height;
                        ctx.rotate(Math.PI);
                        ctx.drawImage(img, -width, -height);
                        break;

                    default:
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0);
                }

                canvas.toBlob(blob => callback(blob), 'image/jpeg', 0.9);
            });

        };

        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
}
// RENDER
function renderPreview() {
    let container = document.getElementById('preview-container');
    container.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        let url = URL.createObjectURL(file);

        let div = document.createElement('div');
        div.className = 'preview-item';

        div.innerHTML = `
            <img src="${url}" onclick="zoomImage(${index})">
            <button class="btn-remove" onclick="removeFile(${index})">x</button>
        `;

        container.appendChild(div);
    });

    syncInput();
}

// DELETE
function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderPreview();
}

// SYNC INPUT
function syncInput() {
    let dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    document.getElementById('cameraInput').files = dt.files;
}

// ================= ZOOM =================
const modalImg = document.getElementById('modalImg');
// MOUSE DOWN
modalImg.addEventListener('mousedown', function (e) {
    if (scale > 1) {
        isMouseDown = true;
        startX = e.clientX - posX;
        startY = e.clientY - posY;
        modalImg.style.cursor = 'grabbing';
    }
});

// MOUSE MOVE
window.addEventListener('mousemove', function (e) {
    if (isMouseDown) {
        posX = e.clientX - startX;
        posY = e.clientY - startY;
        updateTransform();
    }
});

// MOUSE UP
window.addEventListener('mouseup', function () {
    isMouseDown = false;
    modalImg.style.cursor = 'grab';
});
modalImg.addEventListener('wheel', function (e) {
    e.preventDefault();

    scale += e.deltaY * -0.001;
    scale = Math.min(Math.max(1, scale), 3);

    updateTransform();
});
// OPEN
function zoomImage(index) {
    currentIndex = index;

    let modal = document.getElementById('imageModal');

    modalImg.src = URL.createObjectURL(selectedFiles[index]);
    modal.style.display = 'flex';

    scale = 1;
    posX = 0;
    posY = 0;
    updateTransform();
}

// APPLY TRANSFORM
function updateTransform() {
    modalImg.style.transform = `scale(${scale}) translate(${posX}px, ${posY}px)`;
}

// ================= DRAG =================
modalImg.addEventListener('touchstart', function (e) {
    if (scale > 1) {
        isDragging = true;
        startX = e.touches[0].clientX - posX;
        startY = e.touches[0].clientY - posY;
    } else {
        startX = e.touches[0].clientX;
    }
});

modalImg.addEventListener('touchmove', function (e) {

    if (isDragging) {
        posX = e.touches[0].clientX - startX;
        posY = e.touches[0].clientY - startY;
        updateTransform();
    }
});

modalImg.addEventListener('touchend', function (e) {
    isDragging = false;

    let endX = e.changedTouches[0].clientX;

    // SWIPE kalau belum zoom
    if (scale === 1) {
        if (startX - endX > 50) {
            nextImage();
        } else if (endX - startX > 50) {
            prevImage();
        }
    }
});

// ================= DOUBLE TAP ZOOM =================
modalImg.addEventListener('click', function () {
    scale = scale === 1 ? 2 : 1;

    posX = 0;
    posY = 0;

    updateTransform();
});

// ================= NEXT PREV =================
function nextImage() {
    if (currentIndex < selectedFiles.length - 1) {
        currentIndex++;
        zoomImage(currentIndex);
    }
}

function prevImage() {
    if (currentIndex > 0) {
        currentIndex--;
        zoomImage(currentIndex);
    }
}

// CLOSE
document.getElementById('imageModal').onclick = function (e) {
    if (e.target.id === 'imageModal') {
        this.style.display = 'none';
    }
};

// submit pengajuan
$('form').on('submit', function (e) {

    let type = $('[name="type_pengajuan"]').val();

    e.preventDefault(); // 🔥 semua pakai AJAX

    let formData = new FormData(this);

    // =========================
    // ALL DIVISI
    // =========================
    if (type === 'All Divisi') {

        selectedFiles.forEach((file) => {
            formData.append('images[]', file);
        });

        sendAjax('/pengajuan/store-all-divisi', formData);
        return;
    }

    // =========================
    // FINANCE
    // =========================
    formData.set('meta_json', JSON.stringify(excelMeta));
    formData.set('details_json', JSON.stringify(excelDetails));
    formData.set('approval_json', JSON.stringify(excelApproval));

    sendAjax('/pengajuan/store', formData);

});

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

        success: function (res) {

            if (!res.status) {
                Swal.fire({
                    customClass: {
                        popup: 'swal-top'
                    },
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                });
                return;
            }

            Swal.fire({
                customClass: {
                    popup: 'swal-top'
                },
                icon: 'success',
                title: 'Berhasil',
                text: res.message || 'Pengajuan berhasil disimpan',
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(() => {
                location.reload();
            }, 2000);
        },

        error: function (xhr) {

            let res = xhr.responseJSON;

            Swal.fire({
                customClass: {
                    popup: 'swal-top'
                },
                icon: 'error',
                title: 'Error',
                text: res ?.message || 'Server error'
            });
        }
    });

}

function extractTotals(data) {

    let transfer = 0;
    let grand = 0;

    data.forEach(row => {

        if (!row) return;

        row.forEach((cell, i) => {

            if (typeof cell === 'string' && cell.toLowerCase().includes('transfer')) {

                // ambil angka terakhir di row
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

    // kalau kosong
    if (!value) return null;

    // 🔥 kalau Excel number (serial date)
    if (typeof value === 'number') {
        return excelDateToJSDate(value);
    }

    // 🔥 kalau sudah string (misal 04/12/2026)
    if (typeof value === 'string') {
        return value;
    }

    return value;
}
