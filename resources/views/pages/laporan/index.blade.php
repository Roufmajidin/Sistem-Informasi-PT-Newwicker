@extends('master.master')
@section('title', 'Laporan Stok')
@section('content')
    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif

    @if (session('error'))
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
                    <div class="col-md-3">
                        <label>Jenis Barang</label>
                        <select id="filterJenis" class="form-control">
                            <option value="">Semua Jenis</option>
                            <option value="bahan baku">Bahan Baku</option>
                            <option value="bahan penolong">Bahan Penolong</option>
                            <option value="bahan penolong alat">Bahan Penolong Alat</option>
                            <option value="bahan finishing">Bahan Finishing</option>

                        </select>

                    </div>
                    <div class="col-md-2">
                        <label>-</label>
                        <input type="text" id="searchBarang" class="form-control" placeholder="Cari nama barang...">
                    </div>
                    <div class="col-md-2 mt-4">

                        <button type="button" class="btn btn-primary" id="addRow">
                            <i class="fa fa-info"></i>
                            <a href="/laporan/warehouse-history"> history</a>
                        </button>
                    </div>


                    <div class="col-md-5 text-right">
                        <br>

                        <button type="button" class="btn btn-primary" id="addRowss">
                            <i class="fa fa-plus"></i>
                            Tambah Baris
                        </button>

                    </div>
                </div>
                <div id="wrapperStok"
                    style="
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

                            <div class="table-stok-wrapper">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="50">No</th>
                                            <th width="110">Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th width="170">Jenis</th>
                                            <th width="60">Satuan</th>
                                            <th width="100">Harga</th>
                                            <th width="90">Saldo</th>
                                            <th width="80">Stok In</th>
                                            <th width="80">Stok Out</th>
                                            <th width="130">Tanggal</th>
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

                                                    <input type="text" class="form-control kode_barang"
                                                        value="{{ $stok->kode_barang }}">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control nama_barang"
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
                                                        <option value="bahan finishing"
                                                            {{ $stok->jenis == 'bahan finishing' ? 'selected' : '' }}>
                                                            Bahan Finishing
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control satuan"
                                                        value="{{ $stok->satuan }}">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control harga"
                                                        value="{{ !empty($stok->harga) ? number_format($stok->harga, 0, ',', '.') : '' }}">
                                                </td>

                                                <td>
                                                    <input type="number" step="0.001" class="form-control stok_awal"
                                                        value="{{ ($stok->stok_awal ?? 0) + ($stok->total_in ?? 0) - ($stok->total_out ?? 0) }}">

                                                    {{-- <small class="text-muted">
                                                        Total :
                                                        {{ number_format($stok->stok_akhir,3,'.',',') }}
                                                    </small> --}}
                                                </td>

                                                <td>
                                                    {{ number_format($stok->total_in, 2, '.', ',') }}
                                                </td>

                                                <td>
                                                    {{ number_format($stok->total_out, 2, '.', ',') }}
                                                </td>

                                                <td>
                                                    <input type="date" class="form-control tanggal"
                                                        value="{{ date('Y-m-d') }}">
                                                </td>

                                                <td width="120">

                                                    <button type="button" class="btn btn-success btn-sm btn-save">

                                                        <i class="fa fa-save"></i>

                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-sm remove-row">

                                                        <i class="fa fa-trash"></i>

                                                    </button>

                                                </td>

                                                <td>

                                                    <a href="{{ route('laporan.detail', $stok->id) }}"
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
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(function() {

            // ============================
            // TAMBAH BARIS
            // ============================
            $('#addRowss').click(function() {

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
              <option value="bahan finishing">
                Bahan Finishing
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
    value="{{ $stok->saldo }}">
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
            $(document).on('keyup', '.harga', function() {

                let value = $(this).val().replace(/\D/g, '');

                $(this).val(

                    new Intl.NumberFormat('id-ID').format(value)

                );

            });


            // ============================
            // SIMPAN
            // ============================
            $(document).on('click', '.btn-save', function() {

                let btn = $(this);

                let row = btn.closest('tr');

                $.ajax({

                    url: "{{ route('laporan.update') }}",

                    type: "POST",

                    data: {

                        _token: "{{ csrf_token() }}",

                        id: row.find('.id').val(),

                        kode_barang: row.find('.kode_barang').val(),

                        nama_barang: row.find('.nama_barang').val(),

                        jenis: row.find('.jenis').val(),

                        satuan: row.find('.satuan').val(),

                        harga: row.find('.harga').val(),

                        stok_awal: row.find('.stok_awal').val(),

                    },

                    beforeSend: function() {

                        btn
                            .prop('disabled', true)
                            .html('<i class="fa fa-spinner fa-spin"></i>');

                    },

                    success: function(res) {

                        toastr.success(res.message);

                        if (row.find('.id').val() === '') {

                            row.find('.id').val(res.id);

                            row.attr('data-id', res.id);

                        }

                    },

                    error: function(xhr) {

                        toastr.error(

                            xhr.responseJSON?.message ??

                            'Gagal menyimpan'

                        );

                    },

                    complete: function() {

                        btn
                            .prop('disabled', false)
                            .html('<i class="fa fa-save"></i>');

                    }

                });

            });


            // ============================
            // HAPUS BARIS BARU
            // ============================
            $(document).on('click', '.remove-row', function() {

                let row = $(this).closest('tr');

                if (row.find('.id').val() === '') {

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

        function filterData() {

            let keyword = $('#searchBarang')
                .val()
                .toLowerCase();

            let jenis = $('#filterJenis')
                .val()
                .toLowerCase();

            let no = 1;

            $('#tableBody tr').each(function() {

                let row = $(this);

                let nama = row.find('.nama_barang')
                    .val()
                    ?.toLowerCase() || '';

                let rowJenis = row.find('.jenis')
                    .val()
                    ?.toLowerCase() || '';

                let matchNama =
                    keyword == '' ||
                    nama.includes(keyword);

                let matchJenis =
                    jenis == '' ||
                    rowJenis == jenis;

                if (matchNama && matchJenis) {

                    row.show();

                    row.find('td:first')
                        .text(no++);

                } else {

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
            function() {

                let value = $(this)
                    .val()
                    .replace(/\D/g, '');

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
            function() {

                let input = $(this);

                let q = input.val();

                if (q.length < 2) {

                    return;

                }

                $.get(

                    '/stok/search',

                    {
                        q: q
                    },

                    function(res) {

                        if (!res) {

                            return;

                        }

                        let row = input.closest('tr');

                        if (
                            row.find('.kode_barang').val() === ''
                        ) {

                            row.find('.kode_barang')
                                .val(res.kode_barang);
                        }

                        if (
                            row.find('.satuan').val() === ''
                        ) {

                            row.find('.satuan')
                                .val(res.satuan);
                        }

                        if (
                            row.find('.harga').val() === ''
                        ) {

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

<style>
/* =========================================================
   LAPORAN STOK - TABLE UI mengikuti index SPEK
   UI ONLY. Tidak mengubah ID, class, endpoint, AJAX, atau JS.
   ========================================================= */

.table-stok-wrapper{
    width:100%;
    overflow:auto;
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:8px;
    box-shadow:0 1px 2px rgba(16,24,40,.025);
}

.table-stok-wrapper table{
    width:100%;
    min-width:1180px;
    margin:0;
    border-collapse:collapse;
    border-spacing:0;
    background:#fff;
}

.table-stok-wrapper table thead th{
    height:38px;
    padding:0 10px;
    background:#fcfcfd;
    border:0;
    border-bottom:1px solid #e9edf2;
    color:#475467;
    text-align:left;
    vertical-align:middle;
    font-size:10px;
    font-weight:700;
    white-space:nowrap;
}

.table-stok-wrapper table tbody td{
    height:48px;
    padding:7px 10px;
    border:0;
    border-bottom:1px solid #f0f2f5;
    color:#475467;
    background:#fff;
    font-size:11.5px;
    vertical-align:middle;
    white-space:nowrap;
}

.table-stok-wrapper table tbody tr{
    transition:.12s ease;
}

.table-stok-wrapper table tbody tr:hover td{
    background:#fafcff;
}

.table-stok-wrapper table tbody tr:last-child td{
    border-bottom:0;
}

/* Nomor */
.table-stok-wrapper table th:first-child,
.table-stok-wrapper table td:first-child{
    text-align:center;
    color:#667085;
    font-weight:600;
}

/* Input pada tabel tetap editable, tetapi visual dibuat seperti
   field ringan pada index spek */
.table-stok-wrapper .form-control{
    width:100%;
    min-width:0;
    height:32px;
    min-height:32px;
    padding:0 9px;
    border:1px solid #dfe3e8;
    border-radius:6px;
    background:#fff;
    color:#344054;
    font-size:10.5px;
    box-shadow:none;
    outline:none;
    transition:.15s ease;
}

.table-stok-wrapper .form-control:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 3px rgba(37,99,235,.07);
}

.table-stok-wrapper select.form-control{
    cursor:pointer;
    padding-right:25px;
}

.table-stok-wrapper input[type="date"].form-control{
    min-width:130px;
}

.table-stok-wrapper .btn{
    min-width:32px;
    height:31px;
    min-height:31px;
    padding:0 8px;
    border-radius:6px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    font-size:11px;
    line-height:1;
    border-width:1px;
}

.table-stok-wrapper .btn-info{
    color:#2563eb;
    background:#eff6ff;
    border-color:#bfdbfe;
}

.table-stok-wrapper .btn-info:hover{
    color:#1d4ed8;
    background:#dbeafe;
}

.table-stok-wrapper .btn-success{
    color:#15803d;
    background:#f0fdf4;
    border-color:#bbf7d0;
}

.table-stok-wrapper .btn-success:hover{
    color:#166534;
    background:#dcfce7;
}

.table-stok-wrapper .btn-danger{
    color:#dc2626;
    background:#fff;
    border-color:#fecaca;
}

.table-stok-wrapper .btn-danger:hover{
    color:#dc2626;
    background:#fff5f5;
}

/* Kolom angka lebih rapi */
.table-stok-wrapper td:nth-child(7),
.table-stok-wrapper td:nth-child(8),
.table-stok-wrapper td:nth-child(9){
    text-align:right;
    font-variant-numeric:tabular-nums;
}

/* Header kolom angka */
.table-stok-wrapper th:nth-child(7),
.table-stok-wrapper th:nth-child(8),
.table-stok-wrapper th:nth-child(9){
    text-align:right;
}

/* Harga */
.table-stok-wrapper .harga{
    text-align:right;
    font-variant-numeric:tabular-nums;
}

/* Tampilan tombol aksi seperti icon-btn pada spek */
.table-stok-wrapper td:nth-last-child(2),
.table-stok-wrapper td:last-child{
    text-align:center;
}

.table-stok-wrapper td:nth-last-child(2){
    min-width:95px;
}

.table-stok-wrapper td:last-child{
    min-width:90px;
}

/* Sedikit pemisah visual untuk data utama */
.table-stok-wrapper tbody td:first-child{
    color:#98a2b3;
    font-size:10px;
}

/* Responsive */
@media(max-width:900px){
    .table-stok-wrapper{
        border-radius:7px;
    }

    .table-stok-wrapper table{
        min-width:1180px;
    }
}
</style>

    @include('pages.laporan.style')
@endsection
