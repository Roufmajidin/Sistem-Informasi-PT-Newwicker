@extends('master.master')

@section('title','History in/out warehouse')

@section('content')

<div class="box">

    <div class="box-header d-flex justify-content-between">

        <h2 class="mt-4">
            History mutasi barang warehouse
        </h2>

        <a href="{{ url('/laporan') }}" class="btn btn-secondary">
            Kembali
        </a>

    </div>

    <div class="box-body">

        <div class="mb-3">
            <input type="text"
                   id="search"
                   class="form-control"
                   placeholder="Cari kode barang, nama barang, SPK, PO, Remark...">
        </div>

        <div id="tableResult">
            @include('pages.laporan.partials.history_table')
        </div>

    </div>

</div>

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

<script>
$(function () {

    let timer;

    function loadData(page = 1) {

        $.ajax({
            url: "{{ url('/laporan/warehouse-history') }}",
            type: "GET",
            data: {
                search: $('#search').val(),
                page: page
            },
            success: function (html) {
                $('#tableResult').html(html);
            }
        });

    }

    $('#search').on('keyup', function () {

        clearTimeout(timer);

        timer = setTimeout(function () {
            loadData();
        }, 300);

    });

    // Pagination AJAX
    $(document).on('click', '#tableResult .pagination a', function (e) {

        e.preventDefault();

        let page = $(this).attr('href').split('page=')[1];

        loadData(page);

    });

});
</script>

<script>
$(document).on('dblclick','.js-inline-po',function(){

    let td = $(this);

    if(td.find('input').length) return;

    let value = td.data('value') ?? '';

    td.html(
        '<input type="text" class="form-control form-control-sm po-input" value="'+value+'">'
    );

    td.find('input').focus().select();

});
    $(document).on('keypress','.po-input',function(e){

        if(e.which==13){

            $(this).blur();

        }

    });
    $(document).on('blur','.po-input',function(){

    let input = $(this);

    let td = input.closest('td');

    let id = td.data('id');

    let value = input.val();

    $.ajax({

        url:'/history/update-po/'+id,

        type:'POST',

        data:{
            _token:'{{ csrf_token() }}',
            po:value
        },

        success:function(){

            td.data('value',value);

            td.html(value);

        }

    });

});
</script>

<style>
    .table-wrapper{
    max-height: 75vh;
    overflow-y: auto;
    overflow-x: auto;
}

.table-wrapper table{
    margin-bottom: 0;
}

.table-wrapper thead th{
    position: sticky;
    top: 0;
    z-index: 100;
    /* background: #fff; */
    white-space: nowrap;
}
</style>
@endsection
