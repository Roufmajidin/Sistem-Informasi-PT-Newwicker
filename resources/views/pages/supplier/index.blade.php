@extends('master.master')

@section('title', 'Data Supplier')

@section('content')


<style>
.supplier-page{padding:20px}
.supplier-card{background:#fff;border-radius:14px;border:1px solid #edf0f2;box-shadow:0 4px 18px rgba(0,0,0,.06);overflow:hidden}
.supplier-header{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:18px 20px;border-bottom:1px solid #edf0f2}
.supplier-title{margin:0;font-size:20px;font-weight:700;color:#1f2937}
.supplier-subtitle{margin:4px 0 0;font-size:13px;color:#6b7280}
.supplier-content{padding:20px}
.supplier-grid{display:grid;grid-template-columns:minmax(280px,34%) minmax(0,66%);gap:18px}
.supplier-section{min-width:0}
.supplier-section-header{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px}
.supplier-section-title{margin:0;font-size:14px;font-weight:700;color:#334155}
.supplier-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px}
.supplier-search{width:430px;max-width:100%}
.supplier-table-wrapper{width:100%;max-height:650px;overflow:auto;border:1px solid #e2e8f0;border-radius:9px;background:#fff}
.supplier-table{width:100%;min-width:760px;border-collapse:separate;border-spacing:0;margin:0}

/* Tabel Jenis Supplier dibuat compact agar kolom Action dekat dengan nama */
#tblJenis{
    width:auto;
    min-width:0;
    table-layout:auto;
}
#tblJenis th:nth-child(1),
#tblJenis td:nth-child(1){
    width:50px;
    min-width:50px;
}
#tblJenis th:nth-child(2),
#tblJenis td:nth-child(2){
    width:220px;
    min-width:220px;
}
#tblJenis th:nth-child(3),
#tblJenis td:nth-child(3){
    width:65px;
    min-width:65px;
}
#tblJenis .jenis-supplier-name{
    max-width:190px;
}

.supplier-table th{background:#f8fafc;color:#475569;font-size:12px;font-weight:700;padding:13px 12px;white-space:nowrap;border-bottom:1px solid #dbe2ea;border-right:1px solid #edf0f2;position:sticky;top:0;z-index:20}
.supplier-table td{padding:12px;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;border-right:1px solid #f1f5f9;white-space:nowrap;background:#fff}
.supplier-table tbody tr:hover td{background:#f8fafc}
.supplier-alamat{max-width:230px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.jenis-supplier-name{display:block;max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:text;position:relative}
.jenis-supplier-name:hover{text-decoration:underline;text-decoration-style:dotted;text-underline-offset:3px}
#modalLinkVendor .modal-dialog{max-width:700px;width:95%}
#modalLinkVendor .modal-content{min-height:350px}
#modalLinkVendor .modal-body{padding:25px 30px}
#modalLinkVendor #vendorSearchInput{height:44px;font-size:14px}
.supplier-jenis{min-width:150px;width:100%;height:30px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;background:#fff}.ajax-loading{opacity:.55;pointer-events:none}
.supplier-editable{outline:none;min-width:100px}
.supplier-editable:focus{background:#eff6ff!important;box-shadow:inset 0 0 0 1px #93c5fd}
.btn-supplier{border:0;border-radius:8px;padding:9px 14px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:7px;cursor:pointer}
.btn-default-supplier{background:#f3f4f6;color:#374151}
.rekening-cell{min-width:150px;cursor:pointer;border-radius:7px;padding:6px 8px}
.rekening-cell:hover{background:#eff6ff}
.rekening-cell strong{display:block;font-size:12px;color:#1f2937}
.rekening-cell small{display:block;margin-top:2px;color:#64748b;font-size:10px}
.rekening-empty{display:inline-flex;align-items:center;gap:5px;color:#64748b;border:1px dashed #cbd5e1;border-radius:6px;padding:6px 8px;font-size:11px}
.rekening-empty:hover{color:#2563eb;border-color:#93c5fd}
.vendor-search-wrapper{position:relative}
#vendorSuggestions{position:absolute;left:0;right:0;top:calc(100% + 2px);z-index:9999;display:none;max-height:300px;overflow-y:auto;background:#fff;border:1px solid #dbe2ea;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12)}
.vendor-suggestion{padding:10px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer}
.vendor-suggestion:hover{background:#f8fafc}
.vendor-suggestion-name{font-size:13px;font-weight:700;color:#1f2937}
.vendor-suggestion-detail{margin-top:3px;font-size:11px;color:#64748b}
.selected-vendor-card{padding:12px;border:1px solid #bfdbfe;background:#eff6ff;border-radius:9px}
.selected-vendor-name{font-weight:700;color:#1d4ed8}
.selected-vendor-detail{margin-top:6px;font-size:12px;line-height:1.7;color:#475569}
@media(max-width:1000px){.supplier-grid{grid-template-columns:1fr}}
@media(max-width:700px){.supplier-page{padding:10px}.supplier-content{padding:12px}.supplier-header{flex-direction:column;align-items:flex-start}.supplier-toolbar{flex-direction:column;align-items:stretch}.supplier-search{width:100%}}
</style>

<div class="supplier-page">
<div class="supplier-card">

<div class="supplier-header">
    <div>
        <h3 class="supplier-title">Data Supplier</h3>
        <p class="supplier-subtitle">Kelola supplier, jenis supplier, dan rekening vendor</p>
    </div>
</div>

<div class="supplier-content">
<div class="supplier-grid">

<div class="supplier-section">
    <div class="supplier-section-header">
        <h4 class="supplier-section-title">Jenis Supplier</h4>
        <button type="button" class="btn-supplier btn-default-supplier" id="addJenis">
            <i class="fa fa-plus"></i> Add Jenis Sub
        </button>
    </div>

    <div class="supplier-table-wrapper">
        <table class="table table-bordered supplier-table" id="tblJenis">
            <thead>
                <tr><th colspan="3" class="text-center">Jenis Supplier</th></tr>
                <tr><th width="60">Id</th><th>Jenis Sub</th><th width="80">Action</th></tr>
            </thead>
            <tbody>
            @foreach($jenis as $j)
                <tr data-id="{{$j->id}}">
                    <td>{{$j->id}}</td>
                    <td><div class="supplier-editable jenis-supplier-name" contenteditable="true" title="{{$j->name}}">{{$j->name}}</div></td>
                    <td><button type="button" class="btn btn-primary btn-sm saveJenis">Save</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="supplier-section">
    <div class="supplier-section-header">
        <h4 class="supplier-section-title">Data Supplier</h4>
        <button type="button" class="btn-supplier btn-default-supplier" id="addSupplier">
            <i class="fa fa-plus"></i> Add Sub
        </button>
    </div>

    <div class="supplier-toolbar">
        <form method="GET" action="{{ url('/supplier') }}" class="supplier-search" id="supplierSearchForm">
            <div class="input-group input-group-sm">
                <input type="text" name="search" id="supplierSearchInput" class="form-control"
                       placeholder="Cari supplier, alamat, rekening, bank, vendor..."
                       value="{{ $search ?? '' }}" autocomplete="off">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="btnClearSupplierSearch" title="Reset pencarian" style="{{ empty($search) ? 'display:none;' : '' }}">
                        <i class="fa fa-times"></i>
                    </button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>

        <div id="supplierSearchInfo" style="font-size:11px;color:#64748b;white-space:nowrap;{{ empty($search) ? 'display:none;' : '' }}">
            Hasil: <strong>{{ $suppliers->count() }}</strong>
        </div>
    </div>

    <div class="supplier-table-wrapper">
        <table class="table table-bordered supplier-table" id="tblSupplier">
            <thead>
<tr>
    <th>Id</th>
    <th>Nama Sub</th>
    <th>Jenis</th>
    <th>Rekening</th>
    <th>Aksi</th>
    <th>Alamat</th>
</tr>
</thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr data-id="{{$s->id}}">
                    <td>{{$s->id}}</td>
                    <td class="supplier-editable" contenteditable="true">{{$s->name}}</td>
                    <td>
                        <select class="form-control supplier-jenis">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenis as $j)
                                <option value="{{$j->id}}" {{$s->jenis_supplier_id==$j->id?'selected':''}}>{{$j->name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="rekening-cell" data-id="{{$s->id}}" data-vendor-id="{{$s->vendor_id ?: ''}}">
                            @if($s->vendor)
                                <strong>{{ $s->vendor->nomor_rekening ?: '-' }}</strong>
                                <small>
                                    {{ $s->vendor->bank ?: '-' }}
                                    @if($s->vendor->nama_rekening) — A/N {{ $s->vendor->nama_rekening }} @endif
                                </small>
                            @else
                                <span class="rekening-empty"><i class="fa fa-link"></i> Link Rekening</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm saveSupplier">Save</button>
                    </td>
                    <td class="supplier-editable supplier-alamat" contenteditable="true" title="{{$s->alamat}}">{{$s->alamat}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding:35px;color:#94a3b8">
                        @if(!empty($search))
                            Tidak ada supplier yang cocok dengan pencarian.
                        @else
                            Belum ada data supplier.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
</div>
</div>

<div class="modal fade" id="modalLinkVendor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Link Rekening Vendor</h4>
            </div>

            <div class="modal-body">
                <input type="hidden" id="linkSupplierId">

                <div class="form-group">
                    <label>Nomor Rekening / Nama Vendor / Bank</label>
                    <div class="vendor-search-wrapper">
                        <input type="text" id="vendorSearchInput" class="form-control"
                               autocomplete="off" placeholder="Ketik minimal 2 karakter...">
                        <div id="vendorSuggestions"></div>
                    </div>
                </div>

                <div id="selectedVendor" style="display:none">
                    <div class="selected-vendor-card">
                        <div class="selected-vendor-name" id="selectedVendorName"></div>
                        <div class="selected-vendor-detail">
                            <div><strong>Rekening:</strong> <span id="selectedVendorRekening">-</span></div>
                            <div><strong>Nama Rekening:</strong> <span id="selectedVendorNamaRekening">-</span></div>
                            <div><strong>Bank:</strong> <span id="selectedVendorBank">-</span></div>
                            <div><strong>UNIQ:</strong> <span id="selectedVendorUniq">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnUnlinkVendor">
                    <i class="fa fa-unlink"></i> Lepas Link
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveLinkVendor" disabled>
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){

const csrf = "{{ csrf_token() }}";
let selectedVendorId = null;
let vendorSearchTimer = null;
let supplierSearchTimer = null;

let jenisData = @json($jenis->map(function($j){ return ['id'=>$j->id,'name'=>$j->name]; })->values());

function escapeHtml(value){
    if(value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
}

function jenisOptions(selectedId){
    let html = '<option value="">-- Pilih Jenis --</option>';
    jenisData.forEach(function(j){
        html += '<option value="'+j.id+'" '+(String(selectedId)===String(j.id)?'selected':'')+'>'+escapeHtml(j.name)+'</option>';
    });
    return html;
}

function rekeningHtml(vendor){
    if(!vendor){
        return '<span class="rekening-empty"><i class="fa fa-link"></i> Link Rekening</span>';
    }

    let rekening = escapeHtml(vendor.nomor_rekening || '-');
    let bank = escapeHtml(vendor.bank || '-');
    let nama = vendor.nama_rekening ? ' — A/N ' + escapeHtml(vendor.nama_rekening) : '';

    return '<strong>'+rekening+'</strong><small>'+bank+nama+'</small>';
}

function supplierRowHtml(supplier){
    let alamat = supplier.alamat || '';
    let vendor = supplier.vendor || null;

    return `
        <tr data-id="${supplier.id}">
            <td>${supplier.id}</td>
            <td class="supplier-editable" contenteditable="true">${escapeHtml(supplier.name || '')}</td>
            <td>
                <select class="form-control supplier-jenis">
                    ${jenisOptions(supplier.jenis_supplier_id)}
                </select>
            </td>
            <td>
                <div class="rekening-cell" data-id="${supplier.id}" data-vendor-id="${vendor ? vendor.id : ''}">
                    ${rekeningHtml(vendor)}
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-primary btn-sm saveSupplier">Save</button>
            </td>
            <td class="supplier-editable supplier-alamat" contenteditable="true" title="${escapeHtml(alamat)}">${escapeHtml(alamat)}</td>
        </tr>`;
}

function renderSupplierRows(data){
    let $tbody = $('#tblSupplier tbody');
    $tbody.empty();

    if(!data || !data.length){
        $tbody.html('<tr><td colspan="6" class="text-center" style="padding:35px;color:#94a3b8">Tidak ada supplier yang cocok dengan pencarian.</td></tr>');
        return;
    }

    let html = '';
    data.forEach(function(s){ html += supplierRowHtml(s); });
    $tbody.html(html);
}

function updateSearchInfo(search, count){
    let $info = $('#supplierSearchInfo');
    if(search){
        $info.html('Hasil: <strong>'+count+'</strong>').show();
        $('#supplierTableTitle').text('Data Supplier — Pencarian: "'+search+'"');
        $('#btnClearSupplierSearch').show();
    }else{
        $info.hide().html('');
        $('#supplierTableTitle').text('Data Supplier');
        $('#btnClearSupplierSearch').hide();
    }
}

function refreshSuppliers(search){
    search = search || '';
    $('#tblSupplier').addClass('ajax-loading');

    $.ajax({
        url: "{{ url('/supplier/search') }}",
        type: 'GET',
        data: {q: search},
        success: function(response){
            renderSupplierRows(response.data || []);
            updateSearchInfo(response.search || '', response.count || 0);
        },
        error: function(xhr){
            alert(xhr.responseJSON?.message || 'Gagal mengambil data supplier');
        },
        complete: function(){
            $('#tblSupplier').removeClass('ajax-loading');
        }
    });
}

function refreshJenisTable(){
    let html = '';
    jenisData.forEach(function(j){
        html += `
            <tr data-id="${j.id}">
                <td>${j.id}</td>
                <td><div class="supplier-editable jenis-supplier-name" contenteditable="true" title="${escapeHtml(j.name)}">${escapeHtml(j.name)}</div></td>
                <td><button type="button" class="btn btn-primary btn-sm saveJenis">Save</button></td>
            </tr>`;
    });
    $('#tblJenis tbody').html(html);
}

function updateJenisInSelects(){
    $('.supplier-jenis').each(function(){
        let $select = $(this);
        let current = $select.val();
        $select.html(jenisOptions(current));

        if(current && $select.find('option[value="'+current+'"]').length){
            $select.val(current);
        }else{
            $select.val('');
        }
    });
}

/* ADD JENIS */
$('#addJenis').click(function(){
    $('#tblJenis tbody').prepend(`
        <tr class="new-row">
            <td>-</td>
            <td><div class="supplier-editable jenis-supplier-name" contenteditable="true" title=""></div></td>
            <td><button type="button" class="btn btn-success btn-sm saveJenis">Save</button></td>
        </tr>
    `);
});

/* SAVE JENIS */
$(document).on('input','.jenis-supplier-name',function(){
    $(this).attr('title', $(this).text().trim());
});

$(document).on('click','.saveJenis',function(){
    let button = $(this);
    let tr = button.closest('tr');
    let id = tr.data('id');
    let name = tr.find('td:eq(1)').text().trim();

    if(!name) return alert('Name wajib');

    button.prop('disabled',true).text('Saving...');

    let url = tr.hasClass('new-row') ? '/jenis/store' : '/jenis/update/'+id;

    $.post(url, {_token:csrf,name:name})
        .done(function(response){
            if(!response.success) return alert(response.message || 'Gagal menyimpan jenis supplier');

            if(tr.hasClass('new-row')){
                jenisData.push({id:response.data.id,name:response.data.name});
            }else{
                let index = jenisData.findIndex(function(j){ return String(j.id)===String(id); });
                if(index >= 0) jenisData[index].name = response.data.name;
            }

            jenisData.sort(function(a,b){ return a.name.localeCompare(b.name); });
            refreshJenisTable();
            updateJenisInSelects();
        })
        .fail(function(xhr){
            alert(xhr.responseJSON?.message || 'Gagal menyimpan jenis supplier');
        })
        .always(function(){
            button.prop('disabled',false).text('Save');
        });
});

/* ADD SUPPLIER */
$('#addSupplier').click(function(){
    $('#tblSupplier tbody .supplier-empty-new').remove();

    $('#tblSupplier tbody').prepend(`
        <tr class="new-row">
            <td>-</td>
            <td><div class="supplier-editable jenis-supplier-name" contenteditable="true" title=""></div></td>
            <td>
                <select class="form-control supplier-jenis">
                    ${jenisOptions('')}
                </select>
            </td>
            <td><span class="rekening-empty">Simpan supplier dulu</span></td>
            <td><button type="button" class="btn btn-success btn-sm saveSupplier">Save</button></td>
            <td class="supplier-editable supplier-alamat" contenteditable="true"></td>
        </tr>
    `);


});

/* SAVE SUPPLIER */
$(document).on('click','.saveSupplier',function(){
    let button = $(this);
    let tr = button.closest('tr');
    let id = tr.data('id');
    let name = tr.find('td:eq(1)').text().trim();
    let alamat = tr.find('td:eq(5)').text().trim();
    let jenis = tr.find('.supplier-jenis').val();
    let isNew = tr.hasClass('new-row');

    if(!name) return alert('Name wajib');

    button.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    let url = isNew ? '/supplier/store' : '/supplier/update/'+id;
    let data = {
        _token:csrf,
        name:name,
        alamat:alamat,
        jenis_supplier_id:jenis
    };

    $.post(url,data)
        .done(function(response){
            if(!response.success) return alert(response.message || 'Gagal menyimpan supplier');
            refreshSuppliers($('#supplierSearchInput').val().trim());
        })
        .fail(function(xhr){
            let msg = xhr.responseJSON?.message || 'Gagal menyimpan supplier';
            if(xhr.responseJSON?.errors){
                msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            }
            alert(msg);
        })
        .always(function(){
            button.prop('disabled',false).html('Save');
        });
});

/* SEARCH SUPPLIER - AJAX */
$('#supplierSearchForm').on('submit',function(e){
    e.preventDefault();
    refreshSuppliers($('#supplierSearchInput').val().trim());
});

$('#supplierSearchInput').on('input',function(){
    let q = $(this).val().trim();
    clearTimeout(supplierSearchTimer);
    supplierSearchTimer = setTimeout(function(){
        refreshSuppliers(q);
    },300);
});

$('#btnClearSupplierSearch').click(function(){
    $('#supplierSearchInput').val('');
    refreshSuppliers('');
});

/* OPEN REKENING MODAL */
$(document).on('click','.rekening-cell',function(){
    let $cell = $(this);
    let supplierId = $cell.data('id');

    $('#linkSupplierId').val(supplierId);
    selectedVendorId = $cell.data('vendor-id') || null;
    $('#vendorSearchInput').val('');
    $('#vendorSuggestions').hide().html('');
    $('#selectedVendor').hide();
    $('#btnSaveLinkVendor').prop('disabled',true);
    $('#btnUnlinkVendor').prop('disabled',!selectedVendorId);

    if(selectedVendorId){
        let $row = $cell.closest('tr');
        let rekening = $row.find('.rekening-cell strong').text().trim();
        let small = $row.find('.rekening-cell small').text().trim();
        $('#vendorSearchInput').val(rekening || '');
        $('#selectedVendorName').text('Vendor terhubung');
        $('#selectedVendorRekening').text(rekening || '-');
        $('#selectedVendorNamaRekening').text(small.replace(/^.*?— A\/N /,'') || '-');
        $('#selectedVendorBank').text(small.split(' — ')[0] || '-');
        $('#selectedVendorUniq').text('-');
        $('#selectedVendor').show();
    }

    $('#modalLinkVendor').modal('show');
});

/* SEARCH VENDOR */
$('#vendorSearchInput').on('input',function(){
    let q=$(this).val().trim();
    selectedVendorId=null;
    $('#btnSaveLinkVendor').prop('disabled',true);
    $('#btnUnlinkVendor').prop('disabled',true);
    $('#selectedVendor').hide();
    clearTimeout(vendorSearchTimer);

    if(q.length<2){
        $('#vendorSuggestions').hide().html('');
        return;
    }

    vendorSearchTimer=setTimeout(function(){
        $.ajax({
            url:"{{ url('/supplier/vendor-search') }}",
            type:'GET',
            data:{q:q},
            success:function(vendors){
                let html='';

                if(!vendors.length){
                    html='<div class="vendor-suggestion"><span style="color:#94a3b8">Vendor tidak ditemukan</span></div>';
                }else{
                    vendors.forEach(function(v){
                        html+=`
                            <div class="vendor-suggestion select-vendor"
                                 data-id="${v.id}"
                                 data-name="${escapeHtml(v.nama_vendor||'')}"
                                 data-rekening="${escapeHtml(v.nomor_rekening||'')}"
                                 data-nama-rekening="${escapeHtml(v.nama_rekening||'')}"
                                 data-bank="${escapeHtml(v.bank||'')}"
                                 data-uniq="${escapeHtml(v.uniq||'')}">
                                <div class="vendor-suggestion-name">${escapeHtml(v.nama_vendor||'-')}</div>
                                <div class="vendor-suggestion-detail">
                                    Rek: ${escapeHtml(v.nomor_rekening||'-')}
                                    &nbsp; | &nbsp; Bank: ${escapeHtml(v.bank||'-')}
                                    &nbsp; | &nbsp; A/N: ${escapeHtml(v.nama_rekening||'-')}
                                </div>
                            </div>`;
                    });
                }

                $('#vendorSuggestions').html(html).show();
            },
            error:function(xhr){
                $('#vendorSuggestions').html('<div class="vendor-suggestion">Gagal mengambil data vendor</div>').show();
            }
        });
    },250);
});

/* SELECT VENDOR */
$(document).on('click','.select-vendor',function(){
    selectedVendorId=$(this).data('id');

    $('#vendorSearchInput').val($(this).data('rekening') || $(this).data('name'));
    $('#vendorSuggestions').hide().html('');

    $('#selectedVendorName').text($(this).data('name') || '-');
    $('#selectedVendorRekening').text($(this).data('rekening') || '-');
    $('#selectedVendorNamaRekening').text($(this).data('nama-rekening') || '-');
    $('#selectedVendorBank').text($(this).data('bank') || '-');
    $('#selectedVendorUniq').text($(this).data('uniq') || '-');
    $('#selectedVendor').show();
    $('#btnSaveLinkVendor').prop('disabled',false);
    $('#btnUnlinkVendor').prop('disabled',false);
});

/* SAVE LINK - AJAX TANPA RELOAD */
$('#btnSaveLinkVendor').click(function(){
    let supplierId=$('#linkSupplierId').val();
    if(!selectedVendorId) return alert('Pilih vendor terlebih dahulu');

    let button=$(this);
    button.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

    $.ajax({
        url:"{{ url('/supplier') }}/"+supplierId+"/link-vendor",
        type:'POST',
        data:{_token:csrf,vendor_id:selectedVendorId},
        success:function(response){
            if(response.success){
                let rowData = response.data;
                let $cell = $('#tblSupplier tbody tr[data-id="'+supplierId+'"] .rekening-cell');
                $cell.attr('data-vendor-id', rowData.vendor_id || '');
                $cell.html(rekeningHtml(rowData.vendor));
                $('#modalLinkVendor').modal('hide');
            }else{
                alert(response.message || 'Gagal menghubungkan vendor');
            }
        },
        error:function(xhr){
            alert(xhr.responseJSON?.message || 'Gagal menghubungkan vendor');
        },
        complete:function(){
            button.prop('disabled',false).html('<i class="fa fa-save"></i> Simpan');
        }
    });
});

/* UNLINK - AJAX TANPA RELOAD */
$('#btnUnlinkVendor').click(function(){
    let supplierId=$('#linkSupplierId').val();
    if(!supplierId) return;
    if(!confirm('Lepaskan rekening vendor dari supplier ini?')) return;

    let button=$(this);
    button.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Melepas...');

    $.ajax({
        url:"{{ url('/supplier') }}/"+supplierId+"/unlink-vendor",
        type:'POST',
        data:{_token:csrf},
        success:function(response){
            if(response.success){
                let $cell = $('#tblSupplier tbody tr[data-id="'+supplierId+'"] .rekening-cell');
                $cell.attr('data-vendor-id','');
                $cell.html('<span class="rekening-empty"><i class="fa fa-link"></i> Tautkan Rekening</span>');
                $('#modalLinkVendor').modal('hide');
            }else{
                alert(response.message || 'Gagal melepas vendor');
            }
        },
        error:function(xhr){
            alert(xhr.responseJSON?.message || 'Gagal melepas vendor');
        },
        complete:function(){
            button.prop('disabled',false).html('<i class="fa fa-unlink"></i> Lepas Link');
        }
    });
});

/* CLOSE SUGGESTION */
$(document).on('click',function(e){
    if(!$(e.target).closest('.vendor-search-wrapper').length){
        $('#vendorSuggestions').hide();
    }
});

});
</script>

@endsection