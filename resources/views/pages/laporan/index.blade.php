@extends('master.master')
@section('title', 'Laporan Stok')
@section('content')
@if(session('success'))
<script>
    toastr.success("{{ session('success') }}");
</script>
@endif

@if(session('error'))
<script>
    toastr.error("{{ session('error') }}");
</script>
@endif
    <div class="padding">
        <div class="box">
            <div class="box-header d-flex justify-content-between">
                <h2>Laporan Stok</h2>
            </div>
            <div id="spkInfo"></div>
            <div class="box-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Jenis Barang</label>
                        <select id="filterJenis" class="form-control">
                            <option value="">Semua Jenis</option>
                            <option value="bahan baku">Bahan Baku</option>
                            <option value="bahan penolong">Bahan Penolong</option>
                            <option value="bahan penolong alat">Bahan Penolong Alat</option>
                        </select>

                    </div>
                    <div class="col-md-2">
    <label>-</label>
    <input
        type="text"
        id="searchBarang"
        class="form-control"
        placeholder="Cari nama barang...">
</div>
                    <div class="col-md-5 text-right">
                        <br>
                        <button type="button" class="btn btn-primary" id="addRow">
                            <i class="fa fa-plus"></i>
                            Tambah Baris
                        </button>

                    </div>
                </div>
                <div id="wrapperStok" style="
                    overflow-x:auto;
                    overflow-y:hidden;
                    width:100%;
                ">
                                    <div id="canvasStok"
                                        style="
                        width:2200px;
                        display:flex;
                        align-items:flex-start;
                    ">
                                        <div id="masterPanel"
                                            style="
                            min-width:1300px;
                            padding-right:20px;
                        ">
                            <!-- tbl 1 -->

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="50">No</th>
                                                <th>Kode Barang</th>
                                                <th>Nama Barang</th>
                                                <th style="min-width:180px;">Jenis</th>
                                                <th width="50">Satuan</th>
                                                <th>Harga</th>
                                                <th>Saldo</th>
                                                <th>Stok in </th>
                                                <th>Stok out </th>
                                                <!-- <th>IN</th>
                                                <th>OUT</th> -->
                                                <th>Tanggal</th>
                                                <th width="80">Aksi</th>
                                                <th width="100">Detail</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            @forelse($stoks ?? [] as $key => $stok)
                                            <tr data-id="{{ $stok->id }}">
                                                <td>{{ $key + 1 }}</td>

                                                <td>
                                                    <input type="hidden" class="id" value="{{ $stok->id }}">

                                                    <input
                                                        type="text"
                                                        class="form-control kode_barang"
                                                        value="{{ $stok->kode_barang }}">
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control nama_barang"
                                                        value="{{ $stok->nama_barang }}">
                                                </td>

                                                <td>
                                                    <select class="form-control jenis">
                                                        <option value="bahan baku"
                                                            {{ $stok->jenis == 'bahan baku' ? 'selected' : '' }}>
                                                            Bahan Baku
                                                        </option>

                                                        <option value="bahan penolong"
                                                            {{ $stok->jenis == 'bahan penolong' ? 'selected' : '' }}>
                                                            Bahan Penolong
                                                        </option>

                                                        <option value="bahan penolong alat"
                                                            {{ $stok->jenis == 'bahan penolong alat' ? 'selected' : '' }}>
                                                            Bahan Penolong Alat
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control satuan"
                                                        value="{{ $stok->satuan }}">
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control harga"
                                                        value="{{ !empty($stok->harga) ? number_format($stok->harga,0,',','.') : '' }}">
                                                </td>

                                                <td>
                                                    <input
                                                        type="number"
                                                        step="0.001"
                                                        class="form-control stok_awal"
                                                        value="{{ $stok->stok_awal }}">

                                                    <small class="text-muted">
                                                        Total :
                                                        {{ number_format($stok->stok_akhir,3,'.',',') }}
                                                    </small>
                                                </td>

                                                <td>
                                                    {{ $stok->total_in ?? 0 }}
                                                </td>

                                                <td>
                                                    {{ $stok->total_out ?? 0 }}
                                                </td>

                                                <td>
                                                    <input
                                                        type="date"
                                                        class="form-control tanggal"
                                                        value="{{ date('Y-m-d') }}">
                                                </td>

                                                <td width="120">

                                                    <button
                                                        type="button"
                                                        class="btn btn-success btn-sm btn-save">

                                                        <i class="fa fa-save"></i>

                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="btn btn-danger btn-sm remove-row">

                                                        <i class="fa fa-trash"></i>

                                                    </button>

                                                </td>

                                                <td>

                                                    <a
                                                        href="{{ route('laporan.detail',$stok->id) }}"
                                                        class="btn btn-info btn-sm">

                                                        Detail

                                                    </a>

                                                </td>

                                            </tr>
                                            @empty
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                        </div>
                        <!-- tbl2 -->
                        <!-- TABEL DETAIL -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
$(function () {

    // ============================
    // TAMBAH BARIS
    // ============================
    $('#addRow').click(function () {

        let rowCount = $('#tableBody tr').length + 1;

        let row = `
<tr data-id="">

    <td>${rowCount}</td>

    <td>
        <input type="hidden" class="id" value="">

        <input
            type="text"
            class="form-control kode_barang">
    </td>

    <td>
        <input
            type="text"
            class="form-control nama_barang">
    </td>

    <td>
        <select class="form-control jenis">
            <option value="bahan baku">
                Bahan Baku
            </option>

            <option value="bahan penolong">
                Bahan Penolong
            </option>

            <option value="bahan penolong alat">
                Bahan Penolong Alat
            </option>
        </select>
    </td>

    <td>
        <input
            type="text"
            class="form-control satuan">
    </td>

    <td>
        <input
            type="text"
            class="form-control harga">
    </td>

    <td>
        <input
            type="number"
            step="0.001"
            class="form-control stok_awal"
            value="0">
    </td>

    <td>0</td>

    <td>0</td>

    <td>
        <input
            type="date"
            class="form-control tanggal"
            value="{{ date('Y-m-d') }}">
    </td>

    <td>

        <button
            type="button"
            class="btn btn-success btn-sm btn-save">

            <i class="fa fa-save"></i>

        </button>

        <button
            type="button"
            class="btn btn-danger btn-sm remove-row">

            <i class="fa fa-trash"></i>

        </button>

    </td>

    <td></td>

</tr>
`;

        $('#tableBody').append(row);

    });


    // ============================
    // FORMAT HARGA
    // ============================
    $(document).on('keyup','.harga',function(){

        let value=$(this).val().replace(/\D/g,'');

        $(this).val(

            new Intl.NumberFormat('id-ID').format(value)

        );

    });


    // ============================
    // SIMPAN
    // ============================
    $(document).on('click','.btn-save',function(){

        let btn=$(this);

        let row=btn.closest('tr');

        $.ajax({

            url:"{{ route('laporan.update') }}",

            type:"POST",

            data:{

                _token:"{{ csrf_token() }}",

                id:row.find('.id').val(),

                kode_barang:row.find('.kode_barang').val(),

                nama_barang:row.find('.nama_barang').val(),

                jenis:row.find('.jenis').val(),

                satuan:row.find('.satuan').val(),

                harga:row.find('.harga').val(),

                stok_awal:row.find('.stok_awal').val(),

            },

            beforeSend:function(){

                btn
                    .prop('disabled',true)
                    .html('<i class="fa fa-spinner fa-spin"></i>');

            },

            success:function(res){

                toastr.success(res.message);

                if(row.find('.id').val()===''){

                    row.find('.id').val(res.id);

                    row.attr('data-id',res.id);

                }

            },

            error:function(xhr){

                toastr.error(

                    xhr.responseJSON?.message ??

                    'Gagal menyimpan'

                );

            },

            complete:function(){

                btn
                    .prop('disabled',false)
                    .html('<i class="fa fa-save"></i>');

            }

        });

    });


    // ============================
    // HAPUS BARIS BARU
    // ============================
    $(document).on('click','.remove-row',function(){

        let row=$(this).closest('tr');

        if(row.find('.id').val()===''){

            row.remove();

            return;

        }

        toastr.warning(
            'Data sudah tersimpan. Delete database akan kita buat berikutnya.'
        );

    });

});
</script>
<script>

// ===========================
// FILTER
// ===========================

function filterData(){

    let keyword = $('#searchBarang')
        .val()
        .toLowerCase();

    let jenis = $('#filterJenis')
        .val()
        .toLowerCase();

    let no = 1;

    $('#tableBody tr').each(function(){

        let row = $(this);

        let nama = row.find('.nama_barang')
            .val()
            ?.toLowerCase() || '';

        let rowJenis = row.find('.jenis')
            .val()
            ?.toLowerCase() || '';

        let matchNama =
            keyword=='' ||
            nama.includes(keyword);

        let matchJenis =
            jenis=='' ||
            rowJenis==jenis;

        if(matchNama && matchJenis){

            row.show();

            row.find('td:first')
                .text(no++);

        }else{

            row.hide();

        }

    });

}

// ===========================
// SEARCH
// ===========================

$('#searchBarang').on(
    'keyup',
    filterData
);

// ===========================
// FILTER JENIS
// ===========================

$('#filterJenis').on(
    'change',
    filterData
);


// ===========================
// FORMAT HARGA
// ===========================

$(document).on(
    'keyup',
    '.harga',
    function(){

        let value=$(this)
            .val()
            .replace(/\D/g,'');

        $(this).val(

            new Intl.NumberFormat(
                'id-ID'
            ).format(value)

        );

    }
);


// ===========================
// AUTO SEARCH BARANG
// ===========================

$(document).on(
    'keyup',
    '.nama_barang',
    function(){

        let input=$(this);

        let q=input.val();

        if(q.length<2){

            return;

        }

        $.get(

            '/stok/search',

            {q:q},

            function(res){

                if(!res){

                    return;

                }

                let row=input.closest('tr');

                if(
                    row.find('.kode_barang').val()===''
                ){

                    row.find('.kode_barang')
                        .val(res.kode_barang);
                }

                if(
                    row.find('.satuan').val()===''
                ){

                    row.find('.satuan')
                        .val(res.satuan);
                }

                if(
                    row.find('.harga').val()===''
                ){

                    row.find('.harga')
                        .val(

                            new Intl.NumberFormat(
                                'id-ID'
                            ).format(res.harga)

                        );

                }

            }

        );

    }

);

</script>
    @include('pages.laporan.style')
@endsection
