@extends('master.master')
@section('title', "Cart Buyer")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <small class="text-muted">List New Buyer </small>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered" id="inventoryTable">
                    <thead style="color:white">
                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th class="sticky">Company</th>
                            <!-- <th>Company</th> -->
                            <th>Country</th>
                            <th>Shipment date</th>
                            <th>Packing</th>
                            <th>Contact person</th>
                            <th>Order no</th>
                            <th>Aksi</th> <!-- tambah aksi -->
                        </tr>
                    </thead>
                    <tbody id="buyerTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL VIEW PRODUCT ITEM ================= --}}
<div class="modal fade" id="buyerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
              <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Data Buyer</h5>

                <!-- BUTTON EXPORT -->
                <button class="btn btn-success" id="exportBuyer">
                    <i class="fa fa-file-excel"></i> Export
                </button>

                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                        <th>Photo</th>
                        <th>Description</th>
                        <th>Article Nr.</th>
                        <th>Remark</th>
                        <th>Cushion</th>
                        <th>Glass</th>
                        <th>W</th>
                        <th>D</th>
                        <th>H</th>
                        <th>PW</th>
                        <th>PD</th>
                        <th>PH</th>
                        <th>Materials</th>
                        <th>Finishing</th>
                        <th>QTY</th>
                        <th>CBM</th>
                        <th>Price (IDR)</th>
                        <th>Total CBM</th>
                        <th>Value (IDR)</th>
                        </tr>
                    </thead>
                    <tbody id="modalProductItems"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- ================= JS ================ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap4-editable/js/bootstrap-editable.min.js"></script>
<script>
$(document).ready(function () {

    loadBuyers();

    function loadBuyers() {
        $.ajax({
            url: "/api/buyers",
            method: "GET",
            success: function (res) {
                let rows = "";
                let no = 1;

                res.forEach(buyer => {
                    rows += `
                        <tr style="font-size:13px;">
                            <td>${no++}</td>
                            <td>${buyer.company_name ?? '-'}</td>
                            <td>${buyer.country ?? '-'}</td>
                            <td>${buyer.shipment_date ?? '-'}</td>
                            <td>${buyer.packing ?? '-'}</td>
                            <td>${buyer.contact_person ?? '-'}</td>
                            <td>${buyer.order_no ?? '-'}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info viewBuyerBtn" data-id="${buyer.buyer_id}">
                                    👁️
                                </button>
                            </td>
                             <td class="text-center">
                                <button class="btn btn-sm btn-success exportBtn" data-id="${buyer.buyer_id}">
                                    👁️
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $("#buyerTableBody").html(rows);
            },
            error: function (err) {
                console.log("Error load buyers:", err);
            }
        });
    }

    // ================= VIEW BUYER ITEMS =================
    $(document).on("click", ".viewBuyerBtn", function () {
        let buyerId = $(this).data("id");

        $.ajax({
            url: "/api/buyers/" + buyerId,
            method: "GET",
            success: function (res) {

                let rows = "";
                let no = 1;

                res.product_items.forEach(item => {
                    let p = item.product_detail;

                    rows += `
                        <tr>
                            <td>${no++}</td>
                            <td><img src="/storage/${item.photo}" width="80"></td>
                            <td>${p.description ?? '-'}</td>
                            <td>${item.article_code}</td>
                            <td>${item.remark ?? '-'}</td>
                            <td>${p.cushion ?? '-'}</td>
                            <td>${p.glass ?? '-'}</td>
                            <td>${p.item_w ?? '-'}</td>
                            <td>${p.item_d ?? '-'}</td>
                            <td>${p.item_h ?? '-'}</td>
                            <td>${p.packing_w ?? '-'}</td>
                            <td>${p.packing_d ?? '-'}</td>
                            <td>${p.packing_h ?? '-'}</td>
                            <td>${p.materials ?? '-'}</td>
                            <td>${p.finishing ?? '-'}</td>
                            <td>${item.qty ?? '-'}</td>
                          <td>${p.cbm ? parseFloat(p.cbm).toFixed(2) : '-'}</td>

                            <td>${p.fob_jakarta_in_usd ?? '-'}</td>
                            <td>${p.total_cbm ?? '-'}</td>
                            <td>${p.fob_jakarta_in_usd ?? '-'}</td>
                        </tr>
                    `;
                });

                $("#modalProductItems").html(rows);
                $("#buyerModal").modal("show");
            },
            error: function (err) {
                console.log("Gagal load detail buyer:", err);
            }
        });
    });

});
</script>
<style>
.modal-body {
    overflow-x: auto;
}

.table-fixed-header thead th {
    position: sticky;
    top: 0;
    background: #0C2D48;
    color: white;
    z-index: 5;
}
.modal-dialog {
    max-width: 80% !important;
}
</style>
@endsection
