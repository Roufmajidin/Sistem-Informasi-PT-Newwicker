
@extends('master.master')

@section('content')

<style>
    .invoice-modal-wide {
        max-width: 95vw;
        width: 95vw;
    }

    .invoice-modal-body {
        max-height: 75vh;
        overflow-y: auto;
    }

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

        <button
            type="button"
            class="btn btn-primary"
            onclick="openCreateModal()"
        >
            <i class="fa fa-plus"></i>
            Create Invoice
        </button>

    </div>


    {{-- ========================================================= --}}
    {{-- ALERT --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Terdapat kesalahan:
            </strong>

            <ul class="mb-0 mt-1">

                @foreach($errors->all() as $error)

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

                <table class="table table-bordered table-hover mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="70">
                                ID
                            </th>

                            <th>
                                Nomor Invoice
                            </th>

                            <th width="150">
                                Tanggal Invoice
                            </th>

                            <th>
                                Detail Bahan
                            </th>

                            <th width="100">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($invoices as $invoice)

                            <tr>

                                {{-- ID --}}
                                <td>

                                    <button
                                        type="button"
                                        class="btn btn-link p-0 fw-bold"
                                        onclick='openEditInvoice(@json($invoice))'
                                    >
                                        #{{ $invoice->id }}
                                    </button>

                                </td>


                                {{-- NOMOR INVOICE --}}
                                <td>
                                    {{ $invoice->nomor_invoice }}
                                </td>


                                {{-- TANGGAL --}}
                                <td>

                                    {{ optional(
                                        $invoice->tanggal_invoice
                                    )->format('d/m/Y') }}

                                </td>


                                {{-- DETAIL BAHAN --}}
                                <td>

                                    @if(!empty($invoice->detail_bahan))

                                        @foreach($invoice->detail_bahan as $item)

                                            <div class="mb-1">

                                                <strong>
                                                    {{ $item['jenis'] ?? '-' }}
                                                </strong>

                                                &nbsp;

                                                {{ number_format(
                                                    $item['qty'] ?? 0,
                                                    2,
                                                    ',',
                                                    '.'
                                                ) }}

                                                {{ $item['satuan'] ?? '' }}

                                                &nbsp;×

                                                Rp
                                                {{ number_format(
                                                    $item['harga'] ?? 0,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}

                                                &nbsp;=

                                                <strong>
                                                    Rp
                                                    {{ number_format(
                                                        $item['total'] ?? 0,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </strong>

                                            </div>

                                        @endforeach

                                    @else

                                        <span class="text-muted">
                                            Tidak ada detail
                                        </span>

                                    @endif

                                </td>


                                {{-- ACTION --}}
                                <td>

                                    <form
                                        action="{{ route(
                                            'monitoring-invoice.destroy',
                                            $invoice->id
                                        ) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Yakin ingin menghapus invoice ini?'
                                        )"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                            title="Hapus"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center text-muted py-5"
                                >

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

<div
    class="modal fade"
    id="invoiceModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-dialog-centered invoice-modal-wide"
    >

        <div class="modal-content">

            <form
                id="invoiceForm"
                method="POST"
            >

                @csrf

                <div id="invoiceMethod"></div>


                {{-- HEADER --}}
                <div class="modal-header">

                    <div>

                        <h5
                            class="modal-title mb-0"
                            id="invoiceModalTitle"
                        >
                            Create Invoice
                        </h5>

                        <small class="text-muted">
                            Masukkan informasi invoice dan detail bahan
                        </small>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                {{-- BODY --}}
                <div class="modal-body invoice-modal-body">


                    {{-- ================================================= --}}
                    {{-- HEADER INVOICE --}}
                    {{-- ================================================= --}}

                    <div class="row mb-4">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Nomor Invoice
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nomor_invoice"
                                name="nomor_invoice"
                                placeholder="Contoh: INV-001"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Tanggal Invoice
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="tanggal_invoice"
                                name="tanggal_invoice"
                                required
                            >

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


                        <button
                            type="button"
                            class="btn btn-sm btn-success"
                            onclick="addMaterial()"
                        >

                            <i class="fa fa-plus"></i>

                            Tambah Bahan

                        </button>

                    </div>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered table-hover material-table"
                        >

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


                            <tbody id="materialTableBody">

                            </tbody>


                            <tfoot>

                                <tr>

                                    <th
                                        colspan="4"
                                        class="text-end"
                                    >
                                        Grand Total
                                    </th>

                                    <th
                                        class="text-end"
                                        id="grandTotalDisplay"
                                    >
                                        Rp 0
                                    </th>

                                    <th></th>

                                </tr>

                            </tfoot>

                        </table>

                    </div>


                </div>


                {{-- FOOTER --}}
                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

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

<div
    class="modal fade"
    id="materialEditModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">


            <div class="modal-header">

                <div>

                    <h5 class="modal-title">
                        Edit Detail Bahan
                    </h5>

                    <small
                        class="text-muted"
                        id="materialEditSubtitle"
                    >
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">


                {{-- JENIS --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Jenis
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="editJenis"
                        placeholder="Contoh: Thinner"
                    >

                </div>


                {{-- QTY --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Qty
                    </label>

                    <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control"
                        id="editQty"
                    >

                </div>


                {{-- SATUAN --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Satuan
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="editSatuan"
                        placeholder="Liter / Kg / Pcs"
                    >

                </div>


                {{-- HARGA --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Harga
                    </label>

                    <input
                        type="number"
                        step="any"
                        min="0"
                        class="form-control"
                        id="editHarga"
                    >

                </div>


                {{-- TOTAL --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Total
                    </label>

                    <input
                        type="text"
                        class="form-control bg-light fw-bold"
                        id="editTotalDisplay"
                        readonly
                    >

                </div>


            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Batal
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="saveMaterialEdit()"
                >

                    <i class="fa fa-save"></i>

                    Simpan Detail

                </button>

            </div>

        </div>

    </div>

</div>



<script>

let materials = [];

let editingMaterialIndex = null;

let invoiceModalInstance = null;

let materialEditModalInstance = null;



/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function formatRupiah(value)
{
    value = Number(value) || 0;

    return 'Rp ' + value.toLocaleString(
        'id-ID',
        {
            maximumFractionDigits: 0
        }
    );
}



/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

function openCreateModal()
{
    materials = [];

    editingMaterialIndex = null;


    document.getElementById(
        'invoiceModalTitle'
    ).innerText = 'Create Invoice';


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


    addMaterial();


    renderMaterials();


    $('#invoiceModal').modal('show');
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

    if (materials.length === 0) {
        addMaterial();
    }

    renderMaterials();

    $('#invoiceModal').modal('show');
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
)
{
    materials.push({

        jenis: jenis,

        qty: qty,

        satuan: satuan,

        harga: harga,

        total:
            (Number(qty) || 0) *
            (Number(harga) || 0)

    });


    renderMaterials();
}



/*
|--------------------------------------------------------------------------
| RENDER MATERIAL TABLE
|--------------------------------------------------------------------------
*/

function renderMaterials()
{
    const tbody = document.getElementById('materialTableBody');

    tbody.innerHTML = '';

    if (materials.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="detail-empty">
                    Belum ada detail bahan.
                </td>
            </tr>
        `;

        updateGrandTotal();

        return;
    }


    materials.forEach(function(item, index) {

        const qty = Number(item.qty) || 0;
        const harga = Number(item.harga) || 0;
        const total = qty * harga;

        item.total = total;


        const row = document.createElement('tr');

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

    });


    updateGrandTotal();
}

/*
|--------------------------------------------------------------------------
| OPEN EDIT MATERIAL MODAL
|--------------------------------------------------------------------------
*/

function openMaterialEdit(index)
{
    const item =
        materials[index];


    if (!item) {
        return;
    }


    editingMaterialIndex = index;


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


    materialEditModalInstance =
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById(
                'materialEditModal'
            )
        );


$('#materialEditModal').modal('show');
}



/*
|--------------------------------------------------------------------------
| CALCULATE EDIT TOTAL
|--------------------------------------------------------------------------
*/

function calculateEditTotal()
{
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
| AUTO CALCULATE
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'input',
    function(event)
    {

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

function saveMaterialEdit()
{
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


    editingMaterialIndex = null;


    if (materialEditModalInstance) {

      $('#materialEditModal').modal('hide');

    }
}



/*
|--------------------------------------------------------------------------
| DELETE MATERIAL
|--------------------------------------------------------------------------
*/

function deleteMaterial(index)
{
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

function updateGrandTotal()
{
    let total = 0;


    materials.forEach(
        function(item)
        {

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
|
| Karena materials berupa array JavaScript,
| kita inject menjadi input hidden:
|
| detail_bahan[0][jenis]
| detail_bahan[0][qty]
| detail_bahan[0][satuan]
| detail_bahan[0][harga]
| detail_bahan[0][total]
|
|--------------------------------------------------------------------------
*/

document.getElementById(
    'invoiceForm'
).addEventListener(
    'submit',
    function(event)
    {

        if (materials.length === 0) {

            event.preventDefault();

            alert(
                'Minimal harus ada 1 detail bahan.'
            );

            return;
        }


        /*
        |--------------------------------------------------------------
        | Hapus hidden input sebelumnya
        |--------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.dynamic-material-input'
            )
            .forEach(
                function(input)
                {
                    input.remove();
                }
            );


        /*
        |--------------------------------------------------------------
        | Buat hidden input
        |--------------------------------------------------------------
        */

        materials.forEach(
            function(item, index)
            {

                const fields = {

                    jenis: item.jenis ?? '',

                    qty: item.qty ?? 0,

                    satuan: item.satuan ?? '',

                    harga: item.harga ?? 0,

                    total: item.total ?? 0

                };


                Object.keys(fields).forEach(
                    function(field)
                    {

                        const input =
                            document.createElement(
                                'input'
                            );


                        input.type = 'hidden';


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
| FORMAT NUMBER
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    if (
        value === '' ||
        value === null ||
        value === undefined
    ) {

        return '-';

    }


    const number =
        Number(value);


    if (Number.isNaN(number)) {

        return '-';

    }


    return number.toLocaleString(
        'id-ID',
        {
            maximumFractionDigits: 2
        }
    );
}



/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value)
{
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
window.updateMaterialField = function(index, field, value)
{
    if (!materials[index]) {
        return;
    }

    if (field === 'qty' || field === 'harga') {

        materials[index][field] =
            value === ''
                ? ''
                : Number(value);

    } else {

        materials[index][field] = value;
    }


    materials[index].total =
        (Number(materials[index].qty) || 0) *
        (Number(materials[index].harga) || 0);


    /*
    |--------------------------------------------------------------------------
    | Update Total pada row yang sedang diedit
    |--------------------------------------------------------------------------
    */

    const rows = document.querySelectorAll(
        '#materialTableBody tr'
    );

    const row = rows[index];

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


    /*
    |--------------------------------------------------------------------------
    | Update Grand Total
    |--------------------------------------------------------------------------
    */

    updateGrandTotal();
};
</script>

@endsection

