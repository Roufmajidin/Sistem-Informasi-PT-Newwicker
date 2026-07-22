@extends('master.master')

@section('title', 'Export View')

@section('content')
    @include('pages.exports.partials.style')
    <div style="zoom:80%;">

        {{-- semua isi halaman --}}
        <div class="container-fluid py-4">

            {{-- Shipment Information --}}

            @include('pages.exports.partials.shipper')

            {{-- IPL TABLE --}}
            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        Invoice Packing List (IPL)
                    </h5>
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

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>
    @endsection

    @push('scripts')
        <script>
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

                    let html = '';

                    items.forEach(function(item, index) {

                        html += `
            <tr>

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
                        class="form-control form-control-sm text-end"
                        name="items[${index}][net_weight]">
                </td>

                <td>
                    <input
                        class="form-control form-control-sm text-end"
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

            </tr>`;
                    });

                    $('#itemTableBody').html(html);

                    // Hitung semua baris setelah tabel dibuat
                    $('#itemTableBody tr').each(function() {
                        calculateRow($(this));
                        calculateFooter();
                    });

                });

            }
            // load po items details
            function calculateRow(row) {

                let dimension = row.find('.box_dimension').val().trim();

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
                '.box_dimension,.qty_box,.qty_pcs,.unit_price',
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

                return parseFloat(
                    value.replace(/[$,]/g, '')
                ) || 0;

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
                        row.find('.cbm').val().replace(/,/g, '')
                    ) || 0;

                    totalCbm += parseFloat(
                        row.find('.total_cbm').val().replace(/,/g, '')
                    ) || 0;

                    totalNet += parseFloat(
                        row.find('[name$="[net_weight]"]').val()
                    ) || 0;

                    totalGross += parseFloat(
                        row.find('[name$="[gross_weight]"]').val()
                    ) || 0;

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
        </script>
    </div>
@endpush
