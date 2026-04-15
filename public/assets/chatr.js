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

        // default semua 0
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

        // ambil data QC sesuai item
        let rows = globalQc.filter(q =>
            parseInt(q.detail_po_id) === parseInt(d.id)
        );

        rows.forEach(r => {

            let kategori = r.kategori;

            if (!dataQc[kategori]) return;

            dataQc[kategori].inspect += parseInt(r.jumlah_inspect || 0);
            dataQc[kategori].pass += parseInt(r.passed || 0);
            dataQc[kategori].reject += parseInt(r.rejected || 0);
        });

        // function render cell
        function cell(d) {
            return `
                <div>
                    <small>
                        <b>I:</b> ${d.inspect} |
                        <span style="color:green">✔ ${d.pass}</span> |
                        <span style="color:red">✖ ${d.reject}</span>
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
