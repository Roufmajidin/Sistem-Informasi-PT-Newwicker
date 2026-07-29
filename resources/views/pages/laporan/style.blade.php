<style>
    /* Kode Barang */
.table th:nth-child(2),
.table td:nth-child(2){
    width: 110px;
    min-width: 110px;
}

/* Harga */
.table th:nth-child(6),
.table td:nth-child(6){
    width: 100px;
    min-width: 100px;
}

/* Saldo */
.table th:nth-child(7),
.table td:nth-child(7){
    width: 85px;
    min-width: 85px;
}

/* Stok IN */
.table th:nth-child(8),
.table td:nth-child(8){
    width: 80px;
    min-width: 80px;
}

/* Stok OUT */
.table th:nth-child(9),
.table td:nth-child(9){
    width: 80px;
    min-width: 80px;
}

/* Input agar mengikuti lebar kolom */
.table td input.form-control,
.table td select.form-control{
    width:100%;
    min-width:0;
}
    .table input.form-control,
    .table select.form-control {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 2px 4px;
        height: auto;
    }
    .table input.form-control:focus,
    .table select.form-control:focus {
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }
    .ghost-wrapper {
        position: relative;
    }
    .ghost-text {
        position: absolute;
        left: 12px;
        top: 7px;
        color: #c0c0c0;
        pointer-events: none;
        z-index: 1;
    }
    .nama-barang {
        position: relative;
        background: transparent;
        z-index: 2;
    }


    /* Container table */
.table-stok-wrapper{
    max-height: 650px;      /* sesuaikan tinggi */
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #dee2e6;
}

/* Sticky Header */
.table-stok-wrapper table thead th{
    position: sticky;
    top: 0;
    z-index: 100;
    /* background: #fff; */
    white-space: nowrap;
    box-shadow: inset 0 -1px 0 #dee2e6;
}

/* Supaya baris tidak terlalu tinggi */
.table-stok-wrapper table td,
.table-stok-wrapper table th{
    padding: .35rem .5rem;
    vertical-align: middle;
}

/* Input lebih pendek */
.table-stok-wrapper input,
.table-stok-wrapper select{
    height: 30px;
    padding: .2rem .45rem;
    font-size: 12px;
}
</style>
