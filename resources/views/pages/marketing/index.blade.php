@extends('master.master')
@section('title', "Preview Excel + Save Items")
@section('content')
<div class="padding">
    {{-- Upload Excel --}}
    <div class="mb-3">
        <label for="excelFile" class="form-label">Upload File Excel PFI</label>
        <input type="file" class="form-control" id="excelFile" accept=".xls,.xlsx">
    </div>
    {{-- LOADING --}}
<div id="uploadLoadingBox"
    style="
        display:none;
        margin-top:15px;
    ">
    <div style="
        display:flex;
        justify-content:space-between;
        margin-bottom:5px;
    ">
        <span id="loadingText">
            Processing Excel...
        </span>
        <span id="loadingPercent">
            0%
        </span>
    </div>
    <div style="
        width:100%;
        height:25px;
        background:#e9ecef;
        border-radius:10px;
        overflow:hidden;
    ">
        <div id="uploadBar"
            style="
                width:0%;
                height:100%;
                background:#28a745;
                transition:0.2s;
            ">
        </div>
    </div>
</div>
   <div class="row">
    <!-- Kiri: Preview Excel -->
    <div class="col-md-6">
        <h5>Preview Excel</h5>
        <div id="excelPreviewContainer" class="table-responsive"
             style="max-height:500px; overflow:auto; border:1px solid #ddd; padding:5px;">
        </div>
    </div>
    <!-- Kanan: Company Profile + Items -->
    <div class="col-md-6">
        <h5>Company Profile</h5>
        <div class="table-responsive" style="max-height:200px; overflow:auto; border:1px solid #ddd; padding:5px;">
            <table class="table table-bordered" id="companyTable"></table>
        </div>
        <h5 class="mt-4">Items</h5>
        <div class="table-responsive" style="max-height:400px; overflow:auto; border:1px solid #ddd; padding:5px;">
            <table class="table table-bordered table-striped" id="itemsTable">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
        <button id="convertCheckedRows" class="btn btn-primary mt-2">Convert Checked Rows</button>
        <button id="saveItems" class="btn btn-success mt-2">Save Items</button>
    </div>
</div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const skipCheckboxRows = 11; // checkbox mulai dari baris 12
    const companyProfileStartRow = 4; // A5
    const companyProfileEndRow = 9;   // A10
    let rows = [];
    let images = [];
    $('#excelFile').on('change', function(e){
        const file = e.target.files[0];
        if(!file) return;
        let formData = new FormData();
        formData.append('excel_file', file);
        formData.append('_token', '{{ csrf_token() }}');
       $.ajax({
    url: '{{ route("marketing.excel.upload") }}',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    // =========================
    // BEFORE SEND
    // =========================
    beforeSend: function () {
        $('#uploadLoadingBox').show();
        $('#uploadBar').css('width', '0%');
        $('#loadingPercent').text('0%');
        $('#loadingText').text('Uploading Excel...');
    },
    // =========================
    // PROGRESS
    // =========================
    xhr: function () {
        let xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                let percent = Math.round(
                    (e.loaded / e.total) * 100
                );
                $('#uploadBar').css(
                    'width',
                    percent + '%'
                );
                $('#loadingPercent').text(
                    percent + '%'
                );
            }
        });
        return xhr;
    },
    // =========================
    // SUCCESS
    // =========================
    success: function(res){
        $('#loadingText').text(
            'Processing Data...'
        );
        $('#uploadBar').css('width', '100%');
        $('#loadingPercent').text('100%');
        setTimeout(() => {
            $('#uploadLoadingBox').fadeOut();
        }, 500);
        rows = res.rows;
        images = res.images;
        const footerKeywords = [
            'cbm','surcharge','total fob price',
            'payment','production time',
            'bank details','name of bank',
            'address of bank','swift code',
            'account name','account number',
            'made by','approved by'
        ];
        // =========================
        // PREVIEW EXCEL
        // =========================
        let html = '<table border="1" style="border-collapse:collapse;width:100%;">';
        rows.forEach((row,rIndex)=>{
            if(rIndex < skipCheckboxRows) return;
            const firstCol =
                (row[0] || '')
                .toString()
                .trim()
                .toLowerCase();
            if(footerKeywords.includes(firstCol)) return;
            html += '<tr>';
            html += `
                <td>
                    <input type="checkbox"
                        class="rowCheckbox"
                        data-row="${rIndex}">
                </td>
            `;
            row.forEach((val,cIndex)=>{
                html += `<td>${val ?? ''}</td>`;
            });
            html += '</tr>';
        });
        html += '</table>';
        $('#excelPreviewContainer').html(html);
        // =========================
        // COMPANY PROFILE
        // =========================
        const companyTable = $('#companyTable');
        companyTable.empty();
        for(
            let r=companyProfileStartRow;
            r<=companyProfileEndRow;
            r++
        ){
            let key = rows[r][0] ?? '';
            let val = rows[r][2] ?? '';
            val = val
                .toString()
                .replace(/^:\s*/,'');
            if(key && val){
                companyTable.append(`
                    <tr>
                        <th>${key}</th>
                        <td>${val}</td>
                    </tr>
                `);
            }
        }
        // =========================
        // ITEMS TABLE
        // =========================
        renderItemsTable(
            rows,
            images,
            skipCheckboxRows,
            footerKeywords
        );
    },
    // =========================
    // ERROR
    // =========================
    error: function(err){
        $('#uploadLoadingBox').hide();
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Upload Excel gagal'
        });
        console.log(err);
    }
});
    });
    // --- Convert Checked Rows ---
   $('#convertCheckedRows').on('click', function(){
    const checkedRows = $('#excelPreviewContainer tr').has('input.rowCheckbox:checked');
    if(checkedRows.length===0) return;
    const filteredRows = [];
    checkedRows.each(function(){
        const rowIndex = parseInt($(this).find('input.rowCheckbox').data('row'));
        filteredRows.push(rows[rowIndex]);
    });
    const newImagesMap = mapImagesForFilteredRows(filteredRows, rows, images);
    renderItemsTable(filteredRows, newImagesMap, 0, []);
});
    // --- Save Items via AJAX ---
    $('#saveItems').on('click', function(){
        const itemsTable = $('#itemsTable');
        const items = [];
        // Ambil header dari thead
        const headers = [];
        itemsTable.find('thead th').each(function(){
            let text = $(this).text().trim()
                          .replace(/\n/g, ' ')
                          .replace(/\s+/g,' ')
                          .replace(/"/g,'');
            headers.push(text);
        });
        itemsTable.find('tbody tr').each(function(){
            const rowObj = {};
            $(this).find('td').each(function(i){
                const img = $(this).find('img');
                const value = img.length ? img.attr('src') : $(this).text().trim();
                if(headers[i]) rowObj[headers[i]] = value;
            });
            if(Object.values(rowObj).some(v => v !== '')) items.push(rowObj);
        });
      const companyJSON = getCompanyProfileAsJSON();
// console.log(companyJSON);
// console.log('isi', items);
        $.ajax({
            url: '{{ route("marketing.excel.save") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                company: companyJSON,
                items: items
            },
            success: function(res){
                        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Items berhasil disimpan!',
            confirmButtonColor: '#3085d6'
        });
            },
            error: function(err){
                      let message = 'Terjadi kesalahan saat menyimpan!';
        // ambil message dari Laravel kalau ada
        if(err.responseJSON && err.responseJSON.message){
            message = err.responseJSON.message;
        }
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: message,
            confirmButtonColor: '#d33'
        });
            }
        });
    });
    function toSnakeCase(str) {
    if (!str) return '';
    // ganti spasi/tab/dash/dot dengan underscore
    str = str.replace(/[\s\.\-]+/g, '_');
    // hapus karakter selain a-z, A-Z, 0-9, _
    str = str.replace(/[^a-zA-Z0-9_]/g, '');
    // lowercase
    return str.toLowerCase();
}
// Ambil company dari table
function getCompanyProfileAsJSON() {
    const company = {};
    $('#companyTable tr').each(function() {
        const key = $(this).find('th').text().trim();
        const val = $(this).find('td').text().trim();
        if(key) company[toSnakeCase(key)] = val;
    });
    return company;
}
function mapImagesForFilteredRows(filteredRows, originalRows, originalImages) {
    const newMap = {};
    filteredRows.forEach((row, newRIndex) => {
        const oldRIndex = originalRows.indexOf(row);
        row.forEach((val, cIndex) => {
            const key = `${oldRIndex}-${cIndex}`;
            if(originalImages[key]){
                newMap[`${newRIndex}-${cIndex}`] = originalImages[key];
            }
        });
    });
    return newMap;
}
    // --- Fungsi ambil Company Profile ---
    function getCompanyProfile(){
        const company = {};
        $('#companyTable tr').each(function(){
            const key = $(this).find('th').text();
            const val = $(this).find('td').text();
            company[key] = val;
        });
        return company;
    }
    // --- Fungsi render items table ---
 function renderItemsTable(
    dataRows,
    imagesMap,
    skip = 0,
    footerKeywords = []
) {

    const itemsTable = $('#itemsTable');

    itemsTable.find('thead').empty();
    itemsTable.find('tbody').empty();

    if (dataRows.length === 0) {
        return;
    }

    // cari header otomatis
    let headerRowIndex = -1;

    for (let i = 0; i < dataRows.length; i++) {

    const row = dataRows[i].map(v =>
        (v ?? '').toString().toLowerCase().trim()
    );

    const hasPhoto = row.some(v =>
        v.includes('photo')
    );

    const hasDescription = row.some(v =>
        v.includes('description')
    );

    const hasArticle = row.some(v =>
        v.includes('article')
    );

    if (
        hasPhoto &&
        hasDescription &&
        hasArticle
    ) {
        headerRowIndex = i;
        console.log('Header ditemukan di row:', i);
        break;
    }
}

    if (headerRowIndex === -1) {
        console.error('Header tidak ditemukan');
        return;
    }

    const headerRow1 = dataRows[headerRowIndex];
    const headerRow2 = dataRows[headerRowIndex + 1] ?? [];

    const headers = buildHeaders(
        headerRow1,
        headerRow2
    );
    console.log(headers);

    // ======================
    // HEADER TABLE
    // ======================

    let headerHtml = '<tr>';

    headers.forEach(h => {

        headerHtml += `
            <th>${h}</th>
        `;

    });

    headerHtml += '</tr>';

    itemsTable.find('thead').html(
        headerHtml
    );

    // ======================
    // DATA
    // ======================

    for (
        let r = headerRowIndex + 2;
        r < dataRows.length;
        r++
    ) {

        const row = dataRows[r];

        if (!row) continue;

        const firstCol =
            (row[0] ?? '')
            .toString()
            .trim()
            .toLowerCase();

        if (
            footerKeywords.includes(firstCol)
        ) {
            continue;
        }

        if (
            row.every(
                cell =>
                    cell === null ||
                    cell.toString().trim() === ''
            )
        ) {
            continue;
        }

        let rowHtml = '<tr>';

        headers.forEach((header, cIndex) => {

            if (
                imagesMap[
                    `${r}-${cIndex}`
                ]
            ) {

                rowHtml += `
                    <td>
                        <img
                            src="${imagesMap[`${r}-${cIndex}`]}"
                            style="
                                max-width:100px;
                                max-height:80px;
                            "
                        >
                    </td>
                `;

            } else {

                let value =
                    row[cIndex] ?? '';

                rowHtml += `
                    <td>${value}</td>
                `;
            }
        });

        rowHtml += '</tr>';

        itemsTable.find('tbody')
            .append(rowHtml);
    }
}

});
function normalizeHeader(header) {

    if (!header) return '';

    const h = header
        .toLowerCase()
        .trim();

    const map = {

        "buyer's description": "Description",
        "buyers description": "Description",

        "buyer's article nr": "Article Nr.",
        "buyers article nr": "Article Nr.",

        "nw description": "NW Description",

        "qty": "QTY",
        "cbm": "CBM",

        "composition": "Composition",
        "finishing": "Finishing",

        "photo": "Photo",
        "remark": "Remark"
    };

    return map[h] ?? header;
}
function buildHeaders(headerRow1, headerRow2) {

    const headers = [];
    let parentHeader = '';

    const maxCol = Math.max(
        headerRow1.length,
        headerRow2.length
    );

    for (let i = 0; i < maxCol; i++) {

        let h1 = (headerRow1[i] ?? '')
            .toString()
            .replace(/\n/g, ' ')
            .trim();

        let h2 = (headerRow2[i] ?? '')
            .toString()
            .replace(/\n/g, ' ')
            .trim();

        if (h1 !== '') {
            parentHeader = h1;
        }

        if (
            parentHeader.toLowerCase().includes('item') &&
            ['W', 'D', 'H'].includes(h2.toUpperCase())
        ) {

            headers.push(
                `item_${h2.toLowerCase()}`
            );

        } else if (
            parentHeader.toLowerCase().includes('packing') &&
            ['W', 'D', 'H'].includes(h2.toUpperCase())
        ) {

            headers.push(
                `pack_${h2.toLowerCase()}`
            );

        } else {

            headers.push(
                normalizeHeader(
                    h1 || h2
                )
            );
        }
    }

    return headers;
}
</script>
<style>
.rowCheckbox {
    width: 30px;
    height: 30px;
    cursor: pointer;
}
#itemsTable img {
    max-width:100px;
    max-height:80px;
    object-fit:contain;
}
</style>
@endpush
