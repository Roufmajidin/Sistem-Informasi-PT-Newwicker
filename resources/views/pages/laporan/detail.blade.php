@extends('master.master')

@section('title', 'Detail Barang')

@section('content')

    <div class="padding">
        <div id="stokRemoteCursors"></div>

        <div class="box">

            <div class="box-header d-flex justify-content-between">

                <h2>
                    Detail Barang
                </h2>

                <a href="{{ url('/laporan') }}" class="btn btn-secondary">
                    Kembali
                </a>

            </div>
            <div class="card mb-3">
                <div class="card-header">
                    Filter Laporan
                </div>

                <div class="card-body">

                    <form method="GET" action="{{ route('laporan.detail', $stok->id) }}">

                        <div class="row">

                            <div class="col-md-3">
                                <label>Dari Tanggal</label>
                                <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>&nbsp;</label>

                                <div>

                                    <button type="submit" class="btn btn-primary">

                                        <i class="fa fa-search"></i>
                                        Filter

                                    </button>

                                    <a href="{{ route('laporan.detail', $stok->id) }}" class="btn btn-warning">

                                        <i class="fa fa-refresh"></i>
                                        Reset Filter

                                    </a>

                                    <a href="{{ route('laporan.detail.pdf', $stok->id) }}?tanggal_awal={{ request('tanggal_awal') }}&tanggal_akhir={{ request('tanggal_akhir') }}"
                                        target="_blank" class="btn btn-danger">

                                        <i class="fa fa-file-pdf-o"></i>
                                        Export PDF

                                    </a>

                                </div>
                            </div>

                        </div>

                    </form>

                </div>
            </div>
            <div class="box-body">
                @php
                    $totalIn = $transaksi->where('tipe', 'in')->sum('qty');

                    $totalOut = $transaksi->where('tipe', 'out')->sum('qty');

                    $stokTersedia = $stok->stok_awal + $totalIn - $totalOut;
                @endphp
                @php
                    function qtyFormat($value)
                    {
                        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
                    }
                @endphp
                <div class="alert alert-info">

                    <b>Total Masuk :</b>
                    {{ qtyFormat($totalIn) }}
                    {{ $stok->satuan }}

                    |

                    <b>Total Keluar :</b>
                    {{ qtyFormat($totalOut) }}
                    {{ $stok->satuan }}

                    |

                    <b>Stok Saat Ini :</b>
                    {{ qtyFormat($stokTersedia) }}
                    {{ $stok->satuan }}

                    hey : jangan lupa untuk masukkan no invoice setiap pembelian bahan finishing

                </div>
                <div class="row mb-3">

                    <div class="col-md-2">
                        <label>Kode Barang</label>
                        <input type="text" class="form-control" value="{{ $stok->kode_barang }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Nama Barang</label>
                        <input type="text" class="form-control" value="{{ $stok->nama_barang }}" readonly>
                    </div>

                    <div class="col-md-2">
                        <label>Jenis</label>
                        <input type="text" class="form-control" value="{{ $stok->jenis }}" readonly>
                    </div>

                    <div class="col-md-2">
                        <label>Satuan</label>
                        <input type="text" class="form-control" value="{{ $stok->satuan }}" readonly>
                    </div>

                    <div class="col-md-2">
                        <label>Harga</label>
                        <input type="text" class="form-control" value="{{ number_format($stok->harga, 0, ',', '.') }}"
                            readonly>
                    </div>

                </div>

                <hr>

                <div id="spkInfo"></div>

                <div class="card mb-4">

                    <div class="card-header">
                        Input Transaksi
                    </div>

                    <div class="card-body">
                        <input type="hidden" id="stok_tersedia" value="{{ $stokTersedia }}">
                        <input type="hidden" id="stok_id" value="{{ $stok->id }}">

                        <input type="hidden" id="spk_id">

                        <div class="row">

                            <div class="col-md-2">

                                <label>Tanggal</label>

                                <input type="date" id="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                                <small id="warningStok"
                                    style="
                            color:red;
                            display:none;
                        ">
                                </small>
                            </div>

                            <div class="col-md-2">

                                <label>IN</label>

                                <input type="number" step="0.001" id="qty_in" class="form-control">

                            </div>

                            <div class="col-md-2">

                                <label>OUT</label>

                                <input type="number" step="0.001" id="qty_out" class="form-control">

                            </div>
                            <div class="col-md-3 mt-4">
                                <div class="input-group">
                                    <input type="text" id="no_spk" class="form-control" readonly>

                                    <button type="button" id="btnCariSpk" class="btn btn-primary">
                                        klik? spk
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-2">

                                <label>Po Number</label>

                                <input type="text" id="no_po" class="form-control">

                            </div>
                            <div class="col-md-2"> <label>No Invoice</label> <input type="text" id="no_invoice"
                                    class="form-control" placeholder="No Invoice"> </div>
                            <div class="col-md-2">

                                <label>Keterangan</label>

                                <input type="text" id="keterangan" class="form-control">

                            </div>

                            <div class="col-md-1">

                                <label>&nbsp;</label>

                                <button type="button" id="btnTambahTransaksi" class="btn btn-success btn-block" disabled
                                    style="cursor:not-allowed;opacity:.6">

                                    Save

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card">

                    <div class="card-header">

                        Riwayat Transaksi

                    </div>

                    <div class="card-body">

                        <table class="table table-bordered table-striped">

                            <thead>

                                <tr>
                                    <th>Tanggal</th>
                                    <th>IN</th>
                                    <th>OUT</th>
                                    <th>Satuan</th>
                                    <th>PO</th>
                                    <th>No Invoice</th>
                                    <th>SPK</th>
                                    <th>Keterangan</th>
                                    <th>Act</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($transaksi as $item)
                                    <tr>

                                        {{-- Tanggal --}}
                                        <td>
                                            <a href="#" class="editable" data-type="date" data-name="tanggal"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}"
                                                data-format="yyyy-mm-dd" data-viewformat="dd/mm/yyyy"
                                                data-value="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}">

                                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}

                                            </a>
                                        </td>

                                        {{-- IN --}}
                                        <td>
                                            @if ($item->tipe == 'in')
                                                <a href="#" class="editable" data-type="text" data-name="qty"
                                                    data-pk="{{ $item->id }}"
                                                    data-url="{{ route('history.updateField', $item->id) }}">

                                                    {{ $item->qty }}

                                                </a>
                                            @endif
                                        </td>

                                        {{-- OUT --}}
                                        <td>
                                            @if ($item->tipe == 'out')
                                                <a href="#" class="editable" data-type="text" data-name="qty"
                                                    data-pk="{{ $item->id }}"
                                                    data-url="{{ route('history.updateField', $item->id) }}">

                                                    {{ $item->qty }}

                                                </a>
                                            @endif
                                        </td>

                                        {{-- SATUAN --}}
                                        <td>
                                            <a href="#" class="editable" data-type="text" data-name="satuan"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ optional($item->stok)->satuan }}

                                            </a>
                                        </td>

                                        {{-- PO --}}
                                        <td>
                                            <a href="#" class="editable" data-type="text" data-name="po"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ $item->po }}

                                            </a>
                                        </td>
                                        {{-- NO INVOICE --}} <td> <a href="#" class="editable" data-type="text"
                                                data-name="no_invoice" data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">
                                                {{ $item->no_invoice }} </a> </td>

                                        {{-- SPK (tetap drawer) --}}
                                        <td class="editable-spk" data-id="{{ $item->id }}"
                                            data-spkid="{{ $item->spk_id }}" style="cursor:pointer;color:#0d6efd">

                                            {{ optional($item->spk)->data['no_spk'] ?? '-' }}

                                        </td>

                                        {{-- Keterangan --}}
                                        <td>
                                            <a href="#" class="editable" data-type="textarea"
                                                data-name="keterangan" data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ $item->keterangan }}

                                            </a>
                                        </td>
                                        {{-- ACT --}}
                                        <td class="text-center">

                                            @if (strtolower(auth()->user()->name ?? '') === 'sumanti')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete-transaksi"
                                                    data-id="{{ $item->id }}" title="Hapus transaksi">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>



    </div>
    <div id="spkDrawerOverlay"></div>

    <div id="spkDrawer">

        <div class="drawer-header">
            <h5>Cari SPK</h5>

            <button type="button" id="closeDrawer" class="btn btn-danger btn-sm">
                ✕
            </button>
        </div>

        <div class="p-3">

            <input type="text" id="searchSpk" class="form-control mb-3" placeholder="Cari No SPK / Supplier">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>No SPK</th>
                            <th>Supplier</th>
                            <th width="80">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="spkTableBody">
                    </tbody>

                </table>

            </div>

        </div>

    </div>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).on('click', '.btn-delete-transaksi', function () {

    const button = $(this);
    const id = button.data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: 'Transaksi ini akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {

        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: "{{ url('/laporan/transaksi') }}/" + id,
            type: 'DELETE',

            data: {
                _token: "{{ csrf_token() }}"
            },

            beforeSend: function() {
                button.prop('disabled', true);
            },

            success: function(response) {

                if (response.success) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message,
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function() {

                        // refresh supaya total IN, OUT dan stok
                        // langsung dihitung ulang
                        location.reload();

                    });

                }
            },

            error: function(xhr) {

                button.prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message ||
                          'Terjadi kesalahan saat menghapus transaksi.'
                });

            }
        });

    });

});
</script>
    <script>
        
        // ajax
        $(document).on('keyup', '#searchSpk', function() {

            let q = $(this).val();
            loadSpk($(this).val());
            $('#searchSpk').focus();

            $.get('/spk/search-spk', {
                q: q
            }, function(res) {

                let html = '';
                console.log(res);
                res.forEach(function(item) {

                    html += `
            <tr>
                <td>${item.no_spk}</td>
                <td>${item.supplier}</td>

                <td>
                    <button
                        class="btn btn-success btn-sm pilih-spk"
                        data-spk='${JSON.stringify(item)}'>
                        Pilih
                    </button>
                </td>
            </tr>
            `;
                });

                $('#spkTableBody').html(html);

            });

        });
        // pilih
        $(document).on('click', '.pilih-spk', function() {

            let data = $(this).data('spk');

            $('#spk_id').val(data.id);

            $('#no_spk').val(data.no_spk);
            // $('#modalSpk').modal('hide');

            tampilkanDetailSpk(data);

        });
        //
        $('#modalSpk').on('shown.bs.modal', function() {
            backdrop: false
            loadSpk();

            $('#searchSpk').focus();

        });

        function tampilkanDetailSpk(data) {
            let itemRows = '';

            data.items.forEach(function(item) {

                itemRows += `
        <tr>
            <td>${item.kode}</td>
            <td>${item.nama}</td>
            <td>${item.qty}</td>
        </tr>
        `;
            });

            $('#spkInfo').html(`
        <div class="alert alert-success position-relative">

            <button
                type="button"
                id="closeSpkInfo"
                class="btn btn-danger btn-sm"
                style="
                    position:absolute;
                    top:10px;
                    right:10px;
                ">
                ✕
            </button>

            <h4>${data.no_spk}</h4>

            Supplier : ${data.supplier}

            <table class="table table-bordered mt-2">

                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Qty</th>
                    </tr>
                </thead>

                <tbody>
                    ${itemRows}
                </tbody>

            </table>

        </div>
    `);
        }

        function checkSaveButton() {

            let qtyIn =
                parseFloat(
                    $('#qty_in').val()
                ) || 0;

            let qtyOut =
                parseFloat(
                    $('#qty_out').val()
                ) || 0;

            let stokTersedia =
                parseFloat(
                    $('#stok_tersedia').val()
                ) || 0;

            let valid = true;

            $('#warningStok')
                .hide();

            $('#qty_out')
                .removeClass('is-invalid');

            if (
                qtyOut > stokTersedia
            ) {

                valid = false;

                $('#qty_out')
                    .addClass('is-invalid');

                $('#warningStok')
                    .html(
                        'Stok tersedia hanya ' +
                        stokTersedia
                    )
                    .show();

            }

            let enable =
                (qtyIn > 0 || qtyOut > 0) &&
                valid;

            $('#btnTambahTransaksi')
                .prop(
                    'disabled',
                    !enable
                );

            if (enable) {

                $('#btnTambahTransaksi')
                    .css({
                        cursor: 'pointer',
                        opacity: 1
                    });

            } else {

                $('#btnTambahTransaksi')
                    .css({
                        cursor: 'not-allowed',
                        opacity: .6
                    });

            }

        }
        $(document).on(
            'input',
            '#qty_in,#qty_out',
            checkSaveButton
        );

        $(document).ready(function() {

            checkSaveButton();

        });
        $(document).on('click', '#btnTambahTransaksi', function() {

            $.ajax({

                url: '/laporan/transaksi/store',

                type: 'POST',

                data: {
                    _token: '{{ csrf_token() }}',

                    stok_id: $('#stok_id').val(),

                    tanggal: $('#tanggal').val(),

                    in: $('#qty_in').val(),

                    out: $('#qty_out').val(),

                    po: $('#no_po').val(),

                    spk_id: $('#spk_id').val(),

                    keterangan: $('#keterangan').val(),
                    no_invoice: $('#no_invoice').val(),
                },

                success: function() {

                    location.reload();

                }

            });

        });

        $(document).on('keyup', '#po', function() {

            let q = $(this).val();

            if (q.length < 2) {

                $('#spkSuggestion').html('');

                return;
            }

            $.get('/spk/search-spk', {
                q: q
            }, function(res) {

                let html = '';

                res.forEach(function(item) {

                    html += `
                <div
                    class="spk-item"
                    data-id="${item.id}"
                    data-spk='${JSON.stringify(item)}'
                    style="
                        padding:8px;
                        cursor:pointer;
                        border-bottom:1px solid #eee;
                    ">
                    ${item.no_spk}
                </div>
            `;
                });

                $('#spkSuggestion').html(html);

            });

        });

        $(document).on('click', '.spk-item', function() {

            let data = $(this).data('spk');

            $('#spk_id').val(data.id);

            $('#no_spk').val(data.no_spk);
            $('#spkSuggestion').html('');

            let itemRows = '';

            data.items.forEach(function(item) {

                itemRows += `
            <tr>
                <td>${item.kode}</td>
                <td>${item.nama}</td>
                <td>${item.qty}</td>
            </tr>
        `;
            });

            $('#spkInfo').html(`
    <div
        class="alert alert-success position-relative">

        <button
            type="button"
            id="closeSpkInfo"
            class="btn btn-danger btn-sm"
            style="
                position:absolute;
                top:10px;
                right:10px;
            ">
            ✕
        </button>

        <h4>${data.no_spk}</h4>

        Supplier :
        ${data.supplier}

        <table class="table table-bordered mt-2">

            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Qty</th>
                </tr>
            </thead>

            <tbody>
                ${itemRows}
            </tbody>

        </table>

    </div>
`);

        });
        $(document).on('click', '#closeSpkInfo', function() {

            $('#spkInfo').slideUp();

        });

        // get the spk all
        function loadSpk(q = '') {
            $.get('/spk/search-spk', {
                q: q
            }, function(res) {

                let html = '';

                res.forEach(function(item) {

                    html += `
            <tr>
                <td>${item.no_spk}</td>
                <td>${item.supplier}</td>
                <td>
                    <button
                        class="btn btn-success btn-xs pilih-spk"
                        data-spk='${JSON.stringify(item)}'>
                        Pilih
                    </button>
                </td>
            </tr>
            `;
                });

                $('#spkTableBody').html(html);

            });
        }
        $('#btnCariSpk').click(function() {

            $('#spkDrawer').addClass('show');
            $('#spkDrawerOverlay').show();

            loadSpk();

        });

        $('#closeDrawer,#spkDrawerOverlay').click(function() {

            $('#spkDrawer').removeClass('show');
            $('#spkDrawerOverlay').hide();

        });
        $(document).on('click', '.pilih-spk', function() {

            let data = $(this).data('spk');

            $('#spk_id').val(data.id);

            $('#no_spk').val(data.no_spk);


            tampilkanDetailSpk(data);

            $('#spkDrawer').removeClass('show');
            $('#spkDrawerOverlay').hide();

        });
        let currentHistoryId = null;

        $(document).on('click', '.editable-spk', function() {

            currentHistoryId = $(this).data('id');

            $('#spkDrawer').addClass('show');
            $('#spkDrawerOverlay').show();

            loadSpk(); // fungsi yang sudah ada
        });
        // spk edit 
        $(document).on('click', '.pilih-spk', function() {

            let data = $(this).data('spk');

            /*
             * EDIT HISTORY
             */
            if (currentHistoryId) {

                $.ajax({

                    url: '/history/update-spk/' + currentHistoryId,

                    type: 'POST',

                    data: {

                        _token: '{{ csrf_token() }}',

                        spk_id: data.id

                    },

                    success: function(res) {

                        $('td.editable-spk[data-id="' + currentHistoryId + '"]')
                            .text(data.no_spk);

                        $('td.editable-spk[data-id="' + currentHistoryId + '"]')
                            .attr('data-spkid', data.id);

                        currentHistoryId = null;

                        $('#spkDrawer').removeClass('show');
                        $('#spkDrawerOverlay').hide();

                    }

                });

                return;
            }

            /*
             * FORM INPUT (yang lama)
             */
            $('#spk_id').val(data.id);

            $('#no_spk').val(data.no_spk);

            tampilkanDetailSpk(data);

            $('#spkDrawer').removeClass('show');
            $('#spkDrawerOverlay').hide();

        });
        // save
    </script>
    <style>
        /* =========================================================
           REALTIME REMOTE CURSOR - PUSHER
           ========================================================= */

        #stokRemoteCursors {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9999999;
        }

        .stok-remote-cursor {
            position: fixed;
            pointer-events: none;
            transform: translate(-1px, -1px);
            transition:
                left 90ms linear,
                top 90ms linear;
            will-change: left, top;
        }

        .stok-remote-cursor-arrow {
            width: 0;
            height: 0;

            border-top: 0 solid transparent;
            border-bottom: 15px solid transparent;
            border-left: 11px solid #2563eb;

            transform: rotate(-42deg);

            filter:
                drop-shadow(0 1px 1px rgba(0, 0, 0, .25));
        }

        .stok-remote-cursor-name {
            position: absolute;

            left: 9px;
            top: 12px;

            padding: 3px 7px;

            border-radius: 4px;

            background: #2563eb;
            color: #fff;

            font-size: 10px;
            font-weight: 700;

            line-height: 1.2;

            white-space: nowrap;

            box-shadow:
                0 2px 5px rgba(0, 0, 0, .18);
        }

        .stok-remote-cursor.is-idle {
            opacity: .45;
        }

        .editable-text,
        .editable-number,
        .editable-date,
        .editable-spk {
            cursor: pointer;
            transition: .2s;
        }

        .editable-text:hover,
        .editable-number:hover,
        .editable-date:hover,
        .editable-spk:hover {
            background: #fff8d6;
        }

        #spkDrawerOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            display: none;
            z-index: 9998;
        }

        #spkDrawer {
            position: fixed;
            top: 0;
            right: -800px;
            width: 800px;
            max-width: 90vw;
            height: 100vh;
            background: #fff;
            z-index: 9999;
            transition: .3s;
            overflow-y: auto;
            box-shadow: -5px 0 20px rgba(0, 0, 0, .15);
        }

        #spkDrawer.show {
            right: 0;
        }

        .drawer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 10;
        }
    </style>
    <script>
        $(document).ready(function() {

            console.log('Init Editable');

            $.fn.editable.defaults.mode = 'inline';

            $('.editable').editable({

                ajaxOptions: {
                    type: 'POST',
                    dataType: 'json'
                },

                success: function(response) {

                    if (response.status !== 'success') {
                        return response.msg || 'Update failed.';
                    }

                }

            });

        });
    </script>
    <script>
        (function() {

            'use strict';

            /*
            |--------------------------------------------------------------------------
            | STOK ID
            |--------------------------------------------------------------------------
            */

            const stokId =
                @json($stok->id);

            if (!stokId) {
                console.warn('[STOK Cursor] stok_id tidak tersedia.');
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | USER
            |--------------------------------------------------------------------------
            */

            const currentUserId =
                @json(auth()->id());

            const currentUserName =
                @json(auth()->user()->name ?? 'User');


            /*
            |--------------------------------------------------------------------------
            | PUSHER CONFIG
            |--------------------------------------------------------------------------
            */

            const pusherKey =
                @json(config('broadcasting.connections.pusher.key'));

            const pusherCluster =
                @json(config('broadcasting.connections.pusher.options.cluster'));


            if (!pusherKey) {

                console.warn(
                    '[STOK Cursor] Pusher key belum tersedia.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PUSHER
            |--------------------------------------------------------------------------
            */

            const pusher =
                new Pusher(
                    pusherKey, {
                        cluster: pusherCluster || 'ap1',

                        forceTLS: true,

                        authEndpoint: @json(route('pusher.auth')),

                        auth: {
                            headers: {

                                'X-CSRF-TOKEN': document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    ?.getAttribute(
                                        'content'
                                    ) || ''

                            }
                        }
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | CHANNEL
            |--------------------------------------------------------------------------
            */

            const channelName =
                'presence-stok-' + stokId;


            const channel =
                pusher.subscribe(channelName);


            /*
            |--------------------------------------------------------------------------
            | CONNECTION
            |--------------------------------------------------------------------------
            */

            pusher.connection.bind(
                'connected',
                function() {

                    console.log(
                        '[PUSHER STOK] Connected:',
                        pusher.connection.socket_id
                    );

                }
            );


            pusher.connection.bind(
                'error',
                function(err) {

                    console.error(
                        '[PUSHER STOK] Connection error:',
                        err
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SUBSCRIPTION
            |--------------------------------------------------------------------------
            */

            channel.bind(
                'pusher:subscription_succeeded',
                function(members) {

                    console.log(
                        '[PUSHER STOK] Presence connected'
                    );

                    console.log(
                        '[PUSHER STOK] Channel:',
                        channelName
                    );

                    console.log(
                        '[PUSHER STOK] Members:',
                        members.count
                    );

                    members.each(
                        function(member) {

                            console.log(
                                '[PUSHER STOK] Member:',
                                member.id,
                                member.info
                            );

                        }
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CONTAINER
            |--------------------------------------------------------------------------
            */

            const container =
                document.getElementById(
                    'stokRemoteCursors'
                );


            if (!container) {

                console.warn(
                    '[STOK Cursor] Container tidak ditemukan.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CURSOR STORAGE
            |--------------------------------------------------------------------------
            */

            const remoteCursors = {};


            /*
            |--------------------------------------------------------------------------
            | CREATE CURSOR
            |--------------------------------------------------------------------------
            */

            function createCursor(
                userId,
                name
            ) {

                const id =
                    'stok-remote-cursor-' + userId;


                let cursor =
                    document.getElementById(id);


                if (cursor) {
                    return cursor;
                }


                cursor =
                    document.createElement('div');


                cursor.id = id;

                cursor.className =
                    'stok-remote-cursor';


                /*
                | Arrow
                */

                const arrow =
                    document.createElement('div');

                arrow.className =
                    'stok-remote-cursor-arrow';


                /*
                | Name
                */

                const label =
                    document.createElement('div');

                label.className =
                    'stok-remote-cursor-name';

                label.textContent =
                    name || 'User';


                cursor.appendChild(arrow);

                cursor.appendChild(label);


                container.appendChild(cursor);


                remoteCursors[userId] = {

                    element: cursor,

                    lastMove: Date.now(),

                    x: 0,

                    y: 0

                };


                return cursor;

            }


            /*
            |--------------------------------------------------------------------------
            | REMOVE CURSOR
            |--------------------------------------------------------------------------
            */

            function removeCursor(userId) {

                const data =
                    remoteCursors[userId];


                if (!data) {
                    return;
                }


                data.element.remove();


                delete remoteCursors[userId];

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE CURSOR
            |--------------------------------------------------------------------------
            */

            function updateCursor(data) {

                if (!data) {
                    return;
                }


                const userId =
                    String(data.user_id);


                /*
                | Jangan tampilkan cursor sendiri
                */

                if (
                    userId ===
                    String(currentUserId)
                ) {

                    return;

                }


                const cursor =
                    createCursor(
                        userId,
                        data.name
                    );


                const state =
                    remoteCursors[userId];


                if (!state) {
                    return;
                }


                const x =
                    Number(data.x);


                const y =
                    Number(data.y);


                if (
                    !Number.isFinite(x) ||
                    !Number.isFinite(y)
                ) {

                    return;

                }


                state.x = x;

                state.y = y;

                state.lastMove =
                    Date.now();


                cursor.style.left =
                    x + 'px';


                cursor.style.top =
                    y + 'px';


                cursor.classList.remove(
                    'is-idle'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | SEND CURSOR
            |--------------------------------------------------------------------------
            */

            let lastSend = 0;

            let lastX = null;

            let lastY = null;


            const SEND_INTERVAL = 150;

            const MIN_DISTANCE = 5;


            document.addEventListener(
                'mousemove',
                function(event) {

                    const now =
                        Date.now();


                    /*
                    | Throttle
                    */

                    if (
                        now - lastSend <
                        SEND_INTERVAL
                    ) {

                        return;

                    }


                    const x =
                        event.clientX;


                    const y =
                        event.clientY;


                    /*
                    | Jangan kirim jika gerak
                    | terlalu sedikit
                    */

                    if (
                        lastX !== null &&
                        lastY !== null
                    ) {

                        const dx =
                            x - lastX;

                        const dy =
                            y - lastY;


                        const distance =
                            Math.sqrt(
                                dx * dx +
                                dy * dy
                            );


                        if (
                            distance <
                            MIN_DISTANCE
                        ) {

                            return;

                        }

                    }


                    lastX = x;

                    lastY = y;

                    lastSend = now;


                    /*
                    | CLIENT EVENT
                    */

                    try {

                        channel.trigger(
                            'client-stok-cursor', {

                                user_id: currentUserId,

                                name: currentUserName,

                                x: x,

                                y: y

                            }
                        );


                    } catch (error) {

                        console.warn(
                            '[STOK Cursor]',
                            error
                        );

                    }

                }, {
                    passive: true
                }
            );


            /*
            |--------------------------------------------------------------------------
            | RECEIVE CURSOR
            |--------------------------------------------------------------------------
            */

            channel.bind(
                'client-stok-cursor',
                function(data) {

                    console.log(
                        '[PUSHER STOK] Cursor received:',
                        data
                    );

                    updateCursor(data);

                }
            );


            /*
            |--------------------------------------------------------------------------
            | MEMBER ADDED
            |--------------------------------------------------------------------------
            */

            channel.bind(
                'pusher:member_added',
                function(member) {

                    console.log(
                        '[STOK Cursor] User masuk:',
                        member.info?.name ||
                        member.id
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | MEMBER REMOVED
            |--------------------------------------------------------------------------
            */

            channel.bind(
                'pusher:member_removed',
                function(member) {

                    removeCursor(
                        String(member.id)
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | IDLE
            |--------------------------------------------------------------------------
            */

            setInterval(
                function() {

                    const now =
                        Date.now();


                    Object.keys(
                        remoteCursors
                    ).forEach(
                        function(userId) {

                            const state =
                                remoteCursors[
                                    userId
                                ];


                            if (!state) {
                                return;
                            }


                            if (
                                now -
                                state.lastMove >
                                5000
                            ) {

                                state.element
                                    .classList
                                    .add(
                                        'is-idle'
                                    );

                            }

                        }
                    );

                },
                1000
            );


            /*
            |--------------------------------------------------------------------------
            | CLEANUP
            |--------------------------------------------------------------------------
            */

            window.addEventListener(
                'beforeunload',
                function() {

                    try {

                        pusher.unsubscribe(
                            channelName
                        );

                    } catch (e) {}

                }
            );


        })();
    </script>
@endsection
