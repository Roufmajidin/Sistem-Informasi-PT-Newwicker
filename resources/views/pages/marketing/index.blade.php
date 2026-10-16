@extends('master.master')

@section('title', 'Preview Excel + Save Items')

@section('content')
    <div class="pfi-page">
        <div class="pfi-heading">
            <div>
                <div class="pfi-eyebrow">MARKETING / PURCHASE FILE IMPORT</div>
                <h2>PFI Excel Converter</h2>
                <p>Upload Excel, pilih item, lalu periksa hasil konversi di bawah.</p>
            </div>
            <span class="pfi-status"><span class="pfi-status-dot"></span> Ready to import</span>
        </div>

        <div class="pfi-top-grid">
            <section class="pfi-card pfi-upload-card">
                <div class="pfi-card-head">
                    <div class="pfi-heading-icon">01</div>
                    <div>
                        <h5>Upload Excel PFI</h5>
                        <p>Pilih file Excel untuk menampilkan daftar item.</p>
                    </div>
                </div>
                <label class="pfi-upload-zone" for="excelFile"><span class="pfi-upload-symbol">⇧</span><strong>Browse Excel
                        File</strong><span>Klik untuk memilih file dari komputer</span><small>Format: .xls /
                        .xlsx</small></label>
                <input type="file" class="pfi-file-input" id="excelFile" accept=".xls,.xlsx">
                <div class="pfi-file-meta" id="pfiFileMeta" aria-live="polite">No file selected</div>
                <div id="uploadLoadingBox" style="display:none" class="pfi-progress-wrap">
                    <div class="pfi-progress-label"><span id="loadingText">Processing Excel...</span><strong
                            id="loadingPercent">0%</strong></div>
                    <div class="pfi-progress-track">
                        <div id="uploadBar" class="pfi-progress-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="pfi-upload-note">File dibaca menggunakan proses upload yang sama seperti sebelumnya.</div>
            </section>

            <section class="pfi-card pfi-preview-card">
                <div class="pfi-card-head pfi-section-head">
                    <div class="pfi-heading-icon">02</div>
                    <div>
                        <h5>Checklist Item Excel</h5>
                        <p>Centang baris yang ingin dikonversi.</p>
                    </div><span class="pfi-count" id="pfiSelectedCount">0 selected</span>
                </div>
                <div id="excelPreviewContainer" class="pfi-table-scroll pfi-preview-scroll">
                    <div class="pfi-empty" id="pfiPreviewEmpty"><span>▦</span><strong>Belum ada file Excel</strong>
                        <p>Upload file untuk menampilkan baris yang bisa dipilih.</p>
                    </div>
                </div>
                <div class="pfi-card-foot">Tips: gunakan <kbd>Shift</kbd> + klik untuk memilih banyak baris.</div>
                <div class="pfi-actions"><button id="convertCheckedRows" type="button" class="btn pfi-btn-convert">⇄ &nbsp;
                        Convert Checked Rows</button></div>
            </section>
        </div>

        <section class="pfi-card pfi-company-card">
            <div class="pfi-card-head pfi-section-head">
                <div class="pfi-heading-icon">03</div>
                <div>
                    <h5>Company Profile</h5>
                    <p>Informasi perusahaan dari file Excel.</p>
                </div>
            </div>
            <div class="pfi-table-scroll pfi-company-scroll">
                <table class="table table-bordered" id="companyTable"></table>
            </div>
        </section>

        <section class="pfi-card pfi-converted-card">
            <div class="pfi-card-head pfi-section-head">
                <div class="pfi-heading-icon">04</div>
                <div>
                    <h5>Hasil Convert Items</h5>
                    <p>Tabel hasil konversi tampil full width, termasuk dimensi CM dan INCH serta gambar.</p>
                </div>
            </div>
            <div class="pfi-table-scroll pfi-items-scroll">
                <table class="table table-bordered table-striped" id="itemsTable">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="pfi-actions pfi-save-actions"><span>Periksa data sebelum menyimpan ke database.</span><button
                    id="saveItems" type="button" class="btn pfi-btn-save">✓ &nbsp; Save Items</button></div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https\://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script src="https\://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': '{{ csrf_token() }}'

                }

            });







        })
    </script>

    <script>
        $(document).ready(function() {

            const skipCheckboxRows = 11; // checkbox mulai dari baris 12

            const companyProfileStartRow = 4; // A5

            const companyProfileEndRow = 9; // A10

            let rows = [];

            let images = [];

            $('#excelFile').on('change', function(e) {

                const file = e.target.files[0];

                if (!file) return;

                let formData = new FormData();

                formData.append('excel_file', file);

                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({

                    url: '{{ route('marketing.excel.upload') }}',

                    type: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    // =========================

                    // BEFORE SEND

                    // =========================

                    beforeSend: function() {

                        $('#uploadLoadingBox').show();

                        $('#uploadBar').css('width', '0%');

                        $('#loadingPercent').text('0%');

                        $('#loadingText').text('Uploading Excel...');

                    },

                    // =========================

                    // PROGRESS

                    // =========================

                    xhr: function() {

                        let xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener('progress', function(e) {

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

                    success: function(res) {

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

                            'cbm', 'surcharge', 'total fob price',

                            'payment', 'production time',

                            'bank details', 'name of bank',

                            'address of bank', 'swift code',

                            'account name', 'account number',

                            'made by', 'approved by'

                        ];

                        // =========================

                        // PREVIEW EXCEL

                        // =========================

                        let html =
                            '<table border="1" style="border-collapse:collapse;width:100%;">';

                        rows.forEach((row, rIndex) => {

                            if (rIndex < skipCheckboxRows) return;

                            const firstCol =

                                (row[0] || '')

                                .toString()

                                .trim()

                                .toLowerCase();

                            if (footerKeywords.includes(firstCol)) return;

                            html += '<tr>';

                            html += `

                <td>

                    <input type="checkbox"

                        class="rowCheckbox"

                        data-row="${rIndex}">

                </td>

            `;

                            row.forEach((val, cIndex) => {

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

                        for (

                            let r = companyProfileStartRow;

                            r <= companyProfileEndRow;

                            r++

                        ) {

                            let key = rows[r][0] ?? '';

                            let val = rows[r][2] ?? '';

                            val = val

                                .toString()

                                .replace(/^:\s*/, '');

                            if (key && val) {

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

                    error: function(err) {

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

            $('#convertCheckedRows').on('click', function() {

                const checkedRows = $('#excelPreviewContainer tr').has('input.rowCheckbox:checked');

                if (checkedRows.length === 0) return;

                const filteredRows = [];

                checkedRows.each(function() {

                    const rowIndex = parseInt($(this).find('input.rowCheckbox').data('row'));

                    filteredRows.push(rows[rowIndex]);

                });

                const newImagesMap = mapImagesForFilteredRows(filteredRows, rows, images);

                renderItemsTable(filteredRows, newImagesMap, 0, []);

            });

            // --- Save Items via AJAX ---

            $('#saveItems').on('click', function() {

                const itemsTable = $('#itemsTable');

                const items = [];

                // Key asli untuk penyimpanan, bukan teks header bertingkat.
                const headers = itemsTable.data('headers') || [];

                itemsTable.find('tbody tr').each(function() {

                    const rowObj = {};

                    $(this).find('td').each(function(i) {

                        const img = $(this).find('img');

                        const value = img.length ? img.attr('src') : $(this).text().trim();

                        if (headers[i]) rowObj[headers[i]] = value;

                    });

                    if (Object.values(rowObj).some(v => v !== '')) items.push(rowObj);

                });

                const companyJSON = getCompanyProfileAsJSON();

                // console.log(companyJSON);

                // console.log('isi', items);

                $.ajax({

                    url: '{{ route('marketing.excel.save') }}',

                    type: 'POST',

                    contentType: 'application/json',

                    data: JSON.stringify({

                        _token: '{{ csrf_token() }}',

                        company: companyJSON,

                        items: items

                    }),

                    success: function(res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Berhasil',

                            text: 'Items berhasil disimpan!',

                            confirmButtonColor: '#3085d6'

                        });

                    },

                    error: function(err) {

                        let message = 'Terjadi kesalahan saat menyimpan!';

                        // ambil message dari Laravel kalau ada

                        if (err.responseJSON && err.responseJSON.message) {

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

                    if (key) company[toSnakeCase(key)] = val;

                });

                return company;

            }

            function mapImagesForFilteredRows(filteredRows, originalRows, originalImages) {

                const newMap = {};

                filteredRows.forEach((row, newRIndex) => {

                    const oldRIndex = originalRows.indexOf(row);

                    row.forEach((val, cIndex) => {

                        const key = `${oldRIndex}-${cIndex}`;

                        if (originalImages[key]) {

                            newMap[`${newRIndex}-${cIndex}`] = originalImages[key];

                        }

                    });

                });

                return newMap;

            }

            // --- Fungsi ambil Company Profile ---

            function getCompanyProfile() {

                const company = {};

                $('#companyTable tr').each(function() {

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

                itemsTable.data('headers', headers);

                console.log(headers);



                // ======================

                // HEADER TABLE

                // ======================



                // Header 2 tingkat, mengikuti pengelompokan dimensi di Excel.
                // Kolom data dan key untuk Save Items tidak berubah.
                const groups = [{
                        label: 'Item Dimention (CM)',
                        keys: ['item_w', 'item_d', 'item_h']
                    },
                    {
                        label: 'Packing Dimention (CM)',
                        keys: ['pack_w', 'pack_d', 'pack_h']
                    },
                    {
                        label: 'Item Dimention (INCH)',
                        keys: ['item_w_inch', 'item_d_inch', 'item_h_inch']
                    },
                    {
                        label: 'Packing Dimention (INCH)',
                        keys: ['pack_w_inch', 'pack_d_inch', 'pack_h_inch']
                    }
                ];

                let headerTop = '<tr>';
                let headerBottom = '<tr>';
                let hasGroups = false;

                for (let i = 0; i < headers.length;) {
                    const group = groups.find(g => g.keys.every(
                        (key, offset) => headers[i + offset] === key
                    ));
                    if (group) {
                        headerTop += `<th colspan="3" class="text-center">${group.label}</th>`;
                        headerBottom += '<th>W</th><th>D</th><th>H</th>';
                        hasGroups = true;
                        i += 3;
                    } else {
                        headerTop += `<th rowspan="2">${headers[i] ?? ''}</th>`;
                        i++;
                    }
                }
                headerTop += '</tr>';
                headerBottom += '</tr>';
                itemsTable.find('thead').html(hasGroups ? headerTop + headerBottom : headerTop);

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
            const maxCol = Math.max(headerRow1.length, headerRow2.length);

            for (let i = 0; i < maxCol; i++) {
                const h1 = (headerRow1[i] ?? '').toString().replace(/\n/g, ' ').trim();
                const h2 = (headerRow2[i] ?? '').toString().replace(/\n/g, ' ').trim();
                if (h1 !== '') parentHeader = h1;

                const parent = parentHeader.toLowerCase();
                const axis = h2.toLowerCase();
                const isItem = parent.includes('item');
                const isPacking = parent.includes('packing');
                const isInch = /\binch(?:es)?\b/i.test(parent);

                if ((isItem || isPacking) && ['w', 'd', 'h'].includes(axis)) {
                    const prefix = isItem ? 'item' : 'pack';
                    headers.push(`${prefix}_${axis}${isInch ? '_inch' : ''}`);
                } else {
                    headers.push(normalizeHeader(h1 || h2));
                }
            }
            return headers;
        }

        let lastChecked = null;



        $(document).on('click', '.rowCheckbox', function(e) {



            if (!lastChecked) {

                lastChecked = this;

                return;

            }



            if (e.shiftKey) {



                const checkboxes = $('.rowCheckbox');



                const start = checkboxes.index(this);

                const end = checkboxes.index(lastChecked);



                const from = Math.min(start, end);

                const to = Math.max(start, end);



                checkboxes.slice(from, to + 1).prop(

                    'checked',

                    lastChecked.checked

                );

            }



            lastChecked = this;

        });
    </script>

    <style>
        .rowCheckbox {

            width: 30px;

            height: 30px;

            cursor: pointer;

        }

        #itemsTable img {

            max-width: 100px;

            max-height: 80px;

            object-fit: contain;

        }


        /* Marketing PFI presentation only — existing upload/convert/save logic is unchanged */
        .pfi-page {
            padding: 28px 24px 48px;
            background: #f4f7fb;
            min-height: calc(100vh - 80px);
            font-family: inherit;
            color: #24344d
        }

        .pfi-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
            flex-wrap: wrap
        }

        .pfi-heading h2 {
            font-size: 26px;
            line-height: 1.25;
            font-weight: 750;
            margin: 5px 0 7px;
            color: #162b49
        }

        .pfi-heading p,
        .pfi-card-head p {
            margin: 0;
            color: #7a899d;
            font-size: 13px;
            line-height: 1.55
        }

        .pfi-eyebrow {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.7px;
            color: #3277be
        }

        .pfi-status {
            font-size: 12px;
            font-weight: 700;
            color: #197b5b;
            background: #e5f6ed;
            border: 1px solid #c8ebd9;
            border-radius: 99px;
            padding: 9px 13px
        }

        .pfi-status-dot {
            display: inline-block;
            background: #25b47c;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 6px
        }

        .pfi-card {
            background: #fff;
            border: 1px solid #e3eaf3;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(28, 51, 86, .035);
            min-width: 0;
            overflow: hidden
        }

        .pfi-upload-card {
            padding: 22px;
            margin-bottom: 22px
        }

        .pfi-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0
        }

        .pfi-card-head h5 {
            font-size: 15px;
            font-weight: 750;
            margin: 0 0 3px;
            color: #233652
        }

        .pfi-heading-icon {
            width: 35px;
            height: 35px;
            flex: 0 0 35px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #eaf2fc;
            color: #3175c2;
            font-size: 12px;
            font-weight: 800
        }

        .pfi-upload-zone {
            margin: 18px 0 0;
            border: 2px dashed #c4d5eb;
            background: #f8fbff;
            border-radius: 13px;
            padding: 24px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            text-align: center;
            transition: .2s
        }

        .pfi-upload-zone:hover,
        .pfi-upload-zone:focus-within {
            border-color: #397ac9;
            background: #f0f7ff
        }

        .pfi-upload-symbol {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            background: #e5f0fe;
            color: #2e74bf;
            border-radius: 12px;
            font-size: 25px;
            margin-bottom: 5px
        }

        .pfi-upload-zone strong {
            color: #23436a;
            font-size: 14px
        }

        .pfi-upload-zone span:not(.pfi-upload-symbol) {
            color: #73849b;
            font-size: 12px
        }

        .pfi-upload-zone small {
            color: #97a5b6;
            font-size: 11px;
            margin-top: 5px
        }

        .pfi-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            overflow: hidden
        }

        .pfi-file-meta {
            font-size: 12px;
            color: #708198;
            margin-top: 11px;
            overflow-wrap: anywhere
        }

        .pfi-progress-wrap {
            margin-top: 18px
        }

        .pfi-progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #536a85;
            margin-bottom: 8px
        }

        .pfi-progress-track {
            height: 9px;
            background: #e7eef7;
            border-radius: 20px;
            overflow: hidden
        }

        .pfi-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3889e1, #20b99a);
            border-radius: 20px;
            transition: width .2s
        }

        .pfi-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            gap: 20px;
            align-items: start
        }

        .pfi-side {
            display: grid;
            gap: 20px;
            min-width: 0
        }

        .pfi-section-head {
            padding: 19px 18px;
            border-bottom: 1px solid #edf1f6
        }

        .pfi-section-head>div:nth-child(2) {
            min-width: 0;
            flex: 1
        }

        .pfi-count {
            white-space: nowrap;
            font-size: 11px;
            color: #3274b8;
            background: #eaf3fe;
            padding: 6px 10px;
            border-radius: 99px;
            font-weight: 750
        }

        .pfi-table-scroll {
            overflow: auto;
            width: 100%;
            scrollbar-width: thin;
            scrollbar-color: #cbd8e8 transparent
        }

        .pfi-preview-scroll {
            max-height: 650px;
            min-height: 290px
        }

        .pfi-company-scroll {
            max-height: 235px;
            min-height: 95px
        }

        .pfi-items-scroll {
            max-height: 450px;
            min-height: 165px
        }

        .pfi-table-scroll table {
            font-size: 12px;
            margin-bottom: 0;
            white-space: nowrap;
            width: max-content;
            min-width: 100%;
            border-collapse: collapse
        }

        .pfi-table-scroll th {
            background: #edf4fc !important;
            color: #344d6b !important;
            font-weight: 750;
            padding: 11px !important;
            border-color: #e1e9f2 !important;
            position: sticky;
            top: 0;
            z-index: 1
        }

        .pfi-table-scroll td {
            padding: 9px 11px !important;
            border-color: #e8eef5 !important;
            vertical-align: middle;
            color: #40526a
        }

        .pfi-table-scroll tr:hover td {
            background: #f5f9fe
        }

        .pfi-preview-scroll>table {
            width: max-content !important;
            min-width: 100%
        }

        .pfi-preview-scroll td {
            padding: 8px 10px !important;
            font-size: 12px
        }

        .pfi-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 285px;
            text-align: center;
            color: #91a0b3
        }

        .pfi-empty span {
            font-size: 36px;
            color: #b3c5da
        }

        .pfi-empty strong {
            font-size: 13px;
            color: #617590;
            margin: 10px 0 3px
        }

        .pfi-empty p {
            font-size: 12px;
            margin: 0
        }

        .pfi-card-foot {
            padding: 12px 17px;
            border-top: 1px solid #edf1f6;
            color: #8493a7;
            font-size: 11px
        }

        .pfi-card-foot kbd {
            background: #edf2f8;
            color: #4d647d;
            border-radius: 4px;
            padding: 2px 5px
        }

        .pfi-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 16px 18px;
            border-top: 1px solid #edf1f6
        }

        .pfi-actions .btn {
            font-size: 12px;
            font-weight: 750;
            border-radius: 9px;
            padding: 11px 14px;
            box-shadow: none
        }

        .pfi-btn-convert {
            background: #eaf3ff !important;
            color: #2166ad !important;
            border: 1px solid #c9dff8 !important
        }

        .pfi-btn-convert:hover {
            background: #dcecff !important
        }

        .pfi-btn-save {
            background: #198b70 !important;
            color: white !important;
            border: 1px solid #198b70 !important
        }

        .pfi-btn-save:hover {
            background: #11735b !important
        }

        .rowCheckbox {
            width: 19px !important;
            height: 19px !important;
            cursor: pointer;
            accent-color: #367dcc
        }

        #itemsTable img {
            max-width: 100px;
            max-height: 80px;
            object-fit: contain;
            border-radius: 6px
        }

        @media(max-width:1100px) {
            .pfi-workspace {
                grid-template-columns: 1fr
            }

            .pfi-preview-scroll {
                max-height: 440px
            }
        }

        @media(max-width:600px) {
            .pfi-page {
                padding: 16px 12px 32px
            }

            .pfi-heading h2 {
                font-size: 22px
            }

            .pfi-upload-card {
                padding: 16px
            }

            .pfi-section-head {
                padding: 14px 12px
            }

            .pfi-actions .btn {
                width: 100%
            }

            .pfi-status {
                display: none
            }
        }

        /* Layout update only: upload + checklist side-by-side, converter full width */
        .pfi-top-grid {
            display: grid;
            grid-template-columns: minmax(270px, 0.34fr) minmax(0, 0.66fr);
            gap: 20px;
            align-items: stretch;
            margin-bottom: 20px
        }

        .pfi-top-grid>.pfi-card {
            margin-bottom: 0;
            min-width: 0
        }

        .pfi-top-grid .pfi-upload-card {
            display: flex;
            flex-direction: column
        }

        .pfi-top-grid .pfi-upload-zone {
            flex: 0 0 auto;
            margin-top: 20px
        }

        .pfi-upload-note {
            font-size: 11px;
            color: #91a0b3;
            margin-top: auto;
            padding-top: 18px
        }

        .pfi-top-grid .pfi-preview-card {
            display: flex;
            flex-direction: column
        }

        .pfi-top-grid .pfi-preview-scroll {
            min-height: 290px;
            max-height: 410px;
            flex: 1
        }

        .pfi-top-grid .pfi-preview-card .pfi-actions {
            margin-top: auto
        }

        .pfi-company-card {
            margin-bottom: 20px
        }

        .pfi-company-scroll {
            max-height: 210px;
            min-height: 70px
        }

        .pfi-converted-card {
            width: 100%;
            min-width: 0
        }

        .pfi-converted-card .pfi-items-scroll {
            max-height: 650px;
            min-height: 260px;
            overflow: auto
        }

        .pfi-save-actions {
            justify-content: space-between;
            align-items: center
        }

        .pfi-save-actions>span {
            font-size: 12px;
            color: #7a899d
        }

        @media(max-width:950px) {
            .pfi-top-grid {
                grid-template-columns: 1fr
            }

            .pfi-top-grid .pfi-preview-scroll {
                max-height: 400px
            }
        }

        @media(max-width:600px) {
            .pfi-save-actions>span {
                width: 100%
            }

            .pfi-top-grid {
                gap: 14px
            }
        }
    </style>


    <script>
        $(function() {
            $('#excelFile').on('change.pfiUI', function() {
                const file = this.files && this.files[0];
                $('#pfiFileMeta').text(file ? 'Selected: ' + file.name : 'No file selected');
                $('#pfiSelectedCount').text('0 selected');
            });

            function updatePfiSelection() {
                $('#pfiSelectedCount').text($('.rowCheckbox:checked').length + ' selected');
            }
            $(document).on('change.pfiUI click.pfiUI', '.rowCheckbox', updatePfiSelection);
            $('#convertCheckedRows').on('click.pfiUI', function() {
                updatePfiSelection();
            });
        });
    </script>
@endpush
