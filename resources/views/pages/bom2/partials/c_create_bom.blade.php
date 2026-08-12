    @include('pages.bom2.partials.c_bom_style')

    <div class="container-fluid">
        {{-- HEADER BOM --}}
        <div class="card-header d-flex justify-content-between align-items-center">

            @if (isset($bom))
                <h5 class="mb-0">EDIT BOM</h5>
            @else
                <h5 class="mb-0">CREATE BOM</h5>

                <button type="button" id="btn-clear-draft" class="btn btn-warning btn-sm">

                    <i class="fa fa-refresh"></i>
                    Refresh Draft

                </button>
            @endif

        </div>
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
                                    <input type="number" class="form-control" placeholder="Lebar" name="carton_lebar">
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

                                    <input type="number" name="loadability_pcs" class="form-control" placeholder="PCS">

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

                <div id="upload-area" class="border p-3 text-center"
                    style="
                        cursor:pointer;
                        border:2px dashed #cfd8dc !important;
                        border-radius:12px;
                        min-height:260px;
                        display:flex;
                        flex-direction:column;
                        justify-content:center;
                        align-items:center;
                    ">

                    <input type="file" id="bom_image" name="image" accept="image/*" hidden>

                    <img id="preview" src="https://placehold.co/300x200" class="img-fluid"
                        style="
                            width:180px;
                            height:180px;
                            object-fit:contain;
                        ">

                    <small class="text-muted mt-2">
                        Klik, Drag & Drop atau Ctrl + V
                    </small>

                </div>

            </div>
        </div>
    </div>
    </div>
    {{-- LABOUR & MATERIAL --}}
    @if (isset($bom))
        <button type="button" class="btn btn-warning btn-sm" id="btn-update-bom">

            Update BOM

        </button>
        <button type="button" class="btn btn-primary btn-sm" id="btn-copy-bom">

            Copy BOM ?

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
                <button type="button" class="btn btn-success btn-sm" id="btn-add-summary">
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
                        <input type="text" id="total-hpp" class="form-control" readonly value="0">
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

                    <div>
                        <h5 class="modal-title mb-0">
                            Pilih Material
                        </h5>

                        <small class="text-muted">
                            Pilih material yang akan digunakan pada BOM
                        </small>
                    </div>

                    <div class="d-flex align-items-center">

                        <span class="text-muted mr-2">
                            Material tidak ada?
                        </span>

                        <button type="button" class="btn btn-success btn-sm" id="btnAddMaterial">

                            <i class="fa fa-plus"></i>
                            Tambah Material

                        </button>

                        <button type="button" class="close ml-3" data-dismiss="modal">

                            <span>&times;</span>

                        </button>

                    </div>

                </div>

                <div class="modal-body">
                    <input type="text" id="searchMasterMaterial" class="form-control mb-3"
                        placeholder="Cari material...">
                    <table class="table table-bordered" id="materialMasterTable">
                        <thead>
                            <tr>
                                <th width="80">
                                    Pilih
                                </th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>harga</th>
                            </tr>
                        </thead>
                        <tbody id="materialMasterBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form id="formAddMaterial">

                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Tambah Material
                        </h5>

                        <button type="button" class="close" data-dismiss="modal">

                            <span>&times;</span>

                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-info mb-3">
                            Format:
                            <br>
                            <b>Nama Material,Harga,Satuan</b>
                            <br><br>

                            Contoh:
                            <br>
                            Rotan Sintetis,25000,KG
                            <br>
                            Cushion,120000,PCS
                        </div>

                        <textarea class="form-control" id="materials" name="materials" rows="8"
                            placeholder="Nama Material,Harga,Satuan"></textarea>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">

                            Batal

                        </button>

                        <button type="submit" class="btn btn-success">

                            Simpan

                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
    <script src="https://jquery.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <!-- 2. Bootstrap Next -->
    {{-- <script src="https://jsdelivr.net"></script> --}}

    <script>
        console.time('Page Load');
    </script>
    <script>
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

        function updateTotalHpp() {

            let labour = 0;
            let material = 0;
            let summary = 0;

            $('.sub-price-value').each(function() {

                labour += parseFloat(
                    unFormat($(this).val())
                ) || 0;

            });

            $('.material-total').each(function() {

                material += parseFloat(
                    unFormat($(this).val())
                ) || 0;

            });

            $('.summary-total').each(function() {

                summary += parseFloat(
                    unFormat($(this).val())
                ) || 0;

            });

            let totalHpp = labour + material + summary;

            $('#total-hpp').val(
                totalHpp.toLocaleString('id-ID')
            );
            console.log($('#total-hpp').val());
        }
        // rumus loadabiity
        function updateDimensionCalculation() {

            let p = parseFloat($('[name="panjang"]').val()) || 0;
            let l = parseFloat($('[name="lebar"]').val()) || 0;
            let t = parseFloat($('[name="tinggi"]').val()) || 0;

            // Auto Carton
            let cp = p + 3;
            let cl = l + 3;
            let ct = t + 5;

            $('[name="carton_panjang"]').val(cp.toFixed(2));
            $('[name="carton_lebar"]').val(cl.toFixed(2));
            $('[name="carton_tinggi"]').val(ct.toFixed(2));

            // Hitung CBM
            let cbm = (cp * cl * ct) / 1000000;

            $('[name="loadability_cbm"]').val(cbm.toFixed(2));

            // Hitung Loadability
            if (cbm > 0) {

                let loadability = Math.round(65 / cbm);

                $('[name="loadability_pcs"]').val(loadability);

            } else {

                $('[name="loadability_pcs"]').val('');

            }

            saveDraft();
        }
        $(document).on(
            'input',
            '[name="panjang"], [name="lebar"], [name="tinggi"]',
            function() {

                updateDimensionCalculation();

            }
        );

        function formatNumber(value) {

            if (value === '' || value === null) return '';

            return Number(value).toLocaleString('id-ID');

        }

        function unFormat(value) {

            if (value === null || value === undefined || value === '') {
                return 0;
            }

            value = value.toString().trim();

            // Kalau ada koma berarti format Indonesia
            if (value.includes(',')) {
                value = value.replace(/\./g, '').replace(',', '.');
                return parseFloat(value) || 0;
            }

            // Kalau titik diikuti tepat 3 digit di akhir → separator ribuan
            if (/\.\d{3}$/.test(value)) {
                value = value.replace(/\./g, '');
                return parseFloat(value) || 0;
            }

            // Selain itu anggap titik adalah desimal
            return parseFloat(value) || 0;
        }
    </script>
    <script>
        let activeMaterialInput = null;
        let sectionIndex = 0;
        {{-- let activeMaterialInput = null; --}}

        $(document).on('click', '.material-picker', function() {

            activeMaterialInput = $(this);

            loadMaterialMaster();

            $('#materialPickerModal').modal('show');

        });
        // pili material dari modal
        $(document).on('click', '.btn-select-material', function() {

            if (!activeMaterialInput) {
                console.error('activeMaterialInput null');
                return;
            }

            let btn = $(this);

            let id = btn.data('id');
            let nama = btn.data('name');
            let type = btn.data('type');
            let price = btn.data('price') || 0;
            let unit = btn.data('unit') || '';

            let row = activeMaterialInput.closest('tr');

            activeMaterialInput.val(nama);

            row.find('.material-id').val(id);
            row.find('.material-type').val(type);
            row.find('.material-price').val(formatNumber(price));
            row.find('.unit').val(unit);

            calculateRow(row);
            updateTotalHpp();

            // pindahkan fokus ke input
            activeMaterialInput.trigger('focus');

            // baru tutup modal
            $('#materialPickerModal').modal('hide');

        });

        function calculateRow(row) {

            let qty = parseFloat(
                row.find('.qty').val()
            ) || 0;

            let price = unFormat(
                row.find('.material-price').val()
            );

            // console.log({
            //     material: row.find('.material-picker').val(),
            //     qty,
            //     price,
            //     raw: row.find('.material-price').val()
            // });

            let total = qty * price;

            row.find('.material-total').val(
                total.toLocaleString('id-ID')
            );

        }
        // search material di modal
        $('#searchMasterMaterial').on(
            'keyup',
            function() {
                let keyword =
                    $(this)
                    .val()
                    .toLowerCase();
                $('#materialMasterTable tbody tr')
                    .each(function() {
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
        $('#btn-add-header').click(function() {
            sectionIndex++;
            let html = `
    <div class="bom-section card mb-3">

    <div class="card-header bg-success text-white">

        <div class="row align-items-center">

            <div class="col-auto">
                <i class="fa fa-bars drag-header"
                    style="cursor:grab;font-size:18px;"></i>
            </div>

            <div class="col">
                <input
                    type="text"
                    class="form-control section-name"
                    placeholder="Nama Header"
                    value="RANGKA ROTAN">
            </div>

            <div class="col-auto text-right">

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
        </div>
    `;
            $('#bom-sections').append(html);
            initSortable();
            saveDraft();
        });
        // REMOVE HEADER
        $(document).on(
            'click',
            '.btn-remove-header',
            function() {
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
            function() {
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
        type="text"
        class="form-control material-price"
        value="0">

</td>

<td>

    <input
        type="text"
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
            function() {

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
                        type="text"
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
            function() {
                $(this)
                    .closest('tr')
                    .remove();
            });
        updateSummary();
        $(document).on(
            'click',
            '.btn-remove-sub-price',
            function() {

                $(this)
                    .closest('tr')
                    .remove();
                updateSummary();
                saveDraft();

            }
        );
        $(document).on('blur', '.material-price', function() {

            let angka = unFormat($(this).val());

            $(this).val(
                formatNumber(angka)
            );

            calculateRow($(this).closest('tr'));

            updateSummary();
            updateTotalHpp();
            saveDraft();

        });
        $(document).on('blur', '.sub-price-value', function() {

            let angka = unFormat($(this).val());

            $(this).val(formatNumber(angka));

            updateSummary();
            updateTotalHpp();
            saveDraft();

        }); // save bom
        $('#btn-save-bom').click(function() {

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

                url: "/bom-produksi/store",

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function(res) {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Saved BOM, Duar..',
                        showConfirmButton: false,
                        timer: 500,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });

                }

            });

        });
        // image
        function loadImage(file) {

            if (!file) return;

            let reader = new FileReader();

            reader.onload = function(e) {

                $('#preview').attr('src', e.target.result);

            };

            reader.readAsDataURL(file);

            // Supaya file tetap ikut saat submit FormData
            let dt = new DataTransfer();
            dt.items.add(file);

            $('#bom_image')[0].files = dt.files;

        }
        $('#upload-area').on('click', function() {

            $('#bom_image').click();

        });
        $('#bom_image').on('change', function() {

            if (this.files.length) {

                loadImage(this.files[0]);

            }

        });
        $('#upload-area')

            .on('dragover', function(e) {

                e.preventDefault();

                $(this).css('border-color', '#28a745');

            })

            .on('dragleave', function() {

                $(this).css('border-color', '#cfd8dc');

            })

            .on('drop', function(e) {

                e.preventDefault();

                $(this).css('border-color', '#cfd8dc');

                let file = e.originalEvent.dataTransfer.files[0];

                if (file) {

                    loadImage(file);

                }

            });
        $(document).on('paste', function(e) {

            let clipboardData = e.originalEvent.clipboardData || window.clipboardData;

            if (!clipboardData) return;

            let items = clipboardData.items;

            if (!items) return;

            for (let i = 0; i < items.length; i++) {

                let item = items[i];

                if (item.kind === 'file' && item.type.startsWith('image/')) {

                    let file = item.getAsFile();

                    if (!file) continue;

                    loadImage(file);

                    e.preventDefault();

                    return;
                }
            }

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

            function() {

                let row = $(this).closest('tr');

                calculateRow(row);
                updateSummary();
                saveDraft();

            }
        );
        $(document).on(
            'keyup change',
            '.qty, .material-price',
            function() {

                calculateRow(
                    $(this).closest('tr')
                );
                updateTotalHpp();
                updateSummary();
            }
        );

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

            draft.groups.forEach(function(group) {

                let html = `
        <div class="bom-section card mb-3">

        <div class="card-header bg-success text-white">

    <div class="row align-items-center">

        <div class="col-auto">
            <i class="fa fa-bars drag-header"
               style="cursor:grab;font-size:18px;"></i>
        </div>

        <div class="col">

            <input
                type="text"
                class="form-control section-name"
                value="${group.name}">

        </div>

        <div class="col-auto text-right">

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
                (group.items || []).forEach(function(item) {

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
    type="text"
    class="form-control material-price"
    value="${formatNumber(item.price ?? 0)}">

    </td>

    <td>

       <input
        type="text"
        readonly
        class="form-control material-total"
        value="${Number(item.total || 0).toLocaleString('id-ID')}">

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
                (group.sub_prices || []).forEach(function(sub) {

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
    type="text"
    class="form-control sub-price-value"
    value="${Number(sub.price || 0).toLocaleString('id-ID')}">

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
            updateDimensionCalculation();
            $('#summary-body').html('');

            (draft.summaries || []).forEach(function(summary) {

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
            type="text"
            class="form-control summary-price"
            value="${Number(summary.price || 0).toLocaleString('id-ID')}">

    </td>

    <td>

        <input
            type="text"
            readonly
            class="form-control summary-total"
            value="${Number(summary.total || 0).toLocaleString('id-ID')}">

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


            $('.child-body tr').each(function() {

                calculateRow($(this));

            });
            updateTotalHpp(); // <-- di sini
            updateSummary();

        }


        function collectBomData() {

            let groups = [];

            $('.bom-section').each(function() {

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
                    .each(function() {

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
                                .val()
                                .replace(/\./g, ''),

                            unit: $(this)
                                .find('.unit')
                                .val(),

                            total: $(this)
                                .find('.material-total')
                                .val()
                                .replace(/\./g, ''),

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
                    .each(function() {

                        group.sub_prices.push({

                            name: $(this)
                                .find('.sub-price-name')
                                .val(),

                            price: unFormat(
                                $(this)
                                .find('.sub-price-value')
                                .val()
                            )
                        });

                    });

                groups.push(group);

            });

            let summaries = [];
            $('#summary-body tr').each(function() {

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

                    price: unFormat(
                        $(this)
                        .find('.summary-price')
                        .val()
                    ),

                    total: unFormat(
                        $(this)
                        .find('.summary-total')
                        .val()
                    ),

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
        // init
        function initSortable() {

            const el = document.getElementById('bom-sections');

            if (!el) return;

            if (el.sortableInstance) {
                el.sortableInstance.destroy();
            }

            el.sortableInstance = new Sortable(el, {
                animation: 150,
                ghostClass: 'bg-warning',
                handle: '.drag-header',

                onEnd() {
                    saveDraft();
                }
            });

        }
        $(document).ready(function() {
            initSortable();
            let draft = localStorage.getItem('bom_draft');
            if (!draft) {
                return;
            }
            draft = JSON.parse(draft);
            // console.log('draft loaded', draft);
            renderDraft(draft);

        });

        // edit bom
        $(document).on(
            'click',
            '#btn-update-bom',
            function() {

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

                    url: '/bom-produksi/update/' + bomId,

                    type: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    success: function(res) {

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'pantun dulu, jalan jalan ke bekasi.. cakepp',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            location.reload();
                        });

                    },

                    error: function(xhr) {

                        console.log(
                            xhr.responseText
                        );

                    }

                });

            });

        function updateSummary() {

            let labour = 0;
            let material = 0;

            $('.sub-price-value').each(function() {

                labour += parseFloat(
                    unFormat($(this).val())
                ) || 0;

            });

            $('.material-total').each(function() {

                material += parseFloat(
                    unFormat($(this).val())
                ) || 0;

            });

            $('#labour-total').text(
                labour.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            );

            $('#material-total-all').text(
                material.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
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
            type="text"
            class="form-control summary-price"
            value="0">
    </td>

    <td>
        <input
            type="text"
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
            function() {

                let row = $(this).closest('tr');

                let qty = parseFloat(row.find('.summary-qty').val()) || 0;

                let price = parseFloat(
                    unFormat(
                        row.find('.summary-price').val()
                    )
                ) || 0;

                let total = qty * price;

                row.find('.summary-total').val(
                    formatNumber(total)
                );
                updateTotalHpp();
                saveDraft();

            }
        );
        $(document).on('click', '.btn-remove-summary', function() {

            $(this).closest('tr').remove();

            updateTotalHpp();
            saveDraft();

        });
        $(document).on(
            'click',
            '#btn-add-summary',
            function() {

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
            function(e) {

                let file =
                    e.target.files[0];

                if (!file) {
                    return;
                }

                let reader =
                    new FileReader();

                reader.onload =
                    function(event) {

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

        $(document).on('change', '.summary-remark', function() {

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
            function() {

                let row = $(this).closest('tr');

                let remark = row.find('.summary-remark').val().toLowerCase().trim();

                let qty = parseFloat(
                    row.find('.summary-qty').val()
                ) || 0;

                let price = 0;

                if (remark === 'hitung') {

                    price = hitungLuas();

                    row.find('.summary-price').val(
                        formatNumber(Math.round(price))
                    );

                } else {

                    price = parseFloat(
                        unFormat(
                            row.find('.summary-price').val()
                        )
                    ) || 0;

                }

                let total = qty * price;

                row.find('.summary-total').val(
                    formatNumber(Math.round(total))
                );

                updateTotalHpp();
                saveDraft();

            }
        );

        // $(document).on('input', '.material-price', function () {

        //     calculateRow($(this).closest('tr'));

        //     updateSummary();

        //     updateTotalHpp();

        // });
        $(document).on('blur', '.material-price', function() {

            let angka = unFormat($(this).val());

            $(this).val(
                angka.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 4
                })
            );

            calculateRow($(this).closest('tr'));
            updateSummary();
            updateTotalHpp();
        })
        $(document).on('blur', '.material-price', function() {

            saveDraft();

        });
        $(document).on('input', '.summary-price', function() {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.summary-qty').val()) || 0;

            let price = unFormat($(this).val());

            row.find('.summary-total').val(
                formatNumber(qty * price)
            );

            updateTotalHpp();
            saveDraft();

        });
        $(document).on('click', '#btn-copy-bom', function() {

            Swal.fire({

                title: 'Copy BOM?',
                text: 'BOM ini akan diduplikasi menjadi BOM baru.',
                icon: 'question',

                showCancelButton: true,

                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',

                confirmButtonText: 'Ya, Copy',
                cancelButtonText: 'Batal'

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                let formData = new FormData();

                formData.append('_token', '{{ csrf_token() }}');

                formData.append(
                    'bom',
                    JSON.stringify(collectBomData())
                );

                let image = $('#bom_image')[0].files[0];

                if (image) {
                    formData.append('image', image);
                }

                $.ajax({

                    url: "/bom-produksi/bom/copy",

                    type: "POST",

                    data: formData,

                    processData: false,

                    contentType: false,

                    beforeSend: function() {

                        Swal.fire({

                            title: 'Sedang menyalin...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,

                            didOpen: () => {
                                Swal.showLoading();
                            }

                        });

                    },

                    success: function(res) {

                        Swal.fire({

                            icon: "success",

                            title: "Berhasil",

                            text: res.message,

                            confirmButtonText: "OK"

                        }).then(() => {

                            window.location = "/bom-produksi/";

                        });

                    },

                    error: function(xhr) {

                        Swal.fire({

                            icon: "error",

                            title: "Gagal",

                            text: xhr.responseJSON?.message ?? "Terjadi kesalahan."

                        });

                    }

                });

            });

        });
        $('#btn-clear-draft').on('click', function() {

            Swal.fire({
                title: 'Buat BOM baru?',
                text: 'Semua draft yang belum disimpan akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus Draft',
                cancelButtonText: 'Batal'

            }).then((result) => {

                if (!result.isConfirmed) return;

                localStorage.removeItem('bom_draft');

                location.reload();

            });

        });
    </script>
    <script>
        function loadMaterialMaster(keyword = '') {

            $.ajax({

                url: '/cog-material-price/ajax',

                type: 'GET',

                data: {
                    keyword: keyword
                },

                success: function(datas) {

                    let html = '';

                    datas.forEach(function(item) {

                        html += `
        <tr>

            <td>
                <button
                    class="btn btn-primary btn-sm btn-select-material"

                    data-id="${item.id}"
                    data-name="${item.name}"
                    data-type="${item.type}"
                    data-price="${item.price}"
                    data-unit="${item.unit}">

                    Pilih

                </button>
            </td>

            <td>${item.id}</td>

            <td>${item.name}</td>

            <td>${item.jenis}</td>

            <td>${formatNumber(item.price)}</td>

        </tr>
    `;

                    });

                    $('#materialMasterBody').html(html);

                }

            });


        }
        //add material 
        $('#btnAddMaterial').click(function() {

            $('#addMaterialModal').modal('show');

        });
        // save material 
        $('#formAddMaterial').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: '/cog-material-price/store',

                type: 'POST',

                data: $(this).serialize(),

                success: function(res) {

                    $('#addMaterialModal').modal('hide');

                    $('#formAddMaterial')[0].reset();

                    // refresh list material
                    loadMaterialMaster($('#searchMasterMaterial').val());

                }

            });

        });
    </script>
