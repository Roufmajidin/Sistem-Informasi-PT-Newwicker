@extends('master.master')
@section('title', "All SPK")
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

                    <select
                        id="spkTypeFilter"
                        class="form-control"
                        style="width:220px;display:inline-block"
                        {{ $isRndSpk ? 'disabled' : '' }}
                    >
                        <option value="ALL"
                            {{ !$isRndSpk ? 'selected' : '' }}>
                            Semua SPK
                        </option>

                        <option value="NW">
                            SPK Produksi (NW)
                        </option>

                        <option value="NWS"
                            {{ $isRndSpk ? 'selected' : '' }}>
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

                            <input
                                type="text"
                                id="searchPo"
                                class="form-control"
                                placeholder="Cari SPK, Buyer atau PO..."
                            >

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

                                        <td colspan="4"
                                            class="text-center text-muted">

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

                <a
    href="javascript:void(0)"
    id="btnCreateSpk"
    class="btn btn-primary">

    <i class="fa fa-plus"></i>

    Buat SPK Baru

</a>
                <button
                    id="btnExpandDetail"
                    class="btn btn-default">

                    <i class="fa fa-expand"></i>

                </button>

            </div>

            <div class="clearfix"></div>

        </div>

        {{-- CARD --}}
        <div class="detail-card">

            <div class="detail-header">

                <div>

                    <h2>

                        Detail SPK

                    </h2>

                    <div
                        id="detailPoTitle"
                        class="detail-po">

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

</div>        @endsection
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
            // DOWNLOAD SPK
            // ===============================
        $('#searchPo').on('input', function () {

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
data = res.filter(function(po){

    let noPo = (po.data_po?.no_po || '');
    let buyer = (po.data_po?.company || '');

    let prefix = noPo.split(' ')[0];

    let matchType =
        currentType === 'ALL'
        ||
        prefix === currentType;

    let matchKeyword =
        currentKeyword === ''
        ||
        noPo.toLowerCase().includes(currentKeyword)
        ||
        buyer.toLowerCase().includes(currentKeyword);

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

        scrollTop:
            $('#spkDetailBox').offset().top - 20

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
            $('#spkTypeFilter').on('change', function () {

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
                        _token:
                        $('meta[name="csrf-token"]').attr('content')
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
$(document).on('click', '.btn-delete-spk', function () {

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

            success: function (res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message
                });

                loadSpkTable();

                $('#spkDetailBox').hide();

            },

            error: function () {

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
    function(){

        isExpanded = !isExpanded;

        if(isExpanded){

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

        }else{

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
         body{
    background:#f4f7fb;
}

.padding{
    padding:20px;
}

/* ===========================================
   LEFT SIDEBAR
=========================================== */

.spk-sidebar{

    background:#fff;

    border-radius:18px;

    overflow:hidden;

    border:1px solid #edf2f7;

    box-shadow:
        0 8px 25px rgba(15,23,42,.06);

}

.sidebar-toolbar{

    padding:18px;

    border-bottom:1px solid #edf2f7;

    background:#fafcff;

}

.sidebar-toolbar input{

    border-radius:10px;

    height:42px;

}

.sidebar-table{

    max-height:70vh;

    overflow-y:auto;

}

/* ===========================================
   TABLE LEFT
=========================================== */

.table-spk{

    margin-bottom:0;

}

.table-spk thead th{

    background:#fff;

    border-top:none;

    border-bottom:1px solid #edf2f7;

    color:#64748b;

    font-size:12px;

    font-weight:700;

    text-transform:uppercase;

    letter-spacing:.4px;

}

.table-spk tbody td{

    vertical-align:middle;

    border-color:#f1f5f9;

    padding:14px 10px;

}

.table-spk tbody tr{

    transition:.25s;

    cursor:pointer;

}

.table-spk tbody tr:hover{

    background:#f8fbff;

}

.selected-row{

    background:#eef5ff !important;

}

.selected-row td{

    border-color:#dbeafe !important;

}

/* ===========================================
   DETAIL
=========================================== */

.detail-topbar{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:20px;

}

.btn-back{

    color:#2563eb;

    font-weight:600;

}

.btn-back:hover{

    text-decoration:none;

}

.detail-card{

    background:#fff;

    border-radius:18px;

    padding:25px;

    box-shadow:
        0 8px 30px rgba(15,23,42,.06);

    margin-bottom:20px;

}

.detail-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:5px;

}

.detail-header h2{

    margin:0;

    font-size:18px;

    font-weight:700;

}

.detail-po{

    color:#64748b;

    margin-top:5px;

}

/* ===========================================
   SUMMARY
=========================================== */

.summary-row{

    margin-top:15px;
    margin-bottom:18px;

}

.summary-card{

    display:flex;
    align-items:center;
    gap:12px;

    background:#fff;

    border:1px solid #edf2f7;

    border-radius:12px;

    padding:12px 16px;

    min-height:72px;

    transition:.25s;

    box-shadow:0 3px 12px rgba(0,0,0,.04);

}

.summary-card:hover{

    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,.08);

}

.summary-icon{

    width:42px;
    height:42px;

    border-radius:10px;

    display:flex;
    justify-content:center;
    align-items:center;

    color:#fff;

    font-size:18px;

    flex-shrink:0;

}

.summary-card small{

    display:block;

    font-size:11px;

    color:#64748b;

    margin-bottom:2px;

}

.summary-card h3{

    margin:0;

    font-size:28px;

    font-weight:700;

    line-height:1;

}
.summary-card{

    min-height:62px;
    padding:10px 14px;

}

.summary-icon{

    width:36px;
    height:36px;

    font-size:15px;

}

.summary-card h3{

    font-size:22px;

}

.summary-card small{

    font-size:10px;

}

.summary-card:hover{

    transform:translateY(-3px);

    box-shadow:
        0 12px 25px rgba(0,0,0,.06);

}

.summary-card small{

    display:block;

    color:#64748b;

}

.summary-card h3{

    margin:6px 0 0;

    font-size:18px;

    font-weight:700;

}

/* ===========================================
   ICON
=========================================== */

.summary-icon{

    width:58px;

    height:58px;

    border-radius:15px;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    font-size:22px;

    flex-shrink:0;

}

.summary-icon.blue{

    background:#2563eb;

}

.summary-icon.green{

    background:#16a34a;

}

.summary-icon.purple{

    background:#7c3aed;

}

.summary-icon.orange{

    background:#ea580c;

}

/* ===========================================
   TABLE DETAIL
=========================================== */

.table-box{

    border-radius:18px;

    overflow:hidden;

    box-shadow:
        0 8px 25px rgba(15,23,42,.05);

}

.table-box table{

    margin:0;

}

.table-box thead{

    background:#0f172a;

}

.table-box thead th{

    color:#fff;

    border:none;

}

.table-box tbody td{

    vertical-align:top;

}

/* ===========================================
   BUTTON
=========================================== */

.btn{

    border-radius:10px;

}

.btn-outline-primary{

    border:1px solid #2563eb;

    color:#2563eb;

    background:#fff;

}

.btn-outline-primary:hover{

    background:#2563eb;

    color:#fff;

}

/* ===========================================
   EXPAND
=========================================== */

#btnExpandDetail{

    width:42px;

    height:42px;

    border-radius:10px;

}
.spk-card{

    position:relative;

    z-index:999;

    cursor:pointer;

}
/* ===========================================
   SCROLL
=========================================== */

.sidebar-table::-webkit-scrollbar{

    width:8px;

}

.sidebar-table::-webkit-scrollbar-thumb{

    background:#cbd5e1;

    border-radius:20px;

}
.article-image{

    width:80px;

    height:80px;

    object-fit:cover;

    border-radius:10px;

    border:1px solid #eee;

    background:#fff;

    display:block;

    margin:auto;

}

.article-code{

    margin-top:8px;

    font-size:12px;

    font-weight:600;

    color:#64748b;

}
        </style>
        @endpush
