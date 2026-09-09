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

                {{-- ============================================================
                     FILTER JENIS
                ============================================================ --}}
                <div class="col-md-3">

                    <label>Jenis Barang</label>

                    <select id="filterJenis" class="form-control">

                        <option value="">
                            Semua Jenis
                        </option>

                        <option value="bahan baku"
                            {{ request('jenis') == 'bahan baku' ? 'selected' : '' }}>
                            Bahan Baku
                        </option>

                        <option value="bahan penolong"
                            {{ request('jenis') == 'bahan penolong' ? 'selected' : '' }}>
                            Bahan Penolong
                        </option>

                        <option value="bahan penolong alat"
                            {{ request('jenis') == 'bahan penolong alat' ? 'selected' : '' }}>
                            Bahan Penolong Alat
                        </option>

                        <option value="bahan finishing"
                            {{ request('jenis') == 'bahan finishing' ? 'selected' : '' }}>
                            Bahan Finishing
                        </option>

                    </select>

                </div>


                {{-- ============================================================
                     SEARCH
                ============================================================ --}}
                <div class="col-md-2">

                    <label>-</label>

                    <input
                        type="text"
                        id="searchBarang"
                        class="form-control"
                        placeholder="Cari nama barang..."
                        value="{{ request('search', '') }}"
                        autocomplete="off"
                    >

                </div>


                {{-- ============================================================
                     HISTORY
                ============================================================ --}}
                <div class="col-md-2 mt-4">

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="addRow"
                    >

                        <i class="fa fa-info"></i>

                        <a
                            href="/laporan/warehouse-history"
                            style="color:inherit;text-decoration:none;"
                        >
                            history
                        </a>

                    </button>

                </div>


                {{-- ============================================================
                     TAMBAH BARIS
                ============================================================ --}}
                <div class="col-md-5 text-right">

                    <br>

                    <button
                        type="button"
                        class="btn btn-primary"
                        id="addRowss"
                    >

                        <i class="fa fa-plus"></i>

                        Tambah Baris

                    </button>

                </div>

            </div>


            {{-- ================================================================
                 TABLE WRAPPER
            ================================================================= --}}
            <div
                id="wrapperStok"
                style="
                    overflow-x:auto;
                    overflow-y:hidden;
                    width:100%;
                "
            >

                <div
                    id="canvasStok"
                    style="
                        width:2200px;
                        display:flex;
                        align-items:flex-start;
                    "
                >

                    <div
                        id="masterPanel"
                        style="
                            min-width:1300px;
                            padding-right:20px;
                        "
                    >

                        <div class="table-stok-wrapper">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th width="50">
                                            No
                                        </th>

                                        <th width="110">
                                            Kode Barang
                                        </th>

                                        <th>
                                            Nama Barang
                                        </th>

                                        <th width="170">
                                            Jenis
                                        </th>

                                        <th width="60">
                                            Satuan
                                        </th>

                                        <th width="100">
                                            Harga
                                        </th>

                                        <th width="90">
                                            Saldo
                                        </th>

                                        <th width="80">
                                            Stok In
                                        </th>

                                        <th width="80">
                                            Stok Out
                                        </th>

                                        <th width="130">
                                            Tanggal
                                        </th>

                                        <th width="80">
                                            Aksi
                                        </th>

                                        <th width="100">
                                            Detail
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="tableBody">

                                    @forelse($stoks ?? [] as $key => $stok)

                                        <tr data-id="{{ $stok->id }}">

                                            {{-- NO --}}
                                            <td>
                                                {{ $key + 1 }}
                                            </td>


                                            {{-- KODE BARANG --}}
                                            <td>

                                                <input
                                                    type="hidden"
                                                    class="id"
                                                    value="{{ $stok->id }}"
                                                >

                                                <input
                                                    type="text"
                                                    class="form-control kode_barang"
                                                    value="{{ $stok->kode_barang }}"
                                                >

                                            </td>


                                            {{-- NAMA BARANG --}}
                                            <td>

                                                <input
                                                    type="text"
                                                    class="form-control nama_barang"
                                                    value="{{ $stok->nama_barang }}"
                                                >

                                            </td>


                                            {{-- JENIS --}}
                                            <td>

                                                <select class="form-control jenis">

                                                    <option
                                                        value="bahan baku"
                                                        {{ $stok->jenis == 'bahan baku' ? 'selected' : '' }}
                                                    >
                                                        Bahan Baku
                                                    </option>

                                                    <option
                                                        value="bahan penolong"
                                                        {{ $stok->jenis == 'bahan penolong' ? 'selected' : '' }}
                                                    >
                                                        Bahan Penolong
                                                    </option>

                                                    <option
                                                        value="bahan penolong alat"
                                                        {{ $stok->jenis == 'bahan penolong alat' ? 'selected' : '' }}
                                                    >
                                                        Bahan Penolong Alat
                                                    </option>

                                                    <option
                                                        value="bahan finishing"
                                                        {{ $stok->jenis == 'bahan finishing' ? 'selected' : '' }}
                                                    >
                                                        Bahan Finishing
                                                    </option>

                                                </select>

                                            </td>


                                            {{-- SATUAN --}}
                                            <td>

                                                <input
                                                    type="text"
                                                    class="form-control satuan"
                                                    value="{{ $stok->satuan }}"
                                                >

                                            </td>


                                            {{-- HARGA --}}
                                            <td>

                                                <input
                                                    type="text"
                                                    class="form-control harga"
                                                    value="{{ !empty($stok->harga)
                                                        ? number_format($stok->harga, 0, ',', '.')
                                                        : '' }}"
                                                >

                                            </td>


                                            {{-- SALDO --}}
                                            <td>

                                                <input
                                                    type="number"
                                                    step="0.001"
                                                    class="form-control stok_awal"
                                                    value="{{
                                                        ($stok->stok_awal ?? 0)
                                                        + ($stok->total_in ?? 0)
                                                        - ($stok->total_out ?? 0)
                                                    }}"
                                                >

                                            </td>


                                            {{-- STOK IN --}}
                                            <td>

                                                {{ number_format(
                                                    $stok->total_in ?? 0,
                                                    2,
                                                    '.',
                                                    ','
                                                ) }}

                                            </td>


                                            {{-- STOK OUT --}}
                                            <td>

                                                {{ number_format(
                                                    $stok->total_out ?? 0,
                                                    2,
                                                    '.',
                                                    ','
                                                ) }}

                                            </td>


                                            {{-- TANGGAL --}}
                                            <td>

                                                <input
                                                    type="date"
                                                    class="form-control tanggal"
                                                    value="{{ date('Y-m-d') }}"
                                                >

                                            </td>


                                            {{-- AKSI --}}
                                            <td width="120">

                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm btn-save"
                                                >

                                                    <i class="fa fa-save"></i>

                                                </button>


                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm remove-row"
                                                >

                                                    <i class="fa fa-trash"></i>

                                                </button>

                                            </td>


                                            {{-- DETAIL --}}
                                            <td>

                                                <a
                                                    href="{{ route('laporan.detail', $stok->id) }}"
                                                    class="btn btn-info btn-sm"
                                                >
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

                </div>

            </div>

        </div>

    </div>
</div>


{{-- ================================================================
     TOASTR
================================================================= --}}
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script>
    $(document).ready(function() {

        // ============================================================
        // TAMBAH BARIS
        // ============================================================

        $('#addRowss').on('click', function(e) {

            e.preventDefault();

            let rowCount =
                $('#tableBody tr').length + 1;

            let row = `
                <tr data-id="">

                    <td>
                        ${rowCount}
                    </td>

                    <td>

                        <input
                            type="hidden"
                            class="id"
                            value=""
                        >

                        <input
                            type="text"
                            class="form-control kode_barang"
                        >

                    </td>

                    <td>

                        <input
                            type="text"
                            class="form-control nama_barang"
                        >

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
                            class="form-control satuan"
                        >

                    </td>

                    <td>

                        <input
                            type="text"
                            class="form-control harga"
                        >

                    </td>

                    <td>

                        <input
                            type="number"
                            step="0.001"
                            class="form-control stok_awal"
                            value="0"
                        >

                    </td>

                    <td>
                        0
                    </td>

                    <td>
                        0
                    </td>

                    <td>

                        <input
                            type="date"
                            class="form-control tanggal"
                            value="{{ date('Y-m-d') }}"
                        >

                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-success btn-sm btn-save"
                        >
                            <i class="fa fa-save"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger btn-sm remove-row"
                        >
                            <i class="fa fa-trash"></i>
                        </button>

                    </td>

                    <td></td>

                </tr>
            `;

            $('#tableBody').append(row);

            filterData();

        });


        // ============================================================
        // FORMAT HARGA
        // ============================================================

        $(document).on(
            'keyup',
            '.harga',
            function() {

                let value =
                    $(this)
                    .val()
                    .replace(/\D/g, '');

                if (value === '') {

                    $(this).val('');

                    return;

                }

                $(this).val(
                    new Intl.NumberFormat('id-ID')
                        .format(value)
                );

            }
        );


        // ============================================================
        // SIMPAN
        // ============================================================

        $(document).on(
            'click',
            '.btn-save',
            function(e) {

                e.preventDefault();

                let btn =
                    $(this);

                let row =
                    btn.closest('tr');


                let id =
                    row.find('.id').val();

                let kode_barang =
                    row.find('.kode_barang').val();

                let nama_barang =
                    row.find('.nama_barang').val();

                let jenis =
                    row.find('.jenis').val();

                let satuan =
                    row.find('.satuan').val();

                let harga =
                    row.find('.harga').val();

                let stok_awal =
                    row.find('.stok_awal').val();


                // ----------------------------------------------------
                // VALIDASI
                // ----------------------------------------------------

                if (!kode_barang) {

                    toastr.warning(
                        'Kode barang wajib diisi.'
                    );

                    row.find('.kode_barang').focus();

                    return;

                }


                if (!nama_barang) {

                    toastr.warning(
                        'Nama barang wajib diisi.'
                    );

                    row.find('.nama_barang').focus();

                    return;

                }


                // ----------------------------------------------------
                // AJAX
                // ----------------------------------------------------

                $.ajax({

                    url: "{{ route('laporan.update') }}",

                    type: "POST",

                    data: {

                        _token:
                            "{{ csrf_token() }}",

                        id:
                            id,

                        kode_barang:
                            kode_barang,

                        nama_barang:
                            nama_barang,

                        jenis:
                            jenis,

                        satuan:
                            satuan,

                        harga:
                            harga,

                        stok_awal:
                            stok_awal

                    },


                    beforeSend: function() {

                        btn
                            .prop(
                                'disabled',
                                true
                            )
                            .html(
                                '<i class="fa fa-spinner fa-spin"></i>'
                            );

                    },


                    success: function(res) {

                        toastr.success(
                            res.message ||
                            'Data berhasil disimpan'
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | BARIS BARU
                        |--------------------------------------------------------------------------
                        */

                        if (
                            row.find('.id').val() === ''
                            &&
                            res.id
                        ) {

                            row
                                .find('.id')
                                .val(res.id);

                            row.attr(
                                'data-id',
                                res.id
                            );

                        }

                    },


                    error: function(xhr) {

                        console.error(
                            'LAPORAN UPDATE ERROR:',
                            xhr.status,
                            xhr.responseText
                        );


                        let message =
                            'Gagal menyimpan';


                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {

                            message =
                                xhr.responseJSON.message;

                        }


                        toastr.error(
                            message
                        );

                    },


                    complete: function() {

                        btn
                            .prop(
                                'disabled',
                                false
                            )
                            .html(
                                '<i class="fa fa-save"></i>'
                            );

                    }

                });

            }
        );


        // ============================================================
        // HAPUS BARIS BARU
        // ============================================================

        $(document).on(
            'click',
            '.remove-row',
            function(e) {

                e.preventDefault();

                let row =
                    $(this).closest('tr');

                let id =
                    row.find('.id').val();


                if (!id) {

                    row.remove();

                    filterData();

                    return;

                }


                toastr.warning(
                    'Data sudah tersimpan. Delete database akan kita buat berikutnya.'
                );

            }
        );


        // ============================================================
        // FILTER DATA
        // ============================================================

        function filterData() {

            let keyword =
                (
                    $('#searchBarang').val() || ''
                )
                .toLowerCase()
                .trim();


            let jenis =
                (
                    $('#filterJenis').val() || ''
                )
                .toLowerCase()
                .trim();


            let no = 1;


            $('#tableBody tr').each(function() {

                let row =
                    $(this);


                let nama =
                    (
                        row
                            .find('.nama_barang')
                            .val() || ''
                    )
                    .toLowerCase()
                    .trim();


                let kode =
                    (
                        row
                            .find('.kode_barang')
                            .val() || ''
                    )
                    .toLowerCase()
                    .trim();


                let rowJenis =
                    (
                        row
                            .find('.jenis')
                            .val() || ''
                    )
                    .toLowerCase()
                    .trim();


                /*
                |--------------------------------------------------------------------------
                | SEARCH NAMA + KODE
                |--------------------------------------------------------------------------
                */

                let matchSearch =
                    keyword === '' ||
                    nama.includes(keyword) ||
                    kode.includes(keyword);


                /*
                |--------------------------------------------------------------------------
                | FILTER JENIS
                |--------------------------------------------------------------------------
                */

                let matchJenis =
                    jenis === '' ||
                    rowJenis === jenis;


                if (
                    matchSearch &&
                    matchJenis
                ) {

                    row.show();

                    row
                        .find('td:first')
                        .text(no++);

                } else {

                    row.hide();

                }

            });

        }


        // ============================================================
        // UPDATE URL
        // ============================================================

        function updateUrl() {

            let url =
                new URL(
                    window.location.href
                );


            let search =
                (
                    $('#searchBarang').val() || ''
                )
                .trim();


            let jenis =
                (
                    $('#filterJenis').val() || ''
                )
                .trim();


            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            if (search !== '') {

                url.searchParams.set(
                    'search',
                    search
                );

            } else {

                /*
                | Ini yang penting:
                | kalau search dihapus, parameter search juga dihapus.
                */

                url.searchParams.delete(
                    'search'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | JENIS
            |--------------------------------------------------------------------------
            */

            if (jenis !== '') {

                url.searchParams.set(
                    'jenis',
                    jenis
                );

            } else {

                url.searchParams.delete(
                    'jenis'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Ganti URL tanpa reload
            |--------------------------------------------------------------------------
            */

            window.history.replaceState(
                {},
                '',
                url.pathname +
                (
                    url.searchParams.toString()
                        ? '?' + url.searchParams.toString()
                        : ''
                )
            );

        }


        // ============================================================
        // SEARCH
        // ============================================================

        $('#searchBarang').on(
            'input',
            function() {

                let search =
                    $(this)
                    .val()
                    .trim();


                /*
                |--------------------------------------------------------------------------
                | FILTER LANGSUNG
                |--------------------------------------------------------------------------
                */

                filterData();


                /*
                |--------------------------------------------------------------------------
                | UPDATE URL
                |--------------------------------------------------------------------------
                */

                updateUrl();


                /*
                |--------------------------------------------------------------------------
                | JIKA SEARCH DIHAPUS
                |--------------------------------------------------------------------------
                |
                | URL langsung menjadi:
                |
                | /laporan
                |
                | atau:
                |
                | /laporan?jenis=bahan%20baku
                |
                | jika filter jenis masih aktif.
                |
                */

                if (search === '') {

                    /*
                    | Reload agar data dari database kembali
                    | ke seluruh barang.
                    */

                    let url =
                        new URL(
                            window.location.href
                        );

                    /*
                    | Jangan langsung reload setiap input.
                    | Kita tunggu sebentar supaya tidak mengganggu
                    | user saat mengetik.
                    */

                    clearTimeout(
                        window.searchClearTimer
                    );

                    window.searchClearTimer =
                        setTimeout(
                            function() {

                                /*
                                | Pastikan search benar-benar kosong.
                                */

                                if (
                                    $('#searchBarang')
                                        .val()
                                        .trim() === ''
                                ) {

                                    window.location.href =
                                        url.toString();

                                }

                            },
                            250
                        );

                }

            }
        );


        // ============================================================
        // ENTER SEARCH
        // ============================================================

        $('#searchBarang').on(
            'keydown',
            function(e) {

                if (
                    e.key === 'Enter' ||
                    e.keyCode === 13
                ) {

                    e.preventDefault();


                    let search =
                        $(this)
                        .val()
                        .trim();


                    let jenis =
                        $('#filterJenis')
                        .val()
                        .trim();


                    let url =
                        new URL(
                            window.location.href
                        );


                    if (search !== '') {

                        url.searchParams.set(
                            'search',
                            search
                        );

                    } else {

                        url.searchParams.delete(
                            'search'
                        );

                    }


                    if (jenis !== '') {

                        url.searchParams.set(
                            'jenis',
                            jenis
                        );

                    } else {

                        url.searchParams.delete(
                            'jenis'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Reload dengan query database
                    |--------------------------------------------------------------------------
                    */

                    window.location.href =
                        url.toString();

                }

            }
        );


        // ============================================================
        // FILTER JENIS
        // ============================================================

        $('#filterJenis').on(
            'change',
            function() {

                /*
                |--------------------------------------------------------------------------
                | Simpan filter ke URL
                |--------------------------------------------------------------------------
                */

                updateUrl();


                /*
                |--------------------------------------------------------------------------
                | Reload supaya controller melakukan query database
                |--------------------------------------------------------------------------
                */

                let url =
                    new URL(
                        window.location.href
                    );


                window.location.href =
                    url.toString();

            }
        );


        // ============================================================
        // AUTO SEARCH BARANG
        // ============================================================

        $(document).on(
            'keyup',
            '.nama_barang',
            function() {

                let input =
                    $(this);

                let q =
                    input.val();


                if (
                    !q ||
                    q.length < 2
                ) {

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


                        let row =
                            input.closest('tr');


                        /*
                        |--------------------------------------------------------------------------
                        | KODE BARANG
                        |--------------------------------------------------------------------------
                        */

                        if (
                            row
                                .find('.kode_barang')
                                .val() === ''
                        ) {

                            row
                                .find('.kode_barang')
                                .val(
                                    res.kode_barang
                                );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SATUAN
                        |--------------------------------------------------------------------------
                        */

                        if (
                            row
                                .find('.satuan')
                                .val() === ''
                        ) {

                            row
                                .find('.satuan')
                                .val(
                                    res.satuan
                                );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | HARGA
                        |--------------------------------------------------------------------------
                        */

                        if (
                            row
                                .find('.harga')
                                .val() === ''
                        ) {

                            row
                                .find('.harga')
                                .val(

                                    new Intl.NumberFormat(
                                        'id-ID'
                                    ).format(
                                        res.harga
                                    )

                                );

                        }

                    }

                );

            }
        );


        // ============================================================
        // INITIAL FILTER
        // ============================================================

        filterData();


    });

</script>


@include('pages.laporan.style')

@endsection