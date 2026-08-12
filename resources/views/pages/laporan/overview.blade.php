@extends('master.master')

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Header Page & Action Buttons -->
       @section('btn')
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Warehouse Overview</h5>
                <p class="text-muted mb-0 small">Ringkasan kondisi persediaan material & aktivitas gudang terkini</p>
            </div>
            
        </div>
       
       @endsection

        <!-- 1. Executive Summary Cards (4 Metric Cards) -->
        {{-- <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center"
                            style="width: 52px; height: 52px;">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Total SKU</span>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalSku) }}</h4>
                            <span class="text-success small fw-medium"> --}}
                                {{-- <i class="bi bi-arrow-up-short"></i> +12.5% vs bulan lalu --}}
                            {{-- </span>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Inventory Value (RP Valuation - Solusi Lintas Satuan) -->
            {{-- <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Nilai Inventori</span>
                        <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</h4>
                        <span class="text-success small fw-medium">
                            <i class="bi bi-arrow-up-short"></i> +8.7% vs bulan lalu
                        </span>
                    </div>
                </div>
            </div> 
        </div> --}}

            <!-- Low Stock Alert -->
            {{-- <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center"
                            style="width: 52px; height: 52px;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Low Stock (Reorder Needed)</span>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($lowStockCount) }} SKU</h4>
                            <span class="text-danger small fw-medium"> --}}
                                {{-- <i class="bi bi-arrow-up-short"></i> +5 perlu restock --}}
                            {{-- </span>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Empty Stock -->
            {{-- <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center"
                            style="width: 52px; height: 52px;">
                            <i class="bi bi-x-circle fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Empty Stock (Stok Habis)</span>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($emptyStockCount) }} SKU</h4>
                            <span class="text-danger small fw-medium"> --}}
                                {{-- <i class="bi bi-exclamation-circle"></i> Segera lakukan PO --}}
                            {{-- </span>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- Warehouse Movement By Category -->
        <div class="row g-3 mb-4" style="margin-top: 50px">

            @foreach ($categoriesData as $cat)
          <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-3">

                    <div class="card border-0 shadow-sm h-100 p-4">

                        <div class="card-body">

                            <div class="d-flex justify-content-between">

                                <div>

                                    <div class="text-uppercase fw-bold">

                                        {{ $cat['name'] }}

                                    </div>

                                    <small class="text-muted">

                                        Warehouse Movement

                                    </small>

                                </div>

                                <span class="badge bg-light text-dark">

                                    {{ $cat['item_count'] }} Material

                                </span>

                            </div>

                            <hr>

                            <div class="row text-center" style="font-size: 12px">

                                <div class="col-6">

                                    <div class="text-success fw-bold">

                                        Rp {{ number_format($cat['total_in'], 0, ',', '.') }}

                                    </div>

                                    <small class="text-muted">

                                        IN

                                    </small>

                                </div>

                                <div class="col-6">

                                    <div class="text-danger fw-bold">

                                        Rp {{ number_format($cat['total_out'], 0, ',', '.') }}

                                    </div>

                                    <small class="text-muted">

                                        OUT

                                    </small>

                                </div>

                                


                            </div>

                            <div class="mt-3">

                                <div class="progress" style="height:8px">

                                    <div class="progress-bar"
                                        style="width: {{ $cat['percentage'] }}%;
                               background: {{ $cat['color'] }}">

                                    </div>

                                </div>

                                <small class="text-muted">

                                    {{ $cat['percentage'] }}% dari total movement

                                </small>

                            </div>

                        </div>

                    </div>

                </div>
            @endforeach

        </div>
  <!-- 4. Charts & Movement Overview -->
        <div class="row g-3 mb-4">
            <!-- Chart Stock In vs Stock Out -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card shadow-sm">

                        <div class="card-header">

                            <strong>
                                Stock In vs Stock Out (6 Bulan Terakhir)
                            </strong>

                        </div>

                        <div class="card-body">

                            <div id="stockMovementChart"></div>

                        </div>

                    </div>
                </div>
            </div>

            <!-- Donut Chart & Category Ratio -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Proporsi Nilai per Kategori</h6>
                    </div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <div id="chartCategoryDonut" style="height: 200px;" ></div>
                        <div class="mt-3 text-start small p-4">
                            @foreach ($categoriesData as $cat)
                                <div class="d-flex justify-content-between mb-1">
                                    <span><i class="bi bi-circle-fill me-1"
                                            style="color: {{ $cat['color'] }}; font-size: 8px;"></i>
                                        {{ $cat['name'] }}</span>
                                    <span class="fw-bold">{{ $cat['percentage'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 3. BARANG KELUAR TERBANYAK (FAST MOVING MATERIALS) - IMPROVEMENT CORE -->
       <div class="card shadow-sm mb-4">
           
    <div class="card-header"
         data-toggle="collapse"
         data-target="#fastMovingCollapse"
         style="cursor:pointer">
                <div>
                    <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-fire text-danger"></i> Barang Keluar Terbanyak (Fast Moving)
                    </h5>
                    <p class="text-muted small mb-0">Overview material paling intensif keluar dari gudang berdasarkan
                        kuantitas & nominal</p>
                </div>
                <div class="d-flex gap-2">
                    <form action="" method="GET" class="d-flex gap-2">
                        <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>7 Hari Terakhir
                            </option>
                            <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>Bulan Ini
                            </option>
                            <option value="6months" {{ request('period') == '6months' ? 'selected' : '' }}>6 Bulan Terakhir
                            </option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Peringkat</th>
                                <th>Kode & Nama Material</th>
                                <th>Kategori</th>
                                <th class="text-end">Total Keluar (Qty + Satuan)</th>
                                <th class="text-end">Total Nilai Keluar (Rp)</th>
                                <th class="text-center">Frekuensi Keluar</th>
                                <th class="text-center">Status Velocity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topOutgoingMaterials as $index => $material)
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">
                                        @if ($index == 0)
                                            <span class="badge bg-warning text-dark px-2 py-1"><i
                                                    class="bi bi-trophy-fill me-1"></i> #1</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary text-white px-2 py-1">#2</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-bronze text-dark border px-2 py-1">#3</span>
                                        @else
                                            #{{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $material->name }}</div>
                                        <span class="text-muted small">{{ $material->code }} • Rak:
                                            {{ $material->location }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $material->category }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        -{{ number_format($material->total_out_qty) }} {{ $material->unit }}
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rp {{ number_format($material->total_out_value, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info fw-semibold px-2 py-1">
                                            {{ $material->out_frequency }}x Transaksi
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Very Fast Moving</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data barang keluar
                                        pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

      

        <!-- 5. Stock Summary by Unit (Ringkasan per Satuan - Solusi Detail Satuan) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0 text-dark">Stock Summary by Unit (Ringkasan per Satuan)</h6>
                <p class="text-muted small mb-0">Rincian kuantitas fisik dan total nilai inventori yang dikelompokkan
                    menurut masing-masing satuan fisik</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Satuan (Unit)</th>
                                <th class="text-center">Jumlah SKU Item</th>
                                <th class="text-end">Total Fisik Stok</th>
                                <th class="text-end pe-4">Total Nilai Inventori (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unitSummaries as $unit)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-1 me-2">{{ $unit['unit'] }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($unit['total_items']) }} Item</td>
                                    <td class="text-end fw-semibold text-primary">
                                        {{ number_format($unit['total_stock'], 2) }} {{ $unit['unit'] }}</td>
                                    <td class="text-end pe-4 fw-bold text-dark">Rp
                                        {{ number_format($unit['inventory_value'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const data = @json($categoriesData);

        var options = {

            series: data.map(x => x.percentage),

            labels: data.map(x => x.name),

            chart: {
                type: 'donut',
                height: 220
            },

            legend: {
                position: 'bottom'
            }

        };

        new ApexCharts(
            document.querySelector("#chartCategoryDonut"),
            options
        ).render();
    </script>
    <<script>
        const movement = @json($stockMovement);

        const months = movement.map(x => x.month);

        const stockIn = movement.map(x => Number(x.stock_in));

        const stockOut = movement.map(x => Number(x.stock_out));

        var options = {

            chart: {

                type: 'area',

                height: 360,

                toolbar: {
                    show: false
                },

                zoom: {
                    enabled: false
                }

            },

            series: [

                {

                    name: 'Stock In',

                    data: stockIn

                },

                {

                    name: 'Stock Out',

                    data: stockOut

                }

            ],

            colors: [

                '#28a745',

                '#dc3545'

            ],

            stroke: {

                curve: 'smooth',

                width: 3

            },

            fill: {

                opacity: 0.15

            },

            markers: {

                size: 5,

                hover: {
                    size: 7
                }

            },

            xaxis: {

                categories: months,

                title: {
                    text: 'Bulan'
                }

            },

            yaxis: {

                title: {
                    text: 'Juta Rupiah'
                },

                labels: {

                    formatter: function(val) {

                        return 'Rp ' + val.toLocaleString('id-ID') + ' Jt';

                    }

                }

            },

            tooltip: {

                shared: true,

                intersect: false,

                custom: function({
                    series,
                    dataPointIndex
                }) {

                    let row = movement[dataPointIndex];

                    let diff = row.difference;

                    let status = row.status;

                    let color = status == 'Surplus' ?
                        '#28a745' :
                        '#dc3545';

                    return `

                <div style="padding:12px;min-width:220px">

                    <b>${row.month}</b>

                    <hr style="margin:8px 0">

                    🟢 Stock In

                    <br>

                    <b>${row.stock_in_text}</b>

                    <br><br>

                    🔴 Stock Out

                    <br>

                    <b>${row.stock_out_text}</b>

                    <hr style="margin:8px 0">

                    <span style="color:${color};font-weight:bold">

                        ${status}

                    </span>

                    <br>

                    Rp ${Math.abs(diff).toLocaleString('id-ID')} Juta

                </div>

            `;

                }

            },

            legend: {

                position: 'top'

            },

            dataLabels: {

                enabled: false

            },

            grid: {

                borderColor: '#e9ecef'

            }

        };

        new ApexCharts(

            document.querySelector("#stockMovementChart"),

            options

        ).render();
        // mimnimi
        $('#fastMovingCollapse').on('show.bs.collapse', function () {

    $(this)
        .prev()
        .find('i')
        .removeClass('fa-chevron-down')
        .addClass('fa-chevron-up');

});

$('#fastMovingCollapse').on('hide.bs.collapse', function () {

    $(this)
        .prev()
        .find('i')
        .removeClass('fa-chevron-up')
        .addClass('fa-chevron-down');

});
    </script>
    <style>
        /* custom-warehouse.css */
        .bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .bg-soft-warning {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .badge-bronze {
            background-color: #d97706;
            color: #ffffff;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #6b7280;
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01) !important;
        }
    </style>
@endsection
