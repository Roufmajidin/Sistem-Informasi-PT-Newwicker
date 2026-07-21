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
</style>
