@extends('master.master')

@section('title', 'Export View')

@section('content')
    @include('pages.exports.partials.style')
    <div style="zoom:80%;">

        {{-- semua isi halaman --}}
        <div class="container-fluid py-4">
            @if ($mode == 'create')
                <h4>Create Export IPL</h4>
            @else
                <h4>Edit Export IPL</h4>
            @endif
            {{-- Shipment Information --}}

            @include('pages.exports.partials.shipper')

            {{-- IPL TABLE --}}

            <div class="card shadow-sm ">

                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center ">

                    <h5 class="mb-0">
                        Invoice Packing List (IPL)
                    </h5>
                    <div class="d-flex gap-2">

                        <button type="button" class="btn btn-primary" id="btnAddPo">

                            <i class="fa fa-plus"></i>
                            Add PO

                        </button>

                        <button type="button" class="btn btn-success" id="btnSaveExport">

                            <i class="fa fa-save"></i>
                            Save IPL

                        </button>

                    </div>
                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover table-sm align-middle text-center">

                        <thead class="table-light">

                            <tr>

                                <th rowspan="2">No</th>
                                <th rowspan="2">HS Code</th>
                                <th rowspan="2">Photo</th>
                                <th rowspan="2">Description</th>
                                <th rowspan="2">Item Code</th>

                                <th colspan="4" class="table-secondary">
                                    Packing Information
                                </th>

                                <th colspan="2" class="table-secondary">
                                    Invoice
                                </th>

                                <th colspan="4" class="table-secondary">
                                    Shipping Information
                                </th>

                            </tr>

                            <tr>

                                <th>Box Dimension</th>
                                <th>Qty (PCS)</th>
                                <th>Qty (BOX)</th>
                                <th>CBM / Box</th>

                                <th>Unit Price</th>
                                <th>Total Price</th>

                                <th>Net Weight</th>
                                <th>Gross Weight</th>
                                <th>Total CBM</th>
                                <th>Remarks</th>
                                <th>PO No</th>

                                <th>act</th>

                            </tr>

                        </thead>

                        <tbody id="itemTableBody">

                            <tr>

                                <td colspan="14" class="text-center text-muted">

                                    Belum ada Sales Order dipilih

                                </td>

                            </tr>

                        </tbody>

                        <tfoot class="table-light fw-bold">

                            <tr>

                                <td colspan="6" class="text-end">TOTAL</td>

                                <td id="totalQtyPcs">0</td>

                                <td id="totalQtyBox">0</td>

                                <td id="totalCbmBox">0.000</td>

                                <td></td>

                                <td id="grandTotalPrice">$0.00</td>

                                <td id="totalNetWeight">0.00</td>

                                <td id="totalGrossWeight">0.00</td>

                                <td id="grandTotalCbm">0.000</td>

                                <td></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                                        title="Delete Item">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </td>
                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>
        {{-- modals --}}
        <!-- Modal Add PO -->
        <div class="modal fade" id="modalAddPo" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable">

                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">

                        <h5 class="modal-title">

                            <i class="fa fa-plus-circle me-2"></i>

                            Combine Sales Order

                        </h5>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        <!-- Search -->
                        <div class="row mb-3">

                            <div class="col-md-6 position-relative">

                                <label class="form-label fw-bold">
                                    Search Sales Order
                                </label>

                                <input type="text" class="form-control" id="searchCombinePo"
                                    placeholder="Contoh : 26-43">

                                <!-- dropdown search -->
                                <div id="combinePoResult" class="list-group position-absolute w-100 shadow"
                                    style="z-index:1055; display:none;">
                                </div>

                            </div>

                        </div>

                        <hr>

                        <table class="table table-bordered table-hover" id="combineItemTable">

                            <thead>

                                <tr>

                                    <th width="40">
                                        <input type="checkbox" id="checkAllCombine">
                                    </th>

                                    <th>Photo</th>

                                    <th>Sales Order</th>

                                    <th>Article</th>

                                    <th>Description</th>

                                    <th>Qty</th>

                                    <th>FOB</th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr id="emptyCombineItem">

                                    <td colspan="7" class="text-center text-muted">

                                        Belum ada item

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" id="btnRefreshCombine">

                            <i class="fas fa-sync-alt"></i>
                            Refresh

                        </button>

                        <button class="btn btn-secondary" data-bs-dismiss="modal">

                            Close

                        </button>

                        <button type="button" class="btn btn-success" id="btnAddCombine">

                            <i class="fa fa-check"></i>

                            Add To Table

                        </button>

                    </div>

                </div>

            </div>

        </div>
    @endsection

    @push('scripts')
        <script>
            // search modal
            function searchCombinePo(keyword) {

                if (keyword.length < 2) {

                    $('#combinePoResult').hide();

                    return;

                }

                $.ajax({

                    url: '/export/search-po',

                    type: 'GET',

                    data: {
                        keyword: keyword
                    },

                    success: function(data) {

                        let html = '';

                        data.forEach(function(item) {

                            html += `
                    <a href="#"
                        class="list-group-item list-group-item-action combine-po-item"

                        data-id="${item.id}"
                        data-order="${item.order_no}"
                        data-company="${item.company_name}"
                        data-country="${item.country}"
                        data-shipment="${item.shipment_date ?? ''}">

                        <strong>${item.order_no}</strong><br>

                        <small>${item.company_name}</small>

                    </a>
                `;

                        });

                        $('#combinePoResult').html(html).show();

                    }

                });

            }

            function searchPo(keyword) {

                if (keyword.length < 2) {

                    $('#poResult').hide();

                    return;

                }

                $.ajax({

                    url: '/export/search-po',

                    type: 'GET',

                    data: {
                        keyword: keyword
                    },

                    success: function(data) {

                        let html = '';

                        data.forEach(function(item) {

                            html += `
                <a href="#"
                    class="list-group-item list-group-item-action po-item"

                    data-id="${item.id}"
                    data-order="${item.order_no}"
                    data-company="${item.company_name}"
                    data-country="${item.country}">

                    <strong>${item.order_no}</strong><br>
                    <small>${item.company_name}</small>

                </a>`;

                        });

                        $('#poResult').html(html).show();

                    }

                });

            }
            $('#sales_order').on('keyup', function() {

                let value = $(this).val();

                // hanya angka
                value = value.replace(/\D/g, '');

                // 2643 -> 26-43
                if (value.length >= 4) {

                    value = value.substring(0, 2) + '-' + value.substring(2);

                }

                $(this).val(value);

                searchPo(value);

            });
            $(document).on('click', '.po-item', function(e) {

                e.preventDefault();

                $('#po_id').val($(this).data('id'));

                $('#sales_order').val($(this).data('order'));

                $('#buyer_name').val($(this).data('company'));
                loadItems($(this).data('id'));

                $('#poResult').hide();

            });

            function loadItems(poId) {

                $.get('/export/po-items/' + poId, function(items) {
                    console.log(items);

                    let html = '';

                    items.forEach(function(item, index) {

                        html += `

            <tr>
<td style="display:none">
   <input
    type="hidden"
    class="item-po-id"
    name="items[${index}][po_id]"
    value="${item.po_id}"> value="${item.po_id}">
    </td>
<input
    type="hidden"
    class="item-detail-id"
    name="items[${index}][detail_po_id]"
    value="${item.id}">
                <td>${index+1}</td>

                <td>
                    <input type="text"
                        class="form-control form-control-sm"
                        name="items[${index}][hs_code]">
                </td>

                <td>
                    <img src="${item.photo}" width="60" class="img-thumbnail">

                    <input type="hidden"
                        name="items[${index}][photo]"
                        value="${item.photo}">
                </td>

                <td>
                    <textarea
                        rows="2"
                        class="form-control form-control-sm"
                        name="items[${index}][description]">${item.description}</textarea>
                </td>

                <td>
                    <input
                        class="form-control form-control-sm"
                        name="items[${index}][article_nr]"
                        value="${item.article_nr}">
                </td>

                <td>
                    <input
                        type="text"
                        class="form-control form-control-sm box_dimension"
                        name="items[${index}][box_dimension]"
                        value="${item.pack_w} x ${item.pack_d} x ${item.pack_h}">
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control form-control-sm text-center qty_pcs"
                        name="items[${index}][qty_pcs]"
                        value="${item.qty}">
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control form-control-sm text-center qty_box"
                        name="items[${index}][qty_box]"
                        value="1">
                </td>

                <td>
                    <input
                        readonly
                        class="form-control form-control-sm text-end cbm"
                        name="items[${index}][cbm]">
                </td>

                <td>
                    <input
                        type="text"
                        class="form-control form-control-sm text-end unit_price"
                        name="items[${index}][unit_price]"
                        value="${formatCurrency(item.value)}">
                </td>

                <td>
                    <input
                        readonly
                        type="text"
                        class="form-control form-control-sm text-end total_price"
                        name="items[${index}][total_price]">
                </td>

               <td>
                    <input
                        type="number"
                        step="0.01"
                        class="form-control form-control-sm text-end net_weight"
                        name="items[${index}][net_weight]">
                </td>

                <td>
                    <input
                        type="number"
                        step="0.01"
                        class="form-control form-control-sm text-end gross_weight"
                        name="items[${index}][gross_weight]">
                </td>

                <td>
                    <input
                        readonly
                        class="form-control form-control-sm text-end total_cbm"
                        name="items[${index}][total_cbm]">
                </td>

                <td>
                    <input
                        class="form-control form-control-sm"
                        name="items[${index}][remark]">
                </td>
                <td>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        name="items[${index}][po_no]"
                        value="${item.order_no ?? ''}"
                        readonly>
                </td>
                <td class="text-center">

                <button
                    type="button"
                    class="btn btn-sm btn-danger remove-item"
                    title="Remove Item">

                    <i class="fas fa-times"></i>

                </button>

            </td>

            </tr>`;
                    });

                    $('#itemTableBody').html(html);

                    // Hitung semua baris setelah tabel dibuat
                    $('#itemTableBody tr').each(function() {
                        calculateRow($(this));
                        calculateFooter();
                        refreshSalesOrder();
                    });

                });

            }
            // load po items details
            function calculateRow(row) {

                let input = row.find('.box_dimension');

                if (!input.length) {
                    return;
                }

                let dimension = (input.val() || '').trim();

                if (dimension === '') {
                    return;
                }


                dimension = dimension
                    .replace(/×/g, 'x')
                    .replace(/X/g, 'x')
                    .replace(/\*/g, 'x')
                    .replace(/\s+/g, '');

                let parts = dimension.split('x');

                if (parts.length !== 3) {

                    row.find('.cbm').val('');
                    row.find('.total_cbm').val('');
                    return;

                }

                let p = parseFloat(parts[0]) || 0;
                let l = parseFloat(parts[1]) || 0;
                let t = parseFloat(parts[2]) || 0;

                let qtyBox = parseFloat(row.find('.qty_box').val()) || 0;
                let qtyPcs = parseFloat(row.find('.qty_pcs').val()) || 0;
                let unitPrice = parseCurrency(
                    row.find('.unit_price').val()
                );
                let cbm = (p * l * t) / 1000000;

                // CBM / Box
                row.find('.cbm').val(formatNumber(cbm, 2));

                // Total CBM
                row.find('.total_cbm').val(formatNumber(cbm * qtyBox, 2));

                row.find('.total_price').val(
                    formatCurrency(qtyPcs * unitPrice)
                );
                calculateFooter();
            }
            $(document).on(
                'input',
                '.box_dimension,.qty_box,.qty_pcs,.unit_price,.net_weight,.gross_weight',

                function() {

                    calculateRow($(this).closest('tr'));

                }
            );

            function formatNumber(number, decimal = 2) {

                return Number(number).toLocaleString('en-US', {
                    minimumFractionDigits: decimal,
                    maximumFractionDigits: decimal
                });

            }

            function formatDollar(number) {

                return '$' + formatNumber(number, 1);

            }

            function formatCurrency(value) {

                value = parseFloat(value) || 0;

                return '$' + value.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

            }

            function parseCurrency(value) {

                if (!value) return 0;

                value = String(value);

                // buang semua karakter selain angka, titik, koma, minus
                value = value.replace(/[^\d.,-]/g, '');

                // ubah format US
                value = value.replace(/,/g, '');

                return parseFloat(value) || 0;
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const collapse = document.getElementById('shipmentInformation');
                const icon = document.getElementById('shipmentIcon');

                collapse.addEventListener('shown.bs.collapse', function() {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-minus');
                });

                collapse.addEventListener('hidden.bs.collapse', function() {
                    icon.classList.remove('fa-minus');
                    icon.classList.add('fa-plus');
                });

            });
            // calc
            function calculateFooter() {

                let totalQtyPcs = 0;
                let totalQtyBox = 0;
                let totalCbmBox = 0;
                let grandPrice = 0;
                let totalNet = 0;
                let totalGross = 0;
                let totalCbm = 0;

                $('#itemTableBody tr').each(function() {

                    let row = $(this);

                    totalQtyPcs += parseFloat(row.find('.qty_pcs').val()) || 0;

                    totalQtyBox += parseFloat(row.find('.qty_box').val()) || 0;

                    totalCbmBox += parseFloat(
                        (row.find('.cbm').val() || '0').replace(/,/g, '')
                    ) || 0;

                    totalCbm += parseFloat(
                        (row.find('.total_cbm').val() || '0').replace(/,/g, '')
                    ) || 0;

                    let qtyPcs = parseFloat(row.find('.qty_pcs').val()) || 0;

                    let net = parseFloat(row.find('.net_weight').val()) || 0;

                    let gross = parseFloat(row.find('.gross_weight').val()) || 0;

                    totalNet += net * qtyPcs;

                    totalGross += gross * qtyPcs;
                    grandPrice += parseCurrency(
                        row.find('.total_price').val()
                    );

                });

                $('#totalQtyPcs').text(formatNumber(totalQtyPcs, 0));

                $('#totalQtyBox').text(formatNumber(totalQtyBox, 0));

                $('#totalCbmBox').text(formatNumber(totalCbmBox, 3));

                $('#grandTotalPrice').text(formatCurrency(grandPrice));

                $('#totalNetWeight').text(formatNumber(totalNet, 2));

                $('#totalGrossWeight').text(formatNumber(totalGross, 2));

                $('#grandTotalCbm').text(formatNumber(totalCbm, 3));

            }
            //remove
            $(document).on('click', '.remove-item', function() {

                let row = $(this).closest('tr');
                let itemId = row.data('item-id');

                Swal.fire({
                    title: 'Hapus Item?',
                    text: 'Apakah Anda yakin ingin menghapus item ini?',
                    icon: 'warning',
                    showCancelButton: true
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    // Belum pernah disimpan
                    if (!itemId) {
                        row.remove();
                        reIndexRows();
                        calculateFooter();
                        refreshSalesOrder();
                        return;
                    }

                    // Sudah tersimpan di database
                    $.ajax({
                        url: '/export/item/' + itemId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {

                            if (res.success) {
                                row.remove();
                                reIndexRows();
                                calculateFooter();
                                refreshSalesOrder();
                            }
                        }
                    });

                });

            });
            // save
            $('#btnSaveExport').click(function() {
                let payload = buildPayload();
                console.log(payload.shipment);

                if (MODE === "create") {
                    saveIpl(payload);
                } else {
                    updateIpl(payload);
                }

            });
            // save ipl
            function saveIpl(payload) {

                $.ajax({

                    url: "{{ route('export.saveIpl') }}",

                    type: "POST",

                    data: JSON.stringify(payload),

                    contentType: "application/json",

                    headers: {

                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                    },

                    success: function(res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Success',

                            text: res.message

                        }).then(() => {

                            window.location.href = "{{ route('export.ipl') }}";

                        });

                    },

                    error: function(xhr) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text: xhr.responseJSON.message

                        });

                    }

                });

            }



            $(document).on('click', '.combine-po-item', function(e) {

                e.preventDefault();

                let po = {

                    id: $(this).data('id'),
                    order_no: $(this).data('order')

                };

                $.get('/export/po-items/' + po.id, function(items) {

                    items.forEach(function(item) {

                        appendCombineItem(item, po);

                    });

                });

                $('#combinePoResult').hide();

                $('#searchCombinePo').val('');

            });

            function searchCombinePo(keyword) {

                if (keyword.length < 2) {

                    $('#combinePoResult').hide();

                    return;

                }

                $.get('/export/search-po', {
                    keyword: keyword
                }, function(data) {

                    let html = '';

                    data.forEach(function(po) {

                        html += `
                <a href="#"
                    class="list-group-item list-group-item-action combine-po-item"

                    data-id="${po.id}"
                    data-order="${po.order_no}"
                    data-company="${po.company_name}"
                    data-country="${po.country}"
                    data-shipment="${po.shipment_date ?? ''}">

                    <strong>${po.order_no}</strong><br>
                    <small>${po.company_name}</small>

                </a>
            `;

                    });

                    $('#combinePoResult')
                        .html(html)
                        .show();

                });

            }
            $('#searchCombinePo').on('keyup', function() {

                let value = $(this).val();

                value = value.replace(/\D/g, '');

                if (value.length >= 4) {

                    value = value.substring(0, 2) + '-' + value.substring(2);

                }

                $(this).val(value);

                searchCombinePo(value);

            });
            // add modals

            $('#btnSearchCombine').click(function() {

                let keyword = $('#searchCombinePo').val();

                $.get('/export/search-po', {

                    keyword: keyword

                }, function(result) {

                    let html = '';

                    result.forEach(function(po) {

                        html += `

            <div class="card mb-2">

                <div class="card-header d-flex justify-content-between">

                    <strong>${po.order_no}</strong>

                    <button
                        class="btn btn-sm btn-primary load-detail"
                        data-id="${po.id}">

                        Show Items

                    </button>

                </div>

                <div id="detail-${po.id}"></div>

            </div>

            `;

                    });

                    $('#combinePoResult').html(html);

                });

            });
            $(document).on('click', '.load-detail', function() {

                let poId = $(this).data('id');

                $.get('/export/po-items/' + poId, function(items) {

                    let html = '';

                    html += `

        <table class="table table-bordered table-sm">

        <thead>

        <tr>

            <th width="40"></th>

            <th>Photo</th>

            <th>Article</th>

            <th>Description</th>

            <th>Qty</th>

            <th>FOB</th>

        </tr>

        </thead>

        <tbody>

        `;

                    items.forEach(function(item) {

                        html += `

            <tr>

                <td>

                    <input
                        type="checkbox"
                        class="combine-item"

                        data-item='${JSON.stringify(item)}'>

                </td>

                <td>

                    <img
                        src="${item.photo}"
                        width="50">

                </td>

                <td>${item.article_nr}</td>

                <td>${item.description}</td>

                <td>${item.qty}</td>

                <td>${item.value}</td>

            </tr>

            `;

                    });

                    html += `

        </tbody>

        </table>

        `;

                    $('#detail-' + poId).html(html);

                });

            });
            //
            $('#searchCombinePo').on('keyup', function() {

                let value = $(this).val();

                value = value.replace(/\D/g, '');

                if (value.length >= 4) {
                    value = value.substring(0, 2) + '-' + value.substring(2);
                }

                $(this).val(value);

                if (value.length < 2) {

                    $('#combinePoList').hide();

                    return;

                }

                $.get('/export/search-po', {

                    keyword: value

                }, function(data) {

                    let html = '';

                    data.forEach(function(po) {

                        html += `

            <a href="#"
                class="list-group-item list-group-item-action combine-po-item"
                data-id="${po.id}"
                data-order="${po.order_no}"
                data-company="${po.company_name}">

                <strong>${po.order_no}</strong><br>

                <small>${po.company_name}</small>

            </a>

            `;

                    });

                    $('#combinePoList').html(html).show();

                });

            });
            // append to ipl table
            function appendCombineItem(item, po) {

                // jangan dobel
                if ($('#combineItemTable tbody tr[data-detail="' + item.id + '"]').length) {
                    return;
                }

                $('#emptyCombineItem').remove();

                $('#combineItemTable tbody').append(`

<tr data-detail="${item.id}">
    <td><input
    type="hidden"
    class="combine-po-id"
    value="${po.id}"></td>
    <td class="text-center">
        <input
            type="checkbox"
            class="combine-item"
            checked>
    </td>

    <td>
        <img src="${item.photo}" width="60">
    </td>

    <td>${po.order_no}</td>

    <td>${item.article_nr}</td>

    <td>${item.description}</td>

    <td class="text-center">${item.qty}</td>

    <td class="text-end">${formatCurrency(item.value)}</td>

    <!-- hidden -->
    <input type="hidden" class="combine-detail" value="${item.id}">
    <input type="hidden" class="combine-photo" value="${item.photo}">
    <input type="hidden" class="combine-article" value="${item.article_nr}">
    <input type="hidden" class="combine-description" value="${item.description}">
    <input type="hidden" class="combine-qty" value="${item.qty}">
    <input type="hidden" class="combine-packw" value="${item.pack_w}">
    <input type="hidden" class="combine-packd" value="${item.pack_d}">
    <input type="hidden" class="combine-packh" value="${item.pack_h}">
    <input type="hidden" class="combine-value" value="${item.value}">
    <input type="hidden" class="combine-po-no" value="${po.order_no}">

</tr>

`);

            }
            const modalAddPo = new bootstrap.Modal(
                document.getElementById('modalAddPo')
            );

            $('#btnAddPo').on('click', function() {

                modalAddPo.show();

            });
            $('#btnAddCombine').click(function() {

                $('#combineItemTable tbody tr').each(function() {

                    if (!$(this).find('.combine-item').is(':checked'))
                        return;

                    let item = {

                        id: $(this).find('.combine-detail').val(),
                        po_id: $(this).find('.combine-po-id').val(),

                        photo: $(this).find('.combine-photo').val(),

                        article_nr: $(this).find('.combine-article').val(),

                        description: $(this).find('.combine-description').val(),

                        qty: $(this).find('.combine-qty').val(),

                        pack_w: $(this).find('.combine-packw').val(),

                        pack_d: $(this).find('.combine-packd').val(),

                        pack_h: $(this).find('.combine-packh').val(),

                        value: $(this).find('.combine-value').val(),
                        order_no: $(this).find('.combine-po-no').val()

                    };

                    appendItemRow(item);


                });

                $('#modalAddPo').modal('hide');

            });

            function appendItemRow(item) {

                // let index = $('#itemTableBody tr').length;
                let index = $('#itemTableBody input[name$="[detail_po_id]"]').length;

                // hapus row "Belum ada Sales Order dipilih"
                $('#itemTableBody td[colspan]').closest('tr').remove();

                let html = `

                  <tr data-id="${item.item_id ?? item.id}">
                    <td style="display:none">
                        <input
                            type="hidden"
                            name="items[${index}][po_id]"
                            value="${item.po_id }">
                    </td>
                    <td style="display:none">
                        <input type="hidden"
                            name="items[${index}][detail_po_id]"
                            value="${item.id}">
                    </td>

                    <td>${index+1}</td>

                    <td>
                        <input
                            class="form-control form-control-sm" value="${item.hs_code ?? ''}"
                            name="items[${index}][hs_code]">
                    </td>

                    <td>

                    <img
                    src="${item.photo}"
                    width="60"
                    class="img-thumbnail">

                    <input
                    type="hidden"
                    name="items[${index}][photo]"
                    value="${item.photo}">

                    </td>

                    <td>

                    <textarea
                    rows="2"
                    class="form-control form-control-sm"
                    name="items[${index}][description]">${item.description}</textarea>

                    </td>

                    <td>

                    <input
                    class="form-control form-control-sm"
                    name="items[${index}][article_nr]"
                    value="${item.article_nr}">

                    </td>

                    <td>

                    <input
                    type="text"
                    class="form-control form-control-sm box_dimension"
                    name="items[${index}][box_dimension]"
                    value="${item.pack_w} x ${item.pack_d} x ${item.pack_h}">

                    </td>

                    <td>

                    <input
                    type="number"
                    class="form-control form-control-sm qty_pcs"
                    name="items[${index}][qty_pcs]"
                    value="${item.qty}">

                    </td>

                    <td>

                    <input
                    type="number"
                    class="form-control form-control-sm qty_box"
                    name="items[${index}][qty_box]"
                    value="${item.qty_box ?? 1}">

                    </td>

                    <td>

                    <input
                    readonly
                    class="form-control form-control-sm cbm"
                    name="items[${index}][cbm]">

                    </td>

                    <td>

                    <input
                    class="form-control form-control-sm unit_price"
                    name="items[${index}][unit_price]"
                    value="${formatCurrency(item.value)}">

                    </td>

                    <td>

                    <input
                    readonly
                    class="form-control form-control-sm total_price"
                    name="items[${index}][total_price]">

                    </td>

                    <td>

                    <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm net_weight" value="${item.net_weight ?? ''}"
                    name="items[${index}][net_weight]">

                    </td>

                    <td>

                    <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm gross_weight" value="${item.gross_weight ?? ''}"
                    name="items[${index}][gross_weight]">

                    </td>

                    <td>

                    <input
                    readonly
                    class="form-control form-control-sm total_cbm"
                    name="items[${index}][total_cbm]">

                    </td>

                    <td>

                    <input
                    class="form-control form-control-sm" value="${item.remark ?? ''}"
                    name="items[${index}][remark]">

                    </td>
                    <td>

                    <input
                    type="text"
                    class="form-control form-control-sm"
                    name="items[${index}][po_no]"
                    value="${item.order_no ?? ''}"
                    readonly>

                    </td>
                    <td>

                    <button
                    type="button"
                    class="btn btn-sm btn-danger remove-item">

                    <i class="fas fa-times"></i>

                    </button>

                    </td>

                    </tr>

                    `;

                $('#itemTableBody').append(html);

                let row = $('#itemTableBody tr:last');

                calculateRow(row);

                calculateFooter();
                refreshSalesOrder();

            }
            // refresh
            $('#btnRefreshCombine').click(function() {

                $('#combineItemTable tbody').html(`

<tr id="emptyCombineItem">

<td colspan="7"
class="text-center text-muted">

Belum ada item

</td>

</tr>

`);

                $('#searchCombinePo').val('');

                $('#combinePoResult').hide();

            });

            function refreshSalesOrder() {

                let poNos = [];

                $('#itemTableBody').find('[name*="[po_no]"]').each(function() {

                    let po = $(this).val().trim();

                    if (po && !poNos.includes(po)) {
                        poNos.push(po);
                    }

                });

                let value = poNos.join(', ');

                $('#sales_order').val(value);

            }
            // edit
            $(function() {

                if (MODE === "edit") {

                    loadEditData();

                }

            });

            function loadEditData() {

                if (!IPL) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                $('#date').val(IPL.date);

                $('#buyer_name').val(IPL.buyer);

                $('#buyer_address').val(IPL.buyer_address);

                $('#invoice_no').val(IPL.invoice_no);

                $('#customer_code').val(IPL.customer_code);

                $('#customer_po_no').val(IPL.customer_po_no);

                $('#vessel_name').val(IPL.vessel_name);

                $('#container_type').val(IPL.container_type);

                $('#container_no').val(IPL.container_no);

                $('#seal_no').val(IPL.seal_no);

                $('#port_loading').val(IPL.port_loading);

                $('#port_discharge').val(IPL.port_discharge);

                $('#commodity').val(IPL.commodity);

                $('#fumigation').val(IPL.fumigation);

                $('#etd').val(IPL.etd ? IPL.etd.substring(0, 10) : '');
                $('#eta').val(IPL.eta ? IPL.eta.substring(0, 10) : '');

                /*
                |--------------------------------------------------------------------------
                | Items
                |--------------------------------------------------------------------------
                */

                $('#itemTableBody').html('');

                IPL.items.forEach(function(item) {

                    appendItemRow({
                        item_id: item.id,
                        id: item.detail_po_id,

                        po_id: item.po_id,

                        order_no: item.po_no,

                        article_nr: item.article_nr,

                        description: item.description,

                        photo: item.photo,

                        qty: item.qty_pcs,

                        pack_w: getDimension(item.box_dimension, 0),

                        pack_d: getDimension(item.box_dimension, 1),

                        pack_h: getDimension(item.box_dimension, 2),

                        value: item.unit_price,

                        cbm: item.cbm,

                        total_cbm: item.total_cbm,

                        remark: item.remark,

                        hs_code: item.hs_code,

                        net_weight: item.net_weight,

                        gross_weight: item.gross_weight,

                        qty_box: item.qty_box

                    });

                });

                refreshSalesOrder();

            }

            function reIndexRows() {

                $('#itemTableBody tr').each(function(i) {

                    $(this).find('[name]').each(function() {

                        let name = $(this).attr('name');

                        name = name.replace(/items\[\d+\]/, 'items[' + i + ']');

                        $(this).attr('name', name);

                    });

                });

            }

            function getDimension(value, index) {

                if (!value) {

                    return '';

                }

                let arr = value.split(' x ');

                return arr[index] ?? '';

            }
            // edit


            function updateIpl(payload) {

                console.log(payload);

                $.ajax({

                    url: "/export/" + IPL.id,

                    type: "PUT",

                    data: JSON.stringify(payload),

                    contentType: "application/json",

                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },

                    success: function(res) {
                        console.log(res);
                    }

                });

            }

            function buildPayload() {

                let payload = {

                    // po_id: $('#po_id').val(),
                    po_id: $('#po_id').val(), // atau '' juga tidak masalah

                    invoice_no: $('#invoice_no').val(),

                    sales_order: $('#sales_order').val(),

                    buyer: $('#buyer_name').val(),

                    shipment: {

                        invoice_no: $('[name="invoice_no"]').val(),

                        container_type: $('[name="container_type"]').val(),

                        container_no: $('[name="container_no"]').val(),

                        seal_no: $('[name="seal_no"]').val(),

                        vessel_name: $('[name="vessel_name"]').val(),

                        port_loading: $('[name="port_loading"]').val(),

                        port_discharge: $('[name="port_discharge"]').val(),

                        commodity: $('[name="commodity"]').val(),

                        fumigation: $('[name="fumigation"]').val(),

                        etd: $('[name="etd"]').val(),

                        eta: $('[name="eta"]').val(),

                        buyer_address: $('[name="buyer_address"]').val(),

                        customer_code: $('[name="customer_code"]').val(),

                        customer_po_no: $('[name="customer_po_no"]').val(),

                    },

                    items: []

                };

                $('#itemTableBody tr').each(function() {

                    let row = $(this);

                    if (!row.find('.box_dimension').length) {
                        return;
                    }

                    payload.items.push({

                        po_id: row.find('input[name$="[po_id]"]').val(),

                        detail_po_id: row.find('input[name$="[detail_po_id]"]').val(),

                        po_no: row.find('input[name$="[po_no]"]').val(),

                        hs_code: row.find('input[name$="[hs_code]"]').val(),

                        description: row.find('textarea[name$="[description]"]').val(),

                        article_nr: row.find('input[name$="[article_nr]"]').val(),

                        photo: row.find('input[name$="[photo]"]').val(),

                        qty_pcs: row.find('.qty_pcs').val(),

                        qty_box: row.find('.qty_box').val(),

                        box_dimension: row.find('.box_dimension').val(),

                        cbm: parseFloat((row.find('.cbm').val() || '0').replace(/,/g, '')),

                        total_cbm: parseFloat((row.find('.total_cbm').val() || '0').replace(/,/g, '')),

                        unit_price: parseCurrency(row.find('.unit_price').val()),

                        total_price: parseCurrency(row.find('.total_price').val()),

                        net_weight: row.find('.net_weight').val(),

                        gross_weight: row.find('.gross_weight').val(),

                        remark: row.find('input[name$="[remark]"]').val()

                    });

                });

                return payload;

            }
        </script>
        <script>
            const MODE = "{{ $mode }}";

            const IPL = @json($ipl);
        </script>

    </div>
@endpush
