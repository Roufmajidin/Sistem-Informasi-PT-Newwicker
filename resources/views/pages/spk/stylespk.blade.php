<style>

body{
    background:#f5f6fa;
    font-family:'Poppins',sans-serif;
}

/* =========================================
REMOVE BOOTSTRAP BORDER
========================================= */

.table,
.table-bordered,
.table-bordered td,
.table-bordered th{
    border:none !important;
    box-shadow:none !important;
}

/* =========================================
BOX
========================================= */

.box,
.box-header,
.box-body{
    border:none !important;
    box-shadow:none !important;
    background:#fff;
}

/* =========================================
WRAPPER
========================================= */

.spk-wrapper{
    overflow-x:auto;
    padding:10px;
}

/* =========================================
TABLE
========================================= */

.spk-table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    font-size:12px;
}

/* =========================================
DEFAULT TD TH
========================================= */

.spk-table td,
.spk-table th{
    padding:6px;
    vertical-align:middle;
    background:#fff;
}

/* =========================================
HEADER ITEM
========================================= */
th{
    background:#2f437f !important;
    color: #ffffff;
}
.spk-item-header th{
    background:#2f437f !important;
    color:#fff !important;
    border:1px solid #44598d !important;
    font-weight:600;
    text-align:center;
}

/* =========================================
DYNAMIC HEADER
========================================= */

.spk-dynamic-header{
    background:#2f437f !important;
    color:#fff !important;
    font-weight:bold;
    text-align:center;
    white-space:nowrap;
}

/* =========================================
INFO SPK
========================================= */

.spk-info-row td{
    border:none !important;
    background:#fff;
    padding:6px 10px;
}

/* =========================================
ITEM ROW
========================================= */

.spk-rowa td{
    border:1px solid #dcdfe6 !important;
    background:#fff;
}

/* =========================================
EXTRA ROW
========================================= */

.extra-row td{
    border:1px solid #ececec !important;
    background:#fafafa;
}

/* =========================================
ROWSPAN
========================================= */

.spk-rowa td[rowspan]{
    vertical-align:top !important;
}

/* =========================================
EDITABLE
========================================= */

.editable{
    background:#fff8dc;
    min-height:28px;
    padding:4px;
    outline:none;
    border:none !important;
    box-shadow:none !important;
    border-radius:2px;
    cursor:text;
}

.editable:empty:before{
    content:attr(data-placeholder);
    color:#aaa;
}

/* =========================================
IMAGE
========================================= */

.gambar-cell{
    width:150px;
    vertical-align:top !important;
}

.image-box{
    min-height:90px;
    border:1px dashed #ddd !important;
    padding:4px;
    display:flex;
    flex-wrap:wrap;
    gap:4px;
    align-items:center;
    justify-content:center;
    background:#fff;
}

.preview-img{
    max-width:120px;
    max-height:90px;
    object-fit:contain;
    border-radius:4px;
    border:1px solid #ddd;
}

/* =========================================
COLUMN WIDTH
========================================= */

.kode-item{
    width:90px;
    text-align:center;
}

.nama{
    width:220px;
    font-weight:500;
}

.custom-column{
    min-width:120px;
}

.material{
    width:150px;
    white-space:normal;
    word-break:break-word;
    line-height:1.5;
}

.p,
.l,
.t{
    width:55px;
    text-align:center;
}

.pcs,
.set{
    width:60px;
    text-align:center;
}

.harga,
.total{
    width:100px;
    text-align:right;
}

.catatan{
    min-width:160px;
}

/* =========================================
NOTE
========================================= */

.note-box{
    min-height:60px;
    background:#fffef4;
}

/* =========================================
BUTTON
========================================= */

.btn-add-extra,
.btn-delete-extra{
    width:28px;
    height:28px;
    border:none !important;
    border-radius:5px;
    cursor:pointer;
    transition:.2s;
    font-size:12px;
}

/* add */

.btn-add-extra{
    background:#2563eb;
    color:#fff;
}

.btn-add-extra:hover{
    background:#1d4ed8;
}

/* delete */

.btn-delete-extra{
    background:#dc2626;
    color:#fff;
}

.btn-delete-extra:hover{
    background:#b91c1c;
}

/* =========================================
INPUT FILE
========================================= */

input[type=file]{
    width:100%;
    font-size:11px;
    margin-top:4px;
}

/* =========================================
SUGGEST BOX
========================================= */

.suggest-box{
    position:absolute;
    top:100%;
    left:0;
    right:0;
    background:#fff;
    border:1px solid #ddd;
    z-index:999;
    max-height:180px;
    overflow-y:auto;
    display:none;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.suggest-item{
    padding:8px;
    cursor:pointer;
    transition:.2s;
}

.suggest-item:hover{
    background:#f5f5f5;
}

/* =========================================
PAYMENT TABLE
========================================= */

.payment-table{
    width:100%;
    border-collapse:collapse;
    font-size:12px;
}

.payment-table th{
    background:#f3f4f6;
    border:1px solid #e5e7eb;
    padding:6px;
    text-align:center;
}

.payment-table td{
    border:1px solid #e5e7eb;
    padding:6px;
    background:#fff;
}

.payment-row td{
    border:1px solid #e5e7eb !important;
}

/* =========================================
PAYMENT SUMMARY
========================================= */

#paymentSummary{
    border:none !important;
    font-size:13px;
    line-height:1.8;
}

#paymentSummary *{
    border:none !important;
}

/* =========================================
SCROLLBAR
========================================= */

.spk-wrapper::-webkit-scrollbar{
    height:8px;
}

.spk-wrapper::-webkit-scrollbar-thumb{
    background:#bbb;
    border-radius:10px;
}

/* =========================================
REMOVE DUMMY
========================================= */


/* =========================================
PRINT
========================================= */

@media print{

    body{
        background:#fff;
    }

    .spk-rowa td,
    .extra-row td{
        border:1px solid #000 !important;
    }

    .editable{
        background:none !important;
    }

    input[type=file],
    .btn-add-extra,
    .btn-delete-extra{
        display:none !important;
    }
}

</style>
