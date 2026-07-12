<div class="container-fluid">
    {{-- HEADER BOM --}}
    <div class="card mb-3">
        <div class="card-header">
    @if(isset($bom))

            <h5>EDIT BOM</h5>
            @else
            <h5>Create BOM</h5>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">ITEM</th>
                            <td>
                                <input type="text" class="form-control" name="item">
                            </td>
                        </tr>
                        <tr>
                            <th>ARTICLE CODE</th>
                            <td>
                                <input type="text" class="form-control" name="article_code">
                            </td>
                        </tr>
                        <tr>
                            <th>DIMENSION</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="panjang" placeholder="Panjang">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="lebar" placeholder="Lebar">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="tinggi" placeholder="Tinggi">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>CARTON SIZE</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" placeholder="Panjang"
                                            name="carton_panjang">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" placeholder="Lebar"
                                            name="carton_lebar">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" placeholder="Tinggi"
                                            name="carton_tinggi">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>LOADABILITY</th>
                            <td>
                                <div class="row">

                                    <div class="col-md-6">

                                        <input type="number" name="loadability_pcs" class="form-control"
                                            placeholder="PCS">

                                    </div>

                                    <div class="col-md-6">

                                        <input type="number" step="0.001" name="loadability_cbm" class="form-control"
                                            placeholder="CBM">

                                    </div>

                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">

                    <div class="border p-3 text-center">

                        <input type="file" id="bom_image" name="image" accept="image/*" class="form-control mb-2">

                        <img id="preview" src="https://placehold.co/300x200" class="img-fluid border"     style="
            width:180px;
            height:180px;
            object-fit:contain;
        ">

                    </div>

                </div>
            </div>
        </div>
    </div>
    {{-- LABOUR & MATERIAL --}}
    @if(isset($bom))

        <button type="button" class="btn btn-warning btn-sm" id="btn-update-bom">

            Update BOM

        </button>

    @else

        <button type="button" class="btn btn-primary btn-sm" id="btn-save-bom">

            Save BOM

        </button>

    @endif
    <div class="card">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between">
                <strong>LABOUR & MATERIAL</strong>
                <button type="button" class="btn btn-light btn-sm" id="btn-add-header">
                    + Add Header
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="bom-sections">
                {{-- SECTION DINAMIS DISINI --}}
            </div>
           <div class="card mt-3">

                <div class="card-body">

                    <table class="table table-bordered mb-0">

                        <tr>

                            <th width="250">
                                LABOUR
                            </th>

                            <td class="text-right">
                                <span id="labour-total">0</span>
                            </td>

                        </tr>

                        <tr>

                            <th>
                                MATERIAL
                            </th>

                            <td class="text-right">
                                <span id="material-total-all">0</span>
                            </td>

                        </tr>

                    </table>

                </div>
{{-- summary --}}
                SUMMARY
                <button
    type="button"
    class="btn btn-success btn-sm"
    id="btn-add-summary">
    Add Summary
</button>
<table class="table table-bordered">
    <thead>
        <tr>
            <th width="20%">Nama</th>
            <th width="30%">Remark</th>
            <th width="10%">Qty</th>
            <th width="15%">Harga</th>
            <th width="15%">Total</th>
            <th width="10%">Action</th>
        </tr>
    </thead>

    <tbody id="summary-body">

    </tbody>
</table>
</div>
<table class="table table-bordered">
    <tr class="table-success">
        <th colspan="4" class="text-right">
            TOTAL HPP
        </th>
        <th>
            <input
                type="text"
                id="total-hpp"
                class="form-control"
                readonly
                value="0">
        </th>
        <th></th>
    </tr>
</table>
        </div>
    </div>
</div>
<div class="modal fade" id="materialPickerModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5>
                    Pilih Material
                </h5>
            </div>
            <div class="modal-body">
                <input type="text" id="searchMasterMaterial" class="form-control mb-3" placeholder="Cari material...">
                <table class="table table-bordered" id="materialMasterTable">
                    <thead>
                        <tr>
                            <th width="80">
                                Pilih
                            </th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($masterMaterials as $item)

                        @php

                        $harga = 0;

                        if($item->type == 'material_price'){

                            $material = $materialPrices->firstWhere('id',$item->id);

                            $harga = $material->harga ?? 0;
                        }

                        @endphp

                        <tr>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm btn-select-material"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->nama }}"
                                    data-type="{{ $item->type }}"
                                    data-price="{{ $harga }}">

                                    Pilih

                                </button>
                            </td>

                            <td>{{ $item->id }}</td>

                            <td>{{ $item->nama }}</td>

                            <td>{{ $item->jenis }}</td>

                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let activeMaterialInput = null;
    let sectionIndex = 0;
    // klik child
    $(document).on(
        'click',
        '.material-picker',
        function () {
            activeMaterialInput = $(this);
            $('#materialPickerModal')
                .modal('show');
        });
    // pili material dari modal
    $(document).on(
    'click',
    '.btn-select-material',
    function () {

        let id =
            $(this).data('id');

        let nama =
            $(this).data('name');

        let type =
            $(this).data('type');

        let price =
            $(this).data('price') || 0;

        let row =
            activeMaterialInput.closest('tr');

        activeMaterialInput.val(nama);

        row.find('.material-id')
            .val(id);

        row.find('.material-type')
            .val(type);

        row.find('.material-price')
            .val(price);

        calculateRow(row);

        $('#materialPickerModal')
            .modal('hide');

    });
    function calculateRow(row){

    let qty =
        parseFloat(
            row.find('.qty').val()
        ) || 0;

    let price =
        parseFloat(
            row.find('.material-price').val()
        ) || 0;

    let total = qty * price;

    row.find('.material-total')
        .val(total);
}
    // search material di modal
    $('#searchMasterMaterial').on(
        'keyup',
        function () {
            let keyword =
                $(this)
                .val()
                .toLowerCase();
            $('#materialMasterTable tbody tr')
                .each(function () {
                    let text =
                        $(this)
                        .text()
                        .toLowerCase();
                    $(this)
                        .toggle(
                            text.indexOf(keyword) > -1
                        );
                });
        });
    // ADD HEADER
    $('#btn-add-header').click(function () {
        sectionIndex++;
        let html = `
        <div class="bom-section card mb-3">
            <div class="card-header bg-success text-white">
                <div class="row">
                    <div class="col-md-8">
                        <input
                            type="text"
                            class="form-control section-name"
                            placeholder="Nama Header"
                            value="RANGKA ROTAN">
                    </div>
                    <div class="col-md-4 text-right">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm btn-add-child">
                            Add Child
                        </button>
                        <button
                            type="button"
                            class="btn btn-success btn-sm btn-add-sub-price">
                            Add Sub Harga
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger btn-sm btn-remove-header">
                            Delete Header
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="35%">Material</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Satuan</th>
                            <th width="10%">Harga</th>
                            <th width="10%">Total</th>
                            <th width="20%">Keterangan</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody class="child-body">
                    </tbody>

                    <tbody class="sub-price-body">
                    </tbody>
                </table>


            </div>
        </div>
    `;
        $('#bom-sections').append(html);
        saveDraft();
    });
    // REMOVE HEADER
    $(document).on(
        'click',
        '.btn-remove-header',
        function () {
            $(this)
                .closest('.bom-section')
                .remove();
                updateSummary();
            saveDraft();
        });
    // ADD CHILD
    $(document).on(
        'click',
        '.btn-add-child',
        function () {
            let tbody = $(this)
                .closest('.bom-section')
                .find('.child-body');
            let row = `
<tr>
    <td>
        <input
            type="hidden"
            class="material-id">
        <input
            type="hidden"
            class="material-type">
        <input
            type="text"
            readonly
            class="form-control material-picker"
            placeholder="Klik untuk pilih material">
    </td>

    <td>
        <input
            type="number"
            step="0.0001"
            class="form-control qty"
            placeholder="Qty">
    </td>

   <td>
    <input
        type="text"
        class="form-control unit"
        placeholder="Kg / pcs / m3">
</td>

<td>

    <input
        type="number"
        class="form-control material-price"
        value="0">

</td>

<td>

    <input
        type="number"
        readonly
        class="form-control material-total"
        value="0">

</td>
  <td>
        <input
            type="text"
            class="form-control specification"
            placeholder="keterangan">
    </td>
<td>
    <button
        type="button"
        class="btn btn-danger btn-sm btn-remove-child">
        Delete
    </button>
</td>
</tr>
`;
            tbody.append(row);
            updateSummary();

            saveDraft();
        });
        // add sub harga
        $(document).on(
    'click',
    '.btn-add-sub-price',
    function () {

        let tbody = $(this)
            .closest('.bom-section')
            .find('.sub-price-body');

        tbody.append(`
            <tr class="table-success sub-price-row">

                <td colspan="5">
                    <input
                        type="text"
                        class="form-control sub-price-name"
                        placeholder="Contoh : JASA + ANYAM Pak Sumantri">
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control sub-price-value"
                        value="0">
                </td>

                <td>
                    <button
                        type="button"
                        class="btn btn-danger btn-sm btn-remove-sub-price">
                        Delete
                    </button>
                </td>

            </tr>
        `);

        saveDraft();

    }
);
    // REMOVE CHILD
    $(document).on(
        'click',

        '.btn-remove-child',
        function () {
            $(this)
                .closest('tr')
                .remove();
        });
        updateSummary();
        $(document).on(
    'click',
    '.btn-remove-sub-price',
    function () {

        $(this)
            .closest('tr')
            .remove();
    updateSummary();
        saveDraft();

    }
);
    // save bom
    $('#btn-save-bom').click(function () {

        let formData = new FormData();

        formData.append(
            '_token',
            '{{ csrf_token() }}'
        );

        formData.append(
            'bom',
            JSON.stringify(
                collectBomData()
            )
        );

        let image =
            $('#bom_image')[0]
            .files[0];

        if (image) {

            formData.append(
                'image',
                image
            );

        }

        $.ajax({

            url: "{{ route('bom.store') }}",

            type: 'POST',

            data: formData,

            processData: false,

            contentType: false,

            success: function (res) {

                alert(
                    'BOM berhasil disimpan'
                );

            }

        });

    });

    function saveDraft() {
        let draft = collectBomData();
        localStorage.setItem(
            'bom_draft',
            JSON.stringify(draft)
        );
    }
    $(document).on(
    'input change',
    '.qty, .material-price',

    function () {

        let row = $(this).closest('tr');

        calculateRow(row);
        updateSummary();
        saveDraft();

    }
);
   $(document).on(
    'keyup change',
    '.qty, .material-price',
    function(){

        calculateRow(
            $(this).closest('tr')
        );
updateSummary();
    }
);
    $(document).ready(function () {
        let draft =
            localStorage.getItem(
                'bom_draft'
            );
        if (!draft) {
            return;
        }
        draft =
            JSON.parse(draft);
        console.log(draft);
    });

   function renderDraft(draft) {
    // isi pertama
    $('[name="item"]').val(draft.name || '');

    $('[name="article_code"]').val(draft.article_number || '');

    $('[name="panjang"]').val(draft.panjang || '');
    $('[name="lebar"]').val(draft.lebar || '');
    $('[name="tinggi"]').val(draft.tinggi || '');

    $('[name="carton_panjang"]').val(draft.carton_panjang || '');
    $('[name="carton_lebar"]').val(draft.carton_lebar || '');
    $('[name="carton_tinggi"]').val(draft.carton_tinggi || '');

    $('[name="loadability_pcs"]').val(draft.loadability_pcs || '');
    $('[name="loadability_cbm"]').val(draft.loadability_cbm || '');
        $('#bom-sections').html('');

    draft.groups.forEach(function (group) {

        let html = `
        <div class="bom-section card mb-3">

            <div class="card-header bg-success text-white">

                <div class="row">

                    <div class="col-md-8">

                        <input
                            type="text"
                            class="form-control section-name"
                            value="${group.name}">

                    </div>

                    <div class="col-md-4 text-right">

                        <button
                            type="button"
                            class="btn btn-primary btn-sm btn-add-child">
                            Add Child
                        </button>

                        <button
                            type="button"
                            class="btn btn-success btn-sm btn-add-sub-price">
                            Add Sub Harga
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger btn-sm btn-remove-header">
                            Delete Header
                        </button>

                    </div>

                </div>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th width="35%">Material</th>
                            <th width="15%">Qty</th>
                            <th width="10%">Satuan</th>
                            <th width="10%">Harga</th>
                            <th width="10%">Total</th>
                            <th width="20%">Keterangan</th>

                            <th width="10%">Action</th>

                        </tr>

                    </thead>

                    <tbody class="child-body"></tbody>

                    <tbody class="sub-price-body"></tbody>

                </table>

            </div>

        </div>
        `;

        $('#bom-sections').append(html);

        let section = $('#bom-sections .bom-section').last();

        let childBody = section.find('.child-body');

        let subBody = section.find('.sub-price-body');

        // ==========================
        // MATERIAL
        // ==========================
        (group.items || []).forEach(function (item) {

            childBody.append(`
<tr>

    <td>

        <input
            type="hidden"
            class="material-id"
            value="${item.material_id || ''}">

        <input
            type="hidden"
            class="material-type"
            value="${item.material_type || ''}">

        <input
            type="text"
            readonly
            class="form-control material-picker"
            value="${item.name || ''}">

    </td>
    <td>

        <input
            type="number"
            step="0.0001"
            class="form-control qty"
            value="${item.qty || ''}">

    </td>

    <td>

        <input
            type="text"
            class="form-control unit"
            value="${item.unit || ''}">

    </td>

    <td>

        <input
            type="number"
            class="form-control material-price"
            value="${item.price || 0}">

    </td>

    <td>

        <input
            type="number"
            readonly
            class="form-control material-total"
            value="${item.total || 0}">

    </td>

    <td>

        <input
            type="text"
            class="form-control specification"
            value="${item.notes || ''}">

    </td>
    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-child">

            Delete

        </button>

    </td>

</tr>
            `);

        });

        // ==========================
        // SUB HARGA
        // ==========================
        (group.sub_prices || []).forEach(function (sub) {

            subBody.append(`
<tr class="table-success sub-price-row">

    <td colspan="5">

        <input
            type="text"
            class="form-control sub-price-name"
            value="${sub.name || ''}"
            placeholder="Contoh : JASA + ANYAM Pak Sumantri">

    </td>

    <td>

        <input
            type="number"
            class="form-control sub-price-value"
            value="${sub.price || 0}">

    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-sub-price">

            Delete

        </button>

    </td>

</tr>
            `);

        });

    });
    $('#summary-body').html('');

(draft.summaries || []).forEach(function(summary){

    $('#summary-body').append(`
<tr class="summary-row">

    <td>

        <input
            type="text"
            class="form-control summary-name"
            value="${summary.name || ''}">

    </td>

    <td>

        <input
            type="text"
            class="form-control summary-remark"
            value="${summary.remark || ''}">

    </td>

    <td>

        <input
            type="number"
            class="form-control summary-qty"
            value="${summary.qty || 1}">

    </td>

    <td>

        <input
            type="number"
            class="form-control summary-price"
            value="${summary.price || 0}">

    </td>

    <td>

        <input
            type="number"
            readonly
            class="form-control summary-total"
            value="${summary.total || 0}">

    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">

            Delete

        </button>

    </td>

</tr>
`);

});

$(document).on(
    'input',
    '.summary-name,.summary-remark,.summary-qty,.summary-price',
    function(){

        let row = $(this).closest('tr');

        let qty =
            parseFloat(
                row.find('.summary-qty').val()
            ) || 0;

        let price =
            parseFloat(
                row.find('.summary-price').val()
            ) || 0;

        row.find('.summary-total')
            .val(qty * price);

        saveDraft();

    }
);
$('.child-body tr').each(function(){

    calculateRow($(this));

});
updateTotalHpp();   // <-- di sini
updateSummary();
}


  function collectBomData() {

    let groups = [];

    $('.bom-section').each(function () {

        let group = {

            name: $(this)
                .find('.section-name')
                .val(),

            items: [],

            sub_prices: []

        };

        // ==========================
        // MATERIAL
        // ==========================
        $(this)
            .find('.child-body tr')
            .each(function () {

                group.items.push({

                    material_id: $(this)
                        .find('.material-id')
                        .val(),

                    material_type: $(this)
                        .find('.material-type')
                        .val(),

                    name: $(this)
                        .find('.material-picker')
                        .val(),

                    qty: $(this)
                        .find('.qty')
                        .val(),

                    price: $(this)
                        .find('.material-price')
                        .val(),

                    unit: $(this)
                        .find('.unit')
                        .val(),

                    total: $(this)
                        .find('.material-total')
                        .val(),

                    notes: $(this)
                        .find('.specification')
                        .val()

                });

            });

        // ==========================
        // SUB HARGA
        // ==========================
        $(this)
            .find('.sub-price-body tr')
            .each(function () {

                group.sub_prices.push({

                    name: $(this)
                        .find('.sub-price-name')
                        .val(),

                    price: $(this)
                        .find('.sub-price-value')
                        .val()

                });

            });

        groups.push(group);

    });

    let summaries = [];
    $('#summary-body tr').each(function(){

        summaries.push({

            name: $(this)
                .find('.summary-name')
                .val(),

            remark: $(this)
                .find('.summary-remark')
                .val(),

            qty: $(this)
                .find('.summary-qty')
                .val(),

            price: $(this)
                .find('.summary-price')
                .val(),

            total: $(this)
                .find('.summary-total')
                .val()

        });

    });
    return {

        name: $('[name="item"]').val(),

        article_number: $('[name="article_code"]').val(),

        panjang: $('[name="panjang"]').val(),

        lebar: $('[name="lebar"]').val(),

        tinggi: $('[name="tinggi"]').val(),

        carton_panjang: $('[name="carton_panjang"]').val(),

        carton_lebar: $('[name="carton_lebar"]').val(),

        carton_tinggi: $('[name="carton_tinggi"]').val(),

        loadability_pcs: $('[name="loadability_pcs"]').val(),

        loadability_cbm: $('[name="loadability_cbm"]').val(),

        groups: groups,
        summaries: summaries

    };

}
    $(document).ready(function () {
        let draft = localStorage.getItem('bom_draft');
        if (!draft) {
            return;
        }
        draft = JSON.parse(draft);
        console.log('draft loaded', draft);
        renderDraft(draft);
    });

    // edit bom
    $(document).on(
        'click',
        '#btn-update-bom',
        function () {

            let formData =
                new FormData();

            formData.append(
                '_token',
                '{{ csrf_token() }}'
            );

            formData.append(
                'bom',
                JSON.stringify(
                    collectBomData()
                )
            );

            let image =
                $('#bom_image')[0]
                .files[0];

            if (image) {

                formData.append(
                    'image',
                    image
                );

            }

            $.ajax({

                url: '/bom/update/' + bomId,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function (res) {

                    alert(
                        'BOM berhasil diupdate'
                    );

                },

                error: function (xhr) {

                    console.log(
                        xhr.responseText
                    );

                }

            });

        });
function updateSummary(){

    let labour = 0;

    let material = 0;

    $('.sub-price-value').each(function(){

        labour += parseFloat($(this).val()) || 0;

    });

    $('.material-total').each(function(){

        material += parseFloat($(this).val()) || 0;

    });

    $('#labour-total').text(
        labour.toLocaleString(
            'id-ID',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        )
    );

    $('#material-total-all').text(
        material.toLocaleString(
            'id-ID',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        )
    );

}
// add summary
$('#summary-body').append(`
<tr class="summary-row">

    <td>
        <input
            type="text"
            class="form-control summary-name"
            placeholder="LABOUR / MATERIAL">
    </td>

    <td>
        <input
            type="text"
            class="form-control summary-remark"
            placeholder="Remark">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-qty"
            value="1">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-price"
            value="0">
    </td>

    <td>
        <input
            type="number"
            readonly
            class="form-control summary-total"
            value="0">
    </td>

    <td>
        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">
            Delete
        </button>
    </td>

</tr>
`);
$(document).on(
    'input',
    '.summary-qty, .summary-price',
    function () {

        let row = $(this).closest('tr');

        let qty = parseFloat(row.find('.summary-qty').val()) || 0;
        let price = parseFloat(row.find('.summary-price').val()) || 0;

        row.find('.summary-total').val(qty * price);

        updateTotalHpp();
        saveDraft();

    }
);
$(document).on('click','.btn-remove-summary',function(){

    $(this).closest('tr').remove();

    updateTotalHpp();
    saveDraft();

});
$(document).on(
    'click',
    '#btn-add-summary',
    function(){

        $('#summary-body').append(`
<tr class="summary-row">

    <td>
        <input
            type="text"
            class="form-control summary-name">
    </td>

    <td>
        <input
            type="text"
            class="form-control summary-remark">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-qty"
            value="1">
    </td>

    <td>
        <input
            type="number"
            class="form-control summary-price"
            value="0">
    </td>

    <td>
        <input
            type="number"
            readonly
            class="form-control summary-total"
            value="0">
    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-summary">

            Delete

        </button>

    </td>

</tr>
`);

        saveDraft();

    }
);
</script>
<script>
    $('#bom_image').on(
        'change',
        function (e) {

            let file =
                e.target.files[0];

            if (!file) {
                return;
            }

            let reader =
                new FileReader();

            reader.onload =
                function (event) {

                    $('#preview').attr(
                        'src',
                        event.target.result
                    );

                };

            reader.readAsDataURL(
                file
            );

        }
    );
        // hitung box
    function hitungRemark() {

    let c = parseFloat($('[name="carton_panjang"]').val()) || 0;
    let d = parseFloat($('[name="carton_lebar"]').val()) || 0;
    let e = parseFloat($('[name="carton_tinggi"]').val()) || 0;

    return (
        (c / 100 * e / 100 * 2) +
        (d / 100 * e / 100 * 2) +
        (d / 100 * c / 100 * 2) +
        (0.25 * d / 100 * 4)
    );

}

$(document).on('change', '.summary-remark', function () {

    let remark = $(this).val().trim().toLowerCase();

    if (remark === 'hitung') {

        let hasil = hitungRemark();

        $(this).val(hasil.toFixed(3)); // ganti tulisan "hitung" menjadi angka

        saveDraft();

    }

});
$(document).on(
    'input',
    '.summary-remark, .summary-qty',
    function () {

        let row = $(this).closest('tr');

        let remark = row.find('.summary-remark').val().toLowerCase().trim();

        let qty = parseFloat(row.find('.summary-qty').val()) || 0;

        if (remark === 'hitung') {

            let harga = hitungLuas();

            row.find('.summary-price').val(harga.toFixed(3));

            row.find('.summary-total').val((qty * harga).toFixed(3));

        } else {

            let price = parseFloat(row.find('.summary-price').val()) || 0;

            row.find('.summary-total').val((qty * price).toFixed(3));

        }

    }
);
function hitungLuas() {

    let c = parseFloat($('[name="carton_panjang"]').val()) || 0;
    let d = parseFloat($('[name="carton_lebar"]').val()) || 0;
    let e = parseFloat($('[name="carton_tinggi"]').val()) || 0;

    return (
        (c / 100 * e / 100 * 2) +
        (d / 100 * e / 100 * 2) +
        (d / 100 * c / 100 * 2) +
        (0.25 * d / 100 * 4)
    );

}
function updateTotalHpp(){

    let labour = 0;
    let material = 0;
    let summary = 0;

    // Total Labour
    $('.sub-price-value').each(function(){
        labour += parseFloat($(this).val()) || 0;
    });

    // Total Material
    $('.material-total').each(function(){
        material += parseFloat($(this).val()) || 0;
    });

    // Total Summary
    $('.summary-total').each(function(){
        summary += parseFloat($(this).val()) || 0;
    });

    let totalHpp = labour + material + summary;

    $('#total-hpp').val(
        totalHpp.toLocaleString('id-ID',{
            minimumFractionDigits:2,
            maximumFractionDigits:2
        })
    );

}
</script>
<style>
    .card{
    border:none;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    overflow:hidden;
    margin-bottom:25px;
}

.card-header{
    background:#fff !important;
    border-bottom:1px solid #edf1f5;
    padding:18px 24px;
    font-weight:600;
}

.card-body{
    padding:25px;
}
.form-control{
    border:1px solid #dfe6ee;
    border-radius:10px;
    height:42px;
    box-shadow:none;
    transition:.25s;
    font-size:14px;
}

.form-control:focus{

    border-color:#28a745;

    box-shadow:0 0 0 .18rem rgba(40,167,69,.15);

}
.table{

    margin-bottom:0;

}

.table thead th{

    background:#f8fafc;

    border:none;

    color:#555;

    font-size:13px;

    font-weight:600;

}

.table td{

    border-top:1px solid #eef2f7;

    vertical-align:middle;

}
.bg-success{

    background:linear-gradient(90deg,#28a745,#32b768)!important;

}
.btn{

    border-radius:8px;

    font-size:13px;

    font-weight:600;

    padding:7px 16px;

    transition:.2s;

}

.btn:hover{

    transform:translateY(-1px);

}

.btn-success{

    background:#2ea44f;

    border:none;

}

.btn-primary{

    background:#2979ff;

    border:none;

}

.btn-danger{

    border:none;

}
#preview{

    width:180px;

    height:180px;

    object-fit:contain;

    background:#fafafa;

    border-radius:12px;

    padding:10px;

    border:2px dashed #d8dfe7 !important;

}

.border.p-3{

    border:2px dashed #dbe5ef!important;

    border-radius:12px;

    background:#fafafa;

}
.bom-section{

    border-radius:14px;

    overflow:hidden;

    border:none;

    box-shadow:0 5px 18px rgba(0,0,0,.05);

}
#summary-body tr{

    transition:.2s;

}

#summary-body tr:hover{

    background:#f8fff9;

}
.table-success{

    background:#eefbf3!important;

}

#total-hpp{

    font-weight:bold;

    color:#28a745;

    font-size:17px;

    text-align:right;

    background:#fff;

}
.row{

    margin-bottom:10px;

}

.col-md-4,
.col-md-6{

    margin-bottom:10px;

}
.table-responsive{

    border-radius:12px;

    overflow:hidden;

}
body{

    background:#f4f6f9;

}
    </style>
