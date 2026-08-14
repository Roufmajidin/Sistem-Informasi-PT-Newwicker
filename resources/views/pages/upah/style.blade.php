 <style>

        /* =====================================================
           TOOLBAR
        ===================================================== */

        .upah-toolbar {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 10px;

            background: #fff;

            border: 1px solid #e5e7eb;

            border-radius: 10px;

            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }


        .upah-search {
            position: relative;

            flex: 1;
        }


        .upah-search-icon {
            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            color: #9ca3af;

            pointer-events: none;

            z-index: 2;
        }


        .upah-search input {
            height: 40px;

            padding-left: 40px;

            padding-right: 40px;

            border-radius: 8px;
        }


        .upah-search-clear {
            position: absolute;

            right: 7px;

            top: 50%;

            transform: translateY(-50%);

            width: 28px;

            height: 28px;

            border: 0;

            background: transparent;

            color: #999;

            opacity: 0;

            pointer-events: none;
        }


        .upah-search-clear.show {
            opacity: 1;

            pointer-events: auto;
        }


        .upah-search-info {
            color: #6c757d;

            font-size: 13px;

            white-space: nowrap;
        }


        .btn-tambah-upah {
            height: 40px;

            padding: 0 16px;

            border-radius: 8px;

            white-space: nowrap;
        }


        /* =====================================================
           STICKY TABLE
        ===================================================== */

        #tableUpahWrapper {
            width: 100%;

            max-height: calc(100vh - 280px);

            overflow: auto;

            position: relative;

            border: 1px solid #dee2e6;

            border-radius: 6px;
        }


        #tableUpah {
            min-width: 1000px;

            border-collapse: separate;

            border-spacing: 0;
        }


        #tableUpah thead th {
            position: sticky;

            top: 0;

            z-index: 20;

            /* background: #fff; */

            white-space: nowrap;

            border-bottom: 2px solid #dee2e6;
        }


        #tableUpah th:first-child,
        #tableUpah td:first-child {

            position: sticky;
            left: 0;
            /* background: #fff; */
        }

        #tableUpah thead th:first-child {
            z-index: 30;
        }
        #tableUpah tbody td:first-child {

            z-index: 10;
        }


        /* =====================================================
           ARTICLE SEARCH
        ===================================================== */

        .article-input-wrapper {
            position: relative;
        }


        .article-input-container {
            position: relative;
        }


        .article-search-icon {
            position: absolute;

            left: 13px;

            top: 50%;

            transform: translateY(-50%);

            color: #9ca3af;

            font-size: 13px;

            pointer-events: none;

            z-index: 2;
        }


        .article-input {
            padding-left: 38px;

            padding-right: 40px;

            height: 40px;
        }


        .article-input:focus {
            border-color: #80bdff;

            box-shadow:
                0 0 0 3px rgba(0, 123, 255, .08);
        }


        .clear-article {
            position: absolute;

            right: 7px;

            top: 50%;

            transform: translateY(-50%);

            width: 28px;

            height: 28px;

            display: none;

            align-items: center;

            justify-content: center;

            border: 0;

            background: transparent;

            color: #999;

            border-radius: 6px;
        }


        .clear-article.show {
            display: flex;
        }


        .clear-article:hover {
            background: #f1f3f5;

            color: #333;
        }


        /* =====================================================
           ARTICLE SEARCH RESULT
        ===================================================== */

        .article-search-result {
            position: absolute;

            left: 0;

            right: 0;

            top: calc(100% + 4px);

            background: #fff;

            border: 1px solid #dee2e6;

            border-radius: 8px;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, .10);

            max-height: 260px;

            overflow-y: auto;

            z-index: 1055;

            display: none;
        }


        .article-search-result.show {
            display: block;
        }


        .article-item {
            padding: 10px 12px;

            cursor: pointer;

            border-bottom: 1px solid #f1f1f1;

            transition: background .12s ease;
        }


        .article-item:last-child {
            border-bottom: 0;
        }


        .article-item:hover {
            background: #f8f9fa;
        }


        .article-item-title {
            font-size: 14px;

            font-weight: 600;

            color: #212529;
        }


        .article-item-description {
            margin-top: 3px;

            font-size: 12px;

            color: #6c757d;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        /* =====================================================
           NOT FOUND
        ===================================================== */

        .article-not-found {
            display: flex;

            align-items: flex-start;

            gap: 7px;

            margin-top: 7px;

            padding: 8px 10px;

            border-radius: 6px;

            background: #fff8e1;

            border: 1px solid #ffe082;

            color: #856404;

            font-size: 12px;

            line-height: 1.45;
        }


        .article-not-found i {
            margin-top: 2px;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .upah-toolbar {
                flex-wrap: wrap;
            }


            .upah-search {
                width: 100%;

                flex-basis: 100%;
            }


            .upah-search-info {
                margin-right: auto;
            }

        }
        .modal-upah-dialog {
    max-width: 520px;
}

.modal-upah-dialog .modal-content {
    border: 0;
    border-radius: 10px;
}


/* =====================================================
   MODAL NORMAL
   Compact / mengikuti isi form
===================================================== */

#modalUpah .modal-dialog {
    width: auto;

    max-width: 560px;

    /*
     * Paksa Bootstrap/global CSS agar tidak membuat
     * dialog mengambil tinggi layar.
     */
    height: auto !important;
    min-height: 0 !important;

    margin: 1.75rem auto;
}

#modalUpah .modal-content {
    width: 100%;

    height: auto !important;
    min-height: 0 !important;

    max-height: none;

    display: block;

    border: 0;
    border-radius: 10px;

    overflow: visible;
}

#modalUpah form {
    height: auto !important;
    min-height: 0 !important;

    display: block;
}

#modalUpah .modal-header {
    flex: none;

    padding: 14px 18px;

    border-bottom: 1px solid #dee2e6;
}

#modalUpah .modal-title {
    margin: 0;

    font-size: 18px;
    font-weight: 500;
}

#modalUpah .modal-body {
    height: auto !important;
    min-height: 0 !important;

    max-height: none;

    padding: 18px;

    overflow: visible;

    flex: none;
}

#modalUpah .modal-body > * {
    max-height: none;
}

#modalUpah .form-group {
    margin-bottom: 14px;
}

#modalUpah .form-group:last-child {
    margin-bottom: 0;
}

#modalUpah .form-control {
    min-height: 38px;
}

#modalUpah textarea.form-control {
    min-height: auto;
}

#modalUpah .modal-footer {
    flex: none;

    padding: 12px 18px;

    border-top: 1px solid #dee2e6;
}


/* =====================================================
   NORMAL ARTICLE SEARCH
   Dropdown tidak menambah tinggi kecuali JS Anda
   memang ingin membuatnya masuk flow.
===================================================== */

#modalUpah .article-input-wrapper {
    position: relative;
}

#modalUpah .article-search-result {
    position: absolute;

    left: 0;
    right: 0;

    top: calc(100% + 3px);

    display: none;

    max-height: 240px;

    overflow-y: auto;

    background: #fff;

    border: 1px solid #ced4da;

    border-radius: 6px;

    box-shadow: 0 6px 18px rgba(0, 0, 0, .15);

    z-index: 99999;
}

#modalUpah .article-search-result.show {
    display: block;
}


/* =====================================================
   MASS MODE
   Modal mengikuti isi / content.
   Tidak dipaksa 55vh, 85vh, 92vh atau 100vh.
===================================================== */

#modalUpahDialog.mass-mode {
    width: 94vw;

    max-width: 1250px;

    margin: 1.5rem auto;

    height: auto !important;
    min-height: 0 !important;
}

#modalUpahDialog.mass-mode .modal-content {
    width: 100%;

    height: auto !important;
    min-height: 0 !important;

    max-height: none;

    border-radius: 10px;

    display: flex;

    flex-direction: column;
}

#massUpahSection {
    display: flex;

    flex-direction: column;
}

#massUpahSection.d-none {
    display: none !important;
}


/* =====================================================
   MASS HEADER
===================================================== */

#modalUpahDialog.mass-mode .mass-header {
    flex: 0 0 auto;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 14px 20px;

    border-bottom: 1px solid #dee2e6;
}


/* =====================================================
   MASS TABLE
===================================================== */

#modalUpahDialog.mass-mode .mass-table-wrapper {
    position: relative;

    height: auto !important;

    min-height: 0 !important;

    max-height: none;

    overflow: visible;
}

#modalUpahDialog.mass-mode #massUpahTable {
    width: 100%;

    min-width: 1000px;

    margin-bottom: 0;

    border-collapse: separate;

    border-spacing: 0;
}

#modalUpahDialog.mass-mode #massUpahTable thead th {
    position: sticky;

    top: 0;

    z-index: 20;

    /* background: #f8f9fa; */

    white-space: nowrap;

    border-bottom: 1px solid #dee2e6;
}

#modalUpahDialog.mass-mode #massUpahTable td {
    vertical-align: top;

    padding: 7px;
}

#modalUpahDialog.mass-mode #massUpahTable input {
    height: 36px;

    min-width: 0;

    font-size: 13px;

    border-radius: 4px;
}


/* =====================================================
   MASS COLUMN WIDTH
===================================================== */

#modalUpahDialog.mass-mode .mass-article {
    min-width: 220px;
}

#modalUpahDialog.mass-mode .mass-description {
    min-width: 280px;
}

#modalUpahDialog.mass-mode .mass-jenis {
    min-width: 170px;
}

#modalUpahDialog.mass-mode .mass-harga {
    min-width: 130px;
}


/* =====================================================
   MASS ARTICLE WRAPPER
===================================================== */

#modalUpahDialog.mass-mode .mass-article-wrapper {
    position: relative;

    width: 100%;
}


/* =====================================================
   MASS ARTICLE RESULT
===================================================== */

#modalUpahDialog.mass-mode .mass-article-result {
    position: absolute;

    left: 0;
    right: 0;
    top: calc(100% + 3px);

    display: none;

    width: 100%;

    max-height: 240px;

    overflow-y: auto;

    background: #fff;

    border: 1px solid #ced4da;

    border-radius: 6px;

    box-shadow: 0 6px 18px rgba(0, 0, 0, .15);

    z-index: 99999;
}

#modalUpahDialog.mass-mode
.mass-article-result.show {
    display: block;
}


/*
 * Saat JavaScript menambahkan .mass-search-open,
 * dropdown masuk ke flow sehingga modal otomatis
 * menambah tinggi.
 */

#modalUpahDialog.mass-mode.mass-search-open
.mass-article-result {
    position: relative;

    left: auto;
    right: auto;
    top: auto;

    display: block;

    width: 100%;

    margin-top: 3px;

    max-height: 240px;

    overflow-y: auto;

    z-index: 99999;
}


/* =====================================================
   MASS ARTICLE ITEM
===================================================== */

.mass-article-item {
    padding: 9px 11px;

    cursor: pointer;

    background: #fff;

    border-bottom: 1px solid #f1f1f1;

    transition: background .12s ease;
}

.mass-article-item:last-child {
    border-bottom: 0;
}

.mass-article-item:hover {
    background: #f5f7fa;
}

.mass-article-title {
    font-size: 13px;

    font-weight: 600;

    color: #212529;
}

.mass-article-description {
    margin-top: 2px;

    font-size: 11px;

    color: #6c757d;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =====================================================
   MASS ARTICLE NOT FOUND
===================================================== */

.mass-article-not-found {
    display: flex;

    align-items: flex-start;

    gap: 7px;

    padding: 9px 11px;

    font-size: 11px;

    line-height: 1.4;

    color: #856404;

    background: #fff8e1;
}


/* =====================================================
   MASS FOOTER
===================================================== */

#modalUpahDialog.mass-mode .mass-footer {
    flex: 0 0 auto;

    display: flex;

    align-items: center;

    padding: 12px 20px;

    background: #fff;

    border-top: 1px solid #dee2e6;
}

#modalUpahDialog.mass-mode .mass-footer .btn {
    white-space: nowrap;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    #modalUpah .modal-dialog {
        max-width: calc(100% - 20px);

        margin: 1rem auto;
    }

    #modalUpahDialog.mass-mode {
        width: 98vw;

        max-width: 98vw;

        margin: 1rem auto;
    }

    #modalUpahDialog.mass-mode #massUpahTable {
        min-width: 950px;
    }

    #modalUpahDialog.mass-mode
    .mass-article-result {
        max-height: 200px;
    }

    #modalUpahDialog.mass-mode.mass-search-open
    .mass-article-result {
        max-height: 200px;
    }

}

</style>
