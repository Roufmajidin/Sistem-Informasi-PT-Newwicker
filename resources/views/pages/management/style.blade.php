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
