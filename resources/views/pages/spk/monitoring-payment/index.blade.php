@extends('master.master')

@section('content')
    @include('pages.spk.monitoring-payment.style')
    <div class="card">

        <div class="card-header d-flex justify-content-between mt-4">


            <div class="row align-items-center">

                {{-- Judul --}}
                <div class="col-md-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-wallet text-primary me-2"></i>
                        Monitoring Payment
                    </h5>
                </div>

                {{-- Search --}}
                <div class="col-md-5">
                    <input type="text" id="searchTable" class="form-control form-control-sm"
                        placeholder="🔍 Cari No SPK, Sub, Kategori, PO, atau Item...">
                </div>

                {{-- Sort --}}
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="d-inline-flex align-items-center gap-2">

                        <label class="mb-0 text-muted small">
                            Urutkan
                        </label>

                        <select id="sortBy" class="form-select form-select-sm" style="width:200px">
                            <option value="po">📦 No PO</option>
                            <option value="sub">👤 Sub</option>
                            <option value="kategori">🏷️ Kategori</option>
                            <option value="saldo">💰 Saldo Terbesar</option>
                        </select>

                    </div>
                </div>


            </div>
        </div>
        {{-- table --}}
        <div class="card shadow-sm ">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm align-middle">

                    <thead class="table-light">

                        <tr>

                            <th rowspan="2">No</th>
                            <th rowspan="2">No. Spk</th>
                            <th rowspan="2">Sub</th>
                            <th rowspan="2">Kategori</th>
                            <th rowspan="2">po code</th>
                            <th rowspan="2">Item Code</th>
                            <th rowspan="2">Qty SPK</th>
                            <th rowspan="2">Qty In</th>

                            <th rowspan="2">Total</th>
                            <th rowspan="2">Saldo</th>
                            <th rowspan="2">potong bahan</th>


                        </tr>
                    </thead>

                    <tbody>

                        @php$no = 1;
                        @endphp

                        @foreach ($spk as $row)
                            @php
                                $data = is_array($row->data) ? $row->data : json_decode($row->data, true);

                                $items = $data['items'] ?? [];

                                $rowspan = count($items);

                                $total = collect($items)->sum('total');
                                $paid = collect($data['payments'] ?? [])->sum('amount');
                                $saldo = $total - $paid;
                            @endphp
                            @php
                                $currentPo = $data['no_po'];
                                $nextPo = null;

                                if (isset($spk[$loop->index + 1])) {
                                    $nextData = is_array($spk[$loop->index + 1]->data)
                                        ? $spk[$loop->index + 1]->data
                                        : json_decode($spk[$loop->index + 1]->data, true);

                                    $nextPo = $nextData['no_po'] ?? '';
                                }

                                $isLastPo = $currentPo != $nextPo;
                            @endphp
                            @foreach ($items as $index => $item)
                                <tr class="search-row"
                                    data-search="{{ strtolower(
                                        $data['no_spk'] . ' ' . $data['sup'] . ' ' . $data['kategori'] . ' ' . $data['no_po'] . ' ' . $item['nama'],
                                    ) }}">

                                    @if ($index == 0)
                                        <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $data['no_spk'] }}</td>
                                        <td class="item-a" rowspan="{{ $rowspan }}">{{ $data['sup'] }}</td>
                                        <td class="item-a" rowspan="{{ $rowspan }}">{{ $data['kategori'] }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $data['no_po'] }}</td>
                                    @endif

                                    <td class="item-code">{{ $item['nama'] }}</td>
                                    <td>{{ number_format($item['qty']) }}</td>
                                    <td>{{ number_format($qtyIn[$item['detail_po_id']] ?? 0) }}</td>

                                    @if ($index == 0)
                                        <td rowspan="{{ $rowspan }}">{{ number_format($total, 0, ',', '.') }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ number_format($saldo, 0, ',', '.') }}</td>
                                        <td rowspan="{{ $rowspan }}"></td>
                                    @endif

                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>

                </table>

            </div>
        </div>
    </div>
    <script>
        $('#sortBy').change(function() {

            let sort = $(this).val();

            window.location = "{{ route('payment-spk') }}" + "?sort=" + sort;

        });
        $('#searchTable').on('keyup', function() {

            let keyword = $(this).val().toLowerCase();

            $('.search-row').each(function() {

                let match = $(this).data('search').includes(keyword);

                if (match) {
                    $(this).show();

                    // tampilkan semua baris item setelahnya
                    $(this).nextUntil('.search-row').show();
                } else {
                    $(this).hide();

                    // sembunyikan semua baris item setelahnya
                    $(this).nextUntil('.search-row').hide();
                }

            });

        });
    </script>
@endsection
