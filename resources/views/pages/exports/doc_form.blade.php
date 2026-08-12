@extends('master.master')
{{-- @sec --}}
@section('btn')
@php

$isEdit = isset($document);

@endphp
<div class="btn-group shadow-sm">

    <a href="/export/doc_exports"
       class="btn {{ request()->routeIs('export.ipl') ? 'btn-success' : 'btn-light' }}">

        <i class="fa fa-file-alt"></i>

        Form Upload

    </a>

    <a href="{{route('export.document.index')}}"
       class="btn {{ request()->routeIs('export.document.index') ? 'btn-success' : 'btn-light' }}">

        <i class="fa fa-folder-open"></i>

        Documents

    </a>

</div>

@endsection
@section('content')

<div class="container-fluid mt-4">

<div class="card shadow export-card">

<div class="card-header bg-white">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-1">

                <i class="fa fa-file-export text-success"></i>

           @if($isEdit)
           Edit Export Document
          @else Export Document
@endif
            </h4>

            <small class="text-muted">

                #ikan hiu ikan hiu, hiu hiu ikan paus

            </small>

        </div>

       <button
            type="button"
            id="btnSave"
            class="btn btn-success">

            <i class="fa fa-save"></i>

            Save

        </button>

    </div>

</div>

<div class="card-body mt-4">

<form
    id="exportDocumentForm"
    enctype="multipart/form-data">

    @csrf

    @if($isEdit)

        @method('PUT')

        <input
            type="hidden"
            id="document_id"
 value="{{ $document->id ?? '' }}">
    @endif
<div class="row">

<div class="col-md-6">

<div class="form-group">

    <label>No PO</label>

   <input
    type="hidden"
    id="po_id"
    name="po_id"
    value="{{ $document->po_id ?? '' }}">

    <input
        type="text"
        class="form-control"
        id="po_no"
        name="po_no"
        placeholder="Klik untuk memilih PO"
       value="{{ optional(optional($document)->po)->order_no }}"
        readonly
        style="cursor:pointer;background:#fff;">

</div>
</div>

<div class="col-md-6">

<div class="form-group">

<label>Buyer Name</label>

<input
    type="text"
    class="form-control"
    id="buyer_name"
    name="buyer_name"
    readonly
   value="{{ $document->buyer_name ?? '' }}">
</div>

</div>

@php

$uploads = [

'#1 Shipping Instruction'      => 'shipping_instruction',

'#2 Delivery Order'            => 'delivery_order',

'#3 Invoice'                   => 'invoice',

'#4 Packing List'              => 'packing_list',

'#5 Bill Of Lading'            => 'bl',

'#6 Certificate Of Origin'     => 'coo',

'#7 Certificate Fumigation'    => 'fumigation',

'#8 V-Legal'                   => 'v_legal',

'#9 Phyto'                     => 'phyto',

'#10 ISF'                      => 'isf',

'#11 Lacey Plant'              => 'lacey_plant',

'#12 Lacey Animal'             => 'lacey_animal',

];

@endphp
@foreach($uploads as $title=>$name)

<div class="col-md-6 mb-4">

    <div class="upload-card">

        <div class="upload-title">
            {{ $title }}
        </div>

        @if(in_array($name,['invoice','packing_list']))

            <input
                type="hidden"
                name="{{ $name }}"
               id="{{ $name }}_id"
                value="
                @if($name=='invoice')
                    {{ $document->invoice_id ?? '' }}
                @elseif($name=='packing_list')
                    {{ $document->packing_list_id ?? '' }}
                @endif">

            <div class="input-group">

                <input
    type="text"
    class="form-control"
    id="{{ $name }}_text"
    readonly
    value="@if($name=='invoice')
        {{ optional(optional($document)->invoice)->invoice_no }}
    @elseif($name=='packing_list')
        {{ optional(optional($document)->packingList)->invoice_no }}
    @endif"
    placeholder="Belum dipilih">

                <button
                    type="button"
                    class="btn btn-primary btn-choose-document"
                    data-type="{{ $name }}">

                    <i class="fa fa-search"></i>
                    Choose

                </button>

            </div>

        @else
            @php
                    $oldFile = isset($document)
                        ? $document->files->firstWhere('document_type', $name)
                        : null;
                @endphp
            <label class="upload-area">

             <input
    type="file"
    name="{{ $name }}"
    class="file-input d-none">

                <i class="fa fa-cloud-upload-alt"></i>

                <div>Klik untuk upload</div>

                <small>PDF / Excel / Image</small>

            </label>

           <div class="file-preview">

@if($oldFile)

<div class="file-box">

    <div class="file-left">

        <i class="fa {{ \Illuminate\Support\Str::contains($oldFile->mime_type, 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file' }}"></i>

        <div>

            <div class="file-name">

                {{ $oldFile->original_name }}

            </div>

            <div class="file-size">

                {{ number_format($oldFile->file_size/1024,1) }} KB

            </div>

        </div>

    </div>

    <div class="d-flex">

        <a
            href="{{ asset('storage/'.$oldFile->file_path) }}"
            target="_blank"
            class="mr-2 text-info">

            <i class="fa fa-eye"></i>

        </a>

        <i
            class="fa fa-times text-danger file-remove"
            data-existing="{{ $oldFile->id }}"
            style="cursor:pointer"></i>

    </div>

</div>

@endif

</div>

        @endif

    </div>

</div>

@endforeach
<div class="col-12">

    <div class="upload-card">

        <div class="upload-title">
            Declarations
        </div>

        <label class="upload-area">

            <input
                type="file"
                name="declarations[]"
                class="file-input"
                multiple
                hidden>

            <i class="fa fa-copy"></i>

            <div>Upload Multiple File</div>

            <small>Boleh lebih dari satu file</small>

        </label>

       <div class="file-preview">

@if(isset($document))

@foreach($document->files->where('document_type','declaration') as $file)

<div class="file-box">

    <div class="file-left">

        <i class="fa fa-file-pdf text-danger"></i>

        <div>

            <div class="file-name">

                {{ $file->original_name }}

            </div>

            <div class="file-size">

                {{ number_format($file->file_size/1024,1) }} KB

            </div>

        </div>

    </div>

    <div>

        <a
            href="{{ asset('storage/'.$file->file_path) }}"
            target="_blank">

            <i class="fa fa-eye text-info"></i>

        </a>

        <i
            class="fa fa-times text-danger file-remove"
            data-existing="{{ $file->id }}"></i>

    </div>

</div>

@endforeach

@endif

</div>

    </div>

</div>
</div>

<div class="col-md-6 mt-3">

<div class="form-group">

<label>No PEB</label>

<input
type="text"
class="form-control"
name="peb_no" 
value="{{ $document->peb_no ?? '' }}">

</div>

</div>

</div>

</form>

</div>

</div>

</div>
{{-- modals po --}}
<div class="modal fade"
     id="poModal"
     tabindex="-1"
     data-bs-backdrop="static">

    <div class="modal-dialog modal-xl"
         style="max-width:92vw;">

        <div class="modal-content">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">
                    Choose PO
                </h5>

                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input
                    type="text"
                    id="searchPo"
                    class="form-control mb-3"
                    placeholder="Search PO">

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>PO</th>

                                <th>Buyer</th>

                                <th>Country</th>

                                <th>Shipment</th>

                                <th></th>

                            </tr>

                        </thead>

                        <tbody id="poTable">

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- modals --}}
{{-- =======================
    MODAL LIST INVOICE
======================== --}}
<div class="modal fade"
     id="documentModal"
     tabindex="-1"
     data-bs-backdrop="static">

    <div class="modal-dialog modal-xl"
         style="max-width:92vw;">

        <div class="modal-content shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="fa fa-folder-open me-2"></i>

                    Choose Invoice / Packing List

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row mb-3">

                    <div class="col-md-6">

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="fa fa-search"></i>

                            </span>

                            <input
                                id="searchDocument"
                                class="form-control"
                                placeholder="Search Invoice / Buyer / Sales Order">

                        </div>

                    </div>

                </div>

                <div class="table-responsive"
                     style="max-height:70vh;overflow:auto;">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light sticky-top">

                            <tr>

                                <th width="50">No</th>

                                <th width="180">
                                    Invoice No
                                </th>

                                <th width="150">
                                    Sales Order
                                </th>

                                <th>
                                    Buyer
                                </th>

                                <th width="130">
                                    Container
                                </th>

                                <th width="80">
                                    Items
                                </th>

                                <th width="120">
                                    ETD
                                </th>

                                <th width="170">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody id="documentTable">

                            <tr>

                                <td colspan="8"
                                    class="text-center text-muted py-5">

                                    <i class="fa fa-spinner fa-spin"></i>

                                    Loading...

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>



{{-- =======================
    MODAL DETAIL
======================== --}}

<div class="modal fade"
     id="detailModal"
     tabindex="-1"
     data-bs-backdrop="static">

    <div class="modal-dialog modal-xl"
         style="max-width:95vw;">

        <div class="modal-content">

            <div class="modal-header bg-info text-white">

                <h5 class="modal-title">

                    <i class="fa fa-file-text me-2"></i>

                    Invoice Detail

                </h5>

                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div id="invoiceHeader">

                </div>

                <hr>

                <div class="table-responsive"
                     style="max-height:60vh;overflow:auto;">

                    <table class="table table-bordered table-sm">

                        <thead class="table-light sticky-top">

                            <tr>

                                <th width="140">

                                    PO

                                </th>

                                <th width="120">

                                    Article

                                </th>

                                <th>

                                    Description

                                </th>

                                <th width="80">

                                    Qty

                                </th>

                                <th width="80">

                                    Box

                                </th>

                                <th width="90">

                                    CBM

                                </th>

                                <th width="120">

                                    Unit Price

                                </th>

                                <th width="120">

                                    Total

                                </th>

                            </tr>

                        </thead>

                        <tbody id="detailTable">

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    <div id="invoiceTotal">

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>
<style>
    .export-card{

    overflow: visible;

}

.export-card .card-header{

    position: sticky;

    top: 70px;

    z-index: 1040;

    background:#fff;

    padding:20px 25px;

    /* margin:-24px -24px 20px -24px; */

    border-bottom:1px solid #e9ecef;

    box-shadow:0 3px 12px rgba(0,0,0,.08);

}
   


.form-group label{

font-weight:600;

font-size:13px;

margin-bottom:8px;

}

.form-control{

height:46px;

border-radius:10px;

border:1px solid #dbe3ec;

}

.form-control:focus{

box-shadow:none;

border-color:#28a745;

}

.upload-card{

background:#fff;

border:1px solid #e9eef5;

border-radius:14px;

padding:18px;

transition:.2s;

height:100%;

}

.upload-card:hover{

box-shadow:0 6px 18px rgba(0,0,0,.08);

}

.upload-title{

font-weight:600;

margin-bottom:15px;

font-size:14px;

}

.upload-area{

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    height:85px;

    border:2px dashed #b8d9c1;

    border-radius:10px;

    cursor:pointer;

    transition:.2s;

    background:#fbfefc;

}

.upload-area i{

    font-size:24px;

    color:#28a745;

    margin-bottom:6px;

}

.upload-area div{

    font-size:14px;

    font-weight:600;

}

.upload-area small{

    font-size:11px;

    color:#888;

}
.upload-area:hover{

background:#f1fbf4;

border-color:#28a745;

}

.upload-area i{

font-size:34px;

color:#28a745;

margin-bottom:10px;

}

.upload-area div{

font-weight:600;

margin-bottom:5px;

}

.upload-area small{

color:#999;

}

.btn-success{

border-radius:10px;

padding:10px 24px;

}

@media(max-width:768px){

.upload-area{

height:120px;

}

}
.file-preview{

    margin-top:10px;

}

.file-box{

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:10px 14px;

    border:1px solid #e5e7eb;

    border-radius:10px;

    background:#fafafa;

}

.file-left{

    display:flex;

    align-items:center;

}

.file-left i{

    font-size:22px;

    color:#dc3545;

    margin-right:10px;

}

.file-name{

    font-size:13px;

    font-weight:600;

}

.file-size{

    font-size:11px;

    color:#888;

}

.file-remove{

    color:#dc3545;

    cursor:pointer;

}

</style>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>

<script>
$(document).on('change', '.file-input', function () {

    let preview = $(this)
        .closest('.upload-card')
        .find('.file-preview');
    console.log('CHANGE', this.files);
    preview.empty();

    Array.from(this.files).forEach(function(file, index){

        let size = (file.size / 1024 / 1024).toFixed(2);

        let icon = getIcon(file.name);

        preview.append(`
            <div class="file-box mb-2" data-index="${index}">

                <div class="file-left">

                    <i class="fa ${icon}"></i>

                    <div>

                        <div class="file-name">
                            ${file.name}
                        </div>

                        <div class="file-size">
                            ${size} MB
                        </div>

                    </div>

                </div>

                <i class="fa fa-times text-danger file-remove"
                   style="cursor:pointer"></i>

            </div>
        `);

    });

});
$(document).on('click', '.file-remove', function () {

    let box = $(this).closest('.file-box');

    let preview = box.closest('.file-preview');

    let input = preview
        .closest('.upload-card')
        .find('.file-input')[0];

    // Hapus hanya preview
    box.remove();

    // Kalau semua preview habis, kosongkan input
    if (preview.find('.file-box').length === 0) {
        input.value = '';
    }

});
function getIcon(fileName){

    let ext = fileName.split('.').pop().toLowerCase();

    switch(ext){

        case 'pdf':
            return 'fa-file-pdf text-danger';

        case 'xls':
        case 'xlsx':
        case 'csv':
            return 'fa-file-excel text-success';

        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'webp':
            return 'fa-file-image text-primary';

        case 'doc':
        case 'docx':
            return 'fa-file-word text-primary';

        default:
            return 'fa-file';
    }

}
$(document).on('click','.file-remove',function(){

    let card=$(this).closest('.upload-card');

    card.find('.file-input').val('');

    card.find('.file-preview').html('');

});
$(document).on('click','.upload-area',function(e){
    console.log('CLICK UPLOAD');
    if($(e.target).hasClass('file-remove')) return;

    $(this).find('.file-input').trigger('click');

});
// load list 
let currentType='';

$(document).on('click','.btn-choose-document',function(){
    currentChooseType = $(this).data('type');

    currentType=$(this).data('type');
    console.log(currentChooseType);

    $('#documentModal').modal('show');

    loadDocument();

});
function loadDocument(keyword=''){

    $.ajax({

        url:"{{ route('export.document.list') }}",

        type:"GET",

        data:{
            keyword:keyword
        },

        success:function(res){

            let html='';

            if(res.length==0){

                html=`
                    <tr>

                        <td colspan="8" class="text-center">

                            Tidak ada data

                        </td>

                    </tr>
                `;

            }else{

                $.each(res,function(i,row){

                    html+=`

                    <tr>

                        <td>${i+1}</td>

                        <td>${row.invoice_no}</td>

                        <td>${row.sales_order}</td>

                        <td>${row.buyer}</td>

                        <td>${row.container}</td>

                        <td class="text-center">

                            ${row.items}

                        </td>

                        <td>${row.etd}</td>

                        <td>

                            <button
                                class="btn btn-info btn-sm btn-detail"
                                data-id="${row.id}">

                                <i class="fa fa-eye"></i>

                            </button>

                            <button
                                class="btn btn-success btn-sm btn-select"
                                data-id="${row.id}"
                                data-no="${row.invoice_no}">

                                <i class="fa fa-check"></i>

                            </button>

                        </td>

                    </tr>

                    `;

                });

            }

            $('#documentTable').html(html);

        }

    });

}
$(document).on('click', '.btn-detail', function () {

    let id = $(this).data('id');

    let url = "{{ route('export.document.detail', ':id') }}";
    url = url.replace(':id', id);

    $.get(url, function (res) {

        // buka modal
        $('#detailModal').modal('show');

        // ================= HEADER =================
        let h = res.header;

        $('#invoiceHeader').html(`
            <div class="row">

                <div class="col-md-3">
                    <b>Invoice No</b><br>
                    ${h.invoice_no ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>Sales Order</b><br>
                    ${h.sales_order ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>Buyer</b><br>
                    ${h.buyer ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>Container</b><br>
                    ${h.container_type ?? '-'}
                </div>

            </div>

            <div class="row mt-3">

                <div class="col-md-3">
                    <b>Container No</b><br>
                    ${h.container_no ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>Seal No</b><br>
                    ${h.seal_no ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>ETD</b><br>
                    ${h.etd ?? '-'}
                </div>

                <div class="col-md-3">
                    <b>ETA</b><br>
                    ${h.eta ?? '-'}
                </div>

            </div>
        `);

        // ================= DETAIL ITEM =================
        let html = '';

        $.each(res.items, function (i, item) {

            html += `
                <tr>

                    <td>${item.po}</td>

                    <td>${item.article}</td>

                    <td>${item.description}</td>

                    <td class="text-center">${item.qty}</td>

                    <td class="text-center">${item.box}</td>

                    <td class="text-end">${item.cbm}</td>

                    <td class="text-end">${Number(item.price).toLocaleString()}</td>

                    <td class="text-end">${Number(item.total).toLocaleString()}</td>

                </tr>
            `;

        });

        $('#detailTable').html(html);

        // ================= TOTAL =================
        $('#invoiceTotal').html(`
            <div class="row">

                <div class="col-md-3">
                    <div class="alert alert-primary mb-0">
                        <b>Total Qty</b><br>
                        ${res.totals.qty}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="alert alert-info mb-0">
                        <b>Total Box</b><br>
                        ${res.totals.box}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="alert alert-success mb-0">
                        <b>Total CBM</b><br>
                        ${res.totals.total_cbm}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="alert alert-warning mb-0">
                        <b>Grand Total</b><br>
                        ${Number(res.totals.total_price).toLocaleString()}
                    </div>
                </div>

            </div>
        `);

    });

});
let currentChooseType = '';
// eye 
$(document).on('click', '.btn-select', function () {

    let id        = $(this).data('id');
    let invoiceNo = $(this).data('no');

    // isi hidden id
    $('#' + currentChooseType + '_id').val(id);

    // isi textbox
    $('#' + currentChooseType + '_text').val(invoiceNo);
  console.log(currentChooseType);
    console.log(id);
    console.log(invoiceNo);

    // tutup modal
    $('#documentModal').modal('hide');

});
// save 
$('#btnSave').click(function(){

    let form = $('#exportDocumentForm')[0];

    let fd = new FormData(form);

let url = "{{ route('export.document.store') }}";

@if(isset($document))

url = "{{ route('export.document.update',$document->id) }}";

fd.append('_method','PUT');

@endif
fd.append(
    'deleted_files',
    JSON.stringify(deletedFiles)
);

    $.ajax({

     url: url,
        type: "POST",

        data: fd,

        processData: false,

        contentType: false,

        headers:{
            'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
        },

        beforeSend:function(){

            $('#btnSave')
                .prop('disabled',true)
                .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        },

        success:function(res){

            $('#btnSave')
                .prop('disabled',false)
                .html('<i class="fa fa-save"></i> Save');

            alert('Document berhasil disimpan');

            // location.reload();

        },

        error:function(xhr){

            $('#btnSave')
                .prop('disabled',false)
                .html('<i class="fa fa-save"></i> Save');

            console.log(xhr.responseJSON);

            alert('Gagal menyimpan');

        }

    });

});
// list po 
$(document).on('click','.btn-select-po',function(){

    let id=$(this).data('id');

    let po=$(this).data('po');

    let buyer=$(this).data('buyer');

    $('#po_id').val(id);

    $('#po_no').val(po);

    $('#buyer_name').val(buyer);

    loadPoItems(id);

    $('#poModal').modal('hide');

});
function loadPoItems(id){

    $.get(
        "{{ route('export.po.detail',':id') }}".replace(':id',id),
        function(res){

            let html='';

            $.each(res.details,function(i,row){

                html+=`

                <tr>

                    <td>${i+1}</td>

                    <td>${row.item_code}</td>

                    <td>${row.description}</td>

                    <td>${row.qty}</td>

                    <td>${row.unit}</td>

                    <td>${Number(row.price).toLocaleString()}</td>

                </tr>

                `;

            });

            $('#poDetailTable').html(html);

        }
    );

}
// on click
$(document).on('click','#po_no',function(){

    $('#poModal').modal('show');

    loadPo();

});
$(document).on('keyup', '#searchPo', function () {

    console.log('SEARCH:', $(this).val());

    loadPo($(this).val());

});
function loadPo(keyword = '') {

    $.get("{{ route('export.po.list') }}", {

        q: keyword

    }, function(res){
    // console.log(res);

        let html = '';

        $.each(res, function(i,row){

            html += `
            <tr>

                <td>${i+1}</td>

                <td>${row.order_no}</td>

                <td>${row.company_name}</td>

                <td>${row.country}</td>

                <td>${row.shipment_date ?? '-'}</td>

                <td>

                    <button
                        class="btn btn-success btn-sm btn-select-po"
                        data-id="${row.id}"
                        data-po="${row.order_no}"
                        data-buyer="${row.company_name}">

                        <i class="fa fa-check"></i>

                    </button>

                </td>

            </tr>
            `;

        });

        $('#poTable').html(html);

    });

}
let deletedFiles = [];

$(document).on('click','.file-remove',function(){

    let id = $(this).data('existing');

    if(id){
        deletedFiles.push(id);
    }

    $(this).closest('.file-box').remove();

});
</script>
@endsection