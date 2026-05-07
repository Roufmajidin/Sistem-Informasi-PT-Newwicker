// pengajuan global var
let excelMeta = {};
let excelDetails = [];
let excelApproval = {};
let transferValue = 0;
let grandTotalValue = 0;
// ================= RESET =================

// ================= GLOBAL =================
let selectedFiles = [];
let currentIndex = 0;
let scale = 1;

// ================= INIT =================
$(document).ready(function () {

    // ================= TYPE CHANGE =================
    $(document).on('change', '[name="type_pengajuan"]', function () {

        let val = $(this).val();

        if (val === 'Finance') {
            $('#finance-section').show();
            $('#divisi-section, #camera-section, #keterangan-section, #urgent-section').hide();
        } else {
            $('#finance-section').hide();
            $('#divisi-section, #camera-section, #keterangan-section, #urgent-section').show();
        }

    });

    // auto trigger (BIAR GA PERLU RELOAD)
    $('[name="type_pengajuan"]').trigger('change');

});

// ================= MODAL =================
$(document).on('click', '#btn-add', function () {
    $('#modal-pengajuan').addClass('active');
});

$(document).on('click', '#btn-close, .close-modal', function () {
    $(this).closest('.modal-full').removeClass('active');
    $('#view-content, #view-meta').html('');
});

// ================= EXCEL =================
$(document).on('change', '#excelInput', function (e) {

    let file = e.target.files[0];
    if (!file) return;

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

        let meta = extractMeta(json);
        let totals = extractTotals(json);

        if (!meta.nomor || !meta.tanggal) {
            alert('⚠️ Excel tidak valid!');
            return;
        }

        $('#excel-meta').show();
        $('#meta-tanggal').text(meta.tanggal);
        $('#meta-nomor').text(meta.nomor);
        $('#meta-type').text(meta.type);

        excelMeta = {
            tanggal: meta.tanggal,
            nomor: meta.nomor,
            type_pembayaran: meta.type,
            transfer: totals.transfer,
            grand_total: totals.grand
        };

        excelDetails = [];

        let headerIndex = findHeaderRow(json);

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

        $('#meta_json').val(JSON.stringify(excelMeta));
        $('#details_json').val(JSON.stringify(excelDetails));

        renderExcel(json);
    };

    reader.readAsArrayBuffer(file);
});

// ================= CAMERA =================
$(document).on('change', '#cameraInput', async function (e) {

    let files = Array.from(e.target.files);

    for (let file of files) {
        let compressed = await compressImage(file);
        selectedFiles.push(compressed);
    }

    renderPreview();
});

// ================= PREVIEW =================
function renderPreview() {

    let container = $('#preview-container');
    container.html('');

    selectedFiles.forEach((file, index) => {

        let url = URL.createObjectURL(file);

        container.append(`
            <div class="preview-item">
                <img src="${url}" data-index="${index}" class="img-preview">
                <button class="btn-remove" data-index="${index}">x</button>
            </div>
        `);
    });

    syncInput();
}

// REMOVE
$(document).on('click', '.btn-remove', function () {
    let index = $(this).data('index');
    selectedFiles.splice(index, 1);
    renderPreview();
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

// ================= SYNC =================
function syncInput() {
    let input = document.getElementById('cameraInput');
    if (!input) return;

    let dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
}

// ================= HELPERS =================
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
    let headers = data[headerIndex];

    let headHtml = '<tr>';
    headers.forEach(h => headHtml += `<th>${h ?? ''}</th>`);
    headHtml += '</tr>';

    thead.html(headHtml);

    for (let i = headerIndex + 1; i < data.length; i++) {

        let row = data[i];
        if (!row || row.length === 0) continue;

        let tr = '<tr>';

        row.forEach(cell => {

            if (typeof cell === 'number' && cell > 30000 && cell < 60000) {
                cell = excelDateToJSDate(cell);
            }

            tr += `<td>${cell ?? ''}</td>`;
        });

        tr += '</tr>';
        tbody.append(tr);
    }


    // ================= HELPERS =================
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
        let headers = data[headerIndex];

        let headHtml = '<tr>';
        headers.forEach(h => headHtml += `<th>${h ?? ''}</th>`);
        headHtml += '</tr>';

        thead.html(headHtml);

        for (let i = headerIndex + 1; i < data.length; i++) {

            let row = data[i];
            if (!row || row.length === 0) continue;

            let tr = '<tr>';

            row.forEach(cell => {

                if (typeof cell === 'number' && cell > 30000 && cell < 60000) {
                    cell = excelDateToJSDate(cell);
                }

                tr += `<td>${cell ?? ''}</td>`;
            });

            tr += '</tr>';
            tbody.append(tr);
        }
    }
    $(document).ready(function () {

        // reset QR
        $('#modal-qr').removeClass('active');

        // auto load data
        setTimeout(() => {
            $('#filter-type').trigger('change');
        }, 100);

    });
    // ================= ZOOM =================
    const modalImg = document.getElementById('zoomistImg');
    if (modalImg) {
        modalImg.addEventListener('mousedown', function (e) {
            if (scale > 1) {
                isMouseDown = true;
                startX = e.clientX - posX;
                startY = e.clientY - posY;
                modalImg.style.cursor = 'grabbing';
            }
        });
    }
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
}
