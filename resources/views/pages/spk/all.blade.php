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
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No SPK</th>
                    <th>Item</th>
                    <th>kategori</th>

                    <th>Act</th>
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

                let spkCount = po.spks?.length ?? 0;

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
   $(document).on('click','.btn-view-spk', function(){

    let index = $(this).data('index');
    let po    = cacheData[index];

    let html = '';
    let poNo = po.data_po?.no_po ?? '-';

    // ============================
    // FILTER UNIQUE SPK
    // ============================
    let uniqueSpks = [];
    let usedSpkIds = new Set();

    (po.spks || []).forEach(spk => {
        if (!usedSpkIds.has(spk.id)) {
            usedSpkIds.add(spk.id);
            uniqueSpks.push(spk);
        }
    });

    // ============================
    // LOOP SPK
    // ============================
   uniqueSpks.forEach(spk => {

    let items = spk.data?.items || [];

    // 🔥 filter item duplicate
    let uniqueItems = [];
    let usedItemKey = new Set();

    items.forEach(item => {

        let key = item.id + '_' + item.id;

        if (!usedItemKey.has(key)) {
            usedItemKey.add(key);
            uniqueItems.push(item);
        }

    });

    // tampilkan item hasil filter
    uniqueItems.forEach(item => {

      let itemHtml = '';

(spk.data?.items || []).forEach(item => {
    itemHtml += `
        <div style="margin-bottom:4px">
            <b>${item.kode ?? '-'}</b><br>
            <small>
                ${item.nama ?? '-'} —
                ${item.qty ?? 0} ${item.satuan ?? ''}
            </small>
        </div>

    `;
});

html += `
    <tr>
        <td>${spk.data?.no_spk ?? '-'}</td>
        <td>${spk.data?.kategori ?? '-'}</td>
        <td>${itemHtml || '-'}</td>
        <td>
            <button
    class="btn btn-sm btn-info btn-edit-spk"
    data-id="${spk.id}">
    V / Edit
</button>
 <!-- DOWNLOAD -->
        <button
            class="btn btn-sm btn-success btn-download-spk"
            data-id="${spk.id}">
            <i class="fa fa-download"></i>
        </button>
        </td>
    </tr>
`;
});
});


    // ============================
    // EMPTY CHECK
    // ============================
    if(html === ''){
        html = `
            <tr>
                <td colspan="8" class="text-center">
                    Tidak ada SPK
                </td>
            </tr>
        `;
    }

    $('#spk-detail-body').html(html);
    $('#detailPoTitle').text('PO : ' + poNo);
    $('#spkDetailBox').slideDown();

    // highlight row
    $('.po-row').removeClass('selected-spk');
    $(this).closest('.po-row').addClass('selected-spk');

    // scroll
    $('html, body').animate({
        scrollTop: $("#spkDetailBox").offset().top
    }, 500);

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
</style>
@endpush
