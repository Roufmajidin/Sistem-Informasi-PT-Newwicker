@extends('master.master')

@section('content')

    <style>
        /*
        |--------------------------------------------------------------------------
        | INVOICE MODAL
        |--------------------------------------------------------------------------
        */

        .invoice-modal-wide {
            max-width: 95vw;
            width: 95vw;
        }

        .invoice-modal-body {
            max-height: 75vh;
            overflow-y: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | MATERIAL TABLE
        |--------------------------------------------------------------------------
        */

        .material-table th,
        .material-table td {
            vertical-align: middle;
        }

        .material-table input,
        .material-table select {
            min-width: 100px;
        }


        .detail-empty {
            text-align: center;
            color: #999;
            padding: 25px;
        }


        .currency-input {
            text-align: right;
        }


        .detail-total {
            text-align: right;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | SORT BUTTON
        |--------------------------------------------------------------------------
        */

        .date-sort-btn {

            border: none;

            background: transparent;

            color: inherit;

            padding: 0;

            margin-left: 5px;

            cursor: pointer;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: 22px;

            height: 22px;

            border-radius: 4px;

        }


        .date-sort-btn:hover {

            background: rgba(255, 255, 255, .15);

        }


        .date-sort-btn i {

            font-size: 13px;

        }


        /*
        |--------------------------------------------------------------------------
        | TO SUB BADGE
        |--------------------------------------------------------------------------
        */

        .to-sub-badge {

            display: inline-block;

            padding: 4px 9px;

            border-radius: 6px;

            font-size: 12px;

            font-weight: 600;

            text-transform: capitalize;

        }
    </style>


    <div class="container-fluid">


        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">

            <div>

                <h4 class="mb-0">
                    Monitoring Invoice
                </h4>

                <small class="text-muted">
                    Monitoring invoice dan detail bahan
                </small>

            </div>


            <button type="button" class="btn btn-primary" onclick="openCreateModal()">

                <i class="fa fa-plus"></i>

                Create Invoice

            </button>

        </div>



        {{-- ========================================================= --}}
        {{-- ALERT SUCCESS --}}
        {{-- ========================================================= --}}

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}


                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>
        @endif



        {{-- ========================================================= --}}
        {{-- ALERT ERROR --}}
        {{-- ========================================================= --}}

        @if ($errors->any())
            <div class="alert alert-danger">

                <strong>
                    Terdapat kesalahan:
                </strong>


                <ul class="mb-0 mt-1">

                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach

                </ul>

            </div>
        @endif



        {{-- ========================================================= --}}
        {{-- MAIN TABLE --}}
        {{-- ========================================================= --}}

        <div class="card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0" id="invoiceTable">

                        <thead style="background:#2f4050;color:white;">

                            <tr>

                                <th width="70">
                                    ID
                                </th>


                                <th>
                                    Nomor Invoice
                                </th>


                                {{-- ================================================= --}}
                                {{-- TANGGAL + SORT --}}
                                {{-- ================================================= --}}

                                <th width="170">

                                    Tanggal Invoice


                                    <button type="button" id="dateSortButton" class="date-sort-btn"
                                        onclick="sortInvoiceByDate()" title="Urutkan tanggal">

                                        <i id="dateSortIcon" class="fa fa-sort-down"></i>

                                    </button>

                                </th>


                                {{-- ================================================= --}}
                                {{-- TO SUB --}}
                                {{-- ================================================= --}}

                                <th width="120">
                                    To Sub
                                </th>


                                <th>
                                    Detail Bahan
                                </th>


                                <th width="100">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody id="invoiceTableBody">

                            @forelse($invoices as $invoice)
                                <tr data-invoice-date="{{ optional($invoice->tanggal_invoice)->format('Y-m-d') }}"
                                    data-invoice-id="{{ $invoice->id }}">

                                    {{-- ================================================= --}}
                                    {{-- ID --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        <button type="button" class="btn btn-link p-0 fw-bold"
                                            onclick='openEditInvoice(@json($invoice))'>

                                            #{{ $invoice->id }}

                                        </button>

                                    </td>


                                    {{-- ================================================= --}}
                                    {{-- NOMOR INVOICE --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        {{ $invoice->nomor_invoice }}

                                    </td>


                                    {{-- ================================================= --}}
                                    {{-- TANGGAL --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        {{ optional($invoice->tanggal_invoice)->format('d/m/Y') }}

                                    </td>


                                    {{-- ================================================= --}}
                                    {{-- TO SUB --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        @if ($invoice->to_sub)
                                            <span class="to-sub-badge bg-light border">

                                                {{ ucfirst($invoice->to_sub) }}

                                            </span>
                                        @else
                                            <span class="text-muted">
                                                -
                                            </span>
                                        @endif

                                    </td>


                                    {{-- ================================================= --}}
                                    {{-- DETAIL BAHAN --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        @if (!empty($invoice->detail_bahan))
                                            @foreach ($invoice->detail_bahan as $item)
                                                <div class="mb-1">

                                                    <strong>

                                                        {{ $item['jenis'] ?? '-' }}

                                                    </strong>


                                                    &nbsp;


                                                    {{ number_format($item['qty'] ?? 0, 2, ',', '.') }}


                                                    {{ $item['satuan'] ?? '' }}


                                                    &nbsp;×&nbsp;


                                                    Rp
                                                    {{ number_format($item['harga'] ?? 0, 0, ',', '.') }}


                                                    &nbsp;=&nbsp;


                                                    <strong>

                                                        Rp
                                                        {{ number_format($item['total'] ?? 0, 0, ',', '.') }}

                                                    </strong>

                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">
                                                Tidak ada detail
                                            </span>
                                        @endif

                                    </td>


                                    {{-- ================================================= --}}
                                    {{-- ACTION --}}
                                    {{-- ================================================= --}}

                                    <td>

                                        <form
                                            action="{{ route('monitoring-invoice.destroy', $invoice->id) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                            'Yakin ingin menghapus invoice ini?'
                                        )">

                                            @csrf

                                            @method('DELETE')


                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">

                                                <i class="fa fa-trash"></i>

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="text-center text-muted py-5">

                                        <i class="fa fa-file-invoice fa-2x mb-2"></i>

                                        <div>
                                            Belum ada invoice.
                                        </div>

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- MODAL 1 : CREATE / EDIT INVOICE --}}
    {{-- ============================================================= --}}

    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered invoice-modal-wide">

            <div class="modal-content">


                <form id="invoiceForm" method="POST">

                    @csrf


                    <div id="invoiceMethod"></div>


                    {{-- ================================================= --}}
                    {{-- HEADER --}}
                    {{-- ================================================= --}}

                    <div class="modal-header">

                        <div>

                            <h5 class="modal-title mb-0" id="invoiceModalTitle">
                                Create Invoice
                            </h5>


                            <small class="text-muted">
                                Masukkan informasi invoice dan detail bahan
                            </small>

                        </div>


                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>



                    {{-- ================================================= --}}
                    {{-- BODY --}}
                    {{-- ================================================= --}}

                    <div class="modal-body invoice-modal-body">


                        {{-- ================================================= --}}
                        {{-- HEADER INVOICE --}}
                        {{-- ================================================= --}}

                        <div class="row mb-4">


                            {{-- NOMOR INVOICE --}}

                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    Nomor Invoice
                                </label>


                                <input type="text" class="form-control" id="nomor_invoice" name="nomor_invoice"
                                    placeholder="Contoh: INV-001" required>

                            </div>



                            {{-- TANGGAL --}}

                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    Tanggal Invoice
                                </label>


                                <input type="date" class="form-control" id="tanggal_invoice" name="tanggal_invoice"
                                    required>

                            </div>



                            {{-- ================================================= --}}
                            {{-- TO SUB --}}
                            {{-- ================================================= --}}

                            <div class="col-md-4">

                                <label class="form-label fw-semibold">

                                    To Sub

                                </label>


                                <select class="form-select" id="to_sub" name="to_sub" required>

                                    <option value="">
                                        -- Pilih To Sub --
                                    </option>


                                    <option value="tomo">
                                        Tomo
                                    </option>


                                    <option value="darto">
                                        Darto
                                    </option>

                                </select>

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- DETAIL BAHAN --}}
                        {{-- ================================================= --}}

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <div>

                                <h6 class="mb-0 fw-bold">
                                    Detail Bahan
                                </h6>


                                <small class="text-muted">
                                    Klik Edit untuk mengubah masing-masing bahan
                                </small>

                            </div>


                            <button type="button" class="btn btn-sm btn-success" onclick="addMaterial()">

                                <i class="fa fa-plus"></i>

                                Tambah Bahan

                            </button>

                        </div>



                        <div class="table-responsive">

                            <table class="table table-bordered table-hover material-table">

                                <thead class="table-light">

                                    <tr>

                                        <th>
                                            Jenis
                                        </th>

                                        <th width="120">
                                            Qty
                                        </th>

                                        <th width="120">
                                            Satuan
                                        </th>

                                        <th width="180">
                                            Harga
                                        </th>

                                        <th width="180">
                                            Total
                                        </th>

                                        <th width="100">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="materialTableBody"></tbody>


                                <tfoot>

                                    <tr>

                                        <th colspan="4" class="text-end">

                                            Grand Total

                                        </th>


                                        <th class="text-end" id="grandTotalDisplay">

                                            Rp 0

                                        </th>


                                        <th></th>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>



                    {{-- ================================================= --}}
                    {{-- FOOTER --}}
                    {{-- ================================================= --}}

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Batal

                        </button>


                        <button type="submit" class="btn btn-primary">

                            <i class="fa fa-save"></i>

                            Simpan Invoice

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    {{-- ============================================================= --}}
    {{-- MODAL 2 : EDIT DETAIL BAHAN --}}
    {{-- ============================================================= --}}

    <div class="modal fade" id="materialEditModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">


                <div class="modal-header">

                    <div>

                        <h5 class="modal-title">
                            Edit Detail Bahan
                        </h5>


                        <small class="text-muted" id="materialEditSubtitle"></small>

                    </div>


                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>



                <div class="modal-body">


                    {{-- JENIS --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Jenis
                        </label>


                        <input type="text" class="form-control" id="editJenis" placeholder="Contoh: Thinner">

                    </div>



                    {{-- QTY --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Qty
                        </label>


                        <input type="number" step="any" min="0" class="form-control" id="editQty">

                    </div>



                    {{-- SATUAN --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Satuan
                        </label>


                        <input type="text" class="form-control" id="editSatuan" placeholder="Liter / Kg / Pcs">

                    </div>



                    {{-- HARGA --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Harga
                        </label>


                        <input type="number" step="any" min="0" class="form-control" id="editHarga">

                    </div>



                    {{-- TOTAL --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Total
                        </label>


                        <input type="text" class="form-control bg-light fw-bold" id="editTotalDisplay" readonly>

                    </div>

                </div>



                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>


                    <button type="button" class="btn btn-primary" onclick="saveMaterialEdit()">

                        <i class="fa fa-save"></i>

                        Simpan Detail

                    </button>

                </div>

            </div>

        </div>

    </div>



    <script>
        /*
    |--------------------------------------------------------------------------
    | GLOBAL
    |--------------------------------------------------------------------------
    */

        let materials = [];

        let editingMaterialIndex = null;

        let invoiceModalInstance = null;

        let materialEditModalInstance = null;


        /*
        |--------------------------------------------------------------------------
        | SORT DATE
        |--------------------------------------------------------------------------
        |
        | desc = terbaru -> terlama
        | asc  = terlama -> terbaru
        |
        |--------------------------------------------------------------------------
        */

        let invoiceDateSortDirection = 'desc';



        /*
        |--------------------------------------------------------------------------
        | FORMAT RUPIAH
        |--------------------------------------------------------------------------
        */

        function formatRupiah(value) {
            value = Number(value) || 0;

            return 'Rp ' + value.toLocaleString(
                'id-ID', {
                    maximumFractionDigits: 0
                }
            );
        }



        /*
        |--------------------------------------------------------------------------
        | SORT INVOICE BY DATE
        |--------------------------------------------------------------------------
        */

        function sortInvoiceByDate() {
            const tbody =
                document.getElementById(
                    'invoiceTableBody'
                );


            if (!tbody) {
                return;
            }


            const rows =
                Array.from(
                    tbody.querySelectorAll(
                        'tr[data-invoice-date]'
                    )
                );


            if (rows.length === 0) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | TOGGLE
            |--------------------------------------------------------------------------
            */

            invoiceDateSortDirection =
                invoiceDateSortDirection === 'desc' ?
                'asc' :
                'desc';


            /*
            |--------------------------------------------------------------------------
            | SORT
            |--------------------------------------------------------------------------
            */

            rows.sort(function(a, b) {

                const dateA =
                    a.dataset.invoiceDate || '';


                const dateB =
                    b.dataset.invoiceDate || '';


                /*
                |--------------------------------------------------------------------------
                | Empty date
                |--------------------------------------------------------------------------
                */

                if (!dateA && !dateB) {
                    return 0;
                }


                if (!dateA) {
                    return 1;
                }


                if (!dateB) {
                    return -1;
                }


                const timeA =
                    new Date(
                        dateA + 'T00:00:00'
                    ).getTime();


                const timeB =
                    new Date(
                        dateB + 'T00:00:00'
                    ).getTime();


                if (
                    invoiceDateSortDirection === 'asc'
                ) {

                    return timeA - timeB;

                }


                return timeB - timeA;

            });


            /*
            |--------------------------------------------------------------------------
            | RE-APPEND
            |--------------------------------------------------------------------------
            */

            rows.forEach(function(row) {

                tbody.appendChild(row);

            });


            updateDateSortIcon();
        }



        /*
        |--------------------------------------------------------------------------
        | UPDATE SORT ICON
        |--------------------------------------------------------------------------
        */

        function updateDateSortIcon() {
            const icon =
                document.getElementById(
                    'dateSortIcon'
                );


            const button =
                document.getElementById(
                    'dateSortButton'
                );


            if (!icon || !button) {
                return;
            }


            if (
                invoiceDateSortDirection === 'asc'
            ) {

                /*
                |--------------------------------------------------------------------------
                | Terkecil -> terbesar
                | Terlama -> terbaru
                |--------------------------------------------------------------------------
                */

                icon.className =
                    'fa fa-sort-up';


                button.title =
                    'Urutan: terlama → terbaru';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Terbesar -> terkecil
                | Terbaru -> terlama
                |--------------------------------------------------------------------------
                */

                icon.className =
                    'fa fa-sort-down';


                button.title =
                    'Urutan: terbaru → terlama';
            }
        }



        /*
        |--------------------------------------------------------------------------
        | CREATE MODAL
        |--------------------------------------------------------------------------
        */

       function openCreateModal()
{
    materials = [];

    editingMaterialIndex = null;

    document.getElementById(
        'invoiceModalTitle'
    ).innerText =
        'Create Invoice';

    document.getElementById(
        'invoiceForm'
    ).action =
        "{{ route('monitoring-invoice.store') }}";

    document.getElementById(
        'invoiceMethod'
    ).innerHTML = '';

    document.getElementById(
        'nomor_invoice'
    ).value = '';

    document.getElementById(
        'tanggal_invoice'
    ).value = '';

    /*
    |--------------------------------------------------------------------------
    | RESET TO SUB
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'to_sub'
    ).value = '';

    addMaterial();

    renderMaterials();

    /*
    |--------------------------------------------------------------------------
    | SHOW MODAL
    |--------------------------------------------------------------------------
    */

    const modalElement =
        document.getElementById(
            'invoiceModal'
        );

    invoiceModalInstance =
        new bootstrap.Modal(
            modalElement
        );

    invoiceModalInstance.show();
}


        /*
        |--------------------------------------------------------------------------
        | EDIT INVOICE
        |--------------------------------------------------------------------------
        */

    function openEditInvoice(invoice)
{
    materials = JSON.parse(
        JSON.stringify(
            invoice.detail_bahan ?? []
        )
    );

    editingMaterialIndex = null;

    document.getElementById(
        'invoiceModalTitle'
    ).innerText =
        'Edit Invoice #' + invoice.id;

    document.getElementById(
        'invoiceForm'
    ).action =
        `/monitoring-invoice/${invoice.id}`;

    document.getElementById(
        'invoiceMethod'
    ).innerHTML =
        '@method("PUT")';

    document.getElementById(
        'nomor_invoice'
    ).value =
        invoice.nomor_invoice ?? '';

    document.getElementById(
        'tanggal_invoice'
    ).value =
        invoice.tanggal_invoice
            ? invoice.tanggal_invoice.substring(0, 10)
            : '';

    /*
    |--------------------------------------------------------------------------
    | TO SUB
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'to_sub'
    ).value =
        invoice.to_sub ?? '';

    if (materials.length === 0) {
        addMaterial();
    }

    renderMaterials();

    /*
    |--------------------------------------------------------------------------
    | SHOW MODAL
    |--------------------------------------------------------------------------
    */

    const modalElement =
        document.getElementById(
            'invoiceModal'
        );

    invoiceModalInstance =
        new bootstrap.Modal(
            modalElement
        );

    invoiceModalInstance.show();
}


        /*
        |--------------------------------------------------------------------------
        | ADD MATERIAL
        |--------------------------------------------------------------------------
        */

        function addMaterial(
            jenis = '',
            qty = '',
            satuan = '',
            harga = ''
        ) {
            materials.push({

                jenis: jenis,

                qty: qty,

                satuan: satuan,

                harga: harga,

                total: (Number(qty) || 0) *
                    (Number(harga) || 0)

            });


            renderMaterials();
        }



        /*
        |--------------------------------------------------------------------------
        | RENDER MATERIAL
        |--------------------------------------------------------------------------
        */

        function renderMaterials() {
            const tbody =
                document.getElementById(
                    'materialTableBody'
                );


            tbody.innerHTML = '';


            if (materials.length === 0) {

                tbody.innerHTML = `

            <tr>

                <td
                    colspan="6"
                    class="detail-empty"
                >

                    Belum ada detail bahan.

                </td>

            </tr>

        `;


                updateGrandTotal();

                return;
            }


            materials.forEach(
                function(item, index) {

                    const qty =
                        Number(item.qty) || 0;


                    const harga =
                        Number(item.harga) || 0;


                    const total =
                        qty * harga;


                    item.total =
                        total;


                    const row =
                        document.createElement(
                            'tr'
                        );


                    row.innerHTML = `

                <td>

                    <input
                        type="text"
                        class="form-control form-control-sm"
                        value="${escapeHtml(item.jenis ?? '')}"
                        placeholder="Jenis bahan"
                        oninput="updateMaterialField(
                            ${index},
                            'jenis',
                            this.value
                        )"
                    >

                </td>


                <td>

                    <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control form-control-sm text-end"
                        value="${item.qty ?? ''}"
                        placeholder="Qty"
                        oninput="updateMaterialField(
                            ${index},
                            'qty',
                            this.value
                        )"
                    >

                </td>


                <td>

                    <input
                        type="text"
                        class="form-control form-control-sm"
                        value="${escapeHtml(item.satuan ?? '')}"
                        placeholder="Liter / Kg / Pcs"
                        oninput="updateMaterialField(
                            ${index},
                            'satuan',
                            this.value
                        )"
                    >

                </td>


                <td>

                    <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control form-control-sm text-end"
                        value="${item.harga ?? ''}"
                        placeholder="Harga"
                        oninput="updateMaterialField(
                            ${index},
                            'harga',
                            this.value
                        )"
                    >

                </td>


                <td>

                    <input
                        type="text"
                        class="form-control form-control-sm bg-light fw-bold text-end"
                        value="${formatRupiah(total)}"
                        readonly
                    >

                </td>


                <td>

                    <div class="d-flex gap-1">

                        <button
                            type="button"
                            class="btn btn-sm btn-warning"
                            onclick="openMaterialEdit(${index})"
                            title="Edit Detail"
                        >

                            <i class="fa fa-edit"></i>

                        </button>


                        <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            onclick="deleteMaterial(${index})"
                            title="Hapus"
                        >

                            <i class="fa fa-trash"></i>

                        </button>

                    </div>

                </td>

            `;


                    tbody.appendChild(row);

                }
            );


            updateGrandTotal();
        }



        /*
        |--------------------------------------------------------------------------
        | OPEN EDIT MATERIAL
        |--------------------------------------------------------------------------
        */

        function openMaterialEdit(index) {
            const item =
                materials[index];


            if (!item) {
                return;
            }


            editingMaterialIndex =
                index;


            document.getElementById(
                    'materialEditSubtitle'
                ).innerText =
                'Detail bahan #' + (index + 1);


            document.getElementById(
                    'editJenis'
                ).value =
                item.jenis ?? '';


            document.getElementById(
                    'editQty'
                ).value =
                item.qty ?? '';


            document.getElementById(
                    'editSatuan'
                ).value =
                item.satuan ?? '';


            document.getElementById(
                    'editHarga'
                ).value =
                item.harga ?? '';


            calculateEditTotal();


            const modalElement =
                document.getElementById(
                    'materialEditModal'
                );


            materialEditModalInstance =
                bootstrap.Modal.getOrCreateInstance(
                    modalElement
                );


            materialEditModalInstance.show();
        }



        /*
        |--------------------------------------------------------------------------
        | CALCULATE EDIT TOTAL
        |--------------------------------------------------------------------------
        */

        function calculateEditTotal() {
            const qty =
                Number(
                    document.getElementById(
                        'editQty'
                    ).value
                ) || 0;


            const harga =
                Number(
                    document.getElementById(
                        'editHarga'
                    ).value
                ) || 0;


            const total =
                qty * harga;


            document.getElementById(
                    'editTotalDisplay'
                ).value =
                formatRupiah(total);
        }



        /*
        |--------------------------------------------------------------------------
        | AUTO CALCULATE EDIT
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'input',
            function(event) {

                if (
                    event.target.id === 'editQty' ||
                    event.target.id === 'editHarga'
                ) {

                    calculateEditTotal();

                }

            }
        );



        /*
        |--------------------------------------------------------------------------
        | SAVE MATERIAL EDIT
        |--------------------------------------------------------------------------
        */

        function saveMaterialEdit() {
            if (
                editingMaterialIndex === null
            ) {

                return;
            }


            const jenis =
                document.getElementById(
                    'editJenis'
                ).value.trim();


            const qty =
                Number(
                    document.getElementById(
                        'editQty'
                    ).value
                ) || 0;


            const satuan =
                document.getElementById(
                    'editSatuan'
                ).value.trim();


            const harga =
                Number(
                    document.getElementById(
                        'editHarga'
                    ).value
                ) || 0;


            if (!jenis) {

                alert(
                    'Jenis bahan wajib diisi.'
                );

                return;
            }


            if (qty <= 0) {

                alert(
                    'Qty harus lebih dari 0.'
                );

                return;
            }


            if (!satuan) {

                alert(
                    'Satuan wajib diisi.'
                );

                return;
            }


            if (harga < 0) {

                alert(
                    'Harga tidak valid.'
                );

                return;
            }


            materials[
                editingMaterialIndex
            ] = {

                jenis: jenis,

                qty: qty,

                satuan: satuan,

                harga: harga,

                total: qty * harga

            };


            renderMaterials();


            editingMaterialIndex =
                null;


            if (materialEditModalInstance) {

                materialEditModalInstance.hide();

            }
        }



        /*
        |--------------------------------------------------------------------------
        | DELETE MATERIAL
        |--------------------------------------------------------------------------
        */

        function deleteMaterial(index) {
            if (
                !confirm(
                    'Hapus detail bahan ini?'
                )
            ) {

                return;
            }


            materials.splice(
                index,
                1
            );


            renderMaterials();
        }



        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        function updateGrandTotal() {
            let total = 0;


            materials.forEach(
                function(item) {

                    total +=
                        Number(item.total) || 0;

                }
            );


            document.getElementById(
                    'grandTotalDisplay'
                ).innerText =
                formatRupiah(total);
        }



        /*
        |--------------------------------------------------------------------------
        | BEFORE SUBMIT
        |--------------------------------------------------------------------------
        */

        document.getElementById(
            'invoiceForm'
        ).addEventListener(
            'submit',
            function(event) {

                if (materials.length === 0) {

                    event.preventDefault();


                    alert(
                        'Minimal harus ada 1 detail bahan.'
                    );


                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | HAPUS HIDDEN INPUT LAMA
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        '.dynamic-material-input'
                    )
                    .forEach(
                        function(input) {

                            input.remove();

                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | CREATE HIDDEN INPUT
                |--------------------------------------------------------------------------
                */

                materials.forEach(
                    function(item, index) {

                        const fields = {

                            jenis: item.jenis ?? '',

                            qty: item.qty ?? 0,

                            satuan: item.satuan ?? '',

                            harga: item.harga ?? 0,

                            total: item.total ?? 0

                        };


                        Object.keys(fields).forEach(
                            function(field) {

                                const input =
                                    document.createElement(
                                        'input'
                                    );


                                input.type =
                                    'hidden';


                                input.name =
                                    `detail_bahan[${index}][${field}]`;


                                input.value =
                                    fields[field];


                                input.classList.add(
                                    'dynamic-material-input'
                                );


                                document
                                    .getElementById(
                                        'invoiceForm'
                                    )
                                    .appendChild(
                                        input
                                    );

                            }
                        );

                    }
                );

            }
        );



        /*
        |--------------------------------------------------------------------------
        | UPDATE MATERIAL FIELD
        |--------------------------------------------------------------------------
        */

        window.updateMaterialField =
            function(
                index,
                field,
                value
            ) {
                if (!materials[index]) {
                    return;
                }


                if (
                    field === 'qty' ||
                    field === 'harga'
                ) {

                    materials[index][field] =
                        value === '' ?
                        '' :
                        Number(value);

                } else {

                    materials[index][field] =
                        value;

                }


                materials[index].total =
                    (Number(
                        materials[index].qty
                    ) || 0) *
                    (Number(
                        materials[index].harga
                    ) || 0);


                /*
                |--------------------------------------------------------------------------
                | UPDATE TOTAL ROW
                |--------------------------------------------------------------------------
                */

                const rows =
                    document.querySelectorAll(
                        '#materialTableBody tr'
                    );


                const row =
                    rows[index];


                if (row) {

                    const totalInput =
                        row.querySelector(
                            'td:nth-child(5) input'
                        );


                    if (totalInput) {

                        totalInput.value =
                            formatRupiah(
                                materials[index].total
                            );

                    }

                }


                updateGrandTotal();
            };



        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {
            return String(value)

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
        | INITIAL SORT
        |--------------------------------------------------------------------------
        |
        | Saat pertama dibuka:
        | terbaru -> terlama
        |
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                const tbody =
                    document.getElementById(
                        'invoiceTableBody'
                    );


                if (!tbody) {
                    return;
                }


                const rows =
                    Array.from(
                        tbody.querySelectorAll(
                            'tr[data-invoice-date]'
                        )
                    );


                rows.sort(function(a, b) {

                    const dateA =
                        a.dataset.invoiceDate || '';


                    const dateB =
                        b.dataset.invoiceDate || '';


                    if (!dateA && !dateB) {
                        return 0;
                    }


                    if (!dateA) {
                        return 1;
                    }


                    if (!dateB) {
                        return -1;
                    }


                    const timeA =
                        new Date(
                            dateA + 'T00:00:00'
                        ).getTime();


                    const timeB =
                        new Date(
                            dateB + 'T00:00:00'
                        ).getTime();


                    return timeB - timeA;

                });


                rows.forEach(function(row) {

                    tbody.appendChild(row);

                });


                invoiceDateSortDirection =
                    'desc';


                updateDateSortIcon();

            }
        );
    </script>

@endsection
