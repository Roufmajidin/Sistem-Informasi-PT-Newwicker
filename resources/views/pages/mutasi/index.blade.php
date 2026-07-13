@extends('master.master')

@section('title', 'Mutasi Barang')

@section('content')

<div class="container-fluid py-4">
  <div class="card-header bg-white">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-boxes text-primary me-2"></i>
                Monitoring SPK
            </h4>
            <small class="text-muted">
                Cari berdasarkan PO, Buyer, Supplier, Item, atau No SPK
            </small>
        </div>

        <div style="min-width:380px; max-width:500px; width:100%;">

            <div class="input-group">

                <span class="input-group-text bg-white">
                    <i class="fas fa-search text-secondary"></i>
                </span>

                <input
                    type="text"
                    id="searchSpk"
                    class="form-control border-start-0"
                    placeholder="Cari PO, Buyer, Supplier, Item...">

            </div>

        </div>

    </div>

</div>

      <div class="card-body">

    {{-- Search --}}


    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle">

            <thead class="table-light">
                <tr>
                    <th width="50">No</th>
                    <th>No SPK</th>
                    <th>PO</th>
                    <th>Buyer</th>
                    <th>Jenis</th>
                    <th>Sub</th>
                </tr>
            </thead>

            <tbody id="tbodySpk">

            @forelse($a as $index => $spk)

                @php

                    $searchItem = '';

                    foreach(($spk->data['items'] ?? []) as $item){

                        $searchItem .= ' '
                            . ($item['nama'] ?? '')
                            . ' '
                            . ($item['kode'] ?? '');

                    }

                @endphp

                <tr
                    class="pilih-spk"
                    data-id="{{ $spk->id }}"
                    style="cursor:pointer"
                    data-search="{{ strtolower(
                        ($spk->po->order_no ?? '') . ' ' .
                        ($spk->po->company_name ?? '') . ' ' .
                        ($spk->data['sup'] ?? '') . ' ' .
                        ($spk->data['no_spk'] ?? '') . ' ' .
                        ($spk->data['kategori'] ?? '') . ' ' .
                        $searchItem
                    ) }}">

                    <td>{{ $index + 1 }}</td>

                    <td>{{ $spk->data['no_spk'] ?? '-' }}</td>

                    <td>{{ $spk->po->order_no ?? '-' }}</td>

                    <td>{{ $spk->po->company_name ?? '-' }}</td>

                    <td>{{ $spk->data['kategori'] ?? '-' }}</td>

                    <td>{{ $spk->data['sup'] ?? '-' }}</td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center">
                        Tidak ada data SPK
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
<div class="modal fade" id="modalSpk" tabindex="-1">
    <div class="modal-dialog modal-custom">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="judulSpk">Detail SPK</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

       <div class="modal-body">

        <div class="mb-3">
            <label>Item</label>

            <select id="itemSelect" class="form-select">
                <option>Pilih Item</option>
            </select>
        </div>

        {{-- Detail item --}}
        <div id="itemInfo"></div>

        <hr>

        {{-- Timeline --}}
        <div id="timelineTable"></div>

    </div>
    </div>
</div>
{{-- scripts --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let items = [];
let currentSpkId = null;
let supplierName = '';
let currentSupId = null;
let kategoriSpk = '';
$(document).on('click','.pilih-spk',function(){
    resetModal();

currentSpkId = $(this).data('id');
    $.ajax({

        url: "/mutasi/"+currentSpkId,

        type:"GET",

        success:function(res){
            supplierName = res.supplier;
            currentSupId = res.sup_id;
            kategoriSpk = res.kategori;

            console.log(res);

            $('#judulSpk').text(res.no_spk);

            items = res.items;

            let html = '<option value="">Pilih Item</option>';

            $.each(res.items, function(i, item){

                html += `
                    <option value="${item.detail_po_id}" data-index="${i}">
                        ${item.kode} - ${item.nama} (${item.qty} ${item.satuan})
                    </option>
                `;

            });
            $('#modalSpk').modal('show');

            $('#itemSelect').html(html);

        }

    });

});

$('#itemSelect').on('change', function () {

    let detailPoId = $(this).val();

    let index = $(this).find(':selected').data('index');

    let item = items[index];

    if (!item) return;

    // Tampilkan detail
        $('#itemInfo').html(`
        <table class="table table-bordered">
            <tr>
                <th>Detail PO ID</th>
                <td>${detailPoId}</td>
            </tr>
            <tr>
                <th>Kode</th>
                <td>${item.kode}</td>
            </tr>
            <tr>
                <th>Nama</th>
                <td>${item.nama}</td>
            </tr>
            <tr>
                <th>Qty</th>
                <td>${item.qty}</td>
            </tr>
             <tr>
                <th>Supplier Name</th>
              <td>
    <input type="hidden"
           class="sup_id"
           value="${currentSupId}">
    ${supplierName}
</td>
            </tr>
         <tr>
    <th>Kategori SPK</th>
    <td>
        <input type="hidden"
               class="kategori"
               value="${kategoriSpk}">
        ${kategoriSpk}
    </td>
</tr>
        </table>
    `);

    // AJAX kedua
    $.ajax({
        url: '/mutasi/timeline/detail',
        type: 'GET',
        data: {
            spk_id: currentSpkId,
            detail_po_id: detailPoId
        },
        success:function(res){

            let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="160">Tanggal</th>
                            <th width="100">Jam</th>
                            <th width="150">Type</th>
                            <th width="100">Qty</th>
                            <th width="100">Remark</th>
                            <th width="70">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTimeline">
            `;

            $.each(res.timeline,function(i,row){

                let datetime = row.date ? row.date.split(' ') : ['',''];

                let tanggal = datetime[0];
                let jam = datetime[1] ?? '';

                html += `
                    <tr data-id="${row.id}">

                        <td>${i+1}</td>

                        <td>
                            <input type="date"
                                class="form-control form-control-sm tanggal"
                                value="${tanggal}">
                        </td>

                        <td>
                            <input type="time"
                                class="form-control form-control-sm jam"
                                value="${jam}">
                        </td>

                        <td>
                            <select class="form-select form-select-sm type">

                                <option value="in" ${row.type=='in'?'selected':''}>Masuk</option>


                                <option value="kirim_rangka" ${row.type=='kirim_rangka'?'selected':''}>Kirim Rangka</option>


                                <option value="service_masuk" ${row.type=='service_masuk'?'selected':''}>Service</option>
                                <option value="service_keluar" ${row.type=='service_keluar'?'selected':''}>Service Keluar</option>


                            </select>
                        </td>



                        <td>
                            <input type="number"
                                class="form-control form-control-sm qty"
                                value="${row.qty}">
                        </td>
                            <td>
                            <input type="text"
                                class="form-control form-control-sm remark"
                                value="${row.remark}">
                        </td>

                        <td class="text-center">

                            <button
                                class="btn btn-danger btn-sm hapus-row">
                                <i class="fas fa-trash"></i>
                            </button>

                        </td>

                    </tr>
                `;

            });

            html += `
                    </tbody>
                </table>

                <div class="d-flex justify-content-between">

                    <button class="btn btn-success btn-sm" id="btnTambah">

                        <i class="fas fa-plus"></i>
                        Tambah Baris

                    </button>

                    <button class="btn btn-primary btn-sm" id="btnSave">

                        <i class="fas fa-save"></i>
                        Simpan

                    </button>

                </div>
            `;

        $('#timelineTable').html(html);
        },
        error: function(xhr){
            console.log(xhr.responseText);
        }
    });

});
// add rw
$(document).on('click','#btnTambah',function(){

    let no = $('#tbodyTimeline tr').length + 1;

    $('#tbodyTimeline').append(`
        <tr data-id="">

            <td>${no}</td>

            <td>
                <input type="date" class="form-control form-control-sm tanggal">
            </td>

            <td>
                <input type="time" class="form-control form-control-sm jam">
            </td>

            <td>
                <select class="form-select form-select-sm type">

                    <option value="in">Masuk</option>
                    <option value="kirim_rangka">Kirim Rangka</option>
                    <option value="service_keluar">Service Keluar</option>
                    <option value="service_masuk">Service Masuk</option>

                </select>
            </td>



            <td>
                <input type="number" class="form-control form-control-sm qty">
            </td>
              <td>
                <input type="text" class="form-control form-control-sm remark">
            </td>

            <td class="text-center">

                <button class="btn btn-danger btn-sm hapus-row">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>
    `);

});
// hapus row
$(document).on('click','.hapus-row',function(){

    $(this).closest('tr').remove();

});
// save data
$(document).on('click','#btnSave',function(){

    let rows = [];

    $('#tbodyTimeline tr').each(function(){

        rows.push({

            id: $(this).data('id'),

            spk_id: currentSpkId,

            detail_po_id: $('#itemSelect').val(),

            sup_id: currentSupId,

            qty: $(this).find('.qty').val(),

            type: $(this).find('.type').val(),

            remark: $(this).find('.remark').val(),

            date: $(this).find('.tanggal').val(),

            time: $(this).find('.jam').val()

        });

    });

    $.ajax({

        url : "{{ route('mutasi.timeline.save') }}",

        type : "POST",

        data : {
            _token : "{{ csrf_token() }}",
            rows : rows
        },

        success:function(res){

           const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            customClass: {
                popup: 'colored-toast'
            },
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
           Toast.fire({
        icon: 'success',
        title: 'ikan hiu ikan hiu, love you'
    });

    $('#modalSpk').modal('hide');

        },

        error:function(xhr){

            console.log(xhr.responseText);

        }

    });

});
function resetModal() {

    items = [];
    currentSpkId = null;
    currentSupId = null;
    supplierName = '';

    $('#judulSpk').text('Detail SPK');

    $('#itemSelect').html('<option value="">Pilih Item</option>');

    $('#itemInfo').empty();

    $('#timelineTable').empty();

    // Reset select ke option pertama
    $('#itemSelect').prop('selectedIndex', 0);

}

$('#modalSpk').on('hidden.bs.modal', function () {

    resetModal();

});
$('#searchSpk').on('keyup', function () {

    let keyword = $(this).val().toLowerCase().trim();

    $('#tbodySpk tr.pilih-spk').each(function () {

        let search = ($(this).data('search') || '').toLowerCase();

        $(this).toggle(search.includes(keyword));

    });

});
</script>

<style>
    .modal-custom {
    max-width: 95%;
}

.modal-custom .modal-content {
    min-height: 85vh;
}
.colored-toast {
    background: #198754 !important;
    color: #fff !important;
}
</style>
@endsection
