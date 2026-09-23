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
                                <input type="date"
                                    name="tanggal_awal"
                                    value="{{ request('tanggal_awal') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Sampai Tanggal</label>
                                <input type="date"
                                    name="tanggal_akhir"
                                    value="{{ request('tanggal_akhir') }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>&nbsp;</label>

                                <div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i>
                                        Filter
                                    </button>

                                    <a href="{{ route('laporan.detail', $stok->id) }}"
                                        class="btn btn-warning">
                                        <i class="fa fa-refresh"></i>
                                        Reset Filter
                                    </a>

                                    <a href="{{ route('laporan.detail.pdf', $stok->id) }}?tanggal_awal={{ request('tanggal_awal') }}&tanggal_akhir={{ request('tanggal_akhir') }}"
                                        target="_blank"
                                        class="btn btn-danger">
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

                {{-- =====================================================
                    PERHITUNGAN STOK
                    ===================================================== --}}

                @php

                    /*
                     * OPNAME = PENYESUAIAN STOK
                     *
                     * PERUBAHAN:
                     * - stok_awal TIDAK digunakan
                     * - titik awal selalu 0
                     * - Opname IN tetap menambah stok
                     * - Opname OUT tetap mengurangi stok
                     * - Transaksi normal IN menambah stok
                     * - Transaksi normal OUT mengurangi stok
                     */

                    // =====================================================
                    // PISAHKAN TRANSAKSI OPNAME
                    // =====================================================

                    $transaksiOpname = $transaksi->filter(function ($item) {

                        $keterangan = strtolower(
                            trim((string) $item->keterangan)
                        );

                        return str_contains($keterangan, 'opname')
                            || str_contains($keterangan, 'opanem')
                            || str_contains($keterangan, 'opaneme');
                    });


                    // =====================================================
                    // TRANSAKSI NORMAL
                    // =====================================================

                    $transaksiNormal = $transaksi->reject(function ($item) {

                        $keterangan = strtolower(
                            trim((string) $item->keterangan)
                        );

                        return str_contains($keterangan, 'opname')
                            || str_contains($keterangan, 'opanem')
                            || str_contains($keterangan, 'opaneme');
                    });


                    // =====================================================
                    // OPNAME IN
                    // =====================================================

                    $opnameIn = $transaksiOpname
                        ->where('tipe', 'in')
                        ->sum('qty');


                    // =====================================================
                    // OPNAME OUT
                    // =====================================================

                    $opnameOut = $transaksiOpname
                        ->where('tipe', 'out')
                        ->sum('qty');


                    // =====================================================
                    // TRANSAKSI NORMAL IN
                    // =====================================================

                    $totalIn = $transaksiNormal
                        ->where('tipe', 'in')
                        ->sum('qty');


                    // =====================================================
                    // TRANSAKSI NORMAL OUT
                    // =====================================================

                    $totalOut = $transaksiNormal
                        ->where('tipe', 'out')
                        ->sum('qty');


                    // =====================================================
                    // STOK SAAT INI
                    //
                    // stok_awal TIDAK DILIBATKAN
                    //
                    // RUMUS:
                    //
                    // 0
                    // + OPNAME IN
                    // - OPNAME OUT
                    // + IN NORMAL
                    // - OUT NORMAL
                    // =====================================================

                    $stokTersedia =
                        (float) $opnameIn
                        - (float) $opnameOut
                        + (float) $totalIn
                        - (float) $totalOut;

                @endphp


                @php

                    function qtyFormat($value)
                    {
                        return rtrim(
                            rtrim(
                                number_format($value, 3, '.', ''),
                                '0'
                            ),
                            '.'
                        );
                    }

                @endphp


                {{-- =====================================================
                    INFO STOK
                    ===================================================== --}}

                <div class="alert alert-info">

                    <b>Stok Saat Ini :</b>

                    {{ qtyFormat($stokTersedia) }}

                    {{ $stok->satuan }}

                </div>


                <div class="row mb-3">

                    <div class="col-md-2">

                        <label>Kode Barang</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $stok->kode_barang }}"
                            readonly>

                    </div>


                    <div class="col-md-4">

                        <label>Nama Barang</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $stok->nama_barang }}"
                            readonly>

                    </div>


                    <div class="col-md-2">

                        <label>Jenis</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $stok->jenis }}"
                            readonly>

                    </div>


                    <div class="col-md-2">

                        <label>Satuan</label>

                        <input type="text"
                            class="form-control"
                            value="{{ $stok->satuan }}"
                            readonly>

                    </div>


                    <div class="col-md-2">

                        <label>Harga</label>

                        <input type="text"
                            class="form-control"
                            value="{{ number_format($stok->harga, 0, ',', '.') }}"
                            readonly>

                    </div>

                </div>


                <hr>


                <div id="spkInfo"></div>


                {{-- =====================================================
                    INPUT TRANSAKSI
                    ===================================================== --}}

                <div class="card mb-4">

                    <div class="card-header">
                        Input Transaksi
                    </div>

                    <div class="card-body">

                        <input type="hidden"
                            id="stok_tersedia"
                            value="{{ $stokTersedia }}">

                        <input type="hidden"
                            id="stok_id"
                            value="{{ $stok->id }}">

                        <input type="hidden"
                            id="spk_id">


                        <div class="row">

                            <div class="col-md-2">

                                <label>Tanggal</label>

                                <input type="date"
                                    id="tanggal"
                                    class="form-control"
                                    value="{{ date('Y-m-d') }}">

                                <small id="warningStok"
                                    style="
                                        color:red;
                                        display:none;
                                    ">
                                </small>

                            </div>


                            <div class="col-md-2">

                                <label>IN</label>

                                <input type="number"
                                    step="0.001"
                                    id="qty_in"
                                    class="form-control">

                            </div>


                            <div class="col-md-2">

                                <label>OUT</label>

                                <input type="number"
                                    step="0.001"
                                    id="qty_out"
                                    class="form-control">

                            </div>


                            <div class="col-md-3 mt-4">

                                <div class="input-group">

                                    <input type="text"
                                        id="no_spk"
                                        class="form-control"
                                        readonly>

                                    <button type="button"
                                        id="btnCariSpk"
                                        class="btn btn-primary">
                                        klik? spk
                                    </button>

                                </div>

                            </div>


                            <div class="col-md-2">

                                <label>Po Number</label>

                                <input type="text"
                                    id="no_po"
                                    class="form-control">

                            </div>


                            <div class="col-md-2">

                                <label>No Invoice</label>

                                <input type="text"
                                    id="no_invoice"
                                    class="form-control"
                                    placeholder="No Invoice">

                            </div>


                            <div class="col-md-2">

                                <label>Keterangan</label>

                                <input type="text"
                                    id="keterangan"
                                    class="form-control">

                            </div>


                            <div class="col-md-1">

                                <label>&nbsp;</label>

                                <button type="button"
                                    id="btnTambahTransaksi"
                                    class="btn btn-success btn-block"
                                    style="cursor:pointer;opacity:1">
                                    Save
                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    RIWAYAT TRANSAKSI
                    ===================================================== --}}

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

                                    <th>
                                        Keterangan
                                    </th>

                                    <th>Act</th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach ($transaksi as $item)

                                    @php

                                        $isOpname = stripos(
                                            trim((string) $item->keterangan),
                                            'opname'
                                        ) !== false;

                                    @endphp


                                    <tr class="{{ $isOpname ? 'row-opname' : '' }}">

                                        {{-- TANGGAL --}}
                                        <td>

                                            <a href="#"
                                                class="editable"
                                                data-type="date"
                                                data-name="tanggal"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}"
                                                data-format="yyyy-mm-dd"
                                                data-viewformat="dd/mm/yyyy"
                                                data-value="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}">

                                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}

                                            </a>

                                        </td>


                                        {{-- IN --}}
                                        <td>

                                            @if ($item->tipe == 'in')

                                                <a href="#"
                                                    class="editable"
                                                    data-type="text"
                                                    data-name="qty"
                                                    data-pk="{{ $item->id }}"
                                                    data-url="{{ route('history.updateField', $item->id) }}">

                                                    {{ $item->qty }}

                                                </a>

                                            @endif

                                        </td>


                                        {{-- OUT --}}
                                        <td>

                                            @if ($item->tipe == 'out')

                                                <a href="#"
                                                    class="editable"
                                                    data-type="text"
                                                    data-name="qty"
                                                    data-pk="{{ $item->id }}"
                                                    data-url="{{ route('history.updateField', $item->id) }}">

                                                    {{ $item->qty }}

                                                </a>

                                            @endif

                                        </td>


                                        {{-- SATUAN --}}
                                        <td>

                                            <a href="#"
                                                class="editable"
                                                data-type="text"
                                                data-name="satuan"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ optional($item->stok)->satuan }}

                                            </a>

                                        </td>


                                        {{-- PO --}}
                                        <td>

                                            <a href="#"
                                                class="editable"
                                                data-type="text"
                                                data-name="po"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ $item->po }}

                                            </a>

                                        </td>


                                        {{-- NO INVOICE --}}
                                        <td>

                                            <a href="#"
                                                class="editable"
                                                data-type="text"
                                                data-name="no_invoice"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ $item->no_invoice }}

                                            </a>

                                        </td>


                                        {{-- SPK --}}
                                        <td class="editable-spk"
                                            data-id="{{ $item->id }}"
                                            data-spkid="{{ $item->spk_id }}"
                                            style="cursor:pointer;color:#0d6efd">

                                            {{ optional($item->spk)->data['no_spk'] ?? '-' }}

                                        </td>


                                        {{-- KETERANGAN --}}
                                        <td>

                                            <a href="#"
                                                class="editable"
                                                data-type="textarea"
                                                data-name="keterangan"
                                                data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">

                                                {{ $item->keterangan }}

                                            </a>

                                        </td>


                                        {{-- ACT --}}
                                        <td class="text-center">

                                            @if (strtolower(auth()->user()->name ?? '') === 'sumanti')

                                                <button type="button"
                                                    class="btn btn-danger btn-sm btn-delete-transaksi"
                                                    data-id="{{ $item->id }}"
                                                    title="Hapus transaksi">

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


    {{-- =====================================================
        SPK DRAWER
        ===================================================== --}}

    <div id="spkDrawerOverlay"></div>


    <div id="spkDrawer">

        <div class="drawer-header">

            <h5>
                Cari SPK
            </h5>

            <button type="button"
                id="closeDrawer"
                class="btn btn-danger btn-sm">
                ✕
            </button>

        </div>


        <div class="p-3">

            <input type="text"
                id="searchSpk"
                class="form-control mb-3"
                placeholder="Cari No SPK / Supplier">


            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>No SPK</th>
                            <th>Supplier</th>

                            <th width="80">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody id="spkTableBody">
                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- =====================================================
        SCRIPT
        ===================================================== --}}

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    {{-- =====================================================
        DELETE TRANSAKSI
        ===================================================== --}}

    <script>

        $(document).on('click', '.btn-delete-transaksi', function() {

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


    {{-- =====================================================
        INPUT / SAVE TRANSAKSI
        ===================================================== --}}

    <script>

        function checkSaveButton() {

            const qtyInRaw = $('#qty_in').val();
            const qtyOutRaw = $('#qty_out').val();

            const qtyIn = parseFloat(qtyInRaw);
            const qtyOut = parseFloat(qtyOutRaw);

            const stokTersedia =
                parseFloat($('#stok_tersedia').val());

            const inValue =
                Number.isFinite(qtyIn) ? qtyIn : 0;

            const outValue =
                Number.isFinite(qtyOut) ? qtyOut : 0;

            const stokValue =
                Number.isFinite(stokTersedia)
                    ? stokTersedia
                    : 0;


            $('#warningStok')
                .hide()
                .text('');

            $('#qty_out')
                .removeClass('is-invalid');


            let valid = true;


            /*
             * IN / stock opname:
             * boleh dilakukan walaupun stok negatif.
             *
             * OUT:
             * tidak boleh melebihi stok tersedia.
             */

            if (outValue > 0 && outValue > stokValue) {

                valid = false;

                $('#qty_out')
                    .addClass('is-invalid');

                $('#warningStok')
                    .text(
                        'Stok tersedia hanya ' +
                        stokValue
                    )
                    .show();

            }


            /*
             * SAVE aktif jika:
             * - ada IN > 0
             * - atau ada OUT > 0
             */

            const enable =
                (inValue > 0 || outValue > 0)
                && valid;


            $('#btnTambahTransaksi')
                .prop('disabled', !enable)
                .css({

                    cursor: enable
                        ? 'pointer'
                        : 'not-allowed',

                    opacity: enable
                        ? 1
                        : 0.6

                });

        }


        $(document).on(
            'input change keyup',
            '#qty_in, #qty_out',
            function() {

                checkSaveButton();

            }
        );


        $(document).ready(function() {

            setTimeout(function() {

                checkSaveButton();

            }, 50);

        });


        // SAVE TRANSAKSI

        $(document).on(
            'click',
            '#btnTambahTransaksi',
            function() {

                const qtyIn =
                    parseFloat($('#qty_in').val()) || 0;

                const qtyOut =
                    parseFloat($('#qty_out').val()) || 0;

                const stokTersedia =
                    parseFloat($('#stok_tersedia').val()) || 0;


                if (qtyIn <= 0 && qtyOut <= 0) {
                    return;
                }


                if (
                    qtyOut > 0 &&
                    qtyOut > stokTersedia
                ) {

                    $('#qty_out')
                        .addClass('is-invalid');

                    $('#warningStok')
                        .text(
                            'Stok tersedia hanya ' +
                            stokTersedia
                        )
                        .show();

                    checkSaveButton();

                    return;

                }


                $.ajax({

                    url: '/laporan/transaksi/store',

                    type: 'POST',

                    data: {

                        _token: '{{ csrf_token() }}',

                        stok_id:
                            $('#stok_id').val(),

                        tanggal:
                            $('#tanggal').val(),

                        in:
                            $('#qty_in').val(),

                        out:
                            $('#qty_out').val(),

                        po:
                            $('#no_po').val(),

                        spk_id:
                            $('#spk_id').val(),

                        keterangan:
                            $('#keterangan').val(),

                        no_invoice:
                            $('#no_invoice').val()

                    },

                    success: function() {

                        location.reload();

                    }

                });

            }

        );

    </script>


    {{-- =====================================================
        SPK INFO
        ===================================================== --}}

    <script>

        $(document).on(
            'click',
            '#closeSpkInfo',
            function() {

                $('#spkInfo').slideUp();

            }
        );

    </script>


    {{-- =====================================================
        PUSHER REMOTE CURSOR
        ===================================================== --}}

    <style>

        #stokRemoteCursors {

            position: fixed;
            inset: 0;

            pointer-events: none;

            z-index: 9999999;

        }


        .stok-remote-cursor {

            position: fixed;

            pointer-events: none;

            transform:
                translate(-1px, -1px);

            transition:
                left 90ms linear,
                top 90ms linear;

            will-change:
                left,
                top;

        }


        .stok-remote-cursor-arrow {

            width: 0;
            height: 0;

            border-top:
                0 solid transparent;

            border-bottom:
                15px solid transparent;

            border-left:
                11px solid #2563eb;

            transform:
                rotate(-42deg);

            filter:
                drop-shadow(
                    0 1px 1px rgba(0, 0, 0, .25)
                );

        }


        .stok-remote-cursor-name {

            position: absolute;

            left: 9px;
            top: 12px;

            padding:
                3px 7px;

            border-radius: 4px;

            background: #2563eb;
            color: #fff;

            font-size: 10px;
            font-weight: 700;

            line-height: 1.2;

            white-space: nowrap;

            box-shadow:
                0 2px 5px
                rgba(0, 0, 0, .18);

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

            background:
                #fff8d6;

        }


        #spkDrawerOverlay {

            position: fixed;

            inset: 0;

            background:
                rgba(0, 0, 0, .4);

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

            box-shadow:
                -5px 0 20px
                rgba(0, 0, 0, .15);

        }


        #spkDrawer.show {
            right: 0;
        }


        .drawer-header {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            padding: 15px;

            border-bottom:
                1px solid #ddd;

            position: sticky;

            top: 0;

            background: #fff;

            z-index: 10;

        }

    </style>


    {{-- =====================================================
        PUSHER SCRIPT
        ===================================================== --}}

    <script>

        (function() {

            'use strict';


            const stokId =
                @json($stok->id);


            if (!stokId) {

                console.warn(
                    '[STOK Cursor] stok_id tidak tersedia.'
                );

                return;

            }


            const currentUserId =
                @json(auth()->id());


            const currentUserName =
                @json(auth()->user()->name ?? 'User');


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


            const pusher =
                new Pusher(

                    pusherKey,

                    {

                        cluster:
                            pusherCluster || 'ap1',

                        forceTLS:
                            true,

                        authEndpoint:
                            @json(route('pusher.auth')),

                        auth: {

                            headers: {

                                'X-CSRF-TOKEN':
                                    document
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


            const channelName =
                'presence-stok-' + stokId;


            const channel =
                pusher.subscribe(channelName);


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


            const remoteCursors = {};


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


                const arrow =
                    document.createElement('div');

                arrow.className =
                    'stok-remote-cursor-arrow';


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

                    element:
                        cursor,

                    lastMove:
                        Date.now(),

                    x: 0,

                    y: 0

                };


                return cursor;

            }


            function removeCursor(userId) {

                const data =
                    remoteCursors[userId];


                if (!data) {
                    return;
                }


                data.element.remove();


                delete remoteCursors[userId];

            }


            function updateCursor(data) {

                if (!data) {
                    return;
                }


                const userId =
                    String(data.user_id);


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


                    try {

                        channel.trigger(
                            'client-stok-cursor',
                            {

                                user_id:
                                    currentUserId,

                                name:
                                    currentUserName,

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

                },
                {
                    passive: true
                }
            );


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


            channel.bind(
                'pusher:member_removed',
                function(member) {

                    removeCursor(
                        String(member.id)
                    );

                }
            );


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


    {{-- =====================================================
        EDITABLE + SPK
        ===================================================== --}}

    <script>

        $(document).ready(function() {

            console.log('Init Editable');

            $.fn.editable.defaults.mode =
                'inline';


            $('.editable').editable({

                ajaxOptions: {

                    type: 'POST',

                    dataType: 'json'

                },


                success: function(response) {

                    if (
                        response.status !==
                        'success'
                    ) {

                        return response.msg ||
                            'Update failed.';

                    }

                }

            });

        });


        let spkSearchTimer = null;

        let spkRequest = null;

        let spkCache = {};

        let currentHistoryId = null;


        // =====================================================
        // TAMPILKAN DETAIL SPK
        // =====================================================

        function tampilkanDetailSpk(data) {

            console.log(
                'DATA SPK DIPILIH:',
                data
            );


            let itemRows = '';


            const items =
                Array.isArray(data?.items)
                    ? data.items
                    : [];


            if (items.length === 0) {

                itemRows = `

                    <tr>

                        <td colspan="3"
                            class="text-center text-muted"
                            style="padding:15px">

                            Tidak ada detail item pada SPK ini

                        </td>

                    </tr>

                `;

            } else {

                items.forEach(
                    function(item) {

                        itemRows += `

                            <tr>

                                <td>
                                    ${item?.kode ?? '-'}
                                </td>

                                <td>
                                    ${item?.nama ?? '-'}
                                </td>

                                <td>
                                    ${item?.qty ?? 0}
                                </td>

                            </tr>

                        `;

                    }
                );

            }


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


                    <h4>
                        ${data?.no_spk ?? '-'}
                    </h4>


                    <div>

                        Supplier :
                        ${data?.supplier ?? '-'}

                    </div>


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


        // =====================================================
        // LOAD SPK
        // =====================================================

        function loadSpk(q = '') {

            if (spkRequest) {

                spkRequest.abort();

                spkRequest = null;

            }


            $('#spkTableBody').html(`

                <tr>

                    <td colspan="3"
                        class="text-center text-muted"
                        style="padding:20px">

                        <i class="fa fa-spinner fa-spin"></i>

                        Mencari SPK...

                    </td>

                </tr>

            `);


            spkRequest = $.ajax({

                url: '/spk/search-spk',

                type: 'GET',

                data: {
                    q: q
                },


                success: function(res) {

                    console.log(
                        'HASIL SEARCH SPK:',
                        res
                    );


                    let html = '';


                    spkCache = {};


                    if (
                        !Array.isArray(res) ||
                        res.length === 0
                    ) {

                        html = `

                            <tr>

                                <td colspan="3"
                                    class="text-center text-muted"
                                    style="padding:20px">

                                    SPK tidak ditemukan

                                </td>

                            </tr>

                        `;

                    } else {

                        res.forEach(
                            function(item) {

                                spkCache[
                                    String(item.id)
                                ] = item;


                                if (
                                    !Array.isArray(
                                        item.items
                                    )
                                ) {

                                    item.items = [];

                                }


                                html += `

                                    <tr>

                                        <td>
                                            ${escapeHtml(
                                                item.no_spk ?? '-'
                                            )}
                                        </td>

                                        <td>
                                            ${escapeHtml(
                                                item.supplier ?? '-'
                                            )}
                                        </td>

                                        <td>

                                            <button
                                                type="button"
                                                class="btn btn-success btn-xs pilih-spk"
                                                data-spk-id="${item.id}">

                                                Pilih

                                            </button>

                                        </td>

                                    </tr>

                                `;

                            }
                        );

                    }


                    $('#spkTableBody')
                        .html(html);

                },


                error: function(xhr, status) {

                    if (status === 'abort') {
                        return;
                    }


                    console.error(
                        'Gagal mencari SPK:',
                        xhr
                    );


                    $('#spkTableBody').html(`

                        <tr>

                            <td colspan="3"
                                class="text-center text-danger"
                                style="padding:20px">

                                Gagal mengambil data SPK

                            </td>

                        </tr>

                    `);

                },


                complete: function() {

                    spkRequest = null;

                }

            });

        }


        function escapeHtml(value) {

            return $('<div>')
                .text(value ?? '')
                .html();

        }


        // =====================================================
        // BUKA DRAWER
        // =====================================================

        $(document).on(
            'click',
            '#btnCariSpk',
            function() {

                $('#spkDrawer')
                    .addClass('show');

                $('#spkDrawerOverlay')
                    .show();

                $('#searchSpk')
                    .val('');

                loadSpk();


                setTimeout(
                    function() {

                        $('#searchSpk')
                            .focus();

                    },
                    100
                );

            }
        );


        // =====================================================
        // CLOSE DRAWER
        // =====================================================

        $(document).on(
            'click',
            '#closeDrawer, #spkDrawerOverlay',
            function() {

                $('#spkDrawer')
                    .removeClass('show');

                $('#spkDrawerOverlay')
                    .hide();

            }
        );


        // =====================================================
        // SEARCH SPK
        // =====================================================

        $(document).on(
            'input',
            '#searchSpk',
            function() {

                const q =
                    $(this)
                    .val()
                    .trim();


                clearTimeout(
                    spkSearchTimer
                );


                spkSearchTimer =
                    setTimeout(
                        function() {

                            loadSpk(q);

                        },
                        300
                    );

            }
        );


        // =====================================================
        // PILIH SPK
        // =====================================================

        $(document).on(
            'click',
            '.pilih-spk',
            function(e) {

                e.preventDefault();


                const spkId =
                    String(
                        $(this)
                            .attr(
                                'data-spk-id'
                            )
                    );


                const data =
                    spkCache[spkId];


                console.log(
                    'SPK ID:',
                    spkId
                );


                console.log(
                    'DATA SPK DIPILIH:',
                    data
                );


                if (!data) {

                    console.error(
                        'Data SPK tidak ditemukan di cache:',
                        spkId
                    );

                    return;

                }


                // =================================================
                // EDIT HISTORY
                // =================================================

                if (currentHistoryId) {

                    $.ajax({

                        url:
                            '/history/update-spk/' +
                            currentHistoryId,

                        type: 'POST',

                        data: {

                            _token:
                                '{{ csrf_token() }}',

                            spk_id:
                                data.id

                        },


                        success: function() {

                            $(
                                'td.editable-spk[data-id="' +
                                currentHistoryId +
                                '"]'
                            )
                            .text(
                                data.no_spk ?? '-'
                            );


                            $(
                                'td.editable-spk[data-id="' +
                                currentHistoryId +
                                '"]'
                            )
                            .attr(
                                'data-spkid',
                                data.id
                            );


                            currentHistoryId =
                                null;


                            $('#spkDrawer')
                                .removeClass(
                                    'show'
                                );


                            $('#spkDrawerOverlay')
                                .hide();

                        }

                    });


                    return;

                }


                // =================================================
                // FORM INPUT
                // =================================================

                $('#spk_id')
                    .val(data.id ?? '');


                $('#no_spk')
                    .val(data.no_spk ?? '');


                // =================================================
                // DETAIL SPK
                // =================================================

                tampilkanDetailSpk(data);


                // =================================================
                // CLOSE DRAWER
                // =================================================

                $('#spkDrawer')
                    .removeClass('show');

                $('#spkDrawerOverlay')
                    .hide();

            }
        );


        // =====================================================
        // EDIT SPK HISTORY
        // =====================================================

        $(document).on(
            'click',
            '.editable-spk',
            function() {

                currentHistoryId =
                    $(this).data('id');


                $('#spkDrawer')
                    .addClass('show');

                $('#spkDrawerOverlay')
                    .show();


                $('#searchSpk')
                    .val('');


                loadSpk();


                setTimeout(
                    function() {

                        $('#searchSpk')
                            .focus();

                    },
                    100
                );

            }
        );


        // =====================================================
        // CLOSE DETAIL SPK
        // =====================================================

        $(document).on(
            'click',
            '#closeSpkInfo',
            function() {

                $('#spkInfo')
                    .slideUp();

            }
        );

    </script>


    {{-- =====================================================
        OPNAME ROW
        ===================================================== --}}

    <style>

        .row-opname {
            display: none !important;
        }


        .row-opname.opname-visible {
            display: table-row !important;
        }

    </style>


    <script>

        (function() {

            function toggleOpnameRows() {

                const rows =
                    document.querySelectorAll(
                        '.row-opname'
                    );


                if (!rows.length) {

                    console.log(
                        '[OPNAME] Tidak ada baris opname.'
                    );

                    return;

                }


                const visible =
                    document.body.getAttribute(
                        'data-opname-visible'
                    ) === '1';


                rows.forEach(
                    function(row) {

                        if (visible) {

                            row.classList.remove(
                                'opname-visible'
                            );

                        } else {

                            row.classList.add(
                                'opname-visible'
                            );

                        }

                    }
                );


                document.body.setAttribute(
                    'data-opname-visible',
                    visible ? '0' : '1'
                );


                console.log(
                    '[OPNAME]',
                    visible
                        ? 'HIDDEN'
                        : 'SHOW',
                    'jumlah:',
                    rows.length
                );

            }


            /*
             * Ctrl + Alt + O
             */

            document.addEventListener(
                'keydown',
                function(event) {

                    if (

                        event.ctrlKey &&
                        event.altKey &&
                        event.key.toLowerCase() === 'o'

                    ) {

                        event.preventDefault();

                        event.stopPropagation();

                        toggleOpnameRows();

                    }

                },
                true
            );

        })();

    </script>


@endsection