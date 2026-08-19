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
    width:6px;
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

/* .note-box{
    min-height:60px;
    background:#fffef4;
} */

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


<style>
/* =========================================================
   SPK - FIT 100% / CLEAN DESKTOP UI
   Override aman: hanya CSS, tanpa mengubah struktur/JS.
   ========================================================= */

/* ---------- PAGE ---------- */
html,
body {
    overflow-x: hidden !important;
}

body {
    margin: 0 !important;
}

/* ---------- MAIN BOX ---------- */
.box,
.box-header,
.box-body {
    max-width: 100% !important;
}

.box-header {
    min-height: 46px !important;
    padding: 6px 10px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.box-header h3 {
    margin: 0 !important;
    font-size: 13px !important;
    line-height: 1 !important;
    white-space: nowrap !important;
}

/* toolbar kanan: select + riwayat + preview + salin tetap satu baris */
.box-header > div:last-child {
    margin-left: auto !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 5px !important;
    width: auto !important;
    min-width: 0 !important;
}

.box-header > div:last-child label {
    display: none !important;
}

.box-header > div:last-child select {
    width: 100px !important;
    min-width: 100px !important;
    height: 28px !important;
    padding: 3px 6px !important;
    font-size: 9px !important;
    margin: 0 !important;
}

.box-header > div:last-child button {
    height: 28px !important;
    min-width: 70px !important;
    padding: 3px 7px !important;
    margin: 0 !important;
    font-size: 9px !important;
    white-space: nowrap !important;
}

/* ---------- WORKSPACE ---------- */
.spk-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
    padding: 6px !important;
}

/* Jangan pakai zoom/transform karena menggeser posisi layout. */
#printArea {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    transform: none !important;
    zoom: 1 !important;
}

/* ---------- MAIN SPK TABLE ---------- */
.spk-table {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    table-layout: auto !important;
    border-collapse: collapse !important;
    font-size: 10px !important;
}

/* Semua cell dibuat lebih ringkas, tetapi tidak dipaksa fixed. */
.spk-table td,
.spk-table th {
    padding: 4px 5px !important;
}

/* Header item */
.spk-item-header th,
.spk-dynamic-header {
    height: 28px !important;
    padding: 4px 4px !important;
    font-size: 8px !important;
    line-height: 1.1 !important;
}

/* ---------- COLUMN WIDTH ---------- */

/* checkbox */
.spk-table th:first-child,
.spk-table td:first-child {
    width: 28px !important;
    min-width: 28px !important;
}

/* article */
.kode-item,
.article-cell {
    width: 68px !important;
    min-width: 68px !important;
}

/* image */
.gambar-cell {
    width: 68px !important;
    min-width: 68px !important;
}

.image-box {
    min-height: 48px !important;
    padding: 3px !important;
    gap: 3px !important;
}

.preview-img {
    max-width: 52px !important;
    max-height: 42px !important;
}

/* name */
.nama {
    width: 125px !important;
    min-width: 105px !important;
    max-width: 145px !important;
    line-height: 1.2 !important;
}

/* dynamic column */
.custom-column,
.spk-dynamic-header {
    min-width: 48px !important;
    width: 55px !important;
}

/* P L T */
.p,
.l,
.t {
    width: 38px !important;
    min-width: 34px !important;
    text-align: center !important;
}

/* material */
.material {
    width: 95px !important;
    min-width: 80px !important;
    max-width: 110px !important;
    line-height: 1.2 !important;
}

/* pcs set */
.pcs,
.set {
    width: 45px !important;
    min-width: 42px !important;
    text-align: center !important;
}

/* harga total */
.harga,
.total {
    width: 72px !important;
    min-width: 65px !important;
    text-align: right !important;
    white-space: nowrap !important;
}

/* catatan */
/* .catatan,
.note-box {
    min-width: px !important;
    width: 60px !important;
} */

/* action */
.btn-add-extra,
.btn-delete-extra {
    width: 24px !important;
    height: 24px !important;
    font-size: 10px !important;
    padding: 0 !important;
}

/* ---------- EDITABLE ---------- */
.editable {
    min-height: 24px !important;
    padding: 3px 4px !important;
    font-size: 9px !important;
}

/* ---------- SEARCH ---------- */
#itemSearch {
    width: 100% !important;
    height: 27px !important;
    min-height: 27px !important;
    padding: 4px 7px !important;
    font-size: 9px !important;
}

/* ---------- DATE ---------- */
.tgl-terima,
.tgl-selesai {
    min-width: 145px !important;
    width: 145px !important;
    padding: 2px 5px !important;
}

.spk-date-wrap {
    min-width: 130px !important;
    height: 22px !important;
}

.spk-date-display {
    height: 22px !important;
    line-height: 22px !important;
    font-size: 9px !important;
}

/* ---------- FILE INPUT ---------- */
input[type=file] {
    font-size: 8px !important;
    margin-top: 2px !important;
}

/* ---------- BOTTOM TABLES ---------- */
.payment-table {
    font-size: 9px !important;
}

.payment-table th,
.payment-table td {
    padding: 4px !important;
}

#paymentSummary {
    font-size: 10px !important;
    line-height: 1.45 !important;
}

/* ---------- LAPTOP 1280-1400 ----------
   Lebih kecil karena sidebar biasanya mengambil ruang.
*/
@media (min-width: 1000px) and (max-width: 1400px) {

    .spk-wrapper {
        padding: 4px !important;
    }

    .spk-table {
        font-size: 9px !important;
    }

    .spk-table td,
    .spk-table th {
        padding: 3px 4px !important;
    }

    .nama {
        width: 110px !important;
        min-width: 95px !important;
        max-width: 125px !important;
    }

    .material {
        width: 85px !important;
        min-width: 72px !important;
        max-width: 95px !important;
    }

    .harga,
    .total {
        width: 65px !important;
        min-width: 58px !important;
    }

    /* .catatan,
    .note-box {
        width: 68px !important;
        min-width: 60px !important;
    } */

    .custom-column,
    .spk-dynamic-header {
        width: 45px !important;
        min-width: 42px !important;
    }

    .p,
    .l,
    .t {
        width: 34px !important;
        min-width: 30px !important;
    }

    .pcs,
    .set {
        width: 40px !important;
        min-width: 36px !important;
    }

    .gambar-cell {
        width: 62px !important;
        min-width: 62px !important;
    }

    .kode-item {
        width: 62px !important;
        min-width: 62px !important;
    }
}

/* ---------- VERY WIDE SCREEN ---------- */
@media (min-width: 1600px) {
    .spk-wrapper {
        padding: 8px 10px !important;
    }

    .spk-table {
        font-size: 11px !important;
    }
}

/* ---------- PRINT: jangan ikut compact desktop override ---------- */
@media print {
    html,
    body {
        overflow: visible !important;
    }

    .spk-wrapper {
        overflow: visible !important;
        padding: 0 !important;
    }

    #printArea,
    .spk-table {
        width: 100% !important;
        max-width: none !important;
        transform: none !important;
        zoom: 1 !important;
    }
}
</style>

