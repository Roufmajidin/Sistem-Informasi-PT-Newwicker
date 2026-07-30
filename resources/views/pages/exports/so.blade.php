@extends('master.master')

@section('content')
    @include('pages.spk.monitoring-payment.style')

    <div class="card shadow-sm">

        <div class="card-header py-3 mt-4">

            <div class="row align-items-center">

                <div class="col-md-4">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes text-primary me-2"></i>
                        Stock Monitoring
                    </h5>
                </div>

                <div class="col-md-5">
                    <input type="text" id="searchTable" class="form-control form-control-sm"
                        placeholder="Cari PO, Company, Description, Article...">
                </div>

                <div class="col-md-3 text-end">

                    <select id="sortBy" class="form-select form-select-sm">
                        <option value="">Urutkan</option>
                        <option value="po">PO</option>
                        <option value="company">Company</option>
                    </select>

                </div>

            </div>

        </div>

        <div class="card-body p-2">

            @foreach ($po as $header)
                <div class="po-group mb-4" data-company="{{ strtolower($header->company_name) }}"
                    data-po="{{ strtolower($header->order_no) }}">

                    {{-- HEADER PO --}}
                    <div class="bg-primary text-white px-3 py-2 rounded-top">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <strong style="font-size:16px">
                                    {{ strtoupper($header->company_name) }}
                                </strong>

                                <span class="mx-2">|</span>

                                <strong>
                                    {{ $header->order_no }}
                                </strong>

                            </div>

                            <div>


                            </div>

                        </div>

                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-sm mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="50">No</th>
                                    <th width="120">Article</th>
                                    <th>Description</th>
                                    <th width="80">Qty</th>
                                    <th width="80">Qty loaded</th>
                                    <th width="80">CBM</th>
                                    <th width="90">Total CBM</th>
                                    <th width="180">Finishing</th>

                                </tr>

                            </thead>

                            <tbody>

                                @php $no=1; @endphp

                                @foreach ($header->detailPos as $detail)
                                    @php
                                        $item = $detail->detail;
                                    @endphp

                                    <tr class="search-row"
                                        data-search="{{ strtolower(
                                            $header->company_name .
                                                ' ' .
                                                $header->order_no .
                                                ' ' .
                                                ($item['description'] ?? '') .
                                                ' ' .
                                                ($item['article_nr_'] ?? ''),
                                        ) }}">

                                        <td>{{ $no++ }}</td>

                                        <td>
                                            {{ $item['article_nr_'] ?? '-' }}
                                        </td>

                                        <td class="item-b">
                                            {{ $item['description'] ?? '-' }}
                                        </td>

                                        <td class="text-center">

                                           {{ $item['qty'] ?? '-' }}

                                        </td>
                                       <td>

@if(isset($loadedItems[$detail->id]))

    @foreach($loadedItems[$detail->id] as $load)

        <div class="mb-1">

            <a href="{{ url('export/'.$load->export_ipl_id.'/edit') }}"
               class="fw-bold">

                {{ number_format($load->qty_pcs) }}

            </a>

            <br>

            <small class="text-muted">

                Seal numb :
                {{ $load->exportIpl->seal_no ?? '-' }}

            </small>

        </div>

    @endforeach

@endif

</td>

                                        <td class="text-center">
                                            {{ $item['cbm'] ?? 0 }}
                                        </td>

                                        <td class="text-center">
                                            {{ $item['total_cbm'] ?? 0 }}
                                        </td>

                                        <td>
                                            {{ $item['finishing'] ?? '-' }}
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

    <style>
        .po-group {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .po-group table {
            margin-bottom: 0;
        }

        .po-group thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 5;
        }
    </style>

    <script>
        $('#searchTable').on('keyup', function() {

            let keyword = $(this).val().toLowerCase();

            $('.po-group').each(function() {

                let found = false;

                $(this).find('.search-row').each(function() {

                    if ($(this).data('search').includes(keyword)) {
                        $(this).show();
                        found = true;
                    } else {
                        $(this).hide();
                    }

                });

                $(this).toggle(found);

            });

        });

        $('#sortBy').change(function() {

            let groups = $('.po-group').get();

            groups.sort(function(a, b) {

                let av, bv;

                if ($('#sortBy').val() == "company") {
                    av = $(a).data('company');
                    bv = $(b).data('company');
                } else {
                    av = $(a).data('po');
                    bv = $(b).data('po');
                }

                return av.localeCompare(bv);

            });

            $.each(groups, function(_, group) {
                $('.card-body').append(group);
            });

        });
    </script>
@endsection
