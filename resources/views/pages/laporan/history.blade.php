@extends('master.master')

@section('title', 'History in/out warehouse')

@section('content')

    <div class="box">

        @section('btn')
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Warehouse Overview</h5>
                <p class="text-muted mb-0 small">Ringkasan kondisi persediaan material & aktivitas gudang terkini</p>
            </div>
               <div class="ml-4">
                <input type="text" id="search" class="form-control"
                    placeholder="Cari kode barang, nama barang, SPK, PO, Remark...">
            </div>
        </div>
        @endsection

        <div class="box-body">

           @section('btn')
            <div class="mb-3">
                <input type="text" id="search" class="form-control"
                    placeholder="Cari kode barang, nama barang, SPK, PO, Remark...">
            </div>
           
           @endsection
            <div class="row mb-3">

                <div class="col-md-3">
                    <label>Dari Tanggal</label>

                    <input type="date" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>

                    <input type="date" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label>Type</label>

                    <select id="type" class="form-control">
                        <option value="">All</option>
                        <option value="in">IN</option>
                        <option value="out">OUT</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Jenis Bahan</label>

                    <select id="jenis" class="form-control">

                        <option value="">Semua</option>

                        <option value="bahan baku">Bahan Baku</option>

                        <option value="bahan penolong">Bahan Penolong</option>

                        <option value="bahan penolong alat">Bahan Penolong Alat</option>

                        <option value="bahan finishing">Bahan Finishing</option>

                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end mt-2">

                    <button id="btnFilter" class="btn btn-primary mr-2">

                        <i class="fa fa-filter"></i>

                        Filter

                    </button>

                    <button id="btnReset" class="btn btn-secondary">

                        Reset

                    </button>

                    <button id="btnExport" class="btn btn-success">

                        <i class="fa fa-file-excel"></i>

                        Export Xlsx

                    </button>

                </div>

            </div>
            <div id="tableResult">
                @include('pages.laporan.partials.history_table')
            </div>

        </div>

    </div>

    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <script>
        $(function() {

            let timer;

            function loadData(page = 1) {

                $.ajax({

                    url: "{{ url('/laporan/warehouse-history') }}",

                    type: "GET",

                    data: {
                        search: $('#search').val(),
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val(),
                        type: $('#type').val(),
                        jenis: $('#jenis').val(),
                        page: page
                    },

                    success: function(html) {
                        $('#tableResult').html(html);
                    }

                });

            }
            // filter #date_from
            $('#btnFilter').on('click', function() {
                loadData();
            });

            $('#type').on('change', function() {
                loadData();
            });

            $('#btnReset').on('click', function() {

                $('#search').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                $('#type').val('');
                $('#jenis').val('');

                loadData();

            });

            $('#btnReset').click(function() {

                $('#search').val('');

                $('#date_from').val('');

                $('#date_to').val('');

                loadData();

            });
            $('#search').on('keyup', function() {

                clearTimeout(timer);

                timer = setTimeout(function() {
                    loadData();
                }, 300);

            });

            // Pagination AJAX
            $(document).on('click', '#tableResult .pagination a', function(e) {

                e.preventDefault();

                let page = $(this).attr('href').split('page=')[1];

                loadData(page);

            });

        });
    </script>

    <script>
        $(document).on('dblclick', '.js-inline-po', function() {

            let td = $(this);

            if (td.find('input').length) return;

            let value = td.data('value') ?? '';

            td.html(
                '<input type="text" class="form-control form-control-sm po-input" value="' + value + '">'
            );

            td.find('input').focus().select();

        });
        $(document).on('keypress', '.po-input', function(e) {

            if (e.which == 13) {

                $(this).blur();

            }

        });
        $(document).on('blur', '.po-input', function() {

            let input = $(this);

            let td = input.closest('td');

            let id = td.data('id');

            let value = input.val();

            $.ajax({

                url: '/history/update-po/' + id,

                type: 'POST',

                data: {
                    _token: '{{ csrf_token() }}',
                    po: value
                },

                success: function() {

                    td.data('value', value);

                    td.html(value);

                }

            });

        });

        function toggleExport() {

            let from = $('#date_from').val();

            let to = $('#date_to').val();

            $('#btnExport').prop(
                'disabled',
                !(from && to)
            );

        }

        toggleExport();

        $('#date_from,#date_to').on(
            'change',
            toggleExport
        );
        // export btn 
        $('#btnExport').on('click', function() {

            let params = $.param({

                search: $('#search').val(),

                date_from: $('#date_from').val(),

                date_to: $('#date_to').val(),

                type: $('#type').val(),

                jenis: $('#jenis').val()

            });

            window.location =
                "{{ route('warehouse.history.export') }}" +
                "?" +
                params;

        });
    </script>

    <style>
        .table-wrapper {
            max-height: 75vh;
            overflow-y: auto;
            overflow-x: auto;
        }

        .table-wrapper table {
            margin-bottom: 0;
        }

        .table-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 100;
            /* background: #fff; */
            white-space: nowrap;
        }
    </style>
@endsection
