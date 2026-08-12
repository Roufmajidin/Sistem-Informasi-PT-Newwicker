@extends('master.master')

@section('content')

<style>

/* =========================================================
   MASTER PAGE
========================================================= */

.master-page {
    width: 100%;
    padding: 15px;
}

.master-header {
    margin-bottom: 15px;
}

.master-header h3 {
    margin: 0;

    color: #1f2937;

    font-size: 20px;
    font-weight: 700;
}

.master-header p {
    margin: 5px 0 0;

    color: #6b7280;

    font-size: 12px;
}


/* =========================================================
   CARD
========================================================= */

.master-card {
    width: 100%;

    overflow: hidden;

    border:
        1px solid #e5e7eb;

    border-radius: 9px;

    background: #fff;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, .03);
}


/* =========================================================
   TOOLBAR
========================================================= */

.master-toolbar {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 12px 15px;

    border-bottom:
        1px solid #e5e7eb;

    background: #f8fafc;
}

.master-toolbar-left {
    display: flex;

    align-items: center;

    gap: 12px;
}

.master-toolbar-right {
    display: flex;

    align-items: center;

    gap: 8px;
}


.master-select-all {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin: 0;

    color: #374151;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}

.master-select-all input {
    width: 17px;
    height: 17px;

    margin: 0;

    cursor: pointer;
}


.selected-count {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 24px;

    padding: 3px 9px;

    border-radius: 12px;

    background: #e5e7eb;

    color: #374151;

    font-size: 11px;

    font-weight: 700;
}


/* =========================================================
   BUTTON
========================================================= */

.btn-master {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    min-height: 34px;

    padding: 6px 13px;

    border: 0;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

    transition:
        background .15s ease,
        transform .15s ease;
}

.btn-master-primary {
    background: #304783;

    color: #fff;
}

.btn-master-primary:hover {
    background: #263a70;

    transform:
        translateY(-1px);
}

.btn-master-primary:disabled {
    opacity: .5;

    cursor: not-allowed;

    transform: none;
}


/* =========================================================
   TABLE WRAPPER
========================================================= */

.master-table-wrapper {
    width: 100%;

    overflow-x: auto;

    overflow-y: visible;
}


/* =========================================================
   TABLE
========================================================= */

.master-table {
    width: 100%;

    min-width: 1600px;

    border-collapse: separate;

    border-spacing: 0;

    font-size: 12px;
}


/* =========================================================
   HEADER
========================================================= */

.master-table thead th {
    position: sticky;

    top: 0;

    z-index: 5;

    padding: 9px 8px;

    border-right:
        1px solid rgba(255,255,255,.15);

    border-bottom:
        1px solid #243765;

    background: #304783;

    color: #fff;

    font-size: 11px;

    font-weight: 700;

    text-align: center;

    vertical-align: middle;

    white-space: nowrap;
}


/* =========================================================
   BODY
========================================================= */

.master-table tbody td {
    padding: 8px;

    border-right:
        1px solid #e5e7eb;

    border-bottom:
        1px solid #e5e7eb;

    background: #fff;

    color: #374151;

    vertical-align: middle;
}


.master-table tbody tr:nth-child(even) td {
    background: #fafbfc;
}


/* =========================================================
   ROW CLICK
========================================================= */

.master-item-row {
    cursor: pointer;

    transition:
        background .15s ease,
        box-shadow .15s ease;
}


.master-item-row:hover td {
    background:
        #eef4ff !important;
}


.master-item-row:hover td:first-child {
    box-shadow:
        inset 4px 0 0 #304783;
}


.master-item-row:hover .article-cell {
    color: #1d4ed8 !important;
}


.master-item-row:hover .description-cell {
    color: #1e3a8a !important;
}


/* =========================================================
   ALREADY MASTER
========================================================= */

.master-item-row.already-master-row td {
    background: #f8fafc;
}


.master-item-row.already-master-row:hover td {
    background:
        #f0fdf4 !important;
}


.master-item-row.already-master-row:hover td:first-child {
    box-shadow:
        inset 4px 0 0 #16a34a;
}


.master-item-row.already-master-row:hover .article-cell {
    color: #15803d !important;
}


/* =========================================================
   CHECKBOX
========================================================= */

.check-cell {
    width: 45px;

    min-width: 45px;

    text-align: center !important;
}

.check-cell input {
    width: 17px;

    height: 17px;

    margin: 0;

    cursor: pointer;
}

.master-table
.item-check:disabled {
    cursor: not-allowed;

    opacity: .65;
}


/* =========================================================
   ID
========================================================= */

.id-cell {
    width: 75px;

    min-width: 75px;

    text-align: center;

    white-space: nowrap;
}


/* =========================================================
   ARTICLE CODE
========================================================= */

.article-code-cell {
    width: 130px;

    min-width: 130px;

    color: #6b7280 !important;

    text-align: center;

    white-space: nowrap;
}


/* =========================================================
   ARTICLE NR
========================================================= */

.article-cell {
    width: 125px;

    min-width: 125px;

    color: #111827 !important;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.status-cell {
    width: 120px;

    min-width: 120px;

    text-align: center;

    white-space: nowrap;
}


.master-status {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 95px;

    padding: 4px 8px;

    border-radius: 12px;

    font-size: 10px;

    font-weight: 700;
}


.master-status.sudah {
    background: #dcfce7;

    color: #166534;
}


.master-status.belum {
    background: #fef3c7;

    color: #92400e;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.description-cell {
    width: 270px;

    min-width: 270px;

    color: #111827 !important;

    font-weight: 600;

    line-height: 1.35;
}


/* =========================================================
   SUB CATEGORY
========================================================= */

.subcategory-cell {
    width: 110px;

    min-width: 110px;

    white-space: nowrap;
}


/* =========================================================
   COMPOSITION
========================================================= */

.composition-cell {
    width: 130px;

    min-width: 130px;
}


/* =========================================================
   FINISHING
========================================================= */

.finishing-cell {
    width: 170px;

    min-width: 170px;
}


/* =========================================================
   DIMENSION
========================================================= */

.dimension-cell {
    width: 145px;

    min-width: 145px;

    text-align: center;

    white-space: nowrap;

    font-variant-numeric:
        tabular-nums;
}


/* =========================================================
   CBM
========================================================= */

.cbm-cell {
    width: 100px;

    min-width: 100px;

    text-align: right;

    white-space: nowrap;

    font-variant-numeric:
        tabular-nums;
}


/* =========================================================
   PRICE
========================================================= */

.price-cell {
    width: 120px;

    min-width: 120px;

    text-align: right;

    white-space: nowrap;

    font-variant-numeric:
        tabular-nums;
}


/* =========================================================
   PHOTO
========================================================= */

.photo-cell {
    width: 90px;

    min-width: 90px;

    text-align: center;
}


.photo-cell img {
    display: block;

    width: 58px;

    height: 58px;

    margin: auto;

    padding: 2px;

    object-fit: contain;

    border:
        1px solid #e5e7eb;

    border-radius: 5px;

    background: #fff;
}


.photo-empty {
    color: #9ca3af;

    font-size: 11px;
}


/* =========================================================
   PO
========================================================= */

.po-cell {
    width: 130px;

    min-width: 130px;

    color: #374151;

    white-space: nowrap;
}


.po-number {
    color: #111827;

    font-weight: 700;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-master {
    padding: 60px 20px !important;

    color: #9ca3af !important;

    text-align: center;

    font-size: 13px;
}


/* =========================================================
   ALERT
========================================================= */

.master-alert {
    margin-bottom: 15px;

    padding: 10px 13px;

    border-radius: 6px;

    font-size: 12px;
}


/* =========================================================
   PAGINATION
========================================================= */

.pagination-wrapper {
    padding: 12px 15px;

    border-top:
        1px solid #e5e7eb;

    background: #fff;
}


/* =========================================================
   MODAL OVERLAY
========================================================= */

.master-detail-modal-overlay {
    position: fixed !important;

    inset: 0 !important;

    z-index: 2147483647 !important;

    display: none;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(15, 23, 42, .68);

    backdrop-filter:
        blur(4px);
}


.master-detail-modal-overlay.active {
    display: flex !important;
}


/* =========================================================
   MODAL BOX
========================================================= */

.master-detail-modal-box {
    position: relative;

    width: min(
        1050px,
        calc(100vw - 40px)
    );

    max-height:
        calc(100vh - 40px);

    overflow: hidden;

    border-radius: 16px;

    background: #fff;

    box-shadow:
        0 30px 100px
        rgba(0, 0, 0, .35);

    animation:
        masterModalShow .18s ease-out;
}


@keyframes masterModalShow {

    from {
        opacity: 0;

        transform:
            translateY(15px)
            scale(.97);
    }

    to {
        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }

}


/* =========================================================
   MODAL HEADER
========================================================= */

.master-detail-modal-header {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 20px 24px;

    background:
        linear-gradient(
            135deg,
            #304783,
            #172554
        );

    color: #fff;
}


.master-detail-kicker {
    margin-bottom: 5px;

    color:
        rgba(255,255,255,.65);

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.5px;
}


.master-detail-title {
    margin: 0;

    color: #fff;

    font-size: 21px;

    font-weight: 700;

    line-height: 1.3;
}


.master-detail-article {
    margin-top: 5px;

    color: #bfdbfe;

    font-size: 12px;

    font-weight: 600;
}


.master-detail-close {
    flex: 0 0 auto;

    width: 36px;

    height: 36px;

    padding: 0;

    border: 0;

    border-radius: 50%;

    background:
        rgba(255,255,255,.12);

    color: #fff;

    font-size: 26px;

    line-height: 36px;

    cursor: pointer;
}


.master-detail-close:hover {
    background:
        rgba(255,255,255,.25);
}


/* =========================================================
   MODAL BODY
========================================================= */

.master-detail-modal-body {
    max-height:
        calc(100vh - 180px);

    padding: 22px 24px;

    overflow-y: auto;

    background: #f8fafc;
}


/* =========================================================
   MODAL TOP
========================================================= */

.master-detail-top {
    display: grid;

    grid-template-columns:
        230px 1fr;

    gap: 22px;
}


/* =========================================================
   PHOTO
========================================================= */

.master-detail-photo-area {
    text-align: center;
}


.master-detail-photo-box {
    width: 220px;

    height: 220px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: auto;

    overflow: hidden;

    border:
        1px solid #e5e7eb;

    border-radius: 12px;

    background: #fff;
}


.master-detail-photo-box img {
    display: none;

    width: 100%;

    height: 100%;

    padding: 10px;

    object-fit: contain;
}


.master-detail-photo-box span {
    color: #9ca3af;

    font-size: 12px;
}


/* =========================================================
   MODAL STATUS
========================================================= */

.master-detail-status {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    margin-top: 10px;

    padding: 6px 14px;

    border-radius: 20px;

    background: #fef3c7;

    color: #92400e;

    font-size: 10px;

    font-weight: 700;
}


.master-detail-status.master {
    background: #dcfce7;

    color: #166534;
}


/* =========================================================
   MODAL SECTION TITLE
========================================================= */

.master-detail-section-title {
    margin-bottom: 10px;

    color: #304783;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: .8px;
}


/* =========================================================
   INFO GRID
========================================================= */

.master-detail-info-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 10px;
}


.master-detail-info {
    min-height: 62px;

    padding: 11px 13px;

    border:
        1px solid #e5e7eb;

    border-radius: 9px;

    background: #fff;
}


.master-detail-info span {
    display: block;

    margin-bottom: 5px;

    color: #9ca3af;

    font-size: 9px;

    font-weight: 700;

    text-transform: uppercase;
}


.master-detail-info strong {
    display: block;

    color: #111827;

    font-size: 13px;

    font-weight: 700;

    line-height: 1.35;
}


/* =========================================================
   DIMENSION
========================================================= */

.master-detail-dimension-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 10px;

    margin-top: 20px;
}


.master-detail-dimension {
    padding: 14px;

    border:
        1px solid #e5e7eb;

    border-radius: 9px;

    background: #fff;
}


.master-detail-dimension.item {
    border-left:
        4px solid #304783;
}


.master-detail-dimension.pack {
    border-left:
        4px solid #16a34a;
}


.master-detail-dimension.cbm {
    border-left:
        4px solid #f59e0b;
}


.master-detail-dimension span {
    display: block;

    margin-bottom: 5px;

    color: #9ca3af;

    font-size: 10px;

    font-weight: 700;
}


.master-detail-dimension strong {
    color: #111827;

    font-size: 15px;

    font-weight: 700;

    white-space: nowrap;
}


/* =========================================================
   REMARK
========================================================= */

.master-detail-remark-section {
    margin-top: 20px;
}


.master-detail-remark {
    min-height: 45px;

    padding: 11px 13px;

    border:
        1px solid #e5e7eb;

    border-radius: 8px;

    background: #fff;

    color: #4b5563;

    font-size: 12px;

    line-height: 1.5;
}


/* =========================================================
   DETAIL ID
========================================================= */

.master-detail-id {
    margin-top: 15px;

    color: #9ca3af;

    font-size: 10px;
}


.master-detail-id strong {
    color: #374151;
}


/* =========================================================
   MODAL FOOTER
========================================================= */

.master-detail-modal-footer {
    display: flex;

    justify-content: flex-end;

    padding: 12px 20px;

    border-top:
        1px solid #e5e7eb;

    background: #fff;
}


.master-detail-button-close {
    padding: 7px 18px;

    border: 0;

    border-radius: 6px;

    background: #374151;

    color: #fff;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}


.master-detail-button-close:hover {
    background: #1f2937;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    .master-toolbar {
        align-items: flex-start;

        flex-direction: column;
    }

    .master-toolbar-right {
        width: 100%;
    }

    .btn-master {
        width: 100%;
    }

    .master-detail-modal-overlay {
        padding: 10px;
    }

    .master-detail-modal-box {
        width: 100%;

        max-height:
            calc(100vh - 20px);
    }

    .master-detail-top {
        grid-template-columns: 1fr;
    }

    .master-detail-photo-box {
        width: 180px;

        height: 180px;
    }

    .master-detail-info-grid {
        grid-template-columns: 1fr;
    }

    .master-detail-dimension-grid {
        grid-template-columns: 1fr;
    }

}

</style>


<div class="master-page">


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="master-header">

        <h3>
            Bank Data Master Item
        </h3>

        <p>
            Pilih item dari Detail PO yang ingin
            dimasukkan ke database Master Item.
            Klik baris untuk melihat detail item.
        </p>

    </div>


    {{-- =====================================================
         SUCCESS
    ====================================================== --}}

    @if(session('success'))

        <div class="alert alert-success master-alert">

            {{ session('success') }}

        </div>

    @endif


    {{-- =====================================================
         ERROR
    ====================================================== --}}

    @if(session('error'))

        <div class="alert alert-danger master-alert">

            {{ session('error') }}

        </div>

    @endif


    {{-- =====================================================
         VALIDATION
    ====================================================== --}}

    @if($errors->any())

        <div class="alert alert-danger master-alert">

            <ul
                style="
                    margin:0;
                    padding-left:18px;
                "
            >

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =====================================================
         CARD
    ====================================================== --}}

    <div class="master-card">

        <form
            method="POST"
            action="{{ route('master.detail-po.store') }}"
            id="masterForm"
        >

            @csrf


            {{-- =================================================
                 TOOLBAR
            ================================================== --}}

            <div class="master-toolbar">

                <div class="master-toolbar-left">

                    <label class="master-select-all">

                        <input
                            type="checkbox"
                            id="checkAll"
                        >

                        <span>
                            Pilih Semua
                        </span>

                    </label>


                    <span
                        id="selectedCount"
                        class="selected-count"
                    >
                        0 dipilih
                    </span>

                </div>


                <div class="master-toolbar-right">

                    <button
                        type="submit"
                        id="btnImport"
                        class="
                            btn-master
                            btn-master-primary
                        "
                        disabled
                    >

                        <span>
                            +
                        </span>

                        <span>
                            Masukkan ke Master Item
                        </span>

                    </button>

                </div>

            </div>


            {{-- =================================================
                 TABLE
            ================================================== --}}

            <div class="master-table-wrapper">

                <table class="master-table">

                    <thead>

                        <tr>

                            <th class="check-cell">
                                ✓
                            </th>

                            <th class="id-cell">
                                ID DETAIL
                            </th>

                            <th class="article-code-cell">
                                ARTICLE CODE
                            </th>

                            <th class="article-cell">
                                ARTICLE NR
                            </th>

                            <th class="status-cell">
                                STATUS
                            </th>

                            <th class="description-cell">
                                DESCRIPTION
                            </th>

                            <th class="subcategory-cell">
                                SUB CATEGORY
                            </th>

                            <th class="composition-cell">
                                COMPOSITION
                            </th>

                            <th class="finishing-cell">
                                FINISHING
                            </th>

                            <th class="dimension-cell">
                                ITEM D × W × H
                            </th>

                            <th class="dimension-cell">
                                PACK D × W × H
                            </th>

                            <th class="cbm-cell">
                                CBM
                            </th>

                            <th class="price-cell">
                                FOB USD
                            </th>

                            <th class="photo-cell">
                                PHOTO
                            </th>

                            <th class="po-cell">
                                PO
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($detailPo as $row)

                            @php

                                /*
                                |--------------------------------------------------------------------------
                                | DETAIL
                                |--------------------------------------------------------------------------
                                */

                                $detail =
                                    is_array($row->detail)
                                        ? $row->detail
                                        : [];


                                /*
                                |--------------------------------------------------------------------------
                                | ARTICLE
                                |--------------------------------------------------------------------------
                                */

                                $article =
                                    trim(
                                        (string) (
                                            $detail['article_nr_']
                                            ?? ''
                                        )
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | CEK MASTER
                                |--------------------------------------------------------------------------
                                */

                                $isMaster =
                                    $article !== '' &&
                                    in_array(
                                        $article,
                                        $masterArticles ?? [],
                                        true
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | DESCRIPTION
                                |--------------------------------------------------------------------------
                                */

                                $description =
                                    $detail['description']
                                    ?? '-';


                                /*
                                |--------------------------------------------------------------------------
                                | SUB CATEGORY
                                |--------------------------------------------------------------------------
                                */

                                $subCategory =
                                    $detail['sub_category']
                                    ?? '-';


                                /*
                                |--------------------------------------------------------------------------
                                | COMPOSITION
                                |--------------------------------------------------------------------------
                                */

                                $composition =
                                    $detail['composition']
                                    ?? '-';


                                /*
                                |--------------------------------------------------------------------------
                                | FINISHING
                                |--------------------------------------------------------------------------
                                */

                                $finishing =
                                    $detail['finishing']
                                    ?? '-';


                                /*
                                |--------------------------------------------------------------------------
                                | ITEM DIMENSION
                                |--------------------------------------------------------------------------
                                */

                                $itemDimension =
                                    collect([

                                        $detail['item_d']
                                            ?? null,

                                        $detail['item_w']
                                            ?? null,

                                        $detail['item_h']
                                            ?? null,

                                    ])
                                    ->filter(
                                        fn ($value) =>
                                            $value !== null &&
                                            $value !== ''
                                    )
                                    ->implode(' × ');


                                /*
                                |--------------------------------------------------------------------------
                                | PACK DIMENSION
                                |--------------------------------------------------------------------------
                                */

                                $packDimension =
                                    collect([

                                        $detail['pack_d']
                                            ?? null,

                                        $detail['pack_w']
                                            ?? null,

                                        $detail['pack_h']
                                            ?? null,

                                    ])
                                    ->filter(
                                        fn ($value) =>
                                            $value !== null &&
                                            $value !== ''
                                    )
                                    ->implode(' × ');


                                /*
                                |--------------------------------------------------------------------------
                                | PO
                                |--------------------------------------------------------------------------
                                */

                                $poNumber = '-';

                                if ($row->po) {

                                    $poNumber =
                                        $row->po->no_po
                                        ?? $row->po->po
                                        ?? $row->po->nomor_po
                                        ?? $row->po->id;

                                }

                            @endphp


                            {{-- =================================================
                                 ROW
                            ================================================== --}}

                            <tr
                                class="
                                    master-item-row
                                    {{ $isMaster
                                        ? 'already-master-row'
                                        : ''
                                    }}
                                "
                                data-detail-id="{{ $row->id }}"
                                onclick="
                                    openMasterDetail(
                                        {{ $row->id }}
                                    )
                                "
                            >


                                {{-- =================================================
                                     JSON DETAIL
                                ================================================== --}}

                                <script
                                    type="application/json"
                                    id="master-data-{{ $row->id }}"
                                >
{!! json_encode(
    $detail,
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_AMP |
    JSON_HEX_QUOT
) !!}
                                </script>


                                {{-- =================================================
                                     CHECKBOX
                                ================================================== --}}

                                <td class="check-cell">

                                    <input
                                        type="checkbox"
                                        name="detail_po_ids[]"
                                        value="{{ $row->id }}"
                                        class="item-check"
                                        data-article="{{ $article }}"
                                        {{ $isMaster
                                            ? 'checked disabled'
                                            : ''
                                        }}
                                        onclick="
                                            event.stopPropagation()
                                        "
                                    >

                                </td>


                                {{-- =================================================
                                     ID
                                ================================================== --}}

                                <td class="id-cell">

                                    {{ $row->id }}

                                </td>


                                {{-- =================================================
                                     ARTICLE CODE
                                ================================================== --}}

                                <td class="article-code-cell">

                                    -

                                </td>


                                {{-- =================================================
                                     ARTICLE NR
                                ================================================== --}}

                                <td class="article-cell">

                                    {{ $article ?: '-' }}

                                </td>


                                {{-- =================================================
                                     STATUS
                                ================================================== --}}

                                <td class="status-cell">

                                    @if($isMaster)

                                        <span
                                            class="
                                                master-status
                                                sudah
                                            "
                                        >
                                            ✓ Sudah Master
                                        </span>

                                    @else

                                        <span
                                            class="
                                                master-status
                                                belum
                                            "
                                        >
                                            Belum
                                        </span>

                                    @endif

                                </td>


                                {{-- =================================================
                                     DESCRIPTION
                                ================================================== --}}

                                <td class="description-cell">

                                    {{ $description }}

                                </td>


                                {{-- =================================================
                                     SUB CATEGORY
                                ================================================== --}}

                                <td class="subcategory-cell">

                                    {{ $subCategory }}

                                </td>


                                {{-- =================================================
                                     COMPOSITION
                                ================================================== --}}

                                <td class="composition-cell">

                                    {{ $composition }}

                                </td>


                                {{-- =================================================
                                     FINISHING
                                ================================================== --}}

                                <td class="finishing-cell">

                                    {{ $finishing }}

                                </td>


                                {{-- =================================================
                                     ITEM DIMENSION
                                ================================================== --}}

                                <td class="dimension-cell">

                                    {{ $itemDimension ?: '-' }}

                                </td>


                                {{-- =================================================
                                     PACK DIMENSION
                                ================================================== --}}

                                <td class="dimension-cell">

                                    {{ $packDimension ?: '-' }}

                                </td>


                                {{-- =================================================
                                     CBM
                                ================================================== --}}

                                <td class="cbm-cell">

                                    {{ $detail['cbm'] ?? '-' }}

                                </td>


                                {{-- =================================================
                                     FOB
                                ================================================== --}}

                                <td class="price-cell">

                                    @if(
                                        isset(
                                            $detail[
                                                'fob_jakarta_in_usd'
                                            ]
                                        )
                                    )

                                        $
                                        {{ $detail[
                                            'fob_jakarta_in_usd'
                                        ] }}

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- =================================================
                                     PHOTO
                                ================================================== --}}

                                <td class="photo-cell">

                                    @if(
                                        !empty(
                                            $detail['photo']
                                        )
                                    )

                                        <img
                                            src="{{ $detail['photo'] }}"
                                            alt="Item"
                                            loading="lazy"
                                        >

                                    @else

                                        <span
                                            class="photo-empty"
                                        >
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- =================================================
                                     PO
                                ================================================== --}}

                                <td class="po-cell">

                                    <span
                                        class="po-number"
                                    >
                                        {{ $poNumber }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="15"
                                    class="empty-master"
                                >

                                    Belum ada data
                                    Detail PO.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                 PAGINATION
            ================================================== --}}

            @if($detailPo->hasPages())

                <div class="pagination-wrapper">

                    {{ $detailPo->links() }}

                </div>

            @endif

        </form>

    </div>

</div>


{{-- =========================================================
     DETAIL MODAL
========================================================= --}}

<div
    id="masterDetailModal"
    class="master-detail-modal-overlay"
    onclick="closeMasterDetail(event)"
>

    <div
        class="master-detail-modal-box"
        onclick="event.stopPropagation()"
    >


        {{-- =================================================
             HEADER
        ================================================== --}}

        <div class="master-detail-modal-header">

            <div>

                <div class="master-detail-kicker">
                    DETAIL PO ITEM
                </div>

                <h3
                    id="modalItemDescription"
                    class="master-detail-title"
                >
                    -
                </h3>

                <div
                    id="modalItemArticle"
                    class="master-detail-article"
                >
                    -
                </div>

            </div>


            <button
                type="button"
                class="master-detail-close"
                onclick="closeMasterDetail()"
            >
                &times;
            </button>

        </div>


        {{-- =================================================
             BODY
        ================================================== --}}

        <div class="master-detail-modal-body">


            {{-- =================================================
                 TOP
            ================================================== --}}

            <div class="master-detail-top">


                {{-- FOTO --}}

                <div class="master-detail-photo-area">

                    <div class="master-detail-photo-box">

                        <img
                            id="modalItemPhoto"
                            src=""
                            alt="Item"
                        >

                        <span
                            id="modalItemNoPhoto"
                        >
                            Tidak ada foto
                        </span>

                    </div>


                    <div
                        id="modalItemStatus"
                        class="master-detail-status"
                    >
                        -
                    </div>

                </div>


                {{-- INFORMASI --}}

                <div>

                    <div class="master-detail-section-title">
                        INFORMASI ITEM
                    </div>


                    <div class="master-detail-info-grid">


                        <div class="master-detail-info">

                            <span>
                                Article NR
                            </span>

                            <strong
                                id="modalArticleNr"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                Sub Category
                            </span>

                            <strong
                                id="modalSubCategory"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                Composition
                            </span>

                            <strong
                                id="modalComposition"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                Finishing
                            </span>

                            <strong
                                id="modalFinishing"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                Quantity
                            </span>

                            <strong
                                id="modalQty"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                CBM
                            </span>

                            <strong
                                id="modalCbm"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                Value USD
                            </span>

                            <strong
                                id="modalValueUsd"
                            >
                                -
                            </strong>

                        </div>


                        <div class="master-detail-info">

                            <span>
                                FOB Jakarta
                            </span>

                            <strong
                                id="modalFob"
                            >
                                -
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 DIMENSI
            ================================================== --}}

            <div
                class="master-detail-dimension-grid"
            >


                <div
                    class="
                        master-detail-dimension
                        item
                    "
                >

                    <span>
                        ITEM
                    </span>

                    <strong
                        id="modalItemDimension"
                    >
                        -
                    </strong>

                </div>


                <div
                    class="
                        master-detail-dimension
                        pack
                    "
                >

                    <span>
                        PACKING
                    </span>

                    <strong
                        id="modalPackDimension"
                    >
                        -
                    </strong>

                </div>


                <div
                    class="
                        master-detail-dimension
                        cbm
                    "
                >

                    <span>
                        TOTAL CBM
                    </span>

                    <strong
                        id="modalTotalCbm"
                    >
                        -
                    </strong>

                </div>

            </div>


            {{-- =================================================
                 REMARK
            ================================================== --}}

            <div
                class="
                    master-detail-remark-section
                "
            >

                <div
                    class="
                        master-detail-section-title
                    "
                >
                    REMARK
                </div>


                <div
                    id="modalRemark"
                    class="master-detail-remark"
                >
                    -
                </div>

            </div>


            {{-- =================================================
                 DETAIL ID
            ================================================== --}}

            <div
                class="master-detail-id"
            >

                DETAIL PO ID:

                <strong
                    id="modalDetailId"
                >
                    -
                </strong>

            </div>

        </div>


        {{-- =================================================
             FOOTER
        ================================================== --}}

        <div
            class="
                master-detail-modal-footer
            "
        >

            <button
                type="button"
                class="
                    master-detail-button-close
                "
                onclick="closeMasterDetail()"
            >
                Tutup
            </button>

        </div>

    </div>

</div>


<script>

/* =========================================================
   MASTER ITEM CHECKBOX
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const checkAll =
            document.getElementById(
                'checkAll'
            );

        const checks =
            Array.from(
                document.querySelectorAll(
                    '.item-check'
                )
            );

        const selectedCount =
            document.getElementById(
                'selectedCount'
            );

        const btnImport =
            document.getElementById(
                'btnImport'
            );

        const form =
            document.getElementById(
                'masterForm'
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE SELECTION
        |--------------------------------------------------------------------------
        */

        function updateSelection() {

            const selectable =
                checks.filter(
                    function (checkbox) {

                        return !checkbox.disabled;

                    }
                );


            const selected =
                selectable.filter(
                    function (checkbox) {

                        return checkbox.checked;

                    }
                );


            const count =
                selected.length;


            selectedCount.textContent =
                count + ' dipilih';


            btnImport.disabled =
                count === 0;


            /*
            |--------------------------------------------------------------------------
            | CHECK ALL
            |--------------------------------------------------------------------------
            */

            if (
                selectable.length > 0 &&
                count === selectable.length
            ) {

                checkAll.checked = true;

                checkAll.indeterminate =
                    false;

            }
            else if (count > 0) {

                checkAll.checked = false;

                checkAll.indeterminate =
                    true;

            }
            else {

                checkAll.checked = false;

                checkAll.indeterminate =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CHECK ALL
        |--------------------------------------------------------------------------
        */

        checkAll.addEventListener(
            'change',
            function () {

                checks.forEach(
                    function (checkbox) {

                        if (
                            checkbox.disabled
                        ) {

                            return;

                        }


                        checkbox.checked =
                            checkAll.checked;

                    }
                );


                updateSelection();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INDIVIDUAL
        |--------------------------------------------------------------------------
        */

        checks.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    updateSelection
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SUBMIT
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (event) {

                const selected =
                    checks.filter(
                        function (checkbox) {

                            return (
                                !checkbox.disabled &&
                                checkbox.checked
                            );

                        }
                    );


                if (
                    selected.length === 0
                ) {

                    event.preventDefault();

                    alert(
                        'Silakan pilih minimal satu item.'
                    );

                    return;

                }


                const confirmed =
                    confirm(
                        'Masukkan ' +
                        selected.length +
                        ' item terpilih ke Master Item?'
                    );


                if (!confirmed) {

                    event.preventDefault();

                }

            }
        );


        updateSelection();

    }
);


/* =========================================================
   OPEN MODAL
========================================================= */

function openMasterDetail(
    detailId
) {

    console.log(
        'OPEN MASTER DETAIL:',
        detailId
    );


    const modal =
        document.getElementById(
            'masterDetailModal'
        );


    if (!modal) {

        console.error(
            'masterDetailModal tidak ditemukan'
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | DATA JSON
    |--------------------------------------------------------------------------
    */

    const dataElement =
        document.getElementById(
            'master-data-' +
            detailId
        );


    if (!dataElement) {

        console.error(
            'Data item tidak ditemukan:',
            detailId
        );

        return;

    }


    let detail;


    try {

        detail =
            JSON.parse(
                dataElement.textContent
            );

    }
    catch (error) {

        console.error(
            'Gagal membaca JSON detail:',
            error
        );

        console.error(
            dataElement.textContent
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    setModalText(
        'modalItemDescription',
        detail.description ||
        '-'
    );


    setModalText(
        'modalItemArticle',
        'ARTICLE NR : ' +
        (
            detail.article_nr_ ||
            '-'
        )
    );


    /*
    |--------------------------------------------------------------------------
    | INFORMASI
    |--------------------------------------------------------------------------
    */

    setModalText(
        'modalArticleNr',
        detail.article_nr_ ||
        '-'
    );


    setModalText(
        'modalSubCategory',
        detail.sub_category ||
        '-'
    );


    setModalText(
        'modalComposition',
        detail.composition ||
        '-'
    );


    setModalText(
        'modalFinishing',
        detail.finishing ||
        '-'
    );


    setModalText(
        'modalQty',
        detail.qty ||
        '-'
    );


    setModalText(
        'modalCbm',
        detail.cbm ||
        '-'
    );


    setModalText(
        'modalValueUsd',
        detail.value_in_usd
            ? '$ ' +
              detail.value_in_usd
            : '-'
    );


    setModalText(
        'modalFob',
        detail.fob_jakarta_in_usd
            ? '$ ' +
              detail.fob_jakarta_in_usd
            : '-'
    );


    /*
    |--------------------------------------------------------------------------
    | DIMENSION
    |--------------------------------------------------------------------------
    */

    setModalText(
        'modalItemDimension',
        makeDimension([
            detail.item_d,
            detail.item_w,
            detail.item_h
        ])
    );


    setModalText(
        'modalPackDimension',
        makeDimension([
            detail.pack_d,
            detail.pack_w,
            detail.pack_h
        ])
    );


    setModalText(
        'modalTotalCbm',
        detail.total_cbm ||
        '-'
    );


    /*
    |--------------------------------------------------------------------------
    | REMARK
    |--------------------------------------------------------------------------
    */

    setModalText(
        'modalRemark',
        detail.remark ||
        '-'
    );


    /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

    setModalText(
        'modalDetailId',
        detailId
    );


    /*
    |--------------------------------------------------------------------------
    | PHOTO
    |--------------------------------------------------------------------------
    */

    const image =
        document.getElementById(
            'modalItemPhoto'
        );

    const noPhoto =
        document.getElementById(
            'modalItemNoPhoto'
        );


    if (
        detail.photo &&
        String(detail.photo).trim() !== ''
    ) {

        image.src =
            detail.photo;

        image.style.display =
            'block';

        noPhoto.style.display =
            'none';


        image.onerror =
            function () {

                image.style.display =
                    'none';

                noPhoto.style.display =
                    'inline';

            };

    }
    else {

        image.src = '';

        image.style.display =
            'none';

        noPhoto.style.display =
            'inline';

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    const row =
        document.querySelector(
            '.master-item-row[data-detail-id="' +
            detailId +
            '"]'
        );


    const status =
        document.getElementById(
            'modalItemStatus'
        );


    if (
        row &&
        row.classList.contains(
            'already-master-row'
        )
    ) {

        status.textContent =
            '✓ SUDAH MASTER ITEM';

        status.classList.add(
            'master'
        );

    }
    else {

        status.textContent =
            'BELUM MASUK MASTER ITEM';

        status.classList.remove(
            'master'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW MODAL
    |--------------------------------------------------------------------------
    */

    modal.classList.add(
        'active'
    );


    document.body.style.overflow =
        'hidden';

}


/* =========================================================
   CLOSE MODAL
========================================================= */

function closeMasterDetail(
    event
) {

    /*
    |--------------------------------------------------------------------------
    | KALAU KLIK BACKDROP
    |--------------------------------------------------------------------------
    */

    if (
        event &&
        event.target &&
        event.currentTarget &&
        event.target !==
        event.currentTarget
    ) {

        return;

    }


    const modal =
        document.getElementById(
            'masterDetailModal'
        );


    if (!modal) {

        return;

    }


    modal.classList.remove(
        'active'
    );


    document.body.style.overflow =
        '';

}


/* =========================================================
   ESC
========================================================= */

document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape'
        ) {

            closeMasterDetail();

        }

    }
);


/* =========================================================
   HELPER
========================================================= */

function setModalText(
    id,
    value
) {

    const element =
        document.getElementById(
            id
        );


    if (element) {

        element.textContent =
            value ?? '-';

    }

}


function makeDimension(
    values
) {

    const clean =
        values.filter(
            function (value) {

                return (
                    value !== null &&
                    value !== undefined &&
                    String(value).trim() !== ''
                );

            }
        );


    if (
        clean.length === 0
    ) {

        return '-';

    }


    return clean.join(
        ' × '
    );

}

</script>

@endsection