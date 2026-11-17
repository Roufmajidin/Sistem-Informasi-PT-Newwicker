@extends('master.master')

@section('title', 'History in/out warehouse')

@section('btn')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:12px">
        <div>
            <h5 class="fw-bold mb-1 text-dark">Warehouse History</h5>
            <p class="text-muted mb-0 small">Riwayat transaksi gudang — hanya stok Has Opname setelah cut-off terakhir</p>
        </div>
        <div style="min-width:280px;max-width:440px;width:100%">
            <input type="text" id="search" class="form-control" value="{{ request('search', '') }}"
                placeholder="Cari kode barang, nama barang, SPK, PO, Remark...">
        </div>
    </div>
@endsection

@section('content')
    <div class="box">
        <div class="box-body">
            <input type="hidden" id="opname" value="has_opname">

            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label for="date_from">Dari Tanggal</label>
                    <input type="date" id="date_from" class="form-control" value="{{ request('date_from', '') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="date_to">Sampai Tanggal</label>
                    <input type="date" id="date_to" class="form-control" value="{{ request('date_to', '') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="type">Type</label>
                    <select id="type" class="form-control">
                        <option value="">All</option>
                        <option value="in" @selected(request('type') === 'in')>IN</option>
                        <option value="out" @selected(request('type') === 'out')>OUT</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label for="jenis">Jenis Bahan</label>
                    <select id="jenis" class="form-control">
                        <option value="">Semua</option>
                        @foreach (['bahan baku' => 'Bahan Baku', 'bahan penolong' => 'Bahan Penolong', 'bahan penolong alat' => 'Bahan Penolong Alat', 'bahan finishing' => 'Bahan Finishing'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('jenis') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px">
                <button type="button" id="btnFilter" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                <button type="button" id="btnReset" class="btn btn-secondary">Reset</button>
                <button type="button" id="btnExport" class="btn btn-success"><i class="fa fa-file-excel"></i> Export
                    Xlsx</button>
                <small class="text-muted" id="filterInfo">Hanya transaksi barang Has Opname setelah cut-off opname
                    terakhir.</small>
            </div>

            <div id="historyError" class="alert alert-danger d-none" role="alert"></div>
            <div id="tableResult">
                @include('pages.laporan.partials.history_table')
            </div>
        </div>
    </div>

    <style>
        .table-wrapper {
            max-height: 75vh;
            overflow: auto;
        }

        .table-wrapper table {
            margin-bottom: 0;
        }

        .table-wrapper thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        #tableResult {
            position: relative;
            min-height: 100px;
        }

        #tableResult.is-loading {
            opacity: .55;
            pointer-events: none;
        }
    </style>

    <script>
        $(function() {
            let timer = null;
            let activeRequest = null;
            let requestNumber = 0;

            function filters(page = 1) {
                return {
                    search: $('#search').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    type: $('#type').val(),
                    jenis: $('#jenis').val(),
                    opname: 'has_opname',
                    page: page
                };
            }

            function loadData(page = 1) {
                const currentRequest = ++requestNumber;
                if (activeRequest && activeRequest.readyState !== 4) activeRequest.abort();
                $('#historyError').addClass('d-none').text('');
                $('#tableResult').addClass('is-loading');

                activeRequest = $.ajax({
                    url: @json(route('laporan.warehouse-history')),
                    type: 'GET',
                    data: filters(page),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(html) {
                        if (currentRequest === requestNumber) $('#tableResult').html(html);
                    },
                    error: function(xhr, status) {
                        if (status === 'abort' || currentRequest !== requestNumber) return;
                        let message = 'Gagal memuat data warehouse.';
                        if (xhr.status === 422) message =
                        'Filter tidak valid. Periksa rentang tanggal.';
                        $('#historyError').removeClass('d-none').text(message);
                    },
                    complete: function() {
                        if (currentRequest === requestNumber) $('#tableResult').removeClass(
                            'is-loading');
                    }
                });
            }

            $('#btnFilter').on('click', function() {
                loadData();
            });
            $('#type, #jenis').on('change', function() {
                loadData();
            });
            $('#search').on('input', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    loadData();
                }, 350);
            });
            $('#date_from, #date_to').on('change', function() {
                toggleExport();
            });

            $('#btnReset').on('click', function() {
                clearTimeout(timer);
                $('#search, #date_from, #date_to').val('');
                $('#type, #jenis').val('');
                toggleExport();
                loadData();
            });

            $(document).on('click', '#tableResult .pagination a', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                if (!href) return;
                const match = href.match(/[?&]page=(\d+)/);
                loadData(match ? Number(match[1]) : 1);
            });

            $(document).on('dblclick', '#tableResult .js-inline-po', function() {
                const td = $(this);
                if (td.find('input').length) return;
                const value = String(td.attr('data-value') || '');
                const input = $('<input>', {
                    type: 'text',
                    class: 'form-control form-control-sm po-input'
                }).val(value);
                td.empty().append(input);
                input.trigger('focus').trigger('select');
            });

            $(document).on('keydown', '#tableResult .po-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).trigger('blur');
                }
            });

            $(document).on('blur', '#tableResult .po-input', function() {
                const input = $(this);
                const td = input.closest('td');
                const id = td.data('id');
                const value = input.val();
                const previous = String(td.attr('data-value') || '');
                $.ajax({
                    url: '/history/update-po/' + encodeURIComponent(id),
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        po: value
                    },
                    success: function() {
                        td.attr('data-value', value).text(value || '-');
                    },
                    error: function() {
                        td.text(previous || '-');
                        alert('Gagal menyimpan PO.');
                    }
                });
            });

            function toggleExport() {
                $('#btnExport').prop('disabled', !($('#date_from').val() && $('#date_to').val()));
            }

            $('#btnExport').on('click', function() {
                if ($(this).prop('disabled')) return;
                const params = filters(1);
                delete params.page;
                window.location.href = @json(route('warehouse.history.export')) + '?' + $.param(params);
            });

            toggleExport();
            // Initial data must also honor the fixed Has Opname filter,
            // including when the URL previously contained opname=all.
            loadData();
        });
    </script>
@endsection
