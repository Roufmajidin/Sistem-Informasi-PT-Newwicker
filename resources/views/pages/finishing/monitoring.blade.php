@extends('master.master')

@section('content')
    <div class="container-fluid py-3">

        {{-- ============================================================
        HEADER
    ============================================================ --}}
    @section('btn')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">Finishing Monitoring</h4>
                <div class="text-muted small">
                    Mutasi invoice dan pemotongan bahan SPK
                </div>
            </div>
        </div>
    @endsection

    {{-- ============================================================
        SIAPKAN DATA LEDGER
        - Kategori utama diambil dari metadata GROUP terlebih dahulu.
        - Jika group tidak memiliki kategori, kategori dicari dari row.
        - Jika satu invoice/group masuk kategori tertentu, SELURUH row
          invoice tersebut ikut masuk ke tab agar row INVOICE dan
          PEMOTONGAN SPK tetap berpasangan.
    ============================================================ --}}
    @php
        $tabs = [
            'semua' => 'SEMUA',
            'finishing' => 'FINISHING TOMO',
            'darto' => 'DARTO',
            'produksi' => 'PRODUKSI',
            'sample' => 'SAMPLE',
        ];

        $normalizeText = function ($value) {
            return strtoupper(trim((string) ($value ?? '')));
        };

        $detectCategory = function ($value) use ($normalizeText) {
            $text = $normalizeText($value);

            if ($text === '') {
                return null;
            }

            // TOMO / FINISHING masuk ke satu tab FINISHING TOMO.
            if (str_contains($text, 'TOMO') || str_contains($text, 'FINISHING')) {
                return 'finishing';
            }

            if (str_contains($text, 'DARTO')) {
                return 'darto';
            }

            if (str_contains($text, 'PRODUKSI')) {
                return 'produksi';
            }

            if (str_contains($text, 'SAMPLE') || str_contains($text, 'SAMPEL')) {
                return 'sample';
            }

            return null;
        };

        $getRowCategory = function ($row, $fallback = null) use ($detectCategory) {
            $fields = [
                $row['kategori'] ?? null,
                $row['kategori_invoice'] ?? null,
                $row['bagian'] ?? null,
                $row['category'] ?? null,
                $row['supplier'] ?? null,
                $row['name_sub'] ?? null,
                $row['sub'] ?? null,
                $row['source'] ?? null,
                $row['note_tambahan'] ?? null,
            ];

            foreach ($fields as $field) {
                $category = $detectCategory($field);
                if ($category !== null) {
                    return $category;
                }
            }

            return $fallback;
        };

        $groupedRows = [];
        foreach ($tabs as $tabKey => $tabLabel) {
            $groupedRows[$tabKey] = [];
        }

        /*
            |--------------------------------------------------------------------------
            | PROCESS SETIAP GROUP INVOICE
            |--------------------------------------------------------------------------
            */
        foreach ($ledger as $ledgerGroup) {
            $rawGroupRows = $ledgerGroup['rows'] ?? [];

            if ($rawGroupRows instanceof \Illuminate\Support\Collection) {
                $rawGroupRows = $rawGroupRows->all();
            }

            if (!is_array($rawGroupRows)) {
                $rawGroupRows = [];
            }

            /*
                | Kategori group adalah sumber paling kuat.
                | Ini penting karena row INVOICE sering tidak mempunyai
                | supplier/kategori sendiri (biasanya supplier = "-").
                */
            $groupCategory = $getRowCategory([
                'kategori' => $ledgerGroup['kategori'] ?? null,
                'kategori_invoice' => $ledgerGroup['kategori_invoice'] ?? null,
                'bagian' => $ledgerGroup['bagian'] ?? null,
                'category' => $ledgerGroup['category'] ?? null,
                'supplier' => $ledgerGroup['supplier'] ?? null,
                'name_sub' => $ledgerGroup['name_sub'] ?? null,
                'sub' => $ledgerGroup['sub'] ?? null,
                'source' => $ledgerGroup['source'] ?? null,
            ]);

            /*
                | Jika metadata group kosong, cari kategori dari seluruh row.
                | Jangan berhenti di row pertama karena row invoice bisa kosong.
                */
            if ($groupCategory === null) {
                foreach ($rawGroupRows as $rawRow) {
                    $foundCategory = $getRowCategory($rawRow);

                    if ($foundCategory !== null) {
                        $groupCategory = $foundCategory;
                        break;
                    }
                }
            }

            /*
                | Siapkan row final. Kategori group diwariskan ke row yang
                | belum memiliki kategori agar rendering dan JS tetap konsisten.
                */
            $groupRows = [];

            foreach ($rawGroupRows as $rawRow) {
                if (!is_array($rawRow)) {
                    continue;
                }

                $row = $rawRow;

                if (empty($row['kategori']) && $groupCategory !== null) {
                    $row['kategori'] = $groupCategory;
                }

                if (empty($row['kategori_invoice']) && $groupCategory !== null) {
                    $row['kategori_invoice'] = $groupCategory;
                }

                $groupRows[] = $row;
            }

            if (empty($groupRows)) {
                continue;
            }

            // SEMUA selalu mendapat seluruh row group.
            foreach ($groupRows as $row) {
                $groupedRows['semua'][] = $row;
            }

            /*
                | Group category menentukan tab.
                | SELURUH row group dimasukkan supaya invoice + pemotongan
                | tidak terpisah.
                */
            if ($groupCategory !== null && isset($groupedRows[$groupCategory])) {
                foreach ($groupRows as $row) {
                    $groupedRows[$groupCategory][] = $row;
                }
            }
        }

        /*
            |--------------------------------------------------------------------------
            | GROUP BERDASARKAN INVOICE
            |--------------------------------------------------------------------------
            */
        $invoiceGroups = [];

        foreach ($groupedRows as $tabKey => $rows) {
            $invoiceGroups[$tabKey] = [];

            foreach ($rows as $row) {
                $invoiceKey = trim((string) ($row['invoice'] ?? ''));

                if ($invoiceKey === '') {
                    $invoiceKey = '__NO_INVOICE__';
                }

                if (!isset($invoiceGroups[$tabKey][$invoiceKey])) {
                    $invoiceGroups[$tabKey][$invoiceKey] = [];
                }

                $invoiceGroups[$tabKey][$invoiceKey][] = $row;
            }
        }

        /*
            |--------------------------------------------------------------------------
            | TAB AKTIF DARI URL
            |--------------------------------------------------------------------------
            |
            | ?bagian=darto -> DARTO aktif
            | ?bagian=tomo  -> FINISHING TOMO aktif
            | ?bagian=sampel -> SAMPLE aktif
            | tanpa parameter -> SEMUA aktif
            */
        $requestedBagian = strtolower(trim((string) request('bagian', 'semua')));

        $bagianAliases = [
            'tomo' => 'finishing',
            'finishing' => 'finishing',
            'darto' => 'darto',
            'produksi' => 'produksi',
            'sample' => 'sample',
            'sampel' => 'sample',
            'semua' => 'semua',
        ];

        $activeTab = $bagianAliases[$requestedBagian] ?? 'semua';
    @endphp

    {{-- ============================================================
        TAB
    ============================================================ --}}
    <div class="card shadow-sm" style="margin-top:40px">

        <div class="card-header bg-white">

            <ul class="nav nav-tabs ledger-tabs" id="ledgerTabs" role="tablist">

                @foreach ($tabs as $tabKey => $tabLabel)
                    <li class="nav-item" role="presentation">

                        <button class="nav-link {{ $activeTab === $tabKey ? 'active' : '' }}"
                            id="{{ $tabKey }}-tab" data-tab="{{ $tabKey }}" type="button" role="tab"
                            aria-controls="tab-{{ $tabKey }}"
                            aria-selected="{{ $activeTab === $tabKey ? 'true' : 'false' }}">


                            {{ $tabLabel }}

                            <span class="badge bg-secondary ms-1">
                                {{ count($groupedRows[$tabKey]) }}
                            </span>

                        </button>

                    </li>
                @endforeach

            </ul>

        </div>


        <div class="card-body p-0">

            <div class="tab-content" id="ledgerTabContent">


                {{-- ====================================================
                    LOOP TAB
                ==================================================== --}}
                @foreach ($tabs as $tabKey => $tabLabel)
                    <div class="tab-pane fade {{ $activeTab === $tabKey ? 'show active' : '' }}"
                        id="tab-{{ $tabKey }}" role="tabpanel" aria-labelledby="{{ $tabKey }}-tab"
                        data-ledger-tab="{{ $tabKey }}">


                        @if (empty($invoiceGroups[$tabKey]))
                            <div class="text-center text-muted py-5">

                                <i class="fa fa-database fa-2x mb-2"></i>

                                <div>
                                    Belum ada data
                                    {{ $tabLabel }}.
                                </div>

                            </div>
                        @else
                            <div class="table-responsive">

                                <table class="table table-bordered table-hover align-middle mb-0 ledger-table">

                                    <thead class="table-dark">

                                        <tr>

                                            <th style="width:60px" class="text-center">
                                                NO
                                            </th>

                                            <th style="width:120px">
                                                TANGGAL
                                            </th>

                                            <th style="width:160px">
                                                DESCRIPTION
                                            </th>

                                            <th style="min-width:250px">
                                                SUB
                                            </th>

                                            <th style="width:130px">
                                                SUPPLIER
                                            </th>

                                            <th style="width:180px" class="text-end">
                                                PEMBELIAN
                                            </th>

                                            <th style="width:180px" class="text-end">
                                                POTONGAN SPK
                                            </th>

                                            <th style="width:190px" class="text-end">
                                                SALDO
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        {{-- =================================================
                                            LOOP INVOICE
                                        ================================================= --}}
                                        @foreach ($invoiceGroups[$tabKey] as $invoice => $invoiceRows)
                                            @php

                                                $invoiceFirst = $invoiceRows[0] ?? [];

                                                $invoiceNumber = $invoiceFirst['invoice'] ?? '';

                                                $invoiceTanggal = $invoiceFirst['tanggal'] ?? '';

                                                $invoiceSupplier = $invoiceFirst['supplier'] ?? '';

                                            @endphp


                                            {{-- =============================================
                                                LOOP DATA DALAM INVOICE
                                            ============================================== --}}
                                            @foreach ($invoiceRows as $rowIndex => $row)
                                                @php

                                                    /*
                                                    |--------------------------------------------------------------------------
                                                    | Detail bahan
                                                    |--------------------------------------------------------------------------
                                                    */

                                                    $detailBahan = $row['detail_bahan'] ?? [];

                                                    if (is_string($detailBahan)) {
                                                        $decoded = json_decode($detailBahan, true);

                                                        if (json_last_error() === JSON_ERROR_NONE) {
                                                            $detailBahan = $decoded;
                                                        } else {
                                                            $detailBahan = [];
                                                        }
                                                    }

                                                    if (!is_array($detailBahan)) {
                                                        $detailBahan = [];
                                                    }

                                                    /*
                                                    |--------------------------------------------------------------------------
                                                    | Modal data
                                                    |--------------------------------------------------------------------------
                                                    */

                                                    $modalData = [
                                                        'invoice' => $row['invoice'] ?? '',

                                                        'tanggal' => $row['tanggal'] ?? '',

                                                        'source' => $row['source'] ?? '',

                                                        'sub' => $row['sub'] ?? '',

                                                        'supplier' => $row['supplier'] ?? '',

                                                        'detail_bahan' => $detailBahan,
                                                    ];

                                                @endphp


                                                {{-- =========================================
                                                    BARIS LEDGER
                                                ========================================== --}}
                                                <tr class="ledger-row" data-tab="{{ $tabKey }}"
                                                    data-invoice="{{ $row['invoice'] ?? '' }}"
                                                    data-tanggal="{{ $row['tanggal'] ?? '' }}"
                                                    data-source="{{ $row['source'] ?? '' }}"
                                                    data-sub="{{ $row['sub'] ?? '' }}"
                                                    data-supplier="{{ $row['supplier'] ?? '' }}">


                                                    {{-- NO --}}
                                                    <td class="text-center">

                                                        @if ($rowIndex === 0)
                                                            {{ $loop->parent->iteration }}
                                                        @endif

                                                    </td>


                                                    {{-- TANGGAL --}}
                                                    <td>

                                                        @if (!empty($row['tanggal']))
                                                            {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                                        @else
                                                            -
                                                        @endif

                                                    </td>


                                                    {{-- DESCRIPTION --}}
                                                    <td>

                                                        @if (($row['type'] ?? '') === 'invoice')
                                                            <div class="invoice-click-wrapper">

                                                                <span class="badge bg-primary invoice-click-trigger"
                                                                    role="button">

                                                                    INVOICE

                                                                    @if (($row['source'] ?? '') === 'inv_lama')
                                                                        <span class="ms-1">
                                                                            LAMA
                                                                        </span>
                                                                    @endif

                                                                </span>

                                                            </div>
                                                        @else
                                                            <span class="text-muted">

                                                                Pemotongan bahan

                                                            </span>
                                                        @endif

                                                    </td>


                                                    {{-- SUB --}}
                                                    {{-- SUB --}}
                                                    <td
                                                        class="{{ ($row['type'] ?? '') !== 'invoice' ? 'spk-sub-cell' : 'invoice-sub-cell' }}">

                                                        <div
                                                            class="{{ ($row['type'] ?? '') !== 'invoice' ? 'spk-sub-content' : 'invoice-sub-content' }}">

                                                            <strong class="sub-name"
                                                                style="color:#212529 !important; opacity:1 !important;">
                                                                {{ $row['sub'] ?? '-' }}
                                                            </strong>

                                                            @if (!empty($row['note_tambahan']))
                                                                <div class="small text-muted mt-1 sub-note">
                                                                    {{ $row['note_tambahan'] }}
                                                                </div>
                                                            @endif

                                                            @if (($row['source'] ?? '') === 'spk_lama' && !empty($row['po']))
                                                                <div class="small text-muted mt-1 sub-note">
                                                                    PO: {{ $row['po'] }}
                                                                </div>
                                                            @endif

                                                        </div>

                                                    </td>


                                                    {{-- SUPPLIER --}}
                                                    <td>
                                                        @if (!empty($row['supplier']))
                                                            <span class="supplier-name">
                                                                {{ $row['supplier'] }}
                                                            </span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>


                                                    {{-- PEMBELIAN --}}
                                                    <td class="text-end">

                                                        @if (($row['debet'] ?? 0) > 0)
                                                            <strong>

                                                                Rp
                                                                {{ number_format($row['debet'], 0, ',', '.') }}

                                                            </strong>
                                                        @else
                                                            <span class="text-muted">

                                                                -

                                                            </span>
                                                        @endif

                                                    </td>


                                                    {{-- POTONGAN SPK --}}
                                                    <td class="text-end">

                                                        @if (($row['kredit'] ?? 0) > 0)
                                                            <span class="text-danger">

                                                                Rp
                                                                {{ number_format($row['kredit'], 0, ',', '.') }}

                                                            </span>
                                                        @else
                                                            <span class="text-muted">

                                                                -

                                                            </span>
                                                        @endif

                                                    </td>


                                                    {{-- SALDO --}}
                                                    <td class="text-end">

                                                        <strong>

                                                            Rp
                                                            {{ number_format($row['saldo'] ?? 0, 0, ',', '.') }}

                                                        </strong>

                                                    </td>

                                                </tr>


                                                {{-- =================================================
                                                    DETAIL BAHAN
                                                ================================================== --}}
                                                @if (($row['type'] ?? '') === 'invoice' && !empty($detailBahan))
                                                    <tr class="material-detail-row" data-tab="{{ $tabKey }}">

                                                        <td colspan="8" class="p-0">

                                                            <div class="material-detail-box">

                                                                <table class="table table-sm table-bordered mb-0">

                                                                    <thead>

                                                                        <tr>

                                                                            <th style="width:50px">
                                                                                #
                                                                            </th>

                                                                            <th>
                                                                                Jenis Bahan
                                                                            </th>

                                                                            <th class="text-end">
                                                                                Qty
                                                                            </th>

                                                                            <th>
                                                                                Satuan
                                                                            </th>

                                                                            <th class="text-end">
                                                                                Harga
                                                                            </th>

                                                                            <th class="text-end">
                                                                                Total
                                                                            </th>

                                                                        </tr>

                                                                    </thead>


                                                                    <tbody>

                                                                        @php

                                                                            $grandTotalBahan = 0;

                                                                        @endphp


                                                                        @foreach ($detailBahan as $index => $bahan)
                                                                            @php

                                                                                $totalBahan =
                                                                                    (float) ($bahan['total'] ?? 0);

                                                                                $grandTotalBahan += $totalBahan;
                                                                            @endphp


                                                                            <tr>

                                                                                <td>

                                                                                    {{ $index + 1 }}

                                                                                </td>

                                                                                <td>

                                                                                    {{ $bahan['jenis'] ?? '-' }}

                                                                                </td>

                                                                                <td class="text-end">

                                                                                    {{ $bahan['qty'] ?? '-' }}

                                                                                </td>

                                                                                <td>

                                                                                    {{ $bahan['satuan'] ?? '-' }}

                                                                                </td>

                                                                                <td class="text-end">

                                                                                    Rp
                                                                                    {{ number_format($bahan['harga'] ?? 0, 0, ',', '.') }}

                                                                                </td>

                                                                                <td class="text-end">

                                                                                    Rp
                                                                                    {{ number_format($totalBahan, 0, ',', '.') }}

                                                                                </td>

                                                                            </tr>
                                                                        @endforeach

                                                                    </tbody>


                                                                    <tfoot>

                                                                        <tr>

                                                                            <th colspan="5" class="text-end">

                                                                                GRAND TOTAL

                                                                            </th>

                                                                            <th class="text-end">

                                                                                Rp
                                                                                {{ number_format($grandTotalBahan, 0, ',', '.') }}

                                                                            </th>

                                                                        </tr>

                                                                    </tfoot>

                                                                </table>

                                                            </div>

                                                        </td>

                                                    </tr>
                                                @endif
                                            @endforeach


                                            {{-- =================================================
                                                GARIS AKHIR SETIAP INVOICE
                                            ================================================== --}}
                                            <tr class="invoice-separator">

                                                <td colspan="8">
                                                </td>

                                            </tr>
                                        @endforeach


                                    </tbody>

                                </table>

                            </div>
                        @endif

                    </div>
                @endforeach

            </div>

        </div>

    </div>

</div>



{{-- ================================================================
    MODAL IMPORT INVOICE LAMA
================================================================ --}}

<div class="modal fade" id="modalInvoiceLama" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content">


            {{-- HEADER --}}
            <div class="modal-header">

                <div>

                    <h5 class="modal-title mb-1">

                        Input potong SPK

                    </h5>

                    <div class="small text-muted">

                        Invoice:

                        <strong id="invoiceLamaPreview">
                            -
                        </strong>

                    </div>

                </div>


                <button type="button" class="btn-close" id="btnCloseInvoiceLama">
                </button>

            </div>


            {{-- BODY --}}
            <div class="modal-body">


                <div class="alert alert-info small">

                    <strong>
                        Cara penggunaan:
                    </strong>

                    <ol class="mb-0 mt-2">

                        <li>
                            Copy data dari Excel.
                        </li>

                        <li>
                            Paste ke kotak di bawah.
                        </li>

                        <li>
                            Preview akan muncul otomatis.
                        </li>

                        <li>
                            Nilai potongan SPK diambil dari
                            kolom
                            <strong>
                                NILAI POTONGAN
                            </strong>.
                        </li>

                    </ol>

                </div>


                {{-- TEXTAREA --}}
                <div class="mb-3">

                    <label class="form-label fw-bold">

                        Paste dari Excel

                    </label>

                    <textarea id="invoiceLamaPaste" class="form-control" rows="8" placeholder="Paste data dari Excel di sini..."
                        style="
                            font-family:monospace;
                            font-size:12px;
                            white-space:pre;
                        "></textarea>

                </div>


                {{-- PREVIEW HEADER --}}
                <div class="d-flex justify-content-between align-items-center mb-2">

                    <strong>
                        Preview Data
                    </strong>

                    <span id="invoiceLamaPreviewCount" class="badge bg-secondary">

                        0 baris

                    </span>

                </div>


                {{-- PREVIEW --}}
                <div class="table-responsive" style="max-height:400px;">

                    <table class="table table-bordered table-sm table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    SUPPLIER
                                </th>

                                <th>
                                    TANGGAL
                                </th>

                                <th>
                                    INVOICE
                                </th>

                                <th>
                                    NAMA BAHAN
                                </th>

                                <th class="text-end">
                                    QTY
                                </th>

                                <th>
                                    SATUAN
                                </th>

                                <th class="text-end">
                                    HARGA
                                </th>

                                <th class="text-end">
                                    TOTAL
                                </th>

                                <th>
                                    SPK
                                </th>

                                <th class="text-end">
                                    NILAI POTONGAN
                                </th>

                            </tr>

                        </thead>


                        <tbody id="invoiceLamaPreviewBody">

                            <tr>

                                <td colspan="10" class="text-center text-muted py-4">

                                    Belum ada data.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <input type="hidden" id="invoiceLamaNoInvoice">

                <input type="hidden" id="invoiceLamaTanggal">

            </div>


            {{-- FOOTER --}}
            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" id="btnCancelInvoiceLama">

                    Batal

                </button>


                <button type="button" class="btn btn-primary" id="btnSaveInvoiceLama">

                    Simpan

                </button>

            </div>

        </div>

    </div>

</div>



{{-- ================================================================
    STYLE
================================================================ --}}
<style>
    /* =========================================================
   TAB MONITORING
   ========================================================= */

    .ledger-tabs {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        margin: 0;
        padding: 8px 10px 0;
        border-bottom: 2px solid #d6dbe1;
        background: #f8f9fb;
    }

    /* TAB */
    .ledger-tabs .nav-link {
        position: relative;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;

        min-height: 42px;
        padding: 9px 18px;

        border: 1px solid transparent !important;
        border-bottom: 0 !important;
        border-radius: 8px 8px 0 0 !important;

        background: transparent !important;

        color: #495057 !important;

        font-size: 13px;
        font-weight: 600;

        cursor: pointer;

        transition:
            background-color .18s ease,
            color .18s ease,
            border-color .18s ease,
            box-shadow .18s ease,
            transform .18s ease;
    }

    /* HOVER */
    .ledger-tabs .nav-link:hover {
        color: #0d6efd !important;

        background: #eef5ff !important;

        border-color: #d5e5fa !important;

        transform: translateY(-1px);
    }

    /* ACTIVE */
    .ledger-tabs .nav-link.active {
        color: #0d6efd !important;

        background: #ffffff !important;

        border-color: #cfd6df !important;

        border-bottom-color: #ffffff !important;

        font-weight: 700;

        box-shadow:
            0 -2px 6px rgba(0, 0, 0, .04),
            0 2px 5px rgba(0, 0, 0, .04);

        z-index: 2;
    }

    /*
|--------------------------------------------------------------------------
| GARIS ACTIVE
|--------------------------------------------------------------------------
*/

    .ledger-tabs .nav-link.active::after {
        content: '';

        position: absolute;

        left: 10px;
        right: 10px;
        bottom: -2px;

        height: 3px;

        background: #0d6efd;

        border-radius: 3px 3px 0 0;
    }

    /* BADGE */
    .ledger-tabs .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-width: 20px;
        height: 20px;

        padding: 0 6px;

        border-radius: 10px;

        font-size: 10px;
        line-height: 20px;
        font-weight: 700;

        color: #495057 !important;

        background: #e3e7eb !important;

        transition:
            background-color .18s ease,
            color .18s ease;
    }

    /* BADGE ACTIVE */
    .ledger-tabs .nav-link.active .badge {
        color: #ffffff !important;
        background: #0d6efd !important;
    }

    /* BADGE HOVER */
    .ledger-tabs .nav-link:hover .badge {
        color: #0d6efd !important;
        background: #dbeaff !important;
    }

    /* TAB ACTIVE YANG PUNYA 0 TETAP TERLIHAT */
    .ledger-tabs .nav-link.active .badge {
        opacity: 1 !important;
    }

    /* =========================================================
           SUB INVOICE
           ========================================================= */

    .invoice-sub-cell,
    .invoice-sub-content {
        color: #212529 !important;
    }

    .invoice-sub-content .sub-name {
        color: #212529 !important;
    }


    /* =========================================================
           SUB SPK - INDENT
           ========================================================= */

    .spk-sub-cell {
        color: #212529 !important;
    }

    .spk-sub-content {
        margin-left: 45px;
        padding-left: 15px;
        color: #212529 !important;
        border-left: 3px solid #dee2e6;
    }

    .spk-sub-content .sub-name {
        display: block;
        color: #212529 !important;
        font-weight: 700;
    }

    .sub-name {
        color: #212529 !important;
        font-weight: 700;
    }

    .ledger-table td .sub-name {
        color: #212529 !important;
        font-weight: 700;
    }

    .ledger-table tbody tr.ledger-row td:nth-child(4),
    .ledger-table tbody tr.ledger-row td:nth-child(4) strong {
        color: #212529 !important;
    }

    .spk-sub-content .sub-note {
        color: #888 !important;
    }

    spk-indent {
        margin-left: 25px;
        padding-left: 12px;
    }

    .spk-indent {
        margin-right: 25px;
        padding-left: 12px;
        border-left: 3px solid #dee2e6;
    }

    /*
                    |--------------------------------------------------------------------------
                    | TAB
                    |--------------------------------------------------------------------------
                    */

    .ledger-tabs {
        margin: 0;
        padding: 0 6px;
        border-bottom: 1px solid #dee2e6;
        gap: 4px;
    }


    .ledger-tabs .nav-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #495057;
        padding: 11px 16px 10px;
        border: 0 !important;
        border-bottom: 3px solid transparent !important;
        border-radius: 0 !important;
        background: transparent !important;
        transition: color .15s ease, background-color .15s ease, border-color .15s ease;
    }


    .ledger-tabs .nav-link:hover {

        color: #0d6efd;
        background: #f5f9ff;
        border-bottom-color: #b8d7ff;

    }


    .ledger-tabs .nav-link.active {

        color: #0d6efd;
        font-weight: 700;
        background: #fff;
        border-bottom-color: #0d6efd;

    }


    .ledger-tabs .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9px;
        font-size: 10px;
        line-height: 18px;
        font-weight: 700;
        color: #fff !important;
        background: #6c757d !important;
        opacity: 1 !important;
    }


    .ledger-tabs .nav-link.active .badge {
        color: #fff !important;
        background-color: #0d6efd !important;
    }

    .ledger-tabs .nav-link:not(.active):hover .badge {
        background-color: #495057 !important;
    }


    /*
                    |--------------------------------------------------------------------------
                    | TABLE
                    |--------------------------------------------------------------------------
                    */

    .ledger-table {

        font-size: 13px;

    }


    .ledger-table th {

        white-space: nowrap;

    }


    .ledger-table td {

        vertical-align: middle;

    }


    /*
                    |--------------------------------------------------------------------------
                    | ROW
                    |--------------------------------------------------------------------------
                    */

    .ledger-row {

        cursor: pointer;
        transition: background-color .15s ease;

    }


    .ledger-row:hover {

        background-color: #f5f9ff !important;

    }


    .ledger-row:focus-within {

        background-color: #f5f9ff !important;

    }


    /*
                    |--------------------------------------------------------------------------
                    | DETAIL BAHAN
                    |--------------------------------------------------------------------------
                    */

    .material-detail-row {

        display: none;

    }


    /*
                     * Detail muncul saat row utama di-hover.
                     * Detail juga tetap tampil ketika mouse berpindah
                     * dari row utama ke area detail.
                     */
    .ledger-row:hover+.material-detail-row,
    .material-detail-row:hover,
    .material-detail-row.is-visible {

        display: table-row;

    }


    .material-detail-row>td {

        padding: 0 !important;

        border-top: 0 !important;

    }


    .material-detail-box {

        background: #f8f9fa;

        padding: 12px 20px;

        border-left: 4px solid #0d6efd;

    }


    .material-detail-box table {

        background: #fff;

        font-size: 12px;

    }


    .material-detail-box thead th {

        background: #2f4054 !important;

        color: #fff !important;

        white-space: nowrap;

        border-color: #2f4054 !important;

    }


    .material-detail-box tfoot th {

        background: #eef1f5 !important;

        color: #212529 !important;

    }


    .material-detail-box td,
    .material-detail-box th {

        padding: 7px 10px;

    }


    /*
                    |--------------------------------------------------------------------------
                    | GARIS AKHIR INVOICE
                    |--------------------------------------------------------------------------
                    */

    .invoice-separator td {

        height: 16px;

        padding: 0 !important;

        border-top: 3px solid #343a40 !important;

        border-bottom: 0 !important;

        border-left: 0 !important;

        border-right: 0 !important;

        background: #fff;

    }


    /*
                    |--------------------------------------------------------------------------
                    | INVOICE BADGE
                    |--------------------------------------------------------------------------
                    */

    .invoice-click-trigger {

        cursor: pointer;

        user-select: none;

    }


    .invoice-click-trigger:hover {

        opacity: .85;

    }


    /*
                    |--------------------------------------------------------------------------
                    | MODAL
                    |--------------------------------------------------------------------------
                    */

    #modalInvoiceLama .modal-dialog {

        max-width: 1200px;

    }


    #modalInvoiceLama textarea {

        font-family: monospace;

        font-size: 12px;

    }


    /*
                    |--------------------------------------------------------------------------
                    | PREVIEW
                    |--------------------------------------------------------------------------
                    */

    #invoiceLamaPreviewBody {

        font-size: 12px;

    }


    #invoiceLamaPreviewBody td {

        padding: 6px 8px;

    }
</style>



{{-- ================================================================
    SCRIPT
================================================================ --}}
<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {


            /*
            |--------------------------------------------------------------------------
            | ELEMENT
            |--------------------------------------------------------------------------
            */

            const modal =
                document.getElementById(
                    'modalInvoiceLama'
                );


            const pasteArea =
                document.getElementById(
                    'invoiceLamaPaste'
                );


            const previewBody =
                document.getElementById(
                    'invoiceLamaPreviewBody'
                );


            const previewCount =
                document.getElementById(
                    'invoiceLamaPreviewCount'
                );


            const invoicePreview =
                document.getElementById(
                    'invoiceLamaPreview'
                );


            const invoiceInput =
                document.getElementById(
                    'invoiceLamaNoInvoice'
                );


            const tanggalInput =
                document.getElementById(
                    'invoiceLamaTanggal'
                );


            const saveButton =
                document.getElementById(
                    'btnSaveInvoiceLama'
                );


            const closeButton =
                document.getElementById(
                    'btnCloseInvoiceLama'
                );


            const cancelButton =
                document.getElementById(
                    'btnCancelInvoiceLama'
                );


            let parsedRows = [];

            let activeInvoice = '';

            let activeTanggal = '';


            /*
            |--------------------------------------------------------------------------
            | BACKDROP
            |--------------------------------------------------------------------------
            */

            function createBackdrop() {

                removeBackdrop();

                const backdrop =
                    document.createElement(
                        'div'
                    );

                backdrop.id =
                    'invoiceLamaBackdrop';

                backdrop.className =
                    'modal-backdrop fade show';

                backdrop.addEventListener(
                    'click',
                    closeInvoiceModal
                );

                document.body.appendChild(
                    backdrop
                );

            }


            function removeBackdrop() {

                const backdrop =
                    document.getElementById(
                        'invoiceLamaBackdrop'
                    );

                if (backdrop) {

                    backdrop.remove();

                }

            }


            /*
            |--------------------------------------------------------------------------
            | OPEN MODAL
            |--------------------------------------------------------------------------
            */

            function openInvoiceModal(
                invoice,
                tanggal
            ) {

                activeInvoice =
                    invoice || '';

                activeTanggal =
                    tanggal || '';


                invoiceInput.value =
                    activeInvoice;


                tanggalInput.value =
                    activeTanggal;


                invoicePreview.textContent =
                    activeInvoice || '-';


                pasteArea.value = '';

                parsedRows = [];


                previewCount.textContent =
                    '0 baris';


                previewBody.innerHTML = `

                <tr>

                    <td
                        colspan="10"
                        class="text-center text-muted py-4">

                        Belum ada data.

                    </td>

                </tr>

            `;


                modal.classList.add('show');

                modal.style.display =
                    'block';

                modal.removeAttribute(
                    'aria-hidden'
                );

                modal.setAttribute(
                    'aria-modal',
                    'true'
                );


                document.body.classList.add(
                    'modal-open'
                );


                createBackdrop();


                setTimeout(
                    function() {

                        pasteArea.focus();

                    },
                    150
                );

            }


            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */

            function closeInvoiceModal() {

                modal.classList.remove(
                    'show'
                );

                modal.style.display =
                    'none';

                modal.setAttribute(
                    'aria-hidden',
                    'true'
                );

                modal.removeAttribute(
                    'aria-modal'
                );


                document.body.classList.remove(
                    'modal-open'
                );


                removeBackdrop();

            }


            if (closeButton) {

                closeButton.addEventListener(
                    'click',
                    closeInvoiceModal
                );

            }


            if (cancelButton) {

                cancelButton.addEventListener(
                    'click',
                    closeInvoiceModal
                );

            }


            document.addEventListener(
                'keydown',
                function(event) {

                    if (
                        modal &&
                        event.key === 'Escape' &&
                        modal.classList.contains(
                            'show'
                        )
                    ) {

                        closeInvoiceModal();

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | PARSE MONEY
            |--------------------------------------------------------------------------
            */

            function parseMoney(value) {

                if (
                    value === null ||
                    value === undefined
                ) {

                    return 0;

                }


                let text =
                    String(value)
                    .trim();


                if (!text) {

                    return 0;

                }


                text =
                    text
                    .replace(/Rp/gi, '')
                    .replace(/\s/g, '')
                    .replace(/\./g, '')
                    .replace(/,/g, '')
                    .replace(
                        /[^0-9\-]/g,
                        ''
                    );


                return Number(text) || 0;

            }


            /*
            |--------------------------------------------------------------------------
            | FORMAT MONEY
            |--------------------------------------------------------------------------
            */

            function formatMoney(value) {

                const number =
                    parseMoney(value);


                if (!number) {

                    return '-';

                }


                return 'Rp ' +
                    number.toLocaleString(
                        'id-ID'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | FORMAT SPK
            |--------------------------------------------------------------------------
            */

            function formatSpk(spk) {

                if (!spk) {

                    return '';

                }


                let text =
                    String(spk)
                    .trim()
                    .replace(/\r/g, '')
                    .replace(/\n/g, ' ')
                    .replace(
                        /\s+/g,
                        ' '
                    );


                return text.trim();

            }


            /*
            |--------------------------------------------------------------------------
            | GET PO
            |--------------------------------------------------------------------------
            */

            function getPo(spk) {

                if (!spk) {

                    return '';

                }


                const match =
                    spk.match(
                        /NW\s*(\d{2})\s*-\s*(\d{1,3})/i
                    );


                if (!match) {

                    return '';

                }


                return (
                    match[1] +
                    ' - ' +
                    match[2]
                );

            }


            /*
            |--------------------------------------------------------------------------
            | FORMAT DATE
            |--------------------------------------------------------------------------
            */

            function formatDate(value) {

                if (!value) {

                    return '';

                }


                let text =
                    String(value)
                    .trim();


                const parts =
                    text.split('/');


                if (
                    parts.length === 3
                ) {

                    let day =
                        parts[0];

                    let month =
                        parts[1];

                    let year =
                        parts[2];


                    if (
                        year.length === 4
                    ) {

                        return (
                            year +
                            '-' +
                            month.padStart(
                                2,
                                '0'
                            ) +
                            '-' +
                            day.padStart(
                                2,
                                '0'
                            )
                        );

                    }

                }


                return text;

            }


            /*
            |--------------------------------------------------------------------------
            | PARSE EXCEL
            |--------------------------------------------------------------------------
            */

            function parseExcel(text) {

                const lines =
                    text
                    .replace(/\r/g, '')
                    .split('\n');


                const rows = [];


                lines.forEach(
                    function(line) {

                        if (
                            !line.trim()
                        ) {

                            return;

                        }


                        let columns =
                            line.split('\t');


                        columns =
                            columns.map(
                                function(value) {

                                    return String(
                                            value || ''
                                        )
                                        .replace(
                                            /\u00A0/g,
                                            ' '
                                        )
                                        .trim();

                                }
                            );


                        if (
                            columns.length < 9
                        ) {

                            return;

                        }


                        const supplier =
                            columns[0] || '';


                        const tanggal =
                            columns[1] || '';


                        const invoice =
                            columns[2] || '';


                        const jenis =
                            columns[3] || '';


                        const qty =
                            columns[4] || '';


                        const satuan =
                            columns[5] || '';


                        const harga =
                            columns[6] || '';


                        const total =
                            columns[7] || '';


                        const spk =
                            columns[8] || '';


                        let nilaiPotongan =
                            columns[9] || '';


                        if (
                            !nilaiPotongan &&
                            columns.length > 10
                        ) {

                            for (
                                let x = 10; x < columns.length; x++
                            ) {

                                if (
                                    String(
                                        columns[x] || ''
                                    ).trim()
                                ) {

                                    nilaiPotongan =
                                        columns[x];

                                    break;

                                }

                            }

                        }


                        const formattedSpk =
                            formatSpk(spk);


                        rows.push({

                            supplier: supplier,

                            tanggal: tanggal,

                            invoice: invoice,

                            jenis: jenis,

                            qty: qty,

                            satuan: satuan,

                            harga: parseMoney(
                                harga
                            ),

                            total: parseMoney(
                                total
                            ),

                            spk: formattedSpk,

                            po: getPo(
                                formattedSpk
                            ),

                            nilai_potongan: parseMoney(
                                nilaiPotongan
                            )

                        });

                    }
                );


                return rows;

            }


            /*
            |--------------------------------------------------------------------------
            | ESCAPE HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(value) {

                return String(
                        value ?? ''
                    )
                    .replace(
                        /&/g,
                        '&amp;'
                    )
                    .replace(
                        /</g,
                        '&lt;'
                    )
                    .replace(
                        />/g,
                        '&gt;'
                    )
                    .replace(
                        /"/g,
                        '&quot;'
                    )
                    .replace(
                        /'/g,
                        '&#039;'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | RENDER PREVIEW
            |--------------------------------------------------------------------------
            */

            function renderPreview() {

                previewBody.innerHTML = '';


                if (
                    !parsedRows.length
                ) {

                    previewCount.textContent =
                        '0 baris';


                    previewBody.innerHTML = `

                    <tr>

                        <td
                            colspan="10"
                            class="text-center text-muted py-4">

                            Tidak ada data yang terbaca.

                        </td>

                    </tr>

                `;

                    return;

                }


                previewCount.textContent =
                    parsedRows.length +
                    ' baris';


                parsedRows.forEach(
                    function(row) {

                        const tr =
                            document.createElement(
                                'tr'
                            );


                        tr.innerHTML = `

                        <td>
                            ${escapeHtml(
                                row.supplier
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.tanggal
                            )}
                        </td>

                        <td>
                            <strong>
                                ${escapeHtml(
                                    row.invoice
                                )}
                            </strong>
                        </td>

                        <td>
                            ${escapeHtml(
                                row.jenis
                            )}
                        </td>

                        <td class="text-end">
                            ${escapeHtml(
                                row.qty
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.satuan
                            )}
                        </td>

                        <td class="text-end">
                            ${formatMoney(
                                row.harga
                            )}
                        </td>

                        <td class="text-end">
                            ${formatMoney(
                                row.total
                            )}
                        </td>

                     <td>
    <div class="spk-indent">
        <strong>
            ${escapeHtml(
                row.spk || '-'
            )}
        </strong>
    </div>
</td>

                        <td class="text-end">

                            <strong>
                                ${formatMoney(
                                    row.nilai_potongan
                                )}
                            </strong>

                        </td>

                    `;


                        previewBody.appendChild(
                            tr
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | INPUT PASTE
            |--------------------------------------------------------------------------
            */

            pasteArea.addEventListener(
                'input',
                function() {

                    parsedRows =
                        parseExcel(
                            pasteArea.value
                        );


                    if (
                        !activeInvoice &&
                        parsedRows.length
                    ) {

                        activeInvoice =
                            parsedRows[0].invoice;


                        invoiceInput.value =
                            activeInvoice;


                        invoicePreview.textContent =
                            activeInvoice || '-';

                    }


                    if (
                        !activeTanggal &&
                        parsedRows.length
                    ) {

                        activeTanggal =
                            formatDate(
                                parsedRows[0].tanggal
                            );


                        tanggalInput.value =
                            activeTanggal;

                    }


                    renderPreview();

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CARI INVOICE DARI ROW
            |--------------------------------------------------------------------------
            */

            function findInvoiceFromRow(row) {

                if (
                    row.dataset.invoice
                ) {

                    return row.dataset.invoice;

                }


                const match =
                    (
                        row.innerText || ''
                    )
                    .match(
                        /\bKAJ[A-Z0-9]+\b/i
                    );


                return match ?
                    match[0] :
                    '';

            }


            /*
            |--------------------------------------------------------------------------
            | CLICK ROW
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | TAB STATE
            |--------------------------------------------------------------------------
            | URL adalah sumber kebenaran.
            | Tidak memakai sessionStorage karena dapat membuat ?bagian=darto
            | kalah oleh tab terakhir yang tersimpan.
            |--------------------------------------------------------------------------
            */

            const ledgerTabs =
                document.getElementById('ledgerTabs');

            const ledgerTabContent =
                document.getElementById('ledgerTabContent');

            function activateLedgerTab(tabKey, updateUrl = true) {

                if (!ledgerTabs || !ledgerTabContent) {
                    return;
                }

                const buttons =
                    ledgerTabs.querySelectorAll('.nav-link[data-tab]');

                const panes =
                    ledgerTabContent.querySelectorAll('.tab-pane[data-ledger-tab]');

                buttons.forEach(function(button) {

                    const active =
                        button.dataset.tab === tabKey;

                    button.classList.toggle('active', active);

                    button.setAttribute(
                        'aria-selected',
                        active ? 'true' : 'false'
                    );

                    const badge =
                        button.querySelector('.badge');

                    if (badge) {
                        badge.classList.toggle('bg-primary', active);
                        badge.classList.toggle('bg-secondary', !active);
                    }
                });

                panes.forEach(function(pane) {

                    const active =
                        pane.dataset.ledgerTab === tabKey;

                    pane.classList.toggle('show', active);
                    pane.classList.toggle('active', active);
                });

                if (updateUrl) {

                    try {
                        const url = new URL(window.location.href);

                        if (tabKey === 'semua') {
                            url.searchParams.delete('bagian');
                        } else if (tabKey === 'finishing') {
                            // Gunakan "tomo" agar URL lama ?bagian=tomo tetap kompatibel.
                            url.searchParams.set('bagian', 'tomo');
                        } else if (tabKey === 'sample') {
                            url.searchParams.set('bagian', 'sampel');
                        } else {
                            url.searchParams.set('bagian', tabKey);
                        }

                        window.history.replaceState({},
                            '',
                            url.toString()
                        );
                    } catch (e) {
                        // Abaikan jika URL API tidak tersedia.
                    }
                }
            }

            if (ledgerTabs && ledgerTabContent) {

                ledgerTabs.addEventListener(
                    'click',
                    function(event) {

                        const tabButton =
                            event.target.closest('.nav-link[data-tab]');

                        if (!tabButton || !ledgerTabs.contains(tabButton)) {
                            return;
                        }

                        event.preventDefault();

                        activateLedgerTab(
                            tabButton.dataset.tab,
                            true
                        );
                    }
                );

                /*
                |------------------------------------------------------------------
                | State awal dari Blade/URL.
                |------------------------------------------------------------------
                */
                const initialButton =
                    ledgerTabs.querySelector('.nav-link.active[data-tab]');

                if (initialButton) {
                    activateLedgerTab(
                        initialButton.dataset.tab,
                        false
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CLICK ROW PER TAB
            |--------------------------------------------------------------------------
            |
            | Event diletakkan pada #ledgerTabContent, bukan document.
            | Dengan begitu setiap tab tetap mempunyai scope sendiri.
            | Tab baru / isi tab yang berubah tetap otomatis terdeteksi.
            |
            */

            if (ledgerTabContent) {

                ledgerTabContent.addEventListener(
                    'click',
                    function(event) {

                        /*
                        |------------------------------------------------------------------
                        | Jangan proses klik yang terjadi di modal.
                        |------------------------------------------------------------------
                        */

                        if (
                            modal &&
                            modal.contains(
                                event.target
                            )
                        ) {

                            return;

                        }


                        /*
                        |------------------------------------------------------------------
                        | Ambil tab yang sedang aktif.
                        |------------------------------------------------------------------
                        */

                        const activePane =
                            ledgerTabContent.querySelector(
                                '.tab-pane.show.active'
                            );


                        if (!activePane) {

                            return;

                        }


                        /*
                        |------------------------------------------------------------------
                        | Cari row yang diklik.
                        |------------------------------------------------------------------
                        */

                        const row =
                            event.target.closest(
                                '.ledger-table tbody tr.ledger-row'
                            );


                        if (!row) {

                            return;

                        }


                        /*
                        |------------------------------------------------------------------
                        | Pastikan row memang berasal dari tab aktif.
                        |------------------------------------------------------------------
                        */

                        if (
                            !activePane.contains(row)
                        ) {

                            return;

                        }


                        /*
                        |------------------------------------------------------------------
                        | Jangan buka modal ketika klik detail bahan.
                        |------------------------------------------------------------------
                        */

                        if (
                            event.target.closest(
                                '.material-detail-row'
                            )
                        ) {

                            return;

                        }


                        /*
                        |------------------------------------------------------------------
                        | Invoice separator tidak boleh membuka modal.
                        |------------------------------------------------------------------
                        */

                        if (
                            row.classList.contains(
                                'invoice-separator'
                            )
                        ) {

                            return;

                        }


                        const invoice =
                            findInvoiceFromRow(
                                row
                            );


                        const dateCell =
                            row.querySelector(
                                'td:nth-child(2)'
                            );


                        const tanggal =
                            dateCell ?
                            dateCell.innerText.trim() :
                            '';


                        openInvoiceModal(
                            invoice,
                            tanggal
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | SAVE INVOICE LAMA
            |--------------------------------------------------------------------------
            */

            if (saveButton) {

                saveButton.addEventListener(
                    'click',
                    function() {

                        if (
                            !parsedRows.length
                        ) {

                            alert(
                                'Belum ada data yang dipaste.'
                            );

                            return;

                        }


                        let nomorInvoice =
                            activeInvoice;


                        if (!nomorInvoice) {

                            nomorInvoice =
                                parsedRows[0].invoice;

                        }


                        if (!nomorInvoice) {

                            alert(
                                'Nomor invoice tidak ditemukan.'
                            );

                            return;

                        }


                        let tanggalInvoice =
                            activeTanggal;


                        if (
                            tanggalInvoice.includes('/')
                        ) {

                            tanggalInvoice =
                                formatDate(
                                    tanggalInvoice
                                );

                        }


                        const detailBahan =
                            parsedRows.map(
                                function(row) {

                                    return {

                                        supplier: row.supplier,

                                        qty: String(
                                            row.qty
                                        ),

                                        harga: String(
                                            row.harga
                                        ),

                                        jenis: row.jenis,

                                        total: String(
                                            row.total
                                        ),

                                        satuan: row.satuan,

                                        spk: row.spk,

                                        nilai_potongan: String(
                                            row.nilai_potongan
                                        )

                                    };

                                }
                            );


                        const csrf =
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.getAttribute(
                                'content'
                            );


                        const payload = {

                            nomor_invoice: nomorInvoice,

                            tanggal_invoice: tanggalInvoice,

                            detail_bahan: detailBahan,

                            _token: csrf

                        };


                        saveButton.disabled =
                            true;


                        saveButton.textContent =
                            'Menyimpan...';


                        fetch(
                                "{{ route('monitoring-finishing.invoice-lama.store') }}", {

                                    method: 'POST',

                                    headers: {

                                        'Content-Type': 'application/json',

                                        'Accept': 'application/json',

                                        'X-CSRF-TOKEN': csrf

                                    },

                                    body: JSON.stringify(
                                        payload
                                    )

                                }
                            )
                            .then(
                                async function(
                                    response
                                ) {

                                    const data =
                                        await response.json();


                                    if (
                                        !response.ok
                                    ) {

                                        throw new Error(
                                            data.message ||
                                            'Gagal menyimpan data.'
                                        );

                                    }


                                    return data;

                                }
                            )
                            .then(
                                function(data) {

                                    if (
                                        !data.success
                                    ) {

                                        throw new Error(
                                            data.message ||
                                            'Gagal menyimpan.'
                                        );

                                    }


                                    alert(
                                        'Invoice lama dan SPK lama berhasil disimpan.'
                                    );


                                    closeInvoiceModal();


                                    window.location.reload();

                                }
                            )
                            .catch(
                                function(error) {

                                    console.error(
                                        error
                                    );


                                    alert(
                                        'Gagal menyimpan:\n' +
                                        error.message
                                    );

                                }
                            )
                            .finally(
                                function() {

                                    saveButton.disabled =
                                        false;

                                    saveButton.textContent =
                                        'Simpan';

                                }
                            );

                    }
                );

            }


        }

    );
</script>
@endsection
