@extends('master.master')
@section('title', "All SPK")
@section('content')

<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>All Spk</h2>
            <small>semua data SPK</small>
              <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 20px;">No</th>
                                        <th>Po</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="po-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="box" id="spkDetailBox" style="display:none">
    <div class="box-header">
        <h3>Detail SPK</h3>
        <small id="detailPoTitle"></small>
    </div>

    <div class="box-body">
  <table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>SPK</th>
            <th>Rangka</th>
            <th>Sub</th>
            <th>Qty</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody id="spk-detail-body"></tbody>
</table>


    </div>
</div>
                    </div>
                </div>
            </div>
</div>
@endsection
  @push('scripts')
    <!-- jQuery (WAJIB PERTAMA) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (SETELAH jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
<script>
// download
$(document).on('click', '.btn-download-spk', function () {
    const spkId = $(this).data('id');

    window.open(`/spk/export/${spkId}`, '_blank');
});
$(document).ready(function(){

    let cacheData = [];

    // ===============================
    // LOAD PO + SPK TABLE
    // ===============================
   function loadSpkTable()
{
    $.get("{{ route('spk.all') }}", function(res){

        cacheData = res;

        let html = '';
        let no   = 1;

        res.forEach((po, index) => {

            let poNo = po.data_po?.no_po ?? '-';

            // hitung SPK unik
            let spkIds = new Set();

            (po.data_po?.items || []).forEach(item => {
                let summary = item.summary || {};

                Object.values(summary).forEach(suppliers => {
                    Object.values(suppliers).forEach(supplier => {
                        (supplier.spks || []).forEach(spk => {
                            spkIds.add(spk.spk_id);
                        });
                    });
                });
            });

            let spkCount = spkIds.size;

            html += `
                <tr class="po-row" data-index="${index}">
                    <td>${no++}</td>
                    <td>${poNo}</td>
                    <td>
                        <button
                            class="btn btn-sm btn-info btn-view-spk"
                            data-index="${index}">
                            ${spkCount} SPK
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#po-table-body').html(html);
    });
}



    // ===============================
    // CLICK VIEW SPK
    // ===============================
$(document).on('click', '.btn-view-spk', function () {

    let index = $(this).data('index');
    let po    = cacheData[index];
    let items = po.data_po?.items || [];

    let html = '';

    // =========================
    // 1. KUMPULKAN SEMUA SPK
    // =========================
    let spkMap = {};

    items.forEach(item => {

        let d = item.detail || {};
        let summary = item.summary || {};

        Object.entries(summary).forEach(([kategori, suppliers]) => {

            Object.entries(suppliers).forEach(([supplier, data]) => {

                (data.spks || []).forEach(spk => {

                    if (!spkMap[spk.spk_id]) {
                        spkMap[spk.spk_id] = {
                            spk_id: spk.spk_id,
                            no_spk: spk.no_spk,
                            items: []
                        };
                    }

                    spkMap[spk.spk_id].items.push({
                        artikel: d.article_nr_,
                        nama: d.description,
                        kategori: kategori,
                        supplier: supplier,
                        qty: spk.qty,
                        po_qty: d.qty
                    });
                });
            });
        });
    });

    // =========================
    // 2. RENDER PER SPK
    // =========================
    Object.values(spkMap).forEach(spk => {

    let itemCount = spk.items.length;
    let hasArrow  = itemCount > 2;
    let spkClass  = `spk-items-${spk.spk_id}`;

    // ===== SPK HEADER =====
    html += `
        <tr class="spk-header"
            data-spk-id="${spk.spk_id}"
            style="cursor:pointer;background:#f8f9fa">
            <td colspan="5">
                <b>
                    ${hasArrow ? `<span class="spk-arrow">▶</span>` : ''}
                    SPK ${spk.no_spk}
                </b>
                <small class="text-muted">
                    (${itemCount} item)
                </small>
            </td>
        </tr>
    `;

    let lastItem = null;

    // ===== ITEMS =====
    spk.items.forEach(row => {

        let showItem = row.artikel !== lastItem;
        lastItem = row.artikel;

        html += `
            <tr class="spk-item ${spkClass}"
                ${hasArrow ? 'style="display:none"' : ''}>
                <td>
                    ${showItem ? `
                        <b>${row.artikel}</b><br>
                        <small>${row.nama}</small><br>
                        <small>PO Qty: ${row.po_qty}</small>
                    ` : ''}
                </td>
                <td>${row.kategori}</td>
                <td>${row.supplier}</td>
                <td>${row.qty}</td>
                <td>${row.qty}</td>
            </tr>
        `;
    });

    html += `<tr><td colspan="5"></td></tr>`;
});

    $('#spk-detail-body').html(html);
    $('#detailPoTitle').text('PO : ' + (po.data_po?.no_po ?? '-'));
    $('#spkDetailBox').slideDown();
});
// spk no header arrow down
$(document).on('click', '.spk-header', function () {

    let spkId = $(this).data('spk-id');
    let rows  = $(`.spk-items-${spkId}`);
    let arrow = $(this).find('.spk-arrow');

    rows.toggle();

    if (arrow.length) {
        arrow.text(
            rows.first().is(':visible') ? '▼' : '▶'
        );
    }
});

$(document).on('click', '.spk-link', function () {
    let spkId = $(this).data('spk-id');
       window.location.href = `/spk/edit/${spkId}`;

});


    // ===============================
    // EDIT SPK
    // ===============================
   $(document).on('click', '.btn-edit-spk', function () {
    let spkId = $(this).data('id');
    window.location.href = `/spk/edit/${spkId}`;
});

    // ===============================
    // INIT LOAD
    // ===============================
    loadSpkTable();

});
</script>

<style>
    .selected-spk {
    background-color: #ffeeba !important;
    border-left: 5px solid #f39c12;
    font-weight: 600;
}
    .spk-detail-row{
    background:#f9f9f9;
}
/* BARANG */
.barang-row td {
    padding: 12px 10px;
    background: #f9fafb;
    border-top: 2px solid #dee2e6;
}

.barang-title {
    font-weight: 600;
}

.barang-desc {
    font-size: 12px;
    color: #666;
}

.barang-qty {
    font-size: 11px;
    color: #999;
}

/* KATEGORI */
.kategori-row td {
    padding: 6px 10px;
    font-style: italic;
    color: #555;
    background: #f1f3f5;
}

/* SPK */
.spk-row td {
    padding: 6px 10px;
    font-size: 13px;
}

/* TABLE UMUM */
table td {
    vertical-align: top;
}

</style>
@endpush
