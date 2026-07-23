<style>
    .inventor-card{
        border:none;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 4px 20px rgba(0,0,0,.05);
    }

    .inventor-table th{
        background:#111827;
        color:white;
        font-size:13px;
        text-transform:uppercase;
        border:none !important;
    }

    .inventor-table td{
        vertical-align:middle !important;
    }

    .inventor-row{
        cursor:pointer;
        transition:.2s;
    }

    .inventor-row:hover{
        background:#f3f4f6;
    }

    .inventor-row.active-row{
        background:#dbeafe !important;
        border-left:5px solid #2563eb;
    }

    #inventorModal .modal-dialog{
        max-width:1500px;
    }

    #inventorModal .form-control{
        min-width:120px;
        height:42px;
        border-radius:10px;
    }

    #inventorModal table td{
        vertical-align:middle;
    }
     .modal-full-custom {

        max-width: 96vw !important;

        width: 96vw !important;

        margin: 10px auto;

    }

    .modal-full-custom .modal-content {

        min-height: 95vh;

        border-radius: 12px;

    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    #inventorInputModal table td,
    #inventorInputModal table th {

        vertical-align: middle;
        white-space: nowrap;

    }

    #inventorDetailModal table td,
    #inventorDetailModal table th {

        vertical-align: middle;
        white-space: nowrap;

    }

    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    #inventorInputModal .form-control,
    #inventorInputModal .form-select {

        min-width: 120px;

    }

    /*
    |--------------------------------------------------------------------------
    | MODAL BODY
    |--------------------------------------------------------------------------
    */

    .modal-body {

        overflow-x: auto;

    }

</style>
 <style>
            .inventor-wrapper {
                max-height: 70vh;
                overflow: auto;
                position: relative;
            }

            .inventor-table {
                border-collapse: separate;
                border-spacing: 0;
            }

            /* HEADER */
            .inventor-table thead th {
                position: sticky;
                top: 0;
                background: #1f3b7a;
                color: #fff;
                z-index: 100;
            }

            /* ========================= */
            /* STICKY NO */
            /* ========================= */

            .inventor-table td:nth-child(1),
            .inventor-table th:nth-child(1) {

                position: sticky;
                left: 0;

                width: 55px;
                min-width: 55px;
                max-width: 55px;

                background: #fff;
                background-clip: padding-box;

                z-index: 20;

                box-shadow: 2px 0 4px rgba(0, 0, 0, .08);

            }

            /* ========================= */
            /* STICKY NO SPK */
            /* ========================= */

            .inventor-table td:nth-child(2),
            .inventor-table th:nth-child(2) {

                position: sticky;
                left: 55px;

                width: 190px;
                min-width: 190px;

                background: #fff;
                background-clip: padding-box;

                z-index: 19;

                box-shadow: 2px 0 4px rgba(0, 0, 0, .08);

            }

            /* BODY */
            .inventor-table tbody td:nth-child(1) {
                position: sticky;
                left: 0;
                background: #fff;
                z-index: 20;
            }

            .inventor-table tbody td:nth-child(2) {
                position: sticky;
                left: 60px;
                background: #fff;
                z-index: 19;
            }

            /* HEADER */
            .inventor-table thead th:nth-child(1) {
                position: sticky;
                left: 0;
                top: 0;
                background: #1f3b7a;
                color: #fff;
                z-index: 120;
            }

            .inventor-table thead th:nth-child(2) {
                position: sticky;
                left: 60px;
                top: 0;
                background: #1f3b7a;
                color: #fff;
                z-index: 119;
            }

            /* HEADER */

            .inventor-table thead th:nth-child(1) {

                left: 0;
                z-index: 120;

            }

            .inventor-table thead th:nth-child(2) {

                left: 55px;
                z-index: 119;

            }

            /* Tinggi select */
            .select2-container .select2-selection--single {
                height: 38px !important;
                border: 1px solid #ced4da !important;
                border-radius: .375rem !important;
            }

            /* Posisi teks */
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px !important;
                padding-left: 12px;
            }

            /* Posisi icon panah */
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
                right: 8px;
            }

            /* Lebar penuh */
            .select2-container {
                width: 100% !important;
            }

            .harga-vivi-input {
                border: none;
                background: transparent;
                width: 100%;
                outline: none;
                box-shadow: none;
                padding: 0;
            }

            .harga-vivi-input:focus {
                border: none;
                outline: none;
                box-shadow: none;
                background: #fffbe6;
            }

            .harga-vivi-input {
                border: none;
                background: transparent;
                width: 100%;
                padding: 2px 4px;
            }

            .harga-vivi-input:hover {
                background: #f8f9fa;
            }

            .harga-vivi-input:focus {
                background: #fff3cd;
                border: 1px solid #ffc107;
            }

            .deadline-card {
                min-width: 100px;
            }

            .deadline-bar {
                height: 10px;
                background: #edf2f7;
                border-radius: 20px;
                overflow: hidden;
            }

            .deadline-fill {
                height: 100%;
                border-radius: 20px;
                transition: .5s ease;
            }

            .deadline-success {
                background: linear-gradient(90deg,
                        #16a34a,
                        #22c55e);
            }

            .deadline-info {
                background: linear-gradient(90deg,
                        #0891b2,
                        #06b6d4);
            }

            .deadline-warning {
                background: linear-gradient(90deg,
                        #f59e0b,
                        #fbbf24);
            }

            .deadline-danger {
                background: linear-gradient(90deg,
                        #dc2626,
                        #ef4444);
            }

            .deadline-secondary {
                background: linear-gradient(90deg,
                        #94a3b8,
                        #cbd5e1);
            }

            .deadline-footer {
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                margin-top: 4px;
                color: #64748b;
            }

            .modal-full-custom {
                max-width: 96%;
            }

            .inventor-row {
                cursor: pointer;
                transition: .2s;
            }

            .inventor-row:hover {
                background: #f5f5f5;
            }

            .qty-warning {
                font-size: 11px;
            }

            .timeline-wrapper {
                max-height: 200px;
                overflow-y: auto;
            }

            .timeline-wrapper thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                /* background: #f8f9fa; */
            }
            .item-spk{
    max-width: 180px;      /* sesuaikan */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 11px;
    color: #6c757d;
    margin-left: 8px;
}
/* s */
.inventor-table tbody tr{
    cursor: pointer;
    transition: .15s;
}

.inventor-table tbody tr:hover{
    background:#f5f9ff;
}

.inventor-table tbody tr.active-row{
    background:#dbeafe !important;
}

.inventor-table tbody tr.active-row td{
    font-weight:600;
}
        </style>
