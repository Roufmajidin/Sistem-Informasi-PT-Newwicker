@extends('master.master')



@section('title', 'Detail Barang')



@section('content')



    <div class="padding detail-stock-page">

        <div id="stokRemoteCursors"></div>



        <div class="box detail-stock-shell">



        @section('btn')
            <div class="box-header d-flex justify-content-between align-items-center detail-stock-header">

                <h2>

                    <span class="detail-page-title">Detail & Mutasi Stok</span>
                    <small class="detail-page-subtitle">{{ $stok->nama_barang }} &middot; {{ $stok->kode_barang }}</small>

                </h2>



                <a href="{{ url('/laporan') }}" class="btn btn-secondary">

                    Kembali

                </a>

            </div>
        @endsection



        <div class="card mb-3 detail-filter-card">

            <div class="card-header">

                <i class="fa fa-calendar-o"></i> Filter Riwayat Transaksi

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



        <div class="box-body detail-stock-body">
            <div class="detail-overview-layout">
                <div class="detail-overview-primary">



                    {{-- =====================================================

                    PERHITUNGAN STOK

                    ===================================================== --}}

                    @php

                        /*

                                             * Saldo dihitung di LaporanController::detailBarang().

                                             * Jangan hitung ulang di Blade agar hasilnya konsisten

                                             * dengan nilai stokTersedia dari controller.

                                             *

                                             * Filter tanggal hanya mengubah riwayat transaksi.

                     */

                        $stokTersedia = (float) ($stokTersedia ?? 0);

                    @endphp





                    {{-- =====================================================

                    INFO STOK

                    ===================================================== --}}



                    <div class="alert alert-info detail-balance-card">



                        <b class="detail-balance-label">STOK TERSEDIA SAAT INI</b>



                        {{ rtrim(rtrim(number_format((float) $stokTersedia, 3, ',', '.'), '0'), ',') }}



                        {{ $stok->satuan }}



                    </div>





                    {{-- INFORMASI SALDO PEMBUKAAN DAN CUT-OFF OPNAME --}}
                    <div class="alert alert-light border mb-3 detail-opname-card"
                        style="border-left:4px solid #2563eb !important;">
                        <div class="row">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted d-block">Stok Awal / Hasil Opname</small>
                                <strong>{{ rtrim(rtrim(number_format((float) ($stok->stok_awal ?? 0), 3, ',', '.'), '0'), ',') }}
                                    {{ $stok->satuan }}</strong>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <small class="text-muted d-block">Status Opname</small>
                                @if ($stok->is_opname && $stok->opname_at)
                                    <span class="badge badge-success">Sudah Opname</span>
                                @else
                                    <span class="badge badge-secondary">Belum Opname</span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Cut-off Perhitungan</small>
                                <strong>{{ $stok->is_opname && $stok->opname_at ? \Carbon\Carbon::parse($stok->opname_at)->format('d/m/Y H:i:s') : 'Semua transaksi' }}</strong>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Saldo dihitung dari stok awal ditambah IN dan dikurangi
                            OUT setelah cut-off. Filter tanggal di atas hanya memengaruhi riwayat.</small>
                    </div>

                </div>
                <div class="detail-overview-secondary">
                    <div class="row mb-3 detail-item-grid">



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



                            <input type="text" class="form-control"
                                value="{{ number_format($stok->harga, 0, ',', '.') }}" readonly>



                        </div>



                    </div>
                </div>
            </div>

            <div id="spkInfo"></div>





            {{-- =====================================================

                    INPUT TRANSAKSI

                    ===================================================== --}}



            <div class="card mb-4 detail-input-card">



                <div class="card-header">

                    <i class="fa fa-exchange"></i> Input Transaksi <small class="detail-section-note">Catat stok masuk
                        dan keluar</small>

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

                                    Cari SPK

                                </button>



                            </div>



                        </div>





                        <div class="col-md-2">



                            <label>Po Number</label>



                            <input type="text" id="no_po" class="form-control">



                        </div>





                        <div class="col-md-2">



                            <label>No Invoice</label>



                            <input type="text" id="no_invoice" class="form-control" placeholder="No Invoice">



                        </div>





                        <div class="col-md-2">



                            <label>Keterangan</label>



                            <input type="text" id="keterangan" class="form-control">



                        </div>





                        <div class="col-md-1">



                            <label>&nbsp;</label>



                            <button type="button" id="btnTambahTransaksi" class="btn btn-success btn-block"
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



            <div class="card detail-history-card" id="riwayatTransaksiSection">

                <div class="card-header">

                    <i class="fa fa-history"></i> Riwayat Transaksi

                </div>



                <div class="card-body">



                    <div class="detail-table-scroll">
                        <table class="table detail-history-table">



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
                                @php
                                    // Pembatas visual Stock Opname.
                                    // Perhitungan saldo tetap dilakukan di controller.
                                    $showOpnameDivider = (bool) $stok->is_opname && !empty($stok->opname_at);

                                    $opnameCutoff = $showOpnameDivider ? \Carbon\Carbon::parse($stok->opname_at) : null;

                                    $opnameDividerInserted = false;

                                    // Hasil opname 0 harus tetap tampil sebagai 0.
                                    $opnameQty = (float) ($stok->stok_awal ?? 0);

                                    $opnameLabelQty =
                                        $opnameQty == 0
                                            ? '0'
                                            : rtrim(rtrim(number_format($opnameQty, 3, ',', '.'), '0'), ',');
                                @endphp

                                @foreach ($transaksi as $item)
                                    @php
                                        /*
            |--------------------------------------------------------------------------
            | POSISI PEMBATAS STOCK OPNAME
            |--------------------------------------------------------------------------
            | Riwayat diurutkan berdasarkan tanggal DESC.
            |
            | Transaksi pada tanggal yang sama dengan opname
            | dianggap sebagai transaksi sebelum cut-off.
            |
            | Contoh:
            | Opname     : 26/10/2026 17:18
            | Transaksi  : 26/10/2026
            |
            | Pembatas ditampilkan DI ATAS transaksi tersebut.
            */

                                        $olderThanOpname =
                                            $showOpnameDivider &&
                                            \Carbon\Carbon::parse($item->tanggal)->toDateString() <=
                                                $opnameCutoff->toDateString();

                                        $isOpname = stripos(trim((string) $item->keterangan), 'opname') !== false;
                                    @endphp

                                    @if ($olderThanOpname && !$opnameDividerInserted)
                                        <tr class="stok-opname-divider">
                                            <td colspan="9">
                                                <i class="fa fa-clipboard-check" aria-hidden="true"></i>

                                                <strong>BATAS STOCK OPNAME</strong>

                                                <span>
                                                    {{ $opnameCutoff->format('d/m/Y H:i') }}
                                                </span>

                                                <span>
                                                    Stok hasil opname:
                                                    <strong>
                                                        {{ $opnameLabelQty }}
                                                        {{ $stok->satuan }}
                                                    </strong>
                                                </span>
                                            </td>
                                        </tr>

                                        @php
                                            $opnameDividerInserted = true;
                                        @endphp
                                    @endif

                                    <tr class="{{ $isOpname ? 'row-opname' : '' }}">

                                        {{-- TANGGAL --}}
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

                                        {{-- NO INVOICE --}}
                                        <td>
                                            <a href="#" class="editable" data-type="text"
                                                data-name="no_invoice" data-pk="{{ $item->id }}"
                                                data-url="{{ route('history.updateField', $item->id) }}">
                                                {{ $item->no_invoice }}
                                            </a>
                                        </td>

                                        {{-- SPK --}}
                                        <td class="editable-spk" data-id="{{ $item->id }}"
                                            data-spkid="{{ $item->spk_id }}" style="cursor:pointer;color:#0d6efd">
                                            {{ optional($item->spk)->data['no_spk'] ?? '-' }}
                                        </td>

                                        {{-- KETERANGAN --}}
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
                                                <button type="button"
                                                    class="btn btn-danger btn-sm btn-delete-transaksi"
                                                    data-id="{{ $item->id }}" title="Hapus transaksi">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach

                                {{-- Jika tidak ada transaksi lama dalam hasil filter,
         pembatas tetap ditampilkan di akhir tabel. --}}
                                @if ($showOpnameDivider && !$opnameDividerInserted)
                                    <tr class="stok-opname-divider">
                                        <td colspan="9">
                                            <i class="fa fa-clipboard-check" aria-hidden="true"></i>

                                            <strong>BATAS STOCK OPNAME</strong>

                                            <span>
                                                {{ $opnameCutoff->format('d/m/Y H:i') }}
                                            </span>

                                            <span>
                                                Stok hasil opname:
                                                <strong>
                                                    {{ $opnameLabelQty }}
                                                    {{ $stok->satuan }}
                                                </strong>
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>




                        </table>
                    </div>



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

            ?
            stokTersedia

            :
            0;





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

            &&
            valid;





        $('#btnTambahTransaksi')

            .prop('disabled', !enable)

            .css({



                cursor: enable

                    ?
                    'pointer'

                    :
                    'not-allowed',



                opacity: enable

                    ?
                    1

                    :
                    0.6



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

            drop-shadow(0 1px 1px rgba(0, 0, 0, .25));



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



        *max-width*: 90vw;



        height: 100vh;



        background: #fff;



        z-index: 9999;



        transition: .3s;



        overflow-y: auto;



        box-shadow:

            -5px 0 20px rgba(0, 0, 0, .15);



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

            ?
            data.items

            :
            [];





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

                ?
                'HIDDEN'

                :
                'SHOW',

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






<style>
    /* Scoped layout refresh: does not alter selectors/handlers used by existing JS. */
    .detail-stock-page {
        --detail-blue: #1749a5;
        --detail-border: #e2e8f0;
        --detail-ink: #172b4d;
        --detail-muted: #64748b;
        background: #f4f7fb;
        padding: 24px !important;
        min-height: 100vh;
    }

    .detail-stock-page .detail-stock-shell {
        max-width: 1600px;
        margin: 0 auto;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    .detail-stock-page .detail-stock-header {
        background: transparent;
        border: 0;
        padding: 8px 2px 22px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .detail-stock-header h2 {
        display: flex;
        flex-direction: column;
        margin: 0;
        line-height: 1.3;
    }

    .detail-eyebrow {
        font-size: 11px;
        letter-spacing: .13em;
        font-weight: 800;
        color: #59769d;
        margin-bottom: 5px;
    }

    .detail-page-title {
        font-size: 26px;
        font-weight: 800;
        color: #142b4c;
    }

    .detail-page-subtitle {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-top: 5px;
    }

    .detail-stock-page .card {
        border: 1px solid var(--detail-border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(27, 55, 94, .045);
        overflow: hidden;
    }

    .detail-stock-page .card-header {
        background: #fff;
        color: #193454;
        font-size: 15px;
        font-weight: 750;
        padding: 17px 22px;
        border-bottom: 1px solid #e8edf5;
    }

    .detail-stock-page .card-body {
        padding: 22px;
    }

    .detail-stock-page .detail-stock-body {
        padding: 0;
    }

    .detail-stock-page label {
        display: block;
        color: #415570;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .detail-stock-page .form-control {
        width: 100%;
        min-width: 0;
        height: 42px;
        border-radius: 9px;
        border: 1px solid #d6dfeb;
        box-shadow: none;
        padding: 9px 12px;
        font-size: 13px;
        background: #fff;
    }

    .detail-stock-page .form-control:focus {
        border-color: #5c92ec;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .11);
    }

    .detail-stock-page input[readonly] {
        background: #f8fafc;
        color: #344966;
    }

    .detail-stock-page .btn {
        border-radius: 9px;
        font-weight: 650;
        padding: 10px 15px;
        white-space: nowrap;
    }

    .detail-stock-page .btn-primary {
        background: #2059b8;
        border-color: #2059b8;
    }

    .detail-stock-page .btn-success {
        background: #11815d;
        border-color: #11815d;
    }

    .detail-stock-page .detail-filter-card .row {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) minmax(320px, 1.5fr);
        gap: 16px;
        align-items: end;
        margin: 0;
    }

    .detail-stock-page .detail-filter-card .row>div {
        width: auto;
        max-width: none;
        flex: none;
        padding: 0;
    }

    .detail-stock-page .detail-filter-card .row>div:last-child>div {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .detail-stock-page .detail-balance-card {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 16px;
        padding: 22px 26px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(105deg, #163d79, #2468c2);
        color: #fff;
        font-size: 30px;
        font-weight: 800;
        box-shadow: 0 8px 22px rgba(32, 89, 184, .14);
    }

    .detail-stock-page .detail-balance-card .detail-balance-label {
        display: block;
        flex-basis: 100%;
        font-size: 11px;
        letter-spacing: .12em;
        font-weight: 700;
        opacity: .84;
    }

    .detail-stock-page .detail-opname-card {
        padding: 19px 22px;
        border-radius: 13px;
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-left: 4px solid #3b82f6 !important;
    }

    .detail-stock-page .detail-opname-card .row {
        row-gap: 12px;
    }

    .detail-stock-page .detail-opname-card strong {
        color: #193454;
        font-size: 15px;
    }

    .detail-stock-page .detail-item-grid {
        display: grid;
        grid-template-columns: 1.1fr 2fr 1.2fr .8fr 1fr;
        gap: 14px;
        margin: 0 0 24px !important;
    }

    .detail-stock-page .detail-item-grid>div {
        width: auto;
        max-width: none;
        flex: none;
        padding: 0;
    }

    .detail-stock-page hr {
        border-top: 1px solid #e5ebf2;
        margin: 20px 0;
    }

    .detail-stock-page .detail-input-card .card-body>.row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
        margin: 0;
        align-items: end;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div {
        width: auto;
        max-width: none;
        flex: none;
        padding: 0;
        margin: 0 !important;
        grid-column: span 3;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(4) {
        grid-column: span 3;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7) {
        grid-column: span 5;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
        grid-column: span 2;
    }

    .detail-stock-page .detail-input-card .input-group {
        display: flex;
        width: 100%;
        flex-wrap: nowrap;
    }

    .detail-stock-page .detail-input-card .input-group .form-control {
        flex: 1;
        width: 1%;
    }

    .detail-stock-page #btnTambahTransaksi {
        height: 42px;
        width: 100%;
    }

    .detail-stock-page #warningStok {
        display: block;
        font-size: 12px;
        margin-top: 6px;
    }

    .detail-stock-page .detail-history-card .card-body {
        padding: 0;
    }

    .detail-stock-page .detail-table-scroll {
        overflow-x: auto;
        width: 100%;
        scrollbar-width: thin;
    }

    .detail-stock-page .detail-history-card table {
        margin: 0;
        min-width: 1050px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .detail-stock-page .detail-history-card th {
        background: #f4f7fc;
        color: #526783;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .045em;
        padding: 14px 13px;
        white-space: nowrap;
        border-top: 0;
        border-bottom: 1px solid #e0e7f0;
    }

    .detail-stock-page .detail-history-card td {
        padding: 13px;
        font-size: 13px;
        vertical-align: middle;
        border-color: #edf1f6;
        color: #253a55;
    }

    .detail-stock-page .detail-history-card tbody tr:hover {
        background: #f4f8ff;
    }

    .detail-stock-page .detail-history-card td a {
        color: #2059b8;
    }

    .detail-stock-page .detail-history-card .btn-sm {
        padding: 6px 10px;
    }

    @media(max-width:1100px) {
        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: 1/-1;
        }

        .detail-stock-page .detail-item-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .detail-stock-page .detail-input-card .card-body>.row>div {
            grid-column: span 4;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7) {
            grid-column: span 8;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
            grid-column: span 4;
        }
    }

    @media(max-width:650px) {
        .detail-stock-page {
            padding: 12px !important;
        }

        .detail-page-title {
            font-size: 22px;
        }

        .detail-stock-page .card-body {
            padding: 16px;
        }

        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: 1fr;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: auto;
        }

        .detail-stock-page .detail-item-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .detail-stock-page .detail-item-grid>div:nth-child(2) {
            grid-column: span 2;
            grid-row: 1;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div {
            grid-column: span 6;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(4),
        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7) {
            grid-column: span 12;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
            grid-column: span 12;
        }

        .detail-stock-page .detail-balance-card {
            font-size: 25px;
        }
    }

    /* DETAIL BARANG UI V2 — only presentation; existing JS hooks remain untouched */
    .detail-stock-page {
        --detail-ink: #20334e;
        --detail-blue: #2159ad;
        background: #f3f6fb !important;
        padding: 24px 30px !important;
    }

    .detail-stock-page .detail-stock-shell {
        max-width: 1750px;
        width: 100%;
    }

    .detail-stock-page .detail-stock-header {
        padding: 6px 4px 24px;
    }

    .detail-page-title {
        font-size: 27px;
        letter-spacing: -.5px;
    }

    .detail-stock-page .detail-filter-card {
        margin-bottom: 20px !important;
    }

    .detail-stock-page .detail-filter-card .card-header {
        padding: 15px 24px;
    }

    .detail-stock-page .detail-filter-card .card-body {
        padding: 19px 24px 23px;
    }

    .detail-stock-page .detail-filter-card .row {
        display: grid;
        grid-template-columns: minmax(190px, 1fr) minmax(190px, 1fr) minmax(340px, 1.25fr);
        gap: 18px;
        align-items: end;
    }

    .detail-stock-page .detail-filter-card .row>div:last-child>div {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-stock-page .detail-filter-card .row .btn {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .detail-stock-page .detail-filter-card .btn-warning {
        background: #f1f5fa;
        border-color: #dce4ee;
        color: #35516f;
    }

    .detail-stock-page .detail-filter-card .btn-danger {
        background: #fff0f0;
        border-color: #ffd5d5;
        color: #b33b3b;
    }

    .detail-stock-page .detail-overview-layout {
        display: grid;
        grid-template-columns: minmax(290px, .88fr) minmax(0, 1.5fr);
        gap: 18px;
        margin-bottom: 22px;
        align-items: stretch;
    }

    .detail-stock-page .detail-overview-primary {
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-width: 0;
    }

    .detail-stock-page .detail-balance-card {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        min-height: 138px;
        margin: 0;
        padding: 23px 27px;
        font-size: 35px;
        line-height: 1.25;
        gap: 12px;
    }

    .detail-stock-page .detail-balance-card .detail-balance-label {
        flex-basis: auto;
        font-size: 11px;
    }

    .detail-stock-page .detail-opname-card {
        margin: 0 !important;
        padding: 19px 23px;
    }

    .detail-stock-page .detail-opname-card .row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 13px;
        margin: 0;
    }

    .detail-stock-page .detail-opname-card .row>div {
        max-width: none;
        width: auto;
        flex: none;
        padding: 0;
    }

    .detail-stock-page .detail-opname-card .row>div:last-child {
        grid-column: 1/-1;
        padding-top: 11px;
        border-top: 1px solid #edf0f5;
    }

    .detail-stock-page .detail-opname-card small {
        line-height: 1.5;
    }

    .detail-stock-page .detail-overview-secondary {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(27, 55, 94, .045);
        padding: 22px;
        min-width: 0;
    }

    .detail-stock-page .detail-overview-secondary::before {
        content: 'INFORMASI BARANG';
        display: block;
        color: #526783;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .1em;
        margin-bottom: 17px;
    }

    .detail-stock-page .detail-item-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
        margin: 0 !important;
    }

    .detail-stock-page .detail-item-grid>div {
        min-width: 0;
    }

    .detail-stock-page .detail-item-grid>div:nth-child(2) {
        grid-column: 1/-1;
        grid-row: 1;
    }

    .detail-stock-page .detail-item-grid .form-control {
        height: 46px;
        font-weight: 600;
    }

    .detail-stock-page .detail-input-card .card-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-stock-page .detail-section-note {
        font-size: 12px;
        font-weight: 400;
        color: #8090a5;
        margin-left: auto;
    }

    .detail-stock-page .detail-input-card .card-body {
        padding: 24px;
    }

    .detail-stock-page .detail-input-card .card-body>.row {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 19px 16px;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div {
        grid-column: span 3;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(4) {
        grid-column: span 3;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7) {
        grid-column: span 6;
    }

    .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
        grid-column: span 3;
    }

    .detail-stock-page .detail-input-card .form-control {
        height: 46px;
    }

    .detail-stock-page #btnTambahTransaksi {
        height: 46px;
    }

    .detail-stock-page .detail-history-card .card-header {
        padding: 19px 24px;
    }

    .detail-stock-page .detail-history-card td {
        padding: 15px 14px;
    }

    @media(max-width:1250px) {
        .detail-stock-page .detail-overview-layout {
            grid-template-columns: 1fr;
        }

        .detail-stock-page .detail-overview-primary {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .detail-stock-page .detail-balance-card {
            min-height: 180px;
        }

        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: 1fr 1fr;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: 1/-1;
        }
    }

    @media(max-width:760px) {
        .detail-stock-page {
            padding: 12px !important;
        }

        .detail-stock-page .detail-overview-primary {
            display: flex;
        }

        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: 1fr;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: auto;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child>div {
            flex-wrap: wrap;
        }

        .detail-stock-page .detail-filter-card .row .btn {
            flex: 1;
        }

        .detail-stock-page .detail-item-grid {
            grid-template-columns: 1fr 1fr;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div {
            grid-column: span 6;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(4),
        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7),
        .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
            grid-column: span 12;
        }

        .detail-stock-page .detail-section-note {
            display: none;
        }
    }

    /* Compact density override — layout only, preserve all existing JS hooks. */
    .detail-stock-page {
        padding: 10px 14px !important;
    }

    .detail-stock-page .detail-stock-shell {
        max-width: 1750px;
    }

    .detail-stock-page .detail-stock-header {
        padding: 2px 2px 10px !important;
        gap: 10px;
    }

    .detail-page-title {
        font-size: 21px;
    }

    .detail-page-subtitle {
        margin-top: 2px;
        font-size: 12px;
    }

    .detail-eyebrow {
        margin-bottom: 2px;
    }

    .detail-stock-page .card {
        border-radius: 9px;
        box-shadow: 0 2px 9px rgba(27, 55, 94, .035);
    }

    .detail-stock-page .card-header {
        padding: 9px 14px !important;
        font-size: 14px;
    }

    .detail-stock-page .card-body {
        padding: 12px 14px !important;
    }

    .detail-stock-page .detail-filter-card {
        margin-bottom: 10px !important;
    }

    .detail-stock-page .detail-filter-card .card-header {
        padding: 9px 14px !important;
    }

    .detail-stock-page .detail-filter-card .card-body {
        padding: 10px 14px !important;
    }

    .detail-stock-page .detail-filter-card .row {
        gap: 10px;
        grid-template-columns: minmax(160px, 1fr) minmax(160px, 1fr) minmax(320px, 1.35fr);
    }

    .detail-stock-page .detail-filter-card .row>div:last-child>div {
        gap: 6px;
    }

    .detail-stock-page .detail-filter-card .row .btn {
        min-height: 34px;
    }

    .detail-stock-page .detail-overview-layout {
        gap: 10px;
        margin-bottom: 10px;
        grid-template-columns: minmax(280px, .88fr) minmax(0, 1.5fr);
    }

    .detail-stock-page .detail-overview-primary {
        gap: 8px;
    }

    .detail-stock-page .detail-balance-card {
        min-height: 86px;
        padding: 12px 16px;
        font-size: 27px;
        gap: 4px;
        border-radius: 9px;
    }

    .detail-stock-page .detail-opname-card {
        padding: 10px 14px;
        border-radius: 9px;
    }

    .detail-stock-page .detail-opname-card .row {
        gap: 6px 10px;
    }

    .detail-stock-page .detail-opname-card .row>div:last-child {
        padding-top: 6px;
    }

    .detail-stock-page .detail-opname-card strong {
        font-size: 13px;
    }

    .detail-stock-page .detail-opname-card small {
        font-size: 11px;
        line-height: 1.3;
    }

    .detail-stock-page .detail-overview-secondary {
        padding: 12px 14px;
        border-radius: 9px;
    }

    .detail-stock-page .detail-overview-secondary::before {
        margin-bottom: 9px;
    }

    .detail-stock-page .detail-item-grid {
        gap: 9px 12px;
    }

    .detail-stock-page .detail-item-grid .form-control {
        height: 35px;
    }

    .detail-stock-page label {
        margin-bottom: 4px;
        font-size: 11px;
    }

    .detail-stock-page .form-control {
        height: 35px;
        padding: 6px 9px;
        border-radius: 6px;
        font-size: 12px;
    }

    .detail-stock-page .btn {
        padding: 7px 11px;
        border-radius: 6px;
        font-size: 12px;
    }

    .detail-stock-page .detail-input-card {
        margin-bottom: 10px !important;
    }

    .detail-stock-page .detail-input-card .card-body {
        padding: 12px 14px !important;
    }

    .detail-stock-page .detail-input-card .card-body>.row {
        gap: 10px 12px;
    }

    .detail-stock-page .detail-input-card .form-control {
        height: 35px;
    }

    .detail-stock-page #btnTambahTransaksi {
        height: 35px;
    }

    .detail-stock-page #warningStok {
        margin-top: 3px;
    }

    .detail-stock-page .detail-history-card .card-header {
        padding: 10px 14px !important;
    }

    .detail-stock-page .detail-history-card .card-body {
        padding: 0 !important;
    }

    .detail-stock-page .detail-history-card th {
        padding: 9px 10px;
    }

    .detail-stock-page .detail-history-card td {
        padding: 8px 10px;
    }

    .detail-stock-page .detail-history-card .btn-sm {
        padding: 4px 7px;
    }

    .detail-stock-page hr {
        margin: 10px 0;
    }

    @media(max-width:1100px) {
        .detail-stock-page .detail-overview-layout {
            grid-template-columns: 1fr;
        }

        .detail-stock-page .detail-overview-primary {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: 1fr 1fr;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: 1/-1;
        }
    }

    @media(max-width:700px) {
        .detail-stock-page {
            padding: 8px !important;
        }

        .detail-stock-page .detail-overview-primary {
            display: flex;
        }

        .detail-stock-page .detail-filter-card .row {
            grid-template-columns: 1fr;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child {
            grid-column: auto;
        }

        .detail-stock-page .detail-filter-card .row>div:last-child>div {
            flex-wrap: wrap;
        }

        .detail-stock-page .detail-filter-card .row .btn {
            flex: 1;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div {
            grid-column: span 6;
        }

        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(4),
        .detail-stock-page .detail-input-card .card-body>.row>div:nth-child(7),
        .detail-stock-page .detail-input-card .card-body>.row>div:last-child {
            grid-column: span 12;
        }
    }
</style>

<style>
    /* V5: prevent inherited table height from stretching every history row */
    .detail-stock-page .detail-history-card .detail-table-scroll {
        height: auto !important;
        max-height: min(66vh, 640px);
    }

    .detail-stock-page .detail-history-card .detail-table-scroll>table {
        height: auto !important;
        min-height: 0 !important;
        margin-bottom: 0 !important;
    }

    .detail-stock-page .detail-history-card table tbody {
        height: auto !important;
    }

    .detail-stock-page .detail-history-card table tbody>tr {
        height: 36px !important;
        min-height: 0 !important;
    }

    .detail-stock-page .detail-history-card table tbody>tr>td {
        height: 36px !important;
        min-height: 0 !important;
        padding: 5px 9px !important;
        line-height: 1.2 !important;
        vertical-align: middle !important;
    }

    .detail-stock-page .detail-history-card table tbody>tr>td a {
        padding: 0 !important;
        line-height: 1.2 !important;
    }

    .detail-stock-page .detail-history-card table thead>tr {
        height: 34px !important;
    }

    .detail-stock-page .detail-history-card table thead th {
        padding: 7px 9px !important;
    }

    /* V4: compact history rows, sticky header and floating navigation */
    .detail-stock-page .detail-history-card {
        scroll-margin-top: 18px;
    }

    .detail-stock-page .detail-table-scroll {
        max-height: min(66vh, 640px);
        overflow: auto !important;
        overscroll-behavior: contain;
        position: relative;
    }

    .detail-stock-page .detail-history-card table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        table-layout: auto;
    }

    .detail-stock-page .detail-history-card thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 15 !important;
        background: #f0f5fc !important;
        box-shadow: 0 1px 0 #dce5f0;
        padding: 9px 10px !important;
    }

    .detail-stock-page .detail-history-card tbody tr {
        height: auto !important;
        min-height: 0 !important;
    }

    .detail-stock-page .detail-history-card tbody td {
        height: auto !important;
        min-height: 0 !important;
        padding: 8px 10px !important;
        line-height: 1.35 !important;
        vertical-align: middle !important;
        white-space: normal;
    }

    .detail-stock-page .detail-history-card tbody td p,
    .detail-stock-page .detail-history-card tbody td a {
        margin: 0 !important;
        line-height: 1.35 !important;
    }

    .detail-stock-page .detail-history-card tbody td .btn {
        padding: 4px 7px !important;
    }

    .detail-float-nav {
        position: fixed;
        right: 22px;
        bottom: 28px;
        z-index: 950;
        display: flex;
        flex-direction: column;
        gap: 9px;
        align-items: flex-end;
    }

    .detail-float-nav button {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        border: 1px solid #d9e4f3;
        border-radius: 999px;
        background: #fff;
        color: #1c4787;
        font-weight: 600;
        padding: 10px 14px;
        box-shadow: 0 5px 20px rgba(24, 49, 83, .16);
        cursor: pointer;
        font-size: 12px;
    }

    .detail-float-nav button:hover {
        background: #edf4ff;
    }

    .detail-float-nav .detail-go-top {
        background: #1e55a8;
        color: #fff;
        border-color: #1e55a8;
    }

    .detail-float-nav .detail-go-top:hover {
        background: #164383;
    }

    .detail-float-nav .detail-go-top[hidden] {
        display: none !important;
    }

    @media(max-width:700px) {
        .detail-float-nav {
            right: 12px;
            bottom: 16px;
        }

        .detail-float-nav button {
            padding: 9px 11px;
        }

        .detail-stock-page .detail-table-scroll {
            max-height: 65vh;
        }
    }


    /* V6: divider opname dan baris transaksi ringkas. Tidak memengaruhi kalkulasi. */
    .detail-stock-page .detail-history-card .detail-table-scroll>table {
        height: auto !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr:not(.stok-opname-divider) {
        height: auto !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr:not(.stok-opname-divider)>td {
        height: auto !important;
        min-height: 0 !important;
        padding: 4px 8px !important;
        line-height: 1.15 !important;
        font-size: 12px !important;
        vertical-align: middle !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr:not(.stok-opname-divider)>td a {
        display: inline !important;
        line-height: 1.15 !important;
        padding: 0 !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr:not(.stok-opname-divider)>td p,
    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr:not(.stok-opname-divider)>td div {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr.stok-opname-divider {
        height: auto !important;
    }

    .detail-stock-page .detail-history-card .detail-table-scroll tbody>tr.stok-opname-divider>td {
        position: relative;
        height: auto !important;
        padding: 9px 13px !important;
        background: #e9f2ff !important;
        color: #1c4d86 !important;
        border-top: 2px solid #2c69b7 !important;
        border-bottom: 2px solid #2c69b7 !important;
        font-size: 12px !important;
        font-weight: 500;
        white-space: normal !important;
    }

    .detail-stock-page .stok-opname-divider td>span {
        margin-left: 15px;
        display: inline-block;
    }

    .detail-stock-page .stok-opname-divider td>strong {
        letter-spacing: .025em;
    }

    /* V8 - satu lapisan tampilan khusus tabel riwayat, tidak menyentuh AJAX/edit. */
    .detail-stock-page #riwayatTransaksiSection {
        border: 1px solid #e3eaf3 !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 3px 14px rgba(30, 51, 80, .04) !important;
    }

    .detail-stock-page #riwayatTransaksiSection .card-header {
        background: #fff !important;
        color: #203450 !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        padding: 14px 17px !important;
        border-bottom: 1px solid #e8edf4 !important;
    }

    .detail-stock-page #riwayatTransaksiSection .card-body {
        padding: 0 !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-table-scroll {
        display: block !important;
        height: auto !important;
        max-height: min(68vh, 650px) !important;
        overflow: auto !important;
        align-content: start !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        min-width: 1050px !important;
        height: auto !important;
        min-height: 0 !important;
        margin: 0 !important;
        table-layout: auto !important;
        background: #fff !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table thead,
    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody {
        height: auto !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background: #f7f9fc !important;
        color: #68788e !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: .055em !important;
        text-transform: uppercase !important;
        padding: 10px 10px !important;
        height: 36px !important;
        white-space: nowrap !important;
        border: 0 !important;
        border-bottom: 1px solid #e4eaf2 !important;
        box-shadow: none !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody>tr:not(.stok-opname-divider) {
        height: auto !important;
        min-height: 0 !important;
        background: #fff !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody>tr:not(.stok-opname-divider):hover {
        background: #f7faff !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody>tr:not(.stok-opname-divider)>td {
        height: auto !important;
        min-height: 0 !important;
        padding: 6px 10px !important;
        vertical-align: middle !important;
        line-height: 1.25 !important;
        font-size: 12px !important;
        color: #34445c !important;
        background: transparent !important;
        border: 0 !important;
        border-bottom: 1px solid #eef1f5 !important;
        box-shadow: none !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody td a,
    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody td span,
    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody td p {
        line-height: 1.25 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody td .btn {
        padding: 3px 7px !important;
        font-size: 11px !important;
        line-height: 1.25 !important;
        box-shadow: none !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody>tr.stok-opname-divider {
        height: auto !important;
    }

    .detail-stock-page #riwayatTransaksiSection .detail-history-table tbody>tr.stok-opname-divider>td {
        padding: 9px 12px !important;
        height: auto !important;
        color: #27518a !important;
        background: #edf4ff !important;
        font-size: 11px !important;
        border: 0 !important;
        border-top: 1px solid #d3e3fa !important;
        border-bottom: 1px solid #d3e3fa !important;
        white-space: normal !important;
    }

    .detail-stock-page #riwayatTransaksiSection .stok-opname-divider td>span {
        display: inline-block;
        margin-left: 12px;
    }
</style>

<div class="detail-float-nav" aria-label="Navigasi cepat halaman">
    <button type="button" id="detailGoHistory" title="Ke riwayat transaksi"><i class="fa fa-history"></i>
        Riwayat</button>
    <button type="button" id="detailGoTop" class="detail-go-top" title="Kembali ke atas" hidden><i
            class="fa fa-arrow-up"></i> Ke Atas</button>
</div>

<script>
    (function() {
        function initDetailFloatNavigation() {
            const history = document.getElementById('riwayatTransaksiSection');
            const toHistory = document.getElementById('detailGoHistory');
            const toTop = document.getElementById('detailGoTop');
            if (!history || !toHistory || !toTop) return;
            const update = () => {
                const scrolled = window.scrollY || document.documentElement.scrollTop || 0;
                toTop.hidden = scrolled < 180;
            };
            toHistory.addEventListener('click', function() {
                history.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                toTop.hidden = false;
            });
            toTop.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            window.addEventListener('scroll', update, {
                passive: true
            });
            update();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDetailFloatNavigation);
        } else {
            initDetailFloatNavigation();
        }
    })();
</script>

@endsection
