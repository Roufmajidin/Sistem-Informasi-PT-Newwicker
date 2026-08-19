<style>
    /* =========================================================
   SPK INDEX — CONSOLIDATED CSS
   ========================================================= */

/* =========================================================
   SPK FULL LAYOUT CSS
   ========================================================= */

/* =========================================================
   GLOBAL
   ========================================================= */

html,
body {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0;

    overflow-x: hidden;

    font-family:
        "Poppins",
        "Segoe UI",
        Arial,
        sans-serif;

    color: #1f2937;
    background: #ffffff;
}

* {
    box-sizing: border-box;
}


/* =========================================================
   MAIN CONTAINER
   ========================================================= */

.box {
    width: 100% !important;
    max-width: 100% !important;

    margin: 0 !important;
    padding: 0 !important;

    border: 0 !important;
    box-shadow: none !important;
}

.box-body.spk-wrapper {
    width: 100% !important;
    max-width: 100% !important;

    margin: 0 !important;

    padding: 10px 12px 20px !important;

    overflow-x: auto;
    overflow-y: visible;

    background: #ffffff;
}


/* =========================================================
   HEADER
   ========================================================= */

.box-header {
    width: 100% !important;

    padding: 10px 14px !important;

    background: #ffffff;

    border-bottom: 1px solid #e5e7eb;
}

.box-header h3 {
    margin: 0;

    font-size: 18px;
    line-height: 1.4;

    font-weight: 700;

    color: #111827;
}


/* =========================================================
   SPK INFORMATION
   ========================================================= */

.spk-info,
.spk-header,
.spk-detail-header {
    width: 100%;

    margin-bottom: 8px;
}

.spk-info table,
.spk-header table,
.spk-detail-header table {
    width: 100%;
    border-collapse: collapse;
}

.spk-info td,
.spk-header td,
.spk-detail-header td {
    padding: 5px 8px;

    vertical-align: middle;

    font-size: 12px;
}

.spk-info label,
.spk-header label,
.spk-detail-header label {
    font-weight: 700;
    color: #111827;
}


/* =========================================================
   SPK TABLE WRAPPER
   ========================================================= */

.spk-table-wrapper {
    width: 100% !important;
    max-width: 100% !important;

    overflow-x: auto;
    overflow-y: visible;

    margin: 0;
    padding: 0;

    border-radius: 4px;

    scrollbar-width: thin;
}


/* =========================================================
   MAIN SPK TABLE
   ========================================================= */

.spk-table {
    width: 100% !important;

    /*
     * Jangan gunakan table-layout: fixed.
     * Kita ingin browser menghormati min-width
     * tiap kolom.
     */
    table-layout: auto;

    border-collapse: separate !important;
    border-spacing: 0;

    background: #ffffff;

    font-size: 12px;

    /*
     * Minimal width supaya kolom tidak dipaksa terlalu kecil.
     */
    min-width: 1250px;
}


/* =========================================================
   TABLE HEADER
   ========================================================= */

.spk-table thead th {
    position: sticky;
    top: 0;

    z-index: 10;

    height: 36px;

    padding: 7px 8px !important;

    border: 1px solid #dbe2ea !important;

    background: #304783 !important;

    color: #ffffff !important;

    font-size: 11px;
    font-weight: 700;

    text-align: center;
    vertical-align: middle;

    white-space: nowrap;
}


/* =========================================================
   TABLE BODY
   ========================================================= */

.spk-table tbody td {
    padding: 6px 8px !important;

    border: 1px solid #e1e5ea !important;

    background: #ffffff;

    color: #1f2937;

    font-size: 12px;

    vertical-align: middle;
}

.spk-table tbody tr:nth-child(even) td {
    background: #fafafa;
}

.spk-table tbody tr:hover td {
    background: #f5f8fc;
}


/* =========================================================
   CHECKBOX
   ========================================================= */

.select-item-cell,
.spk-table th:first-child,
.spk-table td:first-child {
    width: 42px !important;
    min-width: 42px !important;
    max-width: 42px !important;

    text-align: center !important;
}

.spk-table input[type="checkbox"] {
    width: 15px;
    height: 15px;

    cursor: pointer;
}


/* =========================================================
   ARTICLE
   ========================================================= */

.kode-item,
.article-cell {
    width: 95px !important;
    min-width: 95px !important;
    max-width: 95px !important;

    text-align: center !important;

    white-space: nowrap;
}


/* =========================================================
   GAMBAR
   ========================================================= */

.gambar-cell {
    width: 90px !important;
    min-width: 90px !important;
    max-width: 90px !important;

    text-align: center !important;
}

.image-box {
    width: 100%;

    min-height: 58px;

    display: flex;

    flex-wrap: wrap;

    align-items: center;
    justify-content: center;

    gap: 4px;

    overflow: hidden;
}

.preview-img {
    width: auto !important;

    max-width: 70px !important;
    max-height: 58px !important;

    object-fit: contain;

    border-radius: 4px;

    border: 1px solid #e5e7eb;
}


/* =========================================================
   NAMA BARANG
   ========================================================= */

.nama,
.nama-barang {
    width: 155px !important;
    min-width: 155px !important;
    max-width: 155px !important;

    font-weight: 600;

    color: #111827 !important;

    white-space: normal;
    overflow-wrap: break-word;
}


/* =========================================================
   DYNAMIC COLUMN
   ========================================================= */

.dynamic-column,
.hallo,
.extra-column {
    min-width: 90px !important;

    white-space: normal;

    overflow-wrap: break-word;
}


/* =========================================================
   P / L / T
   ========================================================= */

.spk-table .p,
.spk-table .l,
.spk-table .t {
    width: 55px !important;

    min-width: 55px !important;
    max-width: 55px !important;

    padding: 6px 8px !important;

    text-align: center !important;

    vertical-align: middle !important;

    white-space: nowrap;
}


/* =========================================================
   MATERIAL
   ========================================================= */

.spk-table .material {
    width: 150px !important;

    min-width: 150px !important;
    max-width: 150px !important;

    padding: 7px 10px !important;

    text-align: left !important;

    vertical-align: middle !important;

    white-space: pre-line;

    overflow-wrap: break-word;

    line-height: 1.35;
}


/* =========================================================
   PCS
   ========================================================= */

.spk-table .pcs {
    width: 80px !important;

    min-width: 80px !important;
    max-width: 80px !important;

    padding: 7px 12px !important;

    text-align: center !important;

    vertical-align: middle !important;

    white-space: nowrap;
}


/* =========================================================
   SET
   ========================================================= */

.spk-table .set {
    width: 80px !important;

    min-width: 80px !important;
    max-width: 80px !important;

    padding: 7px 12px !important;

    text-align: center !important;

    vertical-align: middle !important;

    white-space: nowrap;
}


/* =========================================================
   HARGA
   ========================================================= */

.spk-table .harga {
    width: 115px !important;

    min-width: 115px !important;
    max-width: 115px !important;

    padding: 7px 10px !important;

    text-align: right !important;

    vertical-align: middle !important;

    white-space: nowrap;

    font-variant-numeric: tabular-nums;
}


/* =========================================================
   TOTAL
   ========================================================= */

.spk-table .total {
    width: 125px !important;

    min-width: 125px !important;
    max-width: 125px !important;

    padding: 7px 10px !important;

    text-align: right !important;

    vertical-align: middle !important;

    white-space: nowrap;

    font-weight: 700;

    font-variant-numeric: tabular-nums;
}


/* =========================================================
   CATATAN
   ========================================================= */


/* =========================================================
   ACTION
   ========================================================= */

.spk-table .action-cell {
    width: 48px !important;

    min-width: 48px !important;
    max-width: 48px !important;

    padding: 5px !important;

    text-align: center !important;
}


/* =========================================================
   EDITABLE CELL
   ========================================================= */

.spk-table td.editable {
    cursor: text;

    user-select: text;

    transition:
        background-color .12s ease,
        box-shadow .12s ease;
}

.spk-table td.editable:hover {
    background: #f7faff !important;
}

.spk-table td.editable:focus {
    outline: 2px solid #2563eb !important;

    outline-offset: -2px;

    background: #eff6ff !important;

    color: #111827;
}


/* =========================================================
   EXCEL SELECTION
   ========================================================= */

.spk-table td.excel-selected {
    background: #dbeafe !important;

    color: #111827 !important;

    box-shadow:
        inset 0 0 0 1px #3b82f6 !important;
}

.spk-table td.excel-selected:focus {
    background: #bfdbfe !important;

    outline: 2px solid #2563eb !important;

    outline-offset: -2px;
}


/* =========================================================
   SELECT
   ========================================================= */

.spk-table select.form-control,
.spk-table select {
    width: 100% !important;

    min-width: 0 !important;

    height: 30px !important;

    padding: 3px 7px !important;

    border: 1px solid #d1d5db !important;

    border-radius: 5px !important;

    background: #ffffff;

    color: #1f2937;

    font-size: 11px !important;
}

.spk-table select:focus {
    border-color: #3b82f6 !important;

    outline: 2px solid #dbeafe;
}


/* =========================================================
   INPUT
   ========================================================= */

.spk-table input[type="text"],
.spk-table input[type="number"],
.spk-table input[type="date"] {
    width: 100%;

    min-width: 0;

    height: 30px;

    padding: 4px 7px;

    border: 1px solid #d1d5db;

    border-radius: 5px;

    background: #ffffff;

    font-size: 11px;
}

.spk-table input:focus {
    border-color: #3b82f6;

    outline: 2px solid #dbeafe;
}


/* =========================================================
   EXTRA ROW
   ========================================================= */

.spk-table tr.extra-row td {
    background: #ffffff;
}

.spk-table tr.extra-row:hover td {
    background: #f8fafc;
}


/* =========================================================
   ADD ROW BUTTON
   ========================================================= */

.btn-add-extra {
    width: 30px !important;
    height: 30px !important;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 0 !important;

    border: 1px solid #cbd5e1 !important;

    border-radius: 6px !important;

    background: #f8fafc !important;

    color: #334155 !important;

    cursor: pointer;

    transition:
        background .15s ease,
        transform .15s ease;
}

.btn-add-extra:hover {
    background: #e2e8f0 !important;

    transform: translateY(-1px);
}


/* =========================================================
   DELETE ROW BUTTON
   ========================================================= */

.btn-delete-extra {
    width: 30px !important;
    height: 30px !important;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 0 !important;

    border: 0 !important;

    border-radius: 6px !important;

    background: #ef4444 !important;

    color: #ffffff !important;

    cursor: pointer;
}

.btn-delete-extra:hover {
    background: #dc2626 !important;
}


/* =========================================================
   PAYMENT SECTION
   ========================================================= */

.payment-wrapper {
    width: 100%;

    margin-top: 18px;

    display: flex;

    gap: 18px;

    align-items: flex-start;
}


/* =========================================================
   PAYMENT TABLE
   ========================================================= */

#paymentBody,
.payment-table {
    width: 100%;
}

.payment-table {
    table-layout: fixed;

    border-collapse: separate;

    border-spacing: 0;

    font-size: 12px;

    background: #ffffff;
}

.payment-table th {
    padding: 7px 8px !important;

    background: #304783 !important;

    color: #ffffff !important;

    border: 1px solid #dbe2ea !important;

    font-size: 11px;

    font-weight: 700;

    text-align: center;

    white-space: nowrap;
}

.payment-table td {
    padding: 6px 8px !important;

    border: 1px solid #e1e5ea !important;

    vertical-align: middle;
}


/* =========================================================
   PAYMENT COLUMN
   ========================================================= */

.payment-req-col {
    width: 45px !important;
}

.payment-amount-col {
    width: 125px !important;
}

.payment-date-col {
    width: 105px !important;
}

.payment-type-col {
    width: 110px !important;
}

.payment-note-col {
    width: 170px !important;
}

.payment-adjustment-col {
    width: 165px !important;
}


/* =========================================================
   PAYMENT AMOUNT
   ========================================================= */

.payment-row .total-amount {
    width: 125px !important;

    min-width: 125px !important;

    text-align: right !important;

    white-space: nowrap;

    font-weight: 700;

    font-variant-numeric: tabular-nums;

    color: #111827 !important;
}


/* =========================================================
   PAYMENT DATE
   ========================================================= */

.payment-row .date-isian {
    width: 105px !important;

    min-width: 105px !important;

    text-align: center !important;

    white-space: nowrap;
}


/* =========================================================
   PAYMENT TYPE
   ========================================================= */

.payment-row .payment-type {
    width: 110px !important;

    min-width: 110px !important;
}


/* =========================================================
   PAYMENT NOTE
   ========================================================= */

.payment-row .note-tambahan {
    min-width: 150px;

    width: 100%;
}


/* =========================================================
   PAYMENT SUMMARY
   ========================================================= */

#paymentSummary {
    margin-top: 8px;

    padding: 12px 15px !important;

    border: 1px solid #e2e8f0;

    border-radius: 8px;

    background: #f8fafc;

    color: #334155;

    font-size: 13px !important;

    line-height: 1.7 !important;
}


/* =========================================================
   PAYMENT ACTION
   ========================================================= */

.payment-action {
    width: 42px;

    min-width: 42px;

    text-align: center;
}


/* =========================================================
   GENERAL BUTTON
   ========================================================= */

.btn {
    border-radius: 5px !important;
}

.btn-sm {
    padding: 4px 9px !important;

    font-size: 11px !important;
}


/* =========================================================
   SAVE BUTTON
   ========================================================= */

.btn-success {
    border-radius: 5px !important;

    font-weight: 600;
}


/* =========================================================
   CLOSE BUTTON
   ========================================================= */

.btn-secondary {
    border-radius: 5px !important;
}


/* =========================================================
   LIST BAHAN BAKU
   ========================================================= */

.bahan-wrapper {
    width: 100%;

    margin-top: 18px;

    overflow-x: auto;
}

.bahan-table {
    width: 100%;

    min-width: 850px;

    border-collapse: collapse;

    font-size: 11px;
}

.bahan-table th {
    padding: 7px 8px;

    background: #304783;

    color: #ffffff;

    font-weight: 700;

    text-align: center;
}

.bahan-table td {
    padding: 6px 8px;

    border: 1px solid #e1e5ea;

    vertical-align: middle;
}


/* =========================================================
   GREEN SECTION HEADER
   ========================================================= */

.section-title-green {
    width: 100%;

    padding: 9px 12px;

    margin-bottom: 0;

    border-radius: 5px 5px 0 0;

    background: #56b957;

    color: #ffffff;

    font-size: 12px;

    font-weight: 700;
}


/* =========================================================
   SEARCH
   ========================================================= */

#itemSearch,
#supplierInput {
    width: 100%;

    height: 32px;

    padding: 5px 9px;

    border: 1px solid #d1d5db;

    border-radius: 6px;

    background: #ffffff;

    color: #1f2937;

    font-size: 12px;
}

#itemSearch:focus,
#supplierInput:focus {
    border-color: #3b82f6;

    outline: 2px solid #dbeafe;
}


/* =========================================================
   AUTOCOMPLETE
   ========================================================= */

.autocomplete-list,
.autocomplete-results {
    max-height: 260px;

    overflow-y: auto;

    border: 1px solid #d1d5db;

    border-radius: 6px;

    background: #ffffff;

    box-shadow:
        0 8px 20px rgba(0, 0, 0, .08);

    z-index: 9999;
}

.autocomplete-list > div,
.autocomplete-results > div {
    padding: 8px 10px;

    cursor: pointer;

    font-size: 12px;
}

.autocomplete-list > div:hover,
.autocomplete-results > div:hover {
    background: #eff6ff;
}


/* =========================================================
   IMAGE BUTTON
   ========================================================= */

.image-box button,
.image-box .btn {
    font-size: 9px !important;

    padding: 2px 5px !important;
}


/* =========================================================
   SIGNATURE / PRINT AREA
   ========================================================= */

#printArea {
    width: 100%;

    margin-top: 20px;
}

#printArea .card {
    width: 100%;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    background: #ffffff;
}


/* =========================================================
   MODAL
   ========================================================= */

.modal-content {
    border-radius: 8px !important;

    border: 0 !important;

    box-shadow:
        0 20px 50px rgba(0, 0, 0, .18);
}

.modal-header {
    padding: 12px 16px !important;

    border-bottom: 1px solid #e5e7eb;
}

.modal-body {
    padding: 15px !important;
}

.modal-footer {
    padding: 10px 15px !important;

    border-top: 1px solid #e5e7eb;
}


/* =========================================================
   RESPONSIVE 1400
   ========================================================= */

@media (max-width: 1400px) {

    .box-body.spk-wrapper {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    .spk-table {
        font-size: 11px;

        min-width: 1200px;
    }

    .spk-table thead th {
        font-size: 10px;

        padding: 6px 7px !important;
    }

    .spk-table tbody td {   
        font-size: 11px;

        padding: 5px 7px !important;
    }

    .nama,
    .nama-barang {
        width: 145px !important;

        min-width: 145px !important;

        max-width: 145px !important;
    }

    .material {
        width: 140px !important;

        min-width: 140px !important;

        max-width: 140px !important;
    }

    .note-box,
    .catatan-cell {
        width: 135px !important;

        min-width: 135px !important;

        max-width: 135px !important;
    }
}


/* =========================================================
   RESPONSIVE 1200
   ========================================================= */

@media (max-width: 1200px) {

    .spk-table {
        min-width: 1150px;
    }

    /*
     * Jangan mengecilkan PCS/SET.
     * Tetap nyaman.
     */

    .spk-table .pcs,
    .spk-table .set {
        width: 75px !important;

        min-width: 75px !important;

        max-width: 75px !important;
    }

    .spk-table .p,
    .spk-table .l,
    .spk-table .t {
        width: 52px !important;

        min-width: 52px !important;

        max-width: 52px !important;
    }

    .spk-table .material {
        width: 135px !important;

        min-width: 135px !important;

        max-width: 135px !important;
    }
}


/* =========================================================
   LARGE SCREEN
   ========================================================= */

@media (min-width: 1600px) {

    .box-body.spk-wrapper {
        padding-left: 18px !important;

        padding-right: 18px !important;
    }

    .spk-table {
        font-size: 12px;

        min-width: 1350px;
    }

    .spk-table .pcs,
    .spk-table .set {
        width: 85px !important;

        min-width: 85px !important;

        max-width: 85px !important;
    }

    .spk-table .p,
    .spk-table .l,
    .spk-table .t {
        width: 58px !important;

        min-width: 58px !important;

        max-width: 58px !important;
    }
}


/* =========================================================
   SCROLLBAR
   ========================================================= */

.spk-table-wrapper::-webkit-scrollbar,
.box-body.spk-wrapper::-webkit-scrollbar {
    height: 8px;
}

.spk-table-wrapper::-webkit-scrollbar-track,
.box-body.spk-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;

    border-radius: 10px;
}

.spk-table-wrapper::-webkit-scrollbar-thumb,
.box-body.spk-wrapper::-webkit-scrollbar-thumb {
    background: #94a3b8;

    border-radius: 10px;
}

.spk-table-wrapper::-webkit-scrollbar-thumb:hover,
.box-body.spk-wrapper::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}


/* =========================================================
   IMPORTANT:
   PCS / SET PALING NYAMAN
   ========================================================= */

.spk-table th.pcs,
.spk-table th.set,
.spk-table td.pcs,
.spk-table td.set {

    width: 80px !important;

    min-width: 80px !important;

    max-width: 80px !important;

    text-align: center !important;

    padding-left: 12px !important;

    padding-right: 12px !important;
}

    /* =========================================================
       DATE INPUT - DD/MM/YY
       ========================================================= */
    .tgl-terima,
    .tgl-selesai {
        min-width: 180px !important;
        width: 180px !important;
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
        padding: 5px 8px !important;
        vertical-align: middle !important;
    }

    .tgl-terima:focus,
    .tgl-selesai:focus {
        outline: 1px solid #3b82f6;
        background: #fff;
    }

/* =========================================================
   CLEAN UI 100%
   Hanya mengatur ukuran/spacing visual.
   Tidak mengubah struktur table, rowspan, JS, search, add row,
   save, preview, date picker, maupun salin JPEG.
   ========================================================= */

html, body {
    overflow-x: hidden !important;
}

/* HEADER TOOLBAR */
.box-header {
    min-height: 48px !important;
    height: 48px !important;
    padding: 6px 10px !important;
}

.box-header h3 {
    font-size: 13px !important;
    margin: 0 !important;
    white-space: nowrap !important;
}

.box-header .warning,
.box-header .success {
    font-size: 9px !important;
    padding: 2px 5px !important;
    white-space: nowrap !important;
}

.box-header > div:last-child {
    min-width: 360px !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 5px !important;
}

.box-header > div:last-child > label {
    display: none !important;
}

.box-header > div:last-child > select {
    width: 105px !important;
    height: 28px !important;
    margin: 0 !important;
    font-size: 10px !important;
}

.box-header > div:last-child > div {
    margin: 0 !important;
}

.box-header > div:last-child button {
    width: auto !important;
    min-width: 78px !important;
    height: 28px !important;
    margin: 0 !important;
    padding: 3px 8px !important;
    font-size: 9px !important;
    white-space: nowrap !important;
}

/* MAIN WORKSPACE */
.box-body.spk-wrapper {
    padding: 5px 8px 10px !important;
    overflow-x: auto !important;
}

/*
 * Ini yang membuat halaman terasa seperti satu workspace.
 * Semua isi SPK diperkecil sedikit secara proporsional,
 * bukan dipaksa mengecil per-cell sehingga table rusak.
 */
@media (min-width: 1000px) {
    #printArea {
        zoom: 0.88;
    }
}

/* SEARCH */
#itemSearch {
    min-height: 28px !important;
    height: 28px !important;
    padding: 5px 8px !important;
    font-size: 10px !important;
}

/* INFO SPK
   Jangan menyentuh nth-child tabel utama karena info dan item
   berada pada table yang sama.
*/
#supplierInput {
    height: 27px !important;
    min-height: 27px !important;
    padding: 4px 7px !important;
    font-size: 10px !important;
}

/* DATE */
.tgl-terima,
.tgl-selesai {
    min-width: 160px !important;
    width: 160px !important;
}

.spk-date-wrap {
    min-width: 145px !important;
    height: 23px !important;
}

.spk-date-display {
    height: 23px !important;
    line-height: 23px !important;
    font-size: 10px !important;
}

/* MAIN TABLE:
   Pertahankan table-layout auto agar rowspan dan info header
   tidak rusak.
*/
.spk-table {
    table-layout: auto !important;
    font-size: 10px !important;
    min-width: 0 !important;
}

.spk-table thead th {
    height: 29px !important;
    padding: 5px 5px !important;
    font-size: 9px !important;
}

.spk-table tbody td {
    padding: 4px 5px !important;
    font-size: 9px !important;
}

/* Compact only the known item columns */
.kode-item,
.article-cell {
    min-width: 70px !important;
    width: 70px !important;
}

.gambar-cell {
    min-width: 68px !important;
    width: 68px !important;
}

.nama,
.nama-barang {
    min-width: 120px !important;
    width: 120px !important;
    max-width: 150px !important;
}

.dynamic-column,
.hallo,
.extra-column,
.spk-dynamic-header {
    min-width: 55px !important;
    width: 55px !important;
}

.spk-table .p,
.spk-table .l,
.spk-table .t {
    min-width: 40px !important;
    width: 40px !important;
}

.spk-table .material {
    min-width: 105px !important;
    width: 105px !important;
    max-width: 125px !important;
}

.spk-table .pcs,
.spk-table .set {
    min-width: 48px !important;
    width: 48px !important;
}

.spk-table .harga,
.spk-table .total {
    min-width: 75px !important;
    width: 75px !important;
}

.spk-table .note-box,
.spk-table .catatan-cell {
    min-width: 80px !important;
    width: 80px !important;
}

.spk-table .action-cell {
    min-width: 30px !important;
    width: 30px !important;
}

/* Images */
.preview-img {
    max-width: 58px !important;
    max-height: 48px !important;
}

.image-box {
    min-height: 48px !important;
}

/* Buttons inside table */
.btn-add-extra,
.btn-delete-extra {
    font-size: 8px !important;
    padding: 2px 5px !important;
}

/* BOTTOM SECTIONS */
.bahan-wrapper,
.payment-wrapper {
    margin-top: 8px !important;
}

.section-title-green {
    padding: 6px 9px !important;
    font-size: 9px !important;
}

.bahan-table {
    font-size: 9px !important;
    min-width: 0 !important;
}

.bahan-table th,
.bahan-table td {
    padding: 4px 5px !important;
    font-size: 9px !important;
}

.payment-wrapper {
    gap: 10px !important;
}

.payment-table {
    font-size: 9px !important;
}

.payment-table th,
.payment-table td {
    padding: 4px 5px !important;
    font-size: 9px !important;
}

#paymentSummary {
    padding: 6px !important;
    font-size: 9px !important;
}

/* SIGNATURE */
.card.mt-4 {
    margin-top: 8px !important;
}

.card-header {
    padding: 5px 8px !important;
    font-size: 10px !important;
}

.card-body {
    padding: 6px !important;
}

body{
                font-family:Arial;
                padding:20px;
            }
            table{
                width:100%;
                border-collapse:collapse;
                margin-bottom:20px;
            }
            th,td{
                border:1px solid #000;
                padding:5px;
                font-size:12px;
                vertical-align:middle;
            }
            th{
                background:#2f437f;
                color:#fff;
            }
            img{
                display:block;
                margin:auto;
            }
            .header{
                margin-bottom:20px;
            }
            .header div{
                margin-bottom:4px;
            }

.tgl-terima,
    .tgl-selesai {
        min-width: 180px !important;
        width: 180px !important;
        padding: 3px 6px !important;
        vertical-align: middle !important;
        overflow: visible !important;
    }

    .spk-date-wrap {
        position: relative;
        width: 100%;
        min-width: 160px;
        height: 24px;
    }

    .spk-date-display {
        display: block;
        width: 100%;
        height: 24px;
        box-sizing: border-box;
        border: 0;
        outline: 0;
        background: transparent;
        color: #1f2937;
        font-size: 11px;
        line-height: 24px;
        padding: 0 28px 0 5px;
        cursor: pointer;
        white-space: nowrap;
    }

    .spk-date-display::placeholder {
        color: #9ca3af;
    }

    .spk-date-display:focus {
        outline: 1px solid #3b82f6;
        background: #fff;
        border-radius: 2px;
    }

    .spk-date-picker {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
        right: 2px;
        top: 50%;
    }

    .spk-date-wrap::after {
        content: '📅';
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        pointer-events: none;
    }


/* =========================================================
   FINAL COMPACT SPK OVERRIDE
   ---------------------------------------------------------
   CSS ONLY — tidak mengubah HTML / JavaScript / fungsi.
   Tujuan: menghilangkan kolom yang terlalu melebar.
   ========================================================= */

html,
body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

.box,
.box-body.spk-wrapper {
    width: 100% !important;
    max-width: 100% !important;
}

.box-body.spk-wrapper {
    padding: 8px 10px 18px !important;
    overflow-x: auto !important;
}

/* Main table */
.spk-table-wrapper {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
}

.spk-table {
    width: max-content !important;
    min-width: 1080px !important;
    table-layout: auto !important;
    font-size: 10px !important;
}

/* Header/body compact */
.spk-table thead th {
    height: 29px !important;
    padding: 5px 6px !important;
    font-size: 9px !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
}

.spk-table tbody td {
    padding: 4px 6px !important;
    font-size: 10px !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
}

/* Article */
.spk-table .kode-item,
.spk-table .article-cell {
    width: 68px !important;
    min-width: 68px !important;
    max-width: 68px !important;
}

/* Image */
.spk-table .gambar-cell {
    width: 68px !important;
    min-width: 68px !important;
    max-width: 68px !important;
}

/* Product name */
.spk-table .nama,
.spk-table .nama-barang {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
}

/* Dynamic/custom columns such as AKSESORIS */
.spk-table .dynamic-column,
.spk-table .extra-column,
.spk-table .hallo,
.spk-table .spk-dynamic-header {
    width: 58px !important;
    min-width: 58px !important;
    max-width: 58px !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
}

/* Dimension */
.spk-table .p,
.spk-table .l,
.spk-table .t {
    width: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    padding-left: 4px !important;
    padding-right: 4px !important;
    text-align: center !important;
}

/* Material */
.spk-table .material {
    width: 105px !important;
    min-width: 105px !important;
    max-width: 105px !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
}

/* PCS / SET */
.spk-table .pcs,
.spk-table .set,
.spk-table th.pcs,
.spk-table th.set,
.spk-table td.pcs,
.spk-table td.set {
    width: 48px !important;
    min-width: 48px !important;
    max-width: 48px !important;
    padding-left: 5px !important;
    padding-right: 5px !important;
    text-align: center !important;
}

/* Harga / Total */
.spk-table .harga {
    width: 78px !important;
    min-width: 78px !important;
    max-width: 78px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
    text-align: right !important;
}

.spk-table .total {
    width: 88px !important;
    min-width: 88px !important;
    max-width: 88px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
    text-align: right !important;
}

/* Notes */
.spk-table .note-box,
.spk-table .catatan-cell {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
}

/* Inputs/selects inside table */
.spk-table input,
.spk-table select,
.spk-table textarea {
    max-width: 100% !important;
    min-height: 27px !important;
    height: 27px !important;
    padding: 3px 5px !important;
    font-size: 9.5px !important;
}

/* Image area */
.spk-table .image-box {
    min-height: 48px !important;
    padding: 2px !important;
}

.spk-table .image-box img,
.spk-table .preview-img {
    max-width: 58px !important;
    max-height: 48px !important;
    object-fit: contain !important;
}

/* Header/info area */
.spk-info td,
.spk-header td,
.spk-detail-header td {
    padding: 4px 7px !important;
    font-size: 10px !important;
}

/* Buttons */
.box .btn,
.box button.btn {
    min-height: 28px;
    font-size: 9.5px !important;
}

.box .btn-sm {
    padding: 4px 8px !important;
    font-size: 9.5px !important;
}

/* Payment + warehouse section */
.payment-wrapper {
    gap: 10px !important;
    margin-top: 12px !important;
}

.payment-table {
    font-size: 10px !important;
}

.payment-table th {
    padding: 5px 6px !important;
    font-size: 9.5px !important;
}

.payment-table td {
    padding: 5px 6px !important;
    font-size: 10px !important;
}

.bahan-wrapper {
    margin-top: 12px !important;
}

.bahan-table {
    font-size: 10px !important;
    min-width: 760px !important;
}

.bahan-table th,
.bahan-table td {
    padding: 5px 6px !important;
}

/* Scrollbar */
.spk-table-wrapper::-webkit-scrollbar,
.box-body.spk-wrapper::-webkit-scrollbar {
    height: 6px !important;
}

.spk-table-wrapper::-webkit-scrollbar-track,
.box-body.spk-wrapper::-webkit-scrollbar-track {
    background: #eef2f7 !important;
    border-radius: 8px !important;
}

.spk-table-wrapper::-webkit-scrollbar-thumb,
.box-body.spk-wrapper::-webkit-scrollbar-thumb {
    background: #aab4c2 !important;
    border-radius: 8px !important;
}

/* Mobile */
@media (max-width: 768px) {

    .box-body.spk-wrapper {
        padding: 6px !important;
    }

    .spk-table {
        min-width: 1000px !important;
    }

    .spk-table thead th {
        font-size: 8.5px !important;
        padding: 4px 5px !important;
    }

    .spk-table tbody td {
        font-size: 9px !important;
        padding: 4px 5px !important;
    }

    .spk-table .nama,
    .spk-table .nama-barang {
        width: 105px !important;
        min-width: 105px !important;
        max-width: 105px !important;
    }

    .spk-table .material {
        width: 90px !important;
        min-width: 90px !important;
        max-width: 90px !important;
    }

    .spk-table .dynamic-column,
    .spk-table .extra-column,
    .spk-table .hallo,
    .spk-table .spk-dynamic-header {
        width: 52px !important;
        min-width: 52px !important;
        max-width: 52px !important;
    }
}

/* Large desktop: jangan kembali melebar */
@media (min-width: 1600px) {

    .spk-table {
        min-width: 1080px !important;
        font-size: 10.5px !important;
    }

    .spk-table .pcs,
    .spk-table .set {
        width: 52px !important;
        min-width: 52px !important;
        max-width: 52px !important;
    }

    .spk-table .p,
    .spk-table .l,
    .spk-table .t {
        width: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
    }
}


</style>