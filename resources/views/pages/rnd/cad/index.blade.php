@extends('master.master')
@section('title', "CAD - master data")
@section('content')

@php
$detail = $find->detail ?? [];
@endphp

<div class="padding">
    <div class="box">


        {{-- HEADER --}}


        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">

        {{-- ================= HEADER INFO ================= --}}
        <div class="box-body">
            <!-- modal -->
            <div id="modal-upload" style="display:none;">
                <div style="background:#fff;padding:20px;border:1px solid #ddd;max-width:400px">

                    <h4>Upload CAD</h4>

                    <input type="file" id="cad-file" class="form-control"><br>
                    <input type="text" id="master-sample" class="form-control" placeholder="Master Sample / Ukuran">

                    <div class="progress" style="height:20px; display:none;">
                        <div id="progress-bar"
                            style="height:100%;width:0%;background:#28a745;color:#fff;text-align:center;">
                            0%
                        </div>
                    </div>

                    <br>

                    <button class="btn btn-success btn-sm" id="btn-submit-upload">
                        Upload
                    </button>

                    <button class="btn btn-default btn-sm" id="btn-close-modal">
                        Close
                    </button>

                </div>
            </div>
   @php

$detail = $find->detail ?? [];

$priority = [
    'no_',
    'photo',
    'description',
    'article_nr_',
    'article_nr_nw',
    'nw_code',
    'sub_category',
    'qty',
    'remark',
    'cushion',
    'glass',
    'item_w',
    'item_d',
    'item_h',
    'pack_w',
    'pack_d',
    'pack_h',
    'composition',
    'finishing',
    'cbm',
    'total_cbm',
    'value_in_usd',
    'fob_jakarta_in_usd'
];

/*
|--------------------------------------------------------------------------
| SORTING
|--------------------------------------------------------------------------
*/

uksort($detail, function ($a, $b) use ($priority) {

    $posA = array_search($a, $priority);
    $posB = array_search($b, $priority);

    $posA = $posA === false ? 999 : $posA;
    $posB = $posB === false ? 999 : $posB;

    return $posA <=> $posB;
});

/*
|--------------------------------------------------------------------------
| HEADER INFO
|--------------------------------------------------------------------------
*/

$headerFields = [
    'description',
    'article_nr_',
    'article_nr_nw',
    'nw_code',
    'sub_category',
    'remark'
];

$headerData = [];

foreach ($headerFields as $field) {

    if (!empty($detail[$field])) {
        $headerData[$field] = $detail[$field];
    }
}

/*
|--------------------------------------------------------------------------
| DETAIL TABLE
|--------------------------------------------------------------------------
*/

$detailItems = collect($detail)
    ->except(array_merge($headerFields, ['photo']))
    ->toArray();

@endphp
<!-- <table class="table table-bordered">

    <tr>

        {{-- IMAGE --}}
        <td width="220"
            class="text-center align-middle">

            @if(!empty($detail['photo']))
                <img src="{{ $detail['photo'] }}"
                     width="180"
                     style="object-fit:cover;">
            @else
                <span class="text-muted">
                    No Image
                </span>
            @endif

        </td>

        {{-- INFO --}}
        <td>

            <table class="table table-bordered mb-0">

                @foreach($headerData as $key => $value)

                <tr>

                    <td width="220"
                        style="font-weight:bold;background:#f7f7f7;">

                        {{ strtoupper(str_replace('_', ' ', $key)) }}

                    </td>

                    <td>
                        {{ $value ?: '-' }}
                    </td>

                </tr>

                @endforeach

            </table>

        </td>

    </tr>

</table>
<div class="table-responsive">

    <table class="table table-bordered table-striped">

        <thead>

            <tr>

                @foreach($detailItems as $key => $val)

                    <th class="text-center">
                        {{ strtoupper(str_replace('_', ' ', $key)) }}
                    </th>

                @endforeach

            </tr>

        </thead>

        <tbody>

            <tr>

                @foreach($detailItems as $val)

                    <td>
                        {{ $val ?: '-' }}
                    </td>

                @endforeach

            </tr>

        </tbody>

    </table>

</div> -->
            {{-- ================= 2 COLUMN ================= --}}
            <div class="row">

                {{-- ================= LEFT : CAD ================= --}}
                <div class="col-md-8">

                    <div class="box">
                        <div class="box-header">
                            <h4>CAD Files</h4>
                        </div>

                        <div class="box-body">

                            <button class="btn btn-primary btn-sm mb-2" id="btn-upload-cad">
                                Upload CAD
                            </button>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>File</th>
                                            <th>Uploader</th>
                                            <th>Article Code/Nr.</th>
                                            <th>Master Sample</th>
                                            <th>History</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($cads as $i => $cad)
                                   <tr class="cad-row clickable-row"
    data-article="{{ $cad->article_code }}">
                                            <td>{{ $i+1 }}</td>

                                            <td>
                                                <a href="{{ asset('storage/'.$cad->file_path) }}" target="_blank">
                                                    View
                                                </a>
                                            </td>

                                            <td>{{ $cad->user->name ?? '-' }}</td>
                                            <td>{{ $cad->article_code ?? '-' }}</td>
                                            <td>{{ $cad->master_sample ?? '-' }}</td>
                                            <td>
                                                <button
                                                    class="btn btn-info btn-xs btn-history"
                                                    data-article="{{ $cad->article_code }}">
                                                    History
                                                </button>
                                            </td>
                                            <td>


                                                <button class="btn btn-xs btn-danger btn-delete"
                                                    data-id="{{ $cad->id }}">
                                                    Del
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                Belum ada CAD
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>

                </div>






</tbody>

                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="historyDrawer" class="drawer">

    <div class="p-3 border-bottom">

        <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                CAD History
            </h4>

            <button
                class="btn btn-danger btn-sm"
                id="closeDrawer">
                Close
            </button>

        </div>

    </div>

    <div class="p-3">

        {{-- Tombol tampilkan form --}}
        <div class="mb-3">

            <button
                id="btn-show-upload"
                class="btn btn-primary btn-sm">

                <i class="fa fa-upload"></i>
                Upload New Version

            </button>

        </div>

        {{-- Form Upload (Hidden) --}}
        <div
            id="upload-card"
            class="card mb-3"
            style="display:none;">

            <div class="card-body">

                <label>Upload New CAD Version</label>

                <input
                    type="file"
                    id="drawer-cad-file"
                    class="form-control">

                <br>

                <input
                    type="text"
                    id="drawer-master-sample"
                    class="form-control"
                    placeholder="Master Sample">

                <br>

                <button
                    id="btn-upload-version"
                    class="btn btn-success btn-sm">

                    Upload

                </button>

                <button
                    id="btn-cancel-upload"
                    class="btn btn-secondary btn-sm">

                    Cancel

                </button>

                <div
                    id="upload-wrapper"
                    class="progress mt-2"
                    style="display:none;height:22px;">

                    <div
                        id="upload-progress"
                        class="progress-bar progress-bar-striped progress-bar-animated"
                        style="width:0%;">

                        0%

                    </div>

                </div>

            </div>

        </div>

        {{-- History Content --}}
        <div id="historyContent">

        </div>

    </div>

</div>
    <!-- script -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500
});
</script>
    <script>
        let selectedRow = null;
        let currentArticle = null;
$(document).on('click', '#btn-show-upload', function(){

    $('#upload-card').slideDown();

    $(this).hide();

});
$(document).on('click','#btn-upload-version',function(){
    let file = $('#drawer-cad-file')[0].files[0];

    if(!file){

        Toast.fire({
            icon:'warning',
            title:'Choose file first'
        });

        return;
    }

    let btn = $(this);

    let formData = new FormData();

    formData.append('file', file);

    formData.append(
        'article_code',
        currentArticle
    );

    formData.append(
        'master_sample',
        $('#drawer-master-sample').val()
    );

    formData.append(
        '_token',
        '{{ csrf_token() }}'
    );

    btn.prop('disabled', true);

    $('#upload-wrapper').show();

    $.ajax({

        url:'/cad/upload',
        method:'POST',

        data:formData,

        contentType:false,
        processData:false,

        xhr:function(){

            let xhr =
                new window.XMLHttpRequest();

            xhr.upload.addEventListener(
                'progress',
                function(e){

                    if(e.lengthComputable){

                        let percent =
                            Math.round(
                                (e.loaded/e.total)*100
                            );

                        $('#upload-progress')
                            .css(
                                'width',
                                percent+'%'
                            )
                            .text(
                                percent+'%'
                            );
                    }

                }
            );

            return xhr;
        },

        success:function(res){

            Toast.fire({
                icon:'success',
                title:'CAD uploaded'
            });

            $('#upload-card').slideUp();

            $('#btn-show-upload').show();

            $('#drawer-cad-file').val('');
            $('#drawer-master-sample').val('');

            $('#upload-wrapper').hide();

            $('#upload-progress')
                .css('width','0%')
                .text('0%');

            loadHistory(currentArticle);

        },

        error:function(){

            Toast.fire({
                icon:'error',
                title:'Upload failed'
            });

        },

        complete:function(){

            btn.prop('disabled', false);

        }

    });

});
$(document).on('click', '#btn-cancel-upload', function(){

    $('#upload-card').slideUp();

    $('#btn-show-upload').show();

    $('#drawer-cad-file').val('');
    $('#drawer-master-sample').val('');

});
function loadHistory(article){

    $('#historyContent').html(`
        <div class="text-center p-3">
            Loading...
        </div>
    `);

    $.get('/cad/history/' + article, function(res){

        let html = `
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Master Sample</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
        `;

        res.forEach(row => {

            html += `
                <tr>
                    <td>
                        <span class="badge badge-primary">
                            V${row.version}
                        </span>
                    </td>

                    <td>${row.status ?? '-'}</td>

                    <td>${row.master_sample ?? '-'}</td>

                    <td>
                        <a href="/storage/${row.file_path}"
                           target="_blank"
                           class="btn btn-xs btn-info">
                           View
                        </a>

                        <button
                            class="btn btn-xs btn-danger btn-delete-history"
                            data-id="${row.id}">
                            Del
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        $('#historyContent').html(html);

    });

}
$(document).on('click', '.btn-history', function () {

    let article = $(this).data('article');
    currentArticle = $(this).data('article');

    // reset semua highlight
    $('.cad-row').removeClass('cad-row-active');

    // highlight row yg diklik
    selectedRow = $(this).closest('tr');

    selectedRow.addClass('cad-row-active');

    // buka drawer
    $('#historyDrawer').addClass('show');

    $('#historyContent').html(`
        <div class="text-center p-3">
            Loading...
        </div>
    `);

    $.get('/cad/history/' + article, function(res){

        let html = `
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Master Sample</th>

                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
        `;

        res.forEach(row => {

            html += `
                <tr>
                    <td>
                        <span class="badge badge-primary">
                            V${row.version}
                        </span>
                    </td>

                    <td>${row.status ?? '-'}</td>

                    <td>${row.master_sample ?? '-'}</td>


                    <td>
                        <a href="/storage/${row.file_path}"
                           target="_blank"
                           class="btn btn-xs btn-info">
                           View
                        </a>
                         <a href="#"
                           target="_blank"
                           class="btn btn-xs btn-danger">
                           Del
                        </a>
                    </td>

                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        $('#historyContent').html(html);

    });
  loadHistory(currentArticle);
});
// close
$(document).on('click', '#closeDrawer', function () {

    $('#historyDrawer').removeClass('show');

    $('.cad-row').removeClass('cad-row-active');

});
    </script>

    <style>
        /*  */
        .bom-header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #000;
            padding: 8px;
        }

        .bom-info td {
            border: 1px solid #000;
            padding: 6px;
        }

        .bom-table th,
        .bom-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .bom-table th {
            text-align: center;
        }

        /*  */
        .box {
            height: 100%;
        }

        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
        }

        /* freeze header */
        .table-scroll thead th {
            position: sticky;
            top: 0;
            /* background: #f5f5f5; */
            z-index: 2;
        }

        /* optional: freeze kolom pertama */
        .table-scroll th:first-child,
        .table-scroll td:first-child {
            position: sticky;
            left: 0;
            /* background: #fff; */
            z-index: 1;
        }

        /* biar header lebih tegas */
        .table-scroll thead th {
            border-bottom: 2px solid #ddd;
        }
        .drawer{
            position: fixed;
            top: 0;
            right: -650px;
            width: 650px;
            height: 100vh;
            background: #fff;
            z-index: 9999;
            transition: .3s;
            box-shadow: -5px 0 15px rgba(0,0,0,.15);
        }

        .drawer.show{
            right: 0;
        }

        .cad-row-active{
            background: rgba(13,110,253,.12) !important;
            transition: .2s;
        }

        .cad-row-active td{
            border-color: rgba(13,110,253,.35) !important;
        }
        .clickable-row{
    cursor:pointer;
}
    </style>
    @endsection
