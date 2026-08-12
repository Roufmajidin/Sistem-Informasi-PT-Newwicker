<style>
.search-dropdown {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 9999;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 0 0 6px 6px;
    max-height: 280px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 5px 15px rgba(0,0,0,.12);
}

.search-dropdown.show {
    display: block;
}

.search-item {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.search-item:last-child {
    border-bottom: none;
}

.search-item:hover {
    background: #f5f5f5;
}

.search-item-title {
    font-weight: 600;
}

.search-item-meta {
    font-size: 12px;
    color: #777;
}

.search-empty {
    padding: 12px;
    color: #777;
    text-align: center;
}
</style>
<style>
/* =========================================================
   MODAL SUBKON
========================================================= */

#modalTambahKontrak .modal-dialog {
    max-width: 700px;
    margin: 1.75rem auto;
}

#modalTambahKontrak .modal-content {
    height: auto !important;
    min-height: 0 !important;
}

#modalTambahKontrak .modal-body {
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    padding: 20px;
}

#modalTambahKontrak .modal-footer {
    flex-shrink: 0;
    padding: 12px 20px;
}

#modalTambahKontrak .form-group {
    margin-bottom: 15px;
}

#modalTambahKontrak label {
    margin-bottom: 5px;
}

#modalTambahKontrak .alert {
    margin-bottom: 15px;
}
.article-search-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    width: 100%;
}

.article-input-wrapper {
    flex: 1;
    min-width: 0;
}

.article-search-result {
    width: 320px;
    max-height: 180px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
    flex-shrink: 0;
}

.article-search-result.show {
    display: block;
}

.article-search-result .search-item {
    padding: 9px 11px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.article-search-result .search-item:last-child {
    border-bottom: none;
}

.article-search-result .search-item:hover {
    background: #f5f5f5;
}

.article-search-result .search-item-title {
    font-weight: 600;
    font-size: 13px;
}

.article-search-result .search-item-meta {
    font-size: 11px;
    color: #777;
    margin-top: 2px;
}

.article-search-result .search-empty {
    padding: 12px;
    text-align: center;
    font-size: 12px;
    color: #777;
}
/* =========================================================
   SUBKON STICKY TABLE
========================================================= */

#tableSubkonWrapper {
    width: 100%;
    max-height: calc(100vh - 280px);
    overflow: auto;
    position: relative;
}


/* TABLE */

#tableSubkon {
    width: 100%;
    min-width: 1000px;
    border-collapse: separate;
    border-spacing: 0;
}


/* =========================================================
   STICKY HEADER
========================================================= */
.subkon-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 10px;

    background: #fff;

    border: 1px solid #e5e7eb;
    border-radius: 10px;

    box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
}


/* SEARCH */

.subkon-search {
    position: relative;
    flex: 1;
    min-width: 250px;
}


.subkon-search-icon {
    position: absolute;

    left: 14px;
    top: 50%;

    transform: translateY(-50%);

    color: #9ca3af;

    pointer-events: none;

    z-index: 2;
}


.subkon-search input {
    height: 40px;

    padding-left: 40px;
    padding-right: 40px;

    border-radius: 8px;

    border: 1px solid #dee2e6;

    box-shadow: none;
}


.subkon-search input:focus {
    border-color: #80bdff;

    box-shadow:
        0 0 0 3px rgba(0, 123, 255, .08);
}


/* CLEAR */

.subkon-search-clear {
    position: absolute;

    right: 7px;
    top: 50%;

    transform: translateY(-50%);

    width: 28px;
    height: 28px;

    border: 0;
    background: transparent;

    color: #999;

    border-radius: 6px;

    opacity: 0;

    pointer-events: none;

    transition: .15s;
}


.subkon-search-clear.show {
    opacity: 1;
    pointer-events: auto;
}


.subkon-search-clear:hover {
    background: #f1f3f5;
    color: #333;
}


/* INFO */

.subkon-search-info {
    color: #6c757d;

    font-size: 13px;

    white-space: nowrap;

    min-width: 70px;

    text-align: right;
}


/* TAMBAH */

.btn-tambah-kontrak {
    height: 40px;

    padding: 0 16px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 500;

    white-space: nowrap;

    box-shadow:
        0 2px 4px rgba(0, 0, 0, .08);
}
#tableSubkon thead th {
    position: sticky;
    top: 0;
    z-index: 20;

    /* background: #fff; */

    white-space: nowrap;

    border-bottom: 2px solid #dee2e6;
}


/* =========================================================
   STICKY KOLOM NO
========================================================= */

#tableSubkon th:first-child,
#tableSubkon td:first-child {
    position: sticky;
    left: 0;
}


/* HEADER NO harus paling depan */

#tableSubkon thead th:first-child {
    z-index: 30;
    /* background: #fff; */
}


/* BODY NO */

#tableSubkon tbody td:first-child {
    z-index: 10;
    background: #fff;
}


/* =========================================================
   HOVER
========================================================= */

#tableSubkon tbody tr:hover td:first-child {
    background: #f5f5f5;
}


/* =========================================================
   ACTION COLUMN
========================================================= */

#tableSubkon th:last-child,
#tableSubkon td:last-child {
    white-space: nowrap;
}
</style>