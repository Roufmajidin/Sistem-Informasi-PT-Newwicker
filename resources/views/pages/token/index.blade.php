@extends('master.master')
@section('title', "Token Management")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <small class="text-muted">Token Management </small>
                </div>
            </div>
        </div>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#generateModal">
Generate Token
</button>
<div class="modal fade" id="generateModal">

<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Generate Token</h5>
</div>

<div class="modal-body">

<input type="number" id="totalToken" class="form-control" placeholder="Jumlah token">

</div>

<div class="modal-footer">

<button class="btn btn-primary" onclick="generateToken()">Generate</button>

</div>

</div>
</div>

</div>
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered" id="inventoryTable">
                    <thead style="color:white">
                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th class="sticky">Token</th>
                            <th>Company</th> <!-- tambah aksi -->

                            <!-- <th>Company</th> -->
                            <th>email</th>
                            <th>duration</th>
                            <th>expired at</th>
                            <th>used</th>

                        </tr>
                    </thead>
                    <tbody id="buyerTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap4-editable/js/bootstrap-editable.min.js"></script>
<script>
    function generateToken(){

let total=$('#totalToken').val();

$.post('/generate-token',{
total:total,
_token:'{{ csrf_token() }}'
},function(){

$('#generateModal').modal('hide');

loadTokens();

});

}
    loadTokens();

function loadTokens(){

$.get('/token-list',function(data){

let html='';

let no=1;

data.forEach(function(t){
  let expiredAt = null;
let status = '';

if(t.created_at && t.duration){

let created = new Date(t.created_at);
expiredAt = new Date(created.getTime() + (t.duration * 60000));

}

/* ======================
   CEK STATUS
====================== */

if(expiredAt && expiredAt < new Date()){

status = '<span style="color:red;font-weight:bold">Expired</span>';

}
else if(t.used){

status = '<span style="color:green">Used</span>';

}
else{

status = '<span style="color:orange">Unused</span>';

}

/* ======================
   FORMAT EXPIRED DATE
====================== */

let expiredText = '-';

if(expiredAt){

expiredText = expiredAt.toLocaleString();

}
html+=`
<tr>

<td>${no++}</td>

<td>${t.token}</td>

<td contenteditable="true" onblur="updateToken(${t.id},'company_name',this.innerText)">
${t.company_name ?? ''}
</td>

<td contenteditable="true" onblur="updateToken(${t.id},'email',this.innerText)">
${t.email ?? ''}
</td>

<td contenteditable="true"
    title="Format: 4 D / 2 H / 30 M"
    onblur="updateToken(${t.id},'duration',this.innerText)">
${t.duration}
</td>

<td>${t.expired_at ?? '-'}</td>

<td>${status}</td>
</tr>
`;

});

$('#buyerTableBody').html(html);

});

}
// updae token
function updateToken(id,field,value){

console.log('UPDATE',id,field,value);

value = value.trim();

if(field == 'duration'){

let number = parseInt(value);
let unit = value.replace(/[0-9]/g,'').trim().toUpperCase();

if(unit == 'D'){
value = number * 1440;
}
else if(unit == 'H'){
value = number * 60;
}
else if(unit == 'M'){
value = number;
}

}

$.post('/update-token',{
id:id,
field:field,
value:value,
_token:'{{ csrf_token() }}'
},function(res){

console.log(res);

if(!res.status){
alert('Update gagal');
}

});



/* =====================
   UPDATE DATABASE
===================== */

$.post('/update-token',{
id:id,
field:field,
value:value,
_token:'{{ csrf_token() }}'
},function(res){

if(!res.status){
alert('Update gagal');
}

});

}
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
