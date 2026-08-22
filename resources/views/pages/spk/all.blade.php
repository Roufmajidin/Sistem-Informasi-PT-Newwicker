@extends('master.master')
@section('title', 'All SPK')
@section('content')
    <div class="padding">

        <div class="box">

            {{-- HEADER --}}
            <div class="box-header">

                <div class="row">

                    <div class="col-md-6">

                        <h2 class="m-0">
                            Semua SPK
                        </h2>

                        <small class="text-muted">
                            System Informasi PT Newwicker Indonesia
                        </small>

                    </div>

                    <div class="col-md-6 text-right">

                        <select id="spkTypeFilter" class="form-control" style="width:220px;display:inline-block"
                            {{ $isRndSpk ? 'disabled' : '' }}>
                            <option value="ALL" {{ !$isRndSpk ? 'selected' : '' }}>
                                Semua SPK
                            </option>

                            <option value="NW">
                                SPK Produksi (NW)
                            </option>

                            <option value="NWS" {{ $isRndSpk ? 'selected' : '' }}>
                                SPK Sampel (NWS)
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            {{-- BODY --}}
            <div class="box-body">

                <div class="row">

                    {{-- ================= LEFT ================= --}}
                    <div class="col-lg-4 col-md-5">

                        <div class="spk-sidebar">

                            <div class="sidebar-toolbar">

                                <input type="text" id="searchPo" class="form-control"
                                    placeholder="Cari SPK, Buyer atau PO...">

                            </div>

                            <div class="sidebar-table">

                                <table class="table table-hover table-spk">

                                    <thead>

                                        <tr>

                                            <th width="40">
                                                No
                                            </th>

                                            <th>
                                                Buyer Name
                                            </th>

                                            <th width="110">
                                                PO
                                            </th>

                                            <th width="120">
                                                Aksi
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody id="po-table-body">

                                        <tr>

                                            <td colspan="4" class="text-center text-muted">

                                                Loading...

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    {{-- ================= RIGHT ================= --}}
                    <div class="col-lg-8 col-md-7" id="detailColumn">

                        <div id="spkDetailBox" style="display:none">

                            {{-- TOP BAR --}}
                            <div class="detail-topbar">

                                {{-- <div class="pull-left">

                <a
                    href="javascript:void(0)"
                    id="btnBackSidebar"
                    class="btn-back">

                    <i class="fa fa-arrow-left"></i>

                    Kembali ke daftar SPK

                </a>

            </div> --}}

                                <div class="pull-right">

                                    {{-- <button
                    class="btn btn-outline-primary">

                    <i class="fa fa-download"></i>

                    Download All

                </button> --}}

                                    <a href="javascript:void(0)" id="btnCreateSpk" class="btn btn-primary">

                                        <i class="fa fa-plus"></i>

                                        Buat SPK Baru

                                    </a>
                                    <button id="btnExpandDetail" class="btn btn-default">

                                        <i class="fa fa-expand"></i>

                                    </button>

                                </div>

                                <div class="clearfix"></div>

                            </div>

                            {{-- CARD --}}
                            <div class="detail-card">

                                <div class="detail-header">

                                    <div>

                                        <h5>

                                            Detail SPK

                                        </h5>

                                        <div id="detailPoTitle" class="detail-po">

                                        </div>

                                    </div>

                                </div>

                                {{-- SUMMARY --}}
                                <div class="row summary-row" style="margin-bottom:10px">

                                    <div class="col-md-3">

                                        <div class="summary-card">

                                            <div class="summary-icon blue">

                                                <i class="fa fa-cube"></i>

                                            </div>

                                            <div>

                                                <small>Total Item</small>

                                                <h3 id="sumItem">

                                                    0

                                                </h3>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="summary-card">

                                            <div class="summary-icon green">

                                                <i class="fa fa-dropbox"></i>

                                            </div>

                                            <div>

                                                <small>Total Qty</small>

                                                <h3 id="sumQty">

                                                    0

                                                </h3>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="summary-card">

                                            <div class="summary-icon purple">

                                                <i class="fa fa-arrows-alt"></i>

                                            </div>

                                            <div>

                                                <small>Total CBM</small>

                                                <h3 id="sumCBM">

                                                    0

                                                </h3>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-md-3">

                                        <div class="summary-card">

                                            <div class="summary-icon blue">

                                                <i class="fa fa-file"></i>

                                            </div>

                                            <div>

                                                <small>Dokumen</small>

                                                <h3 id="sumDoc">

                                                    0

                                                </h3>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- TABLE --}}
                            <div class="box table-box">

                                <div class="table-responsive">

                                    <table class="table table-bordered">

                                        <tbody id="spk-detail-body">

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

</div> @endsection
@push('scripts')
    <!-- jQuery (WAJIB PERTAMA) -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        console.log(window.history.length);
        // ===============================
        // GLOBAL VARIABLE (WAJIB DI LUAR)
        // ===============================
        let currentKeyword = '';
        let currentType = '{{ $isRndSpk ? 'NWS' : 'ALL' }}';
        let cacheData = [];

        // ===============================
        // SEARCH TAMBAHAN SPK
        // Cari: PO / Buyer / Supplier / Barang / Article / No SPK
        // ===============================
        function matchSpkKeyword(po, keyword) {

            if (!keyword) {
                return true;
            }

            keyword = String(keyword)
                .trim()
                .toLowerCase();

            if (!keyword) {
                return true;
            }

            // ===============================
            // PO / BUYER
            // ===============================
            const noPo = String(
                po.data_po?.no_po ?? ''
            ).toLowerCase();

            const buyer = String(
                po.data_po?.company ?? ''
            ).toLowerCase();

            if (
                noPo.includes(keyword) ||
                buyer.includes(keyword)
            ) {
                return true;
            }

            // ===============================
            // ITEMS PO
            // ===============================
            const items = po.data_po?.items || [];

            for (const item of items) {

                const detail = item.detail || {};

                const namaBarang = String(
                    detail.nama ??
                    detail.name ??
                    detail.nama_barang ??
                    item.nama ??
                    item.name ??
                    ''
                ).toLowerCase();

                const kodeBarang = String(
                    detail.kode ??
                    detail.kode_barang ??
                    detail.article_nr_ ??
                    item.kode ??
                    item.kode_barang ??
                    ''
                ).toLowerCase();

                const description = String(
                    detail.description ??
                    item.description ??
                    ''
                ).toLowerCase();

                if (
                    namaBarang.includes(keyword) ||
                    kodeBarang.includes(keyword) ||
                    description.includes(keyword)
                ) {
                    return true;
                }

                // ===============================
                // SUMMARY
                // kategori -> supplier -> SPK
                // ===============================
                const summary = item.summary || {};

                for (const kategori of Object.keys(summary)) {

                    const suppliers =
                        summary[kategori] || {};

                    for (const supplierName of Object.keys(suppliers)) {

                        // Nama supplier
                        if (
                            String(supplierName)
                                .toLowerCase()
                                .includes(keyword)
                        ) {
                            return true;
                        }

                        const supplierData =
                            suppliers[supplierName] || {};

                        const spks =
                            supplierData.spks || [];

                        for (const spk of spks) {

                            // No SPK
                            const noSpk = String(
                                spk.no_spk ?? ''
                            ).toLowerCase();

                            if (
                                noSpk.includes(keyword)
                            ) {
                                return true;
                            }

                            // Supplier dari object SPK
                            const supplier = String(
                                spk.sup ??
                                spk.supplier ??
                                spk.supplier_name ??
                                supplierName ??
                                ''
                            ).toLowerCase();

                            if (
                                supplier.includes(keyword)
                            ) {
                                return true;
                            }

                            // Nama barang di object SPK
                            const spkNama = String(
                                spk.nama ??
                                spk.name ??
                                spk.nama_barang ??
                                ''
                            ).toLowerCase();

                            if (
                                spkNama.includes(keyword)
                            ) {
                                return true;
                            }

                            // Article / kode di object SPK
                            const spkKode = String(
                                spk.kode ??
                                spk.kode_barang ??
                                spk.article_code ??
                                ''
                            ).toLowerCase();

                            if (
                                spkKode.includes(keyword)
                            ) {
                                return true;
                            }

                            // ===============================
                            // Jika SPK punya data object
                            // ===============================
                            const spkData =
                                spk.data || {};

                            const dataSupplier =
                                String(
                                    spkData.sup ?? ''
                                ).toLowerCase();

                            if (
                                dataSupplier.includes(keyword)
                            ) {
                                return true;
                            }

                            const spkItems =
                                spkData.items || [];

                            for (const spkItem of spkItems) {

                                const nama = String(
                                    spkItem.nama ??
                                    spkItem.name ??
                                    spkItem.nama_barang ??
                                    ''
                                ).toLowerCase();

                                const kode = String(
                                    spkItem.kode ??
                                    spkItem.kode_barang ??
                                    spkItem.article_code ??
                                    ''
                                ).toLowerCase();

                                if (
                                    nama.includes(keyword) ||
                                    kode.includes(keyword)
                                ) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }

            return false;
        }


        // ===============================
        // DOWNLOAD SPK
        // ===============================
        $('#searchPo').on('input', function() {

            currentKeyword = $(this).val().trim().toLowerCase();

            loadSpkTable();

        });
        $(document).on('click', '.btn-download-spk', function() {
            const spkId = $(this).data('id');
            window.open(`/spk/export/${spkId}`, '_blank');
        });

        // ===============================
        // LOAD DATA PO
        // ===============================
        function loadSpkTable() {

            $.get("{{ route('spk.all') }}", function(res) {

                let data = res;

                // FILTER
                data = res.filter(function(po) {

                    let noPo = (po.data_po?.no_po || '');
                    let buyer = (po.data_po?.company || '');

                    let prefix = noPo.split(' ')[0];

                    let matchType =
                        currentType === 'ALL' ||
                        prefix === currentType;

                    let matchKeyword =
                        matchSpkKeyword(
                            po,
                            currentKeyword
                        );

                    return matchType && matchKeyword;

                });

                // cache harus isi data yang tampil
                cacheData = data;

                let html = '';
                let no = 1;

                // LOOP DATA YANG SUDAH DIFILTER
                data.forEach((po, index) => {

                    let poId = po.data_po?.id;
                    let poNo = po.data_po?.no_po ?? '-';
                    let buyerName = po.data_po?.company ?? '-';

                    let spkIds = new Set();

                    (po.data_po?.items || []).forEach(item => {

                        let summary = item.summary || {};

                        Object.values(summary).forEach(suppliers => {
                            Object.values(suppliers).forEach(supplier => {
                                (supplier.spks || []).forEach(spk => {
                                    spkIds.add(spk.spk_id);
                                });
                            });
                        });

                    });

                    let spkCount = spkIds.size;

                    html += `
<tr class="po-row">

    <td class="text-center">
        ${no++}
    </td>

    <td>

        <div class="buyer-name">
            ${buyerName}
        </div>

    </td>

    <td>

        <span class="po-number">
            ${poNo}
        </span>

    </td>

    <td class="text-center">

        <button
            class="btn btn-outline-primary btn-sm btn-view-spk"
            data-index="${index}">

            Lihat SPK (${spkCount})

        </button>

    </td>

    <td width="40" class="text-center">

        <button
            class="btn btn-link p-0">

            <i class="fa fa-ellipsis-v"></i>

        </button>

    </td>

</tr>
`;
                });

                $('#po-table-body').html(html);
            });
        }

        // ===============================
        // CLICK VIEW SPK (EXPAND TABLE)
        // ===============================
        $(document).on('click', '.btn-view-spk', function() {

            $('#po-table-body tr')
                .removeClass('selected-row');

            $(this)
                .closest('tr')
                .addClass('selected-row');
            let index =
                $(this).data('index');

            let po =
                cacheData[index];

            let poId =
                po.data_po.id;

            let items =
                po.data_po.items || [];
            // summary
            // ===============================
            // SUMMARY
            // ===============================

            let totalItem = items.length;

            let totalQty = 0;
            let totalCBM = 0;
            let totalDoc = 0;

            items.forEach(item => {

                let d = item.detail || {};

                totalQty += parseFloat(d.qty || 0);

                totalCBM += parseFloat(d.total_cbm || 0);

                let summary = item.summary || {};

                Object.values(summary).forEach(suppliers => {

                    Object.values(suppliers).forEach(supplier => {

                        totalDoc += (supplier.spks || []).length;

                    });

                });

            });

            $('#sumItem').text(totalItem);

            $('#sumQty').text(totalQty);

            $('#sumCBM').text(totalCBM.toFixed(3));

            $('#sumDoc').text(totalDoc);


            $('#btnCreateSpk').attr(
                'href',
                '/spk/' + poId
            );
            /*
            |--------------------------------------------------------------------------
            | CATEGORY
            |--------------------------------------------------------------------------
            */

            let categories = [];

            items.forEach(item => {

                let summary = item.summary || {};

                Object.keys(summary).forEach(kategori => {

                    let key = kategori
                        .trim()
                        .toUpperCase();

                    if (!categories.includes(key)) {
                        categories.push(key);
                    }

                });

            });

            // urutkan alphabet
            categories.sort();

            let totalCategory = categories.length;

            /*
            |--------------------------------------------------------------------------
            | CATEGORY COUNT
            |--------------------------------------------------------------------------
            */

            // let totalCategory =
            //     Object.keys(categories).length;

            /*
            |--------------------------------------------------------------------------
            | TABLE HEADER
            |--------------------------------------------------------------------------
            */

            let html = `

        <table class="table table-sm table-bordered">

            <thead>

                <tr>

                    <th rowspan="2">
                        No
                    </th>

                    <th rowspan="2">
                        Article
                    </th>

                    <th rowspan="2">
                        Description
                    </th>

                    <th rowspan="2">
                        Qty
                    </th>

                    <th rowspan="2">
                        CBM
                    </th>

                    <th colspan="${totalCategory}">
                        SPK
                    </th>

                    <th rowspan="2">
                        Act
                    </th>

                </tr>

                <tr>

                   ${categories.map(cat => `
        <th>${cat}</th>
    `).join('')}

                </tr>

            </thead>

            <tbody>

    `;

            /*
            |--------------------------------------------------------------------------
            | LOOP ITEM
            |--------------------------------------------------------------------------
            */

            items.forEach((item, i) => {
                console.log('PO:', po.data_po.no_po);
                console.log('ITEMS:', items);
                let d =
                    item.detail || {};

                let summary =
                    item.summary || {};

                /*
                |--------------------------------------------------------------------------
                | CREATE EMPTY MAP
                |--------------------------------------------------------------------------
                */

                let map = {};

                categories.forEach(cat => {
                    map[cat] = [];
                });

                /*
                |--------------------------------------------------------------------------
                | LOOP SUMMARY
                |--------------------------------------------------------------------------
                */

                Object.keys(summary).forEach(kategori => {

                    Object.keys(summary[kategori]).forEach(supplier => {

                        let data =
                            summary[kategori][supplier];

                        (data.spks || []).forEach(spk => {

                            let tgl =
                                spk.tgl_selesai || '';

                            let deadline =
                                getDeadlineLabel(tgl);

                            /*
                            |--------------------------------------------------------------------------
                            | CONTENT
                            |--------------------------------------------------------------------------
                            */

                            let content = `

                        <div class="spk-card"
                             data-id="${spk.spk_id}"
                             data-no="${spk.no_spk}">

                            <b>
                                ${supplier}
                            </b>

                            (${spk.qty})

                            <br>

                            <small>

                                ${spk.no_spk}

                            </small>

                            <br>

                            <small>

                                ${deadline}

                            </small>

                            <br>

                            <div class="spk-btn-group">



                                <button
                                    class="btn btn-xs btn-warning btn-edit-spk"
                                    data-id="${spk.spk_id}">

                                    Edit

                                </button>
                                 <button
                                class="btn btn-xs btn-danger btn-delete-spk"
                                data-id="${spk.spk_id}"
                                data-no="${spk.no_spk}"
                                title="Delete">

                                <i class="fa fa-trash"></i>

                            </button>

                            </div>

                            <div class="hold-progress"></div>

                        </div>

                    `;

                            /*
                            |--------------------------------------------------------------------------
                            | NORMALIZE CATEGORY
                            |--------------------------------------------------------------------------
                            */

                            let key = kategori
                                .trim()
                                .toUpperCase();

                            if (map[key]) {
                                map[key].push(content);
                            }

                        });

                    });

                });

                /*
                |--------------------------------------------------------------------------
                | MAX ROW
                |--------------------------------------------------------------------------
                */

                let lengths =
                    Object.keys(map).map(cat => {

                        return map[cat].length;

                    });

                let maxRow =
                    Math.max(...lengths, 1);

                /*
                |--------------------------------------------------------------------------
                | ROW TABLE
                |--------------------------------------------------------------------------
                */

                for (let r = 0; r < maxRow; r++) {

                    html += `<tr>`;

                    /*
                    |--------------------------------------------------------------------------
                    | FIRST ROW
                    |--------------------------------------------------------------------------
                    */

                    if (r === 0) {

                        html += `

                    <td rowspan="${maxRow}">

                        ${i + 1}

                    </td>

                   <td rowspan="${maxRow}"
    style="
        text-align:center;
        width:120px;
        min-width:120px;
        max-width:120px;
        vertical-align:top;
    ">

                       <div class="article-box">

    ${
        d.images && d.images.length
        ? `
                <img
                    src="${d.images[0]}"
                    class="article-image">
            `
        : d.photo
        ? `
                <img
                    src="${d.photo}"
                    class="article-image">
            `
        : `
                <div class="article-no-image">

                    No Image

                </div>
            `
    }

    <div class="article-code">

        ${d.article_nr_ ?? '-'}

    </div>

</div>
                    </td>

                    <td rowspan="${maxRow}">

                        ${d.description ?? '-'}

                    </td>

                    <td rowspan="${maxRow}">

                        ${d.qty ?? 0}

                    </td>

                    <td rowspan="${maxRow}">

                        ${parseFloat(d.total_cbm ?? 0).toFixed(3)}

                    </td>

                `;

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CATEGORY COLUMN
                    |--------------------------------------------------------------------------
                    */

                    categories.forEach(cat => {

                        html += `
                <td>
                    ${map[cat][r] || ''}
                </td>
            `;

                    });

                    /*
                    |--------------------------------------------------------------------------
                    | ACTION
                    |--------------------------------------------------------------------------
                    */

                    if (r === 0) {

                        html += `

                    <td rowspan="${maxRow}">

                        <button
                            class="btn btn-sm btn-primary">

                            Action

                        </button>

                    </td>

                `;

                    }

                    html += `</tr>`;

                }

            });

            html += `

            </tbody>

        </table>

    `;

            /*
            |--------------------------------------------------------------------------
            | SHOW DETAIL
            |--------------------------------------------------------------------------
            */

            $('#spkDetailBox')
                .show();

            $('#detailPoTitle').html(`

        <b>

            ${po.data_po.no_po}

        </b>

        •

        ${po.data_po.company}

    `);

            $('#spk-detail-body').html(`

        <tr>

            <td colspan="3"
                style="padding:0;border:none">

                ${html}

            </td>

        </tr>

    `);

            /*
            |--------------------------------------------------------------------------
            | SCROLL
            |--------------------------------------------------------------------------
            */

            $('html, body').animate({

                scrollTop: $('#spkDetailBox').offset().top - 20

            }, 400);

        });

        // ===============================
        // EDIT SPK
        // ===============================
        $(document).on('click', '.btn-edit-spk', function() {
            let spkId = $(this).data('id');
            window.location.href = `/spk/edit/${spkId}`;
        });

        $(document).on('click', '.spk-link', function() {
            let spkId = $(this).data('spk-id');
            window.location.href = `/spk/edit/${spkId}`;
        });

        // ===============================
        // INIT
        // ===============================

        $(document).ready(function() {

            let initialType =
                '{{ $isRndSpk ? 'NWS' : 'ALL' }}';

            loadSpkTable(
                initialType
            );

        });
        $('#spkTypeFilter').on('change', function() {

            currentType = $(this).val();

            loadSpkTable();

        });
        // load ddline spk
        function getDeadlineLabel(dateStr) {

            if (!dateStr) return '-';

            /*
            |--------------------------------------------------------------------------
            | FORMAT DD/MM/YYYY
            |--------------------------------------------------------------------------
            */

            let parts =
                dateStr.split('/');

            let date = new Date(

                parts[2],

                parts[1] - 1,

                parts[0]
            );

            let today =
                new Date();

            /*
            |--------------------------------------------------------------------------
            | RESET HOUR
            |--------------------------------------------------------------------------
            */

            today.setHours(
                0, 0, 0, 0
            );

            date.setHours(
                0, 0, 0, 0
            );

            /*
            |--------------------------------------------------------------------------
            | DIFFERENCE
            |--------------------------------------------------------------------------
            */

            let diff = Math.ceil(

                (date - today)

                /

                (1000 * 60 * 60 * 24)
            );

            /*
            |--------------------------------------------------------------------------
            | TODAY
            |--------------------------------------------------------------------------
            */

            if (diff === 0) {

                return `

            <span style="
                color:green;
                font-weight:600;
            ">

                ✅ Hari ini

            </span>
        `;
            }

            /*
            |--------------------------------------------------------------------------
            | LATE
            |--------------------------------------------------------------------------
            */

            if (diff < 0) {

                return `

            <span style="
                color:red;
                font-weight:600;
            ">

                ⚠️ Telat
                ${Math.abs(diff)} hari

            </span>
        `;
            }

            /*
            |--------------------------------------------------------------------------
            | WARNING <= 7 HARI
            |--------------------------------------------------------------------------
            */

            if (diff <= 7) {

                return `

            <span style="
                color:red;
                font-weight:600;
            ">

                ⏳ ${diff} hari lagi

            </span>
        `;
            }

            /*
            |--------------------------------------------------------------------------
            | NORMAL
            |--------------------------------------------------------------------------
            */

            return `

        <span style="
            color:#6b7280;
        ">

            ⏳ ${diff} hari lagi

        </span>
    `;
        }
        // delete
        let holdTimer = null;

        let holdInterval = null;
        $(document).on(
            'click',
            '.spk-card',
            function(e) {

                if (
                    $(e.target)
                    .closest('button')
                    .length
                ) {
                    return;
                }

                $('.spk-card')
                    .removeClass(
                        'selected-spk'
                    );

                $(this)
                    .addClass(
                        'selected-spk'
                    );

            }
        );
        $(document).on('pointerdown', '.spk-card', function(e) {

            /*
            |--------------------------------------------------------------------------
            | JANGAN TRIGGER SAAT KLIK BUTTON
            |--------------------------------------------------------------------------
            */

            if (
                $(e.target).closest('button').length
            ) {
                return;
            }

            let card =
                $(this);

            let spkId =
                card.data('id');

            let spkNo =
                card.data('no');

            let progress =
                card.find('.hold-progress');

            let width = 0;

            /*
            |--------------------------------------------------------------------------
            | RESET
            |--------------------------------------------------------------------------
            */

            progress.css({

                width: '0%',

                opacity: 1

            });

            /*
            |--------------------------------------------------------------------------
            | ANIMATION
            |--------------------------------------------------------------------------
            */

            holdInterval = setInterval(function() {

                width += 2;

                progress.css(
                    'width',
                    width + '%'
                );

            }, 100);

            /*
            |--------------------------------------------------------------------------
            | HOLD 5 DETIK
            |--------------------------------------------------------------------------
            */

            holdTimer = setTimeout(function() {

                clearInterval(
                    holdInterval
                );

                progress.css(
                    'opacity',
                    0
                );

                Swal.fire({

                    title: 'Yakin hapus SPK?',
                    html: `
                SPK :
                <br>
                <b>${spkNo}</b>
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya Delete',
                    cancelButtonText: 'Cancel'

                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({

                            url: `/spk/delete/${spkId}`,

                            type: 'DELETE',

                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },

                            success: function(res) {

                                Swal.fire({

                                    icon: 'success',
                                    title: 'Deleted',
                                    text: res.message

                                });

                                loadSpkTable();

                                $('#spkDetailBox')
                                    .hide();

                            },

                            error: function() {

                                Swal.fire({

                                    icon: 'error',
                                    title: 'Oops',
                                    text: 'Gagal delete SPK'

                                });

                            }

                        });

                    }

                });

            }, 5000);

        });

        /*
        |--------------------------------------------------------------------------
        | CANCEL HOLD
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'pointerup pointerleave pointercancel',
            '.spk-card',
            function() {

                clearTimeout(
                    holdTimer
                );

                clearInterval(
                    holdInterval
                );

                $(this)
                    .find('.hold-progress')
                    .css({

                        width: '0%',

                        opacity: 0

                    });

            }
        );
        $(document).on('click', '.btn-delete-spk', function() {

            let spkId = $(this).data('id');
            let spkNo = $(this).data('no');

            Swal.fire({

                title: 'Hapus SPK?',
                html: `
            SPK <b>${spkNo}</b><br>
            akan dihapus.
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/spk/delete/${spkId}`,

                    type: 'DELETE',

                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(res) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        });

                        loadSpkTable();

                        $('#spkDetailBox').hide();

                    },

                    error: function() {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'SPK tidak dapat dihapus.'
                        });

                    }

                });

            });

        });
        /*
        |--------------------------------------------------------------------------
        | EXPAND / COLLAPSE DETAIL
        |--------------------------------------------------------------------------
        */

        let isExpanded = false;

        $(document).on(
            'click',
            '#btnExpandDetail',
            function() {

                isExpanded = !isExpanded;

                if (isExpanded) {

                    $('#sidebarColumn')
                        .hide();

                    $('#detailColumn')
                        .removeClass(
                            'col-lg-8 col-md-7'
                        )
                        .addClass(
                            'col-lg-12 col-md-12'
                        );

                    $(this)
                        .html(`
                    <i class="fa fa-compress"></i>
                `)
                        .attr(
                            'title',
                            'Kembalikan'
                        );

                } else {

                    $('#sidebarColumn')
                        .show();

                    $('#detailColumn')
                        .removeClass(
                            'col-lg-12 col-md-12'
                        )
                        .addClass(
                            'col-lg-8 col-md-7'
                        );

                    $(this)
                        .html(`
                    <i class="fa fa-expand"></i>
                `)
                        .attr(
                            'title',
                            'Perbesar'
                        );

                }

            });
    </script>
    <style>
/* =========================================================
   ALL SPK — PREMIUM UI / UX V2
   Styling only. Blade + JavaScript preserved.
   ========================================================= */

:root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --line:#e6ebf2;
    --line2:#eef2f6;
    --text:#172033;
    --muted:#718096;
    --primary:#3158c9;
    --primary2:#2447ad;
    --primary-soft:#eef3ff;
    --success:#159957;
    --warning:#d98b18;
    --danger:#d64545;
    --shadow:0 10px 30px rgba(30,45,70,.055);
    --shadow-sm:0 3px 12px rgba(30,45,70,.045);
    --radius:12px;
}

/* PAGE */
body{
    background:var(--bg);
    color:var(--text);
    font-family:Arial,sans-serif;
    font-size:12px;
}
.padding{padding:16px}
.box{background:transparent!important;border:0!important;box-shadow:none!important}

/* TOP HEADER */
.box>.box-header{
    position:relative;
    background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%);
    border:1px solid var(--line);
    border-radius:var(--radius);
    padding:14px 16px;
    margin-bottom:12px;
    box-shadow:var(--shadow);
}
.box>.box-header:before{
    content:"";
    position:absolute;
    left:0;top:12px;bottom:12px;
    width:3px;
    border-radius:0 4px 4px 0;
    background:var(--primary);
}
.box-header h2{
    margin:0;
    color:var(--text);
    font-size:17px;
    font-weight:750;
    letter-spacing:-.25px;
}
.box-header small{
    color:var(--muted);
    font-size:10px;
}

/* FILTER */
#spkTypeFilter{
    height:36px!important;
    min-width:170px;
    border:1px solid #dbe2eb!important;
    border-radius:8px!important;
    background:#fff!important;
    color:#344054;
    font-size:11px;
    box-shadow:none!important;
}
#spkTypeFilter:hover{border-color:#c5d0df!important}
#spkTypeFilter:focus{
    border-color:#91aef0!important;
    box-shadow:0 0 0 3px rgba(49,88,201,.08)!important;
}

/* MAIN PANELS */
.spk-sidebar,.detail-card,.table-box{
    background:var(--card);
    border:1px solid var(--line);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
}

/* SIDEBAR */
.spk-sidebar{overflow:hidden}
.sidebar-toolbar{
    padding:11px;
    background:#fff;
    border-bottom:1px solid var(--line2);
}
.sidebar-toolbar input{
    height:36px!important;
    border:1px solid #dce3ec!important;
    border-radius:8px!important;
    padding:0 11px!important;
    background:#fbfcfe!important;
    color:var(--text);
    font-size:11px;
    box-shadow:none!important;
}
.sidebar-toolbar input:focus{
    background:#fff!important;
    border-color:#91aef0!important;
    box-shadow:0 0 0 3px rgba(49,88,201,.07)!important;
}
.sidebar-toolbar input::placeholder{color:#a0aaba}
.sidebar-table{
    max-height:70vh;
    overflow:auto;
}

/* LEFT TABLE */
.table-spk{
    width:100%;
    margin:0!important;
    border-collapse:separate;
    border-spacing:0;
}
.table-spk thead th{
    position:sticky;
    top:0;
    z-index:20;
    background:#f8fafc!important;
    color:#7a8798!important;
    border:0!important;
    border-bottom:1px solid var(--line)!important;
    padding:8px 8px!important;
    font-size:8px!important;
    font-weight:750!important;
    text-transform:uppercase;
    letter-spacing:.5px;
    white-space:nowrap;
}
.table-spk tbody td{
    padding:8px!important;
    border:0!important;
    border-bottom:1px solid #f0f3f7!important;
    color:#3b4656;
    font-size:10px;
    vertical-align:middle!important;
}
.table-spk tbody tr{
    position:relative;
    cursor:pointer;
    transition:background .12s ease;
}
.table-spk tbody tr:hover{background:#fafcff!important}
.table-spk tbody tr.selected-row{background:var(--primary-soft)!important}
.table-spk tbody tr.selected-row td{color:#2851b9;border-bottom-color:#dfe7fa!important}
.table-spk tbody tr.selected-row td:first-child{
    box-shadow:inset 3px 0 0 var(--primary);
}
.table-spk tbody td:first-child{
    width:34px;
    text-align:center;
    color:#9aa6b5;
    font-size:9px;
    font-weight:700;
}
.buyer-name{
    display:block;
    max-width:145px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    color:#263244;
    font-size:10px;
    font-weight:700;
}
.selected-row .buyer-name{color:#2851b9}
.po-number{
    display:inline-flex;
    align-items:center;
    max-width:105px;
    padding:3px 6px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    background:#f3f5f8;
    color:#667085;
    border:1px solid #e8ecf1;
    border-radius:5px;
    font-size:8px;
    font-weight:750;
}
.selected-row .po-number{
    background:#e1eaff;
    color:#2851b9;
    border-color:#cbd9fb;
}
.btn-view-spk{
    padding:3px 7px!important;
    border:1px solid #cbd8f6!important;
    border-radius:6px!important;
    background:#fff!important;
    color:var(--primary)!important;
    font-size:8px!important;
    font-weight:700!important;
    transition:.12s ease;
}
.btn-view-spk:hover{
    background:var(--primary)!important;
    color:#fff!important;
    border-color:var(--primary)!important;
}
.table-spk .btn-link{
    color:#a0aaba;
    padding:3px 5px;
}
.table-spk .btn-link:hover{
    color:#42526a;
    background:#f3f5f8;
}

/* DETAIL ACTIONS */
.detail-topbar{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:6px;
    margin-bottom:9px;
}
.detail-topbar .btn{
    height:34px;
    border-radius:7px!important;
    font-size:10px;
    font-weight:700;
}
#btnCreateSpk{
    padding:0 11px;
    background:var(--primary);
    border-color:var(--primary);
    box-shadow:0 3px 8px rgba(49,88,201,.12);
}
#btnCreateSpk:hover{background:var(--primary2);border-color:var(--primary2)}
#btnExpandDetail{
    width:34px;
    padding:0!important;
}

/* DETAIL HEADER */
.detail-card{
    padding:13px;
    margin-bottom:10px;
}
.detail-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
    margin-bottom:8px;
}
.detail-header h5,.detail-header h2{
    margin:0;
    color:var(--text);
    font-size:14px;
    font-weight:750;
}
.detail-po{
    margin-top:3px;
    color:#8290a3;
    font-size:10px;
}

/* SUMMARY */
.summary-row{margin:7px -3px 0!important}
.summary-row>div{padding:0 3px!important}
.summary-card{
    display:flex;
    align-items:center;
    gap:8px;
    min-height:55px!important;
    height:55px!important;
    padding:7px 9px!important;
    background:#fff;
    border:1px solid var(--line2);
    border-radius:9px!important;
    box-shadow:var(--shadow-sm)!important;
}
.summary-icon{
    width:32px!important;
    height:32px!important;
    min-width:32px!important;
    min-height:32px!important;
    border-radius:8px!important;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:12px!important;
}
.summary-icon.blue{background:#3158c9}
.summary-icon.green{background:#159957}
.summary-icon.purple{background:#7452c8}
.summary-icon.orange{background:#d98218}
.summary-card small{
    display:block;
    margin:0 0 2px!important;
    color:#8490a0;
    font-size:8px!important;
    line-height:1.1!important;
    white-space:nowrap;
}
.summary-card h3{
    margin:0!important;
    color:#253044;
    font-size:16px!important;
    font-weight:750;
    line-height:1!important;
}

/* DETAIL TABLE */
.table-box{overflow:hidden}
.table-box .table-responsive{
    max-height:67vh;
    overflow:auto;
}
#spk-detail-body{background:#fff}
#spk-detail-body>tr>td{padding:0!important;border:0!important}
#spk-detail-body table{
    width:100%;
    margin:0!important;
    border-collapse:separate;
    border-spacing:0;
}
#spk-detail-body table thead th{
    position:sticky;
    top:0;
    z-index:15;
    background:#f7f9fc!important;
    color:#7a8798!important;
    border:0!important;
    border-bottom:1px solid var(--line)!important;
    padding:8px 7px!important;
    font-size:8px!important;
    font-weight:750!important;
    text-transform:uppercase;
    letter-spacing:.45px;
    white-space:nowrap;
}
#spk-detail-body table tbody td{
    padding:8px 7px!important;
    border:0!important;
    border-bottom:1px solid #f0f3f7!important;
    color:#455164;
    font-size:9px;
    vertical-align:top;
}
#spk-detail-body table tbody tr:hover{background:#fbfcfe}

/* ARTICLE */
.article-box{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:3px;
}
.article-image,.article-no-image{
    width:54px!important;
    height:54px!important;
    border-radius:7px!important;
    object-fit:cover;
    border:1px solid #e5e9ef;
    background:#f8fafc;
}
.article-no-image{
    display:flex;
    align-items:center;
    justify-content:center;
    color:#a0aaba;
    font-size:7px;
}
.article-code{
    margin-top:1px!important;
    color:#697789;
    font-size:8px!important;
    font-weight:700;
    text-align:center;
    word-break:break-word;
}
#spk-detail-body td:nth-child(3){
    color:#4d596b;
    line-height:1.35;
}
#spk-detail-body td:nth-child(4),
#spk-detail-body td:nth-child(5){
    text-align:center;
    white-space:nowrap;
}
#spk-detail-body td:nth-child(4){color:#263244;font-weight:750}
#spk-detail-body td:nth-child(5){
    color:#738095;
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace;
}

/* SPK CARDS */
.spk-card{
    position:relative;
    margin:1px 0;
    padding:7px 8px;
    background:#fff;
    border:1px solid #e4e9f0;
    border-radius:7px;
    box-shadow:0 1px 3px rgba(20,35,55,.025);
    transition:.12s ease;
}
.spk-card:hover{
    border-color:#c8d6f6;
    box-shadow:0 4px 10px rgba(49,88,201,.07);
}
.spk-card b{
    color:#263244;
    font-size:8px;
    font-weight:750;
}
.spk-card small{
    color:#7b8798;
    font-size:7px;
    line-height:1.3;
}
.spk-btn-group{
    display:flex;
    align-items:center;
    gap:3px;
    margin-top:4px;
}
.spk-btn-group .btn{
    padding:2px 5px!important;
    border-radius:4px!important;
    font-size:7px!important;
    line-height:1.4;
}
.spk-btn-group .btn-warning{
    color:#95600d;
    background:#fff9ed;
    border-color:#f5ddb2;
}
.spk-btn-group .btn-danger{
    color:#b83333;
    background:#fff5f5;
    border-color:#f2caca;
}
.hold-progress{
    position:absolute;
    left:0;
    bottom:0;
    height:2px;
    width:0;
    background:#df4545;
    border-radius:0 0 7px 7px;
    opacity:0;
}
.spk-card.selected-spk{
    border-color:#9db4ed;
    background:#f8faff;
    box-shadow:0 0 0 2px rgba(49,88,201,.06);
}

/* GLOBAL BUTTONS */
.btn{border-radius:7px}
.btn-primary{background:var(--primary);border-color:var(--primary)}
.btn-primary:hover{background:var(--primary2);border-color:var(--primary2)}
.btn-outline-primary{
    color:var(--primary);
    border-color:#cbd8f6;
    background:#fff;
}
.btn-outline-primary:hover{
    background:var(--primary);
    border-color:var(--primary);
    color:#fff;
}

/* SCROLLBAR */
.sidebar-table::-webkit-scrollbar,
.table-box .table-responsive::-webkit-scrollbar{
    width:6px;height:6px;
}
.sidebar-table::-webkit-scrollbar-thumb,
.table-box .table-responsive::-webkit-scrollbar-thumb{
    background:#cbd3df;
    border-radius:20px;
}
.sidebar-table::-webkit-scrollbar-track,
.table-box .table-responsive::-webkit-scrollbar-track{background:transparent}

/* EMPTY */
#po-table-body td[colspan],
#spk-detail-body td[colspan]{
    color:#98a3b2;
    font-size:10px;
}

/* RESPONSIVE */
@media(max-width:991px){
    .padding{padding:11px}
    .sidebar-table{max-height:360px}
    .table-box .table-responsive{max-height:60vh}
    .detail-topbar{margin-top:8px}
}
@media(max-width:767px){
    .box>.box-header{padding:12px}
    .box-header .row>div{text-align:left!important}
    #spkTypeFilter{width:100%!important;margin-top:9px}
    .detail-card{padding:10px}
    .summary-card{min-height:52px!important;height:52px!important}
    .summary-icon{
        width:29px!important;height:29px!important;
        min-width:29px!important;min-height:29px!important;
        font-size:11px!important
    }
    .summary-card h3{font-size:15px!important}
    .summary-card small{font-size:7px!important}
}
</style>
@endpush
