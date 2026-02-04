@extends('master.master')

@section('title', "Data Supplier")
@section('content')

<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>Data Suplier</h2>
        </div>

        <div class="box-body">
        <div class="row">

{{-- ================= JENIS ================= --}}
<div class="col-md-4">

<h6>Jenis Supplier</h6>
   <button class="btn btn-default btn-sm" id="addJenis">
                                ← Add Jenis Sub
                            </button>
<table class="table table-bordered" id="tblJenis">
<thead>

    <tr>
        <th colspan="2" class="text-center">
            <h6 class="mb-0">Jenis Supplier</h6>
        </th>
    </tr>

    <tr>
        <th>Jenis Sub</th>
        <th width="80">Action</th>
    </tr>

</thead>

<tbody>
@foreach($jenis as $j)
<tr data-id="{{$j->id}}">
    <td contenteditable="true">{{$j->name}}</td>
    <td>
        <button class="btn btn-primary btn-sm saveJenis">Save</button>
    </td>
</tr>
@endforeach
</tbody>
</table>

</div>



{{-- ================= SUPPLIER ================= --}}
<div class="col-md-8">

<h6>Supplier</h6>
   <button class="btn btn-default btn-sm" id="addSupplier">
                                ← Add Sub
                            </button>
<table class="table table-bordered" id="tblSupplier">
<thead>
    <tr>
        <th colspan="4" class="text-center">Data Supplier</th>
    </tr>
    <tr>
        <th width="">Nama Sub</th>
        <th width="50">Alamat</th>
        <th >Jenis</th>
        <th width="80">Aksi</th>
    </tr>
</thead>

<tbody>
@foreach($suppliers as $s)
<tr data-id="{{$s->id}}">
    <td contenteditable="true">{{$s->name}}</td>
    <td contenteditable="true">{{$s->alamat}}</td>
    <td>
        <select class="form-control">
            @foreach($jenis as $j)
            <option value="{{$j->id}}"
            {{$s->jenis_supplier_id==$j->id?'selected':''}}>
            {{$j->name}}
            </option>
            @endforeach
        </select>
    </td>

    <td>
        <button class="btn btn-primary btn-sm saveSupplier">Save</button>
    </td>
</tr>
@endforeach
</tbody>
</table>

</div>

</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(function(){

const csrf = "{{ csrf_token() }}";


/* ================= ADD JENIS ================= */
$('#addJenis').click(function(){

    $('#tblJenis tbody').prepend(`
    <tr class="new-row">
        <td contenteditable="true"></td>
        <td>
            <button class="btn btn-success btn-sm saveJenis">Save</button>
        </td>
    </tr>
    `);

});


/* ================= SAVE JENIS ================= */
$(document).on('click','.saveJenis',function(){

    let tr = $(this).closest('tr');

    let id   = tr.data('id');
    let name = tr.find('td:eq(0)').text().trim();

    if(!name) return alert('Name wajib');

    if(tr.hasClass('new-row')){

        $.post('/jenis/store',{_token:csrf,name:name})
        .done(()=>location.reload());

        return;
    }

    $.post('/jenis/update/'+id,{
        _token:csrf,
        name:name
    });

});



/* ================= ADD SUPPLIER ================= */
$('#addSupplier').click(function(){

let jenisOptions = `@foreach($jenis as $j)
<option value="{{$j->id}}">{{$j->name}}</option>
@endforeach`;

$('#tblSupplier tbody').prepend(`
<tr class="new-row">
<td contenteditable="true"></td>
<td contenteditable="true"></td>
<td><select class="form-control">${jenisOptions}</select></td>
<td><button class="btn btn-success btn-sm saveSupplier">Save</button></td>
</tr>
`);

});


/* ================= SAVE SUPPLIER ================= */
$(document).on('click','.saveSupplier',function(){

let tr = $(this).closest('tr');

let id     = tr.data('id');
let name   = tr.find('td:eq(0)').text().trim();
let alamat = tr.find('td:eq(1)').text().trim();
let jenis  = tr.find('select').val();

if(!name) return alert('Name wajib');

if(tr.hasClass('new-row')){

    $.post('/supplier/store',{
        _token:csrf,
        name:name,
        alamat:alamat,
        jenis_supplier_id:jenis
    }).done(()=>location.reload());

    return;
}

$.post('/supplier/update/'+id,{
    _token:csrf,
    name:name,
    alamat:alamat,
    jenis_supplier_id:jenis
});

});

});
</script>

@endsection
