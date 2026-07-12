@extends('master.master')

@section('title','Detail Barang')

@section('content')

<div class="padding">

```
<div class="box">

    <div class="box-header d-flex justify-content-between">

        <h2>
            Detail Barang
        </h2>

        <a href="{{ url('/laporan') }}"
           class="btn btn-secondary">
            Kembali
        </a>

    </div>
<div class="card mb-3">
    <div class="card-header">
        Filter Laporan
    </div>

    <div class="card-body">

        <form
            method="GET"
            action="{{ route('laporan.detail',$stok->id) }}">

            <div class="row">

                <div class="col-md-3">
                    <label>Dari Tanggal</label>
                    <input
                        type="date"
                        name="tanggal_awal"
                        value="{{ request('tanggal_awal') }}"
                        class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>
                    <input
                        type="date"
                        name="tanggal_akhir"
                        value="{{ request('tanggal_akhir') }}"
                        class="form-control">
                </div>

           <div class="col-md-6">
    <label>&nbsp;</label>

    <div>

        <button
            type="submit"
            class="btn btn-primary">

            <i class="fa fa-search"></i>
            Filter

        </button>

        <a
            href="{{ route('laporan.detail',$stok->id) }}"
            class="btn btn-warning">

            <i class="fa fa-refresh"></i>
            Reset Filter

        </a>

        <a
            href="{{ route('laporan.detail.pdf',$stok->id) }}?tanggal_awal={{ request('tanggal_awal') }}&tanggal_akhir={{ request('tanggal_akhir') }}"
            target="_blank"
            class="btn btn-danger">

            <i class="fa fa-file-pdf-o"></i>
            Export PDF

        </a>

    </div>
</div>

            </div>

        </form>

    </div>
</div>
    <div class="box-body">
@php
$totalIn = $transaksi->where('tipe','in')->sum('qty');

$totalOut = $transaksi->where('tipe','out')->sum('qty');

$stokTersedia =
    $stok->stok_awal +
    $totalIn -
    $totalOut;
@endphp
@php
function qtyFormat($value)
{
    return rtrim(
        rtrim(number_format($value,3,'.',''),'0'),
        '.'
    );
}
@endphp
<div class="alert alert-info">

  <b>Total Masuk :</b>
{{ qtyFormat($totalIn) }}
{{ $stok->satuan }}

|

<b>Total Keluar :</b>
{{ qtyFormat($totalOut) }}
{{ $stok->satuan }}

|

<b>Stok Saat Ini :</b>
{{ qtyFormat($stokTersedia) }}
{{ $stok->satuan }}

</div>
        <div class="row mb-3">

            <div class="col-md-2">
                <label>Kode Barang</label>
                <input type="text"
                       class="form-control"
                       value="{{ $stok->kode_barang }}"
                       readonly>
            </div>

            <div class="col-md-4">
                <label>Nama Barang</label>
                <input type="text"
                       class="form-control"
                       value="{{ $stok->nama_barang }}"
                       readonly>
            </div>

            <div class="col-md-2">
                <label>Jenis</label>
                <input type="text"
                       class="form-control"
                       value="{{ $stok->jenis }}"
                       readonly>
            </div>

            <div class="col-md-2">
                <label>Satuan</label>
                <input type="text"
                       class="form-control"
                       value="{{ $stok->satuan }}"
                       readonly>
            </div>

            <div class="col-md-2">
                <label>Harga</label>
                <input type="text"
                       class="form-control"
                       value="{{ number_format($stok->harga,0,',','.') }}"
                       readonly>
            </div>

        </div>

        <hr>

        <div id="spkInfo"></div>

        <div class="card mb-4">

            <div class="card-header">
                Input Transaksi
            </div>

            <div class="card-body">
<input type="hidden"
       id="stok_tersedia"
       value="{{ $stokTersedia }}">
                <input type="hidden"
                       id="stok_id"
                       value="{{ $stok->id }}">

                <input type="hidden"
                       id="spk_id">

                <div class="row">

                    <div class="col-md-2">

                        <label>Tanggal</label>

                        <input type="date"
                               id="tanggal"
                               class="form-control"
                               value="{{ date('Y-m-d') }}">
                    <small
                        id="warningStok"
                        style="
                            color:red;
                            display:none;
                        ">
                    </small>
                    </div>

                    <div class="col-md-2">

                        <label>IN</label>

                        <input type="number"
                               step="0.001"
                               id="qty_in"
                               class="form-control">

                    </div>

                    <div class="col-md-2">

                        <label>OUT</label>

                        <input type="number"
                               step="0.001"
                               id="qty_out"
                               class="form-control">

                    </div>
  <div class="col-md-3 mt-4">
    <div class="input-group">
                    <input type="text"
                        id="po"
                        class="form-control"
                        readonly>

                                    <button
                    type="button"
                    id="btnCariSpk"
                    class="btn btn-primary">
                    Cari
                </button>
                </div>
  </div>


                    <div class="col-md-2">

                        <label>Keterangan</label>

                        <input type="text"
                               id="keterangan"
                               class="form-control">

                    </div>

                    <div class="col-md-1">

                        <label>&nbsp;</label>

                       <button type="button"
                                id="btnTambahTransaksi"
                                class="btn btn-success btn-block"
                                disabled
                                style="cursor:not-allowed;opacity:.6">

                            Save

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <div class="card">

            <div class="card-header">

                Riwayat Transaksi

            </div>

            <div class="card-body">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>
                            <th>Tanggal</th>
                            <th>IN</th>
                            <th>OUT</th>
                            <th>Satuan</th>
                            <th>PO/SPK</th>
                            <th>Keterangan</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($transaksi as $item)

                        <tr>

                            <td>{{ $item->tanggal }}</td>

                            <td>
                                {{ $item->tipe == 'in'
                                    ? $item->qty
                                    : '' }}
                            </td>

                            <td>
                                {{ $item->tipe == 'out'
                                    ? $item->qty
                                    : '' }}
                            </td>
<td>{{ $stok->satuan }}</td>
                            <td>
                                {{ $item->po }}
                            </td>

                            <td>
                                {{ $item->keterangan }}
                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>
    </div>
    </div>



</div>
<div id="spkDrawerOverlay"></div>

<div id="spkDrawer">

    <div class="drawer-header">
        <h5>Cari SPK</h5>

        <button
            type="button"
            id="closeDrawer"
            class="btn btn-danger btn-sm">
            ✕
        </button>
    </div>

    <div class="p-3">

        <input
            type="text"
            id="searchSpk"
            class="form-control mb-3"
            placeholder="Cari No SPK / Supplier">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>No SPK</th>
                        <th>Supplier</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>

                <tbody id="spkTableBody">
                </tbody>

            </table>

        </div>

    </div>

</div>
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

<script>
    // ajax
    $(document).on('keyup','#searchSpk',function(){

    let q = $(this).val();
    loadSpk($(this).val());
    $('#searchSpk').focus();

    $.get('/spk/search-spk',{
        q:q
    },function(res){

        let html = '';
        console.log(res);
        res.forEach(function(item){

            html += `
            <tr>
                <td>${item.no_spk}</td>
                <td>${item.supplier}</td>

                <td>
                    <button
                        class="btn btn-success btn-sm pilih-spk"
                        data-spk='${JSON.stringify(item)}'>
                        Pilih
                    </button>
                </td>
            </tr>
            `;
        });

        $('#spkTableBody').html(html);

    });

});
// pilih
$(document).on('click','.pilih-spk',function(){

    let data = $(this).data('spk');

    $('#spk_id').val(data.id);

    $('#po').val(data.no_spk);

    // $('#modalSpk').modal('hide');

    tampilkanDetailSpk(data);

});
//
$('#modalSpk').on('shown.bs.modal', function () {
 backdrop: false
    loadSpk();

    $('#searchSpk').focus();

});
function tampilkanDetailSpk(data)
{
    let itemRows = '';

    data.items.forEach(function(item){

        itemRows += `
        <tr>
            <td>${item.kode}</td>
            <td>${item.nama}</td>
            <td>${item.qty}</td>
        </tr>
        `;
    });

    $('#spkInfo').html(`
        <div class="alert alert-success position-relative">

            <button
                type="button"
                id="closeSpkInfo"
                class="btn btn-danger btn-sm"
                style="
                    position:absolute;
                    top:10px;
                    right:10px;
                ">
                ✕
            </button>

            <h4>${data.no_spk}</h4>

            Supplier : ${data.supplier}

            <table class="table table-bordered mt-2">

                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Qty</th>
                    </tr>
                </thead>

                <tbody>
                    ${itemRows}
                </tbody>

            </table>

        </div>
    `);
}
function checkSaveButton() {

    let qtyIn =
        parseFloat(
            $('#qty_in').val()
        ) || 0;

    let qtyOut =
        parseFloat(
            $('#qty_out').val()
        ) || 0;

    let stokTersedia =
        parseFloat(
            $('#stok_tersedia').val()
        ) || 0;

    let valid = true;

    $('#warningStok')
        .hide();

    $('#qty_out')
        .removeClass('is-invalid');

    if(
        qtyOut > stokTersedia
    ){

        valid = false;

        $('#qty_out')
            .addClass('is-invalid');

        $('#warningStok')
            .html(
                'Stok tersedia hanya ' +
                stokTersedia
            )
            .show();

    }

    let enable =
        (qtyIn > 0 || qtyOut > 0)
        && valid;

    $('#btnTambahTransaksi')
        .prop(
            'disabled',
            !enable
        );

    if(enable){

        $('#btnTambahTransaksi')
            .css({
                cursor:'pointer',
                opacity:1
            });

    }else{

        $('#btnTambahTransaksi')
            .css({
                cursor:'not-allowed',
                opacity:.6
            });

    }

}
$(document).on(
    'input',
    '#qty_in,#qty_out',
    checkSaveButton
);

$(document).ready(function(){

    checkSaveButton();

});
$(document).on('click','#btnTambahTransaksi',function(){

    $.ajax({

        url:'/laporan/transaksi/store',

        type:'POST',

        data:{
            _token:'{{ csrf_token() }}',

            stok_id:$('#stok_id').val(),

            tanggal:$('#tanggal').val(),

            in:$('#qty_in').val(),

            out:$('#qty_out').val(),

            po:$('#po').val(),

            spk_id:$('#spk_id').val(),

            keterangan:$('#keterangan').val()
        },

        success:function(){

            location.reload();

        }

    });

});

$(document).on('keyup','#po',function(){

    let q = $(this).val();

    if(q.length < 2){

        $('#spkSuggestion').html('');

        return;
    }

    $.get('/spk/search-spk',{q:q},function(res){

        let html = '';

        res.forEach(function(item){

            html += `
                <div
                    class="spk-item"
                    data-id="${item.id}"
                    data-spk='${JSON.stringify(item)}'
                    style="
                        padding:8px;
                        cursor:pointer;
                        border-bottom:1px solid #eee;
                    ">
                    ${item.no_spk}
                </div>
            `;
        });

        $('#spkSuggestion').html(html);

    });

});

$(document).on('click','.spk-item',function(){

    let data = $(this).data('spk');

    $('#spk_id').val(data.id);

    $('#po').val(data.no_spk);

    $('#spkSuggestion').html('');

    let itemRows = '';

    data.items.forEach(function(item){

        itemRows += `
            <tr>
                <td>${item.kode}</td>
                <td>${item.nama}</td>
                <td>${item.qty}</td>
            </tr>
        `;
    });

   $('#spkInfo').html(`
    <div
        class="alert alert-success position-relative">

        <button
            type="button"
            id="closeSpkInfo"
            class="btn btn-danger btn-sm"
            style="
                position:absolute;
                top:10px;
                right:10px;
            ">
            ✕
        </button>

        <h4>${data.no_spk}</h4>

        Supplier :
        ${data.supplier}

        <table class="table table-bordered mt-2">

            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Qty</th>
                </tr>
            </thead>

            <tbody>
                ${itemRows}
            </tbody>

        </table>

    </div>
`);

});
$(document).on('click', '#closeSpkInfo', function () {

    $('#spkInfo').slideUp();

});

// get the spk all
function loadSpk(q = '')
{
    $.get('/spk/search-spk', {
        q: q
    }, function(res){

        let html = '';

        res.forEach(function(item){

            html += `
            <tr>
                <td>${item.no_spk}</td>
                <td>${item.supplier}</td>
                <td>
                    <button
                        class="btn btn-success btn-xs pilih-spk"
                        data-spk='${JSON.stringify(item)}'>
                        Pilih
                    </button>
                </td>
            </tr>
            `;
        });

        $('#spkTableBody').html(html);

    });
}
$('#btnCariSpk').click(function(){

    $('#spkDrawer').addClass('show');
    $('#spkDrawerOverlay').show();

    loadSpk();

});

$('#closeDrawer,#spkDrawerOverlay').click(function(){

    $('#spkDrawer').removeClass('show');
    $('#spkDrawerOverlay').hide();

});
$(document).on('click','.pilih-spk',function(){

    let data = $(this).data('spk');

    $('#spk_id').val(data.id);

    $('#po').val(data.no_spk);

    tampilkanDetailSpk(data);

    $('#spkDrawer').removeClass('show');
    $('#spkDrawerOverlay').hide();

});
</script>
<style>

#spkDrawerOverlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    display:none;
    z-index:9998;
}

#spkDrawer{
    position:fixed;
    top:0;
    right:-800px;
    width:800px;
    max-width:90vw;
    height:100vh;
    background:#fff;
    z-index:9999;
    transition:.3s;
    overflow-y:auto;
    box-shadow:-5px 0 20px rgba(0,0,0,.15);
}

#spkDrawer.show{
    right:0;
}

.drawer-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px;
    border-bottom:1px solid #ddd;
    position:sticky;
    top:0;
    background:#fff;
    z-index:10;
}

</style>
@endsection
